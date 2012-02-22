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
$TITRE = "Professeurs &amp; classes / Professeurs principaux";
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_classes">DOC : Gestion des classes</a></span></p>

<hr />

<form action="#" method="post">

<?php
// Deux requêtes préliminaires pour ne pas manquer les classes sans professeurs et les professeurs sans classes
$tab_principal        = array(); // [i_classe][i_prof]
$tab_classes          = array();
$tab_profs            = array();
$tab_profs_par_classe = array();
$tab_classes_par_prof = array();
$tab_lignes_classes   = array();
$tab_lignes_profs     = array();

// Récupérer la liste des classes
$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_classes_avec_niveaux();
if(!count($DB_TAB))
{
	echo'<p class="danger">Aucune classe trouvée !</p>';
}
else
{
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_principal[$DB_ROW['groupe_id']][0] = '<th>'.html($DB_ROW['groupe_nom']).'</th>';
		$tab_classes[$DB_ROW['groupe_id']] = html($DB_ROW['groupe_nom']);
		$tab_profs_par_classe[$DB_ROW['groupe_id']] = '';
		$tab_lignes_classes[$DB_ROW['niveau_id']][] = $DB_ROW['groupe_id'];
	}

	// Récupérer la liste des professeurs
	$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users('professeur',$only_actifs=true,$with_classe=false);
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
			$tab_classes_par_prof[$DB_ROW['user_id']] = '';
			$tab_lignes_profs[floor($compteur/8)][] = $DB_ROW['user_id'];
			$compteur++;
		}

		// Initialiser les jointures
		foreach($tab_classes as $classe_id => $classe_nom)
		{
			foreach($tab_profs as $user_id => $user_nom)
			{
				$tab_principal[$classe_id][$user_id] = '<td class="off" title="'.$user_nom.'<br />'.$classe_nom.'"><input type="checkbox" value="'.$user_id.'" /></td>';
			}
		}

		// Récupérer la liste des jointures
		$liste_profs_id   = implode(',',array_keys($tab_profs));
		$liste_classes_id = implode(',',array_keys($tab_classes));
		$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_jointure_professeurs_groupes($liste_profs_id,$liste_classes_id);
		foreach($DB_TAB as $DB_ROW)
		{
			$tab_principal[$DB_ROW['groupe_id']][$DB_ROW['user_id']] = '<td class="on" title="'.$tab_profs[$DB_ROW['user_id']].'<br />'.$tab_classes[$DB_ROW['groupe_id']].'"><input type="checkbox" value="'.$DB_ROW['user_id'].'" checked /></td>';
			$checked = ($DB_ROW['jointure_pp']) ? ' checked' : '' ;
			$classe  = ($DB_ROW['jointure_pp']) ? 'on' : 'off' ;
			$tab_profs_par_classe[$DB_ROW['groupe_id']] .= '<div id="cp_'.$DB_ROW['groupe_id'].'_'.$DB_ROW['user_id'].'" class="'.$classe.'"><input type="checkbox" id="'.$DB_ROW['groupe_id'].'cp'.$DB_ROW['user_id'].'" value=""'.$checked.' /> <label for="'.$DB_ROW['groupe_id'].'cp'.$DB_ROW['user_id'].'">'.$tab_profs[$DB_ROW['user_id']].'</label></div>';
			$tab_classes_par_prof[$DB_ROW['user_id']]   .= '<div id="pc_'.$DB_ROW['user_id'].'_'.$DB_ROW['groupe_id'].'" class="'.$classe.'"><input type="checkbox" id="'.$DB_ROW['user_id'].'pc'.$DB_ROW['groupe_id'].'" value=""'.$checked.' /> <label for="'.$DB_ROW['user_id'].'pc'.$DB_ROW['groupe_id'].'">'.$tab_classes[$DB_ROW['groupe_id']].'</label></div>';
		}

		// Affichage du tableau principal
		echo'<table id="autocheckbox">';
		echo'<thead><tr><td class="nu"></td>'.implode($tab_principal[0]).'</tr></thead>';
		unset($tab_principal[0]);
		echo'<tbody>';
		foreach($tab_principal as $classe_id => $tab_colonnes)
		{
			echo'<tr id="tr_'.$classe_id.'">'.implode($tab_colonnes).'</tr>';
		}
		echo'</tbody></table>';

		// Assemblage du tableau des profs par classe
		$TH = array();
		$TB = array();
		$TF = array();
		foreach($tab_lignes_classes as $niveau_id => $tab_classe)
		{
			$TH[$niveau_id] = '';
			$TB[$niveau_id] = '';
			$TF[$niveau_id] = '';
			foreach($tab_classe as $classe_id)
			{
				$nb = mb_substr_count($tab_profs_par_classe[$classe_id],'</div>','UTF-8');
				$s = ($nb>1) ? 's' : '' ;
				$TH[$niveau_id] .= '<th id="cph_'.$classe_id.'">'.$tab_classes[$classe_id].'</th>';
				$TB[$niveau_id] .= '<td id="cpb_'.$classe_id.'">'.$tab_profs_par_classe[$classe_id].'</td>';
				$TF[$niveau_id] .= '<td id="cpf_'.$classe_id.'">'.$nb.' professeur'.$s.'</td>';
			}
		}

		// Affichage du tableau des profs par classe
		echo'<hr /><h2>Bilan des professeurs par classe</h2>';
		echo'<div class="astuce">Cocher les professeurs principaux.</div>';
		foreach($tab_lignes_classes as $niveau_id => $tab_classe)
		{
			echo'<table class="affectation">';
			echo'<thead><tr>'.$TH[$niveau_id].'</tr></thead>';
			echo'<tbody><tr>'.$TB[$niveau_id].'</tr></tbody>';
			echo'<tfoot><tr>'.$TF[$niveau_id].'</tr></tfoot>';
			echo'</table>';
		}

		// Assemblage du tableau des classes par prof
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
				$nb = mb_substr_count($tab_classes_par_prof[$user_id],'</div>','UTF-8');
				$s = ($nb>1) ? 's' : '' ;
				$TH[$ligne_id] .= '<th id="pch_'.$user_id.'">'.$tab_profs[$user_id].'</th>';
				$TB[$ligne_id] .= '<td id="pcb_'.$user_id.'">'.$tab_classes_par_prof[$user_id].'</td>';
				$TF[$ligne_id] .= '<td id="pcf_'.$user_id.'">'.$nb.' classe'.$s.'</td>';
			}
		}

		// Affichage du tableau des classes par prof
		echo'<hr /><h2>Bilan des classes par professeur</h2>';
		echo'<div class="astuce">Cocher les professeurs principaux.</div>';
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
