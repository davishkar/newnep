<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/db.php';

echo "Database connection successful.\n";

$emails_to_check = ["admin@example.com", "admin@gmail.com"];

foreach ($emails_to_check as $email) {
    echo "Checking for user: $email\n";
    $query = "SELECT * FROM admins WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if ($result) {
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            echo "Admin found: " . $row['email'] . "\n";
            echo "ID: " . $row['id'] . "\n";
            echo "Name: " . $row['name'] . "\n";
            echo "Password Hash: " . $row['password'] . "\n";
            
            // Test default password
            $test_pass = "admin123";
            if (password_verify($test_pass, $row['password'])) {
                echo "Password '$test_pass' matches hash.\n";
            } else {
                echo "Password '$test_pass' DOES NOT match hash.\n";
                if ($test_pass === $row['password']) {
                     echo "Password '$test_pass' matches plain text.\n";
                } else {
                     echo "Password '$test_pass' does not match plain text either.\n";
                }
            }
        } else {
            echo "Admin user '$email' not found.\n";
        }
    } else {
        echo "Query failed: " . mysqli_error($conn) . "\n";
    }
    echo "--------------------------------------------------\n";
}

// List all admins
$all = mysqli_query($conn, "SELECT * FROM admins");
echo "Listing all admins in DB:\n";
while ($r = mysqli_fetch_assoc($all)) {
    echo $r['id'] . " | " . $r['email'] . " | " . $r['password'] . "\n";
}
?>
