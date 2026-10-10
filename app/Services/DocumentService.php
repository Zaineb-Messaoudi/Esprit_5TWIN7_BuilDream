<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentSignature;
use App\Models\DocumentTemplate;
use App\Models\DocumentVersion;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DocumentService
{
    public function __construct(
        private readonly \App\Services\RealtimeService $realtime
    ) {}

    /**
     * Create a document from a template.
     */
    public function createFromTemplate(
        DocumentTemplate $template,
        User $creator,
        array $variables = [],
        ?User $owner = null,
        ?array $metadata = null
    ): Document {
        return DB::transaction(function () use ($template, $creator, $variables, $owner, $metadata) {
            // Merge template variables with provided values
            $filledVariables = $this->mergeVariables($template->getVariableSchema(), $variables);

            // Render template content with variables
            $renderedContent = $this->renderTemplate($template->content, $filledVariables);

            // Create document
            $document = Document::create([
                'template_id' => $template->id,
                'document_number' => Document::generateNumber(),
                'title' => $template->name,
                'type' => $template->category,
                'status' => 'draft',
                'content' => $renderedContent,
                'variables' => $filledVariables,
                'metadata' => $metadata,
                'created_by' => $creator->id,
                'owner_id' => $owner?->id ?? $creator->id,
            ]);

            // Create initial version
            $this->createVersion($document, $creator, 'Initial creation from template');

            // Create signature requests if template has signature fields
            $this->createSignatureRequests($document, $template->getSignatureFields());

            Log::info("Document created: {$document->document_number} from template {$template->slug}");

            return $document->load(['signatures', 'template']);
        });
    }

    /**
     * Create a document from scratch (no template).
     */
    public function create(
        User $creator,
        array $data
    ): Document {
        return DB::transaction(function () use ($creator, $data) {
            $document = Document::create([
                'document_number' => Document::generateNumber(),
                'title' => $data['title'],
                'type' => $data['type'] ?? 'contract',
                'status' => 'draft',
                'content' => $data['content'] ?? '',
                'variables' => $data['variables'] ?? [],
                'metadata' => $data['metadata'] ?? [],
                'expires_at' => $data['expires_at'] ?? null,
                'created_by' => $creator->id,
                'owner_id' => $data['owner_id'] ?? $creator->id,
            ]);

            $this->createVersion($document, $creator, 'Initial creation');

            if (! empty($data['signature_fields'])) {
                $this->createSignatureRequestsFromFields($document, $data['signature_fields']);
            }

            return $document->load(['signatures']);
        });
    }

    /**
     * Create signature requests from template signature fields.
     */
    private function createSignatureRequests(Document $document, array $fields): void
    {
        foreach ($fields as $field) {
            $signer = $this->resolveSigner($document, $field['signer_role'] ?? 'external');

            DocumentSignature::create([
                'document_id' => $document->id,
                'signer_id' => $signer?->id,
                'signer_email' => $signer?->email ?? $field['email'] ?? '',
                'signer_name' => $signer?->name ?? $field['name'] ?? '',
                'signer_role' => $field['signer_role'] ?? 'external',
                'status' => 'pending',
                'expires_at' => now()->addDays(30),
            ]);
        }

        // Update document status
        if ($document->signatures()->exists()) {
            $document->update(['status' => 'pending_signatures']);
        }
    }

    /**
     * Create signature requests from custom fields.
     */
    private function createSignatureRequestsFromFields(Document $document, array $fields): void
    {
        foreach ($fields as $field) {
            $signer = null;
            if (! empty($field['signer_id'])) {
                $signer = User::find($field['signer_id']);
            }

            DocumentSignature::create([
                'document_id' => $document->id,
                'signer_id' => $signer?->id,
                'signer_email' => $field['email'] ?? '',
                'signer_name' => $field['name'] ?? '',
                'signer_role' => $field['role'] ?? 'external',
                'status' => 'pending',
                'expires_at' => now()->addDays(30),
            ]);
        }

        if ($document->signatures()->exists()) {
            $document->update(['status' => 'pending_signatures']);
        }
    }

    /**
     * Resolve signer based on role.
     */
    private function resolveSigner(Document $document, string $role): ?User
    {
        return match ($role) {
            'owner' => $document->owner,
            'renter' => $document->metadata['renter_id'] ? User::find($document->metadata['renter_id']) : null,
            default => null,
        };
    }

    /**
     * Merge template variables with provided values.
     */
    private function mergeVariables(array $schema, array $provided): array
    {
        $merged = [];

        foreach ($schema as $variable) {
            $name = $variable['name'];
            $default = $variable['default'] ?? null;
            $required = $variable['required'] ?? false;

            if (isset($provided[$name])) {
                $merged[$name] = $provided[$name];
            } elseif ($default !== null) {
                $merged[$name] = $default;
            } elseif ($required) {
                $merged[$name] = null; // Will be validated later
            }
        }

        // Add any extra provided variables not in schema
        foreach ($provided as $key => $value) {
            if (! isset($merged[$key])) {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * Render template content with variables.
     */
    private function renderTemplate(string $template, array $variables): string
    {
        $rendered = $template;

        foreach ($variables as $key => $value) {
            $placeholder = '{{'.$key.'}}';
            $rendered = str_replace($placeholder, $value ?? '', $rendered);
        }

        return $rendered;
    }

    /**
     * Create a document version.
     */
    public function createVersion(Document $document, User $creator, string $changeSummary): DocumentVersion
    {
        $version = $document->versions()->max('version') + 1;

        return DocumentVersion::create([
            'document_id' => $document->id,
            'version' => $version,
            'content' => $document->content,
            'variables' => $document->variables,
            'change_summary' => $changeSummary,
            'created_by' => $creator->id,
        ]);
    }

    /**
     * Update document content and create new version.
     */
    public function updateContent(Document $document, User $user, string $content, string $changeSummary): Document
    {
        $document->update(['content' => $content]);
        $this->createVersion($document, $user, $changeSummary);

        $this->logAudit($document, $user, 'updated', ['content_changed' => true]);

        return $document->load('versions');
    }

    /**
     * Send document for signatures.
     */
    public function sendForSignatures(Document $document, User $user): Document
    {
        abort_unless($document->isDraft(), 400, 'Only draft documents can be sent for signatures.');
        abort_unless($document->signatures()->exists(), 400, 'Document must have at least one signature request.');

        $document->update(['status' => 'pending_signatures']);

        // Generate tokens and send notification emails
        foreach ($document->signatures()->where('status', 'pending')->get() as $signature) {
            $token = $signature->generateToken();
            $this->sendSignatureRequestEmail($signature);
        }

        $document->update(['status' => 'pending_signatures']);
        $this->logAudit($document, $document->creator, 'signature_requested');

        return $document->fresh();
    }

    /**
     * Sign a document.
     */
    public function signDocument(DocumentSignature $signature, string $signatureData, array $metadata = []): bool
    {
        if (! $signature->canBeSigned()) {
            return false;
        }

        $signed = $signature->sign($signatureData, $metadata);

        if ($signed) {
            $this->logAudit($signature->document, $signature->signer ?? null, 'signature_signed', [
                'signature_id' => $signature->id,
            ]);

            // Notify document owner
            if ($signature->document->owner_id !== $signature->signer_id) {
                $this->realtime->broadcastToUser($signature->document->owner_id, 'document.signed', [
                    'document_id' => $signature->document_id,
                    'signature_id' => $signature->id,
                ]);
            }
        }

        return true;
    }

    /**
     * Decline to sign.
     */
    public function declineSignature(DocumentSignature $signature, string $reason): void
    {
        $signature->decline($reason);

        $this->logAudit($signature->document, $signature->signer ?? null, 'signature_declined', [
            'signature_id' => $signature->id,
            'reason' => $reason,
        ]);
    }

    /**
     * Share document with user or email.
     */
    public function shareDocument(
        Document $document,
        User $sharedBy,
        ?User $sharedWith = null,
        ?string $email = null,
        string $permission = 'view',
        ?\DateTime $expiresAt = null
    ): \App\Models\DocumentShare {
        return \App\Models\DocumentShare::create([
            'document_id' => $document->id,
            'shared_by' => $sharedBy->id,
            'shared_with' => $sharedWith?->id,
            'shared_email' => $email,
            'permission' => $permission,
            'expires_at' => $expiresAt,
            'token' => Str::random(64),
        ]);
    }

    /**
     * Get documents for a user.
     */
    public function getUserDocuments(User $user, ?string $status = null, int $perPage = 20): LengthAwarePaginator
    {
        $query = Document::query()
            ->where(function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                    ->orWhere('created_by', $user->id)
                    ->orWhereHas('signatures', fn ($q) => $q->where('signer_id', $user->id))
                    ->orWhereHas('shares', fn ($q) => $q->where('shared_with', $user->id));
            })
            ->with(['template', 'signatures', 'owner', 'creator'])
            ->latest();

        if ($status) {
            $query->where('status', $status);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get document with all relations.
     */
    public function getDocument(int $id, User $user): ?Document
    {
        $document = Document::with([
            'template',
            'signatures.signer',
            'versions.creator',
            'shares.sharedWith',
            'auditTrail.user',
        ])->find($id);

        if (! $document) {
            return null;
        }

        // Check access
        $hasAccess = $document->owner_id === $user->id
            || $document->created_by === $user->id
            || $document->signatures()->where('signer_id', $user->id)->exists()
            || $document->shares()->where('shared_with', $user->id)->exists();

        if (! $hasAccess) {
            return null;
        }

        return $document;
    }

    /**
     * Send signature request email.
     */
    private function sendSignatureRequestEmail(DocumentSignature $signature): void
    {
        // Queue email notification
        $signature->document->owner->notify(
            new \App\Notifications\DocumentSignatureRequested($signature)
        );
    }

    /**
     * Log audit trail entry.
     */
    private function logAudit(Document $document, ?User $user, string $action, array $details = []): void
    {
        \App\Models\DocumentAuditTrail::create([
            'document_id' => $document->id,
            'user_id' => $user?->id,
            'action' => $action,
            'details' => $details,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Get document statistics.
     */
    public function getUserDocumentStats(User $user): array
    {
        $documents = Document::where(function ($q) use ($user) {
            $q->where('owner_id', $user->id)
                ->orWhere('created_by', $user->id);
        });

        return [
            'total' => (clone $documents)->count(),
            'draft' => (clone $documents)->where('status', 'draft')->count(),
            'pending' => (clone $documents)->where('status', 'pending_signatures')->count(),
            'completed' => (clone $documents)->where('status', 'completed')->count(),
            'expired' => (clone $documents)->where('status', 'expired')->count(),
            'total_signatures' => \App\Models\DocumentSignature::whereHas('document', fn ($q) => $q->where('owner_id', $user->id))
                ->where('status', 'signed')->count(),
            'pending_signatures' => \App\Models\DocumentSignature::whereHas('document', fn ($q) => $q->where('owner_id', $user->id))
                ->where('status', 'pending')->count(),
        ];
    }
}
