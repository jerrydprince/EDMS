<template>
    <div>
        <Head title="AGF Registry Tasks Inbox" />
        <div class="px-5">
            <h1 class="text-xl font-semibold mb-4">Workflow Tasks Inbox</h1>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-1">
                    <div class="bg-white border p-4 rounded-xl">
                        <h2 class="font-medium text-lg border-b pb-2 mb-4">Pending Tasks</h2>
                        <ul class="space-y-3 max-h-[70vh] overflow-y-auto">
                            <li v-for="task in props.tasks.data" :key="task.id" 
                                class="p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors"
                                :class="{'border-primary bg-primary/5': selectedTask && selectedTask.id === task.id}"
                                @click="selectTask(task)">
                                <div class="font-medium text-gray-800 flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        {{ task.title }}
                                        <Icon v-if="task.is_signed" icon="lucide:pen-tool" class="text-green-600" width="14" title="Digitally Signed" />
                                    </div>
                                    <Link as="button" :href="'/dashboard/view-task/'+task.id" class="flex items-center justify-center w-8 h-8 rounded-full hover:bg-blue-100 text-blue-600 transition-colors" @click.stop title="View Full Task">
                                        <Icon icon="ph:eye" width="18" />
                                    </Link>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">Status: {{ task.status }}</div>
                                <div class="text-xs text-primary mt-1" v-if="task.workflow_version">Workflow: {{ task.workflow_version.workflow?.title }}</div>
                            </li>
                            <li v-if="!props.tasks.data.length" class="text-gray-500 text-sm italic">
                                No pending tasks.
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="md:col-span-2">
                    <div v-if="selectedTask">
                        <!-- Use GenericWorkflowPanel for generic workflows -->
                        <GenericWorkflowPanel 
                            :task="selectedTask" 
                            :userLevel="'agf registry'"
                            @workflow-updated="handleWorkflowUpdate"
                        />
                        <div class="mt-4 bg-white border p-4 rounded-xl">
                            <h3 class="font-medium mb-2">Task Details</h3>
                            <p class="text-sm text-gray-700"><strong>Description:</strong> {{ selectedTask.description || 'N/A' }}</p>
                            <p class="text-sm text-gray-700 mt-1"><strong>Due Date:</strong> {{ selectedTask.due_date || 'N/A' }}</p>
                        </div>
                    </div>
                    <div v-else class="bg-white border p-10 rounded-xl text-center text-gray-500 h-full flex items-center justify-center">
                        Select a task to view details and execute transitions.
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Icon } from '@iconify/vue';
import GenericWorkflowPanel from '@/components/GenericWorkflowPanel.vue';

const props = defineProps({
    tasks: Object
});

const selectedTask = ref(null);

const selectTask = (task) => {
    selectedTask.value = task;
};

const handleWorkflowUpdate = () => {
    router.reload({ only: ['tasks'] });
    selectedTask.value = null; // deselect task since it might have moved
};
</script>
