<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Demande refusée - MediCabinet</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f6f9;font-family:Arial,Helvetica,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f9;padding:40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                    <tr>
                        <td style="background-color:#ef4444;padding:24px;text-align:center;">
                            <h1 style="color:#ffffff;margin:0;font-size:22px;">❌ Demande refusée</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="font-size:16px;color:#1e293b;">Bonjour <strong>{{ $secretary->prenom }} {{ $secretary->nom }}</strong>,</p>

                            <p style="font-size:15px;color:#334155;line-height:1.6;">
                                Nous vous remercions d'avoir sollicité l'accès en tant que <strong>secrétaire</strong>
                                auprès du docteur <strong>{{ $medecin->prenom }} {{ $medecin->nom }}</strong>.
                            </p>

                            <p style="font-size:15px;color:#334155;line-height:1.6;">
                                Malheureusement, votre demande a été <strong style="color:#ef4444;">refusée</strong>.
                            </p>

                            @if($reason)
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#fef2f2;border-left:4px solid #ef4444;border-radius:4px;margin:16px 0;">
                                <tr>
                                    <td style="padding:12px 16px;">
                                        <p style="font-size:13px;color:#991b1b;margin:0 0 4px 0;font-weight:bold;">Motif du refus :</p>
                                        <p style="font-size:14px;color:#991b1b;margin:0;">{{ $reason }}</p>
                                    </td>
                                </tr>
                            </table>
                            @endif

                            <p style="font-size:15px;color:#334155;line-height:1.6;">
                                Pour plus d'informations ou si vous souhaitez contester cette décision, veuillez contacter directement :
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8fafc;border-radius:6px;padding:16px;margin-top:8px;">
                                <tr><td style="padding:6px 12px;font-size:14px;color:#64748b;">Médecin</td><td style="padding:6px 12px;font-size:14px;color:#1e293b;font-weight:bold;">Dr. {{ $medecin->prenom }} {{ $medecin->nom }}</td></tr>
                                <tr><td style="padding:6px 12px;font-size:14px;color:#64748b;">Email</td><td style="padding:6px 12px;font-size:14px;color:#1e293b;">{{ $medecin->email }}</td></tr>
                            </table>

                            <table cellpadding="0" cellspacing="0" style="margin:24px 0;">
                                <tr>
                                    <td style="background-color:#ef4444;border-radius:6px;padding:12px 28px;">
                                        <a href="{{ $contactUrl }}" style="color:#ffffff;text-decoration:none;font-size:15px;font-weight:bold;">
                                            Contacter le support
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#f8fafc;padding:16px;text-align:center;font-size:12px;color:#94a3b8;">
                            &copy; {{ date('Y') }} MediCabinet. Tous droits réservés.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
