<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates database triggers to automatically maintain:
     * - projects.amount_spent when transactions are created/updated/deleted
     * - financial_records.total_revenue when revenue components change
     * - financial_records.total_expenditures when expenditure components change
     * - financial_records.net_equity when assets/liabilities change
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

    /**
     * Create triggers for MySQL/MariaDB
     */
    private function createMySQLTriggers(): void
    {
        // ============================================
        // Triggers for projects.amount_spent
        // ============================================
        
        // Drop existing triggers if they exist
        DB::unprepared('DROP TRIGGER IF EXISTS update_project_amount_spent_on_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS update_project_amount_spent_on_update');
        DB::unprepared('DROP TRIGGER IF EXISTS update_project_amount_spent_on_delete');

        // Trigger: Update amount_spent when transaction is inserted
        DB::unprepared("
            CREATE TRIGGER update_project_amount_spent_on_insert
            AFTER INSERT ON transactions
            FOR EACH ROW
            BEGIN
                IF NEW.type = 'expense' OR NEW.type IS NULL THEN
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

        // Trigger: Update amount_spent when transaction is updated
        DB::unprepared("
            CREATE TRIGGER update_project_amount_spent_on_update
            AFTER UPDATE ON transactions
            FOR EACH ROW
            BEGIN
                -- Revert old transaction impact
                IF OLD.type = 'expense' OR OLD.type IS NULL THEN
                    UPDATE projects 
                    SET amount_spent = GREATEST(0, amount_spent - OLD.amount)
                    WHERE id = OLD.project_id;
                ELSEIF OLD.type = 'income' THEN
                    UPDATE projects 
                    SET amount_spent = amount_spent + OLD.amount
                    WHERE id = OLD.project_id;
                END IF;
                
                -- Apply new transaction impact
                IF NEW.type = 'expense' OR NEW.type IS NULL THEN
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

        // Trigger: Update amount_spent when transaction is deleted
        DB::unprepared("
            CREATE TRIGGER update_project_amount_spent_on_delete
            AFTER DELETE ON transactions
            FOR EACH ROW
            BEGIN
                IF OLD.type = 'expense' OR OLD.type IS NULL THEN
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

        // ============================================
        // Triggers for financial_records.total_revenue
        // ============================================
        
        DB::unprepared('DROP TRIGGER IF EXISTS calculate_total_revenue_on_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS calculate_total_revenue_on_update');

        DB::unprepared("
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
            END
        ");

        DB::unprepared("
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
            END
        ");

        // ============================================
        // Triggers for financial_records.total_expenditures
        // ============================================
        
        DB::unprepared('DROP TRIGGER IF EXISTS calculate_total_expenditures_on_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS calculate_total_expenditures_on_update');

        DB::unprepared("
            CREATE TRIGGER calculate_total_expenditures_on_insert
            BEFORE INSERT ON financial_records
            FOR EACH ROW
            BEGIN
                SET NEW.total_expenditures = (
                    COALESCE(NEW.personnel_services, 0) +
                    COALESCE(NEW.maintenance_operating_expenses, 0) +
                    COALESCE(NEW.capital_outlay, 0)
                );
            END
        ");

        DB::unprepared("
            CREATE TRIGGER calculate_total_expenditures_on_update
            BEFORE UPDATE ON financial_records
            FOR EACH ROW
            BEGIN
                SET NEW.total_expenditures = (
                    COALESCE(NEW.personnel_services, 0) +
                    COALESCE(NEW.maintenance_operating_expenses, 0) +
                    COALESCE(NEW.capital_outlay, 0)
                );
            END
        ");

        // ============================================
        // Triggers for financial_records.net_equity
        // ============================================
        
        DB::unprepared('DROP TRIGGER IF EXISTS calculate_net_equity_on_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS calculate_net_equity_on_update');

        DB::unprepared("
            CREATE TRIGGER calculate_net_equity_on_insert
            BEFORE INSERT ON financial_records
            FOR EACH ROW
            BEGIN
                SET NEW.net_equity = COALESCE(NEW.total_assets, 0) - COALESCE(NEW.total_liabilities, 0);
            END
        ");

        DB::unprepared("
            CREATE TRIGGER calculate_net_equity_on_update
            BEFORE UPDATE ON financial_records
            FOR EACH ROW
            BEGIN
                SET NEW.net_equity = COALESCE(NEW.total_assets, 0) - COALESCE(NEW.total_liabilities, 0);
            END
        ");
    }

    /**
     * Create triggers for PostgreSQL
     */
    private function createPostgreSQLTriggers(): void
    {
        // ============================================
        // Triggers for projects.amount_spent
        // ============================================
        
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
                IF NEW.type = 'expense' OR NEW.type IS NULL THEN
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
                -- Revert old transaction impact
                IF OLD.type = 'expense' OR OLD.type IS NULL THEN
                    UPDATE projects 
                    SET amount_spent = GREATEST(0, amount_spent - OLD.amount)
                    WHERE id = OLD.project_id;
                ELSIF OLD.type = 'income' THEN
                    UPDATE projects 
                    SET amount_spent = amount_spent + OLD.amount
                    WHERE id = OLD.project_id;
                END IF;
                
                -- Apply new transaction impact
                IF NEW.type = 'expense' OR NEW.type IS NULL THEN
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
                IF OLD.type = 'expense' OR OLD.type IS NULL THEN
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

        // ============================================
        // Triggers for financial_records.total_revenue
        // ============================================
        
        DB::unprepared('DROP TRIGGER IF EXISTS calculate_total_revenue_on_insert ON financial_records');
        DB::unprepared('DROP TRIGGER IF EXISTS calculate_total_revenue_on_update ON financial_records');
        DB::unprepared('DROP FUNCTION IF EXISTS calculate_total_revenue()');

        DB::unprepared("
            CREATE OR REPLACE FUNCTION calculate_total_revenue()
            RETURNS TRIGGER AS \$\$
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
            \$\$ LANGUAGE plpgsql;

            CREATE TRIGGER calculate_total_revenue_on_insert
                BEFORE INSERT ON financial_records
                FOR EACH ROW
                EXECUTE FUNCTION calculate_total_revenue();

            CREATE TRIGGER calculate_total_revenue_on_update
                BEFORE UPDATE ON financial_records
                FOR EACH ROW
                EXECUTE FUNCTION calculate_total_revenue();
        ");

        // ============================================
        // Triggers for financial_records.total_expenditures
        // ============================================
        
        DB::unprepared('DROP TRIGGER IF EXISTS calculate_total_expenditures_on_insert ON financial_records');
        DB::unprepared('DROP TRIGGER IF EXISTS calculate_total_expenditures_on_update ON financial_records');
        DB::unprepared('DROP FUNCTION IF EXISTS calculate_total_expenditures()');

        DB::unprepared("
            CREATE OR REPLACE FUNCTION calculate_total_expenditures()
            RETURNS TRIGGER AS \$\$
            BEGIN
                NEW.total_expenditures := (
                    COALESCE(NEW.personnel_services, 0) +
                    COALESCE(NEW.maintenance_operating_expenses, 0) +
                    COALESCE(NEW.capital_outlay, 0)
                );
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;

            CREATE TRIGGER calculate_total_expenditures_on_insert
                BEFORE INSERT ON financial_records
                FOR EACH ROW
                EXECUTE FUNCTION calculate_total_expenditures();

            CREATE TRIGGER calculate_total_expenditures_on_update
                BEFORE UPDATE ON financial_records
                FOR EACH ROW
                EXECUTE FUNCTION calculate_total_expenditures();
        ");

        // ============================================
        // Triggers for financial_records.net_equity
        // ============================================
        
        DB::unprepared('DROP TRIGGER IF EXISTS calculate_net_equity_on_insert ON financial_records');
        DB::unprepared('DROP TRIGGER IF EXISTS calculate_net_equity_on_update ON financial_records');
        DB::unprepared('DROP FUNCTION IF EXISTS calculate_net_equity()');

        DB::unprepared("
            CREATE OR REPLACE FUNCTION calculate_net_equity()
            RETURNS TRIGGER AS \$\$
            BEGIN
                NEW.net_equity := COALESCE(NEW.total_assets, 0) - COALESCE(NEW.total_liabilities, 0);
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;

            CREATE TRIGGER calculate_net_equity_on_insert
                BEFORE INSERT ON financial_records
                FOR EACH ROW
                EXECUTE FUNCTION calculate_net_equity();

            CREATE TRIGGER calculate_net_equity_on_update
                BEFORE UPDATE ON financial_records
                FOR EACH ROW
                EXECUTE FUNCTION calculate_net_equity();
        ");
    }

    /**
     * Drop MySQL triggers
     */
    private function dropMySQLTriggers(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS update_project_amount_spent_on_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS update_project_amount_spent_on_update');
        DB::unprepared('DROP TRIGGER IF EXISTS update_project_amount_spent_on_delete');
        DB::unprepared('DROP TRIGGER IF EXISTS calculate_total_revenue_on_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS calculate_total_revenue_on_update');
        DB::unprepared('DROP TRIGGER IF EXISTS calculate_total_expenditures_on_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS calculate_total_expenditures_on_update');
        DB::unprepared('DROP TRIGGER IF EXISTS calculate_net_equity_on_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS calculate_net_equity_on_update');
    }

    /**
     * Drop PostgreSQL triggers and functions
     */
    private function dropPostgreSQLTriggers(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS update_project_amount_spent_on_insert ON transactions');
        DB::unprepared('DROP TRIGGER IF EXISTS update_project_amount_spent_on_update ON transactions');
        DB::unprepared('DROP TRIGGER IF EXISTS update_project_amount_spent_on_delete ON transactions');
        DB::unprepared('DROP FUNCTION IF EXISTS update_project_amount_spent_on_insert()');
        DB::unprepared('DROP FUNCTION IF EXISTS update_project_amount_spent_on_update()');
        DB::unprepared('DROP FUNCTION IF EXISTS update_project_amount_spent_on_delete()');
        
        DB::unprepared('DROP TRIGGER IF EXISTS calculate_total_revenue_on_insert ON financial_records');
        DB::unprepared('DROP TRIGGER IF EXISTS calculate_total_revenue_on_update ON financial_records');
        DB::unprepared('DROP FUNCTION IF EXISTS calculate_total_revenue()');
        
        DB::unprepared('DROP TRIGGER IF EXISTS calculate_total_expenditures_on_insert ON financial_records');
        DB::unprepared('DROP TRIGGER IF EXISTS calculate_total_expenditures_on_update ON financial_records');
        DB::unprepared('DROP FUNCTION IF EXISTS calculate_total_expenditures()');
        
        DB::unprepared('DROP TRIGGER IF EXISTS calculate_net_equity_on_insert ON financial_records');
        DB::unprepared('DROP TRIGGER IF EXISTS calculate_net_equity_on_update ON financial_records');
        DB::unprepared('DROP FUNCTION IF EXISTS calculate_net_equity()');
    }
};

