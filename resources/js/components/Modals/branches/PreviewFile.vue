<template>
  <div class="fixed bg-[#00000066] modal flex items-center flex-col">
    <div class="flex items-center px-10 mt-5 w-full justify-between">
      <div class=" flex items-center justify-between w-full lg:mr-28 mr-5">
        <div class="text-3xl text-white cursor-pointer" @click="togglePreview">
          <Icon icon="ic:round-arrow-back" />
        </div>

        <div class="flex gap-2 items-center bg-[#D0D5DD29] rounded-3xl py-1.5 px-5">
          <div class="bg-[#EBF3FE] p-2 rounded-full ">
            <Icon icon="vscode-icons:file-type-word" width="20" />
          </div>
          <p class="text-sm text-white">{{ file.name }}</p>
        </div>
      </div>
      <div class="text-white flex items-center gap-14 text-3xl">
        <Icon icon="material-symbols:add-comment-outline" />
        <Icon icon="ph:printer" />
        <Icon icon="material-symbols:download" />
        <div class=" relative">
          <Icon icon="simple-line-icons:options-vertical" class=" cursor-pointer" @click="toggleMenu" />
          <div class=" absolute z-50 right-2 top-8" v-if="menuVisible">
            <div class="w-[417px] h-[270px] relative ">
              <div
                class="dropmenu w-52 border rounded-lg text-[#1D2939] border-gray-300 text-lg bg-white absolute right-0 shadow-lg cursor-pointer"
                ref="modalRef">
                <div class="flex justify-between items-center hover:bg-[#F9FAFB] px-3 my-2"
                  v-for="(item, index) in menuItem" :key="index" @click="handleMenuItem(item)">
                  <div class="flex items-center gap-2">
                    <Icon :icon="item.icon" />
                    <p>{{ item.label }}</p>
                  </div>
                  <Icon :icon="item.arrow" />
                </div>
              </div>

              <div
                class="w-52 border rounded-lg text-[#1D2939] border-gray-300 text-lg bg-white absolute bottom-0 left-0 shadow-lg cursor-pointer"
                v-if="submenu" ref="modalRef">
                <div class="flex items-center gap-2 hover:bg-[#F9FAFB] px-3 my-2" v-for="(item, index) in submenuItem"
                  :key="index" @click="handleMenuItem(item)">
                  <Icon :icon="item.icon" />
                  <p>{{ item.label }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <button class=" text-lg py-1.5 rounded-md px-5  flex items-center gap-2 bg-[#2F68C5]">
          <Icon icon="lucide:share" />
          <p>Share</p>
        </button>
      </div>
    </div>
    <div class=" absolute bottom-5 flex items-center px-10 w-full justify-center h-[85vh]">
      <div class="text-2xl p-2 rounded-full bg-white cursor-pointer" :class="{ ' opacity-0': currentIndex === 0 }"
        @click="moveItems(-1)">
        <Icon icon="ph:arrow-left" />
      </div>
      <div>
        <div v-for="(item, index) in file.files" :key="index" class=" w-[50vw] h-[80vh]"
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
      <div v-if="file.files" class="text-2xl p-2 rounded-full bg-white cursor-pointer"
        :class="{ 'opacity-0': currentIndex === file.files.length - 1 }" @click="moveItems(1)">
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

const { togglePreviewFunction } = defineProps(["togglePreviewFunction", "file"]);
const page = usePage();
const emit = defineEmits(['toggleVersion']);

const previews = ref([

  {
    image: image1,
  },
  {
    image: image1,
  },
  {
    image: image1,
  },
  {
    image: image1,
  },
]);
const currentIndex = ref(0);
const previousIndex = ref(null);

function moveItems(step) {
  const newIndex = currentIndex.value + step;
  if (newIndex >= 0 && newIndex < previews.value.length) {
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

const getFileExtension = (file) => {
  const f_path = file.path ? file.path : file.name
  const parts = f_path.split('.');

  const extension = parts.pop();

  if (extension == 'jpg' || extension == 'jpeg' || extension == 'gif' || extension == 'png') {
    return encodeURI(page.props.base + '/storage' + file.path)
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

const menuItem = ref([
  {
    icon: "ic:outline-drive-file-rename-outline",
    label: "Rename",
  },
  {
    icon: "pajamas:duplicate",
    label: "Duplicate",
  },
  {
    icon: "ph:swap",
    label: "Change Format",
  },

  {
    icon: "ri:information-line",
    label: "File Information",
    arrow: "flowbite:angle-right-outline",
  },
]);

const submenuItem = ref([
  {
    icon: "ri:information-line",
    label: "Details",
  },
  {
    icon: "iconoir:activity",
    label: "Activity",
  },
  {
    icon: "pajamas:duplicate",
    label: "File Version",
  },
  {
    icon: "lucide:lock",
    label: "Lock",
  },
]);

const toggleMenu = () => {
  menuVisible.value = !menuVisible.value
};

const close = () => {
  menuVisible.value = false,
    submenu.value = false
}
const handleMenuItem = (item) => {
  if (item.label === "File Information") {
    menuVisible.value = true
    submenu.value = !submenu.value;
  }
  else {
    menuVisible.value = false;
    submenu.value = false;
  }
};
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