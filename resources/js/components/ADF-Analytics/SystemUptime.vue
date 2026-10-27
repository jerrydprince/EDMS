<template>
    <div class=" flex items-center justify-end w-full ">
    <div class=" flex items-center gap-5 mt-7">   

    <div class=" flex flex-wrap items-center  bg-white p-1 w-fit text-[12px]  rounded-3xl text-gray-600">
     <button class=" px-5 py-1.5 md:py-2 rounded-2xl  "   :class="toggle_button === 1 ? 'bg-gray-800 text-white' : ''" @click="toggle_button =1">All</button>
     <button class=" px-5 py-1.5 md:py-2 rounded-2xl " :class="toggle_button === 2 ? 'bg-gray-800 text-white' : ''" @click="toggle_button =2">1 Year</button>
     <button class=" px-5 py-1.5 md:py-2 rounded-2xl " :class="toggle_button === 3 ? 'bg-gray-800 text-white' : ''" @click="toggle_button =3">3 mnths</button>
     <button class=" px-5 py-1.5 md:py-2 rounded-2xl " :class="toggle_button === 4 ? 'bg-gray-800 text-white' : ''" @click="toggle_button =4">1 mnths</button>
     <button class=" px-5 py-1.5 md:py-2 rounded-2xl " :class="toggle_button === 5 ? 'bg-gray-800 text-white' : ''" @click="toggle_button =5">1 week</button>
     <button class=" px-5 py-1.5 md:py-2 rounded-2xl " :class="toggle_button === 6 ? 'bg-gray-800 text-white' : ''" @click="toggle_button =6">24 hours</button>

    </div>

    <div class=" bg-white p-4 rounded-full w-fit relative ">
        <Icon icon="uil:calender" />
        <input type="date" name="" id="" class=" absolute top-0 left-0 right-0 cursor-pointer opacity-0  w-[35px] h-full">
    </div>
</div>
</div>
   <div class="rounded-2xl border border-gray-100 bg-white mt-3 p-5">
    <div class=" flex items-center w-full justify-between">
    <h1 class="bg-slate-50 p-2 rounded-2xl w-fit px-5 mb-3 whitespace-nowrap">
        System Uptime
    </h1>
    <div class=" flex items-center gap-2 text-[12px]">
        <p class=" text-gray-500">Current Status</p>

        <div class="   w-fit flex items-center gap-1 text-green-500 py-1.5 md:py-0.5 px-3 rounded-2xl bg-slate-50">

          <Icon icon="oui:dot" />
          <p>Online</p>
        </div>
    </div>
</div>

<div class=" mt-5 p-5 rounded-lg border border-gray-100 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10 ">
     <div >
        <p class=" text-[12px text-gray-500">Uptime</p>
      <h1 class=" text-[30px] text-gray-800 font-semibold my-2">99.98% </h1>
      <div class=" mt-1 flex items-center gap-2 text-[10px]  w-fit text-gray-500 bg-gray-50 p-2 rounded-md ">
        <Icon icon="quill:warning-alt" class=" rotate-180"  width="18"/>
        <p class=" "> The percentage of time the system has been operational. </p>
      </div>
     </div>
     <div >
        <p class=" text-[12px text-gray-500">Downtime</p>
      <h1 class=" text-[30px] text-gray-800 font-semibold my-2">1 hour 45 minutes </h1>
      <div class=" mt-1 flex items-center gap-2 text-[10px]  w-fit text-gray-500 bg-gray-50 p-2 rounded-md ">
        <Icon icon="quill:warning-alt" class=" rotate-180" width="18"/>
        <p class=" "> The total amount of time the system was not operational </p>
      </div>
     </div>
     <div >
        <p class=" text-[12px text-gray-500">Incidents</p>
      <h1 class=" text-[30px] text-gray-800 font-semibold my-2">3</h1>
      <div class=" mt-1 flex items-center gap-2 text-[10px]  w-fit text-gray-500 bg-gray-50 p-2 rounded-md ">
        <Icon icon="quill:warning-alt" class=" rotate-180" width="18"/>
        <p class=" "> The total number of system outages that occurred between specified timeframe</p>
      </div>
     </div>

     </div>

  </div>
</template>

<script setup>
import { ref } from "vue";
const toggle_button = ref(1)
</script>

<style>

</style>
