<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

/*
script cree par twitter:@Havok pour la eedomus

------------------- Parametres :
mode = did - Récupération des identifiants did des appareils
mode = set - Modification de l'etat de l'appareil
  ordre = 0 confort
  ordre = 1 eco
  ordre = 2 hors-gel
  ordre = 3 off
*/

//--- Paramètres Heatzy
$heatzy_username = "";
$heatzy_password = "";
//$did = '';

//--- Paramètres Script
$debug = false;
$heatzy_application_id = "c70a66ff039d41b4a220e198b0fcc8b3";


define('__ROOT__', dirname(dirname(__FILE__)));

//-------------- Parametres
if (isset($_GET['mode'])) $mode = $_GET['mode'];
if (isset($_GET['ordre'])) $ordre = $_GET['ordre'];
if (isset($_GET['did'])) $did = $_GET['did'];

//*************   Récupération du token, validité 7 jours
// POST /app/login (User login)
function heatzy_login($login, $password, $appid) {

  global $debug;

  $curl = curl_init(); //Première étape, initialiser une nouvelle session cURL.
  curl_setopt($curl, CURLOPT_URL, 'https://euapi.gizwits.com/app/login'); //Il va par exemple falloir lui fournir l'url de la page à récupérer.
  curl_setopt($curl, CURLOPT_POST, true); //Pour envoyer une requête POST, il va alors tout d'abord dire à la fonction de faire un HTTP POST
  curl_setopt ($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json","Accept: application/json","X-Gizwits-Application-Id: ".$appid));

  $postfields = array(
    'username' => $login,
    'password' => $password,
    'lang' => 'en'
  );
  $postfields = json_encode($postfields);
  curl_setopt($curl, CURLOPT_POSTFIELDS, $postfields);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //Cette option permet d'indiquer que nous voulons recevoir le résultat du transfert au lieu de l'afficher.

  $return = curl_exec($curl); //Il suffit ensuite d'exécuter la requête
  curl_close($curl);
/*
  $obj = json_decode($return);
  return $obj->{'token'}; // 12345*/
  $obj = json_decode($return,true);
  if ($debug==true) { error_log(date("d-m-Y H:i:s").' heatzy_login: '.print_r($obj,true)."\n", 3, "HZ.log"); }
  return $obj['token'];
}

//*************   Récupération did (identifiant de l'appareil (validité permanente))
// GET /app/bindings
function heatzy_bindings($token, $appid) {

  global $debug;

  $curl = curl_init(); //Première étape, initialiser une nouvelle session cURL.
  curl_setopt($curl, CURLOPT_URL, 'https://euapi.gizwits.com/app/bindings?limit=20&skip=0'); //Il va par exemple falloir lui fournir l'url de la page à récupérer.
  curl_setopt($curl, CURLOPT_HTTPGET, true); //Pour envoyer une requête POST, il va alors tout d'abord dire à la fonction de faire un HTTP POST
  curl_setopt ($curl, CURLOPT_HTTPHEADER, array("Accept: application/json","X-Gizwits-Application-Id: ".$appid,"X-Gizwits-User-token: ".$token));
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //Cette option permet d'indiquer que nous voulons recevoir le résultat du transfert au lieu de l'afficher.

  $return = curl_exec($curl); //Il suffit ensuite d'exécuter la requête
  curl_close($curl);

  $obj = json_decode($return,true);
  if ($debug==true) { error_log(date("d-m-Y H:i:s").' heatzy_bindings: '.print_r($obj,true)."\n", 3, "HZ.log"); }

  reset($obj['devices']);
  $result = "<table border='1'><t<thead><tr><th>N°</th><th>Nom</th><th>@Mac</th><th>did</th></tr></thead><tbody>";
  while ($i = current($obj['devices']))
  {
    $result .= "<tr>";
    $result .=  "<td>".key($obj['devices'])."</td>";
    $result .=  "<td>".$obj['devices'][key($obj['devices'])]['dev_alias']."</td>";
    $result .=  "<td>".$obj['devices'][key($obj['devices'])]['mac']."</td>";
    $result .=  "<td>".$obj['devices'][key($obj['devices'])]['did']."</td>";
    next($obj['devices']);
    $result .= "</tr>";
  }
  $result .= "</tbody></table>";

  return $result;
  //return $obj['devices'][0]['did'];
}

//*************   Récupération de l'etat de l'appareil
// GET /app/devdata/{did}/latest
function heatzy_getstatus($diditem, $appid) {

  global $debug;

  $curl = curl_init(); //Première étape, initialiser une nouvelle session cURL.
  curl_setopt($curl, CURLOPT_URL, 'https://euapi.gizwits.com/app/devdata/'.$diditem.'/latest'); //Il va par exemple falloir lui fournir l'url de la page à récupérer.
  curl_setopt($curl, CURLOPT_HTTPGET, true); //Pour envoyer une requête POST, il va alors tout d'abord dire à la fonction de faire un HTTP POST
  curl_setopt ($curl, CURLOPT_HTTPHEADER, array("Accept: application/json","X-Gizwits-Application-Id: ".$appid));
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //Cette option permet d'indiquer que nous voulons recevoir le résultat du transfert au lieu de l'afficher.

  $return = curl_exec($curl); //Il suffit ensuite d'exécuter la requête
  curl_close($curl);

  $obj = json_decode($return,true);
  if ($debug==true) { error_log(date("d-m-Y H:i:s").' heatzy_getstatus: '.print_r($obj,true)."\n", 3, "HZ.log"); }

  $etatch = $obj['attr']['mode'];
  switch ($etatch) {
    case '舒适':
      $etat = 0; // 'confort';
      break;
    case '停止':
      $etat = 3; //'off';
      break;
    case '经济':
      $etat = 1; //'eco';
      break;
    case '解冻':
      $etat = 2; //'hors-gel';
      break;
    default:
      $etat = 99; //'inconnu';
      break;
  }
  return $etat;
  //return $obj['devices'][0]['did'];
}

//*************   Mise en forme xml du status pour la eedomus
function heatzy_eedomusstatus($status) {

  $eestatus = "<root><status>";
  switch ($status) {
    case '0':
      $eestatus .= '<libelle>confort</libelle><value>0</value>';
      break;
    case '1':
      $eestatus .= '<libelle>eco</libelle><value>1</value>';
      break;
    case '2':
      $eestatus .= '<libelle>hors-gel</libelle><value>2</value>';
      break;
    case '3':
      $eestatus .= '<libelle>off</libelle><value>3</value>';
      break;

    default:
      # code...
      break;
  }
  $eestatus .= "</status></root>";
  return $eestatus;
}

//*************   Modification de l'etat de l'appareil
// POST /app/control/{did}
function heatzy_setstatus($diditem, $appid, $token, $ordre) {

  global $debug;

  if ($ordre !=0 && $ordre !=1 && $ordre !=2 && $ordre !=3) {
    return 'code ordre incorrect';
  }
  $ordreapi = array(1,1,$ordre+0);

  $curl = curl_init(); //Première étape, initialiser une nouvelle session cURL.
  curl_setopt($curl, CURLOPT_URL, 'https://euapi.gizwits.com/app/control/'.$diditem); //Il va par exemple falloir lui fournir l'url de la page à récupérer.
  curl_setopt($curl, CURLOPT_POST, true); //Pour envoyer une requête POST, il va alors tout d'abord dire à la fonction de faire un HTTP POST
  curl_setopt ($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json","Accept: application/json","X-Gizwits-Application-Id: ".$appid,"X-Gizwits-User-token: ".$token));

  $postfields = array(
    'raw' => $ordreapi
  );
  $postfields = json_encode($postfields);
  curl_setopt($curl, CURLOPT_POSTFIELDS, $postfields);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //Cette option permet d'indiquer que nous voulons recevoir le résultat du transfert au lieu de l'afficher.

  $return = curl_exec($curl); //Il suffit ensuite d'exécuter la requête
  curl_close($curl);

  $obj = json_decode($return,true);
  if ($debug==true) { error_log(date("d-m-Y H:i:s").' heatzy_setstatus: '.print_r($obj,true)."\n", 3, "HZ.log"); }

  return $obj;
}

if (isset($mode) && $mode == 'did') {
  $token = heatzy_login($heatzy_username,$heatzy_password,$heatzy_application_id ); // récupération du token
  $did = heatzy_bindings($token,$heatzy_application_id); // récupération du did
  echo $did;
} elseif (isset($mode) && $mode == 'set') {
  $token = heatzy_login($heatzy_username,$heatzy_password,$heatzy_application_id ); // récupération du token
  $result = heatzy_setstatus($did,$heatzy_application_id,$token,$ordre); // définition du status actuel
} else {
  $status = heatzy_getstatus($did,$heatzy_application_id); //récupération du status actuel
  echo heatzy_eedomusstatus($status); //mise en forme du xml pour eedomus
}
 ?>
