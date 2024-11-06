<?php
$recurringTaskId = $_GET['id'] ?? null;

if (!$recurringTaskId) {
	die('No recurring task ID provided');
}

require_once __DIR__ . '../../../init.php';

$categories = getCategories();
$recurringTask = getRecurringTaskById($db, $recurringTaskId);

if (!$recurringTask) {
	die('Recurring task not found');
}

?>


<form class="space-y-4" x-data='recurringTasksEditForm(<?= json_encode($recurringTask->toArray()) ?>)'
	hx-post="/api/update_recurring_task.php"
	hx-target="#recurring-tasks-edit-form-result"
	hx-swap="innerHTML">

	<div id="recurring-tasks-edit-form-result"></div>

	<input type="hidden" name="id" x-model="taskData.id">

	<!-- Move your existing edit form fields here -->
	<div class="space-y-2">
		<label class="block">Title</label>
		<input type="text" name="title" x-model="taskData.title"
			class="w-full px-2 py-1 border rounded">
	</div>

	<div class="space-y-2">
		<label class="block">Body</label>
		<textarea name="body" x-model="taskData.body" rows="3"
			class="w-full px-2 py-1 border rounded"></textarea>
	</div>

	<div class="space-y-2">
		<label class="block">Category</label>
		<select name="category_id" x-model="taskData.category_id"
			class="w-full px-2 py-1 border rounded">
			<option value="">No Category</option>
			<?php foreach ($categories as $category): ?>
				<option value="<?= $category['id'] ?>">
					<?= htmlspecialchars($category['name']) ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>

	<div class="mt-3 flex gap-2 items-center flex-wrap">
		<span class="text-sm text-gray-700">Repeat every</span>
		<input type="number"
			name="recurrence_amount"
			x-model="taskData.recurrence_amount"
			min="1"
			class="block w-10 p-1 rounded border border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
			placeholder="1">

		<select name="recurrence_unit" x-model="taskData.recurrence_unit"
			class="block rounded p-1 border border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
			<option value="d">Days</option>
			<option value="w">Weeks</option>
			<option value="m">Months</option>
			<option value="y">Years</option>
		</select>

		<div x-show="taskData.recurrence_unit === 'w'" class="flex items-center gap-2">
			<span class="text-sm text-gray-700">on</span>
			<select name="recurrence_day" x-model="taskData.recurrence_day"
				class="block rounded p-1 border border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
				<option value="1">Monday</option>
				<option value="2">Tuesday</option>
				<option value="3">Wednesday</option>
				<option value="4">Thursday</option>
				<option value="5">Friday</option>
				<option value="6">Saturday</option>
				<option value="7">Sunday</option>
			</select>
		</div>

		<div x-show="taskData.recurrence_unit === 'm'" class="flex items-center gap-2">
			<span class="text-sm text-gray-700">on day</span>
			<input type="number"
				name="recurrence_day_of_month"
				x-model="taskData.recurrence_day_of_month"
				min="1"
				max="31"
				class="block w-14 p-1 rounded border border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
				placeholder="1">
		</div>

		<div x-show="taskData.recurrence_unit === 'y'" class="flex items-center gap-2">
			<span class="text-sm text-gray-700">on</span>
			<select name="recurrence_month" x-model="taskData.recurrence_month"
				class="block rounded p-1 border border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
				<option value="1">January</option>
				<option value="2">February</option>
				<option value="3">March</option>
				<option value="4">April</option>
				<option value="5">May</option>
				<option value="6">June</option>
				<option value="7">July</option>
				<option value="8">August</option>
				<option value="9">September</option>
				<option value="10">October</option>
				<option value="11">November</option>
				<option value="12">December</option>
			</select>
			<span class="text-sm text-gray-700">day</span>
			<input type="number"
				name="recurrence_day_of_month"
				x-model="taskData.recurrence_day_of_month"
				min="1"
				max="31"
				class="block w-14 p-1 rounded border border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
				placeholder="1">
		</div>
	</div>

	<!-- Dialog Footer -->
	<div class="flex justify-end gap-2 pt-4">
		<button @click="modalIsOpen = false" type="button"
			class="px-4 py-2 text-sm text-neutral-600 hover:opacity-75">
			Cancel
		</button>
		<button type="submit"
			class="px-4 py-2 text-sm bg-black text-white rounded-md hover:opacity-75">
			Save Changes
		</button>
	</div>
</form>