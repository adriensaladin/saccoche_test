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
 
// Extension de classe qui étend PDF

// Ces méthodes ne concernent que la mise en page d'un tableau de synthèse des validations du socle

class PDF_socle_synthese extends PDF
{

  public function initialiser( $titre_info , $groupe_nom , $palier_nom , $eleves_nb , $items_nb , $piliers_nb )
  {
    $this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droite);
    $this->AddPage($this->orientation , 'A4');
    $this->SetAutoPageBreak(TRUE);
    $this->eleve_largeur  = 40; // valeur fixe
    $this->cases_largeur  = ($this->page_largeur_moins_marges - $this->eleve_largeur - $piliers_nb) / ($items_nb); // - intercolonne de 1 * nb piliers
    $this->cases_hauteur  = ($this->page_hauteur_moins_marges - $this->taille_police - $eleves_nb - $this->legende*5) / ($eleves_nb+1); // - titre de 5 - ( interligne de 1 * nb élèves ) - legende
    $this->cases_hauteur  = min($this->cases_hauteur,10);
    $this->lignes_hauteur = $this->cases_hauteur;
    $this->taille_police = 8;
    // Intitulés
    $this->SetFont('Arial' , 'B' , $this->taille_police*1.5);
    $this->Cell(0 , $this->taille_police , To::pdf('Synthèse de maîtrise du socle : '.$titre_info.' - '.$groupe_nom.' - '.$palier_nom) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
  }

  public function entete($tab_pilier)
  {
    $this->SetFont('Arial' , 'B' , $this->taille_police);
    $this->SetXY($this->marge_gauche+$this->eleve_largeur,$this->marge_haut+$this->taille_police);
    $this->choisir_couleur_fond('gris_fonce');
    foreach($tab_pilier as $tab)
    {
      extract($tab);  // $pilier_ref $pilier_nom $pilier_nb_entrees
      $texte = ($pilier_nb_entrees>10) ? 'Compétence ' : 'Comp. ' ;
      $this->SetX( $this->GetX()+1 );
      $this->Cell($pilier_nb_entrees*$this->cases_largeur , $this->cases_hauteur , To::pdf($texte.$pilier_ref) , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , $this->fond );
    }
    // positionnement pour la suite
    $this->SetFont('Arial' , '' , $this->taille_police);
    $this->SetXY( $this->marge_gauche , $this->GetY()+$this->cases_hauteur+1 );
  }

  public function validation_eleve( $eleve_id , $eleve_nom , $eleve_prenom , $tab_user_pilier , $tab_user_entree , $tab_pilier , $tab_socle , $drapeau_langue )
  {
    $this->choisir_couleur_fond('gris_moyen');
    $this->CellFit( $this->eleve_largeur , $this->cases_hauteur , To::pdf($eleve_nom.' '.$eleve_prenom) , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , $this->fond );
    if($drapeau_langue)
    {
      $taille_image = min($this->cases_hauteur,5);
      $this->Image('./_img/drapeau/'.$drapeau_langue.'.gif',$this->GetX()-$taille_image-0.5,$this->GetY(),$taille_image,$taille_image,'GIF');
    }
    $demi_hauteur = $this->cases_hauteur / 2 ;
    // - - - - -
    // Indication des compétences validées
    // - - - - -
    // Pour chaque pilier...
    foreach($tab_pilier as $pilier_id => $tab)
    {
      extract($tab);  // $pilier_ref $pilier_nom $pilier_nb_entrees
      $texte = ( ($this->couleur=='non') && ($tab_user_pilier[$eleve_id][$pilier_id]['etat']==2) ) ? '-' : '' ;
      $this->SetX( $this->GetX()+1 );
      $this->choisir_couleur_fond($this->tab_choix_couleur[$this->couleur]['v'.$tab_user_pilier[$eleve_id][$pilier_id]['etat']]);
      $this->Cell($pilier_nb_entrees*$this->cases_largeur , $demi_hauteur , $texte , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
    }
    // positionnement pour la suite
    $this->SetXY( $this->marge_gauche+$this->eleve_largeur , $this->GetY()+$demi_hauteur );
    // - - - - -
    // Indication des items validés
    // - - - - -
    // Pour chaque entrée du socle...
    foreach($tab_socle as $pilier_id => $tab)
    {
      $this->SetX( $this->GetX()+1 );
      foreach($tab as $socle_id => $socle_nom)
      {
        $texte = ( ($this->couleur=='non') && ($tab_user_pilier[$eleve_id][$pilier_id]['etat']!=1) && ($tab_user_entree[$eleve_id][$socle_id]['etat']==2) ) ? '-' : '' ;
        $couleur = ( ($tab_user_pilier[$eleve_id][$pilier_id]['etat']==1) && ($tab_user_entree[$eleve_id][$socle_id]['etat']==2) && (!$_SESSION['USER_DALTONISME']) ) ? 'gris_clair' : $this->tab_choix_couleur[$this->couleur]['v'.$tab_user_entree[$eleve_id][$socle_id]['etat']] ;
        $this->choisir_couleur_fond($couleur);
        $this->Cell( $this->cases_largeur , $demi_hauteur , $texte , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
      }
    }
    // positionnement pour la suite
    $this->SetXY( $this->marge_gauche , $this->GetY()+$demi_hauteur+1 );
  }

  public function pourcentage_eleve( $eleve_id , $eleve_nom , $eleve_prenom , $tab_score_socle_eleve , $tab_socle , $drapeau_langue )
  {
    $this->pourcentage_largeur = $this->cases_largeur;
    $this->choisir_couleur_fond('gris_moyen');
    $this->SetFont('Arial' , '' , $this->taille_police);
    $this->CellFit( $this->eleve_largeur , $this->cases_hauteur , To::pdf($eleve_nom.' '.$eleve_prenom) , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , $this->fond );
    if($drapeau_langue)
    {
      $taille_image = min($this->cases_hauteur,5);
      $this->Image('./_img/drapeau/'.$drapeau_langue.'.gif',$this->GetX()-$taille_image-0.5,$this->GetY(),$taille_image,$taille_image,'GIF');
    }
    // - - - - -
    // Indication des pourcentages
    // - - - - -
    // Pour chaque entrée du socle...
    foreach($tab_socle as $pilier_id => $tab)
    {
      $this->SetX( $this->GetX()+1 );
      foreach($tab as $socle_id => $socle_nom)
      {
        $this->afficher_pourcentage_acquis( '' , $tab_score_socle_eleve[$socle_id][$eleve_id] , 'pourcentage' /*affich*/ );
      }
    }
    // positionnement pour la suite
    $this->SetXY( $this->marge_gauche , $this->GetY()+$this->cases_hauteur+1 );
  }

  public function legende($type)
  {
    if($this->legende)
    {
      $ordonnee = $this->page_hauteur - $this->marge_bas - $this->lignes_hauteur*0.5 ;
      $type_legende = ($type=='pourcentage') ? 'pourcentage_acquis' : 'etat_validation' ;
      $this->afficher_legende( $type_legende , $ordonnee );
    }
  }

}
?>