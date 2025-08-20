<template>
  <main class="p-5 md:p-7">
    <div class="mb-3 lg:mb-8">
      <p class="text-2xl font-medium md:font-normal md:text-3xl text-primary">Edit Selection</p>
    </div>
    <section>
      <section class="flex md:flex-row flex-col md:items-center gap-x-5 gap-y-3 lg:pt-10 pb-4">
        <div
          class="flex items-center border p-2 rounded-lg gap-2 text-sm mt-5 lg:mt-0 shadow-sm"
        >
          <i class="pi pi-search text-gray-300" style="font-size: 1rem"></i>
          <input
            type="text"
            v-model="searchValue"
            placeholder="Search file"
            class="w-96 h-7 border-0 outline-none text-[#98A2B3]"
          />
        </div>
        <section class="flex items-center gap-x-5">
          <div class="bg-gray-100 flex items-center rounded-lg p-1 shadow-sm">
            <button
              class="bg-white shadow-md border-gray-300 rounded-lg py-2 px-8"
            >
              <p>All</p>
            </button>
            <button
              class="flex gap-1 items-center text-gray-400 rounded-lg py-1 px-6"
            >
              <p>Type</p>
              <Icon icon="icon-park-outline:down" width="16" />
            </button>
          </div>
          <button
            class="flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-lg py-3 px-4 shadow-sm"
          >
            <Icon icon="ion:arrow-up" width="20" />
            <p>Name</p>
          </button>
        </section>
      </section>
      <div class="mt-5 lg:mt-10 rounded-xl">
        <div class="overflow-x-scroll lg:overflow-hidden">
          <table
            class="min-w-max w-full border-b border-gray-200 text-gray-500"
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
                <th class="font-medium">File name</th>
                <th class="font-medium">Date Uploaded</th>
                <th class="font-medium">File Size</th>
                <th class="font-medium">Uploaded By</th>
                <th class="font-medium">Actions</th>
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
                  <div class="flex items-center gap-2">
                    <div class="flex items-center gap-3">
                      <div class="p-2 rounded-full bg-gray-50">
                        <Icon
                          v-if="item.file?.extension === 'ppt'"
                          icon="vscode-icons:file-type-powerpoint"
                          width="25"
                        />
                        <Icon
                          v-else-if="item.file?.extension === 'docx'"
                          icon="vscode-icons:file-type-excel"
                          width="25"
                        />
                        <Icon
                          v-else-if="item.file?.extension === 'pdf'"
                          icon="vscode-icons:file-type-pdf2"
                          width="25"
                        />
                        <Icon
                          v-else
                          icon="material-icon-theme:pdf"
                          width="25"
                        />
                      </div>
                      <p class="text-gray-900 text-base">
                        {{ item.file?.name }}
                      </p>
                    </div>
                  </div>
                </td>

                <td class="align-middle">
                  <p>{{ item.date_uploaded }}</p>
                </td>
                <td class="align-middle">
                  <p>{{ item.file?.size }}</p>
                </td>
                <td class="align-middle">
                  <p>
                    {{ item.uploaded_by }}
                  </p>
                </td>
                <td class="align-middle">
                  <div>
                    <Icon icon="ph:eye" width="20" />
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div
          class="absolute bottom-[5%] p-2 rounded-xl flex items-center left-[5%] md:left-[45%] bg-[#052B0A] justify-between"
          v-if="checkedCount"
        >
          <div class="flex items-center gap-2 text-white text-sm ml-3">
            <p class=" ">
              {{ checkedCount }}
              selected
            </p>
          </div>
          <button
            @click="confirmSelection()"
            class="ml-16 bg-white text-[#052B0A] text-xs px-4 py-3 rounded-md flex items-center gap-2"
          >
            <p class="">Confirm Selection</p>
            <Icon icon="ri:exchange-funds-fill" width="18" />
          </button>
          <button class="cursor-pointer text-white px-3" @click="removeProfile">
            <Icon icon="mdi:times" />
          </button>
        </div>
      </div>
    </section>
  </main>
</template>

<script setup>
import { ref, watch, computed } from "vue";

import { useToast } from "primevue/usetoast";

const router = useRouter();
const route = useRoute();
const toast = useToast();
const searchValue = ref("");
const items = ref([
  {
    id: 1,
    file: {
      extension: "pdf",
      name: "Communication.pdf",
      size: "150 KB",
    },
    date_uploaded: "15 Nov 23",
    file_size: "150 KB",
    uploaded_by: "Mary Westland",
  },
  {
    id: 2,
    file: {
      extension: "docx",
      name: "Payment.docx",
      size: "120 MB",
    },
    date_uploaded: "12 Jan 24",
    file_size: "120 MB",
    uploaded_by: "Ahmed Tinubu",
  },
  {
    id: 3,
    file: {
      extension: "ppt",
      name: "Inugural.ppt",
      size: "230 KB",
    },
    date_uploaded: "1 May 25",
    file_size: "230 KB",
    uploaded_by: "Favour Mike",
  },
  {
    id: 4,
    file: {
      extension: "ppt",
      name: "Ikeja Power.ppt",
      size: "10 MB",
    },
    date_uploaded: "15 Jun 24",
    file_size: "10 MB",
    uploaded_by: "John James",
  },
  {
    id: 5,
    file: {
      extension: "docx",
      name: "Communication.docx",
      size: "2 MB",
    },
    date_uploaded: "3 Sep 23",
    file_size: "2 MB",
    uploaded_by: "Lamie Rock",
  },
  {
    id: 6,
    file: {
      extension: "ppt",
      name: "Federal Int.ppt",
      size: "500 KB",
    },
    date_uploaded: "24 Dec 24",
    file_size: "500 KB",
    uploaded_by: "Aug May",
  },
  {
    id: 7,
    file: {
      extension: "pdf",
      name: "Communication.pdf",
      size: "209 KB",
    },
    date_uploaded: "30 Oct 23",
    file_size: "209 KB",
    uploaded_by: "Doe John",
  },
  {
    id: 8,
    file: {
      extension: "csv",
      name: "Communication.csv",
      size: "10 KB",
    },
    date_uploaded: "5 Mar 23",
    file_size: "10 KB",
    uploaded_by: "Lamie Rock",
  },
  {
    id: 9,
    file: {
      extension: "pdf",
      name: "Communication.pdf",
      size: "1 MB",
    },
    date_uploaded: "15 Nov 23",
    file_size: "1 MB",
    uploaded_by: "Dave Miye",
  },
]);

const displayedItems = computed(() => {
  return items.value;
});

const checkedItems = ref([1, 2, 5]);

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

const confirmSelection = () => {
  // All logics as to saving the files here
  router.push({
    name: "Payment Details",
    params: { paymentId: route.params.paymentId },
    query: { isFileEdited: true },
  });
  // Toast for file edit selection
  toast.add({
    severity: "success",
    summary: "Documents Selected",
    detail: "Changes saved and documents reselected.",
    life: 3000,
  });
};
</script>
