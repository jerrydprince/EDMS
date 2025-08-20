<template>
  <div class="fixed bg-[#00000066] modal flex items-center flex-col">
    <div class="relative w-full flex items-center px-10 mt-5 justify-between">
        <div class="text-3xl text-white cursor-pointer" @click="togglePreview">
            <Icon icon="ic:round-arrow-back" />
        </div>

        <div class="absolute left-1/2 transform -translate-x-1/2">
            <div class="flex gap-2 items-center bg-[#D0D5DD29] rounded-3xl py-1.5 px-5">
            <div class="bg-[#EBF3FE] p-2 rounded-full ">
                <!-- <Icon icon="vscode-icons:file-type-word" width="20" /> -->
                <Icon :icon="getFileIcon(file[currentIndex].path)" width="20" />
            </div>
            <p class="text-sm text-white">{{ getFileName() }}</p>
            </div>
        </div>

        <div class="text-white flex items-center gap-14 text-3xl">
            <Icon icon="ph:printer" @click="printCurrentFile" class=" cursor-pointer"/>
            <Icon icon="material-symbols:download" @click="downloadCurrentFile" class="cursor-pointer" />
        </div>
        </div>

    <div class=" absolute bottom-5 flex items-center px-10 w-full justify-center h-[85vh]">
      <div class="text-2xl p-2 rounded-full bg-white cursor-pointer" :class="{ ' opacity-0': currentIndex === 0 }"
        @click="moveItems(-1)">
        <Icon icon="ph:arrow-left" />
      </div>
      <div>
        <div v-for="(item, index) in file" :key="index" class=" w-[50vw] h-[80vh] px-2"
          v-show="currentIndex === index">
          <img v-if="fileType(item) == 'image'" :src="getFileExtension(item)" alt=""
            class=" w-full h-full object-contain" />
          <object v-if="fileType(item) == 'pdf'" :data="page.props.base + '/storage/' + item.path" type="application/pdf"
            width="100%" height="100%">
            <p>{{ page.props.base + '/storage' + item.path }} <a :href="page.props.base + '/storage' + item.path">click here to
                download the PDF file.</a></p>
          </object>
        </div>
      </div>
      <div v-if="file" class="text-2xl p-2 rounded-full bg-white cursor-pointer"
        :class="{ 'opacity-0': currentIndex === file.length - 1 }" @click="moveItems(1)">
        <Icon icon="ph:arrow-right" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, defineProps, defineEmits } from "vue";
import { onClickOutside } from "@vueuse/core";
import image1 from "../../../assets/images/document3.svg";
import { usePage } from "@inertiajs/vue3";

const { togglePreviewFunction, file } = defineProps(["togglePreviewFunction", "file"]);
const page = usePage();
const emit = defineEmits(['toggleVersion']);

const currentIndex = ref(0);
const previousIndex = ref(null);

function moveItems(step) {
  const newIndex = currentIndex.value + step;
  if (newIndex >= 0 && newIndex < file.length) {
    previousIndex.value = currentIndex.value;
    currentIndex.value = newIndex;
  }
}

const togglePreview = () => {
  togglePreviewFunction();
  emit('toggleVersion');
};
const menuVisible = ref(false);
const submenu = ref(false);
const modalRef = ref(null)
onClickOutside(modalRef, (event) => {
  (menuVisible.value = false), (submenu.value = false);
});

// const getFileName = () => {
//   const currentFile = file[currentIndex.value];
//   if(currentFile.path){
//      const path = currentFile.path ;
//      return path.split('/').pop();
//   }
// };

const getFileName = () => {
  const currentFile = file?.[currentIndex.value]; // optional chaining for array access
  if (currentFile && typeof currentFile.path === 'string') {
    return currentFile.path.split('/').pop();
  }
  return ''; // or return null if you prefer
};



const getFileExtension = (file) => {
  if(!file.path){
    return 
  }
  const f_path = file.path ? file.path : file.name
  const parts = f_path.split('.');

  const extension = parts.pop();

  if (extension == 'jpg' || extension == 'jpeg' || extension == 'gif' || extension == 'png') {
    return encodeURI(page.props.base + file.path)
  }

  if (extension == 'pdf') {
    return encodeURI(page.props.base + '/images/pdf.svg')
  }

  if (extension == 'doc' || extension == 'doctx') {
    return encodeURI(page.props.base + '/images/word.svg')
  }

  if (extension == 'xlx' || extension == 'xlsx') {
    return encodeURI(page.props.base + '/images/excel.svg')
  }
}

const fileType = (file) => {
  if(!file.path){
    return
  }
  const f_path = file.path ? file.path : file.name
  const parts = f_path.split('.');

  const extension = parts.pop();

  if (extension == 'jpg' || extension == 'jpeg' || extension == 'gif' || extension == 'png') {
    return 'image'
  }

  if (extension == 'pdf') {
    return 'pdf'
  }

  if (extension == 'doc' || extension == 'doctx') {
    return 'word'
  }

  if (extension == 'xlx' || extension == 'xlsx') {
    return 'excel'
  }
}

const getExtension = (file_name) => {
  if (!file_name) return "";
  let file_arr = file_name.split(".");
  return file_arr.pop();
}

const getFileIcon = (fileName) => {
  const extension = getExtension(fileName)?.toLowerCase();
  
  switch (extension) {
    case "pdf":
      return "vscode-icons:file-type-pdf2";
    case "docx":
    case "doc":
      return "vscode-icons:file-type-word";
    case "xlsx":
    case "xls":
      return "vscode-icons:file-type-excel";
    case "ppt":
    case "pptx":
      return "vscode-icons:file-type-powerpoint";
    case "jpg":
    case "jpeg":
      return "vscode-icons:file-type-image";
    case "png":
      return "vscode-icons:file-type-image";
    default:
      return "material-icon-theme:pdf";
  }
};

function printCurrentFile() {
  const currentFile = file?.[currentIndex.value];
  if (!currentFile) return;

  const extension = getExtension(currentFile.path)?.toLowerCase();

  if (extension === "pdf") {
    // Open PDF in new window and print
    const pdfUrl = page.props.base + '/storage/' + currentFile.path;
    const win = window.open(pdfUrl, '_blank');
    if (win) {
      win.focus();
      win.print();
    }
  } else if (
    extension === "jpg" ||
    extension === "jpeg" ||
    extension === "png" ||
    extension === "gif"
  ) {
    // Open image in new window and print
    const imgUrl = page.props.base + currentFile.path;
    const win = window.open('', '_blank');
    if (win) {
      win.document.write(`<img src="${imgUrl}" style="width:100%">`);
      win.document.close();
      win.focus();
      win.print();
    }
  } else {
    // For other types, just download or show a message
    alert("Printing is only supported for PDF and image files.");
  }
}

function downloadCurrentFile() {
  const currentFile = file?.[currentIndex.value];
  if (!currentFile) return;

  const extension = getExtension(currentFile.path)?.toLowerCase();
  let fileUrl = "";

  if (extension === "pdf" || extension === "doc" || extension === "docx" || extension === "xls" || extension === "xlsx" || extension === "ppt" || extension === "pptx") {
    fileUrl = page.props.base + '/storage/' + currentFile.path;
  } else if (
    extension === "jpg" ||
    extension === "jpeg" ||
    extension === "png" ||
    extension === "gif"
  ) {
    fileUrl = page.props.base + currentFile.path;
  } else {
    fileUrl = page.props.base + '/storage/' + currentFile.path;
  }

  const link = document.createElement('a');
  link.href = fileUrl;
  link.download = getFileName();
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

</script>

<style>
.carousel-inner {
  display: flex;
  transition: transform 0.5s ease;
}

.carousel-item {
  flex: 0 0 100%;
}

.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 99999;
}
</style>