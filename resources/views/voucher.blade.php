<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Federal Government of Nigeria - Payment Voucher</title>
 <style>
    body {
  font-family: 'Times New Roman', serif;
  background-color: #f5f5f5;
  padding: 20px;
}

.voucher-container {
  max-width: 750px;
  margin: 0 auto;
  background-color: white;
  padding: 40px 60px;
}

.voucher-header {
  text-align: center;
  position: relative;
  margin-bottom: 20px;
  padding-bottom: 10px;
}

.voucher-header h1 {
  font-size: 18px;
  font-weight: 400;
}

.voucher-header h2 {
  font-size: 22px;
  font-weight: bold;
}

.original-label {
  position: absolute;
  top: 0;
  right: 0;
  font-size: 13px;
  text-align: right;
  line-height: 2;
}

.voucher-info {
  margin-bottom: 15px;
  text-align: center;
}

.dept-info {
  font-size: 14px;
}

.dotted-line {
  display: inline-block;
  width: 180px;
  border-bottom: 1.5px dotted #000;
  margin: 0 5px;
}

.dotted-line-transformed {
  display: inline;
  height: 50%;
  font-style: italic;
  border-left: 1.5px dotted #000;
  padding: 25px 0px 15px 0px;
  margin: 0 5px;
}

.dotted-line-long {
  display: inline-block;
  width: 500px;
  border-bottom: 1.5px dotted #000;
  margin: 0 5px;
}

.dotted-line-medium {
  display: inline-block;
  width: 100px;
  border-bottom: 1.5px dotted #000;
  margin: 0 5px;
}

.main-form {
  display: flex;
  margin-bottom: 20px;
}

.vertical-text-container {
  display: flex;
  flex-direction: row;
  justify-content: space-evenly;
  padding: 10px 0px;
  font-size: 13px;
  line-height: 1;
  margin-right: 12px;
}

.vertical-text {
  width: 15px;
  writing-mode: vertical-rl;
  text-orientation: mixed;
  transform: rotate(180deg);
}

.form-container {
  display: flex;
  gap: 20px;
  width: 84%;
}

.left-section {
  flex: 3;
}

table {
  border-collapse: collapse;
  width: 100%;
}

td, th {
  border: 2px solid #000;
  padding: 0px 7px;
  text-align: left;
  font-size: 12px;
  height: 25px;
  font-weight: normal;
}

.flex-border {
  font-size: 13px;
  height: 15px;
  border-left: 1px solid #000;
  display: flex;
  align-items: center;
  padding-top: 15px;
  justify-content: space-between;
}

.flex-noborder {
  font-size: 13px;
  display: flex;
  align-items: center;
  flex-wrap: nowrap;
  justify-content: space-between;
}

.source {
  width: 40%;
}

.classification {
  width: 100%;
}

.classification-container {
  border-right: 1px solid #000;
  border-left: 1px solid #000;
  padding: 7px 10px;
}

.number-cell {
  font-size: 10px;
  font-weight: 350;
}

.large-cell {
  height: 40px;
}

.tall-cell {
  height: 60px;
}

.station-table {
  height: 100px;
}

.station-table td {
  height: 50%;
  width: 4px;
}

.center-section {
  flex: 1;
  padding: 10px;
}

.right-section {
  width: 150px;
}

.voucher-codes {
  padding: 0;
}

.code-row {
  display: flex;
  margin-bottom: 10px;
  align-items: flex-end;
}

.data-type, .source, .voucher-num {
  margin-right: 20px;
}

.data-type label, .source label, .voucher-num label {
  font-size: 10px;
  display: block;
  margin-bottom: 5px;
}

.code-boxes {
  display: flex;
}

.code-boxes span {
  display: inline-block;
  width: 20px;
  height: 25px;
  border: 1px solid #000;
  text-align: center;
  line-height: 25px;
  margin-right: 2px;
  font-size: 14px;
  font-weight: bold;
}

.classification-row {
  margin-bottom: 10px;
}

.classification-row label {
  font-size: 10px;
  display: block;
  margin-bottom: 5px;
}

.classification-boxes {
  display: flex;
}

.classification-boxes span {
  display: inline-block;
  width: 20px;
  height: 25px;
  border: 1px solid #000;
  margin-right: 2px;
}

.date-amount-row {
  display: flex;
  margin-bottom: 10px;
}

.date-section {
  margin-right: 20px;
}

.date-section label, .amount-section label {
  font-size: 10px;
  display: block;
  margin-bottom: 5px;
}

.date-boxes, .amount-boxes {
  display: flex;
}

.date-boxes span, .amount-boxes span {
  display: inline-block;
  width: 20px;
  height: 25px;
  border: 1px solid #000;
  margin-right: 2px;
}

.bottom-classification {
  display: flex;
}

.source-bottom {
  margin-right: 20px;
}

.source-bottom label, .classification-bottom label {
  font-size: 10px;
  display: block;
  margin-bottom: 5px;
}

.station-section {
  height: 100%;
}

.station-header {
  border-bottom: 1px solid #000;
  padding: 10px;
  text-align: center;
  font-weight: bold;
  font-size: 12px;
}

.head-section {
  border-bottom: 1px solid #000;
  padding: 20px 10px;
  text-align: center;
  font-size: 12px;
}

.s-head-section {
  padding: 20px 10px;
  text-align: center;
  font-size: 12px;
}

.payee-section {
  margin-bottom: 15px;
}

.payee-line, .address-line {
  font-size: 12px;
  margin-bottom: 5px;
}

.details-table {
  margin-bottom: 20px;
}

.details-table table {
  width: 100%;
  border-collapse: collapse;
  border: 1px solid #000;
}

.details-table th, .details-table td {
  border: 1px solid #000;
  padding: 8px;
  text-align: left;
  font-size: 12px;
}

.details-table th {
  font-weight: 400;
  text-align: center;
}

.empty-row td {
  height: 160px;
}

.total-row td {
  font-weight: 400;
}

.bottom-section {
  display: flex;
}

.left-bottom {
  flex: 1;
  margin-right: 20px;
}

.right-bottom {
  flex: 1;
}

.payable-section {
  font-size: 12px;
  line-height: 1.6;
}

.payable-section div {
  margin-bottom: 5px;
}

.certificate-section {
  border: 1px solid #000;
  padding: 15px;
}

.certificate-section h3 {
  text-align: center;
  font-size: 14px;
  margin-bottom: 10px;
  font-weight: bold;
}

.certificate-section p {
  font-size: 11px;
  line-height: 1.4;
  margin-bottom: 10px;
  text-align: justify;
}

.signature-line {
  width: 100%;
  height: 1px;
  border-bottom: 1px solid #000;
  margin-top: 20px;
}

@media print {
  body {
      background-color: white;
      padding: 0;
  }
  
  .voucher-container {
      border: 2px solid #000;
      box-shadow: none;
  }
}
 </style>
  </head>
  <body>
    <div class="voucher-container">
      <header class="voucher-header">
        <h1>FEDERAL GOVERNMENT OF NIGERIA</h1>
        <h2>PAYMENT VOUCHER</h2>
        <div class="original-label">ORIGINAL<br />Treasury F1</div>
      </header>

      <div class="voucher-info">
        <div class="dept-info">
          Deptal No <span class="px-4">{{$voucher->deptal_number}}</span> Checked and passed for
          payment at <span class="px-2">{{$voucher->check_and_passed}}</span>
        </div>
      </div>

      <div class="main-form">
        <div class="vertical-text-container">
          <div class="vertical-text">For use in Payment of Advances</div>
          <div class="vertical-text">Certified the Advance of</div>
          <div class="vertical-text">
            No<span class="dotted-line-transformed">Sample Text</span>
          </div>
          <div class="vertical-text">
            Has been entered on TF 174 (A) (B) or (C)
          </div>
          <div class="vertical-text">
            Deptal No<span class="dotted-line-transformed">Sample Text</span>
          </div>
          <div class="vertical-text">
            Name in Block Letters<span class="dotted-line-transformed"
              >Sample Text</span
            >
          </div>
          <div class="vertical-text">
            Signatures<span class="dotted-line-transformed">Sample Text</span>
          </div>
        </div>
        <!-- Table like content goes here -->
        <div class="form-container">
          <div class="left-section">
            <!-- Top row with Data type, Source, and Voucher No -->

            <!-- Vo 1 row with REX 1 -->
            <table>
              <tr>
                <td><div class="flex-noborder"><span class="number-cell">1</span>Data type <span class="number-cell">3</span></div></td>
                <td colspan="3"><div class="flex-noborder"><span class="number-cell">4</span> Source <span class="number-cell">6</span></div></td>
                <td colspan="8"><div class="flex-noborder"><span class="number-cell">7</span> Voucher No <span class="number-cell">14</span></div></td>
              </tr>
              <tr>
                <td class="tall-cell">Vo 1</td>
                <td class="tall-cell"></td>
                <td class="tall-cell"></td>
                <td class="tall-cell"></td>
                <td class="tall-cell">R</td>
                <td class="tall-cell">E</td>
                <td class="tall-cell">X</td>
                <td class="tall-cell">1</td>
                <td class="tall-cell"></td>
                <td class="tall-cell"></td>
                <td class="tall-cell"></td>
                <td class="tall-cell"></td>
              </tr>
            </table>
            <!-- Classification Code section -->
            <div class="flex-border">
              <p class="number-cell">15</p>
              <p colspan="11" class="header-cell">Classification Code</p>
              <p class="number-cell">26</p>
            </div>
            <table>
              <tr>
                <td class="tall-cell"></td>
                <td class="tall-cell"></td>
                <td class="tall-cell"></td>
                <td class="tall-cell"></td>
                <td class="tall-cell"></td>
                <td class="tall-cell"></td>
                <td class="tall-cell"></td>
                <td class="tall-cell"></td>
                <td class="tall-cell"></td>
                <td class="tall-cell"></td>
                <td class="tall-cell"></td>
                <td class="tall-cell"></td>
                <td class="tall-cell"></td>
                <td class="tall-cell"></td>
                <td class="tall-cell"></td>
              </tr>
              <tr>
                <td colspan="7"><div class="flex-noborder"><span class="number-cell">27</span> Date <span class="number-cell">32</span></div></td>
                <td colspan="8"><div class="flex-noborder"><span class="number-cell">33</span>Amount<span class="number-cell">45</span></div></td>
              </tr>
              <tr>
                <td class="tall-cell"></td>
                <td class="tall-cell"></td>
                <td class="tall-cell"></td>
                <td class="tall-cell"></td>
                <td class="tall-cell"></td>
                <td class="tall-cell"></td>
                <td class="tall-cell"></td>
                <td colspan="8" class="tall-cell"></td>
              </tr>
            </table>

            <!-- Bottom Classification Code section -->
            {{-- <div class="classification-container">
              <td colspan="15"><div class="flex-noborder">
                <div class="flex-noborder source"><span class="number-cell">46</span>source<span class="number-cell">48</span></div>
                <div class="flex-noborder classification"><span class="number-cell">49</span>Classification Code<span class="number-cell">60</span></div>
              </div></td>
            </div> --}}
            <div class="classification-container">
            <table>
                <tr>
                <td colspan="15">
                    <div class="flex-noborder">
                    <div class="flex-noborder source">
                        <span class="number-cell">46</span>source<span class="number-cell">48</span>
                    </div>
                    <div class="flex-noborder classification">
                        <span class="number-cell">49</span>Classification Code<span class="number-cell">60</span>
                    </div>
                    </div>
                </td>
                </tr>
            </table>
            </div>

            <table>
              <tr>
                <td class="tall-cell"></td>
                <td class="tall-cell"></td>
                <td class="tall-cell"></td>
                <td class="tall-cell"></td>
                <td class="tall-cell"></td>
                <td class="tall-cell"></td>
                <td class="tall-cell"></td>
                <td class="tall-cell"></td>
                <td class="tall-cell"></td>
                <td class="tall-cell"></td>
                <td class="tall-cell"></td>
                <td class="tall-cell"></td>
                <td class="tall-cell"></td>
                <td class="tall-cell"></td>
                <td class="tall-cell"></td>
              </tr>
            </table>
          </div>

          <div class="right-section">
            <!-- Station section -->
            <table class="station-table">
              <tr>
                <td colspan="3" class="">Station</td>
              </tr>
              <tr>
                <td class="">Head</td>
                <td colspan="5"></td>
              </tr>
              <tr>
                <td class="">S/Head</td>
                <td colspan="5"></td>
              </tr>
            </table>
          </div>
        </div>
      </div>

      <div class="payee-section">
        <div class="payee-line">
          Payee <span class="dotted-line-long"></span>
        </div>
        <div class="address-line">
          Address <span class="dotted-line-long"></span>
        </div>
      </div>

      <div class="details-table">
        <table>
          <thead>
            <tr>
              <th>Date</th>
              <th>Detailed Description of Service/Work</th>
              <th>Rate</th>
              <th>₦</th>
              <th>K</th>
            </tr>
          </thead>
          <tbody>
            <tr class="empty-row">
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
            </tr>
            <tr class="total-row">
              <td></td>
              <td><span>Checked and<br />passed for</span>Insert Amounts in words</td>
              <td>Total ₦</td>
              <td></td>
              <td></td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="bottom-section">
        <div class="left-bottom">
          <div class="payable-section">
            <div>Payable at <span class="dotted-line-medium"></span></div>
            <div>Signature <span class="dotted-line-medium"></span></div>
            <div>Name in</div>
            <div>Block Letters <span class="dotted-line-medium"></span></div>
            <div>
              Station <span class="dotted-line-medium"></span> Date
              <span class="dotted-line-medium"></span>
            </div>
            <div>Paying Officer's</div>
            <div>
              <em>Signature</em> <span class="dotted-line-medium"></span>
            </div>
            <div>Name in</div>
            <div>Block Letters <span class="dotted-line-medium"></span></div>
          </div>
        </div>

        <div class="right-bottom">
          <div class="certificate-section">
            <h3>CERTIFICATE</h3>
            <p>
              I Certify that the above amount is correct and was incurred under
              the authority quoted; that the service have been duly performed;
              that the rate/price charged is according to regulation / contract
              is fair and reasonable; that the amount of
              <span class="dotted-line-medium"></span> Kobo may be paid under
            </p>
            <p>the classification quoted</p>
            <div class="signature-line"></div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
