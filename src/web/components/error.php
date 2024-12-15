<?php

function sendSimpleErrorNotificationTrigger($message)
{
    header('HX-Trigger: ' . json_encode([
        'addNotification' => [
            'type' => 'error',
            'message' => $message,
        ]
    ]));
}
