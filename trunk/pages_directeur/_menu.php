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

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}

$arbre='
<ul id="treeview">
	<li><span>Mon compte</span>
		<ul>
			<li class="accueil"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=accueil">Accueil</a></li>
			<li class="password"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=password">Mot de passe</a></li>
			<li class="fichier"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=fichier">Export listings</a></li>
		</ul>
	</li>
	<li>Référentiels
		<ul>
			<li class="algorithme_info"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=algorithme_info">Algorithme de calcul (pour information)</a></li>
			<li class="referentiel_view"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=referentiel_view">Référentiels en place (pour information)</a></li>
		</ul>
	</li>
	<li><span>Relevés &amp; Bilans</span>
		<ul>
			<li class="releve_grille"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=releve&amp;section=grille">Grilles sur un niveau</a></li>
			<li class="releve_matiere"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=releve&amp;section=matiere">Bilans sur une matière</a></li>
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
echo str_replace('class="'.$FICHIER.'"><a ','class="'.$FICHIER.'"><a class="actif" ',$arbre);
?>
