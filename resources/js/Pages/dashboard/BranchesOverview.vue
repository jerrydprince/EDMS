<template>
  <main>
    <div class=" flex items-center gap-5 px-7 py-5 ">
      <div class=" border rounded-full p-3">
        <Icon icon="material-symbols:database-outline" width="35" />
      </div>
      <div>
        <h1 class=" text-3xl font-semibold text-[#1D2939]">Branches Overview</h1>
        <p class=" opacity-80">Access branches to start archiving</p>
      </div>
    </div>
    <Divider class=" -mt-1" />

    <div v-for="(item, index) in props.branches" :key="index" class="mb-5">
      <div class="flex items-start justify-between pl-7 pr-14">
        <div class="w-[25%]">
          <h1 class="text-xl font-semibold text-[#475467]">
            {{ item.title }}
          </h1>
          <p class="text-[#667085] mt-2">{{ item.description }}</p>
        </div>
        <div class=" ">
          <DataTable v-model:selection="selectedProduct" :rows="4" :value="props.branches" dataKey="id" :pt="{
            table: {
              class: ' border border-border_primary border-solid mb-6 mt-2',
            },
          }" tableStyle="min-width: 50rem; border-radius: 100px;" style="width: ">
            <Column selectionMode="multiple" headerStyle="width: 1rem; color: #667085;"></Column>
            <Column field="name" header="Name" :pt="{
              bodyCell: { class: 'text-gray-700' },
            }" headerStyle="color: #667085; font-weight: 500;" style="width: 200px">
              <template #body="slotProps">
                <div class="font-medium">
                  <p>{{ slotProps.data.name }}</p>
                </div>
              </template>
            </Column>
            <Column field="Doc_number" header="Document Number" :pt="{
              bodyCell: { class: 'text-gray-500' },
            }" headerStyle="color: #667085; font-weight: 500;" style="width: 150px">
            </Column>
            <Column field="LastModified" header="Last Modified" :pt="{
              bodyCell: { class: 'text-gray-500' },
            }" headerStyle="color: #667085; font-weight: 500;" style="width: 150px">
            </Column>

          </DataTable>
        </div>
      </div>
      <Divider />
    </div>
  </main>
</template>

<script setup>
import { ref, defineProps } from 'vue'
const selectedProduct = ref();
const props = defineProps(['branches']);
const data = [
  {
    name: "Department",
    description:
      "You have been assigned to these department for archiving of documents.",
    table: [
      {
        id: "1001",
        name: "Auditing Department",
        Doc_number: '56 Documents',
        LastModified: "Mar 2023",
      }
    ],
  },
  {
    name: "Divisions",
    description:
      "You have been assigned to these divisions for archiving of documents.",
    table: [
      {
        id: "1001",
        name: "Accounting Division",
        Doc_number: '56 Documents',
        LastModified: "Mar 2023",
      },
      {
        id: "1002",
        name: "Accounting Division",
        Doc_number: '56 Documents',
        LastModified: "Mar 2023",
      }
    ],
  },

  {
    name: "Units",
    description:
      "You have been assigned to these units for archiving of documents.",
    table: [
      {
        id: "1001",
        name: "Auditing Unit",
        Doc_number: '56 Documents',
        LastModified: "Mar 2023",
      },
      {
        id: "1002",
        name: "Auditing Unit",
        Doc_number: '56 Documents',
        LastModified: "Mar 2023",
      }

    ],
  },






];
</script>

<style></style>