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

// Fetch current teacher data
$stmt = mysqli_prepare($conn, "SELECT * FROM teachers WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $teacher_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$teacher = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $department = trim($_POST['department']);
    
    // Validation
    if (empty($name) || !preg_match("/^[a-zA-Z\s]+$/", $name)) {
        $error = "Name should contain only letters and spaces";
    }
    elseif (strlen($name) < 3 || strlen($name) > 50) {
        $error = "Name must be between 3 and 50 characters";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    }
    else {
        // Check if email already exists (for other teachers)
        $checkStmt = mysqli_prepare($conn, "SELECT id FROM teachers WHERE email = ? AND id != ?");
        mysqli_stmt_bind_param($checkStmt, "si", $email, $teacher_id);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);
        
        if (mysqli_num_rows($checkResult) > 0) {
            $error = "Email already exists for another teacher";
        } else {
            // Update profile
            $updateStmt = mysqli_prepare($conn, "UPDATE teachers SET name = ?, email = ?, department = ? WHERE id = ?");
            mysqli_stmt_bind_param($updateStmt, "sssi", $name, $email, $department, $teacher_id);
            
            if (mysqli_stmt_execute($updateStmt)) {
                $message = "Profile updated successfully!";
                $_SESSION['teacher_name'] = $name; // Update session
                
                // Refresh teacher data
                $teacher['name'] = $name;
                $teacher['email'] = $email;
                $teacher['department'] = $department;
            } else {
                $error = "Failed to update profile. Please try again.";
            }
            
            mysqli_stmt_close($updateStmt);
        }
        
        mysqli_stmt_close($checkStmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile | Faculty Portal</title>
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
                    <a href="profile.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-green-50 text-green-700 font-medium">
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
            
            <div class="max-w-2xl mx-auto">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Edit Profile</h1>
                    <p class="text-gray-500 text-sm">Update your personal information.</p>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="border-b border-gray-100 p-6 bg-gradient-to-r from-green-50 to-white">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center text-2xl font-bold text-green-600">
                                <?php echo substr($teacher['name'], 0, 1); ?>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">Profile Details</h2>
                                <p class="text-sm text-gray-500">Manage your account settings</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <?php if (!empty($message)): ?>
                        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-r-lg flex items-center">
                            <div class="flex-shrink-0 text-green-500">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-700 font-medium"><?php echo $message; ?></p>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($error)): ?>
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg flex items-center">
                            <div class="flex-shrink-0 text-red-500">
                                <i class="bi bi-exclamation-circle-fill"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700 font-medium"><?php echo $error; ?></p>
                            </div>
                        </div>
                        <?php endif; ?>

                        <form method="POST" class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="bi bi-person mr-1"></i> Full Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" value="<?php echo htmlspecialchars($teacher['name']); ?>" class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition bg-white" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="bi bi-envelope mr-1"></i> Email Address <span class="text-red-500">*</span>
                                </label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($teacher['email']); ?>" class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition bg-white" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="bi bi-building mr-1"></i> Department
                                </label>
                                <input type="text" name="department" value="<?php echo htmlspecialchars($teacher['department'] ?? ''); ?>" class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition bg-white">
                            </div>

                            <div class="flex items-center gap-4 pt-4 border-t border-gray-100">
                                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-green-600 text-white font-medium hover:bg-green-700 transition shadow-lg shadow-green-500/20 flex items-center justify-center gap-2">
                                    <i class="bi bi-check-circle"></i> Save Changes
                                </button>
                                <a href="teacher_dashboard.php" class="px-6 py-2.5 rounded-xl bg-gray-100 text-gray-600 font-medium hover:bg-gray-200 transition">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                    
                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 text-center">
                         <a href="change_password.php" class="text-green-600 hover:text-green-800 font-medium text-sm flex items-center justify-center gap-2">
                            <i class="bi bi-key"></i> Want to change your password?
                        </a>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

</body>
</html>
