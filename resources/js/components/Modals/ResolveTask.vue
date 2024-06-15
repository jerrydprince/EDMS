<template>
	<Dialog
		v-model:visible="visible"
        :closable="false"
		modal
		:pt="{
      content: { class: 'px-0 bg-white pb-6' },
    }"
		:style="{ width: '35rem', padding: '10px 0px' }"
		:breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
        class=" .custom-dialog"
	>
    <template   #header>
        <div class=" flex items-start justify-between  w-full">
        <div class="flex gap-3 items-center mt-2 ">
          <div class="p-2 border rounded-full">
            <Icon icon="charm:circle-tick" width="25" />
          </div>
          <h1 class="font-semibold text-xl text-[#1D2939]">
            Assigned Task
          </h1>
          <button class=" text-[#93370D] rounded-2xl bg-[#FEF0C7] p-1.5 flex items-center gap-1 ">
            <Icon icon="carbon:time" width="15" />
            <p class=" text-xs">pending</p>
            <Icon icon="flowbite:angle-down-outline" width="15" />
          </button>
        </div>
        <div class=" bg-gray-200 p-2 rounded-full cursor-pointer" @click="closeModal">
            <Icon icon="clarity:times-line" />
        </div>
    </div>
    </template>
    <Divider class=" -mt-1"/>

    <section class=" px-6">
       <div>
        <h1 class=" opacity-60 text-lg">Task Name</h1>
        <p class=" font-medium">{{ props.task.title }} </p>
       </div>

       <div class=" mt-5">
        <h1  class=" opacity-60 text-lg">Description</h1>
        <p class=" mt-2 text-base bg-gray-100 p-5 rounded-lg  border-dashed border-[#b3b3b34d] border-[2px]">{{props.task.description}}</p>
       </div>


       <div class=" mt-5">
        <h1  class=" opacity-60 text-lg">Timeline</h1>
        <div class=" flex items-center gap-3">
        <button class="">
            <div class=" p-2 bg-gray-50 rounded-full mt-2">
           <Icon icon="uil:calender" width="25" />
        </div>
        </button>

        <p class=" font-semibold">{{props.task.start}}</p> 
        <span> - </span>
        <p class=" font-semibold">{{props.task.end}}</p>
    </div>
       </div>
    </section>

    <template #footer>
        <div class=" flex flex-col w-full items-start">
        <h1 class=" opacity-70 text-lg">Document Types</h1>
        <div class=" flex gap-5 mt-3">
        <button class=" font-semibold border-[2px] px-5 py-1.5  rounded-3xl">{{props.task.document_type}}</button>
        <!-- <button   class=" font-semibold border-[2px] px-5 py-1.5  rounded-3xl">Receipts</button> -->
    </div>
    </div>
    </template>
	</Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed ,ref} from "vue";
import { useToast } from "primevue/usetoast";

const toast = useToast();
const props = defineProps(["modelValue", 'task']);
const emit = defineEmits(["update:modelValue"]);

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
};


</script>
 <style scoped>
  .custom-dialog .p-dialog-header {
    display: none; /* Hide the header */
  }
</style>