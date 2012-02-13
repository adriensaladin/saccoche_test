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
$TITRE = "Gérer les professeurs coordonnateurs";
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_professeurs">DOC : Gestion des professeurs</a></span></p>

<form action="#" method="post" id="coord">

	<?php
	$tab_matiere = array(ID_MATIERE_TRANSVERSALE=>''); // Pour que ce soit la 1ère affichée
	$tab_user    = array();
	$matiere_id = 0;
	$nb_professeurs = 0;
	// Récupération de la liste des professeurs / matières
	$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_professeurs_par_matiere();
	if(count($DB_TAB))
	{
		foreach($DB_TAB as $DB_ROW)
		{
			if($matiere_id != $DB_ROW['matiere_id'])
			{
				// Nouvelle matière
				$tab_matiere[$DB_ROW['matiere_id']] = html($DB_ROW['matiere_nom']);
				$tab_user[$DB_ROW['matiere_id']] = '';
				$matiere_id = $DB_ROW['matiere_id'];
			}
			if(!is_null($DB_ROW['user_id']))
			{
				// Nouveau professeur
				$checked = ($DB_ROW['jointure_coord']) ? ' checked' : '' ;
				$id = $DB_ROW['matiere_id'].'x'.$DB_ROW['user_id'];
				$tab_user[$DB_ROW['matiere_id']] .= '<input type="checkbox" id="id_'.$id.'" name="f_tab_id" value="'.$id.'"'.$checked.' /> <label for="id_'.$id.'">'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).'</label><br />';
				$nb_professeurs++;
			}
		}
		if($nb_professeurs)
		{
			// Assemblage du tableau résultant
			$TH = array();
			$TB = array();
			$tab_mod = 4;
			$i = $tab_mod-1;
			$memo_tab_num = -1;
			foreach($tab_matiere as $matiere_id => $matiere_nom)
			{
				$tab_num = floor($i/$tab_mod);
				if($memo_tab_num!=$tab_num)
				{
					$memo_tab_num = $tab_num;
					$TH[$tab_num] = '';
					$TB[$tab_num] = '';
				}
				$i++;
				$nb = mb_substr_count($tab_user[$matiere_id],'<br />','UTF-8');
				$TH[$tab_num] .= '<th>'.$matiere_nom.'</th>';
				$TB[$tab_num] .= '<td>'.mb_substr($tab_user[$matiere_id],0,-6,'UTF-8').'</td>';
			}
			// Affichage du tableau résultant
			for($tab_i=0;$tab_i<=$tab_num;$tab_i++)
			{
				$class = ($tab_i) ? '' : ' style="float:right;margin-left:1em;margin-right:1ex"' ;
				echo'<table'.$class.'>';
				echo'<thead><tr>'.$TH[$tab_i].'</tr></thead>';
				echo'<tbody><tr>'.$TB[$tab_i].'</tr></tbody>';
				echo'</table><p>&nbsp;</p>';
			}
		}
		else
		{
			echo'<p>Aucun professeur affecté aux matières !</p>';
		}
	}
	else
	{
		echo'<p>Aucun professeur affecté aux matières !</p>';
	}
	?>

	<p>
		<button id="valider" type="button" class="valider">Valider ce choix de professeurs coordonnateurs.</button><label id="ajax_msg">&nbsp;</label>
	</p>
</form>

