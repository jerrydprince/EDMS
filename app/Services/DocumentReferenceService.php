<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Division;
use App\Models\Unit;
use App\Models\Section;
use App\Models\DocumentTemplate;
use App\Models\DocumentType;
use Carbon\Carbon;

class DocumentReferenceService
{
    /**
     * Dictionary of established Federal Civil Service abbreviations
     */
    protected static array $acronymDirectory = [
        'finance and accounts' => 'F&A',
        'finance & accounts' => 'F&A',
        'revenue and investment' => 'R&I',
        'revenue & investment' => 'R&I',
        'information and communications technology' => 'ICT',
        'information & communications technology' => 'ICT',
        'procurement' => 'PROC',
        'administration' => 'ADM',
        'general administration' => 'GEN-ADM',
        'funds' => 'FD',
        'funds admin' => 'FD-ADM',
        'internal audit' => 'IA',
        'special duties' => 'SD',
        'consolidated accounts' => 'CONACC',
        'treasury' => 'TRY',
        'inspectorate' => 'INSP',
        'pension' => 'PEN',
        'welfare' => 'WELF',
        'human resources' => 'HR',
        'planning, research and statistics' => 'PRS',
        'planning, research & statistics' => 'PRS',
        'expenditure' => 'EXP',
        'fiscal' => 'FISC',
        'payroll' => 'PAY',
        'taxation' => 'TAX',
        'revenue' => 'REV',
        'audit' => 'AUD',
    ];

    /**
     * Generate standard civil service acronym/code from a department/division/unit title
     */
    public static function generateAcronym(?string $title): string
    {
        if (!$title) {
            return 'GEN';
        }

        $clean = trim($title);
        $normalized = strtolower(preg_replace('/\s+/', ' ', $clean));

        if (isset(self::$acronymDirectory[$normalized])) {
            return self::$acronymDirectory[$normalized];
        }

        // Handle ampersand conjunctions (e.g. Planning & Research -> P&R)
        if (str_contains($clean, '&')) {
            $parts = explode('&', $clean);
            $w1 = strtoupper(substr(trim($parts[0]), 0, 1));
            $w2 = strtoupper(substr(trim($parts[1]), 0, 1));
            return "{$w1}&{$w2}";
        }

        // Multi-word titles: take initials ignoring stop words
        $words = preg_split('/\s+/', $clean);
        if (count($words) > 1) {
            $acronym = '';
            $stopWords = ['and', 'of', 'the', 'for', 'in', 'to', 'on', 'with', 'at', 'by'];
            foreach ($words as $w) {
                if (in_array(strtolower($w), $stopWords)) {
                    continue;
                }
                $acronym .= strtoupper(substr($w, 0, 1));
            }
            if (strlen($acronym) >= 2) {
                return $acronym;
            }
        }

        // Single word: take first 3-4 alphanumeric characters
        $alpha = preg_replace('/[^A-Za-z0-9]/', '', $clean);
        return strtoupper(substr($alpha, 0, min(4, strlen($alpha))));
    }

    /**
     * Standardize document type abbreviation
     */
    public static function getDocTypeAbbreviation($docType): string
    {
        if ($docType instanceof DocumentType) {
            $name = $docType->name;
        } elseif (is_numeric($docType)) {
            $dt = DocumentType::find($docType);
            $name = $dt ? $dt->name : 'DOC';
        } elseif (is_string($docType)) {
            $name = $docType;
        } else {
            $name = 'DOC';
        }

        $clean = strtolower(trim($name));
        $map = [
            'memo' => 'MEMO',
            "memo's" => 'MEMO',
            'memorandum' => 'MEMO',
            'letter' => 'LTR',
            'letters' => 'LTR',
            'query' => 'QRY',
            'award letter' => 'AL',
            'demand notice' => 'DN',
            'voucher' => 'VOUCH',
            'vouchers' => 'VOUCH',
            'mandate' => 'MAND',
            'mandates' => 'MAND',
            'confirmed mandates' => 'CMAND',
            'online mandate' => 'OMAND',
            'circular' => 'CIRC',
            'dispatch' => 'DISP',
            'recurrent expenditure' => 'REC-EXP',
            'capital expenditure' => 'CAP-EXP',
            'reports' => 'REP',
            'report' => 'REP',
            'minutes' => 'MIN',
            'minutes of tenders board' => 'MTB',
            'warrants' => 'WARR',
            'cashbook' => 'CB',
            'repayment' => 'RPMT',
            'complete file' => 'FILE',
        ];

        return $map[$clean] ?? self::generateAcronym($name);
    }

    /**
     * Assemble the Reference Prefix based on organizational tier or custom override
     */
    public static function buildPrefix(
        ?int $departmentId = null,
        ?int $divisionId = null,
        ?int $unitId = null,
        ?int $sectionId = null,
        ?string $customOverride = null
    ): string {
        if (!empty($customOverride)) {
            return trim($customOverride, '/');
        }

        $parts = ['OAGF'];

        if ($departmentId) {
            $dept = Department::find($departmentId);
            $parts[] = self::generateAcronym($dept?->title);

            if ($divisionId) {
                $div = Division::find($divisionId);
                $parts[] = 'DIV-' . self::generateAcronym($div?->title);

                if ($unitId) {
                    $unit = Unit::find($unitId);
                    $parts[] = 'U-' . self::generateAcronym($unit?->title);

                    if ($sectionId) {
                        $sec = Section::find($sectionId);
                        $parts[] = 'SEC-' . self::generateAcronym($sec?->title);
                    }
                }
            }
        } else {
            // Global / Institutional
            $parts[] = 'GEN';
        }

        return implode('/', $parts);
    }

    /**
     * Generate preview of Reference Scheme
     */
    public static function previewReferenceScheme(
        ?int $departmentId = null,
        ?int $divisionId = null,
        ?int $unitId = null,
        ?int $sectionId = null,
        $docType = null,
        ?string $customOverride = null
    ): array {
        $prefix = self::buildPrefix($departmentId, $divisionId, $unitId, $sectionId, $customOverride);
        $typeAbbr = self::getDocTypeAbbreviation($docType);
        $year = Carbon::now()->year;
        $sampleRef = "{$prefix}/{$typeAbbr}/{$year}/0001";

        return [
            'prefix' => $prefix,
            'type_abbr' => $typeAbbr,
            'sample' => $sampleRef,
            'format_mask' => "{$prefix}/{$typeAbbr}/YYYY/####",
        ];
    }

    /**
     * Atomically generate next sequential Reference ID for a given document template
     */
    public static function generateNextReference(DocumentTemplate $template, $docType = null): string
    {
        $prefix = $template->reference_prefix ?: self::buildPrefix(
            $template->department_id,
            $template->division_id,
            $template->unit_id,
            $template->section_id
        );

        $typeAbbr = self::getDocTypeAbbreviation($docType ?: $template->documentType ?: $template->type);
        $year = Carbon::now()->year;

        // Atomically increment sequence on the template record
        $seq = \Illuminate\Support\Facades\DB::transaction(function () use ($template) {
            $t = DocumentTemplate::where('id', $template->id)->lockForUpdate()->first();
            $next = ($t->reference_sequence ?? 0) + 1;
            $t->update(['reference_sequence' => $next]);
            return $next;
        });

        $paddedSeq = str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
        return "{$prefix}/{$typeAbbr}/{$year}/{$paddedSeq}";
    }
}
