<?php
require 'backend_db.php';  
// Load database connection + start session (needed to manipulate session variables)

// Remove all session variables
session_unset();  
// This clears the $_SESSION array, logging the user out

// Destroy the session completely
session_destroy();  
// Deletes the session data on the server and invalidates the session ID

// Redirect the user to the homepage after logout
header('Location: index.php');  
exit;  
// Stop script execution to ensure redirect happens immediately