<template>

    <table class="w-full text-left">
      <thead class=" bg-gray-50">
        <tr class="border text-[#667085]">
          <th class="p-3 pl-5 w-[3%]">
            #
          </th>
          <th class="w-[27%]">Title</th>
          <th class="w-[50%]">Description</th>
          <th class="w-[10%]">Amount</th>
          <th class="w-[10%]">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr class="border" v-for="(file, key) in filteredFiles">
          <td class="p-3 px-2 pl-5 py-3"> {{ continuousPageNumbering(key + 1) }}</td>
          <td class="py-3 px-2">
            <div class="flex items-center font-medium gap-2">
              <div class="p-2 rounded-full bg-gray-200">
                <!-- <Icon icon="material-symbols-light:folder-outline" width="20" color="blue" /> -->
                <img :src="getFileExtension(file)" alt="" style="width: 20px; max-width: none;">
              </div>
              {{ file.title?.archive }}
            </div>
          </td>
  
          <td class="px-2">{{ truncateText(file.description?.archive) }}</td>
          <td class="py-3 px-2">{{ file.amount?.archive }}</td>
  
          <td class="py-3 px-2">
            <div class="flex items-center gap-4">
              <div @click="toggle2($event, file, key)" aria-haspopup="true" aria-controls="overlay_menu">
                <div class="flex items-center font-normal h-full border border-gray-300 rounded-lg">
                  <div>
                    <div class="h-full">
                      <Icon icon="majesticons:more-menu-vertical" width="30" />
                    </div>
                    <TieredMenu ref="menu2" id="overlay_menu" :model="menuItems" popup>
                      <template #item="{ item, props, hasSubmenu }">
  
                        <a class="flex align-location-center" v-bind="props.action">
                          <!-- <span :class="item.icon" /> -->
                          <span v-html="item.icon"></span>
                          <span class="ml-2" @click="handleItemClick(item)">{{ item.label }}</span>
                          <!-- <Badge v-if="item.badge" class="ml-auto" :value="item.badge" /> -->
                          <i v-if="hasSubmenu" class="pi pi-angle-right ml-auto text-primary"></i>
                        </a>
                        <div v-if="hasSubmenu" class="submenu">
                          <TieredMenu :model="item.submenuItems" :popup="true"></TieredMenu>
                        </div>
                      </template>
                    </TieredMenu>
                  </div>
                </div>
              </div>
  
              <div class="cursor-pointer pr-4" @click="deleteArchive(file)">
                <Icon icon="uiw:delete" width="16" />
              </div>
            </div>
  
          </td>
        </tr>
      </tbody>
    </table>
    <!-- <div class="w-full pt-8 pb-4">
      <div class="flex">
        <div class="flex pl-6" v-if="props.archives && props.archives?.first_page_url && props.archives.links[0].url">
          <Link :href="props.archives.first_page_url">
            <svg width="86" class=" cursor-pointer" height="20" viewBox="0 0 86 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M15.8333 9.99935H4.16663M4.16663 9.99935L9.99996 15.8327M4.16663 9.99935L9.99996 4.16602" stroke="#667085" stroke-width="1.67" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M29.1186 15V4.81818H32.7479C33.54 4.81818 34.1963 4.96236 34.7166 5.25071C35.237 5.53906 35.6264 5.93347 35.8849 6.43395C36.1435 6.93111 36.2727 7.49124 36.2727 8.11435C36.2727 8.74077 36.1418 9.30421 35.88 9.80469C35.6214 10.3018 35.2304 10.6963 34.7067 10.9879C34.1863 11.2763 33.5317 11.4205 32.7429 11.4205H30.2472V10.1179H32.6037C33.1042 10.1179 33.5102 10.0317 33.8217 9.85938C34.1333 9.68371 34.362 9.44508 34.5078 9.14347C34.6536 8.84186 34.7266 8.49882 34.7266 8.11435C34.7266 7.72988 34.6536 7.38849 34.5078 7.0902C34.362 6.7919 34.1316 6.55824 33.8168 6.3892C33.5052 6.22017 33.0942 6.13565 32.5838 6.13565H30.6548V15H29.1186ZM37.9544 15V7.36364H39.3912V8.5767H39.4707C39.6099 8.16572 39.8552 7.84257 40.2065 7.60724C40.5611 7.36861 40.9622 7.24929 41.4096 7.24929C41.5024 7.24929 41.6118 7.2526 41.7377 7.25923C41.867 7.26586 41.9681 7.27415 42.041 7.28409V8.70597C41.9814 8.68939 41.8753 8.67116 41.7228 8.65128C41.5704 8.62808 41.4179 8.61648 41.2654 8.61648C40.9141 8.61648 40.6009 8.69105 40.3258 8.8402C40.054 8.98603 39.8386 9.18987 39.6795 9.4517C39.5204 9.71023 39.4409 10.0052 39.4409 10.3366V15H37.9544ZM46.3452 15.1541C45.5928 15.1541 44.9448 14.9934 44.4013 14.6719C43.861 14.3471 43.4434 13.8913 43.1484 13.3047C42.8568 12.7147 42.7109 12.0237 42.7109 11.2315C42.7109 10.4493 42.8568 9.75994 43.1484 9.16335C43.4434 8.56676 43.8544 8.10109 44.3814 7.76633C44.9117 7.43158 45.5315 7.2642 46.2408 7.2642C46.6716 7.2642 47.0893 7.33546 47.4936 7.47798C47.898 7.6205 48.2609 7.84422 48.5824 8.14915C48.9039 8.45407 49.1574 8.85014 49.343 9.33736C49.5286 9.82126 49.6214 10.4096 49.6214 11.1023V11.6293H43.5511V10.5156H48.1648C48.1648 10.1245 48.0852 9.77817 47.9261 9.47656C47.767 9.17164 47.5433 8.93134 47.255 8.75568C46.9699 8.58002 46.6352 8.49219 46.2507 8.49219C45.8331 8.49219 45.4685 8.59493 45.157 8.80043C44.8487 9.0026 44.6101 9.26776 44.4411 9.59588C44.2753 9.92069 44.1925 10.2737 44.1925 10.6548V11.5249C44.1925 12.0353 44.282 12.4695 44.4609 12.8274C44.6432 13.1854 44.8968 13.4588 45.2216 13.6477C45.5464 13.8333 45.9259 13.9261 46.3601 13.9261C46.6418 13.9261 46.8987 13.8864 47.1307 13.8068C47.3627 13.724 47.5632 13.6013 47.7322 13.4389C47.9013 13.2765 48.0305 13.076 48.12 12.8374L49.527 13.0909C49.4143 13.5052 49.2121 13.8681 48.9205 14.1797C48.6321 14.4879 48.2692 14.7282 47.8317 14.9006C47.3975 15.0696 46.902 15.1541 46.3452 15.1541ZM57.5847 7.36364L54.8155 15H53.2246L50.4505 7.36364H52.0463L53.9803 13.2401H54.0598L55.9888 7.36364H57.5847ZM58.9817 15V7.36364H60.4682V15H58.9817ZM59.7324 6.18537C59.4739 6.18537 59.2518 6.09919 59.0662 5.92685C58.8839 5.75118 58.7928 5.54238 58.7928 5.30043C58.7928 5.05516 58.8839 4.84635 59.0662 4.67401C59.2518 4.49834 59.4739 4.41051 59.7324 4.41051C59.9909 4.41051 60.2114 4.49834 60.3936 4.67401C60.5792 4.84635 60.6721 5.05516 60.6721 5.30043C60.6721 5.54238 60.5792 5.75118 60.3936 5.92685C60.2114 6.09919 59.9909 6.18537 59.7324 6.18537ZM65.6847 15.1541C64.9688 15.1541 64.344 14.9901 63.8104 14.6619C63.2768 14.3338 62.8625 13.8748 62.5675 13.2848C62.2725 12.6948 62.125 12.0054 62.125 11.2166C62.125 10.4245 62.2725 9.73177 62.5675 9.13849C62.8625 8.54522 63.2768 8.08452 63.8104 7.75639C64.344 7.42827 64.9688 7.2642 65.6847 7.2642C66.4006 7.2642 67.0253 7.42827 67.5589 7.75639C68.0926 8.08452 68.5069 8.54522 68.8018 9.13849C69.0968 9.73177 69.2443 10.4245 69.2443 11.2166C69.2443 12.0054 69.0968 12.6948 68.8018 13.2848C68.5069 13.8748 68.0926 14.3338 67.5589 14.6619C67.0253 14.9901 66.4006 15.1541 65.6847 15.1541ZM65.6896 13.9062C66.1536 13.9062 66.5381 13.7836 66.843 13.5384C67.148 13.2931 67.3733 12.9666 67.5192 12.5589C67.6683 12.1513 67.7429 11.7022 67.7429 11.2116C67.7429 10.7244 67.6683 10.277 67.5192 9.86932C67.3733 9.45833 67.148 9.12855 66.843 8.87997C66.5381 8.63139 66.1536 8.5071 65.6896 8.5071C65.2223 8.5071 64.8345 8.63139 64.5263 8.87997C64.2214 9.12855 63.9943 9.45833 63.8452 9.86932C63.6993 10.277 63.6264 10.7244 63.6264 11.2116C63.6264 11.7022 63.6993 12.1513 63.8452 12.5589C63.9943 12.9666 64.2214 13.2931 64.5263 13.5384C64.8345 13.7836 65.2223 13.9062 65.6896 13.9062ZM75.7409 11.8331V7.36364H77.2324V15H75.7708V13.6776H75.6912C75.5156 14.0852 75.2338 14.425 74.8461 14.6967C74.4616 14.9652 73.9827 15.0994 73.4093 15.0994C72.9187 15.0994 72.4846 14.9917 72.1067 14.7763C71.7322 14.5575 71.4372 14.2344 71.2218 13.8068C71.0096 13.3793 70.9036 12.8506 70.9036 12.2209V7.36364H72.3901V12.0419C72.3901 12.5623 72.5343 12.9766 72.8226 13.2848C73.111 13.593 73.4855 13.7472 73.9462 13.7472C74.2246 13.7472 74.5014 13.6776 74.7765 13.5384C75.0549 13.3991 75.2852 13.1887 75.4675 12.907C75.6531 12.6252 75.7443 12.2673 75.7409 11.8331ZM84.9521 9.22798L83.6048 9.46662C83.5484 9.29427 83.4589 9.13021 83.3363 8.97443C83.217 8.81865 83.0546 8.69105 82.8491 8.59162C82.6436 8.49219 82.3867 8.44247 82.0785 8.44247C81.6576 8.44247 81.3062 8.53693 81.0245 8.72585C80.7428 8.91146 80.6019 9.15175 80.6019 9.44673C80.6019 9.70194 80.6964 9.90743 80.8853 10.0632C81.0742 10.219 81.3791 10.3466 81.8001 10.446L83.0131 10.7244C83.7158 10.8868 84.2395 11.1371 84.5842 11.4751C84.9289 11.8132 85.1012 12.2524 85.1012 12.7926C85.1012 13.25 84.9686 13.6577 84.7035 14.0156C84.4416 14.3703 84.0754 14.6487 83.6048 14.8509C83.1374 15.053 82.5955 15.1541 81.979 15.1541C81.1239 15.1541 80.4263 14.9718 79.886 14.6072C79.3458 14.2393 79.0143 13.7173 78.8917 13.0412L80.3285 12.8224C80.418 13.197 80.6019 13.4804 80.8803 13.6726C81.1587 13.8615 81.5217 13.956 81.9691 13.956C82.4563 13.956 82.8458 13.8549 83.1374 13.6527C83.4291 13.4472 83.5749 13.197 83.5749 12.902C83.5749 12.6634 83.4854 12.4628 83.3065 12.3004C83.1308 12.138 82.8607 12.0154 82.4961 11.9325L81.2035 11.6491C80.4909 11.4867 79.9639 11.2282 79.6225 10.8736C79.2844 10.5189 79.1154 10.0698 79.1154 9.52628C79.1154 9.07552 79.2414 8.68111 79.4933 8.34304C79.7451 8.00497 80.0932 7.74148 80.5373 7.55256C80.9814 7.36032 81.4902 7.2642 82.0636 7.2642C82.8888 7.2642 83.5385 7.44318 84.0124 7.80114C84.4864 8.15578 84.7996 8.63139 84.9521 9.22798Z" fill="#667085"/>
            </svg>
          </Link>
  
        </div>
  
        <div class="mx-auto flex" v-if="links && links.length > 0">
          <template v-for="link in links">
            <template v-if="link.url  && link.label !='&laquo; Previous' && link.label !='Next &raquo;'">
              <Link :href="link.url"><span class="w-[30px] h-[30px] rounded-full text-[#0D6E1A]  flex items-end justify-center p-1 cursor-pointer mx-1" :class="link.active? 'bg-[#b5d1b0]':'bg-[#E7F2E8]'">{{ link.label }}</span></Link>
            </template>
          </template>
        </div>
  
        <div class="flex pr-10" v-if="props.archives && props.archives.next_page_url && props.archives.links[props.archives.links.length -1].url">
          <Link :href="props.archives.next_page_url">
            <svg width="60" height="20" class=" cursor-pointer" viewBox="0 0 60 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M9.33665 4.81818V15H7.92472L2.74929 7.53267H2.65483V15H1.11861V4.81818H2.54048L7.72088 12.2955H7.81534V4.81818H9.33665ZM14.7495 15.1541C13.9971 15.1541 13.3491 14.9934 12.8056 14.6719C12.2653 14.3471 11.8477 13.8913 11.5527 13.3047C11.2611 12.7147 11.1152 12.0237 11.1152 11.2315C11.1152 10.4493 11.2611 9.75994 11.5527 9.16335C11.8477 8.56676 12.2587 8.10109 12.7857 7.76633C13.316 7.43158 13.9358 7.2642 14.6451 7.2642C15.0759 7.2642 15.4935 7.33546 15.8979 7.47798C16.3023 7.6205 16.6652 7.84422 16.9867 8.14915C17.3082 8.45407 17.5617 8.85014 17.7473 9.33736C17.9329 9.82126 18.0257 10.4096 18.0257 11.1023V11.6293H11.9554V10.5156H16.5691C16.5691 10.1245 16.4895 9.77817 16.3304 9.47656C16.1713 9.17164 15.9476 8.93134 15.6593 8.75568C15.3742 8.58002 15.0395 8.49219 14.655 8.49219C14.2374 8.49219 13.8728 8.59493 13.5613 8.80043C13.253 9.0026 13.0144 9.26776 12.8453 9.59588C12.6796 9.92069 12.5968 10.2737 12.5968 10.6548V11.5249C12.5968 12.0353 12.6863 12.4695 12.8652 12.8274C13.0475 13.1854 13.3011 13.4588 13.6259 13.6477C13.9507 13.8333 14.3302 13.9261 14.7644 13.9261C15.0461 13.9261 15.303 13.8864 15.535 13.8068C15.767 13.724 15.9675 13.6013 16.1365 13.4389C16.3056 13.2765 16.4348 13.076 16.5243 12.8374L17.9313 13.0909C17.8186 13.5052 17.6164 13.8681 17.3248 14.1797C17.0364 14.4879 16.6735 14.7282 16.236 14.9006C15.8018 15.0696 15.3063 15.1541 14.7495 15.1541ZM20.6706 7.36364L22.356 10.3366L24.0563 7.36364H25.682L23.3006 11.1818L25.7019 15H24.0762L22.356 12.1463L20.6408 15H19.0101L21.3865 11.1818L19.04 7.36364H20.6706ZM30.7729 7.36364V8.55682H26.6017V7.36364H30.7729ZM27.7203 5.53409H29.2069V12.7578C29.2069 13.0462 29.2499 13.2633 29.3361 13.4091C29.4223 13.5516 29.5333 13.6494 29.6692 13.7024C29.8084 13.7521 29.9592 13.777 30.1216 13.777C30.2409 13.777 30.3453 13.7687 30.4348 13.7521C30.5243 13.7356 30.5939 13.7223 30.6436 13.7124L30.9121 14.9403C30.8259 14.9735 30.7033 15.0066 30.5442 15.0398C30.3851 15.0762 30.1863 15.0961 29.9476 15.0994C29.5565 15.1061 29.1919 15.0365 28.8539 14.8906C28.5158 14.7448 28.2424 14.5194 28.0336 14.2145C27.8248 13.9096 27.7203 13.5268 27.7203 13.0661V5.53409Z" fill="#667085"/>
            <path d="M44.1667 9.99935H55.8334M55.8334 9.99935L50.0001 4.16602M55.8334 9.99935L50.0001 15.8327" stroke="#667085" stroke-width="1.67" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </Link>
        </div>
      </div>
    </div> -->
  
    <pagination :archives="props.archives"/>
  
  
  
  
    <PreviewFile v-if="togglePreview" :togglePreviewFunction="togglePreviewState" :file="selectedArchive" />
   
  </template>
  
  <script setup>
  import { ref, defineProps, watch, defineEmits, computed } from "vue";
  import { router, usePage } from '@inertiajs/vue3';
  import PreviewFile from "../../../components/Modals/branches/PreviewFile.vue";
  import {Link} from '@inertiajs/vue3'
  import pagination from "./pagination.vue";
  import debounce from "lodash.debounce";
  
  
  const props = defineProps(['files', 'documentsGridView', 'archives', 'search','sort',  'branch_type','branch_id', 'currentPage', 'perPage']);

  const emit = defineEmits(['docs', 'fileRename', 'lock', 'changeFormat', 'downloadFile', 'selectedArchive', 'branchDetails',
  'editMetadata', 'addMetadata', 'fileActivity', 'fileVersion', 'lockDocs', 'share', 'folder_id']);
  const selectedDocument = ref()
  const menu2 = ref();
  const selectedArchiveId = ref();
  const selectedArchive = ref();
  const togglePreview = ref(false);
  const toggleMetadata = ref(false);
  const itemsDisplayed = ref(0);
  const maxLength = ref(29);
  const expandedIndices = ref([]);
  const searchQuery = ref(props.search || '');
  
  const page = usePage();
  
  
  const togglePreviewState = () => {
    togglePreview.value = !togglePreview.value;
  };
  
  const continuousPageNumbering = (number)=>{
    if(props.archives){
      let currentPage = props.currentPage;
      let perPage = props.perPage;
      let sum = (currentPage - 1) * perPage;
      return (sum + number)
    }
  }
  
  const getFileExtension = (file)=>{
      const f_path = file.path? file.path : file?.name
      
      if(f_path){
         const parts = f_path.split('.');
  
        const extension = parts.pop();
  
        if(extension == 'jpg' || extension == 'jpeg' || extension == 'gif' || extension == 'png'){
            return page.props.base+'/images/image.svg'
        }
        
        if(extension == 'pdf'){
            return page.props.base+'/images/pdf.svg'
        }
  
        if(extension == 'doc' || extension == 'doctx'){
            return page.props.base+'/images/word.svg'
        }
  
        if(extension == 'xlx' || extension == 'xlsx'){
            return page.props.base+'/images/excel.svg'
        }
      }
     
  
  }
  
  const truncateText = (text)=>{
      let max = 100
      if (text && text.length > max) {
          return text.substring(0, max) + '...';
        }else{
          return text
        }
  }
  
  const menuItems = ref([
    {
      label: 'Preview',
      icon: ` <svg width="20" height="16" viewBox="0 0 20 16" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M0.833344 8.00065C0.833344 8.00065 4.16668 1.33398 10 1.33398C15.8333 1.33398 19.1667 8.00065 19.1667 8.00065C19.1667 8.00065 15.8333 14.6673 10 14.6673C4.16668 14.6673 0.833344 8.00065 0.833344 8.00065Z" stroke="#1D2939" stroke-width="1.67" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M10 10.5007C11.3807 10.5007 12.5 9.38136 12.5 8.00065C12.5 6.61994 11.3807 5.50065 10 5.50065C8.6193 5.50065 7.50001 6.61994 7.50001 8.00065C7.50001 9.38136 8.6193 10.5007 10 10.5007Z" stroke="#1D2939" stroke-width="1.67" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>` 
      ,
      command: () => {
        togglePreview.value = true;
      }
    },
    {
      label: 'Download',
      icon: `<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M16.5 11.5V14.8333C16.5 15.2754 16.3244 15.6993 16.0118 16.0118C15.6993 16.3244 15.2754 16.5 14.8333 16.5H3.16667C2.72464 16.5 2.30072 16.3244 1.98816 16.0118C1.67559 15.6993 1.5 15.2754 1.5 14.8333V11.5M4.83333 7.33333L9 11.5M9 11.5L13.1667 7.33333M9 11.5V1.5" stroke="#101828" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      `,
      command: () => {
        downloadFile()
      }
    },
   
    // {
    //   label: 'Share',
    //   icon: `<svg width="18" height="20" viewBox="0 0 18 20" fill="none" xmlns="http://www.w3.org/2000/svg">
    //   <path d="M6.15833 11.2577L11.85 14.5743M11.8417 5.42435L6.15833 8.74102M16.5 4.16602C16.5 5.54673 15.3807 6.66602 14 6.66602C12.6193 6.66602 11.5 5.54673 11.5 4.16602C11.5 2.7853 12.6193 1.66602 14 1.66602C15.3807 1.66602 16.5 2.7853 16.5 4.16602ZM6.5 9.99935C6.5 11.3801 5.38071 12.4993 4 12.4993C2.61929 12.4993 1.5 11.3801 1.5 9.99935C1.5 8.61864 2.61929 7.49935 4 7.49935C5.38071 7.49935 6.5 8.61864 6.5 9.99935ZM16.5 15.8327C16.5 17.2134 15.3807 18.3327 14 18.3327C12.6193 18.3327 11.5 17.2134 11.5 15.8327C11.5 14.452 12.6193 13.3327 14 13.3327C15.3807 13.3327 16.5 14.452 16.5 15.8327Z" stroke="#1D2939" stroke-width="1.67" stroke-linecap="round" stroke-linejoin="round"/>
    //   </svg>
    //   `,
    //   command: ()=>{
    //     emit('share');
    //   }
    // },
    // {
    //   label: 'Change Format',
    //   icon:  `<svg width="17" height="19" viewBox="0 0 17 19" fill="none" xmlns="http://www.w3.org/2000/svg">
    //   <path d="M2.47938 0.404297C1.91143 0.404297 1.36674 0.629918 0.965139 1.03153C0.563538 1.43314 0.337921 1.97784 0.337921 2.5458V4.92524C0.337921 5.20647 0.393311 5.48494 0.500929 5.74476C0.608548 6.00457 0.766286 6.24065 0.965139 6.43951C1.36674 6.84112 1.91143 7.06674 2.47938 7.06674H11.5211C11.8023 7.06674 12.0808 7.01135 12.3406 6.90373C12.6004 6.79611 12.8365 6.63837 13.0353 6.43951C13.2342 6.24065 13.3919 6.00457 13.4995 5.74476C13.6071 5.48494 13.6625 5.20647 13.6625 4.92524V2.5458C13.6625 1.97784 13.4369 1.43314 13.0353 1.03153C12.6337 0.629918 12.089 0.404297 11.5211 0.404297H2.47938ZM1.76556 2.5458C1.76556 2.35648 1.84076 2.17491 1.97463 2.04104C2.1085 1.90717 2.29006 1.83196 2.47938 1.83196H11.5211C11.7104 1.83196 11.892 1.90717 12.0258 2.04104C12.1597 2.17491 12.2349 2.35648 12.2349 2.5458V4.92524C12.2349 5.11456 12.1597 5.29613 12.0258 5.43C11.892 5.56387 11.7104 5.63907 11.5211 5.63907H2.47938C2.29006 5.63907 2.1085 5.56387 1.97463 5.43C1.84076 5.29613 1.76556 5.11456 1.76556 4.92524V2.5458ZM4.81118 12.7774C4.44285 12.7774 4.14495 13.0972 4.14495 13.4912C4.14495 13.8853 4.44285 14.2051 4.81118 14.2051H9.18927C9.5576 14.2051 9.8555 13.8853 9.8555 13.4912C9.8555 13.0972 9.5576 12.7774 9.18927 12.7774H4.81118ZM13.0467 13.2009L12.9773 13.2809C12.8564 13.4018 12.6962 13.4753 12.5257 13.488C12.3552 13.5008 12.1859 13.4519 12.0483 13.3504L11.9684 13.2809L10.0649 11.3773C9.944 11.2565 9.87049 11.0962 9.85774 10.9258C9.84499 10.7553 9.89384 10.5859 9.99541 10.4484L10.0649 10.3685L11.9684 8.4649C12.0959 8.33836 12.2665 8.26456 12.4461 8.25823C12.6256 8.25191 12.8009 8.31353 12.9371 8.43079C13.0732 8.54805 13.1601 8.71233 13.1804 8.89084C13.2007 9.06935 13.153 9.24897 13.0467 9.39384L12.9773 9.47379L12.293 10.16H13.7206C14.015 10.1599 14.2988 10.0507 14.5173 9.85344C14.7358 9.6562 14.8735 9.38495 14.9036 9.09212L14.9103 8.97029V6.8288C14.9103 6.64794 14.979 6.47384 15.1025 6.34168C15.226 6.20953 15.395 6.12917 15.5754 6.11684C15.7559 6.10451 15.9343 6.16114 16.0746 6.27527C16.2149 6.38941 16.3066 6.55254 16.3313 6.73171L16.3379 6.8288V8.97029C16.3379 9.63697 16.0836 10.2785 15.6267 10.764C15.1699 11.2496 14.545 11.5424 13.8795 11.5829L13.7206 11.5877H12.2939L12.9773 12.272C13.0982 12.3929 13.1717 12.5531 13.1844 12.7236C13.1972 12.8941 13.1483 13.0634 13.0467 13.2009ZM13.6625 15.8707V13.9424L13.6502 13.9538C13.2647 14.3402 12.7365 14.4963 12.2349 14.4249V15.8707C12.2349 16.06 12.1597 16.2416 12.0258 16.3754C11.892 16.5093 11.7104 16.5845 11.5211 16.5845H2.47938C2.29006 16.5845 2.1085 16.5093 1.97463 16.3754C1.84076 16.2416 1.76556 16.06 1.76556 15.8707V11.1118C1.76556 10.9225 1.84076 10.7409 1.97463 10.607C2.1085 10.4732 2.29006 10.398 2.47938 10.398H8.97322C9.02652 10.2152 9.11313 10.0401 9.23019 9.88115L9.25208 9.85259L9.36629 9.71934L10.1163 8.97029H2.47938C1.91143 8.97029 1.36674 9.19592 0.965139 9.59753C0.563538 9.99914 0.337921 10.5438 0.337921 11.1118V15.8707C0.337921 16.4386 0.563538 16.9833 0.965139 17.385C1.36674 17.7866 1.91143 18.0122 2.47938 18.0122H11.5211C12.089 18.0122 12.6337 17.7866 13.0353 17.385C13.4369 16.9833 13.6625 16.4386 13.6625 15.8707Z" fill="#1D2939"/>
    //   </svg>
    //   `
    // },


    // {
    //   label: 'File Information',
    //   icon: `<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
    //   <path d="M9.99999 13.3327V9.99935M9.99999 6.66602H10.0083M18.3333 9.99935C18.3333 14.6017 14.6024 18.3327 9.99999 18.3327C5.39762 18.3327 1.66666 14.6017 1.66666 9.99935C1.66666 5.39698 5.39762 1.66602 9.99999 1.66602C14.6024 1.66602 18.3333 5.39698 18.3333 9.99935Z" stroke="#1D2939" stroke-width="1.67" stroke-linecap="round" stroke-linejoin="round"/>
    //   </svg>
    //   `,
    //   items: [
    //     {
    //       label: "Details",
    //       icon: `<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
    //   <path d="M9.99999 13.3327V9.99935M9.99999 6.66602H10.0083M18.3333 9.99935C18.3333 14.6017 14.6024 18.3327 9.99999 18.3327C5.39762 18.3327 1.66666 14.6017 1.66666 9.99935C1.66666 5.39698 5.39762 1.66602 9.99999 1.66602C14.6024 1.66602 18.3333 5.39698 18.3333 9.99935Z" stroke="#1D2939" stroke-width="1.67" stroke-linecap="round" stroke-linejoin="round"/>
    //   </svg>
    //   `,
    //       command: () => {
    //         emit('branchDetails', true);
    //       }
    //     },
       
    //   ]
    // },
  ]);
  
  const toggle2 = (event, data, index) => {
    selectedArchiveId.value = data.id;
    selectedArchive.value = data
    menu2.value[index].toggle(event);
    console.log(selectedArchive)
    emit('selectedArchive', selectedArchive);
  };
  
  
  const handleItemClick = (item) => {
    if (item.label === 'Lock') {
      //LockDocumentModal.value = true
      emit('lock', true);
    }
    if (item.label === 'Change Format') {
      //ChangeFormatModal.value = true
      emit('changeFormat', true);
    }
  }
  
  
  const downloadFile = async () => {
    try {
      const response = await axios.get('/download-file', {
        params: { id: selectedArchiveId.value },
        responseType: 'blob'
      });
  
      // Extract the filename from the Content-Disposition header
      const contentDisposition = response.headers['content-disposition'];
      const fileName = contentDisposition.split('filename=')[1].replace(/"/g, '').trim();
  
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
      //link.setAttribute('download', encodeURIComponent(fileName));
  
      document.body.appendChild(link);
      link.click();
  
      // Cleanup by revoking the object URL
      window.URL.revokeObjectURL(url);
    } catch (error) {
      console.error('Error downloading file:', error);
    }
  };
  
  const links = computed(()=>{
    if(props.archives && props.archives.links){
      let link = [...props.archives.links]
      link.pop();
      link.shift();
      return link;
    }
  })
  
  const searchFile = async () => {
    let url = '';
    // if(!page.props.roles.includes('data entry')){
    //   url = `/dashboard/folder-view/${props.branch_type}/${props.folder_id}/${props.branch_id}`
    // }else{
    //   url = `/edms/folder-view/${props.branch_type}/${props.folder_id}`
    // }
    //     await router.get(url, { search: searchQuery.value }, {
    //       onSuccess: (page) => {
    //         // handle success
    //       },
    //       preserveState: true,
    //     });
      };
  
      const debouncedFile = debounce(searchFile, 500);
  
      watch(() => props.search, (newSearch) => {
        searchQuery.value = newSearch;
        debouncedFile();
      });
  
      const filteredFiles = computed(() => {
        
        if (!props.archives || !searchQuery.value) {
          return props.files || [];
        }
  
        const search = searchQuery.value.toLowerCase();
        // return props.archives.data.filter(file => {
        //   // return (
        //   //   file.title.toLowerCase().includes(search) ||
        //   //   file.amount.toLowerCase().includes(search) ||
        //   //   file.description.toLowerCase().includes(search)
        //   // );
        // });
  
        let newarr = []
  
        for (let file of props.archives.data) {
        let arr = {
          name: getArchiveDocsVale(file.archive_docs, 'title') ? getArchiveDocsVale(file.archive_docs, 'title')[0].archive : file?.file_path[0]?.file_name,
          size: file?.file_path[0]?.file_size,
          date: formatDate(file.updated_at),
          uploaded_by: file?.file_path[0]?.updated_by_name,
          //path: (file.file_path && file.file_path.length > 0)? file?.file_path[0]?.thumbnails : file?.file_path[0]?.path,
          path: (file.file_path && file.file_path.length > 0) ? file?.file_path[0]?.path : '',
          files: file?.file_path,
          id: file.id,
          amount: getArchiveDocsVale(file.archive_docs, 'amount') ? getArchiveDocsVale(file.archive_docs, 'amount')[0] : '',
          title: getArchiveDocsVale(file.archive_docs, 'title') ? getArchiveDocsVale(file.archive_docs, 'title')[0] : file?.file_path[0]?.file_name,
          description: getArchiveDocsVale(file.archive_docs, 'description') ? getArchiveDocsVale(file.archive_docs, 'description')[0] : '',
          archive_docs_id: getArchiveDocsVale(file.archive_docs, 'title') ? getArchiveDocsVale(file.archive_docs, 'title')[0] : '',
          archive_docs: file.archive_docs,
          department: file.department ? file.department : [],
          division: file.division ? file.division : [],
          unit: file.unit ? file.unit : [],
          file_activity: file.file_activity,
          file_version: file.version,
          is_locked: file.is_locked
        }
        newarr.push(arr);
      }
  
      return newarr
      });
  
      const getArchiveDocsVale = (data, search) => {
  
  if (data) {
    for (let archive of data) {
  
      if (archive.archive_keys.toLowerCase() == search) {
        return [{ archive: archive.archive_values, id: archive.id }]
      }
    }
  }
  }
  
  const formatDate = (inputDate) => {
    var date = new Date(inputDate);
    var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  
    var day = date.getDate();
    var month = months[date.getMonth()];
    var year = date.getFullYear();
  
    day = day < 10 ? '0' + day : day;
  
    var formattedDate = day + ' ' + month + ' ' + year.toString().substr(-2);
  
    return formattedDate;
  }
  
  const deleteArchive = (data) => {
    let id = data.id
    router.get('/delete-archive', { id: id, only: ['department'] }, {
      onSuccess: page => {
        //console.log(page)
      }, preserveScroll: (page) => console.log(page)
    })
  }
  
  
  watch(selectedDocument, () => {
    emit('docs', selectedDocument);
  })
  
  watch(()=>props.sort, (data)=>{
    if(data == 'asc'){
      filteredFiles.value.sort((a, b) => a.name.localeCompare(b.name));
    }else{
      filteredFiles.value.sort((a, b) => b.name.localeCompare(a.name));
    }
    
  })
  </script>