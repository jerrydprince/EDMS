<template>
    <slot/>
    <div v-for="(item, index) in props.selectedMetadata" :key="index">
        <div v-html="item.metadata_value"></div>
    </div>
</template>

<script setup>
    import { defineProps, reactive, onMounted, watch, defineEmits, nextTick } from 'vue'
    import {useForm} from '@inertiajs/vue3'

    const props = defineProps(['selectedMetadata', 'archiveDocs', 'getFormValues']);

    const form = reactive({})
    const emits = defineEmits(['forms'])

    const updateValues = () => {
        let elements = document.getElementsByClassName('metadata-inputs');
        for (let i = 0; i < elements.length; i++) {
            let name = elements[i].getAttribute('name') || elements[i].getAttribute('id');
            if (name) {
                let doc = props.archiveDocs.find(d => d.archive_keys.toLowerCase() === name.toLowerCase());
                if (doc) {
                    elements[i].value = doc.archive_values;
                    elements[i].setAttribute('data-id', doc.id);
                }
            }
        }
    }

    onMounted(()=>{
        updateValues()
    })

    watch(() => props.archiveDocs, async () => {
        await nextTick();
        updateValues()
    }, { deep: true })

    watch(()=>props.getFormValues, ()=>{
        if (!props.getFormValues) return;
        
        let form = []
        let elements = document.getElementsByClassName('metadata-inputs');
        
        for (let i = 0; i < elements.length; i++) {
            let name = elements[i].getAttribute('name') || elements[i].getAttribute('id');
            if (name) {
                let doc = props.archiveDocs.find(d => d.archive_keys.toLowerCase() === name.toLowerCase());
                if (doc) {
                    let arr = {
                        archive_values: elements[i].value,
                        id: doc.id,
                        archive_id: doc.archive_id,
                        archive_keys: doc.archive_keys
                    }
                    form.push(arr);
                }
            }
        }

        emits('forms', form)
        emits('is_form', false);
    })
</script>
