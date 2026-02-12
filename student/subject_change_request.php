<?php
session_start();
require_once __DIR__ . '/../db.php';

// Session timeout check (30 minutes)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header("Location: student_login.php?timeout=1");
    exit;
}
$_SESSION['last_activity'] = time();

// Check if logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit;
}

$student_id = intval($_SESSION['student_id']);
$student_name = $_SESSION['student_name'];

// Fetch student data
$stmt = mysqli_prepare($conn, "SELECT * FROM students WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$student) {
    session_destroy();
    header("Location: student_login.php");
    exit;
}

$current_subjects = json_decode($student['subjects'], true) ?? [];

$error = "";
$success = "";

// Handle form submission
if (isset($_POST['submit_request'])) {
    $requested_subjects = $_POST['requested_subjects'] ?? '';
    $reason = trim($_POST['reason']);
    $proof_file = null;
    
    // Validate inputs
    if (empty($requested_subjects)) {
        $error = "Please enter requested subjects";
    } else if (empty($reason) || strlen($reason) < 10) {
        $error = "Please provide a detailed reason (minimum 10 characters)";
    } else {
        // Handle file upload (optional)
        if (isset($_FILES['proof']) && $_FILES['proof']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../uploads/subject_change_proofs/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['proof']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
            
            if (in_array($file_extension, $allowed_extensions)) {
                $file_name = 'proof_' . $student_id . '_' . time() . '.' . $file_extension;
                $file_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['proof']['tmp_name'], $file_path)) {
                    $proof_file = 'uploads/subject_change_proofs/' . $file_name;
                }
            } else {
                $error = "Invalid file type. Only JPG, PNG, and PDF files are allowed.";
            }
        }
        
        if (empty($error)) {
            // Insert request into database
            $current_subjects_json = json_encode($current_subjects);
            
            $stmt = mysqli_prepare($conn, "INSERT INTO subject_change_requests (student_id, current_subjects, requested_subjects, reason, proof_file) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "issss", $student_id, $current_subjects_json, $requested_subjects, $reason, $proof_file);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "Subject change request submitted successfully! You will be notified once it's reviewed.";
            } else {
                $error = "Failed to submit request. Please try again.";
            }
            
            mysqli_stmt_close($stmt);
        }
    }
}

// Fetch student's previous requests
$requestsStmt = mysqli_prepare($conn, "SELECT * FROM subject_change_requests WHERE student_id = ? ORDER BY created_at DESC");
mysqli_stmt_bind_param($requestsStmt, "i", $student_id);
mysqli_stmt_execute($requestsStmt);
$requestsResult = mysqli_stmt_get_result($requestsStmt);
$requests = [];
while ($row = mysqli_fetch_assoc($requestsResult)) {
    $requests[] = $row;
}
mysqli_stmt_close($requestsStmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject Change Request | NEP Portal</title>
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
                    <a href="my_subjects.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                        <i class="bi bi-book-half"></i> My Subjects
                    </a>
                </li>
                <li>
                    <a href="subject_change_request.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-indigo-50 text-indigo-700 font-medium">
                        <i class="bi bi-arrow-left-right"></i> Subject Change
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
                    <?php echo substr($student_name, 0, 1); ?>
                </div>
                <div class="text-sm">
                    <p class="font-medium text-gray-900"><?php echo htmlspecialchars($student_name); ?></p>
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
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
            
            <div class="max-w-4xl mx-auto">
                
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Subject Change Request</h1>
                        <p class="text-gray-600 text-sm mt-1">Request to change your enrolled subjects</p>
                    </div>
                    <a href="student_dashboard.php" class="hidden sm:inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
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

                <!-- Request Form -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <div class="p-1.5 rounded bg-indigo-100 text-indigo-600"><i class="bi bi-file-earmark-text"></i></div>
                        New Request
                    </h2>
                    
                    <form method="POST" enctype="multipart/form-data" class="space-y-5">
                        
                        <!-- Current Subjects (Read-only) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Current Subjects (Read-only)</label>
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                <?php if (!empty($current_subjects)): ?>
                                    <div class="space-y-2">
                                        <?php foreach($current_subjects as $k => $v): ?>
                                            <div class="flex items-center gap-2">
                                                <i class="bi bi-check-circle-fill text-green-500"></i>
                                                <span class="font-medium text-gray-900"><?php echo htmlspecialchars($k); ?>:</span>
                                                <span class="text-gray-600"><?php echo htmlspecialchars($v); ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-gray-500 text-sm">No subjects assigned yet. Please complete your profile first.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Requested Subjects -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Requested Subjects <span class="text-red-500">*</span></label>
                            <textarea name="requested_subjects" rows="4" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition bg-gray-50 focus:bg-white" placeholder="Enter your requested subjects in the format:&#10;Course 1: Mathematics&#10;Course 2: Chemistry&#10;OE: Yoga & Wellness&#10;KS: Indian Constitution" required></textarea>
                            <p class="text-xs text-gray-500 mt-1">Enter each subject on a new line in the format: Subject Type: Subject Name</p>
                        </div>

                        <!-- Reason -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Reason for Change <span class="text-red-500">*</span></label>
                            <textarea name="reason" rows="4" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition bg-gray-50 focus:bg-white" placeholder="Explain why you want to change your subjects..." required></textarea>
                            <p class="text-xs text-gray-500 mt-1">Minimum 10 characters</p>
                        </div>

                        <!-- Upload Proof (Optional) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Upload Proof (Optional)</label>
                            <input type="file" name="proof" accept=".jpg,.jpeg,.png,.pdf" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition bg-gray-50 focus:bg-white">
                            <p class="text-xs text-gray-500 mt-1">Accepted formats: JPG, PNG, PDF (Max 5MB)</p>
                        </div>

                        <button type="submit" name="submit_request" class="w-full py-4 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-700 transition transform hover:-translate-y-0.5 shadow-xl shadow-indigo-500/20">
                            <i class="bi bi-send-fill mr-2"></i> Submit Request
                        </button>
                    </form>
                </div>

                <!-- Request History -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="font-bold text-gray-800 flex items-center gap-2">
                            <i class="bi bi-clock-history text-indigo-600"></i> My Requests
                        </h3>
                    </div>
                    
                    <div class="p-6">
                        <?php if (empty($requests)): ?>
                            <div class="text-center py-8 text-gray-500">
                                <i class="bi bi-inbox text-4xl mb-3 block text-gray-300"></i>
                                No requests submitted yet.
                            </div>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($requests as $req): ?>
                                    <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition">
                                        <div class="flex justify-between items-start mb-3">
                                            <div>
                                                <span class="text-xs text-gray-500">Request #<?php echo $req['id']; ?></span>
                                                <p class="text-xs text-gray-400"><?php echo date('d M Y, h:i A', strtotime($req['created_at'])); ?></p>
                                            </div>
                                            <?php
                                            $statusColors = [
                                                'Pending' => 'bg-yellow-100 text-yellow-700',
                                                'Approved' => 'bg-green-100 text-green-700',
                                                'Rejected' => 'bg-red-100 text-red-700'
                                            ];
                                            $statusColor = $statusColors[$req['status']] ?? 'bg-gray-100 text-gray-700';
                                            ?>
                                            <span class="px-3 py-1 rounded-full text-xs font-bold <?php echo $statusColor; ?>">
                                                <?php echo $req['status']; ?>
                                            </span>
                                        </div>
                                        
                                        <div class="mb-2">
                                            <p class="text-sm font-medium text-gray-700 mb-1">Requested Subjects:</p>
                                            <p class="text-sm text-gray-600 whitespace-pre-line"><?php echo htmlspecialchars($req['requested_subjects']); ?></p>
                                        </div>
                                        
                                        <div>
                                            <p class="text-sm font-medium text-gray-700 mb-1">Reason:</p>
                                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($req['reason']); ?></p>
                                        </div>
                                        
                                        <?php if ($req['proof_file']): ?>
                                            <div class="mt-2">
                                                <a href="../<?php echo htmlspecialchars($req['proof_file']); ?>" target="_blank" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                                    <i class="bi bi-paperclip"></i> View Proof
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
            
        </main>
    </div>
</div>

</body>
</html>
