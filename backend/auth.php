<?php
session_start();
require_once "../backend/config.php";
header("Content-Type: application/json");
$action = isset($_GET["action"]) ? $_GET["action"] : "";
switch ($action) {
    case "check_auth":
        checkAuth();
        break;
    case "logout":
        logout();
        break;
    default:
        echo json_encode([
            "success" => false,
            "message" => "Invalid action"
        ]);
        break;
}
function checkAuth() {
    $isLoggedIn = isset($_SESSION["user_id"]);
    $user = null;
    if ($isLoggedIn) {
        $user = [
            "id"=> $_SESSION["user_id"],
            "username"=> $_SESSION["username"]
        ];
    }
    echo json_encode([
        "isLoggedIn" => $isLoggedIn,
        "user" => $user
    ]);
}
function logout(){
    $_SESSION = []; // clear session data
    session_destroy(); // destroy session
    echo json_encode([
        "success" => true,
        "message" => "Logged out successfully"
    ]);
}
?>