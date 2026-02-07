<?php
session_start();
require_once __DIR__ . '/../db.php';

// Session timeout check (30 minutes)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header("Location: admin_login.php?timeout=1");
    exit;
}
$_SESSION['last_activity'] = time();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$admin_id = intval($_SESSION['admin_id']);

/* COUNTS using prepared statements */
// FY Students
$fyYear = 'FY';
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS c FROM students WHERE year = ?");
mysqli_stmt_bind_param($stmt, "s", $fyYear);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$fy = mysqli_fetch_assoc($result)['c'];
mysqli_stmt_close($stmt);

// SY Students
$syYear = 'SY';
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS c FROM students WHERE year = ?");
mysqli_stmt_bind_param($stmt, "s", $syYear);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$sy = mysqli_fetch_assoc($result)['c'];
mysqli_stmt_close($stmt);

// TY Students
$tyYear = 'TY';
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS c FROM students WHERE year = ?");
mysqli_stmt_bind_param($stmt, "s", $tyYear);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$ty = mysqli_fetch_assoc($result)['c'];
mysqli_stmt_close($stmt);

$total_students = $fy + $sy + $ty;

// Total Teachers
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS c FROM teachers");
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$total_teachers = mysqli_fetch_assoc($result)['c'];
mysqli_stmt_close($stmt);

// Total Subjects
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS c FROM subjects");
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$total_subjects = mysqli_fetch_assoc($result)['c'];
mysqli_stmt_close($stmt);

// Total Marks Entries
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS c FROM student_marks");
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$total_marks = mysqli_fetch_assoc($result)['c'];
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | NEP Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] }
                }
            }
        }
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-gray-50 font-sans text-gray-900">

<div class="flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-gray-200 hidden md:flex flex-col">
        <div class="h-16 flex items-center px-6 border-b border-gray-200">
            <span class="text-xl font-bold text-indigo-600">Admin<span class="text-gray-900">Panel</span></span>
        </div>
        
        <nav class="flex-1 overflow-y-auto py-4">
            <ul class="space-y-1 px-3">
                <li>
                    <a href="admin_dashboard.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-indigo-50 text-indigo-700 font-medium">
                        <i class="bi bi-grid-fill"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="manage_students.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                        <i class="bi bi-people"></i> Students
                    </a>
                </li>
                <li>
                    <a href="manage_teachers.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                        <i class="bi bi-person-badge"></i> Teachers
                    </a>
                </li>
                <li>
                    <a href="manage_subjects.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                        <i class="bi bi-book"></i> Subjects
                    </a>
                </li>
                <li>
                    <a href="manage_marks_credits.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                        <i class="bi bi-award"></i> Marks & Credits
                    </a>
                </li>
                 <li>
                    <a href="reports.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                        <i class="bi bi-bar-chart"></i> Reports
                    </a>
                </li>
                 <li>
                    <a href="announcements.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                        <i class="bi bi-megaphone"></i> Announcements
                    </a>
                </li>
                <li>
                    <a href="view_feedback.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                        <i class="bi bi-chat-right-text"></i> Feedback
                    </a>
                </li>
            </ul>

            <div class="mt-8 px-6 text-xs font-semibold text-gray-400 uppercase tracking-wider">Settings</div>
            <ul class="space-y-1 px-3 mt-2">
                 <li>
                    <a href="profile.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                        <i class="bi bi-person-circle"></i> Profile
                    </a>
                </li>
                <li>
                    <a href="change_password.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                        <i class="bi bi-shield-lock"></i> Change Password
                    </a>
                </li>
                 <li>
                    <a href="admin_logout.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-red-600 hover:bg-red-50 transition">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </li>
            </ul>
        </nav>
        
        <div class="p-4 border-t border-gray-200">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold">
                    <?php echo substr($_SESSION['admin_name'], 0, 1); ?>
                </div>
                <div class="text-sm">
                    <p class="font-medium text-gray-900"><?php echo $_SESSION['admin_name']; ?></p>
                    <p class="text-gray-500 text-xs">Administrator</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        
        <!-- Mobile Header -->
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 md:hidden">
            <span class="text-xl font-bold text-indigo-600">NEP Portal</span>
            <button class="text-gray-500 hover:text-gray-700">
                <i class="bi bi-list text-2xl"></i>
            </button>
        </header>

        <!-- Main Scrollable Area -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
            
            <div class="mb-8 flex justify-between items-end">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Dashboard Overview</h1>
                    <p class="text-gray-500">Welcome back, here's what's happening today.</p>
                </div>
                <div class="hidden md:block">
                     <span class="text-sm text-gray-500 bg-white px-3 py-1 rounded-full border border-gray-200">
                        Today: <?php echo date('d M, Y'); ?>
                     </span>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Students -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-lg bg-blue-50 text-blue-600">
                            <i class="bi bi-people-fill text-xl"></i>
                        </div>
                        <span class="text-green-500 text-sm font-medium flex items-center gap-1">
                            Year 2026
                        </span>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-900 mb-1"><?php echo $total_students; ?></h3>
                    <p class="text-gray-500 text-sm">Total Students</p>
                </div>

                <!-- Total Teachers -->
                 <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-lg bg-purple-50 text-purple-600">
                            <i class="bi bi-person-badge-fill text-xl"></i>
                        </div>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-900 mb-1"><?php echo $total_teachers; ?></h3>
                    <p class="text-gray-500 text-sm">Active Faculty</p>
                </div>

                <!-- Subjects -->
                 <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-lg bg-orange-50 text-orange-600">
                            <i class="bi bi-book-half text-xl"></i>
                        </div>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-900 mb-1"><?php echo $total_subjects; ?></h3>
                    <p class="text-gray-500 text-sm">Total Subjects</p>
                </div>

                <!-- Marks Entries -->
                 <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-lg bg-emerald-50 text-emerald-600">
                            <i class="bi bi-journal-check text-xl"></i>
                        </div>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-900 mb-1"><?php echo $total_marks; ?></h3>
                    <p class="text-gray-500 text-sm">Marks Entries</p>
                </div>
            </div>

            <!-- Quick Actions -->
            <h2 class="text-lg font-bold text-gray-900 mb-4">Quick Actions</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <a href="manage_students.php" class="group bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:border-indigo-500 transition-colors cursor-pointer">
                    <div class="flex items-center gap-4">
                        <div class="p-3 rounded-xl bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                            <i class="bi bi-person-plus-fill text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900">Add New Student</h4>
                            <p class="text-sm text-gray-500">Register a new enrollment</p>
                        </div>
                    </div>
                </a>

                <a href="manage_teachers.php" class="group bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:border-purple-500 transition-colors cursor-pointer">
                    <div class="flex items-center gap-4">
                        <div class="p-3 rounded-xl bg-purple-50 text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                            <i class="bi bi-person-vcard-fill text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900">Add Faculty</h4>
                            <p class="text-sm text-gray-500">Onboard new teachers</p>
                        </div>
                    </div>
                </a>

                <a href="announcements.php" class="group bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:border-orange-500 transition-colors cursor-pointer">
                    <div class="flex items-center gap-4">
                        <div class="p-3 rounded-xl bg-orange-50 text-orange-600 group-hover:bg-orange-600 group-hover:text-white transition-colors">
                            <i class="bi bi-megaphone-fill text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900">Post Notice</h4>
                            <p class="text-sm text-gray-500">Send updates to university</p>
                        </div>
                    </div>
                </a>
            </div>

        </main>
    </div>
</div>

</body>
</html>

