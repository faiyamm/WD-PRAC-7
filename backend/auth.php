<?php
session_start();
require_once "../backend/config.php"; // db connection
header("Content-Type: application/json"); // set response content type as json
$action = isset($_GET["action"]) ? $_GET["action"] : ""; // handle action
switch ($action) {
    case "check_auth": // check if user is logged in with function checkAuth()
        checkAuth();
        break;
    case "logout": // logout user with function logout()
        logout();
        break;
    default: // if action is not valid, show error
        echo json_encode([
            "success" => false,
            "message" => "Invalid action"
        ]);
        break;
}
function checkAuth() {
    $isLoggedIn = isset($_SESSION["user_id"]); // check if user id is logged in (check id badge)
    $user = null; // set user to null
    if ($isLoggedIn) { // if user is logged in, set user data
        $user = [
            "id"=> $_SESSION["user_id"],
            "username"=> $_SESSION["username"]
        ];
    } // return json response with isLoggedIn(true/false) and user data (if you're logged in), or null (if you're not)
    echo json_encode([
        "isLoggedIn" => $isLoggedIn,
        "user" => $user
    ]);
}
function logout(){
    $_SESSION = []; // clear session data (clears ur id badge)
    session_destroy(); // destroy session (it takes the id badge away)
    echo json_encode([ // return json response with success message
        "success" => true,
        "message" => "Logged out successfully"
    ]);
}
?>