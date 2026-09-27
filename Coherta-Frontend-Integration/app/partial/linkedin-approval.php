<link rel="stylesheet" href="/app/assets/css/linkedin-approval.css?v=<?= filemtime(dirname(__DIR__) . '/assets/css/linkedin-approval.css') ?>">
    <dialog id="ios-linkedin-approval" class="ios-approval-modal" lang="en" aria-labelledby="ios-approval-title" aria-describedby="ios-approval-description">
        <button id="ios-approval-close" class="ios-approval-close" type="button" aria-label="Close approval instructions" autofocus>×</button>
        <div class="ios-approval-layout">
            <div class="ios-approval-copy">
                <p class="ios-eyebrow">ONE QUICK CHECK ON YOUR PHONE</p>
                <h2 id="ios-approval-title">Open your LinkedIn App and Approve the Request</h2>
                <p id="ios-approval-description">LinkedIn needs you to confirm this sign-in. Grab your phone and follow these steps.</p>
                <ol class="ios-approval-steps">
                    <li><span>1</span><div><strong>Open the LinkedIn app</strong><p>Use the app where you’re already signed in.</p></div></li>
                    <li><span>2</span><div><strong>Approve your sign-in</strong><p>When you see “Are you trying to sign in to another device?”, tap <strong>“Yes, it’s me”</strong>.</p></div></li>
                    <li><span>3</span><div><strong>Come back here</strong><p>We’ll automatically confirm when you’re connected.</p></div></li>
                </ol>
                <p class="ios-approval-note">The phone shows an example. Approve the request in your own LinkedIn app.</p>
            </div>
            <div class="ios-approval-demo">
                <div class="ios-demo-phone" aria-hidden="true">
                    <div class="ios-demo-screen">
                        <div class="ios-demo-statusbar"><span>9:41</span><span class="ios-demo-island"></span><span>▮▮▮ ▰</span></div>
                        <div class="ios-demo-home">
                            <div class="ios-demo-clock">9:41<small>Your phone</small></div>
                            <div class="ios-demo-apps">
                                <div class="ios-demo-app"><span class="ios-demo-app-icon ios-demo-mail">✉</span><small>Mail</small></div>
                                <div class="ios-demo-app"><span class="ios-demo-app-icon ios-demo-calendar">15</span><small>Calendar</small></div>
                                <div class="ios-demo-app"><span class="ios-demo-app-icon ios-demo-photos">✿</span><small>Photos</small></div>
                                <div class="ios-demo-app"><span class="ios-demo-app-icon ios-demo-notes">≡</span><small>Notes</small></div>
                                <div class="ios-demo-app ios-demo-linkedin-app"><span class="ios-demo-app-icon ios-demo-in">in<span class="ios-demo-notification"></span><span class="ios-demo-tap"></span></span><small>LinkedIn</small></div>
                                <div class="ios-demo-app"><span class="ios-demo-app-icon ios-demo-settings">⚙</span><small>Settings</small></div>
                            </div>
                            <div class="ios-demo-dock"><span>☎</span><span>◉</span><span>♫</span></div>
                        </div>
                        <div class="ios-demo-request">
                            <div class="ios-demo-wordmark">Linked<span>in</span></div>
                            <svg class="ios-demo-device" viewBox="0 0 48 64" fill="none"><rect x="7" y="2" width="34" height="58" rx="5" fill="#587081"/><rect x="10" y="6" width="28" height="45" rx="2" fill="#f1f7fc"/><circle cx="19" cy="18" r="6" fill="#abc597"/><path d="M28 15h6M28 21h6M15 31h19M15 38h19M15 45h19" stroke="#b3c8da" stroke-width="2.5" stroke-linecap="round"/><circle cx="24" cy="55" r="2.5" fill="#9db3c4"/></svg>
                            <h3>Are you trying to sign in to another device?</h3>
                            <div class="ios-demo-details">
                                <div><span>⌖</span><p>Location<small>Your sign-in location</small></p></div>
                                <div><span>▣</span><p>Device<small>Your web browser</small></p></div>
                                <div><span>◷</span><p>Time<small>Just now</small></p></div>
                            </div>
                            <div class="ios-demo-choices"><span>No, it’s not me</span><span class="ios-demo-yes">✓ Yes, it’s me<span class="ios-demo-tap"></span></span></div>
                            <p class="ios-demo-return">After completing verification, return to the browser where you started.</p>
                        </div>
                        <div class="ios-demo-homebar"></div>
                    </div>
                </div>
                <div class="ios-demo-caption" aria-hidden="true"><span>1 · Open LinkedIn</span><span>2 · Tap “Yes, it’s me”</span></div>
                <button id="ios-approval-animation" class="ios-demo-animation-control" type="button" aria-pressed="false">Pause animation</button>
            </div>
        </div>
        <div class="ios-approval-waiting" role="status"><span aria-hidden="true"></span><p>Waiting for your approval<small>This window closes automatically once LinkedIn is connected.</small></p></div>
    </dialog>
<script src="/app/assets/js/linkedin-approval.js?v=<?= filemtime(dirname(__DIR__) . '/assets/js/linkedin-approval.js') ?>"></script>
