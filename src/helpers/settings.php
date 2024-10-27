<?php

function getSetting($key): ?string
{
	global $db;
	$stmt = $db->prepare("SELECT value FROM settings WHERE key = ?");
	$stmt->bindValue(1, $key, SQLITE3_TEXT);
	$result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
	if (!$result) {
		return null;
	}

	return $result['value'];
}
