<template>
  <main class="px-7  pb-32">
    <div class="flex items-center gap-2 py-1">

      <Link href="/dashboard/pms">
        <Icon icon="lucide:home" width="25" />
      </Link>

      <Icon icon="ic:baseline-greater-than" width="25" class="text-gray-300" />
      <!-- <a href="/dashboard/pms">PMS</a> -->
      <!-- <Icon icon="ic:baseline-greater-than" width="25" class="text-gray-300" /> -->
      <button class="p-2 px-4 rounded-md bg-slate-100">
        User Profile
      </button>
    </div>

    <div class="p-5 border-gray-300 rounded-xl mt-5 border bg-white">
      <div class="flex w-full items-center justify-between">
        <h1 class="pl-2 border-l-[2px] border-primary/60 font-semibold">
          User Profile
        </h1>
        <div class="flex items-center gap-2">
          <!-- <button v-if="isStillOnLeave"
            class="p-2 rounded-md text-xs font-semibold text-gray-700 border flex items-center gap-1 border-gray-300"
            @click="leaveModal = true">
            
            <p>X Cancel Leave</p>
          </button> -->
          
          <button
            v-if="isStillOnLeave() && user.is_on_leave == 1 && !roles.includes('regular users')"
            class="p-2 ml-3 rounded-md text-xs font-semibold text-gray-700 border flex items-center gap-1 border-gray-300"
            @click="cancelLeaveModal = true"
          >
            <Icon icon="iconoir:cancel" width="15" />
            <p>Cancel Leave</p>
          </button>
          <button v-if="!isStillOnLeave() && user.is_on_leave == 0 && !roles.includes('regular users')"
            class="p-2 rounded-md text-xs font-semibold text-gray-700 border flex items-center gap-1 border-gray-300"
            @click="leaveModal = true">
            <Icon icon="ph:pause" />
            <p>Add Leave</p>
          </button>
          <button v-if="!roles.includes('regular users')"
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
          <img v-if="user.profile_image" :src="page.props.base+'/'+user.profile_image" alt="" width="30">
          <img v-else src="../../assets/images/user_profile.svg" alt="" />
          <div class="">
            <div class="flex items-center gap-5">
              <h1 class="text-2xl">{{user.first_name+' '+user.last_name}}</h1>
              <p class="text-xs p-1 bg-primary/10 text-primary font-semibold px-2 rounded-xl">
                {{user.roles[0]?.name}}
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
      <Tasks :task="props.user?.task" v-else />
    </div>
  </main>

  <ReportMisconduct v-model="reportModal" />
  <AddLeave v-model="leaveModal" />
  <CancelLeave v-model="cancelLeaveModal" :user="props.user" />


</template>

<script setup>
import KPI from "../../components/KPI.vue";

import Tasks from "../../components/Tasks.vue";
import { ref, defineProps, computed } from "vue";
import ReportMisconduct from '../../components/Modals/PMS/ReportMisconduct.vue';
import AddLeave from '../../components/Modals/PMS/AddLeave.vue';
import CancelLeave from "../../components/Modals/PMS/CancelLeave.vue";
import {usePage, Link} from "@inertiajs/vue3"

const reportModal = ref(false);
const leaveModal = ref(false)
const cancelLeaveModal = ref(false)
const props = defineProps(['user', 'taskCompletionRate', 'ratings'])
const roles = computed(()=> page.props.roles)
const activebutton = ref(1)
const page = usePage()

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

const isStillOnLeave = ()=>{
  if(props.user.leave && props.user.leave.length > 0){
    let end_date = new Date(props.user.leave[0]);
    const today = new Date();

    today.setHours(0,0,0,0);
    end_date.setHours(0,0,0,0);
    
    if(today.getTime() >= end_date.getTime()){
      return false;
    }else{
      return true;
    }
  }else{
    return false;
  }
}
</script>

<style></style>