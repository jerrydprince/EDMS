<template>
    <Dialog v-model:visible="visible" :closable="false" modal :pt="{
      content: { class: 'px-0 bg-white pb-6' },
    }" :style="{ width: '35rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
      class=" .custom-dialog">
  
      <section class="-mb-6  flex flex-col items-center ">
        <div class=" bg-white p-7 -mt-12  relative">
          <img :src="grid" alt="">
        </div>
        <div class=" border rounded-full p-3 absolute bg-[#F9FAFB] top-20">
          <Icon icon="lucide:lock" width="50" />
        </div>
        <div>
          <h1 class=" font-semibold text-2xl  text-center text-red">Verify 2FA</h1>
          <p class=" text-[#667085] my-4 text-center">Please download google authenticator from PlayStore for android users or from Apple Store for Iphone users and scan the QR code below.</p>
          <div class=" bg-[#FFFCF5] flex justify-center border-dashed border-[2px] border-[#FEC84B] rounded-md mx-7 p-3">
            <span v-if="twoFactor" v-html="twoFactor?.svg" class="text-center justify-center"></span>
          </div>
        </div>
  
        <div class="w-full px-6 py-4">
            <label for="">Enter the 2FA Code:</label>
            <input v-model="code" type="number" class="w-full w-full border border-gray-300 p-2 rounded-md mt-2"
              placeholder="Enter code" />
        </div>
        <Divider />
      </section>
  
      <template #footer>
      
        <Button class="mr-4" :pt="{
          root: {
            class: 'py-2 px-8 border-gray-200 border border-solid rounded-lg',
          },
        }" @click="closeModal" label="Cancel" severity="secondary" outlined />
  
        <Button  @click="verify" label="Verify 2FA" class="" :pt="{
          root: {
            class:
              'py-2 bg-blue_primary px-10 border-0 text-white font-light hover:bg-blue_primary/90 rounded-lg',
          },
        }" />
       
  
        
      </template>
    </Dialog>
  </template>
  
  <script setup>
  import { defineEmits, onMounted, defineProps, watch, computed, ref } from "vue";
  import { useToast } from "primevue/usetoast";
  import { router } from "@inertiajs/vue3";
  import axios from "axios";
  import grid from '../../assets/images/grid.svg'
  
  
  const active = ref(1)
  const code = ref(null)
  const toast = useToast();
  const props = defineProps(["modelValue", 'user', 'twoFactor']);
  const emit = defineEmits(["update:modelValue", "refresh2fa"]);

  //const twoFactor = ref('')
  
  const showPassword = ref(false);
  
  // Computed property to determine the input type
  const passwordFieldType = computed(() => (showPassword.value ? 'text' : 'password'));
  
  // Method to toggle password visibility
  const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
  };
  
  const visible = computed({
    get() {
      return props.modelValue;
    },
    set(value) {
      emit("update:modelValue", value);
    },
  });
  
  const closeModal = () => {
    active.value = 1
    emit("update:modelValue", false);
  };

  const verify = ()=>{
    axios.post('/user/confirmed-two-factor-authentication', {code: code.value}).then((response)=>{
        console.log(response.status)
        emit('refresh2fa')
        if(response.status == 200){
           
            emit("update:modelValue", false);
            toast.add({
                severity: "success",
                summary: 'Code verification',
                detail: "Code verification successfull.",
                life: 3000,
            });
        }else{
            toast.add({
                severity: "error",
                summary: 'Code verification',
                detail: "Invalid code supplied",
                life: 3000,
            });
        }
    })
  
  }

//   const getTwoFactorCode = ()=>{
//         axios.get('/user/two-factor-qr-code').then((response)=>{
//             twoFactor.value = response.data
//         })
        
//     }

//    watch(()=>props.user, ()=>{
//         getTwoFactorCode()
//    })

//  onMounted(()=>{
//     getTwoFactorCode()
//  })
  </script>
  <style scoped>
  .custom-dialog .p-dialog-header {
    display: none;
    width: 20%;
  }
  </style>