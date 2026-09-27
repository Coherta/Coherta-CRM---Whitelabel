<?php
require_once "./base.php";

$requestedUrl = $_SERVER["REQUEST_URI"] ?? "/app/codex";

if (!$client->isLoggedIn()) {
    header("Location: /app/login?url=" . urlencode($requestedUrl));
    exit;
}

$sessionToken = (string)($_SESSION["token"] ?? "");
$user = $client->getCurrentUser() ?? [];
$displayName = trim((string)($user["full_name"] ?? $user["name"] ?? ""));
$email = (string)($user["email"] ?? $user["username"] ?? "");

function codex_is_allowed_loopback_redirect(string $redirectUri): bool
{
    $parts = parse_url($redirectUri);

    if (!is_array($parts)) {
        return false;
    }

    $scheme = strtolower((string)($parts["scheme"] ?? ""));
    $host = strtolower((string)($parts["host"] ?? ""));
    $path = (string)($parts["path"] ?? "");

    return $scheme === "http"
        && in_array($host, ["localhost", "127.0.0.1"], true)
        && $path === "/callback";
}

function codex_is_allowed_mcp_return_url(string $returnUrl): bool
{
    $parts = parse_url($returnUrl);

    if (!is_array($parts)) {
        return false;
    }

    $scheme = strtolower((string)($parts["scheme"] ?? ""));
    $host = strtolower((string)($parts["host"] ?? ""));
    $path = (string)($parts["path"] ?? "");

    return $scheme === "https"
        && in_array($host, ["mcp.coherta.com", "dev.mcp.coherta.com"], true)
        && $path === "/codex/callback";
}

function codex_is_allowed_oauth_callback(string $callbackUrl): bool
{



    return codex_is_allowed_loopback_redirect($callbackUrl)
        || codex_is_allowed_mcp_return_url($callbackUrl);
}

function codex_redirect_with_query(string $redirectUri, array $params): string
{
    $separator = strpos($redirectUri, "?") === false ? "?" : "&";

    return $redirectUri . $separator . http_build_query($params, "", "&", PHP_QUERY_RFC3986);
}

$oauthCallbackUrl = (string)($_GET["redirect_uri"] ?? $_GET["return_url"] ?? "");
if ($oauthCallbackUrl !== "") {
    if (!codex_is_allowed_oauth_callback($oauthCallbackUrl)) {
        http_response_code(400);
        header("Content-Type: text/plain; charset=UTF-8");
        echo "Invalid OAuth callback URL. Claude Code must use http://localhost:<port>/callback, http://127.0.0.1:<port>/callback, or the Coherta MCP callback.";
        exit;
    }

    if (isset($_GET["return_url"])) {
        $callbackParams = [
            "success" => "1",
            "provider" => "coherta",
            "session_id" => $sessionToken,
            "token" => $sessionToken,
            "access_token" => $sessionToken,
            "token_type" => "Bearer",
            "scope" => (string)($_GET["scope"] ?? "mcp:tools"),
        ];

        if (isset($_GET["state"])) {
            $callbackParams["state"] = (string)$_GET["state"];
        }

        header("Location: " . codex_redirect_with_query($oauthCallbackUrl, $callbackParams), true, 302);
        exit;
    }

    $authCode = bin2hex(random_bytes(32));
    $_SESSION["codex_oauth_codes"][$authCode] = [
        "token" => $sessionToken,
        "user_id" => $user["id"] ?? null,
        "created_at" => time(),
        "expires_at" => time() + 300,
        "client_id" => (string)($_GET["client_id"] ?? ""),
        "code_challenge" => (string)($_GET["code_challenge"] ?? ""),
        "code_challenge_method" => (string)($_GET["code_challenge_method"] ?? ""),
        "redirect_uri" => $oauthCallbackUrl,
    ];

    $callbackParams = ["code" => $authCode];
    if (isset($_GET["state"])) {
        $callbackParams["state"] = (string)$_GET["state"];
    }

    header("Location: " . codex_redirect_with_query($oauthCallbackUrl, $callbackParams), true, 302);
    exit;
}

$codexSession = [
    "success" => true,
    "provider" => "coherta",
    "session_id" => $sessionToken,
    "token" => $sessionToken,
    "token_type" => "Bearer",
    "user" => [
        "id" => $user["id"] ?? null,
        "name" => $displayName,
        "email" => $email,
    ],
    "issued_at" => gmdate("c"),
];

$jsonFlags = JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
$codexSessionJson = json_encode($codexSession, $jsonFlags);

function codex_h(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="coherta-codex-auth" content="success">
    <meta name="coherta-session-id" content="<?php echo codex_h($sessionToken); ?>">
    <link rel="icon" href="/app/assets/images/favicon.png" type="image/x-icon">
    <title>Coherta - Codex Connected</title>
    <link rel="stylesheet" type="text/css" href="/app/assets/css/vendors/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="/app/assets/css/style.css">
    <style>
        :root {
            --codex-bg: #f6f8fb;
            --codex-panel: #ffffff;
            --codex-text: #172033;
            --codex-muted: #667085;
            --codex-line: #d9e0ea;
            --codex-accent: #0d6efd;
            --codex-accent-dark: #0b5ed7;
            --codex-success: #12b76a;
        }

        body {
            min-height: 100vh;
            margin: 0;
            background: var(--codex-bg);
            color: var(--codex-text);
            font-family: Inter, Rubik, Arial, sans-serif;
        }

        .codex-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 18px;
        }

        .codex-panel {
            width: min(680px, 100%);
            background: var(--codex-panel);
            border: 1px solid var(--codex-line);
            border-radius: 8px;
            box-shadow: 0 18px 50px rgba(23, 32, 51, 0.08);
            padding: 32px;
        }

        .codex-logo {
            width: 152px;
            height: auto;
            margin-bottom: 28px;
        }

        .codex-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 30px;
            padding: 0 12px;
            border: 1px solid rgba(18, 183, 106, 0.3);
            border-radius: 999px;
            color: #087443;
            background: #ecfdf3;
            font-size: 13px;
            font-weight: 700;
        }

        .codex-status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--codex-success);
        }

        h1 {
            margin: 22px 0 10px;
            font-size: 28px;
            line-height: 1.2;
            font-weight: 800;
            letter-spacing: 0;
        }

        .codex-copy {
            max-width: 560px;
            color: var(--codex-muted);
            font-size: 15px;
            line-height: 1.6;
            margin: 0 0 24px;
        }

        .codex-session-box {
            border: 1px solid var(--codex-line);
            border-radius: 8px;
            overflow: hidden;
            background: #fbfcfe;
        }

        .codex-session-row {
            display: grid;
            grid-template-columns: 120px minmax(0, 1fr);
            gap: 16px;
            padding: 14px 16px;
            border-top: 1px solid var(--codex-line);
            font-size: 14px;
        }

        .codex-session-row:first-child {
            border-top: 0;
        }

        .codex-session-label {
            color: var(--codex-muted);
            font-weight: 700;
        }

        .codex-session-value {
            min-width: 0;
            overflow-wrap: anywhere;
            font-family: Consolas, "Liberation Mono", monospace;
            color: var(--codex-text);
        }

        .codex-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 24px;
        }

        .codex-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 0 16px;
            border-radius: 6px;
            border: 1px solid var(--codex-line);
            background: #fff;
            color: var(--codex-text);
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .codex-button-primary {
            border-color: var(--codex-accent);
            background: var(--codex-accent);
            color: #fff;
        }

        .codex-button-primary:hover {
            background: var(--codex-accent-dark);
            color: #fff;
        }

        .codex-machine-note {
            margin-top: 18px;
            color: var(--codex-muted);
            font-size: 13px;
            line-height: 1.5;
        }

        @media (max-width: 560px) {
            .codex-panel {
                padding: 24px;
            }

            .codex-session-row {
                grid-template-columns: 1fr;
                gap: 6px;
            }
        }
    </style>
</head>
<body
    data-codex-auth="success"
    data-session-id="<?php echo codex_h($sessionToken); ?>"
    session_id="<?php echo codex_h($sessionToken); ?>"
>
<main class="codex-page">
    <section
        class="codex-panel"
        id="coherta-codex-auth"
        data-status="success"
        data-session-id="<?php echo codex_h($sessionToken); ?>"
        data-token-type="Bearer"
    >
        <img class="codex-logo" src="/assets/img/logo/logo.png?w=300" alt="Coherta">
        <div class="codex-status"><span class="codex-status-dot" aria-hidden="true"></span> Codex login connected</div>
        <h1>Your Coherta session is ready</h1>
        <p class="codex-copy">
            You can return to Codex. This page exposes the current Coherta session for the MCP authentication handoff.
        </p>

        <div class="codex-session-box" aria-label="Coherta session details">
            <div class="codex-session-row">
                <div class="codex-session-label">Status</div>
                <div class="codex-session-value">success</div>
            </div>
            <div class="codex-session-row">
                <div class="codex-session-label">Token type</div>
                <div class="codex-session-value">Bearer</div>
            </div>
            <div class="codex-session-row">
                <div class="codex-session-label">Session</div>
                <div class="codex-session-value" id="coherta-session-token"><?php echo codex_h($sessionToken); ?></div>
            </div>
        </div>

        <div class="codex-actions">
            <button class="codex-button codex-button-primary" id="copy-session-button" type="button">Copy session</button>
            <a class="codex-button" href="/app">Open Coherta</a>
        </div>

        <p class="codex-machine-note">
            Machine-readable payload is available in <code>#coherta-codex-session</code>, <code>window.cohertaCodexSession</code>, and <code>localStorage.coherta_codex_session</code>.
        </p>

        <script id="coherta-codex-session" type="application/json"><?php echo $codexSessionJson; ?></script>
    </section>
</main>
<script>
    (function () {
        const payload = <?php echo $codexSessionJson; ?>;
        window.cohertaCodexSession = payload;

        try {
            localStorage.setItem('coherta_codex_session', JSON.stringify(payload));
            localStorage.setItem('session_id', payload.session_id);
            localStorage.setItem('access_token', payload.session_id);
            sessionStorage.setItem('coherta_codex_session', JSON.stringify(payload));
            sessionStorage.setItem('session_id', payload.session_id);
        } catch (error) {}

        try {
            window.dispatchEvent(new CustomEvent('coherta:codex-session', { detail: payload }));
        } catch (error) {}

        try {
            if (window.opener && window.opener !== window) {
                window.opener.postMessage({ type: 'coherta:codex-session', payload: payload }, window.location.origin);
            }
            if (window.parent && window.parent !== window) {
                window.parent.postMessage({ type: 'coherta:codex-session', payload: payload }, window.location.origin);
            }
        } catch (error) {}

        const copyButton = document.getElementById('copy-session-button');
        if (copyButton) {
            copyButton.addEventListener('click', async function () {
                try {
                    await navigator.clipboard.writeText(payload.session_id);
                    copyButton.textContent = 'Copied';
                    setTimeout(function () {
                        copyButton.textContent = 'Copy session';
                    }, 1800);
                } catch (error) {
                    const token = document.getElementById('coherta-session-token');
                    if (token) {
                        const range = document.createRange();
                        range.selectNodeContents(token);
                        const selection = window.getSelection();
                        selection.removeAllRanges();
                        selection.addRange(range);
                    }
                }
            });
        }
    })();
</script>
</body>
</html>
