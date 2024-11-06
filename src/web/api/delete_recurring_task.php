<?php
require_once '../../init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
	http_response_code(405);
	die(renderError("Invalid request method. Please use DELETE."));
}

$taskId = $_GET['taskId'] ?? null;

if (!$taskId || !is_numeric($taskId)) {
	http_response_code(400);
	die(renderError("Invalid task ID."));
}

$db->exec('BEGIN');

try {
	// Delete the recurring task
	$stmt = $db->prepare("DELETE FROM recurring_tasks WHERE id = :id");
	$stmt->bindValue(':id', $taskId);
	$stmt->execute();

	// Note: We're not deleting the existing tasks that were created from this recurring task

	$db->exec('COMMIT');

	header('HX-Trigger: recurringTasksUpdated');
	echo renderSuccess("Recurring task deleted successfully!");
} catch (Exception $e) {
	$db->exec('ROLLBACK');
	http_response_code(500);
	echo renderError($e->getMessage());
}
