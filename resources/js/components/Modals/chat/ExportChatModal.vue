<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        modal
        header="Export Conversation Transcript"
        :style="{ width: '450px' }"
        :closable="true"
        class="p-fluid rounded-2xl overflow-hidden"
    >
        <div class="p-2 space-y-4">
            <div class="flex items-center gap-3 p-3 bg-blue-50/60 rounded-xl border border-blue-100/80">
                <div class="p-2.5 bg-blue-600 text-white rounded-lg shadow-sm">
                    <Icon icon="lucide:file-down" class="text-xl" />
                </div>
                <div>
                    <h4 class="font-semibold text-sm text-gray-800">{{ conversationTitle }}</h4>
                    <p class="text-xs text-gray-500">Certified EDMS Communication Transcript</p>
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">Export Format</label>
                <div class="grid grid-cols-2 gap-3">
                    <div
                        @click="selectedFormat = 'pdf'"
                        class="p-3.5 border-2 rounded-xl cursor-pointer transition-all flex flex-col items-center text-center gap-2"
                        :class="selectedFormat === 'pdf' ? 'border-primary bg-primary/5 text-primary shadow-xs' : 'border-gray-200 hover:border-gray-300 text-gray-600'"
                    >
                        <Icon icon="vscode-icons:file-type-pdf2" class="text-3xl" />
                        <div>
                            <span class="block font-semibold text-xs">Official PDF</span>
                            <span class="text-[11px] text-gray-500">Branded transcript log</span>
                        </div>
                    </div>
                    <div
                        @click="selectedFormat = 'json'"
                        class="p-3.5 border-2 rounded-xl cursor-pointer transition-all flex flex-col items-center text-center gap-2"
                        :class="selectedFormat === 'json' ? 'border-primary bg-primary/5 text-primary shadow-xs' : 'border-gray-200 hover:border-gray-300 text-gray-600'"
                    >
                        <Icon icon="vscode-icons:file-type-json" class="text-3xl" />
                        <div>
                            <span class="block font-semibold text-xs">JSON Data</span>
                            <span class="text-[11px] text-gray-500">Raw audit & retention format</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 text-xs text-gray-500 space-y-1">
                <div class="flex items-center gap-1.5 font-medium text-gray-700">
                    <Icon icon="lucide:shield-check" class="text-emerald-600" />
                    <span>Certified Retention & Compliance</span>
                </div>
                <p>The exported transcript includes timestamps, participant designations, message history, edit logs, and attachment manifests.</p>
            </div>
        </div>

        <template #footer>
            <div class="flex items-center justify-end gap-2 pt-2">
                <Button
                    label="Cancel"
                    severity="secondary"
                    text
                    @click="$emit('update:visible', false)"
                    class="text-xs"
                />
                <Button
                    :label="loading ? 'Generating...' : `Download ${selectedFormat.toUpperCase()}`"
                    icon="pi pi-download"
                    :loading="loading"
                    @click="downloadTranscript"
                    class="bg-primary border-primary text-xs px-4"
                />
            </div>
        </template>
    </Dialog>
</template>

<script setup>
import { ref, computed } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import { Icon } from '@iconify/vue';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    visible: {
        type: Boolean,
        default: false
    },
    conversationType: {
        type: String,
        default: 'direct' // 'direct' or 'group'
    },
    targetId: {
        type: [Number, String],
        required: true
    },
    groupType: {
        type: String,
        default: 'department'
    },
    conversationTitle: {
        type: String,
        default: 'Conversation'
    }
});

const emit = defineEmits(['update:visible']);
const toast = useToast();

const selectedFormat = ref('pdf');
const loading = ref(false);

const downloadTranscript = async () => {
    loading.value = true;
    const token = localStorage.getItem("token");

    try {
        const params = new URLSearchParams({
            type: props.conversationType,
            target_id: props.targetId,
            group_type: props.groupType,
            format: selectedFormat.value
        });

        const response = await axios.get(`/dashboard/chat/export?${params.toString()}`, {
            headers: { Authorization: `Bearer ${token}` },
            responseType: 'blob'
        });

        // Determine filename from content-disposition header or fallback
        const disposition = response.headers['content-disposition'];
        let filename = `transcript_${props.conversationTitle.toLowerCase().replace(/[^a-z0-9]/g, '_')}_${Date.now()}.${selectedFormat.value}`;
        if (disposition && disposition.indexOf('filename=') !== -1) {
            const matches = /filename="?([^"]+)"?/.exec(disposition);
            if (matches && matches[1]) {
                filename = matches[1];
            }
        }

        // Trigger browser download
        const blob = new Blob([response.data], {
            type: selectedFormat.value === 'json' ? 'application/json' : 'application/pdf'
        });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', filename);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);

        toast.add({
            severity: 'success',
            summary: 'Export Complete',
            detail: `Transcript downloaded as ${selectedFormat.value.toUpperCase()}`,
            life: 3000
        });

        emit('update:visible', false);
    } catch (error) {
        console.error('Failed to export transcript:', error);
        toast.add({
            severity: 'error',
            summary: 'Export Failed',
            detail: 'Unable to generate transcript export. Please try again.',
            life: 4000
        });
    } finally {
        loading.value = false;
    }
};
</script>
