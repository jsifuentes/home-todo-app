<?php
require_once '../../init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
	http_response_code(405);
	echo renderError("Invalid request method. Please use DELETE.");
	exit;
}

$taskId = $_REQUEST['task_id'] ?? '';

if (empty($taskId) || !is_numeric($taskId)) {
	http_response_code(400);
	echo renderError("Invalid task ID.");
	exit;
}

$stmt = $db->prepare("DELETE FROM tasks WHERE id = ?");
$stmt->bindValue(1, $taskId, SQLITE3_INTEGER);
$result = $stmt->execute();

if (!$result) {
	throw new Exception("Failed to delete task: " . $db->lastErrorMsg());
}

header('HX-Trigger: tasksUpdated');
echo renderSuccess("Task deleted successfully.");
