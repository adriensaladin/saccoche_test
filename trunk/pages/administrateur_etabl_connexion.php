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
$TITRE = "Mode d'identification";
$VERSION_JS_FILE += 1;

require_once('./_inc/tableau_sso.php');

// Liste des possibilités
$choix = '';
$i_id = 0;	// Pour donner des ids aux checkbox et radio
foreach($tab_connexion_mode as $connexion_mode => $mode_texte)
{
	foreach($tab_connexion_info[$connexion_mode] as $connexion_nom => $tab_info)
	{
		$i_id++;
		$checked = ( ($connexion_mode==$_SESSION['CONNEXION_MODE']) && ($connexion_nom==$_SESSION['CONNEXION_NOM']) ) ? ' checked="checked"' : '' ;
		$choix .= '<span class="tab"><label for="input_'.$i_id.'"><input type="radio" id="input_'.$i_id.'" name="connexion_mode_nom" value="'.$connexion_mode.'|'.$connexion_nom.'"'.$checked.' /> '.$mode_texte.' &rarr; '.$tab_info['txt'].'</label></span><br />'."\r\n";
	}
}

// Retenir en variable javascript les paramètres des serveurs CAS
$tab_cas_js = '';
foreach($tab_connexion_info['cas'] as $connexion_nom => $tab_info)
{
	$tab_cas_js .= 'tab_cas["'.$connexion_nom.'"]="'.html($tab_info['serveur_host'].']¤['.$tab_info['serveur_port'].']¤['.$tab_info['serveur_root']).'";';
}

?>

<div class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_mode_identification">DOC : Mode d'identification &amp; intégration aux ENT</a></div>

<hr />

<script type="text/javascript">
	var tab_cas = new Array();<?php echo $tab_cas_js ?>
</script>

<form action=""><fieldset>
	<div id="cas_options" class="hide">
		<label class="tab" for="cas_serveur_host">Domaine <img alt="" src="./_img/bulle_aide.png" title="Souvent de la forme 'cas.domaine.fr'." /> :</label><input id="cas_serveur_host" name="cas_serveur_host" size="20" type="text" value="<?php echo html($_SESSION['CAS_SERVEUR_HOST']) ?>" /><br />
		<label class="tab" for="cas_serveur_port">Port <img alt="" src="./_img/bulle_aide.png" title="En général 443.<br />Déjà vu à 8443." /> :</label><input id="cas_serveur_port" name="cas_serveur_port" size="5" type="text" value="<?php echo html($_SESSION['CAS_SERVEUR_PORT']) ?>" /><br />
		<label class="tab" for="cas_serveur_root">Chemin <img alt="" src="./_img/bulle_aide.png" title="En général vide.<br />Parfois 'cas'." /> :</label><input id="cas_serveur_root" name="cas_serveur_root" size="10" type="text" value="<?php echo html($_SESSION['CAS_SERVEUR_ROOT']) ?>" /><br />
	</div>
	<?php echo $choix ?>
	<span class="tab"></span><button id="bouton_valider" type="button"><img alt="" src="./_img/bouton/parametre.png" /> Valider ce mode d'identification.</button><label id="ajax_msg">&nbsp;</label>
	<p><span class="astuce">Pour importer les identifiants de l'ENT, utiliser ensuite la page "<a href="./index.php?page=administrateur_fichier_identifiant">importer / imposer des identifiants</a>".</span></p>
</fieldset></form>
