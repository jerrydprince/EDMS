<template>
  <main>
    <div class="lg:px-7 px-5 flex items-center justify-between pt-5">
      <div class="flex items-center gap-5">
        <Link href="/dashboard/manage-users" class="border rounded-md p-3 py-4 cursor-pointer">
          <Icon icon="formkit:arrowleft" width="30" />
        </Link>
        <div>
          <p class="text-3xl text-[#1D2939]">Add New User</p>
          <p class="text-gray-500 text-sm ">Setup User Accounts, Permissions and access levels</p>
        </div>
      </div>
      <div class="flex items-center gap-4">
        <button class="flex gap-2 items-center text-[#3575DD] rounded-lg py-2 px-4" @click="cancelBtn">
          <p>Cancel</p>
        </button>

        <button
          class="flex gap-2 items-center text-white bg-[#2F68C5] rounded-lg py-2 px-4 hover:bg-blue-400 disabled:bg-blue-300"
          :disabled="!isFormReady">

          <p>{{ form.id? 'Approve Account': 'Create Account' }}</p>
        </button>

      </div>
    </div>
    <Divider class=" " />

    <section class=" px-7 ">
      <div class=" flex items-center justify-between">
        <p class="  text-xl font-medium">Choose Access Level</p>
        <button
          class="flex gap-2 items-center text-[#344054] border border-solid font-semibold border-gray-300  rounded-lg py-2 px-4">
          <Icon icon="mynaui:edit" width="20" />
          <p>Edit Acces Level</p>
        </button>
      </div>
      <div class="  grid grid-cols-4 gap-3 mt-5">
        <div v-for=" (item, index) in props.roles" :key="index">
          <div :style="{ borderColor: form.role.includes(item.name) ? '#3B82F6' : '#EAECF0' }"
            class="flex items-center border p-3 gap-3 rounded-md h-full "
            :class="{ 'bg-[#F8FBF9]': form.role.includes(item.name) }">
            <Checkbox v-model="form.role" :value="item.name" />
            <div>
              <p class=" text-lg font-medium">{{ capitalize(item.name) }}</p>
              <p class=" text-xs">{{ item.description }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>
    <Divider class=" " />



    <section class=" px-7 my-5 text-[#344054] pb-10">
      <h3 class="text-xl font-medium text-black">Configure User Information</h3>

      <div class=" flex items-center justify-between">
        <div class=" w-[45%] mt-5">
          <label for="First Name" class=" font-medium">First Name</label>
          <input type="text" v-model="form.first_name" placeholder="John" required
            class=" w-full outline-none border-gray-300 border p-2 mt-2 rounded-md">
          <span class="text-red-400 text-sm my-2" v-if="form.errors.first_name">{{ form.errors.first_name }}</span>
        </div>
        <div class=" w-[45%] mt-5">
          <label for="Last Name" class=" font-medium">Last Name</label>
          <input type="text" v-model="form.last_name" placeholder="Doe" required
            class=" w-full outline-none border-gray-300 border p-2 mt-2 rounded-md">
          <span class="text-red-400 text-sm my-2" v-if="form.errors.last_name">{{ form.errors.last_name }}</span>
        </div>
      </div>

      <div class="flex items-center justify-between">
        <div class=" w-[45%] mt-5">
          <div>
            <label for="gender" class="font-medium">Gender</label>
            <select name="gender" v-model="form.gender" id="gender"
              class=" border border-gray-200 rounded-lg py-2 px-4 w-full focus:border-blue-500 focus:ring-blue-500">
              <option value="" class=" text-gray-400">Select from dropdown</option>
              <option value="male">Male</option>
              <option value="female">Female</option>
              <option value="none">Prefer not to say</option>
            </select>
            <span class="text-red-400 text-sm my-2" v-if="form.errors.gender">{{ form.errors.gender }}</span>
          </div>
        </div>
        <div class=" w-[45%] mt-5">
          <div>
            <label for="grade_level" class="font-medium">Grade Level</label>
            <select name="grade_level" v-model="form.grade_level" id="grade_level"
              class=" border border-gray-200 rounded-lg py-2 px-4 w-full focus:border-blue-500 focus:ring-blue-500">
              <option value="">Select from dropdown</option>
              <option value="0">Grade Level 0 (Data Entry)</option>
              <option value="1">Grade Level 1</option>
              <option value="2">Grade Level 2</option>
              <option value="3">Grade Level 3</option>
              <option value="4">Grade Level 4</option>
              <option value="5">Grade Level 5</option>
              <option value="6">Grade Level 6</option>
              <option value="7">Grade Level 7</option>
              <option value="8">Grade Level 8</option>
              <option value="9">Grade Level 9</option>
              <option value="10">Grade Level 10</option>
              <option value="11">Grade Level 11</option>
              <option value="12">Grade Level 12</option>
              <option value="13">Grade Level 13</option>
              <option value="14">Grade Level 14</option>
              <option value="15">Grade Level 15</option>
            </select>
          </div>
          <span class="text-red-400 text-sm my-2" v-if="form.errors.grade_level">{{ form.errors.grade_level }}</span>
        </div>
      </div>

      <div class=" flex items-center justify-between">
        <div class=" w-[45%] mt-5">
          <label for="Email" class=" font-medium">Email</label>
          <input type="email" ref="email" @blur="checkMailExist" v-model="form.email" placeholder="John@mail.com" required
            class=" w-full outline-none border-gray-300 border p-2 mt-2 rounded-md">
          <span class="text-red-400 text-sm my-2" v-if="email_msg">{{ email_msg }}</span>
          <span class="text-red-400 text-sm my-2" v-if="form.errors.email">{{ form.errors.email }}</span>
        </div>

        <div class=" w-[45%] mt-5">
          <label for="phone" class=" font-medium">Phone</label>
          <!-- <input type="number" ref="phone" v-model="form.phone" placeholder="phone number" required
            class=" w-full outline-none border-gray-300 border p-2 mt-2 rounded-md" @blur="checkPhoneExist"> -->
            <input type="number" ref="phone" v-model="form.phone" placeholder="phone number" required
            class=" w-full outline-none border-gray-300 border p-2 mt-2 rounded-md">
          <span class="text-red-400 text-sm my-2" v-if="phone_msg != ''">{{ phone_msg }}</span>
          <span class="text-red-400 text-sm my-2" v-if="form.errors.phone">{{ form.errors.phone }}</span>
        </div>
      </div>

      <div class="flex items-start justify-between mt-5">
        <div class=" w-[47%]">
          <div v-if="form.id === undefined">
            <h1 class=" font-medium">Profile Image</h1>
            <div class=" flex items-start gap-10 mt-3 ">
              <img :src="previewUrl" alt="" class="w-[64px] h-[64px] rounded-full" />
              <div
                class=" flex flex-col items-center text-center justify-center border-gray-300 border p-3 rounded-lg relative h-full w-full ">
                <div>

                  <i class="pi pi-cloud-upload py-3 px-4 rounded-full text-4xl bg-[#F2F4F7]"></i>

                  <h1 class="  mt-2"><span class=" text-[#2F68C5] font-medium">Click to upload </span> or drag and drop
                  </h1>
                  <p>SVG, PNG, JPG or GIF (max. 800x400px)</p>
                </div>
                <input type="file" class="opacity-0 absolute w-full h-full cursor-pointer" @change="handleFileChange" />
              </div>
            </div>
          </div>
        </div>

        <div class=" w-[45%]">
          <div>
            <label for="Department" class=" font-medium">Department</label>
            <select class=" w-full outline-none border-gray-300 border p-2 mt-2 rounded-md" @change="selectedDepartment"
              v-model="form.department">
              <option value="">Select a department</option>
              <option v-for="department in props.departments" :value="department.id">{{ department.title }}</option>
            </select>
            <span class="text-red-400 text-sm my-2" v-if="form.errors.department">{{ form.errors.department }}</span>
          </div>

          <div class="mt-5 ">
            <label for="Division" class=" font-medium">Division</label>
            <select v-model="form.division" @change="selectedDivision"
              class=" w-full outline-none border-gray-300 border p-2 mt-2 rounded-md">
              <option value="">Select a division</option>
              <option v-for="(item, index) in getDivision" :key="index" :value="item.id">{{ item.title }}</option>
            </select>
          </div>

          <div class=" mt-5">
            <label for="Unit" class=" font-medium">Unit</label>
            <select v-model="form.unit" name="" id="" class=" w-full outline-none border-gray-300 border p-2 mt-2 rounded-md">
              <option value="">Select from dropdown</option>
              <option v-for="(item, index) in getUnit" :value="item.id" :key="index">{{ item.title }}</option>
            </select>
          </div>
        </div>
      </div>
    </section>

    <Divider class=" " />

    <div class="lg:px-7 px-5 flex items-center justify-between pt-2 pb-4">
      <div class="flex items-center gap-5">

      </div>
      <div class="flex items-center gap-4">
        <button class="flex gap-2 items-center text-[#3575DD] rounded-lg py-2 px-4">
          <p>Cancel</p>
        </button>

        <button @click="submitForm"
          class="flex gap-2 items-center text-white bg-[#2F68C5] rounded-lg py-2 px-4 hover:bg-blue-400 disabled:bg-blue-300"
          :disabled="!isFormReady">

          <p>{{ form.id? 'Approve Account': 'Create Account' }}</p>
        </button>

      </div>
    </div>



  </main>
</template>

<script setup>
import { ref, defineProps, computed, watch, onMounted } from "vue";
import { useForm } from "@inertiajs/vue3";
import { useToast } from "primevue/usetoast";
import {Link} from "@inertiajs/vue3";

const props = defineProps(['roles', 'departments','user']);
const department = ref([]);
const division = ref([]);
const previewUrl = ref('');
const isEmailExist = ref(false);
const email_msg = ref(null)
const isEmailValidated = ref(false)
const isPhoneExist = ref(false);
const phone_msg = ref('')
const phone = ref(null);
const email = ref(null)
const toast = useToast();
const form = useForm(
  {
    first_name: '',
    last_name: '',
    email: '',
    department: '',
    division: '',
    unit: '',
    image: '',
    role: [],
    file: [],
    grade_level: '',
    gender: '',
    phone: '',
    id:'',
    is_approved: 0
  }

)


const handleFileChange = (event) => {
  const file = event.target.files[0];
  form.file = file
  if (file) {
    const reader = new FileReader();
    reader.onload = (e) => {
      previewUrl.value = e.target.result;
    };
    reader.readAsDataURL(file);
  } else {
    previewUrl.value = '';
  }
};

const selectedDepartment = () => {
  let dept = props.departments.filter((val) => {
    return val.id == form.department
  });
  department.value = dept;
}

const selectedDivision = () => {

  let div = getDivision.value.filter((val) => {
    return val.id === form.division
  });

  division.value = div

}

const capitalize = (value) => {
  if (!value) return '';
  return value.replace(/\b\w/g, (char) => char.toUpperCase());
}

const validateEmail = (email) => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  console.log(emailRegex.test(email))
  if (emailRegex.test(email)) {
    //email_msg.value = "Invalid email"
    return true
  } else {
    // email_msg.value = ""
    return false;
  }
}

//check if email exist
const checkMailExist = () => {
  axios.get('/auth-email-exist', { params: { email: form.email } }).then((response) => {

    if (response.data.error) {
      console.log(response.data.error_msg)
      email_msg.value = response.data.error_msg
      isEmailExist.value = true;
    }
    if (response.data.success) {
      email_msg.value = null;
      isEmailExist.value = false;
      form.error = ''
    }
  })
}

const validatePhoneNumber = (phone) => {
  const phoneRegex = /^\d{10}$/;
  if (phoneRegex.test(phone)) {
    return true
  } else {
    return false;
  }
};

//check if phone number exist
const checkPhoneExist = () => {
  axios.get('/auth-phone-exist?user_id', { params: { phone: form.phone } }).then((response) => {
    if (response.data.error) {
      phone_msg.value = response.data.error_msg
      isPhoneExist.value = true;
    }
    if (response.data.success) {
      phone_msg.value = null;
      isPhoneExist.value = false;
      form.phone = ''
    }
  })
}


const isFormReady = computed(() => {
  if (form.first_name == '') {
    return false
  } else
    if (form.last_name == '') {
      return false
    } else
      if (form.email == '') {
        return false
      }
      else
        if (form.department == '') {
          return false
        } else
          if (form.grade_level == '') {
            return false
          } else
            if (form.gender == '') {
              return false
            } else
              if (form.role == '') {
                return false
              } else {
                return true;
              }

})

const getDivision = computed(() => {
  return department.value[0]?.division
})

const getUnit = computed(() => {
  return division.value[0]?.unit;
})

const cancelBtn = ()=>{
    form.first_name= '',
    form.last_name= '',
    form.email= '',
    form.department= '',
    form.division= '',
    form.unit= '',
    form.image= '',
    form.role= [],
    form.file= [],
    form.grade_level= '',
    form.gender= '',
    form.phone= '',
    form.id='',
    form.is_approved= 0
}

const submitForm = () => {
  if (!validateEmail(form.email)) {
    validateEmail(form.email)
    email.value.focus();
    return false;
  }
  if (!validatePhoneNumber(form.phone)) {
    phone.value.focus();
    return false;
  }

  if(form.id == ''){
    if (isEmailExist.value) {
      email.value.focus();
      return false
    }

    if (isPhoneExist.value) {
      phone.value.focus();
      return false;
    }
  }

  form.post('/dashboard/user-registration', {
    onSuccess: (response) => {
      previewUrl.value = ''
      let msg = form.id? 'approved': 'added';
      toast.add({
        severity: "success",
        summary: "User "+msg,
        detail: "You've successfully "+msg+" a new user.",
        life: 3000,
      });

      if(!form.id){
        form.reset()
      }
    }
  })
}

watch(() => form.email, () => {
  validateEmail(form.email)
})

onMounted(() => {
    if(props.user){
      form.first_name = props.user.first_name;
      form.last_name = props.user.last_name;
      form.email = props.user.email;
      form.grade_level = props.user.grade_level;
      form.gender = props.user.gender;
      form.phone = props.user.phone;
      form.id = props.user.id;
      form.is_approved = 1;
    }
})

</script>

<style></style>