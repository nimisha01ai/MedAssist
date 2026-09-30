<?php
require_once __DIR__ . '/db_connect.php';

session_start();

function sendBedJsonResponse(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit;
}

function fetchBedStatus(mysqli $conn, string $wardType): ?array
{
    $stmt = $conn->prepare('SELECT ward_type, total_beds, occupied_beds FROM bed_status WHERE ward_type = ?');

    if ($stmt === false) {
        return null;
    }

    $stmt->bind_param('s', $wardType);
    $stmt->execute();
    $result = $stmt->get_result();
    $bedStatus = $result->fetch_assoc() ?: null;
    $stmt->close();

    return $bedStatus;
}

$createTableSql = "CREATE TABLE IF NOT EXISTS bed_status (
    ward_type VARCHAR(20) PRIMARY KEY,
    total_beds INT NOT NULL,
    occupied_beds INT NOT NULL
)";

if (!$conn->query($createTableSql)) {
    http_response_code(500);
    exit('Unable to initialize bed management. Please try again.');
}

// Seed default ward capacities only when those ward records are absent.
$conn->query("INSERT IGNORE INTO bed_status (ward_type, total_beds, occupied_beds) VALUES ('ICU', 20, 15), ('General', 50, 30)");

if (empty($_SESSION['bedsCsrfToken'])) {
    $_SESSION['bedsCsrfToken'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['bedsCsrfToken'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isAjaxRequest = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
    $wardType = $_POST['ward_type'] ?? '';
    $action = $_POST['action'] ?? '';
    $submittedToken = $_POST['csrf_token'] ?? '';
    $allowedWards = ['ICU', 'General'];
    $actionDelta = ['admit' => 1, 'discharge' => -1];

    if (!is_string($submittedToken) || !hash_equals($csrfToken, $submittedToken)) {
        sendBedJsonResponse(['success' => false, 'message' => 'Your session has expired. Please refresh and try again.'], 403);
    }

    if (!in_array($wardType, $allowedWards, true) || !array_key_exists($action, $actionDelta)) {
        sendBedJsonResponse(['success' => false, 'message' => 'Invalid bed management request.'], 422);
    }

    $delta = $actionDelta[$action];
    // The range condition makes the update atomic and prevents over-admission or negative occupancy.
    $updateStmt = $conn->prepare('UPDATE bed_status SET occupied_beds = occupied_beds + ? WHERE ward_type = ? AND occupied_beds + ? BETWEEN 0 AND total_beds');

    if ($updateStmt === false) {
        sendBedJsonResponse(['success' => false, 'message' => 'Unable to update bed capacity. Please try again.'], 500);
    }

    $updateStmt->bind_param('isi', $delta, $wardType, $delta);
    $updateStmt->execute();
    $updated = $updateStmt->affected_rows === 1;
    $updateStmt->close();

    if (!$updated) {
        $message = $action === 'admit'
            ? 'This ward is currently at full capacity.'
            : 'This ward has no occupied beds to discharge.';
        sendBedJsonResponse(['success' => false, 'message' => $message], 409);
    }

    $bedStatus = fetchBedStatus($conn, $wardType);

    if ($bedStatus === null) {
        sendBedJsonResponse(['success' => false, 'message' => 'Capacity updated, but the latest ward status could not be loaded.'], 500);
    }

    $bedStatus['available_beds'] = (int) $bedStatus['total_beds'] - (int) $bedStatus['occupied_beds'];
    $bedStatus['occupancy_percentage'] = round(((int) $bedStatus['occupied_beds'] / (int) $bedStatus['total_beds']) * 100);
    $bedStatus['message'] = $action === 'admit' ? 'Patient admitted successfully.' : 'Patient discharged successfully.';

    sendBedJsonResponse(['success' => true, 'ward' => $bedStatus]);
}

$wards = [];
$result = $conn->query("SELECT ward_type, total_beds, occupied_beds FROM bed_status WHERE ward_type IN ('ICU', 'General') ORDER BY FIELD(ward_type, 'ICU', 'General')");

if ($result === false) {
    http_response_code(500);
    exit('Unable to load bed capacity. Please try again.');
}

while ($ward = $result->fetch_assoc()) {
    $ward['available_beds'] = (int) $ward['total_beds'] - (int) $ward['occupied_beds'];
    $ward['occupancy_percentage'] = round(((int) $ward['occupied_beds'] / (int) $ward['total_beds']) * 100);
    $wards[$ward['ward_type']] = $ward;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="MedAssist Pro real-time hospital bed management dashboard.">
    <title>MedAssist Pro | Bed Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="beds-page">
    <div class="beds-orb beds-orb-one"></div>
    <div class="beds-orb beds-orb-two"></div>

    <main class="beds-shell">
        <a href="index.php" class="queue-back-link"><span aria-hidden="true">←</span> Back to Home</a>

        <header class="beds-header">
            <div>
                <p class="queue-kicker">Hospital operations center</p>
                <h1>Real-time Bed Tracker</h1>
                <p>Monitor live ward capacity and keep patient flow moving with immediate admission and discharge updates.</p>
            </div>
            <span class="beds-live-status"><span></span> Live database sync</span>
        </header>

        <div id="beds-notice" class="beds-notice" role="status" aria-live="polite" hidden></div>

        <section class="beds-grid" aria-label="Ward capacity dashboard">
            <?php foreach (['ICU', 'General'] as $wardType): ?>
                <?php $ward = $wards[$wardType]; ?>
                <article class="bed-card <?php echo $wardType === 'ICU' ? 'icu-card' : 'general-card'; ?>" data-ward-card="<?php echo htmlspecialchars($wardType, ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="bed-card-topline">
                        <span class="bed-ward-icon" aria-hidden="true"><?php echo $wardType === 'ICU' ? '✚' : '⌂'; ?></span>
                        <span class="bed-ward-label"><?php echo htmlspecialchars($wardType, ENT_QUOTES, 'UTF-8'); ?> Ward</span>
                    </div>

                    <div class="bed-availability">
                        <span>Available beds</span>
                        <strong data-available-beds><?php echo (int) $ward['available_beds']; ?></strong>
                        <p>of <span data-total-beds><?php echo (int) $ward['total_beds']; ?></span> total beds available</p>
                    </div>

                    <div class="bed-progress-block">
                        <div class="bed-progress-label"><span>Current occupancy</span><strong data-occupancy-percentage><?php echo (int) $ward['occupancy_percentage']; ?>%</strong></div>
                        <div class="bed-progress-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo (int) $ward['occupancy_percentage']; ?>">
                            <span class="bed-progress-fill" data-progress-fill style="width: <?php echo (int) $ward['occupancy_percentage']; ?>%"></span>
                        </div>
                        <p><span data-occupied-beds><?php echo (int) $ward['occupied_beds']; ?></span> occupied · <span data-available-text><?php echo (int) $ward['available_beds']; ?></span> available</p>
                    </div>

                    <div class="bed-actions">
                        <button type="button" class="bed-action-button admit-button" data-action="admit" data-ward="<?php echo htmlspecialchars($wardType, ENT_QUOTES, 'UTF-8'); ?>">+ Admit Patient</button>
                        <button type="button" class="bed-action-button discharge-button" data-action="discharge" data-ward="<?php echo htmlspecialchars($wardType, ENT_QUOTES, 'UTF-8'); ?>">− Discharge Patient</button>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    </main>

    <script>
        const csrfToken = <?php echo json_encode($csrfToken); ?>;
        const notice = document.getElementById('beds-notice');

        function showNotice(message, type) {
            notice.textContent = message;
            notice.className = 'beds-notice ' + type;
            notice.hidden = false;
            window.clearTimeout(showNotice.timeout);
            showNotice.timeout = window.setTimeout(function () {
                notice.hidden = true;
            }, 3500);
        }

        function updateWardCard(ward) {
            const card = document.querySelector('[data-ward-card="' + ward.ward_type + '"]');
            const percentage = Number(ward.occupancy_percentage);

            card.querySelector('[data-available-beds]').textContent = ward.available_beds;
            card.querySelector('[data-total-beds]').textContent = ward.total_beds;
            card.querySelector('[data-occupied-beds]').textContent = ward.occupied_beds;
            card.querySelector('[data-available-text]').textContent = ward.available_beds;
            card.querySelector('[data-occupancy-percentage]').textContent = percentage + '%';
            card.querySelector('[data-progress-fill]').style.width = percentage + '%';
            card.querySelector('.bed-progress-track').setAttribute('aria-valuenow', percentage);
        }

        document.querySelectorAll('.bed-action-button').forEach(function (button) {
            button.addEventListener('click', async function () {
                const ward = button.dataset.ward;
                const action = button.dataset.action;
                const wardCard = document.querySelector('[data-ward-card="' + ward + '"]');
                const wardButtons = wardCard.querySelectorAll('.bed-action-button');

                wardButtons.forEach(function (wardButton) {
                    wardButton.disabled = true;
                });

                try {
                    const formData = new FormData();
                    formData.append('ward_type', ward);
                    formData.append('action', action);
                    formData.append('csrf_token', csrfToken);

                    const response = await fetch('beds.php', {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body: formData
                    });
                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Unable to update bed capacity.');
                    }

                    updateWardCard(data.ward);
                    showNotice(data.ward.message, 'beds-notice-success');
                } catch (error) {
                    showNotice(error.message || 'Unable to update bed capacity.', 'beds-notice-error');
                } finally {
                    wardButtons.forEach(function (wardButton) {
                        wardButton.disabled = false;
                    });
                }
            });
        });
    </script>
</body>
</html>