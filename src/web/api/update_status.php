<?php
require_once '../components/error.php';
require_once '../../init.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	echo json_encode(['success' => false, 'error' => "Invalid request method. Please use POST."]);
	exit;
}

$taskId = $_POST['task_id'] ?? '';
$newStatus = $_POST['new_status'] ?? '';

if (empty($taskId) || empty($newStatus)) {
	http_response_code(400);
	sendSimpleErrorNotificationTrigger("Task ID and new status are required.");
	exit;
}

$stmt = $db->prepare("UPDATE tasks SET status = ?, sort_order = 0, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
$stmt->bindValue(1, $newStatus, SQLITE3_TEXT);
$stmt->bindValue(2, $taskId, SQLITE3_INTEGER);
$result = $stmt->execute();

if (!$result) {
	throw new Exception("Failed to update task status: " . $db->lastErrorMsg());
}

header('HX-Trigger: ' . json_encode([
	'taskStatusUpdated' => [],
	'addNotification' => [
		'type' => 'success',
		'message' => 'Task status updated successfully!',
	]
]));

