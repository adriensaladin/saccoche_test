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
$TITRE = "Évaluer une classe ou un groupe";
?>

<?php
// Élément de formulaire "f_aff_classe" pour le choix des élèves (liste des classes / groupes / besoins) du professeur, enregistré dans une variable javascript pour utilisation suivant le besoin, et utilisé pour un tri initial
// Fabrication de tableaux javascript "tab_niveau" et "tab_groupe" indiquant le niveau et le nom d'un groupe
$select_eleve  = '';
$tab_niveau_js = 'var tab_niveau = new Array();';
$tab_groupe_js = 'var tab_groupe = new Array();';
$tab_id_classe_groupe = array();
$DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_groupes_professeur($_SESSION['USER_ID']);
$tab_options = array('classe'=>'','groupe'=>'','besoin'=>'');
foreach($DB_TAB as $DB_ROW)
{
	$groupe = strtoupper($DB_ROW['groupe_type']{0}).$DB_ROW['groupe_id'];
	$tab_options[$DB_ROW['groupe_type']] .= '<option value="'.$groupe.'">'.html($DB_ROW['groupe_nom']).'</option>';
	$tab_niveau_js .= 'tab_niveau["'.$groupe.'"]="'.sprintf("%02u",$DB_ROW['niveau_ordre']).'";';
	$tab_groupe_js .= 'tab_groupe["'.$groupe.'"]="'.html($DB_ROW['groupe_nom']).'";';
	if($DB_ROW['groupe_type']!='besoin')
	{
		$tab_id_classe_groupe[] = $DB_ROW['groupe_id'];
	}
}
foreach($tab_options as $type => $contenu)
{
	if($contenu)
	{
		$select_eleve .= '<optgroup label="'.ucwords($type).'s">'.$contenu.'</optgroup>';
	}
}

// Élément de formulaire "f_aff_periode" pour le choix d'une période
$select_periode = Formulaire::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_periodes_etabl() , $select_nom='f_aff_periode' , $option_first='val' , $selection=false , $optgroup='non');
// On désactive les périodes prédéfinies pour le choix "toute classe / tout groupe" initialement sélectionné
$select_periode = preg_replace( '#'.'value="([1-9].*?)"'.'#' , 'value="$1" disabled' , $select_periode );
// Dates par défaut de début et de fin
$date_debut  = date("d/m/Y",mktime(0,0,0,date("m")-2,date("d"),date("Y"))); // 2 mois avant
$date_fin    = date("d/m/Y",mktime(0,0,0,date("m")+1,date("d"),date("Y"))); // 1 mois après

// Fabrication du tableau javascript "tab_groupe_periode" pour les jointures groupes/périodes
$tab_groupe_periode_js = 'var tab_groupe_periode = new Array();';
if(count($tab_id_classe_groupe))
{
	$tab_memo_groupes = array();
	$DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_jointure_groupe_periode($listing_groupe_id = implode(',',$tab_id_classe_groupe));
	foreach($DB_TAB as $DB_ROW)
	{
		if(!isset($tab_memo_groupes[$DB_ROW['groupe_id']]))
		{
			$tab_memo_groupes[$DB_ROW['groupe_id']] = true;
			$tab_groupe_periode_js .= 'tab_groupe_periode['.$DB_ROW['groupe_id'].'] = new Array();';
		}
		$tab_groupe_periode_js .= 'tab_groupe_periode['.$DB_ROW['groupe_id'].']['.$DB_ROW['periode_id'].']="'.$DB_ROW['jointure_date_debut'].'_'.$DB_ROW['jointure_date_fin'].'";';
	}
}
?>

<script type="text/javascript">
	// <![CDATA[
	var select_groupe="<?php echo str_replace('"','\"','<option value=""></option>'.$select_eleve); ?>";
	// ]]>
	var input_date="<?php echo date("d/m/Y") ?>";
	var date_mysql="<?php echo date("Y-m-d") ?>";
	<?php echo $tab_niveau_js ?> 
	<?php echo $tab_groupe_js ?> 
	<?php echo $tab_groupe_periode_js ?> 
</script>

<ul class="puce">
	<li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_professeur__evaluations_gestion">DOC : Gestion des évaluations.</a></span></li>
</ul>

<hr />

<form action="#" method="post" id="form0" class="hide"><fieldset>
	<label class="tab" for="f_aff_classe">Classe / groupe :</label><select id="f_aff_classe" name="f_aff_classe"><option value="d2">Toute classe / tout groupe</option><?php echo $select_eleve ?></select>
	<div id="zone_periodes">
		<label class="tab" for="f_aff_periode">Période :</label><?php echo $select_periode ?>
		<span id="dates_perso" class="show">
			du <input id="f_date_debut" name="f_date_debut" size="9" type="text" value="<?php echo $date_debut ?>" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q>
			au <input id="f_date_fin" name="f_date_fin" size="9" type="text" value="<?php echo $date_fin ?>" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q>
		</span><br />
		<span class="tab"></span><input type="hidden" name="f_action" value="Afficher_evaluations" /><button id="actualiser" type="submit" class="actualiser">Actualiser l'affichage.</button><label id="ajax_msg0">&nbsp;</label>
	</div>
</fieldset></form>

<form action="#" method="post" id="form1" class="hide">
	<hr />
	<table class="form">
		<thead>
			<tr>
				<th>Date devoir</th>
				<th>Date visible</th>
				<th>Classe / Groupe</th>
				<th>Description</th>
				<th>Items</th>
				<th>Profs</th>
				<th class="nu"><q class="ajouter" title="Ajouter une évaluation."></q></th>
			</tr>
		</thead>
		<tbody>
			<tr><td class="nu" colspan="7"></td></tr>
		</tbody>
	</table>
</form>

<form action="#" method="post" id="zone_compet" class="arbre_dynamique arbre_check hide">
	<p>Cocher ci-dessous (<span class="astuce">cliquer sur un intitulé pour déployer son contenu</span>) :</p>
	<?php
	// Affichage de la liste des items pour toutes les matières d'un professeur, sur tous les niveaux
	$DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_arborescence($_SESSION['USER_ID'],$matiere_id=0,$niveau_id=0,$only_socle=false,$only_item=false,$socle_nom=false);
	echo afficher_arborescence_matiere_from_SQL($DB_TAB,$dynamique=true,$reference=true,$aff_coef=false,$aff_cart=false,$aff_socle='texte',$aff_lien=false,$aff_input=true);
	?>
	<p class="danger">Une évaluation dont la saisie a commencé ne devrait pas voir ses items modifiés.<br />En particulier, retirer des items d'une évaluation efface les scores correspondants déjà saisis !</p>
	<div><span class="tab"></span><button id="valider_compet" type="button" class="valider">Valider la sélection</button>&nbsp;&nbsp;&nbsp;<button id="annuler_compet" type="button" class="annuler">Annuler / Retour</button></div>
</form>

<form action="#" method="post" id="zone_profs" class="hide">
	<div class="astuce">Vous pouvez permettre à des collègues de co-saisir les notes de ce devoir (et de le dupliquer).</div>
	<?php
	// Affichage de la liste des professeurs
	$DB_TAB = DB_STRUCTURE_COMMUN::DB_OPT_professeurs_etabl();
	if(is_string($DB_TAB))
	{
		echo $DB_TAB;
	}
	else
	{
		$nb_profs              = count($DB_TAB);
		$nb_profs_maxi_par_col = 20;
		$nb_cols               = floor(($nb_profs-1)/$nb_profs_maxi_par_col)+1;
		$nb_profs_par_col      = ceil($nb_profs/$nb_cols);
		$tab_div = array_fill(0,$nb_cols,'');
		foreach($DB_TAB as $i => $DB_ROW)
		{
			$checked_and_disabled = ($DB_ROW['valeur']==$_SESSION['USER_ID']) ? ' checked disabled' : '' ; // readonly ne fonctionne pas sur un checkbox
			$tab_div[floor($i/$nb_profs_par_col)] .= '<input type="checkbox" name="f_profs[]" id="p_'.$DB_ROW['valeur'].'" value="'.$DB_ROW['valeur'].'"'.$checked_and_disabled.' /><label for="p_'.$DB_ROW['valeur'].'"> '.html($DB_ROW['texte']).'</label><br />';
		}
		echo'<p><a href="#prof_liste" id="prof_check_all"><img src="./_img/all_check.gif" alt="Tout cocher." /> Tout le monde</a>&nbsp;&nbsp;&nbsp;<a href="#prof_liste" id="prof_uncheck_all"><img src="./_img/all_uncheck.gif" alt="Tout décocher." /> Seulement moi</a></p>';
		echo '<div class="prof_liste">'.implode('</div><div class="prof_liste">',$tab_div).'</div>';
	}
	?>
	<div style="clear:both"><button id="valider_profs" type="button" class="valider">Valider la sélection</button>&nbsp;&nbsp;&nbsp;<button id="annuler_profs" type="button" class="annuler">Annuler / Retour</button></div>
</form>

<form action="#" method="post" id="zone_ordonner" class="hide">
	<p class="hc"><b id="titre_ordonner"></b><br /><label id="msg_ordonner"></label></p>
	<div id="div_ordonner">
	</div>
</form>

<!-- Sans "javascript:return false" une soumission incontrôlée s'effectue quand on presse "entrée" dans le cas d'un seul élève évalué sur un seul item. -->
<form action="javascript:return false" method="post" id="zone_saisir" class="hide">
	<p class="hc"><b id="titre_saisir"></b><br /><label id="msg_saisir"></label></p>
	<table id="table_saisir" class="scor_eval">
		<tbody><tr><td></td></tr></tbody>
	</table>
	<div id="td_souris_container"><div class="td_souris">
		<img alt="RR" src="./_img/note/<?php echo $_SESSION['NOTE_DOSSIER'] ?>/h/RR.gif" /><img alt="ABS" src="./_img/note/commun/h/ABS.gif" /><br />
		<img alt="R" src="./_img/note/<?php echo $_SESSION['NOTE_DOSSIER'] ?>/h/R.gif" /><img alt="NN" src="./_img/note/commun/h/NN.gif" /><br />
		<img alt="V" src="./_img/note/<?php echo $_SESSION['NOTE_DOSSIER'] ?>/h/V.gif" /><img alt="DISP" src="./_img/note/commun/h/DISP.gif" /><br />
		<img alt="VV" src="./_img/note/<?php echo $_SESSION['NOTE_DOSSIER'] ?>/h/VV.gif" /><img alt="X" src="./_img/note/commun/h/X.gif" />
	</div></div>
	<p class="ti" id="aide_en_ligne"><button id="report_note" type="button" class="eclair">Reporter</button> le code
		<label for="f_defaut_VV"><input type="radio" id="f_defaut_VV" name="f_defaut" value="VV" checked /><img alt="VV" src="./_img/note/<?php echo $_SESSION['NOTE_DOSSIER'] ?>/h/VV.gif" /></label> |
		<label for="f_defaut_V"><input type="radio" id="f_defaut_V" name="f_defaut" value="V" /><img alt="V" src="./_img/note/<?php echo $_SESSION['NOTE_DOSSIER'] ?>/h/V.gif" /></label> |
		<label for="f_defaut_R"><input type="radio" id="f_defaut_R" name="f_defaut" value="R" /><img alt="R" src="./_img/note/<?php echo $_SESSION['NOTE_DOSSIER'] ?>/h/R.gif" /></label> |
		<label for="f_defaut_RR"><input type="radio" id="f_defaut_RR" name="f_defaut" value="RR" /><img alt="RR" src="./_img/note/<?php echo $_SESSION['NOTE_DOSSIER'] ?>/h/RR.gif" /></label> |
		<label for="f_defaut_ABS"><input type="radio" id="f_defaut_ABS" name="f_defaut" value="ABS" /><img alt="ABS" src="./_img/note/commun/h/ABS.gif" /></label> |
		<label for="f_defaut_NN"><input type="radio" id="f_defaut_NN" name="f_defaut" value="NN" /><img alt="NN" src="./_img/note/commun/h/NN.gif" /></label> |
		<label for="f_defaut_DISP"><input type="radio" id="f_defaut_DISP" name="f_defaut" value="DISP" /><img alt="DISP" src="./_img/note/commun/h/DISP.gif" /></label> dans toutes les cellules vides.<label id="msg_report">&nbsp;</label>
	</p>
	<div>
		<a lang="zone_saisir_deport" href="#"><img src="./_img/toggle_plus.gif" alt="" title="Voir / masquer la saisie déportée." class="toggle" /></a> Saisie déportée
		<div id="zone_saisir_deport" class="hide">
			<input type="hidden" name="filename" id="filename" value="<?php echo './__tmp/export/saisie_'.$_SESSION['BASE'].'_'.$_SESSION['USER_ID'].'_'; ?>" />
			<ul class="puce">
				<li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_professeur__evaluations_saisie_deportee">DOC : Saisie déportée.</a></span></li>
				<li><a id="export_file1" class="lien_ext" href=""><span class="file file_txt">Récupérer un fichier vierge pour une saisie déportée (format <em>csv</em>).</span></a></li>
				<li><a id="export_file4" class="lien_ext" href=""><span class="file file_pdf">Imprimer un tableau vierge utilisable pour un report manuel des notes (format <em>pdf</em>).</span></a></li>
				<li><button id="import_file" type="button" class="fichier_import">Envoyer un fichier de notes complété (format <em>csv</em>).</button><label id="msg_import">&nbsp;</label></li>
			</ul>
		</div>
	</div>
</form>

<div id="zone_voir" class="hide">
	<p class="hc"><b id="titre_voir"></b><br /><label id="msg_voir"></label></p>
	<table id="table_voir" class="scor_eval">
		<tbody><tr><td></td></tr></tbody>
	</table>
	<p>
		<a lang="zone_voir_deport" href="#"><img src="./_img/toggle_plus.gif" alt="" title="Voir / masquer la saisie déportée." class="toggle" /></a> Saisie déportée &amp; archivage
		<div id="zone_voir_deport" class="hide">
			<ul class="puce">
				<li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_professeur__evaluations_saisie_deportee">DOC : Saisie déportée.</a></span></li>
				<li><a id="export_file2" class="lien_ext" href=""><span class="file file_txt">Récupérer un fichier des scores pour une saisie déportée (format <em>csv</em>).</span></a></li>
				<li><a id="export_file3" class="lien_ext" href=""><span class="file file_pdf">Imprimer un tableau vierge utilisable pour un report manuel des notes (format <em>pdf</em>).</span></a></li>
				<li><a id="export_file5" class="lien_ext" href=""><span class="file file_pdf">Archiver / Imprimer le tableau avec les scores (format <em>pdf</em>).</span></a></li>
			</ul>
		</div>
	</p>
</div>

<div id="zone_voir_repart" class="hide">
	<p class="hc"><b id="titre_voir_repart"></b><br /><label id="msg_voir_repart"></label></p>
	<table id="table_voir_repart1" class="scor_eval">
		<tbody><tr><td></td></tr></tbody>
	</table>
	<p>
	<ul class="puce">
		<li><a id="export_file6" class="lien_ext" href=""><span class="file file_pdf">Archiver / Imprimer le tableau avec la répartition quantitative des scores (format <em>pdf</em>).</span></a></li>
	</ul>
	</p>
	<p>
	<table id="table_voir_repart2" class="scor_eval">
		<tbody><tr><td></td></tr></tbody>
	</table>
	</p>
	<p>
	<ul class="puce">
		<li><a id="export_file7" class="lien_ext" href=""><span class="file file_pdf">Archiver / Imprimer le tableau avec la répartition nominative des scores (format <em>pdf</em>).</span></a></li>
	</ul>
	</p>
</div>

<?php
// Fabrication des éléments select du formulaire
Formulaire::load_choix_memo();
$select_cart_contenu = Formulaire::afficher_select(Formulaire::$tab_select_cart_contenu , $select_nom='f_contenu'     , $option_first='non' , $selection=Formulaire::$tab_choix['cart_contenu'] , $optgroup='non');
$select_cart_detail  = Formulaire::afficher_select(Formulaire::$tab_select_cart_detail  , $select_nom='f_detail'      , $option_first='non' , $selection=Formulaire::$tab_choix['cart_detail']  , $optgroup='non');
$select_orientation  = Formulaire::afficher_select(Formulaire::$tab_select_orientation  , $select_nom='f_orientation' , $option_first='non' , $selection=Formulaire::$tab_choix['orientation']  , $optgroup='non');
$select_couleur      = Formulaire::afficher_select(Formulaire::$tab_select_couleur      , $select_nom='f_couleur'     , $option_first='non' , $selection=Formulaire::$tab_choix['couleur']      , $optgroup='non');
$select_marge_min    = Formulaire::afficher_select(Formulaire::$tab_select_marge_min    , $select_nom='f_marge_min'   , $option_first='non' , $selection=Formulaire::$tab_choix['marge_min']    , $optgroup='non');
?>

<form action="#" method="post" id="zone_imprimer" class="hide"><fieldset>
	<p class="hc"><b id="titre_imprimer"></b><br /><button id="fermer_zone_imprimer" type="button" class="retourner">Retour</button></p>
	<label class="tab" for="f_contenu">Remplissage :</label><?php echo $select_cart_contenu ?><br />
	<label class="tab" for="f_detail">Détail :</label><?php echo $select_cart_detail ?><br />
	<div class="toggle">
		<span class="tab"></span><a href="#" class="puce_plus toggle">Afficher plus d'options</a>
	</div>
	<div class="toggle hide">
		<span class="tab"></span><a href="#" class="puce_moins toggle">Afficher moins d'options</a><br />
		<label class="tab">Orientation :</label><?php echo $select_orientation ?> <?php echo $select_couleur ?> <?php echo $select_marge_min ?><br />
		<label class="tab">Restriction :</label><input type="checkbox" id="f_restriction_req" name="f_restriction_req" value="1" /> <label for="f_restriction_req">Uniquement les items ayant fait l'objet d'une demande d'évaluation (ou dont une note est saisie).</label>
	</div>
	<span class="tab"></span><button id="f_submit_imprimer" type="button" value="'.$ref.'" class="valider">Générer le cartouche</button><label id="msg_imprimer">&nbsp;</label>
	<p id="zone_imprimer_retour"></p>
</fieldset></form>
