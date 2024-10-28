<?php

define('DB_PATH', __DIR__ . '/../data/data.db');

// create the directory for DB_PATH if it doesn't exist
if (!file_exists(dirname(DB_PATH))) {
	mkdir(dirname(DB_PATH), 0777, true);
}

require_once __DIR__ . '/db/apply_changes.php';
require_once __DIR__ . '/helpers/time.php';
require_once __DIR__ . '/helpers/categories.php';
require_once __DIR__ . '/web/components/task-constants.php';
require_once __DIR__ . '/web/components/error.php';
require_once __DIR__ . '/web/components/success.php';
$db = new SQLite3(DB_PATH);
