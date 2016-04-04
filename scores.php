<?php
  require_once('the_game.php');
  $scores = afficher_scores();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Anagramme, Les Scores</title>
  <link rel="stylesheet" type="text/css" href="styles.css" />
</head>

<body>
  <div id="page">

    <div id="head_titre">Anagramme, Les Scores</div>

    <div id="lien_jeu">
      <a href="index.php">Retourner au jeu</a>
    </div>

    <div id="scores">

<?php 
if ($scores !== -1) {
  foreach ($scores as $i => $val) {
?>

      <div class="un_score">
        <div class="un_score_user">
          <div class="un_score_label_user">Utilisateur : </div>
          <div class="un_score_donnee"><?php echo $val[0] ?></div>
        </div>
        <div class="un_score_info">
          <div class="un_score_label">Parties terminées : </div>
          <div class="un_score_donnee"><?php echo $val[2] ?></div>
        </div>
        <div class="un_score_info">
          <div class="un_score_label">Parties abandonnées : </div>
          <div class="un_score_donnee"><?php echo $val[3] ?></div>
        </div>
        <div class="un_score_info">
          <div class="un_score_label">Meilleur score : </div>
          <div class="un_score_donnee"><?php echo $val[4] ?></div>
        </div>
      </div>

<?php
  }
} else {
  echo '<div class="erreur">Erreur de chargement de la base de données</div>';
}
?>

    </div>

  </div>
</body>
</html>