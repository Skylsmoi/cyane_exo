<?php
session_start();

if (isset($_GET["action"])) {
  $action = htmlspecialchars($_GET["action"]);

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
    case 'valider_abandonner':
      if (isset($_POST['btn_valider'])) valider_reponse(); //btn valider a été cliqué
      elseif (isset($_POST['btn_abandonner'])) abandonner_partie(); //btn abandonner a été cliqué
      else header('location: index.php?erreur=-1');
      break;
    case 'rejouer':
      header('location: index.php');
      break;
    default:
      header('location: index.php?erreur=-1'); // action inconnue
  }
}


// a partir de là il n'y a que des fonctions non executées automatiquement
//on récupère le contenu du tableau
function load_bdd() { // on déclare la fonction
  $file = "db/lexique.json"; //on assigne une chaine à une variable
  $contenu = file_get_contents($file); //on assigne le contenu du lexique à la variable $contenu
  $array_lexique = json_decode($contenu); //on convertit le lexique en tableau
  return $array_lexique;
}

function load_random_mot($lexique) {
  return str_split($lexique[rand(0, count($lexique))]); //on va aller chercher dans le lexique un mot aléatoirement 
}

function valider_reponse() {
  if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur'] == "") {
    header('location: index.php?erreur=-6');
    die();
  }

  if (!isset($_POST['reponse']) || !isset($_POST['mot_ordonne'])) {
    header('location: index.php?erreur=-2'); // erreur de paramètres
  } else {
    $reponse = htmlspecialchars($_POST["reponse"]);
    $mot_ordonne = htmlspecialchars($_POST["mot_ordonne"]);
    
    require_once('users.php');

    if ($reponse === $mot_ordonne) {
      $nb_pts = strlen($reponse)+10;

      $rez = ajouter_points_utilisateur($_SESSION['utilisateur'], $nb_pts);
      if ($rez == 1) header('location: index.php?info=1&nb_pts='.$nb_pts); // mot trouvé
      else header('location: index.php?info=1&erreur=-7&&nb_pts='.$nb_pts); 
      
    } else {
      $validation = valider_caracteres($reponse, $mot_ordonne);

      switch ($validation) {
        case -2: header('location: index.php?erreur=-3');break; // lettre utilisé trop souvent
        case -1: header('location: index.php?erreur=-4');break; // lettre inexistante dans le mot proposé
        case 1:
          $lexique = load_bdd();

          if (in_array($reponse, $lexique)) {
            $nb_pts = strlen($reponse);
            $rez = ajouter_points_utilisateur($_SESSION['utilisateur'], $nb_pts);

            if ($rez == 1) header('location: index.php?info=2&nb_pts='.$nb_pts); // mot différent du mot proposé mais valide
            else header('location: index.php?info=2&erreur=-7&&nb_pts='.$nb_pts);

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
    header('location: index.php?erreur_log=-1'); // erreur de param on va changer de page
    die(); //on arrete l'execution du code, sinon ça execute tout et on veut pas
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

    if ($result = valider_login_mdp($log, $mdp) === 1) {
      $_SESSION['utilisateur'] = $log;// on connecte l'utilisateur
      header('location: index.php');
    } else if ($result == 0) {
      header('location: index.php?erreur_log=-7');
    } else if ($result === -1) {
      header('location: index.php?erreur_log=-8');
    }
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

function abandonner_partie() {
  if (!isset($_SESSION['utilisateur'])) {
    header('location: index.php?erreur_log=-6');
    die();
  }

  if (isset($_POST['mot_ordonne'])) {
    $mot_ordonne = $_POST['mot_ordonne'];
    
    require_once('users.php');

    $rez = ajouter_abandon_utilisateur($_SESSION['utilisateur']);

    if ($rez == 1) header('location: index.php?info=3&solution='.$mot_ordonne);
    else header('location: index.php?erreur=-7&solution='.mot_ordonne);

  } else header('location: index.php?erreur_log=-2');
}

function afficher_scores() {
  require_once('users.php');

  $scores = get_scores();
  return $scores;
}


?>
