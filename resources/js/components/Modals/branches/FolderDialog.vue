<template>
	<Dialog
		v-model:visible="visible"
        :closable="false"
		modal
		:pt="{
      content: { class: 'px-0 bg-white pb-6' },
    }"
		:style="{ width: '25rem', padding: '10px 0px' }"
		:breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
        class=" .custom-dialog"
	>
	
		<section class="  flex flex-col items-center ">
			<div class=" relative items-center justify-center  flex -mt-5">
				  <img :src="grid" alt="" class=" -mt-10 ">
				
				  <img :src="caution" alt="" class=" absolute " v-if=" action === 'close'">
				
				  <img :src="open" alt="" class=" absolute " v-else>
				


			</div>
			<div class=" text-center w-[85%]" v-if=" action === 'close'">
            <h1 class=" text-xl mb-2 font-semibold" >Close Folder</h1>
			<p class="  text-sm text-center">Are you sure you want to close folder? This means that documents archiving would be temporarily disabled.</p>
		</div>
		<div class=" text-center w-[85%]" v-else>
            <h1 class=" text-xl mb-2 font-semibold" >Open Folder</h1>
			<p class="  text-sm text-center">Are you sure you want to Open folder? Access by other Colleague would be enabled.</p>
		</div>
		</section>
       
		 <template #footer>
			<div class=" flex items-center justify-center gap-5">
                 <button @click="closeModal"
				 class= " py-2 w-full px-8 border-gray-200 border border-solid rounded-lg " label="Cancel"
				>
				Cancel</button>

				<button 
				@click="submit"
				v-if=" action === 'close'"
				class=' bg-[#F04438] w-full  py-2 px-8 text-white rounded-lg'
				severity="secondary"
				outlined>
				Ok</button>

				
				<button 
				
				@click="submit"
				v-else
				class=' bg-[#0E7A1D] w-full  py-2 px-8 text-white rounded-lg'
				severity="secondary"
				outlined>
				Ok</button>
			 </div>
		</template> 
	</Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed ,ref} from "vue";
import { useToast } from "primevue/usetoast";
// import grid from '/assets/images/grid.png';
// import caution from '/assets/images/caution.png';
// import open from '/assets/images/open.png';
import grid from "../../../../../public/images/grid.png";
import caution from "../../../../../public/images/caution.png";
import open from "../../../../../public/images/open.png";

const toast = useToast();
const props = defineProps(["modelValue", "action"]);
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