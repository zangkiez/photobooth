// eslint-disable-next-line no-unused-vars
function adminImageSelect(element, path) {
    const parent = element.closest('.adminImageSelection');
    const origin = element.dataset.origin;
    const src = element.src;
    const previewElement = parent.querySelector('.adminImageSelection-preview');
    const textElement = parent.querySelector('.adminImageSelection-text');
    const inputElement = parent.querySelector('input[name="' + path + '"]');

    previewElement.src = src;
    textElement.textContent = origin;
    inputElement.value = origin;

    if (src !== '') {
        previewElement.parentElement.classList.remove('hidden');
    } else {
        previewElement.parentElement.classList.add('hidden');
    }

    const event = new Event('change');
    inputElement.dispatchEvent(event);

    const toggleGeneralCheckbox = (checkboxName) => {
        const checkbox = document.querySelector(`input[name='${checkboxName}'][data-trigger='general']`);
        if (checkbox && checkbox.checked === false) {
            checkbox.checked = true;
            checkbox.dispatchEvent(new Event('change'));
        }
    };

    if (path === 'generator-background') {
        toggleGeneralCheckbox('show-background');
    }
    if (path === 'generator-frame') {
        toggleGeneralCheckbox('show-frame');
    }
    parent.classList.remove('isOpen');
}

// eslint-disable-next-line no-unused-vars
function openAdminImageSelect(element) {
    element.closest('.adminImageSelection').classList.add('isOpen');
}

// eslint-disable-next-line no-unused-vars
function closeAdminImageSelect() {
    const selections = document.querySelectorAll('.adminImageSelection');
    selections.forEach((selection) => {
        selection.classList.remove('isOpen');
    });
}

// Upload from computer: when file input in image select modal changes, upload and set selection
function initAdminImageSelectUpload() {
    document.addEventListener('change', function (e) {
        if (!e.target.classList || !e.target.classList.contains('adminImageSelectUploadInput')) {
            return;
        }
        const fileInput = e.target;
        const file = fileInput.files && fileInput.files[0];
        if (!file) {
            return;
        }
        const targetName = fileInput.getAttribute('data-target-name');
        const parent = fileInput.closest('.adminImageSelection');
        if (!parent || !targetName) {
            return;
        }
        const formData = new FormData();
        formData.append('type', 'upload_image');
        if (typeof csrf !== 'undefined' && csrf && csrf.token) {
            formData.append('csrf', csrf.token);
        }
        formData.append('image', file);

        const apiUrl = typeof environment !== 'undefined' && environment && environment.baseUrl
            ? (environment.baseUrl.replace(/\/$/, '') + '/api/admin.php')
            : 'api/admin.php';

        fetch(apiUrl, {
            method: 'POST',
            body: formData
        })
            .then(function (res) {
                if (!res.ok) {
                    return res.json().then(function (body) {
                        throw new Error(body.error || 'Upload failed');
                    });
                }
                return res.json();
            })
            .then(function (data) {
                const path = data.path;
                if (!path) {
                    throw new Error('No path returned');
                }
                const previewElement = parent.querySelector('.adminImageSelection-preview');
                const textElement = parent.querySelector('.adminImageSelection-text');
                const inputElement = parent.querySelector('input[name="' + targetName + '"]');
                if (!inputElement) {
                    return;
                }
                const baseUrl = (typeof environment !== 'undefined' && environment && environment.baseUrl)
                    ? environment.baseUrl.replace(/\/$/, '')
                    : '';
                const publicUrl = path.startsWith('http') ? path : (baseUrl + '/' + path.replace(/^\//, ''));
                inputElement.value = path;
                if (previewElement) {
                    previewElement.src = publicUrl;
                    previewElement.parentElement.classList.remove('hidden');
                }
                if (textElement) {
                    textElement.textContent = path;
                }
                inputElement.dispatchEvent(new Event('change'));
                parent.classList.remove('isOpen');
                fileInput.value = '';
            })
            .catch(function (err) {
                if (typeof openToast === 'function') {
                    openToast(err.message || 'Upload failed', 'isError', 5000);
                } else {
                    alert(err.message || 'Upload failed');
                }
                fileInput.value = '';
            });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdminImageSelectUpload);
} else {
    initAdminImageSelectUpload();
}
