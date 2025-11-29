# Quick Setup Guide - PetClinic with Supabase

Follow these steps to get your PetClinic application running with Supabase:

## Step 1: Create Supabase Project
1. Go to https://supabase.com
2. Sign up or log in
3. Click "New Project"
4. Fill in project details and wait for setup to complete

## Step 2: Get Your Credentials
1. In your Supabase project, go to **Settings** → **API**
2. Copy these values:
   - **Project URL** (e.g., `https://xxxxx.supabase.co`)
   - **anon public key** (starts with `eyJ...`)

## Step 3: Configure Application
1. Open the `.env` file in your PetClinic folder
2. Replace the placeholder values:
   ```
   SUPABASE_URL=https://your-project-id.supabase.co
   SUPABASE_KEY=your-anon-public-key-here
   ```

## Step 4: Create Database Table
1. In Supabase, go to **SQL Editor**
2. Click "New Query"
3. Copy and paste the entire content from `supabase_schema.sql`
4. Click "Run" to execute

## Step 5: Create Storage Bucket
1. In Supabase, go to **Storage**
2. Click "New Bucket"
3. Name it: `pet-images`
4. Make it **Public** (check the public checkbox)
5. Click "Create Bucket"

The storage policies are already created by the SQL script in Step 4.

## Step 6: Test Your Application
1. Open your PetClinic in a browser (e.g., `http://localhost/PetClinic`)
2. Try adding a new patient
3. Upload a pet image
4. Verify everything works!

## Verification Checklist
- [ ] `.env` file has correct Supabase URL and key
- [ ] `patients` table exists in Supabase (check Table Editor)
- [ ] `pet-images` bucket exists and is public (check Storage)
- [ ] Can add a new patient successfully
- [ ] Images display correctly

## Troubleshooting

### "Database error" message
- Double-check your SUPABASE_URL and SUPABASE_KEY in `.env`
- Ensure the `patients` table was created successfully

### Images not uploading
- Verify the `pet-images` bucket is public
- Check that storage policies were created
- Look at browser console for error messages

### "Connection failed" error
- Check your internet connection
- Verify your Supabase project is active
- Confirm the project URL is correct

## Need Help?
- Read the full README.md for detailed information
- Check Supabase documentation: https://supabase.com/docs
- Review the SQL script in `supabase_schema.sql`

## Next Steps (Optional)
- Add user authentication using Supabase Auth
- Implement more restrictive RLS policies
- Deploy to production hosting
- Add search and filtering features
