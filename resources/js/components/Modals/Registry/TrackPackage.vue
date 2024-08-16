<template>
    <Dialog v-model:visible="visible" :closable="false" modal :pt="{
        content: { class: 'px-0 bg-white pb-6' },
    }" :style="{ width: '35rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
        class=" .custom-dialog">
        <template #header>
            <div class=" flex items-start justify-between w-full">
                <div class=" flex items-center gap-3">
                    <div class=" p-3 border rounded-full">
                        <Icon icon="streamline:customer-support-1-solid" width="30" />
                    </div>
                    <div>
                        <h1 class=" text-2xl font-medium">Track File</h1>
                        <p class=" text-sm">Efficiently monitor file movement.</p>
                    </div>
                </div>
            </div>

        </template>
        <Divider class=" -mt-2" />

        <section class=" px-6">
            <div class=" flex items-start gap-1">
                <label class=" text-[#344054] font-semibold">Search Package</label>
            </div>
            <div class="relative mt-4">
                <div class="flex items-center border p-2 rounded-lg gap-2 text-sm mt-5 lg:mt-0">
                    <i class="pi pi-search text-gray-700" style="font-size: 1rem"></i>
                    <input @input="TrackFile" type="text" class="w-96 border-0 outline-none text-gray-700"
                        placeholder="Enter File Name / Number" :value="search_text" />
                </div>

                <div class="border border-solid py-3 bg-white max-h-[300px] overflow-y-scroll hide_scroll rounded-md absolute z-50 w-full shadow-xl mt-2"
                    v-if="toggleSuggestion">
                    <div v-if="files.length > 0">
                        <div class=" p-3 px-3  cursor-pointer border-b" v-for="(file, index) in files"
                            :key="index" @click="updateFileSelection(file)">
                            <div class="">
                                <h1 class=" font-normal">{{ file.name }}</h1>
                            </div>
                            <p class=" mt-2 text-xs w-fit font-normal px-2 py-1 rounded-xl text-gray-500 bg-gray-50">{{
                            file.doc_no }}</p>
                        </div>
                    </div>
                    <div v-else class=" flex flex-col items-center my-5">
                        <img src="/images/search.svg" alt="">
                        <h1 class=" font-medium mt-3 text-lg">No Result Found</h1>
                        <p class=" opacity-70 text-center">Try entering a different term or contact <br> support for
                            assistance
                        </p>
                    </div>
                </div>
            </div>
        </section>
        <Divider class=" -mb-2" />
        <template #footer>

            <Button class="mr-4" :pt="{
        root: {
            class: 'py-2 px-8 border-gray-200 border border-solid rounded-lg',
        },
    }" @click="closeModal" label="Cancel" severity="secondary" outlined />
            <Button @click="submit" label="Search" class="" :pt="{
        root: {
            class:
                'py-2  text-white bg-primary rounded-lg py-2 px-7 font-light  rounded-lg',
        },
    }" />
        </template>
    </Dialog>
    <ViewPackage v-model="viewpackageModel" :fileMovement="file_movement" :file="file"
    :department="props.department" :division="props.division" :unit="props.unit" />
</template>

<script setup>
import { defineEmits, defineProps, onMounted, computed, ref, watch } from "vue";
import ViewPackage from "../../Modals/Registry/ViewPackage.vue";
import { useToast } from "primevue/usetoast";
import image from "../../../assets/images/members/1.svg";
import  debounce  from 'lodash.debounce';
import axios from "axios";

const props = defineProps(["modelValue" ,"department", "division", "unit"]);
const emit = defineEmits(["update:modelValue"]);
const viewpackageModel = ref(false)
const Users = ref('')
const department = ref({})
const search_text = ref()

const datas = ref([]);
const Recipients = ref([]);
const Recipients2 = ref([]);
const Suggestions = ref([]);
const toggleSuggestion = ref(false)
const selectedFile = ref()
const file = ref();
const file_movement = ref()
const filterRecipients = () => {
    // const searchTerm = Users.value.toLowerCase();
    // const combinedArray = [...Recipients.value, ...Recipients2.value]
    // Suggestions.value = combinedArray.filter((recipient) =>
    //     recipient.name.toLowerCase().includes(searchTerm) ||
    //     recipient.doc_no.toLowerCase().includes(searchTerm)
    // );
    toggleSuggestion.value = true
};
//const search = ref();
const files = ref([])

const TrackFile = debounce((e)=>{
    let search = e.target.value
    if(search !=''){
        axios.get('/dashboard/registry/track-files', {params:{search:search}}).then((response)=>{
            files.value = response.data
            if(files.value && files.value.length > 0){
                toggleSuggestion.value = true
            }
        })
        search_text.value = search
    }else{
        //files.value.length = 0
        // files.value = []
        // toggleSuggestion.value = false
    }
    
}, 500)

// watch(search, (newval)=>{
//     if(newval){
//         TrackFile()
//     }
// })

// const getFilesInDept = async () => {
//     try {
//         const response = await axios.get('/dashboard/registry/dept');
//         department.value = response.data.data[0]
//         const responses = await axios.get(`/dashboard/registry/deptFiles/${department.value.id}`);
//         console.log('get data', responses.data);
//         Recipients.value = responses.data.received_files;
//         Recipients2.value = responses.data.sent_files;
//         console.log('here again', Recipients.value);

//     } catch (error) {
//         console.error('Error fetching data:', error);
//     }
// };
// onMounted(() => {
//     getFilesInDept()
// })


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
const updateFileSelection = (file) => {
    console.log(file)
    // Users.value = suggestion.name
    // Suggestions.value = []
    // toggleSuggestion.value = false
   if(file.file_movement && file.file_movement.length > 0){
    selectedFile.value = file.file_movement[0];
   }
    search_text.value = file.name
    toggleSuggestion.value = false
};

const submit = () => {
    viewpackageModel.value = true
    // Users.value = ''
    
    axios.get('/dashboard/registry/tracked', {
        params: {
            file_id: selectedFile.value.file_id, 
            file_movement_id: selectedFile.value.file_movement_id
        }
    }).then((response) => {
       file.value = response.data.file
       file_movement.value = response.data.file_movement
        emit("update:modelValue", false);
    }).catch((error) => {
        console.error('An error occurred:', error);
    });

    // try {
    //     const response1 = await axios.get('/dashboard/registry/dept');
    //     department.value = response1.data.data[0]
    //     // console.log('show',encodedFileName);
    //     let encodedFileName = encodeURIComponent(Users.value);
    //     const response2 = await axios.get(`/dashboard/registry/files/department/${department.value.id}/name/${encodedFileName}`);
    //     const response3 = await axios.get(`/dashboard/registry/getFileMovements/${response2.data?.file_id}`);
    //     datas.value = response3.data
    //     // console.log(datas.value);
    //     const newValues = ['gg:organisation', 'tabler:box', 'jam:fork', 'tabler:box']
    //     datas.value.movements.forEach((obj, index) => {
    //         obj.icon = newValues[index]
    //     })
    //     if (datas.value.movements.length > 0) {
    //         const lastObject = datas.value.movements[datas.value.movements.length - 1];
    //         lastObject.moves = 'Destination'; // Example key-value pair
    //     }
    //     datas.value.movements.forEach(obj => {
    //         const date = new Date(obj.created_at);
    //         obj.created_at = date.toDateString()
    //     })
    //     console.log('updated show', datas.value);
    // } catch (error) {
    //     console.error('Error fetching data:', error);
    // }
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
    border-color: #D0D5DD;
    /* Hide border */
}
</style>
