// Image gallery functionality
document.addEventListener('DOMContentLoaded', function() {
    // Handle thumbnail clicks for vehicle galleries
    document.querySelectorAll('.thumbnail-container').forEach(container => {
        const thumbnails = container.querySelectorAll('.thumbnail');
        const primaryImage = container.previousElementSibling.querySelector('img');
        
        if (thumbnails && primaryImage) {
            thumbnails.forEach(thumb => {
                thumb.addEventListener('click', function() {
                    // Update active thumbnail
                    container.querySelector('.thumbnail.active')?.classList.remove('active');
                    this.classList.add('active');
                    
                    // Update primary image
                    const imgSrc = this.querySelector('img').src;
                    primaryImage.src = imgSrc;
                });
            });
        }
    });
    
    // Form validation for registration
    const registerForm = document.querySelector('.auth-container form');
    if (registerForm && registerForm.action.includes('register.php')) {
        registerForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
        });
    }
    
    // Contact seller button functionality
    document.querySelectorAll('.btn-contact').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const sellerInfo = this.closest('.seller-info');
            const phone = sellerInfo.querySelector('p:nth-child(2)').textContent.replace('Phone: ', '');
            const name = sellerInfo.querySelector('p:first-child').textContent.replace('Name: ', '');
            
            if (confirm(`Contact ${name} at ${phone}?`)) {
                window.location.href = `tel:${phone}`;
            }
        });
    });
    
    // Dynamic model dropdown for buy page
    const makeSelect = document.getElementById('make');
    if (makeSelect) {
        makeSelect.addEventListener('change', function() {
            const modelSelect = document.getElementById('model');
            const make = this.value;
            
            if (make) {
                // Clear existing options except the first one
                while (modelSelect.options.length > 1) {
                    modelSelect.remove(1);
                }
                
                // Fetch models for the selected make
                fetch(`/vehicle_marketplace/includes/get_models.php?make=${encodeURIComponent(make)}`)
                    .then(response => response.json())
                    .then(models => {
                        models.forEach(model => {
                            const option = document.createElement('option');
                            option.value = model;
                            option.textContent = model;
                            modelSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error fetching models:', error));
            } else {
                // Clear all options except the first one
                while (modelSelect.options.length > 1) {
                    modelSelect.remove(1);
                }
            }
        });
    }
    
    // Initialize make dropdown if there's a selected make
    if (makeSelect && makeSelect.value) {
        makeSelect.dispatchEvent(new Event('change'));
    }
});