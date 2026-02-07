<?php
session_start();
require_once __DIR__ . '/../db.php';

/* LOGIN CHECK */
if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit;
}

$student_id = intval($_SESSION['student_id']);

/* BASIC DETAILS */
$name   = trim($_POST['name']);
$mobile = trim($_POST['mobile']);
$abc_id = trim($_POST['abc_id']);
$year   = $_POST['year'];

// Server-side validation
$isValid = true;

if (empty($name) || !preg_match("/^[a-zA-Z\s]+$/", $name)) {
    $isValid = false;
}
if (!preg_match("/^[0-9]{10}$/", $mobile)) {
    $isValid = false;
}
if (!preg_match("/^[A-Za-z0-9]{6,}$/", $abc_id)) {
    $isValid = false;
}

/* SUBJECT ARRAY */
$subjects = [];

/* FY SUBJECTS */
if ($year == "FY") {
    $subjects = [
        "Course 1" => $_POST['course1'] ?? '',
        "Course 2" => $_POST['course2'] ?? '',
        "Course 3" => $_POST['course3'] ?? '',
        "OE"       => $_POST['oe'] ?? '',
        "KS"       => $_POST['ks'] ?? ''
    ];
}

/* SY SUBJECTS */
if ($year == "SY") {
    $subjects = [
        "Major"   => $_POST['major'] ?? '',
        "Minor"   => $_POST['minor'] ?? '',
        "OE"      => $_POST['oe'] ?? '',
        "VSC"     => $_POST['vsc'] ?? '',
        "English" => $_POST['english'] ?? '',
        "CC"      => $_POST['cc'] ?? ''
    ];
}

/* TY SUBJECTS */
if ($year == "TY") {
    $subjects = [
        "Major 1" => $_POST['major1'] ?? '',
        "Major 2" => $_POST['major2'] ?? '',
        "OE"      => $_POST['oe'] ?? '',
        "VSC"     => $_POST['vsc'] ?? '',
        "English" => $_POST['english'] ?? '',
        "OJT"     => $_POST['ojt'] ?? ''
    ];
}

/* Convert subjects to JSON */
$subjects_json = json_encode($subjects);

/* SAVE TO DATABASE using prepared statement */
if ($isValid) {
    $stmt = mysqli_prepare($conn, "
        UPDATE students SET
        name = ?,
        mobile = ?,
        abc_id = ?,
        year = ?,
        subjects = ?,
        profile_completed = 1
        WHERE id = ?
    ");
    
    mysqli_stmt_bind_param($stmt, "sssssi", $name, $mobile, $abc_id, $year, $subjects_json, $student_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

/* REDIRECT TO DASHBOARD */
header("Location: student_dashboard.php");
exit;
?>
