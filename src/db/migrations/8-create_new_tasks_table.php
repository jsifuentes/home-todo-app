<?php

return function (SQLite3 $db) {
	$db->exec("CREATE TABLE tasks_temp (
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		title TEXT NOT NULL,
		body TEXT,
		sort_order INTEGER NOT NULL,
		status TEXT CHECK (status IN ('todo', 'in_progress', 'done')) DEFAULT 'todo',
		due_date DATETIME NOT NULL,
		due_date_increment_selected TEXT,
		category_id INTEGER,
		linked_to_recurring_id INTEGER,
		created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
		updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
	)");

	$db->exec("INSERT INTO tasks_temp (id, title, body, sort_order, due_date, status, category_id, linked_to_recurring_id, created_at, updated_at, due_date_increment_selected)
		SELECT id, title, body, sort_order, due_date, status, 0, null, created_at, updated_at, due_date_increment_selected FROM tasks");

	$db->exec("DROP TABLE tasks");
	$db->exec("ALTER TABLE tasks_temp RENAME TO tasks");
};
