<template>
  <main class="min-h-screen p-4 lg:p-8 bg-gradient-to-br from-slate-50 to-blue-50/30 font-sans">
    <div class="mb-6 flex flex-col md:flex-row items-start md:items-center w-full justify-between gap-4 backdrop-blur-sm bg-white/70 p-4 rounded-2xl shadow-xs border border-white/80 relative z-30">
      <!-- Left side: Navigation & Identity -->
      <div class="flex items-center gap-3 flex-wrap">
        <Link href="/dashboard/workflow">
          <div class="flex items-center gap-2 cursor-pointer group px-3.5 py-2 hover:bg-white rounded-xl transition-all shadow-xs border border-transparent hover:border-gray-200">
            <Icon icon="flowbite:angle-left-outline" class="text-gray-500 group-hover:-translate-x-1 transition-transform" />
            <p class="font-medium text-xs md:text-sm text-gray-700 group-hover:text-primary transition-colors">Back to Workflow</p>
          </div>
        </Link>
        <span v-if="props.task?.tracking_id" class="text-xs font-mono font-semibold text-gray-500 bg-gray-100/80 px-2.5 py-1.5 rounded-lg border border-gray-200 flex items-center gap-1.5 shadow-2xs">
          <Icon icon="lucide:hash" width="13" /> {{ props.task?.tracking_id }}
        </span>
        <span v-if="props.task?.priority" class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full shadow-2xs"
          :class="{
            'bg-red-50 text-red-600 border border-red-200': props.task.priority == 2,
            'bg-orange-50 text-orange-600 border border-orange-200': props.task.priority == 3,
            'bg-blue-50 text-blue-600 border border-blue-200': props.task.priority == 1
          }">
          {{ props.task.priority == 1 ? 'Low Priority' : props.task.priority == 2 ? 'High Priority' : 'Medium Priority' }}
        </span>
      </div>

      <!-- Right side: Layout Mode Switcher & Stage-Aware Action Hub -->
      <div class="flex items-center gap-3 font-semibold text-gray-600 flex-wrap self-end md:self-auto relative">

        <!-- Workspace Layout Mode Pill -->
        <div class="hidden xl:flex items-center bg-gray-100/90 p-1 rounded-xl border border-gray-200/80 shadow-2xs mr-1">
          <button 
            type="button" 
            @click="workspaceLayoutMode = 'split'" 
            :class="['px-2.5 py-1.5 text-xs font-semibold rounded-lg transition-all flex items-center gap-1.5 cursor-pointer', workspaceLayoutMode === 'split' ? 'bg-white text-blue-600 shadow-2xs' : 'text-gray-500 hover:text-gray-700']"
            title="Default 60/40 Split"
          >
            <Icon icon="lucide:columns-2" width="14" />
            <span>Split</span>
          </button>
          <button 
            type="button" 
            @click="workspaceLayoutMode = 'balanced'" 
            :class="['px-2.5 py-1.5 text-xs font-semibold rounded-lg transition-all flex items-center gap-1.5 cursor-pointer', workspaceLayoutMode === 'balanced' ? 'bg-white text-blue-600 shadow-2xs' : 'text-gray-500 hover:text-gray-700']"
            title="Equal 50/50 Split"
          >
            <Icon icon="lucide:layout-grid" width="14" />
            <span>50/50</span>
          </button>
          <button 
            type="button" 
            @click="workspaceLayoutMode = 'focus'" 
            :class="['px-2.5 py-1.5 text-xs font-semibold rounded-lg transition-all flex items-center gap-1.5 cursor-pointer', workspaceLayoutMode === 'focus' ? 'bg-white text-blue-600 shadow-2xs' : 'text-gray-500 hover:text-gray-700']"
            title="Document Only / Focus Mode"
          >
            <Icon icon="lucide:maximize-2" width="14" />
            <span>Focus</span>
          </button>
        </div>

        <!-- PRIMARY NEXT ACTION BUTTON (Stage-Aware) -->
        
        <!-- Case 1: Task Submitted By Me -->
        <div v-if="isTaskSubmittedByMe" class="flex items-center gap-2 px-3.5 py-2 bg-blue-50 border border-blue-200 text-blue-900 rounded-xl text-xs font-bold shadow-2xs">
          <Icon icon="lucide:check-circle-2" class="text-blue-600 shrink-0" width="16" />
          <span>Submitted • With {{ currentTaskHolder?.first_name }} {{ currentTaskHolder?.last_name || 'Approver' }}</span>
        </div>

        <!-- Case 2: Resume Task (when hold responded) -->
        <button 
          v-else-if="props.task && props.task.assigned_to_me && props.task.assigned_to_me.status === 'hold_responded' && props.is_assigned == 1"
          @click="resumeTaskAction"
          class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md hover:shadow-lg hover:shadow-emerald-500/20 hover:scale-105 active:scale-95 transition-all rounded-xl px-5 py-2.5 flex items-center gap-2 text-xs md:text-sm font-bold cursor-pointer"
        >
          <Icon icon="mdi:play-circle-outline" width="18" />
          <span>Resume Task</span>
        </button>

        <!-- Case 3: Subordinate Submission Under Review (Supervisor Action) -->
        <template v-else-if="is_subordinate_under_review || isSubmissionApproved">
          <!-- Step A: Needs Approval First -->
          <button 
            v-if="!isSubmissionApproved"
            @click="openApproveOnlyModal = true"
            class="bg-gradient-to-r from-emerald-600 via-teal-600 to-teal-500 text-white shadow-md hover:shadow-lg hover:shadow-emerald-500/30 hover:scale-105 active:scale-95 transition-all rounded-xl px-5 py-2.5 flex items-center gap-2 text-xs md:text-sm font-bold cursor-pointer border-0"
          >
            <Icon icon="mdi:check-decagram" width="20" />
            <span>Approve Submission</span>
          </button>

          <!-- Step B: Approved & Ready to Submit Upward / Final Approval -->
          <button 
            v-else
            :disabled="isApproving"
            @click="openApproveModal = true"
            class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md hover:shadow-lg hover:shadow-blue-500/30 hover:scale-105 active:scale-95 transition-all rounded-xl px-5 py-2.5 flex items-center gap-2 text-xs md:text-sm font-bold cursor-pointer border-0 disabled:opacity-60 disabled:cursor-not-allowed"
          >
            <Icon v-if="isApproving" icon="lucide:loader-2" class="animate-spin" width="18" />
            <Icon v-else icon="lucide:check-circle-2" width="18" />
            <span>{{ approveButtonLabel }}</span>
          </button>
        </template>

        <!-- Case 4: Incoming Task Assigned To Me (Legacy Desk Officer Action) -->
        <button 
          v-else-if="!props.task?.workflow_version_id && props.task && props.task.assigned_to_me && (props.task.assigned_to_me.status == 'pending' || props.task.assigned_to_me.status == 'revision' || props.task.assigned_to_me.status == 'in progress') && props.is_assigned == 1"
          @click="submitForReviewModal = true"
          class="bg-gradient-to-r from-emerald-600 to-teal-500 text-white border-0 hover:from-emerald-700 hover:to-teal-600 hover:scale-105 active:scale-95 transition-all shadow-md hover:shadow-lg hover:shadow-emerald-500/30 rounded-xl px-5 py-2.5 flex items-center gap-2 text-xs md:text-sm font-bold cursor-pointer"
        >
          <Icon icon="mdi:tick-circle-outline" width="20" />
          <span>Submit Task</span>
        </button>

        <!-- Case 5: Task Delegation / Reassignment -->
        <button 
          v-else-if="canReassignTask && !user_roles.includes('regular user') && !user_roles.includes('regular users') && (user_roles.includes('super user') || (props.task && props.task.assigned_to_me && props.task.assigned_to_me.status !='completed' && branchType !='users' && !user_roles.includes('super user')) || (props.task?.created_by == current_user?.id && branchType !='users'))"
          @click="Re_Assign_Modal = true"
          class="bg-gradient-to-r from-blue-600 to-blue-500 text-white hover:from-blue-700 hover:to-blue-600 hover:scale-105 active:scale-95 hover:shadow-lg hover:shadow-blue-500/30 transition-all shadow-md rounded-xl px-5 py-2.5 flex items-center gap-2 text-xs md:text-sm font-bold cursor-pointer"
        >
          <Icon :icon="(props.task.assignments && props.task.assignments.length > 0) ? 'lucide:user-check' : 'lucide:user-plus'" width="18" />
          <span>{{ (props.task.assignments && props.task.assignments.length > 0) ? 'Reassign Task' : 'Assign Task' }}</span>
        </button>

        <!-- Case 6: Reopen Completed Task (Creator Action) -->
        <button 
          v-else-if="props.task?.status === 'completed' && props.task?.created_by == current_user?.id"
          @click="reOpenModal = true"
          class="bg-gradient-to-r from-rose-600 to-red-500 text-white border-0 hover:from-rose-700 hover:to-red-600 hover:scale-105 active:scale-95 transition-all shadow-md hover:shadow-lg hover:shadow-rose-500/30 rounded-xl px-5 py-2.5 flex items-center gap-2 text-xs md:text-sm font-bold cursor-pointer"
        >
          <Icon icon="teenyicons:unlock-outline" width="18" />
          <span>Reopen Task</span>
        </button>

        <!-- SECONDARY ACTIONS DROPDOWN (More Directives) -->
        <div v-if="hasSecondaryActions" :class="['relative', moreActionsOpen ? 'z-50' : '']">
          <button 
            type="button"
            @click="moreActionsOpen = !moreActionsOpen"
            class="px-3.5 py-2.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 transition-all flex items-center gap-1.5 shadow-xs hover:border-gray-300 text-xs md:text-sm font-semibold cursor-pointer"
            title="Additional Task Directives & Options"
          >
            <Icon icon="lucide:sliders-horizontal" width="16" class="text-gray-500" />
            <span>Directives</span>
            <Icon icon="lucide:chevron-down" width="14" class="text-gray-400 transition-transform" :class="{'rotate-180': moreActionsOpen}" />
          </button>

          <!-- Backdrop overlay to dismiss dropdown -->
          <div v-if="moreActionsOpen" @click="moreActionsOpen = false" class="fixed inset-0 z-40"></div>

          <!-- Dropdown Popover Menu -->
          <div 
            v-if="moreActionsOpen" 
            class="absolute right-0 top-full mt-2 w-64 bg-white rounded-2xl shadow-2xl border border-gray-100 py-2 z-50 animate-in fade-in zoom-in-95 duration-150"
          >
            <div class="px-3.5 py-1.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100">
              Task Directives
            </div>

            <!-- Return with Notes -->
            <button 
              v-if="canReturnTask"
              @click="moreActionsOpen = false; handleOpenReturn()"
              class="w-full text-left px-3.5 py-2.5 hover:bg-red-50 text-red-600 transition-colors flex items-center gap-2.5 text-xs font-semibold cursor-pointer"
            >
              <Icon icon="lucide:undo-2" width="16" class="text-red-500 shrink-0" />
              <div>
                <p>Return with Notes</p>
                <p class="text-[10px] text-red-400 font-normal">Send back with revision directives</p>
              </div>
            </button>

            <!-- Request Edit -->
            <button 
              v-if="canRequestEdit"
              @click="moreActionsOpen = false; RequestRevisionModal = true"
              class="w-full text-left px-3.5 py-2.5 hover:bg-orange-50 text-orange-600 transition-colors flex items-center gap-2.5 text-xs font-semibold cursor-pointer"
            >
              <Icon icon="mdi:file-document-edit-outline" width="16" class="text-orange-500 shrink-0" />
              <div>
                <p>Request Edit</p>
                <p class="text-[10px] text-orange-400 font-normal">Flag specific fields for revision</p>
              </div>
            </button>

            <!-- Query / Hold Task -->
            <button 
              v-if="canRequestInfo"
              @click="moreActionsOpen = false; requestInfoModal = true"
              class="w-full text-left px-3.5 py-2.5 hover:bg-purple-50 text-purple-700 transition-colors flex items-center gap-2.5 text-xs font-semibold cursor-pointer"
            >
              <Icon icon="mdi:message-question-outline" width="16" class="text-purple-600 shrink-0" />
              <div>
                <p>{{ is_subordinate_under_review ? 'Query Task' : 'Hold / Request Info' }}</p>
                <p class="text-[10px] text-purple-400 font-normal">Place on temporary pause for clarity</p>
              </div>
            </button>

            <!-- Reroute to Department -->
            <button 
              v-if="canRerouteTask"
              :disabled="!isSubmissionApproved"
              @click="moreActionsOpen = false; isSubmissionApproved && (openAgfRerouteModal = true)"
              class="w-full text-left px-3.5 py-2.5 hover:bg-purple-50 text-purple-700 transition-colors flex items-center gap-2.5 text-xs font-semibold cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <Icon icon="lucide:git-fork" width="16" class="text-purple-600 shrink-0" />
              <div>
                <p>Reroute to Department</p>
                <p class="text-[10px] text-purple-400 font-normal">Hand off to another directorate</p>
              </div>
            </button>

            <!-- Reassign to Subordinate -->
            <button 
              v-if="canReassignSecondary"
              @click="moreActionsOpen = false; (!isSubmissionApproved && is_subordinate_under_review ? approveSubordinateTask(true) : (Re_Assign_Modal = true))"
              class="w-full text-left px-3.5 py-2.5 hover:bg-blue-50 text-blue-600 transition-colors flex items-center gap-2.5 text-xs font-semibold cursor-pointer"
            >
              <Icon icon="lucide:user-plus" width="16" class="text-blue-500 shrink-0" />
              <div>
                <p>Reassign Task</p>
                <p class="text-[10px] text-blue-400 font-normal">Delegate to subordinate officer</p>
              </div>
            </button>

            <!-- Compare Versions (Side-by-Side Diff) -->
            <button 
              type="button"
              @click="moreActionsOpen = false; diffViewerModal = true"
              class="w-full text-left px-3.5 py-2.5 hover:bg-blue-50 text-blue-700 transition-colors flex items-center gap-2.5 text-xs font-semibold cursor-pointer"
            >
              <Icon icon="lucide:columns-2" width="16" class="text-blue-600 shrink-0" />
              <div>
                <p>Compare Versions</p>
                <p class="text-[10px] text-blue-400 font-normal">Side-by-side visual diff inspector</p>
              </div>
            </button>

            <div v-if="canEditTask || canRateTask" class="border-t border-gray-100 my-1"></div>

            <!-- Edit Task Details -->
            <button 
              v-if="canEditTask"
              @click="moreActionsOpen = false; editModal = true"
              class="w-full text-left px-3.5 py-2.5 hover:bg-gray-50 text-gray-700 transition-colors flex items-center gap-2.5 text-xs font-semibold cursor-pointer"
            >
              <Icon icon="icon-park-outline:edit" width="16" class="text-gray-500 shrink-0" />
              <span>Edit Task Details</span>
            </button>

            <!-- Rate Task -->
            <button 
              v-if="canRateTask"
              @click="moreActionsOpen = false; ratingsModal()"
              class="w-full text-left px-3.5 py-2.5 hover:bg-amber-50 text-amber-700 transition-colors flex items-center gap-2.5 text-xs font-semibold cursor-pointer"
            >
              <Icon icon="lucide:star" width="16" class="text-amber-500 shrink-0" />
              <span>Rate Task Performance</span>
            </button>
          </div>
        </div>

      </div>
      <div class="absolute top-6 cursor-pointer z-50 right-6 block lg:hidden p-2 bg-white rounded-lg shadow-sm border" @click="subNavVisible = true">
        <Icon icon="mdi:hamburger-menu-back" width="24" class="text-gray-600" />
      </div>
    </div>

    <!-- Live Administrative Metro Flight Path -->
    <WorkflowMetroMap v-if="props.task" :task="props.task" class="mb-6" @select-station="handleStationClick" />

    <!-- Generic Dynamic Workflow Orchestration Engine (Workflow Builder Blueprint) -->
    <GenericWorkflowPanel 
      v-if="props.task && props.task.workflow_version_id" 
      :task="props.task" 
      class="mb-6 shadow-sm border border-blue-200"
    />

    <!-- Pinned AGF Executive Directive Banner -->
    <div 
      v-if="agfDirectiveMinute" 
      class="mb-6 p-5 rounded-2xl bg-gradient-to-r from-amber-500/10 via-amber-400/5 to-yellow-500/10 border-2 border-amber-400/60 shadow-md backdrop-blur-sm relative overflow-hidden"
    >
      <div class="absolute -right-10 -bottom-10 w-36 h-36 bg-amber-400/10 rounded-full blur-2xl pointer-events-none"></div>
      <div class="flex items-start gap-4 relative z-10">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-amber-600 to-yellow-500 text-white flex items-center justify-center shadow-md shadow-amber-500/30 shrink-0 border border-amber-300">
          <Icon icon="lucide:crown" width="24" />
        </div>
        <div class="flex-1 min-w-0">
          <div class="flex items-center justify-between gap-3 flex-wrap mb-1.5">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="text-xs font-black text-amber-950 uppercase tracking-widest flex items-center gap-1.5">
                <Icon icon="lucide:shield-check" width="14" class="text-amber-700" />
                AGF Executive Directive
              </span>
              <span class="text-[11px] font-semibold bg-amber-200/70 text-amber-900 px-2.5 py-0.5 rounded-full border border-amber-300/80">
                {{ agfDirectiveMinute.officer }}
              </span>
            </div>
            <span class="text-[11px] font-mono font-medium text-amber-800 bg-white/70 px-2.5 py-0.5 rounded-md border border-amber-200 shadow-2xs">
              {{ formatDate(agfDirectiveMinute.date) }}
            </span>
          </div>

          <div class="mt-2 p-4 rounded-xl bg-white/90 border border-amber-200 shadow-xs">
            <p class="text-sm md:text-base font-serif text-slate-900 leading-relaxed italic">
              "{{ agfDirectiveMinute.directive }}"
            </p>
          </div>
          
          <p class="text-[11px] text-amber-800/80 mt-2 font-medium flex items-center gap-1.5">
            <Icon icon="lucide:alert-circle" width="13" class="text-amber-600" />
            <span>Mandatory institutional directive. Must be addressed in all subsequent Directorate findings and memos.</span>
          </p>
        </div>
      </div>
    </div>

    <section class="lg:flex items-start gap-8 w-full transition-all duration-300">

      <div :class="[
        'bg-white/80 backdrop-blur-md rounded-2xl p-6 shadow-sm border border-white/60 transition-all duration-300',
        workspaceLayoutMode === 'focus' ? 'w-full' : (workspaceLayoutMode === 'balanced' ? 'w-full lg:w-[50%]' : 'w-full lg:w-[60%]')
      ]">
        
        <!-- CONSOLIDATED DOCKET STATUS STRIP -->
        <div v-if="isTaskSubmittedByMe || isTaskReturnedToMe || (props.task && props.task.assigned_to_me && (props.task.assigned_to_me.status === 'on_hold' || props.task.assigned_to_me.status === 'hold_responded')) || pendingInfoRequest" 
          class="mb-6 p-4 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs border transition-all duration-200"
          :class="{
            'bg-gradient-to-r from-blue-50 via-indigo-50/50 to-blue-50 border-blue-200': isTaskSubmittedByMe && !isTaskReturnedToMe,
            'bg-gradient-to-r from-rose-50 via-amber-50/50 to-rose-50 border-rose-200': isTaskReturnedToMe,
            'bg-gradient-to-r from-amber-50 to-yellow-50 border-amber-200': props.task?.assigned_to_me?.status === 'on_hold',
            'bg-gradient-to-r from-emerald-50 to-teal-50 border-emerald-200': props.task?.assigned_to_me?.status === 'hold_responded',
            'bg-gradient-to-r from-purple-50 to-blue-50 border-purple-200': pendingInfoRequest
          }"
        >
          <div class="flex items-center gap-3.5 min-w-0">
            <!-- Icon Avatar -->
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 shadow-2xs text-white"
              :class="{
                'bg-blue-600': isTaskSubmittedByMe && !isTaskReturnedToMe,
                'bg-rose-600': isTaskReturnedToMe,
                'bg-amber-600': props.task?.assigned_to_me?.status === 'on_hold',
                'bg-emerald-600': props.task?.assigned_to_me?.status === 'hold_responded',
                'bg-purple-600': pendingInfoRequest
              }"
            >
              <Icon v-if="isTaskReturnedToMe" icon="lucide:corner-up-left" width="20" />
              <Icon v-else-if="props.task?.assigned_to_me?.status === 'on_hold'" icon="mdi:alert-circle-outline" width="22" />
              <Icon v-else-if="props.task?.assigned_to_me?.status === 'hold_responded'" icon="mdi:check-circle-outline" width="22" />
              <Icon v-else-if="pendingInfoRequest" icon="mdi:information-outline" width="22" />
              <Icon v-else icon="lucide:clock" width="20" />
            </div>

            <!-- Descriptive Text -->
            <div class="min-w-0">
              <div class="flex items-center gap-2 flex-wrap">
                <h4 class="text-sm font-bold truncate"
                  :class="{
                    'text-blue-900': isTaskSubmittedByMe && !isTaskReturnedToMe,
                    'text-rose-900': isTaskReturnedToMe,
                    'text-amber-900': props.task?.assigned_to_me?.status === 'on_hold',
                    'text-emerald-900': props.task?.assigned_to_me?.status === 'hold_responded',
                    'text-purple-900': pendingInfoRequest
                  }"
                >
                  <span v-if="isTaskReturnedToMe">Task Returned for Revision</span>
                  <span v-else-if="props.task?.assigned_to_me?.status === 'on_hold'">Task On Hold</span>
                  <span v-else-if="props.task?.assigned_to_me?.status === 'hold_responded'">Hold Responded & Ready</span>
                  <span v-else-if="pendingInfoRequest">Information Requested</span>
                  <span v-else>Task Submitted & In Review</span>
                </h4>
                
                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full uppercase tracking-wider"
                  :class="{
                    'bg-blue-100 text-blue-800 border border-blue-200': isTaskSubmittedByMe && !isTaskReturnedToMe,
                    'bg-rose-100 text-rose-800 border border-rose-200 animate-pulse': isTaskReturnedToMe,
                    'bg-amber-100 text-amber-800 border border-amber-200': props.task?.assigned_to_me?.status === 'on_hold',
                    'bg-emerald-100 text-emerald-800 border border-emerald-200': props.task?.assigned_to_me?.status === 'hold_responded',
                    'bg-purple-100 text-purple-800 border border-purple-200': pendingInfoRequest
                  }"
                >
                  <span v-if="isTaskReturnedToMe">Action Required</span>
                  <span v-else-if="props.task?.assigned_to_me?.status === 'on_hold'">Paused</span>
                  <span v-else-if="props.task?.assigned_to_me?.status === 'hold_responded'">Ready</span>
                  <span v-else-if="pendingInfoRequest">Response Needed</span>
                  <span v-else>Awaiting Review</span>
                </span>
              </div>

              <p class="text-xs mt-0.5"
                :class="{
                  'text-blue-700': isTaskSubmittedByMe && !isTaskReturnedToMe,
                  'text-rose-700': isTaskReturnedToMe,
                  'text-amber-700': props.task?.assigned_to_me?.status === 'on_hold',
                  'text-emerald-700': props.task?.assigned_to_me?.status === 'hold_responded',
                  'text-purple-700': pendingInfoRequest
                }"
              >
                <template v-if="isTaskReturnedToMe">
                  Reviewer returned this file with revision directives. Check the notes in Submissions, update the document, and submit.
                </template>
                <template v-else-if="props.task?.assigned_to_me?.status === 'on_hold'">
                  This task is currently on hold waiting for requested information before execution can continue.
                </template>
                <template v-else-if="props.task?.assigned_to_me?.status === 'hold_responded'">
                  The requested information has been provided. You can now resume working on this docket.
                </template>
                <template v-else-if="pendingInfoRequest">
                  {{ pendingInfoRequest.requested_by?.first_name }} has requested information from you regarding this task.
                </template>
                <template v-else>
                  Task is currently with <span class="font-bold text-blue-900">{{ currentTaskHolder?.first_name }} {{ currentTaskHolder?.last_name || 'Next Approver' }}</span>
                  <span v-if="currentTaskHolder?.department?.title"> ({{ currentTaskHolder.department.title }})</span>. Action controls are held until review completes.
                </template>
              </p>
            </div>
          </div>

          <!-- Quick Action Buttons -->
          <div class="shrink-0 flex items-center gap-2 self-end sm:self-auto">
            <button 
              v-if="isTaskReturnedToMe"
              type="button" 
              @click="toggleSidemenu('submission')" 
              class="px-3.5 py-1.5 rounded-xl bg-rose-600 text-white text-xs font-semibold hover:bg-rose-700 transition-colors shadow-2xs cursor-pointer flex items-center gap-1.5"
            >
              <Icon icon="lucide:file-text" width="14" />
              <span>View Review Notes</span>
            </button>

            <button 
              v-if="props.task?.assigned_to_me?.status === 'hold_responded'"
              @click="resumeTaskAction" 
              class="bg-emerald-600 text-white px-3.5 py-1.5 rounded-xl text-xs font-semibold hover:bg-emerald-700 transition-colors shadow-2xs flex items-center gap-1.5 cursor-pointer"
            >
              <Icon icon="mdi:play" width="14" />
              <span>Resume Task</span>
            </button>

            <button 
              v-if="pendingInfoRequest"
              @click="openProvideInfoModal(pendingInfoRequest)" 
              class="bg-blue-600 text-white px-3.5 py-1.5 rounded-xl text-xs font-semibold hover:bg-blue-700 transition-colors shadow-2xs flex items-center gap-1.5 cursor-pointer"
            >
              <Icon icon="lucide:message-circle" width="14" />
              <span>Provide Info</span>
            </button>
          </div>
        </div>
        <div class="pb-6 flex items-start justify-between gap-4 border-b border-gray-100 mb-6 flex-wrap">
          <div class="flex items-start gap-4">
            <div class="p-3 bg-blue-50 text-blue-600 rounded-xl mt-1 shrink-0">
              <Icon icon="lucide:file-text" width="24" />
            </div>
            <div>
              <p class="text-2xl font-bold text-gray-800 tracking-tight">{{ props.task?.title }}</p>
              <div class="flex items-center gap-2.5 flex-wrap mt-1.5">
                <p v-if="props.task?.tracking_id" class="text-sm font-medium text-gray-500 flex items-center gap-1.5 bg-gray-50/80 px-2.5 py-1 rounded-md w-fit border border-gray-100">
                  <Icon icon="lucide:hash" width="14" /> {{ props.task?.tracking_id }}
                </p>
                <a v-if="props.task?.folder_id" 
                  :href="'/dashboard/folder-view/department/' + props.task.folder_id + '/' + (props.task.department_id || 1)"
                  target="_blank"
                  class="text-xs font-semibold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 px-3 py-1 rounded-md border border-emerald-200 flex items-center gap-1.5 transition-colors shadow-2xs"
                  :title="'Folder No: ' + (props.task.archived_folder?.number || 'View Folder')">
                  <Icon icon="fluent:folder-open-20-filled" width="15" class="text-emerald-600" />
                  <span>Archived to Dept Repository (Folder: {{ props.task.archived_folder?.number || ('#' + props.task.folder_id) }})</span>
                  <Icon icon="lucide:external-link" width="12" class="text-emerald-500" />
                </a>
              </div>
            </div>
          </div>
        </div>

        <div class="h-[800px] overflow-y-auto custom-scrollbar pr-2 rounded-xl">
          <div>
          <div>
            <div class="bg-gray-50/50 border border-gray-100 p-2 w-full rounded-2xl shadow-inner" v-if="activePreviewDoc">
              <img v-if="fileType(activePreviewDoc) == 'image'"
                :src="activePreviewDoc.path" alt=""
                class="overflow-hidden rounded-xl w-full object-cover" />

              <div v-else-if="fileType(activePreviewDoc) == 'pdf'" class="w-full h-[85vh] min-h-[750px]">
                <PDFEditor ref="pdfEditorRef" :url="(activePreviewDoc.path.startsWith('/') ? activePreviewDoc.path : '/' + activePreviewDoc.path) + '?t=' + pdfTimestamp" @save-pdf="handleSavePdf" />
              </div>

              <div v-else class="w-full h-96 flex flex-col items-center justify-center bg-gray-50 rounded-xl border border-dashed border-gray-300 p-8 text-center">
                <Icon icon="lucide:file-text" class="w-16 h-16 text-gray-400 mb-3" />
                <p class="text-gray-700 font-semibold text-lg">{{ activePreviewDoc.file_name || activePreviewDoc.name || 'Document' }}</p>
                <p class="text-sm text-gray-500 mt-1">Direct preview is not supported for this file type.</p>
                <button @click="downloadFile(activePreviewDoc.path)" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2 text-sm font-medium cursor-pointer shadow-sm">
                  <Icon icon="lucide:download" width="16" /> Download File
                </button>
              </div>
            </div>
            
            <div class="bg-gray-50/50 border border-gray-100 p-2 w-full rounded-2xl shadow-inner" v-else-if="props.task?.file && props.task?.file?.path">
              <img v-if="fileType({path: props.task.file.path}) == 'image'"
                :src="props.task.file.path" alt=""
                class="overflow-hidden rounded-xl w-full object-cover" />

              <div v-else-if="fileType({path: props.task.file.path}) == 'pdf'" class="w-full h-[85vh] min-h-[750px]">
                <PDFEditor ref="pdfEditorRef" :url="(props.task.file.path.startsWith('/') ? props.task.file.path : '/' + props.task.file.path) + '?t=' + pdfTimestamp" @save-pdf="handleSavePdf" />
              </div>
            </div>

            <div v-else class="w-full h-[600px] flex flex-col items-center justify-center bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200 p-8 text-center">
              <div class="w-16 h-16 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center mb-4">
                <Icon icon="lucide:file-question" class="w-8 h-8" />
              </div>
              <p class="text-gray-800 font-bold text-lg">No Document Selected</p>
              <p class="text-sm text-gray-500 mt-1 max-w-sm">No previewable PDF document found for this task. Please select a document from the Task Details or Submissions tab.</p>
            </div>
          </div>
            
          </div>
        </div>
      </div>

      <!-- Floating restore button when in full focus mode -->
      <button 
        v-if="workspaceLayoutMode === 'focus'"
        @click="workspaceLayoutMode = 'split'"
        class="fixed bottom-8 right-8 z-40 bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-5 py-3 rounded-2xl shadow-xl hover:shadow-2xl hover:scale-105 active:scale-95 flex items-center gap-2.5 text-xs md:text-sm font-semibold transition-all cursor-pointer border border-white/20 animate-in fade-in"
        title="Restore Details & Discussions Panel"
      >
        <Icon icon="lucide:panel-right-open" width="18" />
        <span>Show Details & Discussions</span>
      </button>

      <div v-show="workspaceLayoutMode !== 'focus'" :class="[
        'bg-white/90 backdrop-blur-xl border border-white hidden lg:flex flex-col h-[920px] overflow-hidden rounded-2xl shadow-lg relative transition-all duration-300',
        workspaceLayoutMode === 'balanced' ? 'w-full lg:w-[50%]' : 'w-full lg:w-[40%]'
      ]">
        
        <!-- Tabs Header -->
        <div class="flex items-center border-b border-gray-200 bg-gray-50/80 px-4 pt-4 shrink-0 overflow-x-auto custom-scrollbar">
          <button @click="activeTab = 'details'" class="px-5 py-3 font-medium text-sm transition-all border-b-2 border-transparent hover:text-primary whitespace-nowrap cursor-pointer" :class="activeTab === 'details' ? 'border-primary text-primary bg-white rounded-t-lg font-semibold' : 'text-gray-500'">Task Details</button>
          
          <!-- Unified Docket Ledger & Minutes Tab Button -->
          <button @click="activeTab = 'minute_sheet'" class="px-5 py-3 font-medium text-sm transition-all border-b-2 border-transparent hover:text-primary flex items-center gap-2 whitespace-nowrap cursor-pointer" :class="activeTab === 'minute_sheet' ? 'border-primary text-primary bg-white rounded-t-lg font-semibold' : 'text-gray-500'">
            <Icon icon="lucide:file-signature" width="16" />
            <span>Docket Ledger & Minutes</span>
            <span v-if="props.task?.minutes?.length" class="text-xs bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full font-mono font-bold">
              {{ props.task.minutes.length }}
            </span>
          </button>

          <!-- Discussions Tab Button -->
          <button v-if="(props.task?.assigned_to_me) || (props.task?.assignments && props.task?.assignments?.length > 0)" @click="activeTab = 'discussions'" class="px-5 py-3 font-medium text-sm transition-all border-b-2 border-transparent hover:text-primary flex items-center gap-2 whitespace-nowrap cursor-pointer" :class="activeTab === 'discussions' ? 'border-primary text-primary bg-white rounded-t-lg font-semibold' : 'text-gray-500'">
            <Icon icon="lucide:message-square" width="16" />
            Discussions
          </button>

          <button v-if="allTaskSubmissions.length > 0 || (props.task?.document_versions && props.task.document_versions.length > 0)" @click="activeTab = 'submission'" class="px-5 py-3 font-medium text-sm transition-all border-b-2 border-transparent hover:text-primary flex items-center gap-2 whitespace-nowrap cursor-pointer" :class="activeTab === 'submission' ? 'border-primary text-primary bg-white rounded-t-lg font-semibold' : 'text-gray-500'">
            <Icon icon="lucide:file-check" width="16" /> Submissions & Versions
          </button>
        </div>

        <div class="flex-1 min-h-0" :class="activeTab === 'discussions' ? 'flex flex-col h-full overflow-hidden' : 'overflow-y-auto custom-scrollbar'">
          <!-- Details Tab Content -->
          <div v-show="activeTab === 'details'" class="flex flex-col h-full">
          <Divider class="mt-0" />

          <section class="p-6 text-gray-600 max-h-[1000px]">
            <div class="space-y-6">
              <!-- Assigned To & Delegation Trail -->
              <div class="bg-gray-50/50 p-4 rounded-xl border border-gray-100 flex flex-col gap-3" v-if="branchType =='department' || branchType == 'division' || branchType =='unit' 
              || user_roles.includes('super user') || user_roles.includes('super admin') || user_roles.includes('admin')">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                  <div class="flex items-center gap-3 min-w-[120px]">
                    <div class="p-2 bg-blue-100 text-blue-600 rounded-lg shrink-0">
                      <Icon icon="lucide:user-check" width="18" />
                    </div>
                    <div>
                      <p class="font-medium text-gray-700 leading-tight">Assigned To</p>
                      <span class="text-[11px] text-gray-400 font-medium">Current Assignee</span>
                    </div>
                  </div>

                  <!-- Active Assignee Pill -->
                  <div v-if="currentAssignment" class="flex items-center gap-3 bg-white px-3.5 py-2 rounded-xl border border-gray-200 shadow-sm">
                    <img :src="getAssigneeAvatar(currentAssignment)" alt="" class="w-8 h-8 rounded-full object-cover border border-gray-100" />
                    <div>
                      <div class="flex items-center gap-2">
                        <p class="text-sm font-bold text-gray-800">{{ getAssigneeName(currentAssignment) }}</p>
                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-md tracking-wider"
                              :class="{
                                'bg-emerald-50 text-emerald-700 border border-emerald-200': currentAssignment.status === 'completed',
                                'bg-amber-50 text-amber-700 border border-amber-200': currentAssignment.status === 'under review' || currentAssignment.status === 'review',
                                'bg-blue-50 text-blue-700 border border-blue-200': currentAssignment.status === 'in progress' || currentAssignment.status === 'pending'
                              }">
                          {{ currentAssignment.status }}
                        </span>
                      </div>
                      <p class="text-xs text-gray-500 font-medium">{{ getAssigneeBranchOrRole(currentAssignment) }}</p>
                    </div>
                  </div>
                  <div v-else class="text-sm text-gray-400 italic">No active assignment</div>
                </div>

                <!-- Delegation Routing Trail -->
                <div v-if="routingTrail && routingTrail.length > 1" class="pt-2.5 border-t border-gray-200/70">
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider flex items-center gap-1.5">
                      <Icon icon="lucide:git-commit" width="14" class="text-blue-500" /> Delegation Trail ({{ routingTrail.length }} steps)
                    </span>
                    <button @click="showDelegationTrail = !showDelegationTrail" class="text-xs text-blue-600 hover:text-blue-700 font-medium flex items-center gap-1 cursor-pointer">
                      <span>{{ showDelegationTrail ? 'Hide Trail' : 'View Full Trail' }}</span>
                      <Icon :icon="showDelegationTrail ? 'lucide:chevron-up' : 'lucide:chevron-down'" width="14" />
                    </button>
                  </div>

                  <div v-show="showDelegationTrail" class="flex flex-wrap items-center gap-2 mt-1.5 bg-white/80 p-2.5 rounded-xl border border-gray-100">
                    <template v-for="(step, sIdx) in routingTrail" :key="step.id">
                      <div class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg border text-xs"
                           :class="sIdx === routingTrail.length - 1 
                             ? 'bg-blue-50/80 border-blue-200 text-blue-900 font-semibold shadow-xs' 
                             : 'bg-white border-gray-200 text-gray-700'">
                        <img :src="getAssigneeAvatar(step)" alt="" class="w-5 h-5 rounded-full object-cover" />
                        <div class="flex flex-col">
                          <span class="leading-tight">{{ getAssigneeName(step) }}</span>
                          <span class="text-[10px] text-gray-400 font-normal leading-tight">{{ getAssigneeBranchOrRole(step) }}</span>
                        </div>
                        <Icon v-if="step.status === 'completed'" icon="lucide:check-circle-2" class="text-emerald-500 ml-1 shrink-0" width="14" />
                        <Icon v-else-if="step.status === 'under review' || step.status === 'review'" icon="lucide:clock" class="text-amber-500 ml-1 shrink-0" width="14" />
                        <Icon v-else icon="lucide:circle-dot" class="text-blue-500 ml-1 shrink-0 animate-pulse" width="14" />
                      </div>
                      <Icon v-if="sIdx < routingTrail.length - 1" icon="lucide:arrow-right" class="text-gray-400 shrink-0" width="14" />
                    </template>
                  </div>
                </div>
              </div>

              <div class="flex items-center gap-14 bg-gray-50/50 py-2 px-4 rounded-xl border border-gray-100">
                <div class="flex items-center gap-3 min-w-[120px]">
                  <div class="p-2 bg-purple-100 text-purple-600 rounded-lg">
                    <Icon icon="lucide:calendar" width="18" />
                  </div>
                  <p class="font-medium text-gray-700">Due date</p>
                </div>
                <div>
                  <p class="text-sm font-semibold bg-white px-4 py-1.5 rounded-full border border-gray-200 shadow-sm">{{ props.task?.due_date }}</p>
                </div>
              </div>

              <div class="flex items-center gap-14 bg-gray-50/50 py-2 px-4 rounded-xl border border-gray-100">
                <div class="flex items-center gap-3 min-w-[120px]">
                  <div class="p-2 bg-orange-100 text-orange-500 rounded-lg">
                    <Icon icon="fluent:document-16-regular" width="18" />
                  </div>
                  <p class="font-medium text-gray-700">Priority</p>
                </div>
                <div class="flex items-center gap-3">
                  <div>
                    <p class="rounded-full font-bold bg-white shadow-sm border border-gray-200 py-1.5 px-5 uppercase text-xs tracking-wider" :style="{
          color:
            props.task?.priority === '1'
              ? '#F59E0B'
              : props.task?.priority === '3'
                ? '#EF4444'
                : props.task?.priority === '2'
                  ? '#F97316'
                  : 'black',
        }">{{ props.task?.priority == 1 ? 'Low' : props.task?.priority == 2 ? 'Medium' : 'High' }}</p>
                  </div>
                </div>
              </div>

              <!-- for super users -->
              <div class="flex items-center gap-14 bg-gray-50/50 py-2 px-4 rounded-xl border border-gray-100" v-if="user_roles.includes('super user') && !props.task?.assigned_to_me">
                <div class="flex items-center gap-3 min-w-[120px]">
                  <div class="p-2 bg-emerald-100 text-emerald-600 rounded-lg">
                    <Icon icon="lucide:activity" width="18" />
                  </div>
                  <p class="font-medium text-gray-700">Status</p>
                </div>

                <div class="flex items-center gap-3">
                  <div class="bg-white border border-gray-200 shadow-sm flex items-center gap-3 px-5 py-1.5 rounded-full">
                    <p class=" uppercase text-xs w-fit font-bold tracking-wider" :style="{
                        color:
                          props.task?.status === 'Completed'
                            ? '#10B981'
                            : props.task?.status === 'Under Review'
                              ? '#F59E0B'
                              : props.task?.status === 'In Progress'
                                ? '#3B82F6'
                                : props.task?.status === 'Uncompleted'
                                  ? '#EF4444'
                                  : '#EF4444',
                      }">
                      {{ props.task?.status }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- for other users -->
               <div class="flex items-center gap-14 bg-gray-50/50 py-2 px-4 rounded-xl border border-gray-100" v-if="props.task?.assigned_to_me">
                  <div class="flex items-center gap-3 min-w-[120px]">
                    <div class="p-2 bg-emerald-100 text-emerald-600 rounded-lg">
                      <Icon icon="lucide:activity" width="18" />
                    </div>
                    <p class="font-medium text-gray-700">Status</p>
                  </div>

                  <div class="flex items-center gap-3">
                    <div class="bg-white border border-gray-200 shadow-sm flex items-center gap-3 px-5 py-1.5 rounded-full">
                      <p class=" uppercase text-xs w-fit font-bold tracking-wider" :style="{
                          color:
                            props.task?.assigned_to_me?.status === 'completed'
                              ? '#10B981'
                              : props.task?.assigned_to_me?.status === 'revision'
                                ? '#F59E0B'
                                : props.task?.assigned_to_me?.status === 'pending'
                                  ? '#3B82F6'
                                  : props.task?.assigned_to_me?.status === 'uncompleted'
                                    ? '#EF4444'
                                    : '#EF4444',
                        }">
                        {{ props.task?.assigned_to_me?.status }}
                      </p>
                    </div>
                  </div>
                </div>

              <div class="bg-gray-50/50 py-2 px-4 rounded-xl border border-gray-100">
                <div class="flex items-center gap-3 mb-4">
                  <div class="p-2 bg-indigo-100 text-indigo-600 rounded-lg">
                    <Icon icon="lucide:align-left" width="18" />
                  </div>
                  <p class="font-medium text-gray-700">Task Description</p>
                </div>
                <p class="bg-white shadow-inner outline-none resize-none p-5 rounded-xl w-full text-gray-600 leading-relaxed border border-gray-100" 
                  v-if="props.task && props.task.assigned_to_me && props.task.assigned_to_me.description">
                  {{ (props.task && props.task.assigned_to_me)? props.task.assigned_to_me.description: '' }}
                </p>
                <p v-else class="bg-white shadow-inner outline-none resize-none p-5 rounded-xl w-full text-gray-600 leading-relaxed border border-gray-100">
                  {{ (props.task.description)? props.task.description: '' }}
                </p>
              </div>

              <div class="bg-gray-50/50 py-2 px-4 rounded-xl border border-gray-100">
                <div class="flex justify-between items-center mb-4 w-full">
                  <div class="flex items-center gap-3">
                    <div class="p-2 bg-rose-100 text-rose-600 rounded-lg">
                      <Icon icon="grommet-icons:attachment" width="18" />
                    </div>
                    <p class="font-medium text-gray-700">Attached Document</p>
                  </div>
                  
                  <div class="flex items-center gap-2">
                    <button 
                      type="button"
                      @click="generateDocModal = true" 
                      class="flex items-center gap-1.5 bg-blue-50 border border-blue-200 text-blue-700 shadow-2xs px-3 py-1.5 rounded-lg text-xs md:text-sm font-semibold hover:bg-blue-100 transition-colors cursor-pointer"
                      title="Generate official memo, letter, or transmittal from Document Templates"
                    >
                      <Icon icon="lucide:file-text" width="16" class="text-blue-600" />
                      <span>Use Template</span>
                    </button>

                    <button @click="openUploadModal = true" class="flex items-center gap-2 bg-white border border-gray-200 shadow-sm px-3 py-1.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                      <Icon icon="lucide:plus" width="16" />
                      Attach File
                    </button>
                  </div>


                </div>
                
                <div v-if="props.task && sortedTaskDocs && sortedTaskDocs.length === 0" class="text-sm text-gray-500 py-3 text-center border-dashed border-2 border-gray-200 rounded-lg bg-gray-50">
                  No documents attached yet.
                </div>

                <div v-for="(document, index) in sortedTaskDocs" :key="index"
                  :class="document.path === file_path.path ? 'border-blue-400 bg-blue-50/30' : 'border-gray-200 bg-white'"
                  class="border hover:border-blue-400 hover:shadow-md transition-all rounded-xl p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-3">
                  <div class="flex items-center gap-4 flex-1 min-w-0 pr-2">
                    <div class="bg-gray-50 shadow-inner w-12 h-12 flex items-center justify-center rounded-xl border border-gray-100 shrink-0">
                      <img :src="getFileExtension(document)" alt="" width="24">
                    </div>
                    <div class="flex-1 min-w-0">
                      <h1 class="font-semibold text-gray-800 truncate" :title="document.file_name">{{ document.file_name }}</h1>
                      <p class="text-sm text-gray-500 mt-1">{{ document.size }}</p>
                    </div>
                  </div>

                  <div class="flex items-center gap-2 bg-gray-50 rounded-lg p-1 border border-gray-100 shrink-0">
                    <button class="p-2 rounded-md hover:bg-white hover:shadow-sm text-gray-500 hover:text-blue-600 transition-all" @click="setFilePath(document)">
                      <Icon icon="ph:eye-light" width="20" />
                    </button>
                    <button class="p-2 rounded-md hover:bg-white hover:shadow-sm text-gray-500 hover:text-green-600 transition-all" @click="downloadFile(document.path)">
                      <Icon icon="material-symbols-light:download" width="20" />
                    </button>
                  </div>
                </div>
              </div>
            </div>
            

          </section>

          <!-- (Toggle Menus Removed in favor of Tabs) -->
          <div class="hidden">

            <!-- upline comment -->
            <div class="group flex w-full items-center p-4 bg-gray-50 hover:bg-blue-50 hover:border-blue-200 border border-transparent rounded-xl transition-all cursor-pointer shadow-sm hover:shadow-md"
              @click="toggleSidemenu('comment')" v-if="props.task.assigned_to_me &&  props.task.assigned_to_me.comments">
              <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-4">
                  <div class="p-2.5 bg-white shadow-sm text-blue-500 rounded-lg group-hover:scale-110 transition-transform">
                    <Icon icon="lucide:message-square" width="20" />
                  </div>
                  <p class="text-lg font-medium text-gray-700 group-hover:text-blue-700">Comments [{{ (props.task?.assigned_to_me?.assigned_by?.last_name || '') + ' ' + (props.task?.assigned_to_me?.assigned_by?.first_name || '') }}]</p>
                  <span class="px-3 py-1 bg-blue-100 text-blue-700 text-sm font-bold rounded-full">
                    {{ (props.task.assigned_to_me &&  props.task.assigned_to_me.comments)? props.task.assigned_to_me.comments.length: 0}}
                  </span>
                </div>
                <Icon icon="flowbite:angle-right-outline" width="20" class="text-gray-400 group-hover:text-blue-500 group-hover:translate-x-1 transition-all" />
              </div>
            </div>

            <!-- downline comment -->
            <div class="group flex w-full items-center p-4 bg-gray-50 hover:bg-indigo-50 hover:border-indigo-200 border border-transparent rounded-xl transition-all cursor-pointer shadow-sm hover:shadow-md"
              @click="toggleSidemenu('comment_down')" v-if="props.task.assignments && props.task.assignments.length > 0">
              <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-4">
                  <div class="p-2.5 bg-white shadow-sm text-indigo-500 rounded-lg group-hover:scale-110 transition-transform">
                    <Icon icon="lucide:message-square" width="20" />
                  </div>
                  <p class="text-lg font-medium text-gray-700 group-hover:text-indigo-700">Comments [{{ (props.task?.assignments?.[0]?.assigned_to?.last_name || '') + ' ' + (props.task?.assignments?.[0]?.assigned_to?.first_name || '') }}]</p>
                  <span class="px-3 py-1 bg-indigo-100 text-indigo-700 text-sm font-bold rounded-full">
                    {{ (props.task.assignments && props.task.assignments.length > 0 && props.task.assignments[0].comments)? props.task.assignments[0].comments.length: 0}}
                  </span>
                </div>
                <Icon icon="flowbite:angle-right-outline" width="20" class="text-gray-400 group-hover:text-indigo-500 group-hover:translate-x-1 transition-all" />
              </div>
            </div>

            <!-- Minute Sheet Button -->
            <div class="group flex w-full items-center p-4 bg-gray-50 hover:bg-yellow-50 hover:border-yellow-200 border border-transparent rounded-xl transition-all cursor-pointer shadow-sm hover:shadow-md"
              @click="toggleSidemenu('minute_sheet')">
              <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-4">
                  <div class="p-2.5 bg-white shadow-sm text-yellow-500 rounded-lg group-hover:scale-110 transition-transform">
                    <Icon icon="lucide:file-signature" width="20" />
                  </div>
                  <p class="text-lg font-medium text-gray-700 group-hover:text-yellow-700">Minute Sheet</p>
                </div>
                <Icon icon="flowbite:angle-right-outline" width="20" class="text-gray-400 group-hover:text-yellow-500 group-hover:translate-x-1 transition-all" />
              </div>
            </div>

            <div class="group flex w-full items-center p-4 bg-gray-50 hover:bg-emerald-50 hover:border-emerald-200 border border-transparent rounded-xl transition-all cursor-pointer shadow-sm hover:shadow-md"
              @click="toggleSidemenu('activity')">
              <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-4">
                  <div class="p-2.5 bg-white shadow-sm text-emerald-500 rounded-lg group-hover:scale-110 transition-transform">
                    <Icon icon="lucide:activity" width="20" />
                  </div>
                  <p class="text-lg font-medium text-gray-700 group-hover:text-emerald-700">Task Activity</p>
                </div>
                <Icon icon="flowbite:angle-right-outline" width="20" class="text-gray-400 group-hover:text-emerald-500 group-hover:translate-x-1 transition-all" />
              </div>
            </div>

            <div class="group flex w-full items-center p-4 bg-gray-50 hover:bg-rose-50 hover:border-rose-200 border border-transparent rounded-xl transition-all cursor-pointer shadow-sm hover:shadow-md"
              @click="toggleSidemenu('submission')" v-if="allTaskSubmissions.length > 0">
              <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-4">
                  <div class="p-2.5 bg-white shadow-sm text-rose-500 rounded-lg group-hover:scale-110 transition-transform">
                    <Icon icon="lucide:file-check" width="20" />
                  </div>
                  <p class="text-lg font-medium text-gray-700 group-hover:text-rose-700">Task Submissions</p>
                  <span class="w-6 h-6 flex items-center justify-center rounded-full bg-rose-500 text-white text-xs font-bold animate-pulse">!</span>
                </div>
                <Icon icon="flowbite:angle-right-outline" width="20" class="text-gray-400 group-hover:text-rose-500 group-hover:translate-x-1 transition-all" />
              </div>
            </div>

            <!-- my task submissions -->
             <div class="group flex w-full items-center p-4 bg-gray-50 hover:bg-teal-50 hover:border-teal-200 border border-transparent rounded-xl transition-all cursor-pointer shadow-sm hover:shadow-md"
              @click="toggleSidemenu('submitted')" v-if="props.task && props.task.assigned_to_me
               && props.task.assigned_to_me.task_submissions && props.task.assigned_to_me.task_submissions.length > 0">
              <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-4">
                  <div class="p-2.5 bg-white shadow-sm text-teal-500 rounded-lg group-hover:scale-110 transition-transform">
                    <Icon icon="lucide:files" width="20" />
                  </div>
                  <p class="text-lg font-medium text-gray-700 group-hover:text-teal-700">Submitted Files</p>
                </div>
                <Icon icon="flowbite:angle-right-outline" width="20" class="text-gray-400 group-hover:text-teal-500 group-hover:translate-x-1 transition-all" />
              </div>
            </div>
          </div>
        </div>

                <!-- /////   comments   ///// -->
        <div v-show="activeTab === 'discussions'" class="h-full flex-1 flex flex-col min-h-0 overflow-hidden">
          <TaskDiscussions :task="props.task" class="h-full flex-1 min-h-0" />
        </div>
        
        <!-- //////   Unified Docket Ledger & Minutes   ///// -->
        <div v-show="activeTab === 'minute_sheet' || activeTab === 'activity'" class="h-full flex-1 flex flex-col min-h-0 overflow-hidden">
          <DocketTimeline :task="props.task" class="h-full flex-1 min-h-0" />
        </div>

        <!-- //////   submissions   ///// -->
        <div v-show="activeTab === 'submission'">
          <div class="p-8">
            <div class="flex items-center p-2 rounded-md cursor-pointer w-fit bg-slate-50 gap-2"
              @click="toggleSidemenu('back')">
              <Icon icon="flowbite:angle-left-outline" />
              <p>Back</p>
            </div>
          </div>
          <Divider class="mt-0" />
          <div class="">

            <!-- Read-Only Banner in Submissions Tab when Task is Submitted -->
            <div v-if="isTaskSubmittedByMe" class="mx-5 mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-2xl flex items-center justify-between shadow-xs">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                  <Icon icon="lucide:clock" width="22" />
                </div>
                <div>
                  <h4 class="text-sm font-bold text-blue-900">Task Submitted & Under Review</h4>
                  <p class="text-xs text-blue-700 mt-0.5">
                    Your submission has been forwarded to 
                    <span class="font-bold text-blue-900">{{ currentTaskHolder?.first_name }} {{ currentTaskHolder?.last_name || 'Next Approver' }}</span>.
                    Action controls are locked until returned.
                  </p>
                </div>
              </div>
              <span class="text-[11px] font-bold px-3 py-1 rounded-full bg-blue-100 text-blue-800 border border-blue-200 uppercase tracking-wide">
                Locked
              </span>
            </div>

            <!-- Supporting Attachments & Memos Card for Reviewing Officers (AD, DD, Director, AGF) -->
            <div v-if="canAttachSupportingDocuments" class="mx-5 mb-6 p-5 bg-gradient-to-br from-white to-slate-50/80 rounded-2xl border border-gray-200/90 shadow-xs">
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center shadow-xs shrink-0">
                    <Icon icon="lucide:paperclip" width="20" />
                  </div>
                  <div>
                    <div class="flex items-center gap-2">
                      <h4 class="font-bold text-sm text-gray-800">Supporting Attachments & Memos</h4>
                      <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-blue-50 text-blue-700 border border-blue-100">
                        {{ taskAttachedDocs.length }} File{{ taskAttachedDocs.length === 1 ? '' : 's' }}
                      </span>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">Attach supporting schedules, memos, or clearance letters for this review stage</p>
                  </div>
                </div>

                <!-- Upload Button -->
                <label class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs transition-all shadow-xs flex items-center gap-2 cursor-pointer shrink-0">
                  <Icon v-if="isUploadingSupportingDoc" icon="lucide:loader-2" class="animate-spin" width="15" />
                  <Icon v-else icon="lucide:cloud-upload" width="15" />
                  <span>{{ isUploadingSupportingDoc ? 'Uploading...' : 'Attach Documents' }}</span>
                  <input type="file" multiple @change="handleSupportingFileUpload" class="hidden" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg" :disabled="isUploadingSupportingDoc" />
                </label>
              </div>

              <!-- Attached Files List -->
              <div class="mt-4 space-y-2.5">
                <div v-if="taskAttachedDocs.length === 0" class="text-center py-6 border border-dashed border-gray-200 rounded-xl bg-gray-50/50 text-gray-400">
                  <Icon icon="lucide:folder-open" width="28" class="mx-auto mb-1 opacity-40" />
                  <p class="text-xs font-medium">No supporting files attached yet.</p>
                  <p class="text-[11px] text-gray-400">Click "Attach Documents" to upload PDFs, memos, or schedules.</p>
                </div>

                <div 
                  v-for="doc in taskAttachedDocs" 
                  :key="doc.id" 
                  class="bg-white rounded-xl p-3 border border-gray-200/80 hover:border-blue-300 transition-all flex items-center justify-between gap-3 shadow-2xs group"
                >
                  <div class="flex items-center gap-3 min-w-0 flex-1">
                    <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                      <Icon :icon="getFileIcon(doc.file_name || doc.path)" width="18" />
                    </div>
                    <div class="min-w-0 flex-1">
                      <p class="text-xs font-semibold text-gray-800 truncate" :title="doc.file_name || doc.path">
                        {{ doc.file_name || 'Document Attachment' }}
                      </p>
                      <p class="text-[10px] text-gray-400 mt-0.5">
                        {{ doc.size || 'Attachment' }} • {{ formatDate(doc.created_at) }}
                      </p>
                    </div>
                  </div>

                  <div class="flex items-center gap-1.5 shrink-0">
                    <button 
                      type="button" 
                      @click="setFilePath(doc)" 
                      class="p-1.5 rounded-lg border border-gray-200 text-gray-600 hover:text-blue-600 hover:bg-blue-50 transition-colors cursor-pointer"
                      title="Preview in Viewer"
                    >
                      <Icon icon="lucide:eye" width="14" />
                    </button>
                    <button 
                      type="button" 
                      @click="downloadFile(doc.path)" 
                      class="p-1.5 rounded-lg border border-gray-200 text-gray-600 hover:text-blue-600 hover:bg-blue-50 transition-colors cursor-pointer"
                      title="Download File"
                    >
                      <Icon icon="lucide:download" width="14" />
                    </button>
                    <button 
                      type="button" 
                      @click="deleteSupportingDoc(doc)" 
                      class="p-1.5 rounded-lg border border-red-100 text-red-500 hover:text-red-700 hover:bg-red-50 transition-colors cursor-pointer"
                      title="Remove File"
                    >
                      <Icon icon="lucide:trash-2" width="14" />
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Read-only files display when user cannot attach/has already submitted -->
            <div v-else-if="taskAttachedDocs.length > 0" class="mx-5 mb-6 p-5 bg-white rounded-2xl border border-gray-200/90 shadow-xs">
              <div class="flex items-center gap-2 pb-3 border-b border-gray-100">
                <Icon icon="lucide:paperclip" width="16" class="text-blue-600" />
                <h4 class="font-bold text-sm text-gray-800">Supporting Attachments & Memos</h4>
                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-blue-50 text-blue-700 border border-blue-100">
                  {{ taskAttachedDocs.length }}
                </span>
              </div>
              <div class="mt-3 space-y-2">
                <div 
                  v-for="doc in taskAttachedDocs" 
                  :key="doc.id" 
                  class="bg-slate-50/70 rounded-xl p-3 border border-gray-200/70 flex items-center justify-between gap-3"
                >
                  <div class="flex items-center gap-3 min-w-0 flex-1">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                      <Icon :icon="getFileIcon(doc.file_name || doc.path)" width="16" />
                    </div>
                    <div class="min-w-0 flex-1">
                      <p class="text-xs font-semibold text-gray-800 truncate" :title="doc.file_name || doc.path">
                        {{ doc.file_name || 'Document Attachment' }}
                      </p>
                      <p class="text-[10px] text-gray-400 mt-0.5">
                        {{ doc.size || 'Attachment' }} • {{ formatDate(doc.created_at) }}
                      </p>
                    </div>
                  </div>
                  <div class="flex items-center gap-1.5 shrink-0">
                    <button type="button" @click="setFilePath(doc)" class="p-1.5 rounded-lg border border-gray-200 text-gray-600 hover:text-blue-600 hover:bg-blue-50 transition-colors cursor-pointer" title="Preview">
                      <Icon icon="lucide:eye" width="14" />
                    </button>
                    <button type="button" @click="downloadFile(doc.path)" class="p-1.5 rounded-lg border border-gray-200 text-gray-600 hover:text-blue-600 hover:bg-blue-50 transition-colors cursor-pointer" title="Download">
                      <Icon icon="lucide:download" width="14" />
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div class="px-5 mb-6">
              <VersionHistory :task="props.task" :activePath="activePreviewDoc?.path" @selectVersion="onSelectVersion" @refresh="reloadTask" />
            </div>

            <div v-if="allTaskSubmissions.length > 0" class="px-5 mt-6 pt-6 border-t border-gray-200 space-y-6">
              <h3 class="font-bold text-gray-700 text-sm flex items-center gap-2 mb-2">
                <Icon icon="lucide:archive" width="16" class="text-gray-400" />
                <span>Historical Submission Records (Legacy)</span>
              </h3>
              <div v-for="(document, index) in allTaskSubmissions" :key="index" class="p-5 border border-gray-200 rounded-2xl bg-white shadow-xs">
                <div class="flex items-center justify-between pb-3 mb-3 border-b border-gray-100">
                  <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <span class="font-semibold text-gray-800 text-sm">
                      Submission by: {{ document.user ? document.user.first_name + ' ' + document.user.last_name : 'Staff Member' }}
                    </span>
                    <span v-if="document.user?.department?.title" class="text-xs text-gray-400">
                      ({{ document.user.department.title }})
                    </span>
                  </div>
                  <span class="text-xs text-gray-400 font-mono" v-if="document.created_at">{{ formatDate(document.created_at) }}</span>
                </div>

                <div class="mt-2" v-if="document.task_submission_files && document.task_submission_files.length > 0">
                  <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Attached Files</p>
                  <div class="bg-slate-50 p-3 rounded-xl border border-gray-100">
                    <div
                      class="border bg-white border-gray-200 hover:border-primary rounded-xl p-3 flex items-center justify-between mb-2 last:mb-0 shadow-2xs"
                      v-for="doc in document.task_submission_files" :key="doc.id">
                      <div class="flex items-center gap-3 flex-1 min-w-0 pr-2">
                        <div class="bg-blue-50 w-fit p-2 rounded-lg shrink-0 text-blue-600">
                          <Icon icon="lucide:file-text" width="20" />
                        </div>
                        <div class="flex-1 min-w-0">
                          <h1 class="truncate font-medium text-sm text-gray-800" :title="doc.name">{{ doc.name }}</h1>
                          <p class="text-xs text-gray-400">{{ doc.size }}</p>
                        </div>
                      </div>

                      <div class="flex items-center gap-2 shrink-0">
                        <Icon icon="ph:eye-light" width="20" class="cursor-pointer text-gray-500 hover:text-primary transition-colors" @click="setFilePath(doc)" />
                        <Icon icon="material-symbols-light:download" width="20" class="cursor-pointer text-gray-500 hover:text-primary transition-colors" @click="downloadFile(doc.path)" />
                      </div>
                    </div>
                  </div>
                </div>

                <div class="mt-4" v-if="document.description">
                  <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">
                    <Icon icon="lucide:file-text" width="14" />
                    <span>Closing Remarks</span>
                  </div>
                  <p class="bg-slate-50 border border-gray-100 p-3.5 rounded-xl text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">
                    {{ document.description }}
                  </p>
                </div>
              </div>

              <!-- Action buttons for supervisor reviewing subordinate submission -->
              <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-end gap-3 flex-wrap" v-if="(is_subordinate_under_review || isSubmissionApproved) && !isTaskSubmittedByMe">
                <!-- Side-by-Side Diff Comparison Button -->
                <button 
                  type="button"
                  @click="diffViewerModal = true" 
                  class="font-semibold text-xs px-4 py-2.5 border border-blue-200 rounded-xl transition-all flex items-center gap-1.5 shadow-2xs text-blue-700 bg-blue-50 hover:bg-blue-100 hover:scale-105 active:scale-95 cursor-pointer"
                  title="Compare subordinate submission side-by-side with original document"
                >
                  <Icon icon="lucide:columns-2" width="16" />
                  <span>Compare Side-by-Side</span>
                </button>

                <!-- Return with Notes -->
                <button 
                  :disabled="isSubmissionApproved"
                  :title="isSubmissionApproved ? 'Task submission has already been approved' : 'Return task with revision directives'"
                  :class="[
                    'font-semibold text-xs px-4 py-2.5 border rounded-xl transition-all flex items-center gap-1.5 shadow-2xs',
                    isSubmissionApproved
                      ? 'border-gray-200 bg-gray-100 text-gray-400 cursor-not-allowed opacity-60 shadow-none'
                      : 'text-rose-700 bg-rose-50 hover:bg-rose-100 border-rose-200 hover:scale-105 active:scale-95 cursor-pointer'
                  ]"
                  @click="isSubmissionApproved ? null : handleOpenReturn">
                  <Icon icon="lucide:corner-up-left" width="16" />
                  <span>Return with Notes</span>
                </button>

                <!-- Approve (separate button) -->
                <button 
                  :disabled="isSubmissionApproved"
                  @click="isSubmissionApproved ? null : (openApproveOnlyModal = true)" 
                  :class="[
                    'border font-bold text-xs transition-all shadow-2xs rounded-xl px-4 py-2.5 flex items-center gap-1.5',
                    isSubmissionApproved 
                      ? 'border-gray-200 bg-gray-100 text-gray-400 cursor-not-allowed opacity-60 shadow-none' 
                      : 'border-emerald-300 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 hover:scale-105 active:scale-95 cursor-pointer'
                  ]"
                  :title="isSubmissionApproved ? 'Submission has already been approved' : 'Approve subordinate submission so you can reassign to another user'"
                >
                  <Icon :icon="isSubmissionApproved ? 'lucide:check-check' : 'mdi:check-decagram'" width="16" :class="isSubmissionApproved ? 'text-gray-400' : 'text-emerald-600'" />
                  <span>{{ isSubmissionApproved ? 'Approved' : 'Approve' }}</span>
                </button>

                <!-- Submit to Assigner / Final Approval -->
                <button 
                  :disabled="!isSubmissionApproved"
                  :title="!isSubmissionApproved ? 'Task must be approved before submitting' : ''"
                  :class="[
                    'transition-all rounded-xl px-5 py-2.5 flex items-center gap-2 text-xs font-bold border-0',
                    !isSubmissionApproved 
                      ? 'bg-gray-200 text-gray-400 cursor-not-allowed opacity-60 shadow-none' 
                      : 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white hover:from-blue-700 hover:to-indigo-700 hover:scale-105 active:scale-95 cursor-pointer shadow-md'
                  ]"
                  @click="isSubmissionApproved ? (openApproveModal = true) : null">
                  <Icon icon="lucide:check-circle-2" width="16" />
                  <span>{{ approveButtonLabel }}</span>
                </button>
              </div>
            </div>
            <div v-else class="text-center py-12 text-gray-400">
              <Icon icon="lucide:file-question" width="40" class="mx-auto mb-2 text-gray-300" />
              <p class="text-sm font-medium">No task submissions uploaded yet.</p>
            </div>
          </div>
        </div>

        <!-- //////   my submissions   ///// -->
          <div v-show="activeTab === 'submitted'">
          <div class="p-8">
            <div class="flex items-center p-2 rounded-md cursor-pointer w-fit bg-slate-50 gap-2"
              @click="toggleSidemenu('back')">
              <Icon icon="flowbite:angle-left-outline" />
              <p>Back</p>
            </div>
          </div>
          <Divider class="mt-0" />
          <div class="">

            <h1 class="px-5 font-semibold text-gray-800 text-3xl">
              My Submitted Task
            </h1>

            <div v-if="props.task && props.task.assigned_to_me
             && props.task.assigned_to_me.task_submissions && props.task.assigned_to_me.task_submissions.length > 0">
              <div v-for="(document, index) in props.task.assigned_to_me.task_submissions" :key="index">
                <div class="px-5 mt-3" v-if="document.task_submission_files">
                  <div class="bg-slate-50 p-3 rounded-md">
                    <div
                      class="border bg-white border-gray-300 hover:border-primary rounded-md p-4 flex items-center justify-between mb-5"
                      v-for="doc in document.task_submission_files">
                      <div class="flex items-center gap-3 flex-1 min-w-0 pr-2">
                        <div class="bg-[#EBF9FF] w-fit p-2 rounded-full shrink-0">
                          <img :src="getFileExtension(doc)" width="25" alt="">
                          <!-- <Icon :icon="document.icon" width="25" /> -->
                        </div>
                        <div class="flex-1 min-w-0">
                          <h1 class="truncate" :title="doc.name">{{ doc.name }}</h1>
                          <p class="text-sm text-gray-400">{{ doc.size }}</p>
                        </div>
                      </div>

                      <div class="flex items-center gap-3 shrink-0">
                        <Icon icon="ph:eye-light" width="25" class="cursor-pointer" @click="setFilePath(doc)" />
                        <Icon icon="material-symbols-light:download" width="25" class="cursor-pointer" @click="downloadFile(doc.path)" />
                      </div>
                    </div>
                  </div>

                </div>

                <div class="mt-7 px-5">
                  <div class="flex items-center gap-3">
                    <Icon icon="lucide:file-text" />
                    <p>Task Description</p>
                  </div>

                  <p class="bg-slate-50 outline-none mt-5 resize-none p-5 rounded-md w-full">
                    {{ document.description }}
                  </p>
                </div>
                 <Divider class="my-2" />
              </div>
              
            </div>
          </div>
        </div>

      </div> <!-- end of flex-1 overflow-y-auto -->
      </div> <!-- end of bg-white/90 hidden lg:flex -->
    </section>
  </main>
  <MarkComplete v-model="completeModal" :task="props.task" :assignedToMe="props.task.assigned_to_me" :modifiedPdfBlob="modifiedPdfBlob" :activeDocPath="currentActiveDocPath" @refresh="refreshHandler" />
  <ApproveReviewModal 
    v-if="subordinate_assignment_under_review" 
    v-model="openApproveModal" 
    :task="props.task" 
    :assignment="subordinate_assignment_under_review" 
    @confirmed="handleApproveConfirmed" 
  />
  <!-- SEPARATE APPROVE TASK MODAL (APPROVES WITHOUT FORWARDING) -->
  <Dialog v-model:visible="openApproveOnlyModal" :closable="false" modal :pt="{
    root: { class: 'bg-white rounded-3xl shadow-2xl max-h-[92vh] flex flex-col w-[95vw] md:w-[620px] border border-gray-100 overflow-hidden' },
    content: { class: 'p-0 flex flex-col overflow-hidden h-full flex-1' },
    header: { class: 'hidden' },
    footer: { class: 'hidden' }
  }">
    <div class="bg-gradient-to-r from-emerald-600 to-teal-700 px-6 py-5 flex items-center justify-between text-white shrink-0">
      <div class="flex items-center gap-3">
        <div class="w-11 h-11 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center border border-white/30">
          <Icon icon="mdi:check-decagram" width="24" class="text-white" />
        </div>
        <div>
          <h3 class="text-lg font-bold text-white">Approve Task</h3>
          <p class="text-emerald-100 text-xs">Approve submission and keep task for reassignment</p>
        </div>
      </div>
      <button @click="openApproveOnlyModal = false" class="w-8 h-8 flex items-center justify-center rounded-xl bg-white/10 hover:bg-white/20 text-white cursor-pointer">
        <Icon icon="lucide:x" width="18" />
      </button>
    </div>

    <div class="p-6 md:p-7 flex flex-col gap-5 overflow-y-auto max-h-[calc(92vh-160px)] custom-scrollbar bg-slate-50/50">
      <div class="bg-emerald-50 border border-emerald-200/80 rounded-2xl p-4 text-emerald-900 text-xs leading-relaxed space-y-1">
        <p class="font-bold flex items-center gap-1.5 text-sm text-emerald-800">
          <Icon icon="lucide:info" width="16" />
          Approval without immediate forwarding
        </p>
        <p class="text-emerald-700">
          Approving this submission marks your subordinate's work completed. The task will stay in your workspace so you can <strong>reassign it to another user</strong> or review further before submitting to your assigner.
        </p>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-2">
        <label class="text-xs font-bold uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
          <Icon icon="lucide:message-square" width="14" class="text-emerald-600" />
          Approval Remark (Optional)
        </label>
        <textarea 
          v-model="approveOnlyRemark" 
          rows="3" 
          class="w-full text-sm border border-slate-200 rounded-xl p-3.5 outline-none focus:border-emerald-500 focus:ring-3 focus:ring-emerald-500/15 text-slate-800 resize-none"
          placeholder="Subordinate submission approved and verified."
        ></textarea>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-2">
        <label class="text-xs font-bold uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
          <Icon icon="lucide:key" width="14" class="text-amber-600" />
          Digital PIN (Optional verification)
        </label>
        <input 
          type="password" 
          v-model="approveOnlyPin" 
          placeholder="••••" 
          autocomplete="off"
          @keyup.enter="executeApproveOnly"
          class="w-full text-center tracking-widest text-base font-bold bg-slate-50 border border-slate-200 focus:border-emerald-500 rounded-xl py-2 px-4 outline-none text-slate-800"
        />
      </div>
    </div>

    <div class="px-6 py-4 bg-white border-t border-slate-100 flex items-center justify-end gap-3 shrink-0">
      <button @click="openApproveOnlyModal = false" class="px-4 py-2.5 rounded-xl font-medium text-xs text-slate-600 bg-slate-100 hover:bg-slate-200 cursor-pointer">
        Cancel
      </button>
      <button 
        @click="executeApproveOnly" 
        :disabled="isProcessingApproveOnly"
        class="px-6 py-2.5 rounded-xl font-bold text-xs text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-md hover:shadow-lg disabled:opacity-50 flex items-center gap-2 cursor-pointer"
      >
        <Icon v-if="isProcessingApproveOnly" icon="lucide:loader-2" class="animate-spin" width="16" />
        <Icon v-else icon="mdi:check-decagram" width="16" />
        <span>{{ isProcessingApproveOnly ? 'Approving...' : 'Approve' }}</span>
      </button>
    </div>
  </Dialog>
  <AgfRerouteModal 
    v-if="subordinate_assignment_under_review" 
    v-model="openAgfRerouteModal" 
    :task="props.task" 
    :assignment="subordinate_assignment_under_review" 
    @confirmed="handleAgfRerouteConfirmed" 
  />
  <AssignExistingTask v-if="Re_Assign_Modal" v-model="Re_Assign_Modal" :task="props.task" @assigned="handleTaskAssigned" />
  <ReturnTask v-model="returnTaskModal" :task="props.task" :modifiedPdfBlob="modifiedPdfBlob" @refresh="refreshHandler" />
  <RequestInfoModal v-model="requestInfoModal" :task="props.task" @refresh="refreshHandler" />
  <ProvideInfoModal v-model="provideInfoModal" :requestData="selectedInfoRequest" @refresh="refreshHandler" />
  <RequestRevision v-model="RequestRevisionModal" :task="props.task" />
  <DeleteRevision v-model="deleteRevisionModal" />
  <Ratings v-model="rating" @taskReload="ReloadTask" :task="props.task" />

  <EditTask v-model="editModal" :task="props.task" @refresh="refreshHandler" />

  <Reopen v-model="reOpenModal" :task="props.task"/>
  <AppendSignatureModal v-model="appendSignatureModal" :taskId="props.task?.id" @refresh="refreshHandler" />
  <TaskUploadDocument v-model="openUploadModal" :task="props.task" @update="router.reload()" />
  <Sidebar v-model:visible="subNavVisible" header=" Left Sidebar" position="right"
    :style="{ width: '32rem', padding: '10px 0px' }">
    <div class="bg-white hide_scroll sticky top-0 h-screen pr-5 overflow-y-scroll">
      <div v-if="!sidemenu">
        <h1 class="p-8 font-semibold text-gray-800 text-3xl">
          Task Details
        </h1>
        <Divider class="mt-0" />

        <section class="p-5 text-gray-600 max-h-[1000px]">
          <div>
            <div class="flex items-center gap-14">
              <div class="flex items-center gap-3">
                <Icon icon="lucide:users" />
                <p>Assign To</p>
              </div>
              <div v-if="props.task.user && props.task.user.length > 0">
                <div class="flex items-center gap-2 h-fit bg-[#f8f8f9] p-2 py-1 rounded-3xl border-gray-300"
                  v-for="user in props.task.user">
                  <img :src="page.props.base + '/' + user.profile_image" alt="" class="w-7" />
                  <p class="text-base w-fit">{{ user.first_name + " " + user.last_name }}</p>
                </div>
              </div>
            </div>

            <div class="flex items-center gap-14 mt-5">
              <div class="flex items-center gap-3">
                <Icon icon="fluent:document-16-regular" />
                <p>Document Type</p>
              </div>
              <div class="flex items-center gap-3">
                <div v-for="(item, index) in data.document_type" :key="index">
                  <p class="rounded-2xl font-semibold bg-slate-50 py-1 px-4">{{ item }}</p>
                </div>
              </div>
            </div>
            <div class="flex items-center gap-14 mt-5">
              <div class="flex items-center gap-3">
                <Icon icon="lucide:calendar" />
                <p>Due date</p>
              </div>
              <div>
                <p class="text-base w-fit">2023-05-15</p>
              </div>
            </div>

            <div class="flex items-center gap-24 mt-5">
              <div class="flex items-center gap-3">
                <Icon icon="lucide:calendar" />
                <p>Tags</p>
              </div>

              <div class="flex items-center gap-3">
                <div class="bg-[#FEFBF6] flex items-center gap-3 px-5 py-1.5 rounded-3xl cursor-pointer">
                  <p class="text-xs w-fit text-[#F67E5A]">Medium</p>
                </div>

                <div class="bg-[#FEFBF6] flex items-center gap-3 px-5 py-1.5 rounded-3xl cursor-pointer">
                  <p class="text-xs w-fit text-[#F67E5A]">In Progress</p>
                </div>
              </div>
            </div>

            <div class="mt-5">
              <div class="flex items-center gap-3">
                <Icon icon="lucide:file-text" />
                <p>Task Description</p>
              </div>

              <p class="bg-slate-50 outline-none mt-5 resize-none p-5 rounded-md w-full">
                {{ data.desc }}
              </p>
            </div>

            <div class="mt-5" v-if="props.task">
              <div class="flex mb-5 items-center justify-between gap-3 flex-wrap">
                <div class="flex items-center gap-3">
                  <Icon icon="grommet-icons:attachment" width="20" />
                  <p>Attached Document</p>
                </div>
                <div class="flex items-center gap-2">
                  <button @click="openUploadModal = true" class="text-xs bg-white border border-gray-300 text-gray-700 px-3 py-1.5 rounded-md flex items-center gap-2 shadow-sm hover:bg-gray-50">
                    <Icon icon="lucide:plus" /> Attach File
                  </button>
                  <button @click="appendSignatureModal = true" class="text-xs bg-primary text-white px-3 py-1.5 rounded-md flex items-center gap-2">
                    <Icon icon="lucide:pen-tool" /> Append Signature
                  </button>
                  <button @click="generateDocModal = true" class="text-xs bg-primary text-white px-3 py-1.5 rounded-md flex items-center gap-2">
                    <Icon icon="pi:file-pdf" /> Generate
                  </button>
                </div>
              </div>
              <div v-if="sortedTaskDocs && sortedTaskDocs.length > 0">
              <div v-for="(document, index) in sortedTaskDocs" :key="index"
                class="border border-gray-300 hover:border-primary rounded-md p-4 flex items-center justify-between mb-5">
                <div>
                  <div class="flex items-center gap-3 flex-1 min-w-0 pr-2">
                    <div class="bg-[#EBF9FF] w-fit p-2 rounded-full shrink-0">
                      <Icon :icon="document.icon" width="25" />
                    </div>
                    <div class="flex-1 min-w-0">
                      <h1 class="truncate" :title="document.name">{{ document.name }}</h1>
                      <p class="text-sm text-gray-400">{{ document.size }}</p>
                    </div>
                  </div>

                  <div class="flex items-center gap-3 shrink-0">
                    <Icon icon="ph:eye-light" width="25" @click="setFilePath(document)" />
                    <Icon icon="material-symbols-light:download" @click="downloadFile(document.path)" width="25" />
                  </div>
                </div>
              </div>
              </div>
            </div>
          </div>
        </section>
        <Divider class="mt-0" />

        <div class="px-5 flex w-full items-center pb-5 justify-between cursor-pointer"
          @click="toggleSidemenu('comment')">
          <div class="flex items-center gap-5">
            <Icon icon="lucide:message-square" width="25" />
            <p class="text-xl">Comments</p>
            <p
              class="w-7 h-7 rounded-md text-gray-800 font-semibold bg-primary/10 flex items-center justify-center flex-col p-3">
              3
            </p>
          </div>
          <Icon icon="flowbite:angle-right-outline" width="25" />
        </div>
        <Divider class="mt-0" />

        <div class="px-5 flex w-full items-center pb-5 justify-between cursor-pointer"
          @click="toggleSidemenu('submission')">
          <div class="flex items-center gap-5">
            <Icon icon="lucide:activity" width="25" />
            <p class="text-xl">Task History</p>
            <p
              class="w-7 h-7 rounded-md text-red-500 font-semibold bg-red-500/10 flex items-center justify-center flex-col p-3">
              !
            </p>
          </div>
          <Icon icon="flowbite:angle-right-outline" width="25" />
        </div>
        <Divider class="mt-0" />
      </div>

      <!-- /////   comments   ///// -->

      <div v-show="activeTab === 'comment'">
        <div class="p-8">
          <div class="flex items-center p-2 rounded-md cursor-pointer w-fit bg-slate-50 gap-2"
            @click="toggleSidemenu('back')">
            <Icon icon="flowbite:angle-left-outline" />
            <p>Back</p>
          </div>
        </div>
        <Divider class="mt-0" />
        <div class="">
          <h1 class="px-5 font-semibold text-gray-800 text-3xl">Comments</h1>

          <div class="mx-5 bg-slate-50 p-5 rounded-lg mt-5">
            <textarea name="" id="" cols="30" placeholder="Enter Message" rows="5"
              class="bg-slate-50 outline-none resize-none"></textarea>
            <div class="w-full flex items-center justify-end">
              <button class="bg-white px-3 py-1.5 border border-gray-300 rounded-md">
                Send
              </button>
            </div>
          </div>
          <div class="mt-5 px-5">
            <div v-for="(item, index) in comments" :key="index">
              <Divider class="mt-0" />
              <div class="flex items-start w-full gap-4">
                <div class="w-[15%]">
                  <div class="w-14 h-14 rounded-full overflow-hidden">
                    <img :src="item.img" alt="" class="w-full h-full" />
                  </div>
                </div>
                <div class="w-[80%]">
                  <div class="flex items-center gap-2">
                    <h1 class="text-gray-800 text-xl">{{ item.name }}</h1>
                    <small class="text-gray-500">• {{ item.time }}</small>
                  </div>
                  <p class="text-base text-gray-500 my-4">
                    {{ item.comment }}
                  </p>

                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- //////   Activities   ///// -->
      <!-- <div v-show="activeTab === 'activity'">
        <div class="p-8">
          <div class="flex items-center p-2 rounded-md cursor-pointer w-fit bg-slate-50 gap-2"
            @click="toggleSidemenu('back')">
            <Icon icon="flowbite:angle-left-outline" />
            <p>Back</p>
          </div>
        </div>
        <Divider class="mt-0" />
        <div class="">

          <h1 class="px-5 font-semibold text-gray-800 text-3xl">
            Task Activity
          </h1>

          <div class="p-5 mx-7 bg-slate-50 rounded-lg mt-5">
            <div v-for="(step, index) in TaskAction" :key="index">
              <div class="flex items-start gap-5">
                <div class="flex flex-col items-center">

                  <Icon icon="akar-icons:circle-check-fill" width="25" class=" text-primary" />

                  <div class="w-[2px] my-1 h-10 bg-[#EBF3FE]" v-if="index < 4"></div>
                </div>
                <div class="text-[#475467]">
                  <h1 class="font-medium text-lg">{{ step.progress }}:</h1>
                  <div class=" flex items-center  text-sm gap-1">
                    <p class="opacity-70">{{ step.to }}</p>
                    <p class=" opacity-100">•</p>
                    <p class="opacity-70">{{ step.date }}</p>
                    <p class=" opacity-100">•</p>
                    <p class="opacity-70">{{ step.time }}</p>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div> -->

      <!-- //////   submissions   ///// -->
      <div v-show="activeTab === 'submission'">
        <div class="p-8">
          <div class="flex items-center p-2 rounded-md cursor-pointer w-fit bg-slate-50 gap-2"
            @click="toggleSidemenu('back')">
            <Icon icon="flowbite:angle-left-outline" />
            <p>Back</p>
          </div>
        </div>
        <Divider class="mt-0" />
        <div class="">
          <h1 class="px-5 font-semibold text-gray-800 text-3xl">
            Task Submissions
          </h1>

          <div class="px-5 mt-3">
            <div class="bg-slate-50 p-3 rounded-md">
              <div v-for="(document, index) in documents" :key="index"
                class="border bg-white border-gray-300 hover:border-primary rounded-md p-4 flex items-center justify-between mb-5">
                <div class="flex items-center gap-3">
                  <div class="bg-[#EBF9FF] w-fit p-2 rounded-full">
                    <Icon :icon="document.icon" width="25" />
                  </div>
                  <div>
                    <h1>{{ document.name }}</h1>
                    <p class="text-sm text-gray-400">{{ document.size }}</p>
                  </div>
                </div>

                <div class="flex items-center gap-3">
                  <Icon icon="ph:eye-light" width="25" class="cursor-pointer"  @click="setFilePath(document)" />
                  <Icon icon="material-symbols-light:download" class="cursor-pointer" width="25" @click="downloadFile(document.path)" />
                </div>
              </div>
            </div>
          </div>

          <div class="mt-7 px-5">
            <div class="flex items-center gap-3">
              <Icon icon="lucide:file-text" />
              <p>Task Description</p>
            </div>

            <p class="bg-slate-50 outline-none mt-5 resize-none p-5 rounded-md w-full">
              {{ data.desc }}
            </p>
          </div>

          <div class="px-5 mt-5 w-full gap-5 flex items-center justify-end">
            <button class="text-red-600" @click="RequestRevisionModal = true">
              Request Revision
            </button>

            <button class="font-semibold text-gray-800 px-3 py-2 border rounded-md flex items-center gap-2" @click="handleOpenSubmit" :disabled="isGeneratingSubmit">
              <Icon v-if="isGeneratingSubmit" icon="lucide:loader-2" class="animate-spin" width="16" />
              {{ isGeneratingSubmit ? 'Preparing...' : 'Mark as complete' }}
            </button>
          </div>
        </div>
      </div>
    </div>

  </Sidebar>

  <!-- Sign & Forward Sticky Action Bar -->
  <SignForwardBar :task="props.task" :pdfEditorRef="pdfEditorRef" :isSubordinateUnderReview="is_subordinate_under_review" :isSubmissionApproved="isSubmissionApproved" :isTaskSubmitted="isTaskSubmittedByMe" @openAgfReroute="openAgfRerouteModal = true" @actionCompleted="reloadTask" @refresh="reloadTask" />

  <!-- Submit For Review Modal (Streamlined) -->
  <SubmitForReview v-model="submitForReviewModal" :task="props.task" @submitted="reloadTask" />

  <!-- Side-by-Side Document Diff Modal -->
  <DocumentDiffViewer v-model="diffViewerModal" :task="props.task" />

  <!-- Generate Document from Template Modal -->
  <GenerateDocumentModal v-model="generateDocModal" :taskId="props.task?.id" @generated="handleDocumentGenerated" />
</template>


<script setup>
import { ref, defineProps, computed, onMounted, inject, watch } from "vue";
import Dialog from "primevue/dialog";
import axios from "axios";
import Swal from "sweetalert2";
import TaskDiscussions from "./Components/TaskDiscussions.vue";
import GenerateDocumentModal from "./Components/GenerateDocumentModal.vue";
//import { useRouter } from "vue-router";
import MarkComplete from "../../Components/Modals/Workflows/MarkComplete.vue";
import SubmitForReview from "../../components/Workflow/SubmitForReview.vue";
import SignForwardBar from "../../components/Workflow/SignForwardBar.vue";
import VersionHistory from "../../components/Workflow/VersionHistory.vue";
import WorkflowMetroMap from "../../components/Workflow/WorkflowMetroMap.vue";
import GenericWorkflowPanel from "../../components/GenericWorkflowPanel.vue";
import DocketTimeline from "../../components/Workflow/DocketTimeline.vue";
import DocumentDiffViewer from "../../components/Workflow/DocumentDiffViewer.vue";
import PDFEditor from "../../components/PDFViewer/PDFEditor.vue";
import AssignExistingTask from "../../components/Modals/Workflows/AssignExistingTask.vue";
import RequestRevision from "../../Components/Modals/Workflows/RequestRevision.vue";
import DeleteRevision from "../../Components/Modals/Workflows/DeleteRevision.vue";
import EditTask from "../../Components/Modals/Workflows/EditTask.vue";
import Reopen from "../../Components/Modals/Workflows/Reopen.vue";
import AppendSignatureModal from "../../Components/Modals/Workflows/AppendSignatureModal.vue";
import TaskUploadDocument from "../../components/Modals/Workflows/TaskUploadDocument.vue";
import ReturnTask from "../../Components/Modals/Workflows/ReturnTask.vue";
import RequestInfoModal from "../../Components/Modals/Workflows/RequestInfoModal.vue";
import ProvideInfoModal from "../../Components/Modals/Workflows/ProvideInfoModal.vue";
import ApproveReviewModal from "../../components/Modals/Workflows/ApproveReviewModal.vue";
import AgfRerouteModal from "../../components/Modals/Workflows/AgfRerouteModal.vue";
import jenna from "../../assets/images/jenna.svg";
import user from "../../assets/images/user.svg";
import { usePage, Link, router } from "@inertiajs/vue3";
import Ratings from "../../Components/Modals/Workflows/Ratings.vue"
import { useToast } from "primevue/usetoast";


import moment from 'moment';

const props = defineProps(['task', 'is_assigned', 'branchType'])
const page = usePage()
const toast = useToast()
const notifications = inject('notifications', null)

const current_user = computed(() => page.props.user || {})
const user_roles = computed(() => {
  const r = page.props.roles || page.props.user_roles || page.props.user?.roles || [];
  if (Array.isArray(r)) return r.map(x => typeof x === 'object' ? x.name : String(x));
  return Object.values(r || {}).map(x => typeof x === 'object' ? x.name : String(x));
})

const submitForReviewModal = ref(false);

const reloadTask = () => {
  router.reload({ only: ['task'] });
};

const onSelectVersion = (ver) => {
  if (ver && ver.path) {
    file_path.value = {
      path: ver.path,
      file_name: `Main Document (v${ver.version}).pdf`,
      name: `Main Document (v${ver.version}).pdf`
    };
    pdfTimestamp.value = new Date().getTime();
    toast.add({
      severity: "info",
      summary: `Viewing Version ${ver.version}`,
      detail: `Main document viewer switched to Version ${ver.version}.`,
      life: 2500
    });
  }
};

const formatDate = (date) => {
  if (!date) return '';
  return moment(date).format('DD MMM YYYY, HH:mm');
};

const editModal = ref(false)
const subNavVisible = ref(false);
const reOpenModal = ref(false);
const activeTab = ref("details");
const diffViewerModal = ref(false);
const generateDocModal = ref(false);

const handleDocumentGenerated = (newDoc) => {
  if (newDoc && newDoc.path) {
    file_path.value = {
      path: newDoc.path,
      file_name: newDoc.file_name,
      name: newDoc.file_name,
      id: newDoc.id
    };
    pdfTimestamp.value = new Date().getTime();
  }
  reloadTask();
};

const agfDirectiveMinute = computed(() => {
  if (!props.task) return null;

  // 1. Scan task minutes in reverse for AGF entry
  if (props.task.minutes && props.task.minutes.length > 0) {
    const minute = [...props.task.minutes].reverse().find(m => {
      const u = m.user;
      if (!u) return false;
      const dept = (u.department?.title || '').toLowerCase();
      const roles = (u.roles || []).map(r => (typeof r === 'string' ? r : r.name || '').toLowerCase());
      return dept.includes('agf') || roles.includes('accountant-general') || roles.includes('super user');
    });
    if (minute) {
      return {
        officer: (minute.user?.first_name || 'Accountant General') + ' ' + (minute.user?.last_name || ''),
        directive: minute.directive || minute.action || 'Please treat and report back.',
        date: minute.created_at
      };
    }
  }

  // 2. Check assignments for initial AGF routing directive
  if (props.task.assignments && props.task.assignments.length > 0) {
    const agfAssignment = props.task.assignments.find(a => {
      const assigner = a.assigned_by_user || a.assigned_by;
      if (!assigner || typeof assigner !== 'object') return false;
      const dept = (assigner.department?.title || '').toLowerCase();
      const roles = (assigner.roles || []).map(r => (typeof r === 'string' ? r : r.name || '').toLowerCase());
      return dept.includes('agf') || roles.includes('accountant-general') || roles.includes('super user');
    });
    if (agfAssignment && (agfAssignment.description || props.task.description)) {
      const assigner = agfAssignment.assigned_by_user || agfAssignment.assigned_by;
      return {
        officer: (assigner.first_name || 'Accountant General') + ' ' + (assigner.last_name || ''),
        directive: agfAssignment.description || props.task.description,
        date: agfAssignment.created_at || props.task.created_at
      };
    }
  }

  return null;
});

const handleStationClick = (station) => {
  activeTab.value = 'minute_sheet';
};


const pendingInfoRequest = computed(() => {
  if (!props.task || !props.task.information_requests) return null;
  return props.task.information_requests.find(req => req.status === 'pending' && req.requested_from?.id === current_user.value?.id);
});

const openProvideInfoModal = (request) => {
  selectedInfoRequest.value = request;
  provideInfoModal.value = true;
};

const isResuming = ref(false);
const resumeTaskAction = () => {
  if (isResuming.value) return;
  isResuming.value = true;
  router.post('/dashboard/resume-task', {
    task_id: props.task.id
  }, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      isResuming.value = false;
    },
    onError: () => {
      isResuming.value = false;
    }
  });
};
const minute_sheet = ref(false);
const submission = ref(false);

const sortedTaskDocs = computed(() => {
    if (!props.task || !props.task.taskdocs) return [];
    return [...props.task.taskdocs].sort((a, b) => {
        const nameA = (a.file_name || a.name || '').toLowerCase();
        const nameB = (b.file_name || b.name || '').toLowerCase();
        const aIsMinute = nameA.includes('minute sheet.pdf');
        const bIsMinute = nameB.includes('minute sheet.pdf');
        if (aIsMinute && !bIsMinute) return 1;
        if (!aIsMinute && bIsMinute) return -1;
        return 0;
    });
});

const allTaskSubmissions = computed(() => {
    const list = [];
    if (!props.task) return list;

    if (props.task.assignments && Array.isArray(props.task.assignments)) {
        for (const a of props.task.assignments) {
            if (a.task_submissions && Array.isArray(a.task_submissions)) {
                for (const sub of a.task_submissions) {
                    list.push({
                        ...sub,
                        user: a.assigned_to
                    });
                }
            }
        }
    }

    if (props.task.assigned_to_me?.task_submissions && Array.isArray(props.task.assigned_to_me.task_submissions)) {
        for (const sub of props.task.assigned_to_me.task_submissions) {
            if (!list.some(s => s.id === sub.id)) {
                list.push({
                    ...sub,
                    user: page.props.user
                });
            }
        }
    }

    return list;
});

const user_comment = ref('')
const activities = ref(false)
const comment = ref(false);
const submitted = ref(false)
const RequestRevisionModal = ref(false);
const completeModal = ref(false);
const pdfEditorRef = ref(null);
const modifiedPdfBlob = ref(null);
const isGeneratingSubmit = ref(false);

const handleOpenSubmit = async () => {
    if (pdfEditorRef.value && pdfEditorRef.value.hasModifications) {
        isGeneratingSubmit.value = true;
        try {
            modifiedPdfBlob.value = await pdfEditorRef.value.getPdfBlob();
        } catch (e) {
            console.error('Error generating modified PDF', e);
        }
        isGeneratingSubmit.value = false;
    } else {
        modifiedPdfBlob.value = null;
    }
    completeModal.value = true;
};

const isGeneratingReturn = ref(false);
const handleOpenReturn = async () => {
    if (pdfEditorRef.value && pdfEditorRef.value.hasModifications) {
        isGeneratingReturn.value = true;
        try {
            modifiedPdfBlob.value = await pdfEditorRef.value.getPdfBlob();
        } catch (e) {
            console.error('Error generating modified PDF', e);
        }
        isGeneratingReturn.value = false;
    } else {
        modifiedPdfBlob.value = null;
    }
    returnTaskModal.value = true;
};

const deleteRevisionModal = ref(false);
const Re_Assign_Modal = ref(false);
const handleTaskAssigned = () => {
  Re_Assign_Modal.value = false;
  router.visit('/dashboard/workflow');
};
const appendSignatureModal = ref(false);
const returnTaskModal = ref(false);
const requestInfoModal = ref(false);
const provideInfoModal = ref(false);
const selectedInfoRequest = ref(null);
const openUploadModal = ref(false);
const file_path = ref([]);
const pdfTimestamp = ref(new Date().getTime());
const rating = ref(false)

// Check if current user has already submitted this task and is awaiting review by next officer
const isTaskSubmittedByMe = computed(() => {
  if (!props.task || !current_user.value) return false;
  if (props.task.status === 'completed') return false;

  const assignments = props.task.assignments || [];
  if (assignments.length === 0) return false;

  const latest = assignments[0];
  const latestAssigneeId = latest.assigned_to?.id || latest.assigned_to;

  // If latest assignment is assigned to me, the task is currently in my workspace (not submitted by me)
  if (latestAssigneeId === current_user.value.id) {
    return false;
  }

  // If latest assignment is to someone else, check if I forwarded it or previously held a completed assignment
  const latestAssignerId = latest.assigned_by?.id || latest.assigned_by;
  const forwardedByMe = latestAssignerId === current_user.value.id;
  const myCompletedAssignment = assignments.find(a => {
    const uid = a.assigned_to?.id || a.assigned_to;
    return uid === current_user.value.id && a.status === 'completed';
  });

  return forwardedByMe || !!myCompletedAssignment;
});

const currentTaskHolder = computed(() => {
  const assignments = props.task?.assignments || [];
  if (assignments.length === 0) return null;
  return assignments[0].assigned_to || null;
});

const isTaskReturnedToMe = computed(() => {
  if (!props.task || !current_user.value) return false;
  const assignments = props.task?.assignments || [];
  if (assignments.length === 0) return false;
  const latest = assignments[0];
  const latestAssigneeId = latest.assigned_to?.id || latest.assigned_to;
  return latestAssigneeId === current_user.value.id && (latest.status === 'revision' || props.task.status === 'returned');
});

const isJustApproved = ref(false);

const isSubmissionApproved = computed(() => {
    if (isJustApproved.value) return true;
    if (props.task?.is_approved) return true;

    const assignments = props.task?.assigned_out_assignments || props.task?.assignments || [];
    if (!assignments || assignments.length === 0) return false;

    const latest = assignments[0];
    const latestStatus = (latest.status || '').toLowerCase();
    const currentUserId = current_user.value?.id;

    // If the active/latest assignment is currently under review or review,
    // it has NOT been approved yet by the current officer reviewing it!
    if (latestStatus === 'under review' || latestStatus === 'review') {
        return false;
    }

    // If the latest assignment is completed, check if current user held or assigned it
    const latestAssigneeId = latest.assigned_to?.id || latest.assigned_to;
    const latestAssignerId = latest.assigned_by?.id || latest.assigned_by;
    const isRelatedToMe = latestAssigneeId === currentUserId || latestAssignerId === currentUserId || isAgfRole.value;

    if (isRelatedToMe && latestStatus === 'completed') {
        return true;
    }

    return false;
});

const is_subordinate_under_review = computed(() => {
    if (!props.task || !props.task.assignments) return false;
    if (isTaskSubmittedByMe.value) return false;
    if (props.task.status === 'completed') return false;

    const isAgf = (user_roles.value || []).some(r => String(r).toLowerCase().includes('super user') || String(r).toLowerCase().includes('accountant-general'));
    if (isAgf && (props.task.status === 'under review' || props.task.assignments.some(a => a.status === 'under review'))) return true;

    // Subordinate under review: any assignment assigned by current user that is under review, OR assigned to current user under review
    return props.task.assignments.some(a => {
        const isAssigner = a.assigned_by?.id === current_user.value?.id || a.assigned_by === current_user.value?.id;
        const isAssignee = a.assigned_to?.id === current_user.value?.id || a.assigned_to === current_user.value?.id;
        return (isAssigner || isAssignee) && (a.status === 'under review' || a.status === 'review');
    });
});

const subordinate_assignment_under_review = computed(() => {
    if (!props.task || !props.task.assignments) return null;
    if (isTaskSubmittedByMe.value) return null;
    const isAgf = (user_roles.value || []).some(r => String(r).toLowerCase().includes('super user') || String(r).toLowerCase().includes('accountant-general'));
    if (isAgf) {
        return props.task.assignments.find(a => a.status === 'under review') || props.task.assignments[0];
    }
    return props.task.assignments.find(a => {
        const isAssigner = a.assigned_by?.id === current_user.value?.id || a.assigned_by === current_user.value?.id;
        const isAssignee = a.assigned_to?.id === current_user.value?.id || a.assigned_to === current_user.value?.id;
        return (isAssigner || isAssignee) && (a.status === 'under review' || a.status === 'review');
    }) || props.task.assignments.find(a => {
        const isAssignee = a.assigned_to?.id === current_user.value?.id || a.assigned_to === current_user.value?.id;
        return isAssignee;
    }) || props.task.assignments[0];
});

const canAttachSupportingDocuments = computed(() => {
  if (isTaskSubmittedByMe.value) return false;
  if (props.task?.status === 'completed') return false;
  const roles = (user_roles.value || []).map(r => String(r).toLowerCase());
  return roles.some(r => 
    r.includes('assistant director') || 
    r.includes('deputy director') || 
    r.includes('director') || 
    r.includes('super user') || 
    r.includes('accountant-general')
  );
});

const taskAttachedDocs = computed(() => {
  const docs = props.task?.taskdocs || [];
  return docs.filter(doc => {
    const name = String(doc.file_name || '').toLowerCase();
    const path = String(doc.path || '').toLowerCase();
    // Exclude the official minute sheet and document versions from supporting documents list
    if (name.includes('minute sheet') || path.includes('/documents/minutes/')) return false;
    if (name.startsWith('main document (v') || path.includes('/task_docs/versions/')) return false;
    if (props.task?.file_name && (doc.file_name === props.task.file_name || doc.path === props.task.file_name)) return false;
    if (path.startsWith('/registry/')) return false;
    return true;
  });
});

const isUploadingSupportingDoc = ref(false);
const handleSupportingFileUpload = async (event) => {
  const files = Array.from(event.target.files || []);
  if (!files.length) return;
  
  isUploadingSupportingDoc.value = true;
  const formData = new FormData();
  files.forEach(f => formData.append('files[]', f));
  
  try {
    const res = await axios.post(`/dashboard/tasks/${props.task.id}/upload-attachment`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    toast.add({ severity: 'success', summary: 'Uploaded', detail: res.data.message || 'Supporting documents attached successfully.', life: 3000 });
    reloadTask();
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Upload Error', detail: err.response?.data?.message || 'Failed to upload attachments.', life: 4000 });
  } finally {
    isUploadingSupportingDoc.value = false;
    if (event.target) event.target.value = '';
  }
};

const deleteSupportingDoc = async (doc) => {
  const result = await Swal.fire({
    title: 'Remove Supporting Document?',
    text: `Are you sure you want to remove "${doc.file_name || 'this document'}"?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#64748b',
    confirmButtonText: 'Yes, Remove',
    cancelButtonText: 'Cancel',
    reverseButtons: true,
    customClass: {
      popup: 'rounded-2xl shadow-xl border border-gray-100',
      confirmButton: 'rounded-xl px-4 py-2 font-semibold text-sm',
      cancelButton: 'rounded-xl px-4 py-2 font-semibold text-sm'
    }
  });

  if (!result.isConfirmed) return;

  try {
    const res = await axios.delete(`/dashboard/tasks/${props.task.id}/attachments/${doc.id}`);
    toast.add({ 
      severity: 'success', 
      summary: 'Document Removed', 
      detail: res.data.message || 'Supporting document removed.', 
      life: 3000 
    });
    reloadTask();
  } catch (err) {
    toast.add({ 
      severity: 'error', 
      summary: 'Delete Failed', 
      detail: err.response?.data?.message || 'Failed to remove document.', 
      life: 4000 
    });
  }
};

const getFileIcon = (fileName) => {
  if (!fileName) return 'lucide:file-text';
  const name = String(fileName).toLowerCase();
  if (name.endsWith('.pdf')) return 'lucide:file-text';
  if (name.endsWith('.doc') || name.endsWith('.docx')) return 'lucide:file-type-doc';
  if (name.endsWith('.xls') || name.endsWith('.xlsx')) return 'lucide:file-spreadsheet';
  if (name.endsWith('.png') || name.endsWith('.jpg') || name.endsWith('.jpeg')) return 'lucide:image';
  return 'lucide:file';
};

const canReassignTask = computed(() => {
  const assignments = props.task?.assigned_out_assignments || props.task?.assignments || [];
  if (!assignments || assignments.length === 0) return true;
  const latest = assignments[0];
  const latestStatus = (latest.status || '').toLowerCase();

  // Reassignment is locked while a submission is under review until approved
  if (latestStatus === 'under review' || latestStatus === 'review') {
    return isSubmissionApproved.value;
  }

  return isSubmissionApproved.value || latestStatus === 'completed';
});

const reassignTaskTooltip = computed(() => {
  if (!canReassignTask.value) {
    return 'Task must be approved before reassigning';
  }
  return (props.task?.assignments && props.task.assignments.length > 0) ? 'Reassign Task' : 'Assign Task';
});

const latestSubmissionDoc = computed(() => {
    const subAssignment = subordinate_assignment_under_review.value;
    if (subAssignment && subAssignment.task_submissions && subAssignment.task_submissions.length > 0) {
        const sortedSubs = [...subAssignment.task_submissions].sort((a, b) => b.id - a.id);
        for (const s of sortedSubs) {
            if (s.task_submission_files && s.task_submission_files.length > 0) {
                return s.task_submission_files[0];
            }
        }
    }
    if (allTaskSubmissions.value && allTaskSubmissions.value.length > 0) {
        for (const s of allTaskSubmissions.value) {
            if (s.task_submission_files && s.task_submission_files.length > 0) {
                return s.task_submission_files[0];
            }
        }
    }
    return null;
});

const activePreviewDoc = computed(() => {
    if (file_path.value && file_path.value.path) {
        return file_path.value;
    }
    if (props.task?.latest_document_version?.path) {
        return {
            path: props.task.latest_document_version.path,
            file_name: `Main Document (v${props.task.latest_document_version.version}).pdf`,
            name: `Main Document (v${props.task.latest_document_version.version}).pdf`
        };
    }
    if (props.task?.document_versions && props.task.document_versions.length > 0) {
        const latest = props.task.document_versions[0];
        return {
            path: latest.path,
            file_name: `Main Document (v${latest.version}).pdf`,
            name: `Main Document (v${latest.version}).pdf`
        };
    }
    if (latestSubmissionDoc.value && latestSubmissionDoc.value.path) {
        return latestSubmissionDoc.value;
    }
    if (sortedTaskDocs.value && sortedTaskDocs.value.length > 0) {
        const nonMinute = sortedTaskDocs.value.find(d => !(d.file_name || d.name || '').toLowerCase().includes('minute sheet'));
        if (nonMinute) return nonMinute;
        return sortedTaskDocs.value[0];
    }
    if (props.task?.file && props.task?.file?.path) {
        return props.task.file;
    }
    return latestSubmissionDoc.value || null;
});

const currentActiveDocPath = computed(() => {
  return activePreviewDoc.value?.path || '';
});

const isSavingPdf = ref(false);
const handleSavePdf = async ({ blob }) => {
    if (!blob) return;
    isSavingPdf.value = true;
    try {
        const formData = new FormData();
        formData.append('task_id', props.task.id);
        formData.append('target_path', currentActiveDocPath.value);
        const rawName = currentActiveDocPath.value ? currentActiveDocPath.value.split('/').pop().split('?')[0] : 'document.pdf';
        formData.append('file', blob, 'modified_' + rawName);

        const res = await axios.post((page.props.base || '') + '/dashboard/task-save-modified-pdf', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        if (res.data.status === 'success') {
            toast.add({
                severity: "success",
                summary: "PDF Saved",
                detail: "Document annotations and signatures have been saved successfully.",
                life: 3000
            });
            pdfTimestamp.value = new Date().getTime();
            router.reload({ only: ['task'] });
        } else {
            toast.add({
                severity: "error",
                summary: "Error",
                detail: res.data.message || 'Failed to save document.',
                life: 4000
            });
        }
    } catch (e) {
        console.error('Error saving modified PDF:', e);
        toast.add({
            severity: "error",
            summary: "Error",
            detail: e.response?.data?.message || 'Failed to save document changes.',
            life: 4000
        });
    } finally {
        isSavingPdf.value = false;
    }
};

const isApproving = ref(false);
const openApproveModal = ref(false);
const openAgfRerouteModal = ref(false);

const workspaceLayoutMode = ref('split'); // 'split' (60/40), 'balanced' (50/50), 'focus' (100% doc)
const moreActionsOpen = ref(false);

const isAgfRole = computed(() => {
  const r = (user_roles.value || []).map(x => String(x).toLowerCase());
  return r.some(x => x.includes('super user') || x.includes('accountant-general') || x.includes('super admin'));
});

const approveButtonLabel = computed(() => {
  if (isAgfRole.value) return 'Final Approval';
  const r = (user_roles.value || []).map(x => String(x).toLowerCase());
  if (r.some(x => x.includes('deputy director'))) return 'Submit to Director';
  if (r.some(x => x.includes('assistant director'))) return 'Submit to Deputy Director';
  if (r.some(x => x.includes('director'))) return 'Submit to AGF';
  return 'Submit to Assigner';
});

const canReturnTask = computed(() => {
  if (isTaskSubmittedByMe.value) return false;
  if (isSubmissionApproved.value) return false;
  return Boolean(
    (props.task && props.task.assigned_to_me && (props.task.assigned_to_me.status == 'pending' || props.task.assigned_to_me.status == 'revision') && props.is_assigned == 1) ||
    is_subordinate_under_review.value
  );
});

const canRequestEdit = computed(() => {
  return !isTaskSubmittedByMe.value && is_subordinate_under_review.value && !isSubmissionApproved.value;
});

const canRequestInfo = computed(() => {
  if (isTaskSubmittedByMe.value) return false;
  return Boolean(
    (props.task && props.task.assigned_to_me && (props.task.assigned_to_me.status == 'pending' || props.task.assigned_to_me.status == 'revision') && props.is_assigned == 1) ||
    is_subordinate_under_review.value
  );
});

const canRerouteTask = computed(() => {
  if (isTaskSubmittedByMe.value) return false;
  const isEligibleRole = (user_roles.value || []).some(r => {
    const lr = String(r).toLowerCase();
    return lr.includes('super user') || lr.includes('super admin') || isAgfRole.value;
  });
  return isEligibleRole && (is_subordinate_under_review.value || isSubmissionApproved.value);
});

const canReassignSecondary = computed(() => {
  if (!canReassignTask.value) return false;
  if (isTaskSubmittedByMe.value) return false;
  const roles = (user_roles.value || []).map(r => String(r).toLowerCase());
  if (roles.includes('regular user') || roles.includes('regular users')) return false;
  return Boolean(is_subordinate_under_review.value || isSubmissionApproved.value);
});

const canEditTask = computed(() => {
  return props.task?.status != 'completed' && props.task?.created_by == current_user.value?.id;
});

const canRateTask = computed(() => {
  return Boolean(props.task && props.task.assignments && props.task.assignments.length > 0 && props.task.assignments[0].status == 'completed' && !props.task.assignments[0].rating);
});

const hasSecondaryActions = computed(() => {
  return canReturnTask.value || canRequestEdit.value || canRequestInfo.value || canRerouteTask.value || canReassignSecondary.value || canEditTask.value || canRateTask.value;
});

const openApproveOnlyModal = ref(false);
const approveOnlyRemark = ref('Subordinate submission approved and verified.');
const approveOnlyPin = ref('');
const isProcessingApproveOnly = ref(false);

const executeApproveOnly = async () => {
  isProcessingApproveOnly.value = true;
  try {
    const res = await axios.post(`/dashboard/tasks/${props.task.id}/approve-submission`, {
      remark: approveOnlyRemark.value,
      pin: approveOnlyPin.value || null,
    });

    if (res.data?.success) {
      isJustApproved.value = true;
      toast.add({
        severity: "success",
        summary: "Task Approved",
        detail: res.data.message || "Subordinate submission approved. You can now reassign this task.",
        life: 3000
      });
      openApproveOnlyModal.value = false;
      approveOnlyPin.value = '';
      reloadTask();
    }
  } catch (err) {
    toast.add({
      severity: "error",
      summary: "Approval Failed",
      detail: err.response?.data?.message || 'Could not approve task.',
      life: 4000
    });
  } finally {
    isProcessingApproveOnly.value = false;
  }
};

const handleApproveConfirmed = async ({ directive, pin, attachments, onDone }) => {
    const assignment = subordinate_assignment_under_review.value;
    if (!assignment) return;
    
    isApproving.value = true;
    let fileToUpload = null;
    let fileToUploadPath = null;
    
    if (pdfEditorRef.value && pdfEditorRef.value.hasModifications) {
        try {
            const blob = await pdfEditorRef.value.getPdfBlob();
            if (blob) {
                let fileName = "modified_document.pdf";
                if (currentActiveDocPath.value) {
                    fileToUploadPath = currentActiveDocPath.value;
                    fileName = "signed_" + fileToUploadPath.split('/').pop().split('?')[0];
                } else if (props.task && props.task.file && props.task.file.path) {
                    fileToUploadPath = props.task.file.path;
                    fileName = "signed_" + fileToUploadPath.split('/').pop().split('?')[0];
                } else if (props.task && props.task.taskdocs && props.task.taskdocs.length > 0) {
                    fileToUploadPath = props.task.taskdocs[0].path;
                    fileName = "signed_" + fileToUploadPath.split('/').pop().split('?')[0];
                }
                fileToUpload = new File([blob], fileName, { type: 'application/pdf' });
            }
        } catch (e) {
            console.error('Error generating modified PDF', e);
        }
    }

    const payload = {
        task_id: props.task.id,
        task_assignment_id: assignment.id,
        directive: directive,
        approval_pin: pin,
        modified_file: fileToUpload,
        modified_file_path: fileToUploadPath,
        attachments: attachments || []
    };

    router.post('/dashboard/approve-task', payload, {
        onSuccess: () => {
            isApproving.value = false;
            if (onDone) onDone(null);
            openApproveModal.value = false;
            toast.add({
                severity: "success",
                summary: "Submitted",
                detail: "Task approved and submitted to assigner.",
                life: 3000
            });
            router.visit('/dashboard/workflow');
        },
        onError: (err) => {
            isApproving.value = false;
            const errMsg = err?.approval_pin || err?.error || 'Failed to submit task.';
            if (onDone) onDone(errMsg);
            toast.add({
                severity: "error",
                summary: "Error",
                detail: errMsg,
                life: 4000
            });
        }
    });
};

const handleAgfRerouteConfirmed = ({ reroute_department_id, directive, onDone }) => {
    const assignment = subordinate_assignment_under_review.value;
    if (!assignment) return;

    router.post('/dashboard/approve-task', {
        task_id: props.task.id,
        task_assignment_id: assignment.id,
        reroute_department_id: reroute_department_id,
        directive: directive,
    }, {
        onSuccess: () => {
            if (onDone) onDone();
            openAgfRerouteModal.value = false;
            toast.add({
                severity: "success",
                summary: "Rerouted",
                detail: "Task successfully rerouted to department.",
                life: 3000
            });
            router.visit('/dashboard/workflow');
        },
        onError: () => {
            if (onDone) onDone();
        }
    });
};

const approveSubordinateTask = async (andReassign = false) => {
    if (andReassign) {
        Re_Assign_Modal.value = true;
    } else {
        openApproveModal.value = true;
    }
};

const comment_down = ref(false)
const up_comment = ref([])
const down_comment = ref([])

const ratingsModal = () => {
  rating.value = true;
}

const ReloadTask = ()=>{
  router.reload({ only: ['task'] })
}

const refreshHandler = () => {
  router.reload({ only: ['task'] })
  pdfTimestamp.value = new Date().getTime();
}

const makeComment = (direction) => {
  if (user_comment.value == '') {
    document.getElementById('comment_text').focus();
    return false
  }

  if(direction == 'up'){
    if(props.task.assigned_to_me){
      const comment_to = props.task.assigned_to_me.assigned_by.id;
      const task_assignment_id = props.task.assigned_to_me.id
      router.post('/dashboard/task-comment', 
        { task_id: props.task.id, comment: user_comment.value, comment_to: comment_to, task_assignment_id: task_assignment_id }, {
        onSuccess: page => {
          user_comment.value = ''
          router.reload({ only: ['task'] })
        }
      })
    }
  }else{
    if(props.task.assignments && props.task.assignments.length >0){
      const comment_to = props.task.assignments[0].assigned_to.id;
      const task_assignment_id = props.task.assignments[0].id
      router.post('/dashboard/task-comment', 
        { task_id: props.task.id, comment: user_comment.value, comment_to: comment_to, task_assignment_id: task_assignment_id }, {
        onSuccess: page => {
          user_comment.value = ''
          router.reload({ only: ['task'] })
        }
      })
    }
  }
  
}

const showDelegationTrail = ref(true);

const currentAssignment = computed(() => {
  if (!props.task?.assignments || props.task.assignments.length === 0) return null;
  return props.task.assignments[0];
});

const routingTrail = computed(() => {
  if (!props.task?.assignments || props.task.assignments.length === 0) return [];
  return [...props.task.assignments].reverse();
});

const getAssigneeName = (assignment) => {
  if (!assignment || !assignment.assigned_to) return 'Unassigned';
  const first = assignment.assigned_to.first_name || '';
  const last = assignment.assigned_to.last_name || '';
  return `${first} ${last}`.trim() || 'Staff Member';
};

const getAssigneeAvatar = (assignment) => {
  if (assignment?.assigned_to?.profile_image) {
    return (page.props.base || '') + '/' + assignment.assigned_to.profile_image;
  }
  return (page.props.base || '') + '/images/user.svg';
};

const getAssigneeBranchOrRole = (assignment) => {
  if (!assignment || !assignment.assigned_to) return '';
  const u = assignment.assigned_to;
  const roles = (u.roles || []).map(r => (typeof r === 'object' ? r.name : String(r)).toLowerCase());

  if (roles.includes('director')) {
    return u.department?.title ? `${u.department.title} (Director)` : 'Director';
  }
  if (roles.includes('deputy director')) {
    return u.division?.title ? `${u.division.title} (Deputy Director)` : 'Deputy Director';
  }
  if (roles.includes('assistant director')) {
    return u.unit?.title ? `${u.unit.title} (Assistant Director)` : 'Assistant Director';
  }
  if (u.unit?.title) {
    return `${u.unit.title} • Staff`;
  }
  if (u.division?.title) {
    return `${u.division.title} • Staff`;
  }
  if (u.department?.title) {
    return `${u.department.title} • Staff`;
  }
  return 'Staff Member';
};

const getUserOrBranchName = (assignment) => getAssigneeName(assignment);

function getImageForGroup(assignment) {
  if(assignment){
    const current_roles = assignment.assigned_to.roles.map(role => role.name)

    if(assignment.assigned_to.unit && current_roles.includes('assistant director')){
      return page.props.base + "/images/unit_active.svg";
    }else

    if(assignment.assigned_to.division && current_roles.includes('deputy director')){
      return page.props.base + "/images/division_active.svg";
    }else

    if(assignment.assigned_to && current_roles.includes('director')){
       return page.props.base + "/images/department_active.svg";
    }else{
       if(assignment.assigned_to.profile_image){
        return page.props.base + "/" + assignment.assigned_to.profile_image;
       }else{
        return page.props.base + "/images/user.svg";
       }
    }
  }
}

const getUserName = (comment) => {

  if (comment.sent_by_user) {
    if (page.props.user.id == comment.comment_by) {
      return 'You';
    }
    return comment.sent_by_user.first_name + ' ' + comment.sent_by_user.last_name
  } else {
    return ''
  }
}

const getUserNameDown = (comment) => {

  if (comment.sent_to_user) {
    if (page.props.user.id == comment.comment_by) {
      return 'You';
    }
    return comment.sent_to_user.first_name + ' ' + comment.sent_to_user.last_name
  } else {
    return ''
  }
}

const getUserProfileImage = (comment) => {
  if(comment.sent_by_user){
    if(page.props.user.id == comment.sent_by){
      if(page.props.user && page.props.user.profile_image){
        return page.props.base + '/' + page.props.user.profile_image;
      }else{
        return page.props.base + '/images/user.png';
      }
    }else{
      if(comment.sent_by_user.profile_image){
        return page.props.base + '/' + comment.sent_by_user.profile_image;
      }else{
        return page.props.base + '/images/user.png';
      }
      
    }
  }
}

const getUserProfileImageDown = (comment) => {
  if(comment.sent_to_user){
    if(page.props.user && page.props.user.id == comment.sent_by){
       if(comment.sent_to_user.profile_image){
        return page.props.base + '/' + comment.sent_to_user.profile_image;
       }else{
        return page.props.base + '/images/user.png';
       }
      
    }else{
      if(comment.sent_to_user.profile_image){
         return page.props.base + '/' + comment.sent_to_user.profile_image;
      }else{
        return page.props.base + '/images/user.png';
      }
    }
  }

  if (comment.user && comment.user.profile_image) {
    return page.props.base + '/' + comment.user.profile_image;
  } else {
    return page.props.base + '/images/user.png';
  }
}

const data = ref({

});

const setFilePath = (path) => {
  file_path.value = path;
}

const ForwardTask = (submission) => {
  let task_submission_id = submission?.id;
  router.post('/dashboard/forward-task', { task: props.task, branchType: props.branchType, task_submission_id: task_submission_id }, {
    onSuccess: () => {
      router.reload({ only: ['task'] })
      toast.add({
        severity: "success",
        detail: `Your task has been forwarded to ${props.task?.assigned_to_me?.assigned_by?.last_name} ${props.task?.assigned_to_me?.assigned_by?.first_name}`,
        life: 3000,
      });
    }
  })
}


const documents = ref([
  // {
  //   name: "Tech design requirements.pdf",
  //   size: "200 KB ",
  //   icon: "vscode-icons:file-type-word",
  // },
  // {
  //   name: "Tech design requirements.pdf",
  //   size: "200 KB ",
  //   icon: "vscode-icons:file-type-excel2",
  // },
]);

const comments = ref([
  // {
  //   img: jenna,
  //   name: "Jenna Ryan obadimu",
  //   time: "6m ago",
  //   comment:
  //     "Task Received i would begin work immediately & review and archive assigned documents promptly to ensure seamless workflow. Documents awaiting archiving in the Finance Department. ",
  // },
  // {
  //   img: user,
  //   name: "You",
  //   time: "6m ago",
  //   comment: "Okay understood, i would work on the changes in due time boss ",
  // },
]);

// const downloadFile = (filename)=>{
//     const url = '/dashboard/download-task-file/'+filename
//     window.open(url, '_blank');
// }

const downloadFile = async (filename) => {
  try {
    const url = `${page.props.base}` + `${filename}`;

    const response = await fetch(url);
    if (!response.ok) {
      throw new Error('Network response was not ok');
    }

    const blob = await response.blob();
    const objectUrl = URL.createObjectURL(blob);

    const link = document.createElement('a');
    link.href = objectUrl;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    // Clean up the object URL
    URL.revokeObjectURL(objectUrl);
  } catch (error) {
    console.error('There has been a problem with your fetch operation:', error);
  }
}

const downComment = computed({
  get() {
    if (props.task && props.task.assignments && props.task.assignments.length > 0) {
      down_comment.value = props.task.assignments[0].comments
      return down_comment.value
    }
    return [];
  }, 
  set(value){
    down_comment.value.unshift(value)
  }
})

const upComment = computed({
  get(){
    if(props.task && props.task.assigned_to_me && props.task.assigned_to_me.comments){
      up_comment.value = props.task.assigned_to_me.comments
      return up_comment.value;
    }
  },
  set(value){
    up_comment.value.unshift(value)
  }
})

const toggleSidemenu = (action) => {

  if (action === "comment") {
    comment.value = !comment.value;
    sidemenu.value = !sidemenu.value;
  }
  if (action === "comment_down") {
    comment_down.value = !comment_down.value;
    sidemenu.value = !sidemenu.value;
  }
  if (action === "submission") {
    submission.value = !submission.value;
    sidemenu.value = !sidemenu.value;
  }
  if (action === "submitted") {
    submitted.value = !submitted.value;
    sidemenu.value = !sidemenu.value;
  }
  if (action === "activity") {
    activities.value = !activities.value;
    sidemenu.value = !sidemenu.value;
  }
  if (action === "minute_sheet") {
    minute_sheet.value = !minute_sheet.value;
    sidemenu.value = !sidemenu.value;
  }
  if (action === "back") {
    comment.value = false;
    sidemenu.value = false;
    submission.value = false;
    activities.value = false;
    minute_sheet.value = false;
  }
};

const getFileExtension = (file) => {
  const f_path = file.path || file.name || (typeof file === 'string' ? file : '');
  const parts = f_path.split('.');
  const extension = parts.pop().toLowerCase();

  if (extension == 'jpg' || extension == 'jpeg' || extension == 'gif' || extension == 'png') {
    return encodeURI((page.props.base || '') + file.path)
  }

  if (extension == 'pdf') {
    return encodeURI((page.props.base || '') + '/images/pdf.svg')
  }

  if (extension == 'doc' || extension == 'docx') {
    return encodeURI((page.props.base || '') + '/images/word.svg')
  }

  if (extension == 'xlx' || extension == 'xlsx') {
    return encodeURI((page.props.base || '') + '/images/excel.svg')
  }

  if (extension == 'ppt' || extension == 'pptx') {
    return encodeURI((page.props.base || '') + '/images/ppt.svg')
  }

  return encodeURI((page.props.base || '') + '/images/file.svg')
}


const fileType = (file) => {
  if (!file || !file.path) return '';
  const clean = String(file.path).split('?')[0].toLowerCase();
  const parts = clean.split('.');
  const extension = parts.pop();

  if (['jpg', 'jpeg', 'gif', 'png', 'webp'].includes(extension)) {
    return 'image';
  }

  if (extension === 'pdf') {
    return 'pdf';
  }

  if (['doc', 'docx', 'doctx'].includes(extension)) {
    return 'word';
  }

  if (['xlx', 'xlsx', 'csv'].includes(extension)) {
    return 'excel';
  }

  return '';
};

watch(notifications,(newVal)=>{

  if(newVal && newVal.Today){
    // alert('yes')
    let new_comment = newVal.Today[0].data.comment;
    let comment = comments.value.findIndex((item)=>item.id == new_comment.id)
    if(comment == -1){
      comments.value.unshift(new_comment)
    }
  }

})

watch(()=>props.task, ()=>{
  comments.value = props.task?.comments
}, {immediate: true})

onMounted(()=>{

  // window.Echo.private('comment.' + current_user.value.id)
  //     .notification((notification) => {
  //       console.log(notification)
  //       alert('yes')
  //         //getUserNotifications()
  //     });




})

</script>

<style></style>
