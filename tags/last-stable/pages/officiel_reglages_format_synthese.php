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
$TITRE = "Format de synthèse par référentiel";
?>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__reglages_syntheses_bilans#toggle_type_synthese">DOC : Réglages synthèses &amp; bilans &rarr; Format de synthèse adapté suivant chaque référentiel</a></span></div>

<hr />

<form action="#" method="post" id="form_synthese"><fieldset>

<?php

$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_referentiels();

if(empty($DB_TAB))
{
  echo'<p class="danger">Aucun référentiel enregistré !</p>';
}
else
{
  $tab_choix = array( 'domaine'=>'synthèse par domaine' , 'theme'=>'synthèse par thème' , 'sans'=>'pas de synthèse' );
  // Récupérer la liste des domaines de chaque référentiel
  $tab_domaines = array();
  $DB_TAB_DOMAINES = DB_STRUCTURE_ADMINISTRATEUR::DB_recuperer_referentiels_domaines();
  foreach($DB_TAB_DOMAINES as $DB_ROW)
  {
    $ids = $DB_ROW['matiere_id'].'_'.$DB_ROW['niveau_id'];
    $tab_domaines[$ids][] = '<li class="li_n1">'.html($DB_ROW['domaine_nom']).'</li>';
  }
  // Récupérer la liste des thèmes de chaque référentiel
  $tab_themes = array();
  $DB_TAB_THEMES = DB_STRUCTURE_ADMINISTRATEUR::DB_recuperer_referentiels_themes();
  foreach($DB_TAB_THEMES as $DB_ROW)
  {
    $ids = $DB_ROW['matiere_id'].'_'.$DB_ROW['niveau_id'];
    $tab_themes[$ids][] = '<li class="li_n2">'.html($DB_ROW['theme_nom']).'</li>';
  }
  // Passer en revue les référentiels
  foreach($DB_TAB as $DB_ROW)
  {
    $ids = $DB_ROW['matiere_id'].'_'.$DB_ROW['niveau_id'];
    // Titre + boutons radio + bouton validation
    echo'<h4>'.html($DB_ROW['matiere_nom'].' - '.$DB_ROW['niveau_nom']).'</h4>';
    echo'<ul class="puce"><li>Traitement :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    foreach($tab_choix as $option_valeur => $option_texte)
    {
      $checked = ($DB_ROW['referentiel_mode_synthese']==$option_valeur) ? ' checked' : '' ;
      echo'<label for="f_'.$ids.'_'.$option_valeur.'"><input type="radio" id="f_'.$ids.'_'.$option_valeur.'" name="f_'.$ids.'" value="'.$option_valeur.'"'.$checked.' /> '.$option_texte.'</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    }
    echo ($DB_ROW['referentiel_mode_synthese']=='inconnu') ? '<button id="bouton_'.$ids.'" type="button" class="valider" disabled>Valider.</button><label id="label_'.$ids.'" class="erreur">Choix manquant !</label>' : '<button id="bouton_'.$ids.'" type="button" class="valider">Valider.</button><label id="label_'.$ids.'" class="valide">ok</label>' ;
    echo'</li></ul>';
    // Div avec ses domaines
    $class = ($DB_ROW['referentiel_mode_synthese']=='domaine') ? '' : ' class="hide"' ;
    echo'<div id="domaine_'.$ids.'"'.$class.'>';
    if(isset($tab_domaines[$ids]))
    {
      echo'<ul class="ul_n1">'.implode('',$tab_domaines[$ids]).'</ul>';
    }
    echo'</div>';
    // Div avec ses thèmes
    $class = ($DB_ROW['referentiel_mode_synthese']=='theme') ? '' : ' class="hide"' ;
    echo'<div id="theme_'.$ids.'"'.$class.'>';
    if(isset($tab_themes[$ids]))
    {
      echo'<ul class="ul_n1">'.implode('',$tab_themes[$ids]).'</ul>';
    }
    echo'</div>';
  }
}
?>

</fieldset></form>
<p />