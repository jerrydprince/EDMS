<template>
    <Dialog v-model:visible="visible" modal header="Header" :pt="{
        content: { class: 'px-0 bg-white pb-6' },
    }" :style="{ width: '35rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
        <template #header>
            <div class="flex items-center gap-3 -mb-6">
                <div class="rounded-full py-4 px-4 border border-border_primary border-solid">
                    <img :src="imageIcon" alt="">
                </div>
                <div>
                    <p class="mb-1 font-medium text-lg">File Activity</p>
                    <p class="text-gray-500 text-xs">View all activities for the selected file</p>
                </div>
            </div>
        </template>
        <section class="-mb-6">
            <Divider class="" />
            <div class="px-6 overflow-y-auto min-h-[40vh]">
               <table class=" table my-4 w-full">
                    <thead class="border-t border-b">
                        <tr>
                            <th class="py-3">#</th>
                            <th class="py-3">Activity</th>
                            <th class="py-3">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(activity, index) in props.selectedArchive.file_activity" class="border-b">
                            <td class="py-3">{{ index +1 }}</td>
                            <td class="py-3">{{ activity.details }}</td>
                            <td class="py-3">{{ moment(activity.date).startOf('day').fromNow() }}</td>
                        </tr>
                    </tbody>
               </table>
            </div>
            <Divider class="" />
        </section>
        <template #footer>
            <Button class="mr-4" :pt="{
                root: {
                    class: 'py-2 px-8 border-gray-200 border border-solid rounded-lg',
                },
            }" @click="closeModal" label="Close" severity="secondary" outlined />
        </template>
    </Dialog>
    
</template>
  
<script setup>
import { ref, defineEmits, defineProps, computed, watch } from "vue";
import { useToast } from "primevue/usetoast";
import imageIcon from "../../../../public/images/image.svg";

import moment from 'moment';


const toast = useToast();
const props = defineProps(["modelValue", "selectedArchive"]);
const emit = defineEmits(["update:modelValue"]);
import { usePage } from '@inertiajs/vue3';

const page = usePage()

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


</script>
  
