<?php
require_once '../../init.php';

header('Content-Type: text/html; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	die(renderError("Invalid request method. Please use POST."));
}

$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$sortOrder = $_POST['sort_order'] ?? 1;
// $priority = $_POST['priority'] ?? 3;
$dueDate = $_POST['due_date'] ?? null;

if (empty($title) || empty($sortOrder) || !is_numeric($sortOrder) || empty($dueDate)) {
	http_response_code(400);
	echo renderError("All fields are required.");
	exit;
}

if (!isset(DUE_DATE_LABELS[$dueDate])) {
	http_response_code(400);
	echo renderError("Invalid due date.");
	exit;
}

// Calculate the due dates
$endDueDate = new DateTime();

$lastChar = substr($dueDate, -1);
$amount = intval(substr($dueDate, 0, -1));

switch ($lastChar) {
	case 'd':
		$endDueDate->modify("+$amount days");
		break;
	case 'm':
		$endDueDate->modify("+$amount months");
		break;
	case 'y':
		$endDueDate->modify("+$amount years");
		break;
	default:
		http_response_code(400);
		echo renderError("Invalid due date format.");
		exit;
}

$stmt = $db->prepare("INSERT INTO tasks (title, body, sort_order, due_date) VALUES (?, ?, ?, ?)");
$stmt->bindValue(1, $title);
$stmt->bindValue(2, $description);
$stmt->bindValue(3, $sortOrder);
$stmt->bindValue(4, $endDueDate->format('Y-m-d H:i:s'));
$stmt->execute();

header('HX-Trigger: refreshTasks');

echo renderSuccess("Task added successfully!");
