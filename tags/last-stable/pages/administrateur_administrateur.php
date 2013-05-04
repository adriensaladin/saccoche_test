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
$TITRE = "Gérer les administrateurs";
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_administrateurs">DOC : Gestion des administrateurs</a></span></p>

<table id="table_action" class="form hsort">
  <thead>
    <tr>
      <th>Id. ENT</th>
      <th>Id. GEPI</th>
      <th>Nom</th>
      <th>Prénom</th>
      <th>Login</th>
      <th>Mot de passe</th>
      <th class="nu"><q class="ajouter" title="Ajouter un administrateur."></q></th>
    </tr>
  </thead>
  <tbody>
    <?php
    // Lister les administrateurs
    $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users( 'administrateur' , 1 /*only_actuels*/ , 'user_id,user_id_ent,user_id_gepi,user_nom,user_prenom,user_login' /*liste_champs*/ , FALSE /*with_classe*/ );
    if(!empty($DB_TAB))
    {
      foreach($DB_TAB as $DB_ROW)
      {
        // Afficher une ligne du tableau
        echo'<tr id="id_'.$DB_ROW['user_id'].'">';
        echo  '<td>'.html($DB_ROW['user_id_ent']).'</td>';
        echo  '<td>'.html($DB_ROW['user_id_gepi']).'</td>';
        echo  '<td>'.html($DB_ROW['user_nom']).'</td>';
        echo  '<td>'.html($DB_ROW['user_prenom']).'</td>';
        echo  '<td>'.html($DB_ROW['user_login']).'</td>';
        echo  '<td class="i">champ crypté</td>';
        echo  '<td class="nu">';
        echo    '<q class="modifier" title="Modifier cet administrateur."></q>';
        echo    ($DB_ROW['user_id']!=$_SESSION['USER_ID']) ? '<q class="supprimer" title="Retirer cet administrateur."></q>' : '<q class="supprimer_non" title="Un administrateur ne peut pas supprimer son propre compte."></q>' ;
        echo  '</td>';
        echo'</tr>';
      }
    }
    else
    {
      echo'<tr><td class="nu" colspan="7"></td></tr>'; // Normalement impossible, puisqu'on est justement connecté comme administrateur !
    }
    ?>
  </tbody>
</table>

<form action="#" method="post" id="form_gestion" class="hide">
  <h2>Ajouter | Modifier | Supprimer un utilisateur</h2>
  <div id="gestion_edit">
    <p>
      <label class="tab" for="f_id_ent">Id. ENT <img alt="" src="./_img/bulle_aide.png" title="Uniquement en cas d'identification via un ENT." /> :</label><input id="f_id_ent" name="f_id_ent" type="text" value="" size="30" maxlength="63" /><br />
      <label class="tab" for="f_id_gepi">Id. GEPI <img alt="" src="./_img/bulle_aide.png" title="Uniquement en cas d'utilisation du logiciel GEPI." /> :</label><input id="f_id_gepi" name="f_id_gepi" type="text" value="" size="30" maxlength="63" />
    </p>
    <p>
      <label class="tab" for="f_nom">Nom :</label><input id="f_nom" name="f_nom" type="text" value="" size="30" maxlength="25" /><br />
      <label class="tab" for="f_prenom">Prénom :</label><input id="f_prenom" name="f_prenom" type="text" value="" size="30" maxlength="25" />
    </p>
    <p>
      <label class="tab" for="f_login">Login :</label><input id="box_login" name="box_login" value="1" type="checkbox" checked /> <label for="box_login">automatique | inchangé</label><span><input id="f_login" name="f_login" type="text" value="" size="15" maxlength="20" /></span><br />
      <label class="tab" for="f_password">Mot de passe :</label><input id="box_password" name="box_password" value="1" type="checkbox" checked /> <label for="box_password">aléatoire | inchangé</label><span><input id="f_password" name="f_password" size="15" maxlength="20" type="text" value="" /></span>
    </p>
  </div>
  <p id="gestion_delete">
    Confirmez-vous la suppression du compte administrateur &laquo;&nbsp;<b id="gestion_delete_identite"></b>&nbsp;&raquo; ?
  </p>
  <p>
    <label class="tab"></label><input id="f_action" name="f_action" type="hidden" value="" /><input id="f_id" name="f_id" type="hidden" value="" /><button id="bouton_valider" type="button" class="valider">Valider.</button> <button id="bouton_annuler" type="button" class="annuler">Annuler.</button><label id="ajax_msg_gestion">&nbsp;</label>
  </p>
</form>

<script type="text/javascript">
  var tab_login_modele = new Array(); <?php foreach($_SESSION['TAB_PROFILS_ADMIN']['LOGIN_MODELE'] as $profil_sigle => $login_modele) { echo'tab_login_modele["'.$profil_sigle.'"]="'.$login_modele.'";'; } ?>
  var tab_mdp_longueur_mini = new Array(); <?php foreach($_SESSION['TAB_PROFILS_ADMIN']['MDP_LONGUEUR_MINI'] as $profil_sigle => $mdp_longueur_mini) { echo'tab_mdp_longueur_mini["'.$profil_sigle.'"]='.$mdp_longueur_mini.';'; } ?>
</script>
