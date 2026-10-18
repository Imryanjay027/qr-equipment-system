// 2. I-verify kung tama ang OTP (mula man sa DB query o sa Session)
        if ($user || ($session_otp && $session_otp == $user_otp)) {
            $_SESSION['user_id'] = $user['id'] ?? $_SESSION['user_id'] ?? 1;
            $_SESSION['username'] = $user['username'] ?? '';
            $_SESSION['fullname'] = $user['fullname'] ?? '';
            $_SESSION['role'] = $user['role'] ?? 'custodian';
            $_SESSION['logged_in'] = true; // Dagdag na flag para sa session

            // Subukang linisin ang OTP
            unset($_SESSION['otp_code']);
            if ($user && isset($user['id'])) {
                try {
                    $pdo->prepare("UPDATE tbl_users SET otp_code = NULL WHERE id = ?")->execute([$user['id']]);
                } catch (\Throwable $e) {}
            }

            // Direktang Redirect sa iyong Custodian Dashboard
            // Palitan ang filename sa ibaba kung iba ang pangalan ng dashboard mo
            header("Location: custodian_dashboard.php");
            exit;
        } else {
            $error = "Invalid OTP code. Please check your email and try again.";
        }
