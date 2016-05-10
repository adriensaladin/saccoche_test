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
$TITRE = html(Lang::_("Livret Scolaire")).' &rarr; '.html(Lang::_("Accueil"));

// Vérifier qu'il y a au moins une classe dans l'établissement
// Vérifier qu'il y a au moins une classe associée au livret, et sinon essayer de faire le boulot automatiquement
$result = DB_STRUCTURE_LIVRET::DB_initialiser_jointures_livret_classes();
if( $result === FALSE )
{
  echo'<p class="danger">Aucune classe enregistrée ! Commencez par peupler <em>SACoche</em> (importer les élèves crée et remplit les classes).</p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}
if( is_int($result) )
{
  $s = ($result>1) ? 's' : '' ;
  echo'<p class="danger">Aucune association de classe au livret scolaire n\'était enregistrée.<br /><em>SACoche</em> les a initialisées : '.$result.' association'.$s.' effectuée'.$s.'<br />Vérifiez et ajustez si besoin (étape "Classes" ci-dessus).</p>'.NL;
}

// On liste les types de parcours
$DB_TAB_parcours = DB_STRUCTURE_LIVRET::DB_lister_parcours_type();

// On liste les thèmes d'enseignements pratiques interdisciplinaires
$DB_TAB_epi = DB_STRUCTURE_LIVRET::DB_lister_epi_theme();

$tab_nb = array(
  'epi'      => array(),
  'ap'       => array(),
  'parcours' => array(),
);

// On compte le nb d'enseignements pratiques interdisciplinaires par page et par thème
$DB_TAB = DB_STRUCTURE_LIVRET::DB_compter_epi_par_page();
foreach($DB_TAB as $DB_ROW)
{
  $tab_nb['epi'][$DB_ROW['livret_page_ref']][$DB_ROW['livret_epi_theme_code']] = $DB_ROW['nombre'];
}

// On compte le nb d'accompagnements personnalisés par page
$DB_TAB = DB_STRUCTURE_LIVRET::DB_compter_ap_par_page();
foreach($DB_TAB as $DB_ROW)
{
  $tab_nb['ap'][$DB_ROW['livret_page_ref']] = $DB_ROW['nombre'];
}

// On compte le nb de parcours par page et par type
$DB_TAB = DB_STRUCTURE_LIVRET::DB_compter_parcours_par_page();
foreach($DB_TAB as $DB_ROW)
{
  $tab_nb['parcours'][$DB_ROW['livret_page_ref']][$DB_ROW['livret_parcours_type_code']] = $DB_ROW['nombre'];
}

?>

<p>
  <!-- <span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__notanet_fiches_brevet#toggle_etape2_epreuves">DOC : Notanet &amp; Fiches brevet &rarr; Définition des épreuves</a></span><br /> -->
  <span class="astuce">Effectuer dans l'ordre les étapes ci-dessus.</span>
</p>

<hr />

<h2>Tableau synthétique</h2>

<table class="p">
  <thead>
    <tr>
      <th>Moment</th>
      <th>Objet</th>
      <th>Rubriques</th>
      <th>Seuils</th>
      <th>E.P.I.</th>
      <th>A.P.</th>
      <th>Parcours</th>
      <th>Édition</th>
    </tr>
  </thead>
  <tbody>
    <?php
    $tab_rubriques = array(
      'theme'   => "liaisons aux thèmes".'<br /><span class="fluo">configuration à venir</span>',
      'domaine' => "liaisons aux domaines".'<br /><span class="fluo">configuration à venir</span>',
      'matiere' => "liaisons aux matières".'<br /><span class="fluo">configuration à venir</span>',
      'socle'   => "liaisons au items<br />voir gestion des référentiels",
      ''        => "",
    );
    $tab_seuils = array(
      'reussite' => "degrés de réussite".'<br /><span class="fluo">configuration à venir</span>',
      'objectif' => "objectifs d'apprentissage".'<br /><span class="fluo">configuration à venir</span>',
      'maitrise' => "degrés de maîtrise".'<br /><span class="fluo">configuration à venir</span>',
      'moyenne'  => "",
    );
    $tab_bad = array( ' - '    , "Bilan de l'acquisition des connaissances et compétences" );
    $tab_bon = array( '<br />' , "Bilan des acquisitions" );
    $DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_pages( TRUE /*with_info_classe*/ );
    foreach($DB_TAB as $DB_ROW)
    {
      $moment = $DB_ROW['livret_page_moment'];
      $objet  = str_replace($tab_bad,$tab_bon,$DB_ROW['livret_page_resume']);
      if(!$DB_ROW['groupe_nb'])
      {
        echo'<tr class="notnow">';
        echo  '<td>'.$moment.'</td>';
        echo  '<td>'.$objet.'</td>';
        echo  '<td colspan="6" class="hc">Aucune classe associée à cette partie du livret.</td>';
        echo'</tr>'.NL;
      }
      else
      {
        $tab_groupes = explode('<br />',$DB_ROW['listing_groupe_nom']);
        sort($tab_groupes);
        $moment_title = implode('<br />',$tab_groupes);
        $s = ($DB_ROW['groupe_nb']>1) ? 's' : '' ;
        $moment = '<b>'.$moment.'</b>'.'<br /><a title="'.$moment_title.'" href="./index.php?page=livret&amp;section=classes">'.$DB_ROW['groupe_nb'].' classe'.$s.'</a>';
        // epi
        $epi = '';
        if($DB_ROW['livret_page_epi'])
        {
          foreach($DB_TAB_epi as $key => $ROW_epi)
          {
            $epi_code = $ROW_epi['livret_epi_theme_code'];
            if(isset($tab_nb['epi'][$DB_ROW['livret_page_ref']][$epi_code]))
            {
              $epi_aff   = substr($epi_code,4);
              $epi_nb    = $tab_nb['epi'][$DB_ROW['livret_page_ref']][$epi_code];
              $epi_class = ($epi_nb) ? 'bf' : 'bj' ;
              $s         = ($epi_nb>1) ? 's' : '' ;
              $epi_title = html($ROW_epi['livret_epi_theme_nom']).'<br />';
              $epi_title.= $epi_nb.' enregistré'.$s;
              $epi .= '<div class="bf" title="'.$epi_title.'"><a href="./index.php?page=livret&amp;section=epi">'.$epi_nb.' '.$epi_aff.'</a></div>';
            }
          }
          if(!$epi)
          {
            $epi = '<div class="bj"><a href="./index.php?page=livret&amp;section=epi">aucun</a></div>';
          }
        }
        // ap
        $ap = '';
        if($DB_ROW['livret_page_ap'])
        {
          $ap_nb    = isset($tab_nb['ap'][$DB_ROW['livret_page_ref']]) ? $tab_nb['ap'][$DB_ROW['livret_page_ref']] : 0 ;
          $ap_class = ($ap_nb) ? 'bf' : 'bj' ;
          $s        = ($ap_nb>1) ? 's' : '' ;
          $ap_aff   = ($ap_nb) ? $ap_nb.' enregistré'.$s : 'aucun' ;
          $ap       = '<div class="'.$ap_class.'"><a href="./index.php?page=livret&amp;section=ap">'.$ap_aff.'</a></div>';
        }
        // parcours
        $parcours = '';
        if($DB_ROW['livret_page_parcours'])
        {
          $tab_parcours_code = explode( ',' , $DB_ROW['livret_page_parcours'] );
          foreach($tab_parcours_code as $parcours_code)
          {
            $parcours_aff   = substr($parcours_code,2);
            $parcours_get   = strtolower($parcours_aff);
            $parcours_nb    = isset($tab_nb['parcours'][$DB_ROW['livret_page_ref']][$parcours_code]) ? $tab_nb['parcours'][$DB_ROW['livret_page_ref']][$parcours_code] : 0 ;
            $parcours_class = ($parcours_nb) ? 'bf' : 'bj' ;
            $s              = ($parcours_nb>1) ? 's' : '' ;
            $parcours_title = html($DB_TAB_parcours[$parcours_code][0]['livret_parcours_type_nom']).'<br />';
            $parcours_title.= ($parcours_nb) ? $parcours_nb.' enregistré'.$s : 'aucun répertorié' ;
            $parcours .= '<div class="'.$parcours_class.'" title="'.$parcours_title.'"><a href="./index.php?page=livret&amp;section=parcours&amp;code='.$parcours_get.'">'.$parcours_nb.' '.$parcours_aff.'</a></div>';
          }
        }
        // affichage
        echo'<tr>';
        echo  '<td>'.$moment.'</td>';
        echo  '<td>'.$objet.'</td>';
        echo  '<td>'.$tab_rubriques[$DB_ROW['livret_page_rubrique_type']].'</td>';
        echo  '<td>'.$tab_seuils[$DB_ROW['livret_page_colonne_type']].'</td>';
        echo  '<td>'.$epi.'</td>';
        echo  '<td>'.$ap.'</td>';
        echo  '<td>'.$parcours.'</td>';
        echo  '<td class="fluo">à venir</td>';
        echo'</tr>'.NL;
      }
    }
    ?>
  </tbody>
</table>
