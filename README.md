#  MediCabinet — Backend API

REST API for managing a medical cabinet: patients, doctors, secretaries, appointments, consultations, prescriptions and lab analyses. Built with **Laravel 11**, secured with **JWT** and **role-based access control**.

Frontend repository: [MediCabinet-Projet-de-synthese-FrontEnd](https://github.com/Fadwa-Saif/MediCabinet-Projet-de-synthese-FrontEnd)


---

##  Features

- **JWT authentication** (`tymon/jwt-auth`): login, protected routes, bearer tokens
- **Role-based access control** with 4 roles: patient, médecin (doctor), secrétaire, admin
- **Appointments**: booking, update, cancel / resume, available time slots
- **Patient–doctor assignment**: a patient is automatically assigned to a doctor when booking, and doctors only see their own patients
- **Consultations** and per-patient consultation history
- **Fine-grained authorization** with `RoleMiddleware` and `PatientPolicy`
- **Data model** covering cabinets, doctor availability, prescriptions, medicines and lab analyses
- **Docker** support (`Dockerfile` included)
- **Postman collection** in `docs/` for the full API

##  Tech stack

| | |
|---|---|
| Framework | Laravel 11 (PHP 8.2+) |
| Database | MySQL / MariaDB |
| Auth | JWT (`tymon/jwt-auth`) |
| Testing | PHPUnit (`php artisan test`) |
| DevOps | Docker |

##  Getting started

**Prerequisites:** PHP 8.2+, Composer, MySQL/MariaDB

```bash
# 1. Clone and install dependencies
git clone https://github.com/Fadwa-Saif/MediCabinet-Projet-de-synthese-BackEnd.git
cd MediCabinet-Projet-de-synthese-BackEnd
composer install

# 2. Environment
cp .env.example .env
php artisan key:generate
php artisan jwt:secret

# 3. Configure your database in .env
#    DB_DATABASE=medicabinet
#    DB_USERNAME=root
#    DB_PASSWORD=

# 4. Create tables (and optional sample data)
php artisan migrate
php artisan db:seed

# 5. Run the API
php artisan serve
# → http://127.0.0.1:8000/api
```

### Connecting the frontend

Add the frontend's origin to `allowed_origins` in `config/cors.php`. For local development this is the address where the React app runs (for example `http://localhost:3000`).

##  API overview

All routes except login/register require the header `Authorization: Bearer <token>`.

| Resource | Endpoints |
|---|---|
| **Auth** | `POST /api/auth/login` |
| **Appointments** | `GET/POST /api/rendezvous`, `GET/PATCH /api/rendezvous/{id}`, `PATCH /api/rendezvous/{id}/annuler`, `PATCH /api/rendezvous/{id}/reprendre`, `GET /api/rendezvous/creneaux` |
| **Patients** | `GET/POST /api/patients`, `GET/PATCH/DELETE /api/patients/{id}`, `GET /api/mes-patients`, `GET /api/patients/{id}/historique` |
| **Consultations** | `GET/POST /api/consultations`, `GET/PATCH /api/consultations/{id}` |
| **Doctors** | `GET /api/medecins` |

Example login:

```http
POST /api/auth/login
Content-Type: application/json

{ "email": "user@example.com", "password": "password" }
```

The full specification is in [`docs/MediCabinet-API.postman_collection.json`](docs/MediCabinet-API.postman_collection.json): import it into Postman.

##  Roles & permissions

| Role | Can do |
|---|---|
| **patient** | Book appointments, update own pending appointments, view own medical records |
| **médecin** | Manage appointments, consultations and prescriptions, view assigned patients |
| **secrétaire** | Create patients and manage appointments for the cabinet |
| **admin** | System administration |

## 📁 Project structure

```
app/
├── Http/
│   ├── Controllers/Api/   # Appointment, Patient, Consultation, Admin controllers...
│   └── Middleware/        # RoleMiddleware
├── Models/                # User, Patient, Admin, RendezVous, Consultation...
└── Policies/              # PatientPolicy
database/
├── migrations/
└── seeders/
routes/api.php             # API routes
docs/                      # Postman collection
```

##  Tests

```bash
php artisan test
```

## 🎓 About

Built by [Fadwa Saif](https://github.com/Fadwa-Saif) as part of the full-stack web development program at OFPPT/ISGI (projet de synthèse).
Portfolio: [fadwasaif.vercel.app](https://fadwasaif.vercel.app/) · [LinkedIn](https://www.linkedin.com/in/fadwa-saif-a7280922b)
