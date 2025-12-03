# Database Triggers Implementation Checklist

This checklist guides you through implementing database triggers and updating both backend and frontend code.

## Backend Changes

### Step 1: Run the Migration
- [ ] Run `php artisan migrate` to create the triggers
- [ ] Verify triggers were created successfully:
  ```sql
  -- MySQL
  SHOW TRIGGERS;
  
  -- PostgreSQL
  SELECT * FROM pg_trigger WHERE tgname LIKE '%amount_spent%';
  ```

### Step 2: Test Triggers in Backend
- [ ] Test creating a transaction and verify `projects.amount_spent` updates
- [ ] Test updating a transaction and verify `projects.amount_spent` updates correctly
- [ ] Test deleting a transaction and verify `projects.amount_spent` decreases
- [ ] Test financial record calculations (total_revenue, total_expenditures, net_equity)

### Step 3: Remove Manual Update Logic (IMPORTANT!)

Once triggers are working, **remove the manual update code** from controllers:

#### TransactionController.php

**Remove from `store()` method (lines ~149-163):**
```php
// ❌ REMOVE THIS:
// Update project's amount_spent based on transaction type
$project = Project::find($validated['project_id']);
if ($project) {
    if ($validated['type'] === 'expense') {
        $project->increment('amount_spent', $validated['amount']);
    } elseif ($validated['type'] === 'income') {
        $project->decrement('amount_spent', $validated['amount']);
        if ($project->amount_spent < 0) {
            $project->update(['amount_spent' => 0]);
        }
    }
}
```

**Remove from `update()` method (lines ~289-312):**
```php
// ❌ REMOVE THIS:
// Update project's amount_spent if amount or type changed
if ($transaction->project && ($oldAmount != $validated['amount'] || $oldType != $validated['type'])) {
    // Revert old transaction impact
    if ($oldType === 'expense') {
        $transaction->project->decrement('amount_spent', $oldAmount);
        if ($transaction->project->amount_spent < 0) {
            $transaction->project->update(['amount_spent' => 0]);
        }
    } elseif ($oldType === 'income') {
        $transaction->project->increment('amount_spent', $oldAmount);
    }
    
    // Apply new transaction impact
    if ($validated['type'] === 'expense') {
        $transaction->project->increment('amount_spent', $validated['amount']);
    } elseif ($validated['type'] === 'income') {
        $transaction->project->decrement('amount_spent', $validated['amount']);
        if ($transaction->project->amount_spent < 0) {
            $transaction->project->update(['amount_spent' => 0]);
        }
    }
}
```

**Remove from `destroy()` method (lines ~351-362):**
```php
// ❌ REMOVE THIS:
// Revert the transaction's impact on project's amount_spent
if ($transaction->project) {
    if ($type === 'expense') {
        $transaction->project->decrement('amount_spent', $amount);
        if ($transaction->project->amount_spent < 0) {
            $transaction->project->update(['amount_spent' => 0]);
        }
    } elseif ($type === 'income') {
        $transaction->project->increment('amount_spent', $amount);
    }
}
```

#### FinancialRecord Model

The `FinancialRecord` model already has a `boot()` method that calculates `total_revenue`. You can keep it as a backup, but the trigger will handle it at the database level.

- [ ] Optional: Remove the `boot()` method from `FinancialRecord.php` if you want triggers to be the sole source of truth
- [ ] Or keep it as a fallback (recommended for safety)

### Step 4: Update API Responses (Optional)

Consider including updated project data in transaction responses:

```php
// In TransactionController@store
$transaction->load(['project', 'official', 'documents']);

return response()->json([
    'message' => 'Transaction created successfully.',
    'data' => $transaction,
    // Optionally include refreshed project data
    'project' => $transaction->project->fresh(),
]);
```

---

## Frontend Changes

### Step 1: Update Transaction Operations

- [ ] Update `createTransaction()` to refetch project after creation
- [ ] Update `updateTransaction()` to refetch project after update
- [ ] Update `deleteTransaction()` to refetch project after deletion

### Step 2: Update Financial Record Operations

- [ ] Update `createFinancialRecord()` to refetch after creation
- [ ] Update `updateFinancialRecord()` to refetch after update

### Step 3: Update State Management

- [ ] Update React Query hooks (if using React)
- [ ] Update Pinia/Vuex stores (if using Vue)
- [ ] Update Redux/Zustand stores (if using Redux)
- [ ] Ensure cache invalidation works correctly

### Step 4: Update UI Components

- [ ] Update transaction forms to show loading states during refetch
- [ ] Update project detail pages to reflect new `amount_spent` values
- [ ] Update financial record displays to show calculated totals
- [ ] Add error handling for refetch failures

### Step 5: Testing

- [ ] Test creating transactions and verify UI updates
- [ ] Test updating transactions and verify UI updates
- [ ] Test deleting transactions and verify UI updates
- [ ] Test with multiple rapid operations (race conditions)
- [ ] Test error scenarios (network failures, etc.)
- [ ] Test optimistic updates (if used)

---

## Verification Steps

### Backend Verification

1. **Test Trigger Execution:**
   ```bash
   php artisan tinker
   ```
   ```php
   $project = Project::first();
   $initialAmount = $project->amount_spent;
   
   $transaction = Transaction::create([
       'project_id' => $project->id,
       'amount' => 1000,
       'type' => 'expense',
       'transaction_date' => now(),
   ]);
   
   $project->refresh();
   echo $project->amount_spent; // Should be $initialAmount + 1000
   ```

2. **Test Financial Record Calculations:**
   ```php
   $record = FinancialRecord::create([
       'year' => 2025,
       'ira_allocation' => 1000,
       'service_business_income' => 500,
       'local_tax_collections' => 300,
       'property_tax' => 200,
       'goods_services_tax' => 100,
   ]);
   
   echo $record->total_revenue; // Should be 2100
   ```

### Frontend Verification

1. **Create a transaction and check:**
   - Transaction is created successfully
   - Project `amount_spent` updates in the UI
   - No stale data is displayed

2. **Update a transaction and check:**
   - Transaction updates successfully
   - Project `amount_spent` reflects the change
   - Old and new amounts are handled correctly

3. **Delete a transaction and check:**
   - Transaction is deleted successfully
   - Project `amount_spent` decreases appropriately

---

## Rollback Plan

If you need to rollback triggers:

1. **Rollback the migration:**
   ```bash
   php artisan migrate:rollback --step=1
   ```

2. **Restore manual update logic** in `TransactionController.php`

3. **Update frontend** to remove refetch logic (optional, won't break anything)

---

## Benefits After Implementation

✅ **Data Consistency:** Automatic updates ensure data is always correct
✅ **Performance:** Database-level operations are faster
✅ **Code Simplification:** Less manual update logic in controllers
✅ **Data Integrity:** Triggers work even if data is modified outside Laravel
✅ **Maintainability:** Business logic centralized in database

---

## Support Documents

- `DATABASE_TRIGGERS_GUIDE.md` - Complete guide on triggers
- `FRONTEND_TRIGGERS_GUIDE.md` - Frontend implementation guide
- `ERD_ATTRIBUTES_ANALYSIS.md` - Database structure analysis

---

## Questions?

If you encounter issues:

1. Check trigger creation: `SHOW TRIGGERS;` (MySQL) or query `pg_trigger` (PostgreSQL)
2. Check Laravel logs: `storage/logs/laravel.log`
3. Test triggers directly in database console
4. Verify frontend is refetching related data
5. Check network tab for API calls and responses


