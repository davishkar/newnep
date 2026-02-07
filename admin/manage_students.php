<?php
session_start();
require_once __DIR__ . '/../db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

/* ================= ADD STUDENT ================= */
if (isset($_POST['add_student'])) {
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $year  = $_POST['year'];
    $password = $_POST['password'];
    
    // Validate inputs
    if (preg_match("/^[a-zA-Z\s]+$/", $name) && filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($password) >= 8) {
        
        // Check for duplicate email
        $checkStmt = mysqli_prepare($conn, "SELECT id FROM students WHERE email = ?");
        mysqli_stmt_bind_param($checkStmt, "s", $email);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);
        
        if (mysqli_num_rows($checkResult) == 0) {
            $pass = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = mysqli_prepare($conn, "INSERT INTO students (name, email, password, year) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $pass, $year);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        
        mysqli_stmt_close($checkStmt);
    }
}

/* ================= UPDATE STUDENT ================= */
if (isset($_POST['update_student'])) {
    $id   = intval($_POST['id']);
    $year = $_POST['year'];

    if (!empty($_POST['password'])) {
        $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        $stmt = mysqli_prepare($conn, "UPDATE students SET year = ?, password = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ssi", $year, $pass, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE students SET year = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $year, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

/* ================= DELETE STUDENT ================= */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    $stmt = mysqli_prepare($conn, "DELETE FROM students WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

/* ================= FETCH YEAR-WISE ================= */
$fy_students = mysqli_query($conn,"SELECT * FROM students WHERE year='FY' ORDER BY id");
$sy_students = mysqli_query($conn,"SELECT * FROM students WHERE year='SY' ORDER BY id");
$ty_students = mysqli_query($conn,"SELECT * FROM students WHERE year='TY' ORDER BY id");

/* ================= FUNCTION TO RENDER TABLE ================= */
function renderTable($result){
    while($s = mysqli_fetch_assoc($result)){
        $yearBadges = [
            'FY' => 'bg-blue-100 text-blue-700',
            'SY' => 'bg-purple-100 text-purple-700',
            'TY' => 'bg-pink-100 text-pink-700'
        ];
        $badgeColor = $yearBadges[$s['year']] ?? 'bg-gray-100 text-gray-700';
        
        echo "
        <tr class='bg-white border-b hover:bg-gray-50 transition'>
            <td class='px-6 py-4 font-medium text-gray-900'>{$s['id']}</td>
            <td class='px-6 py-4'>
                <div class='flex items-center gap-3'>
                    <div class='w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-500'>
                        ".substr($s['name'], 0, 1)."
                    </div>
                    <div>
                        <div class='font-medium text-gray-900'>{$s['name']}</div>
                        <div class='text-xs text-gray-500'>{$s['email']}</div>
                    </div>
                </div>
            </td>
            <td class='px-6 py-4 text-xs font-mono text-gray-500'>{$s['email']}</td>
            <td class='px-6 py-4'>
                <span class='px-2 py-1 rounded-md text-xs font-bold {$badgeColor}'>{$s['year']}</span>
            </td>
            <td class='px-6 py-4 text-right'>
                <a href='edit_student.php?id={$s['id']}' class='text-indigo-600 hover:text-indigo-900 font-medium text-xs px-3 py-1.5 mr-2 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition inline-flex items-center gap-1'>
                    <i class='bi bi-pencil-square'></i> Edit
                </a>
                <a href='?delete={$s['id']}'
                   class='text-red-600 hover:text-red-900 font-medium text-xs px-3 py-1.5 bg-red-50 hover:bg-red-100 rounded-lg transition inline-flex items-center gap-1'
                   onclick='return confirm(\"Delete this student?\")'>
                   <i class='bi bi-trash'></i> Delete
                </a>
            </td>
        </tr>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students | Admin Panel</title>
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
                    <a href="manage_students.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-indigo-50 text-indigo-700 font-medium">
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
                        <h1 class="text-2xl font-bold text-gray-900">Manage Students</h1>
                        <p class="text-gray-600 text-sm mt-1">Add, update, or remove student records.</p>
                    </div>
                    <a href="admin_dashboard.php" class="hidden sm:inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        <i class="bi bi-arrow-left"></i> Back to Dashboard
                    </a>
                </div>

                <!-- Add Student Form -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <div class="p-1.5 rounded bg-indigo-100 text-indigo-600"><i class="bi bi-person-plus-fill"></i></div>
                        Add New Student
                    </h2>
                    
                    <form method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <div class="md:col-span-3">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Student Name</label>
                            <input name="name" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition" placeholder="e.g. John Doe" required>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Email Address</label>
                            <input name="email" type="email" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition" placeholder="e.g. john@example.com" required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Password</label>
                            <input name="password" type="password" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition" placeholder="********" required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Year</label>
                            <select name="year" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition bg-white" required>
                                <option value="">Select</option>
                                <option>FY</option>
                                <option>SY</option>
                                <option>TY</option>
                            </select>
                        </div>
                        <div class="md:col-span-2 flex items-end">
                            <button name="add_student" class="w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition shadow-sm flex items-center justify-center gap-2">
                                <i class="bi bi-plus-lg"></i> Add
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Student Tables -->
                <div class="space-y-8">
                    
                    <!-- FY Students -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-700 text-xs font-bold uppercase tracking-wide">FY</span> First Year Students
                            </h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-6 py-3 font-semibold">ID</th>
                                        <th class="px-6 py-3 font-semibold">Name</th>
                                        <th class="px-6 py-3 font-semibold">Email</th>
                                        <th class="px-6 py-3 font-semibold">Year</th>
                                        <th class="px-6 py-3 font-semibold">Reset Password</th>
                                        <th class="px-6 py-3 font-semibold text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php renderTable($fy_students); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- SY Students -->
                     <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-700 text-xs font-bold uppercase tracking-wide">SY</span> Second Year Students
                            </h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-6 py-3 font-semibold">ID</th>
                                        <th class="px-6 py-3 font-semibold">Name</th>
                                        <th class="px-6 py-3 font-semibold">Email</th>
                                        <th class="px-6 py-3 font-semibold">Year</th>
                                        <th class="px-6 py-3 font-semibold">Reset Password</th>
                                        <th class="px-6 py-3 font-semibold text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php renderTable($sy_students); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TY Students -->
                     <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full bg-pink-100 text-pink-700 text-xs font-bold uppercase tracking-wide">TY</span> Third Year Students
                            </h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-6 py-3 font-semibold">ID</th>
                                        <th class="px-6 py-3 font-semibold">Name</th>
                                        <th class="px-6 py-3 font-semibold">Email</th>
                                        <th class="px-6 py-3 font-semibold">Year</th>
                                        <th class="px-6 py-3 font-semibold">Reset Password</th>
                                        <th class="px-6 py-3 font-semibold text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php renderTable($ty_students); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
            
        </main>
    </div>
</div>

</body>
</html>
<?php
// We still need to update the renderTable function to output Tailwind rows
// But since the PHP function is at the top, I cannot edit it in this block easily if it's mixed with HTML strings.
// Wait, the PHP function `renderTable` (lines 75-106) outputs HTML strings.
// I MUST update that function too.
// The replace_file_content tool works on lines. I can do a separate call or try to verify if I can edit the top part too.
// The current `replace_file_content` call is for lines 108-202.
// I will submit this change first, then immediately update the `renderTable` PHP function at the top.
?>
