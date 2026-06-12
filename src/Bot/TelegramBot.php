<?php
// src/Bot/TelegramBot.php

namespace App\Bot;

use App\Parser\DocxParser;
use App\Parser\PdfParser;
use App\Topic\TopicRepository;

class TelegramBot
{
    private string $token;
    private array  $admins;
    private string $apiBase;

    public function __construct()
    {
        $this->token   = config('telegram.token');
        $this->admins  = config('telegram.admins');
        $this->apiBase = "https://api.telegram.org/bot{$this->token}";
    }

    // ── Webhook entry point ───────────────────────────────────────────────────

    public function handleWebhook(): void
    {
        $input  = file_get_contents('php://input');
        $secret = config('telegram.webhook_secret');

        // Validate secret token header
        if ($secret) {
            $header = $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ?? '';
            if (!hash_equals($secret, $header)) {
                http_response_code(403);
                exit;
            }
        }

        $update = json_decode($input, true);
        if (!$update) exit;

        $this->processUpdate($update);
    }

    private function processUpdate(array $update): void
    {
        if (isset($update['message'])) {
            $this->handleMessage($update['message']);
        } elseif (isset($update['callback_query'])) {
            $this->handleCallback($update['callback_query']);
        }
    }

    private function handleMessage(array $message): void
    {
        $chatId  = $message['chat']['id'];
        $userId  = $message['from']['id'];
        $text    = trim($message['text'] ?? '');
        $document = $message['document'] ?? null;

        // Access control
        if (!$this->isAdmin($userId)) {
            $this->send($chatId, "⛔ Access denied. You are not authorized to use this bot.");
            return;
        }

        // Handle file uploads
        if ($document) {
            $this->handleFileUpload($chatId, $document, $message);
            return;
        }

        // Handle commands
        $command = explode(' ', $text)[0] ?? '';
        switch ($command) {
            case '/start':
            case '/help':
                $this->cmdHelp($chatId);
                break;
            case '/list':
                $this->cmdList($chatId);
                break;
            case '/stats':
                $this->cmdStats($chatId);
                break;
            case '/rebuild':
                $this->cmdRebuild($chatId);
                break;
            case '/delete':
                $slug = trim(substr($text, 7));
                $this->cmdDelete($chatId, $slug);
                break;
            default:
                $this->send($chatId, "❓ Unknown command. Type /help for available commands.");
        }
    }

    private function handleCallback(array $query): void
    {
        $chatId = $query['message']['chat']['id'];
        $data   = $query['data'] ?? '';
        $this->answerCallback($query['id']);

        if (str_starts_with($data, 'confirm_delete:')) {
            $slug = substr($data, 15);
            $repo = new TopicRepository();
            if ($repo->delete($slug)) {
                $this->send($chatId, "🗑 Topic *{$slug}* deleted successfully.");
            } else {
                $this->send($chatId, "❌ Could not delete topic *{$slug}*.");
            }
        }
    }

    // ── File handling ─────────────────────────────────────────────────────────

    private function handleFileUpload(int $chatId, array $document, array $message): void
    {
        $mime    = $document['mime_type'] ?? '';
        $name    = $document['file_name'] ?? '';
        $fileId  = $document['file_id'] ?? '';

        $allowed = [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/msword',
            'application/pdf',
        ];

        if (!in_array($mime, $allowed) &&
            !preg_match('/\.(docx|doc|pdf)$/i', $name)) {
            $this->send($chatId, "⚠️ Only DOCX and PDF files are accepted.");
            return;
        }

        $this->send($chatId, "📥 Downloading and processing *{$name}*...");

        try {
            // Get file path from Telegram
            $fileInfo = $this->getFile($fileId);
            if (!$fileInfo) throw new \RuntimeException("Could not get file info from Telegram.");

            $fileUrl    = "https://api.telegram.org/file/bot{$this->token}/{$fileInfo['file_path']}";
            $uploadDir  = ROOT_PATH . '/uploads';
            $localName  = uniqid('upload_') . '_' . basename($name);
            $localPath  = $uploadDir . '/' . $localName;

            // Download
            $fileData = $this->downloadFile($fileUrl);
            if ($fileData === false) throw new \RuntimeException("Download failed.");
            file_put_contents($localPath, $fileData);

            // Determine level from caption or default B1
            $caption  = trim($message['caption'] ?? '');
            $level    = $this->extractLevelFromCaption($caption) ?: 'B1';

            // Parse
            $parsed = $this->parseFile($localPath, $mime);
            $topic  = $parsed->toTopicArray($level);

            // Check for slug collision
            $repo = new TopicRepository();
            if ($repo->findBySlug($topic['slug'])) {
                $topic['slug'] .= '-' . date('Ymd');
            }

            // Save
            $repo->save($topic);

            // Cleanup
            @unlink($localPath);

            $url = config('app.url') . '/topic/' . $topic['slug'];
            $msg = "✅ *Topic Published*\n\n"
                 . "📖 Title: *{$topic['title']}*\n"
                 . "❓ Questions: " . count($topic['questions']) . "\n"
                 . "📚 Vocabulary: " . count($topic['vocabulary']) . "\n"
                 . "🎯 Level: {$topic['level']}\n\n"
                 . "🔗 URL: {$url}";

            $this->send($chatId, $msg, ['parse_mode' => 'Markdown']);

        } catch (\Throwable $e) {
            $this->logError($e->getMessage());
            $this->send($chatId, "❌ Error processing file: " . $e->getMessage());
        }
    }

    private function parseFile(string $path, string $mime): \App\Parser\ParsedContent
    {
        if (str_contains($mime, 'pdf') || str_ends_with($path, '.pdf')) {
            return (new PdfParser())->parse($path);
        }
        return (new DocxParser())->parse($path);
    }

    // ── Commands ──────────────────────────────────────────────────────────────

    private function cmdHelp(int $chatId): void
    {
        $msg = "🤖 *Free Discussion Class Bot*\n\n"
             . "📤 *Upload*: Send a DOCX or PDF file to publish a topic.\n\n"
             . "*Commands:*\n"
             . "/list — List all published topics\n"
             . "/stats — Platform statistics\n"
             . "/rebuild — Rebuild the topic index\n"
             . "/delete [slug] — Delete a topic\n"
             . "/help — Show this message";
        $this->send($chatId, $msg, ['parse_mode' => 'Markdown']);
    }

    private function cmdList(int $chatId): void
    {
        $repo   = new TopicRepository();
        $result = $repo->all([], 1, 20);
        $items  = $result['items'];

        if (empty($items)) {
            $this->send($chatId, "📭 No topics published yet.");
            return;
        }

        $lines = ["📚 *Published Topics* ({$result['total']} total)\n"];
        foreach ($items as $t) {
            $lines[] = "• [{$t['title']}](" . config('app.url') . "/topic/{$t['slug']}) `{$t['level']}`";
        }
        if ($result['total'] > 20) $lines[] = "\n_Showing first 20 topics_";

        $this->send($chatId, implode("\n", $lines), ['parse_mode' => 'Markdown', 'disable_web_page_preview' => true]);
    }

    private function cmdStats(int $chatId): void
    {
        $repo  = new TopicRepository();
        $stats = $repo->stats();

        $msg = "📊 *Platform Statistics*\n\n"
             . "📖 Topics: *{$stats['total_topics']}*\n"
             . "❓ Questions: *{$stats['total_questions']}*\n"
             . "📚 Vocabulary items: *{$stats['total_vocabulary']}*\n";

        if (!empty($stats['levels'])) {
            $msg .= "\n*By Level:*\n";
            foreach ($stats['levels'] as $l => $c) {
                $msg .= "  {$l}: {$c}\n";
            }
        }

        $this->send($chatId, $msg, ['parse_mode' => 'Markdown']);
    }

    private function cmdRebuild(int $chatId): void
    {
        $repo = new TopicRepository();
        $ok   = $repo->rebuildIndex();
        if ($ok) {
            $stats = $repo->stats();
            $this->send($chatId, "✅ Index rebuilt. {$stats['total_topics']} topics indexed.");
        } else {
            $this->send($chatId, "❌ Failed to rebuild index.");
        }
    }

    private function cmdDelete(int $chatId, string $slug): void
    {
        if (empty($slug)) {
            $this->send($chatId, "Usage: /delete [slug]");
            return;
        }
        $repo  = new TopicRepository();
        $topic = $repo->findBySlug($slug);
        if (!$topic) {
            $this->send($chatId, "❌ Topic `{$slug}` not found.");
            return;
        }
        $keyboard = [
            'inline_keyboard' => [[
                ['text' => '✅ Yes, delete', 'callback_data' => "confirm_delete:{$slug}"],
                ['text' => '❌ Cancel', 'callback_data' => 'cancel'],
            ]],
        ];
        $this->send($chatId, "🗑 Delete *{$topic['title']}*?", [
            'parse_mode'   => 'Markdown',
            'reply_markup' => json_encode($keyboard),
        ]);
    }

    // ── Telegram API helpers ──────────────────────────────────────────────────

    private function send(int $chatId, string $text, array $extra = []): ?array
    {
        return $this->request('sendMessage', array_merge([
            'chat_id'    => $chatId,
            'text'       => $text,
            'parse_mode' => 'Markdown',
        ], $extra));
    }

    private function answerCallback(string $queryId): void
    {
        $this->request('answerCallbackQuery', ['callback_query_id' => $queryId]);
    }

    private function getFile(string $fileId): ?array
    {
        $result = $this->request('getFile', ['file_id' => $fileId]);
        return $result['result'] ?? null;
    }

    private function downloadFile(string $url): string|false
    {
        $ctx = stream_context_create([
            'http' => [
                'timeout' => 60,
                'user_agent' => 'FreeDiscussionClassBot/1.0',
            ],
        ]);
        return file_get_contents($url, false, $ctx);
    }

    private function request(string $method, array $params = []): ?array
    {
        $url  = "{$this->apiBase}/{$method}";
        $ctx  = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query($params),
                'timeout' => 30,
            ],
        ]);
        $resp = file_get_contents($url, false, $ctx);
        if ($resp === false) return null;
        return json_decode($resp, true);
    }

    private function isAdmin(int $userId): bool
    {
        return in_array($userId, $this->admins);
    }

    private function extractLevelFromCaption(string $text): string
    {
        if (preg_match('/\b(A1|A2|B1|B2|C1|C2)\b/i', $text, $m)) {
            return strtoupper($m[1]);
        }
        return '';
    }

    private function logError(string $msg): void
    {
        error_log("[TelegramBot] {$msg}");
    }

    // ── Webhook setup ─────────────────────────────────────────────────────────

    public function setWebhook(string $url): array
    {
        $params = ['url' => $url];
        $secret = config('telegram.webhook_secret');
        if ($secret) $params['secret_token'] = $secret;
        return $this->request('setWebhook', $params) ?? [];
    }
}
