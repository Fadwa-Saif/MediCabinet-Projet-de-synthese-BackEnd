<x-mail::message>
# ✅ Votre demande d'accès a été approuvée

Bonjour {{ $secretary->prenom }} {{ $secretary->nom }},

Bonne nouvelle! Votre demande d'accès en tant que **secrétaire** a été **approuvée** par le docteur {{ $medecin->prenom }} {{ $medecin->nom }}.

Vous pouvez maintenant vous connecter à MediCabinet et accéder à votre espace secrétaire.

<x-mail::button :url="$loginUrl">
Se connecter à MediCabinet
</x-mail::button>

## Détails
- **Médecin responsable**: Dr. {{ $medecin->prenom }} {{ $medecin->nom }}
- **Email du médecin**: {{ $medecin->email }}
- **Date d'approbation**: {{ now()->locale('fr')->translatedFormat('d F Y à H:i') }}

Si vous rencontrez des problèmes lors de la connexion, veuillez contacter le médecin responsable.

Cordialement,  
L'équipe MediCabinet
</x-mail::message>
