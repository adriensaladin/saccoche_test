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
$VERSION_JS_FILE += 2;
?>

<?php
// Fabrication des éléments select du formulaire
// L'élève ne choisit évidemment pas sa classe ni nom nom, mais on construit qd même les formulaires, on les remplit et on les cache (permet un code unique et une transmission des infos en ajax comme pour les autres profils).
$tab_cookie = load_cookie_select('releve_synthese');
if($_SESSION['USER_PROFIL']=='directeur')
{
	$tab_groupes = DB_STRUCTURE_OPT_classes_groupes_etabl();
	$of_g = 'oui'; $sel_g = false; $class_form_eleve = 'show'; $class_form_periode = 'hide'; $class_form_option = 'hide';
	$select_eleves = '<option></option>'; // maj en ajax suivant le choix du groupe
	$check_option_lien = '';
}
if($_SESSION['USER_PROFIL']=='professeur')
{
	$tab_groupes = DB_STRUCTURE_OPT_groupes_professeur($_SESSION['USER_ID']);
	$of_g = 'oui'; $sel_g = false; $class_form_eleve = 'show'; $class_form_periode = 'hide'; $class_form_option = 'hide';
	$select_eleves = '<option></option>'; // maj en ajax suivant le choix du groupe
	$check_option_lien = '';
}
if($_SESSION['USER_PROFIL']=='eleve')
{
	$tab_groupes = array(0=>array('valeur'=>$_SESSION['ELEVE_CLASSE_ID'],'texte'=>$_SESSION['ELEVE_CLASSE_NOM'],'optgroup'=>'classe')); $GLOBALS['tab_select_optgroup'] = array('classe'=>'Classes');
	$of_g = 'non'; $sel_g = true;  $class_form_eleve = 'hide'; $class_form_periode = 'show'; $class_form_option = 'show';
	$select_eleves = '<option value="'.$_SESSION['USER_ID'].'" selected>'.html($_SESSION['USER_NOM'].' '.$_SESSION['USER_PRENOM']).'</option>';
	$check_option_lien = ' checked';
}
$tab_periodes = DB_STRUCTURE_OPT_periodes_etabl();

$select_groupe  = afficher_select($tab_groupes        , $select_nom='f_groupe'  , $option_first=$of_g , $selection=$sel_g                 , $optgroup='oui'); // optgroup à oui y compris pour les élèves (formulaire invisible) car recherche du type de groupe dans le js
$select_periode = afficher_select($tab_periodes       , $select_nom='f_periode' , $option_first='val' , $selection=false                  , $optgroup='non');
$select_couleur = afficher_select($tab_select_couleur , $select_nom='f_couleur' , $option_first='non' , $selection=$tab_cookie['couleur'] , $optgroup='non');
$select_legende = afficher_select($tab_select_legende , $select_nom='f_legende' , $option_first='non' , $selection=$tab_cookie['legende'] , $optgroup='non');

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
		$tab_id_classe_groupe[] = $tab_groupe_infos['valeur'];
	}
	$tab_memo_groupes = array();
	$DB_TAB = DB_STRUCTURE_lister_jointure_groupe_periode($listing_groupe_id = implode(',',$tab_id_classe_groupe));
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

// Fabrication du tableau javascript "tab_groupe_niveau" pour les jointures groupes/niveau
$tab_groupe_niveau_js  = 'var tab_groupe_niveau = new Array();';
if(is_array($tab_groupes))
{
	$DB_TAB = DB_STRUCTURE_recuperer_niveau_groupes($listing_groupe_id); // $listing_groupe_id a été obtenu 15 lignes plus haut
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_groupe_niveau_js  .= 'tab_groupe_niveau['.$DB_ROW['groupe_id'].'] = new Array('.$DB_ROW['niveau_id'].',"'.html($DB_ROW['niveau_nom']).'");';
	}
}
?>

<script type="text/javascript">
	var date_mysql="<?php echo date("Y-m-d") ?>";
	<?php echo $tab_groupe_periode_js ?> 
	<?php echo $tab_groupe_niveau_js ?> 
</script>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__synthese_multimatiere">DOC : Synthèse pluridisciplinaire.</a></span></div>
<div class="astuce">Un administrateur doit effectuer certains réglages préliminaires (<a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_releves_bilans">DOC</a>).</div>
<?php
$nb_inconnu = DB_STRUCTURE_compter_modes_synthese_inconnu();
$s = ($nb_inconnu>1) ? 's' : '' ;
echo ($nb_inconnu) ? '<label class="alerte">Il y a '.$nb_inconnu.' référentiel'.$s.' dont le format de synthèse est inconnu (donc non pris en compte).</label>' : '<label class="valide">Tous les référentiels ont un format de synthèse prédéfini.</label>' ;
?>

<hr />

<form id="form_select" action=""><fieldset>
	<p class="<?php echo $class_form_eleve ?>">
		<label class="tab" for="f_groupe">Élève(s) :</label><?php echo $select_groupe ?><input type="hidden" id="f_groupe_nom" name="f_groupe_nom" value="" /><label id="ajax_maj">&nbsp;</label><br />
		<span class="tab"></span><select id="f_eleve" name="f_eleve[]" multiple size="9"><?php echo $select_eleves ?></select></select><input type="hidden" id="eleves" name="eleves" value="" />
	</p>
	<p id="zone_periodes" class="<?php echo $class_form_periode ?>">
		<label class="tab" for="f_periode"><img alt="" src="./_img/bulle_aide.png" title="Les items pris en compte sont ceux qui sont évalués<br />au moins une fois sur cette période." /> Période :</label><?php echo $select_periode ?>
		<span id="dates_perso" class="show">
			du <input id="f_date_debut" name="f_date_debut" size="9" type="text" value="<?php echo $date_debut ?>" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q>
			au <input id="f_date_fin" name="f_date_fin" size="9" type="text" value="<?php echo $date_fin ?>" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q>
		</span><br />
		<span class="radio"><img alt="" src="./_img/bulle_aide.png" title="Le bilan peut être établi uniquement sur la période considérée<br />ou en tenant compte d'évaluations antérieures des items concernés." /> Prise en compte des évaluations antérieures :</span><label for="f_retro_oui"><input type="radio" id="f_retro_oui" name="f_retroactif" value="oui" checked /> oui</label>&nbsp;&nbsp;&nbsp;&nbsp;<label for="f_retro_non"><input type="radio" id="f_retro_non" name="f_retroactif" value="non" /> non</label>
	</p>
	<div id="zone_options" class="<?php echo $class_form_option ?>">
		<div class="toggle">
			<span class="tab"></span><a href="#" class="puce_plus toggle">Afficher plus d'options</a>
		</div>
		<div class="toggle hide">
			<span class="tab"></span><a href="#" class="puce_moins toggle">Afficher moins d'options</a><br />
			<label class="tab" for="f_opt_html"><img alt="" src="./_img/bulle_aide.png" title="Pour le format html, le détail des items peut être affiché." /> Indications :</label><label for="f_coef"><input type="checkbox" id="f_coef" name="f_coef" value="1" /> Coefficients</label>&nbsp;&nbsp;&nbsp;<label for="f_socle"><input type="checkbox" id="f_socle" name="f_socle" value="1" checked /> Socle</label>&nbsp;&nbsp;&nbsp;<label for="f_lien"><input type="checkbox" id="f_lien" name="f_lien" value="1"<?php echo $check_option_lien ?> /> Liens de remédiation</label><br />
			<label class="tab" for="f_restriction">Restrictions :</label><input type="checkbox" id="f_restriction_socle" name="f_restriction_socle" value="1" /> <label for="f_restriction_socle">Utiliser uniquement les items liés du socle</label><br />
			<label class="tab"></label><input type="checkbox" id="f_restriction_niveau" name="f_restriction_niveau" value="1" /> <label for="f_restriction_niveau">Utiliser uniquement les items du niveau <em id="niveau_nom"></em><input type="hidden" id="f_niveau" name="f_niveau" value="" /></label><br />
			<label class="tab" for="f_impression"><img alt="" src="./_img/bulle_aide.png" title="Pour le format pdf." /> Impression :</label><?php echo $select_couleur ?> <?php echo $select_legende ?>
		</div>
	</div>
	<p>
		<span class="tab"></span><button id="bouton_valider" type="submit"><img alt="" src="./_img/bouton/generer.png" /> Générer.</button><label id="ajax_msg">&nbsp;</label>
	</p>
</fieldset></form>

<hr />

<div id="bilan">
</div>

