<?php
session_start();
require_once __DIR__ . '/../db.php';

if (!isset($_SESSION['teacher_id'])) {
    header("Location: teacher_login.php");
    exit;
}

$students = [];
$year = "";
$semester = $_POST['semester'] ?? '';
$subject  = $_POST['subject'] ?? '';

/* LOAD STUDENTS */
if (!empty($semester)) {

    if ($semester == 1 || $semester == 2) $year = 'FY';
    elseif ($semester == 3 || $semester == 4) $year = 'SY';
    else $year = 'TY';

    $students = mysqli_query($conn,
        "SELECT * FROM students WHERE year='$year' ORDER BY id"
    );
}

/* CHECK IF MARKS ARE LOCKED */
$isLocked = false;
if (!empty($semester) && !empty($subject)) {
    $lockQ = mysqli_query($conn,"
        SELECT locked FROM student_marks
        WHERE semester='$semester' AND subject='$subject'
        LIMIT 1
    ");
    if (mysqli_num_rows($lockQ)) {
        $isLocked = mysqli_fetch_assoc($lockQ)['locked'];
    }
}

/* SAVE / EDIT MARKS */
if (isset($_POST['save_marks']) && !$isLocked) {

    foreach ($_POST['marks'] as $sid => $marks) {
        $sid = intval($sid);
        $credits = intval($_POST['credits'][$sid]);
        $semester = intval($_POST['semester']);
        $subject = mysqli_real_escape_string($conn, $_POST['subject']);
        $exam_name = mysqli_real_escape_string($conn, $_POST['exam_name']);
        $session = mysqli_real_escape_string($conn, $_POST['session']);
        $category = mysqli_real_escape_string($conn, $_POST['category']);
        $teacher_id = intval($_SESSION['teacher_id']);
        
        // Validate marks and credits
        $validMarks = is_numeric($marks) && $marks >= 0 && $marks <= 100;
        $validSpecial = in_array(strtoupper($marks), ['AB', 'UNF', 'EX']);
        
        if (($validMarks || $validSpecial) && $credits >= 1 && $credits <= 10) {
            $stmt = mysqli_prepare($conn, "
                INSERT INTO student_marks
                (student_id, semester, subject, exam_name, marks, credits, session, category, entered_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    marks = VALUES(marks),
                    credits = VALUES(credits),
                    exam_name = VALUES(exam_name),
                    session = VALUES(session),
                    category = VALUES(category)
            ");
            
            mysqli_stmt_bind_param($stmt, "iissssssi", 
                $sid, $semester, $subject, $exam_name, $marks, 
                $credits, $session, $category, $teacher_id
            );
            
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    $msg = "Marks updated successfully!";
}

/* LOCK MARKS */
if (isset($_POST['lock_marks'])) {
    $semester = intval($semester);
    $subject = mysqli_real_escape_string($conn, $subject);

    $stmt = mysqli_prepare($conn, "UPDATE student_marks SET locked = 1 WHERE semester = ? AND subject = ?");
    mysqli_stmt_bind_param($stmt, "is", $semester, $subject);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $isLocked = true;
    $msg = "Marks locked successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Marks | Faculty Portal</title>
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
                    <a href="semester_mark_entry.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-green-50 text-green-700 font-medium">
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
                <h1 class="text-2xl font-bold text-gray-900">Exam Mark Entry</h1>
                <p class="text-gray-500 text-sm">Enter internal and external marks for students.</p>
            </div>

            <?php if(isset($msg)): ?>
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded-r-lg flex justify-between items-center">
                <div class="flex">
                    <div class="flex-shrink-0 text-blue-500">ℹ️</div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700 font-medium"><?php echo $msg; ?></p>
                    </div>
                </div>
                <button onclick="this.parentElement.remove()" class="text-blue-500 hover:text-blue-700">×</button>
            </div>
            <?php endif; ?>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8">
                <div class="flex items-center gap-2 mb-4 text-gray-800 font-semibold">
                    <i class="bi bi-sliders"></i> Exam Details
                </div>
                
                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Session</label>
                            <select name="session" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition bg-white" required>
                                <option>OCT/NOV-2025</option>
                                <option>MAR/APR-2025</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
                            <select name="semester" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition bg-white" required>
                                <option value="">Select</option>
                                <?php for($i=1;$i<=6;$i++): ?>
                                <option value="<?php echo $i; ?>" <?php if($semester==$i) echo "selected"; ?>>
                                    Sem <?php echo $i; ?>
                                </option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                            <select name="subject" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition bg-white" required>
                                <option <?php if($subject=='Mathematics') echo "selected"; ?>>Mathematics</option>
                                <option <?php if($subject=='Data Structures') echo "selected"; ?>>Data Structures</option>
                                <option <?php if($subject=='C Programming') echo "selected"; ?>>C Programming</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Exam Type</label>
                            <select name="exam_name" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition bg-white">
                                <option>ESE</option>
                                <option>ISE</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select name="category" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition bg-white">
                                <option>Regular</option>
                                <option>Supplementary</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button class="px-6 py-2.5 rounded-xl bg-gray-900 text-white font-medium hover:bg-gray-800 transition shadow-lg shadow-gray-500/20">
                            Load Students
                        </button>
                    </div>

                    <?php if($students): ?>
                    
                    <div class="border-t border-gray-100 pt-6 mt-6">
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6 text-sm text-yellow-800 flex items-start gap-3">
                            <i class="bi bi-lightbulb-fill mt-0.5"></i>
                            <div>
                                <strong>Note:</strong> Enter numeric marks (0-100) or use special codes: 
                                <span class="font-mono bg-yellow-100 px-1 rounded">AB</span> (Absent), 
                                <span class="font-mono bg-yellow-100 px-1 rounded">UNF</span> (Unfair Means), 
                                <span class="font-mono bg-yellow-100 px-1 rounded">EX</span> (Exempted).
                            </div>
                        </div>

                        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                            <table class="w-full text-left border-collapse">
                                <thead class="bg-gray-50 text-gray-500 font-medium border-b border-gray-200">
                                    <tr>
                                        <th class="px-6 py-4">Student Name</th>
                                        <th class="px-6 py-4">Roll No / ID</th>
                                        <th class="px-6 py-4 w-40">Marks Obtained</th>
                                        <th class="px-6 py-4 w-32">Credits</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php while($s=mysqli_fetch_assoc($students)): ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 font-medium text-gray-900">
                                            <?php echo htmlspecialchars($s['name']); ?>
                                        </td>
                                        <td class="px-6 py-4 text-gray-500">
                                            <?php echo htmlspecialchars($s['id']); ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <input name="marks[<?php echo $s['id']; ?>]" 
                                                   class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-center font-mono"
                                                   placeholder="00"
                                                   <?php if($isLocked) echo "disabled"; ?>>
                                        </td>
                                        <td class="px-6 py-4">
                                            <input name="credits[<?php echo $s['id']; ?>]" 
                                                   type="number" 
                                                   class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-center"
                                                   placeholder="4"
                                                   value="4"
                                                   <?php if($isLocked) echo "disabled"; ?>>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-end mt-6">
                            <button name="save_marks" class="px-8 py-3 rounded-xl bg-green-600 text-white font-bold hover:bg-green-700 transition shadow-lg shadow-green-500/30 flex items-center gap-2" <?php if($isLocked) echo "disabled"; ?>>
                                <i class="bi bi-save"></i> Save / Update Marks
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>
                </form>
            </div>

        </main>
    </div>
</div>

</body>
</html>
