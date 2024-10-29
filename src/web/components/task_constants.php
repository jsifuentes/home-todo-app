<?php

const TASK_STATUS_TODO = 'todo';
const TASK_STATUS_DONE = 'done';

const PRIORITY_LOW = 2;
const PRIORITY_NORMAL = 1;
const PRIORITY_HIGH = 0;

const PRIORITY_LABELS = [
	PRIORITY_LOW => 'Lower Priority',
	PRIORITY_NORMAL => 'Normal Priority',
	PRIORITY_HIGH => 'Need to do soon',
];

const DUE_DATE_LABELS = [
	'1d' => '1 day',
	'3d' => '3 days',
	'7d' => '7 days',
	'14d' => '14 days',
	'1m' => '1 month',
	'3m' => '3 months',
	'6m' => '6 months',
	'1y' => '1 year',
];

DEFINE('DUE_DATE_RANGE_CHOICES', array_keys(DUE_DATE_LABELS));
