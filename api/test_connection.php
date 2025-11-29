<?php
/**
 * Test Supabase Connection
 * This file helps debug connection issues
 */

require_once 'inc/supabase.php';

echo "<h1>Supabase Connection Test</h1>";
echo "<pre>";

// Test 1: Check environment variables
echo "=== Configuration Check ===\n";
echo "SUPABASE_URL: " . (defined('SUPABASE_URL') ? SUPABASE_URL : 'NOT SET') . "\n";
echo "SUPABASE_KEY: " . (defined('SUPABASE_KEY') ? (substr(SUPABASE_KEY, 0, 20) . '...') : 'NOT SET') . "\n\n";

// Test 2: Try to fetch patients
echo "=== Testing Database Connection ===\n";
try {
    $patients = getAllPatients();
    echo "✅ SUCCESS! Connected to Supabase\n";
    echo "Number of patients found: " . count($patients) . "\n";
    
    if (count($patients) > 0) {
        echo "\nFirst patient:\n";
        print_r($patients[0]);
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nPossible issues:\n";
    echo "1. The 'patients' table doesn't exist in Supabase\n";
    echo "2. RLS policies are blocking access\n";
    echo "3. API key is incorrect\n";
    echo "4. Network/firewall issues\n";
}

// Test 3: Check if table exists
echo "\n=== Testing Table Access ===\n";
try {
    $url = SUPABASE_URL . '/rest/v1/patients?limit=1';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . SUPABASE_KEY,
        'Authorization: Bearer ' . SUPABASE_KEY
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Status Code: $httpCode\n";
    echo "Response: $response\n";
    
    if ($httpCode == 200) {
        echo "✅ Table exists and is accessible\n";
    } elseif ($httpCode == 404) {
        echo "❌ Table 'patients' not found. Run the SQL script!\n";
    } elseif ($httpCode == 401 || $httpCode == 403) {
        echo "❌ Authentication failed or RLS policy blocking access\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>
