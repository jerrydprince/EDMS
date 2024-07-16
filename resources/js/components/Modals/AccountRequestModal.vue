<style>
    .shadow-lg {
    --tw-shadow: none;
    --tw-shadow-colored: none;
    box-shadow: none;
}
</style>
<template>
    <div>
        <Dialog v-model:visible="visible" modal header="Header" :pt="{
            content: { class: 'px-0 bg-white pb-6 shadow-lg' },
        }" :style="{ width: '35rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
            <template #header>
                <div class="flex items-center gap-3 -mb-6">
                    <div class="rounded-full py-4 px-4 border border-border_primary border-solid">
                        <!-- <Icon icon="ph:arrows-split-fill" width="20" :verticalFlip="true" /> -->
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="#344054" stroke-width="2.004" stroke-linecap="round" stroke-linejoin="round"/>
</svg>

                    </div>
                    <div>
                        <p class="mb-1 font-medium text-lg">Account Request</p>
                        <p class="text-gray-500 text-xs">Manage and view account request</p>
                    </div>
                </div>
            </template>
            <Divider class="" />
            <section class="px-6 ">
               <div class=" grid grid-cols-2 py-2" v-for="notification in props.notifications">
                <div>
                    <p class="leading-3">{{parseJson(notification).last_name+" "+parseJson(notification).first_name}}</p>
                    <span class=" text-xs text-gray-400">Grade level {{parseJson(notification).grade_level}}</span>
                </div>
                <div class="flex align-middle items-center justify-center">
                    <button class=" rounded-lg border px-7 py-1 hover:text-gray-600 mx-2">Cancel</button>
                    <Link :href="'/dashboard/add-user?email='+parseJson(notification).email+'&notification_id='+notification.id" class="rounded-lg border px-4 py-1 bg-blue-500 text-white hover:bg-blue-400">View Details</Link>
                </div>
               </div>

               <!-- <div class=" grid grid-cols-2 py-2">
                <div>
                    <p class="leading-3">John smith</p>
                    <span class=" text-xs text-gray-400">Grade 2</span>
                </div>
                <div>
                    <button class=" rounded-lg border px-7 py-1 hover:text-gray-600 mx-2">Cancel</button>
                    <button class="rounded-lg border px-4 py-1 bg-blue-500 text-white hover:bg-blue-400">View Details</button>
                </div>
               </div> -->

            </section>
            <template #footer>
            
            </template>
           
        </Dialog>
    </div>
</template>

<script setup>
import { ref, defineEmits, defineProps, computed, onMounted, reactive, watch} from "vue";
import { useToast } from 'primevue/usetoast';
import { Link } from "@inertiajs/vue3";

const props = defineProps(["modelValue", "notifications"]);
const emit = defineEmits(["update:modelValue"]);

const visible = computed({
        get() {
            return props.modelValue;
        },
        set(value) {
            emit("update:modelValue", value);
        },
    });

const parseJson = (data) =>{
    return JSON.parse(data.data)
}


</script>