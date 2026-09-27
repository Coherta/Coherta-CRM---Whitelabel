<?php
declare(strict_types=1);

header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store, max-age=0');
?>
<!doctype html>
<html lang="da">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Coherta Extension Bridge</title>
    <style>
        html,
        body {
            width: 100%;
            height: 100%;
            margin: 0;
            background: transparent;
            color: #0f172a;
            font-family: Arial, sans-serif;
        }

        .bridge-status {
            display: grid;
            min-height: 100%;
            place-items: center;
            padding: 24px;
            text-align: center;
        }

        .bridge-status strong {
            display: block;
            margin-bottom: 8px;
        }
    </style>
</head>
<body data-coherta-extension-bridge="1">
<div class="bridge-status" id="bridgeStatus" hidden>
    <div>
        <strong>Tjekker Coherta Extension</strong>
        <span>Du sendes tilbage automatisk.</span>
    </div>
</div>
<script>
(function () {
    var EXTENSION_BRIDGE_CHANNEL = 'COHERTA_EXTENSION_BRIDGE';
    var EXTENSION_PAGE_SOURCE = 'coherta-website';
    var EXTENSION_SOURCE = 'coherta-extension';
    var STATUS_STORAGE_KEY = 'coherta_jobseeker_extension_bridge_status';
    var pending = {};
    var seq = 0;
    var params = new URLSearchParams(window.location.search);
    var topBridge = params.get('top_bridge') === '1' || params.get('mode') === 'detect';
    var bridgeStatus = document.getElementById('bridgeStatus');

    function requestCohertaExtension(action, payload, timeoutMs) {
        var requestId = 'coherta-public-bridge-' + Date.now() + '-' + (++seq);
        var waitMs = Number(timeoutMs) > 0 ? Number(timeoutMs) : 2500;

        return new Promise(function (resolve, reject) {
            var timer = window.setTimeout(function () {
                delete pending[requestId];
                reject(new Error('Coherta Extension svarede ikke.'));
            }, waitMs);

            pending[requestId] = {resolve: resolve, reject: reject, timer: timer};
            window.postMessage({
                channel: EXTENSION_BRIDGE_CHANNEL,
                source: EXTENSION_PAGE_SOURCE,
                requestId: requestId,
                action: action,
                payload: payload || {}
            }, window.location.origin);
        });
    }

    function handleExtensionMessage(event) {
        if (event.source !== window || event.origin !== window.location.origin) return;
        var message = event.data;
        if (!message || message.channel !== EXTENSION_BRIDGE_CHANNEL || message.source !== EXTENSION_SOURCE) return;
        var waiter = pending[message.requestId];
        if (!waiter) return;

        window.clearTimeout(waiter.timer);
        delete pending[message.requestId];
        if (message.ok) waiter.resolve(message);
        else waiter.reject(new Error(message.error || 'Coherta Extension svarede med en fejl.'));
    }

    function requestPresenceSignals() {
        try {
            window.postMessage({type: 'COHERTA_PLUGIN_PING', source: 'coherta-public-bridge'}, window.location.origin);
        } catch (error) {}
        try {
            document.dispatchEvent(new CustomEvent('coherta:plugin:ping', {detail: {source: 'coherta-public-bridge'}}));
            document.dispatchEvent(new CustomEvent('coherta-plugin-ping', {detail: {source: 'coherta-public-bridge'}}));
        } catch (error) {}
    }

    function safeReturnUrl() {
        var fallback = '/lp/find-din-naeste-arbejdsgiver/';
        var rawReturn = params.get('return') || fallback;
        try {
            var url = new URL(rawReturn, window.location.origin);
            if (url.origin !== window.location.origin) return fallback;
            return url.pathname + url.search + url.hash;
        } catch (error) {
            return fallback;
        }
    }

    function writeStatus(installed, status, error) {
        try {
            window.localStorage.setItem(STATUS_STORAGE_KEY, JSON.stringify({
                installed: !!installed,
                status: status || {},
                error: error ? String(error.message || error) : '',
                checked_at: Date.now()
            }));
        } catch (storageError) {}
    }

    function returnToCaller(delayMs) {
        if (!topBridge) return;
        window.setTimeout(function () {
            window.location.replace(safeReturnUrl());
        }, Number(delayMs) >= 0 ? Number(delayMs) : 180);
    }

    async function detectAndReturn() {
        if (bridgeStatus) bridgeStatus.hidden = false;
        requestPresenceSignals();
        try {
            await requestCohertaExtension('PING', {}, 3500);
            var status = await requestCohertaExtension('GET_STATUS', {}, 2500).catch(function () { return {}; });
            writeStatus(true, status, '');
            returnToCaller(120);
        } catch (error) {
            writeStatus(false, {}, error);
            returnToCaller(450);
        }
    }

    window.addEventListener('message', handleExtensionMessage);
    window.CohertaExtension = {
        request: requestCohertaExtension,
        ping: function () { return requestCohertaExtension('PING', {}, 2500); },
        getStatus: async function () {
            await requestCohertaExtension('PING', {}, 2500);
            return requestCohertaExtension('GET_STATUS', {}, 2500).catch(function () { return {}; });
        },
        login: function (sessionId) {
            return requestCohertaExtension('LOGIN_WITH_SESSION', {sessionId: sessionId}, 5000);
        },
        connectLinkedIn: function () {
            return requestCohertaExtension('CONNECT_LINKEDIN', {active: true, closeTab: true}, 45000);
        }
    };

    if (topBridge) detectAndReturn();
    else requestPresenceSignals();
}());
</script>
</body>
</html>
