(function () {
    const backendEndpoint = 'https://api.coherta.com/recruiters';
    const leadEnrichEndpoint = 'https://api.coherta.com/pipeline/leads/fullenrich';

    function qs(selector, root) {
        return (root || document).querySelector(selector);
    }

    function qsa(selector, root) {
        return Array.prototype.slice.call((root || document).querySelectorAll(selector));
    }

    function openModal(modal) {
        if (!modal) return;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('recruiter-modal-open');
    }

    function closeModal(modal) {
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        if (!qs('.recruiter-modal.is-open')) {
            document.body.classList.remove('recruiter-modal-open');
        }
    }

    function campaignId() {
        const campaign = qs('[data-recruiter-campaign-id]');
        return campaign ? campaign.getAttribute('data-recruiter-campaign-id') : null;
    }

    function campaignIds(root) {
        const source = root || qs('[data-recruiter-campaign-id]');
        const recruiterCampaignId = source ? source.getAttribute('data-recruiter-campaign-id') : '';
        const campaignIdValue = source ? source.getAttribute('data-campaign-id') : '';

        return {
            campaign_id: campaignIdValue || recruiterCampaignId || '',
            recruiter_campaign_id: recruiterCampaignId || ''
        };
    }

    async function postJson(payload, method) {
        const headers = {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        };
        const token = document.body ? document.body.getAttribute('session_id') : '';
        if (token) headers.Authorization = 'Bearer ' + token;

        const response = await fetch(backendEndpoint, {
            method: method || 'POST',
            headers: headers,
            body: JSON.stringify(payload)
        });

        const data = await response.json().catch(() => ({}));
        if (!response.ok || data.ok === false) {
            throw new Error(data.detail || data.message || 'Request failed');
        }
        return data;
    }

    async function postLeadEnrichment(payload) {
        const token = document.body ? document.body.getAttribute('session_id') : '';
        const response = await fetch(leadEnrichEndpoint, {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        const responseText = await response.text();
        let data = responseText;
        try {
            data = responseText ? JSON.parse(responseText) : {};
        } catch (error) {
            data = responseText;
        }
        if (response.status !== 200 && response.status !== 201) {
            throw new Error((data && (data.detail || data.message)) || responseText || 'Phone enrichment failed');
        }
        return data;
    }

    function setButtonLoading(button, isLoading, label) {
        if (!button) return;
        if (isLoading) {
            button.dataset.originalText = button.textContent;
            button.textContent = label || 'Saving...';
            button.disabled = true;
        } else {
            button.textContent = button.dataset.originalText || button.textContent;
            button.disabled = false;
        }
    }

    function showToast(message, type) {
        let toast = qs('.recruiter-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.className = 'recruiter-toast';
            document.body.appendChild(toast);
        }
        toast.textContent = message;
        toast.classList.toggle('is-error', type === 'error');
        toast.classList.add('is-visible');
        window.setTimeout(() => toast.classList.remove('is-visible'), 2800);
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function phoneHref(phone) {
        return 'tel:' + String(phone || '').replace(/[^\d+]/g, '');
    }

    function phoneLinkHtml(phone) {
        return '<a class="btn btn-light btn-xs candidate-action-button" href="' + escapeHtml(phoneHref(phone)) + '">' + escapeHtml(phone) + '</a>';
    }

    const templateAutocompleteSources = [
        {
            key: 'lead',
            fields: ['id', 'uid', 'pipeline_id', 'connected_users', 'added', 'start_date', 'last_updated', 'country_code', 'vat_code', 'company_name', 'company_form', 'status', 'company_phone', 'company_email', 'company_address', 'company_postal', 'company_city', 'company_website', 'category_code', 'category_text', 'stage', 'number_of_employees', 'number_of_contacts', 'google_maps_address', 'google_maps_display_name', 'google_maps_phone', 'google_maps_primary', 'google_maps_rating', 'google_maps_rating_count', 'google_maps_price_range_currency', 'google_maps_price_range_startPrice', 'google_maps_price_range_endPrice', 'tasks_next', 'financials_equity', 'financials_net_income', 'financials_revenue', 'financials_liquidity_ratio', 'financials_cash_ratio', 'financials_return_on_equity', 'financials_debt_ratio', 'financials_cash', 'financials_ebitda', 'key_employee_employee', 'company_participant_id', 'participant_id', 'participant_jobtitle', 'participant_name', 'participant_first_name', 'participant_middle_name', 'participant_last_name', 'participant_enhedsNummer', 'participant_phonenumber', 'participant_phonenumber2', 'participant_email', 'participant_linkedin_url', 'domain_id', 'domain_name', 'software_ids', 'software_types', 'software_names', 'software_icons', 'trustpilot_score', 'trustpilot_reviews', 'payment_providers', 'google_keywords', 'inserted_in_cache', 'updated_in_cache']
        },
        {
            key: 'campaign',
            fields: ['id', 'uid', 'enhedsNummer', 'name', 'first_name', 'last_name', 'linkedin_url', 'image', 'location', 'gender', 'followers', 'following', 'connections', 'last_post', 'last_updated', 'phonenumber', 'phonenumber_2', 'email', 'next_refresh', 'created_date', 'scrape_required_for_match']
        }
    ];

    function normalizeTemplateQuery(value) {
        return String(value || '').toLowerCase().trim().replace(/[{}]/g, '').replace(/\s+/g, '_');
    }

    function prettifyTemplateField(field) {
        return String(field || '').replace(/_/g, ' ');
    }

    function scoreTemplateMatch(query, field, datasetKey) {
        const q = normalizeTemplateQuery(query);
        const f = normalizeTemplateQuery(field);
        const full = datasetKey + '.' + f;
        if (!q) return 1;
        if (full === q) return 1000;
        if (f === q) return 950;
        if (full.startsWith(q)) return 900;
        if (f.startsWith(q)) return 850;
        if (f.includes(q)) return 700;
        const words = q.split('_').filter(Boolean);
        if (words.length && words.every(word => f.includes(word))) return 650;
        return 0;
    }

    function buildTemplateItems(rawQuery) {
        const items = [];
        templateAutocompleteSources.forEach(function (source) {
            source.fields.forEach(function (field) {
                const score = scoreTemplateMatch(rawQuery, field, source.key);
                if (score > 0) {
                    items.push({ dataset: source.key, field, token: '{' + source.key + '.' + field + '}', score });
                }
            });
        });
        items.sort(function (a, b) {
            if (b.score !== a.score) return b.score - a.score;
            if (a.dataset !== b.dataset) return a.dataset.localeCompare(b.dataset);
            return a.field.localeCompare(b.field);
        });
        return items.slice(0, 8);
    }

    function getBraceQueryAtCursor(textarea) {
        const value = textarea.value || '';
        const cursor = textarea.selectionStart || 0;
        const before = value.slice(0, cursor);
        const start = before.lastIndexOf('{');
        if (start === -1) return null;
        const raw = value.slice(start + 1, cursor);
        if (raw.includes('}') || raw.includes('\n')) return null;
        return { start, end: cursor, raw };
    }

    function attachTemplateAutocomplete(textarea) {
        const root = textarea.closest('.template-field');
        const panel = root ? qs('.template-autocomplete', root) : null;
        const list = root ? qs('.template-autocomplete-list', root) : null;
        if (!panel || !list) return;
        const state = { open: false, items: [], activeIndex: 0, tokenStart: -1 };

        function close() {
            state.open = false;
            state.items = [];
            state.activeIndex = 0;
            state.tokenStart = -1;
            panel.hidden = true;
            list.innerHTML = '';
        }

        function apply(index) {
            const item = state.items[index];
            if (!item) return;
            const value = textarea.value || '';
            const end = textarea.selectionStart || 0;
            const replacement = item.token;
            textarea.value = value.slice(0, state.tokenStart) + replacement + value.slice(end);
            const cursor = state.tokenStart + replacement.length;
            textarea.focus();
            textarea.setSelectionRange(cursor, cursor);
            textarea.dispatchEvent(new Event('input', { bubbles: true }));
            close();
        }

        function render() {
            if (!state.items.length) {
                close();
                return;
            }
            list.innerHTML = state.items.map(function (item, index) {
                return '<div class="template-autocomplete-item' + (index === state.activeIndex ? ' is-active' : '') + '" data-template-index="' + index + '">' +
                    '<div class="template-autocomplete-main">' + escapeHtml(item.token) + '</div>' +
                    '<div class="template-autocomplete-meta">' + escapeHtml(item.dataset + ' - ' + prettifyTemplateField(item.field)) + '</div>' +
                    '</div>';
            }).join('');
            panel.hidden = false;
            state.open = true;
        }

        function refresh() {
            const token = getBraceQueryAtCursor(textarea);
            if (!token) {
                close();
                return;
            }
            state.items = buildTemplateItems(token.raw);
            state.activeIndex = 0;
            state.tokenStart = token.start;
            render();
        }

        textarea.addEventListener('input', refresh);
        textarea.addEventListener('click', refresh);
        textarea.addEventListener('keyup', function (event) {
            if (['ArrowUp', 'ArrowDown', 'Enter', 'Escape'].includes(event.key)) return;
            refresh();
        });
        textarea.addEventListener('keydown', function (event) {
            if (!state.open) return;
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                state.activeIndex = Math.min(state.activeIndex + 1, state.items.length - 1);
                render();
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                state.activeIndex = Math.max(state.activeIndex - 1, 0);
                render();
            } else if (event.key === 'Enter') {
                event.preventDefault();
                apply(state.activeIndex);
            } else if (event.key === 'Escape') {
                event.preventDefault();
                close();
            }
        });
        list.addEventListener('mousedown', function (event) {
            const item = event.target.closest('[data-template-index]');
            if (!item) return;
            event.preventDefault();
            apply(Number(item.getAttribute('data-template-index')));
        });
        document.addEventListener('click', function (event) {
            if (event.target === textarea || panel.contains(event.target)) return;
            close();
        });
    }

    function refreshCampaignView() {
        const url = new URL(window.location.href);
        url.searchParams.delete('settings');
        window.location.replace(url.toString());
    }

    function skillCriteriaRow(index) {
        const row = document.createElement('div');
        row.className = 'skill-criteria-row';
        row.setAttribute('data-skill-criteria-row', '');
        row.innerHTML = [
            '<div class="skill-criteria-field"><label>Skill</label><input class="input" name="steps[2][skill_criteria][' + index + '][skill]" type="text" placeholder="Skill"></div>',
            '<div class="skill-criteria-field"><label>Min. endorsements</label><input class="input" name="steps[2][skill_criteria][' + index + '][endorsements]" type="number" min="0" value=""></div>',
            '<div class="skill-criteria-field"><label>Type</label><select class="select" name="steps[2][skill_criteria][' + index + '][priority]">',
            '<option value="requirement">Requirement</option>',
            '<option value="preferred">Preferred</option>',
            '</select></div>',
            '<div class="skill-criteria-field skill-value-field"><label>Value</label><span>Calculated</span></div>',
            '<button class="btn btn-light btn-sm js-remove-skill-criterion" type="button" aria-label="Remove skill">×</button>'
        ].join('');
        return row;
    }

    function reindexSkillCriteria(list) {
        qsa('[data-skill-criteria-row]', list).forEach(function (row, index) {
            qsa('input, select', row).forEach(function (field) {
                field.name = field.name.replace(/steps\[2\]\[skill_criteria\]\[\d+\]/, 'steps[2][skill_criteria][' + index + ']');
            });
        });
    }

    function applySkillCriteriaValues(criteria) {
        const cleaned = criteria.filter(item => item && item.skill);
        const totalWeight = cleaned.reduce(function (sum, item) {
            return sum + (item.priority === 'preferred' ? 1 : 2);
        }, 0);

        return cleaned.map(function (item) {
            const weight = item.priority === 'preferred' ? 1 : 2;
            return {
                ...item,
                value: totalWeight ? Math.round((weight / totalWeight) * 10000) / 100 : 0
            };
        });
    }

    function updateSkillValueLabels(root) {
        const rows = qsa('[data-skill-criteria-row]', root || document);
        const activeRows = rows.filter(function (row) {
            const input = qs('input[name$="[skill]"]', row);
            return input && input.value.trim();
        });
        const totalWeight = activeRows.reduce(function (sum, row) {
            const select = qs('select[name$="[priority]"]', row);
            return sum + (select && select.value === 'preferred' ? 1 : 2);
        }, 0);

        rows.forEach(function (row) {
            const input = qs('input[name$="[skill]"]', row);
            const select = qs('select[name$="[priority]"]', row);
            const label = qs('.skill-value-field span', row);
            if (!label) return;
            if (!input || !input.value.trim() || !totalWeight) {
                label.textContent = 'Calculated';
                return;
            }
            const weight = select && select.value === 'preferred' ? 1 : 2;
            label.textContent = (Math.round((weight / totalWeight) * 10000) / 100).toLocaleString('da-DK', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            }) + '%';
        });
    }

    function normalizeMatchThreshold(value) {
        const parsed = parseInt(value, 10);
        if (Number.isNaN(parsed)) return 0;
        return Math.max(0, Math.min(100, parsed));
    }

    function updateStageMatchThreshold(stage, value) {
        qsa('[data-stage-match-threshold="' + stage + '"]').forEach(function (label) {
            label.textContent = normalizeMatchThreshold(value) + '% match';
        });
    }

    function skillCriteriaFromForm(formData) {
        const indexed = {};
        formData.forEach(function (value, key) {
            const match = key.match(/^steps\[2\]\[skill_criteria\]\[(\d+)\]\[([^\]]+)\]$/);
            if (!match) return;
            const index = match[1];
            const field = match[2];
            if (!indexed[index]) indexed[index] = {};
            indexed[index][field] = value;
        });

        return Object.keys(indexed)
            .sort((a, b) => parseInt(a, 10) - parseInt(b, 10))
            .map(index => indexed[index])
            .map(function (item) {
                return {
                    skill: String(item.skill || '').trim(),
                    endorsements: String(item.endorsements || '').trim() === '' ? null : Math.max(0, parseInt(item.endorsements, 10) || 0),
                    priority: item.priority === 'preferred' ? 'preferred' : 'requirement'
                };
            })
            .filter(item => item.skill);
    }

    function weightedSkillCriteriaFromForm(formData) {
        return applySkillCriteriaValues(skillCriteriaFromForm(formData));
    }

    document.addEventListener('click', function (event) {
        const openStageButton = event.target.closest('.js-open-stage-modal');
        if (openStageButton) {
            event.preventDefault();
            openModal(qs('#stageModal' + openStageButton.getAttribute('data-stage')));
            return;
        }

        const closeStageButton = event.target.closest('.js-close-stage-modal');
        if (closeStageButton) {
            event.preventDefault();
            closeModal(qs('#stageModal' + closeStageButton.getAttribute('data-stage')));
            return;
        }

        if (event.target.classList.contains('recruiter-modal')) {
            closeModal(event.target);
            return;
        }

        const addSkillButton = event.target.closest('.js-add-skill-criterion');
        if (addSkillButton) {
            event.preventDefault();
            const field = addSkillButton.closest('.field');
            const list = field ? qs('[data-skill-criteria-list]', field) : null;
            if (!list) return;
            list.appendChild(skillCriteriaRow(qsa('[data-skill-criteria-row]', list).length));
            updateSkillValueLabels(list);
            return;
        }

        const removeSkillButton = event.target.closest('.js-remove-skill-criterion');
        if (removeSkillButton) {
            event.preventDefault();
            const list = removeSkillButton.closest('[data-skill-criteria-list]');
            const row = removeSkillButton.closest('[data-skill-criteria-row]');
            if (!list || !row) return;
            if (qsa('[data-skill-criteria-row]', list).length === 1) {
                qsa('input', row).forEach(field => field.value = '');
                const select = qs('select', row);
                if (select) select.value = 'requirement';
            } else {
                row.remove();
                reindexSkillCriteria(list);
            }
            updateSkillValueLabels(list);
        }
    });

    document.addEventListener('input', function (event) {
        if (event.target.closest('[data-skill-criteria-list]')) {
            updateSkillValueLabels(event.target.closest('[data-skill-criteria-list]'));
        }
        if (event.target.matches('input[name="steps[2][match_threshold]"]')) {
            updateStageMatchThreshold(2, event.target.value);
        }
    });

    document.addEventListener('change', function (event) {
        if (event.target.closest('[data-skill-criteria-list]')) {
            updateSkillValueLabels(event.target.closest('[data-skill-criteria-list]'));
        }
        if (event.target.matches('input[name="steps[2][match_threshold]"]')) {
            updateStageMatchThreshold(2, event.target.value);
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            qsa('.recruiter-modal.is-open').forEach(closeModal);
        }
    });

    document.addEventListener('click', async function (event) {
        const stepButton = event.target.closest('.js-step-toggle');
        if (!stepButton) return;

        const cid = campaignId();
        const stage = parseInt(stepButton.getAttribute('data-stage'), 10);
        const action = stepButton.getAttribute('data-action');
        if (!cid || !stage || !action) return;

        setButtonLoading(stepButton, true, action === 'start' ? 'Starter...' : 'Stopper...');
        try {
            const data = await postJson({
                action: action === 'start' ? 'start_step' : 'stop_step',
                ...campaignIds(),
                stage: stage
            });

            const card = stepButton.closest('[data-step-card]');
            const label = card ? qs('[data-started-label]', card) : null;
            if (action === 'start') {
                stepButton.setAttribute('data-action', 'stop');
                stepButton.classList.remove('btn-success');
                stepButton.classList.add('btn-light');
                stepButton.textContent = 'Stop step';
                if (label) label.textContent = 'Started: ' + (data.started_at || 'Now');
                showToast('Step started and connected campaign created.');
            } else {
                stepButton.setAttribute('data-action', 'start');
                stepButton.classList.add('btn-success');
                stepButton.classList.remove('btn-light');
                stepButton.textContent = 'Start step';
                if (label) label.textContent = 'Status: Stopped';
                showToast('Step stopped.');
            }
            stepButton.disabled = false;
        } catch (error) {
            setButtonLoading(stepButton, false);
            showToast(error.message, 'error');
        }
    });

    document.addEventListener('click', async function (event) {
        const deactivateButton = event.target.closest('.js-deactivate-candidate');
        if (!deactivateButton) return;

        const cid = campaignId();
        const candidateId = deactivateButton.getAttribute('data-candidate-id');
        const stage = parseInt(deactivateButton.getAttribute('data-stage'), 10);
        if (!cid || !candidateId) return;

        setButtonLoading(deactivateButton, true, 'Deactivating...');
        try {
            await postJson({
                action: 'remove_candidate',
                ...campaignIds(),
                candidate_id: candidateId,
                stage: stage,
                reason: 'Manually removed from step'
            });

            qsa('[data-candidate-row="' + candidateId + '"]').forEach(function (row) {
                row.classList.add('is-deactivated');
                window.setTimeout(() => row.remove(), 250);
            });
            qsa('[data-candidate-card="' + candidateId + '"]').forEach(function (card) {
                card.classList.add('is-deactivated');
                window.setTimeout(() => card.remove(), 250);
            });
            showToast('Candidate deactivated from the campaign.');
        } catch (error) {
            setButtonLoading(deactivateButton, false);
            showToast(error.message, 'error');
        }
    });

    document.addEventListener('click', async function (event) {
        const deleteButton = event.target.closest('.js-delete-campaign');
        if (!deleteButton) return;

        event.preventDefault();
        const form = deleteButton.closest('#recruiterSettingsForm');
        if (!form) return;
        const confirmed = window.confirm('Are you sure you want to delete this campaign? This cannot be undone.');
        if (!confirmed) return;

        setButtonLoading(deleteButton, true, 'Deleting...');
        try {
            await postJson({
                action: 'delete_campaign',
                ...campaignIds(form)
            }, 'DELETE');
            showToast('Campaign deleted.');
            window.location.href = 'recruiters.php';
        } catch (error) {
            setButtonLoading(deleteButton, false);
            showToast(error.message, 'error');
        }
    });

    qsa('.js-template-autocomplete').forEach(attachTemplateAutocomplete);

    const settingsForm = qs('#recruiterSettingsForm');
    qsa('[data-skill-criteria-list]').forEach(updateSkillValueLabels);
    if (settingsForm) {
        settingsForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            const submit = qs('button[type="submit"]', settingsForm);
            const formData = new FormData(settingsForm);
            const steps = {};
            const payload = {
                action: 'update_settings',
                ...campaignIds(settingsForm),
                steps: steps
            };
            if (formData.has('title')) payload.title = formData.get('title');
            if (formData.has('terms')) payload.terms = formData.get('terms');
            if (formData.get('owner_id')) {
                payload.owner_id = formData.get('owner_id');
                payload.employee_ids = [Number(formData.get('owner_id')) || formData.get('owner_id')];
            }

            formData.forEach(function (value, key) {
                const match = key.match(/^steps\[(\d+)\]\[([^\]]+)\]$/);
                if (!match) return;
                const stepNumber = match[1];
                const settingKey = match[2];
                if (!steps[stepNumber]) steps[stepNumber] = {};
                steps[stepNumber][settingKey] = value;
            });
            const skillCriteria = weightedSkillCriteriaFromForm(formData);
            if (skillCriteria.length) {
                if (!steps[2]) steps[2] = {};
                steps[2].skill_criteria = skillCriteria;
                steps[2].skills = skillCriteria.map(item => item.skill).join(', ');
            }

            setButtonLoading(submit, true, 'Saving...');
            try {
                await postJson(payload);
                if (formData.has('steps[2][match_threshold]')) {
                    updateStageMatchThreshold(2, formData.get('steps[2][match_threshold]'));
                }
                setButtonLoading(submit, false);
                showToast('Settings saved.');
                window.setTimeout(refreshCampaignView, 350);
            } catch (error) {
                setButtonLoading(submit, false);
                showToast(error.message, 'error');
            }
        });
    }

    const candidateDataNode = qs('#recruiterCandidateData');
    const candidateGrid = qs('[data-candidate-grid]');
    if (candidateDataNode && candidateGrid) {
        let candidatePayload = { stages: {}, candidates: [] };
        try {
            candidatePayload = JSON.parse(candidateDataNode.textContent || '{}');
        } catch (error) {
            candidatePayload = { stages: {}, candidates: [] };
        }

        let currentStage = parseInt(candidateGrid.getAttribute('data-selected-stage') || '0', 10) || 0;
        let currentView = candidateGrid.getAttribute('data-current-view') || 'top5';
        const countNode = qs('[data-candidate-count]');

        function candidateLimit(view, candidates) {
            if (view === 'top5') return 5;
            if (view === 'top10') return 10;
            return candidates.length;
        }

        function candidatesForStage(stage) {
            return (candidatePayload.candidates || [])
                .filter(candidate => !stage || parseInt(candidate.stage, 10) === stage)
                .sort(function (a, b) {
                    const fit = (parseInt(b.fit, 10) || 0) - (parseInt(a.fit, 10) || 0);
                    if (fit !== 0) return fit;
                    return String(a.name || '').localeCompare(String(b.name || ''));
                });
        }

        function avatarFor(candidate) {
            if (candidate.image) return candidate.image;
            return '/app/assets/images/user/user.png';
        }

        function candidateCardHtml(candidate, loading) {
            const stage = parseInt(candidate.stage, 10) || 1;
            const stageMeta = (candidatePayload.stages && (candidatePayload.stages[stage] || candidatePayload.stages[String(stage)])) || {};
            const cvEnabled = stage === 4 && Boolean(candidate.cv_available);
            const phoneAction = candidate.phone
                ? phoneLinkHtml(candidate.phone)
                : '<button class="btn btn-light btn-xs js-reveal-phone" type="button" data-candidate-id="' + escapeHtml(candidate.id) + '" data-participant-id="' + escapeHtml(candidate.participant_id || '') + '" data-participant-linkedin-id="' + escapeHtml(candidate.participant_linkedin_id || '') + '" data-name="' + escapeHtml(candidate.name || '') + '" data-title="' + escapeHtml(candidate.title || '') + '" data-company="' + escapeHtml(candidate.company || '') + '" data-linkedin="' + escapeHtml(candidate.linkedin || '') + '" data-phone="">Get phone</button>';
            return [
                '<article class="candidate-card' + (loading ? ' is-loading' : '') + '" data-candidate-card="' + escapeHtml(candidate.id) + '">',
                '<div class="candidate-card-top">',
                '<img class="candidate-avatar" src="' + escapeHtml(avatarFor(candidate)) + '" alt="">',
                '<div class="candidate-heading"><div class="candidate-name-line">',
                '<strong>' + escapeHtml(candidate.name) + '</strong>',
                '<span class="pill">' + stage + '. ' + escapeHtml(stageMeta.short || '') + '</span>',
                '</div><span>' + escapeHtml(candidate.title) + '</span></div>',
                '<div class="match-badge">' + (parseInt(candidate.fit, 10) || 0) + '%</div>',
                '</div>',
                '<div class="candidate-meta-line">' + escapeHtml(candidate.company) + ' · ' + escapeHtml(candidate.location) + '</div>',
                '<p>' + escapeHtml(candidate.note) + '</p>',
                '<div class="candidate-actions">',
                '<a class="btn btn-light btn-xs candidate-action-button" href="' + escapeHtml(candidate.linkedin || '#') + '" target="_blank" rel="noopener">LinkedIn</a>',
                phoneAction,
                '<a class="btn btn-light btn-xs ' + (cvEnabled ? '' : 'is-disabled') + '" href="' + (cvEnabled ? escapeHtml(candidate.cv || '#') : '#') + '" ' + (cvEnabled ? 'target="_blank" rel="noopener"' : 'aria-disabled="true" tabindex="-1"') + '>CV</a>',
                '<button class="btn btn-light btn-xs js-deactivate-candidate" type="button" data-candidate-id="' + escapeHtml(candidate.id) + '" data-stage="' + stage + '">Withdraw candidate</button>',
                '</div>',
                '</article>'
            ].join('');
        }

        function loadingCardHtml(candidate) {
            const stage = parseInt(candidate.stage, 10) || 1;
            const stageMeta = (candidatePayload.stages && (candidatePayload.stages[stage] || candidatePayload.stages[String(stage)])) || {};
            return [
                '<article class="candidate-card candidate-loading-card" data-candidate-card="' + escapeHtml(candidate.id) + '">',
                '<div class="candidate-card-top">',
                '<div class="candidate-avatar candidate-avatar-placeholder"></div>',
                '<div class="candidate-heading"><div class="candidate-name-line">',
                '<strong>' + escapeHtml(candidate.name || 'Candidate') + '</strong>',
                '<span class="pill">' + stage + '. ' + escapeHtml(stageMeta.short || '') + '</span>',
                '</div><span>Loading candidate data...</span></div>',
                '<div class="match-badge">...</div>',
                '</div>',
                '<div class="candidate-loading-lines"><span></span><span></span><span></span></div>',
                '</article>'
            ].join('');
        }

        function syncCandidateControls() {
            qsa('[data-stage-filter] a').forEach(function (link) {
                link.classList.toggle('is-active', parseInt(link.getAttribute('data-stage-value') || '0', 10) === currentStage);
            });
            qsa('[data-candidate-view-tabs] a').forEach(function (link) {
                link.classList.toggle('is-active', link.getAttribute('data-view-value') === currentView);
            });
        }

        function updateCandidateUrl() {
            const url = new URL(window.location.href);
            if (currentStage) {
                url.searchParams.set('stage', String(currentStage));
            } else {
                url.searchParams.delete('stage');
            }
            url.searchParams.set('view', currentView);
            window.history.replaceState({}, '', url.toString());
        }

        function renderCandidateSlice(options) {
            const candidates = candidatesForStage(currentStage);
            const limit = Math.min(candidateLimit(currentView, candidates), candidates.length);
            const nextCandidates = candidates.slice(0, limit);
            const oldIds = qsa('[data-candidate-card]', candidateGrid).map(card => card.getAttribute('data-candidate-card'));
            const oldSet = new Set(oldIds);
            const expanding = options && options.expanding;

            if (expanding) {
                candidateGrid.innerHTML = nextCandidates.map(function (candidate) {
                    return oldSet.has(String(candidate.id)) ? candidateCardHtml(candidate, false) : loadingCardHtml(candidate);
                }).join('');
                if (countNode) countNode.textContent = limit + ' candidates shown';
                window.setTimeout(function () {
                    candidateGrid.innerHTML = nextCandidates.map(candidate => candidateCardHtml(candidate, false)).join('');
                }, 420);
            } else {
                candidateGrid.innerHTML = nextCandidates.map(candidate => candidateCardHtml(candidate, false)).join('');
                if (countNode) countNode.textContent = limit + ' candidates shown';
            }

            candidateGrid.classList.toggle('is-all', currentView === 'all');
            candidateGrid.setAttribute('data-selected-stage', String(currentStage));
            candidateGrid.setAttribute('data-current-view', currentView);
            syncCandidateControls();
            updateCandidateUrl();
        }

        document.addEventListener('click', function (event) {
            const viewLink = event.target.closest('[data-candidate-view-tabs] a');
            if (viewLink) {
                event.preventDefault();
                const previousLimit = candidateLimit(currentView, candidatesForStage(currentStage));
                currentView = viewLink.getAttribute('data-view-value') || 'top5';
                const nextLimit = candidateLimit(currentView, candidatesForStage(currentStage));
                renderCandidateSlice({ expanding: nextLimit > previousLimit });
                return;
            }

            const stageLink = event.target.closest('[data-stage-filter] a');
            if (stageLink) {
                event.preventDefault();
                currentStage = parseInt(stageLink.getAttribute('data-stage-value') || '0', 10) || 0;
                renderCandidateSlice({ expanding: true });
            }
        });
    }

    document.addEventListener('click', async function (event) {
        const phoneButton = event.target.closest('.js-reveal-phone');
        if (!phoneButton) return;
        event.preventDefault();
        const existingPhone = phoneButton.getAttribute('data-phone');
        if (existingPhone) {
            phoneButton.outerHTML = phoneLinkHtml(existingPhone);
            return;
        }
        const participantLinkedInId = phoneButton.getAttribute('data-participant-linkedin-id');
        if (!participantLinkedInId) {
            showToast('Missing participant_linkedin_id for Fullenrich.', 'error');
            return;
        }

        setButtonLoading(phoneButton, true, 'Finding...');
        try {
            const ids = campaignIds();
            const data = await postLeadEnrichment({
                source: 'linkedin',
                participant_id: participantLinkedInId,
                pipeline_id: Number(ids.campaign_id || ids.recruiter_campaign_id || 0) || undefined,
                pipeline_filter: {
                    source: 'recruiter',
                    enrich_type: 'phone',
                    recruiter_campaign_id: ids.recruiter_campaign_id,
                    campaign_id: ids.campaign_id,
                    candidate_id: phoneButton.getAttribute('data-candidate-id'),
                    linkedin_url: phoneButton.getAttribute('data-linkedin'),
                    name: phoneButton.getAttribute('data-name'),
                    title: phoneButton.getAttribute('data-title'),
                    company_name: phoneButton.getAttribute('data-company'),
                    fields: ['key_employee.employee.phone', 'phonenumber', 'phonenumber_2', 'phone']
                }
            });
            const enrichedLead = Array.isArray(data) ? (data[0] || {}) : (typeof data === 'string' ? { phone: data } : (data.lead || data.data || data));
            const keyEmployee = enrichedLead.key_employee && enrichedLead.key_employee.employee ? enrichedLead.key_employee.employee : {};
            const phone = (typeof data === 'string' ? data : '') || data.phone || data.phonenumber || data.phone_number || enrichedLead.phone || enrichedLead.phonenumber || enrichedLead.phonenumber_2 || keyEmployee.phone || '';
            if (!phone) throw new Error('No phone number found yet.');
            phoneButton.setAttribute('data-phone', phone);
            phoneButton.outerHTML = phoneLinkHtml(phone);
        } catch (error) {
            setButtonLoading(phoneButton, false);
            showToast(error.message, 'error');
        }
    });

    const createForm = qs('#recruiterCreateForm');
    if (createForm) {
        const titleInput = qs('#campaignTitle');
        const ownerInput = qs('#campaignOwner');
        const countryInput = qs('#campaignCountry');
        const termsInput = qs('#campaignTerms');
        const messageInput = qs('#campaignMessage');
        const preview = qs('#createPreview');

        function countItems(value) {
            return String(value || '')
                .split(',')
                .map(item => item.trim())
                .filter(Boolean).length;
        }

        function selectedText(select) {
            if (!select || select.selectedIndex < 0) return '';
            return (select.options[select.selectedIndex].textContent || '').trim();
        }

        function updatePreview() {
            if (!preview) return;
            const title = (titleInput && titleInput.value.trim()) || 'New campaign';
            const country = (countryInput && countryInput.value.trim()) || 'No country selected';
            const owner = selectedText(ownerInput) || 'No owner selected';
            const titleCount = termsInput ? countItems(termsInput.value) : 0;
            const skillCount = qsa('[data-skill-criteria-row] input[name$="[skill]"]', createForm).filter(input => input.value.trim()).length;
            const messageLength = messageInput && messageInput.value.trim() ? messageInput.value.trim().length : 0;
            const titleTarget = qs('[data-preview-title]', preview);
            const metaTarget = qs('[data-preview-meta]', preview);
            const identifyTarget = qs('[data-preview-identify]', preview);
            const connectTarget = qs('[data-preview-connect]', preview);
            const messageTarget = qs('[data-preview-message]', preview);
            if (titleTarget) titleTarget.textContent = title;
            if (metaTarget) metaTarget.textContent = country + ' · ' + owner;
            if (identifyTarget) identifyTarget.textContent = 'Identify · ' + titleCount + ' ' + (titleCount === 1 ? 'title' : 'titles');
            if (connectTarget) connectTarget.textContent = 'Connect · ' + skillCount + ' ' + (skillCount === 1 ? 'skill' : 'skills');
            if (messageTarget) messageTarget.textContent = messageLength ? 'Message · ' + messageLength + ' characters' : 'Message · Not written';
        }

        function visitWorkflow() {
            return {
                nodes: [
                    {
                        id: 'opportunity-1',
                        type: 'opportunity',
                        label: 'Opportunity',
                        x: 120,
                        y: 120,
                        data: { label: 'Opportunity', recruiter_stage: 1 }
                    },
                    {
                        id: 'wait-1',
                        type: 'wait',
                        label: 'Wait',
                        x: 360,
                        y: 120,
                        data: { label: 'Wait', amount: 1, unit: 'minute', recruiter_stage: 1 }
                    },
                    {
                        id: 'visit-1',
                        type: 'visit',
                        label: 'Identify',
                        x: 600,
                        y: 120,
                        data: { label: 'Identify', recruiter_stage: 1 }
                    }
                ],
                edges: [
                    { id: 'opportunity-1-wait-1', source: 'opportunity-1', target: 'wait-1' },
                    { id: 'wait-1-visit-1', source: 'wait-1', target: 'visit-1' }
                ]
            };
        }

        ['input', 'change'].forEach(function (eventName) {
            createForm.addEventListener(eventName, updatePreview);
        });
        updatePreview();

        createForm.addEventListener('submit', async function (event) {
            if (!createForm.getAttribute('action')) return;
            event.preventDefault();
            const submit = qs('button[type="submit"]', createForm);
            const formData = new FormData(createForm);
            const steps = {};
            formData.forEach(function (value, key) {
                const match = key.match(/^steps\[(\d+)\]\[([^\]]+)\]$/);
                if (match) {
                    const stepNumber = match[1];
                    const settingKey = match[2];
                    if (!steps[stepNumber]) steps[stepNumber] = {};
                    steps[stepNumber][settingKey] = value;
                }
            });
            const skillCriteria = weightedSkillCriteriaFromForm(formData);
            if (skillCriteria.length) {
                if (!steps[2]) steps[2] = {};
                steps[2].skill_criteria = skillCriteria;
                steps[2].skills = skillCriteria.map(item => item.skill).join(', ');
            }
            steps[1] = Object.assign({}, steps[1] || {}, visitWorkflow());

            const payload = {
                action: 'create_campaign',
                title: formData.get('title'),
                owner_id: formData.get('owner_id'),
                employee_ids: formData.get('owner_id') ? [Number(formData.get('owner_id')) || formData.get('owner_id')] : [],
                employee_title: formData.get('steps[1][titles]'),
                country: formData.get('country'),
                source: {
                    employee_title: formData.get('steps[1][titles]'),
                    country: formData.get('country')
                },
                sequence: [
                    { type: 'opportunity', action: 'opportunity' },
                    { type: 'wait', action: 'wait', amount: 1, unit: 'minute' }
                ],
                steps: steps
            };

            setButtonLoading(submit, true, 'Creating...');
            try {
                const data = await postJson(payload);
                showToast('Campaign created.');
                if (data.recruiter_campaign_id || data.id) {
                    window.location.href = 'recruiters-campaign.php?id=' + encodeURIComponent(data.recruiter_campaign_id || data.id);
                } else if (data.redirect_url) {
                    window.location.href = data.redirect_url;
                }
            } catch (error) {
                setButtonLoading(submit, false);
                showToast(error.message, 'error');
            }
        });
    }
})();
