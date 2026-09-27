(function () {
  'use strict';

  function escapeHtml(value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, function (character) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[character];
    });
  }

  function tableContact(row, key) {
    const leadId = row.pipeline_company_id || row.id;
    if (!leadId) return null;
    if (key === 'company_phone' || key === 'company_email') {
      return {
        target: { lead_id: leadId, contact_type: 'company' },
        name: row.company_name || 'Company',
        phone: row.company_phone || '', email: row.company_email || ''
      };
    }
    if (key.indexOf('key_employee.employee.') === 0) {
      const person = row.key_employee && row.key_employee.employee;
      if (!person || !person.id || !['internal', 'linkedin'].includes(person.enricher)) return null;
      return {
        target: { lead_id: leadId, contact_type: 'key_contact', participant_id: person.id, participant_source: person.enricher },
        name: person.contact_name || 'Key contact',
        phone: person.phone || '', email: person.email || ''
      };
    }
    if ((key === 'phone_number' || key === 'email') && row.participant_id) {
      return {
        target: { lead_id: leadId, contact_type: 'person', participant_id: row.participant_id },
        name: row.name || 'Lead',
        phone: row.phonenumber || row.phone_number || '', email: row.email || ''
      };
    }
    return null;
  }

  function renderCell(value, type, row, key) {
    const isPhone = key.indexOf('phone') !== -1;
    const contact = tableContact(row, key);
    const person = row.key_employee && row.key_employee.employee;
    const displayValue = value || (key === 'phone_number' ? row.phonenumber || row.phonenumber_2 : '') ||
      (key === 'key_employee.employee.phone' && person ? person.phone2 : '') || '';
    if (type !== 'display') return displayValue;
    let html = displayValue ? '<a href="' + (isPhone ? 'tel:' + String(displayValue).replace(/[^+0-9]/g, '') : 'mailto:' + escapeHtml(displayValue)) + '">' + escapeHtml(displayValue) + '</a>' : '';
    if (!displayValue && key === 'key_employee.employee.phone' && person && person.linkedin) {
      html = '<button type="button" data-company_uid="' + escapeHtml(row.uid) + '" data-id="' + escapeHtml(person.id) + '" class="btn btn-info text-black-100 checkFullEnrich">Check FullEnrich</button>';
    }
    if (contact) {
      const label = (displayValue ? 'Edit ' : 'Add ') + (isPhone ? 'phone' : 'email');
      html += '<button type="button" class="lead-contact-edit" data-edit-lead-contact="' + escapeHtml(key) + '" aria-label="' + label + ' for ' + escapeHtml(contact.name) + '">' + label + '</button>';
    }
    return html ? '<span class="lead-contact-cell">' + html + '</span>' : '—';
  }

  async function save(target, changes) {
    const bearer = document.body.getAttribute('session_id') || localStorage.getItem('session_id') || sessionStorage.getItem('session_id') || '';
    const response = await fetch('https://api.coherta.com/leads/contact', {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json', Authorization: 'Bearer ' + bearer },
      body: JSON.stringify(Object.assign({}, target, changes))
    });
    let result;
    try { result = await response.json(); } catch (error) { throw new Error('Could not save contact details. Please try again.'); }
    if (!response.ok || !result || result.status !== 'updated') {
      throw new Error((result && (result.detail || result.message)) || 'Could not save contact details. Please try again.');
    }
    return result;
  }

  function open(options) {
    if (document.querySelector('.lead-contact-dialog')) return;
    const previousFocus = document.activeElement;
    const dialog = document.createElement('dialog');
    dialog.className = 'lead-contact-dialog';
    dialog.setAttribute('aria-labelledby', 'leadContactTitle');
    dialog.innerHTML = '<form><h2 id="leadContactTitle">Contact details</h2><p class="lead-contact-name"></p>' +
      '<p>Add a phone number, an email address, or both.</p>' +
      '<label for="leadContactPhone">Phone number</label><input id="leadContactPhone" name="phone_number" type="tel" autocomplete="tel" maxlength="255" placeholder="e.g. +45 12 34 56 78">' +
      '<label for="leadContactEmail">Email address</label><input id="leadContactEmail" name="email" type="email" autocomplete="email" maxlength="254" placeholder="name@example.com">' +
      '<p class="lead-contact-error" role="alert" hidden></p><footer><button type="button" data-contact-cancel>Cancel</button><button type="submit" data-contact-save>Save contact details</button></footer></form>';
    const form = dialog.querySelector('form');
    const phone = form.elements.phone_number;
    const email = form.elements.email;
    const errorBox = dialog.querySelector('[role="alert"]');
    const saveButton = dialog.querySelector('[data-contact-save]');
    const cancelButton = dialog.querySelector('[data-contact-cancel]');
    const initial = { phone_number: String(options.phone || ''), email: String(options.email || '') };
    let busy = false;
    dialog.querySelector('.lead-contact-name').textContent = options.name || 'Lead';
    phone.value = initial.phone_number;
    email.value = initial.email;
    cancelButton.addEventListener('click', function () { if (!busy) dialog.close(); });
    dialog.addEventListener('cancel', function (event) { if (busy) event.preventDefault(); });
    dialog.addEventListener('close', function () {
      dialog.remove();
      if (previousFocus && previousFocus.isConnected) previousFocus.focus();
    });
    form.addEventListener('submit', async function (event) {
      event.preventDefault();
      if (busy) return;
      phone.value = phone.value.trim();
      email.value = email.value.trim();
      const digits = phone.value.replace(/\D/g, '');
      phone.setCustomValidity(phone.value && (digits.length < 3 || !/^\+?[0-9\s().\-/#xext]+$/i.test(phone.value)) ? 'Enter a valid phone number, including the country code if needed.' : '');
      if (!form.reportValidity()) return;
      const changes = {};
      if (phone.value !== initial.phone_number) changes.phone_number = phone.value;
      if (email.value !== initial.email) changes.email = email.value;
      if (!Object.keys(changes).length) {
        errorBox.textContent = 'Add or change a phone number or email address before saving.';
        errorBox.hidden = false;
        return;
      }
      busy = true;
      saveButton.disabled = cancelButton.disabled = phone.disabled = email.disabled = true;
      saveButton.textContent = 'Saving…';
      errorBox.hidden = true;
      try {
        const result = await save(options.target, changes);
        if (options.onSave) options.onSave(result);
        dialog.close();
      } catch (error) {
        errorBox.textContent = error.message || 'Could not save contact details. Please try again.';
        errorBox.hidden = false;
      } finally {
        busy = false;
        saveButton.disabled = cancelButton.disabled = phone.disabled = email.disabled = false;
        saveButton.textContent = 'Save contact details';
      }
    });
    phone.addEventListener('input', function () { phone.setCustomValidity(''); });
    document.body.appendChild(dialog);
    dialog.showModal();
    (options.focus === 'email' ? email : phone).focus();
  }

  function applyToRow(row, key, result) {
    if (key.indexOf('company_') === 0) {
      row.company_phone = result.phone_number;
      row.company_email = result.email;
    } else if (key.indexOf('key_employee.') === 0) {
      row.key_employee.employee.phone = result.phone_number;
      row.key_employee.employee.email = result.email;
    } else {
      row.phonenumber = result.phone_number;
      row.phone_number = result.phone_number || row.phonenumber_2 || '';
      row.email = result.email;
    }
  }

  document.addEventListener('click', function (event) {
    const button = event.target.closest('[data-edit-lead-contact]');
    if (!button) return;
    event.preventDefault();
    const key = button.getAttribute('data-edit-lead-contact');
    const table = window.jQuery('#ajax-data-array').DataTable();
    const tableRow = table.row(button.closest('tr'));
    const row = tableRow.data();
    const contact = row && tableContact(row, key);
    if (!contact) return;
    contact.focus = key.indexOf('email') !== -1 ? 'email' : 'phone';
    contact.onSave = function (result) {
      table.rows().every(function () {
        const other = this.data();
        const otherContact = tableContact(other, key);
        if (!otherContact) return;
        const sameContact = JSON.stringify(otherContact.target) === JSON.stringify(contact.target) ||
          (contact.target.contact_type === 'person' && String(other.participant_id) === String(row.participant_id)) ||
          (contact.target.contact_type === 'key_contact' && otherContact.target.participant_source === contact.target.participant_source &&
            String(otherContact.target.participant_id) === String(contact.target.participant_id)) ||
          (contact.target.contact_type === 'company' && row.uid && other.uid === row.uid);
        if (sameContact) { applyToRow(other, key, result); this.invalidate('data'); }
      });
      table.draw(false);
    };
    open(contact);
  });

  window.LeadContactEditor = { open: open, renderCell: renderCell, tableContact: tableContact };
})();
