<?php
require_once __DIR__ . '../../../init.php';

// Get recurring tasks with category names
$recurringTasks = $db->query("
    SELECT rt.*, c.name as category_name 
    FROM recurring_tasks rt 
    LEFT JOIN categories c ON rt.category_id = c.id 
    ORDER BY rt.title ASC
");

?>

<div class="flex flex-col justify-between space-x-4 mt-8" x-data="{ modalIsOpen: false, editingTaskId: null }"
	x-init="$watch('editingTaskId', (newVal) => {
		htmx.ajax('GET', `/views/recurring_tasks_edit_form.php?id=${newVal}`, document.getElementById('recurring-task-edit-form'));
	})">
	<h2 class="text-xl font-bold mb-2">Recurring Tasks</h2>

	<div id="recurring-task-form-result"></div>

	<div class="bg-white rounded-lg shadow mb-4 p-4">

		<div class="flex flex-col gap-2">
			<?php while ($recurringTaskData = $recurringTasks->fetchArray(SQLITE3_ASSOC)): ?>
				<?php $recurringTask = new RecurringTask($recurringTaskData); ?>

				<div class="flex flex-col gap-2 border-b border-gray-200 pb-2">
					<span><strong>Title:</strong> <?= htmlspecialchars($recurringTask->title) ?></span>
					<span><strong>Category:</strong> <?= htmlspecialchars($recurringTaskData['category_name']) ?></span>
					<span>
						<strong>Recurrence:</strong>
						<?php
						$recurrence = "Every {$recurringTask->recurrenceAmount} ";
						switch ($recurringTask->recurrenceUnit) {
							case 'd':
								$recurrence .= $recurringTask->recurrenceAmount > 1 ? 'days' : 'day';
								break;
							case 'w':
								$recurrence .= $recurringTask->recurrenceAmount > 1 ? 'weeks' : 'week';
								$recurrence .= " on " . date('l', strtotime("Sunday +{$recurringTask->recurrenceDayOfWeek} days"));
								break;
							case 'm':
								$recurrence .= $recurringTask->recurrenceAmount > 1 ? 'months' : 'month';
								$recurrence .= " on day {$recurringTask->recurrenceDayOfMonth}";
								break;
							case 'y':
								$recurrence .= $recurringTask->recurrenceAmount > 1 ? 'years' : 'year';
								$recurrence .= " on " . date('F', mktime(0, 0, 0, $recurringTask->recurrenceMonth, 1)) . " {$recurringTask->recurrenceDayOfMonth}";
								break;
						}
						echo htmlspecialchars($recurrence);
						?>
					</span>
					<span><strong>Next Schedule:</strong> <?= changeDateTimeToLocalEndOfDay($recurringTask->getNextScheduledGeneration())->format('F jS, Y') ?></span>
					<span><strong>Due After:</strong> <?= DUE_DATE_LABELS[$recurringTask->dueDateIncrementSelected] ?></span>
					<div class="flex gap-4">
						<button @click="modalIsOpen = true; editingTaskId = <?= $recurringTask->id ?>"
							class="bg-blue-500 text-white px-4 py-1 rounded-md hover:bg-blue-900">Edit</button>
						<button
							hx-delete="/api/delete_recurring_task.php?taskId=<?= $recurringTask->id ?>"
							hx-confirm="Are you sure you want to delete this recurring task? This will not delete any existing tasks."
							class="bg-red-500 text-white px-4 py-1 rounded-md hover:bg-red-900">Delete</button>
					</div>
				</div>
			<?php endwhile; ?>
		</div>

		<!-- Modal Dialog -->
		<div x-cloak x-show="modalIsOpen" x-transition.opacity.duration.200ms x-trap.inert.noscroll="modalIsOpen"
			@keydown.esc.window="modalIsOpen = false" @click.self="modalIsOpen = false"
			class="fixed inset-0 z-30 flex items-center justify-center bg-black/20 p-4 pb-8 lg:p-8"
			role="dialog" aria-modal="true" aria-labelledby="editTaskModalTitle">
			<div x-show="modalIsOpen"
				x-transition:enter="transition ease-out duration-200 delay-100 motion-reduce:transition-opacity"
				x-transition:enter-start="opacity-0"
				x-transition:enter-end="opacity-100"
				class="flex max-w-lg flex-col overflow-hidden rounded-md border border-neutral-300 bg-white text-neutral-600 md:min-w-[400px]">
				<!-- Dialog Header -->
				<div class="flex items-center justify-between border-b border-neutral-300 bg-neutral-50/60 p-4">
					<h3 id="editTaskModalTitle" class="font-semibold tracking-wide text-neutral-900">Edit Recurring Task</h3>
					<button @click="modalIsOpen = false" aria-label="close modal">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"
							stroke="currentColor" fill="none" stroke-width="1.4" class="w-5 h-5">
							<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
						</svg>
					</button>
				</div>

				<!-- Dialog Body -->
				<div class="p-4" id="recurring-task-edit-form"></div>
			</div>
		</div>
	</div>
</div>

<script src="/assets/js/recurringTasksEditForm.js"></script>