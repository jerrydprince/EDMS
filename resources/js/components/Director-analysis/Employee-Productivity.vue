<template>
    <div class=" rounded-2xl bg-white mt-5 p-5 ">
      <div class=" md:flex items-center justify-between w-full">
          <h1 class=" bg-slate-50 p-2 rounded-3xl text-gray-800 w-fit px-5 mb-3 whitespace-nowrap">Employee Productivity</h1>
          <div class=" flex items-center border p-2 rounded-lg gap-2 text-sm mt-5 lg:mt-0">
       <i class="pi pi-search text-gray-300 " style="font-size: 1rem"></i>
       <Input type="text" v-model="value" placeholder="Search Employee" class=" w-96 border-0 outline-none text-[#98A2B3]" />
   </div>
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
              field="ip"
              header="Name"
              :pt="{
                bodyCell: { class: 'text-gray-700' },
              }"
              headerStyle="color: #667085; font-weight: 500;"
            >
            </Column>
          
  
            <Column
              field="TimeStamp"
              header="Tasks Completed"
              :pt="{
                bodyCell: { class: 'text-gray-700' },
              }"
              headerStyle="color: #667085; font-weight: 500;"
            >
            </Column>
  
            <Column
              field="User_ID"
              header="Task Completion Rate"
              :pt="{
                bodyCell: { class: 'text-gray-700' },
              }"
              headerStyle="color: #667085; font-weight: 500;"
            >
            </Column>
  
  
            <Column
              field="browser"
              header="Avg. Task Completion Time"
              :pt="{
                bodyCell: { class: 'text-gray-700' },
              }"
              headerStyle="color: #667085; font-weight: 500;"
            >
            
            </Column>
            <Column
              field="location"
              header="Performance Rating"
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
  import {ref} from 'vue'
  const sessions = ref([
      {
        browser: '2 days',
        location: '4.5',
        session: 'Current session',
        ip: 'Brooklyn Simmons',
        TimeStamp: '150',
        User_ID: '95%'
      },
      {
        browser: '2 days',
        location: '4.5',
        session: 'Current session',
        ip: 'Ralph Edwards',
              TimeStamp: '150',
                User_ID: '95%'
      },
      {
        browser: '2 days',
        location: '4.5',
        session: 'Current session',
        ip: 'Guy Hawkins',
              TimeStamp: '150',
                User_ID: '95%'
      },
      {
        browser: '2 days',
        location: '4.5',
        session: 'Current session',
        ip: 'Brooklyn Simmons',
              TimeStamp: '150',
                User_ID: '95%'
      },
      {
        browser: '2 days',
        location: '4.5',
        session: 'Current session',
        ip: 'Cameron Williamson',
              TimeStamp: '150',
                User_ID: '95%'
      }
    ])
  </script>
  
  <style>
  
  </style>
