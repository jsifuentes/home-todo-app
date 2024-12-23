<?php include __DIR__ . '/components/head.php'; ?>
<div id="content-container">
	<div id="category_admin" hx-get="/views/category_admin.php" hx-trigger="load, categoryAdded from:body, categoriesUpdated from:body"></div>

	<div id="recurring_tasks_admin" hx-get="/views/recurring_tasks_admin.php" hx-trigger="load, recurringTasksUpdated from:body"></div>

	<div id="settings_admin" hx-get="/views/settings_admin.php" hx-trigger="load, settingAdded from:body, settingsUpdated from:body"></div>
</div>

<?php
$additionalScripts[] = '/assets/js/recurringTasksEditForm.js';
include __DIR__ . '/components/foot.php';
