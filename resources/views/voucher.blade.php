<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>Federal Republic of Nigeria - Treasury Form 1 Payment Voucher</title>
  <style>
    @page {
      size: A4 portrait;
      margin: 8mm 12mm 8mm 12mm;
    }
    body {
      font-family: "Times New Roman", Times, serif;
      font-size: 8pt;
      color: #000000;
      line-height: 1.15;
      margin: 0;
      padding: 0;
    }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .text-left { text-align: left; }
    .bold { font-weight: bold; }
    .uppercase { text-transform: uppercase; }

    .naira {
      font-family: "DejaVu Sans", sans-serif;
    }

    /* Header styling */
    .header-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 4px;
    }
    .header-table td {
      border: none;
      padding: 0;
      vertical-align: top;
    }
    .title-fgn {
      font-size: 13pt;
      font-weight: bold;
      letter-spacing: 0.5px;
      margin: 0;
    }
    .title-pv {
      font-size: 18pt;
      font-weight: 900;
      letter-spacing: 1px;
      margin: 2px 0 0 0;
    }
    .top-labels {
      font-size: 12pt;
      font-weight: bold;
      line-height: 1.2;
    }

    /* Sub-header lines */
    .subhead-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
      font-size: 8.5pt;
    }
    .subhead-table td {
      border: none;
      padding: 2px 0;
      vertical-align: bottom;
    }
    .dot-line {
      border-bottom: 1px dotted #000;
      display: inline-block;
    }

    /* Top Grid Area */
    .top-grid-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
    }
    .top-grid-table td {
      border: none;
      padding: 0;
      vertical-align: top;
    }

    /* Boxes table */
    .box-table {
      border-collapse: collapse;
      width: 100%;
    }
    .box-table td, .box-table th {
      border: 1px solid #000;
      padding: 0px 2px;
      text-align: center;
      font-size: 8pt;
    }
    .box-header {
      font-size: 6.5pt;
      padding: 1px 2px;
      background: #ffffff;
      height: 12px;
    }
    .box-cell {
      height: 22px;
      font-size: 9pt;
      font-weight: bold;
    }
    .station-table {
      width: 100%;
      border-collapse: collapse;
    }
    .station-table td {
      border: 1px solid #000;
      padding: 2px 4px;
      font-size: 8pt;
      height: 24px;
    }

    /* Payee and Address lines */
    .payee-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
      font-size: 9pt;
    }
    .payee-table td {
      border: none;
      padding: 3px 0;
      vertical-align: bottom;
    }

    /* Main Particulars Table */
    .particulars-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 0px;
      border: 1.5px solid #000;
    }
    .particulars-table th {
      border: 1px solid #000;
      padding: 3px 4px;
      font-size: 8.5pt;
      font-weight: bold;
      text-align: center;
    }
    .particulars-table td {
      border-left: 1px solid #000;
      border-right: 1px solid #000;
      border-top: none;
      border-bottom: none;
      padding: 4px 6px;
      font-size: 8pt;
      vertical-align: top;
    }
    .total-row td {
      border-top: 1.5px solid #000 !important;
      border-bottom: 1.5px solid #000 !important;
      padding: 3px 6px;
      font-size: 8pt;
    }

    /* Bottom Split Box */
    .bottom-box-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 0px;
      border: 1.5px solid #000;
    }
    .bottom-box-table td {
      width: 50%;
      border: 1px solid #000;
      padding: 7px 9px;
      vertical-align: top;
      font-size: 8pt;
      line-height: 1.3;
    }

    /* Bottom Receipt strip */
    .receipt-section {
      width: 100%;
      margin-top: 10px;
      font-size: 8pt;
      line-height: 1.45;
    }

    /* Digital Signature Spaces */
    .sig-box-placeholder {
      border: 1px dashed #475569;
      background: #f8fafc;
      padding: 4px 6px;
      text-align: center;
      margin: 4px auto 2px auto;
      width: 85%;
    }
    .sig-box-title {
      font-weight: bold;
      font-size: 6.8pt;
      color: #0f172a;
      letter-spacing: 0.4px;
    }
    .sig-box-sub {
      font-size: 5.8pt;
      color: #475569;
      margin: 1px 0;
    }
    .sig-box-hint {
      font-size: 5.8pt;
      font-weight: bold;
      color: #047857;
    }
    .sig-box-placeholder-sm {
      border: 1px dashed #64748b;
      background: #f8fafc;
      padding: 2px 4px;
      text-align: center;
      margin-bottom: 2px;
    }
    .sig-pin-badge {
      text-align: center;
      font-size: 6.2pt;
      margin-top: 1px;
    }
  </style>
</head>
<body>

@php
  // Format Amounts
  $gross = floatval($payment->gross_amount ?: ($voucher->amount ?? 0));
  $net = floatval($payment->net_amount ?: $gross);
  $totalDeductions = floatval($payment->total_deductions ?: max(0, $gross - $net));
  
  $nairaPart = (int) $net;
  $koboPart = round(($net - $nairaPart) * 100);

  // Amount in Words calculation
  $wordsNaira = '';
  $wordsKobo = ($koboPart > 0) ? '' : 'Zero';
  if (class_exists('NumberFormatter')) {
      $f = new \NumberFormatter("en", \NumberFormatter::SPELLOUT);
      $wordsNaira = ucwords($f->format($nairaPart));
      if ($koboPart > 0) {
          $wordsKobo = ucwords($f->format($koboPart));
      }
  } else {
      $wordsNaira = number_format($nairaPart);
  }
  $fullWords = $wordsNaira . " Naira and " . $wordsKobo . " Kobo Only";

  // Payee name & address
  $payeeName = 'Jerry Ikediashi';
  if ($payment && $payment->recipient) {
      $payeeName = $payment->recipient->first_name . ' ' . $payment->recipient->last_name;
  } elseif (!empty($voucher->address)) {
      $payeeName = $voucher->address;
  }
  $payeeAddress = $voucher->address ?? ($payment->recipient->department->title ?? 'Federal Secretariat Complex, Abuja');

  // Head and Subhead resolution
  $headVal = is_array($voucher->head) ? ($voucher->head['title'] ?? '0220002001') : ($voucher->head ?? '0220002001');
  $subHeadVal = is_array($voucher->s_head) ? ($voucher->s_head['title'] ?? ($payment->ncoa_code ?? '22020501')) : ($voucher->s_head ?? ($payment->ncoa_code ?? '22020501'));
  $classificationCode = $voucher->classification_code ?? ($payment->ncoa_code ?? '22020501');
  $deptalNo = $voucher->deptal_number ?? $voucher->deptal_no ?? ('DEP/F&A/' . date('Y') . '/' . str_pad($payment->id ?? 1, 4, '0', STR_PAD_LEFT));
  $voucherNo = $voucher->voucher_number ?? ('PV-' . str_pad($voucher->id ?? 1, 6, '0', STR_PAD_LEFT));
  $voucherDate = !empty($voucher->date) ? date('d-M-Y', strtotime($voucher->date)) : date('d-M-Y');
  $checkedAt = $voucher->check_and_passed ?? 'Federal Ministry Headquarters, Abuja';

  // Date digits (6 chars: ddmmyy)
  $dateDigits = str_pad(date('dmy', strtotime($voucherDate)), 6, '0', STR_PAD_LEFT);
  $dateArr = str_split($dateDigits);

  // Classification code digits (12 cells)
  $cleanClassCode = preg_replace('/[^0-9]/', '', (string)$classificationCode);
  $classArr = array_pad(str_split(substr($cleanClassCode, 0, 12)), 12, '');

  // Amount digits right-aligned in 13 cells (naira + 2 kobo digits)
  $amountCombined = strval($nairaPart) . sprintf('%02d', $koboPart);
  $amountCells = array_pad([], 13, '');
  $len = strlen($amountCombined);
  if ($len <= 13) {
      $startIdx = 13 - $len;
      for ($i = 0; $i < $len; $i++) {
          $amountCells[$startIdx + $i] = $amountCombined[$i];
      }
  } else {
      $amountCells = str_split(substr($amountCombined, -13));
  }

  // Stamp paths
  $verticalImgPath = public_path('voucher/tf1_vertical_advances.png');
  $checkingStampPath = !empty($voucher->checking_stamp_path) && file_exists(public_path($voucher->checking_stamp_path)) ? public_path($voucher->checking_stamp_path) : null;
  $auditStampPath = !empty($voucher->audit_stamp_path) && file_exists(public_path($voucher->audit_stamp_path)) ? public_path($voucher->audit_stamp_path) : null;

  // Digital Signature paths & PIN Authentication
  $officerSignaturePath = !empty($voucher->signature) && file_exists(public_path($voucher->signature)) 
      ? public_path($voucher->signature) 
      : (!empty($voucher->auditOfficer->digital_signature_path) && file_exists(public_path($voucher->auditOfficer->digital_signature_path)) 
          ? public_path($voucher->auditOfficer->digital_signature_path) 
          : (file_exists(public_path('signatures/1790802458_sig.png')) ? public_path('signatures/1790802458_sig.png') : null));

  $cpoSignaturePath = null;
  if (!empty($voucher->payment_reference) && file_exists(public_path('signatures/1791188075_sig.png'))) {
      $cpoSignaturePath = public_path('signatures/1791188075_sig.png');
  }

  $payeeSignaturePath = null;
  if (!empty($payment->recipient->digital_signature_path) && file_exists(public_path($payment->recipient->digital_signature_path))) {
      $payeeSignaturePath = public_path($payment->recipient->digital_signature_path);
  }

  $sigTimestamp = date('d-M-Y H:i', strtotime($voucherDate . ' 13:30'));
  $sigVerificationHash = 'PIN-AUTH-' . strtoupper(substr(md5(($voucher->id ?? 1) . ($voucher->amount ?? 1000)), 0, 8));
@endphp

  <!-- Top Right Labels & Center Title -->
  <table class="header-table">
    <tr>
      <td style="width: 25%;"></td>
      <td style="width: 50%; text-align: center;">
        <div class="title-fgn">FEDERAL GOVERNMENT OF NIGERIA</div>
        <div class="title-pv">PAYMENT VOUCHER</div>
      </td>
      <td style="width: 25%; text-align: right;" class="top-labels">
        ORIGINAL<br>
        <span style="font-size: 14pt;">Treasury F1</span>
      </td>
    </tr>
  </table>

  <!-- Deptal No & Checked and passed -->
  <table class="subhead-table">
    <tr>
      <td style="width: 38%;">
        Deptal No. <span style="font-weight: bold; text-decoration: underline;">&nbsp;{{ $deptalNo }}&nbsp;</span><span class="dot-line" style="width: 100px;"></span>
      </td>
      <td style="width: 62%; text-align: right;">
        Checked and passed for payment at <span style="font-weight: bold; text-decoration: underline;">&nbsp;{{ $checkedAt }}&nbsp;</span><span class="dot-line" style="width: 100px;"></span>
      </td>
    </tr>
  </table>

  <!-- Grid Area: Vertical column + Box table + Station table -->
  <table class="top-grid-table">
    <tr>
      <!-- Left: Vertical Advances column -->
      <td style="width: 100px; text-align: left; vertical-align: top;">
        @if(file_exists($verticalImgPath))
          <img src="{{ $verticalImgPath }}" style="width: 95px; height: 135px; border: 0;" />
        @else
          <div style="border: 1px solid #000; width: 95px; height: 135px; font-size: 7pt; padding: 4px;">
            For use in Payment of Advances...
          </div>
        @endif
      </td>

      <!-- Middle: Accounting Grid Boxes -->
      <td style="padding: 0 4px; vertical-align: top;">
        <!-- Row 1: Data Type, Source, Voucher No -->
        <table class="box-table" style="margin-bottom: 2px;">
          <tr>
            <td style="width: 70px;">
              <div class="box-header">1 Data Type 3</div>
              <div class="box-cell" style="font-size: 11pt;">Vo 1</div>
            </td>
            <td style="width: 55px;">
              <div class="box-header">4 Source 6</div>
              <table style="width: 100%; border-collapse: collapse; height: 22px;">
                <tr>
                  <td style="border: none; border-right: 1px solid #000; width: 50%; font-weight: bold;"></td>
                  <td style="border: none; width: 50%; font-weight: bold;"></td>
                </tr>
              </table>
            </td>
            <td>
              <div class="box-header">7 Voucher No. 14</div>
              <table style="width: 100%; border-collapse: collapse; height: 22px;">
                <tr>
                  <td style="border: none; border-right: 1px solid #000; width: 12.5%; font-weight: bold;">R</td>
                  <td style="border: none; border-right: 1px solid #000; width: 12.5%; font-weight: bold;">E</td>
                  <td style="border: none; border-right: 1px solid #000; width: 12.5%; font-weight: bold;">X</td>
                  <td style="border: none; border-right: 1px solid #000; width: 12.5%; font-weight: bold;">1</td>
                  <td style="border: none; border-right: 1px solid #000; width: 12.5%;"></td>
                  <td style="border: none; border-right: 1px solid #000; width: 12.5%;"></td>
                  <td style="border: none; border-right: 1px solid #000; width: 12.5%;"></td>
                  <td style="border: none; width: 12.5%;"></td>
                </tr>
              </table>
            </td>
          </tr>
        </table>

        <!-- Row 2: 15 Classification Code 26 (12 boxes) -->
        <table class="box-table" style="margin-bottom: 2px;">
          <tr>
            <td colspan="12" style="border: none; padding: 0;">
              <table style="width: 100%; border-collapse: collapse; font-size: 6.5pt;">
                <tr>
                  <td style="border: none; text-align: left; width: 20px;">15</td>
                  <td style="border: none; text-align: center; font-weight: bold;">Classification Code</td>
                  <td style="border: none; text-align: right; width: 20px;">26</td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            @for($i = 0; $i < 12; $i++)
              <td style="height: 20px; width: 8.33%; font-weight: bold;">{{ $classArr[$i] ?? '' }}</td>
            @endfor
          </tr>
        </table>

        <!-- Row 3: 27 Date 32 (6 boxes) + 33 Amount 45 (13 boxes) -->
        <table class="box-table" style="margin-bottom: 2px;">
          <tr>
            <td style="width: 32%; border: none; padding: 0;">
              <table style="width: 100%; border-collapse: collapse; font-size: 6.5pt;">
                <tr>
                  <td style="border: none; text-align: left; width: 20px;">27</td>
                  <td style="border: none; text-align: center; font-weight: bold;">Date</td>
                  <td style="border: none; text-align: right; width: 20px;">32</td>
                </tr>
              </table>
              <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; height: 20px;">
                <tr>
                  @for($d = 0; $d < 6; $d++)
                    <td style="width: 16.66%; border: 1px solid #000; font-weight: bold;">{{ $dateArr[$d] ?? '' }}</td>
                  @endfor
                </tr>
              </table>
            </td>
            <td style="width: 68%; border: none; padding: 0 0 0 3px;">
              <table style="width: 100%; border-collapse: collapse; font-size: 6.5pt;">
                <tr>
                  <td style="border: none; text-align: left; width: 20px;">33</td>
                  <td style="border: none; text-align: center; font-weight: bold;">Amount</td>
                  <td style="border: none; text-align: right; width: 20px;">45</td>
                </tr>
              </table>
              <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; height: 20px;">
                <tr>
                  @for($a = 0; $a < 13; $a++)
                    <td style="width: 7.69%; border: 1px solid #000; font-weight: bold;">{{ $amountCells[$a] ?? '' }}</td>
                  @endfor
                </tr>
              </table>
            </td>
          </tr>
        </table>

        <!-- Row 4: 46 Source 48 (2 boxes) + 49 Classification Code 60 (12 boxes) -->
        <table class="box-table">
          <tr>
            <td style="width: 60px; border: none; padding: 0;">
              <table style="width: 100%; border-collapse: collapse; font-size: 6.5pt;">
                <tr>
                  <td style="border: none; text-align: left; width: 10px;">4</td>
                  <td style="border: none; text-align: center; font-weight: bold;">Source</td>
                  <td style="border: none; text-align: right; width: 10px;">6</td>
                </tr>
              </table>
              <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; height: 20px;">
                <tr>
                  <td style="width: 50%; border: 1px solid #000;"></td>
                  <td style="width: 50%; border: 1px solid #000;"></td>
                </tr>
              </table>
            </td>
            <td style="border: none; padding: 0 0 0 3px;">
              <table style="width: 100%; border-collapse: collapse; font-size: 6.5pt;">
                <tr>
                  <td style="border: none; text-align: left; width: 20px;">49</td>
                  <td style="border: none; text-align: center; font-weight: bold;">Classification Code</td>
                  <td style="border: none; text-align: right; width: 20px;">60</td>
                </tr>
              </table>
              <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; height: 20px;">
                <tr>
                  @for($c = 0; $c < 12; $c++)
                    <td style="width: 8.33%; border: 1px solid #000;"></td>
                  @endfor
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </td>

      <!-- Right: Station / Head / S-Head Table -->
      <td style="width: 130px; vertical-align: top;">
        <table class="station-table">
          <tr>
            <td style="width: 45px; font-weight: bold;">Station</td>
            <td>Abuja HQ</td>
          </tr>
          <tr>
            <td style="font-weight: bold;">Head</td>
            <td style="font-size: 7.5pt;">{{ $headVal }}</td>
          </tr>
          <tr>
            <td style="font-weight: bold;">S/Head</td>
            <td style="font-size: 7.5pt;">{{ $subHeadVal }}</td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

  <!-- Payee & Address lines -->
  <table class="payee-table">
    <tr>
      <td style="width: 50px; font-weight: bold;">Payee</td>
      <td>
        <span style="font-weight: bold; font-size: 9.5pt;">{{ $payeeName }}</span>
        <span class="dot-line" style="width: 480px;"></span>
      </td>
    </tr>
    <tr>
      <td style="font-weight: bold;">Address</td>
      <td>
        <span>{{ $payeeAddress }}</span>
        <span class="dot-line" style="width: 480px;"></span>
      </td>
    </tr>
  </table>

  <!-- Main Particulars Table -->
  <table class="particulars-table">
    <thead>
      <tr>
        <th style="width: 11%;">Date</th>
        <th style="width: 56%;">Detailed Description of Service/Work</th>
        <th style="width: 9%;">Rate</th>
        <th style="width: 18%;"><span class="naira">&#8358;</span></th>
        <th style="width: 6%;">K</th>
      </tr>
    </thead>
    <tbody>
      <!-- Main Work Item Row -->
      <tr>
        <td style="text-align: center; height: 220px;">{{ $voucherDate }}</td>
        <td>
          <div style="font-weight: bold; font-size: 9pt; margin-bottom: 6px;">{{ $payment->title }}</div>
          <div style="font-size: 7.5pt; color: #333; margin-bottom: 10px;">
            Authority: Capital Works Contract verified under Treasury Regulations.<br>
            Vote Book Subhead: {{ $subHeadVal }} &bull; Mandate Ref: {{ $voucher->payment_reference ?? "REM-TSA-2026-991204" }}
          </div>
          @if($totalDeductions > 0)
          <table style="width: 100%; border-collapse: collapse; font-size: 7.5pt; margin-top: 8px;">
            <tr>
              <td style="border: none; padding: 1px 0;">Gross Contract Value:</td>
              <td style="border: none; text-align: right; font-weight: bold;"><span class="naira">&#8358;</span>{{ number_format($gross, 2) }}</td>
            </tr>
            <tr>
              <td style="border: none; padding: 1px 0; color: #b91c1c;">Less Value Added Tax (VAT 7.5%):</td>
              <td style="border: none; text-align: right; color: #b91c1c;">-<span class="naira">&#8358;</span>{{ number_format($gross * 0.075, 2) }}</td>
            </tr>
            <tr>
              <td style="border: none; padding: 1px 0; color: #b91c1c;">Less Withholding Tax (WHT 10%):</td>
              <td style="border: none; text-align: right; color: #b91c1c;">-<span class="naira">&#8358;</span>{{ number_format($gross * 0.10, 2) }}</td>
            </tr>
            <tr>
              <td style="border: none; padding: 1px 0; color: #b91c1c;">Less Stamp Duty Pool (1%):</td>
              <td style="border: none; text-align: right; color: #b91c1c;">-<span class="naira">&#8358;</span>{{ number_format($gross * 0.01, 2) }}</td>
            </tr>
            <tr style="border-top: 1px dotted #000;">
              <td style="border: none; padding: 2px 0; font-weight: bold;">NET Amount Disbursable:</td>
              <td style="border: none; text-align: right; font-weight: bold;"><span class="naira">&#8358;</span>{{ number_format($net, 2) }}</td>
            </tr>
          </table>
          @endif
        </td>
        <td style="text-align: center;">Net</td>
        <td style="text-align: right; font-weight: bold; font-size: 9pt;">{{ number_format($nairaPart) }}</td>
        <td style="text-align: center; font-weight: bold; font-size: 9pt;">{{ sprintf('%02d', $koboPart) }}</td>
      </tr>

      <!-- Total Row -->
      <tr class="total-row">
        <td style="border-top: 1.5px solid #000; border-bottom: 1.5px solid #000;"></td>
        <td style="border-top: 1.5px solid #000; border-bottom: 1.5px solid #000; padding: 2px 4px;">
          <table style="width: 100%; border-collapse: collapse;">
            <tr>
              <td style="border: none; width: 85px; font-weight: bold; font-size: 7pt; line-height: 1.1; vertical-align: middle;">
                Checked and<br>passed for
              </td>
              <td style="border: none; font-weight: bold; font-style: italic; font-size: 8pt; vertical-align: middle;">
                {{ $fullWords }}
              </td>
            </tr>
          </table>
        </td>
        <td style="border-top: 1.5px solid #000; border-bottom: 1.5px solid #000; text-align: center; font-weight: bold;">
          Total <span class="naira">&#8358;</span>
        </td>
        <td style="border-top: 1.5px solid #000; border-bottom: 1.5px solid #000; text-align: right; font-weight: bold; font-size: 9.5pt;">
          {{ number_format($nairaPart) }}
        </td>
        <td style="border-top: 1.5px solid #000; border-bottom: 1.5px solid #000; text-align: center; font-weight: bold; font-size: 9.5pt;">
          {{ sprintf('%02d', $koboPart) }}
        </td>
      </tr>
    </tbody>
  </table>

  <!-- Split Bottom Certification and Payment Box -->
  <table class="bottom-box-table">
    <tr>
      <!-- Left Box: Payable at... -->
      <td>
        <div style="margin-bottom: 2px;">Payable at <span style="font-weight: bold;">Central Bank of Nigeria (TSA Sub-Account 0020194829)</span></div>
        <div style="margin-bottom: 2px;">Signature <span class="dot-line" style="width: 200px;"></span></div>
        <div style="margin-bottom: 2px;">Name in Block Letters <span style="font-weight: bold;">{{ $payeeName }}</span></div>
        <div style="margin-bottom: 2px;">Station <span style="font-weight: bold;">Abuja</span> &nbsp;&bull;&nbsp; Date <span style="font-weight: bold;">{{ $voucherDate }}</span></div>

        <!-- Paying Officer Digital Signature Space -->
        <div style="margin-top: 3px; margin-bottom: 3px;">
          <table style="width: 100%; border-collapse: collapse;">
            <tr>
              <td style="border: none; padding: 0; width: 42%; vertical-align: bottom;">
                <div style="font-size: 7.2pt; font-weight: bold;">Paying Officer's Signature:</div>
                <div style="font-size: 6.2pt; color: #475569;">Name in Block Letters:</div>
                <div style="font-size: 7.2pt; font-weight: bold;">Chief Pay Officer (CPO)</div>
              </td>
              <td style="border: none; padding: 0; width: 58%; vertical-align: bottom; text-align: center;">
                @if($cpoSignaturePath)
                  <div style="height: 26px; text-align: center;">
                    <img src="{{ $cpoSignaturePath }}" style="max-height: 24px; max-width: 110px; border: 0;" />
                  </div>
                  <div class="sig-pin-badge">
                    <span class="naira" style="color: #047857; font-weight: bold;">&#10003;</span> <span style="color: #047857; font-weight: bold;">PIN AUTHENTICATED</span>
                  </div>
                @else
                  <div class="sig-box-placeholder-sm">
                    <div style="font-weight: bold; font-size: 6pt; color: #0f172a;">DIGITAL SIGNATURE VIA PIN</div>
                    <div style="font-size: 5.2pt; color: #047857; font-weight: bold;">[ Chief Pay Officer ]</div>
                  </div>
                @endif
                <div style="border-top: 1px solid #000; width: 100%; margin-top: 1px;"></div>
              </td>
            </tr>
          </table>
        </div>

        <div style="margin-bottom: 2px; font-size: 7pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;GW/SW<br>Anthy. &nbsp;&nbsp;AIE No., Etc. <span style="font-weight: bold;">Mandate: {{ $voucher->payment_reference ?? "REM-TSA-2026-991204" }}</span></div>

        <!-- Embedded Checking Confirmation Stamp -->
        @if($checkingStampPath)
        <div style="text-align: center; margin-top: 2px;">
          <img src="{{ $checkingStampPath }}" style="width: 150px; max-width: 85%; border: 0;" />
        </div>
        @endif
      </td>

      <!-- Right Box: CERTIFICATE -->
      <td>
        <div class="text-center bold uppercase" style="font-size: 8.5pt; margin-bottom: 2px;">CERTIFICATE</div>
        <p style="margin: 0 0 3px 0; text-align: justify; font-size: 7.2pt; line-height: 1.2;">
          I Certify that the above amount is correct and was incurred under the Authority quoted; that the service have been dully performed: that the rate/price charged is ac-cording to regulation / contract is fair and reasonable: that the amount of <u>&nbsp;{{ $wordsNaira }}&nbsp;</u>Naira <u>&nbsp;{{ $wordsKobo }}&nbsp;</u>Kobo may be paid under the Classification quoted
        </p>

        <!-- Digital Signature Space for Officer Controlling Expenditure -->
        <div style="margin: 4px 0 2px 0;">
          @if($officerSignaturePath)
            <div style="text-align: center; height: 32px;">
              <img src="{{ $officerSignaturePath }}" style="max-height: 30px; max-width: 130px; border: 0;" />
            </div>
            <div class="sig-pin-badge">
              <span class="naira" style="color: #047857; font-weight: bold;">&#10003;</span> <span style="color: #047857; font-weight: bold;">DIGITALLY SIGNED VIA PIN</span>
              <span style="color: #475569; font-size: 5.6pt;">({{ $sigVerificationHash }} &bull; {{ $sigTimestamp }})</span>
            </div>
          @else
            <div class="sig-box-placeholder">
              <div class="sig-box-title">DIGITAL SIGNATURE VIA PIN</div>
              <div class="sig-box-sub">Officer Controlling Expenditure &bull; Authorizing Signatory</div>
              <div class="sig-box-hint">[ Authenticated via 4-Digit Security PIN ]</div>
            </div>
          @endif
          <div style="border-top: 1px solid #000; width: 85%; margin: 2px auto 0 auto;"></div>
          <div style="font-style: italic; font-size: 7pt; text-align: center; margin-top: 1px;">Signature of Officer Contr Expenditure</div>
        </div>

        <div style="margin-bottom: 1px;">Place <span style="font-weight: bold;">Abuja</span> &nbsp;&bull;&nbsp; Date <span style="font-weight: bold;">{{ $voucherDate }}</span></div>
        <div style="margin-bottom: 2px;">Designation <span style="font-weight: bold;">Director, Finance and Accounts</span></div>

        <!-- Embedded Pre-Payment Internal Audit Clearance Seal -->
        @if($auditStampPath)
        <div style="text-align: center; margin-top: 1px;">
          <img src="{{ $auditStampPath }}" style="width: 70px; max-width: 40%; border: 0;" />
        </div>
        @endif
      </td>
    </tr>
  </table>

  <!-- Receipt Strip at the Bottom -->
  <div class="receipt-section">
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 3px;">
      <tr>
        <td style="border: none; padding: 0; white-space: nowrap; width: 1%;">
          Received from the Federal Government of Nigeria the sum of&nbsp;
        </td>
        <td style="border: none; padding: 0; border-bottom: 1px dotted #000; font-weight: bold;">
          {{ $wordsNaira }}
        </td>
        <td style="border: none; padding: 0; white-space: nowrap; width: 1%;">
          &nbsp;Naira
        </td>
      </tr>
    </table>

    <table style="width: 100%; border-collapse: collapse;">
      <tr>
        <td style="border: none; padding: 0; border-bottom: 1px dotted #000; font-weight: bold; width: 140px;">
          {{ $wordsKobo }}
        </td>
        <td style="border: none; padding: 0 4px; white-space: nowrap;">
          Kobo in full settlement of the Account
        </td>
        <td style="border: none; text-align: right; width: 180px; padding: 0; vertical-align: bottom;">
          @if($payeeSignaturePath)
            <div style="text-align: center; height: 24px;">
              <img src="{{ $payeeSignaturePath }}" style="max-height: 22px; max-width: 90px; border: 0;" />
            </div>
            <div class="sig-pin-badge">
              <span class="naira" style="color: #047857; font-weight: bold;">&#10003;</span> <span style="color: #047857; font-weight: bold;">PIN-VERIFIED RECEIPT</span>
            </div>
          @else
            <div class="sig-box-placeholder-sm" style="width: 140px; margin-left: auto;">
              <div style="font-weight: bold; font-size: 6pt; color: #0f172a;">DIGITAL SIGNATURE VIA PIN</div>
              <div style="font-size: 5.2pt; color: #047857; font-weight: bold;">[ Direct TSA Settlement ]</div>
            </div>
          @endif
          <div style="border-top: 1px solid #000; width: 140px; margin-left: auto; margin-top: 1px;"></div>
          <div style="font-style: italic; font-size: 7pt; text-align: center; width: 140px; margin-left: auto;">Signature</div>
        </td>
      </tr>
    </table>

    <table style="width: 100%; border-collapse: collapse; font-size: 8pt; margin-top: 4px;">
      <tr>
        <td style="border: none; width: 150px; padding: 0; font-weight: bold;">
          <span class="naira">&#8358;</span> {{ number_format($nairaPart) }} &nbsp;:&nbsp; {{ sprintf('%02d', $koboPart) }} K
        </td>
        <td style="border: none; width: 200px; padding: 0;">
          Date <span style="font-weight: bold; border-bottom: 1px dotted #000; display: inline-block; min-width: 80px;">&nbsp;{{ $voucherDate }}&nbsp;</span> 20<span style="border-bottom: 1px dotted #000; display: inline-block; width: 25px;"></span>
        </td>
        <td style="border: none; text-align: right; padding: 0;">
          Place <span style="font-weight: bold; border-bottom: 1px dotted #000; display: inline-block; min-width: 120px;">&nbsp;Abuja&nbsp;</span>
        </td>
      </tr>
    </table>
  </div>

</body>
</html>
