<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 * 
 * ****************************************************************************************************
 * SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath <http://www.sesamath.net> - Tous droits réservés.
 * Logiciel placé sous la licence libre GPL 3 <http://www.rodage.org/gpl-3.0.fr.html>.
 * ****************************************************************************************************
 * 
 * Ce fichier est une partie de SACoche.
 * 
 * SACoche est un logiciel libre ; vous pouvez le redistribuer ou le modifier suivant les termes 
 * de la “GNU General Public License” telle que publiée par la Free Software Foundation :
 * soit la version 3 de cette licence, soit (à votre gré) toute version ultérieure.
 * 
 * SACoche est distribué dans l’espoir qu’il vous sera utile, mais SANS AUCUNE GARANTIE :
 * sans même la garantie implicite de COMMERCIALISABILITÉ ni d’ADÉQUATION À UN OBJECTIF PARTICULIER.
 * Consultez la Licence Générale Publique GNU pour plus de détails.
 * 
 * Vous devriez avoir reçu une copie de la Licence Générale Publique GNU avec SACoche ;
 * si ce n’est pas le cas, consultez : <http://www.gnu.org/licenses/>.
 * 
 */

// Menu professeur

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}

$arbre='
<ul id="treeview">
	<li>Mon compte
		<ul>
			<li class="compte_accueil"><a href="./index.php?page=compte_accueil">Accueil</a></li>
			<li class="compte_password"><a href="./index.php?page=compte_password">Mot de passe</a></li>
			<li class="consultation"><a href="./index.php?page=consultation">Données en consultation</a></li>
			<li class="export_fichier"><a href="./index.php?page=export_fichier">Export listings</a></li>
		</ul>
	</li>
	<li>Référentiels
		<ul>
			<li class="professeur_referentiel_gestion"><a href="./index.php?page=professeur_referentiel&amp;section=gestion">Gérer les référentiels</a></li>
			<li class="professeur_referentiel_edition"><a href="./index.php?page=professeur_referentiel&amp;section=edition">Modifier le contenu des référentiels</a></li>
		</ul>
	</li>
	<li>Groupes de besoin
		<ul>
			<li class="professeur_groupe_gestion"><a href="./index.php?page=professeur_groupe&amp;section=gestion">Gérer les groupes</a></li>
			<li class="professeur_groupe_eleve"><a href="./index.php?page=professeur_groupe&amp;section=eleve">Élèves &amp; groupes de besoin</a></li>
		</ul>
	</li>
	<li>Évaluations &amp; Saisie des résultats
		<ul>
			<li class="professeur_eval_demande"><a href="./index.php?page=professeur_eval&amp;section=demande">Demandes d\'évaluations</a></li>
			<li class="professeur_eval_groupe"><a href="./index.php?page=professeur_eval&amp;section=groupe">Évaluer une classe ou un groupe</a></li>
			<li class="professeur_eval_select"><a href="./index.php?page=professeur_eval&amp;section=select">Évaluer des élèves sélectionnés</a></li>
		</ul>
	</li>
	<li>Relevés &amp; Bilans
		<ul>
			<li class="releve_grille"><a href="./index.php?page=releve&amp;section=grille">Grilles sur un niveau</a></li>
			<li class="releve_matiere"><a href="./index.php?page=releve&amp;section=matiere">Bilans sur une matière</a></li>
			<li class="releve_selection"><a href="./index.php?page=releve&amp;section=selection">Bilans sur une sélection d\'items</a></li>
			<li class="releve_multimatiere"><a href="./index.php?page=releve&amp;section=multimatiere">Bilans transdisciplinaires (P.P.)</a></li>
			<li class="releve_socle"><a href="./index.php?page=releve&amp;section=socle">Attestation de maîtrise du socle</a></li>
		</ul>
	</li>
</ul>
';

echo'<div id="cadre_identite">';
echo	html($_SESSION['DENOMINATION']).'<br />';
echo	html($_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM']).'<q class="deconnecter" title="Se déconnecter."></q><br />';
echo	'<i><img alt="'.$_SESSION['USER_PROFIL'].'" src="./_img/menu/profil_'.$_SESSION['USER_PROFIL'].'.png" /> '.$_SESSION['USER_PROFIL'].' <span id="clock"><img alt="" src="./_img/clock_fixe.png" /> '.$_SESSION['DUREE_INACTIVITE'].' min</span><img alt="" src="./_img/point.gif" /></i><br />';
echo'</div>';
echo'<div id="appel_menu">MENU</div>';
$class = ($SECTION) ? $PAGE.'_'.$SECTION : $PAGE;
echo str_replace('class="'.$class.'"><a ','class="'.$class.'"><a class="actif" ',$arbre);
?>
