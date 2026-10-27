<template>
  <div class=" rounded-2xl bg-white dark:bg-gray-800 mt-5 p-5 border border-gray-100 dark:border-gray-700">
    <div class=" md:flex items-center justify-between w-full">
        <h1 class=" bg-slate-50 dark:bg-gray-900 dark:text-gray-300 border dark:border-gray-700 p-2 rounded-2xl w-fit px-5 mb-3 whitespace-nowrap">Login Activity</h1>
        <div class=" flex items-center border dark:border-gray-700 p-2 rounded-lg gap-2 text-sm mt-5 lg:mt-0">
     <i class="pi pi-search text-gray-300 " style="font-size: 1rem"></i>
     <Input type="text" v-model="value" placeholder="Search Employee" class=" w-96 border-0 outline-none text-[#98A2B3] bg-transparent dark:text-white" />
 </div>
    </div>
    <DataTable
          paginator
          :rows="10"
          :value="props.logins"
          dataKey="id"
          :pt="{
            table: {
              class: 'w-full mt-5 border border-border_primary border-solid mb-6',
            },
          }"
          tableStyle="min-width: 60rem; border-radius: 30px;"
        >
        <Column
            field="ip_address"
            header="IP Address"
            :pt="{
              bodyCell: { class: 'text-gray-700' },
            }"
            headerStyle="color: #667085; font-weight: 500;"
          >
          </Column>


          <Column
            field="created_at"
            header="TimeStamp"
            :pt="{
              bodyCell: { class: 'text-gray-700' },
            }"
            headerStyle="color: #667085; font-weight: 500;"
          >
          </Column>

          <Column
            field="name"
            header="User ID"
            :pt="{
              bodyCell: { class: 'text-gray-700' },
            }"
            headerStyle="color: #667085; font-weight: 500;"
          >
          </Column>


          <Column
            field="browser"
            header="Browser"
            :pt="{
              bodyCell: { class: 'text-gray-700' },
            }"
            headerStyle="color: #667085; font-weight: 500;"
          >

          </Column>
          <Column
            field="location"
            header="Location"
            :pt="{
              bodyCell: { class: 'text-gray-700' },
            }"
            headerStyle="color: #667085; font-weight: 500;"
          >
          </Column>




        </DataTable>
  </div>
</template>

<script setup>
import { defineProps, ref } from 'vue'
const props = defineProps(["logins"]);
const value = ref("");
</script>

<style>
</style>
