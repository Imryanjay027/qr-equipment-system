<?php
// header.php - Shared header and global styling for MQCQC Equipment System
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MQCQC - Equipment System</title>
    <style>
        /* Global Reset & Base Styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            color: #333;
            line-height: 1.6;
        }

        /* Responsive Container & Layout Adjustments */
        .container, .module-box, form {
            max-width: 100%;
            width: 100%;
            margin: 0 auto;
            padding: 15px;
        }

        /* Mobile Responsiveness via Media Query */
        @media (max-width: 768px) {
            .container, .module-box {
                width: 100% !important;
                padding: 10px !important;
                margin: 0 !important;
            }
            
            form, input, select, button {
                width: 100% !important;
            }

            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
        }
    </style>
</head>
<body>