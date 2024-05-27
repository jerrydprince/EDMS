<template>
  <div class="lg:px-7 px-5 flex items-center justify-between pt-5">
    <div class="flex items-center gap-5">
      <div>
        <Icon icon="mingcute:user-1-line" width="50" class=" border p-3 rounded-full" />
      </div>
      <div>
        <h1 class="text-3xl text-[#1D2939] ">Profile Settings</h1>
        <p class=" text-gray-500">Customize and edit essential profile details.</p>
      </div>
    </div>
    <div class=" flex justify-end ">
      <div class="flex items-center gap-3">
        <button class="py-2 px-10 border font-light rounded-lg">
          <p>Cancel</p>
        </button>

        <button class="py-2 bg-[#3575DD] px-10 border-0 text-white font-light hover:bg-[#3575DD]/90 rounded-lg">
          <p>Save</p>
        </button>
      </div>
    </div>
  </div>
  <Divider class=" " />
  <div class="lg:px-7 px-5 ">
    <div class=" flex items-center  gap-36 ">
      <div>
        <h1 class=" text-lg font-semibold">Profile Picture</h1>
        <p class="#667085 text-base font-light">Min 400x400px, PNG or JPEG formats</p>
      </div>

      <div class=" flex gap-10 items-center">
        <img src="/assets/images/profile.svg" alt="">


        <button class="bg-white border-gray-300 border py-2 px-4 rounded-lg relative cursor-pointer">
          <input type="file" class="opacity-0 absolute w-full cursor-pointer" />Upload
        </button>
      </div>
    </div>

    <Divider class=" " />

    <div class=" flex items-center  gap-32 ">
      <div>
        <h1 class=" text-lg font-semibold">Full Name</h1>
        <p class="#667085 text-base font-light">Your name will be visible to your network</p>
      </div>


      <div class=" flex gap-10 items-center">
        <input type="text" class=" outline-none p-2 border border-gray-300 rounded-md" :value="props.user.last_name" disabled>
        <input type="text" class=" outline-none p-2 border border-gray-300 rounded-md" :value="props.user.first_name" disabled>
      </div>
    </div>

    <Divider />

    <div class=" flex items-center gap-44 ">
      <div>
        <h1 class=" text-lg font-semibold">Email Address</h1>
        <p class="#667085 text-base font-light">Organisation email recommended</p>
      </div>

      <div class=" flex items-center gap-3">
        <div class=" flex  items-center  border border-gray-300 rounded-md overflow-hidden ">
          <i class="pi pi-envelope text-gray-300 px-2"></i>
          <input type="text" class=" outline-none p-2  w-[400px]" :value="props.user.email" disabled>
        </div>
        <div class=" " v-tooltip.bottom="{
          value: `Verify your mail as soon as possible`,
          pt: {
            arrow: {
              style: {
                borderRightColor: '',
              },
            },
            text: 'bg-[#1D2939] text-[10px] p-2 text-white font-medium  rounded',
          },
        }">
          <Icon icon="ic:baseline-error" class=" text-[#F79009] cursor-pointer " width="30" @click="EmailModal = true" />
        </div>

      </div>

    </div>

    <Divider />


    <div class=" flex items-center  gap-52 ">
      <div>
        <h1 class=" text-lg font-semibold">Phone Number</h1>
        <p class="#667085 text-base font-light">Work number recommended.</p>
      </div>


      <div class=" flex  items-center  border border-gray-300 rounded-md overflow-hidden ">
        <p class=" p-2 border-r text-gray-300">+234</p>
        <input type="text" class=" outline-none p-2  w-[380px]" disabled :value="props.user.phone">
      </div>
    </div>


    <Divider />
  </div>
  <VerifyEmail v-model="EmailModal" />
</template>

<script setup>
import { ref, defineProps } from 'vue';
import VerifyEmail from '../../../components/Modals/VerifyEmail.vue';

const EmailModal = ref(false)
const props = defineProps(['user'])
</script>

<style></style>