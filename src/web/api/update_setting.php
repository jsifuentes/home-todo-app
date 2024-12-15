<?php
require_once __DIR__ . '/../../init.php';

$key = $_POST['key'] ?? null;
$value = $_POST['value'] ?? null;

if (!$key || $value === null) {
	http_response_code(400);
	sendSimpleErrorNotificationTrigger('Key and value are required');
	exit;
}

// Is this a real key?
if (!isset($settingsConfig[$key])) {
	http_response_code(400);
	sendSimpleErrorNotificationTrigger('Invalid key');
	exit;
}

// Is there a validator for this setting key?
// If there is, run it.
if (isset($settingsConfig[$key]['validator'])) {
	try {
		$settingsConfig[$key]['validator']($value);
	} catch (ValidatorException $e) {
		http_response_code(400);
		sendSimpleErrorNotificationTrigger(sprintf('Bad setting value for %s: %s', $settingsConfig[$key]['label'], $e->getMessage()));
		exit;
	}
}

$stmt = $db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES (?, ?)");
$stmt->bindValue(1, $key, SQLITE3_TEXT);
$stmt->bindValue(2, $value, SQLITE3_TEXT);
if (!$stmt->execute()) {
	http_response_code(500);
	sendSimpleErrorNotificationTrigger('Failed to update setting: ' . $db->lastErrorMsg());
	exit;
}

header('HX-Trigger: ' . json_encode([
	'settingsUpdated' => [],
	'addNotification' => [
		'type' => 'success',
		'message' => 'Setting updated',
	]
]));
