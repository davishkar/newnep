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

// Ensure year is set in session
if (!isset($_SESSION['year'])) {
    $stmt = mysqli_prepare($conn, "SELECT year FROM students WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $_SESSION['student_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($result)) {
        $_SESSION['year'] = $row['year'];
    }
    mysqli_stmt_close($stmt);
}
$year = $_SESSION['year'] ?? '';

// Fetch Subjects depending on year
$subjects = [];
$stmt = mysqli_prepare($conn, "SELECT * FROM subjects WHERE year = ? ORDER BY subject_type, subject_name");
mysqli_stmt_bind_param($stmt, "s", $year);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $subjects[] = $row;
}
mysqli_stmt_close($stmt);

// Group subjects by type
$grouped_subjects = [];
foreach ($subjects as $sub) {
    $grouped_subjects[$sub['subject_type']][] = $sub;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Subjects | Student Portal</title>
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
                    <a href="my_subjects.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-indigo-50 text-indigo-700 font-medium">
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
            
            <div class="max-w-7xl mx-auto">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">My Subjects</h1>
                        <p class="text-gray-500 text-sm">Curriculum for Year: <span class="font-medium text-indigo-600"><?php echo $year; ?></span></p>
                    </div>
                </div>

                <?php if (empty($grouped_subjects)): ?>
                    <div class="bg-white p-12 rounded-2xl shadow-sm border border-gray-100 text-center">
                        <div class="w-16 h-16 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-4 text-indigo-500 text-3xl">
                            <i class="bi bi-book"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1">No Subjects Found</h3>
                        <p class="text-gray-500 text-sm">There are no subjects listed for your year yet.</p>
                        <p class="text-gray-400 text-xs mt-2">Contact your faculty if this is an error.</p>
                    </div>
                <?php else: ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($grouped_subjects as $type => $subs): ?>
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col h-full hover:shadow-md transition duration-200">
                            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <i class="bi bi-bookmarks-fill text-indigo-500"></i>
                                    <h3 class="font-bold text-gray-900"><?php echo htmlspecialchars($type); ?></h3>
                                </div>
                                <span class="bg-white px-2 py-1 rounded-md border border-gray-200 text-xs font-semibold text-gray-600 shadow-sm">
                                    <?php echo count($subs); ?>
                                </span>
                            </div>
                            <div class="p-6 flex-1">
                                <ul class="space-y-3">
                                <?php foreach ($subs as $s): ?>
                                    <li class="flex items-start justify-between gap-3 p-3 rounded-xl bg-gray-50 border border-gray-100 hover:border-indigo-100 hover:bg-indigo-50/30 transition">
                                        <div class="flex items-start gap-2">
                                            <div class="mt-1 text-indigo-400 text-xs">
                                                <i class="bi bi-circle-fill"></i>
                                            </div>
                                            <span class="text-sm font-medium text-gray-800 leading-snug">
                                                <?php echo htmlspecialchars($s['subject_name']); ?>
                                            </span>
                                        </div>
                                        <span class="flex-shrink-0 bg-indigo-100 text-indigo-700 text-xs font-bold px-2 py-1 rounded-md">
                                            <?php echo $s['credits']; ?> Cr
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>

                <?php endif; ?>

            </div>
        </main>
    </div>
</div>

</body>
</html>
