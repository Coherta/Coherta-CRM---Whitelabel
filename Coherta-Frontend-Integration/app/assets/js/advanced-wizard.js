let templateCards = document.querySelectorAll('.template-card');
const templateGridPreview = document.querySelector('.template-grid-preview');
const templateGrid = document.querySelector('.template-grid');
const toggleTemplateExpandBtn = document.getElementById('toggleTemplateExpand');
const activeFilterRules = document.getElementById('activeFilterRules');
const websiteInfoInline = document.getElementById('websiteInfoInline');
const websiteInfoCheckbox = document.getElementById('websiteInfoCheckbox');
const summarySelectedFilterCount = document.getElementById('summarySelectedFilterCount');
const categoryOverview = document.getElementById('filterCategoryOverview');
const categoryDetail = document.getElementById('filterCategoryDetail');
const categoryActions = document.getElementById('filterCategoryActions');
const categoryTitle = document.getElementById('filterCategoryTitle');
const categoryDescription = document.getElementById('filterCategoryDescription');
const categoryCount = document.getElementById('filterCategoryCount');
const categoryBack = document.getElementById('filterCategoryBack');
const jobTitleInput = document.getElementById('jobtitle-input');
const addJobtitleBtn = document.getElementById('add-jobtitle-btn');
const jobtitleList = document.getElementById('jobtitle-list');
const addLeadlistBtn = document.getElementById('addLeadlistBtn');
const leadListModal = document.getElementById('leadListModal');
const closeLeadListModalBtn = document.getElementById('closeLeadListModal');
const cancelLeadListModalBtn = document.getElementById('cancelLeadListModal');
const createLeadListModalBtn = document.getElementById('createLeadListModal');
const leadListTitleInput = document.getElementById('title');
const originSelect = document.getElementById('origin');
const countrySummaryTrigger = document.getElementById('countrySummaryTrigger');
const summaryCountryValue = document.getElementById('summaryCountryValue');
const countryModal = document.getElementById('countryModal');
const closeCountryModalBtn = document.getElementById('closeCountryModal');
const cancelCountryModalBtn = document.getElementById('cancelCountryModal');
const countryOptionGrid = document.getElementById('countryOptionGrid');
const summaryFilters = document.getElementById('summaryFilters');

const activeFiltersModal = document.getElementById('activeFiltersModal');
const activeFiltersModalList = document.getElementById('activeFiltersModalList');
const closeActiveFiltersModalBtn = document.getElementById('closeActiveFiltersModal');
const closeActiveFiltersModalFooterBtn = document.getElementById('closeActiveFiltersModalFooter');
const bearerToken = document.body.getAttribute('session_id');
let filterFieldMapping = Array.isArray(window.filterFieldMapping) ? window.filterFieldMapping : [];
let technologyAutocompleteByField = window.technologyAutocompleteByField || {};
let allSoftwareTags = Array.isArray(window.allSoftwareTags) ? window.allSoftwareTags : [];
let technologyAutocompleteLoaded = false;
let technologyAutocompleteRequestStarted = false;

let currentFilterRequest = null;
let currentFilterRequestId = 0;
let activeFiltersJsonString = '';
let renderActiveCompaniesTimer = null;
let websiteInfo = false;
let removeNonMatchingUnhandledLeads = false;

let jobtitles = [];
const defaultJobtitles = ['CEO', 'CTO', 'CFO', 'COO', 'Founder', 'Co-Founder', 'Owner', 'Managing Director', 'Director', 'Adm. Dir', 'Direktør'];

const filterCategories = [
    {
        "key": "basic-information",
        "title": "Basic Information",
        "description": "Core company data and essential master fields.",
        "filters": [
            {
                "key": "company_name",
                "label": "Company Name"
            },
            {
                "key": "start_date",
                "label": "Founding Date"
            },
            {
                "key": "main_industry",
                "label": "Industry"
            },
            {
                "key": "category_code",
                "label": "Category Code"
            },
            {
                "key": "company_purpose",
                "label": "Company Purpose"
            },
            {
                "key": "company_form",
                "label": "Company Form"
            }
        ]
    },
    {
        "key": "geography",
        "title": "Geography",
        "description": "Location and address-based filters.",
        "filters": [
            {
                "key": "postal_code",
                "label": "Postal Code"
            },
            {
                "key": "road_name",
                "label": "Road Name"
            },
            {
                "key": "city",
                "label": "City Name"
            }
        ]
    },
    {
        "key": "contact-information",
        "title": "Contact Information",
        "description": "Direct contact points for the company.",
        "filters": [
            {
                "key": "phone",
                "label": "Phone Number"
            },
            {
                "key": "email",
                "label": "Email"
            },
            {
                "key": "website",
                "label": "Website"
            }
        ]
    },
    {
        "key": "finance",
        "title": "Finance",
        "description": "Company size and financial result filters.",
        "filters": [
            {
                "key": "employees",
                "label": "Number of Employees"
            },
            {
                "key": "financials_profit_loss",
                "label": "Financial Results: Profit/Loss"
            },
            {
                "key": "financials_ebit",
                "label": "Financial Results: EBIT"
            },
            {
                "key": "financials_equity",
                "label": "Financial Results: Equity"
            },
            {
                "key": "financials_cash",
                "label": "Financial Results: Cash"
            },
            {
                "key": "financials_net",
                "label": "Financial Results: Net Income"
            }
        ]
    },
    {
        "key": "presentation-readiness",
        "title": "Presentation Readiness",
        "description": "Signals that help qualify and present companies more clearly.",
        "filters": [
            {
                "key": "trustpilot_reviews",
                "label": "Trustpilot Reviews"
            },
            {
                "key": "trustpilot_score",
                "label": "Trustpilot Score"
            }
        ]
    },
    {
        "key": "website-information",
        "title": "Website Information",
        "description": "Commercial data and Lighthouse measurements from the company website.",
        "filters": [
            {
                "key": "website_avg_price",
                "label": "Average Product Price"
            },
            {
                "key": "website_total_products",
                "label": "Total Products"
            },
            {
                "key": "lighthouse_performance",
                "label": "Lighthouse Performance Score"
            },
            {
                "key": "lighthouse_accessibility",
                "label": "Lighthouse Accessibility"
            },
            {
                "key": "lighthouse_best_practices",
                "label": "Lighthouse Best Practices"
            },
            {
                "key": "lighthouse_seo",
                "label": "Lighthouse SEO"
            },
            {
                "key": "lighthouse_first_contentful_paint_seconds",
                "label": "Lighthouse First Contentful Paint (s)"
            },
            {
                "key": "lighthouse_largest_contentful_paint_seconds",
                "label": "Lighthouse Largest Contentful Paint (s)"
            },
            {
                "key": "lighthouse_total_blocking_time_ms",
                "label": "Lighthouse Total Blocking Time (ms)"
            },
            {
                "key": "lighthouse_cumulative_layout_shift",
                "label": "Lighthouse Cumulative Layout Shift"
            },
            {
                "key": "lighthouse_speed_index_seconds",
                "label": "Lighthouse Speed Index (s)"
            }
        ]
    },
    {
        "key": "technologies",
        "title": "Technologies",
        "description": "Technographic signals, software tags found on the website.",
        "filters": [
            {
                "key": "website_analytics_tool",
                "label": "Website Analytics"
            },
            {
                "key": "website_chat_platform",
                "label": "Website Chat"
            },
            {
                "key": "website_cms_type",
                "label": "Website CMS"
            },
            {
                "key": "website_framework",
                "label": "Website Framework"
            },
            {
                "key": "website_ecommerce",
                "label": "Website Webshop Platform"
            },
            {
                "key": "payment_systems",
                "label": "Website Payment Method"
            },
            {
                "key": "website_widgets",
                "label": "Website Widgets"
            },
            {
                "key": "website_software_systems",
                "label": "All Software Tags"
            }
        ]
    },
    {
        "key": "contacts",
        "title": "Contacts",
        "description": "Filters targeted at identified contacts and stakeholders.",
        "filters": [
            {
                "key": "employee_title",
                "label": "Employee Title"
            }
        ]
    },
    {
        "key": "recruiter",
        "title": "Recruiter",
        "description": "Recruiting-oriented filters and hiring signals from active job activity.",
        "filters": [
            {
                "key": "recruiter_number_of_open_positions",
                "label": "Number of Open Positions"
            },
            {
                "key": "recruiter_oldest_open_position",
                "label": "Earliest Open Position"
            },
            {
                "key": "recruiter_latest_open_position",
                "label": "Latest Open Position"
            },
            {
                "key": "recruiter_ats_provider",
                "label": "ATS Provider"
            },
            {
                "key": "recruiter_jobpost_title",
                "label": "Jobpost Titel"
            },
            {
                "key": "recruiter_jobpost_description",
                "label": "Jobpost Description"
            }
        ]
    }
];

const filterExplainers = {
    company_name: 'Find companies by legal name or brand wording.',
    start_date: 'Limit results by how recently the company was founded.',
    main_industry: 'Match companies by their main industry classification.',
    category_code: 'Filter by internal or official business category code.',
    company_purpose: 'Search in the registered company purpose or business description.',
    company_form: 'Limit the list to specific legal entity types.',
    postal_code: 'Focus on companies in specific postal code areas.',
    road_name: 'Find companies registered on a specific street name.',
    city: 'Limit results to one or more cities.',
    phone: 'Match companies by available phone number data.',
    email: 'Match companies by available email address data.',
    website: 'Search for companies with a specific website or domain pattern.',
    employees: 'Filter by estimated or reported employee count.',
    financials_profit_loss: 'Use latest profit or loss figures to narrow the list.',
    financials_ebit: 'Filter by EBIT to target operational performance.',
    financials_equity: 'Limit companies by equity level.',
    financials_cash: 'Filter based on available cash or cash equivalents.',
    financials_net: 'Filter based on Net Income.',
    trustpilot_reviews: 'Find companies by the number of Trustpilot reviews.',
    trustpilot_score: 'Find companies by their Trustpilot rating.',
    website_avg_price: 'Estimate company fit based on average product pricing on the website.',
    website_total_products: 'Filter by how many products are detected on the website.',
    website_analytics_tool: 'Match companies using a specific analytics setup.',
    website_chat_platform: 'Find websites with a specific chat or support tool.',
    website_cms_type: 'Filter by the content management system used on the website.',
    website_framework: 'Find websites built with a specific frontend or backend framework.',
    website_ecommerce: 'Filter by the ecommerce platform running the webshop.',
    payment_systems: 'Find sites that expose one or more specific payment methods.',
    website_widgets: 'Target sites using specific on-page widgets or plugins.',
    website_software_systems: 'Search across all detected software technologies on the site.',
    employee_title: 'Find companies where identified contacts match a given title.',
    lighthouse_performance: 'Filter websites by their performance audit score.',
    lighthouse_accessibility: 'Filter websites by accessibility audit score.',
    lighthouse_best_practices: 'Filter websites by best-practice audit score.',
    lighthouse_seo: 'Filter websites by SEO audit score.',
    lighthouse_first_contentful_paint_seconds: 'Limit websites by first contentful paint speed.',
    lighthouse_largest_contentful_paint_seconds: 'Limit websites by largest contentful paint speed.',
    lighthouse_total_blocking_time_ms: 'Filter websites by how much main-thread blocking time they have.',
    lighthouse_cumulative_layout_shift: 'Filter websites by visual stability score.',
    lighthouse_speed_index_seconds: 'Filter websites by overall visual loading speed.',
    recruiter_number_of_open_positions: 'Filter companies by how many active job openings they currently have.',
    recruiter_open_positions: 'Search across currently open job titles or position names.',
    recruiter_latest_open_position: 'Match companies by the most recently detected open position.',
    recruiter_ats_provider: 'Filter by the applicant tracking system or hiring platform used by the company.',
    recruiter_jobpost_title: 'Filter by the applicant job title.',
    recruiter_jobpost_description: 'Filter by the applicant job description.',
};


function normalizeTechnologyAutocompleteValue(value) {
    return String(value ?? '').trim();
}

function mergeUniqueAutocompleteValues(target, values) {
    const output = Array.isArray(target) ? target.slice() : [];
    const seen = new Set(output.map(item => String(item).toLowerCase()));

    (Array.isArray(values) ? values : []).forEach(value => {
        const cleanValue = normalizeTechnologyAutocompleteValue(value);
        if (!cleanValue) return;

        const key = cleanValue.toLowerCase();
        if (seen.has(key)) return;

        seen.add(key);
        output.push(cleanValue);
    });

    output.sort((a, b) => String(a).localeCompare(String(b), undefined, { sensitivity: 'base' }));
    return output;
}

function buildTechnologyAutocompleteFromMapping(mapping) {
    const byField = {};
    const allTags = [];

    (Array.isArray(mapping) ? mapping : []).forEach(group => {
        if (!group || typeof group !== 'object') return;

        const fieldKey = normalizeTechnologyAutocompleteValue(group.field);
        const options = Array.isArray(group.options) ? group.options : [];

        if (!fieldKey) return;
        if (!Array.isArray(byField[fieldKey])) byField[fieldKey] = [];

        options.forEach(option => {
            if (!option || typeof option !== 'object') return;

            const value = normalizeTechnologyAutocompleteValue(option.value || option.label);
            if (!value) return;

            byField[fieldKey].push(value);
            allTags.push(value);
        });
    });

    Object.keys(byField).forEach(fieldKey => {
        byField[fieldKey] = mergeUniqueAutocompleteValues([], byField[fieldKey]);
    });

    return {
        byField,
        allTags: mergeUniqueAutocompleteValues([], allTags)
    };
}

function setTechnologyAutocompleteMapping(mapping) {
    if (!Array.isArray(mapping) || !mapping.length) return false;

    const built = buildTechnologyAutocompleteFromMapping(mapping);

    filterFieldMapping = mapping;
    technologyAutocompleteByField = built.byField;
    allSoftwareTags = built.allTags;

    window.filterFieldMapping = filterFieldMapping;
    window.technologyAutocompleteByField = technologyAutocompleteByField;
    window.allSoftwareTags = allSoftwareTags;

    technologyAutocompleteLoaded = true;
    return true;
}

function getCurrentBearerToken() {
    return window.cohertaBearerToken || bearerToken || document.body?.getAttribute('session_id') || '';
}

function getTechnologyAutocompleteList(filterKey) {
    if (!filterKey) return [];

    if (!technologyAutocompleteLoaded && Array.isArray(window.filterFieldMapping) && window.filterFieldMapping.length) {
        setTechnologyAutocompleteMapping(window.filterFieldMapping);
    }

    if (filterKey === 'website_software_systems') {
        return Array.isArray(allSoftwareTags) ? allSoftwareTags : [];
    }

    const values = technologyAutocompleteByField?.[filterKey] || window.technologyAutocompleteByField?.[filterKey];
    return Array.isArray(values) ? values : [];
}

function escapeAttribute(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

function closeTechnologyAutocomplete(input) {
    if (!input) return;

    const wrapper = input.closest('.technology-autocomplete');
    const dropdown = wrapper?.querySelector('.technology-autocomplete-menu');

    if (dropdown) {
        dropdown.hidden = true;
        dropdown.innerHTML = '';
    }

    wrapper?.classList.remove('is-open');
    input.setAttribute('aria-expanded', 'false');
}

function closeAllTechnologyAutocomplete(exceptInput = null) {
    document.querySelectorAll('.technology-autocomplete input[data-role="value"], .technology-autocomplete input[data-role="modal-value"]').forEach(input => {
        if (input !== exceptInput) closeTechnologyAutocomplete(input);
    });
}

function applyTechnologySuggestion(input, value) {
    if (!input) return;

    input.value = value;
    input.dispatchEvent(new Event('input', { bubbles: true }));
    input.dispatchEvent(new Event('change', { bubbles: true }));
    closeTechnologyAutocomplete(input);
}

function renderTechnologyAutocompleteSuggestions(input, suggestions) {
    if (!input) return;

    const wrapper = input.closest('.technology-autocomplete');
    const dropdown = wrapper?.querySelector('.technology-autocomplete-menu');

    if (!wrapper || !dropdown) return;

    if (!Array.isArray(suggestions) || !suggestions.length) {
        closeTechnologyAutocomplete(input);
        return;
    }

    dropdown.innerHTML = suggestions.map((item, index) => `
        <button
            class="technology-autocomplete-option"
            type="button"
            role="option"
            data-autocomplete-value="${escapeAttribute(item)}"
            ${index === 0 ? 'data-active="true"' : ''}
        >${escapeHtml(item)}</button>
    `).join('');

    dropdown.hidden = false;
    wrapper.classList.add('is-open');
    input.setAttribute('aria-expanded', 'true');

    dropdown.querySelectorAll('.technology-autocomplete-option').forEach(option => {
        option.addEventListener('mousedown', event => {
            event.preventDefault();
            applyTechnologySuggestion(input, option.getAttribute('data-autocomplete-value') || option.textContent || '');
        });
    });
}

function moveTechnologyAutocompleteSelection(input, direction = 1) {
    const wrapper = input.closest('.technology-autocomplete');
    const options = Array.from(wrapper?.querySelectorAll('.technology-autocomplete-option') || []);

    if (!options.length) return;

    let currentIndex = options.findIndex(option => option.getAttribute('data-active') === 'true');
    if (currentIndex === -1) currentIndex = 0;

    const nextIndex = (currentIndex + direction + options.length) % options.length;

    options.forEach((option, index) => {
        if (index === nextIndex) {
            option.setAttribute('data-active', 'true');
            option.scrollIntoView({ block: 'nearest' });
        } else {
            option.removeAttribute('data-active');
        }
    });
}

function handleTechnologyAutocompleteInput(input, filterKey) {
    if (!input || !filterKey) return;

    const allOptions = getTechnologyAutocompleteList(filterKey);

    if (!allOptions.length) {
        loadTechnologyAutocompleteData();
        closeTechnologyAutocomplete(input);
        return;
    }

    const query = String(input.value || '').trim().toLowerCase();
    const startsWithMatches = [];
    const containsMatches = [];

    allOptions.forEach(item => {
        const normalized = String(item || '').toLowerCase();

        if (!query || normalized.startsWith(query)) {
            startsWithMatches.push(item);
        } else if (normalized.includes(query)) {
            containsMatches.push(item);
        }
    });

    renderTechnologyAutocompleteSuggestions(input, startsWithMatches.concat(containsMatches).slice(0, 12));
}

function attachTechnologyAutocomplete(row, filter, valueRole = 'value') {
    if (!row || !filter || filter.categoryKey !== 'technologies') return;

    const input = row.querySelector(`input[data-role="${valueRole}"]`);
    if (!input) return;

    if (!getTechnologyAutocompleteList(filter.key).length) {
        loadTechnologyAutocompleteData();
        return;
    }

    if (input.dataset.autocompleteBound === '1' && input.dataset.autocompleteFilterKey === filter.key) return;

    if (input.dataset.autocompleteBound !== '1') {
        const existingParent = input.parentNode;
        const wrapper = document.createElement('div');
        wrapper.className = 'technology-autocomplete';

        existingParent.insertBefore(wrapper, input);
        wrapper.appendChild(input);

        const dropdown = document.createElement('div');
        dropdown.className = 'technology-autocomplete-menu';
        dropdown.hidden = true;
        dropdown.setAttribute('role', 'listbox');
        wrapper.appendChild(dropdown);

        input.addEventListener('focus', () => {
            closeAllTechnologyAutocomplete(input);
            handleTechnologyAutocompleteInput(input, input.dataset.autocompleteFilterKey);
        });

        input.addEventListener('input', () => {
            closeAllTechnologyAutocomplete(input);
            handleTechnologyAutocompleteInput(input, input.dataset.autocompleteFilterKey);
        });

        input.addEventListener('keydown', event => {
            const dropdown = input.closest('.technology-autocomplete')?.querySelector('.technology-autocomplete-menu');
            const hasOpenDropdown = dropdown && !dropdown.hidden && dropdown.children.length > 0;

            if (event.key === 'ArrowDown' && hasOpenDropdown) {
                event.preventDefault();
                moveTechnologyAutocompleteSelection(input, 1);
                return;
            }

            if (event.key === 'ArrowUp' && hasOpenDropdown) {
                event.preventDefault();
                moveTechnologyAutocompleteSelection(input, -1);
                return;
            }

            if (event.key === 'Enter' && hasOpenDropdown) {
                const activeOption = dropdown.querySelector('.technology-autocomplete-option[data-active="true"]') || dropdown.querySelector('.technology-autocomplete-option');
                if (activeOption) {
                    event.preventDefault();
                    applyTechnologySuggestion(input, activeOption.getAttribute('data-autocomplete-value') || activeOption.textContent || '');
                }
                return;
            }

            if (event.key === 'Escape') {
                closeTechnologyAutocomplete(input);
            }
        });

        input.addEventListener('blur', () => {
            window.setTimeout(() => closeTechnologyAutocomplete(input), 150);
        });

        input.setAttribute('autocomplete', 'off');
        input.setAttribute('aria-autocomplete', 'list');
        input.setAttribute('aria-expanded', 'false');
    }

    input.dataset.autocompleteBound = '1';
    input.dataset.autocompleteFilterKey = filter.key;
}

function bindVisibleTechnologyAutocompletes() {
    document.querySelectorAll('.filter-rule-row[data-filter-id]').forEach(row => {
        const filterId = row.getAttribute('data-filter-id');
        const filter = activeFilters.find(item => item.id === filterId);
        if (filter) attachTechnologyAutocomplete(row, filter, 'value');
    });

    document.querySelectorAll('.applied-rule-card[data-filter-id]').forEach(card => {
        const filterId = card.getAttribute('data-filter-id');
        const filter = activeFilters.find(item => item.id === filterId);
        if (filter) attachTechnologyAutocomplete(card, filter, 'modal-value');
    });
}

function loadTechnologyAutocompleteData() {
    if (Array.isArray(filterFieldMapping) && filterFieldMapping.length) {
        setTechnologyAutocompleteMapping(filterFieldMapping);
        bindVisibleTechnologyAutocompletes();
    }

    if (technologyAutocompleteRequestStarted) return;

    technologyAutocompleteRequestStarted = true;
    const token = getCurrentBearerToken();

    if (!token) return;

    fetch('https://api.coherta.com/filter/field/mapping', {
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token,
            'Accept': 'application/json'
        }
    })
        .then(response => response.ok ? response.json() : Promise.reject(response))
        .then(mapping => {
            if (setTechnologyAutocompleteMapping(mapping)) {
                bindVisibleTechnologyAutocompletes();

                if (currentCategoryKey === 'technologies') {
                    renderActiveRules();
                }
            }
        })
        .catch(error => {
            console.warn('Technology autocomplete mapping could not be loaded', error);
        });
}

let currentCategoryKey = null;
const activeFilters = [];
let nextFilterInstanceId = 1;

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}


/* --- Technology autocomplete dropdown --- */
(function initTechnologyAutocompleteSupport() {
    if (window.__technologyAutocompleteSupportInitialized) return;
    window.__technologyAutocompleteSupportInitialized = true;

    const style = document.createElement('style');
    style.textContent = `
        .technology-autocomplete-dropdown {
            position: fixed;
            z-index: 2147483647;
            max-height: 260px;
            overflow-y: auto;
            background: #fff;
            border: 1px solid rgba(99, 102, 241, 0.22);
            border-radius: 12px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.18);
            padding: 6px;
            display: none;
            box-sizing: border-box;
        }
        .technology-autocomplete-option {
            display: block;
            width: 100%;
            border: 0;
            background: transparent;
            text-align: left;
            padding: 9px 10px;
            border-radius: 9px;
            cursor: pointer;
            font: inherit;
            color: inherit;
        }
        .technology-autocomplete-option:hover,
        .technology-autocomplete-option.active {
            background: rgba(99, 102, 241, 0.10);
        }
    `;
    document.head.appendChild(style);

    const dropdown = document.createElement('div');
    dropdown.className = 'technology-autocomplete-dropdown';
    dropdown.setAttribute('data-technology-autocomplete-dropdown', 'true');
    document.body.appendChild(dropdown);

    let activeInput = null;
    let activeOptions = [];
    let activeIndex = -1;

    function normalizeAutocompleteList(list) {
        const seen = new Set();
        return (Array.isArray(list) ? list : [])
            .map(item => {
                if (typeof item === 'string') return item;
                return item?.value || item?.label || '';
            })
            .map(value => String(value || '').trim())
            .filter(Boolean)
            .filter(value => {
                const key = value.toLowerCase();
                if (seen.has(key)) return false;
                seen.add(key);
                return true;
            })
            .sort((a, b) => a.localeCompare(b));
    }

    function getSuggestionsForTechnologyFilter(filter) {
        if (!filter || filter.categoryKey !== 'technologies') return [];

        if (filter.key === 'website_software_systems') {
            return normalizeAutocompleteList(window.allSoftwareTags || []);
        }

        return normalizeAutocompleteList((window.technologyAutocompleteByField || {})[filter.key] || []);
    }

    function positionDropdown(input) {
        if (!input) return;
        const rect = input.getBoundingClientRect();
        const viewportHeight = window.innerHeight || document.documentElement.clientHeight;
        const wantedHeight = Math.min(260, Math.max(80, dropdown.scrollHeight || 260));
        const spaceBelow = viewportHeight - rect.bottom - 8;
        const spaceAbove = rect.top - 8;
        const openAbove = spaceBelow < 160 && spaceAbove > spaceBelow;

        dropdown.style.left = `${rect.left}px`;
        dropdown.style.width = `${rect.width}px`;

        if (openAbove) {
            dropdown.style.top = `${Math.max(8, rect.top - wantedHeight - 8)}px`;
            dropdown.style.maxHeight = `${Math.max(90, Math.min(260, spaceAbove - 8))}px`;
        } else {
            dropdown.style.top = `${rect.bottom + 6}px`;
            dropdown.style.maxHeight = `${Math.max(90, Math.min(260, spaceBelow))}px`;
        }
    }

    function hideDropdown() {
        dropdown.style.display = 'none';
        dropdown.innerHTML = '';
        activeInput = null;
        activeOptions = [];
        activeIndex = -1;
    }

    function renderDropdown(input, list) {
        const query = String(input.value || '').trim().toLowerCase();

        if (query.length < 2) {
            hideDropdown();
            return;
        }

        const matches = normalizeAutocompleteList(list)
            .filter(item => item.toLowerCase().includes(query))
            .slice(0, 40);

        activeOptions = matches;
        activeIndex = matches.length ? 0 : -1;

        if (!matches.length) {
            hideDropdown();
            return;
        }

        dropdown.innerHTML = matches.map((item, index) => `
            <button type="button" class="technology-autocomplete-option ${index === activeIndex ? 'active' : ''}" data-index="${index}">
                ${escapeHtml(item)}
            </button>
        `).join('');

        activeInput = input;
        positionDropdown(input);
        dropdown.style.display = 'block';
    }

    dropdown.addEventListener('mousedown', event => {
        event.preventDefault();
        const option = event.target.closest('.technology-autocomplete-option');
        if (!option || !activeInput) return;

        const index = Number(option.getAttribute('data-index'));
        const value = activeOptions[index];
        if (!value) return;

        activeInput.value = value;
        activeInput.dispatchEvent(new Event('input', { bubbles: true }));
        activeInput.dispatchEvent(new Event('change', { bubbles: true }));
        hideDropdown();
    });

    window.attachTechnologyAutocomplete = function attachTechnologyAutocomplete(row, filter) {
        if (!row || !filter || filter.categoryKey !== 'technologies') return;

        const input = row.querySelector('input[data-role="value"], input[data-role="modal-value"]');
        if (!input || input.dataset.technologyAutocompleteBound === '1') return;

        input.dataset.technologyAutocompleteBound = '1';

        const list = getSuggestionsForTechnologyFilter(filter);
        if (!list.length) return;

        input.setAttribute('autocomplete', 'off');

        const show = () => renderDropdown(input, list);

        input.addEventListener('focus', show);
        input.addEventListener('input', show);
        input.addEventListener('click', show);

        input.addEventListener('keydown', event => {
            if (dropdown.style.display !== 'block' || activeInput !== input) return;

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                activeIndex = Math.min(activeOptions.length - 1, activeIndex + 1);
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                activeIndex = Math.max(0, activeIndex - 1);
            } else if (event.key === 'Enter') {
                if (activeIndex >= 0 && activeOptions[activeIndex]) {
                    event.preventDefault();
                    input.value = activeOptions[activeIndex];
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                    hideDropdown();
                }
                return;
            } else if (event.key === 'Escape') {
                hideDropdown();
                return;
            } else {
                return;
            }

            dropdown.querySelectorAll('.technology-autocomplete-option').forEach((option, index) => {
                option.classList.toggle('active', index === activeIndex);
            });
        });
    };

    document.addEventListener('mousedown', event => {
        if (event.target.closest('[data-technology-autocomplete-dropdown="true"]')) return;
        if (activeInput && event.target === activeInput) return;
        hideDropdown();
    });

    window.addEventListener('scroll', () => {
        if (activeInput && dropdown.style.display === 'block') {
            positionDropdown(activeInput);
        }
    }, true);

    window.addEventListener('resize', () => {
        if (activeInput && dropdown.style.display === 'block') {
            positionDropdown(activeInput);
        }
    });
})();



function getCategory(categoryKey) {
    return filterCategories.find(category => category.key === categoryKey) || null;
}

function getFilterExplainer(filterKey) {
    return filterExplainers[filterKey] || 'Use this filter to narrow the company list.';
}

function getFilterId(categoryKey, filterKey) {
    return `${categoryKey}__${filterKey}`;
}

function getFoundingDateOptions(selectedValue = '') {
    const options = ['Yesterday', 'Two weeks ago', '1 month ago', '3 months ago', '6 months ago', '12 months ago'];
    return options.map(option =>
        `<option value="${escapeHtml(option)}" ${selectedValue === option ? 'selected' : ''}>${escapeHtml(option)}</option>`
    ).join('');
}

function getPercentageOptions(selectedValue = '1%') {
    const normalizedValue = normalizePercentageValue(selectedValue);
    return Array.from({ length: 100 }, (_, index) => `${index + 1}%`).map(option =>
        `<option value="${escapeHtml(option)}" ${normalizedValue === option ? 'selected' : ''}>${escapeHtml(option)}</option>`
    ).join('');
}

function isPercentageOperator(operator) {
    return [
        'increase monthly',
        'decline monthly',
        'increase quarterly',
        'decrease quarterly',
        'increase yearly',
        'decrease yearly'
    ].includes(operator);
}

function normalizePercentageValue(value) {
    const match = String(value || '').match(/(\d{1,3})/);
    const parsed = match ? Number(match[1]) : 1;
    const bounded = Math.min(100, Math.max(1, Number.isFinite(parsed) ? parsed : 1));
    return `${bounded}%`;
}
function normalizeJobtitle(value) {
    return String(value ?? '').trim();
}

function dedupeJobtitles(values) {
    const seen = new Set();
    return (Array.isArray(values) ? values : [])
        .map(normalizeJobtitle)
        .filter(Boolean)
        .filter(title => {
            const key = title.toLowerCase();
            if (seen.has(key)) return false;
            seen.add(key);
            return true;
        });
}

function renderJobtitles() {
    if (!jobtitleList) return;

    jobtitleList.innerHTML = '';

    jobtitles.forEach(title => {
        const tag = document.createElement('div');
        tag.className = 'title-tag';
        tag.setAttribute('data-jobtitle', title);
        tag.innerHTML = `${escapeHtml(title)} <button type="button" aria-label="Remove ${escapeHtml(title)}">×</button>`;
        jobtitleList.appendChild(tag);
    });
}

function setJobtitles(values) {
    jobtitles = dedupeJobtitles(values);
    renderJobtitles();
}

function ensureFilterValueMatchesOperator(filter) {
    if (!filter) return;

    //if (filter.key === 'start_date') {
    //    const allowed = ['Yesterday', 'Two weeks ago', '1 month ago', '3 months ago', '6 months ago', '12 months ago'];
    //    if (!allowed.includes(filter.value)) {
    //        filter.value = 'Yesterday';
    //    }
    //    return;
    //}

    if (isPercentageOperator(filter.operator)) {
        filter.value = normalizePercentageValue(filter.value);
    }
}

function renderFilterValueControl(filter) {
    ensureFilterValueMatchesOperator(filter);

    //if (filter.key === 'start_date') {
    //    return `
    //      <select class="select" data-role="value">
    //        ${getFoundingDateOptions(filter.value)}
    //      </select>
    //    `;
    //}

    if (isPercentageOperator(filter.operator)) {
        return `
          <select class="select" data-role="value">
            ${getPercentageOptions(filter.value)}
          </select>
        `;
    }

    return `<input class="input" type="text" data-role="value" placeholder="Input value of filter" value="${escapeHtml(filter.value)}" />`;
}

function updateSummaryCounts() {
    if (summarySelectedFilterCount) {
        summarySelectedFilterCount.textContent = String(activeFilters.length);
    }
}

function updateWebsiteOnlyVisibility() {
    const onlyTechnologies = activeFilters.length > 0 && activeFilters.every(
        f => f.categoryKey === 'technologies' || f.categoryKey === 'website-information' || f.categoryKey === 'presentation-readiness'
    );
    websiteInfoInline.hidden = !onlyTechnologies;
    console.log(onlyTechnologies);
    console.log(websiteInfoCheckbox);
    if (onlyTechnologies) {
        websiteInfoCheckbox.checked = true;
        websiteInfo = true;
    } else {
        websiteInfoCheckbox.checked = false;
        websiteInfo = false;
    }
}

function getCategoryAndLabelByFilterKey(filterKey) {
    for (const category of filterCategories) {
        const definition = category.filters.find(item => item.key === filterKey);
        if (definition) {
            return {
                categoryKey: category.key,
                label: definition.label
            };
        }
    }

    return {
        categoryKey: null,
        label: filterKey
    };
}

function hydratePipelineFilters(filters) {
    if (!Array.isArray(filters) || !filters.length) return;

    activeFilters.length = 0;

    filters.forEach((filter, index) => {
        const mapped = getCategoryAndLabelByFilterKey(filter.key);

        activeFilters.push({
            id: `pipeline__${filter.key}__${nextFilterInstanceId++}`,
            categoryKey: mapped.categoryKey,
            key: filter.key,
            label: mapped.label,
            operator: (filter.origin || 'contains').toLowerCase(),
            value: filter.value ?? '',
            joiner: index === 0 ? 'and' : ((filters[index - 1]?.andor || 'and').toLowerCase())
        });
    });

    updateSummaryCounts();

    if (activeFilters[0]?.categoryKey) {
        openCategory(activeFilters[0].categoryKey);
    } else {
        renderActiveRules();
    }

    renderActiveCompanies();
}

function renderActiveRules() {
    if (!activeFilterRules) return;

    const visibleFilters = activeFilters.filter(filter => filter.categoryKey === currentCategoryKey);

    if (!visibleFilters.length) {
        activeFilterRules.innerHTML = '<div class="filter-rule-empty">Choose one or more filters to configure them instantly.</div>';
        updateWebsiteOnlyVisibility();
        return;
    }

    activeFilterRules.innerHTML = visibleFilters.map((filter, index) => `
        ${index > 0 ? `
          <div class="filter-connector-row" data-filter-id="${escapeHtml(filter.id)}">
            <select class="select filter-connector" data-role="joiner" aria-label="Join filters with AND or OR">
              <option value="and" ${filter.joiner === 'and' ? 'selected' : ''}>AND</option>
              <option value="or" ${filter.joiner === 'or' ? 'selected' : ''}>OR</option>
            </select>
          </div>
        ` : ''}
        <div class="filter-rule-row" data-filter-id="${escapeHtml(filter.id)}">
          <div class="filter-rule-name">
            <strong>${escapeHtml(filter.label)}</strong>
            <small>${escapeHtml(getFilterExplainer(filter.key))}</small>
          </div>
          <select class="select" data-role="operator">
            ${getOperatorOptions(filter.operator || 'contains')}
          </select>
          ${renderFilterValueControl(filter)}
          <button class="filter-rule-duplicate" type="button">Duplicate</button>
          <button class="filter-rule-remove" type="button" aria-label="Remove filter">×</button>
        </div>
      `).join('');

    activeFilterRules.querySelectorAll('.filter-connector-row').forEach(row => {
        const filterId = row.getAttribute('data-filter-id');
        const filter = activeFilters.find(item => item.id === filterId);
        if (!filter) return;

        row.querySelector('[data-role="joiner"]')?.addEventListener('change', event => {
            filter.joiner = event.currentTarget.value;
        });
    });

    activeFilterRules.querySelectorAll('.filter-rule-row').forEach(row => {
        const filterId = row.getAttribute('data-filter-id');
        const filter = activeFilters.find(item => item.id === filterId);
        if (!filter) return;

        const operatorControl = row.querySelector('[data-role="operator"]');
        const valueControl = row.querySelector('[data-role="value"]');
        const duplicateButton = row.querySelector('.filter-rule-duplicate');
        const removeButton = row.querySelector('.filter-rule-remove');

        operatorControl?.addEventListener('change', event => {
            filter.operator = event.currentTarget.value;
            ensureFilterValueMatchesOperator(filter);
            renderActiveRules();
            renderActiveCompanies();
        });

        const syncValue = event => {
            filter.value = event.currentTarget.value;
            renderActiveCompanies();
        };

        valueControl?.addEventListener('input', syncValue);
        valueControl?.addEventListener('change', syncValue);

        duplicateButton?.addEventListener('click', event => {
            event.stopPropagation();
            duplicateFilterRule(filterId);
        });

        removeButton?.addEventListener('click', event => {
            event.stopPropagation();
            removeFilterRule(filterId);
            renderActiveCompanies();
        });

        window.attachTechnologyAutocomplete?.(row, filter);

        attachTechnologyAutocomplete(row, filter, 'value');
    });

    updateWebsiteOnlyVisibility();

    if (activeFiltersModal && !activeFiltersModal.hidden) {
        renderActiveFiltersModal();
    }
}

function setCompanyCountCalculating() {
    const countEl = document.getElementById('SubjctsCompletingFilters');
    const metricBox = countEl?.closest('.metric-box');

    if (!countEl) return;

    countEl.textContent = 'Calculating';
    countEl.setAttribute('aria-busy', 'true');
    metricBox?.classList.add('is-calculating');
    metricBox?.classList.remove('has-error');
}

function setCompanyCountResult(value, isError = false) {
    const countEl = document.getElementById('SubjctsCompletingFilters');
    const metricBox = countEl?.closest('.metric-box');

    if (!countEl) return;

    countEl.textContent = value;
    countEl.setAttribute('aria-busy', 'false');
    metricBox?.classList.remove('is-calculating');
    metricBox?.classList.toggle('has-error', Boolean(isError));
}

function renderActiveCompanies() {
    clearTimeout(renderActiveCompaniesTimer);

    renderActiveCompaniesTimer = setTimeout(() => {
        const currentJson = getLeadlistFiltersPayload(false);
        const currentJsonString = JSON.stringify(currentJson);

        if (currentJsonString === activeFiltersJsonString) {
            return;
        }

        activeFiltersJsonString = currentJsonString;
        const requestId = ++currentFilterRequestId;

        setCompanyCountCalculating();

        if (currentFilterRequest && currentFilterRequest.readyState !== 4) {
            currentFilterRequest.abort();
        }
        currentFilterRequest = $.ajax({
            url: 'https://api.coherta.com/filter/company_new',
            method: 'POST',
            contentType: 'application/json',
            data: currentJsonString,
            headers: {
                'Authorization': 'Bearer ' + bearerToken
            },
            success(response) {
                if (requestId !== currentFilterRequestId) return;
                setCompanyCountResult(response.count ?? 0);
            },
            error(xhr, status, err) {
                if (requestId !== currentFilterRequestId || status === 'abort') return;
                setCompanyCountResult('Error applying filters', true);
            }
        });
    }, 1000);
}

function removeFilterRule(filterId) {
    const index = activeFilters.findIndex(filter => filter.id === filterId);
    if (index === -1) return;
    activeFilters.splice(index, 1);
    renderActiveRules();
    updateSummaryCounts();
    updateWebsiteOnlyVisibility();

    if (activeFiltersModal && !activeFiltersModal.hidden) {
        if (activeFilters.length) {
            renderActiveFiltersModal();
        } else {
            closeActiveFiltersModal();
        }
    }
}

function duplicateFilterRule(filterId) {
    const index = activeFilters.findIndex(filter => filter.id === filterId);
    if (index === -1) return;

    const sourceFilter = activeFilters[index];
    const duplicateId = `${getFilterId(sourceFilter.categoryKey, sourceFilter.key)}__${nextFilterInstanceId++}`;

    activeFilters.splice(index + 1, 0, {
        id: duplicateId,
        categoryKey: sourceFilter.categoryKey,
        key: sourceFilter.key,
        label: sourceFilter.label,
        operator: sourceFilter.operator || 'contains',
        value: sourceFilter.value ?? '',
        joiner: sourceFilter.joiner || 'and'
    });

    renderActiveRules();
    updateSummaryCounts();
    renderActiveCompanies();
    activeFilterRules?.querySelector(`[data-filter-id="${CSS.escape(duplicateId)}"] [data-role="value"]`)?.focus();
}

function addFilterRule(categoryKey, filterKey) {

    const category = getCategory(categoryKey);
    const filterDefinition = category?.filters.find(filter => filter.key === filterKey);
    if (!category || !filterDefinition) return;

    const baseId = getFilterId(categoryKey, filterKey);
    const instanceId = `${baseId}__${nextFilterInstanceId++}`;

    activeFilters.push({
        id: instanceId,
        categoryKey,
        key: filterDefinition.key,
        label: filterDefinition.label,
        operator: 'contains',
        //value: filterDefinition.key === 'start_date' ? 'Yesterday' : '',
        value: '',
        joiner: 'and'
    });

    renderActiveRules();
    updateSummaryCounts();
    activeFilterRules.querySelector(`[data-filter-id="${CSS.escape(instanceId)}"] [data-role="value"]`)?.focus();
}
function getLeadlistFiltersPayload(SaveLeadList = false) {
    let payload = {
        websiteInfo: websiteInfoCheckbox.checked,
        title: leadListTitleInput?.value.trim() || 'Standard List',
        priority: $("#leadPriority").val(),
        jobtitles: jobtitles,
        prefetch_companies: false,
        remove_non_matching_unhandled_leads: removeNonMatchingUnhandledLeads
    };
    if (!SaveLeadList) {
        payload.filters = activeFilters.map((filter, index) => ({
            key: filter.key,
            origin: filter.operator,
            value: filter.value,
            andor: activeFilters[index + 1]?.joiner || 'and'
        }))
    } else {
        payload.pipeline_filter = {};
        payload.pipeline_filter.filters = activeFilters.map((filter, index) => ({
            key: filter.key,
            origin: filter.operator,
            value: filter.value,
            andor: activeFilters[index + 1]?.joiner || 'and'
        }))
    }
    const pipelineId = $('#pipeline_id').val();
    if (pipelineId) {
        payload.pipeline_id = pipelineId;
    }
    return payload;
}
function logLeadlistFilters(SaveLeadList = false) {
    return getLeadlistFiltersPayload(SaveLeadList);
}

function renderCategoryOverview() {
    if (!categoryOverview) return;

    categoryOverview.innerHTML = filterCategories.map(category => `
        <button class="category-nav-card" type="button" data-category="${escapeHtml(category.key)}">
          <div class="category-nav-top">
            <span class="meta-pill">${category.filters.length} fields</span>
          </div>
          <div>
            <strong>${escapeHtml(category.title)}</strong>
            <span>${escapeHtml(category.description)}</span>
          </div>
        </button>
      `).join('');

    categoryOverview.style.display = 'grid';
    categoryOverview.querySelectorAll('[data-category]').forEach(button => {
        button.addEventListener('click', () => openCategory(button.getAttribute('data-category')));
    });
}

function openCategory(categoryKey) {
    const category = getCategory(categoryKey);
    if (!category || !categoryDetail || !categoryActions) return;

    currentCategoryKey = categoryKey;
    categoryTitle.textContent = category.title;
    categoryDescription.textContent = category.description;
    categoryCount.textContent = `${category.filters.length} fields`;

    categoryActions.innerHTML = category.filters.map(filter =>
        `<button class="chip-action" type="button" data-filter-key="${escapeHtml(filter.key)}">
          <span class="chip-action-copy">
            <span class="chip-action-title">${escapeHtml(filter.label)}</span>
            <span class="chip-action-note">${escapeHtml(getFilterExplainer(filter.key))}</span>
          </span>
        </button>`
    ).join('');

    categoryActions.querySelectorAll('[data-filter-key]').forEach(button => {
        button.addEventListener('click', () => addFilterRule(categoryKey, button.getAttribute('data-filter-key')));
    });

    if (!category.filters.length) {
        categoryActions.innerHTML = '<div class="filter-rule-empty">No filters available in this category yet.</div>';
    }

    renderActiveRules();
    updateWebsiteOnlyVisibility();
    categoryOverview.style.display = 'none';
    categoryDetail.classList.add('active');
}

function showCategoryOverview() {
    currentCategoryKey = null;
    categoryDetail?.classList.remove('active');
    if (categoryOverview) {
        categoryOverview.style.display = 'grid';
    }
    updateWebsiteOnlyVisibility();
}

function addJobTitle(value = jobTitleInput?.value ?? '') {
    const title = normalizeJobtitle(jobTitleInput.value);
    if (!title) return;

    if (jobtitles.some(item => item.toLowerCase() === title.toLowerCase())) {
        if (jobTitleInput) jobTitleInput.value = '';
        return;
    }
    jobtitles.push(title);
    renderJobtitles();

    if (jobTitleInput) {
        jobTitleInput.value = '';
    }
}


function getOperatorOptions(selectedValue = 'contains') {

    const options = [
        { key: 'contains', value: 'Contains' },
        { key: 'more_than', value: 'More Than' },
        { key: 'less_than', value: 'Less Than' },
        { key: 'between', value: 'Between' },
        { key: 'equals', value: 'Exact' },
        { key: 'not_contains', value: 'Do not contain' },
        { key: 'is_not_empty', value: 'Is not empty' },
        //{ key: 'increase_monthly', value: 'Increase Monthly' },
        //{ key: 'decline_monthly', value: 'Decline Monthly' },
        //{ key: 'increase_quarterly', value: 'Increase Quarterly' },
        //{ key: 'decrease_quarterly', value: 'Decrease Quarterly' },
        //{ key: 'increase_yearly', value: 'Increase Yearly' },
        //{ key: 'decrease_yearly', value: 'Decrease Yearly' }
    ];
    return options.map(option =>
        `<option value="${escapeHtml(option.key)}" ${selectedValue === option.key ? 'selected' : ''}>${escapeHtml(option.value)}</option>`
    ).join('');
}

function getModalValueControl(filter) {
    ensureFilterValueMatchesOperator(filter);

    //if (filter.key === 'start_date') {
    //    return `
    //      <select class="select" data-role="modal-value">
    //        ${getFoundingDateOptions(filter.value)}
    //      </select>
    //    `;
    //}

    if (isPercentageOperator(filter.operator)) {
        return `
          <select class="select" data-role="modal-value">
            ${getPercentageOptions(filter.value)}
          </select>
        `;
    }

    return `<input class="input" type="text" data-role="modal-value" placeholder="Input value of filter" value="${escapeHtml(filter.value)}" />`;
}

function renderActiveFiltersModal() {
    if (!activeFiltersModalList) return;

    if (!activeFilters.length) {
        activeFiltersModalList.innerHTML = '<div class="applied-rule-empty">No active filter rules have been added yet.</div>';
        return;
    }

    activeFiltersModalList.innerHTML = activeFilters.map((filter, index) => `
        ${index > 0 ? `
          <div class="applied-rule-joiner" data-filter-id="${escapeHtml(filter.id)}">
            <select class="select joiner-select" data-role="modal-joiner" aria-label="Join filters with AND or OR">
              <option value="and" ${filter.joiner === 'and' ? 'selected' : ''}>AND</option>
              <option value="or" ${filter.joiner === 'or' ? 'selected' : ''}>OR</option>
            </select>
          </div>
        ` : ''}
        <div class="applied-rule-card" data-filter-id="${escapeHtml(filter.id)}">
          <div class="applied-rule-top">
            <div>
              <strong>${escapeHtml(filter.label)}</strong>
              <span>${escapeHtml(getFilterExplainer(filter.key))}</span>
            </div>
            <span class="meta-pill">${escapeHtml(getCategory(filter.categoryKey)?.title || 'Filter')}</span>
          </div>
          <div class="applied-rule-grid">
            <div class="applied-rule-field">
              <label>Condition</label>
              <select class="select" data-role="modal-operator">
                ${getOperatorOptions(filter.operator || 'contains')}
              </select>
            </div>
            <div class="applied-rule-field">
              <label>Value</label>
              ${getModalValueControl(filter)}
            </div>
          </div>
        </div>
      `).join('');

    activeFiltersModalList.querySelectorAll('.applied-rule-card').forEach(card => {
        const filterId = card.getAttribute('data-filter-id');
        const filter = activeFilters.find(item => item.id === filterId);
        if (!filter) return;

        const operatorControl = card.querySelector('[data-role="modal-operator"]');
        const valueControl = card.querySelector('[data-role="modal-value"]');

        operatorControl?.addEventListener('change', event => {
            filter.operator = event.currentTarget.value;
            ensureFilterValueMatchesOperator(filter);
            renderActiveRules();
            renderActiveFiltersModal();
            renderActiveCompanies();
        });

        const syncValue = event => {
            filter.value = event.currentTarget.value;
        };

        valueControl?.addEventListener('input', syncValue);
        valueControl?.addEventListener('change', event => {
            syncValue(event);
            renderActiveRules();
            renderActiveCompanies();
        });

        window.attachTechnologyAutocomplete?.(card, filter);

        attachTechnologyAutocomplete(card, filter, 'modal-value');
    });

    activeFiltersModalList.querySelectorAll('.applied-rule-joiner').forEach(row => {
        const filterId = row.getAttribute('data-filter-id');
        const filter = activeFilters.find(item => item.id === filterId);
        if (!filter) return;

        row.querySelector('[data-role="modal-joiner"]')?.addEventListener('change', event => {
            filter.joiner = event.currentTarget.value;
            renderActiveRules();
        });
    });
}

function updateSummaryCountry(country) {
    if (summaryCountryValue) {
        summaryCountryValue.textContent = country;
    }

    if (originSelect && originSelect.text !== country) {
        const existingOption = Array.from(originSelect.options).find(option => option.value === country || option.text === country);
        if (!existingOption) {
            const option = document.createElement('option');
            option.value = country;
            option.textContent = country;
            originSelect.appendChild(option);
        }
        originSelect.text = country;
    }

    countryOptionGrid?.querySelectorAll('[data-country]').forEach(button => {
        button.classList.toggle('active', button.getAttribute('data-country') === country);
    });
}

function openCountryModal() {
    if (!countryModal) return;
    updateSummaryCountry(summaryCountryValue?.textContent?.trim() || 'Denmark');
    countryModal.hidden = false;
    document.body.style.overflow = 'hidden';
}

function closeCountryModal() {
    if (!countryModal) return;
    countryModal.hidden = true;
    if ((!leadListModal || leadListModal.hidden) && (!activeFiltersModal || activeFiltersModal.hidden)) {
        document.body.style.overflow = '';
    }
}

function openActiveFiltersModal() {
    if (!activeFiltersModal || !activeFilters.length) return;
    renderActiveFiltersModal();
    activeFiltersModal.hidden = false;
    document.body.style.overflow = 'hidden';
}

function closeActiveFiltersModal() {
    if (!activeFiltersModal) return;
    activeFiltersModal.hidden = true;
    renderActiveRules();
    if (!leadListModal || leadListModal.hidden) {
        document.body.style.overflow = '';
    }
}


function openLeadListModal() {
    if (!leadListModal) return;
    leadListModal.hidden = false;
    document.body.style.overflow = 'hidden';
    leadListTitleInput?.focus();
}
// --- Lead List Animation Overlay ---
function showLeadListCreatingAnimation() {
    const modalCard = document.querySelector('#leadListModal .modal-card');
    if (!modalCard) return;

    if (getComputedStyle(modalCard).position === 'static') {
        modalCard.style.position = 'relative';
    }

    let overlay = modalCard.querySelector('#leadlist-animation-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'leadlist-animation-overlay';
        modalCard.appendChild(overlay);
    }

    overlay.innerHTML = `
        <div class="leadlist-animation-content">
            <div class="leadlist-animation-spinner"></div>
            <div class="leadlist-animation-text">Creating lead list</div>
        </div>
    `;

    overlay.className = 'show';
}


function showLeadListSuccessAnimation() {
    const modal = document.getElementById('leadListModal');
    if (!modal) return;

    const overlay = modal.querySelector('#leadlist-animation-overlay');
    if (!overlay) return;

    overlay.innerHTML = `
        <div class="leadlist-animation-content">
            <div class="leadlist-animation-checkmark">
                <svg viewBox="0 0 80 80" width="80" height="80">
                    <circle cx="40" cy="40" r="38" fill="none" stroke="#4caf50" stroke-width="4"/>
                    <polyline points="22,44 36,58 58,28" fill="none" stroke="#4caf50" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="leadlist-animation-text">Redirecting</div>
        </div>
    `;

    overlay.className = 'show success';
}


function hideLeadListAnimation() {
    const overlay = document.getElementById('leadlist-animation-overlay');
    if (overlay) overlay.className = '';
}

// --- Animation Styles ---

const technologyAutocompleteStyle = document.createElement('style');
technologyAutocompleteStyle.textContent = `
.active-filter-rules,
.filter-rule-row,
.applied-rule-card,
.applied-rule-grid,
.applied-rule-field {
    overflow: visible !important;
}
.technology-autocomplete {
    position: relative;
    min-width: 0;
    z-index: 20;
}
.technology-autocomplete .input {
    width: 100%;
}
.technology-autocomplete-menu {
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    right: 0;
    z-index: 999999;
    background: #fff;
    border: 1px solid var(--line-soft, #e5e7eb);
    border-radius: 14px;
    box-shadow: 0 14px 34px rgba(15, 23, 42, .12);
    padding: 6px;
    max-height: 260px;
    overflow-y: auto;
}
.technology-autocomplete-option {
    width: 100%;
    border: 0;
    background: transparent;
    text-align: left;
    border-radius: 10px;
    padding: 10px 12px;
    color: var(--text, #111827);
    cursor: pointer;
}
.technology-autocomplete-option:hover,
.technology-autocomplete-option[data-active="true"] {
    background: rgba(94,52,182,.08);
}
.metric-box.is-calculating {
    animation: company-count-pulse 1.15s ease-in-out infinite;
}
.metric-box.is-calculating strong {
    color: var(--primary, #5e34b6);
}
.metric-box.has-error strong {
    color: #b91c1c;
}
@keyframes company-count-pulse {
    0%, 100% {
        opacity: 1;
        transform: translateY(0);
        box-shadow: 0 12px 26px rgba(15, 23, 42, 0.08);
    }
    50% {
        opacity: .68;
        transform: translateY(-1px);
        box-shadow: 0 18px 34px rgba(94, 52, 182, 0.18);
    }
}

`;
document.head.appendChild(technologyAutocompleteStyle);

const leadListAnimationStyle = document.createElement('style');
leadListAnimationStyle.textContent = `
#leadlist-animation-overlay {
    position: fixed;
    z-index: 99999;
    inset: 0;
    background: rgba(255,255,255);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s;
}
#leadlist-animation-overlay.show {
    opacity: 1;
    pointer-events: all;
}
.leadlist-animation-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 32px;
    font-size: 1.5rem;
}
.leadlist-animation-spinner {
    width: 64px;
    height: 64px;
    border: 8px solid #e0e0e0;
    border-top: 8px solid #1976d2;
    border-radius: 50%;
    animation: leadlist-spin 1s linear infinite;
}
@keyframes leadlist-spin {
    0% { transform: rotate(0deg);}
    100% { transform: rotate(360deg);}
}
.leadlist-animation-checkmark svg {
    display: block;
    margin: 0 auto;
    animation: leadlist-checkmark-pop 0.5s cubic-bezier(.68,-0.55,.27,1.55);
}
@keyframes leadlist-checkmark-pop {
    0% { transform: scale(0.2); opacity: 0; }
    60% { transform: scale(1.2); opacity: 1; }
    100% { transform: scale(1); }
}
.leadlist-animation-text {
    text-align: center;
    color: #222;
    font-weight: 500;
}
`;
document.head.appendChild(leadListAnimationStyle);

function createLeadListModal() {
    showLeadListCreatingAnimation();
    const payload = logLeadlistFilters(true);
    const method = $('#pipeline_id').val() ? 'PATCH' : 'POST';

    $.ajax({
        url: 'https://api.coherta.com/pipeline',
        method: method,
        contentType: 'application/json',
        data: JSON.stringify(payload),
        headers: {
            'Authorization': 'Bearer ' + bearerToken
        },
        success(response) {
            const pipelineId = response.id;
            showLeadListSuccessAnimation();
            setTimeout(() => {
                window.location.href = `/app/leads.php?pipeline_id=${pipelineId}`;
            }, 2500);
        },
        error(xhr, status, err) {
            hideLeadListAnimation();
            console.error('Error applying filters:', status, err);
        }
    });
}
function closeLeadListModal() {
    if (!leadListModal) return;
    leadListModal.hidden = true;
    if (!activeFiltersModal || activeFiltersModal.hidden) {
        document.body.style.overflow = '';
    }
}

function attachTemplateCardListeners() {
    templateCards.forEach(card => {
        card.addEventListener('click', () => {
            templateCards.forEach(item => item.classList.remove('active'));
            card.classList.add('active');
            const json = card.dataset.json;
            if (!json) return;

            try {
                const parsed = JSON.parse(json);
                const countryMap = {
                    'dk': 'Denmark',
                    'se': 'Sweden',
                    'no': 'Norway',
                    'uk': 'United Kingdom',
                    'nordics': 'Nordics'
                };
                let filtersArray = [];

                websiteInfo = Number(parsed.website_list) === 1;
                if (websiteInfoCheckbox) websiteInfoCheckbox.checked = websiteInfo;
                if (parsed.country && countryMap[parsed.country]) updateSummaryCountry(countryMap[parsed.country]);

                if (typeof parsed.filters === 'string') {
                    filtersArray = JSON.parse(parsed.filters || '[]');
                } else if (Array.isArray(parsed.filters)) {
                    filtersArray = parsed.filters;
                }

                activeFilters.length = 0;

                (Array.isArray(filtersArray) ? filtersArray : []).forEach((filter, index) => {
                    let categoryKey = null;
                    let label = filter.key;
                    const category = filterCategories.find(item => item.filters.some(def => def.key === filter.key));
                    const definition = category?.filters.find(def => def.key === filter.key);

                    if (category && definition) {
                        categoryKey = category.key;
                        label = definition.label;
                    }

                    activeFilters.push({
                        id: `template__${filter.key}__${nextFilterInstanceId++}`,
                        categoryKey,
                        key: filter.key,
                        label,
                        operator: filter.origin || 'contains',
                        value: filter.value ?? '',
                        joiner: index === 0 ? 'and' : ((filtersArray[index - 1]?.andor || 'and').toLowerCase())
                    });
                });
                updateSummaryCounts();
                if (activeFilters[0]?.categoryKey) {
                    openCategory(activeFilters[0].categoryKey);
                } else {
                    renderActiveRules();
                }
                setCompanyCountResult(parsed.results_found || $(".template-card.active .template-card-count").text());
                showCategoryOverview();
                if(!window.advancedWizardPrefill.pipelineId)
                    $("#title").val(card.dataset.title);
            } catch (e) {
                console.error('Invalid JSON in template card:', e);
            }
        });
    });
}

toggleTemplateExpandBtn?.addEventListener('click', () => {
    const isExpanded = templateGridPreview?.classList.toggle('expanded');
    toggleTemplateExpandBtn.textContent = isExpanded ? 'Show fewer templates' : 'Show 3 more templates';
});

categoryBack?.addEventListener('click', showCategoryOverview);
addJobtitleBtn?.addEventListener('click', addJobTitle);

jobTitleInput?.addEventListener('keydown', event => {
    if (event.key === 'Enter') {
        event.preventDefault();
        addJobTitle();
    }
});

jobtitleList?.addEventListener('click', event => {
    const removeButton = event.target.closest('.title-tag button');
    if (!removeButton) return;

    const tag = removeButton.closest('.title-tag');
    const title = tag?.getAttribute('data-jobtitle');
    if (!title) return;

    jobtitles = jobtitles.filter(item => item.toLowerCase() !== title.toLowerCase());
    renderJobtitles();
});


closeActiveFiltersModalBtn?.addEventListener('click', closeActiveFiltersModal);
closeActiveFiltersModalFooterBtn?.addEventListener('click', closeActiveFiltersModal);

activeFiltersModal?.addEventListener('click', event => {
    if (event.target === activeFiltersModal) {
        closeActiveFiltersModal();
    }
});

addLeadlistBtn?.addEventListener('click', () => {
    openLeadListModal();
});
closeLeadListModalBtn?.addEventListener('click', closeLeadListModal);
cancelLeadListModalBtn?.addEventListener('click', closeLeadListModal);
createLeadListModalBtn?.addEventListener('click', createLeadListModal);

leadListModal?.addEventListener('click', event => {
    if (event.target === leadListModal) {
        closeLeadListModal();
    }
});

countrySummaryTrigger?.addEventListener('click', openCountryModal);
countrySummaryTrigger?.addEventListener('keydown', event => {
    if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        openCountryModal();
    }
});
closeCountryModalBtn?.addEventListener('click', closeCountryModal);
cancelCountryModalBtn?.addEventListener('click', closeCountryModal);

countryOptionGrid?.querySelectorAll('[data-country]').forEach(button => {
    button.addEventListener('click', () => {
        updateSummaryCountry(button.getAttribute('data-country') || 'Denmark');
        closeCountryModal();
    });
});

function showSelectedFilters() {
    if (!activeFilters.length) return;
    openActiveFiltersModal();
}


summaryFilters?.addEventListener('click', showSelectedFilters);


originSelect?.addEventListener('change', event => {
    updateSummaryCountry(event.currentTarget.value);
});

countryModal?.addEventListener('click', event => {
    if (event.target === countryModal) {
        closeCountryModal();
    }
});

document.addEventListener('keydown', event => {
    if (event.key !== 'Escape') return;

    if (activeFiltersModal && !activeFiltersModal.hidden) {
        closeActiveFiltersModal();
    }

    if (countryModal && !countryModal.hidden) {
        closeCountryModal();
    }

    if (leadListModal && !leadListModal.hidden) {
        closeLeadListModal();
    }
});

document.getElementById('templateSearch').addEventListener('input', function() {
    const search = this.value.trim();
    const allCards = document.querySelectorAll('.template-card:not(.search-data)');
    if (search.length === 0) {
        allCards.forEach(card => card.style.display = '');
        document.querySelectorAll('.GTMSearch').forEach(el => {
            el.remove();
        });
        return;
    }
    fetch('https://api.coherta.com/gtm/templates?search=' + encodeURIComponent(search))
        .then(res => res.json())
        .then(data => {
            allCards.forEach(card => card.style.display = 'none');
            const card = document.getElementById('searchResultCard');
            if (data && data[0]) {
                document.querySelectorAll('.GTMSearch').forEach(el => {
                    el.remove();
                });
                data.forEach(item => {
                    // Clone the first visible template card in the grid
                    const firstCard = templateGridPreview?.querySelector('.template-card:not(.search-data)');
                    if (!firstCard) return;
                    const cardClone = firstCard.cloneNode(true);

                    cardClone.id = '';
                    cardClone.classList.add('GTMSearch');
                    cardClone.setAttribute('data-template', item.id);
                    cardClone.setAttribute('data-title', item.gtm_title);
                    cardClone.setAttribute('data-website', item.website_list);
                    cardClone.setAttribute('data-json', JSON.stringify(item));
                    cardClone.style.display = '';
                    cardClone.classList.remove('active');
                    cardClone.innerHTML = `
                    <div class="template-title">
                        <strong>${item.gtm_title}</strong>
                        <span>${item.gtm_description}</span>
                    </div>
                    <div class="template-meta">
                        <span class="meta-pill">${(JSON.parse(item.filters || '[]')).length} filters</span>
                    </div>
                    <div class="template-footer">
                        <div class="template-stat">
                            <strong>${item.results_found}</strong>
                            <span>companies</span>
                        </div>
                        <span>Click to fetch and edit</span>
                    </div>
                `;
                    templateGrid.appendChild(cardClone);
                    isExpanded = true;
                });
                templateCards = document.querySelectorAll('.template-card');
                attachTemplateCardListeners();
            }
        })
        .catch(err => {
            document.getElementById('searchResultCard').style.display = 'none';
            allCards.forEach(card => card.style.display = '');
        });
});
function applyAdvancedWizardPrefill() {
    const prefill = window.advancedWizardPrefill || null;
    if (!prefill || !prefill.pipelineId) return;

    if (leadListTitleInput && prefill.listName) {
        leadListTitleInput.value = prefill.listName;
    }

    websiteInfo = Number(prefill.websiteList) === 1;
    if (websiteInfoCheckbox) {
        websiteInfoCheckbox.checked = websiteInfo;
    }

    hydratePipelineFilters(prefill.filters || []);

    if (leadListTitleInput && prefill.listName) {
        leadListTitleInput.value = prefill.listName;
    }

    applyAdvancedWizardJobtitlePrefill(prefill);
}
function applyAdvancedWizardJobtitlePrefill(prefill = {}) {
    const prefetchedJobtitles = Array.isArray(prefill.jobtitles) && prefill.jobtitles.length
        ? prefill.jobtitles
        : defaultJobtitles;
    setJobtitles(prefetchedJobtitles);
}
updateSummaryCountry(summaryCountryValue?.textContent?.trim() || 'Denmark');
renderCategoryOverview();
showCategoryOverview();
updateSummaryCounts();
attachTemplateCardListeners();
loadTechnologyAutocompleteData();
applyAdvancedWizardJobtitlePrefill();
applyAdvancedWizardPrefill();


const activeFilterRulesObserverTarget = document.getElementById('activeFilterRules');
if (activeFilterRulesObserverTarget) {
    const activeFilterRulesObserver = new MutationObserver(() => bindVisibleTechnologyAutocompletes());
    activeFilterRulesObserver.observe(activeFilterRulesObserverTarget, { childList: true, subtree: true });
}

document.addEventListener('focusin', event => {
    const input = event.target?.matches?.('input[data-role="value"], input[data-role="modal-value"]') ? event.target : null;
    if (!input) return;

    const row = input.closest('.filter-rule-row, .applied-rule-card');
    const filterId = row?.getAttribute('data-filter-id');
    const filter = activeFilters.find(item => item.id === filterId);

    if (!filter || filter.categoryKey !== 'technologies') return;

    attachTechnologyAutocomplete(row, filter, input.getAttribute('data-role') === 'modal-value' ? 'modal-value' : 'value');
    handleTechnologyAutocompleteInput(input, filter.key);
});

document.addEventListener('mousedown', event => {
    if (!event.target.closest('.technology-autocomplete')) {
        closeAllTechnologyAutocomplete();
    }
});
