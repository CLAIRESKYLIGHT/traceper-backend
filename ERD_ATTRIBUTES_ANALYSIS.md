# Database Attributes Analysis for ERD

This document lists all multivalued, derived, composite, and unique (non-PK) attributes in the database.

## 1. MULTIVALUED ATTRIBUTES

Multivalued attributes are those that can have multiple values for a single entity instance. In this database, they are represented as separate tables (relationships).

### Project Entity
- **officials** (Many-to-Many)
  - Relationship: `project_officials` pivot table
  - A project can have multiple officials assigned
  - Junction table: `project_officials` with `project_id`, `official_id`, `role_in_project`

- **documents** (One-to-Many)
  - Relationship: `documents` table
  - A project can have multiple documents
  - Foreign key: `documents.project_id`

- **transactions** (One-to-Many)
  - Relationship: `transactions` table
  - A project can have multiple transactions
  - Foreign key: `transactions.project_id`

### Barangay Entity
- **officials** (One-to-Many)
  - Relationship: `officials` table
  - A barangay can have multiple officials
  - Foreign key: `officials.barangay_id`

- **projects** (One-to-Many)
  - Relationship: `projects` table
  - A barangay can have multiple projects
  - Foreign key: `projects.barangay_id`

- **ira_shares** (One-to-Many)
  - Relationship: `barangay_ira_shares` table
  - A barangay can have multiple IRA share records (one per year)
  - Foreign key: `barangay_ira_shares.barangay_id`

### Contractor Entity
- **projects** (One-to-Many)
  - Relationship: `projects` table
  - A contractor can work on multiple projects
  - Foreign key: `projects.contractor_id`

### Official Entity
- **projects** (Many-to-Many)
  - Relationship: `project_officials` pivot table
  - An official can be assigned to multiple projects
  - Junction table: `project_officials` with `project_id`, `official_id`, `role_in_project`

- **transactions** (One-to-Many)
  - Relationship: `transactions` table
  - An official can be associated with multiple transactions
  - Foreign key: `transactions.official_id`

### Transaction Entity
- **documents** (One-to-Many)
  - Relationship: `documents` table
  - A transaction can have multiple documents
  - Foreign key: `documents.transaction_id`

---

## 2. DERIVED ATTRIBUTES

Derived attributes are calculated/computed from other attributes and are not stored directly in the database.

### Contractor Entity
- **total_received** (Virtual/Computed)
  - Calculation: Sum of all expense transactions from all projects associated with the contractor
  - Formula: `SUM(transactions.amount WHERE transactions.type = 'expense' AND transactions.project_id IN contractor.projects)`
  - Accessor: `getTotalReceivedAttribute()` in Contractor model

### Project Entity
- **remaining_budget** (Virtual/Computed)
  - Calculation: `budget_allocated - amount_spent`
  - Accessor: `getRemainingBudgetAttribute()` in Project model

- **total_transactions** (Virtual/Computed)
  - Calculation: Sum of all transaction amounts for the project
  - Formula: `SUM(transactions.amount WHERE transactions.project_id = project.id)`
  - Accessor: `getTotalTransactionsAttribute()` in Project model

### Barangay Entity
- **total_budget_allocated** (Virtual/Computed)
  - Calculation: Sum of budget_allocated from all projects in the barangay
  - Formula: `SUM(projects.budget_allocated WHERE projects.barangay_id = barangay.id)`
  - Accessor: `getTotalBudgetAllocatedAttribute()` in Barangay model

- **total_amount_spent** (Virtual/Computed)
  - Calculation: Sum of amount_spent from all projects in the barangay
  - Formula: `SUM(projects.amount_spent WHERE projects.barangay_id = barangay.id)`
  - Accessor: `getTotalAmountSpentAttribute()` in Barangay model

### FinancialRecord Entity
- **total_revenue** (Derived/Computed)
  - Calculation: Sum of all revenue components
  - Formula: `ira_allocation + service_business_income + local_tax_collections + property_tax + goods_services_tax`
  - Accessor: `getTotalRevenueAttribute()` and `calculateTotalRevenue()` in FinancialRecord model
  - Note: Stored in database but auto-calculated before saving

- **total_project_expenses** (Virtual/Computed)
  - Calculation: Sum of all expense transactions for the year
  - Formula: `SUM(transactions.amount WHERE YEAR(transactions.transaction_date) = financial_record.year AND transactions.type = 'expense')`
  - Accessor: `getTotalProjectExpensesAttribute()` in FinancialRecord model

- **total_project_income** (Virtual/Computed)
  - Calculation: Sum of all income transactions for the year
  - Formula: `SUM(transactions.amount WHERE YEAR(transactions.transaction_date) = financial_record.year AND transactions.type = 'income')`
  - Accessor: `getTotalProjectIncomeAttribute()` in FinancialRecord model

- **available_budget** (Virtual/Computed)
  - Calculation: `total_revenue - total_expenditures - total_project_expenses + total_project_income`
  - Accessor: `getAvailableBudgetAttribute()` in FinancialRecord model

- **total_actual_expenditures** (Virtual/Computed)
  - Calculation: `total_expenditures + total_project_expenses`
  - Accessor: `getTotalActualExpendituresAttribute()` in FinancialRecord model

---

## 3. COMPOSITE ATTRIBUTES

Composite attributes are made up of multiple components that could be broken down further.

### Contractor Entity
- **contact_info** (Composite)
  - Components: Potentially includes phone number, email, fax, etc.
  - Currently stored as a single string field
  - Could be decomposed into: phone, email, fax, mobile, etc.

- **address** (Composite)
  - Components: Potentially includes street, city, province, postal code, etc.
  - Currently stored as a single string field
  - Could be decomposed into: street_address, city, province, postal_code, country, etc.

### Project Entity
- **date_range** (Composite - Implicit)
  - Components:
    - `start_date` (beginning of project)
    - `estimated_completion_date` (planned end)
    - `actual_completion_date` (actual end)
  - These three dates together form a composite date range attribute

### FinancialRecord Entity
- **revenue_components** (Composite - Implicit)
  - Components that make up total revenue:
    - `ira_allocation`
    - `service_business_income`
    - `local_tax_collections`
    - `property_tax`
    - `goods_services_tax`
  - These are stored separately but conceptually form a composite revenue attribute

- **expenditure_components** (Composite - Implicit)
  - Components that make up total expenditures:
    - `personnel_services`
    - `maintenance_operating_expenses`
    - `capital_outlay`
  - These are stored separately but conceptually form a composite expenditure attribute

- **financial_position** (Composite - Implicit)
  - Components:
    - `total_assets`
    - `total_liabilities`
    - `net_equity` (derived as assets - liabilities)
  - These three together form a composite financial position attribute

---

## 4. UNIQUE (NON-PK) ATTRIBUTES

Unique attributes are those with unique constraints that are not primary keys.

### Users Entity
- **email** (Unique)
  - Constraint: `UNIQUE` on `users.email`
  - Migration: `0001_01_01_000000_create_users_table.php`
  - Purpose: Ensures each user has a unique email address

### FinancialRecord Entity
- **year** (Unique)
  - Constraint: `UNIQUE` on `financial_records.year`
  - Migration: `2025_11_21_083000_create_financial_records_table.php`
  - Purpose: Ensures only one financial record per year

### BarangayIraShare Entity
- **composite_unique** (Unique - Composite)
  - Constraint: `UNIQUE(barangay_id, year)` on `barangay_ira_shares` table
  - Migration: `2025_11_21_083100_create_barangay_ira_shares_table.php`
  - Purpose: Ensures one IRA share record per barangay per year
  - Note: This is a composite unique constraint (not a single attribute)

---

## Summary Table

| Entity | Multivalued | Derived | Composite | Unique (non-PK) |
|--------|------------|---------|-----------|-----------------|
| **Contractor** | projects | total_received | contact_info, address | - |
| **Project** | officials, documents, transactions | remaining_budget, total_transactions | date_range | - |
| **Barangay** | officials, projects, ira_shares | total_budget_allocated, total_amount_spent | - | - |
| **Official** | projects, transactions | - | - | - |
| **Transaction** | documents | - | - | - |
| **Document** | - | - | - | - |
| **FinancialRecord** | - | total_revenue, total_project_expenses, total_project_income, available_budget, total_actual_expenditures | revenue_components, expenditure_components, financial_position | year |
| **BarangayIraShare** | - | - | - | (barangay_id, year) composite |
| **User** | - | - | - | email |

---

## Notes for ERD Design

1. **Multivalued attributes** should be represented as separate entities with relationships (1:N or M:N).

2. **Derived attributes** should be shown with a dashed underline or in a separate section, indicating they are computed rather than stored.

3. **Composite attributes** can be shown as:
   - A single attribute with sub-attributes (if not decomposed)
   - Multiple related attributes grouped together (if decomposed)

4. **Unique constraints** should be indicated with a "U" symbol or unique constraint notation on the attribute(s).

5. **Junction tables** (project_officials) represent many-to-many relationships and should be shown as separate entities in the ERD.

