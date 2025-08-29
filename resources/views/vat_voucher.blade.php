<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nigerian VAT Invoice Template</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            padding: 20px;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .invoice-header {
            background: linear-gradient(135deg, #008751, #00b35c);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }
        /*
        .invoice-header::after {
            content: "";
            position: absolute;
            top: 10px;
            right: 20px;
            width: 40px;
            height: 25px;
            background: linear-gradient(to bottom, #008751 33%, white 33%, white 66%, #008751 66%);
            border-radius: 3px;
        }
        */
        .invoice-header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            font-weight: 300;
        }
        
        .invoice-header p {
            font-size: 1.1em;
            opacity: 0.9;
        }
        
        .invoice-content {
            padding: 30px;
        }
        
        .company-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .company-info, .client-info {
            flex: 1;
            min-width: 250px;
        }
        
        .company-info {
            margin-right: 20px;
        }
        
        .section-title {
            font-size: 1.2em;
            color: #008751;
            margin-bottom: 15px;
            border-bottom: 2px solid #00b35c;
            padding-bottom: 5px;
            font-weight: 600;
        }
        
        .info-group {
            margin-bottom: 20px;
        }
        
        .info-item {
            margin-bottom: 8px;
            display: flex;
            align-items: flex-start;
        }
        
        .info-label {
            font-weight: 600;
            min-width: 120px;
            color: #008751;
        }
        
        .info-value {
            flex: 1;
            border-bottom: 1px dotted #ccc;
            padding-bottom: 2px;
            min-height: 20px;
        }
        
        .invoice-meta {
            display: flex;
            justify-content: space-between;
            margin: 30px 0;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #008751;
            flex-wrap: wrap;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .meta-label {
            font-weight: 600;
            margin-right: 15px;
            color: #008751;
            min-width: 100px;
        }
        
        .meta-value {
            border-bottom: 1px solid #00b35c;
            padding-bottom: 3px;
            min-width: 150px;
            font-weight: 500;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .items-table th {
            background: linear-gradient(135deg, #008751, #00b35c);
            color: white;
            padding: 15px 10px;
            text-align: left;
            font-weight: 600;
            font-size: 0.95em;
        }
        
        .items-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #eee;
        }
        
        .items-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .items-table tr:hover {
            background-color: #e8f5e8;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .totals-section {
            background-color: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            margin-top: 20px;
            border-left: 4px solid #008751;
        }
        
        .totals-table {
            width: 100%;
        }
        
        .totals-table td {
            padding: 8px 15px;
            border-bottom: 1px solid #ddd;
        }
        
        .totals-table .total-label {
            font-weight: 600;
            color: #008751;
        }
        
        .totals-table .total-value {
            text-align: right;
            font-weight: 600;
            min-width: 100px;
            border-bottom: 1px dotted #999;
        }
        
        .grand-total {
            background: linear-gradient(135deg, #008751, #00b35c);
            color: white;
            font-size: 1.2em;
        }
        
        .grand-total td {
            border: none;
            padding: 15px;
        }
        
        .currency {
            font-weight: 600;
            color: #008751;
        }
        
        .footer-section {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #00b35c;
        }
        
        .payment-terms, .notes {
            margin-bottom: 20px;
        }
        
        .terms-title, .notes-title {
            font-weight: 600;
            color: #008751;
            margin-bottom: 10px;
        }
        
        .terms-content, .notes-content {
            border: 1px solid #ddd;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
            min-height: 60px;
        }
        
        .vat-notice {
            background-color: #e8f5e8;
            border: 1px solid #00b35c;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            font-size: 0.95em;
        }
        
        .vat-notice strong {
            color: #008751;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .invoice-container {
                box-shadow: none;
                border-radius: 0;
            }
        }
        
        @media (max-width: 768px) {
            .company-details {
                flex-direction: column;
            }
            
            .company-info {
                margin-right: 0;
                margin-bottom: 20px;
            }
            
            .invoice-meta {
                flex-direction: column;
            }
            
            .items-table {
                font-size: 0.9em;
            }
            
            .items-table th,
            .items-table td {
                padding: 8px 5px;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-header">
            <h1>VAT INVOICE</h1>
            <p>Value Added Tax Invoice - Nigeria</p>
        </div>
        
        <div class="invoice-content">
            <div class="company-details">
                <div class="company-info">
                    <div class="section-title">7.5% VAT</div>
                    <div class="info-group">
                        <div class="info-item">
                            <span class="info-label">Payment:</span>
                            <span class="info-value"></span>
                        </div>

                    </div>
                </div>
                
              
            </div>
            
            <div class="invoice-meta">
                <div>
                    <div class="meta-item">
                        <span class="meta-label">Voucher No:</span>
                        <span class="meta-value"></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Voucher Date:</span>
                        <span class="meta-value"></span>
                    </div>
                </div>
                <!--
                <div>
                    <div class="meta-item">
                        <span class="meta-label">Due Date:</span>
                        <span class="meta-value"></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Currency:</span>
                        <span class="meta-value">₦ (Nigerian Naira)</span>
                    </div>
                </div>
            -->
            </div>
            
            <div class="totals-section">
                <table class="totals-table">
                    <tr>
                        <td class="total-label">Sub Total:</td>
                        <td class="total-value"><span class="currency">₦</span> _________</td>
                    </tr>
                    <tr>
                        <td class="total-label">VAT (7.5%):</td>
                        <td class="total-value"><span class="currency">₦</span> _________</td>
                    </tr>
                    <tr class="grand-total">
                        <td>TOTAL AMOUNT:</td>
                        <td class="text-right"><span class="currency">₦</span> _________</td>
                    </tr>
                </table>
            </div>
            
        </div>
    </div>
</body>
</html>