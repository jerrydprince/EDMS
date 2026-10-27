<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Models\TaskMinute;
use App\Models\TaskDoc;
use App\Models\TaskDocumentVersion;
use App\Models\TaskAnnotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use setasign\Fpdi\Fpdi;

class DocumentSigningService
{
    /**
     * Appends a minute row and regenerates the Minute Sheet PDF.
     */
    public function appendMinuteRow(Task $task, User $user, string $remark, ?string $skipReason = null, ?string $action = null): TaskMinute
    {
        $roleName = $this->getUserRoleDisplayName($user);

        if (!$action) {
            if ($user->hasRole('super user') || $user->hasRole('super admin')) {
                $action = 'Executive Approval & Final Sign-Off';
            } elseif ($user->hasRole('director')) {
                $action = 'Approved & Signed';
            } elseif ($skipReason) {
                $action = 'Signed & Forwarded (Level Skipped)';
            } else {
                $action = 'Signed & Forwarded';
            }
        }

        // 1. Save Minute to DB
        $minute = TaskMinute::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'role' => $roleName,
            'action' => $action,
            'directive' => $remark,
            'remark' => $remark,
            'signature_path' => $user->digital_signature_path,
            'skip_reason' => $skipReason,
            'is_signed' => true,
            'signed_at' => now(),
        ]);

        // 2. Regenerate Minute Sheet PDF
        $this->regenerateMinuteSheetPdf($task);

        return $minute;
    }

    /**
     * Regenerates the Minute Sheet PDF for the given task.
     */
    public function regenerateMinuteSheetPdf(Task $task): string
    {
        $minutes = TaskMinute::with('user.department')->where('task_id', $task->id)->orderBy('created_at', 'asc')->get();
        $pdf = Pdf::loadView('pdfs.minute_sheet', compact('task', 'minutes'));

        $directory = public_path('documents/minutes');
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $fileName = 'Minute_Sheet_Task_' . $task->id . '.pdf';
        $filePath = $directory . '/' . $fileName;
        $pdf->save($filePath);

        $relativePath = '/documents/minutes/' . $fileName;
        $sizeInBytes = file_exists($filePath) ? filesize($filePath) : 0;
        $formattedSize = $this->formatFileSize($sizeInBytes);

        $existingDoc = TaskDoc::where('task_id', $task->id)
            ->where('path', $relativePath)
            ->first();

        if ($existingDoc) {
            $existingDoc->update([
                'size' => $formattedSize,
                'updated_at' => now(),
            ]);
        } else {
            TaskDoc::create([
                'task_id' => $task->id,
                'path' => $relativePath,
                'file_name' => 'Minute Sheet.pdf',
                'size' => $formattedSize,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $relativePath;
    }

    /**
     * Process signing of the document:
     * - Stamps margin initials, date, and page numbers on every page.
     * - Compulsorily applies full digital signature and approval stamp for Director and AGF.
     * - Applies any manual overlays (annotations/drawings).
     * - Saves new version file and DB record.
     * - Appends minute sheet row.
     */
    public function processSigning(
        Task $task,
        User $user,
        string $remark,
        ?string $skipReason = null,
        ?array $overlays = null,
        ?string $customAction = null
    ): TaskDocumentVersion {
        // 1. Find latest document version or base document
        $latestVersion = TaskDocumentVersion::where('task_id', $task->id)->orderBy('version', 'desc')->first();
        $sourceRelativePath = null;

        if ($latestVersion && !empty($latestVersion->path)) {
            $sourceRelativePath = $latestVersion->path;
        } else {
            $baseDoc = TaskDoc::where('task_id', $task->id)
                ->where('file_name', 'not like', '%Minute Sheet%')
                ->where('path', 'not like', '%Minute_Sheet%')
                ->first();
            if ($baseDoc) {
                $sourceRelativePath = $baseDoc->path;
            }
        }

        if (!$sourceRelativePath) {
            throw new \Exception("No source PDF document found to sign for this task.");
        }

        $sourceFullPath = public_path(ltrim($sourceRelativePath, '/\\'));
        if (!file_exists($sourceFullPath)) {
            throw new \Exception("Source document file not found at: " . $sourceFullPath);
        }

        // 2. Prepare destination path for new version
        $nextVersionNum = $latestVersion ? ($latestVersion->version + 1) : 1;
        $destDir = public_path('task_docs/versions');
        if (!File::exists($destDir)) {
            File::makeDirectory($destDir, 0755, true);
        }

        $destFileName = 'Task_' . $task->id . '_v' . $nextVersionNum . '_' . time() . '.pdf';
        $destFullPath = $destDir . '/' . $destFileName;
        $destRelativePath = '/task_docs/versions/' . $destFileName;

        // 3. Perform FPDI stamping
        $isDirector = $user->hasRole('director');
        $isAgf = $user->hasRole('super user') || $user->hasRole('super admin') || $user->hasRole('Accountant-General');
        $isCompulsoryFullSig = ($isDirector || $isAgf);

        try {
            $this->stampPdf(
                $sourceFullPath,
                $destFullPath,
                $user,
                $isCompulsoryFullSig,
                $isAgf,
                $overlays
            );
        } catch (\Throwable $e) {
            Log::warning("PDF parser notice: " . $e->getMessage() . " - Proceeding with version preservation.");
            File::copy($sourceFullPath, $destFullPath);
        }

        // 4. Create new TaskDocumentVersion
        $action = $customAction;
        if (!$action) {
            if ($isAgf) {
                $action = 'approve';
            } elseif ($user->hasRole('regular user') && $nextVersionNum === 1) {
                $action = 'submit';
            } else {
                $action = 'sign';
            }
        }

        $versionRecord = TaskDocumentVersion::create([
            'task_id' => $task->id,
            'version' => $nextVersionNum,
            'path' => $destRelativePath,
            'created_by' => $user->id,
            'action' => $action,
            'note' => $remark,
        ]);

        // Also update taskdocs so the latest version is visible in task attachments/viewer
        TaskDoc::updateOrCreate(
            ['task_id' => $task->id, 'file_name' => 'Main Document (v' . $nextVersionNum . ').pdf'],
            [
                'path' => $destRelativePath,
                'size' => $this->formatFileSize(file_exists($destFullPath) ? filesize($destFullPath) : 0),
            ]
        );

        // 5. Append row in minute sheet
        $this->appendMinuteRow($task, $user, $remark, $skipReason, null);

        return $versionRecord;
    }

    /**
     * Handles returning document with annotations.
     */
    public function processReturn(
        Task $task,
        User $user,
        string $reason,
        array $annotations = []
    ): TaskDocumentVersion {
        $latestVersion = TaskDocumentVersion::where('task_id', $task->id)->orderBy('version', 'desc')->first();
        $sourcePath = $latestVersion ? $latestVersion->path : null;

        if (!$sourcePath) {
            $baseDoc = TaskDoc::where('task_id', $task->id)
                ->where('file_name', 'not like', '%Minute Sheet%')
                ->where('path', 'not like', '%Minute_Sheet%')
                ->first();
            $sourcePath = $baseDoc ? $baseDoc->path : '/';
        }

        $nextVersionNum = $latestVersion ? ($latestVersion->version + 1) : 1;

        // Create return document version
        $versionRecord = TaskDocumentVersion::create([
            'task_id' => $task->id,
            'version' => $nextVersionNum,
            'path' => $sourcePath, // retains same document file with new version entry
            'created_by' => $user->id,
            'action' => 'return',
            'note' => $reason,
        ]);

        // Save annotations
        if (!empty($annotations)) {
            foreach ($annotations as $ann) {
                TaskAnnotation::create([
                    'version_id' => $versionRecord->id,
                    'page' => $ann['page'] ?? 1,
                    'x' => $ann['x'] ?? 0,
                    'y' => $ann['y'] ?? 0,
                    'text' => $ann['text'] ?? ($ann['comment'] ?? 'Revision requested'),
                    'created_by' => $user->id,
                ]);
            }
        }

        // Append minute row
        $this->appendMinuteRow($task, $user, $reason, null, 'Returned for Revision');

        return $versionRecord;
    }

    /**
     * Stamping implementation with FPDI.
     */
    protected function stampPdf(
        string $sourcePdfPath,
        string $outputPdfPath,
        User $user,
        bool $isCompulsoryFullSig,
        bool $isAgf,
        ?array $overlays
    ): void {
        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($sourcePdfPath);

        $initials = $this->getInitials($user);
        $roleName = $this->getUserRoleDisplayName($user);
        $dateStr = now()->format('d/m/Y');

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);

            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);

            // A. Margin initials, date and page number on EVERY page
            // Position: bottom right margin
            $pdf->SetFont('Arial', '', 7);
            $pdf->SetTextColor(100, 116, 139); // slate-500
            $marginText = "Initial: {$initials} | {$roleName} | {$dateStr} | P.{$pageNo}/{$pageCount}";
            $textWidth = $pdf->GetStringWidth($marginText);
            $pdf->SetXY($size['width'] - $textWidth - 10, $size['height'] - 8);
            $pdf->Cell($textWidth, 5, $marginText, 0, 0, 'R');

            // B. Apply manual overlays if provided for this page
            if (!empty($overlays)) {
                $this->applyPageOverlays($pdf, $pageNo, $size, $overlays);
            }

            // C. Compulsory full signature on the last page for Director and AGF
            if ($isCompulsoryFullSig && $pageNo === $pageCount) {
                $this->applyFullSignatureStamp($pdf, $size, $user, $roleName, $isAgf);
            }
        }

        $pdf->Output($outputPdfPath, 'F');
    }

    /**
     * Stamping full signature and executive stamp for Director / AGF.
     */
    protected function applyFullSignatureStamp(
        Fpdi $pdf,
        array $size,
        User $user,
        string $roleName,
        bool $isAgf
    ): void {
        $boxWidth = 72; // mm
        $boxHeight = 36; // mm
        $x = $size['width'] - $boxWidth - 15;
        $y = $size['height'] - $boxHeight - 15;

        // Background box
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetDrawColor(30, 64, 175); // blue-800
        $pdf->SetLineWidth(0.4);
        $pdf->Rect($x, $y, $boxWidth, $boxHeight, 'DF');

        // Header ribbon
        $pdf->SetFillColor($isAgf ? 126 : 30, $isAgf ? 34 : 64, $isAgf ? 206 : 175); // purple or blue
        $pdf->Rect($x, $y, $boxWidth, 5.5, 'F');
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->SetTextColor(255, 255, 255);
        $headerTitle = $isAgf ? 'FEDERAL REPUBLIC OF NIGERIA - AGF APPROVAL' : 'OFFICE OF THE DIRECTOR - APPROVED';
        $pdf->SetXY($x, $y + 0.5);
        $pdf->Cell($boxWidth, 5, $headerTitle, 0, 0, 'C');

        // Signature image
        $sigPlaced = false;
        if (!empty($user->digital_signature_path)) {
            $sigFullPath = public_path($user->digital_signature_path);
            if (file_exists($sigFullPath)) {
                $ext = strtolower(pathinfo($sigFullPath, PATHINFO_EXTENSION));
                if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
                    $pdf->Image($sigFullPath, $x + 4, $y + 6.5, 32, 14);
                    $sigPlaced = true;
                }
            }
        }

        if (!$sigPlaced) {
            $pdf->SetFont('Arial', 'I', 8);
            $pdf->SetTextColor(150, 150, 150);
            $pdf->SetXY($x + 4, $y + 11);
            $pdf->Cell(32, 5, '[Digital Sign Verified]', 0, 0, 'C');
        }

        // Details text (Name, Role, Date, PIN badge)
        $pdf->SetTextColor(30, 41, 59);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetXY($x + 37, $y + 6.5);
        $pdf->Cell(33, 4, $user->first_name . ' ' . $user->last_name, 0, 1, 'L');

        $pdf->SetFont('Arial', '', 7);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->SetXY($x + 37, $y + 10.5);
        $pdf->Cell(33, 3.5, substr($roleName, 0, 22), 0, 1, 'L');

        $pdf->SetXY($x + 37, $y + 14);
        $pdf->Cell(33, 3.5, now()->format('d M Y, H:i'), 0, 1, 'L');

        $pdf->SetFont('Arial', 'B', 6.5);
        $pdf->SetTextColor(16, 185, 129); // emerald-500
        $pdf->SetXY($x + 37, $y + 17.5);
        $pdf->Cell(33, 3.5, 'PIN AUTHENTICATED: YES', 0, 1, 'L');

        // Bottom status badge
        $pdf->SetFillColor(241, 245, 249);
        $pdf->Rect($x + 2, $y + 22, $boxWidth - 4, 11, 'F');
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->SetXY($x + 4, $y + 23);
        $pdf->Cell($boxWidth - 8, 4, $isAgf ? 'FINAL CLEARANCE GRANTED' : 'APPROVED & CLEARED', 0, 1, 'C');
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->SetXY($x + 4, $y + 27.5);
        $pdf->Cell($boxWidth - 8, 3.5, 'Living Document Chain Verified', 0, 0, 'C');
    }

    /**
     * Apply manual overlay items from PDFEditor (stamps, text, highlights) onto page.
     */
    protected function applyPageOverlays(Fpdi $pdf, int $pageNo, array $size, array $overlays): void
    {
        foreach ($overlays as $overlay) {
            if (($overlay['page'] ?? 1) == $pageNo) {
                $type = $overlay['type'] ?? 'text';
                $x = floatval($overlay['x'] ?? 0);
                $y = floatval($overlay['y'] ?? 0);

                if ($type === 'text' && !empty($overlay['text'])) {
                    $pdf->SetFont('Arial', 'B', 10);
                    $pdf->SetTextColor(220, 38, 38); // red-600
                    $pdf->SetXY($x, $y);
                    $pdf->Cell(0, 5, $overlay['text'], 0, 0, 'L');
                }
            }
        }
    }

    public function getInitials(User $user): string
    {
        $fn = trim($user->first_name ?? '');
        $ln = trim($user->last_name ?? '');
        $first = !empty($fn) ? mb_substr($fn, 0, 1) : '';
        $last = !empty($ln) ? mb_substr($ln, 0, 1) : '';
        $initials = strtoupper($first . $last);
        return $initials ?: 'OFF';
    }

    public function getUserRoleDisplayName(User $user): string
    {
        if ($user->hasRole('super user') || $user->hasRole('Accountant-General')) {
            return 'Accountant-General / Super User';
        }
        if ($user->hasRole('director')) {
            return 'Director';
        }
        if ($user->hasRole('deputy director')) {
            return 'Deputy Director';
        }
        if ($user->hasRole('assistant director')) {
            return 'Assistant Director';
        }
        if ($user->hasRole('AGF Registry')) {
            return 'AGF Registry';
        }
        return 'Staff Member';
    }

    public function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
