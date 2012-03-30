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

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
$TITRE = "Bienvenue dans votre espace identifié !";

$tab_accueil = array( 'alert'=>'' , 'info'=>'' , 'help'=>'' , 'user'=>'' );

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Alertes pour l'administrateur
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($_SESSION['USER_PROFIL']=='administrateur')
{
	$DB_TAB = DB_STRUCTURE_COMMUN::DB_OPT_matieres_etabl();
	if(!is_array($DB_TAB))
	{
		$tab_accueil['alert'] .= '<p class="danger">Aucune matière n\'est rattachée à l\'établissement ! <a href="./index.php?page=administrateur_etabl_matiere">Gestion des matières.</a></p>';
	}
	$DB_TAB = DB_STRUCTURE_COMMUN::DB_OPT_niveaux_etabl();
	if(!count($DB_TAB))
	{
		$tab_accueil['alert'] .= '<p class="danger">Aucun niveau n\'est rattaché à l\'établissement ! <a href="./index.php?page=administrateur_etabl_niveau">Gestion des niveaux.</a></p>';
	}
	if($tab_accueil['alert'])
	{
		$tab_accueil['alert'] .= '<p><span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_administrateur__guide">DOC : Guide d\'un administrateur de SACoche.</a></span></p>';
	}
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Panneau d'informations
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

// A venir...

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Astuces
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

// A venir...

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Informations utilisateur : infos profil, infos selon profil, infos adresse de connexion
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

// infos profil
require_once('./_inc/tableau_profils.php'); // Charge $tab_profil_libelle[$profil][court|long][1|2]
$tab_accueil['user'] = '<p>Vous êtes connecté avec le statut <b>'.$tab_profil_libelle[$_SESSION['USER_PROFIL']]['long'][1].'</b>.</p>';
// infos selon profil
if($_SESSION['USER_PROFIL']=='parent')
{
	if($_SESSION['NB_ENFANTS'])
	{
		$tab_nom_enfants = array();
		foreach($_SESSION['OPT_PARENT_ENFANTS'] as $DB_ROW)
		{
			$tab_nom_enfants[] =html($DB_ROW['texte']);
		}
		$tab_accueil['user'] .= '<p>Élève(s) associé(s) à votre compte : '.implode(' ; ',$tab_nom_enfants).'</p>';
	}
	else
	{
		$tab_accueil['user'] .= '<p class="danger">'.$_SESSION['OPT_PARENT_ENFANTS'].'</p>';
	}
}
elseif($_SESSION['USER_PROFIL']=='administrateur')
{
	if(!$tab_accueil['alert'])
	{
		$tab_accueil['user'] .= '<p><span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_administrateur__guide">DOC : Guide d\'un administrateur de SACoche.</a></span></p>';
	}
}
else
{
	$tab_accueil['user'] .= '<p><span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=environnement_generalites__ergonomie_generale">DOC : Ergonomie générale.</a></span></p>';
}
// infos adresse de connexion
if($_SESSION['USER_PROFIL']=='webmestre')
{
	$tab_accueil['user'] .= '<p>Pour vous connecter à cet espace, utilisez l\'adresse <b>'.SERVEUR_ADRESSE.'/?webmestre</b></p>';
}
else
{
	if(HEBERGEUR_INSTALLATION=='multi-structures')
	{
		$tab_accueil['user'] .= '<p>Adresse à utiliser pour une sélection automatique de l\'établissement : <b>'.SERVEUR_ADRESSE.'/?id='.$_SESSION['BASE'].'</b></p>';
	}
	if($_SESSION['CONNEXION_MODE']!='normal')
	{
		$get_base = ($_SESSION['BASE']) ? '&amp;base='.$_SESSION['BASE'] : '' ;
		$tab_accueil['user'] .= '<p>Adresse à utiliser pour une connexion automatique avec l\'authentification externe : <b>'.SERVEUR_ADRESSE.'/?sso'.$get_base.'</b></p>';
	}
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Affichage
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

foreach($tab_accueil as $type => $contenu)
{
	if($contenu)
	{
		echo'<hr /><div class="p '.$type.'64">'.$contenu.'</div>';
	}
}
?>
