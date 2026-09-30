<?php
require_once __DIR__ . '/db_connect.php';

$errorMessage = '';
$reportItems = [];
$values = [
    'hemoglobin' => '',
    'wbc' => '',
    'platelets' => '',
    'fasting_sugar' => '',
    'hba1c' => '',
    'cholesterol' => '',
    'triglycerides' => '',
    'creatinine' => '',
    'sgpt' => ''
];

function addLabReportItem(array &$items, string $status, string $title, string $message): void
{
    $items[] = [
        'status' => $status,
        'title' => $title,
        'message' => $message
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($values as $field => $default) {
        $values[$field] = trim($_POST[$field] ?? '');
    }

    foreach ($values as $field => $value) {
        if ($value === '' || !is_numeric($value)) {
            $errorMessage = 'Please enter a valid numeric value for every lab measurement.';
            break;
        }
    }

    if ($errorMessage === '') {
        $hemoglobin = (float) $values['hemoglobin'];
        $wbc = (float) $values['wbc'];
        $platelets = (float) $values['platelets'];
        $fastingSugar = (float) $values['fasting_sugar'];
        $hba1c = (float) $values['hba1c'];
        $cholesterol = (float) $values['cholesterol'];
        $triglycerides = (float) $values['triglycerides'];
        $creatinine = (float) $values['creatinine'];
        $sgpt = (float) $values['sgpt'];

        if ($hemoglobin < 13) {
            addLabReportItem($reportItems, 'attention', 'Hemoglobin below this screening range', 'Your oxygen-carrying blood level may be lower than this tool’s reference range. Discuss this with a clinician, especially if you feel tired, weak, short of breath, or dizzy.');
        } elseif ($hemoglobin > 17) {
            addLabReportItem($reportItems, 'attention', 'Hemoglobin above this screening range', 'This result is above this tool’s reference range. Hydration, altitude, and other factors can matter, so arrange a clinician review rather than self-treating.');
        } else {
            addLabReportItem($reportItems, 'normal', 'Hemoglobin within screening range', 'Your hemoglobin is within this tool’s 13–17 g/dL screening range. Keep sharing any symptoms or prior blood-test history with your clinician.');
        }

        if ($wbc < 4000) {
            addLabReportItem($reportItems, 'attention', 'White blood cells below screening range', 'Your WBC result is below this tool’s 4,000–11,000/µL screening range. A clinician can interpret it alongside medicines, recent illness, and the rest of your blood count.');
        } elseif ($wbc > 11000) {
            addLabReportItem($reportItems, 'attention', 'White blood cells above screening range', 'Your WBC result is above this tool’s screening range. This can occur with infection or inflammation, but needs clinical context and is not a diagnosis by itself.');
        } else {
            addLabReportItem($reportItems, 'normal', 'White blood cells within screening range', 'Your WBC is within this tool’s 4,000–11,000/µL screening range. Continue routine follow-up as advised by your care team.');
        }

        if ($platelets < 150000 || $platelets > 450000) {
            addLabReportItem($reportItems, 'attention', 'Platelets need clinician review', 'Your platelet result is outside this tool’s 150,000–450,000/µL screening range. Contact a clinician to interpret it, particularly if you have unusual bleeding, bruising, or clotting symptoms.');
        } else {
            addLabReportItem($reportItems, 'normal', 'Platelets within screening range', 'Your platelet count is within this tool’s screening range, which is reassuring for this single measurement.');
        }

        if ($fastingSugar < 70) {
            addLabReportItem($reportItems, 'attention', 'Low fasting sugar', 'Your fasting sugar is below 70 mg/dL. If you have sweating, shaking, confusion, or faintness, seek urgent medical advice; otherwise discuss the result with a clinician.');
        } elseif ($fastingSugar < 100) {
            addLabReportItem($reportItems, 'normal', 'Fasting sugar in normal screening range', 'Your fasting sugar is below 100 mg/dL, which is within the usual screening range. Maintain balanced meals, activity, and routine checks.');
        } elseif ($fastingSugar < 126) {
            addLabReportItem($reportItems, 'attention', 'Fasting sugar above normal screening range', 'Your fasting sugar is in a range that warrants follow-up testing and lifestyle discussion. Limit sugary drinks, choose fiber-rich foods, and ask a clinician how to confirm the result.');
        } else {
            addLabReportItem($reportItems, 'attention', 'High fasting sugar: follow up promptly', 'Your fasting sugar is at or above 126 mg/dL. This needs prompt clinician review and usually confirmation with repeat testing; do not rely on this tool alone for a diagnosis.');
        }

        if ($hba1c < 5.7) {
            addLabReportItem($reportItems, 'normal', 'HbA1c in normal screening range', 'Your HbA1c is below 5.7%, which is within the usual screening range. Keep up sustainable food, movement, and preventive-care habits.');
        } elseif ($hba1c < 6.5) {
            addLabReportItem($reportItems, 'attention', 'HbA1c above normal screening range', 'Your HbA1c is in a range that warrants a clinician conversation about diabetes risk, food choices, activity, weight, and follow-up testing.');
        } else {
            addLabReportItem($reportItems, 'attention', 'HbA1c needs prompt clinician review', 'Your HbA1c is at or above 6.5%. Please arrange clinical follow-up to confirm and discuss a care plan; this tool cannot diagnose diabetes.');
        }

        if ($cholesterol < 200) {
            addLabReportItem($reportItems, 'normal', 'Normal: heart health looks good', 'Your total cholesterol is below 200 mg/dL. Continue heart-supportive choices such as regular activity, fiber-rich foods, and routine checkups.');
        } else {
            addLabReportItem($reportItems, 'attention', 'High Cholesterol: Risk of heart issues', 'Your total cholesterol is 200 mg/dL or higher. Reduce oily/junk food, favor fiber-rich meals, and discuss your full lipid profile and overall heart risk with a clinician.');
        }

        if ($triglycerides < 150) {
            addLabReportItem($reportItems, 'normal', 'Triglycerides within screening range', 'Your triglycerides are below 150 mg/dL. Continue limiting excess sugar and alcohol and maintain regular physical activity.');
        } else {
            addLabReportItem($reportItems, 'attention', 'Triglycerides above screening range', 'Your triglycerides are 150 mg/dL or higher. Reducing sugary foods, refined carbohydrates, and alcohol may help; ask a clinician about your complete lipid results.');
        }

        if ($creatinine < 0.7 || $creatinine > 1.3) {
            addLabReportItem($reportItems, 'attention', 'Creatinine needs clinician review', 'Your creatinine is outside this tool’s 0.7–1.3 mg/dL screening range. Kidney results depend on age, muscle mass, hydration, and eGFR, so have a clinician interpret this result.');
        } else {
            addLabReportItem($reportItems, 'normal', 'Creatinine within screening range', 'Your creatinine is within this tool’s screening range. Kidney health is best assessed with this result alongside eGFR and your clinical history.');
        }

        if ($sgpt < 7 || $sgpt > 56) {
            addLabReportItem($reportItems, 'attention', 'SGPT (ALT) needs clinician review', 'Your SGPT result is outside this tool’s 7–56 U/L screening range. Medicines, alcohol, fatty liver, infection, and other factors can affect it, so discuss it with a clinician.');
        } else {
            addLabReportItem($reportItems, 'normal', 'SGPT (ALT) within screening range', 'Your SGPT is within this tool’s screening range. Continue healthy habits and review the full liver panel with your clinician when available.');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="MedAssist Pro Smart Lab Analyzer for patient-friendly screening reports.">
    <title>MedAssist Pro | Smart Lab Analyzer</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="lab-page">
    <div class="lab-orb lab-orb-one"></div>
    <div class="lab-orb lab-orb-two"></div>

    <main class="lab-shell">
        <a href="index.php" class="queue-back-link"><span aria-hidden="true">←</span> Back to Home</a>

        <header class="lab-header">
            <p class="queue-kicker">Clinical intelligence workspace</p>
            <h1>Smart Lab Analyzer</h1>
            <p>Turn routine lab values into a clear, patient-friendly screening summary in seconds.</p>
        </header>

        <?php if ($errorMessage !== ''): ?>
            <div class="queue-alert queue-alert-error" role="alert">
                <span><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                <button type="button" class="queue-alert-close" aria-label="Dismiss error message">&times;</button>
            </div>
        <?php endif; ?>

        <section class="lab-form-card" aria-labelledby="lab-form-title">
            <div class="lab-card-heading">
                <div>
                    <p class="queue-kicker">Lab input panel</p>
                    <h2 id="lab-form-title">Enter laboratory values</h2>
                </div>
                <span class="lab-reference-note">Screening references only</span>
            </div>

            <form method="POST" action="lab.php" class="lab-form">
                <div class="lab-sections-grid">
                    <fieldset class="lab-section blood-section">
                        <legend><span>01</span> Blood</legend>
                        <div class="lab-input-grid">
                            <label>Hemoglobin <small>g/dL</small><input type="number" name="hemoglobin" value="<?php echo htmlspecialchars($values['hemoglobin'], ENT_QUOTES, 'UTF-8'); ?>" min="0" step="0.1" placeholder="e.g. 14.2" required></label>
                            <label>WBC <small>/µL</small><input type="number" name="wbc" value="<?php echo htmlspecialchars($values['wbc'], ENT_QUOTES, 'UTF-8'); ?>" min="0" step="1" placeholder="e.g. 7200" required></label>
                            <label>Platelets <small>/µL</small><input type="number" name="platelets" value="<?php echo htmlspecialchars($values['platelets'], ENT_QUOTES, 'UTF-8'); ?>" min="0" step="1" placeholder="e.g. 250000" required></label>
                        </div>
                    </fieldset>

                    <fieldset class="lab-section diabetes-section">
                        <legend><span>02</span> Diabetes</legend>
                        <div class="lab-input-grid">
                            <label>Fasting Sugar <small>mg/dL</small><input type="number" name="fasting_sugar" value="<?php echo htmlspecialchars($values['fasting_sugar'], ENT_QUOTES, 'UTF-8'); ?>" min="0" step="0.1" placeholder="e.g. 92" required></label>
                            <label>HbA1c <small>%</small><input type="number" name="hba1c" value="<?php echo htmlspecialchars($values['hba1c'], ENT_QUOTES, 'UTF-8'); ?>" min="0" step="0.1" placeholder="e.g. 5.4" required></label>
                        </div>
                    </fieldset>

                    <fieldset class="lab-section heart-section">
                        <legend><span>03</span> Heart / Lipid</legend>
                        <div class="lab-input-grid">
                            <label>Total Cholesterol <small>mg/dL</small><input type="number" name="cholesterol" value="<?php echo htmlspecialchars($values['cholesterol'], ENT_QUOTES, 'UTF-8'); ?>" min="0" step="0.1" placeholder="e.g. 180" required></label>
                            <label>Triglycerides <small>mg/dL</small><input type="number" name="triglycerides" value="<?php echo htmlspecialchars($values['triglycerides'], ENT_QUOTES, 'UTF-8'); ?>" min="0" step="0.1" placeholder="e.g. 110" required></label>
                        </div>
                    </fieldset>

                    <fieldset class="lab-section organs-section">
                        <legend><span>04</span> Organs</legend>
                        <div class="lab-input-grid">
                            <label>Creatinine <small>mg/dL</small><input type="number" name="creatinine" value="<?php echo htmlspecialchars($values['creatinine'], ENT_QUOTES, 'UTF-8'); ?>" min="0" step="0.01" placeholder="e.g. 0.9" required></label>
                            <label>SGPT / ALT <small>U/L</small><input type="number" name="sgpt" value="<?php echo htmlspecialchars($values['sgpt'], ENT_QUOTES, 'UTF-8'); ?>" min="0" step="0.1" placeholder="e.g. 28" required></label>
                        </div>
                    </fieldset>
                </div>
                <button type="submit" class="lab-submit-button">Generate Patient-Friendly Report <span aria-hidden="true">→</span></button>
            </form>
        </section>

        <?php if (count($reportItems) > 0): ?>
            <section class="lab-report-card" aria-labelledby="report-title">
                <div class="lab-card-heading report-heading">
                    <div>
                        <p class="queue-kicker">Automated screening summary</p>
                        <h2 id="report-title">Patient-Friendly Report</h2>
                    </div>
                    <span class="report-complete-mark" aria-label="Report generated">✓</span>
                </div>
                <p class="report-disclaimer">This is an educational screening summary, not a diagnosis or treatment plan. Laboratory ranges vary by lab and person; a qualified clinician should interpret results in context.</p>
                <div class="lab-report-grid">
                    <?php foreach ($reportItems as $item): ?>
                        <article class="lab-report-item report-<?php echo htmlspecialchars($item['status'], ENT_QUOTES, 'UTF-8'); ?>">
                            <span class="report-status-dot" aria-hidden="true"></span>
                            <div>
                                <h3><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                <p><?php echo htmlspecialchars($item['message'], ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </main>

    <script>
        document.querySelectorAll('.queue-alert-close').forEach(function (button) {
            button.addEventListener('click', function () {
                button.parentElement.remove();
            });
        });
    </script>
</body>
</html>