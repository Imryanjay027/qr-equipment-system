<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Mary The Queen College</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f2f5; margin: 0; padding: 0; box-sizing: border-box; }
        .navbar { background: #1a365d; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .navbar-brand { font-size: 16px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }

        .main-container { display: flex; justify-content: center; align-items: center; height: calc(100vh - 60px); padding: 20px; }
        .welcome-card { background: white; padding: 40px 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); text-align: center; width: 100%; max-width: 400px; }
        
        .welcome-card h2 { color: #1a365d; font-size: 22px; margin-bottom: 8px; }
        .welcome-card p { color: #555; font-size: 13px; margin-bottom: 25px; }

        .portal-btn { display: block; width: 100%; padding: 12px 0; margin-bottom: 12px; border-radius: 4px; font-weight: bold; text-decoration: none; font-size: 14px; text-align: center; transition: background 0.2s; box-sizing: border-box; }
        
        .btn-borrower { background: #0062cc; color: white; }
        .btn-borrower:hover { background: #0056b3; }

        .btn-custodian { background: #28a745; color: white; }
        .btn-custodian:hover { background: #218838; }

        .btn-account { background: #ffc107; color: #212529; }
        .btn-account:hover { background: #e0a800; }
    </style>
</head>
<body>

    <div class="navbar">
        <div class="navbar-brand">
            MARY THE QUEEN COLLEGE OF QUEZON CITY
        </div>
    </div>

    <div class="main-container">
        <div class="welcome-card">
            <h2>Mary The Queen College</h2>
            <p>Please select your portal to continue:</p>

            <a href="borrower.php" class="portal-btn btn-borrower">Are you a Borrower?</a>
            <a href="custodian_login.php" class="portal-btn btn-custodian">Are you a Custodian?</a>
            <a href="borrower.php" class="portal-btn btn-account">I have an Account</a>
        </div>
    </div>

</body>
</html>