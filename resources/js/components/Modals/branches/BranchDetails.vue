<template>
	<Sidebar v-model:visible="visible" :show-close-icon="false" position="right" class="w-full relative " style="width: 400px;">

		<div class=" flex justify-between items-center">
			<div class=" flex justify-between gap-3 items-center">
				<img :src="'/assets/images/detail.svg'" alt="">
				<h1 class=" text-[#1D2939] font-semibold text-xl">Details</h1>
			</div>
			<button class="" @click=" visible = false">
				<i class="pi pi-times" style="font-size: 1rem"></i>
			</button>
		</div>
		<Divider />
		<div class=" flex items-start gap-3 text-[#1D2939] text-lg ">
			<img :src="getFileExtension(props.file)" alt="" class=" p-2 rounded-full bg-[#EFFBF5]" width="32">
			<div>
				<h1 class="w-64 whitespace-normal break-word">{{ (props.file.title)? props.file.title.archive: props.file.name }}</h1>
				<div @click="uploadDocument"
					class="flex gap-2 w-44 items-center text-gray-700 border-gray-300 border-solid border rounded-lg text-sm  py-2 px-3 lg:px-4 cursor-pointer mt-2">
					<div>
						<i class="pi pi-cloud-upload" style="font-size: 1rem"></i>
					</div>
					<p class=" text-xs text-[#344054]" >Upload New Version</p>
				</div>
			</div>
		</div>

		<TabMenu v-model:activeIndex="active" :model="items" :pt="{
			root: { class: 'text-primary' },
			inkbar: { class: 'text-primary' },
			action: ({ props, context, state }) => ({
				class: `${context.index === state.d_activeIndex
						? 'text-primary border-b-2 border-solid border-[#3B82F6]'
						: ''
					} p-1 pr-0 md:pr-3 md:p-3 pl-1 text-gray-500 -mb-[2px]`,
			}),
		}">
			<template #item="{ item, props }">

				<p v-bind="props.action" class="flex align-items-center gap-1 md:gap-2 text-sm md:text-base">
					<span class="font-normal text-lg text-nowrap cursor-pointer">{{ item.label }}</span>
				</p>

			</template>
		</TabMenu>

		<div v-if="active === 0">
			<section>
				<div>
					<img :src="page.props.base+'/storage'+props.file.path" alt=""
						class=" p-4 border-[3px] border-b-0 pb-0 rounded-t-2xl mt-5   cursor-pointer"
						@click="togglePreview = true, visible = false">
				</div>

				<!-- =====file details ====== -->
				<div class=" text-sm mt-5 h-32 flex flex-col justify-between ">
					<h1 class=" text-lg text-[#3B82F6] uppercase">File Details</h1>

					<div class=" flex items-center justify-between">
						<span class="text-[#98A2B3] uppercase">Document type</span>
						<span class=" text-[#344054] uppercase">{{ fileType }} Document</span>
					</div>
					<div class=" flex items-center justify-between">
						<span class="text-[#98A2B3] uppercase">Size</span>
						<span class=" text-[#344054]">{{ props.file.size }}</span>
					</div>
					<div class=" flex items-center justify-between">
						<span class="text-[#98A2B3] uppercase">Created</span>
						<span class=" text-[#344054] uppercase">{{ props.file.uploaded_by }}</span>
					</div>
					<div class=" flex items-center justify-between">
						<span class="text-[#98A2B3] uppercase">Last Modified</span>
						<span class=" text-[#344054] uppercase">{{ props.file.date }}</span>
					</div>
				</div>

				<!-- ======other info ===== -->
				<h1 class=" text-lg mt-5 text-[#3B82F6]">Metadata</h1>
				<div class=" my-2" v-if="file.archive_docs">
					<!-- <h1 class="text-[#98A2B3]">Owner</h1>
					<div class=" flex items-center gap-2 mt-2">
						<img src="/assets/images/user.png" alt="" class=" w-8">
						<h1 class=" text-sm text-[#344054] font-semibold">Dave Miye</h1>
					</div> -->
					<template v-for="meta in file.archive_docs">
						<div class=" flex items-center justify-between py-2 border-b border-b-gray-200">
							<span class="text-[#98A2B3] pr-2 uppercase">{{meta.archive_keys}}</span>
							<span class=" text-[#344054] uppercase">{{ meta.archive_values}}</span>
						</div>
					</template>
				</div>

				<!-- =========location ======= -->
				<div>
					<h1 class=" text-lg text-[#98A2B3] mt-5">Location</h1>

					<div class=" flex items-center flex-wrap text-sm font-semibold gap-2 mt-2">
						<h1 class="uppercase">{{file?.department?.title}} <span class="uppercase" v-if="file.department">Department</span> </h1>
						<span><i class="pi pi-angle-right text-[#101828] text-lg"></i></span>
						<h1 class="uppercase">{{file?.division?.title}} <span class="uppercase" v-if="file.division">Division</span></h1>
						<span><i class="pi pi-angle-right text-[#101828] text-lg"></i></span>
						<h1 class="uppercase">{{file?.unit?.title}} <span class="uppercase" v-if="file.unit">Unit</span> </h1>
						<span><i class="pi pi-angle-right text-[#101828] text-lg"></i></span>
						<h1 class="uppercase">{{props.folder?.name}} </h1>

					</div>
				</div>
			</section>
		</div>

		<div v-else>
			<!-- ==========activities ======== -->
			<div v-for=" (activity, index) in props.file.file_activity" :key="index">
				<div class=" flex gap-4 my-5" :class="{ 'expanded': expandedIndexes.includes(index) }">
					<img :src="getFileExtension(props.file)" alt="" class=" w-7 h-7">
					<div class=" text-sm cursor-pointer" @click="toggleExpandable(index)">
						<p class=" font-semibold">{{ activity.details }}</p>
						<p>{{ moment(activity.date).fromNow() }}</p>
						<div v-if="expandedIndexes.includes(index)" class=" text-center">

							<!-- <div class=" flex items-center  ml-5">
								<div>
									<i class="pi pi-file  " style="font-size: 0.7rem"></i>
									<div class=" flex items-center text-[#D0D5DD] ml-2 ">
										<p class="">|</p>
										<p class=" -ml-[2px]">__</p>
									</div>
								</div>
								<div class="ml-1">
									<p class="text-[10px] mt-2">{{ activity.details.details_2 }}</p>
									<p class="text-[10px] mt-1"> {{ activity.details.details_2 }}</p>
								</div>
							</div> -->

							<!-- <button class=" border p-1 text-[6px] rounded-sm mt-2">
								<i class="pi pi-download" style="font-size: 0.4rem"></i>
								<span> Download Previous version</span>
							</button> -->
						</div>
					</div>
				</div>
			</div>

		</div>

	</Sidebar>
	<div>
		<!-- <Preview v-if="togglePreview" :togglePreviewFunction="togglePreviewState" /> -->
		<PreviewFile v-if="togglePreview"  :togglePreviewFunction="togglePreviewState" :file="props.file"/>
		<UploadDocumentDialog v-model="isUploadDocument" :file="props.file" :folder="props.folder" @updateDocument="handleUpdateDocument"/>
	</div>
</template>

<script setup>
import { defineEmits, defineProps, computed, ref } from "vue";
import PreviewFile from "../../../components/Modals/branches/PreviewFile.vue";
import { usePage } from '@inertiajs/vue3';
import UploadDocumentDialog from "../UploadDocumentDialog.vue";
import moment from 'moment';

const previewModal = ref(false)
const props = defineProps(["modelValue", "file", 'folder']);
const emit = defineEmits(["update:modelValue", 'updateDocument']);
const togglePreview = ref(false);
const page = usePage();
const fileType = ref('')
const isUploadDocument = ref(false);

const togglePreviewState = () => {
	togglePreview.value = !togglePreview.value;
};

const uploadDocument = ()=>{
	isUploadDocument.value = true;
}

const active = ref(0);
const expandedIndexes = ref([])
const toggleExpandable = (index) => {
	const indexPosition = expandedIndexes.value.indexOf(index);
	if (indexPosition === -1) {
		expandedIndexes.value.push(index);
	} else {
		expandedIndexes.value.splice(indexPosition, 1);
	}
}

const visible = computed({
	get() {
		return props.modelValue;
	},
	set(value) {
		emit("update:modelValue", value);
	},
});

const items = ref([
	{
		label: "Details",
	},
	{
		label: "Activities",
	},
]);

const fileDatails = ref(
	{
		document_type: "PDF Document",
		size: "65kb",
		created: "Dec 24, 2014",
		Last_Modified: "Dec 24, 2024"
	}
)

const home = ref({
	icon: 'pi pi-home',
	route: '/'
});



const location = ref([
	{ label: 'Accounting Department ' },
	{ label: 'Auditng Division' },
	{ label: 'Division Unit' },
	{ label: 'Architecture Folder' },
])


const activities = ref([

	{
		date: "12 days ago",
		image: "/assets/images/user.png",
		activity: "You created a new version of this file",
		details: {
			details_1: "Compliance document V1",
			details_2: "Compliance document V1",
		},
	}
	,
	{
		date: "12 days ago",
		image: "/assets/images/user.png",
		activity: "You created a new version of this file",
		details: {
			details_1: "Compliance document V1",
			details_2: "Compliance document V1",
		},

	},
	{
		date: "12 days ago",
		image: "/assets/images/user.png",
		activity: "You created a new version of this file",
		details: {
			details_1: "Compliance document V1",
			details_2: "Compliance document V1",
		},
	},
	{
		date: "12 days ago",
		image: "/assets/images/user.png",
		activity: "You created a new version of this file",
		details: {
			details_1: "Compliance document V1",
			details_2: "Compliance document V1",
		},

	},
	{
		date: "12 days ago",
		image: "/assets/images/user.png",
		activity: "You created a new version of this file",
		details: {
			details_1: "Compliance document V1",
			details_2: "Compliance document V1",
		},

	},
])

const getFileExtension = (file)=>{
    const f_path = file.path? file.path : file?.name
   
    if(f_path){
       const parts = f_path.split('.');

      const extension = parts.pop();

      if(extension == 'jpg' || extension == 'jpeg' || extension == 'gif' || extension == 'png'){
		fileType.value = 'Image'
          return page.props.base+'/images/image.svg'
      }
      
      if(extension == 'pdf'){
		fileType.value = 'PDF'
          return page.props.base+'/images/pdf.svg'
      }

      if(extension == 'doc' || extension == 'doctx'){
		fileType.value = 'Word'
          return page.props.base+'/images/word.svg'
      }

      if(extension == 'xlx' || extension == 'xlsx'){
		fileType.value = 'Excel'
          return page.props.base+'/images/excel.svg'
      }
    }
   

}

const handleUpdateDocument = ()=>{
	emit('updateDocument');
}

</script>


<style scoped>
.text-primary {
	color: #3B82F6;
}

.expanded {
	border: 1px solid #D0D5DD;
	padding: 10px 10px;
	border-radius: 10px;
}

.sidebar {
	background: black;
}
</style>
