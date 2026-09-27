function attachFormSaver(formSelector, buttonSelector, saveUrl, onComplete) {
    const form = document.querySelector(formSelector);
    if (!form) {
        console.warn("attachFormSaver: form not found");
        return;
    }
    const button = form.querySelector(buttonSelector);
    if (!button) {
        console.warn("attachFormSaver: button not found");
        return;
    }

    button.addEventListener("click", function (e) {
        e.preventDefault();
        if (window.tinymce) tinymce.triggerSave();

        // validate all required fields in the form
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        // build an object from all form inputs
        const formDataObj = {};
        form.querySelectorAll("input, textarea, select").forEach(el => {
            if (!el.name) return;

            if (el.type === "checkbox") {
                formDataObj[el.name] = el.checked;
            } else if (el.type === "radio") {
                if (el.checked) formDataObj[el.name] = el.value;
            } else {
                formDataObj[el.name] = el.value;
            }
        });
        const bearerToken = document.body.getAttribute('session_id');


        // send it
        fetch("https://api.coherta.com/" + saveUrl, {
            method: "POST",
            headers: { "Content-Type": "application/json", "Authorization": "Bearer " + bearerToken },
            body: JSON.stringify(formDataObj)
        })
            .then(res => res.json())
            .then(data => {
                form.reset();
                const firstTextarea = form.querySelector("textarea");
                if (firstTextarea) {
                    firstTextarea.style.backgroundColor = "#d4edda";
                    firstTextarea.style.borderColor = "#28a745";
                    setTimeout(() => {
                        firstTextarea.style.backgroundColor = "";
                        firstTextarea.style.borderColor = "";
                    }, 1500);
                }
                if (typeof onComplete === 'function') {
                    onComplete(data);
                }
            })
            .catch(err => {
                console.error("Error:", err);
                alert("Error saving form");
            });
    });
}

async function loadNotes(pipelineCompanyId) {
    try {
        // GET all notes for the company
        const response = await fetch(`https://api.coherta.com/pipeline/leads/notes?id=${pipelineCompanyId}`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${document.body.getAttribute('session_id')}`,
                'Accept': 'application/json'
            }
        });
        const notes = await response.json();

        if (!Array.isArray(notes)) {
            console.error('Unexpected response:', notes);
            alert('Failed to load notes.');
            return;
        }

        const template = document.getElementById('note-template');
        const container = document.getElementById('notes-container');

        // Clear previous notes
        container.innerHTML = '';

        notes.forEach(note => {
            const clone = template.content.cloneNode(true);

            const replacements = {};
            for (const key in note) {
                if(note[key] === null && key === "img_key") {
                    const placeholder = `#${key.toUpperCase()}`;
                    replacements[placeholder] = "assets/images/user/user.png";
                } else {
                    if (note.hasOwnProperty(key) && key !== "img_key") {
                        const placeholder = `#${key.toUpperCase()}`;
                        replacements[placeholder] = note[key];
                    }
                    if (note.hasOwnProperty(key) && key === "img_key") {
                        replacements["#IMG_KEY"] = "https://api.coherta.com/images/" + note[key];
                    }
                }
            }
            clone.querySelectorAll('*').forEach(el => {
                if (el.childNodes.length === 1 && el.childNodes[0].nodeType === Node.TEXT_NODE) {
                    el.textContent = replacePlaceholders(el.textContent, replacements);
                } else {
                    el.innerHTML = replacePlaceholders(el.innerHTML, replacements);
                }
            });

            if (note.attachment) {
                const attachmentLink = document.createElement('a');
                attachmentLink.href = `/path/to/attachment/${note.id}`;
                attachmentLink.textContent = 'View Attachment';
                attachmentLink.target = '_blank';
                clone.querySelector('.card-body')?.appendChild(attachmentLink);
            }

            container.appendChild(clone);
        });

        function replacePlaceholders(str, replacements) {

            return str.replace(/#([A-Z0-9_]+)/g, (_, key) => {
                const value = replacements[`#${key}`];
                return value !== undefined && value !== null ? value : `#${key}`;
            });
        }
    } catch (err) {
        console.error('Request failed:', err);
        alert('Network error while loading notes.');
    }
}
