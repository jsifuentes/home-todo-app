document.addEventListener('alpine:init', () => {
	Alpine.data('recurringTasksEditForm', (taskData) => ({
		taskData,

		init() {		
			this.$watch('editingTaskId', new_value => {
				if (new_value) {
					htmx.ajax('GET', `/views/recurring_tasks_edit_form.php?id=${new_value}`, document.getElementById('recurring-task-edit-form'));
				}
			});
		}
	}));
});
