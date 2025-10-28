<?php 

include 'db.php';

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Add Article</title>
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
        <h1>Add Article</h1>
        <div class="subheading">
            <a href="index.php" id="return">Go back home</a>
            <p>Use the form below to add a new article to the wiki.</p>
        </div>
        <form method="POST">
            <div class="form-input" id="image-upload">
                <label for="image">Image:</label><br>
                <input type="file" id="image" accept="image/*">
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
                <textarea id="section3" name="section3" rows="4" cols="50"></textarea>
             </div>
             <!-- reference section, image, date, user uploaded -->
            <input type="submit" value="Add Article">
        </form>
    </body>
</html>