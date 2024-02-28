<template>
  <main>
    <div class="lg:px-7 px-5 flex items-center justify-between pt-5">
      <div class="flex items-center gap-5">
        <Link href="/admin/manage-users" class="border rounded-md p-3 py-4 cursor-pointer">
          <Icon icon="formkit:arrowleft" width="30" />
        </Link>
        <div>
          <p class="text-3xl text-[#1D2939]">{{ capitalizeWords(props.role.name) }}</p>
          <p class="text-gray-500 text-sm w-[70%]">{{ props.role.description }}</p>
        </div>
      </div>
      <div class="flex items-center gap-4">
        <button class="flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-4">
          <Icon icon="material-symbols:download" width="20" />
          <p>Download CSV</p>
        </button>

        <Link href="/admin/add-user" class="flex gap-2 items-center text-white bg-[#2F68C5] rounded-lg py-2 px-4">
          <Icon icon="material-symbols:add" width="20" />
          <p>Add User</p>
        </Link>

      </div>
    </div>
    <Divider class=" " />


    <section class=" flex items-center justify-end pr-7">
      <div class="flex items-center border px-2 rounded-lg gap-2 text-sm mt-5 lg:mt-0">
        <i class="pi pi-search text-gray-300" style="font-size: 1rem"></i>
        <Input type="text" v-model="value" placeholder="Search Users" class="w-96 border-0 outline-none text-[#98A2B3]" />
      </div>
    </section>

    <Divider class=" " />


    <div class=" mt-5 pl-7">
      <DataTable v-model:selection="selectedProduct" :rows="8" paginator :value="props.role.users" dataKey="id" :pt="{
        table: {
          class: ' border border-border_primary border-solid mb-6 mt-2',
        },
      }" tableStyle="min-width: 70rem; border-radius: 100px;" style="width: ">
        <Column selectionMode="multiple" headerStyle="width: 1rem; color: #667085;"></Column>
        <Column field="name" header="Name" :pt="{
          bodyCell: { class: 'text-gray-700' },
        }" headerStyle="color: #667085; font-weight: 500;">
          <template #body="slotProps">
            <div class="font-medium">
              <p>{{ slotProps.data.last_name+" "+slotProps.data.first_name }}</p>

              <p class="text-[#667085]">{{ slotProps.data.email }}</p>
            </div>
          </template>
        </Column>
        <Column field="date" header="Date Added" :pt="{
          bodyCell: { class: 'text-gray-500' },
        }" headerStyle="color: #667085; font-weight: 500;" style="width: 150px">
        <template #body="slotProps">
          <div>
            <p>{{ formatDate(slotProps.data.created_at,false) }}</p>
          </div>
        </template>
        </Column>
        <Column field="active" header="Last Active" :pt="{
          bodyCell: { class: 'text-gray-500' },
        }" headerStyle="color: #667085; font-weight: 500;">
        <template #body="slotProps">
          <div>
            <p>{{ formatDate(slotProps.data.updated_at,true) }}</p>
          </div>
        </template>
        </Column>
        <Column field="" header="Actions" :pt="{
          bodyCell: { class: 'text-gray-500' },
        }" headerStyle="color: #667085; font-weight: 500;">
          <template #body="slotProps">
            <div class="flex gap-3 items-center">
              <div class="p-1 font-semibold border rounded-md border-gray-300">
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
import { ref, defineProps } from "vue";
import { Link } from "@inertiajs/vue3";
const selectedProduct = ref();
const goBack = () => {
  router.go(-1);
};
const props = defineProps(['role']);

const formatDate = (data, last_active)=>{
  let newDate =data;

  // Convert the Laravel date string to a JavaScript Date object
  let date = new Date(newDate);

  // Get the day, month, and year from the Date object
  let day = date.getDate();
  let monthIndex = date.getMonth();
  let year = date.getFullYear();

  // Define an array of month names
  let monthNames = [
    "Jan", "Feb", "Mar", "Apr", "May", "Jun",
    "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
  ];

  // Format the date string
  let formattedDate = `${day} ${monthNames[monthIndex]} ${year.toString().slice(-2)}`;
  let formattedDate2 = ` ${monthNames[monthIndex]} ${year.toString().slice(-2)}`;

  let new_date = last_active? formattedDate2: formattedDate

  return new_date;

}

const capitalizeWords = (str)=> {
  return str.replace(/\b\w/g, function(char) {
    return char.toUpperCase();
  });
}

</script>

<style></style>