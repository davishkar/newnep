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
$message = "";
$error = "";

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_attendance'])) {
    $date = $_POST['date'];
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $year = $_POST['year'];
    
    if (!empty($_POST['attendance'])) {
        $successCount = 0;
        
        foreach ($_POST['attendance'] as $student_id => $status) {
            $student_id = intval($student_id);
            
            // Check if attendance already marked for this date
            $checkStmt = mysqli_prepare($conn, "SELECT id FROM attendance WHERE student_id = ? AND subject = ? AND date = ?");
            mysqli_stmt_bind_param($checkStmt, "iss", $student_id, $subject, $date);
            mysqli_stmt_execute($checkStmt);
            $checkResult = mysqli_stmt_get_result($checkStmt);
            
            if (mysqli_num_rows($checkResult) > 0) {
                // Update existing
                $updateStmt = mysqli_prepare($conn, "UPDATE attendance SET status = ?, marked_by = ? WHERE student_id = ? AND subject = ? AND date = ?");
                mysqli_stmt_bind_param($updateStmt, "siiss", $status, $teacher_id, $student_id, $subject, $date);
                mysqli_stmt_execute($updateStmt);
                mysqli_stmt_close($updateStmt);
            } else {
                // Insert new
                $insertStmt = mysqli_prepare($conn, "INSERT INTO attendance (student_id, subject, date, status, marked_by) VALUES (?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($insertStmt, "isssi", $student_id, $subject, $date, $status, $teacher_id);
                mysqli_stmt_execute($insertStmt);
                mysqli_stmt_close($insertStmt);
            }
            
            mysqli_stmt_close($checkStmt);
            $successCount++;
        }
        
        $message = "Attendance marked successfully for $successCount students!";
    } else {
        $error = "Please mark attendance for at least one student";
    }
}

// Fetch students for selected year
$students = [];
$selectedYear = $_GET['year'] ?? 'FY';
$selectedSubject = $_GET['subject'] ?? '';
$selectedDate = $_GET['date'] ?? date('Y-m-d');

if (!empty($selectedYear)) {
    $stmt = mysqli_prepare($conn, "SELECT id, name, email FROM students WHERE year = ? ORDER BY name");
    mysqli_stmt_bind_param($stmt, "s", $selectedYear);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $students[] = $row;
    }
    
    mysqli_stmt_close($stmt);
}

// Fetch existing attendance for selected date
$existingAttendance = [];
if (!empty($selectedSubject) && !empty($selectedDate)) {
    $stmt = mysqli_prepare($conn, "SELECT student_id, status FROM attendance WHERE subject = ? AND date = ?");
    mysqli_stmt_bind_param($stmt, "ss", $selectedSubject, $selectedDate);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $existingAttendance[$row['student_id']] = $row['status'];
    }
    
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance | Faculty Portal</title>
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
                    <a href="mark_attendance.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-green-50 text-green-700 font-medium">
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
            
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Mark Attendance</h1>
                <p class="text-gray-500 text-sm">Select class details to load student list.</p>
            </div>

            <!-- Alerts -->
            <?php if (!empty($message)): ?>
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-r-lg flex justify-between items-center">
                <div class="flex">
                    <div class="flex-shrink-0 text-green-500">✓</div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700 font-medium"><?php echo $message; ?></p>
                    </div>
                </div>
                <button onclick="this.parentElement.remove()" class="text-green-500 hover:text-green-700">×</button>
            </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg flex justify-between items-center">
                <div class="flex">
                    <div class="flex-shrink-0 text-red-500">⚠️</div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700 font-medium"><?php echo $error; ?></p>
                    </div>
                </div>
                <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">×</button>
            </div>
            <?php endif; ?>

            <!-- Filters Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8">
                <div class="flex items-center gap-2 mb-4 text-gray-800 font-semibold">
                    <i class="bi bi-funnel"></i> Select Class Details
                </div>
                
                <form method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                        <input type="date" name="date" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition" value="<?php echo $selectedDate; ?>" required>
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                        <select name="year" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition bg-white" required>
                            <option value="FY" <?php echo $selectedYear == 'FY' ? 'selected' : ''; ?>>First Year (FY)</option>
                            <option value="SY" <?php echo $selectedYear == 'SY' ? 'selected' : ''; ?>>Second Year (SY)</option>
                            <option value="TY" <?php echo $selectedYear == 'TY' ? 'selected' : ''; ?>>Third Year (TY)</option>
                        </select>
                    </div>
                    <div class="md:col-span-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                        <input type="text" name="subject" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition" value="<?php echo htmlspecialchars($selectedSubject); ?>" placeholder="e.g., Mathematics" required>
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="w-full py-2.5 rounded-xl bg-green-600 text-white font-medium hover:bg-green-700 transition shadow-lg shadow-green-500/20">
                            Load
                        </button>
                    </div>
                </form>
            </div>

            <?php if (!empty($students)): ?>

            <!-- Quick Actions -->
            <div class="flex flex-wrap gap-3 mb-6 p-4 bg-white rounded-xl border border-gray-100 shadow-sm items-center justify-between">
                <span class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Quick Actions</span>
                <div class="flex gap-2">
                    <button type="button" onclick="markAll('Present')" class="px-4 py-2 rounded-lg bg-green-50 text-green-700 text-sm font-medium hover:bg-green-100 transition border border-green-200">
                        <i class="bi bi-check-all"></i> Mark All Present
                    </button>
                    <button type="button" onclick="markAll('Absent')" class="px-4 py-2 rounded-lg bg-red-50 text-red-700 text-sm font-medium hover:bg-red-100 transition border border-red-200">
                        <i class="bi bi-x-circle"></i> Mark All Absent
                    </button>
                </div>
            </div>

            <!-- Attendance List -->
            <form method="POST">
                <input type="hidden" name="date" value="<?php echo $selectedDate; ?>">
                <input type="hidden" name="subject" value="<?php echo htmlspecialchars($selectedSubject); ?>">
                <input type="hidden" name="year" value="<?php echo $selectedYear; ?>">

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                        <h3 class="font-bold text-gray-800">Student List</h3>
                        <span class="px-3 py-1 rounded-full bg-gray-200 text-gray-700 text-xs font-semibold"><?php echo count($students); ?> Students</span>
                    </div>
                    
                    <div class="divide-y divide-gray-100">
                        <?php foreach ($students as $student): 
                            $currentStatus = $existingAttendance[$student['id']] ?? '';
                        ?>
                        <div class="p-4 md:p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:bg-gray-50 transition">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm">
                                    <?php echo substr($student['name'], 0, 1); ?>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900"><?php echo htmlspecialchars($student['name']); ?></h4>
                                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($student['email']); ?></p>
                                </div>
                            </div>
                            
                            <div class="flex gap-2">
                                <!-- Radio Inputs Hidden, Styled Labels Used -->
                                <label class="cursor-pointer">
                                    <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="Present" class="peer sr-only" <?php echo $currentStatus == 'Present' ? 'checked' : ''; ?>>
                                    <div class="px-4 py-2 rounded-lg border border-gray-200 text-gray-500 peer-checked:bg-green-500 peer-checked:text-white peer-checked:border-green-500 transition text-sm font-medium flex items-center gap-2 hover:bg-gray-50">
                                        <i class="bi bi-check-lg"></i> Present
                                    </div>
                                </label>

                                <label class="cursor-pointer">
                                    <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="Absent" class="peer sr-only" <?php echo $currentStatus == 'Absent' ? 'checked' : ''; ?>>
                                    <div class="px-4 py-2 rounded-lg border border-gray-200 text-gray-500 peer-checked:bg-red-500 peer-checked:text-white peer-checked:border-red-500 transition text-sm font-medium flex items-center gap-2 hover:bg-gray-50">
                                        <i class="bi bi-x-lg"></i> Absent
                                    </div>
                                </label>

                                <label class="cursor-pointer">
                                    <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="Late" class="peer sr-only" <?php echo $currentStatus == 'Late' ? 'checked' : ''; ?>>
                                    <div class="px-4 py-2 rounded-lg border border-gray-200 text-gray-500 peer-checked:bg-yellow-500 peer-checked:text-white peer-checked:border-yellow-500 transition text-sm font-medium flex items-center gap-2 hover:bg-gray-50">
                                        <i class="bi bi-clock"></i> Late
                                    </div>
                                </label>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="flex justify-end mb-12">
                     <button type="submit" name="mark_attendance" class="px-8 py-3 rounded-xl bg-green-600 text-white font-bold hover:bg-green-700 transition shadow-lg shadow-green-500/30 flex items-center gap-2">
                        <i class="bi bi-save"></i> Save Attendance Log
                    </button>
                </div>
            </form>

            <?php else: ?>
            <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-r-lg flex items-start gap-4">
                <i class="bi bi-info-circle-fill text-2xl text-blue-500"></i>
                <div>
                    <h4 class="font-bold text-blue-900">Ready to Mark Attendance</h4>
                    <p class="text-blue-700 mt-1">Please select a **Date**, **Year**, and **Subject** above to load the student list and start marking attendance.</p>
                </div>
            </div>
            <?php endif; ?>

        </main>
    </div>
</div>

<script>
function markAll(status) {
    // Find all radio buttons with the specific value
    const radios = document.querySelectorAll(`input[type="radio"][value="${status}"]`);
    radios.forEach(radio => {
        radio.checked = true;
    });
}
</script>

</body>
</html>
