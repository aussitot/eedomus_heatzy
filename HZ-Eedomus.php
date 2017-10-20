<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

/*
script cree par twitter:@Havok pour la eedomus

------------------- Parametres :
mode = did - Récupération des identifiants did des appareils
*/

//--- Paramètres Heatzy
$heatzy_username = "";
$heatzy_password = "";
$did = '';

//--- Paramètres Script
$debug = false;
$heatzy_application_id = "c70a66ff039d41b4a220e198b0fcc8b3";

define('__ROOT__', dirname(dirname(__FILE__)));

//-------------- Parametres
$mode = $_GET['mode'];

//*************   Récupération du token, validité 7 jours
// POST /app/login (User login)
function heatzy_login($login, $password, $appid) {

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

  $obj = json_decode($return);
  return $obj->{'token'}; // 12345
}

//*************   Récupération did (identifiant de l'appareil (validité permanente))
// GET /app/bindings
function heatzy_bindings($token, $appid) {

  $curl = curl_init(); //Première étape, initialiser une nouvelle session cURL.
  curl_setopt($curl, CURLOPT_URL, 'https://euapi.gizwits.com/app/bindings?limit=20&skip=0'); //Il va par exemple falloir lui fournir l'url de la page à récupérer.
  curl_setopt($curl, CURLOPT_HTTPGET, true); //Pour envoyer une requête POST, il va alors tout d'abord dire à la fonction de faire un HTTP POST
  curl_setopt ($curl, CURLOPT_HTTPHEADER, array("Accept: application/json","X-Gizwits-Application-Id: ".$appid,"X-Gizwits-User-token: ".$token));
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //Cette option permet d'indiquer que nous voulons recevoir le résultat du transfert au lieu de l'afficher.

  $return = curl_exec($curl); //Il suffit ensuite d'exécuter la requête
  curl_close($curl);

  $obj = json_decode($return,true);

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

  $curl = curl_init(); //Première étape, initialiser une nouvelle session cURL.
  curl_setopt($curl, CURLOPT_URL, 'https://euapi.gizwits.com/app/devdata/'.$diditem.'/latest'); //Il va par exemple falloir lui fournir l'url de la page à récupérer.
  curl_setopt($curl, CURLOPT_HTTPGET, true); //Pour envoyer une requête POST, il va alors tout d'abord dire à la fonction de faire un HTTP POST
  curl_setopt ($curl, CURLOPT_HTTPHEADER, array("Accept: application/json","X-Gizwits-Application-Id: ".$appid));
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //Cette option permet d'indiquer que nous voulons recevoir le résultat du transfert au lieu de l'afficher.

  $return = curl_exec($curl); //Il suffit ensuite d'exécuter la requête
  curl_close($curl);

  $obj = json_decode($return,true);
  $etatch = $obj['attr']['mode'];
  switch ($etatch) {
    case '舒适':
      $etat = 'confort';
      break;
    case '停止':
      $etat = 'off';
      break;
    case '经济':
      $etat = 'eco';
      break;
    case '解冻':
      $etat = 'hors-gel';
      break;
    default:
      $etat = 'inconnu';
      break;
  }
  return $etat;
  //return $obj['devices'][0]['did'];
}

if ($mode == 'did') {
  /* récupération du token */
  $token = heatzy_login($heatzy_username,$heatzy_password,$heatzy_application_id );
  $did = heatzy_bindings($token,$heatzy_application_id);
  echo $did;
} else {
  $result = heatzy_getstatus($did,$heatzy_application_id);
  echo $result;
}
 ?>
