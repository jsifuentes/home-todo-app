<?php
require_once __DIR__ . '../../../init.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Are we trying to load a category?
$categoryId = isset($_GET['category_id']) && is_numeric($_GET['category_id']) ? $_GET['category_id'] : null;

function getCategories(): array
{
	global $db;
	$categories = $db->query("SELECT * FROM categories ORDER BY is_default DESC, name ASC");
	$result = [];
	while ($category = $categories->fetchArray(SQLITE3_ASSOC)) {
		$result[] = $category;
	}
	return $result;
}

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
	<div class="text-center">
		<button @click="formVisible=!formVisible" class="bg-blue-500 hover:bg-blue-700 text-white font-bold mt-2 py-2 px-4 rounded lg:w-[200px]">
			Add Task
		</button>
	</div>

	<div class="mx-auto">
		<div x-show="formVisible" x-cloak class="bg-white p-4 rounded-lg shadow-md max-w-md mx-auto mt-4"
			x-data="{ priority: '<?= PRIORITY_NORMAL ?>', newTaskCategory: null }">
			<form hx-post="/api/add-task.php" hx-swap="innerHTML" hx-target="#add-task-form-result">
				<div id="add-task-form-result"></div>

				<div class="mb-2">
					<input type="text" name="title"
						placeholder="Task title"
						class="w-full p-2 border rounded"
						required
						x-ref="taskTitle">
				</div>
				<div class="flex flex-wrap gap-2 mb-3">
					<?php foreach ($categories as $category): ?>
						<button type="button"
							class="px-3 py-1 rounded border transition-colors duration-200"
							:class="{
								'bg-blue-500 text-white border-blue-600 hover:bg-blue-600': newTaskCategory === <?= $category['id'] ?>,
								'bg-white text-gray-700 border-gray-300 hover:bg-gray-50': newTaskCategory !== <?= $category['id'] ?>
							}"
							@click="newTaskCategory = <?= $category['id'] ?>">
							<?= htmlspecialchars($category['name']) ?>
						</button>
					<?php endforeach; ?>
					<input type="hidden" name="category_id" x-model="newTaskCategory">
				</div>
				<div class="mb-2">
					<div class="space-y-2">
						<div class="text-sm font-medium text-gray-700 mb-2">Priority:</div>
						<div class="flex text-xs justify-center gap-1 w-full">
							<div class="flex items-center p-1 border rounded transition-all" :class="{ 'border-blue-500 bg-blue-50': priority === '<?= PRIORITY_LOW ?>' }">
								<input type="radio" id="low" name="priority" value="<?= PRIORITY_LOW ?>" class="mr-2" x-model="priority">
								<label for="low" class="flex-grow cursor-pointer">
									<div class="font-semibold">Lower Priority</div>
								</label>
							</div>
							<div class="flex items-center p-1 border rounded transition-all" :class="{ 'border-blue-500 bg-blue-50': priority === '<?= PRIORITY_NORMAL ?>' }">
								<input type="radio" id="normal" name="priority" value="<?= PRIORITY_NORMAL ?>" class="mr-2" x-model="priority">
								<label for="normal" class="flex-grow cursor-pointer">
									<div class="font-semibold">Normal Priority</div>
								</label>
							</div>
							<div class="flex items-center p-1 border rounded transition-all" :class="{ 'border-blue-500 bg-blue-50': priority === '<?= PRIORITY_HIGH ?>' }">
								<input type="radio" id="high" name="priority" value="<?= PRIORITY_HIGH ?>" class="mr-2" x-model="priority">
								<label for="high" class="flex-grow cursor-pointer">
									<div class="font-semibold">Need to do soon</div>
								</label>
							</div>
						</div>
					</div>
				</div>
				<div class="mb-4">
					<label for="due_date_selector" class="block text-sm font-medium text-gray-700 mb-2">Due Date:</label>
					<div x-data='{ choice: 2, due_dates: <?= json_encode(DUE_DATE_LABELS); ?> }'
						x-init="
							$watch('priority', new_value => { 
								if (new_value === '<?= PRIORITY_HIGH ?>') {
									choice = 0;
								} else if (new_value === '<?= PRIORITY_NORMAL ?>') {
									choice = 2;
								} else if (new_value === '<?= PRIORITY_LOW ?>') {
									choice = 4;
								}
							})">
						<input type="range" id="due_date_selector"
							x-model="choice"
							min="0"
							:max="Object.values(due_dates).length - 1"
							step="1"
							class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
						<div class="text-center mt-2" x-text="Object.values(due_dates)[choice]"></div>
						<input type="hidden" :value="Object.keys(due_dates)[choice]" name="due_date">
					</div>
				</div>
				<button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
					Add
				</button>
			</form>
		</div>
	</div>

	<div class="my-4">
		<div class="flex flex-wrap gap-2 mb-3">
			<button type="button"
				class="px-3 py-1 rounded border transition-colors duration-200"
				:class="{
					'bg-blue-500 text-white border-blue-600 hover:bg-blue-600': selectedCategory === null
				}"
				@click="selectedCategory = null">
				All
			</button>
			<?php
			foreach ($categories as $category):
			?>
				<button type="button"
					class="px-3 py-1 rounded border transition-colors duration-200"
					:class="{
						'bg-blue-500 text-white border-blue-600 hover:bg-blue-600': selectedCategory === <?= $category['id'] ?>,
						'bg-white text-gray-700 border-gray-300 hover:bg-gray-50': selectedCategory !== <?= $category['id'] ?>
					}"
					@click="selectedCategory = <?= $category['id'] ?>">
					<?= htmlspecialchars($category['name']) ?>
				</button>
			<?php endforeach; ?>
		</div>

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
											<?php if ($task['due_date']): ?>
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
											<p>updated <?= time2str($task['updated_at']) ?></p>
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