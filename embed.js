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

  // Find active script element (Strict order: document.currentScript -> querySelector script[data-merchant] -> script src)
  var currentScript = document.currentScript || (function () {
    var scripts = document.getElementsByTagName('script');
    for (var i = scripts.length - 1; i >= 0; i--) {
      var s = scripts[i];
      if (s.getAttribute && (s.getAttribute('data-merchant') || (s.src && s.src.indexOf('embed.js') !== -1))) {
        return s;
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
  console.log('[PerkLedger Dynamic Embed] Initialized for merchant:', merchantId);

  var theme = getAttr('data-theme', 'PerkLedgerTheme');
  // Branding & Layout Parameters (The new standard)
  var themeBg = getAttr('data-theme-bg', 'PerkLedgerThemeBg');
  var themeSurface = getAttr('data-theme-surface', 'PerkLedgerThemeSurface');
  var themeText = getAttr('data-theme-text', 'PerkLedgerThemeText');
  var themeAccent = getAttr('data-theme-accent', 'PerkLedgerThemeAccent');
  var fontSize = getAttr('data-font-size', 'PerkLedgerFontSize');
  var inlineWidth = getAttr('data-inline-width', 'PerkLedgerInlineWidth') || '100%';

  // Smart Fallbacks for older specific parameters
  var modalBorderColor = getAttr('data-modal-border-color', 'PerkLedgerModalBorderColor') || (themeAccent ? hexToRgba(themeAccent, '0.4') : null);
  var modalBorderWidth = getAttr('data-modal-border-width', 'PerkLedgerModalBorderWidth') || '1px';
  var backdropColor = getAttr('data-backdrop-color', 'PerkLedgerBackdropColor') || '#000000';
  var backdropOpacity = getAttr('data-backdrop-opacity', 'PerkLedgerBackdropOpacity') || '0.65';
  var backdropBlur = getAttr('data-backdrop-blur', 'PerkLedgerBackdropBlur') || '8px';

  var launcherBg = getAttr('data-launcher-bg', 'PerkLedgerLauncherBg') || themeAccent || '#0F172A';
  var launcherTextColor = getAttr('data-launcher-text-color', 'PerkLedgerLauncherTextColor') || '#FFFFFF';
  var launcherIconColor = getAttr('data-launcher-icon-color', 'PerkLedgerLauncherIconColor') || '#FFFFFF';
  var launcherBorderColor = getAttr('data-launcher-border-color', 'PerkLedgerLauncherBorderColor') || (themeAccent ? hexToRgba(themeAccent, '0.5') : null);
  var launcherBorderWidth = getAttr('data-launcher-border-width', 'PerkLedgerLauncherBorderWidth') || '1px';
  var launcherIcon = getAttr('data-launcher-icon', 'PerkLedgerLauncherIcon') || getAttr('data-stamp-icon', 'PerkLedgerStampIcon') || 'card_membership';
  var modalRadius = getAttr('data-modal-radius', 'PerkLedgerModalRadius');
  var launcherRadius = getAttr('data-launcher-radius', 'PerkLedgerLauncherRadius') || modalRadius || 'subtle';
  var position = getAttr('data-position', 'PerkLedgerPosition') || 'bottom-right';

  var hashTriggerEnabled = getAttr('data-hash-trigger', 'PerkLedgerHashTrigger') === 'yes' || getAttr('data-hash-trigger', 'PerkLedgerHashTrigger') === '1';
  var customHashName = getAttr('data-hash-name', 'PerkLedgerHashName') || 'reward';

  var rawLauncher = getAttr('data-launcher', 'PerkLedgerLauncher');
  // STRICT RULE: Floating launcher MUST ONLY appear if explicitly set to 'yes', 'true', or '1'
  var hasLauncher = rawLauncher === 'yes' || rawLauncher === 'true' || rawLauncher === '1';
  var launcherStyle = getAttr('data-launcher-style', 'PerkLedgerLauncherStyle');
  var launcherText = getAttr('data-launcher-text', 'PerkLedgerLauncherText');
  var launcherHideText = getAttr('data-launcher-hide-text', 'PerkLedgerLauncherHideText') === 'yes' || getAttr('data-launcher-hide-text', 'PerkLedgerLauncherHideText') === '1';

  // Derive dynamic modal corner radius
  var radiusPx = '12px';
  if (modalRadius === 'sharp') radiusPx = '6px';
  else if (modalRadius === 'subtle') radiusPx = '12px';
  else if (modalRadius === 'rounded') radiusPx = '24px';
  else if (modalRadius && modalRadius.indexOf('px') !== -1) radiusPx = modalRadius;

  // Derive launcher corner radius
  var launcherRadiusPx = radiusPx;
  if (launcherRadius === 'sharp') launcherRadiusPx = '6px';
  else if (launcherRadius === 'subtle') launcherRadiusPx = '12px';
  else if (launcherRadius === 'rounded') launcherRadiusPx = '24px';
  else if (launcherRadius === 'pill') launcherRadiusPx = '9999px';
  else if (launcherRadius && launcherRadius.indexOf('px') !== -1) launcherRadiusPx = launcherRadius;

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
  var progNameParam = getAttr('data-program-name', 'PerkLedgerProgramName');
  var taglineParam = getAttr('data-program-tagline', 'PerkLedgerProgramTagline');
  var stampIconParam = getAttr('data-stamp-icon', 'PerkLedgerStampIcon');

  if (progNameParam) passUrl += '&prog_name=' + encodeURIComponent(progNameParam);
  if (taglineParam) passUrl += '&tagline=' + encodeURIComponent(taglineParam);
  if (stampIconParam) passUrl += '&stamp_icon=' + encodeURIComponent(stampIconParam);
  if (theme) passUrl += '&theme=' + encodeURIComponent(theme);
  if (themeBg) passUrl += '&t_bg=' + encodeURIComponent(themeBg);
  if (themeSurface) passUrl += '&t_sfc=' + encodeURIComponent(themeSurface);
  if (themeText) passUrl += '&t_txt=' + encodeURIComponent(themeText);
  if (fontSize) passUrl += '&fs=' + encodeURIComponent(fontSize);
  var isTransparentParam = getAttr('data-transparent', 'PerkLedgerTransparent') === 'true' || themeBg === 'transparent';
  if (isTransparentParam) passUrl += '&transparent=true';

  // Dynamic Curated Vector SVG Icons Map
  var MATERIAL_ICONS_SVG = {
    'french_fries': '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 11V3"/><path d="M10 11V1.5"/><path d="M13 11V3"/><path d="M16 11V4.5"/><path d="M5 11l1.5 10a1 1 0 0 0 1 .9h9a1 1 0 0 0 1-.9L19 11"/><path d="M5 11c3.5 2 10.5 2 14 0"/></svg>',
    'fastfood': '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 11a4 4 0 0 1 8 0h-8z"/><line x1="1.5" y1="14" x2="10.5" y2="14"/><path d="M2 17a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2v-1H2v1z"/><path d="M16 3l-2 5"/><line x1="13" y1="8" x2="21" y2="8"/><path d="M14 8l1 12a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1l1-12"/></svg>',
    'coffee': '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>',
    'local_pizza': '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 22h20L12 2z"/><circle cx="12" cy="14" r="1.5"/><circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/></svg>',
    'card_membership': '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/><circle cx="7" cy="15" r="1"/></svg>',
    'card_giftcard': '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><line x1="12" y1="22" x2="12" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>',
    'stars': '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
    'star': '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
    'verified': '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M23 12l-2.44-2.79.34-3.69-3.61-.82-1.89-3.2L12 2.96 8.6 1.5 6.71 4.69 3.1 5.5l.34 3.7L1 12l2.44 2.79-.34 3.7 3.61.82 1.89 3.2l3.4-1.46 3.4 1.46 1.89-3.2 3.61-.82-.34-3.69L23 12zm-12.91 4.72l-3.8-3.81 1.48-1.48 2.32 2.33 5.85-5.87 1.48 1.48-7.33 7.35z"/></svg>',
    'content_cut': '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><line x1="20" y1="4" x2="8.12" y2="15.88"/><line x1="14.47" y1="14.47" x2="20" y2="20"/><line x1="8.12" y1="8.12" x2="12" y2="12"/></svg>',
    'fitness_center': '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6.5 6.5h11M6.5 17.5h11M4 10v4M20 10v4M8 4v16M16 4v16"/></svg>',
    'auto_awesome': '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M19 9l1.25-2.75L23 5l-2.75-1.25L19 1l-1.25 2.75L15 5l2.75 1.25L19 9zm-7.5.5L9 3 6.5 9.5 0 12l6.5 2.5L9 21l2.5-6.5L18 12l-6.5-2.5zM19 15l-1.25 2.75L15 19l2.75 1.25L19 23l1.25-2.75L23 19l-2.75-1.25L19 15z"/></svg>',
    'shield': '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
    'loyalty': '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>'
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

  function runEngine() {
    var inlineTarget = document.getElementById('perkledger-widget') || document.querySelector('[data-perkledger-pass]');
    if (inlineTarget) {
      // Style inline container (Clean native page integration - zero intrusive borders or YouTube-style box-shadows)
      var isTransparent = getAttr('data-transparent') === 'true' || themeBg === 'transparent';
      var showBorder = getAttr('data-border') === 'true';
      var showShadow = getAttr('data-shadow') === 'true';

      inlineTarget.style.maxWidth = inlineWidth;
      inlineTarget.style.margin = '20px auto';
      inlineTarget.style.width = '100%';
      inlineTarget.style.minHeight = '650px';
      inlineTarget.style.borderRadius = isTransparent ? '0px' : radiusPx;
      inlineTarget.style.overflow = 'hidden';
      inlineTarget.style.transition = 'height 0.2s ease';
      inlineTarget.style.border = showBorder ? (drawerBorderCss || '1px solid rgba(0,0,0,0.08)') : 'none';
      inlineTarget.style.boxShadow = showShadow ? '0 12px 40px rgba(0, 0, 0, 0.12)' : 'none';
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
        if (launcherRadiusPx) {
          launcher.style.borderRadius = launcherRadiusPx;
        }

        var iconHtml = activeIconSvg ? ('<span class="pl-launcher-icon" style="display:inline-flex; align-items:center; color:' + (launcherIconColor || 'currentColor') + ';">' + activeIconSvg + '</span>') : '<span class="pl-launcher-icon" style="display:inline-flex; align-items:center; color:' + (launcherIconColor || 'currentColor') + ';"></span>';
        
        if (isIconOnly) {
          launcher.innerHTML = iconHtml;
        } else {
          var label = launcherText || 'Rewards Pass';
          launcher.innerHTML = iconHtml + '<span class="pl-launcher-text">' + label + '</span>';
        }

        launcher.addEventListener('click', openModal);
        safeAppend(launcher);

        // Real-time Firestore Auto-Hydration Engine (Syncs portal edits live without re-pasting script tags)
        try {
          var firestoreUrl = 'https://firestore.googleapis.com/v1/projects/perkledger/databases/(default)/documents/merchants/' + encodeURIComponent(merchantId);
          var xhr = new XMLHttpRequest();
          xhr.open('GET', firestoreUrl, true);
          xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
              try {
                var data = JSON.parse(xhr.responseText);
                var fields = data.fields || {};
                var themeFields = (fields.theme && fields.theme.mapValue && fields.theme.mapValue.fields) ? fields.theme.mapValue.fields : {};
                
                var liveIcon = (themeFields.stamp_icon && themeFields.stamp_icon.stringValue) || (themeFields.launcher_icon && themeFields.launcher_icon.stringValue);
                var liveText = (themeFields.launcher_text && themeFields.launcher_text.stringValue);
                var liveAccent = (themeFields.accent_color && themeFields.accent_color.stringValue);
                var liveRadius = (themeFields.border_radius && themeFields.border_radius.stringValue);

                var hasExplicitIcon = getAttr('data-launcher-icon') || getAttr('data-stamp-icon');
                if (!hasExplicitIcon && liveIcon && MATERIAL_ICONS_SVG[liveIcon]) {
                  var iconContainer = launcher.querySelector('.pl-launcher-icon');
                  if (iconContainer) {
                    iconContainer.innerHTML = MATERIAL_ICONS_SVG[liveIcon];
                  }
                }

                if (liveText && !getAttr('data-launcher-text')) {
                  var textContainer = launcher.querySelector('.pl-launcher-text');
                  if (textContainer) {
                    textContainer.textContent = liveText;
                  }
                }

                if (liveAccent && !getAttr('data-launcher-bg')) {
                  launcher.style.backgroundColor = liveAccent;
                  closeBtn.style.backgroundColor = liveAccent;
                }

                if (liveRadius && !getAttr('data-launcher-radius')) {
                  var rPx = '12px';
                  if (liveRadius === 'sharp') rPx = '6px';
                  else if (liveRadius === 'subtle') rPx = '12px';
                  else if (liveRadius === 'rounded') rPx = '24px';
                  else if (liveRadius === 'pill') rPx = '9999px';
                  launcher.style.borderRadius = rPx;
                  drawer.style.borderRadius = rPx;
                }
              } catch (e) {
                // Silent fallback to script tag attributes
              }
            }
          };
          xhr.send();
        } catch (e) {}
      }
    }
  }

  if (document.body) {
    runEngine();
  } else if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', runEngine);
  } else {
    setTimeout(runEngine, 50);
  }
})();
