<template>
    <main class="p-7 bg-slate-50">
      <div class="mb-5 flex items-center w-full justify-between">
        <Link href="/dashboard/correspondence">
        <div class="flex items-center gap-2 cursor-pointer">
          <Icon icon="flowbite:angle-left-outline" />
          <p>Back</p>
        </div>
        </Link>
        <div class="flex items-center gap-10 font-semibold text-gray-600">
          <button class="bg-white border rounded-md px-4 py-2" @click="assign_modal = true" v-if="props.correspondence && props.correspondence.is_assigned">
            Re-assign Task
          </button>

          <button class="bg-white border rounded-md px-4 py-2" @click="AssignModal = true" v-if="props.correspondence && !props.correspondence.is_assigned">
            Assign Task
          </button>
  
        </div>
        <div class="absolute top-12 cursor-pointer z-50 right-7 block md:hidden" @click="subNavVisible = true">
          <Icon icon="mdi:hamburger-menu-back" width="30" />
        </div>
      </div>
  
      <section class="lg:flex items-center gap-10 w-full">
        <div class="bg-white w-full lg:w-[60%] rounded-lg p-5">
          <div class="pb-5 flex items-center gap-10">
            <div class="p-4 border rounded-md">
              <Icon icon="lucide:file-text" width="25" />
            </div>
            <!-- <p class="text-2xl">{{file_path}}</p> -->
          </div>
  
          <div class="h-[1000px] overflow-y-auto hide_scroll">
            <div>
              <div class="bg-slate-50 p-5 w-full rounded-md" >
                <img v-if="fileType(props.correspondence.correspondence_file[0]) == 'image'"
                :src="file_path != '' ? getFileExtension(file_path) : getFileExtension(props.correspondence.correspondence_file[0])" alt=""
                class="overflow-hidden rounded-md w-full" />
  
  
                <object v-if="fileType(props.correspondence.correspondence_file[0]) == 'pdf'"
                  :data="file_path != '' ? page.props.base + file_path.path : page.props.base + props.correspondence.correspondence_file[0].path"
                  type="application/pdf" width="100%" class="min-h-[80vh]">
                  <p>{{ page.props.base + props.correspondence.correspondence_file[0].path }}
                    <a
                      :href="file_path != '' ? page.props.base + file_path.path : page.props.base + props.correspondence.correspondence_file[0].path">click
                      here to
                      download the PDF file.</a>
                  </p>
                </object>
              </div>
            </div>
          </div>
        </div>
  
        <div class="bg-white hidden lg:block w-[40%] h-[1120px] overflow-y-scroll hide_scroll rounded-lg">
          <div v-if="!sidemenu">
           
  
            <section class="p-5 text-gray-600 max-h-[1000px]">
              <div>
  
                <div class="mt-5">
                  <div class="flex items-center gap-3">
                    <Icon icon="lucide:file-text" />
                    <p>Correspondence Description</p>
                  </div>
  
                  <p class="bg-slate-50 outline-none mt-5 resize-none p-5 rounded-md w-full">
  
                   {{props.correspondence.description? props.correspondence.description: 'Description'}}
  
                  </p>
                </div>
  
                <div class="mt-5" >
                  <div class="flex mb-5 items-center gap-3">
                    <Icon icon="grommet-icons:attachment" width="20" />
                    <p>Attached Document</p>
                  </div>
                  <div v-for="(document, index) in props.correspondence.correspondence_file" :key="index"
                    :class="document.path === file_path.path ? 'border-gray-500' : 'border-gray-300'"
                    class="border  hover:border-primary rounded-md p-4 flex items-center justify-between  mb-5">
                    <div class="flex items-center gap-3">
                      <div class="bg-[#EBF9FF] w-fit p-2 rounded-full">
                        
                        <img :src="getFileExtension(document)" alt="" width="20">
                      </div>
                      <div>
                        <h1>{{ document.file_name }}</h1>
                        <p class="text-sm text-gray-400">{{ document.size }}</p>
                      </div>
                    </div>
  
                    <div class="flex items-center gap-3">
                      <Icon icon="ph:eye-light" width="25" @click="setFilePath(document)" class="cursor-pointer" />
                      <Icon icon="material-symbols-light:download" width="25" @click="downloadFile(document.path)"
                        class="cursor-pointer" />
                    </div>
                  </div>
                </div>
              </div>
            </section>
         
          </div>
  
          <!-- /////   comments   ///// -->
  
         
  
        </div>
      </section>
    </main>
 
    <ReassignTask :users="[]" v-model="assign_modal" @refresh="refreshHandler" :task_id="props.correspondence?.task_id" />
    <AGFAssignTask v-model="AssignModal" :files="props.correspondence?.correspondence_file" :correspondence="props.correspondence"
     :correspondence_id="props.correspondence?.id" />
   
    <Sidebar v-model:visible="subNavVisible" header=" Left Sidebar" position="right"
      :style="{ width: '32rem', padding: '10px 0px' }">
      <div class="bg-white hide_scroll sticky top-0 h-screen pr-5 overflow-y-scroll">
      
  
      </div>
  
    </Sidebar>
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