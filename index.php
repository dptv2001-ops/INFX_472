
<?php 
require_once "auth.php"; 
require_once "db.php"; 

// Check if the user is authenticated 
if (isset($_SESSION['username'])) {
    // User is logged in, redirect them to the main wiki page
    header('Location: wiki.php'); 
    exit();
}

// Check for status messages from login.php or createuser.php
$message = isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>The INFX Wiki</title>
    <!-- Link to the external CSS file -->
    <link rel="stylesheet" href="style.css"> 
</head>
<body>
    <header>
        <!-- Creative Component: Using custom color palette from style.css -->
        <h1 class="main-heading">Welcome to The INFX Wiki</h1>
        <p class="subheading">Your Knowledge Hub for INFX 472</p>
    </header>

    <main>
        <?php if ($message): ?>
            <p style="color: var(--primary-color); font-weight: bold;"><?php echo $message; ?></p>
        <?php endif; ?>

        <p>You must log in to view or add articles and navigate through the website.</p>
        
        <!-- Navigation for Unauthenticated Users -->
        <p>
            <a href="login.php">Log In</a> | 
            <a href="createuser.php">Create New Account</a>
        </p>
    </main>
    
    <footer>
        <p>&copy; 2025 INFX Wiki Project</p>
    </footer>
</body>
</html>
