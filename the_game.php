<?php
session_start();

if (isset($_GET["action"])) {
  $liste_actions = ['valider_reponse','creation_login','login', 'logout'];
  $action = htmlspecialchars($_GET["action"]);

  if (!in_array($action, $liste_actions)) {
    header('location: index.php?erreur=-1'); // action inconnue
  } else {
    switch ($action) {
      case 'creation_login':
        creation_utilisateur();
        break;
      case 'login':
        login_user();
        break;
      case 'logout':
        logout_user();
        break;
      case 'valider_reponse':
        valider_reponse();
        break;
    }
  }
}


function load_bdd() {
  $file = "db/lexique.json";
  return json_decode(file_get_contents($file));
}

function load_random_mot($lexique) {
  //return str_split($lexique[2]); // ligne de debug pour le mot "abaisse"
  return str_split($lexique[rand(0, count($lexique))]);
}

function valider_reponse() {
  if (!isset($_POST['reponse']) || !isset($_POST['mot_ordonne'])) {
    header('location: index.php?erreur=-2'); // erreur de paramètres
  } else {
    $reponse = htmlspecialchars($_POST["reponse"]);
    $mot_ordonne = htmlspecialchars($_POST["mot_ordonne"]);

    if ($reponse === $mot_ordonne) {
      $nb_pts = strlen($reponse)+10;
      header('location: index.php?info=1&nb_pts='.$nb_pts); // mot trouvé
    } else {
      $validation = valider_caracteres($reponse, $mot_ordonne);

      switch ($validation) {
        case -2: header('location: index.php?erreur=-3');break; // lettre utilisé trop souvent
        case -1: header('location: index.php?erreur=-4');break; // lettre inexistante dans le mot proposé
        case 1:
          $lexique = load_bdd();
          if (in_array($reponse, $lexique)) {
            $nb_pts = strlen($reponse);
            header('location: index.php?info=2&nb_pts='.$nb_pts); // mot différent du mot proposé mais valide
          } else  header('location: index.php?erreur=-5'); // mot inexistant dans la base
          break;
      }
    }
  }
}

/*
Function servant à valider les caractères du mot retourné par l'utilisateur suivant ces conditions :
- Les caractères de l'utilisateur doivent tous appartenir au mot proposé.
- Le nombre d'occurences d'un caractère de l'utilisateur ne doit pas être supérieur au nombre d'occurences de ce caractère dans le mot proposé 

Paramètres :
$rep : string : le mot de réponse envoyé par l'utilisateur
$mot : string : le mot (ordonné) proposé par le jeu

La fonction retourne :
1 si les conditions sont respectées
-1 si un caractère n'est pas dans le mot proposé
-2 si le nombre d'occurence d'un caractère est supérieur à celle du mot proposé
*/
function valider_caracteres($rep, $mot) {

  // on crée un array contenant chaque lettre du mot de l'utilisateur associé à son nombre d'occurences
  $rep_array_incremente = array();
  foreach (count_chars($rep, 1) as $i => $val) { // boucle sur les éléments de l'array retourné par count_chars
    $rep_array_incremente[chr($i)] = $val;
  }

  // idem pour le mot proposé par le jeu
  $mot_array_incremente = array();
  foreach (count_chars($mot, 1) as $i => $val) {
    $mot_array_incremente[chr($i)] = $val;
  }

  // on compare les 2 arrays
  foreach($rep_array_incremente as $i => $val) { // boucle sur les caractères du mot de l'utilisateur
    // test si le caractère appartient au mot proposé par le jeu
    if (!array_key_exists($i, $mot_array_incremente)) return -1;
    // test si le nombre d'occurences du caractère ne dépasse pas le nombre d'occurence de ce caractère dans le mot proposé
    if ($rep_array_incremente[$i] > $mot_array_incremente[$i]) return -2; 
  }

  return 1; // si la boucle s'est terminée sans passer par un return, alors le mot de l'utilisateur respecte les conditions
}

function creation_utilisateur() {
  if (!isset($_POST['login']) || !isset($_POST['mdp']) || !isset($_POST['mdp_verif'])) {
    header('location: index.php?erreur_log=-1'); // erreur de param
    die();
  }

  $log = htmlspecialchars($_POST['login']);
  $mdp = htmlspecialchars($_POST['mdp']);
  $mdp_verif = htmlspecialchars($_POST['mdp_verif']);

  if ($mdp !== $mdp_verif) {
    header('location: index.php?erreur_log=-2'); // erreur verif mdp invalide
    die();
  }

  require_once('users.php');

  if (utilisateur_existe($log)) {
    header('location: index.php?new_user&erreur_log=-3');
    die();
  }

  $rez = creer_utilisateur($log, $mdp);
  switch ($rez) {
    case 1:
      $_SESSION['utilisateur'] = $log;
      header('location: index.php?erreur_log=1');
      break;
    case -1:
      header('location: index.php?new_user&erreur_log=-4');
      break;
    case -2:
      header('location: index.php?new_user&erreur_log=-5');
      break;
  }
}

//@TODO test and finish me
function login_user() {
  if (isset($_SESSION['utilisateur'])) {
    header('location: index.php?erreur_log=-1'); // erreur, déjà logué
    die();
  }

  if (!isset($_POST['login']) || !isset($_POST['mdp'])) {
    header('location: index.php?erreur_log=-2'); // erreur de paramètres
  } else {
    $log = htmlspecialchars($_POST['login']);
    $mdp = htmlspecialchars($_POST['mdp']);

    require_once('users.php');
    echo 'woot';
    if ($result = valider_login_mdp($log, $mdp) === 1) {
      echo '1';
      $_SESSION['utilisateur'] = $log;
      header('location: index.php');
    } else if ($result == 0) {
      echo '2';
      header('location: index.php?erreur_log=-7');
    } else if ($result === -1) {
      echo '3';
      header('location: index.php?erreur_log=-8');
    }
    var_dump($result);
  }
}

function logout_user() {
  if (isset($_SESSION['utilisateur'])) {
    unset($_SESSION['utilisateur']);
    header('location: index.php?erreur_log=2');
  } else {
    header('location: index.php?erreur_log=-6');
  }
}


?>
