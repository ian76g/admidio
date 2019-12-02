<?php
require_once(dirname(__FILE__) . '/../adm_my_files/config.php');
require_once(dirname(__FILE__) . '/../adm_program/system/bootstrap.php');
require_once(dirname(__FILE__) . '/../adm_program/system/common.php');

/**
 * @param $folder
 */
function sendFile($folder)
{
    $users = array('anke.siemers', 'andreas.euler', 'ulf.koester');
    $file = str_replace(array('/', '\\', '..'), '', $_GET['f']);
    foreach ($users as $user) {
        if (file_exists('/volume1/homes/' . $user . '/' . $folder . '/' . $file)) {
            if(substr($file,-4) != '.ogg') {
                header("Content-type:application/octet-stream");
                header("Content-Disposition:inline;filename='$file");
            }
            readfile('/volume1/homes/' . $user . '/' . $folder . '/' . $file);
            exit;
        }
    }
}

if ($gValidLogin) {
    $bills = array();
    $s = $_SESSION['gCurrentSession'];
    $s = $s->getObject('gCurrentUser');
    $s = $s->getValue('LAST_NAME');

    // LV = 63
    // RV = 64
    // Vorstand = 3


    if ($_GET['g'] == 'V') {
        if (in_array(3, $_SESSION['gCurrentSession']->getObject('gCurrentUser')->getRoleMemberships())) {
            sendFile('VORSTAND');
            $text = "Datei $file existiert nicht";
        } else {
            $text = "Du bist nicht berechtigt die Datei zu sehen - sie ist nur für den Vorstand gedacht.";
        }
    } elseif ($_GET['g'] == 'L') {
        if (
            in_array(63, $_SESSION['gCurrentSession']->getObject('gCurrentUser')->getRoleMemberships()) ||
            in_array(3, $_SESSION['gCurrentSession']->getObject('gCurrentUser')->getRoleMemberships())
        ) {
            sendFile('LV');
            $text = "Datei $file existiert nicht";
        } else {
            $text = "Du bist nicht berechtigt die Datei zu sehen - sie ist nur für die Landesvertreter gedacht.";
        }
    } elseif ($_GET['g'] == 'R') {
        if (
            in_array(64, $_SESSION['gCurrentSession']->getObject('gCurrentUser')->getRoleMemberships()) ||
            in_array(63, $_SESSION['gCurrentSession']->getObject('gCurrentUser')->getRoleMemberships()) ||
            in_array(3, $_SESSION['gCurrentSession']->getObject('gCurrentUser')->getRoleMemberships())
        ) {
            sendFile('RV');
            $text = "Datei $file existiert nicht";
        } else {
            $text = "Du bist nicht berechtigt die Datei zu sehen - sie ist nur für die Regionalleiter gedacht.";
        }
    } elseif ($_GET['g'] == 'M') {
        if (
            in_array(5, $_SESSION['gCurrentSession']->getObject('gCurrentUser')->getRoleMemberships())
        ) {
            sendFile('M');
            $text = "Datei $file existiert nicht";
        } else {
            $text = "Du bist nicht berechtigt die Datei zu sehen - sie ist nur für die Regionalleiter gedacht.";
        }
    } else {
        $text = "Berechtigung nur für Vorstand, LV und RV implementiert";
    }
    /*
        echo "<pre>";
        $x = $_SESSION['gCurrentSession']->getObject('gCurrentUser')->getRoleMemberships();

        print_r($x);

        exit;
    */

} else {
    $text = "Du bist nicht angemeldet.";
}

$headline = 'Zugriffschutz';

// create html page object
$page = new HtmlPage($headline);
$page->enableModal();

// get module menu
$menuMenu = $page->getMenu();

$gNavigation->addUrl(CURRENT_URL, $headline);

// get module menu
$profileMenu = $page->getMenu();

if ($gNavigation->count() > 1) {
    $profileMenu->addItem('menu_item_back', $gNavigation->getPreviousUrl(), $gL10n->get('SYS_BACK'), 'back.png');
}

$myUserId = $gCurrentUser->getValue('usr_id');

if (!$myUserId) {
    $gMessage->show($gL10n->get('SYS_NO_RIGHTS'));
    die();
}


$html = $text;

$page->addHtml($html);
$page->show();
