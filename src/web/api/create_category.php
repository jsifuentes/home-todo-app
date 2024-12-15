<?php
require_once __DIR__ . '../../../init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	exit('Method not allowed');
}

$name = trim($_POST['name'] ?? '');
$isDefault = isset($_POST['is_default']) ? 1 : 0;

if (empty($name)) {
	sendSimpleErrorNotificationTrigger('Category name is required');
	exit;
}

$db->exec('BEGIN');

try {
	// If this is set as default, unset any existing default
	if ($isDefault) {
		$db->exec("UPDATE categories SET is_default = 0 WHERE is_default = 1");
	}

	$stmt = $db->prepare("INSERT INTO categories (name, is_default) VALUES (:name, :is_default)");
	$stmt->bindValue(':name', $name);
	$stmt->bindValue(':is_default', $isDefault);
	$stmt->execute();

	$db->exec('COMMIT');

	header('HX-Trigger: ' . json_encode([
		'categoryAdded' => [],
		'addNotification' => [
			'type' => 'success',
			'message' => 'Category added successfully!',
		]
	]));
} catch (Exception $e) {
	$db->exec('ROLLBACK');

	http_response_code(500);
	sendSimpleErrorNotificationTrigger('Failed to create category: ' . $e->getMessage());
	echo $e->getMessage();
}

