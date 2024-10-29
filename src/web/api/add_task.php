<?php
require_once '../../init.php';

header('Content-Type: text/html; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	die(renderError("Invalid request method. Please use POST."));
}

$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$sortOrder = $_POST['sort_order'] ?? 0;
// $priority = $_POST['priority'] ?? 3;
$dueDate = $_POST['due_date'] ?? null;
$categoryId = $_POST['category_id'] ?? null;

if (empty($title) || !is_numeric($sortOrder) || empty($dueDate) || !is_numeric($categoryId)) {
	http_response_code(400);
	echo renderError("All fields are required.");
	exit;
}

if (!isset(DUE_DATE_LABELS[$dueDate])) {
	http_response_code(400);
	echo renderError("Invalid due date.");
	exit;
}

$endDueDate = calculateEndDate($dueDate);

$stmt = $db->prepare("INSERT INTO tasks (title, body, sort_order, due_date, category_id) VALUES (?, ?, ?, ?, ?)");
$stmt->bindValue(1, $title);
$stmt->bindValue(2, $description);
$stmt->bindValue(3, $sortOrder);
$stmt->bindValue(4, $endDueDate->format('Y-m-d H:i:s'));
$stmt->bindValue(5, $categoryId);
$stmt->execute();

header('HX-Trigger: taskCreated');

echo renderSuccess("Task added successfully!");
