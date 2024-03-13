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
                        <svg class="border ml-[35px] mr-5 border-gray-300 p-2 w-10 h-10 rounded-lg" width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17.079 10.9989H4.92105M4.92105 10.9989L11 17.0778M4.92105 10.9989L11 4.91992" stroke="#363C46" stroke-width="1.73684" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class=" text-2xl font-semibold">Upload Documents</h2>
                        <h4 class="text-gray-400 text-sm mt-1">Archive files into directories</h4>
                    </div>
                </div>
                <div class="lg:text-end md:text-center sm:text-center pl-6">
                    <button class=" mr-6 text-green-700">Cancel</button>
                    <button class=" bg-blue-600 text-white px-[64px] py-2 rounded-lg mr-[54px] hover:bg-blue-500 disabled:bg-blue-400" :disabled="files.length == 0 || selected_metadata_strings.length == 0" @click="submitForm">Save</button>
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
        <div class="flex items-center justify-center upload-document-txt ">
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
        </div>
        <!-- eight div -->
        <div class=" mb-6">
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
                    <!-- <div class=" col-span-4 border border-gray-100 bg-gray-100 p-2 rounded-lg cursor-pointer flex">
                        <div class="card flex justify-content-center items-center">
                            <Checkbox v-model="checked2" :binary="true" />
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
                            <h4 class=" text-lg ">Architecture Plan</h4>
                            <p class="text-sm">16 Files</p>
                        </div>
                    </div> -->
                    <!-- <div class=" col-span-4 border border-gray-100 bg-gray-100 p-2 rounded-lg cursor-pointer flex">
                        <div class="card flex justify-content-center items-center">
                            <Checkbox v-model="checked3" :binary="true" />
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
                            <h4 class=" text-lg ">Architecture Plan</h4>
                            <p class="text-sm">16 Files</p>
                        </div>
                    </div> -->
                </div>
            </div>
        </div>
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
                        <h3 class=" text-xl font-semibold">Upload Files</h3>
                        <h4 class="text-gray-600 text-sm mt-1">Select the file from your device to upload</h4>
                    </div>
                </div>
            </div>
        </div>
        <!-- fourth div -->
        <div class=" mb-6">
            <!-- <div class=" w-5/6 mt-6 mx-auto border border-dashed rounded-lg p-5 border-gray-400 items-center text-center">
                        <svg class="mx-auto" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_653_12633)">
                            <path d="M12 12.0001L8.99997 9.00011M8.99997 9.00011L5.99997 12.0001M8.99997 9.00011V15.7501M15.2925 13.7926C16.024 13.3938 16.6018 12.7628 16.9349 11.9991C17.2679 11.2354 17.3371 10.3825 17.1316 9.57513C16.9261 8.76772 16.4576 8.05174 15.8 7.54019C15.1424 7.02865 14.3331 6.75066 13.5 6.75011H12.555C12.328 5.87204 11.9048 5.05686 11.3174 4.36585C10.73 3.67485 9.99361 3.126 9.16355 2.76057C8.33349 2.39513 7.43138 2.22263 6.52506 2.25602C5.61873 2.28941 4.73177 2.52784 3.93085 2.95336C3.12994 3.37888 2.4359 3.98044 1.90094 4.7128C1.36597 5.44516 1.00399 6.28926 0.842205 7.18166C0.680422 8.07405 0.723049 8.99151 0.96688 9.86506C1.21071 10.7386 1.6494 11.5455 2.24997 12.2251" stroke="#344054" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </g>
                            <defs>
                            <clipPath id="clip0_653_12633">
                            <rect width="18" height="18" fill="white"/>
                            </clipPath>
                            </defs>
                        </svg>
                        <p>Choose a file or drag & drop it here</p>
                        <p>JPEG, PNG, PDF, DOC, DOCX, XLX</p>
                        <input type="file" name="" id="">
            </div> -->
            
            <div class="flex items-center justify-center w-5/6 mx-auto mt-9 drop-area" @dragover.prevent @drop="handleDrop">
                <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-48 border-2 border-gray-300 border-dashed rounded-lg bg-gray-50 dark:hover:bg-bray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600" :class="selectedFolder.length == 0?'cursor-not-allowed':'cursor-pointer'">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        <svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                        </svg>
                        <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">SVG, PNG, JPG, GIF, DOC, XLX</p>
                    </div>
                    <input id="dropzone-file" @input="form.files = $event.target.files" :disabled="selectedFolder.length == 0" type="file" class="hidden" multiple @change="handleFileUpload" />
                </label>
            </div> 

            <!-- File Uploading Progress Form -->
            <div class=" w-3/6 ml-[105px] my-2 border border-gray-200 p-2 rounded-lg relative" v-for="(file, index) in files" :key="index">
            <!-- Uploading File Content -->
            <div class="mb-1 flex justify-between items-center">
                <div class="flex items-center gap-x-3">
                    <span class="w-8 h-8 flex justify-center items-center text-gray-500">
                        <svg width="42" height="43" viewBox="0 0 42 43" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M31.6313 3.2207L38.934 10.8332V39.7817H11.6537V39.8762H39.0272V10.929L31.6313 3.2207Z" fill="#909090"/>
                        <path d="M31.5407 3.125H11.5605V39.7805H38.934V10.8333L31.5407 3.125Z" fill="#F4F4F4"/>
                        <path d="M11.3597 5.09375H2.97281V14.0542H29.3541V5.09375H11.3597Z" fill="#7A7B7C"/>
                        <path d="M29.4945 13.9025H3.1434V4.93555H29.4945V13.9025Z" fill="#DD2025"/>
                        <path d="M11.8807 6.44956H10.1652V12.7496H11.5145V10.6246L11.8124 10.6417C12.1019 10.6367 12.3887 10.5848 12.6616 10.4881C12.9009 10.4058 13.121 10.2759 13.3087 10.1062C13.4996 9.94451 13.6502 9.74047 13.7483 9.51031C13.88 9.12771 13.927 8.7211 13.8862 8.31856C13.878 8.031 13.8275 7.74621 13.7365 7.47331C13.6537 7.27631 13.5307 7.09872 13.3755 6.95185C13.2202 6.80497 13.0361 6.69202 12.8348 6.62019C12.6608 6.55718 12.481 6.51146 12.298 6.48369C12.1595 6.46231 12.0195 6.4509 11.8793 6.44956M11.6313 9.46044H11.5145V7.51794H11.7678C11.8796 7.50987 11.9918 7.52704 12.096 7.56816C12.2003 7.60927 12.294 7.6733 12.3702 7.7555C12.5281 7.96683 12.6125 8.22406 12.6104 8.48787C12.6104 8.81075 12.6104 9.10344 12.319 9.3095C12.1091 9.42495 11.8702 9.47825 11.6313 9.46044ZM16.4495 6.4325C16.3038 6.4325 16.162 6.443 16.0623 6.44694L15.7499 6.45481H14.7262V12.7548H15.931C16.3915 12.7674 16.8499 12.6894 17.2803 12.5251C17.6267 12.3877 17.9334 12.1663 18.1728 11.8807C18.4056 11.5925 18.5727 11.257 18.6623 10.8976C18.7655 10.4906 18.8158 10.0719 18.812 9.65206C18.8374 9.15614 18.7991 8.65901 18.6978 8.17287C18.6017 7.81504 18.4217 7.48524 18.1728 7.21081C17.9775 6.98921 17.7384 6.81046 17.4706 6.68581C17.2406 6.57939 16.9987 6.50095 16.75 6.45219C16.6511 6.43585 16.551 6.42838 16.4508 6.42987M16.2119 11.5972H16.0807V7.57569H16.0977C16.3683 7.54455 16.6421 7.59338 16.8852 7.71612C17.0633 7.8583 17.2084 8.03742 17.3105 8.24112C17.4207 8.4555 17.4842 8.69079 17.4968 8.9315C17.5087 9.22025 17.4968 9.4565 17.4968 9.65206C17.5022 9.87733 17.4877 10.1026 17.4535 10.3254C17.4131 10.554 17.3383 10.7753 17.2317 10.9816C17.1111 11.1735 16.9481 11.3351 16.7553 11.4541C16.5934 11.5589 16.4016 11.6077 16.2093 11.5932M22.8768 6.45481H19.6874V12.7548H21.0367V10.2558H22.7429V9.08506H21.0367V7.62556H22.8742V6.45481" fill="#464648"/>
                        <path d="M28.5876 27.0852C28.5876 27.0852 32.7718 26.3266 32.7718 27.7559C32.7718 29.1852 30.1796 28.6038 28.5876 27.0852ZM25.494 27.1942C24.8292 27.341 24.1814 27.5561 23.5607 27.836L24.0857 26.6547C24.6107 25.4735 25.1554 23.863 25.1554 23.863C25.7819 24.9174 26.5108 25.9074 27.3315 26.8188C26.7126 26.9111 26.0991 27.0373 25.494 27.1968V27.1942ZM23.8376 18.6629C23.8376 17.4173 24.2406 17.0774 24.5543 17.0774C24.868 17.0774 25.221 17.2283 25.2328 18.3098C25.1306 19.3973 24.9029 20.4693 24.5543 21.5045C24.0767 20.6354 23.8295 19.6585 23.8363 18.6668L23.8376 18.6629ZM17.7358 32.4652C16.4522 31.6973 20.4278 29.3335 21.1483 29.2574C21.1444 29.2587 19.0798 33.2684 17.7358 32.4652ZM33.9938 27.9252C33.9806 27.794 33.8625 26.341 31.2769 26.4027C30.1992 26.3854 29.122 26.4613 28.0573 26.6298C27.0261 25.5908 26.138 24.4189 25.4166 23.1451C25.8711 21.8318 26.1461 20.4632 26.2343 19.0763C26.1962 17.5013 25.8195 16.5983 24.612 16.6115C23.4045 16.6246 23.2286 17.6812 23.3875 19.2535C23.543 20.3102 23.8365 21.3418 24.2603 22.3222C24.2603 22.3222 23.7025 24.0586 22.9648 25.7858C22.2272 27.5131 21.7232 28.4187 21.7232 28.4187C20.4405 28.8363 19.233 29.4566 18.1466 30.2562C17.0651 31.2629 16.6255 32.036 17.1951 32.809C17.686 33.4758 19.404 33.6267 20.9396 31.6147C21.7556 30.5754 22.501 29.4827 23.1709 28.3439C23.1709 28.3439 25.5124 27.7021 26.2408 27.5262C26.9693 27.3503 27.85 27.2112 27.85 27.2112C27.85 27.2112 29.988 29.3624 32.05 29.2863C34.1119 29.2102 34.0121 28.0538 33.999 27.9278" fill="#DD2025"/>
                        <path d="M31.4397 3.22656V10.9349H38.833L31.4397 3.22656Z" fill="#909090"/>
                        <path d="M31.5407 3.125V10.8333H38.934L31.5407 3.125Z" fill="#F4F4F4"/>
                        <path d="M11.7796 6.34995H10.0642V12.65H11.4187V10.5263L11.718 10.5434C12.0075 10.5384 12.2942 10.4865 12.5671 10.3898C12.8064 10.3075 13.0265 10.1776 13.2142 10.0079C13.4037 9.84578 13.5529 9.64177 13.65 9.41201C13.7816 9.02942 13.8286 8.62281 13.7878 8.22026C13.7796 7.93271 13.7292 7.64791 13.6381 7.37501C13.5553 7.17802 13.4323 7.00043 13.2771 6.85355C13.1219 6.70667 12.9377 6.59372 12.7365 6.52189C12.5616 6.45827 12.3809 6.41211 12.197 6.38408C12.0585 6.3627 11.9185 6.35129 11.7783 6.34995M11.5303 9.36083H11.4135V7.41833H11.6681C11.7799 7.41026 11.8921 7.42743 11.9963 7.46855C12.1006 7.50967 12.1943 7.57369 12.2705 7.65589C12.4284 7.86722 12.5128 8.12446 12.5107 8.38826C12.5107 8.71114 12.5107 9.00383 12.2193 9.20989C12.0094 9.32534 11.7705 9.37732 11.5316 9.35951M16.3485 6.33289C16.2028 6.33289 16.061 6.34339 15.9613 6.34733L15.6528 6.3552H14.6291V12.6552H15.834C16.2944 12.6678 16.7529 12.5898 17.1832 12.4255C17.5296 12.2881 17.8363 12.0667 18.0757 11.7811C18.3085 11.4929 18.4756 11.1574 18.5653 10.798C18.6684 10.391 18.7187 9.97234 18.7149 9.55245C18.7404 9.05653 18.702 8.5594 18.6007 8.07326C18.5046 7.71543 18.3246 7.38564 18.0757 7.1112C17.8804 6.8896 17.6413 6.71085 17.3735 6.5862C17.1435 6.47978 16.9016 6.40134 16.653 6.35258C16.5541 6.33624 16.4539 6.32877 16.3537 6.33026M16.1148 11.4976H15.9836V7.47608H16.0006C16.2712 7.44494 16.545 7.49377 16.7881 7.61651C16.9662 7.75869 17.1113 7.93781 17.2134 8.14151C17.3236 8.35589 17.3871 8.59118 17.3998 8.83189C17.4116 9.12064 17.3998 9.35689 17.3998 9.55245C17.4051 9.77773 17.3906 10.003 17.3565 10.2258C17.316 10.4544 17.2412 10.6757 17.1346 10.882C17.014 11.0738 16.851 11.2355 16.6582 11.3545C16.4963 11.4592 16.3045 11.5081 16.1122 11.4936M22.7758 6.3552H19.5864V12.6552H20.9356V10.1562H22.6419V8.98545H20.9356V7.52595H22.7731V6.3552" fill="white"/>
                        </svg>
                        <div>
                            <img :src="getFileURL(file)" class=" h-[42px] w-[43px] ml-3" alt="File Preview" style="max-width: 200px; max-height: 200px;" />
                        </div>
                        
                    </span>
                    <div class="ml-2">
                        <p class="text-sm font-medium text-gray-800 dark:text-white">{{file.name}}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-500 flex mt-1" v-if="form.progress">{{ form.progress.loaded / 1024}} KB of {{ formatSize(file.size) }}
                            <div aria-label="Loading..." role="status" class="flex items-center space-x-2 ml-3">
                                <svg class=" h-5 w-5 animate-spin stroke-gray-500" viewBox="0 0 256 256">
                                    <line x1="128" y1="32" x2="128" y2="64" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line>
                                    <line x1="195.9" y1="60.1" x2="173.3" y2="82.7" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="24"></line>
                                    <line x1="224" y1="128" x2="192" y2="128" stroke-linecap="round" stroke-linejoin="round" stroke-width="24">
                                    </line>
                                    <line x1="195.9" y1="195.9" x2="173.3" y2="173.3" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="24"></line>
                                    <line x1="128" y1="224" x2="128" y2="192" stroke-linecap="round" stroke-linejoin="round" stroke-width="24">
                                    </line>
                                    <line x1="60.1" y1="195.9" x2="82.7" y2="173.3" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="24"></line>
                                    <line x1="32" y1="128" x2="64" y2="128" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line>
                                    <line x1="60.1" y1="60.1" x2="82.7" y2="82.7" stroke-linecap="round" stroke-linejoin="round" stroke-width="24">
                                    </line>
                                </svg>
                                <span class="text-xs font-medium text-gray-500">Loading...</span>
                            </div>
                        </p>
                    </div>
                    </div>
                   <svg class=" absolute top-1 right-1 cursor-pointer h-[18px] w-[18px]" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11.25 6.75L6.75 11.25M6.75 6.75L11.25 11.25M16.5 9C16.5 13.1421 13.1421 16.5 9 16.5C4.85786 16.5 1.5 13.1421 1.5 9C1.5 4.85786 4.85786 1.5 9 1.5C13.1421 1.5 16.5 4.85786 16.5 9Z" stroke="#475467" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>

                </div>
                <!-- End Uploading File Content -->

                <!-- Progress Bar -->
                <!-- <div class="flex items-center gap-x-3 whitespace-nowrap">
                    <div class="flex w-full h-2 bg-gray-200 rounded-full overflow-hidden dark:bg-gray-700" role="progressbar" aria-valuenow="1" aria-valuemin="0" aria-valuemax="100">
                    <div class="flex flex-col justify-center rounded-full overflow-hidden bg-blue-600 text-xs text-white text-center whitespace-nowrap transition duration-500 dark:bg-blue-500" style="width: 1%"></div>
                    </div>
                    <div class="w-6 text-end">
                    <span class="text-sm text-gray-800 dark:text-white">0%</span>
                    </div>
                </div> -->
                <progress v-if="form.progress" :value="form.progress.percentage" max="100" class="w-full rounded-lg h-2">
                {{ form.progress.percentage }}%
                </progress>
                <!-- End Progress Bar -->
            </div>
            <!-- End File Uploading Progress Form -->

        </div>
        <!-- fifth div -->
        <div class="flex items-center justify-center upload-document-txt">
            <div class="grid grid-cols-12 gap-4 w-full items-center">
                <div class="col-span-6 flex">
                    <div>
                        <svg class="w-[50px] border border-gray-200 rounded-full h-[50px] p-3 ml-[35px] mr-5" viewBox="0 0 19 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9.39063 16.2491H16.4219M12.9063 3.35845C13.2171 3.04765 13.6386 2.87305 14.0781 2.87305C14.2958 2.87305 14.5113 2.91591 14.7123 2.9992C14.9134 3.08249 15.0961 3.20456 15.25 3.35845C15.4039 3.51235 15.526 3.69504 15.6093 3.89611C15.6925 4.09718 15.7354 4.31269 15.7354 4.53033C15.7354 4.74797 15.6925 4.96347 15.6093 5.16454C15.526 5.36561 15.4039 5.54831 15.25 5.7022L5.48438 15.4678L2.35938 16.2491L3.14063 13.1241L12.9063 3.35845Z" stroke="#344054" stroke-width="1.56563" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class=" text-xl font-semibold">Document Details</h3>
                        <h4 class="text-gray-600 text-sm mt-1">Fill in basic details & select the document branch</h4>
                    </div>
                </div>
            </div>
        </div>
        <!-- sixth div -->
        <div>
            <!-- <div class="w-5/6 m-auto grid grid-cols-12 mt-4 gap-8 py-4">
                <div class="col-span-6">
                    <div>
                        <label for="file_name">File Name</label>
                        <input type="text" id="file_name" v-model="form.file_name" class="border border-gray-200 rounded-lg p-2 mt-2 w-full focus:border-blue-400" placeholder="File name">
                    </div>
                </div>
                <div class="col-span-6">
                    <div>
                        <label for="file_name">File number</label>
                        <input type="number" id="file_number" v-model="form.file_number" class="border border-gray-200 rounded-lg p-2 mt-2 w-full focus:border-blue-400" placeholder="File number">
                    </div>
                </div>
            </div> -->
            <div v-if="selected_metadata_strings.length > 0 || selectedDocType !=''" class="mb-4">
                <div class="w-5/6 m-auto grid grid-cols-12 mt-4 gap-8">
                    <!-- <div class="col-span-6">
                        <div>
                            <label for="name">Project title <span class=" text-red-600 ml-2">*</span></label>
                            <input type="text" id="name" class=" mt-2 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Enter project name" required>
                        </div>
                    </div>
                    <div class="col-span-6">
                        <div>
                            <label for="client_name">Client name <span class=" text-red-600 ml-2">*</span></label>
                            <input type="text" id="client_name" class=" mt-2 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Enter client name" required>
                        </div>
                    </div> -->
                    <div v-for="(mtdata, index) in selected_metadata_strings" class="col-span-6" gap-8>
                        <div v-for="(md) in mtdata"  >
                            <div v-html="md"></div>
                        </div>
                    </div>
                </div>
                <div class="w-5/6 m-auto grid-cols-12 gap-8">
                    <div class="col-span-6">

                    </div>
                </div>
            </div>
        </div>
        
        <!-- Ninth div -->
        <div class="flex items-center justify-center upload-document-txt">
            <div class="grid grid-cols-12 gap-4 w-full items-center">
                <div class="col-span-6 flex items-center">
                   
                </div>
                <div class="col-span-6 text-end">
                    <button class=" mr-6 text-green-700">Cancel</button>
                    <button class=" bg-blue-600 text-white px-[64px] py-2 rounded-lg mr-[54px] hover:bg-blue-500 disabled:bg-blue-400"  :disabled="files.length == 0 || selected_metadata_strings.length == 0 " @click="submitForm">Save</button>
                </div>
            </div>
        </div>
        <!-- last div -->
        <div class="mb-6 mt-2">

        </div>
    </div>
    <CreateFolderDialog v-model="createFolder" :department="props.department" :url="page.props.roles !='data entry'? '/admin/create-folder':'/dashboard/create-folder'"/>

</template>

<script setup>
import { ref, defineProps, watch, onMounted, onUpdated } from "vue";
//import CreateFolderModal from "../../components/Modals/CreateFolderModal.vue"
import CreateFolderDialog from "../../components/Modals/branches/CreateFolderDialog.vue";
import { useForm, usePage } from '@inertiajs/vue3'
import { useToast } from "primevue/usetoast";

const props = defineProps(['doctypes','folders','thumbnails', 'department']);

const checked = ref(false);
const checked2 = ref(false);
const checked3 = ref(false);
const selectedDocType = ref('');
const selected_metadata_strings = ref([]);
const search = ref("")
const createFolder = ref(false)
const selectedFolder = ref([])
const files = ref([])
const form = useForm({
  files: [],
  description:'',
  text:'',
  date:'',
  number:'',
  folder:null,
  folder_id:null
  //file_name:'',
  //file_number:''
})
const description = ref('')
const text = ref('')
const date = ref('')
const number = ref('')
const folderId = ref(null);
const toast = useToast();
const page = usePage()

const metadata_strings = [
    {
        description: `<div class="my-2 w-full">
                <label for="description">Description</label>
                <textarea name="description" v-model="description" :value="description" id="description" cols="30" rows="3" class=" border border-gray-200 rounded-lg block mt-2 w-full focus:border-blue-400 p-3"></textarea>
            </div>`,
        },
        {
        text: `<div class="my-2">
                <label for="title">Title/Name</label>
                <input type="text" v-model="text" name="text" id="text" class=" border border-gray-200 rounded-lg mt-2 focus:border-blue-400 block p-2 w-full">
            </div>`,
        },
        {
        date: `<div class="my-2">
                <label for="date">Date</label>
                <input type="date" v-model="date" name="date" id="date" class=" border border-gray-200 rounded-lg mt-2 focus:border-blue-400 block p-2 w-full">
            </div>`,
        },
        {
        number: `<div class="my-2">
                <label for="numbers">Numbers</label>
                <input type="number" v-model="number" class=" border border-gray-200 rounded-lg mt-2 focus:border-blue-400 block p-2 w-full" id="numbers">
            </div>`
    }
]

const handleFileUpload = (event) =>{
    files.value = Array.from(event.target.files);
    uploadFiles()
}

const isImage = (mimeType)=> {
      return mimeType && mimeType.startsWith('image');
    }

const getFileURL = (file) =>{
      if (file.type) {
        return URL.createObjectURL(file);
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

    const getFolderId= (folder)=>{
        folderId.value = folder.id;
        form.folder_id = folder.id
    }

    const submitForm = ()=>{
        let description = document.getElementById('description')
        let text = document.getElementById('text')
        let date = document.getElementById('date')
        let number = document.getElementById('numbers')
        let file_name = document.getElementById('file_name');
        let file_number = document.getElementById('file_number');
        if(file_name != null){
            if(file_name.value ==''){
                file_name.focus();
                return false
            }
        }
        if(file_number != null){
            if(file_number.value ==''){
                file_number.focus();
                return false;
            }
        }
        if(description != null){
            if(description.value ==''){
                description.focus();
                return false
            }
            form.description = description.value
        }
        if(text != null){
            if(text.value ==''){
                text.focus();
                return false
            }
            form.text = text.value
        }
        if(date != null){
            if(date.value ==''){
                date.focus();
                return false
            }
            form.date = date.value
        }
        if(number != null){
            if(number.value ==''){
                number.focus();
                return false
            }
            form.number = number.value
        }

        form.post('/archive-files',{
            onSuccess: page => {
               
                form.reset();
                files.value = [];
                //description.value = ''
               // text.value = ''
               // number.value = ''
               // date.value = ''
                selectedDocType.value =''
                selectedFolder.value.length = 0
                selected_metadata_strings.value = []
                selectedDocType.value = ''
               // folder_id = null

                toast.add({
                    severity: "success",
                    summary: "File Archived",
                    detail: "You've successfully archived a file.",
                    life: 3000,
                });
            }
        } )
    }

    //files to be reviewed
const defaultDocTypes = ()=>{
    let defaultsDocs = [];
    for(let doc of props.doctypes){
        for(let metadata of doc.metadata){
            if(metadata.is_default){
                defaultsDocs.push(metadata)
            }
        }
    }

    let strings = []
    for(let mdata of defaultsDocs){
        strings.push(mdata.string)
    }

    strings = [...new Set(strings)]

    for (const obj of metadata_strings) {
        const objKeys = Object.keys(obj);
       
        for(let i=0; i < strings.length; i++){
            if(objKeys.includes(strings[i].toLowerCase())){
                selected_metadata_strings.value.push(obj) 
            }
        }
     }
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

    metadata = metadata.metadata;
    let strings = []
    for(let mdata of metadata){
        strings.push(mdata.string)
    }
   
    //get the matching metdata
    for (const obj of metadata_strings) {
        const objKeys = Object.keys(obj);
       
        for(let i=0; i < strings.length; i++){
            if(objKeys.includes(strings[i].toLowerCase())){
                selected_metadata_strings.value.push(obj) 
            }
        }
     }
    }
    
})

watch(selectedFolder, (newVal, oldVal)=>{
   if(selectedFolder.value.length > 1){
    selectedFolder.value.shift()
   }
    selectedFolder.value=newVal
    form.folder = selectedFolder
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
   // alert(page.props.roles)
     })
  

</script>