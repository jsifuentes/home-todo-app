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
		return getPastTimeString(new DateTime('@' . $timestamp), new DateTime('now'));
	} else {
		return getFutureTimeString(new DateTime('@' . $timestamp), new DateTime('now'));
	}
}

function getPastTimeString(DateTime $tested, DateTime $against = null)
{
	$diff = $tested->diff($against);

	if ($diff->days == 0) {
		if ($diff->i === 0) {
			if ($diff->s < 60) return 'just now';
		}
		if ($diff->h === 0) {
			if ($diff->i === 1) return '1 minute ago';
			if ($diff->i < 60) return $diff->i . ' minutes ago';
		}
		if ($diff->h === 1) return '1 hour ago';
		if ($diff->h < 24) return $diff->h . ' hours ago';
	}

	if ($diff->days == 1) return 'yesterday';
	if ($diff->days < 7) return $diff->days . ' days ago';
	if ($diff->days < 31) return ceil($diff->days / 7) . ' weeks ago';
	if ($diff->days < 60) return 'last month';
	return $against->format('F Y');
}

function getFutureTimeString(DateTime $tested, DateTime $against)
{
	// make them the same time to get a real days difference
	$tested = changeDateTimeToLocalEndOfDay($tested);
	$against = changeDateTimeToLocalEndOfDay($against);

	$diff = $tested->diff($against);

	if ($diff->days == 0) return 'end of day today';
	if ($diff->days == 1) return 'end of day tomorrow';
	if ($diff->days < 5) return $tested->format('l');
	if ($diff->days < 7 + (7 - $tested->format('w'))) return 'next ' . $tested->format('l');
	if (ceil($diff->days / 7) < 4) return 'in ' . ceil($diff->days / 7) . ' weeks';
	if ($tested->format('n') == $tested->format('n') + 1) return 'next month';
	return $tested->format('F Y');
}

function calculateEndDate(string $incrementer, DateTime $start = null): DateTime
{
	global $settings;

	if ($start === null) {
		$start = new DateTime('now', new DateTimeZone($settings['timezone']));
	} else {
		$start = changeDateTimeToLocalEndOfDay($start);
	}

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

	$endDueDate->setTime(0, 0, 0); // set the time to midnight. the time will always come from the $settings['end_of_day'] setting when needed.

	$endDueDate->setTimezone(new DateTimeZone('UTC')); // UTC is the default timezone for the database

	return $endDueDate;
}

function convertToUTC(DateTime $dateTime): DateTime
{
	$dateTime->setTimezone(new DateTimeZone('UTC'));
	return $dateTime;
}

function convertToLocalTime(DateTime $dateTime): DateTime
{
	global $settings;

	$dateTime->setTimezone(new DateTimeZone($settings['timezone']));
	return $dateTime;
}

function datetimeToLocalDateTime(DateTime|string $timestamp): DateTime
{
	global $settings;

	if (is_string($timestamp)) {
		$ts = new DateTime($timestamp);
	} else {
		$ts = clone $timestamp;
	}

	$ts->setTimezone(new DateTimeZone($settings['timezone']));
	return $ts;
}

function isDueToday(DateTime|string $dueDate): bool
{
	return datetimeToLocalDateTime($dueDate)->format('Y-m-d') === datetimeToLocalDateTime('now')->format('Y-m-d');
}

function isPastDue(DateTime|string $dueDate): bool
{
	if (is_string($dueDate)) {
		$dueDate = datetimeToLocalDateTime($dueDate);
	}

	changeDateTimeToLocalEndOfDay($dueDate);

	return $dueDate->getTimestamp() < datetimeToLocalDateTime('now')->getTimestamp();
}

function isDueWithinDays(DateTime|string $dueDate, int $maxDays, int $minDays = 0): bool
{
	$dueDateTime = datetimeToLocalDateTime($dueDate);
	$nowDateTime = datetimeToLocalDateTime('now');
	changeDateTimeToLocalEndOfDay($dueDateTime);
	$diffInSeconds = $dueDateTime->getTimestamp() - $nowDateTime->getTimestamp();

	return $diffInSeconds <= $maxDays * 86400 && $diffInSeconds >= $minDays * 86400;
}

function changeDateTimeToLocalEndOfDay(DateTime $dt): DateTime
{
	global $settings;

	$eodTime = explode(':', $settings['end_of_day']);
	$dt->setTimezone(new DateTimeZone($settings['timezone']));
	$dt->setTime($eodTime[0], $eodTime[1], 0);
	return $dt;
}
