<div>

    <style>
        body {
            background-color: #f4f6f9;
        }

        .card-title {
            font-weight: bold;
        }

        .info-box {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .info-box h5 {
            font-weight: bold;
            margin-bottom: 20px;
        }

        .info-box .icon {
            font-size: 30px;
            color: #6c757d;
            margin-right: 20px;
        }

        .icon-box {
            display: flex;
            align-items: center;
        }

        .stat-card {
            border: none;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .stat-card h6 {
            margin-bottom: 10px;
        }

        .recent-activity {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .recent-activity li {
            border-bottom: 1px solid #e9ecef;
            padding: 10px 0;
        }

        .recent-activity li:last-child {
            border-bottom: none;
        }

        .department-list {
            list-style: none;
            padding-left: 0;
        }

        .department-list li {
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
        }

        .department-list li a {
            color: #007bff;
            text-decoration: none;
        }

        .department-list li a:hover {
            text-decoration: underline;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }
    </style>
    
    <section class="section dashboard">
        <div class="row">
            <!-- College Overview -->
            <div class="col-lg-8">
                <div class="info-box mb-4">
                    <div class="d-flex align-items-center">
                        <div class="icon">
                            <i class="bi bi-building"></i>
                        </div>
                        <div>
                            <h5>College of Information Technology</h5>
                            <p class="mb-0">Code: CIT</p>
                            <p class="mb-0">Description: The College of Information Technology is committed to
                                providing high-quality education in the field of IT and computer science.</p>
                        </div>
                    </div>
                </div>

                <!-- Department List -->
                <div class="info-box mb-4">
                    <h5>Departments</h5>
                    <ul class="department-list">
                        <li>
                            <span>Department of Computer Science</span>
                            <a href="#">View Details</a>
                        </li>
                        <li>
                            <span>Department of Information Systems</span>
                            <a href="#">View Details</a>
                        </li>
                        <li>
                            <span>Department of Software Engineering</span>
                            <a href="#">View Details</a>
                        </li>
                        <li>
                            <span>Department of Cybersecurity</span>
                            <a href="#">View Details</a>
                        </li>
                    </ul>
                </div>

                <!-- Recent Activity -->
                <div class="recent-activity mb-4">
                    <h5>Recent Activity</h5>
                    <ul>
                        <li>New student enrollments in the Department of Computer Science</li>
                        <li>Updated curriculum for the Department of Information Systems</li>
                        <li>New faculty member joined the Department of Cybersecurity</li>
                        <li>Workshop on AI and Data Science conducted in the Department of Software Engineering</li>
                    </ul>
                </div>
            </div>

            <!-- Statistics & Key Info -->
            <div class="col-lg-4">
                <!-- Stats -->
                <div class="row">
                    <div class="col-md-6 col-lg-12 mb-4">
                        <div class="stat-card">
                            <h6>Total Students</h6>
                            <div class="d-flex align-items-center">
                                <div class="icon-box">
                                    <i class="bi bi-people-fill icon"></i>
                                </div>
                                <div class="ms-3">
                                    <h4>1,200</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-12 mb-4">
                        <div class="stat-card">
                            <h6>Total Instructors</h6>
                            <div class="d-flex align-items-center">
                                <div class="icon-box">
                                    <i class="bi bi-person-fill icon"></i>
                                </div>
                                <div class="ms-3">
                                    <h4>85</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-12 mb-4">
                        <div class="stat-card">
                            <h6>Total Departments</h6>
                            <div class="d-flex align-items-center">
                                <div class="icon-box">
                                    <i class="bi bi-building-fill icon"></i>
                                </div>
                                <div class="ms-3">
                                    <h4>4</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Key Contacts -->
                <div class="stat-card">
                    <h6>Key Contacts</h6>
                    <ul class="department-list">
                        <li>
                            <span>Dean: Dr. Jane Doe</span>
                            <a href="#">Contact</a>
                        </li>
                        <li>
                            <span>Assistant Dean: Mr. John Smith</span>
                            <a href="#">Contact</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</div>
