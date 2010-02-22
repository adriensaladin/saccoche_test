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
 * Pour tester la validité d'un document XML, on peut utiliser un analyseur syntaxique XML : http://fr3.php.net/manual/fr/book.xml.php
 * Voir en particulier l'exemple http://fr3.php.net/manual/fr/example.xml-structure.php
 * 
 * Mais ceci ne permet pas de vérifier la conformité d'un XML avec une DTD.
 * DOMDocument le permet : http://fr2.php.net/manual/fr/domdocument.validate.php
 * Mais d'une part ça emmet des warnings et d'autre part ça ne retourne qu'un booléen sans détails sur les erreurs trouvées
 * 
 * Pour y remédier on peut utiliser cette extention de classe : http://fr2.php.net/manual/fr/domdocument.validate.php
 * Mais attention : il faut lui fournir un objet DOMDocument et load ou loadXML provoquent des warnings préliminaires si le XML est mal formé.
 * 
 * Ma solution est d'utiliser :
 * 1. dans un premier temps l'analyseur syntaxique XML xml_parse pour vérifier que le XML est bien formé
 * 2. dans un second temps l'extention de classe MyDOMDocument pour vérifier la conformité avec la DTD
 * 
 * J'en ai fait la fonction ci-dessous "analyser_XML($fichier)"
 * 
 */

class MyDOMDocument
{
	private $_delegate;
	private $_validationErrors;

	public function __construct (DOMDocument $pDocument)
	{
		$this->_delegate = $pDocument;
		$this->_validationErrors = array();
	}

	public function __call ($pMethodName, $pArgs)
	{
		if ($pMethodName == "validate")
		{
			$eh = set_error_handler(array($this, "onValidateError"));
			$rv = $this->_delegate->validate();
			if ($eh)
			{
				set_error_handler($eh);
			}
			return $rv;
		}
		else
		{
			return call_user_func_array(array($this->_delegate, $pMethodName), $pArgs);
		}
	}

	public function __get ($pMemberName)
	{
		if ($pMemberName == "errors")
		{
			return $this->_validationErrors;
		}
		else
		{
			return $this->_delegate->$pMemberName;
		}
	}

	public function __set ($pMemberName, $pValue)
	{
		$this->_delegate->$pMemberName = $pValue;
	}

	public function onValidateError ($pNo, $pString, $pFile = null, $pLine = null, $pContext = null)
	{
		$this->_validationErrors[] = preg_replace("/^.+: */", "", $pString);
	}
}

function analyser_XML($fichier)
{
	// Récupération du contenu du fichier
	$fcontenu = file_get_contents($fichier);
	// Convertir en UTF-8 si besoin (la chaine + le fichier)
	if( (mb_detect_encoding($fcontenu,"auto",TRUE)!='UTF-8') || (!mb_check_encoding($fcontenu,'UTF-8')) )
	{
		$fcontenu = mb_convert_encoding($fcontenu,'UTF-8','Windows-1252'); // Si on utilise utf8_encode() ou mb_convert_encoding() sans le paramètre 'Windows-1252' ça pose des pbs pour '’' 'Œ' 'œ' etc.
		file_put_contents($fichier,$fcontenu);
	}
	// Analyse XML (s'arrête à la 1ère erreur trouvée)
	$xml_parser = xml_parser_create();
	$valid_XML = xml_parse($xml_parser , $fcontenu , TRUE);
	if(!$valid_XML)
	{
		return sprintf("Erreur XML ligne %d (%s)" , xml_get_current_line_number($xml_parser) , xml_error_string(xml_get_error_code($xml_parser)));
	}
	xml_parser_free($xml_parser);
	// Analyse DTD (renvoie un tableau d'erreurs, affiche la dernière)
	$xml = new DOMDocument;
	$xml -> load($fichier);
	$xml = new MyDOMDocument($xml);
	$valid_DTD = $xml->validate();
	if(!$valid_DTD)
	{
		return "Erreur DTD : ".end($xml->errors);
	}
	// Tout est ok
	return 'ok';
}

?>