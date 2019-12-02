<?php
require_once(dirname(__FILE__) . '/../adm_my_files/config.php');
require_once(dirname(__FILE__) . '/../adm_program/system/bootstrap.php');
require_once(dirname(__FILE__) . '/../adm_program/system/common.php');

//require_once('cf7_export_excel.php');

/**
 *
 */
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

/**
 * @param $sql
 * @return array|null
 */
function query($sql)
{
    global $dbh;
    if (!$dbh) {
        connect();
    }

    $rh = mysqli_query($dbh, $sql);
    if (!$rh) {
        echo mysqli_error($dbh);
        die($sql);
        return array();
    }
    $result = mysqli_fetch_all($rh, MYSQLI_ASSOC);

    return $result;
}


/**
 *
 */
function oldCode()
{


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


    $result = query($sql);

    $fp = fopen('php://memory', 'w');
    fputcsv($fp, array_keys($result[0]));
    for ($i = 0; $i < sizeof($result); $i++) {
        fputcsv($fp, $result[$i]);
    }
    rewind($fp);
// put it all in a variable
    $output = stream_get_contents($fp);

    $filename = 'AdresslisteAbcClub-' . date('Ymd') . '-' . substr(md5(time()), 0, 5) . '.csv';

    file_put_contents($filename, $output);

    header('Content-Disposition: attachment; filename=' . $filename);
    header('Content-type: text/csv');
    header('Content-Length: ' . filesize($filename));
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    readfile($filename);
    unlink($filename);
    die;
}


list($database, $hashData, $admins) = foo();
$hash = substr(md5($hashData), 0, 5);
if(in_array($hash, $admins)) {
    oldCode();
}

?>
    <html>
    <head>
        <style>
            * {
                font-family: Verdana;
                font-size: 8pt;
            }
        </style>
    </head>
    <body>
    <div style="border:0px solid black;margin:auto;width:500px;">
        <?php
        htmloutput($database, $hashData, $admins);
        ?>
    </div>

    </body>
    </html>

<?php
/**
 *
 * HTTP_X_REAL_IP: 66.249.93.44
 * HTTP_ACCEPT: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,**;q=0.8,application/signed-exchange;v=b3
 * HTTP_ACCEPT_ENCODING: gzip, deflate
 * HTTP_ACCEPT_LANGUAGE: de-DE,de;q=0.9,en-US;q=0.8,en;q=0.7,cs;q=0.6
 * HTTP_FORWARDED: for=80.187.80.90
 * HTTP_X_FORWARDED_FOR: 80.187.80.90
 * HTTP_USER_AGENT: Mozilla/5.0 (Linux; Android 8.1.0; Nexus 4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.90 Mobile Safari/537.36
 * REMOTE_ADDR: 66.249.93.44
 */
function foo()
{
    $usefullTags = array(
        'HTTP_X_REAL_IP',
        'HTTP_ACCEPT',
        'HTTP_ACCEPT_ENCODING',
        'HTTP_ACCEPT_LANGUAGE',
        'HTTP_FORWARDED',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_USER_AGENT',
        'REMOTE_ADDR',
    );

    $admins = unserialize(file_get_contents('databaseAdmins'));
    if (!$admins) $admins = array();


    $database = unserialize(file_get_contents('database'));
    if (!$database) $database = array();

    $hashData = '';
    foreach ($usefullTags as $tag) {
        if (isset($_SERVER[$tag])) {
            $hashData .= $tag . ': ' . $_SERVER[$tag] . '<br>';
        }
    }
    $hash = substr(md5($hashData), 0, 5);

    if (isset($_GET['allow']) && $_GET['allow'] != $hash) {
        $admins[] = $_GET['allow'];
    }
    if (isset($_GET['revoke']) && $_GET['revoke'] != $hash) {
        $admins[] = $_GET['revoke'];
    }

    $database[$hash] = $hashData;
    file_put_contents('database', serialize($database));
    file_put_contents('databaseAdmins', serialize($admins));
    return array($database, $hashData, $admins);
}

function htmloutput($database, $hashData, $admins)
{
    $hash = substr(md5($hashData), 0, 5);
    echo "<hr>";
    echo "Ich kenne " . sizeof($database) . " Clients<br>";
    echo "<hr>";
    echo "<br>";
    echo "Dich nenne ich \"" . substr(md5($hashData), 0, 5) . "\"<br>";
    echo "<br>";
    echo "<hr>";
    echo "<br>";
    echo "Ich erkenne Dich an folgenden Parametern<br>";
    echo "<table>";
    $lines = explode("<br>", $hashData);
    foreach ($lines as $line) {
        echo "<tr><td align='right'>" . str_replace(': ', '</td><td>', str_replace(array(',', ';'), ' ', $line)) . "</td></tr>\n";
    }
    echo "</table>";
    //echo $hashData;
    echo "<br>";
    echo "<hr>";
    echo "<br>";
    if (in_array($hash, $admins)) {
        echo "Der Client \"" . $hash . "\" ist berechtigt die Adressdaten einzusehen!<br>";
    } else {
        echo "Der Client \"" . $hash . "\" ist nicht berechtigt die Adressdaten einzusehen!<br>";
    }
    echo "<br>";
    echo "<hr>";
    if (isset($_GET['database']) && in_array($hash, $admins)) {
        echo "<pre>";
        print_r($admins);
        print_r($database);
        echo "</pre>";
    }
}


?>