<?php
declare(strict_types=1);
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

// Field fix: consent form posts "recipient" (not "name").
$name    = trim((string)($_POST['recipient'] ?? $_POST['name'] ?? ''));
$date    = trim((string)($_POST['date'] ?? ''));
$purpose = trim((string)($_POST['purpose'] ?? ''));
$amount  = (string)($_POST['amount'] ?? '');

if (!is_dir(__DIR__ . '/files')) {
    mkdir(__DIR__ . '/files', 0777, true);
}

$phpWord = new PhpWord();
$section = $phpWord->addSection();
$section->addText("KABILOG COMPANY", ["bold" => true, "size" => 16], ["alignment" => "center"]);
$section->addText("LOAN CONSENT FORM", ["bold" => true, "size" => 14], ["alignment" => "center"]);
$section->addTextBreak(1);

$section->addText("I, $name, hereby voluntarily agree to the terms and conditions of borrowing funds from Kabilog Company.");
$section->addText("Date of Agreement: $date");
$section->addText("Purpose of Loan: $purpose");
$section->addText("Amount Borrowed: \u{20B1}$amount");
$section->addTextBreak(1);

$section->addText("I fully understand my obligations as a borrower and agree to repay the loan according to the agreed schedule and conditions.");
$section->addText("I acknowledge that I have been informed of all responsibilities related to this loan.");
$section->addText("By signing this document, I affirm my acceptance and consent.");
$section->addTextBreak(2);

$section->addText("_____________________________", [], ["alignment" => "left"]);
$section->addText("Signature of $name");
$section->addTextBreak(2);
$section->addText("_____________________________", [], ["alignment" => "left"]);
$section->addText("Authorized Representative, Kabilog Company");

// Save DOCX (paths are relative to generate.php's own directory).
$docxFile = __DIR__ . "/files/consent.docx";
$writer = IOFactory::createWriter($phpWord, 'Word2007');
$writer->save($docxFile);

// Convert DOCX -> PDF (using LibreOffice) if available.
$pdfFile = __DIR__ . "/files/consent.pdf";
$libreoffice = (PHP_OS_FAMILY === 'Windows') ? 'soffice' : 'libreoffice';
exec(escapeshellarg($libreoffice) . " --headless --convert-to pdf --outdir " . escapeshellarg(__DIR__ . "/files") . " " . escapeshellarg($docxFile) . " 2>&1");

header('Content-Type: application/json');
echo json_encode([
    "docx" => "files/consent.docx",
    "pdf"  => "files/consent.pdf"
]);
