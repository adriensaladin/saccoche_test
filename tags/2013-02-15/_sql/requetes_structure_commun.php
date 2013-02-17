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
 
// Extension de classe qui étend DB (pour permettre l'autoload)

// Ces méthodes ne concernent qu'une base STRUCTURE.
// Ces méthodes ne concernent que les requêtes communes à plusieurs profils et inclasssables facilement...

class DB_STRUCTURE_COMMUN extends DB
{

/**
 * Exécuter des requêtes MySQL
 *
 * Utilisé dans le cadre d'une restauration de sauvegarde
 *
 * @param string $requetes
 * @return void
 */
public static function DB_executer_requetes_MySQL($requetes)
{
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes );
}

/**
 * Récupérer les informations concernant les tables présentes dans la base
 *
 * Retourne une ligne par table, avec pour chacune les champs Engine / Version / Row_format / Rows / Avg_row_length / Data_length / Max_data_length / Index_length / Data_free / Auto_increment / Create_time / Update_time / Check_time / Collation / Checksum / Create_options / Comment
 *
 * @param void
 * @return array
 */
public static function DB_recuperer_tables_informations()
{
  $DB_SQL = 'SHOW TABLE STATUS ';
  $DB_SQL.= 'LIKE "sacoche_%" ';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Récupérer la commande MySQL pour créer une table existante
 *
 * Retourne un tableau a deux entrées : "Table" (le nom de la table) et "Create Table" (la commande MySQL).
 *
 * @param string $table_nom
 * @return array
 */
public static function DB_recuperer_table_structure($table_nom)
{
  $DB_SQL = 'SHOW CREATE TABLE '.$table_nom;
  return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Récupérer n lignes d'une table
 *
 * @param string $table_nom
 * @param int    $limit_depart
 * @param int    $limit_nombre
 * @return array
 */
public static function DB_recuperer_table_donnees($table_nom,$limit_depart,$limit_nombre)
{
  $DB_SQL = 'SELECT * ';
  $DB_SQL.= 'FROM '.$table_nom.' ';
  $DB_SQL.= 'LIMIT '.$limit_depart.','.$limit_nombre;
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Récupérer la valeur d'une variable système de MySQL
 *
 * Retourne un tableau a deux entrées : "Variable_name" (le nom de la variable) et "Value" (sa valeur).
 *
 * @param string   $variable_nom   max_allowed_packet | max_user_connections | group_concat_max_len
 * @return array
 */
public static function DB_recuperer_variable_MySQL($variable_nom)
{
  $DB_SQL = 'SHOW VARIABLES LIKE "'.$variable_nom.'"';
  return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Récupérer la version de MySQL
 *
 * Avec une connexion classique style mysql_connect() on peut utiliser mysql_get_server_info() .
 *
 * @param void
 * @return string
 */
public static function DB_recuperer_version_MySQL()
{
  $DB_SQL = 'SELECT VERSION()';
  return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * recuperer_dates_periode
 *
 * @param int    $groupe_id    id du groupe
 * @param int    $periode_id   id de la période
 * @return array
 */
public static function DB_recuperer_dates_periode($groupe_id,$periode_id)
{
  $DB_SQL = 'SELECT jointure_date_debut, jointure_date_fin ';
  $DB_SQL.= 'FROM sacoche_jointure_groupe_periode ';
  $DB_SQL.= 'WHERE groupe_id=:groupe_id AND periode_id=:periode_id ';
  $DB_VAR = array(':groupe_id'=>$groupe_id,':periode_id'=>$periode_id);
  return DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Retourner l'arborescence d'un référentiel (tableau issu de la requête SQL)
 * + pour une matière donnée / pour toutes les matières d'un professeur donné
 * + pour un niveau donné / pour tous les niveaux concernés
 *
 * @param int  $prof_id      passer 0 pour une recherche sur toutes les matières de l'établissement (profil directeur) plutôt que d'un prof donné
 * @param int  $matiere_id   passer 0 pour une recherche sur toutes les matières d'un prof plutôt que sur une matière
 * @param int  $niveau_id    passer 0 pour une recherche sur tous les niveaux
 * @param bool $only_socle   "TRUE" pour ne retourner que les items reliés au socle
 * @param bool $only_item    "TRUE" pour ne retourner que les lignes d'items, "FALSE" pour l'arborescence complète, sans forcément descendre jusqu'à l'items (valeurs NULL retournées)
 * @param bool $socle_nom    avec ou pas le nom des items du socle associés
 * @return array
 */
public static function DB_recuperer_arborescence($prof_id,$matiere_id,$niveau_id,$only_socle,$only_item,$socle_nom)
{
  $select_socle_nom  = ($socle_nom)  ? 'entree_id,entree_nom ' : 'entree_id ' ;
  $join_user_matiere = ($prof_id)    ? 'LEFT JOIN sacoche_jointure_user_matiere USING (matiere_id) ' : '' ;
  $join_socle_item   = ($socle_nom)  ? 'LEFT JOIN sacoche_socle_entree USING (entree_id) ' : '' ;
  $where_user        = ($prof_id)    ? 'AND user_id=:user_id ' : '' ;
  $where_matiere     = ($matiere_id) ? 'AND matiere_id=:matiere_id ' : '' ;
  $where_niveau      = ($niveau_id)  ? 'AND niveau_id=:niveau_id ' : 'AND niveau_actif=1 ' ;
  $where_item        = ($only_item)  ? 'AND item_id IS NOT NULL ' : '' ;
  $where_socle       = ($only_socle) ? 'AND entree_id !=0 ' : '' ;
  $order_matiere     = ($matiere_id) ? 'matiere_nom ASC, ' : '' ;
  $order_niveau      = (!$niveau_id) ? 'niveau_ordre ASC, ' : '' ;
  $DB_SQL = 'SELECT ';
  $DB_SQL.= 'matiere_id, matiere_ref, matiere_nom, ';
  $DB_SQL.= 'niveau_id, niveau_ref, niveau_nom, ';
  $DB_SQL.= 'domaine_id, domaine_ordre, domaine_ref, domaine_nom, ';
  $DB_SQL.= 'theme_id, theme_ordre, theme_nom, ';
  $DB_SQL.= 'item_id, item_ordre, item_nom, item_coef, item_cart, item_lien, ';
  $DB_SQL.= $select_socle_nom;
  $DB_SQL.= 'FROM sacoche_referentiel ';
  $DB_SQL.= $join_user_matiere;
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (matiere_id,niveau_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (domaine_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (theme_id) ';
  $DB_SQL.= $join_socle_item;
  $DB_SQL.= 'WHERE matiere_active=1 '.$where_user.$where_matiere.$where_niveau.$where_item.$where_socle;
  $DB_SQL.= 'ORDER BY '.$order_matiere.$order_niveau.'domaine_ordre ASC, theme_ordre ASC, item_ordre ASC';
  $DB_VAR = array(':user_id'=>$prof_id,':matiere_id'=>$matiere_id,':niveau_id'=>$niveau_id);
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * recuperer_arborescence_palier
 *
 * @param int   $palier_id (facultatif ; les paliers de l'établissement sinon
 * @return array
 */
public static function DB_recuperer_arborescence_palier($palier_id=FALSE)
{
  $DB_SQL = 'SELECT * ';
  $DB_SQL.= 'FROM sacoche_socle_palier ';
  $DB_SQL.= 'LEFT JOIN sacoche_socle_pilier USING (palier_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_socle_section USING (pilier_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_socle_entree USING (section_id) ';
  $DB_SQL.= ($palier_id) ? 'WHERE palier_id='.$palier_id.' ' : 'WHERE palier_actif=1 ' ;
  $DB_SQL.= 'ORDER BY pilier_ordre ASC, section_ordre ASC, entree_ordre ASC';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * recuperer_groupe_nom
 *
 * @param int   $groupe_id
 * @return string
 */
public static function DB_recuperer_groupe_nom($groupe_id)
{
  $DB_SQL = 'SELECT groupe_nom ';
  $DB_SQL.= 'FROM sacoche_groupe ';
  $DB_SQL.= 'WHERE groupe_id=:groupe_id ';
  $DB_VAR = array(':groupe_id'=>$groupe_id);
  return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Drecuperer_matieres_professeur
 *
 * @param int $user_id
 * @return string
 */
public static function DB_recuperer_matieres_professeur($user_id)
{
  // Lever si besoin une limitation de GROUP_CONCAT (group_concat_max_len est par défaut limité à une chaine de 1024 caractères) ; éviter plus de 8096 (http://www.glpi-project.org/forum/viewtopic.php?id=23767).
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'SET group_concat_max_len = 8096');
  $DB_SQL = 'SELECT GROUP_CONCAT(matiere_id SEPARATOR ",") AS listing_matieres_id ';
  $DB_SQL.= 'FROM sacoche_jointure_user_matiere ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= 'WHERE user_id=:user_id AND matiere_active=1 ';
  $DB_VAR = array(':user_id'=>$user_id);
  return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_niveaux_etablissement
 *
 * @param bool $with_specifiques
 * @return array
 */
public static function DB_lister_niveaux_etablissement($with_specifiques)
{
  $DB_SQL = 'SELECT niveau_id, niveau_ordre, niveau_ref, code_mef, niveau_nom ';
  $DB_SQL.= 'FROM sacoche_niveau ';
  $DB_SQL.= ($with_specifiques) ? '' : 'LEFT JOIN sacoche_niveau_famille USING (niveau_famille_id) ';
  $DB_SQL.= 'WHERE niveau_actif=1 ';
  $DB_SQL.= ($with_specifiques) ? '' : 'AND niveau_famille_categorie=1 ';
  $DB_SQL.= 'ORDER BY niveau_ordre ASC';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_identite_coordonnateurs_par_matiere
 *
 * @param void
 * @return array   matiere_id et coord_liste avec identités séparées par "]["
 */
public static function DB_lister_identite_coordonnateurs_par_matiere()
{
  // Lever si besoin une limitation de GROUP_CONCAT (group_concat_max_len est par défaut limité à une chaine de 1024 caractères) ; éviter plus de 8096 (http://www.glpi-project.org/forum/viewtopic.php?id=23767).
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'SET group_concat_max_len = 8096');
  $DB_SQL = 'SELECT matiere_id, GROUP_CONCAT(CONCAT(user_nom," ",user_prenom) SEPARATOR "][") AS coord_liste ';
  $DB_SQL.= 'FROM sacoche_jointure_user_matiere ';
  $DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= 'WHERE matiere_active=1 AND jointure_coord=:coord AND user_sortie_date>NOW() '; // Test matiere car un prof peut être encore relié à des matières décochées par l'admin.
  $DB_SQL.= 'GROUP BY matiere_id';
  $DB_VAR = array(':coord'=>1);
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_users_regroupement
 *
 * @param string $profil        eleve | parent | professeur | personnel | directeur | administrateur
 * @param int    $statut        1 pour actuel, 0 pour ancien
 * @param string $groupe_type   all | sdf | niveau | classe | groupe | besoin
 * @param int    $groupe_id     id du niveau ou de la classe ou du groupe
 * @param string $champs        par défaut user_id,user_nom,user_prenom
 * @return array
 */
public static function DB_lister_users_regroupement($profil,$statut,$groupe_type,$groupe_id,$champs='user_id,user_nom,user_prenom')
{
  $as      = ($profil!='parent') ? '' : ' AS enfant' ;
  $prefixe = ($profil!='parent') ? 'sacoche_user.' : 'enfant.' ;
  $test_date_sortie = ($statut) ? 'user_sortie_date>NOW()' : 'user_sortie_date<NOW()' ; // Pas besoin de tester l'égalité, NOW() renvoyant un datetime
  $from  = 'FROM sacoche_user'.$as.' ' ; // Peut être modifié ensuite (requête optimisée si on commence par une autre table)
  $ljoin = '';
  $where = 'WHERE sacoche_user_profil.user_profil_type=:profil_type AND '.$prefixe.$test_date_sortie.' ';
  $group = ($profil!='parent') ? 'GROUP BY user_id ' : 'GROUP BY parent.user_id ' ;
  if(in_array($profil,array('directeur','administrateur')))
  {
    $ljoin .= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  }
  else
  {
    switch ($groupe_type)
    {
      case 'all' :  // On veut tous les users de l'établissement
        $ljoin .= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
        switch ($profil)
        {
          case 'professeur' :
            $where .= 'AND sacoche_user_profil.user_profil_join_groupes=:join_groupes ';
            break;
          case 'personnel' :
            $where .= 'AND sacoche_user_profil.user_profil_join_groupes!=:join_groupes ';
            break;
        }
        break;
      case 'sdf' :  // On veut les users non affectés dans une classe (élèves seulement)
        $ljoin .= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
        $where .= 'AND '.$prefixe.'eleve_classe_id=:classe ';
        break;
      case 'niveau' :  // On veut tous les users d'un niveau
        switch ($profil)
        {
          case 'eleve' :
          case 'parent' :
            $from   = 'FROM sacoche_groupe ';
            $ljoin .= 'LEFT JOIN sacoche_user'.$as.' ON sacoche_groupe.groupe_id='.$prefixe.'eleve_classe_id ';
            $ljoin .= 'LEFT JOIN sacoche_user_profil ON '.$prefixe.'user_profil_sigle=sacoche_user_profil.user_profil_sigle ';
            $where .= 'AND niveau_id=:niveau ';
            break;
          case 'professeur' :
            $from   = 'FROM sacoche_groupe ';
            $ljoin .= 'LEFT JOIN sacoche_jointure_user_groupe USING (groupe_id) ';
            $ljoin .= 'LEFT JOIN sacoche_user USING (user_id) ';
            $ljoin .= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
            $where .= 'AND niveau_id=:niveau ';
            $where .= 'AND sacoche_user_profil.user_profil_join_groupes=:join_groupes ';
            break;
          case 'personnel' :
            $ljoin .= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
            $where .= 'AND sacoche_user_profil.user_profil_join_groupes!=:join_groupes ';
            break;
        }
        break;
      case 'classe' :  // On veut tous les users d'une classe
        switch ($profil)
        {
          case 'eleve' :
          case 'parent' :
            $ljoin .= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
            $where .= 'AND '.$prefixe.'eleve_classe_id=:groupe ';
            break;
          case 'professeur' :
            $from   = 'FROM sacoche_jointure_user_groupe ';
            $ljoin .= 'LEFT JOIN sacoche_user USING (user_id) ';
            $ljoin .= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
            $where .= 'AND groupe_id=:groupe ';
            $where .= 'AND sacoche_user_profil.user_profil_join_groupes=:join_groupes ';
            break;
          case 'personnel' :
            $ljoin .= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
            $where .= 'AND sacoche_user_profil.user_profil_join_groupes!=:join_groupes ';
            break;
        }
        break;
      case 'groupe' :  // On veut tous les users d'un groupe
      case 'besoin' :  // On veut tous les users d'un groupe de besoin (élèves | parents seulements)
      case 'eval'   :  // On veut tous les users d'un groupe utilisé pour une évaluation (élèves seulements)
        switch ($profil)
        {
          case 'eleve' :
          case 'parent' :
          case 'professeur' :
            $from   = 'FROM sacoche_jointure_user_groupe ';
            $ljoin .= 'LEFT JOIN sacoche_user'.$as.' USING (user_id) ';
            $ljoin .= 'LEFT JOIN sacoche_user_profil ON '.$prefixe.'user_profil_sigle=sacoche_user_profil.user_profil_sigle ';
            $where .= 'AND groupe_id=:groupe ';
            $where .= 'AND sacoche_user_profil.user_profil_join_groupes=:join_groupes ';
            break;
          case 'personnel' :
            $ljoin .= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
            $where .= 'AND sacoche_user_profil.user_profil_join_groupes!=:join_groupes ';
            break;
        }
        break;
    }
  }
  if($profil=='parent')
  {
    // INNER JOIN pour obliger une jointure avec un parent
    $ljoin .= 'INNER JOIN sacoche_jointure_parent_eleve ON enfant.user_id=sacoche_jointure_parent_eleve.eleve_id ';
    $ljoin .= 'INNER JOIN sacoche_user AS parent ON sacoche_jointure_parent_eleve.parent_id=parent.user_id ';
    $where .= 'AND parent.user_sortie_date>NOW() ';
  }
  // On peut maintenant assembler les morceaux de la requête !
  $DB_SQL = 'SELECT '.$champs.' '.$from.$ljoin.$where.$group.'ORDER BY '.$prefixe.'user_nom ASC, '.$prefixe.'user_prenom ASC';
  $DB_VAR = array( ':profil_type'=>str_replace(array('parent','personnel'),array('eleve','professeur'),$profil) , ':join_groupes'=>'config' , ':groupe'=>$groupe_id , ':niveau'=>$groupe_id , ':classe'=>0 ) ;
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_eleves_classe_et_groupe
 * Utilisé dans le cas particulier des bilans officiels
 *
 * @param int   $classe_id
 * @param int   $groupe_id
 * @return array
 */
public static function DB_lister_eleves_classe_et_groupe($classe_id,$groupe_id)
{
  $DB_SQL = 'SELECT user_id, user_nom, user_prenom ';
  $DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  $DB_SQL.= 'WHERE groupe_id=:groupe AND eleve_classe_id=:classe AND user_profil_type=:profil_type AND user_sortie_date>NOW() ';
  $DB_SQL.= 'ORDER BY user_nom ASC, user_prenom ASC ';
  $DB_VAR = array( ':groupe'=>$groupe_id , ':classe'=>$classe_id , ':profil_type'=>'eleve' );
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_referentiels_infos_details_matieres_niveaux
 *
 * @param int    $matiere_id   0 par défaut pour toutes les matières
 * @param int    $niveau_id    0 par défaut pour tous les niveaux
 * @return array
 */
public static function DB_lister_referentiels_infos_details_matieres_niveaux( $matiere_id=0 , $niveau_id=0 )
{
  $DB_SQL = 'SELECT matiere_id, niveau_id, niveau_nom, referentiel_partage_etat, referentiel_partage_date, referentiel_calcul_methode, referentiel_calcul_limite, referentiel_calcul_retroactif ';
  $DB_SQL.= 'FROM sacoche_referentiel ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= ($matiere_id) ? 'WHERE matiere_id='.$matiere_id.' ' : 'WHERE matiere_active=1 ' ; // Test matiere car un prof peut être encore relié à des matières décochées par l'admin.
  $DB_SQL.= ($niveau_id)  ? 'AND niveau_id='.$niveau_id.' '     : 'AND niveau_actif=1 ' ;
  $DB_SQL.= 'ORDER BY matiere_id ASC, niveau_ordre ASC';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * lister_messages_user_auteur
 *
 * @param int    $user_id
 * @return array
 */
public static function DB_lister_messages_user_auteur($user_id)
{
  $DB_SQL = 'SELECT message_id, message_debut_date, message_fin_date, message_destinataires, message_contenu ';
  $DB_SQL.= 'FROM sacoche_message ';
  $DB_SQL.= 'WHERE user_id=:user_id ';
  $DB_SQL.= 'ORDER BY message_fin_date DESC, message_debut_date DESC';
  $DB_VAR = array(':user_id'=>$user_id);
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_messages_user_destinataire
 *
 * @param int    $user_id
 * @return array
 */
public static function DB_lister_messages_user_destinataire($user_id)
{
  $DB_SQL = 'SELECT message_id,user_nom, user_prenom, message_contenu,message_dests_cache ';
  $DB_SQL.= 'FROM sacoche_message ';
  $DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
  $DB_SQL.= 'WHERE message_destinataires LIKE :user_id_like AND message_debut_date<NOW() AND DATE_ADD(message_fin_date,INTERVAL 1 DAY)>NOW() '; // NOW() renvoie un datetime
  $DB_SQL.= 'ORDER BY message_debut_date DESC, message_fin_date ASC';
  $DB_VAR = array(':user_id_like'=>'%,'.$user_id.',%');
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * lister_jointure_groupe_periode ; le rangement par ordre de période permet, si les périodes se chevauchent, que javascript choisisse la 1ère par défaut
 *
 * @param string   $listing_groupes_id   id des groupes séparés par des virgules
 * @return array
 */
public static function DB_lister_jointure_groupe_periode($listing_groupes_id)
{
  $DB_SQL = 'SELECT sacoche_jointure_groupe_periode.* ';
  $DB_SQL.= 'FROM sacoche_jointure_groupe_periode ';
  $DB_SQL.= 'LEFT JOIN sacoche_periode USING (periode_id) ';
  $DB_SQL.= 'WHERE groupe_id IN ('.$listing_groupes_id.') ';
  $DB_SQL.= 'ORDER BY periode_ordre ASC';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * ajouter_utilisateur
 *
 * @param string $user_sconet_id
 * @param string $user_sconet_elenoet
 * @param string $user_reference
 * @param string $user_profil_sigle
 * @param string $user_nom
 * @param string $user_prenom
 * @param string $user_login
 * @param string $password_crypte
 * @param int    $eleve_classe_id   facultatif, 0 si pas de classe ou profil non élève
 * @param string $user_id_ent       facultatif
 * @param string $user_id_gepi      facultatif
 * @return int
 */
public static function DB_ajouter_utilisateur($user_sconet_id,$user_sconet_elenoet,$user_reference,$user_profil_sigle,$user_nom,$user_prenom,$user_login,$password_crypte,$eleve_classe_id=0,$user_id_ent='',$user_id_gepi='')
{
  $DB_SQL = 'INSERT INTO sacoche_user(user_sconet_id,user_sconet_elenoet,user_reference,user_profil_sigle,user_nom,user_prenom,user_login,user_password,eleve_classe_id,user_id_ent,user_id_gepi) ';
  $DB_SQL.= 'VALUES(:user_sconet_id,:user_sconet_elenoet,:user_reference,:user_profil_sigle,:user_nom,:user_prenom,:user_login,:password_crypte,:eleve_classe_id,:user_id_ent,:user_id_gepi)';
  $DB_VAR = array(':user_sconet_id'=>$user_sconet_id,':user_sconet_elenoet'=>$user_sconet_elenoet,':user_reference'=>$user_reference,':user_profil_sigle'=>$user_profil_sigle,':user_nom'=>$user_nom,':user_prenom'=>$user_prenom,':user_login'=>$user_login,':password_crypte'=>$password_crypte,':eleve_classe_id'=>$eleve_classe_id,':user_id_ent'=>$user_id_ent,':user_id_gepi'=>$user_id_gepi);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * ajouter_message
 *
 * @param int    $user_id
 * @param string $date_debut_mysql
 * @param string $date_fin_mysql
 * @param string $message_contenu
 * @param string $tab_destinataires
 * @return int
 */
public static function DB_ajouter_message($user_id,$date_debut_mysql,$date_fin_mysql,$message_contenu,$tab_destinataires)
{
  $listing_destinataires = count($tab_destinataires) ? ','.implode(',',$tab_destinataires).',' : '' ;
  $DB_SQL = 'INSERT INTO sacoche_message(user_id,message_debut_date,message_fin_date,message_destinataires,message_contenu,message_dests_cache) ';
  $DB_SQL.= 'VALUES(:user_id,:message_debut_date,:message_fin_date,:message_destinataires,:message_contenu,:message_dests_cache)';
  $DB_VAR = array(':user_id'=>$user_id,':message_debut_date'=>$date_debut_mysql,':message_fin_date'=>$date_fin_mysql,':message_destinataires'=>$listing_destinataires,':message_contenu'=>$message_contenu,':message_dests_cache'=>',');
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * modifier_parametres
 *
 *      modifier_matieres_partagees
 * On ne défait pas pour autant les liaisons avec les enseignants... simplement elles n'apparaitront plus dans les formulaires.
 * Idem pour les jointures avec les référentiels : ainsi les scores des élèves demeurent conservés.
 *     modifier_niveaux
 * On ne défait pas pour autant les liaisons avec les groupes... simplement ils n'apparaitront plus dans les formulaires.
 * Idem pour les jointures avec les référentiels : ainsi les scores des élèves demeurent conservés.
 *     modifier_paliers
 * On ne défait pas pour autant les jointures avec les référentiels : ainsi les scores des élèves demeurent conservés.
 *
 * @param array tableau $parametre_nom => $parametre_valeur des paramètres à modfifier
 * @return void
 */
public static function DB_modifier_parametres($tab_parametres)
{
  $DB_SQL = 'UPDATE sacoche_parametre ';
  $DB_SQL.= 'SET parametre_valeur=:parametre_valeur ';
  $DB_SQL.= 'WHERE parametre_nom=:parametre_nom ';
  foreach($tab_parametres as $parametre_nom => $parametre_valeur)
  {
    $DB_VAR = array(':parametre_nom'=>$parametre_nom,':parametre_valeur'=>$parametre_valeur);
    DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  }
}

/**
 * Modifier son paramètre daltonisme
 *
 * @param int   $user_id
 * @param 0|1   $user_daltonisme
 * @return void
 */
public static function DB_modifier_user_daltonisme($user_id,$user_daltonisme)
{
  $DB_SQL = 'UPDATE sacoche_user ';
  $DB_SQL.= 'SET user_daltonisme=:user_daltonisme ';
  $DB_SQL.= 'WHERE user_id=:user_id ';
  $DB_VAR = array(':user_id'=>$user_id,':user_daltonisme'=>$user_daltonisme);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Modifier sa configuration de la page d'accueil
 *
 * @param int   $user_id
 * @param 0|1   $user_param_accueil
 * @return void
 */
public static function DB_modifier_user_param_accueil($user_id,$user_param_accueil)
{
  $DB_SQL = 'UPDATE sacoche_user ';
  $DB_SQL.= 'SET user_param_accueil=:user_param_accueil ';
  $DB_SQL.= 'WHERE user_id=:user_id ';
  $DB_VAR = array(':user_id'=>$user_id,':user_param_accueil'=>$user_param_accueil);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_mdp_utilisateur
 *
 * @param int    $user_id
 * @param string $password_ancien_crypte
 * @param string $password_nouveau_crypte
 * @return string   'ok' ou 'Le mot de passe actuel est incorrect !'
 */
public static function DB_modifier_mdp_utilisateur($user_id,$password_ancien_crypte,$password_nouveau_crypte)
{
  // Tester si l'ancien mot de passe correspond à celui enregistré
  $DB_SQL = 'SELECT user_id ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'WHERE user_id=:user_id AND user_password=:password_crypte ';
  $DB_VAR = array(':user_id'=>$user_id,':password_crypte'=>$password_ancien_crypte);
  $DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  if(empty($DB_ROW))
  {
    return 'Le mot de passe actuel est incorrect !';
  }
  // Remplacer par le nouveau mot de passe
  $DB_SQL = 'UPDATE sacoche_user ';
  $DB_SQL.= 'SET user_password=:password_crypte ';
  $DB_SQL.= 'WHERE user_id=:user_id ';
  $DB_VAR = array(':user_id'=>$user_id,':password_crypte'=>$password_nouveau_crypte);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return 'ok';
}

/**
 * modifier_DB_modifier_message
 * Rmq : à chaque modification de message, le champ "message_dests_cache" est réinitialisé ; ce n'est pas une limitation mais bien un comportement souhaité (c'est potentiellement un message différent, donc on le rend visible).
 *
 * @param int    $message_id
 * @param int    $user_id
 * @param string $date_debut_mysql
 * @param string $date_fin_mysql
 * @param string $message_contenu
 * @param string $tab_destinataires
 * @return void
 */
public static function DB_modifier_message($message_id,$user_id,$date_debut_mysql,$date_fin_mysql,$message_contenu,$tab_destinataires)
  {
  $listing_destinataires = count($tab_destinataires) ? ','.implode(',',$tab_destinataires).',' : '' ;
  $DB_SQL = 'UPDATE sacoche_message ';
  $DB_SQL.= 'SET message_debut_date=:message_debut_date, message_fin_date=:message_fin_date, message_destinataires=:message_destinataires, message_contenu=:message_contenu, message_dests_cache=:message_dests_cache ';
  $DB_SQL.= 'WHERE message_id=:message_id AND user_id=:user_id ';
  $DB_VAR = array(':message_debut_date'=>$date_debut_mysql,':message_fin_date'=>$date_fin_mysql,':message_destinataires'=>$listing_destinataires,':message_contenu'=>$message_contenu,':message_dests_cache'=>',',':message_id'=>$message_id,':user_id'=>$user_id);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * modifier_message_dests_cache
 *
 * @param int    $message_id
 * @param int    $user_id
 * @param bool   $etat   FALSE pour masquer | TRUE ou voir
 * @return void
 */
public static function DB_modifier_message_dests_cache($message_id,$user_id,$etat)
{
  $commande = ($etat) ? 'REPLACE(message_dests_cache,CONCAT(",",:user_id,","),",")' : 'CONCAT(message_dests_cache,:user_id,",")' ; // Attention : ne pas mettre d'espaces !
  $DB_SQL = 'UPDATE sacoche_message ';
  $DB_SQL.= 'SET message_dests_cache = '.$commande.' ';
  $DB_SQL.= 'WHERE message_id=:message_id ';
  $DB_VAR = array(':message_id'=>$message_id,':user_id'=>$user_id);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * supprimer_message
 *
 * @param int   $message_id
 * @param int   $user_id
 * @return void
 */
public static function DB_supprimer_message($message_id,$user_id)
{
  $DB_SQL = 'DELETE FROM sacoche_message ';
  $DB_SQL.= 'WHERE message_id=:message_id AND user_id=:user_id ';
  $DB_VAR = array(':message_id'=>$message_id,':user_id'=>$user_id);
  DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Créer les tables de la base d'une structure et les remplir
 *
 * @param void
 * @return void
 */
public static function DB_creer_remplir_tables_structure()
{
  $tab_files = FileSystem::lister_contenu_dossier(CHEMIN_DOSSIER_SQL_STRUCTURE);
  foreach($tab_files as $file)
  {
    $extension = pathinfo($file,PATHINFO_EXTENSION);
    if($extension=='sql')
    {
      $requetes = file_get_contents(CHEMIN_DOSSIER_SQL_STRUCTURE.$file);
      DB::query(SACOCHE_STRUCTURE_BD_NAME , $requetes ); // Attention, sur certains LCS ça bloque au dela de 40 instructions MySQL (mais un INSERT multiple avec des milliers de lignes ne pose pas de pb).
      /*
      La classe PDO a un bug. Si on envoie plusieurs requêtes d'un coup ça passe, mais si on recommence juste après alors on récolte : "Cannot execute queries while other unbuffered queries are active.  Consider using PDOStatement::fetchAll().  Alternatively, if your code is only ever going to run against mysql, you may enable query buffering by setting the PDO::MYSQL_ATTR_USE_BUFFERED_QUERY attribute."
      La seule issue est de fermer la connexion après chaque requête multiple en utilisant exceptionnellement la méthode ajouté par SebR suite à mon signalement : DB::close(nom_de_la_connexion);
      */
      DB::close(SACOCHE_STRUCTURE_BD_NAME);
    }
  }
}

/**
 * Retourner un tableau [valeur texte] des matières de l'établissement (communes choisies ou spécifiques ajoutées)
 *
 * @param void
 * @return array|string
 */
public static function DB_OPT_matieres_etabl()
{
  $DB_SQL = 'SELECT matiere_id AS valeur, matiere_nom AS texte ';
  $DB_SQL.= 'FROM sacoche_matiere ';
  $DB_SQL.= 'WHERE matiere_active=1 ';
  $DB_SQL.= 'ORDER BY matiere_nom ASC';
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  return !empty($DB_TAB) ? $DB_TAB : 'Aucune matière n\'est rattachée à l\'établissement.' ;
}

/**
 * Retourner un tableau [valeur texte optgroup] des familles de matières
 *
 * @param void
 * @return array
 */
public static function DB_OPT_familles_matieres()
{
  Form::$tab_select_optgroup = array( 1=>'Enseignements usuels' , 2=>'Enseignements généraux' , 3=>'Enseignements spécifiques' );
  $DB_SQL = 'SELECT matiere_famille_id AS valeur, matiere_famille_nom AS texte, matiere_famille_categorie AS optgroup ';
  $DB_SQL.= 'FROM sacoche_matiere_famille ';
  $DB_SQL.= 'ORDER BY matiere_famille_categorie ASC, matiere_famille_nom ASC';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Retourner un tableau [valeur texte] des matières communes d'une famille donnée
 * optgroup sert à pouvoir regrouper les options
 *
 * @param int   matiere_famille_id
 * @return array
 */
public static function DB_OPT_matieres_famille($matiere_famille_id)
{
  Form::$tab_select_option_first = array(ID_MATIERE_PARTAGEE_MAX+$matiere_famille_id,'Toutes les matières de cette famille','');
  $DB_SQL = 'SELECT matiere_id AS valeur, matiere_nom AS texte ';
  $DB_SQL.= 'FROM sacoche_matiere ';
  $DB_SQL.= ($matiere_famille_id==ID_FAMILLE_MATIERE_USUELLE) ? 'WHERE matiere_usuelle=1 ' : 'WHERE matiere_famille_id='.$matiere_famille_id.' ' ;
  $DB_SQL.= 'ORDER BY matiere_nom ASC';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Retourner un tableau [valeur texte info] des matières du professeur identifié ; info représente le nb de demandes (utilisé par ailleurs)
 *
 * @param int $user_id
 * @return array|string
 */
public static function DB_OPT_matieres_professeur($user_id)
{
  Form::$tab_select_option_first = array(0,'Toutes les matières','');
  $DB_SQL = 'SELECT matiere_id AS valeur, matiere_nom AS texte, matiere_nb_demandes AS info ';
  $DB_SQL.= 'FROM sacoche_jointure_user_matiere ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= 'WHERE user_id=:user_id AND matiere_active=1 ';
  $DB_SQL.= 'ORDER BY matiere_nom ASC';
  $DB_VAR = array(':user_id'=>$user_id);
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return !empty($DB_TAB) ? $DB_TAB : 'Vous n\'êtes rattaché à aucune matière.' ;
}

/**
 * Retourner un tableau [valeur texte info] des matières d'un élève identifié ; info représente le nb de demandes (utilisé par ailleurs)
 *
 * @param int $user_id
 * @return array|string
 */
public static function DB_OPT_matieres_eleve($user_id)
{
  // Lever si besoin une limitation de GROUP_CONCAT (group_concat_max_len est par défaut limité à une chaine de 1024 caractères) ; éviter plus de 8096 (http://www.glpi-project.org/forum/viewtopic.php?id=23767).
  DB::query(SACOCHE_STRUCTURE_BD_NAME , 'SET group_concat_max_len = 8096');
  // On connait la classe ($_SESSION['ELEVE_CLASSE_ID']), donc on commence par récupérer les groupes éventuels associés à l'élève
  $DB_SQL = 'SELECT GROUP_CONCAT(DISTINCT groupe_id SEPARATOR ",") AS sacoche_liste_groupe_id ';
  $DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
  $DB_SQL.= 'WHERE user_id=:user_id AND groupe_type=:type2 ';
  $DB_SQL.= 'GROUP BY user_id ';
  $DB_VAR = array(':user_id'=>$user_id,':type2'=>'groupe');
  $liste_groupes_id = DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  if( (!$_SESSION['ELEVE_CLASSE_ID']) && (!$liste_groupes_id) )
  {
    // élève sans classe et sans groupe
    return 'Aucune classe ni aucun groupe ne vous est affecté !';
  }
  if(!$liste_groupes_id)
  {
    $liste_groupes = $_SESSION['ELEVE_CLASSE_ID'];
  }
  elseif(!$_SESSION['ELEVE_CLASSE_ID'])
  {
    $liste_groupes = $liste_groupes_id;
  }
  else
  {
    $liste_groupes = $_SESSION['ELEVE_CLASSE_ID'].','.$liste_groupes_id;
  }
  // Ensuite on récupère les matières des professeurs (actuels !) qui sont associés à la liste des groupes récupérés
  $DB_SQL = 'SELECT matiere_id AS valeur, matiere_nom AS texte, matiere_nb_demandes AS info ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_user_groupe USING (user_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_user_matiere USING (user_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= 'WHERE groupe_id IN('.$liste_groupes.') AND user_sortie_date>NOW() AND matiere_active=1 ';
  $DB_SQL.= 'GROUP BY matiere_id ';
  $DB_SQL.= 'ORDER BY matiere_nom ASC';
  $DB_VAR = array(':partage'=>0);
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return !empty($DB_TAB) ? $DB_TAB : 'Vous n\'avez pas de professeur rattaché à une matière !' ;
}

/**
 * Retourner un tableau [valeur texte] des matières d'une classe ou d'un groupe
 *
 * @param int $groupe_id     id de la classe ou du groupe
 * @return array|string
 */
public static function DB_OPT_matieres_groupe($groupe_id)
{
  // On récupère les matières des professeurs qui sont associés au groupe
  $DB_SQL = 'SELECT matiere_id AS valeur, matiere_nom AS texte ';
  $DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  $DB_SQL.= 'LEFT JOIN sacoche_jointure_user_matiere USING (user_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
  $DB_SQL.= 'WHERE groupe_id=:groupe_id AND user_profil_type=:profil_type ';
  $DB_SQL.= 'GROUP BY matiere_id ';
  $DB_SQL.= 'ORDER BY matiere_nom ASC';
  $DB_VAR = array(':groupe_id'=>$groupe_id,':profil_type'=>'professeur');
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return !empty($DB_TAB) ? $DB_TAB : 'Aucun professeur du groupe est rattaché à une matière.' ;
}

/**
 * Retourner un tableau [valeur texte optgroup] des familles de niveaux
 *
 * @param void
 * @return array
 */
public static function DB_OPT_familles_niveaux()
{
  Form::$tab_select_optgroup = array( 1=>'Niveaux classes' , 2=>'Niveaux spécifiques' );
  $DB_SQL = 'SELECT niveau_famille_id AS valeur, niveau_famille_nom AS texte, niveau_famille_categorie AS optgroup ';
  $DB_SQL.= 'FROM sacoche_niveau_famille ';
  $DB_SQL.= 'ORDER BY niveau_famille_categorie DESC, niveau_famille_ordre ASC';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Retourner un tableau [valeur texte] des niveaux de l'établissement
 *
 * @param void
 * @return array
 */
public static function DB_OPT_niveaux_etabl()
{
  $DB_SQL = 'SELECT niveau_id AS valeur, niveau_nom AS texte ';
  $DB_SQL.= 'FROM sacoche_niveau ';
  $DB_SQL.= 'WHERE niveau_actif=1 ';
  $DB_SQL.= 'ORDER BY niveau_ordre ASC';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Retourner un tableau [valeur texte] des niveaux (choisis ou pas par l'établissement)
 *
 * @param void
 * @return array
 */
public static function DB_OPT_niveaux()
{
  Form::$tab_select_option_first = array(0,'Tous les niveaux','');
  $DB_SQL = 'SELECT niveau_id AS valeur, niveau_nom AS texte ';
  $DB_SQL.= 'FROM sacoche_niveau ';
  $DB_SQL.= 'ORDER BY niveau_ordre ASC';
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

/**
 * Retourner un tableau [valeur texte] des niveaux d'une famille donnée
 * optgroup sert à pouvoir regrouper les options
 *
 * @param int   niveau_famille_id
 * @return array
 */
public static function DB_OPT_niveaux_famille($niveau_famille_id)
{
  Form::$tab_select_option_first = array(ID_NIVEAU_MAX+$niveau_famille_id,'Tous les niveaux de cette famille','');
  // Ajouter, si pertinent, les niveaux spécifiques qui sinon ne sont pas trouvés car à part...
  $tab_sql = array(
    1 => '',
    2 => 'OR niveau_id IN(5,1,2,201) ',
    3 => 'OR niveau_id IN(3,202,203) ',
    4 => 'OR niveau_id IN(3,202,203) ',
    5 => 'OR niveau_id IN(4,204,205,206) ',
    6 => 'OR niveau_id IN(4,204,205,206) ',
    7 => 'OR niveau_id IN(4,204,205,206) ',
    8 => 'OR niveau_id IN(4,204,205,206) ',
    9 => ''
  );
  $DB_SQL = 'SELECT niveau_id AS valeur, niveau_nom AS texte ';
  $DB_SQL.= 'FROM sacoche_niveau ';
  $DB_SQL.= 'WHERE niveau_famille_id=:niveau_famille_id '.$tab_sql[$niveau_famille_id];
  $DB_SQL.= 'ORDER BY niveau_ordre ASC';
  $DB_VAR = array(':niveau_famille_id'=>$niveau_famille_id);
  return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Retourner un tableau [valeur texte] des niveaux des référentiels d'une matière
 *
 * @param int $matiere_id
 * @return array|string
 */
public static function DB_OPT_niveaux_matiere($matiere_id)
{
  // On récupère les matières des professeurs qui sont associés au groupe
  $DB_SQL = 'SELECT niveau_id AS valeur, niveau_nom AS texte ';
  $DB_SQL.= 'FROM sacoche_referentiel ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'WHERE matiere_id=:matiere_id ';
  $DB_SQL.= 'ORDER BY niveau_ordre ASC';
  $DB_VAR = array(':matiere_id'=>$matiere_id);
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return !empty($DB_TAB) ? $DB_TAB : 'Aucun référentiel est rattaché à cette matière.' ;
}

/**
 * Retourner un tableau [valeur texte] des paliers du socle de l'établissement
 *
 * @param void
 * @return array|string
 */
public static function DB_OPT_paliers_etabl()
{
  $DB_SQL = 'SELECT palier_id AS valeur, palier_nom AS texte ';
  $DB_SQL.= 'FROM sacoche_socle_palier ';
  $DB_SQL.= 'WHERE palier_actif=1 ';
  $DB_SQL.= 'ORDER BY palier_ordre ASC';
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  return !empty($DB_TAB) ? $DB_TAB : 'Aucun palier du socle commun n\'est rattaché à l\'établissement.' ;
}

/**
 * Retourner un tableau [valeur texte] des piliers du socle de tous les paliers de l'établissement, avec optgroup
 *
 * @param void
 * @return array|string
 */
public static function DB_OPT_paliers_piliers()
{
  $DB_SQL = 'SELECT pilier_id AS valeur, pilier_nom AS texte, palier_id AS optgroup, palier_nom AS optgroup_info ';
  $DB_SQL.= 'FROM sacoche_socle_palier ';
  $DB_SQL.= 'LEFT JOIN sacoche_socle_pilier USING (palier_id) ';
  $DB_SQL.= 'WHERE palier_actif=1 ';
  $DB_SQL.= 'ORDER BY palier_ordre ASC, pilier_ordre ASC';
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  $tab_optgroup = array();
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_optgroup[$DB_ROW['optgroup']] = $DB_ROW['optgroup_info'];
  }
  Form::$tab_select_optgroup = $tab_optgroup;
  return !empty($DB_TAB) ? $DB_TAB : 'Aucun palier du socle commun n\'est rattaché à l\'établissement.' ;
}

/**
 * Retourner un tableau [valeur texte] des piliers du socle d'un palier donné
 *
 * @param int $palier_id   id du palier
 * @return array|string
 */
public static function DB_OPT_piliers($palier_id)
{
  Form::$tab_select_option_first = array(0,'Toutes les compétences','');
  $DB_SQL = 'SELECT pilier_id AS valeur, pilier_nom AS texte ';
  $DB_SQL.= 'FROM sacoche_socle_pilier ';
  $DB_SQL.= 'WHERE palier_id=:palier_id ';
  $DB_SQL.= 'ORDER BY pilier_ordre ASC';
  $DB_VAR = array(':palier_id'=>$palier_id);
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return !empty($DB_TAB) ? $DB_TAB : 'Aucune compétence trouvée pour ce palier.' ;
}

/**
 * Retourner un tableau [valeur texte] des domaines du socle d'un pilier donné
 *
 * @param int $pilier_id   id du pilier
 * @return array|string
 */
public static function DB_OPT_domaines($pilier_id)
{
  Form::$tab_select_option_first = array(0,'Tous les domaines','');
  $DB_SQL = 'SELECT section_id AS valeur, section_nom AS texte ';
  $DB_SQL.= 'FROM sacoche_socle_section ';
  $DB_SQL.= 'WHERE pilier_id=:pilier_id ';
  $DB_SQL.= 'ORDER BY section_ordre ASC';
  $DB_VAR = array(':pilier_id'=>$pilier_id);
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return !empty($DB_TAB) ? $DB_TAB : 'Aucun domaine trouvé pour ce pilier.' ;
}

/**
 * Retourner un tableau [valeur texte optgroup] des niveaux / classes / groupes d'un établissement
 * optgroup sert à pouvoir regrouper les options
 *
 * @param bool   $sans   TRUE par défaut => pour avoir un choix "Sans classe affectée"
 * @param bool   $tout   TRUE par défaut => pour avoir un choix "Tout l'établissement"
 * @return array|string
 */
public static function DB_OPT_regroupements_etabl($sans=TRUE,$tout=TRUE)
{
  // Options du select : catégorie "Divers"
  $DB_TAB_divers = array();
  if($sans)
  {
    $DB_TAB_divers[] = array('valeur'=>'d1','texte'=>'Sans classe affectée' ,'optgroup'=>'divers');
  }
  if($tout)
  {
    $DB_TAB_divers[] = array('valeur'=>'d2','texte'=>'Tout l\'établissement','optgroup'=>'divers');
  }
  // Options du select : catégorie "Niveaux" (contenant des classes ou des groupes)
  $DB_SQL = 'SELECT CONCAT("n",niveau_id) AS valeur, niveau_nom AS texte, "niveau" AS optgroup ';
  $DB_SQL.= 'FROM sacoche_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'WHERE groupe_type=:type ';
  $DB_SQL.= 'GROUP BY niveau_id ';
  $DB_SQL.= 'ORDER BY niveau_ordre ASC';
  $DB_VAR = array(':type'=>'classe');
  $DB_TAB_niveau = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  // Options du select : catégories "Classes" et "Groupes"
  $DB_SQL = 'SELECT CONCAT(LEFT(groupe_type,1),groupe_id) AS valeur, groupe_nom AS texte, groupe_type AS optgroup ';
  $DB_SQL.= 'FROM sacoche_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'WHERE groupe_type IN (:type1,:type2) ';
  $DB_SQL.= 'ORDER BY groupe_type ASC, niveau_ordre ASC, groupe_nom ASC';
  $DB_VAR = array(':type1'=>'classe',':type2'=>'groupe');
  $DB_TAB_classe_groupe = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  // On assemble tous ces tableaux à la suite
  $DB_TAB = array_merge($DB_TAB_divers,$DB_TAB_niveau,$DB_TAB_classe_groupe);
  Form::$tab_select_optgroup = array('divers'=>'Divers','niveau'=>'Niveaux','classe'=>'Classes','groupe'=>'Groupes');
  return $DB_TAB ;

}

/**
 * Retourner un tableau [valeur texte optgroup] des groupes d'un établissement
 *
 * @param void
 * @return array|string
 */
public static function DB_OPT_groupes_etabl()
{
  $DB_SQL = 'SELECT groupe_id AS valeur, groupe_nom AS texte ';
  $DB_SQL.= 'FROM sacoche_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'WHERE groupe_type=:type ';
  $DB_SQL.= 'ORDER BY niveau_ordre ASC, groupe_nom ASC';
  $DB_VAR = array(':type'=>'groupe');
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return !empty($DB_TAB) ? $DB_TAB : 'Aucun groupe n\'est enregistré.' ;
}

/**
 * Retourner un tableau [valeur texte optgroup] des classes / groupes d'un professeur identifié
 * optgroup sert à pouvoir regrouper les options
 *
 * @param int $user_id
 * @return array|string
 */
public static function DB_OPT_groupes_professeur($user_id)
{
  Form::$tab_select_option_first = array(0,'Tous les regroupements','');
  $DB_SQL = 'SELECT groupe_id AS valeur, groupe_nom AS texte, groupe_type AS optgroup ';
  $DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'WHERE user_id=:user_id AND groupe_type!=:type4 ';
  $DB_SQL.= 'ORDER BY groupe_type ASC, niveau_ordre ASC, groupe_nom ASC';
  $DB_VAR = array(':user_id'=>$user_id,':type4'=>'eval');
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  Form::$tab_select_optgroup = array('classe'=>'Classes','groupe'=>'Groupes','besoin'=>'Besoins');
  return !empty($DB_TAB) ? $DB_TAB : 'Aucune classe et aucun groupe ne vous sont affectés.' ;
}

/**
 * Retourner un tableau [valeur texte] des classes de l'établissement
 *
 * @param bool   $with_ref   Avec la référence de la classe entre parenthèses.
 * @return array|string
 */
public static function DB_OPT_classes_etabl($with_ref)
{
  $texte = ($with_ref) ? 'CONCAT(groupe_nom," (",groupe_ref,")")' : 'groupe_nom' ;
  $DB_SQL = 'SELECT groupe_id AS valeur, '.$texte.' AS texte ';
  $DB_SQL.= 'FROM sacoche_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'WHERE groupe_type=:type ';
  $DB_SQL.= 'ORDER BY niveau_ordre ASC, groupe_nom ASC';
  $DB_VAR = array(':type'=>'classe');
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return !empty($DB_TAB) ? $DB_TAB : 'Aucune classe n\'est enregistrée.' ;
}

/**
 * Retourner un tableau [valeur texte optgroup] des classes / groupes de l'établissement
 * optgroup sert à pouvoir regrouper les options
 *
 * @param void
 * @return array|string
 */
public static function DB_OPT_classes_groupes_etabl()
{
  Form::$tab_select_option_first = array(0,'Tous les regroupements','');
  $DB_SQL = 'SELECT groupe_id AS valeur, groupe_nom AS texte, groupe_type AS optgroup ';
  $DB_SQL.= 'FROM sacoche_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'WHERE groupe_type IN (:type1,:type2) ';
  $DB_SQL.= 'ORDER BY groupe_type ASC, niveau_ordre ASC, groupe_nom ASC';
  $DB_VAR = array(':type1'=>'classe',':type2'=>'groupe');
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  Form::$tab_select_optgroup = array('classe'=>'Classes','groupe'=>'Groupes');
  return !empty($DB_TAB) ? $DB_TAB : 'Aucune classe et aucun groupe ne sont enregistrés.' ;
}

/**
 * Retourner un tableau [valeur texte] des classes où un professeur identifié est professeur principal
 *
 * @param int $user_id
 * @return array|string
 */
public static function DB_OPT_classes_prof_principal($user_id)
{
  $DB_SQL = 'SELECT groupe_id AS valeur, groupe_nom AS texte ';
  $DB_SQL.= 'FROM sacoche_jointure_user_groupe ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
  $DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
  $DB_SQL.= 'WHERE user_id=:user_id AND groupe_type=:type1 AND jointure_pp=:pp ';
  $DB_SQL.= 'GROUP BY groupe_id ';
  $DB_SQL.= 'ORDER BY niveau_ordre ASC, groupe_nom ASC';
  $DB_VAR = array(':user_id'=>$user_id,':type1'=>'classe',':pp'=>1);
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return !empty($DB_TAB) ? $DB_TAB : 'Vous n\'êtes professeur principal d\'aucune classe.' ;
}

/**
 * Retourner un tableau [valeur texte] des classes des enfants d'un parent
 *
 * @param int   $parent_id
 * @return array|string
 */
public static function DB_OPT_classes_parent($parent_id)
{
  $DB_SQL = 'SELECT groupe_id AS valeur, groupe_nom AS texte, "classe" AS optgroup ';
  $DB_SQL.= 'FROM sacoche_jointure_parent_eleve ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_jointure_parent_eleve.eleve_id=sacoche_user.user_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  $DB_SQL.= 'LEFT JOIN sacoche_groupe ON sacoche_user.eleve_classe_id=sacoche_groupe.groupe_id ';
  $DB_SQL.= 'WHERE parent_id=:parent_id AND user_profil_type=:profil_type AND user_sortie_date>NOW() AND groupe_id IS NOT NULL '; // Not NULL sinon pb qd un parent est rattaché à un enfant affecté dans aucune classe.
  $DB_SQL.= 'GROUP BY groupe_id ';
  $DB_SQL.= 'ORDER BY resp_legal_num ASC, user_nom ASC, user_prenom ASC ';
  $DB_VAR = array(':parent_id'=>$parent_id,':profil_type'=>'eleve');
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return !empty($DB_TAB) ? $DB_TAB : 'Aucune classe ne comporte un élève associé à ce compte.' ;
}

/**
 * Retourner un tableau [valeur texte optgroup] des sélections d'items d'un professeur identifié
 *
 * @param int $user_id
 * @return array|string
 */
public static function DB_OPT_selection_items($user_id)
{
  $DB_SQL = 'SELECT REPLACE(TRIM(BOTH "," FROM selection_item_liste),",","_") AS valeur, selection_item_nom AS texte ';
  $DB_SQL.= 'FROM sacoche_selection_item ';
  $DB_SQL.= 'WHERE user_id=:user_id ';
  $DB_SQL.= 'ORDER BY selection_item_nom ASC';
  $DB_VAR = array(':user_id'=>$user_id);
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return !empty($DB_TAB) ? $DB_TAB : 'Vous n\'avez mémorisé aucune sélection d\'items.' ;
}

/**
 * Retourner un tableau [valeur texte] des périodes de l'établissement, indépendamment des rattachements aux classes
 *
 * @param bool   $alerte   affiche un message d'erreur si aucune periode n'est trouvée
 * @return array|string
 */
public static function DB_OPT_periodes_etabl($alerte=FALSE)
{
  Form::$tab_select_option_first = array(0,'Personnalisée','');
  $DB_SQL = 'SELECT periode_id AS valeur, periode_nom AS texte ';
  $DB_SQL.= 'FROM sacoche_periode ';
  $DB_SQL.= 'ORDER BY periode_ordre ASC';
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
  return !empty($DB_TAB) ? $DB_TAB : ( ($alerte) ? 'Aucune période n\'est enregistrée.' : array() ) ;
}

/**
 * Retourner un tableau [valeur texte] des directeurs actuels de l'établissement
 *
 * @param void
 * @return array|string
 */
public static function DB_OPT_directeurs_etabl()
{
  $DB_SQL = 'SELECT user_id AS valeur, CONCAT(user_nom," ",user_prenom) AS texte ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  $DB_SQL.= 'WHERE user_profil_type=:profil_type AND user_sortie_date>NOW() ';
  $DB_SQL.= 'ORDER BY user_nom ASC, user_prenom ASC';
  $DB_VAR = array(':profil_type'=>'directeur');
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return !empty($DB_TAB) ? $DB_TAB : 'Aucun directeur enregistré.' ;
}

/**
 * Retourner un tableau [valeur texte] des professeurs actuels de l'établissement
 *
 * @param string $groupe_type   facultatif ; valeur parmi [all] [niveau] [classe] [groupe] 
 * @param int    $groupe_id     facultatif ; id du niveau ou de la classe ou du groupe
 * @return array|string
 */
public static function DB_OPT_professeurs_etabl($groupe_type='all',$groupe_id=0)
{
  $select = 'SELECT user_id AS valeur, CONCAT(user_nom," ",user_prenom) AS texte ';
  $where  = 'WHERE user_profil_type=:profil_type AND user_sortie_date>NOW() ';
  $ljoin  = '';
  $group  = '';
  $order  = 'ORDER BY user_nom ASC, user_prenom ASC';
  switch($groupe_type)
  {
    case 'all' :
      $from  = 'FROM sacoche_user ';
      $ljoin.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
      break;
    case 'niveau' :
      $from  = 'FROM sacoche_groupe ';
      $ljoin.= 'LEFT JOIN sacoche_jointure_user_groupe USING (groupe_id) ';
      $ljoin.= 'LEFT JOIN sacoche_user USING (user_id) ';
      $ljoin.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
      $where.= 'AND niveau_id=:niveau ';
      $group.= 'GROUP BY user_id ';
      break;
    case 'classe' :
    case 'groupe' :
      $from  = 'FROM sacoche_jointure_user_groupe ';
      $ljoin.= 'LEFT JOIN sacoche_user USING (user_id) ';
      $ljoin.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
      $where.= 'AND groupe_id=:groupe ';
      break;
  }
  // On peut maintenant assembler les morceaux de la requête !
  $DB_SQL = $select.$from.$ljoin.$where.$group.$order;
  $DB_VAR = array(':profil_type'=>'professeur',':niveau'=>$groupe_id,':groupe'=>$groupe_id);
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return !empty($DB_TAB) ? $DB_TAB : 'Aucun professeur enregistré.' ;
}

/**
 * Retourner un tableau [valeur texte] des professeurs et directeurs de l'établissement
 * optgroup sert à pouvoir regrouper les options
 *
 * @param int $statut   statut des utilisateurs (1 pour actuel, 0 pour ancien)
 * @return array|string
 */
public static function DB_OPT_professeurs_directeurs_etabl($statut)
{
  $test_date_sortie = ($statut) ? 'user_sortie_date>NOW()' : 'user_sortie_date<NOW()' ; // Pas besoin de tester l'égalité, NOW() renvoyant un datetime
  $DB_SQL = 'SELECT user_id AS valeur, CONCAT(user_nom," ",user_prenom) AS texte, user_profil_type AS optgroup ';
  $DB_SQL.= 'FROM sacoche_user ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  $DB_SQL.= 'WHERE user_profil_type IN(:profil_type1,:profil_type2) AND '.$test_date_sortie.' ';
  $DB_SQL.= 'ORDER BY user_profil_type DESC, user_nom ASC, user_prenom ASC';
  $DB_VAR = array(':profil_type1'=>'professeur',':profil_type2'=>'directeur');
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  Form::$tab_select_option_first = array(0,'Tampon de l\'établissement','');
  Form::$tab_select_optgroup = array('directeur'=>'Directeurs','professeur'=>'Professeurs');
  return !empty($DB_TAB) ? $DB_TAB : 'Aucun professeur ou directeur trouvé.' ;
}

/**
 * Retourner un tableau [valeur texte] des parents de l'établissement
 *
 * @param int    $statut        statut des utilisateurs (1 pour actuel, 0 pour ancien)
 * @param string $groupe_type   facultatif ; valeur parmi [all] [niveau] [classe] [groupe] 
 * @param int    $groupe_id     facultatif ; id du niveau ou de la classe ou du groupe
 * @return array|string
 */
public static function DB_OPT_parents_etabl($statut,$groupe_type='all',$groupe_id=0)
{
  $test_date_sortie = ($statut) ? 'user_sortie_date>NOW()' : 'user_sortie_date<NOW()' ; // Pas besoin de tester l'égalité, NOW() renvoyant un datetime
  $select = 'SELECT parent.user_id AS valeur, CONCAT(parent.user_nom," ",parent.user_prenom) AS texte ';
  $where  = 'WHERE parent_profil.user_profil_type=:profil_type AND parent.'.$test_date_sortie.' ';
  $ljoin  = '';
  $group  = '';
  $order  = 'ORDER BY parent.user_nom ASC, parent.user_prenom ASC';
  switch($groupe_type)
  {
    case 'all' :
      $from  = 'FROM sacoche_user AS parent ';
      $ljoin.= 'LEFT JOIN sacoche_user_profil AS parent_profil ON parent.user_profil_sigle=parent_profil.user_profil_sigle ';
      break;
    case 'niveau' :
      $from  = 'FROM sacoche_groupe ';
      $ljoin.= 'LEFT JOIN sacoche_user AS enfant ON sacoche_groupe.groupe_id=enfant.eleve_classe_id ';
      $ljoin.= 'INNER JOIN sacoche_jointure_parent_eleve ON enfant.user_id=sacoche_jointure_parent_eleve.eleve_id ';
      $ljoin.= 'INNER JOIN sacoche_user AS parent ON sacoche_jointure_parent_eleve.parent_id=parent.user_id ';
      $ljoin.= 'LEFT JOIN sacoche_user_profil AS parent_profil ON parent.user_profil_sigle=parent_profil.user_profil_sigle ';
      $where.= 'AND niveau_id=:niveau ';
      $group.= 'GROUP BY parent.user_id ';
      break;
    case 'classe' :
      $from  = 'FROM sacoche_user AS enfant ';
      $ljoin.= 'INNER JOIN sacoche_jointure_parent_eleve ON enfant.user_id=sacoche_jointure_parent_eleve.eleve_id ';
      $ljoin.= 'INNER JOIN sacoche_user AS parent ON sacoche_jointure_parent_eleve.parent_id=parent.user_id ';
      $ljoin.= 'LEFT JOIN sacoche_user_profil AS parent_profil ON parent.user_profil_sigle=parent_profil.user_profil_sigle ';
      $where.= 'AND enfant.eleve_classe_id=:classe ';
      $group.= 'GROUP BY parent.user_id ';
      break;
    case 'groupe' :
      $from  = 'FROM sacoche_jointure_user_groupe ';
      $ljoin.= 'LEFT JOIN sacoche_user AS enfant USING (user_id) ';
      $ljoin.= 'INNER JOIN sacoche_jointure_parent_eleve ON enfant.user_id=sacoche_jointure_parent_eleve.eleve_id ';
      $ljoin.= 'INNER JOIN sacoche_user AS parent ON sacoche_jointure_parent_eleve.parent_id=parent.user_id ';
      $ljoin.= 'LEFT JOIN sacoche_user_profil AS parent_profil ON parent.user_profil_sigle=parent_profil.user_profil_sigle ';
      $where.= 'AND groupe_id=:groupe ';
      $group.= 'GROUP BY parent.user_id ';
      break;
  }
  // On peut maintenant assembler les morceaux de la requête !
  $DB_SQL = $select.$from.$ljoin.$where.$group.$order;
  $DB_VAR = array(':profil_type'=>'parent',':niveau'=>$groupe_id,':classe'=>$groupe_id,':groupe'=>$groupe_id);
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return !empty($DB_TAB) ? $DB_TAB : 'Aucun responsable trouvé.' ;
}

/**
 * Retourner un tableau [valeur texte] des élèves d'un regroupement préselectionné
 *
 * @param string $groupe_type   valeur parmi [sdf] [all] [niveau] [classe] [groupe] [besoin] 
 * @param int    $groupe_id     id du niveau ou de la classe ou du groupe
 * @param int    $statut        statut des utilisateurs (1 pour actuel, 0 pour ancien)
 * @return array|string
 */
public static function DB_OPT_eleves_regroupement($groupe_type,$groupe_id,$statut)
{
  $test_date_sortie = ($statut) ? 'user_sortie_date>NOW()' : 'user_sortie_date<NOW()' ; // Pas besoin de tester l'égalité, NOW() renvoyant un datetime
  if($_SESSION['USER_PROFIL_TYPE']=='parent')
  {
    $DB_TAB = $_SESSION['OPT_PARENT_ENFANTS'];
    foreach($DB_TAB as $key=>$tab)
    {
      if($tab['classe_id']!=$groupe_id)
      {
        unset($DB_TAB[$key]);
      }
    }
  }
  else
  {
    $DB_SQL = 'SELECT user_id AS valeur, CONCAT(user_nom," ",user_prenom) AS texte ';
    $DB_SQL.= 'FROM sacoche_user ';
    $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
    switch ($groupe_type)
    {
      case 'sdf' :  // On veut les élèves non affectés dans une classe
        $DB_SQL.= 'WHERE user_profil_type=:profil_type AND '.$test_date_sortie.' AND eleve_classe_id=:classe ';
        $DB_VAR = array(':profil_type'=>'eleve',':classe'=>0);
        break;
      case 'all' :  // On veut tous les élèves de l'établissement
        $DB_SQL.= 'WHERE user_profil_type=:profil_type AND '.$test_date_sortie.' ';
        $DB_VAR = array(':profil_type'=>'eleve');
        break;
      case 'niveau' :  // On veut tous les élèves d'un niveau
        $DB_SQL.= 'LEFT JOIN sacoche_groupe ON sacoche_user.eleve_classe_id=sacoche_groupe.groupe_id ';
        $DB_SQL.= 'WHERE user_profil_type=:profil_type AND '.$test_date_sortie.' AND niveau_id=:niveau ';
        $DB_VAR = array(':profil_type'=>'eleve',':niveau'=>$groupe_id);
        break;
      case 'classe' :  // On veut tous les élèves d'une classe (on utilise "eleve_classe_id" de "sacoche_user")
        $DB_SQL.= 'WHERE user_profil_type=:profil_type AND '.$test_date_sortie.' AND eleve_classe_id=:classe ';
        $DB_VAR = array(':profil_type'=>'eleve',':classe'=>$groupe_id);
        break;
      case 'groupe' :  // On veut tous les élèves d'un groupe (on utilise la jointure de "sacoche_jointure_user_groupe")
      case 'besoin' :  // On veut tous les élèves d'un groupe de besoin (on utilise la jointure de "sacoche_jointure_user_groupe")
        $DB_SQL.= 'LEFT JOIN sacoche_jointure_user_groupe USING (user_id) ';
        $DB_SQL.= 'WHERE user_profil_type=:profil_type AND '.$test_date_sortie.' AND groupe_id=:groupe ';
        $DB_VAR = array(':profil_type'=>'eleve',':groupe'=>$groupe_id);
        break;
    }
    $DB_SQL.= 'ORDER BY user_nom ASC, user_prenom ASC';
    $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  }
  return !empty($DB_TAB) ? $DB_TAB : 'Aucun élève trouvé.' ;
}

/**
 * Retourner un tableau [valeur texte] des enfants d'un parent
 *
 * @param int   $parent_id
 * @return array|string
 */
public static function DB_OPT_enfants_parent($parent_id)
{
  $DB_SQL = 'SELECT user_id AS valeur, CONCAT(user_nom," ",user_prenom) AS texte, eleve_classe_id AS classe_id ';
  $DB_SQL.= 'FROM sacoche_jointure_parent_eleve ';
  $DB_SQL.= 'LEFT JOIN sacoche_user ON sacoche_jointure_parent_eleve.eleve_id=sacoche_user.user_id ';
  $DB_SQL.= 'LEFT JOIN sacoche_user_profil USING (user_profil_sigle) ';
  $DB_SQL.= 'WHERE parent_id=:parent_id AND user_profil_type=:profil_type AND user_sortie_date>NOW() ';
  $DB_SQL.= 'ORDER BY resp_legal_num ASC, user_nom ASC, user_prenom ASC ';
  $DB_VAR = array(':parent_id'=>$parent_id,':profil_type'=>'eleve');
  $DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
  return !empty($DB_TAB) ? $DB_TAB : 'Aucun élève associé à ce compte.' ;
}

}
?>