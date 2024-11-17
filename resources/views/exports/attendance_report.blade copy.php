<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Daily Time Record</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
                font-size: 10px; /* Set font size to 10 points */
            }
            .pdf-wrapper {
                transform: scale(0.9);
                transform-origin: top left;
                width: 111.11%;
            }
            .header {
                position: relative;
                text-align: center;
                margin-bottom: 20px;
            }
            .header img {
                position: absolute;
                top: 50%;
                left: 0;
                transform: translateY(-50%);
                width: 60px;
                height: auto;
            }
            .header-text {
                display: inline-block;
                text-align: center;
            }
            .header-text h1,
            .header-text p {
                margin: 0;
                font-size: 15px; /* Equal font size for all elements */
            }
            .sub-header h1,
            .sub-header p,
            .sub-header strong,
            .sub-header span,
            .sub-header div,
            .sub-header td,
            .sub-header th {
                margin: 0;
                font-size: 15px;
            }
            .sub-header {
                text-align: center;
                font-size: 1rem;
                margin-bottom: 10px;
            }
            .sub-header {
                text-align: center;
                font-size: 15px;
                margin-bottom: 10px;
            }
            .info {
                margin-bottom: 20px;
            }
            .info-box {
                border: 1px solid #000;
                padding: 5px;
                margin-bottom: 5px;
            }
            .info-box table {
                width: 100%;
                border-collapse: collapse;
            }
            .info-box td {
                padding: 2px;
            }
            .schedule {
                margin-top: 10px;
                margin-bottom: 5px;
                text-align: left;
            }
            .schedule strong {
                display: block;
            }
            .dtr-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            .dtr-table th,
            .dtr-table td {
                border: 1px solid #000;
                text-align: center;
                padding: 2px;
                font-size: 10px;
            }
            .dtr-table th {
                background-color: #f2f2f2;
            }
            .signatures {
                margin-top: 20px;
                display: flex;
                justify-content: space-between;
            }
            .signatures div {
                text-align: center;
                width: 45%;
            }
            .signature-line {
                border-top: 1px solid #000;
                margin-top: 40px;
            }
        </style>
    </head>
    <body>
        <div class="pdf-wrapper">

        <div class="header">
            <img
                src="https://buksu.edu.ph/wp-content/uploads/2020/05/buksu-logo-min-1024x1024.png"
                alt="BukSU Logo"
            />
            <div class="header-text">
                <h1>Bukidnon State University</h1>
                <p>Malaybalay City, Bukidnon 6700</p>
                <p>Tel: (088) 813-5661 to 5663, Telefax: (088) 813-2717</p>
                <p>www.buksu.edu.ph</p>
            </div>
        </div>
        <div class="sub-header">
            <h1>ATTENDANCE RECORD</h1>
        </div>
        <div class="info-box">
            <table>
                <tr>
                    <td><strong>Name:</strong> MARK IAN M. MUKARA</td>
                    <td><strong>Position:</strong> Instructor</td>
                </tr>
                <tr>
                    <td>
                        <strong>College:</strong> College of Technologies -
                        Information Technologies
                    </td>
                    <td><strong>Month:</strong> July 2024</td>
                </tr>
            </table>
        </div>

        <div class="schedule">
            <strong>SCHEDULE:</strong> English Section A(T123) 2024 M-F
            08:00 AM - 12:00 PM : 01:00 PM - 05:00 PM
        </div>
        <table class="dtr-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Status</th>
                    <th>Percentage</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <!-- Example rows -->
                <tr>
                    <td>MON - 07/01/2024</td>
                    <td>08:00 AM</td>
                    <td>12:00 PM</td>
                    <td>Present</td>
                    <td>100%</td>
                    <td></td>
                </tr>
                <tr>
                    <td>TUE - 07/01/2024</td>
                    <td>08:00 AM</td>
                    <td>12:00 PM</td>
                    <td>Present</td>
                    <td>100%</td>
                    <td></td>
                </tr>
                <tr>
                    <td>THU - 07/01/2024</td>
                    <td>08:00 AM</td>
                    <td>12:00 PM</td>
                    <td>Present</td>
                    <td>100%</td>
                    <td></td>
                </tr>
                <tr>
                    <td>FRI - 07/01/2024</td>
                    <td>08:00 AM</td>
                    <td>12:00 PM</td>
                    <td>Present</td>
                    <td>100%</td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <div class="schedule">
            <strong>SCHEDULE:</strong> Science Section A(T123) 2024 M-F
            08:00 AM - 12:00 PM : 01:00 PM - 05:00 PM
        </div>
        <table class="dtr-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Status</th>
                    <th>Percentage</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <!-- Example rows -->
                <tr>
                    <td>MON - 07/01/2024</td>
                    <td>08:00 AM</td>
                    <td>12:00 PM</td>
                    <td>Present</td>
                    <td>100%</td>
                    <td>Marked as late. Arrived 18 minutes late but attended 142 minutes out of 150
                        minutes.
                        </td>
                </tr>
                <tr>
                    <td>TUE - 07/01/2024</td>
                    <td>08:00 AM</td>
                    <td>12:00 PM</td>
                    <td>Present</td>
                    <td>100%</td>
                    <td></td>
                </tr>
                <tr>
                    <td>THU - 07/01/2024</td>
                    <td>08:00 AM</td>
                    <td>12:00 PM</td>
                    <td>Present</td>
                    <td>100%</td>
                    <td></td>
                </tr>
                <tr>
                    <td>FRI - 07/01/2024</td>
                    <td>08:00 AM</td>
                    <td>12:00 PM</td>
                    <td>Present</td>
                    <td>100%</td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <div class="signatures">
            <div>
                <p>Signature</p>
                <div class="signature-line"></div>
            </div>
            <div>
                <p>Dean/Director/Head of Office</p>
                <div class="signature-line"></div>
            </div>
        </div>
    </div>
    </body>
</html>
