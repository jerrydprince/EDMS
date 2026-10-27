<?php

namespace App\Http\Controllers;

use App\Models\DocumentTemplate;
use App\Models\Task;
use App\Models\TaskDoc;
use App\Models\TaskActivity;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DocumentGenerationController extends Controller
{
    public function getTemplates()
    {
        $user = auth()->user();
        $isSuperAdmin = $user && $user->hasRole('super admin');
        
        $templates = DocumentTemplate::where('is_active', true)
            ->when(!$isSuperAdmin, function ($query) use ($user) {
                $query->where(function ($q) use ($user) {
                    // 1. Global / Institutional templates available to everyone
                    $q->where('is_universal', true)
                      ->orWhereNull('department_id');

                    // 2. Department-scoped templates matching user's branch hierarchy
                    if ($user && $user->department_id) {
                        $q->orWhere(function ($deptQ) use ($user) {
                            $deptQ->where('department_id', $user->department_id);

                            $deptQ->where(function ($divQ) use ($user) {
                                $divQ->whereNull('division_id')
                                     ->orWhere('division_id', $user->division_id);
                            });

                            $deptQ->where(function ($unitQ) use ($user) {
                                $unitQ->whereNull('unit_id')
                                      ->orWhere('unit_id', $user->unit_id);
                            });

                            $deptQ->where(function ($secQ) use ($user) {
                                $secQ->whereNull('section_id')
                                      ->orWhere('section_id', $user->section_id);
                            });
                        });
                    }
                });
            })
            ->latest()
            ->get();
            
        return response()->json($templates);
    }

    public function generate(Request $request, Task $task)
    {
        $request->validate([
            'template_id' => 'required|exists:document_templates,id',
            'placeholders' => 'nullable|array'
        ]);

        $template = DocumentTemplate::findOrFail($request->template_id);
        
        $user = auth()->user();
        $isSuperAdmin = $user && $user->hasRole('super admin');

        // Check cascading scope authorization
        if (!$isSuperAdmin && !$template->is_universal && $template->department_id) {
            if ($template->department_id !== $user->department_id) {
                return response()->json(['error' => 'Unauthorized: This template is restricted to another department.'], 403);
            }
            if ($template->division_id && $template->division_id !== $user->division_id) {
                return response()->json(['error' => 'Unauthorized: This template is restricted to another division.'], 403);
            }
            if ($template->unit_id && $template->unit_id !== $user->unit_id) {
                return response()->json(['error' => 'Unauthorized: This template is restricted to another unit.'], 403);
            }
            if ($template->section_id && $template->section_id !== $user->section_id) {
                return response()->json(['error' => 'Unauthorized: This template is restricted to another section.'], 403);
            }
        }

        $hydrationService = app(\App\Services\TemplateHydrationService::class);
        $html = $hydrationService->hydrate($template->content_html, $task, $user, $request->placeholders ?? []);

        // Process explicit user overrides if provided
        if ($request->placeholders && is_array($request->placeholders)) {
            foreach ($request->placeholders as $key => $value) {
                if (!empty($value)) {
                    $html = str_replace('{{ ' . $key . ' }}', $value, $html);
                    $html = str_replace('{{' . $key . '}}', $value, $html);
                }
            }
        }

        // Generate PDF
        $pdf = Pdf::loadHTML($html);
        $fileName = 'generated_' . Str::slug($template->title) . '_' . time() . '.pdf';
        $directory = public_path('taskdocs/generated');
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }
        
        $fullPath = $directory . '/' . $fileName;
        $pdf->save($fullPath);
        $publicPath = 'taskdocs/generated/' . $fileName;
        $fileSize = file_exists($fullPath) ? filesize($fullPath) : 0;

        // Attach to task
        $taskDoc = TaskDoc::create([
            'task_id' => $task->id,
            'path' => $publicPath,
            'file_name' => $template->title . '.pdf',
            'size' => $fileSize > 1048576 ? round($fileSize / 1048576, 2) . ' MB' : round($fileSize / 1024, 2) . ' KB'
        ]);


        // Audit Log
        $ta = new TaskActivity();
        $ta->task_id = $task->id;
        $ta->description = 'Generated document from template: ' . $template->title . ' (v' . $template->version . ')';
        $ta->created_by = $user->id ?? 1;
        $ta->save();

        return response()->json([
            'success' => true,
            'taskDoc' => $taskDoc,
            'message' => 'Document generated successfully.'
        ]);
    }

    /**
     * Generate a standalone official PDF file from a template before task creation
     */
    public function generateStandaloneFile(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:document_templates,id',
            'placeholders' => 'nullable|array',
            'title' => 'nullable|string'
        ]);

        $template = DocumentTemplate::findOrFail($request->template_id);
        $user = auth()->user();

        $hydrationService = app(\App\Services\TemplateHydrationService::class);
        $html = $hydrationService->hydrate(
            $template->content_html,
            null,
            $user,
            $request->placeholders ?? []
        );

        // Generate PDF
        $pdf = Pdf::loadHTML($html);
        $fileName = 'official_' . Str::slug($template->title) . '_' . time() . '.pdf';
        
        $year = date('Y');
        $month = date('m');
        $directory = public_path("task/{$year}/{$month}");
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        $fullPath = $directory . '/' . $fileName;
        $pdf->save($fullPath);
        $publicPath = "/task/{$year}/{$month}/" . $fileName;
        $fileSize = file_exists($fullPath) ? filesize($fullPath) : 0;
        $formattedSize = $fileSize > 1048576 ? round($fileSize / 1048576, 2) . ' MB' : round($fileSize / 1024, 2) . ' KB';

        return response()->json([
            'success' => true,
            'file' => [
                'path' => $publicPath,
                'fileName' => $fileName,
                'file_name' => $fileName,
                'original_name' => $template->title . '.pdf',
                'size' => $formattedSize,
                'is_template_generated' => true,
                'template_id' => $template->id,
                'template_title' => $template->title
            ],
            'message' => 'Official document generated from template successfully.'
        ]);
    }

    /**
     * Preview hydrated HTML before PDF generation
     */
    public function previewHtml(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:document_templates,id',
            'placeholders' => 'nullable|array'
        ]);

        $template = DocumentTemplate::findOrFail($request->template_id);
        $user = auth()->user();

        $hydrationService = app(\App\Services\TemplateHydrationService::class);
        $html = $hydrationService->hydrate(
            $template->content_html,
            null,
            $user,
            $request->placeholders ?? []
        );

        return response()->json([
            'success' => true,
            'html' => $html,
            'template' => $template
        ]);
    }
}
