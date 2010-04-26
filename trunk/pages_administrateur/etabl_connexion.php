<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 * 
 * ****************************************************************************************************
 * SACoche <http://competences.sesamath.net> - Suivi d'Acquisitions de Compétences
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
$TITRE = "Mode d'identification";
?>

<p class="hc"><span class="manuel"><a class="pop_up" href="./aide.php?fichier=gestion_mode_identification">DOC : Gestion du mode d'identification</a></span></p>

<hr />

<form action=""><fieldset>
	<?php
	require_once('./_inc/tableau_sso.php');	// Charge $tab_sso['nom'] = array('txt'=>'...' , 'doc'=>'...');
	$i_id = 0;	// Pour donner des ids aux checkbox et radio
	foreach($tab_sso as $value => $tab_infos)
	{
		$i_id++;
		$checked = ($value==$_SESSION['SSO']) ? ' checked="checked"' : '' ;
		$debut_phrase = ($value!='normal') ? 'Connexion SSO en lien avec l\'' : '' ;
		$documentation = ($tab_infos['doc']) ? ' <span class="manuel"><a class="pop_up" href="./aide.php?fichier=integration_ENT_'.$tab_infos['doc'].'">DOC : Intégration à cet ENT.</a></span>' : '' ;
		echo'<label for="input_'.$i_id.'"><input type="radio" id="input_'.$i_id.'" name="mode_connexion" value="'.$value.'"'.$checked.' /> '.$debut_phrase.$tab_infos['txt'].'</label>'.$documentation.'<br />'."\r\n";
	}
	?>
	<span class="tab"></span><input id="f_submit" type="button" value="Valider." /><label id="ajax_msg">&nbsp;</label>
	<p><span class="astuce">Pour importer les identifiants de l'ENT, utiliser ensuite la page "<a href="./index.php?dossier=administrateur&amp;fichier=fichier&amp;section=import-id-ent">Importer identifiant ENT</a>".</span></p>
</fieldset></form>
