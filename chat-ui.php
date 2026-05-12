<?php

/**
 * Intelligent Ollama API with Real-Time Persistence & Model Discovery
 *
 * Features:
 * - Auto-discovers local Ollama models via /api/tags.
 * - Heuristic selection of the "proper" model (e.g., prefers newer/larger models).
 * - Fallback to cloud endpoints if local is unavailable.
 * - Real-time streaming & Session Persistence.
 */

declare(strict_types=1);

/* -------------------- Configuration -------------------- */

define('DATA_DIR', __DIR__.'/data');
define('SESSION_DIR', DATA_DIR.'/sessions');
define('BLACKLIST_FILE', DATA_DIR.'/blacklist.json');
define('CONFIG_FILE', DATA_DIR.'/global_config.json');

// Ensure directories exist
if (! is_dir(SESSION_DIR)) {
    mkdir(SESSION_DIR, 0775, true);
}

// Local Ollama Configuration
$LOCAL_OLLAMA = [
    'base_url' => 'http://localhost:11434',
    'priority' => 1000, // Highest priority
];

// Cloud Fallbacks (OpenAI Compatible)
$CLOUD_ENDPOINTS = [
    [
        'name' => 'Cloud Provider A',
        'url' => 'https://api.example.com/v1/chat/completions',
        'api_key' => 'your-key',
        'models' => ['gpt-4o', 'gpt-3.5-turbo'], // Pre-defined for cloud
        'priority' => 50,
    ],
];

/* -------------------- Helpers -------------------- */

function json_response(array $data, int $code = 200)
{
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    exit;
}

function get_session_file(string $id): string
{
    $safe_id = preg_replace('/[^a-zA-Z0-9\-_]/', '', $id);

    return SESSION_DIR.'/'.$safe_id.'.json';
}

function load_history(string $file): array
{
    if (! file_exists($file)) {
        return [];
    }

    return json_decode(file_get_contents($file), true) ?? [];
}

function append_history(string $file, array $msg)
{
    $history = load_history($file);
    $history[] = $msg;
    file_put_contents($file, json_encode($history, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
}

function get_session_title(array $history, string $fallback): string
{
    foreach ($history as $msg) {
        if (($msg['role'] ?? '') === 'user' && ! empty($msg['content'])) {
            $title = mb_trim(preg_replace('/\s+/', ' ', (string) $msg['content']));

            return mb_strlen($title) > 48 ? mb_substr($title, 0, 48).'...' : $title;
        }
    }

    return $fallback;
}

/* -------------------- Core Logic -------------------- */

final class ModelManager
{
    private $localConfig;

    private $cloudConfig;

    private $blacklist;

    public function __construct($local, $cloud)
    {
        $this->localConfig = $local;
        $this->cloudConfig = $cloud;
        $this->blacklist = file_exists(BLACKLIST_FILE) ? json_decode(file_get_contents(BLACKLIST_FILE), true) : [];
    }

    /**
     * Fetches available models from Local Ollama (/api/tags)
     */
    public function getLocalModels(): array
    {
        $url = $this->localConfig['base_url'].'/api/tags';
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 2,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ]);
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($code === 200 && $res) {
            $data = json_decode($res, true);

            return $data['models'] ?? [];
        }

        return [];
    }

    /**
     * Intelligent Selection Logic.
     * 1. Check if user requested a specific model.
     * 2. Check Local Ollama availability and sort models by "quality".
     * 3. Fallback to Cloud.
     */
    public function resolveEndpoint(?string $requestedModel): array
    {
        // 1. Try Local Ollama
        $localModels = $this->getLocalModels();

        if (! empty($localModels)) {
            $selectedModel = null;

            if ($requestedModel) {
                // Verify requested model exists locally
                foreach ($localModels as $m) {
                    if ($m['name'] === $requestedModel || explode(':', $m['name'])[0] === $requestedModel) {
                        $selectedModel = $m['name'];
                        break;
                    }
                }
            }

            if (! $selectedModel) {
                // HEURISTIC: Pick the "Proper" model automatically
                // Prefer models containing 'llama', 'mistral', 'gemma' (newer usually)
                // Sort by size/modtime if possible, else just pick the first 'best' guess.
                usort($localModels, function ($a, $b) {
                    // Simple heuristic: prefer 'llama3' over 'llama2', etc.
                    // In a real app, you might check modification time or parameter count.
                    // For now, we prioritize specific keywords.
                    $keywords = ['llama3', 'llama-3', 'mistral', 'gemma2', 'qwen2', 'phi-3'];
                    foreach ($keywords as $kw) {
                        if (mb_stripos($a['name'], $kw) !== false) {
                            return -1;
                        }
                        if (mb_stripos($b['name'], $kw) !== false) {
                            return 1;
                        }
                    }

                    return 0;
                });
                $selectedModel = $localModels[0]['name'];
            }

            return [
                'type' => 'local_ollama',
                'name' => 'Local: '.$selectedModel,
                'model' => $selectedModel,
                'url' => $this->localConfig['base_url'].'/api/chat', // Native Ollama Chat Endpoint
                'priority' => 1000,
            ];
        }

        // 2. Fallback to Cloud
        // (Blacklist logic omitted for brevity, but would go here)
        foreach ($this->cloudConfig as $cloud) {
            if ($requestedModel && in_array($requestedModel, $cloud['models'])) {
                return [
                    'type' => 'cloud',
                    'name' => 'Cloud: '.$requestedModel,
                    'model' => $requestedModel,
                    'url' => $cloud['url'],
                    'key' => $cloud['api_key'],
                    'priority' => $cloud['priority'],
                ];
            }
        }

        // Default Cloud fallback
        if (! empty($this->cloudConfig)) {
            $def = $this->cloudConfig[0];

            return [
                'type' => 'cloud',
                'name' => 'Cloud: '.($def['models'][0] ?? 'default'),
                'model' => $def['models'][0] ?? 'default',
                'url' => $def['url'],
                'key' => $def['api_key'],
                'priority' => $def['priority'],
            ];
        }

        throw new Exception('No endpoints available.');
    }
}

/* -------------------- Router -------------------- */

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$manager = new ModelManager($LOCAL_OLLAMA, $CLOUD_ENDPOINTS);

// 1. Status & Model Discovery
if ($action === 'status') {
    try {
        $models = $manager->getLocalModels();

        json_response([
            'status' => ! empty($models) ? 'connected' : 'disconnected',
            'local_models' => $models,
            'cloud_models' => array_column($CLOUD_ENDPOINTS, 'models'), // Flatten
        ]);
    } catch (Exception $e) {
        json_response(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

// 2. Stream Chat
if ($action === 'stream') {
    set_time_limit(0);
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('X-Accel-Buffering: no');

    $input = json_decode(file_get_contents('php://input'), true);
    $session_id = $input['session_id'] ?? '';
    $message = $input['message'] ?? '';
    $requested_model = $input['model'] ?? null; // User selection

    if (! $session_id || ! $message) {
        json_response(['error' => 'Invalid'], 400);
    }

    $session_file = get_session_file($session_id);

    // 1. Resolve Endpoint
    try {
        $endpoint = $manager->resolveEndpoint($requested_model);
    } catch (Exception $e) {
        echo "event: error\ndata: ".json_encode(['error' => $e->getMessage()])."\n\n";
        exit;
    }

    // 2. Save User Message
    append_history($session_file, ['role' => 'user', 'content' => $message]);
    $history = load_history($session_file);

    // 3. Prepare Payload
    $payload = [
        'model' => $endpoint['model'],
        'messages' => $history,
        'stream' => true,
    ];

    $headers = ['Content-Type: application/json'];
    if ($endpoint['type'] === 'cloud') {
        $headers[] = 'Authorization: Bearer '.$endpoint['key'];
        // OpenAI format tweak
        // unset($payload['stream']); // optional
    }

    // 4. Stream
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $endpoint['url'],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_WRITEFUNCTION => function ($curl, $data) {
            echo $data; // Pass through raw NDJSON/SSE
            flush();

            return mb_strlen($data);
        },
    ]);

    $ok = curl_exec($ch);
    if ($ok === false) {
        echo "event: error\ndata: ".json_encode(['error' => curl_error($ch)])."\n\n";
        flush();
    }
    exit;
}

// 3. Save Response
if ($action === 'save_response') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['session_id'] ?? '';
    $content = $input['content'] ?? '';

    if ($id && $content) {
        append_history(get_session_file($id), ['role' => 'assistant', 'content' => $content]);
        json_response(['status' => 'saved']);
    }
    json_response(['error' => 'fail'], 400);
}

// 4. Load/Session Logic
if ($action === 'list_sessions') {
    $sessions = [];

    foreach (glob(SESSION_DIR.'/*.json') ?: [] as $file) {
        $id = basename($file, '.json');
        $history = load_history($file);

        $sessions[] = [
            'id' => $id,
            'title' => get_session_title($history, $id),
            'updated_at' => filemtime($file) ?: 0,
            'message_count' => count($history),
        ];
    }

    usort($sessions, fn ($a, $b) => $b['updated_at'] <=> $a['updated_at']);
    json_response(['sessions' => $sessions]);
}

if ($action === 'load') {
    $id = (string) ($_GET['session_id'] ?? '');
    if ($id === '') {
        json_response(['error' => 'Missing session_id'], 400);
    }

    $file = get_session_file($id);
    if (! file_exists($file)) {
        json_response(['error' => 'Session not found'], 404);
    }

    json_response([
        'id' => basename($file, '.json'),
        'messages' => load_history($file),
    ]);
}

json_response(['error' => 'Unknown']);
