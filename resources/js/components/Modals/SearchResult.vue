<template>
  <div class="absolute w-[100vw] h-[100vh] bg-[#00000026] z-50 flex flex-col items-center">
    <div class="w-[50em] mt-[10vh]">
      <div class="bg-white rounded-lg overflow-hidden mb-5">
        <div class="flex items-center px-5">
          <div class="flex items-center w-full text-gray-400">
            <Icon icon="iconamoon:search-light" width="20" class="pi pi-search" />
            <input type="text" class="outline-none w-full p-3 border-0" placeholder="Search for documents,folder and branches" />
          </div>
          <button>
            <Icon icon="mdi:times" @click="toggleSearch" />
          </button>
        </div>
        <div class="bg-[#F9FAFB] px-5 border-t py-2 border-solid flex items-center justify-between">
          <div class="flex items-center gap-2">
            <h1>Navigate</h1>
            <button class="bg-white p-1 rounded-md">
              <Icon icon="tabler:arrow-up" />
            </button>
            <button class="bg-white p-1 rounded-md">
              <Icon icon="tabler:arrow-down" />
            </button>
          </div>

          <div class="flex items-center gap-2">
            <button @click="toggleSearch">Close</button>
            <button class="bg-white rounded-md px-2 py-0.5 shadow border">
              esc
            </button>
          </div>
        </div>
      </div>

      <div class="bg-white relative h-[70vh] rounded-lg overflow-hidden">
        <div class="flex items-center justify-between w-full border-b px-5 py-3">
          <div class="flex items-center gap-2">
            <div class="relative ">
              <button :class="{ 'bg-primary text-white': filterTypes !== 'Types' }"
                class="flex items-center gap-1 rounded-lg px-4 py-1 border"
                @click="(activeButton = 1), (openMenu = !openMenu)">
                <p>{{ filterTypes }}</p>
                <Icon v-if="filterTypes === 'Types'" icon="flowbite:angle-down-outline"
                  :class="{ 'rotate-180': activeButton === 1 && openMenu }" />
                <Icon icon="prime:times" width="20" @click="cancelFilter('type')" v-else />

              </button>


              <div class="bg-white left-0 py-2 absolute top-12 w-[200px] shadow-md rounded-md"
                v-if="activeButton === 1 && openMenu" ref="modalRef">

                <div v-for="(item, index) in Types" :key="index" class="flex items-center gap-3 py-1 hover:bg-gray-50 px-2"
                  @click="SelectOption(item.label)">
                  <Icon :icon="item.icon" width="25"
                    :class="{ 'text-red-600': item.label === 'PDFs', 'text-purple-600': item.label === 'Photos & Images', 'text-yellow-500': item.label === 'Audio', 'text-purple-700': item.label === 'Video' }" />
                  <h1 class="text-base">{{ item.label }}</h1>
                </div>
              </div>
            </div>

            <div class=" relative">
              <button class="flex  items-center gap-1 rounded-lg px-4 py-1 border"
                :class="{ 'bg-primary text-white': modifiedDate !== 'Date Modified' }"
                @click="(activeButton = 2), (openMenu = !openMenu)">
                <p>{{ modifiedDate }}</p>
                <Icon v-if="modifiedDate === 'Date Modified'" icon="flowbite:angle-down-outline"
                  :class="{ 'rotate-180': activeButton === 2 && openMenu }" />
                <Icon icon="prime:times" width="20" @click="cancelFilter('date')" v-else />

              </button>

              <div class="bg-white py-2 left-0  absolute top-12 w-[200px] shadow-md rounded-md"
                v-if="activeButton === 2 && openMenu" ref="modalRef">
                <div v-for="(item, index) in dateRanges" :key="index" class="hover:bg-gray-50 px-2"
                  @click="SelectOption(item.label)">
                  <div class="flex items-center gap-3 py-2">
                    <h1 class="text-base">{{ item.label }}</h1>
                  </div>
                </div>
                <div class="flex items-center px-2 cursor-pointer hover:bg-gray-50 gap-3 py-2">
                  <h1 class="text-base">Custom</h1>
                </div>
              </div>
            </div>
            <button class="flex items-center gap-1 rounded-lg px-4 py-1 border">
              <Icon icon="material-symbols-light:list" width="25" />
              <p>List</p>
            </button>
          </div>
          <div v-tooltip.top="{
            value: 'Clear Filter',
            pt: {
              arrow: {
                style: {
                  borderRightColor: '',
                },
              },
              text: 'bg-[#1D2939] text-sm text-white font-medium px-2 py-1 rounded',
            },
          }">
            <Icon icon="tabler:refresh" width="30" />
          </div>
        </div>

        <div class="px-5 pt-5 h-[60vh] pb-5 overflow-y-scroll hide_scroll">


          <!-- ================   Recent Searches    ========== -->
          <div v-if="RecentSearches.length > 0">
            <div class="flex items-center gap-2">
              <h1>Recent Searches</h1>
              <p class=" px-1.5 py-0.5 rounded-md text-xs bg-white border ">{{ RecentSearches.length }}</p>
            </div>

            <div v-for="(RecentSearch, index) in RecentSearches" :key="index">
              <div class="flex items-center justify-between hover:bg-gray-100 my-2">
                <div class="p-2  ">
                  <h1>{{ RecentSearch.searchQuery }}</h1>
                </div>
                <Icon icon="mi:delete-alt" width="25" />
              </div>
            </div>

            <div>
              <button class=" text-sm text-primary flex items-center gap-1" v-if="RecentSearches.length > 3">
                Show More
                <Icon icon="flowbite:angle-down-outline" />
              </button>
            </div>
          </div>


          <!-- ==============     Documents    ============= -->
          <div v-if="Documents.length > 0" class=" mt-5">
            <div class="flex items-center gap-2">
              <h1>Documents</h1>
              <p class=" px-1.5 py-0.5 rounded-md text-xs bg-white border ">{{ Documents.length }}</p>
            </div>

            <div v-for="(document, index) in visibleDocument" :key="index">
              <div class="flex items-center gap-3 my-2">
                <div class="p-2 rounded-full bg-gray-100">
                  <Icon :icon="document.icon" width="15 " />
                </div>
                <p class="text-sm font-medium">{{ document.name }}</p>
              </div>
            </div>

            <div>
              <button class=" text-sm text-primary flex items-center gap-1" v-if="shouldShowButton"
                @click="toggleShowAll">
                {{ showAll ? "Show Less " : "Show More" }}
                <Icon icon="flowbite:angle-up-outline" v-if="showAll" />
                <Icon icon="flowbite:angle-down-outline" v-else />
              </button>
            </div>
          </div>


          <!-- ==============     Folders    ============= -->
          <div class=" mt-5">
            <div class="flex items-center gap-2">
              <h1>Folders</h1>
              <p class=" px-1.5 py-0.5 rounded-md text-xs bg-white border ">3</p>
            </div>
            <div class="grid grid-cols-3   gap-2">
              <div v-for="i in 3" class="rounded-md flex items-center gap-3 hover:bg-gray-100 p-3" :key="i">
                <div class="border rounded-md p-2 bg-gray-100 cursor-pointer">
                  <img src="@/assets/images/folder.svg" alt="" />
                </div>

                <div>
                  <h1 class="text-base font-medium">Architecture Plan</h1>
                  <div class="flex items-center gap-3 mt-1">
                    <p>16 Files</p>
                    <div>
                      <p class=" text-xs"> FLD2103 </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>


          <!-- ==============     Branches    ============= -->
          <div v-if="Branches.length > 0" class=" mt-5">
            <div class="flex items-center gap-2">
              <h1>Branches</h1>
              <p class=" px-1.5 py-0.5 rounded-md text-xs bg-white border ">{{ Branches.length }}</p>
            </div>

            <div v-for="(Branche, index) in Branches" :key="index">
              <div class="flex items-center gap-3 my-2">
                <div class="p-2 rounded-full border ">
                  <Icon :icon="Branche.icon" width="15 " />
                </div>
                <p class="text-sm font-medium">{{ Branche.name }}</p>
              </div>
            </div>

            <div>
              <button class=" text-sm text-primary" v-if="Branches.length > 3">
                Show More
              </button>
            </div>
          </div>


        </div>

        <div class=" absolute bottom-0 w-full px-5 py-1 bg-gray-200 text-gray-500">
          <div class=" flex items-center justify-between">
            <p>Type "/" for tags</p>
            <p>14 Results</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, defineProps } from "vue";
import { onClickOutside } from "@vueuse/core";
const activeButton = ref(1);
const date = ref(false);
const { toggleSearchFunction } = defineProps(["toggleSearchFunction"]);
const toggleSearch = () => {
  toggleSearchFunction();
};

const openMenu = ref(false);
const modalRef = ref(null);
onClickOutside(modalRef, (event) => {
  openMenu.value = false;
});
const filterTypes = ref("Types")
const modifiedDate = ref("Date Modified")

const Types = [
  {
    icon: 'circum:filter',
    label: 'All'
  },
  {
    icon: 'mdi:file-pdf-box',
    label: 'PDFs'
  },
  {
    icon: 'ic:baseline-photo',
    label: 'Photos & Images'
  },
  {
    icon: 'vscode-icons:file-type-word',
    label: 'Word Document'
  },
  {
    icon: 'vscode-icons:file-type-excel',
    label: 'Excel'
  },
  {
    icon: 'vscode-icons:file-type-powerpoint',
    label: 'Powerpoint'
  },
  {
    icon: 'ic:baseline-audio-file',
    label: 'Audio'
  },
  {
    icon: 'entypo:video',
    label: 'Video'
  },
  {
    icon: 'ion:folder',
    label: 'Folders'
  },
  {
    icon: 'mdi:database-outline',
    label: 'Branches'
  }
]

const SelectOption = (label) => {
  if (activeButton.value === 1) {

    filterTypes.value = label
  }
  if (activeButton.value === 2) {
    modifiedDate.value = label
  }
  openMenu.value = false
}

const cancelFilter = (filter) => {
  if (filter === 'type') {

    filterTypes.value = 'Types';
  }
  if (filter === 'date') {
    modifiedDate.value = 'Date Modified'
  }
  openMenu.value = false
}

const dateRanges = [
  {
    label: "Today",
  },
  {
    label: "Yesterday",
  },
  {
    label: "Last 7 days",
  },
  {
    label: "Last 30 days",
  },
  {
    label: "Last 90 days",
  }
];

const Documents = ref([
  {
    icon: "vscode-icons:file-type-excel",
    name: "Agreement contract with FG on housing.docx",
  },
  {
    icon: "vscode-icons:file-type-word",
    name: "Agreement contract with FG on housing.docx",
  },
  {
    icon: "vscode-icons:file-type-powerpoint",
    name: "Agreement contract with FG on housing.docx",
  },
  {
    icon: "vscode-icons:file-type-excel",
    name: "Agreement contract with FG on housing.docx",
  },
  {
    icon: "vscode-icons:file-type-word",
    name: "Agreement contract with FG on housing.docx",
  },
  {
    icon: "vscode-icons:file-type-powerpoint",
    name: "Agreement contract with FG on housing.docx",
  },
  {
    icon: "vscode-icons:file-type-excel",
    name: "Agreement contract with FG on housing.docx",
  },
  {
    icon: "vscode-icons:file-type-word",
    name: "Agreement contract with FG on housing.docx",
  },
  {
    icon: "vscode-icons:file-type-powerpoint",
    name: "Agreement contract with FG on housing.docx",
  },
]);

const RecentSearches = ref([
  {
    searchQuery: 'Compliance Document Signed by minister for 2024- V1 '
  },
  {
    searchQuery: 'Compliance Document Signed by minister for 2024- V1 '
  },
  {
    searchQuery: 'Compliance Document Signed by minister for 2024- V1 '
  }
])




const Branches = ref([
  {
    icon: 'gg:organisation',
    name: 'Public Administration Department'
  },
  {
    icon: 'icon-park-twotone:fork',
    name: 'Public Relations Division'
  },
  {
    icon: 'bi:box',
    name: 'Public Health Awareness Division'
  },
])
const showAll = ref(false);

const visibleDocument = computed(() => {
  return showAll.value ? Documents.value : Documents.value.slice(0, 3);
});

const shouldShowButton = computed(() => {
  return Documents.value.length > 3;
});

const toggleShowAll = () => {
  showAll.value = !showAll.value;
};
</script>
<style scoped>
.custom-dialog .p-dialog-header {
  display: none;
  /* Hide the header */
}
</style>