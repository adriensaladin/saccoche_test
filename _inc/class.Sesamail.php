<?php
/**
 * @version $Id$
 * @author Daniel Caillibaud <daniel.caillibaud@sesamath.net>
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
 * MAILS
 * 
 * Après avoir pas mal fouillé et lu une tonne de doc, faut se passer d'accents 
 * dans les headers (sujet compris) ou bien utiliser une classe externe correcte
 * (y'en a pas 50, et ZF2/Mail ou Symfony, pour faire ça c'est un peu délirant, donc
 * on se prend un peu la tête pour faire du mail txt propre !
 * 
 * Cf pas mal d'explications en commentaires à la fin
 */

/**
 * Une classe pour envoyer des mails en texte brut correctement encodés (avec accents 
 * dans le sujet ou les noms de destinataire / expéditeur).
 * Les destinataires peuvent être des listes (tableaux ou chaînes avec la virgule en séparateur).
 * Chacun pouvant être "nom <adresse>", "<adresse>" ou "adresse".
 *
 * Pour SACoche :
 * $this->default_sender adapté
 * $this->subject_prefix ajouté
 */
class Sesamail
{
  
  /**
   * Le charset utilisé (UTF-8, exception sinon, faudra venir coder une méthode setEncoding
   * et tester de manière approfondie le jour où y'aura besoin d'autre chose)
   * @var string 
   */
  protected $charset;
  
  /**
   * Sujet (encodé $charset quoted-printable)
   * @var string 
   */
  protected $subject;
  
  /**
   * Préfixe du Sujet
   * @var string 
   */
  protected $subject_prefix;
  
  /**
   * Destinataire(s) (encodés $charset quoted-printable)
   * @var string 
   */
  protected $to;
  
  /**
   * Le message complet ($charset)
   * @var string 
   */
  protected $message;
  
  /**
   * Tous les autres headers (encodés $charset quoted-printable)
   * @var string 
   */
  protected $headers;
  
  /**
   * L'expéditeur par défaut, utilisé s'il na pas été précisé avant l'envoi
   * @var string 
   */
  protected $default_sender;

  /**
   * Constructeur pour init de $charset, $default_sender et des headers génériques Content-*
   * @throws \Exception 
   */
  function __construct() {
    // le charset
    $this->charset = strtolower( mb_internal_encoding() );
    // si on veut autre chose que de l'UTF-8 faudra coder une méthode $this->setEncoding
    // On lance une exception pour être sûr que qqun viendra mettre son nez ici
    if ($this->charset != 'utf-8') {
      throw new \Exception("Cette classe Mail ne fonctionne qu'avec un mb_internal_encoding en UTF-8 (ici on a " .$this->charset .')');
    }
    
    // le from par défaut (utilisé en Return-Path)
    $this->default_sender = WEBMESTRE_PRENOM.' '.WEBMESTRE_NOM.' <'.WEBMESTRE_COURRIEL.'>';
    
    // le préfixe du sujet
    $this->subject_prefix = 'SACoche '.HEBERGEUR_DENOMINATION.' - ';
    
    // Les headers communs à tous nos mails
    $this->headers = '';
    
    // Return-Path
    $this->headers .= 'Return-Path: ' .$this->default_sender ."\r\n"; 
    
    // Content
    $this->headers .= 'Content-type: text/plain; charset=' .$this->charset ."\r\n";
    $this->headers .= 'Content-Transfer-Encoding: 8bit'."\r\n";
  } // __construct
  
  /**
   * Une remplaçante de la fct mail classique de php (ATTENTION, pour les 3 premiers arguments seulement)
   * @param string|array $to        Le ou les destinataire(s) 
   *   Adresse seule ou bien "Nom en UTF-8 <adresse@domaine.tld>", dans un tableau ou 
   *   une chaine à virgules si plusieurs destinataires
   * @param string       $subject   L'objet du mail (UTF-8)
   * @param string       $message   Le message texte à envoyer (UTF-8)
   * @param string       $from      Facultatif, l'intitulé de l'expéditeur (ou intitulé + adresse)
   * @param string       $replyto   Facultatif, l'adresse de réponse (adresse seule ou bien "Nom en UTF-8 <adresse@domaine.tld>")
   * @param array        $args_sup  Les headers supplémentaires, sous la forme array(header_key => header_value)
   * @return boolean                Le résultat de la fct mail de php: TRUE si le mail a été accepté pour livraison, FALSE sinon.
   */
  public static function mail($to, $subject, $message, $from = '', $replyto = '', $args_sup = array()) {
    $mail = new Sesamail;
    $mail->setTo($to);
    $mail->setSubject($mail->subject_prefix.$subject);
    $mail->setMessage($message);

    // pour le from éventuel plusieurs cas
    if (!empty($from)) {
      // attention, ça peut être le nom + adresse
      if (strpos($from, '<')) {
        list($from_name,$from_addr) = $mail->extractNameAndAddress($from);
      }
      // l'adresse seule
      elseif (strpos($from, '@')) {
        $from_name = '';
        $from_addr = $from;
      }
      // le nom seul
      else {
        $from_name = str_replace(',', '', $from); // gaffe aux virgules, mb_encode_mimeheader les échappe pas
        $from_addr = '';
      }
      $mail->setFrom($from_name, $from_addr);
    }
    
    // replyto supposé complet ou adresse seule
    if (!empty($replyto)) {
      list($replyto_name, $replyto_addr) = $mail->extractNameAndAddress($replyto);
      $mail->setReplyTo($replyto_name, $replyto_addr);
    }
    
    // et les args Sup
    foreach ($args_sup as $header_key => $header_value) {
      $mail->addHeader($header_key, $header_value);
    }
    
    // reste à envoyer
    return $mail->send();
  } // mail

  /**
   * Affecte le destinataire
   * @param string|array $to Le ou les destinataire, sous la forme "nom <adresse>" ou adresse seule, 
   *   la liste des destinataires peut être sous forme de chaîne (virgule en séparateur) ou de tableau
   */
  public function setTo($to) {
    // $to peut être une adresse seule ou "nom <adresse>", ou une liste séparée par des virgules
    // On ne vérifie pas la syntaxe de l'adresse mail (long si on veut le faire correctement, 
    // cf http://www.linuxjournal.com/article/9585?page=0,3)
    
    // $to peut être une liste, en chaine
    if (strpos($to, ',')) {
      $to = explode(',', $to); // traité ensuite par le cas array
    }
    // ou en tableau
    if (is_array($to)) {
      $this->to = $this->recipientsArrayToHeader('To', $to);
    }
    // mais $to peut être un destinataire seul
    else {
      if (strpos($to, '<')) {
        list($to_name,$to_addr) = $this->extractNameAndAddress($to);
        $this->setToUniq($to_name, $to_addr);
      }
      else {
        $this->to = $to;
      }
    }
  } // setTo
  
  /**
   * Affecte un destinataire unique d'après nom & adresse
   * @param string $name Le nom à afficher, peut être vide ou NULL ou FALSE
   * @param string $address L'adresse mail
   */
  public function setToUniq($name, $address) {
    if (empty($name)) {
      $this->to = $address;
    }
    else {
      $this->to = $this->headerFormat('To', $name, $address);
    }
  } // setToUniq

  /**
   * Affecte le sujet du mail
   * @param string $subject 
   */
  public function setSubject($subject) {
    $this->subject = $this->headerFormat('Subject', $subject);
  } // setSubject
  
  /**
   * Affecte le message
   * @param string $message 
   */
  public function setMessage($message) {
    $this->message = $message;
  } // setMessage
  
  /**
   * Affecte l'expéditeur
   * @param string $name
   * @param string $address
   */
  public function setFrom($name, $address) {
    if ($address == '') {
      $address = $this->default_sender;
    }
    $this->removeHeader('From');
    $this->headers .= $this->headerFormat('From', $name, $address) ."\r\n";
  } // setFrom
  
  /**
   * Affecte le header Reply-To
   * @param string $name
   * @param string $address
   */
  public function setReplyTo($name, $address) {
    $this->removeHeader('Reply-To');
    if ($address == '') {
      $address = $this->default_sender;
    }
    $this->headers .= $this->headerFormat('Reply-To', $name, $address) ."\r\n";
  } // setReplyTo
  
  /**
   * Ajoute un header quelconque
   * @param string        $name     Le nom du header (Cc par ex)
   * @param string|array  $value    La valeur du header, peut être une liste de destinataires (tableau ou chaine avec séparateur ,)
   * @param string        $address  L'adresse mail éventuelle, si destinataire unique (non encodée, les chevrons <> seront ajoutés si besoin)
   */
  public function addHeader($header_key, $header_value, $address = '') {
    if ($header_key == 'Cc' || $header_key == 'Bcc') {
      if (is_string($header_value) && strpos($header_value, ',')) {
        $header_value = explode(',', $header_value);
      }
      if (is_array($header_value)) {
        $this->headers .= $this->recipientsArrayToHeader($header_key, $header_value) ."\r\n";
      }
      else {
        // destinataire unique
        $this->headers .= $this->headerFormat($header_key, $header_value, $address) ."\r\n";
      }
    }
    else { // autre header (pas d'adresse donc)
      $this->headers .= $this->headerFormat($header_key, $header_value) ."\r\n";
    }
  } // addHeader
  
  /**
   * Envoie le mail
   * @return boolean Le résultat de la fct mail de php : TRUE si le mail a été accepté pour livraison, FALSE sinon. 
   */
  public function send() {
    return mail($this->to, $this->subject, $this->message, $this->getHeaders());
  }
  
  /**
   * Récupère les headers du mail courant, ajoute le from s'il n'existe pas
   * @return string Les headers (séparés par des \r\n) 
   */
  protected function getHeaders() {
    // on ajoute le From s'il a pas été mis
    if (strpos($this->headers, 'From: ') === FALSE) {
      $this->headers .= 'From: ' .$this->default_sender ."\r\n";
    }
    return $this->headers;
  } // send
  
  /**
   * Formate un header d'après un tableau de destinataires (To, Cc ou Bcc)
   * @param string $header_key Le nom header (To, Cc ou Bcc)
   * @param array $recipients les destinataires, chacun sous la forme "nom <adresse>" ou "adresse"
   * @return string Le header formaté quoted-printable (et préfixé si c'est pas To)
   */
  protected function recipientsArrayToHeader($header_key, $recipients) {
    if (!is_array($recipients)) {
      throw new \Exception('Paramètre invalide');
    }
    $header = ''; // la string du header
    foreach($recipients as $recipient) {
      // on peut avoir un fragment de nom s'il contenait une virgule, on ignore simplement
      if (strpos($recipient, '@')) {
        list($name, $address) = $this->extractNameAndAddress($recipient);
        if ($header == '') {
          // c'est le 1er
          $header = $this->headerFormat($header_key, $name, $address);
        }
        else {
          // on cherche pas à savoir si ça peut rentrer sur la dernière ligne, on retourne au début
          // on met 'To' en $header car on doit décaler de 2 char et la méthode le virera pour nous
          // (si on avait mis xx il aurait fallu virer les "xx: " du début du retour
          $header .= ",\n " .$this->headerFormat('To', $name, $address);
        }
      } 
      else {
        // portion de string sans adresse, serait envoyé en ajoutant @hostanme, on ignore mais on loggue
        trigger_error("destinataire sans adresse ($recipient), probablement une virgule dans un nom, ignoré");
      }
    }
    // On se préoccupe pas du préfixe, headerFormat l'a fait au 1er passage
    return $header;
  } // recipientsArrayToHeader
  
  /**
   * Sépare adresse mail et nom d'une chaine de la forme "nom <adresse>"
   * @param string $string
   * @param array  [$name,$address]
   */
  protected function extractNameAndAddress($string) {
    $start = strpos($string, '<');
    if ($start === FALSE) {
      $name = '';
      $addr = $string;
    } else {
      $name = trim(str_replace(',', '', substr($string, 0, $start)));
      $addr = substr($string, $start, strpos($string, '>'));
    }
    return array($name,$addr) ;
  } // extractNameAndAddress
  
  /**
   * Encode un header et retourne la chaîne correspondante préfixée (sauf To et Subject)
   *
   * La fonction mb_encode_mimeheader() revoie une chaine contenant "UTF-8" qui pose problèmes à certains lecteurs de messageries qui attendent "utf-8".
   * Et lui donner strtolower($this->charset) comme paramètre n'y change rien, d'où le traitement a posteriori avec str_replace().
   *
   * @param string $header Le nom du header (To, Subject, From, Reply-To, etc.)
   * @param string $str La valeur du header (la chaîne à encoder comme le nom d'un destinataire ou le sujet)
   * @param string $adress L'éventuelle adresse mail
   * @return string Le header formaté et préfixé (sans \r\n de fin)
   */
  protected function headerFormat($header, $str, $address = '') {
    // On ajoute toujours le header en préfixe, et on le virera après si To ou Subject
    // pour que la césure soit à la bonne place
    if ($str != '') {
      $enc_str = str_replace( mb_internal_encoding() , strtolower(mb_internal_encoding()) , mb_encode_mimeheader($header .': ' .$str, $this->charset, "Q") );
    }
    else { // si on laisse mb_encode_mimeheader il vire l'espace de fin
      $enc_str = $header .': ';
    }
    // La chaine est correctement encodée et préfixée, on regarde l'adresse mail
    if ($address != '') {
      if (strpos($address, '<') === FALSE && !empty($str)) {
        // On ajoute les chevrons
        $address = ' <' .$address .'>';
      }
      // et on concatène tout ça, mais faut regarder si on a la place sur la dernière ligne
      $lines = explode("\n", $enc_str);
      $reste = 74 - strlen(array_pop($lines));
      // ça rentre ?
      if ($reste < strlen($address)) {
        // pas la place, faut sauter une ligne et ajouter une espace
        $enc_str .= "\n ";
      }
      $enc_str .= $address;
    }
    // Faut virer le nom du header pour ces deux là car la fct mail les veut pas
    if ($header == 'To' || $header == 'Subject') { 
      $enc_str = substr($enc_str, strlen($header) +2);
    }
    return $enc_str;
  } // headerFormat
  // function headerFormatOld dans la rev129
  
  /**
   * Vire un header s'il le trouve
   * @param string $header_key Le nom du header
   */
  protected function removeHeader($header_key) {
    $start = strpos($this->headers, $header_key .': ');
    if ($start !== FALSE) {
      // y'en avait un, on le vire
      $end = strpos($this->headers, "\r\n", $start);
      $this->headers = substr($this->headers, 0, $start) .substr($this->headers, $end+2);
    }
  }
} // removeHeader


/**
 * Pourquoi tout ça...
 * 
 * Pour les accents dans les entêtes de mails (sujet, expéditeur...) ; 
 * le charset n'a d'effet que sur le corps et les clients de messagerie interprètent 
 * différemment le reste (UTF-8 ou ISO-8859-1 etc.).
 * 
 * Résultat des courses (plus de détails dans la rev 129 : http://redmine.sesamath.net/projects/commun/repository/revisions/129/entry/Sesamath/Mail.class.php#L255)
 * - mb_send_mail est une cata car 
 *   - encode en base64 => spam d'office 
 *   - encode pas le To ni les headers sup (From)
 * - iconv_mime_encode pourrait être mieux pour les headers sans adresse mail (sinon il encode le @), 
 *   donc le sujet, mais pas de bol il veut le nom du header pour l'ajouter, et pour le sujet on veut 
 *   pas du "Subject: " de début pour le filer à la fct mail. Par ailleurs il ajoute des =?UTF-8?Q?
 *   au mileu des lignes (en plus du début, curieux...)
 * - quoted_printable_encode coupe les lignes mais il met pas le =?UTF-8?Q? au début, du coup 
 *   faut recoller pour redécouper autrement
 * - mb_encode_mimeheader est finalement le plus simple
 * 
 * => on utilise mb_encode_mimeheader, mais pas sur la partie adresse mail, donc faudra gérer 
 * soi-même le wrapping (et virer le : au début quand on lui donne une chaine vide comme nom 
 * de header pour To et Subject).
 * 
 * \r\n dans les headers ?
 * Les rfc disent que les headers se terminent par \r\n (et une ligne de header trop longue est coupée par "\n  ")
 * mais les client mails microsoft le digèrent pas toujours très bien 
 *   http://stackoverflow.com/questions/4415654/which-line-break-in-php-mail-header-r-n-or-n
 *   http://stackoverflow.com/questions/3449431/php-mail-formatting-issue-why-do-crlf-header-line-endings-break-html-email-in/7960957#7960957
 * et apparemment, mettre \n ne pose pas trop de pb aux autres (un comble, vu que c'est MS qui utilise \r\n comme fin
 * de ligne dans les txt alors que unix utilise \n depuis que les fin de lignes existent)
 * Pourtant, http://swiftmailer.org/ utilise \r\n (cf AbstractHeader.php)
 */