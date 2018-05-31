<?php
require_once(dirname(__FILE__) . '/../adm_my_files/config.php');
require_once(dirname(__FILE__) . '/../adm_program/system/bootstrap.php');
require_once(dirname(__FILE__) . '/../adm_program/system/common.php');

if($gValidLogin) {
	$s = $_SESSION['gCurrentSession'];
	$s = $s->getObject('gCurrentUser');
	$s = $s->getValue('LAST_NAME');
	$d = opendir('/volume1/homes/anke.siemers/RECHNUNGEN/');
	while($d && $file=readdir($d)){
		if(strpos($file, '2018 '.$s.' Rechnung')!== false){
			header("Content-type:application/pdf");
			header("Content-Disposition:inline;filename='$file");
			readfile('/volume1/homes/anke.siemers/RECHNUNGEN/'.$file);
			exit;
		}
	}
	echo "Du hast keine Rechnung.";
} else {
	echo "Du bist nicht angemeldet.";
}


//$s->