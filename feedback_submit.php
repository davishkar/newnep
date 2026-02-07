<?php
include "db.php";

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);
    
    // Server-side validation
    if (empty($name) || !preg_match("/^[a-zA-Z\s]+$/", $name)) {
        $error = "Name should contain only letters and spaces";
    }
    else if (strlen($name) < 3 || strlen($name) > 50) {
        $error = "Name must be between 3 and 50 characters";
    }
    else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    }
    else if (empty($message) || strlen($message) < 10) {
        $error = "Message must be at least 10 characters";
    }
    else if (strlen($message) > 1000) {
        $error = "Message must not exceed 1000 characters";
    }
    else {
        // Insert feedback using prepared statement
        $stmt = mysqli_prepare($conn, "INSERT INTO feedback (name, email, message, submitted_at) VALUES (?, ?, ?, NOW())");
        mysqli_stmt_bind_param($stmt, "sss", $name, $email, $message);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Thank you for your feedback! We will get back to you soon.";
            
            // Redirect back to homepage with success message
            header("refresh:3;url=index.html");
        } else {
            $error = "Failed to submit feedback. Please try again.";
        }
        
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Feedback Submission</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{
    background:linear-gradient(135deg,#4e46e5,#8b5cf6);
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    font-family:'Poppins',sans-serif;
}
.card{
    background:#fff;
    padding:40px;
    border-radius:20px;
    box-shadow:0 20px 50px rgba(0,0,0,0.2);
    max-width:500px;
    width:100%;
}
.success{
    color:#28a745;
    background:#d4edda;
    padding:15px;
    border-radius:10px;
    margin-bottom:20px;
}
.error{
    color:#dc3545;
    background:#f8d7da;
    padding:15px;
    border-radius:10px;
    margin-bottom:20px;
}
</style>
</head>
<body>

<div class="card">
    <h2 class="text-center mb-4">Feedback Submission</h2>
    
    <?php if(!empty($success)): ?>
        <div class="success"><?php echo $success; ?></div>
        <p class="text-center">Redirecting to homepage...</p>
    <?php elseif(!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
        <a href="index.html" class="btn btn-primary w-100">Go Back</a>
    <?php else: ?>
        <p class="text-center">Processing your feedback...</p>
    <?php endif; ?>
</div>

</body>
</html>
