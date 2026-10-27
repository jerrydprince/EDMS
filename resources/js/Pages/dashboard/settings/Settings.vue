<template>
  <main class="flex flex-col lg:flex-row min-h-screen bg-slate-50 dark:bg-gray-950 font-sans text-slate-800 dark:text-gray-100">
    
    <!-- Mobile & Tablet Horizontal Tab Navigation (< lg screens) -->
    <header class="lg:hidden sticky top-0 z-30 bg-white/95 dark:bg-gray-900/95 backdrop-blur border-b border-slate-200 dark:border-gray-800 p-4">
      <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-2.5">
          <div class="w-9 h-9 rounded-xl bg-primary/10 dark:bg-primary/20 flex items-center justify-center text-primary dark:text-primary-light">
            <Icon icon="lucide:sliders-horizontal" class="text-xl" />
          </div>
          <div>
            <h1 class="text-lg font-bold text-slate-900 dark:text-white leading-tight">Settings & Preferences</h1>
            <p class="text-xs text-slate-500 dark:text-gray-400">Manage account, workspace & organization</p>
          </div>
        </div>
      </div>

      <!-- Quick Scrollable Tab Pills -->
      <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-1">
        <button
          v-for="item in visibleSettings"
          :key="item.key"
          type="button"
          @click="selectTab(item)"
          class="shrink-0 flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-medium transition-all"
          :class="activeTabKey === item.key 
            ? 'bg-primary text-white shadow-sm shadow-primary/25' 
            : 'bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-gray-300 hover:bg-slate-200 dark:hover:bg-gray-700'"
        >
          <Icon :icon="item.icon" class="text-base" />
          <span>{{ item.name }}</span>
        </button>
      </div>
    </header>

    <!-- Desktop Sidebar (>= lg screens) -->
    <aside class="hidden lg:flex flex-col w-72 xl:w-80 shrink-0 border-r border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 sticky top-0 h-screen select-none">
      <!-- Sidebar Header -->
      <div class="p-5 pb-3">
        <div class="flex items-center gap-3 mb-2">
          <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-primary to-emerald-600 flex items-center justify-center text-white shadow-md shadow-primary/20">
            <Icon icon="lucide:settings-2" class="text-xl" />
          </div>
          <div>
            <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Settings</h1>
            <p class="text-xs text-slate-500 dark:text-gray-400">System & Workspace Hub</p>
          </div>
        </div>

        <!-- Filter Search Box -->
        <div class="mt-4 relative">
          <Icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-gray-500 text-sm" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search settings..."
            class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 dark:bg-gray-800/80 border border-slate-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-slate-800 dark:text-gray-200 transition-all placeholder:text-slate-400"
          />
          <button 
            v-if="searchQuery" 
            @click="searchQuery = ''"
            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs"
          >
            <Icon icon="lucide:x" />
          </button>
        </div>
      </div>

      <div class="px-5 py-1">
        <div class="h-px bg-slate-200/80 dark:bg-gray-800"></div>
      </div>

      <!-- Navigation List -->
      <nav class="flex-1 overflow-y-auto px-3 py-3 space-y-1 hide_scroll">
        <template v-for="item in visibleSettings" :key="item.key">
          <button
            type="button"
            @click="selectTab(item)"
            class="w-full text-left flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all group"
            :class="activeTabKey === item.key
              ? 'bg-primary/10 dark:bg-primary/20 text-primary dark:text-emerald-400 font-semibold shadow-xs'
              : 'text-slate-600 dark:text-gray-300 hover:bg-slate-100 dark:hover:bg-gray-800 hover:text-slate-900 dark:hover:text-white'"
          >
            <div class="flex items-center gap-3 min-w-0">
              <div 
                class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors"
                :class="activeTabKey === item.key 
                  ? 'bg-primary text-white shadow-xs' 
                  : 'bg-slate-100 dark:bg-gray-800 text-slate-500 dark:text-gray-400 group-hover:text-slate-800 dark:group-hover:text-gray-200'"
              >
                <Icon :icon="item.icon" class="text-lg" />
              </div>
              <div class="truncate text-left">
                <p class="truncate leading-snug">{{ item.name }}</p>
                <p class="text-[11px] text-slate-400 dark:text-gray-500 font-normal truncate">{{ item.subtitle }}</p>
              </div>
            </div>
            <Icon 
              icon="lucide:chevron-right" 
              class="text-xs opacity-0 group-hover:opacity-100 transition-opacity"
              :class="activeTabKey === item.key ? 'opacity-100 text-primary dark:text-emerald-400' : 'text-slate-400'"
            />
          </button>
        </template>

        <div v-if="visibleSettings.length === 0" class="text-center py-8 text-xs text-slate-400">
          No settings categories match "{{ searchQuery }}"
        </div>
      </nav>

      <!-- Sidebar Footer (System Status Badge) -->
      <div class="p-4 border-t border-slate-200/80 dark:border-gray-800 bg-slate-50/50 dark:bg-gray-900/50">
        <div class="flex items-center justify-between text-xs text-slate-500 dark:text-gray-400">
          <span class="flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            EDMS Enterprise
          </span>
          <span class="font-mono text-[10px] bg-slate-200/60 dark:bg-gray-800 px-1.5 py-0.5 rounded">v2.4</span>
        </div>
      </div>
    </aside>

    <!-- Main Content Panel -->
    <section class="flex-1 min-w-0 overflow-y-auto bg-white dark:bg-gray-900 border-l border-slate-200/60 dark:border-gray-800/80">
      <profile 
        v-if="activeTabKey === 'profile'" 
        :user="props.user || page.props.user" 
      />
      
      <organisation 
        v-else-if="activeTabKey === 'organisation' && canAccessOrganisation"
        :organisation="page.props.organisation"
      />
      
      <preferences 
        v-else-if="activeTabKey === 'preferences'" 
        :user="props.user || page.props.user"
      />
      
      <notification 
        v-else-if="activeTabKey === 'notifications'" 
        :user="props.user || page.props.user"
      />
      
      <security 
        v-else-if="activeTabKey === 'security' && is_passwordPolicies" 
        :user="props.user || page.props.user" 
        @refreshUser="refreshUserHandler" 
        @passwordPolicies="handlePasswordPolicies" 
      />

      <passwordPolicies 
        v-else-if="activeTabKey === 'security' && !is_passwordPolicies" 
        @security="handleSecurity"
      />
      
      <integrations 
        v-else-if="activeTabKey === 'integrations' && canAccessIntegrations" 
      />

      <localisation 
        v-else-if="activeTabKey === 'localisation'" 
        :user="props.user || page.props.user" 
      />
      
      <development 
        v-else-if="activeTabKey === 'development' && canAccessSystem" 
        :system-info="page.props.systemInfo"
      />
      
      <systemConfigurations 
        v-else-if="activeTabKey === 'systemConfigurations' && canAccessSystem" 
      />
    </section>

  </main>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import profile from './profile.vue';
import preferences from './Preferences.vue';
import security from './Security.vue';
import localisation from './Localisation.vue';
import organisation from './Organisation.vue';
import notification from './Notifications.vue';
import integrations from './Integrations.vue';
import development from './Development.vue';
import systemConfigurations from './SystemConfigurations.vue';
import passwordPolicies from './PasswordPolicies.vue';

const page = usePage();
const props = defineProps({
  user: Object
});

const is_passwordPolicies = ref(true);
const searchQuery = ref('');

const roles = computed(() => page.props.roles || page.props.user_roles || []);
const userPermissions = computed(() => {
  const p = page.props.permissions || [];
  return Array.isArray(p) ? p.map(x => String(x).toLowerCase().trim()) : [];
});

const hasRole = (role) => {
  if (!roles.value) return false;
  if (Array.isArray(roles.value)) {
    return roles.value.map(r => String(r).toLowerCase()).includes(role.toLowerCase());
  }
  if (typeof roles.value === 'object') {
    return Object.values(roles.value).map(r => String(r).toLowerCase()).includes(role.toLowerCase());
  }
  return false;
};

const hasPermission = (permission) => {
  if (!permission || permission === '') return true;
  if (hasRole('super admin') || hasRole('admin') || hasRole('super-admin')) return true;
  return userPermissions.value.includes(permission.toLowerCase().trim());
};

const canAccessOrganisation = computed(() => hasPermission('settings organisation'));
const canAccessSystem = computed(() => hasPermission('settings system'));
const canAccessIntegrations = computed(() => hasPermission('settings integrations'));
const canAccessAdminSettings = computed(() => {
  return hasRole('super admin') || hasRole('admin') || hasRole('super-admin') || canAccessSystem.value || canAccessOrganisation.value;
});

const settings = [
  {
    key: 'profile',
    name: 'My Profile',
    subtitle: 'Personal info, phone & digital signature',
    path: '/settings/profile',
    icon: 'lucide:user',
    permission: 'settings view'
  },
  {
    key: 'organisation',
    name: 'Organisation Profile',
    subtitle: 'Branding, official stamps & corporate details',
    path: '/settings/organisation',
    icon: 'lucide:building-2',
    permission: 'settings organisation'
  },
  {
    key: 'preferences',
    name: 'Preferences',
    subtitle: 'Themes, typography & layout density',
    path: '/settings/preferences',
    icon: 'lucide:palette',
    permission: 'settings view'
  },
  {
    key: 'notifications',
    name: 'Notification Alerts',
    subtitle: 'Document, workflow & team alerts',
    path: '/settings/notifications',
    icon: 'lucide:bell-ring',
    permission: 'settings view'
  },
  {
    key: 'security',
    name: 'Security & Auth',
    subtitle: '2FA, active sessions & passwords',
    path: '/settings/security',
    icon: 'lucide:shield-check',
    permission: 'settings view'
  },
  {
    key: 'integrations',
    name: 'Integrations & Apps',
    subtitle: 'Google, Microsoft, Adobe & Webhooks',
    path: '/settings/integrations',
    icon: 'lucide:cpu',
    permission: 'settings integrations'
  },
  {
    key: 'localisation',
    name: 'Localisation',
    subtitle: 'Timezone, currency & regional formats',
    path: '/settings/localisation',
    icon: 'lucide:globe',
    permission: 'settings view'
  },
  {
    key: 'development',
    name: 'System & Development',
    subtitle: 'Diagnostics, telemetry & release team',
    path: '/settings/development',
    icon: 'lucide:terminal',
    permission: 'settings system'
  },
  {
    key: 'systemConfigurations',
    name: 'System Configurations',
    subtitle: 'Enterprise policies, storage & mail',
    path: '/settings/system-configurations',
    icon: 'lucide:sliders',
    permission: 'settings system'
  }
];

// Determine active tab key based on path or initial request
const resolveTabFromPath = (path) => {
  if (!path) return 'profile';
  const cleanPath = path.split('?')[0].toLowerCase();

  if (cleanPath.includes('organisation')) return 'organisation';
  if (cleanPath.includes('preference')) return 'preferences';
  if (cleanPath.includes('notification')) return 'notifications';
  if (cleanPath.includes('security') || cleanPath.includes('password-polic')) return 'security';
  if (cleanPath.includes('integration')) return 'integrations';
  if (cleanPath.includes('localisation')) return 'localisation';
  if (cleanPath.includes('development')) return 'development';
  if (cleanPath.includes('system-configuration')) return 'systemConfigurations';
  return 'profile';
};

const activeTabKey = ref(resolveTabFromPath(page.url));

const selectTab = (item) => {
  activeTabKey.value = item.key;
  if (window.history && window.history.replaceState) {
    window.history.replaceState(null, '', item.path);
  }
};

const visibleSettings = computed(() => {
  return settings.filter(item => {
    // Permission filter
    if (item.permission && !hasPermission(item.permission)) {
      return false;
    }
    // Search query filter
    if (searchQuery.value.trim() !== '') {
      const q = searchQuery.value.toLowerCase();
      return item.name.toLowerCase().includes(q) || item.subtitle.toLowerCase().includes(q);
    }
    return true;
  });
});

const refreshUserHandler = () => {
  router.reload({ only: ['user'] });
};

const handlePasswordPolicies = () => {
  is_passwordPolicies.value = false;
};

const handleSecurity = () => {
  is_passwordPolicies.value = true;
};

watch(() => page.url, (newUrl) => {
  if (newUrl) {
    activeTabKey.value = resolveTabFromPath(newUrl);
  }
});

onMounted(() => {
  if (page.url) {
    activeTabKey.value = resolveTabFromPath(page.url);
  }
});
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
