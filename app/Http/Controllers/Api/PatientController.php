<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\ResolvesCabinetContext;
use App\Models\Patient;
use App\Models\RendezVous;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PatientController extends Controller
{
    use ResolvesCabinetContext;

    public function index(Request $request): JsonResponse
    {
        $query = Patient::query()->with('user');
        $user = auth('api')->user();
        $cabinetId = $this->tokenCabinetId();

        if ($user?->isMedecin() || $user?->isSecretaire()) {
            if ($cabinetId) {
                $query->whereHas('rendezVous.admin.user', function ($builder) use ($cabinetId) {
                    $builder->where('cabinet_id', $cabinetId);
                });
            }
        } elseif ($user?->isPatient()) {
            $query->whereKey(optional($user->patient)->id);
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $patients = $query->paginate(15);
        $patients->getCollection()->transform(function (Patient $patient) use ($cabinetId) {
            return $this->formatPatientSummary($patient, $cabinetId);
        });

        return response()->json($patients, 200);
    }

    public function doctorPatients(Request $request): JsonResponse
    {
        return $this->index($request);
    }

    public function secretaryPatients(Request $request): JsonResponse
    {
        return $this->index($request);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:50',
            'prenom' => 'required|string|max:50',
            'email' => 'required|email|max:100|unique:users',
            'password' => 'required|string|min:6',
            'telephone' => 'nullable|string|max:15',
            'date_naissance' => 'nullable|date',
            'cin' => 'nullable|string|max:10|unique:patients',
            'adresse' => 'nullable|string',
            'ville' => 'nullable|string|max:100',
            'groupe_sanguin' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'antecedents' => 'nullable|string',
            'antecedents_familiaux' => 'nullable|string',
            'allergies' => 'nullable|string',
            'poids_kg' => 'nullable|numeric|min:0',
            'taille_cm' => 'nullable|integer|min:0',
            'traitement_en_cours' => 'nullable|string',
            'photo_profil' => 'nullable|image|max:2048',
        ]);

        $result = DB::transaction(function () use ($request, $validated) {
            $photoPath = null;

            if ($request->hasFile('photo_profil')) {
                $photoPath = $request->file('photo_profil')->store('photos', 'public');
            }

            $user = \App\Models\User::create([
                'nom' => $validated['nom'],
                'prenom' => $validated['prenom'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'telephone' => $validated['telephone'] ?? null,
                'is_active' => 1,
                'photo_profil' => $photoPath,
            ]);

            $patient = Patient::create([
                'user_id' => $user->id,
                'date_naissance' => $validated['date_naissance'] ?? null,
                'cin' => $validated['cin'] ?? null,
                'adresse' => $validated['adresse'] ?? null,
                'ville' => $validated['ville'] ?? null,
                'groupe_sanguin' => $validated['groupe_sanguin'] ?? null,
                'antecedents' => $validated['antecedents'] ?? null,
                'antecedents_familiaux' => $validated['antecedents_familiaux'] ?? null,
                'allergies' => $validated['allergies'] ?? null,
                'poids_kg' => $validated['poids_kg'] ?? null,
                'taille_cm' => $validated['taille_cm'] ?? null,
                'traitement_en_cours' => $validated['traitement_en_cours'] ?? null,
                'date_creation_dossier' => now()->toDateString(),
            ]);

            $patient->load('user');
            if ($patient->user?->photo_profil) {
                $patient->user->photo_profil = url('storage/' . ltrim($patient->user->photo_profil, '/'));
            }

            return $patient;
        });

        return response()->json($result, 201);
    }

    public function show(Patient $patient): JsonResponse
    {
        $user = auth('api')->user();

        if ($user?->isPatient()) {
            if ((int) optional($user->patient)->id !== (int) $patient->id) {
                return response()->json(['message' => 'Accès refusé.'], 403);
            }

            $patient->load([
                'user',
                'rendezVous.admin.user',
                'consultations.admin.user',
                'consultations.ordonnances.medicaments',
                'consultations.analyses',
                'consultations.radiologies',
            ]);

            return response()->json($patient, 200);
        }

        $cabinetId = $this->tokenCabinetId();

        if (!$cabinetId || !$this->patientBelongsToCabinet($patient->id, $cabinetId)) {
            return response()->json(['message' => 'Ce patient n\'est pas rattaché à votre cabinet.'], 403);
        }

        $patient->load([
            'user',
            'rendezVous' => function ($query) use ($cabinetId) {
                $query->whereHas('admin.user', function ($builder) use ($cabinetId) {
                    $builder->where('cabinet_id', $cabinetId);
                })->orderByDesc('date_heure');
            },
        ]);

        return response()->json($patient, 200);
    }

    public function doctorPatientRecord(Patient $patient): JsonResponse
    {
        $access = $this->show($patient);

        if ($access->getStatusCode() !== 200) {
            return $access;
        }

        $payload = $access->getData(true);

        return response()->json([
            'patient' => $payload,
            'medical_record' => [
                'date_naissance' => $patient->date_naissance,
                'adresse' => $patient->adresse,
                'ville' => $patient->ville,
                'groupe_sanguin' => $patient->groupe_sanguin,
                'antecedents' => $patient->antecedents,
                'antecedents_familiaux' => $patient->antecedents_familiaux,
                'allergies' => $patient->allergies,
                'traitement_en_cours' => $patient->traitement_en_cours,
                'poids_kg' => $patient->poids_kg,
                'taille_cm' => $patient->taille_cm,
            ],
        ], 200);
    }

    public function doctorPatientConsultations(Patient $patient): JsonResponse
    {
        $cabinetId = $this->tokenCabinetId();

        if (!$cabinetId || !$this->patientBelongsToCabinet($patient->id, $cabinetId)) {
            return response()->json(['message' => 'Ce patient n\'est pas rattaché à votre cabinet.'], 403);
        }

        $consultations = $patient->consultations()
            ->whereHas('admin.user', function ($builder) use ($cabinetId) {
                $builder->where('cabinet_id', $cabinetId);
            })
            ->with(['admin.user', 'ordonnances.medicaments', 'analyses'])
            ->orderByDesc('date')
            ->get();

        return response()->json($consultations, 200);
    }

    public function doctorPatientAnalyses(Patient $patient): JsonResponse
    {
        $cabinetId = $this->tokenCabinetId();

        if (!$cabinetId || !$this->patientBelongsToCabinet($patient->id, $cabinetId)) {
            return response()->json(['message' => 'Ce patient n\'est pas rattaché à votre cabinet.'], 403);
        }

        $analyses = $patient->consultations()
            ->whereHas('admin.user', function ($builder) use ($cabinetId) {
                $builder->where('cabinet_id', $cabinetId);
            })
            ->with(['admin.user', 'analyses'])
            ->get()
            ->pluck('analyses')
            ->flatten()
            ->values();

        return response()->json($analyses, 200);
    }

    public function secretaryAppointments(Request $request): JsonResponse
    {
        $cabinetId = $this->tokenCabinetId();

        if (!$cabinetId) {
            return response()->json([], 200);
        }

        $query = RendezVous::with(['patient.user', 'admin.user'])
            ->whereHas('admin.user', function ($builder) use ($cabinetId) {
                $builder->where('cabinet_id', $cabinetId);
            });

        if ($request->filled('date')) {
            $query->whereDate('date_heure', $request->query('date'));
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->query('statut'));
        }

        $appointments = $query->orderByDesc('date_heure')->get()->map(fn (RendezVous $rendezVous) => $this->formatAppointment($rendezVous))->values();

        return response()->json($appointments, 200);
    }

    public function searchByContact(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => 'required|string|min:2',
        ]);

        $patients = Patient::query()
            ->with('user')
            ->whereHas('user', function ($builder) use ($validated) {
                $builder->where('email', 'like', '%' . $validated['q'] . '%')
                    ->orWhere('telephone', 'like', '%' . $validated['q'] . '%');
            })
            ->get()
            ->map(fn (Patient $patient) => $this->formatPatientSummary($patient, $this->tokenCabinetId()))
            ->values();

        return response()->json($patients, 200);
    }

    public function update(Request $request, Patient $patient): JsonResponse
    {
        $validated = $request->validate([
            'date_naissance' => 'nullable|date',
            'cin' => 'nullable|string|max:10',
            'adresse' => 'nullable|string',
            'ville' => 'nullable|string|max:100',
            'telephone' => 'nullable|string|max:15',
            'groupe_sanguin' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'antecedents' => 'nullable|string',
            'antecedents_familiaux' => 'nullable|string',
            'allergies' => 'nullable|string',
            'poids_kg' => 'nullable|numeric|min:0',
            'taille_cm' => 'nullable|integer|min:0',
            'traitement_en_cours' => 'nullable|string',
        ]);

        $validated['dossier_updated_at'] = now();

        $patient->update($validated);

        if (isset($validated['telephone'])) {
            $patient->user->update(['telephone' => $validated['telephone']]);
        }

        return response()->json($patient, 200);
    }

    public function destroy(Patient $patient): JsonResponse
    {
        $patient->user->delete();

        return response()->json(['message' => 'Patient supprimé avec succès.'], 200);
    }

    public function updateProfil(Request $request, Patient $patient): JsonResponse
    {
        $validated = $request->validate([
            'nom' => 'nullable|string|max:50',
            'prenom' => 'nullable|string|max:50',
            'telephone' => 'nullable|string|max:15',
            'photo_profil' => 'nullable|image|max:2048',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo_profil')) {
            $photoPath = $request->file('photo_profil')->store('photos', 'public');
            $validated['photo_profil'] = $photoPath;
        }

        if (isset($validated['nom'])) {
            $patient->user->update(['nom' => $validated['nom']]);
        }
        if (isset($validated['prenom'])) {
            $patient->user->update(['prenom' => $validated['prenom']]);
        }
        if (isset($validated['telephone'])) {
            $patient->user->update(['telephone' => $validated['telephone']]);
        }
        if (isset($validated['photo_profil'])) {
            $patient->user->update(['photo_profil' => $validated['photo_profil']]);
        }

        $patient->user->refresh();

        return response()->json(['user' => $patient->user], 200);
    }

    private function formatPatientSummary(Patient $patient, ?int $cabinetId = null): array
    {
        $appointmentsQuery = $patient->rendezVous()->with(['admin.user']);

        if ($cabinetId) {
            $appointmentsQuery->whereHas('admin.user', function ($builder) use ($cabinetId) {
                $builder->where('cabinet_id', $cabinetId);
            });
        }

        $appointments = $appointmentsQuery->orderByDesc('date_heure')->get();

        return [
            'id' => $patient->id,
            'user' => $patient->user,
            'date_naissance' => $patient->date_naissance,
            'cin' => $patient->cin,
            'adresse' => $patient->adresse,
            'ville' => $patient->ville,
            'groupe_sanguin' => $patient->groupe_sanguin,
            'allergies' => $patient->allergies,
            'antecedents' => $patient->antecedents,
            'traitement_en_cours' => $patient->traitement_en_cours,
            'appointment_count' => $appointments->count(),
            'last_appointment_at' => optional($appointments->first())->date_heure,
            'rendezVous' => $appointments,
        ];
    }

    private function formatAppointment(RendezVous $rendezVous): array
    {
        return [
            'id' => $rendezVous->id,
            'patient_id' => $rendezVous->patient_id,
            'cabinet_id' => $rendezVous->admin?->user?->cabinet_id,
            'date' => optional($rendezVous->date_heure)->toDateString(),
            'heure' => optional($rendezVous->date_heure)->format('H:i'),
            'motif' => $rendezVous->motif,
            'statut' => match ($rendezVous->statut) {
                'en_attente' => 'pending',
                'confirme' => 'confirmed',
                'annule' => 'cancelled',
                default => $rendezVous->statut,
            },
            'created_at' => $rendezVous->created_at,
            'patient' => $rendezVous->patient?->user,
            'doctor' => $rendezVous->admin?->user,
        ];
    }
}
