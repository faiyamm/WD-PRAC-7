<?php
session_start();
require_once "../backend/config.php"; // db connection
// define variables and initialize them with empty values
$username = $password = "";
$error = "";
$success = "";
// check if user is already logged in and redirect to index(home) page
if (isset($_SESSION["user_id"])) {
    header("Location: ../frontend/index.html");
    exit;
}
if ($_SERVER["REQUEST_METHOD"] == "POST") { //check if form is submitted
    $username = trim($_POST["username"]); //trim to remove white(extra) spaces
    $password = $_POST["password"]; //get username and password
    if(empty($username) || empty($password)) {
        $error = "Please fill out all required fields"; //check if fields are empty
    } else {
        $sql = "SELECT id, username, password FROM users WHERE username = ?"; // safequery to search for username is db
        if($stmt = mysqli_prepare($conn, $sql)){ //prepare statement
            mysqli_stmt_bind_param($stmt,"s", $username); //prevent sql injection with ? and bind_param
            if(mysqli_stmt_execute($stmt)){ //execute statement
                mysqli_stmt_store_result($stmt); //store result
                if(mysqli_stmt_num_rows($stmt)==1){ // check if a matching username exists
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password); //get stored user id, username and password stored in hash
                    if(mysqli_stmt_fetch($stmt)){
                        if(password_verify($password, $hashed_password)){ // check if password is correct
                            session_start(); // if password is correct, start a new session
                            $_SESSION["user_id"] = $id;
                            $_SESSION["username"] = $username;
                            header("Location: ../frontend/index.html");
                            exit;
                        } else { // if password is incorrect, show error
                            $error = "Invalid username or password.";
                        }
                    }
                } else { // if username is not found, show error
                    $error = "Invalid username or password.";
                }
            } else { // if query fails, show error
                $error = "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt); //close statement
        }
    }
} 
include '../frontend/login.html';
?>