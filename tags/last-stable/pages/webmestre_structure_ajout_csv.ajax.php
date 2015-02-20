<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009-2015
 * 
 * ****************************************************************************************************
 * SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath <http://www.sesamath.net> - Tous droits réservés.
 * Logiciel placé sous la licence libre Affero GPL 3 <https://www.gnu.org/licenses/agpl-3.0.html>.
 * ****************************************************************************************************
 * 
 * Ce fichier est une partie de SACoche.
 * 
 * SACoche est un logiciel libre ; vous pouvez le redistribuer ou le modifier suivant les termes 
 * de la “GNU Affero General Public License” telle que publiée par la Free Software Foundation :
 * soit la version 3 de cette licence, soit (à votre gré) toute version ultérieure.
 * 
 * SACoche est distribué dans l’espoir qu’il vous sera utile, mais SANS AUCUNE GARANTIE :
 * sans même la garantie implicite de COMMERCIALISABILITÉ ni d’ADÉQUATION À UN OBJECTIF PARTICULIER.
 * Consultez la Licence Publique Générale GNU Affero pour plus de détails.
 * 
 * Vous devriez avoir reçu une copie de la Licence Publique Générale GNU Affero avec SACoche ;
 * si ce n’est pas le cas, consultez : <http://www.gnu.org/licenses/>.
 * 
 */

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}

$tab_base_id = (isset($_POST['f_listing_id'])) ? array_filter( Clean::map_entier( explode(',',$_POST['f_listing_id']) ) , 'positif' ) : array() ;
$nb_bases    = count($tab_base_id);

$action         = (isset($_POST['f_action']))         ? Clean::texte($_POST['f_action'])          : '';
$num            = (isset($_POST['num']))              ? (int)$_POST['num']                        : 0 ;  // Numéro de l'étape en cours
$max            = (isset($_POST['max']))              ? (int)$_POST['max']                        : 0 ;  // Nombre d'étapes à effectuer
$courriel_envoi = (isset($_POST['f_courriel_envoi'])) ? Clean::entier($_POST['f_courriel_envoi']) : 0 ;
$courriel_copie = (isset($_POST['f_courriel_copie'])) ? Clean::entier($_POST['f_courriel_copie']) : 0 ;

$fichier_csv_nom  = 'ajout_structures_'.fabriquer_fin_nom_fichier__date_et_alea().'.csv';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Import d'un fichier CSV avec le listing des structures
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='importer_csv')
{
  $result = FileSystem::recuperer_upload( CHEMIN_DOSSIER_IMPORT /*fichier_chemin*/ , $fichier_csv_nom /*fichier_nom*/ , array('txt','csv') /*tab_extensions_autorisees*/ , NULL /*tab_extensions_interdites*/ , NULL /*taille_maxi*/ , NULL /*filename_in_zip*/ );
  if($result!==TRUE)
  {
    exit('Erreur : '.$result);
  }
  // On récupère les zones géographiques pour vérifier que l'identifiant transmis est cohérent
  $tab_geo = array();
  $DB_TAB = DB_WEBMESTRE_WEBMESTRE::DB_lister_zones();
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_geo[$DB_ROW['geo_id']] = TRUE;
  }
  // Tester si le contenu est correct, et mémoriser les infos en session
  $_SESSION['tab_info'] = array();
  $contenu = file_get_contents(CHEMIN_DOSSIER_IMPORT.$fichier_csv_nom);
  $contenu = To::deleteBOM(To::utf8($contenu)); // Mettre en UTF-8 si besoin et retirer le BOM éventuel
  $tab_lignes = extraire_lignes($contenu); // Extraire les lignes du fichier
  $separateur = extraire_separateur_csv($tab_lignes[0]); // Déterminer la nature du séparateur
  unset($tab_lignes[0]); // Supprimer la 1e ligne
  $tab_nouvel_uai = array();
  $tab_nouvel_id  = array();
  $nb_lignes_trouvees = 0;
  $tab_erreur = array(
    'info' => array('nb'=>0,'txt'=>' manquant d\'informations !') ,
    'geo'  => array('nb'=>0,'txt'=>' avec identifiant géographique incorrect !') ,
    'uai'  => array('nb'=>0,'txt'=>' avec UAI déjà présent ou en double ou incorrect !') ,
    'mail' => array('nb'=>0,'txt'=>' avec adresse de courriel incorrecte !') ,
    'id'   => array('nb'=>0,'txt'=>' avec identifiant de base déjà utilisé ou en double !') ,
  );
  foreach ($tab_lignes as $ligne_contenu)
  {
    $tab_elements = str_getcsv($ligne_contenu,$separateur);
    $tab_elements = array_slice($tab_elements,0,8);
    if(count($tab_elements)==8)
    {
      $nb_lignes_trouvees++;
      list($import_id,$geo_id,$localisation,$denomination,$uai,$contact_nom,$contact_prenom,$contact_courriel) = $tab_elements;
      $import_id        = Clean::entier($import_id);
      $geo_id           = Clean::entier($geo_id);
      $localisation     = Clean::texte($localisation);
      $denomination     = Clean::texte($denomination);
      $uai              = Clean::uai($uai);
      $contact_nom      = Clean::nom($contact_nom);
      $contact_prenom   = Clean::prenom($contact_prenom);
      $contact_courriel = Clean::courriel($contact_courriel);
      $_SESSION['tab_info'][$nb_lignes_trouvees] = array( 'import_id'=>$import_id , 'geo_id'=>$geo_id , 'localisation'=>$localisation , 'denomination'=>$denomination , 'uai'=>$uai , 'contact_nom'=>$contact_nom , 'contact_prenom'=>$contact_prenom , 'contact_courriel'=>$contact_courriel );
      // Vérifier la présence des informations
      if( !$geo_id || !$localisation || !$denomination || !$contact_nom || !$contact_prenom || !$contact_courriel )
      {
        $tab_erreur['info']['nb']++;
      }
      // Vérifier que l'id géographique est correct
      if(!isset($tab_geo[$geo_id]))
      {
        $tab_erreur['geo']['nb']++;
      }
      // Vérifier que le n°UAI est disponible et correct
      if($uai)
      {
        if( (!tester_UAI($uai)) || (isset($tab_nouvel_uai[$uai])) || DB_WEBMESTRE_WEBMESTRE::DB_tester_structure_UAI($uai) )
        {
          $tab_erreur['uai']['nb']++;
        }
        $tab_nouvel_uai[$uai] = TRUE;
      }
      // Vérifier que l'adresse de courriel est correcte
      if(!tester_courriel($contact_courriel))
      {
        $tab_erreur['mail']['nb']++;
      }
      // Vérifier le domaine du serveur mail (multi-structures donc serveur ouvert sur l'extérieur).
      $mail_domaine = tester_domaine_courriel_valide($contact_courriel);
      if($mail_domaine!==TRUE)
      {
        $tab_erreur['mail']['nb']++;
      }
      // Vérifier que l'identifiant est disponible
      if($import_id)
      {
        if((isset($tab_nouvel_id[$import_id])) || (DB_WEBMESTRE_WEBMESTRE::DB_tester_structure_Id($import_id)!==NULL) )
        {
          $tab_erreur['id']['nb']++;
        }
        $tab_nouvel_id[$import_id] = TRUE;
      }
    }
  }
  FileSystem::supprimer_fichier(CHEMIN_DOSSIER_IMPORT.$fichier_csv_nom);
  if(!$nb_lignes_trouvees)
  {
    exit('Erreur : aucune ligne du fichier ne semble correcte !');
  }
  $info_lignes_trouvees = ($nb_lignes_trouvees>1) ? $nb_lignes_trouvees.' lignes trouvées' : '1 ligne trouvée' ;
  foreach($tab_erreur as $key => $tab)
  {
    if($tab['nb'])
    {
      $s = ($tab['nb']>1) ? 's' : '' ;
      exit('Erreur : '.$info_lignes_trouvees.' mais '.$tab['nb'].' ligne'.$s.$tab['txt']);
    }
  }
  exit(']¤['.$info_lignes_trouvees);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Etape d'ajout d'un nouvel établissement
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='ajouter') && $num && $max )
{
  if(!count($_SESSION['tab_info']))
  {
    exit('Erreur : données du fichier CSV perdues !');
  }
  require(CHEMIN_DOSSIER_INCLUDE.'fonction_dump.php');
  // Récupérer la série d'infos
  extract($_SESSION['tab_info'][$num]); // import_id / geo_id / localisation / denomination / uai / nom / prenom / courriel
  // Insérer l'enregistrement dans la base du webmestre
  // Créer le fichier de connexion de la base de données de la structure
  // Créer la base de données de la structure
  // Créer un utilisateur pour la base de données de la structure et lui attribuer ses droits
  $base_id = Webmestre::ajouter_structure($import_id,$geo_id,$uai,$localisation,$denomination,$contact_nom,$contact_prenom,$contact_courriel);
  // Créer les dossiers de fichiers temporaires par établissement
  foreach(FileSystem::$tab_dossier_tmp_structure as $dossier)
  {
    FileSystem::creer_dossier($dossier.$base_id);
    FileSystem::ecrire_fichier_index($dossier.$base_id);
  }
  // Charger les paramètres de connexion à cette base afin de pouvoir y effectuer des requêtes
  charger_parametres_mysql_supplementaires($base_id);
  // Lancer les requêtes pour créer et remplir les tables
  charger_parametres_mysql_supplementaires($base_id);
  DB_STRUCTURE_COMMUN::DB_creer_remplir_tables_structure();
  // Il est arrivé que la fonction DB_modifier_parametres() retourne une erreur disant que la table n'existe pas.
  // Comme si les requêtes de DB_creer_remplir_tables_structure() étaient en cache, et pas encore toutes passées (parcequ'au final, quand on va voir la base, toutes les tables sont bien là).
  // Est-ce que c'est possible au vu du fonctionnement de la classe de connexion ? Et, bien sûr, y a-t-il quelque chose à faire pour éviter ce problème ?
  // En attendant une réponse de SebR, j'ai mis ce sleep(1)... sans trop savoir si cela pouvait aider...
  @sleep(1);
  // Personnaliser certains paramètres de la structure
  $tab_parametres = array();
  $tab_parametres['version_base']               = VERSION_BASE_STRUCTURE;
  $tab_parametres['webmestre_uai']              = $uai;
  $tab_parametres['webmestre_denomination']     = $denomination;
  $tab_parametres['etablissement_denomination'] = $denomination;
  DB_STRUCTURE_COMMUN::DB_modifier_parametres($tab_parametres);
  // Insérer le compte administrateur dans la base de cette structure
  $password = fabriquer_mdp();
  $user_id = DB_STRUCTURE_COMMUN::DB_ajouter_utilisateur( 0 /*user_sconet_id*/ , 0 /*user_sconet_elenoet*/ , '' /*reference*/ , 'ADM' , 'I' /*user_genre*/ , $contact_nom , $contact_prenom , NULL /*user_naissance_date*/ , $contact_courriel , 'user' /*user_email_origine*/ , 'admin' /*login*/ , crypter_mdp($password) , 0 /*classe_id*/ , '' /*id_ent*/ , '' /*id_gepi*/ );
  // Pour les admins, abonnement obligatoire aux contacts effectués depuis la page d'authentification
  DB_STRUCTURE_NOTIFICATION::DB_ajouter_abonnement( $user_id , 'contact_externe' , 'accueil' );
  // Envoyer un courriel au contact et / ou une copie du courriel au webmestre
  $courriel_contenu = ( $courriel_envoi || $courriel_copie ) ? Webmestre::contenu_courriel_inscription( $base_id , $denomination , $contact_nom , $contact_prenom , 'admin' , $password , URL_DIR_SACOCHE ) : '' ;
  $courriel_titre   = ( $courriel_envoi || $courriel_copie ) ? 'Création compte - Inscription n°'.$base_id : '' ;
  if($courriel_envoi)
  {
    $courriel_bilan = Sesamail::mail( $contact_courriel , $courriel_titre , $courriel_contenu );
    if(!$courriel_bilan)
    {
      exit('Erreur lors de l\'envoi du courriel !');
    }
  }
  if($courriel_copie)
  {
    $introduction = '================================================================================'."\r\n".'Copie pour information du courriel adressé à '.$contact_courriel."\r\n".'================================================================================'."\r\n\r\n";
    $courriel_bilan = Sesamail::mail( WEBMESTRE_COURRIEL , $courriel_titre , $introduction.$courriel_contenu );
    if(!$courriel_bilan)
    {
      exit('Erreur lors de l\'envoi du courriel !');
    }
  }
  // Mini-ménage si dernier appel
  if($num==$max)
  {
    unset($_SESSION['tab_info']);
  }
  // Retour de l'affichage, appel suivant
  exit(']¤['.'<tr><td class="nu"><input type="checkbox" name="f_ids" value="'.$base_id.'" /></td><td class="label">'.$base_id.'</td><td class="label">'.html($localisation.' | '.$denomination.' ['.$uai.']').'</td><td class="label">'.html($contact_nom.' '.$contact_prenom.' ('.$contact_courriel.')').'</td></tr>');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Supprimer plusieurs structures existantes
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='supprimer') && $nb_bases )
{
  foreach($tab_base_id as $base_id)
  {
    Webmestre::supprimer_multi_structure($base_id);
  }
  exit('<ok>');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Il se peut que rien n'ait été récupéré à cause de l'upload d'un fichier trop lourd
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if(empty($_POST))
{
  exit('Erreur : aucune donnée reçue ! Fichier trop lourd ? '.InfoServeur::minimum_limitations_upload());
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

exit('Erreur avec les données transmises !');

?>
