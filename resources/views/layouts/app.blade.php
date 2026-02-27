<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'ระบบจัดการกิล')</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <style>
        :root {
            --bg: #f7f4ef;
            --panel: #ffffff;
            --ink: #1f2937;
            --muted: #6b7280;
            --accent: #0f766e;
            --accent-2: #ea580c;
            --border: #e5e7eb;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", system-ui, sans-serif;
            background: linear-gradient(135deg, #f7f4ef 0%, #f0f9ff 100%);
            color: var(--ink);
        }
        header {
            background: var(--panel);
            border-bottom: 1px solid var(--border);
        }
        .shell {
            display: grid;
            grid-template-columns: 240px 1fr;
            min-height: 100vh;
        }
        .sidebar {
            background: #0f172a;
            color: #e2e8f0;
            padding: 24px 18px;
        }
        .sidebar h2 {
            font-size: 18px;
            margin: 0 0 16px;
            color: #f8fafc;
        }
        .nav {
            display: grid;
            gap: 8px;
        }
        .nav a {
            color: #e2e8f0;
            text-decoration: none;
            padding: 10px 12px;
            border-radius: 10px;
            background: rgba(148, 163, 184, 0.12);
        }
        .nav a:hover {
            background: rgba(148, 163, 184, 0.24);
        }
        .content {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .container {
            max-width: 100%;
            width: 100%;
            margin: 0;
            padding: 24px;
        }
        .title {
            font-size: 28px;
            margin: 0;
        }
        .actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-block;
            padding: 10px 14px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: var(--panel);
            text-decoration: none;
            color: var(--ink);
        }
        .btn-primary {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
        }
        .btn-danger {
            background: #b91c1c;
            border-color: #b91c1c;
            color: #fff;
        }
        .card {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 18px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.06);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid var(--border);
        }
        th {
            color: var(--muted);
            font-weight: 600;
        }
        .status {
            background: #dcfce7;
            color: #166534;
            padding: 10px 14px;
            border-radius: 10px;
            margin-bottom: 16px;
        }
        .field {
            display: grid;
            gap: 6px;
            margin-bottom: 14px;
        }
        .field label {
            color: var(--muted);
            font-size: 14px;
        }
        input, select {
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid var(--border);
            font-size: 16px;
        }
        .error {
            color: #b91c1c;
            font-size: 14px;
        }
        .muted {
            color: var(--muted);
        }
        @media (max-width: 900px) {
            .shell {
                grid-template-columns: 1fr;
            }
            .sidebar {
                position: sticky;
                top: 0;
                z-index: 10;
            }
        }
        @media (max-width: 640px) {
            .actions { flex-direction: column; }
            .btn { width: 100%; text-align: center; }
            th, td { font-size: 14px; }
        }
    </style>
</head>
<body>
    <div class="shell">
        <aside class="sidebar">
            <h2>Guild Console</h2>
            <nav class="nav">
                <a href="{{ route('guild-members.index') }}">สมาชิกกิล</a>
                <a href="{{ route('party-planner.index') }}">จัดปาร์ตี้</a>
                <a href="{{ route('kvm-planner.index') }}">ปาร์ตี้ KVM</a>
                <a href="{{ route('gvg-weekly-stats.index') }}">ผลงาน GVG</a>
                <a href="{{ route('gvg-weekly-stats.summary') }}">สรุปคะแนน GVG</a>
                <a href="{{ route('job-classes.index') }}">อาชีพ</a>
            </nav>
        </aside>
        <div class="content">
            <header>
                <div class="container">
                    <p class="muted">ระบบจัดการกิล</p>
                    <h1 class="title">@yield('title', 'Guild Console')</h1>
                </div>
            </header>
            <main class="container">
            @if (session('status'))
                <div class="status">{{ session('status') }}</div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof jQuery === 'undefined' || !jQuery.fn.dataTable) {
            return;
        }
        jQuery('table.datatable').each(function () {
            if (!jQuery(this).hasClass('dataTable')) {
                const orderColAttr = jQuery(this).attr('data-order-col');
                const orderDirAttr = (jQuery(this).attr('data-order-dir') || 'asc').toLowerCase();
                const options = {
                    paging: true,
                    searching: true,
                    ordering: true,
                    pageLength: 10,
                };
                if (orderColAttr !== undefined) {
                    const orderCol = parseInt(orderColAttr, 10);
                    if (!Number.isNaN(orderCol)) {
                        options.order = [[orderCol, orderDirAttr === 'desc' ? 'desc' : 'asc']];
                    }
                }
                jQuery(this).DataTable({
                    ...options,
                });
            }
        });
    });
</script>
</body>
</html>
