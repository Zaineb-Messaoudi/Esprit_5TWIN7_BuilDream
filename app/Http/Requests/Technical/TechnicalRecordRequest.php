<?php

namespace App\Http\Requests\Technical;

use Illuminate\Foundation\Http\FormRequest;

abstract class TechnicalRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() || $this->user()?->isOwner();
    }

    public function setActorAndRoute(\Illuminate\Http\Request $request, ?\Illuminate\Database\Eloquent\Model $record = null): void
    {
        $this->setContainer(app());
        $this->setRedirector(app('redirect'));
        $this->setRouteResolver(fn () => $request->route());
        $this->setUserResolver(fn () => $request->user());

        if ($record && $request->route()) {
            $parameter = $request->route()->parameterNames()[0] ?? null;
            if ($parameter) {
                $request->route()->setParameter($parameter, $record);
            }
        }
    }

    protected function isAdminActor(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    protected function ownerEquipmentRule(): array
    {
        $rule = \Illuminate\Validation\Rule::exists('equipment', 'id');
        if (! $this->isAdminActor()) {
            $rule->where('owner_id', $this->user()->getKey());
        }

        return ['required', 'integer', $rule];
    }

    public function validateRecord(): array
    {
        $this->validateResolved();

        return $this->validated();
    }
}
