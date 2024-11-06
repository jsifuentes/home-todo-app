<?php
require_once __DIR__ . '/../init.php';

$now = datetimeToLocalDateTime('now');
$currentHour = (int)$now->format('G');
if ($currentHour !== 18) {
	exit("Not running - current hour is {$currentHour}, waiting for 9\n");
}

// Get all active recurring tasks
$stmt = $db->query("SELECT * FROM recurring_tasks WHERE is_active = 1");

while ($recurringTask = $stmt->fetchArray(SQLITE3_ASSOC)) {
	$recurringTask = new RecurringTask($recurringTask);
	$now = new DateTime();
	$nextScheduled = $recurringTask->getNextScheduledGeneration();

	// If we've passed the next scheduled generation time, create a new task
	if ($now >= $nextScheduled) {
		echo "Generating new task for recurring task: {$recurringTask->title}\n";
		createTaskFromRecurringTask($db, $recurringTask);
	} else {
		echo "Skipping generation for recurring task: {$recurringTask->title} - next scheduled generation is {$nextScheduled->format('Y-m-d H:i')}\n";
	}
}

echo "Cron job completed successfully.\n";
