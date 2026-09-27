<?php
function recruiter_h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function recruiter_owner_options(?array $user, ?array $campaign = null): array {
    $options = [];
    $seen = [];
    $addOption = function ($candidate) use (&$options, &$seen) {
        if (!is_array($candidate) || !isset($candidate["id"]) || (string)$candidate["id"] === "") return;
        $id = (string)$candidate["id"];
        if (isset($seen[$id])) return;
        $seen[$id] = true;
        $options[] = [
            "id" => $id,
            "name" => $candidate["full_name"] ?? $candidate["name"] ?? $candidate["email"] ?? ("User " . $id),
            "email" => $candidate["email"] ?? "",
        ];
    };

    $addOption($user ?? []);
    foreach (($user["colleagues"] ?? []) as $colleague) {
        $addOption($colleague);
    }

    if ($campaign && !empty($campaign["owner_id"])) {
        $addOption([
            "id" => $campaign["owner_id"],
            "full_name" => $campaign["owner"] ?? "",
        ]);
    }

    usort($options, fn($a, $b) => strcasecmp((string)$a["name"], (string)$b["name"]));
    return $options;
}

$recruiterStages = [
    1 => [
        "title" => "Identify candidates",
        "short" => "Identify",
        "action" => "visit",
        "description" => "Build the candidate field from the target roles and search criteria.",
    ],
    2 => [
        "title" => "Connect with candidates",
        "short" => "Connect",
        "action" => "connect",
        "description" => "Narrow the field and send connection requests to the best matches.",
    ],
    3 => [
        "title" => "Message candidates",
        "short" => "Message",
        "action" => "message",
        "description" => "Send the automatic message to the qualified connected candidates.",
    ],
    4 => [
        "title" => "Conclude replies",
        "short" => "Conclude",
        "action" => "reply_view",
        "description" => "Review replies and conclude which candidates should move forward.",
    ],
];

function recruiter_step_defaults(array $terms, array $skills, string $message): array {
    $skillCriteria = array_map(function ($skill) {
        return [
            "skill" => $skill,
            "endorsements" => null,
            "priority" => "requirement",
        ];
    }, array_values(array_filter(array_map("trim", $skills))));
    $skillCriteria = recruiter_apply_skill_values($skillCriteria);

    return [
        1 => [
            "filter_label" => "Filtering",
            "max_candidates" => 500,
            "match_threshold" => 55,
            "titles" => implode(", ", $terms),
            "locations" => "Denmark, Remote",
            "exclude_companies" => "",
            "campaign_action" => "visit",
            "campaign_action_label" => "Identify",
        ],
        2 => [
            "filter_label" => "Filtering",
            "max_candidates" => 150,
            "match_threshold" => 72,
            "skills" => implode(", ", $skills),
            "skill_criteria" => $skillCriteria,
            "connection_note" => "Hi {{first_name}}.\nI came across your profile and would like to connect. We do have an open position as (insert Campaign Title) and I would like to discuss this with you.",
            "campaign_action" => "connect",
            "campaign_action_label" => "Connect",
        ],
        3 => [
            "filter_label" => "Filtering",
            "max_candidates" => 60,
            "match_threshold" => 82,
            "message_body" => $message,
            "campaign_action" => "message",
            "campaign_action_label" => "Message",
        ],
        4 => [
            "filter_label" => "Filtering",
            "max_candidates" => 999,
            "match_threshold" => 0,
            "campaign_action" => "reply_view",
            "campaign_action_label" => "Review",
        ],
    ];
}

function recruiter_step_plan(array $startedAt, int $activeStage, array $settings): array {
    $plan = [];
    foreach ([1, 2, 3, 4] as $stage) {
        $started = $startedAt[$stage] ?? null;
        $plan[$stage] = [
            "started_at" => $started,
            "status" => $started ? ($stage === $activeStage ? "active" : "completed") : "not_started",
            "settings" => $settings[$stage] ?? [],
            "linked_campaign_id" => $started ? "rec-step-" . $stage . "-" . md5((string)$started) : null,
        ];
    }
    return $plan;
}

function recruiter_active_candidates(array $campaign): array {
    return array_values(array_filter($campaign["candidates"] ?? [], fn($candidate) => ($candidate["status"] ?? "active") === "active"));
}

function recruiter_stage_counts(array $campaign): array {
    if (isset($campaign["_stage_counts"]) && is_array($campaign["_stage_counts"])) {
        return [
            1 => (int)($campaign["_stage_counts"][1] ?? $campaign["_stage_counts"]["1"] ?? 0),
            2 => (int)($campaign["_stage_counts"][2] ?? $campaign["_stage_counts"]["2"] ?? 0),
            3 => (int)($campaign["_stage_counts"][3] ?? $campaign["_stage_counts"]["3"] ?? 0),
            4 => (int)($campaign["_stage_counts"][4] ?? $campaign["_stage_counts"]["4"] ?? 0),
        ];
    }
    $counts = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
    foreach (recruiter_active_candidates($campaign) as $candidate) {
        $stage = (int)$candidate["stage"];
        if (isset($counts[$stage])) $counts[$stage]++;
    }
    return $counts;
}

function recruiter_rank_candidates(array $candidates): array {
    usort($candidates, function ($a, $b) {
        $fitCompare = ((int)$b["fit"]) <=> ((int)$a["fit"]);
        if ($fitCompare !== 0) return $fitCompare;
        return strcmp((string)$a["name"], (string)$b["name"]);
    });
    return $candidates;
}

function recruiter_normalize_skill_name($value): string {
    return strtolower(trim((string)$value));
}

function recruiter_apply_skill_values(array $criteria): array {
    $weighted = [];
    $totalWeight = 0;

    foreach ($criteria as $item) {
        if (!is_array($item)) continue;
        $skill = trim((string)($item["skill"] ?? ""));
        if ($skill === "") continue;
        $priority = (string)($item["priority"] ?? "requirement");
        $priority = in_array($priority, ["requirement", "preferred"], true) ? $priority : "requirement";
        $weight = $priority === "requirement" ? 2 : 1;
        $totalWeight += $weight;
        $weighted[] = array_merge($item, [
            "skill" => $skill,
            "priority" => $priority,
            "_weight" => $weight,
        ]);
    }

    foreach ($weighted as &$item) {
        $item["value"] = $totalWeight > 0 ? round(($item["_weight"] / $totalWeight) * 100, 2) : 0;
        unset($item["_weight"]);
    }
    unset($item);

    return $weighted;
}

function recruiter_step_settings(array $campaign, int $stage): array {
    return is_array($campaign["steps"][$stage]["settings"] ?? null) ? $campaign["steps"][$stage]["settings"] : [];
}

function recruiter_skill_names_from_value($value): array {
    if (is_string($value)) {
        return array_values(array_filter(array_map("recruiter_normalize_skill_name", explode(",", $value))));
    }
    if (!is_array($value)) return [];

    $skills = [];
    foreach ($value as $item) {
        if (is_array($item)) {
            $item = $item["skill"] ?? $item["name"] ?? $item["title"] ?? "";
        }
        $skill = recruiter_normalize_skill_name($item);
        if ($skill !== "") $skills[] = $skill;
    }
    return array_values(array_unique($skills));
}

function recruiter_candidate_has_required_skills(array $candidate, array $settings): bool {
    $requiredSkills = array_values(array_filter(array_map(function ($item) {
        if (!is_array($item) || ($item["priority"] ?? "requirement") !== "requirement") return "";
        return recruiter_normalize_skill_name($item["skill"] ?? "");
    }, recruiter_skill_criteria($settings))));
    if (!$requiredSkills) return true;

    $candidateSkills = array_values(array_unique(array_merge(
        recruiter_skill_names_from_value($candidate["skills"] ?? []),
        recruiter_skill_names_from_value($candidate["matched_skills"] ?? [])
    )));

    if (!$candidateSkills) return true;

    foreach ($requiredSkills as $requiredSkill) {
        $matched = false;
        foreach ($candidateSkills as $candidateSkill) {
            if ($candidateSkill === $requiredSkill || str_contains($candidateSkill, $requiredSkill) || str_contains($requiredSkill, $candidateSkill)) {
                $matched = true;
                break;
            }
        }
        if (!$matched) return false;
    }

    return true;
}

function recruiter_filter_narrowing_candidates(array $candidates, array $campaign): array {
    $settings = recruiter_step_settings($campaign, 2);
    $threshold = (int)($settings["match_threshold"] ?? 0);

    return array_values(array_filter($candidates, function ($candidate) use ($settings, $threshold) {
        return (int)($candidate["fit"] ?? 0) > $threshold
            && recruiter_candidate_has_required_skills($candidate, $settings);
    }));
}

function recruiter_candidates_for_view(array $campaign, int $selectedStage, string $view): array {
    $candidates = recruiter_active_candidates($campaign);
    if ($selectedStage > 0) {
        $candidates = array_values(array_filter($candidates, fn($candidate) => (int)$candidate["stage"] === $selectedStage));
    }
    if ($selectedStage === 2) {
        $candidates = recruiter_filter_narrowing_candidates($candidates, $campaign);
    }
    $candidates = recruiter_rank_candidates($candidates);
    if ($view === "top5") return array_slice($candidates, 0, 5);
    if ($view === "top10") return array_slice($candidates, 0, 10);
    return $candidates;
}

function recruiter_candidates_for_stage(array $campaign, int $selectedStage): array {
    $candidates = recruiter_active_candidates($campaign);
    if ($selectedStage > 0) {
        $candidates = array_values(array_filter($candidates, fn($candidate) => (int)$candidate["stage"] === $selectedStage));
    }
    if ($selectedStage === 2) {
        $candidates = recruiter_filter_narrowing_candidates($candidates, $campaign);
    }
    return recruiter_rank_candidates($candidates);
}

function recruiter_find_campaign(array $campaigns, string $id): ?array {
    foreach ($campaigns as $campaign) {
        if ((string)$campaign["id"] === (string)$id) return $campaign;
    }
    return null;
}

function recruiter_format_date(?string $value): string {
    if (!$value) return "";
    try {
        return (new DateTime($value))->format("d.m.Y");
    } catch (Throwable $e) {
        return (string)$value;
    }
}

function recruiter_status_label($status): string {
    if ((string)$status === "1") return "Aktiv";
    if ((string)$status === "2") return "Lukket";
    return $status !== null && $status !== "" ? (string)$status : "Aktiv";
}

function recruiter_skill_criteria($settings): array {
    $criteria = [];
    if (is_array($settings) && is_array($settings["skill_criteria"] ?? null)) {
        foreach ($settings["skill_criteria"] as $item) {
            if (!is_array($item)) continue;
            $skill = trim((string)($item["skill"] ?? ""));
            if ($skill === "") continue;
            $priority = (string)($item["priority"] ?? "requirement");
            $criteria[] = [
                "skill" => $skill,
                "endorsements" => isset($item["endorsements"]) && $item["endorsements"] !== "" ? max(0, (int)$item["endorsements"]) : null,
                "priority" => in_array($priority, ["requirement", "preferred"], true) ? $priority : "requirement",
            ];
        }
    }

    if (!$criteria && is_array($settings)) {
        $skills = array_filter(array_map("trim", explode(",", (string)($settings["skills"] ?? ""))));
        foreach ($skills as $skill) {
            $criteria[] = [
                "skill" => $skill,
                "endorsements" => null,
                "priority" => "requirement",
            ];
        }
    }

    $criteria = recruiter_apply_skill_values($criteria);
    return $criteria ?: [["skill" => "", "endorsements" => null, "priority" => "requirement", "value" => 0]];
}

function recruiter_normalize_steps(array $apiSteps, int $primaryStage): array {
    $steps = [];
    foreach ([1, 2, 3, 4] as $stage) {
        $step = $apiSteps[$stage] ?? $apiSteps[(string)$stage] ?? [];
        $started = $step["started_at"] ?? null;
        $stopped = $step["stopped_at"] ?? null;
        $rawStatus = strtolower((string)($step["status"] ?? ""));
        $isStopped = $stopped || in_array($rawStatus, ["stopped", "stop", "paused", "inactive"], true);
        $steps[$stage] = [
            "started_at" => recruiter_format_date($started),
            "status" => $started ? ($isStopped ? "stopped" : ($stage === $primaryStage ? "active" : "completed")) : "not_started",
            "settings" => is_array($step["settings"] ?? null) ? $step["settings"] : [],
            "linked_campaign_id" => $step["linked_campaign_id"] ?? null,
        ];
    }
    return $steps;
}

function recruiter_normalize_candidate(array $candidate): array {
    $id = (int)($candidate["id"] ?? 0);
    $cvId = (int)($candidate["cv_id"] ?? 0);
    $participantId = $candidate["participant_id"] ?? null;
    $participantLinkedIn = is_array($candidate["participants_linkedin"] ?? null) ? $candidate["participants_linkedin"] : [];
    $image = $candidate["participant_image"]
        ?? $participantLinkedIn["image"]
        ?? $candidate["image"]
        ?? $candidate["profile_image"]
        ?? $candidate["avatar"]
        ?? "";
    if ($image !== "" && !preg_match('/^https?:\/\//i', (string)$image)) {
        $image = "https://api.coherta.com/images/" . ltrim((string)$image, "/");
    }
    $phone = $candidate["phonenumber"] ?? $candidate["phone_number"] ?? $candidate["phone"] ?? "";
    $cvUrl = $candidate["cv_url"] ?? ($cvId > 0 ? "https://api.coherta.com/recruiters/cv?id=" . $cvId : "");
    return [
        "id" => (string)$id,
        "participant_id" => $participantId !== null && $participantId !== "" ? (string)$participantId : "",
        "participant_linkedin_id" => (string)($candidate["participant_linkedin_id"] ?? $candidate["participants_linkedin_id"] ?? ""),
        "name" => $candidate["name"] ?? "",
        "title" => $candidate["title"] ?? "",
        "company" => $candidate["company_name"] ?? $candidate["company"] ?? "",
        "location" => $candidate["location"] ?? "",
        "image" => $image,
        "stage" => (int)($candidate["stage"] ?? 1),
        "time" => recruiter_format_date($candidate["updated_at"] ?? $candidate["created_at"] ?? null),
        "cv" => $cvUrl !== "" ? $cvUrl : "#",
        "cv_available" => $cvUrl !== "" || !empty($candidate["cv_available"]),
        "linkedin" => $candidate["linkedin_url"] ?? $candidate["linkedin"] ?? "#",
        "phone" => $phone,
        "fit" => (int)($candidate["fit"] ?? 0),
        "skills" => recruiter_skill_names_from_value($candidate["skills"] ?? $candidate["skill_names"] ?? []),
        "matched_skills" => recruiter_skill_names_from_value($candidate["matched_skills"] ?? $candidate["matched_skill_names"] ?? []),
        "note" => $candidate["note"] ?? "",
        "status" => empty($candidate["removed_at"]) ? "active" : "removed",
        "reply_after_auto_message" => (int)($candidate["stage"] ?? 0) === 4 || (($candidate["reply_status"] ?? "") === "replied"),
    ];
}

function recruiter_normalize_campaign(array $campaign, bool $isDetail = false): array {
    $primaryStage = (int)($campaign["primary_stage"] ?? 1);
    $stageCounts = [
        1 => (int)($campaign["stage_1_candidates"] ?? 0),
        2 => (int)($campaign["stage_2_candidates"] ?? 0),
        3 => (int)($campaign["stage_3_candidates"] ?? 0),
        4 => (int)($campaign["stage_4_candidates"] ?? $campaign["replies"] ?? 0),
    ];
    $candidates = array_map("recruiter_normalize_candidate", is_array($campaign["candidates"] ?? null) ? $campaign["candidates"] : []);
    if ($isDetail && $candidates) {
        $stageCounts = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
        foreach ($candidates as $candidate) {
            $stage = (int)$candidate["stage"];
            if (isset($stageCounts[$stage])) $stageCounts[$stage]++;
        }
    }

    return [
        "id" => (string)($campaign["recruiter_campaign_id"] ?? $campaign["id"] ?? ""),
        "campaign_id" => (string)($campaign["campaign_id"] ?? ""),
        "recruiter_campaign_id" => (string)($campaign["recruiter_campaign_id"] ?? $campaign["id"] ?? ""),
        "title" => $campaign["title"] ?? "",
        "owner" => $campaign["owner_name"] ?? "",
        "owner_id" => $campaign["owner_id"] ?? null,
        "created" => recruiter_format_date($campaign["created_at"] ?? null),
        "status" => recruiter_status_label($campaign["status"] ?? null),
        "progress" => min(100, max(0, $primaryStage * 25)),
        "primary_stage" => $primaryStage,
        "terms" => is_array($campaign["terms"] ?? null) ? $campaign["terms"] : [],
        "steps" => recruiter_normalize_steps(is_array($campaign["steps"] ?? null) ? $campaign["steps"] : [], $primaryStage),
        "candidates" => $candidates,
        "_stage_counts" => $stageCounts,
        "_active_candidates" => (int)($campaign["active_candidates"] ?? array_sum($stageCounts)),
    ];
}

function recruiter_api_campaigns($client): array {
    if (!$client || !method_exists($client, "get")) return [];
    $campaigns = $client->get("/recruiters");
    if (!is_array($campaigns)) return [];
    return array_map(fn($campaign) => is_array($campaign) ? recruiter_normalize_campaign($campaign) : [], $campaigns);
}

function recruiter_api_campaign($client, string $id): ?array {
    if (!$client || !method_exists($client, "get") || $id === "") return null;
    $campaign = $client->get("/recruiters", ["id" => $id]);
    return is_array($campaign) ? recruiter_normalize_campaign($campaign, true) : null;
}

$recruiterCampaigns = [];
