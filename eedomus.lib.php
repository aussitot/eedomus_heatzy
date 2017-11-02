<?php

/******************************************************************
 librairie d'émulation des fonctions spécifiques à l'eedomus
http://doc.eedomus.com/view/Scripts#Scripts_.22Objets_connect.C3.A9s.22

script cree par twitter:@Havok pour la eedomus
****************************************************************/

/*Exécute une requête HTTP/HTTPS et retourne son résultat sous forme de chaine de caractère.*/
function httpQuery($url, $action = 'GET'/*GET,POST,PUT,DELETE*/, $post = NULL, $oauth_token = NULL, $headers = NULL, $use_cookies = false, $ignore_errors = false) {

  $curl = curl_init(); //Première étape, initialiser une nouvelle session cURL.
  curl_setopt($curl, CURLOPT_URL, $url); //Il va par exemple falloir lui fournir l'url de la page à récupérer.

  if ($action == 'GET') {
    curl_setopt($curl, CURLOPT_HTTPGET, true); //Pour envoyer une requête POST, il va alors tout d'abord dire à la fonction de faire un HTTP POST
  } elseif ($action == 'POST') {
    curl_setopt($curl, CURLOPT_POST, true); //Pour envoyer une requête POST, il va alors tout d'abord dire à la fonction de faire un HTTP POST
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
  }
  curl_setopt ($curl, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //Cette option permet d'indiquer que nous voulons recevoir le résultat du transfert au lieu de l'afficher.

  $return = curl_exec($curl); //Il suffit ensuite d'exécuter la requête
  curl_close($curl);
  return $return;
}

function sdk_json_decode($json) {
  return json_decode($json,true);
}

function getArg($var, $mandatory = true, $default = ' ') {
  $return = '';
  if (isset($_GET[$var])) $return = $_GET[$var];
  return $return;
}

function saveVariable($variable_name, $variable_content) {
  return setcookie($variable_name, $variable_content, time()+3600*24*7);  /* expire dans 7 jours */
}

function loadVariable($variable_name) {
  return $_COOKIE[$variable_name];
}
 ?>
