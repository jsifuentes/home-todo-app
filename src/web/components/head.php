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
	<script src="https://cdn.jsdelivr.net/npm/@alpinejs/focus@3.x.x/dist/cdn.min.js"></script>
	<script src="//unpkg.com/alpinejs" defer></script>
	<script src="https://cdn.tailwindcss.com"></script>
	<script src="https://unpkg.com/htmx.org@1.9.12/dist/ext/alpine-morph.js"></script>
	<script src="/assets/js/Sortable.js"></script>
	<script src="/assets/js/tasksListContainer.js"></script>
	<script src="/assets/js/tasksList.js"></script>


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

		.loading {
			width: 50px;
			aspect-ratio: 1;
			display: grid;
			border-radius: 50%;
			background:
				linear-gradient(0deg ,rgb(0 0 0/50%) 30%,#0000 0 70%,rgb(0 0 0/100%) 0) 50%/8% 100%,
				linear-gradient(90deg,rgb(0 0 0/25%) 30%,#0000 0 70%,rgb(0 0 0/75% ) 0) 50%/100% 8%;
			background-repeat: no-repeat;
			animation: l23 1s infinite steps(12);
			margin: 0 auto;
		}
		.loading::before,
		.loading::after {
			content: "";
			grid-area: 1/1;
			border-radius: 50%;
			background: inherit;
			opacity: 0.915;
			transform: rotate(30deg);
		}
		.loading::after {
			opacity: 0.83;
			transform: rotate(60deg);
		}
		@keyframes l23 {
			100% {transform: rotate(1turn)}
		}
	</style>

	<link href='/apple-touch-icon.png' rel='apple-touch-icon' type='image/png'>
	<link rel="manifest" href="/assets/site.webmanifest">
	<link rel="icon" type="image/png" sizes="32x32" href="/assets/icons/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/assets/icons/favicon-16x16.png">
	<link rel="shortcut icon" href="/favicon.ico">
</head>

<body class="bg-gray-100 lg:p-8 p-2 <?php if (!$tvMode): ?>lg:w-[800px] lg:mx-auto<?php endif; ?>">
	<?php if (!$tvMode): ?>
		<h1 class="pt-4 text-3xl font-bold mb-2 text-center text-blue-600">Todo List</h1>

		<div class="flex justify-center mb-4">
			<a class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded" href="/">
				Tasks
			</a>
			<a class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded ml-2" href="/settings.php">
				Settings
			</a>
		</div>
	<?php endif; ?>