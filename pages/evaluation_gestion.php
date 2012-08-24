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

// Réception d'un formulaire depuis un tableau de synthèse bilan
// Dans ce cas il s'agit d'une évaluation sur une sélection d'élèves.
$tab_items = ( isset($_POST['id_item']) && is_array($_POST['id_item']) ) ? $_POST['id_item'] : array() ;
$tab_items = array_map('clean_entier',$tab_items);
$tab_items = array_filter($tab_items,'positif');
$nb_items  = count($tab_items);
$txt_items = ($nb_items) ? ( ($nb_items>1) ? $nb_items.' items' : $nb_items.' item' ) : 'aucun' ;
$tab_users = ( isset($_POST['id_user']) && is_array($_POST['id_user']) ) ? $_POST['id_user'] : array() ;
$tab_users = array_map('clean_entier',$tab_users);
$tab_users = array_filter($tab_users,'positif');
$nb_users  = count($tab_users);
$txt_users = ($nb_users) ? ( ($nb_users>1) ? $nb_users.' élèves' : $nb_users.' élève' ) : 'aucun' ;
$reception_todo = ($nb_items || $nb_users) ? 'true' : 'false' ;
$script_reception = 'var reception_todo = '.$reception_todo.';';
$script_reception.= 'var reception_items_texte = "'.$txt_items.'";';
$script_reception.= 'var reception_users_texte = "'.$txt_users.'";';
$script_reception.= 'var reception_items_liste = "'.implode('_',$tab_items).'";';
$script_reception.= 'var reception_users_liste = "'.implode('_',$tab_users).'";';

// $TYPE vaut "groupe" ou "selection"
$TYPE = ($nb_items || $nb_users)                    ? 'selection' : $SECTION ;
$TYPE = in_array($TYPE,array('groupe','selection')) ? $TYPE       : 'groupe' ;

$TITRE = ($TYPE=='groupe') ? "Évaluer une classe ou un groupe" : "Évaluer des élèves sélectionnés" ;

require(CHEMIN_DOSSIER_INCLUDE.'fonction_affichage_sections_communes.php');

// Formulaires de choix des élèves et de choix d'une période dans le cas d'une évaluation sur un groupe
$select_eleve   = '';
$select_periode = '';
$tab_niveau_js  = 'var tab_niveau = new Array();';
$tab_groupe_js  = 'var tab_groupe = new Array();';
$tab_groupe_periode_js = 'var tab_groupe_periode = new Array();';
if($TYPE=='groupe')
{
	// Élément de formulaire "f_aff_classe" pour le choix des élèves (liste des classes / groupes / besoins) du professeur, enregistré dans une variable javascript pour utilisation suivant le besoin, et utilisé pour un tri initial
	// Fabrication de tableaux javascript "tab_niveau" et "tab_groupe" indiquant le niveau et le nom d'un groupe
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
	// Fabrication du tableau javascript "tab_groupe_periode" pour les jointures groupes/périodes
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
}

// Date de début d'année scolaire dans le cas d'une évaluation sur une sélection d'élèves
// Sert à rechercher des élèves ayant passés une évaluation de même nom
if($TYPE=='selection')
{
	$annee = (date("n")<$_SESSION['MOIS_BASCULE_ANNEE_SCOLAIRE']) ? date("Y")-1 : date("Y") ;
	$jour_debut_annee_scolaire = '01/'.sprintf("%02u",$_SESSION['MOIS_BASCULE_ANNEE_SCOLAIRE']).'/'.$annee;
}

// Dates par défaut
$date_debut    = date("d/m/Y",mktime(0,0,0,date("m")-2,date("d"),date("Y"))); // 2 mois avant
$date_fin      = date("d/m/Y",mktime(0,0,0,date("m")+1,date("d"),date("Y"))); // 1 mois après
$date_autoeval = date("d/m/Y",mktime(0,0,0,date("m"),date("d")+7,date("Y"))); // 1 semaine après

$select_selection_items = Formulaire::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_selection_items($_SESSION['USER_ID']) , $select_nom='f_selection_items' , $option_first='oui' , $selection=false , $optgroup='non');
?>

<script type="text/javascript">
	var TYPE="<?php echo $TYPE ?>";
	// <![CDATA[
	var select_groupe = "<?php echo str_replace('"','\"','<option value=""></option>'.$select_eleve); ?>";
	// ]]>
	var url_export = "<?php echo URL_DIR_EXPORT ?>";
	var input_date = "<?php echo TODAY_FR ?>";
	var date_mysql = "<?php echo TODAY_MYSQL ?>";
	var input_autoeval = "<?php echo $date_autoeval ?>";
	var tab_items    = new Array();
	var tab_profs    = new Array();
	var tab_eleves   = new Array();
	var tab_sujets   = new Array();
	var tab_corriges = new Array();
	<?php echo $script_reception ?>
	<?php echo $tab_niveau_js ?> 
	<?php echo $tab_groupe_js ?> 
	<?php echo $tab_groupe_periode_js ?> 
</script>

<ul class="puce">
	<li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_professeur__evaluations_gestion">DOC : Gestion des évaluations.</a></span></li>
</ul>

<hr />

<form action="#" method="post" id="form0" class="hide"><fieldset>
<?php if($TYPE=='groupe'): ?>
	<label class="tab" for="f_aff_classe">Classe / groupe :</label><select id="f_aff_classe" name="f_aff_classe"><option value="d2">Toute classe / tout groupe</option><?php echo $select_eleve ?></select>
<?php endif; ?>
	<div id="zone_periodes">
		<label class="tab" for="f_aff_periode">Période :</label><?php echo $select_periode ?>
		<span id="dates_perso" class="show">
			du <input id="f_date_debut" name="f_date_debut" size="9" type="text" value="<?php echo $date_debut ?>" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q>
			au <input id="f_date_fin" name="f_date_fin" size="9" type="text" value="<?php echo $date_fin ?>" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q>
		</span><br />
		<span class="tab"></span><input type="hidden" name="f_action" value="lister_evaluations" /><input type="hidden" name="f_type" value="<?php echo $TYPE ?>" /><button id="actualiser" type="submit" class="actualiser">Actualiser l'affichage.</button><label id="ajax_msg0">&nbsp;</label>
	</div>
</fieldset></form>

<form action="#" method="post" id="form1" class="hide">
	<hr />
	<table class="form hsort">
		<thead>
			<tr>
				<th>Date devoir</th>
				<th>Date visible</th>
				<th>Fin auto-éval.</th>
				<th><?php echo($TYPE=='groupe')?'Classe / Groupe':'Élèves'; ?></th>
				<th>Description</th>
				<th>Items</th>
				<th>Profs</th>
				<th>Fichiers</th>
				<th class="nu"><q class="ajouter" title="Ajouter une évaluation."></q></th>
			</tr>
		</thead>
		<tbody>
			<tr><td class="nu" colspan="7"></td></tr>
		</tbody>
	</table>
</form>


<form action="#" method="post" id="zone_matieres_items" class="arbre_dynamique arbre_check hide">
	<div>Tout déployer / contracter : <a href="m1" class="all_extend"><img alt="m1" src="./_img/deploy_m1.gif" /></a> <a href="m2" class="all_extend"><img alt="m2" src="./_img/deploy_m2.gif" /></a> <a href="n1" class="all_extend"><img alt="n1" src="./_img/deploy_n1.gif" /></a> <a href="n2" class="all_extend"><img alt="n2" src="./_img/deploy_n2.gif" /></a> <a href="n3" class="all_extend"><img alt="n3" src="./_img/deploy_n3.gif" /></a></div>
	<p>Cocher ci-dessous (<span class="astuce">cliquer sur un intitulé pour déployer son contenu</span>) :</p>
	<?php
	// Affichage de la liste des items pour toutes les matières d'un professeur, sur tous les niveaux
	$DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_arborescence($_SESSION['USER_ID'],$matiere_id=0,$niveau_id=0,$only_socle=false,$only_item=false,$socle_nom=false);
	echo afficher_arborescence_matiere_from_SQL($DB_TAB,$dynamique=true,$reference=true,$aff_coef=false,$aff_cart=false,$aff_socle='texte',$aff_lien=false,$aff_input=true);
	?>
	<p class="danger">Une évaluation dont la saisie a commencé ne devrait pas voir ses items modifiés.<br />En particulier, retirer des items d'une évaluation efface les scores correspondants déjà saisis !</p>
	<div><span class="tab"></span><button id="valider_compet" type="button" class="valider">Valider la sélection</button>&nbsp;&nbsp;&nbsp;<button id="annuler_compet" type="button" class="annuler">Annuler / Retour</button></div>
	<hr />
	<p>
		<label class="tab" for="f_selection_items"><img alt="" src="./_img/bulle_aide.png" title="Pour choisir un regroupement d'items mémorisé." /> Initialisation</label><?php echo $select_selection_items ?><br />
		<label class="tab" for="f_liste_items_nom"><img alt="" src="./_img/bulle_aide.png" title="Pour enregistrer le groupe d'items cochés." /> Mémorisation</label><input id="f_liste_items_nom" name="f_liste_items_nom" size="30" type="text" value="" maxlength="60" /> <button id="f_enregistrer_items" type="button" class="fichier_export">Enregistrer</button><label id="ajax_msg_memo">&nbsp;</label>
	</p>
</form>

<form action="#" method="post" id="zone_profs" class="hide">
	<div class="astuce">Vous pouvez permettre à des collègues de co-saisir les notes de ce devoir (et de le dupliquer).</div>
	<?php echo afficher_form_element_checkbox_collegues() ?>
	<div style="clear:both"><button id="valider_profs" type="button" class="valider">Valider la sélection</button>&nbsp;&nbsp;&nbsp;<button id="annuler_profs" type="button" class="annuler">Annuler / Retour</button></div>
</form>

<?php if($TYPE=='selection'): ?>
<form action="#" method="post" id="zone_eleve" class="arbre_dynamique hide">
	<div><button id="indiquer_eleves_deja" type="button" class="eclair">Indiquer les élèves associés à une évaluation de même nom</button> depuis le <input id="f_date_deja" name="f_date_deja" size="9" type="text" value="<?php echo $jour_debut_annee_scolaire ?>" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q><label id="msg_indiquer_eleves_deja"></label></div>
	<p>Cocher ci-dessous (<span class="astuce">cliquer sur un intitulé pour déployer son contenu</span>) :</p>
	<?php echo afficher_form_element_checkbox_eleves_professeur(TRUE /*with_pourcent*/); ?>
	<p class="danger">Une évaluation dont la saisie a commencé ne devrait pas voir ses élèves modifiés.<br />En particulier, retirer des élèves d'une évaluation efface les scores correspondants déjà saisis !</p>
	<div><span class="tab"></span><button id="valider_eleve" type="button" class="valider">Valider la sélection</button>&nbsp;&nbsp;&nbsp;<button id="annuler_eleve" type="button" class="annuler">Annuler / Retour</button></div>
</form>
<?php endif; ?>

<form action="#" method="post" id="zone_ordonner" class="hide">
	<p class="hc"><b id="titre_ordonner"></b><br /><label id="msg_ordonner"></label></p>
	<div id="div_ordonner">
	</div>
</form>

<!-- Sans onsubmit="return false" une soumission incontrôlée s'effectue quand on presse "entrée" dans le cas d'un seul élève évalué sur un seul item. -->
<form action="#" method="post" id="zone_saisir" class="hide" onsubmit="return false">
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

	<p class="ti">Note à reporter dans &hellip;
		<label for="f_report_cellule">[ <input type="radio" id="f_report_cellule" name="f_endroit_report_note" value="cellule" checked /> la cellule ]</label>
		<label for="f_report_colonne">[ <input type="radio" id="f_report_colonne" name="f_endroit_report_note" value="colonne" /> la <span class="u">C</span>olonne ]</label>
		<label for="f_report_ligne">[ <input type="radio" id="f_report_ligne" name="f_endroit_report_note" value="ligne" /> la <span class="u">L</span>igne ]</label>
		<label for="f_report_tableau">[ <input type="radio" id="f_report_tableau" name="f_endroit_report_note" value="tableau" /> le <span class="u">T</span>ableau ]</label>.
		<span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_professeur__evaluations_saisie_resultats#toggle_saisies_multiples ">DOC : Report multiple.</a></span>
	</p>
	<div>
		<a id="to_zone_saisir_deport" href="#"><img src="./_img/toggle_plus.gif" alt="" title="Voir / masquer la saisie déportée." class="toggle" /></a> Saisie déportée
		<div id="zone_saisir_deport" class="hide">
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
		<a id="to_zone_voir_deport" href="#"><img src="./_img/toggle_plus.gif" alt="" title="Voir / masquer la saisie déportée." class="toggle" /></a> Saisie déportée &amp; archivage
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
	<span class="tab"></span><button id="f_submit_imprimer" type="button" class="valider">Générer le cartouche</button><label id="msg_imprimer">&nbsp;</label>
	<p id="zone_imprimer_retour"></p>
</fieldset></form>

<form action="#" method="post" id="zone_upload" class="hide"><fieldset>
	<h2>Ajouter / retirer un sujet ou une correction d'une évaluation</h2>
	<p class="hc b" id="titre_upload"></p>
	<p>
		<label class="tab">Sujet :</label><span id="span_sujet"></span> <button id="bouton_supprimer_sujet" type="button" class="supprimer">Retirer</button><br />
		<span class="tab"></span><button id="bouton_referencer_sujet" type="button" class="referencer_lien">Diriger vers ce lien externe.</button> <input id="f_adresse_sujet" name="f_adresse_sujet" maxlength="256" size="50" type="text" value="" /><br />
		<span class="tab"></span><button id="bouton_uploader_sujet" type="button" class="fichier_import">Envoyer un fichier à utiliser.</button> <?php echo FICHIER_TAILLE_MAX ?> Ko maxi, conservé <?php echo FICHIER_DUREE_CONSERVATION ?> mois. <img alt="" src="./_img/bulle_aide.png" title="La taille maximale autorisée et la durée de conservation des fichiers sont fixées par le webmestre." />
	</p>
	<p>
		<label class="tab">Corrigé :</label><span id="span_corrige"></span> <button id="bouton_supprimer_corrige" type="button" class="supprimer">Retirer</button><br />
		<span class="tab"></span><button id="bouton_referencer_corrige" type="button" class="referencer_lien">Diriger vers ce lien externe.</button> <input id="f_adresse_corrige" name="f_adresse_corrige" maxlength="256" size="50" type="text" value="" /><br />
		<span class="tab"></span><button id="bouton_uploader_corrige" type="button" class="fichier_import">Envoyer un fichier à utiliser.</button> <?php echo FICHIER_TAILLE_MAX ?> Ko maxi, conservé <?php echo FICHIER_DUREE_CONSERVATION ?> mois. <img alt="" src="./_img/bulle_aide.png" title="La taille maximale autorisée et la durée de conservation des fichiers sont fixées par le webmestre." />
	</p>
	<p><span class="tab"></span><button id="fermer_zone_upload" type="button" class="retourner">Retour</button><label id="ajax_document_upload">&nbsp;</label></p>
</fieldset></form>
