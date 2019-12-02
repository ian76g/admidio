<?php
require_once(dirname(__FILE__) . '/../adm_my_files/config.php');
require_once(dirname(__FILE__) . '/../adm_program/system/bootstrap.php');
require_once(dirname(__FILE__) . '/../adm_program/system/common.php');

require_once('cf7_export_excel.php');


if(!isset($_POST['password']) || $_POST['password'] != 'ABC-Club e.V.'){
    echo '<html><head></head><body><form method="POST">Passwort: <input type="password" name="password"><input type="submit" name="Adressliste"></form></body>';

    die();
}


function connect()
{
    global $g_adm_srv;      // Server
    global $g_adm_port;        // Port
    global $g_adm_usr;        // User
    global $g_adm_pw;    // Password
    global $g_adm_db;

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




$sql = <<<EOF
select
'ABC-Club e.V., Schuhstr.4, 30159 Hannover' as Absender, 
'Postvertriebsstück, DPAG, Entgelt bezahlt' as Versandart,
UD1.usd_usr_id as 'Mitgliedsnummer', 'Familie' as Anrede,  
CONCAT(UD2.usd_value , ' ', UD1.usd_value) as `Namen`, 
' ' as 'Namenergänzung',
UD3.usd_value as Strasse, 
UD4.usd_value as PLZ, 
UD5.usd_value as Ort, 
IF(UD6.usd_value = 'DEU', '', 
IF(UD6.usd_value = 'CHE', 'Schweiz',
IF(UD6.usd_value = 'AUT', 'Österreich',
IF(UD6.usd_value = 'FRA', 'France',
IF(UD6.usd_value = 'NLD', 'Niederlande',
IF(UD6.usd_value = 'BEL', 'Belgien',
IF(UD6.usd_value = 'BOL', 'Bolivia',
UD6.usd_value
))))))) as Land
 from
tbl_user_data UD1
join tbl_user_data UD2 on UD1.usd_usr_id = UD2.usd_usr_id
join tbl_user_data UD3 on UD1.usd_usr_id = UD3.usd_usr_id 
join tbl_user_data UD4 on UD1.usd_usr_id = UD4.usd_usr_id 
join tbl_user_data UD5 on UD1.usd_usr_id = UD5.usd_usr_id 
left join tbl_user_data UD6 on UD1.usd_usr_id = UD6.usd_usr_id and UD6.usd_usf_id = 6
join tbl_users U on UD1.usd_usr_id = U.usr_id
join tbl_members M on M.mem_usr_id = U.usr_id
join tbl_roles R on M.mem_rol_id = R.rol_id

WHERE
UD1.usd_usf_id = 1 and UD2.usd_usf_id = 2 and UD3.usd_usf_id = 3 and UD4.usd_usf_id = 4 and UD5.usd_usf_id = 5
AND (R.rol_name like '!%' or R.rol_name = 'Versandanschriften')
AND M.mem_begin <= DATE(NOW())
AND M.mem_end >= DATE(NOW())
AND (U.usr_login_name not like 'admin.%' or U.usr_login_name is null)
order by UD4.usd_value
EOF;

//$sql="select * from tbl_user_data where usd_usr_id = 100832";


$result = query($sql);
//echo '<pre>';
//print_r($result);
//die();

//$fp = fopen('php://memory', 'w');
////add BOM to fix UTF-8 in Excel
//fputs($fp, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
//fputcsv($fp, array_keys($result[0]));
//for($i=0; $i<sizeof($result); $i++){
//    fputcsv($fp, $result[$i]);
//}
//rewind($fp);
//// put it all in a variable
//$output = stream_get_contents($fp);
//file_put_contents('AdresslisteBOM.csv', $output);

$fp = fopen('php://memory', 'w');
fputcsv($fp, array_keys($result[0]));
for($i=0; $i<sizeof($result); $i++){
    fputcsv($fp, $result[$i]);
}
rewind($fp);
// put it all in a variable
$output = stream_get_contents($fp);

$filename = 'AdresslisteAbcClub-'.date('Ymd').'-'.substr(md5(time()),0,5).'.csv';

file_put_contents($filename, $output);

header('Content-Disposition: attachment; filename=' . $filename );
header('Content-type: text/csv');
header('Content-Length: ' . filesize($filename));
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
readfile($filename);
unlink($filename);


die;
$obj = new cf7_export_excel();
$obj->add_row(array_keys($result[0]));
$obj->add_rows($result);

$file = $obj->xlsx_save();
$filename = 'Adressliste.xlsx';

header('Content-Disposition: attachment; filename=' . $filename );
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Length: ' . filesize($file));
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
readfile($file);
unlink($file);
