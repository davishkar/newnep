<?php
session_start();
require_once __DIR__ . '/../db.php';

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

// Fetch current student data
$stmt = mysqli_prepare($conn, "SELECT * FROM students WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $abc_id = trim($_POST['abc_id']);
    
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
    elseif (!empty($mobile) && !preg_match("/^[0-9]{10}$/", $mobile)) {
        $error = "Mobile number must be exactly 10 digits";
    }
    elseif (!empty($abc_id) && !preg_match("/^[A-Za-z0-9]{6,}$/", $abc_id)) {
        $error = "ABC ID must be at least 6 alphanumeric characters";
    }
    else {
        // Check if email already exists (for other students)
        $checkStmt = mysqli_prepare($conn, "SELECT id FROM students WHERE email = ? AND id != ?");
        mysqli_stmt_bind_param($checkStmt, "si", $email, $student_id);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);
        
        if (mysqli_num_rows($checkResult) > 0) {
            $error = "Email already exists for another student";
        } else {
            // Update profile
            $updateStmt = mysqli_prepare($conn, "UPDATE students SET name = ?, email = ?, mobile = ?, abc_id = ? WHERE id = ?");
            mysqli_stmt_bind_param($updateStmt, "ssssi", $name, $email, $mobile, $abc_id, $student_id);
            
            if (mysqli_stmt_execute($updateStmt)) {
                $message = "Profile updated successfully!";
                $_SESSION['student_name'] = $name; // Update session
                
                // Refresh student data
                $student['name'] = $name;
                $student['email'] = $email;
                $student['mobile'] = $mobile;
                $student['abc_id'] = $abc_id;
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
    <title>Edit Profile | Student Portal</title>
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
                    <a href="profile.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-indigo-50 text-indigo-700 font-medium">
                        <i class="bi bi-person-circle"></i> Profile
                    </a>
                </li>
                <li>
                    <a href="change_password.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
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
                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold">
                    <?php echo substr($student['name'], 0, 1); ?>
                </div>
                <div class="text-sm">
                    <p class="font-medium text-gray-900"><?php echo htmlspecialchars($student['name']); ?></p>
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
            
            <div class="max-w-2xl mx-auto">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Edit Profile</h1>
                    <p class="text-gray-500 text-sm">Update your personal information</p>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
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

                        <form method="POST" id="profileForm" class="space-y-5">
                            
                            <div class="flex items-center gap-4 mb-6">
                                <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-2xl font-bold">
                                    <?php echo substr($student['name'], 0, 1); ?>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900 text-lg"><?php echo htmlspecialchars($student['name']); ?></h3>
                                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($student['email']); ?></p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">
                                        <i class="bi bi-person"></i>
                                    </span>
                                    <input type="text" name="name" value="<?php echo htmlspecialchars($student['name']); ?>" required
                                           class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition bg-gray-50 focus:bg-white text-gray-900 text-sm">
                                </div>
                                <p class="text-red-500 text-xs mt-1 hidden" id="nameError">Name should contain only letters and spaces (3-50 chars)</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">
                                        <i class="bi bi-envelope"></i>
                                    </span>
                                    <input type="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required
                                           class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition bg-gray-50 focus:bg-white text-gray-900 text-sm">
                                </div>
                                <p class="text-red-500 text-xs mt-1 hidden" id="emailError">Please enter a valid email address</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Mobile Number</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">
                                            <i class="bi bi-phone"></i>
                                        </span>
                                        <input type="text" name="mobile" value="<?php echo htmlspecialchars($student['mobile'] ?? ''); ?>" maxlength="10"
                                               class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition bg-gray-50 focus:bg-white text-gray-900 text-sm">
                                    </div>
                                    <p class="text-red-500 text-xs mt-1 hidden" id="mobileError">Mobile number must be exactly 10 digits</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">ABC ID</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">
                                            <i class="bi bi-credit-card-2-front"></i>
                                        </span>
                                        <input type="text" name="abc_id" value="<?php echo htmlspecialchars($student['abc_id'] ?? ''); ?>"
                                               class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition bg-gray-50 focus:bg-white text-gray-900 text-sm">
                                    </div>
                                    <p class="text-red-500 text-xs mt-1 hidden" id="abcError">ABC ID must be at least 6 alphanumeric characters</p>
                                </div>
                            </div>

                            <div class="pt-4 flex items-center justify-end gap-3">
                                <a href="student_dashboard.php" class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition text-sm">
                                    Cancel
                                </a>
                                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-medium hover:bg-indigo-700 transition shadow-lg shadow-indigo-500/20 text-sm flex items-center gap-2">
                                    <i class="bi bi-check-circle"></i> Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-center">
                         <a href="change_password.php" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium flex items-center gap-2 transition">
                            <i class="bi bi-key"></i> Need to change your password?
                        </a>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

<script>
// Real-time validation
const form = document.getElementById('profileForm');
const nameInput = form.querySelector('[name="name"]');
const emailInput = form.querySelector('[name="email"]');
const mobileInput = form.querySelector('[name="mobile"]');
const abcInput = form.querySelector('[name="abc_id"]');

function toggleError(input, errorId, show) {
    const errorEl = document.getElementById(errorId);
    if (show) {
        errorEl.classList.remove('hidden');
        input.classList.add('border-red-500', 'focus:ring-red-500', 'focus:border-red-500');
        input.classList.remove('border-gray-300', 'focus:ring-indigo-500', 'focus:border-indigo-500');
    } else {
        errorEl.classList.add('hidden');
        input.classList.remove('border-red-500', 'focus:ring-red-500', 'focus:border-red-500');
        input.classList.add('border-gray-300', 'focus:ring-indigo-500', 'focus:border-indigo-500');
    }
}

nameInput.addEventListener('blur', function() {
    const namePattern = /^[a-zA-Z\s]+$/;
    const isValid = namePattern.test(this.value) && this.value.length >= 3 && this.value.length <= 50;
    toggleError(this, 'nameError', !isValid);
});

emailInput.addEventListener('blur', function() {
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    toggleError(this, 'emailError', !emailPattern.test(this.value));
});

mobileInput.addEventListener('blur', function() {
    if (this.value) {
        toggleError(this, 'mobileError', !/^[0-9]{10}$/.test(this.value));
    } else {
        toggleError(this, 'mobileError', false);
    }
});

abcInput.addEventListener('blur', function() {
    if (this.value) {
        toggleError(this, 'abcError', !/^[A-Za-z0-9]{6,}$/.test(this.value));
    } else {
        toggleError(this, 'abcError', false);
    }
});

form.addEventListener('submit', function(e) {
    const invalidInputs = form.querySelectorAll('.border-red-500');
    if (invalidInputs.length > 0) {
        e.preventDefault();
        alert('Please fix all errors before submitting');
    }
});
</script>

</body>
</html>
