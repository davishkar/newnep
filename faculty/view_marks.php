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

// Fetch marks entered by this teacher
$selectedSubject = $_GET['subject'] ?? '';
$selectedYear = $_GET['year'] ?? '';
$selectedSemester = $_GET['semester'] ?? '';

$marksData = [];
$query = "SELECT sm.*, s.name as student_name, s.email, s.year 
          FROM student_marks sm 
          JOIN students s ON sm.student_id = s.id 
          WHERE sm.entered_by = ?";

$params = [$teacher_id];
$types = "i";

if (!empty($selectedSubject)) {
    $query .= " AND sm.subject = ?";
    $params[] = $selectedSubject;
    $types .= "s";
}

if (!empty($selectedYear)) {
    $query .= " AND s.year = ?";
    $params[] = $selectedYear;
    $types .= "s";
}

if (!empty($selectedSemester)) {
    $query .= " AND sm.semester = ?";
    $params[] = intval($selectedSemester);
    $types .= "i";
}

$query .= " ORDER BY sm.semester DESC, s.name";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $marksData[] = $row;
}
mysqli_stmt_close($stmt);

// Get unique subjects taught by this teacher
$subjectsStmt = mysqli_prepare($conn, "SELECT DISTINCT subject FROM student_marks WHERE entered_by = ? ORDER BY subject");
mysqli_stmt_bind_param($subjectsStmt, "i", $teacher_id);
mysqli_stmt_execute($subjectsStmt);
$subjectsResult = mysqli_stmt_get_result($subjectsStmt);
$subjects = [];
while ($row = mysqli_fetch_assoc($subjectsResult)) {
    $subjects[] = $row['subject'];
}
mysqli_stmt_close($subjectsStmt);

// Calculate statistics
$totalEntries = count($marksData);
$passCount = 0;
$failCount = 0;
$totalMarks = 0;

foreach ($marksData as $record) {
    if (is_numeric($record['marks'])) {
        $totalMarks += $record['marks'];
        if ($record['marks'] >= 40) $passCount++;
        else $failCount++;
    }
}

$averageMarks = $totalEntries > 0 ? round($totalMarks / $totalEntries, 2) : 0;
$passPercentage = $totalEntries > 0 ? round(($passCount / $totalEntries) * 100, 2) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Marks | Faculty Portal</title>
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
                    <a href="view_marks.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-green-50 text-green-700 font-medium">
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
                <h1 class="text-2xl font-bold text-gray-900">Marks Reports</h1>
                <p class="text-gray-500 text-sm">Analyze student performance and results.</p>
            </div>

            <!-- Statistics Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center text-center">
                    <div class="p-3 rounded-full bg-indigo-50 text-indigo-600 mb-3">
                        <i class="bi bi-clipboard-data text-2xl"></i>
                    </div>
                    <div class="text-3xl font-bold text-gray-900"><?php echo $totalEntries; ?></div>
                    <div class="text-sm text-gray-500">Total Entries</div>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center text-center">
                    <div class="p-3 rounded-full bg-blue-50 text-blue-600 mb-3">
                        <i class="bi bi-graph-up text-2xl"></i>
                    </div>
                    <div class="text-3xl font-bold text-blue-600"><?php echo $averageMarks; ?></div>
                    <div class="text-sm text-gray-500">Average Marks</div>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center text-center">
                    <div class="p-3 rounded-full bg-green-50 text-green-600 mb-3">
                        <i class="bi bi-check-circle text-2xl"></i>
                    </div>
                    <div class="text-3xl font-bold text-green-600"><?php echo $passCount; ?></div>
                    <div class="text-sm text-gray-500">Passed</div>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center text-center">
                    <div class="p-3 rounded-full bg-red-50 text-red-600 mb-3">
                        <i class="bi bi-x-circle text-2xl"></i>
                    </div>
                    <div class="text-3xl font-bold text-red-600"><?php echo $failCount; ?></div>
                    <div class="text-sm text-gray-500">Failed</div>
                </div>
            </div>

            <!-- Filters Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8">
                <div class="flex items-center gap-2 mb-4 text-gray-800 font-semibold">
                    <i class="bi bi-funnel"></i> Filter Results
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
                        <select name="semester" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition bg-white">
                            <option value="">All Semesters</option>
                            <?php for ($i = 1; $i <= 6; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo $selectedSemester == $i ? 'selected' : ''; ?>>Semester <?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="md:col-span-3">
                        <button type="submit" class="w-full py-2.5 rounded-xl bg-green-600 text-white font-medium hover:bg-green-700 transition shadow-lg shadow-green-500/20">
                            <i class="bi bi-search"></i> Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Marks Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800">Marks Records</h3>
                    <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold"><?php echo $passPercentage; ?>% Pass Rate</span>
                </div>
                
                <div class="overflow-x-auto">
                    <?php if (empty($marksData)): ?>
                    <div class="p-8 text-center text-gray-500">
                        <i class="bi bi-inbox text-4xl mb-3 block text-gray-300"></i>
                        No marks records found. Try adjusting the filters.
                    </div>
                    <?php else: ?>
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 text-gray-500 font-medium">
                            <tr>
                                <th class="px-6 py-3">Student Name</th>
                                <th class="px-6 py-3">Subject</th>
                                <th class="px-6 py-3">Semester</th>
                                <th class="px-6 py-3">Exam</th>
                                <th class="px-6 py-3">Marks</th>
                                <th class="px-6 py-3">Credits</th>
                                <th class="px-6 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($marksData as $record): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div class="block">
                                        <div class="font-bold text-gray-900"><?php echo htmlspecialchars($record['student_name']); ?></div>
                                        <div class="text-xs text-gray-500"><?php echo htmlspecialchars($record['year']); ?></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    <span class="px-2 py-1 rounded-lg bg-gray-100 text-xs font-medium border border-gray-200"><?php echo htmlspecialchars($record['subject']); ?></span>
                                </td>
                                <td class="px-6 py-4 text-gray-600">Sem <?php echo $record['semester']; ?></td>
                                <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($record['exam_name']); ?></td>
                                <td class="px-6 py-4 font-bold text-gray-900"><?php echo $record['marks']; ?></td>
                                <td class="px-6 py-4 text-gray-600"><?php echo $record['credits']; ?></td>
                                <td class="px-6 py-4">
                                    <?php if (is_numeric($record['marks']) && $record['marks'] >= 40): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="bi bi-check-circle-fill"></i> Pass
                                    </span>
                                    <?php elseif (is_numeric($record['marks'])): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="bi bi-x-circle-fill"></i> Fail
                                    </span>
                                    <?php else: ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <?php echo $record['marks']; ?>
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

        </main>
    </div>
</div>

</body>
</html>
