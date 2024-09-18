<template>
  <div class="rounded-2xl bg-white mt-5 p-5">
    <div class="flex items-center justify-between w-full">
      <h1 class="bg-slate-50 p-2 rounded-2xl w-fit px-5 mb-3">Backup Status</h1>
    </div>
    <DataTable
      paginator
      :rows="10"
      :value="sessions"
      dataKey="id"
      :pt="{
        table: {
          class: 'w-full mt-5 border border-border_primary border-solid mb-6',
        },
      }"
      tableStyle="min-width: 60rem; border-radius: 30px;"
    >
    <Column
        field="Backup_Id"
        header="Backup ID"
        :pt="{
          bodyCell: { class: 'text-gray-700' },
        }"
        headerStyle="color: #667085; font-weight: 500;"
      >
      </Column>
      

      <Column
        field="TimeStamp"
        header="TimeStamp"
        :pt="{
          bodyCell: { class: 'text-gray-700' },
        }"
        headerStyle="color: #667085; font-weight: 500;"
      >
      </Column>

      <Column
        field="Status"
        header="Status"
        :pt="{
          bodyCell: { class: 'text-gray-700' },
        }"
        headerStyle="color: #667085; font-weight: 500;"
      >
      </Column>

      <Column
        field="Backup_Type"
        header="Backup Type"
        :pt="{
          bodyCell: { class: 'text-gray-700' },
        }"
        headerStyle="color: #667085; font-weight: 500;"
      >
      </Column>
      <Column
        field="size"
        header="Size"
        :pt="{
          bodyCell: { class: 'text-gray-700' },
        }"
        headerStyle="color: #667085; font-weight: 500;"
      >
      </Column>
      <Column
        field="Initiated_By"
        header="Initiated By"
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
import { ref } from "vue";
const sessions = ref([
  {
    Backup_Type: "Full",
    location: "Local Server",
    size: "10GB",
    Status: "Failed",
    TimeStamp: "2024-06-26 14:23:45",
    Backup_Id: "001",
    Initiated_By: 'System'
  },
  {
    Backup_Type: "Incremental",
    location: "Cloud Storage",
    size: "50GB",
    Status: "Completed",
    TimeStamp: "2024-06-26 14:23:45",
    Backup_Id: "002",
     Initiated_By: 'System'
  },
  {
    Backup_Type: "Incremental",
    location: "Local Server",
    size: "30GB",
    Status: "Completed",
    TimeStamp: "2024-06-26 14:23:45",
    Backup_Id: "003",
     Initiated_By: 'System'
  },
  {
    Backup_Type: "Differential",
    location: "Cloud Storage",
    size: "100GB",
    Status: "In Progress",
    TimeStamp: "2024-06-26 14:23:45",
    Backup_Id: "004",
     Initiated_By: 'System'
  },
  {
    Backup_Type: "Incremental",
    location: "Cloud Storage",
    size: "20GB",
    Status: "Failed",
    TimeStamp: "2024-06-26 14:23:45",
    Backup_Id: "005",
     Initiated_By: 'Admin'
  },
  {
    Backup_Type: "Incremental",
    location: "Cloud Storage",
    size: "40GB",
    Status: "Completed",
    TimeStamp: "2024-06-26 14:23:45",
    Backup_Id: "006",
     Initiated_By: 'System'
  },
]);
</script>
  
  <style>
</style>