<template>
  <Dialog
    v-model:visible="visible"
    :closable="!submitting"
    modal
    :style="{ width: '38rem' }"
    :breakpoints="{ '1199px': '75vw', '575px': '95vw' }"
    class="custom-dialog"
  >
    <template #header>
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue_primary flex items-center justify-center border border-blue-100 shadow-sm">
          <Icon icon="solar:ticket-bold-duotone" width="24" />
        </div>
        <div>
          <h2 class="text-lg font-bold text-slate-800">Submit IT Support Ticket</h2>
          <p class="text-xs text-slate-500">Log an issue or request assistance from our technical helpdesk</p>
        </div>
      </div>
    </template>

    <div class="pt-2 pb-1 space-y-4 text-slate-700">
      <!-- Category & Priority Row -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">
            Category <span class="text-rose-500">*</span>
          </label>
          <select
            v-model="form.category"
            class="w-full text-sm border border-slate-300 rounded-lg p-2.5 bg-white focus:ring-2 focus:ring-blue_primary/20 focus:border-blue_primary transition-all outline-none"
          >
            <option value="technical">Technical / System Issue</option>
            <option value="access_permissions">Access & Permissions</option>
            <option value="workflow_approval">Workflow & Approvals</option>
            <option value="document_issue">Document Management</option>
            <option value="bug_report">Bug / Error Report</option>
            <option value="general_inquiry">General Inquiry</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">
            Priority Level <span class="text-rose-500">*</span>
          </label>
          <select
            v-model="form.priority"
            class="w-full text-sm border border-slate-300 rounded-lg p-2.5 bg-white focus:ring-2 focus:ring-blue_primary/20 focus:border-blue_primary transition-all outline-none"
          >
            <option value="low">🟢 Low - General Question</option>
            <option value="medium">🟡 Medium - Normal Issue</option>
            <option value="high">🟠 High - Significant Impact</option>
            <option value="urgent">🔴 Urgent - Critical / Blocker</option>
          </select>
        </div>
      </div>

      <!-- Subject -->
      <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">
          Subject / Issue Summary <span class="text-rose-500">*</span>
        </label>
        <input
          v-model="form.subject"
          type="text"
          placeholder="e.g., Cannot preview signed contracts in Workflow tab"
          class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue_primary/20 focus:border-blue_primary transition-all outline-none"
        />
      </div>

      <!-- Description -->
      <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">
          Detailed Description <span class="text-rose-500">*</span>
        </label>
        <textarea
          v-model="form.description"
          rows="4"
          placeholder="Please describe what happened, steps to reproduce, error message text, and relevant document IDs..."
          class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue_primary/20 focus:border-blue_primary transition-all outline-none resize-none"
        ></textarea>
        <p class="text-[11px] text-slate-400 mt-0.5">Be as specific as possible so our technical team can resolve your issue swiftly.</p>
      </div>

      <!-- File Attachment -->
      <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">
          Attachment (Optional screenshot or log file)
        </label>
        <div
          v-if="!selectedFile"
          class="border-2 border-dashed border-slate-300 hover:border-blue_primary/60 rounded-xl p-4 text-center cursor-pointer transition-colors bg-slate-50/50 hover:bg-blue-50/20"
          @click="fileInput?.click()"
        >
          <input
            ref="fileInput"
            type="file"
            class="hidden"
            accept="image/*,.pdf,.doc,.docx,.txt"
            @change="handleFileSelected"
          />
          <div class="flex flex-col items-center justify-center gap-1">
            <Icon icon="solar:cloud-upload-bold-duotone" width="32" class="text-slate-400 hover:text-blue_primary" />
            <p class="text-xs font-medium text-slate-700">Click to upload screenshot or file</p>
            <p class="text-[11px] text-slate-400">PNG, JPG, PDF, TXT or DOC up to 10MB</p>
          </div>
        </div>

        <div
          v-else
          class="flex items-center justify-between p-2.5 bg-blue-50 border border-blue-200 rounded-lg"
        >
          <div class="flex items-center gap-2 overflow-hidden pr-2">
            <Icon icon="solar:document-text-bold-duotone" width="22" class="text-blue_primary flex-shrink-0" />
            <div class="truncate text-xs">
              <span class="font-medium text-slate-800">{{ selectedFile.name }}</span>
              <span class="text-slate-500 ml-1">({{ formatFileSize(selectedFile.size) }})</span>
            </div>
          </div>
          <button
            type="button"
            class="text-rose-500 hover:text-rose-700 p-1 rounded hover:bg-rose-50 transition-colors"
            @click="removeFile"
          >
            <Icon icon="solar:trash-bin-trash-bold" width="18" />
          </button>
        </div>
      </div>
    </div>

    <template #footer>
      <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 w-full">
        <Button
          label="Cancel"
          severity="secondary"
          outlined
          :disabled="submitting"
          @click="closeModal"
          class="px-5 py-2 text-sm border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50"
        />
        <Button
          :label="submitting ? 'Submitting...' : 'Submit Ticket'"
          :loading="submitting"
          :disabled="submitting"
          @click="submitTicket"
          class="px-6 py-2 text-sm bg-blue_primary text-white rounded-lg hover:bg-blue_primary/90 shadow-sm"
        />
      </div>
    </template>
  </Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed, ref } from "vue";
import { router } from "@inertiajs/vue3";
import { useToast } from "primevue/usetoast";

const toast = useToast();
const props = defineProps({
  modelValue: Boolean,
});
const emit = defineEmits(["update:modelValue", "ticket-created"]);

const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  },
});

const submitting = ref(false);
const fileInput = ref(null);
const selectedFile = ref(null);

const form = ref({
  category: "technical",
  priority: "medium",
  subject: "",
  description: "",
});

const handleFileSelected = (e) => {
  const file = e.target.files[0];
  if (!file) return;

  if (file.size > 10 * 1024 * 1024) {
    toast.add({
      severity: "warn",
      summary: "File Too Large",
      detail: "Attachment must be less than 10MB.",
      life: 4000,
    });
    return;
  }
  selectedFile.value = file;
};

const removeFile = () => {
  selectedFile.value = null;
  if (fileInput.value) fileInput.value.value = "";
};

const formatFileSize = (bytes) => {
  if (!bytes) return "0 B";
  const k = 1024;
  const sizes = ["B", "KB", "MB", "GB"];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + " " + sizes[i];
};

const closeModal = () => {
  if (submitting.value) return;
  emit("update:modelValue", false);
};

const resetForm = () => {
  form.value = {
    category: "technical",
    priority: "medium",
    subject: "",
    description: "",
  };
  selectedFile.value = null;
  if (fileInput.value) fileInput.value.value = "";
};

const submitTicket = () => {
  if (!form.value.subject.trim()) {
    toast.add({
      severity: "warn",
      summary: "Subject Required",
      detail: "Please provide a brief subject for your ticket.",
      life: 3500,
    });
    return;
  }

  if (!form.value.description.trim() || form.value.description.trim().length < 10) {
    toast.add({
      severity: "warn",
      summary: "Description Too Short",
      detail: "Please provide at least 10 characters describing the issue.",
      life: 3500,
    });
    return;
  }

  submitting.value = true;

  const payload = new FormData();
  payload.append("category", form.value.category);
  payload.append("priority", form.value.priority);
  payload.append("subject", form.value.subject);
  payload.append("description", form.value.description);
  if (selectedFile.value) {
    payload.append("attachment", selectedFile.value);
  }

  router.post("/dashboard/support/tickets", payload, {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: (page) => {
      submitting.value = false;
      const flash = page.props?.flash || {};
      toast.add({
        severity: "success",
        summary: "Ticket Submitted Successfully",
        detail: flash.ticket_no
          ? `Your ticket (${flash.ticket_no}) has been logged and queued for IT triage.`
          : "Your ticket has been logged and queued for IT triage.",
        life: 5000,
      });
      resetForm();
      emit("update:modelValue", false);
      emit("ticket-created");
    },
    onError: (errors) => {
      submitting.value = false;
      const firstError = Object.values(errors)[0] || "Failed to submit ticket. Please try again.";
      toast.add({
        severity: "error",
        summary: "Submission Failed",
        detail: firstError,
        life: 5000,
      });
    },
  });
};
</script>

<style scoped>
.custom-dialog :deep(.p-dialog-header) {
  padding: 1.25rem 1.5rem 0.75rem 1.5rem;
  border-bottom: 1px solid #f1f5f9;
}
.custom-dialog :deep(.p-dialog-content) {
  padding: 1.25rem 1.5rem;
  background-color: #ffffff;
}
.custom-dialog :deep(.p-dialog-footer) {
  padding: 0.75rem 1.5rem 1.25rem 1.5rem;
  background-color: #ffffff;
}
</style>
