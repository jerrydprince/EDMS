<template>
  <main class="p-7 bg-slate-50">
    <div class="mb-5 flex items-center w-full justify-between">
      <Link href="/dashboard/workflow">
      <div class="flex items-center gap-2 cursor-pointer">
        <Icon icon="flowbite:angle-left-outline" />
        <p>Back</p>
      </div>
      </Link>
      <div class="flex items-center gap-10 font-semibold text-gray-600">
        <button class="bg-white border rounded-md px-4 py-2" @click="Re_Assign_Modal = true"
        v-if="props.is_assigned == 1">
          Reassign Task
        </button>

        <button class="flex items-center bg-white border rounded-md gap-2 px-4 py-2"
          v-if="(props.task.status !=='under review' && props.task.status != 'completed') && props.is_assigned == 1" @click="completeModal = true">
          <Icon icon="mdi:tick" width="25" />
          <p>Submit task</p>
        </button>

        <button class="flex items-center bg-white border rounded-md gap-2 px-4 py-2"
          v-if="props.task.status == 'completed' && props.is_assigned == 2" @click="ratingsModal">
          <Icon icon="mdi:tick" width="25" />
          <p>Ratings</p>
        </button>



        <button class="flex items-center bg-white border rounded-md gap-2 px-4 py-2" 
        v-if="props.status != 'completed' && props.is_assigned == 2"
          @click="editModal = true">
          <Icon icon="icon-park-outline:edit" width="25" />
          <p>Edit Task</p>
        </button>



        <button class="flex items-center bg-white border rounded-md gap-2 px-4 py-2" @click="reOpenModal = true"
          v-if="props.task.status === 'completed'">
          <Icon icon="teenyicons:unlock-outline" width="25" />
          <p>Reopen Task</p>
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
          <p class="text-2xl">{{ props.task.title }}</p>
        </div>

        <div class="h-[1000px] overflow-y-auto hide_scroll">
          <div>
            <div class="bg-slate-50 p-5 w-full rounded-md" v-if="props.task.taskdocs && props.task.taskdocs.length > 0">
              <img v-if="fileType(props.task.taskdocs[0]) == 'image'"
                :src="file_path != '' ? getFileExtension(file_path) : getFileExtension(props.task.taskdocs[0])" alt=""
                class="overflow-hidden rounded-md w-full" />


              <object v-if="fileType(props.task.taskdocs[0]) == 'pdf'"
                :data="file_path != '' ? page.props.base + file_path.path : page.props.base + props.task.taskdocs[0].path"
                type="application/pdf" width="100%" class="min-h-[80vh]">
                <p>{{ page.props.base + props.task.taskdocs[0].path }}
                  <a
                    :href="file_path != '' ? page.props.base + file_path.path : page.props.base + props.task.taskdocs[0].path">click
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
          <h1 class="p-8 font-semibold text-gray-800 text-3xl">
            Append Signature
          </h1>
          <Divider class="mt-0" />

          <section class="p-5 text-gray-600 max-h-[1000px]">
            <div>
              <div class="flex items-center gap-14">
                <div class="flex items-center gap-3">
                  <Icon icon="lucide:users" />
                  <p>Assign To</p>
                </div>
                <div v-if="props.task.user && props.task.user.length > 0">
                  <div class="flex items-center gap-2 h-fit bg-[#f8f8f9] my-1 p-2 py-1 rounded-3xl border-gray-300"
                    v-for="user in props.task.user">
                    <img :src="page.props.base + '/' + user.profile_image" alt="" class="w-7 rounded-full" />
                    <p class="text-base w-fit">{{ user.first_name + " " + user.last_name }}</p>
                  </div>
                </div>
              </div>

              <div class="flex items-center gap-14 mt-5">
                <div class="flex items-center gap-3">
                  <Icon icon="lucide:calendar" />
                  <p>Due date</p>
                </div>
                <div>

                  <p class="text-base w-fit">{{ props.task.due_date }}</p>
                </div>
              </div>

              <!-- <div class="flex items-center gap-14 mt-5">
                <div class="flex items-center gap-3">
                  <Icon icon="fluent:document-16-regular" />
                  <p>Document Type</p>
                </div>
                <div class="flex items-center gap-3">
                  <div v-for="(item, index) in data.document_type" :key="index">
                    <p class="rounded-2xl font-semibold bg-slate-50 py-1 px-4">{{ item }}</p>
                  </div>
                </div>
              </div> -->

              <div class="flex items-center gap-14 mt-5">
                <div class="flex items-center gap-3">
                  <Icon icon="fluent:document-16-regular" />
                  <p>Priority</p>
                </div>
                <div class="flex items-center gap-3">
                  <div>
                    <p class="rounded-2xl font-semibold bg-slate-50 py-1 px-4 uppercase text-xs" :style="{
          color:
            props.task.priority === '1'
              ? '#FFBC53'
              : props.task.priority === '3'
                ? '#FF3737'
                : props.task.priority === '2'
                  ? '#F67E5A'
                  : 'black',
        }">{{ props.task.priority == 1 ? 'Low' : props.task.priority == 2 ? 'Medium' : 'High' }}</p>
                  </div>
                </div>
              </div>


              <div class="flex items-center gap-24 mt-5">
                <div class="flex items-center gap-3">
                  <Icon icon="lucide:calendar" />
                  <p>Status</p>
                </div>

                <div class="flex items-center gap-3">

                  <div class="bg-[#FEFBF6] flex items-center gap-3 px-5 py-1.5 rounded-3xl cursor-pointer">

                    <p class=" uppercase text-xs w-fit font-semibold" :style="{
          color:
            props.task.status === 'Completed'
              ? '#0D6E1A'
              : props.task.status === 'Under Review'
                ? '#FB6514'
                : props.task.status === 'In Progress'
                  ? '#F79009'
                  : props.task.status === 'Uncompleted'
                    ? '#F04438'
                    : '#F04438',
        }">
                      {{ props.task.status }}
                    </p>

                  </div>
                </div>
              </div>

              <div class="mt-5">
                <div class="flex items-center gap-3">
                  <Icon icon="lucide:file-text" />
                  <p>Task Description</p>
                </div>

                <p class="bg-slate-50 outline-none mt-5 resize-none p-5 rounded-md w-full">

                  {{ props.task.description }}

                </p>
              </div>

              <div class="mt-5" v-if="props.task && props.task.taskdocs && props.task.taskdocs.length > 0">
                <div class="flex mb-5 items-center gap-3">
                  <Icon icon="grommet-icons:attachment" width="20" />
                  <p>Attached Document</p>
                </div>
                <div v-for="(document, index) in props.task.taskdocs" :key="index"
                  :class="document.path === file_path.path ? 'border-gray-500' : 'border-gray-300'"
                  class="border  hover:border-primary rounded-md p-4 flex items-center justify-between  mb-5">
                  <div class="flex items-center gap-3">
                    <div class="bg-[#EBF9FF] w-fit p-2 rounded-full">
                      <!-- <Icon :icon="document.icon" width="25" /> -->
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
          <Divider class="mt-0" />

          <div class="px-5 flex w-full items-center pb-5 justify-between cursor-pointer"
            @click="toggleSidemenu('comment')">
            <div class="flex items-center gap-5">
              <Icon icon="lucide:message-square" width="25" />
              <p class="text-xl">Comments</p>
              <p
                class="w-7 h-7 rounded-md text-gray-800 font-semibold bg-primary/10 flex items-center justify-center flex-col p-3">
                {{ (props.task && props.task.comments && props.task.comments.length > 0) ? props.task.comments.length :
          0
                }}
              </p>
            </div>
            <Icon icon="flowbite:angle-right-outline" width="25" />
          </div>
          <Divider class="mt-0" />

          <div class="px-5 flex w-full items-center pb-5 justify-between cursor-pointer"
            @click="toggleSidemenu('activity')">
            <div class="flex items-center gap-5">
              <Icon icon="lucide:activity" width="25" />
              <p class="text-xl">Task Activity</p>
            </div>
            <Icon icon="flowbite:angle-right-outline" width="25" />
          </div>

          <Divider class="mt-0" />
          <div class="px-5 flex w-full items-center pb-5 justify-between cursor-pointer"
            @click="toggleSidemenu('submission')" v-if="task.status == 'under review' || task.status == 'completed'">
            <div class="flex items-center gap-5">
              <Icon icon="lucide:activity" width="25" />
              <p class="text-xl">Task Submissions</p>
              <p
                class="w-7 h-7 rounded-md text-red-500 font-semibold bg-red-500/10 flex items-center justify-center flex-col p-3">
                !
              </p>
            </div>
            <Icon icon="flowbite:angle-right-outline" width="25" />
          </div>
          <Divider class="mt-0" />
        </div>

        <!-- /////   comments   ///// -->

        <div v-if="comment && sidemenu">

          <div class="p-8">
            <div class="flex items-center p-2 rounded-md cursor-pointer w-fit bg-slate-50 gap-2"
              @click="toggleSidemenu('back')">
              <Icon icon="flowbite:angle-left-outline" />
              <p>Back</p>
            </div>
          </div>
          <Divider class="mt-0" />
          <div class="">
            <h1 class="px-5 font-semibold text-gray-800 text-3xl">Comments</h1>

            <div class="mx-5 bg-slate-50 p-5 rounded-lg mt-5">
              <textarea name="" v-model="user_comment" id="comment_text" cols="30" placeholder="Enter Message" rows="5"
                class="bg-slate-50 outline-none resize-none"></textarea>
              <div class="w-full flex items-center justify-end">
                <button class="bg-white px-3 py-1.5 border border-gray-300 rounded-md" @click="makeComment">
                  Send
                </button>
              </div>
            </div>
            <div class="mt-5 px-5" v-if="props.task && props.task.comments && props.task.comments.length > 0">
              <div v-for="(item, index) in props.task.comments" :key="index">
                <Divider class="mt-0" />
                <div class="flex items-start w-full">
                  <div class="w-[15%]">
                    <div class="w-14 h-14 rounded-full overflow-hidden">
                      <img :src="getUserProfileImage(item)" alt="" class="w-full h-full" />
                    </div>
                  </div>
                  <div class="w-[80%]">
                    <div class="flex items-center gap-2">
                      <h1 class="text-gray-800 text-xl">{{ getUserName(item) }}</h1>
                      <small class="text-gray-500 capitalize">• {{ moment(item.created_at).fromNow() }}</small>
                    </div>
                    <p class="text-base text-gray-500 my-4">
                      {{ item.comment }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- //////   Activities   ///// -->
        <div v-if="activities && sidemenu">
          <div class="p-8">
            <div class="flex items-center p-2 rounded-md cursor-pointer w-fit bg-slate-50 gap-2"
              @click="toggleSidemenu('back')">
              <Icon icon="flowbite:angle-left-outline" />
              <p>Back</p>
            </div>
          </div>
          <Divider class="mt-0" />
          <div class="">

            <h1 class="px-5 font-semibold text-gray-800 text-3xl">
              Task Activity
            </h1>

            <div class="p-5 mx-7 bg-slate-50 rounded-lg mt-5">
              <div v-for="(step, index) in TaskAction" :key="index">
                <div class="flex items-start gap-5">
                  <div class="flex flex-col items-center">

                    <Icon icon="akar-icons:circle-check-fill" width="25" class=" text-primary" />

                    <div class="w-[2px] my-1 h-10 bg-[#EBF3FE]" v-if="index < 4"></div>
                  </div>
                  <div class="text-[#475467]">
                    <h1 class="font-medium text-lg">{{ step.progress }}:</h1>
                    <div class=" flex items-center  text-sm gap-1">
                      <p class="opacity-70">{{ step.to }}</p>
                      <p class=" opacity-100">•</p>
                      <p class="opacity-70">{{ step.date }}</p>
                      <p class=" opacity-100">•</p>
                      <p class="opacity-70">{{ step.time }}</p>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>

        <!-- //////   submissions   ///// -->
        <div v-if="submission && sidemenu">
          <div class="p-8">
            <div class="flex items-center p-2 rounded-md cursor-pointer w-fit bg-slate-50 gap-2"
              @click="toggleSidemenu('back')">
              <Icon icon="flowbite:angle-left-outline" />
              <p>Back</p>
            </div>
          </div>
          <Divider class="mt-0" />
          <div class="">

            <h1 class="px-5 font-semibold text-gray-800 text-3xl">
              Task Submissions
            </h1>

            <div v-if="task.task_submission && task.task_submission.length > 0">
              <div v-for="(document, index) in task.task_submission" :key="index">
                <div class="px-5 mt-3" v-if="document.task_submission_files">
                  <div class="bg-slate-50 p-3 rounded-md">
                    <div
                      class="border bg-white border-gray-300 hover:border-primary rounded-md p-4 flex items-center justify-between mb-5"
                      v-for="doc in document.task_submission_files">
                      <div class="flex items-center gap-3">
                        <div class="bg-[#EBF9FF] w-fit p-2 rounded-full">
                          <img :src="getFileExtension(doc)" width="25" alt="">
                          <!-- <Icon :icon="document.icon" width="25" /> -->
                        </div>
                        <div>
                          <h1>{{ doc.name }}</h1>
                          <p class="text-sm text-gray-400">{{ doc.size }}</p>
                        </div>
                      </div>

                      <div class="flex items-center gap-3">
                        <Icon icon="ph:eye-light" width="25" class="cursor-pointer" @click="setFilePath(doc)" />
                        <Icon icon="material-symbols-light:download" width="25" class="cursor-pointer" @click="downloadFile(doc.path)" />
                      </div>
                    </div>
                  </div>

                </div>

                <div class="mt-7 px-5">
                  <div class="flex items-center gap-3">
                    <Icon icon="lucide:file-text" />
                    <p>Task Description</p>
                  </div>

                  <p class="bg-slate-50 outline-none mt-5 resize-none p-5 rounded-md w-full">
                    {{ document.description }}
                  </p>
                </div>

                <div class="px-5 mt-5 w-full gap-5 flex items-center justify-end" v-if="is_assigned == 2">
                  <button v-if="props.task.status != 'completed'" class="text-red-600" @click="RequestRevisionModal = true">
                    Request Revision
                  </button>

                  <Link v-if="props.task.status != 'completed'" href="/dashboard/mark-as-completed" method="post" :data="{id: props.task.id}" class="font-semibold text-gray-800 px-3 py-2 border rounded-md">
                    Mark as complete
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </section>
  </main>
  <MarkComplete v-model="completeModal" :task="props.task" />
  <ReAssign v-model="Re_Assign_Modal" :users="props.task?.user" @refresh="refreshHandler" :task_id="props.task?.id" />
  <RequestRevision v-model="RequestRevisionModal" :task="props.task" />
  <DeleteRevision v-model="deleteRevisionModal" />
  <Ratings v-model="rating" :task="props.task" />

  <EditTask v-model="editModal" :task="props.task" @refresh="refreshHandler" />

  <Reopen v-model="reOpenModal" :task="props.task"/>
  <Sidebar v-model:visible="subNavVisible" header=" Left Sidebar" position="right"
    :style="{ width: '32rem', padding: '10px 0px' }">
    <div class="bg-white hide_scroll sticky top-0 h-screen pr-5 overflow-y-scroll">
      <div v-if="!sidemenu">
        <h1 class="p-8 font-semibold text-gray-800 text-3xl">
          Append Signature
        </h1>
        <Divider class="mt-0" />

        <section class="p-5 text-gray-600 max-h-[1000px]">
          <div>
            <div class="flex items-center gap-14">
              <div class="flex items-center gap-3">
                <Icon icon="lucide:users" />
                <p>Assign To</p>
              </div>
              <div v-if="props.task.user && props.task.user.length > 0">
                <div class="flex items-center gap-2 h-fit bg-[#f8f8f9] p-2 py-1 rounded-3xl border-gray-300"
                  v-for="user in props.task.user">
                  <img :src="page.props.base + '/' + user.profile_image" alt="" class="w-7" />
                  <p class="text-base w-fit">{{ user.first_name + " " + user.last_name }}</p>
                </div>
              </div>
            </div>

            <div class="flex items-center gap-14 mt-5">
              <div class="flex items-center gap-3">
                <Icon icon="fluent:document-16-regular" />
                <p>Document Type</p>
              </div>
              <div class="flex items-center gap-3">
                <div v-for="(item, index) in data.document_type" :key="index">
                  <p class="rounded-2xl font-semibold bg-slate-50 py-1 px-4">{{ item }}</p>
                </div>
              </div>
            </div>
            <div class="flex items-center gap-14 mt-5">
              <div class="flex items-center gap-3">
                <Icon icon="lucide:calendar" />
                <p>Due date</p>
              </div>
              <div>
                <p class="text-base w-fit">2023-05-15</p>
              </div>
            </div>

            <div class="flex items-center gap-24 mt-5">
              <div class="flex items-center gap-3">
                <Icon icon="lucide:calendar" />
                <p>Tags</p>
              </div>

              <div class="flex items-center gap-3">
                <div class="bg-[#FEFBF6] flex items-center gap-3 px-5 py-1.5 rounded-3xl cursor-pointer">
                  <p class="text-xs w-fit text-[#F67E5A]">Medium</p>
                </div>

                <div class="bg-[#FEFBF6] flex items-center gap-3 px-5 py-1.5 rounded-3xl cursor-pointer">
                  <p class="text-xs w-fit text-[#F67E5A]">In Progress</p>
                </div>
              </div>
            </div>

            <div class="mt-5">
              <div class="flex items-center gap-3">
                <Icon icon="lucide:file-text" />
                <p>Task Description</p>
              </div>

              <p class="bg-slate-50 outline-none mt-5 resize-none p-5 rounded-md w-full">
                {{ data.desc }}
              </p>
            </div>

            <div class="mt-5" v-if="props.task && props.task.taskdocs && props.task.taskdocs.length > 0">
              <div class="flex mb-5 items-center gap-3">
                <Icon icon="grommet-icons:attachment" width="20" />
                <p>Attached Document</p>
              </div>
              <div v-for="(document, index) in props.task.taskdocs" :key="index"
                class="border border-gray-300 hover:border-primary rounded-md p-4 flex items-center justify-between mb-5">
                <div>
                  <div class="flex items-center gap-3">
                    <div class="bg-[#EBF9FF] w-fit p-2 rounded-full">
                      <Icon :icon="document.icon" width="25" />
                    </div>
                    <div>
                      <h1>{{ document.name }}</h1>
                      <p class="text-sm text-gray-400">{{ document.size }}</p>
                    </div>
                  </div>

                  <div class="flex items-center gap-3">
                    <Icon icon="ph:eye-light" width="25" @click="setFilePath(document)" />
                    <Icon icon="material-symbols-light:download" @click="downloadFile(document.path)" width="25" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
        <Divider class="mt-0" />

        <div class="px-5 flex w-full items-center pb-5 justify-between cursor-pointer"
          @click="toggleSidemenu('comment')">
          <div class="flex items-center gap-5">
            <Icon icon="lucide:message-square" width="25" />
            <p class="text-xl">Comments</p>
            <p
              class="w-7 h-7 rounded-md text-gray-800 font-semibold bg-primary/10 flex items-center justify-center flex-col p-3">
              3
            </p>
          </div>
          <Icon icon="flowbite:angle-right-outline" width="25" />
        </div>
        <Divider class="mt-0" />

        <div class="px-5 flex w-full items-center pb-5 justify-between cursor-pointer"
          @click="toggleSidemenu('submission')">
          <div class="flex items-center gap-5">
            <Icon icon="lucide:activity" width="25" />
            <p class="text-xl">Task History</p>
            <p
              class="w-7 h-7 rounded-md text-red-500 font-semibold bg-red-500/10 flex items-center justify-center flex-col p-3">
              !
            </p>
          </div>
          <Icon icon="flowbite:angle-right-outline" width="25" />
        </div>
        <Divider class="mt-0" />
      </div>

      <!-- /////   comments   ///// -->

      <div v-if="comment && sidemenu">
        <div class="p-8">
          <div class="flex items-center p-2 rounded-md cursor-pointer w-fit bg-slate-50 gap-2"
            @click="toggleSidemenu('back')">
            <Icon icon="flowbite:angle-left-outline" />
            <p>Back</p>
          </div>
        </div>
        <Divider class="mt-0" />
        <div class="">
          <h1 class="px-5 font-semibold text-gray-800 text-3xl">Comments</h1>

          <div class="mx-5 bg-slate-50 p-5 rounded-lg mt-5">
            <textarea name="" id="" cols="30" placeholder="Enter Message" rows="5"
              class="bg-slate-50 outline-none resize-none"></textarea>
            <div class="w-full flex items-center justify-end">
              <button class="bg-white px-3 py-1.5 border border-gray-300 rounded-md">
                Send
              </button>
            </div>
          </div>
          <div class="mt-5 px-5">
            <div v-for="(item, index) in comments" :key="index">
              <Divider class="mt-0" />
              <div class="flex items-start w-full gap-4">
                <div class="w-[15%]">
                  <div class="w-14 h-14 rounded-full overflow-hidden">
                    <img :src="item.img" alt="" class="w-full h-full" />
                  </div>
                </div>
                <div class="w-[80%]">
                  <div class="flex items-center gap-2">
                    <h1 class="text-gray-800 text-xl">{{ item.name }}</h1>
                    <small class="text-gray-500">• {{ item.time }}</small>
                  </div>
                  <p class="text-base text-gray-500 my-4">
                    {{ item.comment }}
                  </p>

                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- //////   Activities   ///// -->
      <!-- <div v-if="activities && sidemenu">
        <div class="p-8">
          <div class="flex items-center p-2 rounded-md cursor-pointer w-fit bg-slate-50 gap-2"
            @click="toggleSidemenu('back')">
            <Icon icon="flowbite:angle-left-outline" />
            <p>Back</p>
          </div>
        </div>
        <Divider class="mt-0" />
        <div class="">

          <h1 class="px-5 font-semibold text-gray-800 text-3xl">
            Task Activity
          </h1>

          <div class="p-5 mx-7 bg-slate-50 rounded-lg mt-5">
            <div v-for="(step, index) in TaskAction" :key="index">
              <div class="flex items-start gap-5">
                <div class="flex flex-col items-center">

                  <Icon icon="akar-icons:circle-check-fill" width="25" class=" text-primary" />

                  <div class="w-[2px] my-1 h-10 bg-[#EBF3FE]" v-if="index < 4"></div>
                </div>
                <div class="text-[#475467]">
                  <h1 class="font-medium text-lg">{{ step.progress }}:</h1>
                  <div class=" flex items-center  text-sm gap-1">
                    <p class="opacity-70">{{ step.to }}</p>
                    <p class=" opacity-100">•</p>
                    <p class="opacity-70">{{ step.date }}</p>
                    <p class=" opacity-100">•</p>
                    <p class="opacity-70">{{ step.time }}</p>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div> -->

      <!-- //////   submissions   ///// -->
      <div v-if="submission && sidemenu">
        <div class="p-8">
          <div class="flex items-center p-2 rounded-md cursor-pointer w-fit bg-slate-50 gap-2"
            @click="toggleSidemenu('back')">
            <Icon icon="flowbite:angle-left-outline" />
            <p>Back</p>
          </div>
        </div>
        <Divider class="mt-0" />
        <div class="">
          <h1 class="px-5 font-semibold text-gray-800 text-3xl">
            Task Submissions
          </h1>

          <div class="px-5 mt-3">
            <div class="bg-slate-50 p-3 rounded-md">
              <div v-for="(document, index) in documents" :key="index"
                class="border bg-white border-gray-300 hover:border-primary rounded-md p-4 flex items-center justify-between mb-5">
                <div class="flex items-center gap-3">
                  <div class="bg-[#EBF9FF] w-fit p-2 rounded-full">
                    <Icon :icon="document.icon" width="25" />
                  </div>
                  <div>
                    <h1>{{ document.name }}</h1>
                    <p class="text-sm text-gray-400">{{ document.size }}</p>
                  </div>
                </div>

                <div class="flex items-center gap-3">
                  <Icon icon="ph:eye-light" width="25" class="cursor-pointer"  @click="setFilePath(document)" />
                  <Icon icon="material-symbols-light:download" class="cursor-pointer" width="25" @click="downloadFile(document.path)" />
                </div>
              </div>
            </div>
          </div>

          <div class="mt-7 px-5">
            <div class="flex items-center gap-3">
              <Icon icon="lucide:file-text" />
              <p>Task Description</p>
            </div>

            <p class="bg-slate-50 outline-none mt-5 resize-none p-5 rounded-md w-full">
              {{ data.desc }}
            </p>
          </div>

          <div class="px-5 mt-5 w-full gap-5 flex items-center justify-end">
            <button class="text-red-600" @click="RequestRevisionModal = true">
              Request Revision
            </button>

            <button class="font-semibold text-gray-800 px-3 py-2 border rounded-md" @click="completeModal = true">
              Mark as complete
            </button>
          </div>
        </div>
      </div>
    </div>

  </Sidebar>
</template>

<script setup>
import { ref, defineProps, computed } from "vue";
//import { useRouter } from "vue-router";
import MarkComplete from "../../components/Modals/Workflows/MarkComplete.vue";
import ReAssign from "../../components/Modals/Workflows/ReAssign.vue";
import RequestRevision from "../../components/Modals/Workflows/RequestRevision.vue";
import DeleteRevision from "../../components/Modals/Workflows/DeleteRevision.vue";
import EditTask from "../../components/Modals/Workflows/EditTask.vue";
import Reopen from "../../components/Modals/Workflows/Reopen.vue";
import jenna from "../../assets/images/jenna.svg";
import user from "../../assets/images/user.svg";
import { usePage, Link, router } from "@inertiajs/vue3";
import Ratings from "../../components/Modals/Workflows/Ratings.vue"
import debouce from "lodash.debounce";

import moment from 'moment';

const editModal = ref(false)
const subNavVisible = ref(false);
const reOpenModal = ref(false);
const sidemenu = ref(false);
const submission = ref(false);
const props = defineProps(['task', 'is_assigned', 'branchType'])
const page = usePage()
const user_comment = ref('')
const activities = ref(false)

const comment = ref(false);
const RequestRevisionModal = ref(false);
const completeModal = ref(false);
const deleteRevisionModal = ref(false);
const Re_Assign_Modal = ref(false);
const file_path = ref([]);
const rating = ref(false)

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
  router.post('/dashboard/task-comment', { task_id: props.task.id, comment: user_comment.value }, {
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

</script>

<style></style>