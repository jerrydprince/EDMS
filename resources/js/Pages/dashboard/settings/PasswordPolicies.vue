<template>
  <main>
    <div class="lg:px-7 px-5 flex items-center justify-between pt-5">
      <div class="flex items-center gap-5">
        <div @click="goBack" class="cursor-pointer">
          <i class="pi pi-arrow-left border px-3 py-2 rounded-md text-xs text-[#344054]"></i>
        </div>
        <div>
          <p class="text-3xl text-[#1D2939] font-medium">Password Requirements & Policies</p>
          <p class="text-gray-500">Changes here will affect all new passwords created, but not existing user passwords
          </p>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <button class="py-2 px-10 border font-light rounded-lg">
          <p>Cancel</p>
        </button>

        <button @click="save" class="py-2 bg-[#3575DD] px-10 border-0 text-white font-light hover:bg-[#3575DD]/90 rounded-lg">
          <p>Save</p>
        </button>
      </div>
    </div>
    <Divider />

    <!-- ============   change settings   ========= -->
    <div class=" px-7 mt-10 ">
      <h1 class=" font-medium text-lg text-primary">Character Settings</h1>

      <div class=" flex items-center gap-16 text-[#344054] mt-5 font-medium">
        <div>
          <label for="">Password Length</label> <br>
          <input type="range" min="0" max="10" v-model="range" class=" h-[3px] mt-1 w-96">
        </div>

        <div class=" flex items-center gap-2">
          <input type="text" disabled :value="range+' characters'" class=" p-2 border bg-transparent font-normal rounded-md">
          <div class=" " v-tooltip.top="{
            value: `Set minimum password length`,
            pt: {
              arrow: {
                style: {
                  borderRightColor: '',
                },
              },
              text: 'bg-[#1D2939] text-[10px] p-2 text-white font-medium  rounded',
            },
          }">
            <Icon icon="mingcute:warning-line" class=" cursor-pointer  " width="30" @click="EmailModal = true" />
          </div>

        </div>
      </div>
    </div>
    <Divider />

    <!-- ================   Password Requirements   ======= -->
    <div class=" px-7 text-[#344054] mt-10 font-medium">
      <h1 class=" mb-5  text-lg">Password Requirements</h1>
      <div class=" grid grid-cols-2 gap-5  ">
        <div class=" flex items-center gap-2">
          <label class="switch">
            <input type="checkbox" @change="characterToggle" v-model="toggle1"  />
            <span class="slider round"></span>
          </label>

          <p class=" font-normal">{{ range + ' characters' }}</p>
        </div>

        <div class=" flex items-center gap-2">
          <label class="switch">
            <input type="checkbox" v-model="toggle2"  />
            <span class="slider round"></span>
          </label>

          <p class=" font-normal">A special character</p>
        </div>

        <div class=" flex items-center gap-2">
          <label class="switch">
            <input type="checkbox" v-model="toggle3"  />
            <span class="slider round"></span>
          </label>

          <p class=" font-normal">An uppercase letter</p>
        </div>

        <div class=" flex items-center gap-2">
          <label class="switch">
            <input type="checkbox" v-model="toggle4" />
            <span class="slider round"></span>
          </label>

          <p class=" font-normal">A number</p>
        </div>

        <div class=" flex items-center gap-2">
          <label class="switch">
            <input type="checkbox" v-model="toggle5" />
            <span class="slider round"></span>
          </label>

          <p class=" font-normal">A lowercase</p>
        </div>
      </div>
    </div>
    <Divider />

    <!-- =================    Password Reset   ============== -->
    <div class=" px-7 mt-10">
      <h1 class=" mb-5  text-lg text-primary font-medium">Password Resets</h1>

      <div class=" border flex items-center border-[#F79009] justify-between bg-[#FFFCF5] rounded-md p-5">
        <div class=" flex items-start gap-2 w-[80%]">
          <Icon icon="carbon:warning-filled" width="30" class=" text-[#F79009]" />
          <div>
            <h1 class=" font-medium">Perform a global password reset now</h1>
            <p class=" text">Require all current users to reset their passwords next time they login. This will not log
              users out of their current sessions. 
              Action can be reversed only between <span class=" font-medium"> 5
                minutes </span> of its initiation</p>
          </div>
        </div>
        <button class=" bg-white border p-2 px-3 rounded-md">Initiate Reset</button>
      </div>

      <div class=" flex items-center gap-3 mt-5">
        <label class="switch">
          <input disabled type="checkbox" v-model="prevent_password_reusing" />
          <span class="slider round"></span>
        </label>

        <div class=" flex items-center gap-2">
          <p>Prevent reusing password for how </p>
          <select @change="preventPasswordChange" v-model="form.prevent_password_reusing" name="" id="" class=" outline-none border rounded-md py-1 px-3">
            <option value="0">0</option>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
          </select>
          <p>time(s)</p>
        </div>
      </div>
    </div>
    <Divider />

    <!-- ================    Password changes   ============== -->

    <div class=" px-7 mt-10">
      <h1 class=" mb-5  text-lg text-primary font-medium">Password Changes</h1>

      <div class=" flex items-center gap-5">
        <label class="switch">
          <input v-model="form.password_request_notification" type="checkbox" />
          <span class="slider round"></span>
        </label>
        <p>Notify admins when user requests a forget password email</p>
      </div>

      <div class=" flex items-center gap-5 mt-5">
        <label class="switch">
          <input v-model="form.password_change_notification" type="checkbox" />
          <span class="slider round"></span>
        </label>
        <p>Notify admins when user change passwords in settings</p>
      </div>
    </div>
    <Divider class=" mb-10" />
  </main>
</template>

<script setup>
import { ref, defineEmits, watch, onMounted } from "vue"
import {useForm} from "@inertiajs/vue3"
import { useToast } from "primevue/usetoast";

const toggle1 = ref(true)
const toggle2 = ref(true)
const toggle3 = ref(true)
const toggle4 = ref(true)
const toggle5 = ref(true)
const emit = defineEmits(['security'])
const toast = useToast()
const prevent_password_reusing = ref(false);
const goBack = () => {
  //router.go(-1);
  emit('security')
};
const form = useForm({
  'number_of_characters': '',
  'uppercase': '',
  'lowercase': '',
  'special_character': '',
  'number': '',
  'password_request_notification': false,
  'password_change_notification': false,
  'prevent_password_reusing': 0,
})
const range = ref(8)

const passwordRequirement = [
  {
    label: "8 characters"
  },
  {
    label: "A special character"
  },
  {
    label: "An uppercase letter"
  },
  {
    label: "A number"
  },
  {
    label: "An lowercase letter"
  },
]

const characterToggle = (event)=>{
  console.log(toggle1.value)
  if(toggle1.value == false){
    range.value = 0;
  }
}

const getPasswordPolicies = ()=>{
  axios.get('/settings/get-password-policies').then((response)=>{
    let data = response.data;
    form.number_of_characters = data.number_of_characters;
    form.uppercase = data.uppercase;
    form.lowercase = data.lowercase;
    form.number = data.number;
    form.password_request_notification = data.password_request_notification ==1? true: false;
    form.password_change_notification = data.password_change_notification ==1? true: false
    form.prevent_password_reusing = data.prevent_password_reusing
    prevent_password_reusing.value = data.prevent_password_reusing > 0 ? true: false;
    range.value = data.number_of_characters
    toggle2.value = data.special_character == 1? true: false
    toggle3.value = data.uppercase == 1? true: false
    toggle4.value = data.number == 1? true: false
    toggle5.value = data.lowercase == 1? true: false
  })
}

const preventPasswordChange = ()=>{
  if(form.prevent_password_reusing == 0){
    prevent_password_reusing.value = false;
  }else{
    prevent_password_reusing.value = true;
  }
}

const save = ()=>{
  form.number_of_characters = range.value
  form.special_character = toggle2.value
  form.uppercase = toggle3.value
  form.number = toggle4.value
  form.lowercase = toggle5.value;

  form.post('/settings/password-policies', {onSuccess:page=>{
    toast.add({
                    severity: "success",
                    summary: "Success",
                    detail: "You have updated password policies.",
                    life: 3000,
                });

        getPasswordPolicies()
  }})
  
}

watch(range, (newVal)=>{
  if(newVal == 0){
    toggle1.value = false
  }
  if(newVal > 0){
    toggle1.value = true;
  }
})

onMounted(()=>{
  getPasswordPolicies()
})
</script>

<style>
.switch {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 26px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 20px;
  width: 20px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked+.slider {
  background-color: #2196F3;
}

input:focus+.slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked+.slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
</style>
