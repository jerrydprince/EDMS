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
                    <h1 class=" font-semibold text-2xl">Frequenly Asked Questions</h1>
                    <p class=" text-sm">Customize FAQ’s to answer user’s questions</p>
                </div>
            </div>
        </template>
        <Divider class=" -mt-2" />

        <section class="  text-[#1D2939] h-[70vh] overflow-y-auto hide_scroll">
            <div class=" flex items-center justify-end mr-7">
                <button class=" flex items-center gap-1 border border-dashed rounded-2xl px-2 py-1  bg-gray-50"
                    type="button" @click="addFaq">
                    <Icon icon="ic:baseline-plus" width="20" />
                    <p>Add New</p>
                </button>
            </div>
            <div v-for="(item, index) in faqs" :key="index">
                <div class=" flex items-center  gap-3 ml-7">
                    <img src="../../assets/images/dot.svg" alt="">
                    <div>
                        <h1 class=" font-medium">Question {{ index + 1 }}</h1>
                    </div>
                </div>

                <div class=" ml-14 mr-5  ">
                    <div class=" flex items-center border  border-gray-300 p-2 mt-2 rounded-md mr-20">
                        <h1 class=" opacity-50 text-lg">Q:</h1>
                        <input type="text" class="font-medium px-2 outline-none w-full" v-model="item.question">
                    </div>

                    <div class=" flex mt-3 items-center gap-2 mr-12">
                        <div class=" flex items-center border w-full border-gray-300 p-2 mt-2 rounded-md">
                            <h1 class=" opacity-50 text-lg">A:</h1>
                            <input type="text" class="  font-medium px-2 outline-none w-full" v-model="item.answer">
                        </div>
                        <input type="file" ref="fileInputs" :id="`file-input-${index}`" class="hidden"
                            @change="handleFileUpload($event, index)" />
                            <span v-if="item.imageName">{{ item.imageName }}</span>
                        <Icon icon="lucide:image" width="25" class=" text-[#667085]" @click="triggerFileInput(index)" />
                        <div>
                            <button type="button" @click="removeFaq(index)">
                                Remove
                            </button>
                        </div>
                    </div>

                </div>
                <Divider />
            </div>
            <Divider />
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
import { defineEmits, defineProps, ref, computed } from "vue";
import { useToast } from "primevue/usetoast";
const fileInputs = ref([])
const faqs = ref([
    {
        question: '',
        answer: '',
        image: null,
        imageName:""
    },
])

const addFaq = () => {
    faqs.value.push({
        question: '',
        answer: '',
        image: null,
    })
}
const removeFaq = (index) => {
    faqs.value.splice(index, 1)
}

const triggerFileInput = (index) => {
    document.getElementById(`file-input-${index}`).click()
}
const handleFileUpload = (event, index) => {
    faqs.value[index].imageName = event.target.files[0].name;
    faqs.value[index].image = event.target.files[0]
}
const Questions = [
    {
        title: 'Question 1'
    },
    {
        title: 'Question 2'
    }
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
    faqs.value.forEach((faq, index) => {
        formData.append(`faqs[${index}][question]`, faq.question)
        formData.append(`faqs[${index}][answer]`, faq.answer)
        if (faq.image) {
            formData.append(`faqs[${index}][image]`, faq.image)
        }
    })
    try {
    const response = await axios.post('/dashboard/support/faqs', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
    console.log(response.data)
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
