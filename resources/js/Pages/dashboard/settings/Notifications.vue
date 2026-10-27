<template>
  <div class="p-6 lg:p-8 max-w-5xl">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-200 dark:border-gray-800">
      <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-primary/10 dark:bg-primary/20 flex items-center justify-center text-primary dark:text-emerald-400">
          <Icon icon="lucide:bell-ring" class="text-2xl" />
        </div>
        <div>
          <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Notification Alert Channels</h1>
          <p class="text-sm text-slate-500 dark:text-gray-400">Choose how and where you receive notifications across documents, workflows, and collaboration.</p>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <button
          type="button"
          @click="saveSettings"
          :disabled="form.processing"
          class="px-5 py-2 text-xs font-semibold rounded-xl bg-primary hover:bg-primary-dark text-white shadow-sm shadow-primary/20 transition disabled:opacity-50 flex items-center gap-2"
        >
          <Icon v-if="form.processing" icon="lucide:loader-2" class="animate-spin text-sm" />
          <span>{{ form.processing ? 'Saving...' : 'Save Notification Preferences' }}</span>
        </button>
      </div>
    </div>

    <!-- Quick Presets -->
    <div class="my-6 p-4 rounded-2xl bg-slate-50 dark:bg-gray-800/60 border border-slate-200 dark:border-gray-700 flex flex-wrap items-center justify-between gap-3">
      <div class="flex items-center gap-2">
        <Icon icon="lucide:sparkles" class="text-primary text-base" />
        <span class="text-xs font-bold text-slate-700 dark:text-gray-300">Quick Presets:</span>
      </div>
      <div class="flex flex-wrap items-center gap-2">
        <button 
          type="button" 
          @click="applyPreset('Both')" 
          class="px-3 py-1.5 text-xs font-medium rounded-lg bg-white dark:bg-gray-700 border border-slate-200 dark:border-gray-600 hover:border-primary text-slate-700 dark:text-gray-200 transition"
        >
          All Channels (Email + In-App)
        </button>
        <button 
          type="button" 
          @click="applyPreset('In-App Only')" 
          class="px-3 py-1.5 text-xs font-medium rounded-lg bg-white dark:bg-gray-700 border border-slate-200 dark:border-gray-600 hover:border-primary text-slate-700 dark:text-gray-200 transition"
        >
          In-App Only (No Emails)
        </button>
        <button 
          type="button" 
          @click="applyPreset('Email Only')" 
          class="px-3 py-1.5 text-xs font-medium rounded-lg bg-white dark:bg-gray-700 border border-slate-200 dark:border-gray-600 hover:border-primary text-slate-700 dark:text-gray-200 transition"
        >
          Email Only
        </button>
        <button 
          type="button" 
          @click="applyPreset('Muted')" 
          class="px-3 py-1.5 text-xs font-medium rounded-lg bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 hover:bg-rose-100 text-rose-700 dark:text-rose-400 transition"
        >
          Mute All
        </button>
      </div>
    </div>

    <!-- Alert Sections -->
    <div class="space-y-6">

      <!-- Section: Document Activity -->
      <div class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200/80 dark:border-gray-800 p-6">
        <div class="flex items-center gap-2 pb-3 mb-4 border-b border-slate-100 dark:border-gray-800">
          <Icon icon="lucide:files" class="text-primary text-base" />
          <div>
            <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Document Activity Alerts</h2>
            <p class="text-xs text-slate-500 dark:text-gray-400">Triggered when files or archives are added, updated, or revised.</p>
          </div>
        </div>

        <div class="divide-y divide-slate-100 dark:divide-gray-800">
          <div 
            v-for="item in documentAlerts" 
            :key="item.key"
            class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3"
          >
            <div>
              <p class="text-xs font-semibold text-slate-800 dark:text-gray-200">{{ item.label }}</p>
              <p class="text-[11px] text-slate-400">{{ item.description }}</p>
            </div>
            <select 
              v-model="form.notification_settings[item.key]" 
              class="px-3 py-1.5 text-xs bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 text-slate-700 dark:text-gray-300 font-medium w-44 shrink-0"
            >
              <option value="Both">In-App & Email</option>
              <option value="In-App Only">In-App Only</option>
              <option value="Email Only">Email Only</option>
              <option value="Muted">Muted</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Section: Workflow & Approvals -->
      <div class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200/80 dark:border-gray-800 p-6">
        <div class="flex items-center gap-2 pb-3 mb-4 border-b border-slate-100 dark:border-gray-800">
          <Icon icon="lucide:git-pull-request" class="text-primary text-base" />
          <div>
            <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Workflow & Approval Alerts</h2>
            <p class="text-xs text-slate-500 dark:text-gray-400">Triggered for steps requiring your attention, signature, or review.</p>
          </div>
        </div>

        <div class="divide-y divide-slate-100 dark:divide-gray-800">
          <div 
            v-for="item in workflowAlerts" 
            :key="item.key"
            class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3"
          >
            <div>
              <p class="text-xs font-semibold text-slate-800 dark:text-gray-200">{{ item.label }}</p>
              <p class="text-[11px] text-slate-400">{{ item.description }}</p>
            </div>
            <select 
              v-model="form.notification_settings[item.key]" 
              class="px-3 py-1.5 text-xs bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 text-slate-700 dark:text-gray-300 font-medium w-44 shrink-0"
            >
              <option value="Both">In-App & Email</option>
              <option value="In-App Only">In-App Only</option>
              <option value="Email Only">Email Only</option>
              <option value="Muted">Muted</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Section: Team & Collaboration -->
      <div class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200/80 dark:border-gray-800 p-6">
        <div class="flex items-center gap-2 pb-3 mb-4 border-b border-slate-100 dark:border-gray-800">
          <Icon icon="lucide:users" class="text-primary text-base" />
          <div>
            <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Team & Collaboration Alerts</h2>
            <p class="text-xs text-slate-500 dark:text-gray-400">Direct mentions, comments, registry access changes, and memo routing.</p>
          </div>
        </div>

        <div class="divide-y divide-slate-100 dark:divide-gray-800">
          <div 
            v-for="item in collabAlerts" 
            :key="item.key"
            class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3"
          >
            <div>
              <p class="text-xs font-semibold text-slate-800 dark:text-gray-200">{{ item.label }}</p>
              <p class="text-[11px] text-slate-400">{{ item.description }}</p>
            </div>
            <select 
              v-model="form.notification_settings[item.key]" 
              class="px-3 py-1.5 text-xs bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 text-slate-700 dark:text-gray-300 font-medium w-44 shrink-0"
            >
              <option value="Both">In-App & Email</option>
              <option value="In-App Only">In-App Only</option>
              <option value="Email Only">Email Only</option>
              <option value="Muted">Muted</option>
            </select>
          </div>
        </div>
      </div>

    </div>

    <!-- Bottom Save Action -->
    <div class="mt-8 flex justify-end">
      <button 
        type="button" 
        @click="saveSettings" 
        :disabled="form.processing"
        class="px-6 py-2.5 text-xs font-semibold rounded-xl bg-primary hover:bg-primary-dark text-white shadow-sm shadow-primary/20 transition disabled:opacity-50 flex items-center gap-2"
      >
        <Icon v-if="form.processing" icon="lucide:loader-2" class="animate-spin text-sm" />
        <span>{{ form.processing ? 'Saving Preferences...' : 'Save Notification Preferences' }}</span>
      </button>
    </div>

  </div>
</template>

<script setup>
import { usePage, useForm } from '@inertiajs/vue3';
import { useToast } from "primevue/usetoast";

const page = usePage();
const toast = useToast();

const documentAlerts = [
  { key: 'doc_new_added', label: 'New Document Uploaded', description: 'When files are registered to your branches or folders' },
  { key: 'doc_updated', label: 'Document Metadata Updated', description: 'When titles, descriptions, or classifications change' },
  { key: 'doc_deleted', label: 'Document Moved to Recycle Bin', description: 'When a record is archived or removed' },
  { key: 'doc_new_versions', label: 'New Version Published', description: 'When a new document revision is submitted' },
  { key: 'doc_old_versions_restored', label: 'Prior Version Restored', description: 'When an archive rollback is executed' }
];

const workflowAlerts = [
  { key: 'workflow_new_approval', label: 'New Approval Request', description: 'Immediate notification when a step awaits your signature' },
  { key: 'workflow_assigning', label: 'Workflow Assignment', description: 'When you are assigned as initiator or reviewer' },
  { key: 'workflow_response', label: 'Response to Workflow', description: 'When another user approves, rejects, or queries a task' },
  { key: 'workflow_completed', label: 'Workflow Completed', description: 'Final sign-off notification when instance concludes' }
];

const collabAlerts = [
  { key: 'collab_access_granted', label: 'Registry Access Granted', description: 'When elevated permissions are assigned to your staff ID' },
  { key: 'collab_comments_assigned', label: 'Comments & Direct Mentions', description: 'When someone mentions you in document notes' },
  { key: 'collab_annotations', label: 'File Annotations', description: 'When PDF annotations or digital highlights are placed' }
];

let existingSettings = page.props.user?.notification_settings || {};
if (typeof existingSettings === 'string') {
  try {
    existingSettings = JSON.parse(existingSettings);
  } catch(e) {}
}

const form = useForm({
  notification_settings: {
    doc_new_added: existingSettings.doc_new_added || 'Both',
    doc_updated: existingSettings.doc_updated || 'In-App Only',
    doc_deleted: existingSettings.doc_deleted || 'Both',
    doc_new_versions: existingSettings.doc_new_versions || 'In-App Only',
    doc_old_versions_restored: existingSettings.doc_old_versions_restored || 'In-App Only',

    workflow_new_approval: existingSettings.workflow_new_approval || 'Both',
    workflow_assigning: existingSettings.workflow_assigning || 'Both',
    workflow_response: existingSettings.workflow_response || 'Both',
    workflow_completed: existingSettings.workflow_completed || 'Both',

    collab_access_granted: existingSettings.collab_access_granted || 'Both',
    collab_comments_assigned: existingSettings.collab_comments_assigned || 'In-App Only',
    collab_annotations: existingSettings.collab_annotations || 'In-App Only'
  }
});

const applyPreset = (value) => {
  Object.keys(form.notification_settings).forEach(key => {
    form.notification_settings[key] = value;
  });
  toast.add({
    severity: 'info',
    summary: 'Preset Selected',
    detail: `All channels switched to "${value}". Click Save to persist.`,
    life: 3000
  });
};

const saveSettings = () => {
  form.post('/settings/notifications', {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      toast.add({
        severity: 'success',
        summary: 'Preferences Saved',
        detail: 'Your notification alert preferences were updated successfully.',
        life: 3000
      });
    }
  });
};
</script>
