-- Supabase Database Schema for PetClinic
-- Run this in your Supabase SQL Editor

-- Create patients table
CREATE TABLE IF NOT EXISTS patients (
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
  created_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc', NOW()),
  updated_at TIMESTAMP WITH TIME ZONE DEFAULT TIMEZONE('utc', NOW())
);

-- Create an index on created_at for faster sorting
CREATE INDEX IF NOT EXISTS idx_patients_created_at ON patients(created_at DESC);

-- Create an index on pet_name for faster searches
CREATE INDEX IF NOT EXISTS idx_patients_pet_name ON patients(pet_name);

-- Enable Row Level Security
ALTER TABLE patients ENABLE ROW LEVEL SECURITY;

-- Create policy to allow all operations for public access
-- WARNING: In production, you should create more restrictive policies
CREATE POLICY "Allow all operations for public" ON patients
  FOR ALL
  USING (true)
  WITH CHECK (true);

-- Create updated_at trigger
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = TIMEZONE('utc', NOW());
    RETURN NEW;
END;
$$ language 'plpgsql';

CREATE TRIGGER update_patients_updated_at 
  BEFORE UPDATE ON patients 
  FOR EACH ROW 
  EXECUTE FUNCTION update_updated_at_column();

-- Storage bucket setup (run these in the SQL editor as well)
-- Note: You may need to create the bucket via the UI first

-- Storage policies for pet-images bucket
-- Allow public read access
INSERT INTO storage.buckets (id, name, public) 
VALUES ('pet-images', 'pet-images', true)
ON CONFLICT (id) DO NOTHING;

-- Allow public SELECT (read) access to pet-images
CREATE POLICY "Public Access to pet-images" 
ON storage.objects FOR SELECT 
USING ( bucket_id = 'pet-images' );

-- Allow INSERT (upload) for everyone
CREATE POLICY "Anyone can upload to pet-images" 
ON storage.objects FOR INSERT 
WITH CHECK ( bucket_id = 'pet-images' );

-- Allow UPDATE for everyone
CREATE POLICY "Anyone can update pet-images" 
ON storage.objects FOR UPDATE 
USING ( bucket_id = 'pet-images' );

-- Allow DELETE for everyone
CREATE POLICY "Anyone can delete from pet-images" 
ON storage.objects FOR DELETE 
USING ( bucket_id = 'pet-images' );

-- Insert sample data (optional)
-- INSERT INTO patients (pet_name, age, gender, species, breed, owner_name, contact_number, address) 
-- VALUES 
--   ('Max', '3', 'Male', 'Dog', 'Golden Retriever', 'John Doe', '+63 912 345 6789', '123 Main St, Quezon City'),
--   ('Luna', '2', 'Female', 'Cat', 'Persian', 'Jane Smith', '+63 923 456 7890', '456 Oak Ave, Makati City'),
--   ('Charlie', '1', 'Male', 'Rabbit', 'Holland Lop', 'Bob Johnson', '+63 934 567 8901', '789 Pine Rd, Manila');
