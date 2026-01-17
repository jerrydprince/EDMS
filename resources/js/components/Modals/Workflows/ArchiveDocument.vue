<template>
	<Dialog
		v-model:visible="visible"
        :closable="false"
		modal
		:pt="{
      content: { class: 'px-0 bg-white pb-6' },
    }"
		:style="{ width: '40rem', padding: '10px 0px' }"
		:breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
        class=" .custom-dialog"
	>
	<template   #header>
         <div class=" flex items-center justify-between w-full">
            <h1 class=" text-3xl font-semibold text-[#1D2939] mt-5">Archive Documents</h1>
            <div class=" p-2 rounded-full bg-[#F2F4F7]  font-semibold cursor-pointer"  @click="closeModal">
                <Icon icon="clarity:times-line"  width="25"/>
            </div>
         </div>
    </template>
    <section class=" px-6">
      <div class=" flex items-center gap-3">
      <div class=" p-2 bg-gray-100 rounded-full w-fit">
      <Icon
                icon="mynaui:users"
                width="25"
              />
            </div>

            <div  class=" flex  items-center gap-4 ">
     <div class=" flex items-center gap-2 bg-[#f8f8f9] p-2 rounded-3xl border border-solid  border-gray-300" v-for="(item,index) in assignment" :key="index">
       <img :src="oneIcon" alt="" class=" w-5">
        <p class=" text-xs w-fit">{{ item.name }}</p>
        <Icon icon="clarity:times-line"  class=" cursor-pointer"/>
     </div>
    </div>
    <div
                class="-ml-3 bg-[#E2ECFE] text-primary font-medium border-[2px] border-white py-1.5 px-2 rounded-full"
              >
                <p>+5</p>
              </div>
  </div>

  <div class=" flex items-center gap-3 mt-4">
    <div class=" p-2 bg-gray-100 w-fit rounded-full">
      <Icon icon="uil:calender"  width="25"/>
    </div>

    <div class=" ">
       <h1 class=" font-medium text-[#1D2939]">2023-05-15 - 2023-05-18</h1>
    </div>
  </div>

  <div class=" mt-3 flex items-center gap-3">
    <div class=" p-2 px-3 text-lg rounded-full bg-gray-100 w-fit">
      <i class="pi pi-file" width="25" ></i>
    </div>

    <div class="  flex items-center gap-2">
       <div  class=" border border-gray-300 p-2 px-5 font-medium text-[#1D2939] rounded-3xl"  v-for="(item,index)  in document"  :key="index">
          <p>{{ item.doc }}</p>
       </div>
    </div>
  </div>


    </section>
    
    <section>
      <div  class=" relative mt-4">
        <div  class=" flex  px-7 text-[#667085] gap-10 relative z-50 ">
       <h1 class=" p-3 border-b-[1px] cursor-pointer"  @click="activeButton=1"  :style="{ borderColor:  activeButton === 1 ? '#0D6E1A' : '#ffffff00', color: activeButton === 1 ? '#0D6E1A' : 'inherit' }">Description</h1>
       <div  class=" p-3 border-b-[1px]   flex items-center gap-2 cursor-pointer"  @click="activeButton = 2"  :style="{ borderColor:  activeButton === 2 ? '#0D6E1A' : '#ffffff00' , color: activeButton === 2 ? '#0D6E1A' : 'inherit'}">
       <h1 >Comments</h1>
       <span class=" text-xs text-white p-1.5 py-0.5 rounded-full bg-red-600">2</span>
      </div>
       <h1  class=" p-3 border-b-[1px]   cursor-pointer"  @click="activeButton = 3"  :style="{ borderColor:  activeButton === 3 ? '#0D6E1A' : '#ffffff00',  color: activeButton === 3 ? '#0D6E1A' : 'inherit' }">Task Actions</h1>
        </div>
        <div class=" absolute z-30 bottom-0 w-full h-[1px] bg-gray-300"></div>
      </div>


      <div  v-if=" activeButton === 1"  class=" text-base  mx-6 my-5 p-5 bg-[#FCFCFD] border-dashed flex flex-col items-center rounded-xl border-[3px] py-3">
     
  <p>Review and archive assigned documents promptly to ensure seamless workflow. Documents awaiting archiving in the Finance Department. Kindly treat as urgent and begin archiving immediately From management</p>
      </div>

      <div  v-if=" activeButton === 2">
      <div  class=" mt-4 mx-6 border border-gray-300 border-solid rounded-md"   >
        <Textarea
              v-model="message"
              autoResize
              rows="1"
              cols="30"
              placeholder="Start a new message"
              class="p-inputtextarea"
            />
         <div class=" p-3 gap-3  flex items-center w-full justify-end">
          <Icon
                icon="ic:round-link"
                width="25"
                class="cursor-pointer text-gray-500"
               
              />
              <button
              class="flex items-center gap-2 bg-primary text-white px-3 rounded-md py-1"
            >
              <p>Submit</p>
              
            </button>

         </div>
        </div>
         <div class=" my-3   mx-6 flex items-center gap-2">
          <img :src="oneIcon" alt="">
          <h1 class="  text-[#1D2939] font-medium">Dave Miye created this task</h1>
           
          
          <small class="opacity-70"> • 3h ago</small>
         </div>
         <div>
          <Divider  class=" -mt-0.5"/>
          <div class="  mx-6 -mt-1 flex items-center gap-2">
          <img :src="oneIcon" alt="">
          <h1 class="  text-[#1D2939] font-medium">Dave Miye created this task</h1>
          <small class="opacity-70" >• 3h ago</small>
         </div>
          <p class=" text-xs  px-6 mt-2 text-[#667085]">Task Received i would begin work immediately & review and archive assigned documents promptly to ensure seamless workflow. Documents awaiting archiving in the Finance Department. </p>
         </div>
      </div>

      <div   v-if="activeButton  === 3">
        <div class=" mx-6 mt-3 flex items-end  justify-between ">
          <div>
        <div v-for="(step, index) in TaskAction" :key="index">
            <div class="flex items-start gap-5">
              <div class="flex flex-col items-center">
                
                <Icon icon="akar-icons:circle-check-fill" width="25"  :class="{ 'text-[#039855]': index < 4, 'text-[#98A2B3]': index >= 4 }"  />
               
                <div class="w-[2px] my-1 h-10 bg-[#EBF3FE]" v-if="index < 4"></div>
              </div>
              <div class="text-[#475467]">
                <h1 class="font-medium text-lg">{{ step.progress }}:</h1>
                <div class=" flex items-center  text-sm gap-1" >
                  <p class="opacity-70">{{ step.to }}</p>
                  <p class=" opacity-100">•</p>
                <p class="opacity-70">{{ step.date }}</p>
                <p  class=" opacity-100">•</p>
                <p  class="opacity-70">{{ step.time }}</p>
              </div>
              </div>
            </div>
          </div>
        </div>
        <div class=" flex items-center gap-2">
        <button class=" text-[10px] w-[20px]"><p>Request Revision </p></button>
         <button class=" px-1 py-1 border rounded-md text-sm">Approve</button>
        </div>
        </div>
      </div>
    </section>


        <Divider class=" -mb-2"/>
		<template #footer>
			<div class=" flex justify-between items-end w-full">
                <div class=" flex flex-col items-start ">
                    <label for="" class=" font-semibold text-[#1D2939] mb-1">Status</label> 
                    <select name="" id="" class=" py-2 text-[#344054] font-semibold  px-2  outline-none border-[2px] rounded-md border-gray-300">
                      <option value="In Progress">In Progress</option>
                      <option value="Under Review">Under Review</option>
                      <option value="Completed">Completed</option>
                      <option value="Uncompleted">Uncompleted</option>
                    </select>
                </div>
                <div>
                <Button
            @click="closeModal"
				label="Edit Task"
				class=""
				:pt="{
          root: {
            class:
              'py-3  px-10 border border-gray-300 text-[#344054] font-light  rounded-lg',
          },
        }"
				
			/>
            <Button
            @click="submit"
				label="Save"
				class=" ml-3"
				:pt="{
          root: {
            class:
              'py-3  px-10 border border-gray-300 text-white bg-primary font-light  rounded-lg',
          },
        }"
				
			/>
        </div>
            </div>
		</template>
	</Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed ,ref} from "vue";
import { useToast } from "primevue/usetoast";
import oneIcon from "../../../../../public/images/members/1.svg";

const props = defineProps(["modelValue"]);
const emit = defineEmits(["update:modelValue"]);
const selectedIndex = ref(null)
const TaskAction = ref([
  {
    progress: "Task Assigned",
    to: "Dave Miye (You)",
    date: "2024-02-14",
    time: " 10:00 AM"
  },
  {
    progress: "Task Re-assigned to John Smith (Deputy director)",
    to: "Dave Miye (You)",
    date: "2024-02-14",
    time: " 10:00 AM"
  },
  {
    progress: "Added Comments & Feedback",
    to: "John smith",
    date: "2024-02-14",
    time: " 10:00 AM"
  },
  {
    progress: "Appended Signature",
    to: "Dave Miye (You)",
    date: "2024-02-14",
    time: " 10:00 AM"
  },
  {
    progress: "Awaiting Approval",
    to: "Dave Miye (You)",
    date: "2024-02-14",
    time: " 10:00 AM"
  }
])


const activeButton = ref(1)
const assignment = ref([
    {
        name: "Jenny Williams"
    },
    {
        name: "Dave Miye"
    }
])
const document = ref([
  {
    doc: "Invoice"
  },
  {
    doc: "Reciept"
  }
])
const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  },
});

const closeModal = () => {
  emit("update:modelValue", false);
  selectedIndex.value = null
};

const submit = () => {
  emit("update:modelValue", false);
    selectedIndex.value = null
//   toast.add({
//     severity: "success",
//     summary: '',
//     detail: "",
//     life: 3000,
//   });
};
</script>
 <style scoped>
  .custom-dialog .p-dialog-header {
    display: none; 
    width: 20%;
  }
  .p-inputtextarea {
    outline: none;
    box-shadow: none !important;
    border: none;
    resize: none;
    width: 100%
}
.p-inputtextarea:focus {
    border-color: #D0D5DD; /* Hide border */
}
</style>