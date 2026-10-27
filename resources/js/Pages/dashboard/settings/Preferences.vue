<template>
  <div class="p-6 lg:p-8 max-w-5xl">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-200 dark:border-gray-800">
      <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-primary/10 dark:bg-primary/20 flex items-center justify-center text-primary dark:text-emerald-400">
          <Icon icon="lucide:palette" class="text-2xl" />
        </div>
        <div>
          <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Workspace Preferences</h1>
          <p class="text-sm text-slate-500 dark:text-gray-400">Tailor the visual experience, interface density, typography, and default entry screen.</p>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <span class="text-xs text-slate-400">Settings autosave instantly</span>
      </div>
    </div>

    <div class="space-y-8 my-6">

      <!-- Appearance & Theme Mode -->
      <div>
        <div class="mb-4">
          <h2 class="text-base font-bold text-slate-900 dark:text-white">Interface Theme</h2>
          <p class="text-xs text-slate-500 dark:text-gray-400">Choose between light, dark, or automatic system sync.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <!-- Light Mode -->
          <div 
            @click="setTheme('light')"
            class="group cursor-pointer rounded-2xl border-2 p-4 transition-all"
            :class="theme === 'light' 
              ? 'border-primary bg-primary/5 dark:bg-primary/10 shadow-sm' 
              : 'border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 hover:border-slate-300 dark:hover:border-gray-700'"
          >
            <div class="flex items-center justify-between mb-3">
              <div class="flex items-center gap-2">
                <Icon icon="lucide:sun" class="text-lg text-amber-500" />
                <span class="text-xs font-bold text-slate-900 dark:text-white">Light Mode</span>
              </div>
              <div v-if="theme === 'light'" class="w-2 h-2 rounded-full bg-primary"></div>
            </div>
            <div class="h-20 rounded-xl bg-slate-100 border border-slate-200 p-2 space-y-1.5 flex flex-col justify-center">
              <div class="h-2 w-3/4 rounded bg-slate-300"></div>
              <div class="h-2 w-1/2 rounded bg-slate-200"></div>
              <div class="h-2 w-5/6 rounded bg-slate-300"></div>
            </div>
            <p class="text-[11px] text-slate-400 mt-2">Crisp contrast, ideal for brightly lit offices</p>
          </div>

          <!-- Dark Mode -->
          <div 
            @click="setTheme('dark')"
            class="group cursor-pointer rounded-2xl border-2 p-4 transition-all"
            :class="theme === 'dark' 
              ? 'border-primary bg-primary/5 dark:bg-primary/10 shadow-sm' 
              : 'border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 hover:border-slate-300 dark:hover:border-gray-700'"
          >
            <div class="flex items-center justify-between mb-3">
              <div class="flex items-center gap-2">
                <Icon icon="lucide:moon" class="text-lg text-indigo-400" />
                <span class="text-xs font-bold text-slate-900 dark:text-white">Dark Mode</span>
              </div>
              <div v-if="theme === 'dark'" class="w-2 h-2 rounded-full bg-primary"></div>
            </div>
            <div class="h-20 rounded-xl bg-gray-950 border border-gray-800 p-2 space-y-1.5 flex flex-col justify-center">
              <div class="h-2 w-3/4 rounded bg-gray-700"></div>
              <div class="h-2 w-1/2 rounded bg-gray-800"></div>
              <div class="h-2 w-5/6 rounded bg-gray-700"></div>
            </div>
            <p class="text-[11px] text-slate-400 mt-2">Deep charcoal palette, eases eye strain</p>
          </div>

          <!-- System Default -->
          <div 
            @click="setTheme('system')"
            class="group cursor-pointer rounded-2xl border-2 p-4 transition-all"
            :class="theme === 'system' 
              ? 'border-primary bg-primary/5 dark:bg-primary/10 shadow-sm' 
              : 'border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 hover:border-slate-300 dark:hover:border-gray-700'"
          >
            <div class="flex items-center justify-between mb-3">
              <div class="flex items-center gap-2">
                <Icon icon="lucide:laptop" class="text-lg text-slate-500" />
                <span class="text-xs font-bold text-slate-900 dark:text-white">System Auto</span>
              </div>
              <div v-if="theme === 'system'" class="w-2 h-2 rounded-full bg-primary"></div>
            </div>
            <div class="h-20 rounded-xl bg-gradient-to-r from-slate-100 to-gray-950 border border-slate-300 dark:border-gray-700 p-2 flex items-center justify-center">
              <Icon icon="lucide:sparkles" class="text-slate-400 text-lg" />
            </div>
            <p class="text-[11px] text-slate-400 mt-2">Follows your operating system theme schedule</p>
          </div>
        </div>
      </div>

      <div class="h-px bg-slate-200/80 dark:bg-gray-800"></div>

      <!-- Typography & Scale -->
      <div>
        <div class="mb-4">
          <h2 class="text-base font-bold text-slate-900 dark:text-white">Typography & Scaling</h2>
          <p class="text-xs text-slate-500 dark:text-gray-400">Scale the base text size across the entire application.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
          <div class="p-5 rounded-2xl border border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800/40">
            <div class="flex items-center justify-between mb-3">
              <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Live Typography Preview</span>
              <span class="text-xs font-mono font-semibold px-2 py-0.5 rounded bg-white dark:bg-gray-700 border border-slate-200 dark:border-gray-600">
                {{ fontSize }}px
              </span>
            </div>
            <p 
              class="text-slate-800 dark:text-gray-200 transition-all leading-relaxed" 
              :style="{ fontSize: fontSize + 'px' }"
            >
              The electronic document management system securely coordinates memos, approvals, and registries across all departments.
            </p>
          </div>

          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <button 
                type="button" 
                @click="setFontSize(14)" 
                class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-slate-200 dark:border-gray-700 hover:bg-slate-100 dark:hover:bg-gray-800 transition"
              >
                Small (14px)
              </button>
              <button 
                type="button" 
                @click="setFontSize(16)" 
                class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-slate-200 dark:border-gray-700 hover:bg-slate-100 dark:hover:bg-gray-800 transition"
              >
                Normal (16px)
              </button>
              <button 
                type="button" 
                @click="setFontSize(18)" 
                class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-slate-200 dark:border-gray-700 hover:bg-slate-100 dark:hover:bg-gray-800 transition"
              >
                Large (18px)
              </button>
            </div>

            <div class="flex items-center gap-3">
              <span class="text-xs font-bold text-slate-400">A</span>
              <input 
                type="range" 
                v-model.number="fontSize" 
                min="13" 
                max="22" 
                step="1"
                @input="applyFontSize(fontSize)"
                class="w-full h-2 bg-slate-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-primary"
              />
              <span class="text-lg font-bold text-slate-700 dark:text-gray-300">A</span>
            </div>
          </div>
        </div>
      </div>

      <div class="h-px bg-slate-200/80 dark:bg-gray-800"></div>

      <!-- Layout Density -->
      <div>
        <div class="mb-4">
          <h2 class="text-base font-bold text-slate-900 dark:text-white">Workspace Density</h2>
          <p class="text-xs text-slate-500 dark:text-gray-400">Controls data table padding, row heights, and document list compactness.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div 
            @click="setDensity('compact')"
            class="cursor-pointer rounded-2xl border-2 p-4 transition-all"
            :class="density === 'compact' ? 'border-primary bg-primary/5 dark:bg-primary/10' : 'border-slate-200 dark:border-gray-800 hover:border-slate-300'"
          >
            <div class="flex items-center justify-between mb-2">
              <span class="text-xs font-bold text-slate-900 dark:text-white">Compact</span>
              <div v-if="density === 'compact'" class="w-2 h-2 rounded-full bg-primary"></div>
            </div>
            <p class="text-[11px] text-slate-400">Highest data density. Fits maximum records per screen view.</p>
          </div>

          <div 
            @click="setDensity('comfortable')"
            class="cursor-pointer rounded-2xl border-2 p-4 transition-all"
            :class="density === 'comfortable' ? 'border-primary bg-primary/5 dark:bg-primary/10' : 'border-slate-200 dark:border-gray-800 hover:border-slate-300'"
          >
            <div class="flex items-center justify-between mb-2">
              <span class="text-xs font-bold text-slate-900 dark:text-white">Comfortable (Default)</span>
              <div v-if="density === 'comfortable'" class="w-2 h-2 rounded-full bg-primary"></div>
            </div>
            <p class="text-[11px] text-slate-400">Balanced padding and readable rows for standard desktop usage.</p>
          </div>

          <div 
            @click="setDensity('spacious')"
            class="cursor-pointer rounded-2xl border-2 p-4 transition-all"
            :class="density === 'spacious' ? 'border-primary bg-primary/5 dark:bg-primary/10' : 'border-slate-200 dark:border-gray-800 hover:border-slate-300'"
          >
            <div class="flex items-center justify-between mb-2">
              <span class="text-xs font-bold text-slate-900 dark:text-white">Spacious</span>
              <div v-if="density === 'spacious'" class="w-2 h-2 rounded-full bg-primary"></div>
            </div>
            <p class="text-[11px] text-slate-400">Generous spacing, ideal for touch displays and tablets.</p>
          </div>
        </div>
      </div>

      <div class="h-px bg-slate-200/80 dark:bg-gray-800"></div>

      <!-- Default Landing Page -->
      <div>
        <div class="mb-4">
          <h2 class="text-base font-bold text-slate-900 dark:text-white">Default Landing Screen</h2>
          <p class="text-xs text-slate-500 dark:text-gray-400">Choose which module opens immediately after you sign in.</p>
        </div>

        <div class="max-w-md">
          <select 
            v-model="defaultLandingPage" 
            @change="saveLandingPage"
            class="w-full px-3.5 py-2.5 text-xs bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-slate-800 dark:text-gray-200 font-medium"
          >
            <option value="/dashboard">Executive Dashboard (/dashboard)</option>
            <option value="/archiving">Document Registry & Archives (/archiving)</option>
            <option value="/correspondence">Correspondence & Mail Registry (/correspondence)</option>
            <option value="/streamlined-tasks">Tasks & Workflow Approvals (/streamlined-tasks)</option>
            <option value="/finance-and-accounts">Finance & Accounts (/finance-and-accounts)</option>
          </select>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { useToast } from "primevue/usetoast";

const props = defineProps({
  user: Object
});

const page = usePage();
const toast = useToast();

const theme = ref('light');
const fontSize = ref(16);
const density = ref('comfortable');
const defaultLandingPage = ref('/dashboard');

const setTheme = (selectedTheme) => {
  theme.value = selectedTheme;
  localStorage.setItem('edms_theme', selectedTheme);

  if (selectedTheme === 'dark' || (selectedTheme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
  } else {
    document.documentElement.classList.remove('dark');
  }

  router.post('/settings/preferences', { theme: selectedTheme }, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      toast.add({ severity: 'success', summary: 'Theme Updated', detail: `Theme set to ${selectedTheme}.`, life: 2500 });
    }
  });
};

const setFontSize = (size) => {
  fontSize.value = size;
  applyFontSize(size);
};

const applyFontSize = (size) => {
  localStorage.setItem('edms_fontSize', size);
  document.documentElement.style.fontSize = `${size}px`;

  router.post('/settings/preferences', { font_size: size }, {
    preserveScroll: true,
    preserveState: true
  });
};

const setDensity = (selectedDensity) => {
  density.value = selectedDensity;
  localStorage.setItem('edms_density', selectedDensity);

  router.post('/settings/preferences', { density: selectedDensity }, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      toast.add({ severity: 'success', summary: 'Density Applied', detail: `Workspace density set to ${selectedDensity}.`, life: 2500 });
    }
  });
};

const saveLandingPage = () => {
  router.post('/settings/preferences', { default_landing_page: defaultLandingPage.value }, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      toast.add({ severity: 'success', summary: 'Landing Page Set', detail: 'Default entry screen saved.', life: 2500 });
    }
  });
};

onMounted(() => {
  const user = props.user || page.props.user;

  theme.value = user?.theme || localStorage.getItem('edms_theme') || 'light';
  fontSize.value = user?.font_size ? parseInt(user.font_size) : (parseInt(localStorage.getItem('edms_fontSize')) || 16);
  density.value = user?.density || localStorage.getItem('edms_density') || 'comfortable';
  defaultLandingPage.value = user?.default_landing_page || '/dashboard';

  // Apply current theme
  if (theme.value === 'dark' || (theme.value === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
  } else {
    document.documentElement.classList.remove('dark');
  }
});
</script>
