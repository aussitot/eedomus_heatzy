[![GitHub release](https://img.shields.io/github/release/aussitot/eedomus_enedis.svg?style=flat-square)](https://github.com/aussitot/eedomus_heatzy/releases)
![GitHub license](https://img.shields.io/github/license/aussitot/eedomus_enedis.svg?style=flat-square)
![Status](https://img.shields.io/badge/Status-beta-red.svg?style=flat-square)
[![Twitter](https://img.shields.io/badge/twitter-@havok-blue.svg?style=flat-square)](http://twitter.com/havok)
# eedomus_heatzy
Gestion des modules Heatzy dans la eedomus
script cree par twitter:@Havok pour la eedomus

Voici un  script pour intégrer dans l'interface eedomus les chauffages électriques pilotés par des modules Heatzy
**Ce que ca fait** : Ca va vous permettre de
- changer l'etat des radiateurs
- mettre à jour régulierement l'etat de ceux-ci dans l'interface eedomus (en cas de changements programmés dans l'appli Heatzy)

2 versions
# version_store_eedomus

Cette version est intégrée directement au store eedomus. Il suffit, à partir de l'interface eedomus, d'aller dans "Configuration/Ajouter ou supprimer un périphérique/Store eedomus" de choisir "Heatzy Pilote" et de remplir les champs requis. 

# version_serveur_web
A notez que ce script php peut être utilisé pour autre chose que la eedomus.

## INSTALLATION

**Prérequis** : Il faut disposer d'un serveur web/php autre que l'eedomus elle-même.  

### Etape 1
- Copiez le fichier HZ-eedomus.php sur votre serveur.
- Modifiez le pour y reporter vos login et password Heatzy

```php
//--- Paramètres Heatzy
$heatzy_username = "";
$heatzy_password = "";
```
### Etape 2
Dans un navigateur tapez l'url : ```http://www.votreserveur.com/HZ-Eedomus.php?mode=did``` et récupérer la valeur **did** correspondant au radiateur que vous souhaitez piloter dans l'eedomus

### Etape 3
Dans eedomus créez un Actionneur http :
- Nom : Chauffage Heatzy (ou ce que vous voulez ;)
- [VAR1] : le **did** récupéré à l'étape 2

Dans les paramètres experts :
- requete de mise à jour : ```http://www.votreserveur.com/HZ-Eedomus.php?did=[VAR1]```
- Chemin XPath : /root/status/value
- Fréquence de la requête : en mn le délai que vous souhaitez pour l'état (30mn me semble pas mal).

Dans l'onglet Valeurs :
- Valeur brute : 0
- description : Confort
- URL : ```http://www.votreserveur.com/HZ-Eedomus.php?mode=set&ordre=0&did=[VAR1]```
- Type : GET

- Valeur brute : 1
- description : Eco
- URL : ```http://www.votreserveur.com/HZ-Eedomus.php?mode=set&ordre=1&did=[VAR1]```
- Type : GET

- Valeur brute : 2 
- description : Hors-Gel
- URL : ```http://www.votreserveur.com/HZ-Eedomus.php?mode=set&ordre=2&did=[VAR1]```
- Type : GET

- Valeur brute : 3
- description : Off
- URL : ```http://www.votreserveur.com/HZ-Eedomus.php?mode=set&ordre=3&did=[VAR1]```
- Type : GET
