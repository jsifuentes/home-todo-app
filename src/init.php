<?php

date_default_timezone_set('UTC');

define('DB_PATH', __DIR__ . '/../data/data.db');

// create the directory for DB_PATH if it doesn't exist
if (!file_exists(dirname(DB_PATH))) {
	mkdir(dirname(DB_PATH), 0777, true);
}

require_once __DIR__ . '/lib/settings/meta.php';
require_once __DIR__ . '/lib/settings/settings.php';
require_once __DIR__ . '/lib/settings/ValidatorException.php';
require_once __DIR__ . '/lib/time/helpers.php';
require_once __DIR__ . '/lib/categories/db.php';
require_once __DIR__ . '/lib/tasks/constants.php';
require_once __DIR__ . '/lib/tasks/due_date_string.php';
require_once __DIR__ . '/web/components/error.php';
require_once __DIR__ . '/web/components/success.php';

require_once __DIR__ . '/db/apply_changes.php';

$db = new SQLite3(DB_PATH);

$tvMode = isset($_GET['tv']);
$settings = mergeSettingsFromDatabaseWithDefaults();
