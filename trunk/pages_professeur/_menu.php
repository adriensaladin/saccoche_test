<?php
/**
 * @version $Id: _menu.php 8 2009-10-30 20:56:02Z thomas $
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009
 * 
 * ****************************************************************************************************
 * SACoche [http://competences.sesamath.net] - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath [http://www.sesamath.net]
 * Distribution sous licence libre prévue pour l'été 2010.
 * ****************************************************************************************************
 * 
 * Menu professeur
 * 
 */

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}

$arbre='
<ul id="treeview">
	<li>Mon compte
		<ul>
			<li class="accueil"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=accueil">Accueil</a></li>
			<li class="password"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=password">Mot de passe</a></li>
			<li class="fichier"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=fichier">Export listings</a></li>
		</ul>
	</li>
	<li>Référentiels de compétences
		<ul>
			<li class="referentiel_global"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=referentiel&amp;section=global">Gérer les référentiels</a></li>
			<li class="referentiel_detail"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=referentiel&amp;section=detail">Modifier le contenu des référentiels</a></li>
		</ul>
	</li>
	<li>Groupes de besoin
		<ul>
			<li class="groupe_liste"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=groupe&amp;section=gestion">Gérer les groupes</a></li>
			<li class="groupe_eleve"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=groupe&amp;section=eleve">Élèves &amp; groupes de besoin</a></li>
		</ul>
	</li>
	<li>Évaluations et saisie des résultats
		<ul>
			<li class="eval_groupe"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=eval&amp;section=groupe">Pour une classe ou un groupe</a></li>
			<li class="eval_select"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=eval&amp;section=select">Pour un ou plusieurs élèves sélectionnés</a></li>
		</ul>
	</li>
	<li>Relevés de compétences
		<ul>
			<li class="algorithme_info"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=algorithme_info">Algorithme de calcul (pour information)</a></li>
			<li class="releve_grille"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=releve&amp;section=grille">Grilles sur un niveau</a></li>
			<li class="releve_matiere"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=releve&amp;section=matiere">Bilans sur une matière</a></li>
			<li class="releve_selection"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=releve&amp;section=selection">Bilans sur une sélection d\'items</a></li>
			<li class="releve_multimatiere"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=releve&amp;section=multimatiere">Bilans transdisciplinaires (P.P.)</a></li>
			<li class="releve_socle"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=releve&amp;section=socle">Attestation de maîtrise du socle</a></li>
		</ul>
	</li>
</ul>
';

echo'<div id="cadre_identite">';
echo	$_SESSION['STRUCTURE'].'<br />';
echo	$_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM'].'<q class="deconnecter" title="Se déconnecter."></q><br />';
echo	'<i><img alt="'.$_SESSION['PROFIL'].'" src="./_img/menu/'.$_SESSION['PROFIL'].'.png" /> '.$_SESSION['PROFIL'].' <span id="clock"><img alt="" src="./_img/clock_fixe.png" /> '.$_SESSION['DUREE_INACTIVITE'].' min</span><img alt="" src="./_img/point.gif" /></i><br />';
echo'</div>';
echo'<div id="appel_menu">MENU</div>';
$class = ($SECTION) ? $FICHIER.'_'.$SECTION : $FICHIER;
echo str_replace('class="'.$class.'"><a ','class="'.$class.'"><a class="actif" ',$arbre);
?>
