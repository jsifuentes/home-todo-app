<?php

function getCategories(): array
{
	global $db;
	$categories = $db->query("SELECT * FROM categories ORDER BY is_default DESC, name ASC");
	$result = [];
	while ($category = $categories->fetchArray(SQLITE3_ASSOC)) {
		$result[] = $category;
	}
	return $result;
}
