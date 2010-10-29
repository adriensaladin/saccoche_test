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
$TITRE = "Codes de notation et couleurs";
$VERSION_JS_FILE += 1;

// Liste des jeux de codes de couleur

require_once('./_inc/tableau_notes_txt.php');

$lignes = '';
$dossier = './_img/note/';
$tab_notes_txt_js = 'var tab_notes_txt = new Array();';

foreach($tab_notes_txt as $note_nom => $tab_note_texte)
{
	if(is_dir($dossier.$note_nom))
	{
		$checked = ($note_nom==$_SESSION['NOTE_IMAGE_STYLE']) ? ' checked="checked"' : '' ;
		$listing_notes_texte = implode('/',$tab_note_texte);
		$lignes .= '<tr>';
		$lignes .= 	'<td>'.$note_nom.'<br /><input type="radio" id="dossier_'.$note_nom.'" name="image_style" value="'.$note_nom.'"'.$checked.' lang="'.$listing_notes_texte.'" /></td>';
		$lignes .= 	'<td><img alt="'.$tab_note_texte['RR'].'" src="'.$dossier.$note_nom.'/h/RR.gif" /><br />'.$tab_note_texte['RR'].'</td>';
		$lignes .= 	'<td><img alt="'.$tab_note_texte['R'].'" src="'.$dossier.$note_nom.'/h/R.gif" /><br />'.$tab_note_texte['R'].'</td>';
		$lignes .= 	'<td><img alt="'.$tab_note_texte['V'].'" src="'.$dossier.$note_nom.'/h/V.gif" /><br />'.$tab_note_texte['V'].'</td>';
		$lignes .= 	'<td><img alt="'.$tab_note_texte['VV'].'" src="'.$dossier.$note_nom.'/h/VV.gif" /><br />'.$tab_note_texte['VV'].'</td>';
		$lignes .= '</tr>';
		$tab_notes_txt_js .= 'tab_notes_txt["'.html($note_nom).'"] = new Array();';
		$tab_notes_txt_js .= 'tab_notes_txt["'.$note_nom.'"]["RR"]="'.$tab_note_texte['RR'].'";';
		$tab_notes_txt_js .= 'tab_notes_txt["'.$note_nom.'"]["R"]="'.$tab_note_texte['R'].'";';
		$tab_notes_txt_js .= 'tab_notes_txt["'.$note_nom.'"]["V"]="'.$tab_note_texte['V'].'";';
		$tab_notes_txt_js .= 'tab_notes_txt["'.$note_nom.'"]["VV"]="'.$tab_note_texte['VV'].'";';
	}
}

// Équivalents texte
$texte_rr = $_SESSION['NOTE_TEXTE']['RR'];
$texte_r  = $_SESSION['NOTE_TEXTE']['R'];
$texte_v  = $_SESSION['NOTE_TEXTE']['V'];
$texte_vv = $_SESSION['NOTE_TEXTE']['VV'];

// Couleurs d'initialisation
$defaut_r = '#ff9999';
$defaut_o = '#ffdd33';
$defaut_v = '#99ff99';
$color_r = $_SESSION['CSS_BACKGROUND-COLOR']['NA'];
$color_o = $_SESSION['CSS_BACKGROUND-COLOR']['VA'];
$color_v = $_SESSION['CSS_BACKGROUND-COLOR']['A'];
?>

<script type="text/javascript">
	<?php echo $tab_notes_txt_js ?>
</script>

<div class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_codes_couleurs">DOC : Codes de notation et couleurs</a></div>

<hr />

<form id="form" action="">

	<h2>Jeu de symboles colorés</h2>

	<table class="simulation"><tbody>
		<?php echo $lignes ?>
	</tbody></table>

	<h2>Équivalents texte (modifiables)</h2>

	<div id="equiv_txt">
		<input type="text" size="2" maxlength="3" id="texte_RR" name="texte_RR" value="<?php echo $texte_rr ?>" class="hc" />
		<input type="text" size="2" maxlength="3" id="texte_R" name="texte_R" value="<?php echo $texte_r ?>" class="hc" />
		<input type="text" size="2" maxlength="3" id="texte_V" name="texte_V" value="<?php echo $texte_v ?>" class="hc" />
		<input type="text" size="2" maxlength="3" id="texte_VV" name="texte_VV" value="<?php echo $texte_vv ?>" class="hc" />
	</div>

	<hr />

	<h2>Couleurs de fond</h2>

	<!-- Pas mis dans le tableau, sinon colorpicker bugue avec IE -->
	<div class="colorpicker r">
		<p><b>Non Acquis</b></p>
		<p><input type="text" size="8" id="color_NA" name="color_NA" value="<?php echo $color_r ?>" style="background-color:<?php echo $color_r ?>" /></p>
		<p><label>&nbsp;</label></p>
		<p><button type="button" name="color_NA" value="<?php echo $color_r ?>"><img alt="" src="./_img/bouton/colorer.png" /> Couleur de l'établissement.</button></p>
		<p><button type="button" name="color_NA" value="<?php echo $defaut_r ?>"><img alt="" src="./_img/bouton/colorer.png" /> Couleur par défaut.</button></p>
	</div>
	<div class="colorpicker o">
		<p><b>Partiellement Acquis</b></p>
		<p><input type="text" size="8" id="color_VA" name="color_VA" value="<?php echo $color_o ?>" style="background-color:<?php echo $color_o ?>" /></p>
		<p><label>&nbsp;</label></p>
		<p><button type="button" name="color_VA" value="<?php echo $color_o ?>"><img alt="" src="./_img/bouton/colorer.png" /> Couleur de l'établissement.</button></p>
		<p><button type="button" name="color_VA" value="<?php echo $defaut_o ?>"><img alt="" src="./_img/bouton/colorer.png" /> Couleur par défaut.</button></p>
	</div>
	<div class="colorpicker v">
		<p><b>Acquis</b></p>
		<p><input type="text" size="8" id="color_A" name="color_A" value="<?php echo $color_v ?>" style="background-color:<?php echo $color_v ?>" /></p>
		<p><label>&nbsp;</label></p>
		<p><button type="button" name="color_A" value="<?php echo $color_v ?>"><img alt="" src="./_img/bouton/colorer.png" /> Couleur de l'établissement.</button></p>
		<p><button type="button" name="color_A" value="<?php echo $defaut_v ?>"><img alt="" src="./_img/bouton/colorer.png" /> Couleur par défaut.</button></p>
	</div>
	<div id="colorpicker" class="hide"></div>
	<div style="clear:both"></div>

	<hr />

	<fieldset>
		<span class="tab"></span><button id="bouton_valider" type="submit"><img alt="" src="./_img/bouton/parametre.png" /> Enregistrer ces choix.</button><label id="ajax_msg">&nbsp;</label>
	</fieldset>

</form>
