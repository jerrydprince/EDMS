<template>
    <Dialog v-model:visible="visible" modal header="Header" :pt="{
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
                    <p class="mb-1 font-medium text-lg">Add Metadata</p>

                </div>
            </div>
        </template>
        <section class="-mb-6  max-h-[65vh] overflow-y-auto">
            <Divider class="" />
            <div class="my-4 px-12">
                <select name="" id="" class="py-2 px-4 rounded-lg border border-gray-300 w-full"
                    v-model="selectedDoctype">
                    <option value="">Select document type</option>
                    <option :value="doc.id" v-for="doc in branchDocType">{{ doc.name }}</option>
                </select>
            </div>
            <div class=" my-4 px-12" v-if="selectedMetadata && selectedMetadata.length > 0">
                <div v-for="item in selectedMetadata">
                    <div v-html="item.metadata_value"></div>
                </div>
            </div>
            <div class="text-center py-5" v-else>
                <span class=" text-2xl">Please proceed to edit metadata!</span>
                <span class="px-2"><a href="#" class="px-4 py-2 rounded-lg border border-gray-200"
                        @click="setEditMetadata">Edit
                        metadata</a></span>
            </div>

            <Divider class="" />
        </section>
        <template #footer>
            <Button v-if="selectedMetadata && selectedMetadata.length > 0" label="Save" class="" :pt="{
        root: {
            class:
                'py-2 bg-primary px-10 border-0 text-white font-light hover:bg-primary/90 rounded-lg',
        },
    }" @click="submitForm" />
        </template>
    </Dialog>
</template>

<script setup>
import { ref, defineEmits, defineProps, computed, watch, onMounted, reactive } from "vue";
import { useToast } from "primevue/usetoast";
import axios from "axios";


const toast = useToast();
const props = defineProps(["modelValue", 'file']);
const emit = defineEmits(["update:modelValue", 'getFormValues', 'editMetadata', 'formsubmited']);
const metadata = ref([])
const branchDocType = ref([])
const selectedMetadata = ref([])
const archiveDocs = ref([])
const componentKey = ref(0)
const isfileSubmit = ref(false)
const filteredMetadata = ref([])
const documentType = ref([])
const selectedDoctype = ref('')

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

const submitForm = () => {
    isfileSubmit.value = true;

    let elements = document.getElementsByClassName('metadata-inputs');
    let form = []
    for (let i = 0; i < elements.length; i++) {
        let title = elements[i].getAttribute('name');
        let value = elements[i].value;
        if (value == '') {
            elements[i].focus();
            return;
        }
        let arr = {
            archive_values: value,
            archive_id: props.file.id,
            archive_keys: title
        }
        form.push(arr)
    }

    axios.post('/edms/update-branch-metadata', { form: form }).then((response) => {
        emit('formsubmited', true);
        emit("update:modelValue", false);
    })
}

const allMetadatas = () => {
    branchDocType.value.forEach(element => {
        if (element.metadata && element.metadata.length > 0) {
            element.metadata.forEach(meta => {
                if (!metadata.value.find(obj => obj.id == meta.id)) {
                    metadata.value.push(meta)
                }
            })
        }
    });

}

const getDocType = () => {
    axios.get('/edms/branch-metdata').then((response) => {
        branchDocType.value = response.data
    })
}

// const getDocumentType = ()=>{
//     axios.get('/get-branch-document-type').then((response)=>{
//         documentType.value = response.data;
//         console.log(response.data)
//     })
// }

const setEditMetadata = () => {
    emit('editMetadata', true)
    emit("update:modelValue", false);
}

watch(branchDocType, () => {
    allMetadatas()
})


watch(selectedDoctype, () => {
    //filter the metadata and remove any metadata which already exist with the document type
    let found = branchDocType.value.filter((val) => val.id === selectedDoctype.value);
    if (found && found.length > 0) {
        let index = -1;
        let metadata = [...found[0].metadata]

        for (let data of filteredMetadata.value) {
            index = metadata.findIndex((val) => val.name.toLowerCase() === data.name.toLowerCase())
            if (index != -1) {
                metadata.splice(index, 1)
            }
        }

        selectedMetadata.value = metadata
    } else {
        selectedMetadata.value = []
    }
})

watch(() => props.file, () => {
    if (props.file && props.file.archive_docs) {
        let arr = []
        metadata.value.forEach((item) => {
            let data = props.file.archive_docs.find(val => val.archive_keys.toLowerCase() == item.name.toLowerCase());
            //    if(!data){
            //     arr.push(item)
            //    }
            if (data) {
                arr.push(item)
            }
        })
        filteredMetadata.value = arr
    }
})


onMounted(() => {
    getDocType()
    // getDocumentType()
})
</script>