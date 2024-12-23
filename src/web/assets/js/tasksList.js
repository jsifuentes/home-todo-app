document.addEventListener('alpine:init', () => {
	Alpine.data('tasksList', () => ({
		refreshTasksTimeout: null,
		refreshTasksTimeoutFunction: null,
		editingTaskId: null,
		showDropdownTaskId: null,
		disableCheckboxForTaskId: null,
		autoRefreshTimeout: null,
		sortable: null,

		editRecurringToggle: false,
		editRecurrenceUnit: 'd',

		init() {
			this.sortable = new Sortable(document.getElementById('todo-list-notDone'), {
				animation: 150,
				ghostClass: 'bg-gray-100',
				handle: '.handle-button',
				onEnd: function (evt) {
					// get all of the items in current order
					const items = Array.from(this.el.children);
			
					const formData = new FormData();
					formData.append('task_ids_in_order', JSON.stringify(items.filter(x => !x.classList.contains('done')).map(x => x.getAttribute('data-id'))));
			
					fetch('/api/update_sort_order.php', {
							method: 'POST',
							headers: {
								'Content-Type': 'application/x-www-form-urlencoded',
							},
							body: new URLSearchParams(formData).toString(),
						}
					)
						.then(() => {
							document.body.dispatchEvent(new Event('refreshTasks'));
						})
						.catch(error => {
							console.error('Error:', error);
						});
				}
			});

			this.$watch('editingTaskId', new_value => {
				if (new_value) {
					// Set initial values based on the task being edited
					const form = document.querySelector(`li[data-id="${new_value}"] form`);
					this.editRecurringToggle = form.querySelector('[name="is_recurring"]')?.getAttribute('orig-checked') === 'true' || false;
					this.editRecurrenceUnit = form.querySelector('[name="recurrence_unit"]')?.getAttribute('orig-value') || 'd';
				}
			});

			this.autoRefreshTimeout = setInterval(this.tryRefreshTasks.bind(this), 5000);

			this.onTaskStatusUpdated = () => {
				console.log('Status updated successfully');
				// Trigger a refresh of the task list on htmx
				if (this.refreshTasksTimeout) {
					clearTimeout(this.refreshTasksTimeout);
				}

				this.refreshTasksTimeoutFunction = () => {
					document.body.dispatchEvent(new Event('refreshTasks'));
					clearTimeout(this.refreshTasksTimeout);
					delete this.refreshTasksTimeoutFunction;
				};

				this.refreshTasksTimeout = setTimeout(this.refreshTasksTimeoutFunction, 1000);
			};

			document.body.addEventListener('taskStatusUpdated', this.onTaskStatusUpdated);

			if (this.keepOpenTaskId) {
				this.showDropdownTaskId = parseInt(this.keepOpenTaskId);
				this.keepOpenTaskId = null;
			}
		},

		tryRefreshTasks() {
			if (this.autoRefreshEnabled && !this.editingTaskId && !this.showDropdownTaskId) {
				this.$dispatch('refreshTasks');
			}
		},

		destroy() {
			console.log('Destroying tasksList');
			if (this.autoRefreshTimeout) {
				clearInterval(this.autoRefreshTimeout);
			}

			if (this.sortable) {
				this.sortable.destroy();
			}

			document.body.removeEventListener('taskStatusUpdated', this.onTaskStatusUpdated);
		},

		dueWithinHours: (dueUnixtime, maxHours, minHours) => {
			const now = new Date();
			if (minHours === undefined) {
				minHours = 0;
			}

			return dueUnixtime - (now.getTime() / 1000) <= maxHours * 60 * 60 && dueUnixtime - (now.getTime() / 1000) >= minHours * 60 * 60;
		},
	}));

});
