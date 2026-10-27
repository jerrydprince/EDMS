<template>
  <main class="p-4 lg:p-7">
    <div class="mb-4">
      <Link href="/dashboard/workflow-builder" class="text-blue-600 hover:underline flex items-center gap-1">
        <Icon icon="lucide:arrow-left" width="16" /> Back to Workflow Builder
      </Link>
    </div>

    <section class="flex flex-col md:flex-row items-start md:items-center justify-between">
      <div>
        <p class="text-3xl text-gray-800 font-semibold flex items-center gap-2">
          {{ version.workflow.emoji }} {{ version.workflow.title }} - Version {{ version.version_number }}
          <span v-if="version.workflow.is_confidential" class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-bold uppercase flex items-center gap-1">
            <Icon icon="lucide:lock" width="12" /> Confidential
          </span>
          <span v-if="version.is_published" class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold uppercase flex items-center gap-1">
            <Icon icon="lucide:check-circle-2" width="12" /> Published
          </span>
          <span v-else-if="version.is_approved" class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-bold uppercase flex items-center gap-1">
            <Icon icon="lucide:thumbs-up" width="12" /> Approved
          </span>
          <span v-else class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-bold uppercase flex items-center gap-1">
            <Icon icon="lucide:edit-3" width="12" /> Draft
          </span>
        </p>
        <p class="text-gray-500 mt-1">Configure the steps and routing for this workflow version.</p>
      </div>
      <div class="mt-4 md:mt-0 flex gap-4 items-center border border-gray-200 px-4 py-2 rounded-lg bg-gray-50">
        <label class="flex items-center gap-2 cursor-pointer text-sm font-medium text-gray-700">
          <input type="checkbox" v-model="mandateParallelSplits" @change="updateParallelSplits" class="rounded text-blue-600 focus:ring-blue-500 w-4 h-4 cursor-pointer">
          Mandate Parallel Splits
        </label>
        <div class="h-6 w-px bg-gray-300 mx-1"></div>
        
        <button v-if="!version.is_published && !version.is_approved" @click="openApproveModal" class="flex gap-2 items-center text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-md py-1.5 px-4 cursor-pointer transition-colors">
          <Icon icon="lucide:check-square" width="18" />
          <p>Approve Version</p>
        </button>
        
        <button  @click="publishVersion" :disabled="!version.is_approved" :class="{'opacity-50 cursor-not-allowed': !version.is_approved}" class="flex gap-2 items-center text-white bg-green-600 hover:bg-green-700 border-transparent border-solid border rounded-md py-1.5 px-4 cursor-pointer transition-colors" :title="!version.is_approved ? 'Version must be approved before publishing' : ''">
          <Icon icon="lucide:check-circle" width="18" />
          <p>Publish Version</p>
        </button>

        <button v-if="version.is_published" @click="openRollbackModal" class="flex gap-2 items-center text-red-700 bg-red-50 hover:bg-red-100 border border-red-200 rounded-md py-1.5 px-4 cursor-pointer transition-colors">
          <Icon icon="lucide:rotate-ccw" width="18" />
          <p>Rollback</p>
        </button>

        <form v-if="version.is_published" @submit.prevent="cloneToDraft">
          <button type="submit" class="flex gap-2 items-center text-white bg-blue-600 hover:bg-blue-700 border-transparent border-solid border rounded-md py-1.5 px-4 cursor-pointer transition-colors">
            <Icon icon="lucide:copy" width="18" />
            <p>Clone to Draft</p>
          </button>
        </form>
      </div>
    </section>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
      
      <!-- Steps Column -->
      <div class="border border-solid border-gray-300 rounded-lg bg-white p-4 shadow-sm">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-semibold text-gray-700 flex items-center gap-2">
            <Icon icon="lucide:layers" width="22" /> Steps
          </h2>
          <button @click="openStepModal" class="text-sm bg-blue-50 text-blue-600 px-3 py-1.5 rounded-md hover:bg-blue-100 font-medium">
            + Add Step
          </button>
        </div>
        
        <div v-if="version.steps && version.steps.length > 0" class="flex flex-col gap-3">
          <div v-for="step in version.steps" :key="step.id" class="border border-gray-200 p-3 rounded-lg flex justify-between items-center bg-gray-50 hover:bg-white transition-colors">
            <div>
              <div class="flex items-center gap-2">
                <p class="font-semibold text-gray-800">{{ step.name }}</p>
                <span v-if="step.is_start_step" class="text-[10px] bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-bold">START</span>
                <span v-if="step.is_end_step" class="text-[10px] bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-bold">END</span>
              </div>
              <p class="text-xs text-gray-500 mt-1 capitalize">{{ step.step_type }} • Role: {{ step.role_required || 'Any' }}</p>
            </div>
            <div class="flex items-center gap-1">
              <button @click="openChecklistModal(step)" class="text-xs flex items-center gap-1 bg-white border border-gray-300 px-2 py-1 rounded text-gray-600 hover:bg-gray-50 hover:text-blue-600 transition-colors">
                <Icon icon="lucide:check-square" width="14" /> Checklist
              </button>
              <button @click="openFormModal(step)" class="text-xs flex items-center gap-1 bg-white border border-gray-300 px-2 py-1 rounded text-gray-600 hover:bg-gray-50 hover:text-blue-600 transition-colors">
                <Icon icon="lucide:file-text" width="14" /> Form
              </button>
              <button @click="openSlaModal(step)" class="text-xs flex items-center gap-1 bg-white border border-gray-300 px-2 py-1 rounded text-gray-600 hover:bg-gray-50 hover:text-blue-600 transition-colors">
                <Icon icon="lucide:clock" width="14" /> SLA
              </button>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-8 text-gray-500">
          No steps defined. Add a start step to begin.
        </div>
      </div>

      <!-- Transitions Column -->
      <div class="border border-solid border-gray-300 rounded-lg bg-white p-4 shadow-sm">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-semibold text-gray-700 flex items-center gap-2">
            <Icon icon="lucide:arrow-right-circle" width="22" /> Transitions
          </h2>
          <button @click="openTransitionModal" class="text-sm bg-blue-50 text-blue-600 px-3 py-1.5 rounded-md hover:bg-blue-100 font-medium">
            + Add Transition
          </button>
        </div>
        
        <div v-if="version.transitions && version.transitions.length > 0" class="flex flex-col gap-3">
          <div v-for="trans in version.transitions" :key="trans.id" class="border border-gray-200 p-3 rounded-lg flex flex-col gap-1 bg-gray-50 hover:bg-white transition-colors">
            <div class="flex justify-between items-center">
              <span class="text-sm font-semibold text-gray-700">{{ trans.from_step ? trans.from_step.name : 'Unknown' }}</span>
              <Icon icon="lucide:arrow-right" class="text-gray-400" width="16" />
              <span class="text-sm font-semibold text-blue-700">{{ trans.to_step ? trans.to_step.name : 'Unknown' }}</span>
              <button @click="openConditionModal(trans)" class="text-xs flex items-center gap-1 bg-white border border-gray-300 px-2 py-1 rounded text-gray-600 hover:bg-gray-50 hover:text-blue-600 transition-colors">
                <Icon icon="lucide:filter" width="14" /> Conditions
              </button>
            </div>
            <div class="text-xs text-gray-500 text-center mt-1 bg-white border border-gray-100 py-1 rounded inline-block">
              Action: <span class="font-bold text-gray-800">{{ trans.action_name }}</span>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-8 text-gray-500">
          No transitions defined. Link your steps together.
        </div>
      </div>
      
    </div>

    <!-- Step Modal -->
    <Dialog v-model:visible="isStepModalVisible" header="Add Step" :style="{ width: '40vw' }" :breakpoints="{ '960px': '75vw', '641px': '100vw' }">
      <form @submit.prevent="submitStepForm" class="flex flex-col gap-4 mt-2">
        <div class="flex flex-col gap-2">
          <label class="font-medium text-gray-700">Step Name *</label>
          <input v-model="stepForm.name" type="text" class="border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500" placeholder="e.g. HOD Review" required />
        </div>
        <div class="flex flex-col gap-2">
          <label class="font-medium text-gray-700">Step Type *</label>
          <select v-model="stepForm.step_type" class="border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500" required>
            <option value="approval">Approval</option>
            <option value="review">Review</option>
            <option value="input">Data Input</option>
            <option value="automated">Automated Action</option>
            <option value="assign">Assign</option>
            <option value="start">Start</option>
            <option value="end">End</option>
          </select>
        </div>
        <div class="flex flex-col gap-2">
          <label class="font-medium text-gray-700">Required Role</label>
          <select v-model="stepForm.role_required" class="border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500 w-full">
              <option value="">Any Role (None)</option>
              <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
          </select>
        </div>
        <div class="flex flex-col gap-2">
          <label class="font-medium text-gray-700">Department Lock</label>
          <select v-model="stepForm.department_id" class="border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500">
            <option :value="null">Any Department</option>
            <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.title }}</option>
          </select>
        </div>
        <div class="flex gap-4 mt-2 flex-wrap">
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" v-model="stepForm.is_start_step" class="rounded text-blue-600">
            <span class="text-sm font-medium">Is Start Step</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" v-model="stepForm.is_end_step" class="rounded text-blue-600">
            <span class="text-sm font-medium">Is End Step</span>
          </label>
          <label v-if="stepForm.step_type === 'review' || stepForm.step_type === 'approval'" class="flex items-center gap-2 cursor-pointer mt-2 w-full text-amber-600">
            <input type="checkbox" v-model="stepForm.auto_return_on_rejection" class="rounded text-amber-600">
            <span class="text-sm font-medium">If rejected, automatically return to previous step requiring a comment</span>
          </label>
        </div>
        <div class="flex justify-end gap-3 mt-4">
          <button type="button" @click="isStepModalVisible = false" class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
          <button type="submit" :disabled="stepForm.processing" class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
            Save Step
          </button>
        </div>
      </form>
    </Dialog>

    <!-- Transition Modal -->
    <Dialog v-model:visible="isTransitionModalVisible" header="Add Transition" :style="{ width: '40vw' }" :breakpoints="{ '960px': '75vw', '641px': '100vw' }">
      <form @submit.prevent="submitTransitionForm" class="flex flex-col gap-4 mt-2">
        <div class="flex flex-col gap-2">
          <label class="font-medium text-gray-700">From Step *</label>
          <select v-model="transitionForm.from_step_id" class="border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500" required>
            <option :value="null" disabled>Select Step</option>
            <option v-for="step in version.steps" :key="step.id" :value="step.id">{{ step.name }}</option>
          </select>
        </div>
        <div class="flex flex-col gap-2">
          <label class="font-medium text-gray-700">To Step *</label>
          <select v-model="transitionForm.to_step_id" class="border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500" required>
            <option :value="null" disabled>Select Step</option>
            <option v-for="step in version.steps" :key="step.id" :value="step.id">{{ step.name }}</option>
          </select>
        </div>
        <div class="flex flex-col gap-2">
          <label class="font-medium text-gray-700">Action Name (Trigger) *</label>
          <select v-model="transitionForm.action_name" class="border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500" required>
            <option value="" disabled>Select Action Name</option>
            <option value="Approve">Approve</option>
            <option value="Reject">Reject</option>
            <option value="Submit">Submit</option>
            <option value="Review">Review</option>
            <option value="Return">Return</option>
            <option value="Assign">Assign</option>
          </select>
        </div>
        <div class="flex justify-end gap-3 mt-4">
          <button type="button" @click="isTransitionModalVisible = false" class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
          <button type="submit" :disabled="transitionForm.processing" class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
            Save Transition
          </button>
        </div>
      </form>
    </Dialog>
    
    <!-- SLA Config Modal -->
    <Dialog v-model:visible="isSlaModalVisible" header="Configure SLA Rule" :style="{ width: '40vw' }" :breakpoints="{ '960px': '75vw', '641px': '100vw' }">
      <form @submit.prevent="submitSlaForm" class="flex flex-col gap-4 mt-2">
        <div class="flex flex-col gap-2">
          <label class="font-medium text-gray-700">Target Duration (Hours) *</label>
          <input v-model="slaForm.duration_hours" type="number" min="1" class="border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500" required />
          <p class="text-xs text-gray-500">The total amount of time allowed for this step before it is considered breached.</p>
        </div>

        <div class="flex flex-col gap-2">
          <label class="font-medium text-gray-700">Warning Threshold (Hours)</label>
          <input v-model="slaForm.warning_hours" type="number" min="1" class="border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500" />
          <p class="text-xs text-gray-500">Send a warning email this many hours before the breach.</p>
        </div>

        <div class="flex gap-4 mt-2">
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" v-model="slaForm.is_working_hours_only" class="rounded text-blue-600">
            <span class="text-sm font-medium">Respect Working Calendar</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" v-model="slaForm.requires_pause_approval" class="rounded text-blue-600">
            <span class="text-sm font-medium">Requires Pause Approval</span>
          </label>
        </div>

        <div class="border-t border-gray-200 mt-2 pt-4">
          <h4 class="font-semibold text-gray-800 mb-2">Escalation Policy</h4>
          
          <div class="flex flex-col gap-2 mb-3">
            <label class="font-medium text-gray-700">Action on Breach</label>
            <select v-model="slaForm.escalation_action" class="border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="">None (Just mark as breached)</option>
              <option value="reassign">Re-assign to Escalation Role</option>
            </select>
          </div>

          <div v-if="slaForm.escalation_action === 'reassign'" class="flex flex-col gap-2">
            <label class="font-medium text-gray-700">Escalation Role Name</label>
            <input v-model="slaForm.escalation_role" type="text" class="border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500" placeholder="e.g. Director" />
            <label class="flex items-center gap-2 cursor-pointer mt-2">
              <input type="checkbox" v-model="slaForm.remove_original_assignee" class="rounded text-blue-600">
              <span class="text-sm font-medium">Remove Original Assignee(s)</span>
            </label>
          </div>
        </div>

        <div class="flex justify-end gap-3 mt-4">
          <button type="button" @click="isSlaModalVisible = false" class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
          <button type="submit" :disabled="slaForm.processing" class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
            Save SLA Configuration
          </button>
        </div>
      </form>
    </Dialog>
    
    <!-- Checklist Modal -->
    <Dialog v-model:visible="isChecklistModalVisible" header="Manage Checklist" modal :style="{ width: '40vw' }" :breakpoints="{ '960px': '75vw', '641px': '100vw' }">
      <form @submit.prevent="submitChecklistForm" class="flex flex-col gap-4 mt-2" >
        <div class="flex flex-col gap-2">
          <label class="font-medium text-gray-700">Checklist Item *</label>
          <input v-model="checklistForm.item_text" type="text" class="border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500" placeholder="e.g. Verify Signature" required />
        </div>
        <div class="flex items-center gap-2 mt-2">
          <input type="checkbox" v-model="checklistForm.is_required" class="rounded text-blue-600 cursor-pointer w-4 h-4">
          <span class="text-sm font-medium">Is Required?</span>
        </div>
        <div class="flex justify-end gap-3 mt-4">
          <button type="submit" :disabled="checklistForm.processing" class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
            Add Checklist Item
          </button>
        </div>
      </form>
      
    </Dialog>

    <!-- Condition Modal -->
    <Dialog v-model:visible="isConditionModalVisible" header="Manage Routing Conditions" modal :style="{ width: '40vw' }" :breakpoints="{ '960px': '75vw', '641px': '100vw' }">
      <form @submit.prevent="submitConditionForm" class="flex flex-col gap-4 mt-2" >
        <div class="flex flex-col gap-2">
          <label class="font-medium text-gray-700">Field Name *</label>
          <input v-model="conditionForm.field_name" type="text" class="border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500" placeholder="e.g. amount" required />
        </div>
        <div class="flex flex-col gap-2">
          <label class="font-medium text-gray-700">Operator *</label>
          <select v-model="conditionForm.operator" class="border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500" required>
            <option value="==">== (Equals)</option>
            <option value="!=">!= (Not Equals)</option>
            <option value=">">&gt; (Greater Than)</option>
            <option value="<">&lt; (Less Than)</option>
            <option value=">=">&gt;= (Greater or Equal)</option>
            <option value="<=">&lt;= (Less or Equal)</option>
          </select>
        </div>
        <div class="flex flex-col gap-2">
          <label class="font-medium text-gray-700">Value *</label>
          <input v-model="conditionForm.value" type="text" class="border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500" placeholder="e.g. 50000" required />
        </div>
        <div class="flex justify-end gap-3 mt-4">
          <button type="submit" :disabled="conditionForm.processing" class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
            Add Condition
          </button>
        </div>
      </form>
      
    </Dialog>

    <!-- Approval Modal -->
    <Dialog v-model:visible="isApproveModalVisible" header="Approve Workflow Version" :style="{ width: '30vw' }" :breakpoints="{ '960px': '75vw', '641px': '100vw' }">
      <form @submit.prevent="submitApproveForm" class="flex flex-col gap-4 mt-2">
        <p class="text-gray-600 text-sm">Please enter your digital signature PIN to formally approve this workflow configuration.</p>
        <div class="flex flex-col gap-2">
          <label class="font-medium text-gray-700">Digital Signature PIN *</label>
          <input v-model="approveForm.approval_pin" type="password" class="border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500" required />
          <span v-if="approveForm.errors.approval_pin" class="text-red-500 text-sm">{{ approveForm.errors.approval_pin }}</span>
        </div>
        <div class="flex justify-end gap-3 mt-4">
          <button type="button" @click="isApproveModalVisible = false" class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
          <button type="submit" :disabled="approveForm.processing" class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
            Sign & Approve
          </button>
        </div>
      </form>
    </Dialog>

    <!-- Rollback Modal -->
    <Dialog v-model:visible="isRollbackModalVisible" header="Emergency Rollback" :style="{ width: '40vw' }" :breakpoints="{ '960px': '75vw', '641px': '100vw' }">
      <form @submit.prevent="submitRollbackForm" class="flex flex-col gap-4 mt-2">
        <div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded-lg text-sm flex gap-2">
          <Icon icon="lucide:alert-triangle" width="20" class="shrink-0 mt-0.5" />
          <p><strong>Warning:</strong> You are about to unpublish the current version and revert to a historically approved version. This action is logged as critical.</p>
        </div>
        <div class="flex flex-col gap-2">
          <label class="font-medium text-gray-700">Select Version to Restore *</label>
          <select v-model="rollbackForm.rollback_to_version_id" class="border border-gray-300 rounded-lg p-2 focus:ring-red-500 focus:border-red-500" required>
            <option :value="null" disabled>Select previous version...</option>
            <option v-for="prevVersion in previousVersions" :key="prevVersion.id" :value="prevVersion.id">
              Version {{ prevVersion.version_number }} (Approved: {{ new Date(prevVersion.approved_at).toLocaleDateString() }})
            </option>
          </select>
          <span v-if="rollbackForm.errors.rollback_to_version_id" class="text-red-500 text-sm">{{ rollbackForm.errors.rollback_to_version_id }}</span>
        </div>
        <div class="flex justify-end gap-3 mt-4">
          <button type="button" @click="isRollbackModalVisible = false" class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
          <button type="submit" :disabled="rollbackForm.processing || !rollbackForm.rollback_to_version_id" class="px-4 py-2 text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50 flex items-center gap-2">
            <Icon icon="lucide:rotate-ccw" width="16" /> Execute Rollback
          </button>
        </div>
      </form>
    </Dialog>
    
    <!-- Form Schema Builder Modal -->
    <Dialog v-model:visible="isFormModalVisible" header="Configure Step Form" modal :style="{ width: '50vw' }" :breakpoints="{ '960px': '75vw', '641px': '100vw' }">
      <div class="py-4">
        <p class="text-sm text-gray-600 mb-4">Define custom fields that users must fill out when completing this step.</p>
        
        <div class="mb-4 border border-gray-200 rounded p-4 bg-gray-50 flex gap-2 items-end">
          <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-1">Field Label</label>
            <input v-model="newFormField.label" type="text" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border" placeholder="e.g. Comments">
          </div>
          <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-1">Field Type</label>
            <select v-model="newFormField.type" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border">
              <option value="text">Text Input</option>
              <option value="textarea">Long Text (Textarea)</option>
              <option value="number">Number</option>
              <option value="date">Date</option>
              <option value="file">File Upload</option>
            </select>
          </div>
          <div class="flex items-center pb-2">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="newFormField.required" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
              <span class="text-sm text-gray-700">Required</span>
            </label>
          </div>
          <button type="button" @click="addFormField" class="bg-blue-600 text-white px-3 py-2 rounded shadow hover:bg-blue-700 flex items-center">
            <Icon icon="lucide:plus" width="18" /> Add
          </button>
        </div>

        <div v-if="formSchemaForm.form_schema && formSchemaForm.form_schema.length > 0" class="flex flex-col gap-2">
          <div v-for="(field, index) in formSchemaForm.form_schema" :key="index" class="flex justify-between items-center bg-white border border-gray-200 p-3 rounded shadow-sm">
            <div>
              <span class="font-semibold text-gray-800">{{ field.label }}</span>
              <span class="text-xs text-gray-500 ml-2 bg-gray-100 px-2 py-0.5 rounded">{{ field.type }}</span>
              <span v-if="field.required" class="text-xs text-red-500 ml-2 font-medium">Required</span>
            </div>
            <button type="button" @click="removeFormField(index)" class="text-red-500 hover:text-red-700 p-1">
              <Icon icon="lucide:trash-2" width="16" />
            </button>
          </div>
        </div>
        <div v-else class="text-center py-6 text-gray-400 bg-gray-50 rounded border border-dashed border-gray-300">
          No form fields added yet.
        </div>
      </div>
      <template #footer>
        <button @click="isFormModalVisible = false" class="px-4 py-2 text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200 font-medium mr-2">Cancel</button>
        <button @click="submitFormSchema" :disabled="formSchemaForm.processing" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">Save Form Schema</button>
      </template>
    </Dialog>

  </main>
</template>

<script>
import Layout from "../../../Shared/Layout.vue";
export default {
  layout: Layout
}
</script>

<script setup>
import Swal from 'sweetalert2';

import { ref } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import Dialog from 'primevue/dialog';

const props = defineProps({
  version: Object,
  departments: Array,
  previousVersions: Array,
  roles: Array
});

const isStepModalVisible = ref(false);
const isTransitionModalVisible = ref(false);
const isSlaModalVisible = ref(false);
const isChecklistModalVisible = ref(false);
const isConditionModalVisible = ref(false);
const isFormModalVisible = ref(false);
const isApproveModalVisible = ref(false);
const isRollbackModalVisible = ref(false);

const currentChecklistStepId = ref(null);
const currentConditionTransitionId = ref(null);
const currentFormStepId = ref(null);
const currentSlaStepId = ref(null);

const mandateParallelSplits = ref(props.version.mandate_parallel_splits ? true : false);

const stepForm = useForm({
  name: '',
  step_type: 'approval',
  role_required: '',
  department_id: null,
  is_start_step: false,
  is_end_step: false,
  auto_return_on_rejection: false,
  form_schema: null,
  notification_rules: null
});

const transitionForm = useForm({
  from_step_id: null,
  to_step_id: null,
  action_name: '',
  notification_rules: null
});

const slaForm = useForm({
  duration_hours: '',
  warning_hours: '',
  is_working_hours_only: false,
  escalation_action: '',
  escalation_role: '',
  remove_original_assignee: false,
  requires_pause_approval: false
});

const checklistForm = useForm({
  item_text: '',
  is_required: true
});

const formSchemaForm = useForm({
  form_schema: []
});

const newFormField = ref({
  label: '',
  type: 'text',
  required: false
});

const conditionForm = useForm({
  field_name: '',
  operator: '==',
  value: ''
});

const approveForm = useForm({
  approval_pin: ''
});

const rollbackForm = useForm({
  rollback_to_version_id: null
});

const openStepModal = () => {
  stepForm.reset();
  stepForm.clearErrors();
  isStepModalVisible.value = true;
};

const openTransitionModal = () => {
  transitionForm.reset();
  transitionForm.clearErrors();
  isTransitionModalVisible.value = true;
};

const openSlaModal = (step) => {
  currentSlaStepId.value = step.id;
  slaForm.reset();
  slaForm.clearErrors();
  if (step.sla) {
    slaForm.duration_hours = step.sla.duration_hours || '';
    slaForm.warning_hours = step.sla.warning_hours || '';
    slaForm.is_working_hours_only = step.sla.is_working_hours_only || false;
    slaForm.escalation_action = step.sla.escalation_action || '';
    slaForm.escalation_role = step.sla.escalation_role || '';
    slaForm.remove_original_assignee = step.sla.remove_original_assignee || false;
    slaForm.requires_pause_approval = step.sla.requires_pause_approval || false;
  }
  isSlaModalVisible.value = true;
};

const openChecklistModal = (step) => {
  currentChecklistStepId.value = step.id;
  checklistForm.reset();
  isChecklistModalVisible.value = true;
};

const openConditionModal = (trans) => {
  currentConditionTransitionId.value = trans.id;
  conditionForm.reset();
  isConditionModalVisible.value = true;
};

const openFormModal = (step) => {
  currentFormStepId.value = step.id;
  
  if (step.form_schema) {
    try {
      formSchemaForm.form_schema = typeof step.form_schema === 'string' ? JSON.parse(step.form_schema) : step.form_schema;
    } catch (e) {
      formSchemaForm.form_schema = [];
    }
  } else {
    formSchemaForm.form_schema = [];
  }
  
  isFormModalVisible.value = true;
};

const addFormField = () => {
  if (!newFormField.value.label) return;
  formSchemaForm.form_schema = [...formSchemaForm.form_schema, { ...newFormField.value }];
  newFormField.value.label = '';
  newFormField.value.required = false;
  newFormField.value.type = 'text';
};

const removeFormField = (index) => {
  formSchemaForm.form_schema = formSchemaForm.form_schema.filter((_, i) => i !== index);
};

const submitFormSchema = () => {
  if (!currentFormStepId.value) return;
  formSchemaForm.post(`/dashboard/workflow-builder/step/${currentFormStepId.value}/form`, {
    onSuccess: () => {
      isFormModalVisible.value = false;
      formSchemaForm.reset();
    }
  });
};

const submitStepForm = () => {
  stepForm.post(`/dashboard/workflow-builder/version/${props.version.id}/steps`, {
    onSuccess: () => {
      isStepModalVisible.value = false;
      stepForm.reset();
    }
  });
};

const submitTransitionForm = () => {
  transitionForm.post(`/dashboard/workflow-builder/version/${props.version.id}/transitions`, {
    onSuccess: () => {
      isTransitionModalVisible.value = false;
      transitionForm.reset();
    }
  });
};

const submitSlaForm = () => {
  if (!currentSlaStepId.value) return;
  slaForm.post(`/dashboard/workflow-builder/step/${currentSlaStepId.value}/sla`, {
    onSuccess: () => {
      isSlaModalVisible.value = false;
      slaForm.reset();
    }
  });
};

const submitChecklistForm = () => {
  if (!currentChecklistStepId.value) return;
  checklistForm.post(`/dashboard/workflow-builder/step/${currentChecklistStepId.value}/checklist`, {
    onSuccess: () => {
      isChecklistModalVisible.value = false;
      checklistForm.reset();
    }
  });
};

const submitConditionForm = () => {
  if (!currentConditionTransitionId.value) return;
  conditionForm.post(`/dashboard/workflow-builder/transition/${currentConditionTransitionId.value}/condition`, {
    onSuccess: () => {
      isConditionModalVisible.value = false;
      conditionForm.reset();
    }
  });
};

const publishVersion = () => {
  Swal.fire({
    title: 'Confirm',
    text: 'Are you sure you want to publish this version? Active instances of the previous version will continue using their respective definitions, but all new instances will use this version.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes',
    cancelButtonText: 'No'
  }).then((result) => {
    if (result.isConfirmed) {
    router.post(`/dashboard/workflow-builder/version/${props.version.id}/publish`, {
      mandate_parallel_splits: mandateParallelSplits.value
    });
  }
  });
};

const openApproveModal = () => {
  approveForm.reset();
  approveForm.clearErrors();
  isApproveModalVisible.value = true;
};

const openRollbackModal = () => {
  rollbackForm.reset();
  rollbackForm.clearErrors();
  isRollbackModalVisible.value = true;
};

const submitApproveForm = () => {
  approveForm.post(`/dashboard/workflow-builder/version/${props.version.id}/approve`, {
    onSuccess: () => {
      isApproveModalVisible.value = false;
    }
  });
};

const submitRollbackForm = () => {
  Swal.fire({
    title: 'Confirm',
    text: 'Are you absolutely sure you want to rollback to this version?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes',
    cancelButtonText: 'No'
  }).then((result) => {
    if (result.isConfirmed) {
    rollbackForm.post(`/dashboard/workflow-builder/version/${props.version.id}/rollback`, {
      onSuccess: () => {
        isRollbackModalVisible.value = false;
      }
    });
  }
  });
};

const updateParallelSplits = () => {
  router.post(`/dashboard/workflow-builder/version/${props.version.id}/settings`, {
    mandate_parallel_splits: mandateParallelSplits.value
  }, {
    preserveScroll: true,
    preserveState: true,
  });
};

const cloneToDraft = () => {
  Swal.fire({
    title: 'Confirm',
    text: 'Create a new editable Draft from this published version?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes',
    cancelButtonText: 'No'
  }).then((result) => {
    if (result.isConfirmed) {
    router.post(`/dashboard/workflow-builder/version/${props.version.id}/clone`);
  }
  });
};
</script>
