<?php
session_start();
require_once __DIR__ . '/../db.php';

// Session timeout check
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header("Location: faculty_login.php?timeout=1");
    exit;
}
$_SESSION['last_activity'] = time();

if (!isset($_SESSION['teacher_id'])) {
    header("Location: faculty_login.php");
    exit;
}

$teacher_id = intval($_SESSION['teacher_id']);
$student_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($student_id == 0) {
    header("Location: teacher_dashboard.php");
    exit;
}

// Fetch student details
$stmt = mysqli_prepare($conn, "SELECT * FROM students WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$student) {
    header("Location: teacher_dashboard.php");
    exit;
}

// Fetch student marks
$marksStmt = mysqli_prepare($conn, "SELECT * FROM student_marks WHERE student_id = ? ORDER BY semester, subject");
mysqli_stmt_bind_param($marksStmt, "i", $student_id);
mysqli_stmt_execute($marksStmt);
$marksResult = mysqli_stmt_get_result($marksStmt);
$marks = [];
while ($row = mysqli_fetch_assoc($marksResult)) {
    $marks[] = $row;
}
mysqli_stmt_close($marksStmt);

// Fetch student attendance
$attendanceStmt = mysqli_prepare($conn, "
    SELECT 
        subject,
        COUNT(*) as total_classes,
        SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present_count,
        ROUND((SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as percentage
    FROM attendance
    WHERE student_id = ?
    GROUP BY subject
");
mysqli_stmt_bind_param($attendanceStmt, "i", $student_id);
mysqli_stmt_execute($attendanceStmt);
$attendanceResult = mysqli_stmt_get_result($attendanceStmt);
$attendance = [];
while ($row = mysqli_fetch_assoc($attendanceResult)) {
    $attendance[] = $row;
}
mysqli_stmt_close($attendanceStmt);

// Calculate CGPA
$totalCredits = 0;
$totalPoints = 0;
foreach ($marks as $mark) {
    if (is_numeric($mark['marks']) && $mark['marks'] >= 40) {
        $totalCredits += $mark['credits'];
        $gradePoint = ($mark['marks'] >= 90) ? 10 : (($mark['marks'] >= 80) ? 9 : (($mark['marks'] >= 70) ? 8 : (($mark['marks'] >= 60) ? 7 : (($mark['marks'] >= 50) ? 6 : 5))));
        $totalPoints += $gradePoint * $mark['credits'];
    }
}
$cgpa = $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Details | Faculty Portal</title>
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
            <span class="text-xl font-bold text-green-600">Teacher<span class="text-gray-900">Panel</span></span>
        </div>
        
        <nav class="flex-1 overflow-y-auto py-4">
            <ul class="space-y-1 px-3">
                <li>
                    <a href="teacher_dashboard.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                        <i class="bi bi-grid-fill"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="mark_attendance.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                        <i class="bi bi-calendar-check"></i> Mark Attendance
                    </a>
                </li>
                <li>
                    <a href="view_attendance.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                        <i class="bi bi-graph-up"></i> View Attendance
                    </a>
                </li>
                <li>
                    <a href="semester_mark_entry.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                        <i class="bi bi-pencil-square"></i> Enter Marks
                    </a>
                </li>
                <li>
                    <a href="view_marks.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                        <i class="bi bi-clipboard-data"></i> View Marks
                    </a>
                </li>
                 <li>
                    <a href="announcements.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                        <i class="bi bi-megaphone"></i> Notices
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
                    <a href="teacher_logout.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-red-600 hover:bg-red-50 transition">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </li>
            </ul>
        </nav>
        
        <div class="p-4 border-t border-gray-200">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-green-600 font-bold">
                    <?php echo substr($_SESSION['teacher_name'], 0, 1); ?>
                </div>
                <div class="text-sm">
                    <p class="font-medium text-gray-900"><?php echo $_SESSION['teacher_name']; ?></p>
                    <p class="text-gray-500 text-xs">Faculty Member</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        
        <!-- Mobile Header -->
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 md:hidden">
            <span class="text-xl font-bold text-green-600">NEP Portal</span>
            <button class="text-gray-500 hover:text-gray-700">
                <i class="bi bi-list text-2xl"></i>
            </button>
        </header>

        <!-- Main Scrollable Area -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4 md:p-6">
            
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Student Profile</h1>
                    <p class="text-gray-500 text-sm">Detailed academic and personal information.</p>
                </div>
                <a href="teacher_dashboard.php" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 text-sm font-medium transition">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
            </div>

            <!-- Profile Header Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mb-8 text-center md:text-left flex flex-col md:flex-row items-center gap-8 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-24 bg-gradient-to-r from-green-500 to-emerald-600 opacity-10"></div>
                
                <div class="relative w-24 h-24 rounded-full bg-white border-4 border-white shadow-lg flex items-center justify-center text-3xl font-bold text-green-600 z-10">
                    <i class="bi bi-person-fill"></i>
                </div>
                
                <div class="flex-1 z-10 text-center md:text-left">
                    <h2 class="text-2xl font-bold text-gray-900 mb-1"><?php echo htmlspecialchars($student['name']); ?></h2>
                    <p class="text-gray-500 mb-4"><?php echo htmlspecialchars($student['email']); ?></p>
                    
                    <div class="flex flex-wrap gap-2 justify-center md:justify-start">
                        <span class="px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-bold border border-blue-100">
                            <?php echo $student['year']; ?> Student
                        </span>
                        <span class="px-3 py-1 rounded-full bg-green-50 text-green-700 text-xs font-bold border border-green-100">
                            CGPA: <?php echo $cgpa; ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                <!-- Basic Info -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 h-full">
                        <h3 class="font-bold text-gray-800 mb-6 flex items-center gap-2">
                            <i class="bi bi-info-circle text-green-600"></i> Basic Information
                        </h3>
                        
                        <div class="space-y-4">
                            <div class="pb-3 border-b border-gray-50">
                                <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Student ID</p>
                                <p class="text-gray-900 font-medium"><?php echo $student['id']; ?></p>
                            </div>
                            <div class="pb-3 border-b border-gray-50">
                                <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Email</p>
                                <p class="text-gray-900 font-medium break-all"><?php echo htmlspecialchars($student['email']); ?></p>
                            </div>
                            <div class="pb-3 border-b border-gray-50">
                                <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Mobile</p>
                                <p class="text-gray-900 font-medium"><?php echo htmlspecialchars($student['mobile'] ?? 'Not provided'); ?></p>
                            </div>
                            <div class="pb-3 border-b border-gray-50">
                                <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">ABC ID</p>
                                <p class="text-gray-900 font-medium"><?php echo htmlspecialchars($student['abc_id'] ?? 'Not provided'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Marks Table -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden h-full flex flex-col">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                                <i class="bi bi-clipboard-data text-green-600"></i> Academic Performance
                            </h3>
                        </div>
                        
                        <div class="overflow-x-auto flex-1">
                            <?php if (empty($marks)): ?>
                            <div class="p-8 text-center text-gray-500">
                                <i class="bi bi-journal-x text-4xl mb-3 block text-gray-300"></i>
                                No marks records found.
                            </div>
                            <?php else: ?>
                            <table class="w-full text-left border-collapse">
                                <thead class="bg-gray-50 text-gray-500 font-medium border-b border-gray-100">
                                    <tr>
                                        <th class="px-6 py-3 text-xs uppercase tracking-wider">Exam Info</th>
                                        <th class="px-6 py-3 text-xs uppercase tracking-wider">Subject</th>
                                        <th class="px-6 py-3 text-xs uppercase tracking-wider">Marks</th>
                                        <th class="px-6 py-3 text-xs uppercase tracking-wider">Credits</th>
                                        <th class="px-6 py-3 text-xs uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    <?php foreach ($marks as $mark): ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4">
                                            <div class="flex flex-col">
                                                <span class="font-bold text-gray-900">Sem <?php echo $mark['semester']; ?></span>
                                                <span class="text-xs text-gray-500"><?php echo htmlspecialchars($mark['exam_name']); ?></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-gray-600 font-medium text-sm">
                                            <?php echo htmlspecialchars($mark['subject']); ?>
                                        </td>
                                        <td class="px-6 py-4 font-bold text-gray-900"><?php echo $mark['marks']; ?></td>
                                        <td class="px-6 py-4 text-gray-600"><?php echo $mark['credits']; ?></td>
                                        <td class="px-6 py-4">
                                            <?php if (is_numeric($mark['marks']) && $mark['marks'] >= 40): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Pass</span>
                                            <?php elseif (is_numeric($mark['marks'])): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Fail</span>
                                            <?php else: ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800"><?php echo $mark['marks']; ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Summary -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="font-bold text-gray-800 flex items-center gap-2">
                        <i class="bi bi-calendar-check text-green-600"></i> Attendance Summary
                    </h3>
                </div>
                
                <div class="overflow-x-auto">
                    <?php if (empty($attendance)): ?>
                    <div class="p-8 text-center text-gray-500">
                        <i class="bi bi-calendar-x text-4xl mb-3 block text-gray-300"></i>
                         No attendance records found.
                    </div>
                    <?php else: ?>
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 text-gray-500 font-medium">
                            <tr>
                                <th class="px-6 py-3 whitespace-nowrap">Subject</th>
                                <th class="px-6 py-3 whitespace-nowrap">Classes Attended</th>
                                <th class="px-6 py-3 whitespace-nowrap">Total Classes</th>
                                <th class="px-6 py-3 whitespace-nowrap">Attendance %</th>
                                <th class="px-6 py-3 w-1/3">Progress</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($attendance as $att): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-medium text-gray-900"><?php echo htmlspecialchars($att['subject']); ?></td>
                                <td class="px-6 py-4 text-gray-600"><?php echo $att['present_count']; ?></td>
                                <td class="px-6 py-4 text-gray-600"><?php echo $att['total_classes']; ?></td>
                                <td class="px-6 py-4 font-bold <?php echo $att['percentage'] >= 75 ? 'text-green-600' : ($att['percentage'] >= 65 ? 'text-yellow-600' : 'text-red-600'); ?>">
                                    <?php echo $att['percentage']; ?>%
                                </td>
                                <td class="px-6 py-4">
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="h-2.5 rounded-full <?php echo $att['percentage'] >= 75 ? 'bg-green-500' : ($att['percentage'] >= 65 ? 'bg-yellow-500' : 'bg-red-500'); ?>" style="width: <?php echo $att['percentage']; ?>%"></div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>

        </main>
    </div>
</div>

</body>
</html>
