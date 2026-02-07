# NEP Portal (National Education Policy Portal)

A comprehensive academic management system designed to streamline interactions between Students, Faculty, and Administrators in educational institutions. The portal manages student data, attendance, results, subjects, and notices with a modern, responsive user interface.

## 🚀 Features

### for Students
- **Dashboard:** View academic progress and quick stats.
- **Attendance:** Check daily attendance records and status.
- **Results:** Access semester-wise marks and credits.
- **My Subjects:** View enrolled subjects and details.
- **Profile:** Manage personal information and change passwords.
- **Responsive UI:** Optimized for mobile and desktop viewing.

### for Faculty
- **Dashboard:** Overview of assigned classes and students.
- **Student Management:** View student lists, add/edit student details (including passwords and subjects).
- **Academic Tasks:** Mark daily attendance and enter semester marks.
- **Notices:** Post announcements for students.

### for Admin
- **Central Dashboard:** System-wide statistics and management.
- **User Management:** Full control to add, edit, delete Students and Teachers.
- **Academic Management:** Manage Subjects, Marks, and Credits.
- **Reports:** Generate system reports.
- **Announcements:** Post college-wide notices.

## 🛠️ Technology Stack
- **Backend:** PHP (Native)
- **Database:** MySQL
- **Frontend:** HTML5, Tailwind CSS (via CDN), JavaScript
- **Icons:** Bootstrap Icons
- **Fonts:** Inter (Google Fonts)

## 📦 Installation & Setup

1.  **Clone/Download:** 
    - Place the project folder `newnep` inside your web server directory (e.g., `C:\xampp\htdocs\`).

2.  **Database Setup:**
    - Open phpMyAdmin (http://localhost/phpmyadmin).
    - Create a new database named `nep_portal`.
    - Import the provided SQL file: `nep_portal.sql` located in the project root.

3.  **Configuration:**
    - Check `db.php` in the root directory to ensure database credentials match your local setup:
      ```php
      $servername = "localhost";
      $username = "root";
      $password = "";
      $dbname = "nep_portal";
      ```

4.  **Run:**
    - Open your browser and navigate to: `http://localhost/newnep/`

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
├── admin/                  # Administrator Portal
│   ├── admin_dashboard.php # Main admin overview
│   ├── admin_login.php     # Admin authentication
│   ├── manage_students.php # CRUD operations for students
│   ├── edit_student.php    # Enhanced student editor (Passwords/Subjects)
│   ├── manage_teachers.php # CRUD operations for faculty
│   ├── manage_subjects.php # Curriculum management
│   ├── manage_marks_credits.php # Academic results oversight
│   ├── reports.php         # Analytics, Charts, Export & Print
│   ├── announcements.php   # System-wide notice board
│   ├── view_feedback.php   # User feedback viewer
│   ├── export_data.php     # Data export script (CSV)
│   ├── profile.php         # Admin profile settings
│   └── change_password.php # Security settings
│
├── faculty/                # Faculty Portal
│   ├── faculty_login.php   # Faculty authentication
│   ├── teacher_dashboard.php # Main faculty overview
│   ├── edit_student.php    # Student management for teachers
│   ├── mark_attendance.php # Daily attendance marking
│   ├── add_marks.php       # Semester marks entry
│   └── notices.php         # Student announcements
│
├── student/                # Student Portal
│   ├── student_login.php   # Student authentication
│   ├── student_dashboard.php # Main student overview
│   ├── my_subjects.php     # Enrolled subjects list
│   ├── view_marks.php      # Academic results viewer
│   ├── profile.php         # Personal profile management
│   └── change_password.php # Security settings
│
├── db.php                  # Database connection configuration
├── nep_portal.sql          # Database import file (Structure + Demo Data)
├── index.html              # Landing page (Home, About, Contact)
├── feedback_submit.php     # Feedback form processor
├── ai/                     # AI Assistant Module
│   ├── index.html          # Chat Interface
│   ├── chat.php            # Chat backend logic
│   └── api.php             # API handling
└── README.md               # Project documentation
```

## ✨ Recent Updates
- **Full UI Redesign:** Implemented Tailwind CSS for a modern look.
- **Enhanced Data Access:** Admins and Faculty can now fully edit student profiles, including passwords and complex subject data.
- **Security:** Password hashing implementation for all users.

---
*Developed for the NEP Portal Project.*
