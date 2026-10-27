<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\User;
use App\Models\Division;
use App\Models\Department;
use App\Models\Unit;

class WorkFlowController extends Controller
{

    public function isRole($role){
        $roles = auth()->user()->getRoleNames();
        $is_role = $roles->contains(function($item)use ($role){
            //dd($item);
            return $item == $role;
        });
        if($is_role){
            return true;
        }else{
            return false;
        }
    }

    public function getCreatedTask($request) {
        $searchTerm = $request->search; 
        $statusFilter = $request->status;

        $tasks = Task::withCount(['taskdocs', 'assignmentComments'])->with([
            'taskdocs',
            'file.department',
            'minutes' => function($q) {
                $q->with('user.department')->latest();
            },
            'assignedOutAssignments.assignedTo.department',
            'assignedOutAssignments.assignedTo.roles',
            'assignedToMe.assignedBy.department',
            'assignedToMe.comments' => function($cq) {
                $cq->with(['sentByUser.department', 'sentToUser.department', 'sender.department', 'user.department'])->latest();
            },
            'assignments' => function($query){
                $query->with([
                    'assignedTo.department',
                    'assignedTo.roles',
                    'assignedBy.department',
                    'comments' => function($cq) {
                        $cq->with(['sentByUser.department', 'sentToUser.department', 'sender.department', 'user.department'])->latest();
                    },
                    'taskSubmissions.taskSubmissionFiles'
                ])->orderBy('id', 'desc');
            }
        ])
            ->where(function($q) {
                $q->where('created_by', auth()->id())
                  ->orWhereHas('assignments', function($sq) {
                      $sq->where('assigned_by', auth()->id())
                         ->where('status', '!=', 'under review'); // Exclude if subordinate submitted it (goes to Submissions & Review)
                  });
            })
            ->when($statusFilter, function($query, $statusFilter) {
                if ($statusFilter === 'overdue') {
                    $query->where('status', '!=', 'completed')->where('due_date', '<', now());
                } elseif ($statusFilter === 'due_soon') {
                    $query->where('status', '!=', 'completed')->whereBetween('due_date', [now(), now()->addDays(2)]);
                } elseif ($statusFilter === 'uncompleted') {
                    $query->where('status', '!=', 'completed');
                } elseif ($statusFilter === 'on_hold') {
                    $query->whereHas('assignments', function($sq) {
                        $sq->where('assigned_by', auth()->id())->whereIn('status', ['on_hold', 'hold_responded']);
                    });
                } elseif (in_array($statusFilter, ['pending', 'in progress', 'under review', 'completed', 'returned'])) {
                    $query->where(function($q) use ($statusFilter) {
                        $q->where('status', $statusFilter)
                          ->orWhereHas('assignments', function($sq) use ($statusFilter) {
                              $sq->where('assigned_by', auth()->id())->where('status', $statusFilter);
                          })
                          ->orWhereHas('assignments', function($sq) use ($statusFilter) {
                              $sq->where('assigned_to', auth()->id())->where('status', $statusFilter);
                          });
                    });
                } else {
                    $query->where('status', $statusFilter);
                }
            })
            ->when($searchTerm, function ($query, $searchTerm) {
                $query->where(function ($subQuery) use ($searchTerm) {
                    $subQuery->where('title', 'like', "%{$searchTerm}%")
                             ->orWhere('description', 'like', "%{$searchTerm}%")
                             ->orWhere('status', 'like', "%{$searchTerm}%" );
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $tasks->getCollection()->transform(function ($task) {
            $task->is_assigned = 2; // created by me indicator
            $task->comments_count = $task->assignment_comments_count ?? 0;
            $task->suggested_routing = \App\Services\RoutingService::resolveSmartRoutingForUser($task);
            return $task;
        });
        
        return $tasks;
    }

    public function getAssignedTask($request) {
        $searchTerm = $request->search; 
        $statusFilter = $request->status;
        
        $tasks = Task::withCount(['taskdocs', 'assignmentComments'])->where(function($q) {
                // Incoming Tasks assigned TO me to work on
                $q->whereHas('assignments', function($sq) {
                    $sq->where('assigned_to', auth()->id())
                       ->whereNotIn('status', ['completed', 'returned', 'under review']);
                });
            })
            ->with([
                'taskdocs',
                'file.department',
                'minutes' => function($q) {
                    $q->with('user.department')->latest();
                },
                'assignments' => function($query) {
                    $query->with([
                        'assignedTo.department',
                        'assignedTo.roles',
                        'assignedBy.department',
                        'comments' => function($cq) {
                            $cq->with(['sentByUser.department', 'sentToUser.department', 'sender.department', 'user.department'])->latest();
                        },
                        'taskSubmissions.taskSubmissionFiles'
                    ])->orderBy('id', 'desc');
                },
                'assignedToMe.assignedBy.department',
                'assignedToMe.comments' => function($cq) {
                    $cq->with(['sentByUser.department', 'sentToUser.department', 'sender.department', 'user.department'])->latest();
                },
                'assignedOutAssignments.assignedTo.department',
                'assignedOutAssignments.assignedTo.roles'
            ])
            ->when($statusFilter, function($query, $statusFilter) {
                if ($statusFilter === 'overdue') {
                    $query->where('status', '!=', 'completed')->where('due_date', '<', now());
                } elseif ($statusFilter === 'due_soon') {
                    $query->where('status', '!=', 'completed')->whereBetween('due_date', [now(), now()->addDays(2)]);
                } elseif ($statusFilter === 'uncompleted') {
                    $query->where('status', '!=', 'completed');
                } elseif ($statusFilter === 'on_hold') {
                    $query->whereHas('assignments', function($sq) {
                        $sq->where('assigned_to', auth()->id())->whereIn('status', ['on_hold', 'hold_responded']);
                    });
                } elseif (in_array($statusFilter, ['pending', 'in progress', 'under review', 'completed', 'returned'])) {
                    $query->where(function($q) use ($statusFilter) {
                        $q->where('status', $statusFilter)
                          ->orWhereHas('assignments', function($sq) use ($statusFilter) {
                              $sq->where('assigned_to', auth()->id())->where('status', $statusFilter);
                          });
                    });
                } else {
                    $query->where('status', $statusFilter);
                }
            })
            ->when($searchTerm, function ($query, $searchTerm) {
                $query->where(function ($subQuery) use ($searchTerm) {
                    $subQuery->where('title', 'like', "%{$searchTerm}%")
                             ->orWhere('description', 'like', "%{$searchTerm}%")
                             ->orWhere('status', 'like', "%{$searchTerm}%" );
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $tasks->getCollection()->transform(function ($task) {
            $task->is_assigned = 1; // assigned to me indicator
            $task->comments_count = $task->assignment_comments_count ?? 0;
            $task->suggested_routing = \App\Services\RoutingService::resolveSmartRoutingForUser($task);
            return $task;
        });

        return $tasks;
    }

    public function getSubmissionsTask($request) {
        $searchTerm = $request->search;
        $statusFilter = $request->status;

        $tasks = Task::withCount(['taskdocs', 'assignmentComments'])->where(function($q) {
                // Submissions made by subordinates specifically awaiting my review
                $q->whereHas('assignments', function($sq) {
                    $sq->where('assigned_by', auth()->id())
                       ->where('status', 'under review');
                });
                if (auth()->user()->hasRole('super user') || auth()->user()->hasRole('Accountant-General')) {
                    $q->orWhere('status', 'under review')
                      ->orWhereHas('assignments', function($sq) {
                          $sq->where('status', 'under review');
                      });
                }
            })
            ->with([
                'taskdocs',
                'file.department',
                'minutes' => function($q) {
                    $q->with('user.department')->latest();
                },
                'assignments' => function($query) {
                    $query->with([
                        'assignedTo.department',
                        'assignedTo.roles',
                        'assignedBy.department',
                        'comments' => function($cq) {
                            $cq->with(['sentByUser.department', 'sentToUser.department', 'sender.department', 'user.department'])->latest();
                        },
                        'taskSubmissions.taskSubmissionFiles'
                    ])->orderBy('id', 'desc');
                },
                'assignedToMe.assignedBy.department',
                'assignedToMe.comments' => function($cq) {
                    $cq->with(['sentByUser.department', 'sentToUser.department', 'sender.department', 'user.department'])->latest();
                },
                'assignedOutAssignments.assignedTo.department',
                'assignedOutAssignments.assignedTo.roles'
            ])
            ->when($statusFilter, function($query, $statusFilter) {
                if ($statusFilter === 'overdue') {
                    $query->where('status', '!=', 'completed')->where('due_date', '<', now());
                } elseif ($statusFilter === 'due_soon') {
                    $query->where('status', '!=', 'completed')->whereBetween('due_date', [now(), now()->addDays(2)]);
                } elseif ($statusFilter === 'uncompleted') {
                    $query->where('status', '!=', 'completed');
                }
            })
            ->when($searchTerm, function ($query, $searchTerm) {
                $query->where(function ($subQuery) use ($searchTerm) {
                    $subQuery->where('title', 'like', "%{$searchTerm}%")
                             ->orWhere('description', 'like', "%{$searchTerm}%")
                             ->orWhere('status', 'like', "%{$searchTerm}%" );
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $tasks->getCollection()->transform(function ($task) {
            $task->is_assigned = 3; // review indicator
            $task->comments_count = $task->assignment_comments_count ?? 0;
            $task->suggested_routing = \App\Services\RoutingService::resolveSmartRoutingForUser($task);
            return $task;
        });

        return $tasks;
    }

    public function getBranchTask($request){
        $created_task = $this->getCreatedTask($request);
        $assigned_task = $this->getAssignedTask($request);
        $submissions_task = $this->getSubmissionsTask($request);
        return [
            'created_task' => $created_task,
            'assigned_task' => $assigned_task,
            'submissions_task' => $submissions_task
        ];
    }

    public function getFilteredTasks(Request $request)
    {
        $filter = $request->get('tab', 'my_tasks'); // my_tasks, my_submissions, received, sent, pending, returned, overdue, completed
        $searchTerm = $request->get('search');
        $userId = auth()->id();
        
        $query = Task::with(['assignments.assignedTo', 'assignments.assignedBy', 'assignedToMe']);

        switch ($filter) {
            case 'my_tasks':
                // Tasks assigned to me via TaskAssignment (Incoming Tasks)
                $query->where(function($q) use ($userId) {
                    $q->whereHas('assignments', function($sq) use ($userId) {
                        $sq->where('assigned_to', $userId)
                           ->whereNotIn('status', ['completed', 'returned', 'under review']);
                    })->orWhereHas('assignments', function($sq) use ($userId) {
                        $sq->where('assigned_by', $userId)
                           ->where('status', 'under review');
                    });
                });
                break;
                
            case 'my_submissions':
                // Tasks created by me or dispatched by me (Dispatched Tasks)
                $query->where(function($q) use ($userId) {
                    $q->where(function($q2) use ($userId) {
                          $q2->where('created_by', $userId)
                             ->orWhereHas('assignments', function($sq) use ($userId) {
                                 $sq->where('assigned_by', $userId)
                                    ->where('status', '!=', 'under review');
                             });
                      })
                      ->orWhereHas('assignments', function($sq) use ($userId) {
                          $sq->where('assigned_to', $userId)
                             ->where('status', 'under review');
                      });
                });
                break;
                
            case 'received':
                // Physical files/tasks received by me
                $query->whereHas('assignments', function($q) use ($userId) {
                    $q->where('assigned_to', $userId)
                      ->whereNotIn('status', ['returned', 'completed']);
                });
                break;
                
            case 'sent':
                // Physical files/tasks sent by me
                $query->where('created_by', $userId)
                      ->orWhereHas('assignments', function($q) use ($userId) {
                          $q->where('assigned_by', $userId);
                      });
                break;

            case 'pending':
                $query->where(function($q) use ($userId) {
                    $q->where('status', 'pending')
                      ->orWhereHas('assignments', function($sq) use ($userId) {
                          $sq->where('status', 'pending')->where(function($ssq) use ($userId) {
                              $ssq->where('assigned_to', $userId)->orWhere('assigned_by', $userId);
                          });
                      });
                });
                break;
                
            case 'in_progress':
                $query->where(function($q) use ($userId) {
                    $q->where('status', 'in progress')
                      ->orWhereHas('assignments', function($sq) use ($userId) {
                          $sq->where('status', 'in progress')->where(function($ssq) use ($userId) {
                              $ssq->where('assigned_to', $userId)->orWhere('assigned_by', $userId);
                          });
                      });
                });
                break;
                
            case 'review':
                $query->where(function($q) use ($userId) {
                    $q->where('status', 'under review')
                      ->orWhereHas('assignments', function($sq) use ($userId) {
                          $sq->where('status', 'under review')->where(function($ssq) use ($userId) {
                              $ssq->where('assigned_to', $userId)->orWhere('assigned_by', $userId);
                          });
                      });
                });
                break;

            case 'on_hold':
                $query->whereHas('assignments', function($sq) use ($userId) {
                    $sq->whereIn('status', ['on_hold', 'hold_responded'])->where(function($ssq) use ($userId) {
                        $ssq->where('assigned_to', $userId)->orWhere('assigned_by', $userId);
                    });
                });
                break;

            case 'returned':
                $query->whereIn('status', ['returned', 'revision', 'rejected'])
                      ->where(function($q) use ($userId) {
                          $q->where('created_by', $userId)
                            ->orWhereHas('assignments', function($sq) use ($userId) {
                                $sq->where('assigned_by', $userId);
                            });
                      });
                break;

            case 'overdue':
                $query->where('status', '!=', 'completed')
                      ->where(function($q) {
                          $q->where('due_date', '<', now())
                            ->orWhereHas('activeSteps', function($sq) {
                                $sq->where('due_date', '<', now());
                            });
                      });
                break;

            case 'due_soon':
                $query->where('status', '!=', 'completed')
                      ->where(function($q) {
                          $q->whereBetween('due_date', [now(), now()->addDays(2)])
                            ->orWhereHas('activeSteps', function($sq) {
                                $sq->whereBetween('due_date', [now(), now()->addDays(2)]);
                            });
                      });
                break;

            case 'completed':
                $query->where(function($q) use ($userId) {
                    $q->where('status', 'completed')
                      ->orWhereHas('assignments', function($sq) use ($userId) {
                          $sq->where('status', 'completed')->where(function($ssq) use ($userId) {
                              $ssq->where('assigned_to', $userId)->orWhere('assigned_by', $userId);
                          });
                      });
                });
                break;
        }

        if ($searchTerm) {
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('status', 'like', "%{$searchTerm}%");
            });
        }

        return $query->latest()->paginate(20)->withQueryString();
    }

    public function index(Request $request){
        // For backwards compatibility and new UI
        $allTask = $this->getBranchTask($request);
        $created_task = $allTask['created_task'] ?: [];
        $assigned_task = $allTask['assigned_task'] ?: [];

        $tasks = $this->getFilteredTasks($request);
        $activeTab = $request->get('tab', 'my_tasks');
        $branchType = $this->getBranchType();

        $userId = auth()->id();
        
        $assignedQuery = Task::where(function($q) use ($userId) {
            $q->whereHas('assignments', function($sq) use ($userId) {
                $sq->where('assigned_to', $userId)
                   ->whereNotIn('status', ['completed', 'returned', 'under review']);
            });
        });

        $assignedTaskStats = [
            'total' => (clone $assignedQuery)->count(),
            'pending' => (clone $assignedQuery)->whereHas('assignments', function($sq) use ($userId) { $sq->where('assigned_to', $userId)->where('status', 'pending'); })->count(),
            'in_progress' => (clone $assignedQuery)->whereHas('assignments', function($sq) use ($userId) { $sq->where('assigned_to', $userId)->where('status', 'in progress'); })->count(),
            'review' => (clone $assignedQuery)->whereHas('assignments', function($sq) use ($userId) { $sq->where('assigned_to', $userId)->where('status', 'under review'); })->count(),
            'completed' => (clone $assignedQuery)->whereHas('assignments', function($sq) use ($userId) { $sq->where('assigned_to', $userId)->where('status', 'completed'); })->count(),
            'uncompleted' => (clone $assignedQuery)->where('status', '!=', 'completed')->count(),
            'returned' => (clone $assignedQuery)->whereHas('assignments', function($sq) use ($userId) { $sq->where('assigned_to', $userId)->where('status', 'returned'); })->count(),
            'overdue' => (clone $assignedQuery)->where('status', '!=', 'completed')->where('due_date', '<', now())->count(),
            'due_soon' => (clone $assignedQuery)->where('status', '!=', 'completed')->whereBetween('due_date', [now(), now()->addDays(2)])->count(),
            'on_hold' => (clone $assignedQuery)->whereHas('assignments', function($q) use($userId) { $q->where('assigned_to', $userId)->whereIn('status', ['on_hold', 'hold_responded']); })->count(),
        ];

        $createdQuery = Task::where(function($q) use ($userId) {
            $q->where('created_by', $userId)
              ->orWhereHas('assignments', function($sq) use ($userId) {
                  $sq->where('assigned_by', $userId)
                     ->where('status', '!=', 'under review');
              });
        });

        $createdTaskStats = [
            'total' => (clone $createdQuery)->count(),
            'pending' => (clone $createdQuery)->whereHas('assignments', function($sq) use ($userId) { $sq->where('assigned_by', $userId)->where('status', 'pending'); })->count(),
            'in_progress' => (clone $createdQuery)->whereHas('assignments', function($sq) use ($userId) { $sq->where('assigned_by', $userId)->where('status', 'in progress'); })->count(),
            'review' => (clone $createdQuery)->whereHas('assignments', function($sq) use ($userId) { $sq->where('assigned_by', $userId)->where('status', 'under review'); })->count(),
            'completed' => (clone $createdQuery)->where(function($q) use ($userId) { $q->where('status', 'completed')->orWhereHas('assignments', function($sq) use ($userId) { $sq->where('assigned_by', $userId)->where('status', 'completed'); }); })->count(),
            'uncompleted' => (clone $createdQuery)->where('status', '!=', 'completed')->count(),
            'returned' => (clone $createdQuery)->whereHas('assignments', function($sq) use ($userId) { $sq->where('assigned_by', $userId)->where('status', 'returned'); })->count(),
            'overdue' => (clone $createdQuery)->where('status', '!=', 'completed')->where('due_date', '<', now())->count(),
            'due_soon' => (clone $createdQuery)->where('status', '!=', 'completed')->whereBetween('due_date', [now(), now()->addDays(2)])->count(),
            'on_hold' => (clone $createdQuery)->whereHas('assignments', function($q) use ($userId) { $q->where('assigned_by', $userId)->whereIn('status', ['on_hold', 'hold_responded']); })->count(),
        ];

        $submissionQuery = Task::where(function($q) use ($userId) {
            $q->whereHas('assignments', function($sq) use ($userId) {
                $sq->where('assigned_by', $userId)
                   ->where('status', 'under review');
            });
            if (auth()->user()->hasRole('super user') || auth()->user()->hasRole('Accountant-General')) {
                $q->orWhere('status', 'under review')
                  ->orWhereHas('assignments', function($sq) {
                      $sq->where('status', 'under review');
                  });
            }
        });

        $submissionTaskStats = [
            'total' => (clone $submissionQuery)->count(),
            'pending' => (clone $submissionQuery)->where('status', 'under review')->count(),
            'in_progress' => 0,
            'review' => (clone $submissionQuery)->count(),
            'completed' => 0,
            'uncompleted' => (clone $submissionQuery)->count(),
            'returned' => 0,
            'overdue' => (clone $submissionQuery)->where('due_date', '<', now())->count(),
            'due_soon' => (clone $submissionQuery)->whereBetween('due_date', [now(), now()->addDays(2)])->count(),
            'on_hold' => 0,
        ];

        $submissions_task = $allTask['submissions_task'] ?: [];

        return inertia('Workflow/Workflows', compact('created_task', 'assigned_task', 'submissions_task', 'tasks', 'activeTab', 'branchType', 'assignedTaskStats', 'createdTaskStats', 'submissionTaskStats'));
    }

    public function getBranchType(){
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
    }

    public function searchWorkflow(Request $request){
        // For backwards compatibility
        $allTask = $this->getBranchTask($request);
        $tasks = $this->getFilteredTasks($request);
        
        return response()->json([
            'created_task' => $allTask['created_task'],
            'assigned_task' => $allTask['assigned_task'],
            'submissions_task' => $allTask['submissions_task'],
            'tasks' => $tasks
        ]);
    }
}
