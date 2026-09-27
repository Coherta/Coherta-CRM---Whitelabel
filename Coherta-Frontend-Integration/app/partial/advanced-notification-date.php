<?php

function advanced_notification_publication_date(array $post): ?array {
    $repostedAt = isset($post['reposted_at']) ? trim((string)$post['reposted_at']) : '';
    $postedAt = isset($post['posted_at']) ? trim((string)$post['posted_at']) : '';

    $value = $repostedAt !== '' ? $repostedAt : $postedAt;
    if ($value === '') {
        return null;
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:\d{2})$/', $value)) {
        return null;
    }

    try {
        $date = new DateTimeImmutable($value);
    } catch (Exception $exception) {
        return null;
    }

    $utcDate = $date->setTimezone(new DateTimeZone('UTC'));
    $prefix = $repostedAt !== '' ? 'Reposted' : 'Posted';

    return [
        'field' => $repostedAt !== '' ? 'reposted_at' : 'posted_at',
        'formatted_date' => $utcDate->format('Y-m-d'),
        'label' => $prefix . ' ' . $utcDate->format('Y-m-d'),
        'timestamp' => $utcDate->getTimestamp(),
    ];
}

function advanced_notification_post_key(array $post): string {
    foreach (['activity_id', 'post_id', 'job_id', 'post_url', 'id'] as $field) {
        if (!isset($post[$field])) {
            continue;
        }

        $value = trim((string)$post[$field]);
        if ($value !== '') {
            return $value;
        }
    }

    return '';
}

function advanced_notification_identity_keys(array $post): array {
    $keys = [];
    foreach (['activity_id' => 'activity', 'post_id' => 'post', 'job_id' => 'job'] as $field => $prefix) {
        if (!isset($post[$field])) {
            continue;
        }

        $value = trim((string)$post[$field]);
        if ($value !== '') {
            $keys[] = $prefix . ':' . $value;
        }
    }

    return $keys;
}

function advanced_notification_metadata_index(array $response): array {
    $index = [];
    $visit = function (array $value) use (&$visit, &$index): void {
        $identityKeys = advanced_notification_identity_keys($value);
        if (!empty($identityKeys)) {
            $metadata = [];
            foreach (['posted_at', 'reposted_at', 'posted_ago_raw', 'post_url', 'linkedin_url'] as $field) {
                if (array_key_exists($field, $value) && $value[$field] !== null && trim((string)$value[$field]) !== '') {
                    $metadata[$field] = $value[$field];
                }
            }
            foreach ($identityKeys as $identityKey) {
                $index[$identityKey] = array_merge($index[$identityKey] ?? [], $metadata);
            }
        }

        foreach ($value as $child) {
            if (is_array($child)) {
                $visit($child);
            }
        }
    };

    $visit($response);
    return $index;
}

function advanced_notification_hydrate_post(array $post, array $metadataIndex): array {
    foreach (advanced_notification_identity_keys($post) as $identityKey) {
        if (!isset($metadataIndex[$identityKey])) {
            continue;
        }

        foreach ($metadataIndex[$identityKey] as $field => $value) {
            if (!isset($post[$field]) || trim((string)$post[$field]) === '') {
                $post[$field] = $value;
            }
        }
    }

    return $post;
}

function advanced_notification_deduplicate_posts(array $posts): array {
    $seen = [];
    $deduplicated = [];

    foreach ($posts as $post) {
        if (!is_array($post)) {
            continue;
        }

        $key = advanced_notification_post_key($post);
        if ($key !== '' && isset($seen[$key])) {
            continue;
        }

        if ($key !== '') {
            $seen[$key] = true;
        }
        $deduplicated[] = $post;
    }

    return $deduplicated;
}

function advanced_notification_linkedin_url($value): ?string {
    if ($value === null) {
        return null;
    }

    $url = trim((string)$value);
    if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
        return null;
    }

    $parts = parse_url($url);
    $scheme = strtolower((string)($parts['scheme'] ?? ''));
    $host = strtolower((string)($parts['host'] ?? ''));
    if (!in_array($scheme, ['http', 'https'], true)) {
        return null;
    }
    if ($host !== 'linkedin.com' && substr($host, -13) !== '.linkedin.com') {
        return null;
    }
    return $url;
}

function advanced_notification_text(array $post): string {
    return isset($post['text']) ? (string)$post['text'] : '';
}
