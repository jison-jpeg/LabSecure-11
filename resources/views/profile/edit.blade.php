<x-app-layout>
    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Profile</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                    <li class="breadcrumb-item">Users</li>
                    <li class="breadcrumb-item active">Profile</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section profile">
            <div class="row">
                <div class="col-xl-4 d-flex">
                    @include('profile.partials.update-profile-picture')
                </div>
        
                <div class="col-xl-8 d-flex">
                    @include('profile.partials.update-profile-information-form')
                </div>
        
                <div class="col-xl-12">
                    @include('profile.partials.update-password-form')
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </section>
        

    </main>
</x-app-layout>

