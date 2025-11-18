<?php
/**
 * CSV to PHP Array Converter for Barangay 498 Population Data
 * This script reads the CSV file and generates a PHP array
 */

// Read the CSV file
$csvFile = 'P0PULATION BRGY 498(SISA).csv';
$csvFile = 'P0PULATION BRGY 498(BASILIO).csv';
$csvFile = 'P0PULATION BRGY 498(INSTRUCCION).csv';
$csvFile = 'P0PULATION BRGY 498(SIMOUN).csv';
$csvFile = 'P0PULATION BRGY 498(MA CLARA).csv';
$csvFile = 'P0PULATION BRGY 498(MACEDA).csv';




if (!file_exists($csvFile)) {
    die("CSV file not found! Please ensure 'P0PULATION BRGY 498(SISA).csv' is in the same directory.\n");
}

$handle = fopen($csvFile, 'r');
$residents = [];
$currentAddress = '';
$lineNumber = 0;

echo "Reading CSV file...\n\n";

while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
    $lineNumber++;
    
    // Skip header rows and empty rows
    if ($lineNumber <= 2 || empty($data[2]) || trim($data[2]) == '') {
        continue;
    }
    
    // Update address if present
    if (!empty($data[1]) && trim($data[1]) != '') {
        $currentAddress = trim($data[1]);
    }
    
    // Extract data
    $fullName = trim($data[2]);
    $dob = trim($data[3]);
    $pob = trim($data[4]);
    $sex = trim($data[5]);
    $civilStatus = trim($data[6]);
    $occupation = trim($data[7]);
    $citizenship = trim($data[8]);
    $relation = trim($data[9]);
    
    // Skip if essential fields are missing
    if (empty($fullName) || empty($dob)) {
        continue;
    }
    
    // Extract first name (everything before the first space)
    $nameParts = explode(' ', $fullName);
    $firstName = strtoupper($nameParts[0]);
    
    // Convert date format from M/D/YYYY to YYYY-MM-DD
    $dobFormatted = '';
    if (preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})/', $dob, $matches)) {
        $dobFormatted = sprintf('%04d-%02d-%02d', $matches[3], $matches[1], $matches[2]);
    } elseif (preg_match('/(\d{1,2})-(\d{1,2})-(\d{4})/', $dob, $matches)) {
        $dobFormatted = sprintf('%04d-%02d-%02d', $matches[3], $matches[2], $matches[1]);
    } else {
        $dobFormatted = '1900-01-01'; // Default for invalid dates
    }
    
    // Add to residents array
    $residents[] = [
        'first_name' => $firstName,
        'full_name' => $fullName,
        'address' => $currentAddress,
        'dob' => $dobFormatted,
        'pob' => $pob,
        'sex' => $sex,
        'civil_status' => $civilStatus,
        'occupation' => $occupation,
        'citizenship' => $citizenship,
        'relation' => $relation
    ];
}

fclose($handle);

echo "Found " . count($residents) . " residents.\n\n";

// Generate PHP array code
echo "========== PHP ARRAY CODE ==========\n";
echo "Copy this into your password generator script:\n\n";

echo "\$residents = [\n";
foreach ($residents as $resident) {
    echo "    ['" . addslashes($resident['first_name']) . "', ";
    echo "'" . addslashes($resident['full_name']) . "', ";
    echo "'" . addslashes($resident['address']) . "', ";
    echo "'" . $resident['dob'] . "', ";
    echo "'" . addslashes($resident['pob']) . "', ";
    echo "'" . $resident['sex'] . "', ";
    echo "'" . addslashes($resident['civil_status']) . "', ";
    echo "'" . addslashes($resident['occupation']) . "', ";
    echo "'" . addslashes($resident['citizenship']) . "', ";
    echo "'" . addslashes($resident['relation']) . "'],\n";
}
echo "];\n";

echo "\n========== SUMMARY ==========\n";
echo "Total residents processed: " . count($residents) . "\n";
echo "Ready to use in password generator!\n";
?>