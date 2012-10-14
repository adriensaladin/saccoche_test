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
$TITRE = "Bienvenue dans votre espace identifié";

// user + help + ecolo peuvent être masqués
// alert + info sont obligatoires
$tab_accueil = array( 'user'=>'' , 'alert'=>'' , 'info'=>array() , 'help'=>'' , 'ecolo'=>'' );
$tab_msg_rubrique_masquee = array( 'user'=>'Message de bienvenue' , 'help'=>'Astuce du jour' , 'ecolo'=>'Protégeons l\'environnement' );

// Le temps de la mise à jour [2012-06-08], pour éviter tout souci ; [TODO] peut être retiré dans un an environ.
if(!(isset($_SESSION['USER_PARAM_ACCUEIL'])))
{
	$_SESSION['USER_PARAM_ACCUEIL'] = 'user,alert,info,help,ecolo';
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Alertes (pour l'administrateur) ; affiché après mais à définir avant
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($_SESSION['USER_PROFIL']=='administrateur')
{
	$alerte_novice = FALSE ;
	if(!DB_STRUCTURE_ADMINISTRATEUR::compter_matieres_etabl())
	{
		$tab_accueil['alert'] .= '<p class="danger">Aucune matière n\'est choisie pour l\'établissement ! <a href="./index.php?page=administrateur_etabl_matiere">Gestion des matières.</a></p>';
		$alerte_novice = TRUE ;
	}
	if(!DB_STRUCTURE_ADMINISTRATEUR::compter_niveaux_etabl( TRUE /*with_specifiques*/ ))
	{
		$tab_accueil['alert'] .= '<p class="danger">Aucun niveau n\'est choisi pour l\'établissement ! <a href="./index.php?page=administrateur_etabl_niveau">Gestion des niveaux.</a></p>';
		$alerte_novice = TRUE ;
	}
	elseif(!DB_STRUCTURE_ADMINISTRATEUR::compter_niveaux_etabl( FALSE /*with_specifiques*/ ))
	{
		$tab_accueil['alert'] .= '<p class="danger">Aucun niveau de classe n\'est choisi pour l\'établissement ! <a href="./index.php?page=administrateur_etabl_niveau">Gestion des niveaux.</a></p>';
		$alerte_novice = TRUE ;
	}
	if(DB_STRUCTURE_ADMINISTRATEUR::DB_compter_devoirs_annee_scolaire_precedente())
	{
		$tab_accueil['alert'] .= '<p class="danger">Année scolaire précédente non archivée !<br />&nbsp;<br />Au changement d\'année scolaire il faut <a href="./index.php?page=administrateur_nettoyage">lancer l\'initialisation annuelle des données</a>.</p>';
	}
	if($alerte_novice)
	{
		$tab_accueil['alert'] .= '<p><span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_administrateur__guide">DOC : Guide d\'un administrateur de <em>SACoche</em>.</a></span></p>';
	}
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Message de bienvenue (informations utilisateur : infos profil, infos selon profil, infos adresse de connexion)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_accueil['user'] = '';
// infos connexion (pas si webmestre)
if(isset($_SESSION['DELAI_CONNEXION']))
{
	$tab_accueil['user'] .= '<p class="i"><TG> Bonjour <b>'.html($_SESSION['USER_PRENOM']).'</b>. ';
	if($_SESSION['FIRST_CONNEXION'])                           { $tab_accueil['user'] .= 'Heureux de faire votre connaissance&nbsp;; bonne découverte de <em>SACoche</em>&nbsp;!</p>'; }
	elseif($_SESSION['DELAI_CONNEXION']<  43200 /*12*3600*/)   { $tab_accueil['user'] .= 'Déjà de retour&nbsp;? Décidément on ne se quitte plus&nbsp;!</p>'; }
	elseif($_SESSION['DELAI_CONNEXION']< 108000 /*48*3600*/)   { $tab_accueil['user'] .= 'Bonne navigation, et merci de votre fidélité&nbsp;!</p>'; }
	elseif($_SESSION['DELAI_CONNEXION']< 604800 /*7*24*3600*/) { $tab_accueil['user'] .= 'Content de vous retrouver après cette pause de quelques jours&nbsp;!</p>'; }
	elseif($_SESSION['DELAI_CONNEXION']<3000000 /* <3024000*/) { $tab_accueil['user'] .= 'Quel plaisir de vous revoir&nbsp;: le temps semble long sans vous&nbsp;!</p>'; }
	else                                                       { $tab_accueil['user'] .= 'On ne s\'était pas vu depuis trop longtemps&nbsp;: vous nous avez manqué&nbsp;!</p>'; }
	unset( $_SESSION['FIRST_CONNEXION'] , $_SESSION['DELAI_CONNEXION'] );
	$_SESSION['DEUXIEME_PASSAGE'] = TRUE;
}
elseif(isset($_SESSION['DEUXIEME_PASSAGE']))
{
	$tab_accueil['user'] .= '<p class="i"><TG> Encore là <b>'.html($_SESSION['USER_PRENOM']).'</b>&nbsp;? Vous avez raison, faîtes comme chez vous&nbsp;!';
	unset($_SESSION['DEUXIEME_PASSAGE']);
	$_SESSION['PASSAGES_SUIVANTS'] = TRUE;
}
elseif(isset($_SESSION['PASSAGES_SUIVANTS']))
{
	$tab_accueil['user'] .= '<p class="i"><TG> Toujours là <b>'.html($_SESSION['USER_PRENOM']).'</b>&nbsp;? Pas de souci, restez le temps que vous voulez&nbsp;!';
}
// infos profil
require(CHEMIN_DOSSIER_INCLUDE.'tableau_profils.php'); // Charge $tab_profil_libelle[$profil][court|long][1|2]
$tab_accueil['user'] .= '<p>Vous êtes dans l\'environnement <b>'.$tab_profil_libelle[$_SESSION['USER_PROFIL']]['long'][1].'</b>.</p>';
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
		$tab_accueil['user'] .= '<p>Élève(s) associé(s) à votre compte&nbsp;: <b>'.implode('</b> ; <b>',$tab_nom_enfants).'</b></p>';
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
// infos adresse de connexion
if($_SESSION['USER_PROFIL']=='webmestre')
{
	$tab_accueil['user'] .= '<div>Pour vous connecter à cet espace, utilisez l\'adresse <b>'.URL_DIR_SACOCHE.'?webmestre</b></div>';
}
else
{
	if(HEBERGEUR_INSTALLATION=='multi-structures')
	{
		$tab_accueil['user'] .= '<div>Adresse à utiliser pour une sélection automatique de l\'établissement&nbsp;: <b>'.URL_DIR_SACOCHE.'?base='.$_SESSION['BASE'].'</b></div>';
	}
	if($_SESSION['CONNEXION_MODE']!='normal')
	{
		$get_base = ($_SESSION['BASE']) ? '&amp;base='.$_SESSION['BASE'] : '' ;
		$tab_accueil['user'] .= '<div>Adresse à utiliser pour une connexion automatique avec l\'authentification externe&nbsp;: <b>'.URL_DIR_SACOCHE.'?sso'.$get_base.'</b></div>';
	}
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Panneau d'informations ou message écolo
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($_SESSION['USER_PROFIL']!='webmestre')
{
	$DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_messages_user_destinataire($_SESSION['USER_ID']);
	if(!empty($DB_TAB))
	{
		foreach($DB_TAB as $key => $DB_ROW)
		{
			$tab_accueil['info'][$key] = '<p class="b u">Communication ('.html($DB_ROW['user_prenom']{0}.'. '.$DB_ROW['user_nom']).')&nbsp;:</p>'.'<p>'.nl2br(html($DB_ROW['message_contenu'])).'</p>';
		}
	}
	elseif($_SESSION['USER_PROFIL']!='administrateur')
	{
		$tab_accueil['ecolo'] = '<p class="b"><TG> Afin de préserver l\'environnement, n\'imprimer qu\'en cas de nécessité !</p><div>Enregistrer la version numérique d\'un document (grille, relevé, bilan) suffit pour le consulter, l\'archiver, le partager, &hellip;</div>';
	}
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Astuce du jour
// ////////////////////////////////////////////////////////////////////////////////////////////////////

/*
$nombre_indices = 10;
$coef_distorsion = 2;
$tab_indices = array();
for( $i=0 ; $i<100 ; $i++ )
{
	$i_alea = mt_rand(0,99) / 100; // nombre aléatoire entre 0,00 et 0,99
	$i_dist = pow($i_alea,$coef_distorsion) ; // distorsion pour accentuer le nombre de résultats proches de 0
	$indice = (int)floor($nombre_indices*$i_dist);
	$tab_indices[] = (int)$indice;
}
$tab_trie = array_count_values($tab_indices);
ksort($tab_trie);
var_dump( $tab_trie );
*/
require(CHEMIN_DOSSIER_INCLUDE.'tableau_astuces.php'); // Charge $tab_astuces[$profil][]
$astuce_nombre = (isset($tab_astuces[$_SESSION['USER_PROFIL']])) ? count($tab_astuces[$_SESSION['USER_PROFIL']]) : 0 ;
if($astuce_nombre)
{
	$coef_distorsion = 2;
	$i_alea = mt_rand(0,99) / 100; // nombre aléatoire entre 0,00 et 0,99
	$i_dist = pow($i_alea,$coef_distorsion) ; // distorsion pour accentuer le nombre de résultats proches de 0
	$indice = (int)floor($astuce_nombre*$i_dist);
	$tab_accueil['help'] .= '<p class="b i"><TG> Le saviez-vous ?</p>'.$tab_astuces[$_SESSION['USER_PROFIL']][$indice];
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Affichage
// ////////////////////////////////////////////////////////////////////////////////////////////////////
$tab_msg_rubrique_masquee = array( 'user'=>'Message de bienvenue' , 'help'=>'L\'astuce du moment' , 'ecolo'=>'Protégeons l\'environnement !' );

foreach($tab_accueil as $type => $contenu)
{
	if( is_string($contenu) && ($contenu!='') )
	{
		if(isset($tab_msg_rubrique_masquee[$type]))
		{
			$class_moins = (strpos($_SESSION['USER_PARAM_ACCUEIL'],$type)!==FALSE) ? '' : ' hide' ;
			$class_plus  = (strpos($_SESSION['USER_PARAM_ACCUEIL'],$type)===FALSE) ? '' : ' hide' ;
			$toggle_moins = '<a href="#toggle_accueil" class="to_'.$type.'"><img src="./_img/toggle_moins.gif" alt="" title="Masquer" /></a>';
			$toggle_plus  = '<div id="'.$type.'_plus" class="rien64'.$class_plus.'"><a href="#toggle_accueil" class="to_'.$type.'"><img src="./_img/toggle_plus.gif" alt="" title="Voir" /> '.$tab_msg_rubrique_masquee[$type].'</a></div>';
		}
		else
		{
			$class_moins = $class_plus = $toggle_moins = $toggle_plus = '' ;
		}
		echo'<hr />'.$toggle_plus.'<div id="'.$type.'_moins" class="p '.$type.'64'.$class_moins.'">'.str_replace('<TG>',$toggle_moins,$contenu).'</div>';
	}
	elseif( is_array($contenu) && count($contenu) )
	{
		echo'<hr /><div class="p '.$type.'64">'.implode('</div><hr /><div class="p '.$type.'64">',$contenu).'</div>';
	}
}
?>
<hr />