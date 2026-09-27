<?php
require_once "./base.php";
$PageTitle = "Connect Your WhatsApp";
if (!isset($publicPage)) {
    $currentUser = $client->getCurrentUser();
    if (is_array($currentUser)) {
        $user = $currentUser;
    }
}

$connectors = isset($user["connectors"]) && is_array($user["connectors"]) ? $user["connectors"] : [];
$messageConnectors = isset($connectors["messages"]) && is_array($connectors["messages"]) ? $connectors["messages"] : [];

function coherta_whatsapp_connect_truthy($value): bool
{
    if (is_bool($value)) return $value;
    if (is_numeric($value)) return (int)$value === 1;

    $cleanValue = strtolower(trim((string)$value));
    return in_array($cleanValue, ["1", "true", "yes", "connected", "active", "healthy"], true);
}

function coherta_whatsapp_connector_connected($source): bool
{
    if (!is_array($source)) return coherta_whatsapp_connect_truthy($source);

    foreach (["status", "whatsapp_status", "connected", "whatsapp_connected", "is_whatsapp_connected"] as $field) {
        if (array_key_exists($field, $source) && coherta_whatsapp_connect_truthy($source[$field])) return true;
    }

    foreach ($source as $item) {
        if (is_array($item) && coherta_whatsapp_connector_connected($item)) return true;
    }

    return false;
}

$whatsappConnected = false;
$whatsappConnectorSources = [
    $connectors["whatsapp"] ?? null,
    $messageConnectors["whatsapp"] ?? null,
    $user["whatsapp"] ?? null,
];
foreach ($connectors as $key => $connector) {
    $normalizedKey = preg_replace('/[^a-z0-9]/', '', strtolower((string)$key));
    if ($normalizedKey === "whatsapp") $whatsappConnectorSources[] = $connector;
}
foreach ($whatsappConnectorSources as $whatsappConnectorSource) {
    if (coherta_whatsapp_connector_connected($whatsappConnectorSource)) {
        $whatsappConnected = true;
        break;
    }
}

include('partial/header.php');
?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .whatsapp-connect-card.is-connected {
            background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%);
            border-color: rgba(34, 197, 94, 0.34);
            box-shadow: 0 18px 44px rgba(22, 163, 74, 0.14);
        }
        .whatsapp-connect-card.is-connected .card-header {
            background: transparent;
            border-bottom-color: rgba(34, 197, 94, 0.18);
        }
        .whatsapp-method-card {
            border: 1px solid rgba(15, 23, 42, 0.12);
            border-radius: 8px;
            padding: 24px;
            background: #fff;
            min-height: 220px;
        }
        .whatsapp-method-card.is-connected {
            border-color: rgba(34, 197, 94, 0.34);
            background: rgba(240, 253, 244, 0.72);
        }
        .whatsapp-method-header {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 18px;
        }
        .whatsapp-method-icon {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            background: #25d366;
            flex: 0 0 auto;
            font-size: 24px;
        }
        .whatsapp-method-card h6 {
            margin: 0 0 6px;
            font-size: 16px;
            color: #111827;
        }
        .whatsapp-method-card p {
            margin: 0;
            color: #667085;
            line-height: 1.55;
        }
        .whatsapp-connected-state {
            display: inline-flex;
            align-items: center;
            gap: 14px;
            text-align: left;
            padding: 16px 18px;
            border-radius: 8px;
            color: #166534;
            background: rgba(34, 197, 94, 0.14);
            border: 1px solid rgba(34, 197, 94, 0.22);
        }
        .whatsapp-connected-state i {
            width: 36px;
            height: 36px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            background: #22c55e;
            flex: 0 0 auto;
        }
        .whatsapp-connected-state strong,
        .whatsapp-connected-state span {
            display: block;
        }
        .whatsapp-connected-state strong {
            color: #14532d;
            font-size: 15px;
            margin-bottom: 3px;
        }
        .whatsapp-connected-state span {
            color: #166534;
        }
        .whatsapp-pin-box {
            border: 1px dashed rgba(37, 211, 102, 0.6);
            border-radius: 8px;
            padding: 18px;
            background: rgba(236, 253, 245, 0.72);
        }
        .whatsapp-pin-code {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 48px;
            margin-top: 10px;
            padding: 0 18px;
            border-radius: 8px;
            background: #fff;
            border: 1px solid rgba(15, 23, 42, 0.12);
            color: #111827;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 0.08em;
        }
        .whatsapp-qr-code {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-top: 14px;
            padding: 12px;
            border-radius: 8px;
            background: #fff;
            border: 1px solid rgba(15, 23, 42, 0.12);
        }
        .whatsapp-qr-code img {
            display: block;
            width: min(240px, 68vw);
            height: auto;
        }
        .whatsapp-qr-code-text {
            max-width: 100%;
            overflow-wrap: anywhere;
            font-family: monospace;
            font-size: 13px;
            color: #111827;
        }
        .whatsapp-status-note {
            color: #667085;
            font-size: 13px;
            margin-top: 12px;
        }
        .whatsapp-method-choice-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }
        .whatsapp-method-choice {
            display: flex;
            align-items: center;
            gap: 14px;
            width: 100%;
            min-height: 92px;
            padding: 18px;
            text-align: left;
            border: 1px solid rgba(15, 23, 42, 0.12);
            border-radius: 8px;
            background: #fff;
            color: #111827;
            cursor: pointer;
            transition: border-color .16s ease, box-shadow .16s ease, transform .16s ease;
        }
        .whatsapp-method-choice:hover,
        .whatsapp-method-choice:focus-visible {
            border-color: rgba(37, 211, 102, 0.6);
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.08);
            transform: translateY(-1px);
        }
        .whatsapp-method-choice i {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            background: #25d366;
            flex: 0 0 auto;
            font-size: 18px;
        }
        .whatsapp-method-choice strong,
        .whatsapp-method-choice span {
            display: block;
        }
        .whatsapp-method-choice strong {
            margin-bottom: 4px;
            font-size: 15px;
        }
        .whatsapp-method-choice span {
            color: #667085;
            font-size: 13px;
            line-height: 1.4;
        }
        .whatsapp-phone-grid {
            display: grid;
            grid-template-columns: minmax(100px, 140px) minmax(0, 1fr);
            gap: 14px;
        }
        @media (max-width: 575.98px) {
            .whatsapp-method-choice-grid,
            .whatsapp-phone-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
<?php include('partial/loader.php') ?>

    <div class="page-wrapper compact-wrapper" id="pageWrapper">
        <?php include('partial/topbar.php');?>
        <div class="page-body-wrapper">
            <?php include('partial/sidebar.php');?>
            <div class="page-body">
                <?php include('partial/breadcrumb.php'); ?>
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card whatsapp-connect-card<?php echo $whatsappConnected ? ' is-connected' : ''; ?>" id="whatsappConnectCard">
                                <div class="card-header">
                                    <h5>Connect Your WhatsApp</h5>
                                </div>
                                <div class="card-body">
                                    <section class="whatsapp-method-card<?php echo $whatsappConnected ? ' is-connected' : ''; ?>" id="whatsappCredentialCard">
                                        <div class="whatsapp-method-header">
                                            <span class="whatsapp-method-icon"><i class="fab fa-whatsapp"></i></span>
                                            <div>
                                                <h6>Connect with phone number</h6>
                                                <p>Use your WhatsApp country code and phone number to connect your account to Coherta.</p>
                                            </div>
                                        </div>
                                        <div class="whatsapp-connected-state" id="whatsappConnectedState" <?php echo $whatsappConnected ? '' : 'hidden'; ?>>
                                            <i class="fas fa-check"></i>
                                            <div>
                                                <strong>WhatsApp is connected</strong>
                                                <span>Coherta can now use your WhatsApp connection.</span>
                                            </div>
                                        </div>
                                        <?php if(!$whatsappConnected) { ?>
                                            <p class="whatsapp-method-copy" id="whatsappMethodCopy">You will receive a PIN code after WhatsApp prepares the connection.</p>
                                            <div class="mt-3" id="whatsappMethodActions">
                                                <button class="btn btn-primary" id="connectWhatsAppBtn" type="button" onclick="openWhatsAppModal()">
                                                    <i class="fab fa-whatsapp"></i> Connect now
                                                </button>
                                            </div>
                                        <?php } ?>
                                    </section>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include('partial/footer.php');?>
        </div>
    </div>

<div class="modal fade" id="whatsappModal" tabindex="-1" aria-labelledby="whatsappModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="whatsappConnectForm" autocomplete="off">
        <div class="modal-header">
          <h5 class="modal-title" id="whatsappModalLabel">Connect Your WhatsApp</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="resetWhatsAppModal()"></button>
        </div>
        <div class="modal-body" id="whatsappModalBody"></div>
        <div class="modal-footer" id="whatsappModalFooter"></div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
let whatsappModal = null;
let bearerToken = document.body.getAttribute('session_id') || '';
if (bearerToken) {
    try {
        window.localStorage.setItem('session_id', bearerToken);
        window.localStorage.setItem('access_token', bearerToken);
        window.localStorage.setItem('coherta_auth_session_id', bearerToken);
        window.sessionStorage.setItem('access_token', bearerToken);
    } catch (error) {}
}

const WHATSAPP_CONNECT_ENDPOINT = 'https://api.coherta.com/apps/whatsapp/connect';
const whatsappConnectCard = document.getElementById('whatsappConnectCard');
const whatsappCredentialCard = document.getElementById('whatsappCredentialCard');
const whatsappConnectedState = document.getElementById('whatsappConnectedState');
const whatsappMethodCopy = document.getElementById('whatsappMethodCopy');
const whatsappMethodActions = document.getElementById('whatsappMethodActions');
let whatsappPolling = false;
let whatsappConnectMethod = '';
let lastCountryCode = '';
let lastPhoneNumber = '';

function headersJson() {
    return {'Content-Type': 'application/json', 'Authorization': 'Bearer ' + bearerToken};
}
function htmlEscape(value) {
    return String(value || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}
function normalizeCountryCode(value) {
    return String(value || '').trim().replace(/\s+/g, '');
}
function normalizePhoneNumber(value) {
    return String(value || '').trim().replace(/[^\d]/g, '');
}
function findNestedValue(source, keys) {
    if (!source || typeof source !== 'object') return '';
    for (const key of keys) {
        if (source[key] !== undefined && source[key] !== null && String(source[key]).trim() !== '') return source[key];
    }
    for (const childKey of ['data', 'result', 'request', 'connection', 'whatsapp']) {
        if (source[childKey] && typeof source[childKey] === 'object') {
            const nested = findNestedValue(source[childKey], keys);
            if (nested !== '') return nested;
        }
    }
    return '';
}
function getWhatsAppStatus(payload) {
    const value = findNestedValue(payload, ['status', 'whatsapp_status', 'connect_status']);
    const numeric = Number(value);
    return Number.isNaN(numeric) ? null : numeric;
}
function getWhatsAppPin(payload) {
    const directPin = findNestedValue(payload, ['pin_code', 'pinCode', 'pincode', 'pin', 'code', 'pairing_code', 'pairingCode']);
    if (directPin) return directPin;

    const message = findNestedValue(payload, ['status_message', 'detail', 'message']);
    const match = String(message || '').match(/PIN\s*code\s*:\s*([a-z0-9-]+)/i);
    return match ? match[1] : '';
}
function getWhatsAppQrCode(payload) {
    return findNestedValue(payload, ['qr_code', 'qrCode', 'qrcode', 'qr', 'qr_url', 'qrUrl', 'qr_image', 'qrImage']);
}
function whatsappMethodChoiceMarkup() {
    return `
        <div class="mb-3"><strong>How would you like to connect?</strong></div>
        <div class="whatsapp-method-choice-grid">
            <button type="button" class="whatsapp-method-choice" onclick="selectWhatsAppMethod('phone')">
                <i class="fas fa-phone"></i>
                <span><strong>By Phone Number</strong><span>Use your country code and phone number.</span></span>
            </button>
            <button type="button" class="whatsapp-method-choice" onclick="connectWhatsAppByQrCode()">
                <i class="fas fa-qrcode"></i>
                <span><strong>QR Code</strong><span>Start the QR connection immediately.</span></span>
            </button>
        </div>
    `;
}
function whatsappFieldsMarkup() {
    return `
        <div class="whatsapp-phone-grid">
            <div class="mb-3">
                <label for="whatsappCountryCode" class="form-label">Country code</label>
                <input type="text" class="form-control" id="whatsappCountryCode" name="country_code" autocomplete="tel-country-code" inputmode="tel" placeholder="+45" value="${htmlEscape(lastCountryCode)}" required>
            </div>
            <div class="mb-3">
                <label for="whatsappPhoneNumber" class="form-label">Phone number</label>
                <input type="tel" class="form-control" id="whatsappPhoneNumber" name="phone_number" autocomplete="tel-national" inputmode="tel" placeholder="12345678" value="${htmlEscape(lastPhoneNumber)}" required>
            </div>
        </div>
    `;
}
function showWhatsAppMethodChoice() {
    whatsappConnectMethod = '';
    document.getElementById('whatsappModalBody').innerHTML = whatsappMethodChoiceMarkup();
    document.getElementById('whatsappModalFooter').innerHTML = '';
}
function showWhatsAppPhoneFields() {
    whatsappConnectMethod = 'phone';
    document.getElementById('whatsappModalBody').innerHTML = whatsappFieldsMarkup();
    document.getElementById('whatsappModalFooter').innerHTML = `
        <button type="submit" class="btn btn-primary"><i class="fab fa-whatsapp"></i> Connect now</button>
    `;
}
function selectWhatsAppMethod(method) {
    if (whatsappPolling) return;
    if (method === 'phone') showWhatsAppPhoneFields();
}
function setWhatsAppLoading(message) {
    document.getElementById('whatsappModalBody').innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary mb-3" role="status"></div>
            <p>${htmlEscape(message)}</p>
        </div>
    `;
    document.getElementById('whatsappModalFooter').innerHTML = '';
}
function setWhatsAppPin(pinCode) {
    const pin = htmlEscape(pinCode || 'Waiting for PIN code');
    document.getElementById('whatsappModalBody').innerHTML = `
        <div class="whatsapp-pin-box text-center">
            <div class="mb-1"><strong>Enter this PIN code in WhatsApp</strong></div>
            <div class="whatsapp-pin-code">${pin}</div>
            <div class="whatsapp-status-note">Keep this window open. Coherta will finish automatically when WhatsApp confirms the connection.</div>
        </div>
    `;
    document.getElementById('whatsappModalFooter').innerHTML = '';
}
function whatsappQrImageSource(qrCode) {
    const qr = String(qrCode || '').trim();
    if (!qr) return '';
    if (/^(data:image\/|https?:\/\/)/i.test(qr)) return qr;
    if (/^<svg[\s>]/i.test(qr)) return 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(qr);
    if (/^[a-z0-9+/=\r\n]+$/i.test(qr) && qr.replace(/\s+/g, '').length > 120) {
        return 'data:image/png;base64,' + qr.replace(/\s+/g, '');
    }
    return '';
}
function renderQrCodeText(qr) {
    const qrTarget = document.getElementById('whatsappQrRender');
    if (!qrTarget || !qr) return;
    if (window.QRCode) {
        qrTarget.innerHTML = '';
        new QRCode(qrTarget, {
            text: qr,
            width: 240,
            height: 240,
            correctLevel: QRCode.CorrectLevel.M
        });
        return;
    }
    qrTarget.innerHTML = `<div class="whatsapp-qr-code-text">${htmlEscape(qr)}</div>`;
}
function setWhatsAppQrCode(qrCode) {
    const qr = String(qrCode || '').trim();
    const imageSource = whatsappQrImageSource(qr);
    const qrMarkup = imageSource
        ? `<div class="whatsapp-qr-code"><img src="${htmlEscape(imageSource)}" alt="WhatsApp QR code"></div>`
        : `<div class="whatsapp-qr-code" id="whatsappQrRender"><div class="whatsapp-qr-code-text">${htmlEscape(qr || 'Waiting for QR code')}</div></div>`;
    document.getElementById('whatsappModalBody').innerHTML = `
        <div class="whatsapp-pin-box text-center">
            <div class="mb-1"><strong>Scan this QR code in WhatsApp</strong></div>
            ${qrMarkup}
            <div class="whatsapp-status-note">Keep this window open. Coherta will finish automatically when WhatsApp confirms the connection.</div>
        </div>
    `;
    document.getElementById('whatsappModalFooter').innerHTML = '';
    if (!imageSource) renderQrCodeText(qr);
}
function setWhatsAppConnected() {
    whatsappConnectCard?.classList.add('is-connected');
    whatsappCredentialCard?.classList.add('is-connected');
    if (whatsappConnectedState) whatsappConnectedState.hidden = false;
    if (whatsappMethodCopy) whatsappMethodCopy.hidden = true;
    if (whatsappMethodActions) whatsappMethodActions.hidden = true;
    document.getElementById('whatsappModalBody').innerHTML = `
        <div class="text-center">
            <i class="fas fa-check-circle text-success" style="font-size:3rem;"></i>
            <h5 class="mt-3">Your WhatsApp account is now connected</h5>
        </div>
    `;
    document.getElementById('whatsappModalFooter').innerHTML = '<button type="button" class="btn btn-primary" data-bs-dismiss="modal">Done</button>';
}
function setWhatsAppError(message) {
    const retryMarkup = whatsappConnectMethod === 'phone' ? whatsappFieldsMarkup() : whatsappMethodChoiceMarkup();
    const retryFooter = whatsappConnectMethod === 'phone'
        ? '<button type="submit" class="btn btn-primary"><i class="fab fa-whatsapp"></i> Connect now</button>'
        : '';
    document.getElementById('whatsappModalBody').innerHTML = `
        <div class="alert alert-danger" role="alert">${htmlEscape(message || 'WhatsApp could not be connected. Please try again.')}</div>
        ${retryMarkup}
    `;
    document.getElementById('whatsappModalFooter').innerHTML = retryFooter;
}
async function requestWhatsAppStatus() {
    const response = await fetch(WHATSAPP_CONNECT_ENDPOINT, {method: 'GET', headers: headersJson()});
    const text = await response.text();
    const payload = text ? JSON.parse(text) : {};
    if (!response.ok) throw new Error(payload.detail || payload.message || 'WhatsApp request failed.');
    return payload;
}
async function startWhatsAppConnection(payload, loadingMessage) {
    whatsappPolling = true;
    setWhatsAppLoading(loadingMessage || 'We are starting your WhatsApp connection.');
    try {
        const response = await fetch(WHATSAPP_CONNECT_ENDPOINT, {
            method: 'POST',
            headers: headersJson(),
            body: JSON.stringify(payload)
        });
        const text = await response.text();
        const responsePayload = text ? JSON.parse(text) : {};
        if (!response.ok) throw new Error(responsePayload.detail || responsePayload.message || 'WhatsApp could not be connected.');
        await pollWhatsAppConnection(responsePayload);
    } catch (error) {
        setWhatsAppError(error.message || 'WhatsApp could not be connected. Please try again.');
    } finally {
        whatsappPolling = false;
    }
}
async function connectWhatsAppByQrCode() {
    if (whatsappPolling) return;
    whatsappConnectMethod = 'qr';
    await startWhatsAppConnection({status: 6}, 'We are starting your WhatsApp QR code connection.');
}
async function pollWhatsAppConnection(initialPayload) {
    let codeShown = false;
    let qrShown = false;
    for (let i = 0; i < 180; i++) {
        const payload = i === 0 && initialPayload ? initialPayload : await requestWhatsAppStatus();
        const status = getWhatsAppStatus(payload);
        const qrCode = getWhatsAppQrCode(payload);
        const pinCode = getWhatsAppPin(payload);

        if (status === 1) {
            setWhatsAppConnected();
            return;
        }

        if (qrCode) {
            if (!qrShown) setWhatsAppQrCode(qrCode);
            qrShown = true;
            codeShown = true;
        } else if (pinCode) {
            setWhatsAppPin(pinCode);
            codeShown = true;
        } else if (status === 2) {
            setWhatsAppPin('');
            codeShown = true;
        } else if (!codeShown) {
            setWhatsAppLoading('Preparing your WhatsApp connection. Please wait a moment...');
        }

        await new Promise(resolve => setTimeout(resolve, 2000));
    }
    setWhatsAppError('WhatsApp is taking longer than expected. Please try again.');
}

document.addEventListener('DOMContentLoaded', function() {
    whatsappModal = new bootstrap.Modal(document.getElementById('whatsappModal'));
    resetWhatsAppModal();
    document.getElementById('whatsappConnectForm').onsubmit = async function(e) {
        e.preventDefault();
        if (whatsappPolling) return;
        if (whatsappConnectMethod !== 'phone') {
            showWhatsAppMethodChoice();
            return;
        }
        lastCountryCode = normalizeCountryCode(document.getElementById('whatsappCountryCode').value);
        lastPhoneNumber = normalizePhoneNumber(document.getElementById('whatsappPhoneNumber').value);
        if (!/^\+?\d{1,4}$/.test(lastCountryCode)) {
            setWhatsAppError('Please enter a valid country code, for example +45.');
            return;
        }
        if (!/^\d{5,15}$/.test(lastPhoneNumber)) {
            setWhatsAppError('Please enter a valid phone number without spaces.');
            return;
        }
        await startWhatsAppConnection({
            country_code: lastCountryCode,
            phone_number: lastPhoneNumber,
            status: 5
        }, 'We are starting your WhatsApp phone number connection.');
    };
});
function openWhatsAppModal() {
    resetWhatsAppModal();
    whatsappModal.show();
}
function resetWhatsAppModal() {
    if (whatsappPolling) return;
    showWhatsAppMethodChoice();
}
</script>
<?php include('partial/scripts.php'); ?>
<?php include('partial/footer-end.php'); ?>
