<?php
session_start();
require_once __DIR__ . '/../db.php';

$error = "";

if (isset($_POST['login'])) {
    
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt_time'] = time();
    }
    
    if (time() - $_SESSION['last_attempt_time'] > 900) {
        $_SESSION['login_attempts'] = 0;
    }
    
    if ($_SESSION['login_attempts'] >= 5) {
        $error = "Too many login attempts. Please try again after 15 minutes.";
    } else {
        
        $email = trim($_POST['email']);
        $pass  = trim($_POST['password']);
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format";
        } else if (strlen($pass) < 6) {
            $error = "Password must be at least 6 characters";
        } else {
            $stmt = mysqli_prepare($conn, "SELECT id, name, password, year FROM students WHERE email = ?");
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) == 1) {
                $row = mysqli_fetch_assoc($result);
                
                if (password_verify($pass, $row['password'])) {
                    session_regenerate_id(true);
                    $_SESSION['student_id'] = $row['id'];
                    $_SESSION['student_name'] = $row['name'];
                    $_SESSION['year'] = $row['year'];
                    $_SESSION['login_attempts'] = 0;
                    
                    header("Location: student_dashboard.php");
                    exit;
                } else {
                    $error = "Invalid password";
                    $_SESSION['login_attempts']++;
                    $_SESSION['last_attempt_time'] = time();
                }
            } else {
                $error = "Student not found";
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
    <title>Student Login | NEP Portal</title>
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
<body class="bg-gray-50 font-sans text-gray-900 h-screen flex items-center justify-center relative overflow-hidden">

    <!-- Background Decoration -->
    <div class="absolute inset-0 -z-10 overflow-hidden">
        <div class="absolute -top-[30%] -left-[10%] w-[70%] h-[70%] rounded-full bg-blue-200/30 blur-3xl"></div>
        <div class="absolute -bottom-[30%] -right-[10%] w-[70%] h-[70%] rounded-full bg-sky-200/30 blur-3xl"></div>
    </div>

    <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-xl border border-gray-100 relative z-10 m-4">
        
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-4 text-3xl shadow-sm">
                👨‍🎓
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Student Login</h1>
            <p class="text-gray-500 mt-2">Access your learning journey</p>
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

        <?php if (isset($_GET['timeout'])): ?>
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">⏳</div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700 font-medium">Session expired. Please login again.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                <input type="email" id="email" name="email" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition bg-gray-50 focus:bg-white" placeholder="student@example.com" required>
                <span class="text-red-500 text-xs mt-1 block" id="emailError"></span>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" id="password" name="password" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition bg-gray-50 focus:bg-white" placeholder="••••••••" required>
                <span class="text-red-500 text-xs mt-1 block" id="passwordError"></span>
            </div>

            <button type="submit" name="login" class="w-full py-3.5 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700 transition transform hover:-translate-y-0.5 shadow-lg shadow-blue-500/20">
                Sign In
            </button>
        </form>

        <div class="mt-6 text-center">
            <p class="text-gray-500 text-sm">Don't have an account? 
                <a href="student_signup.php" class="text-blue-600 font-semibold hover:text-blue-700 transition">Sign up</a>
            </p>
        </div>

        <div class="mt-4 text-center">
            <a href="../index.html" class="text-sm text-gray-400 hover:text-gray-600 font-medium transition flex items-center justify-center gap-1">
                <span>←</span> Back to Portal
            </a>
        </div>
    </div>

</body>
</html>
