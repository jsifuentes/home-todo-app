<?php
require_once __DIR__ . '/../../init.php';

$categoryId = $_POST['category_id'] ?? null;
$name = $_POST['name'] ?? null;
$isDefault = $_POST['is_default'] ?? false;

if (!$categoryId || !is_numeric($categoryId)) {
	http_response_code(400);
	echo renderError('Category ID is required');
	exit;
}

// Check if category exists
$stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->bindValue(1, $categoryId, SQLITE3_INTEGER);
$result = $stmt->execute();
$category = $result->fetchArray(SQLITE3_ASSOC);

if (!$category) {
	http_response_code(404);
	echo renderError('Category not found');
	exit;
}

$db->exec('BEGIN');

try {
	if ($name) {
		$stmt = $db->prepare("UPDATE categories SET name = ? WHERE id = ?");
		$stmt->bindValue(1, $name, SQLITE3_TEXT);
		$stmt->bindValue(2, $categoryId, SQLITE3_INTEGER);
		$stmt->execute();
	}

	if ($isDefault) {
		$db->exec("UPDATE categories SET is_default = 1 WHERE id = $categoryId");
		// Unset default for all other categories
		$db->exec("UPDATE categories SET is_default = 0 WHERE id != $categoryId");
	}

	$db->exec('COMMIT');

	header('HX-Trigger: categoriesUpdated');
	echo renderSuccess('Default category updated successfully');
} catch (Exception $e) {
	$db->exec('ROLLBACK');
	http_response_code(500);
	echo renderError($e->getMessage());
}
