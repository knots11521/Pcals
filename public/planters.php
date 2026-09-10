<?php
include "../includes/auth.php";
include "../config/db.php";

// Add / Update planter
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $contact = trim($_POST['contact']);
    $address = trim($_POST['address']);

    if (!empty($_POST['id'])) { // Update
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("UPDATE planters SET name = ?, contact = ?, address = ? WHERE id = ?");
        $stmt->execute([$name, $contact, $address, $id]);
        header("Location: planters.php?msg=updated");
        exit;
    } else { // Insert
        $stmt = $pdo->prepare("INSERT INTO planters (name, contact, address) VALUES (?, ?, ?)");
        $stmt->execute([$name, $contact, $address]);
        header("Location: planters.php?msg=added");
        exit;
    }
}

// Delete planter (and cascade loans & repayments because of FK)
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM planters WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: planters.php?msg=deleted");
    exit;
}

// For editing: fetch planter
$editing = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM planters WHERE id = ?");
    $stmt->execute([$id]);
    $editing = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch all planters
$planters = $pdo->query("SELECT * FROM planters ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

include "../includes/header.php";
?>

<main class="min-h-screen bg-gray-100 p-6 sm:p-10">
    <div class="max-w-7xl mx-auto">
        <header class="mb-8 flex justify-between items-center">
            <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-800 tracking-tight">Planters</h1>
            <?php if ($editing) : ?>
                <a href="planters.php" class="text-sm font-semibold text-gray-600 hover:text-gray-900 transition-colors duration-200">
                    <?php echo icon('arrow-left', 'h-4 w-4 mr-1 inline-block'); ?> Go back
                </a>
            <?php endif; ?>
        </header>

        <?php if (!empty($_GET['msg'])) : ?>
            <div class="mb-6 p-4 rounded-lg bg-green-100 text-green-800 font-medium border border-green-200">
                <?php
                $message = '';
                switch ($_GET['msg']) {
                    case 'added':
                        $message = 'Planter added successfully!';
                        break;
                    case 'updated':
                        $message = 'Planter updated successfully!';
                        break;
                    case 'deleted':
                        $message = 'Planter deleted successfully!';
                        break;
                    default:
                        $message = htmlspecialchars($_GET['msg']);
                }
                echo $message;
                ?>
            </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-xl shadow-lg mb-8">
            <h2 class="text-2xl font-semibold text-gray-700 mb-4"><?php echo $editing ? "Edit Planter" : "Add New Planter"; ?></h2>
            <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <?php if ($editing) : ?><input type="hidden" name="id" value="<?php echo (int)$editing['id']; ?>"><?php endif; ?>
                <div class="col-span-1">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" id="name" name="name" placeholder="Full name" required value="<?php echo $editing ? htmlspecialchars($editing['name']) : ''; ?>" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 transition-all duration-200">
                </div>
                <div class="col-span-1">
                    <label for="contact" class="block text-sm font-medium text-gray-700 mb-1">Contact</label>
                    <input type="tel"
                        id="contact"
                        name="contact"
                        inputmode="numeric"
                        pattern="^09[0-9]{9}$"
                        maxlength="11"
                        placeholder="09XX XXX XXXX"
                        value="<?php echo $editing ? htmlspecialchars($editing['contact']) : ''; ?>"
                        class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 transition-all duration-200">
                </div>
                <div class="col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <input type="text" id="address" name="address" placeholder="Address" value="<?php echo $editing ? htmlspecialchars($editing['address']) : ''; ?>" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 transition-all duration-200">
                </div>
                <div class="col-span-2 flex items-center justify-end">
                    <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition-colors duration-200"><?php echo $editing ? "Update Planter" : "Add Planter"; ?></button>
                    <?php if ($editing) : ?><a href="planters.php" class="ml-4 text-gray-600 hover:text-gray-900 font-medium py-3 px-6 transition-colors duration-200">Cancel</a><?php endif; ?>
                </div>
            </form>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">Planter List</h2>
            <?php if (count($planters) === 0) : ?>
                <div class="p-6 text-center text-gray-500 bg-gray-50 rounded-lg">
                    <p>No planters found. Add your first planter using the form above. <?php echo icon('users', 'h-5 w-5 inline-block'); ?></p>
                </div>
            <?php else : ?>
                <div class="overflow-x-auto -mx-6 sm:-mx-8 lg:-mx-10">
                    <div class="inline-block min-w-full py-2 align-middle px-6 lg:px-8">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">#</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Contact</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Address</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($planters as $p) : ?>
                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                        <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo (int)$p['id']; ?></td>
                                        <td class="px-3 py-4 whitespace-nowrap font-medium text-gray-900"><?php echo htmlspecialchars($p['name']); ?></td>
                                        <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($p['contact']); ?></td>
                                        <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($p['address']); ?></td>
                                        <td class="px-3 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="planters.php?edit=<?php echo (int)$p['id']; ?>" class="text-indigo-600 hover:text-indigo-900 transition-colors duration-200 mr-4">Edit</a>
                                            <button type="button" data-id="<?php echo (int)$p['id']; ?>" data-name="<?php echo htmlspecialchars($p['name']); ?>" onclick="openDeleteModal(this.dataset.id, this.dataset.name)" class="text-red-600 hover:text-red-900 transition-colors duration-200">Delete</button>
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

<!-- Delete Modal - FIXED with inline styles -->
<div id="deleteModal" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(0,0,0,0.6); align-items:center; justify-content:center;">
    <div style="background:#ffffff; border-radius:12px; padding:24px; width:90%; max-width:420px; box-shadow:0 10px 30px rgba(0,0,0,0.3);">
        <h3 style="font-size:18px; font-weight:800; color:#1f2937; margin-bottom:8px;">Delete Planter?</h3>
        <p style="font-size:14px; color:#4b5563; margin-bottom:4px;">You are about to delete <span id="deleteName" style="font-weight:700; color:#111827;"></span>.</p>
        <p style="font-size:12px; color:#ef4444; margin-bottom:20px;">This will also remove related loans and repayments. This cannot be undone.</p>
        <div style="display:flex; justify-content:flex-end; gap:12px;">
            <button onclick="closeDeleteModal()" type="button" style="padding:10px 20px; background:#f3f4f6; color:#374151; border-radius:8px; font-weight:600; border:none; cursor:pointer;">Cancel</button>
            <a id="confirmDeleteBtn" href="#" style="padding:10px 20px; background:#dc2626; color:#ffffff !important; border-radius:8px; font-weight:600; text-decoration:none; display:inline-block;">Yes, Delete</a>
        </div>
    </div>
</div>

<script>
    function openDeleteModal(id, name) {
        document.getElementById('deleteName').innerText = name;
        document.getElementById('confirmDeleteBtn').href = 'planters.php?delete=' + id;
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