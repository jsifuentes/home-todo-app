<?php
require_once __DIR__ . '/../init.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Todos</title>
	<script src="https://unpkg.com/htmx.org@1.9.6"></script>
	<script src="//unpkg.com/alpinejs" defer></script>
	<script src="https://cdn.tailwindcss.com"></script>
	<style>
		[x-cloak] {
			display: none;
		}
	</style>
	<script src="https://unpkg.com/htmx.org@1.9.12/dist/ext/alpine-morph.js"></script>
	<script src="/assets/js/Sortable.js"></script>
	<script src="/assets/js/tasksList.js"></script>

	<link href='/apple-touch-icon.png' rel='apple-touch-icon' type='image/png'>
	<link rel="manifest" href="/assets/site.webmanifest">
	<link rel="icon" type="image/png" sizes="32x32" href="/assets/icons/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/assets/icons/favicon-16x16.png">
	<link rel="shortcut icon" href="/favicon.ico">
</head>

<body class="bg-gray-100 lg:p-8 p-2 lg:w-[800px] lg:mx-auto">
	<h1 class=" pt-4 text-3xl font-bold mb-6 text-center text-blue-600">Todo List</h1>

	<div class="flex justify-center mb-4">
		<button class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded" hx-get="/views/tasks_list.php" hx-target="#content-container">
			Tasks
		</button>
		<button class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded ml-2" hx-get="/views/settings.php" hx-target="#content-container">
			Settings
		</button>
	</div>

	<div id="content-container" hx-get="/views/tasks_list.php" :hx-vars="selectedCategory ? 'category_id:' + selectedCategory : ''" hx-trigger="load, refreshTasks from:body" x-data="{ selectedCategory: null }"
		x-init="$watch('selectedCategory', (newVal) => {
			htmx.trigger('body', 'refreshTasks');
		})">
	</div>
</body>

</html>