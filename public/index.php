<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__ . '/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__ . '/../bootstrap/app.php')
    ->handleRequest(Request::capture());

$options = array(
    'cluster' => 'ap2',
    'useTLS' => true
);

// $pusher = new Pusher\Pusher(
//     '91c8a0fa751bddeef247',
//     '371e7ab4c09707bb4d9c',
//     '1919831',
//     $options
// );
$pusher = new Pusher\Pusher(
    env('PUSHER_APP_KEY'),
    env('PUSHER_APP_SECRET'),
    env('PUSHER_APP_ID'),
    $options
);


// // $data['message'] = 'hello world hereee';
// $pusher->trigger('my-channel', 'my-event', $data);
