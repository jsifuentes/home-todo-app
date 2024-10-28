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

// Prepare the SQL statement
$stmt = $db->prepare('INSERT INTO tasks (id, title, body, sort_order, status, category_id, due_date) VALUES (:id, :title, :body, :sort_order, :status, :category_id, :due_date)');

// Insert each task
foreach ($tasks as $task) {
	$endDueDate = calculateEndDate($task['due_date']);
	$stmt->bindValue(':id', $task['id'], SQLITE3_INTEGER);
	$stmt->bindValue(':title', $task['title'], SQLITE3_TEXT);
	$stmt->bindValue(':body', $task['body'], SQLITE3_TEXT);
	$stmt->bindValue(':sort_order', $task['sort_order'], SQLITE3_INTEGER);
	$stmt->bindValue(':status', $task['status'], SQLITE3_TEXT);
	$stmt->bindValue(':category_id', $task['category_id'], SQLITE3_INTEGER);
	$stmt->bindValue(':due_date', $endDueDate->format('Y-m-d H:i:s'), SQLITE3_TEXT);

	if (!$stmt->execute()) {
		echo "Error inserting task: " . $db->lastErrorMsg() . "\n";
	} else {
		echo "Inserted task: " . $task['title'] . "\n";
	}
}

echo "Seeding completed.\n";
