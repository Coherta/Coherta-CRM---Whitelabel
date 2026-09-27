(function () {
  const API_BASE = 'https://api.coherta.com';
  const LEAD_ENRICH_ENDPOINT = API_BASE + '/pipeline/leads/fullenrich';
  const EMAIL_ENRICH_ENDPOINT = API_BASE + '/participants/fullenrich/email';
  const state = {
    stakeholders: [],
    campaigns: [],
    notifications: [],
    changeNotifications: [],
    statusFilter: 'all',
    campaignFilter: new URLSearchParams(window.location.search).get('campaign') || '',
    scope: 'company',
    notificationSeenFilter: false,
    mobileNotificationBoardExpanded: false,
    phoneEnriching: {},
    emailEnriching: {},
    changeHistoryTimer: null,
    credits: null,
    accountCreditLimit: Number(
      window.COHERTA_STAKEHOLDER_CREDITS
      && window.COHERTA_STAKEHOLDER_CREDITS.total
    ) || null
  };

  const el = {
    total: document.getElementById('stakeholderTotal'),
    graphMonitored: document.getElementById('stakeholderGraphMonitored'),
    graphChanges: document.getElementById('stakeholderGraphChanges'),
    credits: document.getElementById('stakeholderCreditDelta'),
    count: document.getElementById('stakeholderCountPill'),
    tableStatus: document.getElementById('stakeholderTableStatus'),
    tableBody: document.getElementById('stakeholderTableBody'),
    campaignFilter: document.getElementById('stakeholderCampaignFilter'),
    notifications: document.getElementById('stakeholderNotifications'),
    notificationBoard: document.querySelector('.sm-notification-board'),
    notificationBadge: document.getElementById('stakeholderNotificationBadge'),
    download: document.getElementById('downloadStakeholdersBtn'),
    enrichMissingPhones: document.getElementById('enrichMissingPhonesBtn'),
    enrichMissingEmails: document.getElementById('enrichMissingEmailsBtn'),
    modal: document.getElementById('stakeholderAddModal'),
    csvFormatModal: document.getElementById('stakeholderCsvFormatModal'),
    toast: document.getElementById('stakeholderToast')
  };
  let activeChangePopover = null;
  let csvImportBusy = false;
  const notificationMobileQuery = window.matchMedia
    ? window.matchMedia('(max-width: 767px)')
    : null;

  function validCreditNumber(value) {
    if (value === null || value === undefined || value === '') return null;
    const number = Number(value);
    return Number.isFinite(number) ? Math.max(0, number) : null;
  }

  function firstCreditNumber(values) {
    for (let index = 0; index < values.length; index += 1) {
      const number = validCreditNumber(values[index]);
      if (number !== null) return number;
    }
    return null;
  }

  function directCreditLimitFromObject(source) {
    if (!source || typeof source !== 'object') return null;

    return firstCreditNumber([
      source.stakeholder_monitoring_credits,
      source.stakeholders_to_monitor,
      source.stakeholder_credits,
      source.stakeholder_credit_limit,
      source.monitoring_credits,
      source.monitoring_credit_limit,
      source.credits && source.credits.total,
      source.credits && source.credits.limit
    ]);
  }

  function creditLimitFromObject(source) {
    if (!source || typeof source !== 'object') return null;

    const directCredit = directCreditLimitFromObject(source);
    if (directCredit !== null) return directCredit;

    const nestedSources = [
      source.data,
      source.account,
      source.company,
      source.current_company,
      source.company_data,
      source.customer,
      source.organization
    ];
    for (let index = 0; index < nestedSources.length; index += 1) {
      const nestedCredit = directCreditLimitFromObject(nestedSources[index]);
      if (nestedCredit !== null) return nestedCredit;
    }

    if (Array.isArray(source.companies)) {
      for (let index = 0; index < source.companies.length; index += 1) {
        const companyCredit = directCreditLimitFromObject(source.companies[index]);
        if (companyCredit !== null) return companyCredit;
      }
    }

    return null;
  }

  function isMobileNotificationViewport() {
    return notificationMobileQuery
      ? notificationMobileQuery.matches
      : (window.innerWidth || document.documentElement.clientWidth || 0) <= 767;
  }

  function setNotificationBoardMinimized(isMinimized) {
    if (!el.notificationBoard) return;
    el.notificationBoard.classList.toggle('is-minimized', isMinimized);
    const toggleButton = el.notificationBoard.querySelector('.sm-notification-fab');
    if (toggleButton) {
      toggleButton.setAttribute('aria-label', isMinimized ? 'Open notification board' : 'Close notification board');
      toggleButton.setAttribute('aria-expanded', String(!isMinimized));
    }
  }

  function enforceMobileNotificationBoardState() {
    if (isMobileNotificationViewport() && !state.mobileNotificationBoardExpanded) {
      setNotificationBoardMinimized(true);
    }
  }

  function token() {
    return document.body.getAttribute('session_id')
      || localStorage.getItem('session_id')
      || sessionStorage.getItem('session_id')
      || '';
  }

  async function api(path, options) {
    const headers = Object.assign({ Accept: 'application/json' }, (options && options.headers) || {});
    const bearer = token();
    if (bearer) headers.Authorization = 'Bearer ' + bearer;
    if (options && options.body && !headers['Content-Type']) headers['Content-Type'] = 'application/json';

    const response = await fetch(API_BASE + path, Object.assign({}, options || {}, { headers: headers }));
    const text = await response.text();
    let data = null;
    try {
      data = text ? JSON.parse(text) : null;
    } catch (error) {
      data = { detail: text || 'Unknown response' };
    }
    if (!response.ok) {
      throw new Error((data && (data.detail || data.message)) || ('Request failed with status ' + response.status));
    }
    return data;
  }

  function escapeHtml(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function initials(name) {
    const parts = String(name || '?').trim().split(/\s+/).filter(Boolean);
    return (parts[0] ? parts[0][0] : '?') + (parts[1] ? parts[1][0] : '');
  }

  function imageHtml(person) {
    if (person.image) {
      return '<img class="sm-avatar" src="' + API_BASE + '/images/' + escapeHtml(person.image) + '" alt="">';
    }
    return '<span class="sm-avatar">' + escapeHtml(initials(person.name || person.stakeholder)) + '</span>';
  }

  function phoneValue(item) {
    return item.phonenumber || item.phonenumber_2 || item.phone || item.phone_number || '';
  }

  function emailValue(item) {
    return item.email || item.email_address || item.work_email || item.mail || '';
  }

  function phoneHref(phone) {
    return 'tel:' + String(phone || '').replace(/[^\d+]/g, '');
  }

  function stakeholderKey(item) {
    return String((item && item.id) || '');
  }

  function normalizeLinkedInUrl(value) {
    return String(value || '')
      .trim()
      .toLowerCase()
      .replace(/^https?:\/\//, '')
      .replace(/^www\./, '')
      .replace(/^[a-z]{2}\.linkedin\.com\//, 'linkedin.com/')
      .replace(/[?#].*$/, '')
      .replace(/\/+$/, '');
  }

  function stakeholderLinkedInKey(item) {
    return normalizeLinkedInUrl(item && (item.linkedin_url || item.linkedin || item.participant_linkedin));
  }

  function setPhoneEnriching(item, isEnriching) {
    const key = typeof item === 'object' ? stakeholderKey(item) : String(item || '');
    if (!key) return;
    if (isEnriching) {
      state.phoneEnriching[key] = true;
    } else {
      delete state.phoneEnriching[key];
    }
  }

  function isPhoneEnriching(item) {
    const key = typeof item === 'object' ? stakeholderKey(item) : String(item || '');
    return Boolean(key && state.phoneEnriching[key]);
  }

  function setEmailEnriching(item, isEnriching) {
    const key = typeof item === 'object' ? stakeholderKey(item) : String(item || '');
    if (!key) return;
    if (isEnriching) {
      state.emailEnriching[key] = true;
    } else {
      delete state.emailEnriching[key];
    }
  }

  function isEmailEnriching(item) {
    const key = typeof item === 'object' ? stakeholderKey(item) : String(item || '');
    return Boolean(key && state.emailEnriching[key]);
  }

  function phoneHtml(item) {
    const phone = phoneValue(item);
    if (phone) {
      return '<a class="sm-contact-action is-found is-phone" href="' + escapeHtml(phoneHref(phone)) + '" data-tooltip="' + escapeHtml(phone) + '" aria-label="Call ' + escapeHtml(phone) + '"><i data-feather="phone"></i></a>';
    }
    if (isPhoneEnriching(item)) {
      return '<button class="sm-contact-action" type="button" disabled data-tooltip="Finding phone number" aria-label="Finding phone number"><i data-feather="phone"></i></button>';
    }
    return '<button class="sm-contact-action" type="button" data-tooltip="Click to find phone number" aria-label="Find phone number" data-enrich-phone="' + escapeHtml(item.id) + '" data-participant-linkedin-id="' + escapeHtml(item.participant_linkedin_id || '') + '" data-name="' + escapeHtml(item.name || item.stakeholder || '') + '" data-title="' + escapeHtml(item.current_title || '') + '" data-company="' + escapeHtml(item.current_company || '') + '" data-linkedin="' + escapeHtml(item.linkedin_url || item.linkedin || '') + '"><i data-feather="phone"></i></button>';
  }

  function emailHtml(item) {
    const email = emailValue(item);
    if (email) {
      return '<a class="sm-contact-action is-found is-email" href="mailto:' + escapeHtml(email) + '" data-tooltip="' + escapeHtml(email) + '" aria-label="Email ' + escapeHtml(email) + '"><i data-feather="mail"></i></a>';
    }
    if (isEmailEnriching(item)) {
      return '<button class="sm-contact-action" type="button" disabled data-tooltip="Finding work email" aria-label="Finding work email"><i data-feather="mail"></i></button>';
    }
    return '<button class="sm-contact-action" type="button" data-tooltip="click to find work email" aria-label="Find work email" data-enrich-email="' + escapeHtml(item.id) + '"><i data-feather="mail"></i></button>';
  }

  function contactDataHtml(item) {
    const editLabel = phoneValue(item) && emailValue(item) ? 'Edit contact details' : 'Add contact details';
    return '<span class="sm-contact-data">' + phoneHtml(item) + emailHtml(item) +
      '<button class="sm-contact-action" type="button" data-edit-stakeholder-contact="' + escapeHtml(item.id) +
      '" data-tooltip="' + editLabel + '" aria-label="' + editLabel + ' for ' + escapeHtml(item.name || item.stakeholder || 'stakeholder') + '"><i data-feather="edit-2"></i></button></span>';
  }

  function isStatusLikeSource(value) {
    const normalized = String(value || '').trim().toLowerCase().replace(/[_-]+/g, ' ');
    return [
      'active',
      'paused',
      'verified',
      'verifying identity',
      'monitoring',
      'verification pending'
    ].indexOf(normalized) !== -1;
  }

  function sourceValue(item) {
    const meta = item && typeof item.metadata === 'object' ? item.metadata : {};
    const candidates = [
      item.database_source,
      item.db_source,
      item.monitoring_source,
      item.stakeholder_source,
      item.import_source,
      item.created_from,
      item.created_source,
      item.lead_source_name,
      item.lead_source,
      item.source_type,
      meta.database_source,
      meta.source,
      meta.source_type,
      item.source
    ];
    for (let index = 0; index < candidates.length; index += 1) {
      const value = String(candidates[index] || '').trim();
      if (value && !isStatusLikeSource(value)) return value;
    }
    return '';
  }

  function sourceLabel(item) {
    const source = String(sourceValue(item) || '').trim();
    if (!source) return '-';
    return source
      .replace(/[_-]+/g, ' ')
      .replace(/\s+/g, ' ')
      .replace(/\b\w/g, function (letter) { return letter.toUpperCase(); });
  }

  function stakeholderCampaigns(item) {
    return Array.isArray(item && item.campaigns) ? item.campaigns.filter(function (campaign) {
      return campaign && campaign.id != null;
    }) : [];
  }

  function campaignOptions() {
    const campaigns = new Map();
    state.campaigns.forEach(function (campaign) {
      if (!campaign || campaign.id == null) return;
      const id = String(campaign.id);
      campaigns.set(id, String(campaign.name || campaign.title || ('Campaign ' + id)));
    });
    state.stakeholders.forEach(function (item) {
      stakeholderCampaigns(item).forEach(function (campaign) {
        const id = String(campaign.id);
        if (!campaigns.has(id)) campaigns.set(id, String(campaign.name || ('Campaign ' + id)));
      });
    });
    return Array.from(campaigns, function (entry) {
      return { id: entry[0], name: entry[1] };
    }).sort(function (left, right) {
      return left.name.localeCompare(right.name, undefined, { sensitivity: 'base' });
    });
  }

  function updateCampaignUrl() {
    const url = new URL(window.location.href);
    if (state.campaignFilter) {
      url.searchParams.set('campaign', state.campaignFilter);
    } else {
      url.searchParams.delete('campaign');
    }
    window.history.replaceState({}, '', url.pathname + url.search + url.hash);
  }

  function renderCampaignFilter() {
    if (!el.campaignFilter) return;
    const options = campaignOptions();
    if (state.campaignFilter && !options.some(function (campaign) { return campaign.id === state.campaignFilter; })) {
      options.push({ id: state.campaignFilter, name: 'Campaign ' + state.campaignFilter });
    }
    el.campaignFilter.innerHTML = '<option value="">All campaigns</option>' + options.map(function (campaign) {
      return '<option value="' + escapeHtml(campaign.id) + '">' + escapeHtml(campaign.name) + '</option>';
    }).join('');
    el.campaignFilter.value = state.campaignFilter;
    updateCampaignUrl();
  }

  function relativeTime(dateValue) {
    if (!dateValue) return 'No changes yet';
    const date = new Date(String(dateValue).replace(' ', 'T'));
    if (Number.isNaN(date.getTime())) return dateValue;
    const diff = Date.now() - date.getTime();
    const minutes = Math.max(1, Math.round(diff / 60000));
    if (minutes < 60) return minutes + ' min ago';
    const hours = Math.round(minutes / 60);
    if (hours < 24) return hours + ' hour' + (hours === 1 ? '' : 's') + ' ago';
    const days = Math.round(hours / 24);
    return days + ' day' + (days === 1 ? '' : 's') + ' ago';
  }

  function changeLabel(item) {
    const type = item.change_type || item.field_name || '';
    if (String(type).includes('company')) return 'Company changed';
    if (String(type).includes('role') || String(type).includes('title') || String(type).includes('job')) return 'Role changed';
    if (item.summary || item.old_value || item.new_value || item.previous_value || item.current_value) return 'Changed';
    return item.changes_count > 0 || item.unseen_count > 0 ? 'Changed' : 'No changes';
  }

  function changeTimestamp(item) {
    return item.detected_at || item.created_at || item.latest_change_at || item.last_change_at || item.updated_at || '';
  }

  function changeTypeText(item) {
    return String(item.change_type || item.field_name || item.field || item.type || '').toLowerCase();
  }

  function changeSummary(item) {
    return item.summary
      || [item.old_value, item.new_value].filter(Boolean).join(' -> ')
      || [item.previous_value, item.current_value].filter(Boolean).join(' -> ')
      || changeLabel(item);
  }

  function normalizeMatchValue(value) {
    return String(value || '').trim().toLowerCase();
  }

  function notificationMatchesStakeholder(notification, stakeholder) {
    const stakeholderId = normalizeMatchValue(stakeholder.id);
    const notificationStakeholderId = normalizeMatchValue(
      notification.stakeholder_monitoring_id
      || notification.monitoring_id
      || notification.stakeholder_id
      || notification.parent_id
    );
    if (stakeholderId && notificationStakeholderId && stakeholderId === notificationStakeholderId) return true;

    const stakeholderLinkedIn = normalizeMatchValue(stakeholder.linkedin_url || stakeholder.linkedin);
    const notificationLinkedIn = normalizeMatchValue(notification.linkedin_url || notification.linkedin);
    if (stakeholderLinkedIn && notificationLinkedIn && stakeholderLinkedIn === notificationLinkedIn) return true;

    const stakeholderParticipantId = normalizeMatchValue(stakeholder.participant_linkedin_id || stakeholder.participant_id);
    const notificationParticipantId = normalizeMatchValue(notification.participant_linkedin_id || notification.participant_id);
    if (stakeholderParticipantId && notificationParticipantId && stakeholderParticipantId === notificationParticipantId) return true;

    const stakeholderName = normalizeMatchValue(stakeholder.name || stakeholder.stakeholder);
    const notificationName = normalizeMatchValue(notification.name || notification.stakeholder);
    return Boolean(stakeholderName && notificationName && stakeholderName === notificationName);
  }

  function recentChanges(item) {
    const directChanges = []
      .concat(Array.isArray(item.recent_changes) ? item.recent_changes : [])
      .concat(Array.isArray(item.latest_changes) ? item.latest_changes : [])
      .concat(Array.isArray(item.changes) ? item.changes : []);
    const allNotifications = state.notifications.concat(state.changeNotifications);
    const notificationChanges = allNotifications.filter(function (notification) {
      return notificationMatchesStakeholder(notification, item);
    });
    const seen = {};
    return directChanges.concat(notificationChanges).filter(function (change) {
      const key = [
        change.id,
        changeTimestamp(change),
        changeTypeText(change),
        changeSummary(change)
      ].join('|');
      if (seen[key]) return false;
      seen[key] = true;
      return true;
    })
      .sort(function (left, right) {
        return new Date(String(changeTimestamp(right)).replace(' ', 'T')).getTime()
          - new Date(String(changeTimestamp(left)).replace(' ', 'T')).getTime();
      })
      .slice(0, 3);
  }

  function latestFieldChange(item, keywords) {
    return recentChanges(item).find(function (change) {
      const type = changeTypeText(change);
      return keywords.some(function (keyword) { return type.includes(keyword); })
        && (change.new_value || change.current_value);
    });
  }

  function displayCompany(item) {
    const change = latestFieldChange(item, ['company']);
    return String((change && (change.new_value || change.current_value)) || item.current_company || '').trim();
  }

  function displayTitle(item) {
    const change = latestFieldChange(item, ['role', 'title', 'job']);
    return String((change && (change.new_value || change.current_value)) || item.current_title || '').trim();
  }

  function changePopoverHtml(item) {
    return '<span class="sm-change-hover"><span class="sm-pill sm-pill-change" data-change-hover="' + escapeHtml(item.id) + '">' + escapeHtml(changeLabel(item)) + '</span></span>';
  }

  function changePopoverContent(item) {
    const changes = recentChanges(item);
    const rows = changes.length ? changes.map(function (change) {
      return '<li><strong>' + escapeHtml(changeLabel(change)) + '</strong><span>' + escapeHtml(changeSummary(change)) + '</span><small>' + escapeHtml(relativeTime(changeTimestamp(change))) + '</small></li>';
    }).join('') : '<li><span>No detailed changes loaded yet.</span></li>';
    return '<h3>Latest changes</h3><ul>' + rows + '</ul>';
  }

  function removeChangePopover() {
    if (activeChangePopover) {
      activeChangePopover.remove();
      activeChangePopover = null;
    }
  }

  function showChangePopover(trigger) {
    const id = trigger.getAttribute('data-change-hover');
    const item = state.stakeholders.find(function (stakeholder) { return String(stakeholder.id) === String(id); });
    if (!item) return;
    removeChangePopover();
    const popover = document.createElement('div');
    popover.className = 'sm-change-popover';
    popover.setAttribute('role', 'dialog');
    popover.setAttribute('aria-label', 'Latest changes');
    popover.innerHTML = changePopoverContent(item);
    document.body.appendChild(popover);
    popover.classList.add('open');
    popover.style.visibility = 'hidden';

    const rect = trigger.getBoundingClientRect();
    const viewportWidth = document.documentElement.clientWidth || window.innerWidth;
    const viewportHeight = document.documentElement.clientHeight || window.innerHeight;
    const width = popover.offsetWidth;
    const height = popover.offsetHeight;
    const left = Math.min(Math.max(12, rect.right - width), viewportWidth - width - 12);
    const top = Math.min(Math.max(12, rect.bottom + 10), viewportHeight - height - 12);
    const arrowLeft = Math.min(Math.max(16, rect.left + (rect.width / 2) - left - 6), width - 28);
    popover.style.left = left + 'px';
    popover.style.top = top + 'px';
    popover.style.setProperty('--sm-change-arrow-left', arrowLeft + 'px');
    popover.style.visibility = '';
    activeChangePopover = popover;
  }

  function filteredStakeholders() {
    return state.stakeholders.filter(function (item) {
      if (state.campaignFilter && !stakeholderCampaigns(item).some(function (campaign) {
        return String(campaign.id) === state.campaignFilter;
      })) return false;
      if (state.statusFilter === 'all') return true;
      if (state.statusFilter === 'changes') return Number(item.unseen_count || item.changes_count || 0) > 0;
      return String(item.status || '').toLowerCase() === state.statusFilter;
    });
  }

  function isActivelyMonitored(item) {
    return String((item && item.status) || '').toLowerCase() !== 'paused';
  }

  function calculateAvailableCredits(monitoredCount) {
    const accountTotal = Number(state.accountCreditLimit || 0);
    const apiTotal = state.credits && Number(state.credits.total);
    const creditTotal = accountTotal || apiTotal || 0;
    const apiRemaining = state.credits && Number(state.credits.remaining);

    if (creditTotal) {
      return Math.max(0, creditTotal - monitoredCount);
    }

    return Number.isFinite(apiRemaining) ? apiRemaining : 0;
  }

  function isCreditLimitError(error) {
    const message = String((error && error.message) || '').toLowerCase();
    return message.includes('credit') || message.includes('limit') || message.includes('quota');
  }

  function uploadErrorMessage(error) {
    const apiTotal = state.credits && Number(state.credits.total);
    if (isCreditLimitError(error) && state.accountCreditLimit && apiTotal && state.accountCreditLimit > apiTotal) {
      return 'The API rejected this because its credit limit is still ' + apiTotal + '. Admin shows ' + state.accountCreditLimit + ', so POST /stakeholder/monitoring must use the database credit limit.';
    }
    return error.message;
  }

  function renderStakeholders() {
    const list = filteredStakeholders();
    const shown = list;
    const monitoredCount = state.stakeholders.filter(isActivelyMonitored).length;
    const changesCount = state.stakeholders.reduce(function (total, item) {
      return total + Number(item.changes_count || 0);
    }, 0);
    el.total.textContent = String(monitoredCount);
    el.graphMonitored.textContent = String(monitoredCount);
    el.graphChanges.textContent = String(changesCount);
    if (el.credits) {
      el.credits.textContent = String(calculateAvailableCredits(monitoredCount));
    }
    el.count.textContent = String(list.length);
    const selectedCampaign = campaignOptions().find(function (campaign) {
      return campaign.id === state.campaignFilter;
    });
    el.tableStatus.textContent = list.length
      ? 'Showing ' + list.length + (selectedCampaign ? ' from ' + selectedCampaign.name : ' stakeholders')
      : 'No stakeholders found';

    if (!shown.length) {
      el.tableBody.innerHTML = '<tr><td colspan="9"><div class="sm-empty">No stakeholders match this filter.</div></td></tr>';
      return;
    }

    el.tableBody.innerHTML = shown.map(function (item) {
      const hasChanges = Number(item.unseen_count || item.changes_count || 0) > 0;
      const rawTitle = displayTitle(item);
      const rawCompany = displayCompany(item);
      const isUnknownPerson = !String(item.name || item.stakeholder || '').trim() || !rawCompany || !rawTitle;
      const title = rawTitle || '-';
      const company = rawCompany || '-';
      const statusPill = isUnknownPerson
        ? '<span class="sm-pill sm-pill-verifying">Verifying identity</span>'
        : item.status === 'paused'
        ? '<span class="sm-pill sm-pill-paused">Paused</span>'
        : '<span class="sm-pill sm-pill-success">Verified</span> <span class="sm-pill sm-pill-monitoring">Monitoring</span>';
      const changePill = hasChanges ? '<strong>' + changePopoverHtml(item) + '</strong>' : '<strong>-</strong>';
      const linkedin = item.linkedin_url || item.linkedin || '';

      return '<tr>' +
        '<td><div class="sm-person">' + imageHtml(item) + '<span>' + escapeHtml(item.name || item.stakeholder || 'Unknown') + '</span></div></td>' +
        '<td><span class="sm-company"><i data-feather="briefcase"></i>' + escapeHtml(company) + '</span></td>' +
        '<td>' + escapeHtml(title) + '</td>' +
        '<td>' + contactDataHtml(item) + '</td>' +
        '<td><span class="sm-linkedin">' + (linkedin ? '<a href="' + escapeHtml(linkedin) + '" target="_blank" rel="noopener">in</a>' : '-') + '</span></td>' +
        '<td><span class="sm-source">' + escapeHtml(sourceLabel(item)) + '</span></td>' +
        '<td>' + statusPill + '</td>' +
        '<td><div class="sm-last-change">' + changePill + '<span>' + escapeHtml(relativeTime(item.latest_change_at || item.last_change_at || item.updated_at)) + '</span></div></td>' +
        '<td class="sm-row-actions"><button class="sm-menu-btn" type="button" data-row-menu="' + item.id + '">...</button><div class="sm-row-menu" id="smRowMenu' + item.id + '">' +
        '<button type="button" data-toggle-status="' + item.id + '">' + (item.status === 'paused' ? 'Activate' : 'Pause') + '</button>' +
        '<button type="button" data-delete-stakeholder="' + item.id + '">Remove</button>' +
        '</div></td>' +
        '</tr>';
    }).join('');

    if (window.feather) window.feather.replace();
  }

  function renderNotifications() {
    const unseenCount = state.notifications.filter(function (item) { return !item.seen; }).length;
    el.notificationBadge.textContent = String(unseenCount);
    el.notificationBadge.setAttribute('data-count', String(unseenCount));
    if (isMobileNotificationViewport()) {
      enforceMobileNotificationBoardState();
    } else if (!state.notificationSeenFilter) {
      setNotificationBoardMinimized(unseenCount === 0);
    }

    if (!state.notifications.length) {
      el.notifications.innerHTML = '<div class="sm-empty">No notifications need attention right now.</div>';
      return;
    }

    el.notifications.innerHTML = state.notifications.map(function (item) {
      return '<article class="sm-notification">' +
        imageHtml(item) +
        '<div><h3>' + escapeHtml(item.name || 'Unknown stakeholder') + '</h3>' +
        '<p>' + escapeHtml(item.summary || [item.old_value, item.new_value].filter(Boolean).join(' -> ') || changeLabel(item)) + '</p>' +
        '<div class="sm-notification-meta"><span class="sm-pill sm-pill-change">' + escapeHtml(changeLabel(item)) + '</span><span>' + escapeHtml(relativeTime(item.detected_at || item.created_at)) + '</span></div>' +
        '<div class="sm-notification-meta"><span>' + escapeHtml(item.owner_name || '') + '</span>' +
        (item.seen ? '<span class="sm-pill sm-pill-success">Seen</span>' : '<button class="sm-mark-seen" type="button" data-mark-seen="' + item.id + '">Mark seen</button>') +
        '</div></div></article>';
    }).join('');
  }

  function toast(message) {
    el.toast.textContent = message;
    el.toast.classList.add('show');
    clearTimeout(toast.timer);
    toast.timer = setTimeout(function () { el.toast.classList.remove('show'); }, 2600);
  }

  async function loadStakeholders() {
    el.tableStatus.textContent = 'Loading...';
    const data = await api('/stakeholder/monitoring?scope=' + encodeURIComponent(state.scope));
    state.stakeholders = data.stakeholders || [];
    const accountCreditLimit = creditLimitFromObject(data);
    if (accountCreditLimit !== null && (!state.accountCreditLimit || accountCreditLimit > state.accountCreditLimit)) {
      state.accountCreditLimit = accountCreditLimit;
    }
    if (data.credits) {
      state.credits = data.credits;
    }
    renderCampaignFilter();
    renderStakeholders();
  }

  async function loadCampaigns() {
    try {
      const data = await api('/campaigns');
      state.campaigns = Array.isArray(data)
        ? data
        : (data && Array.isArray(data.campaigns) ? data.campaigns : []);
      renderCampaignFilter();
      renderStakeholders();
    } catch (error) {
      state.campaigns = [];
    }
  }
  async function loadNotifications() {
    const data = await api('/stakeholder/monitoring/notifications?scope=' + encodeURIComponent(state.scope) + '&seen=' + String(state.notificationSeenFilter) + '&limit=30');
    state.notifications = data.notifications || [];
    renderNotifications();
    if (state.stakeholders.length) renderStakeholders();
  }

  async function loadChangeHistory() {
    try {
      const basePath = '/stakeholder/monitoring/notifications?scope=' + encodeURIComponent(state.scope) + '&limit=100&seen=';
      const results = await Promise.all([
        api(basePath + 'false'),
        api(basePath + 'true')
      ]);
      const seen = {};
      state.changeNotifications = results.reduce(function (all, data) {
        return all.concat((data && data.notifications) || []);
      }, []).filter(function (notification) {
        const key = [
          notification.id,
          changeTimestamp(notification),
          changeTypeText(notification),
          changeSummary(notification)
        ].join('|');
        if (seen[key]) return false;
        seen[key] = true;
        return true;
      });
      if (state.stakeholders.length) renderStakeholders();
    } catch (error) {
      state.changeNotifications = [];
    }
  }

  function scheduleChangeHistoryLoad() {
    clearTimeout(state.changeHistoryTimer);
    state.changeHistoryTimer = setTimeout(loadChangeHistory, 2000);
  }

  async function refreshAll() {
    try {
      await Promise.all([loadStakeholders(), loadNotifications(), loadCampaigns()]);
      scheduleChangeHistoryLoad();
    } catch (error) {
      el.tableStatus.textContent = error.message;
      el.notifications.innerHTML = '<div class="sm-empty">' + escapeHtml(error.message) + '</div>';
    }
  }

  function openModal() {
    el.modal.classList.add('open');
    el.modal.setAttribute('aria-hidden', 'false');
    document.getElementById('stakeholderLinkedInInput').focus();
  }

  function closeModal() {
    el.modal.classList.remove('open');
    el.modal.setAttribute('aria-hidden', 'true');
  }

  function openCsvFormatModal() {
    if (!el.csvFormatModal) return;
    el.csvFormatModal.classList.add('open');
    el.csvFormatModal.setAttribute('aria-hidden', 'false');
  }

  function closeCsvFormatModal() {
    if (!el.csvFormatModal) return;
    el.csvFormatModal.classList.remove('open');
    el.csvFormatModal.setAttribute('aria-hidden', 'true');
  }

  async function addStakeholder(payload) {
    const body = Object.assign({}, payload);
    if (state.accountCreditLimit) {
      body.stakeholder_monitoring_credits = state.accountCreditLimit;
      body.stakeholder_credits = state.accountCreditLimit;
      body.stakeholder_credit_limit = state.accountCreditLimit;
      body.stakeholders_to_monitor = state.accountCreditLimit;
      body.credits = {
        total: state.accountCreditLimit,
        remaining: calculateAvailableCredits(state.stakeholders.filter(isActivelyMonitored).length)
      };
    }
    await api('/stakeholder/monitoring', {
      method: 'POST',
      body: JSON.stringify(body)
    });
  }

  async function postLeadEnrichment(payload) {
    const response = await fetch(LEAD_ENRICH_ENDPOINT, {
      method: 'POST',
      headers: {
        Authorization: 'Bearer ' + token(),
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

  function emailEnrichmentPayload(item) {
    const values = emailEnrichmentValues(item || {});
    if (!values.participantId || !values.companyId) return null;
    const payload = {
      participant_id: values.participantId,
      company_id: values.companyId
    };
    if (values.companyUid) payload.company_uid = values.companyUid;
    return payload;
  }

  async function postEmailEnrichment(payload) {
    const response = await fetch(EMAIL_ENRICH_ENDPOINT, {
      method: 'POST',
      headers: {
        Authorization: 'Bearer ' + token(),
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
      throw new Error((data && (data.detail || data.message)) || responseText || 'Email enrichment failed');
    }
    return data;
  }

  function extractPhone(data) {
    const source = data || {};
    const enrichedLead = Array.isArray(source) ? (source[0] || {}) : (typeof source === 'string' ? { phone: source } : (source.lead || source.data || source || {}));
    const keyEmployee = enrichedLead.key_employee && enrichedLead.key_employee.employee ? enrichedLead.key_employee.employee : {};
    return (typeof source === 'string' ? source : '')
      || source.phone
      || source.phonenumber
      || source.phone_number
      || enrichedLead.phone
      || enrichedLead.phonenumber
      || enrichedLead.phonenumber_2
      || keyEmployee.phone
      || '';
  }

  function extractEmail(data) {
    const source = data || {};
    const enriched = Array.isArray(source) ? (source[0] || {}) : (typeof source === 'string' ? { email: source } : (source.participant || source.lead || source.data || source || {}));
    return (typeof source === 'string' ? source : '')
      || source.email
      || source.email_address
      || source.work_email
      || enriched.email
      || enriched.email_address
      || enriched.work_email
      || '';
  }

  function firstFilled(values) {
    for (let index = 0; index < values.length; index += 1) {
      const value = values[index];
      if (value !== undefined && value !== null && String(value).trim() !== '') return value;
    }
    return '';
  }

  function emailEnrichmentValues(item) {
    const company = item.company && typeof item.company === 'object' ? item.company : {};
    const participant = item.participant && typeof item.participant === 'object' ? item.participant : {};
    const person = item.person && typeof item.person === 'object' ? item.person : {};
    const contact = item.contact && typeof item.contact === 'object' ? item.contact : {};
    return {
      participantId: firstFilled([
        item.participant_id,
        item.participant_linkedin_id,
        item.participants_id,
        item.person_id,
        item.company_participant_id,
        participant.id,
        participant.participant_id,
        participant.participant_linkedin_id,
        person.participant_id,
        person.participant_linkedin_id,
        person.id,
        contact.participant_id,
        contact.participant_linkedin_id
      ]),
      companyId: firstFilled([
        item.company_id,
        item.current_company_id,
        item.company_vat,
        item.current_company_vat,
        item.vat_code,
        item.vat,
        item.vat_number,
        item.cvr,
        item.cvr_number,
        item.company_cvr,
        item.current_company_cvr,
        item.registration_number,
        item.company_registration_number,
        company.company_id,
        company.id,
        company.vat_code,
        company.vat,
        company.cvr,
        company.registration_number
      ]),
      companyUid: firstFilled([
        item.company_uid,
        item.current_company_uid,
        item.uid,
        company.company_uid,
        company.uid
      ])
    };
  }

  function emailEnrichmentMissingFields(item) {
    const values = emailEnrichmentValues(item || {});
    return [
      values.participantId ? '' : 'participant_id',
      values.companyId ? '' : 'company_id'
    ].filter(Boolean);
  }

  async function enrichPhoneForStakeholder(item) {
    if (!item || phoneValue(item)) return '';
    if (!item.participant_linkedin_id) {
      throw new Error('Missing participant LinkedIn id for ' + (item.name || item.stakeholder || 'stakeholder'));
    }
    const data = await postLeadEnrichment({
      source: 'linkedin',
      participant_id: item.participant_linkedin_id,
      pipeline_filter: {
        source: 'stakeholder_monitoring',
        enrich_type: 'phone',
        stakeholder_monitoring_id: item.id,
        linkedin_url: item.linkedin_url || item.linkedin || '',
        name: item.name || item.stakeholder || '',
        title: item.current_title || '',
        company_name: item.current_company || '',
        fields: ['key_employee.employee.phone', 'phonenumber', 'phonenumber_2', 'phone']
      }
    });
    const phone = extractPhone(data);
    if (phone) item.phonenumber = phone;
    return phone;
  }

  async function enrichEmailForStakeholder(item) {
    if (!item || emailValue(item)) return '';
    const payload = emailEnrichmentPayload(item);
    if (!payload) {
      throw new Error('Missing ' + emailEnrichmentMissingFields(item).join(' and ') + ' for ' + (item.name || item.stakeholder || 'stakeholder'));
    }
    const data = await postEmailEnrichment(payload);
    const email = extractEmail(data);
    if (email) item.email = email;
    return email;
  }

  function parseCsv(text) {
    // Find profile links anywhere in the file, regardless of CSV structure.
    const pattern = /(?:^|[\s"'(<>=,;:|\[\]{}])(?:https?:\/\/)?(?:(?:www|[a-z]{2})\.)?linkedin\.com\/in\/([\p{L}\p{N}_%\-]+)/giu;
    const rows = [];
    let match;
    while ((match = pattern.exec(String(text))) !== null) {
      rows.push({
        linkedin_url: 'https://www.linkedin.com/in/' + match[1],
        source: 'upload',
        status: 'active'
      });
    }
    if (!rows.length) {
      throw new Error('No LinkedIn profile URLs found in the file');
    }
    return rows;
  }
  function compareCsvRows(rows, stakeholders) {
    const existing = new Set(stakeholders.map(stakeholderLinkedInKey).filter(Boolean));
    const included = new Set();
    const result = { newRows: [], existing: 0, duplicates: 0, missing: [], unidentified: 0 };
    rows.forEach(function (row) {
      const key = stakeholderLinkedInKey(row);
      if (included.has(key)) {
        result.duplicates += 1;
        return;
      }
      included.add(key);
      if (existing.has(key)) result.existing += 1;
      else result.newRows.push(row);
    });
    stakeholders.forEach(function (item) {
      const key = stakeholderLinkedInKey(item);
      if (!key || !stakeholderKey(item)) result.unidentified += 1;
      else if (!included.has(key)) result.missing.push(item);
    });
    return result;
  }

  function csvSnapshot(stakeholders) {
    return JSON.stringify(stakeholders.map(function (item) {
      return [stakeholderKey(item), stakeholderLinkedInKey(item), item.status];
    }).sort(function (a, b) { return JSON.stringify(a).localeCompare(JSON.stringify(b)); }));
  }

  function reviewCsvImport(comparison, fileName, scope) {
    return new Promise(function (resolve) {
      const previousFocus = document.activeElement;
      const backdrop = document.createElement('div');
      backdrop.className = 'sm-modal-backdrop sm-csv-review open';
      backdrop.innerHTML = '<div class="sm-modal" role="dialog" aria-modal="true" aria-labelledby="smCsvReviewTitle">' +
        '<header><div><h2 id="smCsvReviewTitle">Review CSV upload</h2><p>' + escapeHtml(fileName) + '</p></div></header>' +
        '<div class="sm-modal-body"><p>Comparing all stakeholders in ' + (scope === 'company' ? 'Company' : 'Mine') +
        '. Campaign and status filters do not limit this comparison.</p>' +
        '<ul><li>New rows to add: <strong>' + comparison.newRows.length + '</strong></li>' +
        '<li>Already existing (kept unchanged): <strong>' + comparison.existing + '</strong></li>' +
        '<li>Duplicate CSV rows skipped: <strong>' + comparison.duplicates + '</strong></li>' +
        '<li>Existing rows missing from the CSV: <strong>' + comparison.missing.length + '</strong></li></ul>' +
        (comparison.unidentified ? '<p>' + comparison.unidentified + ' records cannot be matched and will be kept.</p>' : '') +
        (comparison.missing.length ? '<details><summary>Review rows missing from the CSV</summary><ul>' +
          comparison.missing.map(function (item) {
            return '<li>' + escapeHtml(item.name || item.stakeholder || stakeholderLinkedInKey(item)) +
              ' — ' + escapeHtml(stakeholderLinkedInKey(item)) + '</li>';
          }).join('') + '</ul></details><label class="sm-csv-remove-option"><input type="checkbox" id="smCsvRemoveMissing">' +
          '<span>Remove existing rows missing from the CSV (' + comparison.missing.length + ') after importing. You will be asked to confirm twice.</span></label>' : '') +
        '</div><footer><button type="button" class="sm-btn sm-btn-light" data-csv-cancel>Cancel</button>' +
        '<button type="button" class="sm-btn sm-btn-primary" data-csv-apply>Apply CSV</button></footer></div>';
      function finish(value) {
        backdrop.remove();
        document.removeEventListener('keydown', onKeydown);
        if (previousFocus && previousFocus.focus) previousFocus.focus();
        resolve(value);
      }
      function onKeydown(event) {
        if (event.key === 'Escape') {
          event.preventDefault();
          finish(null);
        }
        if (event.key === 'Tab') {
          const controls = Array.from(backdrop.querySelectorAll('button, input, summary'));
          const first = controls[0];
          const last = controls[controls.length - 1];
          if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
          else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        }
      }
      backdrop.querySelector('[data-csv-cancel]').addEventListener('click', function () { finish(null); });
      backdrop.querySelector('[data-csv-apply]').addEventListener('click', function () {
        const checkbox = backdrop.querySelector('#smCsvRemoveMissing');
        const remove = Boolean(checkbox && checkbox.checked);
        if (remove && !window.confirm('Confirmation 1 of 2: Remove existing stakeholders missing from this CSV in ' +
          (scope === 'company' ? 'Company' : 'Mine') + '? Records to remove: ' + comparison.missing.length + '.')) return;
        if (remove && !window.confirm('Final confirmation (2 of 2): Permanently remove the selected stakeholder monitoring records (' +
          comparison.missing.length + ')? This cannot be undone.')) return;
        finish({ remove: remove });
      });
      document.body.appendChild(backdrop);
      document.addEventListener('keydown', onKeydown);
      backdrop.querySelector('[data-csv-cancel]').focus();
    });
  }

  async function importCsvFile(file) {
    if (!String(file.name || '').toLowerCase().endsWith('.csv')) throw new Error('Upload a .csv file');
    const parsedRows = parseCsv(await file.text());
    const scope = state.scope;
    const path = '/stakeholder/monitoring?scope=' + encodeURIComponent(scope);
    const data = await api(path);
    if (!data || !Array.isArray(data.stakeholders)) throw new Error('Could not load existing stakeholders. Please try again.');
    const comparison = compareCsvRows(parsedRows, data.stakeholders);
    closeCsvFormatModal();
    const choice = await reviewCsvImport(comparison, file.name, scope);
    if (!choice) return;
    const current = await api(path);
    if (!current || !Array.isArray(current.stakeholders) || state.scope !== scope ||
      csvSnapshot(current.stakeholders) !== csvSnapshot(data.stakeholders)) {
      throw new Error('The stakeholder list changed during review. Upload the CSV again to review the updated counts.');
    }
    state.stakeholders = current.stakeholders;
    if (current.credits) state.credits = current.credits;
    const availableCredits = calculateAvailableCredits(current.stakeholders.filter(isActivelyMonitored).length);
    if (comparison.newRows.length > availableCredits) {
      throw new Error('The CSV contains ' + comparison.newRows.length + ' new stakeholders, but only ' + availableCredits +
        ' credits are available. No rows were changed.');
    }
    let added = 0;
    let removed = 0;
    try {
      for (const row of comparison.newRows) {
        await addStakeholder(row);
        added += 1;
      }
      // Never start removing records unless every new row was imported successfully.
      if (choice.remove) {
        for (const item of comparison.missing) {
          await api('/stakeholder/monitoring/' + encodeURIComponent(item.id), { method: 'DELETE' });
          removed += 1;
        }
      }
      toast(added + ' stakeholders added · ' + comparison.existing + ' already exist · ' + removed + ' removed');
    } catch (error) {
      window.alert('CSV import stopped: ' + added + ' added, ' + removed + ' removed. ' + uploadErrorMessage(error) +
        ' Upload the CSV again to review the remaining changes.');
    } finally {
      await refreshAll();
    }
  }

  function csvCell(value) {
    const text = String(value == null ? '' : value);
    return '"' + text.replace(/"/g, '""') + '"';
  }

  function downloadStakeholders() {
    const list = filteredStakeholders();
    if (!list.length) {
      toast('No stakeholders to download');
      return;
    }
    const headers = ['Name', 'Company', 'Title', 'Phone', 'Email', 'ParticipantID', 'LinkedIn', 'Source', 'Status', 'Changes', 'Unseen changes', 'Last change', 'Note', 'Owner'];
    const rows = list.map(function (item) {
      return [
        item.name || item.stakeholder || '',
        item.current_company || '',
        item.current_title || '',
        phoneValue(item),
        emailValue(item),
        emailEnrichmentValues(item).participantId,
        item.linkedin_url || item.linkedin || '',
        sourceLabel(item),
        item.status || '',
        item.changes_count || 0,
        item.unseen_count || 0,
        item.latest_change_at || item.last_change_at || '',
        item.note || '',
        item.owner_name || ''
      ];
    });
    const csv = [headers].concat(rows).map(function (row) {
      return row.map(csvCell).join(',');
    }).join('\r\n');
    const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    const date = new Date().toISOString().slice(0, 10);
    link.href = url;
    link.download = 'stakeholder-monitoring-' + date + '.csv';
    document.body.appendChild(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(url);
  }

  function downloadCsvTemplate() {
    const rows = [
      ['https://www.linkedin.com/in/example-person']
    ];
    const csv = rows.map(function (row) { return row.map(csvCell).join(','); }).join('\r\n');
    const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = 'stakeholder-monitoring-template.csv';
    document.body.appendChild(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(url);
  }

  document.addEventListener('click', async function (event) {
    const open = event.target.closest('[data-open-add-stakeholder]');
    const close = event.target.closest('[data-close-add-stakeholder]');
    const closeCsvFormat = event.target.closest('[data-close-csv-format]');
    const rowMenu = event.target.closest('[data-row-menu]');
    const markSeen = event.target.closest('[data-mark-seen]');
    const toggleStatus = event.target.closest('[data-toggle-status]');
    const deleteStakeholder = event.target.closest('[data-delete-stakeholder]');
    const statusFilter = event.target.closest('[data-status-filter]');
    const enrichPhone = event.target.closest('[data-enrich-phone]');
    const enrichEmail = event.target.closest('[data-enrich-email]');
    const editContact = event.target.closest('[data-edit-stakeholder-contact]');

    if (editContact) {
      const item = state.stakeholders.find(function (stakeholder) {
        return String(stakeholder.id) === editContact.getAttribute('data-edit-stakeholder-contact');
      });
      if (item) window.LeadContactEditor.open({
        target: { monitoring_id: item.id },
        name: item.name || item.stakeholder,
        phone: item.phonenumber || item.phone_number || item.phone || '',
        email: emailValue(item),
        focus: phoneValue(item) && !emailValue(item) ? 'email' : 'phone',
        onSave: function (result) {
          state.stakeholders.forEach(function (stakeholder) {
            if (stakeholder.id === item.id || (item.participant_linkedin_id && stakeholder.participant_linkedin_id === item.participant_linkedin_id)) {
              stakeholder.phonenumber = result.phone_number;
              stakeholder.phone_number = result.phone_number;
              stakeholder.phone = result.phone_number;
              stakeholder.email = result.email;
              stakeholder.email_address = stakeholder.work_email = stakeholder.mail = '';
            }
          });
          renderStakeholders();
          toast('Contact details saved');
        }
      });
    }

    if (open) openModal();
    if (close) closeModal();
    if (closeCsvFormat) closeCsvFormatModal();

    if (rowMenu) {
      document.querySelectorAll('.sm-row-menu.open').forEach(function (menu) { menu.classList.remove('open'); });
      const menu = document.getElementById('smRowMenu' + rowMenu.getAttribute('data-row-menu'));
      if (menu) menu.classList.toggle('open');
    } else if (!event.target.closest('.sm-row-menu')) {
      document.querySelectorAll('.sm-row-menu.open').forEach(function (menu) { menu.classList.remove('open'); });
    }

    if (statusFilter) {
      state.statusFilter = statusFilter.getAttribute('data-status-filter');
      document.querySelectorAll('[data-status-filter]').forEach(function (button) { button.classList.remove('active'); });
      statusFilter.classList.add('active');
      renderStakeholders();
    }

    if (event.target.closest('[data-scope-toggle]')) {
      state.scope = state.scope === 'company' ? 'own' : 'company';
      document.querySelector('[data-scope-label]').textContent = state.scope === 'company' ? 'Company' : 'Mine';
      await refreshAll();
    }

    if (event.target.closest('[data-toggle-notifications]')) {
      const isOpening = el.notificationBoard.classList.contains('is-minimized');
      if (isMobileNotificationViewport()) {
        state.mobileNotificationBoardExpanded = isOpening;
      }
      setNotificationBoardMinimized(!isOpening);
    }

    if (event.target.closest('[data-notification-seen-filter]')) {
      state.notificationSeenFilter = !state.notificationSeenFilter;
      event.target.closest('button').querySelector('span').textContent = state.notificationSeenFilter ? 'Seen' : 'Unseen';
      await loadNotifications();
    }

    if (markSeen) {
      await api('/stakeholder/monitoring/notifications/' + markSeen.getAttribute('data-mark-seen'), {
        method: 'PATCH',
        body: JSON.stringify({ seen: true })
      });
      toast('Notification marked as seen');
      await Promise.all([loadStakeholders(), loadNotifications(), loadCampaigns()]);
    }

    if (toggleStatus) {
      const id = Number(toggleStatus.getAttribute('data-toggle-status'));
      const current = state.stakeholders.find(function (item) { return Number(item.id) === id; });
      await api('/stakeholder/monitoring/' + id, {
        method: 'PATCH',
        body: JSON.stringify({ status: current && current.status === 'paused' ? 'active' : 'paused' })
      });
      toast('Stakeholder updated');
      await loadStakeholders();
    }

    if (deleteStakeholder) {
      await api('/stakeholder/monitoring/' + deleteStakeholder.getAttribute('data-delete-stakeholder'), { method: 'DELETE' });
      toast('Stakeholder removed');
      await loadStakeholders();
    }

    if (enrichPhone) {
      const id = enrichPhone.getAttribute('data-enrich-phone');
      if (isPhoneEnriching(id)) return;
      const participantLinkedInId = enrichPhone.getAttribute('data-participant-linkedin-id');
      if (!participantLinkedInId) {
        toast('Missing participant LinkedIn id for FullEnrich');
        return;
      }
      const item = state.stakeholders.find(function (stakeholder) {
        return String(stakeholder.id) === String(id);
      }) || {
        id: id,
        participant_linkedin_id: participantLinkedInId,
        linkedin_url: enrichPhone.getAttribute('data-linkedin'),
        name: enrichPhone.getAttribute('data-name'),
        current_title: enrichPhone.getAttribute('data-title'),
        current_company: enrichPhone.getAttribute('data-company')
      };
      setPhoneEnriching(id, true);
      renderStakeholders();
      try {
        const phone = await enrichPhoneForStakeholder(item);
        if (!phone) throw new Error('No phone number found yet.');
        toast('Phone number found');
      } catch (error) {
        toast(error.message);
      } finally {
        setPhoneEnriching(id, false);
        renderStakeholders();
      }
    }

    if (enrichEmail) {
      const id = enrichEmail.getAttribute('data-enrich-email');
      if (isEmailEnriching(id)) return;
      const item = state.stakeholders.find(function (stakeholder) {
        return String(stakeholder.id) === String(id);
      });
      if (!item) return;
      if (!emailEnrichmentPayload(item)) {
        toast('Missing ' + emailEnrichmentMissingFields(item).join(' and ') + ' for email enrichment');
        return;
      }
      setEmailEnriching(id, true);
      renderStakeholders();
      try {
        const email = await enrichEmailForStakeholder(item);
        if (!email) throw new Error('No work email found yet.');
        toast('Work email found');
        await loadStakeholders();
      } catch (error) {
        toast(error.message);
      } finally {
        setEmailEnriching(id, false);
        renderStakeholders();
      }
    }
  });

  document.addEventListener('mouseover', function (event) {
    const trigger = event.target.closest('[data-change-hover]');
    if (trigger) showChangePopover(trigger);
  });

  document.addEventListener('mouseout', function (event) {
    if (event.target.closest('[data-change-hover]')) removeChangePopover();
  });

  window.addEventListener('scroll', removeChangePopover, true);
  window.addEventListener('resize', removeChangePopover);
  window.addEventListener('resize', function () {
    if (!isMobileNotificationViewport()) {
      state.mobileNotificationBoardExpanded = false;
      return;
    }
    enforceMobileNotificationBoardState();
  });
  if (notificationMobileQuery) {
    const handleNotificationViewportChange = function (event) {
      if (!event.matches) {
        state.mobileNotificationBoardExpanded = false;
        return;
      }
      enforceMobileNotificationBoardState();
    };
    if (notificationMobileQuery.addEventListener) {
      notificationMobileQuery.addEventListener('change', handleNotificationViewportChange);
    } else if (notificationMobileQuery.addListener) {
      notificationMobileQuery.addListener(handleNotificationViewportChange);
    }
  }

  el.enrichMissingPhones.addEventListener('click', async function () {
    const candidates = filteredStakeholders().filter(function (item) {
      return !phoneValue(item) && item.participant_linkedin_id;
    });
    if (!candidates.length) {
      toast('No missing phone numbers in this view');
      return;
    }
    const button = el.enrichMissingPhones;
    const originalText = button.querySelector('span').textContent;
    button.disabled = true;
    let found = 0;
    let failed = 0;
    try {
      for (let index = 0; index < candidates.length; index += 1) {
        if (isPhoneEnriching(candidates[index])) continue;
        button.querySelector('span').textContent = 'Finding ' + (index + 1) + '/' + candidates.length;
        setPhoneEnriching(candidates[index], true);
        renderStakeholders();
        try {
          const phone = await enrichPhoneForStakeholder(candidates[index]);
          if (phone) found += 1;
          else failed += 1;
        } catch (error) {
          failed += 1;
        } finally {
          setPhoneEnriching(candidates[index], false);
          renderStakeholders();
        }
      }
      toast('Found ' + found + ' phone numbers' + (failed ? ' · ' + failed + ' not found' : ''));
    } finally {
      button.querySelector('span').textContent = originalText;
      button.disabled = false;
    }
  });

  el.enrichMissingEmails.addEventListener('click', async function () {
    const candidates = filteredStakeholders().filter(function (item) {
      return !emailValue(item) && emailEnrichmentPayload(item);
    });
    if (!candidates.length) {
      toast('No missing e-mails with participant_id and company_id in this view');
      return;
    }
    const button = el.enrichMissingEmails;
    const originalText = button.querySelector('span').textContent;
    button.disabled = true;
    let found = 0;
    let failed = 0;
    try {
      for (let index = 0; index < candidates.length; index += 1) {
        if (isEmailEnriching(candidates[index])) continue;
        button.querySelector('span').textContent = 'Finding ' + (index + 1) + '/' + candidates.length;
        setEmailEnriching(candidates[index], true);
        try {
          const email = await enrichEmailForStakeholder(candidates[index]);
          if (email) found += 1;
          else failed += 1;
        } catch (error) {
          failed += 1;
        } finally {
          setEmailEnriching(candidates[index], false);
        }
      }
      toast('Found ' + found + ' e-mails' + (failed ? ' · ' + failed + ' not found' : ''));
      await loadStakeholders();
    } finally {
      button.querySelector('span').textContent = originalText;
      button.disabled = false;
    }
  });

  document.getElementById('saveStakeholderBtn').addEventListener('click', async function () {
    const payload = {
      linkedin_url: document.getElementById('stakeholderLinkedInInput').value.trim(),
      note: document.getElementById('stakeholderNoteInput').value.trim(),
      source: 'upload',
      status: 'active'
    };
    if (!payload.linkedin_url) {
      toast('Add a LinkedIn URL first');
      return;
    }
    try {
      await addStakeholder(payload);
      closeModal();
      toast('Stakeholder added');
      await loadStakeholders();
    } catch (error) {
      toast(uploadErrorMessage(error));
    }
  });

  document.getElementById('stakeholderCsvInput').addEventListener('change', async function (event) {
    const input = event.target;
    const file = input.files && input.files[0];
    if (!file || csvImportBusy) return;
    csvImportBusy = true;
    input.disabled = true;
    try {
      await importCsvFile(file);
    } catch (error) {
      window.alert(uploadErrorMessage(error));
    } finally {
      input.value = '';
      input.disabled = false;
      csvImportBusy = false;
    }
  });
  el.download.addEventListener('click', downloadStakeholders);
  if (el.campaignFilter) {
    el.campaignFilter.addEventListener('change', function () {
      state.campaignFilter = el.campaignFilter.value;
      updateCampaignUrl();
      renderStakeholders();
    });
  }
  window.addEventListener('popstate', function () {
    state.campaignFilter = new URLSearchParams(window.location.search).get('campaign') || '';
    renderCampaignFilter();
    renderStakeholders();
  });
  document.getElementById('openCsvFormatBtn').addEventListener('click', openCsvFormatModal);
  document.getElementById('downloadCsvTemplateBtn').addEventListener('click', downloadCsvTemplate);

  refreshAll();
})();
