<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Access-Control-Allow-Methods, Content-Type, Authorization, X-Requested-With');

require_once '../../config/bootstrap.php';

// api example: /controller/reservation/create.php
// body:
// {
//     "guest":{
//         "firstname":"Pavel",
//         "lastname":"Havel",
//         "email":"hp@.com"
//     },
//     "from":"2022-10-20",
//     "to":"2022-10-23",
//     "type_id":4
// }

$data = (array)json_decode(file_get_contents("php://input"), true);

if (count($data) < 4) {
    http_response_code(400);
    echo json_encode(["error" => ["message" => "Missing data to create a reservation."]]);
    die();
}

// --- validate guest's attributes ---

$frstname=strip_tags($data['guest']['firstname']);
$lstname = strip_tags($data['guest']['lastname']);
$email=strip_tags($data['guest']['email']);

$reg_str="/^[a-zA-Z-' ]*$/";

if (!preg_match($reg_str,$frstname)||!preg_match($reg_str,$lstname)) {
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

$reservation = new Reservation($conn);

$guest = new Guest($conn);

$room = new Room($conn);

// --- detabase connection is set

// --- create guest ---

$guest->firstname = $frstname;
$guest->lastname=$lstname;
$guest->email = $email;

$guest_id = $guest->createWithNamesAndEmailAttributes();

if ($guest_id === false) {
    http_response_code(500);
    echo json_encode(["error" => ["message" => "Something went wrong."]]);
    die();
}

// --- validate other params ---

$from = date_create(strip_tags($data['from']));
$to = date_create(strip_tags($data['to']));
$currentDate=new DateTime();
$currentDate->setTime(1,0);

if ($from === false || $to === false) {
    http_response_code(422);
    echo json_encode(["error" => ["message" => "Invalid date format."]]);
    die();
}

if ($from > $to) {
    http_response_code(422);
    echo json_encode(["error" => ["message" => "Date of arrival has to be earlier then date of leaving."]]);
    die();
}

if ($from <= $currentDate) {
    http_response_code(422);
    echo json_encode(["error" => ["message" => "Date of arrival has to be later."]]);
    die();
}

$type_id_str = strip_tags($data['type_id']);

if (!is_numeric($type_id_str)) {
    http_response_code(422);
    echo json_encode(["error" => ["message" => "Parameter 'persons' is not a number."]]);
    die();
}

$type_id = intval($type_id_str);

$found_room = $room->findOneFreeByTypeIdAndDateInterval($from, $to, $type_id);

if ($found_room === false) {
    http_response_code(500);
    echo json_encode(["error" => ["message" => "Something went wrong."]]);
    die();
}

// --- create reservation with guest's id ---

$reservation->date_from = $from;
$reservation->date_to = $to;
$reservation->ID_room = $found_room->ID_room;
$reservation->ID_guest = $guest_id;

$result = $reservation->createNew();

if ($result === false) {
    http_response_code(500);
    echo json_encode(["error" => ["message" => "Something went wrong."]]);
    die();
}

echo json_encode([
    "reservation" => [
        "id" => $result,
        "message" => "Reservation was created."
    ]
]);
