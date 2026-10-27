<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DocumentTemplate;
use App\Models\User;

class StandardDocumentTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $superAdmin = User::whereHas('roles', function($q) {
            $q->where('name', 'super admin');
        })->first();
        $adminId = $superAdmin ? $superAdmin->id : 1;

        $templates = [
            [
                'title' => 'External Correspondence Acknowledgment Letter',
                'type' => 'acknowledgement',
                'content_html' => '<div style="font-family: Arial, sans-serif; line-height: 1.6; color: #1a1a1a; max-width: 800px; margin: 0 auto; padding: 20px;">
<div style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 12px; margin-bottom: 20px;">
  <h2 style="margin: 0; text-transform: uppercase; letter-spacing: 1px; color: #0d47a1;">Federal Republic of Nigeria</h2>
  <h3 style="margin: 5px 0; text-transform: uppercase;">Office of the Accountant General of the Federation</h3>
  <p style="margin: 0; font-size: 12px; color: #555;">Treasury House, Samuel Ladoke Akintola Boulevard, Garki II, Abuja</p>
</div>

<div style="display: flex; justify-content: space-between; margin-bottom: 25px; font-size: 13px;">
  <div>
    <strong>Our Ref:</strong> {{ reference_no }}<br>
    <strong>Docket No:</strong> {{ tracking_id }}
  </div>
  <div style="text-align: right;">
    <strong>Date:</strong> {{ date }}
  </div>
</div>

<p style="margin-bottom: 20px;">
  <strong>To:</strong><br>
  {{ sender_name }}<br>
  <strong>Your Ref:</strong> {{ external_ref }}
</p>

<p style="text-align: center; font-weight: bold; text-transform: uppercase; font-size: 15px; margin: 25px 0; border-bottom: 1px solid #ccc; padding-bottom: 5px;">
  ACKNOWLEDGMENT OF OFFICIAL CORRESPONDENCE: {{ title }}
</p>

<p>I am directed to formally acknowledge receipt of your correspondence referenced above in respect of <strong>"{{ title }}"</strong>, which was received and duly recorded in the Central Registry of this Office.</p>

<p>Please be informed that the document has been forwarded to the Executive Desk of the <strong>Accountant General of the Federation</strong> for executive consideration and necessary administrative directives.</p>

<p>Further communication on the outcome of your submission will be conveyed to you in due course. You may reference the official Tracking ID <strong>#{{ tracking_id }}</strong> in any subsequent inquiries regarding this matter.</p>

<div style="margin-top: 50px;">
  <p style="margin-bottom: 5px;">Yours faithfully,</p>
  <div style="margin-top: 30px; border-bottom: 1px solid #888; width: 220px;"></div>
  <p style="margin-top: 5px; font-weight: bold;">{{ officer_name }}</p>
  <p style="margin: 0; font-size: 12px; color: #555;">For: Accountant General of the Federation</p>
  <p style="margin: 0; font-size: 12px; color: #555;">{{ department }}</p>
</div>
</div>',
                'version' => 1,
                'is_active' => true,
                'department_id' => null,
                'created_by' => $adminId,
            ],

            [
                'title' => 'Directorate Executive Recommendation Memo',
                'type' => 'memo',
                'content_html' => '<div style="font-family: Arial, sans-serif; line-height: 1.6; color: #222; max-width: 800px; margin: 0 auto; padding: 20px;">
<div style="text-align: center; border-bottom: 3px double #333; padding-bottom: 10px; margin-bottom: 20px;">
  <h2 style="margin: 0; text-transform: uppercase; color: #1b5e20;">Internal Memorandum</h2>
  <h4 style="margin: 4px 0; text-transform: uppercase; color: #555;">Office of the Accountant General of the Federation</h4>
</div>

<table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 13px;">
  <tr>
    <td style="padding: 6px 0; width: 15%; font-weight: bold;">TO:</td>
    <td style="padding: 6px 0; width: 45%;">The Accountant General of the Federation</td>
    <td style="padding: 6px 0; width: 15%; font-weight: bold;">FROM:</td>
    <td style="padding: 6px 0; width: 25%;">{{ officer_name }} ({{ officer_role }})</td>
  </tr>
  <tr>
    <td style="padding: 6px 0; font-weight: bold;">THROUGH:</td>
    <td style="padding: 6px 0;">Director, {{ department }}</td>
    <td style="padding: 6px 0; font-weight: bold;">DATE:</td>
    <td style="padding: 6px 0;">{{ date }}</td>
  </tr>
  <tr>
    <td style="padding: 6px 0; font-weight: bold;">DOCKET REF:</td>
    <td style="padding: 6px 0;">#{{ tracking_id }}</td>
    <td style="padding: 6px 0; font-weight: bold;">FILE REF:</td>
    <td style="padding: 6px 0;">{{ external_ref }}</td>
  </tr>
</table>

<div style="background-color: #fff9c4; border-left: 4px solid #fbc02d; padding: 10px 14px; margin-bottom: 20px; font-size: 13px;">
  <strong>AGF EXECUTIVE DIRECTIVE:</strong><br>
  <em>"{{ agf_directive }}"</em>
</div>

<p style="text-align: center; font-weight: bold; text-transform: uppercase; font-size: 14px; margin: 20px 0; text-decoration: underline;">
  SUBJECT: {{ title }}
</p>

<p><strong>1. PURPOSE:</strong><br>
The purpose of this memorandum is to brief the Accountant General of the Federation on the subject matter referenced above and present the Directorate\'s findings and technical recommendations for executive approval.</p>

<p><strong>2. BACKGROUND & ANALYSIS:</strong><br>
In compliance with the AGF\'s directive referenced above, the technical desk of the {{ department }} has carried out a comprehensive verification of the documentation, regulatory compliance, and fiscal provisions relating to this submission.</p>

<p><strong>3. TECHNICAL FINDINGS:</strong><br>
i. The relevant statutory requirements, procurement clearance, and financial regulations have been evaluated.<br>
ii. The applicable votes and financial commitments stand fully compliant with extant treasury guidelines.<br>
iii. The requested fiscal disbursement / administrative action of <strong>{{ amount }}</strong> is verified as legitimate and supported by satisfactory documentation.</p>

<p><strong>4. RECOMMENDATION:</strong><br>
In light of the foregoing findings, the AGF is respectfully invited to:</p>
<ol type="a" style="margin-left: 20px;">
  <li><strong>NOTE</strong> the technical verification completed by the Directorate;</li>
  <li><strong>APPROVE</strong> the recommendation as submitted; and</li>
  <li><strong>AUTHORIZE</strong> the Central Registry / Treasury Funds Directorate to release the formal decision instrument.</li>
</ol>

<div style="margin-top: 40px; display: flex; justify-content: space-between;">
  <div>
    <div style="border-bottom: 1px solid #777; width: 200px; margin-bottom: 4px;"></div>
    <strong>{{ officer_name }}</strong><br>
    <span style="font-size: 12px; color: #555;">Desk Officer, {{ department }}</span>
  </div>
  <div style="text-align: right;">
    <div style="border-bottom: 1px solid #777; width: 200px; margin-bottom: 4px; margin-left: auto;"></div>
    <strong>Director, {{ department }}</strong><br>
    <span style="font-size: 12px; color: #555;">Endorsed for AGF Approval</span>
  </div>
</div>
</div>',
                'version' => 1,
                'is_active' => true,
                'department_id' => null,
                'created_by' => $adminId,
            ],

            [
                'title' => 'Official AGF Executive Approval & Mandate Instrument',
                'type' => 'letter',
                'content_html' => '<div style="font-family: Times New Roman, serif; line-height: 1.6; color: #111; max-width: 800px; margin: 0 auto; padding: 25px; border: 1px solid #ddd; background: #fff;">
<div style="text-align: center; border-bottom: 3px solid #000; padding-bottom: 15px; margin-bottom: 25px;">
  <div style="font-size: 24px; font-weight: bold; letter-spacing: 2px; text-transform: uppercase; color: #0d47a1;">Office of the Accountant General of the Federation</div>
  <div style="font-size: 14px; font-weight: bold; text-transform: uppercase; color: #333; margin-top: 4px;">Federal Ministry of Finance, Budget & National Planning</div>
  <div style="font-size: 12px; color: #666; margin-top: 2px;">Treasury House, Garki II, Abuja, Nigeria</div>
</div>

<div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 20px;">
  <div>
    <strong>Official Ref:</strong> {{ reference_no }}<br>
    <strong>Docket ID:</strong> #{{ tracking_id }}
  </div>
  <div style="text-align: right;">
    <strong>Date of Release:</strong> {{ date }}
  </div>
</div>

<p style="margin-bottom: 25px; font-size: 14px;">
  <strong>To:</strong><br>
  {{ sender_name }}<br>
  <strong>Reference:</strong> {{ external_ref }}
</p>

<p style="text-align: center; font-weight: bold; text-transform: uppercase; font-size: 16px; margin: 25px 0; border-bottom: 2px solid #222; padding-bottom: 6px;">
  EXECUTIVE APPROVAL: {{ title }}
</p>

<p>I have the honour to refer to your submission in respect of the above subject matter and subsequent technical evaluation conducted by the relevant operational Directorates of this Office.</p>

<p>Pursuant to the powers vested in the <strong>Accountant General of the Federation</strong> under the Financial Regulations and Public Finance Management Acts, I hereby convey <strong>FORMAL EXECUTIVE APPROVAL</strong> for the requested administrative and fiscal action regarding <strong>"{{ title }}"</strong>.</p>

<p>The relevant Treasury Directorates and Central Registry are by this instrument mandated to proceed with immediate operational execution and outward dispatch of all clearance documents.</p>

<div style="margin-top: 60px; text-align: right; margin-right: 20px;">
  <div style="display: inline-block; text-align: center;">
    <div style="font-family: cursive; font-size: 22px; color: #0d47a1; margin-bottom: 2px;">Accountant General</div>
    <div style="border-bottom: 2px solid #000; width: 260px; margin: 0 auto 5px auto;"></div>
    <strong style="font-size: 14px; text-transform: uppercase;">Accountant General of the Federation</strong><br>
    <span style="font-size: 12px; color: #444;">Federal Republic of Nigeria</span><br>
    <span style="font-size: 10px; color: #00796b; font-weight: bold; text-transform: uppercase; border: 1px dashed #00796b; padding: 2px 6px; border-radius: 4px; display: inline-block; margin-top: 5px;">
      Digitally Authenticated via EDMS PIN Seal
    </span>
  </div>
</div>
</div>',
                'version' => 1,
                'is_active' => true,
                'department_id' => null,
                'created_by' => $adminId,
            ],

            [
                'title' => 'Central Registry Dispatch Transmittal Slip',
                'type' => 'dispatch',
                'content_html' => '<div style="font-family: Arial, sans-serif; line-height: 1.5; color: #333; max-width: 800px; margin: 0 auto; padding: 20px; border: 1px dashed #aaa;">
<div style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 15px;">
  <h3 style="margin: 0; text-transform: uppercase;">Central Registry Dispatch Transmittal</h3>
  <p style="margin: 2px 0 0 0; font-size: 12px; color: #666;">Office of the Accountant General of the Federation • Outward Mail Section</p>
</div>

<table style="width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 13px;">
  <tr>
    <td style="padding: 6px; border: 1px solid #ccc; font-weight: bold; width: 25%; background: #f9f9f9;">Dispatch Folio No:</td>
    <td style="padding: 6px; border: 1px solid #ccc; width: 25%;">{{ reference_no }}</td>
    <td style="padding: 6px; border: 1px solid #ccc; font-weight: bold; width: 25%; background: #f9f9f9;">Dispatch Date:</td>
    <td style="padding: 6px; border: 1px solid #ccc; width: 25%;">{{ date }}</td>
  </tr>
  <tr>
    <td style="padding: 6px; border: 1px solid #ccc; font-weight: bold; background: #f9f9f9;">EDMS Docket ID:</td>
    <td style="padding: 6px; border: 1px solid #ccc;">#{{ tracking_id }}</td>
    <td style="padding: 6px; border: 1px solid #ccc; font-weight: bold; background: #f9f9f9;">Originating Dept:</td>
    <td style="padding: 6px; border: 1px solid #ccc;">{{ department }}</td>
  </tr>
  <tr>
    <td style="padding: 6px; border: 1px solid #ccc; font-weight: bold; background: #f9f9f9;">Recipient Entity:</td>
    <td colspan="3" style="padding: 6px; border: 1px solid #ccc;">{{ sender_name }}</td>
  </tr>
  <tr>
    <td style="padding: 6px; border: 1px solid #ccc; font-weight: bold; background: #f9f9f9;">Subject:</td>
    <td colspan="3" style="padding: 6px; border: 1px solid #ccc;">{{ title }}</td>
  </tr>
</table>

<div style="margin: 20px 0; padding: 10px; background: #e8f5e9; border: 1px solid #c8e6c9; border-radius: 6px; font-size: 13px;">
  <strong>DISPATCH VERIFICATION:</strong><br>
  This file has received final digital authorization from the <strong>Accountant General of the Federation</strong> and is cleared for outward release and official filing.
</div>

<div style="display: flex; justify-content: space-between; margin-top: 35px; font-size: 12px;">
  <div>
    <p><strong>Dispatched By (Registry Clerk):</strong></p>
    <div style="border-bottom: 1px solid #555; width: 200px; margin-top: 25px;"></div>
    <p style="margin-top: 3px;">{{ officer_name }}</p>
  </div>
  <div>
    <p><strong>Courier / Recipient Acknowledgment:</strong></p>
    <div style="border-bottom: 1px solid #555; width: 200px; margin-top: 25px;"></div>
    <p style="margin-top: 3px;">Signature / Stamp / Date</p>
  </div>
</div>
</div>',
                'version' => 1,
                'is_active' => true,
                'department_id' => null,
                'created_by' => $adminId,
            ],

            [
                'title' => 'Formal Civil Service Disciplinary Query',
                'type' => 'letter',
                'content_html' => '<div style="font-family: Arial, sans-serif; line-height: 1.6; color: #222; max-width: 800px; margin: 0 auto; padding: 25px; border: 2px solid #b71c1c;">
<div style="text-align: center; border-bottom: 2px solid #b71c1c; padding-bottom: 10px; margin-bottom: 20px;">
  <h2 style="margin: 0; text-transform: uppercase; color: #b71c1c;">Office of the Accountant General of the Federation</h2>
  <h4 style="margin: 4px 0; text-transform: uppercase; color: #333;">Disciplinary & Compliance Section</h4>
  <p style="margin: 0; font-size: 12px; color: #666;">Strictly Confidential</p>
</div>

<div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 20px;">
  <div>
    <strong>Query Ref:</strong> OAGF/QUERY/{{ year }}/#{{ tracking_id }}
  </div>
  <div style="text-align: right;">
    <strong>Date of Issuance:</strong> {{ date }}
  </div>
</div>

<p style="margin-bottom: 20px;">
  <strong>To:</strong> {{ officer_name }}<br>
  <strong>Rank/Designation:</strong> {{ officer_role }}<br>
  <strong>Department:</strong> {{ department }}
</p>

<p style="text-align: center; font-weight: bold; text-transform: uppercase; font-size: 15px; margin: 20px 0; color: #b71c1c; border-bottom: 1px solid #ccc; padding-bottom: 5px;">
  QUERY: OFFICIAL MISCONDUCT AND BREACH OF CIVIL SERVICE REGULATIONS
</p>

<p>It has been brought to the attention of executive management that you have engaged in conduct contrary to the provisions of the Public Service Rules (PSR) regarding <strong>"{{ title }}"</strong>.</p>

<p><strong>DETAILS OF INFRACTION:</strong><br>
{{ description }}</p>

<p>Your action amounts to serious misconduct, insubordination, or dereliction of official duty as defined under the extant Public Service Rules.</p>

<p>Consequently, you are hereby required to submit a comprehensive written explanation within <strong>72 HOURS</strong> of receipt of this query (on or before <strong>{{ deadline }}</strong>), showing cause why disciplinary proceedings and appropriate statutory sanctions should not be instituted against you.</p>

<p>Failure to respond within the stipulated timeline will be deemed an admission of the allegation, and management will proceed with appropriate sanctions without further reference to you.</p>

<div style="margin-top: 50px;">
  <p style="margin-bottom: 5px;">Issued by authority of:</p>
  <div style="margin-top: 30px; border-bottom: 1px solid #333; width: 220px;"></div>
  <p style="margin-top: 5px; font-weight: bold;">Accountant General of the Federation / Designated Superior Officer</p>
  <p style="margin: 0; font-size: 12px; color: #555;">{{ department }}</p>
</div>
</div>',
                'version' => 1,
                'is_active' => true,
                'department_id' => null,
                'created_by' => $adminId,
            ],
            [
                'title' => 'Funds Allocation & Sub-Warrant Release Memo',
                'type' => 'memo',
                'content_html' => '<div style="font-family: Arial, sans-serif; line-height: 1.6; color: #222; max-width: 800px; margin: 0 auto; padding: 20px;">
<div style="text-align: center; border-bottom: 3px double #333; padding-bottom: 10px; margin-bottom: 20px;">
  <h2 style="margin: 0; text-transform: uppercase; color: #1565c0;">Department of Funds</h2>
  <h4 style="margin: 4px 0; text-transform: uppercase; color: #555;">Office of the Accountant General of the Federation</h4>
  <p style="margin: 0; font-size: 11px; color: #777;">Treasury House, Abuja | Sub-Warrant & Allocation Desk</p>
</div>

<table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 13px;">
  <tr>
    <td style="padding: 6px 0; width: 15%; font-weight: bold;">TO:</td>
    <td style="padding: 6px 0; width: 45%;">The Accountant General of the Federation</td>
    <td style="padding: 6px 0; width: 15%; font-weight: bold;">FROM:</td>
    <td style="padding: 6px 0; width: 25%;">Director, Funds Department</td>
  </tr>
  <tr>
    <td style="padding: 6px 0; font-weight: bold;">DATE:</td>
    <td style="padding: 6px 0;">{{ date }}</td>
    <td style="padding: 6px 0; font-weight: bold;">DOCKET REF:</td>
    <td style="padding: 6px 0;">#{{ tracking_id }}</td>
  </tr>
</table>

<p style="text-align: center; font-weight: bold; text-transform: uppercase; font-size: 14px; margin: 20px 0; text-decoration: underline;">
  RECOMMENDATION FOR SUB-WARRANT RELEASE: {{ title }}
</p>

<p>I have the honour to refer to the executive directive of the Accountant General of the Federation in respect of <strong>"{{ title }}"</strong>.</p>

<p><strong>1. FISCAL APPRAISAL:</strong><br>
The Funds Directorate has reviewed the budget line and confirms that adequate statutory allocation exists under the relevant capital/recurrent vote head to accommodate the release of <strong>{{ amount }}</strong>.</p>

<p><strong>2. RECOMMENDATION:</strong><br>
It is respectfully recommended that the Accountant General approve the issuance of the statutory Sub-Warrant in favour of the beneficiary MDA to enable treasury disbursement.</p>

<div style="margin-top: 40px;">
  <div style="border-bottom: 1px solid #777; width: 200px; margin-bottom: 4px;"></div>
  <strong>Director, Funds Department</strong><br>
  <span style="font-size: 12px; color: #555;">Funds Directorate, OAGF</span>
</div>
</div>',
                'version' => 1,
                'is_active' => true,
                'department_id' => 1,
                'created_by' => $adminId,
            ],
            [
                'title' => 'Finance & Accounts Internal Audit Clearance Memo',
                'type' => 'memo',
                'content_html' => '<div style="font-family: Arial, sans-serif; line-height: 1.6; color: #222; max-width: 800px; margin: 0 auto; padding: 20px;">
<div style="text-align: center; border-bottom: 3px double #333; padding-bottom: 10px; margin-bottom: 20px;">
  <h2 style="margin: 0; text-transform: uppercase; color: #2e7d32;">Department of Finance and Accounts</h2>
  <h4 style="margin: 4px 0; text-transform: uppercase; color: #555;">Office of the Accountant General of the Federation</h4>
  <p style="margin: 0; font-size: 11px; color: #777;">Financial Controls & Internal Verification Division</p>
</div>

<table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 13px;">
  <tr>
    <td style="padding: 6px 0; width: 15%; font-weight: bold;">TO:</td>
    <td style="padding: 6px 0; width: 45%;">The Accountant General of the Federation</td>
    <td style="padding: 6px 0; width: 15%; font-weight: bold;">FROM:</td>
    <td style="padding: 6px 0; width: 25%;">Director, Finance and Accounts</td>
  </tr>
  <tr>
    <td style="padding: 6px 0; font-weight: bold;">DATE:</td>
    <td style="padding: 6px 0;">{{ date }}</td>
    <td style="padding: 6px 0; font-weight: bold;">DOCKET REF:</td>
    <td style="padding: 6px 0;">#{{ tracking_id }}</td>
  </tr>
</table>

<p style="text-align: center; font-weight: bold; text-transform: uppercase; font-size: 14px; margin: 20px 0; text-decoration: underline;">
  FINANCIAL AUDIT & VOUCHER CLEARANCE: {{ title }}
</p>

<p>In accordance with the financial regulations and executive directive issued on <strong>"{{ title }}"</strong>, the Internal Audit and Financial Control team has performed rigorous pre-payment verification.</p>

<p><strong>1. AUDIT FINDINGS:</strong><br>
All supporting invoices, tax deduction certificates (VAT/WHT), and procurement approvals have been checked and verified as authentic and regular.</p>

<p><strong>2. CLEARANCE OPINION:</strong><br>
The Directorate of Finance & Accounts hereby confirms that payment vouchers may be credited without audit reservation.</p>

<div style="margin-top: 40px;">
  <div style="border-bottom: 1px solid #777; width: 200px; margin-bottom: 4px;"></div>
  <strong>Director, Finance and Accounts</strong><br>
  <span style="font-size: 12px; color: #555;">Finance and Accounts Directorate, OAGF</span>
</div>
</div>',
                'version' => 1,
                'is_active' => true,
                'department_id' => 3,
                'created_by' => $adminId,
            ]
        ];

        foreach ($templates as $tmpl) {
            DocumentTemplate::updateOrCreate(
                ['title' => $tmpl['title']],
                $tmpl
            );
        }

        $this->command->info('Standard Document Templates seeded successfully.');
    }
}
