<?php
require_once __DIR__ . '/db_connect.php';

$triageResult = null;
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Ensure the patients table exists before inserting any data
    $createTableSql = "CREATE TABLE IF NOT EXISTS patients (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100),
        age INT,
        phone VARCHAR(20),
        gender VARCHAR(20),
        blood_group VARCHAR(10),
        symptoms TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if (!$conn->query($createTableSql)) {
        $errorMessage = 'Unable to prepare the patient records table. Please try again.';
    } else {
        // Add triage fields to existing installations without recreating the table.
        $requiredColumns = [
            'triage_score' => 'INT',
            'risk_level' => 'VARCHAR(20)'
        ];

        foreach ($requiredColumns as $columnName => $columnDefinition) {
            $columnResult = $conn->query("SHOW COLUMNS FROM patients LIKE '{$columnName}'");

            if ($columnResult === false) {
                $errorMessage = 'Unable to verify the patient records table. Please try again.';
                break;
            }

            if ($columnResult->num_rows === 0 && !$conn->query("ALTER TABLE patients ADD COLUMN {$columnName} {$columnDefinition}")) {
                $errorMessage = 'Unable to update the patient records table. Please try again.';
                break;
            }
        }

        if ($errorMessage !== '') {
            // Stop processing if the table migration did not complete.
        } else {
        // Collect form input and validate it on the server.
        $name        = trim($_POST['patient_name'] ?? '');
        $age         = $_POST['age'] ?? '';
        $phone       = trim($_POST['phone'] ?? '');
        $gender      = trim($_POST['gender'] ?? '');
        $bloodGroup  = trim($_POST['blood_group'] ?? '');
        $symptomsArr = $_POST['symptoms'] ?? [];
        $allowedGenders = ['male', 'female', 'other'];
        $allowedBloodGroups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
        $symptomScores = [
            'Severe Chest Pain' => 5,
            'Breathing Difficulty' => 4,
            'High Fever' => 3,
            'Dizziness' => 2,
            'Normal Cough' => 1,
            'Body Ache' => 1
        ];
        $allowedSymptoms = array_keys($symptomScores);
        $symptomsArr = is_array($symptomsArr) ? $symptomsArr : [];
        $selectedSymptoms = array_values(array_unique(array_intersect($symptomsArr, $allowedSymptoms)));
        $symptoms = implode(', ', $selectedSymptoms);

        if (
            $name === '' ||
            filter_var($age, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 150]]) === false ||
            $phone === '' ||
            !in_array($gender, $allowedGenders, true) ||
            !in_array($bloodGroup, $allowedBloodGroups, true)
        ) {
            $errorMessage = 'Please provide valid values for all required fields.';
        } else {
            $ageInt = (int) $age;
            $triageScore = $ageInt > 60 ? 3 : ($ageInt < 5 ? 2 : 0);

            foreach ($selectedSymptoms as $symptom) {
                $triageScore += $symptomScores[$symptom];
            }

            if ($triageScore >= 7) {
                $riskLevel = 'High Risk';
                $riskClass = 'risk-high';
            } elseif ($triageScore >= 4) {
                $riskLevel = 'Moderate Risk';
                $riskClass = 'risk-moderate';
            } else {
                $riskLevel = 'Low Risk';
                $riskClass = 'risk-low';
            }

            // Securely insert the data using a prepared statement
            $insertSql = "INSERT INTO patients (name, age, phone, gender, blood_group, symptoms, triage_score, risk_level) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insertSql);

            if ($stmt === false) {
                $errorMessage = 'Unable to prepare the patient registration. Please try again.';
            } else {
                $stmt->bind_param('sissssis', $name, $ageInt, $phone, $gender, $bloodGroup, $symptoms, $triageScore, $riskLevel);

                if ($stmt->execute()) {
                    $triageResult = [
                        'riskLevel' => $riskLevel,
                        'triageScore' => $triageScore,
                        'riskClass' => $riskClass
                    ];
                } else {
                    $errorMessage = 'Unable to register the patient. Please try again.';
                }

                $stmt->close();
            }
        }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedAssist - Automated Smart Triage</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="triage-page">

    <div class="container">
        <div class="form-card">
            <div class="brand">
                <h1>MedAssist</h1>
                <p class="subtitle">Automated Smart Triage &mdash; Patient Registration</p>
            </div>

            <?php if ($triageResult !== null): ?>
                <div class="triage-result <?php echo htmlspecialchars($triageResult['riskClass'], ENT_QUOTES, 'UTF-8'); ?>" role="status">
                    <div class="triage-result-content">
                        <span class="triage-result-label">Smart Triage Result</span>
                        <h2><?php echo htmlspecialchars($triageResult['riskLevel'], ENT_QUOTES, 'UTF-8'); ?></h2>
                        <p>Patient Registered Successfully!</p>
                    </div>
                    <div class="triage-score" aria-label="Total triage score">
                        <span>Total Score</span>
                        <strong><?php echo (int) $triageResult['triageScore']; ?></strong>
                    </div>
                    <button type="button" class="alert-close" aria-label="Dismiss success message">&times;</button>
                </div>
            <?php endif; ?>

            <?php if ($errorMessage !== ''): ?>
                <div class="alert alert-error" role="alert">
                    <span><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                    <button type="button" class="alert-close" aria-label="Dismiss error message">&times;</button>
                </div>
            <?php endif; ?>

            <form action="#" method="POST" class="registration-form">
                <div class="form-group">
                    <label for="patient-name">Patient Name</label>
                    <input type="text" id="patient-name" name="patient_name" placeholder="Enter full name" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="age">Patient Age</label>
                        <input type="number" id="age" name="age" placeholder="Enter age" min="0" max="150" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" placeholder="Enter phone number" pattern="[0-9+\-\s]{7,15}" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender" required>
                            <option value="" disabled selected>Select gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="blood-group">Blood Group</label>
                        <select id="blood-group" name="blood_group" required>
                            <option value="" disabled selected>Select blood group</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Symptoms</label>
                    <div class="checkbox-group">
                        <label class="checkbox-item">
                            <input type="checkbox" name="symptoms[]" value="Severe Chest Pain">
                            <span>Severe Chest Pain</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="symptoms[]" value="Breathing Difficulty">
                            <span>Breathing Difficulty</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="symptoms[]" value="High Fever">
                            <span>High Fever</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="symptoms[]" value="Dizziness">
                            <span>Dizziness</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="symptoms[]" value="Normal Cough">
                            <span>Normal Cough</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="symptoms[]" value="Body Ache">
                            <span>Body Ache</span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Register Patient</button>
            </form>
        </div>
    </div>

    <script>
        document.querySelectorAll('.alert-close').forEach(function (button) {
            button.addEventListener('click', function () {
                button.parentElement.remove();
            });
        });
    </script>
</body>
</html>
