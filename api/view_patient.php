<?php
include('inc/database.php');

// Get patient ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$patient_id = $_GET['id'];

// Fetch patient data from Supabase
try {
    $patient = getPatientById($patient_id);
    if (!$patient) {
        header("Location: index.php");
        exit();
    }
} catch (Exception $e) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Details - Vetcare</title>
    
    <link rel="icon" type="image/svg+xml" href="assets/images/logo.svg"/>
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>
    <!-- Navigation -->
    <nav class="custom-navbar">
        <div class="container-fluid">
            <div class="navbar-content">
                <a href="index.php" class="brand">
                    <img src="assets/images/logo.svg" alt="Vetcare Logo">
                    <span>Vetcare</span>
                </a>
                
                <ul class="nav-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="#services">Our Service</a></li>
                    <li><a href="#about">About Us</a></li>
                </ul>
                
                <a href="index.php" class="contact-btn">Back to Home</a>
            </div>
        </div>
    </nav>

    <!-- Patient Details Section -->
    <div class="detail-wrapper">
        <div class="detail-header">
            <div class="detail-header-content">
                <h1>Patient Details</h1>
                <p>View complete information about this patient</p>
            </div>
            <div class="detail-header-actions">
                <a href="edit_patient.php?id=<?php echo $patient['id']; ?>" class="btn btn-warning-custom">
                    <span class="glyphicon glyphicon-pencil"></span>
                    Edit
                </a>
                <a href="index.php" class="btn btn-secondary">
                    <span class="glyphicon glyphicon-arrow-left"></span>
                    Back
                </a>
            </div>
        </div>

        <div class="detail-container">
            <div class="detail-grid">
                <!-- Left Column - Image and Quick Info -->
                <div class="detail-sidebar">
                    <div class="detail-image-card">
                        <?php 
                        if (!empty($patient['image'])) {
                            if (strpos($patient['image'], 'http') === 0) {
                                // Supabase URL
                                echo '<img src="' . htmlspecialchars($patient['image']) . '" class="detail-patient-image" alt="' . htmlspecialchars($patient['pet_name']) . '">';
                            } elseif (file_exists('uploads/' . $patient['image'])) {
                                // Local file
                                echo '<img src="uploads/' . htmlspecialchars($patient['image']) . '" class="detail-patient-image" alt="' . htmlspecialchars($patient['pet_name']) . '">';
                            } else {
                                echo '<div class="detail-no-image"><i class="bi bi-image"></i><p>No image available</p></div>';
                            }
                        } else {
                            echo '<div class="detail-no-image"><i class="bi bi-image"></i><p>No image available</p></div>';
                        }
                        ?>
                    </div>
                    
                    <div class="detail-quick-info">
                        <div class="quick-info-item">
                            <i class="bi bi-hash"></i>
                            <div>
                                <div class="quick-info-label">Patient ID</div>
                                <div class="quick-info-value">#<?php echo htmlspecialchars($patient['id']); ?></div>
                            </div>
                        </div>
                        <div class="quick-info-item">
                            <i class="bi bi-calendar-check"></i>
                            <div>
                                <div class="quick-info-label">Registered</div>
                                <div class="quick-info-value"><?php echo date('F d, Y', strtotime($patient['created_at'])); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Details -->
                <div class="detail-content">
                    <!-- Pet Information Section -->
                    <div class="info-section">
                        <div class="section-header">
                            <i class="bi bi-heart-fill"></i>
                            <h2>Pet Information</h2>
                        </div>
                        
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">Pet Name</div>
                                <div class="info-value"><?php echo htmlspecialchars($patient['pet_name']); ?></div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-label">Age</div>
                                <div class="info-value"><?php echo htmlspecialchars($patient['age']); ?></div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-label">Gender</div>
                                <div class="info-value">
                                    <span class="badge-custom badge-<?php echo ($patient['gender'] == 'Male') ? 'blue' : 'pink'; ?>">
                                        <?php echo htmlspecialchars($patient['gender']); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-label">Species</div>
                                <div class="info-value"><?php echo htmlspecialchars($patient['species']); ?></div>
                            </div>
                            
                            <div class="info-item full-width">
                                <div class="info-label">Breed</div>
                                <div class="info-value"><?php echo htmlspecialchars($patient['breed']); ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Owner Information Section -->
                    <div class="info-section">
                        <div class="section-header">
                            <i class="bi bi-person-fill"></i>
                            <h2>Owner Information</h2>
                        </div>
                        
                        <div class="info-grid">
                            <div class="info-item full-width">
                                <div class="info-label">Owner Name</div>
                                <div class="info-value">
                                    <?php echo !empty($patient['owner_name']) ? htmlspecialchars($patient['owner_name']) : '<span class="text-muted">Not provided</span>'; ?>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-label">Contact Number</div>
                                <div class="info-value">
                                    <?php if (!empty($patient['contact_number'])): ?>
                                        <i class="bi bi-telephone"></i>
                                        <?php echo htmlspecialchars($patient['contact_number']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">Not provided</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-label">Address</div>
                                <div class="info-value">
                                    <?php if (!empty($patient['address'])): ?>
                                        <i class="bi bi-geo-alt"></i>
                                        <?php echo htmlspecialchars($patient['address']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">Not provided</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="detail-actions">
                        <a href="edit_patient.php?id=<?php echo $patient['id']; ?>" class="btn btn-warning-custom btn-lg">
                            <span class="glyphicon glyphicon-pencil"></span>
                            Edit Patient
                        </a>
                        <a href="delete_patient.php?id=<?php echo $patient['id']; ?>" 
                           class="btn btn-danger-custom btn-lg"
                           onclick="return confirm('Are you sure you want to delete this patient record? This action cannot be undone.');">
                           <span class="glyphicon glyphicon-trash"></span>
                           Delete Patient
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 3 JS and jQuery -->
    <script src="assets/js/jquery.js"></script>
    <script src="assets/js/bootstrap.js"></script>
</body>
</html>