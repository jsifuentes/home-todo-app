<?php

$settingsConfig = [
	'timezone' => [
		'label' => 'Timezone',
		'description' => 'The timezone to use when comparing against due dates.',
		'default' => 'America/New_York',
		'options' => function () {
			return DateTimeZone::listIdentifiers(DateTimeZone::ALL);
		},
	],
	'end_of_day' => [
		'label' => 'End of Day',
		'description' => 'The time of day to consider the end of the day.',
		'default' => '21:00', // 9pm
		'validator' => function ($value) {
			if (!preg_match('/^\d{2}:\d{2}$/', $value)) {
				throw new ValidatorException('Invalid time format');
			}
		},
	],
];
