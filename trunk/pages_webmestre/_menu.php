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

// Menu webmestre

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}

$arbre='
<ul id="treeview">
	<li>Mon compte
		<ul>
			<li class="compte_accueil"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=compte_accueil">Accueil</a></li>
			<li class="compte_password"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=compte_password">Mot de passe du webmestre</a></li>
			<li class="compte_identite_installation"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=compte_identite_installation">Identité de l\'installation</a></li>
			<li class="compte_info_serveur"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=compte_info_serveur">Informations serveur</a></li>
			<li class="webmestre_maintenance"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=blocage-maintenance">Maintenance &amp; mise à jour</a></li>
			</ul>
	</li>
';
if(HEBERGEUR_INSTALLATION=='multi-structures')
{
	$arbre.='
	<li>Gestion des établissements
		<ul>
			<li class="webmestre_geographie"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=geographie">Zones géographiques</a></li>
			<li class="webmestre_structure"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=structure_multi">Établissements</a></li>
			<li class="webmestre_stats"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=statistiques_multi">Statistiques d\'utilisation</a></li>
			<li class="webmestre_newsletter"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=newsletter_multi">Lettre d\'information</a></li>
		</ul>
	</li>
	';
}
elseif(HEBERGEUR_INSTALLATION=='mono-structure')
{
	$arbre.='
	<li>Gestion de l\'établissement
		<ul>
			<li class="webmestre_stats"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=statistiques_mono">Statistiques d\'utilisation</a></li>
			<li class="admin_administrateur"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=init-mdp_mono">Mot de passe administrateur</a></li>
			<li class="admin_resilier"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=structure_resilier_mono">Résilier l\'inscription</a></li>
		</ul>
	</li>
	';
}
$arbre.='</ul>';
echo'<div id="cadre_identite">';
echo	html($_SESSION['DENOMINATION']).'<br />';
echo	html($_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM']).'<q class="deconnecter" title="Se déconnecter."></q><br />';
echo	'<i><img alt="'.$_SESSION['USER_PROFIL'].'" src="./_img/menu/admin_'.$_SESSION['USER_PROFIL'].'.png" /> '.$_SESSION['USER_PROFIL'].' <span id="clock"><img alt="" src="./_img/clock_fixe.png" /> '.$_SESSION['DUREE_INACTIVITE'].' min</span><img alt="" src="./_img/point.gif" /></i><br />';
echo'</div>';
echo'<div id="appel_menu">MENU</div>';
echo str_replace('class="'.$FICHIER.'"><a ','class="'.$FICHIER.'"><a class="actif" ',$arbre);
?>
