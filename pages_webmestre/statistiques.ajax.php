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

$tab_bases = (isset($_POST['bases'])) ? array_map('clean_entier',explode(',',$_POST['bases'])) : array() ;

$num  = (isset($_GET['num'])) ? (int)$_GET['num'] : 0 ;	// Numéro de l'étape en cours
$max  = (isset($_GET['max'])) ? (int)$_GET['max'] : 0 ;	// Nombre d'étapes à effectuer

function positif($n) {return $n;}
$tab_bases = array_filter($tab_bases,'positif');
$nb_bases  = count($tab_bases);

$dossier = './__tmp/export/';
$fichier_info = 'bases_contenu.txt';

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Récupération de la liste des structures avant recherche des stats
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
if( $nb_bases )
{
	// Mémoriser dans un fichier les données des structures concernées par les stats
	$fichier_texte = '';
	$DB_TAB = DB_WEBMESTRE_lister_structures( implode(',',$tab_bases) );
	foreach($DB_TAB as $DB_ROW)
	{
		$fichier_texte .= '<'.$DB_ROW['sacoche_base'].'>-<'.$DB_ROW['structure_denomination'].'>-<'.$DB_ROW['structure_contact_courriel'].'>-<'.$DB_ROW['structure_inscription_date'].'>'."\r\n";
	}
	file_put_contents($dossier.$fichier_info,$fichier_texte);
	$max = $nb_bases + 1 ; // La dernière étape consiste uniquement à effacer le fichier
	exit('ok-'.$max);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Etape de récupération des stats
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
elseif( $num && $max && ($num<$max) )
{
	// Récupérer la ligne de données
	$fichier_texte = file_get_contents($dossier.$fichier_info);
	$fichier_texte = file_get_contents($dossier.$fichier_info);
	$tab_ligne = explode("\r\n",$fichier_texte);
	// Envoyer une série de courriels
	$num_ligne = $num-1;
	list($base_id,$structure_denomination,$contact_courriel,$inscription_date) = explode('>-<',substr($tab_ligne[$num_ligne],1,-1));
	charger_parametres_mysql_supplementaires($base_id);
	list($prof_nb,$prof_use,$eleve_nb,$eleve_use,$score_nb) = DB_STRUCTURE_recuperer_statistiques();
	exit('ok-<tr><td>'.$base_id.'</td><td>'.html($structure_denomination).'</td><td>'.mailto($contact_courriel,'SACoche - Inscription inutilisée','contact').'</td><td>'.$inscription_date.'</td><td>'.$prof_nb.'</td><td>'.$prof_use.'</td><td>'.$eleve_nb.'</td><td>'.$eleve_use.'</td><td><i>'.sprintf("%07u",$score_nb).'</i>'.number_format($score_nb,0,'',' ').'</td></tr>');
}
elseif( $num && $max && ($num==$max) )
{
	// Supprimer les fichiers dont on n'a plus besoin
	unlink($dossier.$fichier_info);
	exit('ok');
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
