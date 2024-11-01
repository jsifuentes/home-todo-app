<?php
require_once __DIR__ . '/../../init.php';
$categories = getCategories();

$defaultCategoryIndex = array_search(true, array_column($categories, 'is_default'));
$defaultCategoryId = $defaultCategoryIndex !== false ? $categories[$defaultCategoryIndex]['id'] : null;
?>

<div x-data='tasksListContainer(
	<?= json_encode(DUE_DATE_LABELS); ?>,
	{
		high: "<?= PRIORITY_HIGH ?>",
		normal: "<?= PRIORITY_NORMAL ?>",
		low: "<?= PRIORITY_LOW ?>"
	},
	<?= $defaultCategoryId ?>
)'>
	<?php if (!$tvMode): ?>
		<div class="text-center">
			<button @click="formVisible = !formVisible" class="bg-blue-500 hover:bg-blue-700 text-white font-bold mt-2 py-2 px-4 rounded lg:w-[200px]">
				Add Task
			</button>
		</div>

		<div class="mx-auto">
			<div x-show="formVisible" x-cloak class="bg-white p-4 rounded-lg shadow-md max-w-md mx-auto my-4">
				<form hx-post="/api/add_task.php" hx-swap="innerHTML" hx-target="#add-task-form-result">
					<div id="add-task-form-result" x-ref="addTaskFormResult"></div>

					<div class="mb-2">
						<input type="text" name="title"
							:placeholder="randomTaskTitle() + '...'"
							class="w-full p-2 border rounded"
							required
							x-ref="taskTitle">
					</div>
					<div class="text-sm font-medium text-gray-700 mb-2">Category</div>
					<div class="flex flex-wrap gap-2 mb-3">
						<?php foreach ($categories as $category): ?>
							<div class="relative">
								<input type="radio"
									name="category_id"
									value="<?= $category['id'] ?>"
									id="category_<?= $category['id'] ?>"
									x-model="newTaskCategory"
									class="absolute opacity-0 w-full h-full cursor-pointer" required>
								<label for="category_<?= $category['id'] ?>"
									class="px-3 py-1 rounded border transition-colors duration-200"
									:class="{ 'bg-blue-500 text-white border-blue-600 hover:bg-blue-600': newTaskCategory == <?= $category['id'] ?>, 'border-gray-300 hover:bg-gray-50': newTaskCategory != <?= $category['id'] ?>}">
									<?= htmlspecialchars($category['name']) ?>
								</label>
							</div>
						<?php endforeach; ?>
					</div>
					<div class="mb-2">
						<label for="due_date_selector" class="block text-sm font-medium text-gray-700 mb-2">Due Date</label>
						<div class="space-y-2">
							<div class="flex text-xs justify-center gap-1 w-full">
								<div class="flex items-center md:p-1 px-1 py-2 border rounded transition-all flex-grow bg-red-100" :class="{ 'border-blue-500 bg-blue-50': newTaskPriority === '<?= PRIORITY_HIGH ?>' }">
									<input type="radio" id="high" name="priority" value="<?= PRIORITY_HIGH ?>" class="mr-2" x-model="newTaskPriority">
									<label for="high" class="flex-grow cursor-pointer">
										<div class="font-semibold">Do by tomorrow!</div>
									</label>
								</div>
								<div class="flex items-center md:p-1 px-1 py-2 border rounded transition-all flex-grow" :class="{ 'border-blue-500 bg-blue-50': newTaskPriority === '<?= PRIORITY_NORMAL ?>' }">
									<input type="radio" id="normal" name="priority" value="<?= PRIORITY_NORMAL ?>" class="mr-2" x-model="newTaskPriority">
									<label for="normal" class="flex-grow cursor-pointer">
										<div class="font-semibold">By next week</div>
									</label>
								</div>
								<div class="flex items-center md:p-1 px-1 py-2 border rounded transition-all flex-grow" :class="{ 'border-blue-500 bg-blue-50': newTaskPriority === '<?= PRIORITY_LOW ?>' }">
									<input type="radio" id="low" name="priority" value="<?= PRIORITY_LOW ?>" class="mr-2" x-model="newTaskPriority">
									<label for="low" class="flex-grow cursor-pointer">
										<div class="font-semibold">By next month</div>
									</label>
								</div>
							</div>
						</div>
					</div>
					<div class="mb-4">
						<div>
							<input type="range" id="due_date_selector"
								x-model="newTaskDueDateRangeChoice"
								min="0"
								:max="Object.values(dueDatesConfig).length - 1"
								step="1"
								class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
							<div class="text-center" x-text="Object.values(dueDatesConfig)[newTaskDueDateRangeChoice]"></div>
							<input type="hidden" :value="Object.keys(dueDatesConfig)[newTaskDueDateRangeChoice]" name="due_date">
						</div>
					</div>
					<button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded w-full">
						Add
					</button>
				</form>
			</div>
		</div>
	<?php endif; ?>

	<div class="flex flex-wrap gap-2 my-3">
		<button type="button"
			class="px-3 py-1 rounded border transition-colors duration-200"
			:class="{
					'bg-blue-500 text-white border-blue-600 hover:bg-blue-600': filteredCategory === null
				}"
			@click="filteredCategory = null">
			All
		</button>
		<?php foreach ($categories as $category): ?>
			<button type="button"
				class="px-3 py-1 rounded border transition-colors duration-200"
				:class="{
						'bg-blue-500 text-white border-blue-600 hover:bg-blue-600': filteredCategory === <?= $category['id'] ?>,
						'bg-white text-gray-700 border-gray-300 hover:bg-gray-50': filteredCategory !== <?= $category['id'] ?>
					}"
				@click="filteredCategory = <?= $category['id'] ?>">
				<?= htmlspecialchars($category['name']) ?>
			</button>
		<?php endforeach; ?>
	</div>

	<div id="tasks-list" hx-get="/views/tasks_list.php" :hx-vars="filteredCategory ? 'category_id:' + filteredCategory : ''" hx-trigger="load, refreshTasks from:body">
	</div>

	<?php if (!$tvMode): ?>
		<div class="text-center">
			<button type="button"
				@click="autoRefreshEnabled = !autoRefreshEnabled"
				class="text-xs mt-4 px-4 py-2 rounded transition-colors duration-200"
				:class="autoRefreshEnabled ? 'bg-gray-100 hover:bg-gray-200 text-gray-600' : 'bg-blue-400 hover:bg-blue-400 text-white'">
				<span x-text="autoRefreshEnabled ? 'Disable' : 'Enable'"></span> Auto-Refresh
			</button>
		</div>
	<?php endif; ?>
</div>