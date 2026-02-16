"use strict";

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/* eslint n/no-unsupported-features/node-builtins: "off" */
/* globals remoteBuzzerClient csrf */
var photoboothTools = function () {
  // vars
  var notificationTimeout = config.ui.notification_timeout * 1000,
    api = {};
  api.translations = null;
  api.sounds = null;
  api.isPrinting = false;
  var addCsrfToUrl = function addCsrfToUrl(url) {
    if (typeof csrf === 'undefined') {
      return url;
    }
    var u = new URL(url, window.location.origin);
    u.searchParams.set(csrf.key, csrf.token);
    return u.toString();
  };

  // Attach CSRF to all jQuery AJAX calls
  if (typeof $ !== 'undefined' && typeof csrf !== 'undefined') {
    $.ajaxSetup({
      data: _defineProperty({}, csrf.key, csrf.token)
    });
  }
  api.initialize = /*#__PURE__*/_asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee() {
    var resultTranslations, resultSounds;
    return _regenerator().w(function (_context) {
      while (1) switch (_context.n) {
        case 0:
          _context.n = 1;
          return fetch(addCsrfToUrl(environment.publicFolders.api + '/translations.php'), {
            cache: 'no-store'
          });
        case 1:
          resultTranslations = _context.v;
          _context.n = 2;
          return resultTranslations.json();
        case 2:
          this.translations = _context.v;
          _context.n = 3;
          return fetch(addCsrfToUrl(environment.publicFolders.api + '/sounds.php'), {
            cache: 'no-store'
          });
        case 3:
          resultSounds = _context.v;
          _context.n = 4;
          return resultSounds.json();
        case 4:
          this.sounds = _context.v;
          this.registerEvents();
        case 5:
          return _context.a(2);
      }
    }, _callee, this);
  }));
  api.registerEvents = function () {
    document.querySelectorAll('[data-command]').forEach(function (button) {
      button.addEventListener('click', function (event) {
        var target = event.currentTarget;
        var data = target.dataset;

        // Check if command is in list of supported events
        // This can be dropped after all actions are migrated
        if (!['remotebuzzer', 'reload'].includes(data.command)) {
          return;
        }
        event.preventDefault();
        event.stopImmediatePropagation();
        var name = 'photobooth.' + data.command;
        var detail = {
          trigger: target,
          data: Object.assign({}, data)
        };
        api.console.log('dispatch: ' + name);
        var customEvent = new CustomEvent(name, {
          detail: detail
        });
        document.dispatchEvent(customEvent);
      });
    });
    document.addEventListener('photobooth.remotebuzzer', function (event) {
      api.getRequest(window.location.protocol + '//' + config.remotebuzzer.serverip + ':' + config.remotebuzzer.port + '/commands/' + event.detail.data.action);
    });
    document.addEventListener('photobooth.reload', function () {
      api.reloadPage();
    });
  };
  api.console = {
    log: function log() {
      for (var _len = arguments.length, content = new Array(_len), _key = 0; _key < _len; _key++) {
        content[_key] = arguments[_key];
      }
      console.log('[', new Date().toISOString(), ']: ' + JSON.stringify(content));
    },
    logDev: function logDev() {
      if (config.dev.loglevel > 0) {
        for (var _len2 = arguments.length, content = new Array(_len2), _key2 = 0; _key2 < _len2; _key2++) {
          content[_key2] = arguments[_key2];
        }
        console.log('[', new Date().toISOString(), ']: ' + JSON.stringify(content));
      }
    }
  };
  api.getTranslation = function (key) {
    if (!this.translations[key]) {
      this.console.logDev('translation key not found: ' + key);
      return key;
    }
    return this.translations[key];
  };
  api.getSound = function (key) {
    if (!this.sounds[key]) {
      this.console.logDev('sound key not found: ' + key);
      return null;
    }
    return this.sounds[key];
  };
  api.overlay = {
    element: null,
    show: function show(message) {
      var type = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'default';
      if (api.overlay.element === null) {
        var element = document.createElement('div');
        element.classList.add('overlay');
        document.body.append(element);
        api.overlay.element = element;
      }
      api.overlay.element.innerHTML = message;
      api.overlay.element.dataset.type = type;
    },
    showWaiting: function showWaiting(message) {
      api.overlay.show('<div><i class="' + config.icons.spinner + '"></i></div><div>' + message + '</div>', 'progress');
    },
    showSuccess: function showSuccess(message) {
      api.overlay.show(message, 'success');
    },
    showWarning: function showWarning(message) {
      api.overlay.show(message, 'warning');
    },
    showError: function showError(message) {
      api.overlay.show(message, 'error');
    },
    close: function close() {
      if (api.overlay.element !== null) {
        api.overlay.element.remove();
        api.overlay.element = null;
      }
    }
  };
  api.button = {
    create: function create(label, iconClass) {
      var severity = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 'default';
      var prefix = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : '';
      var button = document.createElement('button');
      button.classList.add(prefix + 'button');
      button.classList.add('rotaryfocus');
      button.dataset.severity = severity;
      var iconWrap = document.createElement('span');
      iconWrap.classList.add(prefix + 'button--icon');
      var icon = document.createElement('i');
      icon.classList = iconClass;
      iconWrap.appendChild(icon);
      button.appendChild(iconWrap);
      if (label === '') {
        return button;
      }
      var labelWrap = document.createElement('span');
      labelWrap.classList.add(prefix + 'button--label');
      labelWrap.innerHTML = api.getTranslation(label);
      button.appendChild(labelWrap);
      return button;
    }
  };
  api.modal = {
    element: null,
    open: function open() {
      var type = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'default';
      if (api.modal.element === null) {
        var element = document.createElement('div');
        element.dataset.type = type;
        element.classList.add('modal');
        element.classList.add('rotarygroup');
        var inner = document.createElement('div');
        inner.classList.add('modal-inner');
        element.appendChild(inner);
        var body = document.createElement('div');
        body.classList.add('modal-body');
        inner.appendChild(body);
        var buttonbar = document.createElement('div');
        buttonbar.classList.add('modal-buttonbar');
        var closeButton = api.button.create('close', 'fa fa-times', 'default', 'modal-');
        closeButton.addEventListener('click', function () {
          return api.modal.close();
        });
        buttonbar.appendChild(closeButton);
        inner.appendChild(buttonbar);
        document.body.append(element);
        api.modal.element = element;
      }
    },
    close: function close() {
      if (api.modal.element !== null) {
        api.modal.element.remove();
        api.modal.element = null;
      }
    }
  };
  api.confirm = /*#__PURE__*/function () {
    var _ref2 = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee2(confirmationText) {
      return _regenerator().w(function (_context2) {
        while (1) switch (_context2.n) {
          case 0:
            return _context2.a(2, new Promise(function (resolve) {
              var element = document.createElement('dialog');
              element.classList.add('dialog');
              element.classList.add('rotarygroup');
              var message = document.createElement('div');
              message.classList.add('dialog-message');
              message.textContent = confirmationText;
              element.appendChild(message);
              var buttonbar = document.createElement('div');
              buttonbar.classList.add('dialog-buttonbar');
              element.appendChild(buttonbar);

              // confirm
              var confirmButton = api.button.create('confirm', 'fa fa-check', 'default', 'dialog-');
              confirmButton.addEventListener('click', function () {
                element.close(true);
                element.remove();
                resolve(true);
              });
              buttonbar.appendChild(confirmButton);

              // cancel
              var cancelButton = api.button.create('cancel', 'fa fa-times', 'default', 'dialog-');
              cancelButton.addEventListener('click', function () {
                element.close(false);
                element.remove();
                resolve(false);
              });
              buttonbar.appendChild(cancelButton);
              element.addEventListener('cancel', function () {
                element.close(false);
                element.remove();
                resolve(false);
              });
              document.body.append(element);
              element.showModal();
            }));
        }
      }, _callee2);
    }));
    return function (_x) {
      return _ref2.apply(this, arguments);
    };
  }();
  api.askCopies = /*#__PURE__*/_asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee3() {
    return _regenerator().w(function (_context3) {
      while (1) switch (_context3.n) {
        case 0:
          return _context3.a(2, new Promise(function (resolve) {
            var element = document.createElement('dialog');
            element.classList.add('dialog');
            element.classList.add('rotarygroup');
            var message = document.createElement('div');
            message.classList.add('dialog-message');
            message.textContent = api.getTranslation('print:choose_copies');
            element.appendChild(message);
            var inputSection = document.createElement('div');
            inputSection.classList.add('buttonbar--print-copies');
            var minusButton = api.button.create('', 'fa fa-minus', 'default', '');
            var plusButton = api.button.create('', 'fa fa-plus', 'default', '');
            var inputText = document.createElement('input');
            inputText.classList.add('form-input-copies');
            inputText.value = '1';
            minusButton.addEventListener('click', function () {
              var oldValue = parseInt(inputText.value, 10);
              inputText.value = String(Math.max(1, oldValue - 1));
            });
            plusButton.addEventListener('click', function () {
              var oldValue = parseInt(inputText.value, 10);
              inputText.value = String(Math.min(config.print.max_multi, oldValue + 1));
            });
            inputSection.append(minusButton);
            inputSection.append(inputText);
            inputSection.append(plusButton);
            element.append(inputSection);
            var buttonbar = document.createElement('div');
            buttonbar.classList.add('dialog-buttonbar');
            element.appendChild(buttonbar);

            // confirm
            var confirmButton = api.button.create('print', 'fa fa-check', 'default', 'dialog-');
            confirmButton.addEventListener('click', function () {
              element.close(true);
              element.remove();
              resolve(inputText.value);
            });
            buttonbar.appendChild(confirmButton);

            // cancel
            var cancelButton = api.button.create('cancel', 'fa fa-times', 'default', 'dialog-');
            cancelButton.addEventListener('click', function () {
              element.close(false);
              element.remove();
              resolve(false);
            });
            buttonbar.appendChild(cancelButton);
            element.addEventListener('cancel', function () {
              element.close(false);
              element.remove();
              resolve(false);
            });
            document.body.append(element);
            element.showModal();
          }));
      }
    }, _callee3);
  }));
  api.reloadPage = function () {
    var url = new URL(window.location.href);
    url.searchParams.set('refresh', '1');
    window.location.href = url.toString();
  };
  api.getRequest = function (url) {
    api.console.log('Sending GET request to: ' + url);
    fetch(new Request(addCsrfToUrl(url)), {
      method: 'GET',
      mode: 'cors',
      credentials: 'same-origin'
    }).then(function (response) {
      if (response.status === 200) {
        return response.text();
      } else if (response.status === 404) {
        throw new Error('No records found');
      } else {
        throw new Error('Unhandled request status: ' + response.status);
      }
    }).then(function (data) {
      api.console.log(data);
    })["catch"](function (error) {
      api.console.log('Error occurred: ' + error.message);
    });
  };
  api.isVideoFile = function (filename) {
    var extension = api.getFileExtension(filename);
    return extension === 'mp4' || extension === 'gif';
  };
  api.getFileExtension = function (filename) {
    var parts = filename.split('.');
    return parts[parts.length - 1];
  };
  api.resetPrintErrorMessage = function (cb, to) {
    setTimeout(function () {
      api.overlay.close();
      cb();
      api.isPrinting = false;
    }, to);
  };
  api.printImage = function (imageSrc, copies, cb) {
    if (api.isVideoFile(imageSrc)) {
      api.console.log('ERROR: An error occurred: attempt to print non printable file.');
      api.overlay.showError(api.getTranslation('no_printing'));
      setTimeout(function () {
        return api.overlay.close();
      }, notificationTimeout);
    } else if (api.isPrinting) {
      api.console.log('Printing in progress: ' + api.isPrinting);
    } else {
      api.overlay.show(api.getTranslation('printing'));
      api.isPrinting = true;
      if (typeof remoteBuzzerClient !== 'undefined') {
        remoteBuzzerClient.inProgress('print');
      }
      $.ajax({
        method: 'GET',
        url: environment.publicFolders.api + '/print.php',
        data: _defineProperty({
          filename: imageSrc,
          copies: copies
        }, csrf.key, csrf.token),
        success: function success(data) {
          api.console.log('Picture processed: ', data);
          if (data.status == 'locking') {
            api.overlay.showWarning(config.print.locking_msg + ' (' + api.getTranslation('printed') + ' ' + data.count + ')');
            api.resetPrintErrorMessage(cb, config.print.time);
            $('.print-unlock-button').removeClass('hidden');
          } else if (data.status == 'queued') {
            api.overlay.showWarning(api.getTranslation('print_queued'));
            api.resetPrintErrorMessage(cb, 2000);
          } else if (data.status == 'error') {
            if (data.error) {
              api.console.log('ERROR: An error occurred: ', data.error);
              api.overlay.showError(data.error);
            } else {
              api.console.log('ERROR: An error occurred on print.');
              api.overlay.showError(api.getTranslation('error'));
            }
            api.resetPrintErrorMessage(cb, config.print.time);
          } else {
            setTimeout(function () {
              api.overlay.close();
              cb();
              api.isPrinting = false;
            }, config.print.time);
          }
        },
        error: function error(jqXHR, textStatus) {
          api.console.log('ERROR: Print failed: ', textStatus);
          api.overlay.showError(api.getTranslation('error'));
          api.resetPrintErrorMessage(cb, notificationTimeout);
        }
      });
    }
  };
  $(document).on('keyup', function (ev) {
    if (config.reload.key && parseInt(config.reload.key, 10) === ev.keyCode) {
      api.reloadPage();
    }
  });
  return api;
}();

// Init on domready
$(function () {
  photoboothTools.initialize().then(function () {
    photoboothTools.console.log('PhotoboothTools: initialized');
    photoboothTools.console.log('Loglevel: ' + config.dev.loglevel);
  });
});

/* globals photoboothTools csrf */
$(function () {
  initDirtyTracking();

  // adminRangeInput
  $(document).on('input', '.adminRangeInput', function () {
    document.querySelector('#' + this.name.replace('[', '\\[').replace(']', '\\]') + '-value span').innerHTML = this.value;
  });

  // Localization of toggle button text
  $('.adminCheckbox').on('click', function () {
    if ($(this).find('input').is(':checked')) {
      $('.adminCheckbox-true', this).removeClass('hidden');
      $('.adminCheckbox-false', this).addClass('hidden');
    } else {
      $('.adminCheckbox-true', this).addClass('hidden');
      $('.adminCheckbox-false', this).removeClass('hidden');
    }
  });
});

// eslint-disable-next-line no-unused-vars
var shellCommand = function shellCommand($mode) {
  var $filename = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
  var command = {
    mode: $mode,
    filename: $filename
  };
  if (typeof csrf !== 'undefined') {
    command[csrf.key] = csrf.token;
  }
  photoboothTools.console.log('Run' + $mode);
  jQuery.post('../api/shellCommand.php', command).done(function (result) {
    photoboothTools.console.log($mode, 'result: ', result);
  }).fail(function (xhr, status, result) {
    photoboothTools.console.log($mode, 'result: ', result);
  });
};
function initDirtyTracking() {
  var $fields = $('.adminSection').find('input, select, textarea').not('[type="hidden"]');
  $fields.each(function () {
    var $el = $(this);
    $el.data('initial', readFieldValue($el));
  });
  $(document).on('change input', '.adminSection input, .adminSection select, .adminSection textarea', function () {
    updateDirtyState($(this));
  });
  $(document).on('click', '.adminSettingCard-revert', function (e) {
    e.preventDefault();
    var $card = $(this).closest('.adminSettingCard');
    revertCard($card);
  });
}
function readFieldValue($el) {
  var el = $el[0];
  if (el.tagName === 'SELECT' && el.multiple) {
    return ($el.val() || []).slice().sort().join('|');
  }
  if (el.type === 'checkbox') {
    return $el.is(':checked') ? '1' : '0';
  }
  return $el.val();
}
function updateDirtyState($el) {
  var initial = $el.data('initial');
  var current = readFieldValue($el);
  var isDirty = initial !== current;
  var $card = $el.closest('.adminSettingCard');
  if ($card.length === 0) {
    return;
  }
  if (isDirty) {
    $card.addClass('ring-2 ring-indigo-200 shadow-indigo-200');
    $el.data('dirty', true);
    ensureRevertButton($card);
  } else {
    $el.data('dirty', false);
    if (!$card.find('input,select,textarea').filter(function () {
      return $(this).data('dirty');
    }).length) {
      $card.removeClass('ring-2 ring-indigo-200 shadow-indigo-200');
      removeRevertButton($card);
    }
  }
}
function ensureRevertButton($card) {
  if ($card.find('.adminSettingCard-revert').length) {
    return;
  }
  var btn = $('<button type="button" class="adminSettingCard-revert h-7 w-7 absolute right-2 top-2 text-xs font-semibold text-amber-700 border border-amber-400 rounded-full bg-amber-50 hover:bg-amber-100" title="Revert">' + '<i class="fa fa-undo"></i>' + '</button>');
  $card.append(btn);
}
function removeRevertButton($card) {
  $card.find('.adminSettingCard-revert').remove();
}
function revertCard($card) {
  $card.find('input,select,textarea').each(function () {
    var $el = $(this);
    var initial = $el.data('initial');
    restoreFieldValue($el, initial);
    $el.data('dirty', false);
  });
  $card.removeClass('ring-2 ring-indigo-400 shadow-indigo-200');
  removeRevertButton($card);
}
function restoreFieldValue($el, value) {
  var el = $el[0];
  if (el.tagName === 'SELECT' && el.multiple) {
    var list = (value || '').split('|').filter(function (v) {
      return v !== '';
    });
    $el.val(list);
  } else if (el.type === 'checkbox') {
    $el.prop('checked', value === '1');
  } else {
    $el.val(value);
  }
  $el.trigger('change');

  // Keep range labels in sync after revert
  if ($el.hasClass('adminRangeInput')) {
    var labelId = '#' + el.name.replace('[', '\\[').replace(']', '\\]') + '-value span';
    var labelEl = document.querySelector(labelId);
    if (labelEl) {
      labelEl.innerHTML = $el.val();
    }
  }
}

/* eslint n/no-unsupported-features/node-builtins: "off" */
/* globals photoboothTools shellCommand csrf */
$(function () {
  // Highlight save button on form changes
  var $saveButton = $('#save-admin-btn');
  var initialSerialized = $('form').serialize();
  $(document).on('change input', 'form :input', function () {
    var currentSerialized = $('form').serialize();
    if (currentSerialized !== initialSerialized) {
      $saveButton.addClass('isDirty');
    } else {
      $saveButton.removeClass('isDirty');
    }
  });
  $('#reset-btn').on('click', function (e) {
    e.preventDefault();
    var msg = photoboothTools.getTranslation('really_delete');
    var really = confirm(msg);
    var elem = $(this);
    elem.addClass('saving');
    if (really) {
      // show loader
      $('.pageLoader').addClass('isActive');
      $('.pageLoader').find('label').html(photoboothTools.getTranslation('saving'));
      var data = new FormData(document.querySelector('form'));
      data.append('type', 'reset');
      if (typeof csrf !== 'undefined') {
        data.append(csrf.key, csrf.token);
      }
      fetch('../api/admin.php', {
        method: 'POST',
        body: data
      }).then(function (response) {
        return response.json();
      }).then(function (data) {
        if (data.status === 'success') {
          window.location.reload();
        } else {
          photoboothTools.console.logDev(data.message);
          window.location.reload();
        }
      })["catch"](function (error) {
        photoboothTools.console.logDev('Error:', error);
      });
    } else {
      elem.removeClass('saving');
    }
  });
  $('#save-admin-btn').on('click', function (e) {
    e.preventDefault();

    // show loader
    $('.pageLoader').addClass('isActive');
    $('.pageLoader').find('label').html(photoboothTools.getTranslation('saving'));
    var data = new FormData(document.querySelector('form'));
    data.append('type', 'config');
    if (typeof csrf !== 'undefined') {
      data.append(csrf.key, csrf.token);
    }
    fetch('../api/admin.php', {
      method: 'POST',
      body: data
    }).then(function (response) {
      return response.json();
    }).then(function (data) {
      if (data.status === 'success') {
        window.location.reload();
      } else {
        photoboothTools.console.logDev(data.message);
        window.location.reload();
      }
    })["catch"](function (error) {
      photoboothTools.console.logDev('Error:', error);
    });
  });
  $('#screensaver-preview-btn').on('click', function (e) {
    e.preventDefault();
    window.open('../?screensaverPreview=1', '_blank');
    return false;
  });
  $('#layout-generator').on('click', function (ev) {
    ev.preventDefault();
    window.open('../admin/generator');
    return false;
  });
  $('#test-connection').on('click', function (e) {
    e.preventDefault();
    var elem = $(this);

    // show loader
    $('.pageLoader').addClass('isActive');
    $('.pageLoader').find('label').html(photoboothTools.getTranslation('checking'));
    $.ajax({
      url: '../api/testFtpConnection.php',
      dataType: 'json',
      data: function () {
        var formData = $('form').serializeArray();
        if (typeof csrf !== 'undefined') {
          formData.push({
            name: csrf.key,
            value: csrf.token
          });
        }
        return formData;
      }(),
      type: 'post',
      success: function success(resp) {
        photoboothTools.console.log('resp', resp);
        resp.missing.forEach(function (el) {
          photoboothTools.console.log(el);
          $('#ftp\\:' + el).addClass('required');
        });
        alert(photoboothTools.getTranslation(resp.message));
      },
      error: function error(jqXHR) {
        photoboothTools.console.log('Error checking FTP connection: ', jqXHR.responseText);
      },
      complete: function complete(jqXHR, textStatus) {
        var status = jqXHR.status;
        var classes = 'isActive isSuccess';
        var findClasses = '.success span';
        if (status != 200 || jqXHR.responseJSON.response != 'success' || textStatus != 'success') {
          classes = 'isActive isError';
          findClasses = '.error span';
        }
        $('.pageLoader').removeClass('isActive');
        $('.adminToast').addClass(classes);
        var msg = elem.find(findClasses).html();
        $('.adminToast').find('.headline').html(msg);
        setTimeout(function () {
          $('.adminToast').removeClass('isActive');
        }, 2000);
      }
    });
  });
  $('#diskusage-btn').on('click', function (e) {
    e.preventDefault();
    location.assign('../admin/diskusage');
    return false;
  });
  $('#databaserebuild-btn').on('click', function (e) {
    e.preventDefault();
    var elem = $(this);

    // show loader
    $('.pageLoader').addClass('isActive');
    $('.pageLoader').find('label').html(photoboothTools.getTranslation('busy'));
    $.ajax({
      url: '../api/rebuildImageDB.php',
      data: _defineProperty({}, csrf.key, csrf.token),
      // eslint-disable-next-line no-unused-vars
      success: function success(resp) {
        $('.pageLoader').removeClass('isActive');
        $('.adminToast').addClass('isActive isSuccess');
        var msg = elem.find('.success span').html();
        $('.adminToast').find('.headline').html(msg);
        setTimeout(function () {
          $('.adminToast').removeClass('isActive');
        }, 3000);
      }
    });
  });
  $('#checkversion-btn').on('click', function (ev) {
    ev.preventDefault();
    var elem = $(this);

    // show loader
    $('.pageLoader').addClass('isActive');
    $('.pageLoader').find('label').html(photoboothTools.getTranslation('checking'));
    $.ajax({
      url: '../api/checkVersion.php',
      method: 'GET',
      data: _defineProperty({}, csrf.key, csrf.token),
      success: function success(data) {
        $('#checkVersion').empty();
        photoboothTools.console.log('data', data);
        if (!data.updateAvailable) {
          $('#current_version_text').text(photoboothTools.getTranslation('using_latest_version'));
        } else if (/^[0-9]+\.[0-9]+\.[0-9]+$/.test(data.availableVersion)) {
          $('#current_version_text').text(photoboothTools.getTranslation('current_version'));
          $('#current_version').text(data.currentVersion);
          $('#available_version_text').text(photoboothTools.getTranslation('available_version'));
          $('#available_version').text(data.availableVersion);
        } else {
          $('#current_version_text').text(photoboothTools.getTranslation('test_update_available'));
        }
        $('.pageLoader').removeClass('isActive');
        $('.adminToast').addClass('isActive isSuccess');
        var msg = elem.find('.success span').html();
        $('.adminToast').find('.headline').html(msg);
        setTimeout(function () {
          $('.adminToast').removeClass('isActive');
        }, 2000);
      },
      error: function error(jqXHR) {
        photoboothTools.console.log('Error checking Version: ', jqXHR.responseText);
        $('.pageLoader').removeClass('isActive');
        $('.adminToast').addClass('isActive isError');
        var msg = elem.find('.error span').html();
        $('.adminToast').find('.headline').html(msg);
        setTimeout(function () {
          $('.adminToast').removeClass('isActive');
        }, 2000);
      }
    });
  });
  $('#reset-print-lock-btn').on('click', function (e) {
    e.preventDefault();
    var elem = $(this);

    // show loader
    $('.pageLoader').addClass('isActive');
    $('.pageLoader').find('label').html(photoboothTools.getTranslation('busy'));
    $.ajax({
      method: 'GET',
      url: '../api/printDB.php',
      data: _defineProperty({
        action: 'unlockPrint'
      }, csrf.key, csrf.token),
      success: function success(data) {
        $('.pageLoader').removeClass('isActive');
        if (data.success) {
          $('.adminToast').addClass('isActive isSuccess');
          var msg = elem.find('.success span').html();
          $('.adminToast').find('.headline').html(msg);
        } else {
          $('.adminToast').addClass('isActive isError');
          var _msg = elem.find('.error span').html();
          $('.adminToast').find('.headline').html(_msg);
        }
        setTimeout(function () {
          $('.adminToast').removeClass('isActive');
        }, 2000);
      }
    });
  });
  $('#soundtest-btn').on('click', function (ev) {
    ev.preventDefault();
    var audioElement = document.getElementById('testaudio');
    if (audioElement === null) {
      audioElement = document.createElement('audio');
      audioElement.id = 'testaudio';
      document.body.append(audioElement);
    }
    var soundfile = null;
    if ($('[name="sound[voice]"]').val() === 'custom') {
      soundfile = '/private/sounds/' + $('[name="sound[voice]"]').val() + '/counter-' + Math.floor(Math.random() * 10 + 1) + '.mp3';
    } else {
      soundfile = '/resources/sounds/' + $('[name="sound[voice]"]').val() + '/' + $('[name="ui[language]"]').val() + '/counter-' + Math.floor(Math.random() * 10 + 1) + '.mp3';
    }
    audioElement.src = soundfile;
    audioElement.play()["catch"](function (error) {
      photoboothTools.console.log('Error with audio.play: ' + error);
    });
    return false;
  });
  $('#debugpanel-btn').on('click', function (ev) {
    ev.preventDefault();
    window.open('../admin/debug');
    return false;
  });
  $('#translate-btn').on('click', function (ev) {
    ev.preventDefault();
    window.open('https://crowdin.com/project/photobooth');
    return false;
  });
  $('#filesupload-btn').on('click', function (ev) {
    ev.preventDefault();
    window.open('../admin/upload');
    return false;
  });
  $('#reboot-btn').on('click', function (ev) {
    ev.preventDefault();
    shellCommand('reboot');
    return false;
  });
  $('#shutdown-btn').on('click', function (ev) {
    ev.preventDefault();
    shellCommand('shutdown');
    return false;
  });
});
$(function () {
  // init navi item
  getInitialNaviItem();

  // item click
  $('.navItem').on('click', function () {
    setNaviItem($(this)[0].id.replace('nav-', ''));
  });
});
function isElementInViewport(el) {
  var offset = 40;
  if ($('#activeTabLabel').is(':visible')) {
    offset = 100;
  }
  if (typeof jQuery === 'function' && el instanceof jQuery) {
    el = el[0];
  }
  var rect = el.getBoundingClientRect();
  var isActive = rect.top <= offset && rect.top >= -el.clientHeight;
  if (isActive) {
    return true;
  }
  return false;
}
$('.adminContent').on('scroll', function () {
  $('.adminSection.visible').each(function (idx, el) {
    if (isElementInViewport(el)) {
      var hash = window.location.hash;
      var urlHash = '#' + el.id;
      if (hash != urlHash) {
        window.history.pushState(null, null, urlHash);
        setNaviItem(el.id);
      }
    }
  });
});
function getInitialNaviItem() {
  if ($('.navItem')[0]) {
    var urlHash = window.location.hash;
    var hash = urlHash.replace('#', '');
    if (hash) {
      setNaviItem(hash);
      $(urlHash)[0].scrollIntoView();
    } else {
      $('.navItem').removeClass('isActive');
      $('.navItem').first().addClass('isActive');
      var itemName = $('.navItem.isActive').find('.naviLabel').html();
      $('#activeTabLabel').html(itemName);
    }
  }
}
function setNaviItem(item) {
  $('.navItem').removeClass('isActive');
  $('#nav-' + item).addClass('isActive');
  var itemName = $('.navItem.isActive').find('.naviLabel').html();
  $('#activeTabLabel').html(itemName);
  var top = $('#nav-' + item).offset().top;
  var height = $(window).height();
  if (top <= height || top >= height) {
    $('#nav-' + item).parents()[0].scrollIntoView({
      block: 'end'
    });
  }
}

// eslint-disable-next-line no-unused-vars
function toggleAdminNavi() {
  if ($('.adminNavi').hasClass('isActive')) {
    $('.adminNavi').removeClass('isActive');
  } else {
    $('.adminNavi').addClass('isActive');
  }
}

/* globals photoboothTools csrf */

function keypadAdd(value) {
  var keypadPin = $('#keypad_pin').html();
  var newPin = keypadPin + value;
  var pinLength = newPin.length;
  $('#keypad_pin').html(newPin);
  $('.keypad_keybox.active').addClass('checked');
  $('.keypad_keybox').find('.keypad_key.active').addClass('checked');
  $('.keypad_keybox.active').addClass('checked');
  $('.keypad_keybox').find('.keypad_key.active').addClass('checked');
  $('.keypad_keybox').removeClass('active');
  $('.keypad_keybox').find('.keypad_key').removeClass('active');
  $('.keypad_keybox').eq(pinLength).addClass('active');
  $('.keypad_keybox').eq(pinLength).find('.keypad_key').addClass('active');
  if (pinLength == 4) {
    checkKeypadPin(newPin);
  }
}
function keypadRemoveLastValue() {
  var newPin = $('#keypad_pin').html().slice(0, -1);
  var pinLength = newPin.length;
  $('#keypad_pin').html(newPin);
  $('.keypad_keybox').eq(pinLength + 1).removeClass('active').removeClass('checked');
  $('.keypad_keybox').eq(pinLength + 1).find('.keypad_key').removeClass('active').removeClass('checked');
  $('.keypad_keybox').eq(pinLength).addClass('active');
  $('.keypad_keybox').eq(pinLength).find('.keypad_key').addClass('active');
  $('.keypad_keybox').eq(pinLength).removeClass('checked');
  $('.keypad_keybox').eq(pinLength).find('.keypad_key').removeClass('checked');
}
function keypadClear() {
  $('#keypad_pin').html('');
  $('.keypad_keybox').removeClass('active');
  $('.keypad_keybox').find('.keypad_key').removeClass('active');
  $('.keypad_keybox').removeClass('checked');
  $('.keypad_keybox').find('.keypad_key').removeClass('checked');
  $('.keypad_keybox').eq(0).addClass('active');
  $('.keypad_keybox').eq(0).find('.keypad_key').addClass('active');
}
document.addEventListener('keydown', function (event) {
  var keypadPinElement = document.getElementById('keypad_pin');
  if (!keypadPinElement) {
    return;
  }
  var key = event.key || String.fromCharCode(event.which || event.keyCode);
  if (/^[0-9]$/.test(key)) {
    keypadAdd(parseInt(key, 10));
  } else if (key === 'Escape') {
    keypadClear();
  } else if (key === 'Backspace' || key === 'Delete') {
    keypadRemoveLastValue();
  }
});
function checkKeypadPin(pin) {
  $('.keypadLoader').removeClass('hidden');
  $('.keypadLoader').addClass('flex');
  $.ajax({
    url: environment.publicFolders.api + '/controller.php',
    dataType: 'json',
    type: 'POST',
    data: _defineProperty({
      controller: 'keypadLogin',
      pin: pin
    }, csrf.key, csrf.token),
    success: function success(data) {
      if (data.blocked) {
        var waitSeconds = data.retry_after || 0;
        $('.keypadLoader').addClass('hidden').removeClass('flex');
        var msg = data.message || photoboothTools.getTranslation('error');
        $('#keypad_message').text(msg + (waitSeconds ? ' (' + waitSeconds + 's)' : ''));
        $('.keypad_keybox').addClass('error');
        $('.keypad_key').addClass('error');
        // Keep message visible; user must wait out the window
        keypadClear();
        return;
      }
      if (data.state == true) {
        window.location.href = '../admin';
      } else {
        $('.keypad_keybox').addClass('error');
        $('.keypad_key').addClass('error');
        setTimeout(function () {
          $('.keypadLoader').addClass('hidden');
        }, 100);
        setTimeout(function () {
          $('.keypad_keybox').removeClass('error');
          $('.keypad_key').removeClass('error');
          keypadClear();
        }, 555);
      }
    },
    error: function error() {
      keypadClear();
      $('.keypadLoader').addClass('hidden');
      $('.keypadLoader').removeClass('flex');
    }
  });
}

// eslint-disable-next-line no-unused-vars
function adminImageSelect(element, path) {
  var parent = element.closest('.adminImageSelection');
  var origin = element.dataset.origin;
  var src = element.src;
  var previewElement = parent.querySelector('.adminImageSelection-preview');
  var textElement = parent.querySelector('.adminImageSelection-text');
  var inputElement = parent.querySelector('input[name="' + path + '"]');
  previewElement.src = src;
  textElement.textContent = origin;
  inputElement.value = origin;
  if (src !== '') {
    previewElement.parentElement.classList.remove('hidden');
  } else {
    previewElement.parentElement.classList.add('hidden');
  }
  var event = new Event('change');
  inputElement.dispatchEvent(event);
  var toggleGeneralCheckbox = function toggleGeneralCheckbox(checkboxName) {
    var checkbox = document.querySelector("input[name='".concat(checkboxName, "'][data-trigger='general']"));
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
  var selections = document.querySelectorAll('.adminImageSelection');
  selections.forEach(function (selection) {
    selection.classList.remove('isOpen');
  });
}

// eslint-disable-next-line no-unused-vars
function adminFontSelect(element, path, fontclass) {
  var parent = element.closest('.adminFontSelection');
  var origin = element.dataset.origin;
  var src = element.src;
  var previewElement = parent.querySelector('.adminFontSelection-preview');
  var textElement = parent.querySelector('.adminFontSelection-text');
  var inputElement = parent.querySelector('input[name="' + path + '"]');
  previewElement.src = src;
  textElement.textContent = origin;
  inputElement.value = origin;
  inputElement.setAttribute('data-fontclass', fontclass);
  var event = new Event('change');
  inputElement.dispatchEvent(event);
  parent.classList.remove('isOpen');
}

// eslint-disable-next-line no-unused-vars
function openAdminFontSelect(element) {
  element.closest('.adminFontSelection').classList.add('isOpen');
}

// eslint-disable-next-line no-unused-vars
function closeAdminFontSelect() {
  var selections = document.querySelectorAll('.adminFontSelection');
  selections.forEach(function (selection) {
    selection.classList.remove('isOpen');
  });
}

// eslint-disable-next-line no-unused-vars
function adminVideoSelect(element, path) {
  var parent = element.closest('.adminVideoSelection');
  var origin = element.dataset.origin;
  var src = element.src;
  var previewElement = parent.querySelector('.adminVideoSelection-preview');
  var textElement = parent.querySelector('.adminVideoSelection-text');
  var inputElement = parent.querySelector('input[name="' + path + '"]');
  previewElement.src = src;
  textElement.textContent = origin;
  inputElement.value = origin;
  var event = new Event('change');
  inputElement.dispatchEvent(event);
  parent.classList.remove('isOpen');
}

// eslint-disable-next-line no-unused-vars
function openAdminVideoSelect(element) {
  element.closest('.adminVideoSelection').classList.add('isOpen');
}

// eslint-disable-next-line no-unused-vars
function closeAdminVideoSelect() {
  var selections = document.querySelectorAll('.adminVideoSelection');
  selections.forEach(function (selection) {
    selection.classList.remove('isOpen');
  });
}

/* eslint n/no-unsupported-features/node-builtins: "off" */
/* globals photoboothTools csrf */
$(function () {
  function initThemes() {
    var apiBase = environment.publicFolders.api + '/themes.php';
    var $nameInput = $('#theme-name');
    var $saveButton = $('#theme-save-btn');
    var $loadButton = $('#theme-load-btn');
    var $deleteButton = $('#theme-delete-btn');
    var $exportButton = $('#theme-export-btn');
    var $importButton = $('#theme-import-btn');
    var $importInput = $('#theme-import-input');
    var $select = $('#theme-select');
    var $currentInput = $('input[name="theme[current]"]');
    var lastAppliedThemeSnapshot = null;
    function snapshotTheme() {
      lastAppliedThemeSnapshot = JSON.stringify(collectCurrentTheme());
    }
    function hasUnsavedChanges() {
      if (lastAppliedThemeSnapshot === null) {
        return false;
      }
      return JSON.stringify(collectCurrentTheme()) !== lastAppliedThemeSnapshot;
    }
    function updateLoadButtonState() {
      if ($loadButton.length === 0 || $deleteButton.length === 0 || $exportButton.length === 0) {
        return;
      }
      var selected = $select.val();
      var current = $currentInput.length ? $currentInput.val() : '';
      var isDifferent = selected && current && selected !== current;

      // Highlight load button in green when a different theme is selected
      if (isDifferent) {
        $loadButton.addClass('ring-2 ring-green-500');
      } else {
        $loadButton.removeClass('ring-2 ring-green-500');
      }

      // Disable load button when no theme is selected
      if (!selected) {
        $loadButton.prop('disabled', true);
        $loadButton.addClass('opacity-40 cursor-not-allowed');
      } else {
        $loadButton.prop('disabled', false);
        $loadButton.removeClass('opacity-40 cursor-not-allowed');
      }
      var disabledClasses = 'opacity-40 cursor-not-allowed';
      var toggleButton = function toggleButton(button, isDisabled) {
        button.prop('disabled', isDisabled);
        button.toggleClass(disabledClasses, isDisabled);
      };
      toggleButton($deleteButton, !selected);
      toggleButton($exportButton, !selected);
    }
    function getThemeElements() {
      var elements = [];
      $('[data-theme-field="true"]').each(function (_, el) {
        var $el = $(el);
        if ($el.attr('type') === 'hidden') {
          return;
        }
        elements.push(el);
      });
      return elements;
    }
    function parseFieldName(name) {
      var parts = [];
      var regex = /([^[]+)|\[([^\]]*)\]/g;
      var match;
      while ((match = regex.exec(name)) !== null) {
        var key = match[1] || match[2];
        if (key !== '') {
          parts.push(key);
        }
      }
      return parts;
    }
    function setNestedValue(target, path, value) {
      if (!Array.isArray(path) || path.length === 0) {
        return;
      }
      var current = target;
      for (var i = 0; i < path.length - 1; i++) {
        var key = path[i];
        if (!Object.prototype.hasOwnProperty.call(current, key) || _typeof(current[key]) !== 'object' || current[key] === null) {
          current[key] = {};
        }
        current = current[key];
      }
      current[path[path.length - 1]] = value;
    }
    function getNestedValue(source, path) {
      if (!Array.isArray(path) || path.length === 0) {
        return undefined;
      }
      var current = source;
      for (var i = 0; i < path.length; i++) {
        var key = path[i];
        if (!current || !Object.prototype.hasOwnProperty.call(current, key)) {
          return undefined;
        }
        current = current[key];
      }
      return current;
    }
    function collectCurrentTheme() {
      var elements = getThemeElements();
      var data = {};
      elements.forEach(function (el) {
        var $el = $(el);
        var name = $el.attr('name');
        if (!name) {
          return;
        }
        var path = parseFieldName(name);
        if (!path.length) {
          return;
        }
        var value;
        if (el.tagName === 'INPUT') {
          if ($el.attr('type') === 'checkbox') {
            value = $el.is(':checked') ? 'true' : 'false';
          } else {
            value = $el.val();
          }
        } else if (el.tagName === 'SELECT') {
          value = $el.val();
        } else if (el.tagName === 'TEXTAREA') {
          value = $el.val();
        }
        setNestedValue(data, path, value);
      });
      return data;
    }
    function applyTheme(theme) {
      if (!theme || _typeof(theme) !== 'object') {
        return;
      }
      var elements = getThemeElements();
      elements.forEach(function (el) {
        var $el = $(el);
        var name = $el.attr('name');
        if (!name) {
          return;
        }
        var path = parseFieldName(name);
        var value = getNestedValue(theme, path);

        // Fallback for older flat themes
        if (typeof value === 'undefined' && Object.prototype.hasOwnProperty.call(theme, name)) {
          value = theme[name];
        }
        if (el.tagName === 'INPUT') {
          var isCheckbox = $el.attr('type') === 'checkbox';
          var normalized = typeof value === 'undefined' ? isCheckbox ? false : '' : value;
          if (isCheckbox) {
            $el.prop('checked', normalized === true || normalized === 'true');
          } else {
            $el.val(normalized);
          }
        } else if (el.tagName === 'SELECT') {
          $el.val(typeof value === 'undefined' ? '' : value).trigger('change');
        } else if (el.tagName === 'TEXTAREA') {
          $el.val(typeof value === 'undefined' ? '' : value);
        }
        $el.trigger('change');
      });
    }
    function refreshSelect() {
      var desiredSelection = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      var current = $currentInput.length ? $currentInput.val() : '';
      var previousSelected = $select.val();
      var targetSelection = desiredSelection !== null && desiredSelection !== void 0 ? desiredSelection : previousSelected;
      $.getJSON(apiBase, {
        action: 'list',
        _: Date.now()
      }).done(function (data) {
        var themes = Array.isArray(data.themes) ? data.themes : [];
        $select.empty();
        $('<option>', {
          value: '',
          text: photoboothTools.getTranslation('theme_choose')
        }).appendTo($select);
        themes.slice().sort().forEach(function (key) {
          $('<option>', {
            value: key,
            text: key,
            selected: key === previousSelected || key === targetSelection
          }).appendTo($select);
        });
        if (current && $nameInput.length) {
          $nameInput.val(current);
        }
        if (targetSelection) {
          $select.val(targetSelection);
        }
        updateLoadButtonState();
        if (lastAppliedThemeSnapshot === null) {
          snapshotTheme();
        }
      }).fail(function () {
        photoboothTools.overlay.showError(photoboothTools.getTranslation('error'));
      });
    }
    $select.on('change', function () {
      updateLoadButtonState();
    });
    $saveButton.on('click', function () {
      var name = $nameInput.val().trim();
      if (!name) {
        return;
      }
      var themeExists = Array.from($select.find('option')).some(function (opt) {
        return opt.value === name;
      });
      if (themeExists) {
        var confirmMessage = photoboothTools.getTranslation('theme_override_confirm').replace('%s', name);
        var confirmed = window.confirm(confirmMessage);
        if (!confirmed) {
          return;
        }
      }
      var payload = _defineProperty({
        action: 'save',
        name: name,
        theme: collectCurrentTheme()
      }, csrf.key, csrf.token);
      $.ajax({
        url: apiBase,
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(payload),
        dataType: 'json'
      }).done(function () {
        if ($currentInput.length) {
          $currentInput.val(name);
        }
        $select.val(name);
        $nameInput.val(name);
        refreshSelect();
        updateLoadButtonState();
        snapshotTheme();
      }).fail(function () {
        photoboothTools.overlay.showError(photoboothTools.getTranslation('error'));
      });
    });
    $loadButton.on('click', function () {
      var selected = $select.val();
      if (!selected) {
        return;
      }
      if (hasUnsavedChanges()) {
        var confirmMessage = photoboothTools.getTranslation('theme_unsaved_confirm');
        var confirmed = window.confirm(confirmMessage);
        if (!confirmed) {
          return;
        }
      }
      $.getJSON(apiBase, _defineProperty({
        action: 'get',
        name: selected,
        _: Date.now()
      }, csrf.key, csrf.token)).done(function (data) {
        if (data.status === 'success' && data.theme) {
          applyTheme(data.theme);
          if ($currentInput.length) {
            $currentInput.val(selected);
          }
          if ($nameInput.length) {
            $nameInput.val(selected);
          }
          snapshotTheme();
          updateLoadButtonState();
        }
      }).fail(function () {
        photoboothTools.overlay.showError(photoboothTools.getTranslation('error'));
      });
    });
    $deleteButton.on('click', function () {
      var selected = $select.val();
      if (!selected) {
        return;
      }
      var payload = _defineProperty({
        action: 'delete',
        name: selected
      }, csrf.key, csrf.token);
      $.ajax({
        url: apiBase,
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(payload),
        dataType: 'json'
      }).done(function () {
        refreshSelect();
        updateLoadButtonState();
      }).fail(function () {
        photoboothTools.overlay.showError(photoboothTools.getTranslation('error'));
      });
    });
    $exportButton.on('click', function () {
      var selected = $select.val();
      if (!selected) {
        return;
      }
      var url = "".concat(apiBase, "?action=export&name=").concat(encodeURIComponent(selected), "&_=").concat(Date.now());
      var sep = url.includes('?') ? '&' : '?';
      var csrfUrl = "".concat(url).concat(sep).concat(csrf.key, "=").concat(encodeURIComponent(csrf.token));
      var link = document.createElement('a');
      link.href = csrfUrl;
      link.download = "".concat(selected, ".zip");
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    });
    $importButton.on('click', function () {
      if ($importInput.length) {
        $importInput.trigger('click');
      }
    });
    $importInput.on('change', function onImportChange() {
      var file = this.files ? this.files[0] : null;
      if (!file) {
        return;
      }
      var formData = new FormData();
      formData.append('action', 'import');
      formData.append('theme_zip', file);
      formData.append(csrf.key, csrf.token);
      var desiredName = $nameInput.length ? $nameInput.val().trim() : '';
      if (desiredName) {
        formData.append('name', desiredName);
      }
      $.ajax({
        url: apiBase,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json'
      }).done(function (data) {
        if (data.status !== 'success') {
          photoboothTools.overlay.showError(photoboothTools.getTranslation('error'));
          return;
        }
        var importedName = data.name || desiredName;
        if (data.theme) {
          applyTheme(data.theme);
          snapshotTheme();
        }
        if ($currentInput.length && importedName) {
          $currentInput.val(importedName);
        }
        if ($nameInput.length && importedName) {
          $nameInput.val(importedName);
        }
        refreshSelect(importedName);
        updateLoadButtonState();
      }).fail(function () {
        photoboothTools.overlay.showError(photoboothTools.getTranslation('error'));
      }).always(function () {
        $importInput.val('');
      });
    });
    refreshSelect();
    updateLoadButtonState();
  }
  photoboothTools.initialize().then(function () {
    initThemes();
  });
});

// eslint-disable-next-line no-unused-vars
function openToast(msg) {
  var type = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'isSuccess';
  var duration = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 2000;
  $('.adminToast').addClass('isActive ' + type);
  $('.adminToast').find('.headline').html(msg);
  if (type == 'isError') {
    $('.adminToast-icon').removeClass('fa-check');
    $('.adminToast-icon').addClass('fa-times');
  } else if (type == 'isWarning') {
    $('.adminToast-icon').removeClass('fa-check');
    $('.adminToast-icon').addClass('fa-triangle-exclamation');
  }
  setTimeout(function () {
    $('.adminToast').removeClass('isActive');
  }, duration);
}