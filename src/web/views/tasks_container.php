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
		<div class="mx-auto">
			<div class="text-center">
				<button @click="formVisible = !formVisible"
					hx-get="/views/add_task_form.php"
					hx-trigger="click"
					hx-target="#add-task-form"
					hx-indicator=".loading"
					:class="formVisible ? 'bg-gray-300 hover:bg-gray-200 text-gray-600' : 'bg-blue-700 hover:bg-blue-400 text-white'"
					class="bg-blue-500 hover:bg-blue-700 text-white font-bold mt-2 py-2 px-4 rounded lg:w-[200px]"
					x-text="formVisible ? 'Close' : 'Add Task'">
				</button>
			</div>

			<div class="loading"></div>
			<div id="add-task-form" x-show="formVisible" x-cloak class="bg-white p-4 rounded-lg shadow-md max-w-md mx-auto my-4">
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