<?php
session_start();
require_once __DIR__ . '/../db.php';

// Session timeout check
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header("Location: student_login.php?timeout=1");
    exit;
}
$_SESSION['last_activity'] = time();

if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit;
}

$student_id = intval($_SESSION['student_id']);
$student_name = $_SESSION['student_name'];

// Fetch student data
$stmt = mysqli_prepare($conn, "SELECT * FROM students WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$student) {
    session_destroy();
    header("Location: student_login.php");
    exit;
}

// Fetch marks with details
$marksStmt = mysqli_prepare($conn, "
    SELECT sm.*, s.subject_name 
    FROM student_marks sm
    LEFT JOIN subjects s ON s.subject_name = sm.subject
    WHERE sm.student_id = ?
    ORDER BY sm.semester DESC, sm.subject
");
mysqli_stmt_bind_param($marksStmt, "i", $student_id);
mysqli_stmt_execute($marksStmt);
$marksResult = mysqli_stmt_get_result($marksStmt);

$allMarks = [];
while ($row = mysqli_fetch_assoc($marksResult)) {
    $allMarks[] = $row;
}
mysqli_stmt_close($marksStmt);

// Calculate CGPA
$cgpaStmt = mysqli_prepare($conn, "
    SELECT 
        AVG(CASE WHEN marks REGEXP '^[0-9]+$' THEN CAST(marks AS UNSIGNED) ELSE NULL END) as avg_marks
    FROM student_marks 
    WHERE student_id = ?
");
mysqli_stmt_bind_param($cgpaStmt, "i", $student_id);
mysqli_stmt_execute($cgpaStmt);
$cgpaResult = mysqli_stmt_get_result($cgpaStmt);
$avgMarks = mysqli_fetch_assoc($cgpaResult)['avg_marks'] ?? 0;
$cgpa = round(($avgMarks / 10), 2); // Simple CGPA calculation
mysqli_stmt_close($cgpaStmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Marks | Student Portal</title>
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
    <style>
        @media print {
            .no-print { display: none !important; }
            .print-full-width { width: 100% !important; margin: 0 !important; }
            body { background: white !important; }
            .shadow-sm, .shadow-lg { box-shadow: none !important; }
            .border { border: 1px solid #ddd !important; }
        }
    </style>
</head>
<body class="bg-gray-50 font-sans text-gray-900">

<div class="flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-gray-200 hidden md:flex flex-col no-print">
        <div class="h-16 flex items-center px-6 border-b border-gray-200">
            <span class="text-xl font-bold text-indigo-600">Student<span class="text-gray-900">Portal</span></span>
        </div>
        
        <nav class="flex-1 overflow-y-auto py-4">
            <ul class="space-y-1 px-3">
                <li>
                    <a href="student_dashboard.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                        <i class="bi bi-grid-fill"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="my_subjects.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                        <i class="bi bi-book-half"></i> My Subjects
                    </a>
                </li>
                <li>
                    <a href="view_marks.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-indigo-50 text-indigo-700 font-medium">
                        <i class="bi bi-clipboard-data"></i> View Marks
                    </a>
                </li>
                <li>
                    <a href="attendance.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                        <i class="bi bi-calendar-check"></i> Attendance
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
                        <i class="bi bi-key"></i> Password
                    </a>
                </li>
                 <li>
                    <a href="logout.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-red-600 hover:bg-red-50 transition">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </li>
            </ul>
        </nav>
        
        <div class="p-4 border-t border-gray-200">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold">
                    <?php echo substr($student_name, 0, 1); ?>
                </div>
                <div class="text-sm">
                    <p class="font-medium text-gray-900"><?php echo htmlspecialchars($student_name); ?></p>
                    <p class="text-gray-500 text-xs">Student</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        
        <!-- Mobile Header -->
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 md:hidden no-print">
            <span class="text-xl font-bold text-indigo-600">NEP Portal</span>
            <button class="text-gray-500 hover:text-gray-700">
                <i class="bi bi-list text-2xl"></i>
            </button>
        </header>

        <!-- Main Scrollable Area -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4 md:p-6 print-full-width">
            
            <div class="max-w-5xl mx-auto">
                <div class="flex items-center justify-between mb-6 no-print">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Academic Performance</h1>
                        <p class="text-gray-500 text-sm">Track your grades and CGPA</p>
                    </div>
                    <button onclick="window.print()" class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition shadow-sm font-medium text-sm">
                        <i class="bi bi-printer"></i> Print Report
                    </button>
                </div>

                <!-- Print Header -->
                <div class="hidden print:block text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Academic Report Card</h1>
                    <p class="text-gray-500 mt-1"><?php echo htmlspecialchars($student_name); ?> | Student ID: <?php echo $student_id; ?></p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    
                    <!-- CGPA Card -->
                    <div class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-2xl p-6 text-white text-center shadow-lg relative overflow-hidden">
                        <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
                        <p class="text-indigo-100 text-sm font-medium uppercase tracking-wide mb-1">Current CGPA</p>
                        <h2 class="text-5xl font-bold mb-1 tracking-tight"><?php echo $cgpa; ?></h2>
                        <div class="inline-block bg-white/20 px-3 py-1 rounded-full text-xs font-medium backdrop-blur-sm mt-2">
                            Out of 10.0
                        </div>
                    </div>

                    <!-- Grading Info -->
                    <div class="md:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <i class="bi bi-info-circle text-indigo-500"></i> Grading Criteria
                        </h3>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            <div class="p-3 rounded-xl bg-green-50 border border-green-100 text-center">
                                <span class="block text-green-700 font-bold text-lg">40+</span>
                                <span class="text-xs text-green-600 font-medium">Pass</span>
                            </div>
                            <div class="p-3 rounded-xl bg-red-50 border border-red-100 text-center">
                                <span class="block text-red-700 font-bold text-lg">&lt;40</span>
                                <span class="text-xs text-red-600 font-medium">Fail</span>
                            </div>
                            <div class="p-3 rounded-xl bg-amber-50 border border-amber-100 text-center">
                                <span class="block text-amber-700 font-bold text-lg">AB</span>
                                <span class="text-xs text-amber-600 font-medium">Absent</span>
                            </div>
                             <div class="p-3 rounded-xl bg-indigo-50 border border-indigo-100 text-center">
                                <span class="block text-indigo-700 font-bold text-lg">100</span>
                                <span class="text-xs text-indigo-600 font-medium">Max Marks</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Marks List -->
                 <?php if (empty($allMarks)): ?>
                    <div class="bg-white p-12 rounded-2xl shadow-sm border border-gray-100 text-center">
                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400 text-2xl">
                            <i class="bi bi-clipboard-x"></i>
                        </div>
                        <h3 class="text-gray-900 font-medium">No Academic Records</h3>
                        <p class="text-gray-500 text-sm mt-1">Marks data has not been uploaded yet.</p>
                    </div>
                <?php else: ?>

                    <?php 
                    $currentSemester = null;
                    foreach ($allMarks as $index => $mark):
                        // Check if semester changed
                        if ($currentSemester !== $mark['semester']):
                            if ($currentSemester !== null) echo '</div></div>'; // Close previous semester group
                            $currentSemester = $mark['semester'];
                    ?>
                        <div class="mb-8 break-inside-avoid">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="bg-indigo-100 text-indigo-600 p-2 rounded-lg">
                                    <i class="bi bi-calendar3"></i>
                                </div>
                                <h2 class="text-xl font-bold text-gray-900">Semester <?php echo $mark['semester']; ?></h2>
                                <div class="h-px bg-gray-200 flex-1 ml-4"></div>
                            </div>
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <?php endif; ?>

                            <div class="p-5 border-b border-gray-100 last:border-b-0 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:bg-gray-50 transition">
                                <div>
                                    <h4 class="font-bold text-gray-900 text-md"><?php echo htmlspecialchars($mark['subject']); ?></h4>
                                    <div class="flex items-center gap-3 mt-1 text-xs text-gray-500">
                                        <span class="bg-gray-100 px-2 py-0.5 rounded text-gray-600 font-medium border border-gray-200">
                                            <?php echo htmlspecialchars($mark['exam_name']); ?>
                                        </span>
                                        <span>•</span>
                                        <span><?php echo $mark['credits']; ?> Credits</span>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-4">
                                    <?php 
                                    $markValue = $mark['marks'];
                                    $statusColor = 'bg-green-100 text-green-700 border-green-200';
                                    $statusText = 'PASS';
                                    
                                    if (in_array(strtoupper($markValue), ['AB', 'UNF'])) {
                                        $statusColor = 'bg-amber-100 text-amber-700 border-amber-200';
                                        $statusText = 'ABSENT';
                                    } elseif (is_numeric($markValue) && $markValue < 40) {
                                        $statusColor = 'bg-red-100 text-red-700 border-red-200';
                                        $statusText = 'FAIL';
                                    }
                                    ?>
                                    
                                    <div class="text-right">
                                        <span class="block text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($markValue); ?></span>
                                        <span class="text-[10px] font-bold uppercase tracking-wider <?php echo $statusColor; ?> px-2 py-0.5 rounded-full border">
                                            <?php echo $statusText; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                    <?php endforeach; ?>
                        </div></div> <!-- Close last group -->
                    
                <?php endif; ?>

            </div>
        </main>
    </div>
</div>

</body>
</html>
