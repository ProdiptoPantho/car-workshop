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
            if(carEngine.value.length < 5) {
                e.preventDefault();
                alert('Please enter a valid car engine number');
                return;
            }
        });
    }

    // Admin panel functionality
    const editDateInputs = document.querySelectorAll('.edit-appointment-date');
    const editMechanicSelects = document.querySelectorAll('.edit-mechanic');
    const editStatusSelects = document.querySelectorAll('.edit-status');
    const deleteButtons = document.querySelectorAll('.btn-delete');

    // Update appointment date
    editDateInputs.forEach(input => {
        input.addEventListener('change', async function() {
            const id = this.dataset.id;
            const date = this.value;
            
            try {
                const response = await fetch('appointment.php', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'update_date',
                        id: id,
                        date: date
                    })
                });
                
                const data = await response.json();
                if(data.success) {
                    showNotification('Date updated successfully');
                }
            } catch(error) {
                console.error('Error:', error);
                showNotification('Error updating date', 'error');
            }
        });
    });

    // Update mechanic
    editMechanicSelects.forEach(select => {
        select.addEventListener('change', async function() {
            const id = this.dataset.id;
            const mechanicId = this.value;
            
            try {
                const response = await fetch('appointment.php', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'update_mechanic',
                        id: id,
                        mechanic_id: mechanicId
                    })
                });
                
                const data = await response.json();
                if(data.success) {
                    showNotification('Mechanic updated successfully');
                }
            } catch(error) {
                console.error('Error:', error);
                showNotification('Error updating mechanic', 'error');
            }
        });
    });

    // Update status
    editStatusSelects.forEach(select => {
        select.addEventListener('change', async function() {
            const id = this.dataset.id;
            const status = this.value;
            
            try {
                const response = await fetch('appointment.php', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'update_status',
                        id: id,
                        status: status
                    })
                });
                
                const data = await response.json();
                if(data.success) {
                    showNotification('Status updated successfully');
                }
            } catch(error) {
                console.error('Error:', error);
                showNotification('Error updating status', 'error');
            }
        });
    });

    // Delete appointment
    deleteButtons.forEach(button => {
        button.addEventListener('click', async function() {
            if(confirm('Are you sure you want to delete this appointment?')) {
                const id = this.dataset.id;
                
                try {
                    const response = await fetch('appointment.php', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id: id
                        })
                    });
                    
                    const data = await response.json();
                    if(data.success) {
                        this.closest('tr').remove();
                        showNotification('Appointment deleted successfully');
                    }
                } catch(error) {
                    console.error('Error:', error);
                    showNotification('Error deleting appointment', 'error');
                }
            }
        });
    });

    // Notification function
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} fade-in`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    // Add animation classes to elements
    document.querySelectorAll('.section').forEach(section => {
        section.classList.add('fade-in');
    });
});