<?php

namespace App\Services;

use App\Models\DigitalStamp;
use Carbon\Carbon;

class DigitalStampService
{
    /**
     * Nigerian National Coat of Arms SVG Vector (Inner Core Symbol)
     */
    public static function getCoatOfArmsSvg(): string
    {
        return '<g transform="translate(68, 52) scale(0.44)">
            <!-- Shield Background -->
            <path d="M50,15 L78,15 Q95,45 80,85 Q50,110 50,110 Q50,110 20,85 Q5,45 22,15 Z" fill="#000000" stroke="#d97706" stroke-width="2.5" />
            <!-- Silver Y-Shaped Pall (Rivers Niger and Benue) -->
            <path d="M22,18 L42,48 L42,95 L58,95 L58,48 L78,18 L63,18 L50,38 L37,18 Z" fill="#ffffff" />
            <!-- Two Supporting White Horses -->
            <!-- Left Horse -->
            <path d="M12,45 Q-2,30 2,12 Q14,8 20,25 Q16,55 8,75 L16,85 Q10,95 2,75 Z" fill="#ffffff" stroke="#d97706" stroke-width="1.5" />
            <!-- Right Horse -->
            <path d="M88,45 Q102,30 98,12 Q86,8 80,25 Q84,55 92,75 L84,85 Q90,95 98,75 Z" fill="#ffffff" stroke="#d97706" stroke-width="1.5" />
            <!-- Red Eagle Crest on Wreath -->
            <path d="M42,12 Q50,-4 58,12 Q54,16 50,14 Q46,16 42,12 Z" fill="#dc2626" />
            <!-- Motto Banner / Green-White-Green Base -->
            <rect x="25" y="105" width="50" height="6" rx="3" fill="#15803d" />
        </g>';
    }

    /**
     * Generate an authentic civil service circular SVG stamp programmatically.
     *
     * @param string $designationTitle Upper/Center Title (e.g., 'DIRECTOR ADMINISTRATION')
     * @param string|null $date Custom date or null for today's formatted civil service date
     * @param string $inkColor Hex color for ink (e.g., '#1e3a8a' Treasury Blue)
     * @param string $departmentName Name of Department/Office
     * @return string Valid SVG XML string
     */
    public static function generateSvgStamp(
        string $designationTitle = 'DIRECTOR ADMINISTRATION',
        ?string $date = null,
        string $inkColor = '#1e3a8a',
        string $departmentName = 'OFFICE OF THE ACCOUNTANT-GENERAL OF THE FEDERATION'
    ): string {
        $formattedDate = $date ? Carbon::parse($date)->format('d M Y') : Carbon::now()->format('d M Y');
        $formattedDateUpper = strtoupper($formattedDate);
        $titleUpper = strtoupper(trim($designationTitle));
        $uniqueId = 'stamp_' . substr(md5($designationTitle . $inkColor . microtime()), 0, 8);

        // Circular dimensions
        $size = 200;
        $center = 100;
        $outerRadius = 94;
        $innerRadius = 88;
        $subRadius = 78;

        $coatOfArms = self::getCoatOfArmsSvg();

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$size} {$size}" width="{$size}" height="{$size}" style="user-select: none; font-family: 'Arial Black', Arial, sans-serif;">
    <defs>
        <!-- Upper Arc Path (Clockwise) -->
        <path id="{$uniqueId}_upper" d="M 16,100 A 84,84 0 1,1 184,100" fill="none" />
        <!-- Lower Arc Path (Clockwise) -->
        <path id="{$uniqueId}_lower" d="M 184,100 A 84,84 0 0,1 16,100" fill="none" />
        
        <!-- Filter for subtle mechanical ink bleeding -->
        <filter id="{$uniqueId}_ink" x="-5%" y="-5%" width="110%" height="110%">
            <feTurbulence type="fractalNoise" baseFrequency="0.04" numOctaves="2" result="noise" />
            <feDisplacementMap in="SourceGraphic" in2="noise" scale="1.2" xChannelSelector="R" yChannelSelector="G" />
        </filter>
    </defs>

    <g filter="url(#{$uniqueId}_ink)" stroke="{$inkColor}" fill="{$inkColor}">
        <!-- Outer Concentric Thick Circle -->
        <circle cx="{$center}" cy="{$center}" r="{$outerRadius}" fill="none" stroke-width="3.5" />
        
        <!-- Inner Beaded / Dashed Ring -->
        <circle cx="{$center}" cy="{$center}" r="{$innerRadius}" fill="none" stroke-width="1.2" stroke-dasharray="3,2" />
        
        <!-- Inner Enclosing Solid Circle -->
        <circle cx="{$center}" cy="{$center}" r="{$subRadius}" fill="none" stroke-width="1.5" />

        <!-- Upper Curved Text -->
        <text font-size="9" font-weight="900" letter-spacing="1.2" fill="{$inkColor}" stroke="none">
            <textPath href="#{$uniqueId}_upper" startOffset="50%" text-anchor="middle">
                ★ {$departmentName} ★
            </textPath>
        </text>

        <!-- Lower Curved Text -->
        <text font-size="8.5" font-weight="800" letter-spacing="1.4" fill="{$inkColor}" stroke="none">
            <textPath href="#{$uniqueId}_lower" startOffset="50%" text-anchor="middle">
                TREASURY HOUSE, ABUJA • FEDERAL REPUBLIC OF NIGERIA
            </textPath>
        </text>

        <!-- Coat of Arms Center Emblem -->
        {$coatOfArms}

        <!-- Dynamic Date Window Banner -->
        <g transform="translate(35, 114)">
            <rect x="0" y="0" width="130" height="15" rx="3" fill="none" stroke="{$inkColor}" stroke-width="1.2" stroke-dasharray="2,1" />
            <text x="65" y="11" font-size="10" font-weight="900" text-anchor="middle" letter-spacing="1.5" fill="{$inkColor}" stroke="none">
                ★ {$formattedDateUpper} ★
            </text>
        </g>

        <!-- Designation Text -->
        <text x="{$center}" y="142" font-size="9.5" font-weight="900" text-anchor="middle" letter-spacing="0.8" fill="{$inkColor}" stroke="none">
            {$titleUpper}
        </text>

        <!-- Verification Seal Star -->
        <text x="{$center}" y="153" font-size="7" font-weight="bold" text-anchor="middle" fill="{$inkColor}" stroke="none" opacity="0.9">
            OFFICIAL DIGITAL VERIFICATION SEAL
        </text>
    </g>
</svg>
SVG;
    }

    /**
     * Resolve the active digital stamp for a user or directorate.
     */
    public static function getActiveStampFor(string $designationTitle, ?int $userId = null): array
    {
        $stampRecord = DigitalStamp::where('designation_title', $designationTitle)
            ->when($userId, fn($q) => $q->orWhere('user_id', $userId))
            ->where('is_default', true)
            ->first();

        if ($stampRecord && $stampRecord->stamp_type === 'uploaded_png' && $stampRecord->custom_stamp_path) {
            return [
                'type' => 'uploaded_png',
                'url' => asset($stampRecord->custom_stamp_path),
                'ink_color' => $stampRecord->ink_color,
            ];
        }

        $inkColor = $stampRecord?->ink_color ?? '#1e3a8a';
        return [
            'type' => 'svg_generated',
            'svg' => self::generateSvgStamp($designationTitle, null, $inkColor),
            'ink_color' => $inkColor,
        ];
    }
}
