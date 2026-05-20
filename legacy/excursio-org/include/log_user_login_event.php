<?php
// log_user_login_event.php — versione PHP 5.6 (excursio.org)
// Verifica in cPanel che la riga IP non contenga "??"

function log_user_login_event_legacy($mysqli, $userId)
{
    if (!$mysqli) {
        return;
    }

    $uid = (int) $userId;
    if ($uid <= 0) {
        return;
    }

    $ipRaw = '';
    if (isset($_SERVER['REMOTE_ADDR'])) {
        $ipRaw = $_SERVER['REMOTE_ADDR'];
    }
    $ip = mysqli_real_escape_string($mysqli, $ipRaw);
    $nowLogin = date('Y-m-d H:i:s');

    $tableCheck = @mysqli_query($mysqli, "SHOW TABLES LIKE 'user_login_events'");
    if (!$tableCheck || mysqli_num_rows($tableCheck) === 0) {
        @mysqli_query($mysqli, "UPDATE utente SET ultimo_accesso = '$nowLogin' WHERE userID = $uid");
        return;
    }

    $hasSource = false;
    $colCheck = @mysqli_query($mysqli, "SHOW COLUMNS FROM `user_login_events` LIKE 'source'");
    if ($colCheck && mysqli_num_rows($colCheck) > 0) {
        $hasSource = true;
    }

    if ($hasSource) {
        @mysqli_query(
            $mysqli,
            "INSERT INTO user_login_events (user_id, logged_in_at, ip_address, source) " .
            "VALUES ($uid, '$nowLogin', '$ip', 'legacy')"
        );
    } else {
        @mysqli_query(
            $mysqli,
            "INSERT INTO user_login_events (user_id, logged_in_at, ip_address) " .
            "VALUES ($uid, '$nowLogin', '$ip')"
        );
    }

    @mysqli_query($mysqli, "UPDATE utente SET ultimo_accesso = '$nowLogin' WHERE userID = $uid");
}
