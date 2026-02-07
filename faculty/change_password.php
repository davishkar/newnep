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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Fetch current password
    $stmt = mysqli_prepare($conn, "SELECT password FROM teachers WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $teacher_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $teacher = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    // Validate current password
    $passwordValid = false;
    if (password_verify($currentPassword, $teacher['password'])) {
        $passwordValid = true;
    } elseif ($currentPassword === $teacher['password']) {
        // Backward compatibility for plain text passwords
        $passwordValid = true;
    }
    
    if (!$passwordValid) {
        $error = "Current password is incorrect";
    }
    elseif (strlen($newPassword) < 8) {
        $error = "New password must be at least 8 characters";
    }
    elseif (!preg_match("/[A-Z]/", $newPassword)) {
        $error = "New password must contain at least one uppercase letter";
    }
    elseif (!preg_match("/[a-z]/", $newPassword)) {
        $error = "New password must contain at least one lowercase letter";
    }
    elseif (!preg_match("/[0-9]/", $newPassword)) {
        $error = "New password must contain at least one number";
    }
    elseif ($newPassword !== $confirmPassword) {
        $error = "New password and confirm password do not match";
    }
    else {
        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $updateStmt = mysqli_prepare($conn, "UPDATE teachers SET password = ? WHERE id = ?");
        mysqli_stmt_bind_param($updateStmt, "si", $hashedPassword, $teacher_id);
        
        if (mysqli_stmt_execute($updateStmt)) {
            $message = "Password changed successfully!";
        } else {
            $error = "Failed to change password. Please try again.";
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
    <title>Change Password | Faculty Portal</title>
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
            
            <div class="max-w-2xl mx-auto">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Change Password</h1>
                    <p class="text-gray-500 text-sm">Update your password to keep your account secure.</p>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="border-b border-gray-100 p-6 bg-gradient-to-r from-green-50 to-white">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center text-xl text-green-600">
                                <i class="bi bi-shield-lock-fill"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">Security Settings</h2>
                                <p class="text-sm text-gray-500">Ensure your new password is strong</p>
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

                        <form method="POST" id="passwordForm" class="space-y-6">
                            <div class="relative">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Current Password <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="password" name="current_password" id="currentPassword" class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition bg-white pr-10" required>
                                    <button type="button" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600" onclick="togglePassword('currentPassword', this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="relative">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    New Password <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="password" name="new_password" id="newPassword" class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition bg-white pr-10" required>
                                    <button type="button" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600" onclick="togglePassword('newPassword', this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="relative">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Confirm New Password <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="password" name="confirm_password" id="confirmPassword" class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition bg-white pr-10" required>
                                    <button type="button" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600" onclick="togglePassword('confirmPassword', this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                                <h4 class="text-sm font-semibold text-gray-700 mb-2">Password Requirements:</h4>
                                <ul class="space-y-1">
                                    <li class="requirement flex items-center gap-2 text-xs text-gray-500" id="req-length">
                                        <i class="bi bi-circle-fill text-[8px]"></i> At least 8 characters
                                    </li>
                                    <li class="requirement flex items-center gap-2 text-xs text-gray-500" id="req-upper">
                                        <i class="bi bi-circle-fill text-[8px]"></i> One uppercase letter
                                    </li>
                                    <li class="requirement flex items-center gap-2 text-xs text-gray-500" id="req-lower">
                                        <i class="bi bi-circle-fill text-[8px]"></i> One lowercase letter
                                    </li>
                                    <li class="requirement flex items-center gap-2 text-xs text-gray-500" id="req-number">
                                        <i class="bi bi-circle-fill text-[8px]"></i> One number
                                    </li>
                                    <li class="requirement flex items-center gap-2 text-xs text-gray-500" id="req-match">
                                        <i class="bi bi-circle-fill text-[8px]"></i> Passwords match
                                    </li>
                                </ul>
                            </div>

                            <div class="flex items-center gap-4 pt-2">
                                <button type="submit" id="submitBtn" class="flex-1 py-2.5 rounded-xl bg-green-600 text-white font-medium hover:bg-green-700 transition shadow-lg shadow-green-500/20 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                    <i class="bi bi-check-circle"></i> Update Password
                                </button>
                                <a href="profile.php" class="px-6 py-2.5 rounded-xl bg-gray-100 text-gray-600 font-medium hover:bg-gray-200 transition">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
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
    
    // Helper to update UI
    const updateReq = (id, valid) => {
        const el = document.getElementById(id);
        const icon = el.querySelector('i');
        if (valid) {
            el.classList.remove('text-gray-500');
            el.classList.add('text-green-600');
            icon.classList.add('text-green-600');
        } else {
            el.classList.add('text-gray-500');
            el.classList.remove('text-green-600');
            icon.classList.remove('text-green-600');
        }
        return valid;
    };

    const isLengthValid = updateReq('req-length', password.length >= 8);
    const isUpperValid = updateReq('req-upper', /[A-Z]/.test(password));
    const isLowerValid = updateReq('req-lower', /[a-z]/.test(password));
    const isNumberValid = updateReq('req-number', /[0-9]/.test(password));
    const isMatchValid = updateReq('req-match', password && confirm && password === confirm);
    
    const allMet = isLengthValid && isUpperValid && isLowerValid && isNumberValid && isMatchValid;
    submitBtn.disabled = !allMet;
}

newPassword.addEventListener('input', checkRequirements);
confirmPassword.addEventListener('input', checkRequirements);
</script>

</body>
</html>
