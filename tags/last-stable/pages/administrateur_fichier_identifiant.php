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
$TITRE = "Importer / Imposer des identifiants";
?>

<?php
require(CHEMIN_DOSSIER_INCLUDE.'tableau_sso.php');

// Fabrication des éléments select du formulaire
$select_groupe = Form::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_regroupements_etabl(FALSE/*sans*/) , 'f_groupe' /*select_nom*/ , '' /*option_first*/ , FALSE /*selection*/ , 'regroupements' /*optgroup*/);
?>

<ul class="puce">
  <li><span class="astuce">Pour un traitement individuel on peut utiliser les pages de gestion [<a href="./index.php?page=administrateur_eleve&amp;section=gestion">Élèves</a>] [<a href="./index.php?page=administrateur_parent&amp;section=gestion">Parents</a>] [<a href="./index.php?page=administrateur_professeur&amp;section=gestion">Professeurs / Directeurs / Personnels</a>].</span></li>
  <li><span class="astuce">Les administrateurs ne se gèrent qu'individuellement depuis la page [<a href="./index.php?page=administrateur_administrateur">Gérer les administrateurs</a>].</span></li>
</ul>

<hr />

<form action="#" method="post" id="form_select">

  <fieldset>
    <label class="tab" for="f_choix_principal">Objectif :</label>
    <select id="f_choix_principal" name="f_choix_principal">
      <option value=""></option>
      <option value="new_loginmdp">Générer de nouveaux identifiants SACoche.</option>
      <option value="import_loginmdp">Importer / Imposer des identifiants SACoche.</option>
      <option value="import_id_lcs">Récupérer les identifiants du LCS.</option>
      <option value="import_id_argos">Récupérer les identifiants d'ARGOS.</option>
      <option value="import_id_ent_<?php echo $_SESSION['CONNEXION_MODE'] ?>">Importer / Imposer les identifiants d'un ENT.</option>
      <option value="import_id_gepi">Récupérer les identifiants de Gepi.</option>
    </select><br />
  </fieldset>

  <fieldset id="fieldset_new_loginmdp" class="hide">
    <hr />
    <p class="astuce">Les noms d'utilisateurs seront générés selon <a href="./index.php?page=administrateur_etabl_login">le format choisi</a>.</p>
    <table>
      <tr>
        <td class="nu" style="width:30em;text-align:left">
          <div><label class="tab" for="f_profil">Profil :</label><select id="f_profil" name="f_profil"><option value=""></option><option value="eleves">élèves</option><option value="parents">responsables légaux</option><option value="professeurs">professeurs</option><option value="directeurs">directeurs</option></select></div>
          <div><label class="tab" for="f_groupe">Regroupement :</label><?php echo $select_groupe ?></div>
          <div id="div_users" class="hide"><label class="tab" for="f_user">Utilisateur(s) :</label><span id="f_user" class="select_multiple"></span><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><br /><q class="cocher_rien" title="Tout décocher."></q></span></div>
        </td>
        <td id="td_bouton" class="nu" style="width:25em">
          <p><button id="generer_login" type="button" class="mdp_groupe">Générer de nouveaux noms d'utilisateurs.</button></p>
          <p><button id="generer_mdp" type="button" class="mdp_groupe">Générer de nouveaux mots de passe.</button></p>
        </td>
      </tr>
    </table>
  </fieldset>

  <fieldset id="fieldset_import_loginmdp" class="hide">
    <hr />
    <p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__force_login_mdp_tableur">DOC : Imposer identifiants SACoche avec un tableur</a></span></p>
    <p>Vous pouvez <button id="user_export" type="button" class="fichier_export">récupérer un fichier csv avec les noms / prénoms / logins actuels</button> (le mot de passe, crypté, ne peut être restitué).</p>
    <p>Modifiez les identifiants souhaités, puis indiquez ci-dessous le fichier <b>nom-du-fichier.csv</b> (ou <b>nom-du-fichier.txt</b>) obtenu que vous souhaitez importer.</p>
    <p><label class="tab" for="import_loginmdp">Envoyer le fichier :</label><button id="import_loginmdp" type="button" class="fichier_import">Parcourir...</button></p>
  </fieldset>

  <fieldset id="fieldset_import_id_lcs" class="hide">
    <hr />
    <p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_mode_identification__LCS">DOC : Intégration de SACoche dans un LCS</a></span></p>
    <?php
    if(IS_HEBERGEMENT_SESAMATH)
    {
      echo'<div class="danger">Cette fonctionnalité, réservée au paquet LCS-SACoche, est sans objet sur le serveur Sésamath !</div>';
    }
    else if(!is_file(CHEMIN_FICHIER_WS_LCS))
    {
      echo'<div class="danger">Le fichier &laquo;&nbsp;<b>'.FileSystem::fin_chemin(CHEMIN_FICHIER_WS_LCS).'</b>&nbsp;&raquo; uniquement présent dans le paquet LCS-SACoche n\'a pas été détecté !</div>';
    }
    else
    {
      echo'<button name="dupliquer" id="COPY_id_lcs_TO_id_ent" type="button" class="mdp_groupe">Récupérer l\'identifiant LCS</button> comme identifiant de l\'ENT pour tous les utilisateurs.';
    }
    ?>
  </fieldset>

  <fieldset id="fieldset_import_id_argos" class="hide">
    <hr />
    <p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_mode_identification__argos">DOC : Intégration de SACoche dans Argos</a></span></p>
    <?php
    if(IS_HEBERGEMENT_SESAMATH)
    {
      echo'<div class="danger">Cette fonctionnalité, réservée à l\'installation académique Argos, est sans objet sur le serveur Sésamath !</div>';
    }
    else if(!is_file(CHEMIN_FICHIER_WS_ARGOS))
    {
      echo'<div class="danger">Le fichier &laquo;&nbsp;<b>'.FileSystem::fin_chemin(CHEMIN_FICHIER_WS_ARGOS).'</b>&nbsp;&raquo; uniquement présent sur l\'installation académique Argos n\'a pas été détecté !</div>';
    }
    else
    {
      echo'<button name="dupliquer" id="COPY_id_argos_profs_TO_id_ent" type="button" class="mdp_groupe">Récupérer l\'identifiant Argos</button> comme identifiant de l\'ENT pour tous les professeurs &amp; directeurs.<br />'
         .'<button name="dupliquer" id="COPY_id_argos_eleves_TO_id_ent" type="button" class="mdp_groupe">Récupérer l\'identifiant Argos</button> comme identifiant de l\'ENT pour tous les élèves.<br />'
         .'<button name="dupliquer" id="COPY_id_argos_parents_TO_id_ent" type="button" class="mdp_groupe">Récupérer l\'identifiant Argos</button> comme identifiant de l\'ENT pour tous les responsables légaux.';
    }
    ?>
  </fieldset>

  <fieldset id="fieldset_import_id_ent_normal" class="hide">
    <hr />
    <div class="astuce">Vous devez commencer par sélectionner votre ENT depuis la page "<a href="./index.php?page=administrateur_etabl_connexion">Mode d'identification</a>".</div>
  </fieldset>

  <fieldset id="fieldset_import_id_ent_cas" class="hide">
    <hr />
    <h4>En important un fichier</h4>
    <ul class="puce">
      <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_mode_identification__<?php echo $_SESSION['CONNEXION_NOM'] ?>">DOC : <?php echo $tab_connexion_info[$_SESSION['CONNEXION_MODE']][$_SESSION['CONNEXION_DEPARTEMENT'].'|'.$_SESSION['CONNEXION_NOM']]['txt'] ?></a></span></li>
      <li>Importer le fichier <b>csv</b> provenant de l'ENT : <button id="import_ent" type="button" class="fichier_import">Parcourir...</button></li>
    </ul>
    <h4>En dupliquant un autre champ</h4>
    <ul class="puce">
      <li><button name="dupliquer" id="COPY_id_gepi_TO_id_ent" type="button" class="mdp_groupe">Dupliquer l'identifiant de Gepi enregistré</button> comme identifiant de l'ENT pour tous les utilisateurs.</li>
      <li><button name="dupliquer" id="COPY_login_TO_id_ent" type="button" class="mdp_groupe">Dupliquer le login de SACoche enregistré</button> comme identifiant de l'ENT pour tous les utilisateurs.</li>
    </ul>
  </fieldset>

  <fieldset id="fieldset_import_id_gepi" class="hide">
    <hr />
    <h4>En important un fichier</h4>
    <ul class="puce">
      <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__import_identifiant_Gepi_SACoche">DOC : Import des identifiants de Gepi dans SACoche.</a></span></li>
      <li>Importer le fichier <b>base_professeur_gepi.csv</b> issu de Gepi : <button id="import_gepi_profs" type="button" class="fichier_import">Parcourir...</button></li>
      <li>Importer le fichier <b>base_responsable_gepi.csv</b> issu de Gepi : <button id="import_gepi_parents" type="button" class="fichier_import">Parcourir...</button></li>
      <li>Importer le fichier <b>base_eleve_gepi.csv</b> issu de Gepi : <button id="import_gepi_eleves" type="button" class="fichier_import">Parcourir...</button></li>
    </ul>
    <h4>En dupliquant un autre champ</h4>
    <ul class="puce">
      <li><button name="dupliquer" id="COPY_id_ent_TO_id_gepi" type="button" class="mdp_groupe">Dupliquer l'identifiant de l'ENT enregistré</button> comme identifiant de Gepi pour tous les utilisateurs.</li>
      <li><button name="dupliquer" id="COPY_login_TO_id_gepi" type="button" class="mdp_groupe">Dupliquer le login de SACoche enregistré</button> comme identifiant de Gepi pour tous les utilisateurs.</li>
    </ul>
  </fieldset>

</form>

<hr />
<label id="ajax_msg">&nbsp;</label>
<div id="ajax_retour" class="p"></div>