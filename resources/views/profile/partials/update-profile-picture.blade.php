<div class="card flex-fill">
    <div class="card-body profile-card pt-4 d-flex flex-column align-items-center position-relative">
        <!-- Profile Picture with Hover and Fade Effect -->
        <div class="profile-image-wrapper position-relative" style="width: 150px; height: 150px;">
            <img id="profileImage" src="{{ Auth::user()->profile_photo_url }}" alt="Profile"
                class="rounded-circle profile-image w-100 h-100" style="cursor: pointer; transition: filter 0.3s ease;">

            <!-- Upload icon that appears with fade effect on hover -->
            <div class="upload-icon position-absolute top-50 start-50 translate-middle"
                style="opacity: 0; transition: opacity 0.3s ease;">
                <i class="bi bi-upload text-white" style="font-size: 2rem;"></i>
            </div>

            <!-- Hidden file input for profile picture -->
            <input type="file" id="profilePictureInput" name="profile_picture" style="display: none;"
                accept="image/*">
        </div>

        <h2>{{ Auth::user()->full_name }}</h2>
        <h3>{{ Auth::user()->role->name }}</h3>
    </div>
</div>

<!-- Crop Modal -->
<div class="modal fade" id="cropModal" tabindex="-1" aria-labelledby="cropModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg"> <!-- Larger modal to give more space -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crop Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="img-container" style="max-height: 70vh; overflow: hidden; display: flex; justify-content: center; align-items: center;"> <!-- Ensure image fits container -->
                    <img id="cropImage" src="" style="width: 100%; height: auto; max-height: 70vh; object-fit: contain;"> <!-- Scale image, maintain aspect ratio -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="cropAndUpload" class="btn btn-primary">Crop & Upload</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
    let cropper;

    // Show the file input when clicking on the profile image
    document.getElementById('profileImage').addEventListener('click', function() {
        document.getElementById('profilePictureInput').click();
    });

    // Hover effect to show the upload icon and darken the image with fade animation
    const profileImageWrapper = document.querySelector('.profile-image-wrapper');
    const profileImage = document.getElementById('profileImage');
    const uploadIcon = document.querySelector('.upload-icon');

    profileImageWrapper.addEventListener('mouseenter', function() {
        profileImage.style.filter = 'brightness(50%)'; // Darken the image
        uploadIcon.style.opacity = '1'; // Fade in the upload icon
    });

    profileImageWrapper.addEventListener('mouseleave', function() {
        profileImage.style.filter = 'brightness(100%)'; // Restore original brightness
        uploadIcon.style.opacity = '0'; // Fade out the upload icon
    });

    // When a file is selected, show the crop modal
    document.getElementById('profilePictureInput').addEventListener('change', function(event) {
        const files = event.target.files;
        if (files && files.length > 0) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const image = document.getElementById('cropImage');
                image.src = e.target.result;

                // Show the crop modal
                const cropModal = new bootstrap.Modal(document.getElementById('cropModal'), {
                    backdrop: 'static',
                    keyboard: false
                });
                cropModal.show();

                // Initialize cropper after modal is fully shown
                document.getElementById('cropModal').addEventListener('shown.bs.modal', function() {
                    cropper = new Cropper(image, {
                        aspectRatio: 1,
                        viewMode: 3,
                    });
                });

                // Destroy the cropper instance when modal is hidden
                document.getElementById('cropModal').addEventListener('hidden.bs.modal', function() {
                    cropper.destroy();
                    cropper = null;
                });
            };
            reader.readAsDataURL(files[0]);
        }
    });

    // Crop and upload the image
    document.getElementById('cropAndUpload').addEventListener('click', function() {
        const canvas = cropper.getCroppedCanvas({
            width: 150,
            height: 150,
        });

        canvas.toBlob(function(blob) {
            const formData = new FormData();
            formData.append('profile_picture', blob, 'profile.jpg');

            // Send the cropped image to the server via AJAX
            fetch('{{ route('profile.update-picture') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-HTTP-Method-Override': 'PATCH'

                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Update the profile picture on the page with the new image URL
                        profileImage.src = data.url;
                        const cropModal = bootstrap.Modal.getInstance(document.getElementById(
                            'cropModal'));
                        cropModal.hide(); // Hide modal after uploading
                        window.location.reload();
                    } else {
                        alert('Error updating profile picture');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        });
    });
</script>