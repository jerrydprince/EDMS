<template>
    <div class="min-h-screen bg-gray-50 flex flex-col p-8 pb-20">
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-800 tracking-tight">Sent Tasks & Workflows</h1>
          <p class="text-gray-500 mt-1">Track the progress of tasks you have assigned across all departments.</p>
        </div>
        <div class="flex gap-4">
          <div class="relative">
            <Icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" width="18" />
            <input type="text" placeholder="Search tasks..." class="pl-10 pr-4 py-2 bg-white border border-gray-200 rounded-xl focus:ring-4 focus:ring-indigo-50 focus:border-indigo-400 outline-none transition-all w-64 shadow-sm" />
          </div>
          <button class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-50 shadow-sm transition-colors">
            <Icon icon="lucide:filter" width="16" /> Filter
          </button>
        </div>
      </div>
  
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500 mb-1">Total Assigned</p>
            <h3 class="text-3xl font-bold text-gray-800">{{ tasks?.data?.length || 0 }}</h3>
          </div>
          <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center text-blue-500">
            <Icon icon="lucide:send" width="24" />
          </div>
        </div>
        
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500 mb-1">Pending Approval</p>
            <h3 class="text-3xl font-bold text-gray-800">0</h3>
          </div>
          <div class="w-12 h-12 rounded-full bg-orange-50 flex items-center justify-center text-orange-500">
            <Icon icon="lucide:clock" width="24" />
          </div>
        </div>
      </div>
  
      <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-gray-50 border-b border-gray-200">
              <th class="py-4 px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider">Task Details</th>
              <th class="py-4 px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider">Source</th>
              <th class="py-4 px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider">Assigned To</th>
              <th class="py-4 px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
              <th class="py-4 px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider">Due Date</th>
              <th class="py-4 px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="task in tasks?.data" :key="task.id" class="hover:bg-gray-50 transition-colors">
              <td class="py-4 px-6">
                <div class="flex flex-col">
                  <span class="font-semibold text-gray-800">{{ task.title }}</span>
                  <span class="text-xs text-gray-500 mt-1 line-clamp-1 w-64">{{ task.description }}</span>
                </div>
              </td>
              <td class="py-4 px-6">
                <div class="flex items-center gap-2 text-sm text-gray-600" v-if="task.mail_id">
                  <Icon icon="lucide:mail" class="text-blue-500" width="16" /> Correspondence
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-600" v-else-if="task.file_id">
                  <Icon icon="lucide:file-text" class="text-purple-500" width="16" /> Registry Document
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-600" v-else>
                  <Icon icon="lucide:layout-template" class="text-gray-400" width="16" /> Internal Task
                </div>
              </td>
              <td class="py-4 px-6">
                <div class="flex -space-x-2">
                   <div class="w-8 h-8 rounded-full bg-indigo-100 border-2 border-white flex items-center justify-center text-xs font-bold text-indigo-700" title="Assigned Department">
                     {{ task.assignments && task.assignments.length > 0 ? 'Dpt' : '?' }}
                   </div>
                </div>
              </td>
              <td class="py-4 px-6">
                <span class="px-3 py-1 text-xs font-semibold rounded-full" 
                  :class="{
                    'bg-yellow-100 text-yellow-700': task.status === 'pending',
                    'bg-blue-100 text-blue-700': task.status === 'in_progress',
                    'bg-green-100 text-green-700': task.status === 'completed'
                  }">
                  {{ task.status.replace('_', ' ').toUpperCase() }}
                </span>
              </td>
              <td class="py-4 px-6 text-sm text-gray-600">
                {{ task.due_date || 'No Date' }}
              </td>
              <td class="py-4 px-6 text-right">
                <button class="px-4 py-2 bg-indigo-50 text-indigo-600 text-sm font-semibold rounded-xl hover:bg-indigo-100 transition-colors">
                  Review & Approve
                </button>
              </td>
            </tr>
            <tr v-if="!tasks?.data || tasks.data.length === 0">
              <td colspan="6" class="py-16 text-center text-gray-500">
                <div class="flex flex-col items-center justify-center">
                  <Icon icon="lucide:inbox" width="48" class="text-gray-300 mb-4" />
                  <p class="text-lg font-medium text-gray-600">No Sent Tasks Found</p>
                  <p class="text-sm mt-1">You haven't assigned any tasks yet.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </template>
  
  <script setup>
  import { defineProps } from 'vue';
  import { Icon } from '@iconify/vue';
  
  const props = defineProps({
    tasks: Object
  });
  </script>
