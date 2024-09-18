<template>
  <div class=" rounded-2xl bg-white mt-5 p-5 ">
    <div class=" md:flex items-center justify-between w-full">
        <h1 class=" bg-slate-50 p-2 rounded-2xl w-fit px-5 mb-3 whitespace-nowrap">Login Activity</h1>
        <div class=" flex items-center border p-2 rounded-lg gap-2 text-sm mt-5 lg:mt-0">
     <i class="pi pi-search text-gray-300 " style="font-size: 1rem"></i>
     <Input type="text" v-model="value" placeholder="Search Employee" class=" w-96 border-0 outline-none text-[#98A2B3]" />
 </div>
    </div>
    <DataTable
          paginator
          :rows="10"
          :value="newLogins"
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

<script  setup>
import {ref, defineProps} from 'vue'
const props = defineProps(["logins"]);
const newLogins = ref(props.logins)
const sessions = ref([
    {
      browser: 'chrome',
      location: 'Lagos Nigeria',
      session: 'Current session',
      ip: '224.0.1.1',
      TimeStamp: '2024-06-26 14:23:45',
      User_ID: 'john.doe'
    },
    {
      browser: 'chrome',
      location: 'Lagos Nigeria',
      session: 'Current session',
      ip: '224.0.1.1',
            TimeStamp: '2024-06-26 14:23:45',
              User_ID: 'john.doe'
    },
    {
      browser: 'chrome',
      location: 'Lagos Nigeria',
      session: 'Current session',
      ip: '224.0.1.1',
            TimeStamp: '2024-06-26 14:23:45',
              User_ID: 'john.doe'
    },
    {
      browser: 'chrome',
      location: 'Lagos Nigeria',
      session: 'Current session',
      ip: '224.0.1.1',
            TimeStamp: '2024-06-26 14:23:45',
              User_ID: 'john.doe'
    },
    {
      browser: 'chrome',
      location: 'Lagos Nigeria',
      session: 'Current session',
      ip: '224.0.1.1',
            TimeStamp: '2024-06-26 14:23:45',
              User_ID: 'john.doe'
    },
    {
      browser: 'chrome',
      location: 'Lagos Nigeria',
      session: 'Current session',
      ip: '224.0.1.1',
            TimeStamp: '2024-06-26 14:23:45',
              User_ID: 'john.doe'
    }
  ])
</script>

<style>


</style>
