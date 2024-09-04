<template>
  <main class="px-7  pb-32">
    <div class="flex items-center gap-2">

      <router-link to="/PMS/overview">
        <Icon icon="lucide:home" width="25" />
      </router-link>

      <Icon icon="ic:baseline-greater-than" width="25" class="text-gray-300" />
      <p>User Profile</p>
      <Icon icon="ic:baseline-greater-than" width="25" class="text-gray-300" />
      <button class="p-2 px-4 rounded-md bg-slate-100">
        {{ activebutton === 1 ? "KPI" : "Tasks" }}
      </button>
    </div>

    <div class="p-5 border-gray-300 rounded-xl mt-5 border bg-white">
      <div class="flex w-full items-center justify-between">
        <h1 class="pl-2 border-l-[2px] border-primary/60 font-semibold">
          User Profile
        </h1>
        <div class="flex items-center gap-2">
          <button
            class="p-2 rounded-md text-xs font-semibold text-gray-700 border flex items-center gap-1 border-gray-300"
            @click="leaveModal = true">
            <Icon icon="ph:pause" />
            <p>Add Leave</p>
          </button>
          <button
            class="p-2 rounded-md text-xs font-semibold text-gray-700 border flex items-center gap-1 border-gray-300"
            @click="reportModal = true">
            <Icon icon="icon-park-outline:caution" />
            <p>Report Misconduct</p>
          </button>
          <button
            class="p-2 rounded-md text-xs font-semibold text-gray-700 border flex items-center gap-1 border-gray-300">
            <Icon icon="pepicons-pop:file" />
            <p>Generate Report</p>
          </button>
        </div>
      </div>


      <div class=" lg:flex items-start w-full justify-between mt-5">

        <div class="flex start gap-4 ">
          <img src="../../assets/images/user_profile.svg" alt="" />
          <div class="">
            <div class="flex items-center gap-5">
              <h1 class="text-2xl">{{user.first_name+' '+user.last_name}}</h1>
              <p class="text-xs p-1 bg-primary/10 text-primary font-semibold px-2 rounded-xl">
                Deputy Director
              </p>
            </div>
            <button class=" mt-3 bg-gray-50 text-gray-500 text-sm p-2 py-1 rounded-md">{{ getBranchName() }}</button>
          </div>
        </div>

        <div class=" mt-5 lg:mt-0 flex items-center  gap-5">

          <div class=" text-sm">
            <p class=" font-semibold">Email</p>
            <button class=" mt-3 bg-gray-50 text-gray-500 p-2 py-1 rounded-md">{{ user.email }}</button>
          </div>
          <div class=" text-sm">
            <p class=" font-semibold">Phone Number</p>
            <button class=" mt-3 bg-gray-50  text-gray-500 p-2 py-1 rounded-md">(+234) {{user.phone}}</button>
          </div>
        </div>
      </div>
    </div>

    <div class=" flex items-center mt-5 ">
      <button @click=" activebutton = 1" class=" p-1 px-4 rounded-2xl font-semibold"
        :class="{ 'bg-white border border-gray-300': activebutton === 1 }">KPI’s</button>
      <button @click="activebutton = 2" class=" p-1 px-3 rounded-2xl  font-semibold"
        :class="{ 'bg-white border border-gray-300': activebutton === 2 }">Tasks</button>
    </div>

    <div>
      <KPI v-if="activebutton === 1" :ratings="props.ratings" :user="props.user" :taskCompletionRate="props.taskCompletionRate" />
      <Tasks v-else />
    </div>
  </main>

  <ReportMisconduct v-model="reportModal" />
  <AddLeave v-model="leaveModal" />


</template>

<script setup>
import KPI from "../../components/KPI.vue";

import Tasks from "../../components/Tasks.vue";
import { ref, defineProps } from "vue";
import ReportMisconduct from '../../components/Modals/PMS/ReportMisconduct.vue';
import AddLeave from '../../components/Modals/PMS/AddLeave.vue';

const reportModal = ref(false);
const leaveModal = ref(false)
const props = defineProps(['user', 'taskCompletionRate', 'ratings'])

const activebutton = ref(1)

const getBranchName = ()=>{
  if(props.user.unit){
    return props.user.unit.title
  }
  if(props.user.division){
    return props.user.division.title
  }
  if(props.user.department){
    return props.user.department.title
  }
}
</script>

<style></style>