<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-xs p-4">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-4xl w-full overflow-hidden flex flex-col max-h-[95vh] animate-in fade-in zoom-in duration-150">
      <!-- Modal Top Action Bar -->
      <div class="bg-slate-900 text-white p-4 flex items-center justify-between border-b border-slate-800 print:hidden">
        <div class="flex items-center gap-3">
          <div class="p-2 rounded-lg bg-blue-500/20 text-blue-300">
            <Icon icon="lucide:file-text" width="18" />
          </div>
          <div>
            <h3 class="font-bold text-sm tracking-tight">Statutory Civil Service Document Generator</h3>
            <p class="text-[11px] text-slate-400">Department 14 SOP • Official Federal Treasury Layout</p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <button
            @click="triggerPrint"
            class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold rounded-lg shadow-sm transition-all flex items-center gap-1.5 cursor-pointer"
          >
            <Icon icon="lucide:printer" width="14" />
            <span>Print Official Form</span>
          </button>
          <button
            @click="$emit('close')"
            class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition-colors cursor-pointer"
          >
            <Icon icon="lucide:x" width="18" />
          </button>
        </div>
      </div>

      <!-- Printable Document Canvas -->
      <div id="printArea" class="p-8 md:p-12 overflow-y-auto flex-1 bg-white font-serif text-slate-900 print:p-0 print:m-0">
        <!-- 1. POSTING ORDER (FORM OAGF/POOL/01) -->
        <div v-if="docType === 'posting_order' && docData" class="max-w-2xl mx-auto space-y-6">
          <!-- Federal Letterhead -->
          <div class="text-center border-b-2 border-slate-900 pb-4">
            <div class="text-xs uppercase tracking-widest font-sans font-bold text-slate-600">Federal Republic of Nigeria</div>
            <h1 class="text-base md:text-lg font-black uppercase tracking-wider text-slate-900 mt-0.5">
              Office of the Accountant-General of the Federation
            </h1>
            <div class="text-xs font-sans text-slate-700 font-semibold">Department of Administration & Human Resources</div>
            <div class="text-[11px] font-sans text-slate-500">National Pool Management Division • Treasury House, Garki II, Abuja</div>
            <div class="mt-2 inline-block px-3 py-0.5 border border-slate-900 text-xs font-sans font-black uppercase tracking-wider">
              Form OAGF/POOL/01 (Statutory Posting Order)
            </div>
          </div>

          <!-- Ref & Date Bar -->
          <div class="flex justify-between items-center text-xs font-sans border-b border-slate-300 pb-2">
            <div>
              <span class="text-slate-500">Ref: </span>
              <strong class="font-mono text-slate-900">{{ docData.posting_ref || 'OAGF/POOL/2026/001' }}</strong>
            </div>
            <div>
              <span class="text-slate-500">Date: </span>
              <strong class="text-slate-900">{{ formatDate(docData.effective_date) }}</strong>
            </div>
          </div>

          <!-- Addressee Officer -->
          <div class="text-xs leading-relaxed font-sans">
            <div><strong>To: </strong>{{ docData.pool_officer?.first_name }} {{ docData.pool_officer?.last_name }}</div>
            <div><strong>Cadre / Grade Level: </strong>{{ docData.pool_officer?.cadre }} ({{ docData.pool_officer?.grade_level }})</div>
            <div><strong>IPPIS Number: </strong><span class="font-mono">{{ docData.pool_officer?.ippis_no }}</span></div>
            <div><strong>Present Station: </strong>{{ docData.from_mda_name }}</div>
          </div>

          <!-- Subject Banner -->
          <div class="text-center py-2 px-3 bg-slate-100 border-y border-slate-300 text-xs font-bold uppercase tracking-wide font-sans">
            Letter of Posting: Deployment to Federal Treasury Duty Post
          </div>

          <!-- Letter Body -->
          <div class="text-xs space-y-3 leading-relaxed text-justify">
            <p>
              I am directed to convey the statutory approval of the
              <strong>{{ docData.approval_authority === 'agf' ? 'Accountant-General of the Federation' : 'Director of Administration' }}</strong>
              for your immediate deployment from <strong>{{ docData.from_mda_name }}</strong> to:
            </p>

            <div class="p-3 bg-slate-50 border border-slate-300 text-center font-sans">
              <div class="text-sm font-black text-slate-900 uppercase tracking-wide">{{ docData.to_mda_name }}</div>
              <div class="text-[11px] text-slate-600 mt-0.5">Effective Date: {{ formatDate(docData.effective_date) }}</div>
            </div>

            <p>
              This posting is executed in accordance with Public Service Rules (PSR), Federal Civil Service Commission guidelines, and the extant Treasury Pool Posting rotation policy (SOP Department 14).
            </p>

            <p class="font-semibold text-slate-800">
              MANDATORY COMPLIANCE TIMELINE (14-DAY RULE):<br />
              In compliance with established Treasury regulations, you are required to hand over your current duties, report to your new station, and return your completed
              <strong>Assumption of Duty Certificate (Form OAGF/POOL/02)</strong>
              countersigned by the Director/CFO of your receiving MDA to the Director of Administration, OAGF, on or before
              <strong>{{ formatDate(docData.assumption_due_date) }}</strong>.
            </p>

            <p>
              Failure to assume duty within the prescribed 14-day statutory window without formal written dispensation shall constitute Absence from Duty without Leave (Dereliction of Duty) under PSR 030402.
            </p>
          </div>

          <!-- Signatory & Seal Row -->
          <div class="pt-6 flex justify-between items-end font-sans">
            <!-- Stamp Visual -->
            <div class="w-36 h-36 flex items-center justify-center">
              <div v-if="stampSvg" v-html="stampSvg" class="w-36 h-36"></div>
              <div v-else class="w-32 h-32 rounded-full border-2 border-dashed border-blue-900 flex items-center justify-center text-[10px] text-blue-900 font-bold text-center p-2">
                OFFICIAL SEAL OF THE DIRECTOR OF ADMINISTRATION
              </div>
            </div>

            <!-- Signature Line -->
            <div class="text-right text-xs">
              <div class="h-10 border-b border-slate-400 w-48 ml-auto"></div>
              <div class="font-bold text-slate-900 mt-1">For: Accountant-General of the Federation</div>
              <div class="text-slate-600 font-semibold">Director, Administration & HR</div>
              <div class="text-[10px] text-slate-500">Federal Treasury of Nigeria</div>
            </div>
          </div>
        </div>

        <!-- 2. ASSUMPTION SLIP (FORM OAGF/POOL/02) -->
        <div v-else-if="docType === 'assumption_slip' && docData" class="max-w-2xl mx-auto space-y-6">
          <div class="text-center border-b-2 border-slate-900 pb-4">
            <div class="text-xs uppercase tracking-widest font-sans font-bold text-slate-600">Federal Republic of Nigeria</div>
            <h1 class="text-base md:text-lg font-black uppercase tracking-wider text-slate-900 mt-0.5">
              Office of the Accountant-General of the Federation
            </h1>
            <div class="text-xs font-sans text-slate-700 font-semibold">Federal Accountants Pool Management Division</div>
            <div class="mt-2 inline-block px-3 py-0.5 border border-slate-900 text-xs font-sans font-black uppercase tracking-wider">
              Form OAGF/POOL/02 (Assumption of Duty Certificate)
            </div>
          </div>

          <div class="text-xs font-sans space-y-1">
            <div><strong>To: </strong>The Director of Administration, Office of the Accountant-General of the Federation, Abuja</div>
            <div><strong>Reference Posting Order: </strong><span class="font-mono">{{ docData.posting_ref }}</span></div>
          </div>

          <div class="p-4 bg-slate-50 border border-slate-300 text-xs space-y-3 font-sans">
            <p>
              I hereby certify that <strong>{{ docData.pool_officer?.first_name }} {{ docData.pool_officer?.last_name }}</strong>
              (IPPIS: <span class="font-mono font-bold">{{ docData.pool_officer?.ippis_no }}</span>),
              posted from <strong>{{ docData.from_mda_name }}</strong>, has duly reported and assumed official duties at:
            </p>
            <div class="font-bold text-sm text-slate-900 uppercase">{{ docData.to_mda_name }}</div>
            <div><strong>Actual Date of Assumption: </strong>{{ formatDate(docData.assumption_slip_returned_at || docData.effective_date) }}</div>
          </div>

          <div class="pt-8 grid grid-cols-2 gap-8 text-xs font-sans">
            <div>
              <div class="h-10 border-b border-slate-400"></div>
              <div class="font-bold mt-1">Signature of Posted Officer</div>
              <div class="text-[11px] text-slate-500">Date: ________________________</div>
            </div>
            <div class="text-right">
              <div class="h-10 border-b border-slate-400"></div>
              <div class="font-bold mt-1">Director of Finance & Accounts / CFO</div>
              <div class="text-[11px] text-slate-500">Official MDA Stamp & Date</div>
            </div>
          </div>
        </div>

        <!-- 3. APPENDIX A: FILE MOVEMENT CARD -->
        <div v-else-if="docType === 'movement_card' && docData" class="max-w-2xl mx-auto space-y-6">
          <div class="text-center border-b-2 border-slate-900 pb-4">
            <div class="text-xs uppercase tracking-widest font-sans font-bold text-slate-600">Federal Republic of Nigeria</div>
            <h1 class="text-base md:text-lg font-black uppercase tracking-wider text-slate-900 mt-0.5">
              Office of the Accountant-General of the Federation
            </h1>
            <div class="text-xs font-sans text-slate-700 font-semibold">Department 14 Sub-Registry • Treasury File Transit Card</div>
            <div class="mt-2 inline-block px-3 py-0.5 border border-slate-900 text-xs font-sans font-black uppercase tracking-wider">
              Appendix A: Official File Movement Card (24-Hour Table SLA)
            </div>
          </div>

          <div class="p-4 bg-slate-50 border border-slate-300 text-xs font-sans grid grid-cols-2 gap-4">
            <div>
              <span class="text-slate-500">File Reference Number:</span>
              <div class="font-mono font-bold text-sm text-slate-900">{{ docData.file_no }}</div>
            </div>
            <div>
              <span class="text-slate-500">Current Movement Status:</span>
              <div class="font-bold text-sm" :class="docData.is_treated ? 'text-emerald-700' : (docData.holding_hours > 24 ? 'text-red-700' : 'text-blue-700')">
                {{ docData.is_treated ? 'Treated / Returned' : (docData.holding_hours > 24 ? 'SLA BREACHED (>24 hrs)' : 'In Transit / On Desk') }}
              </div>
            </div>
            <div class="col-span-2">
              <span class="text-slate-500">File Subject:</span>
              <div class="font-semibold text-slate-900">{{ docData.subject }}</div>
            </div>
          </div>

          <table class="w-full text-xs font-sans border-collapse border border-slate-300">
            <thead class="bg-slate-100 text-slate-700 font-bold">
              <tr>
                <th class="p-2 border border-slate-300">Originating Office</th>
                <th class="p-2 border border-slate-300">Destination Desk</th>
                <th class="p-2 border border-slate-300">Time Out</th>
                <th class="p-2 border border-slate-300">Holding Time</th>
                <th class="p-2 border border-slate-300">Registry Seal</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="p-2 border border-slate-300 font-semibold">{{ docData.from_office }}</td>
                <td class="p-2 border border-slate-300 font-semibold">{{ docData.to_office }}</td>
                <td class="p-2 border border-slate-300">{{ formatDate(docData.date_time_out) }}</td>
                <td class="p-2 border border-slate-300 font-bold" :class="docData.holding_hours > 24 ? 'text-red-600' : 'text-slate-800'">
                  {{ docData.holding_hours }} Hours
                </td>
                <td class="p-2 border border-slate-300 text-center text-slate-500">SUB-REGISTRY VERIFIED</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Icon } from '@iconify/vue';
import axios from 'axios';

const props = defineProps({
  show: { type: Boolean, default: false },
  docType: { type: String, default: 'posting_order' }, // posting_order, assumption_slip, movement_card, confirmation_letter
  docData: { type: Object, default: () => ({}) },
});

defineEmits(['close']);

const stampSvg = ref('');

const formatDate = (dateStr) => {
  if (!dateStr) return 'N/A';
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};

const fetchStamp = async () => {
  try {
    const res = await axios.get('/dashboard/administration/stamps/preview', {
      params: { designation: 'DIRECTOR ADMINISTRATION' }
    });
    stampSvg.value = res.data.data?.svg || '';
  } catch (err) {
    console.error('Failed to load stamp for document:', err);
  }
};

const triggerPrint = () => {
  window.print();
};

onMounted(() => {
  fetchStamp();
});
</script>

<style>
@media print {
  body * {
    visibility: hidden;
  }
  #printArea, #printArea * {
    visibility: visible;
  }
  #printArea {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
  }
}
</style>
