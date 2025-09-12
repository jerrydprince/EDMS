<template>
  <section>
    <!-- RESOLVED PAYMENT BLOCK -->
    <section class="lg:flex items-center gap-x-5 pt-6 md:pt-10">
      <div class="flex items-center border p-2 rounded-lg gap-2 text-sm mt-2 md:mt-5 lg:mt-0 shadow-sm">
        <i class="pi pi-search text-gray-300" style="font-size: 1rem"></i>
        <input type="text" v-model="searchValue" placeholder="Search Resolved Payments"
          class="w-96 h-7 border-0 outline-none text-[#98A2B3]" />
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
            <tr v-for="(item, index) in items" :key="index"
              class="hover:bg-gray-50 cursor-pointer border-b border-gray-200 h-20 text-sm">
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
                    <p class="font-medium" :style="{
                      color: item.status == 'paid' ? '#101828' : 'black',
                    }">
                      <span class="font-black">•</span> Resolved
                    </p>
                  </div>
                </div>
              </td>
              <td class="align-middle">
                <Link :href="`/finance-and-accounts/view-payment/${item.id}`">
                  <div
                    class="border border-gray-300 rounded-lg flex items-center gap-2 justify-center px-4 lg:px-0 py-3 shadow-sm text-gray-900">
                    <Icon icon="ph:eye" width="20" />
                    <p>View</p>
                  </div>
                </Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div class="mt-5">
      <pagination class="my-4"
        :totalItems="totalItems"
        :itemsPerPage="itemsPerPage"
        v-model="currentPage"
        @setPage="(page) => currentPage = page"
      />
    </div>
  </section>
</template>

<script setup>
import { ref, watch, computed, onMounted } from "vue";
import { Link } from "@inertiajs/vue3";
import pagination from "../../reusables/pagination.vue";
//import paginator from "@/reusables/paginations.vue";

const searchValue = ref("");
const currentPage = ref(1);
const itemsPerPage = ref(10);
const resolvedPayments = ref([]);
const items = ref([
  // Sample data structure; this will be populated from the API
  // {
  //   id: 1,
  //   payment_title: "Payment 1",
  //   date_created: "01 Jan 2024, 10:00 AM",
  //   status: "Resolved",
  //   assigned_to: "John Doe"
  // },
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
const totalItems = ref(0);

const getResolvedPayments = async () => {
  try {
    axios.get('/finance-and-accounts/get-resolved-payments', {
      params: {
        search: searchValue.value,
        page: currentPage.value
      }
    }).then(response => {
      resolvedPayments.value = response.data.data;
      items.value = resolvedPayments.value.map(payment => ({
        id: payment.id,
        payment_title: payment.title,
        date_created: new Date(payment.created_at).toLocaleString('en-US', {
          day: '2-digit',
          month: 'short',
          year: 'numeric',
          // hour: '2-digit',
          // minute: '2-digit',
          // hour12: true
        }),
        status: payment.status,
        assigned_to: payment.assigned_to && payment.assigned_to.length > 0 ? payment.assigned_to[0].user.last_name+" "+payment.assigned_to[0].user.first_name : 'N/A'
      }));
       currentPage.value = response.data.current_page
        itemsPerPage.value = response.data.per_page
        totalItems.value = response.data.total
    });
  } catch (error) {
    console.error("Error fetching resolved payments:", error);
  }
};

watch(searchValue, () => {
  if (searchValue.value.trim()) {
    clearTimeout(window.__searchDebounce__);
    window.__searchDebounce__ = setTimeout(() => {
      getResolvedPayments();
    }, 400);
  } else {
    getResolvedPayments();
  }
  
});

onMounted(() => {
  getResolvedPayments();
});
</script>
