<?php if (empty($coherta_app_chat_rendered)): $coherta_app_chat_rendered = true; ?>
<div class="audience-assistant" data-audience-assistant>
  <button class="audience-launcher" type="button" aria-label="Åbn Chat med os" aria-expanded="false">
    <span class="audience-launcher-glow" aria-hidden="true"></span>
    <span class="audience-launcher-icon" aria-hidden="true">
      <svg viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M6.8 18.7c-1.35-1.42-2.05-3.16-2.05-5.05C4.75 8.72 8.9 5 14 5s9.25 3.72 9.25 8.65S19.1 22.3 14 22.3c-1.03 0-2.03-.15-2.96-.45L7.3 23.1c-.55.18-1.08-.35-.9-.9l1.15-3.5Z" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M10.2 12.2h7.7M10.2 15.6h5.2" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>
      </svg>
    </span>
  </button>

  <div class="audience-nudge" data-audience-nudge aria-hidden="true">
    <strong>Skal jeg hjælpe med målgruppen?</strong>
    <span>Beskriv den med dine egne ord.</span>
  </div>

  <section class="audience-panel" aria-label="Chat med os" aria-hidden="true">
    <header class="audience-panel-header">
      <div>
        <h3>Chat med os</h3>
      </div>
      <button class="audience-close" type="button" aria-label="Luk Chat med os">×</button>
    </header>

    <div class="audience-chat" data-audience-chat aria-live="polite"></div>

    <form class="audience-input" data-audience-form>
      <label class="sr-only" for="app-audience-message">Skriv til Chat med os</label>
      <textarea id="app-audience-message" data-audience-textarea rows="1" placeholder="Skriv hvem du gerne vil sælge til…"></textarea>
      <button class="audience-send" type="submit" aria-label="Send besked">
        <span>Send</span>
      </button>
    </form>

    <form class="audience-lead-form" data-audience-lead-form hidden>
      <div class="audience-lead-copy">
        <strong>Perfekt. Nu har jeg det, jeg skal bruge. Hvor skal vi sende målgruppen?</strong>
        <span>Vi vender tilbage inden for 24 timer.</span>
      </div>
      <label>
        <span>Navn</span>
        <input name="name" autocomplete="name" required />
      </label>
      <label>
        <span>Arbejdsmail</span>
        <input name="email" type="email" autocomplete="email" required />
      </label>
      <label>
        <span>Virksomhed</span>
        <input name="company" autocomplete="organization" required />
      </label>
      <button class="audience-confirm" type="submit">Send målgruppe</button>
      <p>Vi bruger kun oplysningerne til at kontakte dig om målgruppen.</p>
    </form>
  </section>
</div>
<script src="/da/js/maalgruppe-assistent.js?v=20260629-chat-med-os"></script>
<?php
$coherta_chat_avatar = isset($client) ? $client->getColleagueImage(158) : '/app/assets/images/user/user.png';
?>
<script src="/public/chat-widget.js?v=20260807-user-158" data-language="da" data-avatar-url="<?php echo htmlspecialchars($coherta_chat_avatar, ENT_QUOTES, 'UTF-8'); ?>" defer></script>
<?php endif; ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=G-3ZLTPCKF48"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-3ZLTPCKF48');
    var cohertaFooterSessionId = document.body.getAttribute('session_id');
    if (cohertaFooterSessionId && cohertaFooterSessionId !== 'null') {
        localStorage.setItem('session_id', cohertaFooterSessionId);
    }
</script>
<script type="text/javascript">
    _linkedin_partner_id = "9993001";
    window._linkedin_data_partner_ids = window._linkedin_data_partner_ids || [];
    window._linkedin_data_partner_ids.push(_linkedin_partner_id);
</script><script type="text/javascript">
    (function(l) {
        if (!l){window.lintrk = function(a,b){window.lintrk.q.push([a,b])};
            window.lintrk.q=[]}
        var s = document.getElementsByTagName("script")[0];
        var b = document.createElement("script");
        b.type = "text/javascript";b.async = true;
        b.src = "https://snap.licdn.com/li.lms-analytics/insight.min.js";
        s.parentNode.insertBefore(b, s);})(window.lintrk);
</script>
<noscript>
    <img height="1" width="1" style="display:none;" alt="" src="https://px.ads.linkedin.com/collect/?pid=9993001&fmt=gif" />
</noscript>
<!-- Meta Pixel Code -->
<script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '1502833238314510');
    fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
               src="https://www.facebook.com/tr?id=1502833238314510&ev=PageView&noscript=1"
    /></noscript>
<!-- End Meta Pixel Code -->
</body>
</html>
