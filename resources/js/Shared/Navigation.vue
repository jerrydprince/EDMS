<template>
  <!-- <main class="h-screen flex w-full max-w-[1680px] fixed top-0"> -->
  <section
    class="hide_scroll hidden 2xl:block w-1/5 border-r border-solid border-gray-200 sticky top-0 h-screen px-5 py-5 overflow-y-scroll"
    :class="{ 'w-[7%]': shouldShow }">
    <div class="text-xl font-semibold flex items-center gap-4 border-b border-dashed border-gray-200 pb-3 px-3"
      :class="{ 'px-0': shouldShow }">
      <img src="../../../public/images/default-logo.png" alt="" v-if="!shouldShow">
      <div v-else>
        <logo />
      </div>

    </div>
    <div class="border-b border-dashed pb-4">
      <p class="text-gray-500 mt-4 mb-2 px-3" :class="{ 'text-center': shouldShow }">
        Main
      </p>
      <section>
        <div v-for="(item, index) in main" :key="index" class="mb-1">
          <div v-if="checkPermission(item)">
            <Link :href="item.path" v-if="userHasPermission(item.permission)" :class="($page.url === item.path
              ? 'px-3 border bg-gray-25 text-primary bg-[#F9FAFB]'
              : 'px-3') + (shouldShow ? ' w-fit' : '')
              "
              class="py-3 flex gap-3 items-center text-gray-700 border-solid border-gray-200 rounded-xl hover:bg-gray-25 hover:text-primary font-normal transition-all">
            <!-- <Icon :icon="item.icon" width="25" /> -->
            <span v-html="item.icon"></span>
            <p class="text-lg" v-if="!shouldShow">{{ item.name }}</p>
            </Link>
          </div>
        </div>
      </section>
    </div>
    <div v-if="role.includes('super admin') || role.includes('admin')" class="border-b border-dashed pb-4"
      :class="{ '-ml-4': shouldShow }">
      <p class="text-gray-500 mt-4 mb-2 px-3" :class="{ ' ml-3': shouldShow }">
        Admin
      </p>
      <section>
        <div v-for="(item, index) in admin" :key="index" class="mb-1">
          <Link :href="item.path" v-if="userHasPermission(item.permission)" :class="$page.url === item.path
            ? 'px-3 border bg-gray-25 text-primary bg-[#F9FAFB]'
            : 'hover:border-0 px-3'
            "
            class="py-3 flex gap-3 items-center text-gray-700 border-solid border-gray-200 rounded-xl hover:bg-gray-25 hover:text-primary font-normal transition-all">
          <!-- <Icon :icon="item.icon" width="25" :class="{ 'ml-5': shouldShow }" /> -->
          <span v-html="item.icon" :class="{ 'ml-5': shouldShow }"></span>
          <p class="text-lg" v-if="!shouldShow">{{ item.name }}</p>
          </Link>
        </div>
      </section>
    </div>
    
    <div class="pb-4 mb-10" v-if="branches && branches.length > 0 && role.includes('data entry')">
      <div class="text-gray-500 mt-4 mb-2 flex items-center justify-between px-3" :class="{ ' -ml-3': shouldShow }">
        <div class="flex items-center gap-2">
          <Icon icon="ic:round-greater-than" width="20" v-if="!shouldShow" />
          <p>Branches </p>
        </div>
        <Icon icon="line-md:grid-3-filled" width="20" v-if="!shouldShow" />
      </div>
      <section>
        <div v-for="(item, index) in branches" :key="index" class="mb-1">
          <Link :href="item.path" v-if="userHasPermission(item.permission)" :class="$page.url === item.path
            ? ' px-3 border bg-gray-25 text-primary bg-[#F9FAFB]'
            : ' px-3  '
            "
            class="py-3 flex justify-between items-center text-gray-700 border-solid border-gray-200 rounded-xl hover:bg-gray-25 hover:text-primary font-normal transition-all">
          <div class="flex items-center gap-3" :class="{ ' ml-2': shouldShow }">
            <div class="h-4 w-4 rounded-full border border-gray-300" :class="{ 'bg-primary': $page.url === item.path }"
              v-tooltip.right="{
                value: item.name,
                pt: {
                  arrow: {
                    style: {
                      borderRightColor: '',
                    },
                  },
                  text: 'bg-[#1D2939] text-xs text-white font-medium px-2 py-1 rounded',
                },
              }"></div>
            <p class="text-lg" v-if="!shouldShow">{{ item.name }}</p>
          </div>
         
          <span v-html="item.icon" v-if="!shouldShow"></span>
          </Link>
        </div>
        
      </section>
    </div>

    <div class="pb-4 mb-10" v-if="isBranches">
      <div class="text-gray-500 mt-4 mb-2 flex items-center justify-between px-3 cursor-pointer group"
        @click="branchesOpen = !branchesOpen" :class="{ ' -ml-3': shouldShow }">
        <div class="flex items-center gap-3">
          <Icon icon="octicon:git-branch-24" width="25" v-if="!shouldShow" />
          <p>Branches</p>
        </div>
        <Icon :class="!branchesOpen ? '-rotate-90' : ''" icon="ep:arrow-down" width="19" v-if="!shouldShow" />
      </div>
      <section v-if="branchesOpen" class="border-gray-200 border-solid border-l-[1px] ml-4">
        <Link :href="isDepartmentRole(item)" v-for="(item, index) in roleBranches" :key="index" class="mb-1 ml-2 cursor-pointer">
          <div @mouseover="handleDivisionMouseOver(index, $event)"
            class="py-2 flex justify-between items-center text-gray-700 border-solid border-gray-200 rounded-xl hover:bg-[#F9FAFB] hover:text-primary font-normal transition-all px-3 hover:border">
            <div class="flex items-center gap-3" :class="{ ' ml-2': shouldShow }">
              <p class="text-[15px] text-gray-700" v-if="!shouldShow">
                {{ item.title }}
              </p>

              <!-- Divisions modal -->
              <Teleport to="body">
                <section v-if="openDivision === index" @mouseleave="handleDivisionMouseLeave"
                  @mouseenter="cancelDivisionClose" :style="{
                    top: `${divisionsPosition.top}px`,
                    left: `${divisionsPosition.left}px`,
                    zIndex: 9999,
                  }" class="w-[20rem] absolute drop-shadow-xl left-[17%]">
                  <div class="bg-white rounded-lg py-4 px-4 drop-shadow-sm">
                    <p class="float-right text-gray-400 text-[14px]">
                      Divisions
                    </p>
                    <br />
                    <div>
                      <section class="">
                        <Link :href="isDivisionRole(division)" v-for="(division, divisionIndex) in item.division" @mouseover="
                          handleUnitMouseOver(divisionIndex, $event)
                          " :key="divisionIndex" class="mb-1 cursor-pointer">
                          <div
                            class="py-4 flex justify-between items-center text-gray-700 border-solid border-gray-200 rounded-xl hover:bg-[#F9FAFB] hover:text-primary font-normal transition-all px-3 hover:border">
                            <p class="text-[15px] text-gray-700" v-if="!shouldShow">
                              {{ division.title }}
                            </p>

                            <!-- For Units -->
                            <Teleport to="body">
                              <section @mouseleave="handleUnitMouseLeave" @mouseenter="cancelDivisionClose"
                                v-if="openUnit === divisionIndex" :style="{
                                  top: `${unitsPosition.top}px`,
                                  left: `${unitsPosition.left}px`,
                                  zIndex: 9999,
                                }" class="w-[20rem] absolute drop-shadow-xl">
                                <div class="bg-white rounded-lg py-4 px-4">
                                  <p class="float-right text-gray-400 text-[14px]">
                                    Units
                                  </p>
                                  <br />
                                  <section>
                                    <Link :href="isUnitRole(unit)" v-for="(
                                          unit, unitIndex
                                        ) in division.unit" :key="unitIndex" @click="redirectBranch('Unit', unit)"
                                      class="mb-1">
                                      <div
                                        class="py-4 flex justify-between items-center text-gray-700 border-solid border-gray-200 rounded-xl hover:bg-[#F9FAFB] hover:text-primary font-normal transition-all px-3 hover:border">
                                        <p class="text-[15px] text-gray-700" v-if="!shouldShow">
                                          {{ unit.title }}
                                        </p>
                                      </div>
                                    </Link>
                                  </section>
                                </div>
                              </section>
                            </Teleport>
                          </div>
                        </Link>
                      </section>
                    </div>
                  </div>
                </section>
              </Teleport>
            </div>
            <Icon class="border-[1px] border-solid border-gray-200 rounded-md p-2 pr-3"
              icon="fluent:ios-arrow-right-24-regular" width="35" v-if="!shouldShow" />
          </div>
        </Link>
      </section>
      <!-- More branches -->
      <div class="cursor-pointer ml-4 bg-gray-100 text-gray-600 rounded-xl w-[30px]">
        <Icon class="m-auto w-full" icon="solar:menu-dots-bold" width="17" />
      </div>
    </div>

    <div class="border-t border-dashed pt-2 pb-4">
      <section>
        <div class="mb-1">
          <Link :href="'/dashboard/support'"
            class="px-3 py-3 flex gap-3 items-center text-gray-700 border-solid border-gray-200 rounded-xl hover:bg-gray-25 hover:text-primary font-normal transition-all"
            :class="$page.url === '/support'
              ? ' px-3 border bg-gray-25 text-primary bg-[#F9FAFB]'
              : ' px-3  '
              ">
          <Icon icon="system-uicons:support" width="30" :class="{ ' ml-1': shouldShow }" />
          <p class="text-lg" v-if="!shouldShow">Support</p>
          </Link>
        </div>
        <div class="mb-1">
          <Link :href="'/settings'"
            class="px-3 py-3 flex gap-3 items-center text-[#344054] rounded-xl  hover:text-primary font-normal transition-all"
            :class="$page.url === '/settings'
              ? ' px-3 border bg-gray-25 text-primary bg-[#F9FAFB]'
              : ' px-3  '
              ">
          <Icon icon="uiw:setting-o" width="25" :class="{ 'ml-1.5': shouldShow }" />
          <p class="text-lg" v-if="!shouldShow">Settings</p>
          </Link>
        </div>
      </section>
    </div>
  </section>

</template>

<script setup>
import { ref, watch, onMounted, computed } from "vue";
import searchResult from "../components/Modals/SearchResult.vue";
import logo from "../assets/logo.vue";
import TopBar from "./TopBar.vue";
import { usePage, Link } from '@inertiajs/vue3'

const page = usePage()
const departments = computed(() => page.props.shared_departments)
const permissions = computed(() => page.props.permissions)
const role = computed(() => page.props.user_roles)
const branches = ref([]);
const roleBranches = ref([])
const userBranchName = computed(() => page.props.branchName)
const userBranch = computed(() => page.props.userBranch)
const current_page = computed(() => page.url)
const isBranches = ref(false)

const toggleSearch = ref(false);

const toggleSearchState = () => {
  toggleSearch.value = !toggleSearch.value;
};


const openDivision = ref(null); // Track the currently open division index
const openUnit = ref(null); // Track the currently open unit index

const branchesOpen = ref(false);
const divisionsPosition = ref({ top: 0, left: 0 });
const unitsPosition = ref({ top: 0, left: 0 });
let divisionTimeout = null;

// Branches mouse hover function
const handleDivisionMouseOver = (index, event) => {
  const parent = event.currentTarget;
  const rect = parent.getBoundingClientRect();
  divisionsPosition.value = {
    top: rect.top + window.scrollY,
    left: rect.right + 10, // Adjust to position next to the parent
  };
  console.log(divisionsPosition.value);
  openDivision.value = index;
};

// Division mouse hover function
const handleUnitMouseOver = (divisionIndex, event) => {
  const parent = event.currentTarget;
  const rect = parent.getBoundingClientRect();
  unitsPosition.value = {
    top: rect.top + window.scrollY,
    left: rect.right + 20, // Adjust to position next to the division
  };
  openUnit.value = divisionIndex;
};

// Close the division modal with a slight delay to allow for movement between modals
const handleDivisionMouseLeave = () => {
  divisionTimeout = setTimeout(() => {
    openDivision.value = null;
    openUnit.value = null;
  }, 500);
};

// Close the unit modal immediately
const handleUnitMouseLeave = () => {
  openUnit.value = null;
  handleDivisionMouseLeave();
};

const isDepartmentRole = (item)=>{
  if(role.value.includes('super admin') || role.value.includes('admin')
    || role.value.includes('super users') || role.value.includes('directors')){
      return '/dashboard/branches/department/'+item.id
  }else{
    return '#'
  }
}

const isDivisionRole = (item)=>{
  if(role.value.includes('super admin') || role.value.includes('admin')
    || role.value.includes('super users') || role.value.includes('directors')
  || role.value.includes('division heads')){
      return '/dashboard/branches/division/'+item.id
  }else{
    return '#'
  }
}

const isUnitRole = (item)=>{
  if(role.value.includes('super admin') || role.value.includes('admin')
    || role.value.includes('super users') || role.value.includes('directors')
    || role.value.includes('division heads') || role.value.includes('unit heads')){
      return '/dashboard/branches/unit/'+item.id
  }else{
    return '#'
  }
}

const checkPermission = (item) => {
  if (role.value.includes('record manager')) {
    console.log('from the record manager')
    if (item.name == 'Dashboard') {
      console.log('from the dashboard')
      return false
    } else {
      return true;
    }
  } else {
    return true;
  }
}

const shouldShow = ref(false);

const setDepartments = () => {
  if (role.value.includes('super users')) {
    for (let dept of departments.value) {
      let data = {
        name: dept.title,
        path: "/dashboard/branches/department/" + dept.id,
        icon: `<svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect x="0.5" y="0.5" width="9" height="9" rx="4.5" stroke="#344054"/>
        </svg>
        `,
        permission: 'edms view'
      }
      branches.value.push(data)
    }
  } else {

    const branchType = userBranchName.value;
    const branchData = userBranch.value[branchType];

    if (branchData && (role.value.includes('directors') || role.value.includes('unit heads') || role.value.includes('division heads') || role.value.includes('admin') || role.value.includes('super users'))) {
      let data = {
        name: branchData.title,
        path: `/dashboard/branches/${branchType}/${branchData.id}`,
        icon: `<svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect x="0.5" y="0.5" width="9" height="9" rx="4.5" stroke="#344054"/>
        </svg>`,
        permission: 'edms view'
      };
      branches.value.push(data);
    }
  }
}

const userHasPermission = (permit) => {
  if (permit == '') {
    return true;
  }
  if (role.value.includes('super admin')) {
    return true;
  } else {
    // let perm = permissions.value.filter(permission => permission.name == permit);
    // if (perm && perm.length > 0) {
    //   return true
    // } else {
    //   return false
    // }
    let index = permissions.value.findIndex(permission => permission.name == permit);
    if (index != -1) {
      return true
    } else {
      return false
    }
  }

}

const main = ref([
  {
    name: "Dashboard",
    path: "/dashboard",
    icon: `<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M7.5 18.3327V9.99935H12.5V18.3327M2.5 7.49935L10 1.66602L17.5 7.49935V16.666C17.5 17.108 17.3244 17.532 17.0118 17.8445C16.6993 18.1571 16.2754 18.3327 15.8333 18.3327H4.16667C3.72464 18.3327 3.30072 18.1571 2.98816 17.8445C2.67559 17.532 2.5 17.108 2.5 16.666V7.49935Z" stroke="#344054" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    `,
    permission: ''
  },
  {
    name: "Analytics",
    path: "/dashboard/analytics",
    icon: `<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M1.66667 1.66669V15.8334C1.66667 17.2167 1.61709 18.3334 3.00043 18.3334H18.3333" stroke="#344054" stroke-width="1.25" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M16.8341 6.66669L11.234 12.2667L9.10068 9.06669L4.83398 13.3334" stroke="#344054" stroke-width="1.38462" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M14.1667 6.66711H16.8334V9.33378" stroke="#344054" stroke-width="1.38462" stroke-linecap="round" stroke-linejoin="round"/>
</svg>

`,
    permission: 'analytics view'
  },
  {
    name: "Chat",
    path: "/dashboard/chat",
    icon: `<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M17.5 12.5C17.5 12.942 17.3244 13.366 17.0118 13.6785C16.6993 13.9911 16.2754 14.1667 15.8333 14.1667H5.83333L2.5 17.5V4.16667C2.5 3.72464 2.67559 3.30072 2.98816 2.98816C3.30072 2.67559 3.72464 2.5 4.16667 2.5H15.8333C16.2754 2.5 16.6993 2.67559 17.0118 2.98816C17.3244 3.30072 17.5 3.72464 17.5 4.16667V12.5Z" stroke="#344054" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
`,
    permission: 'chat view'
  },
  {
    name: "Mails",
    path: "/dashboard/mails",
    icon: `<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M18.3334 10.0007H13.3334L11.6667 12.5007H8.33335L6.66669 10.0007H1.66669M18.3334 10.0007V15.0007C18.3334 15.4427 18.1578 15.8666 17.8452 16.1792C17.5326 16.4917 17.1087 16.6673 16.6667 16.6673H3.33335C2.89133 16.6673 2.4674 16.4917 2.15484 16.1792C1.84228 15.8666 1.66669 15.4427 1.66669 15.0007V10.0007M18.3334 10.0007L15.4584 4.25898C15.3204 3.98131 15.1077 3.74763 14.8441 3.58422C14.5806 3.42081 14.2768 3.33415 13.9667 3.33398H6.03335C5.72328 3.33415 5.41941 3.42081 5.15589 3.58422C4.89237 3.74763 4.67967 3.98131 4.54169 4.25898L1.66669 10.0007" stroke="#344054" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
`,
    permission: 'mail view'
  },
  {
    name: 'Registry',
    path: '/dashboard/registry',
    icon: `<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M10.75 2.5H5.5C5.10218 2.5 4.72064 2.65804 4.43934 2.93934C4.15804 3.22064 4 3.60218 4 4V16C4 16.3978 4.15804 16.7794 4.43934 17.0607C4.72064 17.342 5.10218 17.5 5.5 17.5H14.5C14.8978 17.5 15.2794 17.342 15.5607 17.0607C15.842 16.7794 16 16.3978 16 16V7.75M10.75 2.5L16 7.75M10.75 2.5V7.75H16" stroke="#475467" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>`,
    permission: 'registry view'
  },
  // {
  //   name:'EDMS',
  //   path: '/edms/branches',
  //   icon: `<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
  //       <path d="M17.5 4.16663C17.5 5.54734 14.1421 6.66663 10 6.66663C5.85786 6.66663 2.5 5.54734 2.5 4.16663M17.5 4.16663C17.5 2.78591 14.1421 1.66663 10 1.66663C5.85786 1.66663 2.5 2.78591 2.5 4.16663M17.5 4.16663V15.8333C17.5 17.2166 14.1667 18.3333 10 18.3333C5.83333 18.3333 2.5 17.2166 2.5 15.8333V4.16663M17.5 9.99996C17.5 11.3833 14.1667 12.5 10 12.5C5.83333 12.5 2.5 11.3833 2.5 9.99996" stroke="#344054" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
  //       </svg>
  //       `,
  //       permission: 'edms view'
  // },
  {
    name: "Workflow",
    path: "/dashboard/workflow",
    icon: `<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
      <g clip-path="url(#clip0_7456_3387)">
      <path d="M7.22224 1.66675H3.51854C2.49579 1.66675 1.66669 2.49585 1.66669 3.5186V7.2223C1.66669 8.24505 2.49579 9.07416 3.51854 9.07416H7.22224C8.24499 9.07416 9.07409 8.24505 9.07409 7.2223V3.5186C9.07409 2.49585 8.24499 1.66675 7.22224 1.66675Z" stroke="#344054" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
      <path d="M5.37036 9.07422V12.7779C5.37036 13.2691 5.56547 13.7401 5.91276 14.0874C6.26005 14.4347 6.73107 14.6298 7.22221 14.6298H10.9259" stroke="#344054" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
      <path d="M16.4815 10.926H12.7778C11.7551 10.926 10.926 11.7551 10.926 12.7779V16.4816C10.926 17.5043 11.7551 18.3334 12.7778 18.3334H16.4815C17.5043 18.3334 18.3334 17.5043 18.3334 16.4816V12.7779C18.3334 11.7551 17.5043 10.926 16.4815 10.926Z" stroke="#344054" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
      </g>
      <defs>
      <clipPath id="clip0_7456_3387">
      <rect width="20" height="20" fill="white"/>
      </clipPath>
      </defs>
      </svg>

`,
    permission: 'workflow view'
  },
  {
    name: "PMS",
    path: "/dashboard/pms",
    icon: `<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
      <g clip-path="url(#clip0_7456_17054)">
      <mask id="mask0_7456_17054" style="mask-type:luminance" maskUnits="userSpaceOnUse" x="0" y="2" width="21" height="17">
      <path d="M16.8422 17.7117C20.147 14.4069 20.147 9.04891 16.8422 5.74414C13.5375 2.4394 8.17943 2.4394 4.87469 5.74414C1.56992 9.04891 1.56995 14.4069 4.87469 17.7117" stroke="white" stroke-width="1.41039" stroke-linecap="round"/>
      <path d="M13.7114 9.51888C15.5582 11.0872 15.7841 13.8557 14.2158 15.7026C12.6475 17.5494 9.879 17.7752 8.03215 16.207C6.1853 14.6387 0.839623 4.34406 0.839623 4.34406C0.839623 4.34406 11.8645 7.95062 13.7114 9.51888Z" fill="black"/>
      <path d="M12.5756 10.8565C13.6836 11.7974 13.8192 13.4586 12.8782 14.5667C11.9372 15.6749 10.2761 15.8103 9.168 14.8694C8.05985 13.9284 3.51487 6.61576 3.51487 6.61576C3.51487 6.61576 11.4674 9.91551 12.5756 10.8565Z" fill="white"/>
      </mask>
      <g mask="url(#mask0_7456_17054)">
      <path d="M22.1416 -0.495483H-0.424622V22.0707H22.1416V-0.495483Z" fill="#344054"/>
      </g>
      </g>
      <defs>
      <clipPath id="clip0_7456_17054">
      <rect width="20" height="20" fill="white"/>
      </clipPath>
      </defs>
      </svg>

`,
    permission: 'pms view'
  },
]);

const admin = ref([
  {
    name: "Manage Users",
    path: "/dashboard/manage-users",
    icon: `<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M14.1666 17.5V15.8333C14.1666 14.9493 13.8155 14.1014 13.1903 13.4763C12.5652 12.8512 11.7174 12.5 10.8333 12.5H4.16665C3.28259 12.5 2.43474 12.8512 1.80962 13.4763C1.1845 14.1014 0.833313 14.9493 0.833313 15.8333V17.5M19.1666 17.5V15.8333C19.1661 15.0948 18.9203 14.3773 18.4678 13.7936C18.0153 13.2099 17.3818 12.793 16.6666 12.6083M13.3333 2.60833C14.0503 2.79192 14.6858 3.20892 15.1397 3.79359C15.5935 4.37827 15.8398 5.09736 15.8398 5.8375C15.8398 6.57764 15.5935 7.29673 15.1397 7.88141C14.6858 8.46608 14.0503 8.88308 13.3333 9.06667M10.8333 5.83333C10.8333 7.67428 9.34093 9.16667 7.49998 9.16667C5.65903 9.16667 4.16665 7.67428 4.16665 5.83333C4.16665 3.99238 5.65903 2.5 7.49998 2.5C9.34093 2.5 10.8333 3.99238 10.8333 5.83333Z" stroke="#344054" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
`,
    permission: 'manage user view'
  },
  {
    name: "Branch Setup",
    path: "/dashboard/branch-setup",
    icon: `<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M17.5 4.16663C17.5 5.54734 14.1421 6.66663 10 6.66663C5.85786 6.66663 2.5 5.54734 2.5 4.16663M17.5 4.16663C17.5 2.78591 14.1421 1.66663 10 1.66663C5.85786 1.66663 2.5 2.78591 2.5 4.16663M17.5 4.16663V15.8333C17.5 17.2166 14.1667 18.3333 10 18.3333C5.83333 18.3333 2.5 17.2166 2.5 15.8333V4.16663M17.5 9.99996C17.5 11.3833 14.1667 12.5 10 12.5C5.83333 12.5 2.5 11.3833 2.5 9.99996" stroke="#344054" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
`,
    permission: 'branch setup view'
  },
  {
    name: "Access Config",
    path: "/dashboard/access-level",
    icon: `<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M7.84165 16.6666H5.41665C4.16665 16.6666 3.06665 16.2499 2.17498 15.3583C1.28331 14.4833 0.833313 13.4083 0.833313 12.1499C0.833313 11.0666 1.15831 10.0999 1.80831 9.24992C2.44216 8.41891 3.35271 7.84242 4.37498 7.62492C4.72498 6.34992 5.41665 5.31659 6.45831 4.52492C7.49998 3.74992 8.68331 3.33325 9.99998 3.33325C11.625 3.33325 13.0083 3.89992 14.1333 5.03325C15.2666 6.15825 15.8333 7.54158 15.8333 9.16658C16.7916 9.27492 17.5916 9.69159 18.2166 10.4166C18.75 11.0249 19.05 11.7166 19.1333 12.4999H17.4666C17.3833 12.0833 17.2 11.7416 16.8916 11.4416C16.4916 11.0333 16 10.8333 15.4166 10.8333H14.1666V9.16658C14.1666 8.01659 13.75 7.03325 12.95 6.21659C12.1416 5.41659 11.1583 4.99992 9.99998 4.99992C8.84998 4.99992 7.86665 5.41659 7.04998 6.21659C6.24998 7.03325 5.83331 8.01659 5.83331 9.16658H5.41665C4.60831 9.16658 3.92498 9.44992 3.35831 10.0249C2.78331 10.5916 2.49998 11.2749 2.49998 12.0833C2.49998 12.8916 2.78331 13.5833 3.35831 14.1666C3.92498 14.7249 4.60831 14.9999 5.41665 14.9999H7.49998C7.49998 15.5999 7.62498 16.1583 7.84165 16.6666ZM19.1666 14.1666V15.8333H17.5V17.4999H15.8333V15.8333H14C13.6666 16.8333 12.75 17.4999 11.6666 17.4999C10.25 17.4999 9.16665 16.4166 9.16665 14.9999C9.16665 13.5833 10.25 12.4999 11.6666 12.4999C12.75 12.4999 13.6666 13.1666 14 14.1666H19.1666ZM12.5 14.9999C12.5 14.5833 12.1666 14.1666 11.6666 14.1666C11.1666 14.1666 10.8333 14.5833 10.8333 14.9999C10.8333 15.4166 11.1666 15.8333 11.6666 15.8333C12.1666 15.8333 12.5 15.4166 12.5 14.9999Z" fill="#344054"/>
</svg>
`,
    permission: 'access config view'
  },
  {
    name: "Metadata Setup",
    path: "/dashboard/metadata-setup",
    icon: `<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<g clip-path="url(#clip0_7456_3427)">
<path d="M9.99998 12.4999C11.3807 12.4999 12.5 11.3806 12.5 9.99992C12.5 8.61921 11.3807 7.49992 9.99998 7.49992C8.61927 7.49992 7.49998 8.61921 7.49998 9.99992C7.49998 11.3806 8.61927 12.4999 9.99998 12.4999Z" stroke="#344054" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M16.1666 12.4999C16.0557 12.7513 16.0226 13.0301 16.0716 13.3004C16.1207 13.5707 16.2495 13.8202 16.4416 14.0166L16.4916 14.0666C16.6466 14.2214 16.7695 14.4052 16.8534 14.6075C16.9373 14.8098 16.9805 15.0267 16.9805 15.2458C16.9805 15.4648 16.9373 15.6817 16.8534 15.884C16.7695 16.0863 16.6466 16.2701 16.4916 16.4249C16.3369 16.5799 16.153 16.7028 15.9507 16.7867C15.7484 16.8706 15.5315 16.9137 15.3125 16.9137C15.0935 16.9137 14.8766 16.8706 14.6742 16.7867C14.4719 16.7028 14.2881 16.5799 14.1333 16.4249L14.0833 16.3749C13.8869 16.1828 13.6375 16.0539 13.3671 16.0049C13.0968 15.9559 12.818 15.989 12.5666 16.0999C12.3202 16.2056 12.11 16.381 11.9619 16.6045C11.8138 16.8281 11.7344 17.0901 11.7333 17.3583V17.4999C11.7333 17.9419 11.5577 18.3659 11.2452 18.6784C10.9326 18.991 10.5087 19.1666 10.0666 19.1666C9.62462 19.1666 9.2007 18.991 8.88813 18.6784C8.57557 18.3659 8.39998 17.9419 8.39998 17.4999V17.4249C8.39353 17.1491 8.30424 16.8816 8.14374 16.6572C7.98323 16.4328 7.75893 16.2618 7.49998 16.1666C7.24863 16.0557 6.96982 16.0226 6.69949 16.0716C6.42916 16.1206 6.17971 16.2495 5.98331 16.4416L5.93331 16.4916C5.77852 16.6465 5.59471 16.7695 5.39238 16.8533C5.19005 16.9372 4.97317 16.9804 4.75415 16.9804C4.53512 16.9804 4.31824 16.9372 4.11591 16.8533C3.91358 16.7695 3.72977 16.6465 3.57498 16.4916C3.42002 16.3368 3.29709 16.153 3.21321 15.9507C3.12934 15.7483 3.08617 15.5314 3.08617 15.3124C3.08617 15.0934 3.12934 14.8765 3.21321 14.6742C3.29709 14.4719 3.42002 14.288 3.57498 14.1332L3.62498 14.0833C3.81709 13.8869 3.94597 13.6374 3.99498 13.3671C4.044 13.0967 4.01091 12.8179 3.89998 12.5666C3.79434 12.3201 3.61894 12.1099 3.39537 11.9618C3.17179 11.8138 2.9098 11.7343 2.64165 11.7333H2.49998C2.05795 11.7333 1.63403 11.5577 1.32147 11.2451C1.00891 10.9325 0.833313 10.5086 0.833313 10.0666C0.833313 9.62456 1.00891 9.20063 1.32147 8.88807C1.63403 8.57551 2.05795 8.39992 2.49998 8.39992H2.57498C2.85081 8.39347 3.11832 8.30418 3.34273 8.14368C3.56714 7.98317 3.73808 7.75886 3.83331 7.49992C3.94424 7.24857 3.97733 6.96976 3.92832 6.69943C3.8793 6.4291 3.75043 6.17965 3.55831 5.98325L3.50831 5.93325C3.35335 5.77846 3.23042 5.59465 3.14655 5.39232C3.06267 5.18999 3.0195 4.97311 3.0195 4.75408C3.0195 4.53506 3.06267 4.31818 3.14655 4.11585C3.23042 3.91352 3.35335 3.72971 3.50831 3.57492C3.6631 3.41996 3.84692 3.29703 4.04925 3.21315C4.25158 3.12928 4.46845 3.08611 4.68748 3.08611C4.90651 3.08611 5.12338 3.12928 5.32571 3.21315C5.52804 3.29703 5.71186 3.41996 5.86665 3.57492L5.91665 3.62492C6.11305 3.81703 6.36249 3.94591 6.63282 3.99492C6.90315 4.04394 7.18197 4.01085 7.43331 3.89992H7.49998C7.74645 3.79428 7.95666 3.61888 8.10472 3.39531C8.25279 3.17173 8.33224 2.90974 8.33331 2.64159V2.49992C8.33331 2.05789 8.50891 1.63397 8.82147 1.32141C9.13403 1.00885 9.55795 0.833252 9.99998 0.833252C10.442 0.833252 10.8659 1.00885 11.1785 1.32141C11.4911 1.63397 11.6666 2.05789 11.6666 2.49992V2.57492C11.6677 2.84307 11.7472 3.10506 11.8952 3.32864C12.0433 3.55221 12.2535 3.72762 12.5 3.83325C12.7513 3.94418 13.0301 3.97727 13.3005 3.92826C13.5708 3.87924 13.8202 3.75037 14.0166 3.55825L14.0666 3.50825C14.2214 3.35329 14.4052 3.23036 14.6076 3.14649C14.8099 3.06261 15.0268 3.01944 15.2458 3.01944C15.4648 3.01944 15.6817 3.06261 15.884 3.14649C16.0864 3.23036 16.2702 3.35329 16.425 3.50825C16.5799 3.66304 16.7029 3.84685 16.7867 4.04918C16.8706 4.25151 16.9138 4.46839 16.9138 4.68742C16.9138 4.90644 16.8706 5.12332 16.7867 5.32565C16.7029 5.52798 16.5799 5.7118 16.425 5.86658L16.375 5.91658C16.1829 6.11298 16.054 6.36243 16.005 6.63276C15.956 6.90309 15.989 7.1819 16.1 7.43325V7.49992C16.2056 7.74639 16.381 7.9566 16.6046 8.10466C16.8282 8.25272 17.0902 8.33218 17.3583 8.33325H17.5C17.942 8.33325 18.3659 8.50885 18.6785 8.82141C18.991 9.13397 19.1666 9.55789 19.1666 9.99992C19.1666 10.4419 18.991 10.8659 18.6785 11.1784C18.3659 11.491 17.942 11.6666 17.5 11.6666H17.425C17.1568 11.6677 16.8948 11.7471 16.6713 11.8952C16.4477 12.0432 16.2723 12.2534 16.1666 12.4999Z" stroke="#344054" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
</g>
<defs>
<clipPath id="clip0_7456_3427">
<rect width="20" height="20" fill="white"/>
</clipPath>
</defs>
</svg>
`,
    permission: 'metadata setup view'
  },
  {
    name: "Admin Registry",
    path: "/dashboard/admin-registry",
    icon: `<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M10.75 2.5H5.5C5.10218 2.5 4.72064 2.65804 4.43934 2.93934C4.15804 3.22064 4 3.60218 4 4V16C4 16.3978 4.15804 16.7794 4.43934 17.0607C4.72064 17.342 5.10218 17.5 5.5 17.5H14.5C14.8978 17.5 15.2794 17.342 15.5607 17.0607C15.842 16.7794 16 16.3978 16 16V7.75M10.75 2.5L16 7.75M10.75 2.5V7.75H16" stroke="#475467" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
`,
    permission: 'admin registry view'
  },
  {
    name: "Audit Log",
    path: "/audit-log",
    icon: `<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M1.66669 12.0833L10 16.25L18.3334 12.0833M10 3.75L1.66669 7.91667L10 12.0833L18.3334 7.91667L10 3.75Z" stroke="#344054" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
`,
    permission: 'audit log view'
  },
  {
    name: "Recycle Bin",
    path: "/recycle-bin",
    icon: `<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M2.5 5.00008H4.16667M4.16667 5.00008H17.5M4.16667 5.00008V16.6667C4.16667 17.1088 4.34226 17.5327 4.65482 17.8453C4.96738 18.1578 5.39131 18.3334 5.83333 18.3334H14.1667C14.6087 18.3334 15.0326 18.1578 15.3452 17.8453C15.6577 17.5327 15.8333 17.1088 15.8333 16.6667V5.00008H4.16667ZM6.66667 5.00008V3.33341C6.66667 2.89139 6.84226 2.46746 7.15482 2.1549C7.46738 1.84234 7.89131 1.66675 8.33333 1.66675H11.6667C12.1087 1.66675 12.5326 1.84234 12.8452 2.1549C13.1577 2.46746 13.3333 2.89139 13.3333 3.33341V5.00008M8.33333 9.16675V14.1667M11.6667 9.16675V14.1667" stroke="#344054" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
`,
    permission: 'recycle bin view'
  },
]);



// const branches = ref([
//     {
//         name: "Communication",
//         path: "/branches/communication",
//         icon: "clarity:circle-line",
//     },
//     {
//         name: "Tech Titans",
//         path: "/branches/tech-titans",
//         icon: "clarity:circle-line",
//     },
//     {
//         name: "Information",
//         path: "/branches/information",
//         icon: "clarity:circle-line",
//     },
//     {
//         name: "Management",
//         path: "/branches/management",
//         icon: "clarity:circle-line",
//     },
// ]);



const getBranchesByRole = () => {
  axios.get('/dashboard/get-branches-by-role').then((response) => {
    roleBranches.value = response.data
  })
}

watch(current_page,
  (newPath) => {
    console.log(newPath)
    shouldShow.value =
      newPath.startsWith("/settings") || newPath.startsWith("/dashboard/chat") || newPath.startsWith("/dashboard/workflow");
  }
);

onMounted(() => {
  setDepartments()
  shouldShow.value =
    current_page.value.startsWith("/settings") || current_page.value.startsWith("/dashboard/chat") || current_page.value.startsWith("/dashboard/workflow");

  if (role.value.includes('super admin') || role.value.includes('admin') || role.value.includes('super users')
    || role.value.includes('directors') || role.value.includes('division heads') ||
    role.value.includes('unit heads')) {
    getBranchesByRole()
    isBranches.value = true;
  }
});
</script>

<style>
/* Hide scrollbar for Chrome, Safari and Opera */
.hide_scroll::-webkit-scrollbar {
  display: none;
}

/* Hide scrollbar for IE, Edge and Firefox */
.hide_scroll {
  -ms-overflow-style: none;
  /* IE and Edge */
  scrollbar-width: none;
  /* Firefox */
}
</style>