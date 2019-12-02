<?php
require_once(dirname(__FILE__) . '/../adm_my_files/config.php');
require_once(dirname(__FILE__) . '/../adm_program/system/bootstrap.php');
require_once(dirname(__FILE__) . '/../adm_program/system/common.php');

if($gValidLogin) {
	$bills = array();
	$s = $_SESSION['gCurrentSession'];
	$s = $s->getObject('gCurrentUser');
	$s = $s->getValue('LAST_NAME');
	$d = opendir('/volume1/homes/anke.siemers/RECHNUNGEN/');
	while($d && $file=readdir($d)){
		if(
		strpos($file, date('Y').' '.$s.' Rechnung')!== false ||
		strpos($file, (date('Y')-1).' '.$s.' Rechnung')!== false
		){
			$bills[] = $file;
			if(isset($_GET['file']) && md5($file) == $_GET['file']) {
				header("Content-type:application/pdf");
				header("Content-Disposition:inline;filename='$file");
				readfile('/volume1/homes/anke.siemers/RECHNUNGEN/'.$file);
				exit;
			}
		}
	}
	if(!$bills)
		$text =  "Du hast keine Rechnung.";
} else {
	$text =  "Du bist nicht angemeldet.";
}

$headline = 'Meine Rechnungen';

// create html page object
$page = new HtmlPage($headline);
$page->enableModal();

// get module menu
$menuMenu = $page->getMenu();

$gNavigation->addUrl(CURRENT_URL, $headline);

// get module menu
$profileMenu = $page->getMenu();

if($gNavigation->count() > 1)
{
    $profileMenu->addItem('menu_item_back', $gNavigation->getPreviousUrl(), $gL10n->get('SYS_BACK'), 'back.png');
}

$myUserId = $gCurrentUser->getValue('usr_id');

if(!$myUserId) {
        $gMessage->show($gL10n->get('SYS_NO_RIGHTS'));
        die();
}


$html = $text;
if($bills){
	$color = false;
	$html .= '<table width="100%"><tr><th>Rechnung</th><th>Downloadlink</th></tr>';
	foreach($bills as $bill) {
		$html.= '<trbgcolor="#'.($color?'ffe4a0':'FFFFFF').'"><td>'.$bill.'</td><td><a href="'.$g_root_path.'/public/meineRechnung.php?file='.md5($bill).'">Download</A></td></tr>';
		$color = !$color;
	}
	$html .= '</table>';
}

$page->addHtml($html);
$page->show();
