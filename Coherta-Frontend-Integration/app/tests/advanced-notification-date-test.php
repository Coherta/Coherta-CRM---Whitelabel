<?php
declare(strict_types=1);

require_once __DIR__ . '/../partial/advanced-notification-date.php';

function assert_same($expected, $actual, string $message): void {
    if ($expected !== $actual) {
        fwrite(STDERR, $message . PHP_EOL);
        fwrite(STDERR, 'Expected: ' . var_export($expected, true) . PHP_EOL);
        fwrite(STDERR, 'Actual: ' . var_export($actual, true) . PHP_EOL);
        exit(1);
    }
}

$originalTimezone = date_default_timezone_get();
date_default_timezone_set('America/Los_Angeles');

$reposted = advanced_notification_publication_date([
    'posted_at' => '2026-08-01T12:00:00Z',
    'reposted_at' => '2026-08-06T20:00:00Z',
    'seen_date' => '2026-08-07T08:00:00Z',
]);
assert_same('reposted_at', $reposted['field'] ?? null, 'reposted_at must take priority over posted_at.');
assert_same('Reposted 2026-08-06', $reposted['label'] ?? null, 'Reposted label must use yyyy-mm-dd.');

$posted = advanced_notification_publication_date([
    'posted_at' => '2026-08-06T00:30:00Z',
    'reposted_at' => null,
    'seen_date' => '2026-08-07T08:00:00Z',
]);
assert_same('posted_at', $posted['field'] ?? null, 'posted_at must be used when reposted_at is null.');
assert_same('2026-08-06', $posted['formatted_date'] ?? null, 'The date must use yyyy-mm-dd.');
assert_same('Posted 2026-08-06', $posted['label'] ?? null, 'The UTC date must not move in a western local timezone.');

$seenOnly = advanced_notification_publication_date([
    'posted_at' => null,
    'reposted_at' => null,
    'seen_date' => '2026-08-07T08:00:00Z',
]);
assert_same(null, $seenOnly, 'seen_date must never be used as a publication-date fallback.');

$missing = advanced_notification_publication_date([]);
assert_same(null, $missing, 'No date must be shown when posted_at and reposted_at are both missing.');

$relativeValue = advanced_notification_publication_date([
    'posted_at' => '1 month ago',
    'reposted_at' => null,
]);
assert_same(null, $relativeValue, 'Relative text must not be accepted in an ISO-8601 timestamp field.');

$frankBollPost = [
    'id' => 436,
    'post_id' => '7480889316831997952',
    'activity_id' => '7480889316831997952',
    'job_id' => null,
    'post_url' => 'https://www.linkedin.com/feed/update/urn:li:activity:7480889316831997952/',
    'linkedin_url' => 'https://dk.linkedin.com/in/frank-boll-7b756265',
    'name' => 'Frank Boll',
    'text' => "Spare Parts Coordinator\nProjektleder\nPLC- og Softwaretekniker\nDanske tegn: æ, ø og å",
    'posted_at' => '2026-07-09T07:43:21.335Z',
    'reposted_at' => null,
    'posted_ago_raw' => null,
    'seen_date' => '2026-08-04',
];

assert_same('7480889316831997952', advanced_notification_post_key($frankBollPost), 'activity_id must be the primary post key.');
assert_same('Posted 2026-07-09', advanced_notification_publication_date($frankBollPost)['label'] ?? null, 'The activity fixture must render its UTC publication date.');
assert_same($frankBollPost['text'], advanced_notification_text($frankBollPost), 'Full text and Danish characters must be preserved.');
assert_same($frankBollPost['linkedin_url'], advanced_notification_linkedin_url($frankBollPost['linkedin_url']), 'The author profile URL must remain available.');
assert_same($frankBollPost['post_url'], advanced_notification_linkedin_url($frankBollPost['post_url']), 'The original post URL must remain available.');

$duplicateInSameNotification = $frankBollPost;
$duplicateInSameNotification['id'] = 999;
$deduplicated = advanced_notification_deduplicate_posts([$frankBollPost, $duplicateInSameNotification]);
assert_same(1, count($deduplicated), 'Duplicate activity IDs must be removed within one notification.');

$firstNotificationPosts = advanced_notification_deduplicate_posts([$frankBollPost]);
$secondNotificationPosts = advanced_notification_deduplicate_posts([$frankBollPost]);
assert_same(1, count($firstNotificationPosts), 'The post must remain in the first notification.');
assert_same(1, count($secondNotificationPosts), 'The same post must remain visible in a different notification.');

assert_same('job-123', advanced_notification_post_key([
    'activity_id' => null,
    'post_id' => null,
    'job_id' => 'job-123',
]), 'job_id must identify a job when activity_id and post_id are missing.');
assert_same(null, advanced_notification_linkedin_url(null), 'A null URL must not render a link.');
assert_same(null, advanced_notification_linkedin_url('javascript:alert(1)'), 'An invalid URL must not render a link.');
assert_same('', advanced_notification_text(['text' => null]), 'A null text value must render as an empty string.');

$nestedApiResponse = [[
    'id' => 10,
    'subject' => 'Account Manager',
    'notifications' => [[
        'id' => 20,
        'job_id' => '4447655116',
        'text' => 'Account Manager',
    ]],
    'post_metadata' => [[
        'job_id' => '4447655116',
        'posted_at' => '2026-08-03T00:00:00Z',
        'reposted_at' => null,
        'posted_ago_raw' => '4 dage siden',
    ]],
]];
$metadataIndex = advanced_notification_metadata_index($nestedApiResponse);
$hydratedJob = advanced_notification_hydrate_post($nestedApiResponse[0]['notifications'][0], $metadataIndex);
assert_same('2026-08-03T00:00:00Z', $hydratedJob['posted_at'] ?? null, 'Date metadata must be joined to a rendered post by job_id.');
assert_same('Posted 2026-08-03', advanced_notification_publication_date($hydratedJob)['label'] ?? null, 'A hydrated job date must render in the card label.');

$apiJobFixtures = [
    ['job_id' => '4447655116', 'posted_at' => '2026-08-03T00:00:00Z', 'reposted_at' => null, 'posted_ago_raw' => '4 dage siden'],
    ['job_id' => '4443874439', 'posted_at' => '2026-07-24T00:00:00Z', 'reposted_at' => null, 'posted_ago_raw' => '2 uger siden'],
    ['job_id' => '4437060874', 'posted_at' => '2026-07-31T00:00:00Z', 'reposted_at' => null, 'posted_ago_raw' => '1 uge siden'],
];
assert_same('Posted 2026-08-03', advanced_notification_publication_date($apiJobFixtures[0])['label'] ?? null, 'Job 4447655116 must render its API date.');
assert_same('Posted 2026-07-24', advanced_notification_publication_date($apiJobFixtures[1])['label'] ?? null, 'Job 4443874439 must render its API date.');
assert_same('Posted 2026-07-31', advanced_notification_publication_date($apiJobFixtures[2])['label'] ?? null, 'Job 4437060874 must render its API date.');

date_default_timezone_set($originalTimezone);
fwrite(STDOUT, "Advanced notification date tests passed.\n");
