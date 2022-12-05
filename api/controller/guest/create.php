<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Access-Control-Allow-Methods, Content-Type, Authorization, X-Requested-With');

require_once '../../config/bootstrap.php';

$data = (array)json_decode(file_get_contents("php://input"), true);

if (count($data) < 6) {
    http_response_code(400);
    echo json_encode(["error" => ["message" => "Missing data to create a reservation."]]);
    die();
}

$frstname = strip_tags($data['firstname']);
$lstname = strip_tags($data['lastname']);
$email = strip_tags($data['email']);

$reg_str = "/^[a-žA-Ž \-']*$/";

if (!preg_match($reg_str, $frstname) || !preg_match($reg_str, $lstname)) {
    http_response_code(422);
    echo json_encode(["error" => ["message" => "Invalid name format."]]);
    die();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(["error" => ["message" => "Invalid format of e-mail address."]]);
    die();
}

// --- set database connection ---

$db = new Database(DB_HOST, DB_NAME, DB_USERNAME, DB_PASSWORD);

try {
    $conn = $db->getConnection();
} catch (PDOException $ex) {
    http_response_code(500);
    echo json_encode(["error" => ["message" => "Something went wrong."]]);
    die();
}

$guest = new Guest($conn);

// --- create guest ---

$guest->firstname = $frstname;
$guest->lastname = $lstname;
$guest->email = $email;
$guest->address = strip_tags($data['address']);
$guest->city = strip_tags($data['city']);
$guest->country = strip_tags($data['country']);

$guest_id = $guest->createWithAllAttributes();

if ($guest_id === false) {
    http_response_code(500);
    echo json_encode(["error" => ["message" => "Something went wrong."]]);
    die();
}

echo json_encode([
    "guest" => [
        "id" => $guest_id,
        "message" => "New guest was saved."
    ]
]);
