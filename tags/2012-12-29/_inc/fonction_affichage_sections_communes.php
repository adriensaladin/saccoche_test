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

/**
 * afficher_form_element_checkbox_eleves_professeur
 *
 * @param bool $with_pourcent
 * @return string
 */
function afficher_form_element_checkbox_eleves_professeur($with_pourcent)
{
	$affichage = '';
	$tab_regroupements = array();
	$tab_id = array('classe'=>'','groupe'=>'');
	// Recherche de la liste des classes et des groupes du professeur
	$DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_classes_groupes_professeur($_SESSION['USER_ID']);
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_regroupements[$DB_ROW['groupe_id']] = array('nom'=>$DB_ROW['groupe_nom'],'eleve'=>array());
		$tab_id[$DB_ROW['groupe_type']][] = $DB_ROW['groupe_id'];
	}
	// Recherche de la liste des élèves pour chaque classe du professeur
	if(is_array($tab_id['classe']))
	{
		$listing = implode(',',$tab_id['classe']);
		$DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_eleves_classes($listing);
		foreach($DB_TAB as $DB_ROW)
		{
			$tab_regroupements[$DB_ROW['eleve_classe_id']]['eleve'][$DB_ROW['user_id']] = $DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'].' ('.$DB_ROW['user_login'].')';
		}
	}
	// Recherche de la liste des élèves pour chaque groupe du professeur
	if(is_array($tab_id['groupe']))
	{
		$listing = implode(',',$tab_id['groupe']);
		$DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_eleves_groupes($listing);
		foreach($DB_TAB as $DB_ROW)
		{
			$tab_regroupements[$DB_ROW['groupe_id']]['eleve'][$DB_ROW['user_id']] = $DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'].' ('.$DB_ROW['user_login'].')';
		}
	}
	// Affichage de la liste des élèves (du professeur) pour chaque classe et groupe
	foreach($tab_regroupements as $groupe_id => $tab_groupe)
	{
		$gradient_pourcent = ($with_pourcent) ? '<span id="groupe_'.$groupe_id.'" class="gradient_pourcent"></span>' : '' ;
		$affichage .= '<ul class="ul_m1">'."\r\n";
		$affichage .= '	<li class="li_m1"><span class="deja">'.html($tab_groupe['nom']).'</span>'.$gradient_pourcent."\r\n";
		$affichage .= '		<ul class="ul_n3">'."\r\n";
		foreach($tab_groupe['eleve'] as $eleve_id => $eleve_nom)
		{
			// C'est plus compliqué que pour les items car un élève peut appartenir à une classe et plusieurs groupes => id du groupe mélé à l'id
			$affichage .= '			<li class="li_n3"><input id="id_'.$eleve_id.'_'.$groupe_id.'" name="f_eleves[]" type="checkbox" value="'.$eleve_id.'" /><label for="id_'.$eleve_id.'_'.$groupe_id.'"> '.html($eleve_nom).'</label><span></span></li>'."\r\n";
		}
		$affichage .= '		</ul>'."\r\n";
		$affichage .= '	</li>'."\r\n";
		$affichage .= '</ul>'."\r\n";
	}
	return $affichage;
}

/**
 * afficher_form_element_checkbox_collegues
 *
 * @param void
 * @return string
 */
function afficher_form_element_checkbox_collegues()
{
	$affichage = '';
	// Affichage de la liste des professeurs
	$DB_TAB = DB_STRUCTURE_COMMUN::DB_OPT_professeurs_etabl();
	if(is_string($DB_TAB))
	{
		echo $DB_TAB;
	}
	else
	{
		$nb_profs              = !empty($DB_TAB) ? count($DB_TAB) : 0 ;
		$nb_profs_maxi_par_col = 20;
		$nb_cols               = floor(($nb_profs-1)/$nb_profs_maxi_par_col)+1;
		$nb_profs_par_col      = ceil($nb_profs/$nb_cols);
		$tab_div = array_fill(0,$nb_cols,'');
		foreach($DB_TAB as $i => $DB_ROW)
		{
			$checked_and_disabled = ($DB_ROW['valeur']==$_SESSION['USER_ID']) ? ' checked disabled' : '' ; // readonly ne fonctionne pas sur un checkbox
			$tab_div[floor($i/$nb_profs_par_col)] .= '<input type="checkbox" name="f_profs[]" id="p_'.$DB_ROW['valeur'].'" value="'.$DB_ROW['valeur'].'"'.$checked_and_disabled.' /><label for="p_'.$DB_ROW['valeur'].'"> '.html($DB_ROW['texte']).'</label><br />';
		}
		$affichage .= '<p><a href="#prof_liste" id="prof_check_all"><img src="./_img/all_check.gif" alt="Tout cocher." /> Tout le monde</a>&nbsp;&nbsp;&nbsp;<a href="#prof_liste" id="prof_uncheck_all"><img src="./_img/all_uncheck.gif" alt="Tout décocher." /> Seulement moi</a></p>';
		$affichage .=  '<div class="prof_liste">'.implode('</div><div class="prof_liste">',$tab_div).'</div>';
	}
	return $affichage;
}

?>