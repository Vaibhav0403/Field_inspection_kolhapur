<?php
// Start the session if it's not already started.
// This is crucial because you need access to the session to destroy it.
session_start();

// Destroy all session variables.
// This effectively logs the user out by removing all stored session data.
$_SESSION = array(); // Clear the $_SESSION array first

// If using cookies for session management (which is default),
// it's a good practice to delete the session cookie as well.
// Note: This will destroy the session, and not just the session data!
// It's important to do this for security.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session.
session_destroy();

// Redirect the user to the login page after logging out.
// Adjust the path as per your file structure.
// If logout.php is in 'includes/' or 'actions/', and login.php is in 'public/',
// then '../public/login.php' is likely the correct path.
header("Location: ../public/login.php");
exit(); // Always exit after a header redirect to ensure no further code is executed
?>