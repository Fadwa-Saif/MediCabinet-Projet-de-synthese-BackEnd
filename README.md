# 🏥 MediCabinet Backend API

**Laravel 11 Medical Cabinet Management System**

> A comprehensive backend API for managing medical practices with patient-doctor assignments, appointments, consultations, and medical records.

---

## 📋 Table of Contents

1. [Quick Start](#quick-start)
2. [Recent Features](#recent-features)
3. [API Documentation](#api-documentation)
4. [Project Structure](#project-structure)
5. [Development Guide](#development-guide)

---

## 🚀 Quick Start

### Prerequisites
- PHP 8.2+
- Composer
- MySQL/MariaDB
- JWT (tymon/jwt-auth configured)

### Installation

```bash
# 1. Install dependencies
composer install

# 2. Copy environment file
cp .env.example .env

# 3. Generate application key
php artisan key:generate

# 4. Configure database in .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_DATABASE=medicabinet
# DB_USERNAME=root
# DB_PASSWORD=

# 5. Run migrations
php artisan migrate

# 6. Seed sample data (optional)
php artisan db:seed

# 7. Start development server
php artisan serve

# API will be available at http://127.0.0.1:8000/api
```

---

## ✨ Recent Features (2026-06-07)

### Patient-Doctor Assignment System

A new system for automatic patient assignment to doctors with role-based visibility control:

#### **Features Implemented**:
- ✅ Automatic patient assignment when booking appointments
- ✅ Pivot table `patient_medecin` for many-to-many relationships
- ✅ Role-based patient filtering (medecin, secretaire, patient)
- ✅ PatientPolicy for fine-grained authorization
- ✅ New endpoints: `GET /api/medecins`, `GET /api/mes-patients`

#### **New Endpoints**:
```
GET  /api/medecins              # List available doctors
GET  /api/mes-patients          # Doctor's assigned patients
POST /api/rendezvous            # Book appointment (auto-assigns)
PATCH /api/rendezvous/{id}       # Patient: update own pending appointment
```

#### **Modified Endpoints**:
```
GET  /api/patients              # Now filtered by role
```

**→ See [PATIENT_DOCTOR_ASSIGNMENT_README.md](../PATIENT_DOCTOR_ASSIGNMENT_README.md) for detailed documentation**

---

## 🔌 API Documentation

### Authentication
All endpoints (except public routes) require JWT token:

```bash
# Login
POST /api/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}

# Response
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "bearer",
  "user": { ... }
}

# Use token in requests
Authorization: Bearer {token}
```

### Core Endpoints

#### **Appointments** (`/api/rendezvous`)
```
GET    /api/rendezvous                     # List appointments
POST   /api/rendezvous                     # Create appointment
GET    /api/rendezvous/{id}                # Get appointment details
PATCH  /api/rendezvous/{id}                # Update appointment
PATCH  /api/rendezvous/{id}/annuler        # Cancel appointment
PATCH  /api/rendezvous/{id}/reprendre      # Resume appointment
GET    /api/rendezvous/creneaux            # Get available time slots
```

#### **Patients** (`/api/patients`)
```
GET    /api/patients                       # List patients (filtered by role)
POST   /api/patients                       # Create patient
GET    /api/patients/{id}                  # Get patient details
PATCH  /api/patients/{id}                  # Update patient
DELETE /api/patients/{id}                  # Delete patient
GET    /api/mes-patients                   # Doctor: list assigned patients
```

#### **Consultations** (`/api/consultations`)
```
GET    /api/consultations                  # List consultations
POST   /api/consultations                  # Create consultation
GET    /api/consultations/{id}             # Get consultation details
PATCH  /api/consultations/{id}             # Update consultation
GET    /api/patients/{id}/historique       # Patient consultation history
```

#### **Doctors** (`/api/medecins`)
```
GET    /api/medecins                       # List available doctors
```

**→ See [docs/MediCabinet-API.postman_collection.json](docs/MediCabinet-API.postman_collection.json) for complete API specification**

---

## 📁 Project Structure

```
app/
├── Http/
│   ├── Controllers/Api/
│   │   ├── AppointmentController.php
│   │   ├── AdminController.php           (NEW: listMedecins)
│   │   ├── PatientController.php         (MODIFIED: mesPatients)
│   │   ├── ConsultationController.php
│   │   ├── RendezVousController.php      (MODIFIED: auto-assignment)
│   │   └── ...
│   ├── Middleware/
│   │   └── RoleMiddleware.php            (Role-based access control)
│   └── Resources/
├── Models/
│   ├── User.php
│   ├── Patient.php                       (MODIFIED: medecins relation)
│   ├── Admin.php                         (MODIFIED: patients relation)
│   ├── RendezVous.php
│   ├── Consultation.php
│   └── ...
├── Policies/
│   └── PatientPolicy.php                 (NEW: Authorization rules)
└── Providers/
    ├── AppServiceProvider.php
    └── AuthServiceProvider.php           (NEW: Policy registration)

database/
├── migrations/
│   ├── ...
│   └── 2026_06_07_000019_create_patient_medecin_table.php (NEW)
└── seeders/
    └── DatabaseSeeder.php

routes/
├── api.php                               (MODIFIED: New routes & groups)
├── web.php
└── console.php

config/
├── auth.php                              (JWT configured)
├── database.php
└── ...
```

---

## 👥 User Roles & Permissions

### Roles
- **patient**: Booke appointments, view own medical records
- **medecin**: Manage appointments, consultations, patients, prescriptions
- **secretaire**: Create patients, manage appointments (for cabinet)
- **admin**: System administration (implied medecin role)

### Access Control
- **PatientPolicy**: Enforces who can view/edit patient records
- **RoleMiddleware**: Validates role requirements on routes
- **Gate policies**: Model-level authorization

---

## 🧪 Development Guide

### Running Tests
```bash
php artisan test
```

### Database Migrations
```bash
# Run all pending migrations
php artisan migrate

# Rollback last batch
php artisan migrate:rollback

# Create new migration
php artisan make:migration create_table_name_table
```

### Tinker (Interactive Shell)
```bash
php artisan tinker

# Example: Create a test patient
>>> $user = User::factory()->create();
>>> $patient = Patient::create(['user_id' => $user->id, ...]);
```

### Common Commands
```bash
# Seed database
php artisan db:seed

# Clear cache
php artisan cache:clear

# Refresh migrations + seed
php artisan migrate:fresh --seed

# Generate JWT secret
php artisan jwt:secret
```

---

## 🔐 Security Considerations

✅ **JWT Authentication**: All routes protected with JWT tokens
✅ **Role-Based Access Control**: RoleMiddleware validates user roles
✅ **Model Policies**: PatientPolicy enforces fine-grained permissions
✅ **Input Validation**: All requests validated with FormRequest
✅ **CORS Configured**: `config/cors.php` allows frontend communication
✅ **Password Hashing**: Passwords hashed with bcrypt
✅ **Authorization**: Gate policies at model level

### CORS Configuration
Frontend should be added to allowed origins in `config/cors.php`:
```php
'allowed_origins' => [
    'http://localhost:3001',
    'http://127.0.0.1:3001',
    'https://medicabinet-frontend.domain.com',
],
```

---

## 📊 Database Schema Highlights

### Key Tables
- `users`: User accounts with cabinet association
- `admins`: Doctor/Secretary designation
- `patients`: Medical patient records
- `patient_medecin`: **Patient-Doctor assignments** (pivot table) ← NEW
- `rendezvous`: Appointments
- `consultations`: Medical visits
- `cabinets`: Medical practices
- `disponibilites`: Doctor working hours
- `ordonnances`: Prescriptions
- `medicaments`: Medicine catalog
- `analyses`: Medical tests/analyses

### Recent Schema Changes (2026-06-07)
```sql
-- NEW: Pivot table for patient-doctor assignments
CREATE TABLE patient_medecin (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    patient_id BIGINT NOT NULL,
    medecin_id BIGINT NOT NULL,
    date_affectation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('actif', 'inactif') DEFAULT 'actif',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (medecin_id) REFERENCES admins(id) ON DELETE CASCADE,
    UNIQUE KEY unique_assignment (patient_id, medecin_id)
);
```

---

## 📝 Configuration Files

### `.env` (Important Settings)
```env
# App
APP_NAME="MediCabinet"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=medicabinet
DB_USERNAME=root
DB_PASSWORD=

# JWT
JWT_SECRET=<generated-by-jwt:secret>
JWT_ALGORITHM=HS256

# CORS Frontend
APP_FRONTEND_URL=http://localhost:3001
```

---

## 🐛 Troubleshooting

### PHP Extensions
If Composer fails with `ext-sodium` error:
- Edit `C:\xampp\php\php.ini` and enable: `extension=php_sodium.dll`
- Restart PHP/Apache

### JWT Token Issues
```bash
# Regenerate JWT secret
php artisan jwt:secret

# Clear application cache
php artisan cache:clear
php artisan config:clear
```

### Database Connection
```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();  # Should return PDO object
```

---

## 📚 Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Eloquent ORM Guide](https://laravel.com/docs/eloquent)
- [JWT Auth Package](https://jwt-auth.readthedocs.io/)
- [Project Analysis Report](../Rapport-Analyse-MediCabinet.md)

---

## 👥 Team & Contributing

**Last Updated**: 2026-06-07
**Version**: 2.1.0
**Status**: 🟢 Active Development

### Recent Changes
- ✅ Added Patient-Doctor Assignment System (2026-06-07)
- ✅ Implemented PatientPolicy for authorization
- ✅ Added role-based patient filtering
- ✅ Auto-assignment on appointment creation

---

**For Frontend Setup**: See [../MediCabinet-Projet-de-synthese-FrontEnd/README.md](../MediCabinet-Projet-de-synthese-FrontEnd/README.md)

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
