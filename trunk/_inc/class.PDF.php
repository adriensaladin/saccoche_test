<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009
 * 
 * ****************************************************************************************************
 * SACoche [http://competences.sesamath.net] - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath [http://www.sesamath.net]
 * Distribution sous licence libre prévue pour l'été 2010.
 * ****************************************************************************************************
 * 
 * Extension de classe de FPDF
 * 
 */

class PDF extends FPDF
{

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	//	Attributs de la classe (équivalents des "variables")
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

	// Couleurs utilisées pour la mise en page PDF
	private static $tab_couleur = array(
		'rouge_clair' => array('r'=>255,'v'=>100,'b'=>100),
		'rouge_fonce' => array('r'=>255,'v'=>50 ,'b'=>50 ),
		'vert_clair' => array('r'=>100,'v'=>255,'b'=>100),
		'vert_fonce' => array('r'=>50 ,'v'=>255,'b'=>50 ),
		'jaune_clair' => array('r'=>255,'v'=>255,'b'=>150),
		'jaune_fonce' => array('r'=>255,'v'=>255,'b'=>50 ),
		'gris_clair' => array('r'=>230,'v'=>230,'b'=>230),
		'gris_fonce' => array('r'=>200,'v'=>200,'b'=>200),
		'blanc' => array('r'=>255,'v'=>255,'b'=>255)
	);
	// Valeurs des marges principales pour la mise en page PDF
	private $orientation   = '';
	private $marge_min     = 5;
	private $couleur       = 'oui';
	private $page_largeur  = 0;
	private $page_hauteur  = 0;
	private $marge_haut    = 0;
	private $marge_gauche  = 0;
	private $marge_droit   = 0;
	private $marge_bas     = 0;
	private $distance_pied = 0;
	// Conserver les informations de l'élève pour une recopie sur plusieurs pages
	private $eleve_id     = 0;
	private $eleve_nom    = '';
	private $eleve_prenom = '';
	// Définition de qqs variables supplémentaires
	private $cases_nb          = 0;
	private $cases_largeur     = 0;
	private $cases_hauteur     = 0;
	private $lignes_nb         = 0;
	private $reference_largeur = 0;
	private $intitule_largeur  = 0;
	private $synthese_largeur  = 0;

	private $pilier_largeur      = 0;
	private $section_largeur     = 0;
	private $item_largeur        = 0;
	private $attestation_largeur = 0;

	private $eleve_largeur     = 0;
	private $taille_police     = 8;

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	//	Méthode Magique - Constructeur
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

	public function __construct($orientation,$marge_min,$couleur)
	{
		// Appeler le constructeur de la classe mère
		parent::FPDF($orientation , $unit='mm' , $format='A4');
		// On passe à la classe fille
		$this->orientation = $orientation;
		$this->marge_min   = $marge_min;
		$this->couleur     = $couleur;
		// initialiser les marges principales
		if($orientation=='landscape')
		{
			$this->page_largeur  = 297;
			$this->page_hauteur  = 210;
			$this->marge_haut    = max(5,$marge_min);
			$this->marge_gauche  = max(5,$marge_min);
			$this->marge_droit   = 12;
			$this->marge_bas     = max(10,$marge_min);
			$this->distance_pied = 7;
		}
		else
		{
			$this->page_largeur  = 210;
			$this->page_hauteur  = 297;
			$this->marge_haut    = max(5,$marge_min);
			$this->marge_gauche  = max(5,$marge_min);
			$this->marge_droit   = max(5,$marge_min);
			$this->marge_bas     = 12;
			$this->distance_pied = 9;
		}
	}

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	//	Méthode Magique - Pour récupérer un attribut private (c'est comme s'il était en lecture seule)
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

	public function __get($nom)
	{
		return (isset($this->$nom)) ? $this->$nom : null ;
	}

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	//	Méthode Magique - Pour affecter une valeur à un attribut
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

	public function __set($nom,$valeur)
	{
			$this->$nom = $valeur;
	}

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	//	Méthodes pour choisir une couleur de fond ou une couleur de tracé
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

	public function choisir_couleur_fond($couleur)
	{
		$this->SetFillColor(self::$tab_couleur[$couleur]['r'] , self::$tab_couleur[$couleur]['v'] , self::$tab_couleur[$couleur]['b']);
	}

	public function choisir_couleur_trait($couleur)
	{
		$this->SetDrawColor(self::$tab_couleur[$couleur]['r'] , self::$tab_couleur[$couleur]['v'] , self::$tab_couleur[$couleur]['b']);
	}

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	//	Méthodes pour la mise en page d'une grille sur un niveau
	//	grille_niveau_initialiser() grille_niveau_entete() grille_niveau_domaine() grille_niveau_theme() grille_niveau_competence()
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

	public function grille_niveau_initialiser($cases_nb,$cases_largeur,$cases_hauteur)
	{
		$this->cases_nb          = $cases_nb;
		$this->cases_largeur     = $cases_largeur;
		$this->cases_hauteur     = $cases_hauteur;
		$this->reference_largeur = 10;
		$this->intitule_largeur  = $this->page_largeur - $this->marge_gauche - $this->marge_droit - $this->reference_largeur - ($this->cases_nb * $this->cases_largeur);
		$this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droit);
		$this->SetAutoPageBreak(false);
	}

	public function grille_niveau_entete($matiere_nom,$niveau_nom,$eleve_id,$eleve_nom,$eleve_prenom)
	{
		// On prend une nouvelle page PDF pour chaque élève
		$this->AddPage($this->orientation , 'A4');
		// Intitulé
		$this->SetFont('Arial' , 'B' , 12);
		$this->SetXY($this->marge_gauche,$this->marge_haut);
		$this->Cell($this->page_largeur-$this->marge_droit-75 , 5 , pdf('Livret de connaissances et de compétences') , 0 , 2 , 'L' , false , '');
		$this->Cell($this->page_largeur-$this->marge_droit-75 , 5 , pdf($matiere_nom.' - Niveau '.$niveau_nom) , 0 , 2 , 'L' , false , '');
		// Nom prénom
		$this->SetFont('Arial' , '' , 12);
		$this->SetXY($this->page_largeur-$this->marge_droit-70 , $this->marge_haut);
		$this->Cell(20 , 5 , pdf('Nom :') , 0 , 2 , 'R' , false , '');
		$this->Cell(20 , 5 , pdf('Prénom :') , 0 , 2 , 'R' , false , '');
		// On met le document au nom de l'élève, ou on établit un document générique
		if($eleve_id)
		{
			$this->SetFont('Arial' , 'B' , 12);
			$this->SetXY($this->page_largeur-$this->marge_droit-50 , $this->marge_haut);
			$this->Cell(50 , 5 , pdf($eleve_nom) , 0 , 2 , 'L' , false , '');
			$this->Cell(50 , 5 , pdf($eleve_prenom) , 0 , 2 , 'L' , false , '');
		}
		else
		{
			$this->choisir_couleur_trait('gris_fonce');
			$this->SetLineWidth(0.1);
			$this->Line($this->page_largeur-$this->marge_droit-50 , $this->marge_haut+5 , $this->page_largeur-$this->marge_droit , $this->marge_haut+5);
			$this->Line($this->page_largeur-$this->marge_droit-50 , $this->marge_haut+10 , $this->page_largeur-$this->marge_droit , $this->marge_haut+10);
			$this->SetXY($this->marge_gauche , $this->marge_haut+15);
			$this->SetDrawColor(0 , 0 , 0);
		}
	}

	public function grille_niveau_domaine($domaine_nom,$domaine_nb_lignes)
	{
		$hauteur_requise = $this->cases_hauteur * $domaine_nb_lignes;
		$hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
		if($hauteur_requise > $hauteur_restante)
		{
			// Prendre une nouvelle page si ça ne rentre pas
			$this->AddPage($this->orientation , 'A4');
		}
		$this->SetFont('Arial' , 'B' , 10);
		$this->SetXY(15 , $this->GetY()+1);
		$this->Cell($this->intitule_largeur , $this->cases_hauteur , pdf($domaine_nom) , 0 , 1 , 'L' , false , '');
	}

	public function grille_niveau_theme($theme_ref,$theme_nom,$theme_nb_lignes)
	{
		$hauteur_requise = $this->cases_hauteur * $theme_nb_lignes;
		$hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
		if($hauteur_requise > $hauteur_restante)
		{
			// Prendre une nouvelle page si ça ne rentre pas
			$this->AddPage($this->orientation , 'A4');
		}
		$this->SetFont('Arial' , 'B' , 8);
		$this->choisir_couleur_fond('gris_fonce');
		$this->Cell($this->reference_largeur , $this->cases_hauteur , pdf($theme_ref) , 1 , 0 , 'C' , true , '');
		$this->Cell($this->intitule_largeur , $this->cases_hauteur , pdf($theme_nom) , 1 , 1 , 'L' , true , '');
		$this->SetFont('Arial' , '' , 8);
	}

	public function grille_niveau_competence($competence_ref,$competence_texte)
	{
		$this->choisir_couleur_fond('gris_clair');
		$this->Cell($this->reference_largeur , $this->cases_hauteur , pdf($competence_ref) , 1 , 0 , 'C' , true , '');
		$this->Cell($this->intitule_largeur , $this->cases_hauteur , pdf($competence_texte) , 1 , 0 , 'L' , false , '');
		$this->choisir_couleur_fond('blanc');
	}

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	//	Méthodes pour la mise en page d'un releve d'attestation de socle commun
	//	releve_socle_initialiser() releve_socle_entete() releve_socle_pilier() releve_socle_section() releve_socle_item()
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

	public function releve_socle_initialiser($detail,$test_affichage_scores)
	{
		$this->cases_hauteur       = 4;
		$this->taille_police       = 6;
		$this->attestation_largeur = 27.5;
		$retrait_attestation       = ( ($detail=='complet') && $test_affichage_scores ) ? $this->attestation_largeur : 0;
		$this->item_largeur        = $this->page_largeur - $this->marge_gauche - $this->marge_droit - $retrait_attestation;
		$this->section_largeur     = $this->item_largeur - $this->attestation_largeur;
		$this->pilier_largeur      = $this->section_largeur - $this->attestation_largeur;
		$this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droit);
		$this->AddFont('ArialNarrow');
	}

	public function releve_socle_identite()
	{
		// On met le document au nom de l'élève, ou on établit un document générique
		if($this->eleve_id)
		{
			$this->SetFont('Arial' , 'B' , 10);
			$this->SetXY($this->page_largeur-$this->marge_droit-50 , $this->marge_haut);
			$this->Cell(50 , 5 , pdf($this->eleve_nom) , 0 , 2 , 'L' , false , '');
			$this->Cell(50 , 5 , pdf($this->eleve_prenom) , 0 , 2 , 'L' , false , '');
		}
		else
		{
			$this->SetFont('Arial' , '' , 10);
			$this->SetXY($this->page_largeur-$this->marge_droit-70 , $this->marge_haut);
			$this->Cell(20 , 5 , pdf('Nom :') , 0 , 2 , 'R' , false , '');
			$this->Cell(20 , 5 , pdf('Prénom :') , 0 , 2 , 'R' , false , '');
			$this->choisir_couleur_trait('gris_fonce');
			$this->SetLineWidth(0.1);
			$this->Line($this->page_largeur-$this->marge_droit-50 , $this->marge_haut+5 , $this->page_largeur-$this->marge_droit , $this->marge_haut+5);
			$this->Line($this->page_largeur-$this->marge_droit-50 , $this->marge_haut+10 , $this->page_largeur-$this->marge_droit , $this->marge_haut+10);
			$this->SetXY($this->marge_gauche , $this->marge_haut+15);
			$this->SetDrawColor(0 , 0 , 0);
		}
	}

	public function releve_socle_entete($palier_nom,$eleve_id,$eleve_nom,$eleve_prenom)
	{
		// On prend une nouvelle page PDF pour chaque élève
		$this->AddPage($this->orientation , 'A4');
		// Intitulé
		$this->SetFont('Arial' , 'B' , 10);
		$this->SetXY($this->marge_gauche,$this->marge_haut);
		$this->Cell($this->page_largeur-$this->marge_droit-75 , 5 , pdf('Attestation de maîtrise du socle commun') , 0 , 2 , 'L' , false , '');
		$this->Cell($this->page_largeur-$this->marge_droit-75 , 5 , pdf($palier_nom) , 0 , 2 , 'L' , false , '');
		// Nom / prénom
		$this->eleve_id     = $eleve_id;
		$this->eleve_nom    = $eleve_nom;
		$this->eleve_prenom = $eleve_prenom;
		$this->releve_socle_identite();
	}

	public function releve_socle_pilier($pilier_nom,$pilier_nb_lignes,$test_affichage_scores,$tab_pilier_score)
	{
		$this->SetXY($this->marge_gauche , $this->GetY()+2);
		$hauteur_requise = $this->cases_hauteur * $pilier_nb_lignes;
		$hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
		if($hauteur_requise > $hauteur_restante)
		{
			// Prendre une nouvelle page si ça ne rentre pas, avec recopie de l'identité de l'élève
			$this->AddPage($this->orientation , 'A4');
			$this->releve_socle_identite();
			$this->SetXY($this->marge_gauche , $this->GetY()+2);
		}
		$this->SetFont('Arial' , 'B' , $this->taille_police + 1);
		$this->choisir_couleur_fond('gris_fonce');
		$br = $test_affichage_scores ? 0 : 1 ;
		$this->Cell($this->pilier_largeur , $this->cases_hauteur , pdf($pilier_nom) , 1 , $br , 'L' , true , '');
		if($test_affichage_scores)
		{
			$this->afficher_validation_socle('B',$tab_pilier_score);
		}
	}

	public function releve_socle_section($section_nom,$test_affichage_scores,$tab_section_score)
	{
		$this->SetFont('Arial' , 'B' , $this->taille_police);
		$this->choisir_couleur_fond('gris_fonce');
		$br = $test_affichage_scores ? 0 : 1 ;
		$this->Cell($this->section_largeur , $this->cases_hauteur , pdf($section_nom) , 1 , $br , 'L' , true , '');
		if($test_affichage_scores)
		{
			$this->afficher_validation_socle('B',$tab_section_score);
		}
	}

	public function releve_socle_item($item_nom,$test_affichage_scores,$tab_item_score)
	{
		$font = (mb_strlen($item_nom)<175) ? 'Arial' : 'ArialNarrow' ;
		$this->SetFont($font , '' , $this->taille_police);
		$this->choisir_couleur_fond('gris_clair');
		$br = $test_affichage_scores ? 0 : 1 ;
		$this->Cell($this->item_largeur , $this->cases_hauteur , pdf($item_nom) , 1 , $br , 'L' , true , '');
		if($test_affichage_scores)
		{
			$this->afficher_validation_socle('',$tab_item_score);
		}
	}

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	//	Méthodes pour la mise en page d'un bilan individuel sur une période
	//	bilan_periode_individuel_initialiser() bilan_periode_individuel_entete() bilan_periode_individuel_competence() bilan_periode_individuel_synthese() bilan_periode_individuel_interligne()
	//	Méthodes supplémentaires pour la mise en page d'un bilan individuel transdisciplinaire sur une période
	//	bilan_periode_individuel_entete_transdisciplinaire_principal() bilan_periode_individuel_entete_transdisciplinaire_secondaire()
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

	public function bilan_periode_individuel_initialiser($cases_nb,$cases_largeur,$cases_hauteur,$lignes_nb,$new_page)
	{
		$this->cases_nb          = $cases_nb;
		$this->cases_largeur     = $cases_largeur;
		$this->cases_hauteur     = $cases_hauteur;
		$this->lignes_nb         = $lignes_nb;
		$this->reference_largeur = 10;
		$this->intitule_largeur  = $this->page_largeur - $this->marge_gauche - $this->marge_droit - $this->reference_largeur - (($this->cases_nb+1) * $this->cases_largeur);
		$this->synthese_largeur  = $this->page_largeur - $this->marge_gauche - $this->marge_droit - $this->reference_largeur;
		$this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droit);
		if($new_page)
		{
			$this->AddPage($this->orientation , 'A4');
		}
		$this->SetAutoPageBreak(true);
	}

	public function bilan_periode_individuel_entete($matiere_nom,$texte_periode,$groupe_nom,$eleve_nom,$eleve_prenom)
	{
		$hauteur_entete = 18;
		// On prend une nouvelle page PDF si besoin
		$hauteur_requise = $hauteur_entete + $this->cases_hauteur * $this->lignes_nb;
		$hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
		if($hauteur_requise > $hauteur_restante)
		{
			$this->AddPage($this->orientation , 'A4');
		}
		$ordonnee = $this->GetY();
		// Intitulé
		$this->SetFont('Arial' , 'B' , 12);
		$this->SetXY($this->marge_gauche , $ordonnee);
		$this->Cell($this->page_largeur-$this->marge_droit-75 , 5 , pdf('Bilan sur une matière') , 0 , 2 , 'L' , false , '');
		$this->Cell($this->page_largeur-$this->marge_droit-75 , 5 , pdf($matiere_nom.' - '.$groupe_nom) , 0 , 2 , 'L' , false , '');
		// Période
		$this->SetFont('Arial' , '' , 10);
		$this->Cell($this->page_largeur-$this->marge_droit-75 , 5 , pdf($texte_periode) , 0 , 2 , 'L' , false , '');
		// Nom prénom
		$this->SetFont('Arial' , '' , 12);
		$this->SetXY($this->page_largeur-$this->marge_droit-70 , $ordonnee);
		$this->Cell(20 , 5 , pdf('Nom :') , 0 , 2 , 'R' , false , '');
		$this->Cell(20 , 5 , pdf('Prénom :') , 0 , 2 , 'R' , false , '');
		// On met le document au nom de l'élève
		$this->SetFont('Arial' , 'B' , 12);
		$this->SetXY($this->page_largeur-$this->marge_droit-50 , $ordonnee);
		$this->Cell(50 , 5 , pdf($eleve_nom) , 0 , 2 , 'L' , false , '');
		$this->Cell(50 , 5 , pdf($eleve_prenom) , 0 , 2 , 'L' , false , '');
		// On se positionne sous l'entête
		$this->SetXY($this->marge_gauche , $ordonnee+$hauteur_entete);
		$this->SetFont('Arial' , '' , 8);
	}

	public function bilan_periode_individuel_entete_transdisciplinaire_principal($texte_format,$texte_periode,$groupe_nom,$eleve_nom,$eleve_prenom)
	{
		// On prend une nouvelle page PDF
		$this->AddPage($this->orientation , 'A4');
		// Intitulé
		$this->SetFont('Arial' , 'B' , 12);
		$this->Cell($this->page_largeur-$this->marge_droit-75 , 5 , pdf('Bilan '.$texte_format) , 0 , 2 , 'L' , false , '');
		$this->Cell($this->page_largeur-$this->marge_droit-75 , 5 , pdf($groupe_nom.' - '.$eleve_nom.' '.$eleve_prenom) , 0 , 2 , 'L' , false , '');
		// Période
		$this->SetFont('Arial' , '' , 10);
		if($texte_periode)
		{
			$this->Cell($this->page_largeur-$this->marge_droit-75 , 5 , pdf($texte_periode) , 0 , 1 , 'L' , false , '');
		}
	}

	public function bilan_periode_individuel_entete_transdisciplinaire_secondaire($matiere_nom,$lignes_nb)
	{
		$this->lignes_nb = $lignes_nb;
		$ordonnee = $this->GetY() + $this->cases_hauteur;
		$this->SetXY($this->marge_gauche , $ordonnee);
		// On prend une nouvelle page PDF si besoin
		$hauteur_requise = 5 + $this->cases_hauteur * $this->lignes_nb;
		$hauteur_restante = $this->page_hauteur - $ordonnee - $this->marge_bas;
		if($hauteur_requise > $hauteur_restante)
		{
			$this->AddPage($this->orientation , 'A4');
			$ordonnee = $this->marge_haut;
		}
		// Intitulé
		$this->SetFont('Arial' , 'B' , 12);
		$this->Cell($this->page_largeur-$this->marge_droit-75 , 5 , pdf($matiere_nom) , 0 , 1 , 'L' , false , '');
		// Interligne
		$this->SetXY($this->marge_gauche , $ordonnee+5);
		$this->SetFont('Arial' , '' , 8);
	}

	public function bilan_periode_individuel_competence($competence_ref,$competence_texte)
	{
		list($ref_matiere,$ref_suite) = explode('.',$competence_ref,2);
		$this->choisir_couleur_fond('gris_clair');
		$this->SetFont('Arial' , '' , 7);
		$this->Cell($this->reference_largeur , $this->cases_hauteur , pdf($ref_suite) , 1 , 0 , 'C' , true , '');
		$this->SetFont('Arial' , '' , 8);
		$this->Cell($this->intitule_largeur , $this->cases_hauteur , pdf($competence_texte) , 1 , 0 , 'L' , false , '');
		$this->choisir_couleur_fond('blanc');
	}

	public function bilan_periode_individuel_synthese($bilan_texte)
	{
		$this->SetFont('Arial' , '' , 8);
		$this->choisir_couleur_fond('gris_fonce');
		$this->Cell($this->reference_largeur , $this->cases_hauteur , '' , 0 , 0 , 'C' , false , '');
		$this->Cell($this->synthese_largeur , $this->cases_hauteur , pdf($bilan_texte) , 1 , 1 , 'R' , true , '');
	}

	public function bilan_periode_individuel_interligne()
	{
		$this->SetXY($this->marge_gauche , $this->GetY() + $this->cases_hauteur);
	}

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	//	Méthodes pour la mise en page d'un bilan de synthèse d'un groupe sur une période
	//	bilan_periode_synthese_initialiser() bilan_periode_synthese_entete() bilan_periode_synthese_pourcentages()
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

	public function bilan_periode_synthese_initialiser($eleve_nb,$competence_nb)
	{
		$this->cases_largeur     = ($this->page_largeur - $this->marge_gauche - $this->marge_droit - 2) / ($competence_nb+5); // -2 pour une petite marge ; 2 colonnes ajoutées + 3 colonnes comptés pour l'identité
		$this->cases_hauteur     = ($this->page_hauteur - $this->marge_haut - $this->marge_bas - 20 - 2) / ($eleve_nb+3); // 1+2 lignes ajoutées + petite marge
		$this->eleve_largeur     = 3 * $this->cases_largeur;
		$this->cases_hauteur     = min($this->cases_hauteur,10); // pas plus de 10
		$this->cases_hauteur     = max($this->cases_hauteur,4); // pas moins de 4
		$this->reference_largeur = 10;
		$this->taille_police     = $this->cases_largeur*0.8;
		$this->taille_police     = min($this->taille_police,10); // pas plus de 10
		$this->taille_police     = max($this->taille_police,4); // pas moins de 4
		$this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droit);
		$this->AddPage($this->orientation , 'A4');
		$this->SetAutoPageBreak(true);
	}

	public function bilan_periode_synthese_entete($titre_nom,$matiere_nom,$texte_periode,$groupe_nom)
	{
		$hauteur_entete = 20;
		// Intitulé
		$this->SetFont('Arial' , 'B' , 12);
		$this->SetXY($this->marge_gauche , $this->marge_haut);
		$this->Cell($this->page_largeur-$this->marge_droit-55 , 5 , pdf('Bilan '.$titre_nom) , 0 , 2 , 'L' , false , '');
		$this->Cell($this->page_largeur-$this->marge_droit-55 , 5 , pdf($matiere_nom.' - '.$groupe_nom) , 0 , 2 , 'L' , false , '');
		// Période
		$this->SetFont('Arial' , '' , 10);
		if($texte_periode)
		{
			$this->Cell($this->page_largeur-$this->marge_droit-55 , 5 , pdf($texte_periode) , 0 , 2 , 'L' , false , '');
		}
		// Synthèse
		$this->SetFont('Arial' , 'B' , 12);
		$this->SetXY($this->page_largeur-$this->marge_droit-50 , $this->marge_haut);
		$this->Cell(20 , 5 , pdf('SYNTHESE') , 0 , 1 , 'C' , false , '');
		// On se positionne sous l'entête
		$this->SetXY($this->marge_gauche , $this->marge_haut+$hauteur_entete);
		$this->SetFont('Arial' , '' , $this->taille_police);
	}

	public function bilan_periode_synthese_pourcentages($moyenne_pourcent,$moyenne_nombre,$last_ligne,$last_colonne)
{
	// $last_ligne = true si on veut afficher les deux dernières lignes
	// $last_colonne = true si on veut afficher les deux dernières colonnes
	// si $last_ligne = $last_colonne = true alors ce sont les deux dernières cases en diagonale

	// sauter 2mm pour la dernière colonne ; pour la ligne cela a déjà été fait avec l'étiquette de ligne
	if($last_colonne)
	{
		$this->SetX( $this->GetX()+2 );
	}
	// pour la dernière ligne, mais pas pour les 2 dernières cases, mémoriser l'ordonnée pour s'y repositionner à la fin
	elseif($last_ligne)
	{
		$memo_y = $this->GetY();
	}

	// aller vers le bas ou vers la droite après la 1ère case 
	$direction_after_case1 = ($last_ligne) ? 2 : 0;
	// aller à la ligne ou vers la droite après la 2ème case 
	$direction_after_case2 = ($last_colonne) ? 1 : 0;

	// cas de 2 cases vierges
	if($moyenne_pourcent===false)
	{
		$this->choisir_couleur_fond('blanc');
		$this->Cell($this->cases_largeur , $this->cases_hauteur , '-' , 1 , $direction_after_case1 , 'C' , true , '');
		// pour les 2 cases en diagonales, une case invisible permet de se positionner correctement
		if($last_colonne && $last_ligne)
		{
			$this->Cell($this->cases_largeur , $this->cases_hauteur , '' , 0 , 0 , 'C' , false , '');
		}
		$this->Cell($this->cases_largeur , $this->cases_hauteur , '-' , 1 , $direction_after_case2 , 'C' , true , '');
	}

	// cas de 2 cases à remplir
	else
	{
		// case 1
				if($moyenne_pourcent<$_SESSION['CALCUL_SEUIL']['R']) {$this->choisir_couleur_fond('rouge_fonce');}
		elseif($moyenne_pourcent>$_SESSION['CALCUL_SEUIL']['V']) {$this->choisir_couleur_fond('vert_fonce');}
		else                                                              {$this->choisir_couleur_fond('jaune_fonce');}
		$this->Cell($this->cases_largeur , $this->cases_hauteur , $moyenne_pourcent.'%' , 1 , $direction_after_case1 , 'C' , true , '');

		// pour les 2 cases en diagonales, une case invisible permet de se positionner correctement
		if($last_colonne && $last_ligne)
		{
			$this->Cell($this->cases_largeur , $this->cases_hauteur , '' , 0 , 0 , 'C' , false , '');
		}

		// case 2
				if($moyenne_nombre<$_SESSION['CALCUL_SEUIL']['R']) {$this->choisir_couleur_fond('rouge_fonce');}
		elseif($moyenne_nombre>$_SESSION['CALCUL_SEUIL']['V']) {$this->choisir_couleur_fond('vert_fonce');}
		else                                                            {$this->choisir_couleur_fond('jaune_fonce');}
		$this->Cell($this->cases_largeur , $this->cases_hauteur , $moyenne_nombre.'%' , 1 , $direction_after_case2 , 'C' , true , '');

		// pour la dernière ligne, mais pas pour les 2 dernières cases, se repositionner à la bonne ordonnée
		if($last_ligne && !$last_colonne)
		{
			$memo_x = $this->GetX();
			$this->SetXY($memo_x , $memo_y);
		}
	}
}

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	//	Méthodes pour la mise en page d'un cartouche
	//	cartouche_initialiser() cartouche_entete() cartouche_minimal_competence() cartouche_complet_competence() cartouche_interligne()
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

	public function cartouche_initialiser($detail,$competence_nb)
	{
		$this->lignes_nb         = ($detail=='minimal') ? 4 : $competence_nb+1 ;
		$this->cases_largeur     = ($detail=='minimal') ? ($this->page_largeur - $this->marge_gauche - $this->marge_droit) / $competence_nb : 10 ;
		$this->cases_hauteur     = 5 ;
		$this->reference_largeur = 15 ;
		$this->intitule_largeur  = ($detail=='minimal') ? 0 : $this->page_largeur - $this->marge_gauche - $this->marge_droit - $this->reference_largeur - $this->cases_largeur ;
		$this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droit);
		$this->AddPage($this->orientation , 'A4');
		$this->SetAutoPageBreak(false);
	}

	public function cartouche_entete($texte_entete)
	{
		// On prend une nouvelle page PDF si besoin
		$hauteur_requise = $this->cases_hauteur * $this->lignes_nb;
		$hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
		if($hauteur_requise > $hauteur_restante)
		{
			$this->AddPage($this->orientation , 'A4');
		}
		// Intitulé
		$this->SetFont('Arial' , '' , 10);
		$this->Cell(0 , $this->cases_hauteur , pdf($texte_entete) , 0 , 1 , 'L' , false , '');
		$this->SetFont('Arial' , '' , 8);
	}

	public function cartouche_minimal_competence($competence_ref,$note)
	{
		$memo_x = $this->GetX();
		$memo_y = $this->GetY();
		list($ref_matiere,$ref_suite) = explode('.',$competence_ref,2);
		$this->SetFont('Arial' , '' , 7);
		$this->Cell($this->cases_largeur , $this->cases_hauteur/2 , pdf($ref_matiere) , 0 , 2 , 'C' , false , '');
		$this->Cell($this->cases_largeur , $this->cases_hauteur/2 , pdf($ref_suite) , 0 , 2 , 'C' , false , '');
		$this->SetFont('Arial' , '' , 8);
		$this->SetXY($memo_x , $memo_y);
		$this->Cell($this->cases_largeur , $this->cases_hauteur , '' , 1 , 2 , 'C' , false , '');
		$this->afficher_note_lomer($note);
		$this->Cell($this->cases_largeur , $this->cases_hauteur , '' , 1 , 0 , 'C' , false , '');
		$this->SetXY($memo_x+$this->cases_largeur , $memo_y);
	}

	public function cartouche_complet_competence($competence_ref,$competence_intitule,$note)
	{
		$memo_x = $this->GetX();
		$memo_y = $this->GetY();
		list($ref_matiere,$ref_suite) = explode('.',$competence_ref,2);
		$this->SetFont('Arial' , '' , 7);
		$this->Cell($this->reference_largeur , $this->cases_hauteur/2 , pdf($ref_matiere) , 0 , 2 , 'C' , false , '');
		$this->Cell($this->reference_largeur , $this->cases_hauteur/2 , pdf($ref_suite) , 0 , 2 , 'C' , false , '');
		$this->SetFont('Arial' , '' , 8);
		$this->SetXY($memo_x , $memo_y);
		$this->Cell($this->reference_largeur , $this->cases_hauteur , '' , 1 , 0 , 'C' , false , '');
		$this->Cell($this->intitule_largeur , $this->cases_hauteur , pdf($competence_intitule) , 1 , 0 , 'L' , false , '');
		$this->afficher_note_lomer($note);
		$this->Cell($this->cases_largeur , $this->cases_hauteur , '' , 1 , 1 , 'C' , false , '');
	}

	public function cartouche_interligne($nb_lignes)
	{
		$this->SetXY($this->marge_gauche , $this->GetY() + $nb_lignes*$this->cases_hauteur);
	}

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	//	Méthode pour afficher une note Lomer
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

	public function afficher_note_lomer($note)
	{
		$memo_x = $this->GetX();
		$memo_y = $this->GetY();
		$rect_largeur_demi  = $this->cases_largeur / 2;
		$rect_largeur_quart = $this->cases_largeur / 4;
		$couleur = ($this->couleur =='oui') ? true : false ;
		switch ($note)
		{
			case 'RR' :
				if($couleur)
				{
					$this->choisir_couleur_fond('rouge_clair');
					$this->Cell($rect_largeur_demi , $this->cases_hauteur , 'R' , 0 , 0 , 'C' , true , '');
					$this->Cell($rect_largeur_demi , $this->cases_hauteur , 'R' , 0 , 0 , 'C' , true , '');
				}
				else
				{
					$this->choisir_couleur_fond('blanc');
					$this->Cell($this->cases_largeur , $this->cases_hauteur , 'RR' , 0 , 0 , 'C' , true , '');
				}
				break;
			case 'R' :
				if($couleur)
				{
					$this->choisir_couleur_fond('jaune_clair');
					$this->Cell($rect_largeur_quart , $this->cases_hauteur , '' , 0 , 0 , 'C' , true , '');
					$this->choisir_couleur_fond('rouge_clair');
					$this->Cell($rect_largeur_demi , $this->cases_hauteur , 'R' , 0 , 0 , 'C' , true , '');
					$this->choisir_couleur_fond('jaune_clair');
					$this->Cell($rect_largeur_quart , $this->cases_hauteur , '' , 0 , 0 , 'C' , true , '');
				}
				else
				{
					$this->choisir_couleur_fond('blanc');
					$this->Cell($this->cases_largeur , $this->cases_hauteur , 'R' , 0 , 0 , 'C' , true , '');
				}
				break;
			case 'V' :
				if($couleur)
				{
					$this->choisir_couleur_fond('jaune_clair');
					$this->Cell($rect_largeur_quart , $this->cases_hauteur , '' , 0 , 0 , 'C' , true , '');
					$this->choisir_couleur_fond('vert_clair');
					$this->Cell($rect_largeur_demi , $this->cases_hauteur , 'V' , 0 , 0 , 'C' , true , '');
					$this->choisir_couleur_fond('jaune_clair');
					$this->Cell($rect_largeur_quart , $this->cases_hauteur , '' , 0 , 0 , 'C' , true , '');
				}
				else
				{
					$this->choisir_couleur_fond('blanc');
					$this->Cell($this->cases_largeur , $this->cases_hauteur , 'V' , 0 , 0 , 'C' , true , '');
				}
				break;
			case 'VV' :
				if($couleur)
				{
					$this->choisir_couleur_fond('vert_clair');
					$this->Cell($rect_largeur_demi , $this->cases_hauteur , 'V' , 0 , 0 , 'C' , true , '');
					$this->Cell($rect_largeur_demi , $this->cases_hauteur , 'V' , 0 , 0 , 'C' , true , '');
				}
				else
				{
					$this->choisir_couleur_fond('blanc');
					$this->Cell($this->cases_largeur , $this->cases_hauteur , 'VV' , 0 , 0 , 'C' , true , '');
				}
				break;
			case 'ABS' :
				$this->SetFont('Arial' , '' , 6);
				$this->Cell($this->cases_largeur , $this->cases_hauteur , 'Abs.' , 0 , 0 , 'C' , false , '');
				$this->SetFont('Arial' , '' , 8);
				break;
			case 'NN' :
				$this->SetFont('Arial' , '' , 6);
				$this->Cell($this->cases_largeur , $this->cases_hauteur , 'N.N.' , 0 , 0 , 'C' , false , '');
				$this->SetFont('Arial' , '' , 8);
				break;
			case 'DISP' :
				$this->SetFont('Arial' , '' , 6);
				$this->Cell($this->cases_largeur , $this->cases_hauteur , 'Disp.' , 0 , 0 , 'C' , false , '');
				$this->SetFont('Arial' , '' , 8);
				break;
		}
		$this->SetXY($memo_x , $memo_y);
	}

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	//	Méthode pour afficher une validation de socle (texte A VA NA et couleur de fond suivant le seuil)
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function afficher_validation_socle($gras,$tab_infos)
{
	// $tab_infos contient 'A' / 'VA' / 'NA' / 'nb' / '%'
	$this->SetFont('Arial' , $gras , $this->taille_police);
	if($tab_infos['%']===false)
	{
		$this->choisir_couleur_fond('blanc');
		$this->Cell($this->attestation_largeur , $this->cases_hauteur , '-' , 1 , 1 , 'C' , true , '');
	}
	else
	{
		    if($tab_infos['%']<$_SESSION['CALCUL_SEUIL']['R']) {$this->choisir_couleur_fond('rouge_fonce');}
		elseif($tab_infos['%']>$_SESSION['CALCUL_SEUIL']['V']) {$this->choisir_couleur_fond('vert_fonce');}
		else                                                            {$this->choisir_couleur_fond('jaune_fonce');}
		$this->Cell($this->attestation_largeur , $this->cases_hauteur , pdf($tab_infos['%'].'% validé ('.$tab_infos['A'].'A '.$tab_infos['VA'].'VA '.$tab_infos['NA'].'NA)') , 1 , 1 , 'C' , true , '');
	}
}

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	//	Méthode pour afficher un score bilan (bilan sur 100 et couleur de fond suivant le seuil)
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

	public function afficher_score_bilan($score,$br)
	{
		if($score===false)
		{
			$this->choisir_couleur_fond('blanc');
			$this->Cell($this->cases_largeur , $this->cases_hauteur , '-' , 1 , $br , 'C' , true , '');
		}
		else
		{
					if($score<$_SESSION['CALCUL_SEUIL']['R']) {$this->choisir_couleur_fond('rouge_fonce');}
			elseif($score>$_SESSION['CALCUL_SEUIL']['V']) {$this->choisir_couleur_fond('vert_fonce');}
			else                                                   {$this->choisir_couleur_fond('jaune_fonce');}
			$this->SetFont('Arial' , '' , $this->taille_police-2);
			$this->Cell($this->cases_largeur , $this->cases_hauteur , $score , 1 , $br , 'C' , true , '');
			$this->SetFont('Arial' , '' , $this->taille_police);
		}
	}

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	//	Méthode pour changer le pied de page
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

	public function Footer()
	{
		$this->SetXY(0 , -$this->distance_pied);
		$this->SetFont('Arial' , 'I' , 7);
		$this->choisir_couleur_fond('jaune_fonce');
		$this->Cell($this->page_largeur , 3 , pdf('Imprimé le '.date("d/m/Y").' par '.$_SESSION['USER_DESCR'].' avec SACoche http://competences.sesamath.net') , 0 , 0 , 'C' , true , '');
	}

}
?>
