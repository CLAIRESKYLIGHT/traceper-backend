# Matnog Municipality - Database Seeding Guide

## Overview
This guide explains how to populate your database with real data for **Matnog Municipality, Sorsogon, Bicol Region (Region V)**.

## Municipality Information
- **Name**: Matnog, Sorsogon
- **Region**: Bicol Region (Region V)
- **Land Area**: 162.40 km²
- **Population (2020)**: 41,989
- **Number of Barangays**: 40
- **Postal Code**: 4708

## What Gets Seeded

### 1. Barangays (40 barangays)
All 40 barangays of Matnog with their 2020 population data:
- Balocawe, Banogao, Banuangdaan, Bariis, Bolo, etc.
- Each includes population from 2020 census
- Status set to "Active"

### 2. Contractors (6 contractors)
Local construction companies and contractors:
- Bicol Builders & Construction Co.
- Matnog Infrastructure Development Corp.
- Southern Luzon Construction Services
- Aqua Solutions Philippines
- Flood Control Engineering Services
- Public Works & Maintenance Co.

### 3. Officials
- **Municipal Officials**: Mayor, Vice Mayor, 8 Councilors (10 total)
- **Barangay Officials**: For each of the 40 barangays:
  - Barangay Captain
  - Barangay Secretary
  - Barangay Treasurer
- **Total**: ~130 officials

### 4. Projects (6 real projects)
Based on actual government projects:
1. **Construction of Level II Potable Water Supply - Hidhid**
   - Budget: ₱2,500,000
   - Status: In Progress

2. **Construction of Flood Control - Barangay Culasi**
   - Budget: ₱5,000,000
   - Status: In Progress

3. **Repair/Rehabilitation of Public C.R. and Stage - Barangay Culasi**
   - Budget: ₱800,000
   - Status: Completed

4. **Matnog Municipal Building Renovation**
   - Budget: ₱12,000,000
   - Status: In Progress

5. **Road Improvement - Barangay Tabunan**
   - Budget: ₱3,500,000
   - Status: In Progress

6. **Drainage System - Barangay Gadgaron**
   - Budget: ₱4,200,000
   - Status: Completed

### 5. Transactions
Each project automatically gets 3-6 sample transactions showing:
- Amount spent
- Date of transaction
- Official who authorized it
- Description of payment

## How to Seed

### Option 1: Seed Everything (Recommended)
```bash
php artisan db:seed
```

This will run all seeders in order:
1. Admin users
2. Matnog barangays (40)
3. Matnog contractors (6)
4. Matnog officials (~130)
5. Matnog projects (6) with transactions

### Option 2: Seed Individual Components
```bash
# Seed only barangays
php artisan db:seed --class=MatnogBarangaySeeder

# Seed only contractors
php artisan db:seed --class=MatnogContractorSeeder

# Seed only officials
php artisan db:seed --class=MatnogOfficialSeeder

# Seed only projects (requires barangays, contractors, officials first)
php artisan db:seed --class=MatnogProjectSeeder
```

### Option 3: Fresh Migration + Seed
```bash
# WARNING: This will delete all existing data!
php artisan migrate:fresh --seed
```

## Data Verification

After seeding, verify the data:

```bash
# Check barangays
php artisan tinker
>>> App\Models\Barangay::count()  // Should return 40

# Check contractors
>>> App\Models\Contractor::count()  // Should return 6

# Check officials
>>> App\Models\Official::count()  // Should return ~130

# Check projects
>>> App\Models\Project::count()  // Should return 6

# Check transactions
>>> App\Models\Transaction::count()  // Should return 18-36 (3-6 per project)
```

## Frontend Display

Once seeded, your frontend will show:

### Dashboard
- 40 Barangays
- 6 Projects
- ~130 Officials
- 6 Contractors
- 18-36 Transactions
- Documents (when uploaded)

### Projects Page
- 6 real projects with:
  - Actual barangay locations
  - Realistic budgets
  - Project statuses
  - Associated contractors
  - Transaction history

### Barangays Page
- All 40 barangays with:
  - Population data
  - Associated projects
  - Barangay officials

## Notes

1. **Population Data**: All population figures are from the 2020 Census
2. **Project Data**: Based on publicly available government procurement notices
3. **Officials**: Names are generic placeholders - update with real names if available
4. **Contractors**: Based on typical local construction companies
5. **Transactions**: Automatically generated to match project budgets

## Customization

To customize the data:

1. **Update Barangay Names**: Edit `MatnogBarangaySeeder.php`
2. **Add More Projects**: Edit `MatnogProjectSeeder.php`
3. **Change Officials**: Edit `MatnogOfficialSeeder.php`
4. **Add Contractors**: Edit `MatnogContractorSeeder.php`

## Next Steps

After seeding:
1. Create admin user (if not already created)
2. Upload documents for projects
3. Add more transactions as needed
4. Update official names with real data
5. Add more projects as they are created

## Support

If you encounter issues:
- Make sure migrations are run: `php artisan migrate`
- Check database connection
- Verify all required models exist
- Check seeder order in `DatabaseSeeder.php`

