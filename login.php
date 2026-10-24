$ch = curl_init('https://api.resend.com/emails');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $resend_api_key,
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        // Kung gusto mong makita kung anong error ang ibinibigay ng server habang nagte-test ka:
        if ($http_code !== 200) {
            echo "HTTP Code: " . $http_code . "<br>";
            echo "cURL Error: " . $curl_error . "<br>";
            echo "Response: " . $response;
            exit();
        }
