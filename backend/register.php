<?php
session_start();
require_once "../backend/config.php"; // db connection
// same as login.php: define variables and initialize them with empty values
$username = $email = $password = $confirm_password = "";
$error = "";
$success = "";
// check if user is already logged in and redirect to index(home) page
if (isset($_SESSION["user_id"])) {
    header("Location: ../frontend/index.html");
    exit;
}
// get data from form 
if($_SERVER["REQUEST_METHOD"]=="POST"){
    $username = trim($_POST["username"]); //trim to remove white(extra) spaces
    $email = trim($_POST["email"]); //get username. email, password
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    //validations
    if(empty($username) || empty($email) || empty($password) || empty($confirm_password)){
        $error = "Please fill out all required fields"; //check if any field is empty, if yes then show error
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $error = "Please enter a valid email address"; //check if email is valid
    } elseif (strlen($password)<6){ //check if password is less than 6 characters
        $error = "Password must have at least 6 characters";
    } elseif($password !=$confirm_password){ //check if password and confirm password match
        $error = "Passwords do not match";
    } else { // check in db for existing username
        $sql = "SELECT id FROM users WHERE username = ?";
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $username); //prevent sql injection with ? and bind_param
            if(mysqli_stmt_execute($stmt)){ //execute statement
                mysqli_stmt_store_result($stmt); //store result
                if(mysqli_stmt_num_rows($stmt)>0){ // check if a matching username exists
                    $error = "Username is already taken"; // if username is found, show error
                }
            } else {
                $error = "Oops! Something went wrong. Please try again later."; // if query fails, show error
            }
            mysqli_stmt_close($stmt); //close statement
        }
        //check if email is already taken
        if(empty($error)){
            $sql = "SELECT id FROM users WHERE email = ?";
            if($stmt = mysqli_prepare($conn, $sql)){
                mysqli_stmt_bind_param($stmt, "s", $email); //prevent sql injection with ? and bind_param
                if(mysqli_stmt_execute($stmt)){
                    mysqli_stmt_store_result($stmt);
                    if(mysqli_stmt_num_rows($stmt)>0){ // check if a matching email exists
                        $error = "Email is already taken";
                    }
                } else {
                    $error = "Oops! Something went wrong. Please try again later.";
                }
                mysqli_stmt_close($stmt);
            }
        }
        //insert new user to db
        if(empty($error)){ // if no error, insert new user to db
            $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)"; // safequery to insert new user
            if($stmt = mysqli_prepare($conn, $sql)){ //prepare statement
                $hashed_password = password_hash($password, PASSWORD_DEFAULT); //hash password for security reasons
                mysqli_stmt_bind_param($stmt, "sss", $username, $email, $hashed_password); // prevent sql injection with ? and bind_param
                if(mysqli_stmt_execute($stmt)){ //execute statement
                    $success = "You have successfully registered. Please login."; // if user is successfully registered, show success message
                    $username = $email = $password = $confirm_password = ""; //clear all fields
                } else {
                    $error = "Oops! Something went wrong. Please try again later."; // if query fails, show error
                }
            }
        }
    }
}
include '../frontend/register.html';
?>