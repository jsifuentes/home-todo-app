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
$taskId = $_POST['task_id'] ?? null;
$title = $_POST['title'] ?? null;
$body = $_POST['body'] ?? null;
$status = $_POST['status'] ?? null;
$sortOrder = $_POST['sort_order'] ?? null;
$categoryId = $_POST['category_id'] ?? null;
$addTimeToDueDate = $_POST['add_to_due_date'] ?? null;

// Validate input
if (!$taskId || !is_numeric($taskId)) {
	http_response_code(412);
	sendSimpleErrorNotificationTrigger('Invalid task ID');
	exit;
}

// Check if sortOrder is set and is numeric
if ($sortOrder !== null && !is_numeric($sortOrder)) {
	http_response_code(400);
	sendSimpleErrorNotificationTrigger('Invalid new index');
	exit;
}

// Check if title is not empty if set
if ($title !== null && empty($title)) {
	http_response_code(400);
	sendSimpleErrorNotificationTrigger('Title cannot be empty');
	exit;
}

// Check if the task exists
$stmt = $db->prepare("SELECT * FROM tasks WHERE id = :taskId");
$stmt->bindValue(':taskId', $taskId);
$result = $stmt->execute();
$taskRow = $result->fetchArray(SQLITE3_ASSOC);
if (!$taskRow) {
	http_response_code(400);
	sendSimpleErrorNotificationTrigger('Task not found');
	exit;
}

// Check if categoryId is set and is numeric and is an existing category in the database
$shouldUpdateCategory = $categoryId !== null;
if ($shouldUpdateCategory) {
	if (is_numeric($categoryId)) {
		$stmt = $db->prepare("SELECT id FROM categories WHERE id = :categoryId");
		$stmt->bindValue(':categoryId', $categoryId);
		$result = $stmt->execute();
		if (!$result) {
			http_response_code(400);
			sendSimpleErrorNotificationTrigger('Invalid category ID');
			exit;
		}
	} else {
		$categoryId = null;
	}
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

if ($shouldUpdateCategory) {
	$query .= ", category_id = :category_id";
	$params[':category_id'] = $categoryId;
}

if ($addTimeToDueDate !== null) {
	$query .= ", due_date = :due_date";
	$params[':due_date'] = calculateEndDate($addTimeToDueDate, new DateTime($taskRow['due_date']))->format('Y-m-d H:i:s');
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

header('HX-Trigger: ' . json_encode([
	'tasksUpdated' => [
		'taskId' => $taskId,
		'keepOpen' => $addTimeToDueDate !== null ? $taskId : null,
	],
	'addNotification' => [
		'type' => 'success',
		'message' => 'Task updated successfully',
	]
]));
