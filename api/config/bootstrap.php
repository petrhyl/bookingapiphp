<?php

define('ROOT_DIR',strip_tags($_SERVER['DOCUMENT_ROOT']));

require_once(ROOT_DIR.'/booking_api/api/config/dbConfig.php');
require_once(ROOT_DIR.'/booking_api/api/config/Database.php');
require_once(ROOT_DIR.'/booking_api/api/models/Reservation.php');
require_once(ROOT_DIR.'/booking_api/api/models/Room.php');
require_once(ROOT_DIR.'/booking_api/api/models/Type.php');
require_once(ROOT_DIR.'/booking_api/api/models/Guest.php');