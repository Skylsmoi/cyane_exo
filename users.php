<?php

function creer_utilisateur($log, $mdp) {
  // on ouvre le fichier utilisateurs en mode "a", write only, ce qui place la tête de lecture en fin de fichier
  if (($handle = fopen("db/utilisateurs.csv", "a")) !== FALSE) {
    if (fputcsv($handle, array($log, md5($mdp), 0, 0, 0)) != FALSE) { //000 pour initialiser à zero les scores, nb parties etc
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
  $mdp = md5($mdp);
  if (($handle = fopen("db/utilisateurs.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 50)) !== FALSE) {
      if ($data[0] === $log && $data[1] === $mdp) {
        fclose($handle);
        return 1;
      }
    }
    fclose($handle);
    return 0;
  } else return -1;
}


/*
structure du csv :
0 : login,
1 : mdp,
2 : nb_parties,
3 : nb_abandons,
4 : meilleur_score
*/
function ajouter_points_utilisateur($log, $nb_pts) {
  if (($handle = fopen("db/utilisateurs.csv", "r")) !== FALSE) {
    $new_db = array();

    while (($data = fgetcsv($handle, 50)) !== FALSE) {
      if ($data[0] === $log) {
        $data[2]++; // on ajoute 1 au nombre de parties jouées
        if ($nb_pts > $data[4]) $data[4] = $nb_pts; // on assigne le meilleur score
      }
      array_push($new_db, $data);// je met les maj de l'utilisateur dans new_db
    }
    fclose($handle);

    $handle = fopen("db/utilisateurs.csv", "w");

    foreach ($new_db as $i => $val) {
      fputcsv($handle, $new_db[$i]);
    }
    fclose($handle);

    return 1;
  } else return -1;
}

function ajouter_abandon_utilisateur($log) {
  if (($handle = fopen("db/utilisateurs.csv", "r")) !== FALSE) {
    $new_db = array();

    while (($data = fgetcsv($handle, 50)) !== FALSE) {
      if ($data[0] === $log) $data[3]++; // on ajoute 1 au nombre de parties abandonnées
      array_push($new_db, $data);
    }
    fclose($handle);

    $handle = fopen("db/utilisateurs.csv", "w");

    foreach ($new_db as $i => $val) {
      fputcsv($handle, $new_db[$i]);
    }
    fclose($handle);

    return 1;
  } else return -1;
}

function get_scores() {
  if (($handle = fopen("db/utilisateurs.csv", "r")) !== FALSE) {
    $scores = array();

    while (($data = fgetcsv($handle, 50)) !== FALSE) {
      unset($data[1]); // on ne retourne pas les mots de passe
      array_push($scores, $data);
    }
    fclose($handle);

    return $scores;
  } return -1;
}
?>
