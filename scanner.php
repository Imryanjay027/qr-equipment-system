// Sapilitang pag-activate ng Back Camera para sa Mobile Devices
            const constraints = {
                facingMode: { exact: "environment" }
            };

            html5QrCode.start(
                constraints, 
                config, 
                handleScanSuccess
            ).catch(err => {
                // Fallback sakaling hindi suportado ang exact environment constraint ng ilang mobile browser
                html5QrCode.start(
                    { facingMode: "environment" }, 
                    config, 
                    handleScanSuccess
                ).catch(e => {
                    // Huling fallback kung sakaling walang back camera (hal. laptop webcam)
                    html5QrCode.start(
                        { facingMode: "user" }, 
                        config, 
                        handleScanSuccess
                    ).catch(ex => {
                        console.error("Hindi ma-access ang camera:", ex);
                    });
                });
            });
