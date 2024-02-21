<template>
	<Dialog
		v-model:visible="visible"
		modal
		:closable="false"
		:pt="{
      content: { class: 'px-0 bg-white pb-6' },
    }"
		:style="{ width: '35rem', padding: '10px 0px' }"
		:breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
	>
		<template #header>
			<div class=" flex items-center gap-5">
              <div class=" border-[1px] p-3 rounded-full ">
				<i class="pi pi-unlock" style="font-size: 2rem"></i>
			  </div>
			  <div >
                <h1 class=" text-xl">Unlock this File?</h1>
				<p class=" text-sm">The ability for others to make changes would be restored</p>
			  </div>
			</div>
		</template>
		<section class="-mb-6">
			<Divider class="" />
			<div class=" px-5">
			<div class=" flex items-center gap-3 p-2 border-[1.5px] rounded-[50px] border-[#3B82F6] w-[70%]">
			<img src="@/assets/images/word.png" alt="" class=" p-2 rounded-full bg-[#EBF9FF]">
			<p>Auditing & Balancing sheet.docx</p>
		</div>
	</div>
			<Divider class="" />
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
				label="Unlock"
				class=""
				:pt="{
          root: {
            class:
              'py-2 bg-blue_primary px-10 border-0 text-white font-light hover:bg-blue_primary/90 rounded-lg',
          },
        }"
				@click="submitForm"
				
			/>
		</template>
	</Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed } from "vue";
import { useToast } from "primevue/usetoast";

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

const submitForm = () => {
	toast.add({
    severity: "success",
    summary: "File Unlocked",
    detail: "Access to this file has been opened for others",
    life: 3000,
  });
  emit("update:modelValue", false);
};
</script>
<style scoped>
  .custom-dialog .p-dialog-header {
    display: none; /* Hide the header */
  }
</style>
