<template>
    <Dialog  v-model:visible="visible" modal header="Header" :pt="{
        content: { class: 'px-0 bg-white pb-6' },
    }" :style="{ width: '35rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
        <template #header>
            <div class="flex items-center gap-3 -mb-10">
                <Icon icon="solar:arrow-left-outline" width="25" @click="closeModal" />
                <div class="">
                    <svg width="92" height="91" viewBox="0 0 92 91" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g filter="url(#filter0_d_618_21370)">
                            <g clip-path="url(#clip0_618_21370)">
                                <rect x="24.3334" y="18.332" width="43.3333" height="42" rx="6.66667" fill="#2B2B33"
                                    fill-opacity="0.9" />
                                <rect x="32.1664" y="24.166" width="28.3333" height="33" rx="3.5" fill="white" />
                                <rect x="32.1664" y="24.166" width="28.3333" height="33" rx="3.5" stroke="#2B2B2B" />
                                <rect x="36.3328" y="30.332" width="16.6667" height="1" rx="0.5" fill="#CBCBCD" />
                                <rect x="36.3328" y="34.334" width="16.6667" height="1" rx="0.5" fill="#CBCBCD" />
                                <g filter="url(#filter1_bii_618_21370)">
                                    <path
                                        d="M23.6664 55.6933V37.0266C23.933 33.5598 26.4442 32.471 27.6664 32.3599H36.6664C38.7997 32.0933 40.6664 33.8044 41.333 34.6933C41.6664 35.0266 42.6664 36.36 44.333 38.6933C45.6664 41.0267 48.2219 42.2488 49.6664 42.3599H62.333C66.5997 42.3599 67.6664 45.0266 67.6664 46.3599V53.0266C67.6664 60.2266 62.1108 62.6933 59.333 63.0266C53.2219 63.1377 39.3997 63.2933 32.9997 63.0266C26.5997 62.7599 24.1108 58.0266 23.6664 55.6933Z"
                                        fill="black" fill-opacity="0.5" />
                                </g>
                            </g>
                        </g>
                        <defs>
                            <filter id="filter0_d_618_21370" x="0.333375" y="0.332033" width="91.3333" height="90"
                                filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                <feFlood flood-opacity="0" result="BackgroundImageFix" />
                                <feColorMatrix in="SourceAlpha" type="matrix"
                                    values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                <feMorphology radius="8" operator="erode" in="SourceAlpha"
                                    result="effect1_dropShadow_618_21370" />
                                <feOffset dy="6" />
                                <feGaussianBlur stdDeviation="16" />
                                <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.15 0" />
                                <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_618_21370" />
                                <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_618_21370"
                                    result="shape" />
                            </filter>
                            <filter id="filter1_bii_618_21370" x="7.66638" y="16.332" width="76" height="62.8418"
                                filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                <feFlood flood-opacity="0" result="BackgroundImageFix" />
                                <feGaussianBlur in="BackgroundImageFix" stdDeviation="8" />
                                <feComposite in2="SourceAlpha" operator="in"
                                    result="effect1_backgroundBlur_618_21370" />
                                <feBlend mode="normal" in="SourceGraphic" in2="effect1_backgroundBlur_618_21370"
                                    result="shape" />
                                <feColorMatrix in="SourceAlpha" type="matrix"
                                    values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                <feOffset dy="1.33333" />
                                <feComposite in2="hardAlpha" operator="arithmetic" k2="-1" k3="1" />
                                <feColorMatrix type="matrix" values="0 0 0 0 1 0 0 0 0 1 0 0 0 0 1 0 0 0 0.12 0" />
                                <feBlend mode="normal" in2="shape" result="effect2_innerShadow_618_21370" />
                                <feColorMatrix in="SourceAlpha" type="matrix"
                                    values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                <feOffset dx="4.66667" dy="-2.66667" />
                                <feGaussianBlur stdDeviation="1.33333" />
                                <feComposite in2="hardAlpha" operator="arithmetic" k2="-1" k3="1" />
                                <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0" />
                                <feBlend mode="normal" in2="effect2_innerShadow_618_21370"
                                    result="effect3_innerShadow_618_21370" />
                            </filter>
                            <clipPath id="clip0_618_21370">
                                <rect x="24.3334" y="18.332" width="43.3333" height="42" rx="6.66667" fill="white" />
                            </clipPath>
                        </defs>
                    </svg>

                </div>
                <div>
                    <p class="mb-1 font-medium text-lg">Edit Metadata</p>

                </div>
            </div>
        </template>
        <section class="-mb-6 overflow-y-scroll max-h-[65vh]">
            <Divider class="" />
            <div class=" my-4 px-12" v-if="selectedMetadata && selectedMetadata.length > 0">
                <!-- <div v-for="item in selectedMetadata">
                    <div v-html="item.metadata_value"></div>
                </div> -->
                <MetadataStringUpdate :selectedMetadata="selectedMetadata" :archiveDocs="archiveDocs" @forms="saveForms" @is_form="isfileSubmit = false" :getFormValues="isfileSubmit"/>
               
                
            </div>

            <Divider class="" />
        </section>
        <template #footer>
            <Button label="Save" class="" :pt="{
        root: {
            class:
                'py-2 bg-primary px-10 border-0 text-white font-light hover:bg-primary/90 rounded-lg',
        },
    }" @click="submitForm" />
        </template>
    </Dialog>
</template>

<script setup>
import { ref, defineEmits, defineProps, computed, watch, onMounted, reactive, onActivated } from "vue";
import { useToast } from "primevue/usetoast";
import MetadataStringUpdate from "../../Pages/dashboard/partials/MetadataStringUpdate.vue";
import axios from "axios";


const toast = useToast();
const props = defineProps(["modelValue",'file']);
const emit = defineEmits(["update:modelValue", 'getFormValues','formsubmited']);
const metadata = ref([])
const selectedMetadata = ref([])
const archiveDocs = ref([])
const componentKey = ref(0)
const isfileSubmit = ref(false)

const form = reactive({
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

const submitForm = ()=>{
    isfileSubmit.value = true;
}

const saveForms = (data)=>{
    axios.post('/dashboard/update-branch-metadata',{form:data}).then((response)=>{
        emit('formsubmited',true);
    })
}

watch(()=>props.file, ()=>{
    if(props.file && props.file.archive_docs){
        let arr = []
        let docs = [];
        for(let item of props.file.archive_docs){
            let found = metadata.value.filter((meta)=>meta.name.toLowerCase() === item.archive_keys.toLowerCase())
            if(found[0] !== undefined){
                 arr.push(found[0])
                 form[item.archive_keys.toLowerCase()] = item.archive_values
                 docs.push(item)
            }
           
        }
        selectedMetadata.value = arr
        archiveDocs.value = docs
    }
})

// watch(()=> props.modelValue,()=>{
//     let keys = Object.keys(form)
//    if(keys && keys.length > 0){
//         for(let i=0; i < keys.length; i++){
//             console.log(keys[i])
//             let element = document.getElementById(`${keys[i]}`);
//             console.log(element)
//         }
//    }
// })


onMounted(()=>{
    axios.get('/dashboard/metadatas').then((response)=>{
        metadata.value = response.data
        for(let docs of archiveDocs.value){
            if(document.getElementById(docs.archive_keys)){
               let element = document.getElementById(docs.archive_keys);
               element.value = docs.archive_values
               element.setAttribute('data-id', docs.id)
            }
        }
    })
})


</script>