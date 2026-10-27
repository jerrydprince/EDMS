<?php

namespace App\Http\Controllers;

use App\Events\GroupMessageSent;
use App\Events\MessageSent;
use App\Events\MessagesRead;
use App\Events\MessageEdited;
use App\Models\categories;
use App\Models\ChatDepartment;
use App\Models\ChatDivision;
use App\Models\ChatGroup;
use App\Models\ChatMessage;
use App\Models\ChatAttachment;
use App\Models\NewAttachment;
use App\Models\ChatUnit;
use App\Models\ChatUser;
use App\Models\GroupMessages;
use App\Models\Groups;
use App\Models\User;
use App\Models\File;
use App\Models\Archive;
use App\Models\FilePath;
use App\Models\ArchiveDocs;
use App\Models\Task;
use App\Models\TaskDoc;
use App\Models\TaskAssignment;
use App\Models\RegistryFile;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Unit;
use App\Models\Division;
use App\Models\Department;
use Barryvdh\DomPDF\Facade\Pdf;

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

        $messages = $branch->groupMessages()->with(['attachments', 'replyTo'])->latest()->paginate(30); // Paginate the original query

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
                        'profile_image' => $message->user->profile_image,
                        'message' => $message->message,
                        'timestamp' => $message->created_at->format('H:i'),
                        'created_at' => $message->created_at,
                        'sender_id' => $message->user->id,
                        'group_type' => $message->group_type,
                        'group_id' => $message->group_id,
                        'attachments' => $message->attachments,
                        'reply_to' => $message->replyTo,
                        'is_pinned' => (bool)$message->is_pinned,
                        'is_starred' => (bool)$message->is_starred,
                        'is_deleted' => (bool)$message->is_deleted,
                        'reactions' => $message->reactions ?? [],
                        'is_edited' => (bool)$message->is_edited,
                        'edited_at' => $message->edited_at,
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
        if($user->hasRole('super user')){
            return 'super user';
        }
        if($user->hasRole('admin') || $user->hasRole('super admin')){
            return 'admin';
        }
    }

    public function getUserGroups()
    {
        $user = Auth::user();
        $unit = [];
        $division = [];
        $department = [];

        $roles = $user->roles ? $user->roles->pluck('name')->toArray() : [];
        $isAgf = count(array_intersect($roles, ['super admin', 'super user', 'Accountant-General', 'agf', 'AGF Registry', 'admin'])) > 0
            || str_contains(strtolower($user->designation ?? ''), 'accountant-general')
            || str_contains(strtolower($user->email ?? ''), 'agf');
        $isDirector = in_array('director', $roles) || $user->is_department_head;
        $isDeputyDirector = in_array('deputy director', $roles) || $user->is_division_head;
        $isAssistantDirector = in_array('assistant director', $roles) || $user->is_unit_head;

        if ($isAgf) {
            $department = Department::with(['user', 'users.roles', 'users.department'])->get();
            $division = Division::with(['users.roles', 'users.division'])->get();
            $unit = Unit::with(['users.roles', 'users.unit'])->get();
        } elseif ($isDirector) {
            $department = Department::with(['user', 'users.roles', 'users.department'])->where('id', $user->department_id)->get();
            $division = Division::with(['users.roles', 'users.division'])->where('department_id', $user->department_id)->get();
            $unit = Unit::with(['users.roles', 'users.unit'])->whereHas('division', function($q) use ($user) {
                $q->where('department_id', $user->department_id);
            })->get();
        } elseif ($isDeputyDirector) {
            $department = Department::with(['user', 'users.roles', 'users.department'])->where('id', $user->department_id)->get();
            $division = Division::with(['users.roles', 'users.division'])->where('id', $user->division_id)->get();
            $unit = Unit::with(['users.roles', 'users.unit'])->where('division_id', $user->division_id)->get();
        } elseif ($isAssistantDirector) {
            $department = Department::with(['user', 'users.roles', 'users.department'])->where('id', $user->department_id)->get();
            $division = Division::with(['users.roles', 'users.division'])->where('id', $user->division_id)->get();
            $unit = Unit::with(['users.roles', 'users.unit'])->where('id', $user->unit_id)->get();
        } else {
            if ($user->department_id) {
                $department = Department::with(['user', 'users.roles', 'users.department'])->where('id', $user->department_id)->get();
            }
            if ($user->division_id) {
                $division = Division::with(['users.roles', 'users.division'])->where('id', $user->division_id)->get();
            }
            if ($user->unit_id) {
                $unit = Unit::with(['users.roles', 'users.unit'])->where('id', $user->unit_id)->get();
            }
        }

        return response()->json([
            'unit' => $unit,
            'division' => $division,
            'department' => $department,
        ]);
    }
    public function sendMessageToGroup(Request $request)
    {
        $request->validate([
            'group_id' => 'required',
            'message' => 'nullable|string|max:1000',
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
            'message' => $request->message ?? '',
            'group_type' => $request->groupType,
            'unit_id' => $unit_group_id,
            'division_id' => $div_group_id,
            'department_id' => $dept_group_id,
            'reply_to_id' => $request->reply_to_id,
        ]);
        
        $attachments = [];
        if ($request->hasFile('files')) {
            $files = is_array($request->file('files')) ? $request->file('files') : [$request->file('files')];
            foreach ($files as $file) {
                $ext = $file->getClientOriginalExtension() ?: 'bin';
                $fileName = uniqid() . '_' . time() . '.' . $ext;
                $file->move(public_path('storage/chat_attachments'), $fileName);
                $path = 'chat_attachments/' . $fileName;
                $attachment = \App\Models\ChatAttachment::create([
                    'group_message_id' => $message->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                ]);
                $attachments[] = $attachment;
            }
        }

        if ($request->has('edms_attachments')) {
            $edmsDocs = is_array($request->edms_attachments) ? $request->edms_attachments : json_decode($request->edms_attachments, true);
            if (!empty($edmsDocs)) {
                foreach ($edmsDocs as $edmsDoc) {
                    $attachment = \App\Models\ChatAttachment::create([
                        'group_message_id' => $message->id,
                        'file_path' => $edmsDoc['file_path'],
                        'file_name' => $edmsDoc['file_name'] ?? ($edmsDoc['title'] ?? 'Document'),
                        'file_type' => $edmsDoc['file_type'] ?? 'application/pdf',
                        'edms_file_id' => $edmsDoc['edms_file_id'] ?? null,
                        'metadata' => [
                            'is_edms' => true,
                            'title' => $edmsDoc['title'] ?? $edmsDoc['file_name'],
                            'doc_no' => $edmsDoc['doc_no'] ?? null,
                            'classification' => $edmsDoc['classification'] ?? 'Standard',
                            'department' => $edmsDoc['department'] ?? null,
                            'size' => $edmsDoc['size'] ?? null,
                        ],
                    ]);
                    $attachments[] = $attachment;
                }
            }
        }

       // $isSentByLoggedInUser = Auth::id() === $message->user->id;
        $MessageArray = [
            'id' => $message->id,
             'sender_name' => $message->user->first_name,
             'profile_image' => $message->user->profile_image,
             'sender_id' => $message->user->id,
            'message' => $message->message,
            'timestamp' => $message->created_at->format('H:i'),
            'created_at' => $message->created_at,
            'group_type' => $request->groupType,
            'group_id' => $request->group_id,
            'group_name' => $request->groupName,
            'attachments' => $attachments,
            'reply_to' => $message->replyTo,
            'is_edited' => false,
            'edited_at' => null,
            //'is_sent_by_logged_in_user' => $isSentByLoggedInUser,
        ];
        // Realtime system notification for channel members
        try {
            $branchUsers = collect();
            if ($request->groupType == 'department') {
                $branch = Department::with('users')->find($request->group_id);
                $branchUsers = $branch?->users ?? collect();
            } elseif ($request->groupType == 'division') {
                $branch = Division::with('users')->find($request->group_id);
                $branchUsers = $branch?->users ?? collect();
            } elseif ($request->groupType == 'unit') {
                $branch = Unit::with('users')->find($request->group_id);
                $branchUsers = $branch?->users ?? collect();
            }
            foreach ($branchUsers as $bUser) {
                if ($bUser->id != Auth::id()) {
                    $bUser->notify(new \App\Notifications\ChatMessageNotification($message, auth()->user(), [
                        'is_group' => true,
                        'group_type' => $request->groupType,
                        'group_id' => $request->group_id,
                        'group_name' => $request->groupName ?? ($branch?->title ?? ucfirst($request->groupType)),
                    ]));
                }
            }
        } catch (\Exception $e) {
            \Log::warning("Failed to dispatch Group ChatMessageNotification: " . $e->getMessage());
        }

        broadcast(new GroupMessageSent($MessageArray, $request->group_id, $request->groupType))->toOthers();
        return response()->json(['message' => 'Message sent to group successfully!', 'sentMessage' => 
$MessageArray], 200);
    }
    public function sendMessageToUser(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'message' => 'nullable|string|max:1000',
        ]);

        $isBlocked = \App\Models\BlockedUser::where(function($query) use ($request) {
            $query->where('user_id', Auth::id())->where('blocked_user_id', $request->recipient_id);
        })->orWhere(function($query) use ($request) {
            $query->where('user_id', $request->recipient_id)->where('blocked_user_id', Auth::id());
        })->exists();

        if ($isBlocked) {
            return response()->json(['message' => 'You cannot send a message to this user because of a block.'], 403);
        }

        $message = ChatMessage::create([
            'sender_id' => Auth::id(),
            'recipient_id' => $request->recipient_id,
            'message' => $request->message ?? '',
            'reply_to_id' => $request->reply_to_id,
        ]);

        $attachments = [];
        if ($request->hasFile('files')) {
            $files = is_array($request->file('files')) ? $request->file('files') : [$request->file('files')];
            foreach ($files as $file) {
                $ext = $file->getClientOriginalExtension() ?: 'bin';
                $fileName = uniqid() . '_' . time() . '.' . $ext;
                $file->move(public_path('storage/chat_attachments'), $fileName);
                $path = 'chat_attachments/' . $fileName;
                $attachment = \App\Models\ChatAttachment::create([
                    'chat_message_id' => $message->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                ]);
                $attachments[] = $attachment;
            }
        }

        if ($request->has('edms_attachments')) {
            $edmsDocs = is_array($request->edms_attachments) ? $request->edms_attachments : json_decode($request->edms_attachments, true);
            if (!empty($edmsDocs)) {
                foreach ($edmsDocs as $edmsDoc) {
                    $attachment = \App\Models\ChatAttachment::create([
                        'chat_message_id' => $message->id,
                        'file_path' => $edmsDoc['file_path'],
                        'file_name' => $edmsDoc['file_name'] ?? ($edmsDoc['title'] ?? 'Document'),
                        'file_type' => $edmsDoc['file_type'] ?? 'application/pdf',
                        'edms_file_id' => $edmsDoc['edms_file_id'] ?? null,
                        'metadata' => [
                            'is_edms' => true,
                            'title' => $edmsDoc['title'] ?? $edmsDoc['file_name'],
                            'doc_no' => $edmsDoc['doc_no'] ?? null,
                            'classification' => $edmsDoc['classification'] ?? 'Standard',
                            'department' => $edmsDoc['department'] ?? null,
                            'size' => $edmsDoc['size'] ?? null,
                        ],
                    ]);
                    $attachments[] = $attachment;
                }
            }
        }

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
            'is_sent_by_logged_in_user' => $isSentByLoggedInUser,
            'attachments' => $attachments,
            'reply_to' => $message->replyTo,
            'is_read' => (bool)$message->is_read,
            'is_edited' => false,
            'edited_at' => null,
        ];
        // Realtime system notification for notifications bell & toast
        $recipientUser = User::find($message->recipient_id);
        if ($recipientUser) {
            try {
                $recipientUser->notify(new \App\Notifications\ChatMessageNotification($message, auth()->user()));
            } catch (\Exception $e) {
                \Log::warning("Failed to dispatch ChatMessageNotification: " . $e->getMessage());
            }
        }

        broadcast(new MessageSent($formattedMessage, $message->recipient_id))->toOthers();
        return response()->json(['message' => 'Message sent to user successfully!', 'sentMessage' => 
$formattedMessage], 200);
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
        $loggedInUserId = Auth::id();
        $user = auth()->user();

        // 1. Any user that has an active conversation with the logged-in user
        $conversationUserIds = ChatMessage::where('sender_id', $loggedInUserId)
            ->orWhere('recipient_id', $loggedInUserId)
            ->pluck('sender_id')
            ->merge(
                ChatMessage::where('sender_id', $loggedInUserId)
                    ->orWhere('recipient_id', $loggedInUserId)
                    ->pluck('recipient_id')
            )
            ->unique()
            ->filter(fn($id) => $id != $loggedInUserId)
            ->values();

        // 2. AGF executive users should always be visible to department leadership
        $agfUserIds = User::where(function($q) {
            $q->whereHas('roles', function($rq) {
                $rq->whereIn('name', ['super admin', 'super user', 'Accountant-General', 'agf', 'AGF Registry']);
            })
            ->orWhere('email', 'like', '%agf%');
        })->where('id', '!=', $loggedInUserId)->pluck('id');

        $alwaysIncludeIds = $conversationUserIds->merge($agfUserIds)->unique()->toArray();

        $query = User::where('id', '!=', $loggedInUserId);

        $isAgf = $user->hasAnyRole(['super admin', 'super user', 'Accountant-General', 'agf', 'AGF Registry', 'admin']) 
            || str_contains(strtolower($user->email ?? ''), 'agf');

        if ($isAgf) {
            // AGF shows all users
        } elseif ($user->hasRole('director')) {
            // director shows users within his department + existing conversation partners + AGF
            $query->where(function($q) use ($user, $alwaysIncludeIds) {
                $q->where('department_id', $user->department_id)
                  ->orWhereIn('id', $alwaysIncludeIds);
            });
        } elseif ($user->hasRole('deputy director')) {
            // deputy director shows users within the division + conversation partners + AGF
            $query->where(function($q) use ($user, $alwaysIncludeIds) {
                $q->where('division_id', $user->division_id)
                  ->orWhereIn('id', $alwaysIncludeIds);
            });
        } elseif ($user->hasRole('assistant director')) {
            // assistant director shows users within the unit + conversation partners + AGF
            $query->where(function($q) use ($user, $alwaysIncludeIds) {
                $q->where('unit_id', $user->unit_id)
                  ->orWhereIn('id', $alwaysIncludeIds);
            });
        } else {
            // Fallback for regular users based on their branch type + conversation partners + AGF
            $branch_type = $this->getBranchType();
            $query->where(function($q) use ($user, $branch_type, $alwaysIncludeIds) {
                if ($branch_type == 'unit') {
                    $q->where('unit_id', $user->unit_id);
                } elseif ($branch_type == 'division') {
                    $q->where('division_id', $user->division_id);
                } elseif ($branch_type == 'department') {
                    $q->where('department_id', $user->department_id);
                }
                $q->orWhereIn('id', $alwaysIncludeIds);
            });
        }

        $users = $query->with(['roles', 'department', 'division', 'unit'])->get();
        $usersWithMeta = $this->attachMessageMetadataToUsers($users, $loggedInUserId);

        return response()->json($usersWithMeta);
    }

    public function getConversationWithUser($userId)
    {
        $loggedInUserId = Auth::id();

        $otherUser = User::find($userId);

        if (!$otherUser) {
            return response()->json(['error' => 'User does not exist'], 404);
        } else {
            // Mark incoming unread messages as read
            $unreadCount = ChatMessage::where('sender_id', $userId)
                ->where('recipient_id', $loggedInUserId)
                ->where('is_read', false)
                ->count();

            if ($unreadCount > 0) {
                ChatMessage::where('sender_id', $userId)
                    ->where('recipient_id', $loggedInUserId)
                    ->where('is_read', false)
                    ->update(['is_read' => true, 'read_at' => now()]);

                broadcast(new MessagesRead($userId, $loggedInUserId))->toOthers();
            }

            $conversation = ChatMessage::with(['sender', 'attachments', 'replyTo'])->where(function ($query) use ($loggedInUserId, $userId) {
                $query->where('sender_id', $loggedInUserId)->where('recipient_id', $userId);
            })
            ->orWhere(function ($query) use ($loggedInUserId, $userId) {
                $query->where('sender_id', $userId)->where('recipient_id', $loggedInUserId);
            })
            ->orderBy('created_at', 'DESC')->paginate(35);
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
                            'user' => $message->sender ? $message->sender->first_name : 'Unknown',
                            'message' => $message->message,
                            'timestamp' => $message->created_at->format('H:i'),
                            'recipient_id' => $message->recipient_id,
                            'sender_id' => $message->sender_id,
                            'is_sent_by_logged_in_user' => $isSentByLoggedInUser,
                            'sender' => $message->sender,
                            'created_at' => $message->created_at,
                            'attachments' => $message->attachments,
                            'reply_to' => $message->replyTo,
                            'is_read' => (bool)$message->is_read,
                            'read_at' => $message->read_at,
                            'is_pinned' => (bool)$message->is_pinned,
                            'is_starred' => (bool)$message->is_starred,
                            'is_deleted' => (bool)$message->is_deleted,
                            'reactions' => $message->reactions ?? [],
                            'is_edited' => (bool)$message->is_edited,
                            'edited_at' => $message->edited_at,
                        ];
                    })->toArray(),
                ];
            })->toArray();

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

    private function attachMessageMetadataToUsers($users, $loggedInUserId)
    {
        $userIds = $users->pluck('id')->toArray();
        if (empty($userIds)) {
            return $users;
        }

        $currentUser = Auth::user() ?: User::with('roles')->find($loggedInUserId);

        $recentMessages = ChatMessage::where(function($q) use ($loggedInUserId, $userIds) {
                $q->where('sender_id', $loggedInUserId)->whereIn('recipient_id', $userIds);
            })->orWhere(function($q) use ($loggedInUserId, $userIds) {
                $q->where('recipient_id', $loggedInUserId)->whereIn('sender_id', $userIds);
            })
            ->latest()
            ->get();

        $latestPerUser = [];
        foreach ($recentMessages as $msg) {
            $otherId = ($msg->sender_id === $loggedInUserId) ? $msg->recipient_id : $msg->sender_id;
            if (!isset($latestPerUser[$otherId])) {
                $latestPerUser[$otherId] = $msg;
            }
        }

        $unreadCounts = ChatMessage::where('recipient_id', $loggedInUserId)
            ->whereIn('sender_id', $userIds)
            ->where('is_read', false)
            ->selectRaw('sender_id, count(*) as count')
            ->groupBy('sender_id')
            ->pluck('count', 'sender_id')
            ->toArray();

        return $users->map(function($u) use ($latestPerUser, $unreadCounts, $loggedInUserId) {
            $lastMsg = $latestPerUser[$u->id] ?? null;
            $u->last_message = $lastMsg ? ($lastMsg->is_deleted ? 'This message was deleted' : ($lastMsg->message ?: 'Sent an attachment')) : null;
            $u->last_message_sender_id = $lastMsg ? $lastMsg->sender_id : null;
            $u->last_message_time = $lastMsg ? $lastMsg->created_at->diffForHumans(null, true) : null;
            $u->last_message_raw_time = $lastMsg ? $lastMsg->created_at->timestamp : 0;
            $u->unread_count = $unreadCounts[$u->id] ?? 0;
            $u->department_name = $u->department ? ($u->department->title ?? $u->department->name) : null;
            $u->division_name = $u->division ? $u->division->name : null;
            $u->unit_name = $u->unit ? $u->unit->name : null;

            // Accurate online status: only logged-in users with active cache or current user are online
            $isOnline = ((int)$u->id === (int)$loggedInUserId) || \Illuminate\Support\Facades\Cache::has('user-is-online-' . $u->id);
            $u->is_online = $isOnline ? 1 : 0;

            return $u;
        })->sortByDesc('last_message_raw_time')->values();
    }

    public function heartbeat(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            \Illuminate\Support\Facades\Cache::put('user-is-online-' . $user->id, true, now()->addMinutes(3));
            \Illuminate\Support\Facades\DB::table('users')->where('id', $user->id)->update(['is_online' => 1]);
            return response()->json(['status' => 'online', 'user_id' => $user->id]);
        }
        return response()->json(['status' => 'offline'], 401);
    }

    public function leaveChat(Request $request)
    {
        if (Auth::check()) {
            $userId = Auth::id();
            \Illuminate\Support\Facades\Cache::forget('user-is-online-' . $userId);
            \Illuminate\Support\Facades\DB::table('users')->where('id', $userId)->update(['is_online' => 0]);
        }
        return response()->json(['status' => 'offline']);
    }

    public function searchUsersForChat(Request $request)
    {
        $search = $request->query('query');
        $users = User::where('id', '!=', Auth::id())
            ->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })
            ->take(20)
            ->get();
        return response()->json($users);
    }

    public function sendChatRequest(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id'
        ]);

        $exists = \App\Models\ChatRequest::where('sender_id', Auth::id())
            ->where('receiver_id', $request->receiver_id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Request already sent.', 'status' => 'exists'], 200);
        }

        $chatRequest = \App\Models\ChatRequest::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'status' => 'pending'
        ]);
        
        $chatRequest->load('sender', 'receiver');
        broadcast(new \App\Events\ChatRequestEvent($chatRequest, $request->receiver_id))->toOthers();

        return response()->json(['message' => 'Chat request sent successfully.', 'status' => 'created']);
    }

    public function acceptChatRequest(Request $request, $id)
    {
        $chatRequest = \App\Models\ChatRequest::findOrFail($id);

        if ($chatRequest->receiver_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $chatRequest->update(['status' => 'accepted']);

        return response()->json(['message' => 'Chat request accepted.']);
    }

    public function declineChatRequest(Request $request, $id)
    {
        $chatRequest = \App\Models\ChatRequest::findOrFail($id);

        if ($chatRequest->receiver_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $chatRequest->update(['status' => 'declined']);

        return response()->json(['message' => 'Chat request declined.']);
    }

    public function getChatRequests()
    {
        $requests = \App\Models\ChatRequest::with(['sender.department', 'sender.roles'])
            ->where('receiver_id', Auth::id())
            ->where('status', 'pending')
            ->get();

        return response()->json($requests);
    }

    public function getPersonalContacts()
    {
        $userId = Auth::id();
        $archivedUserIds = \App\Models\ChatArchive::where('user_id', $userId)->pluck('archived_user_id')->toArray();

        // Get all accepted requests where user is sender or receiver
        $contacts = \App\Models\ChatRequest::where('status', 'accepted')
            ->where(function($q) use ($userId) {
                $q->where('sender_id', $userId)
                  ->orWhere('receiver_id', $userId);
            })
            ->with(['sender', 'receiver'])
            ->get()
            ->map(function($req) use ($userId) {
                return $req->sender_id === $userId ? $req->receiver : $req->sender;
            })
            ->filter(function($contact) use ($archivedUserIds) {
                return !in_array($contact->id, $archivedUserIds);
            })
            ->unique('id')
            ->values();

        $contactsWithMeta = $this->attachMessageMetadataToUsers(collect($contacts), $userId);

        return response()->json($contactsWithMeta);
    }

    public function getArchivedContacts()
    {
        $userId = Auth::id();
        $archivedRecords = \App\Models\ChatArchive::where('user_id', $userId)->get();

        $archivedUserIds = $archivedRecords->whereNotNull('archived_user_id')->pluck('archived_user_id')->toArray();
        $users = [];
        if (!empty($archivedUserIds)) {
            $userModels = \App\Models\User::whereIn('id', $archivedUserIds)->get();
            $users = $this->attachMessageMetadataToUsers(collect($userModels), $userId);
        }

        $groupArchives = $archivedRecords->whereNotNull('group_id')->values();
        $archivedGroups = [];
        foreach ($groupArchives as $ga) {
            $grp = null;
            if ($ga->group_type === 'department') {
                $grp = \App\Models\Department::find($ga->group_id);
            } elseif ($ga->group_type === 'division') {
                $grp = \App\Models\Division::find($ga->group_id);
            } elseif ($ga->group_type === 'unit') {
                $grp = \App\Models\Unit::find($ga->group_id);
            } else {
                $grp = \App\Models\Groups::find($ga->group_id);
            }
            if ($grp) {
                $archivedGroups[] = [
                    'id' => $ga->group_id,
                    'group_id' => $ga->group_id,
                    'name' => $grp->name,
                    'group_type' => $ga->group_type,
                    'is_group' => true,
                ];
            }
        }

        return response()->json([
            'users' => $users,
            'groups' => $archivedGroups
        ]);
    }

    public function archiveChat(Request $request)
    {
        $request->validate([
            'archived_user_id' => 'nullable|exists:users,id',
            'group_id' => 'nullable|integer',
            'group_type' => 'nullable|string'
        ]);

        if ($request->filled('archived_user_id')) {
            \App\Models\ChatArchive::firstOrCreate([
                'user_id' => Auth::id(),
                'archived_user_id' => $request->archived_user_id
            ]);
            return response()->json(['message' => 'Chat archived successfully']);
        }

        if ($request->filled('group_id') && $request->filled('group_type')) {
            \App\Models\ChatArchive::firstOrCreate([
                'user_id' => Auth::id(),
                'group_id' => $request->group_id,
                'group_type' => $request->group_type
            ]);
            return response()->json(['message' => 'Group chat archived successfully']);
        }

        return response()->json(['message' => 'Invalid archive parameters'], 422);
    }

    public function unarchiveChat(Request $request)
    {
        $request->validate([
            'archived_user_id' => 'nullable|exists:users,id',
            'group_id' => 'nullable|integer',
            'group_type' => 'nullable|string'
        ]);

        if ($request->filled('archived_user_id')) {
            \App\Models\ChatArchive::where('user_id', Auth::id())
                ->where('archived_user_id', $request->archived_user_id)
                ->delete();
            return response()->json(['message' => 'Chat unarchived successfully']);
        }

        if ($request->filled('group_id') && $request->filled('group_type')) {
            \App\Models\ChatArchive::where('user_id', Auth::id())
                ->where('group_id', $request->group_id)
                ->where('group_type', $request->group_type)
                ->delete();
            return response()->json(['message' => 'Group chat unarchived successfully']);
        }

        return response()->json(['message' => 'Invalid unarchive parameters'], 422);
    }

    public function checkArchiveStatus(Request $request)
    {
        $userId = Auth::id();
        $isArchived = false;

        if ($request->filled('archived_user_id')) {
            $isArchived = \App\Models\ChatArchive::where('user_id', $userId)
                ->where('archived_user_id', $request->archived_user_id)
                ->exists();
        } elseif ($request->filled('group_id') && $request->filled('group_type')) {
            $isArchived = \App\Models\ChatArchive::where('user_id', $userId)
                ->where('group_id', $request->group_id)
                ->where('group_type', $request->group_type)
                ->exists();
        }

        return response()->json(['is_archived' => (bool)$isArchived]);
    }

    public function exportConversation(Request $request)
    {
        $userId = Auth::id();
        $type = $request->input('type', 'direct'); // 'direct' or 'group'
        $format = strtolower($request->input('format', 'json')); // 'pdf' or 'json'

        if ($type === 'direct') {
            $targetUserId = $request->input('target_id');
            $targetUser = User::findOrFail($targetUserId);

            $messages = ChatMessage::where(function($q) use ($userId, $targetUserId) {
                    $q->where('sender_id', $userId)->where('recipient_id', $targetUserId);
                })
                ->orWhere(function($q) use ($userId, $targetUserId) {
                    $q->where('sender_id', $targetUserId)->where('recipient_id', $userId);
                })
                ->with(['sender', 'recipient', 'attachments', 'replyTo.sender'])
                ->orderBy('created_at', 'asc')
                ->get();

            $title = $targetUser->first_name . ' ' . $targetUser->last_name;
            $participants = Auth::user()->first_name . ' ' . Auth::user()->last_name . ' & ' . $title;
        } else {
            $groupId = $request->input('target_id');
            $groupType = $request->input('group_type', 'department');

            $groupName = 'Group ' . $groupId;
            if ($groupType === 'department') {
                $groupName = Department::find($groupId)?->name ?? $groupName;
            } elseif ($groupType === 'division') {
                $groupName = Division::find($groupId)?->name ?? $groupName;
            } elseif ($groupType === 'unit') {
                $groupName = Unit::find($groupId)?->name ?? $groupName;
            } else {
                $groupName = Groups::find($groupId)?->name ?? $groupName;
            }

            $messages = GroupMessages::where('group_id', $groupId)
                ->where('group_type', $groupType)
                ->with(['user', 'attachments', 'replyTo.user'])
                ->orderBy('created_at', 'asc')
                ->get();

            $title = $groupName . ' (' . ucfirst($groupType) . ')';
            $participants = 'Members of ' . $title;
        }

        $formattedMessages = $messages->map(function($msg) use ($type) {
            $sender = $type === 'direct' ? $msg->sender : $msg->user;
            $senderName = $sender ? ($sender->first_name . ' ' . $sender->last_name) : 'Unknown';

            $attachments = $msg->attachments ? $msg->attachments->map(function($att) {
                return [
                    'name' => $att->file_name ?? 'File',
                    'file_name' => $att->file_name ?? 'File',
                    'size' => $att->metadata['size_formatted'] ?? ($att->metadata['size'] ?? 'Attachment'),
                    'file_path' => $att->file_path ?? null,
                ];
            })->toArray() : [];

            $replyToData = null;
            if ($msg->replyTo) {
                $replySender = $type === 'direct' ? $msg->replyTo->sender : $msg->replyTo->user;
                $replyToData = [
                    'sender_name' => $replySender ? ($replySender->first_name . ' ' . $replySender->last_name) : 'Unknown',
                    'message' => $msg->replyTo->message,
                ];
            }

            return [
                'id' => $msg->id,
                'sender_name' => $senderName,
                'sender_id' => $sender?->id,
                'message' => $msg->is_deleted ? '[This message was deleted]' : ($msg->message ?: ($attachments ? '[Attachment]' : '')),
                'created_at' => $msg->created_at->toIso8601String(),
                'formatted_date' => $msg->created_at->format('F j, Y'),
                'time' => $msg->created_at->format('g:i A'),
                'is_edited' => (bool)$msg->is_edited,
                'reply_to' => $replyToData,
                'attachments' => $attachments,
            ];
        });

        $exportData = [
            'title' => $title,
            'type' => $type,
            'export_date' => Carbon::now()->format('F j, Y, g:i A'),
            'exported_by' => Auth::user()->first_name . ' ' . Auth::user()->last_name,
            'total_messages' => count($formattedMessages),
            'participants' => $participants,
            'messages' => $formattedMessages->toArray(),
        ];

        $cleanTitle = preg_replace('/[^A-Za-z0-9_\-]/', '_', strtolower($title));
        $filename = "transcript_{$cleanTitle}_" . date('Ymd_His');

        if ($format === 'json') {
            return response()->streamDownload(function() use ($exportData) {
                echo json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            }, "{$filename}.json", [
                'Content-Type' => 'application/json',
            ]);
        }

        // PDF format
        $pdf = Pdf::loadView('pdfs.chat_transcript', $exportData);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download("{$filename}.pdf");
    }

    public function globalSearch(Request $request)
    {
        $userId = Auth::id();
        $q = trim($request->input('q', ''));
        $scope = $request->input('scope', 'all'); // 'all', 'direct', 'group', 'attachments'
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        if (strlen($q) < 2) {
            return response()->json(['results' => [], 'total' => 0]);
        }

        $results = collect();

        // 1. Direct Messages
        if (in_array($scope, ['all', 'direct', 'attachments'])) {
            $directQuery = ChatMessage::where(function($builder) use ($userId) {
                    $builder->where('sender_id', $userId)
                            ->orWhere('recipient_id', $userId);
                })
                ->where('is_deleted', false);

            if ($scope === 'attachments') {
                $directQuery->whereHas('attachments', function($attQ) use ($q) {
                    $attQ->where('file_name', 'like', "%{$q}%")
                         ->orWhere('file_path', 'like', "%{$q}%");
                });
            } else {
                $directQuery->where(function($inner) use ($q) {
                    $inner->where('message', 'like', "%{$q}%")
                          ->orWhereHas('attachments', function($attQ) use ($q) {
                              $attQ->where('file_name', 'like', "%{$q}%");
                          });
                });
            }

            if ($dateFrom) {
                $directQuery->whereDate('created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $directQuery->whereDate('created_at', '<=', $dateTo);
            }

            $directMessages = $directQuery->with(['sender', 'recipient', 'attachments'])
                ->latest()
                ->limit(30)
                ->get();

            foreach ($directMessages as $msg) {
                $otherUser = ($msg->sender_id === $userId) ? $msg->recipient : $msg->sender;
                $results->push([
                    'id' => $msg->id,
                    'type' => 'direct',
                    'target_user' => $otherUser ? [
                        'id' => $otherUser->id,
                        'first_name' => $otherUser->first_name,
                        'last_name' => $otherUser->last_name,
                        'email' => $otherUser->email,
                        'profile_image' => $otherUser->profile_image,
                    ] : null,
                    'sender' => [
                        'id' => $msg->sender?->id,
                        'first_name' => $msg->sender?->first_name,
                        'last_name' => $msg->sender?->last_name,
                    ],
                    'message' => $msg->message,
                    'created_at' => $msg->created_at->toIso8601String(),
                    'timestamp' => $msg->created_at->diffForHumans(),
                    'formatted_date' => $msg->created_at->format('M j, Y g:i A'),
                    'attachments' => $msg->attachments ? $msg->attachments->map(fn($a) => [
                        'name' => $a->file_name,
                        'file_name' => $a->file_name,
                        'size' => $a->metadata['size_formatted'] ?? ($a->metadata['size'] ?? 'Attachment'),
                    ])->toArray() : [],
                    'is_edited' => (bool)$msg->is_edited,
                ]);
            }
        }

        // 2. Group Messages
        if (in_array($scope, ['all', 'group', 'attachments'])) {
            $user = Auth::user();
            $userGroupIds = [];
            if ($user->department_id) {
                $userGroupIds['department'][] = $user->department_id;
            }
            if ($user->division_id) {
                $userGroupIds['division'][] = $user->division_id;
            }
            if ($user->unit_id) {
                $userGroupIds['unit'][] = $user->unit_id;
            }
            $customGroupIds = Groups::whereHas('users', fn($q) => $q->where('users.id', $userId))->pluck('id')->toArray();
            if (!empty($customGroupIds)) {
                $userGroupIds['custom'] = $customGroupIds;
            }

            $groupQuery = GroupMessages::where('is_deleted', false);

            if (!empty($userGroupIds)) {
                $groupQuery->where(function($gq) use ($userGroupIds) {
                    foreach ($userGroupIds as $type => $ids) {
                        $gq->orWhere(function($sub) use ($type, $ids) {
                            $sub->where('group_type', $type)->whereIn('group_id', $ids);
                        });
                    }
                });
            }

            if ($scope === 'attachments') {
                $groupQuery->whereHas('attachments', function($attQ) use ($q) {
                    $attQ->where('file_name', 'like', "%{$q}%")
                         ->orWhere('file_path', 'like', "%{$q}%");
                });
            } else {
                $groupQuery->where(function($inner) use ($q) {
                    $inner->where('message', 'like', "%{$q}%")
                          ->orWhereHas('attachments', function($attQ) use ($q) {
                              $attQ->where('file_name', 'like', "%{$q}%");
                          });
                });
            }

            if ($dateFrom) {
                $groupQuery->whereDate('created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $groupQuery->whereDate('created_at', '<=', $dateTo);
            }

            $groupMessages = $groupQuery->with(['user', 'attachments'])
                ->latest()
                ->limit(30)
                ->get();

            foreach ($groupMessages as $msg) {
                $groupName = 'Group ' . $msg->group_id;
                if ($msg->group_type === 'department') {
                    $groupName = Department::find($msg->group_id)?->name ?? $groupName;
                } elseif ($msg->group_type === 'division') {
                    $groupName = Division::find($msg->group_id)?->name ?? $groupName;
                } elseif ($msg->group_type === 'unit') {
                    $groupName = Unit::find($msg->group_id)?->name ?? $groupName;
                } else {
                    $groupName = Groups::find($msg->group_id)?->name ?? $groupName;
                }

                $results->push([
                    'id' => $msg->id,
                    'type' => 'group',
                    'group_id' => $msg->group_id,
                    'group_type' => $msg->group_type,
                    'group_name' => $groupName,
                    'sender' => [
                        'id' => $msg->user?->id,
                        'first_name' => $msg->user?->first_name,
                        'last_name' => $msg->user?->last_name,
                    ],
                    'message' => $msg->message,
                    'created_at' => $msg->created_at->toIso8601String(),
                    'timestamp' => $msg->created_at->diffForHumans(),
                    'formatted_date' => $msg->created_at->format('M j, Y g:i A'),
                    'attachments' => $msg->attachments ? $msg->attachments->map(fn($a) => [
                        'name' => $a->file_name,
                        'file_name' => $a->file_name,
                        'size' => $a->metadata['size_formatted'] ?? ($a->metadata['size'] ?? 'Attachment'),
                    ])->toArray() : [],
                    'is_edited' => (bool)$msg->is_edited,
                ]);
            }
        }

        $sorted = $results->sortByDesc('created_at')->values();

        return response()->json([
            'results' => $sorted,
            'total' => $sorted->count(),
            'query' => $q
        ]);
    }

    public function blockUser(Request $request)
    {
        $request->validate([
            'blocked_user_id' => 'required|exists:users,id'
        ]);

        \App\Models\BlockedUser::firstOrCreate([
            'user_id' => Auth::id(),
            'blocked_user_id' => $request->blocked_user_id
        ]);

        return response()->json(['message' => 'User blocked successfully']);
    }

    public function unblockUser(Request $request)
    {
        $request->validate([
            'blocked_user_id' => 'required|exists:users,id'
        ]);

        \App\Models\BlockedUser::where('user_id', Auth::id())
            ->where('blocked_user_id', $request->blocked_user_id)
            ->delete();

        return response()->json(['message' => 'User unblocked successfully']);
    }

    public function deleteMessage(Request $request, $id)
    {
        $isGroup = $request->boolean('is_group');

        if ($isGroup) {
            $message = \App\Models\GroupMessages::findOrFail($id);
            if ($message->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            $channelId = $message->group_id;
        } else {
            $message = \App\Models\ChatMessage::findOrFail($id);
            if ($message->sender_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            $channelId = $message->recipient_id;
        }

        $message->update([
            'is_deleted' => true,
            'message' => 'This message was deleted'
        ]);
        
        \App\Models\ChatAttachment::where(
            $isGroup ? 'group_message_id' : 'chat_message_id', $id
        )->delete();

        broadcast(new \App\Events\MessageDeleted($id, $isGroup, $channelId))->toOthers();

        return response()->json(['message' => 'Message deleted successfully']);
    }

    public function togglePinMessage(Request $request, $id)
    {
        $isGroup = $request->boolean('is_group');
        if ($isGroup) {
            $message = \App\Models\GroupMessages::findOrFail($id);
        } else {
            $message = \App\Models\ChatMessage::findOrFail($id);
        }

        $message->is_pinned = !$message->is_pinned;
        $message->save();

        return response()->json(['message' => 'Message pin status toggled', 'is_pinned' => $message->is_pinned]);
    }

    public function toggleStarMessage(Request $request, $id)
    {
        $isGroup = $request->boolean('is_group');
        if ($isGroup) {
            $message = \App\Models\GroupMessages::findOrFail($id);
        } else {
            $message = \App\Models\ChatMessage::findOrFail($id);
        }

        $message->is_starred = !$message->is_starred;
        $message->save();

        return response()->json(['message' => 'Message star status toggled', 'is_starred' => $message->is_starred]);
    }

    public function forwardMessage(Request $request)
    {
        $request->validate([
            'message_id' => 'required|integer',
            'is_group' => 'required|boolean',
            'forward_to_users' => 'array',
            'forward_to_groups' => 'array',
        ]);

        $isGroup = $request->boolean('is_group');
        $originalMessage = $isGroup 
            ? \App\Models\GroupMessages::with('attachments')->findOrFail($request->message_id) 
            : \App\Models\ChatMessage::with('attachments')->findOrFail($request->message_id);

        $forwardToUsers = $request->input('forward_to_users', []);
        $forwardToGroups = $request->input('forward_to_groups', []);

        foreach ($forwardToUsers as $userId) {
            $newMessage = \App\Models\ChatMessage::create([
                'sender_id' => Auth::id(),
                'recipient_id' => $userId,
                'message' => $originalMessage->message,
            ]);

            // Copy attachments
            if ($originalMessage->attachments) {
                foreach ($originalMessage->attachments as $attachment) {
                    \App\Models\ChatAttachment::create([
                        'chat_message_id' => $newMessage->id,
                        'file_path' => $attachment->file_path,
                        'file_name' => $attachment->file_name,
                        'file_type' => $attachment->file_type,
                    ]);
                }
            }
            
            broadcast(new \App\Events\MessageSent($newMessage, false, $userId))->toOthers();
        }

        foreach ($forwardToGroups as $groupId) {
            $newMessage = \App\Models\GroupMessages::create([
                'user_id' => Auth::id(),
                'group_id' => $groupId,
                'message' => $originalMessage->message,
            ]);

            // Copy attachments
            if ($originalMessage->attachments) {
                foreach ($originalMessage->attachments as $attachment) {
                    \App\Models\ChatAttachment::create([
                        'group_message_id' => $newMessage->id,
                        'file_path' => $attachment->file_path,
                        'file_name' => $attachment->file_name,
                        'file_type' => $attachment->file_type,
                    ]);
                }
            }

            broadcast(new \App\Events\MessageSent($newMessage, true, $groupId))->toOthers();
        }

        return response()->json(['message' => 'Message forwarded successfully']);
    }

    public function reactToMessage(Request $request, $id)
    {
        $request->validate([
            'is_group' => 'required|boolean',
            'emoji' => 'required|string'
        ]);

        $isGroup = $request->boolean('is_group');
        $message = $isGroup 
            ? \App\Models\GroupMessages::findOrFail($id) 
            : \App\Models\ChatMessage::findOrFail($id);

        $reactions = $message->reactions ?? [];
        $userId = Auth::id();
        $emoji = $request->emoji;

        // Find if user already reacted
        $existingReactionIndex = -1;
        foreach ($reactions as $index => $reaction) {
            if ($reaction['user_id'] === $userId) {
                $existingReactionIndex = $index;
                break;
            }
        }

        if ($existingReactionIndex !== -1) {
            if ($reactions[$existingReactionIndex]['emoji'] === $emoji) {
                // If it's the same emoji, toggle (remove) it
                unset($reactions[$existingReactionIndex]);
                $reactions = array_values($reactions); // re-index array
            } else {
                // Change the emoji
                $reactions[$existingReactionIndex]['emoji'] = $emoji;
            }
        } else {
            // Add new reaction
            $reactions[] = [
                'user_id' => $userId,
                'emoji' => $emoji
            ];
        }

        $message->reactions = $reactions;
        $message->save();

        return response()->json(['message' => 'Reaction updated successfully', 'reactions' => $message->reactions]);
    }

    /**
     * Edit a chat message within the 15-minute window.
     */
    public function editMessage(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'is_group' => 'nullable|boolean',
        ]);

        $userId = Auth::id();
        $isGroup = $request->boolean('is_group');

        if ($isGroup) {
            $msg = GroupMessages::findOrFail($id);
            if ($msg->user_id !== $userId) {
                return response()->json(['message' => 'You are not authorized to edit this message.'], 403);
            }
        } else {
            $msg = ChatMessage::findOrFail($id);
            if ($msg->sender_id !== $userId) {
                return response()->json(['message' => 'You are not authorized to edit this message.'], 403);
            }
        }

        // Check 15-minute rule
        $diffInMinutes = abs(Carbon::parse($msg->created_at)->diffInMinutes(now()));
        if ($diffInMinutes > 15) {
            return response()->json([
                'message' => 'Message editing window has expired. Messages can only be edited within 15 minutes of sending.'
            ], 422);
        }

        $msg->message = $request->message;
        $msg->is_edited = true;
        $msg->edited_at = now();
        $msg->save();

        $payload = [
            'id' => $msg->id,
            'message' => $msg->message,
            'is_edited' => true,
            'edited_at' => $msg->edited_at,
            'is_group' => $isGroup,
        ];

        if ($isGroup) {
            $payload['group_id'] = $msg->group_id;
            $payload['group_type'] = $msg->group_type;
            broadcast(new MessageEdited($payload, "group.{$msg->group_id}.{$msg->group_type}"))->toOthers();
        } else {
            $payload['recipient_id'] = $msg->recipient_id;
            $payload['sender_id'] = $msg->sender_id;
            broadcast(new MessageEdited($payload, "chat.{$msg->recipient_id}"))->toOthers();
            broadcast(new MessageEdited($payload, "chat.{$msg->sender_id}"))->toOthers();
        }

        return response()->json([
            'message' => 'Message edited successfully.',
            'data' => $payload,
        ], 200);
    }

    /**
     * Search documents across EDMS repository and tasks for chat sharing.
     * Enforces the EDMS Document Management access hierarchy:
     * - AGF: Can see all documents and records across all departments.
     * - Director: Can access only documents and records within their department.
     * - Deputy Director: Can access documents and records within their division.
     * - Assistant Director: Can access documents and records within their unit.
     * - Unit Staff: Can access only records uploaded by the staff.
     */
    public function searchEdmsDocuments(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['documents' => [], 'count' => 0]);
        }

        $search = trim($request->input('search', ''));
        $category = $request->input('category', 'all'); // 'all', 'repository', 'tasks'

        // Determine User Hierarchy Tier (strictly separated by role)
        $isAgf = $user->hasAnyRole(['super admin', 'super user', 'Accountant-General', 'agf', 'AGF Registry']) 
            || str_contains(strtolower($user->email ?? ''), 'agf');
        $isDirector = !$isAgf && $user->hasRole('director');
        $isDeputyDirector = !$isAgf && !$isDirector && $user->hasRole('deputy director');
        $isAssistantDirector = !$isAgf && !$isDirector && !$isDeputyDirector && $user->hasRole('assistant director');
        $isUnitStaff = !$isAgf && !$isDirector && !$isDeputyDirector && !$isAssistantDirector;

        $tierLabel = $isAgf ? 'AGF' : ($isDirector ? 'Director' : ($isDeputyDirector ? 'Deputy Director' : ($isAssistantDirector ? 'Assistant Director' : 'Unit Staff')));
        $deptTitle = $user->department ? ($user->department->title ?? $user->department->name) : '';
        $scopeDescription = $isAgf 
            ? 'All Departments & Folders (AGF)' 
            : ($isDirector 
                ? ($deptTitle ? "Department of {$deptTitle}" : 'Departmental Scope') 
                : ($isDeputyDirector 
                    ? 'Divisional Scope' 
                    : ($isAssistantDirector 
                        ? 'Unit Scope' 
                        : 'Personal Uploads (Unit Staff)')));

        $page = max(1, (int) $request->input('page', 1));
        $perPage = max(10, min(100, (int) $request->input('per_page', 30)));
        $offset = ($page - 1) * $perPage;

        $results = [];
        $totalFiles = 0;
        $totalArchives = 0;
        $totalTasks = 0;

        // 1. Files & Archives (Repository category)
        if (in_array($category, ['all', 'repository'])) {
            // A. Active Files from Repository
            $fileQuery = File::query()->withoutGlobalScopes()->with(['department', 'division', 'unit']);

            if ($isAgf) {
                // All documents across all departments
            } elseif ($isDirector) {
                $fileQuery->where('department_id', $user->department_id);
            } elseif ($isDeputyDirector) {
                $fileQuery->where('division_id', $user->division_id);
            } elseif ($isAssistantDirector) {
                $fileQuery->where('unit_id', $user->unit_id);
            } else {
                // Unit staff: only uploaded by staff
                $fileQuery->where('created_by', $user->id);
            }

            if ($search !== '') {
                $fileQuery->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('doc_no', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('department', fn($dq) => $dq->where('title', 'like', "%{$search}%"));
                });
            }

            $totalFiles = (clone $fileQuery)->count();

            // Include files on first page
            if ($page === 1) {
                $files = $fileQuery->latest()->limit(20)->get();

                foreach ($files as $file) {
                    $filePath = $file->path;
                    if (!$filePath) {
                        $regFile = RegistryFile::where('file_id', $file->id)->latest()->first();
                        $filePath = $regFile ? $regFile->file_path : ('storage/files/' . $file->id);
                    }

                    $deptName = $file->department ? ($file->department->title ?? $file->department->name) : 'Registry Files';

                    $results[] = [
                        'id' => 'file_' . $file->id,
                        'edms_file_id' => $file->id,
                        'title' => $file->name,
                        'doc_no' => $file->doc_no ?: ('DOC-' . str_pad($file->id, 5, '0', STR_PAD_LEFT)),
                        'file_name' => $file->name,
                        'file_path' => $filePath,
                        'file_type' => $file->file_type ?: 'application/pdf',
                        'size' => 'Registry File',
                        'classification' => $file->is_confidential ? 'Confidential' : 'Standard',
                        'department' => $deptName,
                        'folder' => 'Registry',
                        'source' => 'Repository File',
                        'date' => $file->created_at ? $file->created_at->format('M d, Y') : null,
                        'raw_date' => $file->created_at ? $file->created_at->timestamp : 0,
                    ];
                }
            }

            // B. Archived Records from Repository (Document Management Folders)
            $archiveQuery = Archive::query()->withoutGlobalScopes()->with(['filePath', 'archiveDocs', 'department', 'division', 'unit', 'folder']);

            if ($isAgf) {
                // All archives across all departments
            } elseif ($isDirector) {
                // Only records within his department
                $archiveQuery->where('department_id', $user->department_id);
            } elseif ($isDeputyDirector) {
                // Only records within his division
                $archiveQuery->where('division_id', $user->division_id);
            } elseif ($isAssistantDirector) {
                // Only records within his unit
                $archiveQuery->where('unit_id', $user->unit_id);
            } else {
                // Unit staff: only uploaded/updated by staff
                $archiveQuery->where(function ($sq) use ($user) {
                    $sq->where('updated_by', $user->id)
                       ->orWhereHas('filePath', fn($fp) => $fp->where('updated_by', $user->id));
                });
            }

            if ($search !== '') {
                $cleanSearch = ltrim(preg_replace('/[^0-9]/', '', $search), '0');
                $archiveQuery->where(function ($q) use ($search, $cleanSearch) {
                    if ($cleanSearch !== '') {
                        $q->where('archives.id', $cleanSearch);
                    }
                    $q->orWhereHas('filePath', function ($fq) use ($search) {
                        $fq->where('file_name', 'like', "%{$search}%");
                    })->orWhereHas('folder', function ($flq) use ($search) {
                        $flq->where('name', 'like', "%{$search}%")
                           ->orWhere('number', 'like', "%{$search}%");
                    })->orWhereHas('department', function ($dq) use ($search) {
                        $dq->where('title', 'like', "%{$search}%");
                    })->orWhereHas('archiveDocs', function ($adq) use ($search) {
                        $adq->where('archive_values', 'like', "%{$search}%");
                    });
                });
            }

            $totalArchives = (clone $archiveQuery)->count();

            // Paged archives
            $archives = $archiveQuery->latest()->skip($offset)->take($perPage)->get();

            foreach ($archives as $archive) {
                // Find document management title case-insensitively from archiveDocs (e.g. "Title", "title", "Subject", "Name")
                $docTitleDoc = $archive->archiveDocs->first(function ($doc) {
                    $k = strtolower(trim($doc->archive_keys ?? ''));
                    return str_contains($k, 'title') || str_contains($k, 'subject') || $k === 'name';
                });
                $docTitle = $docTitleDoc ? trim($docTitleDoc->archive_values ?? '') : null;
                $deptName = $archive->department ? ($archive->department->title ?? $archive->department->name) : 'EDMS Archives';
                $folderName = $archive->folder ? $archive->folder->name : null;

                if ($archive->filePath && $archive->filePath->isNotEmpty()) {
                    foreach ($archive->filePath as $fp) {
                        // Prioritize document management title from archiveDocs over raw uploaded file_name
                        $title = $docTitle ?: ($fp->file_name ?: ($folderName ?: 'Archived Record #' . $archive->id));
                        $results[] = [
                            'id' => 'archive_' . $archive->id . '_' . $fp->id,
                            'edms_file_id' => $archive->id,
                            'title' => $title,
                            'document_management_title' => $docTitle,
                            'doc_no' => 'ARC-' . str_pad($archive->id, 6, '0', STR_PAD_LEFT),
                            'file_name' => $fp->file_name ?: $title,
                            'file_path' => $fp->path,
                            'file_type' => pathinfo($fp->file_name, PATHINFO_EXTENSION) ?: 'pdf',
                            'size' => $fp->file_size ?: 'Archived Doc',
                            'classification' => 'Standard',
                            'department' => $deptName,
                            'folder' => $folderName,
                            'source' => 'Archived Record',
                            'date' => $archive->created_at ? $archive->created_at->format('M d, Y') : null,
                            'raw_date' => $archive->created_at ? $archive->created_at->timestamp : 0,
                        ];
                    }
                } else {
                    $title = $docTitle ?: ($folderName ?: ('Archived Record #' . $archive->id));
                    $results[] = [
                        'id' => 'archive_' . $archive->id,
                        'edms_file_id' => $archive->id,
                        'title' => $title,
                        'document_management_title' => $docTitle,
                        'doc_no' => 'ARC-' . str_pad($archive->id, 6, '0', STR_PAD_LEFT),
                        'file_name' => $title,
                        'file_path' => '/Archives/' . $archive->department_id . '/' . $archive->id,
                        'file_type' => 'pdf',
                        'size' => 'Archived Doc',
                        'classification' => 'Standard',
                        'department' => $deptName,
                        'folder' => $folderName,
                        'source' => 'Archived Record',
                        'date' => $archive->created_at ? $archive->created_at->format('M d, Y') : null,
                        'raw_date' => $archive->created_at ? $archive->created_at->timestamp : 0,
                    ];
                }
            }
        }

        // 2. Task Documents (Tasks category)
        if (in_array($category, ['all', 'tasks'])) {
            $taskQuery = TaskDoc::query()->with(['task.assignments']);

            if ($isAgf) {
                // All task documents
            } elseif ($isDirector) {
                $taskQuery->whereHas('task', function ($t) use ($user) {
                    $t->where('assigned_by_department', $user->department_id)
                      ->orWhereHas('departmentTask', fn($dt) => $dt->where('department_id', $user->department_id));
                });
            } elseif ($isDeputyDirector) {
                $taskQuery->whereHas('task', function ($t) use ($user) {
                    $t->where('assigned_by_division', $user->division_id)
                      ->orWhereHas('divisionTask', fn($dt) => $dt->where('division_id', $user->division_id));
                });
            } elseif ($isAssistantDirector) {
                $taskQuery->whereHas('task', function ($t) use ($user) {
                    $t->where('assigned_by_unit', $user->unit_id)
                      ->orWhereHas('unitTask', fn($ut) => $ut->where('unit_id', $user->unit_id));
                });
            } else {
                // Unit staff: only tasks uploaded/created by staff or assigned to staff
                $taskQuery->whereHas('task', function ($t) use ($user) {
                    $t->where('created_by', $user->id)
                      ->orWhereHas('assignments', fn($as) => $as->where('assigned_to', $user->id));
                });
            }

            if ($search !== '') {
                $taskQuery->where('file_name', 'like', "%{$search}%");
            }

            $totalTasks = (clone $taskQuery)->count();

            // Include task docs on page 1 (or when category is 'tasks')
            if ($category === 'tasks' || $page === 1) {
                $taskLimit = $category === 'tasks' ? $perPage : 10;
                $taskOffset = $category === 'tasks' ? $offset : 0;
                $taskDocs = $taskQuery->latest()->skip($taskOffset)->take($taskLimit)->get();

                foreach ($taskDocs as $td) {
                    $deptName = 'Workflow & Tasks';

                    $results[] = [
                        'id' => 'task_' . $td->id,
                        'edms_file_id' => $td->task_id,
                        'title' => $td->file_name,
                        'doc_no' => $td->task ? ('TSK-' . $td->task->id) : 'TASK-DOC',
                        'file_name' => $td->file_name,
                        'file_path' => $td->path,
                        'file_type' => pathinfo($td->file_name, PATHINFO_EXTENSION) ?: 'pdf',
                        'size' => is_numeric($td->size) ? (round((float)$td->size / 1024, 1) . ' KB') : ($td->size ?: 'Document'),
                        'classification' => 'Internal Task Doc',
                        'department' => $deptName,
                        'folder' => 'Workflow Tasks',
                        'source' => 'Task Document',
                        'date' => $td->created_at ? $td->created_at->format('M d, Y') : null,
                        'raw_date' => $td->created_at ? $td->created_at->timestamp : 0,
                    ];
                }
            }
        }

        // Sort combined results by most recent date
        usort($results, function ($a, $b) {
            return ($b['raw_date'] ?? 0) <=> ($a['raw_date'] ?? 0);
        });

        $totalRecords = $totalFiles + $totalArchives + $totalTasks;
        $hasMore = ($offset + $perPage) < ($category === 'tasks' ? $totalTasks : $totalArchives);

        return response()->json([
            'documents' => $results,
            'count' => count($results),
            'total' => $totalRecords,
            'total_formatted' => number_format($totalRecords),
            'page' => $page,
            'per_page' => $perPage,
            'has_more' => $hasMore,
            'tier' => $tierLabel,
            'scope' => $scopeDescription,
        ]);
    }

    /**
     * Convert a chat message into a workflow task.
     */
    public function createTaskFromChatMessage(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'due_date' => 'nullable|date',
            'priority' => 'nullable|string|in:low,medium,high,1,2,3',
            'users' => 'nullable|array',
            'users.*' => 'exists:users,id',
            'message_id' => 'nullable|integer',
            'is_group' => 'nullable|boolean',
        ]);

        $currentUser = Auth::user();

        $priorityMap = [
            'low' => '1',
            'high' => '2',
            'medium' => '3',
            '1' => '1',
            '2' => '2',
            '3' => '3',
        ];
        $mappedPriority = $priorityMap[strtolower($request->priority ?? 'medium')] ?? '3';

        $task = new Task();
        $task->title = $request->title;
        $task->description = $request->description;
        $task->due_date = $request->due_date ?? now()->addDays(3)->format('Y-m-d');
        $task->status = 'pending';
        $task->priority = $mappedPriority;
        $task->created_by = $currentUser->id;
        $task->assigned_by = $currentUser->id;
        $task->assigned_by_department = $currentUser->department_id;
        $task->assigned_by_division = $currentUser->division_id;
        $task->assigned_by_unit = $currentUser->unit_id;
        $task->created_by_branch = $currentUser->unit_id ?: ($currentUser->division_id ?: $currentUser->department_id);
        $task->save();

        $assignedUsers = $request->input('users', []);
        if (!empty($assignedUsers)) {
            $taskAssignments = [];
            foreach ($assignedUsers as $userId) {
                $taskAssignments[] = [
                    'task_id' => $task->id,
                    'assigned_to' => $userId,
                    'assigned_by' => $currentUser->id,
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            TaskAssignment::insert($taskAssignments);
            $task->user()->sync($assignedUsers);
        }

        // Attach chat message files to the task if available
        if ($request->message_id) {
            $attachments = ChatAttachment::where(
                $request->boolean('is_group') ? 'group_message_id' : 'chat_message_id',
                $request->message_id
            )->get();

            $docs = [];
            foreach ($attachments as $att) {
                $docs[] = [
                    'task_id' => $task->id,
                    'path' => $att->file_path,
                    'file_name' => $att->file_name,
                    'size' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if (!empty($docs)) {
                TaskDoc::insert($docs);
            }
        }

        return response()->json([
            'message' => 'Task created successfully from chat message!',
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'status' => $task->status,
                'priority' => $task->priority,
                'due_date' => $task->due_date,
            ],
        ], 201);
    }
}
