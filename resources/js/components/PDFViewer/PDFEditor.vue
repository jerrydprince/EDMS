<template>
  <div class="flex flex-col w-full h-full bg-gray-100 rounded-lg overflow-hidden border border-gray-200 shadow-sm relative">
    
    <!-- Custom Scrollbar styles -->
    <component :is="'style'">
      .hide-scrollbar::-webkit-scrollbar { display: none; }
      .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </component>

    <!-- Toolbar -->
    <!-- Toolbar -->
    <div class="bg-white border-b border-gray-200 px-4 py-2 flex flex-wrap items-center gap-y-2 gap-x-1 z-10 w-full shadow-sm">
      
      <!-- Page Navigation -->
      <div class="flex items-center gap-1">
        <button @click="prevPage" :disabled="pageNum <= 1" class="p-1.5 rounded hover:bg-gray-100 disabled:opacity-50 text-gray-700 transition-colors">
          <Icon icon="lucide:chevron-left" width="20" />
        </button>
        <div class="flex items-center gap-1 text-sm text-gray-600 font-medium">
          <input type="number" v-model.number="inputPageNum" @change="goToPage" class="w-12 text-center border border-gray-300 rounded px-1 py-0.5 focus:ring-2 focus:ring-blue-500 outline-none" min="1" :max="numPages" />
          <span>/ {{ numPages }}</span>
        </div>
        <button @click="nextPage" :disabled="pageNum >= numPages" class="p-1.5 rounded hover:bg-gray-100 disabled:opacity-50 text-gray-700 transition-colors">
          <Icon icon="lucide:chevron-right" width="20" />
        </button>
      </div>

      <!-- Zoom & Rotate Controls -->
      <div class="flex flex-wrap items-center gap-1 border-l border-r border-gray-200 px-2 mx-2">
        <button @click="zoomOut" class="p-1.5 rounded hover:bg-gray-100 text-gray-700 transition-colors" title="Zoom Out">
          <Icon icon="lucide:zoom-out" width="20" />
        </button>
        <span class="text-xs font-medium text-gray-600 w-12 text-center">{{ Math.round(scale * 100) }}%</span>
        <button @click="zoomIn" class="p-1.5 rounded hover:bg-gray-100 text-gray-700 transition-colors" title="Zoom In">
          <Icon icon="lucide:zoom-in" width="20" />
        </button>
        <div class="w-px h-5 bg-gray-300 mx-1"></div>
        <button @click="rotateRight" class="p-1.5 rounded hover:bg-gray-100 text-gray-700 transition-colors" title="Rotate">
          <Icon icon="lucide:rotate-cw" width="20" />
        </button>
      </div>

      <!-- Editor Actions -->
      <!-- Editor Actions -->
      <div class="flex flex-wrap items-center gap-1 md:border-r border-gray-200 pr-2 mr-1">
        <!-- Text Group -->
        <button @click="addText" class="p-1.5 rounded text-gray-700 hover:bg-gray-100 transition-colors flex items-center justify-center" title="Add Text">
          <Icon icon="lucide:type" width="20" />
        </button>
        <input type="color" v-model="currentTextColor" class="w-6 h-6 p-0 border-0 rounded cursor-pointer bg-transparent" title="Text Color" />
        <input type="number" v-model="currentTextSize" class="w-12 text-sm border border-gray-300 rounded px-1 py-0.5 outline-none bg-white text-center h-7" min="8" max="72" title="Text Size" />

        <div class="w-px h-5 bg-gray-300 mx-1"></div>

        <!-- Format Group -->
        <button @click="toggleFormat('bold')" :class="['p-1.5 rounded transition-colors flex items-center justify-center', currentFormat.bold ? 'bg-gray-200 text-gray-900' : 'text-gray-700 hover:bg-gray-100']" title="Bold"><Icon icon="lucide:bold" width="16" /></button>
        <button @click="toggleFormat('italic')" :class="['p-1.5 rounded transition-colors flex items-center justify-center', currentFormat.italic ? 'bg-gray-200 text-gray-900' : 'text-gray-700 hover:bg-gray-100']" title="Italic"><Icon icon="lucide:italic" width="16" /></button>
        <button @click="toggleFormat('underline')" :class="['p-1.5 rounded transition-colors flex items-center justify-center', currentFormat.underline ? 'bg-gray-200 text-gray-900' : 'text-gray-700 hover:bg-gray-100']" title="Underline"><Icon icon="lucide:underline" width="16" /></button>
        <div class="w-px h-4 bg-gray-300 mx-0.5"></div>
        <button @click="setAlign('left')" :class="['p-1.5 rounded transition-colors flex items-center justify-center', currentFormat.align === 'left' ? 'bg-gray-200 text-gray-900' : 'text-gray-700 hover:bg-gray-100']" title="Align Left"><Icon icon="lucide:align-left" width="16" /></button>
        <button @click="setAlign('center')" :class="['p-1.5 rounded transition-colors flex items-center justify-center', currentFormat.align === 'center' ? 'bg-gray-200 text-gray-900' : 'text-gray-700 hover:bg-gray-100']" title="Align Center"><Icon icon="lucide:align-center" width="16" /></button>
        <button @click="setAlign('right')" :class="['p-1.5 rounded transition-colors flex items-center justify-center', currentFormat.align === 'right' ? 'bg-gray-200 text-gray-900' : 'text-gray-700 hover:bg-gray-100']" title="Align Right"><Icon icon="lucide:align-right" width="16" /></button>

        <div class="w-px h-5 bg-gray-300 mx-1"></div>

        <!-- Tools -->
        <button @click="addSignature" class="p-1.5 rounded text-gray-700 hover:bg-gray-100 transition-colors flex items-center justify-center" title="Add Signature">
          <Icon icon="lucide:pen-tool" width="20" />
        </button>

        <div class="relative group">
            <button class="p-1.5 rounded text-gray-700 hover:bg-gray-100 transition-colors flex items-center justify-center" title="Add Stamp">
              <Icon icon="lucide:stamp" width="20" />
            </button>
            <div class="absolute top-full left-0 mt-1 bg-white border border-gray-200 shadow-lg rounded-md overflow-hidden opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 w-48">
                <div class="px-4 py-1.5 text-xs font-semibold text-gray-400 uppercase bg-gray-50">Current Page</div>
                <button @click="addStamp('APPROVED', {r: 0, g: 0.6, b: 0})" class="w-full text-left px-4 py-2 text-sm text-green-700 font-bold hover:bg-gray-50 border-b border-gray-100">APPROVED</button>
                <button @click="addStamp('REJECTED', {r: 0.8, g: 0, b: 0})" class="w-full text-left px-4 py-2 text-sm text-red-700 font-bold hover:bg-gray-50 border-b border-gray-100">REJECTED</button>
                <button @click="addStamp('CONFIDENTIAL', {r: 1, g: 0.5, b: 0})" class="w-full text-left px-4 py-2 text-sm text-orange-700 font-bold hover:bg-gray-50 border-b border-gray-100">CONFIDENTIAL</button>
                <button @click="addStamp(new Date().toLocaleDateString(), {r: 0, g: 0, b: 0})" class="w-full text-left px-4 py-2 text-sm text-gray-700 font-bold hover:bg-gray-50 border-b border-gray-100">Date</button>

                <div class="px-4 py-1.5 text-xs font-semibold text-gray-400 uppercase bg-gray-50 mt-1">All Pages</div>
                <button @click="addStamp('APPROVED', {r: 0, g: 0.6, b: 0}, true)" class="w-full text-left px-4 py-2 text-sm text-green-700 font-bold hover:bg-gray-50 border-b border-gray-100">Global: APPROVED</button>
                <button @click="addStamp('REJECTED', {r: 0.8, g: 0, b: 0}, true)" class="w-full text-left px-4 py-2 text-sm text-red-700 font-bold hover:bg-gray-50 border-b border-gray-100">Global: REJECTED</button>
                <button @click="addStamp('CONFIDENTIAL', {r: 1, g: 0.5, b: 0}, true)" class="w-full text-left px-4 py-2 text-sm text-orange-700 font-bold hover:bg-gray-50 border-b border-gray-100">Global: CONFIDENTIAL</button>
            </div>
        </div>

        <button @click="addHighlight" class="p-1.5 rounded text-gray-700 hover:bg-gray-100 transition-colors flex items-center justify-center" title="Add Highlight">
          <Icon icon="lucide:highlighter" width="20" />
        </button>

        <button @click="addRedaction" class="p-1.5 rounded text-gray-700 hover:bg-gray-100 transition-colors flex items-center justify-center" title="Add Redaction">
          <Icon icon="lucide:square" width="20" />
        </button>

        <div class="w-px h-5 bg-gray-300 mx-1"></div>

        <button @click="clearAll" :disabled="!hasModifications" :class="['p-1.5 rounded transition-colors flex items-center justify-center', hasModifications ? 'text-red-600 hover:bg-red-50' : 'text-gray-300 cursor-not-allowed']" title="Clear All Overlays">
          <Icon icon="lucide:trash-2" width="20" />
        </button>
      </div>

      <!-- Actions -->
      <div class="flex items-center gap-1.5 md:ml-auto">
        <button v-if="hasModifications" @click="savePdf" :disabled="isSaving" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition-all shadow-sm flex items-center gap-1.5 cursor-pointer disabled:opacity-60" title="Save All Signatures and Annotations to Server">
          <Icon v-if="!isSaving" icon="lucide:save" width="15" />
          <Icon v-else icon="lucide:loader-2" class="animate-spin" width="15" />
          <span>{{ isSaving ? 'Saving...' : 'Save PDF' }}</span>
        </button>
        <button @click="downloadPdf" class="p-1.5 rounded hover:bg-gray-100 text-gray-700 transition-colors" title="Download">
          <Icon v-if="!isDownloading" icon="lucide:download" width="20" />
          <Icon v-else icon="lucide:loader-2" class="animate-spin" width="20" />
        </button>
        <button @click="printPdf" class="p-1.5 rounded hover:bg-gray-100 text-gray-700 transition-colors" title="Print">
          <Icon icon="lucide:printer" width="20" />
        </button>
      </div>

    </div>

    <!-- Viewer Area -->
    <div ref="viewerContainer" class="flex-1 overflow-auto bg-gray-200 relative flex justify-center p-4">
      
      <!-- PIN Modal -->
      <div v-if="showPinModal" class="absolute inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-800">Digital Signature Required</h3>
            <button @click="showPinModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
              <Icon icon="lucide:x" width="20" />
            </button>
          </div>
          <div class="p-6">
            <p class="text-sm text-gray-600 mb-4">Please enter your digital signature PIN to authorize placing your signature on this document.</p>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Signature PIN</label>
              <input type="password" v-model="signaturePin" @keyup.enter="verifyAndAddSignature" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Enter PIN" autofocus />
              <p v-if="pinError" class="mt-1 text-sm text-red-500">{{ pinError }}</p>
            </div>
          </div>
          <div class="px-6 py-4 bg-gray-50 flex justify-end gap-3 border-t border-gray-100">
            <button @click="showPinModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-md transition-colors">Cancel</button>
            <button @click="verifyAndAddSignature" :disabled="isVerifyingPin" class="px-4 py-2 text-sm font-medium bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors flex items-center gap-2 disabled:opacity-70">
              <Icon v-if="isVerifyingPin" icon="lucide:loader-2" class="animate-spin" width="16" />
              Verify & Add
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-gray-200/80 z-20">
        <div class="flex flex-col items-center">
          <Icon icon="lucide:loader-2" class="animate-spin text-blue-500 mb-2" width="32" />
          <span class="text-sm font-medium text-gray-600">Loading PDF...</span>
        </div>
      </div>

      <!-- Error State -->
      <div v-if="loadError" class="absolute inset-0 flex flex-col items-center justify-center bg-white z-30 p-6 text-center">
        <div class="w-16 h-16 rounded-full bg-red-100 text-red-600 flex items-center justify-center mb-4 shadow-sm">
          <Icon icon="lucide:alert-triangle" class="w-8 h-8" />
        </div>
        <h4 class="text-lg font-bold text-gray-800 mb-1">Failed to Display Document</h4>
        <p class="text-sm text-gray-600 max-w-md mb-4">{{ loadError }}</p>
        <div class="flex items-center gap-3">
          <button @click="loadPDF(props.url)" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2 text-sm font-semibold shadow-sm cursor-pointer">
            <Icon icon="lucide:refresh-cw" width="16" /> Retry Loading
          </button>
          <a :href="props.url" target="_blank" download class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition flex items-center gap-2 text-sm font-medium">
            <Icon icon="lucide:download" width="16" /> Download PDF
          </a>
        </div>
      </div>

      <!-- Canvas Wrapper -->
      <div class="relative bg-white shadow-lg transition-transform origin-top inline-block" :style="{ transform: `rotate(${rotation}deg)` }">
        <canvas ref="pdfCanvas" class="block"></canvas>
        
        <!-- Overlay Layer -->
        <div class="absolute inset-0 overflow-visible pointer-events-none" ref="overlayContainer">
          <div v-for="(item, index) in currentPageOverlays" :key="item.id" 
               class="absolute pointer-events-auto border-2 border-transparent hover:border-blue-400 group cursor-move select-none"
               :style="{ left: item.x + '%', top: item.y + '%', width: item.isStamp ? 'auto' : item.width + '%', height: item.isStamp ? 'auto' : item.height + '%' }"
               @mousedown.stop="startDrag($event, item)">
               
               <!-- Delete Button -->
               <button @mousedown.stop="deleteItem(item)" class="absolute -top-3 -right-3 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity shadow z-10">
                 <Icon icon="lucide:x" width="12" />
               </button>
               
               <template v-if="item.type === 'signature'">
                 <img v-if="item.src" :src="item.src" class="w-full h-full pointer-events-none object-contain" alt="Signature" />
                 <div v-else class="w-full h-full bg-gray-100 border border-dashed border-gray-400 flex items-center justify-center text-gray-400 text-xs text-center p-2">
                   No Digital Signature Available
                 </div>
               </template>

               <template v-else-if="item.type === 'text'">
                 <div v-if="item.isStamp" 
                  :class="['leading-tight bg-white/50 p-1 font-bold uppercase border-2 px-2 py-1 rounded-sm tracking-wider opacity-80 whitespace-nowrap']"
                  :style="{
                    fontSize: item.fontSize ? item.fontSize + 'px' : '14px',
                    color: item.color ? `rgb(${item.color.r * 255}, ${item.color.g * 255}, ${item.color.b * 255})` : 'black',
                    fontWeight: item.bold ? 'bold' : 'normal',
                    fontStyle: item.italic ? 'italic' : 'normal',
                    textDecoration: item.underline ? 'underline' : 'none',
                    textAlign: item.align || 'left',
                    borderColor: item.isStamp && item.color ? `rgb(${item.color.r * 255}, ${item.color.g * 255}, ${item.color.b * 255})` : 'transparent'
                  }">
                  {{ item.content }}
                 </div>
                 <textarea v-else v-model="item.content" 
                  :class="['w-full h-full min-h-[40px] resize-none bg-transparent border-none outline-none focus:ring-2 focus:ring-blue-500 leading-tight bg-white/50 p-1 text-black']"
                  :style="{
                    fontSize: item.fontSize ? item.fontSize + 'px' : '14px',
                    color: item.color ? `rgb(${item.color.r * 255}, ${item.color.g * 255}, ${item.color.b * 255})` : 'black',
                    fontWeight: item.bold ? 'bold' : 'normal',
                    fontStyle: item.italic ? 'italic' : 'normal',
                    textDecoration: item.underline ? 'underline' : 'none',
                    textAlign: item.align || 'left',
                    borderColor: 'transparent'
                  }"
                  placeholder="Type text here..." @mousedown.stop="activeOverlay = item" @input="resizeTextarea($event, item)"></textarea>
               </template>

               <template v-else-if="item.type === 'redact'">
                 <div class="w-full h-full bg-black border border-black cursor-move shadow-md opacity-90"></div>
               </template>

               <template v-else-if="item.type === 'highlight'">
                 <div class="w-full h-full bg-yellow-300 opacity-40 border border-yellow-400 cursor-move mix-blend-multiply"></div>
               </template>

               <!-- Resize handle (bottom right) -->
               <div class="absolute -bottom-1 -right-1 w-3 h-3 bg-blue-500 rounded-full opacity-0 group-hover:opacity-100 cursor-se-resize shadow" @mousedown.stop="startResize($event, item)"></div>
          </div>
        </div>

      </div>
      
    </div>

    <!-- Hidden iframe for printing -->
    <iframe v-if="printUrl" :src="printUrl" class="hidden" ref="printIframe" @load="onPrintIframeLoad"></iframe>

  </div>
</template>

<script setup>
import Swal from 'sweetalert2';

import { ref, shallowRef, computed, onMounted, watch, nextTick, onUnmounted } from 'vue';
import { Icon } from '@iconify/vue';
import { PDFDocument, rgb, StandardFonts } from 'pdf-lib';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import * as pdfjsLib from 'pdfjs-dist';
import pdfjsWorker from 'pdfjs-dist/build/pdf.worker.min.mjs?url';

if (typeof window !== 'undefined' && pdfjsLib) {
  try {
    pdfjsLib.GlobalWorkerOptions.workerSrc = pdfjsWorker;
  } catch (e) {
    console.warn("Could not set local workerSrc:", e);
  }
}

const page = usePage();
const currentUser = computed(() => page.props.auth?.user || null);

const loadPdfJs = async () => {
  if (pdfjsLib && pdfjsLib.getDocument) return pdfjsLib;
  if (window.pdfjsLib) return window.pdfjsLib;

  return new Promise((resolve, reject) => {
    const script = document.createElement('script');
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js';
    
    script.onload = async () => {
      try {
        const workerRes = await fetch('https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js');
        const workerText = await workerRes.text();
        const workerBlob = new Blob([workerText], { type: 'text/javascript' });
        window.pdfjsLib.GlobalWorkerOptions.workerSrc = URL.createObjectURL(workerBlob);
        resolve(window.pdfjsLib);
      } catch (err) {
        console.error("Failed to load PDF worker", err);
        resolve(window.pdfjsLib); 
      }
    };
    
    script.onerror = reject;
    document.head.appendChild(script);
  });
};

const props = defineProps({
  url: { type: String, required: true },
});

const pdfDoc = shallowRef(null);
const pageNum = ref(1);
const inputPageNum = ref(1);
const numPages = ref(0);
const scale = ref(1.0);
const rotation = ref(0);
const loading = ref(true);
const isGenerating = ref(false);
const pdfCanvas = ref(null);
const loadError = ref('');
const viewerContainer = ref(null);
const overlayContainer = ref(null);

const printUrl = ref('');
const printIframe = ref(null);

// Overlay Items State
// Store items for all pages. Structure: { pageNum: [item1, item2] }
const allOverlays = ref({});

const currentPageOverlays = computed(() => {
  if (!allOverlays.value[pageNum.value]) {
    allOverlays.value[pageNum.value] = [];
  }
  return allOverlays.value[pageNum.value];
});

let draggedItem = null;
let resizedItem = null;
let startX = 0;
let startY = 0;
let startItemX = 0;
let startItemY = 0;
let startItemW = 0;
let startItemH = 0;

const getSignatureUrl = () => {
  if (currentUser.value && currentUser.value.digital_signature_path) {
    return (page.props.base || '') + '/' + currentUser.value.digital_signature_path;
  }
  return null;
};

const showPinModal = ref(false);
const signaturePin = ref('');
const pinError = ref('');
const isVerifyingPin = ref(false);

const addSignature = () => {
  signaturePin.value = '';
  pinError.value = '';
  showPinModal.value = true;
};

const verifyAndAddSignature = async () => {
  if (!signaturePin.value) {
    pinError.value = 'PIN is required.';
    return;
  }
  
  isVerifyingPin.value = true;
  pinError.value = '';
  
  try {
    const response = await axios.post((page.props.base || '') + '/dashboard/verify-signature-pin', {
      pin: signaturePin.value
    });
    
    if (response.data.valid) {
      showPinModal.value = false;
      const url = (page.props.base || '') + '/' + response.data.path;
      currentPageOverlays.value.push({
        id: Date.now().toString(),
        type: 'signature',
        src: url,
        x: 40,
        y: 40,
        width: 20,
        height: 10
      });
    } else {
      pinError.value = response.data.message || 'Invalid PIN.';
    }
  } catch (err) {
    if (err.response && err.response.data && err.response.data.message) {
      pinError.value = err.response.data.message;
    } else {
      pinError.value = 'An error occurred during verification.';
    }
  } finally {
    isVerifyingPin.value = false;
  }
};

const addHighlight = () => {
  currentPageOverlays.value.push({
    id: Date.now().toString(),
    type: 'highlight',
    x: 40,
    y: 40,
    width: 30,
    height: 3
  });
};

const addRedaction = () => {
  currentPageOverlays.value.push({
    id: Date.now().toString(),
    type: 'redact',
    x: 40,
    y: 40,
    width: 20,
    height: 5
  });
};

const currentTextColor = ref('#000000');
const currentTextSize = ref(14);
const activeOverlay = ref(null);
const currentFormat = ref({
    bold: false,
    italic: false,
    underline: false,
    align: 'left'
});

const toggleFormat = (format) => {
    currentFormat.value[format] = !currentFormat.value[format];
    if (activeOverlay.value && activeOverlay.value.type === 'text') {
        activeOverlay.value[format] = currentFormat.value[format];
    }
};

const setAlign = (align) => {
    currentFormat.value.align = align;
    if (activeOverlay.value && activeOverlay.value.type === 'text') {
        activeOverlay.value.align = align;
    }
};

const resizeTextarea = (e, item) => {
    if (!overlayContainer.value) return;
    const containerHeight = overlayContainer.value.clientHeight;
    e.target.style.height = 'auto';
    const px = e.target.scrollHeight;
    item.height = (px / containerHeight) * 100;
};

const addText = () => {
  const hex = currentTextColor.value;
  const r = parseInt(hex.substr(1,2), 16) / 255;
  const g = parseInt(hex.substr(3,2), 16) / 255;
  const b = parseInt(hex.substr(5,2), 16) / 255;

  currentPageOverlays.value.push({
    id: Date.now().toString(),
    type: 'text',
    content: '',
    x: 40,
    y: 40,
    width: 30,
    height: 10,
    color: { r, g, b },
    fontSize: currentTextSize.value,
    bold: currentFormat.value.bold,
    italic: currentFormat.value.italic,
    underline: currentFormat.value.underline,
    align: currentFormat.value.align
  });
  activeOverlay.value = currentPageOverlays.value[currentPageOverlays.value.length - 1];
};

const deleteItem = (item) => {
  const index = currentPageOverlays.value.findIndex(i => i.id === item.id);
  if (index > -1) {
    currentPageOverlays.value.splice(index, 1);
  }
};

let draggedItemActualHeightPct = 10;

const startDrag = (e, item) => {
  activeOverlay.value = item;
  if (e.target.tagName.toLowerCase() === 'textarea') return; // Don't drag if clicking in text area to type
  draggedItem = item;
  startX = e.clientX;
  startY = e.clientY;
  startItemX = item.x;
  startItemY = item.y;
  
  if (overlayContainer.value && e.currentTarget) {
    const rect = overlayContainer.value.getBoundingClientRect();
    const elRect = e.currentTarget.getBoundingClientRect();
    draggedItemActualHeightPct = (elRect.height / rect.height) * 100;
  }
  
  window.addEventListener('mousemove', onDrag);
  window.addEventListener('mouseup', stopDrag);
};

const emit = defineEmits(['save-pdf']);
const isSaving = ref(false);

const getPdfBlob = async () => {
  if (!hasModifications.value) return null;
  const { blob } = await generateModifiedPdf();
  return blob;
};

const hasModifications = computed(() => {
    for (const [pNum, overlays] of Object.entries(allOverlays.value || {})) {
        if (overlays && overlays.length > 0) return true;
    }
    return false;
});

const savePdf = async () => {
  if (!hasModifications.value) {
    Swal.fire({
      icon: 'info',
      title: 'No Changes',
      text: 'No signatures, stamps, or text annotations have been added.'
    });
    return;
  }
  isSaving.value = true;
  try {
    const { blob } = await generateModifiedPdf();
    if (blob) {
      emit('save-pdf', { blob, overlays: allOverlays.value });
    }
  } catch (err) {
    console.error('Error saving PDF:', err);
    Swal.fire({
      icon: 'error',
      title: 'Save Failed',
      text: 'Could not generate modified PDF.'
    });
  } finally {
    isSaving.value = false;
  }
};

const isDownloading = ref(false);

const downloadPdf = async () => {
    isDownloading.value = true;
    try {
        if (!hasModifications.value) {
            const a = document.createElement('a');
            a.href = props.url;
            a.download = 'document.pdf';
            a.click();
        } else {
            const blob = await getPdfBlob();
            if (blob) {
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'document_modified.pdf';
                a.click();
                setTimeout(() => URL.revokeObjectURL(url), 1000);
            }
        }
    } catch(e) {
        console.error(e);
    }
    isDownloading.value = false;
};

const clearAll = () => {
    Swal.fire({
      title: 'Confirm',
      text: "Are you sure you want to clear all signatures, text, and stamps from all pages?",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes',
      cancelButtonText: 'No'
    }).then((result) => {
      if (result.isConfirmed) {
        allOverlays.value = {};
        allOverlays.value[pageNum.value] = [];
    }
    });
};

const addStamp = (text, color, global = false) => {
  const stampItem = {
    type: 'text',
    content: text,
    x: 40,
    y: 40,
    width: 20,
    height: 10,
    color: color,
    fontSize: 24,
    isStamp: true
  };

  if (global) {
    for (let i = 1; i <= numPages.value; i++) {
       if (!allOverlays.value[i]) {
         allOverlays.value[i] = [];
       }
       allOverlays.value[i].push({ ...stampItem, id: Date.now().toString() + '_' + i });
    }
  } else {
    currentPageOverlays.value.push({ ...stampItem, id: Date.now().toString() });
  }
};

const clearPdfOverlaysCache = () => {
  allOverlays.value = {};
  if (props.url && window.pdfOverlaysCache) {
    delete window.pdfOverlaysCache[props.url];
  }
};

const startResize = (e, item) => {
  resizedItem = item;
  startX = e.clientX;
  startY = e.clientY;
  startItemW = item.width;
  startItemH = item.height;
  
  window.addEventListener('mousemove', onDrag);
  window.addEventListener('mouseup', stopDrag);
};


const onDrag = (e) => {
  if (!overlayContainer.value) return;
  const rect = overlayContainer.value.getBoundingClientRect();
  
  if (draggedItem) {
    const dx = e.clientX - startX;
    const dy = e.clientY - startY;
    
    // Convert px movement to percentages
    const pctX = (dx / rect.width) * 100;
    const pctY = (dy / rect.height) * 100;
    
    draggedItem.x = startItemX + pctX;
    draggedItem.y = startItemY + pctY;
    
    // Bounds check
    if (draggedItem.x < 0) draggedItem.x = 0;
    if (draggedItem.y < 0) draggedItem.y = 0;
    
    // Let the user drag the top of the item all the way down to 98% of the document height.
    // This allows them to push images with transparent padding all the way to the visual bottom.
    // It will be masked by overflow-hidden if they push it too far.
    if (draggedItem.x + draggedItem.width > 105) draggedItem.x = 105 - draggedItem.width;
    if (draggedItem.y > 98) draggedItem.y = 98;
  }
  else if (resizedItem) {
    const dx = e.clientX - startX;
    const dy = e.clientY - startY;
    
    const pctW = (dx / rect.width) * 100;
    const pctH = (dy / rect.height) * 100;
    
    resizedItem.width = Math.max(5, startItemW + pctW); // Min 5% width
    if (resizedItem.type === 'text') {
       resizedItem.height = Math.max(5, startItemH + pctH);
    }
  }
};

const stopDrag = () => {
  draggedItem = null;
  resizedItem = null;
  window.removeEventListener('mousemove', onDrag);
  window.removeEventListener('mouseup', stopDrag);
};

// --- PDF Loader Logic ---
const loadPDF = async (url) => {
  if (!url) {
    loadError.value = 'No PDF URL provided.';
    loading.value = false;
    return;
  }
  loadError.value = '';
  loading.value = true;
  try {
    let targetUrl = url;
    if (typeof targetUrl === 'string' && (targetUrl.startsWith('http://') || targetUrl.startsWith('https://'))) {
      try {
        const parsed = new URL(targetUrl);
        if (parsed.hostname === window.location.hostname ||
            (parsed.hostname === 'localhost' && window.location.hostname === '127.0.0.1') ||
            (parsed.hostname === '127.0.0.1' && window.location.hostname === 'localhost')) {
          targetUrl = parsed.pathname + parsed.search;
        }
      } catch (e) {
        // use original url
      }
    } else if (typeof targetUrl === 'string' && !targetUrl.startsWith('/') && !targetUrl.startsWith('data:')) {
      targetUrl = '/' + targetUrl;
    }

    const pdfLib = await loadPdfJs();
    const loadingTask = pdfLib.getDocument({
      url: targetUrl,
      cMapUrl: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/cmaps/',
      cMapPacked: true,
      standardFontDataUrl: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/standard_fonts/'
    });
    pdfDoc.value = await loadingTask.promise;
    numPages.value = pdfDoc.value.numPages || 0;
    if (numPages.value <= 0) {
      loadError.value = 'The selected PDF document has 0 pages or contains no valid page tree.';
      loading.value = false;
      return;
    }
    pageNum.value = 1;
    inputPageNum.value = 1;
    await renderPage(pageNum.value);
  } catch (error) {
    console.error('Error loading PDF:', error);
    loadError.value = 'Could not load PDF document: ' + (error.message || 'Network or parse error.');
  } finally {
    loading.value = false;
  }
};

const renderPage = async (num) => {
  if (!pdfDoc.value || !pdfCanvas.value || !num || num < 1 || num > numPages.value) {
    loading.value = false;
    return;
  }
  
  loading.value = true;
  try {
    const page = await pdfDoc.value.getPage(num);
    const unscaledViewport = page.getViewport({ scale: 1.0 });
    
    if (scale.value === 1.0 && viewerContainer.value && viewerContainer.value.clientWidth > 100) {
       const containerWidth = viewerContainer.value.clientWidth - 40;
       if (unscaledViewport.width > containerWidth && containerWidth > 0) {
         scale.value = Math.max(0.2, containerWidth / unscaledViewport.width);
       }
    }

    const viewport = page.getViewport({ scale: scale.value });
    const canvas = pdfCanvas.value;
    const ctx = canvas.getContext('2d');

    const outputScale = window.devicePixelRatio || 1;
    canvas.width = Math.floor(viewport.width * outputScale);
    canvas.height = Math.floor(viewport.height * outputScale);
    canvas.style.width = Math.floor(viewport.width) + "px";
    canvas.style.height = Math.floor(viewport.height) + "px";

    const transform = outputScale !== 1 ? [outputScale, 0, 0, outputScale, 0, 0] : null;

    const renderContext = {
      canvasContext: ctx,
      transform: transform,
      viewport: viewport,
    };

    await page.render(renderContext).promise;
  } catch (error) {
    console.error('Error rendering page:', error);
    loadError.value = 'Error rendering page: ' + (error.message || 'Render failure.');
  } finally {
    loading.value = false;
  }
};

const prevPage = () => {
  if (pageNum.value <= 1) return;
  pageNum.value--;
  inputPageNum.value = pageNum.value;
  renderPage(pageNum.value);
};

const nextPage = () => {
  if (pageNum.value >= numPages.value) return;
  pageNum.value++;
  inputPageNum.value = pageNum.value;
  renderPage(pageNum.value);
};

const goToPage = (targetPage) => {
  let num = (typeof targetPage === 'number' || (typeof targetPage === 'string' && !isNaN(parseInt(targetPage))))
    ? parseInt(targetPage)
    : parseInt(inputPageNum.value);
  if (isNaN(num) || num < 1) num = 1;
  if (num > numPages.value) num = numPages.value;
  inputPageNum.value = num;
  pageNum.value = num;
  renderPage(pageNum.value);
};

const zoomIn = () => {
  scale.value = Math.min(scale.value + 0.25, 3.0);
  renderPage(pageNum.value);
};

const zoomOut = () => {
  scale.value = Math.max(scale.value - 0.25, 0.5);
  renderPage(pageNum.value);
};

const rotateRight = () => {
  rotation.value = (rotation.value + 90) % 360;
};

const getImageBytes = async (url) => {
  if (typeof url === 'string' && url.startsWith('data:')) {
    const base64Data = url.split(',')[1];
    const binaryString = window.atob(base64Data);
    const len = binaryString.length;
    const bytes = new Uint8Array(len);
    for (let i = 0; i < len; i++) {
      bytes[i] = binaryString.charCodeAt(i);
    }
    return bytes.buffer;
  }
  let fetchUrl = url;
  if (typeof url === 'string' && !url.startsWith('http://') && !url.startsWith('https://') && !url.startsWith('/')) {
    fetchUrl = '/' + url;
  }
  const cleanUrl = typeof fetchUrl === 'string' ? fetchUrl.replace(/([^:]\/)\/+/g, "$1") : fetchUrl;
  const response = await fetch(cleanUrl);
  if (!response.ok) {
    throw new Error(`Failed to fetch image: ${response.statusText}`);
  }
  return await response.arrayBuffer();
};

const generateModifiedPdf = async () => {
  isGenerating.value = true;
  try {
    // 1. Fetch original PDF
    const pdfBytes = await fetch(props.url).then(res => res.arrayBuffer());
    const pdfDoc = await PDFDocument.load(pdfBytes);
    const fontNormal = await pdfDoc.embedFont(StandardFonts.Helvetica);
    const fontBold = await pdfDoc.embedFont(StandardFonts.HelveticaBold);
    const fontItalic = await pdfDoc.embedFont(StandardFonts.HelveticaOblique);
    const fontBoldItalic = await pdfDoc.embedFont(StandardFonts.HelveticaBoldOblique);
    
    // 2. Apply overlays
    const pages = pdfDoc.getPages();

    for (const [pNum, overlays] of Object.entries(allOverlays.value)) {
      const pageIndex = parseInt(pNum) - 1;
      if (pageIndex < 0 || pageIndex >= pages.length || !overlays || overlays.length === 0) continue;
      
      const page = pages[pageIndex];
      let cropBox;
      try {
          cropBox = page.getCropBox();
      } catch (e) {
          cropBox = page.getSize();
          cropBox.x = 0;
          cropBox.y = 0;
      }
      const { x: cropX, y: cropY, width, height } = cropBox;

      for (const overlay of overlays) {
         const pxX = cropX + ((overlay.x / 100) * width);

         if (overlay.type === 'signature' && overlay.src) {
             let imgBytes;
             try {
                 imgBytes = await getImageBytes(overlay.src);
             } catch (eFetch) {
                 console.error('Failed to fetch signature image bytes:', eFetch);
                 continue;
             }

             let pdfImage;
             try {
                 const lowerSrc = (overlay.src || '').toLowerCase();
                 if (lowerSrc.includes('.png') || lowerSrc.startsWith('data:image/png')) {
                     try {
                         pdfImage = await pdfDoc.embedPng(imgBytes);
                     } catch (ePng) {
                         pdfImage = await pdfDoc.embedJpg(imgBytes);
                     }
                 } else {
                     try {
                         pdfImage = await pdfDoc.embedJpg(imgBytes);
                     } catch (eJpg) {
                         pdfImage = await pdfDoc.embedPng(imgBytes);
                     }
                 }
             } catch (eImg) {
                 console.error('Failed to embed signature into PDF:', eImg);
                 continue;
             }

             const boxWidth = (overlay.width / 100) * width;
             const boxHeight = (overlay.height / 100) * height;

             const imgAspectRatio = pdfImage.width / pdfImage.height;
             const boxAspectRatio = boxWidth / boxHeight;

             let drawWidth, drawHeight, offsetX = 0, offsetY = 0;

             if (imgAspectRatio > boxAspectRatio) {
                 drawWidth = boxWidth;
                 drawHeight = boxWidth / imgAspectRatio;
                 offsetY = (boxHeight - drawHeight) / 2;
             } else {
                 drawHeight = boxHeight;
                 drawWidth = boxHeight * imgAspectRatio;
                 offsetX = (boxWidth - drawWidth) / 2;
             }

             const containerBottomY = cropY + height - ((overlay.y / 100) * height) - boxHeight;
             const finalY = containerBottomY + offsetY;
             const finalX = pxX + offsetX;

             page.drawImage(pdfImage, {
                 x: finalX,
                 y: finalY,
                 width: drawWidth,
                 height: drawHeight
             });
         } else if (overlay.type === 'text' && overlay.content) {
             const fontSize = overlay.fontSize || 12;
             const pxY = cropY + height - ((overlay.y / 100) * height) - fontSize;
             const colorRgb = overlay.color ? rgb(overlay.color.r, overlay.color.g, overlay.color.b) : rgb(0, 0, 0);

             const isBold = !!overlay.bold || !!overlay.isStamp;
             const isItalic = !!overlay.italic;
             let selectedFont = fontNormal;
             if (isBold && isItalic) {
                 selectedFont = fontBoldItalic;
             } else if (isBold) {
                 selectedFont = fontBold;
             } else if (isItalic) {
                 selectedFont = fontItalic;
             }

             const lines = String(overlay.content).split('\n');
             const lineHeight = fontSize * 1.25;

             if (overlay.isStamp) {
                 const maxLineLen = Math.max(...lines.map(l => l.length));
                 const textWidth = maxLineLen * (fontSize * 0.65);
                 const totalHeight = lines.length * lineHeight;
                 page.drawRectangle({
                     x: pxX - 6,
                     y: pxY - 4 - ((lines.length - 1) * lineHeight),
                     width: textWidth + 12,
                     height: totalHeight + 8,
                     borderWidth: 2,
                     borderColor: colorRgb,
                     color: rgb(1, 1, 1),
                     opacity: 0.9
                 });
             }

             lines.forEach((line, lineIdx) => {
                 page.drawText(line, {
                     x: pxX,
                     y: pxY - (lineIdx * lineHeight),
                     size: fontSize,
                     font: selectedFont,
                     color: colorRgb
                 });
             });
         } else if (overlay.type === 'redact') {
             const pxY = cropY + height - ((overlay.y / 100) * height) - ((overlay.height / 100) * height);
             page.drawRectangle({
                 x: pxX,
                 y: pxY,
                 width: (overlay.width / 100) * width,
                 height: (overlay.height / 100) * height,
                 color: rgb(0, 0, 0)
             });
         } else if (overlay.type === 'highlight') {
             const pxY = cropY + height - ((overlay.y / 100) * height) - ((overlay.height / 100) * height);
             page.drawRectangle({
                 x: pxX,
                 y: pxY,
                 width: (overlay.width / 100) * width,
                 height: (overlay.height / 100) * height,
                 color: rgb(1, 1, 0),
                 opacity: 0.4
             });
         }
      }
    }
    
    // 3. Save and return URL
    const pdfBytesModified = await pdfDoc.save();
    const blob = new Blob([pdfBytesModified], { type: 'application/pdf' });
    return { url: URL.createObjectURL(blob), blob };
    
  } catch (error) {
    console.error("Error generating PDF:", error);
    return { url: props.url, blob: null };
  } finally {
    isGenerating.value = false;
  }
};

const printPdf = async () => {
  const result = await generateModifiedPdf();
  printUrl.value = result.url ? result.url : result;
};

const onPrintIframeLoad = () => {
  if (printIframe.value && printIframe.value.contentWindow) {
    try {
      printIframe.value.contentWindow.print();
    } catch (e) {
      console.error("Print failed:", e);
    }
    setTimeout(() => { printUrl.value = ''; }, 1000);
  }
};

watch(() => props.url, (newUrl, oldUrl) => {
  if (oldUrl) {
    window.pdfOverlaysCache = window.pdfOverlaysCache || {};
    window.pdfOverlaysCache[oldUrl] = JSON.parse(JSON.stringify(allOverlays.value));
  }
  if (newUrl) {
    scale.value = 1.0;
    rotation.value = 0;
    window.pdfOverlaysCache = window.pdfOverlaysCache || {};
    allOverlays.value = window.pdfOverlaysCache[newUrl] || {};
    loadPDF(newUrl);
  }
});

const onJumpToPage = (e) => {
  if (e?.detail?.page) {
    goToPage(e.detail.page);
  }
};

onMounted(() => {
  if (props.url) {
    window.pdfOverlaysCache = window.pdfOverlaysCache || {};
    if (window.pdfOverlaysCache[props.url]) {
       allOverlays.value = window.pdfOverlaysCache[props.url];
    }
    loadPDF(props.url);
  }
  window.addEventListener('mouseup', stopDrag);
  window.addEventListener('jump-to-pdf-page', onJumpToPage);
});

onUnmounted(() => {
  if (props.url) {
    window.pdfOverlaysCache = window.pdfOverlaysCache || {};
    window.pdfOverlaysCache[props.url] = JSON.parse(JSON.stringify(allOverlays.value));
  }
  window.removeEventListener('mouseup', stopDrag);
  window.removeEventListener('jump-to-pdf-page', onJumpToPage);
});

defineExpose({
  getPdfBlob,
  hasModifications,
  generateModifiedPdf,
  savePdf,
  allOverlays,
  clearAll,
  clearPdfOverlaysCache,
  goToPage,
  pageNum,
  numPages
});
</script>
