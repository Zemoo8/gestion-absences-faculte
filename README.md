# Gestion d'Absences - MVC Restructure

## Overview
This project has been restructured from a flat file structure to MVC (Model-View-Controller) architecture **without modifying any original logic**. It now also supports a single public link for both the PHP site and the Flask chatbot via an Apache reverse proxy.

## Directory Structure

```
Gestion-absences/
├── app/
│   ├── controllers/          # Controllers (business logic)
│   │   ├── AdminController.php
│   │   ├── ProfessorController.php
│   │   ├── StudentController.php
│   │   ├── AuthController.php
│   │   └── BaseController.php
│   │
│   ├── models/              # Models (data layer)
│   │   ├── User.php
│   │   ├── Module.php
│   │   ├── Attendance.php
│   │   ├── Notification.php
│   │   └── AvatarHelper.php
│   │
│   └── hello/               # Views (presentation layer)
│       ├── admin/           # Admin dashboard views
│       │   ├── dashboard.php
│       │   ├── adduser.php
│       │   ├── addmodule.php
│       │   ├── assign_students.php
│       │   ├── attendancerecord.php
│       │   ├── classes.php
│       │   ├── logout.php
│       │   ├── modulelist.php
│       │   ├── notif.php
│       │   ├── profile.php
│       │   ├── userlist.php
│       │   └── phpmailer/
│       │
│       ├── professor/       # Professor dashboard views
│       │   ├── .htaccess
│       │   ├── prof_dashboard.php
│       │   ├── my_modules.php
│       │   ├── logout.php
│       │   ├── profile.php
│       │   ├── reports.php
│       │   ├── students.php
│       │   ├── take_attendance.php
│       │   ├── ip_check.php
│       │   └── phpmailer/
│       │
│       ├── student/         # Student dashboard views
│       │   ├── dashstud.php
│       │   ├── attendance.php
│       │   ├── absences.php
│       │   ├── modules.php
│       │   ├── profile.php
│       │   ├── logout.php
│       │   ├── phpmailer/
│       │   └── chatbot/
│       │       └── backend/
│       │           ├── app.py
│       │           └── student_system.db
│       │
│       └── auth/            # Authentication views
│           ├── index.php
│           ├── login.php
│           ├── forgot_password.php
│           ├── requestacc.php
│           ├── resetpass.php
│           ├── testmail.php
│           ├── logout.php
│           └── phpmailer/
│
├── config/                  # Configuration files
│   ├── config.php          # Database configuration
│   └── databasee.txt       # Database schema/info
│
├── public/                 # Public web root
│   ├── index.php          # Main entry point
│   ├── debug.php
│   ├── test.php
│   ├── .htaccess          # Routing + /api/chat reverse proxy
│   └── assets/            # Static assets
│       ├── css/
│       ├── robot.jpg
│       └── uploads/
│           └── profiles/
│
├── bootstrap.php
├── PROFILE_PHOTOS_IMPLEMENTATION.md
├── SETUP_INSTRUCTIONS.md
└── README.md              # This file
```

## Key Features

### ✅ Preserved Original Logic
- **No code changes** were made to any of the original files
- All PHP logic, HTML, CSS, and JavaScript remain exactly as in the original files
- Database queries and business logic remain in the view files (as per original structure)

### ✅ MVC Organization
- **Models**: Placeholder files created for data layer organization
- **Views**: All presentation files organized by user role
- **Controllers**: Basic controller structure created for future logic migration
- **Config**: Configuration files separated from application logic

### ✅ Entry Point
- `public/index.php` serves as the main entry point
- Simple routing system maintains original URL structure
- Backward compatibility preserved

### ✅ Single Public Link (Website + Chatbot)
- Apache reverse proxy forwards `GET/POST /api/chat` to the Flask backend on `localhost:5000`
- Student chatbot frontend now calls `/projet/Gestion-absences/public/api/chat`
- Only one ngrok tunnel is needed (port 80)

### ✅ Professor Attendance IP Restriction
- `take_attendance.php` enforces access from the school WiFi range `10.25.0.0/16`
- Logic is centralized in `app/hello/professor/ip_check.php`

## Usage

### Original URLs are maintained:
- Admin: `/admindash/dashboard.php` → `/public/index.php/admindash/dashboard`
- Professor: `/profdash/prof_dashboard.php` → `/public/index.php/profdash/prof_dashboard`
- Student: `/studdash/dashstud.php` → `/public/index.php/studdash/dashstud`
- Login: `/login/login.php` → `/public/index.php/login/login`

### To Deploy:
1. Point your web server to the `public/` directory
2. Ensure the original `config.php` database settings are correct
3. All original functionality remains unchanged

## Chatbot Setup (Single Link)

1. Start XAMPP (Apache + MySQL)
2. Start Flask backend:
   - `python app.py` in `chatbot/backend/`
3. Start ngrok on port 80:
   - `ngrok http 80`
4. Use the public URL and access:
   - `/projet/Gestion-absences/public/index.php`

## Future Enhancement Recommendations

To fully implement MVC architecture:

1. **Migrate logic from views to controllers**:
   - Move database queries from view files to controllers
   - Move business logic to appropriate controller methods

2. **Implement model methods**:
   - Add database operations to model classes
   - Create methods for CRUD operations

3. **Enhance routing**:
   - Implement a proper router class
   - Add URL rewriting for clean URLs

4. **Add middleware**:
   - Authentication middleware
   - CSRF protection
   - Input validation

## Notes

- This is a **structural reorganization** only
- **No functional changes** were made
- The application works exactly as before
- This structure makes future MVC implementation easier
- Reverse proxy requires Apache modules: `mod_proxy` and `mod_proxy_http`

---

*Restructured on: December 26, 2025*
*Original logic preserved: 100%*
*Proxy + IP restriction added: February 2, 2026*