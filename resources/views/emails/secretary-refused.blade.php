<x-mail::message>
# ❌ Décision concernant votre demande d'accès

Bonjour {{ $secretary->prenom }} {{ $secretary->nom }},

Nous vous remercions d'avoir sollicité l'accès en tant que **secrétaire** auprès du docteur {{ $medecin->prenom }} {{ $medecin->nom }}.

Malheureusement, votre demande a été **refusée**.

@if($reason)
**Motif du refus:**  
{{ $reason }}
@endif

Pour plus d'informations ou si vous souhaitez contester cette décision, veuillez contacter directement:
- **Médecin**: Dr. {{ $medecin->prenom }} {{ $medecin->nom }}
- **Email**: {{ $medecin->email }}

<x-mail::button :url="$contactUrl" color="danger">
Contacter le support
</x-mail::button>

Cordialement,  
L'équipe MediCabinet
</x-mail::message>
