<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    root: { class: 'bg-white rounded-2xl shadow-2xl overflow-hidden' },
    content: { class: 'p-8 max-h-[75vh] overflow-auto' },
    header: { class: 'bg-white px-8 py-5 border-b' },
    footer: { class: 'bg-gray-50 px-8 py-4 border-t rounded-b-2xl' },
  }" :style="{ width: '50rem' }" :breakpoints="{ '1199px': '75vw', '575px': '95vw' }"
    class="custom-dialog">
    <template #header>
      <div class="flex items-center justify-between w-full">
        <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Assign Task</h2>
        <div class="flex gap-4 items-center">
          <Icon icon="mingcute:close-line" width="24" @click="closeModal" class="cursor-pointer text-gray-400 hover:text-red-500 transition-colors" />
        </div>
      </div>
    </template>

    <template #footer>
      <div class="flex justify-end gap-3 w-full">
        <button class="px-5 py-2 text-gray-600 font-medium rounded-xl hover:bg-gray-200 transition-colors cursor-pointer" @click="closeModal" :disabled="form.processing">Cancel</button>
        <button class="px-6 py-2 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 transition-colors shadow-md shadow-blue-200 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed" @click="submit" :disabled="form.processing">
          {{ form.processing ? 'Assigning...' : 'Assign Task' }}
        </button>
      </div>
    </template>

    <section class="flex flex-col gap-6">
      <div class="flex flex-col bg-gray-50 p-4 rounded-xl border border-gray-100">
        <p class="text-sm text-gray-500 font-medium mb-1">Task Title</p>
        <h3 class="text-2xl font-semibold text-gray-800">{{ task?.title }}</h3>
      </div>
      <section class="p-5 text-gray-600">
          <div v-if="task?.is_signed" class="flex items-center gap-2 bg-green-50 text-green-700 border border-green-200 px-4 py-3 rounded-xl w-full justify-center shadow-sm mb-4">
            <Icon icon="lucide:check-circle" width="20" />
            <span class="font-medium text-sm">Task documents have been digitally signed.</span>
          </div>
        <div class="flex flex-col gap-5 p-6 rounded-2xl border border-gray-100 bg-gray-50/50 shadow-sm">
          
          <!-- Toggle Assignment Type (Directors Only) -->
          <div v-if="page.props.roles && page.props.roles.includes('director')" class="flex items-center gap-4 bg-gray-100 p-1.5 rounded-xl border border-gray-200 mb-2">
            <button class="flex-1 py-2 text-sm font-medium rounded-lg transition-colors cursor-pointer" @click="assignmentType = 'custom'" :class="assignmentType === 'custom' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-500 hover:text-gray-700'">Custom Assignment</button>
            <button class="flex-1 py-2 text-sm font-medium rounded-lg transition-colors cursor-pointer" @click="assignmentType = 'workflow'" :class="assignmentType === 'workflow' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-500 hover:text-gray-700'">Workflow Template</button>
          </div>

          <!-- Workflow Selection -->
          <div class="flex flex-col gap-6 bg-white p-6 rounded-2xl border border-gray-200 shadow-sm mb-4" v-if="page.props.roles && page.props.roles.includes('director') && assignmentType === 'workflow'">
            <div class="flex flex-col gap-2">
               <h3 class="font-semibold text-gray-800 text-lg border-b border-gray-100 pb-3">Select Workflow</h3>
               <p class="text-gray-500 text-sm mt-1 mb-3">Choose a department-specific workflow template to route this task.</p>
               <select v-model="form.workflow_version_id" class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-50 transition-all text-sm">
                 <option value="" disabled>Select a workflow</option>
                 <option v-for="wf in workflowsList" :key="wf.id" :value="wf.id">{{ wf.workflow ? wf.workflow.title : 'Workflow' }} (v{{ wf.version_number }})</option>
               </select>
            </div>
          </div>
          
          <!-- Assign To -->
          <div class="grid grid-cols-12 items-center gap-4">
            <div class="col-span-12 sm:col-span-4 lg:col-span-3 flex items-center gap-3 text-gray-700 font-medium">
              <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                <Icon icon="lucide:users" width="16" />
              </div>
              <p>Assign To <span class="text-red-400 font-normal text-sm">*</span></p>
            </div>
            <div class="col-span-12 sm:col-span-8 lg:col-span-9 flex items-center relative">
               <MultiSelect
                 v-model="selectedUsers"
                 :options="usersList"
                 optionLabel="display_name"
                 placeholder="Search department / staff"
                 :filter="true"
                 display="chip"
                 :pt="{
                    root: { class: 'w-full text-left bg-white border border-gray-200 rounded-xl hover:border-blue-400 focus:border-blue-400 focus:ring-4 focus:ring-blue-50 transition-all outline-none flex items-center justify-between' },
                    label: { class: 'py-2 px-3 text-sm flex-1' },
                    trigger: { class: 'w-10 flex items-center justify-center text-gray-400' },
                    panel: { class: 'bg-white shadow-xl rounded-xl border border-gray-100 mt-2 p-1 overflow-hidden' },
                    header: { class: 'p-2 border-b border-gray-100 flex items-center justify-between gap-2' },
                    headerCheckboxContainer: { class: 'flex items-center justify-center mr-2' },
                    headerCheckbox: { class: 'w-4 h-4 border border-gray-300 rounded cursor-pointer flex items-center justify-center' },
                    closeButton: { class: 'w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-full transition-colors' },
                    filterContainer: { class: 'relative w-full flex items-center' },
                    filterInput: { class: 'w-full pl-9 pr-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-all outline-none' },
                    filterIcon: { class: 'text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 pointer-events-none' },
                    wrapper: { class: 'max-h-[250px] overflow-auto py-1' },
                    item: ({ context }) => ({ class: ['px-3 py-2.5 hover:bg-gray-50 transition-colors flex flex-row items-center gap-3 rounded-lg mx-1 cursor-pointer whitespace-nowrap', context.selected ? 'bg-blue-50/50' : ''] }),
                    itemCheckbox: { class: 'w-4 h-4 border border-gray-300 rounded cursor-pointer flex-shrink-0 flex items-center justify-center' }
                 }"
                 @change="updateUsersMultiSelect"
               >
                 <template #option="slotProps">
                   <div class="flex items-center justify-between w-full gap-3 py-1">
                     <div class="flex items-center gap-3 min-w-0">
                       <img :src="getImageForGroup(slotProps.option)" class="w-8 h-8 rounded-full object-cover shrink-0" />
                       <div class="min-w-0">
                         <p class="text-sm font-semibold truncate">{{ slotProps.option.display_name }}</p>
                         <p v-if="slotProps.option.email" class="text-[11px] text-gray-400 truncate">{{ slotProps.option.email }}</p>
                       </div>
                     </div>
                     <span v-if="slotProps.option.active_tasks_count !== undefined" class="text-[10px] px-2 py-0.5 rounded-full font-bold shrink-0 shadow-2xs"
                       :class="slotProps.option.active_tasks_count > 5 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'">
                       {{ slotProps.option.active_tasks_count }} Active
                     </span>
                   </div>
                 </template>
               </MultiSelect>
            </div>
          </div>
          <div v-if="form.errors.users" class=" text-red-400">{{ form.errors.users }}</div>


          <!-- Due Date -->
          <div class="grid grid-cols-12 items-center gap-4">
            <div class="col-span-12 sm:col-span-4 lg:col-span-3 flex items-center gap-3 text-gray-700 font-medium">
              <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center text-purple-600">
                <Icon icon="lucide:calendar" width="16" />
              </div>
              <p>Due Date <span class="text-red-400 font-normal text-sm">*</span></p>
            </div>
            <div class="col-span-12 sm:col-span-8 lg:col-span-9 flex items-center">
              <div class="relative w-56" v-if="!form.due_date">
              <Calendar v-model="form.due_date" :minDate="minDate" dateFormat="yy-mm-dd" showIcon :pt="{
                root: { class: 'bg-slate-50 rounded-2xl flex items-center overflow-hidden' },
                input: { class: 'bg-transparent py-1.5 pl-3 border-none outline-none w-full text-sm cursor-pointer', placeholder: 'Set deadline' },
                dropdownButton: { root: { class: 'bg-transparent py-1.5 pr-3 border-none text-gray-500 cursor-pointer' } }
              }" />
            </div>

            <div
              class="flex items-center gap-2 h-fit bg-[#f8f8f9] p-2 py-1 rounded-3xl border border-solid border-gray-300"
              v-else>
              <p class="text-xs w-fit">{{ formattedDate }}</p>
                <div class="bg-white border border-gray-200 rounded-full p-1 text-sm shadow-sm hover:bg-red-50 hover:border-red-200 transition-colors">
                  <Icon icon="clarity:times-line" class="cursor-pointer text-gray-400 hover:text-red-500 transition-colors" @click="form.due_date = null" width="14" />
                </div>
              </div>
            </div>
          </div>
          <div v-if="form.errors.due_date" class="text-red-400 text-sm ml-[25%]">{{ form.errors.due_date }}</div>
          <Divider class="!m-0 border-gray-100" />

          <!-- Priority -->
          <div class="grid grid-cols-12 items-center gap-4">
            <div class="col-span-12 sm:col-span-4 lg:col-span-3 flex items-center gap-3 text-gray-700 font-medium">
              <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center text-orange-500">
                <Icon icon="lucide:flag" width="16" />
              </div>
              <p>Priority <span class="text-red-400 font-normal text-sm">*</span></p>
            </div>
            <div class="col-span-12 sm:col-span-8 lg:col-span-9 relative flex items-center">
              <template v-if="!priority">
                <div class="flex bg-slate-50 hover:bg-slate-200 transition-colors py-1.5 px-3 rounded-2xl items-center gap-2 cursor-pointer" @click="select_Priority = true">
                  <Icon icon="lucide:plus" />
                  <p>Set Priority</p>
                </div>

                <div class="z-50 absolute top-12 bg-white border flex items-center gap-5 p-5 rounded-xl shadow-lg"
                  v-if="select_Priority">
                  <div v-for="(prio, index) in Priorities" :key="index" class="cursor-pointer hover:opacity-80 transition-opacity" @click="updatePriority(prio)">
                    <p class="bg-[#FEFBF6] px-5 py-1.5 rounded-3xl cursor-pointer" :style="{
                      color:
                        prio === 'Low'
                          ? '#FFBC53'
                          : prio === 'High'
                            ? '#FF3737'
                            : prio === 'Medium'
                              ? '#F67E5A'
                              : 'black',
                    }">
                      {{ prio }}
                    </p>
                  </div>
                </div>
              </template>

              <template v-else>
                <div class="bg-[#FEFBF6] flex items-center gap-3 px-5 py-1.5 rounded-3xl cursor-pointer" :style="{
                  color:
                    priority === 'Low'
                      ? '#FFBC53'
                      : priority === 'High'
                        ? '#FF3737'
                        : priority === 'Medium'
                          ? '#F67E5A'
                          : 'black',
                }">
                  <p class="text-xs w-fit">{{ priority }}</p>
                  <div class="ml-2 bg-white border border-transparent rounded-full p-1 text-sm shadow-sm hover:bg-red-50 hover:border-red-200 transition-colors">
                    <Icon icon="clarity:times-line" class="cursor-pointer text-gray-400 hover:text-red-500 transition-colors" @click="priority = ''" width="14" />
                  </div>
                </div>
              </template>
            </div>
          </div>
          <div v-if="form.errors.priority" class="text-red-400 text-sm ml-[25%]">{{ form.errors.priority }}</div>
        </div>

        <!-- Description & Quick Presets -->
        <div class="mt-8">
          <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-3 text-gray-700 font-medium">
              <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                <Icon icon="lucide:align-left" width="16" />
              </div>
              <p>Task Directives & Instructions</p>
            </div>
            <span class="text-xs text-gray-400 font-medium">Click preset to populate</span>
          </div>

          <!-- Quick Directive Chips -->
          <div class="flex flex-wrap gap-2 mb-3">
            <button 
              type="button" 
              v-for="(preset, i) in assignmentDirectives" 
              :key="i"
              @click="applyDirectivePreset(preset)"
              class="text-xs px-3 py-1.5 rounded-xl border transition-all cursor-pointer select-none font-medium text-left"
              :class="form.description.includes(preset) ? 'bg-emerald-50 border-emerald-300 text-emerald-700 shadow-2xs font-semibold' : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100 hover:border-gray-300'"
            >
              {{ preset }}
            </button>
          </div>

          <textarea v-model="form.description" cols="30" rows="4" placeholder="Add detailed instructions for this task assignment..."
            class="bg-gray-50 border border-gray-200 focus:bg-white focus:border-blue-400 focus:ring-4 focus:ring-blue-50 transition-all outline-none resize-y p-4 rounded-2xl w-full text-gray-700 shadow-inner min-h-[100px] text-sm">
          </textarea>
        </div>

        <div class="mt-6 flex flex-col items-center justify-center border border-amber-200 bg-amber-50 rounded-xl p-5 shadow-sm">
          <div class="flex items-center gap-2 mb-3 text-amber-700 font-semibold">
            <Icon icon="lucide:pen-tool" width="20" />
            <p>Digital Signature Approval</p>
          </div>
          <p class="text-sm text-gray-600 text-center mb-4">Enter your PIN to digitally sign and assign this task.</p>
          <div class="w-48">
            <InputText id="approval_pin" v-model="form.approval_pin" type="password"
              placeholder="****" class="w-full text-center tracking-widest text-2xl font-bold bg-white"
               />
          </div>
          <div v-if="form.errors.approval_pin" class="text-red-400 text-sm mt-2 text-center font-medium">{{ form.errors.approval_pin }}</div>
        </div>

      </section>
    </section>
  </Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed, ref, watch, onMounted } from "vue";
import { useToast } from "primevue/usetoast";
import { useForm, usePage, router } from "@inertiajs/vue3";
import debounce from 'lodash.debounce'
import { onClickOutside } from '@vueuse/core'
import MultiSelect from 'primevue/multiselect';
import axios from 'axios';
import { Icon } from '@iconify/vue';

const selectedType = ref(null);
const select_Priority = ref(false);
const toggleRecipient = ref(false);
const date = ref();
// minimum selectable date for Calendar (today)
const minDate = ref(new Date());
const page = usePage()
const assigned_by_img = ref(null);
const toast = useToast()

const users = ref([]);
const Assigned_by = ref("");
const search = ref('')
const user_ids = ref([]);
const selectedUsers = ref([])
const is_dropdown = ref(false)
const search_type = ref('')
const pop_up = ref(null)
const clickOutside = ref(false)
const branch = ref([])

const props = defineProps({
  modelValue: Boolean,
  task: Object
});

onClickOutside(pop_up, () => {
  clickOutside.value = true;
})

const setIsDropdown = () => {
  is_dropdown.value = true
  clickOutside.value = false
  searchUsers()
}

const form = useForm({
  task_id: '',
  due_date: '',
  priority: '',
  description: '',
  approval_pin: '',
  workflow_version_id: '',
  users: []
})

const assignmentType = ref('custom');

const assignmentDirectives = [
  'For your necessary action and report back (FNA).',
  'Please study, verify against ledger, and advise.',
  'Approved as recommended. Prepare response/draft.',
  'Urgent: Treat with priority and submit today.',
  'For information and records (FIAR).'
];

const applyDirectivePreset = (preset) => {
  if (!form.description) {
    form.description = preset;
  } else if (!form.description.includes(preset)) {
    form.description = form.description + '\n' + preset;
  }
};

watch(() => props.task, (newTask) => {
  if (newTask) {
    form.task_id = newTask.id;
    form.due_date = newTask.due_date || '';
    if(newTask.priority){
        priority.value = newTask.priority.charAt(0).toUpperCase() + newTask.priority.slice(1);
        form.priority = newTask.priority;
    } else {
        priority.value = "";
        form.priority = "";
    }
  }
}, { immediate: true });

function getImageForGroup(user) {
    if (user.first_name) {
       if(user.profile_image){
        return page.props.base + "/" + user.profile_image;
       }else{
        return page.props.base + "/images/user.svg";
       }
    }
    if(search_type.value == 'unit'){
      return page.props.base + "/images/unit_active.svg";
    }else if(search_type.value == 'division'){
      return page.props.base + "/images/division_active.svg";
    }else if(search_type.value == 'department'){
       return page.props.base + "/images/department_active.svg";
    }else{
       return page.props.base + "/images/user.svg";
    }
}

const usersList = ref([]);
const workflowsList = ref([]);

const fetchWorkflows = () => {
  axios.get('/dashboard/available-workflows').then((response) => {
    workflowsList.value = response.data || [];
  }).catch((err) => {
    console.error('Failed to fetch workflows', err);
  });
};

const fetchRecipients = () => {
  axios.get('/dashboard/search-users', { params: { search: '' } }).then((response) => {
    search_type.value = response.data.type;
    usersList.value = response.data.data.map(item => {
       item.display_name = item.display_title || item.title || (item.first_name + ' ' + item.last_name);
       return item;
    });
  })
};

onMounted(() => {
  fetchRecipients();
  fetchWorkflows();
});

const updateUsersMultiSelect = (e) => {
    form.users = (e.value || []).map(user => {
       if (user.user && user.user.length >= 0) {
           user.users = user.user;
       }
       return user;
    });
};

watch(selectedUsers, (val) => {
    form.users = (val || []).map(user => {
       if (user.user && user.user.length >= 0) {
           user.users = user.user;
       }
       return user;
    });
}, { deep: true });

const formatDate = (value) => {
  if (!value) return '';
  const d = value instanceof Date ? value : new Date(value);
  if (isNaN(d.getTime())) return '';
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, "0");
  const day = String(d.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
};

const formattedDate = computed(() => formatDate(form.due_date));

watch(() => form.due_date, (val) => {
  if (val instanceof Date) {
    form.due_date = formatDate(val);
  }
});
const priority = ref("");
const Priorities = ref(["Low", "Medium", "High"]);

const updatePriority = (event) => {
  priority.value = event;
  if (priority.value == 'Low') {
    form.priority = 1
  } else if (priority.value == 'Medium') {
    form.priority = 2
  } else if (priority.value == 'High') {
    form.priority = 3
  }
  select_Priority.value = false
};


const emit = defineEmits(["update:modelValue", "assigned"]);
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

const submit = () => {
  if (form.users.length === 0) {
    toast.add({
      severity: "error",
      summary: '',
      detail: "Please assign the task to at least one user/department.",
      life: 3000,
    });
    return false;
  }
  if (form.due_date == '') {
    toast.add({
      severity: "error",
      summary: '',
      detail: "Due date field cannot be empty",
      life: 3000,
    });
    return false;
  }

  if (form.priority == '') {
    toast.add({
      severity: "error",
      summary: '',
      detail: "Priority field cannot be empty",
      life: 3000,
    });
    return false;
  }

  form.post('/dashboard/assign-task', {
    onSuccess: page => {
      if (page.props?.flash?.error || page.props?.errors?.error) {
        toast.add({
          severity: "error",
          summary: 'Error',
          detail: page.props?.flash?.error || page.props?.errors?.error,
          life: 4000,
        });
        return;
      }
      form.reset()
      selectedUsers.value = []
      emit("update:modelValue", false);
      emit("assigned");
      toast.add({
        severity: "success",
        summary: '',
        detail: "Task assigned successfully",
        life: 3000,
      });
      if (typeof window !== 'undefined' && window.location.pathname.includes('/view-task')) {
        router.visit('/dashboard/workflow');
      }
    },
    onError: errors => {
      toast.add({
        severity: "error",
        summary: 'Error',
        detail: Object.values(errors)[0] || "Failed to assign task.",
        life: 4000,
      });
    }
  })

};
</script>
<style scoped>
.custom-dialog .p-dialog-header {
  display: none;
  width: 20%;
}

.activeButton {
  background-color: white;
  border-radius: 5px;
  color: black;
  box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
}
</style>

