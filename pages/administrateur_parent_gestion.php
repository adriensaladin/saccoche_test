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
$TITRE = "Gérer les parents";

// Récupérer d'éventuels paramètres pour restreindre l'affichage
// Pas de passage par la page ajax.php, mais pas besoin ici de protection contre attaques type CSRF
$statut       = (isset($_POST['f_statut']))       ? Clean::entier($_POST['f_statut'])       : 1  ;
$debut_nom    = (isset($_POST['f_debut_nom']))    ? Clean::nom($_POST['f_debut_nom'])       : '' ;
$debut_prenom = (isset($_POST['f_debut_prenom'])) ? Clean::prenom($_POST['f_debut_prenom']) : '' ;
// Construire et personnaliser le formulaire pour restreindre l'affichage
$select_f_statuts = Form::afficher_select(Form::$tab_select_statut , 'f_statut' /*select_nom*/ , FALSE /*option_first*/ , $statut /*selection*/ , '' /*optgroup*/);
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_parents">DOC : Gestion des parents</a></span></p>

<form action="./index.php?page=administrateur_parent&amp;section=gestion" method="post" id="form_prechoix">
  <div><label class="tab" for="f_debut_nom">Recherche :</label>le nom commence par <input type="text" id="f_debut_nom" name="f_debut_nom" value="<?php echo html($debut_nom) ?>" size="5" /> le prénom commence par <input type="text" id="f_debut_prenom" name="f_debut_prenom" value="<?php echo html($debut_prenom) ?>" size="5" /> <input type="hidden" id="f_afficher" name="f_afficher" value="1" /><button id="actualiser" type="submit" class="actualiser">Actualiser.</button></div>
  <div><label class="tab" for="f_statut">Statut :</label><?php echo $select_f_statuts ?></div>
</form>

<hr />

<script type="text/javascript">
  var input_date = "<?php echo TODAY_FR ?>";
  var date_mysql = "<?php echo TODAY_MYSQL ?>";
  var tab_login_modele = new Array(); <?php foreach($_SESSION['TAB_PROFILS_ADMIN']['LOGIN_MODELE'] as $profil_sigle => $login_modele) { echo'tab_login_modele["'.$profil_sigle.'"]="'.$login_modele.'";'; } ?>
  var tab_mdp_longueur_mini = new Array(); <?php foreach($_SESSION['TAB_PROFILS_ADMIN']['MDP_LONGUEUR_MINI'] as $profil_sigle => $mdp_longueur_mini) { echo'tab_mdp_longueur_mini["'.$profil_sigle.'"]='.$mdp_longueur_mini.';'; } ?>
</script>

<?php

if(empty($_POST['f_afficher']))
{
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// Options du formulaire de profils
$options = '';
$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_profils_parametres( 'user_profil_nom_long_singulier' /*listing_champs*/ , TRUE /*only_actif*/ , 'parent' /*only_listing_profils_types*/ );
foreach($DB_TAB as $DB_ROW)
{
  $options .= '<option value="'.$DB_ROW['user_profil_sigle'].'">'.$DB_ROW['user_profil_sigle'].' &rarr; '.$DB_ROW['user_profil_nom_long_singulier'].'</option>';
}
?>

<table id="table_action" class="form t9 hsort">
  <thead>
    <tr>
      <th class="nu"><q class="cocher_tout" title="Tout cocher."></q><br /><q class="cocher_rien" title="Tout décocher."></q></th>
      <th>Resp</th>
      <th>Id. ENT</th>
      <th>Id. GEPI</th>
      <th>Id Sconet</th>
      <th>Référence</th>
      <th>Profil</th>
      <th>Nom</th>
      <th>Prénom</th>
      <th>Login</th>
      <th>Mot de passe</th>
      <th>Date sortie</th>
      <th class="nu"><q class="ajouter" title="Ajouter un parent."></q></th>
    </tr>
  </thead>
  <tbody>
    <?php
    // Lister les parents
    $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_parents_avec_infos_enfants( FALSE /*with_adresse*/ , $statut , $debut_nom , $debut_prenom );
    if(!empty($DB_TAB))
    {
      foreach($DB_TAB as $DB_ROW)
      {
        // Formater la date
        $date_mysql  = $DB_ROW['user_sortie_date'];
        $date_affich = ($date_mysql!=SORTIE_DEFAUT_MYSQL) ? convert_date_mysql_to_french($date_mysql) : '-' ;
        // Afficher une ligne du tableau
        echo'<tr id="id_'.$DB_ROW['user_id'].'">';
        echo  '<td class="nu"><input type="checkbox" name="f_ids" value="'.$DB_ROW['user_id'].'" /></td>';
        echo  ($DB_ROW['enfants_nombre']) ? '<td>'.$DB_ROW['enfants_nombre'].' <img alt="" src="./_img/bulle_aide.png" title="'.str_replace('§BR§','<br />',html(html($DB_ROW['enfants_liste']))).'" /></td>' : '<td>0 <img alt="" src="./_img/bulle_aide.png" title="Aucun lien de responsabilité !" /></td>' ; // Volontairement 2 html() pour le title sinon &lt;* est pris comme une balise html par l'infobulle.
        echo  '<td class="label">'.html($DB_ROW['user_id_ent']).'</td>';
        echo  '<td class="label">'.html($DB_ROW['user_id_gepi']).'</td>';
        echo  '<td class="label">'.html($DB_ROW['user_sconet_id']).'</td>';
        echo  '<td class="label">'.html($DB_ROW['user_reference']).'</td>';
        echo  '<td class="label">'.html($DB_ROW['user_profil_sigle']).' <img alt="" src="./_img/bulle_aide.png" title="'.$_SESSION['TAB_PROFILS_ADMIN']['TYPE'][$DB_ROW['user_profil_sigle']].'" /></td>';
        echo  '<td class="label">'.html($DB_ROW['user_nom']).'</td>';
        echo  '<td class="label">'.html($DB_ROW['user_prenom']).'</td>';
        echo  '<td class="label">'.html($DB_ROW['user_login']).'</td>';
        echo  '<td class="label i">champ crypté</td>';
        echo  '<td class="label">'.$date_affich.'</td>';
        echo  '<td class="nu">';
        echo    '<q class="modifier" title="Modifier ce parent."></q>';
        echo  '</td>';
        echo'</tr>';
      }
    }
    else
    {
      echo'<tr><td class="nu" colspan="13"></td></tr>';
    }
    ?>
  </tbody>
</table>

<div id="zone_actions" style="margin-left:3em">
  <div class="p"><span class="u">Pour les utilisateurs cochés :</span> <input id="listing_ids" name="listing_ids" type="hidden" value="" /><label id="ajax_msg_actions">&nbsp;</label></div>
  <button id="retirer" type="button" class="user_desactiver">Retirer</button> (date de sortie au <?php echo TODAY_FR ?>).<br />
  <button id="reintegrer" type="button" class="user_ajouter">Réintégrer</button> (retrait de la date de sortie).<br />
  <button id="supprimer" type="button" class="supprimer">Supprimer</button> sans attendre 3 ans (uniquement si déjà sortis).
</div>

<form action="#" method="post" id="form_gestion" class="hide">
  <h2>Ajouter | Modifier un utilisateur</h2>
  <p>
    <label class="tab" for="f_id_ent">Id. ENT <img alt="" src="./_img/bulle_aide.png" title="Uniquement en cas d'identification via un ENT." /> :</label><input id="f_id_ent" name="f_id_ent" type="text" value="" size="30" maxlength="63" /><br />
    <label class="tab" for="f_id_gepi">Id. GEPI <img alt="" src="./_img/bulle_aide.png" title="Uniquement en cas d'utilisation du logiciel GEPI." /> :</label><input id="f_id_gepi" name="f_id_gepi" type="text" value="" size="30" maxlength="63" /><br />
    <label class="tab" for="f_sconet_id">Id Sconet <img alt="" src="./_img/bulle_aide.png" title="Champ de Sconet PERSONNE.PERSONNE_ID (laisser vide ou à 0 si inconnu)." /> :</label><input id="f_sconet_id" name="f_sconet_id" type="text" value="" size="15" maxlength="8" /><br />
    <label class="tab" for="f_reference">Référence <img alt="" src="./_img/bulle_aide.png" title="Sconet : champ inutilisé (laisser vide).<br />Tableur : référence dans l'établissement." /> :</label><input id="f_reference" name="f_reference" type="text" value="" size="15" maxlength="11" />
  </p>
  <p>
    <label class="tab" for="f_profil">Profil :</label><select id="f_profil" name="f_profil"><?php echo $options ?></select>
  </p>
  <p>
    <label class="tab" for="f_nom">Nom :</label><input id="f_nom" name="f_nom" type="text" value="" size="30" maxlength="25" /><br />
    <label class="tab" for="f_prenom">Prénom :</label><input id="f_prenom" name="f_prenom" type="text" value="" size="30" maxlength="25" />
  </p>
  <p>
    <label class="tab" for="f_login">Login :</label><input id="box_login" name="box_login" value="1" type="checkbox" checked /> <label for="box_login">automatique | inchangé</label><span><input id="f_login" name="f_login" type="text" value="" size="15" maxlength="20" /></span><br />
    <label class="tab" for="f_password">Mot de passe :</label><input id="box_password" name="box_password" value="1" type="checkbox" checked /> <label for="box_password">aléatoire | inchangé</label><span><input id="f_password" name="f_password" size="15" maxlength="20" type="text" value="" /></span>
  </p>
  <p>
    <label class="tab" for="f_sortie_date">Date de sortie :</label><input id="box_date" name="box_date" value="1" type="checkbox" /> <label for="box_date">sans objet</label><span><input id="f_sortie_date" name="f_sortie_date" size="8" type="text" value="" /><q class="date_calendrier" title="Cliquer sur cette image pour importer une date depuis un calendrier !"></q></span>
  </p>
  <p>
    <label class="tab"></label><input id="f_action" name="f_action" type="hidden" value="" /><input id="f_id" name="f_id" type="hidden" value="" /><input id="f_check" name="f_check" type="hidden" value="" /><button id="bouton_valider" type="button" class="valider">Valider.</button> <button id="bouton_annuler" type="button" class="annuler">Annuler.</button><label id="ajax_msg_gestion">&nbsp;</label>
  </p>
</form>

<div id="temp_td" class="hide"></div>
