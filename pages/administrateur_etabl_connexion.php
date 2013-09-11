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
$tab_connexion_info['cas']['|perso']['serveur_host'] = $_SESSION['CAS_SERVEUR']['HOST'];
$tab_connexion_info['cas']['|perso']['serveur_port'] = $_SESSION['CAS_SERVEUR']['PORT'];
$tab_connexion_info['cas']['|perso']['serveur_root'] = $_SESSION['CAS_SERVEUR']['ROOT'];
$tab_connexion_info['cas']['|perso']['serveur_url_login']    = $_SESSION['CAS_SERVEUR']['URL_LOGIN'];
$tab_connexion_info['cas']['|perso']['serveur_url_logout']   = $_SESSION['CAS_SERVEUR']['URL_LOGOUT'];
$tab_connexion_info['cas']['|perso']['serveur_url_validate'] = $_SESSION['CAS_SERVEUR']['URL_VALIDATE'];

if(IS_HEBERGEMENT_SESAMATH)
{
  if(!is_file(CHEMIN_FICHIER_WS_SESAMATH_ENT))
  {
    echo'<p class="danger">Le fichier &laquo;&nbsp;<b>'.FileSystem::fin_chemin(CHEMIN_FICHIER_WS_SESAMATH_ENT).'</b>&nbsp;&raquo; (uniquement présent sur le serveur Sésamath) n\'a pas été détecté !<p>'.NL;
    return; // Ne pas exécuter la suite de ce fichier inclus.
  }  
  require(CHEMIN_FICHIER_WS_SESAMATH_ENT); // Charge les tableaux   $tab_connecteurs_hebergement & $tab_connecteurs_convention
}
else
{
  $tab_connecteurs_hebergement = $tab_connecteurs_convention = array();
}

// Javascript
$GLOBALS['HEAD']['js']['inline'][] = 'var IS_HEBERGEMENT_SESAMATH = '.(int)IS_HEBERGEMENT_SESAMATH.';';
$GLOBALS['HEAD']['js']['inline'][] = 'var CONVENTION_ENT_REQUISE  = '.(int)CONVENTION_ENT_REQUISE.';';
$GLOBALS['HEAD']['js']['inline'][] = 'var tab_param = new Array();';

// Liste des possibilités
// Retenir en variable javascript les paramètres des serveurs CAS et de Gepi, ainsi que l'état des connecteurs CAS (opérationnels ou pas, avec convention ou pas)
$select_connexions = '';
foreach($tab_connexion_mode as $connexion_mode => $mode_texte)
{
  $select_connexions .= '<optgroup label="'.html($mode_texte).'">';
  $GLOBALS['HEAD']['js']['inline'][] = 'tab_param["'.$connexion_mode.'"] = new Array();';
  foreach($tab_connexion_info[$connexion_mode] as $connexion_ref => $tab_info)
  {
    $selected = ( ($connexion_mode==$_SESSION['CONNEXION_MODE']) && ($connexion_ref==$_SESSION['CONNEXION_DEPARTEMENT'].'|'.$_SESSION['CONNEXION_NOM']) ) ? ' selected' : '' ;
    list($departement,$connexion_nom) = explode('|',$connexion_ref);
    $departement = $departement ? $departement.' | ' : '' ;
    $select_connexions .= '<option value="'.$connexion_mode.'~'.$connexion_ref.'"'.$selected.'>'.$departement.$tab_info['txt'].'</option>';
    switch($connexion_mode)
    {
      case 'cas' :
        $convention = ($connexion_nom=='perso') ? 'hors_ent' : ( isset($tab_connecteurs_hebergement[$connexion_ref]) ? 'heberg_acad' : ( isset($tab_connecteurs_convention[$connexion_ref]) ? 'conv_acad' : 'conv_etabl' ) ) ;
        $GLOBALS['HEAD']['js']['inline'][] = 'tab_param["'.$connexion_mode.'"]["'.$connexion_ref.'"]="'.html($convention.']¤['.$tab_info['etat'].']¤['.$tab_info['serveur_host'].']¤['.$tab_info['serveur_port'].']¤['.$tab_info['serveur_root'].']¤['.$tab_info['serveur_url_login'].']¤['.$tab_info['serveur_url_logout'].']¤['.$tab_info['serveur_url_validate']).'";';
        break;
      case 'shibboleth' :
        $GLOBALS['HEAD']['js']['inline'][] = 'tab_param["'.$connexion_mode.'"]["'.$connexion_ref.'"]="'.html($tab_info['etat']).'";';
        break;
      case 'gepi' :
        $GLOBALS['HEAD']['js']['inline'][] = 'tab_param["'.$connexion_mode.'"]["'.$connexion_ref.'"]="'.html($tab_info['saml_url'].']¤['.$tab_info['saml_rne'].']¤['.$tab_info['saml_certif']).'";';
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

<form id="form_mode" action="#" method="post"><fieldset>
  <p><label class="tab">Choix :</label><select id="connexion_mode_nom" name="connexion_mode_nom"><?php echo $select_connexions ?></select></p>
  <div id="cas_options" class="hide">
    <label class="tab" for="cas_serveur_host">Domaine <img alt="" src="./_img/bulle_aide.png" title="Souvent de la forme 'cas.domaine.fr'." /> :</label><input id="cas_serveur_host" name="cas_serveur_host" size="30" type="text" value="<?php echo html($_SESSION['CAS_SERVEUR']['HOST']) ?>" /><br />
    <label class="tab" for="cas_serveur_port">Port <img alt="" src="./_img/bulle_aide.png" title="En général 443.<br />Parfois 8443." /> :</label><input id="cas_serveur_port" name="cas_serveur_port" size="5" type="text" value="<?php echo html($_SESSION['CAS_SERVEUR']['PORT']) ?>" /><br />
    <label class="tab" for="cas_serveur_root">Chemin <img alt="" src="./_img/bulle_aide.png" title="En général vide.<br />Parfois 'cas'." /> :</label><input id="cas_serveur_root" name="cas_serveur_root" size="10" type="text" value="<?php echo html($_SESSION['CAS_SERVEUR']['ROOT']) ?>" /><br />
    <label class="tab" for="cas_serveur_url_login">URL Login <img alt="" src="./_img/bulle_aide.png" title="Par défaut, laisser le champ vide.<br />Dans ce cas, construit sur le modèle 'https://[domaine]:[port]/[chemin]/login'.<br />Indiquer une autre URL pour surcharger ce chemin automatique." /> :</label><input id="cas_serveur_url_login" name="cas_serveur_url_login" size="50" type="text" value="<?php echo html($_SESSION['CAS_SERVEUR']['URL_LOGIN']) ?>" /><br />
    <label class="tab" for="cas_serveur_url_logout">URL Logout <img alt="" src="./_img/bulle_aide.png" title="Par défaut, laisser le champ vide.<br />Dans ce cas, construit sur le modèle 'https://[domaine]:[port]/[chemin]/logout'.<br />Indiquer une autre URL pour surcharger ce chemin automatique." /> :</label><input id="cas_serveur_url_logout" name="cas_serveur_url_logout" size="50" type="text" value="<?php echo html($_SESSION['CAS_SERVEUR']['URL_LOGOUT']) ?>" /><br />
    <label class="tab" for="cas_serveur_url_validate">URL Validate <img alt="" src="./_img/bulle_aide.png" title="Par défaut, laisser le champ vide.<br />Dans ce cas, construit sur le modèle 'https://[domaine]:[port]/[chemin]/serviceValidate'.<br />Indiquer une autre URL pour surcharger ce chemin automatique." /> :</label><input id="cas_serveur_url_validate" name="cas_serveur_url_validate" size="50" type="text" value="<?php echo html($_SESSION['CAS_SERVEUR']['URL_VALIDATE']) ?>" /><br />
  </div>
  <div id="gepi_options" class="hide">
    <label class="tab" for="gepi_saml_url">Adresse (URL) <img alt="" src="./_img/bulle_aide.png" title="Adresse web de GEPI.<br />http://adresse_web_de_mon_gepi" /> :</label><input id="gepi_saml_url" name="gepi_saml_url" size="30" type="text" value="<?php echo html($_SESSION['GEPI_URL']) ?>" /><br />
    <label class="tab" for="gepi_saml_rne">UAI (ex-RNE) <img alt="" src="./_img/bulle_aide.png" title="Indispensable uniquement si installation multisite de GEPI." /> :</label><input id="gepi_saml_rne" name="gepi_saml_rne" size="10" type="text" value="<?php echo ($_SESSION['GEPI_RNE']) ? html($_SESSION['GEPI_RNE']) : html($_SESSION['WEBMESTRE_UAI']) ; ?>" /><br />
    <label class="tab" for="gepi_saml_certif">Signature <img alt="" src="./_img/bulle_aide.png" title="Empreinte du certificat indiquée par GEPI (ne rien modifier par défaut)." /> :</label><input id="gepi_saml_certif" name="gepi_saml_certif" size="60" type="text" value="<?php echo html($_SESSION['GEPI_CERTIFICAT_EMPREINTE']) ?>" /><br />
  </div>
  <p><span class="tab"></span><button id="bouton_valider_mode" type="button" class="parametre">Valider ce mode d'identification.</button><label id="ajax_msg_mode">&nbsp;</label></p>
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
  <p class="danger"><em>SACoche</em> sait interroger le serveur d'authentification de cet ENT, mais ses responsables ne l'ont pas intégré.</p>
  <p class="astuce">Si vous êtes concerné, alors faites remonter votre intérêt pour un tel connecteur auprès des responsables de cet ENT&hellip;</p>
</div>

<hr />
<h2>Convention d'accès au service</h2>

<div id="info_hors_sesamath" class="hide">
  <p class="astuce">Sans objet pour cet hébergement.</p>
</div>

<div id="info_hors_actualite" class="hide">
  <p class="astuce">Sans objet car non requis actuellement.</p>
</div>

<div id="info_hors_ent" class="hide">
  <p class="astuce">Sans objet pour ce mode d'authentification.</p>
</div>

<div id="info_heberg_acad" class="hide">
  <p class="astuce">Sans objet car hébergement académique ou départemental.</p>
</div>

<div id="info_conv_acad" class="hide">
  <p><label class="valide">Signée et réglée par le service académique ou départemental (<a href="./index.php?page=compte_accueil">voir en page d'accueil</a>).</label></p>
</div>

<div id="info_conv_etabl" class="hide">
  <p class="astuce">
    La signature d'un contrat et son règlement est requis à compter du <?php echo CONVENTION_ENT_START_DATE_FR ?> pour bénéficier de ce service sur le serveur <em>Sésamath</em>.<br />
    Veuillez consulter <a href="<?php echo SERVEUR_BLOG_CONVENTION ?>" class="lien_ext">cet article du blog de l'association Sésamath</a> pour comprendre les raisons de cette procédure.
  </p>
  <table id="table_action" class="form hsort">
    <thead>
      <tr>
        <th>Nom du service</th>
        <th>Période</th>
        <th>Documents générés</th>
        <th>Contrat signé</th>
        <th>Règlement perçu</th>
        <th>Service activé</th>
        <th class="nu"><q class="ajouter" title="Ajouter une convention."></q></th>
      </tr>
    </thead>
    <tbody>
      <?php
      // Récupérer les coordonnées du contact référent
      // Lister les conventions de cet établissement
      $contact_nom = $contact_prenom = $contact_courriel = '' ;
      $DB_TAB = array();
      if( (IS_HEBERGEMENT_SESAMATH) && (HEBERGEUR_INSTALLATION=='multi-structures') )
      {
        charger_parametres_mysql_supplementaires( 0 /*BASE*/ );
        $DB_ROW2 = DB_WEBMESTRE_ADMINISTRATEUR::DB_recuperer_contact_infos($_SESSION['BASE']);
        $contact_nom      = $DB_ROW2['structure_contact_nom'];
        $contact_prenom   = $DB_ROW2['structure_contact_prenom'];
        $contact_courriel = $DB_ROW2['structure_contact_courriel'];
        $DB_TAB = DB_WEBMESTRE_ADMINISTRATEUR::DB_lister_conventions_structure($_SESSION['BASE']);
      }
      if(!empty($DB_TAB))
      {
        foreach($DB_TAB as $DB_ROW)
        {
          // Formater certains éléments
          $texte_signature  = ($DB_ROW['convention_signature']===NULL) ? 'Non réceptionné' : 'Oui, le '.convert_date_mysql_to_french($DB_ROW['convention_signature']) ;
          $texte_paiement   = ($DB_ROW['convention_paiement']===NULL)  ? 'Non réceptionné' : 'Oui, le '.convert_date_mysql_to_french($DB_ROW['convention_paiement']) ;
          $texte_activation = (!$DB_ROW['convention_activation']) ? 'Non' : ( ( ($DB_ROW['convention_date_debut']>TODAY_MYSQL) || ($DB_ROW['convention_date_fin']<TODAY_MYSQL) ) ? 'Non (hors période)' : 'Oui' ) ;
          $class_signature  = (substr($texte_signature ,0,3)=='Non') ? 'br' : 'bv' ;
          $class_paiement   = (substr($texte_paiement  ,0,3)=='Non') ? 'br' : 'bv' ;
          $class_activation = (substr($texte_activation,0,3)=='Non') ? 'br' : 'bv' ;
          // Afficher une ligne du tableau
          echo'<tr id="id_'.$DB_ROW['convention_id'].'">';
          echo  '<td>'.html($DB_ROW['connexion_nom']).'</td>';
          echo  '<td>du '.convert_date_mysql_to_french($DB_ROW['convention_date_debut']).' au '.convert_date_mysql_to_french($DB_ROW['convention_date_fin']).'</td>';
          echo  '<td>Oui, le '.convert_date_mysql_to_french($DB_ROW['convention_creation']).'</td>';
          echo  '<td class="'.$class_signature.'">'.$texte_signature.'</td>';
          echo  '<td class="'.$class_paiement.'">'.$texte_paiement.'</td>';
          echo  '<td class="'.$class_activation.'">'.$texte_activation.'</td>';
          echo  '<td class="nu"><q class="voir_archive" title="Récupérer / Imprimer les documents associés."></q></td>';
          echo'</tr>'.NL;
        }
      }
      else
      {
        echo'<tr><td class="nu probleme" colspan="7">Cliquer sur l\'icone ci-dessus (symbole "+" dans un rond vert) pour ajouter une convention.</td></tr>'.NL;
      }
      ?>
    </tbody>
  </table>
  <p class="astuce">
    Les documents sont établis au nom de <b><?php echo html($contact_nom.' '.$contact_prenom); ?></b>, contact référent de l'établissement pour <em>SACoche</em>, qui recevra des informations sur l'avancement du dossier à son adresse <b><?php echo html($contact_courriel) ?></b>.<br />
    Pour communiquer les coordonnées d'un nouveau contact référent, voyez le menu <a href="./index.php?page=administrateur_etabl_identite">[Identité de l'établissement]</a>.</span>
  </p>
</div>

<form action="#" method="post" id="form_ajout" class="hide">
  <h2>Ajouter une convention</h2>
  <p>
    <label class="tab" for="f_etablissement_denomination">Établissement :</label><input id="f_etablissement_denomination" name="f_etablissement_denomination" type="text" value="<?php echo html($_SESSION['WEBMESTRE_DENOMINATION'].' ['.$_SESSION['WEBMESTRE_UAI'].']'); ?>" size="60" readonly />
  </p>
  <p>
    <label class="tab" for="f_connexion_texte">Service :</label><input id="f_connexion_texte" name="f_connexion_texte" type="text" value="" size="60" readonly /><br />
    <span class="tab"></span><span class="astuce">Le service est celui qui a été sélectionné sur cette même page.</span>
  </p>
  <p>
    <label class="tab" for="f_annee">Période :</label><select id="f_annee" name="f_annee">
      <option value="-1"></option>
      <option value="0">Année scolaire actuelle : du <?php echo jour_debut_annee_scolaire('french',0).' au '.jour_fin_annee_scolaire('french',0) ?></option>
      <option value="1">Année scolaire suivante : du <?php echo jour_debut_annee_scolaire('french',1).' au '.jour_fin_annee_scolaire('french',1) ?></option>
    </select><br />
    <span class="tab"></span><span class="astuce">Les dates sont basées sur l'année scolaire définie dans le menu <a href="./index.php?page=administrateur_etabl_identite">[Identité de l'établissement]</a>.</span>
  </p>
  <p>
    <label class="tab"></label><button id="bouton_valider_ajout" type="button" class="valider">Valider.</button> <button id="bouton_annuler_ajout" type="button" class="annuler">Annuler.</button><br />
    <label class="tab"></label><label id="ajax_msg_ajout">&nbsp;</label>
  </p>
</form>

<form action="#" method="post" id="form_impression" class="hide">
  <h2>Récupérer / Imprimer les documents associés</h2>
  <p class="astuce">Les coordonnées de votre établissement et du contact référent sont définies dans le menu <a href="./index.php?page=administrateur_etabl_identite">[Identité de l'établissement]</a>.</p>
  <ul class="puce">
    <li><a id="fichier_convention" class="lien_ext" href=""><span class="file file_pdf">Récupérer / Imprimer votre contrat (format <em>pdf</em>).</span></a></li>
    <li><a id="fichier_facture" class="lien_ext" href=""><span class="file file_pdf">Récupérer / Imprimer votre facture (format <em>pdf</em>).</span></a></li>
  </ul>
</form>
