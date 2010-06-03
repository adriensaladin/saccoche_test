<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 * 
 * ****************************************************************************************************
 * SACoche <http://competences.sesamath.net> - Suivi d'Acquisitions de Compétences
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
$TITRE = "Évaluer une classe ou un groupe";
$VERSION_JS = '2';
?>

<?php
// Élément de formulaire "f_aff_classe" pour le choix des élèves (liste des classes / groupes / besoins) du professeur, enregistré dans une variable javascript pour utilisation suivant le besoin, et utilisé pour un tri initial
// Fabrication de tableaux javascript "tab_niveau" et "tab_groupes" indiquant le niveau et le nom d'un groupe
$select_eleve  = '<option value=""></option>';
$tab_niveau_js = 'var tab_niveau = new Array();';
$tab_groupe_js = 'var tab_groupes = new Array();';
$DB_SQL = 'SELECT * FROM sacoche_groupe ';
$DB_SQL.= 'LEFT JOIN sacoche_jointure_user_groupe USING (groupe_id) ';
$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
$DB_SQL.= 'WHERE ( user_id=:user_id OR groupe_prof_id=:user_id ) AND groupe_type!=:type4 ';
$DB_SQL.= 'GROUP BY groupe_id ';
$DB_SQL.= 'ORDER BY niveau_ordre ASC, groupe_nom ASC';
$DB_VAR = array(':user_id'=>$_SESSION['USER_ID'],':type4'=>'eval');
$DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
$tab_options = array('classe'=>'','groupe'=>'','besoin'=>'');
foreach($DB_TAB as $DB_ROW)
{
	$groupe = strtoupper($DB_ROW['groupe_type']{0}).$DB_ROW['groupe_id'];
	$tab_options[$DB_ROW['groupe_type']] .= '<option value="'.$groupe.'">'.html($DB_ROW['groupe_nom']).'</option>';
	$tab_niveau_js .= 'tab_niveau["'.$groupe.'"]="'.sprintf("%02u",$DB_ROW['niveau_ordre']).'";';
	$tab_groupe_js .= 'tab_groupes["'.$groupe.'"]="'.html($DB_ROW['groupe_nom']).'";';
}
foreach($tab_options as $type => $contenu)
{
	if($contenu)
	{
		$select_eleve .= '<optgroup label="'.ucwords($type).'s">'.$contenu.'</optgroup>';
	}
}
// Élément de formulaire "f_aff_periode" pour le choix d'une période
$select_periode = afficher_select(DB_OPT_periodes_etabl() , $select_nom='f_aff_periode' , $option_first='val' , $selection=false , $optgroup='non');
// Dates par défaut de début et de fin
$annee_debut = (date('n')>8) ? date('Y') : date('Y')-1 ;
$date_debut  = '01/09/'.$annee_debut;
$date_fin    = date("d/m/Y");
?>

<script type="text/javascript">
	// <![CDATA[
	var select_groupe="<?php echo str_replace('"','\"',$select_eleve); ?>";
	// ]]>
	var input_date="<?php echo date("d/m/Y") ?>";
	<?php echo $tab_niveau_js ?>
	<?php echo $tab_groupe_js ?>
</script>

<ul class="puce">
	<li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_professeur__evaluations_gestion">DOC : Gestion des évaluations.</a></span></li>
	<li><span class="danger">Une évaluation dont la saisie a commencé ne devrait pas voir ses élèves ou ses items modifiés (sinon vous n'aurez plus accès à certaines données) !</span></li>
</ul>

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
		<a class="valider_compet" href="#"><img alt="Valider" src="./_img/action_valider.png" /> Valider ce choix</a><br />
		<a class="annuler_compet" href="#"><img alt="Annuler" src="./_img/action_annuler.png" /> Annuler / Retour</a>
	</div>
	<?php
	// Affichage de la liste des items pour toutes les matières d'un professeur, sur tous les niveaux
	$DB_TAB = DB_recuperer_arborescence($_SESSION['USER_ID'],$matiere_id=0,$niveau_id=0,$only_item=false,$socle_nom=false);
	echo afficher_arborescence_from_SQL($DB_TAB,$dynamique=true,$reference=true,$aff_coef=false,$aff_socle='texte',$aff_lien=false,$aff_input=true);
	?>
</form>

<form action="" id="zone_ordonner" class="hide">
	<p class="hc"><b id="titre_ordonner"></b><br /><label id="msg_ordonner"></label></p>
	<div id="div_ordonner">
	</div>
</form>

<!-- Sans "javascript:return false" une soumission incontrôlée s'effectue quand on presse "entrée" dans le cas d'un seul élève évalué sur un seul item. -->
<form action="javascript:return false" id="zone_saisir" class="hide">
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
	<p />
	<div>
		<a lang="zone_deport" href="#td_souris_container"><img src="./_img/toggle_plus.gif" alt="" title="Voir / masquer la saisie déportée." class="toggle" /></a> Saisie déportée
		<div id="zone_deport" class="hide">
			<input type="hidden" name="filename" id="filename" value="<?php echo './__tmp/export/saisie_'.$_SESSION['BASE'].'_'.$_SESSION['USER_ID'].'_'; ?>" />
			<span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_professeur__evaluations_saisie_deportee">DOC : Saisie déportée.</a></span>
			<ul class="puce">
				<li><a id="export_file" class="lien_ext" href="">Récupérer un fichier vierge au format CSV pour une saisie déportée.</a></li>
				<li><input id="import_file" type="button" value="Envoyer un fichier complété au format CSV." /><label id="msg_import">&nbsp;</label></li>
			</ul>
		</div>
	</div>
</form>

<div id="zone_voir" class="hide">
	<p class="hc"><b id="titre_voir"></b><br /><label id="msg_voir"></label></p>
	<table id="table_voir" class="scor_eval">
		<tbody><tr><td></td></tr></tbody>
	</table>
	<p />
	<ul class="puce">
		<li><a id="export_file2" class="lien_ext" href="">Récupérer un fichier des scores au format CSV pour archivage ou une saisie déportée.</a></li>
	</ul>
</div>

<?php
// Fabrication des éléments select du formulaire
$tab_cookie = load_cookie_select($_SESSION['BASE'],$_SESSION['USER_ID']);
$select_orientation = afficher_select($tab_select_orientation , $select_nom='f_orientation' , $option_first='non' , $selection=$tab_cookie['orientation'] , $optgroup='non');
$select_marge_min   = afficher_select($tab_select_marge_min   , $select_nom='f_marge_min'   , $option_first='non' , $selection=$tab_cookie['marge_min']   , $optgroup='non');
$select_couleur     = afficher_select($tab_select_couleur     , $select_nom='f_couleur'     , $option_first='non' , $selection=$tab_cookie['couleur']     , $optgroup='non');
?>

<form action="" id="zone_imprimer" class="hide"><fieldset>
	<p class="hc"><b id="titre_imprimer"></b><br /><a class="fermer_zone_imprimer" href="#"><img alt="Retourner" src="./_img/action_retourner.png" /> Retour</a></p>
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
