<?php
include('inc/database.php');

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$patient_id = $_GET['id'];

try {
    // Fetch patient data to get image
    $patient = getPatientById($patient_id);
    
    if ($patient) {
        // Delete the patient record from Supabase
        deletePatient($patient_id);
        
        // Delete the image file if it exists
        if (!empty($patient['image'])) {
            if (strpos($patient['image'], 'http') === 0) {
                // Delete from Supabase Storage
                $filename = basename($patient['image']);
                deleteFromSupabase('pet-images', $filename);
            } elseif (file_exists('uploads/' . $patient['image'])) {
                // Delete local file
                unlink('uploads/' . $patient['image']);
            }
        }
        
        // Redirect with success message
        header("Location: index.php?deleted=1");
        exit();
    } else {
        // Patient not found
        header("Location: index.php?error=not_found");
        exit();
    }
} catch (Exception $e) {
    // Redirect with error message
    header("Location: index.php?error=delete_failed");
    exit();
}
?>