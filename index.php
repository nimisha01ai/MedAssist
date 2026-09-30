<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="MedAssist Pro is an offline-first hospital management platform for smart triage, queues, and care coordination.">
    <title>MedAssist Pro | Offline Hospital Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="landing-page">
    <div class="landing-orb landing-orb-one"></div>
    <div class="landing-orb landing-orb-two"></div>

    <header class="site-header">
        <nav class="top-navbar" aria-label="Main navigation">
            <a href="index.php" class="saas-logo" aria-label="MedAssist Pro home">
                <span class="logo-mark" aria-hidden="true">+</span>
                <span>MedAssist <strong>Pro</strong></span>
            </a>
            <a class="nav-cta" href="triage.php">Open Triage</a>
        </nav>
    </header>

    <main>
        <section class="landing-hero" aria-labelledby="hero-title">
            <p class="eyebrow"><span class="status-dot"></span> Built for care teams that cannot afford downtime</p>
            <h1 id="hero-title">Next-Gen <span>Offline Hospital Management.</span></h1>
            <p class="hero-copy">Automate triage, manage queues, and save lives—without needing an internet connection.</p>
            <div class="hero-actions">
                <a class="primary-action" href="#access-portals">Explore Portals <span aria-hidden="true">→</span></a>
                <a class="secondary-action" href="#features">Why MedAssist Pro</a>
            </div>
            <div class="hero-proof" aria-label="Product benefits">
                <span>Offline-first</span>
                <span>Fast deployment</span>
                <span>Built for critical care</span>
            </div>
        </section>

        <section class="access-section" id="access-portals" aria-labelledby="access-title">
            <div class="section-heading">
                <p class="section-kicker">Access portals</p>
                <h2 id="access-title">One system. Every critical workflow.</h2>
                <p>Select your workspace to keep patients moving from arrival to care.</p>
            </div>

            <div class="portal-grid">
                <a class="portal-card triage-portal" href="triage.php">
                    <div class="portal-icon" aria-hidden="true">⌁</div>
                    <div class="portal-content">
                        <p class="portal-overline">Front desk workflow</p>
                        <h3>Receptionist Desk <span>(Smart Triage)</span></h3>
                        <p>Register patients, assess urgency instantly, and direct care with confidence.</p>
                    </div>
                    <span class="portal-link">Launch smart triage <span aria-hidden="true">→</span></span>
                </a>

                <a class="portal-card doctor-portal" href="queue.php" aria-label="Open Doctor Portal Live Queue">
                    <div class="portal-icon" aria-hidden="true">✦</div>
                    <div class="portal-content">
                        <p class="portal-overline">Clinical command center</p>
                        <h3>Doctor Portal <span>(Live Queue)</span></h3>
                        <p>Prioritize urgent cases, monitor arrivals, and coordinate the next best action.</p>
                    </div>
                    <span class="portal-link">Open live queue <span aria-hidden="true">→</span></span>
                </a>

                <a class="portal-card lab-portal" href="lab.php" aria-label="Open Smart Lab Analyzer Automated Reports">
                    <div class="portal-icon" aria-hidden="true">✚</div>
                    <div class="portal-content">
                        <p class="portal-overline">Diagnostic intelligence</p>
                        <h3>Smart Lab Analyzer <span>(Automated Reports)</span></h3>
                        <p>Convert core lab values into clear, patient-friendly screening summaries and next-step guidance.</p>
                    </div>
                    <span class="portal-link">Analyze lab results <span aria-hidden="true">→</span></span>
                </a>

                <a class="portal-card beds-portal" href="beds.php" aria-label="Open Bed Management Real-time Tracker">
                    <div class="portal-icon" aria-hidden="true">▦</div>
                    <div class="portal-content">
                        <p class="portal-overline">Capacity command center</p>
                        <h3>Bed Management <span>(Real-time Tracker)</span></h3>
                        <p>Track live ICU and ward capacity, then update admissions and discharges without refreshing the dashboard.</p>
                    </div>
                    <span class="portal-link">Manage bed capacity <span aria-hidden="true">→</span></span>
                </a>
            </div>
        </section>

        <section class="features-section" id="features" aria-labelledby="features-title">
            <div class="section-heading compact-heading">
                <p class="section-kicker">Designed for resilience</p>
                <h2 id="features-title">Care delivery that keeps moving.</h2>
            </div>
            <div class="features-grid">
                <article class="feature-item">
                    <span class="feature-number">01</span>
                    <h3>100% Offline</h3>
                    <p>Keep registration and workflow tools available even when connectivity disappears.</p>
                </article>
                <article class="feature-item">
                    <span class="feature-number">02</span>
                    <h3>Rule-Based Smart Triage</h3>
                    <p>Surface high-priority patients consistently with transparent, configurable scoring.</p>
                </article>
                <article class="feature-item">
                    <span class="feature-number">03</span>
                    <h3>Real-time Bed Tracking</h3>
                    <p>Turn capacity into clarity so teams can route patients to the right place faster.</p>
                </article>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <span>© <?php echo date('Y'); ?> MedAssist Pro</span>
        <span>Healthcare operations, uninterrupted.</span>
    </footer>
</body>
</html>