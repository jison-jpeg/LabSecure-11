<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Mode</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body, html {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
            color: #fff;
            background-color: #132d60; /* Base blue */
            animation: pulseBackground 3s ease-in-out infinite; /* Animation */
        }
        @keyframes pulseBackground {
            0%, 100% {
                background-color: #132d60;
            }
            50% {
                background-color: #101f3c; /* Brighter blue */
            }
        }
        .container {
            text-align: left;
            max-width: 600px;
            padding: 20px;
        }
        h1 {
            font-size: 3em;
            font-weight: 300;
            margin-bottom: 20px;
        }
        p {
            font-size: 1.2em;
            font-weight: 400;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>We’ll be right back!</h1>
        <p>We're currently performing some maintenance. Please check back soon.</p>
    </div>
</body>
</html>
