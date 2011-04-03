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
$TITRE = "Gérer les professeurs principaux";
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_professeurs">DOC : Gestion des professeurs</a></span></p>

<form id="pp" action="">

	<?php
	$tab_niveau_groupe = array();
	$tab_user          = array();
	$groupe_id = 0;
	$nb_professeurs = 0;
	// Récupération de la liste des professeurs / classes
	$DB_TAB = DB_STRUCTURE_lister_classes_avec_professeurs();
	if(count($DB_TAB))
	{
		foreach($DB_TAB as $DB_ROW)
		{
			if($groupe_id != $DB_ROW['groupe_id'])
			{
				// Nouvelle classe
				$tab_niveau_groupe[$DB_ROW['niveau_id']][$DB_ROW['groupe_id']] = html($DB_ROW['groupe_nom']);
				$tab_user[$DB_ROW['groupe_id']] = '';
				$groupe_id = $DB_ROW['groupe_id'];
			}
			if(!is_null($DB_ROW['user_id']))
			{
				// Nouveau professeur
				$checked = ($DB_ROW['jointure_pp']) ? ' checked="checked"' : '' ;
				$id = $DB_ROW['groupe_id'].'x'.$DB_ROW['user_id'];
				$tab_user[$DB_ROW['groupe_id']] .= '<input type="checkbox" id="id_'.$id.'" name="f_tab_id" value="'.$id.'"'.$checked.' /> <label for="id_'.$id.'">'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).'</label><br />';
				$nb_professeurs++;
			}
		}
		if($nb_professeurs)
		{
			// Assemblage du tableau résultant
			$TH = array();
			$TB = array();
			foreach($tab_niveau_groupe as $niveau_id => $tab_groupe)
			{
				$TH[$niveau_id] = '';
				$TB[$niveau_id] = '';
				foreach($tab_groupe as $groupe_id => $groupe_nom)
				{
					$nb = mb_substr_count($tab_user[$groupe_id],'<br />','UTF-8');
					$TH[$niveau_id] .= '<th>'.$groupe_nom.'</th>';
					$TB[$niveau_id] .= '<td>'.mb_substr($tab_user[$groupe_id],0,-6,'UTF-8').'</td>';
				}
			}
			// Affichage du tableau résultant
			foreach($tab_niveau_groupe as $niveau_id => $tab_groupe)
			{
				echo'<table>';
				echo'<thead><tr>'.$TH[$niveau_id].'</tr></thead>';
				echo'<tbody><tr>'.$TB[$niveau_id].'</tr></tbody>';
				echo'</table><p />';
			}
		}
		else
		{
			echo'<p>Aucun professeur affecté aux classes !</p>';
		}
	}
	else
	{
		echo'<p>Aucun professeur affecté aux classes !</p>';
	}
	?>

	<p>
		<button id="valider" type="button"><img alt="" src="./_img/bouton/valider.png" /> Valider ce choix de professeurs principaux.</button><label id="ajax_msg">&nbsp;</label>
	</p>
</form>

