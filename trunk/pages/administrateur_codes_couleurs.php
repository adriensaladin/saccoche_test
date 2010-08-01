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

// Liste des jeux de codes de couleur
$lignes = '';
$dossier = './_img/note/';
$tab_files = scandir($dossier);
foreach($tab_files as $file)
{
	if( (is_dir($dossier.$file)) && ($file!='.') && ($file!='..') )
	{
		$fichier = $dossier.$file.'/lettres_nb.txt';
		if(is_file($fichier))
		{
			list($rr,$r,$v,$vv) = explode(',',file_get_contents($fichier));
			$checked = ($file==$_SESSION['CSS_NOTE_STYLE']) ? ' checked="checked"' : '' ;
			$lignes .= '<tr>';
			$lignes .= 	'<td>'.$file.'<br /><input type="radio" id="dossier_'.$file.'" name="jeu_codes" value="'.$file.'"'.$checked.' /></td>';
			$lignes .= 	'<td><img alt="'.$rr.'" src="'.$dossier.$file.'/RR.gif" /><br />'.$rr.'</td>';
			$lignes .= 	'<td><img alt="'.$r.'" src="'.$dossier.$file.'/R.gif" /><br />'.$r.'</td>';
			$lignes .= 	'<td><img alt="'.$v.'" src="'.$dossier.$file.'/V.gif" /><br />'.$v.'</td>';
			$lignes .= 	'<td><img alt="'.$vv.'" src="'.$dossier.$file.'/VV.gif" /><br />'.$vv.'</td>';
			$lignes .= '</tr>';
		}
	}
}

// Couleurs d'initialisation
$defaut_r = '#ff9999';
$defaut_o = '#ffdd33';
$defaut_v = '#99ff99';
$color_r = $_SESSION['CSS_BACKGROUND-COLOR']['NA'];
$color_o = $_SESSION['CSS_BACKGROUND-COLOR']['VA'];
$color_v = $_SESSION['CSS_BACKGROUND-COLOR']['A'];
?>


<div class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_codes_couleurs">DOC : Codes de notation et couleurs</a></div>

<hr />

<form id="form" action="">

	<hr />

	<h2>Jeu de codes de couleur</h2>

	<table class="simulation"><tbody>
		<?php echo $lignes ?>
	</tbody></table>

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
