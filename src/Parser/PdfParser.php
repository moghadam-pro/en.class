<?php
// src/Parser/PdfParser.php

namespace App\Parser;

/**
 * PDF text extraction via pdftotext (poppler-utils) or fallback stream parsing.
 */
class PdfParser
{
    public function parse(string $filePath): ParsedContent
    {
        if (!file_exists($filePath)) throw new \RuntimeException("File not found: {$filePath}");

        // Try pdftotext (requires poppler-utils installed on server)
        $text = $this->pdfToText($filePath);

        // Fallback: raw binary stream search for text objects
        if (empty(trim($text))) {
            $text = $this->extractTextFromStream($filePath);
        }

        $docx = new DocxParser();
        // Reuse the DocxParser text-to-content extractor via a private method workaround
        return $this->parseText($text);
    }

    private function pdfToText(string $filePath): string
    {
        if (!shell_exec('which pdftotext 2>/dev/null')) return '';
        $escaped = escapeshellarg($filePath);
        $output  = shell_exec("pdftotext -enc UTF-8 {$escaped} - 2>/dev/null");
        return (string) $output;
    }

    private function extractTextFromStream(string $filePath): string
    {
        $content = file_get_contents($filePath);
        $text    = '';
        // Extract text between BT...ET markers
        preg_match_all('/BT\s+(.*?)\s+ET/s', $content, $matches);
        foreach ($matches[1] as $block) {
            preg_match_all('/\(([^)]+)\)/', $block, $strings);
            foreach ($strings[1] as $str) {
                $text .= $str . ' ';
            }
            $text .= "\n";
        }
        return $text;
    }

    private function parseText(string $text): ParsedContent
    {
        // Delegate to a simple line-based parser (reuse DocxParser logic)
        $content   = new ParsedContent();
        $lines     = array_filter(array_map('trim', explode("\n", $text)));
        $lines     = array_values($lines);
        $title     = '';
        $questions = [];
        $vocab     = [];

        foreach ($lines as $line) {
            if (empty($title) && strlen($line) < 100) { $title = $line; continue; }
            if (preg_match('/^\d+[.)]\s+.+\?/', $line) || str_ends_with(trim($line), '?')) {
                $questions[] = [
                    'text'  => preg_replace('/^\d+[.)]\s+/', '', $line),
                    'level' => '',
                    'type'  => 'open',
                ];
            } elseif (preg_match('/^[a-z][\w\s]+$/i', $line) && strlen($line) < 50) {
                $vocab[] = ['word' => $line, 'definition' => '', 'pronunciation' => '', 'examples' => [], 'collocations' => []];
            }
        }

        $content->title     = sanitize($title ?: 'Untitled Topic');
        $content->summary   = '';
        $content->questions = $questions;
        $content->vocabulary= $vocab;
        return $content;
    }
}
