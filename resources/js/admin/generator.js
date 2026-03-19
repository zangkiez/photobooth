/* globals photoboothTools */
$(function () {
    const startPreloaded = $('#start_preloaded').val() === '1';
    if (startPreloaded) {
        loadCurrentConfig();
    } else {
        changeGeneralSetting();
    }
    syncPctInputs();
});

// triggers
$(window).on('resize', changeGeneralSetting);
$("[data-trigger='general']").change(changeGeneralSetting);
$("[data-trigger='image']").change(handleInputUpdate);
// % inputs for width/height → update hidden expression input, then re-render
$(document).on('input change', 'input[data-pct-for]', function () {
    const pct = parseFloat($(this).val());
    if (isNaN(pct) || pct <= 0) return;
    const axis     = $(this).data('pct-axis');
    const targetName = $(this).data('pct-for');
    const ratio    = parseFloat((pct / 100).toFixed(6));
    $('input[name="' + targetName + '"]').val(axis + '*' + ratio);
    // call updateImage directly — avoids relying on .trigger('change') on hidden input
    const idx = targetName.split('-').pop();
    updateImage(idx);
});


$('input[name^="picture-image-"]').on('change', function () {
    const index = $(this).attr('name').replace('picture-image-', '');
    updateImage(index);
    // Update card background to show selected image
    const path = $(this).val();
    const card = $(this).closest('div[data-picture]');
    if (card.length && path) {
        card.css(
            'background-image',
            'linear-gradient(rgba(255,255,255,.5), rgba(255,255,255,.5)), url(' + toPublicUrl(path) + ')'
        );
    }
});
$('#loadCurrentConfiguration').click(loadCurrentConfig);

// Upload from computer in image select modal
$(document).on('change', '.adminImageSelectUploadInput', function () {
    const fileInput = this;
    const file = fileInput.files && fileInput.files[0];
    if (!file) return;
    const targetName = $(fileInput).data('target-name');
    const parent = $(fileInput).closest('.adminImageSelection');
    if (!parent.length || !targetName) return;
    const formData = new FormData();
    formData.append('type', 'upload_image');
    if (typeof csrf !== 'undefined' && csrf && csrf.token) formData.append('csrf', csrf.token);
    formData.append('image', file);
    const apiUrl =
        typeof environment !== 'undefined' && environment && environment.baseUrl
            ? environment.baseUrl.replace(/\/$/, '') + '/api/admin.php'
            : 'api/admin.php';
    fetch(apiUrl, { method: 'POST', body: formData })
        .then(function (res) {
            if (!res.ok)
                return res.json().then(function (body) {
                    throw new Error(body.error || 'Upload failed');
                });
            return res.json();
        })
        .then(function (data) {
            const path = data.path;
            if (!path) throw new Error('No path returned');
            const previewElement = parent.find('.adminImageSelection-preview')[0];
            const textElement = parent.find('.adminImageSelection-text')[0];
            const inputElement = parent.find('input[name="' + targetName + '"]')[0];
            if (!inputElement) return;
            const publicUrl = toPublicUrl(path);
            $(inputElement).val(path);
            if (previewElement) {
                $(previewElement).attr('src', publicUrl);
                $(previewElement).parent().removeClass('hidden');
            }
            if (textElement) $(textElement).text(path);
            $(inputElement).trigger('change');
            parent.removeClass('isOpen');
            fileInput.value = '';
        })
        .catch(function (err) {
            if (typeof openToast === 'function') openToast(err.message || 'Upload failed', 'isError', 5000);
            else alert(err.message || 'Upload failed');
            fileInput.value = '';
        });
});

function toPublicUrl(path) {
    if (!path) {
        return '';
    }
    if (path.startsWith('http') || path.startsWith('//')) {
        return path;
    }

    // remove trailing slash from baseUrl
    const baseUrl = environment.baseUrl.replace(/\/$/, '');
    // remove leading slash from requested path and concatenate with baseUrl
    return `${baseUrl}/${path.replace(/^\//, '')}`;
}
function loadCurrentConfig() {
    const raw = $('#current_config').val();
    const current_config = raw ? JSON.parse(raw) : null;
    if (!current_config) { changeGeneralSetting(); return; }
    loadConfigFromData(current_config);
}

function loadConfigFromData(current_config) {
    if (!current_config) { changeGeneralSetting(); return; }
    //loading the configuration just like in the backend
    const collageConfig = config.collage;
    const textConfig = config.textoncollage;
    let collage_height = 1200;
    let collage_width = 1800;
    let layout = current_config;
    let backgroundImage = collageConfig.background;
    let show_bg = backgroundImage ? true : false;
    let backgroundColor = collageConfig.background_color;
    let frameImage = collageConfig.frame;
    let show_frame = frameImage ? true : false;
    let applyFrame = collageConfig.take_frame;
    let backgroundOnTop = collageConfig.background_on_top || false;
    let placeholder = collageConfig.placeholder;
    let placeholderpath = collageConfig.placeholderpath;
    let placeholderposition = collageConfig.placeholderposition;
    let text_enabled = textConfig.enabled;
    let font_family = textConfig.font;
    let font_color = textConfig.font_color;
    let font_size = textConfig.font_size;
    let line1 = textConfig.line1;
    let line2 = textConfig.line2;
    let line3 = textConfig.line3;
    let linespace = textConfig.linespace;
    let locationX = textConfig.locationx;
    let locationY = textConfig.locationy;
    let text_rotation = textConfig.rotation;
    if (!Array.isArray(current_config)) {
        collage_width = current_config.width;
        collage_height = current_config.height;
        layout = current_config.layout;
        backgroundImage = current_config.background;
        show_bg = backgroundImage ? true : false;
        backgroundColor = current_config.background_color;
        frameImage = current_config.frame;
        show_frame = frameImage ? true : false;
        applyFrame = current_config.apply_frame;
        backgroundOnTop = current_config.background_on_top || false;
        placeholder = current_config.placeholder;
        placeholderpath = current_config.placeholderpath;
        placeholderposition = current_config.placeholderposition;
        text_enabled = current_config.text_custom_style;
        font_family = current_config.text_font;
        font_color = current_config.text_font_color;
        font_size = current_config.text_font_size;
        line1 = current_config.text_line1;
        line2 = current_config.text_line2;
        line3 = current_config.text_line3;
        linespace = current_config.text_linespace;
        locationX = current_config.text_locationx;
        locationY = current_config.text_locationy;
        text_rotation = current_config.text_rotation;
    }

    //populate the inputs
    //general
    $("input[name='final_width']").val(collage_width);
    $("input[name='final_height']").val(collage_height);
    $("input[name='background_color']").val(backgroundColor);

    $("input[name='generator-background']").attr('value', backgroundImage);
    $("input[name='generator-background']")
        .parents('.adminImageSelection')
        .find('.adminImageSelection-preview')
        .attr('src', toPublicUrl(backgroundImage));
    $("input[name='show-background'][data-trigger='general']").prop('checked', show_bg);

    $("input[name='generator-frame']").attr('value', frameImage);
    $("input[name='generator-frame']")
        .parents('.adminImageSelection')
        .find('.adminImageSelection-preview')
        .attr('src', toPublicUrl(frameImage));
    $("input[name='show-frame'][data-trigger='general']").prop('checked', show_frame);

    $("select[name='apply_frame']").val(applyFrame);
    $("input[name='generator-background_on_top'][data-trigger='general']").prop('checked', backgroundOnTop);

    //placeholder
    $("input[name='placeholder_image_position']").val(placeholderposition);
    $("input[name='placeholder_image']").attr('value', placeholderpath);
    $("input[name='placeholder_image']")
        .parents('.adminImageSelection')
        .find('.adminImageSelection-preview')
        .attr('src', toPublicUrl(placeholderpath));
    $("input[name='enable_placeholder_image'][data-trigger='general']").prop('checked', placeholder);

    //text
    $("input[name='text_enabled'][data-trigger='general']").prop('checked', text_enabled);
    $("input[name='text_font_family']").val(font_family);
    $("input[name='text_font_family']")[0].setAttribute('data-fontclass', 'font-selected');
    const font_family_public = toPublicUrl(font_family);
    $('#fontselectedStyle').html(
        `@font-face{font-family:"fontselected";src:url(${font_family_public}) format("truetype");} .font-selected{font-family:"fontselected",Verdena,Tahoma;}`
    );
    $("input[name='text_font_color']").attr('value', font_color);
    $("input[name='text_font_size']").attr('value', font_size);
    $("input[name='text_line_1']").attr('value', line1);
    $("input[name='text_line_2']").attr('value', line2);
    $("input[name='text_line_3']").attr('value', line3);
    $("input[name='text_line_space']").attr('value', linespace);
    $("input[name='text_location_x']").attr('value', locationX);
    $("input[name='text_location_y']").attr('value', locationY);
    $("input[name='text_rotation']").attr('value', text_rotation);
    $("input[name='text_rotation']").parent().find('span:first').text(text_rotation);

    //hide images and image settings
    $('#result_canvas').find("div[id^='picture-']").addClass('hidden');
    $('#layout_containers').find("div[data-picture^='picture-']").addClass('hidden');

    if (Array.isArray(layout)) {
        for (let i = 0; i < layout.length; i++) {
            let identifier = 'picture-' + i;
            let inputLayout = $("div[data-picture='" + identifier + "']");
            inputLayout.removeClass('hidden');
            let exampleImage = $('#' + identifier);
            exampleImage.removeClass('hidden');

            inputLayout.find('input[data-prop]').each(function (propertyPosition) {
                let inputType = $(this).attr('type');
                if (inputType === 'range') {
                    $(this).parent().find('span:first').text(layout[i][propertyPosition]);
                } else if (inputType === 'checkbox') {
                    $(this).prop('checked', layout[i][propertyPosition]);
                }
                if (propertyPosition !== 5) {
                    $(this).val(layout[i][propertyPosition]);
                }
            });
        }
    }

    //start rendering
    syncPctInputs();
    changeGeneralSetting();
}

// ── Save dialog ──────────────────────────────────────────────────
function genOpenSaveDialog(stringedConfiguration, canSubmit) {
    const overlay = document.getElementById('gen-save-dialog');
    const nameInput = document.getElementById('gen-save-name');
    // pre-fill with last used name
    const lastUsed = $("input[name='collage-name']").val();
    nameInput.value = lastUsed || '';
    overlay.classList.add('show');
    nameInput.focus();
    nameInput.select();

    function doSave() {
        const name = nameInput.value.trim();
        if (!name) { nameInput.focus(); return; }
        overlay.classList.remove('show');
        $("input[name='collage-name']").val(name);
        if (canSubmit === '1') {
            $("input[name='new-configuration']").val(stringedConfiguration);
            $('#configuration_form').trigger('submit');
        } else {
            photoboothTools.modal.open();
            const modalBody = photoboothTools.modal.element.querySelector('.modal-body');
            const enableWriteMessage = $('#enable_write_message').val();
            const messageDiv = document.createElement('div');
            messageDiv.innerText = enableWriteMessage;
            modalBody.appendChild(messageDiv);
            const jsonDiv = document.createElement('div');
            jsonDiv.innerText = stringedConfiguration;
            jsonDiv.style.fontFamily = 'monospace';
            modalBody.appendChild(jsonDiv);
        }
    }

    document.getElementById('gen-save-confirm').onclick = doSave;
    nameInput.onkeydown = function(e) { if (e.key === 'Enter') doSave(); };
    document.getElementById('gen-save-cancel').onclick = function() { overlay.classList.remove('show'); };
    overlay.onclick = function(e) { if (e.target === overlay) overlay.classList.remove('show'); };
}

// ── Collage thumbnail helper ─────────────────────────────────────
function genCollageThumb(data) {
    var layout = Array.isArray(data && data.layout) ? data.layout : (Array.isArray(data) ? data : []);
    var cw = parseFloat((data && data.width) || 1800) || 1800;
    var ch = parseFloat((data && data.height) || 1200) || 1200;
    var isCouple = !!(data && data.couple_mode);
    var halfCount = isCouple ? Math.ceil(layout.length / 2) : layout.length;
    var isPortrait = ch > cw;
    var TW = isPortrait ? 44 : 64, TH = isPortrait ? 64 : 44;
    var sx = TW / cw, sy = TH / ch;
    function evExpr(s) {
        var str = String(s == null ? '0' : s).trim()
            .replace(/\bx\b/g, String(cw))
            .replace(/\by\b/g, String(ch));
        try { return calculate(tokenize(str)); } catch (e) { return 0; }
    }
    var rects = '';
    layout.forEach(function (slot, i) {
        if (!Array.isArray(slot) || slot.length < 4) return;
        var x = evExpr(slot[0]), y = evExpr(slot[1]),
            w = evExpr(slot[2]), h = evExpr(slot[3]);
        var label = (i % halfCount) + 1;
        var rx = Math.round(x * sx), ry = Math.round(y * sy);
        var rw = Math.max(2, Math.round(w * sx)), rh = Math.max(2, Math.round(h * sy));
        var fs = Math.max(4, Math.min(8, Math.round(rh * 0.38)));
        rects += '<rect x="' + rx + '" y="' + ry + '" width="' + rw + '" height="' + rh +
                 '" fill="rgba(99,102,241,.2)" stroke="#6366f1" stroke-width="0.7" rx="1"/>';
        rects += '<text x="' + (rx + rw / 2) + '" y="' + (ry + rh / 2 + fs * 0.38) +
                 '" font-size="' + fs + '" fill="#6366f1" text-anchor="middle" font-family="sans-serif">' +
                 label + '</text>';
    });
    var centerLine = isCouple
        ? '<line x1="' + Math.round(TW / 2) + '" y1="1" x2="' + Math.round(TW / 2) + '" y2="' + (TH - 1) +
          '" stroke="#bbb" stroke-width="0.7" stroke-dasharray="2,1.5"/>'
        : '';
    return '<svg class="gen-collage-thumb" width="' + TW + '" height="' + TH +
           '" viewBox="0 0 ' + TW + ' ' + TH + '" xmlns="http://www.w3.org/2000/svg">' +
           '<rect width="' + TW + '" height="' + TH + '" fill="#f3f4f6" rx="2" stroke="#d1d5db" stroke-width="0.5"/>' +
           centerLine + rects + '</svg>';
}

// ── Load dialog ──────────────────────────────────────────────────
function genOpenLoadModal() {
    const saves = (typeof savedCollagesData !== 'undefined') ? savedCollagesData : [];
    const list = document.getElementById('gen-collage-list');
    const overlay = document.getElementById('gen-load-dialog');
    list.innerHTML = '';

    if (!saves.length) {
        list.innerHTML = '<p class="gen-collage-empty"><i class="fa fa-inbox"></i><br>No saved collages yet.</p>';
    } else {
        saves.forEach(function(s) {
            const row = document.createElement('div');
            row.className = 'gen-collage-item';
            const d = new Date(s.mtime * 1000);
            const dateStr = d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
            const isCouple = !!(s.data && s.data.couple_mode);
            const layoutArr = Array.isArray(s.data && s.data.layout) ? s.data.layout :
                              (Array.isArray(s.data) ? s.data : []);
            const totalSlots = layoutArr.length;
            const photoCount = isCouple ? Math.ceil(totalSlots / 2) : totalSlots;
            const shotWord = photoCount === 1 ? 'SHOT' : 'SHOTS';
            const slotLabel = totalSlots
                ? (isCouple
                    ? photoCount + ' ' + shotWord + ' · 2 STRIPS'
                    : photoCount + ' ' + shotWord + ' · 1 STRIP')
                : '';
            const badge = isCouple ? '<span class="gen-couple-badge">2-STRIPS</span>' : '';
            row.innerHTML =
                genCollageThumb(s.data) +
                '<div class="gen-collage-info"><strong>' + escapeHtml(s.name) + badge + '</strong>' +
                '<span>' + dateStr + (slotLabel ? ' · ' + slotLabel : '') + '</span></div>' +
                '<button type="button" class="gen-collage-load-btn">Load</button>';
            row.querySelector('.gen-collage-load-btn').onclick = function () {
                overlay.classList.remove('show');
                // update collage-name so save will default to same name
                $("input[name='collage-name']").val(s.name);
                loadConfigFromData(s.data);
            };
            list.appendChild(row);
        });
    }

    overlay.classList.add('show');
    document.getElementById('gen-load-close').onclick = function() { overlay.classList.remove('show'); };
    overlay.onclick = function(e) { if (e.target === overlay) overlay.classList.remove('show'); };
}

function escapeHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function changeGeneralSetting() {
    const c_width = $("input[name='final_width']").val();
    const c_height = $("input[name='final_height']").val();
    const c_bg_color = $("input[name='background_color']").val();
    const c_bg = $("input[name='generator-background']").val();
    const c_frame = $("input[name='generator-frame']").val();
    const c_bg_public = toPublicUrl(c_bg);
    const c_frame_public = toPublicUrl(c_frame);
    const c_apply_frame = $("select[name='apply_frame']").val();
    const c_show_frame = $("input[name='show-frame'][data-trigger='general']").is(':checked');
    const c_show_background = $("input[name='show-background'][data-trigger='general']").is(':checked');
    const c_background_on_top = $("input[name='generator-background_on_top'][data-trigger='general']").is(':checked');

    const c_text_enabled = $("input[name='text_enabled'][data-trigger='general']").is(':checked');
    let c_text_font = $("input[name='text_font_family']")[0].getAttribute('data-fontclass');
    let c_text_font_unique_id = $("input[name='text_font_family']").data('unique-id');
    let c_text_font_value = toPublicUrl($("input[name='text_font_family']").val());
    const c_font_color = $("input[name='text_font_color']").val();
    const c_font_size = $("input[name='text_font_size']").val();
    const c_text_1 = $("input[name='text_line_1']").val();
    const c_text_2 = $("input[name='text_line_2']").val();
    const c_text_3 = $("input[name='text_line_3']").val();
    const c_text_space = $("input[name='text_line_space']").val();
    const c_text_top = $("input[name='text_location_y']").val();
    const c_text_left = $("input[name='text_location_x']").val();
    const c_text_rotation = -parseInt($("input[name='text_rotation']").val(), 10);

    const aspect_ratio = c_width / c_height;

    const canvasDOM = $('#result_canvas');

    canvasDOM.css('aspect-ratio', aspect_ratio);
    canvasDOM.css('background-color', c_bg_color);

    const bgDiv = canvasDOM.find('div#collage_background');
    const bgImgElement = bgDiv.find('img');
    const pictureDivs = canvasDOM.find("div[id^='picture-']");
    const frameDiv = canvasDOM.find('div#collage_frame');
    const textDiv = canvasDOM.find('div#collage_text');

    bgImgElement.attr('src', c_bg_public);
    bgImgElement.addClass('hidden');
    if (c_show_background) {
        bgImgElement.removeClass('hidden');
    }

    // Layer stacking: background_on_top puts background ABOVE photos (matching Collage.php behavior)
    bgDiv.css('z-index', c_background_on_top ? 5 : 0);
    pictureDivs.css('z-index', 1);
    frameDiv.css('z-index', 10);
    textDiv.css('z-index', 15);

    let collageImgs = canvasDOM.find('div#collage_frame img');
    let pictureFrameImgs = canvasDOM.find('img.picture-frame');
    let allImgs = collageImgs.add(pictureFrameImgs);

    allImgs.attr('src', c_frame_public).addClass('hidden');

    if (c_show_frame) {
        allImgs.removeClass('hidden');

        if (c_apply_frame === 'always') {
            collageImgs.addClass('hidden');
        } else if (c_apply_frame === 'once') {
            pictureFrameImgs.addClass('hidden');
        } else {
            allImgs.addClass('hidden');
        }
    }

    const canvas_width = canvasDOM.width();
    const canvas_height = canvasDOM.height();
    const adjusted_tfs = (c_font_size * canvas_height) / c_height;
    const adjusted_tt = (c_text_top * canvas_height) / c_height;
    const adjusted_tl = (c_text_left * canvas_width) / c_width;
    const adjusted_tls = (c_text_space * canvas_height) / c_height;
    const real_text_top = (i) => i * adjusted_tls - adjusted_tfs;
    const real_text_left = (i) => i * adjusted_tls;
    const collageTextDOM = $('#collage_text');
    collageTextDOM.css({
        'font-size': adjusted_tfs + 'pt',
        color: c_font_color,
        top: adjusted_tt + 'px',
        left: adjusted_tl + 'px'
    });
    if (c_text_font_value.startsWith('http')) {
        c_text_font = `font-${c_text_font_unique_id}`;
        $('#fontselectedStyle').html(
            `@font-face{font-family:"fontselected";src:url(${c_text_font_value}) format("truetype");} .${c_text_font}{font-family:"fontselected",Verdena,Tahoma;}`
        );
    }
    collageTextDOM.removeClass((index, classes) =>
        classes
            .split(' ')
            .filter((cName) => cName.startsWith('font-'))
            .join(' ')
    );
    collageTextDOM.addClass(c_text_font);
    collageTextDOM
        .find('.text-line-1')
        .css({
            transform: 'rotate(' + c_text_rotation + 'deg)',
            top: real_text_top(0) + 'px'
        })
        .html(c_text_1.replace(/ /g, '\u00a0'));
    collageTextDOM
        .find('.text-line-2')
        .css({
            transform: 'rotate(' + c_text_rotation + 'deg)',
            top: (c_text_rotation > -45 && c_text_rotation < 45 ? real_text_top(1) : real_text_top(0)) + 'px',
            left: (c_text_rotation > -45 && c_text_rotation < 45 ? real_text_left(0) : real_text_left(1)) + 'px'
        })
        .html(c_text_2.replace(/ /g, '\u00a0'));
    collageTextDOM
        .find('.text-line-3')
        .css({
            transform: 'rotate(' + c_text_rotation + 'deg)',
            top: (c_text_rotation > -45 && c_text_rotation < 45 ? real_text_top(2) : real_text_top(0)) + 'px',
            left: (c_text_rotation > -45 && c_text_rotation < 45 ? real_text_left(0) : real_text_left(2)) + 'px'
        })
        .html(c_text_3.replace(/ /g, '\u00a0'));
    collageTextDOM.addClass('hidden');
    if (c_text_enabled) {
        collageTextDOM.removeClass('hidden');
    }

    const totalImages = canvasDOM.find("div[id^='picture-']").length;
    for (let i = 0; i < totalImages; i++) {
        updateImage(i);
    }
}

function handleInputUpdate() {
    const modifiedInput = $(this);
    const inputName = modifiedInput.attr('name');
    const settingsContainerId = inputName.split('-').pop();
    updateImage(settingsContainerId);
}

// Read hidden expression inputs (e.g. "x*0.5") and update visible % inputs
function syncPctInputs() {
    $('input[data-pct-for]').each(function () {
        const targetName = $(this).data('pct-for');
        const expr = $('input[name="' + targetName + '"]').val();
        const m = expr && expr.match(/^[xy]\*(\d+\.?\d*)$/);
        if (m) {
            $(this).val(Math.round(parseFloat(m[1]) * 100));
        }
    });
}

function updateImage(containerId) {
    const settingsContainer = $("div[data-picture='picture-" + containerId + "']");

    const placeholder = $("input[name='enable_placeholder_image']").is(':checked');
    const placeholder_image_position = parseInt($("input[name='placeholder_image_position']").val(), 10);
    const changepath = placeholder && placeholder_image_position === containerId + 1;

    settingsContainer.find('input').each(function () {
        let prop_name = $(this).data('prop');
        let new_value = $(this).val();
        if (prop_name === 'single_frame') {
            new_value = $(this).is(':checked');
        }
        if (prop_name) {
            changeImageSetting(new_value, prop_name, containerId, changepath);
        }
    });
}

function changeImageSetting(new_value, prop_name, index, isPlaceholder) {
    const canvas_width = $('#result_canvas').width();
    const canvas_height = $('#result_canvas').height();
    const img_container = $('#picture-' + index);
    let contImages = img_container.find('img');
    let firstImg = contImages.first();
    const slotImagePath = $("input[name='picture-image-" + index + "']").val();
    if (slotImagePath) {
        const url = toPublicUrl(slotImagePath);
        firstImg.attr('src', url);
        firstImg.data('src', url);
    } else if (isPlaceholder) {
        firstImg.attr('src', toPublicUrl($("input[name='placeholder_image']").val()));
    } else {
        firstImg.attr('src', firstImg.data('src'));
    }

    if (prop_name === 'transform') {
        let angle = -parseInt(new_value, 10);
        contImages.css(prop_name, 'rotate(' + angle + 'deg)');
        contImages.css('transform-origin', angle > 0 ? 'top right' : 'top left');
        let contW = img_container.width();
        let contH = img_container.height();
        let ar = contW / contH;
        const brute_force = angle > -80 && angle < 80 ? 100 : 200;
        const { imgW, imgH, newContW, fromTop, fromHori } = calculateImgDimensions(
            contW,
            contH,
            angle,
            ar,
            1,
            {},
            brute_force
        );
        contImages.height(imgH);
        contImages.width(imgW);
        contImages.css('top', Math.min(fromTop, 2 * contH));
        contImages.css(angle > 0 ? 'right' : 'left', fromHori || 0);
        contImages.css(angle < 0 ? 'right' : 'left', '');
        img_container.width(newContW);
    } else if (prop_name === 'single_frame') {
        contImages.last().addClass('hidden');
        if (new_value && $("select[name='apply_frame']").val() === 'always') {
            contImages.last().removeClass('hidden');
        }
    } else {
        let clean_operation = new_value.replace('x', canvas_width).replace('y', canvas_height);
        let processed_value = calculate(tokenize(clean_operation));
        if (new_value == processed_value) {
            // == and NOT === because one is a string and the other is a number
            let collage_width = $("input[name='final_width']").val();
            let collage_height = $("input[name='final_height']").val();
            if (['width', 'left'].includes(prop_name)) {
                processed_value = (new_value * canvas_width) / collage_width;
            } else if (['height', 'top'].includes(prop_name)) {
                processed_value = (new_value * canvas_height) / collage_height;
            }
        }
        img_container.css(prop_name, processed_value + 'px');
    }
}

function calculateImgDimensions(width, height, angle, aspect_ratio, times, best_guess, brute_force) {
    if ([0, -180, 180].includes(angle)) {
        return { imgW: width, imgH: height, fromTop: angle === 0 ? 0 : height, fromHori: width };
    } else if (Math.abs(angle) === 90) {
        let small_side = Math.min(width, height);
        return {
            imgW: small_side,
            imgH: small_side / aspect_ratio,
            newContW: small_side / aspect_ratio,
            fromTop: small_side,
            fromHori: 0
        };
    }

    const angleCos = Math.abs(Math.cos((angle * Math.PI) / 180));
    const angleSin = Math.abs(Math.sin((angle * Math.PI) / 180));
    let imgW = width / angleCos;
    let imgH = imgW / aspect_ratio;
    let smallCatet = Math.sqrt(Math.pow(imgW, 2) - Math.pow(width, 2));
    let largeCatet = imgH * angleCos;
    let newContW = imgW * angleCos + imgH * angleSin;
    let fromTop = smallCatet + (angle > -90 && angle < 90 ? 0 : largeCatet);
    let fromHori = angle < -90 || angle > 90 ? imgW * angleCos : null;
    let quality = 1 - (largeCatet + smallCatet) / height;

    if (Math.abs(quality) <= 0.001) {
        return { imgW, imgH, newContW, fromTop, fromHori };
    } else {
        if (times < brute_force) {
            let factor = quality > 0 ? 1.05 : 0.95;
            let new_best_guess = { quality: Math.abs(quality), imgW, imgH, newContW, fromTop, fromHori };
            if (best_guess) {
                if (best_guess.quality < new_best_guess.quality) {
                    new_best_guess = { ...best_guess };
                }
            }
            return calculateImgDimensions(
                width * factor,
                height,
                angle,
                aspect_ratio,
                times + 1,
                new_best_guess,
                brute_force
            );
        }
    }
    console.log('brute force not work! ', { quality, angle });
    return {
        imgW: best_guess.imgW,
        imgH: best_guess.imgH,
        newContW: best_guess.newContW,
        fromTop: best_guess.fromTop,
        fromHori: best_guess.fromHori
    };
}

$('#addImage').click(function () {
    const layout_settings = $('#layout_containers').find("div[data-picture^='picture-']:hidden:first");
    layout_settings.removeClass('hidden');
    const img_id = layout_settings.data('picture');
    $('#' + img_id).removeClass('hidden');
});

// eslint-disable-next-line no-unused-vars
function hideImage(containerId) {
    $("div[data-picture='" + containerId + "'").addClass('hidden');
    $('div#' + containerId).addClass('hidden');
}

// eslint-disable-next-line no-unused-vars
function saveConfiguration() {
    var coupleActive = document.getElementById('gen-couple-toggle') && document.getElementById('gen-couple-toggle').checked;
    var originalWidth = parseFloat($("input[name='final_width']").val()) || 0;
    let configuration = {
        // When couple mode is ON, double the canvas width so the prebuilt layout spans both strips.
        width: coupleActive ? String(originalWidth * 2) : $("input[name='final_width']").val(),
        height: $("input[name='final_height']").val(),
        text_custom_style: $("input[name='text_enabled'][data-trigger='general']").is(':checked'),
        text_font_size: $("input[name='text_font_size']").val(),
        text_rotation: $("input[name='text_rotation']").val(),
        text_locationx: $("input[name='text_location_x']").val(),
        text_locationy: $("input[name='text_location_y']").val(),
        text_font_color: $("input[name='text_font_color']").val(),
        text_font: $("input[name='text_font_family']").val(),
        text_line1: $("input[name='text_line_1']").val(),
        text_line2: $("input[name='text_line_2']").val(),
        text_line3: $("input[name='text_line_3']").val(),
        text_linespace: $("input[name='text_line_space']").val(),
        apply_frame: $("select[name='apply_frame']").val(),
        frame: $("input[name='generator-frame']").val(),
        background: $("input[name='generator-background']").val(),
        background_color: $("input[name='background_color']").val(),
        background_on_top: $("input[name='generator-background_on_top'][data-trigger='general']").is(':checked'),
        couple_mode: coupleActive,
        prebuilt_double: coupleActive,
        placeholder: $("input[name='enable_placeholder_image'][data-trigger='general']").is(':checked'),
        placeholderpath: $("input[name='placeholder_image']").val(),
        placeholderposition: $("input[name='placeholder_image_position']").val(),
        layout: []
    };

    // .not('.hidden'): slots live inside a non-active panel (display:none) when user is on step 4
    // so jQuery :visible returns false — use explicit class check instead
    $('div.image_layout').not('.hidden').each(function () {
        let container = $(this);
        let single_image_layout = [];
        container.find('input[data-prop]').each(function () {
            let to_save = $(this).val();
            if ($(this).attr('type') === 'checkbox') {
                to_save = $(this).is(':checked') && configuration.apply_frame === 'always';
            }
            single_image_layout.push(to_save);
        });
        configuration.layout.push(single_image_layout);
    });

    // Couple mode: bake the full prebuilt double-strip layout into the JSON.
    // Slot values are evaluated against the SINGLE-STRIP dimensions (originalWidth × sH).
    // Using the expression evaluator (not DOM measurements) avoids zoom-factor inflation:
    // resolveFrameSize() divides by getScale() which includes window._genZoom, so a
    // zoomed-out portrait canvas (e.g. zoom=0.375) inflates widths by 1/0.375 = 2.67×.
    if (coupleActive && configuration.layout.length > 0) {
        var halfCount = configuration.layout.length;
        var sH = parseFloat($("input[name='final_height']").val()) || 1;
        function evalSlotExpr(expr) {
            var s = String(expr == null ? '0' : expr).trim()
                .replace(/\bx\b/g, String(originalWidth))
                .replace(/\by\b/g, String(sH));
            try { return calculate(tokenize(s)); } catch (e2) { return 0; }
        }
        for (var ci = 0; ci < halfCount; ci++) {
            var row = configuration.layout[ci];
            configuration.layout[ci][0] = String(Math.round(evalSlotExpr(row[0])));
            configuration.layout[ci][1] = String(Math.round(evalSlotExpr(row[1])));
            configuration.layout[ci][2] = String(Math.round(evalSlotExpr(row[2])));
            configuration.layout[ci][3] = String(Math.round(evalSlotExpr(row[3])));
        }
        for (var ri = 0; ri < halfCount; ri++) {
            var mirror = configuration.layout[ri].slice();
            mirror[0] = String(parseInt(configuration.layout[ri][0], 10) + Math.round(originalWidth));
            configuration.layout.push(mirror);
        }
    }

    const canSubmit = $('#can_submit').val();
    const stringedConfiguration = customStringify(configuration);

    genOpenSaveDialog(stringedConfiguration, canSubmit);
}

function customStringify(configuration) {
    let textResult = '{';
    for (const key of Object.keys(configuration)) {
        let val = configuration[key];
        if (val instanceof Array) {
            if (val.length === 0) {
                textResult += '\n\t"' + key + '": [],';
            } else {
                textResult += '\n\t"' + key + '": [';
                for (let prop of val) {
                    textResult += '\n\t\t' + JSON.stringify(prop) + ',';
                }
                textResult = textResult.slice(0, -1); // remove last comma inside array
                textResult += '\n\t],';
            }
            continue;
        }
        textResult += '\n\t"' + key + '": ' + JSON.stringify(val) + ',';
    }
    textResult = textResult.slice(0, -1); // remove trailing comma of last key
    textResult += '\n}';
    return textResult;
}

function tokenize(s) {
    // --- Parse a calculation string into an array of numbers and operators
    const r = [];
    let token = '';
    for (const character of s) {
        if ('^*/+-'.includes(character)) {
            if (token === '' && character === '-') {
                token = '-';
            } else {
                r.push(parseFloat(token), character);
                token = '';
            }
        } else {
            token += character;
        }
    }
    if (token !== '') {
        r.push(parseFloat(token));
    }
    return r;
}

function calculate(tokens) {
    // --- Perform a calculation expressed as an array of operators and numbers
    const operatorPrecedence = [
        { '^': (a, b) => Math.pow(a, b) },
        { '*': (a, b) => a * b, '/': (a, b) => a / b },
        { '+': (a, b) => a + b, '-': (a, b) => a - b }
    ];
    let operator;
    for (const operators of operatorPrecedence) {
        const newTokens = [];
        for (const token of tokens) {
            if (token in operators) {
                operator = operators[token];
            } else if (operator) {
                newTokens[newTokens.length - 1] = operator(newTokens[newTokens.length - 1], token);
                operator = null;
            } else {
                newTokens.push(token);
            }
        }
        tokens = newTokens;
    }
    if (tokens.length > 1) {
        console.log('Error: unable to resolve calculation');
        return tokens;
    } else {
        return tokens[0];
    }
}

/* ══════════════════════════════════════════════════════════════
   GENERATOR v2 UX — append after core functions above
══════════════════════════════════════════════════════════════ */
/* ════════════════════════════════════════════════════════════════
   GENERATOR v2 — Silky smooth RAF drag · Undo · Grid+Snap
   Pan (Space) · Pinch zoom · 8-dir resize · Alignment tools
   Hold-to-repeat spinners · Scroll-wheel on inputs · Shortcuts
════════════════════════════════════════════════════════════════ */
('use strict');

// ═══════════════════════════════════════════════════════════════
// 1. WIZARD
// ═══════════════════════════════════════════════════════════════
var genCurStep = 1,
    GEN_STEPS = 4;

function genGoTo(step) {
    step = Math.max(1, Math.min(GEN_STEPS, step));
    genCurStep = step;
    document.querySelectorAll('.gen-panel').forEach(function (el) {
        el.classList.toggle('active', +el.dataset.panel === step);
    });
    document.querySelectorAll('.gen-step-tab[data-go]').forEach(function (el) {
        var s = +el.dataset.go;
        el.classList.toggle('active', s === step);
        el.classList.toggle('done', s < step);
    });
    // Progress bar
    var pf = document.getElementById('gen-progress-fill');
    if (pf) pf.style.width = Math.round((step / GEN_STEPS) * 100) + '%';
    var prev = document.getElementById('gen-prev');
    var next = document.getElementById('gen-next');
    var lbl = document.getElementById('gen-step-lbl');
    if (prev) prev.disabled = step === 1;
    if (lbl) lbl.textContent = 'Step ' + step + ' of ' + GEN_STEPS;
    if (next) {
        if (step === GEN_STEPS) {
            next.innerHTML = '<i class="fa fa-save"></i> Save';
            next.onclick = function () {
                saveConfiguration();
            };
        } else {
            next.innerHTML = 'Next <i class="fa fa-chevron-right"></i>';
            next.onclick = function () {
                genStep(1);
            };
        }
    }
    var tip = document.getElementById('gen-drag-tip');
    if (tip) tip.style.display = step === 3 ? 'flex' : 'none';
    // Show canvas tools on step 3
    var tools = document.getElementById('gen-canvas-tools');
    if (tools) tools.style.display = step === 3 ? 'flex' : 'none';
    document.querySelectorAll('.gen-drag-ov').forEach(function (ov) {
        ov.classList.toggle('visible', step === 3);
        ov.style.pointerEvents = step === 3 ? 'auto' : 'none';
    });
}
function genStep(dir) {
    genGoTo(genCurStep + dir);
}
document.querySelectorAll('.gen-step-tab[data-go]').forEach(function (el) {
    el.addEventListener('click', function () {
        genGoTo(+el.dataset.go);
    });
});

// ═══════════════════════════════════════════════════════════════
// 2. FRAME CARD — expand/collapse + select
// ═══════════════════════════════════════════════════════════════
function genToggleFrame(hd) {
    var card = hd.closest('.gen-frame-card');
    if (!card) return;
    card.classList.toggle('open');
    var idx = +card.dataset.picture.replace('picture-', '');
    genSelectFrame(idx);
}

var _selectedFrameIdx = null;
function genSelectFrame(idx) {
    _selectedFrameIdx = idx;
    document.querySelectorAll('.gen-frame-card').forEach(function (c) {
        c.classList.remove('selected');
    });
    var card = document.querySelector('[data-picture="picture-' + idx + '"]');
    if (card) {
        card.classList.add('selected');
        card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    document.querySelectorAll('.gen-drag-ov').forEach(function (o) {
        o.classList.remove('sel');
    });
    var ov = document.querySelector('.gen-drag-ov[data-fidx="' + idx + '"]');
    if (ov) ov.classList.add('sel');
}

// ═══════════════════════════════════════════════════════════════
// 3. HELPERS
// ═══════════════════════════════════════════════════════════════
function getScale() {
    var cv = document.getElementById('result_canvas');
    if (!cv) return { x: 1, y: 1 };
    var cw = parseFloat($('input[name="final_width"]').val()) || 1;
    var ch = parseFloat($('input[name="final_height"]').val()) || 1;
    // Account for current zoom so drag distance maps correctly
    var zoomFactor = window._genZoom || 1;
    return { x: (cv.offsetWidth * zoomFactor) / cw, y: (cv.offsetHeight * zoomFactor) / ch };
}
function numVal(name) {
    return parseFloat($('input[name="' + name + '"]').val()) || 0;
}
function setVal(name, v) {
    $('input[name="' + name + '"]')
        .val(v)
        .trigger('change');
}

function resolveFrameSize(idx) {
    var wRaw = $('input[name="picture-width-' + idx + '"]').val();
    var hRaw = $('input[name="picture-height-' + idx + '"]').val();
    var s = getScale();
    var el = document.getElementById('picture-' + idx);
    var fw = /^-?\d+\.?\d*$/.test((wRaw || '').trim()) ? parseFloat(wRaw) : el ? el.offsetWidth / s.x : 400;
    var fh = /^-?\d+\.?\d*$/.test((hRaw || '').trim()) ? parseFloat(hRaw) : el ? el.offsetHeight / s.y : 300;
    return { w: fw, h: fh };
}

// Snap to grid helper
var SNAP_SIZE = 25;
var _snapEnabled = false;
function maybeSnap(v) {
    return _snapEnabled ? Math.round(v / SNAP_SIZE) * SNAP_SIZE : v;
}
function showSnapLines(x, y) {
    var sh = document.getElementById('snap-h'),
        sv = document.getElementById('snap-v');
    if (sh) {
        sh.style.top = (window._genZoom || 1) * y + 'px';
        sh.classList.toggle('active', _snapEnabled);
    }
    if (sv) {
        sv.style.left = (window._genZoom || 1) * x + 'px';
        sv.classList.toggle('active', _snapEnabled);
    }
}
function hideSnapLines() {
    var sh = document.getElementById('snap-h'),
        sv = document.getElementById('snap-v');
    if (sh) sh.classList.remove('active');
    if (sv) sv.classList.remove('active');
}

// ═══════════════════════════════════════════════════════════════
// 4. UNDO STACK
// ═══════════════════════════════════════════════════════════════
var _undoStack = [];
var _undoTimer = null;

function undoPush(idx) {
    _undoStack.push({
        idx: idx,
        x: numVal('picture-x-position-' + idx),
        y: numVal('picture-y-position-' + idx),
        w: $('input[name="picture-width-' + idx + '"]').val(),
        h: $('input[name="picture-height-' + idx + '"]').val()
    });
    if (_undoStack.length > 50) _undoStack.shift();
}

function undoLast() {
    var s = _undoStack.pop();
    if (!s) {
        if (typeof openToast === 'function') openToast('Nothing to undo', 'isWarning', 1800);
        return;
    }
    setVal('picture-x-position-' + s.idx, s.x);
    setVal('picture-y-position-' + s.idx, s.y);
    $('input[name="picture-width-' + s.idx + '"]')
        .val(s.w)
        .trigger('change');
    $('input[name="picture-height-' + s.idx + '"]')
        .val(s.h)
        .trigger('change');
    genSelectFrame(s.idx);
    showUndoToast('Undone');
}

function showUndoToast(msg) {
    var t = document.getElementById('gen-undo-toast');
    var m = document.getElementById('gen-undo-msg');
    if (!t) return;
    if (m) m.textContent = msg || 'Done';
    t.classList.add('show');
    clearTimeout(_undoTimer);
    _undoTimer = setTimeout(function () {
        t.classList.remove('show');
    }, 2500);
}
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('gen-undo-btn');
    if (btn) btn.addEventListener('click', undoLast);
});

// ═══════════════════════════════════════════════════════════════
// 5. RAF DRAG + 8-DIR RESIZE (silky smooth)
// ═══════════════════════════════════════════════════════════════
(function () {
    var mode = null; // 'drag' | 'resize' | 'pan'
    var resizeDir = 'se';
    var activeIdx = null;
    var sx = 0,
        sy = 0;
    var sl = 0,
        st = 0,
        sw = 0,
        sh = 0; // start l/t/w/h
    var rafId = null;
    var pendingX = 0,
        pendingY = 0;
    var _panOriginX = 0,
        _panOriginY = 0;
    var _vpScrollX = 0,
        _vpScrollY = 0;

    window._genPanMode = false;
    var spaceDown = false;

    document.addEventListener('keydown', function (e) {
        if (e.code === 'Space' && !$(e.target).is('input,select,textarea')) {
            spaceDown = true;
            var vp = document.getElementById('canvas-viewport');
            if (vp) vp.style.cursor = 'grab';
        }
    });
    document.addEventListener('keyup', function (e) {
        if (e.code === 'Space') {
            spaceDown = false;
            var vp = document.getElementById('canvas-viewport');
            if (vp) vp.style.cursor = '';
        }
    });

    function applyFrame(nx, ny, nw, nh) {
        var cw = parseFloat($('input[name="final_width"]').val()) || 9999;
        var ch = parseFloat($('input[name="final_height"]').val()) || 9999;
        nx = maybeSnap(Math.max(0, Math.min(cw - 10, nx)));
        ny = maybeSnap(Math.max(0, Math.min(ch - 10, ny)));
        nw = Math.max(20, nw);
        nh = Math.max(20, nh);
        showSnapLines(nx, ny);
        if (mode === 'drag') {
            $('input[name="picture-x-position-' + activeIdx + '"]').val(Math.round(nx));
            $('input[name="picture-y-position-' + activeIdx + '"]').val(Math.round(ny));
            $('input[name="picture-x-position-' + activeIdx + '"]').trigger('change');
        } else {
            $('input[name="picture-width-' + activeIdx + '"]').val(Math.round(nw));
            $('input[name="picture-height-' + activeIdx + '"]').val(Math.round(nh));
            $('input[name="picture-x-position-' + activeIdx + '"]').val(Math.round(nx));
            $('input[name="picture-y-position-' + activeIdx + '"]').val(Math.round(ny));
            $('input[name="picture-width-' + activeIdx + '"]').trigger('change');
        }
    }

    function rafLoop() {
        applyFrame(pendingX, pendingY, sw, sh);
        rafId = null;
    }

    document.addEventListener('mousedown', function (e) {
        if (genCurStep !== 3) return;
        // Space-pan mode
        if (spaceDown || window._genPanMode) {
            var vp = document.getElementById('canvas-viewport');
            if (vp) {
                _panOriginX = e.clientX;
                _panOriginY = e.clientY;
                _vpScrollX = vp.scrollLeft;
                _vpScrollY = vp.scrollTop;
                mode = 'pan';
                vp.style.cursor = 'grabbing';
                e.preventDefault();
                return;
            }
        }
        var rh = e.target.closest('.gen-resize-hdl');
        if (rh) {
            activeIdx = +rh.dataset.fidx;
            resizeDir = rh.dataset.dir || 'se';
            var sz = resolveFrameSize(activeIdx);
            sx = e.clientX;
            sy = e.clientY;
            sl = numVal('picture-x-position-' + activeIdx);
            st = numVal('picture-y-position-' + activeIdx);
            sw = sz.w;
            sh = sz.h;
            undoPush(activeIdx);
            mode = 'resize';
            e.preventDefault();
            genSelectFrame(activeIdx);
            return;
        }
        var ov = e.target.closest('.gen-drag-ov');
        if (ov) {
            activeIdx = +ov.dataset.fidx;
            sx = e.clientX;
            sy = e.clientY;
            sl = numVal('picture-x-position-' + activeIdx);
            st = numVal('picture-y-position-' + activeIdx);
            var sz2 = resolveFrameSize(activeIdx);
            sw = sz2.w;
            sh = sz2.h;
            undoPush(activeIdx);
            mode = 'drag';
            ov.classList.add('dragging');
            e.preventDefault();
            genSelectFrame(activeIdx);
        }
    });

    document.addEventListener('mousemove', function (e) {
        if (!mode) return;
        if (mode === 'pan') {
            var vp = document.getElementById('canvas-viewport');
            if (vp) {
                vp.scrollLeft = _vpScrollX - (e.clientX - _panOriginX);
                vp.scrollTop = _vpScrollY - (e.clientY - _panOriginY);
            }
            return;
        }
        var s = getScale();
        var dx = (e.clientX - sx) / s.x;
        var dy = (e.clientY - sy) / s.y;
        if (mode === 'drag') {
            pendingX = sl + dx;
            pendingY = st + dy;
        } else {
            // 8-direction resize
            var nx = sl,
                ny = st,
                nw = sw,
                nh = sh;
            if (resizeDir.indexOf('e') >= 0) nw = Math.max(20, sw + dx);
            if (resizeDir.indexOf('s') >= 0) nh = Math.max(20, sh + dy);
            if (resizeDir.indexOf('w') >= 0) {
                nw = Math.max(20, sw - dx);
                nx = sl + dx;
            }
            if (resizeDir.indexOf('n') >= 0) {
                nh = Math.max(20, sh - dy);
                ny = st + dy;
            }
            pendingX = nx;
            pendingY = ny;
            sw = nw;
            sh = nh;
        }
        if (!rafId) rafId = requestAnimationFrame(rafLoop);
    });

    document.addEventListener('mouseup', function () {
        if (mode === 'pan') {
            var vp = document.getElementById('canvas-viewport');
            if (vp) vp.style.cursor = spaceDown ? 'grab' : '';
        }
        if (mode && mode !== 'pan') {
            document.querySelectorAll('.gen-drag-ov').forEach(function (o) {
                o.classList.remove('dragging');
            });
            hideSnapLines();
            showUndoToast('Move frame — Ctrl+Z to undo');
            changeGeneralSetting && changeGeneralSetting();
        }
        if (rafId) {
            cancelAnimationFrame(rafId);
            rafId = null;
        }
        mode = null;
        activeIdx = null;
    });

    // ── Touch drag ──────────────────────────────────────────
    document.addEventListener(
        'touchstart',
        function (e) {
            if (genCurStep !== 3) return;
            if (e.touches.length === 2) return; // handled by pinch zoom
            var ov = e.target.closest('.gen-drag-ov');
            if (!ov) return;
            var t = e.touches[0];
            activeIdx = +ov.dataset.fidx;
            sx = t.clientX;
            sy = t.clientY;
            sl = numVal('picture-x-position-' + activeIdx);
            st = numVal('picture-y-position-' + activeIdx);
            var sz = resolveFrameSize(activeIdx);
            sw = sz.w;
            sh = sz.h;
            undoPush(activeIdx);
            mode = 'drag';
            genSelectFrame(activeIdx);
        },
        { passive: true }
    );
    document.addEventListener(
        'touchmove',
        function (e) {
            if (mode !== 'drag') return;
            var t = e.touches[0],
                s = getScale();
            pendingX = sl + (t.clientX - sx) / s.x;
            pendingY = st + (t.clientY - sy) / s.y;
            if (!rafId) rafId = requestAnimationFrame(rafLoop);
        },
        { passive: true }
    );
    document.addEventListener('touchend', function () {
        if (mode === 'drag') {
            hideSnapLines();
            changeGeneralSetting && changeGeneralSetting();
        }
        if (rafId) {
            cancelAnimationFrame(rafId);
            rafId = null;
        }
        mode = null;
        activeIdx = null;
    });
})();

// ═══════════════════════════════════════════════════════════════
// 6. ZOOM + PAN + PINCH
// ═══════════════════════════════════════════════════════════════
(function () {
    var zoom = 1.0,
        MIN = 0.08,
        MAX = 5.0,
        STEP = 0.1;
    window._genZoom = zoom;

    function setZoom(z, cx, cy) {
        var prev = zoom;
        zoom = Math.min(MAX, Math.max(MIN, parseFloat(z.toFixed(3))));
        window._genZoom = zoom;
        var cv = document.getElementById('result_canvas');
        var vp = document.getElementById('canvas-viewport');
        if (cv) cv.style.transform = 'scale(' + zoom + ')';
        var lbl = document.getElementById('zoom-level');
        if (lbl) lbl.textContent = Math.round(zoom * 100) + '%';
        // Keep zoom centered around cx,cy if provided
        if (vp && cx !== undefined) {
            vp.scrollLeft += (cx - vp.getBoundingClientRect().left) * (zoom / prev - 1);
            vp.scrollTop += (cy - vp.getBoundingClientRect().top) * (zoom / prev - 1);
        }
    }
    function fit() {
        var vp = document.getElementById('canvas-viewport');
        var cv = document.getElementById('result_canvas');
        if (!vp || !cv) return;
        var vw = vp.clientWidth - 48,
            vh = vp.clientHeight - 48;
        var cw = cv.offsetWidth,
            ch = cv.offsetHeight;
        if (cw > 0 && ch > 0) setZoom(Math.min(vw / cw, vh / ch, 1.0));
    }
    window._genFit = fit;

    function updateDimLabel() {
        var w = $('input[name="final_width"]').val();
        var h = $('input[name="final_height"]').val();
        var el = document.getElementById('canvas-dim-label');
        if (el && w && h) el.textContent = w + ' × ' + h + ' px';
    }

    $(function () {
        $('#zoom-in').on('click', function () {
            setZoom(zoom + STEP);
        });
        $('#zoom-out').on('click', function () {
            setZoom(zoom - STEP);
        });
        $('#zoom-fit').on('click', fit);
        $('#zoom-level').on('click', function () {
            setZoom(1.0);
        });

        var vp = document.getElementById('canvas-viewport');
        if (vp) {
            // Ctrl+wheel = zoom, plain wheel = scroll
            vp.addEventListener(
                'wheel',
                function (e) {
                    if (e.ctrlKey || e.metaKey) {
                        e.preventDefault();
                        setZoom(zoom + (e.deltaY < 0 ? STEP : -STEP), e.clientX, e.clientY);
                    }
                },
                { passive: false }
            );
        }

        // Pinch-to-zoom
        var _pt1 = null,
            _pt2 = null,
            _ptzStart = 1;
        document.addEventListener(
            'touchstart',
            function (e) {
                if (e.touches.length === 2) {
                    _pt1 = e.touches[0];
                    _pt2 = e.touches[1];
                    _ptzStart = zoom;
                }
            },
            { passive: true }
        );
        document.addEventListener(
            'touchmove',
            function (e) {
                if (e.touches.length === 2 && _pt1 && _pt2) {
                    var d0 = Math.hypot(_pt1.clientX - _pt2.clientX, _pt1.clientY - _pt2.clientY);
                    var d1 = Math.hypot(
                        e.touches[0].clientX - e.touches[1].clientX,
                        e.touches[0].clientY - e.touches[1].clientY
                    );
                    setZoom(_ptzStart * (d1 / d0));
                }
            },
            { passive: true }
        );
        document.addEventListener(
            'touchend',
            function (e) {
                if (e.touches.length < 2) {
                    _pt1 = _pt2 = null;
                }
            },
            { passive: true }
        );

        $('input[name="final_width"],input[name="final_height"]').on('change keyup', function () {
            updateDimLabel();
            setTimeout(fit, 120);
        });
        setTimeout(updateDimLabel, 600);
        setTimeout(fit, 800);
        $(window).on('resize', function () {
            setTimeout(fit, 100);
        });
    });
})();

// ═══════════════════════════════════════════════════════════════
// 7. GRID + SNAP TOGGLE
// ═══════════════════════════════════════════════════════════════
(function () {
    var gridBtn = document.getElementById('gen-grid-btn');
    var snapBtn = document.getElementById('gen-snap-btn');
    var overlay = document.getElementById('gen-grid-overlay');
    var gridOn = false;

    if (gridBtn)
        gridBtn.addEventListener('click', function () {
            gridOn = !gridOn;
            gridBtn.classList.toggle('active', gridOn);
            if (overlay) overlay.classList.toggle('visible', gridOn);
        });
    if (snapBtn)
        snapBtn.addEventListener('click', function () {
            _snapEnabled = !_snapEnabled;
            snapBtn.classList.toggle('active', _snapEnabled);
            if (typeof openToast === 'function') openToast('Snap ' + (_snapEnabled ? 'ON' : 'OFF'), 'isSuccess', 1500);
        });
})();

// ═══════════════════════════════════════════════════════════════
// 8. PAN MODE BUTTON
// ═══════════════════════════════════════════════════════════════
(function () {
    var panBtn = document.getElementById('gen-pan-btn');
    if (!panBtn) return;
    panBtn.addEventListener('click', function () {
        window._genPanMode = !window._genPanMode;
        panBtn.classList.toggle('active', window._genPanMode);
        var vp = document.getElementById('canvas-viewport');
        if (vp) vp.style.cursor = window._genPanMode ? 'grab' : '';
    });
})();

// ═══════════════════════════════════════════════════════════════
// 9. KEYBOARD — nudge, undo, shortcuts, delete, tab-select
// ═══════════════════════════════════════════════════════════════
$(document).on('keydown', function (e) {
    var inInput = $(e.target).is('input,select,textarea');

    // Shortcuts overlay
    if (!inInput && e.key === '?') {
        var ov = document.getElementById('gen-shortcut-overlay');
        if (ov) ov.classList.toggle('show');
        return;
    }

    // Ctrl+Z undo
    if ((e.ctrlKey || e.metaKey) && e.key === 'z') {
        if (!inInput) {
            e.preventDefault();
            undoLast();
            return;
        }
    }

    // Grid / Snap hotkeys
    if (!inInput && e.key === 'g') {
        var gb = document.getElementById('gen-grid-btn');
        if (gb) gb.click();
        return;
    }
    if (!inInput && e.key === 's') {
        var sb = document.getElementById('gen-snap-btn');
        if (sb) sb.click();
        return;
    }

    // Zoom shortcuts
    if (!inInput) {
        if (e.key === '=' || e.key === '+') {
            e.preventDefault();
            $('#zoom-in').trigger('click');
            return;
        }
        if (e.key === '-' || e.key === '_') {
            e.preventDefault();
            $('#zoom-out').trigger('click');
            return;
        }
        if (e.key === '0') {
            e.preventDefault();
            if (window._genFit) window._genFit();
            return;
        }
    }

    // Arrow nudge for selected frame (step 3)
    if (genCurStep === 3 && !inInput && ['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].indexOf(e.key) >= 0) {
        if (_selectedFrameIdx === null) return;
        e.preventDefault();
        var step = e.ctrlKey || e.metaKey ? 100 : e.shiftKey ? 10 : 1;
        var idx = _selectedFrameIdx;
        undoPush(idx);
        if (e.key === 'ArrowLeft')
            setVal('picture-x-position-' + idx, maybeSnap(numVal('picture-x-position-' + idx) - step));
        if (e.key === 'ArrowRight')
            setVal('picture-x-position-' + idx, maybeSnap(numVal('picture-x-position-' + idx) + step));
        if (e.key === 'ArrowUp')
            setVal('picture-y-position-' + idx, maybeSnap(numVal('picture-y-position-' + idx) - step));
        if (e.key === 'ArrowDown')
            setVal('picture-y-position-' + idx, maybeSnap(numVal('picture-y-position-' + idx) + step));
        return;
    }

    // Delete selected frame
    if (genCurStep === 3 && !inInput && (e.key === 'Delete' || e.key === 'Backspace')) {
        if (_selectedFrameIdx !== null) {
            hideImage('picture-' + _selectedFrameIdx);
            return;
        }
    }

    // Tab = select next visible frame
    if (genCurStep === 3 && !inInput && e.key === 'Tab') {
        e.preventDefault();
        var visibleIdxs = [];
        document.querySelectorAll('.gen-frame-card:not(.hidden)').forEach(function (c) {
            var n = +(c.dataset.picture || 'picture-0').replace('picture-', '');
            if (!document.getElementById('picture-' + n).classList.contains('hidden')) visibleIdxs.push(n);
        });
        if (!visibleIdxs.length) return;
        var cur = _selectedFrameIdx !== null ? _selectedFrameIdx : -1;
        var ni = (visibleIdxs.indexOf(cur) + 1) % visibleIdxs.length;
        genSelectFrame(visibleIdxs[ni]);
        return;
    }

    // Arrow ↑/↓ on number/text inputs
    if (inInput && (e.key === 'ArrowUp' || e.key === 'ArrowDown')) {
        var inp = e.target;
        var v = inp.value;
        if (!/^-?\d*\.?\d+$/.test(v.trim())) return;
        e.preventDefault();
        var st2 = e.ctrlKey || e.metaKey ? 100 : e.shiftKey ? 10 : 1;
        var n = parseFloat(v) + (e.key === 'ArrowUp' ? 1 : -1) * st2;
        var mn = parseFloat(inp.getAttribute('min')),
            mx = parseFloat(inp.getAttribute('max'));
        if (!isNaN(mn)) n = Math.max(mn, n);
        if (!isNaN(mx)) n = Math.min(mx, n);
        inp.value = n;
        $(inp).trigger('change');
    }
});

// Scroll wheel on focused number inputs (native addEventListener for passive:false support)
document.addEventListener(
    'wheel',
    function (e) {
        var inp = e.target;
        if (!inp || inp.type !== 'number' || document.activeElement !== inp) return;
        e.preventDefault();
        var step = e.shiftKey ? 10 : 1;
        var n = (parseFloat(inp.value) || 0) + (e.deltaY < 0 ? step : -step);
        var mn = parseFloat(inp.getAttribute('min')),
            mx = parseFloat(inp.getAttribute('max'));
        if (!isNaN(mn)) n = Math.max(mn, n);
        if (!isNaN(mx)) n = Math.min(mx, n);
        inp.value = Math.round(n);
        $(inp).trigger('change');
    },
    { passive: false }
);

// ═══════════════════════════════════════════════════════════════
// 10. ALIGNMENT TOOLS
// ═══════════════════════════════════════════════════════════════
document.querySelectorAll('.g-align-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var action = btn.dataset.align;
        var cw = parseFloat($('input[name="final_width"]').val()) || 1500;
        var ch = parseFloat($('input[name="final_height"]').val()) || 1000;

        if (action === 'dist-h' || action === 'dist-v') {
            // Distribute all visible frames evenly
            var items = [];
            document.querySelectorAll('.gen-frame-card:not(.hidden)').forEach(function (c) {
                var n = +(c.dataset.picture || 'picture-0').replace('picture-', '');
                if (!document.getElementById('picture-' + n).classList.contains('hidden')) {
                    items.push({ idx: n, sz: resolveFrameSize(n) });
                }
            });
            if (items.length < 2) return;
            if (action === 'dist-h') {
                var totalW = items.reduce(function (a, i) {
                    return a + i.sz.w;
                }, 0);
                var gap = (cw - totalW) / (items.length + 1);
                var cx2 = gap;
                items.forEach(function (it) {
                    undoPush(it.idx);
                    setVal('picture-x-position-' + it.idx, Math.round(cx2));
                    cx2 += it.sz.w + gap;
                });
            } else {
                var totalH = items.reduce(function (a, i) {
                    return a + i.sz.h;
                }, 0);
                var gapY = (ch - totalH) / (items.length + 1);
                var cy2 = gapY;
                items.forEach(function (it) {
                    undoPush(it.idx);
                    setVal('picture-y-position-' + it.idx, Math.round(cy2));
                    cy2 += it.sz.h + gapY;
                });
            }
            showUndoToast('Distributed — Ctrl+Z to undo');
            return;
        }

        if (_selectedFrameIdx === null) {
            if (typeof openToast === 'function') openToast('Select a frame first', 'isWarning', 2000);
            return;
        }
        var idx = _selectedFrameIdx;
        var sz = resolveFrameSize(idx);
        undoPush(idx);
        if (action === 'left') setVal('picture-x-position-' + idx, 0);
        if (action === 'right') setVal('picture-x-position-' + idx, Math.round(cw - sz.w));
        if (action === 'center-h') setVal('picture-x-position-' + idx, Math.round((cw - sz.w) / 2));
        if (action === 'top') setVal('picture-y-position-' + idx, 0);
        if (action === 'bottom') setVal('picture-y-position-' + idx, Math.round(ch - sz.h));
        if (action === 'center-v') setVal('picture-y-position-' + idx, Math.round((ch - sz.h) / 2));
        showUndoToast('Aligned — Ctrl+Z to undo');
    });
});

// ═══════════════════════════════════════════════════════════════
// 11. SPINNERS — hold-to-repeat
// ═══════════════════════════════════════════════════════════════
function _spin(inp, dir, ev) {
    var step = ev && (ev.ctrlKey || ev.metaKey) ? 100 : ev && ev.shiftKey ? 10 : 1;
    var v = inp.value;
    if (!/^-?\d*\.?\d+$/.test((v || '').trim())) return;
    var n = parseFloat(v) + dir * step;
    var mn = parseFloat(inp.getAttribute('min')),
        mx = parseFloat(inp.getAttribute('max'));
    if (!isNaN(mn)) n = Math.max(mn, n);
    if (!isNaN(mx)) n = Math.min(mx, n);
    inp.value = n;
    $(inp).trigger('change');
}

function initSpinners() {
    document.querySelectorAll('input[type="number"]').forEach(function (inp) {
        if (inp.closest('.g-spin')) return;
        var wrap = document.createElement('div');
        wrap.className = 'g-spin';
        inp.parentNode.insertBefore(wrap, inp);
        wrap.appendChild(document.createTextNode('')); // placeholder

        function makeBtn(dir, label) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'g-spin-btn';
            btn.innerHTML = label;
            btn.title = (dir < 0 ? 'Decrease' : 'Increase') + ' (Shift ×10, Ctrl ×100)';
            var holdTimer = null,
                holdInterval = null;
            btn.addEventListener('mousedown', function (ev) {
                ev.preventDefault();
                btn.classList.add('holding');
                _spin(inp, dir, ev);
                holdTimer = setTimeout(function () {
                    holdInterval = setInterval(function () {
                        _spin(inp, dir, ev);
                    }, 60);
                }, 400);
            });
            function stopHold() {
                btn.classList.remove('holding');
                clearTimeout(holdTimer);
                clearInterval(holdInterval);
            }
            btn.addEventListener('mouseup', stopHold);
            btn.addEventListener('mouseleave', stopHold);
            btn.addEventListener('touchend', stopHold);
            return btn;
        }
        var minus = makeBtn(-1, '&minus;');
        var plus = makeBtn(1, '+');
        wrap.appendChild(minus);
        wrap.appendChild(inp);
        wrap.appendChild(plus);
    });
}

// ═══════════════════════════════════════════════════════════════
// 12. SHORTCUTS PANEL
// ═══════════════════════════════════════════════════════════════
$(function () {
    var scBtn = document.getElementById('gen-shortcuts-btn');
    if (scBtn)
        scBtn.addEventListener('click', function () {
            var ov = document.getElementById('gen-shortcut-overlay');
            if (ov) ov.classList.toggle('show');
        });
    // Also close on overlay backdrop click
    var ov2 = document.getElementById('gen-shortcut-overlay');
    if (ov2)
        ov2.addEventListener('click', function (e) {
            if (e.target === ov2) ov2.classList.remove('show');
        });
});

// ═══════════════════════════════════════════════════════════════
// 13. CANVAS UNDO BUTTON IN TOOLBAR
// ═══════════════════════════════════════════════════════════════
$(function () {
    var undoToolBtn = document.getElementById('gen-undo-btn');
    if (undoToolBtn) undoToolBtn.addEventListener('click', undoLast);
});

// ═══════════════════════════════════════════════════════════════
// 14. AUTO-DETECT BG SIZE
// ═══════════════════════════════════════════════════════════════
function genDetectBgSize() {
    var bgPath = $('input[name="generator-background"]').val();
    if (!bgPath) {
        if (typeof openToast === 'function') openToast('Please select a background image first', 'isWarning', 3000);
        return;
    }
    var tmp = new Image();
    tmp.onload = function () {
        var w = tmp.naturalWidth,
            h = tmp.naturalHeight;
        $('input[name="final_width"]').val(w).trigger('change');
        $('input[name="final_height"]').val(h).trigger('change');
        if (typeof openToast === 'function') openToast('Canvas set to ' + w + ' × ' + h + ' px', 'isSuccess', 2500);
        setTimeout(function () {
            if (window._genFit) window._genFit();
        }, 200);
    };
    tmp.onerror = function () {
        if (typeof openToast === 'function') openToast('Could not read image dimensions', 'isError', 3000);
    };
    var src = typeof toPublicUrl === 'function' ? toPublicUrl(bgPath) : bgPath;
    tmp.src = src + (src.indexOf('?') >= 0 ? '&' : '?') + '_t=' + Date.now();
}
$('#gen-detect-btn1,#gen-detect-btn2').on('click', genDetectBgSize);

// ═══════════════════════════════════════════════════════════════
// 15. CANVAS SIZE PRESETS
// ═══════════════════════════════════════════════════════════════
document.querySelectorAll('.g-preset').forEach(function (btn) {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.g-preset').forEach(function (b) {
            b.classList.remove('active');
        });
        btn.classList.add('active');
        $('input[name="final_width"]').val(btn.dataset.w).trigger('change');
        $('input[name="final_height"]').val(btn.dataset.h).trigger('change');
    });
});

// ═══════════════════════════════════════════════════════════════
// 16. COUPLE MODE
// ═══════════════════════════════════════════════════════════════
var _coupleOriginalWidth = null;
var _coupleActive = false;

(function () {
    var tog = document.getElementById('gen-couple-toggle');
    if (!tog) return;
    tog.addEventListener('change', function () {
        var on = this.checked;
        _coupleActive = on;
        var div = document.getElementById('gen-couple-divider');
        if (div) div.style.display = on ? 'block' : 'none';
        if (on) {
            _coupleOriginalWidth = parseFloat($('input[name="final_width"]').val()) || 1500;
            if (typeof openToast === 'function') openToast('Couple mode ON — photos reused across both strips', 'isSuccess', 2500);
        } else {
            _coupleOriginalWidth = null;
            if (typeof openToast === 'function') openToast('Couple mode OFF', 'isSuccess', 2000);
        }
        if (typeof changeGeneralSetting === 'function') changeGeneralSetting();
    });
})();

// ═══════════════════════════════════════════════════════════════
// 17. LIVE JSON PREVIEW
// ═══════════════════════════════════════════════════════════════
function updateConfigDisplay() {
    var cfg = {
        width: $('input[name="final_width"]').val(),
        height: $('input[name="final_height"]').val(),
        text_custom_style: $('input[name="text_enabled"][data-trigger="general"]').is(':checked'),
        text_font_size: $('input[name="text_font_size"]').val(),
        text_rotation: $('input[name="text_rotation"]').val(),
        text_locationx: $('input[name="text_location_x"]').val(),
        text_locationy: $('input[name="text_location_y"]').val(),
        text_font_color: $('input[name="text_font_color"]').val(),
        text_font: $('input[name="text_font_family"]').val(),
        text_line1: $('input[name="text_line_1"]').val(),
        text_line2: $('input[name="text_line_2"]').val(),
        text_line3: $('input[name="text_line_3"]').val(),
        text_linespace: $('input[name="text_line_space"]').val(),
        apply_frame: $('select[name="apply_frame"]').val(),
        frame: $('input[name="generator-frame"]').val(),
        background: $('input[name="generator-background"]').val(),
        background_color: $('input[name="background_color"]').val(),
        background_on_top: $('input[name="generator-background_on_top"][data-trigger="general"]').is(':checked'),
        couple_mode: document.getElementById('gen-couple-toggle') ? document.getElementById('gen-couple-toggle').checked : false,
        prebuilt_double: document.getElementById('gen-couple-toggle') ? document.getElementById('gen-couple-toggle').checked : false,
        placeholder: $('input[name="enable_placeholder_image"][data-trigger="general"]').is(':checked'),
        placeholderpath: $('input[name="placeholder_image"]').val(),
        placeholderposition: $('input[name="placeholder_image_position"]').val(),
        layout: []
    };
    $('div.image_layout').not('.hidden').each(function () {
        var row = [];
        $(this)
            .find('input[data-prop]')
            .each(function () {
                var v = $(this).val();
                if ($(this).attr('type') === 'checkbox') v = $(this).is(':checked') && cfg.apply_frame === 'always';
                row.push(v);
            });
        cfg.layout.push(row);
    });
    // Mirror couple layout in the preview so the JSON panel shows the real saved structure.
    // Use expression evaluation (not DOM size) to avoid zoom-factor inflation.
    if (cfg.couple_mode && cfg.layout.length > 0) {
        var previewOrigW = parseFloat($('input[name="final_width"]').val()) || 0;
        var previewH = parseFloat($('input[name="final_height"]').val()) || 1;
        cfg.width = String(previewOrigW * 2);
        var previewHalf = cfg.layout.length;
        function evalPreviewExpr(expr) {
            var s = String(expr == null ? '0' : expr).trim()
                .replace(/\bx\b/g, String(previewOrigW))
                .replace(/\by\b/g, String(previewH));
            try { return calculate(tokenize(s)); } catch (e2) { return 0; }
        }
        for (var pci = 0; pci < previewHalf; pci++) {
            var prow = cfg.layout[pci];
            cfg.layout[pci] = [
                String(Math.round(evalPreviewExpr(prow[0]))),
                String(Math.round(evalPreviewExpr(prow[1]))),
                String(Math.round(evalPreviewExpr(prow[2]))),
                String(Math.round(evalPreviewExpr(prow[3]))),
                prow[4],
                prow[5]
            ];
        }
        for (var pri = 0; pri < previewHalf; pri++) {
            var pmirror = cfg.layout[pri].slice();
            pmirror[0] = String(parseInt(cfg.layout[pri][0], 10) + Math.round(previewOrigW));
            cfg.layout.push(pmirror);
        }
    }
    var box = document.getElementById('config-json-content');
    if (box) box.textContent = JSON.stringify(cfg, null, 2);
}
// Debounce config display updates
var _cfgTimer = null;
$(document).on('change keyup', 'input,select', function () {
    clearTimeout(_cfgTimer);
    _cfgTimer = setTimeout(updateConfigDisplay, 150);
});
$(function () {
    setTimeout(updateConfigDisplay, 500);
});

function genCopyConfig() {
    var c = document.getElementById('config-json-content');
    if (!c) return;
    navigator.clipboard.writeText(c.innerText).then(function () {
        if (typeof openToast === 'function') openToast('JSON copied!', 'isSuccess', 2000);
    });
}

// ═══════════════════════════════════════════════════════════════
// 18. FIX DUPLICATE progress bar & init
// ═══════════════════════════════════════════════════════════════
$(function () {
    // Remove the duplicate progress bar that may have been inserted
    var all = document.querySelectorAll('[id="gen-progress-fill"]');
    if (all.length > 1) {
        all[all.length - 1].closest('.gen-progress-bar,.gen-progress') && all[all.length - 1].parentNode.remove();
    }
    genGoTo(1);
    setTimeout(initSpinners, 400);
    setTimeout(function () {
        if (window._genFit) window._genFit();
    }, 750);
});
