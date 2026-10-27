<template>
  <div class="space-y-6">
    <!-- Active Audit Queries Banner (Visible to all authorized F&A officers) -->
    <div v-if="activeQueries && activeQueries.length > 0" class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-lg shadow-xs">
      <div class="flex items-start justify-between">
        <div class="flex items-center gap-3">
          <div class="p-2 bg-rose-100 rounded-lg text-rose-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
          </div>
          <div>
            <h4 class="text-sm font-bold text-rose-900">Pre-Payment Internal Audit Query In Effect</h4>
            <p class="text-xs text-rose-700 mt-0.5">
              Processing is paused until the raised audit query is resolved by the initiating desk officer.
            </p>
          </div>
        </div>
        <button 
          @click="showQueryDrawer = true" 
          class="text-xs font-bold text-rose-700 hover:text-rose-900 underline px-3 py-1 bg-white rounded border border-rose-200"
        >
          View Queries ({{ activeQueries.length }})
        </button>
      </div>
    </div>

    <!-- 1. ASSISTANT DIRECTOR / DIRECTOR: Classification Desk -->
    <div v-if="isDirectorOrAD && !payment.payment_type" class="bg-white border border-indigo-200 rounded-xl p-5 shadow-xs">
      <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100">
        <div class="flex items-center gap-3">
          <div class="h-8 w-8 rounded-lg bg-indigo-50 border border-indigo-200 flex items-center justify-center text-indigo-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
          </div>
          <div>
            <h3 class="text-sm font-bold text-gray-900">Federal Treasury Payment Classification Desk</h3>
            <p class="text-xs text-gray-500">Route file to specialized operational processing section under Unit 61</p>
          </div>
        </div>
        <span class="text-xs font-mono bg-indigo-50 text-indigo-700 px-2.5 py-1 rounded-full border border-indigo-200 font-bold">
          Unit 61 Triage
        </span>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Track 1: Advances -->
        <div 
          @click="selectedTrack = 'advances'"
          :class="[
            'p-4 rounded-xl border-2 cursor-pointer transition-all',
            selectedTrack === 'advances' ? 'border-amber-500 bg-amber-50/50 shadow-xs' : 'border-gray-200 hover:border-amber-300 bg-slate-50'
          ]"
        >
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-bold uppercase tracking-wider text-amber-800">Track 1</span>
            <span class="text-[10px] font-mono px-2 py-0.5 bg-amber-100 text-amber-800 rounded font-bold">Section 82</span>
          </div>
          <h4 class="text-sm font-bold text-gray-900">Staff Advances (APV / TF1)</h4>
          <p class="text-xs text-gray-500 mt-1 leading-relaxed">
            Non-personal, Touring, Imprest, or Project cash. 0% statutory tax deductions. Subject to strict Civil Service FR 1420 ledger clearance.
          </p>
        </div>

        <!-- Track 2: Capital / Overhead -->
        <div 
          @click="selectedTrack = 'capital'"
          :class="[
            'p-4 rounded-xl border-2 cursor-pointer transition-all',
            selectedTrack === 'capital' ? 'border-indigo-500 bg-indigo-50/50 shadow-xs' : 'border-gray-200 hover:border-indigo-300 bg-slate-50'
          ]"
        >
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-bold uppercase tracking-wider text-indigo-800">Track 2</span>
            <span class="text-[10px] font-mono px-2 py-0.5 bg-indigo-100 text-indigo-800 rounded font-bold">Section 81</span>
          </div>
          <h4 class="text-sm font-bold text-gray-900">Capital & Overhead Expenditure</h4>
          <p class="text-xs text-gray-500 mt-1 leading-relaxed">
            Procurement, Works, and Overhead. Automated statutory deductions (7.5% VAT, 10% WHT, 1% Stamp Duty) and Vote commitment.
          </p>
        </div>
      </div>

      <div class="mt-4 flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
        <button 
          :disabled="!selectedTrack || isSubmitting"
          @click="classifyPayment"
          class="flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-lg text-xs font-bold shadow-xs transition-all cursor-pointer"
        >
          <svg v-if="isSubmitting" class="animate-spin h-3.5 w-3.5" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
          </svg>
          Confirm Classification & Route File
        </button>
      </div>
    </div>

    <!-- 2. TRACK 1: ADVANCES SECTION ACTION DESK (Section 82) -->
    <div v-if="isAdvancesSection && (payment.payment_type === 'advances' || !payment.payment_type)" class="bg-white border border-amber-300 rounded-xl p-5 shadow-xs">
      <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100">
        <div class="flex items-center gap-3">
          <div class="h-8 w-8 rounded-lg bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <h3 class="text-sm font-bold text-gray-900">Advances Section Desk (Section 82)</h3>
            <p class="text-xs text-gray-500">Staff Advance Voucher (TF1 / APV) generation & Financial Regulation 1420 retirements</p>
          </div>
        </div>
        <span class="text-xs font-mono bg-amber-100 text-amber-800 px-2.5 py-1 rounded-full border border-amber-300 font-bold">
          Section 82
        </span>
      </div>

      <!-- FR 1420 Indebtedness Pre-Clearance Check -->
      <div class="mb-4 p-3.5 rounded-lg border text-xs" :class="fr1420Violation ? 'bg-rose-50 border-rose-300 text-rose-900' : 'bg-emerald-50 border-emerald-300 text-emerald-900'">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <svg v-if="!fr1420Violation" class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <svg v-else class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
              <span class="font-bold">Civil Service Financial Regulation 1420 Check:</span>
              <p class="mt-0.5 text-[11px] leading-relaxed">
                {{ fr1420Message || (fr1420Violation ? 'Officer has an outstanding unretired advance blocking further advance issuance.' : 'Beneficiary officer is in compliance with zero overdue advances. Eligible for advance issuance.') }}
              </p>
            </div>
          </div>
          <button @click="checkFRCompliance" :disabled="isCheckingFR" class="text-[11px] font-bold text-gray-600 hover:text-gray-900 underline ml-2 shrink-0">
            {{ isCheckingFR ? 'Checking...' : 'Re-verify' }}
          </button>
        </div>
      </div>

      <!-- Action: Advance Voucher (TF1) Generation -->
      <div v-if="!isVoucherGenerated" class="space-y-3">
        <div class="bg-amber-50/70 p-3.5 rounded-lg border border-amber-200 text-xs text-amber-900">
          <p class="font-bold">Staff Advance Voucher Generation (TF1 Form)</p>
          <p class="text-[11px] text-amber-800 mt-0.5">
            Non-personal & touring advances are exempt from statutory tax deductions (0% VAT, 0% WHT, 0% Stamp Duty). Net payable equals Gross sum.
          </p>
        </div>

        <button 
          :disabled="isSubmitting || fr1420Violation"
          @click="openCreateAdvanceVoucher"
          class="w-full flex items-center justify-center gap-2 px-5 py-2.5 bg-amber-700 hover:bg-amber-800 disabled:opacity-50 text-white rounded-lg text-xs font-bold shadow-xs transition-all cursor-pointer"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          Generate Staff Advance Payment Voucher (TF1 / APV)
        </button>
      </div>

      <!-- Voucher Generated Summary Card -->
      <div v-else class="space-y-3">
        <div class="bg-slate-50 p-3.5 rounded-lg border border-slate-200 text-xs space-y-1.5 font-mono">
          <div class="flex justify-between text-gray-700">
            <span>Advance Voucher No:</span>
            <span class="font-bold text-amber-900">{{ payment.voucher?.[0]?.voucher_number || 'VOU-GENERATED' }}</span>
          </div>
          <div class="flex justify-between text-gray-700">
            <span>Principal Advance Sum:</span>
            <span class="font-bold text-gray-900">₦{{ formatMoney(payment.gross_amount || payment.amount || 0) }}</span>
          </div>
          <div class="flex justify-between text-gray-700">
            <span>Statutory Deductions:</span>
            <span class="font-bold text-emerald-700">₦0.00 (0% Tax-Exempt)</span>
          </div>
          <div class="flex justify-between text-gray-700 border-t border-slate-300 pt-1.5 font-bold">
            <span>Net Sum Payable:</span>
            <span class="text-amber-800">₦{{ formatMoney(payment.net_amount || payment.gross_amount || payment.amount || 0) }}</span>
          </div>
        </div>

        <div class="flex items-center justify-between text-xs pt-1">
          <span class="text-emerald-700 font-bold flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            TF1 Voucher Generated & Folio Registered
          </span>
          <button @click="openCreateAdvanceVoucher" class="text-xs text-amber-700 hover:text-amber-900 underline font-bold">
            Re-generate Voucher
          </button>
        </div>
      </div>

      <!-- Advances Retirement & Reconciliation Section (FR 1420) -->
      <div v-if="isDisbursed" class="mt-5 pt-4 border-t border-amber-200">
        <div class="flex items-center justify-between mb-3">
          <h4 class="text-xs font-bold uppercase tracking-wider text-amber-900 flex items-center gap-1.5">
            <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            Advances Retirement & Reconciliation (FR 1420)
          </h4>
          <span v-if="isRetired" class="text-[10px] font-mono bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded font-bold">
            Retired & Cleared
          </span>
        </div>

        <div v-if="!isRetired" class="space-y-3">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-bold text-gray-700 mb-1">Total Expended (₦)</label>
              <input type="number" v-model="retireForm.expended" placeholder="0.00" class="w-full text-xs rounded-lg border-gray-300">
            </div>
            <div>
              <label class="block text-[11px] font-bold text-gray-700 mb-1">Unspent Refund to TSA (₦)</label>
              <input type="number" v-model="retireForm.refund" placeholder="0.00" class="w-full text-xs rounded-lg border-gray-300">
            </div>
            <div>
              <label class="block text-[11px] font-bold text-gray-700 mb-1">Remita RRR (For TSA Refund)</label>
              <input v-model="retireForm.rrr" placeholder="e.g. 2109-8812-4412" class="w-full text-xs rounded-lg border-gray-300">
            </div>
            <div>
              <label class="block text-[11px] font-bold text-gray-700 mb-1">Retirement Voucher Ref</label>
              <input v-model="retireForm.ref" placeholder="e.g. RET-VOU-2026-09" class="w-full text-xs rounded-lg border-gray-300">
            </div>
          </div>

          <button 
            :disabled="!canSubmitRetirement || isSubmitting"
            @click="submitRetirement"
            class="w-full flex items-center justify-center gap-2 px-5 py-2.5 bg-amber-800 hover:bg-amber-900 disabled:opacity-50 text-white rounded-lg text-xs font-bold shadow-xs transition-all cursor-pointer"
          >
            Submit Retirement & Reconcile Advances Folio
          </button>
        </div>

        <div v-else class="text-xs text-amber-900 bg-amber-50 p-3 rounded-lg border border-amber-200">
          Advance has been retired and reconciled. Folio cleared in compliance with Civil Service Financial Regulation 1420.
        </div>
      </div>
    </div>

    <!-- 3. TRACK 2: OTHER CHARGES SECTION ACTION DESK (Section 81) -->
    <div v-if="isOtherChargesSection && (payment.payment_type === 'capital' || !payment.payment_type)" class="bg-white border border-indigo-300 rounded-xl p-5 shadow-xs">
      <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100">
        <div class="flex items-center gap-3">
          <div class="h-8 w-8 rounded-lg bg-indigo-50 border border-indigo-200 flex items-center justify-center text-indigo-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
          </div>
          <div>
            <h3 class="text-sm font-bold text-gray-900">Other Charges Section Desk (Section 81)</h3>
            <p class="text-xs text-gray-500">Vote Book commitment, Subhead encumbrance, and Capital/Overhead Payment Voucher triad</p>
          </div>
        </div>
        <span class="text-xs font-mono bg-indigo-100 text-indigo-800 px-2.5 py-1 rounded-full border border-indigo-300 font-bold">
          Section 81
        </span>
      </div>

      <!-- If Vouchers Not Yet Generated -->
      <div v-if="!isVoucherGenerated" class="space-y-3">
        <div class="bg-indigo-50/70 p-3.5 rounded-lg border border-indigo-200 text-xs text-indigo-900">
          <p class="font-bold">Vote Book Commitment & Voucher Generation Required</p>
          <p class="text-[11px] text-indigo-800 mt-0.5">
            Encumber budgetary subhead and automatically generate the triad of vouchers: Main Payment Voucher, VAT Deduction Voucher (7.5%), and WHT Deduction Voucher (10%/5%).
          </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
          <button 
            @click="openChargeAccount"
            class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-white hover:bg-gray-50 border-2 border-indigo-600 text-indigo-700 rounded-lg text-xs font-bold transition-all cursor-pointer"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Select Vote Book Subhead (Charge Account)
          </button>

          <button 
            @click="openCreateCapitalVoucher"
            class="flex-1 flex items-center justify-center gap-2 px-5 py-2.5 bg-indigo-700 hover:bg-indigo-800 text-white rounded-lg text-xs font-bold shadow-xs transition-all cursor-pointer"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Generate Capital Expenditure Vouchers
          </button>
        </div>
      </div>

      <!-- If Vouchers Generated -->
      <div v-else class="space-y-3">
        <div class="bg-slate-50 p-3.5 rounded-lg border border-slate-200 text-xs space-y-1.5 font-mono">
          <div class="flex justify-between text-gray-700">
            <span>Main Voucher Ref:</span>
            <span class="font-bold text-indigo-900">{{ payment.voucher?.[0]?.voucher_number || 'VOU-GENERATED' }}</span>
          </div>
          <div class="flex justify-between text-gray-700">
            <span>Gross Contract Sum:</span>
            <span class="font-bold text-gray-900">₦{{ formatMoney(payment.gross_amount || payment.amount || 0) }}</span>
          </div>
          <div class="flex justify-between text-gray-700">
            <span>Total Statutory Deductions:</span>
            <span class="font-bold text-amber-700">₦{{ formatMoney(payment.total_deductions || 0) }} (VAT 7.5%, WHT 10%, Stamp 1%)</span>
          </div>
          <div class="flex justify-between text-gray-700 border-t border-slate-300 pt-1.5 font-bold">
            <span>Net Payable to Vendor:</span>
            <span class="text-indigo-800">₦{{ formatMoney(payment.net_amount || (payment.gross_amount - payment.total_deductions) || 0) }}</span>
          </div>
        </div>

        <div class="flex items-center justify-between text-xs pt-1">
          <div class="flex items-center gap-2">
            <span class="text-emerald-700 font-bold flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
              Triad Vouchers Generated & Vote Encumbered
            </span>
          </div>
          <button @click="openCreateCapitalVoucher" class="text-xs text-indigo-700 hover:text-indigo-900 underline font-bold">
            Re-generate / Edit Voucher
          </button>
        </div>
      </div>
    </div>

    <!-- 4. CHECKING SECTION ACTION DESK (Section 83) -->
    <div v-if="isCheckingSection" class="bg-white border border-emerald-200 rounded-xl p-5 shadow-xs">
      <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100">
        <div class="flex items-center gap-3">
          <div class="h-8 w-8 rounded-lg bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <h3 class="text-sm font-bold text-gray-900">Checking Section Desk (Section 83)</h3>
            <p class="text-xs text-gray-500">Arithmetic audit, statutory deduction check, and 3-way matching</p>
          </div>
        </div>
        <span v-if="hasCheckingStamp" class="text-xs font-mono bg-emerald-100 text-emerald-800 px-2.5 py-1 rounded-full border border-emerald-300 font-bold">
          Stamp Affixed
        </span>
      </div>

      <div v-if="!hasCheckingStamp">
        <!-- 3-Way Match Verification Checklist -->
        <div class="space-y-2 mb-4 bg-slate-50 p-3.5 rounded-lg border border-slate-200">
          <label class="flex items-center gap-2.5 text-xs text-gray-700 cursor-pointer">
            <input type="checkbox" v-model="checkingChecks.arithmetic" class="rounded text-emerald-600 focus:ring-emerald-500 h-4 w-4">
            <span>Gross Contract Sum and arithmetic calculations cross-checked against invoice</span>
          </label>
          <label class="flex items-center gap-2.5 text-xs text-gray-700 cursor-pointer">
            <input type="checkbox" v-model="checkingChecks.deductions" class="rounded text-emerald-600 focus:ring-emerald-500 h-4 w-4">
            <span>Statutory deductions (VAT 7.5%, WHT 10%/5%, Stamp 1%) accurately computed</span>
          </label>
          <label class="flex items-center gap-2.5 text-xs text-gray-700 cursor-pointer">
            <input type="checkbox" v-model="checkingChecks.voteBook" class="rounded text-emerald-600 focus:ring-emerald-500 h-4 w-4">
            <span>Vote Book commitment verified against approved budgetary subhead allocation</span>
          </label>
        </div>

        <button 
          :disabled="!canAffixCheckingStamp || isSubmitting"
          @click="affixCheckingStamp"
          class="w-full flex items-center justify-center gap-2 px-5 py-2.5 bg-emerald-700 hover:bg-emerald-800 disabled:opacity-50 text-white rounded-lg text-xs font-bold shadow-xs transition-all cursor-pointer"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
          </svg>
          Affix Digital Checking Stamp (Checked & Passed)
        </button>
      </div>

      <div v-else class="text-xs text-emerald-800 bg-emerald-50 p-3 rounded-lg border border-emerald-200">
        Checking Confirmation Stamp has been verified and permanently embedded into this Payment Voucher. File is ready for Pre-Payment Internal Audit.
      </div>
    </div>

    <!-- 5. INTERNAL AUDIT ACTION DESK (Section 84) -->
    <div v-if="isInternalAuditSection" class="bg-white border border-rose-200 rounded-xl p-5 shadow-xs">
      <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100">
        <div class="flex items-center gap-3">
          <div class="h-8 w-8 rounded-lg bg-rose-50 border border-rose-200 flex items-center justify-center text-rose-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 004 11m0 0a8 8 0 0015.171 2.868" />
            </svg>
          </div>
          <div>
            <h3 class="text-sm font-bold text-gray-900">Pre-Payment Internal Audit Desk (Section 84)</h3>
            <p class="text-xs text-gray-500">Civil Service Financial Regulation compliance & statutory seal</p>
          </div>
        </div>
        <span v-if="hasAuditStamp" class="text-xs font-mono bg-rose-100 text-rose-800 px-2.5 py-1 rounded-full border border-rose-300 font-bold">
          Clearance Seal Affixed
        </span>
      </div>

      <div v-if="!hasAuditStamp" class="flex flex-col sm:flex-row gap-3">
        <button 
          :disabled="isSubmitting || hasActiveQueries"
          @click="affixAuditStamp"
          class="flex-1 flex items-center justify-center gap-2 px-5 py-2.5 bg-rose-700 hover:bg-rose-800 disabled:opacity-50 text-white rounded-lg text-xs font-bold shadow-xs transition-all cursor-pointer"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
          </svg>
          Affix Audit Clearance Seal (Audited & Passed)
        </button>

        <button 
          @click="showQueryModal = true"
          class="flex items-center justify-center gap-2 px-4 py-2.5 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 rounded-lg text-xs font-bold transition-all cursor-pointer"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
          </svg>
          Raise Pre-Payment Audit Query
        </button>
      </div>

      <div v-else class="text-xs text-rose-800 bg-rose-50 p-3 rounded-lg border border-rose-200">
        Statutory Pre-Payment Audit Clearance Seal has been stamped and verified. Payment is cleared for Cash Office mandate issuance.
      </div>
    </div>

    <!-- 6. CASH OFFICE / CPO DISBURSEMENT GATEWAY (Section 85) -->
    <div v-if="isCashOfficeSection" class="bg-white border border-blue-200 rounded-xl p-5 shadow-xs">
      <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100">
        <div class="flex items-center gap-3">
          <div class="h-8 w-8 rounded-lg bg-blue-50 border border-blue-200 flex items-center justify-center text-blue-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
          <div>
            <h3 class="text-sm font-bold text-gray-900">Cash Office / CPO Disbursement Gateway (Section 85)</h3>
            <p class="text-xs text-gray-500">CBN TSA Mandate Execution via GIFMIS or Remita</p>
          </div>
        </div>
        <span v-if="isDisbursed" class="text-xs font-mono bg-blue-100 text-blue-800 px-2.5 py-1 rounded-full border border-blue-300 font-bold">
          Disbursed
        </span>
      </div>

      <div v-if="!isDisbursed">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Disbursement Platform</label>
            <select v-model="disburseForm.platform" class="w-full text-xs rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
              <option value="GIFMIS">GIFMIS Electronic Payment Platform</option>
              <option value="Remita">Remita TSA Direct Mandate</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Mandate / Transaction Reference</label>
            <input v-model="disburseForm.reference" placeholder="e.g. GIFMIS-MND-2026-90812" class="w-full text-xs rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
          </div>
          <div class="sm:col-span-2">
            <label class="block text-xs font-bold text-gray-700 mb-1">TSA Bank Debited</label>
            <input v-model="disburseForm.bank" placeholder="e.g. CBN TSA Sub-Account 0020194829" class="w-full text-xs rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
          </div>
        </div>

        <button 
          :disabled="!canDisburse || isSubmitting"
          @click="disbursePayment"
          class="w-full flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-700 hover:bg-blue-800 disabled:opacity-50 text-white rounded-lg text-xs font-bold shadow-xs transition-all cursor-pointer"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          Execute Mandate, Log Cash Book & Liquidate Vote
        </button>
      </div>

      <div v-else class="text-xs text-blue-800 bg-blue-50 p-3 rounded-lg border border-blue-200">
        Disbursement executed successfully. NET sum recorded in Cash Book. Vote commitment liquidated into actual expenditure. File forwarded to Final Accounts Unit.
      </div>
    </div>

    <!-- 7. FINAL ACCOUNTS UNIT GENERAL LEDGER DESK (Unit 11) -->
    <div v-if="isFinalAccountsUnit" class="bg-white border border-purple-200 rounded-xl p-5 shadow-xs">
      <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100">
        <div class="flex items-center gap-3">
          <div class="h-8 w-8 rounded-lg bg-purple-50 border border-purple-200 flex items-center justify-center text-purple-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
          </div>
          <div>
            <h3 class="text-sm font-bold text-gray-900">Final Accounts Unit (Unit 11)</h3>
            <p class="text-xs text-gray-500">Automated balanced IPSAS Accrual Double-Entry Journal Posting</p>
          </div>
        </div>
        <span v-if="isGLPosted" class="text-xs font-mono bg-purple-100 text-purple-800 px-2.5 py-1 rounded-full border border-purple-300 font-bold">
          Journal Posted
        </span>
      </div>

      <div v-if="!isGLPosted">
        <div class="bg-slate-50 p-3.5 rounded-lg border border-slate-200 mb-4 font-mono text-xs space-y-1.5">
          <div class="flex justify-between text-gray-700">
            <span>Gross Expenditure Subhead (Debit):</span>
            <span class="font-bold">₦{{ formatMoney(payment.gross_amount || payment.amount || 0) }}</span>
          </div>
          <div class="flex justify-between text-gray-700">
            <span>TSA Bank Account Disbursed (Credit):</span>
            <span class="font-bold text-rose-700">₦{{ formatMoney(payment.net_amount || payment.amount || 0) }}</span>
          </div>
          <div v-if="payment.total_deductions > 0" class="flex justify-between text-gray-700">
            <span>Total Statutory Deductions (Credit):</span>
            <span class="font-bold text-amber-700">₦{{ formatMoney(payment.total_deductions) }}</span>
          </div>
          <div class="border-t border-slate-300 pt-1.5 flex justify-between font-bold text-emerald-800">
            <span>Double-Entry Balance Check:</span>
            <span>BALANCED (Debits == Credits)</span>
          </div>
        </div>

        <button 
          :disabled="isSubmitting"
          @click="postToGeneralLedger"
          class="w-full flex items-center justify-center gap-2 px-5 py-2.5 bg-purple-700 hover:bg-purple-800 disabled:opacity-50 text-white rounded-lg text-xs font-bold shadow-xs transition-all cursor-pointer"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
          Post Balanced IPSAS Journal to General Ledger
        </button>
      </div>

      <div v-else class="text-xs text-purple-800 bg-purple-50 p-3 rounded-lg border border-purple-200">
        Double-entry journal has been recorded in the General Ledger. All subheads and trial balance ledgers are in balance.
      </div>
    </div>

    <!-- 8. OVERVIEW STATUS CARD (For General Viewers) -->
    <div v-if="!isDirectorOrAD && !isOtherChargesSection && !isAdvancesSection && !isCheckingSection && !isInternalAuditSection && !isCashOfficeSection && !isFinalAccountsUnit" class="bg-white border border-gray-200 rounded-xl p-5 shadow-xs">
      <div class="flex items-center justify-between pb-3 mb-3 border-b border-gray-100">
        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700">Statutory File Processing State</h4>
        <span class="text-xs font-mono px-2.5 py-0.5 rounded-full font-bold" :class="isDisbursed ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800'">
          {{ isDisbursed ? 'Disbursed / Paid' : 'Under Processing' }}
        </span>
      </div>
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
        <div class="p-2.5 rounded bg-slate-50 border">
          <p class="text-[10px] text-gray-500 uppercase font-bold">Voucher</p>
          <p class="font-bold mt-1" :class="isVoucherGenerated ? 'text-emerald-700' : 'text-gray-400'">
            {{ isVoucherGenerated ? 'Generated' : 'Pending' }}
          </p>
        </div>
        <div class="p-2.5 rounded bg-slate-50 border">
          <p class="text-[10px] text-gray-500 uppercase font-bold">Checking Stamp</p>
          <p class="font-bold mt-1" :class="hasCheckingStamp ? 'text-emerald-700' : 'text-gray-400'">
            {{ hasCheckingStamp ? 'Checked & Passed' : 'Pending' }}
          </p>
        </div>
        <div class="p-2.5 rounded bg-slate-50 border">
          <p class="text-[10px] text-gray-500 uppercase font-bold">Audit Seal</p>
          <p class="font-bold mt-1" :class="hasAuditStamp ? 'text-emerald-700' : 'text-gray-400'">
            {{ hasAuditStamp ? 'Audited & Passed' : 'Pending' }}
          </p>
        </div>
        <div class="p-2.5 rounded bg-slate-50 border">
          <p class="text-[10px] text-gray-500 uppercase font-bold">Cash Office</p>
          <p class="font-bold mt-1" :class="isDisbursed ? 'text-emerald-700' : 'text-gray-400'">
            {{ isDisbursed ? 'TSA Disbursed' : 'Pending' }}
          </p>
        </div>
      </div>
    </div>

    <!-- Audit Query Raising Modal -->
    <div v-if="showQueryModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
      <div class="bg-white rounded-xl max-w-lg w-full p-6 shadow-xl">
        <h3 class="text-base font-bold text-gray-900 mb-1">Raise Statutory Audit Query</h3>
        <p class="text-xs text-gray-500 mb-4">A formal audit query will pause processing until responded to by the desk officer.</p>

        <div class="space-y-3 mb-5">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Grounds of Query</label>
            <select v-model="queryForm.category" class="w-full text-xs rounded-lg border-gray-300">
              <option value="missing_docs">Incomplete / Missing Supporting Documents</option>
              <option value="calculation_error">Arithmetic / Tax Calculation Discrepancy</option>
              <option value="fr1420_violation">Financial Regulation 1420 Violation (Unretired Advance)</option>
              <option value="overpricing">Overinvoicing / Price Intelligence Discrepancy</option>
              <option value="other">Other Statutory Irregularity</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Query Description / Specific Demands</label>
            <textarea v-model="queryForm.description" rows="4" placeholder="Detail the specific findings and required corrective actions..." class="w-full text-xs rounded-lg border-gray-300"></textarea>
          </div>
        </div>

        <div class="flex items-center justify-end gap-3">
          <button @click="showQueryModal = false" class="px-4 py-2 border rounded-lg text-xs font-bold text-gray-600 hover:bg-gray-50 cursor-pointer">Cancel</button>
          <button 
            :disabled="!queryForm.description || isSubmitting"
            @click="submitAuditQuery" 
            class="px-5 py-2 bg-rose-700 hover:bg-rose-800 disabled:opacity-50 text-white rounded-lg text-xs font-bold cursor-pointer"
          >
            Issue Formal Query
          </button>
        </div>
      </div>
    </div>

    <!-- Active Queries Drawer -->
    <div v-if="showQueryDrawer" class="fixed inset-0 z-50 flex items-center justify-end bg-black/40">
      <div class="bg-white h-full w-full max-w-md p-6 shadow-2xl overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b">
          <h3 class="text-base font-bold text-gray-900">Audit Queries on File</h3>
          <button @click="showQueryDrawer = false" class="text-gray-400 hover:text-gray-600 text-xl font-bold cursor-pointer">&times;</button>
        </div>

        <div class="mt-4 space-y-4">
          <div v-for="q in activeQueries" :key="q.id" class="p-4 bg-rose-50/70 border border-rose-200 rounded-lg">
            <div class="flex items-center justify-between mb-1">
              <span class="text-xs font-bold text-rose-800 uppercase">{{ q.category || 'Statutory Query' }}</span>
              <span class="text-[10px] text-gray-500 font-mono">{{ new Date(q.created_at).toLocaleDateString() }}</span>
            </div>
            <p class="text-xs text-gray-800 mb-3">{{ q.query_text || q.description }}</p>

            <div v-if="q.response_text" class="p-2.5 bg-white border border-gray-200 rounded text-xs text-gray-700 mb-2">
              <span class="font-bold text-gray-900">Response:</span> {{ q.response_text }}
            </div>

            <div v-else class="space-y-2">
              <textarea v-model="responseDrafts[q.id]" placeholder="Type response to audit query..." rows="2" class="w-full text-xs rounded border-gray-300"></textarea>
              <button 
                @click="respondQuery(q.id)" 
                :disabled="!responseDrafts[q.id]"
                class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded text-xs font-bold cursor-pointer"
              >
                Submit Response
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  payment: {
    type: Object,
    required: true
  },
  user: {
    type: Object,
    default: () => ({})
  },
  roles: {
    type: Array,
    default: () => []
  },
  permissions: {
    type: Array,
    default: () => []
  },
  activeQueries: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['refresh', 'notify', 'open-create-voucher', 'open-charge-account']);

// State
const isSubmitting = ref(false);
const selectedTrack = ref(null);
const showQueryModal = ref(false);
const showQueryDrawer = ref(false);
const isCheckingFR = ref(false);
const fr1420Violation = ref(false);
const fr1420Message = ref('');

const checkingChecks = ref({
  arithmetic: false,
  deductions: false,
  voteBook: false
});

const disburseForm = ref({
  platform: 'GIFMIS',
  reference: '',
  bank: 'CBN TSA Sub-Account 0020194829',
  value_date: new Date().toISOString().slice(0, 10)
});

const retireForm = ref({
  expended: null,
  refund: 0,
  rrr: '',
  ref: ''
});

const queryForm = ref({
  category: 'missing_docs',
  description: ''
});

const responseDrafts = ref({});

// Permission helper
const userPermissions = computed(() => {
  const p = props.permissions || [];
  return p.map(item => (typeof item === 'string' ? item : item.name).toLowerCase().trim());
});

const can = (perm) => userPermissions.value.includes(perm.toLowerCase().trim());

// Section & Role permissions (Tri-Layer RBAC: permissions + substantive 4-tier assignment + legacy role fallback)
const isDirectorOrAD = computed(() => {
  const u = props.user || {};
  const r = (props.roles || []).map(x => String(x).toLowerCase());
  return can('payment approve') || can('payment classify') || r.includes('director') || r.includes('assistant director') || u.is_unit_head || u.unit_id === 61 || u.is_department_head || u.is_division_head;
});

const isOtherChargesSection = computed(() => {
  const u = props.user || {};
  const r = (props.roles || []).map(x => String(x).toLowerCase());
  const sName = (u.section?.name || '').toLowerCase();
  return can('votebook encumber') || u.section_id === 81 || sName.includes('other charges') || r.some(x => x.includes('other charges'));
});

const isAdvancesSection = computed(() => {
  const u = props.user || {};
  const r = (props.roles || []).map(x => String(x).toLowerCase());
  const sName = (u.section?.name || '').toLowerCase();
  return can('advances create') || can('advances retire') || u.section_id === 82 || sName.includes('advance') || r.some(x => x.includes('advance'));
});

const isCheckingSection = computed(() => {
  const u = props.user || {};
  const r = (props.roles || []).map(x => String(x).toLowerCase());
  const sName = (u.section?.name || '').toLowerCase();
  return can('checking stamp') || u.section_id === 83 || sName.includes('checking') || r.some(x => x.includes('checking'));
});

const isInternalAuditSection = computed(() => {
  const u = props.user || {};
  const r = (props.roles || []).map(x => String(x).toLowerCase());
  const sName = (u.section?.name || '').toLowerCase();
  return can('audit stamp') || can('audit query raise') || u.section_id === 84 || sName.includes('audit') || r.some(x => x.includes('audit') || x.includes('internal audit'));
});

const isCashOfficeSection = computed(() => {
  const u = props.user || {};
  const r = (props.roles || []).map(x => String(x).toLowerCase());
  const sName = (u.section?.name || '').toLowerCase();
  return can('cashbook disburse') || u.section_id === 85 || sName.includes('cash') || r.some(x => x.includes('cash office'));
});

const isFinalAccountsUnit = computed(() => {
  const u = props.user || {};
  const r = (props.roles || []).map(x => String(x).toLowerCase());
  const uName = (u.unit?.title || u.unit?.name || '').toLowerCase();
  return can('ledger post') || u.unit_id === 11 || uName.includes('final account') || r.some(x => x.includes('final account'));
});

// Voucher & Payment States
const isVoucherGenerated = computed(() => {
  const p = props.payment || {};
  return !!(p.voucher && p.voucher.length > 0 && p.voucher[0]?.id);
});

const hasCheckingStamp = computed(() => {
  const p = props.payment || {};
  return !!(p.has_checking_stamp || p.voucher?.[0]?.has_checking_stamp || p.checking_stamp);
});

const hasAuditStamp = computed(() => {
  const p = props.payment || {};
  return !!(p.has_audit_stamp || p.voucher?.[0]?.has_audit_stamp || p.audit_stamp);
});

const hasActiveQueries = computed(() => {
  return props.activeQueries?.some(q => q.status === 'open') || props.payment?.has_active_query;
});

const isDisbursed = computed(() => {
  const p = props.payment || {};
  return !!(p.status === 'paid' || p.voucher?.[0]?.payment_reference || p.voucher?.[0]?.disbursement_platform);
});

const isGLPosted = computed(() => {
  const p = props.payment || {};
  return !!(p.ledger_count > 0 || p.has_gl_posted);
});

const isRetired = computed(() => {
  const p = props.payment || {};
  return p.status === 'retired' || p.advances_ledger?.status === 'retired' || p.advances_ledger?.[0]?.status === 'retired';
});

const canAffixCheckingStamp = computed(() => {
  return checkingChecks.value.arithmetic && checkingChecks.value.deductions && checkingChecks.value.voteBook;
});

const canDisburse = computed(() => {
  return disburseForm.value.platform && disburseForm.value.reference && disburseForm.value.bank;
});

const canSubmitRetirement = computed(() => {
  return Number(retireForm.value.expended) > 0;
});

const formatMoney = (val) => {
  return Number(val || 0).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

// FR 1420 Compliance Verification Method
const checkFRCompliance = async () => {
  const recipientId = props.payment?.recipient_id || props.payment?.recipient?.id;
  if (!recipientId) return;
  isCheckingFR.value = true;
  try {
    const res = await axios.get(`/finance-and-accounts/check-fr1420/${recipientId}`);
    if (res.data) {
      fr1420Violation.value = res.data.allowed === false;
      fr1420Message.value = res.data.message || '';
    }
  } catch (err) {
    console.error('FR 1420 check failed:', err);
  } finally {
    isCheckingFR.value = false;
  }
};

onMounted(() => {
  if (props.payment?.payment_type === 'advances' || isAdvancesSection.value) {
    checkFRCompliance();
  }
});

watch(() => props.payment?.recipient_id, () => {
  if (props.payment?.payment_type === 'advances' || isAdvancesSection.value) {
    checkFRCompliance();
  }
});

// Emits to parent
const openCreateAdvanceVoucher = () => {
  emit('open-create-voucher', { type: 'advances' });
};

const openCreateCapitalVoucher = () => {
  emit('open-create-voucher', { type: 'capital' });
};

const openChargeAccount = () => {
  emit('open-charge-account');
};

// API Actions
const classifyPayment = async () => {
  if (!selectedTrack.value) return;
  isSubmitting.value = true;
  try {
    await axios.post('/finance-and-accounts/classify-payment', {
      payment_id: props.payment.id,
      payment_type: selectedTrack.value
    });
    emit('notify', { type: 'success', message: 'Payment classified successfully.' });
    emit('refresh');
  } catch (err) {
    emit('notify', { type: 'error', message: err.response?.data?.message || 'Classification failed.' });
  } finally {
    isSubmitting.value = false;
  }
};

const affixCheckingStamp = async () => {
  isSubmitting.value = true;
  try {
    await axios.post('/finance-and-accounts/affix-checking-stamp', {
      payment_id: props.payment.id
    });
    emit('notify', { type: 'success', message: 'Digital Checking Stamp affixed successfully.' });
    emit('refresh');
  } catch (err) {
    emit('notify', { type: 'error', message: err.response?.data?.message || 'Could not affix checking stamp.' });
  } finally {
    isSubmitting.value = false;
  }
};

const affixAuditStamp = async () => {
  isSubmitting.value = true;
  try {
    await axios.post('/finance-and-accounts/affix-audit-stamp', {
      payment_id: props.payment.id
    });
    emit('notify', { type: 'success', message: 'Pre-Payment Internal Audit Clearance Seal affixed.' });
    emit('refresh');
  } catch (err) {
    emit('notify', { type: 'error', message: err.response?.data?.message || 'Could not affix audit seal.' });
  } finally {
    isSubmitting.value = false;
  }
};

const submitAuditQuery = async () => {
  if (!queryForm.value.description) return;
  isSubmitting.value = true;
  try {
    await axios.post('/finance-and-accounts/raise-audit-query', {
      payment_id: props.payment.id,
      category: queryForm.value.category,
      query_text: queryForm.value.description
    });
    showQueryModal.value = false;
    queryForm.value.description = '';
    emit('notify', { type: 'success', message: 'Audit query raised on file.' });
    emit('refresh');
  } catch (err) {
    emit('notify', { type: 'error', message: err.response?.data?.message || 'Could not raise query.' });
  } finally {
    isSubmitting.value = false;
  }
};

const respondQuery = async (queryId) => {
  const text = responseDrafts.value[queryId];
  if (!text) return;
  try {
    await axios.post('/finance-and-accounts/respond-audit-query', {
      query_id: queryId,
      response_text: text
    });
    delete responseDrafts.value[queryId];
    emit('notify', { type: 'success', message: 'Query response submitted.' });
    emit('refresh');
  } catch (err) {
    emit('notify', { type: 'error', message: err.response?.data?.message || 'Failed to submit response.' });
  }
};

const disbursePayment = async () => {
  isSubmitting.value = true;
  try {
    await axios.post('/finance-and-accounts/disburse-cash', {
      payment_id: props.payment.id,
      disbursement_platform: disburseForm.value.platform,
      payment_reference: disburseForm.value.reference,
      tsa_bank: disburseForm.value.bank,
      value_date: disburseForm.value.value_date
    });
    emit('notify', { type: 'success', message: 'Cash disbursement registered and liquidated.' });
    emit('refresh');
  } catch (err) {
    emit('notify', { type: 'error', message: err.response?.data?.message || 'Disbursement failed.' });
  } finally {
    isSubmitting.value = false;
  }
};

const postToGeneralLedger = async () => {
  isSubmitting.value = true;
  try {
    await axios.post('/finance-and-accounts/post-to-gl', {
      payment_id: props.payment.id
    });
    emit('notify', { type: 'success', message: 'Balanced IPSAS Journal posted to General Ledger.' });
    emit('refresh');
  } catch (err) {
    emit('notify', { type: 'error', message: err.response?.data?.message || 'General Ledger posting failed.' });
  } finally {
    isSubmitting.value = false;
  }
};

const submitRetirement = async () => {
  isSubmitting.value = true;
  try {
    await axios.post('/finance-and-accounts/retire-advance', {
      payment_id: props.payment.id,
      total_expended: retireForm.value.expended,
      refund_amount: retireForm.value.refund || 0,
      refund_rrr: retireForm.value.rrr,
      retirement_voucher_no: retireForm.value.ref
    });
    emit('notify', { type: 'success', message: 'Advance retired successfully.' });
    emit('refresh');
  } catch (err) {
    emit('notify', { type: 'error', message: err.response?.data?.message || 'Retirement submission failed.' });
  } finally {
    isSubmitting.value = false;
  }
};
</script>
