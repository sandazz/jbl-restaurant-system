<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - Restaurant BYOB</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f1f5f9 0%, #e9eef5 100%);
        }

        /* Sidebar */
        .sidebar {
            background: #fff;
            box-shadow: 2px 0 12px rgba(0,0,0,0.06);
            border-right: 1px solid #f1f5f9;
        }
        .active-nav {
            background: linear-gradient(135deg, #fef2f2, #fee2e2);
            color: #991b1b;
            border-left: 3px solid #dc2626;
            font-weight: 700;
        }
        .main-content { margin-left: 256px; }
        @media (max-width: 1024px) { .main-content { margin-left: 0; } }
    </style>
    @yield('styles')
</head>
<body>
    <!-- Navbar -->
    @include('layouts.navbar')

    <div class="flex" style="padding-top:67px;">
        <!-- Sidebar -->
        <x-sidebar :modules="$modules ?? []" />

        <!-- Main Content -->
        <div class="flex-1 main-content px-6 py-8 w-full lg:w-auto">
            @yield('content')
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteConfirmModal" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(15,23,42,0.45); backdrop-filter:blur(3px); align-items:center; justify-content:center;">
        <div style="background:#fff; border-radius:16px; padding:32px 28px; max-width:380px; width:90%; box-shadow:0 24px 64px rgba(0,0,0,0.18); text-align:center;">
            <div style="width:60px; height:60px; background:#fef2f2; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 18px;">
                <i class="fas fa-trash" style="color:#dc2626; font-size:22px;"></i>
            </div>
            <h3 style="font-size:17px; font-weight:700; color:#1e293b; margin:0 0 8px;">Delete Confirmation</h3>
            <p id="deleteConfirmMessage" style="font-size:14px; color:#64748b; margin:0 0 24px; line-height:1.5;">Are you sure you want to delete this item?</p>
            <div style="display:flex; gap:10px; justify-content:center;">
                <button onclick="closeDeleteConfirm()" style="padding:9px 22px; border:1.5px solid #e2e8f0; border-radius:8px; font-size:13px; font-weight:600; color:#64748b; background:#fff; cursor:pointer; transition:background .15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                    <i class="fas fa-times mr-1"></i>Cancel
                </button>
                <button id="deleteConfirmBtn" style="padding:9px 22px; background:#dc2626; border:none; border-radius:8px; font-size:13px; font-weight:600; color:#fff; cursor:pointer; transition:background .15s;" onmouseover="this.style.background='#b91c1c'" onmouseout="this.style.background='#dc2626'">
                    <i class="fas fa-trash mr-1"></i>Delete
                </button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var _deleteCallback = null;

            window.showDeleteConfirm = function (formOrMessage, callback) {
                var msg;
                if (typeof formOrMessage === 'string') {
                    msg = formOrMessage;
                    _deleteCallback = callback || null;
                } else {
                    msg = 'Are you sure you want to delete this item? This action cannot be undone.';
                    var form = formOrMessage;
                    _deleteCallback = function () { form.submit(); };
                }
                document.getElementById('deleteConfirmMessage').textContent = msg;
                document.getElementById('deleteConfirmModal').style.display = 'flex';
            };

            window.closeDeleteConfirm = function () {
                document.getElementById('deleteConfirmModal').style.display = 'none';
                _deleteCallback = null;
            };

            document.addEventListener('DOMContentLoaded', function () {
                document.getElementById('deleteConfirmBtn').addEventListener('click', function () {
                    var cb = _deleteCallback;
                    closeDeleteConfirm();
                    if (cb) cb();
                });
                document.getElementById('deleteConfirmModal').addEventListener('click', function (e) {
                    if (e.target === this) closeDeleteConfirm();
                });
            });
        })();
    </script>

    <script>
        document.addEventListener('focusin', function (event) {
            const target = event.target;
            if (!(target instanceof HTMLInputElement)) return;
            if (target.type !== 'number' && !target.dataset.clearZero) return;
            if (target.dataset.clearedZero === 'true') return;
            const value = (target.value || '').trim();
            if (value === '') return;
            if (!Number.isFinite(Number(value))) return;
            if (Number(value) !== 0) return;
            target.value = '';
            target.dataset.clearedZero = 'true';
        });
    </script>
    @yield('scripts')
</body>
</html>
