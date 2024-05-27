<template>
    <slot/>
    <div v-for="item in props.selectedMetadata">
                    <div v-html="item.metadata_value"></div>
                </div>
</template>

<script setup>
    import { defineProps, reactive, onMounted, watch, defineEmits } from 'vue'
    import {useForm} from '@inertiajs/vue3'

    const props = defineProps(['selectedMetadata', 'archiveDocs', 'getFormValues']);

    const form = reactive({})
    const emits = defineEmits(['forms'])

    onMounted(()=>{
        //form.title = props.archiveDocs.title
        for(let docs of props.archiveDocs){
            let id = docs.archive_keys.toLowerCase()
            if(id.includes(' ')){
                id = id.split(' ').join('_')
            }
            if(document.getElementById(id)){
               let element = document.getElementById(id);
               element.value = docs.archive_values
               element.setAttribute('data-id', docs.id)
            }
        }
    })

    watch(()=>props.getFormValues, ()=>{
        let form = []
        for(let docs of props.archiveDocs){
            if(document.getElementById(docs.archive_keys.toLowerCase())){
               let element = document.getElementById(docs.archive_keys.toLowerCase());
               let key = docs.archive_keys;
               let value = element.value;
               let id = docs.id;
               let archive_id = docs.archive_id;
                let archive_keys = docs.archive_keys

               let arr = {
                archive_values: value,
                id: id,
                archive_id: archive_id,
                archive_keys: archive_keys
               }
               form.push(arr);
            }
        }

        emits('forms', form)
        emits('is_form', false);
    })
</script>