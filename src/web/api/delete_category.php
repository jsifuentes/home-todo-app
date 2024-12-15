<?php
require_once '../../init.php';
require_once '../components/error.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
	http_response_code(405);
	echo "Invalid request method. Please use DELETE.";
	exit;
}

$categoryId = $_REQUEST['categoryId'] ?? '';

if (empty($categoryId) || !is_numeric($categoryId)) {
	http_response_code(400);
	echo "Invalid category ID.";
	exit;
}

// Check if category is default
$stmt = $db->prepare("SELECT is_default FROM categories WHERE id = ?");
$stmt->bindValue(1, $categoryId, SQLITE3_INTEGER);
$result = $stmt->execute();
$category = $result->fetchArray(SQLITE3_ASSOC);

if ($category && $category['is_default']) {
	http_response_code(400);
	echo "Cannot delete the default category.";
	exit;
}

// Begin transaction
$db->exec('BEGIN');

try {
	// Update tasks in this category to use the default category
	$stmt = $db->prepare("UPDATE tasks SET category_id = null WHERE category_id = ?");
	$stmt->bindValue(1, $categoryId, SQLITE3_INTEGER);
	$stmt->execute();

	// Delete the category
	$stmt = $db->prepare("DELETE FROM categories WHERE id = ? AND is_default = 0");
	$stmt->bindValue(1, $categoryId, SQLITE3_INTEGER);
	$result = $stmt->execute();

	if (!$result) {
		throw new Exception("Failed to delete category: " . $db->lastErrorMsg());
	}

	$db->exec('COMMIT');

	header('HX-Trigger: ' . json_encode([
		'categoriesUpdated' => [],
		'addNotification' => [
			'type' => 'success',
			'message' => 'Category deleted successfully!',
		]
	]));
} catch (Exception $e) {
	$db->exec('ROLLBACK');
	http_response_code(500);
	sendSimpleErrorNotificationTrigger("Failed to delete category: " . $e->getMessage());
	echo $e->getMessage();
}
