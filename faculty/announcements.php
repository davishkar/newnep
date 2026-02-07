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

$message = "";
$error = "";

// Handle Delete (Only their own notices)
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $teacher_name_check = "Faculty (" . $_SESSION['teacher_name'] . ")";
    
    // Check ownership
    $checkStmt = mysqli_prepare($conn, "SELECT posted_by FROM notices WHERE id = ?");
    mysqli_stmt_bind_param($checkStmt, "i", $id);
    mysqli_stmt_execute($checkStmt);
    $checkResult = mysqli_stmt_get_result($checkStmt);
    $notice = mysqli_fetch_assoc($checkResult);
    
    if ($notice && $notice['posted_by'] === $teacher_name_check) {
        $stmt = mysqli_prepare($conn, "DELETE FROM notices WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            $message = "Notice deleted successfully!";
        } else {
            $error = "Failed to delete notice.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $error = "You can only delete your own notices.";
    }
    mysqli_stmt_close($checkStmt);
}

// Handle Add Notice
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_notice'])) {
    $title = trim($_POST['title']);
    $msg = trim($_POST['message']);
    $target = $_POST['target_year'];
    $posted_by = "Faculty (" . $_SESSION['teacher_name'] . ")";

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

// Fetch Notices (All notices visible, but delete button only for own)
$result = mysqli_query($conn, "SELECT * FROM notices ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Announcements | Faculty Portal</title>
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
                    <a href="announcements.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-green-50 text-green-700 font-medium">
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
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4 md:p-6">
            
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Class Announcements</h1>
                <p class="text-gray-500 text-sm">Post and manage notices for your students.</p>
            </div>

            <?php if (!empty($message)): ?>
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-r-lg flex justify-between items-center transition-all duration-300">
                <div class="flex">
                    <div class="flex-shrink-0 text-green-500">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700 font-medium"><?php echo $message; ?></p>
                    </div>
                </div>
                <button onclick="this.parentElement.remove()" class="text-green-500 hover:text-green-700">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg flex justify-between items-center transition-all duration-300">
                <div class="flex">
                    <div class="flex-shrink-0 text-red-500">
                        <i class="bi bi-exclamation-circle-fill"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700 font-medium"><?php echo $error; ?></p>
                    </div>
                </div>
                <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                <!-- Add Notice Form -->
                <div class="md:col-span-4">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-6">
                        <h3 class="font-bold text-gray-800 mb-6 flex items-center gap-2">
                            <i class="bi bi-plus-circle text-green-600"></i> Post New
                        </h3>
                        
                        <form method="POST" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                <input type="text" name="title" class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition bg-white" placeholder="e.g. Exam Schedule" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                                <textarea name="message" class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition bg-white min-h-[120px]" placeholder="Type your announcement here..." required></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Target Audience</label>
                                <select name="target_year" class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition bg-white">
                                    <option value="All">All Students</option>
                                    <option value="FY">First Year (FY)</option>
                                    <option value="SY">Second Year (SY)</option>
                                    <option value="TY">Third Year (TY)</option>
                                </select>
                            </div>

                            <button type="submit" name="add_notice" class="w-full py-2.5 rounded-xl bg-green-600 text-white font-medium hover:bg-green-700 transition shadow-lg shadow-green-500/20 flex items-center justify-center gap-2">
                                <i class="bi bi-send"></i> Post Announcement
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Notices List -->
                <div class="md:col-span-8">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                                <i class="bi bi-list-ul text-green-600"></i> Recent Announcements
                            </h3>
                        </div>

                        <div class="divide-y divide-gray-100 p-6 space-y-4">
                            <?php if (mysqli_num_rows($result) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <div class="bg-gray-50 rounded-xl p-5 border border-gray-200 hover:shadow-md transition group relative">
                                    <div class="flex justify-between items-start mb-2">
                                        <h5 class="text-lg font-bold text-gray-900"><?php echo htmlspecialchars($row['title']); ?></h5>
                                        <span class="text-xs text-gray-500 bg-white px-2 py-1 rounded border border-gray-200">
                                            <?php echo date('d M Y, h:i A', strtotime($row['created_at'])); ?>
                                        </span>
                                    </div>
                                    
                                    <p class="text-gray-600 mb-4 whitespace-pre-wrap leading-relaxed"><?php echo htmlspecialchars($row['message']); ?></p>
                                    
                                    <div class="flex items-center justify-between pt-3 border-t border-gray-200 text-sm">
                                        <div class="flex items-center gap-4 text-gray-500">
                                            <span class="flex items-center gap-1">
                                                <i class="bi bi-people"></i> <?php echo $row['target_year']; ?>
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <i class="bi bi-person"></i> <?php echo htmlspecialchars($row['posted_by']); ?>
                                            </span>
                                        </div>

                                        <?php if ($row['posted_by'] === "Faculty (" . $_SESSION['teacher_name'] . ")"): ?>
                                        <a href="?delete=<?php echo $row['id']; ?>" class="text-red-500 hover:text-red-700 font-medium text-xs flex items-center gap-1 transition" onclick="return confirm('Delete this announcement?')">
                                            <i class="bi bi-trash"></i> Delete
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="text-center py-8 text-gray-500">
                                    <i class="bi bi-chat-square-quote text-4xl mb-3 block text-gray-300"></i>
                                    No announcements have been posted yet.
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
