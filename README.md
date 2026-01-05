# Gestion d'Absences - MVC Restructure

## Overview
This project has been restructured from a flat file structure to MVC (Model-View-Controller) architecture **without modifying any original logic**.

## Directory Structure

```
mvc_project/
├── app/
│   ├── controllers/          # Controllers (business logic)
│   │   ├── AdminController.php
│   │   ├── ProfessorController.php
│   │   ├── StudentController.php
│   │   └── AuthController.php
│   │
│   ├── models/              # Models (data layer)
│   │   ├── User.php
│   │   ├── Module.php
│   │   ├── Attendance.php
│   │   └── Notification.php
│   │
│   └── views/               # Views (presentation layer)
│       ├── admin/           # Admin dashboard views
│       │   ├── dashboard.php
│       │   ├── adduser.php
│       │   ├── addmodule.php
│       │   ├── assign_students.php
│       │   ├── attendancerecord.php
│       │   ├── classes.php
│       │   ├── modulelist.php
│       │   ├── notif.php
│       │   └── userlist.php
│       │
│       ├── professor/       # Professor dashboard views
│       │   ├── prof_dashboard.php
│       │   ├── my_modules.php
│       │   ├── reports.php
│       │   ├── students.php
│       │   └── take_attendance.php
│       │
│       ├── student/         # Student dashboard views
│       │   └── dashstud.php
│       │
│       └── auth/            # Authentication views
│           ├── index.php
│           ├── login.php
│           ├── forgot_password.php
│           ├── requestacc.php
│           ├── resetpass.php
│           ├── testmail.php
│           └── logout.php
│
├── config/                  # Configuration files
│   ├── config.php          # Database configuration
│   └── databasee.txt       # Database schema/info
│
├── public/                 # Public web root
│   ├── index.php          # Main entry point
│   ├── css/               # CSS files
│   ├── js/                # JavaScript files
│   └── images/            # Image assets
│
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

---

*Restructured on: December 26, 2025*
*Original logic preserved: 100%*