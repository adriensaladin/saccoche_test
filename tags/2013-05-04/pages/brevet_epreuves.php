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
$TITRE = "Étape n°2 - Indiquer les référentiels à utiliser et la manière d'en extraire une note";
?>

<p>
  <span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__notanet_fiches_brevet#toggle_etape2_epreuves">DOC : Notanet &amp; Fiches brevet &rarr; Définition des épreuves</a></span><br />
  <span class="astuce">Il importe de soigner cette configuration, qui détermine les notes extraites à l'étape suivante.</span>
</p>
<hr />

<?php
// Lister les matières utilisées dans l'établissement
$tab_matiere = array();
$tab_matiere_js = 'var tab_matiere = new Array();';
$DB_TAB = DB_STRUCTURE_COMMUN::DB_OPT_matieres_etabl();
if(is_string($DB_TAB))
{
  echo'<p class="danger">'.$DB_TAB.'<p>';
  return; // Ne pas exécuter la suite de ce fichier inclus.
}
foreach($DB_TAB as $DB_ROW)
{
  $tab_matiere[$DB_ROW['valeur']] = html($DB_ROW['texte']);
  $tab_matiere_js .= 'tab_matiere['.$DB_ROW['valeur'].']="'.html($DB_ROW['texte']).'";';
}

// Lister les séries de Brevet à configurer
$DB_TAB_series = DB_STRUCTURE_BREVET::DB_lister_brevet_series_etablissement();
if(empty($DB_TAB_series))
{
  echo'<p class="danger">Aucun élève n\'est associé à une série du brevet !<p>';
  echo'<div class="astuce"><a href="./index.php?page=brevet&amp;section=series">Effectuer l\'étape n°1.</a><div>';
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// Formulaires communs
$tab_choix_recherche = array();
$tab_choix_recherche[] = array( 'valeur'=>1 , 'texte'=>'utiliser en priorité les moyennes des bulletins (si existantes)' );
$tab_choix_recherche[] = array( 'valeur'=>0 , 'texte'=>'utiliser la moyenne annuelle des acquisitions (jusqu\'à ce jour)' );
$tab_choix_moyenne = array();
$tab_choix_moyenne[] = array( 'valeur'=>1 , 'texte'=>'utiliser la moyenne du premier référentiel trouvé' );
$tab_choix_moyenne[] = array( 'valeur'=>0 , 'texte'=>'utiliser la moyenne de tous les référentiels trouvés' );

// Passer les séries de Brevet en revue
foreach($DB_TAB_series as $DB_ROW)
{
  $s = ($DB_ROW['nombre']>1) ? 's' : '' ;
  echo'<h2 id="h2_'.$DB_ROW['brevet_serie_ref'].'">'.html($DB_ROW['brevet_serie_nom']).' ('.$DB_ROW['nombre'].' élève'.$s.')</h2>'."\r\n";
  echo'<form action="#" method="post" id="form_'.$DB_ROW['brevet_serie_ref'].'">'."\r\n";
  // Récupérer les paramètres et lister les réglages éventuellement déjà enregistrés
  $DB_TAB_epreuves = DB_STRUCTURE_BREVET::DB_lister_brevet_epreuves($DB_ROW['brevet_serie_ref']);
  foreach($DB_TAB_epreuves as $DB_ROW)
  {
    // Id & Nom épreuve & Infos
    $id_start = ''.$DB_ROW['brevet_serie_ref'].'_'.$DB_ROW['brevet_epreuve_code'];
    echo'<h4 id="h4_'.$id_start.'">'.html($DB_ROW['brevet_epreuve_nom']).'</h4>'."\r\n";
    if(!$DB_ROW['brevet_epreuve_note_comptee'])
    {
      echo'<p class="astuce">Présence d\'une note obligatoire, mais seulement à titre informatif, celle-ci n\'étant pas comptabilisée dans le total des points.</p>'."\r\n";
    }
    if(!$DB_ROW['brevet_epreuve_note_chiffree'])
    {
      echo'<p class="astuce">Pas de note chiffrée à saisir pour cette épreuve : uniquement un état de validation.</p>'."\r\n";
    }
    echo'<p>'."\r\n";
    // Mode de recherche
    $f_nom_recherche = 'f_'.$id_start.'_recherche';
    $selection_recherche = ($DB_ROW['brevet_epreuve_choix_recherche']===NULL) ? FALSE : $DB_ROW['brevet_epreuve_choix_recherche'] ;
    echo'<label class="tab" for="'.$f_nom_recherche.'">Mode de recherche :</label>'.Form::afficher_select($tab_choix_recherche, $f_nom_recherche /*select_nom*/ , FALSE /*option_first*/ , $selection_recherche , '' /*optgroup*/).'<br />'."\r\n";
    // Moyenne utilisée
    $f_nom_moyenne = 'f_'.$id_start.'_moyenne';
    $selection_moyenne = ($DB_ROW['brevet_epreuve_choix_moyenne']===NULL)   ? FALSE : $DB_ROW['brevet_epreuve_choix_moyenne'] ;
    echo'<label class="tab" for="'.$f_nom_moyenne.'">Moyenne utilisée :</label>'.Form::afficher_select($tab_choix_moyenne, $f_nom_moyenne /*select_nom*/ , FALSE /*option_first*/ , $selection_moyenne , '' /*optgroup*/).'<br />'."\r\n";
    // Matière(s)
    $listing_matieres_id_select = ($DB_ROW['brevet_epreuve_choix_matieres']===NULL) ? $DB_ROW['brevet_epreuve_matieres_cibles'] : $DB_ROW['brevet_epreuve_choix_matieres'] ;
    $tab_matieres_id_select = explode(',',$listing_matieres_id_select);
    $tab_matieres_input_texte  = array();
    $tab_matieres_input_hidden = array();
    foreach($tab_matiere as $matiere_id => $matiere_nom)
    {
      if(in_array($matiere_id,$tab_matieres_id_select))
      {
        $tab_matieres_input_texte[]  = $matiere_nom;
        $tab_matieres_input_hidden[] = $matiere_id;
      }
    }
    $requis_texte = ($DB_ROW['brevet_epreuve_obligatoire']) ? '<span class="now">[obligatoire]</span>' : '<span class="notnow">[optionnel]</span>' ;
    $requis_class = ($DB_ROW['brevet_epreuve_obligatoire']) ? ' class="required"' : '' ;
    $f_nom_matieres = 'f_'.$id_start.'_matieres';
    echo'<label class="tab" for="'.$f_nom_matieres.'_text">Référentiel(s) :</label><input id="'.$f_nom_matieres.'_text" type="text" value="'.implode(' ; ',$tab_matieres_input_texte).'" size="50" readonly /><q class="modifier" title="Modifier la ou les matière(s)."></q><input id="'.$f_nom_matieres.'_id" name="'.$f_nom_matieres.'" type="hidden" value="'.implode(',',$tab_matieres_input_hidden).'"'.$requis_class.' /> '.$requis_texte."\r\n";
    echo'</p>'."\r\n";
  }
  echo'<p><label class="tab"></label><button id="bouton_valider_'.$DB_ROW['brevet_serie_ref'].'" type="button" class="parametre">Valider les paramètres pour cette série.</button><label id="ajax_msg_'.$DB_ROW['brevet_serie_ref'].'">&nbsp;</label>';
  echo'</form><hr />'."\r\n";
}
?>


<script type="text/javascript">
  <?php echo $tab_matiere_js ?> 
</script>

<form action="#" method="post" id="zone_ordonner" class="hide">
  <h2>Choix ordonné du/des référentiel(s) matière(s)</h2>
  <p class="b" id="titre_ordonner"></p>
  <p>
    <button id="valider_ordre" type="button" class="valider">Valider ce choix ordonné</button> <button id="fermer_zone_ordonner" type="button" class="retourner">Retour</button>
  </p>
  <h4>Matière(s) à considérer</h4>
  <ul id="sortable_oui" class="connectedSortable">
    <li></li>
  </ul>
  <h4>Matière(s) à ne pas considérer</h4>
  <ul id="sortable_non" class="connectedSortable">
    <li></li>
  </ul>
</form>
