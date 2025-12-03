# Frontend Guide: Handling Database Triggers

## Overview

After implementing database triggers, your frontend needs to handle automatic data updates. Triggers automatically update related fields (like `projects.amount_spent` and `financial_records.total_revenue`) when transactions or financial records are created, updated, or deleted.

## Key Changes Required

### 1. **Refetch Related Data After Mutations**

Since triggers update data automatically at the database level, you need to **refetch related entities** after mutations to get the updated values.

---

## Transaction Operations

### Creating a Transaction

**Before (without triggers):**
```javascript
// ❌ OLD WAY - Manual update
const createTransaction = async (transactionData) => {
  const response = await api.post('/transactions', transactionData);
  // Backend manually updated project.amount_spent
  // Frontend might have cached project data that's now stale
  return response.data;
};
```

**After (with triggers):**
```javascript
// ✅ NEW WAY - Refetch project after creation
const createTransaction = async (transactionData) => {
  const response = await api.post('/transactions', transactionData);
  
  // IMPORTANT: Refetch the project to get updated amount_spent
  const projectResponse = await api.get(`/projects/${transactionData.project_id}`);
  
  // Update your state/cache with the new project data
  updateProjectInState(projectResponse.data.data);
  
  return response.data;
};
```

### Updating a Transaction

```javascript
// ✅ Refetch project after update
const updateTransaction = async (transactionId, transactionData) => {
  const response = await api.put(`/transactions/${transactionId}`, transactionData);
  
  // Get the project_id from the updated transaction
  const projectId = response.data.data.project_id;
  
  // Refetch project to get updated amount_spent
  const projectResponse = await api.get(`/projects/${projectId}`);
  updateProjectInState(projectResponse.data.data);
  
  return response.data;
};
```

### Deleting a Transaction

```javascript
// ✅ Refetch project after deletion
const deleteTransaction = async (transactionId) => {
  // First, get the transaction to know which project to refetch
  const transaction = await api.get(`/transactions/${transactionId}`);
  const projectId = transaction.data.data.project_id;
  
  // Delete the transaction
  await api.delete(`/transactions/${transactionId}`);
  
  // Refetch project to get updated amount_spent
  const projectResponse = await api.get(`/projects/${projectId}`);
  updateProjectInState(projectResponse.data.data);
};
```

---

## Financial Record Operations

### Creating/Updating Financial Records

```javascript
// ✅ Refetch financial record after mutation
const saveFinancialRecord = async (recordData) => {
  let response;
  
  if (recordData.id) {
    // Update
    response = await api.put(`/financial-records/${recordData.id}`, recordData);
  } else {
    // Create
    response = await api.post('/financial-records', recordData);
  }
  
  // Refetch to get calculated totals (total_revenue, total_expenditures, net_equity)
  const updatedRecord = await api.get(`/financial-records/${response.data.data.id}`);
  updateFinancialRecordInState(updatedRecord.data.data);
  
  return updatedRecord.data;
};
```

---

## React Example (Using React Query / TanStack Query)

### Setup with React Query

```javascript
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { api } from './api';

// Hook for creating transactions
export const useCreateTransaction = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: async (transactionData) => {
      const response = await api.post('/transactions', transactionData);
      return response.data;
    },
    onSuccess: async (data, variables) => {
      // Invalidate and refetch project data
      await queryClient.invalidateQueries({
        queryKey: ['projects', variables.project_id]
      });
      
      // Also invalidate transactions list
      queryClient.invalidateQueries({ queryKey: ['transactions'] });
      
      // Optionally refetch contractor if needed
      if (data.data.project?.contractor_id) {
        queryClient.invalidateQueries({
          queryKey: ['contractors', data.data.project.contractor_id]
        });
      }
    },
  });
};

// Hook for updating transactions
export const useUpdateTransaction = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: async ({ id, ...transactionData }) => {
      const response = await api.put(`/transactions/${id}`, transactionData);
      return { ...response.data, transactionId: id };
    },
    onSuccess: async (data) => {
      const projectId = data.data.project_id;
      
      // Refetch project to get updated amount_spent
      await queryClient.invalidateQueries({
        queryKey: ['projects', projectId]
      });
      
      // Invalidate transactions
      queryClient.invalidateQueries({ queryKey: ['transactions'] });
    },
  });
};

// Hook for deleting transactions
export const useDeleteTransaction = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: async (transactionId) => {
      // First get the transaction to know which project to refetch
      const transaction = await api.get(`/transactions/${transactionId}`);
      const projectId = transaction.data.data.project_id;
      
      await api.delete(`/transactions/${transactionId}`);
      return { transactionId, projectId };
    },
    onSuccess: async ({ projectId }) => {
      // Refetch project to get updated amount_spent
      await queryClient.invalidateQueries({
        queryKey: ['projects', projectId]
      });
      
      // Invalidate transactions list
      queryClient.invalidateQueries({ queryKey: ['transactions'] });
    },
  });
};

// Hook for financial records
export const useSaveFinancialRecord = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: async (recordData) => {
      let response;
      if (recordData.id) {
        response = await api.put(`/financial-records/${recordData.id}`, recordData);
      } else {
        response = await api.post('/financial-records', recordData);
      }
      return response.data;
    },
    onSuccess: async (data) => {
      const recordId = data.data.id;
      
      // Refetch the financial record to get calculated totals
      await queryClient.invalidateQueries({
        queryKey: ['financial-records', recordId]
      });
      
      // Also invalidate the list
      queryClient.invalidateQueries({ queryKey: ['financial-records'] });
    },
  });
};
```

### Usage in Components

```jsx
import { useCreateTransaction, useUpdateTransaction, useDeleteTransaction } from './hooks/useTransactions';
import { useQuery } from '@tanstack/react-query';

function TransactionForm({ projectId, onSuccess }) {
  const createTransaction = useCreateTransaction();
  const { data: project } = useQuery({
    queryKey: ['projects', projectId],
    queryFn: () => api.get(`/projects/${projectId}`).then(res => res.data.data)
  });
  
  const handleSubmit = async (formData) => {
    try {
      await createTransaction.mutateAsync({
        ...formData,
        project_id: projectId
      });
      
      // Project data will be automatically refetched by React Query
      onSuccess?.();
    } catch (error) {
      console.error('Failed to create transaction:', error);
    }
  };
  
  return (
    <form onSubmit={handleSubmit}>
      {/* Form fields */}
      <button type="submit" disabled={createTransaction.isPending}>
        {createTransaction.isPending ? 'Creating...' : 'Create Transaction'}
      </button>
      
      {/* Show updated project amount_spent */}
      {project && (
        <div>
          <p>Amount Spent: {project.amount_spent}</p>
          <p>Remaining Budget: {project.budget_allocated - project.amount_spent}</p>
        </div>
      )}
    </form>
  );
}
```

---

## Vue 3 Example (Using Pinia / Vue Query)

### Using Pinia Store

```javascript
// stores/transactionStore.js
import { defineStore } from 'pinia';
import { api } from '@/api';

export const useTransactionStore = defineStore('transactions', {
  state: () => ({
    transactions: [],
    loading: false,
  }),
  
  actions: {
    async createTransaction(transactionData) {
      this.loading = true;
      try {
        const response = await api.post('/transactions', transactionData);
        
        // IMPORTANT: Refetch project after creation
        await this.$projectStore.fetchProject(transactionData.project_id);
        
        // Add transaction to list
        this.transactions.push(response.data.data);
        
        return response.data;
      } catch (error) {
        console.error('Failed to create transaction:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    },
    
    async updateTransaction(transactionId, transactionData) {
      this.loading = true;
      try {
        const response = await api.put(`/transactions/${transactionId}`, transactionData);
        const projectId = response.data.data.project_id;
        
        // Refetch project to get updated amount_spent
        await this.$projectStore.fetchProject(projectId);
        
        // Update transaction in list
        const index = this.transactions.findIndex(t => t.id === transactionId);
        if (index !== -1) {
          this.transactions[index] = response.data.data;
        }
        
        return response.data;
      } catch (error) {
        console.error('Failed to update transaction:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    },
    
    async deleteTransaction(transactionId) {
      this.loading = true;
      try {
        // Get transaction first to know which project to refetch
        const transaction = await api.get(`/transactions/${transactionId}`);
        const projectId = transaction.data.data.project_id;
        
        await api.delete(`/transactions/${transactionId}`);
        
        // Refetch project to get updated amount_spent
        await this.$projectStore.fetchProject(projectId);
        
        // Remove from list
        this.transactions = this.transactions.filter(t => t.id !== transactionId);
      } catch (error) {
        console.error('Failed to delete transaction:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    },
  },
});
```

---

## Important Considerations

### 1. **Optimistic Updates**

If you're using optimistic updates, be careful! The trigger will update the database, so your optimistic update might conflict:

```javascript
// ⚠️ Be careful with optimistic updates
const createTransaction = async (transactionData) => {
  // Optimistic update
  const optimisticTransaction = {
    ...transactionData,
    id: 'temp-' + Date.now(),
  };
  addTransactionOptimistically(optimisticTransaction);
  
  try {
    const response = await api.post('/transactions', transactionData);
    
    // Replace optimistic transaction with real one
    replaceOptimisticTransaction(optimisticTransaction.id, response.data.data);
    
    // IMPORTANT: Refetch project (trigger updated it)
    await refetchProject(transactionData.project_id);
  } catch (error) {
    // Rollback optimistic update
    removeOptimisticTransaction(optimisticTransaction.id);
    throw error;
  }
};
```

### 2. **Loading States**

Since triggers execute synchronously at the database level, the API response will already include the updated data. However, you still need to refetch related entities:

```javascript
// ✅ Good: Show loading while refetching
const [isRefetching, setIsRefetching] = useState(false);

const handleCreateTransaction = async (data) => {
  setIsRefetching(true);
  try {
    await createTransaction(data);
    await refetchProject(data.project_id); // This might take a moment
  } finally {
    setIsRefetching(false);
  }
};
```

### 3. **Error Handling**

Triggers can fail! Handle errors gracefully:

```javascript
const createTransaction = async (transactionData) => {
  try {
    const response = await api.post('/transactions', transactionData);
    
    // Try to refetch project
    try {
      await refetchProject(transactionData.project_id);
    } catch (refetchError) {
      console.warn('Failed to refetch project, but transaction was created:', refetchError);
      // Show a warning to the user that they may need to refresh
    }
    
    return response.data;
  } catch (error) {
    // Transaction creation failed
    if (error.response?.status === 500) {
      // Could be a trigger error
      console.error('Database trigger may have failed:', error);
    }
    throw error;
  }
};
```

### 4. **Batch Operations**

If you're creating multiple transactions at once, refetch the project once after all operations:

```javascript
const createMultipleTransactions = async (transactions) => {
  const projectId = transactions[0].project_id;
  const results = [];
  
  for (const transactionData of transactions) {
    const response = await api.post('/transactions', transactionData);
    results.push(response.data);
  }
  
  // Refetch project once after all transactions are created
  await refetchProject(projectId);
  
  return results;
};
```

### 5. **Real-time Updates (Optional)**

If you're using WebSockets or Server-Sent Events, you can listen for updates:

```javascript
// Example with WebSockets (Laravel Echo)
Echo.channel(`project.${projectId}`)
  .listen('ProjectUpdated', (e) => {
    // Project was updated (possibly by a trigger)
    updateProjectInState(e.project);
  });
```

---

## API Response Changes

### Transaction Responses

The API responses remain the same, but remember:

```javascript
// POST /transactions response
{
  "message": "Transaction created successfully.",
  "data": {
    "id": 1,
    "project_id": 5,
    "amount": 1000,
    "type": "expense",
    // ... other fields
    "project": {
      "id": 5,
      "amount_spent": 5000, // ⚠️ This might be stale if triggers haven't run yet
      // ...
    }
  }
}
```

**Solution:** Always refetch the project separately to ensure you have the latest `amount_spent`:

```javascript
const response = await api.post('/transactions', transactionData);
const projectResponse = await api.get(`/projects/${transactionData.project_id}`);
// projectResponse.data.data.amount_spent is guaranteed to be up-to-date
```

---

## Testing Checklist

After implementing trigger support in your frontend:

- [ ] Create a transaction and verify project `amount_spent` updates
- [ ] Update a transaction and verify project `amount_spent` updates correctly
- [ ] Delete a transaction and verify project `amount_spent` decreases
- [ ] Create/update financial record and verify calculated totals are correct
- [ ] Test with multiple rapid transactions (race conditions)
- [ ] Test error handling when refetch fails
- [ ] Verify loading states work correctly
- [ ] Test optimistic updates (if used)

---

## Summary

**Key Actions for Frontend:**

1. ✅ **Refetch related entities** after mutations (projects after transactions, financial records after updates)
2. ✅ **Update state/cache** with the refetched data
3. ✅ **Handle loading states** during refetch operations
4. ✅ **Test thoroughly** to ensure data consistency
5. ✅ **Consider real-time updates** for better UX (optional)

**Remember:** Triggers update data at the database level, so your frontend needs to refetch to see the changes!


