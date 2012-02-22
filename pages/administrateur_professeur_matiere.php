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
$TITRE = "Professeurs &amp; matières / Professeurs coordonnateurs";
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_professeurs">DOC : Gestion des professeurs</a></span></p>

<hr />

<form action="#" method="post">

<?php
// Deux requêtes préliminaires pour ne pas manquer les matières sans professeurs et les professeurs sans matières
$tab_principal         = array(); // [i_matiere][i_prof]
$tab_matieres          = array();
$tab_profs             = array();
$tab_profs_par_matiere = array();
$tab_matieres_par_prof = array();
$tab_lignes_matieres   = array();
$tab_lignes_profs      = array();

// Récupérer la liste des matières
$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_matieres_etablissement( TRUE /*order_by_name*/ );
if(!count($DB_TAB))
{
	echo'<p class="danger">Aucune matière trouvée !</p>';
}
else
{
	$compteur = 0 ;
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_principal[$DB_ROW['matiere_id']][0] = '<th>'.html($DB_ROW['matiere_nom']).'</th>';
		$tab_matieres[$DB_ROW['matiere_id']] = html($DB_ROW['matiere_nom']);
		$tab_profs_par_matiere[$DB_ROW['matiere_id']] = '';
		$tab_lignes_matieres[floor($compteur/8)][] = $DB_ROW['matiere_id'];
		$compteur++;
	}

	// Récupérer la liste des professeurs
	$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users('professeur',$only_actifs=true,$with_matiere=false);
	if(!count($DB_TAB))
	{
		echo'<p class="danger">Aucun compte professeur trouvé !</p>';
	}
	else
	{
		$compteur = 0 ;
		foreach($DB_TAB as $DB_ROW)
		{
			$tab_principal[0][$DB_ROW['user_id']] = '<th id="th_'.$DB_ROW['user_id'].'"><img alt="'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).'" src="./_img/php/etiquette.php?dossier='.$_SESSION['BASE'].'&amp;nom='.urlencode($DB_ROW['user_nom']).'&amp;prenom='.urlencode($DB_ROW['user_prenom']).'" /></th>';
			$tab_profs[$DB_ROW['user_id']] = html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']);
			$tab_matieres_par_prof[$DB_ROW['user_id']] = '';
			$tab_lignes_profs[floor($compteur/8)][] = $DB_ROW['user_id'];
			$compteur++;
		}

		// Initialiser les jointures
		foreach($tab_matieres as $matiere_id => $matiere_nom)
		{
			foreach($tab_profs as $user_id => $user_nom)
			{
				$tab_principal[$matiere_id][$user_id] = '<td class="off" title="'.$user_nom.'<br />'.$matiere_nom.'"><input type="checkbox" value="'.$user_id.'" /></td>';
			}
		}

		// Récupérer la liste des jointures
		$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_jointure_professeurs_matieres();
		foreach($DB_TAB as $DB_ROW)
		{
			if( (isset($tab_profs[$DB_ROW['user_id']])) && (isset($tab_matieres[$DB_ROW['matiere_id']])) )
			{
				$tab_principal[$DB_ROW['matiere_id']][$DB_ROW['user_id']] = '<td class="on" title="'.$tab_profs[$DB_ROW['user_id']].'<br />'.$tab_matieres[$DB_ROW['matiere_id']].'"><input type="checkbox" value="'.$DB_ROW['user_id'].'" checked /></td>';
				$checked = ($DB_ROW['jointure_coord']) ? ' checked' : '' ;
				$classe  = ($DB_ROW['jointure_coord']) ? 'on' : 'off' ;
				$tab_profs_par_matiere[$DB_ROW['matiere_id']] .= '<div id="mp_'.$DB_ROW['matiere_id'].'_'.$DB_ROW['user_id'].'" class="'.$classe.'"><input type="checkbox" id="'.$DB_ROW['matiere_id'].'mp'.$DB_ROW['user_id'].'" value=""'.$checked.' /> <label for="'.$DB_ROW['matiere_id'].'mp'.$DB_ROW['user_id'].'">'.$tab_profs[$DB_ROW['user_id']].'</label></div>';
				$tab_matieres_par_prof[$DB_ROW['user_id']]   .= '<div id="pm_'.$DB_ROW['user_id'].'_'.$DB_ROW['matiere_id'].'" class="'.$classe.'"><input type="checkbox" id="'.$DB_ROW['user_id'].'pm'.$DB_ROW['matiere_id'].'" value=""'.$checked.' /> <label for="'.$DB_ROW['user_id'].'pm'.$DB_ROW['matiere_id'].'">'.$tab_matieres[$DB_ROW['matiere_id']].'</label></div>';
			}
		}

		// Affichage du tableau principal
		echo'<table id="autocheckbox">';
		echo'<thead><tr><td class="nu"></td>'.implode($tab_principal[0]).'</tr></thead>';
		unset($tab_principal[0]);
		echo'<tbody>';
		foreach($tab_principal as $matiere_id => $tab_colonnes)
		{
			echo'<tr id="tr_'.$matiere_id.'">'.implode($tab_colonnes).'</tr>';
		}
		echo'</tbody></table>';

		// Assemblage du tableau des profs par matière
		$TH = array();
		$TB = array();
		$TF = array();
		foreach($tab_lignes_matieres as $niveau_id => $tab_matiere)
		{
			$TH[$niveau_id] = '';
			$TB[$niveau_id] = '';
			$TF[$niveau_id] = '';
			foreach($tab_matiere as $matiere_id)
			{
				$nb = mb_substr_count($tab_profs_par_matiere[$matiere_id],'</div>','UTF-8');
				$s = ($nb>1) ? 's' : '' ;
				$TH[$niveau_id] .= '<th id="mph_'.$matiere_id.'">'.$tab_matieres[$matiere_id].'</th>';
				$TB[$niveau_id] .= '<td id="mpb_'.$matiere_id.'">'.$tab_profs_par_matiere[$matiere_id].'</td>';
				$TF[$niveau_id] .= '<td id="mpf_'.$matiere_id.'">'.$nb.' professeur'.$s.'</td>';
			}
		}

		// Affichage du tableau des profs par matière
		echo'<hr /><h2>Bilan des professeurs par matière</h2>';
		echo'<div class="astuce">Cocher les professeurs coordonnateurs.</div>';
		foreach($tab_lignes_matieres as $niveau_id => $tab_matiere)
		{
			echo'<table class="affectation">';
			echo'<thead><tr>'.$TH[$niveau_id].'</tr></thead>';
			echo'<tbody><tr>'.$TB[$niveau_id].'</tr></tbody>';
			echo'<tfoot><tr>'.$TF[$niveau_id].'</tr></tfoot>';
			echo'</table>';
		}

		// Assemblage du tableau des matières par prof
		$TH = array();
		$TB = array();
		$TF = array();
		foreach($tab_lignes_profs as $ligne_id => $tab_user)
		{
			$TH[$ligne_id] = '';
			$TB[$ligne_id] = '';
			$TF[$ligne_id] = '';
			foreach($tab_user as $user_id)
			{
				$nb = mb_substr_count($tab_matieres_par_prof[$user_id],'</div>','UTF-8');
				$s = ($nb>1) ? 's' : '' ;
				$TH[$ligne_id] .= '<th id="pmh_'.$user_id.'">'.$tab_profs[$user_id].'</th>';
				$TB[$ligne_id] .= '<td id="pmb_'.$user_id.'">'.$tab_matieres_par_prof[$user_id].'</td>';
				$TF[$ligne_id] .= '<td id="pmf_'.$user_id.'">'.$nb.' matière'.$s.'</td>';
			}
		}

		// Affichage du tableau des matières par prof
		echo'<hr /><h2>Bilan des matières par professeur</h2>';
		echo'<div class="astuce">Cocher les professeurs coordonnateurs.</div>';
		foreach($tab_lignes_profs as $ligne_id => $tab_user)
		{
			echo'<table class="affectation">';
			echo'<thead><tr>'.$TH[$ligne_id].'</tr></thead>';
			echo'<tbody><tr>'.$TB[$ligne_id].'</tr></tbody>';
			echo'<tfoot><tr>'.$TF[$ligne_id].'</tr></tfoot>';
			echo'</table>';
		}

	}
}
?>

</form>
