document.addEventListener('alpine:init', () => {
	Alpine.data('tasksListContainer', (dueDatesConfig, priorityLevelsConfig, defaultCategoryId) => ({
		formVisible: false,
		filteredCategory: null,
		refreshTasksTimeout: null,
		autoRefreshEnabled: true,

		// For the add task form
		priorityLevelsConfig: priorityLevelsConfig,
		dueDatesConfig: dueDatesConfig,
		dueDateRangeChoices: Object.keys(dueDatesConfig),
		newTaskPriority: priorityLevelsConfig.normal,
		newTaskCategory: defaultCategoryId,
		newTaskDueDateRangeChoice: null,
		newTaskRecurringToggle: false,
		newTaskRecurrenceUnit: 'd',
		newTaskRecurrenceDayOfMonth: null,
		clearSuccessMessageTimeout: null,
		addTaskFormResult: null,

		keepOpenTaskId: null,

		init() {
			this.newTaskDueDateRangeChoice = this.dueDateRangeChoices.indexOf('7d');

			this.$watch('formVisible', (newVal) => {
				if (!newVal) {
					document.getElementById('add-task-form').innerHTML = '';
				}
			});

			this.$watch('filteredCategory', (newVal) => {
				this.newTaskCategory = newVal;

				this.$dispatch('refreshTasks');
			});

			this.$watch('newTaskPriority', new_value => { 
				if (new_value === this.priorityLevelsConfig.high) {
					this.newTaskDueDateRangeChoice = this.dueDateRangeChoices.indexOf('1d');
				} else if (new_value === this.priorityLevelsConfig.normal) {
					this.newTaskDueDateRangeChoice = this.dueDateRangeChoices.indexOf('7d');
				} else if (new_value === this.priorityLevelsConfig.low) {
					this.newTaskDueDateRangeChoice = this.dueDateRangeChoices.indexOf('1m');
				}
			});

			// when a taskCreated event is emitted on the htmx body, then refresh the tasks list
			// and hide the form
			document.body.addEventListener('taskCreated', () => {
				this.$dispatch('refreshTasks');

				// wait 1 second before removing the success message
				if (this.clearSuccessMessageTimeout) {
					clearTimeout(this.clearSuccessMessageTimeout);
				}

				this.clearSuccessMessageTimeout = setTimeout(() => {
					document.getElementById('add-task-form-result').innerHTML = '';
				}, 2000);
			});

			document.body.addEventListener('tasksUpdated', (evt) => {
				if (this.refreshTasksTimeout) {
					clearTimeout(this.refreshTasksTimeout);
				}

				this.$dispatch('refreshTasks');

				if (evt.detail.keepOpen) {
					this.keepOpenTaskId = evt.detail.keepOpen;
				}
			});
		},

		randomTaskTitle() {
			let householdItems = [
				'Milk',
				'Toilet paper',
				'Paper towels',
				'Dish soap',
				'Couch',
				'Vacuum',
				'Clean the bathroom floor',
				'Clean the kitchen floor',
				'Clean the living room floor',
				'Clean the bedroom floor',
				'Clean the hallway floor',
				'Clean the windows',
				'Take out trash',
				'Do laundry',
				'Change bed sheets',
				'Clean mirrors',
				'Organize closet',
				'Clean refrigerator',
				'Wipe kitchen counters',
				'Empty dishwasher',
				'Water plants',
				'Replace air filters',
				'Clean microwave',
				'Clean ceiling fans',
				'Organize pantry',
				'Clean oven',
				'Declutter drawers',
				'Clean shower drain',
				'Wash pet bedding',
				'Clean doorknobs',
				'Organize garage',
				'Clean baseboards',
				'Check smoke detectors'
			];

			return householdItems[Math.floor(Math.random() * householdItems.length)];
		}
	}));

});
