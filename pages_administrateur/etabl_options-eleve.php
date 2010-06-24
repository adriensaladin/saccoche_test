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
$TITRE = "Options de l'environnement élève";
?>

<p class="hc"><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_options_eleve">DOC : Gestion des options de l'environnement élève</a></span></p>

<hr />

<form action=""><fieldset>
	<?php
	$tab_options = array( 'ms'=>'Bilan sur une matière : ligne avec la moyenne des scores d\'acquisitions.' , 'pv'=>'Bilan sur une matière : ligne avec le pourcentage d\'items validés.' , 'as'=>'Menu : accès à l\'attestation de socle.' );
	$tab_check = explode(',',$_SESSION['ELEVE_OPTIONS']);
	$i_id = 0;	// Pour donner des ids aux checkbox et radio
	foreach($tab_options as $option_code => $option_txt)
	{
		$i_id++;
		$checked = (in_array($option_code,$tab_check)) ? ' checked="checked"' : '' ;
		echo'<label for="input_'.$i_id.'"><input type="checkbox" id="input_'.$i_id.'" name="eleve_options" value="'.$option_code.'"'.$checked.' /> '.$option_txt.'</label><br />'."\r\n";
	}
	?>
	<span class="tab"></span><input id="f_submit" type="button" value="Valider." /><label id="ajax_msg">&nbsp;</label>
</fieldset></form>
