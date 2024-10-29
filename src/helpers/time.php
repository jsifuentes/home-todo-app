<?php

function time2str($timestamp)
{
	if (!ctype_digit($timestamp)) {
		$timestamp = strtotime($timestamp);
	}

	$diff = time() - $timestamp;
	$absDiff = abs($diff);
	$dayDiff = floor($absDiff / 86400);

	if ($diff == 0) {
		return 'just now';
	}

	if ($diff > 0) {
		return getPastTimeString($diff, $dayDiff, $timestamp);
	} else {
		return getFutureTimeString($absDiff, $dayDiff, $timestamp);
	}
}

function getPastTimeString($diff, $dayDiff, $timestamp)
{
	if ($dayDiff == 0) {
		if ($diff < 60) return 'just now';
		if ($diff < 120) return '1 minute ago';
		if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
		if ($diff < 7200) return '1 hour ago';
		if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
	}

	if ($dayDiff == 1) return 'Yesterday';
	if ($dayDiff < 7) return $dayDiff . ' days ago';
	if ($dayDiff < 31) return ceil($dayDiff / 7) . ' weeks ago';
	if ($dayDiff < 60) return 'last month';
	return date('F Y', $timestamp);
}

function getFutureTimeString($diff, $dayDiff, $timestamp)
{
	if ($dayDiff == 0) {
		// if ($diff < 120) return 'in a minute';
		// if ($diff < 3600) return 'in ' . floor($diff / 60) . ' minutes';
		// if ($diff < 7200) return 'in an hour';
		// if ($diff < 86400) return 'in ' . floor($diff / 3600) . ' hours';
		return 'end of day today';
	}
	if ($dayDiff == 1) return 'Tomorrow';
	if ($dayDiff < 4) return date('l', $timestamp);
	if ($dayDiff < 7 + (7 - date('w'))) return 'next ' . date('l', $timestamp);
	if (ceil($dayDiff / 7) < 4) return 'in ' . ceil($dayDiff / 7) . ' weeks';
	if (date('n', $timestamp) == date('n') + 1) return 'next month';
	return date('F Y', $timestamp);
}

function calculateEndDate(string $incrementer, DateTime $start = new DateTime()): DateTime
{
	$endDueDate = clone $start;

	$lastChar = substr($incrementer, -1);
	$amount = intval(substr($incrementer, 0, -1));

	switch ($lastChar) {
		case 'd':
			$endDueDate->modify("+$amount days");
			break;
		case 'm':
			$endDueDate->modify("+$amount months");
			break;
		case 'y':
			$endDueDate->modify("+$amount years");
			break;
		default:
			throw new InvalidArgumentException("Invalid due date format.");
	}

	return $endDueDate;
}
