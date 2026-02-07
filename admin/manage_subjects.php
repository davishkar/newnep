<?php
session_start();
require_once __DIR__ . '/../db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

/* ================= ADD SUBJECT ================= */
if (isset($_POST['add_subject'])) {
    $name  = trim($_POST['subject_name']);
    $year  = $_POST['year'];
    $type  = $_POST['subject_type'];
    $cred  = intval($_POST['credits']);
    
    // Validate credits range
    if ($cred >= 1 && $cred <= 10 && !empty($name)) {
        $stmt = mysqli_prepare($conn, "INSERT INTO subjects (subject_name, year, subject_type, credits) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sssi", $name, $year, $type, $cred);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

/* ================= UPDATE SUBJECT ================= */
if (isset($_POST['update_subject'])) {
    $id    = intval($_POST['id']);
    $name  = trim($_POST['subject_name']);
    $year  = $_POST['year'];
    $type  = $_POST['subject_type'];
    $cred  = intval($_POST['credits']);
    
    if ($cred >= 1 && $cred <= 10 && !empty($name)) {
        $stmt = mysqli_prepare($conn, "UPDATE subjects SET subject_name = ?, year = ?, subject_type = ?, credits = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "sssii", $name, $year, $type, $cred, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

/* ================= DELETE SUBJECT ================= */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    $stmt = mysqli_prepare($conn, "DELETE FROM subjects WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

/* ================= FETCH SUBJECTS ================= */
$subjects = mysqli_query($conn,"SELECT * FROM subjects ORDER BY year,id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subjects | Admin Panel</title>
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
                    <a href="manage_subjects.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-indigo-50 text-indigo-700 font-medium">
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
                        <h1 class="text-2xl font-bold text-gray-900">Manage Subjects</h1>
                        <p class="text-gray-600 text-sm mt-1">Add, update, or remove subjects and credits.</p>
                    </div>
                    <a href="admin_dashboard.php" class="hidden sm:inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        <i class="bi bi-arrow-left"></i> Back to Dashboard
                    </a>
                </div>

                <!-- Add Subject Form -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <div class="p-1.5 rounded bg-indigo-100 text-indigo-600"><i class="bi bi-book-half"></i></div>
                        Add New Subject
                    </h2>
                    
                    <form method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <div class="md:col-span-4">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Subject Name</label>
                            <input name="subject_name" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition bg-white" placeholder="e.g. Advanced Java" required>
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
                        <div class="md:col-span-3">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Subject Type</label>
                             <select name="subject_type" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition bg-white" required>
                                <option value="">Select</option>
                                <option>Major</option>
                                <option>Minor</option>
                                <option>OE</option>
                                <option>VSC</option>
                                <option>CC</option>
                                <option>English</option>
                                <option>OJT</option>
                            </select>
                        </div>
                        <div class="md:col-span-1">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Credits</label>
                            <input name="credits" type="number" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition bg-white" placeholder="4" required>
                        </div>
                        <div class="md:col-span-2 flex items-end">
                            <button name="add_subject" class="w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition shadow-sm flex items-center justify-center gap-2">
                                <i class="bi bi-plus-lg"></i> Add
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Subject List -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                        <h3 class="font-bold text-gray-800 flex items-center gap-2">
                            <i class="bi bi-list-task text-gray-500"></i> All Subjects
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3 font-semibold">ID</th>
                                    <th class="px-6 py-3 font-semibold">Subject Name</th>
                                    <th class="px-6 py-3 font-semibold">Year</th>
                                    <th class="px-6 py-3 font-semibold">Type</th>
                                    <th class="px-6 py-3 font-semibold">Credits</th>
                                    <th class="px-6 py-3 font-semibold text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($s = mysqli_fetch_assoc($subjects)): ?>
                                <tr class="bg-white border-b hover:bg-gray-50 transition">
                                <form method="POST">
                                    <td class="px-6 py-4 font-medium text-gray-900"><?php echo $s['id']; ?></td>
                                    
                                    <td class="px-6 py-4">
                                        <input name="subject_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2"
                                               value="<?php echo $s['subject_name']; ?>">
                                    </td>

                                    <td class="px-6 py-4">
                                        <select name="year" class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2">
                                            <option <?php if($s['year']=='FY') echo "selected"; ?>>FY</option>
                                            <option <?php if($s['year']=='SY') echo "selected"; ?>>SY</option>
                                            <option <?php if($s['year']=='TY') echo "selected"; ?>>TY</option>
                                        </select>
                                    </td>

                                    <td class="px-6 py-4">
                                         <select name="subject_type" class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2">
                                            <option <?php if($s['subject_type']=='Major') echo "selected"; ?>>Major</option>
                                            <option <?php if($s['subject_type']=='Minor') echo "selected"; ?>>Minor</option>
                                            <option <?php if($s['subject_type']=='OE') echo "selected"; ?>>OE</option>
                                            <option <?php if($s['subject_type']=='VSC') echo "selected"; ?>>VSC</option>
                                            <option <?php if($s['subject_type']=='CC') echo "selected"; ?>>CC</option>
                                            <option <?php if($s['subject_type']=='English') echo "selected"; ?>>English</option>
                                            <option <?php if($s['subject_type']=='OJT') echo "selected"; ?>>OJT</option>
                                        </select>
                                    </td>

                                    <td class="px-6 py-4">
                                        <input name="credits" type="number" class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2"
                                               value="<?php echo $s['credits']; ?>">
                                    </td>

                                    <td class="px-6 py-4 text-right">
                                        <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                                        <button name="update_subject" class="text-white bg-indigo-600 hover:bg-indigo-700 font-medium rounded-lg text-xs px-3 py-1.5 mr-2 transition">
                                            Update
                                        </button>
                                        <a href="?delete=<?php echo $s['id']; ?>"
                                           class="text-white bg-red-600 hover:bg-red-700 font-medium rounded-lg text-xs px-3 py-1.5 transition"
                                           onclick="return confirm('Delete this subject?')">
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
