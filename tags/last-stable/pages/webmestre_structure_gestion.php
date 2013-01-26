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
$TITRE = "Gestion des établissements";

// Page réservée aux installations multi-structures ; le menu webmestre d'une installation mono-structure ne permet normalement pas d'arriver ici
if(HEBERGEUR_INSTALLATION=='mono-structure')
{
  echo'<p class="astuce">L\'installation étant de type mono-structure, cette fonctionnalité de <em>SACoche</em> est sans objet vous concernant.</p>';
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// Élément de formulaire "f_geo" pour le choix d'une zone géographique
$options_geo = '';
$DB_TAB = DB_WEBMESTRE_WEBMESTRE::DB_lister_zones();
foreach($DB_TAB as $DB_ROW)
{
  $options_geo .= '<option value="'.$DB_ROW['geo_id'].'">'.html($DB_ROW['geo_nom']).'</option>';
}
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_webmestre__gestion_multi_etablissements">DOC : Gestion des établissements (multi-structures)</a></span></p>

<script type="text/javascript">
  // <![CDATA[
  var options_geo="<?php echo str_replace('"','\"',$options_geo); ?>";
  // ]]>
</script>

<table class="form bilan_synthese vm_nug hsort">
  <thead>
    <tr>
      <th class="nu"></th>
      <th class="nu"><input name="leurre" type="image" alt="leurre" src="./_img/auto.gif" /><input id="all_check" type="image" alt="Tout cocher." src="./_img/all_check.gif" title="Tout cocher." /><br /><input id="all_uncheck" type="image" alt="Tout décocher." src="./_img/all_uncheck.gif" title="Tout décocher." /></th>
      <th>Id</th>
      <th>Zone géo</th>
      <th>Localisation<br />Dénomination</th>
      <th>UAI</th>
      <th>Nom<br />Prénom</th>
      <th>Courriel</th>
      <th class="nu"><q class="ajouter" title="Ajouter un établissement."></q></th>
    </tr>
  </thead>
  <tbody>
    <?php
    // Lister les structures
    $DB_TAB = DB_WEBMESTRE_WEBMESTRE::DB_lister_structures();
    foreach($DB_TAB as $DB_ROW)
    {
      // Afficher une ligne du tableau
      $img = (LockAcces::tester_blocage('webmestre',$DB_ROW['sacoche_base'])===NULL) ? '<img class="bloquer" src="./_img/etat/acces_oui.png" title="Bloquer cet établissement." />' : '<img class="debloquer" src="./_img/etat/acces_non.png" title="Débloquer cet établissement." />' ;
      echo'<tr id="id_'.$DB_ROW['sacoche_base'].'">';
      echo  '<td class="nu"><a href="#id_0">'.$img.'</a></td>';
      echo  '<td class="nu"><input type="checkbox" name="f_ids" value="'.$DB_ROW['sacoche_base'].'" /></td>';
      echo  '<td class="label">'.$DB_ROW['sacoche_base'].'</td>';
      echo  '<td class="label"><i>'.sprintf("%02u",$DB_ROW['geo_ordre']).'</i>'.html($DB_ROW['geo_nom']).'</td>';
      echo  '<td class="label">'.html($DB_ROW['structure_localisation']).'<br />'.html($DB_ROW['structure_denomination']).'</td>';
      echo  '<td class="label">'.html($DB_ROW['structure_uai']).'</td>';
      echo  '<td class="label">'.html($DB_ROW['structure_contact_nom']).'<br />'.html($DB_ROW['structure_contact_prenom']).'</td>';
      echo  '<td class="label">'.html($DB_ROW['structure_contact_courriel']).'</td>';
      echo  '<td class="nu">';
      echo    '<q class="modifier" title="Modifier cet établissement."></q>';
      echo    '<q class="initialiser_mdp" title="Générer un nouveau mdp d\'un admin."></q>';
      echo    '<q class="supprimer" title="Supprimer cet établissement."></q>';
      echo  '</td>';
      echo'</tr>';
    }
    ?>
  </tbody>
</table>

<form action="#" method="post" id="form_gestion" class="hide">
  <h2>Ajouter | Modifier | Supprimer un établissement</h2>
  <div id="gestion_edit">
    <p id="p_ajout">
      <label class="tab" for="f_base_id">Id <img alt="" src="./_img/bulle_aide.png" title="Numéro de la base de l'établissement.<br />Saisie facultative et déconseillée.<br />Nombre entier auto-incrémenté par défaut." /> :</label><input id="f_base_id" name="f_base_id" type="text" value="" size="4" maxlength="8" />
    </p>
    <p>
      <label class="tab" for="f_geo">Zone géographique :</label><select id="f_geo" name="f_geo"><option></option></select><br />
      <label class="tab" for="f_localisation">Localisation :</label><input id="f_localisation" name="f_localisation" type="text" value="" size="50" maxlength="100" /><br />
      <label class="tab" for="f_denomination">Dénomination :</label><input id="f_denomination" name="f_denomination" type="text" value="" size="50" maxlength="50" /><br />
      <label class="tab" for="f_uai">UAI :</label><input id="f_uai" name="f_uai" type="text" value="" size="10" maxlength="8" />
    </p>
    <p>
      <label class="tab" for="f_contact_nom">Contact Nom :</label><input id="f_contact_nom" name="f_contact_nom" type="text" value="" size="20" maxlength="20" /><br />
      <label class="tab" for="f_contact_prenom">Contact Prénom :</label><input id="f_contact_prenom" name="f_contact_prenom" type="text" value="" size="20" maxlength="20" /><br />
      <label class="tab" for="f_contact_courriel">Contact Courriel :</label><input id="f_contact_courriel" name="f_contact_courriel" type="text" value="" size="50" maxlength="60" /><br />
      <span id="span_envoi">
        <label class="tab"></label><input id="f_courriel_envoi" name="f_courriel_envoi" type="checkbox" value="1" checked /><label for="f_courriel_envoi"> envoyer le courriel d'inscription</label>
      </span>
    </p>
  </div>
  <div id="gestion_delete">
    <p class="danger">La base sera supprimée, donc tout le travail de l'établissement sera effacé !</p>
    <p>Confirmez-vous la suppression de l'établissement &laquo;&nbsp;<b id="gestion_delete_identite"></b>&nbsp;&raquo; ?</p>
  </div>
  <p>
    <label class="tab"></label><input id="f_action" name="f_action" type="hidden" value="" /><input id="f_acces" name="f_acces" type="hidden" value="" /><input id="f_check" name="f_check" type="hidden" value="" /><button id="bouton_valider" type="button" class="valider">Valider.</button> <button id="bouton_annuler" type="button" class="annuler">Annuler.</button><label id="ajax_msg_gestion">&nbsp;</label>
  </p>
</form>

<form action="#" method="post" id="zone_generer_mdp" class="hide">
  <h2>Générer un nouveau mot de passe pour un administrateur d'établissement</h2>
  <p class="b" id="titre_generer_mdp"></p>
  <p>
    <label class="tab" for="f_admin_id">Administrateur :</label><select id="f_admin_id" name="f_admin_id"><option></option></select>
  </p>
  <p>
    <button id="valider_generer_mdp" type="button" class="valider">Valider.</button> <button id="fermer_zone_generer_mdp" type="button" class="annuler">Annuler.</button> <label id="ajax_msg_generer_mdp">&nbsp;</label>
    <input id="generer_base_id" name="f_base_id" type="hidden" value="" />
  </p>
</form>

<form action="#" method="post" id="structures">
<div id="zone_actions" class="p">
  Pour les structures cochées :<input id="listing_ids" name="listing_ids" type="hidden" value="" />
  <button id="bouton_newsletter" type="button" class="mail_ecrire">Écrire un courriel.</button>
  <button id="bouton_stats" type="button" class="stats">Calculer les statistiques.</button>
  <button id="bouton_transfert" type="button" class="fichier_export">Exporter données &amp; bases.</button>
  <button id="bouton_supprimer" type="button" class="supprimer">Supprimer.</button>
  <label id="ajax_supprimer">&nbsp;</label>
</div>
</form>

