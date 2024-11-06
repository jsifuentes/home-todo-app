<?php

require_once __DIR__ . '/../init.php';

$categories = [
	['id' => 1, 'name' => 'Chores', 'is_default' => true],
	['id' => 2, 'name' => 'Shopping', 'is_default' => false],
];

$tasks = [
	['id' => 1, 'title' => 'Mop the floor', 'body' => '', 'sort_order' => 1, 'due_date' => '7d', 'status' => 'todo', 'category_id' => 1],
	['id' => 2, 'title' => 'Milk', 'body' => '', 'sort_order' => 2, 'due_date' => '7d', 'status' => 'todo', 'category_id' => 2],
	['id' => 3, 'title' => 'Clean the hair out of the shower drains', 'body' => '', 'sort_order' => 3, 'due_date' => '1m', 'status' => 'todo', 'category_id' => 1],
	['id' => 4, 'title' => 'Toilet paper', 'body' => '', 'sort_order' => 4, 'due_date' => '7d', 'status' => 'todo', 'category_id' => 2],
	['id' => 5, 'title' => 'Paper towels', 'body' => '', 'sort_order' => 5, 'due_date' => '7d', 'status' => 'todo', 'category_id' => 2],
	['id' => 6, 'title' => 'Dish soap', 'body' => '', 'sort_order' => 6, 'due_date' => '1d', 'status' => 'todo', 'category_id' => 2],
	['id' => 7, 'title' => 'Clean the bathroom floor', 'body' => '', 'sort_order' => 7, 'due_date' => '7d', 'status' => 'todo', 'category_id' => 1],
	['id' => 8, 'title' => 'Couch', 'body' => '', 'sort_order' => 8, 'due_date' => '1d', 'status' => 'todo', 'category_id' => 2],
];

// Insert categories
foreach ($categories as $category) {
	$stmt = $db->prepare('INSERT INTO categories (id, name, is_default) VALUES (:id, :name, :is_default)');
	$stmt->bindValue(':id', $category['id'], SQLITE3_INTEGER);
	$stmt->bindValue(':name', $category['name'], SQLITE3_TEXT);
	$stmt->bindValue(':is_default', $category['is_default'], SQLITE3_INTEGER);
	$stmt->execute();
}

// Insert tasks using createTask helper
foreach ($tasks as $task) {
	$endDueDate = calculateEndDate($task['due_date']);

	$taskObj = new Task();
	$taskObj->title = $task['title'];
	$taskObj->body = $task['body'];
	$taskObj->sortOrder = $task['sort_order'];
	$taskObj->dueDate = $endDueDate->format('Y-m-d H:i:s');
	$taskObj->dueDateIncrementSelected = $task['due_date'];
	$taskObj->categoryId = $task['category_id'];

	if (!createTask($db, $taskObj)) {
		echo "Error inserting task: " . $db->lastErrorMsg() . "\n";
	} else {
		echo "Inserted task: " . $task['title'] . "\n";
	}
}

// Create recurring tasks
$recurring_tasks = [
	[
		'title' => 'Take out trash',
		'body' => '',
		'due_date_increment_selected' => '1d',
		'category_id' => 1,
		'recurrence_amount' => 1,
		'recurrence_unit' => 'w',
		'recurrence_day_of_week' => 2, // Tuesday
		'recurrence_day_of_month' => null,
		'recurrence_month' => null
	],
	[
		'title' => 'Clean kitchen',
		'body' => 'Deep clean all surfaces',
		'due_date_increment_selected' => '1d',
		'category_id' => 1,
		'recurrence_amount' => 2,
		'recurrence_unit' => 'w',
		'recurrence_day_of_week' => 6, // Saturday
		'recurrence_day_of_month' => null,
		'recurrence_month' => null
	],
	[
		'title' => 'Pay rent',
		'body' => '',
		'due_date_increment_selected' => '1d',
		'category_id' => 1,
		'recurrence_amount' => 1,
		'recurrence_unit' => 'm',
		'recurrence_day_of_week' => null,
		'recurrence_day_of_month' => 1,
		'recurrence_month' => null
	]
];

// Insert recurring tasks using createRecurringTask helper
foreach ($recurring_tasks as $task) {
	$recurringTask = new RecurringTask();
	$recurringTask->title = $task['title'];
	$recurringTask->body = $task['body'];
	$recurringTask->dueDateIncrementSelected = $task['due_date_increment_selected'];
	$recurringTask->categoryId = $task['category_id'];
	$recurringTask->recurrenceAmount = $task['recurrence_amount'];
	$recurringTask->recurrenceUnit = $task['recurrence_unit'];
	$recurringTask->recurrenceDayOfWeek = $task['recurrence_day_of_week'];
	$recurringTask->recurrenceDayOfMonth = $task['recurrence_day_of_month'];
	$recurringTask->recurrenceMonth = $task['recurrence_month'];

	if (!createRecurringTask($db, $recurringTask)) {
		echo "Error inserting recurring task: " . $db->lastErrorMsg() . "\n";
	} else {
		echo "Inserted recurring task: " . $task['title'] . "\n";
	}

	// Create the task using the new Task class and createTask function
	createTaskFromRecurringTask($db, $recurringTask);
}

echo "Seeding completed.\n";
