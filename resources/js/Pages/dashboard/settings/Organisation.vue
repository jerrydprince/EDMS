<template>
  <div class="lg:px-7 px-5 flex items-center justify-between pt-5">
    <div class="flex items-center gap-5">
      <div class=" border rounded-full p-3">
        <Icon icon="mdi:company" width="30" />
      </div>
      <div>
        <h1 class="text-3xl text-[#1D2939] ">Organisation Settings</h1>
        <p class=" text-gray-500">Customize and edit essential organisational details.</p>
      </div>
    </div>
    <div class=" flex justify-end ">
      <div class="flex items-center gap-3">
        <button class="py-2 px-10 border font-light rounded-lg">
          <p>Cancel</p>
        </button>

        <button class="py-2 bg-[#3575DD] px-10 border-0 text-white font-light hover:bg-[#3575DD]/90 rounded-lg" @click="save">
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
        <p class="#667085 text-base font-light">The official name of your company.</p>
      </div>


      <div class=" flex  items-center  border border-gray-300 rounded-md overflow-hidden ">
        <input id="company_name" type="text" v-model="form.company_name" class=" outline-none p-2  w-[400px]" placeholder="Ansel Technologies LTD">
      </div>
      <div v-if="form.errors.email" class=" text-red-400 text-sm">{{ form.errors.email }}</div>
    </div>

    <Divider />

    <div class=" flex items-center  gap-28 ">
      <div>
        <h1 class=" text-lg font-semibold">Email Address</h1>
        <p class="#667085 text-base font-light w-[80%]">The official email address of the organisation.</p>
      </div>


      <div class=" flex  items-center  border border-gray-300 rounded-md overflow-hidden ">
        <i class="pi pi-envelope text-gray-300 px-2"></i>
        <input id="email" type="email" required v-model="form.email" class=" outline-none p-2  w-[365px]" placeholder="davemiye@gmail.com">
      </div>
      <div v-if="form.errors.email" class=" text-red-400 text-sm">{{ form.errors.email }}</div>
    </div>

    <Divider />


    <div class=" flex items-center  gap-24 ">
      <div>
        <h1 class=" text-lg font-semibold">Phone Number</h1>
        <p class="#667085 text-base font-light w-[80%]">The official contact number of the organisation.</p>
      </div>


      <div class=" flex  items-center  border border-gray-300 rounded-md overflow-hidden ">
        <p class=" p-2 border-r text-gray-300">+234</p>
        <input id="phone" v-model="form.phone" type="number" class=" outline-none p-2  w-[350px]" placeholder="9074713739">
      </div>
      <div v-if="form.errors.email" class=" text-red-400 text-sm">{{ form.errors.email }}</div>
    </div>
    <Divider class=" " />

    <div class=" flex items-end  gap-40 ">
      <div>
        <div>
          <h1 class=" my-5 text-base text-[#788CAB]">Branding</h1>
        </div>
        <h1 class=" text-lg font-semibold">Upload Logo</h1>
        <p class="#667085 text-base font-light">Min 400x400px, PNG or JPEG formats</p>
      </div>

      <div class=" flex gap-10 items-center">
        <img :src="url" ref="profile_img" alt="" class="rounded-full border border-gray-200 h-[80px] w-[80px] cursor-pointer" @click="uploadProfileImage"/>


        <button class="bg-white border-gray-300 border py-2 px-4 rounded-lg relative cursor-pointer" @click="uploadProfileImage">
          <input type="file" @change="handleFileUpload" id="organisation-file" class="opacity-0 absolute w-full cursor-pointer" />Upload
        </button>
      </div>
    </div>

    <Divider class=" " />

    <div class=" flex items-center gap-24 ">
      <div>
        <h1 class=" text-lg font-semibold">Customize Primary Colour</h1>
        <p class="#667085 text-base font-light ">Customize your Company’s interfaces to match your brand.</p>
      </div>


      <div class=" flex  items-center  gap-5  rounded-full  ">
        <input v-model="outputHex_primary"
          class=" outline-none p-2  w-[350px] border border-gray-300 rounded-md overflow-hidden"
          placeholder="9074713739">
        <div class="">
          <div class=" h-8 w-8 rounded-full overflow-hidden relative">
            <input @input="outputHexPrimary" v-model="form.primary_color" type="color" class=" h-32 w-32 absolute -bottom-20 -left-20">
          </div>
        </div>
      </div>
    </div>

    <Divider class=" " />

    <div class=" flex items-center gap-24 ">
      <div>
        <h1 class=" text-lg font-semibold">Customize Secondary Colour</h1>
        <p class="#667085 text-base font-light ">Customize your Company’s interfaces to match your brand.</p>
      </div>


      <div class=" flex  items-center  gap-5  rounded-full  ">
        <input v-model="outputHex_secondary"
          class=" outline-none p-2  w-[350px] border border-gray-300 rounded-md overflow-hidden"
          placeholder="9074713739">
        <div class="">
          <div class=" h-8 w-8 rounded-full overflow-hidden relative">
            <input @input="outputHexSecondary" type="color" class=" h-32 w-32 absolute -bottom-20 -left-20" v-model="form.secondary_color">
          </div>
        </div>
      </div>
    </div>
    <Divider class=" " />
  </div>
</template>

<script setup>
import { ref, watch, computed, defineProps, onMounted } from 'vue'
import {usePage, useForm} from "@inertiajs/vue3"
import { useToast } from "primevue/usetoast";

const selectedColor = ref('#000000');

const props = defineProps();

const outputHex_primary = ref('');
const outputHex_secondary = ref('');
const page = usePage()
const roles = computed(()=> page.props.user_roles)
const file = ref(null)
const url = ref('')
const toast = useToast()
const form = useForm({
  file: null,
  company_name:'',
  email:'',
  phone: '',
  primary_color:'',
  secondary_color: ''
})

const save = ()=>{
  if(form.company_name == ''){
    document.getElementById('company_name').focus();
    return false;
  }else if(form.email ==''){
    document.getElementById('email').focus();
    return false;
  }else if(form.phone == ''){
    document.getElementById('phone').focus();
    return false;
  }

  form.post('settings/organisation',{onSuccess: ()=>{
    // form.reset();
    // file.value = null
    // url.value = null;
    // outputHex_secondary.value = ""
    // outputHex_primary.value = ""
    toast.add({
                    severity: "success",
                    summary: "Success",
                    detail: "You have successfully uploaded a profile image.",
                    life: 3000,
                });
  }})
}

const uploadProfileImage = () => {
  document.getElementById('organisation-file').click();
}

const handleFileUpload = (event)=>{
  file.value = event.target.files[0];
  form.file = file.value
  url.value =  URL.createObjectURL(file.value); 
}

const outputHexPrimary = (event)=>{
  outputHex_primary.value = event.target.value;
}

watch(()=>props.organisation, (newVal)=>{
  form = newVal
})

const outputHexSecondary = (event)=>{
  outputHex_secondary.value = event.target.value;
}

const getOrganisation = ()=>{
  axios.get('/settings/get-organisation').then((response)=>{
    form.value = response.data

    if(response.data.logo){
      url.value = page.props.base+'/'+response.data.logo
    }
    form.company_name = response.data.company_name,
    form.email = response.data.email,
    form.phone = response.data.phone,
    form.primary_color = response.data.primary_color,
    form.secondary_color = response.data.secondary_color

    outputHex_secondary.value = response.data.secondary_color
    outputHex_primary.value = response.data.primary_color
  })
}

onMounted(()=>{
  getOrganisation()
})

</script>

<style></style>