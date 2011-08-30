<?php
/******************************************************************************
 * Passwort neu vergeben
 *
 * Copyright    : (c) 2004 - 2011 The Admidio Team
 * Homepage     : http://www.admidio.org
 * License      : GNU Public License 2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Uebergaben:
 *
 * usr_id     - Passwort der übergebenen User-Id aendern
 * mode   : 0 - (Default) Anzeige des Passwordaenderungsformulars
 *          1 - Passwortaenderung wird verarbeitet
 * inline : 1 - für z.B.: colorbox nur Content ohne Header wird angezeigt
 *
 *****************************************************************************/
 
require_once('../../system/common.php');
require_once('../../system/login_valid.php');

$g_message->setExcludeThemeBody();
 
// Uebergabevariablen pruefen und ggf. initialisieren
$get_usr_id = admFuncVariableIsValid($_GET, 'usr_id', 'numeric', null, true);
$get_inline = admFuncVariableIsValid($_GET, 'inline', 'numeric', 1);
$get_mode   = admFuncVariableIsValid($_GET, 'mode', 'numeric', 0);

// nur Webmaster duerfen fremde Passwoerter aendern
if($g_current_user->isWebmaster() == false && $g_current_user->getValue('usr_id') != $get_usr_id)
{
    $g_message->show($g_l10n->get('SYS_NO_RIGHTS'));
}


if($get_mode == 1)
{
    /***********************************************************************/
    /* Formular verarbeiten */
    /***********************************************************************/
    if($g_current_user->isWebmaster() && $g_current_user->getValue('usr_id') != $get_usr_id )
    {
        $_POST['old_password'] = '';
    }
    
    if( (strlen($_POST['old_password']) > 0 || $g_current_user->isWebmaster() )
    && strlen($_POST['new_password']) > 0
    && strlen($_POST['new_password2']) > 0)
    {
        if(strlen($_POST['new_password']) > 5)
        {
            if ($_POST['new_password'] == $_POST['new_password2'])
            {
                // pruefen, ob altes Passwort korrekt eingegeben wurde              
                $user = new User($g_db, $get_usr_id);
                $old_password_crypt = md5($_POST['old_password']);

                // Webmaster duerfen fremde Passwörter so aendern
                if($user->getValue('usr_password') == $old_password_crypt || $g_current_user->isWebmaster() && $g_current_user->getValue('usr_id') != $get_usr_id )
                {
                    $user->setValue('usr_password', $_POST['new_password']);
                    $user->save();

                    // Paralell im Forum aendern, wenn Forum aktiviert ist
                    if($g_preferences['enable_forum_interface'])
                    {
                        $g_forum->userSave($user->getValue('usr_login_name'), $user->getValue('usr_password'), $user->getValue('EMAIL'), '', 3);
                    }

                    // wenn das PW des eingeloggten Users geaendert wird, dann Session-Variablen aktualisieren
                    if($user->getValue('usr_id') == $g_current_user->getValue('usr_id'))
                    {
                        $g_current_user->setValue('usr_password', $user->getValue('usr_password'));
                    }

                    $g_message->setForwardUrl('javascript:self.parent.tb_remove()');
                    $phrase = $g_l10n->get('PRO_PASSWORD_CHANGED')."<SAVED/>";
                }
                else
                {
                    $phrase = $g_l10n->get('PRO_PASSWORD_OLD_WRONG');
                }
            }
            else
            {
                $phrase = $g_l10n->get('PRO_PASSWORDS_NOT_EQUAL');
            }
        }
        else
        {
            $phrase = $g_l10n->get('PRO_PASSWORD_LENGTH');
        }
    }
    else
    {
        $phrase = $g_l10n->get('SYS_FIELDS_EMPTY');
    }
	if ($get_inline == 0)
	{
		$g_message->setExcludeThemeBody();
		$g_message->show($phrase);
	}
	else
	{
		echo $phrase;
	}
}
else
{
    /***********************************************************************/
    /* Passwortformular anzeigen */
    /***********************************************************************/
    
    // Html-Kopf ausgeben
    $g_layout['title']    = $g_l10n->get('PRO_EDIT_PASSWORD');
    $g_layout['includes'] = false;
    if ($get_inline == 0)
	{
		require(SERVER_PATH. '/adm_program/system/overall_header.php');
	}

    // Html des Modules ausgeben
    echo '
    <form id="passwordForm" action="'. $g_root_path. '/adm_program/modules/profile/password.php?usr_id='. $get_usr_id. '&amp;mode=1&amp;inline=1" method="post">
    <div class="formLayout" id="password_form" style="width: 300px">
        <div class="formHead">'. $g_layout['title']. '</div>
        <div class="formBody">
            <ul class="formFieldList">';
                if($g_current_user->getValue('usr_id') == $get_usr_id )
                {
                echo'
                    <li>
                        <dl>
                            <dt><label for="old_password">'.$g_l10n->get('PRO_CURRENT_PASSWORD').':</label></dt>
                            <dd><input type="password" id="old_password" name="old_password" size="12" maxlength="20" />
                                <span class="mandatoryFieldMarker" title="'.$g_l10n->get('SYS_MANDATORY_FIELD').'">*</span></dd>
                        </dl>
                    </li>
                    <li><hr /></li>';
                }    
                echo'
                <li>
                    <dl>
                        <dt><label for="new_password">'.$g_l10n->get('PRO_NEW_PASSWORD').':</label></dt>
                        <dd><input type="password" id="new_password" name="new_password" size="12" maxlength="20" />
                            <span class="mandatoryFieldMarker" title="'.$g_l10n->get('SYS_MANDATORY_FIELD').'">*</span>
                            <img onmouseover="ajax_showTooltip(event,\''.$g_root_path.'/adm_program/system/msg_window.php?message_id=PRO_PASSWORD_DESCRIPTION\',this)" onmouseout="ajax_hideTooltip()"
                                class="iconHelpLink" src="'. THEME_PATH. '/icons/help.png" alt="'.$g_l10n->get('SYS_HELP').'" title="" />
                        </dd>
                    </dl>
                </li>
                <li>
                    <dl>
                        <dt><label for="new_password2">'.$g_l10n->get('SYS_REPEAT').':</label></dt>
                        <dd><input type="password" id="new_password2" name="new_password2" size="12" maxlength="20" />
                            <span class="mandatoryFieldMarker" title="'.$g_l10n->get('SYS_MANDATORY_FIELD').'">*</span></dd>
                    </dl>
                </li>
            </ul>

            <hr />

            <div class="formSubmit">
                <button id="btnSave" type="submit"><img src="'. THEME_PATH. '/icons/disk.png" alt="'.$g_l10n->get('SYS_SAVE').'" />&nbsp;'.$g_l10n->get('SYS_SAVE').'</button>
            </div>
        </div>
    </form>';
    if ($get_inline == 0)
	{  
		require(SERVER_PATH. '/adm_program/system/overall_footer.php');
	}
}

?>