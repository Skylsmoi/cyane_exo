<?php
  require_once('the_game.php');
  $lexique = load_bdd(); // initialisation de la bdd
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Anagramme, The Game</title>
  <link rel="stylesheet" type="text/css" href="styles.css" />
</head>


<body>
  <div id="page">
    <div id="head_titre">Anagramme, The Game</div>
    <div id="game">
      <div id="game_options">
<?php
  $new_mot = load_random_mot($lexique); //charge un mot de la bdd
  $new_mot_ordonne = $new_mot; //sauvegarde dans une autre variable le mot encore ordonné
  shuffle($new_mot); // désordonne le mot
  $nb_lettres = count($new_mot); // compte le nombre de caractères du mot
  for ($i = 0; $i < $nb_lettres; $i++) {
    echo  '
        <div class="une_option">'.$new_mot[$i].'</div>';
  }
?>

      </div>
      <div id="game_reponse">
        <form action="the_game.php?action=valider_reponse" method="post">
          <input type="text" class="input_reponse" name="reponse" value="Ta réponse..." maxlength="50" />
          <input type="submit" value="Valider" />
          <input type="hidden" name="mot_ordonne" value="<?php echo implode($new_mot_ordonne); ?>" />
        </form>
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
        echo 'Erreur interne, données du formulaire enovyées invalide. Requis : $_POST["reponse"] et $_POST["mot_ordonne"]';
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
        echo 'Félicitation, tu as trouvé le mot. Tu as gagné '.$nb_pts.' pts.';
        break;
      case 2:
        $nb_pts = htmlspecialchars($_GET['nb_pts']);
        echo 'Ce n\'est pas le mot proposé mais ton mot est valide. Tu as gagné '.$nb_pts.' pts.';
        break;
    }
  }
?>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>

</html>
