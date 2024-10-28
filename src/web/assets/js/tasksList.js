document.addEventListener('alpine:init', () => {
	Alpine.data('tasksList', () => ({
		formVisible: false,

		init: () => {
			new Sortable(document.getElementById('todo-list-notDone'), {
				animation: 150,
				ghostClass: 'bg-gray-100',
				handle: '.handle-button',
				onEnd: function(evt) {
					// get all of the items in current order
					const items = Array.from(this.el.children);
			
					const formData = new FormData();
					formData.append('task_ids_in_order', JSON.stringify(items.filter(x => !x.classList.contains('done')).map(x => x.getAttribute('data-id'))));
			
					fetch('/api/update-sort-order.php', {
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
		},

		updateStatus: (event, taskId) => {
			const newStatus = event.target.checked ? 'done' : 'todo';
			fetch('/api/update-status.php', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded',
					},
					body: `taskId=${taskId}&newStatus=${newStatus}`
				})
				.then(data => {
					console.log('Status updated successfully');
					// Trigger a refresh of the task list on htmx
					document.body.dispatchEvent(new Event('refreshTasks'));
				})
				.catch(error => {
					console.error('Error:', error);
					event.target.checked = !event.target.checked; // Revert the checkbox state
				});
		},

		dueWithinHours: (dueUnixtime, hours) => {
			const now = new Date();
			return dueUnixtime - (now.getTime() / 1000) <= hours * 60 * 60;
		},
	}));

});
