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
$TITRE = "Référentiels en place (dans l'établissement)";

if( ($_SESSION['USER_PROFIL_TYPE']!='administrateur') && !test_user_droit_specifique($_SESSION['DROIT_VOIR_REFERENTIELS']) )
{
  echo'<p class="danger">Vous n\'avez pas un profil autorisé pour accéder à cette fonctionnalité !<p>';
  echo'<div class="astuce">Profils autorisés (par les administrateurs) :<div>';
  echo afficher_profils_droit_specifique($_SESSION['DROIT_VOIR_REFERENTIELS'],'li');
  return; // Ne pas exécuter la suite de ce fichier inclus.
}
?>

<ul class="puce">
  <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=referentiels_socle__referentiel_organisation">DOC : Organisation des items dans les référentiels.</a></span></li>
  <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=referentiels_socle__calcul_scores_etats_acquisitions">DOC : Calcul des scores et des états d'acquisitions.</a></span></li>
</ul>

<form action="#" method="post">
<hr />

<?php
// Séparé en plusieurs requêtes sinon on ne s'en sort pas (entre les matières sans coordonnateurs, sans référentiel, les deux à la fois...).
// La recherche ne s'effectue que sur les matières et niveaux utilisés, sans débusquer des référentiels résiduels.
$tab_matiere = array();
$tab_niveau  = array();
$tab_colonne = array();

// On récupère la liste des matières utilisées par l'établissement
$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_matieres_etablissement( TRUE /*order_by_name*/ );
$nb_matieres = !empty($DB_TAB) ? count($DB_TAB) : 0 ;
if(!$nb_matieres)
{
  echo'<p><span class="danger">Aucune matière associée à l\'établissement !</span></p>';
}
else
{
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_matiere[$DB_ROW['matiere_id']] = array(
      'nom'         => html($DB_ROW['matiere_nom']) ,
      'nb_demandes' => $DB_ROW['matiere_nb_demandes']
    );
  }
  // On récupère la liste des niveaux utilisés par l'établissement
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_niveaux_etablissement(TRUE /*with_specifiques*/);
  $nb_niveaux = !empty($DB_TAB) ? count($DB_TAB) : 0 ;
  if(!$nb_niveaux)
  {
    echo'<p><span class="danger">Aucun niveau n\'est rattaché à l\'établissement !</span></p>';
  }
  else
  {
    echo'<p><span class="astuce">Cliquer sur l\'&oelig;il pour voir le détail d\'un référentiel.</span></p>';
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_niveau[$DB_ROW['niveau_id']] = html($DB_ROW['niveau_nom']);
    }
    // On récupère la liste des coordonnateurs responsables par matières
    $DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_identite_coordonnateurs_par_matiere();
    if(!empty($DB_TAB))
    {
      foreach($DB_TAB as $DB_ROW)
      {
        $tab_matiere[$DB_ROW['matiere_id']]['coord'] = str_replace('][','<br />',html($DB_ROW['coord_liste']));
      }
    }
    // On récupère la liste des référentiels par matière et niveau
    $tab_partage = array('oui'=>'<img title="Référentiel partagé sur le serveur communautaire (MAJ le ◄DATE►)." alt="" src="./_img/etat/partage_oui.gif" />','non'=>'<img title="Référentiel non partagé avec la communauté (choix du ◄DATE►)." alt="" src="./_img/etat/partage_non.gif" />','bof'=>'<img title="Référentiel dont le partage est sans intérêt (pas novateur)." alt="" src="./_img/etat/partage_non.gif" />','hs'=>'<img title="Référentiel dont le partage est sans objet (matière spécifique)." alt="" src="./_img/etat/partage_non.gif" />');
    $DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_referentiels_infos_details_matieres_niveaux();
    if(!empty($DB_TAB))
    {
      foreach($DB_TAB as $DB_ROW)
      {
        // Définition de $methode_calcul_texte
        $texte_retroactif = ($DB_ROW['referentiel_calcul_retroactif']=='non') ? '(sur la période)' : '(rétroactivement)' ;
        if($DB_ROW['referentiel_calcul_limite']==1)  // si une seule saisie prise en compte
        {
          $methode_calcul_texte = 'Seule la dernière saisie compte '.$texte_retroactif.'.';
        }
        elseif($DB_ROW['referentiel_calcul_methode']=='classique')  // si moyenne classique
        {
          $methode_calcul_texte = ($DB_ROW['referentiel_calcul_limite']==0) ? 'Moyenne de toutes les saisies '.$texte_retroactif.'.' : 'Moyenne des '.$DB_ROW['referentiel_calcul_limite'].' dernières saisies '.$texte_retroactif.'.';
        }
        elseif(in_array($DB_ROW['referentiel_calcul_methode'],array('geometrique','arithmetique')))  // si moyenne geometrique | arithmetique
        {
          $seize = (($DB_ROW['referentiel_calcul_methode']=='geometrique')&&($DB_ROW['referentiel_calcul_limite']==5)) ? 1 : 0 ;
          $coefs = ($DB_ROW['referentiel_calcul_methode']=='arithmetique') ? substr('1/2/3/4/5/6/7/8/9/',0,2*$DB_ROW['referentiel_calcul_limite']-19) : substr('1/2/4/8/16/',0,2*$DB_ROW['referentiel_calcul_limite']-12+$seize) ;
          $methode_calcul_texte = 'Les '.$DB_ROW['referentiel_calcul_limite'].' dernières saisies &times;'.$coefs.' '.$texte_retroactif.'.';
        }
        elseif($DB_ROW['referentiel_calcul_methode']=='bestof1')  // si meilleure note
        {
          $methode_calcul_texte = ($DB_ROW['referentiel_calcul_limite']==0) ? 'Seule la meilleure saisie compte '.$texte_retroactif.'.' : 'Meilleure des '.$DB_ROW['referentiel_calcul_limite'].' dernières saisies '.$texte_retroactif.'.';
        }
        elseif(in_array($DB_ROW['referentiel_calcul_methode'],array('bestof2','bestof3')))  // si 2 | 3 meilleures notes
        {
          $nb_best = (int)substr($DB_ROW['referentiel_calcul_methode'],-1);
          $methode_calcul_texte = ($DB_ROW['referentiel_calcul_limite']==0) ? 'Moyenne des '.$nb_best.' meilleures saisies '.$texte_retroactif.'.' : 'Moyenne des '.$nb_best.' meilleures saisies parmi les '.$DB_ROW['referentiel_calcul_limite'].' dernières '.$texte_retroactif.'.';
        }
        $tab_colonne[$DB_ROW['matiere_id']][$DB_ROW['niveau_id']] = '<td class="hc">'.str_replace('◄DATE►',Html::date($DB_ROW['referentiel_partage_date']),$tab_partage[$DB_ROW['referentiel_partage_etat']]).'</td>'.'<td>'.$methode_calcul_texte.'</td>';
      }
    }
    // On construit et affiche le tableau résultant
    $affichage = '<table class="vm_nug"><thead><tr><th>Matière</th><th>Nb</th><th>Coordonnateur(s)</th><th>Niveau</th><th>Partage</th><th>Méthode de calcul</th><th class="nu"></th></tr></thead><tbody>'."\r\n";
    $infobulle = '<img src="./_img/bulle_aide.png" alt="" title="Nombre maximal de demandes d\'évaluations simultanées autorisées pour un élève." />';
    foreach($tab_matiere as $matiere_id => $tab)
    {
      $rowspan = (isset($tab_colonne[$matiere_id])) ? count($tab_colonne[$matiere_id]) : 1 ;
      $matiere_nom   = $tab['nom'];
      $matiere_nb    = $tab['nb_demandes'].' '.$infobulle;
      $matiere_coord = (isset($tab['coord'])) ? '>'.$tab['coord'] : ' class="br hc">Aucun.' ;
      $affichage .= '<tr><td colspan="7" class="nu">&nbsp;</td></tr>'."\r\n";
      $affichage .= '<tr><td rowspan="'.$rowspan.'">'.$matiere_nom.'</td><td rowspan="'.$rowspan.'">'.$matiere_nb.'</td><td rowspan="'.$rowspan.'"'.$matiere_coord.'</td>';
      $affichage_suite = FALSE;
      if(isset($tab_colonne[$matiere_id]))
      {
        foreach($tab_colonne[$matiere_id] as $niveau_id => $referentiel_info)
        {
          $ids = 'ids_'.$matiere_id.'_'.$niveau_id;
          $colonnes = $referentiel_info.'<td class="nu" id="'.$ids.'"><q class="voir" title="Voir le détail de ce référentiel."></q></td>' ;
          if($affichage_suite===FALSE)
          {
            $affichage .= '<td>'.$tab_niveau[$niveau_id].'</td>'.$colonnes;
            $affichage_suite = '';
          }
          else
          {
            $affichage_suite .= '<tr><td>'.$tab_niveau[$niveau_id].'</td>'.$colonnes.'</tr>'."\r\n";
          }
        }
        $affichage .= '</tr>'."\r\n".$affichage_suite;
      }
      else
      {
        $affichage .= '<td class="br hc">---</td>'.'<td class="br hc">---</td><td class="br hc">---</td>'.'<td class="nu"></td>'.'</tr>'."\r\n";
      }
    }
    $affichage .= '</tbody></table>'."\r\n";
    echo $affichage;
  }
}
?>

</form>