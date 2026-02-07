<?php
require_once __DIR__ . '/../db.php';

$error = "";
$success = "";

if (isset($_POST['signup'])) {
    
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Server-side validation
    if (empty($name) || !preg_match("/^[a-zA-Z\s]+$/", $name)) {
        $error = "Name should contain only letters and spaces";
    } 
    else if (strlen($name) < 3 || strlen($name) > 50) {
        $error = "Name must be between 3 and 50 characters";
    }
    else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    }
    // Password strength validation
    else if (strlen($password) < 8) {
        $error = "Password must be at least 8 characters";
    }
    else if (!preg_match("/[A-Z]/", $password)) {
        $error = "Password must contain at least one uppercase letter";
    }
    else if (!preg_match("/[a-z]/", $password)) {
        $error = "Password must contain at least one lowercase letter";
    }
    else if (!preg_match("/[0-9]/", $password)) {
        $error = "Password must contain at least one number";
    }
    else {
        // Check for duplicate email using prepared statement
        $checkStmt = mysqli_prepare($conn, "SELECT id FROM students WHERE email = ?");
        mysqli_stmt_bind_param($checkStmt, "s", $email);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);
        
        if (mysqli_num_rows($checkResult) > 0) {
            $error = "Email already exists";
        } else {
            // Hash password and insert
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = mysqli_prepare($conn, "INSERT INTO students (name, email, password) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sss", $name, $email, $hashedPassword);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "Account created successfully! Redirecting to login...";
                header("refresh:2;url=student_login.php");
            } else {
                $error = "Registration failed. Please try again.";
            }
            
            mysqli_stmt_close($stmt);
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
    <title>Student Signup | NEP Portal</title>
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
</head>
<body class="bg-gray-50 font-sans text-gray-900 min-h-screen flex items-center justify-center relative overflow-hidden py-12">

    <!-- Background Decoration -->
    <div class="absolute inset-0 -z-10 overflow-hidden">
        <div class="absolute top-0 right-0 w-[80%] h-[80%] rounded-full bg-blue-100/40 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-[80%] h-[80%] rounded-full bg-indigo-100/40 blur-3xl"></div>
    </div>

    <div class="w-full max-w-lg bg-white p-8 rounded-3xl shadow-2xl border border-gray-100 relative z-10 m-4">
        
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Create Account</h1>
            <p class="text-gray-500 mt-2">Join the learning revolution</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">⚠️</div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700 font-medium"><?php echo $error; ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">🎉</div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700 font-medium"><?php echo $success; ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5" onsubmit="return validateForm()">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                <input type="text" id="name" name="name" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition bg-gray-50 focus:bg-white" placeholder="John Doe" required>
                <span class="text-red-500 text-xs mt-1 block" id="nameError"></span>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                <input type="email" id="email" name="email" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition bg-gray-50 focus:bg-white" placeholder="student@example.com" required>
                <span class="text-red-500 text-xs mt-1 block" id="emailError"></span>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <div class="relative">
                    <input type="password" id="password" name="password" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition bg-gray-50 focus:bg-white" placeholder="••••••••" required>
                </div>
                <span class="text-red-500 text-xs mt-1 block" id="passwordError"></span>
                
                <!-- Password Strength Indicators -->
                <div class="mt-3 grid grid-cols-2 gap-2 text-xs text-gray-500" id="passwordStrength">
                    <div id="length" class="flex items-center gap-1 transition-colors duration-300">
                        <span class="w-2 h-2 rounded-full bg-gray-300"></span> 8+ Characters
                    </div>
                    <div id="uppercase" class="flex items-center gap-1 transition-colors duration-300">
                        <span class="w-2 h-2 rounded-full bg-gray-300"></span> 1 Uppercase
                    </div>
                    <div id="lowercase" class="flex items-center gap-1 transition-colors duration-300">
                        <span class="w-2 h-2 rounded-full bg-gray-300"></span> 1 Lowercase
                    </div>
                    <div id="number" class="flex items-center gap-1 transition-colors duration-300">
                        <span class="w-2 h-2 rounded-full bg-gray-300"></span> 1 Number
                    </div>
                </div>
            </div>

            <button type="submit" name="signup" class="w-full py-4 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700 transition transform hover:-translate-y-0.5 shadow-xl shadow-blue-500/20 mt-2">
                Create Account
            </button>
        </form>

        <div class="mt-8 text-center border-t border-gray-100 pt-6">
            <p class="text-gray-500 text-sm">Already have an account? 
                <a href="student_login.php" class="text-blue-600 font-bold hover:text-blue-700 transition">Log in</a>
            </p>
        </div>
    </div>

    <script>
        const nameInput = document.getElementById('name');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');

        // Real-time validation listeners
        nameInput.addEventListener('input', validateName);
        emailInput.addEventListener('input', validateEmail);
        passwordInput.addEventListener('input', validatePassword);

        function validateName() {
            const name = nameInput.value.trim();
            const errorEl = document.getElementById('nameError');
            if (name.length > 0 && name.length < 3) {
                errorEl.textContent = 'Name must be at least 3 characters';
                return false;
            }
            errorEl.textContent = '';
            return true;
        }

        function validateEmail() {
            // Basic format check on input
            const email = emailInput.value.trim();
            const errorEl = document.getElementById('emailError');
            errorEl.textContent = ''; // Clear error typing
            return true; 
        }

        function validatePassword() {
            const password = passwordInput.value;
            const errorEl = document.getElementById('passwordError');
            
            // Requirements
            const hasLength = password.length >= 8;
            const hasUpper = /[A-Z]/.test(password);
            const hasLower = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);

            // Update UI
            updateIndicator('length', hasLength);
            updateIndicator('uppercase', hasUpper);
            updateIndicator('lowercase', hasLower);
            updateIndicator('number', hasNumber);

            errorEl.textContent = '';
            return hasLength && hasUpper && hasLower && hasNumber;
        }

        function updateIndicator(id, isValid) {
            const el = document.getElementById(id);
            const dot = el.querySelector('span');
            
            if (isValid) {
                el.classList.remove('text-gray-500');
                el.classList.add('text-green-600', 'font-medium');
                dot.classList.remove('bg-gray-300');
                dot.classList.add('bg-green-500');
            } else {
                el.classList.add('text-gray-500');
                el.classList.remove('text-green-600', 'font-medium');
                dot.classList.add('bg-gray-300');
                dot.classList.remove('bg-green-500');
            }
        }

        function validateForm() {
            const nameValid = validateName();
            const passValid = validatePassword();
            
            if (!passValid) {
                document.getElementById('passwordError').textContent = 'Please meet all password requirements';
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
