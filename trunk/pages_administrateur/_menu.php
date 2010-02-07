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
 * Menu administrateur
 * 
 */

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}

$arbre='
<ul id="treeview">
	<li><span>Mon compte</span>
		<ul>
			<li class="accueil"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=accueil">Accueil</a></li>
			<li class="password"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=password">Changer mon mot de passe</a></li>
		</ul>
	</li>
	<li><span>Établissement</span>
		<ul>
			<li class="etabl"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=etabl">Paramétrages</a></li>
			<li class="etabl_resilier"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=etabl_resilier">Résilier l\'inscription</a></li>
		</ul>
	</li>
	<li><span>Utilisateurs et affectations</span>
		<ul>
			<li class="fichier"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=fichier">Import / Export</a></li>
			<li class="periode"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=periode">Périodes</a></li>
			<li class="classe"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=classe">Classes</a></li>
			<li class="groupe"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=groupe">Groupes</a></li>
			<li class="eleve"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=eleve">Élèves</a></li>
			<li class="professeur"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=professeur">Professeurs</a></li>
			<li class="directeur"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=directeur">Directeurs</a></li>
			<li class="statut"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=statut">Statuts</a></li>
		</ul>
	</li>
	<li><span>Référentiels</span>
		<ul>
			<li class="algorithme_gestion"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=algorithme_gestion">Paramétrage de l\'algorithme</a></li>
			<li class="referentiel_view"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=referentiel_view">Référentiels en place (pour information)</a></li>
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
echo str_replace('class="'.$FICHIER.'"><a ','class="'.$FICHIER.'"><a class="actif" ',$arbre);
?>
