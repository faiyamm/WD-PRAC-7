<?php
session_start();
require_once "../backend/config.php"; // db connection
header("Content-Type: application/json"); // set response content type as json

function isLoggedIn(){ // check if user is logged in
    return isset($_SESSION["user_id"]); // returns true if you have a user id in the session, false if not
}

$action = isset($_GET["action"]) ? $_GET["action"] : ""; // handle action
if ($_SERVER["REQUEST_METHOD"]=="POST"){ // check if request method is POST
    $json_data = file_get_contents("php://input"); // get json data from request
    $post_data = json_decode($json_data, true); // decode json data

    if(isset($post_data["action"])){ // check if action is set in post data
        $action = $post_data["action"];
    }
}
switch($action){
    case "add_favorite": // add favorite apod
        if (!isLoggedIn()){ // check if user is logged in
            echo json_encode([
                "success" => false,
                "message" => "Please login to add favorite" // if not logged in, show error message
            ]);
            exit;
        }
        addFavorite($post_data["data"], $conn); // if logged in, add favorite
        break;

    case "get_favorites": // get all favorites from user
        if (!isLoggedIn()){ // check if user is logged in
            echo json_encode([
                "success" => false,
                "message" => "Please login to view favorites" // if not logged in, show error message
            ]);
            exit;
        }
        getFavorites($conn); // if logged in, get favorites
        break;
    
    default: // if action is not valid, show error
        echo json_encode([
            "success" => false,
            "message" => "Invalid action"
        ]);
        break;
}
//
function addFavorite($data, $conn){
    if(!isset($data["title"]) || !isset($data["date"]) || !isset($data["explanation"])){
        echo json_encode([
            "success" => false,
            "message" => "Missing required data" // if title, date, or explanation is missing, show error message
        ]);
        exit;
    }
    // clean all data to prevent security issues
    $user_id = $_SESSION["user_id"];
    $title = mysqli_real_escape_string($conn, $data['title']);
    $date = mysqli_real_escape_string($conn, $data['date']);
    $explanation = mysqli_real_escape_string($conn, $data['explanation']);
    $url = mysqli_real_escape_string($conn, $data['url']);
    $media_type = mysqli_real_escape_string($conn, $data['media_type']);
    // check if item is already in favorites
    $check_sql = "SELECT id FROM favorites WHERE date = '$date' AND user_id = $user_id";
    $result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($result) > 0) { // if item is already in favorites, show error message
        echo json_encode([
            'success' => false,
            'message' => 'This item is already in your favorites'
        ]);
        return;
    }
    
    //insert to db
    $sql = "INSERT INTO favorites (user_id, title, date, explanation, url, media_type) 
            VALUES ($user_id, '$title', '$date', '$explanation', '$url', '$media_type')";
    if (mysqli_query($conn, $sql)) { // if item is added to favorites successfully, show success message
        echo json_encode([
            'success' => true,
            'message' => 'Added to favorites successfully'
        ]);
    } else { // if item is not added to favorites, show error message
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . mysqli_error($conn)
        ]);
    }
}
// get all favorites from user
function getFavorites($conn){
    $user_id = $_SESSION["user_id"]; // get user id from session
    $sql = "SELECT * FROM favorites WHERE user_id = $user_id ORDER BY date DESC"; // get all favorites from user and order by date
    $result = mysqli_query($conn, $sql); // query database
    if (!$result){ // if query is not successful, show error message
        echo json_encode([
            "success" => false,
            "message" => "Database error: ".mysqli_error($conn)
        ]);
        return;
    }
    $favorites = []; // convert database results to an array
    while($row = mysqli_fetch_assoc($result)){
        $favorites[] = $row;
    }
    echo json_encode([ // return json response with success message and favorites data
        "success" => true,
        "data" => $favorites
    ]);
}
?>