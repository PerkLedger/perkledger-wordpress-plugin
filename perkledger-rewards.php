<?php
/**
 * Plugin Name: PerkLedger Loyalty Rewards & Digital Pass
 * Plugin URI: https://perkledger.com
 * Description: Official PerkLedger B2B Loyalty Infrastructure Bridge for WordPress. Seamlessly embeds digital stamp passes and floating customer launchers.
 * Version: 1.1.0
 * Author: PerkLedger (FozDigital LTD)
 * Author URI: https://perkledger.com
 * License: GPLv2 or later
 */

if (!defined('ABSPATH')) exit;

define('PERKLEDGER_WP_BUILD', 'build.20260807.114');

class PerkLedgerRewardsPlugin {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_footer', array($this, 'inject_embed_script_tag'));
        add_shortcode('perkledger_pass', array($this, 'render_shortcode'));
    }

    public function add_admin_menu() {
        $icon_svg = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="-20 -20 360 430" fill="currentColor"><path fill-rule="evenodd" clip-rule="evenodd" d="M50.3512 0.696272C49.6946 1.31479 49.1562 3.721 49.1562 6.04545C49.1562 9.98484 49.7406 10.3396 57.774 11.2928C80.0521 13.9366 91.2092 21.6195 97.025 38.3241C98.2643 41.8825 99.2377 68.1795 99.8844 115.46L100.863 187.197L155.852 179.684V104.3C155.852 23.0338 155.913 22.4277 164.486 18.0132C169.979 15.1875 189.64 16.0281 201.671 19.6035C235.498 29.6587 253.284 57.1156 253.259 99.2504C253.23 151.18 229.686 180.292 177.191 193.313C152.243 199.501 77.3913 209.202 22.8926 213.308L10.5815 214.235L17.5825 208.415C21.4334 205.213 24.5735 202.092 24.5587 201.479C24.5292 200.149 0.126937 214.492 0.000543444 215.914C-0.129133 217.355 22.9911 230.007 24.0071 229.053C24.4832 228.605 21.87 226.287 18.1996 223.901C14.5293 221.513 11.4991 219.15 11.4646 218.649C11.4302 218.149 29.3157 217.292 51.2081 216.744C133.517 214.69 198.705 207.884 229.046 198.178C268.837 185.451 292.576 164.86 305.69 131.696C309.943 120.942 309.693 116.649 311.358 99.2504C312.275 75.2685 309.851 63.398 300.685 46.9833C289.163 26.3563 267.968 11.3422 240.06 4.03719C227.793 0.82738 223.578 0.663898 139.401 0.130214C91.0828 -0.176731 51.0095 0.0777545 50.3512 0.696272ZM129.589 230.706C117.401 231.899 105.836 233.173 103.89 233.537C100.36 234.198 100.351 234.336 99.6464 290.119C99.0736 335.624 98.458 347.418 96.3471 353.437C90.8941 368.984 79.7156 376.892 60.361 378.898C50.1181 379.959 49.9704 380.042 49.4632 384.987L48.9478 390H300.24L301.077 386.53C301.538 384.62 305.985 367.303 310.957 348.047C315.931 328.79 320 312.678 320 312.24C320 311.801 318.6 310.74 316.888 309.88C314.189 308.522 312.853 309.645 306.816 318.351C296.635 333.036 278.502 348.262 262.177 355.835C242.844 364.806 229.814 367.44 204.276 367.547C183.754 367.632 182.561 367.457 173.104 363.007C156.256 355.078 156.809 357.478 155.852 288.326C155.33 250.635 154.434 228.973 153.39 228.803C152.487 228.656 141.777 229.512 129.589 230.706Z"/></svg>');

        add_menu_page(
            'PerkLedger Rewards',
            'PerkLedger',
            'manage_options',
            'perkledger-rewards',
            array($this, 'render_settings_page'),
            $icon_svg,
            30
        );
    }

    public function register_settings() {
        register_setting('perkledger_settings_group', 'perkledger_merchant_id');
        register_setting('perkledger_settings_group', 'perkledger_integration_mode');
        register_setting('perkledger_settings_group', 'perkledger_embedded_page_slug');
        register_setting('perkledger_settings_group', 'perkledger_hash_trigger_enabled');
        register_setting('perkledger_settings_group', 'perkledger_hash_name');

        register_setting('perkledger_settings_group', 'perkledger_modal_radius');
        register_setting('perkledger_settings_group', 'perkledger_modal_border_width');

        register_setting('perkledger_settings_group', 'perkledger_launcher_enabled');
        register_setting('perkledger_settings_group', 'perkledger_launcher_style');
        register_setting('perkledger_settings_group', 'perkledger_launcher_text');
        register_setting('perkledger_settings_group', 'perkledger_launcher_hide_text');
        register_setting('perkledger_settings_group', 'perkledger_launcher_icon');
        register_setting('perkledger_settings_group', 'perkledger_launcher_radius');
        register_setting('perkledger_settings_group', 'perkledger_launcher_position');

        // New Branding & Layout Parameters
        register_setting('perkledger_settings_group', 'perkledger_theme_bg');
        register_setting('perkledger_settings_group', 'perkledger_theme_surface');
        register_setting('perkledger_settings_group', 'perkledger_theme_text');
        register_setting('perkledger_settings_group', 'perkledger_theme_accent');
        register_setting('perkledger_settings_group', 'perkledger_font_size');
        register_setting('perkledger_settings_group', 'perkledger_inline_width');
    }

    public function render_settings_page() {
        $merchant_id = esc_attr(get_option('perkledger_merchant_id', ''));
        $integration_mode = get_option('perkledger_integration_mode', 'hybrid');
        $embedded_slug = esc_attr(get_option('perkledger_embedded_page_slug', ''));
        $hash_enabled = get_option('perkledger_hash_trigger_enabled', '1');
        $hash_name = esc_attr(get_option('perkledger_hash_name', 'reward'));

        $modal_radius = get_option('perkledger_modal_radius', 'rounded');
        $modal_border_width = get_option('perkledger_modal_border_width', '1px');

        $launcher_enabled = get_option('perkledger_launcher_enabled', '1');
        $launcher_style = get_option('perkledger_launcher_style', 'with-label');
        $launcher_text = esc_attr(get_option('perkledger_launcher_text', 'Rewards Pass'));
        $launcher_hide_text = get_option('perkledger_launcher_hide_text', '0');
        $launcher_icon = get_option('perkledger_launcher_icon', 'card_membership');
        $launcher_radius = get_option('perkledger_launcher_radius', 'pill');
        $launcher_position = get_option('perkledger_launcher_position', 'bottom-right');

        // Core Branding & Layout
        $theme_bg = esc_attr(get_option('perkledger_theme_bg', '#0F172A'));
        $theme_surface = esc_attr(get_option('perkledger_theme_surface', '#1E293B'));
        $theme_text = esc_attr(get_option('perkledger_theme_text', '#FFFFFF'));
        $theme_accent = esc_attr(get_option('perkledger_theme_accent', '#FCBD0B'));
        $font_size = esc_attr(get_option('perkledger_font_size', 'medium'));
        $inline_width = esc_attr(get_option('perkledger_inline_width', '100%'));

        // Fetch published WordPress pages for dropdown selector
        $wp_pages = get_pages(array('post_status' => 'publish'));
        $site_home_url = esc_url(home_url('/'));
        ?>
        <style>
            .pl-wp-wrap { max-width: 1180px; margin: 20px 20px 20px 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; }
            .pl-wp-card { background: #FFFFFF; border: 1px solid #C3C4C7; border-radius: 8px; padding: 28px 32px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04); }
            .pl-wp-header { display: flex; align-items: center; justify-content: space-between; padding-bottom: 20px; border-bottom: 1px solid #E2E8F0; margin-bottom: 28px; }
            .pl-wp-header-left { display: flex; align-items: center; gap: 14px; }
            .pl-wp-icon { width: 42px; height: 42px; background: #0F172A; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #A68958; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15); }
            .pl-wp-title { margin: 0; font-size: 20px; font-weight: 700; color: #0F172A; line-height: 1.2; }
            .pl-wp-subtitle { margin: 3px 0 0 0; font-size: 13px; color: #64748B; }
            .pl-wp-badge-link { display: inline-flex; align-items: center; gap: 6px; background: #FFFDF9; border: 1px solid #A68958; color: #846B42; padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; text-decoration: none; transition: all 0.2s ease; }
            .pl-wp-badge-link:hover { background: #F5EFE6; border-color: #846B42; color: #5C4A2D; }

            /* 2-Column Responsive Layout with Sticky Sidebar */
            .pl-layout-container { display: grid; grid-template-columns: 1fr 340px; gap: 28px; align-items: start; }
            @media (max-width: 900px) {
                .pl-layout-container { grid-template-columns: 1fr; }
            }
            .pl-layout-main { min-width: 0; }
            
            /* Sticky Right Sidebar */
            .pl-layout-sidebar { position: sticky; top: 32px; background: #0F172A; border: 2px solid #A68958; border-radius: 12px; padding: 20px; box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25); color: #FFFFFF; }
            .pl-sidebar-title { font-size: 13px; font-weight: 700; color: #A68958; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 14px 0; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px; }
            
            .pl-sidebar-section { margin-bottom: 20px; }
            .pl-sidebar-section:last-child { margin-bottom: 0; }
            .pl-sidebar-sub { font-size: 11px; font-weight: 700; color: #94A3B8; text-transform: uppercase; margin-bottom: 8px; display: block; }
            
            /* Live Floating Launcher Preview Container */
            .pl-live-launcher-stage { background: #1E293B; border-radius: 8px; padding: 24px 16px; display: flex; align-items: center; justify-content: center; min-height: 80px; border: 1px solid rgba(255,255,255,0.08); }
            
            /* Dynamic Live Button Component */
            .pl-live-button { display: inline-flex; align-items: center; gap: 10px; cursor: pointer; transition: all 0.25s ease; box-shadow: 0 10px 25px rgba(0,0,0,0.3); }
            .pl-live-button.pill { padding: 12px 20px; font-size: 14px; font-weight: 700; }
            .pl-live-button.circle { width: 56px; height: 56px; border-radius: 50% !important; justify-content: center; padding: 0; }
            .pl-live-button svg { display: block; flex-shrink: 0; }

            /* Live Drawer Mockup Box */
            .pl-drawer-mockup-stage { background: #1E293B; border-radius: 8px; padding: 14px; border: 1px solid rgba(255,255,255,0.08); position: relative; }
            .pl-drawer-mockup-card { height: 160px; background: #0F172A; border-radius: 24px; border: 1px solid #A68958; display: flex; flex-direction: column; overflow: hidden; position: relative; box-shadow: 0 10px 20px rgba(0,0,0,0.5); }
            .pl-drawer-mockup-header { height: 32px; background: rgba(255,255,255,0.05); border-bottom: 1px solid rgba(255,255,255,0.08); display: flex; align-items: center; justify-content: space-between; padding: 0 12px; }
            .pl-drawer-mockup-dot { width: 6px; height: 6px; border-radius: 50%; background: #A68958; }
            .pl-drawer-mockup-close { width: 14px; height: 14px; border-radius: 50%; background: #7C2A2A; color: #FCBD0B; display: flex; align-items: center; justify-content: center; font-size: 9px; font-weight: bold; }
            .pl-drawer-mockup-body { flex: 1; display: flex; flex-direction: column; items-center; justify-content: center; padding: 10px; text-align: center; }
            .pl-drawer-mockup-stamp-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px; margin-top: 8px; width: 100%; max-width: 140px; }
            .pl-drawer-stamp-dot { height: 14px; border-radius: 4px; background: rgba(255,255,255,0.08); border: 1px dashed rgba(255,255,255,0.2); }
            .pl-drawer-stamp-dot.active { background: #A68958; border: none; }
            
            /* Clean & Generous Vertical Spacing for Step Headers */
            .pl-group-header { font-size: 15px; font-weight: 700; color: #0F172A; padding-bottom: 12px; margin-top: 40px !important; margin-bottom: 22px !important; border-bottom: 1px solid #E2E8F0; display: flex; align-items: center; gap: 10px; }
            .pl-group-header:first-of-type { margin-top: 0 !important; }
            .pl-group-badge { width: 24px; height: 24px; border-radius: 50%; background: #0F172A; color: #A68958; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; }
            
            .pl-section-box { margin-bottom: 32px; }
            .pl-wp-field { margin-bottom: 24px; }
            .pl-wp-label { display: block; font-size: 13px; font-weight: 700; color: #1E293B; margin-bottom: 8px; }
            .pl-wp-input, .pl-wp-select { width: 100%; max-width: 460px; background: #FFFFFF !important; border: 1px solid #94A3B8 !important; color: #0F172A !important; padding: 10px 14px !important; border-radius: 6px !important; font-size: 13px !important; }
            
            /* Visual Choice Card Grid System */
            .pl-mode-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-top: 8px; }
            .pl-mode-card, .pl-card-choice { border: 2px solid #E2E8F0; background: #F8FAFC; border-radius: 8px; padding: 16px; cursor: pointer; transition: all 0.2s ease; position: relative; }
            .pl-mode-card:hover, .pl-card-choice:hover { border-color: #CBD5E1; background: #FFFFFF; }
            .pl-mode-card.active, .pl-card-choice.active { border-color: #A68958; background: #FFFDF9; box-shadow: 0 0 0 1px #A68958; }
            .pl-mode-card input[type="radio"], .pl-card-choice input[type="radio"] { position: absolute; top: 14px; right: 14px; margin: 0; accent-color: #A68958; }
            .pl-mode-icon { width: 36px; height: 36px; background: #0F172A; color: #A68958; border-radius: 6px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; }
            .pl-mode-title { font-size: 14px; font-weight: 700; color: #0F172A; margin: 0 0 4px 0; }
            .pl-mode-desc { font-size: 12px; color: #64748B; margin: 0; line-height: 1.4; }

            /* Card Previews */
            .pl-card-preview { height: 56px; background: #0F172A; border-radius: 6px; margin-bottom: 12px; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; border: 1px solid rgba(255,255,255,0.12); }
            .pl-mockup-pill { display: inline-flex; align-items: center; gap: 6px; background: #1E293B; color: #FFFFFF; padding: 6px 14px; border-radius: 9999px; font-size: 11px; font-weight: 700; border: 1px solid #A68958; }
            .pl-mockup-circle { width: 32px; height: 32px; border-radius: 50%; background: #1E293B; color: #FCBD0B; display: flex; align-items: center; justify-content: center; border: 1px solid #A68958; }

            /* Icon Card Previews */
            .pl-icon-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(110px, 1fr)); gap: 10px; margin-top: 8px; }
            .pl-icon-card { border: 2px solid #E2E8F0; background: #F8FAFC; border-radius: 8px; padding: 12px 6px; text-align: center; cursor: pointer; transition: all 0.2s ease; position: relative; }
            .pl-icon-card:hover { border-color: #CBD5E1; background: #FFFFFF; }
            .pl-icon-card.active { border-color: #A68958; background: #FFFDF9; box-shadow: 0 0 0 1px #A68958; }
            .pl-icon-card input[type="radio"] { opacity: 0; position: absolute; }
            .pl-icon-box { width: 36px; height: 36px; background: #0F172A; color: #FCBD0B; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 6px auto; border: 1px solid #A68958; }
            .pl-icon-title { font-size: 11px; font-weight: 700; color: #0F172A; margin: 0; }

            /* Border Thickness Cards */
            .pl-thick-preview { display: flex; align-items: center; justify-content: center; height: 32px; width: 60px; background: #0F172A; border-radius: 4px; color: #A68958; font-size: 11px; font-weight: bold; margin-bottom: 8px; }
            .pl-thick-1 { border: 1px solid #A68958; }
            .pl-thick-2 { border: 2px solid #A68958; }
            .pl-thick-3 { border: 3px solid #A68958; }
            .pl-thick-0 { border: none; }

            /* Screen Mockup for Position */
            .pl-mockup-screen { width: 100%; height: 100%; position: relative; background: #1E293B; border-radius: 4px; }
            .pl-mockup-btn { position: absolute; width: 14px; height: 14px; border-radius: 50%; background: #A68958; box-shadow: 0 2px 4px rgba(0,0,0,0.5); }
            .pl-mockup-btn.br { bottom: 8px; right: 8px; }
            .pl-mockup-btn.bl { bottom: 8px; left: 8px; }
            .pl-mockup-btn.bc { bottom: 8px; left: 50%; transform: translateX(-50%); }
            .pl-mockup-arrow { position: absolute; font-size: 11px; font-weight: bold; color: #FCBD0B; }
            .pl-mockup-arrow.br { bottom: 26px; right: 8px; }
            .pl-mockup-arrow.bl { bottom: 26px; left: 8px; }
            .pl-mockup-arrow.bc { bottom: 26px; left: 50%; transform: translateX(-50%); }

            /* Radius Mockups */
            .pl-mockup-rad { width: 50px; height: 36px; background: #0F172A; border: 2px solid #A68958; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: bold; color: #A68958; }
            .pl-mockup-rad.pill { border-radius: 9999px; }
            .pl-mockup-rad.rounded { border-radius: 12px; }
            .pl-mockup-rad.subtle { border-radius: 6px; }
            .pl-mockup-rad.sharp { border-radius: 2px; }

            /* Modern iOS-style Toggle Switch */
            .pl-toggle-wrap { display: flex; align-items: center; gap: 12px; }
            .pl-toggle { position: relative; display: inline-block; width: 44px; height: 24px; }
            .pl-toggle input { opacity: 0; width: 0; height: 0; }
            .pl-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #CBD5E1; transition: .3s; border-radius: 24px; }
            .pl-slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .3s; border-radius: 50%; box-shadow: 0 1px 3px rgba(0,0,0,0.3); }
            input:checked + .pl-slider { background-color: #A68958; }
            input:checked + .pl-slider:before { transform: translateX(20px); }

            .pl-wp-color-box { display: flex; align-items: center; gap: 8px; }
            .pl-wp-color-picker { width: 36px !important; height: 32px !important; padding: 2px !important; border: 1px solid #94A3B8 !important; border-radius: 4px !important; cursor: pointer; }
            .pl-wp-color-text { width: 120px !important; font-family: monospace; }
            .pl-wp-help { font-size: 12px; color: #64748B; margin-top: 6px; line-height: 1.4; }
            .pl-wp-btn { background: #A68958 !important; color: #FFFFFF !important; border: 1px solid #846B42 !important; padding: 10px 28px !important; border-radius: 6px !important; font-size: 13px !important; font-weight: 700 !important; cursor: pointer !important; transition: all 0.2s ease; }
            .pl-wp-btn:hover { background: #846B42 !important; }
            
            .pl-shortcode-box { background: #FFFDF9; border: 1px solid #A68958; border-radius: 8px; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; margin-top: 18px; margin-bottom: 24px; }
            .pl-wp-code { background: #0F172A; color: #A68958; padding: 6px 14px; border-radius: 6px; font-family: monospace; font-size: 13px; font-weight: 700; }

            /* Copyable Link Box */
            .pl-hash-copy-box { background: #F8FAFC; border: 1px solid #CBD5E1; border-radius: 8px; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-top: 10px; }
            .pl-hash-url { font-family: monospace; font-size: 13px; font-weight: 700; color: #0F172A; word-break: break-all; }
            .pl-hash-copy-btn { background: #0F172A; color: #A68958; border: 1px solid #A68958; padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s ease; }
            .pl-hash-copy-btn:hover { background: #1E293B; color: #FCBD0B; }

            /* Footer Header Matching Emblem Bar */
            .pl-footer-emblem-bar { display: flex; align-items: center; justify-content: space-between; margin-top: 48px; padding-top: 24px; border-top: 1px solid #E2E8F0; font-size: 13px; color: #64748B; }
            .pl-footer-left a { color: #846B42; font-weight: 700; text-decoration: none; }
            .pl-footer-left a:hover { color: #5C4A2D; text-decoration: underline; }
            .pl-footer-right { display: flex; align-items: center; gap: 8px; font-weight: 700; color: #0F172A; }
        </style>

        <div class="pl-wp-wrap">
            <div class="pl-wp-card">
                <div class="pl-wp-header">
                    <div class="pl-wp-header-left">
                        <div class="pl-wp-icon">
                            <svg width="24" height="24" viewBox="0 0 320 390" fill="#A68958"><path fill-rule="evenodd" clip-rule="evenodd" d="M50.3512 0.696272C49.6946 1.31479 49.1562 3.721 49.1562 6.04545C49.1562 9.98484 49.7406 10.3396 57.774 11.2928C80.0521 13.9366 91.2092 21.6195 97.025 38.3241C98.2643 41.8825 99.2377 68.1795 99.8844 115.46L100.863 187.197L155.852 179.684V104.3C155.852 23.0338 155.913 22.4277 164.486 18.0132C169.979 15.1875 189.64 16.0281 201.671 19.6035C235.498 29.6587 253.284 57.1156 253.259 99.2504C253.23 151.18 229.686 180.292 177.191 193.313C152.243 199.501 77.3913 209.202 22.8926 213.308L10.5815 214.235L17.5825 208.415C21.4334 205.213 24.5735 202.092 24.5587 201.479C24.5292 200.149 0.126937 214.492 0.000543444 215.914C-0.129133 217.355 22.9911 230.007 24.0071 229.053C24.4832 228.605 21.87 226.287 18.1996 223.901C14.5293 221.513 11.4991 219.15 11.4646 218.649C11.4302 218.149 29.3157 217.292 51.2081 216.744C133.517 214.69 198.705 207.884 229.046 198.178C268.837 185.451 292.576 164.86 305.69 131.696C309.943 120.942 309.693 116.649 311.358 99.2504C312.275 75.2685 309.851 63.398 300.685 46.9833C289.163 26.3563 267.968 11.3422 240.06 4.03719C227.793 0.82738 223.578 0.663898 139.401 0.130214C91.0828 -0.176731 51.0095 0.0777545 50.3512 0.696272ZM129.589 230.706C117.401 231.899 105.836 233.173 103.89 233.537C100.36 234.198 100.351 234.336 99.6464 290.119C99.0736 335.624 98.458 347.418 96.3471 353.437C90.8941 368.984 79.7156 376.892 60.361 378.898C50.1181 379.959 49.9704 380.042 49.4632 384.987L48.9478 390H300.24L301.077 386.53C301.538 384.62 305.985 367.303 310.957 348.047C315.931 328.79 320 312.678 320 312.24C320 311.801 318.6 310.74 316.888 309.88C314.189 308.522 312.853 309.645 306.816 318.351C296.635 333.036 278.502 348.262 262.177 355.835C242.844 364.806 229.814 367.44 204.276 367.547C183.754 367.632 182.561 367.457 173.104 363.007C156.256 355.078 156.809 357.478 155.852 288.326C155.33 250.635 154.434 228.973 153.39 228.803C152.487 228.656 141.777 229.512 129.589 230.706Z"/></svg>
                        </div>
                        <div>
                            <h1 class="pl-wp-title">PerkLedger Loyalty Rewards</h1>
                            <p class="pl-wp-subtitle">Official B2B Customer Loyalty & Digital Stamp Card Integration</p>
                        </div>
                    </div>
                    <a href="https://app.perkledger.com" target="_blank" rel="noopener" class="pl-wp-badge-link">
                        <span>Open Merchant Portal</span>
                    </a>
                </div>

                <!-- 2-Column Responsive Layout -->
                <div class="pl-layout-container">
                    
                    <!-- Left Column: Form Settings -->
                    <div class="pl-layout-main">
                        <form method="post" action="options.php">
                            <?php settings_fields('perkledger_settings_group'); ?>
                            <?php do_settings_sections('perkledger_settings_group'); ?>

                            <!-- Step 1: Store Credentials -->
                            <div class="pl-group-header">
                                <span class="pl-group-badge">1</span>
                                <span>Account & Store Authorization</span>
                            </div>

                            <div class="pl-section-box">
                                <div class="pl-wp-field">
                                    <label class="pl-wp-label">Merchant ID / Handle</label>
                                    <input type="text" name="perkledger_merchant_id" value="<?php echo $merchant_id; ?>" class="pl-wp-input" placeholder="yourstore" required />
                                    <div class="pl-wp-help">Enter your unique PerkLedger Merchant ID (found in your Merchant Portal settings).</div>
                                </div>
                            </div>

                            <!-- New Step: Branding Settings -->
                            <div class="pl-group-header">
                                <span class="pl-group-badge">2</span>
                                <span>Core Branding & Layout (Zero-Flicker Injection)</span>
                            </div>

                            <div class="pl-section-box">
                                <div class="pl-wp-field">
                                    <label class="pl-wp-label">Background Color</label>
                                    <div class="pl-wp-color-box">
                                        <input type="color" name="perkledger_theme_bg" value="<?php echo $theme_bg; ?>" class="pl-wp-color-picker" />
                                        <input type="text" value="<?php echo $theme_bg; ?>" class="pl-wp-input pl-wp-color-text" readonly />
                                    </div>
                                </div>
                                <div class="pl-wp-field">
                                    <label class="pl-wp-label">Surface Color</label>
                                    <div class="pl-wp-color-box">
                                        <input type="color" name="perkledger_theme_surface" value="<?php echo $theme_surface; ?>" class="pl-wp-color-picker" />
                                        <input type="text" value="<?php echo $theme_surface; ?>" class="pl-wp-input pl-wp-color-text" readonly />
                                    </div>
                                </div>
                                <div class="pl-wp-field">
                                    <label class="pl-wp-label">Text Color</label>
                                    <div class="pl-wp-color-box">
                                        <input type="color" name="perkledger_theme_text" value="<?php echo $theme_text; ?>" class="pl-wp-color-picker" />
                                        <input type="text" value="<?php echo $theme_text; ?>" class="pl-wp-input pl-wp-color-text" readonly />
                                    </div>
                                </div>
                                <div class="pl-wp-field">
                                    <label class="pl-wp-label">Accent Color</label>
                                    <div class="pl-wp-color-box">
                                        <input type="color" name="perkledger_theme_accent" value="<?php echo $theme_accent; ?>" class="pl-wp-color-picker" />
                                        <input type="text" value="<?php echo $theme_accent; ?>" class="pl-wp-input pl-wp-color-text" readonly />
                                    </div>
                                </div>
                                <div class="pl-wp-field">
                                    <label class="pl-wp-label">Base Font Size</label>
                                    <select name="perkledger_font_size" class="pl-wp-select">
                                        <option value="small" <?php selected($font_size, 'small'); ?>>Small</option>
                                        <option value="medium" <?php selected($font_size, 'medium'); ?>>Medium</option>
                                        <option value="large" <?php selected($font_size, 'large'); ?>>Large</option>
                                    </select>
                                    <div class="pl-wp-help">Select the base font size for the Pass.</div>
                                </div>
                                <div class="pl-wp-field">
                                    <label class="pl-wp-label">Embedded Widget Max Width</label>
                                    <input type="text" name="perkledger_inline_width" value="<?php echo $inline_width; ?>" class="pl-wp-input" placeholder="100%" />
                                    <div class="pl-wp-help">Controls the maximum width of the embedded pass (e.g., "100%" or "900px"). Setting this wider allows the responsive 2-column layout to trigger.</div>
                                </div>
                            </div>

                            <!-- Step 3: Integration Method Selector -->
                            <div class="pl-group-header">
                                <span class="pl-group-badge">3</span>
                                <span>Select Integration Method</span>
                            </div>

                            <div class="pl-section-box">
                                <div class="pl-mode-grid">
                                    <label class="pl-mode-card <?php echo $integration_mode === 'floating' ? 'active' : ''; ?>">
                                        <input type="radio" name="perkledger_integration_mode" value="floating" <?php checked($integration_mode, 'floating'); ?> onchange="updateModeSections()" />
                                        <div class="pl-mode-icon">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                        </div>
                                        <h4 class="pl-mode-title">Only Floating Launcher</h4>
                                        <p class="pl-mode-desc">Injects a floating launcher button across all site pages. Opens modal drawer on click.</p>
                                    </label>

                                    <label class="pl-mode-card <?php echo $integration_mode === 'embedded' ? 'active' : ''; ?>">
                                        <input type="radio" name="perkledger_integration_mode" value="embedded" <?php checked($integration_mode, 'embedded'); ?> onchange="updateModeSections()" />
                                        <div class="pl-mode-icon">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                                        </div>
                                        <h4 class="pl-mode-title">Only Embedded Page</h4>
                                        <p class="pl-mode-desc">Embeds pass directly inside a dedicated theme page via shortcode. No floating launcher.</p>
                                    </label>

                                    <label class="pl-mode-card <?php echo $integration_mode === 'hybrid' ? 'active' : ''; ?>">
                                        <input type="radio" name="perkledger_integration_mode" value="hybrid" <?php checked($integration_mode, 'hybrid'); ?> onchange="updateModeSections()" />
                                        <div class="pl-mode-icon">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                                        </div>
                                        <h4 class="pl-mode-title">Hybrid (Floating + Embedded)</h4>
                                        <p class="pl-mode-desc">Floating launcher on general pages. Automatically suppressed on your dedicated embedded pass page!</p>
                                    </label>
                                </div>
                            </div>

                            <!-- Dedicated Embedded Page Settings -->
                            <div id="section-embedded-settings" class="pl-section-box">
                                <div class="pl-group-header">
                                    <span class="pl-group-badge" id="badge-embedded-num">4</span>
                                    <span>Select Your Dedicated WordPress Page</span>
                                </div>

                                <div class="pl-wp-field">
                                    <label class="pl-wp-label">Select Embedded Pass Page</label>
                                    <select id="pl-embedded-select" name="perkledger_embedded_page_slug" class="pl-wp-select" onchange="updatePageUrlDisplay()">
                                        <option value="">-- Select a Published WordPress Page --</option>
                                        <?php
                                        if (!empty($wp_pages)) {
                                            foreach ($wp_pages as $page) {
                                                $slug = $page->post_name;
                                                $title = $page->post_title;
                                                $selected = ($embedded_slug === $slug) ? 'selected' : '';
                                                echo '<option value="' . esc_attr($slug) . '" ' . $selected . '>' . esc_html($title) . ' (/' . esc_html($slug) . ')</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                    <div class="pl-wp-help">Select the WordPress page where you have added the <code style="background: #F1F5F9; padding: 2px 6px; border-radius: 4px; color: #846B42; font-weight: bold;">[perkledger_pass]</code> shortcode.</div>
                                    
                                    <!-- One-Click Copyable Embedded Page URL Box (Hidden until page selected) -->
                                    <div class="pl-hash-copy-box" id="pl-page-url-box" style="<?php echo $embedded_slug ? 'display:flex;' : 'display:none;'; ?>">
                                        <div>
                                            <span style="display: block; font-size: 11px; color: #64748B;">Dedicated Embedded Pass Page URL:</span>
                                            <span class="pl-hash-url" id="pl-page-url-display"><?php echo $embedded_slug ? $site_home_url . $embedded_slug : ''; ?></span>
                                        </div>
                                        <button type="button" class="pl-hash-copy-btn" onclick="copyPageLink()">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                            <span id="pl-page-copy-text">Copy Page Link</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="pl-shortcode-box">
                                    <div>
                                        <strong style="font-size: 13px; color: #0F172A; display: block; margin-bottom: 2px;">Shortcode to paste inside your page:</strong>
                                        <span style="font-size: 12px; color: #64748B;">Place this shortcode anywhere inside your WordPress page editor:</span>
                                    </div>
                                    <span class="pl-wp-code">[perkledger_pass]</span>
                                </div>
                            </div>

                            <!-- Floating Launcher Settings -->
                            <div id="section-floating-settings" class="pl-section-box">
                                <div class="pl-group-header">
                                    <span class="pl-group-badge" id="badge-floating-num">5</span>
                                    <span>Floating Launcher & URL Hash Trigger Settings</span>
                                </div>

                                <div class="pl-wp-field">
                                    <div class="pl-toggle-wrap">
                                        <label class="pl-toggle">
                                            <input type="checkbox" name="perkledger_launcher_enabled" value="1" <?php checked($launcher_enabled, '1'); ?> />
                                            <span class="pl-slider"></span>
                                        </label>
                                        <div>
                                            <span style="font-size: 13px; font-weight: 700; color: #0F172A;">Enable Floating Store Loyalty Launcher Bubble</span>
                                            <div class="pl-wp-help">Shows floating button across all pages (except on pages where shortcode inline embed is placed).</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="pl-wp-field">
                                    <div class="pl-toggle-wrap">
                                        <label class="pl-toggle">
                                            <input type="checkbox" name="perkledger_hash_trigger_enabled" value="1" <?php checked($hash_enabled, '1'); ?> />
                                            <span class="pl-slider"></span>
                                        </label>
                                        <div>
                                            <span style="font-size: 13px; font-weight: 700; color: #0F172A;">Enable URL Hash Auto-Open Trigger (#reward)</span>
                                            <div class="pl-wp-help">Automatically opens the rewards modal drawer when customers visit a link ending with your custom hash tag (ideal for social media & campaign links).</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="pl-wp-field">
                                    <label class="pl-wp-label">Custom URL Hash Tag Name</label>
                                    <input type="text" id="pl-hash-input" name="perkledger_hash_name" value="<?php echo $hash_name; ?>" class="pl-wp-input" placeholder="reward" oninput="updateHashUrlDisplay()" />
                                    <div class="pl-wp-help">Example: Entering <strong>reward</strong> triggers auto-open on links like <code>yoursite.com/#reward</code></div>
                                    
                                    <!-- One-Click Copyable Campaign Link Box -->
                                    <div class="pl-hash-copy-box">
                                        <div>
                                            <span style="display: block; font-size: 11px; color: #64748B;">Shareable Social & Campaign URL:</span>
                                            <span class="pl-hash-url" id="pl-hash-url-display"><?php echo $site_home_url . '#' . ($hash_name ? $hash_name : 'reward'); ?></span>
                                        </div>
                                        <button type="button" class="pl-hash-copy-btn" onclick="copyHashLink()">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                            <span id="pl-copy-text">Copy Hash Link</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Visual Card Chooser: Launcher Display Style -->
                                <div class="pl-wp-field">
                                    <label class="pl-wp-label">Floating Launcher Display Style</label>
                                    <div class="pl-mode-grid">
                                        <label class="pl-card-choice style-choice <?php echo $launcher_style === 'with-label' ? 'active' : ''; ?>">
                                            <input type="radio" name="perkledger_launcher_style" value="with-label" <?php checked($launcher_style, 'with-label'); ?> onchange="updateChoiceCards(this, '.style-choice'); renderLiveSidebar();" />
                                            <div class="pl-card-preview">
                                                <div class="pl-mockup-pill">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                                                    <span>Pill Preview</span>
                                                </div>
                                            </div>
                                            <h4 class="pl-mode-title">Pill Button</h4>
                                            <p class="pl-mode-desc">Icon + Store Label Text</p>
                                        </label>

                                        <label class="pl-card-choice style-choice <?php echo $launcher_style === 'icon-only' ? 'active' : ''; ?>">
                                            <input type="radio" name="perkledger_launcher_style" value="icon-only" <?php checked($launcher_style, 'icon-only'); ?> onchange="updateChoiceCards(this, '.style-choice'); renderLiveSidebar();" />
                                            <div class="pl-card-preview">
                                                <div class="pl-mockup-circle">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                                                </div>
                                            </div>
                                            <h4 class="pl-mode-title">Badge Circle</h4>
                                            <p class="pl-mode-desc">Floating Circle Icon Only</p>
                                        </label>
                                    </div>
                                </div>

                                <div class="pl-wp-field">
                                    <label class="pl-wp-label">Floating Launcher Button Label (Merchant Brand)</label>
                                    <input type="text" id="pl-input-label" name="perkledger_launcher_text" value="<?php echo $launcher_text; ?>" class="pl-wp-input" placeholder="Rewards Pass" oninput="renderLiveSidebar()" />
                                </div>

                                <div class="pl-wp-field">
                                    <div class="pl-toggle-wrap">
                                        <label class="pl-toggle">
                                            <input type="checkbox" id="pl-input-hidetext" name="perkledger_launcher_hide_text" value="1" <?php checked($launcher_hide_text, '1'); ?> onchange="renderLiveSidebar()" />
                                            <span class="pl-slider"></span>
                                        </label>
                                        <span style="font-size: 13px; font-weight: 600; color: #0F172A;">Hide Label Text completely (Force Circle Icon Only)</span>
                                    </div>
                                </div>

                                <!-- Visual Icon Card Grid Chooser -->
                                <div class="pl-wp-field">
                                    <label class="pl-wp-label">Floating Launcher Material Icon</label>
                                    <div class="pl-icon-grid">
                                        <label class="pl-icon-card <?php echo $launcher_icon === 'card_membership' ? 'active' : ''; ?>">
                                            <input type="radio" name="perkledger_launcher_icon" value="card_membership" <?php checked($launcher_icon, 'card_membership'); ?> onchange="updateChoiceCards(this, '.pl-icon-card'); renderLiveSidebar();" />
                                            <div class="pl-icon-box">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                                            </div>
                                            <h5 class="pl-icon-title">Stamp Card</h5>
                                        </label>

                                        <label class="pl-icon-card <?php echo $launcher_icon === 'stars' ? 'active' : ''; ?>">
                                            <input type="radio" name="perkledger_launcher_icon" value="stars" <?php checked($launcher_icon, 'stars'); ?> onchange="updateChoiceCards(this, '.pl-icon-card'); renderLiveSidebar();" />
                                            <div class="pl-icon-box">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                            </div>
                                            <h5 class="pl-icon-title">Star Badge</h5>
                                        </label>

                                        <label class="pl-icon-card <?php echo $launcher_icon === 'card_giftcard' ? 'active' : ''; ?>">
                                            <input type="radio" name="perkledger_launcher_icon" value="card_giftcard" <?php checked($launcher_icon, 'card_giftcard'); ?> onchange="updateChoiceCards(this, '.pl-icon-card'); renderLiveSidebar();" />
                                            <div class="pl-icon-box">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><line x1="12" y1="22" x2="12" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>
                                            </div>
                                            <h5 class="pl-icon-title">Gift Box</h5>
                                        </label>

                                        <label class="pl-icon-card <?php echo $launcher_icon === 'loyalty' ? 'active' : ''; ?>">
                                            <input type="radio" name="perkledger_launcher_icon" value="loyalty" <?php checked($launcher_icon, 'loyalty'); ?> onchange="updateChoiceCards(this, '.pl-icon-card'); renderLiveSidebar();" />
                                            <div class="pl-icon-box">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                                            </div>
                                            <h5 class="pl-icon-title">Loyalty Heart</h5>
                                        </label>

                                        <label class="pl-icon-card <?php echo $launcher_icon === 'local_offer' ? 'active' : ''; ?>">
                                            <input type="radio" name="perkledger_launcher_icon" value="local_offer" <?php checked($launcher_icon, 'local_offer'); ?> onchange="updateChoiceCards(this, '.pl-icon-card'); renderLiveSidebar();" />
                                            <div class="pl-icon-box">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                                            </div>
                                            <h5 class="pl-icon-title">Offer Tag</h5>
                                        </label>

                                        <label class="pl-icon-card <?php echo $launcher_icon === 'workspace_premium' ? 'active' : ''; ?>">
                                            <input type="radio" name="perkledger_launcher_icon" value="workspace_premium" <?php checked($launcher_icon, 'workspace_premium'); ?> onchange="updateChoiceCards(this, '.pl-icon-card'); renderLiveSidebar();" />
                                            <div class="pl-icon-box">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"/></svg>
                                            </div>
                                            <h5 class="pl-icon-title">Premium Ribbon</h5>
                                        </label>

                                        <label class="pl-icon-card <?php echo $launcher_icon === 'confirmation_number' ? 'active' : ''; ?>">
                                            <input type="radio" name="perkledger_launcher_icon" value="confirmation_number" <?php checked($launcher_icon, 'confirmation_number'); ?> onchange="updateChoiceCards(this, '.pl-icon-card'); renderLiveSidebar();" />
                                            <div class="pl-icon-box">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v2z"/><line x1="13" y1="5" x2="13" y2="19" stroke-dasharray="2 2"/></svg>
                                            </div>
                                            <h5 class="pl-icon-title">Ticket Coupon</h5>
                                        </label>
                                    </div>
                                </div>

                                <!-- Visual Card Chooser: Launcher Corner Radius -->
                                <div class="pl-wp-field">
                                    <label class="pl-wp-label">Launcher Corner Radius (Curvature)</label>
                                    <div class="pl-mode-grid">
                                        <label class="pl-card-choice launcher-rad-choice <?php echo $launcher_radius === 'pill' ? 'active' : ''; ?>">
                                            <input type="radio" name="perkledger_launcher_radius" value="pill" <?php checked($launcher_radius, 'pill'); ?> onchange="updateChoiceCards(this, '.launcher-rad-choice'); renderLiveSidebar();" />
                                            <div class="pl-card-preview">
                                                <div class="pl-mockup-rad pill">Capsule</div>
                                            </div>
                                            <h4 class="pl-mode-title">Pill / Capsule</h4>
                                            <p class="pl-mode-desc">Full Rounded (9999px)</p>
                                        </label>

                                        <label class="pl-card-choice launcher-rad-choice <?php echo $launcher_radius === 'subtle' ? 'active' : ''; ?>">
                                            <input type="radio" name="perkledger_launcher_radius" value="subtle" <?php checked($launcher_radius, 'subtle'); ?> onchange="updateChoiceCards(this, '.launcher-rad-choice'); renderLiveSidebar();" />
                                            <div class="pl-card-preview">
                                                <div class="pl-mockup-rad rounded">12px</div>
                                            </div>
                                            <h4 class="pl-mode-title">Subtle (12px)</h4>
                                            <p class="pl-mode-desc">Soft Rounded Corner</p>
                                        </label>

                                        <label class="pl-card-choice launcher-rad-choice <?php echo $launcher_radius === 'sharp' ? 'active' : ''; ?>">
                                            <input type="radio" name="perkledger_launcher_radius" value="sharp" <?php checked($launcher_radius, 'sharp'); ?> onchange="updateChoiceCards(this, '.launcher-rad-choice'); renderLiveSidebar();" />
                                            <div class="pl-card-preview">
                                                <div class="pl-mockup-rad sharp">6px</div>
                                            </div>
                                            <h4 class="pl-mode-title">Sharp (6px)</h4>
                                            <p class="pl-mode-desc">Crisp Square Corner</p>
                                        </label>
                                    </div>
                                </div>

                                <!-- Visual Card Chooser: Launcher Border Thickness -->
                                <div class="pl-wp-field">
                                    <label class="pl-wp-label">Launcher Border Thickness & Style</label>
                                    <div class="pl-mode-grid">
                                        <label class="pl-card-choice thick-choice <?php echo $launcher_border_width === '1px' ? 'active' : ''; ?>">
                                            <input type="radio" name="perkledger_launcher_border_width" value="1px" <?php checked($launcher_border_width, '1px'); ?> onchange="updateChoiceCards(this, '.thick-choice'); renderLiveSidebar();" />
                                            <div class="pl-thick-preview pl-thick-1">1px</div>
                                            <h4 class="pl-mode-title">1px Thin</h4>
                                            <p class="pl-mode-desc">Subtle Standard Border</p>
                                        </label>

                                        <label class="pl-card-choice thick-choice <?php echo $launcher_border_width === '2px' ? 'active' : ''; ?>">
                                            <input type="radio" name="perkledger_launcher_border_width" value="2px" <?php checked($launcher_border_width, '2px'); ?> onchange="updateChoiceCards(this, '.thick-choice'); renderLiveSidebar();" />
                                            <div class="pl-thick-preview pl-thick-2">2px</div>
                                            <h4 class="pl-mode-title">2px Medium</h4>
                                            <p class="pl-mode-desc">Medium Accent Border</p>
                                        </label>

                                        <label class="pl-card-choice thick-choice <?php echo $launcher_border_width === '3px' ? 'active' : ''; ?>">
                                            <input type="radio" name="perkledger_launcher_border_width" value="3px" <?php checked($launcher_border_width, '3px'); ?> onchange="updateChoiceCards(this, '.thick-choice'); renderLiveSidebar();" />
                                            <div class="pl-thick-preview pl-thick-3">3px</div>
                                            <h4 class="pl-mode-title">3px Thick</h4>
                                            <p class="pl-mode-desc">Bold Highlight Border</p>
                                        </label>

                                        <label class="pl-card-choice thick-choice <?php echo $launcher_border_width === '0px' ? 'active' : ''; ?>">
                                            <input type="radio" name="perkledger_launcher_border_width" value="0px" <?php checked($launcher_border_width, '0px'); ?> onchange="updateChoiceCards(this, '.thick-choice'); renderLiveSidebar();" />
                                            <div class="pl-thick-preview pl-thick-0">0px</div>
                                            <h4 class="pl-mode-title">0px None</h4>
                                            <p class="pl-mode-desc">Clean Borderless</p>
                                        </label>
                                    </div>
                                </div>

                                <!-- Visual Card Chooser: Launcher Position & Drawer Opening Direction -->
                                <div class="pl-wp-field">
                                    <label class="pl-wp-label">Launcher Position & Drawer Opening Direction</label>
                                    <div class="pl-mode-grid">
                                        <label class="pl-card-choice pos-choice <?php echo $launcher_position === 'bottom-right' ? 'active' : ''; ?>">
                                            <input type="radio" name="perkledger_launcher_position" value="bottom-right" <?php checked($launcher_position, 'bottom-right'); ?> onchange="updateChoiceCards(this, '.pos-choice')" />
                                            <div class="pl-card-preview">
                                                <div class="pl-mockup-screen">
                                                    <div class="pl-mockup-btn br"></div>
                                                    <div class="pl-mockup-arrow br">←</div>
                                                </div>
                                            </div>
                                            <h4 class="pl-mode-title">Bottom Right</h4>
                                            <p class="pl-mode-desc">Slides from Right Side</p>
                                        </label>

                                        <label class="pl-card-choice pos-choice <?php echo $launcher_position === 'bottom-left' ? 'active' : ''; ?>">
                                            <input type="radio" name="perkledger_launcher_position" value="bottom-left" <?php checked($launcher_position, 'bottom-left'); ?> onchange="updateChoiceCards(this, '.pos-choice')" />
                                            <div class="pl-card-preview">
                                                <div class="pl-mockup-screen">
                                                    <div class="pl-mockup-btn bl"></div>
                                                    <div class="pl-mockup-arrow bl">→</div>
                                                </div>
                                            </div>
                                            <h4 class="pl-mode-title">Bottom Left</h4>
                                            <p class="pl-mode-desc">Slides from Left Side</p>
                                        </label>

                                        <label class="pl-card-choice pos-choice <?php echo $launcher_position === 'bottom-center' ? 'active' : ''; ?>">
                                            <input type="radio" name="perkledger_launcher_position" value="bottom-center" <?php checked($launcher_position, 'bottom-center'); ?> onchange="updateChoiceCards(this, '.pos-choice')" />
                                            <div class="pl-card-preview">
                                                <div class="pl-mockup-screen">
                                                    <div class="pl-mockup-btn bc"></div>
                                                    <div class="pl-mockup-arrow bc">↑</div>
                                                </div>
                                            </div>
                                            <h4 class="pl-mode-title">Bottom Center</h4>
                                            <p class="pl-mode-desc">Slides Up from Bottom</p>
                                        </label>
                                    </div>
                                </div>

                                </div>
                            </div>

                            <!-- Modal Appearance Settings -->
                            <div id="section-modal-settings" class="pl-section-box">
                                <div class="pl-group-header">
                                    <span class="pl-group-badge" id="badge-modal-num">5</span>
                                    <span>Modal Drawer Appearance & Backdrop</span>
                                </div>

                                <!-- Visual Card Chooser: Modal Corner Radius -->
                                <div class="pl-wp-field">
                                    <label class="pl-wp-label">Modal Drawer Corner Radius (Curvature)</label>
                                    <div class="pl-mode-grid">
                                        <label class="pl-card-choice rad-choice <?php echo $modal_radius === 'rounded' ? 'active' : ''; ?>">
                                            <input type="radio" name="perkledger_modal_radius" value="rounded" <?php checked($modal_radius, 'rounded'); ?> onchange="updateChoiceCards(this, '.rad-choice'); renderLiveSidebar();" />
                                            <div class="pl-card-preview">
                                                <div class="pl-mockup-rad rounded">24px</div>
                                            </div>
                                            <h4 class="pl-mode-title">Rounded (24px)</h4>
                                            <p class="pl-mode-desc">Standard Smooth Curve</p>
                                        </label>

                                        <label class="pl-card-choice rad-choice <?php echo $modal_radius === 'subtle' ? 'active' : ''; ?>">
                                            <input type="radio" name="perkledger_modal_radius" value="subtle" <?php checked($modal_radius, 'subtle'); ?> onchange="updateChoiceCards(this, '.rad-choice'); renderLiveSidebar();" />
                                            <div class="pl-card-preview">
                                                <div class="pl-mockup-rad subtle">12px</div>
                                            </div>
                                            <h4 class="pl-mode-title">Subtle (12px)</h4>
                                            <p class="pl-mode-desc">Modern Soft Curve</p>
                                        </label>

                                        <label class="pl-card-choice rad-choice <?php echo $modal_radius === 'sharp' ? 'active' : ''; ?>">
                                            <input type="radio" name="perkledger_modal_radius" value="sharp" <?php checked($modal_radius, 'sharp'); ?> onchange="updateChoiceCards(this, '.rad-choice'); renderLiveSidebar();" />
                                            <div class="pl-card-preview">
                                                <div class="pl-mockup-rad sharp">6px</div>
                                            </div>
                                            <h4 class="pl-mode-title">Sharp (6px)</h4>
                                            <p class="pl-mode-desc">Crisp Square Corner</p>
                                        </label>
                                    </div>
                                </div>

                                <!-- Visual Card Chooser: Modal Border Thickness -->
                                <div class="pl-wp-field">
                                    <label class="pl-wp-label">Modal Drawer Border Thickness & Style</label>
                                    <div class="pl-mode-grid">
                                        <label class="pl-card-choice modal-thick-choice <?php echo $modal_border_width === '1px' ? 'active' : ''; ?>">
                                            <input type="radio" name="perkledger_modal_border_width" value="1px" <?php checked($modal_border_width, '1px'); ?> onchange="updateChoiceCards(this, '.modal-thick-choice'); renderLiveSidebar();" />
                                            <div class="pl-thick-preview pl-thick-1">1px</div>
                                            <h4 class="pl-mode-title">1px Thin</h4>
                                            <p class="pl-mode-desc">Subtle Border</p>
                                        </label>

                                        <label class="pl-card-choice modal-thick-choice <?php echo $modal_border_width === '2px' ? 'active' : ''; ?>">
                                            <input type="radio" name="perkledger_modal_border_width" value="2px" <?php checked($modal_border_width, '2px'); ?> onchange="updateChoiceCards(this, '.modal-thick-choice'); renderLiveSidebar();" />
                                            <div class="pl-thick-preview pl-thick-2">2px</div>
                                            <h4 class="pl-mode-title">2px Medium</h4>
                                            <p class="pl-mode-desc">Medium Accent</p>
                                        </label>

                                        <label class="pl-card-choice modal-thick-choice <?php echo $modal_border_width === '3px' ? 'active' : ''; ?>">
                                            <input type="radio" name="perkledger_modal_border_width" value="3px" <?php checked($modal_border_width, '3px'); ?> onchange="updateChoiceCards(this, '.modal-thick-choice'); renderLiveSidebar();" />
                                            <div class="pl-thick-preview pl-thick-3">3px</div>
                                            <h4 class="pl-mode-title">3px Thick</h4>
                                            <p class="pl-mode-desc">Thick Highlight</p>
                                        </label>

                                        <label class="pl-card-choice modal-thick-choice <?php echo $modal_border_width === '0px' ? 'active' : ''; ?>">
                                            <input type="radio" name="perkledger_modal_border_width" value="0px" <?php checked($modal_border_width, '0px'); ?> onchange="updateChoiceCards(this, '.modal-thick-choice'); renderLiveSidebar();" />
                                            <div class="pl-thick-preview pl-thick-0">0px</div>
                                            <h4 class="pl-mode-title">0px None</h4>
                                            <p class="pl-mode-desc">No Border</p>
                                        </label>
                                    </div>
                                </div>

                                </div>
                            </div>

                            <div style="margin-top: 32px;">
                                <button type="submit" class="pl-wp-btn">Save PerkLedger Settings</button>
                            </div>
                        </form>
                    </div>

                    <!-- Right Column: Sticky Live Interface Preview Sidebar -->
                    <div class="pl-layout-sidebar">
                        <div class="pl-sidebar-title">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <span>Live Interface Preview</span>
                        </div>

                        <!-- 1. Floating Launcher Button Real-Time Preview -->
                        <div class="pl-sidebar-section">
                            <span class="pl-sidebar-sub">Floating Launcher Button:</span>
                            <div class="pl-live-launcher-stage">
                                <div id="pl-sidebar-live-btn" class="pl-live-button pill">
                                    <span id="pl-sidebar-live-icon"></span>
                                    <span id="pl-sidebar-live-text">Rewards Pass</span>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Modal Drawer Wireframe Real-Time Preview -->
                        <div class="pl-sidebar-section">
                            <span class="pl-sidebar-sub">Modal Drawer Screen Mockup:</span>
                            <div class="pl-drawer-mockup-stage">
                                <div id="pl-sidebar-drawer-card" class="pl-drawer-mockup-card">
                                    <div class="pl-drawer-mockup-header">
                                        <div class="pl-drawer-mockup-dot"></div>
                                        <div id="pl-sidebar-close-btn" class="pl-drawer-mockup-close">✕</div>
                                    </div>
                                    <div class="pl-drawer-mockup-body">
                                        <div style="width: 32px; height: 32px; border-radius: 50%; background: #1E293B; margin-bottom: 6px; display: inline-flex; align-items: center; justify-content: center; color: #A68958;">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                        </div>
                                        <span style="font-size: 11px; font-weight: bold; color: #FFFFFF; display: block;">PerkLedger Stamp Card</span>
                                        <span style="font-size: 9px; color: #94A3B8;">Collect Stamps & Win Rewards</span>
                                        
                                        <div class="pl-drawer-mockup-stamp-grid">
                                            <div class="pl-drawer-stamp-dot active"></div>
                                            <div class="pl-drawer-stamp-dot active"></div>
                                            <div class="pl-drawer-stamp-dot active"></div>
                                            <div class="pl-drawer-stamp-dot"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Responsive & Clean Footer Emblem Bar -->
                <div class="pl-footer-emblem-bar">
                    <div class="pl-footer-left">
                        Powered by <a href="https://perkledger.com" target="_blank" rel="noopener">PerkLedger Engine</a>
                    </div>
                    <div class="pl-footer-right">
                        <svg width="18" height="18" viewBox="0 0 320 390" fill="#A68958"><path fill-rule="evenodd" clip-rule="evenodd" d="M50.3512 0.696272C49.6946 1.31479 49.1562 3.721 49.1562 6.04545C49.1562 9.98484 49.7406 10.3396 57.774 11.2928C80.0521 13.9366 91.2092 21.6195 97.025 38.3241C98.2643 41.8825 99.2377 68.1795 99.8844 115.46L100.863 187.197L155.852 179.684V104.3C155.852 23.0338 155.913 22.4277 164.486 18.0132C169.979 15.1875 189.64 16.0281 201.671 19.6035C235.498 29.6587 253.284 57.1156 253.259 99.2504C253.23 151.18 229.686 180.292 177.191 193.313C152.243 199.501 77.3913 209.202 22.8926 213.308L10.5815 214.235L17.5825 208.415C21.4334 205.213 24.5735 202.092 24.5587 201.479C24.5292 200.149 0.126937 214.492 0.000543444 215.914C-0.129133 217.355 22.9911 230.007 24.0071 229.053C24.4832 228.605 21.87 226.287 18.1996 223.901C14.5293 221.513 11.4991 219.15 11.4646 218.649C11.4302 218.149 29.3157 217.292 51.2081 216.744C133.517 214.69 198.705 207.884 229.046 198.178C268.837 185.451 292.576 164.86 305.69 131.696C309.943 120.942 309.693 116.649 311.358 99.2504C312.275 75.2685 309.851 63.398 300.685 46.9833C289.163 26.3563 267.968 11.3422 240.06 4.03719C227.793 0.82738 223.578 0.663898 139.401 0.130214C91.0828 -0.176731 51.0095 0.0777545 50.3512 0.696272ZM129.589 230.706C117.401 231.899 105.836 233.173 103.89 233.537C100.36 234.198 100.351 234.336 99.6464 290.119C99.0736 335.624 98.458 347.418 96.3471 353.437C90.8941 368.984 79.7156 376.892 60.361 378.898C50.1181 379.959 49.9704 380.042 49.4632 384.987L48.9478 390H300.24L301.077 386.53C301.538 384.62 305.985 367.303 310.957 348.047C315.931 328.79 320 312.678 320 312.24C320 311.801 318.6 310.74 316.888 309.88C314.189 308.522 312.853 309.645 306.816 318.351C296.635 333.036 278.502 348.262 262.177 355.835C242.844 364.806 229.814 367.44 204.276 367.547C183.754 367.632 182.561 367.457 173.104 363.007C156.256 355.078 156.809 357.478 155.852 288.326C155.33 250.635 154.434 228.973 153.39 228.803C152.487 228.656 141.777 229.512 129.589 230.706Z"/></svg>
                        <span>perkledger.com</span>
                    </div>
                </div>
            </div>
        </div>

        <script>
            var baseHomeUrl = "<?php echo esc_js($site_home_url); ?>";
            var MATERIAL_SVGS = {
                'card_membership': '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>',
                'stars': '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
                'card_giftcard': '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><line x1="12" y1="22" x2="12" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>',
                'loyalty': '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>',
                'local_offer': '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>',
                'workspace_premium': '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"/></svg>',
                'confirmation_number': '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v2z"/><line x1="13" y1="5" x2="13" y2="19" stroke-dasharray="2 2"/></svg>'
            };

            function getCheckedValue(name) {
                var elem = document.querySelector('input[name="' + name + '"]:checked');
                return elem ? elem.value : null;
            }

            function renderLiveSidebar() {
                var btn = document.getElementById('pl-sidebar-live-btn');
                var iconContainer = document.getElementById('pl-sidebar-live-icon');
                var textContainer = document.getElementById('pl-sidebar-live-text');
                var drawerCard = document.getElementById('pl-sidebar-drawer-card');
                var closeBtn = document.getElementById('pl-sidebar-close-btn');

                var styleVal = getCheckedValue('perkledger_launcher_style') || 'with-label';
                var iconVal = getCheckedValue('perkledger_launcher_icon') || 'card_membership';
                var radiusVal = getCheckedValue('perkledger_launcher_radius') || 'pill';
                var thickVal = getCheckedValue('perkledger_launcher_border_width') || '1px';
                var hideTextVal = document.getElementById('pl-input-hidetext') ? document.getElementById('pl-input-hidetext').checked : false;

                var modalRadVal = getCheckedValue('perkledger_modal_radius') || 'rounded';
                var modalThickVal = getCheckedValue('perkledger_modal_border_width') || '1px';
                var modalBorderColor = document.getElementById('pl-color-modalborder-input') ? document.getElementById('pl-color-modalborder-input').value : '#A68958';

                var labelInput = document.getElementById('pl-input-label');
                var labelText = labelInput ? labelInput.value : 'Rewards Pass';

                var bgColor = document.getElementById('pl-color-bg-picker') ? document.getElementById('pl-color-bg-picker').value : '#7C2A2A';
                var textColor = document.getElementById('pl-color-text-picker') ? document.getElementById('pl-color-text-picker').value : '#FFFFFF';
                var iconColor = document.getElementById('pl-color-icon-picker') ? document.getElementById('pl-color-icon-picker').value : '#FCBD0B';
                var borderColor = document.getElementById('pl-color-border-picker') ? document.getElementById('pl-color-border-picker').value : '#A68958';

                if (btn && iconContainer && textContainer) {
                    iconContainer.innerHTML = MATERIAL_SVGS[iconVal] || MATERIAL_SVGS['card_membership'];
                    iconContainer.style.color = iconColor;

                    if (styleVal === 'icon-only' || hideTextVal) {
                        btn.className = 'pl-live-button circle';
                        textContainer.style.display = 'none';
                    } else {
                        btn.className = 'pl-live-button pill';
                        textContainer.style.display = 'inline';
                        textContainer.textContent = labelText ? labelText : 'Rewards Pass';
                        textContainer.style.color = textColor;

                        // Radius
                        if (radiusVal === 'sharp') btn.style.borderRadius = '6px';
                        else if (radiusVal === 'subtle') btn.style.borderRadius = '12px';
                        else btn.style.borderRadius = '9999px';
                    }

                    btn.style.backgroundColor = bgColor;
                    if (thickVal === '0px') {
                        btn.style.border = 'none';
                    } else {
                        btn.style.border = thickVal + ' solid ' + borderColor;
                    }
                }

                // Modal Drawer Mockup Updates
                if (drawerCard) {
                    if (modalRadVal === 'sharp') drawerCard.style.borderRadius = '6px';
                    else if (modalRadVal === 'subtle') drawerCard.style.borderRadius = '12px';
                    else drawerCard.style.borderRadius = '24px';

                    if (modalThickVal === '0px') {
                        drawerCard.style.border = 'none';
                    } else {
                        drawerCard.style.border = modalThickVal + ' solid ' + modalBorderColor;
                    }
                }
                if (closeBtn) {
                    closeBtn.style.backgroundColor = bgColor;
                    closeBtn.style.color = iconColor;
                }
            }

            function updatePageUrlDisplay() {
                var sel = document.getElementById('pl-embedded-select');
                var box = document.getElementById('pl-page-url-box');
                var disp = document.getElementById('pl-page-url-display');
                if (sel && box && disp) {
                    var val = sel.value;
                    if (!val) {
                        box.style.display = 'none';
                    } else {
                        box.style.display = 'flex';
                        disp.textContent = baseHomeUrl + val;
                    }
                }
            }

            function copyPageLink() {
                var disp = document.getElementById('pl-page-url-display');
                var btnText = document.getElementById('pl-page-copy-text');
                if (disp && disp.textContent) {
                    navigator.clipboard.writeText(disp.textContent).then(function() {
                        if (btnText) {
                            btnText.textContent = 'Copied!';
                            setTimeout(function() { btnText.textContent = 'Copy Page Link'; }, 2000);
                        }
                    });
                }
            }

            function updateHashUrlDisplay() {
                var inp = document.getElementById('pl-hash-input');
                var disp = document.getElementById('pl-hash-url-display');
                if (inp && disp) {
                    var val = inp.value.replace(/[^a-zA-Z0-9_-]/g, '');
                    disp.textContent = baseHomeUrl + '#' + (val ? val : 'reward');
                }
            }

            function copyHashLink() {
                var disp = document.getElementById('pl-hash-url-display');
                var btnText = document.getElementById('pl-copy-text');
                if (disp) {
                    navigator.clipboard.writeText(disp.textContent).then(function() {
                        if (btnText) {
                            btnText.textContent = 'Copied!';
                            setTimeout(function() { btnText.textContent = 'Copy Hash Link'; }, 2000);
                        }
                    });
                }
            }

            function updateChoiceCards(radioElem, cardSelector) {
                var cards = document.querySelectorAll(cardSelector);
                for (var i = 0; i < cards.length; i++) {
                    var r = cards[i].querySelector('input[type="radio"]');
                    if (r && r.checked) {
                        cards[i].classList.add('active');
                    } else {
                        cards[i].classList.remove('active');
                    }
                }
            }

            function updateModeSections() {
                var radios = document.getElementsByName('perkledger_integration_mode');
                var selectedMode = 'hybrid';
                for (var i = 0; i < radios.length; i++) {
                    var card = radios[i].closest('.pl-mode-card');
                    if (radios[i].checked) {
                        selectedMode = radios[i].value;
                        if (card) card.classList.add('active');
                    } else {
                        if (card) card.classList.remove('active');
                    }
                }

                var secEmbedded = document.getElementById('section-embedded-settings');
                var secFloating = document.getElementById('section-floating-settings');
                var secModal = document.getElementById('section-modal-settings');

                var badgeEmbedded = document.getElementById('badge-embedded-num');
                var badgeFloating = document.getElementById('badge-floating-num');
                var badgeModal = document.getElementById('badge-modal-num');

                if (selectedMode === 'floating') {
                    if (secEmbedded) secEmbedded.style.display = 'none';
                    if (secFloating) secFloating.style.display = 'block';
                    if (secModal) secModal.style.display = 'block';

                    if (badgeFloating) badgeFloating.textContent = '3';
                    if (badgeModal) badgeModal.textContent = '4';
                } else if (selectedMode === 'embedded') {
                    if (secEmbedded) secEmbedded.style.display = 'block';
                    if (secFloating) secFloating.style.display = 'none';
                    if (secModal) secModal.style.display = 'block';

                    if (badgeEmbedded) badgeEmbedded.textContent = '3';
                    if (badgeModal) badgeModal.textContent = '4';
                } else {
                    // Hybrid
                    if (secEmbedded) secEmbedded.style.display = 'block';
                    if (secFloating) secFloating.style.display = 'block';
                    if (secModal) secModal.style.display = 'block';

                    if (badgeEmbedded) badgeEmbedded.textContent = '3';
                    if (badgeFloating) badgeFloating.textContent = '4';
                    if (badgeModal) badgeModal.textContent = '5';
                }

                updatePageUrlDisplay();
                renderLiveSidebar();
            }

            document.addEventListener('DOMContentLoaded', function() {
                updateModeSections();
                renderLiveSidebar();
            });
        </script>
        <?php
    }

    public function inject_embed_script_tag() {
        $merchant = esc_attr(get_option('perkledger_merchant_id', ''));
        if (!$merchant) return;

        $mode = get_option('perkledger_integration_mode', 'hybrid');
        $modal_radius = esc_attr(get_option('perkledger_modal_radius', 'rounded'));
        $modal_border_width = esc_attr(get_option('perkledger_modal_border_width', '1px'));

        $launcher_opt = get_option('perkledger_launcher_enabled', '1');
        $launcher = ($mode === 'embedded' || $launcher_opt === '0' || $launcher_opt === 0 || $launcher_opt === 'no') ? 'no' : 'yes';
        
        $style = esc_attr(get_option('perkledger_launcher_style', 'with-label'));
        $text = esc_attr(get_option('perkledger_launcher_text', 'Rewards Pass'));
        $hide_text = get_option('perkledger_launcher_hide_text', '0') === '1' ? 'yes' : 'no';
        $icon = esc_attr(get_option('perkledger_launcher_icon', 'card_membership'));
        $launcher_radius = esc_attr(get_option('perkledger_launcher_radius', 'pill'));
        $position = esc_attr(get_option('perkledger_launcher_position', 'bottom-right'));

        $hash_enabled = get_option('perkledger_hash_trigger_enabled', '1') === '1' ? 'yes' : 'no';
        $hash_name = esc_attr(get_option('perkledger_hash_name', 'reward'));

        $theme_bg = esc_attr(get_option('perkledger_theme_bg', '#0F172A'));
        $theme_surface = esc_attr(get_option('perkledger_theme_surface', '#1E293B'));
        $theme_text = esc_attr(get_option('perkledger_theme_text', '#FFFFFF'));
        $theme_accent = esc_attr(get_option('perkledger_theme_accent', '#FCBD0B'));
        $font_size = esc_attr(get_option('perkledger_font_size', 'medium'));
        $inline_width = esc_attr(get_option('perkledger_inline_width', '100%'));

        printf(
            '<script src="https://pass.perkledger.com/assets/embed.js?v=%s" data-merchant="%s" data-modal-radius="%s" data-modal-border-width="%s" data-launcher="%s" data-launcher-style="%s" data-launcher-text="%s" data-launcher-hide-text="%s" data-launcher-icon="%s" data-launcher-radius="%s" data-position="%s" data-hash-trigger="%s" data-hash-name="%s" data-theme-bg="%s" data-theme-surface="%s" data-theme-text="%s" data-theme-accent="%s" data-font-size="%s" data-inline-width="%s" async></script>' . "\n",
            PERKLEDGER_WP_BUILD,
            $merchant, $modal_radius, $modal_border_width,
            $launcher, $style, $text, $hide_text,
            $icon, $launcher_radius, $position,
            $hash_enabled, $hash_name,
            $theme_bg, $theme_surface, $theme_text, $theme_accent, $font_size, $inline_width
        );
    }

    public function render_shortcode($atts) {
        $merchant = get_option('perkledger_merchant_id', '');
        if (!$merchant) return '';
        $inline_width = esc_attr(get_option('perkledger_inline_width', '100%'));
        return '<div id="perkledger-widget" data-merchant="' . esc_attr($merchant) . '" style="max-width: ' . $inline_width . '; margin: 30px auto; width: 100%; min-height: 720px; border-radius: 24px; overflow: hidden; box-shadow: 0 12px 40px rgba(0,0,0,0.12); display: block;"></div>';
    }
}

new PerkLedgerRewardsPlugin();
