<?php
session_start();
require_once __DIR__ . '/../db.php';

if (!isset($_SESSION['admin_id'])) {
    die("Access Denied");
}

if (isset($_GET['type'])) {
    $type = $_GET['type'];
    $filename = $type . "_data_" . date('Y-m-d') . ".csv";
    
    // Set headers to download file rather than displayed
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '";');
    
    // Create a file pointer connected to the output stream
    $output = fopen('php://output', 'w');
    
    if ($type == 'students') {
        // Output column headings
        fputcsv($output, array('ID', 'Name', 'Email', 'Year', 'Mobile', 'ABC ID', 'Subjects (JSON)'));
        
        // Fetch data
        $query = "SELECT id, name, email, year, mobile, abc_id, subjects FROM students ORDER BY id ASC";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            fputcsv($output, $row);
        }
    } 
    elseif ($type == 'teachers') {
        fputcsv($output, array('ID', 'Name', 'Email', 'Department', 'Created At'));
        
        $query = "SELECT id, name, email, department, created_at FROM teachers ORDER BY id ASC";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            fputcsv($output, $row);
        }
    } 
    elseif ($type == 'marks') {
        fputcsv($output, array('ID', 'Student ID', 'Student Name', 'Subject', 'Marks', 'Credits', 'Semester', 'Updated At'));
        
        $query = "SELECT sm.id, sm.student_id, s.name as student_name, sm.subject, sm.marks, sm.credits, sm.semester, sm.updated_at 
                  FROM student_marks sm 
                  JOIN students s ON sm.student_id = s.id 
                  ORDER BY sm.id ASC";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            fputcsv($output, $row);
        }
    }
    
    fclose($output);
    exit;
}
?>
