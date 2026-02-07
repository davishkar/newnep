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

$id = intval($_GET['id']);
$message = "";
$error = "";

// Fetch student details
$stmt = mysqli_prepare($conn, "SELECT * FROM students WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$student) {
    header("Location: teacher_dashboard.php");
    exit;
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $year = $_POST['year'];
    $mobile = trim($_POST['mobile']);
    $abc_id = trim($_POST['abc_id']);
    $password = $_POST['password'];
    
    // Handle Subjects (JSON)
    $subject_keys = $_POST['subject_keys'] ?? [];
    $subject_values = $_POST['subject_values'] ?? [];
    $subjects_array = [];
    
    for ($i = 0; $i < count($subject_keys); $i++) {
        if (!empty(trim($subject_keys[$i]))) {
            $subjects_array[trim($subject_keys[$i])] = trim($subject_values[$i]);
        }
    }
    
    $subjects_json = !empty($subjects_array) ? json_encode($subjects_array) : NULL;

    if (empty($name) || empty($email)) {
        $error = "Name and Email are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check duplicate email (excluding current student)
        $checkStmt = mysqli_prepare($conn, "SELECT id FROM students WHERE email = ? AND id != ?");
        mysqli_stmt_bind_param($checkStmt, "si", $email, $id);
        mysqli_stmt_execute($checkStmt);
        if (mysqli_num_rows(mysqli_stmt_get_result($checkStmt)) > 0) {
            $error = "Email already exists for another student.";
        } else {
            
            // Prepare Query
            if (!empty($password)) {
                $pass_hash = password_hash($password, PASSWORD_DEFAULT);
                $updateStmt = mysqli_prepare($conn, "UPDATE students SET name = ?, email = ?, year = ?, mobile = ?, abc_id = ?, password = ?, subjects = ? WHERE id = ?");
                mysqli_stmt_bind_param($updateStmt, "sssssssi", $name, $email, $year, $mobile, $abc_id, $pass_hash, $subjects_json, $id);
            } else {
                $updateStmt = mysqli_prepare($conn, "UPDATE students SET name = ?, email = ?, year = ?, mobile = ?, abc_id = ?, subjects = ? WHERE id = ?");
                mysqli_stmt_bind_param($updateStmt, "ssssssi", $name, $email, $year, $mobile, $abc_id, $subjects_json, $id);
            }
            
            if (mysqli_stmt_execute($updateStmt)) {
                $message = "Student updated successfully!";
                // Refresh data
                $student['name'] = $name;
                $student['email'] = $email;
                $student['year'] = $year;
                $student['mobile'] = $mobile;
                $student['abc_id'] = $abc_id;
                $student['subjects'] = $subjects_json;
            } else {
                $error = "Failed to update student: " . mysqli_error($conn);
            }
            mysqli_stmt_close($updateStmt);
        }
        mysqli_stmt_close($checkStmt);
    }
}

// Parse existing subjects
$current_subjects = json_decode($student['subjects'] ?? '{}', true);
if (!is_array($current_subjects)) $current_subjects = [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student | Faculty Portal</title>
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
                 <!-- Other links... -->
                 <li>
                    <a href="teacher_logout.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-red-600 hover:bg-red-50 transition">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
            
            <div class="max-w-4xl mx-auto">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Edit Student</h1>
                        <p class="text-gray-500 text-sm">Update student information, password, and subjects.</p>
                    </div>
                    <a href="teacher_dashboard.php" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Left Column: Basic Info -->
                    <div class="lg:col-span-2 space-y-6">
                        <form method="POST" id="editForm" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="p-6 border-b border-gray-100 bg-gray-50">
                                <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                    <i class="bi bi-person-lines-fill text-green-500"></i> Basic Information
                                </h2>
                            </div>
                            
                            <div class="p-6 space-y-4">
                                <?php if ($message): ?>
                                    <div class="bg-green-50 text-green-700 p-3 rounded-lg text-sm border-l-4 border-green-500"><?php echo $message; ?></div>
                                <?php endif; ?>
                                <?php if ($error): ?>
                                    <div class="bg-red-50 text-red-700 p-3 rounded-lg text-sm border-l-4 border-red-500"><?php echo $error; ?></div>
                                <?php endif; ?>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                        <input type="text" name="name" value="<?php echo htmlspecialchars($student['name']); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-500 outline-none" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                        <input type="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-500 outline-none" required>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                                        <select name="year" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-500 outline-none">
                                            <option value="FY" <?php echo $student['year'] == 'FY' ? 'selected' : ''; ?>>FY</option>
                                            <option value="SY" <?php echo $student['year'] == 'SY' ? 'selected' : ''; ?>>SY</option>
                                            <option value="TY" <?php echo $student['year'] == 'TY' ? 'selected' : ''; ?>>TY</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Mobile</label>
                                        <input type="text" name="mobile" value="<?php echo htmlspecialchars($student['mobile'] ?? ''); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-500 outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">ABC ID</label>
                                        <input type="text" name="abc_id" value="<?php echo htmlspecialchars($student['abc_id'] ?? ''); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-500 outline-none">
                                    </div>
                                </div>
                                
                                <div class="pt-4 border-t border-gray-100">
                                    <h3 class="text-sm font-bold text-gray-900 mb-3">Login Security</h3>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">New Password <span class="text-gray-400 font-normal">(Leave blank to keep current)</span></label>
                                        <input type="password" name="password" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-500 outline-none" placeholder="••••••••">
                                    </div>
                                </div>
                            </div>
                        
                    </div>

                    <!-- Right Column: Subjects -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden h-full flex flex-col">
                            <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                                <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                    <i class="bi bi-book text-green-500"></i> Subjects
                                </h2>
                                <button type="button" onclick="addSubjectField()" class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded hover:bg-green-200 transition">
                                    <i class="bi bi-plus"></i> Add
                                </button>
                            </div>
                            
                            <div class="p-6 flex-1 overflow-y-auto">
                                <p class="text-xs text-gray-500 mb-4">Define subject keys (e.g., 'Major', 'OE') and their names.</p>
                                
                                <div id="subjectsContainer" class="space-y-3">
                                    <?php foreach ($current_subjects as $key => $val): ?>
                                    <div class="flex gap-2">
                                        <input type="text" name="subject_keys[]" value="<?php echo htmlspecialchars($key); ?>" class="w-1/3 px-2 py-1.5 text-sm border rounded focus:ring-2 focus:ring-green-500 outline-none" placeholder="Key">
                                        <input type="text" name="subject_values[]" value="<?php echo htmlspecialchars($val); ?>" class="w-2/3 px-2 py-1.5 text-sm border rounded focus:ring-2 focus:ring-green-500 outline-none" placeholder="Subject Name">
                                        <button type="button" onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-600"><i class="bi bi-trash"></i></button>
                                    </div>
                                    <?php endforeach; ?>
                                    
                                    <?php if (empty($current_subjects)): ?>
                                    <div class="flex gap-2">
                                        <input type="text" name="subject_keys[]" class="w-1/3 px-2 py-1.5 text-sm border rounded focus:ring-2 focus:ring-green-500 outline-none" placeholder="Key (e.g. Major)">
                                        <input type="text" name="subject_values[]" class="w-2/3 px-2 py-1.5 text-sm border rounded focus:ring-2 focus:ring-green-500 outline-none" placeholder="Subject Name">
                                         <button type="button" onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-600"><i class="bi bi-trash"></i></button>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                             <div class="p-6 border-t border-gray-100 bg-gray-50">
                                <button type="submit" form="editForm" class="w-full py-2.5 rounded-xl bg-green-600 text-white font-bold hover:bg-green-700 transition shadow-lg shadow-green-500/20">
                                    Save All Changes
                                </button>
                            </div>
                        </div>
                        </form>
                    </div>

                </div>
            </div>
            
        </main>
    </div>
</div>

<script>
function addSubjectField() {
    const container = document.getElementById('subjectsContainer');
    const div = document.createElement('div');
    div.className = 'flex gap-2';
    div.innerHTML = `
        <input type="text" name="subject_keys[]" class="w-1/3 px-2 py-1.5 text-sm border rounded focus:ring-2 focus:ring-green-500 outline-none" placeholder="Key">
        <input type="text" name="subject_values[]" class="w-2/3 px-2 py-1.5 text-sm border rounded focus:ring-2 focus:ring-green-500 outline-none" placeholder="Subject Name">
        <button type="button" onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-600"><i class="bi bi-trash"></i></button>
    `;
    container.appendChild(div);
}
</script>

</body>
</html>
