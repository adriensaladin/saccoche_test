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
$TITRE = "Bilan d'items d'une matière";
$VERSION_JS_FILE += 11;
?>

<?php
// L'élève ne choisit évidemment pas sa classe ni nom nom, mais on construit qd même les formulaires, on les remplit et on les cache (permet un code unique et une transmission des infos en ajax comme pour les autres profils).
// L'élève ne choisit pas non plus son type de bilan (=>individuel), et les options du relevé sont prédéfinies.
Formulaire::load_choix_memo();
$check_type_individuel = (Formulaire::$tab_choix['type_individuel'])   ? ' checked' : '' ;
$class_form_individuel = (Formulaire::$tab_choix['type_individuel'])   ? 'show'     : 'hide' ;
$check_type_synthese   = (Formulaire::$tab_choix['type_synthese'])     ? ' checked' : '' ;
$check_type_bulletin   = (Formulaire::$tab_choix['type_bulletin'])     ? ' checked' : '' ;
$check_bilan_MS        = (Formulaire::$tab_choix['aff_bilan_MS'])      ? ' checked' : '' ;
$check_bilan_PA        = (Formulaire::$tab_choix['aff_bilan_PA'])      ? ' checked' : '' ;
$check_conv_sur20      = (Formulaire::$tab_choix['aff_conv_sur20'])    ? ' checked' : '' ;
$class_conv_sur20      = ($check_bilan_MS || $check_bilan_PA)           ? 'show'     : 'hide' ;
$check_retro_oui       = (Formulaire::$tab_choix['retroactif']=='oui') ? ' checked' : '' ;
$check_retro_non       = (Formulaire::$tab_choix['retroactif']=='non') ? ' checked' : '' ;
$check_only_socle      = (Formulaire::$tab_choix['only_socle'])        ? ' checked' : '' ;
$check_aff_coef        = (Formulaire::$tab_choix['aff_coef'])          ? ' checked' : '' ;
$check_aff_socle       = (Formulaire::$tab_choix['aff_socle'])         ? ' checked' : '' ;
$check_aff_lien        = (Formulaire::$tab_choix['aff_lien'])          ? ' checked' : '' ;
if($_SESSION['USER_PROFIL']=='directeur')
{
	$tab_groupes  = DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl();
	$tab_matieres = 'Choisir d\'abord un groupe ci dessous...'; // maj en ajax suivant le choix du groupe
	$of_g = 'oui'; $sel_g = false; $class_form_type = 'show'; $class_form_eleve = 'show'; $class_form_periode = 'hide';
	$select_eleves = '<option></option>'; // maj en ajax suivant le choix du groupe
}
if($_SESSION['USER_PROFIL']=='professeur')
{
	$tab_groupes  = DB_STRUCTURE_COMMUN::DB_OPT_groupes_professeur($_SESSION['USER_ID']);
	$tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_professeur($_SESSION['MATIERES'],$_SESSION['USER_ID']);
	$of_g = 'oui'; $sel_g = false; $class_form_type = 'show'; $class_form_eleve = 'show'; $class_form_periode = 'hide';
	$select_eleves = '<option></option>'; // maj en ajax suivant le choix du groupe
}
if( ($_SESSION['USER_PROFIL']=='parent') && ($_SESSION['NB_ENFANTS']!=1) )
{
	$tab_groupes  = $_SESSION['OPT_PARENT_CLASSES']; Formulaire::$tab_select_optgroup = array('classe'=>'Classes');
	$tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_etabl($_SESSION['MATIERES'],$transversal=true);
	$of_g = 'oui'; $sel_g = false; $class_form_type = 'hide'; $class_form_eleve = 'show'; $class_form_periode = 'hide';
	$select_eleves = '<option></option>'; // maj en ajax suivant le choix du groupe
}
if( ($_SESSION['USER_PROFIL']=='parent') && ($_SESSION['NB_ENFANTS']==1) )
{
	$tab_groupes  = array(0=>array('valeur'=>$_SESSION['ELEVE_CLASSE_ID'],'texte'=>$_SESSION['ELEVE_CLASSE_NOM'],'optgroup'=>'classe')); Formulaire::$tab_select_optgroup = array('classe'=>'Classes');
	$tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_eleve($_SESSION['MATIERES'],$_SESSION['OPT_PARENT_ENFANTS'][0]['valeur']);
	$of_g = 'non'; $sel_g = true; $class_form_type = 'hide'; $class_form_eleve = 'hide'; $class_form_periode = 'show';
	$select_eleves = '<option value="'.$_SESSION['OPT_PARENT_ENFANTS'][0]['valeur'].'" selected>'.html($_SESSION['OPT_PARENT_ENFANTS'][0]['texte']).'</option>';
}
if($_SESSION['USER_PROFIL']=='eleve')
{
	$tab_groupes  = array(0=>array('valeur'=>$_SESSION['ELEVE_CLASSE_ID'],'texte'=>$_SESSION['ELEVE_CLASSE_NOM'],'optgroup'=>'classe')); Formulaire::$tab_select_optgroup = array('classe'=>'Classes');
	$tab_matieres = DB_STRUCTURE_COMMUN::DB_OPT_matieres_eleve($_SESSION['MATIERES'],$_SESSION['USER_ID']);
	$of_g = 'non'; $sel_g = true; $class_form_type = 'hide'; $class_form_eleve = 'hide'; $class_form_periode = 'show';
	$select_eleves = '<option value="'.$_SESSION['USER_ID'].'" selected>'.html($_SESSION['USER_NOM'].' '.$_SESSION['USER_PRENOM']).'</option>';
}
$tab_periodes = DB_STRUCTURE_COMMUN::DB_OPT_periodes_etabl();

$select_groupe      = Formulaire::afficher_select($tab_groupes                        , $select_nom='f_groupe'      , $option_first=$of_g , $selection=$sel_g                                   , $optgroup='oui'); // optgroup à oui y compris pour les élèves (formulaire invisible) car recherche du type de groupe dans le js
$select_matiere     = Formulaire::afficher_select($tab_matieres                       , $select_nom='f_matiere'     , $option_first='oui' , $selection=Formulaire::$tab_choix['matiere_id']    , $optgroup='non');
$select_periode     = Formulaire::afficher_select($tab_periodes                       , $select_nom='f_periode'     , $option_first='val' , $selection=false                                    , $optgroup='non');
$select_orientation = Formulaire::afficher_select(Formulaire::$tab_select_orientation , $select_nom='f_orientation' , $option_first='non' , $selection=Formulaire::$tab_choix['orientation']   , $optgroup='non');
$select_marge_min   = Formulaire::afficher_select(Formulaire::$tab_select_marge_min   , $select_nom='f_marge_min'   , $option_first='non' , $selection=Formulaire::$tab_choix['marge_min']     , $optgroup='non');
$select_pages_nb    = Formulaire::afficher_select(Formulaire::$tab_select_pages_nb    , $select_nom='f_pages_nb'    , $option_first='non' , $selection=Formulaire::$tab_choix['pages_nb']      , $optgroup='non');
$select_couleur     = Formulaire::afficher_select(Formulaire::$tab_select_couleur     , $select_nom='f_couleur'     , $option_first='non' , $selection=Formulaire::$tab_choix['couleur']       , $optgroup='non');
$select_legende     = Formulaire::afficher_select(Formulaire::$tab_select_legende     , $select_nom='f_legende'     , $option_first='non' , $selection=Formulaire::$tab_choix['legende']       , $optgroup='non');
$select_cases_nb    = Formulaire::afficher_select(Formulaire::$tab_select_cases_nb    , $select_nom='f_cases_nb'    , $option_first='non' , $selection=Formulaire::$tab_choix['cases_nb']      , $optgroup='non');
$select_cases_larg  = Formulaire::afficher_select(Formulaire::$tab_select_cases_size  , $select_nom='f_cases_larg'  , $option_first='non' , $selection=Formulaire::$tab_choix['cases_largeur'] , $optgroup='non');

// Dates par défaut de début et de fin
$annee_debut = (date('n')>8) ? date('Y') : date('Y')-1 ;
$date_debut = '01/09/'.$annee_debut;
$date_fin   = date("d/m/Y");

// Fabrication du tableau javascript "tab_groupe_periode" pour les jointures groupes/périodes
$tab_groupe_periode_js = 'var tab_groupe_periode = new Array();';
if(is_array($tab_groupes))
{
	$tab_id_classe_groupe = array();
	foreach($tab_groupes as $tab_groupe_infos)
	{
		if($tab_groupe_infos['optgroup']!='besoin')
		{
			$tab_id_classe_groupe[] = $tab_groupe_infos['valeur'];
		}
	}
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
	var profil = "<?php echo $_SESSION['USER_PROFIL'] ?>";
	var date_mysql="<?php echo date("Y-m-d") ?>";
	<?php echo $tab_groupe_periode_js ?> 
</script>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__releve_items_matiere">DOC : Bilan d'items d'une matière.</a></span></div>
<div class="<?php echo $class_form_type ?>"><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__transfert_bulletin_SACoche_Gepi">DOC : Transfert du bulletin de SACoche vers GEPI.</a></span></div>
<div class="<?php echo $class_form_type ?>"><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__transfert_note_SACoche_Gepi">DOC : Transfert d'une note bilan de SACoche vers GEPI.</a></span></div>

<hr />

<form action="#" method="post" id="form_select"><fieldset>
	<p class="<?php echo $class_form_type ?>">
		<label class="tab" for="f_type">Type de bilan :</label><label for="f_type_individuel"><input type="checkbox" id="f_type_individuel" name="f_type" value="individuel"<?php echo $check_type_individuel ?> /> Relevé individuel</label>&nbsp;&nbsp;&nbsp;<label for="f_type_synthese"><input type="checkbox" id="f_type_synthese" name="f_type" value="synthese"<?php echo $check_type_synthese ?> /> Synthèse collective</label>&nbsp;&nbsp;&nbsp;<label for="f_type_bulletin"><input type="checkbox" id="f_type_bulletin" name="f_type" value="bulletin"<?php echo $check_type_bulletin ?> /> Bulletin (moyenne &amp; appréciation)</label><input type="hidden" id="types" name="types" value="" /><br />
		<span id="options_individuel" class="<?php echo $class_form_individuel ?>">
			<label class="tab" for="f_opt_bilan"><img alt="" src="./_img/bulle_aide.png" title="Pour le relévé individuel, deux lignes de synthèse peuvent être ajoutées.<br />Dans ce cas, une note sur 20 peut aussi être affichée." /> Opt. relevé :</label><label for="f_bilan_MS"><input type="checkbox" id="f_bilan_MS" name="f_bilan_MS" value="1"<?php echo $check_bilan_MS ?> /> Moyenne des scores</label>&nbsp;&nbsp;&nbsp;<label for="f_bilan_PA"><input type="checkbox" id="f_bilan_PA" name="f_bilan_PA" value="1"<?php echo $check_bilan_PA ?> /> Pourcentage d'items acquis</label>&nbsp;&nbsp;&nbsp;<label for="f_conv_sur20" class="<?php echo $class_conv_sur20 ?>"><input type="checkbox" id="f_conv_sur20" name="f_conv_sur20" value="1"<?php echo $check_conv_sur20 ?> /> Proposition de note sur 20</label><br />
		</span>
	</p>
	<p>
		<label class="tab" for="f_matiere">Matière :</label><?php echo $select_matiere ?><input type="hidden" id="f_matiere_nom" name="f_matiere_nom" value="" />
	</p>
	<p class="<?php echo $class_form_eleve ?>">
		<label class="tab" for="f_groupe">Classe / groupe :</label><?php echo $select_groupe ?><input type="hidden" id="f_groupe_nom" name="f_groupe_nom" value="" /><label id="ajax_maj">&nbsp;</label><br />
		<label class="tab" for="f_eleve"><img alt="" src="./_img/bulle_aide.png" title="Utiliser la touche &laquo&nbsp;Shift&nbsp;&raquo; pour une sélection multiple contiguë.<br />Utiliser la touche &laquo&nbsp;Ctrl&nbsp;&raquo; pour une sélection multiple non contiguë." /> Élève(s) :</label><select id="f_eleve" name="f_eleve[]" multiple size="9"><?php echo $select_eleves ?></select><input type="hidden" id="eleves" name="eleves" value="" />
	</p>
	<p id="zone_periodes" class="<?php echo $class_form_periode ?>">
		<label class="tab" for="f_periode"><img alt="" src="./_img/bulle_aide.png" title="Les items pris en compte sont ceux qui sont évalués<br />au moins une fois sur cette période." /> Période :</label><?php echo $select_periode ?>
		<span id="dates_perso" class="show">
			du <input id="f_date_debut" name="f_date_debut" size="9" type="text" value="<?php echo $date_debut ?>" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q>
			au <input id="f_date_fin" name="f_date_fin" size="9" type="text" value="<?php echo $date_fin ?>" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q>
		</span><br />
		<span class="radio"><img alt="" src="./_img/bulle_aide.png" title="Le bilan peut être établi uniquement sur la période considérée<br />ou en tenant compte d'évaluations antérieures des items concernés." /> Prise en compte des évaluations antérieures :</span><label for="f_retro_oui"><input type="radio" id="f_retro_oui" name="f_retroactif" value="oui"<?php echo $check_retro_oui ?> /> oui</label>&nbsp;&nbsp;&nbsp;&nbsp;<label for="f_retro_non"><input type="radio" id="f_retro_non" name="f_retroactif" value="non"<?php echo $check_retro_non ?> /> non</label>
	</p>
	<div class="toggle">
		<span class="tab"></span><a href="#" class="puce_plus toggle">Afficher plus d'options</a>
	</div>
	<div class="toggle hide">
		<span class="tab"></span><a href="#" class="puce_moins toggle">Afficher moins d'options</a><br />
		<label class="tab" for="f_restriction">Restriction :</label><input type="checkbox" id="f_restriction" name="f_restriction" value="1"<?php echo $check_only_socle ?> /> <label for="f_restriction">Uniquement les items liés du socle</label><br />
		<label class="tab" for="f_indication"><img alt="" src="./_img/bulle_aide.png" title="Pour le relévé individuel, les paramètres des items peuvent être affichés." /> Indications :</label><label for="f_coef"><input type="checkbox" id="f_coef" name="f_coef" value="1"<?php echo $check_aff_coef ?> /> Coefficients</label>&nbsp;&nbsp;&nbsp;<label for="f_socle"><input type="checkbox" id="f_socle" name="f_socle" value="1"<?php echo $check_aff_socle ?> /> Socle</label>&nbsp;&nbsp;&nbsp;<label for="f_lien"><input type="checkbox" id="f_lien" name="f_lien" value="1"<?php echo $check_aff_lien ?> /> Liens de remédiation</label><br />
		<label class="tab" for="f_impression"><img alt="" src="./_img/bulle_aide.png" title="Pour le format pdf." /> Impression :</label><?php echo $select_orientation ?> <?php echo $select_couleur ?> <?php echo $select_legende ?> <?php echo $select_marge_min ?> <?php echo $select_pages_nb ?></label><br />
		<label class="tab" for="f_cases_nb">Évaluations :</label><?php echo $select_cases_nb ?> de largeur <?php echo $select_cases_larg ?><p />
	</div>
	<span class="tab"></span><button id="bouton_valider" type="submit"><img alt="" src="./_img/bouton/generer.png" /> Générer.</button><label id="ajax_msg">&nbsp;</label><br />
</fieldset></form>

<hr />

<div id="bilan">
</div>

