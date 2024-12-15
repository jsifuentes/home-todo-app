<?php
require_once __DIR__ . '/../../init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	sendSimpleErrorNotificationTrigger("Invalid request method. Please use POST.");
	exit;
}

$taskId = $_POST['id'] ?? null;
$title = $_POST['title'] ?? null;
$body = $_POST['body'] ?? null;
$dueDateIncrementSelected = $_POST['due_date'] ?? null;
$categoryId = $_POST['category_id'] ?? null;
$recurrenceAmount = $_POST['recurrence_amount'] ?? null;
$recurrenceUnit = $_POST['recurrence_unit'] ?? null;
$recurrenceDayOfWeek = $_POST['recurrence_day'] ?? null;
$recurrenceDayOfMonth = $_POST['recurrence_day_of_month'] ?? null;
$recurrenceMonth = $_POST['recurrence_month'] ?? null;

// Validate required fields
if (!$taskId || !is_numeric($taskId)) {
	http_response_code(400);
	sendSimpleErrorNotificationTrigger("Task ID is required.");
	exit;
}

// Get existing task
$recurringTask = getRecurringTaskById($db, $taskId);
if (!$recurringTask) {
	http_response_code(404);
	sendSimpleErrorNotificationTrigger("Recurring task not found.");
	exit;
}

// Validate title if provided
if ($title !== null && empty(trim($title))) {
	http_response_code(400);
	sendSimpleErrorNotificationTrigger("Title cannot be empty.");
	exit;
}

// Validate recurrence fields if provided
if ($recurrenceAmount !== null) {
	if (!is_numeric($recurrenceAmount) || $recurrenceAmount < 1) {
		http_response_code(400);
		sendSimpleErrorNotificationTrigger("Invalid recurrence amount.");
		exit;
	}
}

if ($recurrenceUnit !== null) {
	if (!in_array($recurrenceUnit, ['d', 'w', 'm', 'y'])) {
		http_response_code(400);
		sendSimpleErrorNotificationTrigger("Invalid recurrence unit.");
		exit;
	}

	// Validate specific unit requirements
	switch ($recurrenceUnit) {
		case 'w':
			if (empty($recurrenceDayOfWeek) || !is_numeric($recurrenceDayOfWeek) || $recurrenceDayOfWeek < 1 || $recurrenceDayOfWeek > 7) {
				http_response_code(400);
				sendSimpleErrorNotificationTrigger("Invalid day of week for weekly recurrence.");
				exit;
			}
			break;
		case 'm':
			if (empty($recurrenceDayOfMonth) || !is_numeric($recurrenceDayOfMonth) || $recurrenceDayOfMonth < 1 || $recurrenceDayOfMonth > 31) {
				http_response_code(400);
				sendSimpleErrorNotificationTrigger("Invalid day of month for monthly recurrence.");
				exit;
			}
			break;
		case 'y':
			if (empty($recurrenceMonth) || !is_numeric($recurrenceMonth) || $recurrenceMonth < 1 || $recurrenceMonth > 12) {
				http_response_code(400);
				sendSimpleErrorNotificationTrigger("Invalid month for yearly recurrence.");
				exit;
			}
			if (empty($recurrenceDayOfMonth) || !is_numeric($recurrenceDayOfMonth) || $recurrenceDayOfMonth < 1 || $recurrenceDayOfMonth > 31) {
				http_response_code(400);
				sendSimpleErrorNotificationTrigger("Invalid day of month for yearly recurrence.");
				exit;
			}
			// Verify the month/day combination is valid
			if (!checkdate($recurrenceMonth, $recurrenceDayOfMonth, date('Y'))) {
				http_response_code(400);
				sendSimpleErrorNotificationTrigger("Invalid date: " . $recurrenceDayOfMonth . "/" . $recurrenceMonth . " is not a valid day/month combination.");
				exit;
			}
			break;
	}
}

$db->exec('BEGIN');

try {
	// Build update query
	$query = "UPDATE recurring_tasks SET updated_at = CURRENT_TIMESTAMP";
	$params = [];

	if ($title !== null) {
		$query .= ", title = :title";
		$params[':title'] = $title;
	}

	if ($body !== null) {
		$query .= ", body = :body";
		$params[':body'] = $body;
	}

	if ($dueDateIncrementSelected !== null) {
		$query .= ", due_date_increment_selected = :due_date";
		$params[':due_date'] = $dueDateIncrementSelected;
	}

	if ($categoryId !== null) {
		$query .= ", category_id = :category_id";
		$params[':category_id'] = $categoryId;
	}

	if ($recurrenceAmount !== null) {
		$query .= ", recurrence_amount = :recurrence_amount";
		$params[':recurrence_amount'] = $recurrenceAmount;
	}

	if ($recurrenceUnit !== null) {
		$query .= ", recurrence_unit = :recurrence_unit";
		$params[':recurrence_unit'] = $recurrenceUnit;

		// Update related fields based on unit
		$query .= ", recurrence_day_of_week = :day_of_week";
		$query .= ", recurrence_day_of_month = :day_of_month";
		$query .= ", recurrence_month = :month";

		$params[':day_of_week'] = $recurrenceUnit === 'w' ? $recurrenceDayOfWeek : null;
		$params[':day_of_month'] = ($recurrenceUnit === 'm' || $recurrenceUnit === 'y') ? $recurrenceDayOfMonth : null;
		$params[':month'] = $recurrenceUnit === 'y' ? $recurrenceMonth : null;
	}

	$query .= " WHERE id = :id";
	$params[':id'] = $taskId;

	$stmt = $db->prepare($query);
	foreach ($params as $param => $value) {
		$stmt->bindValue($param, $value);
	}

	if (!$stmt->execute()) {
		throw new Exception("Failed to update recurring task: " . $db->lastErrorMsg());
	}

	$db->exec('COMMIT');

	header('HX-Trigger: ' . json_encode([
		'recurringTasksUpdated' => [],
		'addNotification' => [
			'type' => 'success',
			'message' => 'Recurring task updated successfully!',
		]
	]));
} catch (Exception $e) {
	$db->exec('ROLLBACK');
	http_response_code(500);
	sendSimpleErrorNotificationTrigger($e->getMessage());
	echo $e->getMessage();
}
