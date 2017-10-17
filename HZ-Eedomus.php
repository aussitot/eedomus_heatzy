<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

define('__ROOT__', dirname(dirname(__FILE__)));
require_once ('config.php');

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
  return $obj;
  //return $obj['devices'][0]['did'];
}


$token = heatzy_login($heatzy_username,$heatzy_password,$heatzy_application_id );
echo "token : ".$token."<br>";
$did = heatzy_bindings($token,$heatzy_application_id);
//echo "did : ".$did."<br>";
print_r($did);
 ?>
