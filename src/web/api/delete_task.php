<?php
require_once '../../init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
	http_response_code(405);
	sendSimpleErrorNotificationTrigger("Invalid request method. Please use DELETE.");
	exit;
}

$taskId = $_REQUEST['task_id'] ?? '';

if (empty($taskId) || !is_numeric($taskId)) {
	http_response_code(400);
	sendSimpleErrorNotificationTrigger("Invalid task ID.");
	exit;
}

try {
	$stmt = $db->prepare("DELETE FROM tasks WHERE id = ?");
	$stmt->bindValue(1, $taskId, SQLITE3_INTEGER);
	$result = $stmt->execute();

	if (!$result) {
		throw new Exception("Error from db: " . $db->lastErrorMsg());
	}

	header('HX-Trigger: ' . json_encode([
		'tasksUpdated' => [],
		'addNotification' => [
			'type' => 'success',
			'message' => 'Task deleted successfully!',
		]
	]));
} catch (\Exception $e) {
	http_response_code(500);
	sendSimpleErrorNotificationTrigger("Failed to delete task: " . $e->getMessage());
	echo $e->getMessage();
}
