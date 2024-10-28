<?php
require_once __DIR__ . '/../../init.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="htmx-config" content='{"responseHandling": [{"code":".*", "swap": true}]}' />
	<title>Todos</title>
	<script src="https://unpkg.com/htmx.org@2.0.3"></script>
	<script src="//unpkg.com/alpinejs" defer></script>
	<script src="https://cdn.tailwindcss.com"></script>
	<style>
		[x-cloak] {
			display: none;
		}

		::-webkit-input-placeholder {
			font-style: italic;
		}

		:-moz-placeholder {
			font-style: italic;
		}

		::-moz-placeholder {
			font-style: italic;
		}

		:-ms-input-placeholder {
			font-style: italic;
		}
	</style>
	<script src="https://unpkg.com/htmx.org@1.9.12/dist/ext/alpine-morph.js"></script>
	<script src="/assets/js/Sortable.js"></script>
	<script src="/assets/js/tasksListContainer.js"></script>
	<script src="/assets/js/tasksList.js"></script>

	<link href='/apple-touch-icon.png' rel='apple-touch-icon' type='image/png'>
	<link rel="manifest" href="/assets/site.webmanifest">
	<link rel="icon" type="image/png" sizes="32x32" href="/assets/icons/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/assets/icons/favicon-16x16.png">
	<link rel="shortcut icon" href="/favicon.ico">
</head>

<body class="bg-gray-100 lg:p-8 p-2 <?php if (!$tvMode): ?>lg:w-[800px] lg:mx-auto<?php endif; ?>">
	<?php if (!$tvMode): ?>
		<h1 class=" pt-4 text-3xl font-bold mb-6 text-center text-blue-600">Todo List</h1>

		<div class="flex justify-center mb-4">
			<a class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded" href="/">
				Tasks
			</a>
			<a class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded ml-2" href="/settings.php">
				Settings
			</a>
		</div>
	<?php endif; ?>