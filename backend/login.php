<?php
session_start();
require_once "../backend/config.php";

$username = $password = "";
$error = "";
$success = "";

if (isset($_SESSION["user_id"])) {
    header("Location: ../frontend/index.html");
    exit;
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    if(empty($username) || empty($password)) {
        $error = "Please fill out all required fields";
    } else {
        $sql = "SELECT id, username, password FROM users WHERE username = ?";
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt,"s", $username); //prepare statement
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt); //store result
                if(mysqli_stmt_num_rows($stmt)==1){ // check if username exists
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);
                    if(mysqli_stmt_fetch($stmt)){
                        if(password_verify($password, $hashed_password)){ // check if password is correct
                            session_start();
                            $_SESSION["user_id"] = $id;
                            $_SESSION["username"] = $username;
                            header("Location: ../frontend/index.html");
                            exit;
                        } else {
                            $error = "Invalid username or password.";
                        }
                    }
                } else {
                    $error = "Invalid username or password.";
                }
            } else {
                $error = "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}
include '../frontend/login.html';
?>