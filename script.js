document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const appointmentForm = document.getElementById('appointmentForm');
    if(appointmentForm) {
        appointmentForm.addEventListener('submit', function(e) {
            const phone = document.querySelector('input[name="phone"]');
            const carLicense = document.querySelector('input[name="car_license"]');
            const carEngine = document.querySelector('input[name="car_engine"]');
            
            // Phone validation
            if(!/^\d{11}$/.test(phone.value)) {
                e.preventDefault();
                alert('Please enter a valid 11-digit phone number');
                return;
            }
            
            // Car license validation
            if(carLicense.value.length < 5) {
                e.preventDefault();
                alert('Please enter a valid car license number');
                return;
            }
            
            // Car engine validation
            if (carEngine.value.length < 5 || !/^\d+$/.test(carEngine.value)) {
                e.preventDefault();
                alert('Please enter a valid car engine number (at least 5 digits and numbers only)');
                return;
            } 
        });
    }

    // Admin panel functionality
    const editDateInputs = document.querySelectorAll('.edit-appointment-date');
    const editMechanicSelects = document.querySelectorAll('.edit-mechanic');
    const editStatusSelects = document.querySelectorAll('.edit-status');
    const deleteButtons = document.querySelectorAll('.btn-delete');

    // Generic update function with POST fallback
    async function updateAppointment(action, id, value, additionalData = {}) {
        try {
            // Try PUT first
            let response = await fetch('appointment.php', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: action,
                    id: id,
                    ...additionalData
                })
            });

            // If PUT fails, try POST fallback
            if (!response.ok) {
                const formData = new FormData();
                formData.append('action', action);
                formData.append('id', id);
                for (let key in additionalData) {
                    formData.append(key, additionalData[key]);
                }

                response = await fetch('appointment.php', {
                    method: 'POST',
                    body: formData
                });
            }

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            if (!data.success) {
                throw new Error(data.message || 'Operation failed');
            }

            return data;
        } catch (error) {
            throw error;
        }
    }

    // Update appointment date
    editDateInputs.forEach(input => {
        input.addEventListener('change', async function() {
            try {
                await updateAppointment('update_date', this.dataset.id, this.value, {
                    date: this.value
                });
                showNotification('Date updated successfully');
            } catch(error) {
                console.error('Error:', error);
                showNotification(`Error updating date: ${error.message}`, 'error');
            }
        });
    });

    // Update mechanic
    editMechanicSelects.forEach(select => {
        select.addEventListener('change', async function() {
            try {
                await updateAppointment('update_mechanic', this.dataset.id, this.value, {
                    mechanic_id: this.value
                });
                showNotification('Mechanic updated successfully');
            } catch(error) {
                console.error('Error:', error);
                showNotification(`Error updating mechanic: ${error.message}`, 'error');
            }
        });
    });

    // Update status
    editStatusSelects.forEach(select => {
        select.addEventListener('change', async function() {
            try {
                await updateAppointment('update_status', this.dataset.id, this.value, {
                    status: this.value
                });
                showNotification('Status updated successfully');
            } catch(error) {
                console.error('Error:', error);
                showNotification(`Error updating status: ${error.message}`, 'error');
            }
        });
    });

    // Delete appointment
    deleteButtons.forEach(button => {
        button.addEventListener('click', async function() {
            if(!confirm('Are you sure you want to delete this appointment?')) {
                return;
            }

            const row = this.closest('tr');
            try {
                // Try DELETE first
                let response = await fetch('appointment.php', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: this.dataset.id
                    })
                });

                // If DELETE fails, try POST fallback
                if (!response.ok) {
                    const formData = new FormData();
                    formData.append('action', 'delete');
                    formData.append('id', this.dataset.id);

                    response = await fetch('appointment.php', {
                        method: 'POST',
                        body: formData
                    });
                }

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                if (!data.success) {
                    throw new Error(data.message || 'Delete failed');
                }

                row.remove();
                showNotification('Appointment deleted successfully');
            } catch(error) {
                console.error('Error:', error);
                showNotification(`Error deleting appointment: ${error.message}`, 'error');
            }
        });
    });

    // Enhanced notification function with better visibility
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} fade-in`;
        notification.style.position = 'fixed';
        notification.style.top = '20px';
        notification.style.right = '20px';
        notification.style.zIndex = '1000';
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('fade-out');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }

    // Add animation classes to elements
    document.querySelectorAll('.section').forEach(section => {
        section.classList.add('fade-in');
    });
});

document.getElementById('refreshData').addEventListener('click', function() {
    const status = document.getElementById('statusFilter').value;
    const urlParams = new URLSearchParams(window.location.search);
    
    if (status) {
        urlParams.set('status', status);
    } else {
        urlParams.delete('status');
    }

    // Reload the page with the updated URL
    window.location.href = window.location.pathname + '?' + urlParams.toString();
});

// Ensure the correct filter is selected on page load
window.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    
    if (status) {
        document.getElementById('statusFilter').value = status;
    }
});