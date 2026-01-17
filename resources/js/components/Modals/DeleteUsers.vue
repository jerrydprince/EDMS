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
				
				  <img :src="caution" alt="" class=" absolute " >
				


			</div>
			<div class=" text-center w-[85%]" >
            <h1 class=" text-xl mb-2 font-semibold" >Deactivate User Account</h1>
			<p class="  text-sm text-center">Are you sure you want to delete account? Please note that the account be retrieved back after deletion.</p>
		</div>
	
		</section>
       
		 <template #footer>
			<div class=" flex items-center justify-center gap-5">
                 <button @click="cancelDelete"
				 class= " py-2 w-full px-8 border-gray-200 border border-solid rounded-lg " label="Cancel"
				>
				Cancel</button>

				<button 
				@click="confirmDelete"
				
				class=' bg-[#F04438] w-full  py-2 px-8 text-white rounded-lg'
				severity="secondary"
				outlined>
				Delete
            </button>
			 </div>
		</template> 
	</Dialog>
</template>

<script setup>
import { defineEmits, ref} from "vue";
import { useToast } from "primevue/usetoast";
import grid from "../../../../public/images/grid.svg";
import caution from "../../../../public/images/caution.svg";

const toast = useToast();
const emit = defineEmits(['confirmed', 'cancelled']);
const visible = ref(true)
const confirmDelete = () => {
  emit('confirmed');
  toast.add({
   severity: "success",
     summary: 'Account Deleted',
   detail: "User account deleted successfully ",
    life: 3000,
   });
};

const cancelDelete = () => {
  emit('cancelled');
};


</script>
 <style scoped>
  .custom-dialog .p-dialog-header {
    display: none; /* Hide the header */
  }
</style>