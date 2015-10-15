<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009-2015
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
$TITRE = html(Lang::_("Notation : codes, couleurs, légendes"));

// Évaluation : symboles colorés, équivalents textes, légendes

$tab_note = array(
  'RR' => 'très mauvais',
  'R'  => 'assez mauvais',
  'V'  => 'assez bon',
  'VV' => 'très bon',
);

$note_div = '';
foreach($tab_note as $note => $label)
{
  $input_image   = '<img alt="'.$note.'" src="./_img/note/choix/h/'.$_SESSION['NOTE_IMAGE'][$note].'.gif" />';
  $input_hidden  = '<input type="hidden" id="note_image_'.$note.'" name="note_image_'.$note.'" value="'.html($_SESSION['NOTE_IMAGE'][$note]).'" />';
  $input_texte   = '<input type="text" class="hc" size="2" maxlength="3" id="note_texte_'.$note.'" name="note_texte_'.$note.'" value="'.html($_SESSION['NOTE_TEXTE'][$note]).'" />';
  $input_legende = '<input type="text" size="30" maxlength="40" id="note_legende_'.$note.'" name="note_legende_'.$note.'" value="'.html($_SESSION['NOTE_LEGENDE'][$note]).'" />';
  $note_div .= '<div id="div_'.$note.'"><label class="tab">'.$label.' :</label>'.$input_image.$input_hidden.'<q class="modifier" title="Modifier ce choix."></q>'.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$input_texte.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$input_legende.'</div>';
}

// États d'acquisitions calculés : couleurs de fond, équivalents textes, légendes

$tab_acquis = array( 'NA'=>'r'       , 'VA'=>'o'       , 'A'=>'v' );
$tab_defaut = array( 'NA'=>'#ff9999' , 'VA'=>'#ffdd33' , 'A'=>'#99ff99' );

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

// Listing des symboles colorés

$chemin_dossier = CHEMIN_DOSSIER_IMG.'note'.DS.'choix'.DS.'h'.DS;
$tab_fichiers = FileSystem::lister_contenu_dossier($chemin_dossier);
$tab_liste = array();

foreach($tab_fichiers as $fichier_nom)
{
  list( $fichier_partie_1 , $fichier_partie_2 ) = explode( '_' , $fichier_nom , 2 );
  $image_nom = substr($fichier_nom,0,-4);
  if(!isset($tab_liste[$fichier_partie_1]))
  {
    $tab_liste[$fichier_partie_1] = '';
  }
  $tab_liste[$fichier_partie_1] .= '<div class="p"><a href="#" id="a_'.$image_nom.'"><img alt="'.$image_nom.'" src="./_img/note/choix/h/'.$fichier_nom.'" /></a></div>';
}

?>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_codes_couleurs">DOC : Notation : codes, couleurs, légendes</a></span></div>

<hr />

<form action="#" method="post" id="form_notes">
  <h2>Notes aux évaluations : symboles colorés, équivalents textes, légendes</h2>
  <?php echo $note_div ?>
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

<form action="#" method="post" id="zone_notes" class="hide">
  <h3>Codes de couleur</h3>
  <div>Cliquer sur un symbole coloré ou <a id="annuler_note" href="#">[ annuler le choix ]</a>.</div>
  <div class="note_liste"><?php echo implode('</div>'.NL.'<div class="note_liste">',$tab_liste) ?></div>
</form>
