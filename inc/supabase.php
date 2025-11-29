<?php
/**
 * Supabase Configuration and Helper Functions
 * This file provides a simple interface to interact with Supabase
 */

// Load environment variables
function loadEnv($path) {
    if (!file_exists($path)) {
        return false;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }
    return true;
}

loadEnv(__DIR__ . '/../.env');

// Supabase configuration
define('SUPABASE_URL', getenv('SUPABASE_URL') ?: 'your_supabase_url_here');
define('SUPABASE_KEY', getenv('SUPABASE_KEY') ?: 'your_supabase_key_here');

/**
 * Make a request to Supabase REST API
 */
function supabaseRequest($endpoint, $method = 'GET', $data = null, $query = []) {
    $url = SUPABASE_URL . '/rest/v1/' . $endpoint;
    
    if (!empty($query)) {
        $url .= '?' . http_build_query($query);
    }
    
    $headers = [
        'apikey: ' . SUPABASE_KEY,
        'Authorization: Bearer ' . SUPABASE_KEY,
        'Content-Type: application/json',
        'Prefer: return=representation'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    // SSL certificate fix for Windows/Laragon
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    switch ($method) {
        case 'POST':
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            break;
        case 'PATCH':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            break;
        case 'DELETE':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            break;
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        throw new Exception("cURL Error: " . $curlError);
    }
    
    if ($httpCode >= 200 && $httpCode < 300) {
        return json_decode($response, true);
    } else {
        $errorDetails = json_decode($response, true);
        $errorMessage = isset($errorDetails['message']) ? $errorDetails['message'] : $response;
        throw new Exception("Supabase request failed (HTTP $httpCode): " . $errorMessage);
    }
}

/**
 * Get all patients
 */
function getAllPatients($orderBy = 'created_at', $order = 'desc') {
    return supabaseRequest('patients', 'GET', null, [
        'order' => $orderBy . '.' . $order
    ]);
}

/**
 * Get a single patient by ID
 */
function getPatientById($id) {
    $result = supabaseRequest('patients', 'GET', null, [
        'id' => 'eq.' . $id,
        'select' => '*'
    ]);
    return !empty($result) ? $result[0] : null;
}

/**
 * Insert a new patient
 */
function insertPatient($data) {
    return supabaseRequest('patients', 'POST', $data);
}

/**
 * Update a patient
 */
function updatePatient($id, $data) {
    return supabaseRequest('patients?id=eq.' . $id, 'PATCH', $data);
}

/**
 * Delete a patient
 */
function deletePatient($id) {
    return supabaseRequest('patients?id=eq.' . $id, 'DELETE');
}

/**
 * Upload file to Supabase Storage
 */
function uploadToSupabase($bucket, $filePath, $fileContent) {
    $url = SUPABASE_URL . '/storage/v1/object/' . $bucket . '/' . $filePath;
    
    $headers = [
        'apikey: ' . SUPABASE_KEY,
        'Authorization: Bearer ' . SUPABASE_KEY,
        'Content-Type: application/octet-stream'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fileContent);
    
    // SSL certificate fix for Windows/Laragon
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        return json_decode($response, true);
    } else {
        throw new Exception("File upload failed: " . $response);
    }
}

/**
 * Delete file from Supabase Storage
 */
function deleteFromSupabase($bucket, $filePath) {
    $url = SUPABASE_URL . '/storage/v1/object/' . $bucket . '/' . $filePath;
    
    $headers = [
        'apikey: ' . SUPABASE_KEY,
        'Authorization: Bearer ' . SUPABASE_KEY
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    
    // SSL certificate fix for Windows/Laragon
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ($httpCode >= 200 && $httpCode < 300);
}

/**
 * Get public URL for uploaded file
 */
function getPublicUrl($bucket, $filePath) {
    return SUPABASE_URL . '/storage/v1/object/public/' . $bucket . '/' . $filePath;
}
