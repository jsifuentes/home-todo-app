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

		// If the  task is past due, it should be at the top
		if ($aDueTimestamp < $now) {
			return -1;
		}
		if ($bDueTimestamp < $now) {
			return 1;
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

<div class="flex flex-col justify-between" x-data="tasksList">
	<?php if (empty($taskLists['notDone']) && empty($taskLists['done'])): ?>
		<div class="bg-white rounded-lg shadow-sm p-8 text-center">
			<p class="text-gray-500 text-lg mb-2">No tasks found</p>
			<p class="text-gray-400">Add a new task above to get started</p>
		</div>
	<?php endif; ?>

	<?php foreach ($taskLists as $status => $taskList): ?>
		<ul id="todo-list-<?= $status ?>"
			class="list-group bg-gray-100 rounded <?php if ($tvMode): ?>grid grid-cols-2 gap-x-2<?php endif; ?>">
			<?php foreach ($taskList as $task): ?>
				<?php
				$isDone = $task['status'] === TASK_STATUS_DONE;
				$isPastDue = isPastDue($task['due_date']);
				$isDueToday = isDueToday($task['due_date']);
				$isDueWithinTwoDays = isDueWithinDays($task['due_date'], 2);
				?>

				<li class="list-group-item mb-2 rounded shadow flex flex-col <?= $task['status'] === TASK_STATUS_DONE ? 'done bg-gray-200' : 'bg-white' ?> <?= !$isDone && $isPastDue ? 'border-2 border-red-400' : '' ?>"
					data-id="<?= $task['id'] ?>">

					<form class="edit-task-form" hx-post="/api/update_task.php">
						<input type="hidden" name="task_id" value="<?= $task['id'] ?>">
						<div class="flex w-full items-stretch p-2">
							<div class="flex items-center w-full items-stretch cursor-pointer"
								@click="showDropdownTaskId = showDropdownTaskId === <?= $task['id'] ?> ? null : <?= $task['id'] ?>">
								<div class="status-checkbox mx-auto flex items-center">
									<input type="checkbox" class="mr-3 h-5 w-5"
										<?php if ($isDone): ?>checked<?php endif; ?>
										@click.stop="updateStatus($event, <?= $task['id'] ?>)">
								</div>
								<div <?php if ($isDone): ?>:class="{ 'line-through': !editingTaskId }" <?php endif; ?> class="w-full">
									<div x-show="editingTaskId !== <?= $task['id'] ?>">
										<h3><?= htmlspecialchars($task['title']) ?></h3>
										<p class="text-sm text-gray-500"><?= htmlspecialchars($task['body']) ?></p>
										<div class="flex text-xs text-gray-500">
											<?php if ($task['due_date'] && $task['status'] !== TASK_STATUS_DONE): ?>
												<p class="rounded bg-gray-200 px-1 mr-1"
													:class="{
														'bg-red-700 text-white': <?= $isPastDue ? 'true' : 'false' ?>,
														'bg-red-400 text-white': <?= !$isPastDue && $isDueToday ? 'true' : 'false' ?>,
														'bg-yellow-500 text-white': <?= !$isPastDue && !$isDueToday && $isDueWithinTwoDays ? 'true' : 'false' ?>,
													}" title="due <?= htmlspecialchars($task['due_date']) ?>">
													due <?= getRelativeDueDateString($task['due_date']) ?><?php if ($isDueWithinTwoDays): ?><span>!</span><?php endif; ?>
												</p>
											<?php endif; ?>
											<?php if ($task['category_name']): ?>
												<p class="rounded bg-gray-200 px-1 mr-1">
													<?= htmlspecialchars($task['category_name']) ?>
												</p>
											<?php endif; ?>
											<p class="hidden md:inline" title="<?= $task['updated_at'] ?>">updated <?= time2str($task['updated_at']) ?></p>
											<p class="inline md:hidden"><?= time2str($task['updated_at']) ?></p>
										</div>
									</div>

									<div x-show="editingTaskId === <?= $task['id'] ?>">
										<input type="text" name="title" class="w-full border rounded p-1 mb-1"
											value="<?= htmlspecialchars($task['title']) ?>"
											@keydown.enter="$event.target.form.submit()"
											@keydown.escape="editingTaskId = null"
											x-ref="titleInput">
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
									</div>
								</div>
							</div>

							<!-- Handle button is outside of the inner flex container so that we can handle click events on it separately -->
							<div class="handle-button mx-auto px-3 flex items-center">
								<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
									<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9h16.5m-16.5 6.75h16.5" />
								</svg>
							</div>
						</div>

						<div class="flex w-full items-stretch border-t border-gray-200 pt-2" x-show="editingTaskId === <?= $task['id'] ?> || showDropdownTaskId === <?= $task['id'] ?>">
							<div class="flex w-full justify-between px-[16px] items-stretch flex-grow">
								<button x-show="editingTaskId !== <?= $task['id'] ?>" type="button" class="px-3 py-1 w-full text-sm text-gray-400 text-center" hx-post="/api/update_task.php" hx-vals='{"task_id": "<?= $task['id'] ?>", "add_to_due_date": "1d"}'>
									<div>
										<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 m-auto">
											<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
										</svg>
									</div>
									<span>Add one more day</span>
								</button>
								<button x-show="editingTaskId !== <?= $task['id'] ?>" type="button" class="px-3 py-1 w-full text-sm text-gray-400 text-center"
									@click="editingTaskId = <?= $task['id'] ?>">
									<div>
										<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 m-auto">
											<path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
										</svg>
									</div>
									<span>Edit</span>
								</button>
								<button x-show="editingTaskId !== <?= $task['id'] ?>" type="button" class="px-3 py-1 w-full text-sm text-gray-400 text-center" hx-delete="/api/delete_task.php?task_id=<?= $task['id'] ?>" hx-confirm="Are you sure you want to delete this task?">
									<div>
										<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 m-auto">
											<path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
										</svg>
									</div>
									<span>Delete</span>
								</button>

								<button x-show="editingTaskId === <?= $task['id'] ?>" type="submit" class="px-3 py-1 w-full text-sm text-gray-400 text-center">
									<div>
										<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 m-auto">
											<path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
										</svg>
									</div>
									<span>Save</span>
								</button>

								<button x-show="editingTaskId === <?= $task['id'] ?>" type="button" class="px-3 py-1 w-full text-sm text-gray-400 text-center" @click="editingTaskId = null; showDropdownTaskId = null">
									<div>
										<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 m-auto">
											<path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
										</svg>

									</div>
									<span>Cancel</span>
								</button>
							</div>
						</div>
					</form>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endforeach; ?>
</div>