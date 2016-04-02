<?php

//deprecated
function load_bdd_utilisateurs_json() {
  $file = "db/utilisateurs.json";
  return json_decode(file_get_contents($file));
}

function creer_utilisateur($log, $mdp) {
  // on ouvre le fichier utilisateurs en mode "a", write only, ce qui place la tête de lecture en fin de fichier
  if (($handle = fopen("db/utilisateurs.csv", "a")) !== FALSE) {
    if (fputcsv($handle, array($log, $mdp)) != FALSE) {
      fclose($handle);
      return 1;
    } else return -2; //erreur, impossible d'écrire dans le fichier d'utilisateurs
  } else return -1; // erreur, impossible d'ouvrir le fichier d'utilisateurs
}

/*
cette fonction teste si un nom d'utilisateur existe déjà dans la base de donnée.
Paramètre :
$log : string : nom d'utilisateur à chercher dans la base
Retourne :
1 si le nom d'utilisateur a été trouvé dans la base
0 sinon
-1 si il y a eu un problème de lecture de la base
*/
function utilisateur_existe($log) {
  if (($handle = fopen("db/utilisateurs.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 50)) !== FALSE) {
      if ($data[0] === $log) {
        fclose($handle);
        return 1;
      }
    }
    fclose($handle);
    return 0;
  } else return -1;
}

/*
cette fonction vérifie si le couple login/mot de passe de l'utilisateur correspond à celui dans la base de données.
Paramètre :
$log : string : nom d'utilisateur à chercher dans la base
$mdp : string : mot de passe de l'utilisateur à chercher dans la base
Retourne :
1 si le couple nom d'utilisateur/mot de passe a été trouvé dans la base
0 sinon
*/
function valider_login_mdp($log, $mdp) {
  if (($handle = fopen("db/utilisateurs.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 50)) !== FALSE) {
      if ($data[0] === $log && $data[1] === $mdp) {
        fclose($handle);
        return 1;
      }
    }
    fclose($handle);
    return 0;
  } return -1;
}

?>
