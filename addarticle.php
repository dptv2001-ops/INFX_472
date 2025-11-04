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
    // DATA COLLECTION AND SANITIZATION
    // Retrieve and clean form input data
    // Using null coalescing operator (??) to provide empty string defaults
    $title = trim($_POST["title"] ?? "");
    $intro = trim($_POST["intro"] ?? "");
    $body = trim($_POST["body"] ?? "");
    $references = trim($_POST["references"] ?? "");
    
    // INPUT VALIDATION
    // Check that required fields (title and intro) are not empty
    if (empty($title) || empty($intro)) {
        $error_msg = "Title and Intro are required fields.";
    } else {
        // SHORT TITLE GENERATION
        // Generate a URL-friendly short_title from the article title
        // This is used for creating clean URLs (e.g., "my-article-title")
        // Steps:
        // 1. Convert to lowercase
        // 2. Replace non-alphanumeric characters with hyphens
        // 3. Remove leading/trailing hyphens
        $short_title = strtolower($title);
        $short_title = preg_replace('/[^a-z0-9]+/', '-', $short_title);
        $short_title = trim($short_title, '-');
        
        // SQL INJECTION PREVENTION
        // Escape all user input to prevent SQL injection attacks
        // This converts special characters to safe SQL strings
        $title = $mysqli->real_escape_string($title);
        $intro = $mysqli->real_escape_string($intro);
        $body = $mysqli->real_escape_string($body);
        $references = $mysqli->real_escape_string($references);
        $short_title = $mysqli->real_escape_string($short_title);
        
        // IMAGE UPLOAD HANDLING
        // Process image upload if one was provided
        $imagename = "";
        
        // Check if an image file was uploaded and the upload was successful
        if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            // Extract image information
            $imagename = $_FILES['image']['name'];        // Original filename
            $imagetmp = $_FILES['image']['tmp_name'];     // Temporary file path
            $image_size = $_FILES['image']['size'];       // File size in bytes
            
            // CREATE UPLOADS DIRECTORY
            // Ensure the uploads directory exists, create it if it doesn't
            // Use absolute path based on current script directory
            $upload_dir_absolute = __DIR__ . "/uploads/";
            $upload_dir_relative = "uploads/";
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir_absolute)) {
                if (!mkdir($upload_dir_absolute, 0777, true)) {
                    $error_msg = "Failed to create uploads directory. Please check permissions.";
                    $imagename = "";
                }
            }
            
            // Only proceed if directory exists or was created successfully
            if (empty($error_msg)) {
                // IMAGE VALIDATION
                // Define allowed image MIME types for security
                $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                $file_type = $_FILES['image']['type'];
                
                // Validate file type
                if (!in_array($file_type, $allowed_types)) {
                    $error_msg = "Invalid image type. Only JPEG, PNG, GIF, and WebP are allowed.";
                } 
                // Validate file size (5MB = 5,000,000 bytes)
                elseif ($image_size > 5000000) {
                    $error_msg = "Image size must be less than 5MB.";
                } 
                // If validation passes, process the upload
                else {
                    // GENERATE UNIQUE FILENAME
                    // Create a unique filename to prevent filename conflicts
                    // Format: uniqid_timestamp.extension
                    $file_extension = pathinfo($imagename, PATHINFO_EXTENSION);
                    $unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
                    $upload_path_absolute = $upload_dir_absolute . $unique_filename;
                    
                    // Move uploaded file from temporary location to permanent storage
                    // Use absolute path for move_uploaded_file
                    if (move_uploaded_file($imagetmp, $upload_path_absolute)) {
                        // Verify file was actually moved
                        if (file_exists($upload_path_absolute)) {
                            // Store only the unique filename in database (relative path for web access)
                            $imagename = $unique_filename;
                        } else {
                            $error_msg = "Image upload failed: File was not saved correctly.";
                            $imagename = "";
                        }
                    } else {
                        // Upload failed - provide more detailed error message
                        $upload_error = $_FILES['image']['error'];
                        $error_msg = "Failed to upload image. Error code: " . $upload_error;
                        if (!is_writable($upload_dir_absolute)) {
                            $error_msg .= " Uploads directory is not writable.";
                        }
                        $imagename = "";
                    }
                }
            }
        }
        
        // DATABASE INSERTION
        // Only proceed if no errors occurred during validation/upload
        if (empty($error_msg)) {
            // Escape image filename if it exists
            if (!empty($imagename)) {
                $imagename = $mysqli->real_escape_string($imagename);
            }
            
            // BUILD SQL INSERT QUERY
            // Create INSERT statement with or without image field
            // Note: 'reference' is in backticks because it's a MySQL reserved word
            if (!empty($imagename)) {
                // Include image in the insert if one was uploaded
                $sql = "INSERT INTO article (short_title, image, title, intro, body, `reference`, author_id) 
                        VALUES ('$short_title', '$imagename', '$title', '$intro', '$body', '$references', '$user_id')";
            } else {
                // Insert without image field if no image was provided
                $sql = "INSERT INTO article (short_title, title, intro, body, `reference`, author_id) 
                        VALUES ('$short_title', '$title', '$intro', '$body', '$references', '$user_id')";
            }
            
            // Execute the SQL query
            if ($mysqli->query($sql)) {
                // SUCCESS - REDIRECT TO WIKI PAGE
                // Article saved successfully - redirect to wiki page with success message
                header("Location: wiki.php?msg=Article+added+successfully");
                exit();
            } else {
                // Database error occurred - display error message
                $error_msg = "Error adding article: " . $mysqli->error;
            }
        }
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
            
            <!-- Body Field (Optional) -->
            <div class="form-input" style="margin-bottom: 20px;">
                <label for="body" style="display: block; margin-bottom: 8px; color: #333; font-weight: 600;">Body:</label>
                <textarea id="body" name="body" rows="8" style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1em; box-sizing: border-box; font-family: inherit; resize: vertical;"></textarea>
            </div>
            
            <!-- References Field (Optional) -->
            <div class="form-input" style="margin-bottom: 20px;">
                <label for="references" style="display: block; margin-bottom: 8px; color: #333; font-weight: 600;">References:</label>
                <textarea id="references" name="references" rows="4" style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1em; box-sizing: border-box; font-family: inherit; resize: vertical;"></textarea>
            </div>
            
            <!-- Submit Button -->
            <input type="submit" value="Add Article" name="submit" style="width: 100%; padding: 14px; background: linear-gradient(135deg, #6bb6ff 0%, #4a90e2 100%); color: white; border: none; border-radius: 10px; font-size: 1.1em; font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(74, 144, 226, 0.4);">
            
            <!-- FORM INTERACTION STYLES -->
            <!-- Focus and hover effects for better user experience -->
            <style>
                /* Add blue border and shadow when input fields are focused */
                input[type="text"]:focus, textarea:focus, input[type="file"]:focus {
                    outline: none;
                    border-color: #4a90e2;
                    box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
                }
                /* Lift button slightly on hover */
                input[type="submit"]:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 6px 20px rgba(74, 144, 226, 0.6);
                }
                /* Return button to normal position on click */
                input[type="submit"]:active {
                    transform: translateY(0);
                }
            </style>
        </form>
    </body>
</html>