<template>
   <div class="lg:px-7 px-5 flex items-center justify-between pt-5">
      <div class="flex items-center gap-5">
        <div class=" border rounded-full p-3"  >
          <Icon icon="mdi:company" width="30" />
        </div>
        <div>
          <h1 class="text-3xl text-[#1D2939] ">Organisation Settings</h1>
          <p class=" text-gray-500">Customize and edit essential organisational details.</p>
        </div>
      </div>
      <div class=" flex justify-end ">
      <div class="flex items-center gap-3">
        <button
          class="py-2 px-10 border font-light rounded-lg"
         
        >
          <p>Cancel</p>
        </button>

        <button
          class="py-2 bg-[#3575DD] px-10 border-0 text-white font-light hover:bg-[#3575DD]/90 rounded-lg"
        >
          <p>Save</p>
        </button>
      </div>
     </div>
    </div>
    <Divider class=" " />

    <div class=" lg:px-7 px-5 ">
    <div class=" flex items-end  gap-44 ">
       <div>
        <div>
            <h1 class=" my-5 text-base text-[#788CAB]">Company Name</h1>
        </div>
        <h1 class=" text-lg font-semibold">Legal Name</h1>
        <p class="#667085 text-base font-light" >The official name of your company.</p>
       </div>

       
       <div class=" flex  items-center  border border-gray-300 rounded-md overflow-hidden ">
        <input type="text" class=" outline-none p-2  w-[400px]" placeholder="Ansel Technologies LTD">
       </div>
    </div>

    <Divider/>

    <div class=" flex items-center  gap-28 ">
       <div>
        <h1 class=" text-lg font-semibold">Email Address</h1>
        <p class="#667085 text-base font-light w-[80%]"  >The official email address of the organisation.</p>
       </div>

       
       <div class=" flex  items-center  border border-gray-300 rounded-md overflow-hidden ">
        <i
            class="pi pi-envelope text-gray-300 px-2"
          ></i>
        <input type="text" class=" outline-none p-2  w-[365px]" placeholder="davemiye@gmail.com">
       </div>
    </div>

    <Divider/>


    <div class=" flex items-center  gap-24 ">
       <div>
        <h1 class=" text-lg font-semibold">Phone Number</h1>
        <p class="#667085 text-base font-light w-[80%]" >The official contact number of the organisation.</p>
       </div>

       
       <div class=" flex  items-center  border border-gray-300 rounded-md overflow-hidden ">
        <p class=" p-2 border-r text-gray-300">+234</p>
        <input type="text" class=" outline-none p-2  w-[350px]" placeholder="9074713739">
       </div>
    </div>
    <Divider class=" "/>
    
    <div class=" flex items-end  gap-40 ">
       <div>
        <div>
            <h1 class=" my-5 text-base text-[#788CAB]">Branding</h1>
        </div>
        <h1 class=" text-lg font-semibold">Upload Logo</h1>
        <p class="#667085 text-base font-light">Min 400x400px, PNG or JPEG formats</p>
       </div>

       <div class=" flex gap-10 items-center">
         <img src="/assets/images/organisation.svg" alt="">


         <button class="bg-white border-gray-300 border py-2 px-4 rounded-lg relative cursor-pointer">
        <input type="file" class="opacity-0 absolute w-full cursor-pointer" />Upload
      </button>
       </div>
    </div>

    <Divider class=" " />

    <div class=" flex items-center gap-24 ">
       <div>
        <h1 class=" text-lg font-semibold">Customize Primary Colour</h1>
        <p class="#667085 text-base font-light " >Customize your Company’s interfaces to match your brand.</p>
       </div>

       
       <div class=" flex  items-center  gap-5  rounded-full  ">
        <input   v-model="outputHex" class=" outline-none p-2  w-[350px] border border-gray-300 rounded-md overflow-hidden" placeholder="9074713739">
        <div class="">
        <div class=" h-8 w-8 rounded-full overflow-hidden relative">
        <input type="color" class=" h-32 w-32 absolute -bottom-20 -left-20" v-model="selectedColor" >
    </div>
    </div>
       </div>
    </div>
    <Divider class=" "/>
  </div>
</template>

<script setup>
     import {ref,watch} from 'vue'
     const selectedColor = ref('#000000'); 

  const outputHex = ref('');

  watch(selectedColor, (newColor) => {
    outputHex.value = newColor;
  });

  outputHex.value = selectedColor.value;
</script>

<style>

</style>