<?php
/**
 * @version $Id: professeur_coordonnateur.php 8 2009-10-30 20:56:02Z thomas $
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009
 * 
 * ****************************************************************************************************
 * SACoche [http://competences.sesamath.net] - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath [http://www.sesamath.net]
 * Distribution sous licence libre prévue pour l'été 2010.
 * ****************************************************************************************************
 * 
 */

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
$TITRE = "Gérer les professeurs coordonnateurs";
?>

<p class="hc">
	<span class="manuel"><a class="pop_up" href="./aide.php?fichier=gestion_professeurs">DOC : Gestion des professeurs</a></span>
</p>

<form id="coord" action="">

	<?php
	$tab_matiere = array();
	$tab_user    = array();
	$matiere_id = 0;
	$nb_professeurs   = 0;
	// Récupération de la liste des professeurs par matières
	$DB_SQL = 'SELECT * FROM livret_matiere ';
	$DB_SQL.= 'LEFT JOIN livret_jointure_user_matiere USING (livret_matiere_id) ';
	$DB_SQL.= 'LEFT JOIN livret_user USING (livret_structure_id,livret_user_id) ';
	$DB_SQL.= ($_SESSION['MATIERES']) ? 'WHERE livret_matiere_structure_id=:structure_id OR (livret_structure_id=:structure_id AND livret_matiere_id IN('.$_SESSION['MATIERES'].')) ' : 'WHERE livret_matiere_structure_id=:structure_id ';
	$DB_SQL.= 'ORDER BY livret_matiere_transversal DESC, livret_matiere_nom ASC, livret_user_nom ASC, livret_user_prenom ASC';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID']);
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	if(count($DB_TAB))
	{
		foreach($DB_TAB as $key => $DB_ROW)
		{
			if($matiere_id != $DB_ROW['livret_matiere_id'])
			{
				// Nouvelle matière
				$tab_matiere[$DB_ROW['livret_matiere_id']] = html($DB_ROW['livret_matiere_nom']);
				$tab_user[$DB_ROW['livret_matiere_id']] = '';
				$matiere_id = $DB_ROW['livret_matiere_id'];
			}
			if(!is_null($DB_ROW['livret_user_id']))
			{
				// Nouveau professeur
				if($DB_ROW['livret_user_statut'])
				{
					$checked = ($DB_ROW['livret_jointure_coord']) ? ' checked="checked"' : '' ;
					$id = $DB_ROW['livret_matiere_id'].'x'.$DB_ROW['livret_user_id'];
					$tab_user[$DB_ROW['livret_matiere_id']] .= '<input type="checkbox" id="id_'.$id.'" name="f_tab_id" value="'.$id.'"'.$checked.' /> <label for="id_'.$id.'">'.html($DB_ROW['livret_user_nom'].' '.$DB_ROW['livret_user_prenom']).'</label><br />';
					$nb_professeurs++;
				}
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
				echo'</table><p />';
			}
		}
		else
		{
			echo'<p>Aucun professeur affecté aux matières !</p>';
		}
	}
	else
	{
		echo'<p>Aucune matière enregistrée ou associée à l\'établissement !</p>';
	}
	?>

	<p>
		<input id="f_submit" type="button" value="Valider ce choix de professeurs coordonnateurs." /> <label id="ajax_msg">&nbsp;</label>
	</p>
</form>

