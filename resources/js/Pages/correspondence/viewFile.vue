<template>
  <main class="min-h-screen bg-gray-50/50 p-4 md:p-8">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
      <Link href="/dashboard/correspondence" class="group flex items-center gap-2 text-gray-500 hover:text-indigo-600 transition-colors w-fit">
        <div class="p-2 bg-white border border-gray-200 rounded-lg group-hover:border-indigo-200 group-hover:bg-indigo-50 transition-all shadow-sm">
          <Icon icon="flowbite:angle-left-outline" width="18" />
        </div>
        <span class="font-medium text-sm">Back to Dashboard</span>
      </Link>

      <div class="flex items-center gap-3">
        <button v-if="props.correspondence?.is_assigned" @click="assign_modal = true" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-200 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-all shadow-sm focus:ring-2 focus:ring-indigo-500 outline-none">
          <Icon icon="lucide:user-cog" width="18" class="text-gray-400" />
          <span>Re-assign Task</span>
        </button>
        
        <button v-if="props.correspondence && !props.correspondence.is_assigned" @click="AssignModal = true" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition-all shadow-sm focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 outline-none">
          <Icon icon="lucide:user-plus" width="18" />
          <span>Assign Task</span>
        </button>

        <div class="cursor-pointer block lg:hidden p-2.5 bg-white border border-gray-200 rounded-xl hover:bg-gray-50" @click="subNavVisible = true">
          <Icon icon="mdi:hamburger-menu-back" width="20" class="text-gray-600" />
        </div>
      </div>
    </div>

    <section class="flex flex-col lg:flex-row items-start gap-8 w-full">
      <!-- Main Document Viewer Area -->
      <div class="bg-white w-full lg:w-[65%] rounded-2xl shadow-sm border border-gray-200/60 overflow-hidden flex flex-col h-[calc(100vh-140px)]">
        <!-- Viewer Header -->
        <div class="px-6 py-5 border-b border-gray-100 flex items-center gap-4 bg-gray-50/30">
          <div class="p-3 bg-indigo-50 border border-indigo-100 text-indigo-600 rounded-xl shadow-inner">
            <Icon icon="lucide:file-text" width="24" />
          </div>
          <div>
            <h2 class="text-xl font-bold text-gray-800 leading-tight">{{ props.correspondence?.file_name || 'Untitled Document' }}</h2>
            <p class="text-xs font-medium text-gray-500 mt-0.5" v-if="props.correspondence?.document_number">Doc No: <span class="text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded">{{ props.correspondence.document_number }}</span></p>
          </div>
        </div>

        <!-- Viewer Content -->
        <div class="flex-1 overflow-auto bg-slate-100/50 p-6 flex flex-col">
          <div class="w-full bg-white rounded-xl shadow-sm border border-gray-200/50 p-2 min-h-full flex items-center justify-center relative overflow-hidden group">
            
            <template v-if="props.correspondence?.correspondence_file?.length > 0">
              <!-- Render Image -->
              <img v-if="fileType(file_path?.path ? file_path : props.correspondence.correspondence_file[0]) == 'image'"
                   :src="file_path?.path ? getFileExtension(file_path) : getFileExtension(props.correspondence.correspondence_file[0])" 
                   alt="Document Preview"
                   class="max-w-full rounded-lg object-contain shadow-sm border border-gray-100 transition-transform duration-300" />

              <!-- Render PDF -->
              <object v-else-if="fileType(file_path?.path ? file_path : props.correspondence.correspondence_file[0]) == 'pdf'"
                      :data="file_path?.path ? page.props.base + file_path.path : page.props.base + props.correspondence.correspondence_file[0].path"
                      type="application/pdf" width="100%" class="h-[75vh] w-full rounded-lg">
                <div class="flex flex-col items-center justify-center h-full text-gray-500">
                  <Icon icon="lucide:file-warning" width="40" class="mb-3 text-gray-300" />
                  <p>Your browser doesn't support PDF viewing.</p>
                  <a :href="file_path?.path ? page.props.base + file_path.path : page.props.base + props.correspondence.correspondence_file[0].path" class="mt-2 text-indigo-600 hover:underline">Download PDF instead</a>
                </div>
              </object>
              
              <!-- Unknown File Type -->
              <div v-else class="flex flex-col items-center justify-center text-center p-12">
                 <Icon icon="lucide:file-question" width="64" class="text-gray-300 mb-4" />
                 <h3 class="text-lg font-medium text-gray-700">Preview not available</h3>
                 <p class="text-sm text-gray-500 mt-2">This file type cannot be previewed in the browser.</p>
              </div>
            </template>
            
            <template v-else>
              <div class="flex flex-col items-center justify-center text-center p-12">
                 <Icon icon="lucide:file-x-2" width="64" class="text-gray-300 mb-4" />
                 <h3 class="text-lg font-medium text-gray-700">No attached files</h3>
                 <p class="text-sm text-gray-500 mt-2">There are no files attached to this correspondence.</p>
              </div>
            </template>
            
          </div>
        </div>
      </div>

      <!-- Right Metadata Panel -->
      <div class="bg-white hidden lg:flex flex-col w-[35%] rounded-2xl shadow-sm border border-gray-200/60 h-[calc(100vh-140px)] overflow-hidden">
        
        <!-- Tabs Header -->
        <div class="flex items-center border-b border-gray-100 bg-gray-50/30 px-2 pt-2">
          <button class="px-6 py-3.5 text-sm font-semibold border-b-2 border-indigo-600 text-indigo-700 bg-white rounded-t-lg transition-colors">
            Details & Files
          </button>
        </div>

        <div class="flex-1 overflow-y-auto p-6 space-y-8 hide_scroll">
          
          <!-- Description Block -->
          <div>
            <div class="flex items-center gap-2 mb-3">
              <Icon icon="lucide:align-left" class="text-indigo-500" width="18" />
              <h3 class="font-bold text-gray-800 text-sm tracking-wide uppercase">Description</h3>
            </div>
            <div class="bg-indigo-50/50 border border-indigo-100/50 rounded-xl p-5 shadow-inner">
              <p class="text-gray-700 text-sm leading-relaxed whitespace-pre-wrap">
                {{ props.correspondence?.description ? props.correspondence.description : 'No description provided for this correspondence.' }}
              </p>
            </div>
          </div>

          <!-- Attachments Block -->
          <div>
            <div class="flex items-center justify-between mb-3">
              <div class="flex items-center gap-2">
                <Icon icon="lucide:paperclip" class="text-indigo-500" width="18" />
                <h3 class="font-bold text-gray-800 text-sm tracking-wide uppercase">Attached Documents</h3>
              </div>
              <span class="text-xs font-semibold bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">{{ props.correspondence?.correspondence_file?.length || 0 }}</span>
            </div>
            
            <div v-if="props.correspondence?.correspondence_file?.length > 0" class="space-y-3">
              <div v-for="(document, index) in props.correspondence.correspondence_file" :key="index"
                   @click="setFilePath(document)"
                   :class="[
                     'group flex items-center justify-between p-3.5 rounded-xl border transition-all cursor-pointer shadow-sm',
                     document.path === file_path?.path 
                       ? 'bg-indigo-50/50 border-indigo-300 ring-1 ring-indigo-300' 
                       : 'bg-white border-gray-200 hover:border-indigo-200 hover:bg-gray-50'
                   ]">
                
                <div class="flex items-center gap-4 overflow-hidden">
                  <div class="flex-shrink-0 bg-white border border-gray-100 w-10 h-10 rounded-lg shadow-sm flex items-center justify-center p-1.5">
                    <img :src="getFileExtension(document)" alt="icon" class="max-w-full max-h-full opacity-90 group-hover:opacity-100 transition-opacity">
                  </div>
                  <div class="min-w-0">
                    <h4 class="text-sm font-semibold text-gray-800 truncate" :title="props.correspondence.file_name">
                      {{ props.correspondence.file_name }}
                    </h4>
                    <p class="text-xs font-medium text-gray-400 mt-0.5">{{ document.size || 'Unknown size' }}</p>
                  </div>
                </div>

                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click.stop="downloadFile(document.path)" class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Download">
                    <Icon icon="lucide:download-cloud" width="18" />
                  </button>
                </div>
              </div>
            </div>
            
            <div v-else class="text-center p-6 border border-dashed border-gray-200 rounded-xl bg-gray-50/50">
              <p class="text-sm text-gray-400">No documents attached</p>
            </div>
          </div>
          
        </div>
      </div>
    </section>

    <!-- Modals & Sidebar -->
    <ReassignTask :users="[]" v-model="assign_modal" @refresh="refreshHandler" :task_id="props.correspondence?.task_id" />
    <AGFAssignTask v-model="AssignModal" :files="props.correspondence?.correspondence_file" :correspondence="props.correspondence" :correspondence_id="props.correspondence?.id" />
   
    <Sidebar v-model:visible="subNavVisible" header="Options" position="right" :style="{ width: '32rem' }">
      <div class="bg-white hide_scroll sticky top-0 h-screen pr-5 overflow-y-auto">
        <!-- Sidebar content for mobile can go here -->
      </div>
    </Sidebar>
  </main>
</template>
  
  <script setup>
  import { ref, defineProps, computed, onMounted, inject, watch } from "vue";
  
  import ReassignTask from "../../components/Modals/correspondence/AGFReassignTask.vue";
  import AGFAssignTask from "../../components/Modals/correspondence/AGFAssignTask.vue";
  import jenna from "../../assets/images/jenna.svg";
  import user from "../../assets/images/user.svg";
  import { usePage, Link, router } from "@inertiajs/vue3";
  import Ratings from "../../components/Modals/Workflows/Ratings.vue"
  
  
  import moment from 'moment';
  
  const editModal = ref(false)
  const subNavVisible = ref(false);
  const reOpenModal = ref(false);
  const sidemenu = ref(false);
  const submission = ref(false);
  const props = defineProps(['task', 'is_assigned', 'branchType', 'correspondence'])
  const page = usePage()
  const user_comment = ref('')
  const activities = ref(false)
  const AssignModal = ref(false)
  
  const comment = ref(false);
  const RequestRevisionModal = ref(false);
  const completeModal = ref(false);
  const deleteRevisionModal = ref(false);
  const assign_modal = ref(false);
  const file_path = ref([]);
  const rating = ref(false)
  const current_user = computed(()=>page.props.user)
  const notifications = inject('notifications');
  
  const ratingsModal = () => {
    rating.value = true;
  }
  
  
  const refreshHandler = () => {
    router.reload({ only: ['task'] })
  }
  
  const makeComment = () => {
    if (user_comment.value == '') {
      document.getElementById('comment_text').focus();
      return false
    }
    router.post('/dashboard/task-comment', { task_id: props.task.id, comment: user_comment.value, is_assigned: props.is_assigned }, {
      onSuccess: page => {
        user_comment.value = ''
        router.reload({ only: ['task'] })
      }
    })
  }
  
  
  const goBack = () => {
    // router.go(-1);
    window.history.back()
  };
  
  const getUserName = (comment) => {
  
    if (comment.user) {
      if (page.props.user.id == comment.user.id) {
        return 'You';
      }
      return comment.user.first_name + ' ' + comment.user.last_name
    } else {
      return ''
    }
  }
  
  const getUserProfileImage = (comment) => {
    if (comment.user && comment.user.profile_image) {
      return page.props.base + '/' + comment.user.profile_image;
    } else {
      return page.props.base + '/images/user.png';
    }
  }
  
  const data = ref({
  
  });
  
  const setFilePath = (path) => {
    file_path.value = path;
  }
  
  
  const documents = ref([
    // {
    //   name: "Tech design requirements.pdf",
    //   size: "200 KB ",
    //   icon: "vscode-icons:file-type-word",
    // },
    // {
    //   name: "Tech design requirements.pdf",
    //   size: "200 KB ",
    //   icon: "vscode-icons:file-type-excel2",
    // },
  ]);
  
  const comments = ref([
    // {
    //   img: jenna,
    //   name: "Jenna Ryan obadimu",
    //   time: "6m ago",
    //   comment:
    //     "Task Received i would begin work immediately & review and archive assigned documents promptly to ensure seamless workflow. Documents awaiting archiving in the Finance Department. ",
    // },
    // {
    //   img: user,
    //   name: "You",
    //   time: "6m ago",
    //   comment: "Okay understood, i would work on the changes in due time boss ",
    // },
  ]);
  
  // const downloadFile = (filename)=>{
  //     const url = '/dashboard/download-task-file/'+filename
  //     window.open(url, '_blank');
  // }
  
  const downloadFile = async (filename) => {
    try {
      const url = `${page.props.base}` + `${filename}`;
  
      const response = await fetch(url);
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
  
      const blob = await response.blob();
      const objectUrl = URL.createObjectURL(blob);
  
      const link = document.createElement('a');
      link.href = objectUrl;
      link.download = filename;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
  
      // Clean up the object URL
      URL.revokeObjectURL(objectUrl);
    } catch (error) {
      console.error('There has been a problem with your fetch operation:', error);
    }
  }
  
  const toggleSidemenu = (action) => {
  
    if (action === "comment") {
      comment.value = !comment.value;
      sidemenu.value = !sidemenu.value;
    }
    if (action === "submission") {
      submission.value = !submission.value;
      sidemenu.value = !sidemenu.value;
    }
    if (action === "activity") {
      activities.value = !activities.value;
      sidemenu.value = !sidemenu.value;
    }
    if (action === "back") {
      comment.value = false;
      sidemenu.value = false;
      submission.value = false;
      activities.value = false
    }
  };
  
  const getFileExtension = (file) => {
    const f_path = file.path
    const parts = f_path.split('.');
  
    const extension = parts.pop();
  
    if (extension == 'jpg' || extension == 'jpeg' || extension == 'gif' || extension == 'png') {
      return encodeURI(page.props.base + file.path)
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
  
  const TaskAction = ref([
    {
      progress: "Task created",
      to: "Dave Miye (You)",
      date: "2024-02-14",
      time: " 10:00 AM"
    },
    {
      progress: "Task Assigned to Patricia Darlene ",
      to: "Dave Miye (You)",
      date: "2024-02-14",
      time: " 10:00 AM"
    },
    {
      progress: "Task Re-assigned to John Smith (Deputy director)",
      to: "John smith",
      date: "2024-02-14",
      time: " 10:00 AM"
    },
    {
      progress: "Added Comments & Feedback",
      to: "Dave Miye (You)",
      date: "2024-02-14",
      time: " 10:00 AM"
    },
    {
      progress: "Appended Signature",
      to: "Dave Miye (You)",
      date: "2024-02-14",
      time: " 10:00 AM"
    }
  ])
  
  const fileType = (file) => {
   if(file && file.path != undefined){
     const f_path = file.path
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
   }else{
    return ''
   }
  }
  
  watch(notifications,(newVal)=>{
  
    if(newVal && newVal.Today){
      // alert('yes')
      let new_comment = newVal.Today[0].data.comment;
      let comment = comments.value.findIndex((item)=>item.id == new_comment.id)
      if(comment == -1){
        comments.value.unshift(new_comment)
      }
    }
    
  })
  
  watch(()=>props.task, ()=>{
    comments.value = props.task?.comments
  }, {immediate: true})
  
  onMounted(()=>{
    
    // Echo.private('comment.' + current_user.value.id)
    //     .notification((notification) => {
    //         //getUserNotifications()
    //     });
  })
  
  </script>
  
  <style></style>
