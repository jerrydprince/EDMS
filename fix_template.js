const fs = require('fs');
let content = fs.readFileSync('resources/js/components/Modals/Workflows/AssignTask.vue', 'utf8');
const newTemplate = `
<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    root: { class: 'bg-white rounded-2xl shadow-2xl overflow-hidden' },
    content: { class: 'p-8 max-h-[75vh] overflow-auto' },
    header: { class: 'bg-white px-8 py-5 border-b' },
    footer: { class: 'bg-gray-50 px-8 py-4 border-t rounded-b-2xl' },
  }" :style="{ width: '60rem' }" :breakpoints="{ '1199px': '75vw', '575px': '95vw' }"
    class="custom-dialog">
    <template #header>
      <div class="flex items-center justify-between w-full">
        <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Create New Task</h2>
        <div class="flex gap-4 items-center">
          <Icon icon="grommet-icons:attachment" :class="{ 'hidden': buttonSwitch === 3 }" width="20" @click="getFile" class="cursor-pointer text-gray-400 hover:text-gray-700 transition-colors" />
          <Icon icon="mingcute:close-line" width="24" @click="closeModal" class="cursor-pointer text-gray-400 hover:text-red-500 transition-colors" />
        </div>
      </div>
      <input type="file" name="" @change="handleFileUpload" id="fileUpload" multiple class="hidden">
    </template>

    <template #footer>
      <div class="flex justify-end gap-3 w-full">
        <button class="px-5 py-2 text-gray-600 font-medium rounded-xl hover:bg-gray-200 transition-colors cursor-pointer" @click="closeModal" :disabled="form.processing">Cancel</button>
        <button class="px-6 py-2 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 transition-colors shadow-md shadow-blue-200 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed" @click="submit" :disabled="form.processing">
          {{ form.processing ? 'Creating...' : 'Create Task' }}
        </button>
      </div>
    </template>

    <section class="flex flex-col gap-6">
      <div class="flex flex-col">
        <input type="text" id="task_title" class="w-full text-4xl font-semibold text-gray-800 outline-none placeholder-gray-300 tracking-tight" placeholder="Task Title..." v-model="form.title">
        <div v-if="form.errors.title" class="text-red-500 text-sm mt-1">{{ form.errors.title }}</div>
      </div>

      <div class="bg-gray-100 flex items-center rounded-xl p-1 w-fit border border-gray-200">
        <button class="rounded-lg py-2 px-5 text-sm font-medium transition-colors cursor-pointer" @click="activeButton('')" :class="buttonSwitch === 1 ? 'bg-white shadow-sm text-blue-600' : 'text-gray-500 hover:bg-gray-200'">
          Document + Instruction
        </button>
        <button class="rounded-lg py-2 px-5 text-sm font-medium transition-colors cursor-pointer" @click="activeButton('document_only')" :class="buttonSwitch === 2 ? 'bg-white shadow-sm text-blue-600' : 'text-gray-500 hover:bg-gray-200'">
          Document Only
        </button>
        <button class="rounded-lg py-2 px-5 text-sm font-medium transition-colors cursor-pointer" @click="activeButton('text_only')" :class="buttonSwitch === 3 ? 'bg-white shadow-sm text-blue-600' : 'text-gray-500 hover:bg-gray-200'">
          Instruction Only
        </button>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-2">
        <div class="col-span-1 lg:col-span-2 flex flex-col gap-6">
          
          <div class="flex flex-col gap-3" :class="{ 'hidden': buttonSwitch === 2 }">
            <div class="flex items-center gap-3 text-gray-700 font-semibold text-lg">
              <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                <Icon icon="lucide:align-left" width="16" />
              </div>
              <h3>Instruction</h3>
            </div>
            <textarea v-model="form.description" cols="30" rows="5" placeholder="Add detailed instructions for this task..."
              class="bg-gray-50 border border-gray-200 focus:bg-white focus:border-blue-400 focus:ring-4 focus:ring-blue-50 transition-all outline-none resize-y p-5 rounded-2xl w-full text-gray-700 shadow-inner min-h-[150px]">
            </textarea>
            <div v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}</div>
          </div>

          <div class="flex flex-col gap-4 mt-2" :class="{ 'hidden': buttonSwitch === 3 }">
            <div class="flex items-center justify-between">
               <div class="flex items-center gap-3 text-gray-700 font-semibold text-lg">
                  <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600">
                    <Icon icon="lucide:paperclip" width="16" />
                  </div>
                  <h3>Attached Documents</h3>
               </div>
               <button @click="getFile" class="flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition-colors text-sm font-medium cursor-pointer">
                 <Icon icon="lucide:plus" width="16" /> Add File
               </button>
            </div>
            <div class="w-full flex flex-wrap gap-4">
              <div class="flex-1 min-w-[250px] max-w-[320px] bg-white border border-gray-200 p-4 rounded-xl shadow-sm relative group" v-for="(file, index) in files" :key="index">
                <div class="flex items-start gap-4">
                  <div class="shrink-0 w-12 h-12 rounded-lg bg-gray-50 flex items-center justify-center border border-gray-100 overflow-hidden">
                    <img :src="getFileURL(file)" class="w-full h-full object-cover" alt="File Preview" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800 truncate pr-6">{{ file.name }}</p>
                    <div class="text-xs text-gray-500 mt-1 flex items-center gap-2" v-if="form.progress">
                      <span class="font-medium text-blue-600">{{ Math.round(form.progress.loaded / 1024) }} KB</span> of {{ formatSize(file.size) }}
                      <div aria-label="Loading..." role="status" class="flex items-center gap-1.5 ml-2 bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full">
                        <Icon icon="lucide:loader" class="animate-spin" width="12" />
                        <span class="text-[10px] font-semibold uppercase tracking-wider">Loading</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="absolute top-3 right-3 w-6 h-6 rounded-full bg-gray-50 hover:bg-red-50 flex items-center justify-center cursor-pointer transition-colors group-hover:opacity-100 opacity-0" @click="removeFile(index)">
                  <Icon icon="lucide:x" width="14" class="text-gray-400 hover:text-red-500" />
                </div>
                <div v-if="form.progress" class="w-full bg-gray-100 rounded-full h-1.5 mt-4 overflow-hidden relative">
                  <div class="bg-blue-500 h-1.5 rounded-full transition-all duration-300" :style="{ width: form.progress.percentage + '%' }"></div>
                </div>
              </div>
              <div class="flex-1 min-w-[250px] border-dashed border-2 border-gray-300 rounded-xl bg-gray-50 hover:bg-blue-50 hover:border-blue-400 cursor-pointer transition-all flex flex-col items-center justify-center p-8 gap-2" @click="getFile">
                <div class="w-12 h-12 rounded-full bg-white shadow-sm flex items-center justify-center text-blue-500 mb-1">
                  <Icon icon="lucide:upload-cloud" width="24" />
                </div>
                <p class="text-sm font-medium text-gray-700">Click to upload files</p>
                <p class="text-xs text-gray-400">PDF, DOC, Images up to 10MB</p>
              </div>
            </div>
          </div>
        </div>

        <div class="col-span-1 flex flex-col gap-6 bg-gray-50 p-6 rounded-2xl border border-gray-100 h-fit">
           <h3 class="font-semibold text-gray-800 text-lg border-b border-gray-200 pb-3">Task Settings</h3>
           
           <div class="flex flex-col gap-2">
              <label class="text-gray-700 font-medium text-sm flex items-center gap-2">
                 <Icon icon="lucide:users" class="text-blue-500" width="16" /> Assign To <span class="text-gray-400 font-normal text-xs">(optional)</span>
              </label>
              <MultiSelect
                 v-model="selectedUsers"
                 :options="usersList"
                 optionLabel="display_name"
                 placeholder="Search department / staff"
                 :filter="true"
                 display="chip"
                 class="w-full text-left bg-white border border-gray-200 rounded-xl hover:border-blue-400 focus:border-blue-400 focus:ring-4 focus:ring-blue-50 transition-all outline-none"
                 @change="updateUsersMultiSelect"
               >
                 <template #option="slotProps">
                   <div class="flex items-center gap-3">
                     <img :src="getImageForGroup(slotProps.option)" class="w-6 h-6 rounded-full object-cover" />
                     <p class="text-sm font-semibold">{{ slotProps.option.display_name }}</p>
                   </div>
                 </template>
              </MultiSelect>
              <div v-if="form.errors.users" class="text-red-400 text-xs">{{ form.errors.users }}</div>
           </div>
           
           <div class="flex flex-col gap-2">
              <label class="text-gray-700 font-medium text-sm flex items-center gap-2">
                 <Icon icon="lucide:calendar" class="text-purple-500" width="16" /> Due Date
              </label>
              <div class="w-full" v-if="!form.due_date">
                <Calendar v-model="form.due_date" :minDate="minDate" dateFormat="yy-mm-dd" showIcon :pt="{
                  root: { class: 'bg-white border border-gray-200 rounded-xl flex items-center overflow-hidden hover:border-blue-400 focus-within:border-blue-400 focus-within:ring-4 focus-within:ring-blue-50 transition-all' },
                  input: { class: 'bg-transparent py-2 pl-3 border-none outline-none w-full text-sm cursor-pointer' },
                  dropdownButton: { root: { class: 'bg-transparent py-2 pr-3 border-none text-gray-500 cursor-pointer' } }
                }" />
              </div>
              <div class="flex items-center gap-2 h-fit bg-white p-2 py-1.5 rounded-xl border border-gray-200 justify-between w-full shadow-sm" v-else>
                <p class="text-sm font-medium px-2">{{ formattedDate }}</p>
                <div class="bg-gray-50 border border-gray-100 rounded-full p-1 text-sm hover:bg-red-50 hover:border-red-200 transition-colors cursor-pointer" @click="form.due_date = null">
                  <Icon icon="clarity:times-line" class="text-gray-400 hover:text-red-500 transition-colors" width="14" />
                </div>
              </div>
              <div v-if="form.errors.due_date" class="text-red-400 text-xs">{{ form.errors.due_date }}</div>
           </div>
           
           <div class="flex flex-col gap-2">
              <label class="text-gray-700 font-medium text-sm flex items-center gap-2">
                 <Icon icon="lucide:flag" class="text-orange-500" width="16" /> Priority
              </label>
              <div class="grid grid-cols-3 gap-2 w-full">
                 <button v-for="p in Priorities" :key="p" @click="priority = p" class="py-2 text-sm font-medium rounded-xl border transition-all text-center cursor-pointer" 
                    :class="priority === p ? 
                      (p === 'High' ? 'bg-red-50 border-red-300 text-red-600 shadow-sm' : 
                       p === 'Medium' ? 'bg-orange-50 border-orange-300 text-orange-600 shadow-sm' : 
                       'bg-yellow-50 border-yellow-300 text-yellow-600 shadow-sm') 
                      : 'bg-white border-gray-200 text-gray-500 hover:bg-gray-100 hover:text-gray-700'">
                    {{ p }}
                 </button>
              </div>
              <div v-if="form.errors.priority" class="text-red-400 text-xs">{{ form.errors.priority }}</div>
           </div>
           
           <div class="flex flex-col gap-2 mt-2 pt-5 border-t border-gray-200">
              <label class="text-gray-700 font-medium text-sm flex items-center gap-2">
                 <Icon icon="lucide:split" class="text-teal-500" width="16" /> Task Routing
              </label>
              <div class="flex items-center gap-3 border border-gray-200 px-4 py-3 rounded-xl bg-white cursor-pointer hover:bg-gray-50 hover:border-gray-300 transition-all w-full shadow-sm" @click="form.allow_step_splitting = !form.allow_step_splitting">
                 <input type="checkbox" v-model="form.allow_step_splitting" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 cursor-pointer">
                 <span class="text-sm text-gray-700 font-medium">Allow Parallel Step Splitting</span>
              </div>
           </div>
        </div>
      </div>
    </section>
  </Dialog>
</template>
`;
content = content.replace(/<template>[\s\S]*?<\/template>/, newTemplate);
fs.writeFileSync('resources/js/components/Modals/Workflows/AssignTask.vue', content);
