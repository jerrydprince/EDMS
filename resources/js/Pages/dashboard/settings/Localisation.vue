<template>
   <div class="lg:px-7 px-5 flex items-center justify-between pt-5">
      <div class="flex items-center gap-5">
        <div class=" border rounded-full p-3"  >
          <Icon icon="basil:notification-outline" width="30" />
        </div>
        <div>
          <h1 class="text-3xl text-[#1D2939] ">Localisation</h1>
          <p class=" text-gray-500">Customise settings to fit your location.</p>
        </div>
      </div>
    </div>
    <Divider class=" " />

    <div class=" lg:pl-7  ">
      <div class=" flex items-center justify-between  pr-7">
          <div >
          <h1 class="text-xl text-[#1D2939] font-semibold ">Language</h1>
          <p class=" text-gray-500">Select the language for your interface on your device.</p>
        </div>
        <div>
          <select name="" id="" class=" p-2 rounded-md border border-gray-300 text-base w-40" >\
            <option value="English">English</option>
            <option value="English">English</option>
            <option value="English">English</option>
          </select>
        </div>
        </div>
        <Divider/>

        <div class=" flex items-center justify-between  pr-7">
          <div >
          <h1 class="text-xl text-[#1D2939] font-semibold ">Time Format</h1>
          <p class=" text-gray-500">customise your time format preference.</p>
        </div>
        <div>
          <select name="" id="" class=" p-2 rounded-md border border-gray-300 text-base w-40" >\
            <option value="24 Hours">24 Hours</option>
            <option value="24 Hours">24 Hours</option>
            <option value="24 Hours">24 Hours</option>
          </select>
        </div>
        </div>
        <Divider/>


        <div class=" flex items-center justify-between  pr-7">
          <div >
          <h1 class="text-xl text-[#1D2939] font-semibold ">Date Format</h1>
          <p class=" text-gray-500">customise your date format preference.</p>
        </div>
        <div>
          <select name="" id="" class=" p-2 rounded-md border border-gray-300 text-base w-40" >\
            <option value="DD/MM/YY">DD/MM/YY</option>
            <option value="DD/MM/YY">DD/MM/YY</option>
            <option value="DD/MM/YY">DD/MM/YY</option>
          </select>
        </div>
        </div>
        <Divider/>
    </div>
</template>

<script setup>

</script>

<style>

</style>