<?php require_once __DIR__ . '/icon.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Planter's Cash Advance & Loan System (PCALS)</title>
  <link rel="stylesheet" href="assets/css/output.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="bg-gray-100 min-h-screen">
  <nav class="bg-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <a href="dashboard.php" class="flex-shrink-0">
          <span class="text-2xl font-bold text-teal-700">PCALS</span>
          <span class="hidden sm:inline text-sm text-gray-500 ml-2">Planter's Cash Advance & Loan System</span>
        </a>

        <div class="hidden md:flex md:items-center md:space-x-4">
          <a href="dashboard.php" class="text-gray-600 hover:text-teal-600 px-3 py-2 rounded-md font-medium transition-colors duration-200">Dashboard</a>
          <a href="planters.php" class="text-gray-600 hover:text-teal-600 px-3 py-2 rounded-md font-medium transition-colors duration-200">Planters</a>
          <a href="loans.php" class="text-gray-600 hover:text-teal-600 px-3 py-2 rounded-md font-medium transition-colors duration-200">Loans</a>
          <a href="repayments.php" class="text-gray-600 hover:text-teal-600 px-3 py-2 rounded-md font-medium transition-colors duration-200">Repayments</a>
          <a href="reports.php" class="text-gray-600 hover:text-teal-600 px-3 py-2 rounded-md font-medium transition-colors duration-200">Reports</a>
        </div>

        <div class="hidden md:block">
          <a href="logout.php" class="bg-red-500 text-white font-semibold py-2 px-4 rounded-lg shadow-md hover:bg-red-600 transition-colors duration-200">
            Logout
          </a>
        </div>

        <div class="md:hidden">
          <button id="mobile-menu-button" type="button" class="text-gray-500 hover:text-gray-800 focus:outline-none focus:text-gray-800">
            <?php echo icon('menu', 'h-6 w-6'); ?>
          </button>
        </div>
      </div>
    </div>

    <div id="mobile-menu" class="md:hidden hidden">
      <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
        <a href="dashboard.php" class="block text-gray-700 hover:bg-gray-200 px-3 py-2 rounded-md text-base font-medium transition-colors duration-200">Dashboard</a>
        <a href="planters.php" class="block text-gray-700 hover:bg-gray-200 px-3 py-2 rounded-md text-base font-medium transition-colors duration-200">Planters</a>
        <a href="loans.php" class="block text-gray-700 hover:bg-gray-200 px-3 py-2 rounded-md text-base font-medium transition-colors duration-200">Loans</a>
        <a href="repayments.php" class="block text-gray-700 hover:bg-gray-200 px-3 py-2 rounded-md text-base font-medium transition-colors duration-200">Repayments</a>
        <a href="reports.php" class="block text-gray-700 hover:bg-gray-200 px-3 py-2 rounded-md text-base font-medium transition-colors duration-200">Reports</a>
        <a href="logout.php" class="block text-red-500 hover:bg-red-100 px-3 py-2 rounded-md text-base font-medium transition-colors duration-200">Logout</a>
      </div>
    </div>
  </nav>

  <main class="max-w-7xl mx-auto mt-6 px-4 sm:px-6 lg:px-8">
