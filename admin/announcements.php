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
    $stmt = mysqli_prepare($conn, "DELETE FROM notices WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    if (mysqli_stmt_execute($stmt)) {
        $message = "Notice deleted successfully!";
    } else {
        $error = "Failed to delete notice.";
    }
    mysqli_stmt_close($stmt);
}

// Handle Add Notice
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_notice'])) {
    $title = trim($_POST['title']);
    $msg = trim($_POST['message']);
    $target = $_POST['target_year'];
    $posted_by = "Admin (" . $_SESSION['admin_name'] . ")";

    if (!empty($title) && !empty($msg)) {
        $stmt = mysqli_prepare($conn, "INSERT INTO notices (title, message, target_year, posted_by) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssss", $title, $msg, $target, $posted_by);
        if (mysqli_stmt_execute($stmt)) {
            $message = "Notice posted successfully!";
        } else {
            $error = "Failed to post notice.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $error = "Title and Message are required.";
    }
}

// Fetch Notices
$result = mysqli_query($conn, "SELECT * FROM notices ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Notices | Admin Panel</title>
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
                    <a href="announcements.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-indigo-50 text-indigo-700 font-medium">
                        <i class="bi bi-megaphone"></i> Announcements
                    </a>
                </li>
                <li>
                    <a href="view_feedback.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
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
                        <h1 class="text-2xl font-bold text-gray-900">Manage Notices</h1>
                        <p class="text-gray-600 text-sm mt-1">Post and manage announcements for students.</p>
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

                <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                    
                    <!-- ADD NOTICE FORM -->
                    <div class="md:col-span-4">
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sticky top-6">
                            <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <div class="p-1.5 rounded bg-indigo-100 text-indigo-600"><i class="bi bi-plus-lg"></i></div>
                                Post New Notice
                            </h2>
                            <form method="POST">
                                <div class="mb-4">
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Title</label>
                                    <input type="text" name="title" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition" placeholder="Notice Title" required>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Message</label>
                                    <textarea name="message" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition" rows="4" placeholder="Type your message here..." required></textarea>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Target Audience</label>
                                    <select name="target_year" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition bg-white">
                                        <option value="All">All Students</option>
                                        <option value="FY">First Year (FY)</option>
                                        <option value="SY">Second Year (SY)</option>
                                        <option value="TY">Third Year (TY)</option>
                                    </select>
                                </div>
                                <button type="submit" name="add_notice" class="w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition shadow-sm flex items-center justify-center gap-2">
                                    <i class="bi bi-send"></i> Post Notice
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- NOTICE LIST -->
                    <div class="md:col-span-8">
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                            <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <div class="p-1.5 rounded bg-gray-100 text-gray-600"><i class="bi bi-list-ul"></i></div>
                                Active Notices
                            </h2>
                            
                            <?php if (mysqli_num_rows($result) > 0): ?>
                                <div class="space-y-4">
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                    <div class="p-4 rounded-xl border border-gray-200 hover:border-indigo-200 hover:bg-indigo-50/30 transition group">
                                        <div class="flex justify-between items-start mb-2">
                                            <h5 class="text-base font-bold text-gray-900 group-hover:text-indigo-700 transition"><?php echo htmlspecialchars($row['title']); ?></h5>
                                            <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full"><?php echo date('d M Y, h:i A', strtotime($row['created_at'])); ?></span>
                                        </div>
                                        <p class="text-sm text-gray-600 mb-3 whitespace-pre-line"><?php echo htmlspecialchars($row['message']); ?></p>
                                        <div class="flex justify-between items-center pt-3 border-t border-gray-100">
                                            <div class="flex items-center gap-3 text-xs text-gray-500">
                                                <span class="flex items-center gap-1"><i class="bi bi-people"></i> For: <strong class="text-gray-700"><?php echo $row['target_year']; ?></strong></span>
                                                <span class="flex items-center gap-1"><i class="bi bi-person"></i> By: <span class="text-gray-700"><?php echo htmlspecialchars($row['posted_by']); ?></span></span>
                                            </div>
                                            <a href="?delete=<?php echo $row['id']; ?>" class="text-red-500 hover:text-red-700 text-xs font-medium flex items-center gap-1" onclick="return confirm('Delete this notice?')">
                                                <i class="bi bi-trash"></i> Delete
                                            </a>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-8 text-gray-500">
                                    <i class="bi bi-inbox text-4xl mb-2 block"></i>
                                    No notices found.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                
                </div>
            </div>
            
        </main>
    </div>
</div>

</body>
</html>
