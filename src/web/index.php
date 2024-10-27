<?php
require_once __DIR__ . '/../init.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Todos</title>
	<script src="https://unpkg.com/htmx.org@1.9.6"></script>
	<script src="//unpkg.com/alpinejs" defer></script>
	<script src="https://cdn.tailwindcss.com"></script>
	<style>
		[x-cloak] {
			display: none;
		}
	</style>
	<script src="https://unpkg.com/htmx.org@1.9.12/dist/ext/alpine-morph.js"></script>
	<script src="/assets/js/Sortable.js"></script>
	<script src="/assets/js/tasksList.js"></script>

	<link href='/apple-touch-icon.png' rel='apple-touch-icon' type='image/png'>
	<link rel="manifest" href="/assets/site.webmanifest">
	<link rel="icon" type="image/png" sizes="32x32" href="/assets/icons/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/assets/icons/favicon-16x16.png">
	<link rel="shortcut icon" href="/favicon.ico">
</head>

<body class="bg-gray-100 lg:p-8 p-2">
	<h1 class="pt-4 text-3xl font-bold mb-6 text-center text-blue-600">Todo List</h1>

	<div x-data="{ formVisible: false }" class="max-w-md mx-auto mb-4" @refreshTasks.window="formVisible = false">
		<div class="flex justify-center">
			<button @click="formVisible=!formVisible" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
				Add Task
			</button>
		</div>

		<div x-show="formVisible" x-cloak class="bg-white p-4 rounded-lg shadow-md max-w-md mx-auto mt-4"
			x-data="{ priority: '<?= PRIORITY_NORMAL ?>' }">
			<form hx-post="/api/add-task.php" hx-swap="innerHTML" hx-target="#add-task-form-result">
				<div id="add-task-form-result"></div>

				<div class="mb-2">
					<input type="text" name="title"
						placeholder="Task title"
						class="w-full p-2 border rounded"
						required
						x-ref="taskTitle">
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

	<div id="todo-container" hx-get="/views/list.php" hx-trigger="load, refreshTasks from:body">
	</div>
</body>

</html>