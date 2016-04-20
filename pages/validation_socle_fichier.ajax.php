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
if($_SESSION['SESAMATH_ID']==ID_DEMO) {Json::end( FALSE , 'Action désactivée pour la démo.' );}

$action    = (isset($_POST['f_action'])) ? Clean::texte($_POST['f_action']) : '';
$tab_eleve = (isset($_POST['f_eleve']))  ? explode(',',$_POST['f_eleve'])   : array() ;
$tab_eleve = array_filter( Clean::map('entier',$tab_eleve) , 'positif' );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Exporter un fichier de validations
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( in_array( $action , array('export_lpc','export_sacoche') ) && count($tab_eleve) )
{
  $tab_validations  = array(); // [i] => [user_id][palier_id][pilier_id][entree_id] => [date][etat] Retenir les validations ; item à 0 si validation d'un palier.
  $listing_eleve_id = implode(',',$tab_eleve);
  $only_positives   = ($action=='export_lpc') ? TRUE : FALSE ;
  // Validations des items
  $DB_TAB = DB_STRUCTURE_SOCLE::DB_lister_validations_items($listing_eleve_id,$only_positives);
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_validations[$DB_ROW['user_id']][$DB_ROW['palier_id']][$DB_ROW['pilier_id']][$DB_ROW['entree_id']] = array('date'=>$DB_ROW['validation_entree_date'],'etat'=>$DB_ROW['validation_entree_etat'],'info'=>$DB_ROW['validation_entree_info']) ;
  }
  // Validations des compétences
  $DB_TAB = DB_STRUCTURE_SOCLE::DB_lister_validations_competences($listing_eleve_id,$only_positives);
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_validations[$DB_ROW['user_id']][$DB_ROW['palier_id']][$DB_ROW['pilier_id']][0] = array('date'=>$DB_ROW['validation_pilier_date'],'etat'=>$DB_ROW['validation_pilier_etat'],'info'=>$DB_ROW['validation_pilier_info']) ;
  }
  // Validations trouvées ?
  if(!count($tab_validations))
  {
    $positive = $only_positives ? 'positive ' : '' ;
    Json::end( FALSE , 'Aucune validation '.$positive.'d\'élève trouvée !');
  }
  // Données élèves
  $tab_eleves     = array(); // [user_id] => array(nom,prenom,sconet_id) Ordonné par classe et alphabet.
  $only_sconet_id = ($action=='export_lpc') ? TRUE : FALSE ;
  $DB_TAB = DB_STRUCTURE_SOCLE::DB_lister_eleves_cibles_actuels_avec_sconet_id($listing_eleve_id,$only_sconet_id);
  if(empty($DB_TAB))
  {
    $identifiant = $only_sconet_id ? 'n\'ont pas d\'identifiant Sconet ou ' : '' ;
    Json::end( FALSE , 'Les élèves trouvés '.$identifiant.'sont anciens !');
  }
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_eleves[$DB_ROW['user_id']] = array('nom'=>$DB_ROW['user_nom'],'prenom'=>$DB_ROW['user_prenom'],'sconet_id'=>$DB_ROW['user_sconet_id']);
  }
  // Fabrication du XML
  $nb_eleves  = 0;
  $nb_piliers = 0;
  $nb_items   = 0;
  if($action=='export_lpc')
  {
    $xml = '<?xml version="1.0" encoding="ISO-8859-15"?>'."\r\n";
    $xml.= '<lpc xmlns="urn:ac-grenoble.fr:lpc:import:v1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="urn:ac-grenoble.fr:lpc:import:v1.0 import-lpc.xsd">'."\r\n";
    $xml.= '  <entete>'."\r\n";
    $xml.= '    <editeur>SESAMATH</editeur>'."\r\n";
    $xml.= '    <application>SACOCHE</application>'."\r\n";
    $xml.= '    <etablissement>'.html($_SESSION['WEBMESTRE_UAI']).'</etablissement>'."\r\n";
    $xml.= '  </entete>'."\r\n";
    $xml.= '  <donnees>'."\r\n";
  }
  else
  {
    $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\r\n";
    $xml.= '<sacoche>'."\r\n";
    $xml.= '  <donnees>'."\r\n";
  }
  foreach($tab_eleves as $user_id => $tab_user)
  {
    if(isset($tab_validations[$user_id]))
    {
      $nb_eleves++;
      $xml.= '    <eleve id="'.$tab_user['sconet_id'].'" nom="'.html($tab_user['nom']).'" prenom="'.html($tab_user['prenom']).'">'."\r\n";
      foreach($tab_validations[$user_id] as $palier_id => $tab_pilier)
      {
        $xml.= '      <palier id="'.$palier_id.'">'."\r\n";
        foreach($tab_pilier as $pilier_id => $tab_item)
        {
          $xml.= '        <competence id="'.$pilier_id.'">'."\r\n";
          if(isset($tab_item[0]))
          {
            // Validation de la compétence
            $nb_piliers++;
            $xml.= '          <validation>'."\r\n";
            $xml.= '            <date>'.$tab_item[0]['date'].'</date>'."\r\n";
            if(!$only_positives)
            {
              $xml.= '            <etat>'.$tab_item[0]['etat'].'</etat>'."\r\n";
              $xml.= '            <info>'.html($tab_item[0]['info']).'</info>'."\r\n";
            }
            $xml.= '          </validation>'."\r\n";
            unset($tab_item[0]);
          }
          if(count($tab_item))
          {
            // Validation d'items de la compétence
            foreach($tab_item as $item_id => $tab_item_infos)
            {
              $nb_items++;
              $xml.= '          <item id="'.$item_id.'">'."\r\n";
              $xml.= '            <renseignement>'."\r\n";
              $xml.= '              <date>'.$tab_item_infos['date'].'</date>'."\r\n";
              if(!$only_positives)
              {
                $xml.= '              <etat>'.$tab_item_infos['etat'].'</etat>'."\r\n";
                $xml.= '              <info>'.html($tab_item_infos['info']).'</info>'."\r\n";
              }
              $xml.= '            </renseignement>'."\r\n";
              $xml.= '          </item>'."\r\n";
            }
          }
          $xml.= '        </competence>'."\r\n";
        }
        $xml.= '      </palier>'."\r\n";
      }
      $xml.= '    </eleve>'."\r\n";
    }
  }
  $fichier_extension = ($action=='export_lpc') ? 'xml' : 'zip' ;
  $fichier_nom = str_replace('export_','import-',$action).'-'.Clean::fichier($_SESSION['WEBMESTRE_UAI']).'_'.FileSystem::generer_fin_nom_fichier__date_et_alea().'.'.$fichier_extension; // LPC recommande le modèle "import-lpc-{timestamp}.xml"
  if($action=='export_lpc')
  {
    $xml.= '  </donnees>'."\r\n";
    $xml.= '</lpc>'."\r\n";
    // Pour LPC, ajouter la signature via un appel au serveur sécurisé
    $xml = utf8_decode($xml);
    $xml = ServeurCommunautaire::signer_exportLPC( $_SESSION['SESAMATH_ID'] , $_SESSION['SESAMATH_KEY'] , $xml ); // fonction sur le modèle de envoyer_arborescence_XML()
    if(substr($xml,0,5)!='<?xml')
    {
      Json::end( FALSE , html($xml) );
    }
    FileSystem::ecrire_fichier( CHEMIN_DOSSIER_EXPORT.$fichier_nom , $xml );
    $fichier_lien = './force_download.php?fichier='.$fichier_nom;
  }
  else
  {
    $xml.= '  </donnees>'."\r\n";
    $xml.= '</sacoche>'."\r\n";
    // L'export pour SACoche on peut le zipper (le gain est très significatif : facteur 40 à 50 !)
    $result = FileSystem::zip_chaine( CHEMIN_DOSSIER_EXPORT.$fichier_nom , 'import_validations.xml' , $xml );
    if($result!==TRUE)
    {
      Json::end( FALSE , $result );
    }
    $fichier_lien = URL_DIR_EXPORT.$fichier_nom;
  }
  // Afficher le retour
  $se = ($nb_eleves>1)  ? 's' : '' ;
  $sp = ($nb_piliers>1) ? 's' : '' ;
  $si = ($nb_items>1)   ? 's' : '' ;
  $in = $only_positives ? '' : '(in)-' ;
  Json::add_str('<li><label class="valide">Fichier d\'export généré : '.$nb_piliers.' '.$in.'validation'.$sp.' de compétence'.$sp.' et '.$nb_items.' '.$in.'validation'.$si.' d\'item'.$si.' concernant '.$nb_eleves.' élève'.$se.'.</label></li>'.NL);
  Json::add_str('<li><a target="_blank" href="'.$fichier_lien.'"><span class="file file_'.$fichier_extension.'">Récupérer le fichier au format <em>'.$fichier_extension.'</em>.</span></a></li>'.NL);
  if($action=='export_lpc')
  {
    Json::add_str('<li>Vous devrez indiquer dans <em>lpc</em> les dates suivantes : <span class="b">'.html(CNIL_DATE_ENGAGEMENT).'</span> (déclaration <em>cnil</em>) et <span class="b">'.html(CNIL_DATE_RECEPISSE).'</span> (retour du récépissé).</li>'.NL);
  }
  Json::add_str('<li><label class="alerte">Pour des raisons de sécurité et de confidentialité, ce fichier sera effacé du serveur dans 1h.</label></li>'.NL);
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Importer un fichier de validations
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( in_array( $action , array('import_sacoche','import_compatible') ) )
{
  // Récupération du fichier
  // Si c'est un fichier zippé, on considère alors que c'est un zip devant venir de SACoche, et contenant import_validations.xml
  $fichier_nom = 'import_validations_'.$_SESSION['BASE'].'_'.FileSystem::generer_fin_nom_fichier__date_et_alea().'.xml';
  $result = FileSystem::recuperer_upload( CHEMIN_DOSSIER_IMPORT /*fichier_chemin*/ , $fichier_nom /*fichier_nom*/ , array('xml','zip') /*tab_extensions_autorisees*/ , NULL /*tab_extensions_interdites*/ , NULL /*taille_maxi*/ , 'import_validations.xml' /*filename_in_zip*/ );
  if($result!==TRUE)
  {
    Json::end( FALSE , $result );
  }
  // On passe au contenu
  $fichier_contenu = file_get_contents(CHEMIN_DOSSIER_IMPORT.$fichier_nom);
  $fichier_contenu = To::deleteBOM(To::utf8($fichier_contenu)); // Mettre en UTF-8 si besoin et retirer le BOM éventuel
  $xml = @simplexml_load_string($fichier_contenu);
  if($xml===FALSE)
  {
    Json::end( FALSE , 'Le fichier transmis n\'est pas un XML valide !');
  }
  // On extrait les infos du XML
  $tab_eleve_fichier = array();
  if( ($xml->donnees) && ($xml->donnees->eleve) )
  {
    foreach ($xml->donnees->eleve as $eleve)
    {
      $tab_eleve_fichier['sconet_id'][] = Clean::entier($eleve->attributes()->id);
      $tab_eleve_fichier['nom'][]       = Clean::nom($eleve->attributes()->nom);
      $tab_eleve_fichier['prenom'][]    = Clean::prenom($eleve->attributes()->prenom);
      // Indication des (in-)validations
      $tab_validations = array();
      if($eleve->palier)
      {
        foreach ($eleve->palier as $palier)
        {
          $palier_id = Clean::entier($palier->attributes()->id);
          if($palier->competence)
          {
            foreach ($palier->competence as $competence)
            {
              $pilier_id = Clean::entier($competence->attributes()->id);
              if( ($competence->validation) && ($competence->validation->date) )
              {
                $date = Clean::texte($competence->validation->date) ;
                $etat = ($competence->validation->etat) ? Clean::entier($competence->validation->etat) : 1 ;
                $info = ($competence->validation->info) ? html_decode($competence->validation->info) : $action ;
                $tab_validations['pilier'][$pilier_id] = array('date'=>$date,'etat'=>$etat,'info'=>$info);
              }
              if( ($competence->item) && ($competence->item->renseignement) && ($competence->item->renseignement->date) )
              {
                foreach ($competence->item as $item)
                {
                  if( ($item->renseignement) && ($item->renseignement->date) )
                  {
                    $item_id = Clean::entier($item->attributes()->id);
                    $date = Clean::texte($item->renseignement->date) ;
                    $etat = ($item->renseignement->etat) ? Clean::entier($item->renseignement->etat) : 1 ;
                    $info = ($item->renseignement->info) ? html_decode($item->renseignement->info) : $action ;
                    $tab_validations['entree'][$item_id] = array('date'=>$date,'etat'=>$etat,'info'=>$info);
                  }
                }
              }
            }
          }
        }
      }
      $tab_eleve_fichier['validations'][] = $tab_validations;
    }
  }
  // On récupère les infos de la base pour les comparer ; on commence par les identités des élèves
  $tab_eleve_base                = array();
  $tab_eleve_base['sconet_id']   = array();
  $tab_eleve_base['nom']         = array();
  $tab_eleve_base['prenom']      = array();
  $tab_eleve_base['validations'] = array();
  $DB_TAB = DB_STRUCTURE_SOCLE::DB_lister_eleves_identite_et_sconet();
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_eleve_base['sconet_id'][$DB_ROW['user_id']]  = (int)$DB_ROW['user_sconet_id'];
    $tab_eleve_base['nom'][$DB_ROW['user_id']]        = $DB_ROW['user_nom'];
    $tab_eleve_base['prenom'][$DB_ROW['user_id']]     = $DB_ROW['user_prenom'];
  }
  // Voyons donc si on trouve les élèves du fichier dans la base
  $tab_i_fichier_TO_id_base = array();
  // Pour préparer l'affichage
  $lignes_ignorer   = '';
  $lignes_modifier  = '';
  $lignes_inchanger = '';
  $tab_indices_fichier = (isset($tab_eleve_fichier['sconet_id'])) ? array_keys($tab_eleve_fichier['sconet_id']) : array() ;
  // Parcourir chaque entrée du fichier
  foreach($tab_indices_fichier as $i_fichier)
  {
    $id_base = FALSE;
    // Recherche sur sconet_id
    if( (!$id_base) && ($tab_eleve_fichier['sconet_id'][$i_fichier]) )
    {
      $id_base = array_search($tab_eleve_fichier['sconet_id'][$i_fichier],$tab_eleve_base['sconet_id']);
    }
    // Si pas trouvé, recherche sur nom prénom
    if(!$id_base)
    {
      $tab_id_nom    = array_keys($tab_eleve_base['nom'],$tab_eleve_fichier['nom'][$i_fichier]);
      $tab_id_prenom = array_keys($tab_eleve_base['prenom'],$tab_eleve_fichier['prenom'][$i_fichier]);
      $tab_id_commun = array_intersect($tab_id_nom,$tab_id_prenom);
      $nb_homonymes  = count($tab_id_commun);
      if($nb_homonymes==1)
      {
        list($inutile,$id_base) = each($tab_id_commun);
      }
    }
    // Cas [1] : non trouvé dans la base : contenu à ignorer
    if(!$id_base)
    {
      $lignes_ignorer .= '<li><em>Ignoré</em> (non trouvé dans la base) : '.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).' ('.$tab_users_fichier['sconet_id'][$i_fichier].')</li>'.NL;
      unset( $tab_eleve_fichier['validations'][$i_fichier] );
    }
    // Cas [2] : trouvé dans la base : contenu à étudier par la suite
    else
    {
      $tab_i_fichier_TO_id_base[$i_fichier] = $id_base;
    }
  }
  unset( $tab_eleve_fichier['sconet_id'] , $tab_eleve_fichier['nom'] , $tab_eleve_fichier['prenom'] );
  if(count($tab_i_fichier_TO_id_base))
  {
    // On récupère les infos de la base pour les comparer ; on poursuit par les validations
    $tab_validations  = array();
    $listing_eleve_id = implode(',',$tab_i_fichier_TO_id_base);
    $only_positives   = FALSE ;
    // Validations des items
    $DB_TAB = DB_STRUCTURE_SOCLE::DB_lister_validations_items($listing_eleve_id,$only_positives);
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_validations[$DB_ROW['user_id']]['entree'][$DB_ROW['entree_id']] = $DB_ROW['validation_entree_date'] ; // Pas besoin d'autre chose que la date
    }
    // Validations des compétences
    $DB_TAB = DB_STRUCTURE_SOCLE::DB_lister_validations_competences($listing_eleve_id,$only_positives);
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_validations[$DB_ROW['user_id']]['pilier'][$DB_ROW['pilier_id']] = $DB_ROW['validation_pilier_date'] ; // Pas besoin d'autre chose que la date
    }
    // Parcourir chaque entrée du fichier
    foreach($tab_i_fichier_TO_id_base as $i_fichier => $id_base)
    {
      $nb_modifs = 0;
      // les validations de piliers
      if(isset($tab_eleve_fichier['validations'][$i_fichier]['pilier']))
      {
        foreach($tab_eleve_fichier['validations'][$i_fichier]['pilier'] as $pilier_id => $tab_infos_fichier)
        {
          if(!isset($tab_validations[$id_base]['pilier'][$pilier_id]))
          {
            DB_STRUCTURE_SOCLE::DB_ajouter_validation('pilier',$id_base,$pilier_id,$tab_infos_fichier['etat'],$tab_infos_fichier['date'],$tab_infos_fichier['info']);
            $nb_modifs++;
          }
          elseif($tab_validations[$id_base]['pilier'][$pilier_id]<$tab_infos_fichier['date'])
          {
            DB_STRUCTURE_SOCLE::DB_modifier_validation('pilier',$id_base,$pilier_id,$tab_infos_fichier['etat'],$tab_infos_fichier['date'],$tab_infos_fichier['info']);
            $nb_modifs++;
          }
        }
      }
      // les validations d'items
      if(isset($tab_eleve_fichier['validations'][$i_fichier]['entree']))
      {
        foreach($tab_eleve_fichier['validations'][$i_fichier]['entree'] as $entree_id => $tab_infos_fichier)
        {
          if(!isset($tab_validations[$id_base]['entree'][$entree_id]))
          {
            DB_STRUCTURE_SOCLE::DB_ajouter_validation('entree',$id_base,$entree_id,$tab_infos_fichier['etat'],$tab_infos_fichier['date'],$tab_infos_fichier['info']);
            $nb_modifs++;
          }
          elseif($tab_validations[$id_base]['entree'][$entree_id]<$tab_infos_fichier['date'])
          {
            DB_STRUCTURE_SOCLE::DB_modifier_validation('entree',$id_base,$entree_id,$tab_infos_fichier['etat'],$tab_infos_fichier['date'],$tab_infos_fichier['info']);
            $nb_modifs++;
          }
        }
      }
      if($nb_modifs)
      {
        $s = ($nb_modifs>1) ? 's' : '' ;
        $lignes_modifier .= '<li><em>Modifié</em> ('.$nb_modifs.' import'.$s.' de validation'.$s.' ) : '.html($tab_eleve_base['nom'][$id_base].' '.$tab_eleve_base['prenom'][$id_base]).' ('.$tab_eleve_base['sconet_id'][$id_base].')</li>'.NL;
      }
      else
      {
        $lignes_inchanger .= '<li><em>Inchangé</em> (pas de validations nouvelles) : '.html($tab_eleve_base['nom'][$id_base].' '.$tab_eleve_base['prenom'][$id_base]).' ('.$tab_eleve_base['sconet_id'][$id_base].')</li>'.NL;
      }
    }
  }
  // On complète et on affiche le bilan
  Json::add_str('<li><label class="valide">Fichier d\'import traité.</label></li>'.NL);
  Json::add_str($lignes_modifier);
  Json::add_str($lignes_inchanger);
  Json::add_str($lignes_ignorer);
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Il se peut que rien n'ait été récupéré à cause de l'upload d'un fichier trop lourd
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if(empty($_POST))
{
  Json::end( FALSE , 'Aucune donnée reçue ! Fichier trop lourd ? '.InfoServeur::minimum_limitations_upload() );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
