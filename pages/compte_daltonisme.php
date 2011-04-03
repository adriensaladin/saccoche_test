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
$TITRE = "Daltonisme";
$VERSION_JS_FILE += 0;

$checked_normal = $_SESSION['USER_DALTONISME'] ? '' : ' checked="checked"' ;
$checked_dalton = $_SESSION['USER_DALTONISME'] ? ' checked="checked"' : '' ;

// codes de notation
$td_normal = '<td class="nu">&nbsp;</td>';
$td_dalton = '<td class="nu">&nbsp;</td>';
$tab_note = array('RR','R','V','VV');
foreach($tab_note as $note)
{
	$td_normal .= '<td>note '.html($_SESSION['NOTE_TEXTE'][$note]).'<br /><img alt="" src="./_img/note/'.$_SESSION['NOTE_IMAGE_STYLE'].'/h/'.$note.'.gif" /></td>';
	$td_dalton .= '<td>note '.html($_SESSION['NOTE_TEXTE'][$note]).'<br /><img alt="" src="./_img/note/Dalton/h/'.$note.'.gif" /></td>';
}

// couleurs des états d'acquisition
$td_normal .= '<td class="nu">&nbsp;</td>';
$td_dalton .= '<td class="nu">&nbsp;</td>';
$tab_acquis = array('NA'=>'#909090','VA'=>'#BEBEBE','A'=>'#EAEAEA');
foreach($tab_acquis as $acquis => $style)
{
	$td_normal .= '<td style="background-color:'.$_SESSION['CSS_BACKGROUND-COLOR'][$acquis].'">acquisition<br />'.html($_SESSION['ACQUIS_TEXTE'][$acquis]).'</td>';
	$td_dalton .= '<td style="background-color:'.$style.'">acquisition<br />'.html($_SESSION['ACQUIS_TEXTE'][$acquis]).'</td>';
}

// couleurs des états de validation
$td_normal .= '<td class="nu">&nbsp;</td>';
$td_dalton .= '<td class="nu">&nbsp;</td>';
$tab_valid = array( 'en attente'=>array('normal'=>'#BBBBFF','dalton'=>'#BEBEBE') , 'négative'=>array('normal'=>'#FF9999','dalton'=>'#909090') , 'positive'=>array('normal'=>'#99FF99','dalton'=>'#EAEAEA') );
foreach($tab_valid as $etat => $tab_style)
{
	$td_normal .= '<td style="background-color:'.$tab_style['normal'].'">validation<br />'.$etat.'</td>';
	$td_dalton .= '<td style="background-color:'.$tab_style['dalton'].'">validation<br />'.$etat.'</td>';
}
?>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=environnement_generalites__daltonisme">DOC : Daltonisme</a></span></div>

<hr />

<form id="form_notes" action="">
	<table class="simulation">
		<thead>
			<tr>
				<th class="nu"></th>
				<th class="nu"></th>
				<th colspan="4">Notes aux évaluations</th>
				<th class="nu"></th>
				<th colspan="3">Degrés d'acquisitions</th>
				<th class="nu"></th>
				<th colspan="3">États de validations</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td colspan="14" class="nu" style="font-size:50%"></td>
			</tr>
			<tr>
				<th><label for="note_normal">Conventions dans l'établissement</label><br /><input type="radio" id="note_normal" name="daltonisme" value="0"<?php echo $checked_normal ?> /></th>
				<?php echo $td_normal ?>
			</tr>
			<tr>
				<td colspan="14" class="nu" style="font-size:50%"></td>
			</tr>
			<tr>
				<th><label for="note_dalton">Conventions en remplacement</label><br /><input type="radio" id="note_dalton" name="daltonisme" value="1"<?php echo $checked_dalton ?> /></th>
				<?php echo $td_dalton ?>
			</tr>
		</tbody>
	</table>
	<p />
	<fieldset><span class="tab"></span><button id="bouton_valider" type="submit"><img alt="" src="./_img/bouton/parametre.png" /> Enregistrer ce choix.</button><label id="ajax_msg">&nbsp;</label></fieldset>
</form>
