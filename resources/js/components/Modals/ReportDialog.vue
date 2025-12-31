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
       
           <div class=" flex flex-col items-center justify-center w-full h-">
            <img :src="'@/assets/images/report_modal.svg'" alt="" class=" -mt-5 h-56">
            <div class=" text-center absolute flex flex-col items-center justify-center bg-white py-5 top-36   shadow-white">
                <h1 class=" text-xl font-semibold">Report Complaint</h1>
                <p class=" text-sm text-[#667085] w-[80%] ">If you encounter any issues or concerns with a contact, use this form to report it. </p>
            </div>
           </div>
         
     </template>
		<Divider class=" -mt-2"/>

        <section class=" px-6">
           <div class=" mb-3">
            <div class=" flex items-start gap-1">
            <label class=" text-[#344054] font-semibold">Subject</label>
            <span>*</span>
        </div>
            <input type="text" class=' border-solid border-gray-300 mt-1 p-2 border rounded-md outline-none w-full text-sm' placeholder="A brief subject line for the report." required>
            
           </div>
           <div class=" mb-2">
            <label class=" text-[#344054] font-semibold">Describe Situation</label>
            <textarea name="" id="" cols="30" rows="3" class=" w-full border border-gray-300 rounded-md overflow-hidden resize-none outline-none p-2"></textarea>
           </div>
           <div class=" flex items-center gap-1 mb-2">
            <h1 class=" text-[#344054] font-semibold">Upload proof</h1>
            <span class=" text-gray-500">(Optional)</span>
           </div>

           <button class="bg-[#EBF3FE] text-xs text-[#2C62B9] border-[#2C62B9] border-dashed border-[2px] px-2 py-1  rounded-3xl relative flex items-center gap-1 "  v-if="imagePreviews.length === 0">
        <input type="file" class="opacity-0 cursor-pointer absolute w-full h-full"   @change="previewImages" accept="image/*" multiple/>
        <Icon icon="material-symbols:upload"   width="15" />
        <p>Upload Image</p>
      </button>

      <!-- ================ image upload ========= -->
      <div class=" flex items-center gap-3  ">
        <div class=" flex items-center gap-3 max-w-[60%] overflow-x-auto hide_scroll ">
          <div v-for="(image, index) in imagePreviews" :key="index"  class=" w-20 h-28 border rounded-xl overflow-hidden relative flex-none">
           <img :src="image" alt="" class=" object-cover h-full w-full absolute z-10">
           <div class=" p-0.5 rounded-full bg-white  absolute right-1 top-1 z-30 cursor-pointer" @click="removeImage(index)">
           <Icon icon="uil:times"   />
          </div>
          </div>   
        </div>
        <div class=" w-20 h-28 bg-[#EBF3FE] border-[#2C62B9] border-dashed border-[2px]   flex items-center flex-col justify-center  rounded-xl overflow-hidden relative " v-if="imagePreviews.length !== 0">
          <input type="file" class="opacity-0 cursor-pointer absolute w-full h-full"   @change="previewImages" accept="image/*" multiple/>
          <Icon icon="ic:baseline-plus"  class=" text-primary" width="25" />
          </div> 
        </div>
        </section>
        <Divider class=" -mb-2"/>
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
            @click="submit"
				label="Submit Complaint"
				class=""
				:pt="{
          root: {
            class:
              'py-2 bg-primary px-10 border-0 text-white font-light  rounded-lg',
          },
        }"
				
			/>
		</template>
	</Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed ,ref} from "vue";
import { useToast } from "primevue/usetoast";
const imagePreviews = ref([]);
const previewImages = (event) => {
  const files = event.target.files;
  if (files) {
        for (let i = 0; i < files.length; i++) {
          const file = files[i];
          const reader = new FileReader();
          reader.onload = (e) => {
            imagePreviews.value.push(e.target.result);
          };
          reader.readAsDataURL(file);
        }
      }
}

const removeImage = (index) => {
      imagePreviews.value.splice(index, 1);
    };
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
  imagePreviews.value = []
};

const submit = () => {
  emit("update:modelValue", false);
  imagePreviews.value = []
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
    resize: none;
    width: 100%
}
.p-inputtextarea:focus {
    border-color: #D0D5DD; /* Hide border */
}
</style>