<template>
    <Dialog v-model:visible="visible" :closable="false" modal :pt="{
        content: { class: 'px-0 bg-white pb-6' },
    }" :style="{ width: '35rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
        class=" .custom-dialog">
        <template #header>
            <div class=" flex items-center gap-3">
                <div class=" p-3 border border-solid rounded-full">
                    <Icon icon="radix-icons:question-mark-circled" width="25" />
                </div>

                <div>
                    <h1 class=" font-semibold text-2xl">Documentation</h1>
                    <p class=" text-sm">Customize FAQ’s to answer user’s questions</p>
                </div>
            </div>
        </template>
        <Divider class=" -mt-2" />

        <section class="  text-[#1D2939] h-[70vh] overflow-y-auto hide_scroll">
            <div class=" flex items-center justify-end mx-7">
                <button class=" flex items-center gap-1 border border-dashed rounded-2xl px-2 py-1  bg-gray-50" @click="addDoc  ">
                    <Icon icon="ic:baseline-plus" width="20" />
                    <p>Add New</p>
                </button>
            </div>

            <div v-for="(item, index) in docs" :key="index">
                <div class=" flex items-center  gap-4 mx-7">
                    <img src="../../assets/images/dot.svg" alt="">
                    <div>
                        <h1 class=" font-medium">Documentation {{ index + 1 }}</h1>

                    </div>
                </div>

                <div class=" ml-14 mx-7">
                    <div class=" flex items-center border  border-gray-300 p-2 mt-2 rounded-md ">
                        <h1 class=" opacity-50 text-lg">Text:</h1>
                        <input type="text" class="  font-medium px-2 outline-none w-full" v-model="item.name">
                    </div>

                    <div class=" flex mt-3 items-center gap-2 ">
                        <div class=" flex items-center border w-full border-gray-300 p-2 mt-2 rounded-md">
                            <h1 class=" opacity-50 text-lg">Link:</h1>
                            <input type="text" class="  font-medium px-2 outline-none w-full" v-model="item.link">
                        </div>
                        <div>
                            <button type="button" @click="removeDoc(index)">
                                Remove
                            </button>
                        </div>
                    </div>

                </div>
                <Divider />
            </div>

        </section>



        <template #footer>
            <Button class="mr-4" :pt="{
        root: {
            class: 'py-2 px-8 border-gray-200 border border-solid rounded-lg',
        },
    }" @click="closeModal" label="Cancel" severity="secondary" outlined />
            <Button class="  items-center" :pt="{
        root: {
            class:
                'py-2 bg-blue_primary px-10 border-0 text-white font-light hover:bg-blue_primary/90 rounded-lg',
        },
    }" @click="submit">
                Save
            </Button>
        </template>
    </Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed, ref } from "vue";
import { useToast } from "primevue/usetoast";
const docs = ref([
    {
        name: '',
        link: ''
    }
])
const addDoc = () => {
    docs.value.push({
        name: '',
        link: '',
    })
}
const removeDoc = (index) => {
        docs.value.splice(index, 1)
}
const Documentations = [
    { title: 'Documentation 1' },
    { title: 'Documentation 2' },
    { title: 'Documentation 3' },
]
const toast = useToast();
const props = defineProps(["modelValue"]);
const emit = defineEmits(["update:modelValue"]);

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

const submit = async () => {
    const formData = new FormData()
    docs.value.forEach((doc, index) => {
        if (doc.link && !/^https?:\/\//i.test(doc.link)) {
        doc.link = `http://${doc.link}`;
    }
    formData.append(`docs[${index}][name]`, doc.name);
    formData.append(`docs[${index}][link]`, doc.link);
    })
    try {
    const response = await axios.post('/admin/support/doc', formData)
    console.log("sent:", response.data)
  } catch (error) {
    console.error(error)
  }
    emit("update:modelValue", false);
};
</script>
<style scoped>
.custom-dialog .p-dialog-header {
    display: none;
    /* Hide the header */
}
</style>
