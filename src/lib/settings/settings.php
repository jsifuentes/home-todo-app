<?php

function getSettingsFromDatabase(): array
{
	global $db;

	$settings = $db->query("SELECT * FROM settings");
	$result = [];
	while ($setting = $settings->fetchArray(SQLITE3_ASSOC)) {
		$result[$setting['key']] = $setting['value'];
	}

	return $result;
}

function mergeSettingsFromDatabaseWithDefaults(): array
{
	global $settingsConfig;

	$settings = getSettingsFromDatabase();

	foreach ($settingsConfig as $key => $config) {
		if (!isset($settings[$key])) {
			$settings[$key] = $config['default'] ?? null;
		}
	}

	return $settings;
}
