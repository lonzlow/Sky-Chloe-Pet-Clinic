<?php
include('inc/database.php');

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect and sanitize inputs
    $pet_name = trim($_POST['pet_name']);
    $age = trim($_POST['age']);
    $gender = trim($_POST['gender']);
    $species = trim($_POST['species']);
    $breed = trim($_POST['breed']);
    $owner_name = trim($_POST['owner_name']);
    $contact_number = trim($_POST['contact_number']);
    $address = trim($_POST['address']);
    
    // Validation
    if (empty($pet_name)) $errors[] = "Pet name is required.";
    if (empty($age)) $errors[] = "Age is required.";
    if (empty($gender)) $errors[] = "Gender is required.";
    if (empty($species)) $errors[] = "Species is required.";
    if (empty($breed)) $errors[] = "Breed is required.";
    
    // Handle image upload to Supabase Storage
    $image_url = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_name = $_FILES['image']['name'];
        $file_size = $_FILES['image']['size'];
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed_extensions)) {
            $errors[] = "Only JPG, JPEG, PNG & GIF files are allowed.";
        }

        if ($file_size > 10242880) {
            $errors[] = "Image size must be less than 10MB.";
        }
        
        if (empty($errors)) {
            $unique_name = uniqid('pet_', true) . '.' . $file_ext;
            $file_content = file_get_contents($file_tmp);
            
            try {
                // Upload to Supabase Storage bucket 'pet-images'
                uploadToSupabase('pet-images', $unique_name, $file_content);
                $image_url = getPublicUrl('pet-images', $unique_name);
            } catch (Exception $e) {
                // Fallback to local storage if Supabase upload fails
                if (!file_exists('uploads')) {
                    mkdir('uploads', 0777, true);
                }
                $upload_path = 'uploads/' . $unique_name;
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    $image_url = $unique_name; // Store filename only for local
                } else {
                    $errors[] = "Failed to upload image.";
                }
            }
        }
    }

    // Insert data to Supabase
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
            
            insertPatient($patientData);
            
            header("Location: index.php?success=1");
            exit();

        } catch (Exception $e) {
            $errors[] = "Database error: " . $e->getMessage();
            // Log the full error for debugging
            error_log("Supabase insert error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Patient - Pet Clinic</title>
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
                    <li><a href="index.php">Home</a></li>
                    <li><a href="#services">Our Service</a></li>
                    <li><a href="#about">About Us</a></li>
                </ul>
                
                <a href="add_patient.php" class="contact-btn">Contact Us</a>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 30px; margin-bottom: 30px;">
        <div class="form-wrapper">
            <div class="form-header">
                <h1>Add New Patient</h1>
                <p>Fill in the details below to register a new pet patient</p>
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
                    <div class="form-group">
                        <label for="pet_name">Pet Name <span class="required">*</span></label>
                        <input type="text" class="form-control" id="pet_name" name="pet_name" 
                            placeholder="Enter pet's name"
                            value="<?php echo isset($_POST['pet_name']) ? htmlspecialchars($_POST['pet_name']) : ''; ?>" 
                            required>
                    </div>

                    <div class="form-group">
                        <label for="age">Age <span class="required">*</span></label>
                        <input type="number" class="form-control" id="age" name="age" 
                            placeholder="Enter age"
                            value="<?php echo isset($_POST['age']) ? htmlspecialchars($_POST['age']) : ''; ?>" 
                            required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="gender">Gender <span class="text-danger">*</span></label>
                                <select class="form-control" id="gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="species">Species <span class="text-danger">*</span></label>
                                <select class="form-control" id="species" name="species" required>
                                    <option value="">Select Species</option>
                                    <option value="Dog" <?php echo (isset($_POST['species']) && $_POST['species'] == 'Dog') ? 'selected' : ''; ?>>Dog</option>
                                    <option value="Cat" <?php echo (isset($_POST['species']) && $_POST['species'] == 'Cat') ? 'selected' : ''; ?>>Cat</option>
                                    <option value="Bird" <?php echo (isset($_POST['species']) && $_POST['species'] == 'Bird') ? 'selected' : ''; ?>>Bird</option>
                                    <option value="Rabbit" <?php echo (isset($_POST['species']) && $_POST['species'] == 'Rabbit') ? 'selected' : ''; ?>>Rabbit</option>
                                    <option value="Other" <?php echo (isset($_POST['species']) && $_POST['species'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="breed">Breed <span class="required">*</span></label>
                        <input type="text" class="form-control" id="breed" name="breed" 
                            placeholder="Enter breed"
                            value="<?php echo isset($_POST['breed']) ? htmlspecialchars($_POST['breed']) : ''; ?>" 
                            required>
                    </div>

                    <div class="form-group">
                        <label for="owner_name">Owner Name</label>
                        <input type="text" class="form-control" id="owner_name" name="owner_name" 
                            placeholder="Enter owner's full name"
                            value="<?php echo isset($_POST['owner_name']) ? htmlspecialchars($_POST['owner_name']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="contact_number">Contact Number</label>
                        <input type="text" class="form-control" id="contact_number" name="contact_number" 
                            placeholder="e.g., +63 912 345 6789"
                            value="<?php echo isset($_POST['contact_number']) ? htmlspecialchars($_POST['contact_number']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" class="form-control" id="address" name="address" 
                            placeholder="Enter address"
                            value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>Pet Image</label>
                        <div class="image-upload-wrapper">
                            <label for="image" class="image-upload-label">
                                <i class="bi bi-cloud-upload"></i>
                                <p><strong>Click to upload</strong> or drag and drop</p>
                                <p style="font-size: 12px; color: #999; margin-top: 8px;">JPG, JPEG, PNG or GIF (Max 10MB)</p>
                            </label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <img id="imagePreview" class="image-preview" alt="Image Preview">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <span class="glyphicon glyphicon-ok-circle"></span> Save Patient
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <span class="glyphicon glyphicon-remove-circle"></span> Cancel
                        </a>
                    </div>
                   
                </form>
            </div>
        </div>
    </div>

    <!-- JS -->
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
    </script>
</body>
</html>
