<?php
session_start();
require_once __DIR__ . '/../db.php';

// Session timeout check
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header("Location: admin_login.php?timeout=1");
    exit;
}
$_SESSION['last_activity'] = time();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$admin_id = intval($_SESSION['admin_id']);
$message = "";
$error = "";

// Fetch admin details
$stmt = mysqli_prepare($conn, "SELECT * FROM admins WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $admin_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$admin = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    
    // Validation
    if (empty($name) || strlen($name) < 3) {
        $error = "Name must be at least 3 characters";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        // Check duplicate email
        $checkStmt = mysqli_prepare($conn, "SELECT id FROM admins WHERE email = ? AND id != ?");
        mysqli_stmt_bind_param($checkStmt, "si", $email, $admin_id);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);
        
        if (mysqli_num_rows($checkResult) > 0) {
            $error = "Email already exists";
        } else {
            // Update profile
            $updateStmt = mysqli_prepare($conn, "UPDATE admins SET name = ?, email = ? WHERE id = ?");
            mysqli_stmt_bind_param($updateStmt, "ssi", $name, $email, $admin_id);
            
            if (mysqli_stmt_execute($updateStmt)) {
                $_SESSION['admin_name'] = $name;
                $message = "Profile updated successfully!";
                $admin['name'] = $name;
                $admin['email'] = $email;
            } else {
                $error = "Failed to update profile";
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
    <title>Admin Profile | Admin Panel</title>
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
                    <a href="profile.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-indigo-50 text-indigo-700 font-medium">
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
            
            <div class="max-w-4xl mx-auto">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Admin Profile</h1>
                        <p class="text-gray-600 text-sm mt-1">Manage your account information.</p>
                    </div>
                </div>

                <!-- ALERTS -->
                <?php if (!empty($message)): ?>
                <div class="mb-6 p-4 rounded-lg bg-green-50 text-green-700 border border-green-200 flex items-center gap-3">
                    <i class="bi bi-check-circle-fill text-xl"></i>
                    <?php echo $message; ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                <div class="mb-6 p-4 rounded-lg bg-red-50 text-red-700 border border-red-200 flex items-center gap-3">
                    <i class="bi bi-exclamation-triangle-fill text-xl"></i>
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    
                    <!-- Profile Header Card -->
                    <div class="md:col-span-1">
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 text-center">
                            <div class="w-24 h-24 mx-auto bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 text-4xl mb-4">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <h2 class="text-xl font-bold text-gray-900 mb-1"><?php echo htmlspecialchars($admin['name']); ?></h2>
                             <p class="text-gray-500 text-sm mb-4"><?php echo htmlspecialchars($admin['email']); ?></p>
                             <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700">Administrator</span>
                        </div>
                         
                         <!-- quick links -->
                         <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mt-6 overflow-hidden">
                             <a href="change_password.php" class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition border-b border-gray-100">
                                 <span class="text-gray-700 font-medium flex items-center gap-2"><i class="bi bi-shield-lock"></i> Change Password</span>
                                 <i class="bi bi-chevron-right text-gray-400"></i>
                             </a>
                             <a href="admin_logout.php" class="flex items-center justify-between px-6 py-4 hover:bg-red-50 transition text-red-600">
                                 <span class="font-medium flex items-center gap-2"><i class="bi bi-box-arrow-right"></i> Logout</span>
                                 <i class="bi bi-chevron-right text-red-300"></i>
                             </a>
                         </div>
                    </div>

                    <!-- Edit Profile Form -->
                    <div class="md:col-span-2">
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                                <i class="bi bi-pencil-square text-gray-400"></i> Edit Profile
                            </h3>
                            <form method="POST" id="profileForm">
                                <div class="mb-5">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                                    <input type="text" name="name" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition" value="<?php echo htmlspecialchars($admin['name']); ?>" required minlength="3">
                                    <p class="text-xs text-gray-500 mt-1">Name must be at least 3 characters.</p>
                                </div>

                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                                    <input type="email" name="email" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                                </div>

                                <div class="flex justify-end">
                                     <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition shadow-sm flex items-center gap-2">
                                        <i class="bi bi-save"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
            
        </main>
    </div>
</div>

<script>
    // Client-side validation
    document.getElementById('profileForm').addEventListener('submit', function(e) {
        const nameInput = this.querySelector('[name="name"]');
        const emailInput = this.querySelector('[name="email"]');
        
        if (nameInput.value.trim().length < 3) {
            e.preventDefault();
            alert('Name must be at least 3 characters');
            nameInput.focus();
            return false;
        }
        
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(emailInput.value.trim())) {
            e.preventDefault();
            alert('Please enter a valid email address');
            emailInput.focus();
            return false;
        }
    });
</script>

</body>
</html>
