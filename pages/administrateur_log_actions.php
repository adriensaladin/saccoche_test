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
$TITRE = "Log des actions sensibles";
?>

<p class="astuce">Les actions sensibles sont enregistrées, ce qui permet aux administrateurs de rechercher quel compte est fautif en cas de problème...</p>

<?php
$fichier_log_chemin = './__private/log/base_'.$_SESSION['BASE'].'.php';
if(!file_exists($fichier_log_chemin))
{
	echo'<p class="danger">Le fichier n\'existe pas : propablemant qu\'aucune action sensible n\'a encore été effectuée !<p />';
}
else
{
	$fichier_log_contenu = file_get_contents($fichier_log_chemin);
	// 1 En extraire le plus récent (les 100 derniers enregistrements)
	$table_log_extrait = '<table><thead><tr><th>Date &amp; Heure</th><th>Utilisateur</th><th>Action</th></tr></thead><tbody>';
	$tab_lignes = extraire_lignes($fichier_log_contenu);
	$indice_ligne_debut = count($tab_lignes)-1 ;
	$indice_ligne_fin   = max(-1 , $indice_ligne_debut-100) ;
	$nb_lignes          = $indice_ligne_debut - $indice_ligne_fin ;
	$s = ($nb_lignes>1) ? 's' : '' ;
	for( $indice_ligne=$indice_ligne_debut ; $indice_ligne>$indice_ligne_fin ; $indice_ligne-- )
	{
		list( $balise_debut , $date_heure , $utilisateur , $action , $balise_fin ) = explode("\t",$tab_lignes[$indice_ligne]);
		$table_log_extrait .= '<tr><td>'.$date_heure.'</td><td>'.html($utilisateur).'</td><td>'.html($action).'</td></tr>';
	}
	$table_log_extrait .= '</tbody></table>';
	// 2 En faire un csv zippé récupérable
	$fichier_log_contenu = str_replace(array('<?php /*','*/ ?>'),'',$fichier_log_contenu);
	$dossier_export = './__tmp/export/';
	$fichier_export_nom = 'log_'.$_SESSION['BASE'].'_'.time();

	$zip = new ZipArchive();
	if ($zip->open($dossier_export.$fichier_export_nom.'.zip', ZIPARCHIVE::CREATE)===TRUE)
	{
		$zip->addFromString($fichier_export_nom.'.csv',csv($fichier_log_contenu));
		$zip->close();
	}
	// Afficher tout ça
	echo'<ul class="puce">';
	echo'<li><a class="lien_ext" href="'.$dossier_export.$fichier_export_nom.'.zip">Récupérer le fichier complet (format <em>csv</em>).</a></li>';
	echo'<li>Consulter les derniers logs ('.$nb_lignes.' ligne'.$s.') :</li>';
	echo'</ul>';
	echo'<p />';
	echo $table_log_extrait;
	echo'<p />';
}
?>
