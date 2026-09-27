<?php
require_once __DIR__ . '/partial/billing-access.php';
require_once __DIR__ . '/../pricing.php';
session_start();
if (!function_exists('coherta_price_period_key')) {
    function coherta_price_period_key($price) {
        $interval = $price['recurring']['interval'] ?? 'one_time';
        $count = (int)($price['recurring']['interval_count'] ?? 1);
        return $interval . '_' . $count;
    }
}

if (!function_exists('coherta_pick_prices_by_period')) {
    function coherta_pick_prices_by_period($prices) {
        $grouped = [];
        foreach (($prices ?? []) as $price) {
            if (empty($price['id']) || empty($price['active'])) {
                continue;
            }
            $grouped[coherta_price_period_key($price)] = $price;
        }
        return $grouped;
    }
}

if (!function_exists('coherta_pick_default_period_key')) {
    function coherta_pick_default_period_key($pricesByPeriod) {
        if (isset($pricesByPeriod['year_1'])) {
            $yearlyAmount = (int)($pricesByPeriod['year_1']['unit_amount'] ?? 0);
            if ($yearlyAmount > 1000000 && isset($pricesByPeriod['month_1'])) {
                return 'month_1';
            }
            return 'year_1';
        }
        if (isset($pricesByPeriod['month_1'])) {
            return 'month_1';
        }
        foreach ($pricesByPeriod as $key => $price) {
            return $key;
        }
        return null;
    }
}

if (!function_exists('coherta_get_selected_price')) {
    function coherta_get_selected_price($bundle, $selectedPeriodKey = null, $selectedPriceId = null) {
        $pricesByPeriod = coherta_pick_prices_by_period($bundle['prices'] ?? []);
        if ($selectedPriceId) {
            foreach (($bundle['prices'] ?? []) as $price) {
                if (!empty($price['active']) && ($price['id'] ?? null) === $selectedPriceId) {
                    return [$price, $pricesByPeriod, coherta_price_period_key($price)];
                }
            }
        }
        if ($selectedPeriodKey && isset($pricesByPeriod[$selectedPeriodKey])) {
            return [$pricesByPeriod[$selectedPeriodKey], $pricesByPeriod, $selectedPeriodKey];
        }
        $defaultKey = coherta_pick_default_period_key($pricesByPeriod);
        return [$defaultKey ? $pricesByPeriod[$defaultKey] : null, $pricesByPeriod, $defaultKey];
    }
}

class ApiClient
{
    private string $baseUrl;
    private ?string $token = null;
    private int $timeout;

    private ?array $currentUser = null;
    private ?int $currentUserFetchedAt = null;

    public ?array $accessRights = null;

    /**
     * @param int    $timeout  cURL timeout in seconds
     */
    public function __construct(int $timeout = 60)
    {
        $this->baseUrl = rtrim("https://api.coherta.com", '/');
        $this->timeout = $timeout;
        if(isset($_SESSION["token"]) && !is_null($_SESSION["token"]) && strlen($_SESSION["token"]) > 1) {
            $this->token = $_SESSION["token"];
            $this->getAccessRights();
        }
    }

    /**
     * Set the bearer token to be used for all requests.
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    private function base64url_decode(string $data): string {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }

    /**
     * Make a generic HTTP request.
     *
     * @param string $method        HTTP method (GET, POST, PUT, DELETE, etc.)
     * @param string $endpoint      API endpoint path (e.g. '/user/me')
     * @param array|null $data      Data to send (query for GET, JSON body for others)
     * @param array      $extraHeaders  Additional headers
     *
     * @return array|null  Decoded JSON response, or null on failure.
     */
    private function request(string $method, string $endpoint, ?array $data = null, array $extraHeaders = []): ?array
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        $headers = [
            'Accept: application/json',
        ];
        if ($this->token) {
            $headers[] = 'Authorization: Bearer ' . $this->token;
        }

        // Append extra headers
        foreach ($extraHeaders as $h) {
            $headers[] = $h;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if (strtoupper($method) === 'GET' && $data) {
            $url .= '?' . http_build_query($data);
        } elseif ($data !== null) {
            $json = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        $body     = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err      = curl_error($ch);
        curl_close($ch);
        if ($err || $httpCode < 200 || $httpCode >= 300) {
            return null;
        }
        $decoded = json_decode($body, true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Shortcut for GET requests.
     */
    public function get(string $endpoint, array $params = [], array $headers = []): ?array
    {
        return $this->request('GET', $endpoint, $params, $headers);
    }

    /**
     * Shortcut for POST requests.
     */
    public function post(string $endpoint, array $data = [], array $headers = []): ?array
    {
        return $this->request('POST', $endpoint, $data, $headers);
    }

    /**
     * Shortcut for PUT requests.
     */
    public function put(string $endpoint, array $data = [], array $headers = []): ?array
    {
        return $this->request('PUT', $endpoint, $data, $headers);
    }

    /**
     * Shortcut for PATCH requests.
     */
    public function patch(string $endpoint, array $data = [], array $headers = []): ?array
    {
        return $this->request('PATCH', $endpoint, $data, $headers);
    }


    /**
     * Shortcut for DELETE requests.
     */
    public function delete(string $endpoint, array $data = [], array $headers = []): ?array
    {
        return $this->request('DELETE', $endpoint, $data, $headers);
    }

    /**
     * Get current user info from /user/me
     */
    public function getCurrentUser(): ?array
    {
        $now = time();
        // If currentUser is set and fetched within the last 24 hours, return it
        if ($this->currentUser && $this->currentUserFetchedAt && ($now - $this->currentUserFetchedAt) < 86400) {
            return $this->currentUser;
        }
        //$this->renewToken();
        $this->currentUser = $this->get('/users/me/');
        $this->currentUserFetchedAt = $now;
        return $this->currentUser;
    }

    public function getColleagueImage($colleague_id): string
    {
        if (!isset($this->currentUser['colleagues']) || !is_array($this->currentUser['colleagues'])) {
            return '/app/assets/images/user/user.png';
        }
        foreach ($this->currentUser['colleagues'] as $colleague) {
            if ($colleague['id'] == $colleague_id) {
                return $colleague['img_key']
                    ? 'https://api.coherta.com/images/' . $colleague['img_key']
                    : '/app/assets/images/user/user.png';
            }
        }
        return '/app/assets/images/user/user.png';
    }

    public function getAccessRights(): array
    {
        $this->accessRights = $this->get('/users/me/companies/access_rights?');
        return $this->accessRights ?? [];
    }


    public function getColleagueName($colleague_id): string
    {
        foreach ($this->currentUser['colleagues'] as $colleague) {
            if ($colleague['id'] == $colleague_id) {
                return $colleague['full_name'] ?: 'Unknown User';
            }
        }
        return 'Unknown User';
    }

    public function getCurrentCompanies(): ?array
    {
        return $this->get('/users/me/companies');
    }
    public function getCurrentPipelines(): ?array
    {
        return $this->get('/pipeline/list');
    }

    /**
     * Check if the user is logged in by validating the Bearer token.
     */
    public function isLoggedIn(): bool
    {
        return $this->getCurrentUser() !== null;
    }

    public function resetPassword(string $username): bool
    {
        $url = $this->baseUrl . '/users/password/forgot';
        $postFields = http_build_query([
            'email'   => $username,
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postFields,
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'Content-Type: application/x-www-form-urlencoded',
            ],
        ]);

        $body     = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err      = curl_error($ch);
        curl_close($ch);
        if ($err || $httpCode !== 200) {
            return false;
        }

        return true;
    }
    public function fetchToken(string $username, string $password, string $endpoint = '/token'): bool
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        $postFields = http_build_query([
            'grant_type' => 'password',
            'username'   => $username,
            'password'   => $password,
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postFields,
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'Content-Type: application/x-www-form-urlencoded',
            ],
        ]);

        $body     = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err      = curl_error($ch);
        curl_close($ch);

        if ($err || $httpCode !== 200) {
            return false;
        }

        $data = json_decode($body, true);
        if (isset($data['access_token'])) {
            $this->set_access_token($data['access_token']);
            return $data['access_token'];
        }

        return false;
    }

    public function renewToken(string $endpoint = '/token/renew'): bool
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        $postFields = http_build_query([
            'grant_type' => 'jwt'
        ]);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postFields,
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'Content-Type: application/x-www-form-urlencoded',
                "Authorization: Bearer ".$_SESSION["token"]
            ],
        ]);

        $body     = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err      = curl_error($ch);
        curl_close($ch);
        if ($err || $httpCode !== 200) {
            return false;
        }

        $data = json_decode($body, true);
        if (isset($data['access_token'])) {
            $this->set_access_token($data['access_token']);
            return $data['access_token'];
        }
        return false;
    }
    function set_access_token(string $token): true
    {
        $this->token = $token;
        $_SESSION["token"] = $token;
        return true;
    }
}

class Utils {
    public function active_record($lead_data) {
        $the_entry = null;
        foreach ($lead_data as $entry) {
            if (empty($entry['until_date'])) {
                $the_entry = $entry;
                break;
            }
        }
        return $the_entry;
    }
}

$client = new ApiClient();
$utils = new Utils();

$stages     = [
    1 => ["icon"=> "target", "title" => "Initial"],
    2 => ["icon"=> "mail", "title" => "Contacted"],
    3 => ["icon"=> "check-circle", "title" => "Qualified"],
    4 => ["icon"=> "file-text", "title" => "Proposal"],
    5 => ["icon"=> "message-square", "title" => "Negotiation"],
];

$user_image = "/app/assets/images/user/user.png";
