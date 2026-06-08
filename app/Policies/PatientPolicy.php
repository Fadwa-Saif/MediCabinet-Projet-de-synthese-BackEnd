<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    /**
     * Determine if the user can view a list of patients.
     */
    public function viewAny(User $user): bool
    {
        // Medecins and secretaires can view patients
        return $user->isMedecin() || $user->isSecretaire();
    }

    /**
     * Determine if the user can view a specific patient.
     */
    public function view(User $user, Patient $patient): bool
    {
        // If user is a patient, they can only view themselves
        if ($user->isPatient()) {
            return $user->patient->id === $patient->id;
        }

        // If user is a medecin, check if patient is assigned to them
        if ($user->isMedecin()) {
            $admin = $user->admin;
            return $patient->medecins()
                ->where('medecin_id', $admin->id)
                ->where('statut', 'actif')
                ->exists();
        }

        // If user is a secretaire, check if patient is assigned to their doctor
        if ($user->isSecretaire()) {
            return $patient->medecins()
                ->where('medecin_id', $user->secretaryRequest?->medecin_id)
                ->where('statut', 'actif')
                ->exists();
        }

        return false;
    }

    /**
     * Determine if the user can create a patient.
     */
    public function create(User $user): bool
    {
        return $user->isSecretaire();
    }

    /**
     * Determine if the user can update a patient.
     */
    public function update(User $user, Patient $patient): bool
    {
        // Patient can update their own profile
        if ($user->isPatient()) {
            return $user->patient->id === $patient->id;
        }

        // Secretaire can update patients assigned to their doctor
        if ($user->isSecretaire()) {
            return $patient->medecins()
                ->where('medecin_id', $user->secretaryRequest?->medecin_id)
                ->where('statut', 'actif')
                ->exists();
        }

        return false;
    }

    /**
     * Determine if the user can delete a patient.
     */
    public function delete(User $user, Patient $patient): bool
    {
        // Only secretaires can delete patients assigned to their doctor
        if ($user->isSecretaire()) {
            return $patient->medecins()
                ->where('medecin_id', $user->secretaryRequest?->medecin_id)
                ->where('statut', 'actif')
                ->exists();
        }

        return false;
    }
}
