<?php
require_once(dirname(__FILE__) . '/../adm_my_files/config.php');
require_once(dirname(__FILE__) . '/../adm_program/system/bootstrap.php');
require_once(dirname(__FILE__) . '/../adm_program/system/common.php');

if ($gValidLogin) {
    $s = $_SESSION['gCurrentSession'];
    $s = $s->getObject('gCurrentUser');
    $s = $s->getValue('LAST_NAME');

	if (in_array(3, 
		$_SESSION['gCurrentSession']->getObject('gCurrentUser')->getRoleMemberships())) {
			
		if(isset($_POST) && !empty($_POST)){
			createUser();
			echo "erledigt";
		} else {
			?>
			<html><head></head><body>
			<form method="POST">
			ID der ELTERN:<input name="id"><br>
			FAMILIENNAME:<input name="name"><br>
			VORNAMEN:<input name="vor1"><input name="vor2"><input name="vor3"><br>
			GEBDAT YYYY-MM-DD:<input name="gebdat"><br>
			<input type="submit" value="EINTRAGEN">
			</BODY></html>
			<?php
		}
	
		
	} else {
		echo "berechtigungen reichen nicht aus!";
	}
} else {
	die("nicht angemeldet!");
}


function createUser()
{
	global $dbh;
	
	$sql = "insert into tbl_users (usr_id, usr_valid) values (NULL, 1)";
	query($sql);
	$user = $dbh->insert_id;
	// 1, 2, 10
	query("insert into tbl_user_data values (NULL, ". $user .", 1, '".$_POST["name"]."')");
	query("insert into tbl_user_data values (NULL, ". $user .", 2, '".$_POST["vor1"]."')");
	query("insert into tbl_user_data values (NULL, ". $user .", 10, '".$_POST["gebdat"]."')");

	query("insert into tbl_user_relations values (NULL, 1, ".$user.",".$_POST['id'].", 2, NOW(), NULL, NULL)");
	query("insert into tbl_user_relations values (NULL, 2, ".$_POST["id"].",".$user.", 2, NOW(), NULL, NULL)");
	
	$sql = "insert into tbl_users (usr_id, usr_valid) values (NULL, 1)";
	query($sql);
	$user = $dbh->insert_id;
	// 1, 2, 10
	query("insert into tbl_user_data values (NULL, ". $user .", 1, '".$_POST["name"]."')");
	query("insert into tbl_user_data values (NULL, ". $user .", 2, '".$_POST["vor2"]."')");
	query("insert into tbl_user_data values (NULL, ". $user .", 10, '".$_POST["gebdat"]."')");

	query("insert into tbl_user_relations values (NULL, 1, ".$user.",".$_POST['id'].", 2, NOW(), NULL, NULL)");
	query("insert into tbl_user_relations values (NULL, 2, ".$_POST["id"].",".$user.", 2, NOW(), NULL, NULL)");
	
	$sql = "insert into tbl_users (usr_id, usr_valid) values (NULL, 1)";
	query($sql);
	$user = $dbh->insert_id;
	// 1, 2, 10
	query("insert into tbl_user_data values (NULL, ". $user .", 1, '".$_POST["name"]."')");
	query("insert into tbl_user_data values (NULL, ". $user .", 2, '".$_POST["vor3"]."')");
	query("insert into tbl_user_data values (NULL, ". $user .", 10, '".$_POST["gebdat"]."')");

	query("insert into tbl_user_relations values (NULL, 1, ".$user.",".$_POST['id'].", 2, NOW(), NULL, NULL)");
	query("insert into tbl_user_relations values (NULL, 2, ".$_POST["id"].",".$user.", 2, NOW(), NULL, NULL)");

}


function connect()
{
    global $g_adm_srv;      // Server
    global $g_adm_port;        // Port
    global $g_adm_usr;        // User
    global $g_adm_pw;    // Password
    $g_adm_db   = 'admidio';

    global $dbh;

    $dbh = mysqli_connect($g_adm_srv, $g_adm_usr, $g_adm_pw, $g_adm_db, $g_adm_port);
}

function query($sql)
{
    global $dbh;
    if(!$dbh){
        connect();
    }

    $rh = mysqli_query($dbh, $sql);
    if(!$rh) {
        echo mysqli_error($dbh);
        die($sql);
        return array();
    }
    $result = mysqli_fetch_all($rh, MYSQLI_ASSOC);

    return $result;
}
