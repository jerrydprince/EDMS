<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\File;

class DigitalStampingService
{
    /**
     * Generate Checking Confirmation Stamp (Rectangular Federal Inspection Stamp)
     *
     * @param User $officer
     * @param string $voucherNumber
     * @param string|null $date
     * @return string Relative path to generated stamp
     */
    public function generateCheckingStamp(User $officer, string $voucherNumber, $date = null): string
    {
        $dateStr = $date ? date('d-M-Y H:i', strtotime($date)) : date('d-M-Y H:i');
        $officerName = strtoupper($officer->first_name . ' ' . $officer->last_name);
        $officerTitle = $officer->title ?? 'CHIEF ACCOUNTANT';
        $refHash = strtoupper(substr(hash('sha256', $voucherNumber . $officer->id . $dateStr), 0, 10));
        $refNo = 'CHK-' . $refHash;

        $dir = public_path('stamps/checking');
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $filename = 'checking_' . md5($voucherNumber . '_' . $officer->id) . '.svg';
        $fullPath = $dir . '/' . $filename;

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 340 130" width="340" height="130">
    <rect x="3" y="3" width="334" height="124" rx="6" fill="#f0fdf4" stroke="#15803d" stroke-width="2.5" stroke-dasharray="0" />
    <rect x="8" y="8" width="324" height="114" rx="3" fill="none" stroke="#16a34a" stroke-width="1" />
    <text x="170" y="27" font-family="Arial, Helvetica, sans-serif" font-size="11" font-weight="900" fill="#14532d" text-anchor="middle" letter-spacing="0.5">FEDERAL REPUBLIC OF NIGERIA</text>
    <text x="170" y="42" font-family="Arial, Helvetica, sans-serif" font-size="12" font-weight="bold" fill="#15803d" text-anchor="middle" letter-spacing="1">CHECKED &amp; PASSED FOR PAYMENT</text>
    <line x1="20" y1="48" x2="320" y2="48" stroke="#16a34a" stroke-width="1.2" />
    <text x="24" y="66" font-family="Arial, Helvetica, sans-serif" font-size="10" font-weight="bold" fill="#166534">OFFICER:</text>
    <text x="85" y="66" font-family="Arial, Helvetica, sans-serif" font-size="10" font-weight="600" fill="#0f172a">{$officerName} ({$officerTitle})</text>
    <text x="24" y="82" font-family="Arial, Helvetica, sans-serif" font-size="10" font-weight="bold" fill="#166534">VERIFIED:</text>
    <text x="85" y="82" font-family="Arial, Helvetica, sans-serif" font-size="10" font-weight="600" fill="#0f172a">{$dateStr} HRS</text>
    <text x="24" y="98" font-family="Arial, Helvetica, sans-serif" font-size="10" font-weight="bold" fill="#166534">VOUCHER:</text>
    <text x="85" y="98" font-family="Courier, monospace" font-size="10" font-weight="bold" fill="#15803d">{$voucherNumber}</text>
    <line x1="20" y1="105" x2="320" y2="105" stroke="#86efac" stroke-width="0.8" />
    <text x="170" y="118" font-family="Courier, monospace" font-size="9" font-weight="bold" fill="#166534" text-anchor="middle">VERIFICATION HASH: {$refNo}</text>
</svg>
SVG;

        File::put($fullPath, $svg);
        return 'stamps/checking/' . $filename;
    }

    /**
     * Generate Internal Audit Clearance Stamp (Dual-Ring Seal)
     *
     * @param User $auditor
     * @param string $voucherNumber
     * @param string|null $date
     * @return string Relative path to generated stamp
     */
    public function generateAuditStamp(User $auditor, string $voucherNumber, $date = null): string
    {
        $dateStr = $date ? date('d-M-Y H:i', strtotime($date)) : date('d-M-Y H:i');
        $auditorName = strtoupper($auditor->first_name . ' ' . $auditor->last_name);
        $refHash = strtoupper(substr(hash('sha256', 'AUDIT_' . $voucherNumber . $auditor->id . $dateStr), 0, 10));
        $refNo = 'AUD-' . $refHash;

        $dir = public_path('stamps/audit');
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $filename = 'audit_' . md5($voucherNumber . '_' . $auditor->id) . '.svg';
        $fullPath = $dir . '/' . $filename;

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" width="200" height="200">
    <!-- Outer Dual Ring -->
    <circle cx="100" cy="100" r="95" fill="#fff1f2" stroke="#991b1b" stroke-width="3" />
    <circle cx="100" cy="100" r="88" fill="none" stroke="#be123c" stroke-width="1.2" stroke-dasharray="3,3" />
    <circle cx="100" cy="100" r="62" fill="none" stroke="#991b1b" stroke-width="1.5" />
    
    <!-- Top Text Arc path -->
    <path id="topArc" d="M 22,100 A 78,78 0 0,1 178,100" fill="none" />
    <text font-family="Arial, Helvetica, sans-serif" font-size="9" font-weight="900" fill="#881337" letter-spacing="1.2">
        <textPath href="#topArc" startOffset="50%" text-anchor="middle">
            OFFICE OF THE ACCOUNTANT-GENERAL
        </textPath>
    </text>

    <!-- Bottom Text Arc path -->
    <path id="bottomArc" d="M 178,100 A 78,78 0 0,1 22,100" fill="none" />
    <text font-family="Arial, Helvetica, sans-serif" font-size="9" font-weight="900" fill="#881337" letter-spacing="1.2">
        <textPath href="#bottomArc" startOffset="50%" text-anchor="middle">
            INTERNAL AUDIT CLEARANCE
        </textPath>
    </text>

    <!-- Center Content -->
    <text x="100" y="82" font-family="Arial, Helvetica, sans-serif" font-size="11" font-weight="bold" fill="#991b1b" text-anchor="middle">AUDITED &amp;</text>
    <rect x="36" y="87" width="128" height="20" rx="3" fill="#991b1b" />
    <text x="100" y="101" font-family="Arial, Helvetica, sans-serif" font-size="10" font-weight="900" fill="#ffffff" text-anchor="middle" letter-spacing="0.5">PASSED FOR PAYMENT</text>
    <text x="100" y="122" font-family="Arial, Helvetica, sans-serif" font-size="8" font-weight="bold" fill="#1e293b" text-anchor="middle">{$auditorName}</text>
    <text x="100" y="133" font-family="Arial, Helvetica, sans-serif" font-size="7.5" font-weight="600" fill="#475569" text-anchor="middle">{$dateStr}</text>
    <text x="100" y="145" font-family="Courier, monospace" font-size="8" font-weight="bold" fill="#991b1b" text-anchor="middle">{$refNo}</text>
</svg>
SVG;

        File::put($fullPath, $svg);
        return 'stamps/audit/' . $filename;
    }
}
