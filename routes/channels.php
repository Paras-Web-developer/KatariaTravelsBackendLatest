<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Broadcast::channel('chat.{roomId}', function ($user, $roomId) {
//     return true; // Add your logic to authorize the user for this channel
// });

Broadcast::channel('chat.{receiverId}', function ($user, $receiverId) {
	return (int) $user->id === (int) $receiverId || (int) $user->id !== null;
});

Broadcast::channel('followup-channel.{receiverId}', function ($user, $receiverId) {
	return (int) $user->id === (int) $receiverId || (int) $user->id !== null;
});

Broadcast::channel('my-channel.{receiverId}', function ($user, $receiverId) {
	return (int) $user->id === (int) $receiverId || (int) $user->id !== null;
});
