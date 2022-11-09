<?php

define('ROOT_DIR',strip_tags($_SERVER['DOCUMENT_ROOT']));

require_once(ROOT_DIR.'/config/dbConfig.php');
require_once(ROOT_DIR.'/config/Database.php');
require_once(ROOT_DIR.'/models/Reservation.php');
require_once(ROOT_DIR.'/models/Room.php');
require_once(ROOT_DIR.'/models/Type.php');
require_once(ROOT_DIR.'/models/Guest.php');