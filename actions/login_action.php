<?php
session_start(); // Start the session at the very beginning of the script
include("../config/db.php"); // Include your database connection file.
                            // This file is assumed to establish a $conn variable (a mysqli object).

// Check if the request method is POST (meaning the form was submitted)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Get and Sanitize User Input
    // Use trim() to remove any leading/trailing whitespace from the username.
    // Use the null coalescing operator (?? '') for robustness,
    // ensuring variables are set even if POST data is missing.
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? ''; // Do NOT trim passwords before verification

    // Basic validation: Check if both fields are provided
    if (empty($username) || empty($password)) {
        $_SESSION['error_message'] = "Please enter both username and password.";
        header("Location: ../public/login.php"); // Redirect back to the login page
        exit(); // Stop script execution
    }

    // 2. Prepare and Execute the SQL Statement (using Prepared Statements for security)
    // Prepared statements are essential to prevent SQL injection attacks.
    // We select the user's ID, username, and the hashed password from the 'users' table.
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");

    // Check if the prepared statement failed (e.g., due to a syntax error in the query
    // or an incorrect table/column name). Log this error for debugging.
    if ($stmt === false) {
        error_log("Login prepare statement failed: " . $conn->error); // Log to PHP error log
        $_SESSION['error_message'] = "An unexpected error occurred. Please try again.";
        header("Location: ../public/login.php");
        exit();
    }

    // Bind the username parameter to the prepared statement. "s" indicates a string type.
    $stmt->bind_param("s", $username);
    // Execute the prepared statement.
    $stmt->execute();
    // Get the result set from the executed statement.
    $result = $stmt->get_result();

    // 3. Process the Query Result
    // Check if exactly one user was found with the given username.
    if ($result->num_rows === 1) {
        // Fetch the user's data as an associative array.
        $user_data = $result->fetch_assoc();

        // Verify the provided password against the hashed password stored in the database.
        // password_verify() is used because passwords should be stored using password_hash().
        if (password_verify($password, $user_data['password'])) {
            // Password is correct! Set session variables for the logged-in user.
            // Ensure you use 'id' as per your database table's primary key column name.
            $_SESSION['user_id'] = $user_data['id']; // Assuming 'id' is the primary key column
            $_SESSION['username'] = $user_data['username'];
            $_SESSION['logged_in'] = true; // Flag to indicate user is logged in

            // Security: Regenerate session ID to prevent Session Fixation attacks.
            session_regenerate_id(true);

            // Set a success message to be displayed on the dashboard.
            $_SESSION['success_message'] = "Login successful! Welcome, " . htmlspecialchars($user_data['username']) . ".";

            // Redirect the user to the dashboard page.
            header("Location: ../pages/dashboard.php");
            exit(); // Stop script execution after redirect
        } else {
            // Password does not match. Provide a generic error message for security.
            $_SESSION['error_message'] = "Invalid username or password.";
            header("Location: ../public/login.php"); // Redirect back to login form
            exit();
        }
    } else {
        // No user found with the provided username. Provide a generic error message.
        $_SESSION['error_message'] = "Invalid username or password.";
        header("Location: ../public/login.php"); // Redirect back to login form
        exit();
    }

    // 4. Close Database Resources
    $stmt->close(); // Close the prepared statement.
    // It's generally good practice to keep the main database connection ($conn) open
    // if other parts of your application might include db.php later.
    // Only close $conn here if this is the very last database interaction.
    // $conn->close();

} else {
    // If the script is accessed directly (not via POST request), redirect to the login page.
    header("Location: ../public/login.php");
    exit(); // Stop script execution
}
?>