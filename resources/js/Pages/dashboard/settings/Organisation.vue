<template>
  <div class="p-6 lg:p-8 max-w-6xl">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-200 dark:border-gray-800">
      <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-primary/10 dark:bg-primary/20 flex items-center justify-center text-primary dark:text-emerald-400">
          <Icon icon="lucide:building-2" class="text-2xl" />
        </div>
        <div>
          <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Organisation Profile</h1>
          <p class="text-sm text-slate-500 dark:text-gray-400">Configure legal corporate identity, branding colors, official seals, and letterhead.</p>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <button
          type="button"
          @click="getOrganisation"
          class="px-4 py-2 text-xs font-semibold rounded-xl border border-slate-200 dark:border-gray-700 text-slate-600 dark:text-gray-300 hover:bg-slate-100 dark:hover:bg-gray-800 transition"
        >
          Reload
        </button>
        <button
          type="button"
          @click="save"
          :disabled="form.processing"
          class="px-5 py-2 text-xs font-semibold rounded-xl bg-primary hover:bg-primary-dark text-white shadow-sm shadow-primary/20 transition disabled:opacity-50 flex items-center gap-2"
        >
          <Icon v-if="form.processing" icon="lucide:loader-2" class="animate-spin text-sm" />
          <span>{{ form.processing ? 'Saving...' : 'Save Corporate Profile' }}</span>
        </button>
      </div>
    </div>

    <!-- Live Corporate Branding Preview Card -->
    <div class="my-6 p-6 rounded-3xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm overflow-hidden">
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: form.primary_color || '#16a34a' }"></span>
          <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Live Executive Brand & Letterhead Preview</h3>
        </div>
        <span class="text-[11px] font-mono px-2 py-0.5 rounded bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-gray-400">Document Template</span>
      </div>

      <!-- Letterhead Simulation -->
      <div class="p-6 rounded-2xl border border-slate-200/80 dark:border-gray-700/80 bg-slate-50/60 dark:bg-gray-800/40">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6 pb-4 border-b-2" :style="{ borderColor: form.primary_color || '#16a34a' }">
          <div class="flex items-center gap-4">
            <img 
              :src="logoUrl || '/images/default-logo.png'" 
              alt="Logo Preview" 
              class="w-16 h-16 rounded-2xl object-contain bg-white dark:bg-gray-900 p-1.5 border border-slate-200 dark:border-gray-700 shadow-xs"
            />
            <div>
              <h2 class="text-lg font-black tracking-tight" :style="{ color: form.primary_color || '#16a34a' }">
                {{ form.company_name || 'OFFICE OF THE ACCOUNTANT-GENERAL OF THE FEDERATION' }}
              </h2>
              <p class="text-xs italic text-slate-500 dark:text-gray-400" v-if="form.motto">
                "{{ form.motto }}"
              </p>
              <div class="flex flex-wrap items-center gap-3 text-[11px] text-slate-500 dark:text-gray-400 mt-1">
                <span v-if="form.rc_number">RC/CAC: {{ form.rc_number }}</span>
                <span v-if="form.tin_number">TIN: {{ form.tin_number }}</span>
                <span v-if="form.website">{{ form.website }}</span>
              </div>
            </div>
          </div>

          <!-- Official Stamp Preview -->
          <div v-if="stampUrl" class="shrink-0 flex flex-col items-center">
            <div class="w-16 h-16 rounded-full border-2 border-dashed border-rose-500/40 p-1 flex items-center justify-center">
              <img :src="stampUrl" alt="Seal" class="w-full h-full object-contain filter contrast-125" />
            </div>
            <span class="text-[9px] uppercase tracking-widest text-rose-600 font-bold mt-1">Official Seal</span>
          </div>
        </div>

        <div class="pt-3 flex flex-wrap items-center justify-between text-[11px] text-slate-400">
          <span>{{ form.address || 'Treasury House, Samuel Ladoke Akintola Boulevard, Garki II, Abuja' }}</span>
          <span class="font-mono">{{ form.email }} | {{ form.phone }}</span>
        </div>
      </div>
    </div>

    <!-- Corporate Information Form -->
    <div class="space-y-6">

      <!-- Section: Legal Information -->
      <div class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200/80 dark:border-gray-800 p-6 space-y-5">
        <div class="flex items-center gap-2 pb-3 border-b border-slate-100 dark:border-gray-800">
          <Icon icon="lucide:file-text" class="text-primary text-base" />
          <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Legal Entity Details</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-gray-300 mb-1.5">Legal Corporate Name *</label>
            <input 
              type="text" 
              v-model="form.company_name" 
              placeholder="e.g. Office of the Accountant-General of the Federation"
              class="w-full px-3.5 py-2.5 text-xs bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-slate-800 dark:text-gray-200 font-medium"
            />
            <p v-if="form.errors.company_name" class="text-rose-500 text-xs mt-1">{{ form.errors.company_name }}</p>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-gray-300 mb-1.5">Corporate Motto / Tagline</label>
            <input 
              type="text" 
              v-model="form.motto" 
              placeholder="e.g. Integrity, Accountability, and Excellence"
              class="w-full px-3.5 py-2.5 text-xs bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-slate-800 dark:text-gray-200"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-gray-300 mb-1.5">RC / CAC Registration Number</label>
            <input 
              type="text" 
              v-model="form.rc_number" 
              placeholder="e.g. RC-1029384"
              class="w-full px-3.5 py-2.5 text-xs bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-slate-800 dark:text-gray-200 font-mono"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-gray-300 mb-1.5">Tax Identification Number (TIN)</label>
            <input 
              type="text" 
              v-model="form.tin_number" 
              placeholder="e.g. 24098765-0001"
              class="w-full px-3.5 py-2.5 text-xs bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-slate-800 dark:text-gray-200 font-mono"
            />
          </div>
        </div>
      </div>

      <!-- Section: Communication & Contact -->
      <div class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200/80 dark:border-gray-800 p-6 space-y-5">
        <div class="flex items-center gap-2 pb-3 border-b border-slate-100 dark:border-gray-800">
          <Icon icon="lucide:phone-call" class="text-primary text-base" />
          <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Contact & Headquarters</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-gray-300 mb-1.5">Official Corporate Email *</label>
            <input 
              type="email" 
              v-model="form.email" 
              placeholder="contact@oagf.gov.ng"
              class="w-full px-3.5 py-2.5 text-xs bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-slate-800 dark:text-gray-200"
            />
            <p v-if="form.errors.email" class="text-rose-500 text-xs mt-1">{{ form.errors.email }}</p>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-gray-300 mb-1.5">Official Phone Number *</label>
            <div class="flex items-center">
              <span class="px-3 py-2.5 bg-slate-100 dark:bg-gray-700 border border-r-0 border-slate-200 dark:border-gray-700 rounded-l-xl text-xs font-mono text-slate-500">+234</span>
              <input 
                type="text" 
                v-model="form.phone" 
                placeholder="8030000000"
                class="w-full px-3.5 py-2.5 text-xs bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-r-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-slate-800 dark:text-gray-200 font-mono"
              />
            </div>
            <p v-if="form.errors.phone" class="text-rose-500 text-xs mt-1">{{ form.errors.phone }}</p>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-gray-300 mb-1.5">Official Web Portal</label>
            <input 
              type="url" 
              v-model="form.website" 
              placeholder="https://oagf.gov.ng"
              class="w-full px-3.5 py-2.5 text-xs bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-slate-800 dark:text-gray-200"
            />
          </div>

          <div class="md:col-span-3">
            <label class="block text-xs font-semibold text-slate-700 dark:text-gray-300 mb-1.5">Headquarters Street Address</label>
            <textarea 
              v-model="form.address" 
              rows="2"
              placeholder="Treasury House, Samuel Ladoke Akintola Boulevard, P.M.B. 7015, Garki II, Abuja F.C.T"
              class="w-full px-3.5 py-2 text-xs bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-slate-800 dark:text-gray-200 resize-none"
            ></textarea>
          </div>
        </div>
      </div>

      <!-- Section: Visual Branding & Assets -->
      <div class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200/80 dark:border-gray-800 p-6 space-y-6">
        <div class="flex items-center gap-2 pb-3 border-b border-slate-100 dark:border-gray-800">
          <Icon icon="lucide:palette" class="text-primary text-base" />
          <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Visual Branding & Official Seals</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          
          <!-- Logo Asset -->
          <div class="p-4 rounded-xl border border-slate-200 dark:border-gray-700 flex items-center gap-4">
            <div class="w-20 h-20 rounded-2xl bg-slate-100 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 p-1 flex items-center justify-center shrink-0">
              <img :src="logoUrl || '/images/default-logo.png'" alt="Logo" class="max-w-full max-h-full object-contain" />
            </div>
            <div class="flex-1 min-w-0">
              <h3 class="text-xs font-bold text-slate-900 dark:text-white">Corporate Logo</h3>
              <p class="text-[11px] text-slate-400 mb-2">PNG or SVG with transparent background recommended.</p>
              <button 
                type="button" 
                @click="triggerLogoUpload"
                class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-white dark:bg-gray-800 border border-slate-300 dark:border-gray-600 hover:bg-slate-50 transition"
              >
                Change Logo
              </button>
              <input type="file" ref="logoInput" @change="handleLogoUpload" accept="image/*" class="hidden" />
            </div>
          </div>

          <!-- Official Seal / Stamp Asset -->
          <div class="p-4 rounded-xl border border-slate-200 dark:border-gray-700 flex items-center gap-4">
            <div class="w-20 h-20 rounded-2xl bg-slate-100 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 p-1 flex items-center justify-center shrink-0">
              <img v-if="stampUrl" :src="stampUrl" alt="Stamp" class="max-w-full max-h-full object-contain" />
              <Icon v-else icon="lucide:stamp" class="text-2xl text-slate-300" />
            </div>
            <div class="flex-1 min-w-0">
              <h3 class="text-xs font-bold text-slate-900 dark:text-white">Official Stamp / Seal</h3>
              <p class="text-[11px] text-slate-400 mb-2">Applied to certified document outputs and vouchers.</p>
              <button 
                type="button" 
                @click="triggerStampUpload"
                class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-white dark:bg-gray-800 border border-slate-300 dark:border-gray-600 hover:bg-slate-50 transition"
              >
                Upload Official Seal
              </button>
              <input type="file" ref="stampInput" @change="handleStampUpload" accept="image/png,image/jpeg" class="hidden" />
            </div>
          </div>

          <!-- Color Customization -->
          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-gray-300 mb-1.5">Primary Brand Color</label>
            <div class="flex items-center gap-2">
              <input 
                type="color" 
                v-model="form.primary_color" 
                class="w-10 h-10 rounded-xl cursor-pointer border border-slate-300 dark:border-gray-600 p-0.5 bg-transparent"
              />
              <input 
                type="text" 
                v-model="form.primary_color" 
                placeholder="#16a34a"
                class="flex-1 px-3.5 py-2 text-xs font-mono bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl text-slate-800 dark:text-gray-200 uppercase"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-gray-300 mb-1.5">Secondary Brand Color</label>
            <div class="flex items-center gap-2">
              <input 
                type="color" 
                v-model="form.secondary_color" 
                class="w-10 h-10 rounded-xl cursor-pointer border border-slate-300 dark:border-gray-600 p-0.5 bg-transparent"
              />
              <input 
                type="text" 
                v-model="form.secondary_color" 
                placeholder="#0284c7"
                class="flex-1 px-3.5 py-2 text-xs font-mono bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl text-slate-800 dark:text-gray-200 uppercase"
              />
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { useToast } from "primevue/usetoast";
import axios from 'axios';

const props = defineProps({
  organisation: Object
});

const page = usePage();
const toast = useToast();

const logoInput = ref(null);
const stampInput = ref(null);
const logoUrl = ref('');
const stampUrl = ref('');

const form = useForm({
  file: null,
  stamp_file: null,
  company_name: '',
  motto: '',
  email: '',
  phone: '',
  website: '',
  address: '',
  rc_number: '',
  tin_number: '',
  primary_color: '#16a34a',
  secondary_color: '#0284c7'
});

const triggerLogoUpload = () => {
  if (logoInput.value) logoInput.value.click();
};

const handleLogoUpload = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.file = file;
    logoUrl.value = URL.createObjectURL(file);
  }
};

const triggerStampUpload = () => {
  if (stampInput.value) stampInput.value.click();
};

const handleStampUpload = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.stamp_file = file;
    stampUrl.value = URL.createObjectURL(file);
  }
};

const getOrganisation = async () => {
  try {
    const res = await axios.get('/settings/get-organisation');
    if (res.data) {
      const d = res.data;
      form.company_name = d.company_name || '';
      form.motto = d.motto || '';
      form.email = d.email || '';
      form.phone = d.phone || '';
      form.website = d.website || '';
      form.address = d.address || '';
      form.rc_number = d.rc_number || '';
      form.tin_number = d.tin_number || '';
      form.primary_color = d.primary_color || '#16a34a';
      form.secondary_color = d.secondary_color || '#0284c7';

      if (d.logo) {
        logoUrl.value = page.props.base + '/' + d.logo;
      }
      if (d.stamp_path) {
        stampUrl.value = page.props.base + '/' + d.stamp_path;
      }
    }
  } catch (err) {
    console.error('Failed to fetch organisation profile', err);
  }
};

const save = () => {
  if (!form.company_name.trim()) {
    toast.add({ severity: 'warn', summary: 'Missing Name', detail: 'Please provide corporate legal name.', life: 3000 });
    return;
  }
  if (!form.email.trim()) {
    toast.add({ severity: 'warn', summary: 'Missing Email', detail: 'Please provide official email.', life: 3000 });
    return;
  }

  form.post('/settings/organisation', {
    preserveScroll: true,
    onSuccess: () => {
      toast.add({
        severity: 'success',
        summary: 'Organisation Updated',
        detail: 'Corporate legal profile and branding have been saved successfully.',
        life: 3500
      });
      getOrganisation();
    },
    onError: () => {
      toast.add({
        severity: 'error',
        summary: 'Save Failed',
        detail: 'Please inspect the form errors and try again.',
        life: 3500
      });
    }
  });
};

onMounted(() => {
  getOrganisation();
});
</script>
