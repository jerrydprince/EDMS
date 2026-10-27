<template>

    <div class=" block md:flex items-center justify-between px-7 py-8">
        <div>
            <h1 class="text-3xl text-[#1D2939] font-semibold">File Registry</h1>
            <h2>{{ department?.title }}</h2>
            <p class=" opacity-80 pt-3">Efficiently monitor and manage branch document movement.</p>
        </div>

        <div class="flex items-center gap-4 mt-5 md:mt-0">
            <!-- <button v-if="isPermissions('agf registry view') || user_roles.includes('super admin')" @click="openScan = true"
                class="flex gap-2 items-center font-semibold text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-4">
                <Icon icon="tabler:line-scan" />
                <p>Scan Files</p>
            </button> -->
            <button @click="TrackPackageModel = true"
                class="flex gap-2 items-center font-medium text-gray-700 bg-white shadow-sm border border-gray-200 rounded-full py-2 px-5 hover:bg-gray-50 hover:-translate-y-0.5 transition-all">
                <p>Track File</p>
            </button>

            <button 
                v-if="can('registry create') || hasRole('super admin')"
                class="flex gap-2 items-center text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-full py-2 px-5 shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all"
                @click="createEntry = true">
                <Icon icon="material-symbols:add" width="20" />
                <p>Create Entry</p>
            </button>
        </div>
    </div>

    <Divider class="" />
    <div class="flex justify-between items-center gap-4 px-7 py-2">
        <div class="bg-white border border-gray-100 shadow-sm flex items-center rounded-full p-1.5 gap-1">
            <button class="flex gap-1.5 items-center rounded-full py-1.5 px-5 whitespace-nowrap transition-all duration-300 font-medium cursor-pointer"
                @click="activeButton('incoming')" :class="buttonToggle === 1 ? 'bg-blue-50 text-blue-600 shadow-sm font-bold' : 'text-gray-500 hover:bg-gray-50'">
                Incoming Files
            </button>
            <button class="flex gap-1.5 items-center rounded-full py-1.5 px-5 whitespace-nowrap transition-all duration-300 font-medium cursor-pointer"
                @click="activeButton('outgoing')" :class="buttonToggle === 2 ? 'bg-blue-50 text-blue-600 shadow-sm font-bold' : 'text-gray-500 hover:bg-gray-50'">
                Outgoing Files
            </button>
            <button class="flex gap-1.5 items-center rounded-full py-1.5 px-5 whitespace-nowrap transition-all duration-300 font-medium cursor-pointer"
                @click="activeButton('correspondence')" :class="buttonToggle === 3 ? 'bg-indigo-50 text-indigo-700 shadow-sm font-bold' : 'text-gray-500 hover:bg-gray-50'">
                <Icon icon="lucide:mail-open" width="16" />
                <span>Correspondence & Memos</span>
            </button>
        </div>
        <button class="flex gap-2 items-center text-gray-700 bg-white shadow-sm border border-gray-200 rounded-full py-2 px-5 hover:bg-gray-50 hover:-translate-y-0.5 transition-all">
            <Icon icon="raphael:barchart" width="20" />
            <p>Generate Report</p>
        </button>
    </div>
    <Divider class="-mt-2" />
    <div class="px-7">
        <!-- incoming  -->
        <div class=" mb-52" v-if="clickedButton == 'incoming' && incoming && incoming.data">

            <DataTable v-model:expandedRows="expandedRows" :value="incoming.data" dataKey="id"
                @rowExpand="onRowExpand" @rowCollapse="onRowCollapse" tableStyle="min-width: 60rem" :pt="{
                root: {
                    class: 'bg-white/80 backdrop-blur border border-gray-100 shadow-sm rounded-xl pb-10 overflow-hidden relative'
                },
                paginator: {
                    class: 'pt-10'
                }
            }">
                <Column field="name" header="File name"></Column>
                <Column field="doc_no" header="Number"> </Column>
                <Column field="created_at" header="Date">
                    <template #body="slotProps">
                        <div>
                            {{ formatDate(slotProps.data.created_at) }}
                        </div>
                    </template>
                </Column>
                <!-- <Column field="Source" header="Source"> </Column> -->
                <Column header="Physical Status">
                    <template #body="slotProps">
                        <div class="flex items-center w-fit py-1.5 px-2 rounded-2xl text-xs" :class="{
                'bg-green-400/10': slotProps.data.status === 'delivered',
                'bg-yellow-400/10': slotProps.data.status === 'pending',
                'bg-red-400/10': slotProps.data.status === 'rejected'
            }" :style="{
                color:
                    slotProps.data.status === 'delivered' || slotProps.data.status === 'forwarded'
                        ? '#12B76A'
                        : slotProps.data.status === 'pending'
                            ? '#DC6803'
                            : slotProps.data.status === 'rejected'
                                ? '#F04438'
                                : 'black',
                borderColor:
                    slotProps.data.status === 'delivered' || slotProps.data.status === 'forwarded'
                        ? '#12B76A'
                        : slotProps.data.status === 'pending'
                            ? '#DC6803'
                            : slotProps.data.status === 'rejected'
                                ? '#F04438'
                                : 'black',
                borderWidth: '1px',
                borderStyle: 'solid',
            }">
                            <Icon icon="fluent-mdl2:radio-bullet" />
                            <p>{{ slotProps.data.status }}</p>
                        </div>
                    </template>
                </Column>
                <Column header="Workflow State">
                    <template #body="slotProps">
                        <div v-if="slotProps.data.task">
                            <span class="text-xs font-semibold px-2 py-1 bg-indigo-100 text-indigo-800 rounded-md">
                                {{ slotProps.data.task.active_steps && slotProps.data.task.active_steps.length ? slotProps.data.task.active_steps[0].step.name : (slotProps.data.task.status === 'completed' ? 'Completed' : 'Pending') }}
                            </span>
                        </div>
                        <div v-else>
                            <span class="text-xs font-semibold px-2 py-1 bg-gray-100 text-gray-500 rounded-md">
                                N/A
                            </span>
                        </div>
                    </template>
                </Column>
                <Column expander header="Actions" :pt="{
                rowToggler: {
                    class: ' border border-gray-300 px-2.5 py-1.5 rounded-md',
                },
            }">
                </Column>
                <template #expansion="slotProps">
                    <div class=" p-5">
                        <div v-if="checkData" class=" gap-5 ">
                            <!-- <div v-if="slotProps.data.status !== 'rejected' && slotProps.data.status !== 'delivered' && slotProps.data.is_forwarded == 0"
                                class="flex items-center gap-5"> -->
                            <div v-if="slotProps.data.status !== 'rejected' && slotProps.data.status !== 'delivered' "
                                class="flex items-center gap-3 mb-5">
                                <button @click="rejectFileHandler(slotProps.data.id)"
                                    class="flex gap-2 items-center font-medium text-red-600 bg-red-50 hover:bg-red-100 border-red-200 border rounded-full py-1.5 px-4 transition-all">
                                    <Icon icon="iconoir:cancel" />
                                    <p class=" text-sm ">Reject</p>
                                </button>

                                <button @click="acceptFileHandler(slotProps.data)"
                                    class="flex gap-2 items-center font-medium text-green-700 bg-green-50 hover:bg-green-100 border-green-200 border rounded-full py-1.5 px-4 transition-all">
                                    <Icon icon="charm:tick" />
                                    <p class=" text-sm ">Accept</p>
                                </button>

                                <button class="flex gap-2 items-center font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 border-blue-200 border rounded-full py-1.5 px-4 transition-all" @click="forwardFileHandler(slotProps.data)">
                                    <Icon icon="charm:move" />
                                    <p class=" text-sm ">Forward</p>
                                </button>
                            </div>
                            <div class="flex items-center">
                                <div class="w-3 rounded-bl-md border border-r-0 border-t-0 h-14 border-gray-300"></div>
                            <div>
                                <p class="font-normal">Source</p>
                                <div class="mt-5 flex items-center gap-2">
                                    <div class="p-3 rounded-full border w-fit">
                                        <!-- <img :src="slotProps.data.icon" alt=""
                                            v-if="getDestinationBranch(slotProps.data)"> -->
                                        <img :src="page.props.base + '/images/unit.svg'" alt="" width="20"
                                            v-if="getSourceBranchType(slotProps.data) == 'unit'">
                                        <img :src="page.props.base + '/images/division.svg'" alt="" width="20"
                                            v-if="getSourceBranchType(slotProps.data) == 'division'">
                                        <img :src="page.props.base + '/images/department.svg'" alt="" width="20"
                                            v-if="getSourceBranchType(slotProps.data) == 'department'">
                                        <img :src="page.props.base + '/images/department.svg'" alt="" width="20"
                                            v-if="getSourceBranchType(slotProps.data) == 'agf'">
                                        <!-- <Icon icon="fa-solid:question" v-else /> -->
                                    </div>
                                    <div>
                                        <h1 class="font-medium" v-if="getSourceBranch(slotProps.data)">{{
                                        getSourceBranch(slotProps.data) }}</h1>
                                        <h1 class="font-medium" v-else>Not set yet </h1>
                                    </div>
                                </div>
                            </div>
                            <!-- <button v-if="!slotProps.data.is_forwarded" @click="forwardFileHandler(slotProps.data)"
                                class="flex gap-2 h-8 items-center font-semibold text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-3">
                                <Icon icon="charm:move" />
                                <p class=" text-xs ">Forward File</p>
                            </button> -->
                            </div>
                            
                        </div>
                        <!-- <div v-if="checkDest">
                            <button class="flex gap-2 items-center border ml-4  rounded-lg py-2 px-4 absolute bottom-5"
                        @click="destination(slotProps.data.id)">
                        <Icon icon="gridicons:add-outline" width="20" />
                        <p>Add New Destination</p>
                    </button>
                        </div> -->
                        <!-- <div class="flex items-center" v-if='hide'>
                            <div class="w-3 rounded-bl-md border border-r-0 border-t-0 h-14 border-gray-300"></div>
                            <div>
                                <p class="font-normal">Destination</p>
                                <div class="mt-5 flex items-center gap-2">
                                    <div class="p-3 rounded-full border w-fit">
                                        <img :src="slotProps.data.icon" alt=""
                                            v-if="slotProps.data.sent_to_department_id">
                                        <Icon icon="fa-solid:question" v-else />
                                    </div>
                                    <div>
                                        <h1 class="font-medium" v-if="slotProps.data.sent_to_department_id">{{
                slotProps.data.sent_to_department_id }}</h1>
                                        <h1 class="font-medium" v-else>Not set yet</h1>
                                    </div>
                                </div>
                            </div>
                        </div> -->
                        <div v-if="slotProps.data.description">
                            <h1 class=" mt-2">Description</h1>
                            <p class=" mt-2 p-2 bg-gray-50 border border-gray-300 rounded-md">{{
                slotProps.data.description
            }}</p>
                        </div>
                    </div>
                </template>

            </DataTable>
        </div>

        <!-- outgoing -->
        <div class=" mb-52" v-if="clickedButton == 'outgoing' && outgoing && outgoing.data">

            <DataTable v-model:expandedRows="expandedRows" :value="outgoing.data" dataKey="id"
                @rowExpand="onRowExpand" @rowCollapse="onRowCollapse" tableStyle="min-width: 60rem" :pt="{
                root: {
                    class: 'bg-white/80 backdrop-blur border border-gray-100 shadow-sm rounded-xl pb-10 overflow-hidden relative'
                },
                paginator: {
                    class: 'pt-10'
                }
            }">
                <Column field="name" header="File name"></Column>
                <Column field="doc_no" header="Number"> </Column>
                <Column field="created_at" header="Date">
                    <template #body="slotProps">
                        <div>
                            {{ formatDate(slotProps.data.created_at) }}
                        </div>
                    </template>
                </Column>
                <!-- <Column field="Source" header="Source"> </Column> -->
                <Column header="Status">
                    <template #body="slotProps">
                        <div class="flex items-center w-fit py-1.5 px-2 rounded-2xl text-xs" :class="{
                'bg-green-400/10': slotProps.data.status === 'delivered',
                'bg-yellow-400/10': slotProps.data.status === 'pending',
                'bg-red-400/10': slotProps.data.status === 'rejected'
            }" :style="{
                color:
                    slotProps.data.status === 'delivered'
                        ? '#12B76A'
                        : slotProps.data.status === 'pending'
                            ? '#DC6803'
                            : slotProps.data.status === 'rejected'
                                ? '#F04438'
                                : 'black',
                borderColor:
                    slotProps.data.status === 'delivered'
                        ? '#12B76A'
                        : slotProps.data.status === 'pending'
                            ? '#DC6803'
                            : slotProps.data.status === 'rejected'
                                ? '#F04438'
                                : 'black',
                borderWidth: '1px',
                borderStyle: 'solid',
            }">
                            <Icon icon="fluent-mdl2:radio-bullet" />
                            <p>{{ slotProps.data.status }}</p>
                        </div>
                    </template>
                </Column>
                <Column expander header="Actions" :pt="{
                rowToggler: {
                    class: ' border border-gray-300 px-2.5 py-1.5 rounded-md',
                },
            }">
                </Column>
                <template #expansion="slotProps">
                    <div class=" p-5">
                        <div v-if="checkData" class="flex gap-5 ">
                            <!-- <div v-if="slotProps.data.status !== 'rejected' && slotProps.data.status !== 'delivered'"
                                class="flex items-center gap-5">
                                <button @click="rejectFileHandler(slotProps.data.id)"
                                    class="flex gap-2 items-center font-semibold text-red-700 border-red-300 border-solid border rounded-lg py-2 px-3">
                                    <Icon icon="iconoir:cancel" />
                                    <p class=" text-xs ">Reject File</p>
                                </button>

                                <button @click="acceptFileHandler(slotProps.data.id)"
                                    class="flex gap-2 items-center font-semibold text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-3">
                                    <Icon icon="charm:tick" />
                                    <p class=" text-xs ">Accept File</p>
                                </button>

                            </div> -->
                            <div class="flex items-center" >
                                <div class="w-3 rounded-bl-md border border-r-0 border-t-0 h-14 border-gray-300"></div>
                            <div>
                                <p class="font-normal">Destination</p>
                                <div class="mt-5 flex items-center gap-2">
                                    <div class="p-3 rounded-full border w-fit">
                                        <!-- <img :src="slotProps.data.icon" alt=""
                                            v-if="getDestinationBranch(slotProps.data)"> -->

                                        <img :src="page.props.base + '/images/unit.svg'" alt="" width="20"
                                            v-if="getDestinationBranchType(slotProps.data) == 'unit'">
                                        <img :src="page.props.base + '/images/division.svg'" alt="" width="20"
                                            v-if="getDestinationBranchType(slotProps.data) == 'division'">
                                        <img :src="page.props.base + '/images/department.svg'" alt="" width="20"
                                            v-if="getDestinationBranchType(slotProps.data) == 'department'">
                                        <img :src="page.props.base + '/images/department.svg'" alt="" width="20"
                                            v-if="getDestinationBranchType(slotProps.data) == 'agf'">
                                        <!-- <Icon icon="fa-solid:question" v-else /> -->
                                    </div>
                                    <div>
                                        <h1 class="font-medium" v-if="getDestinationBranch(slotProps.data)">{{
                                    getDestinationBranch(slotProps.data) }}</h1>
                                        <h1 class="font-medium" v-else>Not set yet </h1>
                                    </div>
                                </div>
                            </div>
                            </div>
                            
                            <button @click="openEditModal(slotProps.data)" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-primary/90 text-sm font-medium flex items-center gap-2 h-10 ml-5 mt-5">
                                <Icon icon="lucide:edit" width="16" /> Edit Entry
                            </button>
                            <!-- <button v-if="!slotProps.data.is_forwarded" @click="forwardFileHandler(slotProps.data)"
                                class="flex gap-2 h-8 items-center font-semibold text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-3">
                                <Icon icon="charm:move" />
                                <p class=" text-xs ">Forward File</p>
                            </button> -->
                        </div>
                        <!-- <div v-if="checkDest">
                <button class="flex gap-2 items-center border ml-4  rounded-lg py-2 px-4 absolute bottom-5"
            @click="destination(slotProps.data.id)">
            <Icon icon="gridicons:add-outline" width="20" />
            <p>Add New Destination</p>
        </button>
            </div> -->
                        <!-- <div class="flex items-center" v-if='hide'>
                            <div class="w-3 rounded-bl-md border border-r-0 border-t-0 h-14 border-gray-300"></div>
                            <div>
                                <p class="font-normal">Destination</p>
                                <div class="mt-5 flex items-center gap-2">
                                    <div class="p-3 rounded-full border w-fit">
                                        <img :src="slotProps.data.icon" alt=""
                                            v-if="slotProps.data.sent_to_department_id">
                                        <Icon icon="fa-solid:question" v-else />
                                    </div>
                                    <div>
                                        <h1 class="font-medium" v-if="slotProps.data.sent_to_department_id">{{
                                    slotProps.data.sent_to_department_id }}</h1>
                                        <h1 class="font-medium" v-else>Not set yet</h1>
                                    </div>
                                </div>
                            </div>
                        </div> -->
                        <div v-if="slotProps.data.subject">
                            <h1 class=" mt-2">Subject Matter</h1>
                            <p class=" mt-2 p-2 bg-gray-50 border border-gray-300 rounded-md">{{ slotProps.data.subject
                                }}</p>
                        </div>
                    </div>
                </template>

            </DataTable>
        </div>

        <!-- Correspondence & Memos (Option 1 Clean Solution) -->
        <div class="mb-52" v-if="clickedButton == 'correspondence'">
            <div class="bg-white border border-gray-100 shadow-sm rounded-2xl p-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-gray-100">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">External Correspondence & Inbound Letters</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Manage external letters, official memos, and inbound communications received into the registry.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="relative w-72">
                            <Icon icon="ph:magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" width="18" />
                            <input 
                                type="text" 
                                placeholder="Search letters, ref no..." 
                                v-model="correspondenceSearch" 
                                @input="searchCorrespondences" 
                                class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-gray-200 rounded-xl text-xs outline-none focus:ring-2 focus:ring-blue-500/20 focus:bg-white transition-all"
                            />
                        </div>
                    </div>
                </div>

                <div v-if="correspondenceList.length > 0" class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-slate-50/80 text-gray-500 text-xs uppercase font-bold tracking-wider border-b border-gray-200">
                            <tr>
                                <th class="py-3.5 px-4">Date</th>
                                <th class="py-3.5 px-4">Subject / Title</th>
                                <th class="py-3.5 px-4">Doc / Letter No</th>
                                <th class="py-3.5 px-4">Description / Summary</th>
                                <th class="py-3.5 px-4">Sender / Logged By</th>
                                <th class="py-3.5 px-4">Attachments</th>
                                <th class="py-3.5 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="c in correspondenceList" :key="c.id" class="hover:bg-indigo-50/20 transition-colors">
                                <td class="py-3.5 px-4 whitespace-nowrap text-xs font-mono text-gray-500">
                                    {{ formatDate(c.created_at) }}
                                </td>
                                <td class="py-3.5 px-4 text-xs font-bold text-gray-900 max-w-xs">
                                    {{ c.file_name || 'Untitled Document' }}
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap text-xs">
                                    <span v-if="c.document_number" class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 font-semibold border border-blue-100 text-[11px]">
                                        {{ c.document_number }}
                                    </span>
                                    <span v-else class="text-gray-400 italic text-[11px]">None</span>
                                </td>
                                <td class="py-3.5 px-4 text-xs max-w-sm">
                                    <p class="line-clamp-2 text-gray-600 leading-relaxed">
                                        {{ c.description || 'No description provided.' }}
                                    </p>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap text-xs">
                                    <div class="flex items-center gap-2" v-if="c.sent_by">
                                        <span class="font-medium text-gray-800">{{ c.sent_by.first_name }} {{ c.sent_by.last_name }}</span>
                                    </div>
                                    <span v-else class="text-gray-400 italic">Central Registry</span>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap text-xs">
                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-700 font-semibold rounded-full text-[11px]">
                                        {{ (c.correspondence_file || []).length }} Attached
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link :href="'/dashboard/correspondence/view-files/' + c.id" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-200 text-gray-700 hover:text-indigo-600 hover:border-indigo-200 rounded-lg text-xs font-semibold shadow-2xs transition-all">
                                            <Icon icon="lucide:eye" width="14" />
                                            <span>View Document</span>
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="text-center py-16 text-gray-400">
                    <Icon icon="lucide:mail-open" width="40" class="mx-auto text-gray-300 mb-2" />
                    <p class="font-bold text-gray-700 text-base">No Correspondence Records Found</p>
                    <p class="text-xs text-gray-500 mt-1">External incoming correspondence and memos registered will appear here.</p>
                </div>
            </div>
        </div>
    </div>
    <RejectFileModal v-model="rejectFile" :data="fileId" @refresh="refreshPage" />
    <AddDestinationModal v-model="addDestination" :fileId="destFileId" @refresh="refreshPage" :branches="branches"
        :branch_type="getUserBranchType()" />
    <CreatEntryModal v-model="createEntry" @refresh="refreshPage" :branches="branches" />
    <TrackPackage v-model="TrackPackageModel" :department="props.department" :division="props.division"
        :unit="props.unit" />
    <ScannedFiles v-model="scanModal" />
    <ScanFile v-model="openScan" @updateData="updateData" />
    <EditEntryModal v-model="showEditEntry" :entry="selectedEntry" @refresh="getBranchFiles" :isAgf="false" />
</template>
<script setup>
import { ref, onMounted, computed, defineProps, watchEffect, watch } from "vue";
import { useToast } from "primevue/usetoast";
import RejectFileModal from '../../components/Modals/RecordManager/RejectFile.vue';
import AddDestinationModal from '../../components/Modals/RecordManager/AdddDestination.vue';
import TrackPackage from "../../components/Modals/Registry/TrackPackage.vue";
import CreatEntryModal from '../../components/Modals/RecordManager/CreateEntry.vue'
import EditEntryModal from '../../components/Modals/RecordManager/EditEntry.vue'
import { router, usePage, Link } from "@inertiajs/vue3";
import ScannedFiles from '../../components/Modals/RecordManager/ScannedFiles.vue';
import ScanFile from '../../components/Modals/RecordManager/ScanFile.vue';
import axios from "axios";
import { usePermissions } from "@/composables/usePermissions";

const { can, hasRole } = usePermissions();

const TrackPackageModel = ref(false);
const addDestination = ref(false);
const createEntry = ref(false)
const showEditEntry = ref(false)
const selectedEntry = ref(null)
const openEditModal = (entry) => {
    selectedEntry.value = entry;
    showEditEntry.value = true;
}
const buttonToggle = ref(1);
const clickedButton = ref("incoming");
const files = ref([])
const rejectFile = ref(false)
const fileId = ref('')
const destFileId = ref(null)
const department = ref({})
const departments = ref([])
const openScan = ref(false)
const permissions = computed(()=>page.props.permissions)
const scanModal = ref(false)
const user_roles = computed(() => page.props.user_roles)
const formData = ref({
    status: "delivered",
});
const checkData = ref(true)
const checkDest = ref(false)
const hide = ref(false)
const page = usePage()
const branches = ref([]);
const destBranchType = ref()
const props = defineProps(['department', 'division', 'unit', 'correspondences']);
const selected_branch = ref()
const raw_files = ref([])
const incoming = ref([])
const outgoing = ref([])

const correspondenceList = ref(props.correspondences?.data || [])
const correspondenceSearch = ref('')
const correspondenceLoading = ref(false)

const activeButton = (btn) => {
    clickedButton.value = btn;
    if (btn === 'incoming') buttonToggle.value = 1;
    if (btn === 'outgoing') buttonToggle.value = 2;
    if (btn === 'correspondence') {
        buttonToggle.value = 3;
        if (correspondenceList.value.length === 0) {
            fetchCorrespondences();
        }
    }
}

const fetchCorrespondences = (search = '') => {
    correspondenceLoading.value = true;
    axios.get('/dashboard/registry/correspondences', { params: { search } })
        .then((res) => {
            correspondenceList.value = res.data?.data || [];
        })
        .catch((err) => {
            console.error('Error fetching correspondences:', err);
        })
        .finally(() => {
            correspondenceLoading.value = false;
        });
}

let searchDebounceTimer = null;
const searchCorrespondences = () => {
    clearTimeout(searchDebounceTimer);
    searchDebounceTimer = setTimeout(() => {
        fetchCorrespondences(correspondenceSearch.value);
    }, 300);
}

const user_branch = computed(() => page.props.userBranch)

const updateData = ()=>{
    toast.add({
            severity: "success",
            summary: 'File forwrded',
            detail: "file has been forwarded to outgoing files.",
            life: 3000,
        });
    getBranchFiles()
    //getBranchFiles()
    getUserBranches()
}

const getUserBranch = () => {
    if (user_branch.value.unit_id) {
        return user_branch.value.unit_id
    }
    if (user_branch.value.division_id) {
        return user_branch.value.division_id
    }
    if (user_branch.value.department_id) {
        return user_branch.value.department_id
    }
}

const getUserBranchType = () => {
    if (user_branch.value.unit_id) {
        return 'unit'
    }
    if (user_branch.value.division_id) {
        return 'division'
    }
    if (user_branch.value.department_id) {
        return 'department'
    }
}

// const outgoing = computed(() => {
//     if (user_branch.value.unit_id) {
//         let filtered = files.value.filter((item) => item.sent_from_unit === user_branch.value.unit_id)
//         return (filtered && filtered.length > 0) ? filtered : []
//     }
//     if (user_branch.value.division_id) {
//         let filtered = files.value.filter((item) => item.sent_from_division === user_branch.value.division_id)
//         return (filtered && filtered.length > 0) ? filtered : []
//     }
//     if (user_branch.value.department_id) {
//         let filtered = files.value.filter((item) => item.sent_from_department === user_branch.value.department_id)
//         return (filtered && filtered.length > 0) ? filtered : []
//     }
//     if (isPermissions('agf registry view')) {
//         let filtered = files.value.filter((item) => item.sent_to_agf == 1)
//         return (filtered && filtered.length > 0) ? filtered : []
//     }
// })

// const incoming = computed(() => {
//     if (user_branch.value.unit_id) {
//         let filtered = files.value.filter((item) => item.sent_to_unit == user_branch.value.unit_id)
//         return (filtered && filtered.length > 0) ? filtered : []
//     }
//     if (user_branch.value.division_id) {
//         let filtered = files.value.filter((item) => item.sent_to_division == user_branch.value.division_id)
//         return (filtered && filtered.length > 0) ? filtered : []
//     }
//     if (user_branch.value.department_id) {
//         let filtered = files.value.filter((item) => item.sent_to_department == user_branch.value.department_id)
//         return (filtered && filtered.length > 0) ? filtered : []
//     }

//     if (isPermissions('agf registry view')) {
//         let filtered = files.value.filter((item) => item.sent_from_agf == 1)
//         return (filtered && filtered.length > 0) ? filtered : []
//     }
// })

const isPermissions = (perm) =>{
    let found = permissions.value.find((item)=>{
       return  item.name == perm
    })

    if(found){
        return true;
    }else{
        return false
    }
}

const formatDate = (inputDate) => {
    var date = new Date(inputDate);
    var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    var day = date.getDate();
    var month = months[date.getMonth()];
    var year = date.getFullYear();

    day = day < 10 ? '0' + day : day;

    var formattedDate = day + ' ' + month + ' ' + year.toString();

    return formattedDate;
}


const getBranchFiles = () => {
    axios.get(`/dashboard/registry/branchFiles`).then((response) => {
        files.value = response.data.data;
        incoming.value = response.data.incoming 
        outgoing.value = response.data.outgoing
        raw_files.value = response.data;
    })

};
// const destination = async (id) =>{
//     destFileId.value = id;
// }
const forwardFileHandler = async (fileId) => {
    addDestination.value = true
    destFileId.value = fileId.id;
    destBranchType.value = fileId.branch_type
    // try {
    //     const response = await axios.post(`/dashboard/registry/forward/${fileId}`);
    //     console.log('updated', response.data.data);
    //     toast.add({
    //         severity: "success",
    //         summary: 'File forwrded',
    //         detail: "file has been forwarded to outgoing files.",
    //         life: 3000,
    //     });
    // } catch (error) {
    //     console.error('Error fetching data:', error.message);
    // }
}

const refreshPage = () => {
    getBranchFiles()
}
onMounted(() => {
    getBranchFiles();
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('tab') === 'correspondence') {
        activeButton('correspondence');
    }
})

const getDestinationBranch = (data) => {

    if (data.sent_to_unit) {
        let filtered = props.unit.filter((item) => {
            return item.id == data.sent_to_unit
        })
        if (filtered.length > 0) {
            return filtered[0].title;
        } else {
            return null;
        }
    }

    if (data.sent_to_division) {
        let filtered = props.division.filter((item) => {
            return item.id == data.sent_to_division
        })
        if (filtered.length > 0) {
            return filtered[0].title;
        } else {
            return null;
        }
    }

    if (data.sent_to_department) {
        let filtered = props.department.filter((item) => {
            return item.id == data.sent_to_department
        })
        if (filtered.length > 0) {
            return filtered[0].title;
        } else {
            return null;
        }
    }

    if (data.sent_to_agf) {
       return 'AGF'
    }
}

const getSourceBranch = (data) => {

    if (data.sent_from_unit) {
        let filtered = props.unit.filter((item) => {
            return item.id == data.sent_from_unit
        })
        if (filtered.length > 0) {
            return filtered[0].title;
        } else {
            return null;
        }
    }

    if (data.sent_from_division) {
        let filtered = props.division.filter((item) => {
            return item.id == data.sent_from_division
        })
        if (filtered.length > 0) {
            return filtered[0].title;
        } else {
            return null;
        }
    }

    if (data.sent_from_department) {
        let filtered = props.department.filter((item) => {
            return item.id == data.sent_from_department
        })
        if (filtered.length > 0) {
            return filtered[0].title;
        } else {
            return null;
        }
    }

    if (data.sent_from_agf) {
       return 'AGF'
    }
}

const getDestinationBranchType = (data) => {

    if (data.sent_to_unit) {
        return 'unit'
    }

    if (data.sent_to_division) {
        return 'division'
    }

    if (data.sent_to_department) {
        return 'department'
    }

    if (data.sent_to_agf) {
        return 'agf'
    }
}

const getSourceBranchType = (data) => {

    if (data.sent_from_unit) {
        return 'unit'
    }

    if (data.sent_from_division) {
        return 'division'
    }

    if (data.sent_from_department) {
        return 'department'
    }

    if (data.sent_from_agf) {
        return 'agf'
    }
}

const displayedItems = computed(() => {

    // if (files.value.received_files && Array.isArray(files.value.received_files)) {
    //     files.value.received_files.forEach(obj => {
    //         const date = new Date(obj.created_at);
    //         obj.created_at = date.toDateString()
    //     })
    // }
    // if (files.value.sent_files && Array.isArray(files.value.sent_files)) {
    //     files.value.sent_files.forEach(obj => {
    //         const date = new Date(obj.created_at);
    //         obj.created_at = date.toDateString()
    //     })
    // }
    if (clickedButton.value == "incoming") {
        checkData.value = true;
        hide.value = false;
        // return files.value.received_files || files.value.received_files.filter((file) => file.is_incoming == 1)
        // return files.value.received_files
        return incoming
    }
    else if (clickedButton.value == "outgoing") {
        checkData.value = false;
        checkDest.value = true;
        hide.value = true;
        // const filtered = files.value.received_files.filter((file) => file.is_outgoing == 1)
        // return [...filtered, ...(files.value.sent_files || [])]
        return outgoing
    }
    // files.value.sent_files.filter((file) => file.created_at = new Date(file.created_at).toDateString())
    // if (!files.value) return [];
    // let data = files.value
    // if (clickedButton.value == "incoming") {
    //     return data = files.value.sent_files.filter((file) => file.is_outgoing == 1)
    // } else {
    //     return data = files.value.sent_files.filter((file) => file.is_incoming == 1)
    // }
});




const toast = useToast();
const expandedRows = ref([]);
const onRowExpand = (event) => { };
const onRowCollapse = (event) => { };

const acceptFileHandler = (file) => {
    // If we have file movements, we acknowledge the latest one
    if (file.file_movement && file.file_movement.length > 0) {
        const movementId = file.file_movement[file.file_movement.length - 1].id;
        axios.post(`/dashboard/registry/movements/${movementId}/acknowledge`)
            .then(response => {
                toast.add({
                    severity: "success",
                    summary: 'File Accepted',
                    detail: "The physical file has been received and acknowledged.",
                    life: 3000,
                });
                getBranchFiles();
            })
            .catch(error => {
                toast.add({
                    severity: "error",
                    summary: 'Error',
                    detail: error.response?.data?.message || "Failed to acknowledge file.",
                    life: 3000,
                });
            });
    } else {
        // Fallback for legacy items without a file_movement relation returned (if any)
        router.post(`/dashboard/registry/updateFileStatus/${file.id}`, formData.value, {
            onSuccess: page => {
                toast.add({
                    severity: "success",
                    summary: 'File Accepted',
                    detail: "You can view and add new destinations for outgoing files.",
                    life: 3000,
                });
                getBranchFiles()
            }
        });
    }
}


const rejectFileHandler = (id) => {
    rejectFile.value = true;
    fileId.value = id
}

const getUserBranches = () => {
    axios.get('/dashboard/registry/user-branches').then((response) => {
        branches.value = response.data
    })
}

onMounted(() => {
    getUserBranches();

    // Echo.private(`registry.${getUserBranch()}.${getUserBranchType()}`)
    // .listen('.RegistryEvent', (event) => {
    //     console.log(event);
    // });
})

// watchEffect(() => {
//     const branch = getUserBranch();
//     const branchType = getUserBranchType();
//     if (branch && branchType) {
//         Echo.private(`registry.${branch}.${branchType}`)
//             .listen('.RegistryEvent', (event) => {
//                 alert('yes');
//                 console.log(event);
//             });
//     }
// });

watch([getUserBranch, getUserBranchType], ([newBranch, newBranchType]) => {
    if (newBranch && newBranchType) {
        Echo.private(`registry.${newBranch}.${newBranchType}`)
            .listen('RegistryEvent', (event) => {
                alert('yes');
                console.log(event);
            });
    }
}, { immediate: true });

// // const displayedItems = computed(() => {
// //   const filteredItems = data.value.filter((item) => {
// //     return item.Going
// //       .toLowerCase()
// //       .includes(clickedButton.value.toLowerCase());
// //   });

//   return filteredItems;
// });
</script>

<style scoped>
.activeButton {
    background-color: white;
    color: black;
    font-weight: bold;
    border: 1px solid #0B6217;
}
</style>
