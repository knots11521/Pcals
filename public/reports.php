<?php
include "../includes/auth.php";
include "../config/db.php";

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    try {
        $stmt = $pdo->query("SELECT p.id, p.name FROM planters p ORDER BY p.name");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($rows)) {
            header('HTTP/1.1 204 No Content');
            exit('NO_DATA');
        }
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=planter_report.csv');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Planter ID', 'Name', 'Total Loaned', 'Total Repaid', 'Outstanding']);
        foreach ($rows as $r) {
            $pid = (int)$r['id'];
            $totalLoan = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM loans WHERE planter_id = $pid")->fetchColumn();
            $totalRepaid = (float)$pdo->query("SELECT COALESCE(SUM(r.amount),0) FROM repayments r JOIN loans l ON l.id = r.loan_id WHERE l.planter_id = $pid")->fetchColumn();
            $outstanding = (float)$pdo->query("SELECT COALESCE(SUM(balance),0) FROM loans WHERE planter_id = $pid")->fetchColumn();
            fputcsv($out, [$pid, $r['name'], number_format($totalLoan, 2, '.', ''), number_format($totalRepaid, 2, '.', ''), number_format($outstanding, 2, '.', '')]);
        }
        fclose($out);
        exit;
    } catch (Exception $e) {
        header('HTTP/1.1 500 Internal Server Error');
        exit('DB_ERROR:' . $e->getMessage());
    }
}

$totalLoaned = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM loans")->fetchColumn();
$totalRepaid = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM repayments")->fetchColumn();
$totalOutstanding = $pdo->query("SELECT COALESCE(SUM(balance),0) FROM loans")->fetchColumn();
$planterSummary = $pdo->query(" SELECT p.id, p.name, COALESCE(SUM(l.amount),0) AS total_loan, COALESCE(SUM(l.balance),0) AS outstanding FROM planters p LEFT JOIN loans l ON l.planter_id = p.id GROUP BY p.id ORDER BY p.name ")->fetchAll(PDO::FETCH_ASSOC);
foreach ($planterSummary as &$ps) {
    $pid = (int)$ps['id'];
    $ps['total_repaid'] = $pdo->query("SELECT COALESCE(SUM(r.amount),0) FROM repayments r JOIN loans l ON l.id = r.loan_id WHERE l.planter_id = $pid")->fetchColumn();
}
unset($ps);
include "../includes/header.php";
?>
<main class="min-h-screen bg-gray-100 p-6 sm:p-10">
    <div class="max-w-7xl mx-auto">
        <header class="mb-8">
            <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-800 tracking-tight">Financial Reports</h1>
        </header>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-lg flex flex-col items-start">
                <div class="flex items-center space-x-2 text-teal-600 mb-2"><?php echo icon('currency-dollar', 'h-6 w-6'); ?><h3 class="text-lg font-semibold text-gray-700">Total Loaned</h3>
                </div>
                <p class="text-3xl sm:text-4xl font-bold text-gray-900">₱<?php echo number_format($totalLoaned, 2); ?></p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-lg flex flex-col items-start">
                <div class="flex items-center space-x-2 text-green-600 mb-2"><?php echo icon('receipt-refund', 'h-6 w-6'); ?><h3 class="text-lg font-semibold text-gray-700">Total Repaid</h3>
                </div>
                <p class="text-3xl sm:text-4xl font-bold text-gray-900">₱<?php echo number_format($totalRepaid, 2); ?></p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-lg flex flex-col items-start">
                <div class="flex items-center space-x-2 text-red-600 mb-2"><?php echo icon('exclamation-circle', 'h-6 w-6'); ?><h3 class="text-lg font-semibold text-gray-700">Total Outstanding</h3>
                </div>
                <p class="text-3xl sm:text-4xl font-bold text-gray-900">₱<?php echo number_format($totalOutstanding, 2); ?></p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-semibold text-gray-800">Per-Planter Summary</h2><button id="exportBtn" type="button" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-4 rounded-lg flex items-center transition-colors"><?php echo icon('download', 'h-5 w-5 mr-2'); ?> Export to CSV</button>
            </div>
            <?php if (count($planterSummary) === 0): ?><div class="p-6 text-center text-gray-500 bg-gray-50 rounded-lg">
                    <p>No planters found.</p>
                </div><?php else: ?><div class="overflow-x-auto -mx-6">
                    <div class="inline-block min-w-full py-2 align-middle px-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Planter</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Total Loaned</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Total Repaid</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Outstanding</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200"><?php foreach ($planterSummary as $ps): ?><tr class="hover:bg-gray-50">
                                        <td class="px-3 py-4 font-medium text-gray-900"><?php echo htmlspecialchars($ps['name']); ?></td>
                                        <td class="px-3 py-4 text-sm text-gray-500">₱<?php echo number_format($ps['total_loan'], 2); ?></td>
                                        <td class="px-3 py-4 text-sm text-gray-500">₱<?php echo number_format($ps['total_repaid'], 2); ?></td>
                                        <td class="px-3 py-4 font-semibold text-red-500">₱<?php echo number_format($ps['outstanding'], 2); ?></td>
                                    </tr><?php endforeach; ?></tbody>
                        </table>
                    </div>
                </div><?php endif; ?>
        </div>
    </div>
</main>

<div id="toast" style="display:none; position:fixed; bottom:20px; right:20px; z-index:99999; background:white; padding:16px; border-radius:12px; box-shadow:0 8px 30px rgba(0,0,0,0.2); min-width:340px; border:1px solid #e5e7eb;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div style="display:flex; align-items:center; gap:10px;">
            <div id="toastIcon" style="width:32px; height:32px; background:#0d9488; border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-size:14px;">↓</div>
            <div>
                <div id="toastTitle" style="font-weight:700; font-size:13px; color:#111827;">Preparing Export...</div>
                <div style="font-size:11px; color:#6b7280;">planter_report.csv</div>
            </div>
        </div>
        <span id="toastPercent" style="font-weight:800; font-size:13px; color:#0d9488;">0%</span>
    </div>
    <div style="width:100%; height:6px; background:#f3f4f6; border-radius:10px; overflow:hidden;">
        <div id="toastBar" style="height:100%; width:0%; background:#0d9488; transition:width 0.2s;"></div>
    </div>
    <div id="toastStatus" style="font-size:11px; color:#6b7280; margin-top:6px;">Building report, please wait...</div>
</div>

<script>
    document.getElementById('exportBtn').addEventListener('click', async function() {
        const toast = document.getElementById('toast');
        const bar = document.getElementById('toastBar');
        const pct = document.getElementById('toastPercent');
        const title = document.getElementById('toastTitle');
        const status = document.getElementById('toastStatus');
        const icon = document.getElementById('toastIcon');
        const btn = this;

        function showError(msg) {
            title.innerText = 'Export Failed';
            status.innerText = msg;
            icon.innerText = '!';
            icon.style.background = '#ef4444';
            bar.style.background = '#ef4444';
            pct.innerText = '!';
            pct.style.color = '#ef4444';
            bar.style.width = '100%';
            setTimeout(() => {
                toast.style.display = 'none';
                btn.disabled = false;
            }, 3500);
        }

        toast.style.display = 'block';
        btn.disabled = true;
        bar.style.width = '15%';
        pct.innerText = '...';
        title.innerText = 'Preparing Export...';
        status.innerText = 'Building report, please wait...';
        icon.innerText = '↓';
        icon.style.background = '#0d9488';
        pct.style.color = '#0d9488';
        bar.style.background = '#0d9488';

        try {
            const res = await fetch('reports.php?export=csv');
            if (res.status === 204) {
                showError('No records found to export');
                return;
            }
            if (!res.ok) {
                showError('Unable to generate report');
                return;
            }

            bar.style.width = '40%';
            status.innerText = 'Generating CSV...';

            const blob = await res.blob();
            const text = await blob.text();
            if (text.startsWith('NO_DATA') || text.trim().length < 20) {
                showError('No records found to export');
                return;
            }
            if (text.startsWith('DB_ERROR')) {
                showError('Database unavailable');
                return;
            }

            bar.style.width = '75%';
            pct.innerText = '75%';
            status.innerText = 'File generated — waiting for save dialog...';

            const finalBlob = new Blob([text], {
                type: 'text/csv'
            });
            const url = URL.createObjectURL(finalBlob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'planter_report.csv';
            document.body.appendChild(a);
            a.click();
            a.remove();
            URL.revokeObjectURL(url);

            bar.style.width = '100%';
            pct.innerText = '100%';
            title.innerText = 'File Ready!';
            status.innerText = 'Choose where to save your file';
            icon.innerText = '✓';
            icon.style.background = '#10b981';
            bar.style.background = '#10b981';
            pct.style.color = '#10b981';

            setTimeout(() => {
                toast.style.display = 'none';
                btn.disabled = false;
                bar.style.width = '0%';
            }, 4000);

        } catch (e) {
            showError('Local server stopped — start XAMPP');
        }
    });
</script>

<?php include "../includes/footer.php"; ?>