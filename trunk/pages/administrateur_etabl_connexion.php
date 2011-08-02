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
$VERSION_JS_FILE += 4;

require_once('./_inc/tableau_sso.php');

// Liste des possibilités
$choix = '';
$i_id = 0;	// Pour donner des ids aux checkbox et radio
foreach($tab_connexion_mode as $connexion_mode => $mode_texte)
{
	foreach($tab_connexion_info[$connexion_mode] as $connexion_nom => $tab_info)
	{
		$i_id++;
		$checked = ( ($connexion_mode==$_SESSION['CONNEXION_MODE']) && ($connexion_nom==$_SESSION['CONNEXION_NOM']) ) ? ' checked' : '' ;
		$choix .= '<span class="tab"><label for="input_'.$i_id.'"><input type="radio" id="input_'.$i_id.'" name="connexion_mode_nom" value="'.$connexion_mode.'|'.$connexion_nom.'"'.$checked.' /> '.$mode_texte.' &rarr; '.$tab_info['txt'].'</label></span><br />'."\r\n";
	}
}

// Retenir en variable javascript les paramètres des serveurs CAS et de Gepi
$tab_param_js = 'tab_param["cas"] = new Array();';
foreach($tab_connexion_info['cas'] as $connexion_nom => $tab_info)
{
	$tab_param_js .= 'tab_param["cas"]["'.$connexion_nom.'"]="'.html($tab_info['serveur_host'].']¤['.$tab_info['serveur_port'].']¤['.$tab_info['serveur_root']).'";';
}
$tab_param_js .= 'tab_param["gepi"] = new Array();';
foreach($tab_connexion_info['gepi'] as $connexion_nom => $tab_info)
{
	$tab_param_js .= 'tab_param["gepi"]["'.$connexion_nom.'"]="'.html($tab_info['saml_url'].']¤['.$tab_info['saml_rne'].']¤['.$tab_info['saml_certif']).'";';
}

// Modèle d'url SSO
$get_base = ($_SESSION['BASE']) ? '&amp;base='.$_SESSION['BASE'] : '' ;
$url_sso = SERVEUR_ADRESSE.'/?sso'.$get_base;

?>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_mode_identification">DOC : Mode d'identification &amp; intégration aux ENT</a></span></div>

<hr />

<script type="text/javascript">
	var tab_param = new Array();<?php echo $tab_param_js ?>
</script>

<form action=""><fieldset>
	<div id="cas_options" class="hide">
		<label class="tab" for="cas_serveur_host">Domaine <img alt="" src="./_img/bulle_aide.png" title="Souvent de la forme 'cas.domaine.fr'." /> :</label><input id="cas_serveur_host" name="cas_serveur_host" size="30" type="text" value="<?php echo html($_SESSION['CAS_SERVEUR_HOST']) ?>" /><br />
		<label class="tab" for="cas_serveur_port">Port <img alt="" src="./_img/bulle_aide.png" title="En général 443.<br />Déjà vu à 8443." /> :</label><input id="cas_serveur_port" name="cas_serveur_port" size="5" type="text" value="<?php echo html($_SESSION['CAS_SERVEUR_PORT']) ?>" /><br />
		<label class="tab" for="cas_serveur_root">Chemin <img alt="" src="./_img/bulle_aide.png" title="En général vide.<br />Parfois 'cas'." /> :</label><input id="cas_serveur_root" name="cas_serveur_root" size="10" type="text" value="<?php echo html($_SESSION['CAS_SERVEUR_ROOT']) ?>" /><br />
	</div>
	<div id="gepi_options" class="hide">
		<label class="tab" for="gepi_saml_url">Adresse (URL) <img alt="" src="./_img/bulle_aide.png" title="Adresse web de GEPI.<br />http://adresse_web_de_mon_gepi" /> :</label><input id="gepi_saml_url" name="gepi_saml_url" size="30" type="text" value="<?php echo html($_SESSION['GEPI_URL']) ?>" /><br />
		<label class="tab" for="gepi_saml_rne">UAI (ex-RNE) <img alt="" src="./_img/bulle_aide.png" title="Indispensable uniquement si installation multisite de GEPI." /> :</label><input id="gepi_saml_rne" name="gepi_saml_rne" size="10" type="text" value="<?php echo ($_SESSION['GEPI_RNE']) ? html($_SESSION['GEPI_RNE']) : html($_SESSION['UAI']) ; ?>" /><br />
		<label class="tab" for="gepi_saml_certif">Signature <img alt="" src="./_img/bulle_aide.png" title="[ Expliquer où trouver l'empreinte du sertificat... ]" /> :</label><input id="gepi_saml_certif" name="gepi_saml_certif" size="60" type="text" value="<?php echo html($_SESSION['GEPI_CERTIFICAT_EMPREINTE']) ?>" /><br />
	</div>
	<?php echo $choix ?>
	<span class="tab"></span><button id="bouton_valider" type="button"><img alt="" src="./_img/bouton/parametre.png" /> Valider ce mode d'identification.</button><label id="ajax_msg">&nbsp;</label>
</fieldset></form>

<p><span class="astuce">Pour importer les identifiants de l'ENT, utiliser ensuite la page "<a href="./index.php?page=administrateur_fichier_identifiant">importer / imposer des identifiants</a>".</span></p>

<div id="lien_direct" class="<?php echo ($_SESSION['CONNEXION_MODE']!='normal') ? 'show' : 'hide' ; ?>">
	<span class="astuce">Une fois <em>SACoche</em> convenablement configuré, pour une connexion automatique avec l'authentification externe, utiliser cette adresse&nbsp;:</span>
	<ul class="puce"><li class="b"><?php echo $url_sso ?></li></ul>
</div>
