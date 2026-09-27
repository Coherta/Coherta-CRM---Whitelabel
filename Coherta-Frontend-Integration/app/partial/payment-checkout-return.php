<?php
// Retained for old callers. A legacy checkout URL never grants or suppresses access.
// Revolut returns are verified by the authenticated billing endpoints.
function coherta_payment_checkout_pending($unused, array $company, array $query, array &$session): bool
{
    unset($session['payment_checkout_pending']);
    return false;
}
