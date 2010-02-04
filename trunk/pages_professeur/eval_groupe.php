<?php
/**
 * @version $Id: eval_groupe.php 8 2009-10-30 20:56:02Z thomas $
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
$TITRE = "Évaluer une classe ou un groupe";
?>

<?php
// Élément de formulaire "f_aff_classe" pour le choix des élèves (liste des classes / groupes / besoins) du professeur, enregistré dans une variable javascript pour utilisation suivant le besoin, et utilisé pour un tri itnitial
// Fabrication de tableaux javascript "tab_niveau" et "tab_groupes" indiquant le niveau et le nom d'un groupe
$select_eleve  = '<option value=""></option>';
$tab_niveau_js = 'var tab_niveau = new Array();';
$tab_groupe_js = 'var tab_groupes = new Array();';
$DB_SQL = 'SELECT * FROM livret_groupe ';
$DB_SQL.= 'LEFT JOIN livret_jointure_user_groupe USING (livret_structure_id,livret_groupe_id) ';
$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND ( livret_user_id=:user_id OR livret_groupe_prof_id=:user_id ) AND livret_groupe_type!=:type4 ';
$DB_SQL.= 'GROUP BY livret_groupe_id ';
$DB_SQL.= 'ORDER BY livret_niveau_ordre ASC, livret_groupe_nom ASC';
$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':user_id'=>$_SESSION['USER_ID'],':type4'=>'eval');
$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
$tab_options = array('classe'=>'','groupe'=>'','besoin'=>'');
foreach($DB_TAB as $key => $DB_ROW)
{
	$groupe = strtoupper($DB_ROW['livret_groupe_type']{0}).$DB_ROW['livret_groupe_id'];
	$tab_options[$DB_ROW['livret_groupe_type']] .= '<option value="'.$groupe.'">'.html($DB_ROW['livret_groupe_nom']).'</option>';
	$tab_niveau_js .= 'tab_niveau["'.$groupe.'"]="'.sprintf("%02u",$DB_ROW['livret_niveau_ordre']).'";';
	$tab_groupe_js .= 'tab_groupes["'.$groupe.'"]="'.html($DB_ROW['livret_groupe_nom']).'";';
}
foreach($tab_options as $type => $contenu)
{
	if($contenu)
	{
		$select_eleve .= '<optgroup label="'.ucwords($type).'s">'.$contenu.'</optgroup>';
	}
}
// Élément de formulaire "f_aff_periode" pour le choix d'une période
$select_periode = afficher_select(DB_OPT_periodes_etabl($_SESSION['STRUCTURE_ID']) , $select_nom='f_aff_periode' , $option_first='val' , $selection=false , $optgroup='non');
// Dates par défaut de début et de fin
$annee_debut = (date('n')>8) ? date('Y') : date('Y')-1 ;
$date_debut = '01/09/'.$annee_debut;
$date_fin   = date("d/m/Y");
?>

<script type="text/javascript">
	// <![CDATA[
	var select_groupe="<?php echo str_replace('"','\"',$select_eleve); ?>";
	// ]]>
	var input_date="<?php echo date("d/m/Y") ?>";
	<?php echo $tab_niveau_js ?>
	<?php echo $tab_groupe_js ?>
</script>

<div class="manuel"><a class="pop_up" href="./aide.php?fichier=evaluations_gestion">DOC : Gestion des évaluations.</a></div>
<div class="danger">Une évaluation dont la saisie a commencé ne devrait pas voir ses élèves ou ses items modifiés (sinon vous n'aurez plus accès à certaines données) !</div>
<hr />

<form action="" id="form0"><fieldset>
	<label class="tab" for="f_aff_classe">Classe / groupe :</label><select id="f_aff_classe" name="f_aff_classe"><?php echo $select_eleve ?></select><br />
	<label class="tab" for="f_aff_periode">Période :</label><?php echo $select_periode ?>
	<span id="dates_perso" class="show">
		du <input id="f_date_debut" name="f_date_debut" size="9" type="text" value="<?php echo $date_debut ?>" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q>
		au <input id="f_date_fin" name="f_date_fin" size="9" type="text" value="<?php echo $date_fin ?>" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q>
	</span><br />
	<span class="tab"></span><input type="hidden" name="f_action" value="Afficher_evaluations" /><input type="submit" value="Actualiser l'affichage." /><label id="ajax_msg0">&nbsp;</label>
</fieldset></form>

<form action="" id="form1">
	<hr />
	<table class="form">
		<thead>
			<tr>
				<th>Date</th>
				<th>Classe / Groupe</th>
				<th>Description</th>
				<th>Items</th>
				<th class="nu"><q class="ajouter" title="Ajouter une évaluation."></q></th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
</form>

<form action="" id="zone_compet" class="hide">
	<div class="hc">
		<a class="valider_compet" href="#"><img alt="Valider" src="./_img/action/action_valider.png" /> Valider ce choix</a><br />
		<a class="annuler_compet" href="#"><img alt="Annuler" src="./_img/action/action_annuler.png" /> Annuler / Retour</a>
	</div>
	<?php
	// Affichage de la liste des items pour toutes les matières d'un professeur, sur tous les niveaux
	$DB_TAB = DB_select_arborescence($_SESSION['STRUCTURE_ID'],$_SESSION['USER_ID'],$matiere_id=0,$niveau_id=0,$socle_nom=false);
	echo afficher_arborescence($DB_TAB,$dynamique=true,$reference=true,$aff_coef=false,$aff_socle='texte',$aff_lien=false,$aff_input=true);
	?>
</form>

<form action="" id="zone_ordonner" class="hide">
	<p class="hc"><b id="titre_ordonner"></b><br /><label id="msg_ordonner"></label></p>
	<div id="div_ordonner">
	</div>
</form>

<form action="" id="zone_saisir" class="hide">
	<p class="hc"><b id="titre_saisir"></b><br /><label id="msg_saisir"></label></p>
	<table id="table_saisir" class="scor_eval">
		<tbody><tr><td></td></tr></tbody>
	</table>
	<div id="td_souris_container"><div class="td_souris">
		<img alt="RR" src="./_img/note/note_RR.gif" /><img alt="ABS" src="./_img/note/note_ABS.gif" /><br />
		<img alt="R" src="./_img/note/note_R.gif" /><img alt="NN" src="./_img/note/note_NN.gif" /><br />
		<img alt="V" src="./_img/note/note_V.gif" /><img alt="DISP" src="./_img/note/note_DISP.gif" /><br />
		<img alt="VV" src="./_img/note/note_VV.gif" /><img alt="X" src="./_img/note/note_X.gif" />
	</div></div>
	<p>
		<a lang="zone_deport" href="#td_souris_container"><img src="./_img/toggle_plus.gif" alt="" title="Voir / masquer la saisie déportée." class="toggle" /></a> Saisie déportée
		<div id="zone_deport" class="hide">
			<input type="hidden" name="filename" id="filename" value="<?php echo './__tmp/export/saisie_'.$_SESSION['STRUCTURE_ID'].'_'.$_SESSION['USER_ID'].'_'; ?>" />
			<span class="manuel"><a class="pop_up" href="./aide.php?fichier=evaluations_saisie_deportee">DOC : Saisie déportée.</a></span>
			<ul class="puce">
				<li><a id="export_file" class="lien_ext" href="">Récupérer un fichier vierge au format CSV pour une saisie déportée.</a></li>
				<li><input id="import_file" type="button" value="Envoyer un fichier complété au format CSV." /><label id="msg_import">&nbsp;</label></li>
			</ul>
		</div>
	</p>
</form>

<div id="zone_voir" class="hide">
	<p class="hc"><b id="titre_voir"></b><br /><label id="msg_voir"></label></p>
	<table id="table_voir" class="scor_eval">
		<tbody><tr><td></td></tr></tbody>
	</table>
	<p>
		<ul class="puce">
			<li><a id="export_file2" class="lien_ext" href="">Récupérer un fichier des scores au format CSV pour archivage ou une saisie déportée.</a></li>
		</ul>
	</p>
</div>

<?php
// Fabrication des éléments select du formulaire
$tab_cookie = load_cookie_select($_SESSION['STRUCTURE_ID'],$_SESSION['USER_ID']);
$select_orientation = afficher_select($tab_select_orientation , $select_nom='f_orientation' , $option_first='non' , $selection=$tab_cookie['orientation']   , $optgroup='non');
$select_marge_min   = afficher_select($tab_select_marge_min   , $select_nom='f_marge_min'   , $option_first='non' , $selection=$tab_cookie['marge_min']     , $optgroup='non');
$select_couleur     = afficher_select($tab_select_couleur     , $select_nom='f_couleur'     , $option_first='non' , $selection=$tab_cookie['couleur']       , $optgroup='non');
?>

<form action="" id="zone_imprimer" class="hide"><fieldset>
	<p class="hc"><b id="titre_imprimer"></b><br /><a class="fermer_zone_imprimer" href="#"><img alt="Retourner" src="./_img/action/action_retourner.png" /> Retour</a></p>
	<label class="tab" for="f_valeur">Remplissage :</label><select id="f_valeur" name="f_valeur"><option value="vide">cartouche vierge de tout résultat</option><option value="plein">cartouche avec les résultats des élèves (si saisis)</option></select><br />
	<label class="tab" for="f_detail">Détail :</label><select id="f_detail" name="f_detail"><option value="complet">cartouche avec la dénomination complète de chaque item</option><option value="minimal">cartouche minimal avec uniquement les références des items</option></select><br />
	<div class="toggle">
		<span class="tab"></span><a href="#" class="puce_plus toggle">Afficher plus d'options</a>
	</div>
	<div class="toggle hide">
		<span class="tab"></span><a href="#" class="puce_moins toggle">Afficher moins d'options</a><br />
		<label class="tab" for="f_orientation">Orientation :</label><?php echo $select_orientation ?> en <?php echo $select_couleur ?> avec marges minimales de <?php echo $select_marge_min ?><br />
	</div>
	<span class="tab"></span><input id="f_submit_imprimer" type="button" value="Valider." /><label id="msg_imprimer">&nbsp;</label>
	<p id="zone_imprimer_retour"></p>
</fieldset></form>
