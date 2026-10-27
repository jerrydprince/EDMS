<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use Smalot\PdfParser\Parser as PdfParser;

class TemplateImportService
{
    /**
     * Convert an uploaded Word (.docx, .doc) or PDF document into editable HTML with auto-detected tokens.
     *
     * @param UploadedFile $file
     * @return array
     */
    public function convertFile(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $title = ucwords(str_replace(['_', '-'], ' ', $originalName));

        $rawHtml = '';
        $detectedType = 'memo';

        if (in_array($extension, ['docx', 'doc'])) {
            $rawHtml = $this->convertWordToHtml($file->getRealPath());
        } elseif ($extension === 'pdf') {
            $rawHtml = $this->convertPdfToHtml($file->getRealPath());
        } else {
            throw new \InvalidArgumentException("Unsupported file type: {$extension}. Please upload a Word (.docx) or PDF file.");
        }

        // Clean & normalize HTML
        $cleanedHtml = $this->cleanHtml($rawHtml);

        // Normalize placeholders and collect list of variable tokens
        [$normalizedHtml, $placeholders] = $this->extractAndNormalizePlaceholders($cleanedHtml);

        // Guess document type from content
        $lowerHtml = strtolower($normalizedHtml);
        if (str_contains($lowerHtml, 'query') || str_contains($lowerHtml, 'misconduct')) {
            $detectedType = 'query';
        } elseif (str_contains($lowerHtml, 'letter') || str_contains($lowerHtml, 'dear sir') || str_contains($lowerHtml, 'yours faithfully')) {
            $detectedType = 'letter';
        } elseif (str_contains($lowerHtml, 'transmittal') || str_contains($lowerHtml, 'dispatch')) {
            $detectedType = 'transmittal';
        } elseif (str_contains($lowerHtml, 'voucher') || str_contains($lowerHtml, 'warrant') || str_contains($lowerHtml, 'payment')) {
            $detectedType = 'voucher';
        }

        return [
            'title' => $title,
            'type' => $detectedType,
            'content_html' => $normalizedHtml,
            'placeholders' => $placeholders,
        ];
    }

    /**
     * Convert Word document (.docx, .doc) to structured HTML with automatic fallbacks.
     */
    protected function convertWordToHtml(string $filePath): string
    {
        // 1. First attempt: Try PhpWord for full HTML rendering (preserves tables and inline styling)
        try {
            $phpWord = WordIOFactory::load($filePath);
            $htmlWriter = WordIOFactory::createWriter($phpWord, 'HTML');

            ob_start();
            $htmlWriter->save('php://output');
            $html = ob_get_clean();

            if (!empty(trim(strip_tags($html)))) {
                return $html;
            }
        } catch (\Throwable $e) {
            \Log::info("TemplateImportService: Standard PhpWord loader failed, attempting fallbacks: " . $e->getMessage());
        }

        // 2. Second attempt: Direct DOCX Zip XML parsing (fast, 100% resilient for all .docx files)
        $docxText = $this->extractFromDocxZip($filePath);
        if (!empty(trim($docxText))) {
            return $this->formatPlainTextToHtml($docxText);
        }

        // 3. Third attempt: Binary DOC (Word 97-2003) or RTF text stream extractor
        $docText = $this->extractFromBinaryDoc($filePath);
        if (!empty(trim($docText))) {
            return $this->formatPlainTextToHtml($docText);
        }

        throw new \RuntimeException("Unable to extract text from Word document. Please ensure it is a valid .docx or .doc file with readable text.");
    }

    /**
     * Extract document text directly from DOCX WordprocessingML XML archive.
     */
    protected function extractFromDocxZip(string $filePath): string
    {
        if (!class_exists('ZipArchive')) {
            return '';
        }

        $zip = new \ZipArchive();
        if ($zip->open($filePath) === true) {
            $xmlContent = $zip->getFromName('word/document.xml');
            $zip->close();

            if ($xmlContent) {
                // Preserve paragraph boundaries and line breaks
                $xmlContent = preg_replace('/<\/w:p>/', "\n\n", $xmlContent);
                $xmlContent = preg_replace('/<w:br[^>]*>/', "\n", $xmlContent);
                $xmlContent = preg_replace('/<w:tab[^>]*>/', "    ", $xmlContent);
                $text = strip_tags($xmlContent);
                return html_entity_decode($text, ENT_QUOTES, 'UTF-8');
            }
        }

        return '';
    }

    /**
     * Extract readable text runs from legacy binary Word (.doc) or RTF documents.
     */
    protected function extractFromBinaryDoc(string $filePath): string
    {
        $content = @file_get_contents($filePath);
        if ($content === false || strlen($content) === 0) {
            return '';
        }

        // Check if RTF formatted
        if (str_starts_with($content, '{\\rtf')) {
            $rtf = preg_replace('/\{\\\\rtf[0-9]*.*?\}/s', '', $content);
            $rtf = preg_replace('/\\\\[a-z0-9]+ ?/i', '', $rtf);
            $rtf = str_replace(['{', '}'], '', $rtf);
            return trim($rtf);
        }

        // Binary CFBF .doc text extraction
        $text = '';
        if (preg_match_all('/[\x20-\x7E\t\r\n]{3,}/', $content, $matches)) {
            $cleanLines = [];
            foreach ($matches[0] as $match) {
                $match = trim($match);
                if (strlen($match) < 3) continue;
                if (preg_match('/^(bjbj|jbjb|CompObj|WordDocument|SummaryInformation|DocumentSummaryInformation|Table|Font Table|Styles)/i', $match)) {
                    continue;
                }
                if (preg_match('/^[a-zA-Z0-9_\-\.\:\/\\\\]+$/', $match) && strlen($match) > 60 && !str_contains($match, ' ')) {
                    continue;
                }
                $cleanLines[] = $match;
            }
            $text = implode("\n\n", $cleanLines);
        }

        // Try UTF-16LE encoding if ASCII yielded sparse content
        if (strlen(trim($text)) < 50 && function_exists('mb_convert_encoding')) {
            $utf16 = @mb_convert_encoding($content, 'UTF-8', 'UTF-16LE');
            if ($utf16 && preg_match_all('/[\x20-\x7E\t\r\n]{4,}/', $utf16, $matches16)) {
                $cleanLines16 = [];
                foreach ($matches16[0] as $m) {
                    $m = trim($m);
                    if (strlen($m) >= 4 && !preg_match('/^(bjbj|WordDocument|SummaryInformation)/i', $m)) {
                        $cleanLines16[] = $m;
                    }
                }
                if (count($cleanLines16) > 0) {
                    $text = implode("\n\n", $cleanLines16);
                }
            }
        }

        return trim($text);
    }

    /**
     * Convert structured plain text blocks into styled HTML paragraphs and headings.
     */
    protected function formatPlainTextToHtml(string $text): string
    {
        $lines = preg_split('/\r\n|\r|\n/', $text);
        $htmlParts = [];
        $buffer = '';

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (empty($trimmed)) {
                if (!empty($buffer)) {
                    $htmlParts[] = $this->formatParagraphOrHeading($buffer);
                    $buffer = '';
                }
                continue;
            }

            if (!empty($buffer)) {
                $buffer .= ' ' . $trimmed;
            } else {
                $buffer = $trimmed;
            }
        }

        if (!empty($buffer)) {
            $htmlParts[] = $this->formatParagraphOrHeading($buffer);
        }

        return '<div style="font-family: Arial, sans-serif; line-height: 1.6; color: #1e293b; max-width: 800px; margin: 0 auto; padding: 25px;">' . implode("\n", $htmlParts) . '</div>';
    }

    /**
     * Convert PDF document to structured HTML via Smalot PdfParser.
     */
    protected function convertPdfToHtml(string $filePath): string
    {
        try {
            $parser = new PdfParser();
            $pdf = $parser->parseFile($filePath);
            $text = $pdf->getText();

            if (empty(trim($text))) {
                throw new \RuntimeException("PDF does not contain extractable text. Scanned image-only PDFs must have an embedded text layer or OCR.");
            }

            return $this->formatPlainTextToHtml($text);
        } catch (\Throwable $e) {
            \Log::error("TemplateImportService PDF conversion failed: " . $e->getMessage());
            throw new \RuntimeException("Unable to parse PDF document: " . $e->getMessage());
        }
    }

    /**
     * Determine if a text block is a heading or regular paragraph.
     */
    protected function formatParagraphOrHeading(string $text): string
    {
        $escaped = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        // Check if short and all uppercase -> make heading
        if (strlen($text) < 80 && strtoupper($text) === $text && !preg_match('/[0-9]{4,}/', $text)) {
            return "<h3 style=\"margin: 15px 0 8px; font-weight: bold; text-transform: uppercase; color: #0f172a;\">{$escaped}</h3>";
        }

        return "<p style=\"margin-bottom: 12px;\">{$escaped}</p>";
    }

    /**
     * Clean up and normalize generated HTML markup from Word/PDF for full WYSIWYG document editors.
     */
    protected function cleanHtml(string $html): string
    {
        // Extract body if full HTML document was generated
        if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $html, $matches)) {
            $html = $matches[1];
        }

        // Remove script, link, and meta tags
        $html = preg_replace('/<(script|style|link|meta)\b[^>]*>.*?<\/\1>/is', '', $html);

        // Strip outer wrapper divs (like <div style='page: page1'> or <div style="font-family: ...">)
        $html = preg_replace('/^\s*<div[^>]*page:\s*page\d+[^>]*>/i', '', $html);
        $html = preg_replace('/^\s*<div[^>]*font-family:[^>]*>/i', '', $html);
        $html = preg_replace('/<\/div>\s*<\/div>\s*$/i', '', $html);
        $html = preg_replace('/<\/div>\s*$/i', '', $html);

        // Normalize text alignments for Quill / PrimeVue Editor
        $html = preg_replace_callback('/<p([^>]*)style="([^"]*text-align:\s*center[^"]*)"([^>]*)>/i', function ($m) {
            return '<p' . $m[1] . 'class="ql-align-center" style="' . $m[2] . '"' . $m[3] . '>';
        }, $html);

        $html = preg_replace_callback('/<p([^>]*)style="([^"]*text-align:\s*right[^"]*)"([^>]*)>/i', function ($m) {
            return '<p' . $m[1] . 'class="ql-align-right" style="' . $m[2] . '"' . $m[3] . '>';
        }, $html);

        $html = preg_replace_callback('/<p([^>]*)style="([^"]*text-align:\s*justify[^"]*)"([^>]*)>/i', function ($m) {
            return '<p' . $m[1] . 'class="ql-align-justify" style="' . $m[2] . '"' . $m[3] . '>';
        }, $html);

        // Convert font-weight: bold spans into semantic <strong> tags
        $html = preg_replace('/<span\b[^>]*style="[^"]*font-weight:\s*bold[^"]*"[^>]*>(.*?)<\/span>/is', '<strong>$1</strong>', $html);

        // Convert underline spans into <u> tags
        $html = preg_replace('/<span\b[^>]*style="[^"]*text-decoration:\s*underline[^"]*"[^>]*>(.*?)<\/span>/is', '<u>$1</u>', $html);

        // Convert italic spans into <em> tags
        $html = preg_replace('/<span\b[^>]*style="[^"]*font-style:\s*italic[^"]*"[^>]*>(.*?)<\/span>/is', '<em>$1</em>', $html);

        // Replace empty non-breaking paragraphs with standard breaks
        $html = preg_replace('/<p>&nbsp;<\/p>/i', '<p><br></p>', $html);

        // Format tables with clean civil service styling
        $html = preg_replace('/<table\b[^>]*>/i', '<table style="width: 100%; border-collapse: collapse; margin: 14px 0; font-size: 13px;">', $html);
        $html = preg_replace('/<td\b[^>]*>/i', '<td style="border: 1px solid #cbd5e1; padding: 8px 12px; vertical-align: top;">', $html);
        $html = preg_replace('/<th\b[^>]*>/i', '<th style="border: 1px solid #94a3b8; background-color: #f1f5f9; padding: 8px 12px; font-weight: bold; text-align: left;">', $html);

        return trim($html);
    }

    /**
     * Extract and normalize placeholders into {{ placeholder_name }}.
     */
    protected function extractAndNormalizePlaceholders(string $html): array
    {
        $placeholders = [];

        // 1. Convert bracket tokens e.g. [RECIPIENT_NAME] -> {{ recipient_name }}
        $html = preg_replace_callback('/\[([A-Z0-9_]{3,30})\]/', function ($matches) use (&$placeholders) {
            $cleanKey = strtolower($matches[1]);
            $placeholders[] = $cleanKey;
            return '{{ ' . $cleanKey . ' }}';
        }, $html);

        // 2. Convert chevron tokens e.g. <<DATE>> -> {{ date }}
        $html = preg_replace_callback('/<<\s*([a-zA-Z0-9_]{2,30})\s*>>/', function ($matches) use (&$placeholders) {
            $cleanKey = strtolower($matches[1]);
            $placeholders[] = $cleanKey;
            return '{{ ' . $cleanKey . ' }}';
        }, $html);

        // 3. Find standard {{ token }}
        if (preg_match_all('/\{\{\s*([a-zA-Z0-9_\-\.]+)\s*\}\}/', $html, $matches)) {
            foreach ($matches[1] as $key) {
                $placeholders[] = strtolower(trim($key));
            }
        }

        return [$html, array_values(array_unique($placeholders))];
    }
}
