<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

/*
script cree par twitter:@Havok pour la eedomus

------------------- Parametres :

user = login du compte heatzy_username
pass = password du compte heatzy_username
device = nom du module heatzy
mode = set - Modification de l'etat de l'appareil
  ordre = 0 confort/ 1 eco/ 2 hors-gel/ 3 off
*/
//require 'eedomus.lib.php';

//--- Paramètres Script
$heatzy_application_id = "c70a66ff039d41b4a220e198b0fcc8b3";

//-------------- Parametres
$mode = getArg('mode');
$ordre = getArg('ordre');
$heatzy_username = getArg('user');
$heatzy_password = getArg('pass');
$heatzy_devicename = getArg('device');

//*************   Récupération du token, validité 7 jours
// POST /app/login (User login)
function sdk_heatzy_login($login, $password, $appid) {

  $postfields = '{"username":"'.$login.'","password":"'.$password.'","lang":"en"}';

  $return = httpQuery('https://euapi.gizwits.com/app/login','POST',$postfields,NULL,array("Content-Type: application/json","Accept: application/json","X-Gizwits-Application-Id: ".$appid));
  $obj = sdk_json_decode($return);
  //print_r($obj);
  saveVariable('tokenheatzy',$obj['token']);
  saveVariable('tokenexpireat',$obj['expire_at']);
  return $obj['token'];
}


//*************   Récupération did (identifiant de l'appareil (validité permanente)) en fonction du nom de l'appareil
// GET /app/bindings
function sdk_heatzy_getdidbyname($token, $appid, $devicename) {

  $return = httpQuery('https://euapi.gizwits.com/app/bindings?limit=20&skip=0','GET',NULL,NULL,array("Accept: application/json","X-Gizwits-Application-Id: ".$appid,"X-Gizwits-User-token: ".$token));

  $obj = sdk_json_decode($return);
  //print_r($obj);

  foreach($obj['devices'] as $device) {
    if (strtolower(trim($device['dev_alias'])) == strtolower(trim($devicename))) {
      $did = $device['did'];
      saveVariable('did-'.strtolower(trim($devicename)),$did);
      return $did;
    }
  }

/*
  while ($i = current($obj['devices']))
  {
    if (strtolower(trim($obj['devices'][key($obj['devices'])]['dev_alias'])) == strtolower(trim($devicename))) {
      $did = $obj['devices'][key($obj['devices'])]['did'];
      saveVariable('did-'.strtolower(trim($devicename)),$did);
      return $did;
    }
    next($obj['devices']);
  }
*/
}


//*************   Récupération de l'etat de l'appareil
// GET /app/devdata/{did}/latest
function sdk_heatzy_getstatus($diditem, $appid) {

  $return = httpQuery('https://euapi.gizwits.com/app/devdata/'.$diditem.'/latest','GET',NULL,NULL,array("Accept: application/json","X-Gizwits-Application-Id: ".$appid));

  $obj = sdk_json_decode($return);
  //print_r($obj);

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
}

//*************   Mise en forme xml du status pour la eedomus
function sdk_heatzy_eedomusstatus($status) {

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
function sdk_heatzy_setstatus($diditem, $appid, $token, $ordre) {

  if ($ordre !=0 && $ordre !=1 && $ordre !=2 && $ordre !=3) {
    return 'code ordre incorrect';
  }
  $ordreapi = $ordre+0;

  $postfields = '{"raw":[1,1,'.$ordreapi.']}';

  $return = httpQuery('https://euapi.gizwits.com/app/control/'.$diditem,'POST',$postfields,NULL,array("Content-Type: application/json","Accept: application/json","X-Gizwits-Application-Id: ".$appid,"X-Gizwits-User-token: ".$token));

  $obj = sdk_json_decode($return);
  //print_r($obj);

  return $obj;
}

/* **** MAIN PROGRAM *******************/

if (isset($mode) && $mode == 'set') {

  if (loadVariable('tokenheatzy') != '' && (loadVariable('tokenexpireat') > time())) { // récupération du token
    $token = loadVariable('tokenheatzy');
  } else {
    $token = sdk_heatzy_login($heatzy_username,$heatzy_password,$heatzy_application_id ); // récupération du token
  }

  if (loadVariable('did-'.strtolower(trim($heatzy_devicename))) != '') { // récupération du did
    $did = loadVariable('did-'.strtolower(trim($heatzy_devicename)));
  } else {
    $did = sdk_heatzy_getdidbyname($token,$heatzy_application_id,$heatzy_devicename);
  }

  $result = sdk_heatzy_setstatus($did,$heatzy_application_id,$token,$ordre); // définition du status actuel

} else {

  if (loadVariable('tokenheatzy') != '' && (loadVariable('tokenexpireat') > time())) { // récupération du token
    $token = loadVariable('tokenheatzy');
  } else {
    $token = sdk_heatzy_login($heatzy_username,$heatzy_password,$heatzy_application_id ); // récupération du token
  }

  if (loadVariable('did-'.strtolower(trim($heatzy_devicename))) != '') { // récupération du did
    $did = loadVariable('did-'.strtolower(trim($heatzy_devicename)));
  } else {
    $did = sdk_heatzy_getdidbyname($token,$heatzy_application_id,$heatzy_devicename);
  }

  $status = sdk_heatzy_getstatus($did,$heatzy_application_id); //récupération du status actuel
  echo sdk_heatzy_eedomusstatus($status); //mise en forme du xml pour eedomus
}
 ?>