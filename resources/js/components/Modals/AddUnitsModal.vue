<style>
    .custom-card {
    /* border: 1px solid gray; */
    border-radius: 5px;
    max-height: 21vh;
    position: absolute;
    z-index: 999;
    background: #fff;
    top: 81%;
    width: 92%;
    overflow-y: auto;
    /* box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px; */
    /* box-shadow: rgba(0, 0, 0, 0.09) 0px 2px 1px, rgba(0, 0, 0, 0.09) 0px 4px 2px, rgba(0, 0, 0, 0.09) 0px 8px 4px, rgba(0, 0, 0, 0.09) 0px 16px 8px, rgba(0, 0, 0, 0.09) 0px 32px 16px; */
    box-shadow: rgba(0, 0, 0, 0.2) 0px 12px 28px 0px, rgba(0, 0, 0, 0.1) 0px 2px 4px 0px, rgba(255, 255, 255, 0.05) 0px 0px 0px 1px inset;
}

.custom-card-inner-div:hover{
  background-color: #F2F4F7;
}
</style>
<template>
    <div>
        <Dialog v-model:visible="visible" modal header="Header" :pt="{
            content: { class: 'px-0 bg-white pb-6' },
        }" :style="{ width: '35rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
            <template #header>
                <div class="flex items-center gap-3 -mb-6">
                    <div class="rounded-full py-4 px-4 border border-border_primary border-solid">
                        <Icon icon="ph:arrows-split-fill" width="20" :verticalFlip="true" />
                    </div>
                    <div>
                        <p class="mb-1 font-medium text-lg">{{ props.division.title }}</p>
                        <p class="text-gray-500 text-xs">Add unit to {{ props.division.title }}</p>
                    </div>
                </div>
            </template>
            <section class="-mb-6">
                <Divider class="" />
                <div class="px-6">
                    <div class="mb-5 mt-8">
                        <p class="text-sm lg:text-base">Unit Title *</p>
                        <InputText id="division_title" class="mt-1 w-full" v-model="divisionForm.title"
                            aria-describedby="username-help" placeholder="Enter Division Name" />
                    </div>
                    <div class="mb-5">
                        <p class="text-sm lg:text-base">Unit Description (Optional)</p>
                        <Textarea v-model="divisionForm.description" rows="3" cols="30" class="mt-1 w-full"
                            placeholder="Enter a description..." />
                    </div>

                    <!-- display searched result starts here -->
                    <div v-if="unitHead.length !==0"  class=" custom-card py-4">
                    <div class="py-2 flex items-center justify-between cursor-pointer custom-card-inner-div px-6" v-for="(head, index) in unitHead" @click="setSelectedUnitHead(head,index)"
                    :class="{'cursor-pointer': head.is_unit_head !== 1}">
                        <div class="flex items-center gap-3">
                        <Userimageholder />
                        <div>
                            <p :class="{'line-through': head.is_unit_head === 1}" class="text-base lg:text-lg font-medium">{{head.first_name+" "+head.last_name}}</p>
                            <p :class="{'line-through': head.is_unit_head === 1}" class="-mt-1 text-gray-700 font-light text-sm lg:text-base">
                            {{head.email}}
                            </p>
                        </div>
                        </div>

                    </div>
                    </div>
                    <!-- display searched result ends here -->

                    <!-- <div class="mb-6">
                        <p class="text-sm lg:text-base">Set Unit Head</p>
                        <span class="text-gray-400 relative">
                            <Icon icon="iconamoon:search-light" width="20"
                                class="pi pi-search absolute top-2/4 -mt-[10.2px] left-3 text-surface-400 dark:text-surface-600" />
                            <InputText id="division_head" class="mt-1 w-full pl-10" v-model="divisionForm.head"
                                aria-describedby="username-help" placeholder="Add by name or email" />
                        </span>
                    </div> -->
                    <!-- USer Image is displayed here -->
                    <!-- <div class="mb-6 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <Userimageholder />
                            <div>
                                <p class="text-base lg:text-lg font-medium">Dave Miye</p>
                                <p class="-mt-1 text-gray-700 font-light text-sm lg:text-base">
                                    davemiye@ansel.com
                                </p>
                            </div>
                        </div>
                        <Icon icon="prime:times" width="35" />
                    </div> -->
                    <div v-if="selectedHead.length != 0" class=" overflow-auto max-h-20">
                        <div class="mb-6 flex items-center justify-between" v-for="(head, index) in selectedHead">
                            <div class="flex items-center gap-3">
                            <Userimageholder />
                            <div>
                                <p class="text-base lg:text-lg font-medium">{{head.first_name+" "+head.last_name}}</p>
                                <p class="-mt-1 text-gray-700 font-light text-sm lg:text-base">
                                {{head.email}}
                                </p>
                            </div>
                            </div>
                            <Icon icon="prime:times" width="35" @click="removeSelectedHead(head, index)" />
                        </div>
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
                            'py-2 bg-primary px-10 border-0 text-white font-light hover:bg-primary/90 rounded-lg',
                    },
                }" @click="submitForm('Division')" />
            </template>
        </Dialog>
    </div>
    <!-- <Toast /> -->

</template>

<script setup>
import { ref, defineEmits, defineProps, computed, reactive, watch} from "vue";
import { useToast } from 'primevue/usetoast';
import Userimageholder from "../../assets/Userimageholder.vue";
import { router } from '@inertiajs/vue3'
import { formToJSON } from "axios";
import { useForm } from "@inertiajs/vue3";
import { debounce } from 'lodash';


let search_data = null;

const props = defineProps(["modelValue", "division"]);
const emit = defineEmits(["update:modelValue"]);
const selectedHead = ref([])
const unitHead = ref([])
const toast = useToast()

//forms
const divisionForm = useForm({title:'', description:'',head:'', division_id: props.division.id})

const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  },
});



const closeModal = () => {
  emit("update:step", 1);
  emit("update:modelValue", false);
};

const submitForm = (modal = "Department") => {
    divisionForm.division_id = props.division.id
    divisionForm.post('/dashboard/add-unit-to-division',{onSuccess: ()=>{
        emit("close", true);
        divisionForm.reset();
        selectedHead.value.length = 0;
        unitHead.value.length = 0;
  }})
   
  toast.add({
            severity: "success",
            summary: `${modal} Created`,
            detail: `You've successfully updated a new ${modal}.`,
            life: 3000,
        });
  
};

const Search = (search) => {
  search_data = search
}

const setSelectedUnitHead = (val,index)=> {
    if(val.is_unit_head === 1){
        return false;
    }
  unitHead.value.splice(index,1);
  selectedHead.value.push(val)
}

const removeSelectedHead = (head, index)=>{
 if(unitHead.value.length !== 0){
  unitHead.value.push(head);
 }
  selectedHead.value.splice(index,1)

}

watch(() => divisionForm.head, (val)=>{
  
  debouncedSearch()
})

const debouncedSearch = debounce(() => {
  if(divisionForm.head !==''){
    axios.get('/dashboard/search-unit-head',{params:{name:divisionForm.head, 'type':'unit'}}).then((response)=>{
      unitHead.value = response.data
    })
  }else{
    unitHead.value.length = 0
  }
}, 300);

</script>
