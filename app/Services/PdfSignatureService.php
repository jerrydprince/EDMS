<?php

namespace App\Services;

use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Log;

class PdfSignatureService
{
    /**
     * Stamps a signature image onto the last page of a PDF document.
     *
     * @param string $pdfPath The absolute path to the PDF document.
     * @param string $signatureImagePath The absolute path to the signature image (must be PNG or JPEG).
     * @param string $outputPath The absolute path to save the modified PDF.
     * @return bool True on success, false on failure.
     */
    public function stampSignatureOnPdf($pdfPath, $signatureImagePath, $outputPath = null)
    {
        if (!$outputPath) {
            $outputPath = $pdfPath;
        }

        if (!file_exists($pdfPath)) {
            Log::error("PDF not found for signing: " . $pdfPath);
            return false;
        }

        if (!file_exists($signatureImagePath)) {
            Log::error("Signature image not found: " . $signatureImagePath);
            return false;
        }

        try {
            // Determine image type for FPDF
            $ext = strtolower(pathinfo($signatureImagePath, PATHINFO_EXTENSION));
            if (!in_array($ext, ['png', 'jpg', 'jpeg'])) {
                Log::error("Unsupported signature image format: " . $ext);
                return false;
            }

            $pdf = new Fpdi();
            
            // Get the total number of pages
            $pageCount = $pdf->setSourceFile($pdfPath);

            // Iterate over all pages and import them
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                
                // Get page size
                $size = $pdf->getTemplateSize($templateId);
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                
                // Use the imported page
                $pdf->useTemplate($templateId);

                // If it's the last page, append the signature
                if ($pageNo == $pageCount) {
                    // Coordinates: We will place it at the bottom right.
                    // Assuming a standard margin of 15 from bottom right.
                    $sigWidth = 40; // width in mm
                    $sigHeight = 20; // height in mm
                    $x = $size['width'] - $sigWidth - 15;
                    $y = $size['height'] - $sigHeight - 15;

                    $pdf->Image($signatureImagePath, $x, $y, $sigWidth, $sigHeight);
                    
                    // Add a small text indicating who signed it (optional)
                    $pdf->SetFont('Arial', 'I', 8);
                    $pdf->SetTextColor(100, 100, 100);
                    $pdf->SetXY($x, $y + $sigHeight + 2);
                    $pdf->Cell($sigWidth, 5, 'Digitally Signed', 0, 0, 'C');
                }
            }

            $pdf->Output($outputPath, 'F');
            return true;

        } catch (\Exception $e) {
            Log::error("Error stamping signature: " . $e->getMessage());
            return false;
        }
    }
}
