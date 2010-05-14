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

// Menu administrateur

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}

$arbre='
<ul id="treeview">
	<li><span>Mon compte</span>
		<ul>
			<li class="compte_accueil"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=compte_accueil">Accueil</a></li>
			<li class="compte_password"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=compte_password">Changer mon mot de passe</a></li>
			<li class="compte_info_serveur"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=compte_info_serveur">Informations serveur</a></li>
		</ul>
	</li>
	<li><span>Établissement</span>
		<ul>
			<li class="admin_administrateur"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=administrateur">Administrateurs</a></li>
			<li class="admin_etabl"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=etabl">Paramétrages</a></li>
			<li class="admin_dump"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=dump">Sauvegarde / Restauration</a></li>
			<li class="admin_blocage"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=blocage">Blocage de l\'application</a></li>
			<li class="admin_resilier"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=resilier">Résilier l\'inscription</a></li>
		</ul>
	</li>
	<li><span>Utilisateurs et affectations</span>
		<ul>
			<li class="admin_fichier"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=fichier">Import / Export</a></li>
			<li class="admin_periode"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=periode">Périodes</a></li>
			<li class="admin_classe"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=classe">Classes</a></li>
			<li class="admin_groupe"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=groupe">Groupes</a></li>
			<li class="admin_eleve"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=eleve">Élèves</a></li>
			<li class="admin_professeur"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=professeur">Professeurs</a></li>
			<li class="admin_directeur"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=directeur">Directeurs</a></li>
			<li class="admin_statut"><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=statut">Statuts</a></li>
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
echo	html($_SESSION['DENOMINATION']).'<br />';
echo	html($_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM']).'<q class="deconnecter" title="Se déconnecter."></q><br />';
echo	'<i><img alt="'.$_SESSION['USER_PROFIL'].'" src="./_img/menu/admin_'.$_SESSION['USER_PROFIL'].'.png" /> '.$_SESSION['USER_PROFIL'].' <span id="clock"><img alt="" src="./_img/clock_fixe.png" /> '.$_SESSION['DUREE_INACTIVITE'].' min</span><img alt="" src="./_img/point.gif" /></i><br />';
echo'</div>';
echo'<div id="appel_menu">MENU</div>';
echo str_replace('class="'.$FICHIER.'"><a ','class="'.$FICHIER.'"><a class="actif" ',$arbre);
?>
