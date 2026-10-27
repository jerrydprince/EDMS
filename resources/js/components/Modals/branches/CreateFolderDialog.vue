<template>
	<Dialog v-model:visible="visible" modal header="Header" :pt="{
		content: { class: 'px-0 bg-white pb-6' },
	}" :style="{ width: '35rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
		<template #header>
			<div class="flex items-center gap-3 -mb-6">
				<Icon icon="solar:folder-bold" width="45" />
				<div>
					<p class="mb-1 font-medium text-lg">Create File</p>
					<p class="text-gray-500 text-xs">Files keep your documents organized</p>
				</div>
			</div>
		</template>
		<section class="-mb-6">
			<Divider class="" />
			<div class="px-6 w-full overflow-y-auto max-h-[60vh]">
				<div class="mb-5 mt-4">
					<p class="text-sm lg:text-base">
						File Name <span class="text-red-600">*</span>
					</p>
					<InputText id="folder_name" class="mt-1 w-full" v-model="form.name" aria-describedby="username-help"
						placeholder="Enter file name" />
				</div>
				<div class="mb-5 mt-4">
					<p class="text-sm lg:text-base">File Number</p>
					<InputText id="folder_number" class="mt-1 w-full" v-model="form.number" aria-describedby="username-help"
						placeholder="Enter file number" />
				</div>
				<div class="mb-5 mt-4">
					<p class="text-sm lg:text-base">File Type</p>
					<!-- <InputText id="folderType" class="mt-1 w-full" v-model="form.type" aria-describedby="username-help"
						placeholder="Enter folder type" /> -->
					<select name="filetype" v-model="form.type" class="w-full rounded-lg border border-gray-300 px-3 py-3">
						<option value="">Select File type</option>
						<option v-for="filetype in fileType" :value="filetype.id">{{ filetype.name }}</option>
					</select>
				</div>

				<div class="mb-5 mt-4">
					<p class="text-sm lg:text-base">File Year</p>
					<input id="folderYear" type="date"
						class="mt-1 border border-gray-300 outline-none p-3 rounded-md w-full" v-model="form.year" />
					
				</div>

				<div v-if="page.props.roles != 'record manager' && props?.department">
					<div class="mb-5 mt-4">
						<p class="text-sm lg:text-base">Department</p>
						<select name="" id="department" disabled class="w-full rounded-lg border border-gray-300 px-3 py-3" v-model="form.department">
							<option :value="props.department.id" class=" text-gray-400">{{ department_title}}</option>
						</select>
					</div>

					<div class="mb-5 mt-4">
						<p class="text-sm lg:text-base">Division</p>
						<select name="" id="division" class="w-full rounded-lg border border-gray-300 px-3 py-3" v-model="form.division">
							<option value="">Select Division</option>
							<option v-for="div in division" :value="div.id">{{ div.title }}</option>
						</select>
					</div>

					<div class="mb-5 mt-4">
						<p class="text-sm lg:text-base">Unit</p>
						<select name="" id="unit" class="w-full rounded-lg border border-gray-300 px-3 py-3" v-model="form.unit">
							<option value="">Select Unit</option>
							<option v-for="unit in units" :value="unit.id">{{ unit.title }}</option>
						</select>
					</div>
				</div>

				<div class="mb-5 mt-4">
					<p class="text-sm lg:text-base">File Description (optional)</p>
					<textarea name="" id="" class=" w-full border border-gray-300 rounded-md px-4 py-2" v-model="form.description" rows="3"></textarea>
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
import { defineEmits, defineProps, computed, onMounted, ref, watch, onBeforeMount } from "vue";
import { useToast } from "primevue/usetoast";
import { useForm, usePage } from '@inertiajs/vue3'
import axios from "axios";

const toast = useToast();
const props = defineProps(["modelValue", "url", "department", 'fileType', 'folderId', 'deptId', 'divId','unitId']);
const emit = defineEmits(["update:modelValue"]);
const page = usePage();
const division = ref([])
const department_title = ref()
const units = ref([])
const fileType = ref([]);

const form = useForm({
	name: "",
	number: '',
	type: '',
	year: '',
	department:'',
	division:'',
	description:'',
	unit:'',
	id: ''
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
	let folder_type = document.getElementById('folderType');
	let folder_year = document.getElementById('folderYear');
	let department = document.getElementById('department');
	let division = document.getElementById('division');
	let unit = document.getElementById('unit');

	if (folder_name.value == '') {
		folder_name.focus();
		return false
	}
	if (folder_number.value == '') {
		folder_number.focus();
		return false;
	}
	if (folder_year.value == '') {
		folder_year.focus();
		return false;
	}
	if(page.props.roles != 'record manager' && props.department){
		if (department.value == '') {
			department.focus();
			return false;
		}
		if (division.value == '') {
			division.focus();
			return false;
		}
		if (unit.value == '') {
			unit.focus();
			return false;
		}
	}
	form.id = props.folderId
	
	form.get(props.url, {
		onSuccess: page => {
			toast.add({
				severity: "success",
				summary: "Folder Created",
				detail: "You've successfully created a new Folder.",
				life: 3000,
			});
			//	emit("update:modelValue", false);
		},
		onError: errors => {
			emit("update:modelValue", true);
		},
	})
}

const getFileType = ()=>{
	axios.get(`/edms/get-branch-file-type?deptId=${props.deptId}&divId=${props.divId}&unitId=${props.unitId}`).then((response)=>{
		fileType.value = response.data
	})
}

const getUnit = ()=>{
	let found = division.value.filter((val)=>{
		return val.id == form.division
	})
	units.value = found;
}

watch(()=>form.division,()=>{
	getUnit()
})

onMounted(()=>{
	if(page.props.roles !='record manager' && props.department){

		axios.get(`/dashboard/get-division-with-unit`,{params:{id: props.department.id}}).then((response)=>{
			division.value = response.data;
		})

		form.department = props.department.id
	department_title.value = props.department.title
	}

	getFileType()
})


</script>
