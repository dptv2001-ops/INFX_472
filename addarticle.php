<?php 

require_once "auth.php";
require_once "db.php";
require_auth();
$user = current_user();
$id = user_id();

if($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

// checks if form is submitted
if(isset($_POST['submit'])) {
    $short_title = $mysqli->real_escape_string($_POST["short-title"]);
    $title = $mysqli->real_escape_string($_POST["title"]);
    $intro = $mysqli->real_escape_string($_POST["intro"]);
    $body = $mysqli->real_escape_string($_POST["body"]);
    $references = $mysqli->real_escape_string($_POST["references"]);
    $create_date = date("Y-m-d H:i:s");
    // gets submitted values - if image is uploaded
    if(isset($_FILES['image'])) {
        $imagename = $_FILES['image']['name'];
        $imagetmp = $_FILES['image']['tmp_name'];
        $folder = "./images/".$imagename;
        $sql = "INSERT INTO article (image, short_title, title, intro, body, `reference`, author_id, created_at) VALUES ('$imagename', '$short_title', '$title', '$intro', '$body', '$references', '$id', '$create_date')";
    }
    // gets submitted values - if no image is uploaded
    else {
        $sql = "INSERT INTO article (short_title, title, intro, body, `reference`, created_at) VALUES ('$short_title', '$title', '$intro', '$body', '$references', '$create_date')";
    }

    $submit = $mysqli->query($sql);
    // check if image file is moved to folder
    if(!move_uploaded_file($imagetmp, $folder)) {
        echo "Failed to upload image";
    } 
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Add Article</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <header>
        <nav class="nav-bar">
            <ul>
                <li><a href="wiki.php">Home</a></li>
                <li><a class="active" href="addarticle.php">Add Article</a></li>
                <li><a href="logout.php">Logout</a></li>
                <li><?php echo htmlspecialchars($user); ?></li>
            </ul>
        </nav>
        <h1>The INFX Wiki</h1>
        <p class="subheading">Welcome, <?php echo htmlspecialchars($user); ?>!</p>
    </header>
        <h1>Add Article</h1>
        <div class="subheading">
            <p>Use the form below to add a new article to the wiki.</p>
        </div>
        <form method="POST" enctype="multipart/form-data" action="addarticle.php">
            <div class="form-input" id="image-upload">
                <label for="image">Image:</label><br>
                <input type="file" id="image" accept="image/*" name="image">
            </div>
             <div class="form-input">
                <label for="short-title">Short URL title:</label><br>
                <input type="text" id="short-title" name="short-title" required>
            </div>
            <div class="form-input">
                <label for="title">Title:</label><br>
                <input type="text" id="title" name="title" required>
            </div>
            <div class="form-input">
                <label for="intro">Intro Text:</label><br>
                <textarea id="intro" name="intro" rows="4" cols="50" required></textarea>
            </div>
            <div class="form-input">
                <label for="body">Body:</label><br>
                <textarea id="body" name="body" rows="4" cols="50"></textarea>
             </div>
             <div>
                <label for="references">References:</label><br>
                <textarea id="references" name="references" rows="4" cols="50"></textarea>
             </div>
            <input type="submit" value="Add Article" name="submit">
        </form>
    </body>
</html>