document.addEventListener('alpine:init', () => {
	Alpine.data('tasksList', () => ({
		refreshTasksTimeout: null,
		editingTaskId: null,
		showDropdownTaskId: null,

		init() {
			new Sortable(document.getElementById('todo-list-notDone'), {
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
						})
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
					htmx.process(htmx.find('.edit-task-form'))
				}
			});

			setInterval(() => {
				if (this.autoRefreshEnabled && !this.editingTaskId && !this.showDropdownTaskId) {
					this.$dispatch('refreshTasks');
				}
			}, 5000);
		},

		updateStatus: (event, taskId) => {
			const newStatus = event.target.checked ? 'done' : 'todo';
			fetch('/api/update_status.php', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded',
					},
					body: `task_id=${taskId}&new_status=${newStatus}`
				})
				.then(data => {
					console.log('Status updated successfully');
					// Trigger a refresh of the task list on htmx
					if (this.refreshTasksTimeout) {
						clearTimeout(this.refreshTasksTimeout);
					}

					this.refreshTasksTimeout = setTimeout(() => {
						document.body.dispatchEvent(new Event('refreshTasks'));
					}, 1000);
				})
				.catch(error => {
					console.error('Error:', error);
					event.target.checked = !event.target.checked; // Revert the checkbox state
				});
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
