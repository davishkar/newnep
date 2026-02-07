<?php
session_start();
require_once __DIR__ . '/../db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$semester = $_POST['semester'] ?? '';
$msg = "";

/* ================= UPDATE MARKS ================= */
if (isset($_POST['update_marks'])) {
    foreach ($_POST['marks'] as $mid => $marks) {
        $mid = intval($mid);
        $credits = intval($_POST['credits'][$mid]);
        
        // Validate marks (0-100 or special values)
        $validMarks = is_numeric($marks) && $marks >= 0 && $marks <= 100;
        $validSpecial = in_array(strtoupper($marks), ['AB', 'UNF', 'EX']);
        
        // Validate credits (1-10)
        if (($validMarks || $validSpecial) && $credits >= 1 && $credits <= 10) {
            $stmt = mysqli_prepare($conn, "UPDATE student_marks SET marks = ?, credits = ? WHERE id = ? AND locked = 0");
            mysqli_stmt_bind_param($stmt, "sii", $marks, $credits, $mid);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    $msg = "Marks & credits updated successfully!";
}

/* ================= LOCK SEMESTER ================= */
if (isset($_POST['lock_semester'])) {
    $semester = intval($_POST['semester']);
    
    $stmt = mysqli_prepare($conn, "UPDATE student_marks SET locked = 1 WHERE semester = ?");
    mysqli_stmt_bind_param($stmt, "i", $semester);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    $msg = "Semester marks approved & locked!";
}

/* ================= UNLOCK SEMESTER ================= */
if (isset($_POST['unlock_semester'])) {
    $semester = intval($_POST['semester']);
    
    $stmt = mysqli_prepare($conn, "UPDATE student_marks SET locked = 0 WHERE semester = ?");
    mysqli_stmt_bind_param($stmt, "i", $semester);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    $msg = "Semester marks unlocked!";
}

/* ================= FETCH MARKS ================= */
$marksData = [];
$isLocked = false;

if (!empty($semester)) {

    $q = mysqli_query($conn,"
        SELECT m.*, s.name
        FROM student_marks m
        JOIN students s ON s.id = m.student_id
        WHERE m.semester='$semester'
        ORDER BY s.id
    ");

    while ($r = mysqli_fetch_assoc($q)) {
        $marksData[] = $r;
        $isLocked = $r['locked']; // same for whole semester
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Marks & Credits | Admin Panel</title>
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
                    <a href="manage_marks_credits.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-indigo-50 text-indigo-700 font-medium">
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
                        <h1 class="text-2xl font-bold text-gray-900">Manage Marks & Credits</h1>
                        <p class="text-gray-600 text-sm mt-1">Updates marks, assign credits, and lock semesters.</p>
                    </div>
                    <a href="admin_dashboard.php" class="hidden sm:inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        <i class="bi bi-arrow-left"></i> Back to Dashboard
                    </a>
                </div>

                <?php if($msg): ?>
                <div class="mb-6 p-4 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 flex items-center gap-3">
                    <i class="bi bi-info-circle-fill text-xl"></i>
                    <?php echo $msg; ?>
                </div>
                <?php endif; ?>

                <!-- Filter Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
                     <form method="POST" class="flex flex-col md:flex-row gap-4 items-end">
                        <div class="w-full md:w-1/3">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Select Semester</label>
                            <select name="semester" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition bg-white" required>
                                <option value="">Choose Semester...</option>
                                <?php for($i=1;$i<=6;$i++): ?>
                                <option value="<?php echo $i; ?>" <?php if($semester==$i) echo "selected"; ?>>
                                    Semester <?php echo $i; ?>
                                </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="w-full md:w-auto">
                            <button class="w-full px-6 py-2 bg-gray-900 hover:bg-gray-800 text-white rounded-lg font-medium transition shadow-sm">
                                Load Students
                            </button>
                        </div>
                    </form>
                </div>

                <?php if($marksData): ?>
                <!-- Marks List -->
                 <form method="POST">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-8">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                                <i class="bi bi-pencil-square text-gray-500"></i> Student Marks
                            </h3>
                             <?php if($isLocked): ?>
                                <span class="bg-red-100 text-red-700 text-xs px-2 py-1 rounded font-semibold"><i class="bi bi-lock-fill"></i> Locked</span>
                            <?php else: ?>
                                <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded font-semibold"><i class="bi bi-unlock-fill"></i> Editable</span>
                            <?php endif; ?>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-6 py-3 font-semibold">Student Name</th>
                                        <th class="px-6 py-3 font-semibold">Subject</th>
                                        <th class="px-6 py-3 font-semibold w-32">Marks</th>
                                        <th class="px-6 py-3 font-semibold w-32">Credits</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($marksData as $m): ?>
                                    <tr class="bg-white border-b hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 font-medium text-gray-900"><?php echo $m['name']; ?></td>
                                        <td class="px-6 py-4 text-gray-600"><?php echo $m['subject']; ?></td>

                                        <td class="px-6 py-4">
                                            <input name="marks[<?php echo $m['id']; ?>]"
                                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 <?php if($isLocked) echo 'opacity-50 cursor-not-allowed'; ?>"
                                                   value="<?php echo $m['marks']; ?>"
                                                   <?php if($isLocked) echo "disabled"; ?>>
                                        </td>

                                        <td class="px-6 py-4">
                                            <input name="credits[<?php echo $m['id']; ?>]"
                                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 <?php if($isLocked) echo 'opacity-50 cursor-not-allowed'; ?>"
                                                   type="number"
                                                   value="<?php echo $m['credits']; ?>"
                                                   <?php if($isLocked) echo "disabled"; ?>>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-4">
                        <button name="update_marks" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition shadow-sm flex items-center gap-2 <?php if($isLocked) echo 'opacity-50 cursor-not-allowed'; ?>" <?php if($isLocked) echo "disabled"; ?>>
                            <i class="bi bi-save"></i> Update Marks
                        </button>
                        
                        <?php if(!$isLocked): ?>
                        <button name="lock_semester" class="px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition shadow-sm flex items-center gap-2"
                                onclick="return confirm('Approve & lock this semester?')">
                            <i class="bi bi-check-circle-fill"></i> Approve & Lock Semester
                        </button>
                        <?php else: ?>
                        <button name="unlock_semester" class="px-6 py-2.5 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg font-medium transition shadow-sm flex items-center gap-2"
                                onclick="return confirm('Unlock this semester?')">
                            <i class="bi bi-unlock-fill"></i> Unlock Semester
                        </button>
                        <?php endif; ?>
                    </div>
                </form>
                <?php endif; ?>

            </div>
            
        </main>
    </div>
</div>

</body>
</html>
