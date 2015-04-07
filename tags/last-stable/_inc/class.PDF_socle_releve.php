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

// Ces méthodes ne concernent que la mise en page d'un relevé des validations du socle

class PDF_socle_releve extends PDF
{

  public function initialiser( $test_affichage_Pourcentage , $test_affichage_Validation )
  {
    $this->pourcentage_largeur = 25; // valeur fixe
    $this->validation_largeur  = 15; // valeur fixe
    $this->retrait_pourcentage = ( $test_affichage_Pourcentage ) ? $this->pourcentage_largeur : 0;
    $retrait_validation        = ( $test_affichage_Validation )  ? $this->validation_largeur  : 0;
    $this->item_largeur        = $this->page_largeur_moins_marges - $this->retrait_pourcentage - $retrait_validation;
    $this->section_largeur     = $this->item_largeur;
    $this->pilier_largeur      = $this->section_largeur - $retrait_validation;
    $this->SetMargins( $this->marge_gauche , $this->marge_haut , $this->marge_droite );
    $this->SetAutoPageBreak(FALSE);
  }

  private function premiere_page()
  {
    $this->AddPage($this->orientation , 'A4');
    $this->page_numero_first = $this->page;
    $this->choisir_couleur_texte('gris_fonce');
    $this->SetFont('Arial' , 'B' , 7);
    $this->Cell( $this->page_largeur_moins_marges , 4 /*ligne_hauteur*/ , To::pdf('Page 1/'.$this->page_nombre_alias) , 0 /*bordure*/ , 1 /*br*/ , $this->page_nombre_alignement , FALSE /*fond*/ );
    $this->choisir_couleur_texte('noir');
    $this->SetXY( $this->marge_gauche , $this->marge_haut );
  }

  private function rappel_eleve_page()
  {
    $info_identite = ($this->eleve_id) ? ' - '.$this->eleve_nom.' '.$this->eleve_prenom : '' ;
    $this->AddPage($this->orientation , 'A4');
    $page_numero = $this->page - $this->page_numero_first + 1 ;
    $this->SetFont('Arial' , 'B' , 7);
    $this->choisir_couleur_texte('gris_fonce');
    $this->Cell( $this->page_largeur_moins_marges , $this->lignes_hauteur , To::pdf($this->doc_titre.$info_identite.' - Page '.$page_numero.'/'.$this->page_nombre_alias) , 0 /*bordure*/ , 1 /*br*/ , $this->page_nombre_alignement , FALSE /*fond*/ );
    $this->choisir_couleur_texte('noir');
    $this->SetXY( $this->marge_gauche+$this->retrait_pourcentage , $this->GetY()+1 );
  }

  public function entete( $tab_infos_entete , $break , $eleve_id , $eleve_nom , $eleve_prenom , $eleve_INE , $eleve_nb_lignes )
  {
    $this->eleve_id     = $eleve_id;
    $this->eleve_nom    = $eleve_nom;
    $this->eleve_prenom = $eleve_prenom;
    // On prend une nouvelle page PDF pour chaque élève en cas d'affichage d'un palier avec tous les piliers ; pour un seul pilier, on étudie la place restante... tout en forçant une nouvelle page pour le 1er élève
    if( ($break==FALSE) || ($this->GetY()==0) )
    {
      $this->premiere_page();
      if($break==FALSE)
      {
        if($this->officiel)
        {
          // Ecrire l'en-tête (qui ne dépend pas de la taille de la police calculée ensuite) et récupérer la place requise par cet en-tête.
          list( $tab_etabl_coords , $tab_etabl_logo , $etabl_coords__bloc_hauteur , $tab_bloc_titres , $tab_adresse , $tag_date_heure_initiales , $eleve_genre , $date_naissance ) = $tab_infos_entete;
          $this->doc_titre = $tab_bloc_titres[0].' - '.$tab_bloc_titres[1];
          // Bloc adresse en positionnement contraint
          if( (is_array($tab_adresse)) && ($_SESSION['OFFICIEL']['INFOS_RESPONSABLES']=='oui_force') )
          {
            list( $bloc_droite_hauteur , $bloc_gauche_largeur_restante ) = $this->officiel_bloc_adresse_position_contrainte_et_pliures($tab_adresse);
            $this->SetXY( $this->marge_gauche , $this->marge_haut );
          }
          // Bloc établissement
          $bloc_etabl_largeur = (isset($bloc_gauche_largeur_restante)) ? $bloc_gauche_largeur_restante : 80 ;
          $bloc_etabl_hauteur = $this->officiel_bloc_etablissement( $tab_etabl_coords , $tab_etabl_logo , $bloc_etabl_largeur );
          // Bloc titres
          $alerte_archive = (($tab_adresse==='archive')&&($_SESSION['OFFICIEL']['ARCHIVE_AJOUT_MESSAGE_COPIE'])) ? TRUE : FALSE ;
          if( (is_array($tab_adresse)) && ($_SESSION['OFFICIEL']['INFOS_RESPONSABLES']=='oui_force') )
          {
            // En dessous du bloc établissement
            $bloc_titre_largeur = $bloc_etabl_largeur ;
            $this->SetXY( $this->marge_gauche , $this->GetY() + 2 );
            $bloc_titre_hauteur = $this->officiel_bloc_titres( $tab_bloc_titres , $alerte_archive , $bloc_titre_largeur );
            $bloc_gauche_hauteur = $bloc_etabl_hauteur + 2 + $bloc_titre_hauteur + 2 ;
          }
          else
          {
          // En haut à droite, modulo la place pour le texte indiquant le nombre de pages
            $bloc_titre_largeur = 100;
            $this->SetXY( $this->page_largeur-$this->marge_droite-$bloc_titre_largeur , $this->marge_haut+4 );
            $bloc_titre_hauteur = $this->officiel_bloc_titres( $tab_bloc_titres , $alerte_archive , $bloc_titre_largeur+4 );
            $bloc_gauche_hauteur = $bloc_etabl_hauteur ;
            $bloc_droite_hauteur = $bloc_titre_hauteur ; // temporaire, au cas où il n'y aurait pas d'adresse à ajouter
          }
          // Date de naissance + Tag date heure initiales (sous le bloc titres dans toutes les situations)
          $this->officiel_ligne_tag( $eleve_genre , $date_naissance , $eleve_INE , $tag_date_heure_initiales , $bloc_titre_largeur );
          // Bloc adresse en positionnement libre
          if( (is_array($tab_adresse)) && ($_SESSION['OFFICIEL']['INFOS_RESPONSABLES']=='oui_libre') )
          {
            $bloc_adresse_largeur = $bloc_titre_largeur;
            $this->SetXY( $this->page_largeur-$this->marge_droite-$bloc_adresse_largeur , $this->marge_haut+$bloc_titre_hauteur+2 );
            $bloc_adresse_hauteur = $this->officiel_bloc_adresse_position_libre($tab_adresse,$bloc_adresse_largeur);
            $bloc_droite_hauteur = $bloc_titre_hauteur + $bloc_adresse_hauteur ;
          }
          $hauteur_entete = max($bloc_gauche_hauteur,$bloc_droite_hauteur);
        }
        else
        {
          $hauteur_entete = 3*4.5 ; // HG L1 intitulé L2 palier-pilier ; HD L1 structure L2 élève
        }
        // On optimise la hauteur de ligne pour limiter le nombre de pages si possible dans le cas d'un palier avec tous les piliers.
        $hauteur_ligne_maximale   = 5;
        $hauteur_dispo_par_page   = $this->page_hauteur_moins_marges ;
        $hauteur_ligne_moyenne    = 4.5;
        $lignes_nb                = ( $hauteur_entete / 4.5 ) + $eleve_nb_lignes + ($this->legende*2) + 2 ; // entete + lignes dont résumés + légendes + marge
        $lignes_nb_moyen_par_page = $hauteur_dispo_par_page / $hauteur_ligne_moyenne ;
        $nb_page_moyen            = $lignes_nb / $lignes_nb_moyen_par_page ;
        /*
        $nb_page_pleines          = floor($nb_page_moyen) ;
        $prop_last_page           = $nb_page_moyen - $nb_page_pleines ;
        if( ($nb_page_pleines==1) && ($prop_last_page<0.25) )
        {
          $nb_page_calcule = $nb_page_pleines - 0.1 ;
        }
        elseif( ($nb_page_pleines>1) && ($prop_last_page<0.5) )
        {
          $nb_page_calcule = $nb_page_pleines - 0.2 ;
        }
        elseif( ($nb_page_pleines>1) && ($prop_last_page>0.8) )
        {
          $nb_page_calcule = $nb_page_pleines + 0.8 ;
        }
        else
        {
          $nb_page_calcule = max(0.9,$nb_page_moyen) ;
        }
        */
        $nb_page_calcule = $nb_page_moyen*0.9; // Pour tenter de compenser la place perdue à cause des blocs par pilier
        $lignes_nb_calcule_par_page = $lignes_nb / $nb_page_calcule ;
        $hauteur_ligne_calcule      = $hauteur_dispo_par_page / $lignes_nb_calcule_par_page ;
        $this->lignes_hauteur = floor($hauteur_ligne_calcule*10)/10 ; // round($hauteur_ligne_calcule,1,PHP_ROUND_HALF_DOWN) à partir de PHP 5.3
        $this->lignes_hauteur = min ( $this->lignes_hauteur , $hauteur_ligne_maximale ) ;
        $this->cases_hauteur  = $this->lignes_hauteur;
        // On s'occupe aussi maintenant de la taille de la police
        $this->taille_police  = $this->lignes_hauteur * 1.5 ; // 4mm de hauteur par ligne donne une taille de 6
      }
      else
      {
        $this->cases_hauteur  = 4.5;
        $this->lignes_hauteur = $this->cases_hauteur;
        $this->taille_police  = 6;
      }
      if($this->officiel)
      {
        $this->SetXY( $this->marge_gauche , $this->marge_haut+$hauteur_entete );
      }
      else
      {
        $this->SetXY( $this->marge_gauche , $this->marge_haut );
      }
    }
    else
    {
      $hauteur_requise  = $this->cases_hauteur * ($eleve_nb_lignes + 2 + 0.5 + 1); // titres + marge + interligne
      $hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
      if($hauteur_requise > $hauteur_restante)
      {
        $this->premiere_page();
      }
      else
      {
        $this->SetXY( $this->marge_gauche , $this->GetY()+$this->cases_hauteur );
      }
    }
    if(!$this->officiel)
    {
      list( $titre , $palier_nom ) = $tab_infos_entete;
      $this->doc_titre = $titre.' - '.$palier_nom;
      // Intitulé
      $this->SetFont('Arial' , 'B' , $this->taille_police*1.5);
      $this->Cell( $this->page_largeur-$this->marge_droite-75 , $this->cases_hauteur , To::pdf($titre)      , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
      $this->Cell( $this->page_largeur-$this->marge_droite-75 , $this->cases_hauteur , To::pdf($palier_nom) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
      // Structure + Nom / prénom ; on met le document au nom de l'élève ou on établit un document générique
      if(!$this->eleve_id)
      {
        $this->choisir_couleur_trait('gris_moyen');
        $this->SetLineWidth(0.1);
        $this->Line( $this->page_largeur-$this->marge_droite-75 , $this->marge_haut+2*$this->cases_hauteur , $this->page_largeur-$this->marge_droite , $this->marge_haut+2*$this->cases_hauteur );
        $this->choisir_couleur_trait('noir');
      }
      $this->SetFont('Arial' , 'B' , $this->taille_police*1.5);
      $this->SetXY( $this->page_largeur-$this->marge_droite-50 , max($this->marge_haut,$this->GetY()-2*$this->cases_hauteur) ); // Soit c'est une nouvelle page, soit il ne faut pas se mettre en haut de la page
      $this->Cell( 50 , $this->cases_hauteur , To::pdf($_SESSION['ETABLISSEMENT']['DENOMINATION']) , 0 /*bordure*/ , 2 /*br*/ , 'R' /*alignement*/ , FALSE /*fond*/ );
      $this->Cell( 50 , $this->cases_hauteur , To::pdf($this->eleve_nom.' '.$this->eleve_prenom)   , 0 /*bordure*/ , 2 /*br*/ , 'R' /*alignement*/ , FALSE /*fond*/ );
    }
  }

  public function pilier( $pilier_nom , $pilier_nb_lignes , $test_affichage_Validation , $tab_pilier_validation , $drapeau_langue )
  {
    $this->SetXY( $this->marge_gauche+$this->retrait_pourcentage , $this->GetY() + $this->cases_hauteur );
    $hauteur_requise  = $this->cases_hauteur * $pilier_nb_lignes;
    $hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
    if($hauteur_requise > $hauteur_restante)
    {
      // Prendre une nouvelle page si ça ne rentre pas, avec recopie de l'identité de l'élève
      $this->rappel_eleve_page();
    }
    $this->SetFont('Arial' , 'B' , $this->taille_police*1.25);
    $couleur_fond = ($this->fond) ? 'gris_moyen' : 'gris_clair' ; // Forcer a minima un fond clair sinon c'est peu lisible
    $this->choisir_couleur_fond($couleur_fond);
    $br = $test_affichage_Validation ? 0 : 1 ;
    $this->CellFit( $this->pilier_largeur , $this->cases_hauteur , To::pdf($pilier_nom) , 1 , $br , 'L' , TRUE /*fond*/ ); // fond forcé
    if($test_affichage_Validation)
    {
      $this->afficher_etat_validation('B',$tab_pilier_validation);
    }
    if($drapeau_langue)
    {
      $this->Image('./_img/drapeau/'.$drapeau_langue.'.gif',$this->GetX()+$this->pilier_largeur-$this->cases_hauteur-0.5,$this->GetY()-$this->cases_hauteur,$this->cases_hauteur,$this->cases_hauteur,'GIF');
    }
  }

  public function section($section_nom)
  {
    $this->SetXY($this->marge_gauche+$this->retrait_pourcentage , $this->GetY());
    $this->SetFont('Arial' , 'B' , $this->taille_police);
    $couleur_fond = ($this->fond) ? 'gris_moyen' : 'gris_clair' ; // Forcer a minima un fond clair sinon c'est peu lisible
    $this->choisir_couleur_fond($couleur_fond);
    $this->CellFit( $this->section_largeur , $this->cases_hauteur , To::pdf($section_nom) , 1 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , TRUE /*fond*/ ); // fond forcé
  }

  public function item( $item_nom , $test_affichage_Pourcentage , $tab_item_pourcentage , $test_affichage_Validation , $tab_item_validation )
  {
    // Case pourcentage
    if($test_affichage_Pourcentage)
    {
      $this->afficher_pourcentage_acquis( '' , $tab_item_pourcentage , 'detail' /*affich*/ );
    }
    // Case intitulé
    $this->choisir_couleur_fond('gris_clair');
    $this->SetFont('Arial' , '' , $this->taille_police);
    $br = $test_affichage_Validation ? 0 : 1 ;
    $this->CellFit( $this->item_largeur , $this->cases_hauteur , To::pdf($item_nom) , 1 /*bordure*/ , $br , 'L' /*alignement*/ , $this->fond );
    // Case validation
    if($test_affichage_Validation)
    {
      $this->afficher_etat_validation('',$tab_item_validation);
    }
  }

  public function appreciation_rubrique($tab_saisie)
  {
    $this->SetXY( $this->marge_gauche + $this->retrait_pourcentage , $this->GetY() );
    $this->officiel_bloc_appreciation_intermediaire( $tab_saisie , $this->item_largeur , $this->cases_hauteur , 'socle' , $_SESSION['OFFICIEL']['SOCLE_APPRECIATION_RUBRIQUE_LONGUEUR'] );
  }

  public function appreciation_generale( $prof_id , $tab_infos , $tab_image_tampon_signature , $nb_lignes_appreciation_generale_avec_intitule , $nb_lignes_assiduite_et_pp_et_message_et_legende )
  {
    $this->SetXY( $this->marge_gauche + $this->retrait_pourcentage , $this->GetY() + $this->cases_hauteur );
    $hauteur_requise = $this->lignes_hauteur * ( $nb_lignes_appreciation_generale_avec_intitule + $nb_lignes_assiduite_et_pp_et_message_et_legende ) ;
    $hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
    if($hauteur_requise > $hauteur_restante)
    {
      // Prendre une nouvelle page si ça ne rentre pas, avec recopie de l'identité de l'élève
      $this->rappel_eleve_page();
    }
    $this->officiel_bloc_appreciation_generale( $prof_id , $tab_infos , $tab_image_tampon_signature , $nb_lignes_appreciation_generale_avec_intitule , $this->item_largeur , $this->cases_hauteur , NULL /*moyenne_generale_eleve*/ , NULL /*moyenne_generale_classe*/ );
  }

  public function legende( $test_affichage_Pourcentage , $test_affichage_Validation )
  {
    if($test_affichage_Pourcentage)
    {
      $ordonnee = ($test_affichage_Validation) ? $this->page_hauteur - $this->marge_bas - $this->lignes_hauteur*3 : $this->page_hauteur - $this->marge_bas - $this->lignes_hauteur*1 ;
      $this->afficher_legende( 'pourcentage_acquis' /*type_legende*/ , $ordonnee     /*ordonnée*/ );
      $this->afficher_legende( 'etat_acquisition'   /*type_legende*/ , $this->GetY() /*ordonnée*/ , TRUE /*force_nb*/ );
    }
    if($test_affichage_Validation)
    {
      $ordonnee = $this->page_hauteur - $this->marge_bas - $this->lignes_hauteur*1 ;
      $this->afficher_legende( 'etat_validation' /*type_legende*/ , $ordonnee /*ordonnée*/ );
    }
  }

}
?>