<?php
/**
 * @version $Id: referentiel_global.php 8 2009-10-30 20:56:02Z thomas $
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
$TITRE = "Créer / importer / partager / détruire un référentiel";
?>

<span class="manuel"><a class="pop_up" href="./aide.php?fichier=referentiel_organisation_competences">DOC : Organisation d'un référentiel de compétences.</a></span><br />
<span class="manuel"><a class="pop_up" href="./aide.php?fichier=referentiel_creer_importer_partager_detruire">DOC : Créer / importer / partager / détruire un référentiel.</a></span>
<div class="danger">Détruire un référentiel supprime les résultats associés de tous les élèves !</div>

<hr />

<form action="">

<?php
// J'ai séparé en plusieurs requêtes au bout de plusieurs heures sans m'en sortir (entre les matières sans coordonnateurs, sans référentiel, les deux à la fois...).
// La recherche ne s'effectue que sur les matières et niveaux utilisés, sans débusquer des référentiels résiduels.
$tab_matiere = array();
$tab_niveau  = array();
$tab_colonne = array();

// On récupère la liste des matières où le professeur est rattaché, et s'il en est coordonnateur
$DB_SQL = 'SELECT livret_matiere_id,livret_matiere_nom,livret_jointure_coord,livret_matiere_structure_id FROM livret_jointure_user_matiere ';
$DB_SQL.= 'LEFT JOIN livret_matiere USING (livret_matiere_id) ';
$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id=:user_id ';
$DB_SQL.= 'ORDER BY livret_matiere_nom ASC';
$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':user_id'=>$_SESSION['USER_ID']);
$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
if(count($DB_TAB))
{
	foreach($DB_TAB as $key => $DB_ROW)
	{
		$tab_matiere[$DB_ROW['livret_matiere_id']] = array( 'nom'=>html($DB_ROW['livret_matiere_nom']) , 'coord'=>$DB_ROW['livret_jointure_coord'] , 'perso'=>$DB_ROW['livret_matiere_structure_id'] );
	}
}
$liste_matieres = implode(',',array_keys($tab_matiere));

if(!$liste_matieres)
{
	echo'<p><span class="danger">Vous n\'êtes coordonnateur d\'aucune matière de cet établissement !</span></p>';
}
elseif(!$_SESSION['NIVEAUX'])
{
	echo'<p><span class="danger">Aucun niveau n\'est rattaché à l\'établissement !</span></p>';
}
else
{
	// On récupère la liste des niveaux utilisés par l'établissement
	$DB_SQL = 'SELECT livret_niveau_id,livret_niveau_nom FROM livret_niveau ';
	$DB_SQL.= 'WHERE livret_niveau_id IN('.$_SESSION['NIVEAUX'].') ';
	$DB_SQL.= ($_SESSION['PALIERS']) ? 'OR livret_palier_id IN('.$_SESSION['PALIERS'].') ' : '' ;
	$DB_SQL.= 'ORDER BY livret_niveau_ordre ASC';
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL);
	$nb_niveaux = count($DB_TAB);
	foreach($DB_TAB as $key => $DB_ROW)
	{
		$tab_niveau[$DB_ROW['livret_niveau_id']] = html($DB_ROW['livret_niveau_nom']);
	}
	// On récupère la liste des référentiels par matière et niveau
	$tab_partage = array('oui'=>'<img title="Référentiel accessible aux autres établissements." alt="" src="./_img/partage1.gif" />','non'=>'<img title="Référentiel caché aux autres établissements." alt="" src="./_img/partage0.gif" />','bof'=>'<img title="Référentiel dont le partage est sans intérêt (pas novateur)." alt="" src="./_img/partage0.gif" />','hs'=>'<img title="Référentiel dont le partage est sans objet (matière spécifique)." alt="" src="./_img/partage0.gif" />');
	$DB_SQL = 'SELECT livret_matiere_id,livret_niveau_id,livret_niveau_nom,livret_referentiel_partage FROM livret_referentiel ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_matiere_id IN('.$liste_matieres.') AND livret_niveau_id IN('.$_SESSION['NIVEAUX'].') ';
	$DB_SQL.= 'ORDER BY livret_matiere_id ASC, livret_niveau_ordre ASC';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID']);
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	if(count($DB_TAB))
	{
		foreach($DB_TAB as $key => $DB_ROW)
		{
			$tab_colonne[$DB_ROW['livret_matiere_id']][$DB_ROW['livret_niveau_id']] = '<td lang="'.$DB_ROW['livret_referentiel_partage'].'" class="v">Référentiel présent. '.$tab_partage[$DB_ROW['livret_referentiel_partage']].'</td>';
		}
	}
	// On construit et affiche le tableau résultant
	$affichage = '<table class="comp_view"><thead><tr><th>Matière</th><th>Niveau</th><th>Référentiel</th><th class="nu"></th></tr></thead><tbody>'."\r\n";
	foreach($tab_matiere as $matiere_id => $tab)
	{
		$rowspan = ($matiere_id!=ID_MATIERE_TRANSVERSALE) ? $nb_niveaux : mb_substr_count($_SESSION['PALIERS'],',','UTF-8')+1 ;
		$matiere_nom   = $tab['nom'];
		$matiere_coord = $tab['coord'];
		$matiere_perso = ($tab['perso']) ? 1 : 0 ;
		$affichage .= '<tr><td colspan="4" class="nu">&nbsp;</td></tr>'."\r\n";
		$affichage .= '<tr lang="'.$matiere_nom.'"><td rowspan="'.$rowspan.'">'.$matiere_nom.'</td>';
		$affichage_suite = false;
		foreach($tab_niveau as $niveau_id => $niveau_nom)
		{
			if( ($matiere_id!=ID_MATIERE_TRANSVERSALE) || (in_array($niveau_id,$GLOBALS['TAB_ID_NIVEAUX_PALIERS'])) )
			{
				$ids = 'ids_'.$matiere_perso.'_'.$matiere_id.'_'.$niveau_id;
				if($matiere_coord)
				{
					$proposition = ($matiere_perso) ? '' : ' ou importer un référentiel existant' ;
					$partager = ($matiere_perso) ? '<q class="partager_non" title="Le référentiel d\'une matière spécifique à l\'établissement ne peut être partagé."></q>' : '<q class="partager" title="Modifier le partage de ce référentiel."></q>' ;
					$colonnes = (isset($tab_colonne[$matiere_id][$niveau_id])) ? $tab_colonne[$matiere_id][$niveau_id].'<td class="nu" id="'.$ids.'"><q class="voir" title="Voir le détail de ce référentiel."></q>'.$partager.'<q class="supprimer" title="Supprimer ce référentiel."></q></td>' : '<td class="r">Absence de référentiel.</td><td class="nu" id="'.$ids.'"><q class="ajouter" title="Créer un référentiel vierge'.$proposition.'."></q></td>' ;
				}
				else
				{
					$colonnes = (isset($tab_colonne[$matiere_id][$niveau_id])) ? $tab_colonne[$matiere_id][$niveau_id].'<td class="nu" id="'.$ids.'"><q class="voir" title="Voir le détail de ce référentiel."></q><q class="partager_non" title="Action réservée aux coordonnateurs."></q><q class="supprimer_non" title="Action réservée aux coordonnateurs."></q></td>' : '<td class="r">Absence de référentiel.</td><td class="nu" id="'.$ids.'"><q class="ajouter_non" title="Action réservée aux coordonnateurs."></q></td>' ;
				}
				if($affichage_suite===false)
				{
					$affichage .= '<td>'.$niveau_nom.'</td>'.$colonnes;
					$affichage_suite = '';
				}
				else
				{
					$affichage_suite .= '<tr lang="'.$matiere_nom.'"><td>'.$niveau_nom.'</td>'.$colonnes.'</tr>'."\r\n";
				}
			}
		}
		$affichage .= '</tr>'."\r\n".$affichage_suite;
	}
	$affichage .= '</tbody></table>'."\r\n";
	echo $affichage;
}
?>

<?php
// Fabrication des éléments select du formulaire, pour pouvoir prendre un référentiel d'une autre matière ou d'un autre niveau (demandé...).
$select_matiere = afficher_select(DB_OPT_matieres_communes($_SESSION['MATIERES']) , $select_nom='f_matiere' , $option_first='oui' , $selection=false , $optgroup='non');
$select_niveau  = afficher_select(DB_OPT_niveaux_etabl($_SESSION['NIVEAUX'])      , $select_nom='f_niveau'  , $option_first='oui' , $selection=false , $optgroup='non');
?>

<hr />

<div id="choisir_referentiel" class="hide">
	<h2>Liste des référentiels disponibles</h2>
	<?php echo $select_matiere ?> <?php echo $select_niveau ?> <input id="f_submit_lister" type="button" value="Actualiser." /><label id="ajax_msg_actualiser">&nbsp;</label><p />
	<a class="Valider_choisir" href="#"><img alt="Valider" src="./_img/action/action_valider.png" /> Valider le choix du référentiel coché.</a><br />
	<a class="Annuler_choisir" href="#"><img alt="Annuler" src="./_img/action/action_annuler.png" /> Annuler le choix d'un référentiel.</a><br />
	<label id="ajax_msg_choisir">&nbsp;</label>
	<p />
	<ul class="donneur link">
		<li></li>
	</ul>
	<p />
	<hr />
</div>

<div id="voir_referentiel">
</div>

</form>


