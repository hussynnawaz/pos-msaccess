<?php
require_once __DIR__ . '/../../bootstrap.php';

$session = new SessionManager();
$session->start();

if (!$session->isLoggedIn()) {
    $session->set('logged_in', true);
    $session->set('user_name', 'Admin');
    $session->set('user_role', 'admin');
}

$_SESSION['user_name'] = $session->get('user_name', 'Admin');
$_SESSION['user_role'] = $session->get('user_role', 'admin');

$page = $_GET['page'] ?? 'pos';
$validPages = ['dashboard', 'pos', 'orders', 'products', 'suppliers', 'inventory', 'reports', 'backup', 'profile'];
if (!in_array($page, $validPages)) $page = 'pos';

$pageFile = __DIR__ . '/pages/' . $page . '.php';
if (!file_exists($pageFile)) $pageFile = __DIR__ . '/pages/pos.php';

// HTMX request: render page fragment only
if (isset($_SERVER['HTTP_HX_REQUEST']) && $_SERVER['HTTP_HX_REQUEST'] === 'true') {
    require $pageFile;
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - POS System</title>
    <link rel="stylesheet" href="/public/assets/css/tailwind.css">
    <script src="/public/assets/js/htmx.min.js"></script>
    <style>
        body { font-family: 'Segoe UI', system-ui, sans-serif; }
        .sidebar-link { transition: all 0.15s ease; }
        .sidebar-link.active { background: #eff6ff; color: #2563eb; font-weight: 600; }
        .sidebar-link:hover:not(.active) { background: #f1f5f9; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex">
    <?php require __DIR__ . '/partials/sidebar.php'; ?>
    <div class="flex-1 ml-72">
        <div id="admin-content">
            <?php require $pageFile; ?>
        </div>
    </div>
    <script>
    function getCurrentPage() {
        var params = new URLSearchParams(window.location.search);
        var page = params.get('page');
        if (!page) {
            var parts = window.location.pathname.replace(/\/$/, '').split('/');
            page = parts[parts.length - 1];
        }
        return page || 'pos';
    }

    function updateBackupInfo() {
        var info = document.getElementById('backupInfo');
        if (!info) return;
        var content = document.querySelector('input[name="content"]:checked');
        var format = document.querySelector('input[name="format"]:checked');
        if (!content || !format) return;
        var labels = { structure: 'structure (tables only)', data: 'data (rows only)', both: 'structure & data' };
        var fmtLabels = { sql: 'MySQL SQL', tsql: 'SQL Server T-SQL', accessdb: 'Access .accdb file copy' };
        document.getElementById('backupInfoText').innerHTML = 'Exporting <strong>' + labels[content.value] + '</strong> as <strong>' + fmtLabels[format.value] + '</strong>.';
        info.classList.remove('hidden');
    }

    function downloadBackup() {
        var content = document.querySelector('input[name="content"]:checked').value;
        var format = document.querySelector('input[name="format"]:checked').value;
        var btn = document.getElementById('backupBtn');

        btn.disabled = true;
        btn.innerHTML = '<svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg> Generating...';

        var url = '/api/backup.php?content=' + encodeURIComponent(content) + '&format=' + encodeURIComponent(format);

        fetch(url, { credentials: 'same-origin' })
            .then(function(res) {
                if (!res.ok) return res.json().then(function(d) { throw new Error(d.message || 'Backup failed'); });
                return res.blob();
            })
            .then(function(blob) {
                if (!blob) return;
                var a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                if (format === 'accessdb') {
                    a.download = 'pos_backup_' + new Date().toISOString().slice(0,19).replace(/[T:]/g,'-') + '.accdb';
                } else {
                    a.download = 'pos_backup_' + new Date().toISOString().slice(0,19).replace(/[T:]/g,'-') + '.sql';
                }
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(a.href);
            })
            .catch(function(err) {
                alert('Error: ' + err.message);
            })
            .finally(function() {
                btn.disabled = false;
                btn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg> Download Backup';
            });
    }

    document.body.addEventListener('htmx:afterSwap', function(e) {
        if (e.detail.target.id === 'admin-content') {
            setActive(getCurrentPage());
            if (getCurrentPage() === 'backup') {
                document.querySelectorAll('input[name="content"], input[name="format"]').forEach(function(r) {
                    r.addEventListener('change', updateBackupInfo);
                });
                updateBackupInfo();
            }
        }
    });

    if (getCurrentPage() === 'backup') {
        document.querySelectorAll('input[name="content"], input[name="format"]').forEach(function(r) {
            r.addEventListener('change', updateBackupInfo);
        });
        updateBackupInfo();
    }
    </script>
</body>
</html>
