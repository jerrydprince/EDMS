<template>
  <section class="px-8 py-7">
    <!-- HEADER SECTION STARTS -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between">
      <div class="flex items-center gap-3">
        <p
          @click="router.push('/votebook/liability')"
          class="bg-[#F2F4F7] px-10 py-2 rounded-full border border-solid border-transparent hover:border-[#0B6217] hover:drop-shadow-md"
        >
          Liability
        </p>
        <p
          class="bg-[#F2F4F7] px-10 py-2 rounded-full border border-solid border-[#0B6217] drop-shadow-md"
        >
          Expenditure
        </p>
      </div>
      <div
        class="flex items-center border p-2 rounded-lg gap-2 text-sm mt-5 lg:mt-0 shadow-sm"
      >
        <i class="pi pi-search text-gray-300 ml-1" style="font-size: 1rem"></i>
        <input
          type="text"
          v-model="searchValue"
          placeholder="Search Records"
          class="w-72 md:w-96 h-7 border-0 outline-none text-[#98A2B3]"
        />
      </div>
    </div>
    <!-- HEADER SECTION ENDS -->

    <!-- TABLE STARTS -->
    <section>
      <div class="mt-7 rounded-xl">
        <div class="overflow-x-scroll lg:overflow-hidden">
          <table
            class="rounded-lg min-w-max w-full border border-gray-200 text-gray-500 text-sm"
          >
            <thead class="bg-gray-50">
              <tr class="text-left">
                <th class="py-3 pl-5">
                  <Checkbox
                    v-model="selectAll"
                    @update:modelValue="toggleAll"
                    class="h-5 w-5"
                  />
                </th>
                <th class="font-medium">Date</th>
                <th class="font-medium">Liability Ref</th>
                <th class="font-medium">Remarks</th>
                <th class="font-medium">Incurred</th>
                <th class="font-medium">Cleared</th>
                <th class="font-medium">Total Outstanding</th>
                <th class="font-medium">Balance</th>
                <th class="font-medium">Line No</th>
                <th class="font-medium">Line No</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(item, index) in displayedItems"
                :key="index"
                class="hover:bg-gray-50 cursor-pointer border-b border-gray-200 h-20 text-sm"
              >
                <td class="align-middle pl-5">
                  <div class="flex items-center gap-2">
                    <Checkbox v-model="checkedItems[index]" class="h-5 w-5" />
                  </div>
                </td>

                <td class="align-middle">
                  <p class="text-gray-900">
                    {{ item.date }}
                  </p>
                </td>

                <td class="align-middle">
                  <p>{{ item.liabilityRef }}</p>
                </td>
                <td class="align-middle truncate">
                  <p>{{ item.remarks }}</p>
                </td>
                <td class="align-middle">
                  <p>{{ item.incurred }}</p>
                </td>
                <td class="align-middle">
                  <p>{{ item.cleared || "---" }}</p>
                </td>
                <td class="align-middle">
                  <p>{{ item.totalOutstanding || "---" }}</p>
                </td>
                <td class="align-middle">
                  <p>{{ item.balance }}</p>
                </td>
                <td class="align-middle">
                  <p>{{ item.lineNo1 }}</p>
                </td>
                <td class="align-middle">
                  <p>{{ item.lineNo2 }}</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div
          class="absolute bottom-[5%] p-2 rounded-xl flex items-center left-[45%] bg-[#052B0A] justify-between"
          v-if="checkedCount"
        >
          <div class="flex items-center gap-2 text-white text-sm ml-3">
            <p class=" ">
              {{ checkedCount }}
              selected
            </p>
          </div>
          <button
            @click=""
            class="ml-16 bg-white text-[#052B0A] text-xs px-4 py-3 rounded-md flex items-center gap-2"
          >
            <p>Generate Report</p>
            <Icon icon="mdi:file-text-outline" width="18" />
          </button>
          <button class="cursor-pointer text-white px-3" @click="removeProfile">
            <Icon icon="mdi:times" />
          </button>
        </div>
      </div>
    </section>
    <!-- TABLE ENDS -->
  </section>
</template>
<script setup>
import { ref, computed } from "vue";

const searchValue = ref("");

const items = ref([
  {
    date: "15-01-2025, 09:00 AM",
    liabilityRef: "REF001",
    remarks: "Payment for office",
    incurred: 50000,
    cleared: null,
    totalOutstanding: null,
    balance: 300000,
    lineNo1: 14,
    lineNo2: 20,
  },
  {
    date: "12-01-2025, 09:00 AM",
    liabilityRef: "REF002",
    remarks: "Service fees for client",
    incurred: 25000,
    cleared: 120000,
    totalOutstanding: 120000,
    balance: 67000,
    lineNo1: 17,
    lineNo2: 2,
  },
  {
    date: "13-01-2025, 09:00 AM",
    liabilityRef: "REF004",
    remarks: "Client payment received",
    incurred: 300000,
    cleared: null,
    totalOutstanding: null,
    balance: 50000,
    lineNo1: 24,
    lineNo2: 17,
  },
  {
    date: "12-01-2025, 08:00 PM",
    liabilityRef: "REF003",
    remarks: "Refund for cancellation",
    incurred: 25000,
    cleared: 25000,
    totalOutstanding: 25000,
    balance: 300000,
    lineNo1: 20,
    lineNo2: 1,
  },
  {
    date: "12-01-2025, 08:00 PM",
    liabilityRef: "REF007",
    remarks: "Client payment received",
    incurred: 300000,
    cleared: null,
    totalOutstanding: null,
    balance: 50000,
    lineNo1: 14,
    lineNo2: 24,
  },
  {
    date: "13-01-2025, 09:00 AM",
    liabilityRef: "REF006",
    remarks: "Refund for cancellation",
    incurred: 50000,
    cleared: 50000,
    totalOutstanding: 50000,
    balance: 25000,
    lineNo1: 2,
    lineNo2: 14,
  },
  {
    date: "12-01-2025, 08:00 PM",
    liabilityRef: "REF004",
    remarks: "Client payment received",
    incurred: 25000,
    cleared: null,
    totalOutstanding: null,
    balance: 25000,
    lineNo1: 1,
    lineNo2: 14,
  },
]);

const displayedItems = computed(() => {
  return items.value;
});

const checkedItems = ref(new Array(displayedItems.value.length).fill(false));

const selectAll = ref(false);

const toggleAll = () => {
  const newState = selectAll.value;
  checkedItems.value = checkedItems.value.map(() => newState);
};

// Checked files Logic
const checkedCount = computed(() => {
  return checkedItems.value.filter((checked) => checked).length;
});

const removeProfile = () => {
  checkedItems.value = [];
};
</script>
