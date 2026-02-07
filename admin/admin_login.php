<?php
session_start();
require_once __DIR__ . '/../db.php';

$error = "";

if (isset($_POST['login'])) {
    
    // Rate limiting check
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt_time'] = time();
    }
    
    // Reset attempts after 15 minutes
    if (time() - $_SESSION['last_attempt_time'] > 900) {
        $_SESSION['login_attempts'] = 0;
    }
    
    if ($_SESSION['login_attempts'] >= 5) {
        $error = "Too many login attempts. Please try again after 15 minutes.";
    } else {
        
        // Server-side validation
        $email = trim($_POST['email']);
        $pass  = trim($_POST['password']);
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format";
        } 
        // Validate password length
        else if (strlen($pass) < 6) {
            $error = "Password must be at least 6 characters";
        } 
        else {
            // Prepared statement to prevent SQL injection
            $stmt = mysqli_prepare($conn, "SELECT id, name, password FROM admins WHERE email = ?");
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) == 1) {
                $row = mysqli_fetch_assoc($result);
                
                // Check if password is hashed or plain text (for backward compatibility)
                $isValidPassword = false;
                
                // Try password_verify first (for hashed passwords)
                if (password_verify($pass, $row['password'])) {
                    $isValidPassword = true;
                } 
                // Fallback to plain text comparison (for old passwords)
                else if ($pass === $row['password']) {
                    $isValidPassword = true;
                    
                    // Update to hashed password
                    $hashedPass = password_hash($pass, PASSWORD_DEFAULT);
                    $updateStmt = mysqli_prepare($conn, "UPDATE admins SET password = ? WHERE id = ?");
                    mysqli_stmt_bind_param($updateStmt, "si", $hashedPass, $row['id']);
                    mysqli_stmt_execute($updateStmt);
                    mysqli_stmt_close($updateStmt);
                }
                
                if ($isValidPassword) {
                    session_regenerate_id(true);
                    $_SESSION['admin_id']   = $row['id'];
                    $_SESSION['admin_name'] = $row['name'];
                    $_SESSION['login_attempts'] = 0; // Reset attempts
                    
                    header("Location: admin_dashboard.php");
                    exit;
                } else {
                    $error = "Invalid Password";
                    $_SESSION['login_attempts']++;
                    $_SESSION['last_attempt_time'] = time();
                }
            } else {
                $error = "Admin not found";
                $_SESSION['login_attempts']++;
                $_SESSION['last_attempt_time'] = time();
            }
            
            mysqli_stmt_close($stmt);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | NEP Portal</title>
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
<body class="bg-gray-100 flex items-center justify-center min-h-screen font-sans">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-indigo-600 px-8 py-6 text-center">
            <h2 class="text-2xl font-bold text-white tracking-wide">Admin Portal</h2>
            <p class="text-indigo-200 text-sm mt-1">Please login to continue</p>
        </div>

        <div class="p-8">
            <?php if(!empty($error)): ?>
            <div class="mb-6 p-4 rounded-lg bg-red-50 text-red-700 border border-red-200 flex items-center gap-3 text-sm">
                <i class="bi bi-exclamation-circle-fill text-lg"></i>
                <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <form method="POST" onsubmit="return validateForm()">
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="bi bi-envelope"></i>
                        </div>
                        <input type="email" name="email" id="email" class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition" placeholder="admin@example.com" required>
                    </div>
                    <p class="text-xs text-red-600 mt-1 hidden" id="emailError"></p>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="bi bi-lock"></i>
                        </div>
                        <input type="password" name="password" id="password" class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition" placeholder="••••••••" required>
                    </div>
                    <p class="text-xs text-red-600 mt-1 hidden" id="passwordError"></p>
                </div>

                <button type="submit" name="login" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 rounded-lg transition shadow-lg shadow-indigo-200">
                    Sign In
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="../index.html" class="text-sm text-gray-500 hover:text-indigo-600 transition flex items-center justify-center gap-1">
                    <i class="bi bi-arrow-left"></i> Back to Home
                </a>
            </div>
        </div>
    </div>

<script>
    function validateForm() {
        let valid = true;
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const emailError = document.getElementById('emailError');
        const passwordError = document.getElementById('passwordError');

        // Reset errors
        emailError.classList.add('hidden');
        passwordError.classList.add('hidden');

        if (!email || !email.includes('@')) {
            emailError.textContent = "Please enter a valid email address.";
            emailError.classList.remove('hidden');
            valid = false;
        }

        if (!password || password.length < 6) {
            passwordError.textContent = "Password must be at least 6 characters.";
            passwordError.classList.remove('hidden');
            valid = false;
        }

        return valid;
    }
</script>

</body>
</html>
