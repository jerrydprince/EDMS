<template>
  <main class=" ">
    <div class="lg:px-7 px-5 flex items-center justify-between pt-5">
      <div class="flex items-center gap-5">
        <div @click="goBack" class="cursor-pointer">
          <i
            class="pi pi-arrow-left border px-3 py-2 rounded-md text-xs text-[#344054]"
          ></i>
        </div>
        <div>
          <p class="text-xl font-semibold">Upload Documents</p>
          <p class="text-sm">Archive files into directories</p>
        </div>
      </div>
    </div>
    <Divider class=" " />
    <div class="lg:px-7 px-5 lg:flex items-center justify-between">
      <div class="flex items-center gap-5">
        <div class="  ">
          <i
            class="pi pi-file py-2 px-3 text-sm border rounded-full border-gray-300 text-[#344054]"
          ></i>
        </div>
        <div>
          <p class="text-lg">Select Document Type</p>
          <p class="text-xs">Choose the specific category for the document</p>
        </div>

        <div>
          <span
            class="bg-[#E2ECFE] text-[#3B82F6] px-5 py-1 rounded-3xl text-sm"
            >1/5</span
          >
        </div>
      </div>

      <div class="flex items-center mt-5 lg:mt-0">
        <select
          name=""
          id=""
          class="w-96 border border-gray-300 outline-none p-2 text-sm rounded-md text-gray-700"
        >
          <option value="" disabled selected class="text-[#98A2B3]">
            Select Document Type
          </option>
          <option value="pdf">pdf</option>
          <option value="jpeg">jpeg</option>
        </select>
      </div>
    </div>
    <Divider class=" " />

    <!-- =============== upload files ======== -->

    <div class="lg:px-7 px-5 flex items-center justify-between">
      <div class="flex items-center gap-5">
        <div class="  ">
          <i
            class="pi pi-cloud-upload py-2 px-3 text-sm border rounded-full border-gray-300"
          ></i>
        </div>
        <div>
          <p class="text-lg">Upload Files</p>
          <p class="text-xs">Select the file from your device to upload.</p>
        </div>

        <div>
          <span
            class="bg-[#E2ECFE] text-[#3B82F6] ml-7 px-5 py-1 rounded-3xl text-sm"
            >2/5</span
          >
        </div>
      </div>
    </div>
    <Divider class=" " />

    <div
      class="mx-5 lg:ml-20 lg:mr-7 bg-[#FCFCFD] border-dashed flex flex-col items-center py-7 rounded-xl border-[3px] mt-10"
    >
      <i class="pi pi-cloud-upload py-2 px-3 text-2xl"></i>
      <div class="text-center">
        <h1 class="font-semibold">Choose a file or drag & drop it here</h1>
        <p class="text-sm leading-7 mb-7 text-[#475467]">
          JPEG, PNG, PDF, MP3 & other formats
        </p>
      </div>

      <button class="bg-white border-gray-300 border p-3 rounded-lg relative">
        <input type="file" class="opacity-0 absolute w-full" />Browse File
      </button>
    </div>
    <div
      class="mx-5 lg:mx-20 mt-2 lg:mt-5 border w-[70%] lg:w-[50%] p-3 rounded-md"
    >
      <div class="flex justify-between">
        <div class="flex items-center gap-1 justify-center">
          <img :src="fileIcon" alt="" class="w-7" />
          <div class="">
            <h1 class="text-[#1D2939]">Dmiye.pdf</h1>
            <div class="flex items-center gap-5">
              <p class="text-xs leading-7 text-[#667085]">60 KB of 120 KB.</p>
              <span class="flex items-center text-xs gap-1">
                <img :src="loadingIcon" alt="" /> Uploading...
              </span>
            </div>
          </div>
        </div>
        <div>
          <i class="pi pi-times-circle cursor-pointer text-[#1D2939]"></i>
        </div>
      </div>
      <!-- ========== Progress bar  ========== -->

      <div class="w-full h-2 bg-slate-200 mt-2 rounded-md overflow-hidden">
        <div class="w-[50%] h-[100%] bg-[#3B82F6]"></div>
      </div>
    </div>

    <Divider class="mt-14" />

    <!-- ==================    document details    ============= -->
    <div class="lg:px-7 px-5 flex items-center justify-between">
      <div class="flex items-center gap-5">
        <div class="  ">
          <img
            :src="penIcon"
            alt=""
            class="p-3 border rounded-full border-gray-300"
          />
        </div>
        <div>
          <p class="text-lg">Document Details</p>
          <p class="text-xs">
            Fill in basic details & select the document branch.
          </p>
        </div>

        <div>
          <span
            class="bg-[#E2ECFE] text-[#3B82F6] ml-7 px-5 py-1 rounded-3xl text-sm"
            >3/5</span
          >
        </div>
      </div>
    </div>
    <Divider class=" " />

    <div class="lg:ml-20 lg:mr-7 mt-10 px-5">
      <div class="lg:flex justify-between">
        <div class="w-full lg:w-[47%]">
          <label for="" class="font-semibold"
            >Document Name <span class="text-[#F04438]">* </span></label
          >
          <br />
          <input
            type="text"
            required
            class="w-full border border-gray-300 outline-none p-2 text-base rounded-md mt-3"
            placeholder=" Enter Department Name"
          />
        </div>

        <div class="w-full lg:w-[47%] mt-5 lg:mt-0">
          <div class="flex items-center justify-between">
            <label for="" class="font-semibold">Department </label>
            <div class="card flex justify-content-center">
              <InputSwitch v-model="toggle_department" class="" />
            </div>
          </div>
          <select
            name=""
            id=""
            class="border border-gray-300 outline-[#3575DD] p-2 text-base rounded-md w-full mt-2"
            :disabled="!toggle_department"
          >
            <option value="" disabled selected class="text-[#98A2B3]">
              Select Document Type
            </option>
            <option value="pdf">pdf</option>
            <option value="jpeg">jpeg</option>
          </select>
        </div>
      </div>

      <div class="lg:flex justify-between mt-10">
        <div class="w-full lg:w-[47%]">
          <label for="" class="">Document Description (optional) </label>
          <br />
          <textarea
            name=""
            id=""
            cols="10"
            rows="4"
            class="w-full p-2 outline-none border border-gray-300 rounded-md mt-2"
            placeholder=" Provide a brief description"
          ></textarea>
        </div>
        <div class="w-full lg:w-[47%] mt-5 lg:mt-0">
          <div>
            <div class="flex items-center justify-between">
              <label for="" class="font-semibold">Division </label>
              <div class="card flex justify-content-center">
                <InputSwitch v-model="toggle_division" />
              </div>
            </div>
            <select
              name=""
              id=""
              class="border border-gray-300 outline-[#3575DD] p-2 text-base rounded-md w-full mt-2"
              :disabled="!toggle_division"
            >
              <option value="" disabled selected class="text-[#98A2B3]">
                Select Document Type
              </option>
              <option value="pdf">pdf</option>
              <option value="jpeg">jpeg</option>
            </select>
          </div>

          <div class="mt-10">
            <div class="flex items-center justify-between">
              <label for="" class="font-semibold">Unit </label>
              <div class="card flex justify-content-center">
                <InputSwitch v-model="toggle_unit" />
              </div>
            </div>
            <select
              name=""
              id=""
              class="border border-gray-300 outline-[#3575DD] p-2 text-base rounded-md w-full mt-2"
              :disabled="!toggle_unit"
            >
              <option value="" disabled selected class="text-[#98A2B3]">
                Select Document Type
              </option>
              <option value="pdf">pdf</option>
              <option value="jpeg">jpeg</option>
            </select>
          </div>
        </div>
      </div>
    </div>
    <Divider class="mt-10" />

    <!-- =================    document type details ============== -->
    <div class="px-5 lg:px-7 flex items-center justify-between">
      <div class="flex items-center gap-5">
        <div class="  ">
          <img
            :src="editIcon"
            alt=""
            class="p-3 border rounded-full border-gray-300"
          />
        </div>
        <div>
          <p class="text-lg">Document Type Details</p>
          <p class="text-xs">
            Fill details specific to the selected document type.
          </p>
        </div>

        <div>
          <span
            class="bg-[#E2ECFE] text-[#3B82F6] ml-7 px-5 py-1 rounded-3xl text-sm"
            >4/5</span
          >
        </div>
      </div>
    </div>
    <Divider class=" " />

    <div class="lg:flex justify-between lg:ml-20 lg:mr-7 px-5">
      <div class="w-full lg:w-[47%]">
        <label for="" class="font-semibold"
          >Project Title <span class="text-[#F04438]">* </span></label
        >
        <br />
        <input
          type="text"
          required
          class="w-full border border-gray-300 outline-none p-2 text-base rounded-md mt-3"
          placeholder=" Enter Department Name"
        />
      </div>

      <div class="w-full lg:w-[47%] mt-5 lg:mt-0">
        <label for="" class="font-semibold"
          >Client Name <span class="text-[#F04438]">* </span></label
        >
        <br />
        <input
          type="text"
          required
          class="w-full border border-gray-300 outline-none p-2 text-base rounded-md mt-3"
          placeholder=" Enter Department Name"
        />
      </div>
    </div>

    <div class="lg:flex justify-between lg:ml-20 lg:mr-7 mt-10 px-5">
      <div class="w-full lg:w-[47%]">
        <label for="" class="font-semibold"
          >Due Date <span class="text-[#F04438]">* </span></label
        >
        <br />
        <input
          type="date"
          required
          class="w-full border border-gray-300 outline-none p-2 text-base rounded-md mt-3"
          placeholder=" Enter Department Name"
        />
      </div>

      <div class="w-full lg:w-[47%] mt-5 lg:mt-0">
        <label for="" class="font-semibold"
          >Due Date <span class="text-[#F04438]">* </span></label
        >
        <br />
        <input
          type="text"
          required
          class="w-full border border-gray-300 outline-none p-2 text-base rounded-md mt-3"
          placeholder=" Enter Department Name"
        />
      </div>
    </div>
    <Divider class=" " />

    <!-- =========================    choose folder ====================== -->

    <div class="px-5 lg:px-7 lg:flex items-center justify-between">
      <div class="flex items-center gap-5">
        <div class="  ">
          <i
            class="pi pi-folder py-2 px-3 text-sm border rounded-full border-gray-300 text-[#344054]"
          ></i>
        </div>
        <div>
          <p class="text-lg">Choose Folder Location</p>
          <p class="text-xs">
            Fill details specific to the selected document type.
          </p>
        </div>

        <div>
          <span
            class="bg-[#E2ECFE] text-[#3B82F6] px-5 py-1 rounded-3xl text-sm"
            >5/5</span
          >
        </div>
      </div>

      <div
        class="flex items-center border border-gray-300 rounded-md overflow-hidden mt-5 lg:mt-0"
      >
        <i class="pi pi-search p-2 text-[#667085]"></i>
        <input
          placeholder="Search Folder"
          type="text"
          class="w-96 p-2 outline-none"
        />
      </div>
    </div>
    <Divider class=" " />

    <!-- =====================    Add folder ================ -->

    <div>
      <div class="flex items-center justify-end px-5 lg:mx-7">
        <button
          @click="createFolderModal = true"
          class="flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-2xl py-1 px-4"
        >
          <Icon icon="quill:folder-add" width="20" />
          <p>Add Folder</p>
        </button>
      </div>

      <div class="flex flex-wrap gap-10 mt-10 justify-center mx-5 lg:mx-20">
        <div
          v-for="i in 6"
          :style="{ borderColor: checked.includes(i) ? '#3B82F6' : '#EAECF0' }"
          class="flex items-center border p-3 gap-3 rounded-md h-full"
          :class="[
            'rounded-md',
            'flex',
            'cursor-pointer',
            'items-center',
            'border',
            'justify-between',
            'bg-gray-100',
            'w-full',
            'lg:w-72',
            'p-3',
            'border-blue-500',
          ]"
          :key="i"
        >
          <div class="card flex justify-content-center">
            <Checkbox v-model="checked" :value="i" />
          </div>
          <div class="border rounded-md p-2 bg-gray-100">
            <img :src="folderIcon" alt="" />
          </div>
          <div>
            <h1 class="text-lg font-medium">Architecture Plan</h1>
            <div class="flex items-center gap-3 mt-1">
              <p>16 Files</p>
            </div>
          </div>
        </div>
      </div>

      <Divider class="mt-10" />
    </div>

    <div class="flex justify-end px-5 lg:mx-7">
      <div class="flex items-center gap-3">
        <button
          class="py-2 px-10 border font-light rounded-lg"
          @click="createFolderModal = true"
        >
          <p>Cancel</p>
        </button>

        <button
          class="py-2 bg-[#3575DD] px-10 border-0 text-white font-light hover:bg-[#3575DD]/90 rounded-lg"
        >
          <p>Save</p>
        </button>
      </div>
    </div>
    <Divider class=" " />

    <CreateFolderDialog v-model="createFolderModal" />
  </main>
</template>

<script setup>
import { ref } from "vue";
import CreateFolderDialog from "../../components/Modals/branches/CreateFolderDialog.vue";
import fileIcon from "../../../../public/images/file.svg";
import loadingIcon from "../../../../public/images/loading.svg";
import penIcon from "../../../../public/images/pen.svg";
import editIcon from "../../../../public/images/edit.svg";
import folderIcon from "../../../../public/images/folder.svg";

const goBack = () => {
  router.go(-1);
};

const checked = ref([]);
console.log(checked.value);
const createFolderModal = ref(false);
const toggle_department = ref(true);
const toggle_division = ref(true);
const toggle_unit = ref(true);
</script>
<style scoped>
.p-progressbar-value {
  display: none;
}
</style>
