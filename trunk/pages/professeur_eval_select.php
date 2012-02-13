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
$TITRE = "Évaluer des élèves sélectionnés";
?>

<?php
// Dates par défaut de début et de fin
$date_debut  = date("d/m/Y",mktime(0,0,0,date("m")-2,date("d"),date("Y"))); // 2 mois avant
$date_fin    = date("d/m/Y",mktime(0,0,0,date("m")+1,date("d"),date("Y"))); // 1 mois après
// Date de début d'année scolaire
$annee = ($_SESSION['MOIS_BASCULE_ANNEE_SCOLAIRE']<date("n")) ? date("Y") : date("Y")-1 ;
$date_start = '01/'.sprintf("%02u",$_SESSION['MOIS_BASCULE_ANNEE_SCOLAIRE']).'/'.$annee;
?>

<ul class="puce">
	<li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_professeur__evaluations_gestion">DOC : Gestion des évaluations.</a></span></li>
</ul>

<hr />

<form action="#" method="post" id="form0" class="hide"><fieldset>
	<label class="tab">Période :</label>
		du <input id="f_date_debut" name="f_date_debut" size="9" type="text" value="<?php echo $date_debut ?>" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q>
		au <input id="f_date_fin" name="f_date_fin" size="9" type="text" value="<?php echo $date_fin ?>" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q>
	<br />
	<span class="tab"></span><input type="hidden" name="f_action" value="Afficher_evaluations" /><button id="actualiser" type="submit" class="actualiser">Actualiser l'affichage.</button><label id="ajax_msg0">&nbsp;</label>
</fieldset></form>

<form action="#" method="post" id="form1" class="hide">
	<hr />
	<table class="form">
		<thead>
			<tr>
				<th>Date devoir</th>
				<th>Date visible</th>
				<th>Élèves</th>
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

<script type="text/javascript">
	var input_date="<?php echo date("d/m/Y") ?>";
	var tab_items = new Array();
	var tab_profs = new Array();
	var tab_eleves = new Array();
</script>

<form action="#" method="post" id="zone_compet" class="arbre_dynamique arbre_check hide">
	<div>Tout déployer / contracter : <a href="m1" class="all_extend"><img alt="m1" src="./_img/deploy_m1.gif" /></a> <a href="m2" class="all_extend"><img alt="m2" src="./_img/deploy_m2.gif" /></a> <a href="n1" class="all_extend"><img alt="n1" src="./_img/deploy_n1.gif" /></a> <a href="n2" class="all_extend"><img alt="n2" src="./_img/deploy_n2.gif" /></a> <a href="n3" class="all_extend"><img alt="n3" src="./_img/deploy_n3.gif" /></a></div>
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

<form action="#" method="post" id="zone_eleve" class="arbre_dynamique hide">
	<div><button id="indiquer_eleves_deja" type="button" class="eclair">Indiquer les élèves associés à une évaluation de même nom</button> depuis le <input id="f_date_deja" name="f_date_deja" size="9" type="text" value="<?php echo $date_start ?>" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q><label id="msg_indiquer_eleves_deja"></label></div>
	<p>Cocher ci-dessous (<span class="astuce">cliquer sur un intitulé pour déployer son contenu</span>) :</p>
	<?php
	$tab_regroupements = array();
	$tab_id = array('classe'=>'','groupe'=>'');
	// Recherche de la liste des classes et des groupes du professeur
	$DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_classes_groupes_professeur($_SESSION['USER_ID']);
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_regroupements[$DB_ROW['groupe_id']] = array('nom'=>$DB_ROW['groupe_nom'],'eleve'=>array());
		$tab_id[$DB_ROW['groupe_type']][] = $DB_ROW['groupe_id'];
	}
	// Recherche de la liste des élèves pour chaque classe du professeur
	if(is_array($tab_id['classe']))
	{
		$listing = implode(',',$tab_id['classe']);
		$DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_eleves_classes($listing);
		foreach($DB_TAB as $DB_ROW)
		{
			$tab_regroupements[$DB_ROW['eleve_classe_id']]['eleve'][$DB_ROW['user_id']] = $DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'].' ('.$DB_ROW['user_login'].')';
		}
	}
	// Recherche de la liste des élèves pour chaque groupe du professeur
	if(is_array($tab_id['groupe']))
	{
		$listing = implode(',',$tab_id['groupe']);
		$DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_eleves_groupes($listing);
		foreach($DB_TAB as $DB_ROW)
		{
			$tab_regroupements[$DB_ROW['groupe_id']]['eleve'][$DB_ROW['user_id']] = $DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'].' ('.$DB_ROW['user_login'].')';
		}
	}
	// Affichage de la liste des élèves (du professeur) pour chaque classe et groupe
	foreach($tab_regroupements as $groupe_id => $tab_groupe)
	{
		echo'<ul class="ul_m1">'."\r\n";
		echo'	<li class="li_m1"><span class="deja">'.html($tab_groupe['nom']).'</span><span id="groupe_'.$groupe_id.'" class="gradient_pourcent"></span>'."\r\n";
		echo'		<ul class="ul_n3">'."\r\n";
		foreach($tab_groupe['eleve'] as $eleve_id => $eleve_nom)
		{
			// C'est plus compliqué que pour les items car un élève peut appartenir à une classe et plusieurs groupes => id du groupe mélé à l'id
			echo'			<li class="li_n3"><input id="id_'.$eleve_id.'_'.$groupe_id.'" name="f_eleves[]" type="checkbox" value="'.$eleve_id.'" /><label for="id_'.$eleve_id.'_'.$groupe_id.'"> '.html($eleve_nom).'</label><span></span></li>'."\r\n";
		}
		echo'		</ul>'."\r\n";
		echo'	</li>'."\r\n";
		echo'</ul>'."\r\n";
	}
	?>
	<p class="danger">Une évaluation dont la saisie a commencé ne devrait pas voir ses élèves modifiés.<br />En particulier, retirer des élèves d'une évaluation efface les scores correspondants déjà saisis !</p>
	<div><span class="tab"></span><button id="valider_eleve" type="button" class="valider">Valider la sélection</button>&nbsp;&nbsp;&nbsp;<button id="annuler_eleve" type="button" class="annuler">Annuler / Retour</button></div>
</form>

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
		<a id="to_zone_saisir_deport" href="#"><img src="./_img/toggle_plus.gif" alt="" title="Voir / masquer la saisie déportée." class="toggle" /></a> Saisie déportée
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
	<span class="tab"></span><button id="f_submit_imprimer" type="button" value="'.$ref.'" class="valider">Générer le cartouche</button><label id="msg_imprimer">&nbsp;</label>
	<p id="zone_imprimer_retour"></p>
</fieldset></form>
