<?php
/**
 * @version $Id: eval_demande_oui.php 8 2009-10-30 20:56:02Z thomas $
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
?>

<ul class="puce">
	<li><span class="manuel"><a class="pop_up" href="./aide.php?fichier=demandes_evaluations">DOC : Demandes d'évaluations.</a></span></li>
	<li><span class="astuce">Vous avez la possibilité de formuler au maximum <?php echo $_SESSION['ELEVE_DEMANDES'] ?> demande<?php echo ($_SESSION['ELEVE_DEMANDES']>1) ? 's' : '' ; ?> par matière.</span></li>
</ul>

<hr />

<form action="">
	<table class="form">
		<thead>
			<tr>
				<th>Date</th>
				<th>Matière</th>
				<th>Item</th>
				<th>Score</th>
				<th>Statut</th>
				<th class="nu"></th>
			</tr>
		</thead>
		<tbody>
			<?php
			// Lister les demandes d'évaluation
			$DB_SQL = 'SELECT livret_demande.*, ';
			$DB_SQL.= 'CONCAT(livret_niveau_ref,".",livret_domaine_ref,livret_theme_ordre,livret_competence_ordre) AS competence_ref , ';
			$DB_SQL.= 'livret_competence_nom , livret_matiere_nom ';
			$DB_SQL.= 'FROM livret_demande ';
			$DB_SQL.= 'LEFT JOIN livret_competence_item USING (livret_structure_id,livret_competence_id) ';
			$DB_SQL.= 'LEFT JOIN livret_competence_theme USING (livret_structure_id,livret_theme_id) ';
			$DB_SQL.= 'LEFT JOIN livret_competence_domaine USING (livret_structure_id,livret_domaine_id) ';
			$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
			$DB_SQL.= 'LEFT JOIN livret_matiere ON livret_competence_domaine.livret_matiere_id=livret_matiere.livret_matiere_id ';
			$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id=:user_id ';
			$DB_SQL.= 'ORDER BY livret_demande.livret_matiere_id ASC, livret_niveau_ref ASC, livret_domaine_ref ASC, livret_theme_ordre ASC, livret_competence_ordre ASC';
			$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':user_id'=>$_SESSION['USER_ID']);
			$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
			foreach($DB_TAB as $DB_ROW)
			{
				$score  = ($DB_ROW['livret_demande_score']!==null) ? $DB_ROW['livret_demande_score'] : false ;
				$statut = ($DB_ROW['livret_demande_statut']=='eleve') ? 'demande non traitée' : 'évaluation en préparation' ;
				// Afficher une ligne du tableau 
				echo'<tr id="id_'.$DB_ROW['livret_demande_id'].'">';
				echo	'<td><i>'.html($DB_ROW['livret_demande_date']).'</i>'.convert_date_mysql_to_french($DB_ROW['livret_demande_date']).'</td>';
				echo	'<td>'.html($DB_ROW['livret_matiere_nom']).'</td>';
				echo	'<td>'.html($DB_ROW['competence_ref']).' <img alt="" src="./_img/bulle_aide.png" title="'.html($DB_ROW['livret_competence_nom']).'" /></td>';
				echo	affich_score_html($score,'score',$pourcent='');
				echo	'<td>'.$statut.'</td>';
				echo	'<td class="nu"><q class="demander_del" title="Supprimer cette demande d\'évaluation."></q></td>';
				echo'</tr>';
			}
			?>
		</tbody>
	</table>
</form>
