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

$message = "";
$error = "";

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = mysqli_prepare($conn, "DELETE FROM feedback WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    if (mysqli_stmt_execute($stmt)) {
        $message = "Feedback deleted successfully!";
    } else {
        $error = "Failed to delete feedback.";
    }
    mysqli_stmt_close($stmt);
}

// Fetch Feedback
$result = mysqli_query($conn, "SELECT * FROM feedback ORDER BY submitted_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Feedback | Admin Panel</title>
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
                    <a href="view_feedback.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-indigo-50 text-indigo-700 font-medium">
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
                    <a href="change_password.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
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
            
            <div class="max-w-7xl mx-auto">
                
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">User Feedback</h1>
                        <p class="text-gray-600 text-sm mt-1">View messages and feedback submitted by users.</p>
                    </div>
                    <a href="admin_dashboard.php" class="hidden sm:inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        <i class="bi bi-arrow-left"></i> Back to Dashboard
                    </a>
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

                <!-- Feedback List -->
                <div class="grid grid-cols-1 gap-6">
                    <?php 
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            // Format date: "07 Feb, 2026 at 10:30 AM"
                            $date = date("d M, Y \a\\t h:i A", strtotime($row['submitted_at']));
                            $initial = strtoupper(substr($row['name'], 0, 1));
                    ?>
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col md:flex-row gap-6 hover:shadow-md transition">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 text-xl font-bold">
                                <?php echo $initial; ?>
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-2">
                                <h3 class="text-lg font-bold text-gray-900"><?php echo htmlspecialchars($row['name']); ?></h3>
                                <span class="text-xs text-gray-500 bg-gray-50 px-2 py-1 rounded-full border border-gray-100">
                                    <i class="bi bi-clock"></i> <?php echo $date; ?>
                                </span>
                            </div>
                            <p class="text-sm text-indigo-600 mb-3 font-medium"><?php echo htmlspecialchars($row['email']); ?></p>
                            <div class="bg-gray-50 p-4 rounded-xl text-gray-700 text-sm leading-relaxed border border-gray-100">
                                <?php echo nl2br(htmlspecialchars($row['message'])); ?>
                            </div>
                        </div>
                        <div class="flex-shrink-0 flex items-start">
                            <a href="?delete=<?php echo $row['id']; ?>" class="text-red-400 hover:text-red-600 p-2 rounded-lg hover:bg-red-50 transition" onclick="return confirm('Delete this feedback?')" title="Delete">
                                <i class="bi bi-trash text-lg"></i>
                            </a>
                        </div>
                    </div>
                    <?php 
                        } 
                    } else {
                    ?>
                    <div class="text-center py-12 bg-white rounded-2xl border border-gray-200 border-dashed">
                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                            <i class="bi bi-chat-square-dots text-3xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900">No Feedback Yet</h3>
                        <p class="text-gray-500 text-sm mt-1">Messages submitted by users will appear here.</p>
                    </div>
                    <?php } ?>
                </div>

            </div>
            
        </main>
    </div>
</div>

</body>
</html>
