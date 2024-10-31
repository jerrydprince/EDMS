<template>
    <div>
        <div class=" w-4/6 mx-auto mt-4 ">
                    <div class=" flex items-center justify-center" v-if="!formSuccess">
                        <svg  width="54" height="54" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="0.5" y="0.5" width="63" height="63" rx="14.0455" fill="white"/>
                            <rect x="0.5" y="0.5" width="63" height="63" rx="14.0455" stroke="#EAECF0"/>
                            <g clip-path="url(#clip0_1056_19206)">
                            <path d="M37.8182 45.091V42.1819C37.8182 40.6388 37.2052 39.159 36.1141 38.0678C35.023 36.9767 33.5431 36.3637 32 36.3637H21.8182C20.2751 36.3637 18.7952 36.9767 17.7041 38.0678C16.613 39.159 16 40.6388 16 42.1819V45.091M43.6364 26.1819V34.9092M48 30.5455H39.2727M32.7273 24.7274C32.7273 27.9407 30.1224 30.5455 26.9091 30.5455C23.6958 30.5455 21.0909 27.9407 21.0909 24.7274C21.0909 21.5141 23.6958 18.9092 26.9091 18.9092C30.1224 18.9092 32.7273 21.5141 32.7273 24.7274Z" stroke="#475467" stroke-width="2.90909" stroke-linecap="round" stroke-linejoin="round"/>
                            </g>
                            <defs>
                            <clipPath id="clip0_1056_19206">
                            <rect width="34.9091" height="34.9091" fill="white" transform="translate(14.5454 14.5454)"/>
                            </clipPath>
                            </defs>
                        </svg>
                    </div>
                    <div class="mt-4">
                        <h3 v-if="!formSuccess" class=" text-3xl mt-4 text-center font-bold">
                            Create Account
                        </h3>
                        <p v-if="!formSuccess" class="text-center mt-2 text-gray-500">Get started by creating profile.</p>
                        <div class="mt-4">

                        <div v-if="!formSuccess" class="mb-3 bg-gray-50 border border-gray-200 rounded-lg py-[18px] px-[20px] flex justify-between">
                                    
                            <div class="flex items-center">
                                <div class="flex items-center h-5">
                                    <!-- <input id="helper-radio" aria-describedby="helper-radio-text" type="radio" value="" class="w-4 h-4 text-blue-600 border border-white dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"> -->
                                    <svg v-if="form1" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="2.08333" y="2.08333" width="15.8333" height="15.8333" rx="7.91667" stroke="#3B82F6" stroke-width="4.16667"/>
                                    </svg>
                                    <svg v-else width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 2C6.49 2 2 6.49 2 12C2 17.51 6.49 22 12 22C17.51 22 22 17.51 22 12C22 6.49 17.51 2 12 2ZM16.78 9.7L11.11 15.37C10.97 15.51 10.78 15.59 10.58 15.59C10.38 15.59 10.19 15.51 10.05 15.37L7.22 12.54C6.93 12.25 6.93 11.77 7.22 11.48C7.51 11.19 7.99 11.19 8.28 11.48L10.58 13.78L15.72 8.64C16.01 8.35 16.49 8.35 16.78 8.64C17.07 8.93 17.07 9.4 16.78 9.7Z" fill="#12B76A"/>
                                    </svg>

                                </div>
                                <div class="ms-2 text-sm min-w-32" v-if="form1">
                                    <p  class="font-normal text-gray-400 text-xs">STEP 1</p>
                                    <p id="helper-radio-text" class="text-sm font-medium text-gray-700 dark:text-gray-300 font-semibold">Basic information</p>
                                </div>
                            </div>
                            <div class="flex items-center justify-center w-1/2">
                                <span class="border-2 border-t border-gray-300 block flex-grow  h-0 mx-2"></span>
                            </div>

                            <div class="flex items-center">
                                <div class="flex items-center h-5">
                                    <!-- <input id="helper-radio" aria-describedby="helper-radio-text" type="radio" value="" class="w-4 h-4 text-blue-600 border border-white dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"> -->
                                    <svg v-if="form1" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect width="20" height="20" rx="10" fill="#EAECF0"/>
                                    </svg>
                                    <svg v-if="form2" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="2.08333" y="2.08333" width="15.8333" height="15.8333" rx="7.91667" stroke="#3B82F6" stroke-width="4.16667"/>
                                    </svg>
                                    <svg v-if="form3" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 2C6.49 2 2 6.49 2 12C2 17.51 6.49 22 12 22C17.51 22 22 17.51 22 12C22 6.49 17.51 2 12 2ZM16.78 9.7L11.11 15.37C10.97 15.51 10.78 15.59 10.58 15.59C10.38 15.59 10.19 15.51 10.05 15.37L7.22 12.54C6.93 12.25 6.93 11.77 7.22 11.48C7.51 11.19 7.99 11.19 8.28 11.48L10.58 13.78L15.72 8.64C16.01 8.35 16.49 8.35 16.78 8.64C17.07 8.93 17.07 9.4 16.78 9.7Z" fill="#12B76A"/>
                                    </svg>
                                </div>
                                 <div class="ms-2 text-sm min-w-32" v-if="form2">
                                    <p  class="font-normal text-gray-400 text-xs">STEP 2</p>
                                    <p id="helper-radio-text" class="text-xs text-gray-700 dark:text-gray-300">Basic information</p>
                                </div> 
                                
                            </div>
                            <div class="flex items-center justify-center w-1/2 ">
                                <span class="border-2 border-t border-gray-300 block flex-grow  h-0 mx-2"></span>
                            </div>

                            <div class="flex items-center">
                                <div class="flex items-center h-5">
                                    <!-- <input id="helper-radio" aria-describedby="helper-radio-text" type="radio" value="" class="w-4 h-4 text-blue-600 border border-white dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"> -->
                                    <svg v-if="form1 || form2" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect width="20" height="20" rx="10" fill="#EAECF0"/>
                                    </svg>
                                    <svg v-if="form3" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="2.08333" y="2.08333" width="15.8333" height="15.8333" rx="7.91667" stroke="#3B82F6" stroke-width="4.16667"/>
                                    </svg>
                                    

                                </div>
                                <div class="ms-2 text-sm min-w-32" v-if="form3">
                                    <p  class="font-normal text-gray-400 text-xs">STEP 3</p>
                                    <p id="helper-radio-text" class="text-sm font-medium text-gray-700 dark:text-gray-300 ">Basic information</p>
                                </div>
                            </div>

                        </div>
                            <form action="{{route('post-login')}}" method="post">
                                <!-- form one: basic information  -->
                               <div v-if="form1">
                                <div class="grid grid-cols-12 gap-4">
                                    <div class=" col-span-6">
                                        <div>
                                            <label for="first_name">First Name</label>
                                            <input type="text" v-model="form.first_name" name="first_name" id="first_name" class="border border-gray-200 rounded-lg py-2 px-4 focus:border-blue-500 focus:ring-blue-500 w-full" placeholder="Enter first name">
                                        </div>
                                    </div>
                                    <div class="col-span-6">
                                        <div>
                                            <label for="first_name">Last Name</label>
                                            <input type="text" v-model="form.last_name" name="last_name" id="last_name" class="border border-gray-200 rounded-lg py-2 px-4 focus:border-blue-500 focus:ring-blue-500 w-full" placeholder="Enter last name">
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-4 mt-4">
                                    <div class="col-span-6">
                                        <div>
                                            <label for="gender">Gender</label>
                                            <select name="gender" v-model="form.gender" id="gender" class=" border border-gray-200 rounded-lg py-2 px-4 w-full focus:border-blue-500 focus:ring-blue-500">
                                                <option value="" class=" text-gray-400">Select from dropdown</option>
                                                <option value="male">Male</option>
                                                <option value="female">Female</option>
                                                <option value="none">Prefer not to say</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-span-6">
                                        <div>
                                            <label for="grade_level">Grade Level</label>
                                            <select name="grade_level" v-model="form.grade_level" id="grade_level" class=" border border-gray-200 rounded-lg py-2 px-4 w-full focus:border-blue-500 focus:ring-blue-500">
                                                <option value="">Select from dropdown</option>
                                                <option value="0">Grade  Level 0 (Data Entry)</option>
                                                <option value="1">Grade  Level 1</option>
                                                <option value="2">Grade  Level 2</option>
                                                <option value="3">Grade  Level 3</option>
                                                <option value="4">Grade  Level 4</option>
                                                <option value="5">Grade  Level 5</option>
                                                <option value="6">Grade  Level 6</option>
                                                <option value="7">Grade  Level 7</option>
                                                <option value="8">Grade  Level 8</option>
                                                <option value="9">Grade  Level 9</option>
                                                <option value="10">Grade Level  10</option>
                                                <option value="11">Grade Level  11</option>
                                                <option value="12">Grade Level  12</option>
                                                <option value="13">Grade Level  13</option>
                                                <option value="14">Grade Level  14</option>
                                                <option value="15">Grade Level  15</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <label for="Email" class=" text-gray-600">Email</label>
                                    <div class="relative mt-1">
                                        <input type="email" @blur="checkMailExist" id="email" v-model="form.email" name="email" class="peer py-3 px-4 ps-11 block w-full bg-white border border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-gray-700 dark:border-transparent dark:text-gray-400 dark:focus:ring-gray-600" placeholder="Enter Email">
                                        <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-4 peer-disabled:opacity-50 peer-disabled:pointer-events-none">
                                            <svg class="flex-shrink-0 w-4 h-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                        </div>
                                    </div>
                                    <span class="text-red-400 text-sm" v-if="email_msg">{{ email_msg }}</span>
                                </div>
                                
                                <div class="mt-4">
                                    <label for="phone_number" class="text-gray-600">Phone Number</label>
                                    <div class="flex items-center">
                                        
                                        <button id="dropdown-phone-button" data-dropdown-toggle="dropdown-phone" class="flex-shrink-0 z-10 inline-flex items-center py-2.5 px-4 text-sm font-medium text-center text-gray-900 bg-gray-100 border-t border-l border-b border-gray-300 rounded-s-lg hover:bg-gray-200 focus:ring-4 focus:outline-none focus:ring-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:focus:ring-gray-700 dark:text-white dark:border-gray-600" type="button">
                                            <svg class="mr-2" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <g clip-path="url(#clip0_1057_22005)">
                                                <rect width="16" height="16" rx="8" fill="white"/>
                                                <rect x="-2.6665" y="-0.888672" width="8" height="17.7778" fill="#008751"/>
                                                <rect x="10.6665" y="-0.888672" width="8" height="17.7778" fill="#008751"/>
                                                </g>
                                                <defs>
                                                <clipPath id="clip0_1057_22005">
                                                <rect width="16" height="16" rx="8" fill="white"/>
                                                </clipPath>
                                                </defs>
                                                </svg>
                                                
                                        +234 <svg class="w-2.5 h-2.5 ms-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/></svg>
                                        </button>
                                        <div id="dropdown-phone" class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-52 dark:bg-gray-700">
                                            <ul class="py-2 text-sm text-gray-700 dark:text-gray-200" aria-labelledby="dropdown-phone-button">
                                                <li>
                                                    <button type="button" class="inline-flex w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600 dark:hover:text-white" role="menuitem">
                                                        <div class="inline-flex items-center">
                                                            <svg fill="none" aria-hidden="true" class="h-4 w-4 me-2" viewBox="0 0 20 15"><rect width="19.6" height="14" y=".5" fill="#fff" rx="2"/><mask id="a" style="mask-type:luminance" width="20" height="15" x="0" y="0" maskUnits="userSpaceOnUse"><rect width="19.6" height="14" y=".5" fill="#fff" rx="2"/></mask><g mask="url(#a)"><path fill="#D02F44" fill-rule="evenodd" d="M19.6.5H0v.933h19.6V.5zm0 1.867H0V3.3h19.6v-.933zM0 4.233h19.6v.934H0v-.934zM19.6 6.1H0v.933h19.6V6.1zM0 7.967h19.6V8.9H0v-.933zm19.6 1.866H0v.934h19.6v-.934zM0 11.7h19.6v.933H0V11.7zm19.6 1.867H0v.933h19.6v-.933z" clip-rule="evenodd"/><path fill="#46467F" d="M0 .5h8.4v6.533H0z"/><g filter="url(#filter0_d_343_121520)"><path fill="url(#paint0_linear_343_121520)" fill-rule="evenodd" d="M1.867 1.9a.467.467 0 11-.934 0 .467.467 0 01.934 0zm1.866 0a.467.467 0 11-.933 0 .467.467 0 01.933 0zm1.4.467a.467.467 0 100-.934.467.467 0 000 .934zM7.467 1.9a.467.467 0 11-.934 0 .467.467 0 01.934 0zM2.333 3.3a.467.467 0 100-.933.467.467 0 000 .933zm2.334-.467a.467.467 0 11-.934 0 .467.467 0 01.934 0zm1.4.467a.467.467 0 100-.933.467.467 0 000 .933zm1.4.467a.467.467 0 11-.934 0 .467.467 0 01.934 0zm-2.334.466a.467.467 0 100-.933.467.467 0 000 .933zm-1.4-.466a.467.467 0 11-.933 0 .467.467 0 01.933 0zM1.4 4.233a.467.467 0 100-.933.467.467 0 000 .933zm1.4.467a.467.467 0 11-.933 0 .467.467 0 01.933 0zm1.4.467a.467.467 0 100-.934.467.467 0 000 .934zM6.533 4.7a.467.467 0 11-.933 0 .467.467 0 01.933 0zM7 6.1a.467.467 0 100-.933.467.467 0 000 .933zm-1.4-.467a.467.467 0 11-.933 0 .467.467 0 01.933 0zM3.267 6.1a.467.467 0 100-.933.467.467 0 000 .933zm-1.4-.467a.467.467 0 11-.934 0 .467.467 0 01.934 0z" clip-rule="evenodd"/></g></g><defs><linearGradient id="paint0_linear_343_121520" x1=".933" x2=".933" y1="1.433" y2="6.1" gradientUnits="userSpaceOnUse"><stop stop-color="#fff"/><stop offset="1" stop-color="#F0F0F0"/></linearGradient><filter id="filter0_d_343_121520" width="6.533" height="5.667" x=".933" y="1.433" color-interpolation-filters="sRGB" filterUnits="userSpaceOnUse"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feColorMatrix in="SourceAlpha" result="hardAlpha" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"/><feOffset dy="1"/><feColorMatrix values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.06 0"/><feBlend in2="BackgroundImageFix" result="effect1_dropShadow_343_121520"/><feBlend in="SourceGraphic" in2="effect1_dropShadow_343_121520" result="shape"/></filter></defs></svg>
                                                            United States (+1)
                                                        </div>
                                                    </button>
                                                </li>
                                                <li>
                                                    <button type="button" class="inline-flex w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600 dark:hover:text-white" role="menuitem">
                                                        <div class="inline-flex items-center">
                                                            <svg class="h-4 w-4 me-2" fill="none" viewBox="0 0 20 15"><rect width="19.6" height="14" y=".5" fill="#fff" rx="2"/><mask id="a" style="mask-type:luminance" width="20" height="15" x="0" y="0" maskUnits="userSpaceOnUse"><rect width="19.6" height="14" y=".5" fill="#fff" rx="2"/></mask><g mask="url(#a)"><path fill="#0A17A7" d="M0 .5h19.6v14H0z"/><path fill="#fff" fill-rule="evenodd" d="M-.898-.842L7.467 4.8V-.433h4.667V4.8l8.364-5.642L21.542.706l-6.614 4.46H19.6v4.667h-4.672l6.614 4.46-1.044 1.549-8.365-5.642v5.233H7.467V10.2l-8.365 5.642-1.043-1.548 6.613-4.46H0V5.166h4.672L-1.941.706-.898-.842z" clip-rule="evenodd"/><path stroke="#DB1F35" stroke-linecap="round" stroke-width=".667" d="M13.067 4.933L21.933-.9M14.009 10.088l7.947 5.357M5.604 4.917L-2.686-.67M6.503 10.024l-9.189 6.093"/><path fill="#E6273E" fill-rule="evenodd" d="M0 8.9h8.4v5.6h2.8V8.9h8.4V6.1h-8.4V.5H8.4v5.6H0v2.8z" clip-rule="evenodd"/></g></svg>
                                                            United Kingdom (+44)
                                                        </div>
                                                    </button>
                                                </li>
                                               
                                            </ul>
                                        </div>
                                         <!-- <label for="phone-input" class="mb-2 text-sm font-medium text-gray-900 sr-only dark:text-white">Phone number:</label>  -->
                                        <div class="relative w-full">
                                            <input type="number" @blur="checkPhoneExist" v-model="form.phone" id="phone" class="block p-2.5 w-full z-20 text-sm text-gray-900 bg-white rounded-e-lg border-s-0 border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-s-gray-700  dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:border-blue-500" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}" placeholder="Enter phone number">
                                        </div>
                                    </div>
                                    <span class="text-red-400 text-sm" v-if="isPhoneExist">{{ phone_msg }}</span>
                                </div>

                                <div class="mt-6">
                                    <button @click.prevent="goToForm2"  class=" bg-blue-500 w-full text-white px-4 py-2 rounded-lg hover:bg-blue-400">Continue</button>
                                </div>
                                <div class="mt-6">
                                    <p class="text-center text-gray-500">Already have an account? <a href="/login" class=" text-blue-500">Login</a></p>
                                </div>
                               </div>

                               <!-- form two: Create password -->
                               <div v-if="form2">
                                    <div class="relative mt-4">
                                        <label for="password" class=" text-gray-600 block">Password </label>
                                        <div class="relative mt-1">
                                            <input type="password" v-model="form.password" name="password" class="peer py-3 px-4 ps-11 block w-full border border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-gray-700 dark:border-transparent dark:text-gray-400 dark:focus:ring-gray-600" :placeholder="'Enter password (Min. of  '+props.passwordpolicy.number_of_characters+'  characters)'">
                                            <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-4 peer-disabled:opacity-50 peer-disabled:pointer-events-none">
                                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M5.83333 9.1665V5.83317C5.83333 4.7281 6.27232 3.66829 7.05372 2.88689C7.83512 2.10549 8.89493 1.6665 10 1.6665C11.1051 1.6665 12.1649 2.10549 12.9463 2.88689C13.7277 3.66829 14.1667 4.7281 14.1667 5.83317V9.1665M4.16667 9.1665H15.8333C16.7538 9.1665 17.5 9.9127 17.5 10.8332V16.6665C17.5 17.587 16.7538 18.3332 15.8333 18.3332H4.16667C3.24619 18.3332 2.5 17.587 2.5 16.6665V10.8332C2.5 9.9127 3.24619 9.1665 4.16667 9.1665Z" stroke="#667085" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="mt-1 " v-if="form.password.trim() != ''">
                                            <span :class="!eightCharacters?'border-red-400 bg-red-50':'border-green-400 bg-green-50'" class="py-2 px-3 border border-dashed   my-2 inline-block rounded-3xl text-sm text-gray-500 mx-1">{{ props.passwordpolicy.number_of_characters }} characters minimum</span>
                                            <span v-if="passwordpolicy.uppercase" :class="!uppercase?'border-red-400 bg-red-50':'border-green-400 bg-green-50'" class="py-2 px-3 border border-dashed  my-2 inline-block rounded-3xl text-sm text-gray-500 mx-1">An Uppercase letter</span>
                                            <span v-if="passwordpolicy.lowercase" :class="!lowercase?'border-red-400 bg-red-50':'border-green-400 bg-green-50'" class="py-2 px-3 border border-dashed  my-2 inline-block rounded-3xl text-sm text-gray-500 mx-1">A Lowercase letter</span>
                                            <span v-if="passwordpolicy.special_character" :class="!specialCharacters?'border-red-400 bg-red-50':'border-green-400 bg-green-50'" class="py-2 px-3 border border-dashed  my-2 inline-block rounded-3xl text-sm text-gray-500 mx-1">A special character</span>
                                            <span v-if="passwordpolicy.number" :class="!number?'border-red-400 bg-red-50':'border-green-400 bg-green-50'" class="py-2 px-3 border border-dashed  my-2 inline-block rounded-3xl text-sm text-gray-500 mx-1">A number</span>
                                        </div>
                                    </div>
                                    <div class="relative mt-4" v-if="isPasswordValid">
                                        <label for="password" class=" text-gray-600 block">Password Confirmation
                                        
                                        </label>
                                        <div class="relative mt-1">
                                            <input type="password" @keydown="!isPasswordMisMatch" v-model="form.password_confirmation" name="password_confirmation" class="peer py-3 px-4 ps-11 block w-full border border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-gray-700 dark:border-transparent dark:text-gray-400 dark:focus:ring-gray-600" placeholder="Confirm password">
                                            <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-4 peer-disabled:opacity-50 peer-disabled:pointer-events-none">
                                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M5.83333 9.1665V5.83317C5.83333 4.7281 6.27232 3.66829 7.05372 2.88689C7.83512 2.10549 8.89493 1.6665 10 1.6665C11.1051 1.6665 12.1649 2.10549 12.9463 2.88689C13.7277 3.66829 14.1667 4.7281 14.1667 5.83317V9.1665M4.16667 9.1665H15.8333C16.7538 9.1665 17.5 9.9127 17.5 10.8332V16.6665C17.5 17.587 16.7538 18.3332 15.8333 18.3332H4.16667C3.24619 18.3332 2.5 17.587 2.5 16.6665V10.8332C2.5 9.9127 3.24619 9.1665 4.16667 9.1665Z" stroke="#667085" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <span class="text-red-500 my-1 text-sm" v-if="!passwordMismatch">Password mismatch</span>
                                    </div>
                                    <div class="mt-6">
                                        <button @click.prevent="submitForm" :disabled="!isPasswordMisMatch || !isPasswordValid||form.processing" class=" bg-blue-500 w-full text-white px-4 py-2 rounded-lg hover:bg-blue-400 disabled:bg-blue-200">Continue</button>
                                    </div>
                                    <div class="mt-6">
                                        <p class="text-center text-gray-500">By clicking continue, you accept our <a href="#" class="text-blue-500">Terms of use</a> and <a href="#" class="text-blue-500">Privacy policy</a> </p>
                                    </div>
                               </div>

                               <!-- Form three: Email verification -->

                               <div v-if="form3">
                                <div class="flex items-center">
                                    <input type="text" v-model="emailForm.code1" name="" id="" class=" border-gray-300 border-2 w-14 text-3xl text-center p-2 rounded-lg text-gray-300 focus:border-blue-500 focus:ring-blue-400 mx-3" placeholder="0">

                                    <input type="text" v-model="emailForm.code2" name="" id="" class=" border-gray-300 border-2 w-14 text-3xl text-center p-2 rounded-lg text-gray-300 focus:border-blue-500 focus:ring-blue-400 mx-3" placeholder="0">

                                    <input type="text" v-model="emailForm.code3" name="" id="" class=" border-gray-300 border-2 w-14 text-3xl text-center p-2 rounded-lg text-gray-300 focus:border-blue-500 focus:ring-blue-400 mx-3" placeholder="0">

                                    <span class="border-t border-2 border-gray-300 block w-16 h-0"></span>

                                    <input type="text" v-model="emailForm.code4" name="" id="" class=" border-gray-300 border-2 w-14 text-3xl text-center p-2 rounded-lg text-gray-300 focus:border-blue-500 focus:ring-blue-400 mx-3" placeholder="0">

                                    <input type="text" v-model="emailForm.code5" name="" id="" class=" border-gray-300 border-2 w-14 text-3xl text-center p-2 rounded-lg text-gray-300 focus:border-blue-500 focus:ring-blue-400 mx-3" placeholder="0">

                                    <input type="text" v-model="emailForm.code6" name="" id="" class=" border-gray-300 border-2 w-14 text-3xl text-center p-2 rounded-lg text-gray-300 focus:border-blue-500 focus:ring-blue-400 mx-3" placeholder="0">
                                </div>
                                <div class="mt-10">
                                    <button @click.prevent="verifyEmail" class=" bg-blue-500 w-full text-white px-4 py-2 rounded-lg hover:bg-blue-400">Verify your account</button>
                                </div>
                                <div class="mt-6 text-center">
                                    <a href="#" class="text-blue-500" @click.prevent="resendVerificationCode">
                                        <svg class="inline" width="13" height="13" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M0.164464 0.649023C0.164464 0.290038 0.455479 -0.000976562 0.814464 -0.000976562C1.17345 -0.000976562 1.46446 0.290038 1.46446 0.649023V2.86184C2.03379 2.14682 2.75756 1.56137 3.5885 1.15341C4.85018 0.533965 6.28451 0.360255 7.65758 0.660603C9.03066 0.96095 10.2615 1.71764 11.1493 2.80726C12.0371 3.89689 12.5296 5.25517 12.5464 6.66061C12.5632 8.06605 12.1034 9.43572 11.2419 10.5463C10.3804 11.6568 9.168 12.4427 7.80251 12.7758C6.43701 13.109 4.99894 12.9696 3.72281 12.3805C2.44667 11.7914 1.40776 10.7874 0.775497 9.53208C0.614013 9.21146 0.743011 8.82065 1.06363 8.65916C1.38424 8.49768 1.77506 8.62668 1.93654 8.94729C2.43662 9.94015 3.25832 10.7343 4.26766 11.2002C5.27699 11.6661 6.4144 11.7763 7.49441 11.5129C8.57443 11.2494 9.53332 10.6278 10.2147 9.74945C10.8961 8.87108 11.2598 7.78776 11.2465 6.67616C11.2332 5.56456 10.8437 4.49025 10.1415 3.62843C9.43928 2.76661 8.46579 2.16813 7.37979 1.93057C6.29378 1.69302 5.15933 1.83041 4.16143 2.32035C3.31839 2.73425 2.61482 3.37915 2.12964 4.17503H4.52647C4.88545 4.17503 5.17647 4.46604 5.17647 4.82503C5.17647 5.18401 4.88545 5.47503 4.52647 5.47503H0.814464C0.455479 5.47503 0.164464 5.18401 0.164464 4.82503V0.649023Z" fill="#3575DD"/>
                                            </svg>
                                            
                                        Resend Code
                                    </a>
                                </div>
                               </div>
                               
                            </form>
                            
                        </div>
                    </div>
                </div>

                <!-- form success -->
                <div class="mt-32" v-if="formSuccess">
                    <div class=" flex items-center justify-center">
                        <div>
                            <svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="0.5" y="0.5" width="63" height="63" rx="14.0455" fill="white"/>
                                <rect x="0.5" y="0.5" width="63" height="63" rx="14.0455" stroke="#EAECF0"/>
                                <path d="M43.6365 23.2725L27.6365 39.2725L20.3638 31.9997" stroke="#475467" stroke-width="2.90909" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-10 flex items-center justify-center">
                        <svg width="392" height="60" viewBox="0 0 392 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="0.5" y="0.5" width="391" height="59" rx="9.5" fill="#FCFCFD"/>
                            <rect x="0.5" y="0.5" width="391" height="59" rx="9.5" stroke="#EAECF0"/>
                            <path d="M52 20C46.49 20 42 24.49 42 30C42 35.51 46.49 40 52 40C57.51 40 62 35.51 62 30C62 24.49 57.51 20 52 20ZM56.78 27.7L51.11 33.37C50.97 33.51 50.78 33.59 50.58 33.59C50.38 33.59 50.19 33.51 50.05 33.37L47.22 30.54C46.93 30.25 46.93 29.77 47.22 29.48C47.51 29.19 47.99 29.19 48.28 29.48L50.58 31.78L55.72 26.64C56.01 26.35 56.49 26.35 56.78 26.64C57.07 26.93 57.07 27.4 56.78 27.7Z" fill="#12B76A"/>
                            <path d="M88 30H160" stroke="#D0D5DD" stroke-width="2" stroke-linecap="round"/>
                            <path d="M196 20C190.49 20 186 24.49 186 30C186 35.51 190.49 40 196 40C201.51 40 206 35.51 206 30C206 24.49 201.51 20 196 20ZM200.78 27.7L195.11 33.37C194.97 33.51 194.78 33.59 194.58 33.59C194.38 33.59 194.19 33.51 194.05 33.37L191.22 30.54C190.93 30.25 190.93 29.77 191.22 29.48C191.51 29.19 191.99 29.19 192.28 29.48L194.58 31.78L199.72 26.64C200.01 26.35 200.49 26.35 200.78 26.64C201.07 26.93 201.07 27.4 200.78 27.7Z" fill="#12B76A"/>
                            <path d="M232 30H304" stroke="#D0D5DD" stroke-width="2" stroke-linecap="round"/>
                            <path d="M340 20C334.49 20 330 24.49 330 30C330 35.51 334.49 40 340 40C345.51 40 350 35.51 350 30C350 24.49 345.51 20 340 20ZM344.78 27.7L339.11 33.37C338.97 33.51 338.78 33.59 338.58 33.59C338.38 33.59 338.19 33.51 338.05 33.37L335.22 30.54C334.93 30.25 334.93 29.77 335.22 29.48C335.51 29.19 335.99 29.19 336.28 29.48L338.58 31.78L343.72 26.64C344.01 26.35 344.49 26.35 344.78 26.64C345.07 26.93 345.07 27.4 344.78 27.7Z" fill="#12B76A"/>
                            </svg>
                            
                    </div>
                    <div class="mt-6 text-center">
                        <span class=" text-3xl font-semibold">Profile Created</span>
                    </div>
                    <div class="mt-10 grid w-1/2 mx-auto px-5 py-3 bg-orange-50 border-2 border-dashed border-orange-200 rounded-lg">
                        <span class="text-center">Your account is under verification, you would receive a response email when your account has been approved.</span>
                    </div>
                </div>
    </div>
</template>

<script setup>
    import { ref, watch, computed, defineProps } from 'vue'
    import { useForm } from '@inertiajs/vue3'
    import { useToast } from "primevue/usetoast";
    import axios from 'axios';

    const form1 = ref(true);
    const form2 = ref(false);
    const form3 = ref(false);
    const formSuccess = ref(false);
    const toast = useToast();
    const props = defineProps(['passwordpolicy'])

    const eightCharacters = ref(false);
    const uppercase = ref(false);
    const lowercase = ref(false);
    const specialCharacters = ref(false);
    const number = ref(false);
    const isPasswordValid = ref(false)
    const passwordMismatch = ref(true)
    const email_msg = ref(null);
    const phone_msg = ref('phone number exist');
    const isEmailExist = ref(false)
    const isPhoneExist = ref(false)

    const form = useForm({
        first_name:'',
        last_name:'',
        gender:'',
        grade_level:'',
        email:'',
        phone:'',
        password:'',
        password_confirmation: ''
    })

    const emailForm = useForm({
        code1:'',
        code2:'',
        code3:'',
        code4:'',
        code5:'',
        code6:''
    })

    const goToForm2 = ()=>{
        checkMailExist()
        checkPhoneExist()

        if(form.first_name.trim() == ''){
        document.getElementById('first_name').focus();
        return false;
       }
       if(form.last_name.trim() == ''){
        document.getElementById('last_name').focus();
        return false;
       }
       if(form.gender.trim() == ''){
        document.getElementById('gender').focus();
        return false;
       }
       if(form.grade_level.trim() == ''){
        document.getElementById('grade_level').focus();
        return false;
       }
       if(form.email.trim() == ''){
        document.getElementById('email').focus();
        return false;
       }
       if(!validateEmail(form.email)){
        document.getElementById('email').focus();
        return false
       }
       if(form.phone == ''){
        document.getElementById('phone').focus();
        return false;
       }
       if(!validatePhoneNumber(form.phone)){
        document.getElementById('phone').focus();
        return false;
       }
       if(isEmailExist.value){
        document.getElementById('email').focus();
        return false;
       }
       if(isPhoneExist.value){
        document.getElementById('phone').focus();
        return false;
       }
        form1.value = false;
        form2.value = true;
    }

    const validateEmail = (email) => {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if(emailRegex.test(email)){
        return true
      }else{
        return false;
      }
    }

    // const isPasswordConfirm = () =>{
    //     let isMisMatch = form.password == form.password_confirmation? true: false;
    //     passwordMismatch.value = isMisMatch
    // }

    const validatePhoneNumber = (phone) => {
      const phoneRegex = /^\d{10}$/;
        if(phoneRegex.test(phone)){
            return true
        }else{
            return false;
        }
    };

    const validatePassword = () => {
      // Regular expressions for password validation
      const uppercaseRegex = /[A-Z]/;
      const lowercaseRegex = /[a-z]/;
      const specialCharRegex = /[!@#$%^&*(),.?":{}|<>]/;
      const numberRegex = /\d/;

      
      // Check if password meets the criteria
    //   let isValid = (
    //     uppercaseRegex.test(form.password) &&
    //     lowercaseRegex.test(form.password) &&
    //     specialCharRegex.test(form.password) &&
    //     numberRegex.test(form.password) &&
    //     form.password.length >= props.passwordpolicy.number_of_characters 
    //   );

        let checkUppercase = props.passwordpolicy.uppercase;   
        let checkLowercase = props.passwordpolicy.lowercase;   
        let checkSpecialChar = props.passwordpolicy.special_character; 
        let checkNumber = props.passwordpolicy.number;  
        
        let isValid = true;

        // Apply validation checks based on the flags
        if (checkUppercase) {
        isValid = isValid && uppercaseRegex.test(form.password);
        }

        if (checkLowercase) {
        isValid = isValid && lowercaseRegex.test(form.password);
        }

        if (checkSpecialChar) {
        isValid = isValid && specialCharRegex.test(form.password);
        }

        if (checkNumber) {
        isValid = isValid && numberRegex.test(form.password);
        }

        // Length validation
        isValid = isValid && form.password.length >= props.passwordpolicy.number_of_characters;

     
      isPasswordValid.value = isValid
    };

    const submitForm = ()=>{
        form.post('/register',{onSuccess:()=>{
            form1.value = false
            form2.value = false
            //.value = true
            formSuccess.value = true
        }})
    }

    const verifyEmail = ()=>{
        axios.post('/email-verification',emailForm,{onSuccess: (data)=>{
                if(data.success){
                    form1.value = false
                    form2.value = false
                    form3.value = false
                    formSuccess.value = true
                }else{
                    toast.add({
                        severity: "error",
                        summary: "Wrong code",
                        detail: "Please check the code again",
                        life: 3000,
                    });
                }

        }})
    }

    const isPasswordMisMatch = computed(()=>{
        let isMisMatch = form.password == form.password_confirmation? true: false;
       return isMisMatch
    })

    const resendVerificationCode = ()=>{
        axios.get('/resend-verification-code').then((data)=>{
            if(data.success){
                toast.add({
                    severity: "success",
                    summary: "Code sent",
                    detail: "You've successfully generated a new verification code.",
                    life: 3000,
                });
            }
        })
    }

    //check if email exist
    const checkMailExist = ()=>{
        axios.get('/email-exist',{params:{email:form.email}}).then((response)=>{
         
            if(response.data.error){
                email_msg.value = response.data.error_msg
                isEmailExist.value = true;
            }
            if(response.data.success){
                email_msg.value = null;
                isEmailExist.value = false;
            }
        })
    }

    //check if phone number exist
    const checkPhoneExist = ()=>{
        axios.get('/phone-exist', {params:{phone: form.phone}}).then((response)=>{
            if(response.data.error){
                phone_msg.value = response.data.error_msg
                isPhoneExist.value = true;
            }
             if(response.data.success){
                phone_msg.value = null;
                isPhoneExist.value = false;
            }
        })
    }
    
    watch(() => form.password, ()=>{
         // Regular expressions for password validation
      const uppercaseRegex = /[A-Z]/;
      const lowercaseRegex = /[a-z]/;
      const specialCharRegex = /[!@#$%^&*(),.?":{}|<>]/;
      const numberRegex = /\d/;

      if(uppercaseRegex.test(form.password)){
        uppercase.value = true
      }else{
        uppercase.value = false
      }
      if(lowercaseRegex.test(form.password)){
        lowercase.value = true
      }else{
        lowercase.value = false
      }
      if(specialCharRegex.test(form.password)){
        specialCharacters.value = true
      }else{
        specialCharacters.value = false
      }
      if(numberRegex.test(form.password)){
        number.value = true
      }else{
        number.value = false
      }
      if(form.password.length >= props.passwordpolicy.number_of_characters){
        eightCharacters.value = true
      }else{
        eightCharacters.value = false
      }
      validatePassword()
    })

    watch(()=>form.password_confirmation, ()=>{
        passwordMismatch.value = form.password == form.password_confirmation? true: false;
    })

</script>