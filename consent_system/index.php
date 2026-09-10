<?php require_once '../includes/icon.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kabilog Loan Consent</title>
    <link rel="stylesheet" href="../public/assets/css/output.css">
    <link rel="stylesheet" href="../public/assets/css/style.css">
    <style>
        /* Styles for Print Media */
        @media print {
            body * {
                visibility: hidden;
            }

            #previewContent,
            #previewContent * {
                visibility: visible;
            }

            #previewModal {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                height: auto;
                background: none;
                display: block !important;
            }

            .modal-content {
                box-shadow: none;
                padding: 0;
                margin: 0;
                width: 100%;
            }

            .modal-footer,
            .modal-header {
                display: none;
            }
        }
    </style>
</head>

<body class="bg-gray-100 font-sans antialiased">

    <div class="flex items-center justify-center p-6">
        <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-2xl">
            <h1 class="text-3xl font-bold text-gray-800 mb-2 text-center">Loan Consent Form</h1>
            <p class="text-gray-500 mb-6 text-center">Kabilog Company</p>

            <form method="POST" action="generate.php" target="_blank" class="space-y-6">
                <div>
                    <label for="recipient" class="block text-sm font-medium text-gray-700">Borrower Name</label>
                    <input type="text" id="recipient" name="recipient" required
                        class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm p-2 text-base">
                </div>

                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                    <input type="date" id="date" name="date" required
                        class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm p-2 text-base">
                </div>

                <div>
                    <label for="purpose" class="block text-sm font-medium text-gray-700">Purpose of Loan</label>
                    <input type="text" id="purpose" name="purpose" required
                        class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm p-2 text-base">
                </div>

                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700">Amount Borrowed (₱)</label>
                    <input type="number" step="0.01" id="amount" name="amount" required
                        class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm p-2 text-base">
                </div>

                <div class="flex justify-center space-x-4 pt-4">
                    <button type="button" onclick="generatePreview()"
                        class="bg-gray-200 text-gray-800 font-medium py-2 px-6 rounded-md hover:bg-gray-300 transition-colors duration-200">
                        Preview
                    </button>
                    <button type="submit"
                        class="bg-teal-600 text-white font-medium py-2 px-6 rounded-md hover:bg-teal-700 transition-colors duration-200">
                        Download Word
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="previewModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
        <div class="bg-white rounded-lg shadow-2xl w-full max-w-3xl overflow-hidden transform scale-100 transition-transform duration-300">
            <div class="bg-teal-600 text-white p-4 flex justify-between items-center">
                <h3 class="text-xl font-semibold">Loan Consent Form Preview</h3>
                <button onclick="closeModal()" class="text-white hover:text-gray-200 transition-colors duration-200">
                    <?php echo icon('x', 'h-6 w-6'); ?>
                </button>
            </div>

            <div class="p-8 max-h-[80vh] overflow-y-auto">
                <div id="previewContent">
                    <h3 class="text-2xl font-bold text-center mb-1">KABILOG COMPANY</h3>
                    <h4 class="text-xl font-semibold text-center text-gray-700 mb-6">LOAN CONSENT FORM</h4>

                    <p class="text-gray-600 mb-4">I, <b id="previewName" class="text-gray-900"></b>, hereby voluntarily agree to the terms and conditions of borrowing funds from Kabilog Company.</p>

                    <ul class="space-y-2 mb-6 text-gray-600">
                        <li><span class="font-medium">Date of Agreement:</span> <b id="previewDate" class="text-gray-900"></b></li>
                        <li><span class="font-medium">Purpose of Loan:</span> <b id="previewPurpose" class="text-gray-900"></b></li>
                        <li><span class="font-medium">Amount Borrowed:</span> <b id="previewAmount" class="text-gray-900"></b></li>
                    </ul>

                    <p class="text-gray-600 mb-4">I fully understand my obligations as a borrower and agree to repay the loan according to the agreed schedule and conditions. I acknowledge that I have been informed of all responsibilities related to this loan.</p>

                    <p class="text-gray-600 mb-6">By signing this document, I affirm my acceptance and consent.</p>

                    <div class="mt-12 text-center">
                        <div class="border-b-2 border-gray-400 w-3/4 mx-auto mb-2"></div>
                        <span class="block text-gray-500">Signature of <span id="previewName2"></span></span>
                    </div>

                    <div class="mt-12 text-center">
                        <div class="border-b-2 border-gray-400 w-3/4 mx-auto mb-2"></div>
                        <span class="block text-gray-500">Authorized Representative, Kabilog Company</span>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 p-4 flex justify-end space-x-3">
                <button onclick="printPreview()" class="bg-teal-600 text-white font-medium py-2 px-4 rounded-md hover:bg-teal-700 transition-colors duration-200">
                    Print
                </button>
                <button onclick="closeModal()" class="bg-white text-gray-700 border border-gray-300 font-medium py-2 px-4 rounded-md hover:bg-gray-100 transition-colors duration-200">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script>
        function generatePreview() {
            const recipient = document.getElementById("recipient").value;
            const date = document.getElementById("date").value;
            const purpose = document.getElementById("purpose").value;
            const amount = document.getElementById("amount").value;

            if (!recipient || !date || !purpose || !amount) {
                alert("Please fill all fields first!");
                return;
            }

            // Fill modal placeholders
            document.getElementById("previewName").innerText = recipient;
            document.getElementById("previewName2").innerText = recipient;
            document.getElementById("previewDate").innerText = date;
            document.getElementById("previewPurpose").innerText = purpose;
            document.getElementById("previewAmount").innerText = "₱" + parseFloat(amount).toFixed(2);

            // Show modal
            document.getElementById("previewModal").classList.remove("hidden");
        }

        function closeModal() {
            document.getElementById("previewModal").classList.add("hidden");
        }

        function printPreview() {
            const printContent = document.getElementById("previewContent").innerHTML;

            const printWindow = window.open('', '', 'height=800,width=600');
            printWindow.document.write(`
        <html>
        <head>
            <title>Print Loan Consent</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                h3 { font-size: 24px; font-weight: bold; text-align: center; margin-bottom: 4px; }
                h4 { font-size: 18px; font-weight: 600; text-align: center; margin-bottom: 24px; color: #4b5563; }
                p, li { font-size: 14px; line-height: 1.5; color: #4b5563; }
                b { font-weight: 700; color: #1f2937; }
                ul { list-style: none; padding: 0; }
                .border-b-2 { border-bottom: 2px solid #9ca3af; }
                .w-3-4 { width: 75%; }
                .mx-auto { margin-left: auto; margin-right: auto; }
                .mt-12 { margin-top: 3rem; }
                .text-center { text-align: center; }
                .text-gray-500 { color: #6b7280; }
            </style>
        </head>
        <body>
            ${printContent}
        </body>
        </html>
    `);
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        }
    </script>
</body>

</html>