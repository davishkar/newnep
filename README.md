# NEP Portal (National Education Policy Portal)

A comprehensive academic management system designed to streamline interactions between Students, Faculty, and Administrators in educational institutions. The portal manages student data, attendance, results, subjects, and notices with a modern, responsive user interface.

## 🚀 Features

### for Students
- **Dashboard:** View academic progress and quick stats.
- **Attendance:** Check daily attendance records and status.
# NEP Portal - Student Management System

A comprehensive web-based student management system built with PHP and MySQL, designed to manage students, teachers, subjects, marks, and attendance following the National Education Policy (NEP) framework.

## Features

### Student Portal
- **Student registration and login** with year selection (FY/SY/TY)
- View personal dashboard with academic overview
- View enrolled subjects
- **Subject Change Request** - Submit requests to change enrolled subjects with optional proof upload
- View request history and status (Pending/Approved/Rejected)
- View marks and attendance
- Profile management
- Password change functionality

### Admin Panel
- Manage students (add, edit, delete, view)
- Manage teachers
- Manage subjects
- **Subject Change Approvals** - Review and approve/reject student subject change requests
- Manage marks and credits
- View reports and analytics
- Announcements management
- View student feedback

### Faculty Panel
- Mark attendance
- View attendance reports
- Enter semester marks
- View student marks
- **Subject Change Approvals** - Review and approve/reject student subject change requests
- Manage announcements
- Student management

## Recent Updates (February 2026)

### ✨ New Features
1. **Year Selection in Student Signup**
   - Students now select their academic year (FY/SY/TY) during registration
   - Fixes visibility issue where new students weren't appearing in admin/faculty panels

2. **Subject Change Request System**
   - Students can request to change their enrolled subjects
   - Optional file upload for supporting documents (JPG, PNG, PDF)
   - Both admin and teachers can approve/reject requests
   - Automatic enrollment update upon approval
   - Complete request history tracking with status badges

## Technologies Used

- **Backend**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript
- **CSS Framework**: Tailwind CSS (CDN)
- **Icons**: Bootstrap Icons
- **Charts**: Chart.js
- **Server**: Apache (XAMPP)

## Installation

1. **Prerequisites**
   - XAMPP (or any Apache + MySQL + PHP stack)
   - PHP 7.4 or higher
   - MySQL 5.7 or higher

2. **Setup Steps**
    - Place the project folder `newnep` inside your web server directory (e.g., `C:\xampp\htdocs\`).

   - Import the base database: `nep_portal.sql`
   - Run migrations for new features:
     ```bash
     # Option 1: Use the batch file
     c:\xampp\htdocs\newnep\migrations\run_migration.bat
     
     # Option 2: Manual import via phpMyAdmin
     # Import: migrations/add_subject_change_requests.sql
     ```

3. **Configuration**
   - Verify database credentials in `db.php`:
     ```php
     $servername = "localhost";
     $username = "root";
     $password = "";
     $dbname = "nep_portal";
     ```

4. **File Permissions**
   - Ensure the `uploads/` directory is writable:
     ```bash
     mkdir uploads/subject_change_proofs
     chmod 755 uploads/subject_change_proofs
     ```

5. **Access the Application**
   - Homepage: `http://localhost/newnep/`
   - Admin Panel: `http://localhost/newnep/admin/admin_login.php`
   - Faculty Panel: `http://localhost/newnep/faculty/faculty_login.php`
   - Student Portal: `http://localhost/newnep/student/student_login.php`

## 🔑 Default Login Credentials (Demo Data)

### Admin Portal
- **URL:** `http://localhost/newnep/admin/admin_login.php`
- **Email:** `admin@gmail.com`
- **Password:** `admin123`

### Faculty Portal
- **URL:** `http://localhost/newnep/faculty/faculty_login.php`
- **Email:** `komal@gmail.com`
- **Password:** `123456`

### Student Portal
- **URL:** `http://localhost/newnep/student/student_login.php`
- **Email:** `aarav@example.com`
- **Password:** `Student@123`
*(Note: All demo students have the password `Student@123`)*

## 📂 Project Structure

```bash
newnep/
├── admin/                       # Administrator Portal
│   ├── admin_dashboard.php      # Main admin overview
│   ├── admin_login.php          # Admin authentication
│   ├── manage_students.php      # CRUD operations for students
│   ├── subject_change_approvals.php # NEW: Review subject change requests
│   ├── manage_teachers.php      # CRUD operations for faculty
│   ├── manage_subjects.php      # Curriculum management
│   ├── manage_marks_credits.php # Academic results oversight
│   ├── reports.php              # Analytics, Charts, Export & Print
│   └── announcements.php        # System-wide notice board
│
├── faculty/                     # Faculty Portal
│   ├── faculty_login.php        # Faculty authentication
│   ├── teacher_dashboard.php    # Main faculty overview
│   ├── subject_change_approvals.php # NEW: Review subject change requests
│   ├── mark_attendance.php      # Daily attendance marking
│   ├── semester_mark_entry.php  # Semester marks entry
│   └── view_marks.php           # View student marks
│
├── student/                     # Student Portal
│   ├── student_login.php        # Student authentication
│   ├── student_signup.php       # UPDATED: Now includes year selection
│   ├── student_dashboard.php    # Main student overview
│   ├── subject_change_request.php # NEW: Submit subject change requests
│   ├── my_subjects.php          # Enrolled subjects list
│   ├── view_marks.php           # Academic results viewer
│   └── profile.php              # Personal profile management
│
├── migrations/                  # Database Migrations
│   ├── add_subject_change_requests.sql # NEW: Subject change requests table
│   └── run_migration.bat        # NEW: Migration runner script
│
├── uploads/                     # File Uploads
│   └── subject_change_proofs/   # NEW: Subject change request proof files
│
├── db.php                       # Database connection configuration
├── nep_portal.sql               # Base database schema
├── index.html                   # Landing page
└── README.md                    # Project documentation
```

## 🔒 Security Features

- **Password Hashing**: All passwords are hashed using PHP's `password_hash()` with bcrypt
- **SQL Injection Prevention**: Prepared statements used throughout
- **XSS Protection**: Output sanitization with `htmlspecialchars()`
- **Session Management**: 30-minute timeout for student/faculty sessions
- **File Upload Validation**: Type and size restrictions on uploaded files
- **Role-Based Access Control**: Separate authentication for students, faculty, and admins

## 📝 Usage Guide

### For Students
1. Register at `/student/student_signup.php` (select your year: FY/SY/TY)
2. Login and complete your profile
3. View subjects, marks, and attendance
4. Submit subject change requests when needed

### For Faculty
1. Login with your credentials
2. Mark attendance and enter marks
3. Review and approve/reject subject change requests
4. Manage student information

### For Administrators
1. Login to admin panel
2. Manage all users (students, teachers)
3. Review subject change requests
4. Generate reports and analytics

## 🤝 Contributors
1.Avishkar Deshmukh
2.Varadraj Nalavade

## 🫶 Contributing

Contributions are welcome! Please feel free to submit pull requests or open issues for bugs and feature requests.

## 📄 License

This project is open source and available for educational purposes.

---

```markdown
**Last updated:** 13 April 2026  

```
