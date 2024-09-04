<template>
  <main class="px-7">
    <div class="flex items-center gap-2">
      <Icon icon="lucide:home" width="25" />
      <Icon icon="ic:baseline-greater-than" width="25" class="text-gray-300" />
      <p>User Profile</p>
      <Icon icon="ic:baseline-greater-than" width="25" class="text-gray-300" />
      <button @click="goBack">KPI</button>
      <Icon icon="ic:baseline-greater-than" width="25" class="text-gray-300" />
      <button class="p-2 px-4 rounded-md bg-slate-100">Attendance</button>
    </div>

    <div class="flex items-center my-5 w-full justify-between">
      <h1 class="font-semibold text-2xl">Attendance Report</h1>
      <div class="flex items-center gap-16">
        <div class="flex items-center gap-1">
          <input type="date" class="border rounded-md p-2" />
          <p>-</p>
          <input type="date" class="border rounded-md p-2" />
        </div>

        <!-- <button
          class="p-2 rounded-md font-semibold text-gray-700 border flex items-center gap-1 border-gray-300"
        >
          <Icon icon="pepicons-pop:file" />
          <p>Generate Report</p>
        </button> -->
      </div>
    </div>

    <div class="border rounded-2xl p-7 bg-white grid grid-cols-3 gap-10" v-if="props.user.attendance && props.user.attendance.length > 0">
      <div class="bg-slate-50 p-1.5 rounded-md border border-gray-100" v-for="(item,index) in props.user?.attendance" :key="index">
        <p class="bg-white ml-2 p-2 w-fit rounded-md mt-5">{{ getDate(item.created_at) }}</p>
        <div
          class="grid grid-cols-3 gap-5 justify-between bg-white rounded-md p-2 mt-4"
        >
          <div>
            <p class="text-gray-500">Check in</p>
            <h1 class="font-semibold text-2xl">{{ getCheckIn(item.login_time) }}</h1>
          </div>
          <div>
            <p class="text-gray-500">Check out</p>
            <h1 class="font-semibold text-2xl">{{ getCheckOut(item.logout_time) }}</h1>
          </div>
          <div>
            <p class="text-gray-500">Total</p>
            <h1 class="font-semibold text-2xl">{{ totalTimeSpent(item.login_time, item.logout_time) }}</h1>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>

<script  setup>
//import { useRouter, useRoute } from "vue-router";
import {ref, defineProps} from 'vue'
import moment from "moment";
import {Link} from "@inertiajs/vue3"

//const router = useRouter();

const props = defineProps(['user'])
const goBack = () => {
  // router.push({
  //   name: "UserProfile",
  //   params: { profileId: route.params.profileId },
  // });
};

const attendance = ref([
  {
    date: "Wed, May, 2024",
    checkin: "9:00 AM",
    checkout: "9:00 PM",
    total: "8hr",
  },
  {
    date: "Wed, May, 2024",
    checkin: "9:00 AM",
    checkout: "9:00 PM",
    total: "8hr",
  },
  {
    date: "Wed, May, 2024",
    checkin: "9:00 AM",
    checkout: "9:00 PM",
    total: "8hr",
  },
  {
    date: "Wed, May, 2024",
    checkin: "9:00 AM",
    checkout: "9:00 PM",
    total: "8hr",
  },
  {
    date: "Wed, May, 2024",
    checkin: "9:00 AM",
    checkout: "9:00 PM",
    total: "8hr",
  },
  {
    date: "Wed, May, 2024",
    checkin: "9:00 AM",
    checkout: "9:00 PM",
    total: "8hr",
  },
  {
    date: "Wed, May, 2024",
    checkin: "9:00 AM",
    checkout: "9:00 PM",
    total: "8hr",
  },
  {
    date: "Wed, May, 2024",
    checkin: "9:00 AM",
    checkout: "9:00 PM",
    total: "8hr",
  },
  {
    date: "Wed, May, 2024",
    checkin: "9:00 AM",
    checkout: "9:00 PM",
    total: "8hr",
  },
  {
    date: "Wed, May, 2024",
    checkin: "9:00 AM",
    checkout: "9:00 PM",
    total: "8hr",
  },
  {
    date: "Wed, May, 2024",
    checkin: "9:00 AM",
    checkout: "9:00 PM",
    total: "8hr",
  },
  {
    date: "Wed, May, 2024",
    checkin: "9:00 AM",
    checkout: "9:00 PM",
    total: "8hr",
  },
  {
    date: "Wed, May, 2024",
    checkin: "9:00 AM",
    checkout: "9:00 PM",
    total: "8hr",
  },
]);

function formatDate(dateString) {
  return moment(dateString).format('h:mm A');
}

const getCheckIn = (date)=>{
  if(date){
    return (formatDate(date))
  }else{
    return "NIL"
  }

}

const getCheckOut = (date)=>{
  if(date){
    return (formatDate(date))
  }else{
    return "NIL"
  }
}

const timeDif = (login, logout)=>{

  const start = moment(login);
  const end = moment(logout);

  // Difference in various units
  const differenceInHours = end.diff(start, 'hours'); // Difference in hours
  const differenceInMinutes = end.diff(start, 'minutes'); // Difference in minutes

  return `${differenceInHours} hrs ${differenceInMinutes} mins`
}

const totalTimeSpent = (login_time, logout_time)=>{
  if(login_time && logout_time){
    return timeDif(login_time, logout_time)
  }else{
    return "--"
  }
}

const getDate = (date)=>{
    const formattedDate = moment(date).format('ddd, MMM Do, YYYY');
    return formattedDate
}
</script>

<style>
</style>