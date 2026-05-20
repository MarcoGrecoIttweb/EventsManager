<?php
/**
 * Registra un accesso dal sito legacy (excursio.org) in user_login_events.
 * Compatibile PHP 5.4+ (niente operatore ??).
 */
function log_user_login_event_legacy($mysqli, $userId)
{
    if (!$mysqli || (int) $userId <= 0) {
        return;
    }

    $uid = (int) $userId;
    $ipRaw = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
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
