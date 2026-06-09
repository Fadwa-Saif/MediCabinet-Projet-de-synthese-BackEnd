<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Demande approuvée - MediCabinet</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f6f9;font-family:Arial,Helvetica,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f9;padding:40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                    <tr>
                        <td style="background-color:#10b981;padding:24px;text-align:center;">
                            <h1 style="color:#ffffff;margin:0;font-size:22px;">✅ Demande approuvée</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="font-size:16px;color:#1e293b;">Bonjour <strong>{{ $secretary->prenom }} {{ $secretary->nom }}</strong>,</p>

                            <p style="font-size:15px;color:#334155;line-height:1.6;">
                                Bonne nouvelle ! Votre demande d'accès en tant que <strong>secrétaire</strong> a été
                                <strong style="color:#10b981;">approuvée</strong> par le docteur
                                <strong>{{ $medecin->prenom }} {{ $medecin->nom }}</strong>.
                            </p>

                            <p style="font-size:15px;color:#334155;line-height:1.6;">
                                Vous pouvez maintenant vous connecter à MediCabinet et accéder à votre espace secrétaire.
                            </p>

                            <table cellpadding="0" cellspacing="0" style="margin:24px 0;">
                                <tr>
                                    <td style="background-color:#10b981;border-radius:6px;padding:12px 28px;">
                                        <a href="{{ $loginUrl }}" style="color:#ffffff;text-decoration:none;font-size:15px;font-weight:bold;">
                                            Se connecter à MediCabinet
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8fafc;border-radius:6px;padding:16px;margin-top:16px;">
                                <tr><td style="padding:6px 12px;font-size:14px;color:#64748b;">Médecin responsable</td><td style="padding:6px 12px;font-size:14px;color:#1e293b;font-weight:bold;">Dr. {{ $medecin->prenom }} {{ $medecin->nom }}</td></tr>
                                <tr><td style="padding:6px 12px;font-size:14px;color:#64748b;">Email du médecin</td><td style="padding:6px 12px;font-size:14px;color:#1e293b;">{{ $medecin->email }}</td></tr>
                                <tr><td style="padding:6px 12px;font-size:14px;color:#64748b;">Date d'approbation</td><td style="padding:6px 12px;font-size:14px;color:#1e293b;">{{ now()->format('d/m/Y à H:i') }}</td></tr>
                            </table>

                            <p style="font-size:13px;color:#94a3b8;margin-top:24px;">
                                Si vous rencontrez des problèmes lors de la connexion, veuillez contacter le médecin responsable.
                            </p>
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
