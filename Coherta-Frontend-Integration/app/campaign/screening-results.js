(function () {
    'use strict';
    let dialog = null;

    function contactValue(contact, field) {
        const sources = [contact, contact?.opportunity, contact?.connected_opportunity,
            contact?.participant, contact?.person, contact?.profile, contact?.lead, contact?.data];
        for (const source of sources) {
            const value = source?.[field];
            if (value !== undefined && value !== null && String(value).trim() !== '') return String(value).trim();
        }
        return '';
    }

    function recordedScore(value) {
        return value !== '' && Number.isFinite(Number(value)) && Number(value) >= 0 && Number(value) <= 100
            ? Number(value) : null;
    }

    function getResult(contact) {
        const score = recordedScore(contactValue(contact, 'automated_screening_score'));
        const reason = contactValue(contact, 'automated_screening_reason');
        if (score === null && !reason && !contactValue(contact, 'automated_screening_id')) return null;
        const threshold = recordedScore(contactValue(contact, 'automated_screening_threshold'));
        const branch = contactValue(contact, 'automated_screening_branch').toLowerCase();
        // Use the saved decision and threshold, never the workflow's current settings.
        const decision = branch === 'match' ? 'accepted' : branch === 'no match' ? 'declined'
            : score !== null && threshold !== null ? (score >= threshold ? 'accepted' : 'declined') : '';
        return {score, reason, threshold, decision};
    }

    function decisionLabel(result) {
        return result.decision === 'accepted' ? 'Accepted' : result.decision === 'declined' ? 'Declined' : 'Completed';
    }

    function createDialog() {
        const element = document.createElement('dialog');
        element.id = 'screeningResultDialog';
        element.className = 'wf-screening-result-dialog';
        element.setAttribute('aria-labelledby', 'screeningResultTitle');
        element.setAttribute('aria-describedby', 'screeningResultPerson');
        element.innerHTML = `
            <div class="wf-screening-result-head"><div><h2 id="screeningResultTitle">Automated Screening</h2><p id="screeningResultPerson"></p></div><button id="closeScreeningResult" type="button" autofocus>Close</button></div>
            <div class="wf-screening-result-summary"><div><h3>Score</h3><strong id="screeningResultScore" class="wf-screening-result-score"></strong></div><span id="screeningResultDecision" class="wf-screening-result-decision"></span></div>
            <p id="screeningResultThreshold" class="wf-screening-result-threshold" hidden></p>
            <h3>Reason</h3><p id="screeningResultReason" class="wf-screening-result-reason"></p>`;
        element.querySelector('#closeScreeningResult').addEventListener('click', () => element.close());
        element.addEventListener('click', event => {
            if (event.target !== element) return;
            const rect = element.getBoundingClientRect();
            if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) element.close();
        });
        document.body.appendChild(element);
        return element;
    }

    function open(contact, name) {
        const result = getResult(contact);
        if (!result) return;
        if (!dialog) dialog = createDialog();
        dialog.querySelector('#screeningResultPerson').textContent = name || 'Unknown person';
        dialog.querySelector('#screeningResultScore').textContent = result.score === null ? 'Not recorded' : result.score + ' / 100';
        const decision = dialog.querySelector('#screeningResultDecision');
        decision.textContent = decisionLabel(result);
        decision.dataset.decision = result.decision;
        const threshold = dialog.querySelector('#screeningResultThreshold');
        threshold.hidden = result.threshold === null;
        threshold.textContent = result.threshold === null ? '' : 'Acceptance threshold at screening: ' + result.threshold + ' / 100';
        dialog.querySelector('#screeningResultReason').textContent = result.reason || 'No reason was recorded for this screening.';
        if (!dialog.open) dialog.showModal();
    }

    window.CohertaScreeningResults = {getResult, decisionLabel, open, isOpen: () => !!dialog?.open};
})();
