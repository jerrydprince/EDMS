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
                <div class=" p-4 border rounded-full">
                <Icon icon="pajamas:link" width="25"/>
            </div>
             <div>
                <h1 class=" font-semibold text-lg">Add Link</h1>
                <p class=" text-sm text-[#1D2939]">Add hyperlinks to texts</p>
             </div>
            </div>

     </template>
		<Divider class=" -mt-2"/>

        <section class=" px-6">
           <div class=" mb-3">
            <label>Text</label>
            <input type="text" v-model="linkData.text" class=' mt-1 p-2 border rounded-md outline-none w-full'>
           </div>
           <div class=" mb-3">
            <label>Link</label>
            <input type="text" v-model="linkData.link" placeholder="Input text link" class='  mt-1 p-2 border rounded-md outline-none w-full'>
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
				label="Confirm"
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

const linkData = ref({
    text: '',
    link: '',
})

const toast = useToast();
const props = defineProps(["modelValue"]);
const emit = defineEmits(["update:modelValue", "data"]);


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
    emit("data", linkData.value);
};
</script>
<style scoped>
  .custom-dialog .p-dialog-header {
    display: none;
    width: 20%;
  }
</style>
