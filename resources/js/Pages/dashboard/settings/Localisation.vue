<template>
  <div class="p-6 lg:p-8 max-w-5xl">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-200 dark:border-gray-800">
      <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-primary/10 dark:bg-primary/20 flex items-center justify-center text-primary dark:text-emerald-400">
          <Icon icon="lucide:globe" class="text-2xl" />
        </div>
        <div>
          <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Regional & Localisation</h1>
          <p class="text-sm text-slate-500 dark:text-gray-400">Customise language, date and time conventions, corporate timezone, and currency symbol.</p>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <button
          type="button"
          @click="saveLocalisation"
          :disabled="form.processing"
          class="px-5 py-2 text-xs font-semibold rounded-xl bg-primary hover:bg-primary-dark text-white shadow-sm shadow-primary/20 transition disabled:opacity-50 flex items-center gap-2"
        >
          <Icon v-if="form.processing" icon="lucide:loader-2" class="animate-spin text-sm" />
          <span>{{ form.processing ? 'Saving...' : 'Save Regional Settings' }}</span>
        </button>
      </div>
    </div>

    <!-- Live Regional Preview -->
    <div class="my-6 p-5 rounded-2xl bg-gradient-to-r from-slate-50 to-indigo-50/30 dark:from-gray-800/60 dark:to-gray-800/40 border border-slate-200/80 dark:border-gray-700">
      <div class="flex items-center justify-between mb-2">
        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Live Regional Output Preview</span>
        <span class="text-[11px] font-mono text-primary font-semibold">{{ form.timezone }}</span>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
        <div>
          <span class="text-[11px] text-slate-400 block">Current Formatted Time:</span>
          <span class="text-sm font-mono font-bold text-slate-900 dark:text-white">{{ previewTime }}</span>
        </div>
        <div>
          <span class="text-[11px] text-slate-400 block">Current Formatted Date:</span>
          <span class="text-sm font-mono font-bold text-slate-900 dark:text-white">{{ previewDate }}</span>
        </div>
        <div>
          <span class="text-[11px] text-slate-400 block">Currency Sample:</span>
          <span class="text-sm font-mono font-bold text-emerald-600 dark:text-emerald-400">{{ previewCurrency }}</span>
        </div>
      </div>
    </div>

    <!-- Form -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200/80 dark:border-gray-800 p-6 space-y-6">

      <!-- Language -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-gray-800">
        <div>
          <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Interface Language</h2>
          <p class="text-[11px] text-slate-500 dark:text-gray-400">Select language for menus, dialogues, and system notifications.</p>
        </div>
        <select 
          v-model="form.language" 
          class="w-full sm:w-64 px-3.5 py-2 text-xs bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 text-slate-800 dark:text-gray-200 font-medium"
        >
          <option value="English">English (United Kingdom / Nigeria)</option>
          <option value="French">Français (French)</option>
          <option value="Spanish">Español (Spanish)</option>
          <option value="German">Deutsch (German)</option>
          <option value="Chinese">中文 (Simplified Chinese)</option>
          <option value="Arabic">العربية (Arabic)</option>
          <option value="Hausa">Harshen Hausa</option>
          <option value="Yoruba">Èdè Yorùbá</option>
          <option value="Igbo">Asụsụ Igbo</option>
        </select>
      </div>

      <!-- Timezone -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-gray-800">
        <div>
          <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Timezone (Regional Clock)</h2>
          <p class="text-[11px] text-slate-500 dark:text-gray-400">All workflow timestamps, audit events, and memo dates map to this zone.</p>
        </div>
        <select 
          v-model="form.timezone" 
          class="w-full sm:w-64 px-3.5 py-2 text-xs bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 text-slate-800 dark:text-gray-200 font-medium"
        >
          <option value="Africa/Lagos">Africa/Lagos (WAT, GMT+1)</option>
          <option value="UTC">UTC (Universal Coordinated Time)</option>
          <option value="Europe/London">Europe/London (GMT / BST)</option>
          <option value="America/New_York">America/New_York (EST / EDT)</option>
          <option value="Asia/Dubai">Asia/Dubai (GST, GMT+4)</option>
        </select>
      </div>

      <!-- Time Format -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-gray-800">
        <div>
          <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Time Display Format</h2>
          <p class="text-[11px] text-slate-500 dark:text-gray-400">Choose 12-hour (AM/PM) or 24-hour military notation.</p>
        </div>
        <select 
          v-model="form.time_format" 
          class="w-full sm:w-64 px-3.5 py-2 text-xs bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 text-slate-800 dark:text-gray-200 font-medium"
        >
          <option value="12 Hours">12 Hours (e.g. 02:45 PM)</option>
          <option value="24 Hours">24 Hours (e.g. 14:45)</option>
        </select>
      </div>

      <!-- Date Format -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-gray-800">
        <div>
          <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Date Display Format</h2>
          <p class="text-[11px] text-slate-500 dark:text-gray-400">Controls dates shown in registry tables, vouchers, and audit trails.</p>
        </div>
        <select 
          v-model="form.date_format" 
          class="w-full sm:w-64 px-3.5 py-2 text-xs bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 text-slate-800 dark:text-gray-200 font-medium"
        >
          <option value="DD/MM/YYYY">DD/MM/YYYY (e.g. 21/10/2026 - Commonwealth / Nigeria)</option>
          <option value="YYYY-MM-DD">YYYY-MM-DD (e.g. 2026-10-21 - ISO Standard)</option>
          <option value="MM/DD/YYYY">MM/DD/YYYY (e.g. 10/21/2026 - US Format)</option>
        </select>
      </div>

      <!-- Currency -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Operating Currency</h2>
          <p class="text-[11px] text-slate-500 dark:text-gray-400">Default currency code and symbol in vouchers and finance registries.</p>
        </div>
        <div v-if="canManageGlobalCurrency">
          <select 
            v-model="form.currency" 
            class="w-full sm:w-64 px-3.5 py-2 text-xs bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 text-slate-800 dark:text-gray-200 font-medium"
          >
            <option value="NGN">NGN - Nigerian Naira (₦)</option>
            <option value="USD">USD - US Dollar ($)</option>
            <option value="GBP">GBP - British Pound (£)</option>
            <option value="EUR">EUR - Euro (€)</option>
          </select>
        </div>
        <div v-else class="flex items-center gap-2">
          <span class="px-3.5 py-2 bg-slate-100 dark:bg-gray-800/80 border border-slate-200 dark:border-gray-700 rounded-xl text-xs font-bold text-slate-800 dark:text-gray-200 flex items-center gap-1.5 shadow-2xs">
            <Icon icon="lucide:lock" class="text-slate-400 text-xs" />
            NGN - Nigerian Naira (₦)
          </span>
          <span class="text-[11px] text-slate-400 dark:text-gray-500 font-medium">(Institutional Standard)</span>
        </div>
      </div>

    </div>

    <!-- Bottom Save Action -->
    <div class="mt-8 flex justify-end">
      <button 
        type="button" 
        @click="saveLocalisation" 
        :disabled="form.processing"
        class="px-6 py-2.5 text-xs font-semibold rounded-xl bg-primary hover:bg-primary-dark text-white shadow-sm shadow-primary/20 transition disabled:opacity-50 flex items-center gap-2"
      >
        <Icon v-if="form.processing" icon="lucide:loader-2" class="animate-spin text-sm" />
        <span>{{ form.processing ? 'Updating...' : 'Save Regional Preferences' }}</span>
      </button>
    </div>

  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Icon } from '@iconify/vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { useToast } from "primevue/usetoast";

const props = defineProps({
  user: Object
});

const page = usePage();
const toast = useToast();

const userObj = props.user || page.props.user || {};

const canManageGlobalCurrency = computed(() => {
  const roles = page.props.user_roles || page.props.roles || [];
  const r = Array.isArray(roles) ? roles.map(x => String(x).toLowerCase().trim()) : [String(roles).toLowerCase().trim()];
  return r.includes('super admin') || r.includes('admin') || r.includes('super-admin');
});

const form = useForm({
  language: userObj.language || 'English',
  time_format: userObj.time_format || '12 Hours',
  date_format: userObj.date_format || 'DD/MM/YYYY',
  timezone: userObj.timezone || 'Africa/Lagos',
  currency: userObj.currency || 'NGN'
});

const previewTime = computed(() => {
  return form.time_format === '24 Hours' ? '14:35:00' : '02:35:00 PM';
});

const previewDate = computed(() => {
  if (form.date_format === 'YYYY-MM-DD') return '2026-10-21';
  if (form.date_format === 'MM/DD/YYYY') return '10/21/2026';
  return '21/10/2026';
});

const previewCurrency = computed(() => {
  const symbols = {
    NGN: '₦ 2,500,000.00',
    USD: '$ 2,500,000.00',
    GBP: '£ 2,500,000.00',
    EUR: '€ 2,500,000.00'
  };
  return symbols[form.currency] || '₦ 2,500,000.00';
});

const saveLocalisation = () => {
  form.post('/settings/localisation', {
    preserveScroll: true,
    onSuccess: () => {
      toast.add({
        severity: 'success',
        summary: 'Regional Preferences Saved',
        detail: 'Localisation preferences updated successfully.',
        life: 3000
      });
    }
  });
};
</script>
