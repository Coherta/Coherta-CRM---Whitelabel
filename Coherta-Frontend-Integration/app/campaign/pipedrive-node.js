/* Configuration UI only. CRM operations belong to the workflow executor. */
(function () {
    'use strict';
    window.PipedriveNode = {create};

    function create({fields, onApply}) {
        const modal = document.createElement('div');
        modal.className = 'modal-backdrop';
        modal.id = 'pipedriveModalBackdrop';
        modal.innerHTML = `<style>
            #pipedriveModalBackdrop .pipedrive-mapping-row{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:12px}
            #pipedriveModalBackdrop .pipedrive-mapping-row .field{min-width:0}
            #pipedriveModalBackdrop .pipedrive-dynamic-row,#pipedriveModalBackdrop .pipedrive-mapping-head{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr) 74px;gap:12px;align-items:start}
            #pipedriveModalBackdrop .pipedrive-mapping-head{margin-bottom:10px;font-size:13px;font-weight:700}
            #pipedriveModalBackdrop .pipedrive-dynamic-row .pipedrive-mapping-row{grid-column:span 2}
            #pipedriveModalBackdrop .pipedrive-dynamic-row label{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap}
            #pipedriveModalBackdrop .pipedrive-dynamic-row button{margin-top:4px}
            @media(max-width:540px){#pipedriveModalBackdrop .pipedrive-mapping-row{grid-template-columns:minmax(0,1fr);gap:0;margin-bottom:16px}}
        </style><div class="modal" role="dialog" aria-modal="true" aria-labelledby="pipedriveTitle">
            <div class="modal-head"><div><h2 id="pipedriveTitle">Pipedrive</h2><p>Configure how Coherta fields map to your CRM.</p></div><button type="button" data-close>Close</button></div>
            <p data-progress aria-live="polite"></p>
            <form novalidate><div data-fields></div><p data-error role="alert" style="color:var(--danger)"></p>
            <div class="modal-actions"><button type="button" data-close>Cancel</button><button type="button" data-back>Back</button><button type="submit" data-next>Next</button></div></form></div>`;
        document.body.appendChild(modal);
        const form = modal.querySelector('form'), content = modal.querySelector('[data-fields]');
        let draft, step = 0, nodeId, previousFocus, migratedFromCopy = false;
        const entities = {deal:'Deal', lead:'Lead', contact:'Person', company:'Organization'};
        const profile = {"name": "Pipedrive", "deal": "title", "lead": "title", "amount": "value", "contact": {"participant_name": "name", "participant_email": "emails", "participant_phonenumber": "phones"}, "company": {"company_name": "name", "company_website": "website", "company_linkedin_url": "linkedin", "number_of_employees": "employee_count"}, "connection": {"companyDomain": "", "apiToken": ""}};
        const defaultValueExpectations = '- Companies with less than 100 employees is usually 10.000\n- Companies with less than 200 employees is usually 30.000';
        const copy = value => JSON.parse(JSON.stringify(value));
        const esc = value => String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
        const get = path => path.split('.').reduce((value, key) => value?.[key], draft);
        function set(path, value) {
            const keys = path.split('.');
            const key = keys.pop();
            keys.reduce((object, part) => object[part] ||= {}, draft)[key] = value;
        }
        function input(path, label, placeholder = '', type = 'text', required = true) {
            return `<div class="field"><label for="pipedrive-${path}">${esc(label)}</label><input id="pipedrive-${path}" data-path="${path}" type="${type}" value="${esc(get(path))}" placeholder="${esc(placeholder)}" ${required?'required':''} ${type==='number'?'min="0" step="any"':''}></div>`;
        }
        function select(path, label, options, required = true) {
            return `<div class="field"><label for="pipedrive-${path}">${esc(label)}</label><select id="pipedrive-${path}" data-path="${path}" ${required?'required':''}><option value="">Choose…</option>${options.map(([value, text]) => `<option value="${esc(value)}" ${get(path)===value?'selected':''}>${esc(text)}</option>`).join('')}</select></div>`;
        }
        function sourceOptions() {
            const options = fields.map(field => [field.value, field.label]);
            if (!options.some(([value]) => value === 'id')) options.push(['id', 'Lead ID']);
            if (!options.some(([value]) => value === 'participant_id')) options.push(['participant_id', 'Participant ID']);
            if (!options.some(([value]) => value === 'company_linkedin_url')) options.push(['company_linkedin_url', 'Company: LinkedIn URL']);
            if (!options.some(([value]) => value === 'latest_sent_automatic_message')) options.push(['latest_sent_automatic_message', 'Latest sent automatic message']);
            if (!options.some(([value]) => value === 'automated_screening_score')) options.push(['automated_screening_score', 'Automated Screening Score']);
            if (!options.some(([value]) => value === 'automated_screening_reason')) options.push(['automated_screening_reason', 'Automated Screening Reason']);
            return options;
        }
        function source(path, label, required = true) {
            const options = sourceOptions();
            if (get(path) && !options.some(([value]) => value === get(path))) options.push([get(path),get(path)]);
            return select(path, label, options, required);
        }
        function toggle(path, label) {
            return `<div class="field"><label><input style="width:auto" type="checkbox" data-path="${path}" ${get(path)?'checked':''}> ${esc(label)}</label></div>`;
        }
        function mapping(entity) {
            return mappingRow(`${entity}.name`, `${entities[entity]} name`);
        }
        function mappingRow(path, label, required = true) {
            return `<div class="pipedrive-mapping-row" data-mapping-pair>`
                + source(`${path}.sourceField`, `Coherta: ${label}`, required)
                + input(`${path}.targetField`, `Pipedrive: ${label}`, 'Pipedrive field code', 'text', required)
                + '</div>';
        }
        function matching(path, label = 'Unique identifier', allowPartial = false) {
            const operators = [['exact','Exact match'],['case_insensitive','Case-insensitive match']];
            if (get(`${path}.operator`) && !operators.some(([value])=>value===get(`${path}.operator`))) operators.push([get(`${path}.operator`), 'Saved comparison: '+get(`${path}.operator`)]);
            return '<p>For Coherta Lead ID or Participant ID, use a dedicated custom CRM field. Do not use the CRM record ID. Names and screening results are not guaranteed to be unique.</p>' + `<h3>${esc(label)}</h3>` + mappingRow(path, 'Identifier field')
                + select(`${path}.operator`, 'How to match', operators);
        }
        function details(entity) {
            return `<section data-mapping-entity="${entity}"><h3>${entities[entity]} details</h3>`
                + '<div class="pipedrive-mapping-head" aria-hidden="true"><span>Coherta field</span><span>Pipedrive field</span><span></span></div>'
                + draft[entity].fieldMappings.map((row, index) => `<div class="pipedrive-dynamic-row">`
                    + mappingRow(`${entity}.fieldMappings.${index}`, `Field ${index+1}`)
                    + `<button type="button" data-remove-mapping="${index}" aria-label="Remove field ${index+1}">Remove</button></div>`).join('')
                + '<button type="button" data-add-mapping>Add field</button></section>';
        }
        function relation(entity) {
            const path = `${draft.entityType}.${entity}Relation`;
            let html = toggle(`${path}.enabled`, `Look up and link an existing ${entities[entity]}`);
            if (get(`${path}.enabled`)) {
                html += matching(`${path}.lookup`, `${entities[entity]} lookup value`, entity === 'company')
                    + toggle(`${path}.createIfMissing`, `Automatically create ${entities[entity]} if no match exists`);
                if (get(`${path}.createIfMissing`)) html += details(entity);
            }
            return html;
        }
        function steps() {
            const entity = draft.entityType;
            const list = [
                {title:'Connection', body:() => input('connection.companyDomain','Pipedrive company domain','your-company') + input('connection.apiToken','API token','','password') + '<p>Enter the subdomain from your Pipedrive account, e.g. your-company from your-company.pipedrive.com. No webhook URL is needed.</p>'},
                {title:'Create record', body:() => select('entityType','What should this node create?',Object.entries(entities))},
                {title:`${entities[entity]} ${['contact','company'].includes(entity)?'details':'name'}`, body:() => ['contact','company'].includes(entity)?details(entity):mapping(entity)}
            ];
            if (entity === 'deal') list.push({title:'Deal value', body:() => {
                const value = draft.deal.value;
                const selected = value.mode === 'llm' ? '__automatic' : value.mode === 'fixed' ? '__fixed' : value.sourceField || '';
                const options = [['__automatic','Calculate Automatically'],['','Choose a Coherta field…'],['__fixed','Fixed amount'],...sourceOptions()];
                if (selected && !options.some(([key]) => key === selected)) options.push([selected,selected]);
                let html = '<div class="pipedrive-mapping-row"><div class="field"><label for="pipedrive-deal-value-source">Coherta Deal Value</label>'
                    + `<select id="pipedrive-deal-value-source" data-deal-value-source required>${options.map(([key,label]) => `<option value="${esc(key)}" ${key===selected?'selected':''}>${esc(label)}</option>`).join('')}</select></div>`
                    + input('deal.value.targetField','Pipedrive: Deal value','Pipedrive field code') + '</div>';
                if (draft.deal.value.mode === 'fixed') html += input('deal.value.amount','Amount','0','number');
                if (draft.deal.value.mode === 'llm') html += `<div class="field"><label for="pipedrive-prompt">Define your value expectations</label><textarea id="pipedrive-prompt" data-path="deal.value.prompt" required>${esc(value.prompt)}</textarea></div>`;
                return html + input('deal.value.currency','Currency code','DKK');
            }});
            if (['deal','lead','company'].includes(entity)) list.push({title:'Contact lookup',body:() => relation('contact')});
            if (['deal','lead','contact'].includes(entity)) list.push({title:'Company lookup',body:() => relation('company')});
            const identifierStep = {title:'Unique identifier',body:() => {
                const path = `${entity}.matching`;
                if (!['contact','company'].includes(entity)) return matching(path);
                return toggle(`${path}.enabled`, `Check whether the ${entities[entity].toLowerCase()} already exists`)
                    + (get(`${path}.enabled`) ? matching(path) : '<p>Create a new record without checking for an existing match.</p>');
            }};
            list.splice(2,0,identifierStep);
            if (entity === 'deal') list.push({title:'Deal stage',body:() => input('deal.pipelineId','Pipedrive pipeline ID','Enter a pipeline ID') + input('deal.stageId','Pipedrive stage ID','Enter a stage ID')});
            return list;
        }
        function render() {
            const list = steps();
            modal.querySelector('[data-progress]').textContent = `Step ${step+1} of ${list.length} · ${list[step].title}`;
            content.innerHTML = list[step].body();
            if (step === 0 && migratedFromCopy) content.insertAdjacentHTML('beforeend', '<p>This setup was copied from Bitrix24. Review the converted fields and enter your CRM connection. The old webhook and quote setup will be removed when you apply.</p>');
            if (step > 1) content.insertAdjacentHTML('beforeend', '<p>Standard fields are suggested when available. For Lead ID, Participant ID, screening score/reason or other custom data, enter an existing custom field '+'API key from Pipedrive Data fields'+'. Pipeline and stage IDs must come from your account.</p>');
            modal.querySelector('[data-error]').textContent = '';
            modal.querySelector('[data-back]').disabled = step === 0;
            modal.querySelector('[data-next]').textContent = step === list.length-1 ? 'Apply configuration' : 'Next';
            content.querySelectorAll('select[data-path$="sourceField"]').forEach(el=>el.dataset.previousSource=el.value);
            content.querySelector('input,select,textarea')?.focus();
        }
        function collect() {
            content.querySelectorAll('[data-path]').forEach(el => set(el.dataset.path, el.type==='checkbox'?el.checked:el.value.trim()));
        }
        function valid() {
            collect();
            content.querySelectorAll('input,textarea,select').forEach(el => {
                el.setCustomValidity('');
                if (el.required && !el.value.trim()) el.setCustomValidity('Please fill out this field.');
            });
            content.querySelectorAll('[data-mapping-pair]').forEach(row => {
                const sourceField = row.querySelector('select'), targetField = row.querySelector('input');
                if (sourceField.value && !targetField.value.trim()) targetField.setCustomValidity('Enter the matching Pipedrive field code.');
                if (['id','hs_object_id'].includes(targetField.value.trim())) targetField.setCustomValidity('Use a custom field for Coherta data; the CRM record ID is assigned by the CRM.');
                if (targetField.value.trim() && !sourceField.value) sourceField.setCustomValidity('Choose the Coherta field to map.');
            });
            const detailSection = content.querySelector('[data-mapping-entity]');
            if (detailSection && !draft[detailSection.dataset.mappingEntity].fieldMappings.length) {
                modal.querySelector('[data-error]').textContent = 'Add at least one field mapping.';
                return false;
            }
            if (step === 0 && profile.connection.companyDomain !== undefined) {
                const el = content.querySelector('[data-path="connection.companyDomain"]');
                if (!/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/i.test(el.value)) el.setCustomValidity('Enter only your Pipedrive subdomain, without https:// or .pipedrive.com.');
            }
            for (const path of ['deal.pipelineId','deal.stageId']) {
                const el = content.querySelector(`[data-path="${path}"]`);
                if (el && !/^[1-9][0-9]*$/.test(el.value)) el.setCustomValidity('Enter a positive numeric ID from your Pipedrive account.');
            }
            const currency = content.querySelector('[data-path="deal.value.currency"]');
            if (currency && !/^[a-z]{3}$/i.test(currency.value)) currency.setCustomValidity('Enter a three-letter currency code.');
            if (draft.entityType === 'lead' && steps()[step].title === 'Company lookup' && !draft.lead.contactRelation.enabled && !draft.lead.companyRelation.enabled) {
                modal.querySelector('[data-error]').textContent = 'A Lead must be linked to a contact/person or company/organization. Enable at least one lookup.';
                return false;
            }
            return form.reportValidity();
        }
        function close() {
            modal.classList.remove('open');
            modal.style.display = 'none';
            previousFocus?.focus();
        }
        modal.querySelectorAll('[data-close]').forEach(button => button.onclick = close);
        modal.querySelector('[data-back]').onclick = () => {collect(); step--; render();};
        content.addEventListener('change', event => {
            collect();
            const path = event.target.dataset.path || '';
            if (path.endsWith('.sourceField')) {
                const targetPath = path.replace(/sourceField$/, 'targetField');
                const entity = path.includes('contactRelation') ? 'contact' : path.includes('companyRelation') ? 'company' : path.split('.')[0];
                const target = content.querySelector(`[data-path="${targetPath}"]`);
                const previousSource = event.target.dataset.previousSource;
                const previousTarget = targetFor(entity, previousSource);
                if (!get(targetPath) || (previousTarget && get(targetPath) === previousTarget)) {
                    set(targetPath,targetFor(entity,event.target.value));
                    if (target) target.value = get(targetPath);
                }
                event.target.dataset.previousSource = event.target.value;
            }
            if (event.target.hasAttribute('data-deal-value-source')) {
                const selected = event.target.value;
                draft.deal.value.mode = selected === '__automatic' ? 'llm' : selected === '__fixed' ? 'fixed' : 'field';
                if (draft.deal.value.mode === 'field') draft.deal.value.sourceField = selected;
                if (draft.deal.value.mode === 'llm') {
                    if (!draft.deal.value.targetField) draft.deal.value.targetField = profile.amount;
                    if (!String(draft.deal.value.prompt || '').trim()) draft.deal.value.prompt = defaultValueExpectations;
                    if (!draft.deal.value.contextField) draft.deal.value.contextField = 'number_of_employees';
                }
                render();
                return;
            }
            if (event.target.type === 'checkbox' || ['entityType','deal.value.mode'].includes(event.target.dataset.path)) render();
        });
        content.addEventListener('click', event => {
            const button = event.target.closest('[data-add-mapping],[data-remove-mapping]');
            if (!button) return;
            collect();
            const entity = button.closest('[data-mapping-entity]').dataset.mappingEntity;
            const rows = draft[entity].fieldMappings;
            const adding = button.hasAttribute('data-add-mapping');
            if (adding) rows.push({sourceField:'',targetField:''});
            else rows.splice(Number(button.dataset.removeMapping),1);
            render();
            const section = content.querySelector('[data-mapping-entity]');
            if (adding) section.querySelectorAll('select')[rows.length-1]?.focus();
            else section.querySelector('[data-add-mapping]').focus();
        });
        modal.addEventListener('keydown', event => {
            if (event.key === 'Escape') {event.stopPropagation(); close();}
            if (event.key === 'Tab') {
                const items = [...modal.querySelectorAll('button:not(:disabled),input,select,textarea')];
                const first = items[0], last = items[items.length-1];
                if (event.shiftKey && document.activeElement === first) {event.preventDefault(); last.focus();}
                else if (!event.shiftKey && document.activeElement === last) {event.preventDefault(); first.focus();}
            }
        });
        form.onsubmit = event => {
            event.preventDefault();
            if (!valid()) return;
            if (step < steps().length-1) {step++; render(); return;}
            // Recheck earlier conditional steps after navigating backwards or changing entity.
            const finalStep = step;
            for (step = 0; step <= finalStep; step++) {render(); if (!valid()) return;}
            draft.deal.value.currency = draft.deal.value.currency.toUpperCase();
            onApply(nodeId, copy(draft));
            close();
        };
        function targetFor(entity, sourceField) {
            if (entity === 'deal' || entity === 'lead') return sourceField === 'company_name' ? profile[entity] : '';
            return profile[entity]?.[sourceField] || '';
        }
        function defaults(entity) {
            const contact = {enabled:true,sourceField:'participant_email',targetField:profile.contact.participant_email,operator:'exact'};
            const company = {enabled:true,sourceField:'company_name',targetField:'name',operator:'exact'};
            return {name:{sourceField:'company_name',targetField:profile[entity] || 'name'},
                matching:entity==='contact'?copy(contact):entity==='company'?copy(company):{enabled:true,sourceField:'id',targetField:'',operator:'exact'},
                contactRelation:{enabled:false,createIfMissing:true,lookup:copy(contact)},
                companyRelation:{enabled:false,createIfMissing:true,lookup:copy(company)}};
        }
        function standardSetup() {
            return {schemaVersion:3,connection:copy(profile.connection),entityType:'deal',
                deal:{...defaults('deal'),value:{mode:'fixed',amount:'100',currency:'DKK',targetField:profile.amount,prompt:defaultValueExpectations,contextField:'number_of_employees',sourceField:''},pipelineId:'',stageId:''},
                lead:defaults('lead'),
                contact:{...defaults('contact'),fieldMappings:Object.entries(profile.contact).map(([sourceField,targetField])=>({sourceField,targetField}))},
                company:{...defaults('company'),fieldMappings:Object.entries(profile.company).map(([sourceField,targetField])=>({sourceField,targetField}))}};
        }
        function merge(base, saved) {
            Object.keys(saved || {}).forEach(key => {
                if (['__proto__','constructor','prototype'].includes(key)) return;
                if (saved[key] && typeof saved[key]==='object' && !Array.isArray(saved[key])) base[key] = merge(base[key] || {}, saved[key]);
                else base[key] = saved[key];
            });
            return base;
        }
        function initializeFieldMappings(entity) {
            const config = draft[entity];
            const legacyKeys = entity === 'contact'
                ? ['name','lastName','jobTitle','linkedinUrl','email','phone']
                : ['name','website','linkedinUrl','vat','address','email','phone'];
            if (!Array.isArray(config.fieldMappings)) {
                const existing = legacyKeys.filter(key => config[key]?.sourceField).map(key => ({sourceField:config[key].sourceField,targetField:config[key].targetField || ''}));
                const presets = entity === 'contact'
                    ? ['participant_first_name','participant_jobtitle','participant_linkedin_url','participant_email','participant_phonenumber']
                    : ['company_name','company_website','company_linkedin_url','vat_code','company_address','company_email','company_phone'];
                config.fieldMappings = existing.length ? existing : presets.map((sourceField,index) => ({sourceField,targetField:targetFor(entity,sourceField)}));
            }
            // The dynamic list is authoritative, so removed fields cannot survive as legacy mappings.
            legacyKeys.forEach(key => delete config[key]);
        }
        function migrateTargets(config, entity) {
            if (!config || typeof config !== 'object') return;
            if (/^FIELDS\[|^(NAME|TITLE|opportunity)$/.test(config.targetField || '')) {
                config.targetField = config.targetField === 'opportunity' ? profile.amount : targetFor(entity, config.sourceField);
            }
            Object.entries(config).forEach(([key,value])=>migrateTargets(value,key==='contactRelation'?'contact':key==='companyRelation'?'company':entity));
        }
        function normalizeConfig(saved) {
            const result = merge(standardSetup(),copy(saved || {}));
            for (const entity of ['contact','company']) {
                if (saved?.[entity] && !Array.isArray(saved[entity].fieldMappings) && ['name','lastName','jobTitle','linkedinUrl','email','phone'].some(key=>saved[entity][key]?.sourceField)) delete result[entity].fieldMappings;
            }
            if (Number(saved?.schemaVersion || 0) < 3) {
                for (const entity of Object.keys(entities)) migrateTargets(result[entity],entity);
                if (result.deal.pipelineId === '0') result.deal.pipelineId = '';
                if (result.deal.stageId === 'NEW') result.deal.stageId = '';
            }
            // Webhooks and the copied Bitrix24 quote payload do not describe these APIs.
            delete result.webhookUrl;
            delete result.deal.quote;
            result.schemaVersion = 3;
            return result;
        }
        return {open(node) {
            nodeId = node.id;
            previousFocus = document.activeElement;
            migratedFromCopy = !!Object.keys(node.config || {}).length && Number(node.config.schemaVersion || 0) < 3;
            draft = normalizeConfig(node.config);
            if (!entities[draft.entityType]) draft.entityType = 'deal';
            initializeFieldMappings('contact');
            initializeFieldMappings('company');

            if (draft.deal.value.mode === 'field' && !draft.deal.value.sourceField) draft.deal.value.mode = 'llm';
            if (draft.deal.value.mode === 'llm') {
                if (!draft.deal.value.targetField) draft.deal.value.targetField = profile.amount;
                if (!draft.deal.value.contextField) draft.deal.value.contextField = 'number_of_employees';
                if (!String(draft.deal.value.prompt || '').trim()) draft.deal.value.prompt = defaultValueExpectations;
            }
            step = 0;
            modal.style.display = 'flex';
            modal.classList.add('open');
            render();
        }};
    }
})();
