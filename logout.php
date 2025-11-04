<?php
// Start session if not already started
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

// Destroy the session
session_destroy();

// Redirect to the home page with a logout message
header('Location: index.php?msg=You+have+been+logged+out+successfully');
exit();
?>

