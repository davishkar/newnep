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

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Fetch current password
    $stmt = mysqli_prepare($conn, "SELECT password FROM admins WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $admin_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $admin = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    // Verify current password
    $passwordMatch = false;
    if (password_verify($current_password, $admin['password'])) {
        $passwordMatch = true;
    } elseif ($current_password === $admin['password']) {
        // Plain text password (backward compatibility)
        $passwordMatch = true;
    }
    
    if (!$passwordMatch) {
        $error = "Current password is incorrect";
    } elseif (strlen($new_password) < 8) {
        $error = "New password must be at least 8 characters";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (!preg_match('/[A-Z]/', $new_password)) {
        $error = "Password must contain at least one uppercase letter";
    } elseif (!preg_match('/[a-z]/', $new_password)) {
        $error = "Password must contain at least one lowercase letter";
    } elseif (!preg_match('/[0-9]/', $new_password)) {
        $error = "Password must contain at least one number";
    } else {
        // Update password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $updateStmt = mysqli_prepare($conn, "UPDATE admins SET password = ? WHERE id = ?");
        mysqli_stmt_bind_param($updateStmt, "si", $hashed_password, $admin_id);
        
        if (mysqli_stmt_execute($updateStmt)) {
            $message = "Password changed successfully!";
        } else {
            $error = "Failed to change password";
        }
        mysqli_stmt_close($updateStmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password | Admin Panel</title>
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
    <style>
        .requirement-item { transition: all 0.3s ease; }
        .requirement-item.met { color: #047857; background-color: #ecfdf5; border-color: #a7f3d0; }
        .requirement-item.met i { color: #059669; }
    </style>
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
                    <a href="profile.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                        <i class="bi bi-person-circle"></i> Profile
                    </a>
                </li>
                <li>
                    <a href="change_password.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-indigo-50 text-indigo-700 font-medium">
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
            
            <div class="max-w-2xl mx-auto">
                
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Change Password</h1>
                        <p class="text-gray-600 text-sm mt-1">Update your existing password for security.</p>
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

                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
                    <form method="POST" id="passwordForm">
                        
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                            <div class="relative">
                                <input type="password" name="current_password" id="currentPassword" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition pr-10" required>
                                <button type="button" class="absolute right-3 top-3.5 text-gray-400 hover:text-gray-600" onclick="togglePassword('currentPassword', this)">
                                    <i class="bi bi-eye text-lg"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                            <div class="relative">
                                <input type="password" name="new_password" id="newPassword" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition pr-10" required minlength="8">
                                <button type="button" class="absolute right-3 top-3.5 text-gray-400 hover:text-gray-600" onclick="togglePassword('newPassword', this)">
                                    <i class="bi bi-eye text-lg"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-8">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                            <div class="relative">
                                <input type="password" name="confirm_password" id="confirmPassword" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition pr-10" required>
                                <button type="button" class="absolute right-3 top-3.5 text-gray-400 hover:text-gray-600" onclick="togglePassword('confirmPassword', this)">
                                    <i class="bi bi-eye text-lg"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Password Requirements -->
                        <div class="bg-gray-50 rounded-xl p-5 mb-8 border border-gray-200">
                            <h4 class="text-sm font-semibold text-gray-900 mb-3">Password Requirements:</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div id="req-length" class="requirement-item flex items-center gap-2 text-sm text-gray-500 p-2 rounded border border-transparent">
                                    <i class="bi bi-circle text-xs"></i> 8+ characters
                                </div>
                                <div id="req-upper" class="requirement-item flex items-center gap-2 text-sm text-gray-500 p-2 rounded border border-transparent">
                                    <i class="bi bi-circle text-xs"></i> Uppercase letter
                                </div>
                                <div id="req-lower" class="requirement-item flex items-center gap-2 text-sm text-gray-500 p-2 rounded border border-transparent">
                                    <i class="bi bi-circle text-xs"></i> Lowercase letter
                                </div>
                                <div id="req-number" class="requirement-item flex items-center gap-2 text-sm text-gray-500 p-2 rounded border border-transparent">
                                    <i class="bi bi-circle text-xs"></i> Number
                                </div>
                                <div id="req-match" class="col-span-1 sm:col-span-2 requirement-item flex items-center gap-2 text-sm text-gray-500 p-2 rounded border border-transparent">
                                    <i class="bi bi-circle text-xs"></i> Passwords match
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold text-lg transition shadow-lg shadow-indigo-200 disabled:opacity-50 disabled:cursor-not-allowed" id="submitBtn" disabled>
                            Change Password
                        </button>
                    </form>
                </div>

            </div>
            
        </main>
    </div>
</div>

<script>
    function togglePassword(fieldId, btn) {
        const field = document.getElementById(fieldId);
        const icon = btn.querySelector('i');
        
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }

    const newPassword = document.getElementById('newPassword');
    const confirmPassword = document.getElementById('confirmPassword');
    const submitBtn = document.getElementById('submitBtn');

    function checkRequirements() {
        const password = newPassword.value;
        const confirm = confirmPassword.value;
        
        // Check length
        const lengthMet = password.length >= 8;
        updateRequirement('req-length', lengthMet);
        
        // Check uppercase
        const upperMet = /[A-Z]/.test(password);
        updateRequirement('req-upper', upperMet);
        
        // Check lowercase
        const lowerMet = /[a-z]/.test(password);
        updateRequirement('req-lower', lowerMet);
        
        // Check number
        const numberMet = /[0-9]/.test(password);
        updateRequirement('req-number', numberMet);
        
        // Check match
        const matchMet = password.length > 0 && password === confirm;
        updateRequirement('req-match', matchMet);
        
        // Enable submit if all met
        const allMet = lengthMet && upperMet && lowerMet && numberMet && matchMet;
        submitBtn.disabled = !allMet;
    }

    function updateRequirement(id, met) {
        const elem = document.getElementById(id);
        const icon = elem.querySelector('i');
        
        if (met) {
            elem.classList.add('met');
            icon.classList.remove('bi-circle');
            icon.classList.add('bi-check-circle-fill');
        } else {
            elem.classList.remove('met');
            icon.classList.remove('bi-check-circle-fill');
            icon.classList.add('bi-circle');
        }
    }

    newPassword.addEventListener('input', checkRequirements);
    confirmPassword.addEventListener('input', checkRequirements);
</script>

</body>
</html>
