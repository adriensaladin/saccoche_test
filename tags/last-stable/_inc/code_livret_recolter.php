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

$tab_rubrique_type = array('eval','socle','epi','ap','parcours','bilan','viesco');
$tab_saisie_objet = array('position','appreciation','elements');
$tab_compte_rendu = array(
  'erreur' => array() ,
  'alerte' => array() ,
);
$tab_objet_used = array();

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Extraction des infos sur la période + détermination des informations principales
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if(substr($periode,0,7)=='periode')
{
  $PAGE_PERIODICITE = 'periode';
  $JOINTURE_PERIODE = substr($periode,7);
  $DB_ROW = DB_STRUCTURE_LIVRET::DB_recuperer_periode_info( $JOINTURE_PERIODE , $classe_id );
  if(empty($DB_ROW))
  {
    Json::end( FALSE , 'Jointure période/classe transmise indéfinie !' );
  }
  $periode_id       = $DB_ROW['periode_id'];
  $date_mysql_debut = $DB_ROW['jointure_date_debut'];
  $date_mysql_fin   = $DB_ROW['jointure_date_fin'];
  $date_debut = To::date_mysql_to_french($date_mysql_debut);
  $date_fin   = To::date_mysql_to_french($date_mysql_fin);
}
else
{
  $PAGE_PERIODICITE = $periode;
  $JOINTURE_PERIODE = '';
  $periode_id       = 0;
  $date_mysql_debut = NULL;
  $date_mysql_fin   = NULL;
  $date_debut = '';
  $date_fin   = '';
}

// On vérifie que le bilan est bien accessible en modification et on récupère les infos associées

$DB_ROW = DB_STRUCTURE_LIVRET::DB_recuperer_page_groupe_info( $classe_id , $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE );
if(empty($DB_ROW))
{
  Json::end( FALSE , 'Association classe / livret introuvable !' );
}
if($DB_ROW['jointure_etat']!='5complet')
{
  Json::end( FALSE , 'Bilan interdit d\'accès pour cette action !' );
}
if(is_null($DB_ROW['jointure_date_verrou']))
{
  Json::end( FALSE , 'Bilan non verrouillé !' );
}

$BILAN_ETAT          = $DB_ROW['jointure_etat'];
$PAGE_MOMENT         = $DB_ROW['livret_page_moment'];
$PAGE_TITRE_CLASSE   = $DB_ROW['livret_page_titre_classe'];
$PAGE_RESUME         = $DB_ROW['livret_page_resume'];
$PAGE_RUBRIQUE_TYPE  = $DB_ROW['livret_page_rubrique_type'];
$PAGE_RUBRIQUE_JOIN  = $DB_ROW['livret_page_rubrique_join'];
$PAGE_COLONNE        = $DB_ROW['livret_page_colonne'];
$PAGE_MOYENNE_CLASSE = $DB_ROW['livret_page_moyenne_classe'];
$PAGE_EPI            = $DB_ROW['livret_page_epi'];
$PAGE_AP             = $DB_ROW['livret_page_ap'];
$PAGE_PARCOURS       = $DB_ROW['livret_page_parcours'];
$PAGE_VIE_SCOLAIRE   = $DB_ROW['livret_page_vie_scolaire'];
$classe_nom          = $DB_ROW['groupe_nom'];
$classe_ref          = $DB_ROW['groupe_ref'];
$DATE_VERROU         = $DB_ROW['jointure_date_verrou'];
$BILAN_TYPE_ETABL    = in_array($PAGE_RUBRIQUE_TYPE,array('c3_matiere','c4_matiere','c3_socle','c4_socle')) ? 'college' : 'ecole' ;

$champ_classe = ($BILAN_TYPE_ETABL=='college') ? 'code-division' : 'classe-ref' ;
$classe_value = ($BILAN_TYPE_ETABL=='college') ? $classe_ref : 'CL'.$classe_id ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer et mettre en session les infos sur les seuils enregistrés
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( !in_array($PAGE_COLONNE,array('moyenne','pourcentage')) )
{
  $DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_page_seuils_infos( $PAGE_REF , $PAGE_COLONNE );
  foreach($DB_TAB as $DB_ROW)
  {
    $id = $DB_ROW['livret_colonne_id'] % 10 ; // 1 2 3 4
    $_SESSION['LIVRET'][$id]['SEUIL_MIN'] = $DB_ROW['livret_seuil_min'];
    $_SESSION['LIVRET'][$id]['SEUIL_MAX'] = $DB_ROW['livret_seuil_max'];
    $_SESSION['LIVRET'][$id]['LEGENDE']   = $DB_ROW['livret_colonne_legende'];
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer la classe (1D)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_classe = array();
$key_classe = 'CL'.$classe_id;

$tab_classe[$key_classe] = array(
  'id-be'   => $classe_id, // En attendant l'identifiant de classe de BE1D...
  'libelle' => $classe_nom,
);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer la période
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_periode = array();
$key_periode = 'PER'.$periode_id;

$tab_periode[$key_periode] = array(
  'millesime'   => To::annee_scolaire('siecle'),
  'indice'      => substr($periode_nom,-3,1),
  'nb-periodes' => substr($periode_nom,-1),
);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer le chef d'établissement
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_responsable_etabl = array();
$key_responsable = '';
$affichage_chef_etabl = ( ($PAGE_PERIODICITE=='cycle') || ($BILAN_TYPE_ETABL=='college') ) ? TRUE : FALSE ;

if($affichage_chef_etabl)
{
  $DB_ROW = DB_STRUCTURE_LIVRET::DB_recuperer_chef_etabl_infos($_SESSION['ETABLISSEMENT']['CHEF_ID']);
  if(empty($DB_ROW))
  {
    Json::end( FALSE , "Absence de désignation du chef d'établissement ou directeur d'école !" );
  }
  $key_responsable = 'DIR'.$DB_ROW['user_id'];
  $tab_responsable_etabl[$key_responsable] = array(
    'libelle' => To::texte_identite($DB_ROW['user_nom'],FALSE,$DB_ROW['user_prenom'],TRUE,$DB_ROW['user_genre']) // max 100 caractères, mais pour SACoche ce ne peut pas dépasser 4+25+25
  );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Paramètres du bilan
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$champ_position = in_array($PAGE_COLONNE,array('moyenne','pourcentage')) ? 'moyenne-eleve' : 'positionnement' ;

$tab_bilan = array(
  'prof-princ-refs'      => '', // Complété ultérieurement pour le 2nd degré
  'periode-ref'          => $key_periode,
  'date-conseil-classe'  => $DATE_VERROU,
  'date-scolarite'       => $date_mysql_debut,
  'date-verrou'          => $DATE_VERROU,
  'responsable-etab-ref' => $key_responsable,
  'position'             => $champ_position,
);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Initialisation Vie scolaire
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$affichage_assiduite = ($PAGE_VIE_SCOLAIRE) ? TRUE : FALSE ;

$tab_viesco = (!$affichage_assiduite) ? array() : array(
  'appreciation'        => '',
  'nb-retards'          => 0,
  'nb-abs-justifiees'   => 0,
  'nb-abs-injustifiees' => 0,
);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer des infos dans les fichiers SIECLE archivés : ref classe ; epp/local ; modalite-election ; date-scolarite
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_lsu_prof_type    = array_fill_keys( array('epp','local'), TRUE );
$tab_lsu_mod_election = array_fill_keys( array('S','O','F','N','L','R','X'), TRUE );
unset($tab_lsu_mod_election['N']); // Dans SIECLE mais pas dans les spécifications...

if( in_array( $PAGE_REF , array('6e','5e','4e','3e') ) )
{
  // Fichier sts_emp_UAI
  $DB_TAB = DB_STRUCTURE_SIECLE::DB_recuperer_import_contenu('sts_emp_UAI');
  if(empty($DB_TAB))
  {
    Json::end( FALSE , 'Fichier sts_emp_UAI manquant ! À importer page précédente...' );
  }
  // Ref classe
  if(empty($DB_TAB['DONNEES']['STRUCTURE']['DIVISIONS']['DIVISION']))
  {
    Json::end( FALSE , 'Classes absentes du fichier sts_emp_UAI !' );
  }
  $tab_siecle_division = array();
  foreach($DB_TAB['DONNEES']['STRUCTURE']['DIVISIONS']['DIVISION'] as $tab)
  {
    $division_ref = $tab['@attributes']['CODE'];
    if(isset($tab['MEFS_APPARTENANCE']['MEF_APPARTENANCE']['@attributes']['CODE']))
    {
      $code_mef = $tab['MEFS_APPARTENANCE']['MEF_APPARTENANCE']['@attributes']['CODE'] ;
      $tab_siecle_division[$division_ref][$code_mef] = TRUE;
    }
    elseif(is_array($tab['MEFS_APPARTENANCE']['MEF_APPARTENANCE']))
    {
      foreach($tab['MEFS_APPARTENANCE']['MEF_APPARTENANCE'] as $tab_mef)
      {
        $code_mef = $tab_mef['@attributes']['CODE'] ;
        $tab_siecle_division[$division_ref][$code_mef] = TRUE;
      }
    }
  }
  if(!isset($tab_siecle_division[$classe_ref]))
  {
    Json::end( FALSE , 'La référence de la classe "'.$classe_ref.'" ne figure pas dans celles du fichier SIECLE !<br />SIECLE comporte '.implode(' ',array_keys($tab_siecle_division)) );
  }
  // Type epp | local
  if(empty($DB_TAB['DONNEES']['INDIVIDUS']['INDIVIDU']))
  {
    Json::end( FALSE , 'Enseignants absents du fichier sts_emp_UAI !' );
  }
  $tab_siecle_prof_type = array();
  foreach($DB_TAB['DONNEES']['INDIVIDUS']['INDIVIDU'] as $tab)
  {
    $tab_siecle_prof_type[ $tab['@attributes']['ID'] ] = $tab['@attributes']['TYPE'];
  }
  // Fichier Nomenclature
  $DB_TAB = DB_STRUCTURE_SIECLE::DB_recuperer_import_contenu('Nomenclature');
  if(empty($DB_TAB))
  {
    Json::end( FALSE , 'Fichier Nomenclature manquant ! À importer page précédente...' );
  }
  // Modalités d'élection
  if(empty($DB_TAB['DONNEES']['PROGRAMMES']['PROGRAMME']))
  {
    Json::end( FALSE , 'Enseignements absents du fichier Nomenclature !' );
  }
  $tab_siecle_modalite_election = array();
  foreach($DB_TAB['DONNEES']['PROGRAMMES']['PROGRAMME'] as $tab)
  {
    if( isset($tab_siecle_division[$classe_ref][$tab['CODE_MEF']]) && (float)$tab['HORAIRE'] )
    {
      $tab_siecle_modalite_election[ $tab['CODE_MATIERE'] ] = $tab['CODE_MODALITE_ELECT'];
    }
  }
  // Fichier Eleves
  $DB_TAB = DB_STRUCTURE_SIECLE::DB_recuperer_import_contenu('Eleves');
  if(empty($DB_TAB))
  {
    Json::end( FALSE , 'Fichier ElevesSansAdresses manquant ! À importer page précédente...' );
  }
  if(empty($DB_TAB['DONNEES']['ELEVES']['ELEVE']))
  {
    Json::end( FALSE , 'Élèves absents du fichier ElevesSansAdresses !' );
  }
  $tab_siecle_eleve_date = array();
  foreach($DB_TAB['DONNEES']['ELEVES']['ELEVE'] as $tab)
  {
    $eleve_id = $tab['@attributes']['ELEVE_ID'];
    if( isset($tab['DATE_ENTREE']) )
    {
      $date_mysql_entree = To::date_french_to_mysql($tab['DATE_ENTREE']);
      $tab_siecle_eleve_date[$eleve_id]['date_entree'] = ($date_mysql_entree<$date_mysql_debut) ? $date_mysql_debut : $date_mysql_entree ;
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer la liste des élèves
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_eleve = array();
$DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' /*profil_type*/ , 2 /*actuels_et_anciens*/ , 'classe' , $classe_id , 'alpha' /*eleves_ordre*/ , 'user_id,user_nom,user_prenom,user_sconet_id,user_reference' /*champs*/ , $periode_id );
if(empty($DB_TAB))
{
  Json::end( FALSE , 'Aucun élève évalué trouvé dans le regroupement '.$classe_nom.' !' );
}
foreach($DB_TAB as $DB_ROW)
{
  // hack en attendant un identifiant extrait de BE1D
  if( ($BILAN_TYPE_ETABL=='ecole') && !$DB_ROW['user_reference'] )
  {
    $DB_ROW['user_reference'] = $DB_ROW['user_id'];
  }
  if( ( ($BILAN_TYPE_ETABL=='college') && $DB_ROW['user_sconet_id'] ) || ( ($BILAN_TYPE_ETABL=='ecole') && $DB_ROW['user_reference'] ) )
  {
    $tab_eleve[$DB_ROW['user_id']] = array(
      'eleve'         => array(
        'id'          => 'ELV'.$DB_ROW['user_id'],
        'id-be'       => $DB_ROW['user_sconet_id'], // 2D
        'ine'         => $DB_ROW['user_reference'], // 1D
        $champ_classe => $classe_value, // 2D max 8 caractères : idem dans SACoche
        'nom'         => $DB_ROW['user_nom'], // max 100 caractères : 25 dans SACoche
        'prenom'      => $DB_ROW['user_prenom'], // max 100 caractères : 25 dans SACoche
      ),
      'commun'        => array(
        'responsable-etab' => $tab_responsable_etabl,
        'classe'           => $tab_classe, // 1D
        'periode'          => $tab_periode,
        'discipline'       => array(),
        'enseignant'       => array(),
        'element'          => array(),
        'epi'              => array(),
        'ap'               => array(),
        'parcours'         => array(),
        'viesco'           => array(),
      ),
      'bilan'         => $tab_bilan + array( 'eleve-ref' => 'ELV'.$DB_ROW['user_id'] ) ,
      'acquis'        => array(),
      'epi'           => array(),
      'ap'            => array(),
      'parcours'      => array(),
      'modaccomp'     => array(),
      'synthese'      => array(),
      'viesco'        => $tab_viesco,
      'socle'         => array(), // non utilisé, en prévision si besoin
      'responsables'  => array(),
    );
    if(isset($tab_siecle_eleve_date[$DB_ROW['user_sconet_id']]['date_entree']))
    {
      $tab_eleve[$DB_ROW['user_id']]['bilan']['date-scolarite'] = $tab_siecle_eleve_date[$DB_ROW['user_sconet_id']]['date_entree'];
    }
  }
  else if($BILAN_TYPE_ETABL=='college')
  {
    $tab_compte_rendu['erreur'][] = 'Absence d\'identifiant SIECLE pour l\'élève "'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).'" : données non exportables.';
  }
  else if($BILAN_TYPE_ETABL=='ecole')
  {
    $tab_compte_rendu['erreur'][] = 'Absence d\'identifiant national (INE) pour l\'élève "'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).'" : données non exportables.';
  }
}
if(empty($tab_eleve))
{
  Json::end( FALSE , 'Aucun élève trouvé avec un identifiant SCONET renreigné !' );
}
$liste_eleve_id = implode(',',array_keys($tab_eleve));

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Liste de tous les professeurs / personnels
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_prof = array();
$tab_lsu_civilite = array( 'I'=>'' , 'M'=>'M' , 'F'=>'MME' );
$tab_profils_types = array('professeur','directeur');
$listing_champs = 'user_id, user_sconet_id, user_genre, user_nom, user_prenom';
$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users( $tab_profils_types , 2 /*actuels_et_anciens*/ , $listing_champs , FALSE /*with_classe*/ );
foreach($DB_TAB as $DB_ROW)
{
  $key_prof = 'ENS'.$DB_ROW['user_id'];
  if($DB_ROW['user_sconet_id'])
  {
    $id_sts = $DB_ROW['user_sconet_id'];
    $type = ( isset($tab_siecle_prof_type[$id_sts]) && isset($tab_lsu_prof_type[$tab_siecle_prof_type[$id_sts]]) ) ? $tab_siecle_prof_type[$id_sts] : 'epp' ;
  }
  else
  {
    $id_sts = sprintf("%'96u",$DB_ROW['user_id']);
    $type = 'epp' ;
  }
  if( ($BILAN_TYPE_ETABL=='college') && !$DB_ROW['user_sconet_id'] )
  {
    $tab_compte_rendu['alerte'][$key_prof] = 'Absence d\'identifiant SIECLE (STS) pour "'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).'" : génèrera un message d\'alerte non bloquant.';
  }
  $tab_prof[$key_prof] = array(
    'type'     => $type,
    'id-sts'   => $id_sts,
    'civilite' => $tab_lsu_civilite[$DB_ROW['user_genre']],
    'nom'      => $DB_ROW['user_nom'], // max 100 caractères : 25 dans SACoche
    'prenom'   => $DB_ROW['user_prenom'], // max 100 caractères : 25 dans SACoche
  );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer les professeurs principaux
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$affichage_prof_principal = ($BILAN_TYPE_ETABL=='college') ? TRUE : FALSE ;
$prof_principal_refs = '';

if( $affichage_prof_principal )
{
  $tab_pp = array();
  $DB_TAB = DB_STRUCTURE_OFFICIEL::DB_lister_profs_principaux($classe_id);
  if(empty($DB_TAB))
  {
    Json::end( FALSE , 'Absence de désignation du professeur principal pour la classe '.$classe_nom.' !' );
  }
  else
  {
    foreach($DB_TAB as $DB_ROW)
    {
      $key_prof = 'ENS'.$DB_ROW['user_id'];
      $tab_pp[$DB_ROW['user_id']] = $key_prof;
      foreach($tab_eleve as $eleve_id => $tab)
      {
        $tab_eleve[$eleve_id]['commun']['enseignant'][$key_prof] = $tab_prof[$key_prof];
      }
    }
  }
  $prof_principal_refs = implode(' ', $tab_pp);
  foreach($tab_eleve as $eleve_id => $tab)
  {
    $tab_eleve[$eleve_id]['bilan']['prof-princ-refs'] = $prof_principal_refs;
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Liste de toutes les rubriques (acquis scolaires)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_rubrique = array();
$DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_rubriques( $PAGE_RUBRIQUE_TYPE , TRUE /*for_edition*/ );
foreach($DB_TAB as $DB_ROW)
{
  $key_rubrique = 'MAT'.$DB_ROW['livret_rubrique_id'];
  if($BILAN_TYPE_ETABL=='college')
  {
    if($DB_ROW['matiere_siecle'])
    {
      $code = sprintf("%06u",$DB_ROW['rubrique_id_livret']);
      $modalite_election = ( isset($tab_siecle_modalite_election[$code]) && isset($tab_lsu_mod_election[$tab_siecle_modalite_election[$code]]) ) ? $tab_siecle_modalite_election[$code] : 'S' ;
    }
    else
    {
      $code = sprintf("%'96u",$DB_ROW['livret_rubrique_id']);
      $modalite_election = 'X' ;
    }
    if(!$DB_ROW['matiere_siecle'])
    {
      $tab_compte_rendu['alerte'][$key_rubrique] = 'Discipline utilisée "'.html($DB_ROW['rubrique']).'" hors SIECLE : génèrera un message d\'alerte non bloquant.';
    }
  }
  else
  {
    $code = str_replace('___','RAC',$DB_ROW['rubrique_id_livret']);
    $modalite_election = 'S' ;
  }
  $tab_rubrique[$key_rubrique] = array(
    'libelle'           => mb_substr($DB_ROW['rubrique'],0,40), // max 40 caractères : 63 dans SACoche
    // 'sous_partie'       => $DB_ROW['sous_rubrique'],
    'code'              => $code,
    'modalite-election' => $modalite_election,
  );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer les saisies déjà effectuées ou enregistrées pour la page de livret concernée
// Pas besoin de 'saisie_id' ni 'prof_id' ni 'saisie_origine' ni 'acquis_detail'
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_saisie = array();  // [eleve_id][rubrique_id][prof_id] => array(prof_info,appreciation,note);

$DB_TAB = DB_STRUCTURE_LIVRET::DB_recuperer_donnees_eleves( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , '' /*liste_rubrique_type*/ , $liste_eleve_id , 0 /*prof_id*/ , FALSE /*with_periodes_avant*/ );
foreach($DB_TAB as $DB_ROW)
{
  $tab_saisie[$DB_ROW['eleve_id']][$DB_ROW['rubrique_type']][$DB_ROW['rubrique_id']][$DB_ROW['saisie_objet']] = array( 'saisie_valeur'=>$DB_ROW['saisie_valeur'] , 'listing_profs'=>$DB_ROW['listing_profs'] );
}
$DB_TAB = DB_STRUCTURE_LIVRET::DB_recuperer_donnees_classe( $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , '' /*liste_rubrique_type*/ , $classe_id , 0 /*prof_id*/ , FALSE /*with_periodes_avant*/ , FALSE /*only_synthese_generale*/ );
foreach($DB_TAB as $DB_ROW)
{
  $tab_saisie[0][$DB_ROW['rubrique_type']][$DB_ROW['rubrique_id']][$DB_ROW['saisie_objet']] = array( 'saisie_valeur'=>$DB_ROW['saisie_valeur'] , 'listing_profs'=>$DB_ROW['listing_profs'] );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// EPI
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_epi = array();
if($PAGE_EPI)
{
  $DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_epi( $PAGE_REF , $classe_id );
  foreach($DB_TAB as $DB_ROW)
  {
    $key_rubrique = 'EPI'.$DB_ROW['livret_epi_id'].$key_periode;
    $description = isset($tab_saisie[0]['epi'][$DB_ROW['livret_epi_id']]['appreciation']) ? $tab_saisie[0]['epi'][$DB_ROW['livret_epi_id']]['appreciation']['saisie_valeur'] : NULL ;
    $tab_epi[$key_rubrique] = array(
      'id'              => 'EPI'.$DB_ROW['livret_epi_id'] ,
      'intitule'        => $DB_ROW['livret_epi_titre'] , // max 150 caractères : 128 dans SACoche
      'thematique'      => $DB_ROW['livret_epi_theme_code'] ,
      'description'     => $description , // max 600 caractères : idem dans SACoche
      'discipline-refs' => array() ,
      'enseignant-refs' => array() ,
    );
    $tab_id = explode(' ',$DB_ROW['matiere_prof_id']);
    $tab_mat = array();
    foreach($tab_id as $key => $ids)
    {
      list($matiere_id,$user_id) = explode('_',$ids);
      if(!isset($tab_prof['ENS'.$user_id]))
      {
        $tab_compte_rendu['alerte'][] = 'EPI "'.html($DB_ROW['livret_epi_titre']).'" associé à un enseignant (n°'.$user_id.') inconnu : non exporté.';
        unset($tab_id[$key]);
      }
      elseif(!isset($tab_rubrique['MAT'.$matiere_id]))
      {
        $tab_compte_rendu['alerte'][] = 'EPI "'.html($DB_ROW['livret_epi_titre']).'" associé à une matière (n°'.$matiere_id.') inconnue : non exporté.';
        unset($tab_id[$key]);
      }
      else
      {
        $modelec = $tab_rubrique['MAT'.$matiere_id]['modalite-election'];
        $tab_epi[$key_rubrique]['discipline-refs'][] = 'MAT'.$matiere_id.$modelec;
        $tab_epi[$key_rubrique]['enseignant-refs'][] = 'ENS'.$user_id;
        $tab_mat[$matiere_id] = TRUE;
      }
    }
    if(count($tab_mat)<2)
    {
      $tab_compte_rendu['alerte'][] = 'EPI "'.html($DB_ROW['livret_epi_titre']).'" avec moins de 2 matières associées : non exporté.';
    }
    else if($description)
    {
      foreach($tab_eleve as $eleve_id => $tab)
      {
        foreach($tab_id as $ids)
        {
          list($matiere_id,$user_id) = explode('_',$ids);
          $modelec = $tab_rubrique['MAT'.$matiere_id]['modalite-election'];
          $tab_eleve[$eleve_id]['commun']['epi'][$key_rubrique] = $tab_epi[$key_rubrique];
          $tab_eleve[$eleve_id]['commun']['enseignant']['ENS'.$user_id] = $tab_prof['ENS'.$user_id];
          $tab_eleve[$eleve_id]['commun']['discipline']['MAT'.$matiere_id.$modelec] = $tab_rubrique['MAT'.$matiere_id];
          $tab_objet_used['ENS'.$user_id] = TRUE;
          $tab_objet_used['MAT'.$matiere_id] = TRUE;
        }
      }
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// AP
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_ap = array();
if($PAGE_AP)
{
  $DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_ap( $PAGE_REF , $classe_id );
  foreach($DB_TAB as $DB_ROW)
  {
    $key_rubrique = 'AP'.$DB_ROW['livret_ap_id'].$key_periode;
    $description = isset($tab_saisie[0]['ap'][$DB_ROW['livret_ap_id']]['appreciation']) ? $tab_saisie[0]['ap'][$DB_ROW['livret_ap_id']]['appreciation']['saisie_valeur'] : NULL ;
    $tab_ap[$key_rubrique] = array(
      'id'              => 'AP'.$DB_ROW['livret_ap_id'] ,
      'intitule'        => $DB_ROW['livret_ap_titre'] , // max 150 caractères : 128 dans SACoche
      'description'     => $description , // max 600 caractères : idem dans SACoche
      'discipline-refs' => array() ,
      'enseignant-refs' => array() ,
    );
    $tab_id  = explode(' '   ,$DB_ROW['matiere_prof_id']);
    foreach($tab_id as $key => $ids)
    {
      list($matiere_id,$user_id) = explode('_',$ids);
      if(!isset($tab_prof['ENS'.$user_id]))
      {
        $tab_compte_rendu['alerte'][] = 'AP "'.html($DB_ROW['livret_ap_titre']).'" associé à un enseignant (n°'.$user_id.') inconnu : non exporté.';
        unset($tab_id[$key]);
      }
      elseif(!isset($tab_rubrique['MAT'.$matiere_id]))
      {
        $tab_compte_rendu['alerte'][] = 'AP "'.html($DB_ROW['livret_ap_titre']).'" associé à une matière (n°'.$matiere_id.') inconnue : non exporté.';
        unset($tab_id[$key]);
      }
      else
      {
        $modelec = $tab_rubrique['MAT'.$matiere_id]['modalite-election'];
        $tab_ap[$key_rubrique]['discipline-refs'][] = 'MAT'.$matiere_id.$modelec;
        $tab_ap[$key_rubrique]['enseignant-refs'][] = 'ENS'.$user_id;
      }
    }
    if(count($tab_id)<1)
    {
      $tab_compte_rendu['alerte'][] = 'AP "'.html($DB_ROW['livret_ap_titre']).'" sans couple {enseignant;matière} associé : non exporté.';
    }
    else if($description)
    {
      foreach($tab_eleve as $eleve_id => $tab)
      {
        foreach($tab_id as $ids)
        {
          list($matiere_id,$user_id) = explode('_',$ids);
          $modelec = $tab_rubrique['MAT'.$matiere_id]['modalite-election'];
          $tab_eleve[$eleve_id]['commun']['ap'][$key_rubrique] = $tab_ap[$key_rubrique];
          $tab_eleve[$eleve_id]['commun']['enseignant']['ENS'.$user_id] = $tab_prof['ENS'.$user_id];
          $tab_eleve[$eleve_id]['commun']['discipline']['MAT'.$matiere_id.$modelec] = $tab_rubrique['MAT'.$matiere_id];
          $tab_objet_used['ENS'.$user_id] = TRUE;
          $tab_objet_used['MAT'.$matiere_id] = TRUE;
        }
      }
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Parcours
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_parcours = array();
if($PAGE_PARCOURS)
{
  $tab_parcours_code = explode(',',$PAGE_PARCOURS);
  foreach($tab_parcours_code as $parcours_code)
  {
    $DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_parcours( $parcours_code , $PAGE_REF , $classe_id );
    if(!empty($DB_TAB))
    {
      $DB_ROW = $DB_TAB[0]; // 1 parcours de chaque type au maximum par classe
      $key_rubrique = 'PAR'.$DB_ROW['livret_parcours_id'].$key_periode;
      $projet = isset($tab_saisie[0]['parcours'][$DB_ROW['livret_parcours_id']]['appreciation']) ? $tab_saisie[0]['parcours'][$DB_ROW['livret_parcours_id']]['appreciation']['saisie_valeur'] : NULL ;
      $tab_parcours[$key_rubrique] = array(
        'periode-ref' => $key_periode ,
        $champ_classe => $classe_value ,
        'code'        => $parcours_code ,
        'projet'      => $projet ,
      );
      if($projet)
      {
        foreach($tab_eleve as $eleve_id => $tab)
        {
          $tab_eleve[$eleve_id]['commun']['parcours'][$key_rubrique] = $tab_parcours[$key_rubrique];
        }
      }
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Modalités d'accompagnement
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_eleve_modaccomp( $liste_eleve_id );
foreach($DB_TAB as $DB_ROW)
{
  $value = ($DB_ROW['info_complement']) ? $DB_ROW['info_complement'] : NULL ; // max 600 caractères : 255 dans SACoche
  $tab_eleve[$DB_ROW['user_id']]['modaccomp'][$DB_ROW['livret_modaccomp_code']] = $value;
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traitement des saisies
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$nb_caract_max_par_colonne = 50;
foreach($tab_saisie as $eleve_id => $tab_tmp_eleve)
{
  if( $eleve_id )
  {
    foreach($tab_tmp_eleve as $rubrique_type => $tab_tmp_rubrique)
    {
      foreach($tab_tmp_rubrique as $rubrique_id => $tab_tmp_saisie)
      {
        // AQUIS SCOLAIRES
        if( ($rubrique_type=='eval') && isset($tab_rubrique['MAT'.$rubrique_id]) )
        {
          $key_rubrique = 'MAT'.$rubrique_id;
          $modelec = $tab_rubrique['MAT'.$rubrique_id]['modalite-election'];
          $tab_eleve[$eleve_id]['acquis'][$key_rubrique.$modelec] = array(
            'profs'             => array(),
            'elements'          => array(),
            $champ_position     => NULL, // Si indéfini, utiliser ultérieurement "eleve-non-note"
            'moyenne-structure' => NULL, // Si indéfini, utiliser ultérieurement "structure-non-notee"
            'appreciation'      => NULL,
          );
          $tab_eleve[$eleve_id]['commun']['discipline'][$key_rubrique.$modelec] = $tab_rubrique[$key_rubrique];
          $tab_objet_used[$key_rubrique] = TRUE;
          $tab_tmp_profs = array( 'appreciation' => array() , 'position' => array() );
          foreach($tab_tmp_saisie as $saisie_objet => $saisie_info)
          {
            if($saisie_objet=='elements')
            {
              $nb_lignes_elements = 0;
              $tab_valeurs = json_decode($saisie_info['saisie_valeur'], TRUE);
              foreach($tab_valeurs as $texte => $nb_used)
              {
                if( ($nb_lignes_elements>=4) && ($nb_used==1) )
                {
                  break;
                }
                $key_element = 'EL'.md5($texte);
                $tab_eleve[$eleve_id]['acquis'][$key_rubrique.$modelec]['elements'][] = $key_element;
                $tab_eleve[$eleve_id]['commun']['element'][$key_element] = $texte; // max 300 caractères : 255 dans SACoche
                $nb_lignes_elements += min( 3 , ceil(strlen($texte)/$nb_caract_max_par_colonne) );
                if($nb_lignes_elements>=6)
                {
                  break;
                }
              }
            }
            if($saisie_objet=='appreciation')
            {
              $tab_eleve[$eleve_id]['acquis'][$key_rubrique.$modelec]['appreciation'] = $saisie_info['saisie_valeur'];
              $tab_tmp_profs['appreciation'] = is_null($saisie_info['listing_profs']) ? array() : explode(',',$saisie_info['listing_profs']) ;
            }
            if($saisie_objet=='position')
            {
              if(!is_null($saisie_info['saisie_valeur']))
              {
                $pourcentage = $saisie_info['saisie_valeur'];
                $note = in_array($PAGE_COLONNE,array('objectif','position')) ? OutilBilan::determiner_degre_maitrise($pourcentage) : ( ($PAGE_COLONNE=='moyenne') ? round(($pourcentage/5),1).'/20' : $pourcentage.'%' ) ;
                $tab_eleve[$eleve_id]['acquis'][$key_rubrique.$modelec][$champ_position] = $note; // max 8 caractères : 7 depuis SACoche
              }
              if( in_array($PAGE_COLONNE,array('moyenne','pourcentage')) && $PAGE_MOYENNE_CLASSE && isset($tab_saisie[0]['eval'][$rubrique_id]['position']) && !is_null($tab_saisie[0]['eval'][$rubrique_id]['position']['saisie_valeur']) )
              {
                $pourcentage = $tab_saisie[0]['eval'][$rubrique_id]['position']['saisie_valeur'];
                $note = in_array($PAGE_COLONNE,array('objectif','position')) ? OutilBilan::determiner_degre_maitrise($pourcentage) : ( ($PAGE_COLONNE=='moyenne') ? round(($pourcentage/5),1).'/20' : $pourcentage.'%' ) ;
                $tab_eleve[$eleve_id]['acquis'][$key_rubrique.$modelec]['moyenne-structure'] = $note; // max 8 caractères : 7 depuis SACoche
              }
              $tab_tmp_profs['position'] = is_null($saisie_info['listing_profs']) ? array() : explode(',',$saisie_info['listing_profs']) ;
            }
          }
          $tab_tmp_profs = !empty($tab_tmp_profs['appreciation']) ? $tab_tmp_profs['appreciation'] : $tab_tmp_profs['position'] ;
          if(!empty($tab_tmp_profs))
          {
            foreach($tab_tmp_profs as $prof_id)
            {
              $key_prof = 'ENS'.$prof_id;
              $tab_eleve[$eleve_id]['acquis'][$key_rubrique.$modelec]['profs'][$prof_id] = $key_prof;
              $tab_eleve[$eleve_id]['commun']['enseignant'][$key_prof] = $tab_prof[$key_prof];
              $tab_objet_used[$key_prof] = TRUE;
            }
          }
        }
        // EPI
        if( ($rubrique_type=='epi') && isset($tab_epi['EPI'.$rubrique_id.$key_periode]) )
        {
          $key_rubrique = 'EPI'.$rubrique_id.$key_periode ;
          $tab_eleve[$eleve_id]['commun']['epi'][$key_rubrique] = $tab_epi[$key_rubrique];
          $tab_eleve[$eleve_id]['epi'][$key_rubrique] = array(
            'appreciation' => $tab_tmp_saisie['appreciation']['saisie_valeur'], // max 600 caractères : idem dans SACoche
          );
        }
        // AP
        if( ($rubrique_type=='ap') && isset($tab_ap['AP'.$rubrique_id.$key_periode]) )
        {
          $key_rubrique = 'AP'.$rubrique_id.$key_periode ;
          $tab_eleve[$eleve_id]['commun']['ap'][$key_rubrique] = $tab_ap[$key_rubrique];
          $tab_eleve[$eleve_id]['ap'][$key_rubrique] = array(
            'appreciation' => $tab_tmp_saisie['appreciation']['saisie_valeur'], // max 600 caractères : idem dans SACoche
          );
        }
        // Parcours
        if( ($rubrique_type=='parcours') && isset($tab_parcours['PAR'.$rubrique_id.$key_periode]) )
        {
          $key_rubrique = 'PAR'.$rubrique_id.$key_periode ;
          $tab_eleve[$eleve_id]['parcours'][$key_rubrique] = array(
            'code'         => $tab_parcours[$key_rubrique]['code'],
            'appreciation' => $tab_tmp_saisie['appreciation']['saisie_valeur'], // max 600 caractères : idem dans SACoche
          );
        }
        // Bilan
        if($rubrique_type=='bilan')
        {
          $tab_eleve[$eleve_id]['synthese']['appreciation'] = $tab_tmp_saisie['appreciation']['saisie_valeur']; // max 1000 caractères : idem dans SACoche
        }
        // Communication avec la famille
        if($rubrique_type=='viesco')
        {
          $tab_eleve[$eleve_id]['viesco']['appreciation'] = $tab_tmp_saisie['appreciation']['saisie_valeur']; // max 600 caractères : idem dans SACoche
        }
      }
    }
  }
}

unset($tab_saisie);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Vérifier qu'il y a au moins une info d'acquisition par élève + un positionnement défini si pas de note ni de pourcentage + au moins un prof rattaché à chaque matière + une appréciation de synthèse
// ////////////////////////////////////////////////////////////////////////////////////////////////////

foreach($tab_eleve as $eleve_id => $tab)
{
  if(empty($tab['acquis']))
  {
    $tab_compte_rendu['erreur'][] = html($tab['eleve']['nom'].' '.$tab['eleve']['prenom']).' &rarr; Aucune saisie d\'acquis (ni positionnement ni appréciation ni élément travaillé) : données non exportables.';
    unset($tab_eleve[$eleve_id]);
  }
  else
  {
    if($BILAN_TYPE_ETABL=='college')
    {
      foreach($tab['acquis'] as $discipline_ref => $tab_rubrique_info)
      {
        if(empty($tab_rubrique_info['profs']))
        {
          $tab_compte_rendu['alerte'][] = html($tab['eleve']['nom'].' '.$tab['eleve']['prenom']).' &rarr; Aucun enseignant rattaché à la discipline "'.$tab_rubrique[substr($discipline_ref,0,-1)]['libelle'].'" : rubrique non exportée.';
          unset($tab_eleve[$eleve_id]['acquis'][$discipline_ref]);
        }
        else if( ($champ_position=='positionnement') && is_null($tab_rubrique_info[$champ_position]) )
        {
          $tab_compte_rendu['alerte'][] = html($tab['eleve']['nom'].' '.$tab['eleve']['prenom']).' &rarr; Aucun positionnement pour la discipline "'.$tab_rubrique[substr($discipline_ref,0,-1)]['libelle'].'" : rubrique non exportée.';
          unset($tab_eleve[$eleve_id]['acquis'][$discipline_ref]);
        }
      }
    }
    if(empty($tab_eleve[$eleve_id]['acquis']))
    {
      $tab_compte_rendu['erreur'][] = html($tab['eleve']['nom'].' '.$tab['eleve']['prenom']).' &rarr; Plus de rubriques d\'acquis : données non exportables.';
      unset($tab_eleve[$eleve_id]);
    }
    else if(empty($tab_eleve[$eleve_id]['synthese']))
    {
      $tab_compte_rendu['alerte'][] = html($tab['eleve']['nom'].' '.$tab['eleve']['prenom']).' &rarr; Absence d\'appréciation bilan : champ normalement requis passé à "-".';
      $tab_eleve[$eleve_id]['synthese']['appreciation'] = '-';
    }
  }
}

$liste_eleve_id = implode(',',array_keys($tab_eleve)); // si les données n'étaient pas exportables, alors la liste a été restreinte

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer les absences / retards
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $affichage_assiduite && !empty($tab_eleve) )
{
  $DB_TAB = DB_STRUCTURE_OFFICIEL::DB_lister_officiel_assiduite( $periode_id , $liste_eleve_id );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_eleve[$DB_ROW['user_id']]['viesco']['nb-retards']          = (int)$DB_ROW['assiduite_retard'];
    $tab_eleve[$DB_ROW['user_id']]['viesco']['nb-abs-justifiees']   = (int)$DB_ROW['assiduite_absence'] - (int)$DB_ROW['assiduite_absence_nj'];
    $tab_eleve[$DB_ROW['user_id']]['viesco']['nb-abs-injustifiees'] = (int)$DB_ROW['assiduite_absence_nj'];
  }
  $nb_eleves = count($tab_eleve);
  $nb_viesco = count($DB_TAB);
  $nb_manque = $nb_eleves - $nb_viesco;
  if( $nb_manque )
  {
    $s = ($nb_manque>1) ? 's' : '' ;
    $tab_compte_rendu['alerte'][] = "Informations de vie scolaire (assiduité / ponctualité)  absentes pour ".$nb_manque." élève".$s." : exportées à zéro.";
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer les noms et coordonnées des responsables légaux
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( !empty($tab_eleve) )
{
  $DB_TAB = DB_STRUCTURE_OFFICIEL::DB_lister_adresses_parents_for_enfants($liste_eleve_id);
  if(!empty($DB_TAB))
  {
    $pays_majoritaire = DB_STRUCTURE_OFFICIEL::DB_recuperer_pays_majoritaire();
    foreach($DB_TAB as $eleve_id => $DB_ROW)
    {
      foreach($DB_ROW as $key => $DB_RESP)
      {
        if( ( ($DB_RESP['resp_legal_num']==1) || ($DB_RESP['resp_legal_num']==2) ) && $tab_lsu_civilite[$DB_RESP['user_genre']] && $DB_RESP['user_nom'] && $DB_RESP['user_prenom'] && $DB_RESP['adresse_ligne1'] && $DB_RESP['adresse_postal_code'] && $DB_RESP['adresse_postal_libelle'] )
        {
          $pays_nom = ($DB_RESP['adresse_pays_nom']==$pays_majoritaire) ? '' : ' - '.$DB_RESP['adresse_pays_nom'] ;
          $tab_eleve[$eleve_id]['responsables'][$DB_RESP['resp_legal_num']] = array(
            'civilite'    => $tab_lsu_civilite[$DB_RESP['user_genre']],
            'nom'         => $DB_RESP['user_nom'], // max 100 caractères : 25 dans SACoche
            'prenom'      => $DB_RESP['user_prenom'], // max 100 caractères : 25 dans SACoche
            'ligne1'      => $DB_RESP['adresse_ligne1'], // max 50 caractères : idem dans SACoche
            'ligne2'      => $DB_RESP['adresse_ligne2'], // max 50 caractères : idem dans SACoche
            'ligne3'      => $DB_RESP['adresse_ligne3'], // max 50 caractères : idem dans SACoche
            'ligne4'      => $DB_RESP['adresse_ligne4'], // max 50 caractères : idem dans SACoche
            'code-postal' => $DB_RESP['adresse_postal_code'], // max 10 caractères : idem dans SACoche
            'commune'     => $DB_RESP['adresse_postal_libelle'].$pays_nom, // max 100 caractères : 45+3+35 dans SACoche
          );
        }
      }
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Enregistrer en base de données
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( !empty($tab_eleve) )
{
  foreach($tab_eleve as $eleve_id => $tab)
  {
    DB_STRUCTURE_LIVRET::DB_ajouter_livret_export_eleve( $eleve_id , $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE , VERSION_PROG , json_encode($tab) );
  }
  DB_STRUCTURE_LIVRET::DB_modifier_jointure_date_export( $classe_id , $PAGE_REF , $PAGE_PERIODICITE , $JOINTURE_PERIODE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Retour
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// On retire les message alertant de prof sans id Siècle ou de matière hors Siècle s'ils ne sont finalement pas utilisés
foreach($tab_compte_rendu as $type_alerte => $tab_message)
{
  foreach($tab_message as $key => $message)
  {
    if( !is_int($key) && !isset($tab_objet_used[$key]))
    {
      unset($tab_compte_rendu[$type_alerte][$key]);
    }
  }
}
$retour = '';
foreach($tab_compte_rendu as $type_alerte => $tab_message)
{
  if(!empty($tab_message))
  {
    $retour .= '<label class="'.$type_alerte.'">'.implode('</label><br /><label class="'.$type_alerte.'">',$tab_message).'</label><br />';
  }
}

Json::end( TRUE , $retour );

?>
