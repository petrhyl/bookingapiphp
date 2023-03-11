<?php

header('Access-Control-Allow-Origin:*');
header('Content-Type:application/json');

require_once '../../config/bootstrap.php';

// * * * * * * * *
// api example: /controller/roomtypes/free.php?from=2022-10-01&to=23-10-2022&persons=2
// * * * * * * * *
// api response example: 
// {
//     "rooms": [
//         {
//             "ID_type": 2,
//             "beds_number": 2,
//             "double_bed": false,
//             "business": false,
//             "name": "Dvě lůžka - standardní výbava",
//             "description": "Pro dvě osoby. Standardní výbava.",
//             "picture": "path",
//             "numberOfAvailable": 1
//         },
//         {
//             "ID_type": 3,
//             "beds_number": 2,
//             "double_bed": true,
//             "business": true,
//             "name": "Dvě lůžka - business class",
//             "description": "Pro dvě osoby. Business class.",
//             "picture": "https://hyl-petr.xf.cz/images/fourbeds_business.jpg",
//             "numberOfAvailable": 2
//         }
//     ]
// }

if (!isset($_GET['from']) || !isset($_GET['to']) || !isset($_GET['persons'])) {
    http_response_code(400);
    echo json_encode(["error" => ["message" => "Some parameter is missing"]]);
    die();
}

// --- validate params ---

$from = date_create(strip_tags($_GET['from']));
$to = date_create(strip_tags($_GET['to']));

if (!$from || !$to) {
    http_response_code(400);
    echo json_encode(["error" => ["message" => "Invalid date format."]]);
    die();
}

if ($from > $to) {
    http_response_code(422);
    echo json_encode(["error" => ["message" => "Date of arrival has to be earlier then date of leaving."]]);
    die();
}

$person_number_str = strip_tags($_GET['persons']);

if (!filter_var($person_number_str, FILTER_VALIDATE_INT)) {
    http_response_code(422);
    echo json_encode(["error" => ["message" => "Parameter 'persons' is not a number."]]);
    die();
}

$person_number = intval($person_number_str);

// --- set database connection ---

$db = new Database(DB_HOST, DB_NAME, DB_USERNAME, DB_PASSWORD);

try {
    $conn = $db->getConnection();
} catch (PDOException $ex) {
    http_response_code(500);
    echo json_encode(["error" => ["message" => "Something went wrong. Cannto connect to database."]]);
    die();
}

$type = new Type($conn);

// --- find free types of rooms ---

$data['rooms'] = $type->findFreeTypesByDateIntervalAndMinBedsNumber(
    $from,
    $to,
    $person_number
);

if ($data['rooms'] === false) {
    http_response_code(500);
    echo json_encode(["error" => ["message" => 'No data. Something was wrong. Please contact help desk.']]);
    die();
}

echo json_encode($data);
