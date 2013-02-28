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
$TITRE = "Synthèse pluridisciplinaire";
?>

<?php
// L'élève ne choisit évidemment pas sa classe ni son nom, mais on construit qd même les formulaires, on les remplit et on les cache (permet un code unique et une transmission des infos en ajax comme pour les autres profils).
Form::load_choix_memo();
$check_fusion_niveaux = (Form::$tab_choix['fusion_niveaux'])     ? ' checked' : '' ;
$check_retro_auto     = (Form::$tab_choix['retroactif']=='auto') ? ' checked' : '' ;
$check_retro_non      = (Form::$tab_choix['retroactif']=='non')  ? ' checked' : '' ;
$check_retro_oui      = (Form::$tab_choix['retroactif']=='oui')  ? ' checked' : '' ;
$check_only_socle     = (Form::$tab_choix['only_socle'])         ? ' checked' : '' ;
$check_only_niveau    = (Form::$tab_choix['only_niveau'])        ? ' checked' : '' ;
$check_aff_coef       = (Form::$tab_choix['aff_coef'])           ? ' checked' : '' ;
$check_aff_socle      = (Form::$tab_choix['aff_socle'])          ? ' checked' : '' ;
$check_aff_lien       = (Form::$tab_choix['aff_lien'])           ? ' checked' : '' ;
$check_aff_start      = (Form::$tab_choix['aff_start'])          ? ' checked' : '' ;
if($_SESSION['USER_PROFIL_TYPE']=='directeur')
{
  $tab_groupes = DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl();
  $of_g = 'oui'; $sel_g = FALSE; $class_form_eleve = 'show'; $class_form_periode = 'hide'; $class_form_option = 'hide';
  $multiple_eleve = ' multiple size="9"';
  $select_eleves = '<option></option>'; // maj en ajax suivant le choix du groupe
}
if($_SESSION['USER_PROFIL_TYPE']=='professeur')
{
  $tab_groupes = ($_SESSION['USER_JOIN_GROUPES']=='config') ? DB_STRUCTURE_COMMUN::DB_OPT_groupes_professeur($_SESSION['USER_ID']) : DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl() ;
  $of_g = 'oui'; $sel_g = FALSE; $class_form_eleve = 'show'; $class_form_periode = 'hide'; $class_form_option = 'hide';
  $multiple_eleve = ' multiple size="9"';
  $select_eleves = '<option></option>'; // maj en ajax suivant le choix du groupe
}
if( ($_SESSION['USER_PROFIL_TYPE']=='parent') && ($_SESSION['NB_ENFANTS']!=1) )
{
  $tab_groupes  = $_SESSION['OPT_PARENT_CLASSES']; Form::$tab_select_optgroup = array('classe'=>'Classes');
  $of_g = 'oui'; $sel_g = FALSE; $class_form_eleve = 'show'; $class_form_periode = 'hide'; $class_form_option = 'hide';
  $multiple_eleve = ''; // volontaire
  $select_eleves = '<option></option>'; // maj en ajax suivant le choix du groupe
}
if( ($_SESSION['USER_PROFIL_TYPE']=='parent') && ($_SESSION['NB_ENFANTS']==1) )
{
  $tab_groupes  = array(0=>array('valeur'=>$_SESSION['ELEVE_CLASSE_ID'],'texte'=>$_SESSION['ELEVE_CLASSE_NOM'],'optgroup'=>'classe')); Form::$tab_select_optgroup = array('classe'=>'Classes');
  $of_g = 'non'; $sel_g = TRUE; $class_form_eleve = 'hide'; $class_form_periode = 'show'; $class_form_option = 'hide';
  $multiple_eleve = '';
  $select_eleves = '<option value="'.$_SESSION['OPT_PARENT_ENFANTS'][0]['valeur'].'" selected>'.html($_SESSION['OPT_PARENT_ENFANTS'][0]['texte']).'</option>';
}
if($_SESSION['USER_PROFIL_TYPE']=='eleve')
{
  $tab_groupes = array(0=>array('valeur'=>$_SESSION['ELEVE_CLASSE_ID'],'texte'=>$_SESSION['ELEVE_CLASSE_NOM'],'optgroup'=>'classe')); Form::$tab_select_optgroup = array('classe'=>'Classes');
  $of_g = 'non'; $sel_g = TRUE;  $class_form_eleve = 'hide'; $class_form_periode = 'show'; $class_form_option = 'show';
  $multiple_eleve = '';
  $select_eleves = '<option value="'.$_SESSION['USER_ID'].'" selected>'.html($_SESSION['USER_NOM'].' '.$_SESSION['USER_PRENOM']).'</option>';
}
$tab_periodes = DB_STRUCTURE_COMMUN::DB_OPT_periodes_etabl();

$select_groupe    = Form::afficher_select($tab_groupes                , $select_nom='f_groupe'    , $option_first=$of_g , $selection=$sel_g                        , $optgroup='oui'); // optgroup à oui y compris pour les élèves (formulaire invisible) car recherche du type de groupe dans le js
$select_periode   = Form::afficher_select($tab_periodes               , $select_nom='f_periode'   , $option_first='val' , $selection=FALSE                         , $optgroup='non');
$select_marge_min = Form::afficher_select(Form::$tab_select_marge_min , $select_nom='f_marge_min' , $option_first='non' , $selection=Form::$tab_choix['marge_min'] , $optgroup='non');
$select_couleur   = Form::afficher_select(Form::$tab_select_couleur   , $select_nom='f_couleur'   , $option_first='non' , $selection=Form::$tab_choix['couleur']   , $optgroup='non');
$select_legende   = Form::afficher_select(Form::$tab_select_legende   , $select_nom='f_legende'   , $option_first='non' , $selection=Form::$tab_choix['legende']   , $optgroup='non');

// Fabrication du tableau javascript "tab_groupe_periode" pour les jointures groupes/périodes
// Fabrication du tableau javascript "tab_groupe_niveau" pour les jointures groupes/niveaux
list( $tab_groupe_periode_js , $tab_groupe_niveau_js ) = Form::fabriquer_tab_js_jointure_groupe( $tab_groupes , TRUE /*return_jointure_periode*/ , TRUE /*return_jointure_niveau*/ );
?>

<script type="text/javascript">
  var date_mysql="<?php echo TODAY_MYSQL ?>";
  <?php echo $tab_groupe_periode_js ?> 
  <?php echo $tab_groupe_niveau_js ?> 
</script>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__synthese_multimatiere">DOC : Synthèse pluridisciplinaire.</a></span></div>
<div class="astuce">Un administrateur ou un directeur doit indiquer le type de synthèse adapté suivant chaque référentiel (<span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__reglages_syntheses_bilans#toggle_type_synthese">DOC</a></span>).</div>
<?php
$nb_inconnu = DB_STRUCTURE_BILAN::DB_compter_modes_synthese_inconnu();
$s = ($nb_inconnu>1) ? 's' : '' ;
echo ($nb_inconnu) ? '<label class="alerte">Il y a '.$nb_inconnu.' référentiel'.$s.' dont le format de synthèse est inconnu (donc non pris en compte).</label>' : '<label class="valide">Tous les référentiels ont un format de synthèse prédéfini.</label>' ;
?>

<hr />

<form action="#" method="post" id="form_select"><fieldset>
  <p class="<?php echo $class_form_eleve ?>">
    <label class="tab" for="f_groupe">Classe / groupe :</label><?php echo $select_groupe ?><input type="hidden" id="f_groupe_nom" name="f_groupe_nom" value="" /><label id="ajax_maj">&nbsp;</label><br />
    <label class="tab" for="f_eleve"><img alt="" src="./_img/bulle_aide.png" title="Utiliser la touche &laquo;&nbsp;Shift&nbsp;&raquo; pour une sélection multiple contiguë.<br />Utiliser la touche &laquo;&nbsp;Ctrl&nbsp;&raquo; pour une sélection multiple non contiguë." /> Élève(s) :</label><select id="f_eleve" name="f_eleve[]"<?php echo $multiple_eleve ?>><?php echo $select_eleves ?></select>
  </p>
  <p id="zone_periodes" class="<?php echo $class_form_periode ?>">
    <label class="tab" for="f_periode"><img alt="" src="./_img/bulle_aide.png" title="Les items pris en compte sont ceux qui sont évalués<br />au moins une fois sur cette période." /> Période :</label><?php echo $select_periode ?>
    <span id="dates_perso" class="show">
      du <input id="f_date_debut" name="f_date_debut" size="9" type="text" value="<?php echo jour_debut_annee_scolaire('french') ?>" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q>
      au <input id="f_date_fin" name="f_date_fin" size="9" type="text" value="<?php echo TODAY_FR ?>" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q>
    </span><br />
    <span class="radio"><img alt="" src="./_img/bulle_aide.png" title="Le bilan peut être établi uniquement sur la période considérée<br />ou en tenant compte d'évaluations antérieures des items concernés.<br />En automatique, les paramètres enregistrés pour chaque référentiel s'appliquent." /> Prise en compte des évaluations antérieures :</span>
      <label for="f_retro_auto"><input type="radio" id="f_retro_auto" name="f_retroactif" value="auto"<?php echo $check_retro_auto ?> /> automatique (selon référentiels)</label>&nbsp;&nbsp;&nbsp;
      <label for="f_retro_non"><input type="radio" id="f_retro_non" name="f_retroactif" value="non"<?php echo $check_retro_non ?> /> non</label>&nbsp;&nbsp;&nbsp;
      <label for="f_retro_oui"><input type="radio" id="f_retro_oui" name="f_retroactif" value="oui"<?php echo $check_retro_oui ?> /> oui</label>
  </p>
  <div id="zone_options" class="<?php echo $class_form_option ?>">
    <div class="toggle">
      <span class="tab"></span><a href="#" class="puce_plus toggle">Afficher plus d'options</a>
    </div>
    <div class="toggle hide">
      <span class="tab"></span><a href="#" class="puce_moins toggle">Afficher moins d'options</a><br />
      <label class="tab">Mode de synthèse :</label><label for="f_fusion_niveaux"><input type="checkbox" id="f_fusion_niveaux" name="f_fusion_niveaux" value="1"<?php echo $check_fusion_niveaux ?> /> Ne pas indiquer le niveau et fusionner les synthèses de même intitulé</label><br />
      <label class="tab"><img alt="" src="./_img/bulle_aide.png" title="Pour le format html, le détail des items peut être affiché." /> Indications :</label><label for="f_coef"><input type="checkbox" id="f_coef" name="f_coef" value="1"<?php echo $check_aff_coef ?> /> Coefficients</label>&nbsp;&nbsp;&nbsp;<label for="f_socle"><input type="checkbox" id="f_socle" name="f_socle" value="1"<?php echo $check_aff_socle ?> /> Appartenance au socle</label>&nbsp;&nbsp;&nbsp;<label for="f_lien"><input type="checkbox" id="f_lien" name="f_lien" value="1"<?php echo $check_aff_lien ?> /> Liens (ressources pour travailler)</label>&nbsp;&nbsp;&nbsp;<label for="f_start"><input type="checkbox" id="f_start" name="f_start" value="1"<?php echo $check_aff_start ?> /> Détails affichés par défaut</label><br />
      <label class="tab">Restrictions :</label><label for="f_restriction_socle"><input type="checkbox" id="f_restriction_socle" name="f_restriction_socle" value="1"<?php echo $check_only_socle ?> /> Uniquement les items liés du socle</label><br />
      <label class="tab"></label><label for="f_restriction_niveau"><input type="checkbox" id="f_restriction_niveau" name="f_restriction_niveau" value="1"<?php echo $check_only_niveau ?> /> Utiliser uniquement les items du niveau <em id="niveau_nom"></em></label><input type="hidden" id="f_niveau" name="f_niveau" value="" /><br />
      <label class="tab"><img alt="" src="./_img/bulle_aide.png" title="Pour le format pdf." /> Impression :</label><?php echo $select_couleur ?> <?php echo $select_legende ?> <?php echo $select_marge_min ?>
    </div>
  </div>
  <p>
    <span class="tab"></span><button id="bouton_valider" type="submit" class="generer">Générer.</button><label id="ajax_msg">&nbsp;</label>
  </p>
</fieldset></form>

<div id="bilan"></div>

