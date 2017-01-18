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
if($_SESSION['SESAMATH_ID']==ID_DEMO){Json::end( FALSE , 'Action désactivée pour la démo.' );}

$action  = (isset($_POST['f_action']))  ? Clean::texte($_POST['f_action'])  : '' ;
$section = (isset($_POST['f_section'])) ? Clean::texte($_POST['f_section']) : '' ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Il se peut que rien n'ait été récupéré à cause de l'upload d'un fichier trop lourd
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if(empty($_POST))
{
  Json::end( FALSE , 'Aucune donnée reçue ! Fichier trop lourd ? '.InfoServeur::minimum_limitations_upload() );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Import d'un fichier SIECLE
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$annee_scolaire = To::annee_scolaire('siecle');
$tab_fichier = array(
  'Eleves'       => 'ElevesSansAdresses.xml' ,
  'sts_emp_UAI'  => 'sts_emp_'.$_SESSION['WEBMESTRE_UAI'].'_'.$annee_scolaire.'.xml' ,
  'Nomenclature' => 'Nomenclature.xml' ,
);

if( isset($tab_fichier[$action]) )
{
  // Nom du fichier à extraire si c'est un fichier zippé
  $nom_fichier_extrait = $tab_fichier[$action];
  // Récupération du fichier
  $result = FileSystem::recuperer_upload( CHEMIN_DOSSIER_IMPORT /*fichier_chemin*/ , $nom_fichier_extrait /*fichier_nom*/ , array('zip','xml') /*tab_extensions_autorisees*/ , NULL /*tab_extensions_interdites*/ , NULL /*taille_maxi*/ , $nom_fichier_extrait /*filename_in_zip*/ );
  if($result!==TRUE)
  {
    Json::end( FALSE , $result );
  }
  $xml = @simplexml_load_file(CHEMIN_DOSSIER_IMPORT.$nom_fichier_extrait);
  if($xml===FALSE)
  {
    Json::end( FALSE , 'Le fichier transmis n\'est pas un XML valide !' );
  }
  // Vérifications
  if($action=='sts_emp_UAI')
  {
    $editeur_prive_edt = @(string)$xml->PARAMETRES->APPLICATION_SOURCE;
    if($editeur_prive_edt)
    {
      Json::end( FALSE , 'Le fichier transmis est issu d\'un éditeur privé d\'emploi du temps, pas de STS !' );
    }
  }
  $uai = ($action=='sts_emp_UAI') ? @(string)$xml->PARAMETRES->UAJ->attributes()->CODE : @(string)$xml->PARAMETRES->UAJ ;
  if(!$uai)
  {
    Json::end( FALSE , 'Le contenu du fichier transmis ne correspond pas à ce qui est attendu !' );
  }
  if($uai!=$_SESSION['WEBMESTRE_UAI'])
  {
    Json::end( FALSE , 'Le fichier transmis est issu de l\'établissement '.$uai.' et non '.$_SESSION['WEBMESTRE_UAI'].' !' );
  }
  $annee = ($action=='sts_emp_UAI') ? @(string)$xml->PARAMETRES->ANNEE_SCOLAIRE->attributes()->ANNEE : @(string)$xml->PARAMETRES->ANNEE_SCOLAIRE ;
  $annee_scolaire = To::annee_scolaire('siecle');
  if( $annee_scolaire !== $annee )
  {
    Json::end( FALSE , 'Le fichier transmis ne correspond pas à l\'année scolaire '.$annee_scolaire.' !' );
  }
  // Archivage
  DB_STRUCTURE_SIECLE::DB_ajouter_import( $action , $annee , $xml );
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération des valeurs transmises
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action!='recolter') && ($action!='generer_export') )
{
  Json::end( FALSE , 'Erreur avec les données transmises !' );
}

$PAGE_REF   = (isset($_POST['f_page_ref']))  ? Clean::texte($_POST['f_page_ref']) : '';
$periode    = (isset($_POST['f_periode']))   ? Clean::texte($_POST['f_periode'])  : '';
$classe_id  = (isset($_POST['f_classe']))    ? Clean::entier($_POST['f_classe'])  : 0;
$tab_eleve  = (isset($_POST['listing_ids'])) ? explode(',',$_POST['listing_ids']) : array() ;
$tab_eleve  = array_filter( Clean::map('entier',$tab_eleve) , 'positif' );

// Tableaux communs utiles

$tab_periode_livret = array(
  'periodeT1' => 'Trimestre 1/3' ,
  'periodeT2' => 'Trimestre 2/3' ,
  'periodeT3' => 'Trimestre 3/3' ,
  'periodeS1' => 'Semestre 1/2'  ,
  'periodeS2' => 'Semestre 2/2'  ,
  'cycle'     => 'Fin de cycle'  ,
  'college'   => 'Fin du collège',
);

$tab_export_type = array(
  'cp'     => 'ecole',
  'ce1'    => 'ecole',
  'ce2'    => 'ecole',
  'cm1'    => 'ecole',
  'cm2'    => 'ecole',
  '6e'     => 'college',
  '5e'     => 'college',
  '4e'     => 'college',
  '3e'     => 'college',
  'cycle1' => 'cycle_maternelle',
  'cycle2' => 'cycle_ecole',
  'cycle3' => 'cycle_college',
  'cycle4' => 'cycle_college',
);

// Vérification période

if( !isset($tab_periode_livret[$periode]) && ( ($action=='recolter') || ($periode!='0') ) )
{
  Json::end( FALSE , 'Période "'.html($periode).'" inconnue !' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récolter les données pour les élèves d'une classes et d'une période
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='recolter')
{
  $periode_nom = $tab_periode_livret[$periode];
  require(CHEMIN_DOSSIER_INCLUDE.'code_livret_recolter.php');
  // Normalement, on est stoppé avant.
  Json::end( FALSE , 'Problème de code : point d\'arrêt manquant !' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Générer le fichier d'export en fonction des critères sélectionnés
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Elèves
if( empty($tab_eleve) )
{
  Json::end( FALSE , 'Erreur avec les données transmises !' );
}
$liste_eleve_id = implode(',',$tab_eleve);

// Période
if(substr($periode,0,7)=='periode')
{
  $PAGE_PERIODICITE = 'periode';
  $JOINTURE_PERIODE = substr($periode,7);
}
else
{
  $PAGE_PERIODICITE = ($periode!='0') ? $periode : '' ;
  $JOINTURE_PERIODE = '';
}

// Au cas où...
Erreur500::prevention_et_gestion_erreurs_fatales( TRUE /*memory*/ , TRUE /*time*/ );

// Données
$DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_livret_export_eleves( $liste_eleve_id , $PAGE_PERIODICITE , $JOINTURE_PERIODE );
if( empty($DB_TAB) )
{
  Json::end( FALSE , 'Aucun bilan trouvé selon les critères transmis !' );
}

// On extrait les données élèves et on mutualise les données communes
$tab_export_objet   = array();
$tab_export_eleve   = array();
$tab_export_donnees = array();
$tab_export_commun  = array(
  'periode'          => array(),
  'responsable-etab' => array(),
  'discipline'       => array(),
  'enseignant'       => array(),
  'element'          => array(),
  'epi'              => array(),
  'ap'               => array(),
  'parcours'         => array(),
  'viesco'           => array(),
);

foreach($DB_TAB as $key => $DB_ROW)
{
  $export_objet = $tab_export_type[$DB_ROW['livret_page_ref']];
  $tab_export_objet[ $export_objet ] = TRUE;
  $tab_export_donnees[$key] = json_decode($DB_ROW['export_contenu'], TRUE);
  $tab_export_eleve[$DB_ROW['user_id']] = $tab_export_donnees[$key]['eleve'];
  if($export_objet=='college')
  {
    $tab_export_commun['responsable-etab'] = array_merge( $tab_export_commun['responsable-etab'] , $tab_export_donnees[$key]['commun']['responsable-etab'] );
    $tab_export_commun['discipline']       = array_merge( $tab_export_commun['discipline']       , $tab_export_donnees[$key]['commun']['discipline']       );
    $tab_export_commun['epi']              = array_merge( $tab_export_commun['epi']              , $tab_export_donnees[$key]['commun']['epi']              );
    $tab_export_commun['ap']               = array_merge( $tab_export_commun['ap']               , $tab_export_donnees[$key]['commun']['ap']               );
    $tab_export_commun['viesco']           = array_merge( $tab_export_commun['viesco']           , $tab_export_donnees[$key]['commun']['viesco']           );
  }
  if($export_objet=='ecole')
  {
    $tab_export_commun['classe']           = array_merge( $tab_export_commun['classe']           , $tab_export_donnees[$key]['commun']['classe']           );
  }
  $tab_export_commun['periode']          = array_merge( $tab_export_commun['periode']          , $tab_export_donnees[$key]['commun']['periode']          );
  $tab_export_commun['enseignant']       = array_merge( $tab_export_commun['enseignant']       , $tab_export_donnees[$key]['commun']['enseignant']       );
  $tab_export_commun['element']          = array_merge( $tab_export_commun['element']          , $tab_export_donnees[$key]['commun']['element']          );
  $tab_export_commun['parcours']         = array_merge( $tab_export_commun['parcours']         , $tab_export_donnees[$key]['commun']['parcours']         );
  unset( $tab_export_donnees[$key]['eleve'] , $tab_export_donnees[$key]['commun'] );
}

if( isset($tab_export_objet['cycle_maternelle']) )
{
  Json::end( FALSE , 'L\'application nationale ne gère pas le bilan de maternelle (qui ne fait pas partie du Livret Scolaire) !' );
}

if( count($tab_export_objet) > 1 )
{
  Json::end( FALSE , 'Il est impossible d\'exporter dans un fichier unique des bilans de différents formats('.implode(' / ',array_keys($tab_export_objet)).') !' );
}

$export_objet = key($tab_export_objet);

// On attaque le XML : prologue + élément racine

$tab_xml = array();
$tab_xml[] = '<?xml version="1.0" encoding="UTF-8"?>';
switch($export_objet)
{
  case 'college' : $tab_xml[] = '<lsun-bilans xmlns="urn:fr:edu:scolarite:lsun:bilans:import" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="urn:fr:edu:scolarite:lsun:bilans:import import-bilan-complet.xsd" schemaVersion="2.0">'; break;
  case 'ecole'   : $tab_xml[] = '<lsun-bilans xmlns="urn:fr:edu:scolarite:lsun:bilans:import" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="urn:fr:edu:scolarite:lsun:bilans:import import-bilan-1d.xsd" schemaVersion="1.0">'; break;
}
// entête
$tab_xml[] = ' <entete>';
$tab_xml[] = '  <editeur>Sésamath</editeur>';
$tab_xml[] = '  <application>SACoche</application>';
$tab_xml[] = '  <etablissement>'.html($_SESSION['WEBMESTRE_UAI']).'</etablissement>';
$tab_xml[] = ' </entete>';
$tab_xml[] = ' <donnees>';

// Attention, pour la conformité du XML, l'odre est important (j'aurais bien mis <eleves> en premier mais ce n'est pas permis).

if($export_objet=='college')
{
  // responsables-etab
  $tab_xml[] = '  <responsables-etab>';
  foreach($tab_export_commun['responsable-etab'] as $id => $tab)
  {
    $tab_xml[] = '   <responsable-etab id="'.$id.'" libelle="'.html($tab['libelle']).'" />';
  }
  $tab_xml[] = '  </responsables-etab>';
}

if($export_objet=='ecole')
{
  // classes
  $tab_xml[] = '  <classes>';
  foreach($tab_export_commun['classe'] as $id => $tab)
  {
    $tab_xml[] = '   <classe id="'.$id.'" id-be="'.$tab['id-be'].'" libelle="'.html($tab['libelle']).'" />';
  }
  $tab_xml[] = '  </classes>';
}

// eleves
$nb_eleves = count($tab_export_eleve);
$tab_xml[] = '  <eleves>';
foreach($tab_export_eleve as $eleve_id => $tab)
{
  switch($export_objet)
  {
    case 'college' : $tab_xml[] = '   <eleve id="'.$tab['id'].'" id-be="'.$tab['id-be'].'" nom="'.html($tab['nom']).'" prenom="'.html($tab['prenom']).'" code-division="'.html($tab['code-division']).'" />'; break;
    case 'ecole'   : $tab_xml[] = '   <eleve id="'.$tab['id'].'" ine="'.$tab['ine'].'" classe-ref="'.html($tab['classe-ref']).'" nom="'.html($tab['nom']).'" prenom="'.html($tab['prenom']).'" />'; break;
  }
  
}
$tab_xml[] = '  </eleves>';
unset( $tab_export_eleve );

// periodes
$tab_xml[] = '  <periodes>';
foreach($tab_export_commun['periode'] as $id => $tab)
{
  $tab_xml[] = '   <periode id="'.$id.'" millesime="'.$tab['millesime'].'" indice="'.$tab['indice'].'" nb-periodes="'.$tab['nb-periodes'].'" />';
}
$tab_xml[] = '  </periodes>';

// disciplines
$tab_xml[] = '  <disciplines>';
foreach($tab_export_commun['discipline'] as $id => $tab)
{
  $tab_xml[] = '   <discipline id="'.$id.'" code="'.$tab['code'].'" modalite-election="'.$tab['modalite-election'].'" libelle="'.html($tab['libelle']).'" />';
}
$tab_xml[] = '  </disciplines>';

// enseignants
$tab_xml[] = '  <enseignants>';
foreach($tab_export_commun['enseignant'] as $id => $tab)
{
  $civilite = $tab['civilite'] ? ' civilite="'.$tab['civilite'].'"' : '' ;
  switch($export_objet)
  {
    case 'college' : $tab_xml[] = '   <enseignant id="'.$id.'" type="'.$tab['type'].'" id-sts="'.$tab['id-sts'].'"'.$civilite.' nom="'.html($tab['nom']).'" prenom="'.html($tab['prenom']).'" />'; break;
    case 'ecole'   : $tab_xml[] = '   <enseignant id="'.$id.'"'.$civilite.' nom="'.html($tab['nom']).'" prenom="'.html($tab['prenom']).'" />'; break;
  }
}
$tab_xml[] = '  </enseignants>';

// elements-programme
if(!empty($tab_export_commun['element']))
{
  $tab_xml[] = '  <elements-programme>';
  foreach($tab_export_commun['element'] as $id => $libelle)
  {
    $tab_xml[] = '   <element-programme id="'.$id.'" libelle="'.html($libelle).'" />';
  }
  $tab_xml[] = '  </elements-programme>';
}

// parcours-communs (appréciation sur la classe)
if(!empty($tab_export_commun['parcours']))
{
  $tab_parcours = array();
  foreach($tab_export_commun['parcours'] as $key => $tab)
  {
    $tab_parcours[$tab['periode-ref'].'¤'.$tab['code-division']][] = $tab;
  }
  $tab_xml[] = '  <parcours-communs>';
  foreach($tab_parcours as $key => $tab_ensemble)
  {
    list( $periode_ref , $code_division ) = explode('¤',$key,2);
    $tab_xml[] = '   <parcours-commun periode-ref="'.$periode_ref.'" code-division="'.html($code_division).'">';
    foreach($tab_ensemble as $tab)
    {
      $tab_xml[] = '    <parcours code="'.$tab['code'].'">'.html($tab['projet']).'</parcours>';
    }
    $tab_xml[] = '   </parcours-commun>';
  }
  $tab_xml[] = '  </parcours-communs>';
}

// vies-scolaires-communs (appréciation sur la classe)
// --> pas de saisie proposée dans SACoche

// epis & epis-groupes
if(!empty($tab_export_commun['epi']))
{
  $tab_xml[] = '  <epis>';
  foreach($tab_export_commun['epi'] as $key => $tab)
  {
    $tab_xml[] = '   <epi id="'.$tab['id'].'" intitule="'.html($tab['intitule']).'" thematique="'.$tab['thematique'].'" discipline-refs="'.implode(' ',array_unique($tab['discipline-refs'])).'">';
    if($tab['description'])
    {
      $tab_xml[] = '    <description>'.html($tab['description']).'</description>';
    }
    $tab_xml[] = '   </epi>';
  }
  $tab_xml[] = '  </epis>';
    // Pas de sous-groupes gérés dans SACoche, l'EPI est défini sur une classe...
  $tab_xml[] = '  <epis-groupes>';
  foreach($tab_export_commun['epi'] as $key => $tab)
  {
    $tab_xml[] = '   <epi-groupe id="'.$key.'GR" intitule="'.html($tab['intitule']).'" epi-ref="'.$tab['id'].'">';
    // Pour le commentaire sur le groupe, on reporte la description (c'est ce que veut LSU de toutes façons).
    if($tab['description'])
    {
      $tab_xml[] = '    <commentaire>'.html($tab['description']).'</commentaire>';
    }
    $tab_xml[] = '    <enseignants-disciplines>';
    foreach($tab['discipline-refs'] as $key => $discipline_ref)
    {
      $enseignant_ref = $tab['enseignant-refs'][$key];
      $tab_xml[] = '     <enseignant-discipline discipline-ref="'.$discipline_ref.'" enseignant-ref="'.$enseignant_ref.'" />';
    }
    $tab_xml[] = '    </enseignants-disciplines>';
    $tab_xml[] = '   </epi-groupe>';
  }
  $tab_xml[] = '  </epis-groupes>';
}

// acc-persos & acc-persos-groupes
if(!empty($tab_export_commun['ap']))
{
  $tab_xml[] = '  <acc-persos>';
  foreach($tab_export_commun['ap'] as $key => $tab)
  {
    $tab_xml[] = '   <acc-perso id="'.$tab['id'].'" intitule="'.html($tab['intitule']).'" discipline-refs="'.implode(' ',array_unique($tab['discipline-refs'])).'">';
    if($tab['description'])
    {
      $tab_xml[] = '    <description>'.html($tab['description']).'</description>';
    }
    $tab_xml[] = '   </acc-perso>';
  }
  $tab_xml[] = '  </acc-persos>';
    // Pas de sous-groupes gérés dans SACoche, l'AP est défini sur une classe...
  $tab_xml[] = '  <acc-persos-groupes>';
  foreach($tab_export_commun['ap'] as $key => $tab)
  {
    $tab_xml[] = '   <acc-perso-groupe id="'.$key.'GR" intitule="'.html($tab['intitule']).'" acc-perso-ref="'.$tab['id'].'">';
    // Pour le commentaire sur le groupe, on reporte la description (c'est ce que veut LSU de toutes façons).
    if($tab['description'])
    {
      $tab_xml[] = '    <commentaire>'.html($tab['description']).'</commentaire>';
    }
    $tab_xml[] = '    <enseignants-disciplines>';
    foreach($tab['discipline-refs'] as $key => $discipline_ref)
    {
      $enseignant_ref = $tab['enseignant-refs'][$key];
      $tab_xml[] = '     <enseignant-discipline discipline-ref="'.$discipline_ref.'" enseignant-ref="'.$enseignant_ref.'" />';
    }
    $tab_xml[] = '    </enseignants-disciplines>';
    $tab_xml[] = '   </acc-perso-groupe>';
  }
  $tab_xml[] = '  </acc-persos-groupes>';
}

unset( $tab_export_commun );

// bilans périodiques
$nb_bilans = count($tab_export_donnees);
$rubrique_sans_element = FALSE ;
$tab_xml[] = '  <bilans-periodiques>';
foreach($tab_export_donnees as $key => $tab_donnee_bilan)
{
  $tab_bilan = $tab_donnee_bilan['bilan'];
  $prof_princ_refs = !empty($tab_bilan['prof-princ-refs']) ? ' prof-princ-refs="'.$tab_bilan['prof-princ-refs'].'"' : '' ;
  $tab_xml[] = '   <bilan-periodique'.$prof_princ_refs.' eleve-ref="'.$tab_bilan['eleve-ref'].'" periode-ref="'.$tab_bilan['periode-ref'].'" date-conseil-classe="'.$tab_bilan['date-conseil-classe'].'" date-scolarite="'.$tab_bilan['date-scolarite'].'" date-verrou="'.$tab_bilan['date-verrou'].'" responsable-etab-ref="'.$tab_bilan['responsable-etab-ref'].'">';
  $champ_position = $tab_bilan['position'];
  // liste-acquis
  $tab_xml[] = '    <liste-acquis>';
  foreach($tab_donnee_bilan['acquis'] as $discipline_ref => $tab)
  {
    $element_programme_refs = !empty($tab['elements']) ? implode(' ',$tab['elements']) : 'EL0' ;
    $position_eleve  = is_null($tab[$champ_position]) ? ' eleve-non-note="true"' : ' '.$champ_position.'="'.$tab[$champ_position].'"' ;
    $position_classe = ($champ_position=='positionnement') ? '' : ( is_null($tab['moyenne-structure']) ? ' structure-non-notee="true"' : ' moyenne-structure="'.$tab['moyenne-structure'].'"' ) ;
    $tab_xml[] = '     <acquis discipline-ref="'.$discipline_ref.'" enseignant-refs="'.implode(' ',$tab['profs']).'" element-programme-refs="'.$element_programme_refs.'"'.$position_eleve.$position_classe.' >';
    // appréciation obligatoire sauf si élève non noté
    if( $tab['appreciation'] || !is_null($tab[$champ_position]) )
    {
      $tab['appreciation'] = !empty($tab['appreciation']) ? $tab['appreciation'] : '-' ;
      $tab_xml[] = '      <appreciation>'.html($tab['appreciation']).'</appreciation>';
    }
    $rubrique_sans_element = $rubrique_sans_element || ($element_programme_refs=='EL0') ;
    $tab_xml[] = '     </acquis>';
  }
  $tab_xml[] = '    </liste-acquis>';
  // epis-eleve
  if(!empty($tab_donnee_bilan['epi']))
  {
    $tab_xml[] = '    <epis-eleve>';
    foreach($tab_donnee_bilan['epi'] as $id => $tab)
    {
      $tab_xml[] = '     <epi-eleve epi-groupe-ref="'.$id.'GR">';
      if(!empty($tab['appreciation']))
      {
        $tab_xml[] = '      <commentaire>'.html($tab['appreciation']).'</commentaire>';
      }
      $tab_xml[] = '     </epi-eleve>';
    }
    $tab_xml[] = '    </epis-eleve>';
  }
  // acc-persos-eleve
  if(!empty($tab_donnee_bilan['ap']))
  {
    $tab_xml[] = '    <acc-persos-eleve>';
    foreach($tab_donnee_bilan['ap'] as $id => $tab)
    {
      $tab_xml[] = '     <acc-perso-eleve acc-perso-groupe-ref="'.$id.'GR">';
      if(!empty($tab['appreciation']))
      {
        $tab_xml[] = '      <commentaire>'.html($tab['appreciation']).'</commentaire>';
      }
      $tab_xml[] = '     </acc-perso-eleve>';
    }
    $tab_xml[] = '    </acc-persos-eleve>';
  }
  // parcours
  if(!empty($tab_donnee_bilan['parcours']))
  {
    $tab_xml[] = '    <liste-parcours>';
    foreach($tab_donnee_bilan['parcours'] as $id => $tab)
    {
      $tab['appreciation'] = !empty($tab['appreciation']) ? $tab['appreciation'] : '-' ;
      $tab_xml[] = '     <parcours code="'.$tab['code'].'">'.html($tab['appreciation']).'</parcours>';
    }
    $tab_xml[] = '    </liste-parcours>';
  }
  // modalites-accompagnement
  if(!empty($tab_donnee_bilan['modaccomp']))
  {
    $tab_xml[] = '    <modalites-accompagnement>';
    foreach($tab_donnee_bilan['modaccomp'] as $code => $info_complement)
    {
      if( ($code!='PPRE') || !$info_complement )
      {
        $tab_xml[] = '     <modalite-accompagnement code="'.$code.'" />';
      }
      else
      {
        $tab_xml[] = '     <modalite-accompagnement code="'.$code.'"><complement-ppre>'.html($info_complement).'</complement-ppre></modalite-accompagnement>';
      }
    }
    $tab_xml[] = '    </modalites-accompagnement>';
  }
  // acquis-conseils
  $tab_synthese = $tab_donnee_bilan['synthese'];
  $tab_xml[] = '    <acquis-conseils>'.html($tab_synthese['appreciation']).'</acquis-conseils>';
  // vie-scolaire
  $tab_viesco = $tab_donnee_bilan['viesco'];
  $tab_xml[] = '    <vie-scolaire nb-retards="'.$tab_viesco['nb-retards'].'" nb-abs-justifiees="'.$tab_viesco['nb-abs-justifiees'].'" nb-abs-injustifiees="'.$tab_viesco['nb-abs-injustifiees'].'">'; // nb-heures-manquees non géré
  if(!empty($tab_viesco['appreciation']))
  {
    $tab_xml[] = '     <commentaire>'.html($tab_viesco['appreciation']).'</commentaire>';
  }
  $tab_xml[] = '    </vie-scolaire>';
  // socle
  // --> pas géré pour l'instant
  // responsables
  if(!empty($tab_donnee_bilan['responsables']))
  {
    $tab_xml[] = '    <responsables>';
    foreach($tab_donnee_bilan['responsables'] as $resp_num => $tab)
    {
      $civilite = $tab['civilite'] ? ' civilite="'.$tab['civilite'].'"'   : '' ;
      $ligne2   = $tab['ligne2']   ? ' ligne2="'.html($tab['ligne2']).'"' : '' ;
      $ligne3   = $tab['ligne3']   ? ' ligne3="'.html($tab['ligne3']).'"' : '' ;
      $ligne4   = $tab['ligne4']   ? ' ligne4="'.html($tab['ligne4']).'"' : '' ;
      $tab_xml[] = '     <responsable'.$civilite.' nom="'.html($tab['nom']).'" prenom="'.html($tab['prenom']).'" legal'.$resp_num.'="true">'; // lien-parente non géré (facultatif)
      $tab_xml[] = '      <adresse ligne1="'.html($tab['ligne1']).'"'.$ligne2.$ligne3.$ligne4.' code-postal="'.html($tab['code-postal']).'" commune="'.html($tab['commune']).'" />';
      $tab_xml[] = '     </responsable>';
    }
    $tab_xml[] = '    </responsables>';
  }
  $tab_xml[] = '   </bilan-periodique>';
  unset($tab_export_donnees[$key]);
}
$tab_xml[] = '  </bilans-periodiques>';

// fermeture des balises
$tab_xml[] = ' </donnees>';
$tab_xml[] = '</lsun-bilans>';

// Le xml
$export_xml = implode(NL,$tab_xml);
if($rubrique_sans_element)
{
  $export_xml = str_replace( '<elements-programme>' , '<elements-programme>'.NL.'   <element-programme id="EL0" libelle="-" />' , $export_xml );
}

// Vérification XML valide

// @see http://php.net/manual/fr/domdocument.schemavalidate.php#62032
function libxml_display_error($error)
{
  $return = '<p>';
  switch ($error->level)
  {
    case LIBXML_ERR_WARNING:
      $return .= "<b>Warning $error->code</b> : ";
      break;
    case LIBXML_ERR_ERROR:
      $return .= "<b>Error $error->code</b> : ";
      break;
    case LIBXML_ERR_FATAL:
      $return .= "<b>Fatal Error $error->code</b> : ";
      break;
  }
  $return .= trim($error->message);
  /*
  if ($error->file) {
    $return .=    " in <b>$error->file</b>";
  }
  $return .= " on line <b>$error->line</b>\n";
  */
  $return .= '</p>'.NL;
  return $return;
}

libxml_use_internal_errors(TRUE);
$dom_xml= new DOMDocument();
$dom_xml->loadXML($export_xml);
if(!$dom_xml->schemaValidate(CHEMIN_DOSSIER_XSD.'livret-scolaire_2d_v20.xsd'))
{
  $errors = libxml_get_errors();
  Json::add_str( '<p><label class="erreur">Fichier XML obtenu invalide ; veuillez prendre contact avec l\'assistance de SACoche.</label></p>' );
  foreach ($errors as $error)
  {
    Json::add_str( libxml_display_error($error) );
  }
  libxml_clear_errors();
  // Json::end( FALSE );
}

// Enregistrement du fichier
$fichier_extension = 'xml';
$fichier_nom = 'import-lsun-'.Clean::fichier($_SESSION['WEBMESTRE_UAI']).'_'.FileSystem::generer_fin_nom_fichier__date_et_alea().'.'.$fichier_extension; // LSU recommande le modèle "import-lsun-{timestamp}.xml"
FileSystem::ecrire_fichier( CHEMIN_DOSSIER_EXPORT.$fichier_nom , $export_xml );
$fichier_lien = './force_download.php?fichier='.$fichier_nom;

// Retour
$sb = ($nb_bilans>1) ? 's' : '' ;
$se = ($nb_eleves>1) ? 's' : '' ;
Json::add_str('<p><label class="valide">Fichier d\'export généré : '.$nb_bilans.' bilan'.$sb.' concernant '.$nb_eleves.' élève'.$se.'.</label></p>'.NL);
Json::add_str('<p><a target="_blank" href="'.$fichier_lien.'"><span class="file file_'.$fichier_extension.'">Récupérer le fichier au format <em>'.$fichier_extension.'</em>.</span></a></p>'.NL);
Json::add_str('<p><label class="alerte">Pour des raisons de sécurité et de confidentialité, ce fichier sera effacé du serveur dans 1h.</label></p>'.NL);
Json::end( TRUE );

?>
