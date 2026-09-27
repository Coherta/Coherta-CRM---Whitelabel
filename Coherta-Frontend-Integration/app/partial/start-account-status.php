<?php

function coherta_account_start_required($subscription, ?int $companyId = null): bool
{
    // Unknown status must never be mistaken for an active subscription.
    if (!is_array($subscription) || !is_string($subscription['subscription_id'] ?? null)) return true;
    if ($companyId !== null && ($subscription['company_id'] ?? null) !== $companyId) return true;
    if (($subscription['source'] ?? '') === 'crm_company_stripe') {
        return ($subscription['active'] ?? false) !== true || trim($subscription['subscription_id']) === ''
            || ($subscription['stripe_renewal_current'] ?? false) !== true;
    }
    if (($subscription['source'] ?? '') !== 'hubspot') return true;

    // A known empty HubSpot field requires account start, regardless of local renewal dates.
    return trim($subscription['subscription_id']) === '';
}
