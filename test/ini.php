<?php
/**
 * KiwiPanel — PHP Configuration Inspector
 *
 * A dynamic, generic PHP info page that reads and displays the active PHP
 * configuration for any website. No hardcoded values — it simply shows what
 * is currently configured on the server.
 *
 * Usage:
 *   1. Copy this file to your website's public_html/ directory
 *   2. Open https://yourdomain.com/ini.php in your browser
 *   3. Delete the file immediately after testing
 *
 * ⚠️  WARNING: This file exposes sensitive server information.
 *     Delete it as soon as you're done inspecting.
 */

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Content-Type: text/html; charset=UTF-8");

if (isset($_GET["phpinfo"])) {
    phpinfo();
    exit();
}

function format_bool(string $value): string
{
    $on = ["1", "on", "yes", "true"];
    return in_array(strtolower(trim($value)), $on, true) ? "On" : "Off";
}

function error_reporting_label(int $level): string
{
    if ($level === E_ALL) {
        return "E_ALL";
    }
    if ($level === (E_ALL & ~E_DEPRECATED & ~E_STRICT)) {
        return "E_ALL & ~E_DEPRECATED & ~E_STRICT";
    }
    if ($level === (E_ALL & ~E_NOTICE)) {
        return "E_ALL & ~E_NOTICE";
    }
    if ($level === (E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED)) {
        return "E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED";
    }
    if ($level === (E_ERROR | E_WARNING | E_PARSE)) {
        return "E_ERROR | E_WARNING | E_PARSE";
    }
    return (string) $level;
}

function ini(string $key, string $fallback = "(not set)"): string
{
    $v = ini_get($key);
    return $v !== false && $v !== "" ? $v : $fallback;
}

$resource_limits = [
    "memory_limit" => ini("memory_limit"),
    "max_execution_time" => ini("max_execution_time"),
    "max_input_time" => ini("max_input_time"),
    "max_input_vars" => ini("max_input_vars"),
];

$file_upload = [
    "upload_max_filesize" => ini("upload_max_filesize"),
    "post_max_size" => ini("post_max_size"),
    "max_file_uploads" => ini("max_file_uploads"),
];

$error_handling = [
    "display_errors" => format_bool(ini_get("display_errors") ?: "0"),
    "error_reporting" =>
        error_reporting_label(error_reporting()) .
        " (" .
        error_reporting() .
        ")",
    "log_errors" => format_bool(ini_get("log_errors") ?: "0"),
    "error_log" => ini("error_log"),
];

$session = [
    "session.gc_maxlifetime" => ini("session.gc_maxlifetime"),
    "session.cookie_lifetime" => ini("session.cookie_lifetime"),
];

$other = [
    "date.timezone" => ini("date.timezone"),
    "opcache.enable" => format_bool(ini_get("opcache.enable") ?: "0"),
    "opcache.memory_consumption" => ini("opcache.memory_consumption") . " MB",
];

$security = [
    "open_basedir" => ini("open_basedir"),
    "disable_functions" => ini("disable_functions"),
    "expose_php" => format_bool(ini_get("expose_php") ?: "0"),
    "allow_url_include" => format_bool(ini_get("allow_url_include") ?: "0"),
    "session.cookie_httponly" => format_bool(
        ini_get("session.cookie_httponly") ?: "0",
    ),
    "session.cookie_secure" => format_bool(
        ini_get("session.cookie_secure") ?: "0",
    ),
    "session.use_strict_mode" => format_bool(
        ini_get("session.use_strict_mode") ?: "0",
    ),
];

$extensions = get_loaded_extensions();
sort($extensions);

$zend_extensions = get_loaded_extensions(true);
sort($zend_extensions);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Configuration — KiwiPanel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Oracle Redwood-inspired palette — warm, light, natural */
            --bg-base:      #f5f3f0;
            --bg-surface:   #ffffff;
            --bg-elevated:  #fafaf8;
            --bg-hover:     #f0ede8;
            --border:       #e4dfd8;
            --border-light: #ebe7e1;
            --text-primary: #1a1614;
            --text-secondary: #6b5e54;
            --text-muted:   #9a8e82;
            --accent:       #c74634;
            --accent-hover: #a83a2c;
            --accent-dim:   #fdf0ee;
            --green:        #1a7a3a;
            --green-dim:    #ecf7f0;
            --red:          #c74634;
            --red-dim:      #fdf0ee;
            --blue:         #0b6bcb;
            --blue-dim:     #eef4fb;
            --amber:        #b45309;
            --amber-dim:    #fef7ec;
            --font-sans:    'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            --font-mono:    'JetBrains Mono', 'Fira Code', monospace;
            --shadow-sm:    0 1px 2px rgba(26, 22, 20, 0.05);
            --shadow-md:    0 4px 12px rgba(26, 22, 20, 0.08);
            --shadow-lg:    0 8px 24px rgba(26, 22, 20, 0.1);
            --radius:       12px;
            --radius-sm:    8px;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html { scroll-behavior: smooth; }

        body {
            font-family: var(--font-sans);
            background: var(--bg-base);
            color: var(--text-primary);
            font-size: 15px;
            line-height: 1.6;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        /* ── Topbar ── */
        .topbar {
            position: sticky; top: 0; z-index: 100;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            border-bottom: 1px solid var(--border);
            padding: 0 28px;
            display: flex; align-items: center; justify-content: space-between;
            height: 56px;
            box-shadow: var(--shadow-sm);
        }
        .topbar-brand {
            display: flex; align-items: center; gap: 12px;
        }
        .topbar-logo {
            width: 32px; height: 32px;
            background: linear-gradient(135deg, var(--accent), #e05a47);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; line-height: 1;
            box-shadow: 0 2px 6px rgba(199, 70, 52, 0.25);
        }
        .topbar-name {
            font-weight: 700; font-size: 15px; letter-spacing: -0.02em;
            color: var(--text-primary);
        }
        .topbar-sep {
            width: 1px; height: 20px; background: var(--border); margin: 0 4px;
        }
        .topbar-page { color: var(--text-secondary); font-size: 13px; font-weight: 500; }
        .topbar-right { display: flex; align-items: center; gap: 12px; }
        .topbar-time {
            font-family: var(--font-mono); font-size: 11.5px; color: var(--text-muted);
            letter-spacing: 0.02em;
            background: var(--bg-base);
            padding: 4px 10px;
            border-radius: 6px;
        }

        /* ── Alert Banner ── */
        .alert {
            background: var(--amber-dim);
            border-left: 4px solid var(--amber);
            padding: 12px 28px;
            display: flex; align-items: center; gap: 10px;
            font-size: 15px; color: var(--amber);
            letter-spacing: 0.01em;
        }
        .alert strong { color: #92400e; font-weight: 600; }
        .alert-icon { font-size: 16px; flex-shrink: 0; }

        /* ── Layout ── */
        .layout {
            display: grid;
            grid-template-columns: 240px 1fr;
            gap: 0;
            min-height: calc(100vh - 56px);
        }

        /* ── Sidebar Nav ── */
        .sidebar {
            background: var(--bg-surface);
            border-right: 1px solid var(--border);
            padding: 28px 0;
            position: sticky; top: 56px;
            height: calc(100vh - 56px);
            overflow-y: auto;
        }
        .sidebar-section-label {
            font-size: 10px; font-weight: 700; letter-spacing: 0.12em;
            text-transform: uppercase; color: var(--text-muted);
            padding: 0 20px 10px;
        }
        .sidebar-link {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 20px; color: var(--text-secondary);
            text-decoration: none; font-size: 15px; font-weight: 500;
            transition: all 0.15s ease;
            border-left: 3px solid transparent;
            margin-left: -1px;
            border-radius: 0 6px 6px 0;
            margin-right: 8px;
        }
        .sidebar-link:hover {
            color: var(--text-primary); background: var(--bg-hover);
        }
        .sidebar-link.active {
            color: var(--accent); border-left-color: var(--accent);
            background: var(--accent-dim);
            font-weight: 600;
        }
        .sidebar-icon { font-size: 14px; width: 20px; text-align: center; opacity: 0.8; }
        .sidebar-divider {
            height: 1px; background: var(--border); margin: 14px 20px 18px;
        }
        .sidebar-badge {
            margin-left: auto; background: var(--bg-base);
            color: var(--text-muted); font-family: var(--font-mono);
            font-size: 10.5px; padding: 2px 8px; border-radius: 999px;
            border: 1px solid var(--border);
            font-weight: 600;
        }

        /* ── Main Content ── */
        .main {
            padding: 32px 40px;
            overflow-x: hidden;
            max-width: 960px;
        }

        /* ── Page Header ── */
        .page-header {
            margin-bottom: 32px;
            padding-bottom: 24px;
            border-bottom: 1px solid var(--border);
        }
        .page-title {
            font-size: 22px; font-weight: 700; letter-spacing: -0.03em;
            color: var(--text-primary); margin-bottom: 6px;
        }
        .page-subtitle {
            font-size: 13.5px; color: var(--text-secondary); font-weight: 400;
        }

        /* ── Metric Cards ── */
        .metrics {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 16px;
            margin-bottom: 32px;
        }
        .metric {
            background: var(--bg-surface);
            padding: 20px;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            transition: box-shadow 0.2s ease, transform 0.2s ease;
        }
        .metric:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-1px);
        }
        .metric-label {
            font-size: 10.5px; font-weight: 600; letter-spacing: 0.08em;
            text-transform: uppercase; color: var(--text-muted); margin-bottom: 10px;
        }
        .metric-value {
            font-family: var(--font-mono); font-size: 20px; font-weight: 600;
            color: var(--text-primary); letter-spacing: -0.02em;
        }
        .metric-value.accent { color: var(--accent); }
        .metric-value.green  { color: var(--green); }
        .metric-hint {
            font-size: 11px; color: var(--text-muted); margin-top: 6px;
            font-family: var(--font-mono);
        }

        /* ── Section ── */
        .section {
            margin-bottom: 24px;
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }
        .section-header {
            background: var(--bg-elevated);
            padding: 14px 24px;
            display: flex; align-items: center; gap: 12px;
            border-bottom: 1px solid var(--border);
        }
        .section-icon {
            width: 32px; height: 32px;
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            display: flex; align-items: center; justify-content: center;
            font-size: 14px;
            box-shadow: var(--shadow-sm);
        }
        .section-title {
            font-size: 14px; font-weight: 600; letter-spacing: -0.01em;
            color: var(--text-primary);
        }
        .section-count {
            margin-left: auto; font-family: var(--font-mono);
            font-size: 11.5px; color: var(--text-muted);
            background: var(--bg-base); border: 1px solid var(--border);
            padding: 3px 10px; border-radius: 6px;
            font-weight: 500;
        }

        /* ── Table ── */
        table { width: 100%; border-collapse: collapse; }
        tr { border-bottom: 1px solid var(--border-light); }
        tr:last-child { border-bottom: none; }
        tr:hover td, tr:hover th { background: var(--bg-hover); }
        th {
            width: 220px; padding: 12px 24px;
            font-family: var(--font-mono); font-size: 12px; font-weight: 500;
            color: var(--text-muted); letter-spacing: 0.01em;
            text-align: left; vertical-align: top;
            background: var(--bg-elevated);
            white-space: nowrap;
        }
        td {
            padding: 12px 24px;
            font-family: var(--font-mono); font-size: 12.5px;
            color: var(--text-primary); vertical-align: top;
            background: var(--bg-surface);
            word-break: break-all;
        }

        /* ── Badges ── */
        .badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 10px; border-radius: 6px;
            font-family: var(--font-mono); font-size: 11.5px; font-weight: 600;
            letter-spacing: 0.02em; border: 1px solid;
        }
        .badge-on {
            background: var(--green-dim); color: var(--green);
            border-color: rgba(26, 122, 58, 0.15);
        }
        .badge-off {
            background: var(--red-dim); color: var(--red);
            border-color: rgba(199, 70, 52, 0.15);
        }
        .badge-set {
            background: var(--blue-dim); color: var(--blue);
            border-color: rgba(11, 107, 203, 0.15);
        }
        .badge-php {
            background: var(--accent-dim); color: var(--accent);
            border-color: rgba(199, 70, 52, 0.2);
            font-size: 13.5px; padding: 5px 14px;
        }
        .long-value {
            display: block; margin-top: 8px;
            font-size: 11.5px; color: var(--text-secondary);
            line-height: 1.7; word-break: break-all;
        }

        /* ── Extensions Grid ── */
        .ext-grid {
            padding: 20px 24px;
            display: flex; flex-wrap: wrap; gap: 8px;
            background: var(--bg-surface);
        }
        .ext-tag {
            font-family: var(--font-mono); font-size: 11.5px;
            padding: 4px 10px; border-radius: 6px;
            border: 1px solid var(--border);
            color: var(--text-secondary);
            background: var(--bg-elevated);
            transition: all 0.15s; cursor: default;
            font-weight: 500;
        }
        .ext-tag:hover {
            color: var(--text-primary);
            border-color: var(--text-muted);
            box-shadow: var(--shadow-sm);
        }
        .ext-tag.zend {
            color: var(--amber); border-color: rgba(180, 83, 9, 0.2);
            background: var(--amber-dim);
        }
        .ext-row-label {
            padding: 12px 24px;
            font-size: 10.5px; font-weight: 700; letter-spacing: 0.08em;
            text-transform: uppercase; color: var(--text-muted);
            background: var(--bg-elevated);
            border-bottom: 1px solid var(--border);
        }

        /* ── Actions ── */
        .actions {
            display: flex; gap: 12px; margin-bottom: 32px;
        }
        .btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 20px; border-radius: var(--radius-sm);
            font-family: var(--font-sans); font-size: 13px; font-weight: 600;
            text-decoration: none; border: 1px solid;
            transition: all 0.2s ease; cursor: pointer;
        }
        .btn-primary {
            background: var(--accent); color: #ffffff;
            border-color: var(--accent);
            box-shadow: 0 2px 6px rgba(199, 70, 52, 0.2);
        }
        .btn-primary:hover {
            background: var(--accent-hover);
            border-color: var(--accent-hover);
            box-shadow: 0 4px 10px rgba(199, 70, 52, 0.3);
            transform: translateY(-1px);
        }
        .btn-ghost {
            background: var(--bg-surface); color: var(--text-secondary);
            border-color: var(--border);
        }
        .btn-ghost:hover {
            background: var(--bg-hover); color: var(--text-primary);
            border-color: var(--text-muted);
        }

        /* ── Footer ── */
        .footer {
            margin-top: 48px; padding: 24px 0;
            border-top: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
        }
        .footer-left {
            font-family: var(--font-mono); font-size: 11.5px; color: var(--text-muted);
        }
        .footer-right {
            font-size: 12px; color: var(--text-muted); font-weight: 500;
        }

        /* ── Scrollbar ── */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--text-muted); }

        /* ── Animations ── */
        @keyframes fadeSlideIn {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .section {
            animation: fadeSlideIn 0.35s ease both;
        }
        .section:nth-child(1) { animation-delay: 0.04s; }
        .section:nth-child(2) { animation-delay: 0.08s; }
        .section:nth-child(3) { animation-delay: 0.12s; }
        .section:nth-child(4) { animation-delay: 0.16s; }
        .section:nth-child(5) { animation-delay: 0.20s; }
        .section:nth-child(6) { animation-delay: 0.24s; }
        .section:nth-child(7) { animation-delay: 0.28s; }

        @media (max-width: 900px) {
            .layout { grid-template-columns: 1fr; }
            .sidebar { display: none; }
            .metrics { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 600px) {
            .metrics { grid-template-columns: repeat(2, 1fr); }
            .main { padding: 24px 16px; }
        }
    </style>
</head>
<body>

<!-- ── Topbar ─────────────────────────────────────────────────────────── -->
<header class="topbar">
    <div class="topbar-brand">
        <div class="topbar-logo">🥝</div>
        <span class="topbar-name">KiwiPanel</span>
        <div class="topbar-sep"></div>
        <span class="topbar-page">PHP Configuration Inspector</span>
    </div>
    <div class="topbar-right">
        <span class="topbar-time"><?= date("Y-m-d H:i:s T") ?></span>
    </div>
</header>

<!-- ── Security Alert ─────────────────────────────────────────────────── -->
<div class="alert">
    <span class="alert-icon">⚠</span>
    <span><strong>Security Notice:</strong> This file exposes server configuration. <strong>Delete ini.php immediately</strong> after inspection is complete.</span>
</div>

<!-- ── Layout ─────────────────────────────────────────────────────────── -->
<div class="layout">

    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-section-label">Navigation</div>
        <a href="#overview"   class="sidebar-link active"><span class="sidebar-icon">◈</span> Overview</a>
        <a href="#version"    class="sidebar-link"><span class="sidebar-icon">◉</span> PHP Version</a>
        <a href="#resources"  class="sidebar-link"><span class="sidebar-icon">⏱</span> Resource Limits</a>
        <a href="#upload"     class="sidebar-link"><span class="sidebar-icon">↑</span> File Upload</a>
        <a href="#errors"     class="sidebar-link"><span class="sidebar-icon">⚡</span> Error Handling</a>
        <a href="#session"    class="sidebar-link"><span class="sidebar-icon">🔒</span> Session</a>
        <a href="#settings"   class="sidebar-link"><span class="sidebar-icon">⚙</span> Other Settings</a>
        <a href="#security"   class="sidebar-link"><span class="sidebar-icon">🛡</span> Security</a>
        <div class="sidebar-divider"></div>
        <a href="#extensions" class="sidebar-link">
            <span class="sidebar-icon">◫</span> Extensions
            <span class="sidebar-badge"><?= count($extensions) ?></span>
        </a>
        <div class="sidebar-divider"></div>
        <a href="?phpinfo=1"  class="sidebar-link"><span class="sidebar-icon">↗</span> Full phpinfo()</a>
    </nav>

    <!-- Main -->
    <main class="main">

        <div class="page-header" id="overview">
            <div class="page-title">Runtime Configuration</div>
            <div class="page-subtitle">
                Active PHP settings for this virtual host &mdash; <?= $_SERVER[
                    "HTTP_HOST"
                ] ?? "localhost" ?>
            </div>
        </div>

        <!-- Metrics -->
        <div class="metrics">
            <div class="metric">
                <div class="metric-label">PHP Version</div>
                <div class="metric-value accent"><?= PHP_MAJOR_VERSION .
                    "." .
                    PHP_MINOR_VERSION .
                    "." .
                    PHP_RELEASE_VERSION ?></div>
                <div class="metric-hint"><?= php_sapi_name() ?></div>
            </div>
            <div class="metric">
                <div class="metric-label">Memory Limit</div>
                <div class="metric-value"><?= htmlspecialchars(
                    $resource_limits["memory_limit"],
                ) ?></div>
                <div class="metric-hint">memory_limit</div>
            </div>
            <div class="metric">
                <div class="metric-label">Max Upload</div>
                <div class="metric-value"><?= htmlspecialchars(
                    $file_upload["upload_max_filesize"],
                ) ?></div>
                <div class="metric-hint">upload_max_filesize</div>
            </div>
            <div class="metric">
                <div class="metric-label">Exec Timeout</div>
                <div class="metric-value"><?= htmlspecialchars(
                    $resource_limits["max_execution_time"],
                ) ?>s</div>
                <div class="metric-hint">max_execution_time</div>
            </div>
            <div class="metric">
                <div class="metric-label">OPcache</div>
                <div class="metric-value <?= $other["opcache.enable"] === "On"
                    ? "green"
                    : "" ?>">
                    <?= htmlspecialchars($other["opcache.enable"]) ?>
                </div>
                <div class="metric-hint"><?= htmlspecialchars(
                    $other["opcache.memory_consumption"],
                ) ?></div>
            </div>
        </div>

        <!-- Actions -->
        <div class="actions">
            <a href="?phpinfo=1" class="btn btn-primary">↗ View Full phpinfo()</a>
            <a href="javascript:window.print()" class="btn btn-ghost">⎙ Print Report</a>
        </div>

        <!-- PHP Version -->
        <div class="section" id="version">
            <div class="section-header">
                <div class="section-icon">◉</div>
                <span class="section-title">PHP Version</span>
            </div>
            <table>
                <tr><th>php_version</th><td><span class="badge badge-php"><?= phpversion() ?></span></td></tr>
                <tr><th>sapi_name</th><td><?= php_sapi_name() ?></td></tr>
                <tr><th>zend_version</th><td><?= zend_version() ?></td></tr>
                <tr><th>os</th><td><?= PHP_OS .
                    " &mdash; " .
                    php_uname("r") ?></td></tr>
                <tr><th>architecture</th><td><?= PHP_INT_SIZE === 8
                    ? "64-bit"
                    : "32-bit" ?></td></tr>
            </table>
        </div>

        <!-- Resource Limits -->
        <div class="section" id="resources">
            <div class="section-header">
                <div class="section-icon">⏱</div>
                <span class="section-title">Resource Limits</span>
            </div>
            <table>
                <?php foreach ($resource_limits as $key => $val): ?>
                <tr><th><?= $key ?></th><td><?= htmlspecialchars(
    $val,
) ?></td></tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- File Upload -->
        <div class="section" id="upload">
            <div class="section-header">
                <div class="section-icon">↑</div>
                <span class="section-title">File Upload</span>
            </div>
            <table>
                <?php foreach ($file_upload as $key => $val): ?>
                <tr><th><?= $key ?></th><td><?= htmlspecialchars(
    $val,
) ?></td></tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- Error Handling -->
        <div class="section" id="errors">
            <div class="section-header">
                <div class="section-icon">⚡</div>
                <span class="section-title">Error Handling</span>
            </div>
            <table>
                <?php foreach ($error_handling as $key => $val): ?>
                <tr>
                    <th><?= $key ?></th>
                    <td>
                        <?php if ($val === "On" || $val === "Off"): ?>
                            <span class="badge <?= $val === "On"
                                ? "badge-on"
                                : "badge-off" ?>"><?= $val === "On"
    ? "● On"
    : "○ Off" ?></span>
                        <?php else: ?>
                            <?= htmlspecialchars($val) ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- Session -->
        <div class="section" id="session">
            <div class="section-header">
                <div class="section-icon">🔒</div>
                <span class="section-title">Session</span>
            </div>
            <table>
                <?php foreach ($session as $key => $val): ?>
                <tr><th><?= $key ?></th><td><?= htmlspecialchars(
    $val,
) ?></td></tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- Other Settings -->
        <div class="section" id="settings">
            <div class="section-header">
                <div class="section-icon">⚙</div>
                <span class="section-title">Other Settings</span>
            </div>
            <table>
                <?php foreach ($other as $key => $val): ?>
                <tr>
                    <th><?= $key ?></th>
                    <td>
                        <?php if ($val === "On" || $val === "Off"): ?>
                            <span class="badge <?= $val === "On"
                                ? "badge-on"
                                : "badge-off" ?>"><?= $val === "On"
    ? "● On"
    : "○ Off" ?></span>
                        <?php else: ?>
                            <?= htmlspecialchars($val) ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- Security -->
        <div class="section" id="security">
            <div class="section-header">
                <div class="section-icon">🛡</div>
                <span class="section-title">Security Settings</span>
            </div>
            <table>
                <?php foreach ($security as $key => $val): ?>
                <tr>
                    <th><?= $key ?></th>
                    <td>
                        <?php if (
                            $key === "disable_functions" ||
                            $key === "open_basedir"
                        ): ?>
                            <?php if ($val !== "(not set)"): ?>
                                <span class="badge badge-set">✓ Configured</span>
                                <span class="long-value"><?= htmlspecialchars(
                                    $val,
                                ) ?></span>
                            <?php else: ?>
                                <span class="badge badge-off">○ Not set</span>
                            <?php endif; ?>
                        <?php elseif ($val === "On" || $val === "Off"): ?>
                            <span class="badge <?= $val === "On"
                                ? "badge-on"
                                : "badge-off" ?>">
                                <?= $val === "On" ? "● On" : "○ Off" ?>
                            </span>
                        <?php else: ?>
                            <?= htmlspecialchars($val) ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- Extensions -->
        <div class="section" id="extensions">
            <div class="section-header">
                <div class="section-icon">◫</div>
                <span class="section-title">Installed Extensions</span>
                <span class="section-count"><?= count(
                    $extensions,
                ) ?> loaded</span>
            </div>
            <?php if (!empty($zend_extensions)): ?>
            <div class="ext-row-label">Zend Extensions</div>
            <div class="ext-grid" style="border-bottom: 1px solid var(--border);">
                <?php foreach ($zend_extensions as $ext): ?>
                    <span class="ext-tag zend"><?= htmlspecialchars(
                        $ext,
                    ) ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <div class="ext-row-label">PHP Extensions</div>
            <div class="ext-grid">
                <?php foreach ($extensions as $ext): ?>
                    <span class="ext-tag"><?= htmlspecialchars($ext) ?></span>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-left">
                Generated <?= date("Y-m-d H:i:s T") ?> &bull; <?= $_SERVER[
     "SERVER_ADDR"
 ] ?? "" ?>
            </div>
            <div class="footer-right">KiwiPanel PHP Configuration Inspector</div>
        </div>

    </main>
</div>

<script>
// Active sidebar link on scroll
const sections = document.querySelectorAll('[id]');
const links = document.querySelectorAll('.sidebar-link[href^="#"]');
const observer = new IntersectionObserver(entries => {
    entries.forEach(e => {
        if (e.isIntersecting) {
            links.forEach(l => l.classList.remove('active'));
            const active = document.querySelector(`.sidebar-link[href="#${e.target.id}"]`);
            if (active) active.classList.add('active');
        }
    });
}, { rootMargin: '-40% 0px -55% 0px' });
sections.forEach(s => observer.observe(s));
</script>

</body>
</html>
