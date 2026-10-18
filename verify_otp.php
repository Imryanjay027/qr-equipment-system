if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_otp = trim($_POST['otp_code'] ?? '');
    $session_otp = $_SESSION['otp_code'] ?? null;

    if (empty($user_otp)) {
        $error = "Please enter the 6-digit OTP code.";
    } else {
        $user = null;

        // 1. Subukang hanapin ang user gamit ang OTP code mula sa database
        try {
            $stmt = $pdo->prepare("SELECT * FROM tbl_users WHERE otp_code = ?");
            $stmt->execute([$user_otp]);
            $user = $stmt->fetch();
        } catch (\Throwable $e) {}

        // 2. Kung walang nahanap gamit ang OTP column, hanapin sa pamamagitan ng pending email session
        if (!$user && !empty($_SESSION['pending_otp_email'])) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM tbl_users WHERE email = ?");
                $stmt->execute([$_SESSION['pending_otp_email']]);
                $user = $stmt->fetch();
            } catch (\Throwable $e) {}
        }

        // 3. Suriin kung lehitimo at wasto ang OTP (mula sa DB o mula sa Session)
        $is_valid = false;
        
        if ($user && isset($user['otp_code']) && !empty($user['otp_code']) && $user['otp_code'] == $user_otp) {
            $is_valid = true;
        } elseif ($session_otp && $session_otp == $user_otp) {
            $is_valid = true;
            // Kung na-verify via session pero wala pang $user, kunin ito gamit ang session email
            if (!$user && !empty($_SESSION['pending_otp_email'])) {
                try {
                    $stmt = $pdo->prepare("SELECT * FROM tbl_users WHERE email = ?");
                    $stmt->execute([$_SESSION['pending_otp_email']]);
                    $user = $stmt->fetch();
                } catch (\Throwable $e) {}
            }
        }

        if ($is_valid) {
            // Itakda ang lahat ng session variables na kailangan ng custodian_dashboard.php
            $_SESSION['user_id'] = $user['id'] ?? $_SESSION['user_id'] ?? 1;
            $_SESSION['username'] = $user['username'] ?? '';
            $_SESSION['fullname'] = $user['fullname'] ?? '';
            $_SESSION['role'] = $user['role'] ?? 'custodian';
            $_SESSION['logged_in'] = true;

            // Linisin ang temporary OTP sessions
            unset($_SESSION['otp_code']);
            unset($_SESSION['pending_otp_email']);

            // Burahin ang OTP sa database para isang beses lang magamit
            if ($user && isset($user['id'])) {
                try {
                    $pdo->prepare("UPDATE tbl_users SET otp_code = NULL, otp_expiry = NULL WHERE id = ?")->execute([$user['id']]);
                } catch (\Throwable $e) {}
            }

            // Diretso sa Custodian Dashboard nang walang sagabal
            header("Location: custodian_dashboard.php");
            exit;
        } else {
            $error = "Invalid or expired OTP code. Please check your email and try again.";
        }
    }
}
