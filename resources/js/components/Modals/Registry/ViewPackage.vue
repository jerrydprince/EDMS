<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    content: { class: 'px-0 bg-white pb-6' },
  }" :style="{ width: '45rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
    class=".custom-dialog">
    <template #header>
      <div class="w-full">
        <div class="flex items-center w-full justify-between">
          <div @click="closeModal"
            class="cursor-pointer flex gap-3 items-center text-gray-500 border-r border-gray-300 pr-3">
            <i class="pi pi-arrow-left border px-3 py-2 rounded-md text-xs text-[#344054]"></i>
            <h1>Back</h1>
          </div>
          <div class="flex items-center gap-2">
            <button v-if="props.fileMovement && props.fileMovement.length && props.fileMovement[props.fileMovement.length-1].status === 'pending'" class="flex items-center gap-1 border p-2 rounded-md border-green-500 bg-green-50 text-green-700 mr-2 border-solid"
              @click="acknowledgeFile">
              <Icon icon="charm:tick" width="20" />
              <p>Acknowledge Receipt</p>
            </button>
            <button class="flex items-center gap-1 border p-2 rounded-md border-gray-300 border-solid"
              @click="downloadPDF">
              <Icon icon="material-symbols:download" width="25" />
              <p>Download Report</p>
            </button>
          </div>
        </div>
        <div class="mt-5" v-if="props.task">
          <div class="flex items-center justify-between">
            <h1 class="font-medium text-lg">{{ props.task.title }}</h1>
            <div class="flex items-center gap-2">
              <Link :href="'/dashboard/view-task/' + (props.task.id || props.task.tracking_id)"
                class="flex items-center gap-1.5 px-3 py-1 bg-primary/10 text-primary hover:bg-primary hover:text-white border border-primary/20 rounded-lg text-xs font-medium transition-all">
                <Icon icon="ph:arrow-square-out" width="16" />
                <span>Open in Workflow</span>
              </Link>
              <span :class="['px-3 py-1 rounded-full text-xs font-medium', props.task.status === 'Completed' || props.task.status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700']">
                {{ props.task.status || 'In Progress' }}
              </span>
            </div>
          </div>
          <p class="mt-2 text-xs w-fit font-normal px-2 py-1 rounded-xl text-gray-500 bg-gray-50">
            #{{ props.task.tracking_id }}
          </p>
        </div>
        <div class="mt-5" v-else>
          <h1 class="font-medium text-lg">{{ props.file?.name }}</h1>
          <p class="mt-2 text-xs w-fit font-normal px-2 py-1 rounded-xl text-gray-500 bg-gray-50">
            #{{ props.file?.doc_no }}
          </p>
        </div>
      </div>
    </template>
    <Divider class="-mt-2" />
    <section class="px-7 overflow-y-auto max-h-[500px] border-b border-gray-100" ref="content">
      <div v-if="props.task">
        <div v-for="(item, index) in taskTimeline" :key="index" class="">
          <div class="mb-3 flex items-start gap-3">
            <div class="flex items-center flex-col">
              <div class="p-3 mb-2 border rounded-full text-blue-600" :class="item.type === 'assignment' ? 'bg-indigo-50 border-indigo-100' : 'bg-blue-50 border-blue-100'">
                 <Icon :icon="item.type === 'assignment' ? 'ph:users' : 'ph:file-text'" width="25" />
              </div>
              <div class="w-[1px] h-full min-h-12 bg-slate-300" v-if="index !== taskTimeline.length - 1"></div>
            </div>
            
            <!-- Minute Sheet View -->
            <div class="flex-1 pb-4" v-if="item.type === 'minute'">
              <div class="flex items-center gap-2">
                <p class="font-semibold text-gray-800">{{ item.user?.first_name }} {{ item.user?.last_name }}</p>
                <span v-if="getUserDepartment(item.user) || item.user?.designation" class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">
                  {{ item.user?.designation ? item.user.designation.title : '' }} 
                  <span v-if="item.user?.designation && getUserDepartment(item.user)">-</span> 
                  {{ getUserDepartment(item.user) }}
                </span>
              </div>
              <div class="flex items-center gap-2 mt-1 mb-2">
                <span class="text-xs px-2 py-1 bg-gray-100 rounded-md text-gray-600">{{ item.action }}</span>
                <p class="text-xs text-gray-400">{{ formatDate(item.created_at) }}</p>
                <span :class="['text-[11px] px-2 py-0.5 rounded-full font-medium ml-1.5', getDwellBadge(item.created_at, taskTimeline[index + 1]).class]">
                  {{ getDwellBadge(item.created_at, taskTimeline[index + 1]).text }}
                </span>
              </div>
              <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 mt-2 text-sm text-gray-600 whitespace-pre-wrap" v-if="item.directive">
                {{ item.directive }}
              </div>
            </div>

            <!-- Assignment View -->
            <div class="flex-1 pb-4" v-if="item.type === 'assignment'">
              <p class="font-semibold text-gray-800">Task Assigned</p>
              <div class="flex items-center gap-2 mt-1 mb-2">
                <span class="text-xs px-2 py-1 bg-indigo-100 rounded-md text-indigo-700">Assignment</span>
                <p class="text-xs text-gray-400">{{ formatDate(item.created_at) }}</p>
                <span :class="['text-[11px] px-2 py-0.5 rounded-full font-medium ml-1.5', getDwellBadge(item.created_at, taskTimeline[index + 1]).class]">
                  {{ getDwellBadge(item.created_at, taskTimeline[index + 1]).text }}
                </span>
              </div>
              <div class="bg-indigo-50/50 p-3 rounded-lg border border-indigo-100 mt-2 text-sm text-gray-700">
                Assigned to <span class="font-semibold">{{ item.assignedTo?.first_name }} {{ item.assignedTo?.last_name }}</span> 
                <span v-if="getUserDepartment(item.assignedTo) || item.assignedTo?.designation" class="text-xs text-gray-500 bg-white border px-1.5 py-0.5 rounded-md ml-1">
                  {{ item.assignedTo?.designation ? item.assignedTo.designation.title : '' }}
                  <span v-if="item.assignedTo?.designation && getUserDepartment(item.assignedTo)">-</span> 
                  {{ getUserDepartment(item.assignedTo) }}
                </span>
                <br />
                <span class="text-gray-500 mt-1 block">by {{ item.assignedBy?.first_name }} {{ item.assignedBy?.last_name }}</span>
              </div>
            </div>
          </div>
        </div>
        <div v-if="!taskTimeline || taskTimeline.length === 0" class="text-center py-8 text-gray-500">
          No history found for this task.
        </div>
      </div>
      
      <div v-else>
        <div v-for="(branch, index) in props.fileMovement" :key="index" class="">
          <div class="mb-3 flex items-start gap-3">
            <div class="flex items-center flex-col">
              <div class="p-3 mb-2 border rounded-full bg-yellow-50 text-yellow-600 shadow-sm">
                 <img :src="unitIcon" alt="" width="25" v-if="branchType(branch) == 'unit'">
                 <img :src="divisionIcon" alt="" width="25" v-if="branchType(branch) == 'division'">
                 <img :src="departmentIcon" alt="" width="25" v-if="branchType(branch) == 'department'">
                 <img :src="unitIcon" alt="" width="25" v-if="branch.sent_to_branch.toLowerCase() == 'agf registry'">
              </div>
              <div class="w-[1px] h-full min-h-12 bg-slate-300"></div>
            </div>
            <div class="flex-1 pb-4">
              <p class="font-semibold text-gray-800">{{ getBranchName(branch, index) }}</p>
              <div class="flex items-center gap-2 mt-1 mb-2">
                <span v-if="branch.moves" class="text-xs px-2 py-1 bg-yellow-100 rounded-md text-yellow-700 font-medium">{{ branch.moves }}</span>
                <p class="text-xs text-gray-500 font-medium">{{ formatDate(branch.created_at) }}</p>
              </div>
              <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 mt-2 text-sm text-gray-600" v-if="branch.status">
                <span class="font-medium text-gray-700">Status:</span> 
                <span :class="{'text-green-600 font-medium': branch.status === 'acknowledged', 'text-orange-500 font-medium': branch.status === 'pending'}">{{ branch.status.charAt(0).toUpperCase() + branch.status.slice(1) }}</span>
              </div>
            </div>
          </div>
  
          <div v-if="props.fileMovement.length - 1 == index">
            <div class="mb-3 flex items-start gap-3">
              <div class="flex items-center flex-col">
                <div class="p-3 border rounded-full bg-green-50 text-green-600 shadow-sm">
                  <img :src="unitIcon" alt="" width="25" v-if="branchType2(branch) == 'unit'">
                 <img :src="divisionIcon" alt="" width="25" v-if="branchType2(branch) == 'division'">
                 <img :src="departmentIcon" alt="" width="25" v-if="branchType2(branch) == 'department' || branchType2(branch) == 'agf'">
                </div>
                <div class="w-[1px] h-full min-h-12 bg-slate-300" v-if="props.fileMovement.length - 1 !== index"></div>
              </div>
              <div class="flex-1 pb-4">
                <p class="font-semibold text-gray-800">{{ getBranchDestinationName(branch, index) }} (Destination)</p>
                <div class="flex items-center gap-2 mt-1 mb-2">
                  <span v-if="branch.moves" class="text-xs px-2 py-1 bg-green-100 rounded-md text-green-700 font-medium">Final Destination</span>
                  <p class="text-xs text-gray-500 font-medium">{{ formatDate(branch.created_at) }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div v-if="!props.fileMovement || props.fileMovement.length === 0" class="text-center py-8 text-gray-500">
          No movement history found for this file.
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
import { usePage, Link } from '@inertiajs/vue3'
import Swal from 'sweetalert2';
import unitIcon from '../../../../../public/images/unit.svg';
import divisionIcon from '../../../../../public/images/division.svg';
import departmentIcon from '../../../../../public/images/department.svg';

const props = defineProps(["modelValue", "fileMovement", 'file', 'task', 'taskMinutes', 'department', 'division', 'unit']);
const page = usePage();
const emit = defineEmits(["update:modelValue"]);

const getDwellBadge = (created_at, nextItem) => {
  if (!created_at) return { text: '', class: 'hidden' };
  const current = new Date(created_at).getTime();
  const end = nextItem && nextItem.created_at ? new Date(nextItem.created_at).getTime() : Date.now();
  const diffHours = Math.max(1, Math.round((end - current) / (1000 * 60 * 60)));
  
  if (diffHours < 24) {
    return { text: diffHours <= 1 ? 'Just now' : `${diffHours}h at desk`, class: 'bg-emerald-50 text-emerald-700 border border-emerald-200' };
  } else if (diffHours < 72) {
    const days = Math.floor(diffHours / 24);
    return { text: `${days}d at desk`, class: 'bg-amber-50 text-amber-700 border border-amber-200' };
  } else {
    const days = Math.floor(diffHours / 24);
    return { text: `⚠️ Delayed (${days}d)`, class: 'bg-red-50 text-red-700 border border-red-200 font-semibold' };
  }
};
const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  },
});

const content = ref(null);

const taskTimeline = computed(() => {
  let timeline = [];
  
  if (props.taskMinutes && props.taskMinutes.length > 0) {
    props.taskMinutes.forEach(minute => {
      timeline.push({
        type: 'minute',
        user: minute.user,
        action: minute.action,
        directive: minute.directive,
        created_at: minute.created_at
      });
    });
  }
  
  if (props.task && props.task.assignments && props.task.assignments.length > 0) {
    props.task.assignments.forEach(assignment => {
      timeline.push({
        type: 'assignment',
        assignedBy: assignment.assigned_by, 
        assignedTo: assignment.assigned_to,
        created_at: assignment.created_at,
        status: assignment.status
      });
    });
  }
  
  return timeline.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
});

const getUserDepartment = (user) => {
  if (!user || !user.department_id || !props.department) return '';
  const dept = props.department.find(d => d.id === user.department_id);
  return dept ? dept.title : '';
};

const branchFilterSource = (branch) => {
  if (branch.sent_from_agf) {
    return [{title: 'AGF Office'}]
  }

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
  if (branch.sent_from_agf) return 'agf'
  if (branch.sent_from_unit) return 'unit'
  if (branch.sent_from_division) return 'division'
  if (branch.sent_from_department) return 'department'
}

const branchType2 = (branch)=>{
  if (branch.sent_to_agf) return 'agf'
  if (branch.sent_to_unit) return 'unit'
  if (branch.sent_to_division) return 'division'
  if (branch.sent_to_department) return 'department'
}

const branchFilter = (branch) => {
  if (branch.sent_to_agf) {
    return [{title: 'AGF Office'}]
  }

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

      doc.addImage(img, "PNG", 20, 10, doc.internal.pageSize.getWidth() - 40, doc.internal.pageSize.getHeight() - 20);
      const rawName = props.file?.name || (props.task ? `Docket_${props.task.tracking_id}` : 'Docket_Tracking_Report');
      const safeName = rawName.replace(/[^a-zA-Z0-9_-]/g, '_');
      doc.save(safeName + '.pdf');
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
const acknowledgeFile = () => {
  const lastMovement = props.fileMovement && props.fileMovement.length ? props.fileMovement[props.fileMovement.length - 1] : null;
  if (!lastMovement) return;
  
  axios.post(`/dashboard/registry/movements/${lastMovement.id}/acknowledge`)
    .then(response => {
        closeModal();
        Swal.fire({
          icon: 'success',
          title: 'Acknowledged',
          text: 'File custody acknowledged successfully.',
          timer: 1800,
          showConfirmButton: false
        }).then(() => {
          window.location.reload();
        });
    })
    .catch(error => {
        Swal.fire({
          icon: 'error',
          title: 'Acknowledgment Error',
          text: error.response?.data?.message || "Failed to acknowledge file."
        });
    });
};

const closeModal = () => {
  emit("update:modelValue", false);
};
</script>
<style scoped>
.custom-dialog .p-dialog-header {
  display: none;
  width: 20%;
}
</style>
