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
if($_SESSION['STRUCTURE_ID']==ID_DEMO) {}

$action     = (isset($_POST['action']))  ? $_POST['action']                : '';
$etabl_id   = (isset($_POST['etabl']))   ? clean_entier($_POST['etabl'])   : 0;
$matiere_id = (isset($_POST['matiere'])) ? clean_entier($_POST['matiere']) : 0;

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Affichage du détail d'un référentiel pour une matière et un établissement donné
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
if( ($action=='Voir') && $matiere_id && $etabl_id )
{
	// A REFAIRE ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! ! !
	// Affichage de la liste des items pour la matière et le niveau sélectionnés
	$DB_SQL = 'SELECT * FROM sacoche_referentiel_domaine ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (sacoche_structure_id,domaine_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (sacoche_structure_id,theme_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_socle_entree USING (entree_id) ';
	$DB_SQL.= 'WHERE sacoche_structure_id=:structure_id AND matiere_id=:matiere_id AND niveau_id IN('.$_SESSION['NIVEAUX'].') ';
	$DB_SQL.= 'ORDER BY niveau_ordre ASC, domaine_ordre ASC, theme_ordre ASC, item_ordre ASC';
	$DB_VAR = array(':structure_id'=>$etabl_id,':matiere_id'=>$matiere_id);
	$DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	if(!count($DB_TAB))
	{
		exit('Aucun référentiel partagé par cet établissement pour cette matière...');
	}
	$niveau_id = 0;
	$domaine_id = 0;
	$theme_id = 0;
	$competence_id = 0;
	foreach($DB_TAB as $DB_ROW)
	{
		if( (!is_null($DB_ROW['niveau_id'])) && ($DB_ROW['niveau_id']!=$niveau_id) )
		{
			// nouveau niveau
			$niveau_id = $DB_ROW['niveau_id'];
			if($competence_id)
			{
				$competence_id = 0;
				echo'					</ul>'."\r\n";
				echo'				</li>'."\r\n";
			}
			if($theme_id)
			{
				$theme_id = 0;
				echo'			</ul>'."\r\n";
				echo'		</li>'."\r\n";
			}
			if($domaine_id)
			{
				$domaine_id = 0;
				echo'	</ul>'."\r\n";
				echo'</li>'."\r\n";
			}
			echo'<li class="li_m2"><span>'.html($DB_ROW['niveau_nom']).'</span>'."\r\n";
			echo'	<ul class="ul_n1">'."\r\n";
		}
		if( (!is_null($DB_ROW['domaine_id'])) && ($DB_ROW['domaine_id']!=$domaine_id) )
		{
			// nouveau domaine
			$domaine_id = $DB_ROW['domaine_id'];
			if($competence_id)
			{
				$competence_id = 0;
				echo'					</ul>'."\r\n";
				echo'				</li>'."\r\n";
			}
			if($theme_id)
			{
				$theme_id = 0;
				echo'			</ul>'."\r\n";
				echo'		</li>'."\r\n";
			}
			echo'		<li class="li_n1"><span>'.html($DB_ROW['domaine_nom']).'</span>'."\r\n";
			echo'			<ul class="ul_n2">'."\r\n";
		}
		if( (!is_null($DB_ROW['theme_id'])) && ($DB_ROW['theme_id']!=$theme_id) )
		{
			// nouveau thème
			$theme_id = $DB_ROW['theme_id'];
			if($competence_id)
			{
				$competence_id = 0;
				echo'					</ul>'."\r\n";
				echo'				</li>'."\r\n";
			}
			echo'				<li class="li_n2"><span>'.html($DB_ROW['theme_nom']).'</span>'."\r\n";
			echo'					<ul class="ul_n3">'."\r\n";
		}
		if(!is_null($DB_ROW['item_id']))
		{
			// nouvel item
			$competence_id = $DB_ROW['item_id'];
			$coef_texte    = '<img src="./_img/x'.$DB_ROW['item_coef'].'.gif" alt="" title="Coef '.$DB_ROW['item_coef'].'" />';
			$socle_image   = ($DB_ROW['entree_id']==0) ? 'off' : 'on' ;
			$socle_nom     = ($DB_ROW['entree_id']==0) ? 'Hors-socle.' : html($DB_ROW['entree_nom']) ;
			$socle_texte   = '<img src="./_img/socle_'.$socle_image.'.png" alt="" title="'.$socle_nom.'" />';
			$lien_image    = ($DB_ROW['item_lien']=='') ? 'off' : 'on' ;
			$lien_nom      = ($DB_ROW['item_lien']=='') ? 'Absence de ressource.' : html($DB_ROW['item_lien']) ;
			$lien_texte    = '<img src="./_img/link_'.$lien_image.'.png" alt="" title="'.$lien_nom.'" />';
			$texte_lien_avant = ($DB_ROW['item_lien']) ? '<a class="lien_ext" href="'.html($DB_ROW['item_lien']).'">' : '';
			$texte_lien_apres = ($DB_ROW['item_lien']) ? '</a>' : '';
			echo'								<li class="li_n3"><b>'.$coef_texte.$socle_texte.$lien_texte.$texte_lien_avant.html($DB_ROW['item_nom']).$texte_lien_apres.'</b></li>'."\r\n";
		}
	}
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
