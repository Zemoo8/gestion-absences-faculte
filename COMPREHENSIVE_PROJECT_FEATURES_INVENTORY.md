# GESTION D'ABSENCES - COMPREHENSIVE PROJECT FEATURES INVENTORY

**Project**: Attendance Management System (Gestion d'Absences)  
**Architecture**: MVC-based PHP Application with Flask Chatbot Integration  
**Roles**: Admin, Professor, Student  
**Date Scanned**: February 3, 2026  

---

## TABLE OF CONTENTS
1. [SYSTEM OVERVIEW](#system-overview)
2. [AUTHENTICATION SYSTEM](#authentication-system)
3. [ADMIN FEATURES](#admin-features)
4. [PROFESSOR FEATURES](#professor-features)
5. [STUDENT FEATURES](#student-features)
6. [AI CHATBOT FEATURES](#ai-chatbot-features)
7. [COMMON FEATURES (ALL USERS)](#common-features-all-users)
8. [DATABASE MODELS](#database-models)
9. [TECHNICAL FEATURES](#technical-features)
10. [FEATURE CLASSIFICATION MATRIX](#feature-classification-matrix)

---

## SYSTEM OVERVIEW

### Project Description
A comprehensive web-based attendance management system for educational institutions. The system manages student attendance across multiple modules, with role-based dashboards for administrators, professors, and students. Integration with an AI chatbot provides intelligent attendance queries.

### Key Statistics
- **3 Primary User Roles**: Admin, Professor, Student
- **Total Pages**: 25+ view files
- **Database Tables**: Users, Modules, Attendance, Notifications, Account Requests, Module Schedule, Classes, Reminder Log
- **Backend Technologies**: PHP, MySQL, Python Flask, OpenAI API
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Additional Services**: PHPMailer (SMTP), Python Chatbot, Apache Reverse Proxy

---

## AUTHENTICATION SYSTEM
**Files**: `app/hello/auth/` directory  
**Controller**: `app/controllers/AuthController.php`

### Feature 1: User Login
- **File**: `app/hello/auth/login.php`
- **Description**: Secure login interface for all user roles (Admin, Professor, Student)
- **Functionality**:
  - Email and password verification
  - Role-based session initialization
  - Remember me functionality
  - Error handling and validation
  - Responsive dark/light theme toggle
  - Role indicator badges
- **Database Interaction**: Queries `users` table with email/password validation
- **Security**: Session management, input sanitization
- **UI Elements**: Login form, feature showcase cards, role selection hints

### Feature 2: Account Request/Registration
- **File**: `app/hello/auth/requestacc.php`
- **Description**: User registration flow for new students requesting accounts
- **Functionality**:
  - Name, email, and password input form
  - **Profile photo upload** with drag-and-drop support
  - Photo preview before submission
  - File validation (max 5MB, JPG/PNG/GIF/WebP)
  - Submission to account_requests table
  - Email confirmation capability
- **Database Interaction**: Inserts into `account_requests` table with `photo_path`
- **File Storage**: Uploads to `public/assets/uploads/profiles/`
- **Related Model**: `app/models/AvatarHelper.php`

### Feature 3: Password Reset
- **File**: `app/hello/auth/resetpass.php`
- **Description**: Password reset via email link
- **Functionality**:
  - Email-based password recovery
  - Secure token generation
  - Password update form
- **Database Interaction**: Updates `users` table password
- **Email Integration**: PHPMailer for reset link delivery

### Feature 4: Forgot Password
- **File**: `app/hello/auth/forgot_password.php`
- **Description**: Initial password recovery step
- **Functionality**:
  - Email input for account lookup
  - Sends password reset link
- **Database Interaction**: Queries users by email

### Feature 5: Logout
- **File**: `app/hello/auth/logout.php` (shared across all roles)
- **Description**: Session termination for all user types
- **Functionality**:
  - Destroys user session
  - Clears session variables
  - Redirects to login page

### Feature 6: Email Testing
- **File**: `app/hello/auth/testmail.php`
- **Description**: SMTP email configuration testing tool
- **Functionality**:
  - Tests PHPMailer setup
  - Verifies Gmail credentials
  - Validates SMTP connection
- **Configuration**: Uses Gmail SMTP with app password

---

## ADMIN FEATURES
**Directory**: `app/hello/admin/`  
**Controller**: `app/controllers/AdminController.php`

### Feature 1: Admin Dashboard
- **File**: `app/hello/admin/dashboard.php`
- **Description**: Main administrative overview and metrics
- **Functionality**:
  - Total users count (Admin, Professor, Student)
  - Total modules count
  - Today's attendance statistics
  - Pending account requests count
  - Recent attendance activity log (last 10 records)
  - Active modules with today's attendance count
  - Pending requests list (last 5)
  - System statistics cards
  - Quick navigation to all admin features
- **Database Interaction**: Multiple queries on `users`, `modules`, `attendance`, `account_requests`
- **UI Components**: Stats cards, activity table, module grid, request list
- **Real-time Data**: Updates based on current date

### Feature 2: Add New User
- **File**: `app/hello/admin/adduser.php`
- **Description**: Create new user accounts (Admin, Professor, Student)
- **Functionality**:
  - User role selection (dropdown)
  - Full name input (Nom, Prenom)
  - Email input
  - Password generation/input
  - Default password (email-based)
  - **Automatically send credentials via email**
  - Form validation
  - Duplicate email check
- **Database Interaction**: Inserts into `users` table
- **Email Feature**: Sends login credentials to user email using PHPMailer
- **Related**: Integrates with account request approval (copies photo from request)

### Feature 3: View/Manage Users
- **File**: `app/hello/admin/userlist.php`
- **Description**: List and manage all system users
- **Functionality**:
  - Display all users with pagination
  - Show user role, name, email
  - Edit user details
  - Delete user accounts
  - Search/filter users
- **Database Interaction**: `users` table CRUD operations
- **UI**: Paginated table with action buttons

### Feature 4: Add New Module
- **File**: `app/hello/admin/addmodule.php`
- **Description**: Create new course modules
- **Functionality**:
  - Module name input
  - Module code (optional)
  - Professor assignment (dropdown)
  - Total hours specification
  - Module description (optional)
  - Form validation
- **Database Interaction**: Inserts into `modules` table
- **Related**: Links to professor for teaching assignment

### Feature 5: View/Manage Modules
- **File**: `app/hello/admin/modulelist.php`
- **Description**: List and manage all course modules
- **Functionality**:
  - Display all modules in table format
  - Show module name, professor, associated classes
  - Edit module details
  - Delete modules
  - Pagination support
- **Database Interaction**: `modules` table queries

### Feature 6: Manage Classes
- **File**: `app/hello/admin/classes.php`
- **Description**: Create and manage student classes/groups
- **Functionality**:
  - Add new classes/groups
  - Assign class name and code
  - Link to academic level (1st, 2nd, 3rd year)
  - View existing classes
  - Edit class details
- **Database Interaction**: `classes` table CRUD

### Feature 7: Assign Students to Modules
- **File**: `app/hello/admin/assign_students.php`
- **Description**: Bulk assign students to course modules
- **Functionality**:
  - Select module
  - Multi-select student picker
  - Batch assignment interface
  - Visual feedback on selected students
  - Confirmation before assignment
  - View current assignments
- **Database Interaction**: Inserts/updates `attendance` table seed data
- **UI**: Interactive multi-select component

### Feature 8: View Attendance Records
- **File**: `app/hello/admin/attendancerecord.php`
- **Description**: System-wide attendance viewing
- **Functionality**:
  - Display all attendance records
  - Filter by:
    - Date range
    - Student
    - Module
    - Status (Present/Absent)
  - Pagination (100 records per page)
  - Export capability (view/print)
  - Attendance statistics overview
  - Visual status indicators (color-coded)
- **Database Interaction**: Queries `attendance` table with joins to `users` and `modules`

### Feature 9: System Notifications
- **File**: `app/hello/admin/notif.php`
- **Description**: View and manage system notifications
- **Functionality**:
  - Display all system notifications
  - Show notification triggers (e.g., "Student X reached 20% absence")
  - Sort by date/time
  - Pagination
  - Mark as read/unread
  - Archive old notifications
- **Database Interaction**: `notifications` table queries
- **Source**: Notifications created by professor absence alerts

### Feature 10: Admin Profile
- **File**: `app/hello/admin/profile.php`
- **Description**: Admin account management
- **Functionality**:
  - View admin profile information
  - Edit name, email
  - Change password
  - **Display profile photo** (if uploaded)
  - Update settings
  - Account security information
  - Logout button
- **Database Interaction**: `users` table update
- **Photo Display**: Uses `AvatarHelper.php` model

---

## PROFESSOR FEATURES
**Directory**: `app/hello/professor/`  
**Controller**: `app/controllers/ProfessorController.php`

### Feature 1: Professor Dashboard
- **File**: `app/hello/professor/prof_dashboard.php`
- **Description**: Main professor overview with teaching analytics
- **Functionality**:
  - List all assigned modules
  - Show total enrolled students per module
  - Display total class hours
  - Show number of attendance sessions recorded
  - **Display students at-risk (>20% absence)** - changed to 6.6%
  - Quick access to take attendance
  - Recent attendance activity
  - Module performance metrics
  - Student engagement indicators
  - Can identify which modules need attention
- **Database Interaction**: Complex queries on `modules`, `attendance`, `users`, `student_classes`
- **Time-based Logic**: Checks if current time falls within scheduled class times
- **UI**: Cards, stats, sortable lists

### Feature 2: View My Modules
- **File**: `app/hello/professor/my_modules.php`
- **Description**: Detailed view of assigned modules
- **Functionality**:
  - List all modules taught by professor
  - Show enrolled student count
  - Display average attendance percentage
  - Show total hours planned
  - Quick access to take attendance (button)
  - Module schedule display
  - Student list per module
  - Attendance rate visual indicators
  - Direct link to attendance taking
- **Database Interaction**: `modules`, `attendance`, `student_classes`, `module_schedule` queries
- **UI**: Module cards with statistics

### Feature 3: Take Attendance
- **File**: `app/hello/professor/take_attendance.php`
- **Description**: Core attendance marking interface with IP restriction
- **Functionality**:
  - **IP Restriction**: Only accessible from school WiFi (10.25.0.0/16 network)
  - Current class session identification
  - Student list with photos
  - Mark attendance (Present/Absent) with visual toggle
  - Submit attendance for the session
  - Validation:
    - Module ownership verification
    - Schedule time verification (only during scheduled hours)
    - One submission per session prevention
  - Confirmation before submission
  - Success feedback
- **Database Interaction**: Inserts into `attendance` table
- **Security Features**:
  - `.htaccess` IP restriction
  - `ip_check.php` validation function
  - Time-based class hour validation
- **UI**: Student grid with toggle switches, time display, submit button

### Feature 4: View Students
- **File**: `app/hello/professor/students.php`
- **Description**: Monitor student attendance by class and module
- **Functionality**:
  - Select class from dropdown
  - Select module from dropdown
  - View all students in selected class/module
  - **Display absence rate** for each student as percentage
  - **Display absence count** (e.g., "3 absences × 3.3%")
  - **Visual absence rate indicator** (circular progress with color coding):
    - Green: <6.6%
    - Yellow: 6.6%-15%
    - Red: >15%
  - **Alert badge** for students at/above 6.6% threshold
  - **"Auto Sent" badge** - shows if email reminder already sent today
  - **Send Reminder button** - manually trigger absence alert email
  - Auto-send reminders:
    - Automatically sends emails to students reaching 6.6% threshold
    - Shows count of auto-sent emails at page top
    - Prevents duplicate sends (once per day)
  - **Email Configuration**:
    - From: macademia Faculty System
    - Subject: Attendance Alert
    - Body: HTML formatted with professor name, absence rate, absence count, student name
  - Toggle between absence/presence rate display
  - Sort and filter capabilities
  - Pagination support
- **Database Interaction**: `attendance`, `users`, `modules`, `student_classes`, `reminder_log` tables
- **Email Features**: PHPMailer integration for reminder emails
- **Threshold Logic**: 
  - Each absence = 3.3%
  - Email triggered at ≥6.6% (or 2 absences)
- **Logging**: Records email sends in `reminder_log` table to prevent spam

### Feature 5: View Reports
- **File**: `app/hello/professor/reports.php`
- **Description**: Attendance analysis and reporting
- **Functionality**:
  - Generate attendance reports by:
    - Module
    - Class
    - Date range
  - Statistics:
    - Total classes held
    - Total students
    - Overall attendance rate
    - Attendance by date
  - Export options (PDF/Excel consideration)
  - Graphical representations (charts)
  - Identify attendance patterns
  - Student-specific reports
- **Database Interaction**: Aggregated queries on `attendance` with statistical functions

### Feature 6: Professor Profile
- **File**: `app/hello/professor/profile.php`
- **Description**: Professor account management
- **Functionality**:
  - View profile information
  - Edit name, email
  - Change password
  - **Display profile photo** (if uploaded during account creation)
  - Academic title/qualification
  - Contact information
  - Department assignment (if applicable)
  - Update profile settings
- **Database Interaction**: `users` table queries/updates
- **Photo Display**: Uses `AvatarHelper.php`

---

## STUDENT FEATURES
**Directory**: `app/hello/student/`  
**Controller**: `app/controllers/StudentController.php`

### Feature 1: Student Dashboard
- **File**: `app/hello/student/dashstud.php`
- **Description**: Main student overview and statistics
- **Functionality**:
  - **Attendance Rate Display**: Overall percentage (classes attended / total classes)
  - **Total Absences Count**: Number of recorded absences
  - **Total Classes Attended**: Sessions where marked present
  - **Today's Classes**: Number of scheduled classes today
  - **My Modules List**: Enrolled courses with professor names and schedules
  - **Weekly Schedule Display**: Classes by day and time
  - **Quick Stats Cards**:
    - Total modules enrolled
    - Total absences
    - Overall attendance %
    - Today's class count
  - **Upcoming Classes**: Next scheduled sessions
  - **Last Week's Performance**: Recent attendance summary
  - **Absence Alert**: If at critical threshold
- **Database Interaction**: `attendance`, `modules`, `users`, `module_schedule` queries
- **Calculations**: Attendance rate percentage, absence count
- **UI**: Stats cards, module list, schedule grid, progress indicators

### Feature 2: View My Attendance
- **File**: `app/hello/student/attendance.php`
- **Description**: Detailed attendance record review
- **Functionality**:
  - Display all attendance records chronologically
  - Filter by:
    - Module
    - Date range
    - Status (Present/Absent)
  - Show date, module, professor, status
  - Color-coded status indicators
  - Attendance rate per module
  - Total sessions attended/missed
  - Pagination
  - Export to PDF (consideration)
- **Database Interaction**: `attendance` table with joins to `modules` and `users`
- **UI**: Table with filters, color coding, pagination

### Feature 3: View Absences
- **File**: `app/hello/student/absences.php`
- **Description**: Focused view of absence records
- **Functionality**:
  - List all absences
  - Show date, module, reason (if provided)
  - Filter by:
    - Module
    - Date range
    - Excused/Unexcused (if tracked)
  - Absence statistics:
    - Total absences
    - Absences per module
    - Excused vs unexcused breakdown
  - Warning if approaching critical threshold
  - Month-by-month absence trend
  - Export capability
- **Database Interaction**: Filtered `attendance` queries where status='absent'

### Feature 4: View My Modules
- **File**: `app/hello/student/modules.php`
- **Description**: Enrolled courses display
- **Functionality**:
  - List all enrolled modules
  - Show professor name
  - Display schedule (day/time)
  - Module description (if available)
  - Attendance rate per module
  - Number of absences per module
  - Total hours/credits
  - Module code (if available)
  - Quick link to detailed module view
- **Database Interaction**: `modules`, `users`, `module_schedule`, `attendance` queries

### Feature 5: Student Profile
- **File**: `app/hello/student/profile.php`
- **Description**: Student account management
- **Functionality**:
  - View profile information:
    - Full name
    - Email
    - Student ID (if applicable)
    - Registration date
  - Edit profile:
    - Name
    - Email
    - Password change
  - **Display profile photo** (uploaded during registration)
  - Contact preferences
  - Privacy settings
  - Feature highlights:
    - Email notification feature
    - Attendance tracking
  - Logout option
- **Database Interaction**: `users` table
- **Photo Display**: `AvatarHelper.php` model

### Feature 6: AI Chatbot Interface
- **File**: `app/hello/student/chatbot/` directory with backend
- **Description**: Intelligent attendance Q&A system
- **Frontend Functionality**:
  - Chat input box
  - Conversation history display
  - Send message button
  - Real-time response from AI
  - User-friendly interface
  - Typing indicators
  - Clear chat option
  - Suggested questions
- **Backend**: Flask Python application (`app.py`)

---

## AI CHATBOT FEATURES
**Files**: `app/hello/student/chatbot/backend/app.py`

### Architecture
- **Framework**: Flask (Python)
- **API**: OpenAI GPT-4 / GPT-3.5
- **Database**: MySQL connector (same database as PHP app)
- **Integration**: Reverse proxy via Apache to `localhost:5000`
- **Access Point**: `/api/chat` endpoint from PHP frontend

### Feature 1: Absence Count Query
- **Action**: `get_absence_count`
- **User Query**: "How many absences do I have?"
- **Functionality**:
  - Retrieves total absence count from database
  - Returns formatted response with emoji
  - Example: "📊 **Total Absences**: 5"
- **Database**: Queries `attendance` table where status='absent'

### Feature 2: Absence by Module
- **Action**: `get_absence_by_module`
- **User Query**: "Which module do I miss the most?"
- **Functionality**:
  - Breaks down absences by each module
  - Shows absence count per module
  - Lists modules with highest absences first
  - Example output: "📊 **Absences by Module**: • Math: 3 • Physics: 2"
- **Database**: GROUP BY module_name query

### Feature 3: Absence This Week
- **Action**: `get_absence_this_week`
- **User Query**: "How many classes did I miss this week?"
- **Functionality**:
  - Counts absences in current week (Monday-Sunday)
  - Shows positive/negative feedback
  - Example: "📅 **This Week**: 2 absences"
- **Calculation**: Uses WEEK() function in MySQL

### Feature 4: Absence This Month
- **Action**: `get_absence_this_month`
- **User Query**: "What's my absence count this month?"
- **Functionality**:
  - Aggregates absences for current month
  - Motivational message if perfect attendance
  - Example: "📅 **This Month**: 1 absence"
- **Calculation**: Uses MONTH() and YEAR() functions

### Feature 5: Excused vs Unexcused Absences
- **Action**: `get_excused_vs_unexcused`
- **User Query**: "How many excused vs unexcused absences?"
- **Functionality**:
  - Breaks down absence types
  - Shows percentage distribution
  - Example: "✓ Excused: 2, ✗ Unexcused: 3"
- **Database**: Conditional COUNT on attendance.reason field

### Feature 6: Attendance Rate
- **Action**: `get_attendance_rate`
- **User Query**: "What's my overall attendance percentage?"
- **Functionality**:
  - Calculates (present / total_sessions) * 100
  - Shows emoji indicator:
    - 🌟 if ≥90%
    - 👍 if ≥75%
    - ⚠️ if <75%
  - Displays present count, absent count, total
  - Example: "🌟 **Attendance Rate: 92%** ✓ Present: 23, ✗ Absent: 2"

### Feature 7: Next Class Query
- **Action**: `get_next_class`
- **User Query**: "When is my next class?"
- **Functionality**:
  - Finds next scheduled class from current time
  - Shows module name, professor, time, day
  - Considers current day and time
  - Example: "🎓 **Next Class**: Mathematics with Prof. Smith, Today at 2:00 PM"
- **Calculation**: Real-time comparison with module_schedule table

### Feature 8: Last Class of Week
- **Action**: `get_last_class_of_week`
- **User Query**: "What's my last class this week?"
- **Functionality**:
  - Identifies final scheduled class in week
  - Shows day and time
  - Example: "📅 **Last Class This Week**: Physics Friday at 5:00 PM"

### Feature 9: Today's Schedule
- **Action**: `get_today_schedule`
- **User Query**: "What classes do I have today?"
- **Functionality**:
  - Lists all classes scheduled for today
  - Ordered by time
  - Shows module, professor, time
  - Example: "📚 **Today's Schedule**: \n• Math 9:00 AM (Prof. A)\n• English 2:00 PM (Prof. B)"
- **Database**: Queries module_schedule WHERE weekday = DAYOFWEEK(TODAY())

### Feature 10: Weekly Schedule
- **Action**: `get_week_schedule`
- **User Query**: "Show me my schedule for the week"
- **Functionality**:
  - Displays all classes for the week
  - Organized by day
  - Shows time and professor
  - Example output with daily breakdown

### Feature 11: Classes Today Count
- **Action**: `get_classes_today_count`
- **User Query**: "How many classes do I have today?"
- **Functionality**:
  - Simple count of today's classes
  - Example: "Today you have 3 classes"

### Feature 12: Module List
- **Action**: `get_modules_list`
- **User Query**: "List all my modules/courses"
- **Functionality**:
  - Shows all enrolled modules
  - Example: "📚 **Your Modules**: • Math • Physics • English • History"
- **Database**: SELECT DISTINCT modules from student's attendance records

### Feature 13: Module Details
- **Action**: `get_module_details`
- **User Query**: "Tell me about the Physics module"
- **Functionality**:
  - Retrieves module information
  - Shows professor, credits, hours
  - Current attendance in that module
  - Example with detailed breakdown

### Feature 14: Professors List
- **Action**: `get_professors_list`
- **User Query**: "Who are my professors?" / "List all my teachers"
- **Functionality**:
  - Shows all professors teaching the student
  - Displays professor name, email, modules taught
  - Formatted list
  - Example: "👨‍🏫 **Your Professors**: • **Dr. Smith** (Math, Physics), • **Prof. Johnson** (English)"
- **Database**: DISTINCT professors joined through modules and attendance

### Feature 15: Free-form Conversation
- **Action**: `conversation`
- **User Query**: Any general question
- **Functionality**:
  - Routes to OpenAI API for general conversation
  - Context-aware (knows student's attendance data)
  - Can answer questions beyond predefined actions
  - Example: "Tell me study tips for improving my attendance"
- **Integration**: Uses `chat_with_ai_enhanced()` function

### Feature 16: Confidence Scoring
- **All Actions**: Includes confidence level (0.0-1.0)
- **Functionality**:
  - Only executes actions with confidence ≥0.5
  - Low confidence queries get: "I'm not quite sure what you're asking..."
  - Helps filter misunderstood queries

### Feature 17: Error Handling
- **Database Connection Errors**: Returns user-friendly error messages
- **No Data Scenarios**: Returns appropriate "No data" messages (e.g., "You have no absences!")
- **Logging**: Error logging for debugging

---

## COMMON FEATURES (ALL USERS)

### Feature 1: Theme Toggle (Dark/Light Mode)
- **Implementation**: JavaScript-based theme switching
- **Storage**: LocalStorage persistence
- **Availability**: All dashboard pages
- **UI**: Moon/Sun icon toggle button in navbar
- **CSS Variables**: Uses CSS custom properties for theming

### Feature 2: Profile Photo Display
- **Implementation**: `app/models/AvatarHelper.php`
- **Functionality**:
  - Display uploaded profile photo in navbar
  - Fallback to user initial avatar if no photo
  - Responsive sizing (38x38px)
  - Object-fit for proper image scaling
  - All role dashboards support photo display
- **Storage**: `public/assets/uploads/profiles/`
- **File Formats Supported**: JPG, PNG, GIF, WebP
- **Max Size**: 5MB

### Feature 3: Notification Bell
- **Implementation**: JavaScript dropdown component
- **Functionality**:
  - Bell icon in navbar
  - Click to show notification dropdown
  - Displays system notifications
  - Shows "No new notifications" when empty
  - Badge with notification count (static for now)
  - Can be enhanced for real-time updates
- **UI**: Icon, dropdown menu, notification list

### Feature 4: Responsive Sidebar Navigation
- **Implementation**: Collapsible/expandable sidebar
- **Functionality**:
  - Toggle button to show/hide sidebar
  - Active page highlighting
  - Role-specific menu items
  - Icon + text labels
  - Organized menu structure
- **Mobile**: Responsive design for small screens

### Feature 5: Session Management
- **Implementation**: PHP native sessions with bootstrap.php
- **Functionality**:
  - Secure session initialization
  - Role verification on every page
  - Redirect to login if not authenticated
  - Session timeout handling
  - Logout clears all session data
- **Security**: Prevents direct access to view files

### Feature 6: Bootstrap Entry Point
- **File**: `bootstrap.php`
- **Functionality**:
  - Initializes application
  - Loads configuration
  - Starts session
  - Defines global constants:
    - `BASE_PATH`
    - `CONFIG_PATH`
    - `PUBLIC_URL`
    - `CONTROLLERS_PATH`
  - Connects to database
  - Prevents direct view file access

---

## DATABASE MODELS

### Model 1: User Model
- **File**: `app/models/User.php`
- **Table**: `users`
- **Attributes**:
  - id (Primary Key)
  - nom (Last Name)
  - prenom (First Name)
  - email (Unique)
  - password (Hashed)
  - role (admin/professor/student)
  - photo_path (Profile photo location)
  - created_at
  - updated_at
- **Relationships**: 1:Many with Modules (professor), 1:Many with Attendance (student)

### Model 2: Module Model
- **File**: `app/models/Module.php`
- **Table**: `modules`
- **Attributes**:
  - id (Primary Key)
  - module_name
  - module_code (Optional)
  - professor_id (Foreign Key to users)
  - total_hours
  - description
  - created_at
- **Relationships**: 1:Many with Attendance, 1:Many with ModuleSchedule, Many:Many with Classes

### Model 3: Attendance Model
- **File**: `app/models/Attendance.php`
- **Table**: `attendance`
- **Attributes**:
  - id (Primary Key)
  - student_id (Foreign Key to users)
  - module_id (Foreign Key to modules)
  - date
  - status (present/absent/excused)
  - created_at
  - updated_at
- **Indexes**: On student_id, module_id, date for performance

### Model 4: Notification Model
- **File**: `app/models/Notification.php`
- **Table**: `notifications`
- **Attributes**:
  - id (Primary Key)
  - user_id (Foreign Key to users)
  - message
  - type (alert/info/warning)
  - is_read
  - created_at
- **Purpose**: Store system notifications for absence alerts

### Model 5: Avatar Helper
- **File**: `app/models/AvatarHelper.php`
- **Functions**:
  - `getAvatarURL()` - Returns photo URL or fallback initial
  - `hasProfilePhoto()` - Checks if user has photo
  - `displayAvatar()` - Renders HTML img tag with fallback
  - Support functions for all roles

### Additional Database Tables
- **account_requests**: Pending user registrations with photo_path
- **module_schedule**: Course schedule (day/time/location)
- **module_classes**: Mapping modules to classes
- **student_classes**: Mapping students to classes
- **reminder_log**: Email send tracking to prevent spam

---

## TECHNICAL FEATURES

### Feature 1: MVC Architecture
- **Models**: Data layer classes in `app/models/`
- **Views**: Presentation files in `app/hello/`
- **Controllers**: Logic layer in `app/controllers/`
- **Routing**: `public/index.php` front controller
- **Current State**: Controllers are minimal; most logic in views (original structure preserved)

### Feature 2: Apache Reverse Proxy
- **Configuration**: `.htaccess` in `public/`
- **Purpose**: Route `/api/chat` to Flask backend at `localhost:5000`
- **Functionality**: Single public URL for both PHP and Python
- **Modules Required**: `mod_proxy`, `mod_proxy_http`
- **Use Case**: Student chatbot integration seamless

### Feature 3: IP Restriction for Attendance
- **Files**: 
  - `app/hello/professor/ip_check.php` - IP validation function
  - `app/hello/professor/.htaccess` - Apache directive
- **Functionality**:
  - Only school WiFi (10.25.0.0/16) can access `take_attendance.php`
  - Returns 403 Forbidden for external IPs
  - Shows client IP in error message
  - Handles X-Forwarded-For headers for proxy scenarios

### Feature 4: PHPMailer Email Integration
- **Location**: `app/hello/*/phpmailer/` directories
- **SMTP**: Gmail SMTP (smtp.gmail.com:587)
- **Authentication**: App-specific password
- **Features**:
  - HTML and plain text email support
  - Attachment support
  - Error handling and logging
- **Used For**:
  - Account approval notifications
  - Absence alerts (6.6%+ threshold)
  - Password reset links
  - Login credentials delivery

### Feature 5: Front Controller Routing
- **Entry Point**: `public/index.php`
- **URL Pattern**: `/index.php/role/action`
- **Examples**:
  - `/index.php/admindash/dashboard` - Admin dashboard
  - `/index.php/profdash/students` - Professor students view
  - `/index.php/studdash/dashstud` - Student dashboard
  - `/index.php/login/login` - Login page
- **Benefits**: SEO-friendly URLs, centralized routing

### Feature 6: Database Connection Pooling
- **Implementation**: MySQLi persistent connections
- **Configuration**: `config/config.php`
- **Availability**: Global `$mysqli` object via bootstrap

### Feature 7: Input Validation & Sanitization
- **Methods**:
  - Prepared statements for SQL injection prevention
  - htmlspecialchars() for XSS prevention
  - Input type casting (int/string)
  - Email validation
  - File upload validation (size, type)

### Feature 8: Error Handling
- **Approach**: Try-catch blocks in critical sections
- **Logging**: error_log() function usage
- **User Feedback**: Graceful error messages
- **No Exposure**: Production error details not shown

### Feature 9: Dynamic CSS Theming
- **Variables**: CSS custom properties (--primary, --text, etc.)
- **Toggle**: JavaScript-based dark/light switch
- **Persistence**: localStorage for theme preference
- **Files Affected**: All dashboard CSS

### Feature 10: Responsive Design
- **Mobile First**: CSS media queries
- **Breakpoints**: Mobile, tablet, desktop
- **Framework**: Custom CSS (no Bootstrap dependency)
- **Components**: Sidebar collapses, tables responsive

---

## FEATURE CLASSIFICATION MATRIX

### PRESENTATION-HEAVY FEATURES
*(Best for live demo with visual component)*

1. **Login Interface** - Dramatic visual entry point
2. **Student Dashboard** - Shows real attendance data at a glance
3. **Professor Take Attendance** - Interactive student list with toggle
4. **Admin Dashboard** - Comprehensive metrics and stats
5. **View Attendance Records** - Real-time data filtering
6. **Student Absences View** - Visual absence breakdown
7. **Professor Students View** - Color-coded absence rates with visual indicators
8. **Theme Toggle** - Shows UI responsiveness immediately
9. **Profile Photo Display** - User-focused feature
10. **Chatbot Interface** - Engaging AI interaction

### BACKEND/LOGIC-HEAVY FEATURES
*(Better for explanation/presentation slides)*

1. **Email Notification System** - Explain threshold logic (6.6%)
2. **IP Restriction** - Security mechanism explanation
3. **Reverse Proxy Setup** - Architecture diagram
4. **Database Relations** - Schema and data flow
5. **Session Management** - Security/authentication flow
6. **Password Reset** - Email integration explanation
7. **Chatbot Database Queries** - Show AI logic
8. **Account Request Approval** - Workflow explanation
9. **Absence Rate Calculation** - Math/formula explanation
10. **Reminder Log Tracking** - Prevent spam logic

### QUICK-TO-DEMONSTRATE FEATURES
*(2-5 minutes demo time)*

1. Login with different roles
2. Check student attendance rate
3. View module schedule
4. Send attendance reminder email
5. Filter attendance by module
6. Toggle theme
7. Ask chatbot a question
8. View profile with photo
9. Check today's classes
10. Navigate between pages

### TIME-INTENSIVE FEATURES
*(Require data setup/longer demo)*

1. Assign students to modules
2. Mark full attendance session
3. Generate attendance reports
4. Create new modules
5. Create user accounts
6. View month-long attendance trends
7. Compare module attendance rates
8. Generate statistics

### SECURITY-FOCUSED FEATURES
*(Explain value in presentation)*

1. Role-based access control (3 roles)
2. IP-based location restriction
3. Session timeout
4. Direct view file access blocking
5. SQL injection prevention (prepared statements)
6. XSS protection (htmlspecialchars)
7. Password hashing
8. CSRF prevention potential
9. Email verification
10. Duplicate prevention checks

### DATABASE-INTENSIVE FEATURES
*(Show via charts/slides)*

1. Attendance statistics
2. Module performance metrics
3. Student risk analysis
4. Absence trends
5. Enrollment numbers
6. Email send logs
7. Account request queue
8. Schedule conflicts
9. Professor workload
10. Class size analysis

### INTEGRATION FEATURES
*(Highlight technical accomplishment)*

1. PHP + Python Flask integration
2. Apache reverse proxy
3. OpenAI API integration
4. PHPMailer SMTP integration
5. MySQL database connection
6. File upload to server
7. Session state persistence
8. Dynamic theme switching
9. Real-time notification updates
10. Multi-language support (potential)

### FRONTEND FEATURES
*(Visual polish - good for presentation)*

1. Dark/Light mode
2. Responsive sidebar
3. Profile photo avatars
4. Color-coded status indicators
5. Progress bars for attendance
6. Notification bell
7. Icon-based navigation
8. Smooth transitions
9. Loading states
10. Error message styling

### USER-FACING VALUE FEATURES
*(Highlight for stakeholder appeal)*

1. Attendance tracking (student engagement)
2. Absence alerts (early warning system)
3. Visual performance indicators
4. Schedule viewing (convenience)
5. Email notifications (communication)
6. AI chatbot assistance (support)
7. Photo profile personalization
8. One-click attendance (efficiency)
9. Comprehensive reports (insights)
10. Module management (organization)

---

## ROLE DISTRIBUTION SUGGESTIONS FOR TEAM PRESENTATION

### APPROACH 1: Role-Based Distribution (Recommended)

**Person 1: Admin & System Architecture**
- Present login system
- Show admin dashboard creation workflow
- Demonstrate user/module/class management
- Explain IP restriction and security features
- Show database schema
- Explain reverse proxy setup
- Time: ~10-12 minutes

**Person 2: Professor & Attendance System**
- Present professor dashboard
- Demonstrate take attendance interface (interactive)
- Show student monitoring with absence alerts
- Demonstrate email reminder sending
- Explain attendance calculation logic
- Time: ~8-10 minutes

**Person 3: Student & AI Features**
- Present student dashboard
- Show attendance/absence views
- Demonstrate chatbot interaction
- Query chatbot for different attendance questions
- Show schedule and module information
- Time: ~8-10 minutes

### APPROACH 2: Feature-Type Distribution

**Person 1: Core Features & Workflows**
- Login system
- Attendance marking (professor)
- Attendance viewing (student)
- Dashboard overviews (all roles)
- Time: ~12-15 minutes

**Person 2: Advanced Features & Integration**
- Email notifications system
- AI Chatbot with queries
- IP restriction + security
- Theme toggling + responsive design
- File upload + profile photos
- Time: ~10-12 minutes

**Person 3: Backend & Management**
- Database structure and relationships
- Account creation/approval workflow
- Module and class management
- Report generation
- Statistics and analytics
- Time: ~8-10 minutes

### APPROACH 3: Flow-Based Distribution

**Person 1: User Journey - Day 1 (Registration & Setup)**
- Account request with photo
- Admin approval workflow
- Email credential delivery
- First login
- Profile setup
- Time: ~7-9 minutes

**Person 2: User Journey - Regular Usage (Daily Operations)**
- Professor: Take attendance
- Student: View attendance
- Both: Check schedules
- Professor: Send absence alerts
- Student: Check notifications
- Time: ~10-12 minutes

**Person 3: Advanced Usage & Insights (Analytics & Support)**
- Attendance reports and trends
- Student performance analysis
- Chatbot assistance for queries
- Profile management
- System administration
- Time: ~8-10 minutes

---

## SUMMARY STATISTICS

- **Total Pages/Views**: 25+
- **User Roles**: 3 (Admin, Professor, Student)
- **Database Tables**: 8+
- **Core Features**: 50+
- **Integration Points**: 4 (MySQL, PHPMailer, Flask, OpenAI)
- **Files to Demonstrate**: 15-20 key pages
- **Estimated Demo Time**: 28-35 minutes (full demo)
- **Quick Demo Time**: 8-12 minutes (key features only)

---

## RECOMMENDED PRESENTATION FLOW

**Total Time: 30-35 minutes**

1. **Intro** (2 min): Project overview, architecture, tech stack
2. **Login & Authentication** (2 min): Show 3 roles, login process
3. **Admin Section** (5 min): Dashboard, user/module management, assignments
4. **Professor Section** (8 min): Dashboard, take attendance (interactive), student monitoring, email alerts
5. **Student Section** (6 min): Dashboard, attendance/absence views, chatbot demo
6. **Backend Architecture** (4 min): Database, security, email, AI integration
7. **Q&A** (3 min): Teacher questions

---

**END OF COMPREHENSIVE FEATURES INVENTORY**

*This document contains every feature found in the project from index.php to the last file, with descriptions suitable for classification by another AI or team member for presentation and live demo purposes.*
