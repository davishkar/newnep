<?php
session_start();
require_once __DIR__ . '/../db.php';

// Session timeout check
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

// Get statistics
// Total students by year
$fyStmt = mysqli_prepare($conn, "SELECT COUNT(*) as c FROM students WHERE year = 'FY'");
mysqli_stmt_execute($fyStmt);
$fy = mysqli_fetch_assoc(mysqli_stmt_get_result($fyStmt))['c'];

$syStmt = mysqli_prepare($conn, "SELECT COUNT(*) as c FROM students WHERE year = 'SY'");
mysqli_stmt_execute($syStmt);
$sy = mysqli_fetch_assoc(mysqli_stmt_get_result($syStmt))['c'];

$tyStmt = mysqli_prepare($conn, "SELECT COUNT(*) as c FROM students WHERE year = 'TY'");
mysqli_stmt_execute($tyStmt);
$ty = mysqli_fetch_assoc(mysqli_stmt_get_result($tyStmt))['c'];

// Pass/Fail statistics
$passStmt = mysqli_prepare($conn, "SELECT COUNT(*) as c FROM student_marks WHERE marks >= 40");
mysqli_stmt_execute($passStmt);
$passCount = mysqli_fetch_assoc(mysqli_stmt_get_result($passStmt))['c'];

$failStmt = mysqli_prepare($conn, "SELECT COUNT(*) as c FROM student_marks WHERE marks < 40 AND marks IS NOT NULL");
mysqli_stmt_execute($failStmt);
$failCount = mysqli_fetch_assoc(mysqli_stmt_get_result($failStmt))['c'];

// Average marks
$avgStmt = mysqli_prepare($conn, "SELECT AVG(marks) as avg FROM student_marks WHERE marks IS NOT NULL");
mysqli_stmt_execute($avgStmt);
$avgMarks = round(mysqli_fetch_assoc(mysqli_stmt_get_result($avgStmt))['avg'] ?? 0, 2);

// Attendance statistics
$totalAttStmt = mysqli_prepare($conn, "SELECT COUNT(*) as c FROM attendance");
mysqli_stmt_execute($totalAttStmt);
$totalAttendance = mysqli_fetch_assoc(mysqli_stmt_get_result($totalAttStmt))['c'];

$presentStmt = mysqli_prepare($conn, "SELECT COUNT(*) as c FROM attendance WHERE status = 'Present'");
mysqli_stmt_execute($presentStmt);
$presentCount = mysqli_fetch_assoc(mysqli_stmt_get_result($presentStmt))['c'];

$avgAttendance = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100, 2) : 0;

// Subject-wise marks
$subjectStmt = mysqli_prepare($conn, "SELECT subject, AVG(marks) as avg_marks, COUNT(*) as total FROM student_marks WHERE marks IS NOT NULL GROUP BY subject ORDER BY avg_marks DESC LIMIT 10");
mysqli_stmt_execute($subjectStmt);
$subjectMarks = mysqli_stmt_get_result($subjectStmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics | Admin Panel</title>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    <a href="admin_dashboard.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
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
                    <a href="reports.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-indigo-50 text-indigo-700 font-medium">
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
                    A
                </div>
                <div class="text-sm">
                    <p class="font-medium text-gray-900">Admin</p>
                    <p class="text-gray-500 text-xs">Administrator</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        
        <!-- Mobile Header -->
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 md:hidden">
            <span class="text-xl font-bold text-indigo-600">AdminPanel</span>
            <button class="text-gray-500 hover:text-gray-700">
                <i class="bi bi-list text-2xl"></i>
            </button>
        </header>

        <!-- Main Scrollable Area -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
            
            <div class="max-w-7xl mx-auto">
                
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Reports & Analytics</h1>
                        <p class="text-gray-600 text-sm mt-1">Key metrics, student performance, and insights.</p>
                    </div>
                    <a href="admin_dashboard.php" class="hidden sm:inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        <i class="bi bi-arrow-left"></i> Back to Dashboard
                    </a>
                </div>

                <!-- STATISTICS GRID -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Students</p>
                                <h3 class="text-2xl font-bold text-gray-900 mt-1"><?php echo $fy + $sy + $ty; ?></h3>
                            </div>
                            <div class="p-2 bg-indigo-100 rounded-lg text-indigo-600">
                                <i class="bi bi-people text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                         <div class="flex justify-between items-start mb-4">
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Average Marks</p>
                                <h3 class="text-2xl font-bold text-gray-900 mt-1"><?php echo $avgMarks; ?></h3>
                            </div>
                            <div class="p-2 bg-blue-100 rounded-lg text-blue-600">
                                <i class="bi bi-graph-up text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                         <div class="flex justify-between items-start mb-4">
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Pass Count</p>
                                <h3 class="text-2xl font-bold text-gray-900 mt-1"><?php echo $passCount; ?></h3>
                            </div>
                            <div class="p-2 bg-green-100 rounded-lg text-green-600">
                                <i class="bi bi-check-circle text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                         <div class="flex justify-between items-start mb-4">
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Attendance</p>
                                <h3 class="text-2xl font-bold text-gray-900 mt-1"><?php echo $avgAttendance; ?>%</h3>
                            </div>
                             <div class="p-2 bg-purple-100 rounded-lg text-purple-600">
                                <i class="bi bi-calendar-check text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CHARTS ROW -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                             <div class="p-1.5 rounded bg-indigo-100 text-indigo-600"><i class="bi bi-pie-chart-fill"></i></div>
                             Student Distribution by Year
                        </h3>
                        <div class="h-64 flex justify-center">
                            <canvas id="studentChart"></canvas>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                             <div class="p-1.5 rounded bg-green-100 text-green-600"><i class="bi bi-pie-chart-fill"></i></div>
                             Pass/Fail Distribution
                        </h3>
                         <div class="h-64 flex justify-center">
                            <canvas id="passFailChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- SUBJECT-WISE MARKS -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                        <h3 class="font-bold text-gray-800 flex items-center gap-2">
                            <i class="bi bi-bar-chart-fill text-gray-500"></i> Subject-wise Average Marks
                        </h3>
                    </div>
                    <?php if (mysqli_num_rows($subjectMarks) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3 font-semibold">Subject</th>
                                    <th class="px-6 py-3 font-semibold">Average Marks</th>
                                    <th class="px-6 py-3 font-semibold">Total Entries</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($subjectMarks)): ?>
                                <tr class="bg-white border-b hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 font-medium text-gray-900"><?php echo htmlspecialchars($row['subject']); ?></td>
                                    <td class="px-6 py-4 font-bold text-gray-900"><?php echo round($row['avg_marks'], 2); ?></td>
                                    <td class="px-6 py-4 text-gray-600"><?php echo $row['total']; ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="p-8 text-center text-gray-500">
                        <i class="bi bi-info-circle text-4xl mb-2 block"></i>
                        No marks data available yet.
                    </div>
                    <?php endif; ?>
                </div>

                </div>

                <!-- EXPORT & PRINT SECTION -->
                <div class="mt-8 bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                <i class="bi bi-cloud-download text-indigo-600"></i> Export Data
                            </h3>
                            <p class="text-gray-500 text-sm mt-1">Download database records in CSV format or print this report.</p>
                        </div>
                        <button onclick="window.print()" class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition flex items-center gap-2">
                            <i class="bi bi-printer"></i> Print / Save PDF
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a href="export_data.php?type=students" class="flex items-center justify-between p-4 bg-indigo-50 rounded-xl border border-indigo-100 hover:bg-indigo-100 transition group">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-indigo-200 text-indigo-700 flex items-center justify-center">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-indigo-900">Students Data</h4>
                                    <p class="text-xs text-indigo-600">Export as CSV</p>
                                </div>
                            </div>
                            <i class="bi bi-download text-indigo-400 group-hover:text-indigo-600"></i>
                        </a>

                        <a href="export_data.php?type=teachers" class="flex items-center justify-between p-4 bg-purple-50 rounded-xl border border-purple-100 hover:bg-purple-100 transition group">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-purple-200 text-purple-700 flex items-center justify-center">
                                    <i class="bi bi-person-badge-fill"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-purple-900">Teachers Data</h4>
                                    <p class="text-xs text-purple-600">Export as CSV</p>
                                </div>
                            </div>
                            <i class="bi bi-download text-purple-400 group-hover:text-purple-600"></i>
                        </a>

                        <a href="export_data.php?type=marks" class="flex items-center justify-between p-4 bg-green-50 rounded-xl border border-green-100 hover:bg-green-100 transition group">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-green-200 text-green-700 flex items-center justify-center">
                                    <i class="bi bi-table"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-green-900">Marks & Results</h4>
                                    <p class="text-xs text-green-600">Export as CSV</p>
                                </div>
                            </div>
                            <i class="bi bi-download text-green-400 group-hover:text-green-600"></i>
                        </a>
                    </div>
                </div>

            </div>
            
        </main>
    </div>
</div>

<script>
// Student Distribution Chart
const studentCtx = document.getElementById('studentChart').getContext('2d');
new Chart(studentCtx, {
    type: 'doughnut',
    data: {
        labels: ['First Year (FY)', 'Second Year (SY)', 'Third Year (TY)'],
        datasets: [{
            data: [<?php echo $fy; ?>, <?php echo $sy; ?>, <?php echo $ty; ?>],
            backgroundColor: ['#4f46e5', '#6366f1', '#818cf8'],
            borderWidth: 0,
            hoverOffset: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '70%',
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    usePointStyle: true,
                    padding: 20,
                    font: { family: 'Inter', size: 12 }
                }
            }
        }
    }
});

// Pass/Fail Chart
const passFailCtx = document.getElementById('passFailChart').getContext('2d');
new Chart(passFailCtx, {
    type: 'doughnut',
    data: {
        labels: ['Pass', 'Fail'],
        datasets: [{
            data: [<?php echo $passCount; ?>, <?php echo $failCount; ?>],
            backgroundColor: ['#10b981', '#ef4444'],
            borderWidth: 0,
            hoverOffset: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '70%',
        plugins: {
            legend: {
                position: 'bottom',
                 labels: {
                    usePointStyle: true,
                    padding: 20,
                    font: { family: 'Inter', size: 12 }
                }
            }
        }
    }
});
</script>

</body>
</html>
