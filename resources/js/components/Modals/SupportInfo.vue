<template>
	<Dialog
		v-model:visible="visible"
		:closable="false"
		modal
		:pt="{
      content: { class: 'px-0 bg-white dark:bg-slate-900 pb-6' },
    }"
		:style="{ width: '32rem', padding: '10px 0px' }"
		:breakpoints="{ '1199px': '75vw', '575px': '92vw' }"
		class="custom-dialog"
	>
		<template #header>
			<div class="flex items-center gap-3">
				<div class="p-3 border border-slate-200 dark:border-slate-700 bg-blue-50 dark:bg-slate-800 text-blue-600 dark:text-blue-400 rounded-full">
					<Icon icon="simple-line-icons:earphones-alt" width="22" />
				</div>

				<div>
					<h1 class="font-bold text-xl text-slate-800 dark:text-slate-100">
						{{ props.canUpdate ? 'Configure Support Channels' : 'Helpdesk & Support Directory' }}
					</h1>
					<p class="text-xs text-slate-500 dark:text-slate-400">
						{{ props.canUpdate ? 'Customize official support information for the organization' : 'Official technical assistance channels and office hours' }}
					</p>
				</div>
			</div>
		</template>
		<Divider class="-mt-2" />

		<!-- READ-ONLY DIRECTORY VIEW (When user does NOT have support update) -->
		<section v-if="!props.canUpdate" class="mx-6 space-y-3.5">
			<div class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700 text-xs text-slate-600 dark:text-slate-300">
				<Icon icon="solar:info-circle-bold-duotone" width="18" class="text-blue-600 shrink-0" />
				<span>Official IT Directorate contact channels. Reach out for technical support or portal assistance.</span>
			</div>

			<!-- Email Card -->
			<div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-700/80 bg-white dark:bg-slate-800 flex items-center justify-between shadow-2xs">
				<div class="flex items-center gap-3">
					<div class="w-9 h-9 rounded-lg bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
						<Icon icon="solar:letter-bold-duotone" width="18" />
					</div>
					<div>
						<p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Support Email</p>
						<p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ form.email || 'support@ansel.internal' }}</p>
					</div>
				</div>
				<button 
					type="button"
					@click="copyVal(form.email || 'support@ansel.internal')" 
					class="p-2 text-slate-400 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
					title="Copy Email"
				>
					<Icon icon="solar:copy-bold-duotone" width="16" />
				</button>
			</div>

			<!-- Phone Card -->
			<div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-700/80 bg-white dark:bg-slate-800 flex items-center justify-between shadow-2xs">
				<div class="flex items-center gap-3">
					<div class="w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
						<Icon icon="solar:phone-bold-duotone" width="18" />
					</div>
					<div>
						<p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Helpdesk Line</p>
						<p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ form.phone_number || '+234 800 000 0000' }}</p>
					</div>
				</div>
				<button 
					type="button"
					@click="copyVal(form.phone_number || '+234 800 000 0000')" 
					class="p-2 text-slate-400 hover:text-emerald-600 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
					title="Copy Phone Number"
				>
					<Icon icon="solar:copy-bold-duotone" width="16" />
				</button>
			</div>

			<!-- Location Card -->
			<div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-700/80 bg-white dark:bg-slate-800 flex items-center gap-3 shadow-2xs">
				<div class="w-9 h-9 rounded-lg bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
					<Icon icon="solar:map-point-bold-duotone" width="18" />
				</div>
				<div>
					<p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Office Location</p>
					<p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ form.location || 'Treasury House, Abuja' }}</p>
				</div>
			</div>

			<!-- Office Hours Card -->
			<div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-700/80 bg-white dark:bg-slate-800 flex items-center gap-3 shadow-2xs">
				<div class="w-9 h-9 rounded-lg bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
					<Icon icon="solar:clock-circle-bold-duotone" width="18" />
				</div>
				<div>
					<p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Official Service Hours</p>
					<p class="text-sm font-semibold text-slate-800 dark:text-slate-200">
						{{ form.start_day }} – {{ form.end_day }} ({{ form.start_time }} to {{ form.end_time }})
					</p>
				</div>
			</div>
		</section>

		<!-- EDITABLE CONFIGURATION VIEW (When user HAS support update) -->
		<section v-else class="mx-6">
			<div class="text-[#344054] dark:text-slate-200 text-xs">
				<div>
					<label class="font-semibold text-slate-700 dark:text-slate-300">Support Mail</label>
					<input type="email" v-model="form.email" class="mt-1 w-full p-2.5 border border-slate-300 dark:border-slate-700 dark:bg-slate-800 rounded-lg outline-none focus:ring-2 focus:ring-blue_primary/20" placeholder="support@ansel.com" >
				</div>

				<div class="mt-3.5">
					<label class="font-semibold text-slate-700 dark:text-slate-300">Office Location</label>
					<input type="text" v-model="form.location" class="mt-1 w-full p-2.5 border border-slate-300 dark:border-slate-700 dark:bg-slate-800 rounded-lg outline-none focus:ring-2 focus:ring-blue_primary/20" placeholder="e.g. 2nd Floor, IT Directorate, Treasury House" >
				</div>

				<div class="mt-3.5">
					<label class="font-semibold text-slate-700 dark:text-slate-300">Office Phone Number</label>
					<input type="text" v-model="form.phone_number" class="mt-1 w-full p-2.5 border border-slate-300 dark:border-slate-700 dark:bg-slate-800 rounded-lg outline-none focus:ring-2 focus:ring-blue_primary/20" placeholder="+234 800 000 0000" >
				</div>

				<h3 class="mt-4 mb-1.5 font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider text-[11px]">Active Service Days</h3>
				<div class="flex items-center w-full gap-2">
					<select v-model="form.start_day" class="w-full p-2 border border-slate-300 dark:border-slate-700 dark:bg-slate-800 rounded-lg outline-none focus:ring-2 focus:ring-blue_primary/20">
						<option v-for="day in days" :key="day" :value="day">{{ day }}</option>
					</select>

					<span class="px-2 text-xs text-slate-400 font-semibold">To</span>

					<select v-model="form.end_day" class="w-full p-2 border border-slate-300 dark:border-slate-700 dark:bg-slate-800 rounded-lg outline-none focus:ring-2 focus:ring-blue_primary/20">
						<option v-for="day in days" :key="day" :value="day">{{ day }}</option>
					</select>
				</div>

				<h3 class="mt-4 mb-1.5 font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider text-[11px]">Daily Office Hours</h3>
				<div class="flex items-center w-full gap-2">
					<select v-model="form.start_time" class="w-full p-2 border border-slate-300 dark:border-slate-700 dark:bg-slate-800 rounded-lg outline-none focus:ring-2 focus:ring-blue_primary/20">
						<option v-for="time in times" :key="time" :value="time">{{ time }}</option>
					</select>

					<span class="px-2 text-xs text-slate-400 font-semibold">To</span>

					<select v-model="form.end_time" class="w-full p-2 border border-slate-300 dark:border-slate-700 dark:bg-slate-800 rounded-lg outline-none focus:ring-2 focus:ring-blue_primary/20">
						<option v-for="time in times" :key="time" :value="time">{{ time }}</option>
					</select>
				</div>
			</div>
		</section>

		<template #footer>
			<!-- When Read-Only: Show Close button only -->
			<div v-if="!props.canUpdate" class="flex justify-end pt-2">
				<Button
					@click="closeModal"
					label="Close"
					severity="secondary"
					outlined
					class="py-2 px-8 border-gray-300 text-slate-700 font-medium rounded-lg"
				/>
			</div>

			<!-- When Editable: Show Cancel and Save buttons -->
			<div v-else class="flex justify-end gap-3 pt-2">
				<Button
					@click="closeModal"
					label="Cancel"
					severity="secondary"
					outlined
					class="py-2 px-6 border-gray-300 text-slate-700 font-medium rounded-lg"
				/>
				<Button
					@click="submit"
					label="Save Changes"
					class="py-2 bg-blue_primary px-8 border-0 text-white font-medium hover:bg-blue_primary/90 rounded-lg shadow-sm"
				/>
			</div>
		</template>
	</Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed, watch } from "vue";
import { useToast } from "primevue/usetoast";
import { useForm } from '@inertiajs/vue3';
import { Icon } from "@iconify/vue";

const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
const times = [ '08:00 AM', '09:00 AM', '10:00 AM', '11:00 AM', '12:00 PM', '01:00 PM', '02:00 PM', '03:00 PM', '04:00 PM', '05:00 PM', '06:00 PM', '07:00 PM', '08:00 PM', '09:00 PM', '10:00 PM', '11:00 PM'];
const toast = useToast();

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  supportData: { type: Object, default: () => ({}) },
  canUpdate: { type: Boolean, default: false },
});

const emit = defineEmits(["update:modelValue"]);

const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  },
});

const form = useForm({
  email: '',
  phone_number: '',
  location: '',
  start_day: 'Monday',
  end_day: 'Friday',
  start_time: '08:00 AM',
  end_time: '05:00 PM'
});

watch(() => props.supportData, (newVal) => {
  if (newVal) {
    form.email = newVal.email || '';
    form.phone_number = newVal.phone_number || '';
    form.location = newVal.location || '';
    form.start_day = newVal.start_day || 'Monday';
    form.end_day = newVal.end_day || 'Friday';
    
    // helper to format H:i:s to h:i A
    const formatTime = (time) => {
      if (!time) return '08:00 AM';
      let [h, m] = time.split(':');
      let hour = parseInt(h);
      let ampm = hour >= 12 ? 'PM' : 'AM';
      hour = hour % 12;
      hour = hour ? hour : 12; // the hour '0' should be '12'
      return (hour < 10 ? '0' + hour : hour) + ':' + (m || '00') + ' ' + ampm;
    };
    
    form.start_time = formatTime(newVal.start_time);
    form.end_time = formatTime(newVal.end_time);
  }
}, { immediate: true });

const copyVal = (text) => {
  if (!text) return;
  navigator.clipboard.writeText(text);
  toast.add({ severity: 'info', summary: 'Copied', detail: `${text} copied to clipboard`, life: 2500 });
};

const closeModal = () => {
  emit("update:modelValue", false);
};

const submit = () => {
  if (!props.canUpdate) {
    toast.add({ severity: 'warn', summary: 'Restricted', detail: 'You do not have permission to update support details.', life: 4000 });
    return;
  }

  form.post('/dashboard/support/support-details', {
    preserveScroll: true,
    onSuccess: () => {
      toast.add({ severity: 'success', summary: 'Success', detail: 'Support details updated successfully', life: 3000 });
      emit("update:modelValue", false);
    },
    onError: (errors) => {
      let msg = Object.values(errors).join(', ');
      toast.add({ severity: 'error', summary: 'Error', detail: msg || 'Failed to update support details', life: 5000 });
    }
  });
};
</script>
 <style scoped>
  .custom-dialog .p-dialog-header {
    display: none; /* Hide the header */
  }
</style>
