<?php

return function ($db) {
	$db->exec("ALTER TABLE tasks ADD COLUMN due_date_increment_selected INTEGER AFTER due_date");
};
