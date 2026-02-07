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

// Fetch attendance statistics
$selectedSubject = $_GET['subject'] ?? '';
$selectedYear = $_GET['year'] ?? '';
$selectedDate = $_GET['date'] ?? '';

$attendanceData = [];
$query = "SELECT a.*, s.name as student_name, s.email 
          FROM attendance a 
          JOIN students s ON a.student_id = s.id 
          WHERE a.marked_by = ?";

$params = [$teacher_id];
$types = "i";

if (!empty($selectedSubject)) {
    $query .= " AND a.subject = ?";
    $params[] = $selectedSubject;
    $types .= "s";
}

if (!empty($selectedYear)) {
    $query .= " AND s.year = ?";
    $params[] = $selectedYear;
    $types .= "s";
}

if (!empty($selectedDate)) {
    $query .= " AND a.date = ?";
    $params[] = $selectedDate;
    $types .= "s";
}

$query .= " ORDER BY a.date DESC, s.name";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $attendanceData[] = $row;
}
mysqli_stmt_close($stmt);

// Get unique subjects taught by this teacher
$subjectsStmt = mysqli_prepare($conn, "SELECT DISTINCT subject FROM attendance WHERE marked_by = ? ORDER BY subject");
mysqli_stmt_bind_param($subjectsStmt, "i", $teacher_id);
mysqli_stmt_execute($subjectsStmt);
$subjectsResult = mysqli_stmt_get_result($subjectsStmt);
$subjects = [];
while ($row = mysqli_fetch_assoc($subjectsResult)) {
    $subjects[] = $row['subject'];
}
mysqli_stmt_close($subjectsStmt);

// Calculate statistics
$totalRecords = count($attendanceData);
$presentCount = 0;
$absentCount = 0;
$lateCount = 0;

foreach ($attendanceData as $record) {
    if ($record['status'] == 'Present') $presentCount++;
    elseif ($record['status'] == 'Absent') $absentCount++;
    elseif ($record['status'] == 'Late') $lateCount++;
}

$presentPercentage = $totalRecords > 0 ? round(($presentCount / $totalRecords) * 100, 2) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Attendance | Faculty Portal</title>
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
                    <a href="view_attendance.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-green-50 text-green-700 font-medium">
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
            
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Attendance Reports</h1>
                <p class="text-gray-500 text-sm">View and analyze student attendance records.</p>
            </div>

            <!-- Statistics Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center text-center">
                    <div class="p-3 rounded-full bg-indigo-50 text-indigo-600 mb-3">
                        <i class="bi bi-clipboard-data text-2xl"></i>
                    </div>
                    <div class="text-3xl font-bold text-gray-900"><?php echo $totalRecords; ?></div>
                    <div class="text-sm text-gray-500">Total Records</div>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center text-center">
                    <div class="p-3 rounded-full bg-green-50 text-green-600 mb-3">
                        <i class="bi bi-check-circle text-2xl"></i>
                    </div>
                    <div class="text-3xl font-bold text-green-600"><?php echo $presentCount; ?></div>
                    <div class="text-sm text-gray-500">Present</div>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center text-center">
                    <div class="p-3 rounded-full bg-red-50 text-red-600 mb-3">
                        <i class="bi bi-x-circle text-2xl"></i>
                    </div>
                    <div class="text-3xl font-bold text-red-600"><?php echo $absentCount; ?></div>
                    <div class="text-sm text-gray-500">Absent</div>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center text-center">
                    <div class="p-3 rounded-full bg-yellow-50 text-yellow-600 mb-3">
                        <i class="bi bi-clock text-2xl"></i>
                    </div>
                    <div class="text-3xl font-bold text-yellow-600"><?php echo $lateCount; ?></div>
                    <div class="text-sm text-gray-500">Late</div>
                </div>
            </div>

            <!-- Filters Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8">
                <div class="flex items-center gap-2 mb-4 text-gray-800 font-semibold">
                    <i class="bi bi-funnel"></i> Filter Records
                </div>
                
                <form method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                        <select name="subject" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition bg-white">
                            <option value="">All Subjects</option>
                            <?php foreach ($subjects as $subj): ?>
                            <option value="<?php echo htmlspecialchars($subj); ?>" <?php echo $selectedSubject == $subj ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($subj); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                        <select name="year" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition bg-white">
                            <option value="">All Years</option>
                            <option value="FY" <?php echo $selectedYear == 'FY' ? 'selected' : ''; ?>>First Year (FY)</option>
                            <option value="SY" <?php echo $selectedYear == 'SY' ? 'selected' : ''; ?>>Second Year (SY)</option>
                            <option value="TY" <?php echo $selectedYear == 'TY' ? 'selected' : ''; ?>>Third Year (TY)</option>
                        </select>
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                        <input type="date" name="date" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition" value="<?php echo $selectedDate; ?>">
                    </div>
                    <div class="md:col-span-3">
                        <button type="submit" class="w-full py-2.5 rounded-xl bg-green-600 text-white font-medium hover:bg-green-700 transition shadow-lg shadow-green-500/20">
                            <i class="bi bi-search"></i> Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Attendance Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800">Attendance Records</h3>
                    <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold"><?php echo $presentPercentage; ?>% Attendance Rate</span>
                </div>
                
                <div class="overflow-x-auto">
                    <?php if (empty($attendanceData)): ?>
                    <div class="p-8 text-center text-gray-500">
                        <i class="bi bi-inbox text-4xl mb-3 block text-gray-300"></i>
                        No attendance records found. Try adjusting the filters.
                    </div>
                    <?php else: ?>
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 text-gray-500 font-medium">
                            <tr>
                                <th class="px-6 py-3">Date</th>
                                <th class="px-6 py-3">Student Order</th>
                                <th class="px-6 py-3">Subject</th>
                                <th class="px-6 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($attendanceData as $record): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 text-gray-900 whitespace-nowrap">
                                    <?php echo date('d M Y', strtotime($record['date'])); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-500">
                                            <?php echo substr($record['student_name'], 0, 1); ?>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900"><?php echo htmlspecialchars($record['student_name']); ?></p>
                                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($record['email']); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    <span class="px-2 py-1 rounded-lg bg-gray-100 text-xs font-medium"><?php echo htmlspecialchars($record['subject']); ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($record['status'] == 'Present'): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="bi bi-check-circle-fill"></i> Present
                                    </span>
                                    <?php elseif ($record['status'] == 'Absent'): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="bi bi-x-circle-fill"></i> Absent
                                    </span>
                                    <?php else: ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="bi bi-clock-fill"></i> Late
                                    </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Chart -->
            <?php if ($totalRecords > 0): ?>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 max-w-lg mx-auto">
                <h3 class="font-bold text-gray-800 mb-6 flex items-center gap-2">
                    <i class="bi bi-pie-chart text-green-600"></i> Attendance Distribution
                </h3>
                <div class="relative h-64 w-full">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>

            <script>
            const ctx = document.getElementById('attendanceChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Present', 'Absent', 'Late'],
                    datasets: [{
                        data: [<?php echo $presentCount; ?>, <?php echo $absentCount; ?>, <?php echo $lateCount; ?>],
                        backgroundColor: ['#22c55e', '#ef4444', '#eab308'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    family: "'Inter', sans-serif"
                                }
                            }
                        }
                    },
                    cutout: '70%'
                }
            });
            </script>
            <?php endif; ?>

        </main>
    </div>
</div>

</body>
</html>
