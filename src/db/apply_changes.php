<?php


if (!file_exists(DB_PATH)) {
	$db = new SQLite3(DB_PATH);

	// Array of CREATE TABLE queries
	$tables = [
		'migrations' => "CREATE TABLE IF NOT EXISTS migrations (
			file VARCHAR PRIMARY KEY,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP
		)",

		'tasks' => "CREATE TABLE IF NOT EXISTS tasks (
			id INTEGER PRIMARY KEY AUTOINCREMENT,
			title TEXT NOT NULL,
			body TEXT,
			sort_order INTEGER,
			due_date DATETIME,
			status TEXT CHECK (status IN ('todo', 'in_progress', 'done')) DEFAULT 'todo',
			created_by_recurring BOOLEAN DEFAULT 0,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
		)",

		"settings" => "CREATE TABLE IF NOT EXISTS settings (
			key TEXT PRIMARY KEY,
			value TEXT
		)",
	];

	// Loop through the tables and create them
	foreach ($tables as $tableName => $query) {
		if (!$db->exec($query)) {
			die("Error creating $tableName table: " . $db->lastErrorMsg());
		}
	}
}

$db = new SQLite3(DB_PATH);

// Check for migration updates
$migrations = glob(__DIR__ . '/migrations/*.php');
// get list of all migrations previously run
$previousMigrations = $db->query("SELECT file FROM migrations")->fetchArray(SQLITE3_ASSOC) ?: [];

foreach ($migrations as $migration) {
	// check if already run
	if (in_array(basename($migration), $previousMigrations)) {
		continue;
	}

	$migrationFunction = include $migration;
	$migrationFunction($db);

	$db->exec("INSERT INTO migrations (file) VALUES ('" . basename($migration) . "')");
}
