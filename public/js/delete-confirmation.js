// Global SweetAlert2 handler for delete confirmations
document.addEventListener('DOMContentLoaded', function() {
    // Find all delete forms (forms with DELETE method)
    const deleteForms = document.querySelectorAll('form[method="POST"]');
    
    deleteForms.forEach(form => {
        // Check if form has DELETE method
        const methodInput = form.querySelector('input[name="_method"][value="DELETE"]');
        if (!methodInput) return;
        
        // Find the submit button
        const submitButton = form.querySelector('button[type="submit"]');
        if (!submitButton) return;
        
        // Remove original onclick confirm
        submitButton.removeAttribute('onclick');
        submitButton.type = 'button';
        
        // Add SweetAlert2 confirmation
        submitButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Get custom messages from data attributes
            const itemName = form.dataset.itemName || 'este elemento';
            const itemType = form.dataset.itemType || 'elemento';
            const warningMessage = form.dataset.warningMessage || null;
            
            // Build confirmation text
            let confirmText = `¿Está seguro de eliminar ${itemType === 'elemento' ? 'este elemento' : 'el ' + itemType} "${itemName}"?`;
            if (warningMessage) {
                confirmText += `\n\n⚠️ ${warningMessage}`;
            }
            
            Swal.fire({
                title: `¿Eliminar ${itemType}?`,
                html: confirmText.replace(/\n/g, '<br>'),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#95a5a6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
