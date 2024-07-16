<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    content: { class: 'px-0 bg-white pb-6' },
  }" :style="{ width: '35rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '60vw', '575px': '90vw' }"
    class=" .custom-dialog">

    <section class="-mb-6  flex flex-col items-center relative">
      <div
        class="w-full absolute shadow-2xl -mt-7  shadow-white bg-white pb-[5px] flex items-center justify-between px-5" @click="downloadFile">
        <div class=" flex items-center gap-3">
          <img :src="page.props.base+'/images/file.svg'" alt="" class="">
          <p>{{ props.archive?.name }}</p>
        </div>
        <i class="pi pi-download font-semibold cursor-pointer"></i>
      </div>
      <Divider class=" bg-white" />
      <img :src="page.props.base+'/images/change_format.svg'" alt="" class=" -mt-16 w-70">
      <div class=" text-center w-[75%] absolute bottom-5 shadow-2xl shadow-white  bg-white p-5">
        <h1 class=" text-2xl">Convert Format</h1>
        <p class=" opacity-70 my-2">Choose the conversion format for your file.</p>
        <div class=" flex  justify-between mt-5">
          <input type="text" class=" outline-none border w-32 rounded-md p-2 bg-slate-50" :placeholder="currentFileFormat"
            disabled>
          <img :src="page.props.base+'/images/converter.svg'" alt="" class=" px-[20px] py-[2px]  rounded-3xl border ">
          <select @change="setSelectedFormat" class=" border w-32 rounded-md p-2 bg-slate-50 outline-none cursor-pointer">
            <option value="">Select format</option>
            <option v-for="format in fileFormat" :value="format">{{ format }}</option>
          </select>
        </div>
      </div>
      <Divider class=" mt-20" />
    </section>
    <template #footer>
      <Button class="mr-4" :pt="{
        root: {
          class: 'py-2 px-8 border-gray-200 border border-solid rounded-lg',
        },
      }" @click="closeModal" label="Cancel" severity="secondary" outlined />
      <Button class="  items-center" :disabled="form.to==''" :pt="{
        root: {
          class:
            'py-2 bg-blue_primary px-10 border-0 text-white font-light hover:bg-blue_primary/90 rounded-lg',
        },
      }" @click="submit">
        Convert File
      </Button>
    </template>
  </Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed, onMounted, ref } from "vue";
import { useToast } from "primevue/usetoast";
import { useForm, usePage } from "@inertiajs/vue3";

const toast = useToast();
const props = defineProps(["modelValue", "archive"]);
const emit = defineEmits(["update:modelValue"]);
const page = usePage();
const form = useForm({
  current: '',
  to: '',
  id:null
})


const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  },
});

const setSelectedFormat = (event) => {
  form.to = event.target.value;
  form.current = currentFileFormat.value
  form.id = props.archive?.id
}
const closeModal = () => {
  emit("update:modelValue", false);
};

const submit = () => {
  form.post('/change-file-format',{onSuccess: ()=>{
    toast.add({
				severity: "success",
				summary: "Format changed",
				detail: "You've successfully changed document format",
				life: 3000,
			});
  }})
  emit("update:modelValue", false);
};

const getFileExtension = computed(() => {
  let extension_split = props.archive?.path.split('.');
  let extension = extension_split ? extension_split[extension_split.length - 1] : [];
  return extension
})

const fileFormat = computed(() => {
  let arr = ["Image Format", "Document Format", "PDF Format", "Excel Format"];
  let index = arr.indexOf(currentFileFormat.value);

  if (index !== -1) {
    arr.splice(index, 1);
  }
  return arr;
})

const currentFileFormat = computed(() => {
  let images = ['jpg', 'jpeg', 'gif', 'png', 'svg'];
  let docs = ['doc', 'docx'];
  let pdf = ['pdf'];
  let excel = ['xls', 'xlsx'];
  if (images.includes(getFileExtension.value)) {
    return "Image Format"
  } else if (docs.includes(getFileExtension.value)) {
    return "Document Format"
  } else if (pdf.includes(getFileExtension.value)) {
    return "PDF Format"
  } else if (excel.includes(getFileExtension.value)) {
    return "Excel Format"
  }
})

const downloadFile = async () => {
  try {
    const response = await axios.get('/download-file', {
      params: { id: props.archive.id },
      responseType: 'blob'
    });

    // Extract the filename from the Content-Disposition header
    const contentDisposition = response.headers['content-disposition'];
    const fileName = contentDisposition.split('filename=')[1].replace(/"/g, '').trim();;

    // Determine the content type based on the file extension
    const contentType = fileName.endsWith('.pdf') ? 'application/pdf' :
                        fileName.endsWith('.jpg') || fileName.endsWith('.jpeg') ? 'image/jpeg' :
                        fileName.endsWith('.png') ? 'image/png' :
                        'application/octet-stream'; // Default to binary data

    // Create a blob object from the binary data
    const blob = new Blob([response.data], { type: contentType });

    // Create a URL for the blob object
    const url = window.URL.createObjectURL(blob);

    // Create a link element and simulate a click to trigger the file download
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', fileName); // Set the filename for the downloaded file
    document.body.appendChild(link);
    link.click();

    // Cleanup by revoking the object URL
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Error downloading file:', error);
  }
};

onMounted(() => {
 
})
</script>
<style scoped>
.custom-dialog .p-dialog-header {
  display: none;
  /* Hide the header */
}
</style>