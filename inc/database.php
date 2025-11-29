<?php
/**
 * Database Connection - Now using Supabase
 * The old MySQL/PDO connection has been replaced with Supabase
 */

// Include Supabase helper functions
require_once __DIR__ . '/supabase.php';

// For backward compatibility with existing code that uses $pdo
// We'll keep the variable but it won't be used
$pdo = null;

// Note: All database operations now use Supabase helper functions:
// - getAllPatients() - Get all patients
// - getPatientById($id) - Get a single patient
// - insertPatient($data) - Insert new patient
// - updatePatient($id, $data) - Update patient
// - deletePatient($id) - Delete patient
