<?php
include "../includes/auth.php";
include "../config/db.php";

$error = null;
$success = null;

// If loan_id passed in GET, pre-select it
$selected_loan_id = isset($_GET['loan_id']) ? (int)$_GET['loan_id'] : null;

// Add repayment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loan_id = (int)$_POST['loan_id'];
    $amount = number_format((float)$_POST['amount'], 2, '.', '');
    $date_paid = !empty($_POST['date_paid']) ? $_POST['date_paid'] : date('Y-m-d');

    // Fetch loan
    $stmt = $pdo->prepare("SELECT * FROM loans WHERE id = ?");
    $stmt->execute([$loan_id]);
    $loan = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$loan) {
        $error = "Loan not found.";
    } elseif ($amount <= 0) {
        $error = "Enter a valid amount.";
    } elseif ($amount > $loan['balance']) {
        $error = "Amount exceeds loan balance (" . number_format($loan['balance'], 2) . ").";
    } else {
        try {
            $pdo->beginTransaction();

            // Save repayment
            $ins = $pdo->prepare("INSERT INTO repayments (loan_id, amount, date_paid) VALUES (?, ?, ?)");
            $ins->execute([$loan_id, $amount, $date_paid]);

            // Update loan balance
            $upd = $pdo->prepare("UPDATE loans SET balance = balance - ? WHERE id = ?");
            $upd->execute([$amount, $loan_id]);

            $pdo->commit();
            $success = "Repayment of ₱" . number_format($amount, 2) . " recorded successfully.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Failed to record repayment: " . $e->getMessage();
        }
    }
}

// Fetch loans (only loans with balance > 0)
$loans = $pdo->query("SELECT l.id, l.amount, l.balance, l.date_issued, p.name as planter_name
                        FROM loans l JOIN planters p ON p.id = l.planter_id
                        WHERE l.balance > 0
                        ORDER BY p.name, l.date_issued")->fetchAll(PDO::FETCH_ASSOC);

// Fetch repayments list (most recent first)
$repayments = $pdo->query("SELECT r.*, l.planter_id, l.date_issued, p.name as planter_name
                              FROM repayments r
                              JOIN loans l ON l.id = r.loan_id
                              JOIN planters p ON p.id = l.planter_id
                              ORDER BY r.date_paid DESC")->fetchAll(PDO::FETCH_ASSOC);

include "../includes/header.php";
?>

<main class="min-h-screen bg-gray-100 p-6 sm:p-10">
    <div class="max-w-7xl mx-auto">
        <header class="mb-8">
            <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-800 tracking-tight">
                Loan Repayments
            </h1>
            <p class="text-gray-500 mt-2 text-lg">Manage and record all payments for outstanding loans.</p>
        </header>

        <?php if ($success) : ?>
            <div class="mb-6 p-4 rounded-lg bg-green-100 text-green-800 font-medium border border-green-200" role="alert">
                <p><?php echo htmlspecialchars($success); ?></p>
            </div>
        <?php endif; ?>

        <?php if ($error) : ?>
            <div class="mb-6 p-4 rounded-lg bg-red-100 text-red-800 font-medium border border-red-200" role="alert">
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-xl shadow-lg mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Record a New Repayment</h2>
            <form method="POST">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div class="col-span-full">
                        <label for="loan_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Step 1: Select Loan <?php echo icon('currency-dollar', 'h-4 w-4 inline-block'); ?>
                        </label>
                        <select id="loan_id" name="loan_id" required
                            class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 transition-all duration-200">
                            <option value="">-- Choose a Loan --</option>
                            <?php foreach ($loans as $ln) : ?>
                                <?php $withInterest = $ln['balance'] * 1.02; ?>
                                <option value="<?php echo (int)$ln['id']; ?>"
                                    data-balance="<?php echo $ln['balance']; ?>"
                                    data-interest="<?php echo $withInterest; ?>"
                                    <?php echo ($selected_loan_id && $selected_loan_id == $ln['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($ln['planter_name']) . " — Loan ID #" . (int)$ln['id'] . " (Balance: ₱" . number_format($ln['balance'], 2) . ")"; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div id="loanInfo" class="hidden bg-gray-50 p-4 rounded-lg shadow-inner mt-4 mb-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Current Balance</p>
                            <p id="loanBalance" class="text-xl font-bold text-gray-900">₱0.00</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Suggested Payment (with 2% interest)</p>
                            <p id="loanInterest" class="text-xl font-bold text-teal-600">₱0.00</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div class="col-span-1">
                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">
                            Step 2: Enter Amount <?php echo icon('currency-dollar', 'h-4 w-4 inline-block'); ?>
                        </label>
                        <input type="number" id="amount" step="0.01" min="0.01" name="amount" placeholder="e.g., 500.00" required
                            class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 transition-all duration-200">
                    </div>

                    <div class="col-span-1">
                        <label for="date_paid" class="block text-sm font-medium text-gray-700 mb-1">
                            Step 3: Choose Date <?php echo icon('calendar', 'h-4 w-4 inline-block'); ?>
                        </label>
                        <input type="date" id="date_paid" name="date_paid" value="<?php echo date('Y-m-d'); ?>"
                            class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 transition-all duration-200">
                    </div>

                    <div class="col-span-full mt-2 flex justify-start">
                        <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition-colors duration-200 flex items-center">
                            <?php echo icon('plus', 'h-5 w-5 mr-2'); ?>
                            Record Repayment
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Repayment History</h2>
            <?php if (count($repayments) === 0) : ?>
                <div class="p-6 text-center text-gray-500 bg-gray-50 rounded-lg">
                    <p>No repayments have been recorded yet. Begin by using the form above. <?php echo icon('hand', 'h-5 w-5 inline-block'); ?></p>
                </div>
            <?php else : ?>
                <div class="overflow-x-auto -mx-6 sm:-mx-8 lg:-mx-10">
                    <div class="inline-block min-w-full py-2 align-middle px-6 lg:px-8">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Planter Name</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Loan ID</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Amount Paid</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date Paid</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($repayments as $r) : ?>
                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                        <td class="px-3 py-4 text-sm text-gray-500"><?php echo (int)$r['id']; ?></td>
                                        <td class="px-3 py-4 font-medium text-gray-900"><?php echo htmlspecialchars($r['planter_name']); ?></td>
                                        <td class="px-3 py-4 text-sm text-gray-500"><?php echo (int)$r['loan_id']; ?></td>
                                        <td class="px-3 py-4 font-semibold text-green-600">₱<?php echo number_format($r['amount'], 2); ?></td>
                                        <td class="px-3 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($r['date_paid']); ?></td>
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

<script>
document.addEventListener("DOMContentLoaded", function () {
    const loanSelect = document.getElementById("loan_id");
    const loanInfo = document.getElementById("loanInfo");
    const loanBalance = document.getElementById("loanBalance");
    const loanInterest = document.getElementById("loanInterest");
    const amountInput = document.getElementById("amount");

    const updateUI = () => {
        const selectedOption = loanSelect.options[loanSelect.selectedIndex];
        const balance = selectedOption.getAttribute("data-balance");
        const interest = selectedOption.getAttribute("data-interest");

        if (balance && interest) {
            loanBalance.textContent = "₱" + parseFloat(balance).toLocaleString(undefined, { minimumFractionDigits: 2 });
            loanInterest.textContent = "₱" + parseFloat(interest).toLocaleString(undefined, { minimumFractionDigits: 2 });
            amountInput.value = parseFloat(interest).toFixed(2);
            loanInfo.classList.remove("hidden");
        } else {
            loanInfo.classList.add("hidden");
            amountInput.value = "";
        }
    };

    loanSelect.addEventListener("change", updateUI);

    // Trigger if already selected on page load
    if (loanSelect.value) {
        updateUI();
    }
});
</script>

<?php include "../includes/footer.php"; ?>