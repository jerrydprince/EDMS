<template>
  <main class="px-7">
    <div class="py-7">
      <h1 class="font-semibold text-3xl text-[#1D2939] dark:text-white">
        AGF Registry Dashboard
      </h1>
      <p class="text-gray-500">
        Welcome to your centralized control panel. Oversee incoming and outgoing dispatches, track active registry operations, and manage files effectively.
      </p>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-white border rounded-xl p-6 shadow-sm flex items-center gap-4">
        <div class="p-3 bg-blue-50 text-blue-600 rounded-lg">
          <Icon icon="mdi:folder-multiple" width="30" />
        </div>
        <div>
          <h2 class="text-gray-500 text-sm font-medium">Registry Files</h2>
          <p class="text-2xl font-bold text-gray-800">Manage Files</p>
          <Link href="/dashboard/agf-registry" class="text-sm text-blue-600 hover:underline mt-1 inline-block">Go to Registry &rarr;</Link>
        </div>
      </div>

      <!-- Card 2: Workflow Tasks or Mail Dispatches -->
      <div v-if="hasPermission('workflow view')" class="bg-white border rounded-xl p-6 shadow-sm flex items-center gap-4">
        <div class="p-3 bg-green-50 text-green-600 rounded-lg">
          <Icon icon="mdi:clipboard-check-multiple-outline" width="30" />
        </div>
        <div>
          <h2 class="text-gray-500 text-sm font-medium">My Tasks</h2>
          <p class="text-2xl font-bold text-gray-800">Pending Actions</p>
          <Link href="/dashboard/workflow" class="text-sm text-green-600 hover:underline mt-1 inline-block">View Inbox &rarr;</Link>
        </div>
      </div>
      <div v-else-if="hasPermission('mails view')" class="bg-white border rounded-xl p-6 shadow-sm flex items-center gap-4">
        <div class="p-3 bg-amber-50 text-amber-600 rounded-lg">
          <Icon icon="mdi:email-outline" width="30" />
        </div>
        <div>
          <h2 class="text-gray-500 text-sm font-medium">Incoming Mails</h2>
          <p class="text-2xl font-bold text-gray-800">Mail Dispatch</p>
          <Link href="/dashboard/mails" class="text-sm text-amber-600 hover:underline mt-1 inline-block">View Mails &rarr;</Link>
        </div>
      </div>
      <div v-else class="bg-white border rounded-xl p-6 shadow-sm flex items-center gap-4">
        <div class="p-3 bg-indigo-50 text-indigo-600 rounded-lg">
          <Icon icon="octicon:git-branch-24" width="30" />
        </div>
        <div>
          <h2 class="text-gray-500 text-sm font-medium">Documents</h2>
          <p class="text-2xl font-bold text-gray-800">Document Hub</p>
          <Link href="/dashboard/document-management" class="text-sm text-indigo-600 hover:underline mt-1 inline-block">Browse &rarr;</Link>
        </div>
      </div>

      <!-- Card 3: Workflow Tracking or Registry Activity -->
      <div v-if="hasPermission('workflow view')" class="bg-white border rounded-xl p-6 shadow-sm flex items-center gap-4">
        <div class="p-3 bg-purple-50 text-purple-600 rounded-lg">
          <Icon icon="mdi:timeline-check" width="30" />
        </div>
        <div>
          <h2 class="text-gray-500 text-sm font-medium">Workflow Tracking</h2>
          <p class="text-2xl font-bold text-gray-800">Track Files</p>
          <Link href="/dashboard/agf-track-tasks" class="text-sm text-purple-600 hover:underline mt-1 inline-block">Track Progress &rarr;</Link>
        </div>
      </div>
      <div v-else class="bg-white border rounded-xl p-6 shadow-sm flex items-center gap-4">
        <div class="p-3 bg-purple-50 text-purple-600 rounded-lg">
          <Icon icon="mdi:magnify-scan" width="30" />
        </div>
        <div>
          <h2 class="text-gray-500 text-sm font-medium">File Tracking</h2>
          <p class="text-2xl font-bold text-gray-800">Registry Search</p>
          <Link href="/dashboard/agf-registry" class="text-sm text-purple-600 hover:underline mt-1 inline-block">View Records &rarr;</Link>
        </div>
      </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-6 justify-between">
      <div class="w-full lg:w-[48%] border border-solid rounded-lg bg-white">
        <div class="flex gap-4 items-center border-b px-5 py-4 bg-gray-50 rounded-t-lg">
          <div class="p-2 border rounded-full bg-white text-blue-600">
            <Icon icon="mdi:information-outline" width="24" />
          </div>
          <h1 class="font-semibold text-xl text-[#1D2939] dark:text-white">
            Registry Guidelines
          </h1>
        </div>

        <div class="px-5 pt-5 pb-7">
          <div class="space-y-4">
            <div class="flex items-start gap-4">
              <div class="mt-1 bg-blue-100 text-blue-600 p-1 rounded-full"><Icon icon="mdi:check" width="16" /></div>
              <div class="text-[#475467]">
                <h1 class="font-medium text-lg text-gray-800">File Intake & Tagging:</h1>
                <p class="opacity-80 mt-1">Ensure all newly received physical files are logged immediately into the Registry with accurate metadata.</p>
              </div>
            </div>
            <div class="flex items-start gap-4">
              <div class="mt-1 bg-blue-100 text-blue-600 p-1 rounded-full"><Icon icon="mdi:check" width="16" /></div>
              <div class="text-[#475467]">
                <h1 class="font-medium text-lg text-gray-800">Registry Dispatches:</h1>
                <p class="opacity-80 mt-1">When routing a file out of the registry, verify the destination and ensure the recipient department/officer is accurately recorded.</p>
              </div>
            </div>
            <div class="flex items-start gap-4">
              <div class="mt-1 bg-blue-100 text-blue-600 p-1 rounded-full"><Icon icon="mdi:check" width="16" /></div>
              <div class="text-[#475467]">
                <h1 class="font-medium text-lg text-gray-800">Records Archiving:</h1>
                <p class="opacity-80 mt-1">Check registry logs regularly to ensure completed documents are appropriately archived or dispatched.</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="w-full lg:w-[48%] border border-solid rounded-lg bg-white">
        <div class="flex gap-4 items-center border-b px-5 py-4 bg-gray-50 rounded-t-lg">
          <div class="p-2 border rounded-full bg-white text-green-600">
            <Icon icon="mdi:lightning-bolt-outline" width="24" />
          </div>
          <h1 class="font-semibold text-xl text-[#1D2939] dark:text-white">
            Quick Actions
          </h1>
        </div>

        <div class="px-5 pt-5 pb-7 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <Link href="/dashboard/admin-registry" class="flex flex-col items-center justify-center p-6 border rounded-xl hover:border-primary hover:bg-gray-50 transition-all text-center">
                <Icon icon="mdi:file-document-plus-outline" width="36" class="text-primary mb-3" />
                <span class="font-medium">New Registry Entry</span>
            </Link>

            <Link v-if="hasPermission('workflow view')" href="/dashboard/workflow" class="flex flex-col items-center justify-center p-6 border rounded-xl hover:border-green-600 hover:bg-green-50 transition-all text-center group">
                <Icon icon="mdi:inbox-arrow-down" width="36" class="text-green-600 mb-3 group-hover:animate-bounce" />
                <span class="font-medium">Workflow Inbox</span>
            </Link>

            <Link v-else-if="hasPermission('mails view')" href="/dashboard/mails" class="flex flex-col items-center justify-center p-6 border rounded-xl hover:border-amber-600 hover:bg-amber-50 transition-all text-center group">
                <Icon icon="mdi:email-outline" width="36" class="text-amber-600 mb-3" />
                <span class="font-medium">Mails Overview</span>
            </Link>

            <Link v-if="hasPermission('workflow view')" href="/dashboard/agf-track-tasks" class="flex flex-col items-center justify-center p-6 border rounded-xl hover:border-purple-600 hover:bg-purple-50 transition-all text-center">
                <Icon icon="mdi:magnify-scan" width="36" class="text-purple-600 mb-3" />
                <span class="font-medium">Track Operations</span>
            </Link>

            <Link v-else href="/dashboard/agf-registry" class="flex flex-col items-center justify-center p-6 border rounded-xl hover:border-purple-600 hover:bg-purple-50 transition-all text-center">
                <Icon icon="mdi:folder-search-outline" width="36" class="text-purple-600 mb-3" />
                <span class="font-medium">Search Registry</span>
            </Link>

            <Link href="/dashboard/support" class="flex flex-col items-center justify-center p-6 border rounded-xl hover:border-orange-600 hover:bg-orange-50 transition-all text-center">
                <Icon icon="mdi:lifebuoy" width="36" class="text-orange-600 mb-3" />
                <span class="font-medium">Support & Help</span>
            </Link>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import Layout from '../../Shared/Layout.vue';

defineOptions({
  layout: Layout
});

const page = usePage();
const permissions = computed(() => page.props.permissions || []);
const role = computed(() => {
  const r = page.props.user_roles;
  if (!r) return [];
  if (Array.isArray(r)) return r;
  return [r];
});

const hasRole = (targetRole) => {
  if (!targetRole) return false;
  const target = targetRole.toLowerCase().trim();
  return role.value.some(r => {
    const name = (typeof r === 'string' ? r : (r?.name || '')).toLowerCase().trim();
    return name === target || name === target + 's' || name + 's' === target;
  });
};

const hasPermission = (permit) => {
  if (!permit) return true;
  if (hasRole('super admin') || hasRole('admin')) return true;
  if (permissions.value && permissions.value.length > 0) {
    const permitNorm = permit.toLowerCase().trim();
    if (typeof permissions.value[0] === 'string') {
      return permissions.value.some(p => typeof p === 'string' && p.toLowerCase().trim() === permitNorm);
    } else {
      return permissions.value.some(p => p && p.name && p.name.toLowerCase().trim() === permitNorm);
    }
  }
  return false;
};
</script>
