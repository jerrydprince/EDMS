<template>
  <div class="p-6 lg:p-8 max-w-5xl">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-200 dark:border-gray-800">
      <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-primary/10 dark:bg-primary/20 flex items-center justify-center text-primary dark:text-emerald-400">
          <Icon icon="lucide:user" class="text-2xl" />
        </div>
        <div>
          <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Profile Settings</h1>
          <p class="text-sm text-slate-500 dark:text-gray-400">Manage your identity, approval credentials, and digital signature.</p>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <button
          type="button"
          @click="resetForm"
          class="px-4 py-2 text-xs font-semibold rounded-xl border border-slate-200 dark:border-gray-700 text-slate-600 dark:text-gray-300 hover:bg-slate-100 dark:hover:bg-gray-800 transition"
        >
          Reset
        </button>
        <button
          type="button"
          @click="saveProfile"
          :disabled="form.processing"
          class="px-5 py-2 text-xs font-semibold rounded-xl bg-primary hover:bg-primary-dark text-white shadow-sm shadow-primary/20 transition disabled:opacity-50 flex items-center gap-2"
        >
          <Icon v-if="form.processing" icon="lucide:loader-2" class="animate-spin text-sm" />
          <span>{{ form.processing ? 'Saving Changes...' : 'Save Profile' }}</span>
        </button>
      </div>
    </div>

    <!-- Official Employment Badge Overview -->
    <div class="my-6 p-5 rounded-2xl bg-gradient-to-r from-slate-50 via-indigo-50/30 to-slate-50 dark:from-gray-800/60 dark:via-gray-800/40 dark:to-gray-800/60 border border-slate-200/80 dark:border-gray-700">
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
          <div class="w-10 h-10 rounded-xl bg-primary/10 dark:bg-primary/20 text-primary dark:text-emerald-400 flex items-center justify-center font-bold">
            <Icon icon="lucide:badge-check" class="text-xl" />
          </div>
          <div>
            <div class="flex items-center gap-2">
              <span class="text-sm font-bold text-slate-900 dark:text-white">
                {{ props.user.first_name }} {{ props.user.last_name }}
              </span>
              <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                Active Staff
              </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-gray-400">
              Staff ID: <span class="font-mono font-medium text-slate-700 dark:text-gray-300">{{ formattedStaffId }}</span>
              <span v-if="designationTitle" class="ml-2 font-medium text-slate-700 dark:text-gray-300">| {{ designationTitle }}</span>
            </p>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 text-xs">
          <span v-if="departmentTitle" class="px-2.5 py-1 rounded-lg bg-white dark:bg-gray-700 border border-slate-200 dark:border-gray-600 text-slate-600 dark:text-gray-200 font-medium">
            Dept: {{ departmentTitle }}
          </span>
          <span v-if="divisionTitle" class="px-2.5 py-1 rounded-lg bg-white dark:bg-gray-700 border border-slate-200 dark:border-gray-600 text-slate-600 dark:text-gray-200 font-medium">
            Div: {{ divisionTitle }}
          </span>
          <span v-if="unitTitle" class="px-2.5 py-1 rounded-lg bg-white dark:bg-gray-700 border border-slate-200 dark:border-gray-600 text-slate-600 dark:text-gray-200 font-medium">
            Unit: {{ unitTitle }}
          </span>
        </div>
      </div>
    </div>

    <!-- Form Section -->
    <div class="space-y-6">

      <!-- Profile Avatar -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center p-4 rounded-xl hover:bg-slate-50/50 dark:hover:bg-gray-800/40 transition">
        <div>
          <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Profile Photo</h2>
          <p class="text-xs text-slate-500 dark:text-gray-400">Min 400x400px. JPG, PNG or WEBP.</p>
        </div>
        <div class="md:col-span-2 flex items-center gap-5">
          <div class="relative group cursor-pointer" @click="triggerAvatarUpload">
            <img 
              :src="avatarUrl || '/images/default-avatar.png'" 
              alt="Avatar" 
              class="w-20 h-20 rounded-2xl object-cover border-2 border-slate-200 dark:border-gray-700 shadow-sm"
            />
            <div class="absolute inset-0 bg-black/40 rounded-2xl flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity text-white">
              <Icon icon="lucide:camera" class="text-lg" />
            </div>
          </div>
          <div class="flex flex-col gap-2">
            <button 
              type="button" 
              @click="triggerAvatarUpload" 
              class="px-3.5 py-1.5 text-xs font-semibold rounded-xl bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 hover:bg-slate-50 dark:hover:bg-gray-700 transition"
            >
              Upload New Photo
            </button>
            <input 
              type="file" 
              ref="avatarInput" 
              @change="handleAvatarFile" 
              accept="image/*" 
              class="hidden" 
            />
            <span class="text-[11px] text-slate-400">Recommended square ratio for official files</span>
          </div>
        </div>
      </div>

      <div class="h-px bg-slate-200/80 dark:bg-gray-800"></div>

      <!-- Full Legal Name (Read Only) -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center p-4 rounded-xl">
        <div>
          <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Full Legal Name</h2>
          <p class="text-xs text-slate-500 dark:text-gray-400">Synchronized with HR & Payroll records.</p>
        </div>
        <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block text-[11px] text-slate-400 mb-1">First Name</label>
            <input 
              type="text" 
              :value="props.user.first_name" 
              disabled 
              class="w-full px-3.5 py-2 text-xs bg-slate-100 dark:bg-gray-800/60 border border-slate-200 dark:border-gray-700 rounded-xl text-slate-600 dark:text-gray-400 cursor-not-allowed"
            />
          </div>
          <div>
            <label class="block text-[11px] text-slate-400 mb-1">Last / Surname</label>
            <input 
              type="text" 
              :value="props.user.last_name" 
              disabled 
              class="w-full px-3.5 py-2 text-xs bg-slate-100 dark:bg-gray-800/60 border border-slate-200 dark:border-gray-700 rounded-xl text-slate-600 dark:text-gray-400 cursor-not-allowed"
            />
          </div>
        </div>
      </div>

      <div class="h-px bg-slate-200/80 dark:bg-gray-800"></div>

      <!-- Email Address -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center p-4 rounded-xl">
        <div>
          <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Official Email Address</h2>
          <p class="text-xs text-slate-500 dark:text-gray-400">Primary communication address.</p>
        </div>
        <div class="md:col-span-2 flex items-center gap-3">
          <div class="relative flex-1">
            <Icon icon="lucide:mail" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm" />
            <input 
              type="email" 
              :value="props.user.email" 
              disabled 
              class="w-full pl-9 pr-3.5 py-2 text-xs bg-slate-100 dark:bg-gray-800/60 border border-slate-200 dark:border-gray-700 rounded-xl text-slate-600 dark:text-gray-400 cursor-not-allowed"
            />
          </div>
          <button 
            type="button" 
            @click="EmailModal = true"
            class="px-3 py-2 rounded-xl text-xs font-semibold bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800 hover:bg-amber-100 transition flex items-center gap-1.5"
            title="Verify or update official email"
          >
            <Icon icon="lucide:check-circle" class="text-sm" />
            <span>Verify</span>
          </button>
        </div>
      </div>

      <div class="h-px bg-slate-200/80 dark:bg-gray-800"></div>

      <!-- Phone Number (Editable) -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center p-4 rounded-xl hover:bg-slate-50/50 dark:hover:bg-gray-800/40 transition">
        <div>
          <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Phone Number</h2>
          <p class="text-xs text-slate-500 dark:text-gray-400">Used for SMS dispatch and emergency approvals.</p>
        </div>
        <div class="md:col-span-2 flex items-center gap-2">
          <div class="relative flex-1">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-mono font-semibold text-slate-500">+234</span>
            <input 
              type="text" 
              v-model="phoneValue" 
              placeholder="e.g. 8031234567" 
              class="w-full pl-14 pr-3.5 py-2 text-xs bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl text-slate-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-mono"
            />
          </div>
          <button 
            type="button" 
            @click="savePhone" 
            :disabled="savingPhone"
            class="px-4 py-2 text-xs font-semibold rounded-xl bg-slate-800 hover:bg-slate-900 text-white dark:bg-gray-700 dark:hover:bg-gray-600 transition disabled:opacity-50"
          >
            {{ savingPhone ? 'Updating...' : 'Update Phone' }}
          </button>
        </div>
      </div>

      <div class="h-px bg-slate-200/80 dark:bg-gray-800"></div>

      <!-- Email Client Password -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center p-4 rounded-xl hover:bg-slate-50/50 dark:hover:bg-gray-800/40 transition">
        <div>
          <h2 class="text-sm font-semibold text-slate-900 dark:text-white">In-App Mail Password</h2>
          <p class="text-xs text-slate-500 dark:text-gray-400">Used for background IMAP/SMTP mail fetching.</p>
        </div>
        <div class="md:col-span-2 relative">
          <Icon icon="lucide:key" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm" />
          <input 
            :type="showPassword ? 'text' : 'password'" 
            v-model="form.email_password" 
            placeholder="Enter mailbox app password"
            class="w-full pl-9 pr-10 py-2 text-xs bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl text-slate-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-mono"
          />
          <button 
            type="button" 
            @click="showPassword = !showPassword"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-gray-300"
          >
            <Icon :icon="showPassword ? 'lucide:eye-off' : 'lucide:eye'" class="text-sm" />
          </button>
        </div>
      </div>

      <div class="h-px bg-slate-200/80 dark:bg-gray-800"></div>

      <!-- Approval PIN -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center p-4 rounded-xl hover:bg-slate-50/50 dark:hover:bg-gray-800/40 transition">
        <div>
          <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Approval Security PIN</h2>
          <p class="text-xs text-slate-500 dark:text-gray-400">4-10 digits required when approving workflow tasks.</p>
        </div>
        <div class="md:col-span-2 relative">
          <Icon icon="lucide:shield-alert" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm" />
          <input 
            :type="showPin ? 'text' : 'password'" 
            v-model="form.approval_pin" 
            placeholder="Enter 4-10 digit approval PIN"
            maxlength="10"
            class="w-full pl-9 pr-10 py-2 text-xs bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl text-slate-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-mono tracking-widest"
          />
          <button 
            type="button" 
            @click="showPin = !showPin"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-gray-300"
          >
            <Icon :icon="showPin ? 'lucide:eye-off' : 'lucide:eye'" class="text-sm" />
          </button>
        </div>
      </div>

      <div class="h-px bg-slate-200/80 dark:bg-gray-800"></div>

      <!-- Digital Signature & Canvas Pad -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start p-4 rounded-xl hover:bg-slate-50/50 dark:hover:bg-gray-800/40 transition">
        <div>
          <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Digital Signature</h2>
          <p class="text-xs text-slate-500 dark:text-gray-400">Applied automatically to official approvals, memos, and certificates.</p>
        </div>

        <div class="md:col-span-2 space-y-4">
          <!-- Current Signature Display -->
          <div class="flex items-center gap-4">
            <div class="h-24 w-52 rounded-xl border-2 border-dashed border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800/60 flex items-center justify-center p-2 overflow-hidden shadow-inner">
              <img 
                v-if="sigUrl && !sigError" 
                :src="sigUrl" 
                alt="Digital Signature" 
                @error="sigError = true"
                class="max-h-full max-w-full object-contain filter dark:brightness-110"
              />
              <div v-else class="text-center text-xs text-slate-400 flex flex-col items-center gap-1">
                <Icon icon="lucide:pen-tool" class="text-lg opacity-40" />
                <span>{{ sigError ? 'Signature file missing' : 'No signature saved' }}</span>
              </div>
            </div>

            <div class="flex flex-col gap-2">
              <button 
                type="button" 
                @click="openSignaturePadModal"
                class="px-4 py-2 text-xs font-semibold rounded-xl bg-primary text-white hover:bg-primary-dark transition flex items-center gap-1.5 shadow-xs"
              >
                <Icon icon="lucide:pen-line" class="text-sm" />
                <span>Draw on Screen</span>
              </button>

              <button 
                type="button" 
                @click="triggerSignatureUpload"
                class="px-4 py-2 text-xs font-semibold rounded-xl bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-300 hover:bg-slate-50 dark:hover:bg-gray-700 transition flex items-center gap-1.5"
              >
                <Icon icon="lucide:upload" class="text-sm" />
                <span>Upload PNG File</span>
              </button>

              <input 
                type="file" 
                ref="signatureInput" 
                @change="handleSignatureUpload" 
                accept="image/png,image/jpeg" 
                class="hidden" 
              />
            </div>
          </div>
          <p class="text-[11px] text-slate-400">
            For best document reproduction, use a clean transparent PNG or draw directly using the signature pad.
          </p>
        </div>
      </div>

    </div>

    <!-- Interactive Digital Signature Canvas Modal -->
    <div 
      v-if="showSignaturePad" 
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4"
    >
      <div class="bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-800 rounded-3xl w-full max-w-xl p-6 shadow-2xl">
        <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-gray-800">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
              <Icon icon="lucide:pen-tool" class="text-lg" />
            </div>
            <div>
              <h3 class="text-base font-bold text-slate-900 dark:text-white">Draw Digital Signature</h3>
              <p class="text-xs text-slate-500">Sign cleanly within the box using your mouse, trackpad or stylus.</p>
            </div>
          </div>
          <button 
            type="button" 
            @click="showSignaturePad = false" 
            class="text-slate-400 hover:text-slate-600 dark:hover:text-gray-300"
          >
            <Icon icon="lucide:x" class="text-lg" />
          </button>
        </div>

        <!-- Controls: Color & Stroke -->
        <div class="flex items-center justify-between py-3">
          <div class="flex items-center gap-2">
            <span class="text-xs text-slate-500 font-medium">Ink:</span>
            <button 
              type="button"
              @click="penColor = '#0F172A'"
              class="w-6 h-6 rounded-full border-2 transition"
              :class="penColor === '#0F172A' ? 'border-primary ring-2 ring-primary/20' : 'border-transparent'"
              style="background-color: #0F172A;"
              title="Midnight Black"
            ></button>
            <button 
              type="button"
              @click="penColor = '#1E40AF'"
              class="w-6 h-6 rounded-full border-2 transition"
              :class="penColor === '#1E40AF' ? 'border-primary ring-2 ring-primary/20' : 'border-transparent'"
              style="background-color: #1E40AF;"
              title="Executive Blue"
            ></button>
          </div>

          <div class="flex items-center gap-2">
            <button 
              type="button" 
              @click="clearCanvas"
              class="px-3 py-1.5 text-xs font-semibold rounded-lg text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition flex items-center gap-1"
            >
              <Icon icon="lucide:trash-2" class="text-xs" />
              <span>Clear Canvas</span>
            </button>
          </div>
        </div>

        <!-- Drawing Canvas Area -->
        <div class="relative rounded-2xl border-2 border-slate-300 dark:border-gray-700 bg-white overflow-hidden shadow-inner cursor-crosshair">
          <canvas 
            ref="sigCanvas" 
            width="520" 
            height="220" 
            class="w-full h-56 block touch-none"
            @mousedown="startDrawing"
            @mousemove="draw"
            @mouseup="stopDrawing"
            @mouseleave="stopDrawing"
            @touchstart.prevent="startTouchDrawing"
            @touchmove.prevent="touchDraw"
            @touchend="stopDrawing"
          ></canvas>
          <div class="absolute bottom-3 left-4 pointer-events-none text-[11px] text-slate-300 select-none flex items-center gap-1">
            <Icon icon="lucide:pen-line" class="text-xs" />
            <span>Sign on the baseline</span>
          </div>
        </div>

        <!-- Modal Actions -->
        <div class="flex items-center justify-end gap-3 mt-5 pt-4 border-t border-slate-200 dark:border-gray-800">
          <button 
            type="button" 
            @click="showSignaturePad = false"
            class="px-4 py-2 text-xs font-semibold rounded-xl border border-slate-200 dark:border-gray-700 text-slate-600 dark:text-gray-300 hover:bg-slate-100 dark:hover:bg-gray-800 transition"
          >
            Cancel
          </button>
          <button 
            type="button" 
            @click="saveDrawnSignature"
            :disabled="savingDrawnSig"
            class="px-5 py-2 text-xs font-semibold rounded-xl bg-primary hover:bg-primary-dark text-white shadow-sm transition disabled:opacity-50 flex items-center gap-1.5"
          >
            <Icon v-if="savingDrawnSig" icon="lucide:loader-2" class="animate-spin text-sm" />
            <span>{{ savingDrawnSig ? 'Applying Signature...' : 'Save Signature' }}</span>
          </button>
        </div>
      </div>
    </div>

    <VerifyEmail v-model="EmailModal" :email="props.user.email" />
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { useToast } from "primevue/usetoast";
import axios from 'axios';
import VerifyEmail from '../../../components/Modals/VerifyEmail.vue';

const props = defineProps({
  user: {
    type: Object,
    required: true
  }
});

const page = usePage();
const toast = useToast();
const EmailModal = ref(false);

const avatarInput = ref(null);
const signatureInput = ref(null);
const sigCanvas = ref(null);

const avatarUrl = ref('');
const sigUrl = ref('');
const sigError = ref(false);
const showPassword = ref(false);
const showPin = ref(false);
const phoneValue = ref(props.user.phone || '');
const savingPhone = ref(false);

const resolveImageUrl = (path) => {
  if (!path) return '';
  if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('blob:') || path.startsWith('data:')) {
    return path;
  }
  return '/' + path.replace(/^\/+/, '');
};

const departmentTitle = computed(() => {
  const d = props.user?.department;
  if (!d) return null;
  return typeof d === 'object' ? (d.title || d.name || null) : d;
});

const divisionTitle = computed(() => {
  const d = props.user?.division;
  if (!d) return null;
  return typeof d === 'object' ? (d.title || d.name || null) : d;
});

const unitTitle = computed(() => {
  const u = props.user?.unit;
  if (!u) return null;
  return typeof u === 'object' ? (u.title || u.name || null) : u;
});

const designationTitle = computed(() => {
  const des = props.user?.designation;
  if (!des) return null;
  return typeof des === 'object' ? (des.name || des.title || null) : des;
});

const formattedStaffId = computed(() => {
  if (props.user?.staff_id && props.user.staff_id !== 'N/A') return props.user.staff_id;
  if (props.user?.id) {
    return 'OAGF/' + (props.user.id < 10 ? '00' : '0') + props.user.id;
  }
  return 'OAGF/0001';
});

// Signature Pad Canvas State
const showSignaturePad = ref(false);
const isDrawing = ref(false);
const penColor = ref('#0F172A');
const savingDrawnSig = ref(false);
let ctx = null;

const form = useForm({
  file: null,
  signature_file: null,
  approval_pin: props.user.approval_pin || '',
  email_password: props.user.email_password || ''
});

const roles = computed(() => page.props.user_roles || []);

const triggerAvatarUpload = () => {
  if (avatarInput.value) avatarInput.value.click();
};

const handleAvatarFile = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.file = file;
    avatarUrl.value = URL.createObjectURL(file);
  }
};

const triggerSignatureUpload = () => {
  if (signatureInput.value) signatureInput.value.click();
};

const handleSignatureUpload = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.signature_file = file;
    sigUrl.value = URL.createObjectURL(file);
    toast.add({
      severity: 'info',
      summary: 'Signature Loaded',
      detail: 'Click "Save Profile" to finalize and store your signature file.',
      life: 3000
    });
  }
};

// Canvas Drawing functions
const openSignaturePadModal = () => {
  showSignaturePad.value = true;
  nextTick(() => {
    initCanvas();
  });
};

const initCanvas = () => {
  if (!sigCanvas.value) return;
  ctx = sigCanvas.value.getContext('2d');
  ctx.strokeStyle = penColor.value;
  ctx.lineWidth = 2.5;
  ctx.lineCap = 'round';
  ctx.lineJoin = 'round';
};

const getCanvasCoordinates = (e) => {
  const rect = sigCanvas.value.getBoundingClientRect();
  const scaleX = sigCanvas.value.width / rect.width;
  const scaleY = sigCanvas.value.height / rect.height;
  return {
    x: (e.clientX - rect.left) * scaleX,
    y: (e.clientY - rect.top) * scaleY
  };
};

const startDrawing = (e) => {
  isDrawing.value = true;
  if (!ctx) initCanvas();
  ctx.strokeStyle = penColor.value;
  const { x, y } = getCanvasCoordinates(e);
  ctx.beginPath();
  ctx.moveTo(x, y);
};

const draw = (e) => {
  if (!isDrawing.value) return;
  const { x, y } = getCanvasCoordinates(e);
  ctx.lineTo(x, y);
  ctx.stroke();
};

const stopDrawing = () => {
  if (isDrawing.value) {
    ctx.closePath();
    isDrawing.value = false;
  }
};

const startTouchDrawing = (e) => {
  if (e.touches.length > 0) {
    const touch = e.touches[0];
    startDrawing(touch);
  }
};

const touchDraw = (e) => {
  if (e.touches.length > 0) {
    const touch = e.touches[0];
    draw(touch);
  }
};

const clearCanvas = () => {
  if (!ctx || !sigCanvas.value) return;
  ctx.clearRect(0, 0, sigCanvas.value.width, sigCanvas.value.height);
};

const saveDrawnSignature = async () => {
  if (!sigCanvas.value) return;
  const dataUrl = sigCanvas.value.toDataURL('image/png');
  
  savingDrawnSig.value = true;
  try {
    const response = await axios.post('/settings/signature-pad', { signature_base64: dataUrl });
    if (response.data.success) {
      sigUrl.value = response.data.signature_url;
      showSignaturePad.value = false;
      toast.add({
        severity: 'success',
        summary: 'Signature Saved',
        detail: 'Digital signature has been stored and activated for approvals.',
        life: 3500
      });
    }
  } catch (err) {
    toast.add({
      severity: 'error',
      summary: 'Signature Error',
      detail: err.response?.data?.message || 'Failed to save digital signature.',
      life: 3500
    });
  } finally {
    savingDrawnSig.value = false;
  }
};

// Phone Update
const savePhone = async () => {
  if (!phoneValue.value) return;
  savingPhone.value = true;
  try {
    const response = await axios.post('/settings/update-phone', { phone: phoneValue.value });
    if (response.data.success) {
      toast.add({
        severity: 'success',
        summary: 'Phone Updated',
        detail: 'Your contact telephone number was updated successfully.',
        life: 3000
      });
    }
  } catch (err) {
    toast.add({
      severity: 'error',
      summary: 'Update Failed',
      detail: err.response?.data?.message || 'Could not update telephone number.',
      life: 3000
    });
  } finally {
    savingPhone.value = false;
  }
};

const resetForm = () => {
  form.approval_pin = props.user.approval_pin || '';
  form.email_password = props.user.email_password || '';
  phoneValue.value = props.user.phone || '';
  if (props.user.profile_image) {
    avatarUrl.value = page.props.base + '/' + props.user.profile_image;
  }
  if (props.user.digital_signature_path) {
    sigUrl.value = page.props.base + '/' + props.user.digital_signature_path;
  }
};

const saveProfile = () => {
  let url = roles.value.includes('record manager') 
    ? '/edms/update-profile-image' 
    : '/dashboard/update-profile-image';

  form.post(url, {
    preserveScroll: true,
    onSuccess: () => {
      toast.add({
        severity: 'success',
        summary: 'Profile Updated',
        detail: 'Your profile settings and credentials have been updated successfully.',
        life: 3000
      });
    },
    onError: () => {
      toast.add({
        severity: 'error',
        summary: 'Validation Error',
        detail: 'Please check your inputs and try again.',
        life: 3000
      });
    }
  });
};

watch(() => props.user, (user) => {
  if (user) {
    if (user.profile_image) {
      avatarUrl.value = resolveImageUrl(user.profile_image);
    }
    if (user.digital_signature_path) {
      sigError.value = false;
      sigUrl.value = resolveImageUrl(user.digital_signature_path);
    }
    phoneValue.value = user.phone || '';
  }
}, { immediate: true });
</script>
