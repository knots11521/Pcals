<?php
include "../includes/auth.php";
include "../includes/header.php";
?>

<main class="min-h-screen bg-gray-100 p-6 sm:p-10">
    <div class="max-w-7xl mx-auto">
        <header class="mb-8">
            <h1 class="text-4xl sm:text-5xl font-extrabold text-gray-800 tracking-tight">
                Dashboard
            </h1>
            <p class="mt-2 text-lg text-gray-600">
                Welcome back, <span class="font-semibold text-gray-900"><?php echo htmlspecialchars($_SESSION['admin']); ?></span>!
            </p>
        </header>

        <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <a href="planters.php" class="block">
                <div class="bg-white shadow-lg rounded-xl p-6 transform transition-transform duration-300 hover:scale-105 hover:shadow-2xl">
                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl font-bold text-gray-900">Planters</h2>
                        <span class="text-teal-500" style="font-size: 2rem;"><?php echo icon('users', 'h-8 w-8'); ?></span>
                    </div>
                    <p class="mt-2 text-gray-500">Manage all registered planters.</p>
                </div>
            </a>

            <a href="loans.php" class="block">
                <div class="bg-white shadow-lg rounded-xl p-6 transform transition-transform duration-300 hover:scale-105 hover:shadow-2xl">
                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl font-bold text-gray-900">Loans</h2>
                        <span class="text-indigo-500" style="font-size: 2rem;"><?php echo icon('currency-dollar', 'h-8 w-8'); ?></span>
                    </div>
                    <p class="mt-2 text-gray-500">View and manage all loan records.</p>
                </div>
            </a>

            <a href="repayments.php" class="block">
                <div class="bg-white shadow-lg rounded-xl p-6 transform transition-transform duration-300 hover:scale-105 hover:shadow-2xl">
                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl font-bold text-gray-900">Repayments</h2>
                        <span class="text-green-500" style="font-size: 2rem;"><?php echo icon('check-circle', 'h-8 w-8'); ?></span>
                    </div>
                    <p class="mt-2 text-gray-500">Track and log all repayments.</p>
                </div>
            </a>
        </section>

        </div>
    </main>

<?php include "../includes/footer.php"; ?>
