# eedomus_heatzy
Gestion des modules Heatzy dans la eedomus
script cree par twitter:@Havok pour la eedomus

NB : Script à installer sur un serveur web/php autre que l'eedomus elle-même

# INSTALLATION
Bonjour,

Voici un  script pour intégrer dans l'interface eedomus les chauffages électriques pilotés par des modules Heatzy
A notez que ce script php peut être utilisé pour autre chose que la eedomus.

**Prérequis** : Il faut disposer d'un serveur web/php autre que l'eedomus elle-même.  

**Ce que ca fait** : Ca va vous permettre de
- changer l'etat des radiateurs
- mettre à jour régulierement l'etat de ceux-ci dans l'interface eedomus (en cas de changements programmés dans l'appli Heatzy)

## Etape 1
- Copiez le fichier HZ-eedomus.php sur votre serveur.
- Modifiez le pour y reporter vos login et password Heatzy

```php
//--- Paramètres Heatzy
$heatzy_username = "";
$heatzy_password = "";
```
## Etape 2
Dans un navigateur tapez l'url : ```http://www.votreserveur.com/HZ-Eedomus.php?mode=did``` et récupérer la valeur **did** correspondant au radiateur que vous souhaitez piloter dans l'eedomus

## Etape 3
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

Et c'est tout.
Si j'ai le courage je tenterais un script intégré au Store eedomus.
Bonne chance (Anne-marie tu peux le faire !)
