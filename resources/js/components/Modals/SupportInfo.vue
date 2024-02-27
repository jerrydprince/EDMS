<template>
	<Dialog
		v-model:visible="visible"
        :closable="false"
		modal
		:pt="{
      content: { class: 'px-0 bg-white pb-6' },
    }"
		:style="{ width: '30rem', padding: '10px 0px' }"
		:breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
        class=" .custom-dialog"
	>
	<template #header>
          <div class=" flex items-center gap-3">
            <div class=" p-3 border border-solid rounded-full">
             <Icon icon="simple-line-icons:earphones-alt" width="25" />
            </div>

            <div>
              <h1 class=" font-semibold text-2xl">Support Info</h1>
              <p class=" text-sm">Customize support information for the organization</p>
            </div>
          </div>
  </template>
  <Divider class=" -mt-2"/>

  <section class=" mx-6">
    <div class=" text-[#344054]">'
    <div>
     <label for="" class=" font-medium">Support Mail</label>
     <input type="text" class=" mt-1 w-full p-2 border outline-none rounded-md" placeholder="support@ansel.com" >
    </div>

    <div class=" mt-3">
     <label for="" class=" font-medium">Office Location</label>
     <input type="text" class=" mt-1 w-full p-2 border outline-none rounded-md" placeholder="" >
    </div>


    <div class=" mt-3">
     <label for="" class=" font-medium">Office Phone Number</label>
     <input type="text" class=" mt-1 w-full p-2 border outline-none rounded-md" placeholder="" >
    </div>
     


    <h1 class=" mt-5 mb-2">Office Hours</h1>
    <div class=" flex items-center w-full " >
      <select name="" id="" class="  w-full p-2 border outline-none rounded-md">
        <option v-for="day in days" :key="day" :value="day">{{ day }}</option>
      </select>

      <div>
         <p class=" px-5 py-0.5 mx-3 rounded-2xl border border-solid">To</p>
      </div>


      <select name="" id="" class="  w-full p-2 border outline-none rounded-md">
        <option v-for="day in days" :key="day" :value="day">{{ day }}</option>
      </select>
    </div>

    <div class=" flex items-center w-full mt-5" >
      <select name="" id="" class="  w-full p-2 border outline-none rounded-md">
        <option v-for="time in times" :key="time" :value="time">{{ time }}</option>
      </select>

      <div>
         <p class=" px-5 py-0.5 mx-3 rounded-2xl border border-solid">To</p>
      </div>


      <select name="" id="" class="  w-full p-2 border outline-none rounded-md">
        <option v-for="time in times" :key="time" :value="time">{{ time }}</option>
      </select>
    </div>
    </div>
  </section>


		<template #footer>
			<Button
				class="mr-4"
				:pt="{
          root: {
            class: 'py-2 px-8 border-gray-200 border border-solid rounded-lg',
          },
        }"
				@click="closeModal"
				label="Cancel"
				severity="secondary"
				outlined
			/>
			<Button
			
				class="  items-center"
				:pt="{
          root: {
            class:
              'py-2 bg-blue_primary px-10 border-0 text-white font-light hover:bg-blue_primary/90 rounded-lg',
          },
        }"
				@click="submit"
			> 	
     Save
    </Button>
		</template>
	</Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed } from "vue";
import { useToast } from "primevue/usetoast";

const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
const  times = [ '08:00 AM', '09:00 AM', '10:00 AM', '11:00 AM', '12:00 PM', '01:00 PM', '02:00 PM', '03:00 PM', '04:00 PM', '05:00 PM', '06:00 PM', '07:00 PM', '08:00 PM', '09:00 PM', '10:00 PM', '11:00 PM']
const toast = useToast();
const props = defineProps(["modelValue"]);
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

const submit = () => {
  emit("update:modelValue", false);
};
</script>
 <style scoped>
  .custom-dialog .p-dialog-header {
    display: none; /* Hide the header */
  }
</style>