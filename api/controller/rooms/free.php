<?php

require_once '../../config/bootstrap.php';

header('Access-Control-Allow-Origin:*');
header('Content-Type:application/json');

if(!isset($_GET['from'])||!isset($_GET['to'])||!isset($_GET['id_type'])){
    http_response_code(400);
    echo json_encode(["error"=>["message"=>"Some parameter is missing"]]);
    die();
}

// --- validate params ---

$from=date_create(strip_tags($_GET['from']));
$to=date_create(strip_tags($_GET['to']));

if (!$from || !$to) {
    http_response_code(422);
    echo json_encode(["error"=>["message"=>"Invalid date format."]]);
    die();
}

if ($from>$to) {
    http_response_code(422);
    echo json_encode(["error"=>["message"=>"Date of arrival has to be earlier then date of leaving."]]);
    die();
}

$type_str=strip_tags($_GET['id_type']);

if (!filter_var($type_str,FILTER_VALIDATE_INT)) {
    http_response_code(422);
    echo json_encode(["error"=>["message"=>"Parameter 'id_type' is not valid."]]);
    die();
}

$type=intval($type_str);

// --- set database connection ---

$db= new Database(DB_HOST,DB_NAME,DB_USERNAME,DB_PASSWORD);

try {
    $conn = $db->getConnection();
} catch (PDOException $ex) {
    http_response_code(500);
    echo json_encode(["error" => ["message" => "Something went wrong."]]);
    die();
}

$room=new Room($conn);

// --- find free room ---

$data['room']=$room->findOneFreeByTypeIdAndDateInterval($from,$to,$type);

if ($data['room']===false) {
    http_response_code(500);
    echo json_encode(["error"=>["message"=>'No data. Something was wrong. Please contact help desk.']]);
    die();
}

echo json_encode($data);