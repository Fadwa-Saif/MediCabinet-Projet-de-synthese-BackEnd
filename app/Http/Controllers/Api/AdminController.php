<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\SecretaryApprovedMail;
use App\Mail\SecretaryRefusedMail;
use App\Models\Admin;
use App\Models\Analyse;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\RendezVous;
use App\Models\SecretaryMedecin;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    public function index(): JsonResponse
    {
        $admins = Admin::with('user')->orderBy('role')->get();

        return response()->json($admins, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:50',
            'prenom' => 'required|string|max:50',
            'email' => 'required|email|max:100|unique:users,email',
            'password' => 'required|string|min:8',
            'telephone' => 'nullable|string|max:15',
            'role' => 'required|in:medecin,secretaire',
            'matricule' => 'nullable|string|max:50|unique:admins,matricule',
            'biographie' => 'nullable|string',
        ]);

        $user = User::create([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'telephone' => $validated['telephone'] ?? null,
            'is_active' => true,
        ]);

        $admin = Admin::create([
            'user_id' => $user->id,
            'role' => $validated['role'],
            'matricule' => $validated['matricule'] ?? null,
            'biographie' => $validated['biographie'] ?? null,
        ]);

        $admin->load('user');

        return response()->json($admin, 201);
    }

    public function toggleActive(User $user): JsonResponse
    {
        $user->is_active = !$user->is_active;
        $user->save();

        return response()->json($user, 200);
    }

    public function dashboard(): JsonResponse
    {
        $data = [
            'total_patients' => Patient::count(),
            'total_medecins' => Admin::where('role', 'medecin')->count(),
            'rdv_aujourd_hui' => RendezVous::whereDate('date_heure', now()->toDateString())->count(),
            'rdv_en_attente' => RendezVous::where('statut', 'en_attente')->count(),
            'consultations_mois' => Consultation::whereYear('date', now()->year)
                ->whereMonth('date', now()->month)
                ->count(),
            'analyses_en_attente' => Analyse::whereNull('date_resultat')->count(),
            'secretary_requests_en_attente' => SecretaryMedecin::where('statut', 'en_attente')->count(),
        ];

        return response()->json($data, 200);
    }

    public function listMedecins(): JsonResponse
    {
        $medecins = Admin::where('role', 'medecin')
            ->with(['user.cabinet', 'disponibilites'])
            ->get()
            ->map(function ($medecin) {
                return [
                    'id' => $medecin->id,
                    'nom' => $medecin->user->nom,
                    'prenom' => $medecin->user->prenom,
                    'email' => $medecin->user->email,
                    'telephone' => $medecin->user->telephone,
                    'photo_profil' => $medecin->user->photo_profil,
                    'matricule' => $medecin->matricule,
                    'biographie' => $medecin->biographie,
                    'specialite' => $medecin->user->cabinet?->specialite,
                    'cabinet_nom' => $medecin->user->cabinet?->nom,
                    'cabinet_ville' => $medecin->user->cabinet?->ville,
                ];
            });

        return response()->json($medecins, 200);
    }

    public function secretaryRequests(Request $request): JsonResponse
    {
        $medecin = auth('api')->user();

        $query = SecretaryMedecin::with(['secretary.cabinet'])
            ->where('medecin_id', $medecin->id);

        if ($status = $request->query('statut')) {
            if ($status !== 'tous') {
                $query->where('statut', $status);
            }
        }

        $requests = $query->orderByDesc('date_demande')->get()->map(function (SecretaryMedecin $request) {
            return [
                'id' => $request->id,
                'secretary_id' => $request->secretary_id,
                'secretary_nom' => $request->secretary?->nom,
                'secretary_prenom' => $request->secretary?->prenom,
                'secretary_email' => $request->secretary?->email,
                'secretary_telephone' => $request->secretary?->telephone,
                'cabinet_id' => $request->secretary?->cabinet_id,
                'cabinet_nom' => $request->secretary?->cabinet?->nom,
                'cabinet_ville' => $request->secretary?->cabinet?->ville,
                'statut' => $request->statut,
                'date_demande' => $request->date_demande,
                'date_decision' => $request->date_decision,
                'motif_refus' => $request->motif_refus,
            ];
        });

        $counts = SecretaryMedecin::where('medecin_id', $medecin->id)
            ->selectRaw('statut, count(*) as total')
            ->groupBy('statut')
            ->pluck('total', 'statut');

        return response()->json([
            'requests' => $requests,
            'counts' => [
                'en_attente' => $counts['en_attente'] ?? 0,
                'approuvee' => $counts['approuvee'] ?? 0,
                'refusee' => $counts['refusee'] ?? 0,
            ],
        ], 200);
    }

    public function updateSecretaryRequest(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'statut' => 'required|in:approuvee,refusee',
            'motif_refus' => 'nullable|string|max:1000',
        ]);

        $secretaryRequest = SecretaryMedecin::with('secretary.admin')
            ->find($id)
            ?? SecretaryMedecin::with('secretary.admin')->where('secretary_id', $id)->first();

        if (!$secretaryRequest) {
            return response()->json(['message' => 'Demande introuvable.'], 404);
        }

        if (!$secretaryRequest->secretary || !$secretaryRequest->secretary->isSecretaire()) {
            return response()->json(['message' => 'Utilisateur non autorisé.'], 403);
        }

        $medecin = auth('api')->user();
        if ($secretaryRequest->medecin_id !== $medecin->id) {
            return response()->json(['message' => "Vous n'êtes pas autorisé à gérer cette demande."], 403);
        }

        $secretaryRequest->statut = $validated['statut'];
        $secretaryRequest->date_decision = now();
        $secretaryRequest->motif_refus = $validated['statut'] === 'refusee'
            ? $validated['motif_refus']
            : null;
        $secretaryRequest->save();

        // Send email notification (wrapped in try-catch so mail failure never breaks the response)
        $emailSent = false;
        try {
            if ($validated['statut'] === 'approuvee') {
                Mail::to($secretaryRequest->secretary->email)->send(
                    new SecretaryApprovedMail($secretaryRequest->secretary, $medecin)
                );
            } elseif ($validated['statut'] === 'refusee') {
                Mail::to($secretaryRequest->secretary->email)->send(
                    new SecretaryRefusedMail(
                        $secretaryRequest->secretary,
                        $medecin,
                        $secretaryRequest->motif_refus
                    )
                );
            }
            $emailSent = true;
        } catch (\Exception $e) {
            Log::error('Failed to send secretary notification email: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Statut de la demande mis à jour.',
            'email_sent' => $emailSent,
            'request' => [
                'id' => $secretaryRequest->id,
                'secretary_id' => $secretaryRequest->secretary_id,
                'medecin_id' => $secretaryRequest->medecin_id,
                'statut' => $secretaryRequest->statut,
                'date_decision' => $secretaryRequest->date_decision,
                'motif_refus' => $secretaryRequest->motif_refus,
            ],
        ], 200);
    }

    public function approveSecretaryRequest($id): JsonResponse
    {
        $secretaryRequest = SecretaryMedecin::with('secretary.admin')->find($id);

        if (!$secretaryRequest) {
            return response()->json(['message' => 'Demande introuvable.'], 404);
        }

        if (!$secretaryRequest->secretary || !$secretaryRequest->secretary->isSecretaire()) {
            return response()->json(['message' => 'Utilisateur non autorisé.'], 403);
        }

        $medecin = auth('api')->user();
        if ($secretaryRequest->medecin_id !== $medecin->id) {
            return response()->json(['message' => "Vous n'êtes pas autorisé à gérer cette demande."], 403);
        }

        $secretaryRequest->statut = 'approuvee';
        $secretaryRequest->date_decision = now();
        $secretaryRequest->motif_refus = null;
        $secretaryRequest->save();

        // Send approval email (wrapped in try-catch so mail failure never breaks the response)
        $emailSent = false;
        try {
            Mail::to($secretaryRequest->secretary->email)->send(
                new SecretaryApprovedMail($secretaryRequest->secretary, $medecin)
            );
            $emailSent = true;
        } catch (\Exception $e) {
            Log::error('Failed to send secretary approval email: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Secrétaire approuvée avec succès.',
            'email_sent' => $emailSent,
            'request' => $secretaryRequest,
        ], 200);
    }

    public function refuseSecretaryRequest(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'motif_refus' => 'nullable|string|max:1000',
        ]);

        $secretaryRequest = SecretaryMedecin::with('secretary.admin')->find($id);

        if (!$secretaryRequest) {
            return response()->json(['message' => 'Demande introuvable.'], 404);
        }

        if (!$secretaryRequest->secretary || !$secretaryRequest->secretary->isSecretaire()) {
            return response()->json(['message' => 'Utilisateur non autorisé.'], 403);
        }

        $medecin = auth('api')->user();
        if ($secretaryRequest->medecin_id !== $medecin->id) {
            return response()->json(['message' => "Vous n'êtes pas autorisé à gérer cette demande."], 403);
        }

        $secretaryRequest->statut = 'refusee';
        $secretaryRequest->date_decision = now();
        $secretaryRequest->motif_refus = $validated['motif_refus'] ?? null;
        $secretaryRequest->save();

        // Send refusal email (wrapped in try-catch so mail failure never breaks the response)
        $emailSent = false;
        try {
            Mail::to($secretaryRequest->secretary->email)->send(
                new SecretaryRefusedMail(
                    $secretaryRequest->secretary,
                    $medecin,
                    $secretaryRequest->motif_refus
                )
            );
            $emailSent = true;
        } catch (\Exception $e) {
            Log::error('Failed to send secretary refusal email: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Secrétaire refusée avec succès.',
            'email_sent' => $emailSent,
            'request' => $secretaryRequest,
        ], 200);
    }
}
