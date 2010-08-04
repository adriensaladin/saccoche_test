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

$titre     = (isset($_POST['f_titre']))   ? clean_texte($_POST['f_titre'])   : '';
$contenu   = (isset($_POST['f_contenu'])) ? clean_texte($_POST['f_contenu']) : '';
$tab_bases = (isset($_POST['bases']))     ? array_map('clean_entier',explode(',',$_POST['bases'])) : array() ;

$num  = (isset($_GET['num'])) ? (int)$_GET['num'] : 0 ;	// Numéro de l'étape en cours
$max  = (isset($_GET['max'])) ? (int)$_GET['max'] : 0 ;	// Nombre d'étapes à effectuer
$pack = 10 ;	// Nombre de mails envoyés à chaque étape

function positif($n) {return $n;}
$tab_bases = array_filter($tab_bases,'positif');
$nb_bases  = count($tab_bases);

$dossier = './__tmp/export/';
$fichier_contenu = 'lettre_contenu.txt';
$fichier_contact = 'lettre_contact.txt';

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Préparation d'une lettre d'informations avant envoi
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
if( $titre && $contenu && $nb_bases )
{
	// Mémoriser dans un fichier le titre et le contenu de la lettre d'informations
	$fichier_texte = $titre.' ◄■► '.$contenu."\r\n";
	Ecrire_Fichier($dossier.$fichier_contenu,$fichier_texte);
	// Mémoriser dans un fichier les données des contacts concernés par la lettre
	$fichier_texte = '';
	$DB_TAB = DB_WEBMESTRE_lister_contacts_cibles( implode(',',$tab_bases) );
	foreach($DB_TAB as $DB_ROW)
	{
		$fichier_texte .= '<'.$DB_ROW['contact_id'].'>-<'.$DB_ROW['contact_nom'].'>-<'.$DB_ROW['contact_prenom'].'>-<'.$DB_ROW['contact_courriel'].'>'."\r\n";
	}
	Ecrire_Fichier($dossier.$fichier_contact,$fichier_texte);
	$max = 1 + floor($nb_bases/$pack) + 1 ; // La dernière étape consiste uniquement à effacer les fichiers
	exit('ok-'.$max);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Etape d'envoi d'une lettre d'informations
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
elseif( $num && $max && ($num<$max) )
{
	// Récupérer le titre et le contenu de la lettre d'informations
	$fichier_texte = file_get_contents($dossier.$fichier_contenu);
	list($titre,$contenu) = explode(' ◄■► ',$fichier_texte);
	// Récupérer une série de données des contacts concernés par la lettre
	$fichier_texte = file_get_contents($dossier.$fichier_contact);
	$tab_ligne = explode("\r\n",$fichier_texte);
	// Envoyer une série de courriels
	$i_min = ($num-1)*10;
	$i_max = min( count($tab_ligne)-1 , $num*10); // -1 à cause du dernier retour chariot dans $fichier_contact qui créé une ligne de trop
	for($i=$i_min ; $i<$i_max ; $i++)
	{
		list($base_id,$contact_nom,$contact_prenom,$contact_courriel) = explode('>-<',substr($tab_ligne[$i],1,-1));
		$texte = 'Bonjour '.$contact_prenom.' '.$contact_nom.'.'."\r\n\r\n";
		$texte.= $contenu."\r\n\r\n";
		$texte.= 'Rappel des adresses à utiliser :'."\r\n";
		$texte.= SERVEUR_ADRESSE.'?id='.$base_id.' (hébergement de l\'établissement)'."\r\n";
		$texte.= SERVEUR_ADRESSE.'?id='.$base_id.'&admin'.' (connexion administrateur)'."\r\n";
		$texte.= SERVEUR_PROJET.' (site du projet SACoche)'."\r\n\r\n";
		$texte.= 'Cordialement'."\r\n";
		$texte.= WEBMESTRE_PRENOM.' '.WEBMESTRE_NOM."\r\n\r\n";
		$courriel_bilan = envoyer_webmestre_courriel($contact_courriel,$titre,$texte,false);
		if(!$courriel_bilan)
		{
			exit('Erreur lors de l\'envoi du courriel !');
		}
	}
	exit('ok');
}
elseif( $num && $max && ($num==$max) )
{
	// Supprimer les fichiers dont on n'a plus besoin
	unlink($dossier.$fichier_contenu);
	unlink($dossier.$fichier_contact);
	exit('ok');
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
