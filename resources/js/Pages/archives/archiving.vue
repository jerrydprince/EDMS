<style>
    .upload-document-txt{
        min-height: 100px;
    }
    /* .select-document-div{
        height: 130px;
    } */
</style>

<template>
    <div class="divide-y divide-slate-200">
        <!-- first div -->
        <div class="flex items-center justify-center upload-document-txt">
            <div class="grid xl:grid-cols-2 lg:grid-cols-2 md:grid-cols-1 sm:grid-cols-1 gap-4 w-full items-center md:py-4 py-4">
                <div class=" flex items-center">
                  
                    <div>
                        <Link :href="'/dashboard/folder-view/'+props.branch_type+'/'+folderId+'/'+props.branches.id" v-if="page.props.roles.includes('super admin') || page.props.roles.includes('admin') || page.props.roles.includes('super user')
                        || page.props.roles.includes('director') ||page.props.roles.includes('deputy director') || page.props.roles.includes('assistant director') ">
                            <svg class="border ml-[35px] mr-5 border-gray-300 p-2 w-10 h-10 rounded-lg" width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.079 10.9989H4.92105M4.92105 10.9989L11 17.0778M4.92105 10.9989L11 4.91992" stroke="#363C46" stroke-width="1.73684" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </Link>
                        <Link :href="'/edms/folder-view/'+props.branch_type+'/'+folderId" v-else>
                            <svg class="border ml-[35px] mr-5 border-gray-300 p-2 w-10 h-10 rounded-lg" width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.079 10.9989H4.92105M4.92105 10.9989L11 17.0778M4.92105 10.9989L11 4.91992" stroke="#363C46" stroke-width="1.73684" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </Link>
                    </div>
                  
                    <div>
                        <h2 class=" text-2xl font-semibold">Upload Documents</h2>
                        <h4 class="text-gray-400 text-sm mt-1">Archive files into directories</h4>
                    </div>
                </div>
                <div class="lg:text-end md:text-center sm:text-center pl-6">
                    <button class=" mr-6 text-green-700" @click="cancelForm">Cancel</button>
                    <button class=" bg-primary text-white px-[64px] py-2 rounded-lg mr-[54px] hover:bg-primary_hover disabled:bg-primary_disabled" :disabled="files.length == 0 || selected_metadata_strings.length == 0" @click="submitForm">Upload</button>
                </div>
            </div>
        </div>
        <!-- second div -->
        <div class="flex items-center justify-center upload-document-txt py-4">
            <div class="grid lg:grid-cols-2 md:grid-cols-2 sm:grid-cols-1 gap-4 w-full items-center">
                <div class=" flex">
                    <div><svg width="19" class="w-[50px] border border-gray-200 rounded-full h-[50px] p-3 ml-[35px] mr-5" height="20" viewBox="0 0 19 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M10.9532 2.1875H4.70316C4.28875 2.1875 3.89133 2.35212 3.5983 2.64515C3.30528 2.93817 3.14066 3.3356 3.14066 3.75V16.25C3.14066 16.6644 3.30528 17.0618 3.5983 17.3549C3.89133 17.6479 4.28875 17.8125 4.70316 17.8125H14.0782C14.4926 17.8125 14.89 17.6479 15.183 17.3549C15.476 17.0618 15.6407 16.6644 15.6407 16.25V6.875M10.9532 2.1875L15.6407 6.875M10.9532 2.1875V6.875H15.6407M12.5157 10.7813H6.26566M12.5157 13.9063H6.26566M7.82816 7.65625H6.26566" stroke="#344054" stroke-width="1.56563" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg></div>
                    <div>
                        <h3 class=" text-xl font-semibold">Select document type</h3>
                        <h4 class="text-gray-600 text-sm mt-1">Choose the specific category for the document</h4>
                    </div>
                </div>
                <div class=" lg:text-end md:text-center sm:text-left pl-6 mr-14">
                    <select name="" id="" class="border border-gray-300 px-4 py-2 rounded-lg w-2/3 text-gray-500 bg-white" v-model="selectedDocType">
                        <option value="">Select document type</option>
                        <option :value="doctype.id" v-for="(doctype, index) in props.doctypes" :key="index">{{ doctype.name }}</option>
                    </select>
                </div>
            </div>
        </div>
        <!-- seventh div -->
        <!-- <div class="flex items-center justify-center upload-document-txt ">
            <div class="grid lg:grid-cols-2 md:grid-cols-1 sm:grid-cols-1 gap-4 w-full items-center">
                <div class="flex">
                    <div>
                        <svg class="w-[50px] border border-gray-200 rounded-full h-[50px] p-3 ml-[35px] mr-5" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M16.5 14.25C16.5 14.6478 16.342 15.0294 16.0607 15.3107C15.7794 15.592 15.3978 15.75 15 15.75H3C2.60218 15.75 2.22064 15.592 1.93934 15.3107C1.65804 15.0294 1.5 14.6478 1.5 14.25V3.75C1.5 3.35218 1.65804 2.97064 1.93934 2.68934C2.22064 2.40804 2.60218 2.25 3 2.25H6.75L8.25 4.5H15C15.3978 4.5 15.7794 4.65804 16.0607 4.93934C16.342 5.22064 16.5 5.60218 16.5 6V14.25Z" stroke="#344054" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>

                    </div>
                    <div>
                        <h3 class=" text-xl font-semibold">Choose Folder Location</h3>
                        <h4 class="text-gray-600 text-sm mt-1">Fill details specific to the selected document type.</h4>
                    </div>
                </div>
                <div class="lg:text-end pr-14">
                    <div
                        class=" lg:flex text-xl"
                    >
                        <span class="text-gray-400 relative w-full">
                        <Icon
                            icon="iconamoon:search-light"
                            width="20"
                            class="pi pi-search absolute top-2/4 -mt-[10.2px] left-3 text-surface-400 dark:text-surface-600"
                        />
                        <InputText
                            v-model="search"
                            placeholder="Search for documents, folders & branches"
                            class="pl-10 w-full h-10 bg-transparent font-light"
                            size="small"
                        />
                        </span>
                    </div>
                </div>
            </div>
        </div> -->
        <!-- eight div -->
        <!-- <div class=" mb-6">
            <div class="mt-6 mr-[105px] text-end">
                <button class=" px-6 py-1 border border-gray-200 rounded-3xl hover:bg-gray-100" @click="createFolder=true">
                    <span class="flex text-sm">
                        <svg class=" h-4 mr-1 items-center align-middle justify-center mt-1" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 11V17M9 14H15M22 19C22 19.5304 21.7893 20.0391 21.4142 20.4142C21.0391 20.7893 20.5304 21 20 21H4C3.46957 21 2.96086 20.7893 2.58579 20.4142C2.21071 20.0391 2 19.5304 2 19V5C2 4.46957 2.21071 3.96086 2.58579 3.58579C2.96086 3.21071 3.46957 3 4 3H9L11 6H20C20.5304 6 21.0391 6.21071 21.4142 6.58579C21.7893 6.96086 22 7.46957 22 8V19Z" stroke="#344054" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>

                    New Folder
                    </span>
                </button>
            </div>
            <div class=" w-5/6 m-auto mt-4">
                <div class="mr-8 grid grid-cols-12 gap-12">
                    <div v-for="(folder, index) in props.folders" :key="index" class=" col-span-4 border border-gray-100 bg-gray-100 p-2 rounded-lg cursor-pointer flex">
                        <div class="card flex justify-content-center items-center">
                            <Checkbox v-model="selectedFolder" @change="getFolderId(folder)"  :value="folder.name" name="folder" />
                        </div>
                        <svg class=" ml-4" width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="0.25" y="0.828125" width="47.5" height="46.9214" rx="5.53311" fill="white"/>
                        <rect x="0.25" y="0.828125" width="47.5" height="46.9214" rx="5.53311" stroke="#EAECF0" stroke-width="0.5"/>
                        <g filter="url(#filter0_i_653_12638)">
                        <path d="M6.65063 23.8383V17.4791C6.79774 15.8943 7.8422 12.7246 10.8432 12.7246H18.8421C19.5041 12.7642 21.0597 13.0693 21.9865 13.9727C23.145 15.1019 26.3997 14.8047 27.3927 14.8641C28.3636 14.8166 34.086 14.8443 36.8259 14.8641C38.1499 14.8047 40.9081 15.6486 41.3494 19.4998V29.9597H6.65063V23.8383Z" fill="#858585"/>
                        </g>
                        <g filter="url(#filter1_dd_653_12638)">
                        <rect width="13.1349" height="13.6586" rx="2.19612" transform="matrix(0.998042 -0.0625513 0.0631662 0.998003 9.94476 17.8008)" fill="white"/>
                        <rect width="7.35702" height="0.768639" rx="0.38432" transform="matrix(0.998042 -0.0625513 0.0631662 0.998003 12.0214 19.3203)" fill="#F4F4F4"/>
                        <rect width="4.72167" height="0.768639" rx="0.38432" transform="matrix(0.998042 -0.0625513 0.0631662 0.998003 12.1046 20.6348)" fill="#F4F4F4"/>
                        <rect width="6.58838" height="0.768639" rx="0.38432" transform="matrix(0.998042 -0.0625513 0.0631662 0.998003 12.1879 21.9512)" fill="#F4F4F4"/>
                        <rect width="6.58838" height="0.768639" rx="0.38432" transform="matrix(0.998042 -0.0625513 0.0631662 0.998003 12.2711 23.2656)" fill="#F4F4F4"/>
                        <rect width="6.58838" height="0.768639" rx="0.38432" transform="matrix(0.998042 -0.0625513 0.0631662 0.998003 12.3543 24.5801)" fill="#F4F4F4"/>
                        </g>
                        <g filter="url(#filter2_dd_653_12638)">
                        <rect width="13.135" height="13.6586" rx="2.19612" transform="matrix(0.998634 0.0522452 -0.0524268 0.998625 18.0626 17.8066)" fill="white"/>
                        <rect width="7.35702" height="0.768639" rx="0.38432" transform="matrix(0.998634 0.0522452 -0.0524268 0.998625 19.95 19.5566)" fill="#F4F4F4"/>
                        </g>
                        <g filter="url(#filter3_dd_653_12638)">
                        <rect x="24.8104" y="17.2656" width="13.135" height="13.6586" rx="2.19612" fill="white"/>
                        <rect x="26.7868" y="18.9141" width="7.35702" height="0.768639" rx="0.38432" fill="#F4F4F4"/>
                        <rect x="26.7868" y="20.2324" width="2.08632" height="0.768639" rx="0.38432" fill="#F4F4F4"/>
                        <rect x="26.7868" y="21.5488" width="4.72167" height="0.768639" rx="0.38432" fill="#F4F4F4"/>
                        <rect x="26.7868" y="22.8672" width="6.58838" height="0.768639" rx="0.38432" fill="#F4F4F4"/>
                        <rect x="26.7868" y="24.1855" width="3.73341" height="0.768639" rx="0.38432" fill="#F4F4F4"/>
                        <rect x="26.7868" y="25.502" width="2.08632" height="0.768639" rx="0.38432" fill="#F4F4F4"/>
                        </g>
                        <g filter="url(#filter4_i_653_12638)">
                        <path d="M6.65063 23.7852H41.3494V31.5213C41.3494 33.9471 39.383 35.9136 36.9572 35.9136H11.0429C8.61711 35.9136 6.65063 33.9471 6.65063 31.5213V23.7852Z" fill="#595959"/>
                        </g>
                        <rect x="6.65063" y="20.709" width="34.6988" height="3.51378" fill="url(#paint0_linear_653_12638)" fill-opacity="0.5"/>
                        <defs>
                        <filter id="filter0_i_653_12638" x="6.65063" y="12.7246" width="34.6988" height="18.1919" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                        <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                        <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>
                        <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
                        <feOffset dy="0.957508"/>
                        <feGaussianBlur stdDeviation="0.638339"/>
                        <feComposite in2="hardAlpha" operator="arithmetic" k2="-1" k3="1"/>
                        <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0"/>
                        <feBlend mode="normal" in2="shape" result="effect1_innerShadow_653_12638"/>
                        </filter>
                        <filter id="filter1_dd_653_12638" x="8.62709" y="16.9785" width="16.6073" height="17.0885" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                        <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                        <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
                        <feMorphology radius="0.219612" operator="erode" in="SourceAlpha" result="effect1_dropShadow_653_12638"/>
                        <feOffset dy="0.439224"/>
                        <feGaussianBlur stdDeviation="0.329418"/>
                        <feColorMatrix type="matrix" values="0 0 0 0 0.0627451 0 0 0 0 0.0941176 0 0 0 0 0.156863 0 0 0 0.03 0"/>
                        <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_653_12638"/>
                        <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
                        <feMorphology radius="0.439224" operator="erode" in="SourceAlpha" result="effect2_dropShadow_653_12638"/>
                        <feOffset dy="1.31767"/>
                        <feGaussianBlur stdDeviation="0.878448"/>
                        <feColorMatrix type="matrix" values="0 0 0 0 0.0627451 0 0 0 0 0.0941176 0 0 0 0 0.156863 0 0 0 0.08 0"/>
                        <feBlend mode="normal" in2="effect1_dropShadow_653_12638" result="effect2_dropShadow_653_12638"/>
                        <feBlend mode="normal" in="SourceGraphic" in2="effect2_dropShadow_653_12638" result="shape"/>
                        </filter>
                        <filter id="filter2_dd_653_12638" x="15.1504" y="17.8066" width="18.2253" height="18.7184" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                        <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                        <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
                        <feMorphology radius="0.439224" operator="erode" in="SourceAlpha" result="effect1_dropShadow_653_12638"/>
                        <feOffset dy="0.878448"/>
                        <feGaussianBlur stdDeviation="0.439224"/>
                        <feColorMatrix type="matrix" values="0 0 0 0 0.0627451 0 0 0 0 0.0941176 0 0 0 0 0.156863 0 0 0 0.03 0"/>
                        <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_653_12638"/>
                        <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
                        <feMorphology radius="0.439224" operator="erode" in="SourceAlpha" result="effect2_dropShadow_653_12638"/>
                        <feOffset dy="2.19612"/>
                        <feGaussianBlur stdDeviation="1.31767"/>
                        <feColorMatrix type="matrix" values="0 0 0 0 0.0627451 0 0 0 0 0.0941176 0 0 0 0 0.156863 0 0 0 0.08 0"/>
                        <feBlend mode="normal" in2="effect1_dropShadow_653_12638" result="effect2_dropShadow_653_12638"/>
                        <feBlend mode="normal" in="SourceGraphic" in2="effect2_dropShadow_653_12638" result="shape"/>
                        </filter>
                        <filter id="filter3_dd_653_12638" x="22.6142" y="17.2656" width="17.5273" height="18.0504" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                        <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                        <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
                        <feMorphology radius="0.439224" operator="erode" in="SourceAlpha" result="effect1_dropShadow_653_12638"/>
                        <feOffset dy="0.878448"/>
                        <feGaussianBlur stdDeviation="0.439224"/>
                        <feColorMatrix type="matrix" values="0 0 0 0 0.0627451 0 0 0 0 0.0941176 0 0 0 0 0.156863 0 0 0 0.03 0"/>
                        <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_653_12638"/>
                        <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
                        <feMorphology radius="0.439224" operator="erode" in="SourceAlpha" result="effect2_dropShadow_653_12638"/>
                        <feOffset dy="2.19612"/>
                        <feGaussianBlur stdDeviation="1.31767"/>
                        <feColorMatrix type="matrix" values="0 0 0 0 0.0627451 0 0 0 0 0.0941176 0 0 0 0 0.156863 0 0 0 0.08 0"/>
                        <feBlend mode="normal" in2="effect1_dropShadow_653_12638" result="effect2_dropShadow_653_12638"/>
                        <feBlend mode="normal" in="SourceGraphic" in2="effect2_dropShadow_653_12638" result="shape"/>
                        </filter>
                        <filter id="filter4_i_653_12638" x="6.65063" y="22.5085" width="35.3371" height="13.4056" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                        <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                        <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>
                        <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
                        <feOffset dx="0.638339" dy="-1.27668"/>
                        <feGaussianBlur stdDeviation="0.957508"/>
                        <feComposite in2="hardAlpha" operator="arithmetic" k2="-1" k3="1"/>
                        <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0"/>
                        <feBlend mode="normal" in2="shape" result="effect1_innerShadow_653_12638"/>
                        </filter>
                        <linearGradient id="paint0_linear_653_12638" x1="24" y1="21.4277" x2="24" y2="27.9761" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#595959" stop-opacity="0"/>
                        <stop offset="1" stop-color="#595959"/>
                        </linearGradient>
                        </defs>
                        </svg>
                        <div class="ml-4">
                            <h4 class=" text-lg ">{{folder.name}}</h4>
                            <p class="text-sm">{{ (folder?.file_count)? folder.file_count: 0 }} File(s)</p>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div> -->
        <!-- third div -->
        <div class="flex items-center justify-center upload-document-txt">
            <div class="grid grid-cols-12 gap-4 w-full items-center">
                <div class="col-span-6 flex">
                    <div>
                        <svg class="w-[50px] border border-gray-200 rounded-full h-[50px] p-3 ml-[35px] mr-5" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_653_12633)">
                            <path d="M12 12.0001L8.99997 9.00011M8.99997 9.00011L5.99997 12.0001M8.99997 9.00011V15.7501M15.2925 13.7926C16.024 13.3938 16.6018 12.7628 16.9349 11.9991C17.2679 11.2354 17.3371 10.3825 17.1316 9.57513C16.9261 8.76772 16.4576 8.05174 15.8 7.54019C15.1424 7.02865 14.3331 6.75066 13.5 6.75011H12.555C12.328 5.87204 11.9048 5.05686 11.3174 4.36585C10.73 3.67485 9.99361 3.126 9.16355 2.76057C8.33349 2.39513 7.43138 2.22263 6.52506 2.25602C5.61873 2.28941 4.73177 2.52784 3.93085 2.95336C3.12994 3.37888 2.4359 3.98044 1.90094 4.7128C1.36597 5.44516 1.00399 6.28926 0.842205 7.18166C0.680422 8.07405 0.723049 8.99151 0.96688 9.86506C1.21071 10.7386 1.6494 11.5455 2.24997 12.2251" stroke="#344054" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </g>
                            <defs>
                            <clipPath id="clip0_653_12633">
                            <rect width="18" height="18" fill="white"/>
                            </clipPath>
                            </defs>
                        </svg>

                    </div>
                    <div>
                        <h3 class=" text-xl font-semibold">Upload document</h3>
                        <h4 class="text-gray-600 text-sm mt-1">Select the document from your device to upload</h4>
                    </div>
                </div>
            </div>
        </div>
        <!-- fourth div -->
<div v-if="files.length === 0" class="w-5/6 mx-auto mt-9 grid grid-cols-1 gap-8 items-start mb-6">
    <div class="flex flex-col gap-4 w-full max-w-3xl mx-auto">
        <div class="drop-area w-full" @dragover.prevent @drop="handleDrop">
            <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-48 border-2 border-gray-300 border-dashed rounded-lg bg-gray-50 dark:hover:bg-bray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600" :class="selectedFolder.length == 0?'cursor-not-allowed':'cursor-pointer'">
                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                    <svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                    </svg>
                    <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">SVG, PNG, JPG, GIF, DOC, XLX, PDF</p>
                </div>
                <input id="dropzone-file" :disabled="selectedFolder.length == 0" type="file" class="hidden" multiple @change="handleFileUpload" />
            </label>
        </div> 
        
        <div class="flex items-center justify-center w-full mb-4">
            <button type="button" @click="isScanning ? cancelScan() : scanDocument()" :disabled="selectedFolder.length == 0 && !isScanning" class="flex gap-2 items-center bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 py-2 px-6 rounded-lg transition-colors shadow-sm w-full justify-center" :class="[selectedFolder.length == 0 && !isScanning ? 'cursor-not-allowed opacity-50' : '', isScanning ? 'text-red-600 border-red-300 hover:bg-red-50' : '']">
                <Icon :icon="isScanning ? 'mdi:stop-circle-outline' : 'mdi:scanner'" width="20" />
                <span class="font-medium">{{ isScanning ? 'Cancel Scan' : 'Scan Document from Device' }}</span>
                <svg v-if="isScanning" class="animate-spin -ml-1 mr-3 h-5 w-5 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </button>
        </div>
    </div>
</div>

<div v-if="files.length > 0" class="w-[98%] mx-auto mt-6 grid grid-cols-12 gap-4 items-start h-[800px] mb-6">
    <div class="col-span-3 flex flex-col gap-2 h-full overflow-y-auto pr-2 w-full border-r border-gray-200">
        <h3 class="font-semibold text-gray-700 mb-2 sticky top-0 bg-white z-10 py-1 flex justify-between items-center">
            <span>Staged Documents ({{ files.length }})</span>
            <div class="flex items-center gap-3">
                <button type="button" @click="isScanning ? cancelScan() : scanDocument()" :class="isScanning ? 'text-red-500 hover:text-red-700' : 'text-blue-600 hover:text-blue-800'" class="text-xs font-medium flex items-center gap-1 cursor-pointer transition-colors">
                    <svg v-if="isScanning" class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <Icon v-else icon="mdi:scanner" class="w-3 h-3" />
                    <span>{{ isScanning ? 'Stop' : '+ Scan' }}</span>
                </button>
                <label for="add-more-files" class="text-xs text-blue-600 hover:text-blue-800 cursor-pointer font-medium">+ Add</label>
                <input id="add-more-files" type="file" class="hidden" multiple @change="handleFileUpload" />
            </div>
        </h3>
        
        <div class="border p-2 rounded-lg relative shadow-sm hover:shadow-md transition-all cursor-pointer" :class="activeFileIndex === index ? 'border-blue-500 bg-blue-50' : 'border-gray-200 bg-white'" @click="selectFile(index)" v-for="(file, index) in files" :key="index">
            <div class="mb-1 flex justify-between items-center">
                <div class="flex items-center gap-x-3 overflow-hidden">
                    <span @click.stop="openPreview(file.rawFile)" class="w-10 h-10 flex-shrink-0 flex justify-center items-center text-gray-500 bg-gray-50 rounded border border-gray-100 overflow-hidden cursor-pointer hover:bg-gray-200 transition-colors" title="Click to expand">
                        <img v-if="getFileURL(file.rawFile).startsWith('blob:') || getFileURL(file.rawFile).includes('images/')" :src="getFileURL(file.rawFile)" class="w-full h-full object-cover" alt="File Preview" />
                        <svg v-else width="42" height="43" viewBox="0 0 42 43" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M31.6313 3.2207L38.934 10.8332V39.7817H11.6537V39.8762H39.0272V10.929L31.6313 3.2207Z" fill="#909090"/>
                            <path d="M31.5407 3.125H11.5605V39.7805H38.934V10.8333L31.5407 3.125Z" fill="#F4F4F4"/>
                            <path d="M11.3597 5.09375H2.97281V14.0542H29.3541V5.09375H11.3597Z" fill="#7A7B7C"/>
                        </svg>
                    </span>
                    <div class="ml-2 overflow-hidden">
                        <p class="text-sm font-medium text-gray-800 dark:text-white truncate" :title="file.rawFile.name">{{file.rawFile.name}}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-500 flex mt-1">{{ formatSize(file.rawFile.size) }}</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-x-2 flex-shrink-0">
                    <button type="button" @click.stop="removeFile(index)" class="text-gray-400 hover:text-red-500 hover:bg-red-50 p-1.5 rounded-full transition-colors" title="Remove Document">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div v-if="!file.docType" class="mt-1 flex items-center text-xs text-red-500 font-medium">
                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                Metadata Required
            </div>
        </div>
    </div>

    <div class="col-span-5 h-full border border-gray-200 rounded-lg bg-gray-50 flex justify-center items-center relative overflow-hidden" v-if="activeFileIndex !== -1 && activeFileIndex < files.length">
        <button class="absolute top-2 right-2 bg-white/90 shadow-sm p-1.5 rounded-md text-gray-700 hover:bg-white hover:text-blue-600 border border-gray-200 z-10 transition-colors" title="Expand Fullscreen" @click="openPreview(files[activeFileIndex].rawFile)">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 4h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
        </button>
        
        <PDFEditor v-if="getActualFileURL(files[activeFileIndex].rawFile) && isPdfFile(files[activeFileIndex].rawFile)" :url="getActualFileURL(files[activeFileIndex].rawFile)" />
        <img v-else-if="getActualFileURL(files[activeFileIndex].rawFile) && isImageFile(files[activeFileIndex].rawFile)" :src="getActualFileURL(files[activeFileIndex].rawFile)" class="max-w-full max-h-full object-contain shadow-md p-4" />
        <div v-else class="text-gray-500 flex flex-col items-center justify-center p-8 text-center h-full">
            <svg class="w-16 h-16 mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="font-medium text-lg">Preview not available</span>
            <span class="text-sm text-gray-400 mt-1">This file type ({{(files[activeFileIndex].rawFile.name || '').split('.').pop().toUpperCase()}}) cannot be previewed.</span>
        </div>
    </div>
    <div class="col-span-5 h-full border border-gray-200 rounded-lg bg-gray-50 flex justify-center items-center" v-else>
        <span class="text-gray-400">Select a document to preview</span>
    </div>

    <div class="col-span-4 h-full overflow-y-auto pl-2" v-if="activeFileIndex !== -1 && activeFileIndex < files.length">
        <div class="card p-5 h-full flex flex-col border border-gray-200 rounded-lg bg-white shadow-sm">
            <div class="flex items-center gap-3 border-b border-gray-100 pb-4 mb-4 shrink-0">
                <div class="bg-blue-50 text-blue-600 p-2 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <div class="overflow-hidden">
                    <h3 class="text-lg font-semibold text-gray-800">Document Metadata</h3>
                    <p class="text-xs text-gray-500 truncate" :title="files[activeFileIndex].rawFile.name">{{ files[activeFileIndex].rawFile.name }}</p>
                </div>
            </div>

            <div class="mb-5 shrink-0">
                <label class="block text-sm font-medium text-gray-700 mb-1">Document Type <span class="text-red-500">*</span></label>
                <select class="border border-gray-300 px-3 py-2 rounded-lg w-full text-gray-700 bg-white focus:ring-blue-500 focus:border-blue-500" v-model="selectedDocType">
                    <option value="">Select document type</option>
                    <option :value="doctype.id" v-for="(doctype, index) in props.doctypes" :key="index">{{ doctype.name }}</option>
                </select>
            </div>

            <div v-if="selected_metadata_strings.length > 0 || selectedDocType !=''" class="flex-1 overflow-y-auto pr-1">
                <div class="flex flex-col gap-4">
                    <div v-for="(mtdata, index) in selected_metadata_strings" :key="index" class="w-full">
                        <div v-html="mtdata.metadata_value"></div>
                    </div>
                </div>
            </div>
            
            <div v-else class="flex-1 flex justify-center items-center text-gray-400 text-sm border-2 border-dashed border-gray-100 rounded-lg p-4 text-center mt-2">
                Select a Document Type above to load metadata fields.
            </div>

            <div class="mt-6 pt-4 border-t border-gray-100 flex justify-end gap-3 shrink-0">
                <button class="text-gray-500 hover:text-gray-700 px-4 py-2 text-sm font-medium transition-colors" @click="cancelForm">Cancel</button>
                <button class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-primary_hover disabled:bg-primary_disabled disabled:opacity-50 text-sm font-medium transition-colors shadow-sm flex items-center gap-2" :disabled="files.length === 0" @click="submitForm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                    Batch Upload
                </button>
            </div>
        </div>
    </div>
</div>

<div v-if="isPreviewModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center bg-black bg-opacity-75 p-4" @click.self="closePreview">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-5xl h-[90vh] flex flex-col overflow-hidden">
        <div class="flex justify-between items-center p-4 border-b">
            <h3 class="font-semibold text-lg truncate">{{ previewFile?.name }}</h3>
            <button @click="closePreview" class="text-gray-500 hover:text-red-500 p-1 rounded-full hover:bg-red-50 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="flex-1 bg-gray-100 overflow-auto flex justify-center items-center relative">
            <PDFEditor v-if="getActualFileURL(previewFile) && isPdfFile(previewFile)" :url="getActualFileURL(previewFile)" />
            <img v-else-if="getActualFileURL(previewFile) && isImageFile(previewFile)" :src="getActualFileURL(previewFile)" class="max-w-full max-h-full object-contain shadow-md" />
            <div v-else class="text-gray-500 flex flex-col items-center">
                <svg class="w-16 h-16 mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="font-medium text-lg">Preview not available</span>
                <span class="text-sm text-gray-400 mt-1">This file type ({{(previewFile?.name || '').split('.').pop().toUpperCase()}}) cannot be previewed in the browser.</span>
            </div>
        </div>
    </div>
</div>

<!-- last div -->
        <div class="mb-6 mt-2">

        </div>
    </div>
    <CreateFolderDialog v-model="createFolder" :department="props.branch" :url="page.props.roles !='record manager'? '/dashboard/create-folder':'/data-entry/create-folder'"/>

</template>

<script setup>
import { ref, defineProps, watch, onMounted } from "vue";
//import CreateFolderModal from "../../components/Modals/CreateFolderModal.vue"
import CreateFolderDialog from "../../components/Modals/branches/CreateFolderDialog.vue";
import { useForm, usePage, Link, router } from '@inertiajs/vue3'
import { useToast } from "primevue/usetoast";
import { useTitle } from "@vueuse/core";

const props = defineProps(['doctypes', 'thumbnails', 'branches', 'branch_type']);

const checked = ref(false);
const checked2 = ref(false);
const checked3 = ref(false);
const selectedDocType = ref('');
const selected_metadata_strings = ref([]);
const search = ref("")
const createFolder = ref(false)
const selectedFolder = ref([])
const files = ref([])
const activeFileIndex = ref(-1);

const isPreviewModalOpen = ref(false);
const previewFile = ref(null);
const openPreview = (file) => { previewFile.value = file; isPreviewModalOpen.value = true; };
const closePreview = () => { isPreviewModalOpen.value = false; previewFile.value = null; };
const getActualFileURL = (file) => { if (!file) return ''; return URL.createObjectURL(file); };
const isImageFile = (file) => { if (!file) return false; const type = file.type || ''; const ext = (file.name || '').split('.').pop().toLowerCase(); return type.startsWith('image/') || ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'].includes(ext); };
const isPdfFile = (file) => { if (!file) return false; const type = file.type || ''; const ext = (file.name || '').split('.').pop().toLowerCase(); return type === 'application/pdf' || ext === 'pdf'; };

const isScanning = ref(false);
const scanDocument = async () => {
    isScanning.value = true;
    try {
        const response = await fetch('http://localhost:9880/scan?profile=Jerry');
        if (!response.ok) {
            if (response.status === 429) {
                toast.add({ severity: 'warn', summary: 'Scanner Busy', detail: 'A scan is already in progress.', life: 3000 });
            } else {
                throw new Error('Scan failed');
            }
            return;
        }
        const blob = await response.blob();
        const file = new File([blob], `Scanned_Document_${Date.now()}.pdf`, { type: 'application/pdf' });
        files.value.push({ rawFile: file, docType: "", metadata: [] });
        if (activeFileIndex.value === -1) {
            selectFile(0);
        }
        toast.add({ severity: 'success', summary: 'Scan Successful', detail: 'Document scanned and staged.', life: 3000 });
    } catch (error) {
        console.error(error);
        toast.add({ severity: 'error', summary: 'Scan Failed', detail: 'Could not connect to scanner.', life: 3000 });
    } finally {
        isScanning.value = false;
    }
};
const cancelScan = async () => {
    isScanning.value = false;
    try {
        await fetch('http://localhost:9880/scan', { method: 'DELETE' });
        toast.add({ severity: 'info', summary: 'Scan Cancelled', detail: 'Scanning operation aborted.', life: 3000 });
    } catch(e) {}
};

const form = useForm({
  files: [],
  description:'',
  text:'',
  date:'',
  number:'',
  folder:null,
  folder_id:null,
  branch_type: '',
  input_obj: [],
  branch_id: null
})
const description = ref('')
const text = ref('')
const date = ref('')
const number = ref('')
const folderId = ref(null);
const toast = useToast();
const page = usePage()
const branch_type = ref('')
const selected_metadata = ref([]);

// const metadata_strings = [
//     {
//         description: `<div class="my-2 w-full">
//                 <label for="description">Description</label>
//                 <textarea name="description" v-model="description" :value="description" id="description" cols="30" rows="3" class=" border border-gray-200 rounded-lg block mt-2 w-full focus:border-blue-400 p-3"></textarea>
//             </div>`,
//         },
//         {
//         text: `<div class="my-2">
//                 <label for="title">Title</label>
//                 <input type="text" v-model="text" name="text" id="text" class=" border border-gray-200 rounded-lg mt-2 focus:border-blue-400 block p-2 w-full">
//             </div>`,
//         },
//         {
//         date: `<div class="my-2">
//                 <label for="date">Date</label>
//                 <input type="date" v-model="date" name="date" id="date" class=" border border-gray-200 rounded-lg mt-2 focus:border-blue-400 block p-2 w-full">
//             </div>`,
//         },
//         {
//         number: `<div class="my-2">
//                 <label for="numbers">Document Number</label>
//                 <input type="text" v-model="number" class=" border border-gray-200 rounded-lg mt-2 focus:border-blue-400 block p-2 w-full" id="numbers">
//             </div>`
//     }
// ]

const handleFileUpload = (event) =>{
    const newFiles = Array.from(event.target.files);
    newFiles.forEach(file => {
        files.value.push({ rawFile: file, docType: "", metadata: [] });
    });
    if (files.value.length > 0 && activeFileIndex.value === -1) {
        selectFile(0);
    }
}


const getFileURL = (file) =>{
      if (file.type) {
        if(file.type == 'image/jpeg' || file.type == 'image/jpeg' || file.type == 'image/png' || file.type == 'image/gif'){
            return URL.createObjectURL(file); 
        }
       
        if(file.type == 'application/pdf'){
            return page.props.base+'/images/pdf.svg' 
        }

        if(file.type == 'application/msword' || file.type == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'){
            return page.props.base+'/images/word.svg'
        }

        if(file.type == 'application/vnd.ms-excel' || file.type == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'){
            return page.props.base+'/images/excel.svg'
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

const formatSize = (size) => {
      const units = ['B', 'KB', 'MB', 'GB', 'TB'];
      let i = 0;
      while (size >= 1024) {
        size /= 1024;
        i++;
      }
      return size.toFixed(2) + ' ' + units[i];
    }

    const uploadFiles = () =>{
        form.post('/upload-files',{onSuccess: ()=>{
            //alert(' this was successfull')
            form.files = null;
            //files.value = []
        }})
    }


    
const saveCurrentMetadata = () => {
    if (activeFileIndex.value === -1 || activeFileIndex.value >= files.value.length) return;
    let allInputs = document.getElementsByClassName("metadata-inputs");
    let input_obj = [];
    for(let i = 0; i < allInputs.length; i++){
        let key = allInputs[i].getAttribute("name");
        let value = allInputs[i].value;
        input_obj.push({ [key]: value });
    }
    files.value[activeFileIndex.value].docType = selectedDocType.value;
    files.value[activeFileIndex.value].metadata = input_obj;
};

const selectFile = (index) => {
    if (activeFileIndex.value !== -1 && activeFileIndex.value < files.value.length) {
        saveCurrentMetadata();
    }
    activeFileIndex.value = index;
    const nextFile = files.value[index];
    
    // Changing selectedDocType will trigger the watch to render new inputs
    selectedDocType.value = nextFile.docType;
    
    setTimeout(() => {
        let allInputs = document.getElementsByClassName("metadata-inputs");
        if (nextFile.metadata && nextFile.metadata.length > 0) {
            for(let i = 0; i < allInputs.length; i++){
                let key = allInputs[i].getAttribute("name");
                let saved = nextFile.metadata.find(m => Object.keys(m)[0] === key);
                if (saved) {
                    allInputs[i].value = saved[key];
                }
            }
        }
    }, 150);
};

const removeFile = (index) => {
    files.value.splice(index, 1);
    if (files.value.length === 0) {
        activeFileIndex.value = -1;
        selectedDocType.value = "";
    } else if (activeFileIndex.value === index) {
        selectFile(0);
    } else if (activeFileIndex.value > index) {
        activeFileIndex.value--;
    }
};


const submitForm = ()=>{
    if (files.value.length === 0) return;
    saveCurrentMetadata(); 
    
    for (let i = 0; i < files.value.length; i++) {
        let f = files.value[i];
        if (!f.docType || f.metadata.length === 0) {
            selectFile(i);
            return;
        }
        let hasEmpty = f.metadata.some(m => Object.values(m)[0] === "");
        if (hasEmpty) {
            selectFile(i);
            return;
        }
    }

    let formData = new FormData();
    formData.append("folder_id", selectedFolder.value?.id || page.props.folder_id || folderId.value || '');

    files.value.forEach((f, idx) => {
        formData.append(`documents[${idx}][file]`, f.rawFile);
        formData.append(`documents[${idx}][doctype_id]`, f.docType);
        f.metadata.forEach((m, mIdx) => {
            formData.append(`documents[${idx}][input_obj][${mIdx}]`, JSON.stringify(m));
        });
    });

    let url = ""; 
    if(page.props.roles.includes("record manager")) { 
        url = "/batch-upload-files"; 
    } else { 
        url = "/dashboard/batch-upload-files"; 
    }
    
    router.post(url, formData, {
        onSuccess: () => {
            files.value = [];
            activeFileIndex.value = -1;
            selectedDocType.value = "";
            selected_metadata_strings.value = [];
            defaultDocTypes();
            toast.add({ severity: "success", summary: "Files Uploaded", detail: "Batch upload successful.", life: 3000 });
        }
    });
}

const cancelForm = ()=>{
    form.reset();
    files.value = [];
                selectedDocType.value =''
                //selectedFolder.value.length = 0
                selected_metadata_strings.value = []
                selectedDocType.value = ''
               // folder_id = null
               defaultDocTypes()
}

const defaultDocTypes = ()=>{
    let defaultsDocs = [];
    for(let doc of props.doctypes){
        for(let metadata of doc.metadata){
            if(metadata.is_default){
                let filtered = defaultsDocs.find((val)=>{
                    return val.id == metadata.id
                })
                if(!filtered){
                    defaultsDocs.push(metadata)
                }
            }
        }
    }
 

selected_metadata_strings.value = defaultsDocs;
}

//handle file drop 
const handleDrop = (event)=>{
    event.preventDefault();
    const newFiles = event.dataTransfer.files;
    for(let i=0; i < newFiles.length; i++){
        form.files.push(newFiles[i]);
        files.value.push(newFiles[i])
    }
    uploadFiles()
}

watch(selectedDocType, (val)=>{
    if(val != ''){
        //get the metadata belonging to the selected doc type
    selected_metadata_strings.value = []
    let metadata = props.doctypes.find((data)=>{
        return data.id ==val
    });

    selected_metadata_strings.value = metadata.metadata;
   
    }else{
        defaultDocTypes()
    }
    
})

watch(description, (val)=>{
    form.description = description
})

watch(text, (val)=> {
    form.text = text
})

watch(date, (val)=>{
    form.date = date
})

watch(number, (val)=>{
    form.number = number
})

watch(()=>props.doctypes, (val)=>{
   // alert(' yes')
})

onMounted(()=>{
    // let myDropzone = new Dropzone("#dropzone");
    //     myDropzone.on("addedfile", file => {
    //     console.log(`File added: ${file.name}`);
    //     });
    defaultDocTypes()

    let url = new URL(window.location.href);
    let url_arr = url.pathname.split('/');
    let folderid = url_arr.pop()
    folderId.value = folderid;
    form.folder_id = folderid;
    selectedFolder.value = folderid

    //get the branch type
    form.branch_type = url_arr[url_arr.length -1]
    console.log(folderid)
     })
  
</script>