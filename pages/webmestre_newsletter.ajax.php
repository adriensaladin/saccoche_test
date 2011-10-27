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

$tab_base_id = (isset($_POST['f_listing_id'])) ? array_filter( array_map( 'clean_entier' , explode(',',$_POST['f_listing_id']) ) , 'positif' ) : array() ;
$nb_bases    = count($tab_base_id);

$action  = (isset($_POST['f_action']))  ? clean_texte($_POST['f_action'])  : '';
$titre   = (isset($_POST['f_titre']))   ? clean_texte($_POST['f_titre'])   : '';
$contenu = (isset($_POST['f_contenu'])) ? clean_texte($_POST['f_contenu']) : '';
$num     = (isset($_POST['num']))       ? clean_entier($_POST['num'])      : 0 ;	// Numéro de l'étape en cours
$max     = (isset($_POST['max']))       ? clean_entier($_POST['max'])      : 0 ;	// Nombre d'étapes à effectuer
$pack    = 10 ;	// Nombre de mails envoyés à chaque étape

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Préparation d'une lettre d'informations avant envoi
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='envoyer') && $titre && $contenu && $nb_bases )
{
	// Mémoriser en session le nb d'envoi / le titre / le contenu de la lettre d'informations
	$_SESSION['tmp']['nombre']  = $nb_bases ;
	$_SESSION['tmp']['titre']   = $titre ;
	$_SESSION['tmp']['contenu'] = $contenu ;
	// Mémoriser en session les données des contacts concernés par la lettre
	$_SESSION['tmp']['infos'] = array();
	$DB_TAB = DB_WEBMESTRE_WEBMESTRE::DB_lister_contacts_cibles( implode(',',$tab_base_id) );
	foreach($DB_TAB as $DB_ROW)
	{
		$_SESSION['tmp']['infos'][] = array(
			'base_id'          => $DB_ROW['contact_id'] ,
			'contact_nom'      => $DB_ROW['contact_nom'] ,
			'contact_prenom'   => $DB_ROW['contact_prenom'] ,
			'contact_courriel' => $DB_ROW['contact_courriel']
		);
	}
	// Retour
	$max = 1 + floor($nb_bases/$pack) + 1 ; // La dernière étape consistera uniquement à vider la session temporaire
	exit('ok-'.$max);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Etape d'envoi d'une lettre d'informations
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='envoyer') && $num && $max && ($num<$max) )
{
	// Envoyer une série de courriels
	$i_min = ($num-1)*10;
	$i_max = min( $_SESSION['tmp']['nombre'] , $num*10);
	for($i=$i_min ; $i<$i_max ; $i++)
	{
		extract($_SESSION['tmp']['infos'][$i]); // $base_id $contact_nom $contact_prenom $contact_courriel
		$texte = 'Bonjour '.$contact_prenom.' '.$contact_nom.'.'."\r\n\r\n";
		$texte.= $_SESSION['tmp']['contenu']."\r\n\r\n";
		$texte.= 'Rappel des adresses à utiliser :'."\r\n";
		$texte.= SERVEUR_ADRESSE.'/?id='.$base_id.' (hébergement de l\'établissement)'."\r\n";
		$texte.= SERVEUR_PROJET.' (site du projet SACoche)'."\r\n\r\n";
		$texte.= 'Cordialement'."\r\n";
		$texte.= WEBMESTRE_PRENOM.' '.WEBMESTRE_NOM."\r\n\r\n";
		$courriel_bilan = envoyer_webmestre_courriel($contact_courriel,$_SESSION['tmp']['titre'],$texte,false);
		if(!$courriel_bilan)
		{
			exit('Erreur lors de l\'envoi du courriel !');
		}
	}
	exit('ok');
}
if( ($action=='envoyer') && $num && $max && ($num==$max) )
{
	unset($_SESSION['tmp']);
	exit('ok');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Supprimer plusieurs structures existantes
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='supprimer') && $nb_bases )
{
	foreach($tab_base_id as $base_id)
	{
		supprimer_multi_structure($base_id);
	}
	exit('<ok>');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	On ne devrait pas en arriver là...
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

exit('Erreur avec les données transmises !');

?>
