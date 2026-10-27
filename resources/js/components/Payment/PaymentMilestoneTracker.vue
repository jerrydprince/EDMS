<template>
  <div class="bg-white border border-gray-200 rounded-xl p-5 mb-6 shadow-xs">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 mb-5 border-b border-gray-100">
      <div class="flex items-center gap-3">
        <div class="h-9 w-9 rounded-lg bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-700">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
          </svg>
        </div>
        <div>
          <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Federal Treasury Processing Pipeline</h3>
          <p class="text-xs text-gray-500">Public Financial Management & Electronic Voucher System (PFM-EVS)</p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <span 
          :class="[
            'px-2.5 py-1 rounded-full text-xs font-semibold capitalize tracking-wide border',
            isPaid ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
            hasActiveQuery ? 'bg-rose-50 text-rose-700 border-rose-200 animate-pulse' :
            'bg-indigo-50 text-indigo-700 border-indigo-200'
          ]"
        >
          {{ isPaid ? 'Treasury Liquidated' : (hasActiveQuery ? 'Audit Query Pending' : 'Processing in Progress') }}
        </span>
        <span v-if="paymentType" class="px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700 uppercase border border-slate-200">
          {{ paymentType }}
        </span>
      </div>
    </div>

    <!-- Stepper Navigation -->
    <div class="relative">
      <div :class="['grid grid-cols-2 md:grid-cols-4 gap-3 relative z-10', stages.length > 7 ? 'lg:grid-cols-8' : 'lg:grid-cols-7']">
        <div 
          v-for="(stage, idx) in stages" 
          :key="stage.id"
          :class="[
            'flex flex-col p-3 rounded-lg border transition-all duration-200',
            stage.status === 'completed' ? 'bg-emerald-50/40 border-emerald-200 text-emerald-900' :
            stage.status === 'active' ? 'bg-indigo-50/70 border-indigo-400 text-indigo-950 shadow-xs ring-1 ring-indigo-300' :
            stage.status === 'queried' ? 'bg-rose-50/70 border-rose-400 text-rose-950 shadow-xs ring-1 ring-rose-300' :
            'bg-gray-50/60 border-gray-200 text-gray-400 opacity-70'
          ]"
        >
          <!-- Step Top indicator -->
          <div class="flex items-center justify-between mb-2">
            <span 
              :class="[
                'w-6 h-6 rounded-full flex items-center justify-center text-[11px] font-bold',
                stage.status === 'completed' ? 'bg-emerald-600 text-white' :
                stage.status === 'active' ? 'bg-indigo-600 text-white animate-pulse' :
                stage.status === 'queried' ? 'bg-rose-600 text-white' :
                'bg-gray-200 text-gray-500'
              ]"
            >
              <svg v-if="stage.status === 'completed'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
              </svg>
              <span v-else>{{ idx + 1 }}</span>
            </span>

            <span class="text-[10px] font-mono uppercase tracking-wider">
              {{ stage.status === 'completed' ? 'Done' : (stage.status === 'active' ? 'Current' : (stage.status === 'queried' ? 'Query' : 'Pending')) }}
            </span>
          </div>

          <!-- Title & Section -->
          <div class="mt-0.5">
            <h4 class="text-xs font-bold leading-tight">{{ stage.title }}</h4>
            <p class="text-[10px] mt-0.5 opacity-80 leading-normal">{{ stage.unitSection }}</p>
          </div>

          <!-- Micro Detail if available -->
          <div v-if="stage.detail" class="mt-2 pt-1.5 border-t border-black/5 text-[9px] font-mono truncate opacity-90" :title="stage.detail">
            {{ stage.detail }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  payment: {
    type: Object,
    required: true
  },
  auditQueries: {
    type: Array,
    default: () => []
  }
});

const isPaid = computed(() => props.payment?.status === 'paid' || props.payment?.voucher?.some(v => v.payment_reference));
const hasActiveQuery = computed(() => props.auditQueries?.some(q => q.status === 'open') || props.payment?.has_active_query);
const paymentType = computed(() => props.payment?.payment_type || (props.payment?.is_advance ? 'advances' : null));

const stages = computed(() => {
  const p = props.payment || {};
  const vList = p.voucher || [];
  const primaryVoucher = vList[0] || {};
  const hasVoucher = vList.length > 0;
  const hasCheckingStamp = !!(p.has_checking_stamp || primaryVoucher.has_checking_stamp || p.checking_stamp);
  const hasAuditStamp = !!(p.has_audit_stamp || primaryVoucher.has_audit_stamp || p.audit_stamp);
  const isDisbursed = !!(primaryVoucher.payment_reference || primaryVoucher.disbursement_platform || p.status === 'paid');
  const isGLPosted = !!(p.ledger_count > 0 || p.has_gl_posted);

  // Compute status for each stage
  // 1. Registry Intake
  const s1 = {
    id: 1,
    title: 'Registry Intake',
    unitSection: 'Central Registry / AGF',
    status: 'completed',
    detail: p.created_at ? new Date(p.created_at).toLocaleDateString() : 'File Logged'
  };

  // 2. Classification
  const s2Status = (p.payment_type || hasVoucher) ? 'completed' : 'active';
  const s2 = {
    id: 2,
    title: 'AD Classification',
    unitSection: 'F&A Operations (Unit 61)',
    status: s2Status,
    detail: p.payment_type ? `Track: ${p.payment_type.toUpperCase()}` : 'Awaiting Routing'
  };

  // 3. Section Processing
  let s3Status = 'pending';
  if (s2Status === 'completed') {
    s3Status = hasVoucher ? 'completed' : 'active';
  }
  const s3 = {
    id: 3,
    title: 'PV & Deductions',
    unitSection: p.payment_type === 'advances' ? 'Advances Sec. (82)' : 'Other Charges (81)',
    status: s3Status,
    detail: primaryVoucher.deptal_no ? `PV #${primaryVoucher.deptal_no}` : (hasVoucher ? 'Voucher Generated' : null)
  };

  // 4. Checking Review
  let s4Status = 'pending';
  if (s3Status === 'completed') {
    s4Status = hasCheckingStamp ? 'completed' : 'active';
  }
  const s4 = {
    id: 4,
    title: 'Checking Review',
    unitSection: 'Checking Sec. (83)',
    status: s4Status,
    detail: hasCheckingStamp ? '3-Way Match Verified' : null
  };

  // 5. Internal Audit
  let s5Status = 'pending';
  if (s4Status === 'completed') {
    if (hasActiveQuery.value) {
      s5Status = 'queried';
    } else {
      s5Status = hasAuditStamp ? 'completed' : 'active';
    }
  }
  const s5 = {
    id: 5,
    title: 'Internal Audit',
    unitSection: 'Internal Audit Sec. (84)',
    status: s5Status,
    detail: hasActiveQuery.value ? 'Statutory Query Raised' : (hasAuditStamp ? 'Clearance Seal Affixed' : null)
  };

  // 6. Cash Office
  let s6Status = 'pending';
  if (s5Status === 'completed') {
    s6Status = isDisbursed ? 'completed' : 'active';
  }
  const s6 = {
    id: 6,
    title: 'Cash Office Mandate',
    unitSection: 'Cash Office / CPO (85)',
    status: s6Status,
    detail: isDisbursed ? `${primaryVoucher.disbursement_platform || 'GIFMIS'} Disbursed` : null
  };

  // 7. Final Accounts
  let s7Status = 'pending';
  if (s6Status === 'completed') {
    s7Status = isGLPosted ? 'completed' : 'active';
  }
  const s7 = {
    id: 7,
    title: 'Final Accounts GL',
    unitSection: 'Final Accounts (Unit 11)',
    status: s7Status,
    detail: isGLPosted ? 'IPSAS Double-Entry Posted' : null
  };

  const result = [s1, s2, s3, s4, s5, s6, s7];

  if (paymentType.value === 'advances') {
    const isRetired = p.status === 'retired';
    let s8Status = 'pending';
    if (s6Status === 'completed') {
      s8Status = isRetired ? 'completed' : 'active';
    }
    result.push({
      id: 8,
      title: 'Advance Retirement',
      unitSection: 'Advances Sec. (82)',
      status: s8Status,
      detail: isRetired ? 'Folio Reconciled & Cleared' : (s6Status === 'completed' ? 'Awaiting Retirement (FR 1420)' : null)
    });
  }

  return result;
});
</script>
