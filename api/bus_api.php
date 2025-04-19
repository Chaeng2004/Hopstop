<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

include 'database.php';
include_once '../class/Bus.php';

$database = new Database();
$db = $database->getConnection();
$bus = new Bus($db);


if (!$db) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

$inputData = null;
if ($method === 'POST' || $method === 'PUT') {
    $inputJSON = file_get_contents('php://input');
    $inputData = json_decode($inputJSON, true);

    if ($inputData === null && json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(["status" => "error", "message" => "Invalid JSON: " . json_last_error_msg()]);
        exit;
    }
}

switch ($method) {
    case 'GET':
        $filters = [];
        if (isset($_GET['location'])) $filters['location'] = $_GET['location'];
        if (isset($_GET['destination'])) $filters['destination'] = $_GET['destination'];
        if (isset($_GET['bus_type'])) $filters['bus_type'] = $_GET['bus_type'];

        if (isset($_GET['id'])) {
            $busData = $bus->getBusById($_GET['id']);
            if ($busData) {
                echo json_encode(["status" => "success", "bus" => $busData]);
            } else {
                echo json_encode(["status" => "error", "message" => "Bus not found"]);
            }
            exit;
        } else {
            $result = empty($filters) ? $bus->getAllBusDetails() : $bus->getFilteredBuses($filters);
            echo json_encode(["status" => "success", "buses" => $result]);
            exit;
        }

    case 'POST':
        if (!isset($inputData['id'], $inputData['location'], $inputData['destination'], $inputData['departure_time'], 
                  $inputData['arrival_time'], $inputData['bus_type'], $inputData['price'], 
                  $inputData['available_seats'], $inputData['status'])) {
            echo json_encode(["status" => "error", "message" => "Missing required fields"]);
            exit;
        }

        $result = $bus->createNewBus(
            $inputData['id'],
            $inputData['location'],
            $inputData['destination'],
            $inputData['departure_time'],
            $inputData['arrival_time'],
            $inputData['bus_type'],
            $inputData['price'],
            $inputData['available_seats'],
            $inputData['status']
        );

        if ($result) {
            echo json_encode(["status" => "success", "message" => "Bus created successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to create bus"]);
        }
        exit;

    case 'PUT':
        if (!isset($inputData['id'], $inputData['location'], $inputData['destination'], 
                   $inputData['departure_time'], $inputData['arrival_time'], $inputData['bus_type'], 
                   $inputData['price'], $inputData['available_seats'], $inputData['status'])) {
            echo json_encode(["status" => "error", "message" => "Missing required fields for update"]);
            exit;
        }

        $result = $bus->updateBus(
            $inputData['id'],
            $inputData['location'],
            $inputData['destination'],
            $inputData['departure_time'],
            $inputData['arrival_time'],
            $inputData['bus_type'],
            $inputData['price'],
            $inputData['available_seats'],
            $inputData['status'],
        );

        if ($result) {
            echo json_encode(["status" => "success", "message" => "Bus updated successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update bus"]);
        }
        exit;

    case 'DELETE':
        if (!isset($_GET['id'])) {
            echo json_encode(["status" => "error", "message" => "Bus ID is required for deletion"]);
            exit;
        }

        $result = $bus->deleteBus($_GET['id']);
        if ($result) {
            echo json_encode(["status" => "success", "message" => "Bus deleted successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to delete bus"]);
        }
        exit;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid request method"]);
        exit;
}
?>
