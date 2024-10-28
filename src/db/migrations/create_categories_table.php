<?php

return function ($db) {
	$db->exec("CREATE TABLE IF NOT EXISTS categories (
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		name TEXT NOT NULL,
		is_default BOOLEAN DEFAULT FALSE,
		created_at DATETIME DEFAULT CURRENT_TIMESTAMP
	)");

	// add category to tasks
	$db->exec("ALTER TABLE tasks ADD COLUMN category_id INTEGER AFTER status");
};
