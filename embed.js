/**
 * PerkLedger Universal Pure Dynamic Embed Engine v1.1.0
 * 
 * Zero class-level visual style leakage.
 * 100% Parameter-driven inline styling zapped directly onto elements.
 * Ultra-robust DOM-ready & Rocket Loader safe append mechanism.
 * Smart Auto-Suppression: Automatically suppresses floating launcher on pages with inline embed target.
 * Dynamic Side-Drawer Slide Animations (Left, Right, Bottom-Center).
 * Modern Dynamic Auto-Resize Bridge via postMessage.
 * URL Hash Auto-Open Trigger (#reward, #club, #loyalty).
 */
(function () {
  // DOM-ready safe append helper for all browsers & Rocket Loader
  function safeAppend(elem) {
    if (document.body) {
      document.body.appendChild(elem);
    } else if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', function () {
        if (document.body) document.body.appendChild(elem);
      });
    } else {
      setTimeout(function () {
        if (document.body) document.body.appendChild(elem);
      }, 50);
    }
  }

  // Purge any pre-existing overlay or launcher elements from DOM on load
  function purgeOld() {
    try {
      var oldOverlays = document.querySelectorAll('.perkledger-modal-overlay');
      for (var o = 0; o < oldOverlays.length; o++) {
        if (oldOverlays[o] && oldOverlays[o].parentNode) {
          oldOverlays[o].parentNode.removeChild(oldOverlays[o]);
        }
      }
      var oldLaunchers = document.querySelectorAll('.perkledger-launcher');
      for (var l = 0; l < oldLaunchers.length; l++) {
        if (oldLaunchers[l] && oldLaunchers[l].parentNode) {
          oldLaunchers[l].parentNode.removeChild(oldLaunchers[l]);
        }
      }
    } catch(e) {}
  }
  
  if (document.body) {
    purgeOld();
  } else if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', purgeOld);
  } else {
    setTimeout(purgeOld, 50);
  }

  // Find active script element
  var currentScript = document.currentScript || (function () {
    var scripts = document.getElementsByTagName('script');
    for (var i = scripts.length - 1; i >= 0; i--) {
      if (scripts[i].src && scripts[i].src.indexOf('embed.js') !== -1) {
        return scripts[i];
      }
    }
    return null;
  })();

  // Pure parameter retriever (Strict order: currentScript attributes -> window globals)
  function getAttr(attrName, globalVarName) {
    if (currentScript && currentScript.getAttribute) {
      var val = currentScript.getAttribute(attrName);
      if (val !== null && val !== undefined && val !== '') return val;
    }
    if (window[globalVarName] !== undefined && window[globalVarName] !== null && window[globalVarName] !== '') {
      return window[globalVarName];
    }
    return null;
  }

  // Helper to convert Hex + Alpha to RGBA string strictly
  function hexToRgba(hex, alpha) {
    if (!hex) return null;
    if (hex.indexOf('rgba') === 0 || hex.indexOf('rgb') === 0) return hex;
    var c = hex.replace('#', '');
    if (c.length === 3) c = c[0] + c[0] + c[1] + c[1] + c[2] + c[2];
    var r = parseInt(c.substring(0, 2), 16) || 0;
    var g = parseInt(c.substring(2, 4), 16) || 0;
    var b = parseInt(c.substring(4, 6), 16) || 0;
    return 'rgba(' + r + ', ' + g + ', ' + b + ', ' + (alpha !== null ? alpha : '1') + ')';
  }

  // Extract parameters strictly from merchant config (No dummy fallbacks allowed)
  var merchantId = getAttr('data-merchant', 'PerkLedgerMerchantId');
  if (!merchantId) {
    console.warn('[PerkLedger Dynamic Embed] Merchant ID is required.');
    return;
  }

  var theme = getAttr('data-theme', 'PerkLedgerTheme');
  var modalRadius = getAttr('data-modal-radius', 'PerkLedgerModalRadius');
  var modalBorderColor = getAttr('data-modal-border-color', 'PerkLedgerModalBorderColor');
  var modalBorderWidth = getAttr('data-modal-border-width', 'PerkLedgerModalBorderWidth');
  var backdropColor = getAttr('data-backdrop-color', 'PerkLedgerBackdropColor');
  var backdropOpacity = getAttr('data-backdrop-opacity', 'PerkLedgerBackdropOpacity');
  var backdropBlur = getAttr('data-backdrop-blur', 'PerkLedgerBackdropBlur');

  var rawLauncher = getAttr('data-launcher', 'PerkLedgerLauncher');
  // STRICT RULE: Floating launcher MUST ONLY appear if explicitly set to 'yes', 'true', or '1'
  var hasLauncher = rawLauncher === 'yes' || rawLauncher === 'true' || rawLauncher === '1';
  var launcherStyle = getAttr('data-launcher-style', 'PerkLedgerLauncherStyle');
  var launcherText = getAttr('data-launcher-text', 'PerkLedgerLauncherText');
  var launcherHideText = getAttr('data-launcher-hide-text', 'PerkLedgerLauncherHideText') === 'yes' || getAttr('data-launcher-hide-text', 'PerkLedgerLauncherHideText') === '1';
  var launcherBg = getAttr('data-launcher-bg', 'PerkLedgerLauncherBg');
  var launcherTextColor = getAttr('data-launcher-text-color', 'PerkLedgerLauncherTextColor');
  var launcherIconColor = getAttr('data-launcher-icon-color', 'PerkLedgerLauncherIconColor');
  var launcherBorderColor = getAttr('data-launcher-border-color', 'PerkLedgerLauncherBorderColor');
  var launcherBorderWidth = getAttr('data-launcher-border-width', 'PerkLedgerLauncherBorderWidth');
  var launcherIcon = getAttr('data-launcher-icon', 'PerkLedgerLauncherIcon');
  var launcherRadius = getAttr('data-launcher-radius', 'PerkLedgerLauncherRadius') || 'pill';
  var position = getAttr('data-position', 'PerkLedgerPosition') || 'bottom-right';

  var hashTriggerEnabled = getAttr('data-hash-trigger', 'PerkLedgerHashTrigger') === 'yes' || getAttr('data-hash-trigger', 'PerkLedgerHashTrigger') === '1';
  var customHashName = getAttr('data-hash-name', 'PerkLedgerHashName') || 'reward';

  // Derive dynamic modal corner radius
  var radiusPx = '24px';
  if (modalRadius === 'sharp') radiusPx = '6px';
  else if (modalRadius === 'subtle') radiusPx = '12px';
  else if (modalRadius === 'rounded') radiusPx = '24px';
  else if (modalRadius && modalRadius.indexOf('px') !== -1) radiusPx = modalRadius;

  // Derive launcher corner radius
  var launcherRadiusPx = '9999px';
  if (launcherRadius === 'sharp') launcherRadiusPx = '6px';
  else if (launcherRadius === 'subtle') launcherRadiusPx = '12px';
  else if (launcherRadius === 'pill' || launcherRadius === 'rounded') launcherRadiusPx = '9999px';

  // Derive border styling dynamically
  var drawerBorderCss = null;
  if (modalBorderWidth && modalBorderWidth !== '0px' && modalBorderColor) {
    drawerBorderCss = modalBorderWidth + ' solid ' + modalBorderColor;
  } else if (modalBorderWidth === '0px') {
    drawerBorderCss = 'none';
  }

  var launcherBorderCss = null;
  if (launcherBorderWidth && launcherBorderWidth !== '0px' && launcherBorderColor) {
    launcherBorderCss = launcherBorderWidth + ' solid ' + launcherBorderColor;
  } else if (launcherBorderWidth === '0px') {
    launcherBorderCss = 'none';
  }

  // Target pass URL strictly uses official authorized domain pass.perkledger.com
  var passUrl = 'https://pass.perkledger.com/?m=' + encodeURIComponent(merchantId) + '&embed=true';
  if (theme) {
      passUrl += '&theme=' + encodeURIComponent(theme);
  }

  // Dynamic Material SVG Icons (Clean vector SVGs)
  var MATERIAL_ICONS_SVG = {
    'card_membership': '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>',
    'stars': '<svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
    'card_giftcard': '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><line x1="12" y1="22" x2="12" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>',
    'loyalty': '<svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>',
    'local_offer': '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>',
    'workspace_premium': '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"/></svg>',
    'confirmation_number': '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v2z"/><line x1="13" y1="5" x2="13" y2="19" stroke-dasharray="2 2"/></svg>'
  };

  var activeIconSvg = (launcherIcon && MATERIAL_ICONS_SVG[launcherIcon]) ? MATERIAL_ICONS_SVG[launcherIcon] : null;
  var CLOSE_ICON_SVG = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';

  // Inject ONLY Scoped CSS (NEVER mutate global website scrollbars or styles!)
  var styleId = 'perkledger-embed-styles';
  var existingStyle = document.getElementById(styleId);
  if (existingStyle && existingStyle.parentNode) existingStyle.parentNode.removeChild(existingStyle);

  var style = document.createElement('style');
  style.id = styleId;
  style.textContent = `
    .perkledger-modal-overlay {
      position: fixed !important;
      top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important;
      z-index: 999999 !important;
      display: flex !important;
      opacity: 0 !important;
      visibility: hidden !important;
      transition: opacity 0.3s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
      padding: 20px !important;
      box-sizing: border-box !important;
      margin: 0 !important;
    }
    .perkledger-modal-overlay.active {
      opacity: 1 !important;
      visibility: visible !important;
    }

    /* Position: Bottom Right (Right Drawer Slide) */
    .perkledger-modal-overlay.pos-bottom-right {
      justify-content: flex-end !important;
      align-items: flex-end !important;
    }
    .perkledger-modal-overlay.pos-bottom-right .perkledger-drawer {
      transform: translateX(120%) scale(0.96) !important;
    }
    .perkledger-modal-overlay.pos-bottom-right.active .perkledger-drawer {
      transform: translateX(0) scale(1) !important;
    }

    /* Position: Bottom Left (Left Drawer Slide) */
    .perkledger-modal-overlay.pos-bottom-left {
      justify-content: flex-start !important;
      align-items: flex-end !important;
    }
    .perkledger-modal-overlay.pos-bottom-left .perkledger-drawer {
      transform: translateX(-120%) scale(0.96) !important;
    }
    .perkledger-modal-overlay.pos-bottom-left.active .perkledger-drawer {
      transform: translateX(0) scale(1) !important;
    }

    /* Position: Bottom Center (Bottom Up Slide) */
    .perkledger-modal-overlay.pos-bottom-center {
      justify-content: center !important;
      align-items: flex-end !important;
    }
    .perkledger-modal-overlay.pos-bottom-center .perkledger-drawer {
      transform: translateY(120%) scale(0.96) !important;
    }
    .perkledger-modal-overlay.pos-bottom-center.active .perkledger-drawer {
      transform: translateY(0) scale(1) !important;
    }

    .perkledger-drawer {
      width: 100% !important;
      max-width: 440px !important;
      height: calc(100vh - 40px) !important;
      max-height: calc(100vh - 40px) !important;
      box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.6) !important;
      overflow: hidden !important;
      position: relative !important;
      transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1) !important;
      box-sizing: border-box !important;
      margin: 0 !important;
      background: transparent !important;
    }
    
    .perkledger-launcher {
      position: fixed !important;
      bottom: 24px !important;
      z-index: 999998 !important;
      cursor: pointer !important;
      display: flex !important;
      align-items: center !important;
      box-shadow: 0 12px 30px rgba(0,0,0,0.3) !important;
      transition: transform 0.25s ease, box-shadow 0.25s ease !important;
      animation: perkledgerLauncherFloat 3.5s ease-in-out infinite alternate !important;
      box-sizing: border-box !important;
    }
    @keyframes perkledgerLauncherFloat {
      0% { transform: translateY(0px); box-shadow: 0 10px 25px rgba(0,0,0,0.25); }
      100% { transform: translateY(-7px); box-shadow: 0 20px 38px rgba(0,0,0,0.4); }
    }

    .perkledger-launcher.style-with-label {
      padding: 12px 20px !important;
      gap: 10px !important;
      font-size: 14px !important;
      font-weight: 700 !important;
    }
    .perkledger-launcher.style-icon-only {
      width: 56px !important;
      height: 56px !important;
      border-radius: 50% !important;
      padding: 0 !important;
      justify-content: center !important;
    }
    .perkledger-launcher:hover {
      transform: translateY(-4px) scale(1.04) !important;
      box-shadow: 0 22px 42px rgba(0,0,0,0.45) !important;
    }
    .perkledger-launcher.position-bottom-right { right: 24px !important; left: auto !important; }
    .perkledger-launcher.position-bottom-left { left: 24px !important; right: auto !important; }
    .perkledger-launcher.position-bottom-center { 
      left: 50% !important; right: auto !important;
      animation: perkledgerLauncherFloatCenter 3.5s ease-in-out infinite alternate !important;
    }
    @keyframes perkledgerLauncherFloatCenter {
      0% { transform: translateX(-50%) translateY(0px); box-shadow: 0 10px 25px rgba(0,0,0,0.25); }
      100% { transform: translateX(-50%) translateY(-7px); box-shadow: 0 20px 38px rgba(0,0,0,0.4); }
    }
    .perkledger-launcher.position-bottom-center:hover { 
      transform: translateX(-50%) translateY(-4px) scale(1.04) !important; 
    }

    /* Perfectly Circular High-Contrast Close Button */
    .perkledger-close-btn {
      position: absolute !important;
      top: 14px !important;
      right: 14px !important;
      z-index: 99999 !important;
      width: 36px !important;
      height: 36px !important;
      min-width: 36px !important;
      min-height: 36px !important;
      border-radius: 50% !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      cursor: pointer !important;
      padding: 0 !important;
      box-shadow: 0 4px 14px rgba(0, 0, 0, 0.4) !important;
      transition: transform 0.2s ease, filter 0.2s ease !important;
      outline: none !important;
      border: 1px solid rgba(255, 255, 255, 0.22) !important;
      box-sizing: border-box !important;
    }
    .perkledger-close-btn svg {
      width: 18px !important;
      height: 18px !important;
      display: block !important;
      pointer-events: none !important;
    }
    .perkledger-close-btn:hover {
      transform: scale(1.1) rotate(90deg) !important;
      filter: brightness(1.2) !important;
    }
    .perkledger-close-btn:active {
      transform: scale(0.95) !important;
    }

    .perkledger-iframe {
      width: 100% !important;
      height: 100% !important;
      border: none !important;
      background: transparent !important;
      display: block !important;
      margin: 0 !important;
      padding: 0 !important;
    }
  `;
  document.head.appendChild(style);

  // Check if inline target element exists on page (Smart Auto-Suppression)
  var inlineTarget = document.getElementById('perkledger-widget') || document.querySelector('[data-perkledger-pass]');
  if (inlineTarget) {
    // Style inline container to be a sleek 440px centered native mobile pass card
    inlineTarget.style.maxWidth = '440px';
    inlineTarget.style.margin = '30px auto';
    inlineTarget.style.width = '100%';
    inlineTarget.style.minHeight = '740px';
    inlineTarget.style.borderRadius = radiusPx;
    inlineTarget.style.overflow = 'hidden';
    inlineTarget.style.transition = 'height 0.2s ease';
    if (drawerBorderCss && drawerBorderCss !== 'none') {
      inlineTarget.style.border = drawerBorderCss;
    } else {
      inlineTarget.style.border = '1px solid rgba(0, 0, 0, 0.08)';
    }
    inlineTarget.style.boxShadow = '0 12px 40px rgba(0, 0, 0, 0.12)';
    inlineTarget.style.background = 'transparent';

    if (!inlineTarget.querySelector('iframe')) {
      var iframe = document.createElement('iframe');
      iframe.src = passUrl;
      iframe.className = 'perkledger-iframe';
      iframe.setAttribute('allow', 'camera; microphone; payment');
      iframe.setAttribute('scrolling', 'no');
      iframe.style.width = '100%';
      iframe.style.minHeight = '740px';
      iframe.style.border = 'none';
      iframe.style.display = 'block';
      iframe.style.transition = 'height 0.2s ease';
      
      inlineTarget.appendChild(iframe);

      var lastReportedH = 0;
      window.addEventListener('message', function (event) {
        if (event.data && event.data.type === 'PERKLEDGER_RESIZE' && event.data.height) {
          var h = Math.round(Number(event.data.height));
          if (h > 150 && Math.abs(h - lastReportedH) > 4) {
            lastReportedH = h;
            iframe.style.height = h + 'px';
            iframe.style.minHeight = h + 'px';
            inlineTarget.style.height = h + 'px';
            inlineTarget.style.minHeight = h + 'px';
          }
        }
      });
    }

    // Suppress floating launcher on this dedicated page
    hasLauncher = false;
  }

  // Create Modal Overlay & Side-Drawer Container ONLY IF launcher is explicitly enabled OR hash trigger is enabled
  if (hasLauncher || hashTriggerEnabled) {
    var overlay = document.createElement('div');
    overlay.className = 'perkledger-modal-overlay pos-' + position;

    // Zap backdrop styling directly via parameter parameters
    var alpha = backdropOpacity ? parseFloat(backdropOpacity) : 0.65;
    var bgRgba = hexToRgba(backdropColor || '#000000', alpha);
    overlay.style.backgroundColor = bgRgba;
    
    if (backdropBlur && backdropBlur !== '0px') {
      overlay.style.backdropFilter = 'blur(' + backdropBlur + ')';
      overlay.style.webkitBackdropFilter = 'blur(' + backdropBlur + ')';
    } else {
      overlay.style.backdropFilter = 'none';
      overlay.style.webkitBackdropFilter = 'none';
    }

    var drawer = document.createElement('div');
    drawer.className = 'perkledger-drawer';
    
    if (radiusPx) {
      drawer.style.borderRadius = radiusPx;
    }
    if (drawerBorderCss) {
      drawer.style.border = drawerBorderCss;
    }

    // Create Circular Close Button matching launcher colors
    var closeBtn = document.createElement('button');
    closeBtn.className = 'perkledger-close-btn';
    closeBtn.innerHTML = CLOSE_ICON_SVG;
    closeBtn.setAttribute('aria-label', 'Close Modal');
    
    var closeBg = launcherBg || '#0F172A';
    var closeIconColor = launcherIconColor || '#FCBD0B';
    closeBtn.style.backgroundColor = closeBg;
    closeBtn.style.color = closeIconColor;

    var passIframe = document.createElement('iframe');
    passIframe.src = passUrl;
    passIframe.className = 'perkledger-iframe';
    passIframe.setAttribute('allow', 'camera; microphone; payment');

    drawer.appendChild(closeBtn);
    drawer.appendChild(passIframe);
    overlay.appendChild(drawer);
    safeAppend(overlay);

    // Overlay Open & Close Actions
    function openModal() {
      overlay.classList.add('active');
      document.body.style.overflow = 'hidden';
    }

    function closeModal() {
      overlay.classList.remove('active');
      document.body.style.overflow = '';
    }

    closeBtn.addEventListener('click', closeModal);
    overlay.addEventListener('click', function (e) {
      if (e.target === overlay) closeModal();
    });

    // URL Hash Auto-Open Trigger (#reward or custom hash)
    function checkHashTrigger() {
      if (!hashTriggerEnabled) return;
      var currentHash = window.location.hash ? window.location.hash.replace('#', '').toLowerCase() : '';
      var targetHash = (customHashName || 'reward').toLowerCase();
      if (currentHash === targetHash) {
        setTimeout(openModal, 300);
      }
    }
    checkHashTrigger();
    window.addEventListener('hashchange', checkHashTrigger);

    // Inject Floating Launcher Bubble ONLY IF explicitly enabled
    if (hasLauncher) {
      var launcher = document.createElement('div');
      var isIconOnly = launcherStyle === 'icon-only' || launcherHideText;
      
      launcher.className = 'perkledger-launcher position-' + position + (isIconOnly ? ' style-icon-only' : ' style-with-label');
      
      if (launcherBg) launcher.style.backgroundColor = launcherBg;
      if (launcherTextColor) launcher.style.color = launcherTextColor;
      if (launcherBorderCss) launcher.style.border = launcherBorderCss;
      if (!isIconOnly && launcherRadiusPx) {
        launcher.style.borderRadius = launcherRadiusPx;
      }

      var iconHtml = activeIconSvg ? ('<span style="display:inline-flex; align-items:center; color:' + (launcherIconColor || 'currentColor') + ';">' + activeIconSvg + '</span>') : '';
      
      if (isIconOnly) {
        launcher.innerHTML = iconHtml ? iconHtml : ('<span style="color:' + (launcherIconColor || 'currentColor') + ';">💳</span>');
      } else {
        var label = launcherText || 'Rewards Pass';
        launcher.innerHTML = iconHtml + '<span>' + label + '</span>';
      }

      launcher.addEventListener('click', openModal);
      safeAppend(launcher);
    }
  }
})();
