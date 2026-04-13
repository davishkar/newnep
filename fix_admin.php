<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/db.php';

echo "Database connection successful.<br>";

$email = "admin@gmail.com";
$password = "admin123";
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt = mysqli_prepare($conn, "UPDATE admins SET password = ? WHERE email = ?");
mysqli_stmt_bind_param($stmt, "ss", $hashed_password, $email);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        echo "SUCCESS: Password for '$email' has been reset to '$password'.<br>";
    } else {
        echo "NOTICE: Password was already set or user '$email' not found.<br>";
        
        // Check if user exists
        $check = mysqli_query($conn, "SELECT * FROM admins WHERE email = '$email'");
        if (mysqli_num_rows($check) == 0) {
            echo "ERROR: User '$email' does not exist in the database!<br>";
             // Create the user if it doesn't exist
            $insert = mysqli_prepare($conn, "INSERT INTO admins (name, email, password) VALUES ('Administrator', ?, ?)");
            mysqli_stmt_bind_param($insert, "ss", $email, $hashed_password);
            if (mysqli_stmt_execute($insert)) {
                echo "CREATED: User '$email' created with password '$password'.<br>";
            } else {
                 echo "ERROR CREATING USER: " . mysqli_error($conn) . "<br>";
            }
        }
    }
} else {
    echo "ERROR UPDATING PASSWORD: " . mysqli_error($conn) . "<br>";
}

mysqli_stmt_close($stmt);
echo "Done.";
?>
