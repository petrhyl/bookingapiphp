<?php

define('ROOT_DIR',strip_tags($_SERVER['DOCUMENT_ROOT']));

require_once(ROOT_DIR.'/api/config/dbConfig.php');
require_once(ROOT_DIR.'/api/config/Database.php');
require_once(ROOT_DIR.'/api/models/Reservation.php');
require_once(ROOT_DIR.'/api/models/Room.php');
require_once(ROOT_DIR.'/api/models/Type.php');
require_once(ROOT_DIR.'/api/models/Guest.php');