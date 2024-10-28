<?php
require_once __DIR__ . '../../../init.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Are we trying to load a category?
$categoryId = isset($_GET['category_id']) && is_numeric($_GET['category_id']) ? $_GET['category_id'] : null;

function getTasks($categoryId = null): array
{
	global $db;

	// Prepare and execute the query
	$bindValues = [];
	$query = "SELECT tasks.*, categories.name AS category_name FROM tasks LEFT JOIN categories ON tasks.category_id = categories.id";
	if ($categoryId) {
		$query .= " WHERE category_id = :category_id";
		$bindValues[':category_id'] = $categoryId;
	}
	$query .= " ORDER BY sort_order ASC, updated_at DESC";
	$stmt = $db->prepare($query);
	foreach ($bindValues as $key => $value) {
		$stmt->bindValue($key, $value);
	}
	$result = $stmt->execute();

	// Fetch all rows and store them in an array
	$tasks = [];
	while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
		$tasks[] = $row;
	}

	// Sort them so all the tasks that are due within the next 24 hours are at the top.
	// Do not sort any other tasks.
	usort($tasks, function ($a, $b) {
		// Get current timestamp
		$now = time();

		// Get timestamps for tasks' due dates
		$aDueTimestamp = strtotime($a['due_date']);
		$bDueTimestamp = strtotime($b['due_date']);

		// If either task has no due date, don't change order
		if (!$aDueTimestamp || !$bDueTimestamp) {
			return 0;
		}

		// Calculate if tasks are due within 24 hours
		$aWithin24h = ($aDueTimestamp - $now) <= 86400; // 86400 seconds = 24 hours
		$bWithin24h = ($bDueTimestamp - $now) <= 86400;

		// If both or neither are within 24h, maintain current order
		if ($aWithin24h === $bWithin24h) {
			return 0;
		}

		// Sort tasks within 24h to the top
		return $aWithin24h ? -1 : 1;
	});

	return [
		'notDone' => array_filter($tasks, function ($task) {
			return $task['status'] !== 'done';
		}),
		'done' => array_filter($tasks, function ($task) {
			return $task['status'] === 'done';
		}),
	];
}

$categories = getCategories();
$taskLists = getTasks($categoryId);
?>

<div class="flex flex-col justify-between space-x-4" x-data="tasksList">
	<div class="">
		<?php if (empty($taskLists['notDone']) && empty($taskLists['done'])): ?>
			<div class="bg-white rounded-lg shadow-sm p-8 text-center">
				<p class="text-gray-500 text-lg mb-2">No tasks found</p>
				<p class="text-gray-400">Add a new task above to get started</p>
			</div>
		<?php endif; ?>

		<?php foreach ($taskLists as $status => $taskList): ?>
			<ul id="todo-list-<?= $status ?>"
				class="list-group bg-gray-100 rounded"
				x-data="{ editing: false }">
				<?php foreach ($taskList as $task): ?>
					<li class="list-group-item bg-white p-2 mb-2 rounded shadow flex items-center justify-between"
						data-id="<?= $task['id'] ?>"
						:class="{ 'done bg-gray-200': done }"
						x-data="{ done: <?= $task['status'] === 'done' ? 'true' : 'false' ?> }">
						<div class="flex items-center w-full items-stretch">
							<div class="status-checkbox mx-auto flex items-center">
								<input type="checkbox" class="mr-3 h-5 w-5"
									:checked="done"
									@click.stop="updateStatus($event, <?= $task['id'] ?>)">
							</div>
							<div :class="{ 'line-through': done && !editing }" class="w-full">
								<template x-if="editing !== <?= $task['id'] ?>">
									<div>
										<h3 @click="editing = <?= $task['id'] ?>"><?= htmlspecialchars($task['title']) ?></h3>
										<p class="text-sm text-gray-500"><?= htmlspecialchars($task['body']) ?></p>
										<div class="flex text-xs text-gray-500">
											<?php if ($task['due_date'] && $task['status'] !== TASK_STATUS_DONE): ?>
												<p class="rounded bg-gray-200 px-1 mr-1"
													:class="{
														'bg-red-500 text-white': dueWithinHours(<?= strtotime($task['due_date']) ?>, 24),
														'bg-yellow-500 text-white': dueWithinHours(<?= strtotime($task['due_date']) ?>, 48)
													}">
													due <?= time2str($task['due_date']) ?><template x-if="dueWithinHours(<?= strtotime($task['due_date']) ?>, 48)">
														<span>!</span>
													</template>
												</p>
											<?php endif; ?>
											<?php if ($task['category_name']): ?>
												<p class="rounded bg-gray-200 px-1 mr-1">
													<?= htmlspecialchars($task['category_name']) ?>
												</p>
											<?php endif; ?>
											<p class="hidden md:inline">updated <?= time2str($task['updated_at']) ?></p>
											<p class="inline md:hidden"><?= time2str($task['updated_at']) ?></p>
										</div>
									</div>
								</template>
								<template x-if="editing === <?= $task['id'] ?>"
									x-init="$watch('editing', new_value => { if (new_value) { htmx.process(htmx.find('.edit-task-form')) } })">
									<form class="edit-task-form" hx-post="/api/update-task.php">
										<input type="hidden" name="taskId" value="<?= $task['id'] ?>">
										<input type="text" name="title" class="w-full border rounded p-1 mb-1"
											value="<?= htmlspecialchars($task['title']) ?>"
											@keydown.enter="$event.target.form.submit()"
											@keydown.escape="editing = false"
											x-ref="titleInput"
											x-init="$nextTick(() => $refs.titleInput.focus() && $refs.titleInput.select())">
										<textarea name="body" class="w-full border rounded p-1"
											rows="2"><?= htmlspecialchars($task['body']) ?></textarea>
										<select name="category_id" class="w-full border rounded py-2 px-1">
											<option value="">No Category</option>
											<?php foreach ($categories as $category): ?>
												<option value="<?= $category['id'] ?>" <?= $task['category_id'] === $category['id'] ? 'selected' : '' ?>>
													<?= htmlspecialchars($category['name']) ?>
												</option>
											<?php endforeach; ?>
										</select>
										<div class="flex space-x-2 mt-2">
											<button type="submit" class="px-3 py-1 text-sm text-white bg-blue-500 rounded hover:bg-blue-600">
												Submit
											</button>
											<button type="button" class="px-3 py-1 text-sm text-gray-600 bg-gray-200 rounded hover:bg-gray-300" @click="editing = false">
												Cancel
											</button>
											<button type="button" class="px-3 py-1 text-sm text-white bg-red-500 rounded hover:bg-red-600 transition-colors duration-200" hx-delete="/api/delete-task.php?taskId=<?= $task['id'] ?>" hx-confirm="Are you sure you want to delete this task?">
												Delete
											</button>
										</div>
									</form>
								</template>
							</div>

							<div class="handle-button mx-auto px-3 flex items-center">
								<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
									<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9h16.5m-16.5 6.75h16.5" />
								</svg>
							</div>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endforeach; ?>
	</div>
</div>