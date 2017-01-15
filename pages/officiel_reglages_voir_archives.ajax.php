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

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération des valeurs transmises
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// TODO : FILTRER SELON L'ETABLISSEMENT
$structure_uai  = '';
$uai_origine    = (isset($_POST['f_uai_origine'])) ? Clean::uai($_POST['f_uai_origine']) : '';
$annee_scolaire = (isset($_POST['f_annee']))       ? Clean::code($_POST['f_annee'])      : '';
$periode_id     = (isset($_POST['f_periode']))     ? Clean::entier($_POST['f_periode'])  : 0;

$tab_eleve    = (isset($_POST['listing_ids'])) ? explode(',',$_POST['listing_ids']) : array() ;
$tab_type_ref = (isset($_POST['f_type_ref']))  ? ( (is_array($_POST['f_type_ref'])) ? $_POST['f_type_ref'] : explode(',',$_POST['f_type_ref']) ) : array() ;
$tab_eleve    = array_filter( Clean::map('entier',$tab_eleve) , 'positif' );
$tab_type_ref = Clean::map('code',$tab_type_ref);

$tab_type = array();
$tab_ref  = array();

foreach($tab_type_ref as $type_ref)
{
  list($type,$ref) = explode('_',$type_ref) + array_fill(0,2,NULL); // Evite des NOTICE en initialisant les valeurs manquantes
  if( $type && $ref )
  {
    $tab_type[$type] = $type;
    $tab_ref[ $ref ] = $ref;
  }
}

if( empty($tab_eleve) || empty($tab_type) || empty($tab_ref) )
{
  Json::end( FALSE , 'Erreur avec les données transmises !' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer et générer les bilans demandés
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Erreur500::prevention_et_gestion_erreurs_fatales( TRUE /*memory*/ , TRUE /*time*/ );

$listing_eleve = implode(',',$tab_eleve);
$listing_type  = '"'.implode('","',$tab_type).'"';
$listing_ref   = '"'.implode('","',$tab_ref).'"';
list( $DB_TAB_Archives , $DB_TAB_Images ) = DB_STRUCTURE_OFFICIEL::DB_recuperer_officiel_archive_avec_infos( $listing_eleve , $structure_uai , $annee_scolaire , $listing_type , $listing_ref , $periode_id , $uai_origine );

$nb_archives = count($DB_TAB_Archives);
if( !$nb_archives )
{
  Json::end( FALSE , 'Aucune archive trouvée satisfaisant aux conditions demandées !' );
}
if( $nb_archives>250 )
{
  Json::end( FALSE , 'Plus de 250 archives trouvées : veuillez ajouter un critère de sélection.' );
}

// Remplacement des md5 par les images
foreach($DB_TAB_Archives as $key => $DB_ROW)
{
  for( $image_num=1 ; $image_num<=4 ; $image_num++)
  {
    $image_md5 = $DB_ROW['archive_md5_image'.$image_num];
    if( $image_md5 && isset($DB_TAB_Images[$image_md5][0]) )
    {
      $image_base64 = $DB_TAB_Images[$image_md5][0]['archive_image_contenu'];
      $DB_TAB_Archives[$key]['archive_contenu'] = str_replace( $image_md5 , $image_base64 , $DB_TAB_Archives[$key]['archive_contenu'] );
    }
    else
    {
      // sinon, référence d'une image non présente dans sacoche_officiel_archive_image : ce n'est pas normal
      $DB_TAB_Archives[$key]['archive_contenu'] = str_replace( $image_md5 , '' , $DB_TAB_Archives[$key]['archive_contenu'] );
    }
  }
}

// Dossier accueillant les PDF
$is_make_zip = ( ($nb_archives>1) || $uai_origine ) ? TRUE : FALSE;
// TODO : UTILISER LE DOSSIER "OFFICIEL" AVEC UNE DUREE DE CONSERVATION D'1 SEMAINE
if($is_make_zip)
{
  $chemin_temp_pdf = CHEMIN_DOSSIER_EXPORT.'pdf_'.mt_rand().DS;
  FileSystem::creer_ou_vider_dossier($chemin_temp_pdf);
  $pdf_fin_date_alea = '';
}
else
{
  $chemin_temp_pdf = CHEMIN_DOSSIER_EXPORT;
  $pdf_fin_date_alea = '_'.FileSystem::generer_fin_nom_fichier__date_et_alea();
}
// Génération des documents
foreach($DB_TAB_Archives as $DB_ROW)
{
  // Instanciation de la classe
  $tab_classname = array(
    'livret'   => 'PDF_livret_scolaire',
    'bulletin' => 'PDF_item_synthese',
    'releve'   => 'PDF_item_releve',
    'palier'   => 'PDF_socle_releve', // non implémenté en attente de la mise en place de la refonte du socle -> à virer
  );
  $key = ($DB_ROW['archive_type']=='sacoche') ? $DB_ROW['archive_ref'] : 'livret' ;
  $classname = $tab_classname[$key];
  $archive_PDF = new $classname();
  // Fabrication de l'archive PDF à partir du JSON enregistré
  $tab_archive = json_decode($DB_ROW['archive_contenu'], TRUE);
  foreach($tab_archive as $archive)
  {
    list( $methode , $tab_param ) = $archive;
    call_user_func_array( array( $archive_PDF , $methode ) , $tab_param );
  }
  // Écriture du PDF
  $fichier_nom = 'archive_'.Clean::fichier($DB_ROW['structure_uai']).'_'.Clean::fichier($DB_ROW['annee_scolaire']).'_'.$DB_ROW['archive_type'].'_'.$DB_ROW['archive_ref'].'_'.Clean::fichier($DB_ROW['periode_nom']).'_'.Clean::fichier($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).$pdf_fin_date_alea.'.pdf';
  FileSystem::ecrire_sortie_PDF( $chemin_temp_pdf.$fichier_nom  , $archive_PDF  );
}
// On zippe l'ensemble
if($is_make_zip)
{
  $fichier_nom  = 'archive_';
  $fichier_nom .= (!$uai_origine)       ? '' : Clean::fichier($uai_origine).'_' ;
  $fichier_nom .= (!$structure_uai)     ? '' : Clean::fichier($DB_ROW['structure_uai']).'_' ;
  $fichier_nom .= (!$annee_scolaire)    ? '' : Clean::fichier($DB_ROW['annee_scolaire']).'_' ;
  $fichier_nom .= (count($tab_type)>1)  ? '' : $type.'_' ;
  $fichier_nom .= (count($tab_ref)>1)   ? '' : $ref.'_' ;
  $fichier_nom .= (!$periode_id)        ? '' : Clean::fichier($DB_ROW['periode_nom']).'_' ;
  $fichier_nom .= (count($tab_eleve)>1) ? '' : Clean::fichier($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).'_' ;
  $fichier_nom .= FileSystem::generer_fin_nom_fichier__date_et_alea().'.zip';
  $result = FileSystem::zip_fichiers( $chemin_temp_pdf , CHEMIN_DOSSIER_EXPORT , $fichier_nom );
  if($result!==TRUE)
  {
    Json::end( FALSE , $result );
  }
  FileSystem::supprimer_dossier($chemin_temp_pdf);
}
// Retour
// TODO : SI $uai_origine TRANSMIS, PROPOSER ENVOI AUTOMATIQUE D'UN MAIL TYPE AVEC UN LIEN VERS LE FICHIER GENERE, (on a l'adresse de l'établ d'origine et celle de l'établ actuel)
$s = ($nb_archives>1) ? 's' : '' ;
$texte = ($is_make_zip)
        ? $nb_archives.' archive'.$s.' générée'.$s.' dans <span class="file file_zip">ce fichier <em>zip</em></span>.'
        : 'Archive générée dans <span class="file file_pdf">ce fichier <em>pdf</em></span>.' ;
Json::add_row( 'texte' ,$texte );
Json::add_row( 'href' , URL_DIR_EXPORT.$fichier_nom );
Json::end( TRUE );

?>
