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
	
		<section class="-mb-6  flex flex-col items-center relative">
            <img src="@/assets/images/encription.png" alt="" class=" -mt-12">
            <div class=" text-center w-[75%] absolute mt-28 shadow-2xl shadow-white  bg-white p-5" >
                <h1 class=" text-2xl">Encryption Code</h1>
                <p  class=" opacity-70 my-2">Generate a one time encryption code to grant someone one-time access to this file</p>
            </div>
            <div class=" flex gap-3 absolute bottom-12">
			<input type="text" placeholder="0" class=" border-[2px] outline-none w-16 text-center text-[25px] p-0 rounded-2xl ">
            <input type="text" placeholder="0" class=" border-[2px] outline-none w-16 text-center text-[25px] rounded-2xl ">
            <input type="text" placeholder="0" class=" border-[2px] outline-none w-16 text-center text-[25px] rounded-2xl ">
            <span class=" text-[25px] opacity-50 mb-8">_</span>
            <input type="text" placeholder="0" class=" border-[2px] outline-none w-16 text-center text-[25px] rounded-2xl ">
            <input type="text" placeholder="0" class=" border-[2px] outline-none w-16 text-center text-[25px] rounded-2xl ">
            <input type="text" placeholder="0" class=" border-[2px] outline-none w-16 text-center text-[25px] rounded-2xl ">
        </div>
			<Divider class=" mt-28" />
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
				label="Generate Code"
				class=""
				:pt="{
          root: {
            class:
              'py-2 bg-blue_primary px-10 border-0 text-white font-light hover:bg-blue_primary/90 rounded-lg',
          },
        }"
				@click="submit"
			/>
		</template>
	</Dialog>
  <CopyEncriptionCode v-model="CopyEncriptionModal"  />
</template>

<script setup>
import { defineEmits, defineProps, computed ,ref} from "vue";
import { useToast } from "primevue/usetoast";
import CopyEncriptionCode from "@/components/Modals/branches/CopyEncriptionCode.vue";
const toast = useToast();
const props = defineProps(["modelValue"]);
const emit = defineEmits(["update:modelValue"]);

const CopyEncriptionModal = ref(false)

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
  CopyEncriptionModal.value = true
  emit("update:modelValue", false);
};
</script>
 <style scoped>
  .custom-dialog .p-dialog-header {
    display: none; /* Hide the header */
  }
</style>