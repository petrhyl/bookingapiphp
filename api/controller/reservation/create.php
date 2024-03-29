<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST,PUT');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Access-Control-Allow-Methods, Content-Type, Authorization, X-Requested-With');

require_once '../../config/bootstrap.php';

/* 
*** api example: ***
/controller/reservation/create.php 
body:
{
    "guest":{
        "firstname":"Pavel",
        "lastname":"Havel",
        "email":"hp@nl.com"
    },
    "from":"2022-10-20",
    "to":"2022-10-23",
   "id_type":4
}

*** successful response example: ***
{
    "reservation": {
        "id": 6,
        "message": "Reservation was created."
    }
}
 */ 

$data = (array)json_decode(file_get_contents("php://input"), true);

if (count($data) < 4) {
    http_response_code(400);
    echo json_encode(["error" => ["message" => "Missing data to create a reservation."]]);
    die();
}

// --- validate params ---

$frstname=strip_tags($data['guest']['firstname']);
$lstname = strip_tags($data['guest']['lastname']);
$email=strip_tags($data['guest']['email']);

$reg_str="/^[a-žA-Ž \-']*$/";

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

$from = date_create(strip_tags($data['from']));
$to = date_create(strip_tags($data['to']));
$from->add(new DateInterval('PT20H30M')); // format pridani hodin a minut viz. dokumentace php
$currentDate=new DateTime();

if ($from === false || $to === false) {
    http_response_code(422);
    echo json_encode(["error" => ["message" => "Invalid date format."]]);
    die();
}

if ($from < $currentDate) {
    http_response_code(422);
    echo json_encode(["error" => ["message" => "Date of arrival has to be later."]]);
    die();
}

if ($from >= $to) {
    http_response_code(422);
    echo json_encode(["error" => ["message" => "Date of arrival has to be earlier then date of leaving."]]);
    die();
}

$type_id_str = strip_tags($data['id_type']);

if (!filter_var($type_id_str, FILTER_VALIDATE_INT)) {
    http_response_code(422);
    echo json_encode(["error" => ["message" => "Type of room is not a valid number."]]);
    die();
}

$type_id = intval($type_id_str);


// --- set database connection ---

$db = new Database(DB_HOST, DB_NAME, DB_USERNAME, DB_PASSWORD);

try {
    $conn = $db->getConnection();
} catch (PDOException $ex) {
    http_response_code(500);
    echo json_encode(["error" => ["message" => "Something went wrong. Cannot connect to database."]]);
    die();
}

$reservation = new Reservation($conn);

$guest = new Guest($conn);

$room = new Room($conn);

// --- detabase connection is set

$found_room = $room->findOneFreeByTypeIdAndDateInterval($from, $to, $type_id);

if ($found_room === false) {
    http_response_code(500);
    echo json_encode(["error" => ["message" => "Something went wrong. No such a room available."]]);
    die();
}

// --- create guest ---

$guest->firstname = $frstname;
$guest->lastname=$lstname;
$guest->email = $email;

$guest_id = $guest->createWithNamesAndEmailAttributes();

if ($guest_id === false) {
    http_response_code(500);
    echo json_encode(["error" => ["message" => "Something went wrong. Cannot save client's data."]]);
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
    echo json_encode(["error" => ["message" => "Something went wrong. Cannot create reservation."]]);
    die();
}

http_response_code(201);
echo json_encode([
    "reservation" => [
        "id" => $result,
        "message" => "Reservation was created."
    ]
]);
