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

        <button class="py-2 bg-[#3575DD] px-10 border-0 text-white font-light hover:bg-[#3575DD]/90 rounded-lg" @click="uploadImage">
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
         <img :src="url" ref="profile_img" alt="" class="rounded-full border border-gray-200 h-[80px] w-[80px]"/>


         <button class="bg-white border-gray-300 border py-2 px-4 rounded-lg relative cursor-pointer" @click="uploadProfileImage">
          Browse
         </button>
        <input type="file" @change="handleFileUpload" value="" class="opacity-0 absolute w-full cursor-pointer"  id="profile_img_file" />
       </div>
    </div>

    <Divider class=" " />

    <div class=" flex items-center  gap-32 ">
      <div>
        <h1 class=" text-lg font-semibold">Full Name</h1>
        <p class="#667085 text-base font-light">Your name will be visible to your network</p>
      </div>


      <div class=" flex gap-10 items-center">
        <input type="text" class=" outline-none p-2 border border-gray-300 rounded-md" :value="props.user.last_name"
          disabled>
        <input type="text" class=" outline-none p-2 border border-gray-300 rounded-md" :value="props.user.first_name"
          disabled>
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
          <Icon icon="ic:baseline-error" class=" text-[#F79009] cursor-pointer " width="30"
            @click="EmailModal = true" />
        </div>

      </div>

    </div>

    <Divider />

    <div class=" flex items-center gap-44 ">
      <div>
        <h1 class=" text-lg font-semibold">Email Password</h1>
        <p class="#667085 text-base font-light">Enter password to receive Email</p>
      </div>

      <div class=" flex items-center gap-3">
        <div class=" flex  items-center  border border-gray-300 rounded-md overflow-hidden ">
          <!-- <i class="pi pi-key text-gray-300 px-2"></i> -->
          <input :type="showPassword ? 'text' : 'password'" class=" outline-none p-2  w-[400px]" v-model="form.email_password">
          <i :class="showPassword ? 'pi pi-eye-slash' : 'pi pi-eye'" class="text-gray-300 px-2 cursor-pointer" @click="showPassword = !showPassword"></i>
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
import { ref, defineProps, watch, computed } from 'vue';
import VerifyEmail from '../../../components/Modals/VerifyEmail.vue';
import { useForm, usePage } from '@inertiajs/vue3'
import { useToast } from "primevue/usetoast";

const EmailModal = ref(false)
const props = defineProps(['user'])
const file = ref(null)
const profile_img = ref('')
const url = ref('')
const toast = useToast();
const page = usePage()
const roles = computed(()=> page.props.user_roles)
const showPassword = ref(false)
const permissions = computed(()=> page.props.permissions)

const form = useForm({
  file: null,
  email_password: props.user.email_password? props.user.email_password : '',
})



const uploadProfileImage = () => {
  document.getElementById('profile_img_file').click();
}

const handleFileUpload = (event)=>{
  file.value = event.target.files[0];
  form.file = file.value
  url.value =  URL.createObjectURL(file.value); 
}

const uploadImage = ()=>{
  let url = '';
  if(roles.value.includes('data entry')){
    url = '/edms/update-profile-image'
  }else{
    url = '/dashboard/update-profile-image'
  }
  form.post(url,{onSuccess: ()=>{
    toast.add({
                    severity: "success",
                    summary: "File Uploaded",
                    detail: "You have successfully uploaded a profile image.",
                    life: 3000,
                });
  }})
}

watch(()=>props.user, ()=>{
  if(props.user && props.user.profile_image){
    url.value = page.props.base+'/'+props.user.profile_image;
  }
  
},
{immediate:true})


</script>

<style></style>