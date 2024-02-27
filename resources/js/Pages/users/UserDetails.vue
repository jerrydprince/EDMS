<template>
  <main>
    <div class="lg:px-7 px-5 flex items-center justify-between pt-5">
      <div class="flex items-center gap-5">
        <div class="border rounded-md p-3 py-4 cursor-pointer" @click="goBack">
            <Icon icon="formkit:arrowleft" width="30" />
        </div>
        <div>
          <p class="text-3xl text-[#1D2939]">{{ data.name }}</p>
          <p class="text-gray-500 text-sm w-[70%]">{{ data.description }}</p>
        </div>
      </div>
      <div class="flex items-center gap-4">
        <button
          class="flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-4"
        >
          <Icon icon="material-symbols:download" width="20" />
          <p>Download CSV</p>
        </button>

          <button
            class="flex gap-2 items-center text-white bg-[#2F68C5] rounded-lg py-2 px-4"
          >
            <Icon icon="material-symbols:add" width="20" />
            <p>Add User</p>
          </button>
      
      </div>
    </div>
    <Divider class=" " />
     

    <section class=" flex items-center justify-end pr-7">
        <div
        class="flex items-center border p-2 rounded-lg gap-2 text-sm mt-5 lg:mt-0"
      >
        <i class="pi pi-search text-gray-300" style="font-size: 1rem"></i>
        <Input
          type="text"
          v-model="value"
          placeholder="Search Users"
          class="w-96 border-0 outline-none text-[#98A2B3]"
        />
      </div>
    </section>

    <Divider class=" " />


    <div class=" mt-5 pl-7">
        <DataTable
              v-model:selection="selectedProduct"
              :rows="8"
              paginator
              :value="data.table"
              dataKey="id"
              :pt="{
                table: {
                  class: ' border border-border_primary border-solid mb-6 mt-2',
                },
              }"
              tableStyle="min-width: 70rem; border-radius: 100px;"
              style="width: "
            >
              <Column
                selectionMode="multiple"
                headerStyle="width: 1rem; color: #667085;"
              ></Column>
              <Column
                field="name"
                header="Name"
                :pt="{
                  bodyCell: { class: 'text-gray-700' },
                }"
                headerStyle="color: #667085; font-weight: 500;"
                
              >
                <template #body="slotProps">
                  <div class="font-medium">
                    <p>{{ slotProps.data.name }}</p>

                    <p class="text-[#667085]">{{ slotProps.data.email }}</p>
                  </div>
                </template>
              </Column>
              <Column
                field="date"
                header="Date Added"
                :pt="{
                  bodyCell: { class: 'text-gray-500' },
                }"
                headerStyle="color: #667085; font-weight: 500;"
                style="width: 150px"
              >
              </Column>
              <Column
                field="active"
                header="Last Active"
                :pt="{
                  bodyCell: { class: 'text-gray-500' },
                }"
                headerStyle="color: #667085; font-weight: 500;"
                
              >
              </Column>
              <Column
                field=""
                header="Actions"
                :pt="{
                  bodyCell: { class: 'text-gray-500' },
                }"
                headerStyle="color: #667085; font-weight: 500;"
              >
                <template #body="slotProps">
                  <div class="flex gap-3 items-center">
                    <div
                      class="p-1 font-semibold border rounded-md border-gray-300"
                    >
                      <Icon icon="mingcute:pencil-2-line" width="25" />
                    </div>
                    <div @click=" handleDelete(slotProps.data.id)">
                      <Icon icon="mingcute:delete-2-line" width="25" />
                    </div>
                  </div>
                </template>
              </Column>
            </DataTable>
    </div>

  </main>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from 'vue-router';
const router = useRouter();
const selectedProduct = ref();
const goBack = () => {
  router.go(-1);
};


const data = {
    name: "Administrator Access level",
    description: "Focuses on document management. Configures workflows, archiving policies, and document-specific settings.",
    table: [
      {
        id: "1001",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1002",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1003",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1004",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1005",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1006",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1007",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1008",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1009",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1010",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1012",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1013",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
    ],
  }


</script>

<style>

</style>