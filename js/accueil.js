document.addEventListener('DOMContentLoaded', function() {
    // Gestionnaire de la modale de certification
    const btnOpen = document.querySelector('.btn-main');
    if (btnOpen) {
        btnOpen.addEventListener('click', function() {
            const modal = document.getElementById('certifManagerModal');
            if (modal) modal.style.display = 'block';
        });
    }

    const btnCancel = document.querySelector('.btn-cancel');
    if (btnCancel) {
        btnCancel.addEventListener('click', function() {
            closeCertifManager();
        });
    }

    // Confirmation de suppression
    const deleteLinks = document.querySelectorAll('.btn-delete-link');
    deleteLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirm('Supprimer ce compte ?')) {
                e.preventDefault();
            }
        });
    });

    // Upload de photo de profil via AJAX
    const photoInput = document.getElementById('photo_upload_input');
    if (photoInput) {
        photoInput.addEventListener('change', function() {
            console.log("Change event detected on file input.");

            const form = document.getElementById('profile_form');
            if (!form) {
                return console.error("Upload Error: The form with id 'profile_form' was not found.");
            }
            console.log("Form found.");

            const meta = document.querySelector('meta[name="base-url"]');
            if (!meta) {
                return console.error("Configuration Error: The 'base-url' meta tag was not found.");
            }
            const baseUrl = meta.getAttribute('content');
            console.log("Base URL found:", baseUrl);

            const uploadUrl = baseUrl + 'upload-profile-picture';
            const formData = new FormData(form);

            const profileContainer = document.querySelector('.profile-container');
            if (profileContainer) {
                profileContainer.style.opacity = '0.5';
                console.log("Set opacity on profile container.");
            }

            console.log("Sending file to:", uploadUrl);
            fetch(uploadUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                console.log("Received response from server.");
                if (!response.ok) {
                    throw new Error(`Server responded with status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log("Response JSON parsed successfully:", data);
                window.location.reload();
            })
            .catch(error => {
                console.error('A critical error occurred during the upload process:', error);
                alert("Upload failed. Check the browser console (F12) for more details.");
                if (profileContainer) {
                    profileContainer.style.opacity = '1';
                }
            });
        });
    }

    // Fermer la modale
    window.onclick = function(event) {
        const modal = document.getElementById('certifManagerModal');
        if (event.target == modal) {
            closeCertifManager();
        }
    };
});

function closeCertifManager() {
    const modal = document.getElementById('certifManagerModal');
    if (modal) {
        modal.style.display = 'none';
        const passwordInput = document.getElementById('admin_password');
        if (passwordInput) passwordInput.value = '';
    }
}
