<template>
  <div class="lg:px-7 px-5 flex items-center justify-between pt-5">
      <div class="flex items-center gap-5">
        <div class=" border rounded-full p-3"  >
          <Icon icon="material-symbols:room-preferences-rounded" width="30" />
        </div>
        <div>
          <h1 class="text-3xl text-[#1D2939] ">Preferences</h1>
          <p class=" text-gray-500">personalize your experiences & Preferences.</p>
        </div>
      </div>
      
    </div>
    <Divider />

    <div class="px-7">
      <div class=" ">
        <div>
          <h1 class="text-3xl text-[#1D2939] ">Appearance</h1>
          <p class=" text-gray-500">Pick theme to Personalize experience.</p>
        </div>

        <div class=" flex mt-10 gap-10">
         <div class=" bg-[#F9FAFB] border rounded-md overflow-hidden ">
            <div class=" flex gap-2 font-semibold items-center text-[#344054] px-4 py-4">

                <Icon
          icon="heroicons:sun-20-solid"
          width="20"
          class=" mt-1"
        />
        <p>Light Mode</p>
            </div>
            <img :src="'/assets/images/themes/light_theme.svg'" alt="" class=" p-4 bg-white w-80">
            <p class="px-4 py-3 bg-white text-[#1D2939] font-semibold">Default Light</p>
         </div>
         <div class=" bg-[#F9FAFB] border rounded-md overflow-hidden ">
            <div class=" flex gap-2 font-semibold items-center text-[#344054] px-4 py-4">

              <Icon icon="ph:moon-fill" width="20" class=" mt-1"/>
        <p>Dark Mode</p>
            </div>
            <img :src="'/assets/images/themes/dark_theme.svg'" alt="" class=" p-4 bg-white w-80">
            <p class="px-4 py-3 bg-white text-[#1D2939] font-semibold">Default Dark</p>
         </div>
         <div class=" bg-[#F9FAFB] border rounded-md overflow-hidden ">
            <div class=" flex gap-2 font-semibold items-center text-[#344054] px-4 py-4 ">

              <Icon icon="basil:desktop-outline"   width="20" class=" mt-1"/>
        <p>System</p>
            </div>
            <img :src="'/assets/images/themes/default.svg'" alt="" class=" p-4 bg-white w-80">
            <p class="px-4 py-3 bg-white text-[#1D2939] font-semibold">Default System</p>
         </div>
        </div>
        
      </div>
      <Divider class=" mt-10"/>

      <div class=" ">
        <div>
          <h1 class="text-3xl text-[#1D2939] ">Font Size</h1>
          <p class=" text-gray-500">Adjust the font size to match your preference.</p>
        </div>

        <div class=" flex mt-10 justify-between items-end pb-20">
            <div class=" bg-[#F9FAFB] border border-[#EAECF0] w-3/6 p-3 rounded-lg">
            <button class=" p-1 rounded-2xl px-3 bg-white border border-gray-300">Preview</button>
          <p class=" mt-2 text-base">
            Mini habits compel you to take that first step, because it's so easy. And even if you step back into your safe zone right after that, you're stepping out again tomorrow.
          </p>
        </div>
        
  <div>
        <div class=" flex items-center ">
          
          <h1  class=" text-xl font-medium">A</h1>
        
    <input
      type="range"
      v-model="value"
      :min="0"
      :max="100"
     class=" h-[1px] w-96"

    />
   
          <h1 class=" text-3xl font-semibold">A</h1>
        
        </div>
      <p class=" text-center">Make the text on screen smaller or larger</p>
      </div>
        <div>
          
        </div>
        </div>
      </div>
    </div>
</template>

<script setup>

</script>