<?php

    header("Content-Type: application/json");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
    header("Access-Control-Allow-Headers: Content-Type");

    include "database.php";
    include "../class/Passenger.php";

    $database = new Database();
    $db = $database->getConnection();

    $passenger = new Passenger($db);

    $method = $_SERVER["REQUEST_METHOD"];

    switch ($method){
        case "POST":
            $postData = file_get_contents("php://input");
            $data = json_decode($postData, true);

            $passenger->addUser($data["firstName"], $data["lastName"], $data["email"], $data["password"]);
            echo json_encode(["status" => "success"]);
            break;
            
}
?>