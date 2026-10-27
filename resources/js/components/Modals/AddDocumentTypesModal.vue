<template>
    <Dialog v-model:visible="visible" modal header="Header" :pt="{
        content: { class: 'px-0 bg-white pb-6' },
    }" :style="{ width: '35rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
        <template #header>
            <div class="flex items-center gap-3 -mb-6">
                <Icon icon="solar:arrow-left-outline" width="25" @click="closeModal" />
                <div class="ml-2 rounded-full py-4 px-4 border border-border_primary border-solid">
                    <Icon icon="uiw:setting-o" width="25" />
                </div>
                <div>
                    <p class="mb-1 font-medium text-lg">Docuent Type</p>
                    <p class="text-gray-500 text-xs">Add document type to department</p>
                </div>
            </div>
        </template>
        <section class="-mb-6">
            <Divider class="" />
            <div class="px-6 w-full">
                <div class="mb-5 mt-8">
                    <p class="text-sm lg:text-base text-gray-700">
                        Search Document Type
                    </p>
                    <span class="text-gray-400 relative mr-1">
                        <Icon icon="iconamoon:search-light" width="20"
                            class="pi pi-search absolute top-2/4 -mt-[10.2px] left-3 text-surface-400 dark:text-surface-600" />
                        <InputText v-model="search" placeholder="Search"
                            class="pl-10 w-full mt-1 h-10 bg-transparent font-light" aria-describedby="username-help" />
                    </span>
                </div>
                <div class="mt-4 overflow-y-scroll max-h-[45vh]">
                    <div v-for="doctype of filteredMetadata" :key="doctype.key" class="flex gap-3 mb-7 items-center">
                        <Checkbox v-model="selectedDocTypes"  :inputId="doctype.key" :pt="{}" name="doctype"
                            :value="doctype.id" />
                        <label :for="doctype.id" class="text-gray-700">{{
                            doctype.name
                        }}</label>
                    </div>
                </div>
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
import { ref, defineEmits, defineProps, computed, watch } from "vue";
import { useToast } from "primevue/usetoast";
import { router } from '@inertiajs/vue3';

const toast = useToast();
const props = defineProps(["modelValue",'doctypes','branch','url']);
const emit = defineEmits(["update:modelValue"]);
const search = ref("");
const selectedDocTypes = ref([]);



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
    let data = {id: props.branch.id, doctypes: selectedDocTypes.value}
    console.log(data)
    router.post(props.url, data, {onSuccess: (status)=>{
        selectDocType()
        
        toast.add({
            severity: "success",
            summary: "Document type ",
            detail: "You've successfully added document type to department",
            life: 3000,
        });
    }})
    
    emit("update:modelValue", false);
};

const selectDocType = ()=>{
    selectedDocTypes.value = []
    if(props.branch.document_type){
       for(let doctype of props.branch.document_type){
        selectedDocTypes.value.push(doctype.id);
       }
    } 
   
}

const filteredMetadata = computed(()=>{
 
    let filtered = null
    if(search.value !==''){
        let filtered = props.doctypes.filter((val)=>{
            return val.name.toLowerCase().includes(search.value.toLocaleLowerCase())
        })
        return filtered;
    }else{
        return props.doctypes
    }
   
})

watch(()=>props.branch, ()=>{
    selectDocType()
})
</script>
  
