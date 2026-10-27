<template>
  <div v-if="shouldShowBar" class="sticky bottom-4 z-40 w-full max-w-5xl mx-auto px-4 transition-all duration-300">
    <div class="backdrop-blur-xl bg-white/90 border border-slate-200/90 shadow-2xl rounded-2xl p-3 md:p-4 flex flex-col md:flex-row items-center justify-between gap-3 text-slate-800">
      
      <!-- Left: Status & Routing Preview -->
      <div class="flex items-center gap-3 w-full md:w-auto">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center shadow-md shadow-blue-500/20 shrink-0">
          <Icon :icon="chainInfo?.current_user?.is_agf ? 'lucide:award' : 'lucide:git-merge'" width="20" />
        </div>
        <div class="min-w-0">
          <div class="flex items-center gap-2 flex-wrap">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">
              {{ props.task?.workflow_version_id ? (props.task?.workflow_version?.workflow?.title || 'Official Statutory Application') : 'Living Document Docket' }}
            </span>
            <span v-if="props.task?.workflow_version_id && activeStepName" class="px-2 py-0.5 text-[10px] font-bold bg-blue-100 text-blue-800 rounded-full border border-blue-200">
              {{ activeStepName }}
            </span>
            <span v-else-if="chainInfo?.current_user?.is_compulsory_full_sig" class="px-2 py-0.5 text-[10px] font-bold bg-amber-100 text-amber-800 rounded-full border border-amber-200">
              Full Signature Compulsory
            </span>
          </div>
          <p class="text-sm font-semibold text-slate-800 truncate">
            <template v-if="props.task?.workflow_version_id">
              <span v-if="isTaskSubmitted" class="inline-flex items-center gap-1.5 text-emerald-600 font-bold">
                <Icon icon="lucide:check-circle" width="16" />
                Submitted • Docket with Reviewing Authority
              </span>
              <span v-else-if="primaryGenericForwardAction" class="inline-flex items-center gap-1.5 text-blue-700 font-semibold flex-wrap">
                <Icon icon="lucide:arrow-right-circle" width="16" class="text-blue-600" />
                <span>Next Action: <strong class="text-blue-900">{{ primaryGenericForwardAction.action_name || primaryGenericForwardAction.name }}</strong></span>
                <span v-if="primaryGenericForwardAction.target_user" class="text-slate-500 font-normal">
                  • Target: <strong class="text-slate-800">{{ primaryGenericForwardAction.target_user.name }}</strong> ({{ primaryGenericForwardAction.target_user.role }})
                </span>
              </span>
              <span v-else class="text-slate-500">Awaiting Action</span>
            </template>
            <template v-else-if="isTaskSubmitted">
              <span class="inline-flex items-center gap-1.5 text-emerald-600 font-bold">
                <Icon icon="lucide:check-circle" width="16" />
                Submitted to {{ chainInfo?.current_holder?.name || chainInfo?.next_approver?.name || 'Assigner' }} ({{ chainInfo?.current_holder?.role || chainInfo?.next_approver?.role || 'Reviewing Authority' }})
              </span>
            </template>
            <template v-else-if="chainInfo?.current_user?.is_agf">
              Executive Final Approval (Accountant-General)
            </template>
            <template v-else-if="chainInfo?.next_approver">
              Submit Target: <span class="text-blue-600">{{ chainInfo.next_approver.name }}</span> ({{ chainInfo.next_approver.role }})
            </template>
            <template v-else-if="chainInfo?.assigner">
              Submit Target: <span class="text-blue-600">{{ chainInfo.assigner.name }}</span> ({{ chainInfo.assigner.role }})
            </template>
            <template v-else>
              Awaiting Action
            </template>
          </p>
        </div>
      </div>

      <!-- Right: Action Buttons -->
      <div class="flex items-center gap-2.5 w-full md:w-auto justify-end flex-wrap">
        
        <!-- Return with Notes Button -->
        <button 
          v-if="!props.task?.workflow_version_id || primaryGenericReturnAction"
          type="button"
          :disabled="isReturnLocked"
          @click="isReturnLocked ? null : (openReturnModal = true)"
          :class="[
            'px-4 py-2.5 rounded-xl border font-semibold text-xs transition-all flex items-center gap-1.5 shadow-2xs',
            isReturnLocked 
              ? 'border-slate-200 bg-slate-100 text-slate-400 cursor-not-allowed opacity-60 shadow-none' 
              : 'border-rose-200 bg-rose-50/70 hover:bg-rose-100/80 text-rose-700 hover:scale-105 active:scale-95 cursor-pointer'
          ]"
          :title="returnButtonTooltip"
        >
          <Icon icon="lucide:corner-up-left" width="16" />
          <span>{{ props.task?.workflow_version_id && primaryGenericReturnAction ? (primaryGenericReturnAction.action_name || primaryGenericReturnAction.name) : 'Return with Notes' }}</span>
        </button>

        <!-- Skip Level Button (AD / DD only) -->
        <button 
          v-if="!props.task?.workflow_version_id && hasSkipTargets"
          type="button"
          :disabled="isSkipLocked"
          @click="isSkipLocked ? null : openSkipLevelDialog()"
          :class="[
            'px-4 py-2.5 rounded-xl border font-semibold text-xs transition-all flex items-center gap-1.5 shadow-2xs',
            isSkipLocked 
              ? 'border-slate-200 bg-slate-100 text-slate-400 cursor-not-allowed opacity-60 shadow-none' 
              : 'border-indigo-200 bg-indigo-50/70 hover:bg-indigo-100 text-indigo-700 hover:scale-105 active:scale-95 cursor-pointer'
          ]"
          :title="skipButtonTooltip"
        >
          <Icon icon="lucide:fast-forward" width="16" />
          <span>Skip Level</span>
        </button>

        <!-- Separate Approve Button (approves subordinate's work without forwarding so it can be reassigned) -->
        <button 
          v-if="!props.task?.workflow_version_id && (isSubordinateUnderReview || isSubmissionApproved)"
          type="button"
          :disabled="isSubmissionApproved || isTaskSubmitted"
          @click="(isSubmissionApproved || isTaskSubmitted) ? null : (openApproveOnlyModal = true)"
          :class="[
            'px-4 py-2.5 rounded-xl font-bold text-xs transition-all flex items-center gap-1.5 shadow-2xs',
            (isSubmissionApproved || isTaskSubmitted) 
              ? 'border border-slate-200 bg-slate-100 text-slate-400 cursor-not-allowed opacity-60 shadow-none' 
              : 'border border-emerald-300 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 hover:scale-105 active:scale-95 cursor-pointer'
          ]"
          :title="isTaskSubmitted ? 'Task has already been submitted' : (isSubmissionApproved ? 'Submission has already been approved' : 'Approve subordinate submission so you can reassign to another user or submit to your assigner')"
        >
          <Icon :icon="(isSubmissionApproved || isTaskSubmitted) ? 'lucide:check-check' : 'mdi:check-decagram'" width="16" :class="(isSubmissionApproved || isTaskSubmitted) ? 'text-slate-400' : 'text-emerald-600'" />
          <span>{{ (isSubmissionApproved || isTaskSubmitted) ? 'Approved' : 'Approve' }}</span>
        </button>

        <!-- AGF Reroute to Department Button -->
        <button 
          v-if="!props.task?.workflow_version_id && chainInfo?.current_user?.is_agf"
          type="button"
          :disabled="!isSubmissionApproved || isTaskSubmitted"
          @click="(!isSubmissionApproved || isTaskSubmitted) ? null : emit('openAgfReroute')"
          :class="[
            'px-4 py-2.5 rounded-xl font-bold text-xs transition-all flex items-center gap-1.5 shadow-2xs',
            (!isSubmissionApproved || isTaskSubmitted) 
              ? 'border border-slate-200 bg-slate-100 text-slate-400 cursor-not-allowed opacity-60 shadow-none' 
              : 'border border-purple-300 bg-purple-50 hover:bg-purple-100 text-purple-800 hover:scale-105 active:scale-95 cursor-pointer'
          ]"
          :title="!isSubmissionApproved ? 'Task submission must be approved before rerouting' : (isTaskSubmitted ? 'Task has already been submitted' : 'Reroute task to another department for a different process')"
        >
          <Icon icon="lucide:git-fork" width="16" :class="(!isSubmissionApproved || isTaskSubmitted) ? 'text-slate-400' : 'text-purple-600'" />
          <span>Reroute to Department</span>
        </button>

        <!-- Submit to Assigner Button (or Final Approval for AGF or Generic Forward Action) -->
        <button 
          type="button"
          :disabled="isSubmitLocked"
          @click="isSubmitLocked ? null : (openSignModal = true)"
          :class="[
            'px-5 py-2.5 rounded-xl font-bold text-xs transition-all flex items-center gap-2 shadow-2xs',
            isSubmitLocked 
              ? 'border border-slate-200 bg-slate-100 text-slate-400 cursor-not-allowed opacity-60 shadow-none' 
              : 'text-white bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 hover:from-blue-700 hover:to-indigo-700 shadow-md hover:shadow-lg hover:shadow-blue-500/25 hover:scale-105 active:scale-95 cursor-pointer'
          ]"
          :title="submitButtonTooltip"
        >
          <Icon :icon="isTaskSubmitted ? 'lucide:check-check' : 'lucide:send'" width="16" :class="isSubmitLocked ? 'text-slate-400' : 'text-white'" />
          <span>{{ isTaskSubmitted ? 'Submitted' : submitButtonLabel }}</span>
        </button>
      </div>

    </div>

    <!-- SUBMIT TO ASSIGNER MODAL -->
    <Dialog v-model:visible="openSignModal" :closable="false" modal :pt="{
      root: { class: 'bg-white rounded-3xl shadow-2xl max-h-[92vh] flex flex-col w-[95vw] md:w-[680px] border border-gray-100 overflow-hidden' },
      content: { class: 'p-0 flex flex-col overflow-hidden h-full flex-1' },
      header: { class: 'hidden' },
      footer: { class: 'hidden' }
    }">
      <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 px-6 py-5 flex items-center justify-between text-white shrink-0">
        <div class="flex items-center gap-3">
          <div class="w-11 h-11 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center border border-white/30">
            <Icon icon="lucide:signature" width="22" class="text-white" />
          </div>
          <div>
            <h3 class="text-lg font-bold text-white">
              {{ modalTitle }}
            </h3>
            <p class="text-blue-100 text-xs">A single PIN signs, stamps the minute sheet and submits.</p>
          </div>
        </div>
        <button @click="openSignModal = false" class="w-8 h-8 flex items-center justify-center rounded-xl bg-white/10 hover:bg-white/20 text-white cursor-pointer">
          <Icon icon="lucide:x" width="18" />
        </button>
      </div>

      <div class="p-6 md:p-7 flex flex-col gap-5 overflow-y-auto max-h-[calc(92vh-160px)] custom-scrollbar bg-slate-50/50">
        
        <!-- Forwarding Info Card -->
        <div class="bg-white p-4 rounded-2xl border border-blue-100 flex items-center justify-between shadow-2xs">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
              <Icon icon="lucide:arrow-right" width="18" />
            </div>
            <div>
              <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                {{ selectedSkipTarget ? 'Skipping Level Forward To' : 'Submission Target' }}
              </p>
              <h5 class="text-sm font-bold text-slate-800">
                <template v-if="selectedSkipTarget">
                  {{ selectedSkipTarget.name }} ({{ selectedSkipTarget.role }})
                </template>
                <template v-else-if="props.task?.workflow_version_id && primaryGenericForwardAction?.target_user">
                  {{ primaryGenericForwardAction.target_user.name }} ({{ primaryGenericForwardAction.target_user.role }})
                </template>
                <template v-else-if="chainInfo?.next_approver">
                  {{ chainInfo.next_approver.name }} ({{ chainInfo.next_approver.role }})
                </template>
                <template v-else-if="chainInfo?.assigner">
                  {{ chainInfo.assigner.name }} ({{ chainInfo.assigner.role }})
                </template>
                <template v-else>
                  Accountant-General (Final Approval)
                </template>
              </h5>
            </div>
          </div>
          <span v-if="selectedSkipTarget" class="text-xs px-2.5 py-1 bg-amber-100 text-amber-800 rounded-lg font-bold">
            Skip Level Mode
          </span>
        </div>

        <!-- Optional Skip Reason if skip level is active -->
        <div v-if="selectedSkipTarget" class="bg-amber-50/80 p-4 rounded-2xl border border-amber-200 space-y-2">
          <label class="text-xs font-bold uppercase tracking-wider text-amber-900 flex items-center gap-1.5">
            <Icon icon="lucide:alert-triangle" width="14" class="text-amber-600" />
            Reason for Skipping Approval Level (Required on Minute Sheet)
          </label>
          <input 
            type="text" 
            v-model="skipReason" 
            placeholder="e.g. Urgent deadline clearance / Direct executive directive..." 
            class="w-full text-sm border border-amber-300 rounded-xl p-3 outline-none focus:border-amber-500 bg-white"
          />
        </div>

        <!-- Quick Remark Chips -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-3">
          <label class="text-xs font-bold uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
            <Icon icon="lucide:message-square" width="14" class="text-blue-600" />
            Minute Directive / Remarks
          </label>

          <div class="flex flex-wrap gap-2">
            <button 
              type="button"
              v-for="(chip, i) in signChips" 
              :key="i"
              @click="signRemark = chip"
              class="text-xs px-3 py-1.5 rounded-xl border transition-all cursor-pointer select-none font-medium text-left"
              :class="signRemark === chip ? 'bg-blue-50 border-blue-300 text-blue-700 shadow-2xs font-semibold' : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100'"
            >
              {{ chip }}
            </button>
          </div>

          <textarea 
            v-model="signRemark" 
            rows="3" 
            class="w-full text-sm border border-slate-200 rounded-xl p-3.5 outline-none focus:border-blue-500 focus:ring-3 focus:ring-blue-500/15 text-slate-800 resize-none"
            placeholder="Enter endorsement notes for minute sheet..."
          ></textarea>
        </div>

        <!-- Optional Attachments Dropzone for AD, DD, Director -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-3">
          <div class="flex items-center justify-between">
            <label class="text-xs font-bold uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
              <Icon icon="lucide:paperclip" width="14" class="text-blue-600" />
              Supporting Attachments (Optional)
            </label>
            <span class="text-[11px] text-slate-400">PDF, DOCX, PNG, JPG (max 20MB)</span>
          </div>

          <div class="relative border-2 border-dashed border-slate-200 hover:border-blue-400 bg-slate-50/50 hover:bg-blue-50/20 rounded-xl p-3.5 transition-all text-center cursor-pointer group">
            <input type="file" multiple @change="handleFiles" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full z-10" />
            <div class="flex flex-col items-center justify-center gap-1 pointer-events-none">
              <Icon icon="lucide:upload-cloud" width="22" class="text-slate-400 group-hover:text-blue-600 transition-colors" />
              <p class="text-xs text-slate-600 font-medium"><span class="text-blue-600 font-bold">Click to upload</span> or drag and drop supporting files</p>
            </div>
          </div>

          <div v-if="attachments.length > 0" class="flex flex-wrap gap-2 pt-1">
            <div v-for="(file, idx) in attachments" :key="idx" class="flex items-center gap-2 bg-blue-50 border border-blue-200 text-blue-800 px-3 py-1.5 rounded-lg text-xs font-medium">
              <Icon icon="lucide:file" width="14" />
              <span class="truncate max-w-[180px]">{{ file.name }}</span>
              <button @click="removeFile(idx)" type="button" class="text-blue-600 hover:text-red-600 transition-colors cursor-pointer ml-1">
                <Icon icon="lucide:x" width="14" />
              </button>
            </div>
          </div>
        </div>

        <!-- Single PIN Authorization -->
        <div class="bg-gradient-to-br from-amber-500/10 via-amber-50 to-orange-50 p-5 rounded-2xl border border-amber-200 shadow-inner flex flex-col items-center gap-2.5">
          <div class="flex items-center gap-2 text-amber-900 font-bold text-sm">
            <Icon icon="lucide:key" width="16" class="text-amber-700" />
            <span>Digital Signature PIN</span>
          </div>
          <p class="text-xs text-amber-700/90 text-center">Verify your PIN to stamp initials and digital signature</p>

          <div class="w-full max-w-xs relative mt-1">
            <input 
              type="password" 
              v-model="signPin" 
              placeholder="••••••••" 
              autocomplete="off"
              @keyup.enter="executeSignAndForward"
              class="w-full text-center tracking-widest text-lg font-bold bg-white border-2 border-amber-300 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/20 rounded-xl py-2 px-4 outline-none transition-all text-amber-950 placeholder:text-amber-200 shadow-sm"
            />
            <p v-if="signPinError" class="text-red-500 text-xs font-medium mt-1.5 text-center flex items-center justify-center gap-1">
              <Icon icon="lucide:alert-circle" width="13" />
              {{ signPinError }}
            </p>
          </div>
        </div>

      </div>

      <div class="px-6 py-4 bg-white border-t border-slate-100 flex items-center justify-end gap-3 shrink-0">
        <button @click="openSignModal = false" class="px-4 py-2.5 rounded-xl font-medium text-xs text-slate-600 bg-slate-100 hover:bg-slate-200 cursor-pointer">
          Cancel
        </button>
        <button 
          @click="executeSignAndForward" 
          :disabled="isProcessingSign || !signPin || (selectedSkipTarget && !skipReason)"
          class="px-6 py-2.5 rounded-xl font-bold text-xs text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 cursor-pointer"
        >
          <Icon v-if="isProcessingSign" icon="lucide:loader-2" class="animate-spin" width="16" />
          <Icon v-else icon="lucide:check-check" width="16" />
          <span>{{ isProcessingSign ? 'Submitting...' : submitButtonLabel }}</span>
        </button>
      </div>
    </Dialog>

    <!-- SEPARATE APPROVE SUBORDINATE TASK MODAL -->
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
            v-model="approveRemark" 
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
            v-model="approvePin" 
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
          :disabled="isProcessingApprove"
          class="px-6 py-2.5 rounded-xl font-bold text-xs text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-md hover:shadow-lg disabled:opacity-50 flex items-center gap-2 cursor-pointer"
        >
          <Icon v-if="isProcessingApprove" icon="lucide:loader-2" class="animate-spin" width="16" />
          <Icon v-else icon="mdi:check-decagram" width="16" />
          <span>{{ isProcessingApprove ? 'Approving...' : 'Approve' }}</span>
        </button>
      </div>
    </Dialog>

    <!-- RETURN TASK MODAL -->
    <Dialog v-model:visible="openReturnModal" :closable="false" modal :pt="{
      root: { class: 'bg-white rounded-3xl shadow-2xl max-h-[92vh] flex flex-col w-[95vw] md:w-[650px] border border-gray-100 overflow-hidden' },
      content: { class: 'p-0 flex flex-col overflow-hidden h-full flex-1' },
      header: { class: 'hidden' },
      footer: { class: 'hidden' }
    }">
      <div class="bg-gradient-to-r from-rose-600 to-red-700 px-6 py-5 flex items-center justify-between text-white shrink-0">
        <div class="flex items-center gap-3">
          <div class="w-11 h-11 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center border border-white/30">
            <Icon icon="lucide:corner-up-left" width="22" class="text-white" />
          </div>
          <div>
            <h3 class="text-lg font-bold text-white">Return with Notes</h3>
            <p class="text-rose-100 text-xs">Annotate and route back with review directives</p>
          </div>
        </div>
        <button @click="openReturnModal = false" class="w-8 h-8 flex items-center justify-center rounded-xl bg-white/10 hover:bg-white/20 text-white cursor-pointer">
          <Icon icon="lucide:x" width="18" />
        </button>
      </div>

      <div class="p-6 md:p-7 flex flex-col gap-5 overflow-y-auto max-h-[calc(92vh-160px)] custom-scrollbar bg-slate-50/50">
        
        <!-- Recipient selector -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 space-y-2">
          <label class="text-xs font-bold uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
            <Icon icon="lucide:user" width="14" class="text-rose-600" />
            Return Destination
          </label>
          <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-sm font-semibold text-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
              <span>{{ (props.task?.workflow_version_id && primaryGenericReturnAction?.target_user) ? primaryGenericReturnAction.target_user.name : (chainInfo?.return_target?.name || 'Assigner / Previous Submitter') }}</span>
              <span class="text-xs text-slate-400 font-normal">({{ (props.task?.workflow_version_id && primaryGenericReturnAction?.target_user) ? primaryGenericReturnAction.target_user.role : (chainInfo?.return_target?.role || 'Sender') }})</span>
            </div>
            <span class="text-[11px] font-bold px-2 py-0.5 bg-rose-100 text-rose-800 rounded-md">Default Sender</span>
          </div>
        </div>

        <!-- Return notes / Reason -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-3">
          <label class="text-xs font-bold uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
            <Icon icon="lucide:message-square-dashed" width="14" class="text-rose-600" />
            Revision Notes & Directives (Required)
          </label>
          <textarea 
            v-model="returnReason" 
            rows="4" 
            placeholder="Specify what corrections or amendments are required before resubmission..." 
            class="w-full text-sm border border-slate-200 rounded-xl p-3.5 outline-none focus:border-rose-500 focus:ring-3 focus:ring-rose-500/15 text-slate-800 resize-none"
          ></textarea>
        </div>

        <p class="text-xs text-slate-500 flex items-center gap-1.5 bg-white p-3 rounded-xl border border-slate-200">
          <Icon icon="lucide:info" width="16" class="text-blue-500 shrink-0" />
          Any annotations drawn or typed on the document will be saved and carried over to the recipient.
        </p>
      </div>

      <div class="px-6 py-4 bg-white border-t border-slate-100 flex items-center justify-end gap-3 shrink-0">
        <button @click="openReturnModal = false" class="px-4 py-2.5 rounded-xl font-medium text-xs text-slate-600 bg-slate-100 hover:bg-slate-200 cursor-pointer">
          Cancel
        </button>
        <button 
          @click="executeReturn" 
          :disabled="isProcessingReturn || !returnReason"
          class="px-6 py-2.5 rounded-xl font-bold text-xs text-white bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-700 hover:to-red-700 shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 cursor-pointer"
        >
          <Icon v-if="isProcessingReturn" icon="lucide:loader-2" class="animate-spin" width="16" />
          <Icon v-else icon="lucide:corner-up-left" width="16" />
          <span>{{ isProcessingReturn ? 'Returning...' : 'Return with Notes' }}</span>
        </button>
      </div>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import Dialog from 'primevue/dialog';

const props = defineProps({
  task: { type: Object, required: true },
  pdfEditorRef: { type: Object, default: null },
  isSubordinateUnderReview: { type: Boolean, default: false },
  isSubmissionApproved: { type: Boolean, default: false },
  isTaskSubmitted: { type: Boolean, default: false },
});

const emit = defineEmits(['actionCompleted', 'refresh', 'openAgfReroute']);

const chainInfo = ref(null);
const openSignModal = ref(false);
const openReturnModal = ref(false);
const openApproveOnlyModal = ref(false);
const isJustApproved = ref(false);
const isJustSubmitted = ref(false);

const genericActiveSteps = ref([]);
const genericAvailableActions = ref([]);

const fetchGenericState = async () => {
  if (!props.task?.workflow_version_id || !props.task?.id) return;
  try {
    const res = await axios.get(`/dashboard/generic-workflow/tasks/${props.task.id}/state`);
    if (res.data) {
      genericActiveSteps.value = res.data.active_steps || [];
      genericAvailableActions.value = res.data.available_actions || [];
    }
  } catch (err) {
    console.error('Failed to fetch generic workflow state in SignForwardBar', err);
  }
};

const activeStepName = computed(() => {
  if (genericActiveSteps.value.length > 0) {
    const s = genericActiveSteps.value[0];
    return s.step?.name || s.workflow_step?.name || '';
  }
  return '';
});

const primaryGenericForwardAction = computed(() => {
  if (!genericAvailableActions.value || genericAvailableActions.value.length === 0) return null;
  return genericAvailableActions.value.find(a => !a.is_auto_return && a.action_type !== 'return') || genericAvailableActions.value[0];
});

const primaryGenericReturnAction = computed(() => {
  if (!genericAvailableActions.value || genericAvailableActions.value.length === 0) return null;
  return genericAvailableActions.value.find(a => a.is_auto_return || a.action_type === 'return');
});

const isTaskSubmitted = computed(() => {
  if (isJustSubmitted.value) return true;
  if (props.task?.workflow_version_id) {
    if (genericAvailableActions.value && genericAvailableActions.value.length > 0) {
      return false;
    }
    return true;
  }
  if (props.isTaskSubmitted) return true;
  if (chainInfo.value?.is_task_submitted_by_me) return true;

  const currentUserId = chainInfo.value?.current_user?.id;
  const assignments = props.task?.assignments || [];
  if (currentUserId && assignments.length > 0) {
    const latest = assignments[0];
    const latestAssigneeId = latest.assigned_to?.id || latest.assigned_to;
    if (latestAssigneeId !== currentUserId) {
      const forwardedByMe = (latest.assigned_by?.id || latest.assigned_by) === currentUserId;
      const myCompleted = assignments.some(a => {
        const uid = a.assigned_to?.id || a.assigned_to;
        return uid === currentUserId && a.status === 'completed';
      });
      if (forwardedByMe || myCompleted) {
        return true;
      }
    }
  }

  return false;
});

const isSubmissionApproved = computed(() => {
  if (isJustApproved.value || props.isSubmissionApproved) return true;
  if (props.task?.is_approved) return true;

  const assignments = props.task?.assignments || [];
  if (!assignments || assignments.length === 0) return false;

  const latest = assignments[0];
  const latestStatus = (latest.status || '').toLowerCase();
  const currentUserId = chainInfo.value?.current_user?.id;

  // If the active/latest assignment is currently under review or review,
  // it has NOT been approved yet by the current reviewer holding it!
  if (latestStatus === 'under review' || latestStatus === 'review') {
    return false;
  }

  const latestAssigneeId = latest.assigned_to?.id || latest.assigned_to;
  const latestAssignerId = latest.assigned_by?.id || latest.assigned_by;
  const isRelatedToMe = latestAssigneeId === currentUserId || latestAssignerId === currentUserId || chainInfo.value?.current_user?.is_agf;

  if (isRelatedToMe && latestStatus === 'completed') {
    return true;
  }

  return false;
});

const isReviewAwaitingApproval = computed(() => {
  return (props.isSubordinateUnderReview || chainInfo.value?.is_subordinate_under_review) && !isSubmissionApproved.value;
});

const isReturnLocked = computed(() => {
  if (props.task?.workflow_version_id) {
    if (isTaskSubmitted.value) return true;
    return !primaryGenericReturnAction.value;
  }
  if (props.isTaskSubmitted) return true;
  if (isSubmissionApproved.value) return true;
  return false;
});

const isSubmitLocked = computed(() => {
  if (props.task?.workflow_version_id) {
    if (isTaskSubmitted.value) return true;
    return !primaryGenericForwardAction.value;
  }
  if (props.isTaskSubmitted) return true;
  if (isReviewAwaitingApproval.value) return true;
  return false;
});

const isSkipLocked = computed(() => {
  if (props.isTaskSubmitted) return true;
  if (isReviewAwaitingApproval.value) return true;
  return false;
});

const returnButtonTooltip = computed(() => {
  if (props.isTaskSubmitted) return 'Task has already been submitted';
  if (isSubmissionApproved.value) return 'Task submission has already been approved';
  return 'Return task with revision directives';
});

const submitButtonTooltip = computed(() => {
  if (props.isTaskSubmitted) {
    return 'Task has been submitted to ' + (chainInfo.value?.current_holder?.name || chainInfo.value?.next_approver?.name || 'Assigner');
  }
  if (isReviewAwaitingApproval.value) {
    return 'Task must be approved before submitting';
  }
  return 'Submit task';
});

const skipButtonTooltip = computed(() => {
  if (props.isTaskSubmitted) return 'Task has already been submitted';
  if (isReviewAwaitingApproval.value) return 'Task must be approved before skipping levels';
  return 'Skip approval levels';
});

const signRemark = ref('Approved and submitted as recommended.');
const signPin = ref('');
const signPinError = ref('');
const isProcessingSign = ref(false);
const attachments = ref([]);

const selectedSkipTarget = ref(null);
const skipReason = ref('');

const approveRemark = ref('Subordinate submission approved and verified.');
const approvePin = ref('');
const isProcessingApprove = ref(false);

const returnReason = ref('');
const isProcessingReturn = ref(false);

const signChips = [
  'Approved and submitted as recommended.',
  'Endorsed for directorate clearance.',
  'Cleared for executive consideration.',
  'Minute sheet signed and submitted to assigner.'
];

const targetRole = computed(() => {
  if (selectedSkipTarget.value) {
    return selectedSkipTarget.value.role;
  }
  if (chainInfo.value?.next_approver?.role) {
    return chainInfo.value.next_approver.role;
  }
  if (chainInfo.value?.assigner?.role) {
    return chainInfo.value.assigner.role;
  }
  return null;
});

const submitButtonLabel = computed(() => {
  if (props.task?.workflow_version_id && primaryGenericForwardAction.value) {
    return primaryGenericForwardAction.value.action_name || primaryGenericForwardAction.value.name;
  }
  if (chainInfo.value?.current_user?.is_agf) {
    return 'Final Approval';
  }
  if (targetRole.value) {
    const roleLower = targetRole.value.toLowerCase();
    if (roleLower.includes('super user') || roleLower.includes('accountant-general') || roleLower.includes('auditor-general') || roleLower.includes('super admin')) {
      return 'Submit to AGF';
    }
    if (roleLower.includes('deputy director')) {
      return 'Submit to Deputy Director';
    }
    if (roleLower.includes('assistant director')) {
      return 'Submit to Assistant Director';
    }
    if (roleLower.includes('director')) {
      return 'Submit to Director';
    }
    return `Submit to ${targetRole.value}`;
  }
  return 'Submit to Assigner';
});

const modalTitle = computed(() => {
  if (props.task?.workflow_version_id && primaryGenericForwardAction.value) {
    return primaryGenericForwardAction.value.action_name || primaryGenericForwardAction.value.name;
  }
  if (chainInfo.value?.current_user?.is_agf) {
    return 'Executive Sign-Off & Final Approval';
  }
  if (selectedSkipTarget.value) {
    return `Skip Level & Submit to ${selectedSkipTarget.value.role}`;
  }
  return submitButtonLabel.value;
});

const hasSkipTargets = computed(() => {
  return chainInfo.value?.skip_targets && chainInfo.value.skip_targets.length > 0;
});

const isSubordinateUnderReview = computed(() => {
  if (props.isSubordinateUnderReview) return true;
  if (!props.task || !props.task.assignments) return false;
  const currentUserId = chainInfo.value?.current_user?.id;
  if (!currentUserId) return false;
  const isAgf = chainInfo.value?.current_user?.is_agf;
  if (isAgf && (props.task.status === 'under review' || props.task.assignments.some(a => a.status === 'under review'))) return true;
  return props.task.assignments.some(a => {
    const isAssigner = a.assigned_by?.id === currentUserId || a.assigned_by === currentUserId;
    const isAssignee = a.assigned_to?.id === currentUserId || a.assigned_to === currentUserId;
    return (isAssigner || isAssignee) && (a.status === 'under review' || a.status === 'review');
  });
});

const shouldShowBar = computed(() => {
  if (!props.task) return false;
  if (props.task.status === 'completed' || props.task.status === 'closed') return false;
  if (props.task.workflow_version_id) {
    return true;
  }
  if (!chainInfo.value) return false;
  if (isTaskSubmitted.value) return true;
  if (!chainInfo.value.is_task_currently_with_me && !chainInfo.value.current_user?.is_agf) return false;
  return true;
});

const fetchChainInfo = async () => {
  try {
    const res = await axios.get(`/dashboard/tasks/${props.task.id}/chain-info`);
    chainInfo.value = res.data;
  } catch (err) {
    console.error('Failed to fetch chain info:', err);
  }
};

onMounted(() => {
  fetchChainInfo();
  fetchGenericState();
});

watch(() => props.task?.id, () => {
  fetchChainInfo();
  fetchGenericState();
});

const openSkipLevelDialog = () => {
  if (hasSkipTargets.value) {
    selectedSkipTarget.value = chainInfo.value.skip_targets[0];
    openSignModal.value = true;
  }
};

const handleFiles = (e) => {
  const selected = Array.from(e.target.files || []);
  attachments.value.push(...selected);
};

const removeFile = (idx) => {
  attachments.value.splice(idx, 1);
};

const executeApproveOnly = async () => {
  isProcessingApprove.value = true;
  try {
    const res = await axios.post(`/dashboard/tasks/${props.task.id}/approve-submission`, {
      remark: approveRemark.value,
      pin: approvePin.value || null,
    });

    if (res.data?.success) {
      isJustApproved.value = true;
      fetchChainInfo();
      Swal.fire({
        icon: 'success',
        title: 'Task Approved',
        text: res.data.message,
        timer: 2500,
        showConfirmButton: false,
      });
      openApproveOnlyModal.value = false;
      approvePin.value = '';
      emit('actionCompleted');
      emit('refresh');
    }
  } catch (err) {
    Swal.fire({
      icon: 'error',
      title: 'Approval Failed',
      text: err.response?.data?.message || 'Could not approve task.',
    });
  } finally {
    isProcessingApprove.value = false;
  }
};

const executeSignAndForward = async () => {
  if (!signPin.value) {
    signPinError.value = 'Please enter your digital signature PIN.';
    return;
  }

  // Generic Workflow Action execution
  if (props.task?.workflow_version_id && primaryGenericForwardAction.value) {
    isProcessingSign.value = true;
    signPinError.value = '';
    try {
      const formData = new FormData();
      formData.append('task_id', props.task.id);
      formData.append('transition_ids[]', primaryGenericForwardAction.value.id);
      formData.append('comments', signRemark.value || 'Submitted for consideration.');
      formData.append('pin', signPin.value);
      attachments.value.forEach((file) => {
        formData.append('attachments[]', file);
      });

      const res = await axios.post('/dashboard/generic-workflow/execute-action', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });

      isJustSubmitted.value = true;
      openSignModal.value = false;
      signPin.value = '';
      attachments.value = [];
      await fetchGenericState();

      Swal.fire({
        icon: 'success',
        title: 'Action Executed',
        text: res.data?.message || 'Workflow action executed successfully.',
        timer: 2500,
        showConfirmButton: false
      });

      emit('actionCompleted');
      emit('refresh');
      window.location.reload();
      return;
    } catch (err) {
      signPinError.value = err.response?.data?.message || 'An error occurred during workflow action.';
      return;
    } finally {
      isProcessingSign.value = false;
    }
  }

  if (selectedSkipTarget.value && !skipReason.value) {
    signPinError.value = 'Skip reason is mandatory when skipping an approval level.';
    return;
  }

  isProcessingSign.value = true;
  signPinError.value = '';

  let overlays = [];
  if (props.pdfEditorRef && props.pdfEditorRef.allOverlays) {
    overlays = props.pdfEditorRef.allOverlays;
  }

  try {
    const formData = new FormData();
    formData.append('pin', signPin.value);
    formData.append('remark', signRemark.value || '');
    if (selectedSkipTarget.value) {
      formData.append('skip_target_id', selectedSkipTarget.value.id);
      formData.append('skip_reason', skipReason.value || '');
    }
    if (overlays && overlays.length > 0) {
      formData.append('overlays', JSON.stringify(overlays));
    }
    attachments.value.forEach((file) => {
      formData.append('attachments[]', file);
    });

    const res = await axios.post(`/dashboard/tasks/${props.task.id}/sign-forward`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    if (res.data?.success) {
      isJustSubmitted.value = true;
      openSignModal.value = false;
      signPin.value = '';
      selectedSkipTarget.value = null;
      skipReason.value = '';
      attachments.value = [];
      fetchChainInfo();

      Swal.fire({
        icon: 'success',
        title: chainInfo.value?.current_user?.is_agf ? 'Final Approval Granted' : 'Submitted',
        text: res.data.message,
        timer: 2500,
        showConfirmButton: false
      });

      emit('actionCompleted');
      emit('refresh');
    } else {
      signPinError.value = res.data?.message || 'Action failed.';
    }
  } catch (err) {
    signPinError.value = err.response?.data?.message || 'An error occurred during submission.';
  } finally {
    isProcessingSign.value = false;
  }
};

const executeReturn = async () => {
  if (!returnReason.value) return;

  if (props.task?.workflow_version_id && primaryGenericReturnAction.value) {
    isProcessingReturn.value = true;
    try {
      const formData = new FormData();
      formData.append('task_id', props.task.id);
      formData.append('transition_ids[]', primaryGenericReturnAction.value.id);
      formData.append('comments', returnReason.value);

      const res = await axios.post('/dashboard/generic-workflow/execute-action', formData);

      openReturnModal.value = false;
      returnReason.value = '';
      await fetchGenericState();

      Swal.fire({
        icon: 'success',
        title: 'Task Returned',
        text: res.data?.message || 'Task returned successfully.',
        timer: 2500,
        showConfirmButton: false
      });

      emit('actionCompleted');
      emit('refresh');
      window.location.reload();
      return;
    } catch (err) {
      Swal.fire({
        icon: 'error',
        title: 'Return Failed',
        text: err.response?.data?.message || 'Could not return task.'
      });
      return;
    } finally {
      isProcessingReturn.value = false;
    }
  }

  isProcessingReturn.value = true;

  let annotations = [];
  if (props.pdfEditorRef && props.pdfEditorRef.allOverlays) {
    const overlaysByPage = props.pdfEditorRef.allOverlays;
    for (const [page, items] of Object.entries(overlaysByPage)) {
      if (Array.isArray(items)) {
        items.forEach(item => {
          annotations.push({
            page: parseInt(page),
            x: item.x || 0,
            y: item.y || 0,
            text: item.text || item.comment || 'Revision note'
          });
        });
      }
    }
  }

  try {
    const payload = {
      target_user_id: chainInfo.value?.return_target?.id || null,
      reason: returnReason.value,
      annotations: annotations
    };

    const res = await axios.post(`/dashboard/tasks/${props.task.id}/return`, payload);

    if (res.data?.success) {
      Swal.fire({
        icon: 'info',
        title: 'Task Returned',
        text: res.data.message,
        timer: 2500,
        showConfirmButton: false
      });
      openReturnModal.value = false;
      returnReason.value = '';
      emit('actionCompleted');
      emit('refresh');
    }
  } catch (err) {
    Swal.fire({
      icon: 'error',
      title: 'Return Failed',
      text: err.response?.data?.message || 'Could not return task.'
    });
  } finally {
    isProcessingReturn.value = false;
  }
};
</script>
