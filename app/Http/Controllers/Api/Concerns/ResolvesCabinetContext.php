<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\Cabinet;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Builder;
use Tymon\JWTAuth\Facades\JWTAuth;

trait ResolvesCabinetContext
{
    protected function tokenCabinetId(): ?int
    {
        $user = auth('api')->user();

        if (!$user) {
            return null;
        }

        try {
            $cabinetId = JWTAuth::parseToken()->getPayload()->get('cabinet_id');

            if ($cabinetId !== null && $cabinetId !== '') {
                return (int) $cabinetId;
            }
        } catch (\Throwable) {
            // Fallback to the authenticated user if the payload is not available.
        }

        return $user->cabinet_id ? (int) $user->cabinet_id : null;
    }

    protected function cabinetDoctorAdminId(?int $cabinetId = null): ?int
    {
        $cabinetId ??= $this->tokenCabinetId();

        if (!$cabinetId) {
            return null;
        }

        return Cabinet::query()->whereKey($cabinetId)->value('docteur_id')
            ? optional(Cabinet::query()->with('doctor.admin')->find($cabinetId)?->doctor?->admin)->id
            : null;
    }

    protected function cabinetPatientQuery(Builder $query, int $cabinetId): Builder
    {
        return $query->whereHas('rendezVous.admin.user', function (Builder $builder) use ($cabinetId) {
            $builder->where('cabinet_id', $cabinetId);
        });
    }

    protected function patientBelongsToCabinet(int $patientId, int $cabinetId): bool
    {
        return Patient::query()
            ->whereKey($patientId)
            ->whereHas('rendezVous.admin.user', function (Builder $builder) use ($cabinetId) {
                $builder->where('cabinet_id', $cabinetId);
            })
            ->exists();
    }
}