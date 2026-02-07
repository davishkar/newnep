<?php
session_start();
include "../db.php";

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

// Fetch attendance data
$attendanceStmt = mysqli_prepare($conn, "
    SELECT 
        subject,
        COUNT(*) as total_classes,
        SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present_count,
        SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent_count,
        ROUND((SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as percentage
    FROM attendance
    WHERE student_id = ?
    GROUP BY subject
");
mysqli_stmt_bind_param($attendanceStmt, "i", $student_id);
mysqli_stmt_execute($attendanceStmt);
$attendanceResult = mysqli_stmt_get_result($attendanceStmt);

$attendanceData = [];
$totalPresent = 0;
$totalClasses = 0;

while ($row = mysqli_fetch_assoc($attendanceResult)) {
    $attendanceData[] = $row;
    $totalPresent += $row['present_count'];
    $totalClasses += $row['total_classes'];
}
mysqli_stmt_close($attendanceStmt);

$overallPercentage = $totalClasses > 0 ? round(($totalPresent / $totalClasses) * 100, 2) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance | Student Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    <a href="view_marks.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                        <i class="bi bi-clipboard-data"></i> View Marks
                    </a>
                </li>
                <li>
                    <a href="attendance.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-indigo-50 text-indigo-700 font-medium">
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
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 md:hidden">
            <span class="text-xl font-bold text-indigo-600">NEP Portal</span>
            <button class="text-gray-500 hover:text-gray-700">
                <i class="bi bi-list text-2xl"></i>
            </button>
        </header>

        <!-- Main Scrollable Area -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4 md:p-6">
            
            <div class="max-w-7xl mx-auto">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Attendance Tracker</h1>
                        <p class="text-gray-500 text-sm">Monitor your academic presence</p>
                    </div>
                </div>

                <!-- Overall Attendance & Guidelines -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    
                    <!-- Overall Stats -->
                    <div class="bg-gradient-to-br from-indigo-600 to-blue-600 rounded-2xl p-6 text-white text-center shadow-lg relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl"></div>
                        <p class="text-indigo-100 text-sm font-medium mb-2">Overall Attendance</p>
                        <h2 class="text-5xl font-bold mb-2"><?php echo $overallPercentage; ?>%</h2>
                        <p class="text-indigo-100 text-sm"><?php echo $totalPresent; ?> / <?php echo $totalClasses; ?> Classes Attended</p>
                    </div>

                    <!-- Guidelines -->
                    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <i class="bi bi-info-circle text-indigo-500"></i> Attendance Guidelines
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="p-4 rounded-xl bg-green-50 border border-green-100 text-center">
                                <span class="block text-green-700 font-bold text-lg mb-1">75%+</span>
                                <span class="text-xs text-green-600 font-medium">Safe Zone</span>
                                <p class="text-xs text-gray-500 mt-1">Eligible for exams</p>
                            </div>
                            <div class="p-4 rounded-xl bg-yellow-50 border border-yellow-100 text-center">
                                <span class="block text-yellow-700 font-bold text-lg mb-1">65-74%</span>
                                <span class="text-xs text-yellow-600 font-medium">Warning Zone</span>
                                <p class="text-xs text-gray-500 mt-1">Improve attendance</p>
                            </div>
                            <div class="p-4 rounded-xl bg-red-50 border border-red-100 text-center">
                                <span class="block text-red-700 font-bold text-lg mb-1">&lt;65%</span>
                                <span class="text-xs text-red-600 font-medium">Critical Zone</span>
                                <p class="text-xs text-gray-500 mt-1">Risk of detention</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alerts -->
                <?php if ($overallPercentage < 75 && $overallPercentage >= 65): ?>
                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6 rounded-r-lg flex items-center">
                    <i class="bi bi-exclamation-triangle-fill text-yellow-500 text-xl mr-3"></i>
                    <div>
                        <p class="text-sm text-yellow-700 font-bold">Warning</p>
                        <p class="text-sm text-yellow-600">Your overall attendance is below 75%. Please improve your attendance to avoid issues.</p>
                    </div>
                </div>
                <?php elseif ($overallPercentage < 65): ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg flex items-center">
                    <i class="bi bi-x-circle-fill text-red-500 text-xl mr-3"></i>
                    <div>
                        <p class="text-sm text-red-700 font-bold">Critical Alert</p>
                        <p class="text-sm text-red-600">Your attendance is critically low! You may not be eligible for exams. Contact your faculty immediately.</p>
                    </div>
                </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    
                    <!-- Subject-wise List -->
                    <div class="space-y-4">
                        <h3 class="font-bold text-gray-900 mb-2 flex items-center gap-2">
                            <i class="bi bi-list-check text-indigo-500"></i> Subject-wise Details
                        </h3>
                        
                        <?php if (empty($attendanceData)): ?>
                            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 text-center">
                                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400 text-2xl">
                                    <i class="bi bi-calendar-x"></i>
                                </div>
                                <h3 class="text-gray-900 font-medium">No Attendance Records</h3>
                                <p class="text-gray-500 text-sm mt-1">Attendance data has not been uploaded yet.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($attendanceData as $attendance): 
                                $percentage = $attendance['percentage'];
                                $badgeColor = 'bg-green-100 text-green-700';
                                $progressColor = 'bg-green-500';
                                
                                if ($percentage < 75 && $percentage >= 65) {
                                    $badgeColor = 'bg-yellow-100 text-yellow-700';
                                    $progressColor = 'bg-yellow-500';
                                } elseif ($percentage < 65) {
                                    $badgeColor = 'bg-red-100 text-red-700';
                                    $progressColor = 'bg-red-500';
                                }
                            ?>
                            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition duration-200">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h4 class="font-bold text-gray-900"><?php echo htmlspecialchars($attendance['subject']); ?></h4>
                                        <div class="flex items-center gap-3 mt-1 text-xs text-gray-500">
                                            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-green-500"></span> <?php echo $attendance['present_count']; ?> Present</span>
                                            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-500"></span> <?php echo $attendance['absent_count']; ?> Absent</span>
                                        </div>
                                    </div>
                                    <span class="px-3 py-1 rounded-full text-sm font-bold <?php echo $badgeColor; ?>">
                                        <?php echo $percentage; ?>%
                                    </span>
                                </div>
                                
                                <div class="w-full bg-gray-100 rounded-full h-2.5 mb-1 overflow-hidden">
                                    <div class="<?php echo $progressColor; ?> h-2.5 rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                                <p class="text-right text-xs text-gray-400">Total Classes: <?php echo $attendance['total_classes']; ?></p>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Chart Section -->
                    <div>
                        <h3 class="font-bold text-gray-900 mb-2 flex items-center gap-2">
                            <i class="bi bi-bar-chart-fill text-indigo-500"></i> Visual Comparison
                        </h3>
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 h-fit">
                            <?php if (!empty($attendanceData)): ?>
                                <canvas id="attendanceChart"></canvas>
                            <?php else: ?>
                                <div class="text-center py-12 text-gray-400 text-sm">No data available for chart</div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>

            </div>
        </main>
    </div>
</div>

<?php if (!empty($attendanceData)): ?>
<script>
const ctx = document.getElementById('attendanceChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($attendanceData, 'subject')); ?>,
        datasets: [{
            label: 'Attendance %',
            data: <?php echo json_encode(array_column($attendanceData, 'percentage')); ?>,
            backgroundColor: <?php echo json_encode(array_map(function($p) {
                if ($p >= 75) return 'rgba(34, 197, 94, 0.8)'; // green-500
                if ($p >= 65) return 'rgba(234, 179, 8, 0.8)'; // yellow-500
                return 'rgba(239, 68, 68, 0.8)'; // red-500
            }, array_column($attendanceData, 'percentage'))); ?>,
            borderRadius: 6,
            barThickness: 30
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(17, 24, 39, 0.9)',
                padding: 12,
                cornerRadius: 8,
                callbacks: {
                    label: function(context) {
                        return context.parsed.y + '% Attendance';
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                max: 100,
                grid: { color: '#f3f4f6' },
                ticks: { font: { family: 'Inter' } }
            },
            x: {
                grid: { display: false },
                ticks: { font: { family: 'Inter' } }
            }
        }
    }
});
</script>
<?php endif; ?>

</body>
</html>
