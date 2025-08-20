<template>
  <section>
    <!-- RESOLVED PAYMENT BLOCK -->
    <section class="lg:flex items-center gap-x-5 pt-6 md:pt-10">
      <div
        class="flex items-center border p-2 rounded-lg gap-2 text-sm mt-2 md:mt-5 lg:mt-0 shadow-sm"
      >
        <i class="pi pi-search text-gray-300" style="font-size: 1rem"></i>
        <input
          type="text"
          v-model="searchValue"
          placeholder="Search Resolved Payments"
          class="w-96 h-7 border-0 outline-none text-[#98A2B3]"
        />
      </div>
    </section>
    <div class="relative mt-6 md:mt-10 rounded-xl">
      <div class="overflow-x-scroll lg:overflow-hidden">
        <table class="min-w-max w-full border-b border-gray-200 text-gray-500">
          <thead class="bg-gray-50">
            <tr class="text-left">
              <th class="font-medium pl-7">Payment Title</th>
              <th class="font-medium">Date Created</th>
              <th class="font-medium">Assigned to</th>
              <th class="font-medium">Status</th>
              <th class="font-medium">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(item, index) in displayedItems"
              :key="index"
              class="hover:bg-gray-50 cursor-pointer border-b border-gray-200 h-20 text-sm"
            >
              <td class="align-middle">
                <div class="pl-5">
                  <p class="text-gray-900 text-base">
                    {{ item.payment_title }}
                  </p>
                </div>
              </td>

              <td class="align-middle">
                <p>{{ item.date_created }}</p>
              </td>
              <td class="align-middle">
                <p>
                  {{ item.assigned_to }}
                </p>
              </td>
              <td class="align-middle w-auto">
                <div class="flex">
                  <div class="bg-gray-100 rounded-2xl py-2 px-4 text-center">
                    <p
                      class="font-medium"
                      :style="{
                        color: item.status === 'Resolved' ? '#101828' : 'black',
                      }"
                    >
                      <span class="font-black">•</span> {{ item.status }}
                    </p>
                  </div>
                </div>
              </td>
              <td class="align-middle">
                <router-link
                  :to="{
                    name: 'Payment Details',
                    params: { paymentId: item.id },
                  }"
                >
                  <div
                    class="border border-gray-300 rounded-lg flex items-center gap-2 justify-center px-4 lg:px-0 py-3 shadow-sm text-gray-900"
                  >
                    <Icon icon="ph:eye" width="20" />
                    <p>View</p>
                  </div>
                </router-link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div class="mt-5">
      <!-- <paginator
        :totalItems="totalItems"
        :itemsPerPage="itemsPerPage"
        v-model="currentPage"
      /> -->
    </div>
  </section>
</template>

<script setup>
import { ref, watch, computed } from "vue";
//import paginator from "@/reusables/paginations.vue";

const searchValue = ref("");
const currentPage = ref(1);
const itemsPerPage = 10;
const items = ref([
  {
    id: 1,
    payment_title: "Office Equipment Procurement",
    date_created: "15 Nov 2023 9:24 PM",
    status: "Resolved",
    assigned_to: "Deputy Director of Accounting",
  },
  {
    id: 2,
    payment_title: "Q2 Staff Welfare Disbursement",
    date_created: "12 Jan 2024 7:24 PM",
    status: "Resolved",
    assigned_to: "Deputy Director of Payment",
  },
  {
    id: 3,
    payment_title: "Building Renovation Payment",
    date_created: "1 May 2025 9:24 PM",
    status: "Resolved",
    assigned_to: "Deputy Director of Accounting",
  },
  {
    id: 4,
    payment_title: "Generator Maintenance Fee",
    date_created: "15 Jun 2024 9:24 PM",
    status: "Resolved",
    assigned_to: "Deputy Director of Accounting",
  },
  {
    id: 5,
    payment_title: "Field Research Logistics",
    date_created: "3 Sep 2023 9:24 AM",
    status: "Resolved",
    assigned_to: "Deputy Director of Payment",
  },
  {
    id: 6,
    payment_title: "January Electricity Bill",
    date_created: "24 Dec 2024 9:24 PM",
    status: "Resolved",
    assigned_to: "Deputy Director of Payment",
  },
  {
    id: 7,
    payment_title: "Building Renovation Payment",
    date_created: "30 Oct 2023 10:24 PM",
    status: "Resolved",
    assigned_to: "Deputy Director of Accounting",
  },
  {
    id: 8,
    payment_title: "Office Equipment Procurement",
    date_created: "5 Mar 2023 19:24 PM",
    status: "Resolved",
    assigned_to: "Deputy Director of Accounting",
  },
  {
    id: 9,
    payment_title: "January Electricity Bill",
    date_created: "15 Nov 2023 9:24 AM",
    status: "Resolved",
    assigned_to: "Deputy Director of Payment",
  },
]);
const pageStart = ref(1);
const pageEnd = ref(1);
const displayedItems = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;

  pageStart.value = startIndex;
  pageEnd.value = endIndex;

  return items.value.slice(startIndex, endIndex);
});
const totalItems = computed(() => items.value.length);
</script>
