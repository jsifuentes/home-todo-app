<?php

require_once __DIR__ . '/../init.php';

// Sample task data
$tasks = [
	[
		'title' => 'Buy milk',
		'body' => '',
		'sort_order' => 3,
		'status' => 'todo',
	],
	[
		'title' => 'Call plumber',
		'body' => 'Leaky faucet in kitchen',
		'sort_order' => 2,
		'status' => 'todo',
	],
	[
		'title' => 'Laundry',
		'body' => '',
		'sort_order' => 4,
		'status' => 'in_progress',
	],
	[
		'title' => 'Pay electric bill',
		'body' => '',
		'sort_order' => 1,
		'status' => 'todo',
	],
	[
		'title' => 'Mow lawn',
		'body' => '',
		'sort_order' => 3,
		'status' => 'todo',
	],
	[
		'title' => 'Dentist appt',
		'body' => 'Thurs @ 2pm',
		'sort_order' => 2,
		'status' => 'todo',
	],
	[
		'title' => 'Pick up dry cleaning',
		'body' => '',
		'sort_order' => 4,
		'status' => 'todo',
	],
	[
		'title' => 'Get car oil change',
		'body' => 'Due soon',
		'sort_order' => 3,
		'status' => 'in_progress',
	],
	[
		'title' => 'Grocery shopping',
		'body' => '',
		'sort_order' => 2,
		'status' => 'done',
	],
	[
		'title' => 'Clean garage',
		'body' => '',
		'sort_order' => 5,
		'status' => 'todo',
	],
];

// Prepare the SQL statement
$stmt = $db->prepare('INSERT INTO tasks (title, body, sort_order, status) VALUES (:title, :body, :sort_order, :status)');

// Insert each task
foreach ($tasks as $task) {
	$stmt->bindValue(':title', $task['title'], SQLITE3_TEXT);
	$stmt->bindValue(':body', $task['body'], SQLITE3_TEXT);
	$stmt->bindValue(':sort_order', $task['sort_order'], SQLITE3_INTEGER);
	$stmt->bindValue(':status', $task['status'], SQLITE3_TEXT);

	if (!$stmt->execute()) {
		echo "Error inserting task: " . $db->lastErrorMsg() . "\n";
	} else {
		echo "Inserted task: " . $task['title'] . "\n";
	}
}

echo "Seeding completed.\n";
