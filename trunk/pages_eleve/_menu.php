<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 * 
 * ****************************************************************************************************
 * SACoche <http://competences.sesamath.net> - Suivi d'Acquisitions de Compétences
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

// Menu élève

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}

$arbre='
<ul id="treeview">
	<li><span>Mon compte</span>
		<ul>
			<li class="compte_accueil"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=compte_accueil">Accueil</a></li>
			<li class="compte_password"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=compte_password">Mot de passe</a></li>
			<li class="eval_demande"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=eval_demande">Demandes d\'évaluations</a></li>
		</ul>
	</li>
	<li><span>Relevés de compétences</span>
		<ul>
			<li class="releve_matiere"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=releve_matiere">Bilans sur une matière</a></li>';
if(mb_substr_count($_SESSION['ELEVE_OPTIONS'],'as'))
{
	$arbre.='
			<li class="releve_socle"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=releve_socle">Attestation de maîtrise du socle</a></li>
	';
}
$arbre.='
			<li class="releve_grille"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=releve_grille">Grilles sur un niveau</a></li>
		</ul>
	</li>
</ul>
';

echo'<div id="cadre_identite">';
echo	html($_SESSION['DENOMINATION']).'<br />';
echo	html($_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM']).'<q class="deconnecter" title="Se déconnecter."></q><br />';
echo	'<i><img alt="'.$_SESSION['USER_PROFIL'].'" src="./_img/menu/admin_'.$_SESSION['USER_PROFIL'].'.png" /> '.$_SESSION['USER_PROFIL'].' <span id="clock"><img alt="" src="./_img/clock_fixe.png" /> '.$_SESSION['DUREE_INACTIVITE'].' min</span><img alt="" src="./_img/point.gif" /></i><br />';
echo'</div>';
echo'<div id="appel_menu">MENU</div>';
echo str_replace('class="'.$FICHIER.'"><a ','class="'.$FICHIER.'"><a class="actif" ',$arbre);
?>
