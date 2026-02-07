<?php
session_start();
require_once __DIR__ . '/../db.php';

// Session timeout check (30 minutes)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header("Location: student_login.php?timeout=1");
    exit;
}
$_SESSION['last_activity'] = time();

// Check if logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit;
}

$student_id = intval($_SESSION['student_id']);
$student_name = $_SESSION['student_name'];

// Fetch student data using prepared statement
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

$subjects = json_decode($student['subjects'], true) ?? [];

// Fetch REAL marks from database
$marksStmt = mysqli_prepare($conn, "SELECT exam_name, AVG(marks) as avg_marks FROM student_marks WHERE student_id = ? AND marks REGEXP '^[0-9]+$' GROUP BY exam_name LIMIT 4");
mysqli_stmt_bind_param($marksStmt, "i", $student_id);
mysqli_stmt_execute($marksStmt);
$marksResult = mysqli_stmt_get_result($marksStmt);

$marks = [];
while ($row = mysqli_fetch_assoc($marksResult)) {
    $marks[$row['exam_name']] = round($row['avg_marks']);
}
mysqli_stmt_close($marksStmt);

// If no marks found, use placeholder
if (empty($marks)) {
    $marks = ["No Data" => 0];
}

// Fetch total credits using prepared statement
$creditStmt = mysqli_prepare($conn, "SELECT SUM(credits) as total FROM student_marks WHERE student_id = ?");
mysqli_stmt_bind_param($creditStmt, "i", $student_id);
mysqli_stmt_execute($creditStmt);
$creditResult = mysqli_stmt_get_result($creditStmt);
$credits = mysqli_fetch_assoc($creditResult)['total'] ?? 0;
mysqli_stmt_close($creditStmt);

// Fetch semester-wise performance for line chart
$semesterStmt = mysqli_prepare($conn, "SELECT semester, AVG(CAST(marks AS UNSIGNED)) as avg_marks FROM student_marks WHERE student_id = ? AND marks REGEXP '^[0-9]+$' GROUP BY semester ORDER BY semester");
mysqli_stmt_bind_param($semesterStmt, "i", $student_id);
mysqli_stmt_execute($semesterStmt);
$semesterResult = mysqli_stmt_get_result($semesterStmt);

$semesterData = [];
while ($row = mysqli_fetch_assoc($semesterResult)) {
    $semesterData[] = round($row['avg_marks']);
}
mysqli_stmt_close($semesterStmt);

// If no semester data, use placeholder
if (empty($semesterData)) {
    $semesterData = [0];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | NEP Portal</title>
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
                    <a href="student_dashboard.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-indigo-50 text-indigo-700 font-medium">
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
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 md:hidden">
            <span class="text-xl font-bold text-indigo-600">NEP Portal</span>
            <button class="text-gray-500 hover:text-gray-700">
                <i class="bi bi-list text-2xl"></i>
            </button>
        </header>

        <!-- Main Scrollable Area -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
            
            <div class="mb-8 bg-gradient-to-r from-indigo-600 to-blue-600 rounded-3xl p-8 text-white shadow-lg relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl"></div>
                <div class="relative z-10">
                    <h1 class="text-3xl font-bold mb-2">👋 Welcome back, <?php echo htmlspecialchars($student['name']); ?>!</h1>
                    <p class="text-indigo-100">Year: <?php echo $student['year']; ?> | Semester: <?php echo isset($currentSemester) ? $currentSemester : 'N/A'; ?></p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                
                <!-- Left Column: Notices & Info -->
                <div class="lg:col-span-2 space-y-8">
                    
                     <!-- Stats Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                            <i class="bi bi-phone text-indigo-500 text-xl mb-1 block"></i>
                            <p class="text-xs text-gray-500">Mobile</p>
                            <p class="font-bold text-gray-900 text-sm truncate"><?php echo $student['mobile']; ?></p>
                        </div>
                        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                            <i class="bi bi-credit-card-2-front text-purple-500 text-xl mb-1 block"></i>
                             <p class="text-xs text-gray-500">ABC ID</p>
                            <p class="font-bold text-gray-900 text-sm truncate"><?php echo $student['abc_id']; ?></p>
                        </div>
                        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                            <i class="bi bi-award-fill text-yellow-500 text-xl mb-1 block"></i>
                             <p class="text-xs text-gray-500">Credits</p>
                            <p class="font-bold text-gray-900 text-sm truncate"><?php echo $credits; ?></p>
                        </div>
                        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                            <i class="bi bi-person-badge text-green-500 text-xl mb-1 block"></i>
                             <p class="text-xs text-gray-500">ID</p>
                            <p class="font-bold text-gray-900 text-sm truncate"><?php echo $student['student_id'] ?? $student['id']; ?></p>
                        </div>
                    </div>

                    <!-- Notices -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                             <div class="p-1.5 rounded bg-yellow-100 text-yellow-600">
                                <i class="bi bi-megaphone-fill"></i>
                            </div>
                            <h3 class="font-bold text-gray-800">Latest Announcements</h3>
                        </div>
                        <div class="p-6 space-y-4">
                             <?php
                                $target_year = $student['year'];
                                $noticeStmt = mysqli_prepare($conn, "SELECT * FROM notices WHERE target_year = 'All' OR target_year = ? ORDER BY created_at DESC LIMIT 3");
                                mysqli_stmt_bind_param($noticeStmt, "s", $target_year);
                                mysqli_stmt_execute($noticeStmt);
                                $notices = mysqli_stmt_get_result($noticeStmt);
                                
                                if (mysqli_num_rows($notices) > 0) {
                                    while ($n = mysqli_fetch_assoc($notices)) {
                                        echo "<div class='bg-gray-50 p-4 rounded-xl border border-gray-100'>
                                                <div class='flex justify-between items-start mb-1'>
                                                    <h4 class='font-bold text-gray-900 text-sm'>".htmlspecialchars($n['title'])."</h4>
                                                    <span class='text-xs text-gray-400'>".date('d M', strtotime($n['created_at']))."</span>
                                                </div>
                                                <p class='text-sm text-gray-600 mb-2'>".nl2br(htmlspecialchars($n['message']))."</p>
                                                <p class='text-xs text-indigo-500 font-medium'>By: {$n['posted_by']}</p>
                                              </div>";
                                    }
                                } else {
                                    echo "<div class='text-center py-6 text-gray-500 text-sm'>No new announcements posted yet.</div>";
                                }
                                ?>
                        </div>
                    </div>

                    <!-- Charts Row -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Pie Chart -->
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                            <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <i class="bi bi-pie-chart-fill text-indigo-500"></i> Marks Distribution
                            </h3>
                            <canvas id="pie"></canvas>
                        </div>
                        
                        <!-- Line Chart -->
                         <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                            <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <i class="bi bi-graph-up-arrow text-green-500"></i> Academic Growth
                            </h3>
                            <canvas id="line"></canvas>
                        </div>
                    </div>

                </div>

                <!-- Right Column: Quick Actions & Subjects -->
                <div class="space-y-8">
                    
                    <!-- Quick Actions -->
                    <div>
                        <h3 class="font-bold text-gray-900 mb-4">Quick Actions</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <a href="view_marks.php" class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-green-500 transition group text-center block">
                                <i class="bi bi-clipboard-data text-2xl text-green-500 mb-2 block group-hover:scale-110 transition-transform"></i>
                                <span class="text-sm font-medium text-gray-700">View Results</span>
                            </a>
                             <a href="attendance.php" class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-yellow-500 transition group text-center block">
                                <i class="bi bi-calendar-check text-2xl text-yellow-500 mb-2 block group-hover:scale-110 transition-transform"></i>
                                <span class="text-sm font-medium text-gray-700">Attendance</span>
                            </a>
                             <a href="profile.php" class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-purple-500 transition group text-center block">
                                <i class="bi bi-person-circle text-2xl text-purple-500 mb-2 block group-hover:scale-110 transition-transform"></i>
                                <span class="text-sm font-medium text-gray-700">Edit Profile</span>
                            </a>
                             <a href="change_password.php" class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-red-500 transition group text-center block">
                                <i class="bi bi-key text-2xl text-red-500 mb-2 block group-hover:scale-110 transition-transform"></i>
                                <span class="text-sm font-medium text-gray-700">Password</span>
                            </a>
                        </div>
                    </div>

                    <!-- Subjects List -->
                     <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center gap-2 mb-4 pb-2 border-b border-gray-100">
                             <i class="bi bi-book-half text-indigo-500"></i>
                             <h3 class="font-bold text-gray-900">My Subjects</h3>
                        </div>
                        <div class="space-y-3">
                            <?php 
                            if (!empty($subjects)) {
                                foreach($subjects as $k=>$v) {
                                    echo "<div class='flex items-start gap-3 p-3 rounded-xl bg-gray-50 hover:bg-gray-100 transition'>
                                            <div class='mt-1 text-indigo-600'><i class='bi bi-journal-check'></i></div>
                                            <div>
                                                <h4 class='font-bold text-gray-900 text-sm'>$k</h4>
                                                <p class='text-xs text-gray-500'>$v</p>
                                            </div>
                                          </div>";
                                } 
                            } else {
                                echo "<p class='text-sm text-gray-500'>No subjects assigned yet.</p>";
                            }
                            ?>
                        </div>
                    </div>

                    <!-- Exit Option -->
                    <div class="bg-red-50 rounded-2xl p-6 border border-red-100 text-center">
                        <h3 class="font-bold text-red-800 mb-2 flex items-center justify-center gap-2">
                             <i class="bi bi-exclamation-triangle-fill"></i> Exit Option
                        </h3>
                        <p class="text-xs text-red-600 mb-4">Leaving the program? Generate your exit certificate here.</p>
                        <button onclick="showCert()" class="w-full py-2.5 rounded-lg bg-red-600 text-white text-sm font-bold hover:bg-red-700 transition shadow-lg shadow-red-500/20">
                            Generate Certificate
                        </button>
                    </div>

                </div>
            </div>

            <!-- Certificate Section (Hidden by default) -->
            <div id="cert" class="hidden mt-8 mb-12 animate-fade-in-up">
                <div class="bg-white p-12 rounded-3xl shadow-xl border-4 border-double border-indigo-200 text-center max-w-2xl mx-auto relative">
                    <div class="absolute top-4 left-4 w-16 h-16 border-t-4 border-l-4 border-indigo-600"></div>
                    <div class="absolute bottom-4 right-4 w-16 h-16 border-b-4 border-r-4 border-indigo-600"></div>
                    
                    <h2 class="text-2xl font-bold text-gray-900 mb-1">SHIVAJI UNIVERSITY, KOLHAPUR</h2>
                    <p class="text-indigo-600 font-medium mb-6">NEP 2020 – Exit Certificate</p>
                    <hr class="border-gray-200 mb-6">
                    
                    <p class="text-gray-700 text-lg leading-relaxed mb-6">
                        This certifies that <strong class="text-gray-900 text-xl"><?php echo $student['name']; ?></strong> has successfully completed
                        <strong class="text-gray-900"><?php echo $student['year']; ?></strong> under the NEP 2020 curriculum keyframe.
                    </p>
                    
                    <div class="grid grid-cols-2 gap-8 text-left max-w-sm mx-auto mb-12">
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">ABC ID</p>
                            <p class="font-mono font-bold text-gray-900"><?php echo $student['abc_id']; ?></p>
                        </div>
                         <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Date</p>
                            <p class="font-mono font-bold text-gray-900"><?php echo date("d-m-Y"); ?></p>
                        </div>
                    </div>
                    
                    <div class="mt-8 pt-8 border-t border-gray-100 flex justify-end">
                        <div class="text-center">
                            <!-- <img src="signature_placeholder.png" class="h-12 mx-auto opacity-50 mb-2"> -->
                            <div class="h-12 w-32 mx-auto"></div>
                            <p class="font-serif italic text-gray-900">Authorized Signatory</p>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<script>
    // Chart Config
    new Chart(document.getElementById('pie'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_keys($marks)); ?>,
            datasets: [{
                data: <?php echo json_encode(array_values($marks)); ?>,
                backgroundColor: ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } }
            },
            cutout: '70%'
        }
    });

    new Chart(document.getElementById('line'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_map(function($i){return 'Sem '.($i+1);}, array_keys($semesterData))); ?>,
            datasets: [{
                label: 'Average Marks',
                data: <?php echo json_encode(array_values($semesterData)); ?>,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#10b981',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, max: 100, grid: { borderDash: [2, 4] } },
                x: { grid: { display: false } }
            }
        }
    });

    function showCert(){
        const cert = document.getElementById("cert");
        cert.classList.remove("hidden");
        cert.scrollIntoView({ behavior: 'smooth' });
    }
</script>

</body>
</html>
