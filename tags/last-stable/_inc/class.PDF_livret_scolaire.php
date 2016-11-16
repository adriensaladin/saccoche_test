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

// Ces méthodes ne concernent que la mise en page d'un bilan d'items

class PDF_livret_scolaire extends PDF
{

  private $PAGE_REF            = '';
  private $BILAN_TYPE_ETABL    = '';
  private $PAGE_COLONNE        = '';
  private $PAGE_MOYENNE_CLASSE = '';
  private $tab_saisie_initialisation  = array();
  private $app_rubrique_nb_caract_max =  600;
  private $app_bilan_nb_caract_max    = 1000;
  private $nb_caract_max_par_ligne   = 150;
  private $nb_caract_max_par_colonne = 50;

  private $tab_livret_damier_cases = array(
    0 => array(
      0 => array('txt'=>'2'  ,'cp'=>'bleu_fonce','ce1'=>'bleu_fonce','ce2'=>'bleu_fonce','cycle2'=>'bleu_fonce'),
      1 => array('txt'=>'CP' ,'cp'=>'vert_fonce','ce1'=>'vert_clair','ce2'=>'vert_clair','cycle2'=>'vert_fonce'),
      2 => array('txt'=>'CE1','cp'=>'vert_clair','ce1'=>'vert_fonce','ce2'=>'vert_clair','cycle2'=>'vert_fonce'),
      3 => array('txt'=>'CE2','cp'=>'vert_clair','ce1'=>'vert_clair','ce2'=>'vert_fonce','cycle2'=>'vert_fonce'),
    ),
    1 => array(
      0 => array('txt'=>'3'  ,'cm1'=>'bleu_fonce','cm2'=>'bleu_fonce','6e'=>'bleu_fonce','cycle3'=>'bleu_fonce'),
      1 => array('txt'=>'CM1','cm1'=>'vert_fonce','cm2'=>'vert_clair','6e'=>'vert_clair','cycle3'=>'vert_fonce'),
      2 => array('txt'=>'CM2','cm1'=>'vert_clair','cm2'=>'vert_fonce','6e'=>'vert_clair','cycle3'=>'vert_fonce'),
      3 => array('txt'=>'6e' ,'cm1'=>'vert_clair','cm2'=>'vert_clair','6e'=>'vert_fonce','cycle3'=>'vert_fonce'),
    ),
    2 => array(
      0 => array('txt'=>'4'  ,'5e'=>'bleu_fonce','4e'=>'bleu_fonce','3e'=>'bleu_fonce','cycle4'=>'bleu_fonce'),
      1 => array('txt'=>'5e' ,'5e'=>'vert_fonce','4e'=>'vert_clair','3e'=>'vert_clair','cycle4'=>'vert_fonce'),
      2 => array('txt'=>'4e' ,'5e'=>'vert_clair','4e'=>'vert_fonce','3e'=>'vert_clair','cycle4'=>'vert_fonce'),
      3 => array('txt'=>'3e' ,'5e'=>'vert_clair','4e'=>'vert_clair','3e'=>'vert_fonce','cycle4'=>'vert_fonce'),
    ),
  );

  public function initialiser( $PAGE_REF , $BILAN_TYPE_ETABL , $PAGE_COLONNE , $PAGE_MOYENNE_CLASSE , $app_rubrique_nb_caract_max , $app_bilan_nb_caract_max , $tab_saisie_initialisation )
  {
    $this->PAGE_REF            = $PAGE_REF;
    $this->BILAN_TYPE_ETABL    = $BILAN_TYPE_ETABL;
    $this->PAGE_COLONNE        = $PAGE_COLONNE;
    $this->PAGE_MOYENNE_CLASSE = $PAGE_MOYENNE_CLASSE;
    $this->SetMargins( $this->marge_gauche , $this->marge_haut , $this->marge_droite );
    $this->SetAutoPageBreak(FALSE);
    $this->app_rubrique_nb_caract_max = $app_rubrique_nb_caract_max;
    $this->app_bilan_nb_caract_max    = $app_bilan_nb_caract_max;
    $this->tab_saisie_initialisation  = $tab_saisie_initialisation;
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
    // Légende éventuelle du positionnement, si pas déjà fait
    if( in_array($this->PAGE_COLONNE,array('objectif','position')) && (!$this->legende_deja_affichee) && ($this->GetY()+$this->lignes_hauteur<$this->page_hauteur-$this->marge_bas) )
    {
     $tab_txt = array(
        'objectif' => '[*] Positionnement par objectifs d’apprentissage :  1 = Non atteints    2 = Partiellement atteints    3 = Atteints    4 = Dépassés',
        'position' => '[*] 1 est le moins bon ; 4 est le meilleur',
      );
      $this->CellFit( $this->page_largeur_moins_marges , $this->lignes_hauteur , To::pdf($tab_txt[$this->PAGE_COLONNE]) , 0 /*bordure*/ , 0 /*br*/ , 'R' /*alignement*/ , FALSE /*fond*/ );
      $this->legende_deja_affichee = TRUE;
    }
    // Saut de page, si pas déjà fait
    if( $this->page == $this->page_numero_first )
    {
      $this->AddPage($this->orientation , 'A4');
      $page_numero = $this->page - $this->page_numero_first + 1 ;
      $this->SetFont('Arial' , 'B' , $this->taille_police);
      $this->choisir_couleur_texte('gris_fonce');
      $this->Cell( $this->page_largeur_moins_marges , $this->lignes_hauteur , To::pdf($this->doc_titre.' - '.$this->eleve_nom.' '.$this->eleve_prenom.' - Page '.$page_numero.'/'.$this->page_nombre_alias) , 0 /*bordure*/ , 1 /*br*/ , $this->page_nombre_alignement , FALSE /*fond*/ );
      $this->choisir_couleur_texte('noir');
    }
  }

  // Une première ligne de blocs de 3cm de haut : Logo EN + Infos établ + Logo niveau livret + Logo établ
  private function entete_blocs_premiere_ligne( $hauteur_blocs_ligne1 , $tab_menesr_logo , $tab_etabl_coords , $tab_etabl_logo )
  {
    // largeur (mm) : 5 marge + 20 logo + 2.5 espace + 72.5 infos établ + 2.5 espace + 40 logo livret + 2.5 espace + 60 logo établ + 5 marge = 210
    $largeur_logo_en     = 20; // non modifiable
    $largeur_info_etabl  = 72.5;
    $largeur_logo_livret = 40; // non modifiable
    $largeur_logo_etabl  = 60;
    $largeur_espace      = 2.5;
    // Logo EN : 542 x 791 donc 2,05 cm x 3cm
    $memoX = $this->GetX();
    $memoY = $this->GetY();
    $largeur_logo = $this->afficher_image( $largeur_logo_en+5 , $hauteur_blocs_ligne1 , $tab_menesr_logo , 'logo_seul' );
    $this->SetXY( $memoX+$largeur_logo_en+$largeur_espace , $memoY );
    // Infos établ
    $memoX = $this->GetX();
    $nb_etabl_coords = count($tab_etabl_coords);
    if($nb_etabl_coords)
    {
      foreach($tab_etabl_coords as $key => $ligne_etabl)
      {
        $taille_police = ($key=='denomination') ? 12 : 9 ;
        $ligne_hauteur = $taille_police*0.4 ; // Au maximum (pour 1 titre + 7 lignes) on a bien en tout 12*0.4 + 9*0.4*7 = 30
        $this->SetFont('Arial' , '' , $taille_police);
        $this->CellFit( $largeur_info_etabl /*bloc_largeur*/ , $ligne_hauteur , To::pdf($ligne_etabl) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
      }
    }
    $this->SetXY( $memoX+$largeur_info_etabl+$largeur_espace , $memoY );
    // Logo niveau livret
    $memoX = $this->GetX();
    $taille_case_livret_ext = $largeur_logo_livret / 4; // 10
    $taille_case_livret_int = $taille_case_livret_ext - 1; // 9
    $rayon = ($taille_case_livret_int - 2) / 2; // 3.5
    $this->SetFont('Arial' , 'B' , 10);
    $this->choisir_couleur_texte('blanc');
    $this->choisir_couleur_trait('blanc');
    foreach($this->tab_livret_damier_cases as $num_ligne => $tab_ligne)
    {
      foreach($tab_ligne as $num_colonne => $tab_case)
      {
        $this->SetXY( $memoX+$num_colonne*$taille_case_livret_ext , $memoY+$num_ligne*$taille_case_livret_ext );
        $couleur_fond = isset($tab_case[$this->PAGE_REF]) ? 'livret_'.$tab_case[$this->PAGE_REF] : 'livret_gris' ;
        $this->choisir_couleur_fond($couleur_fond);
        $this->Rect( $this->GetX() , $this->GetY() , $taille_case_livret_int /*largeur*/ , $taille_case_livret_int /*hauteur*/ , 'F' /*fill*/ );
        if(!$num_colonne)
        {
          $this->Circle( $this->GetX()+1+$rayon , $this->GetY()+1+$rayon , $rayon /*rayon*/ , 'D' /*draw*/ );
        }
        $this->CellFit( $taille_case_livret_int /*bloc_largeur*/ , $taille_case_livret_int+0.5 /*ligne_hauteur*/ , To::pdf($tab_case['txt']) , 0 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
      }
    }
    $this->choisir_couleur_texte('noir');
    $this->choisir_couleur_trait('noir');
    $this->SetXY( $memoX+$largeur_logo_livret+$largeur_espace , $memoY );
    // Logo établ
    $memoX = $this->GetX();
    if($tab_etabl_logo)
    {
      // on tâche de ne pas recouvrir "Page 1/2"
      $reduc_largeur = 12.5;
      $reduc_hauteur = 3.5;
      $ratio_image = $tab_etabl_logo['largeur'] / $tab_etabl_logo['hauteur'];
      $ratio_place_large = $largeur_logo_etabl / ($hauteur_blocs_ligne1-$reduc_hauteur);
      $ratio_place_haut  = ($largeur_logo_etabl-$reduc_largeur) / $hauteur_blocs_ligne1;
      if( abs($ratio_image-$ratio_place_large) < abs($ratio_image-$ratio_place_haut) )
      {
        // image plus large que haute par rapport à la place disponibles : on prend toute la largeur et un peu moins de hauteur
        $this->SetXY( $memoX , $memoY+$reduc_hauteur );
        $largeur_dispo = $largeur_logo_etabl;
        $hauteur_dispo = $hauteur_blocs_ligne1-$reduc_hauteur;
      }
      else
      {
        // image plus haute que large par rapport à la place disponibles : on prend toute la hauteur et un peu moins de largeur
        $largeur_dispo = $largeur_logo_etabl - $reduc_largeur;
        $hauteur_dispo = $hauteur_blocs_ligne1;
      }
      $largeur_logo = $this->afficher_image( $largeur_dispo , $hauteur_dispo , $tab_etabl_logo , 'logo_seul' );
    }
    // Repositionnement
    $this->SetXY( $this->marge_gauche , $this->marge_haut+$hauteur_blocs_ligne1 );
  }

  private function entete_bloc_adresse( $hauteur_blocs_ligne1 , $hauteur_blocs_ligne2 , $tab_adresse )
  {
    $hauteur_blocs_ligne1_plus_marge = $hauteur_blocs_ligne1+$this->marge_haut;
    if($this->SESSION['OFFICIEL']['INFOS_RESPONSABLES']=='oui_libre')
    {
      // On définit des positions optimales permettant d'effectuer les calculs comme dans le cas d'un positionnement imposé
      $this->SESSION['ENVELOPPE']['VERTICAL_HAUT']   = $hauteur_blocs_ligne1_plus_marge; // place déjà prise par la 1ère ligne de bloc (marge page comprise)
      $this->SESSION['ENVELOPPE']['VERTICAL_MILIEU'] = $hauteur_blocs_ligne2; // minimum esthétique et nécessaire pour le bloc titre à côté
      $this->SESSION['ENVELOPPE']['VERTICAL_BAS']    = 40; // complément pour une hauteur d'enveloppe cohérente (105 donc > 297/3)
      $this->SESSION['ENVELOPPE']['HORIZONTAL_GAUCHE'] = 125; // laisser pas mal de place pour les titres
      $this->SESSION['ENVELOPPE']['HORIZONTAL_DROITE'] =  10; // minimum esthétique
      $this->SESSION['ENVELOPPE']['HORIZONTAL_MILIEU'] =  80; // complément pour une largeur légèrement > à 210
    }
    $enveloppe_hauteur = $this->SESSION['ENVELOPPE']['VERTICAL_HAUT'] + $this->SESSION['ENVELOPPE']['VERTICAL_MILIEU'] + $this->SESSION['ENVELOPPE']['VERTICAL_BAS'] ;
    $enveloppe_largeur = $this->SESSION['ENVELOPPE']['HORIZONTAL_GAUCHE'] + $this->SESSION['ENVELOPPE']['HORIZONTAL_MILIEU'] + $this->SESSION['ENVELOPPE']['HORIZONTAL_DROITE'] ;
    $jeu_minimum    = 2 ;
    $jeu_vertical   = 1 ;
    $jeu_horizontal = $enveloppe_largeur - $this->page_largeur - $jeu_minimum ;
    // Déterminer et dessiner l'emplacement du bloc adresse
    $interieur_coin_hg_x = $this->SESSION['ENVELOPPE']['HORIZONTAL_GAUCHE'] ;
    $exterieur_coin_hg_x = $interieur_coin_hg_x - $jeu_horizontal ;
    $interieur_coin_bd_x = $this->page_largeur - $this->SESSION['ENVELOPPE']['HORIZONTAL_DROITE'] ;
    $exterieur_coin_bd_x = $interieur_coin_bd_x + $jeu_horizontal ;
    $exterieur_coin_hg_y = $hauteur_blocs_ligne1_plus_marge;
    $interieur_coin_hg_y = $exterieur_coin_hg_y + $jeu_vertical ;
    $interieur_coin_bd_y = $interieur_coin_hg_y + $this->SESSION['ENVELOPPE']['VERTICAL_MILIEU'] ;
    $exterieur_coin_bd_y = $interieur_coin_bd_y + $jeu_vertical ;
    $exterieur_largeur = $exterieur_coin_bd_x - $exterieur_coin_hg_x ;
    $exterieur_hauteur = $exterieur_coin_bd_y - $exterieur_coin_hg_y ;
    $interieur_largeur = $interieur_coin_bd_x - $interieur_coin_hg_x ;
    $interieur_hauteur = $interieur_coin_bd_y - $interieur_coin_hg_y ;
    if($this->SESSION['OFFICIEL']['INFOS_RESPONSABLES']=='oui_force')
    {
      $this->choisir_couleur_trait('gris_clair');
      $this->Rect( $exterieur_coin_hg_x , $exterieur_coin_hg_y , $exterieur_largeur , $exterieur_hauteur , 'D' /* DrawFill */ );
    }
    $this->choisir_couleur_trait('gris_moyen');
    $this->Rect( $interieur_coin_hg_x , $interieur_coin_hg_y , $interieur_largeur , $interieur_hauteur , 'D' /* DrawFill */ );
    $this->choisir_couleur_trait('noir');
    // Placer les marques des pliures
    if($this->SESSION['OFFICIEL']['INFOS_RESPONSABLES']=='oui_force')
    {
      $jeu_vertical += 1 ; // Le pliage est manuel donc imparfait et il y a l'épaisseur du papier ;)
      $longueur_tiret = 1; // <= 5
      $this->SetLineWidth(0.1);
      $ligne1_y = $interieur_coin_bd_y + $this->SESSION['ENVELOPPE']['VERTICAL_BAS'] - $jeu_vertical ;
      $ligne2_y = $ligne1_y + $enveloppe_hauteur - $jeu_vertical ;
      $this->Line( $this->marge_gauche-$longueur_tiret , $ligne1_y , $this->marge_gauche , $ligne1_y );
      $this->Line( $this->page_largeur-$this->marge_droite , $ligne1_y , $this->page_largeur-$this->marge_droite+$longueur_tiret , $ligne1_y );
      $this->Line( $this->marge_gauche-$longueur_tiret , $ligne2_y , $this->marge_gauche , $ligne2_y );
      $this->Line( $this->page_largeur-$this->marge_droite , $ligne2_y , $this->page_largeur-$this->marge_droite+$longueur_tiret , $ligne2_y );
    }
    // Affiner la position du contenu de l'adresse, et l'afficher
    $marge_suppl_x = $interieur_largeur*0.05;
    $marge_suppl_y = $interieur_hauteur*0.05;
    $interieur_largeur_reste = $interieur_largeur*0.8;
    $interieur_hauteur_reste = $interieur_hauteur*0.8;
    $lignes_adresse_nb = count($tab_adresse);
    $ligne_hauteur_reste = min( 4 , $interieur_hauteur_reste/$lignes_adresse_nb );
    $taille_police = $ligne_hauteur_reste*2.5 ;
    $marge_centrage_y = ( $interieur_hauteur_reste - $ligne_hauteur_reste*$lignes_adresse_nb ) / 2 ;
    $this->SetXY( $interieur_coin_hg_x+$marge_suppl_x , $interieur_coin_hg_y+$marge_suppl_y+$marge_centrage_y );
    $this->SetFont('Arial' , '' , $taille_police);
    foreach($tab_adresse as $ligne_adresse)
    {
      $this->CellFit( $interieur_largeur_reste , $ligne_hauteur_reste , To::pdf($ligne_adresse) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    }
    // Retourner les dimensions à considérer pour le bloc titre à placer à côté
    $bloc_adresse_hauteur = $exterieur_coin_bd_y - $hauteur_blocs_ligne1_plus_marge ;
    $bloc_gauche_largeur_restante = $exterieur_coin_hg_x - $this->marge_gauche - 2 ;
    return array( $bloc_adresse_hauteur , $bloc_gauche_largeur_restante );
  }

  private function entete_bloc_titres( $largeur_bloc_titre , $hauteur_blocs_ligne1 , $hauteur_blocs_ligne2 , $tab_bloc_titres )
  {
    $marge_haut_bloc_titre = max( 0 , ( $hauteur_blocs_ligne2 - 30 ) / 2 );
    $taille_police = 12 ;
    $bloc_hauteur = $hauteur_blocs_ligne2 - 2*$marge_haut_bloc_titre - 5 ;
    $ligne_hauteur = $bloc_hauteur/4.5 ; // 4 lignes + 2 quart-interlignes de marge
    $tab_bloc_titres[3] = $this->eleve_nom.' '.$this->eleve_prenom.' ('.$tab_bloc_titres[3].')';
    $this->choisir_couleur_fond('livret_bleu_clair');
    $this->SetXY( $this->marge_gauche , $this->marge_haut + $hauteur_blocs_ligne1 + $marge_haut_bloc_titre + 1 );
    $this->Rect( $this->GetX() , $this->GetY() , $largeur_bloc_titre , $bloc_hauteur , 'DF' /*DrawFill*/ );
    $this->SetXY( $this->GetX() , $this->GetY() + 0.25*$ligne_hauteur );
    foreach($tab_bloc_titres as $key => $ligne_titre)
    {
      $bold = ( ($key==0) || ($key==3) ) ? 'B' : '' ;
      $this->SetFont('Arial' , $bold , $taille_police);
      $this->CellFit( $largeur_bloc_titre , $ligne_hauteur , To::pdf($ligne_titre) , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
    }
    $this->SetXY( $this->GetX() , $this->GetY() + 0.25*$ligne_hauteur );
  }

  public function entete( $tab_infos_entete , $eleve_nom , $eleve_prenom , $eleve_INE , $nb_lignes_eleve_eval_total )
  {
    $hauteur_blocs_ligne1 = 30;
    $hauteur_blocs_ligne2 = 30;
    $this->eleve_nom    = $eleve_nom;
    $this->eleve_prenom = $eleve_prenom;
    // On prend une nouvelle page PDF
    $this->premiere_page();
    // Ecrire l'en-tête (qui ne dépend pas de la taille de la police calculée ensuite) et récupérer la place requise par cet en-tête.
    extract($tab_infos_entete); // $tab_menesr_logo , $tab_etabl_coords , $tab_etabl_logo , $tab_bloc_titres , $tab_adresse , $tag_date_heure_initiales , $eleve_genre , $date_naissance
    $this->entete_blocs_premiere_ligne( $hauteur_blocs_ligne1 , $tab_menesr_logo , $tab_etabl_coords , $tab_etabl_logo );
    $this->doc_titre = $tab_bloc_titres[0].' - '.$tab_bloc_titres[1];
    // Bloc adresse en positionnement contraint ou en positionnement libre
    if(is_array($tab_adresse))
    {
      list( $hauteur_blocs_ligne2 , $largeur_bloc_titre ) = $this->entete_bloc_adresse( $hauteur_blocs_ligne1 , $hauteur_blocs_ligne2 , $tab_adresse );
    }
    else
    {
      $hauteur_blocs_ligne2 = 25;
      $largeur_bloc_titre = 200 ;
    }
    // Bloc titres
    $this->entete_bloc_titres( $largeur_bloc_titre , $hauteur_blocs_ligne1 , $hauteur_blocs_ligne2 , $tab_bloc_titres );
    // Date de naissance + Tag date heure initiales (sous le bloc titres dans toutes les situations)
    $this->officiel_ligne_tag( $eleve_genre , $date_naissance , $eleve_INE , $tag_date_heure_initiales , $largeur_bloc_titre );
    // On calcule la hauteur de la ligne et la taille de la police pour faire rentrer le bloc des acquis si possible sur un recto (le verso comportant le reste)
    $hauteur_disponible = $this->page_hauteur_moins_marges - $hauteur_blocs_ligne1 - $hauteur_blocs_ligne2 ;
    $hauteur_ligne_minimale = 4.5;
    $hauteur_ligne_maximale = 6;
    $this->lignes_hauteur = round( $hauteur_disponible / $nb_lignes_eleve_eval_total , 1 , PHP_ROUND_HALF_DOWN ) ; // valeur approchée au dixième près par défaut
    $this->lignes_hauteur = max ( $this->lignes_hauteur , $hauteur_ligne_minimale ) ;
    $this->lignes_hauteur = min ( $this->lignes_hauteur , $hauteur_ligne_maximale ) ;
    $this->taille_police  = $this->lignes_hauteur * 1.6 ; // 5mm de hauteur par ligne donne une taille de 8
    $this->taille_police  = min ( $this->taille_police , 10 ) ;
    // Enfin, on se positionne pour la suite
    $this->SetXY( $this->marge_gauche , $this->marge_haut + $hauteur_blocs_ligne1 + $hauteur_blocs_ligne2 );
    $this->choisir_couleur_trait('noir');
    // $this->choisir_couleur_trait('blanc');
  }

  private function bloc_titre( $rubrique_type , $rubrique_titre )
  {
    $this->SetXY( 0 , $this->GetY() + 0.5*$this->lignes_hauteur );
    $this->SetFont('Arial' , 'B' , 1.5*$this->taille_police);
    $this->choisir_couleur_texte('blanc');
    $this->choisir_couleur_fond('livret_titre_'.$rubrique_type);
    $this->CellFit( $this->page_largeur , 1.5*$this->lignes_hauteur , To::pdf($rubrique_titre) , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
    $this->SetXY( $this->marge_gauche , $this->GetY() + 0.5*$this->lignes_hauteur );
    $this->SetFont('Arial' , '' , $this->taille_police);
    $this->choisir_couleur_texte('noir');
    $this->choisir_couleur_fond('livret_fond_'.$rubrique_type);
  }

  public function bloc_eval( $tab_rubriques , $tab_used_eval_eleve_rubrique , $tab_id_rubrique , $tab_saisie , $tab_moyenne , $tab_nb_lignes_eval , $nb_lignes_eleve_autre_total , $tab_profs )
  {
    $tab_deja_affiche = array();
    // Largeur des rubriques ; total = 200 = 210 - 5*2 (marges)
    $reduc_position       = in_array($this->PAGE_COLONNE,array('moyenne','pourcentage')) ? 5 : 0 ;
    $largeur_domaine      = ($this->BILAN_TYPE_ETABL=='college') ? 40 : 50 ;
    $largeur_elements     = ($this->BILAN_TYPE_ETABL=='college') ? 60+$reduc_position : 65+$reduc_position ;
    $largeur_appreciation = ($this->BILAN_TYPE_ETABL=='college') ? 70+$reduc_position : 55+$reduc_position ;
    $largeur_position     = 30 - 2*$reduc_position ;
    $largeur_sous_domaine = $largeur_domaine / 2; // 1er degré seulement
    // Titre
    $this->bloc_titre( 'eval' , 'Suivi des acquis scolaires de l’élève' );
    // Première ligne du tableau
    $entete_hauteur =2*$this->lignes_hauteur;
    $this->choisir_couleur_fond('livret_fond_eval');
    if($this->BILAN_TYPE_ETABL=='college')
    {
      $this->SetX( $this->GetX() + $largeur_domaine );
    }
    else
    {
      $this->CellFit( $largeur_domaine    , $entete_hauteur , To::pdf('Domaines d’enseignement')                          , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
    }
    $this->CellFit( $largeur_elements     , $entete_hauteur , To::pdf('Principaux éléments du programme travaillés')      , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
    $this->CellFit( $largeur_appreciation , $entete_hauteur , To::pdf('Acquisitions, progrès et difficultés éventuelles') , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
    if(in_array($this->PAGE_COLONNE,array('objectif','position')))
    {
      $this->CellFit( $largeur_position , $this->lignes_hauteur , To::pdf('Positionnement [*]') , 1 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
      $largeur_sous_position = $largeur_position / 4;
      foreach($this->SESSION['LIVRET'] as $id => $tab)
      {
        $br = ($id<4) ? 0 : 1 ;
        $this->choisir_couleur_fond('M'.$id.'oui');
        $this->CellFit( $largeur_sous_position , $this->lignes_hauteur , To::pdf($id) , 1 /*bordure*/ , $br , 'C' /*alignement*/ , TRUE /*fond*/ );
      }
      $this->choisir_couleur_fond('livret_fond_eval');
    }
    else if(in_array($this->PAGE_COLONNE,array('moyenne','pourcentage')))
    {
      $texte = ($this->PAGE_COLONNE=='moyenne') ? 'Moyenne sur 20' : 'Pourcentage de réussite' ;
      if(!$this->PAGE_MOYENNE_CLASSE)
      {
        $this->CellFit( $largeur_position , $entete_hauteur , To::pdf($texte) , 1 /*bordure*/ , 1 /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
      }
      else
      {
        $this->CellFit( $largeur_position   , $entete_hauteur/2 , To::pdf($texte)   , 1 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
        $this->CellFit( $largeur_position/2 , $entete_hauteur/2 , To::pdf('Élève')  , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
        $this->CellFit( $largeur_position/2 , $entete_hauteur/2 , To::pdf('Classe') , 1 /*bordure*/ , 1 /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
      }
    }
    // On passe en revue les rubriques...
    foreach($tab_rubriques as $livret_rubrique_id => $tab_rubrique)
    {
      if( isset($tab_used_eval_eleve_rubrique[$livret_rubrique_id]) )
      {
        // récup éléments travaillés
        $id_rubrique_elements = $livret_rubrique_id ; // On force une ligne par sous-rubrique, donc pas $tab_rubriques[$livret_rubrique_id]['elements'];
        $elements_info = isset($tab_saisie[$id_rubrique_elements]['elements']) ? $tab_saisie[$id_rubrique_elements]['elements'] : $this->tab_saisie_initialisation ;
        // récup appréciation
        $id_rubrique_appreciation = $tab_rubriques[$livret_rubrique_id]['appreciation'];
        $appreciation_info = isset($tab_saisie[$id_rubrique_appreciation]['appreciation']) ? $tab_saisie[$id_rubrique_appreciation]['appreciation'] : $this->tab_saisie_initialisation ;
        $tab_profs_appreciation = is_null($appreciation_info['listing_profs']) ? array() : explode(',',$appreciation_info['listing_profs']) ;
        // récup positionnement
        $id_rubrique_position = $tab_rubriques[$livret_rubrique_id]['position'];
        $position_info = isset($tab_saisie[$id_rubrique_position]['position']) ? $tab_saisie[$id_rubrique_position]['position'] : $this->tab_saisie_initialisation ;
        $tab_profs_position = is_null($position_info['listing_profs']) ? array() : explode(',',$position_info['listing_profs']) ;
        // ensuite...
        $id_premiere_sous_rubrique = $tab_rubriques[$livret_rubrique_id]['appreciation'];
        $nb_lignes_rubrique = $tab_nb_lignes_eval[$id_premiere_sous_rubrique];
        $hauteur_rubrique = $nb_lignes_rubrique*$this->lignes_hauteur;
        // La hauteur de ligne a déjà été calculée ; mais il reste à déterminer si on saute une page ou non en fonction de la place restante (et sinon => interligne)
        $hauteur_dispo_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas ;
        if($this->lignes_hauteur*$nb_lignes_rubrique > $hauteur_dispo_restante)
        {
          $this->rappel_eleve_page($this->PAGE_COLONNE);
        }
        // Domaine d’enseignement
        $memoX = $this->GetX();
        $memoY = $this->GetY();
        if($this->BILAN_TYPE_ETABL=='college')
        {
          // Pour les profs indiqués, on prend ceux qui ont renseigné l'appréciation, ou à défaut ceux qui ont participé à l'évaluation
          $tab_profs_affiche = !empty($tab_profs_appreciation) ? $tab_profs_appreciation : $tab_profs_position ;
          $listing_profs = '';
          $nombre_sous_rubriques = 1;
          $this->Rect( $memoX , $memoY , $largeur_domaine , $hauteur_rubrique , 'DF' /*DrawFill*/ );
          // centrage vertical
          $nb_lignes_texte = 1 + count($tab_profs_affiche);
          if( $nb_lignes_texte < $nb_lignes_rubrique )
          {
            $nb_ligne_marge = ( $nb_lignes_rubrique - $nb_lignes_texte ) / 2 ;
            $this->SetY( $memoY + $nb_ligne_marge*$this->lignes_hauteur );
          }
          // nom domaine
          $this->SetFont('Arial' , 'B' , $this->taille_police);
          $this->CellFit( $largeur_domaine , $this->lignes_hauteur , To::pdf($tab_rubrique['partie']) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
          // noms profs
          $this->SetFont('Arial' , '' , $this->taille_police);
          if( $nb_lignes_texte <= $nb_lignes_rubrique )
          {
            foreach($tab_profs_affiche as $key => $prof_id)
            {
              $this->CellFit( $largeur_domaine , $this->lignes_hauteur , To::pdf($tab_profs[$prof_id]) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
            }
          }
          $this->SetXY( $memoX + $largeur_domaine , $memoY );
        }
        else
        {
          // domaine et / ou sous-domaine
          // $this->SetFont('Arial' , 'B' , $this->taille_police);
          $nombre_sous_rubriques = isset($tab_id_rubrique['appreciation'][$id_premiere_sous_rubrique]) ? count($tab_id_rubrique['appreciation'][$id_premiere_sous_rubrique]) : 0 ;
          if( $nombre_sous_rubriques == 1 )
          {
            if($tab_rubrique['sous_partie'])
            {
              $this->CellFit( $largeur_sous_domaine , $hauteur_rubrique , To::pdf($tab_rubrique['partie'])      , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , TRUE /*fond*/ );
              $this->CellFit( $largeur_sous_domaine , $hauteur_rubrique , To::pdf($tab_rubrique['sous_partie']) , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , TRUE /*fond*/ );
            }
            else
            {
              $this->CellFit( $largeur_domaine , $hauteur_rubrique , To::pdf($tab_rubrique['partie']) , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
            }
          }
          else
          {
            $nb_lignes_sous_rubrique = $nb_lignes_rubrique / $nombre_sous_rubriques ;
            $hauteur_sous_rubrique = $nb_lignes_sous_rubrique*$this->lignes_hauteur;
            if(isset($tab_deja_affiche[$id_premiere_sous_rubrique]))
            {
              $this->SetXY( $memoX + $largeur_sous_domaine , $memoY_sous_rubrique_suivante );
              $this->CellFit( $largeur_sous_domaine , $hauteur_sous_rubrique , To::pdf($tab_rubrique['sous_partie']) , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , TRUE /*fond*/ );
            }
            else
            {
              $this->CellFit( $largeur_sous_domaine , $hauteur_rubrique      , To::pdf($tab_rubrique['partie'])      , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , TRUE /*fond*/ );
              $this->CellFit( $largeur_sous_domaine , $hauteur_sous_rubrique , To::pdf($tab_rubrique['sous_partie']) , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , TRUE /*fond*/ );
            }
            $memoY_sous_rubrique_suivante = $this->GetY() + $hauteur_sous_rubrique ;
          }
          // $this->SetFont('Arial' , '' , $this->taille_police);
        }
        // Principaux éléments du programme travaillés durant la période
        $memoX = $this->GetX();
        $memoY = $this->GetY();
        // contenu
        if($elements_info['saisie_valeur'])
        {
          $tab_elements = array();
          $tab_valeurs = array_slice( json_decode($elements_info['saisie_valeur'], TRUE) , 0 , 3 );
          foreach($tab_valeurs as $texte => $nb_used)
          {
            $tab_elements[] = '- '.$texte;
          }
          $elements = implode("\n",$tab_elements);
        }
        else
        {
          $elements = '' ;
        }
        if( ($this->BILAN_TYPE_ETABL=='college') || ( $nombre_sous_rubriques == 1 ) )
        {
          $this->Rect( $memoX , $memoY , $largeur_elements , $hauteur_rubrique , 'DF' /*DrawFill*/ );
          $this->afficher_appreciation( $largeur_elements , $hauteur_rubrique , $this->taille_police , 0.8*$this->lignes_hauteur /*taille_interligne*/ , $elements );
        }
        else
        {
          $this->Rect( $memoX , $memoY , $largeur_elements , $hauteur_sous_rubrique , 'DF' /*DrawFill*/ );
          $this->afficher_appreciation( $largeur_elements , $hauteur_sous_rubrique , $this->taille_police , 0.8*$this->lignes_hauteur /*taille_interligne*/ , $elements );
        }
        $this->SetXY( $memoX + $largeur_elements , $memoY );
        // Acquisitions, progrès et difficultés éventuelles
        $memoX = $this->GetX();
        $memoY = $this->GetY();
        $nombre_rubriques_regroupees = isset($tab_id_rubrique['appreciation'][$id_rubrique_appreciation]) ? count($tab_id_rubrique['appreciation'][$id_rubrique_appreciation]) : 0 ;
        if( ( $nombre_rubriques_regroupees == 1 ) || !isset($tab_deja_affiche[$id_rubrique_appreciation]) )
        {
          $appreciation = ($appreciation_info['saisie_valeur']) ? $appreciation_info['saisie_valeur'] : '' ;
          $hauteur_appreciation = $hauteur_rubrique ;
          $this->Rect( $memoX , $memoY , $largeur_appreciation , $hauteur_appreciation , 'DF' /*DrawFill*/ );
          $this->afficher_appreciation( $largeur_appreciation , $hauteur_appreciation , $this->taille_police , 0.8*$this->lignes_hauteur /*taille_interligne*/ , $appreciation );
        }
        $this->SetXY( $memoX + $largeur_appreciation , $memoY );
        // Positionnement
        $memoX = $this->GetX();
        $memoY = $this->GetY();
        if( ( $nombre_rubriques_regroupees == 1 ) || !isset($tab_deja_affiche[$id_rubrique_position]) )
        {
          $pourcentage = !is_null($position_info['saisie_valeur']) ? $position_info['saisie_valeur'] : FALSE ;
          $hauteur_position = ($nombre_rubriques_regroupees>1) ? $hauteur_sous_rubrique : $hauteur_rubrique ;
          if( in_array($this->PAGE_COLONNE,array('objectif','position')) )
          {
            $indice = OutilBilan::determiner_degre_maitrise($pourcentage);
            $this->SetFont('Arial' , 'B' , $this->taille_police);
            foreach($this->SESSION['LIVRET'] as $id => $tab)
            {
              $texte = ($id==$indice) ? 'X' : '' ;
              $this->choisir_couleur_fond('M'.$id.'oui');
              $this->Cell( $largeur_sous_position , $hauteur_position , To::pdf($texte) , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
            }
            $this->choisir_couleur_fond('livret_fond_eval');
            $this->SetFont('Arial' , '' , $this->taille_police);
          }
          else if( in_array($this->PAGE_COLONNE,array('moyenne','pourcentage')) )
          {
            $note = ($position_info['saisie_valeur']!==NULL) ? ( ($this->PAGE_COLONNE=='moyenne') ? round(($pourcentage/5),1) : $pourcentage.' %' ) : '-' ;
            if(!$this->PAGE_MOYENNE_CLASSE)
            {
              $this->CellFit( $largeur_position , $hauteur_position , To::pdf($note) , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
            }
            else
            {
              $position_info = isset($tab_moyenne[$id_rubrique_position]['position']) ? $tab_moyenne[$id_rubrique_position]['position'] : $this->tab_saisie_initialisation ;
              $pourcentage = !is_null($position_info['saisie_valeur']) ? $position_info['saisie_valeur'] : FALSE ;
              $note_moyenne = ($position_info['saisie_valeur']!==NULL) ? ( ($this->PAGE_COLONNE=='moyenne') ? round(($pourcentage/5),1) : $pourcentage.' %' ) : '-' ;
              $this->SetFont('Arial' , 'B' , $this->taille_police);
              $this->CellFit( $largeur_position/2 , $hauteur_position , To::pdf($note)         , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
              $this->SetFont('Arial' , '' , $this->taille_police);
              $this->CellFit( $largeur_position/2 , $hauteur_position , To::pdf($note_moyenne) , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*fond*/ );
            }
          }
        }
        $tab_deja_affiche[$id_premiere_sous_rubrique] = TRUE;
        // positionnement ligne suivante
        $nextY = ( $nombre_sous_rubriques == 1 ) ? $memoY + $hauteur_rubrique : $memoY + $hauteur_sous_rubrique ;
        $this->SetXY( $this->marge_gauche , $nextY );
      }
    }
    // Légende si pas déjà fait - Nouvelle page si pas déjà fait
    $this->rappel_eleve_page();
    // Pour le prochain tirage (autre responsable légal...)
    $this->legende_deja_affichee = FALSE;
    // On calcule la hauteur de la ligne et la taille de la police pour faire rentrer les blocs suivants sur le verso (ou ce qu'il en reste)
    $hauteur_disponible = $this->page_hauteur - $this->GetY() - $this->marge_bas ;
    // $hauteur_ligne_minimale = 4.5; // pas de hauteur minimale, on impose seulement 2 pages !
    $hauteur_ligne_maximale = ($this->lignes_hauteur>5) ? $this->lignes_hauteur : 6 ; // on continue autant que possible avec la taille précédente, sauf si elle est petite
    $this->lignes_hauteur = round( $hauteur_disponible / $nb_lignes_eleve_autre_total , 1 , PHP_ROUND_HALF_DOWN ) ; // valeur approchée au dixième près par défaut
    $this->lignes_hauteur = min ( $this->lignes_hauteur , $hauteur_ligne_maximale ) ;
    $this->taille_police  = $this->lignes_hauteur * 1.6 ; // 5mm de hauteur par ligne donne une taille de 8
    $this->taille_police  = min ( $this->taille_police , 10 ) ;
  }

  public function bloc_epi( $tab_rubriques_epi , $tab_saisie_eleve , $tab_saisie_classe )
  {
    // Titre
    $this->bloc_titre( 'epi' , 'Enseignements pratiques interdisciplinaires' );
    // On passe en revue les EPI
    foreach($tab_rubriques_epi as $livret_epi_id => $tab_epi)
    {
      $saisie_classe = isset($tab_saisie_classe[$livret_epi_id]['appreciation']) ? $tab_saisie_classe[$livret_epi_id]['appreciation'] : $this->tab_saisie_initialisation ;
      $saisie_eleve  = isset($tab_saisie_eleve[ $livret_epi_id]['appreciation']) ? $tab_saisie_eleve[ $livret_epi_id]['appreciation'] : $this->tab_saisie_initialisation ;
      if( $saisie_eleve['saisie_valeur'] || $saisie_classe['saisie_valeur'] )
      {
        $nb_lignes_classe = ($saisie_classe['saisie_valeur']) ? max( ceil(strlen($saisie_classe['saisie_valeur'])/$this->nb_caract_max_par_ligne) , min( substr_count($saisie_classe['saisie_valeur'],"\n") + 1 , $this->app_rubrique_nb_caract_max / $this->nb_caract_max_par_ligne ) ) : 0 ;
        $nb_lignes_eleve  = ($saisie_eleve[ 'saisie_valeur']) ? max( ceil(strlen($saisie_eleve[ 'saisie_valeur'])/$this->nb_caract_max_par_ligne) , min( substr_count($saisie_eleve[ 'saisie_valeur'],"\n") + 1 , $this->app_rubrique_nb_caract_max / $this->nb_caract_max_par_ligne ) ) : 0 ;
        $nb_lignes_epi = 3 + $nb_lignes_classe + $nb_lignes_eleve; // titre + thème + profs + saisies
        $hauteur_epi = $nb_lignes_epi*$this->lignes_hauteur;
        $memoY = $this->GetY();
        // fond & contour
        $this->Rect( $this->GetX() , $this->GetY() , $this->page_largeur_moins_marges , $hauteur_epi , 'DF' /*DrawFill*/ );
        // intitulé + thématique interdisciplinaire
        $this->SetFont('Arial' , 'B' , $this->taille_police);
        $this->CellFit( $this->page_largeur_moins_marges , $this->lignes_hauteur , To::pdf('['.$tab_epi['theme_nom'].'] '.$tab_epi['titre']) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
        // noms profs
        if(count($tab_epi['mat_prof_txt'])>4)
        {
          $tab_epi['mat_prof_txt'] = array_slice( $tab_epi['mat_prof_txt'] , 0 , 3 );
          $tab_epi['mat_prof_txt'][3] = '[...]';
        }
        $this->SetFont('Arial' , '' , $this->taille_police);
        $this->CellFit( $this->page_largeur_moins_marges , $this->lignes_hauteur , To::pdf(implode(' ; ',$tab_epi['mat_prof_txt'])) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
        // Projet réalisé
        if($nb_lignes_classe)
        {
          $this->afficher_appreciation( $this->page_largeur_moins_marges , $nb_lignes_classe*$this->lignes_hauteur , $this->taille_police , 0.8*$this->lignes_hauteur /*taille_interligne*/ , 'Projet réalisé : '.$saisie_classe['saisie_valeur'] );
        }
        // Implication de l’élève
        if($nb_lignes_eleve)
        {
          $this->afficher_appreciation( $this->page_largeur_moins_marges , $nb_lignes_eleve*$this->lignes_hauteur , $this->taille_police , 0.8*$this->lignes_hauteur /*taille_interligne*/ , 'Implication de l’élève : '.$saisie_eleve['saisie_valeur'] );
        }
        // Repositionnement
        $this->SetXY( $this->marge_gauche , $memoY + $hauteur_epi );
      }
    }
  }

  public function bloc_ap( $tab_rubriques_ap , $tab_saisie_eleve , $tab_saisie_classe )
  {
    // Titre
    $this->bloc_titre( 'ap' , 'Accompagnement personnalisé' );
    // On passe en revue les AP
    foreach($tab_rubriques_ap as $livret_ap_id => $tab_ap)
    {
      $saisie_classe = isset($tab_saisie_classe[$livret_ap_id]['appreciation']) ? $tab_saisie_classe[$livret_ap_id]['appreciation'] : $this->tab_saisie_initialisation ;
      $saisie_eleve  = isset($tab_saisie_eleve[ $livret_ap_id]['appreciation']) ? $tab_saisie_eleve[ $livret_ap_id]['appreciation'] : $this->tab_saisie_initialisation ;
      if( $saisie_eleve['saisie_valeur'] || $saisie_classe['saisie_valeur'] )
      {
        $nb_lignes_classe = ($saisie_classe['saisie_valeur']) ? max( ceil(strlen($saisie_classe['saisie_valeur'])/$this->nb_caract_max_par_ligne) , min( substr_count($saisie_classe['saisie_valeur'],"\n") + 1 , $this->app_rubrique_nb_caract_max / $this->nb_caract_max_par_ligne ) ) : 0 ;
        $nb_lignes_eleve  = ($saisie_eleve[ 'saisie_valeur']) ? max( ceil(strlen($saisie_eleve[ 'saisie_valeur'])/$this->nb_caract_max_par_ligne) , min( substr_count($saisie_eleve[ 'saisie_valeur'],"\n") + 1 , $this->app_rubrique_nb_caract_max / $this->nb_caract_max_par_ligne ) ) : 0 ;
        $nb_lignes_ap = 2 + $nb_lignes_classe + $nb_lignes_eleve; // titre + profs + saisies
        $hauteur_ap = $nb_lignes_ap*$this->lignes_hauteur;
        $memoY = $this->GetY();
        // fond & contour
        $this->Rect( $this->GetX() , $this->GetY() , $this->page_largeur_moins_marges , $hauteur_ap , 'DF' /*DrawFill*/ );
        // intitulé
        $this->SetFont('Arial' , 'B' , $this->taille_police);
        $this->CellFit( $this->page_largeur_moins_marges , $this->lignes_hauteur , To::pdf($tab_ap['titre']) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
        // noms profs
        if(count($tab_ap['mat_prof_txt'])>4)
        {
          $tab_ap['mat_prof_txt'] = array_slice( $tab_ap['mat_prof_txt'] , 0 , 3 );
          $tab_ap['mat_prof_txt'][3] = '[...]';
        }
        $this->SetFont('Arial' , '' , $this->taille_police);
        $this->CellFit( $this->page_largeur_moins_marges , $this->lignes_hauteur , To::pdf(implode(' ; ',$tab_ap['mat_prof_txt'])) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
        // Action réalisée
        if($nb_lignes_classe)
        {
          $this->afficher_appreciation( $this->page_largeur_moins_marges , $nb_lignes_classe*$this->lignes_hauteur , $this->taille_police , 0.8*$this->lignes_hauteur /*taille_interligne*/ , 'Action réalisée : '.$saisie_classe['saisie_valeur'] );
        }
        // Implication de l’élève
        if($nb_lignes_eleve)
        {
          $this->afficher_appreciation( $this->page_largeur_moins_marges , $nb_lignes_eleve*$this->lignes_hauteur , $this->taille_police , 0.8*$this->lignes_hauteur /*taille_interligne*/ , 'Implication de l’élève : '.$saisie_eleve['saisie_valeur'] );
        }
        // Repositionnement
        $this->SetXY( $this->marge_gauche , $memoY + $hauteur_ap );
      }
    }
  }

  public function bloc_parcours( $tab_rubriques_parcours , $tab_saisie_eleve , $tab_saisie_classe )
  {
    // Titre
    $this->bloc_titre( 'parcours' , 'Parcours éducatifs' );
    // On passe en revue les parcours
    foreach($tab_rubriques_parcours as $livret_parcours_id => $tab_parcours)
    {
      $saisie_classe = isset($tab_saisie_classe[$livret_parcours_id]['appreciation']) ? $tab_saisie_classe[$livret_parcours_id]['appreciation'] : $this->tab_saisie_initialisation ;
      $saisie_eleve  = ( ($this->BILAN_TYPE_ETABL=='college') && isset($tab_saisie_eleve[ $livret_parcours_id]['appreciation']) ) ? $tab_saisie_eleve[ $livret_parcours_id]['appreciation'] : $this->tab_saisie_initialisation ;
      if( $saisie_classe['saisie_valeur'] ) // conditionné au renseignement du projet
      {
        $nb_lignes_classe = ($saisie_classe['saisie_valeur']) ? max( ceil(strlen($saisie_classe['saisie_valeur'])/$this->nb_caract_max_par_ligne) , min( substr_count($saisie_classe['saisie_valeur'],"\n") + 1 , $this->app_rubrique_nb_caract_max / $this->nb_caract_max_par_ligne ) ) : 0 ;
        $nb_lignes_eleve  = ($saisie_eleve[ 'saisie_valeur']) ? max( ceil(strlen($saisie_eleve[ 'saisie_valeur'])/$this->nb_caract_max_par_ligne) , min( substr_count($saisie_eleve[ 'saisie_valeur'],"\n") + 1 , $this->app_rubrique_nb_caract_max / $this->nb_caract_max_par_ligne ) ) : 0 ;
        $nb_lignes_parcours = 1 + $nb_lignes_classe + $nb_lignes_eleve; // type_nom / prof + saisies
        $hauteur_parcours = $nb_lignes_parcours*$this->lignes_hauteur;
        $memoY = $this->GetY();
        // fond & contour
        $this->Rect( $this->GetX() , $this->GetY() , $this->page_largeur_moins_marges , $hauteur_parcours , 'DF' /*DrawFill*/ );
        // type de parcours + nom prof
        $this->SetFont('Arial' , 'B' , $this->taille_police);
        $this->CellFit( $this->page_largeur_moins_marges , $this->lignes_hauteur , To::pdf($tab_parcours['type_nom'].' ('.$tab_parcours['prof_txt'].')') , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
        $this->SetFont('Arial' , '' , $this->taille_police);
        // Projet mis en oeuvre
        $this->afficher_appreciation( $this->page_largeur_moins_marges , $nb_lignes_classe*$this->lignes_hauteur , $this->taille_police , 0.8*$this->lignes_hauteur /*taille_interligne*/ , 'Projet mis en oeuvre : '.$saisie_classe['saisie_valeur'] );
        // Implication de l’élève
        if($nb_lignes_eleve)
        {
          $this->afficher_appreciation( $this->page_largeur_moins_marges , $nb_lignes_eleve*$this->lignes_hauteur , $this->taille_police , 0.8*$this->lignes_hauteur /*taille_interligne*/ , 'Implication de l’élève : '.$saisie_eleve['saisie_valeur'] );
        }
        // Repositionnement
        $this->SetXY( $this->marge_gauche , $memoY + $hauteur_parcours );
      }
    }
  }

  public function bloc_modaccomp( $tab_rubriques_modaccomp , $information_ppre )
  {
    // Titre
    $s = (count($tab_rubriques_modaccomp)>1) ? 's' : '' ;
    $this->bloc_titre( 'modaccomp' , 'Modalité'.$s.' spécifique'.$s.' d’accompagnement' );
    // calculs
    $nb_lignes_ppre = ($information_ppre) ? max( ceil(strlen($information_ppre)/$this->nb_caract_max_par_ligne) , min( substr_count($information_ppre,"\n") + 1 , $this->app_rubrique_nb_caract_max / $this->nb_caract_max_par_ligne ) ) : 0 ;
    $nb_lignes_modaccomp = 1 + $nb_lignes_ppre; // modalités + saisie complément PPRE
    $hauteur_modaccomp = $nb_lignes_modaccomp*$this->lignes_hauteur;
    $memoY = $this->GetY();
    // fond & contour
    $this->Rect( $this->GetX() , $this->GetY() , $this->page_largeur_moins_marges , $hauteur_modaccomp , 'DF' /*DrawFill*/ );
    // modalités
    $this->SetFont('Arial' , 'B' , $this->taille_police);
    $this->CellFit( $this->page_largeur_moins_marges , $this->lignes_hauteur , To::pdf(implode(', ',$tab_rubriques_modaccomp)) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    $this->SetFont('Arial' , '' , $this->taille_police);
    // Commentaire PPRE
    if($information_ppre)
    {
      $this->afficher_appreciation( $this->page_largeur_moins_marges , $nb_lignes_ppre*$this->lignes_hauteur , $this->taille_police , 0.8*$this->lignes_hauteur /*taille_interligne*/ , 'Information PPRE : '.$information_ppre );
    }
    // Repositionnement
    $this->SetXY( $this->marge_gauche , $memoY + $hauteur_modaccomp );
  }

  public function bloc_bilan( $bilan_saisie , $texte_prof_principal )
  {
    // Titre
    $this->bloc_titre( 'bilan' , 'Bilan de l’acquisition des connaissances et compétences' );
    // calculs
    $nb_lignes_saisie = ($bilan_saisie) ? max( 6 , ceil(strlen($bilan_saisie)/$this->nb_caract_max_par_ligne), min( substr_count($bilan_saisie,"\n") + 1 , $this->app_bilan_nb_caract_max / $this->nb_caract_max_par_ligne ) ) : 6 ; // On prévoit un emplacement par défaut
    $nb_lignes_bilan  = 1 + $nb_lignes_saisie ; // texte introductif + saisie
    $nb_lignes_bilan += ($this->BILAN_TYPE_ETABL=='college') ? 1 : 0 ; // prof principal
    $hauteur_bilan = $nb_lignes_bilan*$this->lignes_hauteur;
    $memoY = $this->GetY();
    // fond & contour
    $this->Rect( $this->GetX() , $this->GetY() , $this->page_largeur_moins_marges , $hauteur_bilan , 'DF' /*DrawFill*/ );
    // texte introductif
    $texte = ($this->BILAN_TYPE_ETABL=='college') ? 'Synthèse de l’évolution des acquis scolaires et conseils pour progresser :' : 'Appréciation générale sur la progression de l’élève :' ;
    $this->SetFont('Arial' , 'U' , $this->taille_police);
    $this->CellFit( $this->page_largeur_moins_marges , $this->lignes_hauteur , To::pdf($texte) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    $this->SetFont('Arial' , '' , $this->taille_police);
    // saisie
    if($bilan_saisie)
    {
      $this->afficher_appreciation( $this->page_largeur_moins_marges , $nb_lignes_saisie*$this->lignes_hauteur , $this->taille_police , 0.8*$this->lignes_hauteur /*taille_interligne*/ , $bilan_saisie );
    }
    else
    {
      $this->SetY( $memoY + $hauteur_bilan - $this->lignes_hauteur );
    }
    // prof principal
    if($this->BILAN_TYPE_ETABL=='college')
    {
      $this->CellFit( $this->page_largeur_moins_marges , $this->lignes_hauteur , To::pdf($texte_prof_principal) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    }
    // Repositionnement
    $this->SetXY( $this->marge_gauche , $memoY + $hauteur_bilan );
  }

  public function bloc_viesco_2d( $viesco_saisie , $texte_assiduite , $DATE_VERROU , $texte_chef_etabl , $tab_signature , $tab_parent_lecture )
  {
    // Titre
    $this->bloc_titre( 'viesco' , 'Communication avec la famille' );
    // calculs
    $nb_lignes_saisie = ($viesco_saisie) ? max( 6 , ceil(strlen($viesco_saisie)/$this->nb_caract_max_par_ligne), min( substr_count($viesco_saisie,"\n") + 1 , $this->app_rubrique_nb_caract_max / $this->nb_caract_max_par_ligne ) ) : 6 ; // On prévoit un emplacement par défaut
    $nb_lignes_viesco  = 1 + $nb_lignes_saisie + 1 ; // texte introductif + saisie + assiduité
    $hauteur_viesco = $nb_lignes_viesco*$this->lignes_hauteur;
    $hauteur_signature = $nb_lignes_saisie*$this->lignes_hauteur; // 2 lignes de moins pour chef établ + date
    $largeur_sousbloc_signature = $hauteur_viesco;
    $largeur_sousbloc_saisie    = $this->page_largeur_moins_marges - $largeur_sousbloc_signature;
    $memoY = $this->GetY();
    // image de la signature ; on commence par elle car sinon elle peut déborder légèrement sur le fond coloré ou la bordure, et de toutes façons on éviter un fond coloré en dessous
    if($tab_signature)
    {
      $epaisseur_bord = 0.5; // on compte quand même un peut de marge sinon cela peut être collé et ce n'est pas très joli
      $this->SetX( $this->GetX() + $largeur_sousbloc_saisie + $epaisseur_bord );
      $largeur_signature =  $this->afficher_image( $largeur_sousbloc_signature-2*$epaisseur_bord /*largeur_autorisee*/ , $hauteur_signature /*hauteur_autorisee*/ , $tab_signature , 'logo_seul' /*img_objet*/ );
    }
    $this->SetXY( $this->marge_gauche , $memoY );
    // fond & contour
    if($tab_signature)
    {
      $this->Rect( $this->GetX() , $this->GetY() , $largeur_sousbloc_saisie         , $hauteur_viesco , 'F' /*DrawFill*/ );
      $this->Rect( $this->GetX() , $this->GetY() , $this->page_largeur_moins_marges , $hauteur_viesco , 'D' /*DrawFill*/ );
    }
    else
    {
      $this->Rect( $this->GetX() , $this->GetY() , $this->page_largeur_moins_marges , $hauteur_viesco , 'DF' /*DrawFill*/ );
    }
    // texte introductif
    $this->SetFont('Arial' , 'U' , $this->taille_police);
    $this->CellFit( $largeur_sousbloc_saisie , $this->lignes_hauteur , To::pdf('Vie scolaire (assiduité, ponctualité ; respect du règlement ; participation à la vie de l’établissement) :') , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    $this->SetFont('Arial' , '' , $this->taille_police);
    // saisie
    if($viesco_saisie)
    {
      $this->afficher_appreciation( $largeur_sousbloc_saisie , $nb_lignes_saisie*$this->lignes_hauteur , $this->taille_police , 0.8*$this->lignes_hauteur /*taille_interligne*/ , $viesco_saisie );
    }
    else
    {
      $this->SetY( $memoY + $hauteur_viesco - $this->lignes_hauteur );
    }
    // assiduité
    $this->CellFit( $largeur_sousbloc_saisie , $this->lignes_hauteur , To::pdf($texte_assiduite) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    // infos signature
    $this->SetXY( $this->marge_gauche + $largeur_sousbloc_saisie , $memoY + $hauteur_signature );
    $this->CellFit( $largeur_sousbloc_signature , $this->lignes_hauteur , To::pdf($texte_chef_etabl)  , 0 /*bordure*/ , 2 /*br*/ , 'R' /*alignement*/ , FALSE /*fond*/ );
    $this->CellFit( $largeur_sousbloc_signature , $this->lignes_hauteur , To::pdf('le '.$DATE_VERROU) , 0 /*bordure*/ , 2 /*br*/ , 'R' /*alignement*/ , FALSE /*fond*/ );
    // Repositionnement
    $this->SetXY( $this->marge_gauche , $memoY + $hauteur_viesco );
    // Cadre pour les responsables légaux
    $this->SetXY( $this->marge_gauche , $this->GetY() + 0.5*$this->lignes_hauteur );
    // fond & contour
    $this->Rect( $this->GetX() , $this->GetY() , $this->page_largeur_moins_marges , 4*$this->lignes_hauteur , 'DF' /*DrawFill*/ );
    // texte introductif
    $this->SetFont('Arial' , 'U' , $this->taille_police);
    $this->CellFit( $this->page_largeur_moins_marges , $this->lignes_hauteur , To::pdf('Date, nom et signature des responsables légaux :') , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    $this->SetFont('Arial' , '' , $this->taille_police);
    // contenu
    foreach($tab_parent_lecture as $parent_info)
    {
      if($parent_info) // sort du cadre si plus de 3 responsables légaux signataires, mais ce cas de devrait pas se produire...
      {
        $this->CellFit( $this->page_largeur_moins_marges , $this->lignes_hauteur , To::pdf($parent_info) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
      }
    }
  }

  public function bloc_viesco_1d( $DATE_VERROU , $tab_instit , $tab_signature , $tab_parent_lecture )
  {
    $nb_instit = count($tab_instit);
    // Titre
    $this->bloc_titre( 'viesco' , 'Communication avec la famille' );
    // calculs
    $hauteur_viesco = ( max( $nb_instit , 3 ) + 2 )*$this->lignes_hauteur; // hauteur minimale + ligne intro + ligne date
    $hauteur_signature = $hauteur_viesco; // 2 lignes de moins pour chef établ + date
    $largeur_sousbloc_signature = $this->page_largeur_moins_marges / 4;
    $largeur_sousbloc_saisie    = $this->page_largeur_moins_marges / 4;
    $largeur_sousbloc_parent    = $this->page_largeur_moins_marges / 2;
    $memoY = $this->GetY();
    // image de la signature ; on commence par elle car sinon elle peut déborder légèrement sur le fond coloré ou la bordure, et de toutes façons on éviter un fond coloré en dessous
    if($tab_signature)
    {
      $epaisseur_bord = 0.5; // on compte quand même un peut de marge sinon cela peut être collé et ce n'est pas très joli
      $this->SetX( $this->GetX() + $largeur_sousbloc_saisie + $epaisseur_bord );
      $largeur_signature =  $this->afficher_image( $largeur_sousbloc_signature-2*$epaisseur_bord /*largeur_autorisee*/ , $hauteur_signature /*hauteur_autorisee*/ , $tab_signature , 'logo_seul' /*img_objet*/ );
    }
    $this->SetXY( $this->marge_gauche , $memoY );
    // fond & contour
    $this->Rect( $this->GetX()+$largeur_sousbloc_parent , $this->GetY() , $largeur_sousbloc_parent , $hauteur_viesco , 'DF' /*DrawFill*/ );
    if($tab_signature)
    {
      $this->Rect( $this->GetX() , $this->GetY() , $largeur_sousbloc_saisie , $hauteur_viesco , 'F' /*DrawFill*/ );
      $this->Rect( $this->GetX() , $this->GetY() , $largeur_sousbloc_parent , $hauteur_viesco , 'D' /*DrawFill*/ );
    }
    else
    {
      $this->Rect( $this->GetX() , $this->GetY() , $largeur_sousbloc_parent , $hauteur_viesco , 'DF' /*DrawFill*/ );
    }
    // Cadre pour les enseignant(s)
    // texte introductif
    $texte = ($nb_instit==1) ? 'Visa de l’enseignant(e) :' : 'Visa des enseignant(e)s :' ;
    $this->SetFont('Arial' , 'U' , $this->taille_police);
    $this->CellFit( $this->page_largeur_moins_marges , $this->lignes_hauteur , To::pdf($texte) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    $this->SetFont('Arial' , '' , $this->taille_police);
    // contenu
    foreach($tab_instit as $instit_info)
    {
      $this->CellFit( $largeur_sousbloc_saisie , $this->lignes_hauteur , To::pdf($instit_info) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    }
    $this->CellFit( $largeur_sousbloc_saisie , $this->lignes_hauteur , To::pdf('le '.$DATE_VERROU) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    $this->SetXY( $this->marge_gauche+$largeur_sousbloc_saisie , $memoY );
    // Cadre pour les responsables légaux
    $this->SetXY( $this->marge_gauche+$largeur_sousbloc_parent , $memoY );
    // texte introductif
    $this->SetFont('Arial' , 'U' , $this->taille_police);
    $this->CellFit( $this->page_largeur_moins_marges , $this->lignes_hauteur , To::pdf('Visa des parents / du responsable légal :') , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
    $this->SetFont('Arial' , '' , $this->taille_police);
    // contenu
    foreach($tab_parent_lecture as $parent_info)
    {
      if($parent_info) // sort du cadre si plus de 3 responsables légaux signataires, mais ce cas de devrait pas se produire...
      {
        $this->CellFit( $this->page_largeur_moins_marges , $this->lignes_hauteur , To::pdf($parent_info) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
      }
    }
  }

}
?>