(() => {
    'use strict';
    const modal = document.getElementById('ios-linkedin-approval');
    let dismissed = false;

    function update(required) {
        if (!required) {
            if (modal.open) modal.close();
            dismissed = false;
        } else if (!dismissed && !modal.open) {
            modal.showModal();
        }
    }

    document.getElementById('ios-approval-close').addEventListener('click', () => {
        dismissed = true;
        modal.close();
    });
    modal.addEventListener('cancel', () => { dismissed = true; });
    // Keep Escape scoped to these instructions when nested inside a Bootstrap modal.
    modal.addEventListener('keydown', event => {
        if (event.key === 'Escape') event.stopPropagation();
    });
    document.getElementById('ios-approval-animation').addEventListener('click', event => {
        const paused = modal.classList.toggle('is-paused');
        event.currentTarget.setAttribute('aria-pressed', String(paused));
        event.currentTarget.textContent = paused ? 'Play animation' : 'Pause animation';
    });
    window.addEventListener('pagehide', () => update(false));

    window.CohertaLinkedInApproval = Object.freeze({
        update,
        messages: Object.freeze({
            invalidCredentials: 'LinkedIn could not verify your username or password. Check your details and try again.',
            approvalTimeout: "You didn't approve the request within the timeframe. Please try again"
        })
    });
})();
