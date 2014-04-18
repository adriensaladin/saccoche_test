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

class PDF_BilanItemIndividuel_Model2 extends PDF
{
  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthodes pour la mise en page d'un bilan d'items d'une matiere ou pluridisciplinaire
  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // bilan_item_individuel_initialiser()   c'est là que les calculs se font pour une sortie "matiere" ou "selection" ou "professeur"
  // bilan_item_individuel_entete()        c'est là que les calculs se font pour une sortie "multimatiere"
  // bilan_item_individuel_premiere_page()
  // bilan_item_individuel_rappel_eleve_page()
  // bilan_item_individuel_transdisciplinaire_ligne_matiere()
  // bilan_item_individuel_appreciation_rubrique()
  // bilan_item_individuel_appreciation_generale()
  // bilan_item_individuel_debut_ligne_item()
  // bilan_item_individuel_ligne_synthese()
  // bilan_item_individuel_legende()
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function bilan_item_individuel_initialiser( $format , $aff_etat_acquisition , $aff_date_reussite, $aff_anciennete_notation , $cases_nb , $cases_largeur , $lignes_nb , $eleves_nb , $pages_nb_methode )
  {
    $this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droite);
    $this->SetAutoPageBreak(FALSE);
    $this->format                  = $format;
    $this->cases_nb                = $cases_nb;
    $this->SetFont($_SESSION["OFFICIEL"]["POLICE"] , 'B' , 12);
    $this->case_date_largeur       = max($this->GetMinCellWidth(To::pdf("Date d'\nacquisition")), $cases_largeur);
    $this->case_resultat_largeur   = max($this->GetMinCellWidth(To::pdf("Résultat")), $cases_largeur);
    $this->cases_largeur           = max($this->GetMinCellWidth(To::pdf("Evaluations")), $cases_largeur * $cases_nb) / $cases_nb;
    $this->colonne_bilan_largeur   = ($aff_etat_acquisition) ? max($this->GetMinCellWidth(To::pdf("Résultat")), $cases_largeur) : 0 ;
    $this->reference_largeur       = max($this->GetMinCellWidth(To::pdf("Code de\nl'épreuve")), 20);
    $this->synthese_largeur        = $this->page_largeur_moins_marges - $this->reference_largeur;
    $this->aff_date_reussite       = $aff_date_reussite;
    $this->legende_deja_affichee   = FALSE; // Si multimatières, on n'est pas certain qu'il y ait la place pour la légende en dernière page, alors on la met dès que possible
    $this->legende_nb_lignes       = 1 + (int)$aff_anciennete_notation + (int)$aff_etat_acquisition ;
    $this->aff_codes_notation      = TRUE;
    $this->aff_anciennete_notation = $aff_anciennete_notation;
    $this->aff_etat_acquisition    = $aff_etat_acquisition;
  }

  public function bilan_item_individuel_premiere_page()
  {
    $this->AddPage($this->orientation , 'A4');
    $this->choisir_couleur_texte('gris_fonce');
    $this->Cell( $this->page_largeur_moins_marges , $this->lignes_hauteur , To::pdf($this->eleve_nom.' '.$this->eleve_prenom) , 0 /*bordure*/ , 1 /*br*/ , 'R' /*Alignement*/ , FALSE /*remplissage*/ );
    $this->choisir_couleur_texte('noir');
    $this->SetXY($this->marge_gauche,$this->marge_haut);
  }

  public function bilan_item_individuel_rappel_eleve_page()
  {
    $this->AddPage($this->orientation , 'A4');
    $this->choisir_couleur_texte('gris_fonce');
    $this->Cell( $this->page_largeur_moins_marges , $this->lignes_hauteur , To::pdf($this->eleve_nom.' '.$this->eleve_prenom) , 0 /*bordure*/ , 1 /*br*/ , 'R' /*Alignement*/ , FALSE /*remplissage*/ );
    $this->choisir_couleur_texte('noir');
  }

  public function bilan_item_individuel_entete( $pages_nb_methode , $tab_infos_entete , $eleve_nom , $eleve_prenom , $eleve_nb_lignes )
  {
    $this->intitule_largeur = $this->synthese_largeur - ( $this->cases_nb * $this->cases_largeur ) - $this->colonne_bilan_largeur;
    if ($this->aff_date_reussite == true)
    {
      $this->intitule_largeur -= $this->case_date_largeur;
    }
    $this->aff_colonne_date_reussite = $this->aff_date_reussite;
    $this->eleve_nom    = $eleve_nom;
    $this->eleve_prenom = $eleve_prenom;
    if( ($this->format!='multimatiere') )
    {
      // La hauteur de ligne a déjà été calculée ; mais il reste à déterminer si on saute une page ou non en fonction de la place restante (et sinon => interligne)
      $hauteur_dispo_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas ;
      $lignes_nb = 1 + 1 + $eleve_nb_lignes + ($this->legende*$this->legende_nb_lignes) + 2 ; // intitulé-matiere-structure + classe-élève-date + lignes dont résumés + légendes + marge
      if($this->lignes_hauteur*$lignes_nb > $hauteur_dispo_restante)
      {
        $this->AddPage($this->orientation , 'A4');
      }
      else
      {
        if ($this->PageNo() != 1)
        {
          // Interligne
          $this->SetXY($this->marge_gauche , $this->GetY() + $this->lignes_hauteur*2);
        }
      }
      list( $texte_format , $texte_periode , $groupe_nom ) = $tab_infos_entete;
    }
    elseif($this->format=='multimatiere')
    {
      // On prend une nouvelle page PDF
      $this->bilan_item_individuel_premiere_page();
      $this->legende_deja_affichee = FALSE; // Si multimatières, on n'est pas certain qu'il y ait la place pour la légende en dernière page, alors on la met dès que possible

      if($this->officiel)
      {
        // Ecrire l'entête (qui ne dépend pas de la taille de la police calculée ensuite) et récupérer la place requise par cet entête.
        list( $tab_etabl_coords , $tab_etabl_logo , $etabl_coords__bloc_hauteur , $tab_bloc_titres , $tab_adresse , $tag_date_heure_initiales , $date_naissance ) = $tab_infos_entete;
        $this->doc_titre = $tab_bloc_titres[0].' - '.$tab_bloc_titres[1];
        // Bloc adresse en positionnement contraint
        if( (is_array($tab_adresse)) && ($_SESSION['OFFICIEL']['INFOS_RESPONSABLES']=='oui_force') )
        {
          list( $bloc_droite_hauteur , $bloc_gauche_largeur_restante ) = $this->officiel_bloc_adresse_position_contrainte_et_pliures($tab_adresse);
          $this->SetXY($this->marge_gauche,$this->marge_haut);
        }
        // Bloc établissement
        $bloc_etabl_largeur = (isset($bloc_gauche_largeur_restante)) ? $bloc_gauche_largeur_restante : 80 ;
        $bloc_etabl_hauteur = $this->officiel_bloc_etablissement($tab_etabl_coords,$tab_etabl_logo,$bloc_etabl_largeur);
        // Bloc titres
        $alerte_archive = ($tab_adresse==='archive') ? TRUE : FALSE ;
        if( (is_array($tab_adresse)) && ($_SESSION['OFFICIEL']['INFOS_RESPONSABLES']=='oui_force') )
        {
          // En dessous du bloc établissement
          $bloc_titre_largeur = $bloc_etabl_largeur ;
          $this->SetXY( $this->marge_gauche , $this->GetY() + 2 );
          $bloc_titre_hauteur = $this->officiel_bloc_titres($tab_bloc_titres,$alerte_archive,$bloc_titre_largeur);
          $bloc_gauche_hauteur = $bloc_etabl_hauteur + 2 + $bloc_titre_hauteur + 2 ;
        }
        else
        {
          // En haut à droite, modulo la place pour le texte indiquant le nombre de pages
          $bloc_titre_largeur = 100;
          $this->SetXY( $this->page_largeur-$this->marge_droite-$bloc_titre_largeur , $this->marge_haut+4 );
          $bloc_titre_hauteur = $this->officiel_bloc_titres($tab_bloc_titres,$alerte_archive,$bloc_titre_largeur)+4;
          $bloc_gauche_hauteur = $bloc_etabl_hauteur ;
          $bloc_droite_hauteur = $bloc_titre_hauteur ; // temporaire, au cas où il n'y aurait pas d'adresse à ajouter
        }
        // Date de naissance + Tag date heure initiales (sous le bloc titres dans toutes les situations)
        $this->officiel_ligne_tag($date_naissance,$tag_date_heure_initiales,$bloc_titre_largeur);
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
        list( $texte_format , $texte_periode , $groupe_nom ) = $tab_infos_entete;
        $this->doc_titre = 'Bilan '.$texte_format.' - '.$texte_periode;
        $hauteur_entete = 2*4 ; // HG L1 intitulé L2 période ; HD L1 structure L2 élève classe
      }
      
      $this->SetXY($this->marge_gauche,$this->marge_haut+$hauteur_entete);
      $this->taille_police  = 8;
      
      /* Calcul de la hauteur des cellules en fonction de la police de caractères */
      $StringExample = "";
      for ($i = 0 ; $i < 192 ; $i++)
      {
        $StringExample .= "o";
      }
      $this->SetFont($_SESSION["OFFICIEL"]["POLICE"] , '' , $this->taille_police);
      $NbMaxLines = $this->GetNbLines($this->intitule_largeur, $StringExample);
      $this->lignes_hauteur = ($this->taille_police * 1.2 /* Interline */ * 0.3528) * ($NbMaxLines + 0.5);
      $this->lignes_hauteur_courante = $this->lignes_hauteur;

      // Hauteur d'une case
      $this->cases_hauteur = $this->lignes_hauteur;
      $this->calculer_dimensions_images($this->cases_largeur,$this->cases_hauteur);
    }
  }

  public function bilan_item_individuel_transdisciplinaire_ligne_matiere( $matiere_nom , $lignes_nb )
  {
    // La hauteur de ligne a déjà été calculée ; mais il reste à déterminer si on saute une page ou non en fonction de la place restante (et sinon => interligne)
    $hauteur_dispo_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas ;
    $lignes_nb = 1.5 + $lignes_nb ; // matière(marge+intitulé) + lignes dont résumés (on ne compte pas la légende)
    $test_nouvelle_page = ($this->lignes_hauteur*$lignes_nb > $hauteur_dispo_restante) && ($this->GetY() > $this->lignes_hauteur*5) ; // 2e condition pour éviter un saut de page si déjà en haut (à cause d'une liste à rallonge dans une matière)
    if( $test_nouvelle_page )
    {
      if( ($this->legende) && (!$this->legende_deja_affichee) )
      {
        // Si multimatières, on n'est pas certain qu'il y ait la place pour la légende en dernière page, alors on la met dès que possible
        $test_place_legende = ($this->lignes_hauteur*$this->legende_nb_lignes*0.9 < $hauteur_dispo_restante) ;
        if( $test_place_legende )
        {
          $this->bilan_item_individuel_legende();
          $this->legende_deja_affichee = TRUE;
        }
      }
    }
    else
    {
      // Interligne
      $this->SetXY($this->marge_gauche , $this->GetY() + $this->lignes_hauteur*0.5);
    }
    
    // Intitulé matière + éventuellement rappel élève
    if( $test_nouvelle_page )
    {
      $this->bilan_item_individuel_rappel_eleve_page();
    }
    $this->bilan_item_individuel_AjouterTitre($matiere_nom);
    
    $this->ForcerAffichageTitre2 = TRUE;
    
    if ($this->aff_date_reussite == TRUE)
    {
      /* Si on doit afficher la date, l'affichage ne sera effectué qu'à l'appel de bilan_item_individuel_afficher_date_reussite() */
      $this->DelayInProgress = TRUE;
    }
  }

  public function bilan_item_individuel_appreciation_rubrique($tab_saisie)
  {
    $this->SetXY( $this->marge_gauche + $this->reference_largeur , $this->GetY() );
    $this->officiel_bloc_appreciation_intermediaire( $tab_saisie , $this->synthese_largeur , $this->lignes_hauteur , 'releve' , $_SESSION['OFFICIEL']['RELEVE_APPRECIATION_RUBRIQUE'] );
  }

  public function bilan_item_individuel_appreciation_generale( $prof_id , $tab_infos , $tab_image_tampon_signature , $nb_lignes_appreciation_generale_avec_intitule , $nb_lignes_assiduite_et_message_et_legende )
  {
    $hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
    $hauteur_requise = $this->lignes_hauteur * ( $nb_lignes_appreciation_generale_avec_intitule + $nb_lignes_assiduite_et_message_et_legende ) ;
    if($hauteur_requise > $hauteur_restante)
    {
      // Prendre une nouvelle page si ça ne rentre pas, avec recopie de l'identité de l'élève
      $this->bilan_item_individuel_rappel_eleve_page();
      $this->SetXY( $this->marge_gauche+$this->reference_largeur , $this->GetY() + 2 );
    }
    else
    {
      // Interligne
      $this->SetXY($this->marge_gauche+$this->reference_largeur , $this->GetY() + $this->lignes_hauteur*0.5);
    }
    $this->officiel_bloc_appreciation_generale( $prof_id , $tab_infos , $tab_image_tampon_signature , $nb_lignes_appreciation_generale_avec_intitule , $this->synthese_largeur , $this->cases_hauteur , NULL /*moyenne_generale_eleve*/ , NULL /*moyenne_generale_classe*/ );
  }

  public function bilan_item_individuel_debut_ligne_item( $item_ref , $item_texte )
  {
    $Titres = explode(' | ', $item_texte);
    switch (count($Titres))
    {
      case 1:
        /* Pas de titres détecté, on crée la table sans titres */
        $Titre1Actuel = '';
        $Titre2Actuel = '';
        break;
       
      case 2:
        /* Un seul niveau de titre */
        $Titre1Actuel = '';
        $Titre2Actuel = $Titres[0];
        $item_texte = $Titres[1];
        break;
      
      default:
        /* 2 niveaux de titres (ou plus) détectés, on prend seulement les 2 premiers */
        $Titre1Actuel = $Titres[0];
        $Titre2Actuel = $Titres[1];
        $item_texte = $Titres[2];
        for ($i = 3 ; $i < count($Titres) ; $i++)
        {
          $item_texte .= ' | '.$Titres[$i];
        }
        break;
    }

    $hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
    $hauteur_requise = $this->lignes_hauteur;
    if ($Titre1Actuel != $this->TitreNiveau1)
    { 
      /* Il y a deux titres à ajouter */
      $hauteur_requise += $this->HauteurTitre1 + $this->HauteurTitre2 * 2 /* Titre 2 peut être sur 2 lignes */ + $this->HauteurTitreColonne;
    }
    else if ($Titre2Actuel != $this->TitreNiveau2)
    {
      /* Il y a le titre 2 à ajouter */
      $hauteur_requise += $this->HauteurTitre2 * 2 /* Titre 2 peut être sur 2 lignes */ + $this->HauteurTitreColonne;
    }

    if($hauteur_requise > $hauteur_restante)
    {
      // Prendre une nouvelle page si ça ne rentre pas, avec recopie de l'identité de l'élève (y a des bilans avec tellement d'items qu'il faut aussi mettre le test ici...
      $this->bilan_item_individuel_rappel_eleve_page();
      $NouvellePageCree = true;      
    }
    else 
    {
      $NouvellePageCree = false;
    }
    
    if ($Titre1Actuel != $this->TitreNiveau1)
    {
      /* Le titre 1 a changé, il faut créer le titre 1 et le titre 2 */
      $this->bilan_item_individuel_AjouterTitre1($Titre1Actuel);
      $this->bilan_item_individuel_AjouterTitre2($Titre2Actuel);
      $this->ForcerAffichageTitre2 = FALSE;
    }
    else if ($Titre2Actuel != $this->TitreNiveau2 || $NouvellePageCree == true || $this->ForcerAffichageTitre2 == TRUE)
    {
      /* Le titre 2 a changé, ou on a créé une nouvelle page, on répète le titre 2 */
      $this->bilan_item_individuel_AjouterTitre2($Titre2Actuel);
      $this->ForcerAffichageTitre2 = FALSE;
    }
    
    list($ref_matiere,$ref_suite) = explode('.',$item_ref,2);
    
    $this->SetFont($_SESSION["OFFICIEL"]["POLICE"] , '' , $this->taille_police);
    $NbLines = $this->GetNbLines($this->intitule_largeur, $item_texte);
    $ligne_hauteur_min = ($this->taille_police * 1.2 /* Interline */ * 0.3528) * ($NbLines + 0.5);
    $this->cases_hauteur = max($ligne_hauteur_min, $this->lignes_hauteur_courante);
    $this->lomer_espace_hauteur = $this->cases_hauteur;
    
    $this->bilan_item_individuel_AjouterReference($ref_suite);

    $this->bilan_item_individuel_AjouterItem($item_texte);
  }
  
  public function bilan_item_individuel_ligne_synthese($bilan_texte)
  {
    $this->SetFont($_SESSION["OFFICIEL"]["POLICE"] , '' , $this->taille_police);
    $this->choisir_couleur_fond('gris_moyen');
    /* Ajouter l'interligne double */
    $CellSize = $this->taille_police * 2.5;
    /* Convertir les Points en millimètres */
    $CellSize = $CellSize * 0.3528;
    $this->Cell( $this->reference_largeur , $CellSize , ''                    , 0 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
    $this->Cell( $this->synthese_largeur  , $CellSize , To::pdf($bilan_texte) , 1 /*bordure*/ , 1 /*br*/ , 'R' /*alignement*/ , TRUE  /*remplissage*/ );
  }

  public function bilan_item_individuel_legende()
  {
    if(!$this->legende_deja_affichee)
    {
      // Légende : à la suite si 'matiere' ou 'selection' ou 'professeur' , en bas de page si 'multimatiere',
      $ordonnee = ( ($this->format!='multimatiere') ) ? $this->GetY() + $this->lignes_hauteur*0.2 : $this->page_hauteur - $this->marge_bas - $this->lignes_hauteur*$this->legende_nb_lignes*0.9 ;
      if($this->aff_codes_notation && $this->cases_nb > 0)
      {
        $this->afficher_legende( 'codes_notation'      /*type_legende*/ , $ordonnee     /*ordonnée*/ );
        $ordonnee = $this->GetY();
      } /*toujours TRUE*/
      if($this->aff_anciennete_notation)
      {
        $this->afficher_legende( 'anciennete_notation' /*type_legende*/ , $ordonnee /*ordonnée*/ );
        $ordonnee = $this->GetY();
      }
      if($this->aff_etat_acquisition)
      {
        $this->afficher_legende( 'score_bilan'         /*type_legende*/ , $ordonnee /*ordonnée*/ );
      }
    }
  }

  public function bilan_item_individuel_afficher_date_reussite($date, $background)
  {
    $this->SetFont($_SESSION["OFFICIEL"]["POLICE"] , '' , $this->taille_police);
    /* On recalcule la taille de l'intitulé pour ajouter, ou non, la colonne de la date */
    if ( $background == "not_available" )
    {
      $this->intitule_largeur = $this->synthese_largeur - ( $this->cases_nb * $this->cases_largeur ) - $this->colonne_bilan_largeur;
      $this->aff_colonne_date_reussite = FALSE;
    }
    else 
    {
      $this->intitule_largeur = $this->synthese_largeur - ( $this->cases_nb * $this->cases_largeur ) - $this->colonne_bilan_largeur - $this->case_date_largeur;
      $this->aff_colonne_date_reussite = TRUE;
    }
    
    /* Si les commandes sont en attente, on les exécute */
    if ($this->DelayInProgress == TRUE)
    {
      $this->ExecuteDelayedCommands();
    }
    
    /* On crée la cellule seulement si la date doit être affichée */
    if ( $background != "not_available" )
    {
      $this->Cell( $this->case_date_largeur , $this->cases_hauteur , $date , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
    }
  }
  
  public function afficher_score_bilan( $score , $br )
  {
    $previous_cases_largeur = $this->cases_largeur;
    $this->cases_largeur = $this->case_resultat_largeur;
    parent::afficher_score_bilan( $score , $br );
    $this->cases_largeur = $previous_cases_largeur;
  }
  
  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthode pour afficher la légende ( $type_legende = 'codes_notation' | 'anciennete_notation' | 'etat_acquisition' | 'pourcentage_acquis' | 'etat_validation' )
  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  
  public function afficher_legende( $type_legende , $ordonnee , $force_nb = FALSE )
  {
    $espace  = '     ';
    $espace_mini  = '   ';
    $hauteur = min(4,$this->lignes_hauteur*0.9);
    $size    = ceil($hauteur * 1.6);
    $this->SetXY($this->marge_gauche , $ordonnee);
    $case_hauteur = $hauteur*0.9;
    $case_largeur = $hauteur*0.9*1.5;
    // Afficher la légende des codes de notation
    if($type_legende=='codes_notation')
    {
      // Le texte des codes de notation étant personnalisable, il peut falloir condenser en largeur...
      $OrdreAffichageNote = array('RR', 'R', 'V', 'VV');
      $CalculValeurActuelle = -1;
      $boites_nb = 0;
      $texte = 'Codes d\'évaluation :';
      foreach ($OrdreAffichageNote as $Note)
      {
        if ($CalculValeurActuelle < $_SESSION['CALCUL_VALEUR'][$Note])
        {
          $old_texte = $texte;
          $CalculValeurActuelle = $_SESSION['CALCUL_VALEUR'][$Note];
          $texte .= $espace.$_SESSION['NOTE_LEGENDE'][$Note];
          $tab_codes_normaux[$boites_nb] = $Note;
          $boites_nb ++;
        }
        else 
        {
          $texte = $old_texte.$espace.$_SESSION['NOTE_LEGENDE'][$Note];
          $tab_codes_normaux[$boites_nb - 1] = $Note;
        }
      }
      
      foreach($this->tab_legende_notes_speciales_nombre as $note => $nombre)
      {
        if($nombre)
        {
          $texte .= $espace.$this->tab_legende_notes_speciales_texte[$note];
          $boites_nb++;
        }
      }
      $largeur_dispo_pour_texte = $this->page_largeur_moins_marges - $boites_nb*$this->lomer_espace_largeur;
  
      $largeur_texte = $this->GetStringWidth($texte);
      $ratio = min( 1 , $largeur_dispo_pour_texte / $largeur_texte );
      // On y va maintenant
  
      $this->SetFont($_SESSION["OFFICIEL"]["POLICE"] , 'B' , $size);
      $this->Write($hauteur , To::pdf('Codes d\'évaluation :') , '');
      $this->SetFont($_SESSION["OFFICIEL"]["POLICE"] , '' , $size);
      $memo_lomer_espace_largeur = $this->lomer_espace_largeur;
      $memo_lomer_espace_hauteur = $this->lomer_espace_hauteur;
      $memo_taille_police = $this->taille_police;
      $this->taille_police = $size; // On est obligé de le changer provisoirement car, si impression N&B, afficher_note_lomer() l'utilise
      $this->calculer_dimensions_images($case_largeur,$case_hauteur);
      foreach($tab_codes_normaux as $code)
      {
        $texte = $_SESSION['NOTE_LEGENDE'][$code];
        $largeur = $this->GetStringWidth($texte)*$ratio*1.1;
        $this->Write($hauteur , $espace_mini , '');
        $this->afficher_note_lomer($code, 1 /*border*/ , 0 /*br*/ );
        $this->CellFit( $largeur , $hauteur , To::pdf($texte) , 0 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
        // $this->Write($hauteur , To::pdf($_SESSION['NOTE_LEGENDE'][$code]) , '');
      }
      foreach($this->tab_legende_notes_speciales_nombre as $note => $nombre)
      {
        if($nombre)
        {
          $texte = $this->tab_legende_notes_speciales_texte[$note];
          $largeur = $this->GetStringWidth($texte)*$ratio*1.1;
          $this->Write($hauteur , $espace_mini , '');
          $this->afficher_note_lomer($note, 1 /*border*/ , 0 /*br*/ );
          $this->CellFit( $largeur , $hauteur , To::pdf($texte) , 0 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
          // $this->Write($hauteur , To::pdf($this->tab_legende_notes_speciales_texte[$note]) , '');
        }
      }
      $this->legende_initialiser();
      $this->calculer_dimensions_images($memo_lomer_espace_largeur,$memo_lomer_espace_hauteur);
      $this->taille_police = $memo_taille_police;
    }
    // Afficher la légende de l'ancienneté de la notation
    if($type_legende=='anciennete_notation')
    {
      $this->SetFont($_SESSION["OFFICIEL"]["POLICE"] , 'B' , $size);
      $this->Write($hauteur , To::pdf('Ancienneté :') , '');
      $this->SetFont($_SESSION["OFFICIEL"]["POLICE"] , '' , $size);
      $tab_etats = array('blanc'=>'Sur la période.','gris_moyen'=>'Début d\'année scolaire.','gris_fonce'=>'Année scolaire précédente.');
      foreach($tab_etats as $couleur => $texte)
      {
        $this->Write($hauteur , $espace , '');
        $this->choisir_couleur_fond($couleur);
        $this->Cell($case_largeur , $case_hauteur , '' , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
        $this->Write($hauteur , To::pdf($texte) , '');
      }
    }
    // Afficher la légende des scores bilan
    if($type_legende=='score_bilan')
    {
      // Pour un bulletin on prend les droits du profil parent, surtout qu'il peut être imprimé par un administrateur (pas de droit paramétré pour lui).
      $afficher_score = test_user_droit_specifique( $_SESSION['DROIT_VOIR_SCORE_BILAN'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , 0 /*matiere_id_or_groupe_id_a_tester*/ , (bool)$this->officiel /*forcer_parent*/ );
      $this->SetFont($_SESSION["OFFICIEL"]["POLICE"] , 'B' , $size);
      $this->Write($hauteur , To::pdf('États d\'acquisitions :') , '');
      $this->SetFont($_SESSION["OFFICIEL"]["POLICE"] , '' , $size);
      $seuil_NA = ( $afficher_score && ($_SESSION['CALCUL_SEUIL']['R']>0)   ) ? '0 à '.($_SESSION['CALCUL_SEUIL']['R']-1)   : '' ;
      $seuil_A  = ( $afficher_score && ($_SESSION['CALCUL_SEUIL']['V']<100) ) ? ($_SESSION['CALCUL_SEUIL']['V']+1).' à 100' : '' ;
      $seuil_VA = ( $afficher_score && ($_SESSION['CALCUL_SEUIL']['R']!=$_SESSION['CALCUL_SEUIL']['V']) ) ? $_SESSION['CALCUL_SEUIL']['R'].' à '.$_SESSION['CALCUL_SEUIL']['V'] : '' ;
      $tab_seuils = array( 'NA'=>$seuil_NA, 'VA'=>$seuil_VA, 'A'=>$seuil_A );
      foreach($tab_seuils as $etat => $texte)
      {
        $this->Write($hauteur , $espace , '');
        $this->choisir_couleur_fond($this->tab_choix_couleur[$this->couleur][$etat]);
        $this->Cell(2*$case_largeur , $case_hauteur , To::pdf($texte) , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
        $this->Write($hauteur , To::pdf($_SESSION['ACQUIS_LEGENDE'][$etat]) , '');
      }
    }
    // Afficher la légende des états d'acquisition
    if($type_legende=='etat_acquisition')
    {
      $this->SetFont($_SESSION["OFFICIEL"]["POLICE"] , 'B' , $size);
      $this->Write($hauteur , To::pdf('États d\'acquisitions :') , '');
      $this->SetFont($_SESSION["OFFICIEL"]["POLICE"] , '' , $size);
      $tab_etats = array('NA','VA','A');
      foreach($tab_etats as $etat)
      {
        $this->Write($hauteur , $espace , '');
        $couleur_fond = (!$force_nb) ? $this->tab_choix_couleur[$this->couleur][$etat] : 'blanc' ;
        $this->choisir_couleur_fond($couleur_fond);
        $this->Cell($case_largeur , $case_hauteur , To::pdf($_SESSION['ACQUIS_TEXTE'][$etat]) , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
        $this->Write($hauteur , To::pdf($_SESSION['ACQUIS_LEGENDE'][$etat]) , '');
      }
    }
    // Afficher la légende des pourcentages d'items acquis
    if($type_legende=='pourcentage_acquis')
    {
      $this->SetFont($_SESSION["OFFICIEL"]["POLICE"] , 'B' , $size);
      $indication_position = ($this->orientation=='portrait') ? ' (à gauche)' : '' ;
      $this->Write($hauteur , To::pdf('Pourcentages d\'items acquis'.$indication_position.' :') , '');
      $this->SetFont($_SESSION["OFFICIEL"]["POLICE"] , '' , $size);
      $tab_seuils = array('NA'=>'0 à '.$_SESSION['CALCUL_SEUIL']['R'],'VA'=>$_SESSION['CALCUL_SEUIL']['R'].' à '.$_SESSION['CALCUL_SEUIL']['V'],'A'=>$_SESSION['CALCUL_SEUIL']['V'].' à 100');
      foreach($tab_seuils as $etat => $texte)
      {
        $this->Write($hauteur , $espace , '');
        $this->choisir_couleur_fond($this->tab_choix_couleur[$this->couleur][$etat]);
        $this->Cell(3*$case_largeur , $case_hauteur , To::pdf($texte) , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
      }
    }
    // Afficher la légende des états de validation
    if($type_legende=='etat_validation')
    {
      $this->SetFont($_SESSION["OFFICIEL"]["POLICE"] , 'B' , $size);
      $indication_position = ($this->orientation=='portrait') ? ' (à droite)' : '' ;
      $this->Write($hauteur , To::pdf('États de validation'.$indication_position.' :') , '');
      $this->SetFont($_SESSION["OFFICIEL"]["POLICE"] , '' , $size);
      $tab_etats = array('v1'=>'Validé','v0'=>'Invalidé','v2'=>'Non renseigné');
      foreach($tab_etats as $etat => $texte)
      {
        $this->Write($hauteur , $espace , '');
        $this->choisir_couleur_fond($this->tab_choix_couleur[$this->couleur][$etat]);
        $this->Cell(3.5*$case_largeur , $case_hauteur , To::pdf($texte) , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
      }
    }
    $this->SetXY($this->marge_gauche , $ordonnee+$hauteur);
  }
  
  public function afficher_note_lomer( $note , $border , $br , $fill='' )
  {
    if ($this->DelayInProgress == TRUE)
    {
      $this->DelayCommand('afficher_note_lomer', array($note, $border, $br, $fill));
    }
    else
    {
      parent::afficher_note_lomer($note, $border, $br, $fill);
    }
  }
  
  private function bilan_item_individuel_AjouterTitre($NouveauTitre)
  {
    $this->SetFont($_SESSION["OFFICIEL"]["POLICE"] , 'B' , 16);
    $NouveauTitre = preg_replace('#^[ 0-9.]*(.*)$#', '$1', $NouveauTitre);
    $this->Cell($this->page_largeur_moins_marges , 7 , To::pdf($NouveauTitre) , 0 /*bordure*/ , 1 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
  }
  
  private function bilan_item_individuel_AjouterTitre1($NouveauTitre)
  {
    $this->TitreNiveau1 = $NouveauTitre;
    $this->SetFont($_SESSION["OFFICIEL"]["POLICE"] , 'B' , 12);
    $this->Cell($this->page_largeur_moins_marges , ($this->HauteurTitre1 - 5) / 2 , "" , 0 /*bordure*/ , 1 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
    $this->AddMultiCell($this->page_largeur_moins_marges, 5 , To::pdf($NouveauTitre) , 0 /*bordure*/ , 1 /*br*/ , 'LT' /*alignement*/ , FALSE /*remplissage*/, TRUE /* AutoFit */ );
    $this->Cell($this->page_largeur_moins_marges , ($this->HauteurTitre1 - 5) / 2  , "" , 0 /*bordure*/ , 1 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
  }
  
  private function bilan_item_individuel_AjouterTitre2($NouveauTitre)
  {
    if ($this->DelayInProgress == TRUE)
    {
      $this->DelayCommand('bilan_item_individuel_AjouterTitre2', array($NouveauTitre));
    }
    else
    {
      $this->TitreNiveau2 = $NouveauTitre;
      $this->SetFont($_SESSION["OFFICIEL"]["POLICE"] , 'B' , 12);
      if ($NouveauTitre != '')
      {
        $this->choisir_couleur_fond('gris_clair');
    
        $this->AddMultiCell($this->page_largeur_moins_marges, $this->HauteurTitre2 , To::pdf($NouveauTitre) , 1 /*bordure*/ , 1 /*br*/ , 'LM' /*alignement*/ , TRUE /*remplissage*/ , TRUE /* AutoFit */ );
      }
      
      /* Créer les titres des colonnes */
      $this->choisir_couleur_fond('blanc');
      $this->AddMultiCell($this->reference_largeur, $this->HauteurTitreColonne , To::pdf("Code de\nl'épreuve") , 1 /*bordure*/ , 0 /*br*/ , 'CM' /*alignement*/ , TRUE /*remplissage*/ );
      $this->AddMultiCell($this->intitule_largeur, $this->HauteurTitreColonne , To::pdf("Objectifs évalués") , 1 /*bordure*/ , 0 /*br*/ , 'CM' /*alignement*/ , TRUE /*remplissage*/ );
      if ($this->cases_nb > 0)
      {
        $this->AddMultiCell($this->cases_largeur * $this->cases_nb, $this->HauteurTitreColonne , To::pdf("Evaluations") , 1 /*bordure*/ , 0 /*br*/ , 'CM' /*alignement*/ , TRUE /*remplissage*/ );
      }
      if ($this->aff_colonne_date_reussite == true)
      {
        $this->AddMultiCell($this->case_date_largeur , $this->HauteurTitreColonne , To::pdf("Date d'\nacquisition") , 1 /*bordure*/ , 0 /*br*/ , 'CM' /*alignement*/ , TRUE /*remplissage*/ );
      }
      if ($this->aff_etat_acquisition == true)
      {
        $this->AddMultiCell($this->colonne_bilan_largeur , $this->HauteurTitreColonne , To::pdf("Résultat") , 1 /*bordure*/ , 0 /*br*/ , 'CM' /*alignement*/ , TRUE /*remplissage*/ );
      }
      /* Ajouter le passage à la ligne */
      $this->Cell( 1 , $this->HauteurTitreColonne , "" , 0 /*bordure*/ , 1 /*br*/ , 'C' /*alignement*/ , FALSE  /*remplissage*/ );
    }
  }
  
  private function bilan_item_individuel_AjouterReference($ref_suite)
  {
    if ($this->DelayInProgress == TRUE)
    {
      $this->DelayCommand('bilan_item_individuel_AjouterReference', array($ref_suite));
    }
    else
    {
      $this->SetFont($_SESSION["OFFICIEL"]["POLICE"] , '' , 12);
      $NbLines = $this->GetNbLines($this->reference_largeur, To::pdf($ref_suite));
      if ($NbLines > 1)
      {
        $ref_suite = preg_replace("/\./", ".\n", $ref_suite);
      }
      $this->choisir_couleur_fond('blanc');
      $this->AddMultiCell( $this->reference_largeur , $this->cases_hauteur , To::pdf($ref_suite) , 1 /*bordure*/ , 0 /*br*/ , 'CM' /*alignement*/ , TRUE  /*remplissage*/ );
    }
  }
  
  private function bilan_item_individuel_AjouterItem($item_texte)
  {
    if ($this->DelayInProgress == TRUE)
    {
      $this->DelayCommand('bilan_item_individuel_AjouterItem', array($item_texte));
    }
    else 
    {
      $this->SetFont($_SESSION["OFFICIEL"]["POLICE"] , '' , $this->taille_police);
      $this->AddMultiCell($this->intitule_largeur, $this->cases_hauteur, To::pdf($item_texte), 1 /*bordure*/ , 0 /*br*/ , 'CM' /*alignement*/ , TRUE  /*remplissage*/ );
    }
  }
  
  private function DelayCommand($CommandName, $Arguments)
  {
    $this->DelayedCommandList[count($this->DelayedCommandList)] = array(Command => $CommandName, Args => $Arguments);
  }
  
  private function ExecuteDelayedCommands()
  {
    $this->DelayInProgress = FALSE;
    
    for ($Id = 0 ; $Id < count($this->DelayedCommandList) ; $Id ++)
    {
      switch ($this->DelayedCommandList[$Id][Command])
      {
        case bilan_item_individuel_AjouterItem:
          $this->bilan_item_individuel_AjouterItem($this->DelayedCommandList[$Id][Args][0]);
          break;
          
        case bilan_item_individuel_AjouterReference:
          $this->bilan_item_individuel_AjouterReference($this->DelayedCommandList[$Id][Args][0]);
          break;
          
        case bilan_item_individuel_AjouterTitre2:
          $this->bilan_item_individuel_AjouterTitre2($this->DelayedCommandList[$Id][Args][0]);
          break;
          
        case afficher_note_lomer:
          $this->afficher_note_lomer($this->DelayedCommandList[$Id][Args][0], $this->DelayedCommandList[$Id][Args][1], $this->DelayedCommandList[$Id][Args][2], $this->DelayedCommandList[$Id][Args][3]);
          break;
      }
    }
    array_splice($this->DelayedCommandList, 0);
  }
  
  private $ForcerAffichageTitre2 = FALSE;
  private $TitreNiveau1 = '';
  private $TitreNiveau2 = '';
  private $HauteurTitre = 12;
  private $HauteurTitre1 = 15;
  private $HauteurTitre2 = 7;
  private $HauteurTitreColonne = 12;
  private $aff_date_reussite = false;
  private $aff_colonne_date_reussite = false;
  private $DelayedCommandList;
  private $DelayInProgress = FALSE;
  private $case_date_largeur = 20;
  private $lignes_hauteur_courante = 20;
}

?>