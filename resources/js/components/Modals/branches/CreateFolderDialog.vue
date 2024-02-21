<template>
	<Dialog v-model:visible="visible" modal header="Header" :pt="{
		content: { class: 'px-0 bg-white pb-6' },
	}" :style="{ width: '35rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
		<template #header>
			<div class="flex items-center gap-3 -mb-6">
				<Icon icon="solar:folder-bold" width="45" />
				<div>
					<p class="mb-1 font-medium text-lg">Create Folder</p>
					<p class="text-gray-500 text-xs">Folders keep your documents organized</p>
				</div>
			</div>
		</template>
		<section class="-mb-6">
			<Divider class="" />
			<div class="px-6 w-full">
				<div class="mb-5 mt-8">
					<p class="text-sm lg:text-base">
						Folder Name <span class="text-red-600">*</span>
					</p>
					<InputText id="folder_name" class="mt-1 w-full" v-model="form.name" aria-describedby="username-help"
						placeholder="Enter folder name" />
				</div>
				<div class="mb-5 mt-8">
					<p class="text-sm lg:text-base">Folder Number</p>
					<InputText  id="folder_number" class="mt-1 w-full" v-model="form.number" aria-describedby="username-help"
						placeholder="Enter folder number" />
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
						'py-2 bg-blue_primary px-10 border-0 text-white font-light hover:bg-blue_primary/90 rounded-lg',
				},
			}" @click="submitForm" />
		</template>
	</Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed } from "vue";
import { useToast } from "primevue/usetoast";

const toast = useToast();
const props = defineProps(["modelValue"]);
const emit = defineEmits(["update:modelValue"]);
import { useForm } from '@inertiajs/vue3'

const form = useForm({
        name: "",
        number:''
    })

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
/*
const submitForm = () => {
  toast.add({
	severity: "success",
	summary: "Folder Created",
	detail: "You've successfully created a new folder.",
	life: 3000,
  });
  emit("update:modelValue", false);
};
*/
const submitForm = () => {
	let folder_name = document.getElementById('folder_name');
	let folder_number = document.getElementById('folder_number');
	if (folder_name.value == '') {
		folder_name.focus();
		return false
	}
	if (folder_number.value == '') {
		folder_number.focus();
		return false;
	}
	form.get('/create-folder', {onSuccess: page => {
			toast.add({
					severity: "success",
					summary: "Document Created",
					detail: "You've successfully created a new document.",
					life: 3000,
				});
			//	emit("update:modelValue", false);
		},
		onError: errors => {
			emit("update:modelValue", true);
		},
	})
}
</script>
