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
$dueDateIncrementSelected = $_POST['due_date'] ?? null;
$categoryId = $_POST['category_id'] ?? null;

// Add new recurring task fields
$isRecurring = isset($_POST['is_recurring']) ? 1 : 0;
$recurrenceAmount = $_POST['recurrence_amount'] ?? null;
$recurrenceUnit = $_POST['recurrence_unit'] ?? null;
$recurrenceDayOfWeek = $_POST['recurrence_day'] ?? null;
$recurrenceDayOfMonth = $_POST['recurrence_day_of_month'] ?? null;
$recurrenceMonth = $_POST['recurrence_month'] ?? null;

if (empty($title) || !is_numeric($sortOrder) || empty($dueDateIncrementSelected) || !is_numeric($categoryId)) {
	http_response_code(400);
	echo renderError("All fields are required.");
	exit;
}

if (!isset(DUE_DATE_LABELS[$dueDateIncrementSelected])) {
	http_response_code(400);
	echo renderError("Invalid due date.");
	exit;
}

// Validate recurring task fields if enabled
if ($isRecurring) {
	if (empty($recurrenceAmount) || !is_numeric($recurrenceAmount) || $recurrenceAmount < 1) {
		http_response_code(400);
		echo renderError("Invalid recurrence amount.");
		exit;
	}

	if (!in_array($recurrenceUnit, ['d', 'w', 'm', 'y'])) {
		http_response_code(400);
		echo renderError("Invalid recurrence unit.");
		exit;
	}

	// Validate specific unit requirements
	switch ($recurrenceUnit) {
		case 'w':
			if (empty($recurrenceDayOfWeek) || !is_numeric($recurrenceDayOfWeek) || $recurrenceDayOfWeek < 1 || $recurrenceDayOfWeek > 7) {
				http_response_code(400);
				echo renderError("Invalid day of week for weekly recurrence.");
				exit;
			}
			break;
		case 'm':
			if (empty($recurrenceDayOfMonth) || !is_numeric($recurrenceDayOfMonth) || $recurrenceDayOfMonth < 1 || $recurrenceDayOfMonth > 31) {
				http_response_code(400);
				echo renderError("Invalid day of month for monthly recurrence.");
				exit;
			}
			break;
		case 'y':
			if (empty($recurrenceMonth) || !is_numeric($recurrenceMonth) || $recurrenceMonth < 1 || $recurrenceMonth > 12) {
				http_response_code(400);
				echo renderError("Invalid month for yearly recurrence.");
				exit;
			}
			if (empty($recurrenceDayOfMonth) || !is_numeric($recurrenceDayOfMonth) || $recurrenceDayOfMonth < 1 || $recurrenceDayOfMonth > 31) {
				http_response_code(400);
				echo renderError("Invalid day of month for yearly recurrence.");
				exit;
			}
			// Verify the month/day combination is valid
			if (!checkdate($recurrenceMonth, $recurrenceDayOfMonth, date('Y'))) {
				http_response_code(400);
				echo renderError("Invalid date: " . $recurrenceDayOfMonth . "/" . $recurrenceMonth . " is not a valid day/month combination.");
				exit;
			}
			break;
	}
}

$db->exec('BEGIN');

try {
	$endDueDate = calculateEndDate($dueDateIncrementSelected);

	// If this is a recurring task, create the recurring configuration first
	$recurringTaskId = null;
	if ($isRecurring) {
		$recurringTask = new RecurringTask();
		$recurringTask->title = $title;
		$recurringTask->body = $description;
		$recurringTask->dueDateIncrementSelected = $dueDateIncrementSelected;
		$recurringTask->categoryId = $categoryId;
		$recurringTask->recurrenceAmount = $recurrenceAmount;
		$recurringTask->recurrenceUnit = $recurrenceUnit;
		$recurringTask->recurrenceDayOfWeek = $recurrenceUnit === 'w' ? $recurrenceDayOfWeek : null;
		$recurringTask->recurrenceDayOfMonth = ($recurrenceUnit === 'm' || $recurrenceUnit === 'y') ? $recurrenceDayOfMonth : null;
		$recurringTask->recurrenceMonth = $recurrenceUnit === 'y' ? $recurrenceMonth : null;

		$recurringTaskId = createRecurringTask($db, $recurringTask);
	}

	// Create the task using the new Task class and createTask function
	$task = new Task();
	$task->title = $title;
	$task->body = $description;
	$task->sortOrder = $sortOrder;
	$task->dueDate = $endDueDate->format('Y-m-d H:i:s');
	$task->dueDateIncrementSelected = $dueDateIncrementSelected;
	$task->categoryId = $categoryId;
	$task->linkedToRecurringId = $recurringTaskId;

	createTask($db, $task);

	$db->exec('COMMIT');

	header('HX-Trigger: taskCreated');
	echo renderSuccess("Task added successfully!");
} catch (Exception $e) {
	$db->exec('ROLLBACK');
	http_response_code(500);
	echo renderError($e->getMessage());
}
