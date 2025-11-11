<?php 
/**
 * ADD ARTICLE PAGE
 * 
 * This page allows authenticated users to add new articles to the wiki.
 * It handles form submission, validates input, uploads images, and saves
 * articles to the database.
 */

// Include required files for authentication and database connection
require_once "auth.php";
require_once "db.php";

// AUTHENTICATION CHECK
// Ensure the user is logged in before allowing article creation
require_auth();

// Get the current logged-in user's ID from the session
// This will be used to associate the article with the author
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    // Redirect to login page if user ID is not found in session
    header("Location: login.php?msg=Please+log+in+to+add+articles");
    exit();
}

// Initialize message variables for displaying success/error feedback
$success_msg = "";
$error_msg = "";

// FORM SUBMISSION HANDLING
// Check if the form has been submitted
if(isset($_POST['submit'])) {
    $short_title = $mysqli->real_escape_string($_POST["short_title"]);
        $title = $mysqli->real_escape_string($_POST["title"]);
        $intro = $mysqli->real_escape_string($_POST["intro"]);
        $body = $mysqli->real_escape_string($_POST["body"]);
        $references = $mysqli->real_escape_string($_POST["references"]);
        $create_date = date("Y-m-d H:i:s");
    // gets submitted values
    if(isset($_FILES['image'])) {
        $imagename = $_FILES['image']['name'];
        $imagetmp = $_FILES['image']['tmp_name'];
        $folder = "./images/".$imagename;
        $sql = "INSERT INTO article (image, short_title, title, intro, body, `reference`, author_id, created_at) VALUES ('$imagename', '$short_title', '$title', '$intro', '$body', '$references', '$user_id', '$create_date')";
    }
    else {
        $sql = "INSERT INTO article (short_title, title, intro, body, `reference`, created_at) VALUES ('$short_title', '$title', '$intro', '$body', '$references', '$create_date')";
    }

    $submit = $mysqli->query($sql);

    if($submit) {
        echo "Added article successfully. Return to home to view.";
    }
    else {
        echo "Error" . mysqli_error($mysqli);
    }
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <!-- HTML HEAD SECTION -->
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Add Article - The INFX Wiki</title>
        <link rel="stylesheet" href="style.css">
        
        <!-- Page-specific styles for the add article form -->
        <style>
            body {
                background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
                min-height: 100vh;
                padding: 20px;
            }
        </style>
    </head>
    <body>
        <!-- PAGE HEADER -->
        <header style="text-align: center; margin-bottom: 30px; background: rgba(255, 255, 255, 0.9); padding: 20px; border-radius: 15px; box-shadow: 0 4px 15px rgba(74, 144, 226, 0.2);">
            <h1 style="color: #4a90e2;">Add Article</h1>
            <div class="subheading">
                <a href="wiki.php" id="return">← Back to Home</a>
                <p style="margin-top: 15px; color: #666;">Use the form below to add a new article to the wiki.</p>
            </div>
        </header>
        
        <!-- ERROR MESSAGE DISPLAY -->
        <!-- Display error messages if validation failed or an error occurred -->
        <?php if ($error_msg): ?>
            <div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #f5c6cb; max-width: 800px; margin-left: auto; margin-right: auto;">
                <?php echo htmlspecialchars($error_msg); ?>
            </div>
        <?php endif; ?>
        
        <!-- SUCCESS MESSAGE DISPLAY -->
        <!-- Display success messages (though we usually redirect on success) -->
        <?php if ($success_msg): ?>
            <div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #c3e6cb; max-width: 800px; margin-left: auto; margin-right: auto;">
                <?php echo htmlspecialchars($success_msg); ?>
            </div>
        <?php endif; ?>
        
        <!-- ARTICLE FORM -->
        <!-- 
             Form attributes:
             - method="POST": Send form data via POST request
             - enctype="multipart/form-data": Required for file uploads
             - action="addarticle.php": Submit to this same page
        -->
        <form method="POST" enctype="multipart/form-data" action="addarticle.php" style="background: rgba(255, 255, 255, 0.9); padding: 30px; border-radius: 15px; box-shadow: 0 4px 15px rgba(74, 144, 226, 0.2); max-width: 800px; margin: 0 auto;">
            
            <!-- Image Upload Field (Optional) -->
            <div class="form-input" id="image-upload" style="margin-bottom: 20px;">
                <label for="image" style="display: block; margin-bottom: 8px; color: #333; font-weight: 600;">Image (optional):</label>
                <input type="file" id="image" accept="image/*" name="image" style="padding: 8px; border: 2px solid #e0e0e0; border-radius: 8px; width: 100%; box-sizing: border-box;">
                <small style="color: #666;">Max size: 5MB. Allowed types: JPEG, PNG, GIF, WebP</small>
            </div>
            
            <!-- Short Title Field (Required) -->
            <div class="form-input" id="short_title" style="margin-bottom: 20px;">
                <label for="short_title" style="display: block; margin-bottom: 8px; color: #333; font-weight: 600;">Short title: <span style="color: red;">*</span></label>
                <input type="text" id="short_title"  name="short_title" required style="padding: 8px; border: 2px solid #e0e0e0; border-radius: 8px; width: 100%; box-sizing: border-box;">
            </div>

            <!-- Title Field (Required) -->
            <div class="form-input" style="margin-bottom: 20px;">
                <label for="title" style="display: block; margin-bottom: 8px; color: #333; font-weight: 600;">Title: <span style="color: red;">*</span></label>
                <input type="text" id="title" name="title" required style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1em; box-sizing: border-box;">
            </div>
            
            <!-- Intro Text Field (Required) -->
            <div class="form-input" style="margin-bottom: 20px;">
                <label for="intro" style="display: block; margin-bottom: 8px; color: #333; font-weight: 600;">Intro Text: <span style="color: red;">*</span></label>
                <textarea id="intro" name="intro" rows="4" required style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1em; box-sizing: border-box; font-family: inherit; resize: vertical;"></textarea>
            </div>
            <div class="form-input">
                <label for="body">Body:</label><br>
                <textarea id="body" name="body" rows="4" cols="50"></textarea>
             </div>
             <div>
                <label for="references">References:</label><br>
                <textarea id="references" name="references" rows="4" cols="50"></textarea>
             </div>
             <!-- reference section, image, date, user uploaded -->
            <input type="submit" value="Add Article" name="submit">
        </form>

        <footer style="text-align:center;">
            <p>&copy; 2025 INFX Wiki Project</p>
        </footer>
    </body>
</html>