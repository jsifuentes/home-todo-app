<?php
require_once __DIR__ . '../../../init.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo 'Bad method';
	exit;
}

// Get the task data from the POST request
$taskId = $_POST['taskId'] ?? null;
$title = $_POST['title'] ?? null;
$body = $_POST['body'] ?? null;
$sortOrder = $_POST['sort_order'] ?? null;

// Validate input
if (!$taskId || !is_numeric($taskId)) {
	http_response_code(412);
	echo renderError('Invalid task ID');
	exit;
}

// Check if sortOrder is set and is numeric
if ($sortOrder !== null && !is_numeric($sortOrder)) {
	http_response_code(400);
	echo renderError('Invalid new index');
	exit;
}

// Check if title is not empty if set
if ($title !== null && empty($title)) {
	http_response_code(400);
	echo renderError('Title cannot be empty');
	exit;
}

// Prepare and execute the update query
$query = "UPDATE tasks SET updated_at = CURRENT_TIMESTAMP";
$params = [];

if ($title !== null) {
	$query .= ", title = :title";
	$params[':title'] = $title;
}

if ($body !== null) {
	$query .= ", body = :body";
	$params[':body'] = $body;
}

if ($sortOrder !== null) {
	$query .= ", sort_order = :sort_order";
	$params[':sort_order'] = $sortOrder;
}

$query .= " WHERE id = :id";
$params[':id'] = $taskId;

$stmt = $db->prepare($query);

foreach ($params as $param => $value) {
	$stmt->bindValue($param, $value);
}

$result = $stmt->execute();

if (!$result) {
	throw new Exception("Failed to update task: " . $db->lastErrorMsg());
}

header('HX-Trigger: refreshTasks');
echo renderSuccess('Task updated successfully');
