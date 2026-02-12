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

$teacher_id = intval($_SESSION['teacher_id']);
$success = "";
$error = "";

// Handle approval/rejection
if (isset($_POST['action']) && isset($_POST['request_id'])) {
    $request_id = intval($_POST['request_id']);
    $action = $_POST['action']; // 'approve' or 'reject'
    
    if ($action === 'approve') {
        // Fetch request details
        $stmt = mysqli_prepare($conn, "SELECT student_id, requested_subjects FROM subject_change_requests WHERE id = ? AND status = 'Pending'");
        mysqli_stmt_bind_param($stmt, "i", $request_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $request = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        if ($request) {
            // Update student's subjects
            $updateStmt = mysqli_prepare($conn, "UPDATE students SET subjects = ? WHERE id = ?");
            mysqli_stmt_bind_param($updateStmt, "si", $request['requested_subjects'], $request['student_id']);
            mysqli_stmt_execute($updateStmt);
            mysqli_stmt_close($updateStmt);
            
            // Update request status
            $statusStmt = mysqli_prepare($conn, "UPDATE subject_change_requests SET status = 'Approved', approved_by = ?, approver_type = 'Teacher', updated_at = NOW() WHERE id = ?");
            mysqli_stmt_bind_param($statusStmt, "ii", $teacher_id, $request_id);
            mysqli_stmt_execute($statusStmt);
            mysqli_stmt_close($statusStmt);
            
            $success = "Request approved successfully! Student's subjects have been updated.";
        } else {
            $error = "Request not found or already processed.";
        }
    } else if ($action === 'reject') {
        $statusStmt = mysqli_prepare($conn, "UPDATE subject_change_requests SET status = 'Rejected', approved_by = ?, approver_type = 'Teacher', updated_at = NOW() WHERE id = ? AND status = 'Pending'");
        mysqli_stmt_bind_param($statusStmt, "ii", $teacher_id, $request_id);
        mysqli_stmt_execute($statusStmt);
        
        if (mysqli_stmt_affected_rows($statusStmt) > 0) {
            $success = "Request rejected successfully.";
        } else {
            $error = "Request not found or already processed.";
        }
        
        mysqli_stmt_close($statusStmt);
    }
}

// Fetch all pending requests
$pendingStmt = mysqli_query($conn, "
    SELECT scr.*, s.name as student_name, s.email as student_email, s.year as student_year
    FROM subject_change_requests scr
    JOIN students s ON scr.student_id = s.id
    WHERE scr.status = 'Pending'
    ORDER BY scr.created_at DESC
");

// Fetch processed requests
$processedStmt = mysqli_query($conn, "
    SELECT scr.*, s.name as student_name, s.email as student_email, s.year as student_year
    FROM subject_change_requests scr
    JOIN students s ON scr.student_id = s.id
    WHERE scr.status IN ('Approved', 'Rejected')
    ORDER BY scr.updated_at DESC
    LIMIT 20
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject Change Approvals | Faculty Portal</title>
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
                    <a href="subject_change_approvals.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-green-50 text-green-700 font-medium">
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
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4 md:p-6">
            
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Subject Change Requests</h1>
                    <p class="text-gray-500 text-sm">Review and approve student subject change requests.</p>
                </div>
                <a href="teacher_dashboard.php" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 text-sm font-medium transition">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
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

            <!-- Pending Requests -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="font-bold text-gray-800 flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full bg-yellow-100 text-yellow-700 text-xs font-bold uppercase tracking-wide">Pending</span>
                        Requests Awaiting Approval
                    </h3>
                </div>
                
                <div class="p-6">
                    <?php if (mysqli_num_rows($pendingStmt) == 0): ?>
                        <div class="text-center py-8 text-gray-500">
                            <i class="bi bi-inbox text-4xl mb-3 block text-gray-300"></i>
                            No pending requests at the moment.
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php while ($req = mysqli_fetch_assoc($pendingStmt)): ?>
                                <div class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition">
                                    <div class="flex justify-between items-start mb-4">
                                        <div>
                                            <h4 class="font-bold text-gray-900 text-lg"><?php echo htmlspecialchars($req['student_name']); ?></h4>
                                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($req['student_email']); ?> | Year: <?php echo $req['student_year']; ?></p>
                                            <p class="text-xs text-gray-400 mt-1">Submitted: <?php echo date('d M Y, h:i A', strtotime($req['created_at'])); ?></p>
                                        </div>
                                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700">
                                            Pending
                                        </span>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-700 mb-2">Current Subjects:</p>
                                            <div class="bg-gray-50 rounded-lg p-3 text-sm text-gray-600">
                                                <?php
                                                $current = json_decode($req['current_subjects'], true);
                                                if ($current) {
                                                    foreach ($current as $k => $v) {
                                                        echo "<div><strong>$k:</strong> $v</div>";
                                                    }
                                                } else {
                                                    echo "No subjects assigned";
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-700 mb-2">Requested Subjects:</p>
                                            <div class="bg-green-50 rounded-lg p-3 text-sm text-gray-700 whitespace-pre-line">
                                                <?php echo htmlspecialchars($req['requested_subjects']); ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <p class="text-sm font-semibold text-gray-700 mb-2">Reason:</p>
                                        <p class="text-sm text-gray-600 bg-gray-50 rounded-lg p-3"><?php echo htmlspecialchars($req['reason']); ?></p>
                                    </div>
                                    
                                    <?php if ($req['proof_file']): ?>
                                        <div class="mb-4">
                                            <a href="../<?php echo htmlspecialchars($req['proof_file']); ?>" target="_blank" class="text-green-600 hover:text-green-800 text-sm font-medium">
                                                <i class="bi bi-paperclip"></i> View Uploaded Proof
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="flex gap-3 pt-4 border-t border-gray-200">
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" onclick="return confirm('Are you sure you want to approve this request? The student\'s subjects will be updated.')" class="px-4 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition">
                                                <i class="bi bi-check-circle"></i> Approve
                                            </button>
                                        </form>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" onclick="return confirm('Are you sure you want to reject this request?')" class="px-4 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition">
                                                <i class="bi bi-x-circle"></i> Reject
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Processed Requests -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="font-bold text-gray-800 flex items-center gap-2">
                        <i class="bi bi-clock-history text-gray-600"></i> Recent Processed Requests
                    </h3>
                </div>
                
                <div class="p-6">
                    <?php if (mysqli_num_rows($processedStmt) == 0): ?>
                        <div class="text-center py-8 text-gray-500">
                            <i class="bi bi-inbox text-4xl mb-3 block text-gray-300"></i>
                            No processed requests yet.
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-100">
                                    <tr>
                                        <th class="px-6 py-3">Student</th>
                                        <th class="px-6 py-3">Year</th>
                                        <th class="px-6 py-3">Requested</th>
                                        <th class="px-6 py-3">Status</th>
                                        <th class="px-6 py-3">Processed By</th>
                                        <th class="px-6 py-3">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php while ($req = mysqli_fetch_assoc($processedStmt)): ?>
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-6 py-4 font-medium text-gray-900"><?php echo htmlspecialchars($req['student_name']); ?></td>
                                            <td class="px-6 py-4 text-gray-600"><?php echo $req['student_year']; ?></td>
                                            <td class="px-6 py-4 text-gray-600 text-xs"><?php echo date('d M Y', strtotime($req['created_at'])); ?></td>
                                            <td class="px-6 py-4">
                                                <?php
                                                $statusColor = $req['status'] === 'Approved' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
                                                ?>
                                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold <?php echo $statusColor; ?>">
                                                    <?php echo $req['status']; ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-gray-600 text-xs"><?php echo $req['approver_type']; ?></td>
                                            <td class="px-6 py-4 text-gray-600 text-xs"><?php echo date('d M Y', strtotime($req['updated_at'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </main>
    </div>
</div>

</body>
</html>
