<?php
session_start();
require_once __DIR__ . '/../db.php';

// Session timeout check (30 minutes)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header("Location: faculty_login.php?timeout=1");
    exit;
}
$_SESSION['last_activity'] = time();

/* LOGIN CHECK */
if (!isset($_SESSION['teacher_id'])) {
    header("Location: faculty_login.php");
    exit;
}

$teacher_id = intval($_SESSION['teacher_id']);
$message = "";
$error = "";

/* ADD STUDENT */
if (isset($_POST['add_student'])) {
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $year  = $_POST['year'];
    
    // Validate inputs
    if (empty($name) || !preg_match("/^[a-zA-Z\s]+$/", $name)) {
        $error = "Name should contain only letters and spaces";
    }
    elseif (strlen($name) < 3 || strlen($name) > 50) {
        $error = "Name must be between 3 and 50 characters";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    }
    elseif (!in_array($year, ['FY', 'SY', 'TY'])) {
        $error = "Invalid year selected";
    }
    else {
        // Check for duplicate email
        $checkStmt = mysqli_prepare($conn, "SELECT id FROM students WHERE email = ?");
        mysqli_stmt_bind_param($checkStmt, "s", $email);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);
        
        if (mysqli_num_rows($checkResult) > 0) {
            $error = "Email already exists";
        } else {
            // Generate default password
            $defaultPassword = password_hash('student123', PASSWORD_DEFAULT);
            
            $stmt = mysqli_prepare($conn, "INSERT INTO students (name, email, year, password) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $year, $defaultPassword);
            
            if (mysqli_stmt_execute($stmt)) {
                $message = "Student added successfully! Default password: student123";
            } else {
                $error = "Failed to add student";
            }
            
            mysqli_stmt_close($stmt);
        }
        
        mysqli_stmt_close($checkStmt);
    }
}

/* DELETE STUDENT */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    $stmt = mysqli_prepare($conn, "DELETE FROM students WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $message = "Student deleted successfully";
    } else {
        $error = "Failed to delete student";
    }
    
    mysqli_stmt_close($stmt);
}

/* FETCH STUDENTS YEAR WISE using prepared statements */
$fyYear = 'FY';
$stmt = mysqli_prepare($conn, "SELECT * FROM students WHERE year = ? ORDER BY name");
mysqli_stmt_bind_param($stmt, "s", $fyYear);
mysqli_stmt_execute($stmt);
$fy = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

$syYear = 'SY';
$stmt = mysqli_prepare($conn, "SELECT * FROM students WHERE year = ? ORDER BY name");
mysqli_stmt_bind_param($stmt, "s", $syYear);
mysqli_stmt_execute($stmt);
$sy = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

$tyYear = 'TY';
$stmt = mysqli_prepare($conn, "SELECT * FROM students WHERE year = ? ORDER BY name");
mysqli_stmt_bind_param($stmt, "s", $tyYear);
mysqli_stmt_execute($stmt);
$ty = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard | NEP Portal</title>
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
                    <a href="teacher_dashboard.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-green-50 text-green-700 font-medium">
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
                    <a href="subject_change_approvals.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                        <i class="bi bi-check2-square"></i> Subject Requests
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
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
            
            <div class="mb-8 flex justify-between items-end">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Faculty Dashboard</h1>
                    <p class="text-gray-500">Manage your students and academic activities.</p>
                </div>
                <div class="hidden md:block">
                     <span class="text-sm text-gray-500 bg-white px-3 py-1 rounded-full border border-gray-200">
                        Today: <?php echo date('d M, Y'); ?>
                     </span>
                </div>
            </div>

            <!-- Alerts -->
            <?php if (!empty($message)): ?>
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-r-lg flex justify-between items-center">
                <div class="flex">
                    <div class="flex-shrink-0 text-green-500">✓</div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700 font-medium"><?php echo $message; ?></p>
                    </div>
                </div>
                <button onclick="this.parentElement.remove()" class="text-green-500 hover:text-green-700">×</button>
            </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg flex justify-between items-center">
                <div class="flex">
                    <div class="flex-shrink-0 text-red-500">⚠️</div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700 font-medium"><?php echo $error; ?></p>
                    </div>
                </div>
                <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">×</button>
            </div>
            <?php endif; ?>

            <!-- Quick Actions Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                 <a href="mark_attendance.php" class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md hover:border-green-500 transition group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-lg bg-green-50 text-green-600 group-hover:bg-green-600 group-hover:text-white transition">
                            <i class="bi bi-calendar-check text-xl"></i>
                        </div>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Mark Attendance</h3>
                    <p class="text-gray-500 text-sm">Daily logs</p>
                </a>

                <a href="view_attendance.php" class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md hover:border-blue-500 transition group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-lg bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition">
                            <i class="bi bi-graph-up text-xl"></i>
                        </div>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">View Reports</h3>
                    <p class="text-gray-500 text-sm">Attendance stats</p>
                </a>

                <a href="semester_mark_entry.php" class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md hover:border-yellow-500 transition group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-lg bg-yellow-50 text-yellow-600 group-hover:bg-yellow-600 group-hover:text-white transition">
                            <i class="bi bi-pencil-square text-xl"></i>
                        </div>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Enter Marks</h3>
                    <p class="text-gray-500 text-sm">Update results</p>
                </a>

                <a href="view_marks.php" class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md hover:border-purple-500 transition group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-lg bg-purple-50 text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition">
                            <i class="bi bi-clipboard-data text-xl"></i>
                        </div>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">View Results</h3>
                    <p class="text-gray-500 text-sm">Student performance</p>
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Left Column: Student Lists -->
                <div class="lg:col-span-2 space-y-8">
                    
                    <!-- Search & Filter -->
                    <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row gap-4">
                        <div class="flex-1 relative">
                            <i class="bi bi-search absolute left-4 top-3.5 text-gray-400"></i>
                            <input type="text" id="searchInput" class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition" placeholder="Search students...">
                        </div>
                        <div class="min-w-[150px]">
                            <select id="yearFilter" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition bg-white">
                                <option value="">All Years</option>
                                <option value="FY">First Year (FY)</option>
                                <option value="SY">Second Year (SY)</option>
                                <option value="TY">Third Year (TY)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Student Tables -->
                    <?php
                    function studentTable($result, $title, $badgeColor){
                        echo "
                        <div class='bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden'>
                            <div class='px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50'>
                                <h3 class='font-bold text-gray-800'>$title</h3>
                                <span class='px-3 py-1 rounded-full text-xs font-semibold $badgeColor'>Active</span>
                            </div>
                            <div class='p-0 overflow-x-auto'>
                                <table class='w-full text-left'>
                                    <thead class='bg-gray-50 text-gray-500 font-medium border-b border-gray-100'>
                                        <tr>
                                            <th class='px-6 py-3'>Name</th>
                                            <th class='px-6 py-3'>Email</th>
                                            <th class='px-6 py-3 text-right'>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class='divide-y divide-gray-100'>";
                        if (mysqli_num_rows($result) > 0) {
                            while ($s = mysqli_fetch_assoc($result)) {
                                $yearStr = ($title[0] == '1' ? 'FY' : ($title[0] == '2' ? 'SY' : 'TY')); // Helper for JS filter
                                echo "
                                <tr class='student-row hover:bg-gray-50 transition' data-name='{$s['name']}' data-email='{$s['email']}' data-year='$yearStr'>
                                    <td class='px-6 py-4 font-medium text-gray-900'>
                                        <a href='student_details.php?id={$s['id']}' class='hover:text-green-600 transition'>{$s['name']}</a>
                                    </td>
                                    <td class='px-6 py-4 text-gray-500'>{$s['email']}</td>
                                    <td class='px-6 py-4 text-right space-x-2'>
                                        <a href='edit_student.php?id={$s['id']}' class='text-yellow-500 hover:text-yellow-600 transition' title='Edit'><i class='bi bi-pencil'></i></a>
                                        <a href='student_details.php?id={$s['id']}' class='text-blue-500 hover:text-blue-600 transition' title='View'><i class='bi bi-eye'></i></a>
                                        <a href='?delete={$s['id']}' class='text-red-500 hover:text-red-600 transition' onclick=\"return confirm('Delete student? This cannot be undone.')\" title='Delete'><i class='bi bi-trash'></i></a>
                                    </td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3' class='px-6 py-8 text-center text-gray-500'>No students enrolled in this year yet.</td></tr>";
                        }
                        echo "</tbody></table></div></div>";
                    }

                    studentTable($fy, "1st Year Students (FY)", "bg-blue-100 text-blue-700");
                    studentTable($sy, "2nd Year Students (SY)", "bg-purple-100 text-purple-700");
                    studentTable($ty, "3rd Year Students (TY)", "bg-orange-100 text-orange-700");
                    ?>
                </div>

                <!-- Right Column: Add Student Form -->
                <div>
                     <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2 rounded-lg bg-green-50 text-green-600">
                                <i class="bi bi-person-plus-fill text-lg"></i>
                            </div>
                            <h3 class="font-bold text-gray-900">Add New Student</h3>
                        </div>
                        
                        <form method="POST" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                <input name="name" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition" placeholder="John Doe" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                <input name="email" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition" placeholder="student@example.com" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Academic Year</label>
                                <select name="year" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition bg-white" required>
                                    <option value="">Select Year</option>
                                    <option value="FY">First Year (FY)</option>
                                    <option value="SY">Second Year (SY)</option>
                                    <option value="TY">Third Year (TY)</option>
                                </select>
                            </div>
                             <button name="add_student" class="w-full py-3 rounded-xl bg-green-600 text-white font-bold hover:bg-green-700 transition shadow-lg shadow-green-500/20 mt-2">
                                Add Student
                            </button>
                        </form>
                     </div>
                </div>

            </div>

        </main>
    </div>
</div>

<script>
// Search and filter functionality
const searchInput = document.getElementById('searchInput');
const yearFilter = document.getElementById('yearFilter');
const studentRows = document.querySelectorAll('.student-row');

function filterStudents() {
    const searchTerm = searchInput.value.toLowerCase();
    const selectedYear = yearFilter.value; // FY, SY, TY
    
    studentRows.forEach(row => {
        const name = row.dataset.name.toLowerCase();
        const email = row.dataset.email.toLowerCase();
        const year = row.dataset.year; // Should be FY, SY, TY
        
        const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
        const matchesYear = !selectedYear || year === selectedYear;
        
        if (matchesSearch && matchesYear) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

searchInput.addEventListener('input', filterStudents);
yearFilter.addEventListener('change', filterStudents);
</script>

</body>
</html>
