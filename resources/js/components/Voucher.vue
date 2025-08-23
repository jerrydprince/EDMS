<template>
  <div class="max-w-[1250px] mx-auto bg-white p-10 border shadow">
    <!-- Header -->
    <header class="text-center relative mb-6 pb-2 border-b">
      <h1 class="text-lg font-normal">FEDERAL GOVERNMENT OF NIGERIA</h1>
      <h2 class="text-xl font-bold">PAYMENT VOUCHER</h2>
      <div class="absolute top-0 right-0 text-xs leading-5 text-right">
        ORIGINAL <br /> Treasury F1
      </div>
    </header>

    <!-- Voucher Info -->
    <div class="mb-4 text-sm">
      Deptal No <span class="inline-block w-44 border-b border-dotted border-black mx-1"></span>
      Checked and passed for payment at 
      <span class="inline-block w-44 border-b border-dotted border-black mx-1"></span>
    </div>

    <!-- Main Form -->
    <div class="flex mb-6">
      <!-- Vertical Section -->
      <div class="flex space-x-3 text-xs mr-3">
        <div class="rotate-180 writing-vertical">For use in Payment of Advances</div>
        <div class="rotate-180 writing-vertical">Certified the Advance of</div>
        <div class="rotate-180 writing-vertical">No <span class="border-l border-dotted pl-1 italic">Sample Text</span></div>
        <div class="rotate-180 writing-vertical">Has been entered on TF 174 (A) (B) or (C)</div>
        <div class="rotate-180 writing-vertical">Deptal No <span class="border-l border-dotted pl-1 italic">Sample Text</span></div>
        <div class="rotate-180 writing-vertical">Name in Block Letters <span class="border-l border-dotted pl-1 italic">Sample Text</span></div>
        <div class="rotate-180 writing-vertical">Signatures <span class="border-l border-dotted pl-1 italic">Sample Text</span></div>
      </div>

      <!-- Table Section -->
      <div class="flex gap-4 w-3/4">
        <!-- Left Section -->
        <div class="flex-1 space-y-3">
          <!-- Top Row -->
          <table class="w-full border-collapse text-xs">
            <tr>
              <td class="border p-1">1 Data type 3</td>
              <td colspan="3" class="border p-1">4 Source 6</td>
              <td colspan="8" class="border p-1">7 Voucher No 14</td>
            </tr>
            <tr>
              <td class="border h-14">Vo 1</td>
              <td class="border h-14"></td>
              <td class="border h-14"></td>
              <td class="border h-14"></td>
              <td class="border h-14">R</td>
              <td class="border h-14">E</td>
              <td class="border h-14">X</td>
              <td class="border h-14">1</td>
              <td class="border h-14"></td>
              <td class="border h-14"></td>
              <td class="border h-14"></td>
              <td class="border h-14"></td>
            </tr>
          </table>

          <!-- Classification Code -->
          <div class="flex justify-between border-l border-black pl-2 text-sm">
            <span>15</span>
            <span>Classification Code</span>
            <span>26</span>
          </div>

          <table class="w-full border-collapse text-xs">
            <tr>
              <td class="border h-14" colspan="15"></td>
            </tr>
            <tr>
              <td colspan="7" class="border p-1">27 Date 32</td>
              <td colspan="8" class="border p-1">33 Amount 45</td>
            </tr>
            <tr>
              <td colspan="7" class="border h-14"></td>
              <td colspan="8" class="border h-14"></td>
            </tr>
          </table>

          <!-- Bottom Classification -->
          <div class="flex justify-between border-x border-black p-2 text-sm">
            <span>46 Source 48</span>
            <span>49 Classification Code 60</span>
          </div>

          <table class="w-full border-collapse text-xs">
            <tr>
              <td colspan="15" class="border h-14"></td>
            </tr>
          </table>
        </div>

        <!-- Right Section -->
        <div class="w-40">
          <table class="w-full border-collapse border text-xs h-28">
            <tr><td colspan="3" class="border text-center">Station</td></tr>
            <tr><td class="border">Head</td><td colspan="5" class="border"></td></tr>
            <tr><td class="border">S/Head</td><td colspan="5" class="border"></td></tr>
          </table>
        </div>
      </div>
    </div>

    <!-- Payee Section -->
    <div class="mb-4 text-sm">
      Payee <span class="inline-block w-[500px] border-b border-dotted border-black mx-1"></span>
      <br />
      Address <span class="inline-block w-[500px] border-b border-dotted border-black mx-1"></span>
    </div>

    <!-- Details Table -->
    <div class="mb-6">
      <table class="w-full border text-xs border-collapse">
        <thead>
          <tr class="border">
            <th class="border p-2">Date</th>
            <th class="border p-2">Detailed Description of Service/Work</th>
            <th class="border p-2">Rate</th>
            <th class="border p-2">₦</th>
            <th class="border p-2">K</th>
          </tr>
        </thead>
        <tbody>
          <tr class="h-48 border">
            <td class="border"></td>
            <td class="border"></td>
            <td class="border"></td>
            <td class="border"></td>
            <td class="border"></td>
          </tr>
          <tr>
            <td class="border"></td>
            <td class="border p-2">Checked and passed for — Insert Amounts in words</td>
            <td class="border text-right">Total ₦</td>
            <td class="border"></td>
            <td class="border"></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Bottom Section -->
    <div class="flex">
      <!-- Left -->
      <div class="flex-1 text-xs space-y-1">
        <div>Payable at <span class="inline-block w-28 border-b border-dotted border-black"></span></div>
        <div>Signature <span class="inline-block w-28 border-b border-dotted border-black"></span></div>
        <div>Name in Block Letters <span class="inline-block w-28 border-b border-dotted border-black"></span></div>
        <div>Station <span class="inline-block w-28 border-b border-dotted border-black"></span> Date <span class="inline-block w-28 border-b border-dotted border-black"></span></div>
        <div>Paying Officer’s <em>Signature</em> <span class="inline-block w-28 border-b border-dotted border-black"></span></div>
        <div>Name in Block Letters <span class="inline-block w-28 border-b border-dotted border-black"></span></div>
      </div>

      <!-- Right -->
      <div class="flex-1 border text-xs p-3 ml-4">
        <h3 class="text-center font-bold mb-2">CERTIFICATE</h3>
        <p class="text-justify mb-2">
          I Certify that the above amount is correct and was incurred under
          the authority quoted; that the service has been duly performed; that the rate/price
          charged is according to regulation/contract and is fair and reasonable; that the amount of
          <span class="inline-block w-28 border-b border-dotted border-black"></span> Kobo may be paid under
        </p>
        <p class="text-justify">the classification quoted</p>
        <div class="border-b mt-6"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
// Optional: add this helper class for vertical text in Tailwind
</script>

<style>
.writing-vertical {
  writing-mode: vertical-rl;
  text-orientation: mixed;
}
</style>
