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

/**
 * Fonction permettant de lire le contenu d'une url distante.
 * Permet par exemple de tester si un ticket SSO envoyé par un autre site est valide.
 * @author Sébastien Cogez <sebastien.cogez@sesamath.net>
 * 
 * Exemple d'utilisation :
 * require_once('./_inc/class.httprequest.php');
 * $verif_requete = new HTTPRequest('http://site.com/ticket.php?user_id=...&ticket_site=...');
 * $verif_reponse = $verif_requete->DownloadToString();
 */

class HTTPRequest
{
	var $_fp;       // HTTP socket
	var $_url;      // full URL
	var $_host;     // HTTP host
	var $_protocol; // protocol (HTTP/HTTPS)
	var $_uri;      // request URI
	var $_port;     // port
	// scan url
	function _scan_url()
	{
		$req = parse_url($this->_url);
		$this->_host     = $req['host'];
		$this->_protocol = isset($req['scheme']) ? $req['scheme']    : 'http' ;
		$this->_port     = isset($req['port'])   ? $req['port']      : 80;
		$this->_uri      = isset($req['path'])   ? $req['path']      : '/';
		$this->_query    = isset($req['query'])  ? '?'.$req['query'] : '';
	}
	// constructor
	function HTTPRequest($url)
	{
		$this->_url = $url;
		$this->_scan_url();
	}
	// download URL to string
	function DownloadToString()
	{
		$crlf = "\r\n";
		// generate request
		$req = 'GET ' . $this->_uri . $this->_query . ' HTTP/1.0' . $crlf . 'Host: ' . $this->_host . $crlf . $crlf;
		// fetch
		$this->_fp = fsockopen($this->_host, $this->_port);
		fwrite($this->_fp, $req);
		$response = '';
		while ( (is_resource($this->_fp)) && ($this->_fp) && (!feof($this->_fp)) )
		{
			$response .= fread($this->_fp, 1024);
		}
		fclose($this->_fp);
		// split header and body
		$pos = strpos($response, $crlf . $crlf);
		if ($pos === false)
		{
			return $response;
		}
		$header = substr($response, 0, $pos);
		$body   = substr($response, $pos + 2 * strlen($crlf));
		// parse headers
		$headers = array();
		$lines = explode($crlf, $header);
		foreach($lines as $line)
		if (($pos = strpos($line, ':')) !== false)
		{
			$headers[strtolower(trim(substr($line, 0, $pos)))] = trim(substr($line, $pos + 1));
		}
		// redirection?
		if (isset($headers['location']))
		{
			$http = new HTTPRequest($this->_protocol . "://" . $this->_host . substr($this->_uri, 0, strrpos($this->_uri, "/")) . "/" . $headers['location']);
			return $http->DownloadToString();
		}
		else
		{
			return $body;
		}
	}
}
?>