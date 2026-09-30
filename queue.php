<?php
require_once __DIR__ . '/db_connect.php';

session_start();

$errorMessage = '';

if (empty($_SESSION['queueCsrfToken'])) {
    $_SESSION['queueCsrfToken'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['queueCsrfToken'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patientId = filter_input(INPUT_POST, 'patient_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $submittedToken = $_POST['csrf_token'] ?? '';

    if (!is_string($submittedToken) || !hash_equals($csrfToken, $submittedToken)) {
        $errorMessage = 'Your queue session has expired. Please refresh and try again.';
    } elseif ($patientId === false || $patientId === null) {
        $errorMessage = 'Invalid patient record selected.';
    } else {
        $deleteStmt = $conn->prepare('DELETE FROM patients WHERE id = ?');

        if ($deleteStmt === false) {
            $errorMessage = 'Unable to update the patient queue. Please try again.';
        } else {
            $deleteStmt->bind_param('i', $patientId);

            if ($deleteStmt->execute()) {
                $_SESSION['queueSuccessMessage'] = $deleteStmt->affected_rows === 1
                    ? 'Patient marked as seen and removed from the live queue.'
                    : 'That patient record is no longer in the live queue.';
                $deleteStmt->close();
                header('Location: queue.php');
                exit;
            }

            $errorMessage = 'Unable to update the patient queue. Please try again.';
            $deleteStmt->close();
        }
    }
}


$successMessage = $_SESSION['queueSuccessMessage'] ?? '';
unset($_SESSION['queueSuccessMessage']);

$patients = [];
$queueResult = $conn->query('SELECT id, name, age, symptoms, triage_score, risk_level, created_at FROM patients ORDER BY triage_score DESC, created_at ASC');

if ($queueResult === false) {
    $errorMessage = 'Unable to load the live patient queue. Please try again.';
} else {
    while ($patient = $queueResult->fetch_assoc()) {
        $patients[] = $patient;
    }
}

function getRiskBadgeClass(?string $riskLevel): string
{
    return match ($riskLevel) {
        'High Risk' => 'queue-risk-high',
        'Moderate Risk' => 'queue-risk-moderate',
        default => 'queue-risk-low'
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="MedAssist Pro live doctor queue for prioritized patient care.">
    <title>MedAssist Pro | Doctor Portal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="queue-page">
    <div class="queue-orb queue-orb-one"></div>
    <div class="queue-orb queue-orb-two"></div>

    <main class="queue-shell">
        <a href="index.php" class="queue-back-link"><span aria-hidden="true">←</span> Back to Home</a>

        <header class="queue-header">
            <div>
                <p class="queue-kicker">Clinical command center</p>
                <h1>Live Patient Queue</h1>
                <p>Patients are prioritized by triage score, so urgent cases remain at the top.</p>
            </div>
            <div class="queue-total" aria-label="Patients waiting">
                <span>Patients waiting</span>
                <strong><?php echo count($patients); ?></strong>
            </div>
        </header>

        <?php if ($successMessage !== ''): ?>
            <div class="queue-alert queue-alert-success" role="status">
                <span><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                <button type="button" class="queue-alert-close" aria-label="Dismiss success message">&times;</button>
            </div>
        <?php endif; ?>

        <?php if ($errorMessage !== ''): ?>
            <div class="queue-alert queue-alert-error" role="alert">
                <span><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                <button type="button" class="queue-alert-close" aria-label="Dismiss error message">&times;</button>
            </div>
        <?php endif; ?>

        <section class="queue-card" aria-labelledby="queue-table-title">
            <div class="queue-card-heading">
                <div>
                    <p class="queue-kicker">Priority overview</p>
                    <h2 id="queue-table-title">Patients awaiting consultation</h2>
                </div>
                <span class="queue-live-status"><span></span> Live queue</span>
            </div>

            <?php if (count($patients) > 0): ?>
                <div class="queue-table-wrapper">
                    <table class="patient-queue-table">
                        <thead>
                            <tr>
                                <th scope="col">Patient ID</th>
                                <th scope="col">Name</th>
                                <th scope="col">Age</th>
                                <th scope="col">Symptoms</th>
                                <th scope="col">Risk Level</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($patients as $patient): ?>
                                <tr>
                                    <td data-label="Patient ID"><span class="patient-id">#<?php echo (int) $patient['id']; ?></span></td>
                                    <td data-label="Name"><strong class="patient-name"><?php echo htmlspecialchars($patient['name'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                    <td data-label="Age"><?php echo (int) $patient['age']; ?></td>
                                    <td data-label="Symptoms"><span class="patient-symptoms"><?php echo htmlspecialchars($patient['symptoms'] ?: 'No symptoms selected', ENT_QUOTES, 'UTF-8'); ?></span></td>
                                    <td data-label="Risk Level">
                                        <span class="risk-badge <?php echo getRiskBadgeClass($patient['risk_level']); ?>">
                                            <?php echo htmlspecialchars($patient['risk_level'] ?: 'Low Risk', ENT_QUOTES, 'UTF-8'); ?>
                                            <small>Score <?php echo (int) $patient['triage_score']; ?></small>
                                        </span>
                                    </td>
                                    <td data-label="Action">
                                        <form action="queue.php" method="POST" class="seen-form">
                                            <input type="hidden" name="patient_id" value="<?php echo (int) $patient['id']; ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                                            <button type="submit" class="seen-button">Mark as Seen</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="queue-empty-state">
                    <span class="queue-empty-icon" aria-hidden="true">✓</span>
                    <h3>The queue is clear</h3>
                    <p>No patients are currently waiting for consultation.</p>
                    <a href="triage.php">Register a patient</a>
                </div>
            <?php endif; ?>
        </section>
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