<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Local Purchase Order - {{ $procurement->lpo_number ?? $procurement->bpp_ref }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #1a202c;
            margin: 0;
            padding: 40px;
            background: #f7fafc;
        }
        .container {
            max-width: 850px;
            margin: 0 auto;
            background: #ffffff;
            padding: 50px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border: 2px solid #2d3748;
            position: relative;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #2d3748;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        .crest {
            font-size: 26px;
            font-weight: bold;
            letter-spacing: 2px;
            color: #1b4d3e;
            text-transform: uppercase;
        }
        .sub-header {
            font-size: 14px;
            letter-spacing: 1px;
            text-transform: uppercase;
            font-weight: bold;
            color: #4a5568;
            margin-top: 5px;
        }
        .title {
            font-size: 20px;
            font-weight: bold;
            text-decoration: underline;
            margin-top: 15px;
            color: #1a202c;
            letter-spacing: 1px;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 25px;
            font-size: 14px;
        }
        .meta-box {
            padding: 10px 14px;
            border: 1px solid #cbd5e0;
            background: #f8fafc;
            border-radius: 4px;
        }
        .meta-box strong {
            display: inline-block;
            width: 140px;
            color: #4a5568;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            font-size: 14px;
        }
        table, th, td {
            border: 1px solid #4a5568;
        }
        th {
            background-color: #edf2f7;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        td {
            padding: 10px;
        }
        .amount-row td {
            font-weight: bold;
            font-size: 15px;
            background: #f7fafc;
        }
        .terms {
            font-size: 12px;
            line-height: 1.5;
            color: #4a5568;
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
            margin-bottom: 30px;
        }
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 30px;
            font-size: 13px;
        }
        .sig-box {
            border-top: 1px solid #718096;
            padding-top: 8px;
            text-align: center;
        }
        .sig-title {
            font-weight: bold;
            color: #2d3748;
        }
        .print-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #1b4d3e;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .print-btn:hover {
            background: #153e32;
        }
        .seal {
            position: absolute;
            top: 40px;
            right: 40px;
            border: 3px double #1b4d3e;
            color: #1b4d3e;
            padding: 8px 14px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            border-radius: 6px;
        }
        @media print {
            body { background: white; padding: 0; }
            .container { box-shadow: none; border: none; padding: 20px; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>

    <button class="print-btn" onclick="window.print()">🖨️ Print / Save PDF</button>

    <div class="container">
        <div class="seal">
            BPP ACT 2007<br>
            COMPLIANT ORDER
        </div>

        <div class="header">
            <div class="crest">Federal Republic of Nigeria</div>
            <div class="sub-header">Federal Department / Parastatal • Electronic Document Management System</div>
            <div class="sub-header" style="color: #1b4d3e; font-weight: 800;">Directorate of Procurement</div>
            <div class="title">LOCAL PURCHASE ORDER (LPO)</div>
        </div>

        <div class="meta-grid">
            <div class="meta-box">
                <div><strong>LPO Number:</strong> <span style="font-weight: bold; color: #1b4d3e;">{{ $procurement->lpo_number ?? 'PENDING' }}</span></div>
                <div><strong>BPP File Ref:</strong> {{ $procurement->bpp_ref }}</div>
                <div><strong>Threshold Level:</strong> {{ $procurement->threshold_level }}</div>
                <div><strong>Date of Issue:</strong> {{ $procurement->updated_at ? $procurement->updated_at->format('d/m/Y') : date('d/m/Y') }}</div>
            </div>
            <div class="meta-box">
                <div><strong>Vendor / Contractor:</strong> {{ $procurement->vendor_name }}</div>
                <div><strong>RC / BN Number:</strong> {{ $procurement->vendor_rc_number ?? 'N/A' }}</div>
                <div><strong>Bank Name:</strong> {{ $procurement->vendor_bank_name ?? 'N/A' }}</div>
                <div><strong>Account Number:</strong> {{ $procurement->vendor_account_no ?? 'N/A' }}</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">Item</th>
                    <th style="width: 55%;">Description of Goods / Scope of Works</th>
                    <th style="width: 17%;">Delivery Period</th>
                    <th style="width: 20%; text-align: right;">Total Amount (₦)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: center;">1</td>
                    <td>
                        <strong>{{ $procurement->title }}</strong>
                        <p style="font-size: 12px; color: #718096; margin: 5px 0 0 0;">
                            Executed in strict accordance with the Public Procurement Act (BPP 2007), standard ministerial specifications, and competitive bidding parameters.
                        </p>
                    </td>
                    <td>{{ $procurement->delivery_period_days }} Calendar Days</td>
                    <td style="text-align: right; font-weight: bold;">
                        ₦{{ number_format($procurement->estimated_amount, 2) }}
                    </td>
                </tr>
                <tr class="amount-row">
                    <td colspan="3" style="text-align: right;">GRAND TOTAL STATUTORY CONTRACT SUM:</td>
                    <td style="text-align: right; color: #1b4d3e;">₦{{ number_format($procurement->estimated_amount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="terms">
            <strong>STATUTORY TERMS & CONDITIONS OF CONTRACT:</strong>
            <ol style="margin-top: 5px; padding-left: 20px;">
                <li>Goods or services delivered under this LPO are subject to technical inspection and formal certification by the Ministerial Verification Committee.</li>
                <li>Invoices must be accompanied by the original copy of this Local Purchase Order and a Certificate of Job Completion / Goods Received Note (GRN).</li>
                <li>Statutory withholding tax (WHT) of 10% and Value Added Tax (VAT) of 7.5% shall be deducted at source by Finance and Accounts.</li>
                <li>Failure to deliver within {{ $procurement->delivery_period_days }} calendar days without approved written extension constitutes breach of contract.</li>
            </ol>
        </div>

        <div class="signatures">
            <div class="sig-box">
                <br><br>
                <div class="sig-title">Head of Procurement / Tenders Board</div>
                <div style="font-size: 11px; color: #718096;">Verified & Certified under BPP 2007</div>
            </div>
            <div class="sig-box">
                <br><br>
                <div class="sig-title">Accounting Officer / Permanent Secretary</div>
                <div style="font-size: 11px; color: #718096;">Approved for Execution & Cross-Directorate Dispatch</div>
            </div>
        </div>
    </div>

</body>
</html>
