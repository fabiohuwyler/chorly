// Toast notification system
const toast = {
    element: null,
    init() {
        this.element = document.getElementById('toast');
        if (!this.element) {
            console.error('Toast element not found');
            return;
        }
    },
    show(message, type = 'success') {
        if (!this.element) {
            this.init();
        }
        
        const toastEl = this.element;
        if (!toastEl) {
            console.error('Toast element not found');
            return;
        }
        
        const icon = toastEl.querySelector('.toast-icon');
        const messageEl = toastEl.querySelector('.toast-message');
        
        // Set icon based on type
        icon.className = 'toast-icon bi';
        if (type === 'success') {
            icon.classList.add('bi-check-circle', 'text-success');
        } else if (type === 'error') {
            icon.classList.add('bi-x-circle', 'text-danger');
        } else if (type === 'warning') {
            icon.classList.add('bi-exclamation-triangle', 'text-warning');
        }
        
        // Set message
        messageEl.textContent = message;
        
        // Show toast
        toastEl.classList.add('show');
        
        // Hide after 3 seconds
        setTimeout(() => {
            toastEl.classList.remove('show');
        }, 3000);
    }
};

// Initialize toast when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    toast.init();
});

// Start a chore
function startChore(choreId) {
    console.log('Starting chore:', choreId); // Debug log
    
    fetch('includes/start_chore.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ chore_id: choreId })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        console.log('Response:', data); // Debug log
        if (data.success) {
            toast.show(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            toast.show(data.message || 'An error occurred', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toast.show('An error occurred while starting the chore', 'error');
    });
}

// Complete a chore
function completeChore(choreId) {
    console.log('Completing chore:', choreId); // Debug log
    
    fetch('includes/complete_chore.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ chore_id: choreId })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        console.log('Response:', data); // Debug log
        if (data.success) {
            toast.show(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            toast.show(data.message || 'An error occurred', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toast.show('An error occurred while completing the chore', 'error');
    });
}

// Delete a chore
function deleteChore(choreId) {
    if (!confirm(document.body.dataset.confirmDelete || 'Are you sure you want to delete this chore?')) {
        return;
    }
    
    console.log('Deleting chore:', choreId); // Debug log
    
    fetch('includes/delete_chore.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ chore_id: choreId })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        console.log('Response:', data); // Debug log
        if (data.success) {
            toast.show(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            toast.show(data.message || 'An error occurred', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toast.show('An error occurred while deleting the chore', 'error');
    });
}

// Handle recurring chore toggle
document.addEventListener('DOMContentLoaded', function() {
    const recurringCheckbox = document.getElementById('is_recurring');
    const intervalInput = document.getElementById('recurring_interval');
    const intervalContainer = document.getElementById('interval_container');
    
    if (recurringCheckbox && intervalContainer) {
        recurringCheckbox.addEventListener('change', function() {
            intervalContainer.style.display = this.checked ? 'block' : 'none';
            if (this.checked) {
                intervalInput.required = true;
                intervalInput.value = intervalInput.value || '7';
            } else {
                intervalInput.required = false;
            }
        });
    }
});
