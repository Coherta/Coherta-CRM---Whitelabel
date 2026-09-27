/* Configuration UI only. CRM operations belong to the workflow executor. */
(function () {
    'use strict';
    window.Bitrix24Node = {create};

    function create({fields, onApply}) {
        const modal = document.createElement('div');
        modal.className = 'modal-backdrop';
        modal.id = 'bitrix24ModalBackdrop';
        modal.innerHTML = `<style>
            #bitrix24ModalBackdrop .bitrix24-mapping-row{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:12px}
            #bitrix24ModalBackdrop .bitrix24-mapping-row .field{min-width:0}
            #bitrix24ModalBackdrop .bitrix24-dynamic-row,#bitrix24ModalBackdrop .bitrix24-mapping-head{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr) 74px;gap:12px;align-items:start}
            #bitrix24ModalBackdrop .bitrix24-mapping-head{margin-bottom:10px;font-size:13px;font-weight:700}
            #bitrix24ModalBackdrop .bitrix24-dynamic-row .bitrix24-mapping-row{grid-column:span 2}
            #bitrix24ModalBackdrop .bitrix24-dynamic-row label{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap}
            #bitrix24ModalBackdrop .bitrix24-dynamic-row button{margin-top:4px}
            @media(max-width:540px){#bitrix24ModalBackdrop .bitrix24-mapping-row{grid-template-columns:minmax(0,1fr);gap:0;margin-bottom:16px}}
        </style><div class="modal" role="dialog" aria-modal="true" aria-labelledby="bitrix24Title">
            <div class="modal-head"><div><h2 id="bitrix24Title">Bitrix24</h2><p>Configure how Coherta fields map to your CRM.</p></div><button type="button" data-close>Close</button></div>
            <p data-progress aria-live="polite"></p>
            <form novalidate><div data-fields></div><p data-error role="alert" style="color:var(--danger)"></p>
            <div class="modal-actions"><button type="button" data-close>Cancel</button><button type="button" data-back>Back</button><button type="submit" data-next>Next</button></div></form></div>`;
        document.body.appendChild(modal);
        const form = modal.querySelector('form'), content = modal.querySelector('[data-fields]');
        let draft, step = 0, nodeId, previousFocus;
        const entities = {deal:'Deal', lead:'Lead', contact:'Contact', company:'Company'};
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
            return `<div class="field"><label for="b24-${path}">${esc(label)}</label><input id="b24-${path}" data-path="${path}" type="${type}" value="${esc(get(path))}" placeholder="${esc(placeholder)}" ${required?'required':''} ${type==='number'?'min="0" step="any"':''}></div>`;
        }
        function select(path, label, options, required = true) {
            return `<div class="field"><label for="b24-${path}">${esc(label)}</label><select id="b24-${path}" data-path="${path}" ${required?'required':''}><option value="">Choose…</option>${options.map(([value, text]) => `<option value="${esc(value)}" ${get(path)===value?'selected':''}>${esc(text)}</option>`).join('')}</select></div>`;
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
            return `<div class="bitrix24-mapping-row" data-mapping-pair>`
                + source(`${path}.sourceField`, `Coherta: ${label}`, required)
                + input(`${path}.targetField`, `Bitrix24: ${label}`, 'Bitrix24 field code', 'text', required)
                + '</div>';
        }
        function matching(path, label = 'Unique identifier', allowPartial = false) {
            const operators = [['exact','Exact match'],['case_insensitive','Case-insensitive match']];
            if (allowPartial) operators.push(['contains','Partial Match'],['starts_with','Match to the left'],['ends_with','Match to the right']);
            return `<h3>${esc(label)}</h3>` + mappingRow(path, 'Identifier field')
                + select(`${path}.operator`, 'How to match', operators);
        }
        function details(entity) {
            return `<section data-mapping-entity="${entity}"><h3>${entities[entity]} details</h3>`
                + '<div class="bitrix24-mapping-head" aria-hidden="true"><span>Coherta field</span><span>Bitrix24 field</span><span></span></div>'
                + draft[entity].fieldMappings.map((row, index) => `<div class="bitrix24-dynamic-row">`
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
                {title:'Webhook URL', body:() => input('webhookUrl','Webhook URL','https://your-portal.example/rest/user/token/','url')},
                {title:'Create record', body:() => select('entityType','What should this node create?',Object.entries(entities))},
                {title:`${entities[entity]} ${['contact','company'].includes(entity)?'details':'name'}`, body:() => ['contact','company'].includes(entity)?details(entity):mapping(entity)}
            ];
            if (entity === 'deal') list.push({title:'Deal value', body:() => {
                const value = draft.deal.value;
                const selected = value.mode === 'llm' ? '__automatic' : value.mode === 'fixed' ? '__fixed' : value.sourceField || '';
                const options = [['__automatic','Calculate Automatically'],['','Choose a Coherta field…'],['__fixed','Fixed amount'],...sourceOptions()];
                if (selected && !options.some(([key]) => key === selected)) options.push([selected,selected]);
                let html = '<div class="bitrix24-mapping-row"><div class="field"><label for="b24-deal-value-source">Coherta Deal Value</label>'
                    + `<select id="b24-deal-value-source" data-deal-value-source required>${options.map(([key,label]) => `<option value="${esc(key)}" ${key===selected?'selected':''}>${esc(label)}</option>`).join('')}</select></div>`
                    + input('deal.value.targetField','Bitrix24: Deal value','Bitrix24 field code') + '</div>';
                if (draft.deal.value.mode === 'fixed') html += input('deal.value.amount','Amount','0','number');
                if (draft.deal.value.mode === 'llm') html += `<div class="field"><label for="b24-prompt">Define your value expectations</label><textarea id="b24-prompt" data-path="deal.value.prompt" required>${esc(value.prompt)}</textarea></div>`;
                return html + input('deal.value.currency','Currency code','DKK');
            }});
            if (['deal','company'].includes(entity)) list.push({title:'Contact lookup',body:() => relation('contact')});
            if (['deal','contact'].includes(entity)) list.push({title:'Company lookup',body:() => relation('company')});
            if (entity === 'deal') list.push({title:'Quote',body:() => toggle('deal.quote.enabled','Create a quote for the linked contact') + (draft.deal.quote.enabled
                ? input('deal.quote.productId','Bitrix24 product ID','Enter a product ID') + input('deal.quote.quantity','Quantity','1','number') : '')});
            const identifierStep = {title:'Unique identifier',body:() => {
                const path = `${entity}.matching`;
                if (!['contact','company'].includes(entity)) return matching(path);
                return toggle(`${path}.enabled`, `Check whether the ${entities[entity].toLowerCase()} already exists`)
                    + (get(`${path}.enabled`) ? matching(path) : '<p>Create a new record without checking for an existing match.</p>');
            }};
            list.splice(2,0,identifierStep);
            if (entity === 'deal') list.push({title:'Deal stage',body:() => input('deal.pipelineId','Bitrix24 pipeline ID','Enter a pipeline ID') + input('deal.stageId','Bitrix24 stage ID','Enter a stage ID')});
            return list;
        }
        function render() {
            const list = steps();
            modal.querySelector('[data-progress]').textContent = `Step ${step+1} of ${list.length} · ${list[step].title}`;
            content.innerHTML = list[step].body();
            modal.querySelector('[data-error]').textContent = '';
            modal.querySelector('[data-back]').disabled = step === 0;
            modal.querySelector('[data-next]').textContent = step === list.length-1 ? 'Apply configuration' : 'Next';
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
                if (sourceField.value && !targetField.value.trim()) targetField.setCustomValidity('Enter the matching Bitrix24 field code.');
                if (targetField.value.trim() && !sourceField.value) sourceField.setCustomValidity('Choose the Coherta field to map.');
            });
            const detailSection = content.querySelector('[data-mapping-entity]');
            if (detailSection && !draft[detailSection.dataset.mappingEntity].fieldMappings.length) {
                modal.querySelector('[data-error]').textContent = 'Add at least one field mapping.';
                return false;
            }
            if (step === 0) {
                const el = content.querySelector('input');
                try { const url = new URL(draft.webhookUrl); if (!['https:','http:'].includes(url.protocol) || !url.hostname || url.username || url.password) throw new Error(); }
                catch { el.setCustomValidity('Enter a valid HTTP or HTTPS webhook URL.'); }
            }
            const currency = content.querySelector('[data-path="deal.value.currency"]');
            if (currency && !/^[a-z]{3}$/i.test(currency.value)) currency.setCustomValidity('Enter a three-letter currency code.');
            const quantity = content.querySelector('[data-path="deal.quote.quantity"]');
            if (quantity && Number(quantity.value) <= 0) quantity.setCustomValidity('Quantity must be greater than zero.');
            if (draft.entityType === 'deal' && draft.deal.quote.enabled && steps()[step].title === 'Quote' && !draft.deal.contactRelation.enabled) {
                modal.querySelector('[data-error]').textContent = 'Enable Contact lookup before creating a quote for the linked contact.';
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
            if (event.target.hasAttribute('data-deal-value-source')) {
                const selected = event.target.value;
                draft.deal.value.mode = selected === '__automatic' ? 'llm' : selected === '__fixed' ? 'fixed' : 'field';
                if (draft.deal.value.mode === 'field') draft.deal.value.sourceField = selected;
                if (draft.deal.value.mode === 'llm') {
                    draft.deal.value.targetField = 'opportunity';
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
        function defaults(entity) {
            return {name:{sourceField:'',targetField:entity==='contact'?'FIELDS[NAME]':'FIELDS[TITLE]'},matching:{enabled:true,targetField:'',sourceField:'',operator:'exact'},contactRelation:{enabled:false,createIfMissing:true,lookup:{operator:'exact'}},companyRelation:{enabled:false,createIfMissing:true,lookup:{operator:'exact'}}};
        }
        function standardSetup() {
            return {
                schemaVersion:2,webhookUrl:'',entityType:'deal',
                deal:{
                    ...defaults('deal'),
                    name:{sourceField:'company_name',targetField:'FIELDS[TITLE]'},
                    matching:{enabled:true,targetField:'FIELDS[TITLE]',sourceField:'company_name',operator:'case_insensitive'},
                    contactRelation:{enabled:true,createIfMissing:true,lookup:{operator:'exact',sourceField:'participant_linkedin_url',targetField:'FIELDS[WEB]'}},
                    companyRelation:{enabled:true,createIfMissing:true,lookup:{operator:'exact',sourceField:'company_name',targetField:'FIELDS[TITLE]'}},
                    value:{mode:'fixed',currency:'DKK',prompt:defaultValueExpectations,targetField:'opportunity',contextField:'number_of_employees',sourceField:'',amount:'100'},
                    quote:{enabled:true,quantity:'3',productId:'2'},pipelineId:'0',stageId:'NEW'
                },
                lead:defaults('lead'),
                contact:{...defaults('contact'),fieldMappings:[
                    {sourceField:'participant_first_name',targetField:'FIELDS[NAME]'},
                    {sourceField:'participant_last_name',targetField:'FIELDS[LAST_NAME]'},
                    {sourceField:'participant_linkedin_url',targetField:'FIELDS[WEB]'}
                ]},
                company:{...defaults('company'),fieldMappings:[{sourceField:'company_name',targetField:'FIELDS[TITLE]'}]}
            };
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
                config.fieldMappings = existing.length ? existing : presets.map((sourceField,index) => ({sourceField,targetField:index===0?(entity==='contact'?'FIELDS[NAME]':'FIELDS[TITLE]'):''}));
            }
            // The dynamic list is authoritative, so removed fields cannot survive as legacy mappings.
            legacyKeys.forEach(key => delete config[key]);
        }
        function normalizeTargetFields(config) {
            if (!config || typeof config !== 'object') return;
            if (config.targetField === 'NAME' || config.targetField === 'TITLE') config.targetField = `FIELDS[${config.targetField}]`;
            Object.values(config).forEach(normalizeTargetFields);
        }
        return {open(node) {
            nodeId = node.id;
            previousFocus = document.activeElement;
            draft = merge({schemaVersion:1,webhookUrl:'',entityType:'deal',deal:{...defaults('deal'),value:{mode:'llm',currency:'DKK',prompt:''},quote:{enabled:false,quantity:'1'}},lead:defaults('lead'),contact:defaults('contact'),company:defaults('company')},copy(node.config || {}));
            if (!Object.keys(node.config || {}).length) draft = standardSetup();
            if (!entities[draft.entityType]) draft.entityType = 'deal';
            initializeFieldMappings('contact');
            initializeFieldMappings('company');
            normalizeTargetFields(draft);
            draft.schemaVersion = 2;
            if (draft.deal.value.mode === 'field' && !draft.deal.value.sourceField) draft.deal.value.mode = 'llm';
            if (draft.deal.value.mode === 'llm') {
                draft.deal.value.targetField = 'opportunity';
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
