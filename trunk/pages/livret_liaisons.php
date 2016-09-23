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
$TITRE = html(Lang::_("Livret Scolaire")).' &rarr; '.html(Lang::_("Rubriques / Liaisons"));

// On récupère l'éventuel type de rubrique
$get_rubrique_type = isset($_GET['ref']) ? Clean::code($_GET['ref']) : '' ;

// On liste les pages et leurs paramètres principaux
$DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_pages( TRUE /*with_info_classe*/ );

// On complète le Sous-Menu d'en-tête
$tab_sousmenu = array();
foreach($DB_TAB as $DB_ROW)
{
  if(!$DB_ROW['groupe_nb'])
  {
    $class = 'disabled';
  }
  else if($DB_ROW['livret_page_rubrique_type']==$get_rubrique_type)
  {
    $class = 'actif';
  }
  else
  {
    $class = '';
  }
  if($DB_ROW['livret_page_rubrique_type']) // hors-brevet
  {
    $tab_sousmenu[$DB_ROW['livret_page_rubrique_type']][$class]['page_ref'][]    = $DB_ROW['livret_page_ref'];
    $tab_sousmenu[$DB_ROW['livret_page_rubrique_type']][$class]['page_moment'][] = $DB_ROW['livret_page_moment'];
    $tab_sousmenu[$DB_ROW['livret_page_rubrique_type']][$class]['rubrique_join'] = $DB_ROW['livret_page_rubrique_join'];
    $tab_sousmenu[$DB_ROW['livret_page_rubrique_type']][$class]['vignette'][]    = '<a href="'.URL_DIR_PDF.'livret_'.$DB_ROW['livret_page_ref'].'_original.pdf" class="fancybox" rel="gallery" data-titre="'.html($DB_ROW['livret_page_moment'].' : '.$DB_ROW['livret_page_resume']).'"><span class="livret livret_float_liaisons livret_'.$DB_ROW['livret_page_ref'].'"></span></a>';
  }
}
$SOUS_MENU .= '<br />';
foreach($tab_sousmenu as $livret_page_rubrique_type => $tab_class)
{
  foreach($tab_class as $class => $tab)
  {
    if($class=='actif')
    {
      $livret_page_moment = implode( ' - ' , $tab['page_moment'] );
      $livret_vignettes = implode( '' , $tab['vignette'] );
      $liaison_rubrique_type = substr($get_rubrique_type,3); // Par défaut
      $liaison_rubrique_join = $tab['rubrique_join']; // Actuel (matiere | domaine | theme | item | prof)
    }
    $class = ($class) ? ' class="'.$class.'"' : '' ;
    $SOUS_MENU .= '<a'.$class.' href="./index.php?page=livret&amp;section=liaisons&amp;ref='.$livret_page_rubrique_type.'">'.html(implode( ' - ' , $tab['page_ref'] )).'</a>'.NL;
  }
}

if(!isset($liaison_rubrique_type))
{
  echo'<p class="astuce">Choisir ci-dessus une section active du livret.</p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// On complète le titre de la page
$TITRE .= ' &rarr; '.html($livret_page_moment);
?>

<?php if($liaison_rubrique_type=='socle'): /* * * * * * CYCLE2 | CYCLE3 | CYCLE4 * * * * * */ ?>

<?php echo $livret_vignettes ?>
<ul class="puce ml">
  <li>Les liaisons des items des référentiels au composantes du socle commun sont effectuées par les enseignants (<span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=referentiels_socle__referentiel_modifier_contenu">DOC</a></span>).</span></li>
  <li>Voici un bilan des liaisons actuelles, aussi consultable via le menu <a href="./index.php?page=export_fichier">[Informations] [Export de données]</a>.</li>
</ul>
<hr />
<p id="socle_liaisons">
</p>
<?php
$cycle_id  = $get_rubrique_type[1];
$cycle_nom = 'Cycle '.$cycle_id;
Layout::add( 'js_inline_before' , 'var cycle_id  = '.$cycle_id.';' );
Layout::add( 'js_inline_before' , 'var cycle_nom = "'.$cycle_nom.'";' );
?>

<?php elseif($get_rubrique_type!==''): /* * * * * * CYCLE1 | CP-CE1-CE2 | CM1-CM2 | 6E | 5E-4E-3E * * * * * */ ?>

<?php
$tab_jointure = array(
  'item'    => "liaisons aux items",
  'theme'   => "liaisons aux thèmes",
  'domaine' => "liaisons aux domaines",
  'matiere' => "liaisons aux matières",
  'user'    => "liaisons aux enseignants (en projet, faisabilité à étudier)",
);
$select_jointure = '';
foreach($tab_jointure as $jointure_key => $jointure_txt)
{
  $selected      = ($jointure_key==$liaison_rubrique_join) ? ' selected' : '' ;
  $jointure_txt .= ($jointure_key==$liaison_rubrique_type) ? ' (par défaut)' : '' ;
  $select_jointure .= '<option value="'.$jointure_key.'"'.$selected.'>'.$jointure_txt.'</option>';
}

// Une requête pour récupérer les éléments de référentiel
$tab_element = array();
$ordre = 0;
if( $liaison_rubrique_join != 'user' )
{
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_arborescence( 0 /*prof_id*/ , 0 /*matiere_id*/ , 0 /*niveau_id*/ , FALSE /*only_socle*/ , FALSE /*only_item*/ , FALSE /*socle_nom*/ , FALSE /*s2016_count*/ , FALSE /*item_comm*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    $element_id = $DB_ROW[$liaison_rubrique_join.'_id'];
    if( !is_null($element_id) && !isset($tab_element[$element_id]) )
    {
      $ordre++;
      $element_nom = '';
      switch($liaison_rubrique_join)
      {
        case 'item' :
          $element_nom = ' | '.$DB_ROW['item_nom'];
        case 'theme' :
          $element_nom = ' | '.$DB_ROW['theme_nom'].$element_nom;
        case 'domaine' :
          $element_nom = ' | '.$DB_ROW['niveau_nom'].' | '.$DB_ROW['domaine_nom'].$element_nom;
        case 'matiere' :
          $element_nom = $DB_ROW['matiere_nom'].$element_nom;
      }
      $tab_element[$element_id] = array( 'ordre'=>$ordre , 'nom'=>$element_nom , 'used'=>FALSE );
    }
  }
}
else
{
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'professeur' /*profil_type*/ , 1 /*statut*/ , 'all' /*groupe_type*/ , 0 /*groupe_id*/, 'alpha' /*eleves_ordre*/ , 'user_id, CONCAT(user_nom," ",user_prenom) AS user_identite' ) ;
  foreach($DB_TAB as $DB_ROW)
  {
    $ordre++;
    $element_id = $DB_ROW['user_id'];
    $element_nom = $DB_ROW['user_identite'];
    $tab_element[$element_id] = array( 'ordre'=>$ordre , 'nom'=>$element_nom , 'used'=>FALSE );
  }
}

// Une requête pour récupérer les rubriques avec les jointures vers les référentiels
$tab_rubrique = array();
$DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_rubriques_avec_jointures_référentiels( $get_rubrique_type );
foreach($DB_TAB as $DB_ROW)
{
  $rubrique_id  = $DB_ROW['livret_rubrique_id'];
  $rubrique_nom = $DB_ROW['livret_rubrique_nom'] ;
  $tab_rubrique[$rubrique_id] = array( 'join'=>array() , 'nom'=>$rubrique_nom );
  if( $DB_ROW['listing_elements'] )
  {
    $tab_element_id = explode( ',' , $DB_ROW['listing_elements'] );
    foreach($tab_element_id as $element_id)
    {
      $tab_rubrique[$rubrique_id]['join'][$tab_element[$element_id]['ordre']] = array(
        'id'  => $element_id ,
        'nom' => $tab_element[$element_id]['nom']
      );
      $tab_element[$element_id]['used'] = TRUE;
    }
  }
}

// Formulaire avec les éléments de référentiels restants
$tab_html_element = array( FALSE => array() , TRUE => array());
foreach($tab_element as $element_id => $tab_info)
{
  $tab_html_element[$tab_info['used']][$element_id] = '<label for="f_element_'.$element_id.'" data-ordre='.$tab_info['ordre'].'><input type="checkbox" name="f_elements[]" id="f_element_'.$element_id.'" value="'.$element_id.'"> '.html($tab_info['nom']).'</label>';
}

// Formulaire avec les éléments par rubriques
$tab_html_rubrique = array();
foreach($tab_rubrique as $rubrique_id => $tab_info)
{
  $tab_html_rubrique[$rubrique_id] = '<h3>'.html($tab_info['nom']).' <q class="ajouter" title="Ajouter les éléments"></q></h3><div id="f_rubrique_'.$rubrique_id.'" class="ml">';
  if(!empty($tab_info['join']))
  {
    ksort($tab_info['join']);
    foreach($tab_info['join'] as $ordre => $tab)
    {
      $tab_html_rubrique[$rubrique_id] .= '<div id="f_liaison_'.$tab['id'].'_'.$rubrique_id.'" data-ordre='.$ordre.'><q class="annuler" title="Retirer cet élément"></q> '.html($tab['nom']).'</div>';
    }
  }
  else
  {
    $tab_html_rubrique[$rubrique_id] .= '<label class="alerte">Aucun élément de <em>SACoche</em> associé.</label>';
  }
  $tab_html_rubrique[$rubrique_id] .= '</div>';
}

// Texte d'explication / d'avertissement pour le collège

if($liaison_rubrique_type=='matiere')
{
  
  if(DB_STRUCTURE_LIVRET::DB_tester_livret_matiere_siecle())
  {
    $DB_ROW = DB_STRUCTURE_SIECLE::DB_recuperer_import_date_annee('sts_emp_UAI');
    if( empty($DB_ROW) || is_null($DB_ROW['siecle_import_date']) )
    {
      $texte_info_rubriques = "Votre import des matières de <em>siecle</em> n'a pas été trouvé, probablement à cause d'un bug corrigé courant septembre 2016.";
      $texte_info_rubriques .= "<br /><b>Merci de ré-importer le fichier issu de <em>STS-Web</em> avec les données des professeurs : aller jusqu'à l'étape 2 suffit.</b>";
    }
    else
    {
      $texte_info_rubriques = "Dernier import des matières de <em>siecle</em> en date du <b>".To::date_mysql_to_french($DB_ROW['siecle_import_date'])."</b>.";
      $annee_scolaire = To::annee_scolaire('siecle');
      if( $annee_scolaire != $DB_ROW['siecle_import_annee'] )
      {
        $texte_info_rubriques .= "<br /><b>Attention : aucun import trouvé pour cette année scolaire &rarr; vous devez mettre à jour en important le fichier issu de <em>STS-Web</em> !</b>";
      }
    }
  }
  else
  {
    $texte_info_rubriques = "Aucun import <em>siecle</em> trouvé : les rubriques actuellement enregistrées sont celles du modèle de <em>Livret Scolaire</em> officiel.";
  }
}

// Javascript
Layout::add( 'js_inline_before' , 'var rubrique_type="'.$get_rubrique_type.'";' );
Layout::add( 'js_inline_before' , 'var rubrique_join="'.$liaison_rubrique_join.'";' );
?>

<?php if($liaison_rubrique_type=='matiere'): /* * * * * * SOUS-BOUCLE 6E | 5E-4E-3E * * * * * */ ?>

<div class="p accueil64 alert64">
  <div class="danger">L'export du <em>Livret Scolaire</em> vers l'application ministérielle pour les relevés périodiques des classes de collège s'annonce particulièrement complexe.</div>
  <div>En effet, il requiert, entre autres, d'utiliser les matières correspondant strictement aux services des enseignants (avec "modalités d'élections" associées&hellip;).</div>
  <p>
    Concernant les établissements <em>sans</em> accès à <em>siecle</em> (par exemple les lycées français à l'étranger), les matières utilisées sont les rubriques du modèle officiel.<br />
    Dans ce cas il n'y a pas d'export vers l'application nationale.
  </p>
  <p>
    Concernant les établissements utilisant <em>siecle</em>, les matières utilisées sont celles issues de <em>STS-Web</em>.<br />
    Elles sont récupérées et mises à jour par <em>SACoche</em> lors de <a href="./index.php?page=administrateur_fichier_user"><b>l'import des personnels de l'établissement</b></a> depuis <em>siecle</em>.
  </p>
  <div class="danger"><?php echo $texte_info_rubriques ?></div>
  <p>
    <div class="astuce">Il est inutile d'essayer d'utiliser toutes les rubriques du livret ci-dessous.</div>
    Au contraire, mieux vaut aller au plus simple en ne renseignant que l'essentiel pour éviter des complications ultérieures.<br />
    Les rubriques sans élément associé seront tout simplement ignorées.
  </p>
  <p>
    <div class="astuce">Un paramétrage séparé est proposé pour le niveau 6e et les niveaux 5e-4e-3e.</div>
    Ceci avec l'objectif de pouvoir gérer au mieux les différentes situations selon les référentiels de Cycle 3 utilisés&hellip;
  </p>
</div>
<hr />

<?php endif /* * * * * * FIN SOUS-BOUCLE * * * * * */ ?>

<h2>Choix général</h2>
<?php echo $livret_vignettes ?>
<form action="#" method="post" id="form_gestion">
  <p><span class="manuel">Documentation à venir ultérieurement&hellip;</p>
  <p>Type de liaison aux référentiels : <select id="rubrique_join" name="rubrique_join"><?php echo $select_jointure ?></select></p>
  <p>&nbsp;</p>
</form>

<hr />

<h2>Éléments de <em>SACoche</em></h2>

<form action="#" method="post" id="form_select">
  <div class="liaison_livret">
    <h3>Éléments restants</h3>
    <div class="ml"><span id="f_elements_rest" class="select_multiple"><?php echo implode('',$tab_html_element[FALSE]); ?></span></div>
  </div>
  <div class="liaison_livret">
    <h3>Éléments déjà reliés</h3>
    <div class="ml"><span id="f_elements_used" class="select_multiple"><?php echo implode('',$tab_html_element[TRUE]); ?></span></div>
  </div>
</form>

<hr />

<h2>Rubriques du livret</h2>

<form action="#" method="post" id="form_rubrique">
<?php echo implode('<p />',$tab_html_rubrique); ?>
</form>

<hr />
<p>&nbsp;</p>

<?php else: /* * * * * * BREVET (normalement il n'y a pas de lien qui pointe vers ici) * * * * * */ ?>

<ul class="puce">
  <li>Pour la partie "Brevet des collèges", aucune liaison n'est à effectuer, c'est la page "Cycle 4" qui fournit les informations.</span></li>
</ul>

<?php endif /* * * * * * FIN TYPES DE RUBRIQUES * * * * * */ ?>
