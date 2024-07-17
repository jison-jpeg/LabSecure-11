<div class="card flex-fill">
    <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">
        <img src="assets/img/profile-img.jpg" alt="Profile" class="rounded-circle">
        <h2>{{ Auth::user()->full_name }}</h2>
        <h3>{{ Auth::user()->role->name }}</h3>
        <div class="social-links mt-2">
            <a href="#" class="twitter"><i class="bi bi-twitter"></i></a>
            <a href="#" class="facebook"><i class="bi bi-facebook"></i></a>
            <a href="#" class="instagram"><i class="bi bi-instagram"></i></a>
            <a href="#" class="linkedin"><i class="bi bi-linkedin"></i></a>
        </div>
    </div>
</div>