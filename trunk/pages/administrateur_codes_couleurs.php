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
$TITRE = "Notation : codes, couleurs, légendes";
$VERSION_JS_FILE += 2;

// Évaluation : symboles colorés

require_once('./_inc/tableau_notes_txt.php');

$dossier = './_img/note/';
$tab_notes_txt_js = 'var tab_notes_txt = new Array();';

$simulation_lignes = array('','','','','','');
foreach($tab_notes_txt as $note_nom => $tab_note_texte)
{
	if(is_dir($dossier.$note_nom))
	{
		$checked = ($note_nom==$_SESSION['NOTE_IMAGE_STYLE']) ? ' checked="checked"' : '' ;
		$listing_notes_texte = implode('/',$tab_note_texte);
		$simulation_lignes[0] .= 	'<td style="width:5em"><label for="dossier_'.$note_nom.'">'.$note_nom.'</label><br /><input type="radio" id="dossier_'.$note_nom.'" name="note_image_style" value="'.$note_nom.'"'.$checked.' lang="'.$listing_notes_texte.'" /></td>';
		$simulation_lignes[1] .= 	'<td><img alt="'.$tab_note_texte['RR'].'" src="'.$dossier.$note_nom.'/h/RR.gif" /><br />'.$tab_note_texte['RR'].'</td>';
		$simulation_lignes[2] .= 	'<td><img alt="'.$tab_note_texte['R'].'" src="'.$dossier.$note_nom.'/h/R.gif" /><br />'.$tab_note_texte['R'].'</td>';
		$simulation_lignes[3] .= 	'<td><img alt="'.$tab_note_texte['V'].'" src="'.$dossier.$note_nom.'/h/V.gif" /><br />'.$tab_note_texte['V'].'</td>';
		$simulation_lignes[4] .= 	'<td><img alt="'.$tab_note_texte['VV'].'" src="'.$dossier.$note_nom.'/h/VV.gif" /><br />'.$tab_note_texte['VV'].'</td>';
		$tab_notes_txt_js .= 'tab_notes_txt["'.html($note_nom).'"] = new Array();';
		$tab_notes_txt_js .= 'tab_notes_txt["'.$note_nom.'"]["RR"]="'.$tab_note_texte['RR'].'";';
		$tab_notes_txt_js .= 'tab_notes_txt["'.$note_nom.'"]["R"]="'.$tab_note_texte['R'].'";';
		$tab_notes_txt_js .= 'tab_notes_txt["'.$note_nom.'"]["V"]="'.$tab_note_texte['V'].'";';
		$tab_notes_txt_js .= 'tab_notes_txt["'.$note_nom.'"]["VV"]="'.$tab_note_texte['VV'].'";';
	}
}

// Évaluation : équivalents textes & légende

$note_equiv_div = '';
$tab_note = array('RR','R','V','VV');
foreach($tab_note as $note)
{
	$note_equiv_div .= '<div class="ti"><input type="text" class="hc" size="2" maxlength="3" id="note_texte_'.$note.'" name="note_texte_'.$note.'" value="'.html($_SESSION['NOTE_TEXTE'][$note]).'" /> <input type="text" size="30" maxlength="40" id="note_legende_'.$note.'" name="note_legende_'.$note.'" value="'.html($_SESSION['NOTE_LEGENDE'][$note]).'" /></div>';
}

// États d'acquisitions calculés : couleurs de fond, équivalents textes, légende

$tab_acquis = array('NA'=>'r','VA'=>'o','A'=>'v');
$tab_defaut = array('NA'=>'#ff9999','VA'=>'#ffdd33','A'=>'#99ff99');

$acquis_box = '';
foreach($tab_acquis as $acquis => $class)
{
	$acquis_box .= '<div class="colorpicker '.$class.'">';
	$acquis_box .= '<p><input type="text" class="hc" size="2" maxlength="3" id="acquis_texte_'.$note.'" name="acquis_texte_'.$acquis.'" value="'.html($_SESSION['ACQUIS_TEXTE'][$acquis]).'" /><br /><input type="text" class="hc" size="25" maxlength="40" id="acquis_legende_'.$acquis.'" name="acquis_legende_'.$acquis.'" value="'.html($_SESSION['ACQUIS_LEGENDE'][$acquis]).'" /></p>';
	$acquis_box .= '<div><button type="button" name="color_'.$acquis.'" value="'.$_SESSION['BACKGROUND_'.$acquis].'"><img alt="" src="./_img/bouton/colorer.png" /> Couleur de l\'établissement.</button></div>';
	$acquis_box .= '<div><button type="button" name="color_'.$acquis.'" value="'.$tab_defaut[$acquis].'"><img alt="" src="./_img/bouton/colorer.png" /> Couleur par défaut.</button></div>';
	$acquis_box .= '<p><input type="text" class="stretch" size="8" id="acquis_color_'.$acquis.'" name="acquis_color_'.$acquis.'" value="'.$_SESSION['BACKGROUND_'.$acquis].'" style="background-color:'.$_SESSION['BACKGROUND_'.$acquis].'" /><br /></p>';
	$acquis_box .= '</div>';
}

?>

<script type="text/javascript">
	<?php echo $tab_notes_txt_js ?>
</script>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_codes_couleurs">DOC : Notation : codes, couleurs, légendes</a></span></div>

<hr />

<form id="form_notes" action="">
	<h2>Notes aux évaluations : symboles colorés, équivalents textes, légende</h2>
	<table class="simulation"><tbody><tr><?php echo implode('</tr><tr>',$simulation_lignes) ?></tr></tbody></table>
	<label id="ajax_msg_note_symbole"></label>
	<p />
	<div id="note_equiv_div">
		<?php echo $note_equiv_div ?>
	</div>
	<p />
	<fieldset><span class="tab"></span><input type="hidden" id="objet" name="objet" value="notes" /><button id="bouton_valider_notes" type="submit"><img alt="" src="./_img/bouton/parametre.png" /> Enregistrer ces choix.</button><label id="ajax_msg_notes">&nbsp;</label></fieldset>
</form>

	<hr />

<form id="form_acquis" action="">
	<h2>Degrés d'acquisitions calculés : couleurs de fond, équivalents textes, légende</h2>
	<!-- Pas mis dans un tableau, sinon colorpicker bugue avec IE -->
	<?php echo $acquis_box; ?>
	<div id="colorpicker" class="hide"></div>
	<div style="clear:both"></div>
	<p />
	<fieldset><span class="tab"></span><input type="hidden" id="objet" name="objet" value="acquis" /><button id="bouton_valider_acquis" type="submit"><img alt="" src="./_img/bouton/parametre.png" /> Enregistrer ces choix.</button><label id="ajax_msg_acquis">&nbsp;</label></fieldset>
</form>

	<hr />
