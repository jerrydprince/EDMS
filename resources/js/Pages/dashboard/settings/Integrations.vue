<template>
   <div class="lg:px-7 px-5 flex items-center justify-between pt-5">
      <div class="flex items-center gap-5">
        <div class=" border rounded-full p-3"  >
          <Icon icon="mi:filter" width="30" />
        </div>
        <div>
          <h1 class="text-3xl text-[#1D2939] ">Integrations</h1>
          <p class=" text-gray-500">Connect and sync with essential tools and platforms.</p>
        </div>
      </div>
    </div>
    <Divider class=" " />

    <div class=" lg:pl-7  ">

      
      <section class=" lg:flex items-center justify-between  pr-7">
       
       <div class="flex items-center justify-between  lg:w-[40%] text-sm">
     <div class="bg-gray-100 flex items-center rounded-md p-1">
       <button
         class="bg-white shadow-md border-gray-300 rounded-lg py-1 px-4"
       >
         <p>All Apps</p>
       </button>
       <button
         class="flex gap-1 items-center text-gray-400 rounded-lg py-1 px-4"
       >
         <p>Connected</p>
       </button>
       <button
         class="flex gap-1 items-center text-gray-400 rounded-lg py-1 px-4"
       >
         <p>Disconnected</p>
       </button>
     </div>
   
   </div>

 <div class=" flex items-center border p-2 rounded-lg gap-2 text-sm mt-5 lg:mt-0">
     <i class="pi pi-search text-gray-300 " style="font-size: 1rem"></i>
     <Input type="text" v-model="value" placeholder="Search Apps" class=" w-96 border-0 outline-none text-[#98A2B3]" />
 </div>
   </section>

   <div class=" mt-10">
    <div>
          <h1 class="text-2xl font-semibold text-[#1D2939] ">All Apps</h1>
          <p class=" text-gray-500">Access all the integrated tools and apps ready for document management</p>
        </div>
   </div>
   
      <Divider/>
    
     <!-- ================  All Apps ========= -->
     <div class=" w-full flex gap-5 flex-wrap justify-between pr-7">
      <div v-for="(item,index) in apps" :key="index" class=" border rounded-md border-gray-30 w-[350px] p-5 ">
        <div class=" flex gap-2 items-start  ">
        <div class=" ">
            <Icon :icon="item.logo" width="70" class="p-3 bg-[#F9FAFB] "/>
            </div>
           <div>
            <h1 class="text-xl font-semibold text-[#1D2939] "> {{ item.name }}</h1>
            <p class=" text-gray-500 text-sm">{{ item.desc }}</p>
           </div>
          </div>
          <button @click="toggleConnect(index)" class="w-full border border-gray-300 p-2 rounded-md mt-2 font-semibold"
              :class="{ 'bg-[#EBF3FE]': isConnected(index), 'text-[#3575DD]': isConnected(index), 'border-[#3575DD]': isConnected(index) }">
        {{ isConnected(index) ? 'Connected' : 'Connect' }}
      </button>
     </div>
    </div>
    </div>
</template>

<script setup>
   import {ref} from 'vue'
   const apps = ref([
    {
      logo: 'logos:trello',
      name: 'Google Calendar',
      desc: 'Integration with google dropbox for easy Storage/backup of documents.'
    },
    {
      logo: 'logos:google-calendar',
      name: 'Google Calendar',
      desc: 'Integration with google dropbox for easy Storage/backup of documents.'
    },
    {
      logo: 'logos:google-drive',
      name: 'Google Calendar',
      desc: 'Integration with google dropbox for easy Storage/backup of documents.'
    },
    {
      logo: 'logos:dropbox',
      name: 'Google Calendar',
      desc: 'Integration with google dropbox for easy Storage/backup of documents.'
    },
    {
      logo: 'logos:trello',
      name: 'Google Calendar',
      desc: 'Integration with google dropbox for easy Storage/backup of documents.'
    },
    {
      logo: 'logos:google-calendar',
      name: 'Google Calendar',
      desc: 'Integration with google dropbox for easy Storage/backup of documents.'
    }
   ])
   const connectedStatus = ref(Array(apps.value.length).fill(false));

const toggleConnect = (index) => {
  connectedStatus.value[index] = !connectedStatus.value[index];
};

const isConnected = (index) => {
  return connectedStatus.value[index];
};

</script>

<style>

</style>