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
| meetings.{meetingId} là public channel — không cần xác thực.
| Frontend subscribe trực tiếp qua Pusher mà không cần auth endpoint.
|
*/

Broadcast::channel('meetings.{meetingId}', function () {
    return true;
});
