<template>
  <main class=" p-5 lg:p-7 rounded-2xl mt-5 border border-gray-300 bg-white">
    <section class="w-full justify-between lg:flex items-center gap-10">
      <div class="bg-slate-100 w-full lg:w-[47%]  rounded-lg p-1">
        <div class="flex items-center justify-between w-full p-5">
          <h1 class=" font-semibold text-lg">Attendance</h1>
          <p class=" bg-white px-2 text-green-600 py-0.5  rounded-2xl"> • online</p>
        </div>

        <div class=" bg-white rounded-xl p-5">
          <div class="  grid grid-cols-3 gap-5 justify-between ">
            <div>
              <p class=" text-gray-500">Check in</p>
              <h1 class=" font-semibold text-2xl">{{ getCheckIn() }}</h1>
            </div>
            <div>
              <p class=" text-gray-500">Check out</p>
              <h1 class=" font-semibold text-2xl">{{ getCheckOut() }}</h1>
            </div>
            <div>
              <p class=" text-gray-500">Total</p>
              <h1 class=" font-semibold text-2xl">{{ totalTimeSpent() }}</h1>
            </div>
          </div>

          <p class=" bg-slate-50 p-2 w-fit rounded-md mt-5">{{ getDate() }}</p>
          <div class=" flex items-center w-full justify-between mt-5">


            <Link :href="'/dashboard/pms-attendance/'+props.user.id"> <button
                class="p-2 rounded-md text-xs font-semibold text-gray-700 border flex items-center gap-1 border-gray-300">
                <Icon icon="pepicons-pop:file" />
                <p>Attendance Report</p>
              </button>
            </Link>


            <!-- <button class=" text-gray-500 text-sm flex items-center gap-1">
              <p>View All</p>
              <Icon icon="flowbite:angle-right-outline" width="15" />
            </button> -->
          </div>
        </div>
      </div>
      <div class="bg-slate-100  w-full lg:w-[53%]  mt-5 lg:mt-0 rounded-lg p-1">
        <div class="flex items-center justify-between w-full p-5">
          <h1 class=" font-semibold text-lg">Timeliness</h1>
        </div>

        <div class=" bg-white rounded-xl p-5">
          <div class=" flex items-start w-full justify-between">
            <div>
              <p class=" text-gray-500 text-base">Average Turnaround Time</p>
              <h1 class=" font-semibold text-2xl">{{ props.taskCompletionRate?.avg_completion_time || 'N/A' }}</h1>
            </div>

            <div>
              <p class=" text-[#039855] text-lg flex items-center gap-1 ">
                <Icon icon="oui:dot" /><span class=" text-sm text-gray-400">Completed ({{ props.taskCompletionRate?.completion_rate || 0 }}%)</span>
              </p>
              <p class=" text-yellow-500 text-lg my-2 flex items-center gap-1">
                <Icon icon="oui:dot" /> <span class=" text-sm text-gray-400">Pending ({{ props.taskCompletionRate?.pending_rate || 0 }}%)</span>
              </p>
              <p class=" text-red-500 text-lg flex items-center gap-1 ">
                <Icon icon="oui:dot" /> <span class=" text-sm text-gray-400">Overdue / Returned ({{ props.taskCompletionRate?.uncompleted_rate || 0 }}%)</span>
              </p>
            </div>
          </div>

          <p class=" text-gray-500 text-base mt-2">Task Execution Rate</p>
          <div class=" flex items-center w-full gap-3 justify-between mt-3" v-if="props.taskCompletionRate">

            <div v-if="props.taskCompletionRate.completion_rate > 0" :style="{ width: props.taskCompletionRate.completion_rate + '%' }">
              <div class="h-2 rounded-md w-full bg-green-500"></div>
              <h1 class=" mt-2 font-semibold text-lg text-emerald-700">{{props.taskCompletionRate.completion_rate}}%</h1>
            </div>

            <div v-if="props.taskCompletionRate.pending_rate > 0" :style="{ width: props.taskCompletionRate.pending_rate + '%' }">
              <div class="h-2 rounded-md w-full bg-yellow-500"></div>
              <h1 class=" mt-2 font-semibold text-lg text-amber-700">{{props.taskCompletionRate.pending_rate}}%</h1>
            </div>

            <div v-if="props.taskCompletionRate.uncompleted_rate > 0" :style="{ width: props.taskCompletionRate.uncompleted_rate + '%' }">
              <div class="h-2 rounded-md w-full bg-red-500"></div>
              <h1 class=" mt-2 font-semibold text-lg text-red-700">{{props.taskCompletionRate.uncompleted_rate}}%</h1>
            </div>

          </div>
        </div>
      </div>
    </section>

    <section class="w-full justify-between lg:flex items-center gap-10 mt-5">
      <div class="bg-slate-100  w-full lg:w-full rounded-lg p-1">
        <div class="flex items-center justify-between w-full p-3">
          <h1 class=" font-semibold text-lg">Professional Development</h1>
          <button
            @click="generateReport"
            class="p-2 rounded-md text-xs font-semibold text-gray-700 hover:bg-slate-50 border flex items-center gap-1 border-gray-300 transition-colors"
            title="Download Performance Report PDF"
          >
            <Icon icon="pepicons-pop:file" />
            <p>Generate Report</p>
          </button>
        </div>

        <div class=" bg-white rounded-xl p-5" v-if="props.user && props.user.certificates && props.user.certificates.length > 0">
          <div v-for="(item, index) in props.user.certificates" :key="index" class=" mb-3 flex items-center justify-between w-full">
            <div class=" flex gap-4 items-start">
              <div class=" p-3 rounded-md border mt-2 ">
                <Icon :icon="'akar-icons:file'" width="25" />
              </div>
              <div>
                <h1 class=" font-semibold">{{ item.title }}</h1>
                <p class=" mt-1 bg-slate-50 text-gray-500 text-sm w-fit p-1.5 rounded-md">{{ certDate(item.created_at) }}</p>
              </div>
            </div>

            <div class=" flex  items-center gap-2">
              <button>
                <Icon icon="ph:eye" width="25" @click="viewCertificate(item)" />
              </button>
              <button>
                <Icon icon="mingcute:delete-2-line" width="25" @click="deleteCert(item)" />
              </button>
            </div>
          </div>
        </div>
        <div class=" mb-1 flex items-center  w-full justify-between mt-5">
          <button
            class="p-2 rounded-md bg-white text-xs font-semibold text-gray-700 border flex items-center gap-1 border-gray-300"
            @click="certificationModal = true">
            <Icon icon="material-symbols:add" width="20" />
            <p>Add Certification</p>
          </button>

          <button class=" mr-3 text-gray-500 text-sm flex items-center gap-1" @click="certificatesModal = true">
            <p>View All</p>
            <Icon icon="flowbite:angle-right-outline" width="15" />
          </button>
        </div>
      </div>




      <!-- <div class="bg-slate-100 w-full  lg:w-[43%] mt-5 lg:mt-0  rounded-lg p-1">
        <Accuracy />

      </div> -->
    </section>

    <section v-if="props.ratings">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-5">
        <div v-for="(item, index) in props.ratings" :key="index" class="bg-slate-100 w-full rounded-2xl p-1">
          <PolicyChart v-if="item[0].string=='percentage'" :chart="item" />
          <ProductivityChart v-if="item[0].string=='point'" :chart="item" />
        </div>
      </div>
    </section>



   


    <!-- <section class="w-full justify-between lg:flex items-center gap-10 mt-5">
      <div class="bg-slate-100  w-full lg:w-[50%]    rounded-lg p-1">
        <TeamCollaboration />
      </div>


      <div class="bg-slate-100  w-full lg:w-[50%]  mt-5 lg:mt-0   rounded-lg p-1">
        <UtilizationRate />
      </div>

    </section> -->
  </main>
  <AddCertification v-model="certificationModal" :user="props.user" />
  <DeleteCertificate v-model="deleteModal" :cert="selectedCert" />
  <ViewCertificate v-model="viewCertificateModal" :cert="selectedCert" />
  <AllCertificate v-model="certificatesModal" :user="props.user" />
</template>

<script setup>

import { ref, defineProps } from 'vue'
import moment from "moment";
import {Link} from "@inertiajs/vue3"
//import { useRoute } from 'vue-router';
import AddCertification from '../components/Modals/PMS/AddCertification.vue';
import DeleteCertificate from '../components/Modals/PMS/DeleteCertificate.vue';
import ViewCertificate from '../components/Modals/PMS/ViewCertificate.vue';
import AllCertificate from '../components/Modals/PMS/AllCertification.vue';
import PolicyChart from '../components/PolicyChart.vue';
import TeamCollaboration from '../components/TeamCollaboration.vue';
import Accuracy from '../components/Accuracy.vue'
import ProductivityChart from '../components/ProductivityChart.vue';
import UtilizationRate from '../components/UtilizationRate.vue';

const certificatesModal = ref(false)
const viewCertificateModal = ref(false)
const deleteModal = ref(false)
const certificationModal = ref(false);
const props = defineProps(['taskCompletionRate', 'user', 'ratings'])
//const route = useRoute();
//const profileId = route.params.profileId;
const profileId = 2
const Professional = ref([
  {
    title: 'MIT Financial Management course',
    date: 'Wed, May, 2024',
    icon: "hugeicons:teaching"
  },
  {
    title: 'Caltech 6 months audit training certificate',
    date: 'Wed, May, 2024',
    icon: "akar-icons:file"
  },
  {
    title: 'MIT Financial Management course',
    date: 'Wed, May, 2024',
    icon: "hugeicons:teaching"
  },
  {
    title: 'Caltech 6 months audit training certificate',
    date: 'Wed, May, 2024',
    icon: "akar-icons:file"
  },

])

const deleteCert = (cert)=>{
  deleteModal.value = true
  selectedCert.value = cert
}

const selectedCert = ref()

const generateReport = () => {
  if (props.user?.id) {
    window.open(`/dashboard/pms/report/user/${props.user.id}`, '_blank')
  }
}

function formatDate(dateString) {
  return moment(dateString).format('h:mm A');
}

const getCheckIn = ()=>{
  if(props.user.attendance){
    let login = props.user?.attendance[0];
   if(login){
    return (formatDate(login.login_time))
   }
  }else{
    return "NIL"
  }
}

const viewCertificate = (cert)=>{
  viewCertificateModal.value = true
  selectedCert.value = cert
}

const getCheckOut = ()=>{
  if(props.user.attendance){
    let login = props.user?.attendance[0];
   if(login){
    if(login.logout_time){
      return (formatDate(login.logout_time))
    }else{
      return 'NIL'
    }
   }
  }else{
    return "NIL"
  }
}

const timeDif = (login, logout) => {
  const start = moment(login);
  const end = moment(logout);

  const differenceInHours = end.diff(start, 'hours');
  const differenceInMinutes = end.diff(start, 'minutes') % 60;

  return `${differenceInHours}h ${differenceInMinutes}m`;
}

const totalTimeSpent = () => {
  if (props.user?.attendance && props.user.attendance.length > 0) {
    let login = props.user.attendance[0];
    if (login && login.login_time && login.logout_time) {
      return timeDif(login.login_time, login.logout_time);
    }
  }
  return "--";
}

const getDate = () => {
  const attendance = props.user?.attendance;
  if (attendance && attendance.length > 0) {
    const item = attendance[0];
    return moment(item.created_at || item.login_time).format('ddd, MMM Do, YYYY');
  }
  return moment().format('ddd, MMM Do, YYYY');
}

function certDate(date) {
  return moment(date).format('ddd, MMM Do, YYYY');
}
</script>

<style scoped>
</style>
