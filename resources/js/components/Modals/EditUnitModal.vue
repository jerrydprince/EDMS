<template>
    <Dialog v-model:visible="visible" modal header="Header" :pt="{
        content: { class: 'px-0 bg-white pb-6' },
    }" :style="{ width: '35rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
        <template #header>
            <div class="flex items-center gap-3 -mb-6">
                <div class="rounded-full py-4 px-4 border border-border_primary border-solid">
                    <Icon icon="mdi:company" width="25" />
                </div>
                <div>
                    <p class="mb-1 font-medium text-lg">Edit Unit</p>
                    <p class="text-gray-500 text-xs">Define Unit Attributes</p>
                </div>
            </div>
        </template>
        <section class="-mb-6">
            <Divider class="" />
            <div class="px-6">
                <div class="mb-5 mt-8">
                    <p class="text-sm lg:text-base">Unit Title *</p>
                    <InputText id="username" class="mt-1 w-full" v-model="form.title" aria-describedby="username-help"
                        placeholder="Enter Department Name" />
                </div>
                <div class="mb-5">
                    <p class="text-sm lg:text-base">Unit Description (Optional)</p>
                    <Textarea v-model="form.description" rows="5" cols="30" class="mt-1 w-full" placeholder="Enter a description..." />
                </div>
                <!-- <div class="mb-6">
                    <p class="text-sm lg:text-base">Set Department Head</p>
                    <span class="text-gray-400 relative">
                        <Icon icon="iconamoon:search-light" width="20"
                            class="pi pi-search absolute top-2/4 -mt-[10.2px] left-3 text-surface-400 dark:text-surface-600" />
                        <InputText id="username" class="mt-1 w-full pl-10" v-model="value" aria-describedby="username-help"
                            placeholder="Add by name or email" />
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
            }" @click="submitForm('Department')" />
        </template>
    </Dialog>
    
</template>
  
<script setup>
import { ref, defineEmits, defineProps, computed, watch } from "vue";
import { useToast } from "primevue/usetoast";
import Userimageholder from "../../assets/Userimageholder.vue";
import { useForm } from '@inertiajs/vue3';

const toast = useToast();
const props = defineProps(["modelValue", "unit"]);
const emit = defineEmits(["update:modelValue", "update:step"]);

const form = useForm({
    title: "",
    description: "",
    id: ""
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
    emit("update:step", 1);
    emit("update:modelValue", false);
};

const submitForm = (modal = "Department") => {
   // form.id = props.department.id
    form.post('/admin/edit-unit',{onSuccess: ()=>{
        toast.add({
            severity: "success",
            summary: `${modal} Created`,
            detail: `You've successfully updated a new ${modal}.`,
            life: 3000,
        });
        emit('close');
    }})
    
    if (props.step === 3) {
        emit("update:modelValue", false);
    } else {
        //setTimeout(() => emit("update:step", props.step + 1), 1000);
        emit("update:step", props.step + 1)
    }
};

watch(()=>props.unit, ()=>{
    form.title = props.unit.title
    form.description = props.unit.description
    form.id = props.unit.id
})
</script>
  