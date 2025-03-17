<?php
session_start();
require_once "../backend/config.php";
header("Content-Type: application/json");

function isLoggedIn(){
    return isset($_SESSION["user_id"]);
}
$action = isset($_GET["action"]) ? $_GET["action"] : "";
if ($_SERVER["REQUEST_METHOD"]=="POST"){
    $json_data = file_get_contents("php://input");
    $post_data = json_decode($json_data, true);

    if(isset($post_data["action"])){
        $action = $post_data["action"];
    }
}
switch($action){
    case "add_favorite":
        if (!isLoggedIn()){
            echo json_encode([
                "success" => false,
                "message" => "Please login to add favorite"
            ]);
            exit;
        }
        addFavorite($post_data["data"], $conn);
        break;

    case "get_favorites":
        if (!isLoggedIn()){
            echo json_encode([
                "success" => false,
                "message" => "Please login to view favorites"
            ]);
            exit;
        }
        getFavorites($conn);
        break;
    
    default:
        echo json_encode([
            "success" => false,
            "message" => "Invalid action"
        ]);
        break;
}

function addFavorite($data, $conn){
    if(!isset($data["title"]) || !isset($data["date"]) || !isset($data["explanation"])){
        echo json_encode([
            "success" => false,
            "message" => "Missing required data"
        ]);
        exit;
    }
    $user_id = $_SESSION["user_id"];
    $title = mysqli_real_escape_string($conn, $data['title']);
    $date = mysqli_real_escape_string($conn, $data['date']);
    $explanation = mysqli_real_escape_string($conn, $data['explanation']);
    $url = mysqli_real_escape_string($conn, $data['url']);
    $media_type = mysqli_real_escape_string($conn, $data['media_type']);

    $check_sql = "SELECT id FROM favorites WHERE date = '$date' AND user_id = $user_id";
    $result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($result) > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'This item is already in your favorites'
        ]);
        return;
    }
    
    //insert to db
    $sql = "INSERT INTO favorites (user_id, title, date, explanation, url, media_type) 
            VALUES ($user_id, '$title', '$date', '$explanation', '$url', '$media_type')";
    if (mysqli_query($conn, $sql)) {
        echo json_encode([
            'success' => true,
            'message' => 'Added to favorites successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . mysqli_error($conn)
        ]);
    }
}

function getFavorites($conn){
    $user_id = $_SESSION["user_id"];
    $sql = "SELECT * FROM favorites WHERE user_id = $user_id ORDER BY date DESC";
    $result = mysqli_query($conn, $sql);
    if (!$result){
        echo json_encode([
            "success" => false,
            "message" => "Database error: ".mysqli_error($conn)
        ]);
        return;
    }
    $favorites = [];
    while($row = mysqli_fetch_assoc($result)){
        $favorites[] = $row;
    }
    echo json_encode([
        "success" => true,
        "data" => $favorites
    ]);
}
?>