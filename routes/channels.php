<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;


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

// Broadcast::channel('chat.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });

Broadcast::channel('chat.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Broadcast::channel('task.{userId}.{roles}', function($user, $userId, $roles){
//      if($roles ==  'unit heads'){
//         return (int) $user->id == (int)$userId;
//     }
//     if($roles == 'division heads' ){
//         return (int) $user->id == (int)$userId;
//     }
//     if($roles == 'directors' ){
//         return (int) $user->id == (int)$userId;
//     }
//     return (int) $user->id === (int) $userId;
// });

Broadcast::channel('task.{userId}', function($user, $userId){
  
   return (int) $user->id === (int) $userId;
});

Broadcast::channel('registry.{sentToId}.{branchType}', function (User $user, $sentToId, $branchType) {
    if($branchType == 'unit'){
        return (int) $user->unit_id == $sentToId;
    }
    if($branchType == 'division'){
        return (int) $user->division_id == $sentToId;
    }
    if($branchType == 'department'){
        return (int) $user->department_id == $sentToId;
    }
    // return (int) $user->id === (int) $id;
});
