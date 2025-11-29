<?php
include('inc/database.php');

// Fetch all patients from Supabase
try {
    $patients = getAllPatients('created_at', 'desc');
} catch (Exception $e) {
    $patients = [];
    error_log("Error fetching patients: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vetcare - Ensuring Care and Support for Your Beloved Pet's</title>
    
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
                    <img src="assets/images/logo.svg" alt="">
                    <span>Vetcare</span>
                </a>
                
                <ul class="nav-links">
                    <li class="active"><a href="index.php">Home</a></li>
                    <li><a href="#services">Our Service</a></li>
                    <li><a href="#about">About Us</a></li>
                </ul>
                
                <a href="add_patient.php" class="contact-btn">Contact Us</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <span class="badge">#1 Petcare in Quezon City</span>
            
            <h1 class="hero-title">Ensuring Care and Support for Your Beloved Pet's</h1>
            
            <p class="hero-description">
                Our veterinary clinic provides comprehensive and compassionate care for your beloved pets
            </p>
            
            <div class="cta-buttons">
                <a href="add_patient.php" class="btn-primary-custom">Add a Patient</a>
                <a href="view_patient.php" class="btn-secondary-custom"><span><img src="assets/images/logo.svg" alt="logo" style="width: 20px; height: 20px; margin-right: 5px;"></span>View all Patients</a>
            </div>
        </div>
        
        <div class="fluid-container image-grid">
            
            <div class="image-placeholder">
                <img src="assets/images/swag.jpg" alt="">
            </div>
            <div class="image-placeholder">
                <img src="assets/images/smiley.jpg" alt="">
            </div>
            <div class="image-placeholder">
                <img src="assets/images/watermelo.jpg" alt="">
            </div>
            <div class="image-placeholder">
                <img src="assets/images/drivingcorgi.jpg" alt="">
            </div>
            
            <!-- Wave decoration -->
            <div class="wave-decoration">
                <svg viewBox="0 0 300 60" xmlns="http://www.w3.org/2000/svg">
                    <path d="M 0 30 Q 30 10, 60 30 T 120 30 T 180 30 T 240 30 T 300 30" 
                          stroke="#ff9500" 
                          stroke-width="3" 
                          fill="none"
                          stroke-linecap="round"/>
                </svg>
            </div>
        </div>
        
        <!-- Paw print decoration -->
        <div class="paw-print paw-bottom-left">🐾</div>
    </section>

    <!-- Patient List Section -->
    <section>
        <div class="container">
            <h2>Patient List</h2>
            <?php if (count($patients) > 0): ?>
                 <?php foreach ($patients as $patient): ?>
                    <div class="patient-card">
                        <div class="patient-image">
                            <?php 
                            if (!empty($patient['image'])) {
                                // Check if it's a Supabase URL or local file
                                if (strpos($patient['image'], 'http') === 0) {
                                    echo '<img src="' . htmlspecialchars($patient['image']) . '" alt="Patient Image">';
                                } elseif (file_exists('uploads/' . $patient['image'])) {
                                    echo '<img src="uploads/' . htmlspecialchars($patient['image']) . '" alt="Patient Image">';
                                } else {
                                    echo '<img src="assets/images/default-pet.png" alt="Default Image">';
                                }
                            } else {
                                echo '<img src="assets/images/default-pet.png" alt="Default Image">';
                            }
                            ?>
                        </div>
                        <div class="patient-info">
                            <h3><?php echo htmlspecialchars($patient['pet_name']); ?></h3>
                            <p><strong>Species:</strong> <?php echo htmlspecialchars($patient['species']); ?></p>
                            <p><strong>Breed:</strong> <?php echo htmlspecialchars($patient['breed']); ?></p>
                            <p><strong>Age:</strong> <?php echo htmlspecialchars($patient['age']); ?> years old</p>
                            <p><strong>Gender:</strong> <?php echo htmlspecialchars($patient['gender']); ?></p>
                            <p><strong>Owner:</strong> <?php echo htmlspecialchars($patient['owner_name']); ?></p>
                            <p><strong>Contact:</strong> <?php echo htmlspecialchars($patient['contact_number']); ?></p>
                        </div>
                        <div class="patient-actions">
                            <a href="view_patient.php?id=<?php echo $patient['id']; ?>" class="btn btn-info"><span class="glyphicon glyphicon-eye-open"></span>View</a>
                            <a href="edit_patient.php?id=<?php echo $patient['id']; ?>" class="btn btn-warning"><span class="glyphicon glyphicon-pencil"></span>Edit</a>
                            <a href="delete_patient.php?id=<?php echo $patient['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this patient?');"><span class="glyphicon glyphicon-trash"></span>Delete</a>
                        </div>
                    </div>
                 <?php endforeach; ?>
                 <?php endif; ?>

            <?php if (count($patients) === 0): ?>
                <p class="no-patients text-warning">No patients found. <a href="add_patient.php">Add a new patient</a>.</p>
            <?php endif; ?>
    </section>

</body>
</html>