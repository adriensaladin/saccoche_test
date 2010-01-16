<?php
/**
 * @version $Id: accueil.php 8 2009-10-30 20:56:02Z thomas $
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009
 * 
 * ****************************************************************************************************
 * SACoche [http://competences.sesamath.net] - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath [http://www.sesamath.net]
 * Distribution sous licence libre prévue pour l'été 2010.
 * ****************************************************************************************************
 * 
 */

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
$TITRE = "Bienvenue dans votre espace identifié !";
?>

<img id="look_menu" src="./_img/fleche_h1.gif" alt="look !" />

<ul class="puce">
	<li><span class="astuce">En haut à gauche, le bouton <img src="./_img/menu.gif" alt="Menu" /> se développe au survol de la souris et permet de naviguer dans son espace.</span></li>
	<li><span class="astuce">En haut à droite, le bouton <img src="./_img/action/action_deconnecter.png" alt="" /> permet de se déconnecter.</span></li>
</ul>

<hr />

<ul class="puce">
	<li><span class="manuel"><a class="pop_up" href="./aide.php?fichier=ergonomie_generale">DOC : Ergonomie générale.</a></span></li>
	<li><span class="manuel"><a class="pop_up" href="./aide.php?fichier=environnement_eleve">DOC : L'environnement élève.</a></span></li>
</ul>

<hr />

<div>
	<span class="astuce">Pour que votre établissement soit automatiquement sélectionné depuis n'importe quel ordinateur, utilisez l'adresse <b>http://competences.sesamath.net?id=<?php echo $_SESSION['STRUCTURE_ID'] ?></b></span><br />
</div>

