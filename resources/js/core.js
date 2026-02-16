"use strict";

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
/* eslint n/no-unsupported-features/node-builtins: "off" */
/* globals initPhotoSwipeFromDOM initRemoteBuzzerFromDOM processChromaImage remoteBuzzerClient rotaryController globalGalleryHandle photoboothTools photoboothPreview virtualKeyboard csrf */

/* global createScreensaver */
var photoBooth = function () {
  var PhotoStyle = {
      PHOTO: 'photo',
      COLLAGE: 'collage',
      CHROMA: 'chroma',
      VIDEO: 'video',
      CUSTOM: 'custom'
    },
    CameraDisplayMode = {
      INIT: 1,
      BACKGROUND: 2,
      COUNTDOWN: 3,
      TEST: 4
    },
    PreviewMode = {
      NONE: 'none',
      DEVICE: 'device_cam',
      URL: 'url'
    },
    PreviewStyle = {
      FILL: 'fill',
      CONTAIN: 'contain',
      COVER: 'cover',
      NONE: 'none',
      SCALE_DOWN: 'scale-down'
    },
    CollageFrameMode = {
      OFF: 'off',
      ALWAYS: 'always',
      ONCE: 'once'
    };
  var api = {},
    startPage = $('.stage[data-stage="start"]'),
    loader = $('.stage[data-stage="loader"]'),
    loaderButtonBar = loader.find('.buttonbar'),
    loaderMessage = loader.find('.stage-message'),
    loaderImage = loader.find('.stage-image'),
    resultPage = $('.stage[data-stage="result"]'),
    screensaverOverlay = $('#screensaver-overlay'),
    screensaverVideo = $('#screensaver-video'),
    screensaverImage = $('#screensaver-image'),
    screensaverTextTop = $('#screensaver-text-top'),
    screensaverTextCenter = $('#screensaver-text-center'),
    screensaverTextBottom = $('#screensaver-text-bottom'),
    previewIpcam = $('#preview--ipcam'),
    previewVideo = $('#preview--video'),
    previewFramePicture = $('#previewframe--picture'),
    previewFrameCollage = $('#previewframe--collage'),
    videoBackground = $('#video-background'),
    videoSensor = $('#video--sensor'),
    buttonDelete = $('[data-command="deletebtn"]'),
    buttonPrint = $('[data-command="printbtn"]'),
    gallery = $('#gallery'),
    filternav = $('#filternav'),
    galimages = $('#galimages'),
    videoAnimation = $('#videoAnimation'),
    resultVideo = $('#resultVideo'),
    resultVideoQR = $('#resultVideoQR'),
    usesBackgroundPreview = config.preview.asBackground && config.preview.mode === PreviewMode.DEVICE.valueOf() && (config.commands.preview && !config.preview.bsm || !config.commands.preview),
    timeToLive = parseInt(config.picture.time_to_live, 10) * 1000,
    continuousCollageTime = config.collage.continuous_time * 1000,
    retryTimeout = config.picture.retry_timeout * 1000,
    notificationTimeout = config.ui.notification_timeout * 1000,
    screensaverMode = config.screensaver.mode,
    screensaverEnabled = config.screensaver.enabled && config.screensaver.timeout_minutes > 0 && (screensaverMode === 'gallery' || screensaverMode === 'folder' || (screensaverMode === 'video' ? !!config.screensaver.video_source : !!config.screensaver.image_source)),
    screensaverTimeoutMs = (config.screensaver.timeout_minutes || 0) * 60000,
    screensaverSwitchMs = (config.screensaver.switch_seconds || 60) * 1000,
    urlSafe = function urlSafe(src) {
      return src ? encodeURI(src) : '';
    };
  var timeOut,
    chromaFile = '',
    currentCollageFile = '',
    imgFilter = config.filters.defaults,
    isProcessingEffects = false,
    command,
    startTime,
    endTime,
    totalTime;
  api.takingPic = false;
  api.nextCollageNumber = 0;
  api.chromaimage = '';
  api.filename = '';
  api.photoStyle = '';
  api.collageLayout = config.collage.layout;
  api.collageLimit = config.collage.limit;
  api.isTimeOutPending = function () {
    return typeof timeOut !== 'undefined';
  };
  api.resetTimeOut = function () {
    if (timeToLive === 0) {
      return;
    }
    clearTimeout(timeOut);
    photoboothTools.console.log('Timeout for auto reload cleared.');
    if (!api.takingPic) {
      photoboothTools.console.logDev('Timeout for auto reload set to ' + timeToLive + ' milliseconds.');
      timeOut = setTimeout(function () {
        photoboothTools.reloadPage();
      }, timeToLive);
    }
  };
  api.reset = function () {
    loader.css('--stage-background', 'var(--background-countdown-color)');
    loader.removeClass('stage--active');
    loaderButtonBar.empty();
    loaderMessage.empty();
    loaderMessage.removeClass('stage-message--error');
    resultPage.removeAttr('style data-img');
    resultPage.removeClass('stage--active');
    gallery.removeClass('gallery--open');
    gallery.find('.gallery__inner').hide();
    previewVideo.hide();
    previewFrameCollage.hide();
    previewFramePicture.hide();
    previewVideo.css('z-index', 0);
    videoSensor.hide();
    previewIpcam.hide();
    photoboothTools.overlay.close();
    photoboothTools.modal.close();
  };
  api.init = function () {
    api.reset();
    startPage.addClass('stage--active');
    if (usesBackgroundPreview) {
      photoboothPreview.startVideo(CameraDisplayMode.BACKGROUND);
      photoboothTools.console.logDev('Preview: core: start video (BACKGROUND) from api.init.');
    } else if (config.commands.preview && !config.preview.bsm) {
      photoboothTools.console.logDev('Preview: core: start video (INIT) from api.init.');
      photoboothPreview.startVideo(CameraDisplayMode.INIT);
    }
    initRemoteBuzzerFromDOM();
    rotaryController.focusSet(startPage);
    initPhotoSwipeFromDOM('#galimages');
    api.screensaver.resetTimer();
    var params = new URLSearchParams(window.location.search);
    if (params.has('screensaverPreview')) {
      api.screensaver.show(true);
    }
  };
  api.screensaver = createScreensaver({
    config: config,
    environment: environment,
    startPage: startPage,
    overlay: screensaverOverlay,
    videoEl: screensaverVideo,
    imageEl: screensaverImage,
    textTop: screensaverTextTop,
    textCenter: screensaverTextCenter,
    textBottom: screensaverTextBottom,
    screensaverEnabled: screensaverEnabled,
    screensaverMode: screensaverMode,
    screensaverTimeoutMs: screensaverTimeoutMs,
    screensaverSwitchMs: screensaverSwitchMs,
    urlSafe: urlSafe,
    photoboothTools: photoboothTools
  });
  api.navbar = {
    open: function open() {
      filternav.addClass('sidenav--open');
      rotaryController.focusSet(filternav);
    },
    close: function close() {
      filternav.removeClass('sidenav--open');
    },
    toggle: function toggle() {
      filternav.toggleClass('sidenav--open');
      if (filternav.hasClass('sidenav--open')) {
        rotaryController.focusSet(filternav);
      }
    }
  };
  var setFiltersEnabled = function setFiltersEnabled(enabled) {
    isProcessingEffects = !enabled;
    filternav.css('pointer-events', enabled ? '' : 'none');
    filternav.toggleClass('filters--disabled', !enabled);
  };
  api.stopPreviewAndCaptureFromVideo = function () {
    if (config.preview.camTakesPic) {
      if (photoboothPreview.stream) {
        videoSensor.get(0).width = previewVideo.get(0).videoWidth;
        videoSensor.get(0).height = previewVideo.get(0).videoHeight;
        videoSensor.get(0).getContext('2d').drawImage(previewVideo.get(0), 0, 0);
      }
    }
    if (!config.commands.preview_kill || config.preview.camTakesPic) {
      photoboothTools.console.logDev('Preview: core: stopping preview from stopPreviewAndCaptureFromVideo.');
      photoboothPreview.stopPreview();
    }
  };
  api.countdown = {
    element: null,
    audioElement: null,
    create: function create() {
      if (api.countdown.element === null) {
        var element = document.createElement('div');
        element.classList.add('countdown');
        document.body.append(element);
        api.countdown.element = element;
      }
      if (api.countdown.audioElement === null) {
        var audioElement = document.createElement('audio');
        document.body.append(audioElement);
        api.countdown.audioElement = audioElement;
      }
    },
    destroy: function destroy() {
      if (api.countdown.element !== null) {
        api.countdown.element.remove();
        api.countdown.element = null;
      }
      if (api.countdown.audioElement !== null) {
        api.countdown.audioElement.remove();
        api.countdown.audioElement = null;
      }
    },
    start: function start(seconds) {
      photoboothTools.console.log('Countdown started. Set to ' + seconds + ' seconds.');
      api.countdown.create();
      return new Promise(function (resolve) {
        var stop = parseInt(config.preview.stop_time, 10) > seconds ? 0 : parseInt(config.preview.stop_time, 10);
        photoboothTools.console.logDev('Preview: core: stop at ' + stop);
        var startTime = performance.now();
        var targetTime = startTime + seconds * 1000;
        var lastSecondShown = null;
        var _tick = function tick() {
          var now = performance.now();
          var remainingSeconds = Math.ceil((targetTime - now) / 1000);

          // Only update when we moved to the next integer second
          if (remainingSeconds !== lastSecondShown) {
            lastSecondShown = remainingSeconds;
            photoboothTools.console.logDev('Preview: core: countdown seconds ' + remainingSeconds);
            api.countdown.element.innerHTML = '';
            if (remainingSeconds > 0) {
              // dont show the 0 as countdown number
              var numberElement = document.createElement('div');
              numberElement.classList.add('countdown-number');
              numberElement.textContent = Number(remainingSeconds).toString();
              api.countdown.element.appendChild(numberElement);
            }
            if (config.sound.enabled && config.sound.countdown_enabled) {
              var soundfile = photoboothTools.getSound('counter-' + Number(remainingSeconds).toString());
              if (soundfile !== null) {
                api.countdown.audioElement.src = soundfile;
                api.countdown.audioElement.play()["catch"](function (error) {
                  photoboothTools.console.log('Error with audio.play: ' + error);
                });
              }
            }

            // stop second hit
            if (remainingSeconds === stop && !config.preview.camTakesPic) {
              photoboothTools.console.logDev('Preview: core: stopping preview at countdown.');
              photoboothPreview.stopPreview();
            }

            // after 1 is faded out, on second 0
            if (remainingSeconds <= 0) {
              photoboothTools.console.log('Countdown finished.');
              api.countdown.destroy();
              resolve();
              return;
            }
          }
          if (remainingSeconds > 0) {
            if (typeof window.requestAnimationFrame === 'function') {
              window.requestAnimationFrame(_tick);
            } else {
              setTimeout(_tick, 50);
            }
          }
        };
        _tick();
      });
    }
  };
  api.cheese = {
    element: null,
    audioElement: null,
    create: function create() {
      if (api.cheese.audioElement === null) {
        var audioElement = document.createElement('audio');
        document.body.append(audioElement);
        api.cheese.audioElement = audioElement;
      }
      if (api.cheese.element === null) {
        var element = document.createElement('div');
        element.classList.add('cheese');
        if (config.ui.shutter_cheese_img != null && config.ui.shutter_cheese_img !== '') {
          var image = document.createElement('img');
          image.src = config.ui.shutter_cheese_img;
          var imageElement = document.createElement('div');
          imageElement.classList.add('cheese-image');
          imageElement.appendChild(image);
          element.appendChild(imageElement);
        } else if (api.photoStyle === PhotoStyle.VIDEO) {
          var labelElement = document.createElement('div');
          labelElement.classList.add('cheese-label');
          labelElement.textContent = config.video.cheese;
          element.appendChild(labelElement);
        } else if (api.photoStyle === PhotoStyle.COLLAGE) {
          var _labelElement = document.createElement('div');
          _labelElement.classList.add('cheese-label');
          _labelElement.innerHTML = photoboothTools.getTranslation('cheese') + '<br>' + (api.nextCollageNumber + 1) + ' / ' + api.collageLimit;
          _labelElement.style.textAlign = 'center';
          element.appendChild(_labelElement);
        } else {
          var _labelElement2 = document.createElement('div');
          _labelElement2.classList.add('cheese-label');
          _labelElement2.textContent = photoboothTools.getTranslation('cheese');
          element.appendChild(_labelElement2);
        }
        document.body.append(element);
        api.cheese.element = element;
      }
    },
    destroy: function destroy() {
      if (api.cheese.audioElement !== null) {
        api.cheese.audioElement.remove();
        api.cheese.audioElement = null;
      }
      if (api.cheese.element !== null) {
        api.cheese.element.remove();
        api.cheese.element = null;
      }
    },
    start: function start() {
      photoboothTools.console.log('Cheese: Start');
      api.cheese.create();
      return new Promise(function (resolve) {
        if (config.sound.enabled && config.sound.cheese_enabled) {
          var soundfile = photoboothTools.getSound('cheese');
          if (soundfile !== null) {
            api.cheese.audioElement.src = soundfile;
            api.cheese.audioElement.play()["catch"](function (error) {
              photoboothTools.console.log('Error with audio.play: ' + error);
            });
          }
        }
        setTimeout(function () {
          photoboothTools.console.log('Cheese: End');
          resolve();
        }, config.picture.cheese_time);
      });
    }
  };
  api.shutter = {
    element: null,
    create: function create() {
      if (api.shutter.element === null) {
        var flash = document.createElement('div');
        flash.classList.add('shutter-flash');
        var aperture = document.createElement('div');
        aperture.classList.add('shutter-aperture');
        var element = document.createElement('div');
        element.classList.add('shutter');
        element.appendChild(flash);
        element.appendChild(aperture);
        document.body.append(element);
        api.shutter.element = element;
      }
    },
    destroy: function destroy() {
      if (api.shutter.element !== null) {
        api.shutter.element.remove();
        api.shutter.element = null;
      }
    },
    start: function start() {
      api.shutter.create();
      return new Promise(function (resolve) {
        photoboothTools.console.log('Shutter: Start');
        var flash = api.shutter.element.querySelector('.shutter-flash');
        flash.style.transition = 'opacity 0.5s';
        var flashAnimation = flash.animate([{}, {
          opacity: 1
        }], {
          duration: 500,
          fill: 'forwards'
        });
        flashAnimation.onfinish = function () {
          resolve();
        };
      });
    },
    stop: function stop() {
      api.shutter.create();
      return new Promise(function (resolve) {
        photoboothTools.console.log('Shutter: Stop');
        var aperture = api.shutter.element.querySelector('.shutter-aperture');
        aperture.style.transition = 'width 0.5s, padding-bottom 0.5s';
        var apertureAnimation = aperture.animate([{}, {
          width: 0,
          paddingBottom: 0
        }], {
          duration: 500,
          fill: 'forwards'
        });
        apertureAnimation.onfinish = function () {
          api.shutter.destroy();
          resolve();
        };
      });
    }
  };
  api.clearLoaderImage = function () {
    loaderImage.css({
      display: 'none',
      'background-image': ''
    });
    loaderImage.removeAttr('data-img');
  };
  api.shellCommand = function (cmd) {
    var file = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
    command = {
      mode: cmd,
      filename: file
    };
    if (typeof csrf !== 'undefined') {
      command[csrf.key] = csrf.token;
    }
    photoboothTools.console.log('Run', cmd);
    jQuery.post(environment.publicFolders.api + '/shellCommand.php', command).done(function (result) {
      photoboothTools.console.log(cmd, 'result: ', result);
    }).fail(function (xhr, status, result) {
      photoboothTools.console.log(cmd, 'result: ', result);
    });
  };
  api.thrill = /*#__PURE__*/function () {
    var _ref = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee(photoStyle) {
      var retry,
        countdownTime,
        maxGetMediaRetry,
        getMode,
        getUrl,
        _args = arguments,
        _t,
        _t2;
      return _regenerator().w(function (_context) {
        while (1) switch (_context.n) {
          case 0:
            retry = _args.length > 1 && _args[1] !== undefined ? _args[1] : 0;
            if (!api.takingPic) {
              _context.n = 1;
              break;
            }
            photoboothTools.console.logDev('ERROR: Taking picture in progress already!');
            return _context.a(2);
          case 1:
            if (!config.selfie_mode) {
              _context.n = 2;
              break;
            }
            photoboothTools.console.logDev('ERROR: Taking picture unsupported on selfie mode!');
            return _context.a(2);
          case 2:
            api.navbar.close();
            api.reset();
            api.closeGallery();
            api.clearLoaderImage();
            remoteBuzzerClient.inProgress(photoStyle);
            api.takingPic = true;
            photoboothTools.console.logDev('Taking picture in progress: ' + api.takingPic);
            if (api.isTimeOutPending()) {
              api.resetTimeOut();
            }
            if (config.commands.pre_photo) {
              api.shellCommand('pre-command');
            }
            if (currentCollageFile && api.nextCollageNumber) {
              photoStyle = PhotoStyle.COLLAGE;
            }
            if (chromaFile) {
              photoStyle = PhotoStyle.CHROMA;
            }
            api.photoStyle = photoStyle;
            photoboothTools.console.log('PhotoStyle: ' + api.photoStyle);
            _t = api.photoStyle;
            _context.n = _t === PhotoStyle.COLLAGE ? 3 : _t === PhotoStyle.VIDEO ? 4 : _t === PhotoStyle.CUSTOM ? 5 : _t === PhotoStyle.PHOTO ? 6 : 6;
            break;
          case 3:
            countdownTime = config.collage.cntdwn_time;
            return _context.a(3, 7);
          case 4:
            countdownTime = config.video.cntdwn_time;
            return _context.a(3, 7);
          case 5:
            countdownTime = config.custom.cntdwn_time;
            return _context.a(3, 7);
          case 6:
            countdownTime = config.picture.cntdwn_time;
            return _context.a(3, 7);
          case 7:
            maxGetMediaRetry = Math.max(countdownTime - 1, 0);
            if (config.commands.preview_kill && maxGetMediaRetry > 0) {
              maxGetMediaRetry = Math.max(countdownTime - parseInt(config.preview.stop_time, 10), 0);
            }
            photoboothPreview.startVideo(CameraDisplayMode.COUNTDOWN, retry, maxGetMediaRetry);
            if (config.preview.mode !== PreviewMode.NONE.valueOf() && (config.preview.style === PreviewStyle.CONTAIN.valueOf() || config.preview.style === PreviewStyle.SCALE_DOWN.valueOf()) && config.preview.showFrame) {
              if ((api.photoStyle === PhotoStyle.PHOTO || api.photoStyle === PhotoStyle.CUSTOM) && config.picture.take_frame) {
                previewFramePicture.show();
              } else if (api.photoStyle === PhotoStyle.COLLAGE && config.collage.take_frame === CollageFrameMode.ALWAYS.valueOf()) {
                previewFrameCollage.show();
              }
            }
            videoBackground.hide();
            startPage.removeClass('stage--active');
            loader.addClass('stage--active');
            api.screensaver.hide();
            if (!config.get_request.countdown) {
              _context.n = 13;
              break;
            }
            _t2 = api.photoStyle;
            _context.n = _t2 === PhotoStyle.COLLAGE ? 8 : _t2 === PhotoStyle.VIDEO ? 9 : _t2 === PhotoStyle.CUSTOM ? 10 : _t2 === PhotoStyle.PHOTO ? 11 : 11;
            break;
          case 8:
            getMode = config.get_request.collage;
            return _context.a(3, 12);
          case 9:
            getMode = config.get_request.video;
            return _context.a(3, 12);
          case 10:
            getMode = config.get_request.custom;
            return _context.a(3, 12);
          case 11:
            getMode = config.get_request.picture;
            return _context.a(3, 12);
          case 12:
            getUrl = config.get_request.server + '/' + getMode;
            photoboothTools.getRequest(getUrl);
          case 13:
            _context.n = 14;
            return api.countdown.start(countdownTime);
          case 14:
            _context.n = 15;
            return api.cheese.start();
          case 15:
            if (config.preview.camTakesPic && !photoboothPreview.stream && !config.dev.demo_images) {
              api.errorPic({
                error: 'No preview by device cam available!'
              });
            } else if (api.photoStyle === PhotoStyle.VIDEO) {
              api.takeVideo(retry);
            } else {
              api.takePic(retry);
            }
          case 16:
            return _context.a(2);
        }
      }, _callee);
    }));
    return function (_x) {
      return _ref.apply(this, arguments);
    };
  }();
  api.takeVideo = function (retry) {
    remoteBuzzerClient.inProgress('in-progress');
    var data = {
      style: api.photoStyle
    };
    api.callTakeVideoApi(data, retry);
  };
  api.takePic = function (retry) {
    remoteBuzzerClient.inProgress('in-progress');
    api.stopPreviewAndCaptureFromVideo();
    var data = {
      filter: imgFilter,
      style: api.photoStyle,
      canvasimg: videoSensor.get(0).toDataURL('image/jpeg')
    };
    if (api.photoStyle === PhotoStyle.COLLAGE) {
      data.file = currentCollageFile;
      data.collageNumber = api.nextCollageNumber;
      data.collageLimit = api.collageLimit;
    }
    if (api.photoStyle === PhotoStyle.CHROMA) {
      data.file = chromaFile;
    }
    loader.css('--stage-background', 'var(--background-countdown-color)');
    api.callTakePicApi(data, retry);
  };
  api.retryTakePic = function (retry) {
    api.takingPic = false;
    retry += 1;
    loaderMessage.text(photoboothTools.getTranslation('retry_message') + ' ' + retry + '/' + config.picture.retry_on_error);
    photoboothTools.console.logDev('Retry to capture image: ' + retry);
    setTimeout(function () {
      api.thrill(api.photoStyle, retry);
    }, retryTimeout);
  };
  api.callTakePicApi = /*#__PURE__*/function () {
    var _ref2 = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee4(data) {
      var retry,
        _args4 = arguments;
      return _regenerator().w(function (_context4) {
        while (1) switch (_context4.n) {
          case 0:
            retry = _args4.length > 1 && _args4[1] !== undefined ? _args4[1] : 0;
            startTime = new Date().getTime();
            photoboothTools.console.logDev('Capture image.');
            jQuery.post({
              url: environment.publicFolders.api + '/capture.php',
              data: data,
              timeout: 25000
            }).done(/*#__PURE__*/function () {
              var _ref3 = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee2(result) {
                var imageUrl, preloadImage, picdate, takePictureButton, collageProcessButton, retakeButton, abortButton;
                return _regenerator().w(function (_context2) {
                  while (1) switch (_context2.n) {
                    case 0:
                      api.cheese.destroy();
                      if (!config.ui.shutter_animation) {
                        _context2.n = 2;
                        break;
                      }
                      _context2.n = 1;
                      return api.shutter.start();
                    case 1:
                      _context2.n = 2;
                      return api.shutter.stop();
                    case 2:
                      endTime = new Date().getTime();
                      totalTime = endTime - startTime;
                      photoboothTools.console.log('Took ' + data.style, result);
                      photoboothTools.console.logDev('Taking picture took ' + totalTime + 'ms');
                      imgFilter = config.filters.defaults;
                      $('#filternav .sidenav-list-item--active').removeClass('sidenav-list-item--active');
                      $('.sidenav-list-item[data-filter="' + imgFilter + '"]').addClass('sidenav-list-item--active');
                      previewFrameCollage.hide();
                      previewFramePicture.hide();
                      if (result.error) {
                        photoboothTools.console.logDev('Error while taking picture.');
                        if (config.picture.retry_on_error > 0 && retry < config.picture.retry_on_error) {
                          api.retryTakePic(retry);
                        } else {
                          api.errorPic(result);
                        }
                      } else if (result.success === PhotoStyle.COLLAGE) {
                        currentCollageFile = result.file;
                        api.nextCollageNumber = result.current + 1;
                        loaderButtonBar.empty();
                        loaderMessage.empty();
                        videoSensor.hide();
                        previewVideo.hide();
                        imageUrl = environment.publicFolders.tmp + '/' + result.collage_file;
                        preloadImage = new Image();
                        picdate = Date.now().toString();
                        preloadImage.onload = function () {
                          loaderImage.attr('data-img', picdate);
                          loaderImage.css('background-image', "url(".concat(imageUrl, "?filter=").concat(imgFilter, "&v=").concat(picdate, ")"));
                        };
                        preloadImage.src = imageUrl;
                        loaderImage.show();
                        photoboothTools.console.logDev('Taken collage photo number: ' + (result.current + 1) + ' / ' + api.collageLimit);
                        if (result.current + 1 < api.collageLimit) {
                          photoboothTools.console.logDev('core: initialize Media.');
                          photoboothPreview.initializeMedia();
                          api.takingPic = false;
                        }
                        if (config.collage.continuous) {
                          loaderMessage.append($('<p>').text(photoboothTools.getTranslation('wait_message')));
                          setTimeout(function () {
                            api.clearLoaderImage();
                            imageUrl = '';
                            if (result.current + 1 < api.collageLimit) {
                              api.thrill(PhotoStyle.COLLAGE);
                            } else {
                              currentCollageFile = '';
                              api.nextCollageNumber = 0;
                              api.processPic(result);
                            }
                          }, continuousCollageTime);
                        } else {
                          // collage with interruption
                          if (result.current + 1 < api.collageLimit) {
                            takePictureButton = $('<button type="button" class="button collageNext rotaryfocus" id="btnCollageNext">');
                            takePictureButton.append('<span class="button--icon"><i class="' + config.icons.take_picture + '"></i></span>');
                            takePictureButton.append('<span class="button--label">' + photoboothTools.getTranslation('nextPhoto') + '</span>');
                            takePictureButton.appendTo(loaderButtonBar).on('click', function (event) {
                              event.stopPropagation();
                              event.preventDefault();
                              imageUrl = '';
                              api.thrill(PhotoStyle.COLLAGE);
                            });
                            remoteBuzzerClient.collageWaitForNext();
                          } else {
                            collageProcessButton = $('<button type="button" class="button collageProcess rotaryfocus" id="btnCollageProcess">');
                            collageProcessButton.append('<span class="button--icon"><i class="' + config.icons.save + '"></i></span>');
                            collageProcessButton.append('<span class="button--label">' + photoboothTools.getTranslation('processPhoto') + '</span>');
                            collageProcessButton.appendTo(loaderButtonBar).on('click', function (event) {
                              event.stopPropagation();
                              event.preventDefault();
                              imageUrl = '';
                              currentCollageFile = '';
                              api.nextCollageNumber = 0;
                              api.processPic(result);
                            });
                            remoteBuzzerClient.collageWaitForProcessing();
                          }
                          retakeButton = $('<button type="button" class="button collageRetake rotaryfocus">');
                          retakeButton.append('<span class="button--icon"><i class="' + config.icons.refresh + '"></i></span>');
                          retakeButton.append('<span class="button--label">' + photoboothTools.getTranslation('retakePhoto') + '</span>');
                          retakeButton.appendTo(loaderButtonBar).on('click', function (event) {
                            event.stopPropagation();
                            event.preventDefault();
                            imageUrl = '';
                            api.deleteImage(result.collage_file, function () {
                              setTimeout(function () {
                                api.nextCollageNumber = result.current;
                                api.thrill(PhotoStyle.COLLAGE);
                              }, notificationTimeout);
                            });
                          });
                          abortButton = $('<button type="button" class="button collageAbort rotaryfocus">');
                          abortButton.append('<span class="button--icon"><i class="' + config.icons["delete"] + '"></i></span>');
                          abortButton.append('<span class="button--label">' + photoboothTools.getTranslation('abort') + '</span>');
                          abortButton.appendTo(loaderButtonBar).on('click', function () {
                            location.assign('./');
                          });
                          rotaryController.focusSet(loader);
                        }
                      } else if (result.success === PhotoStyle.CHROMA) {
                        chromaFile = result.file;
                        api.processPic(result);
                      } else {
                        currentCollageFile = '';
                        api.nextCollageNumber = 0;
                        api.processPic(result);
                      }
                    case 3:
                      return _context2.a(2);
                  }
                }, _callee2);
              }));
              return function (_x3) {
                return _ref3.apply(this, arguments);
              };
            }()).fail(/*#__PURE__*/function () {
              var _ref4 = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee3(xhr, status, result) {
                return _regenerator().w(function (_context3) {
                  while (1) switch (_context3.n) {
                    case 0:
                      try {
                        endTime = new Date().getTime();
                        totalTime = endTime - startTime;
                        api.cheese.destroy();
                        if (result === null || result === undefined || typeof result === 'string') {
                          result = {
                            error: result || 'Unexpected error: result is null or undefined'
                          };
                        } else if (!result.error) {
                          result.error = 'Unknown error occurred';
                        }
                        photoboothTools.console.log('Took ' + data.style, result);
                        photoboothTools.console.logDev('Failed after ' + totalTime + 'ms');
                        if (config.picture.retry_on_error > 0 && retry < config.picture.retry_on_error) {
                          photoboothTools.console.logDev('ERROR: Taking picture failed. Retrying. Retry: ' + retry + ' / ' + config.picture.retry_on_error);
                          api.retryTakePic(retry);
                        } else {
                          api.errorPic(result);
                        }
                      } catch (error) {
                        photoboothTools.console.log('Unexpected error in .fail block', error);
                        api.errorPic({
                          error: error.message || 'An unexpected error occurred during failure handling'
                        });
                      }
                    case 1:
                      return _context3.a(2);
                  }
                }, _callee3);
              }));
              return function (_x4, _x5, _x6) {
                return _ref4.apply(this, arguments);
              };
            }());
          case 1:
            return _context4.a(2);
        }
      }, _callee4);
    }));
    return function (_x2) {
      return _ref2.apply(this, arguments);
    };
  }();
  api.callTakeVideoApi = function (data) {
    if (config.video.animation) {
      videoAnimation.show();
    }
    startTime = new Date().getTime();
    jQuery.post(environment.publicFolders.api + '/capture.php', data).done(/*#__PURE__*/function () {
      var _ref5 = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee5(result) {
        return _regenerator().w(function (_context5) {
          while (1) switch (_context5.n) {
            case 0:
              api.cheese.destroy();
              if (config.video.animation) {
                videoAnimation.hide();
              }
              endTime = new Date().getTime();
              totalTime = endTime - startTime;
              photoboothTools.console.log('Took ' + data.style, result);
              photoboothTools.console.logDev('Taking video took ' + totalTime + 'ms');
              imgFilter = config.filters.defaults;
              $('#filternav .sidenav-list-item--active').removeClass('sidenav-list-item--active');
              $('.sidenav-list-item[data-filter="' + imgFilter + '"]').addClass('sidenav-list-item--active');
              if (result.error) {
                api.errorPic(result);
              } else {
                api.processVideo(result);
              }
            case 1:
              return _context5.a(2);
          }
        }, _callee5);
      }));
      return function (_x7) {
        return _ref5.apply(this, arguments);
      };
    }()).fail(function (xhr, status, result) {
      try {
        api.cheese.destroy();
        if (result === null || result === undefined || typeof result === 'string') {
          result = {
            error: result || 'Unexpected error: result is null or undefined'
          };
        } else if (!result.error) {
          result.error = 'Unknown error occurred';
        }
        api.errorPic(result);
      } catch (error) {
        photoboothTools.console.log('Unexpected error in .fail block', error);
        api.errorPic({
          error: error.message || 'An unexpected error occurred during failure handling'
        });
      }
    });
  };
  api.errorPic = function (data) {
    setTimeout(function () {
      api.cheese.destroy();
      api.shutter.destroy();
      setFiltersEnabled(true);
      loaderMessage.empty();
      loaderButtonBar.empty();
      previewVideo.hide();
      videoSensor.hide();
      previewFrameCollage.hide();
      previewFramePicture.hide();
      if (config.video.animation) {
        videoAnimation.hide();
      }
      loaderMessage.addClass('stage-message--error');
      loaderMessage.append($('<p>').text(photoboothTools.getTranslation('error')));
      photoboothTools.console.log('An error occurred:', data.error);
      if (config.dev.loglevel > 1) {
        loaderMessage.append($('<p>').text(data.error));
      }
      api.takingPic = false;
      remoteBuzzerClient.inProgress(false);
      photoboothTools.console.logDev('Taking picture in progress: ' + api.takingPic);
      if (config.dev.reload_on_error) {
        loaderMessage.append($('<p>').text(photoboothTools.getTranslation('auto_reload')));
        setTimeout(function () {
          photoboothTools.reloadPage();
        }, notificationTimeout);
      } else {
        var reloadButton = $('<button type="button" class="button rotaryfocus">');
        reloadButton.append('<span class="button--icon"><i class="' + config.icons.refresh + '"></i></span>');
        reloadButton.append('<span class="button--label">' + photoboothTools.getTranslation('reload') + '</span>');
        reloadButton.appendTo(loaderButtonBar).on('click', function () {
          photoboothTools.reloadPage();
        });
      }
    }, 500);
  };
  api.processPic = function (result) {
    startTime = new Date().getTime();
    loader.addClass('stage--active');
    startPage.removeClass('stage--active');
    resultPage.removeClass('stage--active');
    setFiltersEnabled(false);
    loaderMessage.html('<i class="' + config.icons.spinner + '"></i><br>' + (api.photoStyle === PhotoStyle.COLLAGE ? photoboothTools.getTranslation('busyCollage') : photoboothTools.getTranslation('busy')));
    if ((api.photoStyle === PhotoStyle.PHOTO || api.photoStyle === PhotoStyle.CUSTOM) && config.picture.preview_before_processing) {
      var tempImageUrl = environment.publicFolders.tmp + '/' + result.file;
      var preloadImage = new Image();
      preloadImage.onload = function () {
        loader.css('background-image', "url(".concat(tempImageUrl, ")"));
        loader.addClass('showBackgroundImage');
      };
      preloadImage.src = tempImageUrl;
    }
    $.ajax({
      method: 'POST',
      url: environment.publicFolders.api + '/applyEffects.php',
      data: {
        file: result.file,
        filter: imgFilter,
        style: api.photoStyle,
        collageLayout: api.collageLayout,
        collageLimit: api.collageLimit
      },
      success: function success(data) {
        setFiltersEnabled(true);
        photoboothTools.console.log(api.photoStyle + ' processed', data);
        endTime = new Date().getTime();
        totalTime = endTime - startTime;
        photoboothTools.console.logDev('Processing ' + api.photoStyle + ' took ' + totalTime + 'ms for filter `' + imgFilter + '`');
        photoboothTools.console.logDev('Images:', data.images);
        if (config.get_request.processed) {
          var getUrl = config.get_request.server + '/' + api.photoStyle;
          photoboothTools.getRequest(getUrl);
        }
        if (data.error) {
          api.errorPic(data);
        } else if (api.photoStyle === PhotoStyle.CHROMA) {
          api.renderChroma(data.file);
        } else {
          api.renderPic(data.file, data.images);
        }
      },
      error: function error(jqXHR, textStatus) {
        setFiltersEnabled(true);
        api.errorPic({
          error: 'Request failed: ' + textStatus
        });
      }
    });
  };
  api.processVideo = function (result) {
    startTime = new Date().getTime();
    videoSensor.hide();
    previewVideo.hide();
    videoBackground.hide();
    loader.css('--stage-background', 'var(--background-countdown-color)');
    loaderMessage.html('<i class="' + config.icons.spinner + '"></i><br>' + photoboothTools.getTranslation('busyVideo'));
    $.ajax({
      method: 'POST',
      url: environment.publicFolders.api + '/applyVideoEffects.php',
      data: {
        file: result.file
      },
      success: function success(data) {
        photoboothTools.console.log('video processed', data);
        endTime = new Date().getTime();
        totalTime = endTime - startTime;
        photoboothTools.console.logDev('Processing video took ' + totalTime + 'ms');
        photoboothTools.console.logDev('Video:', data.file);
        if (config.get_request.processed) {
          var getUrl = config.get_request.server + '/video';
          photoboothTools.getRequest(getUrl);
        }
        if (data.error) {
          api.errorPic(data);
        } else {
          // if collage exists: render the result for the collage image and overlay the video over the image
          var collage = data.file + '-collage.jpg';
          var filename = data.images.includes(collage) ? collage : data.file;
          api.renderPic(filename, data.images);
          var file = environment.publicFolders.images + '/' + data.file;
          if (!config.video.collage_only) {
            if (config.video.gif) {
              resultVideo.attr('src', file);
            } else {
              var source = document.createElement('source');
              source.setAttribute('src', file);
              source.setAttribute('type', 'video/mp4');
              resultVideo.append(source);
              resultVideo.get(0).play();
            }
            resultVideo.show();
            if (config.video.qr) {
              resultVideoQR.attr('src', environment.publicFolders.api + '/qrcode.php?filename=' + data.file);
              resultVideoQR.show();
            }
          }
        }
      },
      error: function error(jqXHR, textStatus) {
        api.errorPic({
          error: 'Request failed: ' + textStatus
        });
      }
    });
  };
  api.renderChroma = function (filename) {
    api.filename = filename;
    if (config.keying.show_all) {
      api.addImage(filename);
    }
    startPage.removeClass('stage--active');
    loader.removeClass('stage--active');
    resultPage.addClass('stage--active');
    var chromaimage = environment.publicFolders.keying + '/' + filename;
    processChromaImage(chromaimage, true, filename);
    rotaryController.focusSet(resultPage);
    api.takingPic = false;
    remoteBuzzerClient.inProgress(false);
    photoboothTools.console.logDev('Taking picture in progress: ' + api.takingPic);
    api.resetTimeOut();
  };
  api.showMailForm = function (image) {
    photoboothTools.modal.open('mail');
    var body = photoboothTools.modal.element.querySelector('.modal-body');
    var buttonbar = photoboothTools.modal.element.querySelector('.modal-buttonbar');

    // Text
    var text = document.createElement('p');
    text.textContent = config.mail.send_all_later ? photoboothTools.getTranslation('insertMailToDB') : photoboothTools.getTranslation('insertMail');
    body.appendChild(text);

    // Form
    var form = document.createElement('form');
    form.id = 'send-mail-form';
    form.classList.add('form');
    form.addEventListener('submit', function (event) {
      event.preventDefault();
      if (document.querySelector('#send-mail-message')) {
        document.querySelector('#send-mail-message').remove();
      }
      var message = document.createElement('div');
      message.id = 'send-mail-message';
      message.classList.add('form-message');
      form.appendChild(message);
      var submitButton = document.querySelector('#send-mail-submit');
      submitButton.disabled = true;
      var fd = new FormData(form);
      if (typeof csrf !== 'undefined') {
        fd.append(csrf.key, csrf.token);
      }
      fetch(environment.publicFolders.api + '/sendPic.php', {
        method: 'post',
        body: fd
      }).then(function (response) {
        return response.json();
      }).then(function (data) {
        if (data.success) {
          document.querySelector('#send-mail-recipient').value = '';
          message.classList.add('text-success');
          if (data.saved) {
            message.textContent = photoboothTools.getTranslation('mailSaved');
          } else {
            message.textContent = photoboothTools.getTranslation('mailSent');
          }
        } else {
          message.classList.add('text-danger');
          message.textContent = data.error;
        }
        submitButton.disabled = false;
      })["catch"](function () {
        message.classList.add('text-danger');
        message.textContent = photoboothTools.getTranslation('mailError');
        submitButton.disabled = false;
      });
    });
    body.appendChild(form);

    // Image
    var imageInput = document.createElement('input');
    imageInput.type = 'hidden';
    imageInput.name = 'image';
    imageInput.value = image;
    form.appendChild(imageInput);

    // Recipient
    var recipientInput = document.createElement('input');
    recipientInput.classList.add('form-input');
    recipientInput.id = 'send-mail-recipient';
    recipientInput.type = 'text';
    recipientInput.name = 'recipient';
    recipientInput.addEventListener('focusin', function (event) {
      // workaround for photoswipe blocking input
      event.stopImmediatePropagation();
    });
    form.appendChild(recipientInput);
    if (config.mail.virtualKeyboard) {
      virtualKeyboard.initialize(config.mail.keyboardLayout, '#send-mail-recipient', '#send-mail-form');
    }

    // Submit
    var submitLabel = config.mail.send_all_later ? photoboothTools.getTranslation('add') : photoboothTools.getTranslation('send');
    var submitButton = photoboothTools.button.create(submitLabel, 'fa fa-check', 'primary', 'modal-');
    submitButton.id = 'send-mail-submit';
    submitButton.addEventListener('click', function (event) {
      event.preventDefault();
      event.stopPropagation();
      if (typeof form.requestSubmit === 'function') {
        form.requestSubmit();
      } else {
        var tmpSubmit = document.createElement('button');
        tmpSubmit.type = 'submit';
        tmpSubmit.style.display = 'none';
        form.appendChild(tmpSubmit);
        tmpSubmit.click();
        form.removeChild(tmpSubmit);
      }
    });
    buttonbar.insertBefore(submitButton, buttonbar.firstChild);
  };
  api.showQrCode = function (filename) {
    if (!config.qr.enabled) {
      return;
    }
    photoboothTools.modal.open();
    var body = photoboothTools.modal.element.querySelector('.modal-body');
    var image = document.createElement('img');
    image.src = environment.publicFolders.api + '/qrcode.php?filename=' + filename;
    body.appendChild(image);
    var qrHelpText = config.qr.custom_text ? config.qr.text : photoboothTools.getTranslation('qrHelp') + '<br><b>' + config.webserver.ssid + '</b>';
    var text = document.createElement('p');
    text.innerHTML = qrHelpText;
    body.appendChild(text);
  };
  api.renderPic = function (filename, files) {
    api.filename = filename;
    if (config.print.auto && config.filters.enabled === false) {
      setTimeout(function () {
        photoboothTools.printImage(filename, 1, function () {
          remoteBuzzerClient.inProgress(false);
        });
      }, config.print.auto_delay);
    }
    buttonPrint.off('click').on('click', /*#__PURE__*/function () {
      var _ref6 = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee6(event) {
        var copies, _t3;
        return _regenerator().w(function (_context6) {
          while (1) switch (_context6.n) {
            case 0:
              event.preventDefault();
              event.stopPropagation();
              if (!(config.print.max_multi === 1)) {
                _context6.n = 1;
                break;
              }
              _t3 = 1;
              _context6.n = 3;
              break;
            case 1:
              _context6.n = 2;
              return photoboothTools.askCopies();
            case 2:
              _t3 = _context6.v;
            case 3:
              copies = _t3;
              if (copies && !isNaN(copies)) {
                photoboothTools.printImage(filename, copies, function () {
                  remoteBuzzerClient.inProgress(false);
                  buttonPrint.trigger('blur');
                });
              }
            case 4:
              return _context6.a(2);
          }
        }, _callee6);
      }));
      return function (_x8) {
        return _ref6.apply(this, arguments);
      };
    }());
    resultPage.find('[data-command="qrbtn"]').off('click').on('click', function (event) {
      event.preventDefault();
      api.showQrCode(filename);
    });
    resultPage.find('.deletebtn').off('click').on('click', /*#__PURE__*/function () {
      var _ref7 = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee7(ev) {
        var really, _t4;
        return _regenerator().w(function (_context7) {
          while (1) switch (_context7.n) {
            case 0:
              ev.preventDefault();
              if (!config["delete"].no_request) {
                _context7.n = 1;
                break;
              }
              _t4 = true;
              _context7.n = 3;
              break;
            case 1:
              _context7.n = 2;
              return photoboothTools.confirm(filename + ' ' + photoboothTools.getTranslation('really_delete_image'));
            case 2:
              _t4 = _context7.v;
            case 3:
              really = _t4;
              if (really) {
                files.forEach(function (file, index, array) {
                  photoboothTools.console.logDev('Index:', index);
                  photoboothTools.console.logDev('Array:', array);
                  api.deleteImage(file, function () {
                    return;
                  });
                });
                setTimeout(function () {
                  photoboothTools.reloadPage();
                }, notificationTimeout);
              } else {
                buttonDelete.trigger('blur');
              }
            case 4:
              return _context7.a(2);
          }
        }, _callee7);
      }));
      return function (_x9) {
        return _ref7.apply(this, arguments);
      };
    }());

    // gallery doesn't support videos atm
    if (!photoboothTools.isVideoFile(filename)) {
      api.addImage(filename);
    }

    // if image is a video render the qr code as image (video should be displayed over this)
    var imageUrl = photoboothTools.isVideoFile(filename) ? environment.publicFolders.api + '/qrcode.php?filename=' + filename : environment.publicFolders.images + '/' + filename;
    var preloadImage = new Image();
    preloadImage.onload = function () {
      startPage.removeClass('stage--active');
      resultPage.css({
        '--stage-background-image': "url(".concat(imageUrl, "?filter=").concat(imgFilter, ")")
      });
      resultPage.attr('data-img', filename);
      resultPage.addClass('stage--active');
      loader.removeClass('stage--active showBackgroundImage');
      loader.css('background-image', '');
      if (config.qr.enabled && config.qr.result != 'hidden') {
        if (document.getElementById('resultQR')) {
          document.getElementById('resultQR').remove();
        }
        var qrWrapper = document.createElement('div');
        qrWrapper.id = 'resultQR';
        qrWrapper.setAttribute('class', 'stage-code ' + config.qr.result);
        var qrResultImage = document.createElement('img');
        qrResultImage.addEventListener('load', function () {
          resultPage.append(qrWrapper);
        });
        qrResultImage.src = environment.publicFolders.api + '/qrcode.php?filename=' + filename;
        qrResultImage.alt = 'QR-Code';
        qrResultImage.classList.add('stage-code__image');
        qrWrapper.append(qrResultImage);
        var qrShortText = config.qr.short_text;
        if (qrShortText && qrShortText.length > 0) {
          var qrCaption = document.createElement('p');
          qrCaption.classList.add('stage-code__caption');
          qrCaption.textContent = qrShortText;
          qrWrapper.append(qrCaption);
        }
      }
      if (!filternav.hasClass('sidenav--open')) {
        rotaryController.focusSet(resultPage);
      }
    };
    preloadImage.src = imageUrl;
    api.takingPic = false;
    remoteBuzzerClient.inProgress(false);
    photoboothTools.console.logDev('Taking picture in progress: ' + api.takingPic);
    api.resetTimeOut();
    if (config.commands.preview && !config.preview.bsm) {
      photoboothTools.console.logDev('Preview: core: start video from api.renderPic');
      photoboothPreview.startVideo(CameraDisplayMode.INIT);
    }
    if (config.commands.post_photo) {
      api.shellCommand('post-command', filename);
    }
    api.screensaver.resetTimer();
  };
  api.addImage = function (imageName) {
    if (!config.gallery.enabled) {
      return;
    }
    var useThumb = config.gallery.use_thumb;
    var thumbImg = new Image();
    var bigImg = new Image();
    var thumbSize = '';
    var bigSize = '';
    var bigSizeW = '';
    var bigSizeH = '';
    var maxSizeW = config.gallery.picture_width || 800;
    var maxSizeH = config.gallery.picture_height || 600;
    var imgtoLoad = 2;
    thumbImg.onload = function () {
      thumbSize = this.width + 'x' + this.height;
      if (--imgtoLoad === 0) {
        allLoaded();
      }
    };
    bigImg.onload = function () {
      bigSizeW = this.width;
      bigSizeH = this.height;
      bigSize = bigSizeW + 'x' + bigSizeH;
      // Calculate PSWP dimensions to max 800x600 while keeping aspect ratio
      var aspectRatio = bigSizeW / bigSizeH;
      if (aspectRatio >= 1) {
        // Landscape or square
        if (bigSizeW > maxSizeW) {
          bigSizeW = maxSizeW;
          bigSizeH = Math.round(bigSizeW / aspectRatio);
        } else if (bigSizeH > maxSizeH) {
          bigSizeH = maxSizeH;
          bigSizeW = Math.round(bigSizeH * aspectRatio);
        }
      } else {
        // Portrait
        if (bigSizeH > maxSizeH) {
          bigSizeH = maxSizeH;
          bigSizeW = Math.round(bigSizeH * aspectRatio);
        } else if (bigSizeW > maxSizeW) {
          bigSizeW = maxSizeW;
          bigSizeH = Math.round(bigSizeW / aspectRatio);
        }
      }
      if (--imgtoLoad === 0) {
        allLoaded();
      }
    };
    bigImg.src = environment.publicFolders.images + '/' + imageName;
    var thumbUrl = (useThumb ? environment.publicFolders.thumbs : environment.publicFolders.images) + '/' + imageName;
    thumbImg.src = thumbUrl;
    function allLoaded() {
      var linkElement = $('<a>').html(thumbImg);
      linkElement.attr('class', 'gallery-list-item rotaryfocus');
      linkElement.attr('data-size', bigSize);
      linkElement.attr('data-pswp-width', bigSizeW);
      linkElement.attr('data-pswp-height', bigSizeH);
      linkElement.attr('href', environment.publicFolders.images + '/' + imageName);
      linkElement.attr('data-med', thumbUrl);
      linkElement.attr('data-med-size', thumbSize);
      if (config.gallery.newest_first) {
        linkElement.prependTo(galimages);
      } else {
        linkElement.appendTo(galimages);
      }
      galimages.children().not('a').remove();
    }
  };
  api.openGallery = function () {
    if (config.gallery.scrollbar) {
      gallery.addClass('scrollbar');
    }
    gallery.addClass('gallery--open');
    setTimeout(function () {
      gallery.find('.gallery__inner').show();
      rotaryController.focusSet(gallery);
    }, 300);
  };
  api.closeGallery = function () {
    if (typeof globalGalleryHandle !== 'undefined') {
      if (globalGalleryHandle.pswp) {
        globalGalleryHandle.pswp.close();
      }
    }
    gallery.find('.gallery__inner').hide();
    gallery.removeClass('gallery--open');
    if (resultPage.is(':visible')) {
      rotaryController.focusSet(resultPage);
    } else if (startPage.is(':visible')) {
      rotaryController.focusSet(startPage);
    }
  };
  api.deleteImage = function (imageName, cb) {
    var errorMsg = photoboothTools.getTranslation('error') + '</br>' + photoboothTools.getTranslation('auto_reload');
    $.ajax({
      url: environment.publicFolders.api + '/deletePhoto.php',
      method: 'POST',
      data: _defineProperty({
        file: imageName
      }, csrf.key, csrf.token),
      success: function success(data) {
        if (data.success) {
          var msg = data.file + ' ' + photoboothTools.getTranslation('deleted_successfully') + '</br>' + photoboothTools.getTranslation('auto_reload');
          photoboothTools.console.log('Deleted ' + data.file);
          photoboothTools.overlay.showSuccess(msg);
        } else {
          photoboothTools.console.log('Error while deleting ' + data.file);
          photoboothTools.console.log('Failed: ' + data.failed);
          photoboothTools.overlay.showError(errorMsg);
        }
        setTimeout(function () {
          return photoboothTools.overlay.close();
        }, notificationTimeout);
        cb(data);
      },
      error: function error(jqXHR, textStatus) {
        photoboothTools.console.log('Error while deleting image: ', textStatus);
        photoboothTools.overlay.showError(errorMsg);
        setTimeout(function () {
          return photoboothTools.reloadPage();
        }, notificationTimeout);
      }
    });
  };
  $('.imageFilter').on('click', function (e) {
    e.preventDefault();
    api.navbar.toggle();
  });
  $('.sidenav-list-item[data-filter]').on('click', function () {
    if (isProcessingEffects) {
      photoboothTools.console.logDev('Ignoring filter click: processing in progress.');
      return;
    }
    $('.sidenav').find('.sidenav-list-item--active').removeClass('sidenav-list-item--active');
    $(this).addClass('sidenav-list-item--active');
    imgFilter = $(this).data('filter');
    var result = {
      file: resultPage.attr('data-img')
    };
    photoboothTools.console.logDev('Applying filter: ' + imgFilter, result);
    api.processPic(result);
    rotaryController.focusSet(filternav);
  });
  $('.takePic, .newpic').on('click', function (e) {
    e.preventDefault();
    api.thrill(PhotoStyle.PHOTO);
    $(this).trigger('blur');
  });
  $('.takeCollage, .newcollage').on('click', function (e) {
    e.preventDefault();
    if (config.collage.enabled && config.collage.allow_selection && $('#collageSelectorModal').length) {
      $('#collageSelectorModal').data('pending-start', true);
      $('#collageSelectorModal').removeClass('hidden').attr('aria-hidden', 'false');
      $(this).trigger('blur');
      return;
    }
    api.thrill(PhotoStyle.COLLAGE);
    $(this).trigger('blur');
  });
  $('.takeCustom, .newcustom').on('click', function (e) {
    e.preventDefault();
    api.thrill(PhotoStyle.CUSTOM);
    $(this).trigger('blur');
  });
  $('.takeVideo, .newVideo').on('click', function (e) {
    e.preventDefault();
    api.thrill(PhotoStyle.VIDEO);
    $(this).trigger('blur');
  });
  $('[data-command="sidenav-close"]').on('click', function (e) {
    e.preventDefault();
    api.navbar.close();
    rotaryController.focusSet(resultPage);
  });
  $('.gallery-button, .gallerybtn').on('click', function (e) {
    e.preventDefault();
    api.navbar.close();
    api.openGallery($(this));
  });
  $('[data-command="gallery__refresh"]').on('click', function (e) {
    e.preventDefault();
    photoboothTools.reloadPage();
  });
  $('[data-command="gallery__close"]').on('click', function (e) {
    e.preventDefault();
    api.closeGallery();
  });
  $('.mailbtn').on('click touchstart', function (e) {
    e.preventDefault();
    e.stopPropagation();
    var img = resultPage.attr('data-img');
    api.showMailForm(img);
  });
  resultPage.on('click', function () {
    if (!filternav.hasClass('sidenav--open')) {
      rotaryController.focusSet(resultPage);
    }
  });
  $('.homebtn').on('click', function (e) {
    e.preventDefault();
    e.stopPropagation();
    photoboothTools.reloadPage();
    rotaryController.focusSet(startPage);
  });
  if (screensaverEnabled) {
    $(document).on('click touchstart keydown mousemove', function () {
      api.screensaver.resetTimer();
    });
    screensaverOverlay.on('click touchstart', function (e) {
      e.preventDefault();
      api.screensaver.resetTimer();
    });
  }
  $('.cups-button').on('click', function (ev) {
    ev.preventDefault();
    var url = "http://".concat(location.hostname, ":631/jobs/");
    var features = 'width=1024,height=600,left=0,top=0,screenX=0,screenY=0,resizable=NO,scrollbars=NO';
    window.open(url, 'newwin', features);
  });
  $('.print-unlock-button').on('click', function (e) {
    var _this = this;
    e.preventDefault();
    photoboothTools.overlay.showWaiting(photoboothTools.getTranslation('wait_message'));
    $.ajax({
      method: 'GET',
      url: environment.publicFolders.api + '/printDB.php',
      data: _defineProperty({
        action: 'unlockPrint'
      }, csrf.key, csrf.token),
      success: function success(data) {
        if (data.success) {
          photoboothTools.overlay.showSuccess(photoboothTools.getTranslation('success'));
          $(_this).addClass('hidden');
        } else {
          photoboothTools.overlay.showError(photoboothTools.getTranslation('error'));
        }
        setTimeout(function () {
          photoboothTools.overlay.close();
        }, 2000);
      }
    });
  });
  api.handleButtonPressWhileTakingPic = function () {
    if (api.nextCollageNumber > 0) {
      var btnCollageNext = $('#btnCollageNext');
      var btnCollageProcess = $('#btnCollageProcess');
      if (btnCollageNext.length) {
        photoboothTools.console.logDev('Next collage image triggered by keypress.');
        btnCollageNext.trigger('click');
      } else if (btnCollageProcess.length) {
        photoboothTools.console.logDev('Processing collage triggered by keypress.');
        btnCollageProcess.trigger('click');
      } else {
        photoboothTools.console.logDev('Taking picture already in progress!');
      }
    } else {
      photoboothTools.console.logDev('Taking picture already in progress!');
    }
  };
  $(document).on('keyup', function (ev) {
    if (api.isTimeOutPending()) {
      if (typeof onStandaloneGalleryView !== 'undefined' || startPage.is(':visible')) {
        clearTimeout(timeOut);
        photoboothTools.console.logDev('Timeout for auto reload cleared.');
      } else {
        api.resetTimeOut();
      }
    }
    if (config.selfie_mode) {
      return;
    }
    if (typeof onStandaloneGalleryView === 'undefined' && typeof onCaptureChromaView === 'undefined') {
      if (config.picture.key && parseInt(config.picture.key, 10) === ev.keyCode || config.collage.key && parseInt(config.collage.key, 10) === ev.keyCode || config.custom.key && parseInt(config.custom.key, 10) === ev.keyCode) {
        if (api.takingPic) {
          api.handleButtonPressWhileTakingPic();
          return;
        }
        api.closeGallery();
      } else if (config.print.from_result && config.print.key && parseInt(config.print.key, 10) === ev.keyCode) {
        if (photoboothTools.isPrinting) {
          photoboothTools.console.log('Printing already in progress!');
        } else {
          buttonPrint.trigger('click');
        }
        return;
      } else {
        return;
      }

      // picture
      if (config.picture.key && parseInt(config.picture.key, 10) === ev.keyCode) {
        if (config.picture.enabled) {
          api.thrill(PhotoStyle.PHOTO);
        } else {
          photoboothTools.console.logDev('Picture key pressed, but taking pictures disabled. Please enable picture in your config.');
        }
      }

      // collage
      if (config.collage.key && parseInt(config.collage.key, 10) === ev.keyCode) {
        if (config.collage.enabled) {
          api.thrill(PhotoStyle.COLLAGE);
        } else {
          photoboothTools.console.logDev('Collage key pressed, but Collage disabled. Please enable collage in your config.');
        }
      }

      // custom
      if (config.custom.key && parseInt(config.custom.key, 10) === ev.keyCode) {
        if (config.custom.enabled) {
          api.thrill(PhotoStyle.CUSTOM);
        } else {
          photoboothTools.console.logDev('Custom key pressed, but custom action disabled. Please enable custom action in your config.');
        }
      }
    }
  });
  $(document).on('click', function () {
    if (api.isTimeOutPending()) {
      if (typeof onStandaloneGalleryView !== 'undefined' || startPage.is(':visible')) {
        clearTimeout(timeOut);
        photoboothTools.console.logDev('Timeout for auto reload cleared.');
      } else {
        api.resetTimeOut();
      }
    }
  });

  // Disable Right-Click
  if (config.dev.loglevel > 0) {
    $(this).on('contextmenu', function (e) {
      e.preventDefault();
    });
  }
  if (typeof onStandaloneGalleryView === 'undefined' && typeof onCaptureChromaView === 'undefined' && config.collage.enabled && config.collage.allow_selection) {
    var collageModal = $('#collageSelectorModal');
    var closeBtn = $('#collageSelectorClose');
    var optionButtons = $('.collageSelector__option');

    // Move modal to body so it isn't hidden by stage visibility
    if (collageModal.length) {
      $('body').append(collageModal.detach());
    }
    var setSelection = function setSelection(layout, limit) {
      api.collageLayout = layout;
      api.collageLimit = parseInt(limit, 10);
    };
    var closeModal = function closeModal() {
      collageModal.addClass('hidden');
      collageModal.attr('aria-hidden', 'true');
    };
    closeBtn.on('click', closeModal);
    optionButtons.on('click', function () {
      var button = $(this);
      setSelection(button.data('layout'), button.data('limit'));
      closeModal();
      api.thrill(PhotoStyle.COLLAGE);
    });
  }
  previewVideo.on('loadedmetadata', function (ev) {
    var videoEl = ev.target;
    var newWidth = videoEl.offsetWidth;
    var newHeight = videoEl.offsetHeight;
    if (config.preview.style === PreviewStyle.SCALE_DOWN.valueOf()) {
      newWidth = videoEl.videoWidth;
      newHeight = videoEl.videoHeight;
    }
    if (newWidth !== 0 && newHeight !== 0) {
      previewFramePicture.css({
        width: newWidth,
        height: newHeight
      });
      previewFrameCollage.css({
        width: newWidth,
        height: newHeight
      });
    }
  });
  return api;
}();
$(function () {
  photoBooth.init();
});