<?php
/**
 * Registra un accesso dal sito legacy (excursio.org) in user_login_events.
 * La tabella (e la colonna source) sono create dalle migration Laravel su excursio.
 */
function log_user_login_event_legacy($mysqli, $userId)
{
    if (!$mysqli || (int) $userId <= 0) {
        return;
    }

    $uid = (int) $userId;
    $ip = mysqli_real_escape_string($mysqli, $_SERVER['REMOTE_ADDR'] ?? '');
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
            "INSERT INTO user_login_events (user_id, logged_in_at, ip_address, source)
             VALUES ($uid, '$nowLogin', '$ip', 'legacy')"
        );
    } else {
        @mysqli_query(
            $mysqli,
            "INSERT INTO user_login_events (user_id, logged_in_at, ip_address)
             VALUES ($uid, '$nowLogin', '$ip')"
        );
    }

    @mysqli_query($mysqli, "UPDATE utente SET ultimo_accesso = '$nowLogin' WHERE userID = $uid");
}
