# PetClinic - Supabase Integration

## Overview
This Pet Clinic application has been migrated from MySQL to **Supabase**, a modern Backend-as-a-Service platform. The application now uses Supabase for database operations and file storage.

## Prerequisites
- PHP 7.4 or higher
- Composer (PHP dependency manager)
- A Supabase account and project

## Supabase Setup

### 1. Create a Supabase Project
1. Go to [https://supabase.com](https://supabase.com) and sign up/login
2. Create a new project
3. Note your project URL and API keys from Settings > API

### 2. Create the Database Table
In your Supabase project's SQL Editor, run this SQL:

```sql
-- Create patients table
CREATE TABLE patients (
  id BIGSERIAL PRIMARY KEY,
  pet_name VARCHAR(255) NOT NULL,
  age VARCHAR(50) NOT NULL,
  gender VARCHAR(20) NOT NULL,
  species VARCHAR(100) NOT NULL,
  breed VARCHAR(255) NOT NULL,
  owner_name VARCHAR(255),
  contact_number VARCHAR(50),
  address TEXT,
  image TEXT,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc', NOW())
);

-- Enable Row Level Security
ALTER TABLE patients ENABLE ROW LEVEL SECURITY;

-- Create policy to allow all operations (adjust based on your needs)
CREATE POLICY "Allow all operations" ON patients
  FOR ALL
  USING (true)
  WITH CHECK (true);
```

### 3. Create Storage Bucket
1. Go to Storage in your Supabase dashboard
2. Create a new bucket named `pet-images`
3. Make it **public** (so images can be displayed)
4. Set appropriate policies:

```sql
-- Allow public read access
CREATE POLICY "Public Access"
ON storage.objects FOR SELECT
USING ( bucket_id = 'pet-images' );

-- Allow authenticated uploads
CREATE POLICY "Authenticated Upload"
ON storage.objects FOR INSERT
WITH CHECK ( bucket_id = 'pet-images' );

-- Allow authenticated updates
CREATE POLICY "Authenticated Update"
ON storage.objects FOR UPDATE
USING ( bucket_id = 'pet-images' );

-- Allow authenticated deletes
CREATE POLICY "Authenticated Delete"
ON storage.objects FOR DELETE
USING ( bucket_id = 'pet-images' );
```

## Installation

### 1. Configure Environment Variables
Copy `.env.example` to `.env`:

```bash
copy .env.example .env
```

Edit `.env` and add your Supabase credentials:

```env
SUPABASE_URL=https://your-project.supabase.co
SUPABASE_KEY=your-anon-public-key
SUPABASE_SERVICE_KEY=your-service-role-key
```

### 2. Install Dependencies (Optional)
The application uses native PHP cURL for API calls, so Composer is optional. However, if you want to use the official Supabase PHP client in the future:

```bash
composer install
```

## Project Structure

```
PetClinic/
├── inc/
│   ├── database.php       # Database connection (now uses Supabase)
│   └── supabase.php       # Supabase helper functions
├── assets/                # CSS, JS, images
├── uploads/               # Local fallback for images
├── .env                   # Environment variables (create from .env.example)
├── .env.example           # Environment template
├── composer.json          # PHP dependencies
├── index.php              # Home page (list patients)
├── add_patient.php        # Add new patient
├── edit_patient.php       # Edit patient
├── view_patient.php       # View patient details
└── delete_patient.php     # Delete patient
```

## Key Changes from MySQL

### Database Operations
- **Before**: PDO with prepared statements
- **After**: Supabase REST API with helper functions

### Helper Functions (in `inc/supabase.php`)
- `getAllPatients()` - Fetch all patients
- `getPatientById($id)` - Fetch single patient
- `insertPatient($data)` - Create new patient
- `updatePatient($id, $data)` - Update patient
- `deletePatient($id)` - Delete patient

### File Storage
- **Before**: Local `uploads/` directory
- **After**: Supabase Storage bucket `pet-images`
- **Fallback**: Still supports local storage if Supabase upload fails

### Image Handling
Images are now stored in Supabase Storage and the database stores the public URL:
- Format: `https://your-project.supabase.co/storage/v1/object/public/pet-images/filename.jpg`
- The application detects if an image is a URL or local filename

## Usage

1. **Add Patient**: Upload pet information and images
2. **View Patients**: Browse all registered pets
3. **Edit Patient**: Update pet information
4. **Delete Patient**: Remove patient (also deletes associated image)

## Troubleshooting

### Images Not Displaying
1. Check if the `pet-images` bucket is public
2. Verify storage policies allow public read access
3. Check browser console for CORS errors

### Database Errors
1. Verify your `.env` credentials are correct
2. Check if the `patients` table exists
3. Ensure RLS policies allow the operations

### Connection Issues
1. Check if your Supabase project is active
2. Verify the SUPABASE_URL is correct
3. Test API key validity in Supabase dashboard

## Security Notes

- The `.env` file contains sensitive credentials - **never commit it to version control**
- Consider implementing user authentication for production
- Review and tighten RLS policies based on your security requirements
- Use the service role key only for admin operations

## Migration from MySQL

If you have existing MySQL data, you can migrate it:

1. Export your MySQL data:
```sql
SELECT * FROM patients INTO OUTFILE '/tmp/patients.csv'
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n';
```

2. Import to Supabase using the dashboard (Table Editor > Import Data)

3. Upload existing images to the `pet-images` bucket

4. Update image paths in the database to use Supabase URLs

## Support

For Supabase documentation: [https://supabase.com/docs](https://supabase.com/docs)

## License

This project is open source and available for educational purposes.
