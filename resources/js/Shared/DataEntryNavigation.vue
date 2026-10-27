<template>
    <!-- <section class="hidden lg:block w-1/5 border-r border-solid border-gray-200 sticky top-0  px-5 py-5"> -->
    <section class="hide_scroll hidden 2xl:block w-1/5 border-r border-solid border-gray-200 sticky top-0 h-screen px-5 py-5 overflow-y-scroll">
        <div class="text-xl font-semibold flex items-center gap-4 border-b border-dashed border-gray-200 pb-3 ">
            <!-- <logo /> -->
            <img src="../../../public/images/default-logo.png" alt="">
            <!-- <p>Ansel LTD</p> -->
        </div>
        <div class="border-b border-dashed pb-4">
            <p class="text-gray-500 pl-4 mt-4 mb-2">Main</p>
            <section>
                <div v-for="(item, index) in main" :key="index" class="mb-1">
                    <Link :href="item.path"
                        class="px-4 py-3 flex gap-3 items-center text-gray-700 border-solid border-gray-200 rounded-xl hover:bg-gray-25 hover:text-primary font-normal transition-all">
                    <!-- <Icon :icon="item.icon" width="25" /> -->
                    <span v-html="item.icon"</span>
                    <p class="text-lg">{{ item.name }}</p>
                    </Link>
                </div>
            </section>
        </div>
        
        <div class="pb-4 mb-6">
            <div class="text-gray-500 pl-4 mt-4 mb-2 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <!-- <Icon icon="ic:round-greater-than" width="20" /> -->
                    <p>Branches</p>
                </div>
                <Icon icon="line-md:grid-3-filled" width="20" />
            </div>
            <section>
                <div  class="mb-1">
                    <Link :href="'/edms/branches'"
                        class="pl-4 pr-1 py-3 flex items-center justify-between text-gray-700 border-solid border-gray-200 rounded-xl hover:bg-gray-25 hover:text-primary font-normal transition-all">
                    <div class="flex items-center gap-3">
                        <Icon :icon="branch.icon" width="15" />
                        <p class="text-lg">{{ (userBranch?.unit )? userBranch.unit?.title+' Unit': (userBranch?.division )? userBranch.division?.title+' Division': userBranch.department?.title+' Department' }}</p>
                    </div>
                    <!-- <Icon icon="ic:round-greater-than" width="20" /> -->
                    </Link>
                </div>
                
            </section>
        </div>
        <div class="border-t border-dashed pt-2 pb-4">
            <section>
                <div class="mb-1">
                    <Link href="/edms/support"
                        class="px-4 py-3 flex gap-3 items-center text-gray-700 border-0  border-solid border-gray-200 rounded-xl hover:bg-gray-25 hover:text-primary font-normal transition-all">
                    <!-- <Icon icon="system-uicons:support" width="30" /> -->
                    <svg width="25" height="25" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_4006_44928)">
                    <path d="M4.10817 4.10829L7.6415 7.64163M12.3582 12.3583L15.8915 15.8916M15.8915 4.10829L12.3582 7.64163L15.2998 4.69996M4.10817 15.8916L7.6415 12.3583M18.3332 9.99996C18.3332 14.6023 14.6022 18.3333 9.99984 18.3333C5.39746 18.3333 1.6665 14.6023 1.6665 9.99996C1.6665 5.39759 5.39746 1.66663 9.99984 1.66663C14.6022 1.66663 18.3332 5.39759 18.3332 9.99996ZM13.3332 9.99996C13.3332 11.8409 11.8408 13.3333 9.99984 13.3333C8.15889 13.3333 6.6665 11.8409 6.6665 9.99996C6.6665 8.15901 8.15889 6.66663 9.99984 6.66663C11.8408 6.66663 13.3332 8.15901 13.3332 9.99996Z" stroke="#48505E" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
                    </g>
                    <defs>
                    <clipPath id="clip0_4006_44928">
                    <rect width="20" height="20" fill="white"/>
                    </clipPath>
                    </defs>
                    </svg>

                    <p class="text-lg">Support</p>
                    </Link>
                </div>
                <div class="mb-1">
                    <Link href="/edms/settings"
                        class="px-4 py-3 flex gap-3 items-center text-gray-700 border-0  border-solid border-gray-200 rounded-xl hover:bg-gray-25 hover:text-primary font-normal transition-all">
                    <!-- <Icon icon="uiw:setting-o" width="25" /> -->
                    <svg width="25" height="25" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_4006_44933)">
                        <path d="M10.0002 12.5C11.3809 12.5 12.5002 11.3808 12.5002 10C12.5002 8.61933 11.3809 7.50004 10.0002 7.50004C8.61945 7.50004 7.50016 8.61933 7.50016 10C7.50016 11.3808 8.61945 12.5 10.0002 12.5Z" stroke="#48505E" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M16.1668 12.5C16.0559 12.7514 16.0228 13.0302 16.0718 13.3005C16.1208 13.5709 16.2497 13.8203 16.4418 14.0167L16.4918 14.0667C16.6468 14.2215 16.7697 14.4053 16.8536 14.6076C16.9375 14.81 16.9806 15.0268 16.9806 15.2459C16.9806 15.4649 16.9375 15.6818 16.8536 15.8841C16.7697 16.0864 16.6468 16.2702 16.4918 16.425C16.337 16.58 16.1532 16.7029 15.9509 16.7868C15.7486 16.8707 15.5317 16.9139 15.3127 16.9139C15.0936 16.9139 14.8768 16.8707 14.6744 16.7868C14.4721 16.7029 14.2883 16.58 14.1335 16.425L14.0835 16.375C13.8871 16.1829 13.6376 16.0541 13.3673 16.005C13.097 15.956 12.8182 15.9891 12.5668 16.1C12.3204 16.2057 12.1101 16.3811 11.9621 16.6047C11.814 16.8282 11.7346 17.0902 11.7335 17.3584V17.5C11.7335 17.9421 11.5579 18.366 11.2453 18.6785C10.9328 18.9911 10.5089 19.1667 10.0668 19.1667C9.6248 19.1667 9.20088 18.9911 8.88832 18.6785C8.57576 18.366 8.40016 17.9421 8.40016 17.5V17.425C8.39371 17.1492 8.30443 16.8817 8.14392 16.6573C7.98341 16.4329 7.75911 16.2619 7.50016 16.1667C7.24882 16.0558 6.97 16.0227 6.69967 16.0717C6.42934 16.1207 6.17989 16.2496 5.9835 16.4417L5.9335 16.4917C5.77871 16.6467 5.59489 16.7696 5.39256 16.8535C5.19023 16.9373 4.97335 16.9805 4.75433 16.9805C4.5353 16.9805 4.31843 16.9373 4.1161 16.8535C3.91377 16.7696 3.72995 16.6467 3.57516 16.4917C3.4202 16.3369 3.29727 16.1531 3.2134 15.9508C3.12952 15.7484 3.08635 15.5316 3.08635 15.3125C3.08635 15.0935 3.12952 14.8766 3.2134 14.6743C3.29727 14.472 3.4202 14.2882 3.57516 14.1334L3.62516 14.0834C3.81728 13.887 3.94615 13.6375 3.99517 13.3672C4.04418 13.0969 4.01109 12.8181 3.90016 12.5667C3.79453 12.3202 3.61913 12.11 3.39555 11.962C3.17198 11.8139 2.90998 11.7344 2.64183 11.7334H2.50016C2.05814 11.7334 1.63421 11.5578 1.32165 11.2452C1.00909 10.9327 0.833496 10.5087 0.833496 10.0667C0.833496 9.62468 1.00909 9.20076 1.32165 8.8882C1.63421 8.57563 2.05814 8.40004 2.50016 8.40004H2.57516C2.85099 8.39359 3.1185 8.30431 3.34291 8.1438C3.56732 7.98329 3.73826 7.75899 3.8335 7.50004C3.94443 7.24869 3.97752 6.96988 3.9285 6.69955C3.87948 6.42922 3.75061 6.17977 3.5585 5.98337L3.5085 5.93337C3.35354 5.77858 3.2306 5.59477 3.14673 5.39244C3.06286 5.19011 3.01968 4.97323 3.01968 4.75421C3.01968 4.53518 3.06286 4.3183 3.14673 4.11597C3.2306 3.91364 3.35354 3.72983 3.5085 3.57504C3.66328 3.42008 3.8471 3.29715 4.04943 3.21327C4.25176 3.1294 4.46864 3.08623 4.68766 3.08623C4.90669 3.08623 5.12357 3.1294 5.3259 3.21327C5.52823 3.29715 5.71204 3.42008 5.86683 3.57504L5.91683 3.62504C6.11323 3.81715 6.36268 3.94603 6.633 3.99504C6.90333 4.04406 7.18215 4.01097 7.4335 3.90004H7.50016C7.74664 3.7944 7.95684 3.619 8.10491 3.39543C8.25297 3.17185 8.33243 2.90986 8.3335 2.64171V2.50004C8.3335 2.05801 8.50909 1.63409 8.82165 1.32153C9.13421 1.00897 9.55813 0.833374 10.0002 0.833374C10.4422 0.833374 10.8661 1.00897 11.1787 1.32153C11.4912 1.63409 11.6668 2.05801 11.6668 2.50004V2.57504C11.6679 2.8432 11.7474 3.10519 11.8954 3.32876C12.0435 3.55234 12.2537 3.72774 12.5002 3.83337C12.7515 3.9443 13.0303 3.97739 13.3007 3.92838C13.571 3.87936 13.8204 3.75049 14.0168 3.55837L14.0668 3.50837C14.2216 3.35341 14.4054 3.23048 14.6078 3.14661C14.8101 3.06273 15.027 3.01956 15.246 3.01956C15.465 3.01956 15.6819 3.06273 15.8842 3.14661C16.0866 3.23048 16.2704 3.35341 16.4252 3.50837C16.5801 3.66316 16.7031 3.84698 16.7869 4.04931C16.8708 4.25164 16.914 4.46851 16.914 4.68754C16.914 4.90657 16.8708 5.12344 16.7869 5.32577C16.7031 5.5281 16.5801 5.71192 16.4252 5.86671L16.3752 5.91671C16.183 6.11311 16.0542 6.36255 16.0052 6.63288C15.9561 6.90321 15.9892 7.18203 16.1002 7.43337V7.50004C16.2058 7.74651 16.3812 7.95672 16.6048 8.10478C16.8283 8.25285 17.0903 8.3323 17.3585 8.33337H17.5002C17.9422 8.33337 18.3661 8.50897 18.6787 8.82153C18.9912 9.13409 19.1668 9.55801 19.1668 10C19.1668 10.4421 18.9912 10.866 18.6787 11.1786C18.3661 11.4911 17.9422 11.6667 17.5002 11.6667H17.4252C17.157 11.6678 16.895 11.7472 16.6714 11.8953C16.4479 12.0434 16.2725 12.2536 16.1668 12.5Z" stroke="#48505E" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
                        </g>
                        <defs>
                        <clipPath id="clip0_4006_44933">
                        <rect width="20" height="20" fill="white"/>
                        </clipPath>
                        </defs>
                        </svg>

                    <p class="text-lg">Settings</p>
                    </Link>
                </div>
            </section>
        </div>
    </section>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import logo from "../assets/logo.vue";
import { Link } from '@inertiajs/vue3'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const branch = ref('');
//const departments = computed(() => page.props.shared_departments)
const department = computed(() => page.props.department)
// const unit = computed(()=> page.props.unit);
// const division = computed(()=> page.props.division);
const userBranch = computed(()=> page.props.userBranch);

const branches = ref([])

const setDepartments = () => {
    // for (let dept of departments.value) {
    //     let data = {
    //         name: dept.title,
    //         path: "/branches/" + dept.id,
    //         icon: "clarity:circle-line",
    //     }
    //     branches.value.push(data)
    // }
    let data = {
        name: userBranch.value.department.title,
        path: "/edms/branches/"+userBranch.value.department.id,
        icon: "clarity:circle-line"
    }
    branches.value.push(data)
}

const main = ref([
{
      name: "Dashboard",
      path: "/edms",
      //icon: "lucide:home",
      icon: `<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M7.5 18.3334V10H12.5V18.3334M2.5 7.50002L10 1.66669L17.5 7.50002V16.6667C17.5 17.1087 17.3244 17.5326 17.0118 17.8452C16.6993 18.1578 16.2754 18.3334 15.8333 18.3334H4.16667C3.72464 18.3334 3.30072 18.1578 2.98816 17.8452C2.67559 17.5326 2.5 17.1087 2.5 16.6667V7.50002Z" stroke="#344054" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        `,
      icon_active: '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.5 18.3334V10.0001H12.5V18.3334M2.5 7.50008L10 1.66675L17.5 7.50008V16.6667C17.5 17.1088 17.3244 17.5327 17.0118 17.8453C16.6993 18.1578 16.2754 18.3334 15.8333 18.3334H4.16667C3.72464 18.3334 3.30072 18.1578 2.98816 17.8453C2.67559 17.5327 2.5 17.1088 2.5 16.6667V7.50008Z" stroke="#0D6E1A" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/></svg>'
    },
    {
      name: "Branches",
      path: "/edms/branch-overview",
      //icon: "material-symbols:database-outline"
      icon: `<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M17.5 4.16675C17.5 5.54746 14.1421 6.66675 10 6.66675C5.85786 6.66675 2.5 5.54746 2.5 4.16675M17.5 4.16675C17.5 2.78604 14.1421 1.66675 10 1.66675C5.85786 1.66675 2.5 2.78604 2.5 4.16675M17.5 4.16675V15.8334C17.5 17.2167 14.1667 18.3334 10 18.3334C5.83333 18.3334 2.5 17.2167 2.5 15.8334V4.16675M17.5 10.0001C17.5 11.3834 14.1667 12.5001 10 12.5001C5.83333 12.5001 2.5 11.3834 2.5 10.0001" stroke="#344054" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        `,
      icon_active: `<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M17.5 4.16663C17.5 5.54734 14.1421 6.66663 10 6.66663C5.85786 6.66663 2.5 5.54734 2.5 4.16663M17.5 4.16663C17.5 2.78591 14.1421 1.66663 10 1.66663C5.85786 1.66663 2.5 2.78591 2.5 4.16663M17.5 4.16663V15.8333C17.5 17.2166 14.1667 18.3333 10 18.3333C5.83333 18.3333 2.5 17.2166 2.5 15.8333V4.16663M17.5 9.99996C17.5 11.3833 14.1667 12.5 10 12.5C5.83333 12.5 2.5 11.3833 2.5 9.99996" stroke="#0D6E1A" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        `
    }
]);




onMounted(() => {
    setDepartments()
    //branch.value = getBranchName()
})
</script>
