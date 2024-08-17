<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    root: { class: 'bg-white px-5 max-h-[97vh] hide_scroll overflow-y-auto  rounded-md overflow-hidden' },
    content: { class: 'px-0  pb-0' },
    header: { class: ' bg-slate-100 -mt-2 ' },

    footer: { class: 'bg-white' },
  }" :style="{ width: '45rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
    class=".custom-dialog">
    <template #header>
      <div class="flex bg-slate-100 p-3 justify-end w-full">
        <div class="flex gap-5 bg-slate-100 items-center px-5">
          <Icon icon="grommet-icons:attachment" :class="{ 'hidden': buttonSwitch === 3 }" width="25" @click="getFile"
            class=" cursor-pointer" />
          <Icon icon="mingcute:delete-2-line" width="25" @click="closeModal" />
          <button class="p-2 border-[2px] border-solid rounded-md bg-white" @click="submit">
            Create Task
          </button>
        </div>
      </div>
      <input type="file" name="" @change="handleFileUpload" id="fileUpload" multiple class=" opacity-0 absolute">
    </template>
    <section class="bg-white p-5">
      <input type="text" class=" w-full text-3xl outline-none" placeholder="Write a Task Name" v-model="form.title">
      <div v-if="form.errors.title" class=" text-red-400">{{ form.errors.title }}</div>
    </section>
    <Divider class="-mt-0" />
    <section class="p-5 text-gray-600">

      <div class="flex gap-3 pb-4 items-center justify-between text-sm">
        <div class="bg-gray-100 flex items-center rounded-md p-1">
          <button class="rounded-lg py-1 px-4" @click="activeButton('')" :class="{ activeButton: buttonSwitch === 1 }">
            <p>Document Upload + Text comment</p>
          </button>
          <div>
            <button class="flex gap-2 items-center text-gray-400 rounded-lg py-1 px-4"
              @click="activeButton('document_only')" :class="{ activeButton: buttonSwitch === 2 }">
              <p>Document Only</p>
            </button>
          </div>

          <button class="flex gap-1 items-center text-gray-400 rounded-lg py-1 px-4" @click="activeButton('text_only')"
            :class="{ activeButton: buttonSwitch === 3 }">
            <p>Text Only</p>
          </button>

        </div>
      </div>

      <div>
        <div class="flex items-center gap-14">
          <div class="flex items-center gap-3">
            <Icon icon="lucide:users" />
            <p>Assign To</p>
          </div>

          <div class="relative" v-if="toggleRecipient">
            <input v-model="search" type="text"
              class="border border-primary px-4 py-2 rounded-md outline-none text-gray-700"
              placeholder="Search department / staff" @input="filterRecipients" />

            <div
              class="border border-solid py-3 bg-white max-h-[300px] overflow-y-auto rounded-md absolute z-50 w-[300px] shadow-xl mt-2"
              v-if="users && users.length > 0">
              <div class="flex items-center p-2 px-3  justify-between hover:bg-[#f8f8f9]"
                v-for="(suggestion, index) in users" :key="index" @click="updateUsers(suggestion)">
                <div class=" flex items-center gap-3 ">
                  <img :src="page.props.base + '/' + suggestion.profile_image" alt="" class="w-8 h-8 rounded-full" />
                  <p class="text-sm w-fit text-black font-semibold">{{
                  (suggestion.first_name + " " + suggestion.last_name).length > 15
                    ? `${(suggestion.first_name + " " + suggestion.last_name).substring(0, 15)}..`
                    : suggestion.first_name + " " + suggestion.last_name
                }}</p>
                </div>

                <div>
                  <p>{{ suggestion.email }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="flex bg-slate-50 py-1.5 px-3 rounded-2xl items-center gap-2"
            v-if="!toggleRecipient && Assigned_by === ''" @click="toggleRecipient = true">
            <p>Set assignee</p>
            <Icon icon="lucide:plus" />
          </div>

          <!-- <div
            class="flex items-center gap-2 h-fit bg-[#f8f8f9] p-2 py-1 rounded-3xl border border-solid border-gray-300"
            v-if="Assigned_by">
            <img :src="page.props.base + '/' + assigned_by_img" alt="" class="w-5 h-5 rounded-full" />
            <p class="text-xs w-fit">{{ Assigned_by }}</p>
            <div class="bg-white border rounded-full p-0.5 text-sm">
              <Icon icon="clarity:times-line" class="cursor-pointer" @click="Assigned_by = ''" />
            </div>
          </div> -->

          <div v-if="Assigned_by  !=''" class="flex items-center flex-wrap gap-2">
            <div v-for="user in selectedUsers"
              class="flex items-center gap-2 h-fit bg-[#f8f8f9] p-2 py-1 rounded-3xl border border-solid border-gray-300">
              <img :src="page.props.base + '/' + user.profile_image" alt="" class="w-5 h-5 rounded-full" />
              <p class="text-xs w-fit">{{ user.first_name+' '+user.last_name }}</p>
              <div class="bg-white border rounded-full p-0.5 text-sm">
                <Icon icon="clarity:times-line" class="cursor-pointer" @click="removeUser(user)" />
              </div>
            </div>
              <!-- <div
                class="flex items-center gap-2 h-fit bg-[#f8f8f9] p-2 py-1 rounded-3xl border border-solid border-gray-300"
                v-for="(tag, index) in Tags" :key="index">
                <img :src="tag.img" alt="" class="w-5" />
                <p class="text-xs w-fit">{{ tag.name }}</p>
                <div class=" bg-white border rounded-full p-0.5 text-sm">
                  <Icon icon="clarity:times-line" class="cursor-pointer" @click="removeTag(tag)" />
                </div>
              </div> -->
              <!-- <div class="relative">
                <input v-model="Users" type="text"
                  class="border border-primary px-4 py-2 rounded-md outline-none text-gray-700" placeholder=""
                  @input="filterRecipients" />

                <div
                  class="border border-solid py-3 bg-white right-0 max-h-[300px] overflow-y-auto rounded-md absolute z-50 w-[300px] shadow-xl mt-2"
                  v-if="Users && Suggestions.length > 0">
                  <div class="flex items-center p-2 px-3 justify-between hover:bg-[#f8f8f9]"
                    v-for="(suggestion, index) in Suggestions" :key="index" @click="updateUsers(suggestion)">
                    <div class="flex items-center gap-3">
                      <img :src="suggestion.img" alt="" class="w-8" />
                      <p class="text-sm w-fit text-black font-semibold">
                        {{
                        suggestion.name.length > 15
                          ? `${suggestion.name.substring(0, 15)}..`
                          : suggestion.name
                      }}
                      </p>
                    </div>

                    <div>
                      <p>{{ suggestion.email }}</p>
                    </div>
                  </div>
                </div>
              </div> -->
              <div class="relative">
            <input v-model="search" type="text"
              class="border border-primary px-4 py-2 rounded-md outline-none text-gray-700"
              placeholder="Search department / staff" @input="filterRecipients" />

            <div
              class="border border-solid py-3 bg-white max-h-[300px] overflow-y-auto rounded-md absolute z-50 w-[300px] shadow-xl mt-2"
              v-if="users && users.length > 0">
              <div class="flex items-center p-2 px-3  justify-between hover:bg-[#f8f8f9]"
                v-for="(suggestion, index) in users" :key="index" @click="updateUsers(suggestion)">
                <div class=" flex items-center gap-3 ">
                  <img :src="page.props.base + '/' + suggestion.profile_image" alt="" class="w-8 h-8 rounded-full" />
                  <p class="text-sm w-fit text-black font-semibold">{{
                  (suggestion.first_name + " " + suggestion.last_name).length > 15
                    ? `${(suggestion.first_name + " " + suggestion.last_name).substring(0, 15)}..`
                    : suggestion.first_name + " " + suggestion.last_name
                }}</p>
                </div>

                <div>
                  <p>{{ suggestion.email }}</p>
                </div>
              </div>
            </div>
          </div>
            </div>
        </div>
        <div v-if="form.errors.users" class=" text-red-400">{{ form.errors.users }}</div>


        <div class="flex items-center gap-14 mt-5">
          <div class="flex items-center gap-3">
            <Icon icon="lucide:calendar" />
            <p>Due date</p>
          </div>
          <div class="relative" v-if="!form.due_date">
            <div class="flex bg-slate-50 py-1.5 px-3 rounded-2xl items-center gap-2">
              <p>Set deadline</p>
              <Icon icon="lucide:plus" />
            </div>
            <div class="absolute top-0 opacity-0">
              <Calendar v-model="form.due_date" />
            </div>
          </div>

          <div
            class="flex items-center gap-2 h-fit bg-[#f8f8f9] p-2 py-1 rounded-3xl border border-solid border-gray-300"
            v-else>
            <p class="text-xs w-fit">{{ formattedDate }}</p>
            <div class="bg-white border rounded-full p-0.5 text-sm">
              <Icon icon="clarity:times-line" class="cursor-pointer" @click="form.due_date = null" />
            </div>
          </div>
        </div>
        <div v-if="form.errors.due_date" class=" text-red-400">{{ form.errors.due_date }}</div>

        <div class="flex items-center gap-24 mt-5">
          <div class="flex items-center gap-3">
            <Icon icon="lucide:calendar" />
            <p>Priority</p>
          </div>
          <div class="relative" v-if="!priority">
            <div class="flex bg-slate-50 py-1.5 px-3 rounded-2xl items-center gap-2" @click="select_Priority = true">
              <Icon icon="lucide:plus" />
              <p>Set Priority</p>
            </div>

            <div class="z-50 absolute top-12 bg-white border flex items-center gap-5 p-5 rounded-xl shadow-lg"
              v-if="select_Priority">
              <div v-for="(priority, index) in Priorities" :key="index" class=" " @click="updatePriority(priority)">
                <p class="bg-[#FEFBF6] px-5 py-1.5 rounded-3xl cursor-pointer" :style="{
    color:
      priority === 'Low'
        ? '#FFBC53'
        : priority === 'High'
          ? '#FF3737'
          : priority === 'Medium'
            ? '#F67E5A'
            : 'black',
  }">
                  {{ priority }}
                </p>
              </div>
            </div>
          </div>

          <div class="bg-[#FEFBF6]  flex items-center gap-3 px-5 py-1.5 rounded-3xl cursor-pointer" :style="{
    color:
      priority === 'Low'
        ? '#FFBC53'
        : priority === 'High'
          ? '#FF3737'
          : priority === 'Medium'
            ? '#F67E5A'
            : 'black',
  }" v-else>
            <p class="text-xs w-fit">{{ priority }}</p>
            <div class="">
              <Icon icon="clarity:times-line" class="cursor-pointer" @click="priority = ''" />
            </div>
          </div>
        </div>
        <div v-if="form.errors.priority" class=" text-red-400">{{ form.errors.priority }}</div>

        <div class=" mt-5 " :class="{ 'hidden': buttonSwitch === 2 }">
          <div class="flex items-center gap-3">
            <Icon icon="lucide:file-text" />
            <p>Task Description</p>
          </div>

          <textarea v-model="form.description" id="" cols="30" rows="5"
            class=" bg-slate-50 outline-none mt-5 resize-none p-5 rounded-md w-full ">
      </textarea>
        </div>

        <div class=" mt-5" :class="{ 'hidden': buttonSwitch === 3 }">
          <div class=" flex items-center gap-3">
            <Icon icon="grommet-icons:attachment" width="20" />
            <p>Attached Document</p>
          </div>

          <div class="w-[70%] lg:w-[100%] mt-3  gap-4 flex items-center flex-wrap ">


            <!-- <div class="border  p-3 rounded-md">
              <div class="flex justify-between">
                <div class="flex items-center gap-1 justify-center">
                  <img src="../../../assets/images/file.svg" alt="" class="w-7" />
                  <div class="">
                    <h1 class="text-[#1D2939]">Dmiye.pdf</h1>
                    <div class="flex items-center gap-5">
                      <p class="text-xs leading-7 text-[#667085]">60 KB of 120 KB.</p>
                      <span class="flex items-center text-xs gap-1">
                        <img src="../../../assets/images/loading.svg" alt="" /> Uploading...
                      </span>
                    </div>
                  </div>
                </div>
                <div>
                  <i class="pi pi-times-circle cursor-pointer text-[#1D2939]"></i>
                </div>
              </div>
              

              <div class="w-full h-2 bg-slate-200 mt-2 rounded-md overflow-hidden">
                <div class="w-[50%] h-[100%] bg-primary"></div>
              </div>
            </div> -->


            <div class=" w-1/3 border  p-2 rounded-md" v-for="(file, index) in files" :key="index">
              <!-- Uploading File Content -->
              <div class="mb-1 flex justify-between items-center">
                <div class="flex items-center gap-x-3">
                  <span class="w-8 h-8 flex justify-center items-center text-gray-500">
                    <!-- <svg width="42" height="43" viewBox="0 0 42 43" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M31.6313 3.2207L38.934 10.8332V39.7817H11.6537V39.8762H39.0272V10.929L31.6313 3.2207Z" fill="#909090"/>
                        <path d="M31.5407 3.125H11.5605V39.7805H38.934V10.8333L31.5407 3.125Z" fill="#F4F4F4"/>
                        <path d="M11.3597 5.09375H2.97281V14.0542H29.3541V5.09375H11.3597Z" fill="#7A7B7C"/>
                        <path d="M29.4945 13.9025H3.1434V4.93555H29.4945V13.9025Z" fill="#DD2025"/>
                        <path d="M11.8807 6.44956H10.1652V12.7496H11.5145V10.6246L11.8124 10.6417C12.1019 10.6367 12.3887 10.5848 12.6616 10.4881C12.9009 10.4058 13.121 10.2759 13.3087 10.1062C13.4996 9.94451 13.6502 9.74047 13.7483 9.51031C13.88 9.12771 13.927 8.7211 13.8862 8.31856C13.878 8.031 13.8275 7.74621 13.7365 7.47331C13.6537 7.27631 13.5307 7.09872 13.3755 6.95185C13.2202 6.80497 13.0361 6.69202 12.8348 6.62019C12.6608 6.55718 12.481 6.51146 12.298 6.48369C12.1595 6.46231 12.0195 6.4509 11.8793 6.44956M11.6313 9.46044H11.5145V7.51794H11.7678C11.8796 7.50987 11.9918 7.52704 12.096 7.56816C12.2003 7.60927 12.294 7.6733 12.3702 7.7555C12.5281 7.96683 12.6125 8.22406 12.6104 8.48787C12.6104 8.81075 12.6104 9.10344 12.319 9.3095C12.1091 9.42495 11.8702 9.47825 11.6313 9.46044ZM16.4495 6.4325C16.3038 6.4325 16.162 6.443 16.0623 6.44694L15.7499 6.45481H14.7262V12.7548H15.931C16.3915 12.7674 16.8499 12.6894 17.2803 12.5251C17.6267 12.3877 17.9334 12.1663 18.1728 11.8807C18.4056 11.5925 18.5727 11.257 18.6623 10.8976C18.7655 10.4906 18.8158 10.0719 18.812 9.65206C18.8374 9.15614 18.7991 8.65901 18.6978 8.17287C18.6017 7.81504 18.4217 7.48524 18.1728 7.21081C17.9775 6.98921 17.7384 6.81046 17.4706 6.68581C17.2406 6.57939 16.9987 6.50095 16.75 6.45219C16.6511 6.43585 16.551 6.42838 16.4508 6.42987M16.2119 11.5972H16.0807V7.57569H16.0977C16.3683 7.54455 16.6421 7.59338 16.8852 7.71612C17.0633 7.8583 17.2084 8.03742 17.3105 8.24112C17.4207 8.4555 17.4842 8.69079 17.4968 8.9315C17.5087 9.22025 17.4968 9.4565 17.4968 9.65206C17.5022 9.87733 17.4877 10.1026 17.4535 10.3254C17.4131 10.554 17.3383 10.7753 17.2317 10.9816C17.1111 11.1735 16.9481 11.3351 16.7553 11.4541C16.5934 11.5589 16.4016 11.6077 16.2093 11.5932M22.8768 6.45481H19.6874V12.7548H21.0367V10.2558H22.7429V9.08506H21.0367V7.62556H22.8742V6.45481" fill="#464648"/>
                        <path d="M28.5876 27.0852C28.5876 27.0852 32.7718 26.3266 32.7718 27.7559C32.7718 29.1852 30.1796 28.6038 28.5876 27.0852ZM25.494 27.1942C24.8292 27.341 24.1814 27.5561 23.5607 27.836L24.0857 26.6547C24.6107 25.4735 25.1554 23.863 25.1554 23.863C25.7819 24.9174 26.5108 25.9074 27.3315 26.8188C26.7126 26.9111 26.0991 27.0373 25.494 27.1968V27.1942ZM23.8376 18.6629C23.8376 17.4173 24.2406 17.0774 24.5543 17.0774C24.868 17.0774 25.221 17.2283 25.2328 18.3098C25.1306 19.3973 24.9029 20.4693 24.5543 21.5045C24.0767 20.6354 23.8295 19.6585 23.8363 18.6668L23.8376 18.6629ZM17.7358 32.4652C16.4522 31.6973 20.4278 29.3335 21.1483 29.2574C21.1444 29.2587 19.0798 33.2684 17.7358 32.4652ZM33.9938 27.9252C33.9806 27.794 33.8625 26.341 31.2769 26.4027C30.1992 26.3854 29.122 26.4613 28.0573 26.6298C27.0261 25.5908 26.138 24.4189 25.4166 23.1451C25.8711 21.8318 26.1461 20.4632 26.2343 19.0763C26.1962 17.5013 25.8195 16.5983 24.612 16.6115C23.4045 16.6246 23.2286 17.6812 23.3875 19.2535C23.543 20.3102 23.8365 21.3418 24.2603 22.3222C24.2603 22.3222 23.7025 24.0586 22.9648 25.7858C22.2272 27.5131 21.7232 28.4187 21.7232 28.4187C20.4405 28.8363 19.233 29.4566 18.1466 30.2562C17.0651 31.2629 16.6255 32.036 17.1951 32.809C17.686 33.4758 19.404 33.6267 20.9396 31.6147C21.7556 30.5754 22.501 29.4827 23.1709 28.3439C23.1709 28.3439 25.5124 27.7021 26.2408 27.5262C26.9693 27.3503 27.85 27.2112 27.85 27.2112C27.85 27.2112 29.988 29.3624 32.05 29.2863C34.1119 29.2102 34.0121 28.0538 33.999 27.9278" fill="#DD2025"/>
                        <path d="M31.4397 3.22656V10.9349H38.833L31.4397 3.22656Z" fill="#909090"/>
                        <path d="M31.5407 3.125V10.8333H38.934L31.5407 3.125Z" fill="#F4F4F4"/>
                        <path d="M11.7796 6.34995H10.0642V12.65H11.4187V10.5263L11.718 10.5434C12.0075 10.5384 12.2942 10.4865 12.5671 10.3898C12.8064 10.3075 13.0265 10.1776 13.2142 10.0079C13.4037 9.84578 13.5529 9.64177 13.65 9.41201C13.7816 9.02942 13.8286 8.62281 13.7878 8.22026C13.7796 7.93271 13.7292 7.64791 13.6381 7.37501C13.5553 7.17802 13.4323 7.00043 13.2771 6.85355C13.1219 6.70667 12.9377 6.59372 12.7365 6.52189C12.5616 6.45827 12.3809 6.41211 12.197 6.38408C12.0585 6.3627 11.9185 6.35129 11.7783 6.34995M11.5303 9.36083H11.4135V7.41833H11.6681C11.7799 7.41026 11.8921 7.42743 11.9963 7.46855C12.1006 7.50967 12.1943 7.57369 12.2705 7.65589C12.4284 7.86722 12.5128 8.12446 12.5107 8.38826C12.5107 8.71114 12.5107 9.00383 12.2193 9.20989C12.0094 9.32534 11.7705 9.37732 11.5316 9.35951M16.3485 6.33289C16.2028 6.33289 16.061 6.34339 15.9613 6.34733L15.6528 6.3552H14.6291V12.6552H15.834C16.2944 12.6678 16.7529 12.5898 17.1832 12.4255C17.5296 12.2881 17.8363 12.0667 18.0757 11.7811C18.3085 11.4929 18.4756 11.1574 18.5653 10.798C18.6684 10.391 18.7187 9.97234 18.7149 9.55245C18.7404 9.05653 18.702 8.5594 18.6007 8.07326C18.5046 7.71543 18.3246 7.38564 18.0757 7.1112C17.8804 6.8896 17.6413 6.71085 17.3735 6.5862C17.1435 6.47978 16.9016 6.40134 16.653 6.35258C16.5541 6.33624 16.4539 6.32877 16.3537 6.33026M16.1148 11.4976H15.9836V7.47608H16.0006C16.2712 7.44494 16.545 7.49377 16.7881 7.61651C16.9662 7.75869 17.1113 7.93781 17.2134 8.14151C17.3236 8.35589 17.3871 8.59118 17.3998 8.83189C17.4116 9.12064 17.3998 9.35689 17.3998 9.55245C17.4051 9.77773 17.3906 10.003 17.3565 10.2258C17.316 10.4544 17.2412 10.6757 17.1346 10.882C17.014 11.0738 16.851 11.2355 16.6582 11.3545C16.4963 11.4592 16.3045 11.5081 16.1122 11.4936M22.7758 6.3552H19.5864V12.6552H20.9356V10.1562H22.6419V8.98545H20.9356V7.52595H22.7731V6.3552" fill="white"/>
                        </svg> -->
                    <div>
                      <img :src="getFileURL(file)" class=" h-[42px] w-[43px] ml-3" alt="File Preview"
                        style="max-width: 200px; max-height: 200px;" />
                    </div>

                  </span>
                  <div class="ml-2">
                    <p class="text-sm font-medium text-gray-800 dark:text-white">{{ file.name }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-500 flex mt-1" v-if="form.progress">{{
                      form.progress.loaded / 1024 }} KB of {{ formatSize(file.size) }}
                    <div aria-label="Loading..." role="status" class="flex items-center space-x-2 ml-3">
                      <svg class=" h-5 w-5 animate-spin stroke-gray-500" viewBox="0 0 256 256">
                        <line x1="128" y1="32" x2="128" y2="64" stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="24"></line>
                        <line x1="195.9" y1="60.1" x2="173.3" y2="82.7" stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="24"></line>
                        <line x1="224" y1="128" x2="192" y2="128" stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="24">
                        </line>
                        <line x1="195.9" y1="195.9" x2="173.3" y2="173.3" stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="24"></line>
                        <line x1="128" y1="224" x2="128" y2="192" stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="24">
                        </line>
                        <line x1="60.1" y1="195.9" x2="82.7" y2="173.3" stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="24"></line>
                        <line x1="32" y1="128" x2="64" y2="128" stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="24"></line>
                        <line x1="60.1" y1="60.1" x2="82.7" y2="82.7" stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="24">
                        </line>
                      </svg>
                      <span class="text-xs font-medium text-gray-500">Loading...</span>
                    </div>
                    </p>
                  </div>
                </div>
                <svg v-if="form.progress" class=" absolute top-1 right-1 cursor-pointer h-[18px] w-[18px]"
                  viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M11.25 6.75L6.75 11.25M6.75 6.75L11.25 11.25M16.5 9C16.5 13.1421 13.1421 16.5 9 16.5C4.85786 16.5 1.5 13.1421 1.5 9C1.5 4.85786 4.85786 1.5 9 1.5C13.1421 1.5 16.5 4.85786 16.5 9Z"
                    stroke="#475467" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>

              </div>
              <!-- End Uploading File Content -->

              <progress v-if="form.progress" :value="form.progress.percentage" max="100" class="w-full rounded-lg h-2">
                {{ form.progress.percentage }}%
              </progress>
              <!-- End Progress Bar -->
            </div>



            <div class=" p-7 border-dashed border-[3px] rounded-md cursor-pointer" @click="getFile">
              <Icon icon="lucide:plus" width="30" />
            </div>
          </div>


        </div>

        <!-- <div class=" mt-5">
               <p>Submission Type</p>

               <div  v-for="(item,index) in type" :key="index" class=" flex mt-3 items-center gap-5">
                   <input type="radio" v-model="selectedType" :value="index">
                   <p class=" text-black font-semibold">{{ item }}</p>
               </div>
        </div> -->
      </div>

    </section>

    <div class="flex p-3 justify-end w-full">
      <div class="flex gap-5 items-center px-5">
        <!-- <Icon  icon="grommet-icons:attachment" :class="{ 'hidden': buttonSwitch === 3 }" width="25" @click="getFile" class=" cursor-pointer" />
          <Icon icon="mingcute:delete-2-line" width="25" @click="closeModal" /> -->
        <button class="p-2 border-[2px] border-solid rounded-md bg-white" @click="submit">
          Create Task
        </button>
      </div>
    </div>
  </Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed, ref } from "vue";
import image from "../../../assets/images/members/1.svg";
import { useToast } from "primevue/usetoast";
import { useForm, usePage } from "@inertiajs/vue3";

const selectedType = ref(null);
const select_Priority = ref(false);
const toggleRecipient = ref(false);
const date = ref();
const uses = ref([])
const files = ref([])
const page = usePage()
const assigned_by_img = ref(null);

const users = ref([]);
const Assigned_by = ref("");
const buttonSwitch = ref(1);
const clickedButton = ref("");
const search = ref('')
const user_ids = ref([]);
const selectedUsers = ref([])

const props = defineProps(["modelValue"]);

const form = useForm({
  files: [],
  title: '',
  due_date: '',
  priority: '',
  description: '',
  users: []
})

const getFile = () => {
  let file = document.getElementById('fileUpload');
  file.click();
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

const uploadFiles = () => {
  form.post('/upload-files', {
    onSuccess: () => {
      //alert(' this was successfull')
      form.files = null;
      //files.value = []
    }
  })
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


const searchUsers = () => {
  axios.get('/dashboard/search-users', { params: { search: search.value } }).then((response) => {
    users.value = response.data
  })
}


const filterRecipients = () => {
  // const searchTerm = Users.value.toLowerCase();
  // Suggestions.value = Recipients.value.filter((recipient) =>
  //   recipient.name.toLowerCase().includes(searchTerm)
  // );
  searchUsers()
};
const updateUsers = (suggestion) => {
  Assigned_by.value = suggestion.first_name + " " + suggestion.last_name;
  assigned_by_img.value = suggestion.profile_image
  user_ids.value = suggestion.id
  toggleRecipient.value = false;
  selectedUsers.value.push(suggestion)
  form.users.push(suggestion.id)

  let index = users.value.findIndex((item)=>item.id == suggestion.id)
  users.value.splice(index,1);
  // Suggestions.value = [];
};

const removeUser = (user)=>{
  users.value.push(user)
  let index = selectedUsers.value.findIndex((item)=>item.id == user.id)
  selectedUsers.value.splice(index,1)
}

const formatDate = (dateString) => {
  const date = new Date(dateString);
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
  form.post('/dashboard/create-task', {
    onSuccess: page => {
      form.reset()
      emit("update:modelValue", false);
    }
  })


  //   toast.add({
  //     severity: "success",
  //     summary: '',
  //     detail: "",
  //     life: 3000,
  //   });
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