<?php
/**
 * @version $Id$
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
$TITRE = "Gérer les professeurs principaux";
?>

<p class="hc">
	<span class="manuel"><a class="pop_up" href="./aide.php?fichier=gestion_professeurs">DOC : Gestion des professeurs</a></span>
</p>

<form id="pp" action="">

	<?php
	$tab_niveau_groupe = array();
	$tab_user          = array();
	$groupe_id = 0;
	$nb_professeurs = 0;
	// Récupération de la liste des professeurs par classes
	$DB_SQL = 'SELECT * FROM livret_groupe ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'LEFT JOIN livret_jointure_user_groupe USING (livret_structure_id,livret_groupe_id) ';
	$DB_SQL.= 'LEFT JOIN livret_user USING (livret_structure_id,livret_user_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_groupe_type=:type AND livret_user_statut=:statut ';
	$DB_SQL.= 'ORDER BY livret_niveau_ordre ASC, livret_groupe_ref ASC, livret_user_nom ASC, livret_user_prenom ASC';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':type'=>'classe',':statut'=>1);
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	if(count($DB_TAB))
	{
		foreach($DB_TAB as $DB_ROW)
		{
			if($groupe_id != $DB_ROW['livret_groupe_id'])
			{
				// Nouvelle classe
				$tab_niveau_groupe[$DB_ROW['livret_niveau_id']][$DB_ROW['livret_groupe_id']] = html($DB_ROW['livret_groupe_nom']);
				$tab_user[$DB_ROW['livret_groupe_id']] = '';
				$groupe_id = $DB_ROW['livret_groupe_id'];
			}
			if(!is_null($DB_ROW['livret_user_id']))
			{
				// Nouveau professeur
				$checked = ($DB_ROW['livret_jointure_pp']) ? ' checked="checked"' : '' ;
				$id = $DB_ROW['livret_groupe_id'].'x'.$DB_ROW['livret_user_id'];
				$tab_user[$DB_ROW['livret_groupe_id']] .= '<input type="checkbox" id="id_'.$id.'" name="f_tab_id" value="'.$id.'"'.$checked.' /> <label for="id_'.$id.'">'.html($DB_ROW['livret_user_nom'].' '.$DB_ROW['livret_user_prenom']).'</label><br />';
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
		echo'<p>Aucune classe enregistrée !</p>';
	}
	?>

	<p>
		<input id="f_submit" type="button" value="Valider ce choix de professeurs principaux." /> <label id="ajax_msg">&nbsp;</label>
	</p>
</form>

