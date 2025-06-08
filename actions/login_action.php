<?php
session_start();
include("../config/db.php"); // Assuming this connects to the database and sets $conn

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Sanitize input (important for security)
    // Using mysqli_real_escape_string if using MySQLi procedural
    // or prepared statements (recommended) if using PDO or MySQLi object-oriented

    // Assuming your 'users' table has 'username' and 'password' columns
    // And 'password' is hashed (e.g., using password_hash())

    // --- Potential Error Source (Line 12 could be around here) ---
    // Example using MySQLi (if your db.php gives you $conn as mysqli object)
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Line 12 might be right after this, trying to fetch without checking
    // $user_data = $result->fetch_assoc(); // If this line is 12 and $result is empty

    // --- FIX: Always check if a row was found before accessing its data ---
    if ($result->num_rows === 1) { // Check if exactly one user was found
        $user_data = $result->fetch_assoc();

        // Verify password (assuming password_hash was used for storage)
        if (password_verify($password, $user_data['password'])) {
            // Password is correct, set session variables
            $_SESSION['user_id'] = $user_data['user_id'];
            $_SESSION['username'] = $user_data['username'];
            $_SESSION['logged_in'] = true;

            // Redirect to dashboard
            header("Location: ../pages/dashboard.php");
            exit();
        } else {
            // Invalid password
            $_SESSION['error_message'] = "Invalid credentials.";
            header("Location: ../public/login.php"); // Redirect back to login form
            exit();
        }
    } else {
        // No user found with that username
        $_SESSION['error_message'] = "Invalid credentials.";
        header("Location: ../public/login.php"); // Redirect back to login form
        exit();
    }

    $stmt->close();
    $conn->close(); // Close connection if not used elsewhere
} else {
    // Not a POST request, redirect to login page
    header("Location: ../public/login.php");
    exit();
}
?>