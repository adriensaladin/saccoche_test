<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010-2014
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

class Form
{

  private static $dossier_cookie = '';
  private static $fichier_cookie = '';

  // //////////////////////////////////////////////////
  // Tableaux prédéfinis
  // //////////////////////////////////////////////////

  public static $tab_select_tri_objet = array(
    array('valeur' => 'eleve' , 'texte' => 'élèves en lignes (triés pour un item donné)') ,
    array('valeur' => 'item'  , 'texte' => 'items en lignes (triés pour un élève donné)') ,
  );

  public static $tab_select_tri_mode = array(
    array('valeur' => 'score' , 'texte' => 'tri par score d\'acquisition (pour un tri unique)') ,
    array('valeur' => 'etat'  , 'texte' => 'tri par état d\'acquisition (pour un tri multiple)') ,
  );

  public static $tab_select_orientation = array(
    array('valeur' => 'portrait'  , 'texte' => 'Portrait (vertical)') ,
    array('valeur' => 'landscape' , 'texte' => 'Paysage (horizontal)') ,
  );

  public static $tab_select_marge_min = array(
    array('valeur' =>  5 , 'texte' => 'marges de 5 mm')  ,
    array('valeur' =>  6 , 'texte' => 'marges de 6 mm')  ,
    array('valeur' =>  7 , 'texte' => 'marges de 7 mm')  ,
    array('valeur' =>  8 , 'texte' => 'marges de 8 mm')  ,
    array('valeur' =>  9 , 'texte' => 'marges de 9 mm')  ,
    array('valeur' => 10 , 'texte' => 'marges de 10 mm') ,
    array('valeur' => 11 , 'texte' => 'marges de 11 mm') ,
    array('valeur' => 12 , 'texte' => 'marges de 12 mm') ,
    array('valeur' => 13 , 'texte' => 'marges de 13 mm') ,
    array('valeur' => 14 , 'texte' => 'marges de 14 mm') ,
    array('valeur' => 15 , 'texte' => 'marges de 15 mm') ,
  );

  public static $tab_select_pages_nb = array(
    array('valeur' => 'optimise' , 'texte' => 'nombre de pages optimisé') ,
    array('valeur' => 'augmente' , 'texte' => 'nombre de pages augmenté') ,
  );

  public static $tab_select_couleur = array(
    array('valeur' => 'oui' , 'texte' => 'en couleurs') ,
    array('valeur' => 'non' , 'texte' => 'en niveaux de gris') ,
  );

  public static $tab_select_legende = array(
    array('valeur' => 'oui' , 'texte' => 'avec légende') ,
    array('valeur' => 'non' , 'texte' => 'sans légende') ,
  );

  public static $tab_select_cases_nb = array(
    array('valeur' =>  0 , 'texte' =>  '0 case')  ,
    array('valeur' =>  1 , 'texte' =>  '1 case')  ,
    array('valeur' =>  2 , 'texte' =>  '2 cases') ,
    array('valeur' =>  3 , 'texte' =>  '3 cases') ,
    array('valeur' =>  4 , 'texte' =>  '4 cases') ,
    array('valeur' =>  5 , 'texte' =>  '5 cases') ,
    array('valeur' =>  6 , 'texte' =>  '6 cases') ,
    array('valeur' =>  7 , 'texte' =>  '7 cases') ,
    array('valeur' =>  8 , 'texte' =>  '8 cases') ,
    array('valeur' =>  9 , 'texte' =>  '9 cases') ,
    array('valeur' => 10 , 'texte' => '10 cases') ,
    array('valeur' => 11 , 'texte' => '11 cases') ,
    array('valeur' => 12 , 'texte' => '12 cases') ,
    array('valeur' => 13 , 'texte' => '13 cases') ,
    array('valeur' => 14 , 'texte' => '14 cases') ,
    array('valeur' => 15 , 'texte' => '15 cases') ,
  );

  public static $tab_select_cases_size = array(
    array('valeur' =>  5 , 'texte' =>  '5 mm') ,
    array('valeur' =>  6 , 'texte' =>  '6 mm') ,
    array('valeur' =>  7 , 'texte' =>  '7 mm') ,
    array('valeur' =>  8 , 'texte' =>  '8 mm') ,
    array('valeur' =>  9 , 'texte' =>  '9 mm') ,
    array('valeur' => 10 , 'texte' => '10 mm') ,
    array('valeur' => 11 , 'texte' => '11 mm') ,
    array('valeur' => 12 , 'texte' => '12 mm') ,
    array('valeur' => 13 , 'texte' => '13 mm') ,
    array('valeur' => 14 , 'texte' => '14 mm') ,
    array('valeur' => 15 , 'texte' => '15 mm') ,
  );

  public static $tab_select_remplissage = array(
    array('valeur' => 'vide'  , 'texte' => 'sans les dernières notes') ,
    array('valeur' => 'plein' , 'texte' => 'avec les dernières notes') ,
  );

  public static $tab_select_colonne_bilan = array(
    array('valeur' => 'non' , 'texte' => 'sans colonne bilan') ,
    array('valeur' => 'oui' , 'texte' => 'avec colonne bilan') ,
  );

  public static $tab_select_colonne_vide = array(
    array('valeur' =>  0  , 'texte' => 'sans colonne supplémentaire')  ,
    array('valeur' => 30  , 'texte' => 'avec une colonne vide de 1cm') ,
    array('valeur' => 30  , 'texte' => 'avec une colonne vide de 3cm') ,
    array('valeur' => 50  , 'texte' => 'avec une colonne vide de 5cm') ,
    array('valeur' => 70  , 'texte' => 'avec une colonne vide de 7cm') ,
    array('valeur' => 90  , 'texte' => 'avec une colonne vide de 9cm') ,
  );

  public static $tab_select_cart_contenu = array(
    array('valeur' => 'SANS_nom_SANS_result' , 'texte' => 'cartouche SANS les noms d\'élèves et SANS les résultats') ,
    array('valeur' => 'AVEC_nom_SANS_result' , 'texte' => 'cartouche AVEC les noms d\'élèves mais SANS les résultats') ,
    array('valeur' => 'AVEC_nom_AVEC_result' , 'texte' => 'cartouche AVEC les noms d\'élèves et AVEC les résultats (si saisis)') ,
  );

  public static $tab_select_cart_detail = array(
    array('valeur' => 'complet' , 'texte' => 'cartouche avec la dénomination complète de chaque item') ,
    array('valeur' => 'minimal' , 'texte' => 'cartouche minimal avec uniquement les références des items') ,
  );

  public static $tab_select_recherche_objet = array(
    array('valeur' => 'matiere_items_bilanMS'   , 'optgroup'=>1 , 'texte' => 'moyenne des scores d\'acquisition') ,
    array('valeur' => 'matiere_items_bilanPA'   , 'optgroup'=>1 , 'texte' => 'pourcentage d\'items acquis') ,
    array('valeur' => 'socle_item_pourcentage'  , 'optgroup'=>2 , 'texte' => 'pourcentage d\'items disciplinaires acquis') ,
    array('valeur' => 'socle_item_validation'   , 'optgroup'=>2 , 'texte' => 'état de validation') ,
    array('valeur' => 'socle_pilier_validation' , 'optgroup'=>3 , 'texte' => 'état de validation') ,
  );

  public static $tab_select_statut = array(
    array('valeur' => 1 , 'texte' => 'comptes actuels (date de sortie sans objet ou ultérieure)') ,
    array('valeur' => 0 , 'texte' => 'comptes anciens (date de sortie présente et antérieure)') ,
  );

  public static $tab_select_appreciation = array(
    array('valeur' =>   0 , 'texte' => 'Non → pas de saisie d\'appréciation') ,
    array('valeur' => 100 , 'texte' => 'Oui → 100 caractères maximum (super court)') ,
    array('valeur' => 200 , 'texte' => 'Oui → 200 caractères maximum (très court)') ,
    array('valeur' => 300 , 'texte' => 'Oui → 300 caractères maximum (court)') ,
    array('valeur' => 400 , 'texte' => 'Oui → 400 caractères maximum (moyen)') ,
    array('valeur' => 500 , 'texte' => 'Oui → 500 caractères maximum (long)') ,
    array('valeur' => 600 , 'texte' => 'Oui → 600 caractères maximum (très long)') ,
    array('valeur' => 700 , 'texte' => 'Oui → 700 caractères maximum (super long)') ,
    array('valeur' => 800 , 'texte' => 'Oui → 800 caractères maximum (trop long…)') ,
  );

  public static $tab_select_optgroup = array(
    'regroupements' => array(
      'divers' => 'Divers',
      'niveau' => 'Niveaux',
      'classe' => 'Classes',
      'groupe' => 'Groupes',
      'besoin' => 'Besoins',
    ),
    'familles_matieres' => array(
      1 => 'Enseignements usuels',
      2 => 'Enseignements généraux',
      3 => 'Enseignements spécifiques',
      4 => 'Enseignements complémentaires',
    ),
    'familles_niveaux' => array(
      1 => 'Niveaux classes',
      2 => 'Niveaux particuliers',
    ),
    'profs_directeurs' => array(
      'directeur'  => 'Directeurs',
      'professeur' => 'Professeurs',
    ),
    'langues' => array(
      0 => 'Inconnue',
      1 => 'Enseignées',
      2 => 'Autres',
    ),
    'objet_recherche' => array(
      1 => 'item(s) matière(s)',
      2 => 'item du socle',
      3 => 'compétence du socle',
    ),
    // complété à partir de la base si besoin (contenu dynamique)
    'zones_geo'  => array(), 
    'continents' => array(),
    'paliers'    => array(),
  );

  public static $tab_select_option_first = array(
    'periode_personnalisee' => array( 0 , 'Personnalisée' ),
    'tous_regroupements'    => array( 0 , 'Tous les regroupements' ),
    'toutes_matieres'       => array( 0 , 'Toutes les matières' ),
    'fiche_generique'       => array( 0 , 'Fiche générique' ),
    'tampon_structure'      => array( 0 , 'Tampon de l\'établissement' ),
    'structures_partage'    => array( 0 , 'Toutes les structures partageant au moins un référentiel' ),
    // inutilisé
    'tous_niveaux'          => array( 0 , 'Tous les niveaux' ),
    'tous_piliers'          => array( 0 , 'Toutes les compétences' ),
    'tous_domaines'         => array( 0 , 'Tous les domaines' ),
    // complété à partir de la base si besoin (car indice variable)
    'matieres_famille'      => array(), 
    'niveaux_famille'       => array(),
  );

  // //////////////////////////////////////////////////
  // Méthodes
  // //////////////////////////////////////////////////

  public static $tab_choix = array();

  /**
   * Initialiser deux propriétés...
   * 
   * @param void
   * @return void
   */
  private static function init_variables()
  {
    Form::$dossier_cookie = CHEMIN_DOSSIER_COOKIE.$_SESSION['BASE'].DS;
    Form::$fichier_cookie = Form::$dossier_cookie.'user'.$_SESSION['USER_ID'].'.txt';
  }

  /**
   * Initialiser les choix d'un formulaire (certains choix sont présélectionnés ou imposés suivant les statuts).
   * Ce tableau sera ensuite surchargé avec les choix mémorisés éventuels (enregistré dans un fichier texte).
   * En cas d'ajout ultérieur d'une fonctionnalité, compléter cette fonction permet de ne pas générer d'erreur.
   * 
   * @param void
   * @return void
   */
  private static function init_tab_choix()
  {
    Form::init_variables();
    $check_type_individuel    = (in_array($_SESSION['USER_PROFIL_TYPE'],array('parent','eleve'))) ? 1 : 0 ;
    $check_etat_acquisition   = ( in_array($_SESSION['USER_PROFIL_TYPE'],array('directeur','professeur')) || test_user_droit_specifique($_SESSION['DROIT_RELEVE_ETAT_ACQUISITION'])   ) ? 1 : 0 ;
    $check_moyenne_score      = ( in_array($_SESSION['USER_PROFIL_TYPE'],array('directeur','professeur')) || test_user_droit_specifique($_SESSION['DROIT_RELEVE_MOYENNE_SCORE'])      ) ? 1 : 0 ;
    $check_pourcentage_acquis = ( in_array($_SESSION['USER_PROFIL_TYPE'],array('directeur','professeur')) || test_user_droit_specifique($_SESSION['DROIT_RELEVE_POURCENTAGE_ACQUIS']) ) ? 1 : 0 ;
    $check_conversion_sur_20  = test_user_droit_specifique($_SESSION['DROIT_RELEVE_CONVERSION_SUR_20']) ? 1 : 0 ;
    $check_aff_lien           = (in_array($_SESSION['USER_PROFIL_TYPE'],array('parent','eleve'))) ? 1 : 0 ;
    Form::$tab_choix = array(
      'matiere_id'             => 0 ,
      'niveau_id'              => 0 ,
      'palier_id'              => 0 ,
      'orientation'            => 'portrait' ,
      'couleur'                => 'oui' ,
      'legende'                => 'oui' , 
      'marge_min'              => 5 ,
      'pages_nb'               => 'optimise' ,
      'cart_contenu'           => 'AVEC_nom_SANS_result' ,
      'cart_detail'            => 'complet' ,
      'only_niveau'            => 0 ,
      'only_presence'          => 0 ,
      'only_socle'             => 0 ,
      'aff_coef'               => 0 ,
      'aff_socle'              => 1 ,
      'aff_lien'               => $check_aff_lien ,
      'aff_start'              => 0 ,
      'aff_domaine'            => 0 ,
      'aff_theme'              => 0 ,
      'cases_nb'               => 4 ,
      'cases_largeur'          => 5 ,
      'remplissage'            => 'plein' ,
      'colonne_bilan'          => 'oui' ,
      'colonne_vide'           => 0 ,
      'type_generique'         => 0 ,
      'type_individuel'        => $check_type_individuel ,
      'type_synthese'          => 0 ,
      'type_bulletin'          => 0 ,
      'aff_etat_acquisition'   => $check_etat_acquisition ,
      'aff_moyenne_scores'     => $check_moyenne_score ,
      'aff_pourcentage_acquis' => $check_pourcentage_acquis ,
      'conversion_sur_20'      => $check_conversion_sur_20 ,
      'indicateur'             => 'moyenne_scores' ,
      'tableau_tri_objet'      => 'eleve',
      'tableau_tri_mode'       => 'score',
      'with_coef'              => 1 ,
      'retroactif'             => 'auto' ,
      'mode_synthese'          => 'predefini' ,
      'fusion_niveaux'         => 1 ,
      'aff_socle_PA'           => 1 ,
      'aff_socle_EV'           => 1 ,
      'type'                   => '' ,
      'mode'                   => 'auto' ,
    );
  }

  /**
   * Charger les choix mémorisées d'un formulaire.
   * 
   * On commence par initialiser les valeurs (certains choix sont présélectionnés ou imposés suivant les statuts).
   * Puis on surcharge avec un choix mémorisé éventuel (enregistré dans un fichier texte).
   * 
   * @param void
   * @return void
   */

  public static function load_choix_memo()
  {
    Form::init_tab_choix();
    // Récupération du contenu du "cookie"
    if(is_file(Form::$fichier_cookie))
    {
      $contenu = file_get_contents(Form::$fichier_cookie);
      $tab_choix_cookie = @unserialize($contenu);
      if(is_array($tab_choix_cookie))
      {
        Form::$tab_choix = array_merge( Form::$tab_choix , $tab_choix_cookie );
      }
    }
  }

  /**
   * Enregistrer les choix mémorisées d'un formulaire.
   * 
   * @param string $page
   * @return void
   */

   public static function save_choix($page)
  {
    switch($page)
    {
      case 'cartouche' :
        global $orientation,$couleur,$legende,$marge_min,$cart_contenu,$cart_detail;
        $tab_choix_new = compact('orientation','couleur','legende','marge_min','cart_contenu','cart_detail');
        break;
      case 'grille_referentiel' :
        global $matiere_id,$niveau_id,$type_generique,$type_individuel,$type_synthese,$tableau_tri_objet,$tableau_tri_mode,$retroactif,$only_socle,$aff_coef,$aff_socle,$aff_lien,$cases_nb,$cases_largeur,$remplissage,$colonne_bilan,$colonne_vide,$orientation,$couleur,$legende,$marge_min,$pages_nb;
        $tab_choix_new = compact('matiere_id','niveau_id','type_generique','type_individuel','type_synthese','retroactif','only_socle','aff_coef','aff_socle','aff_lien','cases_nb','cases_largeur','remplissage','colonne_bilan','colonne_vide','orientation','couleur','legende','marge_min','pages_nb');
        break;
      case 'items_matiere' :
        global $matiere_id,$type_individuel,$type_synthese,$type_bulletin,$aff_etat_acquisition,$aff_moyenne_scores,$aff_pourcentage_acquis,$conversion_sur_20,$tableau_tri_objet,$tableau_tri_mode,$retroactif,$only_socle,$aff_coef,$aff_socle,$aff_lien,$aff_domaine,$aff_theme,$cases_nb,$cases_largeur,$orientation,$couleur,$legende,$marge_min,$pages_nb;
        $tab_choix_new = compact('matiere_id','type_individuel','type_synthese','type_bulletin','aff_etat_acquisition','aff_moyenne_scores','aff_pourcentage_acquis','conversion_sur_20','tableau_tri_objet','tableau_tri_mode','retroactif','only_socle','aff_coef','aff_socle','aff_lien','aff_domaine','aff_theme','cases_nb','cases_largeur','orientation','couleur','legende','marge_min','pages_nb');
        break;
      case 'items_selection' :
        global $type_individuel,$type_synthese,$type_bulletin,$aff_etat_acquisition,$aff_moyenne_scores,$aff_pourcentage_acquis,$conversion_sur_20,$tableau_tri_objet,$tableau_tri_mode,$with_coef,$retroactif,$aff_coef,$aff_socle,$aff_lien,$aff_domaine,$aff_theme,$cases_nb,$cases_largeur,$orientation,$couleur,$legende,$marge_min,$pages_nb;
        $tab_choix_new = compact('type_individuel','type_synthese','type_bulletin','aff_etat_acquisition','aff_moyenne_scores','aff_pourcentage_acquis','conversion_sur_20','tableau_tri_objet','tableau_tri_mode','with_coef','retroactif','aff_coef','aff_socle','aff_lien','aff_domaine','aff_theme','cases_nb','cases_largeur','orientation','couleur','legende','marge_min','pages_nb');
        break;
      case 'items_professeur' :
        global $type_individuel,$type_synthese,$type_bulletin,$aff_etat_acquisition,$aff_moyenne_scores,$aff_pourcentage_acquis,$conversion_sur_20,$tableau_tri_objet,$tableau_tri_mode,$with_coef,$retroactif,$only_socle,$aff_coef,$aff_socle,$aff_lien,$aff_domaine,$aff_theme,$cases_nb,$cases_largeur,$orientation,$couleur,$legende,$marge_min,$pages_nb;
        $tab_choix_new = compact('type_individuel','type_synthese','type_bulletin','aff_etat_acquisition','aff_moyenne_scores','aff_pourcentage_acquis','conversion_sur_20','tableau_tri_objet','tableau_tri_mode','with_coef','retroactif','only_socle','aff_coef','aff_socle','aff_lien','aff_domaine','aff_theme','cases_nb','cases_largeur','orientation','couleur','legende','marge_min','pages_nb');
        break;
      case 'items_multimatiere' :
        global $aff_etat_acquisition,$aff_moyenne_scores,$aff_pourcentage_acquis,$conversion_sur_20,$retroactif,$only_socle,$aff_coef,$aff_socle,$aff_lien,$aff_domaine,$aff_theme,$cases_nb,$cases_largeur,$orientation,$couleur,$legende,$marge_min,$pages_nb;
        $tab_choix_new = compact('aff_etat_acquisition','aff_moyenne_scores','aff_pourcentage_acquis','conversion_sur_20','retroactif','only_socle','aff_coef','aff_socle','aff_lien','aff_domaine','aff_theme','cases_nb','cases_largeur','orientation','couleur','legende','marge_min','pages_nb');
        break;
      case 'bilan_chronologique' :
        global $indicateur,$conversion_sur_20,$retroactif,$only_socle;
        $tab_choix_new = compact('indicateur','conversion_sur_20','retroactif','only_socle');
        break;
      case 'synthese_matiere' :
        global $matiere_id,$mode_synthese,$fusion_niveaux,$retroactif,$only_socle,$only_niveau,$aff_coef,$aff_socle,$aff_lien,$aff_start,$couleur,$legende,$marge_min;
        $tab_choix_new = compact('matiere_id','mode_synthese','fusion_niveaux','retroactif','only_socle','only_niveau','aff_coef','aff_socle','aff_lien','aff_start','couleur','legende','marge_min');
        break;
      case 'synthese_multimatiere' :
        global $fusion_niveaux,$retroactif,$only_socle,$only_niveau,$aff_coef,$aff_socle,$aff_lien,$aff_start,$couleur,$legende,$marge_min;
        $tab_choix_new = compact('fusion_niveaux','retroactif','only_socle','only_niveau','aff_coef','aff_socle','aff_lien','aff_start','couleur','legende','marge_min');
        break;
      case 'releve_socle' :
        global $palier_id,$only_presence,$aff_coef,$aff_socle,$aff_lien,$aff_start,$aff_socle_PA,$aff_socle_EV,$mode,$couleur,$legende,$marge_min;
        $tab_choix_new = compact('palier_id','only_presence','aff_coef','aff_socle','aff_lien','aff_start','aff_socle_PA','aff_socle_EV','mode','couleur','legende','marge_min');
      case 'synthese_socle' :
        global $palier_id,$type,$mode,$couleur,$legende,$marge_min;
        $tab_choix_new = compact('palier_id','type','mode','couleur','legende','marge_min');
        break;
      case 'matiere' :
        global $matiere_id;
        $tab_choix_new = compact('matiere_id');
        break;
      case 'palier' :
        global $palier_id;
        $tab_choix_new = compact('palier_id');
        break;
      case 'validation_socle_item' :
        global $palier_id,$mode;
        $tab_choix_new = compact('palier_id','mode');
        break;
      default :
        $tab_choix_new = array();
    }
    // Récupération du contenu du "cookie", surchargé avec les choix effectués
    Form::load_choix_memo();
    Form::$tab_choix = array_merge( Form::$tab_choix , $tab_choix_new );
    /*
      Remarque : il y a un problème de serialize avec les type float : voir http://fr2.php.net/manual/fr/function.serialize.php#85988
      Dans ce cas il faut remplacer
      serialize(Form::$tab_choix)
      par
      preg_replace( '/d:([0-9]+(\.[0-9]+)?([Ee][+-]?[0-9]+)?);/e', "'d:'.(round($1,9)).';'", serialize(Form::$tab_choix) );
    */
    FileSystem::ecrire_fichier(Form::$fichier_cookie,serialize(Form::$tab_choix));
  }

  /**
   * Afficher un élément select de formulaire à partir d'un tableau de données et d'options
   * 
   * Les select multiples sont convertis en une liste de checkbox (code plus lourd, mais résultat plus maniable pour l'utilisateur).
   * Cela revient à remplacer ...
   *  <select id="select_nom" name="select_nom[]" multiple size="8">
   *    <optgroup label="Groupe A">
   *      <option value="2204" selected>Option 1</option>
   *      <option value="2206">Option 2</option>
   *    </optgroup>
   *  </select>
   * ... par ...
   *  <span id="select_nom" class="select_multiple">
   *    <span>Groupe A</span>
   *    <label for="select_nom_2204" class="check"><input type="checkbox" name="select_nom[]" id="select_nom_2204" value="2204" checked /> Option 1</label>
   *    <label for="select_nom_2206"><input type="checkbox" name="select_nom[]" id="select_nom_2206" value="2206" /> Option 2</label>
   *  </span>
   * 
   * @param array             $DB_TAB       tableau des données [i] => [valeur texte optgroup]
   * @param string|bool       $select_nom   chaine à utiliser pour l'id/nom du select, ou FALSE si on retourne juste les options sans les encapsuler dans un select (doit être transmis si $multiple l'est aussi)
   * @param string|bool       $option_first 1ère option éventuelle [FALSE] [] [nom_option]
   * @param string|bool|array $selection    préselection éventuelle [FALSE] [TRUE] [val] [ou $...] [ou array(...)]
   * @param string            $optgroup     regroupement d'options éventuel [] [nom_du_regroupement]
   * @param bool              $multiple     TRUE si transmis pour forcer un faux select multiple
   * @return string
   */
  public static function afficher_select($DB_TAB,$select_nom,$option_first,$selection,$optgroup='',$multiple=FALSE)
  {
    // On commence par la 1ère option
    if($option_first===FALSE)
    {
      // ... sans option initiale
      $options = '';
    }
    elseif($option_first==='')
    {
      // ... avec une option initiale vierge
      $options = (!$multiple) ? '<option value=""></option>' : '' ;
    }
    else
    {
      // ... avec une option initiale dont le contenu est à récupérer
      list($option_valeur,$option_texte) = Form::$tab_select_option_first[$option_first];
      $options = (!$multiple) ? '<option value="'.$option_valeur.'">'.html($option_texte).'</option>' : '<label for="'.$select_nom.'_'.$option_valeur.'"><input type="checkbox" name="'.$select_nom.'[]" id="'.$select_nom.'_'.$option_valeur.'" value="'.$option_valeur.'" /> '.html($option_texte).'</label>' ;
    }
    if(is_array($DB_TAB))
    {
      // On construit les options...
      if(!$optgroup)
      {
        // ... classiquement, sans regroupements
        foreach($DB_TAB as $DB_ROW)
        {
          $options .= (!$multiple) ? '<option value="'.$DB_ROW['valeur'].'">'.html($DB_ROW['texte']).'</option>' : '<label for="'.$select_nom.'_'.$DB_ROW['valeur'].'"><input type="checkbox" name="'.$select_nom.'[]" id="'.$select_nom.'_'.$DB_ROW['valeur'].'" value="'.$DB_ROW['valeur'].'" /> '.html($DB_ROW['texte']).'</label>' ;
        }
      }
      else
      {
        // ... en regroupant par optgroup ; $tab_select_optgroup[$optgroup] est alors un tableau à 2 champs
        $tab_options = array();
        foreach($DB_TAB as $DB_ROW)
        {
          $tab_options[$DB_ROW['optgroup']][] = (!$multiple) ? '<option value="'.$DB_ROW['valeur'].'">'.html($DB_ROW['texte']).'</option>' : '<label for="'.$select_nom.'_'.$DB_ROW['valeur'].'"><input type="checkbox" name="'.$select_nom.'[]" id="'.$select_nom.'_'.$DB_ROW['valeur'].'" value="'.$DB_ROW['valeur'].'" /> '.html($DB_ROW['texte']).'</label>' ;
        }
        foreach($tab_options as $group_key => $tab_group_options)
        {
          $options .= (!$multiple) ? '<optgroup label="'.html(Form::$tab_select_optgroup[$optgroup][$group_key]).'">'.implode('',$tab_group_options).'</optgroup>' : '<span>'.html(Form::$tab_select_optgroup[$optgroup][$group_key]).'</span>'.implode('',$tab_group_options) ;
        }
      }
      // On sélectionne les options qu'il faut... (fait après le foreach précédent sinon c'est compliqué à gérer simultanément avec les groupes d'options éventuels
      if($selection===FALSE)
      {
        // ... ne rien sélectionner
      }
      elseif($selection===TRUE)
      {
        // ... tout sélectionner
        $options = (!$multiple) ? str_replace( '<option' , '<option selected' , $options ) : str_replace( array('><input','" />') , array(' class="check"><input','" checked />') , $options ) ;
      }
      else
      {
        // ... sélectionner une ou plusieurs option(s) ; $selection contient la valeur ou le tableau de valeurs à sélectionner
        if(!is_array($selection))
        {
          $options = (!$multiple) ? str_replace( 'value="'.$selection.'"' , 'value="'.$selection.'" selected' , $options ) : str_replace( array($selection.'"><input',$selection.'" />') , array($selection.'" class="check"><input',$selection.'" checked />') , $options ) ;
        }
        else
        {
          foreach($selection as $selection_val)
          {
            $options = (!$multiple) ? str_replace( 'value="'.$selection_val.'"' , 'value="'.$selection_val.'" selected' , $options ) : str_replace( array('for="'.$select_nom.'_'.$selection_val.'"','value="'.$selection_val.'"') , array('for="'.$select_nom.'_'.$selection_val.'" class="check"','value="'.$selection_val.'" checked') , $options ) ;
          }
        }
      }
    }
    // Si $DB_TAB n'est pas un tableau alors c'est une chaine avec un message d'erreur affichée sous la forme d'une option disable
    else
    {
      $options .= (!$multiple) ? '<option value="" disabled>'.$DB_TAB.'</option>' : $DB_TAB;
    }
    // On insère dans un select si demandé
    return (!$multiple) ? ( ($select_nom) ? '<select id="'.$select_nom.'" name="'.$select_nom.'">'.$options.'</select>' : $options ) : $options ;
  }

  /**
   * Fabrication de tableau javascript de jointures à partir des groupes
   * 
   * @param array     $tab_groupes          tableau des données [i] => [valeur texte optgroup]
   * @param bool      $tab_groupe_periode   charger ou non "tab_groupe_periode" pour les jointures groupes/périodes
   * @param bool      $tab_groupe_niveau    charger ou non "tab_groupe_niveau"  pour les jointures groupes/niveaux
   * @return void     alimente Layout::$tab_js_inline[]
   */
  public static function fabriquer_tab_js_jointure_groupe($tab_groupes,$tab_groupe_periode,$tab_groupe_niveau)
  {
    Layout::add( 'js_inline_before' , 'var tab_groupe_periode = new Array();' );
    Layout::add( 'js_inline_before' , 'var tab_groupe_niveau  = new Array();' );
    if(is_array($tab_groupes))
    {
      // On liste les ids des classes et groupes
      $tab_id_classe_groupe = array();
      foreach($tab_groupes as $tab_groupe_infos)
      {
        if( !isset($tab_groupe_infos['optgroup']) || ($tab_groupe_infos['optgroup']!='besoin') )
        {
          $tab_id_classe_groupe[] = $tab_groupe_infos['valeur'];
        }
      }
      if(count($tab_id_classe_groupe))
      {
        $listing_groupe_id = implode(',',$tab_id_classe_groupe);
        // Charger le tableau js $tab_groupe_periode de jointures groupes/périodes
        if($tab_groupe_periode)
        {
          $tab_memo_groupes = array();
          $DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_jointure_groupe_periode($listing_groupe_id);
          foreach($DB_TAB as $DB_ROW)
          {
            if(!isset($tab_memo_groupes[$DB_ROW['groupe_id']]))
            {
              $tab_memo_groupes[$DB_ROW['groupe_id']] = TRUE;
              Layout::add( 'js_inline_before' , 'tab_groupe_periode['.$DB_ROW['groupe_id'].'] = new Array();' );
            }
            Layout::add( 'js_inline_before' , 'tab_groupe_periode['.$DB_ROW['groupe_id'].']['.$DB_ROW['periode_id'].']="'.$DB_ROW['jointure_date_debut'].'_'.$DB_ROW['jointure_date_fin'].'";' );
          }
        }
        // Charger le tableau js $tab_groupe_niveau de jointures groupes/périodes
        if($tab_groupe_niveau)
        {
          $DB_TAB = DB_STRUCTURE_BILAN::DB_recuperer_niveau_groupes($listing_groupe_id);
          foreach($DB_TAB as $DB_ROW)
          {
            Layout::add( 'js_inline_before' , 'tab_groupe_niveau['.$DB_ROW['groupe_id'].'] = new Array('.$DB_ROW['niveau_id'].',"'.html($DB_ROW['niveau_nom']).'");' );
          }
        }
      }
    }
  }

}
?>