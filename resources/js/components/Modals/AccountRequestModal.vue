<template>
  <div>
    <Dialog 
      v-model:visible="visible" 
      modal 
      :closable="false"
      :pt="{
        root: { class: 'rounded-2xl overflow-hidden shadow-2xl border border-slate-100 bg-white' },
        content: { class: '!p-0 bg-white' },
        header: { class: '!p-0 !border-0' }
      }" 
      :style="{ width: '42rem' }" 
      :breakpoints="{ '1199px': '75vw', '575px': '95vw' }"
    >
      <template #header>
        <div class="flex items-center justify-between w-full px-6 py-4 bg-slate-50/90 border-b border-slate-200">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 border border-blue-200/80 flex items-center justify-center shrink-0 shadow-2xs">
              <Icon icon="solar:user-hand-up-bold-duotone" width="22" />
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h2 class="font-bold text-slate-900 text-base">Account Requests</h2>
                <span 
                  v-if="props.notifications && props.notifications.length > 0"
                  class="bg-amber-100 text-amber-800 text-[11px] px-2 py-0.5 rounded-full font-bold border border-amber-200"
                >
                  {{ props.notifications.length }} Waiting
                </span>
              </div>
              <p class="text-slate-500 text-xs mt-0.5">Accounts waiting for administrative review and clearance</p>
            </div>
          </div>

          <!-- Close Interface Icon -->
          <button 
            type="button"
            @click="visible = false"
            class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 transition-colors flex items-center justify-center cursor-pointer shrink-0 ml-3"
            aria-label="Close"
          >
            <Icon icon="solar:close-circle-linear" width="22" />
          </button>
        </div>
      </template>

      <section class="divide-y divide-slate-100 max-h-[60vh] overflow-y-auto px-6 py-2">
        <!-- Empty State -->
        <div v-if="!props.notifications || props.notifications.length === 0" class="py-12 flex flex-col items-center justify-center text-center">
          <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-3 border border-emerald-100 shadow-xs">
            <Icon icon="solar:check-circle-bold-duotone" width="30" />
          </div>
          <h3 class="text-sm font-bold text-slate-800">All Clear! No Pending Requests</h3>
          <p class="text-xs text-slate-500 mt-1 max-w-xs">
            There are no user accounts waiting for administrative approval.
          </p>
        </div>

        <!-- Requests List -->
        <div 
          v-else 
          v-for="notification in props.notifications" 
          :key="notification.id"
          class="flex flex-col sm:flex-row sm:items-center justify-between py-4 gap-4 hover:bg-slate-50/70 transition-colors rounded-xl px-3"
        >
          <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-full bg-blue-100/80 text-blue-700 flex items-center justify-center font-bold text-sm shrink-0 border border-blue-200 shadow-2xs">
              {{ getInitials(parseJson(notification)) }}
            </div>
            <div class="flex flex-col min-w-0">
              <div class="flex items-center gap-2 flex-wrap">
                <span class="font-bold text-slate-900 text-sm truncate">
                  {{ parseJson(notification).last_name }} {{ parseJson(notification).first_name }}
                </span>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                  GL {{ parseJson(notification).grade_level ?? 'N/A' }}
                </span>
              </div>
              <span class="text-xs text-slate-500 truncate mt-0.5">
                {{ parseJson(notification).email }}
              </span>
              <div class="flex items-center gap-1.5 mt-1.5">
                <span class="inline-flex items-center gap-1.5 text-[11px] font-medium text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200/80">
                  <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                  Waiting for Clearance
                </span>
              </div>
            </div>
          </div>

          <div class="flex items-center gap-2 self-end sm:self-center shrink-0">
            <button 
              type="button"
              @click="declineRequest(notification)" 
              :disabled="rejectingId === parseJson(notification).id"
              class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100/80 rounded-xl border border-rose-200/80 transition-colors disabled:opacity-50 cursor-pointer"
            >
              <Icon icon="solar:close-circle-linear" width="15" />
              <span>{{ rejectingId === parseJson(notification).id ? 'Declining...' : 'Decline' }}</span>
            </button>
            <Link 
              :href="'/dashboard/add-user?email=' + encodeURIComponent(parseJson(notification).email || '') + '&notification_id=' + (notification.id || '')" 
              class="flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-xs transition-colors cursor-pointer"
            >
              <span>Review & Approve</span>
              <Icon icon="solar:arrow-right-linear" width="14" />
            </Link>
          </div>
        </div>
      </section>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { Link, router } from "@inertiajs/vue3";
import { Icon } from "@iconify/vue";
import Dialog from "primevue/dialog";

const props = defineProps(["modelValue", "notifications"]);
const emit = defineEmits(["update:modelValue"]);

const rejectingId = ref(null);

const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  },
});

const parseJson = (notification) => {
  if (!notification) return {};
  if (typeof notification.data === "object" && notification.data !== null) {
    return notification.data;
  }
  try {
    return JSON.parse(notification.data || "{}");
  } catch (e) {
    return {};
  }
};

const getInitials = (data) => {
  const f = data.first_name ? data.first_name.charAt(0).toUpperCase() : "";
  const l = data.last_name ? data.last_name.charAt(0).toUpperCase() : "";
  return f + l || "U";
};

const declineRequest = (notification) => {
  const data = parseJson(notification);
  if (!data.id) return;
  
  const name = `${data.first_name || ''} ${data.last_name || ''}`.trim() || data.email;
  if (!confirm(`Are you sure you want to decline and remove the account request for ${name}?`)) {
    return;
  }

  rejectingId.value = data.id;
  router.post('/dashboard/reject-account-request', { id: data.id }, {
    preserveScroll: true,
    onFinish: () => {
      rejectingId.value = null;
    }
  });
};
</script>

<style scoped>
/* Scoped Dialog styling */
</style>
