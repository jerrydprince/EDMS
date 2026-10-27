<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;
use App\Models\Department;
use App\Models\Division;
use App\Models\Unit;


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

Broadcast::channel('chat-requests.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('comment.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id; 
});

Broadcast::channel('group.{groupId}.{groupType}', function ($user, $groupId, $groupType) {
    if (!$user) return false;
    if ($user->hasRole('super admin') || $user->hasRole('admin') || $user->hasRole('Accountant-General') || $user->hasRole('super user')) {
        return true;
    }

    if ($groupType === 'department') {
        return (int)$user->department_id === (int)$groupId || $user->hasRole('director');
    }

    if ($groupType === 'division') {
        $division = Division::find($groupId);
        return (int)$user->division_id === (int)$groupId || ((int)$user->department_id === (int)optional($division)->department_id);
    }

    if ($groupType === 'unit') {
        return (int)$user->unit_id === (int)$groupId;
    }

    return true;
});

Broadcast::channel('chat.presence', function ($user) {
    if ($user) {
        return [
            'id' => (int)$user->id,
            'name' => $user->first_name . ' ' . $user->last_name,
            'first_name' => $user->first_name,
            'profile_image' => $user->profile_image
        ];
    }
    return false;
});

Broadcast::channel('pressence.{userId}', function($user, $userId){
    return (int)$user->id == $userId;
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

Broadcast::channel('chat-requests.{userId}', function ($user, $userId) { return (int) $user->id === (int) $userId; });
