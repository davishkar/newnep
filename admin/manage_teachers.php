<?php
session_start();
require_once __DIR__ . '/../db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

/* ================= ADD TEACHER ================= */
if (isset($_POST['add_teacher'])) {
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $pass  = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $dept  = trim($_POST['department']);
    
    // Validate and check duplicate
    if (preg_match("/^[a-zA-Z\s]+$/", $name) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $checkStmt = mysqli_prepare($conn, "SELECT id FROM teachers WHERE email = ?");
        mysqli_stmt_bind_param($checkStmt, "s", $email);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);
        
        if (mysqli_num_rows($checkResult) == 0) {
            $stmt = mysqli_prepare($conn, "INSERT INTO teachers (name, email, password, department) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $pass, $dept);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        
        mysqli_stmt_close($checkStmt);
    }
}

/* ================= UPDATE TEACHER ================= */
if (isset($_POST['update_teacher'])) {
    $id   = intval($_POST['id']);
    $dept = trim($_POST['department']);

    if (!empty($_POST['password'])) {
        $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        $stmt = mysqli_prepare($conn, "UPDATE teachers SET department = ?, password = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ssi", $dept, $pass, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE teachers SET department = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $dept, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

/* ================= DELETE TEACHER ================= */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    $stmt = mysqli_prepare($conn, "DELETE FROM teachers WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

/* ================= FETCH TEACHERS ================= */
$teachers = mysqli_query($conn,"SELECT * FROM teachers ORDER BY id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Teachers | Admin Panel</title>
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
                    <a href="manage_teachers.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-indigo-50 text-indigo-700 font-medium">
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
                        <h1 class="text-2xl font-bold text-gray-900">Manage Teachers</h1>
                        <p class="text-gray-600 text-sm mt-1">Add, update, or remove teacher records.</p>
                    </div>
                    <a href="admin_dashboard.php" class="hidden sm:inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        <i class="bi bi-arrow-left"></i> Back to Dashboard
                    </a>
                </div>

                <!-- Add Teacher Form -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <div class="p-1.5 rounded bg-indigo-100 text-indigo-600"><i class="bi bi-person-plus-fill"></i></div>
                        Add New Teacher
                    </h2>
                    
                    <form method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <div class="md:col-span-3">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Teacher Name</label>
                            <input name="name" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition" placeholder="e.g. Prof. Smith" required>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Email Address</label>
                            <input name="email" type="email" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition" placeholder="email@college.edu" required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Password</label>
                            <input name="password" type="password" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition" placeholder="********" required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Department</label>
                            <input name="department" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition" placeholder="e.g. CS" required>
                        </div>
                        <div class="md:col-span-2 flex items-end">
                            <button name="add_teacher" class="w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition shadow-sm flex items-center justify-center gap-2">
                                <i class="bi bi-plus-lg"></i> Add
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Teacher List -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                        <h3 class="font-bold text-gray-800 flex items-center gap-2">
                            <i class="bi bi-list-task text-gray-500"></i> All Teachers
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3 font-semibold">ID</th>
                                    <th class="px-6 py-3 font-semibold">Name</th>
                                    <th class="px-6 py-3 font-semibold">Email</th>
                                    <th class="px-6 py-3 font-semibold">Department</th>
                                    <th class="px-6 py-3 font-semibold">Reset Password</th>
                                    <th class="px-6 py-3 font-semibold text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($t = mysqli_fetch_assoc($teachers)): ?>
                                <tr class="bg-white border-b hover:bg-gray-50 transition">
                                <form method="POST">
                                    <td class="px-6 py-4 font-medium text-gray-900"><?php echo $t['id']; ?></td>
                                    <td class="px-6 py-4 font-medium text-gray-900"><?php echo $t['name']; ?></td>
                                    <td class="px-6 py-4 text-gray-500"><?php echo $t['email']; ?></td>
                                    <td class="px-6 py-4">
                                        <input name="department" class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2"
                                               value="<?php echo $t['department']; ?>">
                                    </td>
                                    <td class="px-6 py-4">
                                        <input name="password" type="password" class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2"
                                               placeholder="New pass (optional)">
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                                        <button name="update_teacher" class="text-white bg-indigo-600 hover:bg-indigo-700 font-medium rounded-lg text-xs px-3 py-1.5 mr-2 transition">
                                            Update
                                        </button>
                                        <a href="?delete=<?php echo $t['id']; ?>"
                                           class="text-white bg-red-600 hover:bg-red-700 font-medium rounded-lg text-xs px-3 py-1.5 transition"
                                           onclick="return confirm('Delete this teacher?')">
                                           Delete
                                        </a>
                                    </td>
                                </form>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
            
        </main>
    </div>
</div>

</body>
</html>
