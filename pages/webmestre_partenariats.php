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
$TITRE = "Gérer les partenaires ENT conventionnés";

// Page réservée aux installations multi-structures sur le serveur Sésamath ; le menu webmestre d'une installation mono-structure ne permet normalement pas d'arriver ici
if( (HEBERGEUR_INSTALLATION=='mono-structure') || (!IS_HEBERGEMENT_SESAMATH) )
{
  echo'<p class="astuce">L\'installation étant de type mono-structure ou non hébergée par Sésamath, cette fonctionnalité de <em>SACoche</em> est sans objet vous concernant.</p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}
?>

<table id="table_action" class="form hsort">
  <thead>
    <tr>
      <th>Id</th>
      <th>Dénomination</th>
      <th>Nom</th>
      <th>Prénom</th>
      <th>Courriel</th>
      <th>Connecteurs</th>
      <th class="nu"><q class="ajouter" title="Ajouter un partenaire conventionné."></q></th>
    </tr>
  </thead>
  <tbody>
    <?php
    // Lister les partenaires ENT conventionnés
    $DB_TAB = DB_WEBMESTRE_WEBMESTRE::DB_lister_partenaires_conventionnes();
    if(!empty($DB_TAB))
    {
      foreach($DB_TAB as $DB_ROW)
      {
        // Afficher une ligne du tableau
        echo'<tr id="id_'.$DB_ROW['partenaire_id'].'">';
        echo  '<td>'.$DB_ROW['partenaire_id'].'</td>';
        echo  '<td>'.html($DB_ROW['partenaire_denomination']).'</td>';
        echo  '<td>'.html($DB_ROW['partenaire_nom']).'</td>';
        echo  '<td>'.html($DB_ROW['partenaire_prenom']).'</td>';
        echo  '<td>'.html($DB_ROW['partenaire_courriel']).'</td>';
        echo  '<td>'.html($DB_ROW['partenaire_connecteurs']).'</td>';
        echo  '<td class="nu">';
        echo    '<q class="modifier" title="Modifier ce partenaire."></q>';
        echo    '<q class="initialiser_mdp" title="Générer un nouveau mdp pour ce partenaire."></q>';
        echo    '<q class="supprimer" title="Retirer ce partenaire."></q>';
        echo  '</td>';
        echo'</tr>'.NL;
      }
    }
    else
    {
      echo'<tr><td class="nu" colspan="7"></td></tr>'.NL;
    }
    ?>
  </tbody>
</table>

<form action="#" method="post" id="form_gestion" class="hide">
  <h2>Ajouter | Modifier | Supprimer un partenaire conventionné</h2>
  <div id="gestion_edit">
    <p>
      <label class="tab" for="f_denomination">Dénomination <img alt="" src="./_img/bulle_aide.png" title="Exemple : Académie de ..." /> :</label><input id="f_denomination" name="f_denomination" type="text" value="" size="50" maxlength="63" />
    </p>
    <p>
      <label class="tab" for="f_nom">Nom :</label><input id="f_nom" name="f_nom" type="text" value="" size="30" maxlength="25" /><br />
      <label class="tab" for="f_prenom">Prénom :</label><input id="f_prenom" name="f_prenom" type="text" value="" size="30" maxlength="25" /><br />
      <label class="tab" for="f_courriel">Courriel :</label><input id="f_courriel" name="f_courriel" type="text" value="" size="50" maxlength="63" />
    </p>
    <p>
      <label class="tab" for="f_connecteurs">Connecteurs <img alt="" src="./_img/bulle_aide.png" title="Valeurs à prendre dans le fichier tableau_sso.php.<br />Valeurs à séparer, faire précéder et terminer par des virgules." /> :</label><input id="f_connecteurs" name="f_connecteurs" type="text" value="" size="50" maxlength="255" />
    </p>
  </div>
  <p id="gestion_delete">
    Confirmez-vous la suppression du compte partenaire &laquo;&nbsp;<b id="gestion_delete_identite"></b>&nbsp;&raquo; ?
  </p>
  <p id="gestion_generer_mdp">
    Confirmez-vous la génération et l'envoi d'un nouveau mot de passe pour &laquo;&nbsp;<b id="gestion_initialiser_mdp_identite"></b>&nbsp;&raquo; ?
  </p>
  <p>
    <label class="tab"></label><input id="f_action" name="f_action" type="hidden" value="" /><input id="f_id" name="f_id" type="hidden" value="" /><button id="bouton_valider" type="button" class="valider">Valider.</button> <button id="bouton_annuler" type="button" class="annuler">Annuler.</button><label id="ajax_msg_gestion">&nbsp;</label>
  </p>
</form>
