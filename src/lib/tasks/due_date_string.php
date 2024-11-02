<?php

function getRelativeDueDateString(int|string|DateTime $timestamp): string
{
	if (is_string($timestamp) && !ctype_digit($timestamp)) {
		$dateTime = new DateTime($timestamp);
	} elseif (is_numeric($timestamp)) {
		$dateTime = (new DateTime())->setTimestamp($timestamp);
	} elseif ($timestamp instanceof DateTime) {
		$dateTime = clone $timestamp;
	} else {
		throw new InvalidArgumentException('Invalid timestamp format');
	}

	$now = new DateTime();
	$dueDateTime = changeDateTimeToLocalEndOfDay($dateTime);
	$diff = $dateTime->getTimestamp() - $now->getTimestamp();
	$absDiff = abs($diff);
	$dayDiff = (int)floor($absDiff / 86400);

	// is the task due in the future still?
	// the task due time will be the configured end of day time.
	// so if task is still due 'in the future', then we have not passed the end of the day yet.

	$isDueToday = isDueToday($dueDateTime);
	if ($isDueToday) {
		return 'end of day today';
	}

	if ($diff > 0 && $dayDiff === 0 && !$isDueToday) {
		return 'end of day tomorrow';
	}

	// is the task due in the future?
	if ($diff > 0) {
		return getFutureTimeString($dueDateTime, $now);
	} else {
		return getPastTimeString($dueDateTime, $now, false);
	}
}
