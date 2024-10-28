<?php
require_once __DIR__ . '../../../init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	exit('Method not allowed');
}

$name = trim($_POST['name'] ?? '');
$isDefault = isset($_POST['is_default']) ? 1 : 0;

if (empty($name)) {
	echo renderError('Category name is required');
	exit;
}

// If this is set as default, unset any existing default
if ($isDefault) {
	$db->exec("UPDATE categories SET is_default = 0 WHERE is_default = 1");
}

$stmt = $db->prepare("INSERT INTO categories (name, is_default) VALUES (:name, :is_default)");
$stmt->bindValue(':name', $name);
$stmt->bindValue(':is_default', $isDefault);
$stmt->execute();

header('HX-Trigger: categoryAdded');
echo renderSuccess('Category created successfully');