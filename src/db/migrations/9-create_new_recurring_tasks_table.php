<?php

return function (SQLite3 $db) {
	$db->exec("CREATE TABLE recurring_tasks (
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		title TEXT NOT NULL,
		body TEXT,
		due_date_increment_selected TEXT,
		category_id INTEGER,
		is_active BOOLEAN DEFAULT 1,
		recurrence_amount INTEGER NOT NULL,
		recurrence_unit TEXT NOT NULL,
		recurrence_day_of_week INTEGER,
		recurrence_day_of_month INTEGER,
		recurrence_month INTEGER,
		last_generated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
		created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
		updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
	)");
};
