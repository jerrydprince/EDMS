<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;

class TemplateHydrationService
{
    /**
     * Hydrate template HTML content by interpolating docket and officer tokens.
     *
     * @param string $contentHtml
     * @param Task|null $task
     * @param User|null $user
     * @param array $extraData
     * @return string
     */
    public function hydrate(string $contentHtml, ?Task $task = null, ?User $user = null, array $extraData = []): string
    {
        $user = $user ?? auth()->user();
        
        // Extract AGF directive if present on the docket
        $agfDirective = 'Please treat in accordance with standard civil service guidelines and financial regulations.';
        if ($task && $task->minutes) {
            $agfMinute = $task->minutes->reverse()->first(function ($m) {
                $isAgfRole = $m->user && $m->user->roles && $m->user->roles->contains(function ($r) {
                    $name = is_string($r) ? $r : ($r->name ?? '');
                    return str_contains(strtolower($name), 'accountant-general') || str_contains(strtolower($name), 'super user');
                });
                $isAgfDept = $m->user && $m->user->department && str_contains(strtolower($m->user->department->title ?? ''), 'agf');
                return $isAgfRole || $isAgfDept;
            });

            if ($agfMinute) {
                $agfDirective = $agfMinute->directive ?: ($agfMinute->action ?: $agfDirective);
            }
        }

        $trackingId = $task ? ($task->tracking_id ?: ('EDMS-' . str_pad($task->id, 5, '0', STR_PAD_LEFT))) : 'EDMS-' . strtoupper(bin2hex(random_bytes(3)));
        $title = $task ? $task->title : ($extraData['title'] ?? 'Official Subject Matter');
        $description = $task ? ($task->description ?: '') : '';
        $officerName = $user ? ($user->first_name . ' ' . $user->last_name) : 'Authorized Desk Officer';
        $officerRank = $user && $user->roles && $user->roles->first() ? ucwords($user->roles->first()->name) : 'Desk Officer';
        $deptTitle = $user && $user->department ? $user->department->title : 'Office of the Accountant General of the Federation';
        $dateFormatted = Carbon::now()->format('d F Y');
        $year = Carbon::now()->year;
        $externalRef = $task && $task->file ? ($task->file->file_number ?: ('OAGF/' . $year . '/' . $task->id)) : 'OAGF/' . $year . '/DISP-' . rand(100, 999);
        $senderName = $task && $task->file ? ($task->file->sender ?: 'Central Registry') : ($extraData['sender'] ?? 'External MDA / Correspondent');
        $amount = isset($extraData['amount']) ? ('₦' . number_format((float)str_replace([',', ' '], '', $extraData['amount']), 2)) : 'N/A';
        $deadline = $task && $task->due_date ? Carbon::parse($task->due_date)->format('d F Y') : Carbon::now()->addDays(3)->format('d F Y');

        $fileNumber = $user ? ($user->ippis_no ?: ($user->staff_number ?: 'FTH/EST/' . $user->id)) : 'FTH/EST/1001';
        $designation = ($user && $user->designation ? $user->designation->name : null) ?: $officerRank;
        $gradeLevel = $user && $user->grade_level ? $user->grade_level : '12';

        $referenceId = $extraData['reference_id'] ?? ($extraData['reference_number'] ?? ($extraData['reference_no'] ?? $externalRef));

        $replacements = [
            'tracking_id' => $trackingId,
            'docket_number' => $trackingId,
            'docket_no' => $trackingId,
            'title' => $title,
            'subject' => $title,
            'description' => $description,
            'officer_name' => $officerName,
            'user_name' => $officerName,
            'applicant_name' => $officerName,
            'officer_role' => $officerRank,
            'user_role' => $officerRank,
            'designation' => $designation,
            'grade_level' => $gradeLevel,
            'file_number' => $fileNumber,
            'staff_file_number' => $fileNumber,
            'service_no' => $fileNumber,
            'department' => $deptTitle,
            'department_name' => $deptTitle,
            'agf_directive' => $agfDirective,
            'date' => $dateFormatted,
            'today' => $dateFormatted,
            'submission_date' => $dateFormatted,
            'year' => $year,
            'external_ref' => $referenceId,
            'reference_no' => $referenceId,
            'reference_id' => $referenceId,
            'reference_number' => $referenceId,
            'document_reference_number' => $referenceId,
            'sender_name' => $senderName,
            'sender' => $senderName,
            'amount' => $amount,
            'deadline' => $deadline,
        ];

        // Merge extra placeholders (custom form inputs from officer)
        if (!empty($extraData) && is_array($extraData)) {
            foreach ($extraData as $extraKey => $extraVal) {
                if ($extraVal !== null && is_scalar($extraVal)) {
                    $replacements[$extraKey] = (string)$extraVal;
                }
            }
        }

        // Replace case-insensitively {{ placeholder }} or {{placeholder}}
        foreach ($replacements as $key => $val) {
            $pattern = '/\{\{\s*' . preg_quote($key, '/') . '\s*\}\}/i';
            $contentHtml = preg_replace($pattern, htmlspecialchars($val, ENT_QUOTES, 'UTF-8'), $contentHtml);
        }

        return $contentHtml;
    }
}
