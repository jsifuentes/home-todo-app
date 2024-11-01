<?php

return function (SQLite3 $db) {
	$settings = mergeSettingsFromDatabaseWithDefaults();

	// Adjust all the times in the tasks table so that it's midnight for the local time, but still UTC in the database.
	$tasks = $db->query("SELECT * FROM tasks");

	while ($task = $tasks->fetchArray(SQLITE3_ASSOC)) {
		$dueDate = new DateTime($task['due_date'], new DateTimeZone($settings['timezone']));
		$dueDate->setTime(0, 0, 0);
		$dueDate->setTimezone(new DateTimeZone('UTC'));
		$endDueDate = $dueDate->format('Y-m-d H:i:s');
		$db->exec("UPDATE tasks SET due_date = '$endDueDate' WHERE id = " . $task['id']);
	}
};
