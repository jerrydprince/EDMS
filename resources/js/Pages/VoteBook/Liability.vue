<template>
  <section class="px-8 py-7">
    <!-- HEADER SECTION STARTS -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between">
      <div class="flex items-center gap-3">
        <p
          class="bg-[#F2F4F7] px-10 py-2 rounded-full border border-solid border-[#0B6217] drop-shadow-md"
        >
          Liability
        </p>
        <p
          @click="router.push('/votebook/expenditure')"
          class="bg-[#F2F4F7] px-10 py-2 rounded-full border border-solid border-transparent hover:border-[#0B6217] hover:drop-shadow-md"
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
                <th class="font-medium">Voucher No</th>
                <th class="font-medium">Line No</th>
                <th class="font-medium">Particulars</th>
                <th class="font-medium">Payments</th>
                <th class="font-medium">Total</th>
                <th class="font-medium">Balance</th>
                <th class="font-medium">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(item, index) in displayedItems"
                :key="index"
                @click="router.push(`/votebook/liability/${item.id}`)"
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
                  <p>{{ item.voucherNo }}</p>
                </td>
                <td class="align-middle">
                  <p>{{ item.lineNo }}</p>
                </td>
                <td class="align-middle truncate">
                  <p>{{ item.particulars }}</p>
                </td>
                <td class="align-middle">
                  <p>{{ item.payment || "---" }}</p>
                </td>
                <td class="align-middle">
                  <p>{{ item.totalPayment || "---" }}</p>
                </td>
                <td class="align-middle">
                  <p>{{ item.balance }}</p>
                </td>
                <td class="align-middle">
                  <div class="flex">
                    <button
                      class="border border-gray-300 rounded-lg py-2 px-5"
                      :disabled="item.status === 'Retired'"
                    >
                      <p
                        class="text-gray-700"
                        :class="
                          item.status == 'Retired'
                            ? 'opacity-50'
                            : 'opacity-100'
                        "
                      >
                        {{ item.status === "Retired" ? "Retired" : "Retire" }}
                      </p>
                    </button>
                  </div>
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
    id: 1,
    date: "15-01-2025, 09:00 AM",
    voucherNo: "REF001",
    lineNo: "20",
    particulars: "Payment for office",
    payment: 50000,
    totalPayment: null,
    balance: 300000,
    status: "Retired",
  },
  {
    id: 2,
    date: "12-01-2025, 09:00 AM",
    voucherNo: "REF002",
    lineNo: "14",
    particulars: "Service fees for ...",
    payment: 25000,
    totalPayment: 120000,
    balance: 67000,
    status: "Not Retired",
  },
  {
    id: 3,
    date: "13-01-2025, 09:00 AM",
    voucherNo: "REF004",
    lineNo: "10",
    particulars: "Client payment...",
    payment: 300000,
    totalPayment: null,
    balance: 50000,
    status: "Not Retired",
  },
  {
    id: 4,
    date: "12-01-2025, 08:00 PM",
    voucherNo: "REF003",
    lineNo: "02",
    particulars: "Refund for cancellation",
    payment: 25000,
    totalPayment: 25000,
    balance: 300000,
    status: "Not Retired",
  },
  {
    id: 5,
    date: "12-01-2025, 08:00 PM",
    voucherNo: "REF007",
    lineNo: "40",
    particulars: "Client payment...",
    payment: 300000,
    totalPayment: null,
    balance: 50000,
    status: "Retired",
  },
  {
    id: 6,
    date: "13-01-2025, 09:00 AM",
    voucherNo: "REF006",
    lineNo: "10",
    particulars: "Refund for cancellation",
    payment: 50000,
    totalPayment: 50000,
    balance: 25000,
    status: "Not Retired",
  },
  {
    id: 7,
    date: "12-01-2025, 08:00 PM",
    voucherNo: "REF004",
    lineNo: "50",
    particulars: "Client payment...",
    payment: 25000,
    totalPayment: null,
    balance: 25000,
    status: "Retired",
  },
  {
    id: 8,
    date: "13-01-2025, 09:00 AM",
    voucherNo: "REF001",
    lineNo: "01",
    particulars: "Client payment...",
    payment: 67000,
    totalPayment: 67000,
    balance: 25000,
    status: "Retired",
  },
  {
    id: 9,
    date: "12-01-2025, 08:00 PM",
    voucherNo: "REF001",
    lineNo: "10",
    particulars: "Monthly subscription",
    payment: 50000,
    totalPayment: null,
    balance: 50000,
    status: "Retired",
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
