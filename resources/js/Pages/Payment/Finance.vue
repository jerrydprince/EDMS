<template>
  <main class="min-h-[100vh] p-5 md:p-7 bg-slate-50/60">
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2 mb-1">
          <span class="px-2.5 py-0.5 text-xs font-bold font-mono uppercase bg-emerald-100 text-emerald-800 rounded border border-emerald-200">
            PFM-EVS Treasury Engine
          </span>
          <span v-if="userSectionTitle" class="px-2.5 py-0.5 text-xs font-medium bg-indigo-50 text-indigo-700 rounded border border-indigo-200">
            {{ userSectionTitle }}
          </span>
        </div>
        <h1 class="text-xl font-bold md:text-3xl text-gray-900 tracking-tight">
          Finance & Accounts Operations Desk
        </h1>
        <p class="text-xs md:text-sm text-gray-500 mt-1">
          Federal Treasury Single Account (TSA) & Electronic Payment Voucher Processing
        </p>
      </div>

      <!-- Quick Treasury Action Links -->
      <div class="flex flex-wrap items-center gap-2">
        <Link href="/finance-and-accounts/vote-book" class="px-3.5 py-2 bg-white hover:bg-indigo-50/60 border border-indigo-200/80 rounded-xl text-xs font-semibold text-indigo-900 shadow-2xs hover:shadow-xs transition-all flex items-center gap-2">
          <div class="w-5 h-5 rounded-md bg-indigo-100/70 flex items-center justify-center text-indigo-600">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
          </div>
          <span>Vote Books</span>
        </Link>
        <Link href="/finance-and-accounts/cashbook" class="px-3.5 py-2 bg-white hover:bg-blue-50/60 border border-blue-200/80 rounded-xl text-xs font-semibold text-blue-900 shadow-2xs hover:shadow-xs transition-all flex items-center gap-2">
          <div class="w-5 h-5 rounded-md bg-blue-100/70 flex items-center justify-center text-blue-600">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
          </div>
          <span>Cash Book</span>
        </Link>
        <Link href="/finance-and-accounts/ledger" class="px-3.5 py-2 bg-white hover:bg-purple-50/60 border border-purple-200/80 rounded-xl text-xs font-semibold text-purple-900 shadow-2xs hover:shadow-xs transition-all flex items-center gap-2">
          <div class="w-5 h-5 rounded-md bg-purple-100/70 flex items-center justify-center text-purple-600">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
          </div>
          <span>General Ledger</span>
        </Link>
      </div>
    </div>
    <div>
      <TabMenu
        v-model:activeIndex="active"
        :model="filteredItems"
        :pt="{
          root: { class: 'text-primary' },
          inkbar: { class: 'text-primary' },
          action: ({ props, context, state }) => ({
            class: `${context.index === state.d_activeIndex
              ? 'text-gray-900 border-b-2 border-solid border-primary cursor-pointer'
              : 'text-gray-500 cursor-pointer'
            } p-1 md:p-3 pl-1 mr-7 text-gray-500 -mb-[2px]`,
          }),
        }"
      >
        <template #item="{ item, props }">
          <a v-bind="props.action" class="w-full flex items-center gap-1 md:gap-2 text-sm md:text-base">
            <span class="font-normal">{{ item.label }}</span>
          </a>
        </template>
      </TabMenu>

    </div>
    <div class="mt-4">
     <RegistryFiles v-if="active == 0 && roles.length > 0 && roles.includes('director')" :payment="payment"/>
     <ActivePayments v-if="getActiveIndex()== 1"/>
     <ResolvedPayments v-if="getActiveIndex() == 2"/>
    </div>
  </main>
</template>

<script setup>
import { ref, defineProps, watch, computed, onMounted } from "vue";
import {Link, usePage} from '@inertiajs/vue3'
import RegistryFiles from "./RegistryFiles.vue";
import ActivePayments from "./ActivePayments.vue";
import ResolvedPayments from "./ResolvedPayments.vue";
const active = ref(0);
const props = defineProps(["payment"])
const page = usePage();
const roles = computed(() => page.props.roles || []);
const user = page.props.user;

const userSectionTitle = computed(() => {
  const u = user || {};
  if (u.section_id === 81) return 'Section 81: Other Charges';
  if (u.section_id === 82) return 'Section 82: Staff Advances';
  if (u.section_id === 83) return 'Section 83: Checking Section';
  if (u.section_id === 84) return 'Section 84: Pre-Payment Internal Audit';
  if (u.section_id === 85) return 'Section 85: Cash Office / CPO';
  if (u.unit_id === 11) return 'Unit 11: Final Accounts';
  if (u.unit_id === 61) return 'Unit 61: Finance & Accounts Operations';
  return null;
});

const items = ref([
  {
    label: "Files from Registry",
    link: "/f-and-a/registry-files",
  },
  {
    label: "Active Payments",
    link: "/f-and-a/active-payments",
  },
  {
    label: "Resolved Payments",
    link: "/payment/resolved-payments",
  },
]);

const getActiveIndex = () => {
  if (roles.value.length > 0 && !roles.value.includes('director')) {
    return active.value+ 1
  } else {
    return active.value
  }
};


const filteredItems = computed(() =>
  items.value.filter(item => !(item.label === 'Files from Registry' && !roles.value.includes('director')))
)

onMounted(() => {
  
  // if(roles.value.length > 0 && roles.value.includes('director')) {
  //   active.value = 0; // Default to Registry Files if user is a director
  // } else {
  //   active.value = 0; // Default to Active Payments if not a director
  // }
});

</script>

<style></style>
