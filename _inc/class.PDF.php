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

// Extension de classe qui étend FPDF

class PDF extends FPDF
{

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Pour optimiser la gestion de la mémoire et éviter un « Fatal error : Allowed memory size ... »
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Consulter  http://www.fpdf.org/fr/script/script18.php (a l'avantage de ne rien changer au code)
 * Voir aussi http://www.fpdf.org/fr/script/script76.php (a l'inconvénient qu'il faut changer certaines syntaxes)
 * 
 * Attention : la mémoire utilisée par fpdf n'est pas comptabilisée dans memory_get_usage().
 * Un script peut annoncer utiliser 8Mo, et dépasser en réalité 32Mo même pour générer un pdf de 200Ko au final.
**/

function _putpages()
{
	$nb=$this->page;
	if(!empty($this->AliasNbPages))
	{
		//Replace number of pages
		for($n=1;$n<=$nb;$n++)
		{
			if($this->compress)
				$this->pages[$n]=gzcompress(str_replace($this->AliasNbPages,$nb,gzuncompress($this->pages[$n])));
			else
				$this->pages[$n]=str_replace($this->AliasNbPages,$nb,$this->pages[$n]);
		}
	}
	if($this->DefOrientation=='P')
	{
		$wPt=$this->DefPageSize[0]*$this->k;
		$hPt=$this->DefPageSize[1]*$this->k;
	}
	else
	{
		$wPt=$this->DefPageSize[1]*$this->k;
		$hPt=$this->DefPageSize[0]*$this->k;
	}
	$filter=($this->compress) ? '/Filter /FlateDecode ' : '';
	for($n=1;$n<=$nb;$n++)
	{
		//Page
		$this->_newobj();
		$this->_out('<</Type /Page');
		$this->_out('/Parent 1 0 R');
		if(isset($this->PageSizes[$n]))
			$this->_out(sprintf('/MediaBox [0 0 %.2F %.2F]',$this->PageSizes[$n][0],$this->PageSizes[$n][1]));
		$this->_out('/Resources 2 0 R');
		if(isset($this->PageLinks[$n]))
		{
			//Links
			$annots='/Annots [';
			foreach($this->PageLinks[$n] as $pl)
			{
				$rect=sprintf('%.2F %.2F %.2F %.2F',$pl[0],$pl[1],$pl[0]+$pl[2],$pl[1]-$pl[3]);
				$annots.='<</Type /Annot /Subtype /Link /Rect ['.$rect.'] /Border [0 0 0] ';
				if(is_string($pl[4]))
					$annots.='/A <</S /URI /URI '.$this->_textstring($pl[4]).'>>>>';
				else
				{
					$l=$this->links[$pl[4]];
					$h=isset($this->PageSizes[$l[0]]) ? $this->PageSizes[$l[0]][1] : $hPt;
					$annots.=sprintf('/Dest [%d 0 R /XYZ 0 %.2F null]>>',1+2*$l[0],$h-$l[1]*$this->k);
				}
			}
			$this->_out($annots.']');
		}
		$this->_out('/Contents '.($this->n+1).' 0 R>>');
		$this->_out('endobj');
		//Page content
		$p=$this->pages[$n];
		$this->_newobj();
		$this->_out('<<'.$filter.'/Length '.strlen($p).'>>');
		$this->_putstream($p);
		$this->_out('endobj');
	}
	//Pages root
	$this->offsets[1]=strlen($this->buffer);
	$this->_out('1 0 obj');
	$this->_out('<</Type /Pages');
	$kids='/Kids [';
	for($i=0;$i<$nb;$i++)
		$kids.=(3+2*$i).' 0 R ';
	$this->_out($kids.']');
	$this->_out('/Count '.$nb);
	$this->_out(sprintf('/MediaBox [0 0 %.2F %.2F]',$wPt,$hPt));
	$this->_out('>>');
	$this->_out('endobj');
}

function _endpage()
{
	parent::_endpage();
	if($this->compress)
		$this->pages[$this->page] = gzcompress($this->pages[$this->page]);
}

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Pour écrire un texte tourné
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Consulter  http://www.fpdf.org/fr/script/script31.php
 * Voir aussi http://www.fpdf.org/fr/script/script2.php
**/

	function TextWithDirection($x, $y, $txt, $direction='R')
	{
		if ($direction=='R')
			$s = sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',1,0,0,1,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
		elseif ($direction=='L')
			$s = sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',-1,0,0,-1,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
		elseif ($direction=='U')
			$s = sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',0,1,-1,0,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
		elseif ($direction=='D')
			$s = sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',0,-1,1,0,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
		else
			$s = sprintf('BT %.2F %.2F Td (%s) Tj ET',$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
		if ($this->ColorFlag)
			$s = 'q '.$this->TextColor.' '.$s.' Q';
		$this->_out($s);
	}

	function TextWithRotation($x, $y, $txt, $txt_angle, $font_angle=0)
	{
		$font_angle += 90+$txt_angle;
		$txt_angle  *= M_PI/180;
		$font_angle *= M_PI/180;

		$txt_dx  = cos($txt_angle);
		$txt_dy  = sin($txt_angle);
		$font_dx = cos($font_angle);
		$font_dy = sin($font_angle);

		$s = sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',$txt_dx,$txt_dy,$font_dx,$font_dy,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
		if ($this->ColorFlag)
			$s = 'q '.$this->TextColor.' '.$s.' Q';
		$this->_out($s);
	}

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Pour tracer un cercle (ou une ellipse)
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Consulter  http://www.fpdf.org/fr/script/script6.php
 * Voir aussi http://www.fpdf.org/fr/script/script28.php
 * Voir aussi http://www.fpdf.org/fr/script/script69.php
**/

	function Circle($x, $y, $r, $style='D')
	{
		$this->Ellipse($x,$y,$r,$r,$style);
	}

	function Ellipse($x, $y, $rx, $ry, $style='D')
	{
		if($style=='F')
			$op = 'f';
		elseif($style=='FD' || $style=='DF')
			$op = 'B';
		else
			$op = 'S';
		$lx = 4/3*(M_SQRT2-1)*$rx;
		$ly = 4/3*(M_SQRT2-1)*$ry;
		$k = $this->k;
		$h = $this->h;
		$this->_out(sprintf('%.2F %.2F m %.2F %.2F %.2F %.2F %.2F %.2F c', ($x+$rx)*$k, ($h-$y)*$k,       ($x+$rx)*$k, ($h-($y-$ly))*$k, ($x+$lx)*$k, ($h-($y-$ry))*$k,$x*$k, ($h-($y-$ry))*$k));
		$this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',             ($x-$lx)*$k, ($h-($y-$ry))*$k, ($x-$rx)*$k, ($h-($y-$ly))*$k, ($x-$rx)*$k, ($h-$y)*$k));
		$this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',             ($x-$rx)*$k, ($h-($y+$ly))*$k, ($x-$lx)*$k, ($h-($y+$ry))*$k, $x*$k,       ($h-($y+$ry))*$k));
		$this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c %s',          ($x+$lx)*$k, ($h-($y+$ry))*$k, ($x+$rx)*$k, ($h-($y+$ly))*$k, ($x+$rx)*$k, ($h-$y)*$k,             $op));
	}

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Attributs de la classe (équivalents des "variables")
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	// Couleurs de fond
	private $tab_couleur       = array();
	private $tab_choix_couleur = array();
	// Lettres utilisées en remplacement des images Lomer pour du noir et blanc
	private $tab_lettre = array();
	// Valeurs des marges principales pour la mise en page PDF
	private $orientation   = '';
	private $marge_min     = 5;
	private $couleur       = 'oui';
	private $legende       = 1;
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
	private $cases_nb             = 0;
	private $cases_largeur        = 0;
	private $cases_hauteur        = 0;
	private $lignes_hauteur       = 0;
	private $lignes_nb            = 0;
	private $reference_largeur    = 0;
	private $intitule_largeur     = 0;
	private $synthese_largeur     = 0;
	private $etiquette_hauteur    = 0;
	private $colonne_vide_largeur = 0;

	private $pilier_largeur      = 0;
	private $section_largeur     = 0;
	private $item_largeur        = 0;
	private $pourcentage_largeur = 0;

	private $eleve_largeur     = 0;
	private $taille_police     = 8;

	private $lomer_espace_largeur = 0;
	private $lomer_espace_hauteur = 0;
	private $lomer_image_largeur  = 0;
	private $lomer_image_hauteur  = 0;

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Méthode Magique - Constructeur
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function __construct($orientation,$marge_min,$couleur='oui',$legende='oui')
	{
		// Appeler le constructeur de la classe mère
		parent::FPDF($orientation , $unit='mm' , $format='A4');
		// On passe à la classe fille
		$this->orientation = $orientation;
		$this->marge_min   = $marge_min;
		$this->couleur     = $couleur;
		$this->legende     = ($legende=='oui') ? 1 : 0 ;
		// Déclaration de la police pour la rendre disponible même si non présente sur le serveur
		$this->AddFont('Arial','' ,'arial.php');
		$this->AddFont('Arial','B','arialbd.php');
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
		// Couleurs prédéfinies
		$this->tab_choix_couleur = ($this->couleur=='oui') ? array('NA'=>'rouge','VA'=>'jaune','A'=>'vert') : array('NA'=>'gris_fonce','VA'=>'gris_moyen','A'=>'gris_clair') ;
		$this->tab_couleur['blanc']      = array('r'=>255,'v'=>255,'b'=>255);
		$this->tab_couleur['gris_clair'] = array('r'=>230,'v'=>230,'b'=>230);
		$this->tab_couleur['gris_moyen'] = array('r'=>190,'v'=>190,'b'=>190);
		$this->tab_couleur['gris_fonce'] = array('r'=>150,'v'=>150,'b'=>150);
		// Couleurs des états d'acquisition ; il faut convertir l'hexadécimal en RVB décimal
		$rr = hexdec(substr($_SESSION['BACKGROUND_NA'],1,2));
		$rv = hexdec(substr($_SESSION['BACKGROUND_NA'],3,2));
		$rb = hexdec(substr($_SESSION['BACKGROUND_NA'],5,2));
		$jr = hexdec(substr($_SESSION['BACKGROUND_VA'],1,2));
		$jv = hexdec(substr($_SESSION['BACKGROUND_VA'],3,2));
		$jb = hexdec(substr($_SESSION['BACKGROUND_VA'],5,2));
		$vr = hexdec(substr($_SESSION['BACKGROUND_A'] ,1,2));
		$vv = hexdec(substr($_SESSION['BACKGROUND_A'] ,3,2));
		$vb = hexdec(substr($_SESSION['BACKGROUND_A'] ,5,2));
		$this->tab_couleur['rouge'] = array('r'=>$rr,'v'=>$rv,'b'=>$rb);
		$this->tab_couleur['jaune'] = array('r'=>$jr,'v'=>$jv,'b'=>$jb);
		$this->tab_couleur['vert']  = array('r'=>$vr,'v'=>$vv,'b'=>$vb);
		// Couleurs des états de validation ; il faut convertir l'hexadécimal en RVB décimal
		$rr = hexdec(substr($_SESSION['BACKGROUND_V0'],1,2));
		$rv = hexdec(substr($_SESSION['BACKGROUND_V0'],3,2));
		$rb = hexdec(substr($_SESSION['BACKGROUND_V0'],5,2));
		$vr = hexdec(substr($_SESSION['BACKGROUND_V1'],1,2));
		$vv = hexdec(substr($_SESSION['BACKGROUND_V1'],3,2));
		$vb = hexdec(substr($_SESSION['BACKGROUND_V1'],5,2));
		$br = hexdec(substr($_SESSION['BACKGROUND_V2'],1,2));
		$bv = hexdec(substr($_SESSION['BACKGROUND_V2'],3,2));
		$bb = hexdec(substr($_SESSION['BACKGROUND_V2'],5,2));
		$this->tab_couleur['v0'] = array('r'=>$rr,'v'=>$rv,'b'=>$rb);
		$this->tab_couleur['v1'] = array('r'=>$vr,'v'=>$vv,'b'=>$vb);
		$this->tab_couleur['v2'] = array('r'=>$br,'v'=>$bv,'b'=>$bb);
		// Lettres utilisées en remplacement des images Lomer pour du noir et blanc
		$this->tab_lettre['RR'] = $_SESSION['NOTE_TEXTE']['RR'];
		$this->tab_lettre['R']  = $_SESSION['NOTE_TEXTE']['R'];
		$this->tab_lettre['V']  = $_SESSION['NOTE_TEXTE']['V'];
		$this->tab_lettre['VV'] = $_SESSION['NOTE_TEXTE']['VV'];
	}

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Méthode Magique - Pour récupérer un attribut private (c'est comme s'il était en lecture seule)
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function __get($nom)
	{
		return (isset($this->$nom)) ? $this->$nom : null ;
	}

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Méthode Magique - Pour affecter une valeur à un attribut
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function __set($nom,$valeur)
	{
			$this->$nom = $valeur;
	}

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Méthode pour calculer les dimensions d'une image Lomer
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function calculer_dimensions_images($espace_largeur,$espace_hauteur)
	{
		$this->lomer_espace_largeur = $espace_largeur;
		$this->lomer_espace_hauteur = $espace_hauteur;
		// Une image a des dimensions initiales de 20px sur 10px
		$rapport_largeur = $espace_largeur / 20 ;
		$rapport_hauteur = $espace_hauteur / 10 ;
		$centrage = ($rapport_largeur<$rapport_hauteur) ? 'hauteur' : 'largeur';
		$rapport_coef = ($centrage=='hauteur') ? $rapport_largeur : $rapport_hauteur ;
		$rapport_coef = min( floor($rapport_coef*10)/10 , 0.4 ) ;	// A partir de PHP 5.3 on peut utiliser l'option PHP_ROUND_HALF_DOWN de round()
		$this->lomer_image_largeur = floor(20*$rapport_coef) ;
		$this->lomer_image_hauteur = floor(10*$rapport_coef) ;
	}

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Méthodes pour choisir une couleur de fond ou une couleur de tracé
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function choisir_couleur_fond($couleur)
	{
		$this->SetFillColor($this->tab_couleur[$couleur]['r'] , $this->tab_couleur[$couleur]['v'] , $this->tab_couleur[$couleur]['b']);
	}

	public function choisir_couleur_trait($couleur)
	{
		$this->SetDrawColor($this->tab_couleur[$couleur]['r'] , $this->tab_couleur[$couleur]['v'] , $this->tab_couleur[$couleur]['b']);
	}

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Méthode pour afficher une note Lomer
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function afficher_note_lomer($note,$border,$br)
	{
		$memo_x = $this->GetX();
		$memo_y = $this->GetY();
		switch ($note)
		{
			case 'RR' :
			case 'R' :
			case 'V' :
			case 'VV' :
				$this->choisir_couleur_fond('blanc');
				if($this->couleur == 'oui')
				{
					$img_pos_x = $memo_x + ( ($this->lomer_espace_largeur - $this->lomer_image_largeur) / 2 ) ;
					$img_pos_y = $memo_y + ( ($this->lomer_espace_hauteur - $this->lomer_image_hauteur) / 2 ) ;
					$this->Image('./_img/note/'.$_SESSION['NOTE_DOSSIER'].'/h/'.$note.'.gif',$img_pos_x,$img_pos_y,$this->lomer_image_largeur,$this->lomer_image_hauteur,'GIF');
				}
				else
				{
					if(strlen( $this->tab_lettre[$note]<3)) {$this->SetFontSize($this->taille_police);} else {$this->SetFontSize($this->taille_police*0.85);}
					$this->Cell($this->lomer_espace_largeur , $this->lomer_espace_hauteur ,  $this->tab_lettre[$note] , 0 , 0 , 'C' , true , '');
					if(strlen( $this->tab_lettre[$note]<3)) {$this->SetFontSize($this->taille_police);}
				}
				break;
			case 'ABS' :
			case 'NN' :
			case 'DISP' :
				$tab_texte = array('ABS'=>'Abs.','NN'=>'N.N.','DISP'=>'Disp.');
				$this->SetFontSize($this->taille_police*0.7);
				$this->Cell($this->lomer_espace_largeur , $this->lomer_espace_hauteur , $tab_texte[$note] , 0 , 0 , 'C' , false , '');
				$this->SetFontSize($this->taille_police);
				break;
		}
		// Ensuite on met ou non une bordure et on se positionne comme souhaité
		$this->SetXY($memo_x , $memo_y);
		$this->Cell($this->lomer_espace_largeur , $this->lomer_espace_hauteur , '' , $border , $br , 'C' , false , '');
	}

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Méthode pour afficher un état de validation (date sur fond coloré)
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function afficher_etat_validation($gras,$tab_infos)
{
	// $tab_infos contient 'etat' / 'date' / 'info'
	$this->SetFont('Arial' , $gras , $this->taille_police);
	$texte = ($tab_infos['etat']==2) ? '---' : $tab_infos['date'] ;
	$this->choisir_couleur_fond('v'.$tab_infos['etat']);
	$this->Cell($this->validation_largeur , $this->cases_hauteur , pdf($texte) , 1 , 1 , 'C' , true , '');
}

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Méthode pour afficher un pourcentage d'items acquis (texte A VA NA et couleur de fond suivant le seuil)
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function afficher_pourcentage_acquis($gras,$tab_infos,$detail)
{
	// $tab_infos contient 'A' / 'VA' / 'NA' / 'nb' / '%'
	$taille_police = $detail ? $this->taille_police : $this->taille_police/2 ;
	$this->SetFont('Arial' , $gras , $taille_police);
	if($tab_infos['%']===false)
	{
		$this->choisir_couleur_fond('blanc');
		$this->Cell($this->pourcentage_largeur , $this->cases_hauteur , '-' , 1 , 0 , 'C' , true , '');
	}
	else
	{
				if($tab_infos['%']<$_SESSION['CALCUL_SEUIL']['R']) {$this->choisir_couleur_fond($this->tab_choix_couleur['NA']);}
		elseif($tab_infos['%']>$_SESSION['CALCUL_SEUIL']['V']) {$this->choisir_couleur_fond($this->tab_choix_couleur['A']);}
		else                                                   {$this->choisir_couleur_fond($this->tab_choix_couleur['VA']);}
		if($detail)
		{
			$this->Cell($this->pourcentage_largeur , $this->cases_hauteur , pdf($tab_infos['%'].'% acquis ('.$tab_infos['A'].$_SESSION['ACQUIS_TEXTE']['A'].' '.$tab_infos['VA'].$_SESSION['ACQUIS_TEXTE']['VA'].' '.$tab_infos['NA'].$_SESSION['ACQUIS_TEXTE']['NA'].')') , 1 , 0 , 'C' , true , '');
		}
		else
		{
			$this->Cell($this->pourcentage_largeur , $this->cases_hauteur , pdf($tab_infos['%']) , 1 , 0 , 'C' , true , '');
		}
	}
}

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Méthode pour afficher un score bilan (bilan sur 100 et couleur de fond suivant le seuil)
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function afficher_score_bilan($score,$br)
	{
		if($score===false)
		{
			$score_affiche = (mb_substr_count($_SESSION['DROIT_VOIR_SCORE_BILAN'],$_SESSION['USER_PROFIL'])) ? '-' : '' ;
			$this->choisir_couleur_fond('blanc');
			$this->Cell($this->cases_largeur , $this->cases_hauteur , $score_affiche , 1 , $br , 'C' , true , '');
		}
		else
		{
					if($score<$_SESSION['CALCUL_SEUIL']['R']) {$this->choisir_couleur_fond($this->tab_choix_couleur['NA']);}
			elseif($score>$_SESSION['CALCUL_SEUIL']['V']) {$this->choisir_couleur_fond($this->tab_choix_couleur['A']);}
			else                                          {$this->choisir_couleur_fond($this->tab_choix_couleur['VA']);}
			$score_affiche = (mb_substr_count($_SESSION['DROIT_VOIR_SCORE_BILAN'],$_SESSION['USER_PROFIL'])) ? $score : '' ;
			$this->SetFont('Arial' , '' , $this->taille_police-2);
			$this->Cell($this->cases_largeur , $this->cases_hauteur , $score_affiche , 1 , $br , 'C' , true , '');
			$this->SetFont('Arial' , '' , $this->taille_police);
		}
	}

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Méthode pour afficher une barre avec les états des items acquis (rectangles A VA NA et couleur de fond suivant le seuil)
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function afficher_proportion_acquis($largeur,$hauteur,$tab_infos,$total)
	{
		// $tab_infos contient 'A' / 'VA' / 'NA'
		$abscisse = $this->GetX();
		$ordonnee = $this->GetY();
		// Couleurs de fond + textes
		foreach($tab_infos as $etat => $nb)
		{
			$this->choisir_couleur_fond($this->tab_choix_couleur[$etat]);
			$largeur_case = $largeur*$nb/$total ;
			$texte = ($largeur_case>$hauteur) ? $nb.' '.$_SESSION['ACQUIS_TEXTE'][$etat] : $nb ;
			$this->Cell($largeur_case , $hauteur , pdf($texte) , 0 , 0 , 'C' , true , '');
		}
		// Bordure unique autour
		$this->SetXY($abscisse , $ordonnee);
		$this->Cell($largeur , $hauteur , '' , 1 , 0 , 'C' , false , '');
	}

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Méthode pour afficher la légende ($type_legende vaut 'notes' ou 'acquis')
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function afficher_legende($type_legende,$ordonnee)
	{
		$hauteur = min(4,$this->lignes_hauteur);
		$this->SetFont('Arial' , '' , ceil($hauteur * 1.6));
		$this->SetXY($this->marge_gauche , $ordonnee);
		// Afficher la légende des notes
		if($type_legende=='notes')
		{
			$memo_lomer_espace_largeur = $this->lomer_espace_largeur;
			$memo_lomer_espace_hauteur = $this->lomer_espace_hauteur;
			$border = ($this->couleur == 'oui') ? 0 : 1 ;
			$memo_taille_police = $this->taille_police;
			$this->taille_police = ceil($hauteur * 1.6); // On est obligé de le changer provisoirement car si impression N&B afficher_note_lomer() l'utilise
			$this->calculer_dimensions_images($hauteur*1.5,$hauteur);
			$this->afficher_note_lomer('RR',$border,$br=0);
			$this->Write($hauteur , pdf($_SESSION['NOTE_LEGENDE']['RR']).'     ' , '');
			$this->afficher_note_lomer('R',$border,$br=0);
			$this->Write($hauteur , pdf($_SESSION['NOTE_LEGENDE']['R']) .'     ' , '');
			$this->afficher_note_lomer('V',$border,$br=0);
			$this->Write($hauteur , pdf($_SESSION['NOTE_LEGENDE']['V']) .'     ' , '');
			$this->afficher_note_lomer('VV',$border,$br=0);
			$this->Write($hauteur , pdf($_SESSION['NOTE_LEGENDE']['VV']).'     ' , '');
			$this->calculer_dimensions_images($memo_lomer_espace_largeur,$memo_lomer_espace_hauteur);
			$this->taille_police = $memo_taille_police;
		}
		// Afficher la légende des états d'acquisition
		if($type_legende=='acquis')
		{
			foreach($this->tab_choix_couleur as $etat => $couleur_fond)
			{
				$this->choisir_couleur_fond($couleur_fond);
				$this->Cell($hauteur*1.5 , $hauteur , pdf($_SESSION['ACQUIS_TEXTE'][$etat]) , 1 , 0 , 'C' , true , '');
				$this->Write($hauteur , pdf($_SESSION['ACQUIS_LEGENDE'][$etat]).'     ' , '');
			}
		}
		$this->SetXY($this->marge_gauche , $ordonnee+$hauteur);
	}

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Méthode pour changer le pied de page
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function Footer()
	{
		$this->SetXY(0 , -$this->distance_pied);
		$this->SetFont('Arial' , '' , 7);
		$this->choisir_couleur_fond('gris_clair');
		$this->choisir_couleur_trait('gris_moyen');
		$this->Cell($this->page_largeur , 3 , pdf('Généré le '.date("d/m/Y \à H\hi\m\i\\n").' par '.$_SESSION['USER_PRENOM']{0}.'. '.$_SESSION['USER_NOM'].' ('.$_SESSION['USER_PROFIL'].') avec SACoche [ http://sacoche.sesamath.net ].') , 'TB' , 0 , 'C' , true , 'http://sacoche.sesamath.net');
	}

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Méthode pour tester si un intitulé rentre dans une case sur une seule ligne (sinon => 2 lignes, pas prévu plus) [méthode publique car appelée depuis professeur_eval_*.ajax.php]
	//	Méthode pour afficher un texte sur 1 ou 2 lignes maxi si pas la place.
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function test_pas_trop_long($texte,$taille_police,$longueur_cellule)
	{
		return (mb_strlen($texte)*$taille_police*0.15 < $longueur_cellule) ? true : false ;
	}

	private function afficher_texte_sur_2lignes_maxi($texte,$taille_police,$longueur_cellule,$hauteur_cellule,$bordure,$br,$alignement,$remplissage)
	{
		if($this->test_pas_trop_long($texte,$taille_police,$longueur_cellule))
		{
			$this->Cell($longueur_cellule , $hauteur_cellule , pdf($texte) , $bordure , $br , $alignement , $remplissage , '');
		}
		elseif($this->test_pas_trop_long($texte,$taille_police*0.9,$longueur_cellule))
		{
			$this->SetFont('Arial' , '' , $taille_police*0.9);
			$this->Cell($longueur_cellule , $hauteur_cellule , pdf($texte) , $bordure , $br , $alignement , $remplissage , '');
			$this->SetFont('Arial' , '' , $taille_police);
		}
		elseif($this->test_pas_trop_long($texte,$taille_police*0.8,$longueur_cellule))
		{
			$this->SetFont('Arial' , '' , $taille_police*0.8);
			$this->Cell($longueur_cellule , $hauteur_cellule , pdf($texte) , $bordure , $br , $alignement , $remplissage , '');
			$this->SetFont('Arial' , '' , $taille_police);
		}
		else
		{
			$abscisse = $this->GetX();
			$ordonnee = $this->GetY();
			$demihauteur = $hauteur_cellule *0.48 ;
			$this->SetFont('Arial' , '' , $taille_police * 0.8);
			$this->MultiCell($longueur_cellule , $demihauteur , pdf($texte) , 0 , $alignement , $remplissage );
			$this->SetFont('Arial' , '' , $taille_police);
			$this->SetXY($abscisse , $ordonnee);
			$this->Cell($longueur_cellule , $hauteur_cellule , '' , $bordure , $br , '' , false , '');
		}
	}

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Méthodes pour la mise en page d'une synthèse matiere ou pluridisciplinaire ; a priori pas de pb avec la hauteur de ligne minimale
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	bilan_synthese_initialiser()   c'est là que les calculs se font pour une sortie "matiere"
	//	bilan_synthese_entete()        c'est là que les calculs se font pour une sortie "multimatiere"
	//	bilan_synthese_ligne_matiere()
	//	bilan_synthese_ligne_synthese()
	//	bilan_synthese_legende()
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function bilan_synthese_initialiser($format,$nb_syntheses_total,$eleves_nb)
	{
		$this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droit);
		$this->SetAutoPageBreak(false);
		if($format=='matiere')
		{
			// Dans ce cas on met plusieurs élèves par page : on calcule maintenant combien et la hauteur de ligne à prendre
			$hauteur_dispo_par_page = $this->page_hauteur - $this->marge_haut - $this->marge_bas ;
			$lignes_nb_tous_eleves = $eleves_nb * ( 1 + 1 + 1 + 1*(1.5+1.5) + ($this->legende*1.5) ) + $nb_syntheses_total ; // eleves * [ intitulé-structure + classe-élève + date + matières(marge+intitulé) + légende ] + toutes_synthèses
			$hauteur_ligne_moyenne = 6;
			$lignes_nb_moyen_par_page = $hauteur_dispo_par_page / $hauteur_ligne_moyenne ;
			$nb_page_moyen = max( 1 , round( $lignes_nb_tous_eleves / $lignes_nb_moyen_par_page ) ); // max 1 pour éviter une division par zéro
			$eleves_nb_par_page = ceil( $eleves_nb / $nb_page_moyen ) ;
			// $nb_page_calcule = ceil( $eleves_nb / $eleves_nb_par_page ) ; // devenu inutile
			$lignes_nb_moyen_eleve = $lignes_nb_tous_eleves / $eleves_nb ;
			$lignes_nb_calcule_par_page = $eleves_nb_par_page * $lignes_nb_moyen_eleve ; // $lignes_nb/$nb_page_calcule ne va pas à cause un élève peut alors être considéré à cheval sur 2 pages
			$hauteur_ligne_calcule = $hauteur_dispo_par_page / $lignes_nb_calcule_par_page ;
			$this->lignes_hauteur = floor($hauteur_ligne_calcule*10)/10 ; // round($hauteur_ligne_calcule,1,PHP_ROUND_HALF_DOWN) à partir de PHP 5.3
			$this->lignes_hauteur = min ( $this->lignes_hauteur , 7.5 ) ;
			// On s'occupe aussi maintenant de la taille de la police
			$this->taille_police  = $this->lignes_hauteur * 1.6 ; // 5mm de hauteur par ligne donne une taille de 8
			$this->taille_police  = min ( $this->taille_police , 10 ) ;
			// Pour forcer à prendre une nouvelle page au 1er élève
			$this->SetXY(0,$this->page_hauteur);
		}
	}

	public function bilan_synthese_entete($format,$matieres_nb,$syntheses_nb,$texte_format,$texte_periode,$groupe_nom,$eleve_nom,$eleve_prenom)
	{
		if($format=='matiere')
		{
			// La hauteur de ligne a déjà été calculée ; mais il reste à déterminer si on saute une page ou non en fonction de la place restante (et sinon => interligne)
			$hauteur_dispo_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas ;
			$lignes_nb = 1 + 1 + 1 + 1*(1.5+1.5) + ($this->legende*1.5) + $syntheses_nb ; // intitulé-structure + classe-élève + date + matières(marge+intitulé) + légende + synthèses
			if($this->lignes_hauteur*$lignes_nb > $hauteur_dispo_restante)
			{
				$this->AddPage($this->orientation , 'A4');
			}
			else
			{
				// Interligne
				$this->SetXY($this->marge_gauche , $this->GetY() + $this->lignes_hauteur*1.5);
			}
		}
		elseif($format=='multimatiere')
		{
			// On prend une nouvelle page PDF
			$this->AddPage($this->orientation , 'A4');
			// On calcule la hauteur de la ligne et la taille de la police pour tout faire rentrer sur une page (personnalisée par élève)
			$hauteur_dispo_par_page = $this->page_hauteur - $this->marge_haut - $this->marge_bas ;
			$lignes_nb = 1.5 + 1.5 + 1 + $matieres_nb*(1.5+1.5) + $syntheses_nb ; // intitulé-structure + classe-élève + date + matières(marge+intitulé) + synthèses
			$this->lignes_hauteur = $hauteur_dispo_par_page / $lignes_nb ;
			$this->lignes_hauteur = min ( $this->lignes_hauteur , 7.5 ) ;
			$this->taille_police  = $this->lignes_hauteur * 1.6 ; // 5mm de hauteur par ligne donne une taille de 8
			$this->taille_police  = min ( $this->taille_police , 10 ) ;
		}
		// Intitulé / structure
		$largeur_demi_page = ( $this->page_largeur - $this->marge_gauche - $this->marge_droit ) / 2;
		$this->SetFont('Arial' , 'B' , $this->taille_police*1.5);
		$this->Cell($largeur_demi_page , $this->lignes_hauteur , pdf('Synthèse '.$texte_format) , 0 , 0 , 'L' , false , '');
		$this->Cell($largeur_demi_page , $this->lignes_hauteur , pdf($_SESSION['DENOMINATION']) , 0 , 1 , 'R' , false , '');
		// Classe / élève
		$this->Cell($largeur_demi_page , $this->lignes_hauteur , pdf($groupe_nom.' - '.$eleve_nom.' '.$eleve_prenom) , 0 , 2 , 'L' , false , '');
		// Période
		$this->SetFont('Arial' , '' , $this->taille_police);
		$this->Cell($largeur_demi_page , 5 , pdf($texte_periode) , 0 , 1 , 'L' , false , '');
	}

	public function bilan_synthese_ligne_matiere($format,$matiere_nom,$tab_infos_matiere,$total)
	{
		if($format=='multimatiere')
		{
			// Interligne
			$this->SetXY($this->marge_gauche , $this->GetY() + $this->lignes_hauteur*1.5);
		}
		// Intitulé matière
		$this->SetFont('Arial' , 'B' , $this->taille_police*1.25);
		$couleur_fond = ($this->couleur=='oui') ? 'gris_moyen' : 'blanc' ;
		$this->choisir_couleur_fond($couleur_fond);
		$this->Cell(116 , $this->lignes_hauteur*1.5 , pdf($matiere_nom) , 1 , 0 , 'L' , true , '');
		// Diagramme matière
		$this->SetFont('Arial' , 'B' , $this->taille_police);
		$this->afficher_proportion_acquis(80,$this->lignes_hauteur*1.5,$tab_infos_matiere,$total); // 210 - 7 - 7 - 116
		// Interligne
		$this->SetXY($this->marge_gauche , $this->GetY() + $this->lignes_hauteur*1.5);
	}

	public function bilan_synthese_ligne_synthese($synthese_nom,$tab_infos_synthese,$total)
	{
		$this->SetFont('Arial' , '' , $this->taille_police);
		// Diagramme synthèse
		$this->SetFont('Arial' , '' , $this->taille_police*0.8);
		$this->afficher_proportion_acquis(40,$this->lignes_hauteur,$tab_infos_synthese,$total);
		// Intitulé synthèse
		$this->SetFont('Arial' , '' , $this->taille_police);
		$couleur_fond = ($this->couleur=='oui') ? 'gris_clair' : 'blanc' ;
		$this->choisir_couleur_fond($couleur_fond);
		$this->Cell(156 , $this->lignes_hauteur , pdf($synthese_nom) , 1 , 1 , 'L' , true , ''); // 210 - 7 - 7 - 40
	}

	public function bilan_synthese_legende($format)
	{
		// Légende : en bas de page si 'multimatiere', à la suite si 'matiere'
		$ordonnee = ($format=='multimatiere') ?  $this->page_hauteur - $this->marge_bas - $this->lignes_hauteur*0.5 : $this->GetY() + $this->lignes_hauteur*0.5 ;
		$this->afficher_legende($type_legende='acquis' , $ordonnee);
	}

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Méthodes pour la mise en page d'un bilan d'items d'une matiere ou pluridisciplinaire
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	bilan_item_individuel_initialiser()   c'est là que les calculs se font pour une sortie "matiere"
	//	bilan_item_individuel_entete()        c'est là que les calculs se font pour une sortie "multimatiere" ou "selection"
	//	bilan_item_individuel_transdisciplinaire_ligne_matiere()
	//	bilan_item_individuel_debut_ligne_item()
	//	bilan_item_individuel_ligne_synthese()
	//	bilan_item_individuel_legende()
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function bilan_item_individuel_initialiser($format,$cases_nb,$cases_largeur,$lignes_nb,$eleves_nb,$pages_nb_methode)
	{
		$this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droit);
		$this->SetAutoPageBreak(false);
		$this->cases_nb          = $cases_nb;
		$this->cases_largeur     = $cases_largeur;
		$this->reference_largeur = 10;
		$this->synthese_largeur  = $this->page_largeur - $this->marge_gauche - $this->marge_droit - $this->reference_largeur;
		$this->intitule_largeur  = $this->synthese_largeur - (($this->cases_nb+1) * $this->cases_largeur);
		if($format=='matiere')
		{
			// Dans ce cas on met plusieurs élèves par page : on calcule maintenant combien et la hauteur de ligne à prendre
			$hauteur_dispo_par_page = $this->page_hauteur - $this->marge_haut - $this->marge_bas ;
			$lignes_nb_tous_eleves = $eleves_nb * ( 1 + 1 + 1 + $lignes_nb + ($this->legende*2*1) + 2 ) ; // eleves * [ intitulé-matiere-structure + classe-élève + date + lignes dont résumés + légendes + marge ]
			$hauteur_ligne_moyenne = 5;
			$lignes_nb_moyen_par_page = $hauteur_dispo_par_page / $hauteur_ligne_moyenne ;
			$nb_page_moyen = max( 1 , round( $lignes_nb_tous_eleves / $lignes_nb_moyen_par_page ) ); // max 1 pour éviter une division par zéro
			$eleves_nb_par_page = ceil( $eleves_nb / $nb_page_moyen ) ;
			if($pages_nb_methode=='augmente')
			{
				$eleves_nb_par_page = max( 1 , $eleves_nb_par_page-1 ) ; // Sans doute à revoir... un élève demeure forcé sur 1 page...
			}
			// $nb_page_calcule = ceil( $eleves_nb / $eleves_nb_par_page ) ; // devenu inutile
			$lignes_nb_moyen_eleve = $lignes_nb_tous_eleves / $eleves_nb ;
			$lignes_nb_calcule_par_page = $eleves_nb_par_page * $lignes_nb_moyen_eleve ; // $lignes_nb/$nb_page_calcule ne va pas car un élève peut alors être considéré à cheval sur 2 pages
			$hauteur_ligne_calcule = $hauteur_dispo_par_page / $lignes_nb_calcule_par_page ;
			$this->lignes_hauteur = floor($hauteur_ligne_calcule*10)/10 ; // round($hauteur_ligne_calcule,1,PHP_ROUND_HALF_DOWN) à partir de PHP 5.3
			$this->lignes_hauteur = min ( $this->lignes_hauteur , 7.5 ) ;
			// On s'occupe aussi maintenant de la taille de la police
			$this->taille_police  = min($this->lignes_hauteur,$this->cases_largeur) * 1.6 ; // 5mm de hauteur par ligne donne une taille de 8
			$this->taille_police  = min ( $this->taille_police , 10 ) ;
			// Pour forcer à prendre une nouvelle page au 1er élève
			$this->SetXY(0,$this->page_hauteur);
			// Hauteur d'une case
			$this->cases_hauteur = $this->lignes_hauteur;
			$this->calculer_dimensions_images($this->cases_largeur,$this->cases_hauteur);
		}
	}

	public function bilan_item_individuel_entete($format,$matieres_nb,$items_nb,$pages_nb_methode,$texte_format,$texte_periode,$groupe_nom,$eleve_nom,$eleve_prenom)
	{
		if($format=='matiere')
		{
			// La hauteur de ligne a déjà été calculée ; mais il reste à déterminer si on saute une page ou non en fonction de la place restante (et sinon => interligne)
			$hauteur_dispo_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas ;
			$lignes_nb = 1 + 1 + 1 + $items_nb + ($this->legende*2*1) + 2 ; // intitulé-matiere-structure + classe-élève + date + lignes dont résumés + légendes + marge
			if($this->lignes_hauteur*$lignes_nb > $hauteur_dispo_restante)
			{
				$this->AddPage($this->orientation , 'A4');
			}
			else
			{
				// Interligne
				$this->SetXY($this->marge_gauche , $this->GetY() + $this->lignes_hauteur*2);
			}
		}
		elseif( ($format=='multimatiere') || ($format=='selection') )
		{
			// On prend une nouvelle page PDF
			$this->AddPage($this->orientation , 'A4');
			// On calcule la hauteur de la ligne et la taille de la police pour tout faire rentrer sur une page si possible (personnalisée par élève), un minimum de pages sinon
			$hauteur_dispo_par_page = $this->page_hauteur - $this->marge_haut - $this->marge_bas ;
			$lignes_nb = 1 + 1 + 1 + $matieres_nb*1.5 + $items_nb + ($this->legende*2*1) ; // intitulé-structure + classe-élève + date + matières(marge+intitulé) + lignes dont résumés + légendes
			$hauteur_ligne_minimale = 3;
			$hauteur_ligne_maximale = 3+2;
			$nb_pages = 0;
			do
			{
				$nb_pages++;
				$hauteur_ligne_calcule = $nb_pages*$hauteur_dispo_par_page / $lignes_nb ;
			}
			while($hauteur_ligne_calcule < $hauteur_ligne_minimale);
			if($pages_nb_methode=='augmente')
			{
				$nb_pages++;
				$hauteur_ligne_calcule = $nb_pages*$hauteur_dispo_par_page / $lignes_nb ;
			}
			$this->lignes_hauteur = floor($hauteur_ligne_calcule*10)/10 ; // round($hauteur_ligne_calcule,1,PHP_ROUND_HALF_DOWN) à partir de PHP 5.3
			$this->lignes_hauteur = min ( $this->lignes_hauteur , $hauteur_ligne_maximale ) ;
			$this->taille_police  = $this->lignes_hauteur * 1.6 ; // 5mm de hauteur par ligne donne une taille de 8
			$this->taille_police  = min ( $this->taille_police , 10 ) ;
			// Hauteur d'une case
			$this->cases_hauteur = $this->lignes_hauteur;
			$this->calculer_dimensions_images($this->cases_largeur,$this->cases_hauteur);
		}
		// Intitulé (dont éventuellement matière) / structure
		$largeur_demi_page = ( $this->page_largeur - $this->marge_gauche - $this->marge_droit ) / 2;
		$this->SetFont('Arial' , 'B' , $this->taille_police*1.5);
		$this->Cell($largeur_demi_page , $this->lignes_hauteur , pdf('Bilan '.$texte_format) , 0 , 0 , 'L' , false , '');
		$this->Cell($largeur_demi_page , $this->lignes_hauteur , pdf($_SESSION['DENOMINATION']) , 0 , 1 , 'R' , false , '');
		// Classe / élève
		$this->Cell($largeur_demi_page , $this->lignes_hauteur , pdf($groupe_nom.' - '.$eleve_nom.' '.$eleve_prenom) , 0 , 2 , 'L' , false , '');
		// Période
		$this->SetFont('Arial' , '' , $this->taille_police);
		$this->Cell($largeur_demi_page , 5 , pdf($texte_periode) , 0 , 1 , 'L' , false , '');
	}

	public function bilan_item_individuel_transdisciplinaire_ligne_matiere($matiere_nom,$lignes_nb,$eleve_nom,$eleve_prenom)
	{
		$largeur_demi_page = ( $this->page_largeur - $this->marge_gauche - $this->marge_droit ) / 2;
		// La hauteur de ligne a déjà été calculée ; mais il reste à déterminer si on saute une page ou non en fonction de la place restante (et sinon => interligne)
		$hauteur_dispo_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas ;
		$lignes_nb = 1.5 + $lignes_nb ; // matière(marge+intitulé) + lignes dont résumés (on ne compte pas la légende)
		$test_nouvelle_page = ($this->lignes_hauteur*$lignes_nb > $hauteur_dispo_restante) && ($this->GetY() > $this->lignes_hauteur*5) ; // 2e condition pour éviter un saut de page si déjà en haut (à cause d'une liste à rallonge dans une matière)
		if( $test_nouvelle_page )
		{
			$this->AddPage($this->orientation , 'A4');
			$rappel_eleve = true;
		}
		else
		{
			// Interligne
			$this->SetXY($this->marge_gauche , $this->GetY() + $this->lignes_hauteur*0.5);
			$rappel_eleve = false;
		}
		// Intitulé matière + éventuellement rappel élève
		$this->SetFont('Arial' , 'B' , $this->taille_police*1.5);
		if( $test_nouvelle_page )
		{
			$this->Cell($largeur_demi_page , $this->lignes_hauteur , pdf($matiere_nom) , 0 , 0 , 'L' , false , '');
			$this->SetFont('Arial' , 'B' , $this->taille_police);
			$this->Cell($largeur_demi_page , $this->lignes_hauteur , pdf($eleve_nom.' '.$eleve_prenom.' (suite)') , 0 , 1 , 'R' , false , '');
		}
		else
		{
			$this->Cell($largeur_demi_page , $this->lignes_hauteur , pdf($matiere_nom) , 0 , 1 , 'L' , false , '');
		}
	}

	public function bilan_item_individuel_debut_ligne_item($item_ref,$item_texte)
	{
		list($ref_matiere,$ref_suite) = explode('.',$item_ref,2);
		$this->choisir_couleur_fond('gris_clair');
		$this->SetFont('Arial' , '' , $this->taille_police*0.8);
		$this->Cell($this->reference_largeur , $this->cases_hauteur , pdf($ref_suite) , 1 , 0 , 'C' , true , '');
		$this->SetFont('Arial' , '' , $this->taille_police);
		$this->afficher_texte_sur_2lignes_maxi( $item_texte , $this->taille_police , $this->intitule_largeur , $this->cases_hauteur , $bordure=1 , $br=0 , $alignement='L' , $remplissage=false );
		$this->choisir_couleur_fond('blanc');
	}

	public function bilan_item_individuel_ligne_synthese($bilan_texte)
	{
		$this->SetFont('Arial' , '' , $this->taille_police);
		$this->choisir_couleur_fond('gris_moyen');
		$this->Cell($this->reference_largeur , $this->cases_hauteur , '' , 0 , 0 , 'C' , false , '');
		$this->Cell($this->synthese_largeur , $this->cases_hauteur , pdf($bilan_texte) , 1 , 1 , 'R' , true , '');
	}

	public function bilan_item_individuel_legende($format)
	{
		// Légende : à la suite si 'matiere' , en bas de page si 'multimatiere' ou 'selection',
		$ordonnee = ($format=='matiere') ? $this->GetY() + $this->lignes_hauteur*0.2 : $this->page_hauteur - $this->marge_bas - $this->lignes_hauteur*1.5 ;
		$this->afficher_legende($type_legende='notes'  , $ordonnee);
		$this->afficher_legende($type_legende='acquis' , $this->GetY() + $this->lignes_hauteur*0.2);
	}

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Méthodes pour la mise en page d'une grille d'items d'un référentiel
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	grille_referentiel_initialiser()
	//	grille_referentiel_entete()
	//	grille_referentiel_domaine()
	//	grille_referentiel_theme()
	//	grille_referentiel_item()
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function grille_referentiel_initialiser($cases_nb,$cases_largeur,$lignes_nb,$colonne_vide)
	{
		// On calcule la hauteur de la ligne et la taille de la police pour tout faire rentrer sur une page si possible, un minimum de pages sinon
		$hauteur_dispo_par_page = $this->page_hauteur - $this->marge_haut - $this->marge_bas ;
		$lignes_nb = 1 + 1 + 1 + $lignes_nb + $this->legende ; // intitulé-structure + matière-niveau-élève + marge (1 & un peu plus car aussi avant domaines) + lignes (domaines+thèmes+items) + légende
		$hauteur_ligne_minimale = 3.5;
		$hauteur_ligne_maximale = 5;
		$nb_pages = 0;
		do
		{
			$nb_pages++;
			$hauteur_ligne_calcule = $nb_pages*$hauteur_dispo_par_page / $lignes_nb ;
		}
		while($hauteur_ligne_calcule < $hauteur_ligne_minimale);
		if($nb_pages>1)
		{
			$hauteur_ligne_calcule -= $nb_pages*0.1; // Tenter de contrebalancer un peu le pb des thèmes non coupés
		}
		$this->lignes_hauteur = floor($hauteur_ligne_calcule*10)/10 ; // round($hauteur_ligne_calcule,1,PHP_ROUND_HALF_DOWN) à partir de PHP 5.3
		$this->lignes_hauteur = min ( $this->lignes_hauteur , $hauteur_ligne_maximale ) ;
		$this->taille_police  = $this->lignes_hauteur * 1.6 ; // 5mm de hauteur par ligne donne une taille de 8
		$this->taille_police  = min ( $this->taille_police , 10 ) ;
		// La suite est classique
		$this->cases_nb          = $cases_nb;
		$this->cases_largeur     = $cases_largeur;
		$this->cases_hauteur     = $this->lignes_hauteur;
		$this->colonne_vide_largeur = $colonne_vide;
		$this->reference_largeur = 10;
		$this->intitule_largeur  = $this->page_largeur - $this->marge_gauche - $this->marge_droit - $this->reference_largeur - ($this->cases_nb * $this->cases_largeur) - $this->colonne_vide_largeur ;
		$this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droit);
		$this->SetAutoPageBreak(false);
		$this->calculer_dimensions_images($this->cases_largeur,$this->cases_hauteur);
	}

	public function grille_referentiel_entete($matiere_nom,$niveau_nom,$eleve_id,$eleve_nom,$eleve_prenom)
	{
		// On prend une nouvelle page PDF pour chaque élève
		$this->AddPage($this->orientation , 'A4');
		$this->SetXY($this->marge_gauche,$this->marge_haut);
		$largeur_demi_page = ( $this->page_largeur - $this->marge_gauche - $this->marge_droit ) / 2;
		// intitulé-structure
		$this->SetFont('Arial' , 'B' , $this->taille_police*1.4);
		$this->Cell($largeur_demi_page , $this->lignes_hauteur , pdf('Grille d\'items d\'un référentiel') , 0 , 0 , 'L' , false , '');
		$this->Cell($largeur_demi_page , $this->lignes_hauteur , pdf($_SESSION['DENOMINATION']) , 0 , 1 , 'R' , false , '');
		// matière-niveau-élève
		$this->Cell($largeur_demi_page , $this->lignes_hauteur , pdf($matiere_nom.' - Niveau '.$niveau_nom) , 0 , 0 , 'L' , false , '');
		if($eleve_id)
		{
			$this->Cell($largeur_demi_page , $this->lignes_hauteur , pdf($eleve_nom.' '.$eleve_prenom) , 0 , 1 , 'R' , false , '');
		}
		else
		{
			$this->choisir_couleur_trait('gris_moyen');
			$this->SetLineWidth(0.1);
			$this->Line($this->page_largeur-$this->marge_droit-75 , $this->marge_haut+2*$this->lignes_hauteur , $this->page_largeur-$this->marge_droit , $this->marge_haut+2*$this->lignes_hauteur);
			$this->SetDrawColor(0 , 0 , 0);
		}
		$this->SetXY($this->marge_gauche,$this->marge_haut+2.5*$this->lignes_hauteur);
	}

	public function grille_referentiel_domaine($domaine_nom,$domaine_nb_lignes)
	{
		$hauteur_requise = $this->cases_hauteur * $domaine_nb_lignes;
		$hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
		if($hauteur_requise > $hauteur_restante)
		{
			// Prendre une nouvelle page si ça ne rentre pas
			$this->AddPage($this->orientation , 'A4');
		}
		$this->SetFont('Arial' , 'B' , $this->taille_police*1.25);
		$this->SetXY(15 , $this->GetY()+1);
		$this->Cell($this->intitule_largeur , $this->cases_hauteur , pdf($domaine_nom) , 0 , 1 , 'L' , false , '');
	}

	public function grille_referentiel_theme($theme_ref,$theme_nom,$theme_nb_lignes)
	{
		$hauteur_requise = $this->cases_hauteur * $theme_nb_lignes;
		$hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
		if($hauteur_requise > $hauteur_restante)
		{
			// Prendre une nouvelle page si ça ne rentre pas
			$this->AddPage($this->orientation , 'A4');
		}
		$this->SetFont('Arial' , 'B' , $this->taille_police);
		$this->choisir_couleur_fond('gris_moyen');
		$this->Cell($this->reference_largeur , $this->cases_hauteur , pdf($theme_ref) , 1 , 0 , 'C' , true , '');
		$this->Cell($this->intitule_largeur , $this->cases_hauteur , pdf($theme_nom) , 1 , 1 , 'L' , true , '');
		if($this->colonne_vide_largeur)
		{
			// Ajouter une case vide sur la hauteur du nombre d'items du thème
			$abscisse = $this->GetX();
			$ordonnee = $this->GetY();
			$this->SetXY( $this->page_largeur - $this->marge_droit - $this->colonne_vide_largeur , $ordonnee );
			$this->Cell($this->colonne_vide_largeur , $this->cases_hauteur * ($theme_nb_lignes-1) , '' , 1 , 0 , '' , false , '');
			$this->SetXY( $abscisse , $ordonnee );
		}
		$this->SetFont('Arial' , '' , $this->taille_police);
	}

	public function grille_referentiel_item($item_ref,$item_texte)
	{
		$this->choisir_couleur_fond('gris_clair');
		$this->Cell($this->reference_largeur , $this->cases_hauteur , pdf($item_ref) , 1 , 0 , 'C' , true , '');
		$this->afficher_texte_sur_2lignes_maxi( $item_texte , $this->taille_police , $this->intitule_largeur , $this->cases_hauteur , $bordure=1 , $br=0 , $alignement='L' , $remplissage=false );
		$this->choisir_couleur_fond('blanc');
	}

	public function grille_referentiel_legende()
	{
		$this->afficher_legende($type_legende='notes'  , $this->page_hauteur - $this->marge_bas - $this->lignes_hauteur*0.8);
	}

	/*
		La suite de ce fichier pourrait être reprise : utilisation de $this->taille_police, optimisations éventuelles (pas trop pour le socle dont le contenu est figé).
	*/

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Méthodes pour la mise en page d'un releve d'attestation de socle commun
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	releve_socle_initialiser()
	//	releve_socle_entete()
	//	releve_socle_pilier()
	//	releve_socle_section()
	//	releve_socle_item()
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function releve_socle_initialiser($test_affichage_Pourcentage,$test_affichage_Validation)
	{
		$this->cases_hauteur       = 4.5; // Dans le cas d'un bilan juste sur un pilier, il faudrait oprimiser la hauteur de ligne...
		$this->taille_police       = 6;
		$this->pourcentage_largeur = 27.5;
		$this->validation_largeur  = 17.5;
		$this->retrait_pourcentage = ( $test_affichage_Pourcentage ) ? $this->pourcentage_largeur : 0;
		$retrait_validation        = ( $test_affichage_Validation ) ? $this->validation_largeur : 0;
		$this->item_largeur        = $this->page_largeur - $this->marge_gauche - $this->marge_droit - $this->retrait_pourcentage - $retrait_validation;
		$this->section_largeur     = $this->item_largeur;
		$this->pilier_largeur      = $this->section_largeur - $retrait_validation;
		$this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droit);
		$this->SetAutoPageBreak(false);
	}

	public function releve_socle_identite()
	{
		// On met le document au nom de l'élève, ou on établit un document générique
		if(!$this->eleve_id)
		{
			$this->choisir_couleur_trait('gris_moyen');
			$this->SetLineWidth(0.1);
			$this->Line($this->page_largeur-$this->marge_droit-75 , $this->marge_haut+2*$this->cases_hauteur , $this->page_largeur-$this->marge_droit , $this->marge_haut+2*$this->cases_hauteur);
			$this->SetDrawColor(0 , 0 , 0);
		}
		$this->SetFont('Arial' , 'B' , $this->taille_police*1.5);
		$this->SetXY($this->page_largeur-$this->marge_droit-50 , max($this->marge_haut,$this->GetY()-2*$this->cases_hauteur) ); // Soit c'est une nouvelle page, soit il ne faut pas se mettre en haut de la page
		$this->Cell(50 , $this->cases_hauteur , pdf($_SESSION['DENOMINATION']) , 0 , 2 , 'R' , false , '');
		$this->Cell(50 , $this->cases_hauteur , pdf($this->eleve_nom.' '.$this->eleve_prenom) , 0 , 2 , 'R' , false , '');
	}

	public function releve_socle_entete($titre,$palier_nom,$break,$eleve_id,$eleve_nom,$eleve_prenom)
	{
		// On prend une nouvelle page PDF pour chaque élève en cas d'affichage d'un palier avec tous les piliers ; pour un seul pilier, on étudie la place restante... tout en forçant une nouvelle page pour le 1er élève
		if( ($break==0) || ($this->GetY()==0) )
		{
			$this->AddPage($this->orientation , 'A4');
			$this->SetXY($this->marge_gauche,$this->marge_haut);
		}
		else
		{
			$hauteur_requise = $this->cases_hauteur * ($break + 2 + 0.5 + 1); // titres + marge + interligne
			$hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
			if($hauteur_requise > $hauteur_restante)
			{
				$this->AddPage($this->orientation , 'A4');
				$this->SetXY($this->marge_gauche,$this->marge_haut);
			}
			else
			{
				$this->SetXY($this->marge_gauche,$this->GetY()+$this->cases_hauteur);
			}
		}
		// Intitulé
		$this->SetFont('Arial' , 'B' , $this->taille_police*1.5);
		$this->Cell($this->page_largeur-$this->marge_droit-75 , $this->cases_hauteur , pdf($titre) , 0 , 2 , 'L' , false , '');
		$this->Cell($this->page_largeur-$this->marge_droit-75 , $this->cases_hauteur , pdf($palier_nom) , 0 , 2 , 'L' , false , '');
		// Nom / prénom
		$this->eleve_id     = $eleve_id;
		$this->eleve_nom    = $eleve_nom;
		$this->eleve_prenom = $eleve_prenom;
		$this->releve_socle_identite();
	}

	public function releve_socle_pilier($pilier_nom,$pilier_nb_lignes,$test_affichage_Validation,$tab_pilier_validation,$drapeau_langue)
	{
		$this->SetXY($this->marge_gauche+$this->retrait_pourcentage , $this->GetY() + 0.5*$this->cases_hauteur);
		$hauteur_requise = $this->cases_hauteur * $pilier_nb_lignes;
		$hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
		if($hauteur_requise > $hauteur_restante)
		{
			// Prendre une nouvelle page si ça ne rentre pas, avec recopie de l'identité de l'élève
			$this->AddPage($this->orientation , 'A4');
			$this->releve_socle_identite();
			$this->SetXY($this->marge_gauche+$this->retrait_pourcentage , $this->GetY()+2);
		}
		$this->SetFont('Arial' , 'B' , $this->taille_police*1.25);
		$this->choisir_couleur_fond('gris_moyen');
		$br = $test_affichage_Validation ? 0 : 1 ;
		$this->Cell($this->pilier_largeur , $this->cases_hauteur , pdf($pilier_nom) , 1 , $br , 'L' , true , '');
		if($test_affichage_Validation)
		{
			$this->afficher_etat_validation('B',$tab_pilier_validation);
		}
		if($drapeau_langue)
		{
			$this->Image('./_img/drapeau/'.$drapeau_langue.'.gif',$this->GetX()+$this->pilier_largeur-$this->cases_hauteur-0.5,$this->GetY()-$this->cases_hauteur,$this->cases_hauteur,$this->cases_hauteur,'GIF');
		}
	}

	public function releve_socle_section($section_nom)
	{
		$this->SetXY($this->marge_gauche+$this->retrait_pourcentage , $this->GetY());
		$this->SetFont('Arial' , 'B' , $this->taille_police);
		$this->choisir_couleur_fond('gris_moyen');
		$this->Cell($this->section_largeur , $this->cases_hauteur , pdf($section_nom) , 1 , 1 , 'L' , true , '');
	}

	public function releve_socle_item($item_nom,$test_affichage_Pourcentage,$tab_item_pourcentage,$test_affichage_Validation,$tab_item_validation)
	{
		// Case pourcentage
		if($test_affichage_Pourcentage)
		{
			$this->afficher_pourcentage_acquis('',$tab_item_pourcentage,$detail=true);
		}
		// Case intitulé
		$this->choisir_couleur_fond('gris_clair');
		$this->SetFont('Arial' , '' , $this->taille_police);
		$br = $test_affichage_Validation ? 0 : 1 ;
		$this->afficher_texte_sur_2lignes_maxi( $item_nom , $this->taille_police , $this->item_largeur , $this->cases_hauteur , $bordure=1 , $br , $alignement='L' , $remplissage=true );
		// Case validation
		if($test_affichage_Validation)
		{
			$this->afficher_etat_validation('',$tab_item_validation);
		}
	}

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Méthodes pour la mise en page d'une synthèse des validations du socle commun
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	releve_synthese_socle_initialiser()
	//	releve_synthese_socle_entete()
	//	releve_synthese_socle_validation_eleve()
	//	releve_synthese_socle_pourcentage_eleve()
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function releve_synthese_socle_initialiser($titre_info,$groupe_nom,$palier_nom,$eleves_nb,$items_nb,$piliers_nb)
	{
		$this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droit);
		$this->AddPage($this->orientation , 'A4');
		$this->SetAutoPageBreak(true);
		$this->eleve_largeur = 40;
		$this->cases_largeur = ($this->page_largeur - $this->marge_gauche - $this->marge_droit - $this->eleve_largeur - $piliers_nb) / ($items_nb); // - intercolonne de 1 * nb piliers
		$this->cases_hauteur = ($this->page_hauteur - $this->marge_haut - $this->marge_bas - $this->taille_police - $eleves_nb) / ($eleves_nb+1); // - titre de 5 - ( interligne de 1 * nb élèves )
		$this->cases_hauteur = min($this->cases_hauteur,10);
		$this->taille_police = 8;
		// Intitulés
		$this->SetFont('Arial' , 'B' , $this->taille_police*1.5);
		$this->Cell(0 , $this->taille_police , pdf('Synthèse de maîtrise du socle : '.$titre_info.' - '.$groupe_nom.' - '.$palier_nom) , 0 , 2 , 'L' , false , '');
	}

	public function releve_synthese_socle_entete($tab_pilier)
	{
		$this->SetFont('Arial' , 'B' , $this->taille_police);
		$this->SetXY($this->marge_gauche+$this->eleve_largeur,$this->marge_haut+$this->taille_police);
		$this->choisir_couleur_fond('gris_fonce');
		foreach($tab_pilier as $tab)
		{
			extract($tab);	// $pilier_ref $pilier_nom $pilier_nb_entrees
			$texte = ($pilier_nb_entrees>10) ? 'Compétence ' : 'Comp. ' ;
			$this->SetX( $this->GetX()+1 );
			$this->Cell($pilier_nb_entrees*$this->cases_largeur , $this->cases_hauteur , pdf($texte.$pilier_ref) , 1 , 0 , 'C' , true , '');
		}
		// positionnement pour la suite
		$this->SetFont('Arial' , '' , $this->taille_police);
		$this->SetXY( $this->marge_gauche , $this->GetY()+$this->cases_hauteur+1 );
	}

	public function releve_synthese_socle_validation_eleve($eleve_id,$eleve_nom,$eleve_prenom,$tab_user_pilier,$tab_user_entree,$tab_pilier,$tab_socle,$drapeau_langue)
	{
		$this->choisir_couleur_fond('gris_moyen');
		$this->Cell($this->eleve_largeur , $this->cases_hauteur , pdf($eleve_nom.' '.$eleve_prenom) , 1 , 0 , 'L' , true , '');
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
			extract($tab);	// $pilier_ref $pilier_nom $pilier_nb_entrees
			$this->SetX( $this->GetX()+1 );
			$this->choisir_couleur_fond('v'.$tab_user_pilier[$eleve_id][$pilier_id]['etat']);
			$this->Cell($pilier_nb_entrees*$this->cases_largeur , $demi_hauteur , '' , 1 , 0 , 'C' , true , '');
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
				$couleur = ( ($tab_user_pilier[$eleve_id][$pilier_id]['etat']==1) && ($tab_user_entree[$eleve_id][$socle_id]['etat']==2) && (!$_SESSION['USER_DALTONISME']) ) ? 'gris_clair' : 'v'.$tab_user_entree[$eleve_id][$socle_id]['etat'] ;
				$this->choisir_couleur_fond($couleur);
				$this->Cell($this->cases_largeur , $demi_hauteur , '' , 1 , 0 , 'C' , true , '');
			}
		}
		// positionnement pour la suite
		$this->SetXY( $this->marge_gauche , $this->GetY()+$demi_hauteur+1 );
	}

	public function releve_synthese_socle_pourcentage_eleve($eleve_id,$eleve_nom,$eleve_prenom,$tab_score_socle_eleve,$tab_socle,$drapeau_langue)
	{
		$this->pourcentage_largeur = $this->cases_largeur;
		$this->choisir_couleur_fond('gris_moyen');
		$this->SetFont('Arial' , '' , $this->taille_police);
		$this->Cell($this->eleve_largeur , $this->cases_hauteur , pdf($eleve_nom.' '.$eleve_prenom) , 1 , 0 , 'L' , true , '');
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
				$this->afficher_pourcentage_acquis('',$tab_score_socle_eleve[$socle_id][$eleve_id],$detail=false);
			}
		}
		// positionnement pour la suite
		$this->SetXY( $this->marge_gauche , $this->GetY()+$this->cases_hauteur+1 );
	}

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	//	Méthodes pour la mise en page d'un bilan de synthèse d'un groupe sur une période
	//	bilan_periode_synthese_initialiser() bilan_periode_synthese_entete() bilan_periode_synthese_pourcentages()
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

	public function bilan_periode_synthese_initialiser($eleve_nb,$item_nb)
	{
		$this->cases_largeur     = ($this->page_largeur - $this->marge_gauche - $this->marge_droit - 2) / ($item_nb+5); // -2 pour une petite marge ; 2 colonnes ajoutées + 3 colonnes comptés pour l'identité
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
		$this->calculer_dimensions_images($this->cases_largeur,$this->cases_hauteur);
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

		// première case
		if($moyenne_pourcent===false)
		{
			$this->choisir_couleur_fond('blanc');
			$this->Cell($this->cases_largeur , $this->cases_hauteur , '-' , 1 , $direction_after_case1 , 'C' , true , '');
		}
		else
		{
					if($moyenne_pourcent<$_SESSION['CALCUL_SEUIL']['R']) {$this->choisir_couleur_fond($this->tab_choix_couleur['NA']);}
			elseif($moyenne_pourcent>$_SESSION['CALCUL_SEUIL']['V']) {$this->choisir_couleur_fond($this->tab_choix_couleur['A']);}
			else                                                     {$this->choisir_couleur_fond($this->tab_choix_couleur['VA']);}
			$score_affiche = (mb_substr_count($_SESSION['DROIT_VOIR_SCORE_BILAN'],$_SESSION['USER_PROFIL'])) ? $moyenne_pourcent.'%' : '' ;
			$this->Cell($this->cases_largeur , $this->cases_hauteur , $score_affiche , 1 , $direction_after_case1 , 'C' , true , '');
		}

		// pour les 2 cases en diagonales, une case invisible permet de se positionner correctement
		if($last_colonne && $last_ligne)
		{
			$this->Cell($this->cases_largeur , $this->cases_hauteur , '' , 0 , 0 , 'C' , false , '');
		}

		// deuxième case
		if($moyenne_pourcent===false)
		{
			$this->Cell($this->cases_largeur , $this->cases_hauteur , '-' , 1 , $direction_after_case2 , 'C' , true , '');
		}
		else
		{
					if($moyenne_nombre<$_SESSION['CALCUL_SEUIL']['R']) {$this->choisir_couleur_fond($this->tab_choix_couleur['NA']);}
			elseif($moyenne_nombre>$_SESSION['CALCUL_SEUIL']['V']) {$this->choisir_couleur_fond($this->tab_choix_couleur['A']);}
			else                                                   {$this->choisir_couleur_fond($this->tab_choix_couleur['VA']);}
			$score_affiche = (mb_substr_count($_SESSION['DROIT_VOIR_SCORE_BILAN'],$_SESSION['USER_PROFIL'])) ? $moyenne_nombre.'%' : '' ;
			$this->Cell($this->cases_largeur , $this->cases_hauteur , $score_affiche , 1 , $direction_after_case2 , 'C' , true , '');
		}

		// pour la dernière ligne, mais pas pour les 2 dernières cases, se repositionner à la bonne ordonnée
		if($last_ligne && !$last_colonne)
		{
			$memo_x = $this->GetX();
			$this->SetXY($memo_x , $memo_y);
		}
	}

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	//	Méthodes pour la mise en page d'un tableau vierge de saisie d'évaluation
	//	tableau_saisie_initialiser() tableau_saisie_reference_devoir() tableau_saisie_reference_eleve() tableau_saisie_reference_item() tableau_saisie_cellule()
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

	public function tableau_saisie_initialiser($eleve_nb,$item_nb)
	{
		$reference_largeur_minimum = 50;
		$cases_hauteur_maximum     = 25;
		$this->cases_largeur     = 10; // valeur par défaut ; diminué si pas assez de place pour la référence de l'item
		$this->etiquette_hauteur = 40; // valeur fixe
		$this->reference_largeur = $this->page_largeur - $this->marge_gauche - $this->marge_droit - ($eleve_nb * $this->cases_largeur);
		if($this->reference_largeur < $reference_largeur_minimum)
		{
			$this->reference_largeur = $reference_largeur_minimum;
			$this->cases_largeur     = ($this->page_largeur - $this->marge_gauche - $this->marge_droit - $this->reference_largeur) / $eleve_nb;
		}
		$this->cases_hauteur     = ($this->page_hauteur - $this->marge_haut - $this->marge_bas - $this->etiquette_hauteur) / $item_nb;
		$this->cases_hauteur     = min($this->cases_hauteur,$cases_hauteur_maximum);
		$this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droit);
		$this->AddPage($this->orientation , 'A4');
		$this->SetAutoPageBreak(true);
		$this->calculer_dimensions_images($this->cases_largeur,$this->cases_hauteur);
	}

	public function tableau_saisie_reference_devoir($texte)
	{
		$hauteur_tiers = $this->etiquette_hauteur / 3 ;
		$tab_lignes = explode(':::',$texte);
		$this->SetXY($this->marge_gauche , $this->marge_haut);
		$this->SetFont('Arial' , 'B' , $this->taille_police);
		$this->Cell($this->reference_largeur , $hauteur_tiers , pdf($tab_lignes[0]) , 0 , 2 , 'C' , false , '');
		$this->Cell($this->reference_largeur , $hauteur_tiers , pdf($tab_lignes[1]) , 0 , 2 , 'C' , false , '');
		$this->Cell($this->reference_largeur , $hauteur_tiers , pdf($tab_lignes[2]) , 0 , 2 , 'C' , false , '');
		$this->SetXY($this->marge_gauche , $this->marge_haut);
		$this->SetFont('Arial' , '' , $this->taille_police);
		$this->Cell($this->reference_largeur , $this->etiquette_hauteur , '' , 0 , 0 , 'C' , false , '');
	}

	public function tableau_saisie_reference_eleve($texte)
	{
		$memo_x = $this->GetX();
		$memo_y = $this->GetY();
		$this->choisir_couleur_fond('gris_clair');
		$this->Cell($this->cases_largeur , $this->etiquette_hauteur , '' , 1 , 0 , 'C' , true , '');
		$this->TextWithDirection($memo_x+($this->cases_largeur)/2 +1 , $memo_y+$this->etiquette_hauteur-2, pdf($texte) , $direction='U');
		$this->SetXY($memo_x+$this->cases_largeur , $memo_y);
	}

	public function tableau_saisie_reference_item($item_intro,$item_nom)
	{
		$memo_x = $this->GetX();
		$memo_y = $this->GetY();
		$this->choisir_couleur_fond('gris_clair');
		$this->Cell($this->reference_largeur , $this->cases_hauteur , '' , 1 , 0 , 'L' , true , '');
		$this->SetXY($memo_x , $memo_y+1);
		$this->SetFont('Arial' , 'B' , $this->taille_police);
		$this->Cell($this->reference_largeur , 3 , pdf($item_intro) , 0 , 1 , 'L' , false , '');
		$this->SetFont('Arial' , '' , $this->taille_police);
		$this->MultiCell($this->reference_largeur , 3 , pdf($item_nom) , 0 , 'L' , false , '');
		$this->SetXY($memo_x+$this->reference_largeur , $memo_y);
	}

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	//	Méthodes pour la mise en page d'un bilan d'un devoir : répartition quantitative ou nominative
	//	tableau_devoir_repartition_quantitative_initialiser() tableau_devoir_repartition_nominative_initialiser()
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

	public function tableau_devoir_repartition_quantitative_initialiser($item_nb)
	{
		$cases_hauteur_maximum   = 20;
		$this->cases_largeur     = 20; // valeur fixe
		$this->reference_largeur = $this->page_largeur - $this->marge_gauche - $this->marge_droit - (4 * $this->cases_largeur);
		$this->etiquette_hauteur = 10; // valeur fixe
		$this->cases_hauteur     = ($this->page_hauteur - $this->marge_haut - $this->marge_bas - $this->etiquette_hauteur) / $item_nb;
		$this->cases_hauteur     = min($this->cases_hauteur,$cases_hauteur_maximum);
		$this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droit);
		$this->AddPage($this->orientation , 'A4');
		$this->SetAutoPageBreak(true);
		$this->calculer_dimensions_images($this->cases_largeur,$this->etiquette_hauteur);
	}

	public function tableau_devoir_repartition_nominative_initialiser($lignes_nb)
	{
		$this->cases_largeur     = 35; // valeur fixe
		$this->reference_largeur = $this->page_largeur - $this->marge_gauche - $this->marge_droit - (4 * $this->cases_largeur);
		$this->etiquette_hauteur = 10; // valeur fixe
		$lignes_hauteur_maximum  = 5;
		$this->lignes_hauteur    = ($this->page_hauteur - $this->marge_haut - $this->marge_bas - $this->etiquette_hauteur) / $lignes_nb;
		$this->lignes_hauteur    = min($this->lignes_hauteur,$lignes_hauteur_maximum);
		$this->lignes_hauteur    = max($this->cases_hauteur,3.5); // pas moins de 3,5
		$this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droit);
		$this->AddPage($this->orientation , 'A4');
		$this->SetAutoPageBreak(false);
		$this->calculer_dimensions_images($this->cases_largeur,$this->etiquette_hauteur);
	}

	public function tableau_devoir_repartition_nominative_entete($descriptif,$tab_init_quantitatif,$tab_repartition_quantitatif)
	{
		// on calcule la hauteur de la case
		$this->cases_hauteur = $this->lignes_hauteur * max(4,max($tab_repartition_quantitatif));
		// On prend une nouvelle page PDF si besoin et y remettre la ligne d'entête si y a pas assez de place
		if($this->GetY()>$this->marge_haut)
		{
			// on regarde s'il y a la place
			$hauteur_requise = $this->cases_hauteur;
			$hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
			if($hauteur_requise > $hauteur_restante)
			{
				$this->AddPage($this->orientation , 'A4');
			}
		}
		// 1ère ligne : référence des codes
		if($this->GetY()==$this->marge_haut)
		{
			$this->tableau_saisie_reference_devoir($descriptif);
			$this->SetXY($this->marge_gauche+$this->reference_largeur , $this->marge_haut);
			foreach($tab_init_quantitatif as $note=>$vide)
			{
				$this->afficher_note_lomer($note,$border=1,$br=0);
			}
			$this->SetXY($this->marge_gauche , $this->marge_haut+$this->etiquette_hauteur);
		}
	}

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	//	Méthodes pour la mise en page d'un cartouche
	//	cartouche_initialiser() cartouche_entete() cartouche_minimal_competence() cartouche_complet_competence() cartouche_interligne()
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

	public function cartouche_initialiser($detail,$item_nb)
	{
		$this->cases_largeur     = ($detail=='minimal') ? ($this->page_largeur - $this->marge_gauche - $this->marge_droit) / $item_nb : 10 ;
		$this->cases_hauteur     = 5 ;
		$this->reference_largeur = 15 ;
		$this->intitule_largeur  = ($detail=='minimal') ? 0 : $this->page_largeur - $this->marge_gauche - $this->marge_droit - $this->reference_largeur - $this->cases_largeur ;
		$this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droit);
		$this->AddPage($this->orientation , 'A4');
		$this->SetAutoPageBreak(false);
		$this->calculer_dimensions_images($this->cases_largeur,$this->cases_hauteur);
	}

	public function cartouche_entete($texte_entete,$lignes_nb)
	{
		// On prend une nouvelle page PDF si besoin
		$hauteur_requise = $this->cases_hauteur * $lignes_nb;
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

	public function cartouche_minimal_competence($item_ref,$note)
	{
		$memo_x = $this->GetX();
		$memo_y = $this->GetY();
		list($ref_matiere,$ref_suite) = explode('.',$item_ref,2);
		$this->SetFont('Arial' , '' , 7);
		$this->Cell($this->cases_largeur , $this->cases_hauteur/2 , pdf($ref_matiere) , 0 , 2 , 'C' , false , '');
		$this->Cell($this->cases_largeur , $this->cases_hauteur/2 , pdf($ref_suite) , 0 , 2 , 'C' , false , '');
		$this->SetFont('Arial' , '' , 8);
		$this->SetXY($memo_x , $memo_y);
		$this->Cell($this->cases_largeur , $this->cases_hauteur , '' , 1 , 2 , 'C' , false , '');
		$this->afficher_note_lomer($note,$border=1,$br=0);
		$this->SetXY($memo_x+$this->cases_largeur , $memo_y);
	}

	public function cartouche_complet_competence($item_ref,$item_intitule,$note)
	{
		$memo_x = $this->GetX();
		$memo_y = $this->GetY();
		list($ref_matiere,$ref_suite) = explode('.',$item_ref,2);
		$this->SetFont('Arial' , '' , 7);
		$this->Cell($this->reference_largeur , $this->cases_hauteur/2 , pdf($ref_matiere) , 0 , 2 , 'C' , false , '');
		$this->Cell($this->reference_largeur , $this->cases_hauteur/2 , pdf($ref_suite) , 0 , 2 , 'C' , false , '');
		$this->SetFont('Arial' , '' , 8);
		$this->SetXY($memo_x , $memo_y);
		$this->Cell($this->reference_largeur , $this->cases_hauteur , '' , 1 , 0 , 'C' , false , '');
		$this->afficher_texte_sur_2lignes_maxi( $item_intitule , 8 , $this->intitule_largeur , $this->cases_hauteur , $bordure=1 , $br=0 , $alignement='L' , $remplissage=false );
		$this->afficher_note_lomer($note,$border=1,$br=1);
	}

	public function cartouche_interligne($nb_lignes)
	{
		$this->SetXY($this->marge_gauche , $this->GetY() + $nb_lignes*$this->cases_hauteur);
	}

}
?>
