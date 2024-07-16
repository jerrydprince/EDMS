<style>
.search_div {
  margin-top: 5px;
  padding: 5px 0;
  border-radius: 8px;
  border: 1px solid var(--Gray-100, #F2F4F7);
  background: var(--White, #FFF);
  box-shadow: 0px 4px 6px -2px rgba(16, 24, 40, 0.03), 0px 12px 16px -4px rgba(16, 24, 40, 0.08);
}

.search_div li {
  padding: 4px 28px;
}

.search_div li:hover {
  background: #e2e2e2;
}

   /* Custom scrollbar styles */
   .custom-scrollbar {
  scrollbar-width: thin; /* Firefox */
  scrollbar-color: #a0aec0 #edf2f7; /* Firefox */
}

.custom-scrollbar::-webkit-scrollbar {
  width: 4px; /* Set the width of the scrollbar */
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #E9E9E9; /* Set the color of the thumb */
}

.custom-scrollbar::-webkit-scrollbar-track {
  background-color: #fff; /* Set the color of the track */
}

.left-line::before {
    content: '';
    width: 30px;
    display: flex;
    border-top: 1px solid #D0D5DD;
    position: relative;
    top: 12px;
}

.custom-card {
    /* border: 1px solid gray; */
    border-radius: 5px;
    max-height: 21vh;
    position: absolute;
    z-index: 999;
    background: #fff;
    top: 81%;
    width: 92%;
    overflow-y: auto;
    /* box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px; */
    /* box-shadow: rgba(0, 0, 0, 0.09) 0px 2px 1px, rgba(0, 0, 0, 0.09) 0px 4px 2px, rgba(0, 0, 0, 0.09) 0px 8px 4px, rgba(0, 0, 0, 0.09) 0px 16px 8px, rgba(0, 0, 0, 0.09) 0px 32px 16px; */
    box-shadow: rgba(0, 0, 0, 0.2) 0px 12px 28px 0px, rgba(0, 0, 0, 0.1) 0px 2px 4px 0px, rgba(255, 255, 255, 0.05) 0px 0px 0px 1px inset;
}

.custom-card-inner-div:hover{
  background-color: #F2F4F7;
}

.line-through{
  text-decoration: line-through;
}

</style>

<template>
  <form @submit.prevent="submitForm('Unit')">
    <Dialog v-model:visible="visible" v-if="props.step === 1" modal header="Header" :pt="{
      content: { class: 'px-0 bg-white pb-6' },
    }" :style="{ width: '35rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
      <template #header>
        <div class="flex items-center gap-3 -mb-6">
          <div class="rounded-full py-4 px-4 border border-border_primary border-solid">
            <Icon icon="mdi:company" width="25" />
          </div>
          <div>
            <p class="mb-1 font-medium text-lg">Create Department</p>
            <p class="text-gray-500 text-xs">Define Department Attributes</p>
          </div>
        </div>
      </template>
      <section class="-mb-6">
        <Divider class="" />
        <div class="px-6">
          <div class="mb-5 mt-8">
            <p class="text-sm lg:text-base">Department Title *</p>
            <InputText id="department_title" class="mt-1 w-full" v-model="departmentForm.title"
              aria-describedby="username-help" placeholder="Enter Department Name" required />
          </div>
          <div class="mb-5">
            <p class="text-sm lg:text-base">Department Description (Optional)</p>
            <Textarea v-model="departmentForm.description" rows="3" cols="30" class="mt-1 w-full"
              placeholder="Enter a description..." />
          </div>

          <!-- display searched result starts here -->
          <div v-if="departmentHead.length !==0"  class=" custom-card py-4">
          <div class="py-2 flex items-center justify-between custom-card-inner-div px-6" v-for="(head, index) in departmentHead" @click="setSelectedDeptHead(head,index)"
          :class="{'cursor-pointer': head.is_department_head !== 1}">
            <div class="flex items-center gap-3">
              <Userimageholder />
              <div>
                <p :class="{'line-through': head.is_department_head === 1}" class="text-base lg:text-lg font-medium">{{head.first_name+" "+head.last_name}}</p>
                <p :class="{'line-through': head.is_department_head === 1}" class="-mt-1 text-gray-700 font-light text-sm lg:text-base">
                  {{head.email}}
                </p>
              </div>
            </div>

          </div>
         </div>
         <!-- display searched result ends here -->

          <div class="mb-6">
            <p class="text-sm lg:text-base">Set Department Head</p>
            <span class="text-gray-400 relative">
              <Icon icon="iconamoon:search-light" width="20"
                class="pi pi-search absolute top-2/4 -mt-[10.2px] left-3 text-surface-400 dark:text-surface-600" />
              <InputText id="department_head" class="mt-1 w-full pl-10"   v-model="departmentForm.head"
                aria-describedby="username-help" placeholder="Add by name or email"
                @keyup="Search($event.target.value)" />
            </span>
            <div class="search_div" v-if="is_searching">
              <ul>
                <li v-for="(ind, val) in searchedData" :key="ind">{{ val.name }}</li>
              </ul>
            </div>
          </div>
          
          <!-- USer Image is displayed here -->
         <div v-if="selectedHead.length != 0" class=" overflow-auto max-h-20">
          <div class="mb-6 flex items-center justify-between" v-for="(head, index) in selectedHead">
            <div class="flex items-center gap-3">
              <Userimageholder />
              <div>
                <p class="text-base lg:text-lg font-medium">{{head.first_name+" "+head.last_name}}</p>
                <p class="-mt-1 text-gray-700 font-light text-sm lg:text-base">
                  {{head.email}}
                </p>
              </div>
            </div>
            <Icon icon="prime:times" width="35" @click="removeSelectedHead(head, index)" />
          </div>
         </div>
        </div>
        <Divider class="" />
      </section>
      <template #footer>
        <Button class="mr-4" :pt="{
          root: {
            class: 'py-2 px-8 border-gray-200 border border-solid rounded-lg',
          },
        }" @click="closeModal" label="Cancel" severity="secondary" outlined />
        <Button label="Save" class="" :pt="{
          root: {
            class:
              'py-2 bg-primary px-10 border-0 text-white font-light hover:bg-primary/90 rounded-lg',
          },
        }" @click="submitForm('Department')" />
      </template>
    </Dialog>
    <Dialog v-model:visible="visible" v-else-if="props.step === 2" modal header="Header" :pt="{
      content: { class: 'px-0 bg-white pb-6' },
    }" :style="{ width: '35rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
      <template #header>
        <div class="flex items-center gap-3 -mb-6">
          <div class="rounded-full py-4 px-4 border border-border_primary border-solid">
            <Icon icon="ph:arrows-split-fill" width="20" :verticalFlip="true" />
          </div>
          <div>
            <p class="mb-1 font-medium text-lg">Create Division</p>
            <p class="text-gray-500 text-xs">Define Division Attributes</p>
          </div>
        </div>
      </template>
      <section class="-mb-6">
        <Divider class="" />
        <div class="px-6">
          <div class="mb-5 mt-8">
            <p class="text-sm lg:text-base">Division Title *</p>
            <InputText id="division_title" class="mt-1 w-full" v-model="divisionForm.title"
              aria-describedby="username-help" placeholder="Enter Division Name" />
          </div>
          <div class="mb-5">
            <p class="text-sm lg:text-base">Division Description (Optional)</p>
            <Textarea v-model="divisionForm.description" rows="5" cols="30" class="mt-1 w-full"
              placeholder="Enter a description..." />
          </div>
          <div class="mb-6">
            <p class="text-sm lg:text-base">Set Division Head</p>
            <span class="text-gray-400 relative">
              <Icon icon="iconamoon:search-light" width="20"
                class="pi pi-search absolute top-2/4 -mt-[10.2px] left-3 text-surface-400 dark:text-surface-600" />
              <InputText id="division_head" class="mt-1 w-full pl-10" v-model="divisionForm.head"
                aria-describedby="username-help" placeholder="Add by name or email" />
            </span>
          </div>
          <!-- USer Image is displayed here -->
          <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <Userimageholder />
              <div>
                <p class="text-base lg:text-lg font-medium">Dave Miye</p>
                <p class="-mt-1 text-gray-700 font-light text-sm lg:text-base">
                  davemiye@ansel.com
                </p>
              </div>
            </div>
            <Icon icon="prime:times" width="35" />
          </div>
        </div>
        <Divider class="" />
      </section>
      <template #footer>
        <Button class="mr-4" :pt="{
          root: {
            class: 'py-2 px-8 border-gray-200 border border-solid rounded-lg',
          },
        }" @click="closeModal" label="Cancel" severity="secondary" outlined />
        <Button label="Next" class="" :pt="{
          root: {
            class:
              'py-2 bg-primary px-10 border-0 text-white font-light hover:bg-primary/90 rounded-lg',
          },
        }" @click="submitForm('Division')" />
      </template>
    </Dialog>
    <Dialog v-model:visible="visible" v-else-if="(props.step = 3)" modal header="Header" :pt="{
      content: { class: 'px-0 bg-white pb-6' },
    }" :style="{ width: '35rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
      <template #header>
        <div class="flex items-center gap-3 -mb-6">
          <div class="rounded-full py-4 px-4 border border-border_primary border-solid">
            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 20 20" fill="none">
              <path
                d="M2.725 5.80008L10 10.0084L17.275 5.80008M10 18.4001V10.0001M17.5 13.3334V6.66675C17.4997 6.37448 17.4225 6.08742 17.2763 5.83438C17.13 5.58134 16.9198 5.37122 16.6667 5.22508L10.8333 1.89175C10.58 1.74547 10.2926 1.66846 10 1.66846C9.70744 1.66846 9.42003 1.74547 9.16667 1.89175L3.33333 5.22508C3.08022 5.37122 2.86998 5.58134 2.72372 5.83438C2.57745 6.08742 2.5003 6.37448 2.5 6.66675V13.3334C2.5003 13.6257 2.57745 13.9127 2.72372 14.1658C2.86998 14.4188 3.08022 14.6289 3.33333 14.7751L9.16667 18.1084C9.42003 18.2547 9.70744 18.3317 10 18.3317C10.2926 18.3317 10.58 18.2547 10.8333 18.1084L16.6667 14.7751C16.9198 14.6289 17.13 14.4188 17.2763 14.1658C17.4225 13.9127 17.4997 13.6257 17.5 13.3334Z"
                stroke="#101828" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </div>
          <div>
            <p class="mb-1 font-medium text-lg">Create Unit</p>
            <p class="text-gray-500 text-xs">Define Unit Attributes</p>
          </div>
        </div>
      </template>
      <section class="-mb-6">
        <Divider class="" />
        <div class="px-6">
          <div class="mb-5 mt-8">
            <p class="text-sm lg:text-base">Unit Title *</p>
            <InputText id="unit_title" class="mt-1 w-full" v-model="unitForm.title" aria-describedby="username-help"
              placeholder="Enter Unit Name" />
          </div>
          <div class="mb-5">
            <p class="text-sm lg:text-base">Unit Description (Optional)</p>
            <Textarea v-model="unitForm.description" rows="5" cols="30" class="mt-1 w-full"
              placeholder="Enter a description..." />
          </div>
          <div class="mb-6">
            <p class="text-sm lg:text-base">Set Unit Head</p>
            <span class="text-gray-400 relative">
              <Icon icon="iconamoon:search-light" width="20"
                class="pi pi-search absolute top-2/4 -mt-[10.2px] left-3 text-surface-400 dark:text-surface-600" />
              <InputText id="unit_head" class="mt-1 w-full pl-10" v-model="unitForm.head" aria-describedby="username-help"
                placeholder="Add by name or email" />
            </span>
          </div>
          <!-- USer Image is displayed here -->
          <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <Userimageholder />
              <div>
                <p class="text-base lg:text-lg font-medium">Dave Miye</p>
                <p class="-mt-1 text-gray-700 font-light text-sm lg:text-base">
                  davemiye@ansel.com
                </p>
              </div>
            </div>
            <Icon icon="prime:times" width="35" />
          </div>
        </div>
        <Divider class="" />
      </section>
      <template #footer>
        <Button class="mr-4" :pt="{
          root: {
            class: 'py-2 px-8 border-gray-200 border border-solid rounded-lg',
          },
        }" @click="closeModal" label="Cancel" severity="secondary" outlined />
        <Button label="Save" class="" :pt="{
          root: {
            class:
              'py-2 bg-primary px-10 border-0 text-white font-light hover:bg-primary/90 rounded-lg',
          },
        }" type="submit" @click="submitForm('Unit')" />
      </template>
    </Dialog>
  </form>
</template>

<script setup>
import { ref, defineEmits, defineProps, computed, reactive, watch } from "vue";
import { useToast } from 'primevue/usetoast';
import Userimageholder from "../../assets/Userimageholder.vue";
import { router } from '@inertiajs/vue3'
import { debounce } from 'lodash';

let is_searching = ref(false);
let testData = [
  { name: "Emeka" },
  { name: "Dave" },
  { name: "James" },
  { name: "Ansel" },
  { name: "Jerry" }
]
let search_data = null;

const toast = useToast();
const props = defineProps(["modelValue", "step"]);
const emit = defineEmits(["update:modelValue", "update:step"]);
const departmentHead = ref([])

//forms
const departmentForm = reactive({ title: '', description: '', head: '' })
const divisionForm = reactive({ title: '', description: '', head: '' })
const unitForm = reactive({ title: '', description: '', head: '' })
const selectedHead = ref([])

const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  },
});


const searchedData = computed(() => {
  let found = testData.filter((val) => {
    return val.name.toLowerCase() === search_data.toLowerCase()
  })
  return found
})

const closeModal = () => {
  emit("update:step", 1);
  emit("update:modelValue", false);
};

/*
const submitForm = (modal) => {

  if (props.step === 3) {
    //check if the unit form is empty
    if (modal == 'Unit') {
      if (unitForm.title == '') {
        document.getElementById('unit_title').focus();
        return false;
      }
      // else if(unitForm.head == ''){
      //   document.getElementById('unit_head').focus();
      //   return false
      // }
    }

    let data = {
      department: departmentForm,
      division: divisionForm,
      unit: unitForm
    }
    router.post('/dashboard/create-department-division-unit', data, {
      onSuccess: function () {
        emit("update:modelValue", false);
        emit("update:step", 1)

        departmentForm.title = '';
        departmentForm.description = ''
        departmentForm.head = ''
        divisionForm.title = '';
        divisionForm.description = '';
        divisionForm.head = ''
        unitForm.title = ''
        unitForm.description = '';
        unitForm.head = ''

        toast.add({
          severity: "success",
          summary: `Department Created`,
          detail: `You've successfully created a new Department.`,
          life: 3000,
        });
      }
    })

  } else {
    //check if form element is empty
    if (modal == 'Department') {
      if (departmentForm.title == '') {
        document.getElementById('department_title').focus();
        return false;
      } 
      // else if (departmentForm.head == '') {
      //   document.getElementById('department_head').focus();
      //   return false
      // }
    }
    if (modal == 'Division') {
      if (divisionForm.title == '') {
        document.getElementById('division_title').focus();
        return false;
      } 
      // else if (divisionForm.head == '') {
      //   document.getElementById('division_head').focus();
      //   return false
      // }
    }
    if (modal == 'Unit') {
      if (unitForm.title == '') {
        document.getElementById('unit_title').focus();
        return false;
      } else if (unitForm.head == '') {
        document.getElementById('unit_head').focus();
        return false
      }
    }
    //setTimeout(() => emit("update:step", props.step + 1), 1000);
    emit("update:step", props.step + 1)
  }
};
*/

const submitForm = (modal) => {

if (props.step === 1) {
  //check if the unit form is empty
    if (departmentForm.title == '') {
      document.getElementById('unit_title').focus();
      return false;
    }
   

  let data = {
    department: departmentForm,
    division: divisionForm,
    unit: unitForm,
    heads: selectedHead.value
  }
  router.post('/dashboard/create-department-division-unit', data, {
    onSuccess: function () {
      emit("update:modelValue", false);
      emit("update:step", 1)

      departmentForm.title = '';
      departmentForm.description = ''
      departmentForm.head = ''
      selectedHead.value.length = 0;
      departmentHead.value.length=0;
      // divisionForm.title = '';
      // divisionForm.description = '';
      // divisionForm.head = ''
      // unitForm.title = ''
      // unitForm.description = '';
      // unitForm.head = ''

      toast.add({
        severity: "success",
        summary: `Department Created`,
        detail: `You've successfully created a new Department.`,
        life: 3000,
      });
    }
  })

} 

};

const Search = (search) => {
  search_data = search
}

const setSelectedDeptHead = (val,index)=> {
  if(val.is_department_head === 1){
    return false;
  }
  departmentHead.value.splice(index,1);
  selectedHead.value.push(val)
}

const removeSelectedHead = (head, index)=>{
 if(departmentHead.value.length !== 0){
  departmentHead.value.push(head);
 }
  selectedHead.value.splice(index,1)

}

watch(() => departmentForm.head, (val)=>{
  
  debouncedSearch()
})

const debouncedSearch = debounce(() => {
  if(departmentForm.head !==''){
    axios.get('/dashboard/search-department-head',{params:{name:departmentForm.head, 'type':'department'}}).then((response)=>{
      departmentHead.value = response.data
    })
  }else{
    departmentHead.value.length = 0
  }
}, 300);

</script>
