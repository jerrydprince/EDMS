<template>
  <Dialog
    v-model:visible="visible"
    :closable="false"
    modal
    :pt="{
      content: { class: 'px-0 bg-white pb-6' },
    }"
    :style="{ width: '35rem', padding: '10px 0px' }"
    :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
    class=".custom-dialog"
  >
    <template #header>
      <div class="w-full">
        <div class="flex items-center w-full justify-between">
          <div
            @click="closeModal"
            class="cursor-pointer flex gap-3 items-center text-gray-500 border-r border-gray-300 pr-3"
          >
            <i
              class="pi pi-arrow-left border px-3 py-2 rounded-md text-xs text-[#344054]"
            ></i>
            <h1>Back</h1>
          </div>
          <div>
            <button
              class="flex items-center gap-1 border p-2 rounded-md border-gray-300 border-solid"
            @click="downloadPDF"
              >
              <Icon icon="material-symbols:download" width="25" />
              <p>Download Report</p>
            </button>
          </div>
        </div>
        <div class="mt-5">
          <h1 class="font-medium text-lg">{{props.fileMovement.file_name}}</h1>
          <p
            class="mt-2 text-xs w-fit font-normal px-2 py-1 rounded-xl text-gray-500 bg-gray-50"
          >
            #{{props.fileMovement.file_id}}
          </p>
        </div>
      </div>
    </template>
    <Divider class="-mt-2" />
    <section class="px-7">
      <div  v-for="(branch, index) in props.fileMovement.movements" :key="index" class="">
        <div class="mb-3 flex items-start gap-3">
          <div class="flex items-center flex-col">
            <div class="p-3 border rounded-full">
              <Icon :icon="branch.icon" width="25" class="text-gray-600" />
            </div>
            <div class="w-[1px] h-11 bg-slate-300" v-if="branches.length-1 !== index"></div>
          </div>
         <div  class=" flex items-start gap-2">
            <!-- <div class="content">
                <p>save</p>
                <p>HERE</p>
            </div> -->
          <div ref="content">
          <p class=" font-semibold" >{{ branch.to_division.title }} </p>
          <p  class=" text-gray-400 mt-1" >{{ branch.created_at }}</p>
        </div>
          <span v-if="branch.moves" class=" text-gray-400">({{ branch.moves }})</span>

        </div>
        </div>
        <div></div>
      </div>
    </section>
    <template #footer> </template>
  </Dialog>
</template>

<script setup>
import { defineEmits, defineProps,nextTick, computed, ref } from "vue";
import jsPDF from 'jspdf';
import html2canvas from 'html2canvas'
const props = defineProps(["modelValue", "fileMovement"]);
console.log("seen", props.fileMovement);
const emit = defineEmits(["update:modelValue"]);
const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  },
});

const content = ref(null);

const downloadPDF = async () => {
    await nextTick();  // Ensure the DOM is fully updated
    if (content.value) {
        console.log('Content is available:', content.value);
        const doc = new jsPDF({
        orientation: 'p',
        unit: 'px',
        format: 'a4',
        hotfixes: ['px_scaling'],
      });
      html2canvas(content.value, {
        width: doc.internal.pageSize.getWidth(),
        height: doc.internal.pageSize.getHeight()
      }).then((canvas) => {
        const img = canvas.toDataURL("image/png");

        doc.addImage(img, "PNG", 140, 10, doc.internal.pageSize.getWidth(), doc.internal.pageSize.getHeight());
        doc.save("p&lstatement.pdf");
      }).catch(error => {
          console.error('Error generating PDF:', error);
        });
      } else {
        console.error('Content is not available');
      }
};
const branches = ref([
  { name: "Accounting Department", icon: "gg:organisation", moves:"Source" ,date:"24 Jan 2024"},
  { name: "Electronics Repair Division", icon: "tabler:box",date:"24 Feb 2024" },
  { name: "Audit transaction Repair Division", icon: "jam:fork",date:"24 Mar 2024" },
  { name: "Electronics Repair Division", icon: "tabler:box",moves:"Destination",date:"24 Apr 2024" },
]);
const closeModal = () => {
  emit("update:modelValue", false);
//   selectedIndex.value = null;
};
</script>
 <style scoped>
.custom-dialog .p-dialog-header {
  display: none;
  width: 20%;
}
</style>
