<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010-2014
 * 
 * ****************************************************************************************************
 * SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath <http://www.sesamath.net> - Tous droits réservés.
 * Logiciel placé sous la licence libre Affero GPL 3 <https://www.gnu.org/licenses/agpl-3.0.html>.
 * ****************************************************************************************************
 * 
 * Ce fichier est une partie de SACoche.
 * 
 * SACoche est un logiciel libre ; vous pouvez le redistribuer ou le modifier suivant les termes 
 * de la “GNU Affero General Public License” telle que publiée par la Free Software Foundation :
 * soit la version 3 de cette licence, soit (à votre gré) toute version ultérieure.
 * 
 * SACoche est distribué dans l’espoir qu’il vous sera utile, mais SANS AUCUNE GARANTIE :
 * sans même la garantie implicite de COMMERCIALISABILITÉ ni d’ADÉQUATION À UN OBJECTIF PARTICULIER.
 * Consultez la Licence Publique Générale GNU Affero pour plus de détails.
 * 
 * Vous devriez avoir reçu une copie de la Licence Publique Générale GNU Affero avec SACoche ;
 * si ce n’est pas le cas, consultez : <http://www.gnu.org/licenses/>.
 * 
 */

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
$TITRE = "Notation : codes, couleurs, légendes";

// Évaluation : symboles colorés

require(CHEMIN_DOSSIER_INCLUDE.'tableau_notes_txt.php');

$dossier = './_img/note/';
Layout::add( 'js_inline_before' , 'var tab_notes_txt = new Array();' );

$simulation_lignes = array('','','','','');
foreach($tab_notes_info as $note_code => $tab_note_info)
{
  if(is_dir($dossier.$note_code))
  {
    $checked = ($note_code==$_SESSION['NOTE_IMAGE_STYLE']) ? ' checked' : '' ;
    $simulation_lignes[0] .=   '<td style="width:5em"><label for="dossier_'.$note_code.'">'.html($tab_note_info['nom']).'</label><br /><input type="radio" id="dossier_'.$note_code.'" name="note_image_style" value="'.$note_code.'"'.$checked.' /></td>';
    $simulation_lignes[1] .=   '<td><img alt="'.$tab_note_info['RR'].'" src="'.$dossier.$note_code.'/h/RR.gif" /><br />'.$tab_note_info['RR'].'</td>';
    $simulation_lignes[2] .=   '<td><img alt="'.$tab_note_info['R' ].'" src="'.$dossier.$note_code.'/h/R.gif"  /><br />'.$tab_note_info['R' ].'</td>';
    $simulation_lignes[3] .=   '<td><img alt="'.$tab_note_info['V' ].'" src="'.$dossier.$note_code.'/h/V.gif"  /><br />'.$tab_note_info['V' ].'</td>';
    $simulation_lignes[4] .=   '<td><img alt="'.$tab_note_info['VV'].'" src="'.$dossier.$note_code.'/h/VV.gif" /><br />'.$tab_note_info['VV'].'</td>';
    Layout::add( 'js_inline_before' , 'tab_notes_txt["'.html($note_code).'"] = new Array();' );
    Layout::add( 'js_inline_before' , 'tab_notes_txt["'.$note_code.'"]["RR"]="'.$tab_note_info['RR'].'";' );
    Layout::add( 'js_inline_before' , 'tab_notes_txt["'.$note_code.'"]["R"] ="'.$tab_note_info['R'].'";' );
    Layout::add( 'js_inline_before' , 'tab_notes_txt["'.$note_code.'"]["V"] ="'.$tab_note_info['V'].'";' );
    Layout::add( 'js_inline_before' , 'tab_notes_txt["'.$note_code.'"]["VV"]="'.$tab_note_info['VV'].'";' );
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
  $acquis_box .= '<p><input type="text" class="hc" size="2" maxlength="3" id="acquis_texte_'.$acquis.'" name="acquis_texte_'.$acquis.'" value="'.html($_SESSION['ACQUIS_TEXTE'][$acquis]).'" /><br /><input type="text" class="hc" size="25" maxlength="40" id="acquis_legende_'.$acquis.'" name="acquis_legende_'.$acquis.'" value="'.html($_SESSION['ACQUIS_LEGENDE'][$acquis]).'" /></p>';
  $acquis_box .= '<div><button type="button" name="color_'.$acquis.'" value="'.$_SESSION['BACKGROUND_'.$acquis].'" class="colorer">Couleur de l\'établissement.</button></div>';
  $acquis_box .= '<div><button type="button" name="color_'.$acquis.'" value="'.$tab_defaut[$acquis].'" class="colorer">Couleur par défaut.</button></div>';
  $acquis_box .= '<p><input type="text" class="stretch" size="8" id="acquis_color_'.$acquis.'" name="acquis_color_'.$acquis.'" value="'.$_SESSION['BACKGROUND_'.$acquis].'" style="background-color:'.$_SESSION['BACKGROUND_'.$acquis].'" /><br /></p>';
  $acquis_box .= '</div>';
}

?>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_codes_couleurs">DOC : Notation : codes, couleurs, légendes</a></span></div>

<hr />

<form action="#" method="post" id="form_notes">
  <h2>Notes aux évaluations : symboles colorés, équivalents textes, légende</h2>
  <table class="simulation p"><tbody><tr><?php echo implode('</tr><tr>',$simulation_lignes) ?></tr></tbody></table>
  <label id="ajax_msg_note_symbole"></label>
  <?php echo $note_equiv_div ?>
  <p><span class="tab"></span><input type="hidden" name="objet" value="notes" /><button id="bouton_valider_notes" type="submit" class="parametre">Enregistrer ces choix.</button><label id="ajax_msg_notes">&nbsp;</label></p>
</form>

  <hr />

<form action="#" method="post" id="form_acquis">
  <h2>Degrés d'acquisitions calculés : couleurs de fond, équivalents textes, légende</h2>
  <?php /* Pas mis dans un tableau, sinon colorpicker bugue avec IE */ ?>
  <?php echo $acquis_box; ?>
  <div id="colorpicker" class="hide"></div>
  <div style="clear:both"></div>
  <p><span class="tab"></span><input type="hidden" name="objet" value="acquis" /><button id="bouton_valider_acquis" type="submit" class="parametre">Enregistrer ces choix.</button><label id="ajax_msg_acquis">&nbsp;</label></p>
</form>

  <hr />
