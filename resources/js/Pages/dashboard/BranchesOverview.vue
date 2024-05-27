<template>
  <main>
    <div class="flex items-center gap-5 px-7 py-5">
      <div class="border rounded-full p-3">
        <Icon icon="material-symbols:database-outline" width="35" />
      </div>
      <div>
        <h1 class="text-3xl font-semibold text-[#1D2939]">Branches Overview</h1>
        <p class="opacity-80">Access branches to start archiving</p>
      </div>
    </div>
    <Divider class="-mt-1" />

    <div v-for="(item, index) in department" :key="index" class="mb-5">
      <div class="flex items-start justify-between pl-7 pr-14">
        <div class="w-[25%]">
          <h1 class="text-xl font-semibold text-[#475467]">
            {{ item.title }}
          </h1>
          <p class="text-[#667085] mt-2">You have been assigned to these department for archiving of documents.</p>
        </div>
        <div class="w-4/6">
          <table class="w-full text-left">
            <thead class=" bg-gray-50">
              <tr class="border text-[#667085]">
                <th class="p-3 pl-5">
                  <Checkbox v-model="checked" :binary="true" class="mr-1" />
                  Department Name
                </th>
                <th>Document Number</th>
                <th>Last Modified</th>
              </tr>
            </thead>
            <tbody>
              <tr class="border">
                <td class="p-3 pl-5">
                  <Checkbox v-model="checked" :binary="true" class="mr-1" />
                  {{ item.title }} Department
                </td>
                <td>{{ item.archive_count }} Documents</td>
                <td>{{ formatDate(item.updated_at) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <Divider />
      <div v-for="(division, index) in props.division" :key="index" class="mb-5">
        <div class="flex items-start justify-between pl-7 pr-14">
          <div class="w-[25%]">
            <h1 class="text-xl font-semibold text-[#475467]">
              {{ division.title }}
            </h1>
            <p class="text-[#667085] mt-2">You have been assigned to these divisions for archiving of documents.</p>
          </div>
          <div class="w-4/6">
            <table class="w-full text-left">
              <thead class=" bg-gray-50">
                <tr class="border text-[#667085]">
                  <th class="p-3 pl-5">
                    <Checkbox v-model="checked" :binary="true" class="mr-1" />
                    Division Name
                  </th>
                  <th>Document Number</th>
                  <th>Last Modified</th>
                </tr>
              </thead>
              <tbody>
                <tr class="border">
                  <td class="p-3 pl-5">
                    <Checkbox v-model="checked" :binary="true" class="mr-1" />
                    {{ division?.title }} Division
                  </td>
                  <td>{{ division.archive_count }} Documents</td>
                  <td>{{ formatDate(division.updated_at) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <Divider />
        <div class="flex items-start justify-between pl-7 pr-14">
          <div class="w-[25%]">
            <h1 class="text-xl font-semibold text-[#475467]">
              Units
            </h1>
            <p class="text-[#667085] mt-2">You have been assigned to these units for archiving of documents.</p>
          </div>

          <div class="mb-5 w-4/6">
            <table class="w-full text-left">
              <thead class=" bg-gray-50">
                <tr class="border text-[#667085]">
                  <th class="p-3 pl-5">
                    <Checkbox v-model="checked" :binary="true" class="mr-1" />
                    Unit Name
                  </th>
                  <th>Document Number</th>
                  <th>Last Modified</th>
                </tr>
              </thead>
              <tbody>
                <tr class="border">
                  <td class="p-3 pl-5">
                    <Checkbox v-model="checked" :binary="true" class="mr-1" />
                    {{ props.unit?.title }}
                  </td>
                  <td>{{ props.unit?.archive_count }} Documents</td>
                  <td>{{ formatDate(props.unit?.updated_at) }}</td>
                </tr>
              </tbody>
            </table>
          </div>

        </div>
      </div>
    </div>
  </main>
</template>
 
<script setup>
import { ref, computed, defineProps } from "vue";
import { usePage } from "@inertiajs/vue3";

const checked = ref(false);
const selectedProduct = ref();
const page = usePage()
const department = computed(() => page.props.department)
const userDepartment = computed(() => page.props.department_div_unit)
const props = defineProps(['department', 'division', 'unit']);


const formatDate =(inputDate)=> {
    var date = new Date(inputDate);
    var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    var day = date.getDate();
    var month = months[date.getMonth()];
    var year = date.getFullYear();

    day = day < 10 ? '0' + day : day;

    var formattedDate =  month + ' ' + year.toString().substr(-2);

    return formattedDate;
}
</script>
 
<style></style>
