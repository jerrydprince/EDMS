<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    root: { class: 'bg-white rounded-2xl shadow-2xl overflow-hidden' },
    content: { class: 'p-8 max-h-[80vh] overflow-auto hide_scroll' },
    header: { class: 'bg-white px-8 py-5 border-b' },
    footer: { class: 'bg-gray-50 px-8 py-4 border-t rounded-b-2xl' },
  }" :style="{ width: '60rem' }" :breakpoints="{ '1199px': '75vw', '575px': '95vw' }"
    class="custom-dialog">
    <template #header>
      <div class="flex items-center justify-between w-full">
        <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Assign Task</h2>
        <div class="flex gap-4 items-center">
          <Icon icon="grommet-icons:attachment" :class="{ 'hidden': buttonSwitch === 3 }" width="20" @click="getFile" class="cursor-pointer text-gray-400 hover:text-gray-700 transition-colors" />
          <Icon icon="mingcute:close-line" width="24" @click="closeModal" class="cursor-pointer text-gray-400 hover:text-red-500 transition-colors" />
        </div>
      </div>
      <input type="file" name="" @change="handleFileUpload" id="fileUpload" multiple class="hidden">
    </template>

    <template #footer>
      <div class="flex justify-end gap-3 w-full">
        <button class="px-5 py-2 text-gray-600 font-medium rounded-xl hover:bg-gray-200 transition-colors cursor-pointer" @click="closeModal">Cancel</button>
        <button class="px-6 py-2 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition-colors shadow-md shadow-indigo-200 cursor-pointer" @click="submit">
          Assign Task
        </button>
      </div>
    </template>

    <section class="flex flex-col gap-6">
      <div class="flex flex-col">
        <input type="text" id="task_title" class="w-full text-4xl font-semibold text-gray-800 outline-none placeholder-gray-300 tracking-tight" placeholder="Task Title..." v-model="form.title">
        <div v-if="form.errors.title" class="text-red-500 text-sm mt-1">{{ form.errors.title }}</div>
      </div>

      <div class="bg-gray-100 flex items-center rounded-xl p-1 w-fit border border-gray-200">
        <button class="rounded-lg py-2 px-5 text-sm font-medium transition-colors cursor-pointer" @click="activeButton('')" :class="buttonSwitch === 1 ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-500 hover:bg-gray-200'">
          Document + Instruction
        </button>
        <button class="rounded-lg py-2 px-5 text-sm font-medium transition-colors cursor-pointer" @click="activeButton('document_only')" :class="buttonSwitch === 2 ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-500 hover:bg-gray-200'">
          Document Only
        </button>
        <button class="rounded-lg py-2 px-5 text-sm font-medium transition-colors cursor-pointer" @click="activeButton('text_only')" :class="buttonSwitch === 3 ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-500 hover:bg-gray-200'">
          Instruction Only
        </button>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-2">
        <!-- Main Form Area -->
        <div class="col-span-1 lg:col-span-2 flex flex-col gap-8">
          
          <div class="flex flex-col gap-3" :class="{ 'hidden': buttonSwitch === 2 }">
            <div class="flex items-center gap-3 text-gray-700 font-semibold text-lg">
              <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                <Icon icon="lucide:align-left" width="16" />
              </div>
              <h3>Task Description</h3>
            </div>
            <textarea v-model="form.description" cols="30" rows="5" placeholder="Add detailed instructions for this task..."
              class="bg-gray-50 border border-gray-200 focus:bg-white focus:border-indigo-400 focus:ring-4 focus:ring-indigo-50 transition-all outline-none resize-y p-5 rounded-2xl w-full text-gray-700 shadow-inner min-h-[150px]">
            </textarea>
            <div v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}</div>
          </div>

          <div class="flex flex-col gap-4 mt-2" :class="{ 'hidden': buttonSwitch === 3 }">
            <div class="flex items-center justify-between border-b border-gray-200 pb-2">
               <div class="flex items-center gap-3 text-gray-700 font-semibold text-lg">
                  <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600">
                    <Icon icon="lucide:paperclip" width="16" />
                  </div>
                  <h3>Attached Documents</h3>
               </div>
            </div>

            <!-- Existing Correspondence Mail Attachments -->
            <div v-if="props.files && props.files.length > 0" class="w-full flex flex-col gap-3 mt-2">
              <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">From Mail</h4>
              <div class="w-full grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-center gap-4 bg-white border border-gray-200 p-3 rounded-xl shadow-sm relative group" v-for="file in props.files" :key="file.id">
                  <div class="shrink-0 w-12 h-12 rounded-lg bg-gray-50 flex items-center justify-center border border-gray-100 overflow-hidden p-1.5">
                    <img :src="getFileURLExt(file.path)" class="max-w-full max-h-full object-contain" alt="File Preview" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800 truncate pr-6" :title="getFileName(file)">{{ getFileName(file) }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ file.size || 'Unknown size' }}</p>
                  </div>
                  <div class="absolute top-2 right-2 w-6 h-6 rounded-full bg-gray-50 hover:bg-red-50 flex items-center justify-center cursor-pointer transition-colors opacity-0 group-hover:opacity-100" @click="removeImage(file.id)">
                    <Icon icon="lucide:x" width="14" class="text-gray-400 hover:text-red-500" />
                  </div>
                </div>
              </div>
            </div>

            <!-- Newly Uploaded Files -->
            <div class="w-full flex flex-col gap-3 mt-4" v-if="files && files.length > 0">
               <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">New Attachments</h4>
               <div class="w-full grid grid-cols-1 md:grid-cols-2 gap-4">
                 <div class="flex items-center gap-4 bg-white border border-gray-200 p-3 rounded-xl shadow-sm relative group" v-for="(file, index) in files" :key="index">
                   <div class="shrink-0 w-12 h-12 rounded-lg bg-gray-50 flex items-center justify-center border border-gray-100 overflow-hidden">
                     <img :src="getFileURL(file)" class="w-full h-full object-cover" alt="File Preview" />
                   </div>
                   <div class="flex-1 min-w-0">
                     <p class="text-sm font-semibold text-gray-800 truncate pr-6">{{ file.name }}</p>
                     <div class="text-xs text-gray-500 mt-1 flex items-center gap-2" v-if="form.progress">
                       <span class="font-medium text-indigo-600">{{ Math.round(form.progress.loaded / 1024) }} KB</span> of {{ formatSize(file.size) }}
                       <div aria-label="Loading..." role="status" class="flex items-center gap-1.5 ml-2 bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded-full">
                         <Icon icon="lucide:loader" class="animate-spin" width="12" />
                         <span class="text-[10px] font-semibold uppercase tracking-wider">Loading</span>
                       </div>
                     </div>
                   </div>
                   <div v-if="form.progress" class="w-full bg-gray-100 rounded-full h-1 mt-3 overflow-hidden relative col-span-2 absolute bottom-0 left-0">
                     <div class="bg-indigo-500 h-1 rounded-full transition-all duration-300" :style="{ width: form.progress.percentage + '%' }"></div>
                   </div>
                   <div class="absolute top-2 right-2 flex gap-1 group-hover:opacity-100 opacity-0 transition-opacity">
                     <div class="w-6 h-6 rounded-full bg-gray-50 hover:bg-blue-50 flex items-center justify-center cursor-pointer transition-colors shadow-sm" @click.stop="openPreview(file)" title="Preview">
                       <Icon icon="ph:eye" width="14" class="text-gray-400 hover:text-blue-500" />
                     </div>
                     <div class="w-6 h-6 rounded-full bg-gray-50 hover:bg-green-50 flex items-center justify-center cursor-pointer transition-colors shadow-sm" @click.stop="downloadAttachedFile(file)" title="Download">
                       <Icon icon="material-symbols-light:download" width="14" class="text-gray-400 hover:text-green-500" />
                     </div>
                     <div class="w-6 h-6 rounded-full bg-gray-50 hover:bg-red-50 flex items-center justify-center cursor-pointer transition-colors shadow-sm" @click.stop="removeFile(index)" title="Remove">
                       <Icon icon="lucide:x" width="14" class="text-gray-400 hover:text-red-500" />
                     </div>
                   </div>
                 </div>
               </div>
            </div>

            <!-- Attachment Buttons -->
            <div class="flex flex-wrap gap-4 mt-2">
              <div class="flex flex-col items-center justify-center p-4 border-dashed border-[2px] border-gray-300 rounded-xl cursor-pointer hover:bg-indigo-50 hover:border-indigo-300 transition-colors flex-1 min-w-[120px] max-w-[160px] text-center group shadow-sm" @click="getFile">
                <Icon icon="lucide:monitor-up" width="24" class="text-gray-400 group-hover:text-indigo-500 mb-2 transition-colors" />
                <p class="text-xs font-semibold text-gray-600 group-hover:text-indigo-700">From System</p>
              </div>
              
              <div class="flex flex-col items-center justify-center p-4 border-dashed border-[2px] border-gray-300 rounded-xl cursor-pointer hover:bg-indigo-50 hover:border-indigo-300 transition-colors flex-1 min-w-[120px] max-w-[160px] text-center group shadow-sm" @click="scanDocument">
                <Icon icon="lucide:scanner" width="24" class="text-gray-400 group-hover:text-indigo-500 mb-2 transition-colors" />
                <p class="text-xs font-semibold text-gray-600 group-hover:text-indigo-700">Scan Document</p>
              </div>
              
              <div class="flex flex-col items-center justify-center p-4 border-dashed border-[2px] border-gray-300 rounded-xl cursor-pointer hover:bg-indigo-50 hover:border-indigo-300 transition-colors flex-1 min-w-[120px] max-w-[160px] text-center group shadow-sm" @click="openEDMSModal">
                <Icon icon="lucide:folder-search" width="24" class="text-gray-400 group-hover:text-indigo-500 mb-2 transition-colors" />
                <p class="text-xs font-semibold text-gray-600 group-hover:text-indigo-700">From Registry</p>
              </div>
            </div>

          </div>
        </div>

        
        <!-- Sidebar Container -->
        <div class="col-span-1 flex flex-col gap-6">
            <!-- Assignment Type Toggle -->
            <div class="flex flex-col gap-6 bg-gray-50 p-6 rounded-2xl border border-gray-100 h-fit">
               <h3 class="font-semibold text-gray-800 text-lg border-b border-gray-200 pb-3">Assignment Type</h3>
               <div class="flex bg-gray-200 p-1 rounded-xl">
                 <button class="flex-1 py-2 text-sm font-medium rounded-lg transition-colors cursor-pointer" @click="assignmentType = 'simple'" :class="assignmentType === 'simple' ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-500 hover:text-gray-700'">Simple Task</button>
                 <button class="flex-1 py-2 text-sm font-medium rounded-lg transition-colors cursor-pointer" @click="assignmentType = 'workflow'" :class="assignmentType === 'workflow' ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-500 hover:text-gray-700'">Workflow Template</button>
               </div>
            </div>

            <div class="flex flex-col gap-6 bg-gray-50 p-6 rounded-2xl border border-gray-100 h-fit" v-if="assignmentType === 'simple'">
               <h3 class="font-semibold text-gray-800 text-lg border-b border-gray-200 pb-3">Task Settings</h3>
               
               <!-- Assignee Field -->
               <div class="flex flex-col gap-2">
                  <label class="text-gray-700 font-medium text-sm flex items-center gap-2">
                     <Icon icon="lucide:users" class="text-blue-500" width="16" /> Assign To <span class="text-gray-400 font-normal text-xs">(required)</span>
                  </label>
                  <MultiSelect
                     v-model="selectedUsers"
                     :options="usersList"
                     optionLabel="display_name"
                     placeholder="Search department / staff"
                     :filter="true"
                     display="chip"
                     :pt="{
                        root: { class: 'w-full text-left bg-white border border-gray-200 rounded-xl hover:border-indigo-400 focus:border-indigo-400 focus:ring-4 focus:ring-indigo-50 transition-all outline-none flex items-center justify-between shadow-sm' },
                        label: { class: 'py-2 px-3 text-sm flex-1' },
                        trigger: { class: 'w-10 flex items-center justify-center text-gray-400' },
                        panel: { class: 'bg-white shadow-xl rounded-xl border border-gray-100 mt-2 p-1 overflow-hidden' },
                        header: { class: 'p-2 border-b border-gray-100 flex items-center justify-between gap-2' },
                        headerCheckboxContainer: { class: 'flex items-center justify-center mr-2' },
                        headerCheckbox: { class: 'w-4 h-4 border border-gray-300 rounded cursor-pointer flex items-center justify-center' },
                        closeButton: { class: 'w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-full transition-colors' },
                        filterContainer: { class: 'relative w-full flex items-center' },
                        filterInput: { class: 'w-full pl-9 pr-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all outline-none' },
                        filterIcon: { class: 'text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 pointer-events-none' },
                        wrapper: { class: 'max-h-[250px] overflow-auto py-1' },
                        item: ({ context }) => ({ class: ['px-3 py-2.5 hover:bg-gray-50 transition-colors flex flex-row items-center gap-3 rounded-lg mx-1 cursor-pointer whitespace-nowrap', context.selected ? 'bg-indigo-50/50' : ''] }),
                        itemCheckbox: { class: 'w-4 h-4 border border-gray-300 rounded cursor-pointer flex-shrink-0 flex items-center justify-center' }
                     }"
                     @change="updateUsersMultiSelect"
                   >
                     <template #option="slotProps">
                       <div class="flex items-center gap-3">
                         <img :src="getImageForGroup(slotProps.option)" class="w-6 h-6 rounded-full object-cover" />
                         <p class="text-sm font-semibold">{{ slotProps.option.display_name }}</p>
                       </div>
                     </template>
                  </MultiSelect>
                  <div v-if="form.errors.users" class="text-red-500 text-xs mt-1">{{ form.errors.users }}</div>
               </div>
               
               <!-- Due Date Field -->
               <div class="flex flex-col gap-2">
                  <label class="text-gray-700 font-medium text-sm flex items-center gap-2">
                     <Icon icon="lucide:calendar" class="text-purple-500" width="16" /> Due Date
                  </label>
                  <div class="w-full" v-if="!form.due_date">
                    <Calendar v-model="form.due_date" dateFormat="yy-mm-dd" showIcon :pt="{
                      root: { class: 'bg-white border border-gray-200 rounded-xl flex items-center overflow-hidden hover:border-indigo-400 focus-within:border-indigo-400 focus-within:ring-4 focus-within:ring-indigo-50 transition-all shadow-sm' },
                      input: { class: 'bg-transparent py-2 pl-3 border-none outline-none w-full text-sm cursor-pointer text-gray-700' },
                      dropdownButton: { root: { class: 'bg-transparent py-2 pr-3 border-none text-gray-500 cursor-pointer' } }
                    }" />
                  </div>
                  <div class="flex items-center gap-2 h-fit bg-white p-2 py-1.5 rounded-xl border border-gray-200 justify-between w-full shadow-sm" v-else>
                    <p class="text-sm font-semibold text-gray-700 px-2">{{ formattedDate }}</p>
                    <div class="bg-gray-50 border border-gray-100 rounded-full p-1.5 text-sm hover:bg-red-50 hover:border-red-200 transition-colors cursor-pointer" @click="form.due_date = null">
                      <Icon icon="clarity:times-line" class="text-gray-400 hover:text-red-500 transition-colors" width="14" />
                    </div>
                  </div>
                  <div v-if="form.errors.due_date" class="text-red-500 text-xs mt-1">{{ form.errors.due_date }}</div>
               </div>
               
               <!-- Priority Field -->
               <div class="flex flex-col gap-2">
                  <label class="text-gray-700 font-medium text-sm flex items-center gap-2">
                     <Icon icon="lucide:flag" class="text-orange-500" width="16" /> Priority
                  </label>
                  <div class="grid grid-cols-3 gap-2 w-full">
                     <button v-for="p in Priorities" :key="p" @click="updatePriority(p)" class="py-2 text-sm font-semibold rounded-xl border transition-all text-center cursor-pointer shadow-sm" 
                        :class="priority === p ? 
                          (p === 'High' ? 'bg-red-50 border-red-300 text-red-600 ring-2 ring-red-100' : 
                           p === 'Medium' ? 'bg-orange-50 border-orange-300 text-orange-600 ring-2 ring-orange-100' : 
                           'bg-yellow-50 border-yellow-300 text-yellow-600 ring-2 ring-yellow-100') 
                          : 'bg-white border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-gray-700'">
                        {{ p }}
                     </button>
                  </div>
                  <div v-if="form.errors.priority" class="text-red-500 text-xs mt-1">{{ form.errors.priority }}</div>
               </div>
               
            </div>

            <div class="flex flex-col gap-6 bg-gray-50 p-6 rounded-2xl border border-gray-100 h-fit" v-if="assignmentType === 'workflow'">
               <h3 class="font-semibold text-gray-800 text-lg border-b border-gray-200 pb-3">Select Workflow</h3>
               <div class="flex flex-col gap-2">
                  <label class="text-gray-700 font-medium text-sm flex items-center gap-2">
                     <Icon icon="lucide:git-merge" class="text-indigo-500" width="16" /> Workflow Template <span class="text-gray-400 font-normal text-xs">(required)</span>
                  </label>
                  <select v-model="form.workflow_version_id" class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 outline-none focus:border-indigo-400 focus:ring-4 focus:ring-indigo-50 transition-all text-sm">
                    <option value="" disabled>Select a workflow</option>
                    <option v-for="wf in workflowsList" :key="wf.id" :value="wf.id">{{ wf.workflow ? wf.workflow.title : 'Workflow' }} (v{{ wf.version_number }})</option>
                  </select>
               </div>
            </div>
            
            <!-- PIN Signature -->
            <div class="mt-6 flex flex-col items-center justify-center border border-amber-200 bg-amber-50 rounded-xl p-5 shadow-sm col-span-1 xl:col-span-2 w-full">
                <div class="flex items-center gap-2 mb-3 text-amber-700 font-semibold">
                    <Icon icon="lucide:pen-tool" width="20" />
                    <p>Digital Signature Approval</p>
                </div>
                <p class="text-sm text-gray-600 text-center mb-4">Enter your PIN to digitally sign and initialize this task.</p>
                <div class="w-48">
                    <InputText id="approval_pin" v-model="form.approval_pin" type="password"
                        placeholder="****" class="w-full text-center tracking-widest text-2xl font-bold bg-white" />
                </div>
                <div v-if="form.errors.approval_pin" class="text-red-400 text-sm mt-2 text-center font-medium">{{ form.errors.approval_pin }}</div>
            </div>

        </div>
      </div>

    </section>
  </Dialog>
  <!-- Document Preview Modal -->
  <Dialog v-model:visible="previewVisible" header="Document Preview" modal :style="{ width: '50rem' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
    <div class="flex flex-col items-center justify-center p-4 bg-gray-50 rounded-lg min-h-[400px]">
      <img v-if="previewFile && isImage(previewFile)" :src="previewFileUrl" class="max-w-full max-h-[600px] object-contain shadow-md rounded" />
      <object v-else-if="previewFile && isPDF(previewFile)" :data="previewFileUrl" type="application/pdf" width="100%" height="600px" class="rounded shadow-md">
        <p>PDF preview is not available in your browser. <a :href="previewFileUrl" target="_blank" class="text-primary">Download it here</a>.</p>
      </object>
      <div v-else class="text-center text-gray-500">
        <Icon icon="ph:file-x" width="64" class="mx-auto mb-4 text-gray-300" />
        <p>Preview is not available for this file type.</p>
        <p class="text-sm mt-2">{{ previewFile?.name }}</p>
      </div>
    </div>
  </Dialog>
</template>
  <script setup>
  import { defineEmits, defineProps, computed, ref, onMounted } from "vue";
  import image from "../../../assets/images/members/1.svg";
  import { useToast } from "primevue/usetoast";
  import { useForm, usePage, router } from "@inertiajs/vue3";
  import debounce from 'lodash.debounce'
  import { onClickOutside } from '@vueuse/core'
  import MultiSelect from 'primevue/multiselect';
  
  const selectedType = ref(null);
  const select_Priority = ref(false);
  const toggleRecipient = ref(false);
  const date = ref();
  const uses = ref([])
  const files = ref([])
  const page = usePage()
  const assigned_by_img = ref(null);
  const toast = useToast()
  
  const users = ref([]);
  const assignmentType = ref('simple');
  const workflowsList = ref([]);
  const Assigned_by = ref("");
  const buttonSwitch = ref(1);
  const clickedButton = ref("");
  const search = ref('')
  const user_ids = ref([]);
  const selectedUsers = ref([])
  const is_dropdown = ref(false)
  let is_dropwdown_blur = ref(false)
  const search_type = ref('')
  const pop_up = ref(null)
  const clickOutside = ref(false)
  
  const props = defineProps(["modelValue", 'files', 'correspondence_id', 'correspondence']);
  
  onClickOutside(pop_up, () => {
    clickOutside.value = true;
  })
  
  const setIsDropdown = () => {
    is_dropdown.value = true
    is_dropwdown_blur = false
  }
  
  
const previewVisible = ref(false);
const previewFile = ref(null);
const previewFileUrl = ref('');

const openPreview = (file) => {
  previewFile.value = file;
  if (file.type) {
    previewFileUrl.value = URL.createObjectURL(file);
  } else {
    const extension = file.name.split('.').pop();
    const mimeTypes = {
      'jpg': 'image/jpeg', 'jpeg': 'image/jpeg', 'png': 'image/png', 'gif': 'image/gif',
      'pdf': 'application/pdf'
    };
    previewFileUrl.value = URL.createObjectURL(new Blob([file], { type: mimeTypes[extension] || 'application/octet-stream' }));
  }
  previewVisible.value = true;
};

const isImage = (file) => {
  const t = file.type || '';
  if (t.startsWith('image/')) return true;
  const ext = file.name.split('.').pop().toLowerCase();
  return ['jpg', 'jpeg', 'png', 'gif'].includes(ext);
};

const isPDF = (file) => {
  const t = file.type || '';
  if (t === 'application/pdf') return true;
  const ext = file.name.split('.').pop().toLowerCase();
  return ext === 'pdf';
};

const downloadAttachedFile = (file) => {
  const url = URL.createObjectURL(file);
  const a = document.createElement('a');
  a.href = url;
  a.download = file.name;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
};

const form = useForm({
    files: [],
    title: '',
    due_date: '',
    priority: '',
    description: '',
    users: [],
    workflow_version_id: '',
    extra_files: [],
    is_from_correspondece: true,
    correspondence_id: props.correspondence_id,
    approval_pin: ''
  })

  const getFileURLExt = (url) => {
  // const parts = url.split('.');
  // let extension = parts.length > 1 ? parts.pop().split(/#|\?/)[0] : '';
  const extension = url.split('.').pop();

  if (extension == 'jpg' || extension == 'jpeg' || extension == 'gif' || extension == 'svg' || extension == 'png') {
    let file = page.props.base+url
      return file
   // return URL.createObjectURL(file);
  }

  if (extension == 'pdf') {
    return page.props.base + '/images/pdf.svg'
  }

  if (extension == 'doc' || extension == 'docx') {
    return page.props.base + '/images/word.svg'
  }

  if (extension == 'xls' || extension == 'xlsx') {
    return page.props.base + '/images/excel.svg'
  }

}
  
  const getFile = () => {
    let file = document.getElementById('fileUpload');
    file.click();
  }

  const scanDocument = () => {
    toast.add({
      severity: "info",
      summary: "Scanner Connection",
      detail: "Attempting to connect to local scanner hardware...",
      life: 4000,
    });
  }

  const edmsModalVisible = ref(false);
  const openEDMSModal = () => {
    toast.add({
      severity: "info",
      summary: "Document Management",
      detail: "Opening EDMS file registry...",
      life: 3000,
    });
    edmsModalVisible.value = true;
  }
  
  const formatSize = (size) => {
    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    let i = 0;
    while (size >= 1024) {
      size /= 1024;
      i++;
    }
    return size.toFixed(2) + ' ' + units[i];
  }
  
  const handleFileUpload = (event) => {
    files.value = Array.from(event.target.files);
    form.files = Array.from(event.target.files);
    //uploadFiles()
  }

  const removeFile = (index) => {
    files.value.splice(index, 1);
    form.files.splice(index, 1);
  }
  
  
  const activeButton = (event) => {
    if (event === "") {
      buttonSwitch.value = 1;
    }
    if (event === "document_only") {
      buttonSwitch.value = 2;
    }
    if (event === "text_only") {
      buttonSwitch.value = 3;
    }
    clickedButton.value = event;
  };

  const removeImage = (id)=>{
  axios.get('/dashboard/remove-task-image', {params: {id: id}}).then((response)=>{
    if(response.data){
      emit('refresh')
    }
  })
}
  
  const getFileURL = (file) => {
    if (file.type) {
      if (file.type == 'image/jpeg' || file.type == 'image/jpeg' || file.type == 'image/png' || file.type == 'image/gif') {
        return URL.createObjectURL(file);
      }
  
      if (file.type == 'application/pdf') {
        return page.props.base + '/images/pdf.svg'
      }
  
      if (file.type == 'application/msword' || file.type == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
        return page.props.base + '/images/word.svg'
      }
  
      if (file.type == 'application/vnd.ms-excel' || file.type == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
        return page.props.base + '/images/excel.svg'
      }
    } else {
      // If type is not available, manually determine file type based on extension
      const extension = file.name.split('.').pop();
      const mimeTypes = {
        'jpg': 'image/jpeg',
        'jpeg': 'image/jpeg',
        'png': 'image/png',
        'gif': 'image/gif',
        'pdf': 'application/pdf',
        'doc': 'application/msword',
        'docx': 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls': 'application/vnd.ms-excel',
        'xlsx': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      };
  
      return URL.createObjectURL(new Blob([file], { type: mimeTypes[extension] || 'application/octet-stream' }));
    }
  }

  const getFileName = (file)=>{
    if(file.path){
      const parts = file.path.split('/');
      const lastPart = parts[parts.length - 1];
      return lastPart
    }
  }
  
  
  const usersList = ref([]);

  
  const fetchWorkflows = () => {
    axios.get('/dashboard/workflows').then((response) => {
      workflowsList.value = response.data.data;
    });
  };

  const fetchRecipients = () => {
    axios.get('/dashboard/search-users', { params: { search: '' } }).then((response) => {
      search_type.value = response.data.type;
      usersList.value = response.data.data.map(item => {
         item.display_name = item.title || (item.first_name + ' ' + item.last_name);
         return item;
      });
    })
  };

  onMounted(() => {
    fetchRecipients();
    fetchWorkflows();
  });

  const updateUsersMultiSelect = (e) => {
    form.users = e.value.map(user => {
       if (user.user && user.user.length >= 0) {
           user.users = user.user;
       }
       return user.id;
    });
  };

  function getImageForGroup(user) {
    if(search_type.value == 'unit'){
      return page.props.base + "/images/unit_active.svg";
    }else if(search_type.value == 'division'){
      return page.props.base + "/images/division_active.svg";
    }else if(search_type.value == 'department'){
       return page.props.base + "/images/department_active.svg";
    }else{
       if(user.profile_image){
        return page.props.base + "/" + user.profile_image;
       }else{
        return page.props.base + "/images/user.svg";
       }
    }
  }

  const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return '';
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");
    form.due_date = `${year}-${month}-${day}`
    return `${year}-${month}-${day}`;
  };
  
  const formattedDate = computed(() => formatDate(form.due_date));
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
  
  const type = ref([
    'Document Upload + Text comment', 'Document Only', 'Text Only'
  ])
  
  const emit = defineEmits(["update:modelValue"]);
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
    form.extra_files = props.files;
    form.post('/dashboard/create-task', {
      onSuccess: page => {
        form.reset()
        emit("update:modelValue", false);
        router.reload({only:['correspondence']})
        toast.add({
          severity: "success",
          summary: '',
          detail: "Task created successfully",
          life: 3000,
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
