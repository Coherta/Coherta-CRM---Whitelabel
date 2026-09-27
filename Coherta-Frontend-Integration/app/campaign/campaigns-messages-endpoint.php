<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$sharedPath = __DIR__ . '/campaigns-shared.php';
if (is_file($sharedPath)) {
    require_once $sharedPath;
}

if (!isset($defaultEmployees) || !is_array($defaultEmployees)) {
    $defaultEmployees = $campaignEmployees ?? [];
}
if (!isset($threadsStore) || !is_array($threadsStore)) {
    $threadsStore = [];
}

function read_json_body(): array {
    static $input = null;
    if ($input !== null) return $input;

    $raw = file_get_contents('php://input');
    $decoded = json_decode($raw ?: '', true);
    $input = is_array($decoded) ? $decoded : [];
    return $input;
}

function json_response(array $payload, int $status = 200): void {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function find_employee(array $employees, int $employeeId): array {
    foreach ($employees as $employee) {
        if ((int)($employee['id'] ?? 0) === $employeeId) return $employee;
    }
    return $employees[0] ?? ['id' => $employeeId, 'full_name' => 'User', 'email' => null];
}

function normalize_read_status(array $thread): string {
    $status = strtolower((string)($thread['status'] ?? ''));
    if ($status === 'unread') return 'unread';

    $fallback = strtolower((string)($thread['thread_status'] ?? ''));
    if ($status === '' && $fallback === 'unread') return 'unread';

    return 'read';
}

function normalize_thread_status(array $thread): string {
    $raw = strtolower((string)($thread['thread_status'] ?? 'read'));
    $needsReplyStatuses = ['needs_reply', 'needs-reply', 'unanswered', 'pending_reply', 'awaiting_reply', 'requires_reply'];
    $notSentStatuses = ['not_sent', 'not-sent', 'not sent', 'prepared', 'queued', 'ready'];

    if (in_array($raw, $needsReplyStatuses, true)) return 'needs_reply';
    if ((int)($thread['needs_reply'] ?? 0) === 1) return 'needs_reply';
    if (in_array($raw, $notSentStatuses, true)) return 'not_sent';
    if (thread_has_ready_not_sent_message($thread)) return 'not_sent';

    return 'read';
}

function normalize_nullable_datetime($value): ?string {
    if ($value === null) return null;
    $raw = trim((string)$value);
    if ($raw === '' || strtolower($raw) === 'null' || $raw === '0000-00-00 00:00:00') return null;
    return $raw;
}

function truthy_value($value): bool {
    if ($value === true || $value === 1) return true;
    $raw = strtolower(trim((string)$value));
    return in_array($raw, ['1', 'true', 'yes', 'y', 'favorite', 'favourite', 'favorited', 'favourited', 'hearted'], true);
}

function thread_is_favorite(array $thread): bool {
    foreach (['is_favorite', 'favorite', 'is_favorited', 'is_favourited', 'hearted', 'marked_with_heart'] as $key) {
        if (array_key_exists($key, $thread) && $thread[$key] !== null && $thread[$key] !== '') {
            return truthy_value($thread[$key]);
        }
    }

    return normalize_nullable_datetime($thread['favorite_at'] ?? ($thread['favorited_at'] ?? ($thread['hearted_at'] ?? null))) !== null;
}

function clean_outgoing_html(string $html, string $fallbackText = ''): string {
    $html = trim($html);
    if ($html === '' && $fallbackText !== '') {
        return nl2br(htmlspecialchars($fallbackText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
    }

    $html = strip_tags($html, '<strong><b><em><i><br><p><div><span><a><ul><ol><li>');
    $html = preg_replace('/\s(on[a-z]+|style|class|id|data-[a-z0-9_-]+)=(".*?"|\'.*?\'|[^\s>]+)/i', '', $html);
    $html = preg_replace_callback('/<a\s+([^>]*?)>/i', function($matches) {
        $attrs = $matches[1];
        if (!preg_match('/href=("|\')([^"\']+)\1/i', $attrs, $hrefMatch)) {
            return '<a>';
        }
        $href = $hrefMatch[2];
        if (preg_match('/^\s*javascript:/i', $href)) {
            return '<a>';
        }
        return '<a href="' . htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" target="_blank" rel="noopener noreferrer">';
    }, $html);

    return trim($html);
}

function clean_plain_text(string $text, string $html = ''): string {
    $text = trim($text);
    if ($text !== '') return $text;

    if ($html !== '') {
        $html = preg_replace('/<br\s*\/?>/i', "\n", $html);
        return trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
    }

    return '';
}

function normalize_message(array $message): array {
    if (array_key_exists('prepared_send_at', $message)) {
        $message['prepared_send_at'] = normalize_nullable_datetime($message['prepared_send_at']);
    }
    if (array_key_exists('sent_at', $message)) {
        $message['sent_at'] = normalize_nullable_datetime($message['sent_at']);
    }
    if (!array_key_exists('is_editable', $message)) {
        $message['is_editable'] = !empty($message['editable']) ? 1 : 0;
    }
    if (!array_key_exists('editable', $message)) {
        $message['editable'] = !empty($message['is_editable']);
    }
    return $message;
}

function message_is_ready_not_sent(array $message): bool {
    $message = normalize_message($message);

    if (normalize_nullable_datetime($message['sent_at'] ?? null) !== null) {
        return false;
    }

    if (normalize_nullable_datetime($message['prepared_send_at'] ?? null) !== null) {
        return true;
    }

    if ((int)($message['prepared'] ?? $message['is_prepared'] ?? 0) === 1) {
        return true;
    }

    $status = strtolower(trim((string)($message['message_status'] ?? $message['send_status'] ?? $message['status'] ?? '')));
    $status = str_replace([' ', '-'], '_', $status);

    return in_array($status, ['prepared', 'ready', 'queued', 'draft'], true);
}

function thread_has_ready_not_sent_message(array $thread): bool {
    if (!isset($thread['messages']) || !is_array($thread['messages'])) {
        return false;
    }

    foreach ($thread['messages'] as $message) {
        if (is_array($message) && message_is_ready_not_sent($message)) {
            return true;
        }
    }

    return false;
}

function normalize_thread(array $thread): array {
    $thread['status'] = normalize_read_status($thread);
    $isFavorite = thread_is_favorite($thread);
    $thread['is_favorite'] = $isFavorite ? 1 : 0;
    $thread['favorite'] = $thread['is_favorite'];
    $thread['favorite_at'] = $isFavorite ? normalize_nullable_datetime($thread['favorite_at'] ?? ($thread['favorited_at'] ?? ($thread['hearted_at'] ?? null))) : null;

    if (isset($thread['messages']) && is_array($thread['messages'])) {
        $thread['messages'] = array_map('normalize_message', $thread['messages']);
    }

    $thread['thread_status'] = normalize_thread_status($thread);
    $thread['has_not_sent_message'] = thread_has_ready_not_sent_message($thread) ? 1 : 0;

    return $thread;
}

function thread_summary(array $thread): array {
    $thread = normalize_thread($thread);

    return [
        'id' => (int)($thread['id'] ?? 0),
        'lead_id' => (int)($thread['lead_id'] ?? 0),
        'participant_id' => (int)($thread['participant_id'] ?? 0),
        'campaign_id' => (int)($thread['campaign_id'] ?? 0),
        'employee_id' => (int)($thread['employee_id'] ?? 0),
        'name' => $thread['name'] ?? ($thread['contact_name'] ?? 'Unknown'),
        'company' => $thread['company'] ?? ($thread['company_name'] ?? null),
        'image' => $thread['image'] ?? ($thread['avatar_url'] ?? null),
        'participant_type' => $thread['participant_type'] ?? '',
        'linkedin_url' => $thread['linkedin_url'] ?? '',
        'linkedin_thread_url' => $thread['linkedin_thread_url']
            ?? ($thread['linkedin_conversation_url']
            ?? ($thread['messaging_url']
            ?? ($thread['conversation_url']
            ?? ($thread['thread_url'] ?? '')))),
        'linkedin_conversation_id' => $thread['linkedin_conversation_id']
            ?? ($thread['conversation_id']
            ?? ($thread['linkedin_thread_id']
            ?? ($thread['provider_thread_id']
            ?? ($thread['external_thread_id'] ?? '')))),
        'snippet' => $thread['snippet'] ?? '',
        'time' => $thread['time'] ?? ($thread['last_message_time'] ?? ''),
        'last_message_at' => $thread['last_message_at'] ?? ($thread['updated_at'] ?? null),
        'status' => $thread['status'],
        'thread_status' => $thread['thread_status'],
        'needs_reply' => $thread['thread_status'] === 'needs_reply' ? 1 : 0,
        'has_not_sent_message' => !empty($thread['has_not_sent_message']) ? 1 : 0,
        'is_favorite' => !empty($thread['is_favorite']) ? 1 : 0,
        'favorite' => !empty($thread['is_favorite']) ? 1 : 0,
        'favorite_at' => $thread['favorite_at'] ?? null,
    ];
}

function sort_thread_summaries(array $summaries): array {
    foreach ($summaries as $index => &$summary) {
        $raw = $summary['last_message_at'] ?? null;
        $ts = $raw ? strtotime((string)$raw) : false;
        $summary['_sort_ts'] = $ts ?: null;
        $summary['_sort_index'] = $index;
    }
    unset($summary);

    usort($summaries, function($a, $b) {
        if ($a['_sort_ts'] || $b['_sort_ts']) {
            return (int)($b['_sort_ts'] ?? 0) <=> (int)($a['_sort_ts'] ?? 0);
        }
        return (int)$a['_sort_index'] <=> (int)$b['_sort_index'];
    });

    foreach ($summaries as &$summary) {
        unset($summary['_sort_ts'], $summary['_sort_index']);
    }
    unset($summary);

    return array_values($summaries);
}

function summaries_for_employee(array $threadsStore, int $employeeId): array {
    $summaries = array_map('thread_summary', array_values($threadsStore[$employeeId] ?? []));
    return sort_thread_summaries($summaries);
}

function update_thread_after_outgoing_message(array &$thread, string $plainText): void {
    $thread['snippet'] = 'You: ' . $plainText;
    $thread['time'] = 'Now';
    $thread['status'] = 'read';
    $thread['thread_status'] = 'read';
    $thread['needs_reply'] = 0;
    $thread['has_not_sent_message'] = 0;
    $thread['last_message_at'] = date('Y-m-d H:i:s');
}

$input = read_json_body();
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$employeeId = (int)($input['employee_id'] ?? $input['user_id'] ?? $_REQUEST['employee_id'] ?? $_REQUEST['user_id'] ?? 1);

if (!isset($threadsStore[$employeeId]) && !empty($threadsStore)) {
    $employeeId = (int)array_key_first($threadsStore);
}

$employee = find_employee($defaultEmployees, $employeeId);

if (in_array($method, ['POST', 'PATCH'], true)) {
    $action = (string)($input['action'] ?? '');
    $threadId = (int)($input['thread_id'] ?? 0);

    if (!isset($threadsStore[$employeeId])) {
        json_response(['error' => 'Employee inbox was not found.'], 404);
    }

    foreach ($threadsStore[$employeeId] as &$thread) {
        if ((int)($thread['id'] ?? 0) !== $threadId) continue;

        $thread = normalize_thread($thread);
        if (!isset($thread['messages']) || !is_array($thread['messages'])) {
            $thread['messages'] = [];
        }

        if ($method === 'PATCH' && in_array($action, ['set_favorite', 'favorite', 'unfavorite', 'toggle_favorite', 'heart', 'unheart'], true)) {
            $favoriteInput = null;
            foreach (['is_favorite', 'favorite', 'hearted', 'marked_with_heart'] as $key) {
                if (array_key_exists($key, $input)) {
                    $favoriteInput = truthy_value($input[$key]);
                    break;
                }
            }

            if ($favoriteInput === null) {
                if (in_array($action, ['unfavorite', 'unheart'], true)) {
                    $favoriteInput = false;
                } elseif (in_array($action, ['favorite', 'heart'], true)) {
                    $favoriteInput = true;
                } else {
                    $favoriteInput = !thread_is_favorite($thread);
                }
            }

            $thread['is_favorite'] = $favoriteInput ? 1 : 0;
            $thread['favorite'] = $thread['is_favorite'];
            $thread['favorite_at'] = $favoriteInput ? ($thread['favorite_at'] ?? date('Y-m-d H:i:s')) : null;
            $thread = normalize_thread($thread);

            json_response([
                'employee' => $employee,
                'threads' => summaries_for_employee($threadsStore, $employeeId),
                'selected_thread' => $thread,
            ]);
        }

        if ($method === 'POST' && in_array($action, ['reply', 'send_reply'], true)) {
            $html = clean_outgoing_html((string)($input['html'] ?? ''), (string)($input['text'] ?? ''));
            $plainText = clean_plain_text((string)($input['text'] ?? ''), $html);

            if ($plainText === '') {
                json_response(['error' => 'Reply text is required.'], 422);
            }

            $messageId = (int)(microtime(true) * 1000) + random_int(100, 999);
            $thread['messages'][] = [
                'id' => $messageId,
                'from' => $employee['full_name'] ?? 'You',
                'side' => 'right',
                'text' => $html,
                'time' => date('H:i'),
                'sent_at' => date('Y-m-d H:i:s'),
                'prepared_send_at' => null,
                'prepared' => false,
                'is_prepared' => 0,
                'editable' => false,
                'is_editable' => 0,
            ];
            update_thread_after_outgoing_message($thread, $plainText);

            json_response([
                'employee' => $employee,
                'threads' => summaries_for_employee($threadsStore, $employeeId),
                'selected_thread' => $thread,
            ]);
        }

        if ($method === 'PATCH' && $action === 'delete_message') {
            $messageId = (int)($input['message_id'] ?? 0);
            $messageIndex = null;

            foreach ($thread['messages'] as $index => $message) {
                if ((int)($message['id'] ?? 0) === $messageId) {
                    $messageIndex = $index;
                    break;
                }
            }

            if ($messageIndex === null) {
                json_response(['error' => 'Message was not found.'], 404);
            }

            array_splice($thread['messages'], $messageIndex, 1);
            $thread = normalize_thread($thread);

            json_response([
                'employee' => $employee,
                'threads' => summaries_for_employee($threadsStore, $employeeId),
                'selected_thread' => $thread,
                'workflow_stopped' => !empty($input['helper_id']) && !empty($input['stop_workflow']),
            ]);
        }

        if ($method === 'PATCH' && in_array($action, ['save_and_send', 'edit_reply'], true)) {
            $messageId = (int)($input['message_id'] ?? 0);
            $html = clean_outgoing_html((string)($input['html'] ?? ''), (string)($input['text'] ?? ''));
            $plainText = clean_plain_text((string)($input['text'] ?? ''), $html);

            if ($plainText === '') {
                json_response(['error' => 'Message text is required.'], 422);
            }

            foreach ($thread['messages'] as &$message) {
                $message = normalize_message($message);
                if ((int)($message['id'] ?? 0) !== $messageId) continue;

                $isEditable = ((int)($message['is_editable'] ?? 0) === 1) || !empty($message['editable']);
                if (!$isEditable) {
                    json_response(['error' => 'Message is not editable.'], 409);
                }

                $message['text'] = $html;
                $message['time'] = date('H:i');
                $message['sent_at'] = date('Y-m-d H:i:s');
                $message['prepared_send_at'] = date('Y-m-d H:i:s');
                $message['prepared'] = true;
                $message['is_prepared'] = 1;
                $message['editable'] = false;
                $message['is_editable'] = 0;

                update_thread_after_outgoing_message($thread, $plainText);

                json_response([
                    'employee' => $employee,
                    'threads' => summaries_for_employee($threadsStore, $employeeId),
                    'selected_thread' => $thread,
                ]);
            }
            unset($message);

            json_response(['error' => 'Editable message was not found.'], 404);
        }

        if ($method === 'PATCH' && $action === 'prepare_message') {
            $messageId = (int)($input['message_id'] ?? 0);

            foreach ($thread['messages'] as &$message) {
                $message = normalize_message($message);
                if ((int)($message['id'] ?? 0) !== $messageId) continue;

                if (normalize_nullable_datetime($message['sent_at'] ?? null) !== null) {
                    json_response(['error' => 'Message has already been sent.'], 409);
                }

                $message['prepared'] = true;
                $message['is_prepared'] = 1;
                $message['prepared_send_at'] = date('Y-m-d H:i:s');
                $thread = normalize_thread($thread);

                json_response([
                    'employee' => $employee,
                    'threads' => summaries_for_employee($threadsStore, $employeeId),
                    'selected_thread' => $thread,
                ]);
            }
            unset($message);

            json_response(['error' => 'Message was not found.'], 404);
        }

        json_response(['error' => 'Unknown action.'], 422);
    }
    unset($thread);

    json_response(['error' => 'Thread was not found.'], 404);
}

$threadId = (int)($_GET['thread_id'] ?? 0);
$mode = $_GET['mode'] ?? 'full';
$selectedThread = null;

if (isset($threadsStore[$employeeId])) {
    foreach ($threadsStore[$employeeId] as &$thread) {
        $thread = normalize_thread($thread);

        if ($threadId && (int)($thread['id'] ?? 0) === $threadId) {
            $thread['status'] = 'read';
            $selectedThread = $thread;
            break;
        }
    }
    unset($thread);

    if (!$selectedThread && !empty($threadsStore[$employeeId])) {
        $selectedThread = normalize_thread($threadsStore[$employeeId][0]);
    }
}

$response = [
    'employee' => $employee,
    'selected_thread' => $selectedThread,
];

if ($mode !== 'thread') {
    $response['threads'] = summaries_for_employee($threadsStore, $employeeId);
}

json_response($response);
