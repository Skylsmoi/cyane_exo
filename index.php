<?php
  require_once('the_game.php');
  $lexique = load_bdd(); // initialisation de la base de données (le lexique)
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Anagramme, Le Jeu</title>
  <link rel="stylesheet" type="text/css" href="styles.css" />
</head>


<body>
  <div id="page">
    <div id="head_titre">Anagramme, Le Jeu</div>

    <div id="login">

<?php 
//on ne génère le html seulement si "new_user" est dans l'url si une session n'est pas deja ouverte
if (isset($_GET['new_user']) && !isset($_SESSION['utilisateur'])) {
?> 

      <div class="login_title">
        Crée ton compte :<br />
        <span class="login_title_msg">
          Tu as déjà un compte ? <a href="index.php">Clique ici</a> pour te connecter.
        </span>
      </div>

      <form id="creation_login" action="the_game.php?action=creation_login" method="post">
        <label for="login">Login : </label>
        <input type="text" name="login" id="login" maxlength="50" />
        <br />
        <label for="mdp">Mot de passe :</label>
        <input type="password" name="mdp" id="mdp" maxlength="50" />
        <br />
        <label for="mdp_verif">Vérification :</label>
        <input type="password" name="mdp_verif" id="mdp_verif" maxlength="50" />
        <input type="submit" value="Créer" />
      </form>

<?php // si une session est ouverte on génère le code html ci après
} else if (isset($_SESSION['utilisateur']) && $_SESSION['utilisateur'] != "") { ?>

      <div class="login_title">
        Tu es connecté au compte de <?php echo $_SESSION['utilisateur'] ?>.<br />
      </div>
      <form id="login_connecte" action="the_game.php?action=logout" method="post">
        <input type="submit" value="Déconnexion" />
      </form>

<?php } else if (!isset($_SESSION['utilisateur'])) { ?>

      <div class="login_title">
        Connecte toi :<br />
        <span class="login_title_msg">
          Tu n'as pas de compte ? <a href="index.php?new_user">Clique ici</a> pour en créer un.
        </span>
      </div>

      <form id="login_form" action="the_game.php?action=login" method="post">
        <label for="login">Login : </label>
        <input type="text" name="login" id="login" maxlength="50" />
        <br />
        <label for="mdp">Mot de passe :</label>
        <input type="password" name="mdp" id="mdp" maxlength="50" />
        <input type="submit" value="Valider" />
      </form>

<?php } ?>

    </div>

    <div id="login_message">
      <div class="erreur">
<?php
  if (isset($_GET['erreur_log'])) {
    $erreur = htmlspecialchars($_GET['erreur_log']);
    switch ($erreur) {
      case 1:
        echo 'Votre compte a été crée, vous êtes maintenant connecté.';
        break;
      case 2:
        echo 'Tu es maintenant déconnecté.';
        break;
      case -1:
        echo 'Erreur interne, données du formulaire envoyées invalide. Requis : $_POST["login"], $_POST["mdp"] et $_POST["mdp_verif"]';
        break;
      case -2:
        echo 'La vérification de votre mot de passe est différente.';
        break;
      case -3:
        echo 'Erreur, ce nom d\'utilisateur existe déjà.';
        break;
      case -4:
        echo 'Erreur, impossible d\'ouvrir le fichier d\'utilisateurs.';
        break;
      case -5:
        echo 'Erreur, impossible d\'écrire dans le fichier d\'utilisateurs.';
        break;
      case -6:
        echo 'Erreur, tu n\'es pas connecté(e).';
        break;
      case -7:
        echo 'Ton mot de passe est invalide ou l\'utilisateur n\'existe pas.';
        break;
      case -8:
        echo 'Erreur interne, la base de donnée n\'existe pas.';
        break;
    }
  }
?>
      </div>
    </div>

    <div id="game">

      <div id="game_options">

<?php
  if (!isset($_GET['solution'])) { //si le joueur a cliqué sur abandonné, on n'affiche pas de nouvelle partie
    $new_mot = load_random_mot($lexique); //charge un mot de la bdd
    $new_mot_ordonne = $new_mot; //sauvegarde dans une autre variable le mot encore ordonné
    shuffle($new_mot); // désordonne le mot
    $nb_lettres = count($new_mot); // compte le nombre de caractères du mot
    for ($i = 0; $i < $nb_lettres; $i++) { // boucle de 0 à nombre de lettre 
      echo  '
          <div class="une_option">'.$new_mot[$i].'</div>';
    }
  }
?>

      </div>
      <div id="game_reponse">

<?php if (!isset($_GET['solution'])) { //si le joueur a cliqué sur abandonné, on n'affiche pas de nouvelle partie ?>

        <form action="the_game.php?action=valider_abandonner" method="post">
          <label for="input_reponse">Ta réponse : </label>
          <input id="input_reponse" type="text" class="input_reponse" name="reponse" value="" maxlength="50" />
          <input type="hidden" name="mot_ordonne" value="<?php echo implode($new_mot_ordonne); ?>" /><br />

          <div class="game_reponse_btns">

<?php   if (isset($_SESSION['utilisateur'])) { ?>

            <input type="submit" name="btn_abandonner" value="Abandonner" />
            <input type="submit" name="btn_valider" value="Valider" />

<?php   } else { ?>

            <input type="submit" name="btn_abandonner" value="Abandonner" disabled />
            <input type="submit" name="btn_valider" value="Connecte toi pour jouer" disabled />

<?php   } // if (isset($_SESSION['utilisateur'])) ?>
          </div>

        </form>

<?php } else { // fin if (!isset($_GET['solution'])) ?>

        <form action="the_game.php?action=rejouer" id="rejouer" method="post">
          <input type="submit" value="Rejouer" />
        </form>

<?php } ?>

        <div id="game_message">
          <div class="erreur">
<?php
  if (isset($_GET['erreur'])) {
    $erreur = htmlspecialchars($_GET['erreur']);
    switch ($erreur) {
      case -1:
        echo 'Erreur interne, action invalide';
        break;
      case -2:
        echo 'Erreur interne, données du formulaire enovyées invalide.';
        break;
      case -3:
        echo 'Une lettre est utilisée trop souvent, ton mot est invalide, tu n\'as gagné aucun point.';
        break;
      case -4:
        echo 'Une lettre utilisée n\'est pas proposée, ton mot est invalide, tu n\'as gagné aucun point.';
        break;
      case -5:
        echo 'Ton mot n\'existe pas dans le lexique, tu n\'as gagné aucun point.';
        break;
      case -6:
        echo 'Erreur, tu n\'es pas logué';
        break;
      case -7:
        echo 'Erreur de sauvegarde de tes statistiques.';
        break;
    }
  }
?>
          </div>
          <div class="info">
<?php
  if (isset($_GET['info'])) {
    $info = htmlspecialchars($_GET['info']);
    switch ($info) {
      case 1:
        $nb_pts = htmlspecialchars($_GET['nb_pts']);
        echo 'Félicitations, tu as trouvé le mot. Tu as gagné '.$nb_pts.' pts.';
        break;
      case 2:
        $nb_pts = htmlspecialchars($_GET['nb_pts']);
        echo 'Ce n\'est pas le mot proposé mais ton mot est valide. Tu as gagné '.$nb_pts.' pts.';
        break;
      case 3:
        echo 'La bonne réponse était : '.htmlspecialchars($_GET['solution']);
        break;
    }
  }
?>
          </div>
        </div>

        <a class="scores" href="scores.php">Voir les scores</a>

      </div>
    </div>
  </div>
</body>

</html>
