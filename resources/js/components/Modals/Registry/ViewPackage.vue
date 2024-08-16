<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    content: { class: 'px-0 bg-white pb-6' },
  }" :style="{ width: '35rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
    class=".custom-dialog">
    <template #header>
      <div class="w-full">
        <div class="flex items-center w-full justify-between">
          <div @click="closeModal"
            class="cursor-pointer flex gap-3 items-center text-gray-500 border-r border-gray-300 pr-3">
            <i class="pi pi-arrow-left border px-3 py-2 rounded-md text-xs text-[#344054]"></i>
            <h1>Back</h1>
          </div>
          <div>
            <button class="flex items-center gap-1 border p-2 rounded-md border-gray-300 border-solid"
              @click="downloadPDF">
              <Icon icon="material-symbols:download" width="25" />
              <p>Download Report</p>
            </button>
          </div>
        </div>
        <div class="mt-5">
          <h1 class="font-medium text-lg">{{ props.file?.name }}</h1>
          <p class="mt-2 text-xs w-fit font-normal px-2 py-1 rounded-xl text-gray-500 bg-gray-50">
            #{{ props.file?.doc_no }}
          </p>
        </div>
      </div>
    </template>
    <Divider class="-mt-2" />
    <section class="px-7" ref="content">
      <div v-for="(branch, index) in props.fileMovement" :key="index" class="">
        <div class="mb-3 flex items-start gap-3">
          <div class="flex items-center flex-col">
            <div class="p-3 mb-2 border rounded-full">
              <!-- <Icon :icon="branch.icon" width="25" class="text-gray-600" /> -->
               <img :src="page.props.base+'/images/unit.svg'" alt="" width="25" v-if="branchType(branch) == 'unit'">
               <img :src="page.props.base+'/images/division.svg'" alt="" width="25" v-if="branchType(branch) == 'division'">
               <img :src="page.props.base+'/images/department.svg'" alt="" width="25" v-if="branchType(branch) == 'department'">
            </div>
            <div class="w-[1px] h-11 bg-slate-300"></div>
          </div>
          <div class=" flex items-start gap-2">
            <div>
              <p class=" font-semibold">{{ getBranchName(branch, index) }} </p>
              <p class=" text-gray-400 mt-1">{{ formatDate(branch.created_at) }}</p>
            </div>
            <span v-if="branch.moves" class=" text-gray-400">({{ branch.moves }})</span>

          </div>
        </div>
        <div></div>

        <div v-if="props.fileMovement.length - 1 == index">
          <div class="mb-3 flex items-start gap-3">
            <div class="flex items-center flex-col">
              <div class="p-3 border rounded-full">
                <!-- <Icon :icon="branch.icon" width="25" class="text-gray-600" /> -->
                <img :src="page.props.base+'/images/unit.svg'" alt="" width="25" v-if="branchType2(branch) == 'unit'">
               <img :src="page.props.base+'/images/division.svg'" alt="" width="25" v-if="branchType2(branch) == 'division'">
               <img :src="page.props.base+'/images/department.svg'" alt="" width="25" v-if="branchType2(branch) == 'department'">
              </div>
              <div class="w-[1px] h-11 bg-slate-300" v-if="props.fileMovement.length - 1 !== index"></div>
            </div>
            <div class=" flex items-start gap-2">
              <div>
                <p class=" font-semibold">{{ getBranchDestinationName(branch, index) }} </p>
                <p class=" text-gray-400 mt-1">{{ formatDate(branch.created_at) }}</p>
              </div>
              <span v-if="branch.moves" class=" text-gray-400">({{ branch.moves }})</span>

            </div>
          </div>
          <div></div>
        </div>
      </div>
    </section>
    <template #footer> </template>
  </Dialog>
</template>

<script setup>
import { defineEmits, defineProps, nextTick, computed, ref } from "vue";
import jsPDF from 'jspdf';
import html2canvas from 'html2canvas'
import { usePage } from '@inertiajs/vue3'

const props = defineProps(["modelValue", "fileMovement", 'file', 'department', 'division', 'unit']);
const page = usePage();
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

const branchFilterSource = (branch) => {
  if (branch.sent_from_unit) {
    let filtered = props.unit.filter((item) => {
      return item.id == branch.sent_from_unit
    })
    return filtered
  }

  if (branch.sent_from_division) {
    let filtered = props.division.filter((item) => {
      return item.id == branch.sent_from_division
    })
    return filtered
  }

  if (branch.sent_from_department) {
    let filtered = props.department.filter((item) => {
      return item.id == branch.sent_from_department
    })
    return filtered
  }
}

const branchType = (branch)=>{
  if (branch.sent_from_unit) {
    return 'unit'
  }

  if (branch.sent_from_division) {
    return 'division'
  }

  if (branch.sent_from_department) {
    return 'department'
  }
}

const branchType2 = (branch)=>{
  if (branch.sent_to_unit) {
    return 'unit'
  }

  if (branch.sent_to_division) {
    return 'division'
  }

  if (branch.sent_to_department) {
    return 'department'
  }
}

const branchFilter = (branch) => {
  if (branch.sent_to_unit) {
    let filtered = props.unit.filter((item) => {
      return item.id == branch.sent_to_unit
    })
    return filtered
  }

  if (branch.sent_to_division) {
    let filtered = props.division.filter((item) => {
      return item.id == branch.sent_to_division
    })
    return filtered
  }

  if (branch.sent_to_department) {
    let filtered = props.department.filter((item) => {
      return item.id == branch.sent_to_department
    })
    return filtered
  }
}

const getBranchName = (branch, index) => {
  //check for the source of the file
  if (index == 0) {
    let filtered = branchFilterSource(branch)
    if (filtered && filtered.length > 0) {
      return filtered[0].title
    } else {
      return ''
    }
  }

  //the final destination
  if (props.fileMovement && index == props.fileMovement.length - 1) {
    let filtered = branchFilterSource(branch)
    if (filtered && filtered.length > 0) {
      return filtered[0].title
    } else {
      return ''
    }
  }

  let filtered = branchFilterSource(branch)
  if (filtered && filtered.length > 0) {
    return filtered[0].title
  } else {
    return ''
  }
}

const formatDate = (inputDate) => {
  var date = new Date(inputDate);
  var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

  var day = date.getDate();
  var month = months[date.getMonth()];
  var year = date.getFullYear();

  day = day < 10 ? '0' + day : day;

  var formattedDate = day + ' ' + month + ' ' + year.toString();

  return formattedDate;
}

const getBranchDestinationName = (branch, index) => {
  //the final destination
  if (props.fileMovement && index == props.fileMovement.length - 1) {
    let filtered = branchFilter(branch)
    if (filtered && filtered.length > 0) {
      return filtered[0].title
    } else {
      return ''
    }
  }
}

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
      doc.save(props.file.name);
    }).catch(error => {
      console.error('Error generating PDF:', error);
    });
  } else {
    console.error('Content is not available');
  }
};
const branches = ref([
  { name: "Accounting Department", icon: "gg:organisation", moves: "Source", date: "24 Jan 2024" },
  { name: "Electronics Repair Division", icon: "tabler:box", date: "24 Feb 2024" },
  { name: "Audit transaction Repair Division", icon: "jam:fork", date: "24 Mar 2024" },
  { name: "Electronics Repair Division", icon: "tabler:box", moves: "Destination", date: "24 Apr 2024" },
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
