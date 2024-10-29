<?php
require_once __DIR__ . '../../../init.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo 'Bad method';
	exit;
}

// Get the task data from the POST request
$taskIds = json_decode($_POST['task_ids_in_order'] ?? '[]') ?: [];

// Validate input
if (!$taskIds || empty($taskIds)) {
	http_response_code(412);
	echo renderError('Invalid task IDs');
	exit;
}

foreach ($taskIds as $index => $taskId) {
	$stmt = $db->prepare("UPDATE tasks SET sort_order = ? WHERE id = ?");
	$stmt->bindValue(1, $index + 1, SQLITE3_INTEGER);
	$stmt->bindValue(2, $taskId, SQLITE3_INTEGER);
	if (!$stmt->execute()) {
		throw new Exception("Failed to update task sort order: " . $db->lastErrorMsg());
	}
}


header('HX-Trigger: tasksUpdated');
echo renderSuccess('Sort order updated successfully');
