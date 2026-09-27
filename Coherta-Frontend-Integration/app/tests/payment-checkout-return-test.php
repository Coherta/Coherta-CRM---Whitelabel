<?php
require_once __DIR__ . '/../partial/payment-checkout-return.php';
$provider = new class {
    public function __call($name, $arguments) { throw new RuntimeException('Legacy provider must not be queried.'); }
};
foreach ([[], ['stripe_customer_id' => 'cus_old', 'stripe_subscription' => ''], ['stripe_customer_id' => 'cus_old', 'stripe_subscription' => 'sub_old']] as $company) {
    $session = ['payment_checkout_pending' => ['cus_old' => 'cs_legacy']];
    if (coherta_payment_checkout_pending($provider, $company, ['session_id' => 'cs_legacy'], $session)) throw new RuntimeException('A legacy return cannot suppress the payment prompt.');
    if (isset($session['payment_checkout_pending'])) throw new RuntimeException('Legacy pending state must be cleared.');
}
echo "PASS: legacy returns never query Stripe or grant access.\n";
