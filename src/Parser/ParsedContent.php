<?php
// src/Parser/ParsedContent.php

namespace App\Parser;

class ParsedContent
{
    public string $title       = '';
    public string $summary     = '';
    public array  $questions   = [];
    public array  $vocabulary  = [];
    public array  $phrases     = [];
    public array  $idioms      = [];
    public array  $collocations = [];
    public array  $quotes      = [];

    /**
     * Convert to the standard topic JSON structure
     */
    public function toTopicArray(string $level = 'B1'): array
    {
        $slug = slugify($this->title);
        return [
            'slug'          => $slug,
            'title'         => $this->title,
            'cover'         => '',
            'summary'       => $this->summary,
            'level'         => $level,
            'tags'          => $this->extractTags(),
            'questions'     => $this->questions,
            'vocabulary'    => $this->vocabulary,
            'collocations'  => $this->collocations,
            'phrases'       => $this->phrases,
            'idioms'        => $this->idioms,
            'quotes'        => $this->quotes,
            'teacher_notes' => '',
            'games'         => $this->defaultGames(),
            'roleplay'      => [],
            'created_at'    => date('c'),
        ];
    }

    private function extractTags(): array
    {
        $words = preg_split('/\s+/', strtolower($this->title));
        return array_filter($words, fn($w) => strlen($w) > 3);
    }

    private function defaultGames(): array
    {
        return [
            [
                'name'        => 'Hot Seat',
                'description' => 'One student sits with their back to the board. Others describe the vocabulary word without saying it.',
                'players'     => '4+',
                'time'        => '10 min',
            ],
            [
                'name'        => 'Discussion Bingo',
                'description' => 'Create bingo cards with vocabulary. Students cross off words as they use them in discussion.',
                'players'     => '4+',
                'time'        => '20 min',
            ],
        ];
    }
}
