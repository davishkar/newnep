<?php
session_start();
include "../db.php";

// Session timeout check
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header("Location: student_login.php?timeout=1");
    exit;
}
$_SESSION['last_activity'] = time();

if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit;
}

$student_id = intval($_SESSION['student_id']);
$message = "";
$error = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Fetch current password
    $stmt = mysqli_prepare($conn, "SELECT password FROM students WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $student_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $student = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    // Validate current password
    $passwordValid = false;
    if (password_verify($currentPassword, $student['password'])) {
        $passwordValid = true;
    } elseif ($currentPassword === $student['password']) {
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
        
        $updateStmt = mysqli_prepare($conn, "UPDATE students SET password = ? WHERE id = ?");
        mysqli_stmt_bind_param($updateStmt, "si", $hashedPassword, $student_id);
        
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
    <title>Change Password | Student Portal</title>
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
            <span class="text-xl font-bold text-indigo-600">Student<span class="text-gray-900">Portal</span></span>
        </div>
        
        <nav class="flex-1 overflow-y-auto py-4">
            <ul class="space-y-1 px-3">
                <li>
                    <a href="student_dashboard.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                        <i class="bi bi-grid-fill"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="my_subjects.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                        <i class="bi bi-book-half"></i> My Subjects
                    </a>
                </li>
                <li>
                    <a href="view_marks.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                        <i class="bi bi-clipboard-data"></i> View Marks
                    </a>
                </li>
                <li>
                    <a href="attendance.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                        <i class="bi bi-calendar-check"></i> Attendance
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
                        <i class="bi bi-key"></i> Password
                    </a>
                </li>
                 <li>
                    <a href="logout.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-red-600 hover:bg-red-50 transition">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </li>
            </ul>
        </nav>
        
        <div class="p-4 border-t border-gray-200">
            <div class="flex items-center gap-3">
                <!-- Using session name directly if $student variable isn't available from earlier includes, 
                     though looking at PHP code, $student is not fetched unless POST, so let's use $_SESSION -->
                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold">
                    <?php echo substr($_SESSION['student_name'], 0, 1); ?>
                </div>
                <div class="text-sm">
                    <p class="font-medium text-gray-900"><?php echo $_SESSION['student_name']; ?></p>
                    <p class="text-gray-500 text-xs">Student</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        
        <!-- Mobile Header -->
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 md:hidden">
            <span class="text-xl font-bold text-indigo-600">NEP Portal</span>
            <button class="text-gray-500 hover:text-gray-700">
                <i class="bi bi-list text-2xl"></i>
            </button>
        </header>

        <!-- Main Scrollable Area -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4 md:p-6">
            
            <div class="max-w-lg mx-auto mt-10">
                
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 bg-gray-50">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <i class="bi bi-shield-lock text-indigo-500"></i> Change Password
                        </h2>
                        <p class="text-gray-500 text-sm mt-1">Ensure your account stays secure</p>
                    </div>

                    <div class="p-6">
                        
                        <?php if (!empty($message)): ?>
                        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-r-lg flex items-center">
                            <i class="bi bi-check-circle-fill text-green-500 text-xl mr-3"></i>
                            <p class="text-sm text-green-700 font-medium"><?php echo $message; ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($error)): ?>
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg flex items-center">
                            <i class="bi bi-exclamation-triangle-fill text-red-500 text-xl mr-3"></i>
                            <p class="text-sm text-red-700 font-medium"><?php echo $error; ?></p>
                        </div>
                        <?php endif; ?>

                        <form method="POST" id="passwordForm" class="space-y-5">
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Current Password *</label>
                                <div class="relative">
                                    <input type="password" name="current_password" id="currentPassword" required
                                           class="w-full pl-4 pr-10 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition bg-gray-50 focus:bg-white text-gray-900 text-sm">
                                    <i class="bi bi-eye absolute right-3 top-3 text-gray-400 hover:text-gray-600 cursor-pointer transition" onclick="togglePassword('currentPassword', this)"></i>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">New Password *</label>
                                <div class="relative">
                                    <input type="password" name="new_password" id="newPassword" required
                                           class="w-full pl-4 pr-10 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition bg-gray-50 focus:bg-white text-gray-900 text-sm">
                                    <i class="bi bi-eye absolute right-3 top-3 text-gray-400 hover:text-gray-600 cursor-pointer transition" onclick="togglePassword('newPassword', this)"></i>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password *</label>
                                <div class="relative">
                                    <input type="password" name="confirm_password" id="confirmPassword" required
                                           class="w-full pl-4 pr-10 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition bg-gray-50 focus:bg-white text-gray-900 text-sm">
                                    <i class="bi bi-eye absolute right-3 top-3 text-gray-400 hover:text-gray-600 cursor-pointer transition" onclick="togglePassword('confirmPassword', this)"></i>
                                </div>
                            </div>

                            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Password Requirements</h4>
                                <ul class="space-y-1">
                                    <li class="requirement flex items-center gap-2 text-xs text-gray-500 transition-colors duration-200" id="req-length">
                                        <i class="bi bi-circle text-[10px]"></i> At least 8 characters
                                    </li>
                                    <li class="requirement flex items-center gap-2 text-xs text-gray-500 transition-colors duration-200" id="req-upper">
                                        <i class="bi bi-circle text-[10px]"></i> One uppercase letter
                                    </li>
                                    <li class="requirement flex items-center gap-2 text-xs text-gray-500 transition-colors duration-200" id="req-lower">
                                        <i class="bi bi-circle text-[10px]"></i> One lowercase letter
                                    </li>
                                    <li class="requirement flex items-center gap-2 text-xs text-gray-500 transition-colors duration-200" id="req-number">
                                        <i class="bi bi-circle text-[10px]"></i> One number
                                    </li>
                                    <li class="requirement flex items-center gap-2 text-xs text-gray-500 transition-colors duration-200" id="req-match">
                                        <i class="bi bi-circle text-[10px]"></i> Passwords match
                                    </li>
                                </ul>
                            </div>

                            <div class="pt-2 flex items-center justify-end gap-3">
                                <a href="profile.php" class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition text-sm">
                                    Cancel
                                </a>
                                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-medium hover:bg-indigo-700 transition shadow-lg shadow-indigo-500/20 text-sm flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed" id="submitBtn" disabled>
                                    <i class="bi bi-check-circle"></i> Change Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

<script>
function togglePassword(fieldId, icon) {
    const field = document.getElementById(fieldId);
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

function updateRequirement(id, met) {
    const el = document.getElementById(id);
    const icon = el.querySelector('i');
    
    if (met) {
        el.classList.remove('text-gray-500');
        el.classList.add('text-green-600', 'font-medium');
        icon.classList.remove('bi-circle');
        icon.classList.add('bi-check-circle-fill');
    } else {
        el.classList.add('text-gray-500');
        el.classList.remove('text-green-600', 'font-medium');
        icon.classList.add('bi-circle');
        icon.classList.remove('bi-check-circle-fill');
    }
}

function checkRequirements() {
    const password = newPassword.value;
    const confirm = confirmPassword.value;
    
    // Check requirements
    const lenMet = password.length >= 8;
    const upperMet = /[A-Z]/.test(password);
    const lowerMet = /[a-z]/.test(password);
    const numMet = /[0-9]/.test(password);
    const matchMet = password && confirm && password === confirm;
    
    updateRequirement('req-length', lenMet);
    updateRequirement('req-upper', upperMet);
    updateRequirement('req-lower', lowerMet);
    updateRequirement('req-number', numMet);
    updateRequirement('req-match', matchMet);
    
    // Enable submit if all requirements met
    const allMet = lenMet && upperMet && lowerMet && numMet && matchMet;
    submitBtn.disabled = !allMet;
}

newPassword.addEventListener('input', checkRequirements);
confirmPassword.addEventListener('input', checkRequirements);
</script>

</body>
</html>
