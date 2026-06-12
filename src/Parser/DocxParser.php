<?php
// src/Parser/DocxParser.php

namespace App\Parser;

/**
 * Native DOCX parser (ZIP-based XML extraction, no external dependencies).
 */
class DocxParser
{
    public function parse(string $filePath): ParsedContent
    {
        if (!file_exists($filePath)) throw new \RuntimeException("File not found: {$filePath}");

        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) throw new \RuntimeException("Cannot open DOCX file");

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xml === false) throw new \RuntimeException("Cannot read document.xml");

        $text = $this->xmlToText($xml);
        return $this->extractContent($text);
    }

    private function xmlToText(string $xml): string
    {
        // Replace paragraph breaks
        $xml = preg_replace('/<w:p[ >]/', "\n<w:p>", $xml);
        // Replace explicit line breaks
        $xml = str_replace('<w:br/>', "\n", $xml);
        // Strip remaining XML
        $text = strip_tags($xml);
        // Decode XML entities
        $text = html_entity_decode($text, ENT_XML1, 'UTF-8');
        return $text;
    }

    private function extractContent(string $text): ParsedContent
    {
        $lines    = array_filter(array_map('trim', explode("\n", $text)));
        $lines    = array_values($lines);
        $content  = new ParsedContent();

        $mode     = 'title';
        $title    = '';
        $summary  = [];
        $questions = [];
        $vocabulary = [];
        $phrases   = [];
        $idioms    = [];
        $collocations = [];
        $quotes    = [];

        $questionPatterns  = ['/^\d+[.)]\s+.+\?/', '/^Q\d*[:.]\s+.+\?/i', '/^•\s*.+\?/'];
        $vocabPatterns     = ['/^vocabulary[:\s]/i', '/^new words[:\s]/i', '/^word list[:\s]/i'];
        $phrasePatterns    = ['/^phrases?[:\s]/i', '/^useful phrases?[:\s]/i'];
        $idiomPatterns     = ['/^idioms?[:\s]/i', '/^expressions?[:\s]/i'];
        $collocationPatterns = ['/^collocations?[:\s]/i'];
        $quotePatterns     = ['/^[""].*[""]/', '/^quote[:\s]/i'];
        $sectionBreakers   = array_merge($vocabPatterns, $phrasePatterns, $idiomPatterns, $collocationPatterns);

        foreach ($lines as $i => $line) {
            // Detect section headers
            foreach ($vocabPatterns as $p) {
                if (preg_match($p, $line)) { $mode = 'vocab'; continue 2; }
            }
            foreach ($phrasePatterns as $p) {
                if (preg_match($p, $line)) { $mode = 'phrases'; continue 2; }
            }
            foreach ($idiomPatterns as $p) {
                if (preg_match($p, $line)) { $mode = 'idioms'; continue 2; }
            }
            foreach ($collocationPatterns as $p) {
                if (preg_match($p, $line)) { $mode = 'collocations'; continue 2; }
            }
            if (stripos($line, 'discussion question') !== false ||
                stripos($line, 'warm-up') !== false) {
                $mode = 'questions'; continue;
            }

            // Grab the first non-empty line as title
            if (empty($title) && strlen($line) < 100) {
                $title = $line; continue;
            }

            // Detect questions
            $isQuestion = false;
            foreach ($questionPatterns as $p) {
                if (preg_match($p, $line)) { $isQuestion = true; break; }
            }

            if ($isQuestion || ($mode === 'questions' && str_ends_with($line, '?'))) {
                $questions[] = [
                    'text'  => preg_replace('/^\d+[.)]\s+|^Q\d*[:.]\s+|^•\s*/', '', $line),
                    'level' => '',
                    'type'  => 'open',
                ];
                $mode = 'questions';
            } elseif ($mode === 'vocab') {
                if (strlen($line) > 2 && strlen($line) < 80) {
                    $vocabulary[] = $this->parseVocabLine($line);
                }
            } elseif ($mode === 'phrases') {
                if (strlen($line) > 2) $phrases[] = $line;
            } elseif ($mode === 'idioms') {
                if (strlen($line) > 2) $idioms[] = $line;
            } elseif ($mode === 'collocations') {
                if (strlen($line) > 2) $collocations[] = $line;
            } elseif ($mode === 'title') {
                $summary[] = $line;
            }
        }

        $content->title        = sanitize($title ?: 'Untitled Topic');
        $content->summary      = sanitize(implode(' ', array_slice($summary, 0, 3)));
        $content->questions    = $questions;
        $content->vocabulary   = $vocabulary;
        $content->phrases      = $phrases;
        $content->idioms       = $idioms;
        $content->collocations = $collocations;
        $content->quotes       = $quotes;

        return $content;
    }

    private function parseVocabLine(string $line): array
    {
        // Try "word - definition" or "word: definition"
        if (preg_match('/^([^:\-–—]+)[:\-–—]\s*(.+)/', $line, $m)) {
            return [
                'word'        => trim($m[1]),
                'definition'  => trim($m[2]),
                'pronunciation' => '',
                'examples'    => [],
                'collocations' => [],
            ];
        }
        return [
            'word'        => $line,
            'definition'  => '',
            'pronunciation' => '',
            'examples'    => [],
            'collocations' => [],
        ];
    }
}
