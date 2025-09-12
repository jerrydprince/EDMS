<template>
  <main class="min-h-[100vh] p-5 md:p-7">
    <div class="mb-8">
      <p class="text-xl font-medium md:font-normal md:text-3xl text-primary">
        Payment Initiation Dashboard
      </p>
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
    <div class="">
     <RegistryFiles v-if="active == 0 && roles.length > 0 && roles.includes('directors')" :payment="payment"/>
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
  if (roles.value.length > 0 && !roles.value.includes('directors')) {
    return active.value+ 1
  } else {
    return active.value
  }
};


const filteredItems = computed(() =>
  items.value.filter(item => !(item.label === 'Files from Registry' && !roles.value.includes('directors')))
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
