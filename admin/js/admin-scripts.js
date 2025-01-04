document.addEventListener('DOMContentLoaded', function () {
    console.log('Pustakabilitas Admin Panel Loaded');

    // Contoh interaksi form
    const formInputs = document.querySelectorAll('.pustakabilitas-form-group input');
    formInputs.forEach(input => {
        input.addEventListener('focus', function () {
            this.style.borderColor = '#0073aa';
        });
        input.addEventListener('blur', function () {
            this.style.borderColor = '#ddd';
        });
    });
});

jQuery(document).ready(function($) {
    // Handle import form submission
    $('#pustakabilitas-import-form').on('submit', function(e) {
        e.preventDefault();
        
        // Show progress bar
        $('#import-progress-container').show();
        var progressBar = $('#import-progress-bar');
        var progressText = $('#import-progress-text');
        var form = $(this);
        
        // Disable submit button
        form.find('input[type="submit"]').prop('disabled', true);
        
        // Create FormData object
        var formData = new FormData(this);
        
        // Start import
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    checkProgress(response.data.import_id);
                } else {
                    showError(response.data.message);
                }
            },
            error: function() {
                showError('Error starting import');
            }
        });
        
        function checkProgress(importId) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'check_import_progress',
                    nonce: pustakabilitasAdmin.importNonce,
                    import_id: importId
                },
                success: function(response) {
                    if (response.success) {
                        var progress = response.data;
                        var percentage = progress.percentage;
                        
                        // Update progress bar
                        progressBar.css('width', percentage + '%');
                        progressText.text(percentage + '% (' + progress.processed + ' of ' + progress.total + ' books imported)');
                        
                        if (!progress.completed) {
                            // Check again in 1 second
                            setTimeout(function() {
                                checkProgress(importId);
                            }, 1000);
                        } else {
                            // Import completed
                            progressText.text('Import completed successfully!');
                            form.find('input[type="submit"]').prop('disabled', false);
                            
                            // Reload page after 2 seconds
                            setTimeout(function() {
                                window.location.reload();
                            }, 2000);
                        }
                    } else {
                        showError('Error checking progress');
                    }
                },
                error: function() {
                    showError('Error checking progress');
                }
            });
        }
        
        function showError(message) {
            $('#import-error').text(message).show();
            form.find('input[type="submit"]').prop('disabled', false);
            $('#import-progress-container').hide();
        }
    });
});
