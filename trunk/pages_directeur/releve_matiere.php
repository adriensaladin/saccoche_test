<?php
/**
 * @version $Id: releve_matiere.php 8 2009-10-30 20:56:02Z thomas $
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
$TITRE = "Bilans sur une matière";
?>

<?php
// Fabrication des éléments select du formulaire
$tab_cookie = load_cookie_select($_SESSION['STRUCTURE_ID'],$_SESSION['USER_ID']);
$select_groupe      = afficher_select(DB_OPT_classes_groupes_etabl($_SESSION['STRUCTURE_ID']) , $select_nom='f_groupe'      , $option_first='oui' , $selection=false                        , $optgroup='oui');
$select_periode     = afficher_select(DB_OPT_periodes_etabl($_SESSION['STRUCTURE_ID'])        , $select_nom='f_periode'     , $option_first='val' , $selection=false                        , $optgroup='non');
$select_orientation = afficher_select($tab_select_orientation                                 , $select_nom='f_orientation' , $option_first='non' , $selection=$tab_cookie['orientation']   , $optgroup='non');
$select_marge_min   = afficher_select($tab_select_marge_min                                   , $select_nom='f_marge_min'   , $option_first='non' , $selection=$tab_cookie['marge_min']     , $optgroup='non');
$select_couleur     = afficher_select($tab_select_couleur                                     , $select_nom='f_couleur'     , $option_first='non' , $selection=$tab_cookie['couleur']       , $optgroup='non');
$select_cases_nb    = afficher_select($tab_select_cases_nb                                    , $select_nom='f_cases_nb'    , $option_first='non' , $selection=$tab_cookie['cases_nb']      , $optgroup='non');
$select_cases_larg  = afficher_select($tab_select_cases_size                                  , $select_nom='f_cases_larg'  , $option_first='non' , $selection=$tab_cookie['cases_largeur'] , $optgroup='non');
$select_cases_haut  = afficher_select($tab_select_cases_size                                  , $select_nom='f_cases_haut'  , $option_first='non' , $selection=$tab_cookie['cases_hauteur'] , $optgroup='non');
// Dates par défaut de début et de fin
$annee_debut = (date('n')>8) ? date('Y') : date('Y')-1 ;
$date_debut = '01/09/'.$annee_debut;
$date_fin   = date("d/m/Y");
?>

<div class="hc"><span class="manuel"><a class="pop_up" href="./aide.php?fichier=releve_matiere">DOC : Bilans sur une matière.</a></span></div>

<form id="form_select" action=""><fieldset>
	<label class="tab" for="f_type">Type de bilan :</label><label for="f_type_individuel"><input type="checkbox" id="f_type_individuel" name="f_type" value="individuel" /> Relevé individuel</label>&nbsp;&nbsp;&nbsp;<label for="f_type_synthese"><input type="checkbox" id="f_type_synthese" name="f_type" value="synthese" /> Synthèse collective</label>&nbsp;&nbsp;&nbsp;<label for="f_type_bulletin"><input type="checkbox" id="f_type_bulletin" name="f_type" value="bulletin" /> Bulletin (moyenne &amp; appréciation)</label><input type="hidden" id="types" name="types" value="" /><br />
	<span id="options_releve" class="hide">
		<label class="tab" for="f_opt_grille">Opt. relevé <img alt="" src="./_img/bulle_aide.png" title="Pour le relévé individuel, les paramètres des items peuvent être affichés." /> :</label><label for="f_coef"><input type="checkbox" id="f_coef" name="f_coef" value="1" /> Coefficients</label>&nbsp;&nbsp;&nbsp;<label for="f_socle"><input type="checkbox" id="f_socle" name="f_socle" value="1" /> Socle</label>&nbsp;&nbsp;&nbsp;<label for="f_lien"><input type="checkbox" id="f_lien" name="f_lien" value="1" /> Liens de remédiation</label><br />
		<label class="tab" for="f_opt_bilan">Opt. relevé <img alt="" src="./_img/bulle_aide.png" title="Pour le relévé individuel, deux lignes de synthèse peuvent être ajoutées.<br />Dans ce cas, une note sur 20 peut aussi être affichée." /> :</label><label for="f_bilan_ms"><input type="checkbox" id="f_bilan_ms" name="f_bilan_ms" value="1" checked="checked" /> Moyenne des scores</label>&nbsp;&nbsp;&nbsp;<label for="f_bilan_pv"><input type="checkbox" id="f_bilan_pv" name="f_bilan_pv" value="1" checked="checked" /> Pourcentage d'acquisitions validées</label>&nbsp;&nbsp;&nbsp;<label for="f_conv_sur20"><input type="checkbox" id="f_conv_sur20" name="f_conv_sur20" value="1" /> Proposition de note sur 20</label><br />
	</span>
	<span id="astuce_bulletin" class="hide">
		<span class="tab"></span><span class="manuel"><a class="pop_up" href="./aide.php?fichier=transfert_bulletin_SACoche_Gepi">DOC : Transfert du bulletin de SACoche dans GEPI.</a></span>
	</span>
	<p />
	<label class="tab" for="f_periode"><img alt="" src="./_img/bulle_aide.png" title="Les items pris en compte sont ceux qui sont évalués<br />au moins une fois sur cette période." /> Période :</label><?php echo $select_periode ?>
	<span id="dates_perso" class="show">
		du <input id="f_date_debut" name="f_date_debut" size="9" type="text" value="<?php echo $date_debut ?>" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q>
		au <input id="f_date_fin" name="f_date_fin" size="9" type="text" value="<?php echo $date_fin ?>" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q>
	</span><br />
	<span class="radio"><img alt="" src="./_img/bulle_aide.png" title="Le bilan peut être établi uniquement sur la période considérée<br />ou en tenant compte d'évaluations antérieures des items concernés." /> Prise en compte des évaluations antérieures :</span><label for="f_retro_oui"><input type="radio" id="f_retro_oui" name="f_retroactif" value="oui" checked="checked" /> oui</label>&nbsp;&nbsp;&nbsp;&nbsp;<label for="f_retro_non"><input type="radio" id="f_retro_non" name="f_retroactif" value="non" /> non</label><p />
	<label class="tab" for="f_groupe">Élève(s) :</label><?php echo $select_groupe ?><input type="hidden" id="f_groupe_nom" name="f_groupe_nom" value="" /><label id="ajax_maj">&nbsp;</label><br />
	<label class="tab" for="f_matiere">Matière :</label><select id="f_matiere" name="f_matiere"><option></option></select><input type="hidden" id="f_matiere_nom" name="f_matiere_nom" value="" /><br />
	<span class="tab"></span><select id="f_eleve" name="f_eleve[]" multiple="multiple" size="9"><option></option></select><input type="hidden" id="eleves" name="eleves" value="" />
	<div class="toggle">
		<span class="tab"></span><a href="#" class="puce_plus toggle">Afficher plus d'options</a>
	</div>
	<div class="toggle hide">
		<span class="tab"></span><a href="#" class="puce_moins toggle">Afficher moins d'options</a><br />
		<label class="tab" for="f_orientation">Orientation :</label><?php echo $select_orientation ?> en <?php echo $select_couleur ?> avec marges minimales de </label><?php echo $select_marge_min ?><br />
		<label class="tab" for="f_cases_nb">Évaluations :</label><?php echo $select_cases_nb ?> de largeur <?php echo $select_cases_larg ?> et de hauteur <?php echo $select_cases_haut ?><p />
	</div>
	<span class="tab"></span><input id="f_submit" type="submit" value="Valider." /><label id="ajax_msg">&nbsp;</label><br />
</fieldset></form>

<hr />

<div id="bilan">
</div>

