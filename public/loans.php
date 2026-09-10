<?php
include "../includes/auth.php";
include "../config/db.php";

// Add loan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $planter_id = (int)$_POST['planter_id'];
    $purpose = trim($_POST['purpose']);
    $amount = number_format((float)$_POST['amount'], 2, '.', '');
    $date_issued = !empty($_POST['date_issued']) ? $_POST['date_issued'] : date('Y-m-d');

    // Insert loan with balance = amount
    $stmt = $pdo->prepare("INSERT INTO loans (planter_id, amount, date_issued, balance, purpose) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$planter_id, $amount, $date_issued, $amount, $purpose]);

    header("Location: loans.php?msg=added");
    exit;
}

// Delete loan
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM loans WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: loans.php?msg=deleted");
    exit;
}

// Fetch planters for dropdown
$planters = $pdo->query("SELECT id, name FROM planters ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Fetch loans
$sql = "SELECT l.*, p.name as planter_name 
        FROM loans l 
        JOIN planters p ON p.id = l.planter_id 
        ORDER BY l.date_issued DESC";
$loans = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

include "../includes/header.php";
?>

<main class="min-h-screen bg-gray-100 p-6 sm:p-10">
    <div class="max-w-7xl mx-auto">
        <header class="mb-8">
            <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-800 tracking-tight">Loans / Cash Advances</h1>
        </header>

        <?php if (!empty($_GET['msg'])) : ?>
            <div class="mb-6 p-4 rounded-lg bg-green-100 text-green-800 font-medium border border-green-200">
                <?php
                switch ($_GET['msg']) {
                    case 'added':
                        $message = 'Loan recorded successfully!';
                        break;
                    case 'deleted':
                        $message = 'Loan deleted successfully.';
                        break;
                    default:
                        $message = htmlspecialchars($_GET['msg']);
                }
                echo $message;
                ?>
            </div>
        <?php endif; ?>

        <!-- Record New Loan -->
        <div class="bg-white p-6 rounded-xl shadow-lg mb-8">
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">Record New Loan</h2>
            <form method="POST" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <div class="col-span-1">
                    <label for="planter_id" class="block text-sm font-medium text-gray-700 mb-1">Select Planter</label>
                    <select id="planter_id" name="planter_id" required class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                        <option value="">-- Select Planter --</option>
                        <?php foreach ($planters as $pl) : ?><option value="<?php echo (int)$pl['id']; ?>"><?php echo htmlspecialchars($pl['name']); ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-span-1">
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                    <input type="number" id="amount" step="0.01" min="0.01" name="amount" placeholder="Amount (e.g., 5000.00)" required class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                </div>
                <div class="col-span-1">
                    <label for="date_issued" class="block text-sm font-medium text-gray-700 mb-1">Date Issued</label>
                    <input type="date" id="date_issued" name="date_issued" value="<?php echo date('Y-m-d'); ?>" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                </div>
                <div class="col-span-2">
                    <label for="purpose" class="block text-sm font-medium text-gray-700 mb-1">Purpose of Loan</label>
                    <input type="text" id="purpose" name="purpose" placeholder="Purpose of Loan" required class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                </div>
                <div class="col-span-full flex justify-start space-x-4 mt-2">
                    <button type="button" onclick="generatePreview()" class="bg-gray-200 text-gray-800 font-medium py-2 px-6 rounded-md hover:bg-gray-300">Preview Consent Form</button>
                    <button type="submit" class="bg-teal-600 text-white font-medium py-2 px-6 rounded-md hover:bg-teal-700">Save Loan</button>
                </div>
            </form>
        </div>

        <!-- Loan List -->
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">Loan List</h2>
            <?php if (count($loans) === 0) : ?>
                <div class="p-6 text-center text-gray-500 bg-gray-50 rounded-lg">
                    <p>No loans recorded yet. Use the form above to add a new one.</p>
                </div>
            <?php else : ?>
                <div class="overflow-x-auto -mx-6 sm:-mx-8 lg:-mx-10">
                    <div class="inline-block min-w-full py-2 align-middle px-6 lg:px-8">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Planter</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Amount</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Balance</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">+2% Payment</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date Issued</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($loans as $l) : $withFee = $l['amount'] * 1.02; ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-4 text-sm text-gray-500"><?php echo (int)$l['id']; ?></td>
                                        <td class="px-3 py-4 font-medium text-gray-900"><?php echo htmlspecialchars($l['planter_name']); ?></td>
                                        <td class="px-3 py-4 text-sm text-gray-500">₱<?php echo number_format($l['amount'], 2); ?></td>
                                        <td class="px-3 py-4 text-sm font-semibold <?php echo ($l['balance'] > 0) ? 'text-red-500' : 'text-green-500'; ?>">₱<?php echo number_format($l['balance'], 2); ?></td>
                                        <td class="px-3 py-4 text-sm text-blue-600 font-semibold">₱<?php echo number_format($withFee, 2); ?></td>
                                        <td class="px-3 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($l['date_issued']); ?></td>
                                        <td class="px-3 py-4 text-sm font-medium">
                                            <a href="repayments.php?loan_id=<?php echo (int)$l['id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-4">View Repayments</a>
                                            <button type="button" data-id="<?php echo (int)$l['id']; ?>" data-name="<?php echo htmlspecialchars($l['planter_name']); ?> - ₱<?php echo number_format($l['amount'], 2); ?>" onclick="openDeleteModal(this.dataset.id, this.dataset.name)" class="text-red-600 hover:text-red-900">Delete</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Delete Modal -->
<div id="deleteModal" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(0,0,0,0.6); align-items:center; justify-content:center;">
    <div style="background:#ffffff; border-radius:12px; padding:24px; width:90%; max-width:420px; box-shadow:0 10px 30px rgba(0,0,0,0.3);">
        <h3 style="font-size:18px; font-weight:800; color:#1f2937; margin-bottom:8px;">Delete Loan?</h3>
        <p style="font-size:14px; color:#4b5563; margin-bottom:4px;">You are about to delete <span id="deleteName" style="font-weight:700; color:#111827;"></span>.</p>
        <p style="font-size:12px; color:#ef4444; margin-bottom:20px;">This will also remove all associated repayments. This cannot be undone.</p>
        <div style="display:flex; justify-content:flex-end; gap:12px;">
            <button onclick="closeDeleteModal()" type="button" style="padding:10px 20px; background:#f3f4f6; color:#374151; border-radius:8px; font-weight:600; border:none; cursor:pointer;">Cancel</button>
            <a id="confirmDeleteBtn" href="#" style="padding:10px 20px; background:#dc2626; color:#ffffff !important; border-radius:8px; font-weight:600; text-decoration:none; display:inline-block;">Yes, Delete</a>
        </div>
    </div>
</div>

<script>
    function openDeleteModal(id, name) {
        document.getElementById('deleteName').innerText = name;
        document.getElementById('confirmDeleteBtn').href = 'loans.php?delete=' + id;
        document.getElementById('deleteModal').style.display = 'flex';
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
    }
    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) closeDeleteModal();
    });
</script>

<?php include "../includes/footer.php"; ?>