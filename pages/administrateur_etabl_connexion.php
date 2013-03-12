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

require(CHEMIN_DOSSIER_INCLUDE.'tableau_sso.php');

// Surcharger les paramètres CAS perso (vides par défaut) avec ceux en session (éventuellement personnalisés).
$tab_connexion_info['cas']['|perso']['serveur_host'] = $_SESSION['CAS_SERVEUR_HOST'];
$tab_connexion_info['cas']['|perso']['serveur_port'] = $_SESSION['CAS_SERVEUR_PORT'];
$tab_connexion_info['cas']['|perso']['serveur_root'] = $_SESSION['CAS_SERVEUR_ROOT'];

// Liste des possibilités
// Retenir en variable javascript les paramètres des serveurs CAS et de Gepi, ainsi que l'état des connecteurs CAS (opérationnels ou pas)
$select_connexions = '';
$tab_param_js = '';
foreach($tab_connexion_mode as $connexion_mode => $mode_texte)
{
  $select_connexions .= '<optgroup label="'.html($mode_texte).'">';
  $tab_param_js .= 'tab_param["'.$connexion_mode.'"] = new Array();';
  foreach($tab_connexion_info[$connexion_mode] as $connexion_ref => $tab_info)
  {
    $selected = ( ($connexion_mode==$_SESSION['CONNEXION_MODE']) && ($connexion_ref==$_SESSION['CONNEXION_DEPARTEMENT'].'|'.$_SESSION['CONNEXION_NOM']) ) ? ' selected' : '' ;
    list($departement,$connexion_nom) = explode('|',$connexion_ref);
    $departement = $departement ? $departement.' | ' : '' ;
    $select_connexions .= '<option value="'.$connexion_mode.'~'.$connexion_ref.'"'.$selected.'>'.$departement.$tab_info['txt'].'</option>';
    switch($connexion_mode)
    {
      case 'cas' :
        $tab_param_js .= 'tab_param["'.$connexion_mode.'"]["'.$connexion_ref.'"]="'.html($tab_info['etat'].']¤['.$tab_info['serveur_host'].']¤['.$tab_info['serveur_port'].']¤['.$tab_info['serveur_root']).'";';
        break;
      case 'shibboleth' :
        $tab_param_js .= 'tab_param["'.$connexion_mode.'"]["'.$connexion_ref.'"]="'.html($tab_info['etat']).'";';
        break;
      case 'gepi' :
        $tab_param_js .= 'tab_param["'.$connexion_mode.'"]["'.$connexion_ref.'"]="'.html($tab_info['saml_url'].']¤['.$tab_info['saml_rne'].']¤['.$tab_info['saml_certif']).'";';
        break;
    }
  }
  $select_connexions .= '</optgroup>';
}

// Modèle d'url SSO
$get_base = ($_SESSION['BASE']) ? '&amp;base='.$_SESSION['BASE'] : '' ;
$url_sso = URL_DIR_SACOCHE.'?sso'.$get_base;

?>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_mode_identification">DOC : Mode d'identification &amp; intégration aux ENT</a></span></div>

<hr />

<script type="text/javascript">
  var tab_param = new Array();<?php echo $tab_param_js ?>
</script>

<form action="#" method="post"><fieldset>
  <p><label class="tab">Choix :</label><select id="connexion_mode_nom" name="connexion_mode_nom"><?php echo $select_connexions ?></select></p>
  <div id="cas_options" class="hide">
    <label class="tab" for="cas_serveur_host">Domaine <img alt="" src="./_img/bulle_aide.png" title="Souvent de la forme 'cas.domaine.fr'." /> :</label><input id="cas_serveur_host" name="cas_serveur_host" size="30" type="text" value="<?php echo html($_SESSION['CAS_SERVEUR_HOST']) ?>" /><br />
    <label class="tab" for="cas_serveur_port">Port <img alt="" src="./_img/bulle_aide.png" title="En général 443.<br />Parfois 8443." /> :</label><input id="cas_serveur_port" name="cas_serveur_port" size="5" type="text" value="<?php echo html($_SESSION['CAS_SERVEUR_PORT']) ?>" /><br />
    <label class="tab" for="cas_serveur_root">Chemin <img alt="" src="./_img/bulle_aide.png" title="En général vide.<br />Parfois 'cas'." /> :</label><input id="cas_serveur_root" name="cas_serveur_root" size="10" type="text" value="<?php echo html($_SESSION['CAS_SERVEUR_ROOT']) ?>" /><br />
  </div>
  <div id="gepi_options" class="hide">
    <label class="tab" for="gepi_saml_url">Adresse (URL) <img alt="" src="./_img/bulle_aide.png" title="Adresse web de GEPI.<br />http://adresse_web_de_mon_gepi" /> :</label><input id="gepi_saml_url" name="gepi_saml_url" size="30" type="text" value="<?php echo html($_SESSION['GEPI_URL']) ?>" /><br />
    <label class="tab" for="gepi_saml_rne">UAI (ex-RNE) <img alt="" src="./_img/bulle_aide.png" title="Indispensable uniquement si installation multisite de GEPI." /> :</label><input id="gepi_saml_rne" name="gepi_saml_rne" size="10" type="text" value="<?php echo ($_SESSION['GEPI_RNE']) ? html($_SESSION['GEPI_RNE']) : html($_SESSION['WEBMESTRE_UAI']) ; ?>" /><br />
    <label class="tab" for="gepi_saml_certif">Signature <img alt="" src="./_img/bulle_aide.png" title="Empreinte du certificat indiquée par GEPI (ne rien modifier par défaut)." /> :</label><input id="gepi_saml_certif" name="gepi_saml_certif" size="60" type="text" value="<?php echo html($_SESSION['GEPI_CERTIFICAT_EMPREINTE']) ?>" /><br />
  </div>
  <p><span class="tab"></span><button id="bouton_valider" type="button" class="parametre">Valider ce mode d'identification.</button><label id="ajax_msg">&nbsp;</label></p>
</fieldset></form>

<div id="lien_direct" class="hide">
  <p class="astuce">Pour importer les identifiants de l'ENT, utiliser ensuite la page "<a href="./index.php?page=administrateur_fichier_identifiant">importer / imposer des identifiants</a>".</p>
  <p class="astuce">Une fois <em>SACoche</em> convenablement configuré, pour une connexion automatique avec l'authentification externe, utiliser cette adresse&nbsp;:</p>
  <ul class="puce"><li class="b"><?php echo $url_sso ?></li></ul>
</div>

<div id="lien_gepi" class="hide">
  <p class="astuce">Dans <em>GEPI</em>, l'adresse de <em>SACoche</em> à indiquer (<span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_mode_identification__Gepi">DOC</a></span>) est&nbsp;: <b><?php echo URL_DIR_SACOCHE ?></b></p>
</div>

<div id="info_inacheve" class="hide">
  <p class="danger"><em>SACoche</em> sait interroger le serveur d'authentification de cet ENT, mais cette passerelle n'est pas finalisée.</p>
  <p class="astuce">Si vous êtes concerné, alors faites remonter votre intérêt pour un tel connecteur auprès des responsables de cet ENT&hellip;</p>
</div>
