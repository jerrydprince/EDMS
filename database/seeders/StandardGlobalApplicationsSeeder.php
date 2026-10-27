<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Workflow;
use App\Models\WorkflowVersion;
use App\Models\WorkflowStep;
use App\Models\WorkflowTransition;
use App\Models\DocumentTemplate;

class StandardGlobalApplicationsSeeder extends Seeder
{
    public function run()
    {
        $applications = [
            [
                'title' => 'Casual Leave Application',
                'emoji' => '🏖️',
                'template' => [
                    'title' => 'Official Casual Leave Requisition Form (PSR SEC. 100201)',
                    'type' => 'memo',
                    'html' => '<div style="font-family: Arial, sans-serif; padding: 24px; color: #1e293b; line-height: 1.6;">
  <div style="text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 20px;">
    <h2 style="margin: 0; text-transform: uppercase; font-size: 18px; letter-spacing: 1px;">Office of the Accountant-General of the Federation</h2>
    <h3 style="margin: 4px 0 0 0; font-size: 14px; color: #475569; text-transform: uppercase;">Federal Treasury House, Abuja</h3>
    <h4 style="margin: 8px 0 0 0; font-size: 14px; background: #f1f5f9; display: inline-block; padding: 4px 16px; border-radius: 4px; border: 1px solid #cbd5e1;">OFFICIAL CASUAL LEAVE REQUISITION DOCKET (PSR SEC. 100201)</h4>
  </div>

  <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 13px;">
    <tr>
      <td style="padding: 8px; border: 1px solid #cbd5e1; background: #f8fafc; font-weight: bold; width: 25%;">Applicant Officer:</td>
      <td style="padding: 8px; border: 1px solid #cbd5e1; width: 25%;">{{ applicant_name }}</td>
      <td style="padding: 8px; border: 1px solid #cbd5e1; background: #f8fafc; font-weight: bold; width: 25%;">Staff File / Service No:</td>
      <td style="padding: 8px; border: 1px solid #cbd5e1; width: 25%;">{{ file_number }}</td>
    </tr>
    <tr>
      <td style="padding: 8px; border: 1px solid #cbd5e1; background: #f8fafc; font-weight: bold;">Department / Directorate:</td>
      <td style="padding: 8px; border: 1px solid #cbd5e1;">{{ department_name }}</td>
      <td style="padding: 8px; border: 1px solid #cbd5e1; background: #f8fafc; font-weight: bold;">Designation & Grade Level:</td>
      <td style="padding: 8px; border: 1px solid #cbd5e1;">{{ designation }} (GL {{ grade_level }})</td>
    </tr>
    <tr>
      <td style="padding: 8px; border: 1px solid #cbd5e1; background: #f8fafc; font-weight: bold;">Duration of Leave:</td>
      <td style="padding: 8px; border: 1px solid #cbd5e1;">{{ number_of_days }} Working Days</td>
      <td style="padding: 8px; border: 1px solid #cbd5e1; background: #f8fafc; font-weight: bold;">Leave Period:</td>
      <td style="padding: 8px; border: 1px solid #cbd5e1;">From {{ start_date }} to {{ end_date }}</td>
    </tr>
    <tr>
      <td style="padding: 8px; border: 1px solid #cbd5e1; background: #f8fafc; font-weight: bold;">Relieving Officer:</td>
      <td colspan="3" style="padding: 8px; border: 1px solid #cbd5e1;">{{ relieving_officer }}</td>
    </tr>
  </table>

  <div style="margin-bottom: 20px;">
    <h5 style="margin: 0 0 6px 0; font-size: 12px; text-transform: uppercase; color: #475569;">Grounds for Casual Leave:</h5>
    <div style="border: 1px solid #cbd5e1; padding: 12px; background: #ffffff; border-radius: 4px; min-height: 70px; font-size: 13px;">
      {{ grounds }}
    </div>
  </div>

  <div style="margin-top: 25px; border-top: 1px solid #e2e8f0; padding-top: 15px; font-size: 12px; color: #64748b;">
    <p style="margin: 0;"><strong>Statutory Routing:</strong> Immediate Supervisor &rarr; Departmental Director &rarr; Director Administration</p>
    <p style="margin: 4px 0 0 0; color: #059669; font-weight: bold;">✓ Certified by Initiating Officer with Digital Signature PIN</p>
  </div>
</div>'
                ],
                'steps' => [
                    ['name' => 'Officer Application Initiation', 'role' => null, 'type' => 'input', 'is_start' => true, 'is_end' => false],
                    ['name' => 'Immediate Supervisor Review', 'role' => 'supervisor', 'type' => 'review', 'is_start' => false, 'is_end' => false],
                    ['name' => 'Departmental Director Recommendation', 'role' => 'director', 'type' => 'approval', 'is_start' => false, 'is_end' => false],
                    ['name' => 'Director Administration Clearance & Approval', 'role' => 'director admin', 'type' => 'approval', 'is_start' => false, 'is_end' => true]
                ]
            ],
            [
                'title' => 'Annual Leave Application',
                'emoji' => '🌴',
                'template' => [
                    'title' => 'Statutory Annual Leave Application Form (Civil Service Roster)',
                    'type' => 'memo',
                    'html' => '<div style="font-family: Arial, sans-serif; padding: 24px; color: #1e293b; line-height: 1.6;">
  <div style="text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 20px;">
    <h2 style="margin: 0; text-transform: uppercase; font-size: 18px; letter-spacing: 1px;">Office of the Accountant-General of the Federation</h2>
    <h3 style="margin: 4px 0 0 0; font-size: 14px; color: #475569; text-transform: uppercase;">Federal Treasury House, Abuja</h3>
    <h4 style="margin: 8px 0 0 0; font-size: 14px; background: #f1f5f9; display: inline-block; padding: 4px 16px; border-radius: 4px; border: 1px solid #cbd5e1;">STATUTORY ANNUAL LEAVE APPLICATION FORM</h4>
  </div>

  <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 13px;">
    <tr>
      <td style="padding: 8px; border: 1px solid #cbd5e1; background: #f8fafc; font-weight: bold; width: 25%;">Applicant Officer:</td>
      <td style="padding: 8px; border: 1px solid #cbd5e1; width: 25%;">{{ applicant_name }}</td>
      <td style="padding: 8px; border: 1px solid #cbd5e1; background: #f8fafc; font-weight: bold; width: 25%;">Staff File Number:</td>
      <td style="padding: 8px; border: 1px solid #cbd5e1; width: 25%;">{{ file_number }}</td>
    </tr>
    <tr>
      <td style="padding: 8px; border: 1px solid #cbd5e1; background: #f8fafc; font-weight: bold;">Directorate:</td>
      <td style="padding: 8px; border: 1px solid #cbd5e1;">{{ department_name }}</td>
      <td style="padding: 8px; border: 1px solid #cbd5e1; background: #f8fafc; font-weight: bold;">Designation / GL:</td>
      <td style="padding: 8px; border: 1px solid #cbd5e1;">{{ designation }} (GL {{ grade_level }})</td>
    </tr>
    <tr>
      <td style="padding: 8px; border: 1px solid #cbd5e1; background: #f8fafc; font-weight: bold;">Leave Entitlement:</td>
      <td style="padding: 8px; border: 1px solid #cbd5e1;">{{ annual_leave_days }} Calendar Days</td>
      <td style="padding: 8px; border: 1px solid #cbd5e1; background: #f8fafc; font-weight: bold;">Period:</td>
      <td style="padding: 8px; border: 1px solid #cbd5e1;">From {{ start_date }} to {{ resumption_date }}</td>
    </tr>
    <tr>
      <td style="padding: 8px; border: 1px solid #cbd5e1; background: #f8fafc; font-weight: bold;">Relieving Officer:</td>
      <td style="padding: 8px; border: 1px solid #cbd5e1;">{{ relieving_officer }}</td>
      <td style="padding: 8px; border: 1px solid #cbd5e1; background: #f8fafc; font-weight: bold;">Leave Address / Phone:</td>
      <td style="padding: 8px; border: 1px solid #cbd5e1;">{{ contact_address }}</td>
    </tr>
  </table>

  <div style="margin-top: 25px; border-top: 1px solid #e2e8f0; padding-top: 15px; font-size: 12px; color: #64748b;">
    <p style="margin: 0;"><strong>Clearance Pathway:</strong> Immediate Supervisor &rarr; Director of Department &rarr; Director Administration</p>
    <p style="margin: 4px 0 0 0; color: #059669; font-weight: bold;">✓ Digitally submitted and encrypted along official registry channel</p>
  </div>
</div>'
                ],
                'steps' => [
                    ['name' => 'Officer Annual Leave Initiation', 'role' => null, 'type' => 'input', 'is_start' => true, 'is_end' => false],
                    ['name' => 'Immediate Supervisor Recommendation', 'role' => 'supervisor', 'type' => 'review', 'is_start' => false, 'is_end' => false],
                    ['name' => 'Departmental Director Endorsement', 'role' => 'director', 'type' => 'approval', 'is_start' => false, 'is_end' => false],
                    ['name' => 'Director Administration Statutory Clearance & Approval', 'role' => 'director admin', 'type' => 'approval', 'is_start' => false, 'is_end' => true]
                ]
            ],
            [
                'title' => 'Duty Tour Allowance (DTA) Requisition',
                'emoji' => '💼',
                'template' => [
                    'title' => 'Duty Tour Allowance (DTA) Requisition Voucher & Itinerary',
                    'type' => 'voucher',
                    'html' => '<div style="font-family: Arial, sans-serif; padding: 24px; color: #1e293b; line-height: 1.6;">
  <div style="text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 20px;">
    <h2 style="margin: 0; text-transform: uppercase; font-size: 18px; letter-spacing: 1px;">Office of the Accountant-General of the Federation</h2>
    <h3 style="margin: 4px 0 0 0; font-size: 14px; color: #475569; text-transform: uppercase;">Federal Treasury House, Abuja</h3>
    <h4 style="margin: 8px 0 0 0; font-size: 14px; background: #f1f5f9; display: inline-block; padding: 4px 16px; border-radius: 4px; border: 1px solid #cbd5e1;">DUTY TOUR ALLOWANCE (DTA) REQUISITION VOUCHER</h4>
  </div>

  <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 13px;">
    <tr>
      <td style="padding: 8px; border: 1px solid #cbd5e1; background: #f8fafc; font-weight: bold; width: 25%;">Officer Name:</td>
      <td style="padding: 8px; border: 1px solid #cbd5e1; width: 25%;">{{ applicant_name }}</td>
      <td style="padding: 8px; border: 1px solid #cbd5e1; background: #f8fafc; font-weight: bold; width: 25%;">Staff File Number:</td>
      <td style="padding: 8px; border: 1px solid #cbd5e1; width: 25%;">{{ file_number }}</td>
    </tr>
    <tr>
      <td style="padding: 8px; border: 1px solid #cbd5e1; background: #f8fafc; font-weight: bold;">Department:</td>
      <td style="padding: 8px; border: 1px solid #cbd5e1;">{{ department_name }}</td>
      <td style="padding: 8px; border: 1px solid #cbd5e1; background: #f8fafc; font-weight: bold;">Grade Level / Rank:</td>
      <td style="padding: 8px; border: 1px solid #cbd5e1;">{{ designation }} (GL {{ grade_level }})</td>
    </tr>
    <tr>
      <td style="padding: 8px; border: 1px solid #cbd5e1; background: #f8fafc; font-weight: bold;">Destination / Station:</td>
      <td style="padding: 8px; border: 1px solid #cbd5e1;">{{ destination }}</td>
      <td style="padding: 8px; border: 1px solid #cbd5e1; background: #f8fafc; font-weight: bold;">Tour Dates:</td>
      <td style="padding: 8px; border: 1px solid #cbd5e1;">{{ departure_date }} to {{ return_date }}</td>
    </tr>
    <tr>
      <td style="padding: 8px; border: 1px solid #cbd5e1; background: #f8fafc; font-weight: bold;">Approved DTA Rate / Day:</td>
      <td style="padding: 8px; border: 1px solid #cbd5e1;">₦{{ daily_rate }}</td>
      <td style="padding: 8px; border: 1px solid #cbd5e1; background: #f8fafc; font-weight: bold;">Total Amount Payable:</td>
      <td style="padding: 8px; border: 1px solid #cbd5e1; font-weight: bold; color: #047857;">₦{{ total_amount }}</td>
    </tr>
  </table>

  <div style="margin-bottom: 20px;">
    <h5 style="margin: 0 0 6px 0; font-size: 12px; text-transform: uppercase; color: #475569;">Official Purpose of Journey:</h5>
    <div style="border: 1px solid #cbd5e1; padding: 12px; background: #ffffff; border-radius: 4px; min-height: 60px; font-size: 13px;">
      {{ purpose_of_journey }}
    </div>
  </div>
</div>'
                ],
                'steps' => [
                    ['name' => 'Officer Journey Proposal & DTA Request', 'role' => null, 'type' => 'input', 'is_start' => true, 'is_end' => false],
                    ['name' => 'Departmental Director Recommendation', 'role' => 'director', 'type' => 'approval', 'is_start' => false, 'is_end' => false],
                    ['name' => 'Director Finance & Accounts Audit Clearance', 'role' => 'director finance', 'type' => 'review', 'is_start' => false, 'is_end' => false],
                    ['name' => 'Executive Approval & Warrant Clearance', 'role' => 'director admin', 'type' => 'approval', 'is_start' => false, 'is_end' => true]
                ]
            ],
            [
                'title' => 'Official Vehicle Requisition',
                'emoji' => '🚗',
                'template' => [
                    'title' => 'Official Pool Vehicle Requisition & Movement Pass',
                    'type' => 'transmittal',
                    'html' => '<div style="font-family: Arial, sans-serif; padding: 24px; color: #1e293b; line-height: 1.6;">
  <div style="text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 20px;">
    <h2 style="margin: 0; text-transform: uppercase; font-size: 18px; letter-spacing: 1px;">Office of the Accountant-General of the Federation</h2>
    <h3 style="margin: 4px 0 0 0; font-size: 14px; color: #475569; text-transform: uppercase;">Federal Treasury House, Abuja</h3>
    <h4 style="margin: 8px 0 0 0; font-size: 14px; background: #f1f5f9; display: inline-block; padding: 4px 16px; border-radius: 4px; border: 1px solid #cbd5e1;">OFFICIAL POOL VEHICLE REQUISITION PASS</h4>
  </div>

  <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 13px;">
    <tr>
      <td style="padding: 8px; border: 1px solid #cbd5e1; background: #f8fafc; font-weight: bold; width: 25%;">Requesting Officer:</td>
      <td style="padding: 8px; border: 1px solid #cbd5e1; width: 25%;">{{ applicant_name }}</td>
      <td style="padding: 8px; border: 1px solid #cbd5e1; background: #f8fafc; font-weight: bold; width: 25%;">Department:</td>
      <td style="padding: 8px; border: 1px solid #cbd5e1; width: 25%;">{{ department_name }}</td>
    </tr>
    <tr>
      <td style="padding: 8px; border: 1px solid #cbd5e1; background: #f8fafc; font-weight: bold;">Destination / Route:</td>
      <td style="padding: 8px; border: 1px solid #cbd5e1;">{{ destination }}</td>
      <td style="padding: 8px; border: 1px solid #cbd5e1; background: #f8fafc; font-weight: bold;">Schedule:</td>
      <td style="padding: 8px; border: 1px solid #cbd5e1;">{{ departure_time }} to {{ return_time }}</td>
    </tr>
    <tr>
      <td style="padding: 8px; border: 1px solid #cbd5e1; background: #f8fafc; font-weight: bold;">Passengers Count:</td>
      <td colspan="3" style="padding: 8px; border: 1px solid #cbd5e1;">{{ passengers_count }} Personnel</td>
    </tr>
  </table>

  <div style="margin-bottom: 20px;">
    <h5 style="margin: 0 0 6px 0; font-size: 12px; text-transform: uppercase; color: #475569;">Official Duty Description:</h5>
    <div style="border: 1px solid #cbd5e1; padding: 12px; background: #ffffff; border-radius: 4px; min-height: 60px; font-size: 13px;">
      {{ purpose }}
    </div>
  </div>
</div>'
                ],
                'steps' => [
                    ['name' => 'Officer Vehicle Requisition', 'role' => null, 'type' => 'input', 'is_start' => true, 'is_end' => false],
                    ['name' => 'Departmental Director Recommendation', 'role' => 'director', 'type' => 'approval', 'is_start' => false, 'is_end' => false],
                    ['name' => 'Transport Officer Allocation & Schedule', 'role' => 'transport officer', 'type' => 'review', 'is_start' => false, 'is_end' => false],
                    ['name' => 'Director Administration Final Release', 'role' => 'director admin', 'type' => 'approval', 'is_start' => false, 'is_end' => true]
                ]
            ]
        ];

        foreach ($applications as $appData) {
            // 1. Create / Update Document Template
            $template = DocumentTemplate::updateOrCreate(
                [
                    'title' => $appData['template']['title'],
                ],
                [
                    'type' => $appData['template']['type'],
                    'content_html' => $appData['template']['html'],
                    'version' => 1,
                    'is_active' => true,
                    'is_universal' => true,
                    'department_id' => null,
                    'created_by' => 1
                ]
            );

            // 2. Create / Update Workflow linked to Document Template
            $workflow = Workflow::updateOrCreate(
                [
                    'title' => $appData['title'],
                    'department_id' => null, // Strictly Global Institutional Application
                ],
                [
                    'emoji' => $appData['emoji'],
                    'status' => 'active',
                    'is_confidential' => false,
                    'document_template_id' => $template->id,
                    'created_by' => 1
                ]
            );

            $version = WorkflowVersion::firstOrCreate(
                [
                    'workflow_id' => $workflow->id,
                    'version_number' => 1
                ],
                [
                    'is_published' => true,
                    'published_at' => now(),
                    'mandate_parallel_splits' => false
                ]
            );

            // Clean existing steps for these specific applications to ensure idempotence
            WorkflowStep::where('workflow_version_id', $version->id)->delete();
            WorkflowTransition::where('workflow_version_id', $version->id)->delete();

            $createdSteps = [];
            foreach ($appData['steps'] as $idx => $stepInfo) {
                $step = WorkflowStep::create([
                    'workflow_version_id' => $version->id,
                    'name' => $stepInfo['name'],
                    'step_type' => $stepInfo['type'],
                    'role_required' => $stepInfo['role'],
                    'is_start_step' => $stepInfo['is_start'],
                    'is_end_step' => $stepInfo['is_end']
                ]);
                $createdSteps[] = $step;
            }

            // Create sequential forward and return transitions
            for ($i = 0; $i < count($createdSteps) - 1; $i++) {
                $fromStep = $createdSteps[$i];
                $toStep = $createdSteps[$i + 1];

                WorkflowTransition::create([
                    'workflow_version_id' => $version->id,
                    'from_step_id' => $fromStep->id,
                    'to_step_id' => $toStep->id,
                    'action_name' => $i === 0 ? 'Submit Application' : 'Recommend & Transmit Upward',
                ]);

                if ($i > 0) {
                    WorkflowTransition::create([
                        'workflow_version_id' => $version->id,
                        'from_step_id' => $fromStep->id,
                        'to_step_id' => $createdSteps[0]->id,
                        'action_name' => 'Return for Clarification / Correction',
                    ]);
                }
            }
        }
    }
}
