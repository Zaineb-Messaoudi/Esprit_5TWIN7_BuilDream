<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreDocumentRequest;
use App\Http\Requests\Api\SignDocumentRequest;
use App\Models\Document;
use App\Models\DocumentSignature;
use App\Models\DocumentTemplate;
use App\Models\DocumentShare;
use App\Services\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Document management API controller.
 */
class DocumentController
{
    public function __construct(private readonly DocumentService $service) {}

    /**
     * List documents for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $status = $request->get('status');
        $documents = $this->service->getUserDocuments($request->user(), $status, $request->integer('per_page', 20));

        return response()->json($documents);
    }

    /**
     * Get document templates.
     */
    public function templates(Request $request): JsonResponse
    {
        $templates = DocumentTemplate::active()
            ->when($request->filled('category'), fn ($q) => $q->category($request->category))
            ->latest()
            ->paginate(20);

        return response()->json($templates);
    }

    /**
     * Get a single template.
     */
    public function showTemplate(DocumentTemplate $template): JsonResponse
    {
        return response()->json($template->load('documents'));
    }

    /**
     * Create a new document from template or scratch.
     */
    public function store(StoreDocumentRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if ($request->filled('template_id')) {
            $template = DocumentTemplate::findOrFail($data['template_id']);
            $document = $this->service->createFromTemplate(
                $template,
                $user,
                $data['variables'] ?? [],
                $user
            );
        } else {
            $document = $this->service->create($user, $data);
        }

        return response()->json([
            'message' => 'Document created successfully.',
            'document' => $document->load(['signatures', 'template']),
        ], 201);
    }

    /**
     * Show a single document.
     */
    public function show(Document $document): JsonResponse
    {
        $document = $this->service->getDocument($document->id, request()->user());

        abort_unless($document, 403);

        return response()->json($document->load([
            'template',
            'signatures.signer',
            'versions.creator',
            'shares.sharedWith',
            'auditTrail.user',
        ]));
    }

    /**
     * Update document content (draft only).
     */
    public function update(Request $request, Document $document): JsonResponse
    {
        abort_unless($document->isDraft(), 400, 'Only draft documents can be edited.');

        $validated = $request->validate([
            'content' => ['required', 'string'],
            'title' => ['nullable', 'string', 'max:255'],
            'variables' => ['nullable', 'array'],
        );

        $changeSummary = $request->input('change_summary', 'Content updated via API');

        $document = $this->service->updateContent($document, request()->user(), $validated['content'], $changeSummary);

        return response()->json([
            'message' => 'Document updated successfully.',
            'document' => $document->load('versions'),
        ]);
    }

    /**
     * Send document for signatures.
     */
    public function sendForSignatures(Document $document): JsonResponse
    {
        abort_unless($document->status === 'draft', 400, 'Only draft documents can be sent for signatures.');

        $document = $this->service->sendForSignatures($document, request()->user());

        return response()->json([
            'message' => 'Document sent for signatures.',
            'document' => $document->load('signatures'),
        ]);
    }

    /**
     * Sign a document.
     */
    public function sign(SignDocumentRequest $request, DocumentSignature $signature): JsonResponse
    {
        $signed = $this->service->signDocument($signature, $request->validated('signature_data'), $request->validated('metadata') ?? []);

        if (!$signed) {
            return response()->json(['message' => 'Signature cannot be completed.'], 400);
        }

        return response()->json([
            'message' => 'Document signed successfully.',
            'signature' => $signature->fresh(),
        ]);
    }

    /**
     * Decline to sign.
     */
    public function decline(Request $request, DocumentSignature $signature): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $this->service->declineSignature($signature, $validated['reason']);

        return response()->json(['message' => 'Signature declined.']);
    }

    /**
     * Share document with user or email.
     */
    public function share(Request $request, Document $document): JsonResponse
    {
        $validated = $request->validate([
            'shared_with' => ['nullable', 'exists:users,id'],
            'shared_email' => ['nullable', 'email', 'max:255'],
            'permission' => ['required', 'in:view,comment,edit,sign'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);

        $share = $this->service->shareDocument(
            $document,
            request()->user(),
            $request->user($validated['shared_with'] ?? null),
            $validated['shared_email'] ?? null,
            $validated['permission'],
            $validated['expires_at'] ?? null
        );

        return response()->json([
            'message' => 'Document shared successfully.',
            'share' => $share->load('sharedWith'),
        ], 201);
    }

    /**
     * Get document shares.
     */
    public function shares(Document $document): JsonResponse
    {
        $shares = $document->shares()->with('sharedWith:id,name,email')->paginate(20);

        return response()->json($shares);
    }

    /**
     * Revoke document share.
     */
    public function revokeShare(Document $document, DocumentShare $share): JsonResponse
    {
        abort_unless($share->document_id === $document->id, 404);

        $share->delete();

        return response()->json(['message' => 'Share revoked.']);
    }

    /**
     * Get document statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        $stats = $this->service->getUserDocumentStats($request->user());

        return response()->json($stats);
    }

    /**
     * Get document versions.
     */
    public function versions(Document $document): JsonResponse
    {
        $versions = $document->versions()->with('creator:id,name')->latest()->paginate(20);

        return response()->json($versions);
    }

    /**
     * Restore a document version.
     */
    public function restoreVersion(Document $document, \App\Models\DocumentVersion $version): JsonResponse
    {
        abort_unless($version->document_id === $document->id, 404);

        $document = $this->service->updateContent($document, request()->user(), $version->content, "Restored version {$version->version}");

        return response()->json([
            'message' => 'Version restored successfully.',
            'document' => $document->load('versions'),
        ]);
    }

    /**
     * Get document audit trail.
     */
    public function auditTrail(Document $document): JsonResponse
    {
        $audit = $document->auditTrail()->with('user:id,name')->latest()->paginate(20);

        return response()->json($audit);
    }
}