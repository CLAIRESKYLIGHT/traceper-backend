# Database Triggers Guide

## Yes, You Can Use Database Triggers in Laravel!

Database triggers are **absolutely possible** in Laravel. You can create them using migrations with raw SQL statements. This guide shows you how to implement triggers for your database.

## Benefits of Using Triggers

1. **Data Consistency**: Automatically maintain calculated fields
2. **Performance**: Database-level operations are faster than application-level
3. **Data Integrity**: Ensures calculations happen even if data is modified outside Laravel
4. **Reduced Code**: Less manual update logic in controllers

## Current Manual Updates (That Could Be Automated)

Looking at your codebase, you're currently manually updating:
- `projects.amount_spent` when transactions are created/updated/deleted
- `financial_records.total_revenue` (already handled in model, but could be a trigger)

---

## How to Create Triggers in Laravel

### Step 1: Create a Migration

```bash
php artisan make:migration create_database_triggers
```

### Step 2: Write Trigger SQL

You'll need different SQL syntax depending on your database:

- **MySQL/MariaDB**: Uses `CREATE TRIGGER` syntax
- **PostgreSQL**: Uses `CREATE OR REPLACE FUNCTION` + `CREATE TRIGGER` syntax
- **SQL Server**: Uses `CREATE TRIGGER` with different syntax

---

## Example Triggers for Your Database

### Example 1: Auto-Update `projects.amount_spent` on Transaction Changes

This trigger automatically updates the project's `amount_spent` when transactions are inserted, updated, or deleted.

#### For MySQL/MariaDB:

```sql
-- Trigger for INSERT
DELIMITER $$
CREATE TRIGGER update_project_amount_spent_on_insert
AFTER INSERT ON transactions
FOR EACH ROW
BEGIN
    IF NEW.type = 'expense' THEN
        UPDATE projects 
        SET amount_spent = amount_spent + NEW.amount
        WHERE id = NEW.project_id;
    ELSEIF NEW.type = 'income' THEN
        UPDATE projects 
        SET amount_spent = GREATEST(0, amount_spent - NEW.amount)
        WHERE id = NEW.project_id;
    END IF;
END$$
DELIMITER ;

-- Trigger for UPDATE
DELIMITER $$
CREATE TRIGGER update_project_amount_spent_on_update
AFTER UPDATE ON transactions
FOR EACH ROW
BEGIN
    -- Revert old transaction impact
    IF OLD.type = 'expense' THEN
        UPDATE projects 
        SET amount_spent = GREATEST(0, amount_spent - OLD.amount)
        WHERE id = OLD.project_id;
    ELSEIF OLD.type = 'income' THEN
        UPDATE projects 
        SET amount_spent = amount_spent + OLD.amount
        WHERE id = OLD.project_id;
    END IF;
    
    -- Apply new transaction impact
    IF NEW.type = 'expense' THEN
        UPDATE projects 
        SET amount_spent = amount_spent + NEW.amount
        WHERE id = NEW.project_id;
    ELSEIF NEW.type = 'income' THEN
        UPDATE projects 
        SET amount_spent = GREATEST(0, amount_spent - NEW.amount)
        WHERE id = NEW.project_id;
    END IF;
END$$
DELIMITER ;

-- Trigger for DELETE
DELIMITER $$
CREATE TRIGGER update_project_amount_spent_on_delete
AFTER DELETE ON transactions
FOR EACH ROW
BEGIN
    IF OLD.type = 'expense' THEN
        UPDATE projects 
        SET amount_spent = GREATEST(0, amount_spent - OLD.amount)
        WHERE id = OLD.project_id;
    ELSEIF OLD.type = 'income' THEN
        UPDATE projects 
        SET amount_spent = amount_spent + OLD.amount
        WHERE id = OLD.project_id;
    END IF;
END$$
DELIMITER ;
```

#### For PostgreSQL:

```sql
-- Function for INSERT
CREATE OR REPLACE FUNCTION update_project_amount_spent_on_insert()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.type = 'expense' THEN
        UPDATE projects 
        SET amount_spent = amount_spent + NEW.amount
        WHERE id = NEW.project_id;
    ELSIF NEW.type = 'income' THEN
        UPDATE projects 
        SET amount_spent = GREATEST(0, amount_spent - NEW.amount)
        WHERE id = NEW.project_id;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Function for UPDATE
CREATE OR REPLACE FUNCTION update_project_amount_spent_on_update()
RETURNS TRIGGER AS $$
BEGIN
    -- Revert old transaction impact
    IF OLD.type = 'expense' THEN
        UPDATE projects 
        SET amount_spent = GREATEST(0, amount_spent - OLD.amount)
        WHERE id = OLD.project_id;
    ELSIF OLD.type = 'income' THEN
        UPDATE projects 
        SET amount_spent = amount_spent + OLD.amount
        WHERE id = OLD.project_id;
    END IF;
    
    -- Apply new transaction impact
    IF NEW.type = 'expense' THEN
        UPDATE projects 
        SET amount_spent = amount_spent + NEW.amount
        WHERE id = NEW.project_id;
    ELSIF NEW.type = 'income' THEN
        UPDATE projects 
        SET amount_spent = GREATEST(0, amount_spent - NEW.amount)
        WHERE id = NEW.project_id;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Function for DELETE
CREATE OR REPLACE FUNCTION update_project_amount_spent_on_delete()
RETURNS TRIGGER AS $$
BEGIN
    IF OLD.type = 'expense' THEN
        UPDATE projects 
        SET amount_spent = GREATEST(0, amount_spent - OLD.amount)
        WHERE id = OLD.project_id;
    ELSIF OLD.type = 'income' THEN
        UPDATE projects 
        SET amount_spent = amount_spent + OLD.amount
        WHERE id = OLD.project_id;
    END IF;
    RETURN OLD;
END;
$$ LANGUAGE plpgsql;

-- Create triggers
CREATE TRIGGER update_project_amount_spent_on_insert
    AFTER INSERT ON transactions
    FOR EACH ROW
    EXECUTE FUNCTION update_project_amount_spent_on_insert();

CREATE TRIGGER update_project_amount_spent_on_update
    AFTER UPDATE ON transactions
    FOR EACH ROW
    EXECUTE FUNCTION update_project_amount_spent_on_update();

CREATE TRIGGER update_project_amount_spent_on_delete
    AFTER DELETE ON transactions
    FOR EACH ROW
    EXECUTE FUNCTION update_project_amount_spent_on_delete();
```

### Example 2: Auto-Calculate `financial_records.total_revenue`

#### For MySQL/MariaDB:

```sql
DELIMITER $$
CREATE TRIGGER calculate_total_revenue_on_update
BEFORE UPDATE ON financial_records
FOR EACH ROW
BEGIN
    SET NEW.total_revenue = (
        COALESCE(NEW.ira_allocation, 0) +
        COALESCE(NEW.service_business_income, 0) +
        COALESCE(NEW.local_tax_collections, 0) +
        COALESCE(NEW.property_tax, 0) +
        COALESCE(NEW.goods_services_tax, 0)
    );
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER calculate_total_revenue_on_insert
BEFORE INSERT ON financial_records
FOR EACH ROW
BEGIN
    SET NEW.total_revenue = (
        COALESCE(NEW.ira_allocation, 0) +
        COALESCE(NEW.service_business_income, 0) +
        COALESCE(NEW.local_tax_collections, 0) +
        COALESCE(NEW.property_tax, 0) +
        COALESCE(NEW.goods_services_tax, 0)
    );
END$$
DELIMITER ;
```

#### For PostgreSQL:

```sql
CREATE OR REPLACE FUNCTION calculate_total_revenue()
RETURNS TRIGGER AS $$
BEGIN
    NEW.total_revenue := (
        COALESCE(NEW.ira_allocation, 0) +
        COALESCE(NEW.service_business_income, 0) +
        COALESCE(NEW.local_tax_collections, 0) +
        COALESCE(NEW.property_tax, 0) +
        COALESCE(NEW.goods_services_tax, 0)
    );
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER calculate_total_revenue_on_insert
    BEFORE INSERT ON financial_records
    FOR EACH ROW
    EXECUTE FUNCTION calculate_total_revenue();

CREATE TRIGGER calculate_total_revenue_on_update
    BEFORE UPDATE ON financial_records
    FOR EACH ROW
    EXECUTE FUNCTION calculate_total_revenue();
```

### Example 3: Auto-Calculate `financial_records.total_expenditures`

#### For MySQL/MariaDB:

```sql
DELIMITER $$
CREATE TRIGGER calculate_total_expenditures
BEFORE INSERT ON financial_records
FOR EACH ROW
BEGIN
    SET NEW.total_expenditures = (
        COALESCE(NEW.personnel_services, 0) +
        COALESCE(NEW.maintenance_operating_expenses, 0) +
        COALESCE(NEW.capital_outlay, 0)
    );
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER calculate_total_expenditures_update
BEFORE UPDATE ON financial_records
FOR EACH ROW
BEGIN
    SET NEW.total_expenditures = (
        COALESCE(NEW.personnel_services, 0) +
        COALESCE(NEW.maintenance_operating_expenses, 0) +
        COALESCE(NEW.capital_outlay, 0)
    );
END$$
DELIMITER ;
```

#### For PostgreSQL:

```sql
CREATE OR REPLACE FUNCTION calculate_total_expenditures()
RETURNS TRIGGER AS $$
BEGIN
    NEW.total_expenditures := (
        COALESCE(NEW.personnel_services, 0) +
        COALESCE(NEW.maintenance_operating_expenses, 0) +
        COALESCE(NEW.capital_outlay, 0)
    );
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER calculate_total_expenditures_on_insert
    BEFORE INSERT ON financial_records
    FOR EACH ROW
    EXECUTE FUNCTION calculate_total_expenditures();

CREATE TRIGGER calculate_total_expenditures_on_update
    BEFORE UPDATE ON financial_records
    FOR EACH ROW
    EXECUTE FUNCTION calculate_total_expenditures();
```

### Example 4: Auto-Calculate `financial_records.net_equity`

#### For MySQL/MariaDB:

```sql
DELIMITER $$
CREATE TRIGGER calculate_net_equity
BEFORE INSERT ON financial_records
FOR EACH ROW
BEGIN
    SET NEW.net_equity = COALESCE(NEW.total_assets, 0) - COALESCE(NEW.total_liabilities, 0);
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER calculate_net_equity_update
BEFORE UPDATE ON financial_records
FOR EACH ROW
BEGIN
    SET NEW.net_equity = COALESCE(NEW.total_assets, 0) - COALESCE(NEW.total_liabilities, 0);
END$$
DELIMITER ;
```

#### For PostgreSQL:

```sql
CREATE OR REPLACE FUNCTION calculate_net_equity()
RETURNS TRIGGER AS $$
BEGIN
    NEW.net_equity := COALESCE(NEW.total_assets, 0) - COALESCE(NEW.total_liabilities, 0);
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER calculate_net_equity_on_insert
    BEFORE INSERT ON financial_records
    FOR EACH ROW
    EXECUTE FUNCTION calculate_net_equity();

CREATE TRIGGER calculate_net_equity_on_update
    BEFORE UPDATE ON financial_records
    FOR EACH ROW
    EXECUTE FUNCTION calculate_net_equity();
```

---

## Complete Migration Example

Here's a complete Laravel migration file that creates triggers:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'mysql' || $driver === 'mariadb') {
            $this->createMySQLTriggers();
        } elseif ($driver === 'pgsql') {
            $this->createPostgreSQLTriggers();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'mysql' || $driver === 'mariadb') {
            $this->dropMySQLTriggers();
        } elseif ($driver === 'pgsql') {
            $this->dropPostgreSQLTriggers();
        }
    }

    private function createMySQLTriggers(): void
    {
        // Drop existing triggers if they exist
        DB::unprepared('DROP TRIGGER IF EXISTS update_project_amount_spent_on_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS update_project_amount_spent_on_update');
        DB::unprepared('DROP TRIGGER IF EXISTS update_project_amount_spent_on_delete');

        // Insert trigger
        DB::unprepared("
            CREATE TRIGGER update_project_amount_spent_on_insert
            AFTER INSERT ON transactions
            FOR EACH ROW
            BEGIN
                IF NEW.type = 'expense' THEN
                    UPDATE projects 
                    SET amount_spent = amount_spent + NEW.amount
                    WHERE id = NEW.project_id;
                ELSEIF NEW.type = 'income' THEN
                    UPDATE projects 
                    SET amount_spent = GREATEST(0, amount_spent - NEW.amount)
                    WHERE id = NEW.project_id;
                END IF;
            END
        ");

        // Update trigger
        DB::unprepared("
            CREATE TRIGGER update_project_amount_spent_on_update
            AFTER UPDATE ON transactions
            FOR EACH ROW
            BEGIN
                IF OLD.type = 'expense' THEN
                    UPDATE projects 
                    SET amount_spent = GREATEST(0, amount_spent - OLD.amount)
                    WHERE id = OLD.project_id;
                ELSEIF OLD.type = 'income' THEN
                    UPDATE projects 
                    SET amount_spent = amount_spent + OLD.amount
                    WHERE id = OLD.project_id;
                END IF;
                
                IF NEW.type = 'expense' THEN
                    UPDATE projects 
                    SET amount_spent = amount_spent + NEW.amount
                    WHERE id = NEW.project_id;
                ELSEIF NEW.type = 'income' THEN
                    UPDATE projects 
                    SET amount_spent = GREATEST(0, amount_spent - NEW.amount)
                    WHERE id = NEW.project_id;
                END IF;
            END
        ");

        // Delete trigger
        DB::unprepared("
            CREATE TRIGGER update_project_amount_spent_on_delete
            AFTER DELETE ON transactions
            FOR EACH ROW
            BEGIN
                IF OLD.type = 'expense' THEN
                    UPDATE projects 
                    SET amount_spent = GREATEST(0, amount_spent - OLD.amount)
                    WHERE id = OLD.project_id;
                ELSEIF OLD.type = 'income' THEN
                    UPDATE projects 
                    SET amount_spent = amount_spent + OLD.amount
                    WHERE id = OLD.project_id;
                END IF;
            END
        ");
    }

    private function createPostgreSQLTriggers(): void
    {
        // Drop existing functions and triggers
        DB::unprepared('DROP TRIGGER IF EXISTS update_project_amount_spent_on_insert ON transactions');
        DB::unprepared('DROP TRIGGER IF EXISTS update_project_amount_spent_on_update ON transactions');
        DB::unprepared('DROP TRIGGER IF EXISTS update_project_amount_spent_on_delete ON transactions');
        DB::unprepared('DROP FUNCTION IF EXISTS update_project_amount_spent_on_insert()');
        DB::unprepared('DROP FUNCTION IF EXISTS update_project_amount_spent_on_update()');
        DB::unprepared('DROP FUNCTION IF EXISTS update_project_amount_spent_on_delete()');

        // Insert function and trigger
        DB::unprepared("
            CREATE OR REPLACE FUNCTION update_project_amount_spent_on_insert()
            RETURNS TRIGGER AS \$\$
            BEGIN
                IF NEW.type = 'expense' THEN
                    UPDATE projects 
                    SET amount_spent = amount_spent + NEW.amount
                    WHERE id = NEW.project_id;
                ELSIF NEW.type = 'income' THEN
                    UPDATE projects 
                    SET amount_spent = GREATEST(0, amount_spent - NEW.amount)
                    WHERE id = NEW.project_id;
                END IF;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;

            CREATE TRIGGER update_project_amount_spent_on_insert
                AFTER INSERT ON transactions
                FOR EACH ROW
                EXECUTE FUNCTION update_project_amount_spent_on_insert();
        ");

        // Update function and trigger
        DB::unprepared("
            CREATE OR REPLACE FUNCTION update_project_amount_spent_on_update()
            RETURNS TRIGGER AS \$\$
            BEGIN
                IF OLD.type = 'expense' THEN
                    UPDATE projects 
                    SET amount_spent = GREATEST(0, amount_spent - OLD.amount)
                    WHERE id = OLD.project_id;
                ELSIF OLD.type = 'income' THEN
                    UPDATE projects 
                    SET amount_spent = amount_spent + OLD.amount
                    WHERE id = OLD.project_id;
                END IF;
                
                IF NEW.type = 'expense' THEN
                    UPDATE projects 
                    SET amount_spent = amount_spent + NEW.amount
                    WHERE id = NEW.project_id;
                ELSIF NEW.type = 'income' THEN
                    UPDATE projects 
                    SET amount_spent = GREATEST(0, amount_spent - NEW.amount)
                    WHERE id = NEW.project_id;
                END IF;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;

            CREATE TRIGGER update_project_amount_spent_on_update
                AFTER UPDATE ON transactions
                FOR EACH ROW
                EXECUTE FUNCTION update_project_amount_spent_on_update();
        ");

        // Delete function and trigger
        DB::unprepared("
            CREATE OR REPLACE FUNCTION update_project_amount_spent_on_delete()
            RETURNS TRIGGER AS \$\$
            BEGIN
                IF OLD.type = 'expense' THEN
                    UPDATE projects 
                    SET amount_spent = GREATEST(0, amount_spent - OLD.amount)
                    WHERE id = OLD.project_id;
                ELSIF OLD.type = 'income' THEN
                    UPDATE projects 
                    SET amount_spent = amount_spent + OLD.amount
                    WHERE id = OLD.project_id;
                END IF;
                RETURN OLD;
            END;
            \$\$ LANGUAGE plpgsql;

            CREATE TRIGGER update_project_amount_spent_on_delete
                AFTER DELETE ON transactions
                FOR EACH ROW
                EXECUTE FUNCTION update_project_amount_spent_on_delete();
        ");
    }

    private function dropMySQLTriggers(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS update_project_amount_spent_on_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS update_project_amount_spent_on_update');
        DB::unprepared('DROP TRIGGER IF EXISTS update_project_amount_spent_on_delete');
    }

    private function dropPostgreSQLTriggers(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS update_project_amount_spent_on_insert ON transactions');
        DB::unprepared('DROP TRIGGER IF EXISTS update_project_amount_spent_on_update ON transactions');
        DB::unprepared('DROP TRIGGER IF EXISTS update_project_amount_spent_on_delete ON transactions');
        DB::unprepared('DROP FUNCTION IF EXISTS update_project_amount_spent_on_insert()');
        DB::unprepared('DROP FUNCTION IF EXISTS update_project_amount_spent_on_update()');
        DB::unprepared('DROP FUNCTION IF EXISTS update_project_amount_spent_on_delete()');
    }
};
```

---

## Important Considerations

### 1. **Remove Manual Updates from Controllers**

If you implement triggers, you should **remove** the manual update logic from `TransactionController.php`:

```php
// REMOVE THIS from store() method:
$project->increment('amount_spent', $validated['amount']);

// REMOVE THIS from update() method:
$transaction->project->increment('amount_spent', $validated['amount']);

// REMOVE THIS from destroy() method:
$transaction->project->decrement('amount_spent', $amount);
```

The triggers will handle this automatically!

### 2. **Testing Triggers**

After creating triggers, test them:

```php
// Test in Tinker
php artisan tinker

// Create a transaction
$project = Project::first();
$transaction = Transaction::create([
    'project_id' => $project->id,
    'amount' => 1000,
    'type' => 'expense',
    'transaction_date' => now(),
]);

// Check if amount_spent was updated
$project->refresh();
echo $project->amount_spent; // Should be increased by 1000
```

### 3. **Database Compatibility**

- **MySQL 5.7+**: Full trigger support
- **MariaDB 10.2+**: Full trigger support
- **PostgreSQL 9.1+**: Full trigger support
- **SQLite**: Limited trigger support (not recommended for complex triggers)

### 4. **Performance**

Triggers execute at the database level, which is generally faster than application-level updates. However, be mindful of:
- Trigger execution time
- Potential for trigger cascades
- Lock contention on frequently updated tables

### 5. **Debugging Triggers**

To see if triggers are working:

```sql
-- MySQL
SHOW TRIGGERS;

-- PostgreSQL
SELECT * FROM pg_trigger WHERE tgname LIKE '%amount_spent%';
```

---

## When to Use Triggers vs. Model Events

**Use Triggers When:**
- Data integrity must be maintained even if data is modified outside Laravel
- Performance is critical (database-level is faster)
- You want to ensure consistency across all access methods

**Use Model Events When:**
- You need Laravel-specific features (relationships, accessors, etc.)
- You want easier debugging and testing
- You need more complex business logic

**Best Practice:** You can use both! Triggers for critical data integrity, Model Events for business logic.

---

## Next Steps

1. Create the migration file using the example above
2. Test the triggers in a development environment
3. Remove manual update logic from controllers
4. Update your ERD to note which fields are maintained by triggers
5. Document triggers in your codebase

---

## Summary

✅ **Yes, triggers are possible in Laravel!**
✅ Use migrations with `DB::unprepared()` for raw SQL
✅ Support both MySQL and PostgreSQL
✅ Automate data consistency and reduce controller code
✅ Test thoroughly before deploying to production

