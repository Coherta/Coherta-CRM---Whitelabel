<section class="setup-screen setup-clearhaus" data-step-id="payment" data-step-title="Betaling" data-step-copy="Test">
    <div class="setup-kicker">Betaling · test</div>
    <h2 class="setup-title">Dit abonnement</h2>
    <p class="setup-lead">Gennemgå abonnementet og godkend abonnementsvilkårene for at fortsætte.</p>

    <div class="setup-alert clearhaus-test-notice">
        <strong>Dette er en testbetaling.</strong> Kortet er simuleret. Der trækkes ingen penge, og testgodkendelsen opretter ikke et betalt abonnement.
    </div>

    <section class="clearhaus-card" aria-labelledby="clearhausOverviewTitle">
        <h3 id="clearhausOverviewTitle">Pris og betalingsplan</h3>
        <dl class="clearhaus-details">
            <div>
                <dt>Produkt</dt>
                <dd>
                    <strong id="clearhausPlan">Valgt abonnement</strong>
                    <br><a href="/da/priser" target="_blank" rel="noopener">Se pakkens indhold</a>
                </dd>
            </div>
            <div><dt>Betales ved godkendelse</dt><dd id="clearhausInitialPrice"></dd></div>
            <div><dt>Løbende abonnementspris</dt><dd id="clearhausRecurringPrice"></dd></div>
            <div><dt>Startdato</dt><dd id="clearhausStartDate"></dd></div>
            <div><dt>Første abonnementsbetaling</dt><dd id="clearhausFirstBillingDate"></dd></div>
            <div><dt>Faktureringshyppighed</dt><dd id="clearhausFrequency"></dd></div>
            <div><dt>Varighed og fornyelse</dt><dd id="clearhausDuration"></dd></div>
            <div><dt>Betalingskort</dt><dd>Testkort •••• 4242 <span class="clearhaus-muted">(sidste fire cifre, simuleret)</span></dd></div>
        </dl>
        <p class="clearhaus-muted">Alle priser er ekskl. moms. Beløb og datoer viser betalingsplanen for det valgte abonnement i denne test.</p>
        <p id="clearhausCustomNotice" hidden>Custom kræver en individuelt aftalt pris. <a id="clearhausStandardLink" href="?clearhaus=1&amp;plan=standard&amp;billing=monthly&amp;locale=da&amp;step=payment">Vælg Standard for at teste betaling</a>.</p>
    </section>

    <div class="clearhaus-card clearhaus-acceptance">
        <label class="clearhaus-consent" for="clearhausAcceptTerms">
            <input type="checkbox" id="clearhausAcceptTerms" required aria-describedby="setupPaymentStatus">
            <span>Jeg har læst og accepterer <a href="/da/forretningsbetingelser#abonnement" target="_blank" rel="noopener">abonnementsvilkårene</a>, herunder pris, betaling ved godkendelse, fornyelse og opsigelse, for denne test.</span>
        </label>
    </div>
    <p class="clearhaus-general-terms">Øvrige vilkår: <a href="/da/forretningsbetingelser" target="_blank" rel="noopener">Generelle forretningsbetingelser</a>.</p>
    <p>Coherta ApS, Hovedvejen 72, 2600 Glostrup, CVR: 42836044</p>
    <p class="setup-status" id="setupPaymentStatus" aria-live="polite"></p>
</section>
