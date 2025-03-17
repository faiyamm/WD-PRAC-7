<?php
session_start();
require_once "../backend/config.php";

$username = $email = $password = $confirm_password = "";
$error = "";
$success = "";

if (isset($_SESSION["user_id"])) {
    header("Location: ../frontend/index.html");
    exit;
}
if($_SERVER["REQUEST_METHOD"]=="POST"){
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    //validations
    if(empty($username) || empty($email) || empty($password) || empty($confirm_password)){
        $error = "Please fill out all required fields";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $error = "Please enter a valid email address";
    } elseif (strlen($password)<6){
        $error = "Password must have at least 6 characters";
    } elseif($password !=$confirm_password){
        $error = "Passwords do not match";
    } else { // check for username
        $sql = "SELECT id FROM users WHERE username = ?";
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $username);
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                if(mysqli_stmt_num_rows($stmt)>0){
                    $error = "Username is already taken";
                }
            } else {
                $error = "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
        //check if email is already taken
        if(empty($error)){
            $sql = "SELECT id FROM users WHERE email = ?";
            if($stmt = mysqli_prepare($conn, $sql)){
                mysqli_stmt_bind_param($stmt, "s", $email);
                if(mysqli_stmt_execute($stmt)){
                    mysqli_stmt_store_result($stmt);
                    if(mysqli_stmt_num_rows($stmt)>0){
                        $error = "Email is already taken";
                    }
                } else {
                    $error = "Oops! Something went wrong. Please try again later.";
                }
                mysqli_stmt_close($stmt);
            }
        }
        //insert new user to db
        if(empty($error)){
            $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
            if($stmt = mysqli_prepare($conn, $sql)){
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                mysqli_stmt_bind_param($stmt, "sss", $username, $email, $hashed_password);
                if(mysqli_stmt_execute($stmt)){
                    $success = "You have successfully registered. Please login.";
                    $username = $email = $password = $confirm_password = "";
                } else {
                    $error = "Oops! Something went wrong. Please try again later.";
                }
            }
        }
    }
}
include '../frontend/register.html';
?>