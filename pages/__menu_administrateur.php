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

// Menu administrateur

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}

$arbre='
<ul id="treeview">
	<li>Accueil &amp; Informations
		<ul>
			<li class="compte_accueil"><a href="./index.php?page=compte_accueil">Accueil</a></li>
			<li class="compte_password"><a href="./index.php?page=compte_password">Changer mon mot de passe</a></li>
			<li class="compte_info_serveur"><a href="./index.php?page=compte_info_serveur">Informations serveur</a></li>
		</ul>
	</li>
	<li>Établissement
		<ul>
			<li class="administrateur_administrateur"><a href="./index.php?page=administrateur_administrateur">Administrateurs</a></li>
			<li class="administrateur_etabl"><a href="./index.php?page=administrateur_etabl">Paramétrages</a></li>
			<li class="administrateur_dump"><a href="./index.php?page=administrateur_dump">Sauvegarde / Restauration de la base</a></li>
			<li class="administrateur_nettoyage"><a href="./index.php?page=administrateur_nettoyage">Nettoyage / Initialisation de la base</a></li>
			<li class="administrateur_blocage"><a href="./index.php?page=administrateur_blocage">Blocage de l\'application</a></li>
			<li class="administrateur_resilier"><a href="./index.php?page=administrateur_resilier">Résilier l\'inscription</a></li>
		</ul>
	</li>
	<li>Utilisateurs &amp; Affectations
		<ul>
			<li class="administrateur_fichier"><a href="./index.php?page=administrateur_fichier">Import / Export</a></li>
			<li class="administrateur_periode"><a href="./index.php?page=administrateur_periode">Périodes</a></li>
			<li class="administrateur_classe"><a href="./index.php?page=administrateur_classe">Classes</a></li>
			<li class="administrateur_groupe"><a href="./index.php?page=administrateur_groupe">Groupes</a></li>
			<li class="administrateur_eleve"><a href="./index.php?page=administrateur_eleve">Élèves</a></li>
			<li class="administrateur_professeur"><a href="./index.php?page=administrateur_professeur">Professeurs</a></li>
			<li class="administrateur_directeur"><a href="./index.php?page=administrateur_directeur">Directeurs</a></li>
			<li class="administrateur_statut"><a href="./index.php?page=administrateur_statut">Statuts</a></li>
		</ul>
	</li>
	<li>Référentiels
		<ul>
			<li class="administrateur_codes_couleurs"><a href="./index.php?page=administrateur_codes_couleurs">Choix des codes et des couleurs</a></li>
			<li class="administrateur_algorithme_gestion"><a href="./index.php?page=administrateur_algorithme_gestion">Paramétrage de l\'algorithme</a></li>
			<li class="consultation_referentiel_interne"><a href="./index.php?page=consultation_referentiel_interne">Consultation des référentiels en place</a></li>
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
echo str_replace('class="'.$PAGE.'"><a ','class="'.$PAGE.'"><a class="actif" ',$arbre);
?>
