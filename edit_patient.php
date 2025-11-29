<?php
include('inc/database.php');

$errors = [];
$success = '';

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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate required fields
    $pet_name = trim($_POST['pet_name']);
    $age = trim($_POST['age']);
    $gender = trim($_POST['gender']);
    $species = trim($_POST['species']);
    $breed = trim($_POST['breed']);
    $owner_name = trim($_POST['owner_name']);
    $contact_number = trim($_POST['contact_number']);
    $address = trim($_POST['address']);
    
    // Validation
    if (empty($pet_name)) {
        $errors[] = "Pet name is required.";
    }
    if (empty($age)) {
        $errors[] = "Age is required.";
    }
    if (empty($gender)) {
        $errors[] = "Gender is required.";
    }
    if (empty($species)) {
        $errors[] = "Species is required.";
    }
    if (empty($breed)) {
        $errors[] = "Breed is required.";
    }
    
    // Handle image upload
    $image_url = $patient['image']; 
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_name = $_FILES['image']['name'];
        $file_size = $_FILES['image']['size'];
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Validate file extension
        if (!in_array($file_ext, $allowed_extensions)) {
            $errors[] = "Only JPG, JPEG, PNG & GIF files are allowed.";
        }
        
        // Validate file size (10MB max)
        if ($file_size > 10485760) {
            $errors[] = "Image size must be less than 10MB.";
        }
        
        // Upload new image
        if (empty($errors)) {
            $unique_name = uniqid('pet_', true) . '.' . $file_ext;
            $file_content = file_get_contents($file_tmp);
            
            try {
                // Upload to Supabase Storage
                uploadToSupabase('pet-images', $unique_name, $file_content);
                
                // Delete old image if it exists and is a Supabase URL
                if (!empty($patient['image']) && strpos($patient['image'], 'http') === 0) {
                    $old_filename = basename($patient['image']);
                    deleteFromSupabase('pet-images', $old_filename);
                } elseif (!empty($patient['image']) && file_exists('uploads/' . $patient['image'])) {
                    unlink('uploads/' . $patient['image']);
                }
                
                $image_url = getPublicUrl('pet-images', $unique_name);
            } catch (Exception $e) {
                // Fallback to local storage
                if (!file_exists('uploads')) {
                    mkdir('uploads', 0777, true);
                }
                $upload_path = 'uploads/' . $unique_name;
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    // Delete old local image
                    if (!empty($patient['image']) && strpos($patient['image'], 'http') !== 0 && file_exists('uploads/' . $patient['image'])) {
                        unlink('uploads/' . $patient['image']);
                    }
                    $image_url = $unique_name;
                } else {
                    $errors[] = "Failed to upload image.";
                }
            }
        }
    }
    
    // Update database if no errors
    if (empty($errors)) {
        try {
            $patientData = [
                'pet_name' => $pet_name,
                'age' => $age,
                'gender' => $gender,
                'species' => $species,
                'breed' => $breed,
                'owner_name' => $owner_name,
                'contact_number' => $contact_number,
                'address' => $address,
                'image' => $image_url
            ];
            
            updatePatient($patient_id, $patientData);
            
            header("Location: index.php");
            exit();
            
        } catch (Exception $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Patient - Vetcare</title>
    
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

    <div class="container" style="margin-top: 30px; margin-bottom: 30px;">
        <div class="form-wrapper">
            <div class="form-header">
                <h1>Edit Patient Record</h1>
                <p>Update the patient information below</p>
            </div>

            <div class="form-container">
                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <strong>Oops! There were some errors:</strong>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="pet_name">Pet Name <span class="required">*</span></label>
                            <input type="text" class="form-control" id="pet_name" name="pet_name" 
                                placeholder="Enter pet's name"
                                value="<?php echo htmlspecialchars($patient['pet_name']); ?>" 
                                required>
                        </div>
                        
                        <div class="form-group">
                            <label for="age">Age <span class="required">*</span></label>
                            <input type="text" class="form-control" id="age" name="age" 
                                placeholder="e.g., 2 years, 6 months"
                                value="<?php echo htmlspecialchars($patient['age']); ?>" 
                                required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="gender">Gender <span class="required">*</span></label>
                            <select class="form-control" id="gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male" <?php echo ($patient['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo ($patient['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="species">Species <span class="required">*</span></label>
                            <select class="form-control" id="species" name="species" required>
                                <option value="">Select Species</option>
                                <option value="Dog" <?php echo ($patient['species'] == 'Dog') ? 'selected' : ''; ?>>Dog</option>
                                <option value="Cat" <?php echo ($patient['species'] == 'Cat') ? 'selected' : ''; ?>>Cat</option>
                                <option value="Bird" <?php echo ($patient['species'] == 'Bird') ? 'selected' : ''; ?>>Bird</option>
                                <option value="Rabbit" <?php echo ($patient['species'] == 'Rabbit') ? 'selected' : ''; ?>>Rabbit</option>
                                <option value="Other" <?php echo ($patient['species'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="breed">Breed <span class="required">*</span></label>
                        <input type="text" class="form-control" id="breed" name="breed" 
                            placeholder="Enter breed"
                            value="<?php echo htmlspecialchars($patient['breed']); ?>" 
                            required>
                    </div>

                    <div class="form-group">
                        <label for="owner_name">Owner Name</label>
                        <input type="text" class="form-control" id="owner_name" name="owner_name" 
                            placeholder="Enter owner's full name"
                            value="<?php echo htmlspecialchars($patient['owner_name']); ?>">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="contact_number">Contact Number</label>
                            <input type="text" class="form-control" id="contact_number" name="contact_number" 
                                placeholder="e.g., +63 912 345 6789"
                                value="<?php echo htmlspecialchars($patient['contact_number']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" class="form-control" id="address" name="address" 
                                placeholder="Enter address"
                                value="<?php echo htmlspecialchars($patient['address']); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Current Pet Image</label>
                        <div class="current-image-container">
                            <?php 
                            if (!empty($patient['image'])) {
                                if (strpos($patient['image'], 'http') === 0) {
                                    // Supabase URL
                                    echo '<img src="' . htmlspecialchars($patient['image']) . '" class="current-image" alt="Current Pet Image">';
                                } elseif (file_exists('uploads/' . $patient['image'])) {
                                    // Local file
                                    echo '<img src="uploads/' . htmlspecialchars($patient['image']) . '" class="current-image" alt="Current Pet Image">';
                                } else {
                                    echo '<div class="no-image"><i class="bi bi-image"></i><p class="help-text">No image uploaded</p></div>';
                                }
                                if (!empty($patient['image'])) {
                                    echo '<p class="help-text">Upload a new image to replace the current one</p>';
                                }
                            } else {
                                echo '<div class="no-image"><i class="bi bi-image"></i><p class="help-text">No image uploaded</p></div>';
                            }
                            ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Upload New Pet Image (Optional)</label>
                        <div class="image-upload-wrapper">
                            <label for="image" class="image-upload-label">
                                <i class="bi bi-cloud-upload"></i>
                                <p><strong>Click to upload</strong> or drag and drop</p>
                                <p style="font-size: 12px; color: #999; margin-top: 8px;">JPG, JPEG, PNG or GIF (Max 5MB)</p>
                            </label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <img id="imagePreview" class="image-preview" alt="New Image Preview">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <span class="glyphicon glyphicon-ok-circle"></span>
                            Update Patient
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <span class="glyphicon glyphicon-remove-circle"></span>
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Image preview functionality
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('imagePreview');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });

        // Drag and drop functionality
        const uploadLabel = document.querySelector('.image-upload-label');
        const fileInput = document.getElementById('image');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadLabel.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadLabel.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadLabel.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            uploadLabel.style.borderColor = '#ff9500';
            uploadLabel.style.background = '#fff5e6';
        }

        function unhighlight(e) {
            uploadLabel.style.borderColor = '#e0e0e0';
            uploadLabel.style.background = '#fafafa';
        }

        uploadLabel.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            fileInput.files = files;
            
            // Trigger change event
            const event = new Event('change', { bubbles: true });
            fileInput.dispatchEvent(event);
        }
    </script>
</body>
</html>