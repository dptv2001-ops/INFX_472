<?php
//start sessions and authentication

if(session_status() === PHP_SESSION_NONE){
    session_start();
}

function require_auth(){
    if(!isset($_SESSION['username'])){
        header("Location: login.php?msg=Please+log+in"); //please log in???
        exit;
    }
}

function current_user(){
    return $_SESSION['username'] ?? null;
}

function user_id(){
    return $_SESSION['user_id'] ?? null;
}
?>