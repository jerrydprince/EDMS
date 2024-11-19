<?php

namespace App\Http\Controllers;

use App\Events\GroupMessageSent;
use App\Events\MessageSent;
use App\Models\categories;
use App\Models\ChatDepartment;
use App\Models\ChatDivision;
use App\Models\ChatGroup;
use App\Models\ChatMessage;
use App\Models\NewAttachment;
use App\Models\ChatUnit;
use App\Models\ChatUser;
use App\Models\GroupMessages;
use App\Models\Groups;
use App\Models\User;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Unit;
use App\Models\Division;
use App\Models\Department;

class ChatController extends Controller
{
    public function chat()
    {
        // return $request->header();
        return Inertia::render('chat/chat');
    }
    public function login()
    {
        //return Inertia::render('chat/Login');
        return view('login');
    }
    public function createCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category = categories::create([
            'name' => $request->name,
        ]);

        return response()->json(['message' => 'Category created successfully!', 'category' => $category], 201);
    }
    public function getGroupWithConversations($category, $groupId)
    {
        $group = Groups::with(['users', 'messages.user'])->where('category', $category)->findOrFail($groupId);

        return response()->json($group, 200);
    }
    public function getGroupsByCategory($category)
    {
        $groups = Groups::with('users')->where('category', $category)->get();

        return response()->json($groups, 200);
    }
    public function getAllCategories()
    {
        //     $categories = categories::with(['groups.users', 'groups.messages'])->get();
        //    return response()->json($categories, 200);
        $loggedInUserId = Auth::id();

        $categories =  categories::with(['groups' => function ($query) use ($loggedInUserId) {
            // Filter groups to include only those where the logged-in user is a member
            $query->whereHas('users', function ($userQuery) use ($loggedInUserId) {
                $userQuery->where('user_id', $loggedInUserId);
            })->with('users'); // Load all users in the group
        }])->get();

        return response()->json($categories, 200);
    }
    public function createGroup(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
        ]);

        $group = Groups::create([
            'name' => $request->name,
            'category_id' => $request->category_id,
        ]);

        return response()->json(['message' => 'Group created successfully!', 'group' => $group], 201);
    }
    public function getGroupMessages($groupId, $groupType)
    {
        $loggedInUserId = Auth::id();
        $branch = "";
        if($groupType =="department"){
            $branch = Department::with(['groupMessages'])->find($groupId);
        }
        if($groupType =="division"){
            $branch = Division::with(['groupMessages'])->find($groupId);
        }
        if($groupType =="unit"){
            $branch = Unit::with(['groupMessages'])->find($groupId);
        }

        // $group = Groups::with('messages.user')->find($groupId);
        // if (!$group->users->contains(Auth::id())) {
        //     return response()->json(['message' => 'User not a member of this group'], 403);
        // }
        // $formattedMessages = $branch->groupMessages->groupBy(function ($message) {
        //     return Carbon::parse($message->created_at)->format('Y-m-d');
        // });

        // $formattedMessages = $formattedMessages->map(function ($dayMessages, $date) use ($loggedInUserId) {
        //     return [
        //         'date' => $this->formatDate($date),
        //         'messages' => $dayMessages->map(function ($message) use ($loggedInUserId) {
        //             return [
        //                 'id' => $message->id,
        //                 'sender_name' => $message->user->first_name." ".$message->user->last_name, // Assuming 'first_name' is the column in the users table
        //                 'message' => $message->message,
        //                 'timestamp' => $message->created_at->format('H:i'),
        //                 'created_at' => $message->created_at,
        //                 'sender_id' => $message->user->id,
        //                 'group_type' => $message->group_type,
        //                 'group_id' => $message->group_id,
        //                 //'is_sent_by_logged_in_user' => $message->user_id === $loggedInUserId,
        //             ];
        //         }),
        //     ];
        // });

        $messages = $branch->groupMessages()->latest()->paginate(30); // Paginate the original query

        // Format the paginated messages
        $formattedMessages = $messages->groupBy(function ($message) {
            return Carbon::parse($message->created_at)->format('Y-m-d');
        })->map(function ($dayMessages, $date) use ($loggedInUserId) {
            return [
                'date' => $this->formatDate($date),
                'messages' => $dayMessages->map(function ($message) use ($loggedInUserId) {
                    return [
                        'id' => $message->id,
                        'sender_name' => $message->user->first_name." ".$message->user->last_name,
                        'message' => $message->message,
                        'timestamp' => $message->created_at->format('H:i'),
                        'created_at' => $message->created_at,
                        'sender_id' => $message->user->id,
                        'group_type' => $message->group_type,
                        'group_id' => $message->group_id,
                        //'is_sent_by_logged_in_user' => $message->user_id === $loggedInUserId,
                    ];
                }),
            ];
        });

        // Add pagination metadata back if needed
        $formattedMessages = [
            'data' => $formattedMessages,
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
            ],
        ];


        return response()->json($formattedMessages, 200);

        // return response()->json($group->messages);
    }
    public function addUserToGroup(Request $request, $groupId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $group = Groups::findOrFail($groupId);
        $group->users()->attach($request->user_id);

        return response()->json(['message' => 'User added to group successfully!'], 200);
    }

    public function getUserBranch(){
        $user = auth()->user();
        if($user->unit_id){
            return 'unit';
        }
        if($user->division_id){
            return 'division';
        }
        if($user->department_id){
            return 'department';
        }
        if($user->hasRole('super users')){
            return 'super users';
        }
        if($user->hasRole('admin') || $user->hasRole('super admin')){
            return 'admin';
        }
    }

    public function getUserGroups()
    {
        $userId = Auth::id();
        $user = User::find($userId);
        //$groups = $user->groups()->get();
        $branch = $this->getUserBranch();
        $unit = [];
        $division = [];
        $department = [];

        if($branch == 'unit' && !$user->hasRole('unit heads')){
            $unit = Unit::with('users')->where('id', $user->unit_id)->get();
            return response()->json(['unit'=>$unit]);
        }
        if($branch == 'division' && !$user->hasRole('division heads')){
            $division = Division::with(['users'])->where('id', $user->division_id)->get();
            $unit = $division->first()->unit()->with('users')->get();
           return response()->json(['division'=>$division, 'unit'=> $unit]);
        }
        if($branch == 'department' && !$user->hasRole('directors')){
            $department = Department::with(['user'])->where('id', $user->department_id)->get();
           
            return response()->json(['department'=>$department]);
        }

        //branch heads
        if($user->hasRole('unit heads')){
            $unit = Unit::with('users')->where('id', $user->unit_id)->get();
            return response()->json(['unit'=>$unit]);
        }
        if($user->hasRole('division heads')){
            $division = Division::with(['users', 'unit'=>function($query){
                $query->with('users');
            }])->find($user->division_id);
            $unit = $division->unit;

            return response()->json(['unit'=>$unit, 'division'=>$division]);
        }
        if($user->hasRole('directors')){
            $department = Department::with(['user'])->where('id', $user->department_id)->get();
            $division = $department->first()->division()->with(['users', 'unit'=>function($q){
                $q->with('users');
            }])->get();
            $unit = $division->flatMap(function ($div) {
                return $div->unit;
            });
            return response()->json(['unit'=>$unit, 'division'=>$division, 'department'=>$department]);
        }
        if($branch == 'super users' || $branch == 'admin'){
            $unit = Unit::with('users')->get();
            $division = Division::with('users')->get();
            $department = Department::with('user')->get();
        }

       return response()->json(['unit'=>$unit, 'division'=>$division, 'department'=>$department]);
    }
    public function sendMessageToGroup(Request $request)
    {
        $request->validate([
            'group_id' => 'required',
            'message' => 'required|string|max:1000',
        ]);

        // $group = Groups::find($request->group_id);

        // if (!$group->users->contains(Auth::id())) {
        //     return response()->json(['message' => 'User not a member of this group'], 403);
        // }
        $unit_group_id = null;
        $div_group_id = null;
        $dept_group_id = null;
        if($request->groupType == 'unit'){
            $unit_group_id = $request->group_id;
        }
        if($request->groupType == 'division'){
            $div_group_id = $request->group_id;
        }
        if($request->groupType == 'department'){
            $dept_group_id = $request->group_id;
        }

        $message = GroupMessages::create([
            'group_id' => $request->group_id,
            'user_id' => Auth::id(),
            'message' => $request->message,
            'group_type' => $request->groupType,
            'unit_id' => $unit_group_id,
            'division_id' => $div_group_id,
            'department_id' => $dept_group_id,
        ]);
       // $isSentByLoggedInUser = Auth::id() === $message->user->id;
        $MessageArray = [
            'id' => $message->id,
             'sender_name' => $message->user->first_name,
             'sender_id' => $message->user->id,
            'message' => $message->message,
            'timestamp' => $message->created_at->format('H:i'),
            'created_at' => $message->created_at,
            'group_type' => $request->groupType,
            'group_id' => $request->group_id,
            'group_name' => $request->groupName,
            //'is_sent_by_logged_in_user' => $isSentByLoggedInUser,
        ];
        broadcast(new GroupMessageSent($MessageArray, $request->group_id, $request->groupType))->toOthers();
        return response()->json(['message' => 'Message sent to group successfully!', 'sentMessage' => $MessageArray], 200);
    }
    public function sendMessageToUser(Request $request)
    {dd($request->all());
        $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'message' => 'required|string|max:1000',
        ]);
        $message = ChatMessage::create([
            'sender_id' => Auth::id(),
            'recipient_id' => $request->recipient_id,
            'message' => $request->message
        ]);
        $isSentByLoggedInUser = Auth::id() === $message->sender_id;
       
        $formattedMessage = [
            'id' => $message->id,
            'user' => $message->sender->first_name,
            'message' => $message->message,
            'timestamp' => $message->created_at->format('H:i'),
            'created_at' => $message->created_at,
            'recipient_id' => $message->recipient_id,
            'sender'=> auth()->user(),
            'sender_id' => $message->sender_id,
            'is_sent_by_logged_in_user' => $isSentByLoggedInUser
        ];
        // $MessageArray = $formattedMessage->toArray();
        broadcast(new MessageSent($formattedMessage, $message->recipient_id))->toOthers();
        //broadcast(new MessageSent($formattedMessage, $message->sender_id))->toOthers();
        return response()->json(['message' => 'Message sent to user successfully!', 'sentMessage' => $formattedMessage], 200);
    }

    public function getBranchType(){
        $user= auth()->user();
        if($user->unit_id){
            return 'unit';
        }
        if($user->division_id){
            return 'division';
        }
        if($user->department_id){
            return 'department';
        }
    }

    public function getAllUsers(Request $request)
    {
        // return $request->header();
        $loggedInUserId = Auth::id();

        $user = auth()->user();
        $branch_type = $this->getBranchType();

        if($branch_type == 'unit'){
            $users = User::where('unit_id', $user->unit_id)->where('id', '!=', $loggedInUserId)->get();
        }
        if($branch_type == 'division'){
            $users = User::where('division_id', $user->division_id)->where('id', '!=', $loggedInUserId)->get();
        }
        if($branch_type == 'department'){
            $users = User::where('department_id', $user->department_id)->where('id', '!=', $loggedInUserId)->get();
        }
        if(auth()->user()->hasRole('super user')  || auth()->user()->hasRole('admin')){
            $users = User::where('id', '!=', $loggedInUserId)->get();
        }


        // Get all users except the logged-in user
        //$users = User::where('id', '!=', $loggedInUserId)->get();

        // Prepare the response data
        // $usersWithConversations = $users->map(function ($user) use ($loggedInUserId) {
        //     $conversations = ChatMessage::where(function ($query) use ($user, $loggedInUserId) {
        //         $query->where('sender_id', $loggedInUserId)
        //             ->where('recipient_id', $user->id);
        //     })->orWhere(function ($query) use ($user, $loggedInUserId) {
        //         $query->where('sender_id', $user->id)
        //             ->where('recipient_id', $loggedInUserId);
        //     })->orderBy('created_at')->get();

        //     return [
        //         'user' => $user,
        //         'conversations' => $conversations,
        //     ];
        // });
        //return response()->json($usersWithConversations);
        return response()->json($users);
    }
    public function getConversationWithUser($userId)
    {
        $loggedInUserId = Auth::id();

        $otherUser = User::find($userId);

        if (!$otherUser) {
            return response()->json(['error' => 'User does not exist'], 404);
        } else {
            $conversation = ChatMessage::with('sender')->where(function ($query) use ($loggedInUserId, $userId) {
                $query->where('sender_id', $loggedInUserId)->where('recipient_id', $userId);
            })
            ->orWhere(function ($query) use ($loggedInUserId, $userId) {
                $query->where('sender_id', $userId)->where('recipient_id', $loggedInUserId);
            })
            ->orderBy('created_at', 'DESC')->paginate(5);
        }
        $pagination = [
            'current_page' => $conversation->currentPage(),
            'last_page' => $conversation->lastPage(),
            'per_page' => $conversation->perPage(),
            'total' => $conversation->total(),
        ];

        $formattedMessages = $conversation->groupBy(function ($message) {
            return Carbon::parse($message->created_at)->format('Y-m-d');
        })
            ->map(function ($dayMessages, $date) use ($loggedInUserId) {
                return [
                    'date' => $this->formatDate($date),
                    'messages' => $dayMessages->map(function ($message) use ($loggedInUserId) {
                        $isSentByLoggedInUser = $message->sender_id === $loggedInUserId;
                        return [
                            'id' => $message->id,
                            'user' => $message->sender->first_name,
                            'message' => $message->message,
                            'timestamp' => $message->created_at->format('H:i'),
                            'recipient_id' => $message->recipient_id,
                            'sender_id' => $message->sender_id,
                            'is_sent_by_logged_in_user' => $isSentByLoggedInUser,
                            'sender' => $message->sender,
                            'created_at' => $message->created_at,
                            // 'attachments' => $message->attachments->map(function ($attachment) {
                            //     return [
                            //         'id' => $attachment->id,
                            //         'file_path' => $attachment->file_path,
                            //         'created_at' => $attachment->created_at->format('Y-m-d H:i:s'),
                            //     ];
                            // }),
                        ];
                    })->toArray(),
                ];
            })->toArray();
        //broadcast(new MessageSent($formattedMessages, $userId))->toOthers();
        return response()->json([
            'data' => $formattedMessages, // Transformed messages grouped by date
            'pagination' => $pagination,  // Pagination metadata
        ]);
    }


    private function formatDate($date)
    {
        $carbonDate = Carbon::parse($date);
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        if ($carbonDate->isToday()) {
            return 'Today';
        } elseif ($carbonDate->isYesterday()) {
            return 'Yesterday';
        } else {
            return $carbonDate->format('F j, Y'); // Example: 'September 14, 2023'
        }
    }
}
