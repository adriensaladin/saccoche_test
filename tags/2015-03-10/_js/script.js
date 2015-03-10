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

// Pour éviter une soumission d'un formulaire en double :
// + lors de l'appui sur "entrée" (constaté avec Chrome, malgré l'usage de la biblio jquery.form.js, avant l'utilisation complémentaire de "disabled")
// + lors d'un clic sur une image "q", même si elles sont normalement masquées...
var please_wait = false;

/**
 * Fonction htmlspecialchars() en javascript
 *
 * @param unsafe
 * @return string
 */
function escapeHtml(unsafe)
{
  return unsafe
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

/**
 * Fonction réciproque de htmlspecialchars() en javascript
 *
 * @param unsafe
 * @return string
 */
function unescapeHtml(safe)
{
  return safe
    .replace(/&amp;/g , "&")
    .replace(/&lt;/g  , "<")
    .replace(/&gt;/g  , ">")
    .replace(/&quot;/g, "\"")
    .replace(/&#039;/g, "'");
}

/**
 * Fonction htmlspecialchars() en javascript mais juste pour les apostrophes doubles.
 *
 * @param unsafe
 * @return string
 */
function escapeQuote(unsafe)
{
  return unsafe.replace(/"/g, "&quot;");
}

/**
 * Fonction replaceAll() pour remplacer une chaine par une autre à chaque occurence.
 * @see http://stackoverflow.com/questions/1144783/replacing-all-occurrences-of-a-string-in-javascript
 * @see http://javascript.developpez.com/sources/?page=tips#replaceall
 *
 * @param find
 * @param replace
 * @param str
 * @return string
 */
function replaceAll(find, replace, str)
{
  return str.replace(new RegExp(find, 'g'), replace);
}

/**
 * Ajout de la méthode trim() pour les navigateurs embarquant un javascript de version < 1.8.1
 * @see https://developer.mozilla.org/fr/docs/Web/JavaScript/Reference/Objets_globaux/String/Trim
 * @see http://www.w3schools.com/jsref/jsref_trim_string.asp
 *
 * @param string
 * @return string
 */
if(!String.prototype.trim)
{
  String.prototype.trim = function()
  {
    return this.replace(/^\s+|\s+$/gm,'');
  };
}

/**
 * Fonction pour extraire le hash (sans le dièse) d'une URL
 * Mise en place car un substring() ne passe pas si 
 * session.use_trans_sid = ON et session.use_only_cookies = OFF
 * car alors PHP rajoute ?SACoche-session= dans les liens
 *
 * @param href
 * @return string
 */
function extract_hash(href)
{
  var pos_hash = href.lastIndexOf('#');
  return (pos_hash!==-1) ? href.substr(pos_hash+1) : '' ;
}

/**
 * Fonction pour envoyer un message vers la console javascript
 *
 * @param type  log | info | warn | error | table | time | timeEnd | group | dir | assert | trace
 * @param msg   le contenu du message
 * @return string
 */
function log(type,msg)
{
  try
  {
         if(type=='log')     { console.log(msg);     }
    else if(type=='info')    { console.info(msg);    }
    else if(type=='warn')    { console.warn(msg);    }
    else if(type=='error')   { console.error(msg);   }
    else if(type=='table')   { console.table(msg);   }
    else if(type=='time')    { console.time(msg);    }
    else if(type=='timeEnd') { console.timeEnd(msg); }
    else if(type=='group')   { console.group(msg);   }
    else if(type=='dir')     { console.dir(msg);     }
    else if(type=='assert')  { console.assert(msg);  }
    else if(type=='trace')   { console.trace(msg);   }
  }
  catch (e)
  {}
}

/**
 * Fonction pour interpréter une erreur d'extraction json
 *
 * @param jqXHR      l'objet retourné par ajax, contenant la réponse du serveur
 * @param textStatus le statut de l'analyse json
 * @return string
 */
function afficher_json_message_erreur(jqXHR, textStatus)
{
  // Une erreur de syntaxe lors de l'analyse du json : probablement une erreur ou un avertissement PHP, éventuellement suivi de la chaine json retournée
  if(textStatus=='parsererror')
  {
    var pos_debut_json = jqXHR['responseText'].indexOf('{"');
    var chaine_anormale = (pos_debut_json>0) ? jqXHR['responseText'].substr(0,pos_debut_json) : jqXHR['responseText'] ;
    return 'Anomalie rencontrée ! ' + chaine_anormale;
  }
  // Rien de retourné : probablement un souci de connexion au serveur
  else if( (textStatus=='error') && (typeof(jqXHR['responseText'])=='undefined') )
  {
    return 'Échec de la connexion au serveur !';
  }
  // 404 ou autre...
  else
  {
    return 'Erreur inattendue ! ' + jqXHR['responseText'];
  }
}

/**
 * Fonction pour afficher / masquer les images cliquables (en général dans la dernière colonne du tableau)
 *
 * Remarque : un toogle ne peut être simplement mis en oeuvre à cause des nouvelle images créées...
 *
 * @param why valeur parmi [show] [hide]
 * @return void
 */
function afficher_masquer_images_action(why)
{
  if(why=='show')
  {
    $('form q').show();
  }
  else if(why=='hide')
  {
    $('form q').hide();
  }
}

/**
 * Fonction pour appliquer une infobulle au survol de tous les éléments possédants un attribut "title"
 *
 * Remarque : attention, cela fait disparaitre le contenu de l'attribut alt"...
 *
 * @param void
 * @return void
 */
function infobulle()
{
  $(document).tooltip
  (
    {
      track: true,
      position: { my: "left+15 top+15", collision: "flipfit" },
      content: function()
      {
        if( ($(this).hasClass('fancybox-nav')) || ($(this).hasClass('fancybox-item')) )
        {
          $(this).removeAttr('title');
          return false;
        }
        return '<b>'+$(this).attr("title")+'</b>'; // Cette ligne permet aussi la prise en compte des <br />... pas vraiment compris pourquoi mais bon...
      }
    }
  );
}

/**
 * Fonction pour un tester la robustesse d'un mot de passe.
 *
 * @param mdp
 * @return void
 */
function analyse_mdp(mdp)
{
  mdp.replace(/^\s+/g,'').replace(/\s+$/g,'');  // équivalent de trim() en javascript
  mdp = mdp.substring(0,20);
  var nb_min = 0;
  var nb_maj = 0;
  var nb_num = 0;
  var nb_spe = 0;
  var longueur = mdp.length;
  for (i=0 ; i<longueur ; i++)
  {
    var car = mdp.charAt(i);
         if((/[a-z]/).test(car)) {nb_min++;}  // 2 points maxi pour des minuscules
    else if((/[A-Z]/).test(car)) {nb_maj++;}  // 2 points maxi pour des majuscules
    else if((/[0-9]/).test(car)) {nb_num++;}  // 2 points maxi pour des chiffres
    else                         {nb_spe++;}  // 6 points maxi pour des caractères autres
  }
  var coef = Math.min(nb_min,2) + Math.min(nb_maj,2) + Math.min(nb_num,2) + Math.min(nb_spe*2,6) ;
  if(longueur>7)
  {
    coef += Math.floor( (longueur-5)/3 );  // 6 points maxi pour la longueur du mdp
  }
  coef = Math.min(coef,12);  // total 18 points maxi, plafonné à 12
  var rouge = 255 - 16*Math.max(0,coef-6) ; // 255 -> 255 -> 159
  var vert  = 159 + 16*Math.min(6,coef) ;   // 159 -> 255 -> 255
  var bleu  = 159 ;
  $('#robustesse').css('background-color','rgb('+rouge+','+vert+','+bleu+')').children('span').html(coef);
}

/**
 * Fonction pour imprimer un contenu
 *
 * En javascript, print() s'applique à l'objet window, et l'usage d'une feuille de style adaptée n'a pas permis d'obtenir un résultat satisfaisant.
 * D'où l'ouverture d'un pop-up (inspiration : http://www.asp-php.net/ressources/bouts_de_code.aspx?id=342).
 *
 * @param object contenu
 * @return void
 */
function imprimer(contenu)
{
  var wp = window.open("","SACochePrint","toolbar=no,location=no,menubar=no,directories=no,status=no,scrollbars=no,resizable=no,copyhistory=no,width=1,height=1,top=0,left=0");
  wp.document.write('<!DOCTYPE html><html><head><link rel="stylesheet" type="text/css" href="./_css/style.css" /><title>SACoche - Impression</title></head><body onload="window.print();window.close()">'+document.getElementById('top_info').innerHTML+contenu+'</body></html>');
  wp.document.close();
}

/**
 * Fonction pour afficher et cocher une liste d'items donnés
 *
 * @param string matieres_items_liste : ids séparés par des underscores
 * @return void
 */
function cocher_matieres_items(matieres_items_liste)
{
  // Replier tout sauf le plus haut niveau
  $('#zone_matieres_items ul').css("display","none");
  $('#zone_matieres_items ul.ul_m1').css("display","block");
  // Décocher tout
  $("#zone_matieres_items input[type=checkbox]").each
  (
    function()
    {
      this.checked = false;
    }
  );
  // Cocher ce qui doit l'être (initialisation)
  if(matieres_items_liste.length)
  {
    var tab_id = matieres_items_liste.split('_');
    for(i in tab_id)
    {
      id = 'id_'+tab_id[i];
      if($('#'+id).length)
      {
        $('#'+id).prop('checked',true);
        $('#'+id).closest('ul.ul_n3').css("display","block");  // les items
        $('#'+id).closest('ul.ul_n2').css("display","block");  // le thème
        $('#'+id).closest('ul.ul_n1').css("display","block");  // le domaine
        $('#'+id).closest('ul.ul_m2').css("display","block");  // le niveau
      }
    }
  }
}

/**
 * Fonction pour mémoriser une liste d'items donnés
 *
 * @param string selection_items_nom
 * @return void
 */
function memoriser_selection_matieres_items(selection_items_nom)
{
  if(!selection_items_nom)
  {
    $('#ajax_msg_memo').removeAttr("class").addClass("erreur").html("nom manquant");
    $("#f_liste_items_nom").focus();
    return false;
  }
  var compet_liste = '';
  $("#zone_matieres_items input[type=checkbox]:checked").each
  (
    function()
    {
      compet_liste += $(this).val()+'_';
    }
  );
  if(!compet_liste)
  {
    $('#ajax_msg_memo').removeAttr("class").addClass("erreur").html("Aucun item coché !");
    return false;
  }
  var compet_liste  = compet_liste.substring(0,compet_liste.length-1);
  $('#ajax_msg_memo').removeAttr("class").addClass("loader").html("En cours&hellip;");
  $.ajax
  (
    {
      type : 'POST',
      url : 'ajax.php?page=compte_selection_items',
      data : 'f_action='+'ajouter'+'&f_origine='+PAGE+'&f_compet_liste='+compet_liste+'&f_nom='+encodeURIComponent(selection_items_nom),
      dataType : "html",
      error : function(jqXHR, textStatus, errorThrown)
      {
        $('#ajax_msg_memo').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
      },
      success : function(responseHTML)
      {
        initialiser_compteur();
        if(responseHTML.substring(0,7)=='<option')  // Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
        {
          $('#ajax_msg_memo').removeAttr("class").addClass("valide").html("Sélection mémorisée.");
          $("#f_selection_items option:disabled").remove();
          $("#f_selection_items").append(responseHTML);
        }
        else
        {
          $('#ajax_msg_memo').removeAttr("class").addClass("alerte").html(responseHTML);
          $("#f_liste_items_nom").focus();
        }
      }
    }
  );
}

/**
 * Fonction pour afficher et cocher un item du socle
 *
 * @param socle_item_id
 * @return void
 */
function cocher_socle_item(socle_item_id)
{
  // Replier tout sauf le plus haut niveau la 1e fois ; ensuite on laisse aussi volontairement ouvert ce qui a pu l'être précédemment
  if(cocher_socle_item_first_appel)
  {
    $('#zone_socle_item ul').css("display","none");
    $('#zone_socle_item ul.ul_m1').css("display","block");
    cocher_socle_item_first_appel = false;
  }
  $('#zone_socle_item ul.ul_n1').css("display","block"); // zone "Hors socle" éventuelle
  // Décocher tout
  $("#zone_socle_item input[type=radio]").each
  (
    function()
    {
      this.checked = false;
    }
  );
  // Cocher ce qui doit l'être (initialisation)
  if(socle_item_id!='0')
  {
    if($('#socle_'+socle_item_id).length)
    {
      $('#socle_'+socle_item_id).prop('checked',true);
      $('#socle_'+socle_item_id).closest('ul.ul_n3').css("display","block");  // les items
      $('#socle_'+socle_item_id).closest('ul.ul_n2').css("display","block");  // la section
      $('#socle_'+socle_item_id).closest('ul.ul_n1').css("display","block");  // le pilier
    }
  }
  else
  {
    $('#socle_0').prop('checked',true);
  }
  $('#socle_'+socle_item_id).focus();
}

var cocher_socle_item_first_appel = true;

/**
 * Fonction pour afficher et cocher une liste d'élèves donnés
 *
 * @param prof_liste : ids séparés par des underscores
 * @return void
 */
function cocher_eleves(eleve_liste)
{
  // Replier les classes
    $('#zone_eleve ul').css("display","none");
    $('#zone_eleve ul.ul_m1').css("display","block");
  // Décocher tout
  $("#zone_eleve input[type=checkbox]").each
  (
    function()
    {
      this.checked = false;
      $(this).next('label').removeAttr('class').next('span').html(''); // retrait des indications éventuelles d'élèves associés à une évaluation de même nom
    }
  );
  // Cocher ce qui doit l'être (initialisation)
  if(eleve_liste.length)
  {
    var tab_id = eleve_liste.split('_');
    for(i in tab_id)
    {
      var id_debut = 'id_'+tab_id[i]+'_';
      if($('input[id^='+id_debut+']').length)
      {
        $('input[id^='+id_debut+']').prop('checked',true);
        $('input[id^='+id_debut+']').parent().parent().css("display","block");  // le regroupement
      }
    }
  }
}

/**
 * Fonction pour cocher une liste de matières données
 *
 * @param matiere_liste : ids séparés par des virgules
 * @return void
 */
function cocher_matieres(matiere_liste)
{
  // Décocher tout
  $("#zone_matieres input[type=checkbox]").each
  (
    function()
    {
      this.checked = false;
    }
  );
  // Cocher des cases des matières
  if(matiere_liste.length)
  {
    var tab_id = matiere_liste.split('_');
    for(i in tab_id)
    {
      var id = 'm_'+tab_id[i];
      if($('#'+id).length)
      {
        $('#'+id).prop('checked',true);
      }
    }
  }
}

/**
 * Fonction pour cocher une liste de profs donnés
 *
 * @param prof_liste : ids séparés par des underscores
 * @return void
 */
function cocher_profs(prof_liste)
{
  // Décocher tout
  $("#zone_profs input[type=checkbox]").each
  (
    function()
    {
      if(this.disabled == false)
      {
        this.checked = false;
      }
    }
  );
  // Cocher des cases des profs
  if(prof_liste.length)
  {
    var tab_id = prof_liste.split('_');
    for(i in tab_id)
    {
      var id = 'p_'+tab_id[i];
      if($('#'+id).length)
      {
        $('#'+id).prop('checked',true);
      }
    }
  }
}

/**
 * Fonction pour selectionner une option pour une liste de profs donnés
 *
 * @param prof_liste : { lettre de l'option concaténée avec l'id du prof } séparés par des underscores
 * @return void
 */
function selectionner_profs_option(prof_liste)
{
  // Sélectionner l'option par défaut pour tous les profs
  $('#zone_profs').find('select').find('option[value=x]').prop('selected',true);
  $('.prof_liste').find('span.select_img').removeAttr('class').addClass('select_img droit_x');
  // Décocher les boutons pour reporter une valeur à tous
  $('#zone_profs').find('input[type=radio]').prop('checked',false);
  // Modifier les sélections des profs concernés
  if(prof_liste.length)
  {
    var tab_val = prof_liste.split('_');
    for(i in tab_val)
    {
      var val_option = tab_val[i].substring(0,1);
      var id_prof    = tab_val[i].substring(1);
      var id_select  = 'p'+'_'+id_prof;
      if($('#'+id_select).length)
      {
        $('#'+id_select+' option[value='+val_option+']').prop('selected',true);
        $('#'+id_select).next('span').removeAttr('class').addClass('select_img droit_'+val_option);
      }
    }
  }
}

/**
 * Fonction pour afficher le nombre de caractères restants autorisés dans un textarea.
 * A appeler avec l'événement onkeyup.
 *
 * Inspiration : http://www.paperblog.fr/349086/limiter-le-nombre-de-caractere-d-un-textarea/
 * Plugin jQuery possible : http://www.devzone.fr/plugin-jquery-maxlength-nombre-de-caracteres-restants
 *
 * @param textarea_obj
 * @param textarea_maxi_length
 * @return void
 */
function afficher_textarea_reste(textarea_obj,textarea_maxi_length)
{
  var textarea_contenu = textarea_obj.val();
  var textarea_longueur = textarea_contenu.length;
  if(textarea_longueur > textarea_maxi_length)
  {
    textarea_obj.val( textarea_contenu.substring(0,textarea_maxi_length) );
    textarea_longueur = textarea_maxi_length;
  }
  var reste_nb    = textarea_maxi_length - textarea_longueur;
  var reste_str   = (reste_nb>1) ? ' caractères restants' : ' caractère restant' ;
  var reste_class = (reste_nb>9) ? 'valide' : 'alerte' ;
  $('#'+textarea_obj.attr('id')+'_reste').html(reste_nb+reste_str).removeAttr("class").addClass(reste_class);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Gestion de la durée d'inactivité
// On utilise un cookie plutôt qu'une variable js car ceci permet de gérer plusieurs onglets.
// ////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Fonction pour écrire un cookie
 *
 * @param name   nom du cookie
 * @param value  valeur du cookie
 * @return void
 */
function SetCookie(name,value)
{
  var argv = SetCookie.arguments;
  var argc = SetCookie.arguments.length;
  var expires = (argc > 2) ? argv[2] : null ;
  var path    = (argc > 3) ? argv[3] : null ;
  var domain  = (argc > 4) ? argv[4] : null ;
  var secure  = (argc > 5) ? argv[5] : false ;
  document.cookie = name + "=" + escape(value) +
                    ((expires==null) ? "" : ("; expires="+expires.toGMTString())) +
                    ((path==null) ? "" : ("; path="+path)) +
                    ((domain==null) ? "" : ("; domain="+domain)) +
                    ((secure==true) ? "; secure" : "") ;
}

/**
 * Fonction pour lire un cookie
 *
 * @param name   nom du cookie
 * @return string
 */
function GetCookie(name)
{
  var arg  = name + "=";
  var alen = arg.length;
  var clen = document.cookie.length;
  var i = 0;
  while(i<clen)
  {
    var j = i+alen;
    if(document.cookie.substring(i,j)==arg)
    {
      return getCookieVal(j);
    }
    i = document.cookie.indexOf(" ",i)+1;
    if(i==0)
    {
      break;
    }
  }
  return null;
}
function getCookieVal(offset)
{
  var endstr = document.cookie.indexOf(";", offset);
  if (endstr==-1)
  {
    endstr = document.cookie.length;
  }
  return unescape(document.cookie.substring(offset, endstr));
}

/**
 * Fonction pour remettre le compteur au maximum (cookie + affichage)
 *
 * @param void
 * @return void
 */
function initialiser_compteur()
{
  var date = new Date();
  SetCookie('SACoche-compteur',date.getTime());
  DUREE_AFFICHEE = DUREE_AUTORISEE;
  $("#clock").html(DUREE_AFFICHEE+' min').parent().removeAttr("class").addClass("top clock_fixe");
}

/**
 * Fonction pour modifier l'état du compteur, et déconnecter si besoin
 *
 * @param void
 * @return void
 */
function tester_compteur()
{
  var date  = new Date();
  var now   = date.getTime();
  var avant = GetCookie('SACoche-compteur');
  var duree_ecoulee  = Math.floor((now-avant)/60/1000);
  var duree_restante = DUREE_AUTORISEE-duree_ecoulee;
  if(duree_restante!=DUREE_AFFICHEE)
  {
    DUREE_AFFICHEE = Math.max(duree_restante,0);
    if(DUREE_AFFICHEE>5)
    {
      $("#clock").html(DUREE_AFFICHEE+' min').parent().removeAttr("class").addClass("top clock_fixe");
      if(DUREE_AFFICHEE%10==0)
      {
        // Fonction conserver_session_active() à appeler une fois toutes les 10min ; code placé ici pour éviter un appel après déconnection, et l'application inutile d'un 2nd compteur
        conserver_session_active();
      }
    }
    else
    {
      if(window.HTMLAudioElement) // Éviter une erreur si balise audio HTML5 non supportée
      {
        $('#audio_bip').get(0).play(); // Fonctionne sauf avec IE<9 et Safari sous Windows si Quicktime n'est pas installé.
      }
      $("#clock").html(DUREE_AFFICHEE+' min').parent().removeAttr("class").addClass("top clock_anim");
      if(DUREE_AFFICHEE==0)
      {
        fermer_session_en_ajax('inactivite');
      }
    }
  }
}

/**
 * Fonction pour ne pas perdre la session : appel au serveur toutes les 10 minutes (en ajax)
 *
 * @param void
 * @return void
 */
function conserver_session_active()
{
  $.ajax
  (
    {
      type : 'GET',
      url : 'ajax.php?page=conserver_session_active',
      data : '',
      dataType : "html",
      error : function(jqXHR, textStatus, errorThrown)
      {
        $('div.jqibox').remove(); // Sinon il y a un pb d'affichage lors d'appels successifs
        $.prompt(
          "Échec lors de la connexion au serveur !<br />Le travail en cours pourrait ne pas pouvoir être sauvegardé...",
          {
            title  : 'Avertissement'
          }
        );
      },
      success : function(responseHTML)
      {
        if(responseHTML != 'ok')
        {
          $('div.jqibox').remove(); // Sinon il y a un pb d'affichage lors d'appels successifs
          $.prompt(
            responseHTML ,
            {
              title: 'Anomalie'
            }
          );
        }
      }
    }
  );
}

/**
 * Fonction pour fermer la session : appel si le compteur arrive à zéro (en ajax)
 *
 * @param string motif   inactivite | redirection
 * @return bool
 */
function fermer_session_en_ajax(motif)
{
  $.ajax
  (
    {
      type : 'GET',
      url : 'ajax.php?page=fermer_session',
      data : '',
      dataType : "html",
      error : function(jqXHR, textStatus, errorThrown)
      {
        return false;
      },
      success : function(responseHTML)
      {
        if(responseHTML != 'ok')
        {
          return false;
        }
        if(motif=='redirection')
        {
          window.document.location.href = DECONNEXION_REDIR ;
        }
        if(motif=='inactivite')
        {
          $("body").stopTime('compteur');
          $('#menu').remove();
          if(CONNEXION_USED=='normal')
          {
            var adresse = ( (PROFIL_TYPE!='webmestre') && (PROFIL_TYPE!='partenaire') ) ? './index.php' : './index.php?'+PROFIL_TYPE ;
            $('#top_info').html('<div><span class="top expiration">Votre session a expiré. Vous êtes désormais déconnecté de SACoche !</span><br /><span class="top connexion"><a href="'+adresse+'">Se reconnecter&hellip;</a></span></div>');
          }
          else
          {
            $('#top_info').html('<div><span class="top expiration">Session expirée. Vous êtes déconnecté de SACoche mais sans doute pas du SSO !</span><br /><span class="top connexion"><a href="#" onclick="document.location.reload()">Recharger la page&hellip;</a></span></div>');
          }
          $.fancybox( '<div class="danger">Délai de '+DUREE_AUTORISEE+'min sans activité atteint &rarr; session fermée.<br />Toute action ultérieure ne sera pas enregistrée.</div>' , {'centerOnScroll':true} );
        }
      }
    }
  );
}

/**
 * Fonction pour tester une URL : extrait du plugin jQuery Validation
 *
 * @param string
 * @return bool
 */
function testURL(lien)
{
  return /^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)*(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(lien);
}

/**
 * Fonction pour tester une adresse mail : extrait du plugin jQuery Validation
 *
 * @param string
 * @return bool
 */
function testMail(adresse)
{
  return /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))$/i.test(adresse);
}

/**
 * Ajout de méthodes pour jquery.validate.js
 */

// Méthode pour vérifier le format du numéro UAI
function test_uai_format(value)
{
  var uai = value.toUpperCase();
  if(uai.length!=8)
  {
    return false;
  }
  else
  {
    var uai_fin = uai.substring(7,8);
    if((uai_fin<"A")||(uai_fin>"Z"))
    {
      return false;
    }
    else
    {
      for(i=0;i<7;i++)
      {
        var t = uai.substring(i,i+1);
        if((t<"0")||(t>"9"))
        {
          return false;
        }
      }
    }
  }
  return true;
}
jQuery.validator.addMethod
(
  "uai_format", function(value, element)
  {
    return this.optional(element) || test_uai_format(value) ;
  }
  , "il faut 7 chiffres suivis d'une lettre"
); 

// Méthode pour vérifier la clef de contrôle du numéro UAI
function test_uai_clef(value)
{
  var uai = value.toUpperCase();
  var uai_valide = true;
  var uai_nombre = uai.substring(0,7);
  var uai_fin = uai.substring(7,8);
  alphabet = "ABCDEFGHJKLMNPRSTUVWXYZ";
  reste = uai_nombre-(23*Math.floor(uai_nombre/23));
  clef = alphabet.substring(reste,reste+1);;
  return (clef==uai_fin) ? true : false ;
}
jQuery.validator.addMethod
(
  "uai_clef", function(value, element)
  {
    return this.optional(element) || test_uai_clef(value) ;
  }
  , "clef de contrôle incompatible"
); 

// Méthode pour valider les dates de la forme jj/mm/aaaa (trouvé dans le zip du plugin, corrige en plus un bug avec Safari)
function test_dateITA(value)
{
  var re = /^\d{1,2}\/\d{1,2}\/\d{4}$/ ;
  if( re.test(value))
  {
    var adata = value.split('/');
    var gg = parseInt(adata[0],10);
    var mm = parseInt(adata[1],10);
    var aaaa = parseInt(adata[2],10);
    var xdata = new Date(aaaa,mm-1,gg);
    if ( ( xdata.getFullYear() == aaaa ) && ( xdata.getMonth () == mm - 1 ) && ( xdata.getDate() == gg ) )
      return true;
    else
      return false;
  }
  else
    return false;
}
jQuery.validator.addMethod
(
  "dateITA",
  function(value, element)
  {
    return this.optional(element) || test_dateITA(value);
  }, 
  "date JJ/MM/AAAA incorrecte"
);

// Ajout d'une méthode pour vérifier le format hexadécimal
jQuery.validator.addMethod
(
  "hexa_format", function(value, element)
  {
    return this.optional(element) || ( (/^\#[0-9a-f]{3,6}$/i.test(value)) && (value.length!=5) && (value.length!=6) ) ;
  }
  , "format incorrect"
); 

/**
 * Ajout d'une méthode pour tester la présence d'un mot
 */
jQuery.validator.addMethod
(
  "isWord", function(value, element, param)
  {
    return this.optional(element) || (value.match(new RegExp(param))) ;
  }
  , "élément manquant"
); 

/**
 * Ajout d'une méthode pour tester la syntaxe d'un domaine
 */
function test_domaine(domaine)
{
  return /^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,6}$/i.test(domaine);
}
jQuery.validator.addMethod
(
  "domaine", function(value, element)
  {
    return this.optional(element) || test_domaine(value) ;
  }
  , "élément manquant"
); 

/**
 * Ajout d'une alerte dans le DOM sans jQuery.
 * Utilisé par les deux tests qui suivent cette fonction.
 */
function ajout_alerte(texte)
{
  // Contenu
  var paragraphe = document.createElement('div');
  paragraphe.setAttribute('class', 'probleme');
  paragraphe.innerHTML = texte;
  // Emplacement
  var endroit = false;
  if( document.getElementById('titre_logo') !== null )
  {
    endroit = document.getElementById('titre_logo');
  }
  else if( document.getElementsByTagName('h1').length )
  {
    endroit = document.getElementsByTagName('h1').item(0);
  }
  // Insertion
  if(endroit)
  {
    // Il n'existe pas de méthode insertAfter pour insérer un nœud après un autre, cependant on peut l'émuler avec une combinaison de insertBefore et nextSibling.
    // @see https://developer.mozilla.org/fr/docs/DOM/element.insertBefore
    endroit.parentNode.insertBefore( paragraphe , endroit.nextSibling );
  }
}

/**
 * Alerte si usage frame / iframe
 * Écrit sans nécessiter jQuery car l'ENT d'Itop fait planter la bibliothèque sous IE (SACoche mis dans un iframe lui-même imbriqué récursivement dans 4 tableaux et 5 div, avec des scripts en pagaille).
 */
if(top.frames.length!=0)
{
  ajout_alerte('L\'usage de cadres (frame/iframe) pour afficher <em>SACoche</em> est inapproprié et peut entrainer des dysfonctionnements.<br /><a href="'+location.href+'" target="_blank">Ouvrir <em>SACoche</em> dans un nouvel onglet.</a>');
}

/**
 * Alerte si non acceptation des cookies
 * Peut se tester directement en javascript (éxécuté par le client) alors qu'en PHP il faut recharger une page (info envoyée au serveur dans les en-têtes)
 */
if(typeof(navigator.cookieEnabled)!="undefined")
{
  var accepteCookies = (navigator.cookieEnabled) ? true : false ;
}
else
{
  document.cookie = "test";
  var accepteCookies = (document.cookie.indexOf("test") != -1) ? true : false ;
}
if(!accepteCookies)
{
  ajout_alerte('Pour utiliser <em>SACoche</em> vous devez configurer l\'acceptation des cookies par votre navigateur.');
}

/**
 * jQuery !
 */
$(document).ready
(
  function()
  {

    /**
     * Initialisation
     */
    infobulle();

    /**
     * Clic sur une image-lien afin d'afficher ou de masquer le détail d'une synthese ou d'un relevé socle
     */
    $(document).on
    (
      'click',
      'a[href=#toggle]',
      function()
      {
        var id   = $(this).attr('id').substring(3); // 'to_' + id
        var class_old = $(this).attr('class');
        var class_new = (class_old=='toggle_plus') ? 'toggle_moins' : 'toggle_plus' ;
        $(this).removeAttr("class").addClass(class_new);
        $('#'+id).toggle('fast');
        return false;
      }
    );

    /**
     * Clic sur un lien pour ouvrir une fenêtre d'aide en ligne (pop-up)
     */
    $(document).on
    (
      'click',
      'a.pop_up',
      function()
      {
        adresse = $(this).attr("href");
        // Fenêtre principale ; si ce n'est pas le pop-up, on la redimensionne / repositionne
        if(window.name!='popup')
        {
          var largeur = Math.max( 1000 , screen.width - 600 );
          var hauteur = screen.height * 1 ;
          var gauche = 0 ;
          var haut   = 0 ;
          window.moveTo(gauche,haut);
          window.resizeTo(largeur,hauteur);
        }
        // Fenêtre pop-up
        var largeur = 600 ;
        var hauteur = screen.height * 1 ;
        var gauche = screen.width - largeur ;
        var haut   = 0 ;
        w = window.open( adresse , 'popup' ,"toolbar=no,location=no,menubar=no,directories=no,status=no,scrollbars=yes,resizable=yes,copyhistory=no,width="+largeur+",height="+hauteur+",top="+haut+",left="+gauche ) ;
        w.focus() ;
        return false;
      }
    );

// ////////////////////////////////////////////////////////////////////////////////
// La suite n'est à exécuter que si l'on est connecté.
// Remarque : poursuivre l'analyse en l'état provoquerait des erreurs.
// ////////////////////////////////////////////////////////////////////////////////
    if(PAGE.substring(0,6)=='public') return false;
// ////////////////////////////////////////////////////////////////////////////////

    /**
     * Plugin Impromptu - Options par défaut
     */
    jQuery.prompt.setDefaults({
      opacity: 0.7, // Combiné au background-color:#000 modifié dans le css
      zIndex : 9000 // Pour passer devant un fancybox
    });
    jQuery.prompt.setStateDefaults({
      focus  : null // Pas de focus particulier ; ne fonctionne qu'avec la syntaxe utilisant une collection d'étapes, pas la syntaxe directe simplifiée.
    });

    /**
     * Ajouter une méthode de tri au plugin TableSorter
     */
    $.tablesorter.addParser
    (
      {
        // set a unique id
        id: 'date_fr',
        is: function(date_fr)
        {
          // return false so this parser is not auto detected
          return false;
        },
        format: function(date_fr)
        {
          // format your data for normalization
          if(date_fr=='-')
          {
            return 99991231;
          }
          tab_date = date_fr.split('/');
          if(tab_date.length==3)
          {
            return tab_date[2]+tab_date[1]+tab_date[0]; // Il s'agit bien d'une concaténation, pas d'une somme.
          }
          else
          {
            return 0;
          }
        },
        // set type, either numeric or text
        type: 'numeric'
      }
    );

    /**
     * MENU - Rendre transparente la page au survol.
     *
     * Difficultés pour utiliser fadeTo('slow',0.05) et fadeTo('normal',1) car une durée d'animation provoque des boucles
     * Difficultés pour utiliser aussi css('opacity',0.05) et css('opacity',1) car un passage de la souris au dessus du menu provoque un clignotement désagréable
     * Alors il a fallu ruser (compliquer) avec un marqueur et un timing...
     */
    var test_over_avant = false;
    var test_over_apres = false;
    $('#menu').mouseenter( function(){test_over_apres = true; });
    $('#menu').mouseleave( function(){test_over_apres = false;});
    function page_transparente()
    {
      $("body").everyTime
      ('5ds', function()
        {
          if( test_over_avant != test_over_apres )
          {
            test_over_avant = test_over_apres ;
            if(test_over_apres)
            {
              $('#cadre_bas').fadeTo('normal',0.05);
            }
            else
            {
              $('#cadre_bas').fadeTo('fast',1);
            }
          }
        }
      );
    }
    page_transparente();

    /**
     * MENU - Déploiement au clic et plus seulement au survol pour les dispositifs tactiles.
     */
    if(isMobile)
    {
      $('#menu').on
      (
        'click',
        'a',
        function()
        {
          var obj_ul = $(this).next('ul');
          if(typeof(obj_ul!=='undefined'))
          {
            var montrer = (obj_ul.css('display')=='block') ? false : true ;
            var premier_menu = ($(this).hasClass('menu')) ? true : false ;
            if(premier_menu)
            {
              $(this).next('ul').css('display','none').find('ul').css('display','none');
              $(this).parent('li').css('background','#66F').find('li').css('background','#66F');
            }
            else
            {
              $(this).parent().parent().find('ul').css('display','none');
              $(this).parent().parent().find('li').css('background','#66F');
            }
            if(montrer)
            {
              obj_ul.css('display','block');
              $(this).parent('li').css('background','#AAF');
            }
            else
            {
              obj_ul.css('display','none');
            }
            if( premier_menu && !montrer )
            {
              $('#cadre_bas').css('opacity',1);
            }
            else
            {
              $('#cadre_bas').css('opacity',0.05);
            }
          }
        }
      );
    }

    /**
     * Select multiples remplacés par une liste de checkbox (code plus lourd, mais résultat plus maniable pour l'utilisateur)
     * - modifier le style du parent d'un chekbox coché (non réalisable en css)
     * - réagir aux clics pour tout cocher ou tout décocher
     */
    $('span.select_multiple').on
    (
      'change',
      'input',
      function()
      {
        if(this.checked)
        {
          $(this).parent().addClass('check');
        }
        else
        {
          $(this).parent().removeAttr('class');
        }
      }
    );

    $('span.check_multiple q.cocher_tout').click
    (
      function()
      {
        var obj_select_multiple = $(this).parent().parent().children('span.select_multiple');
        obj_select_multiple.find('input[type=checkbox]').prop('checked',true);
        obj_select_multiple.children('label').addClass('check');
      }
    );

    $('span.check_multiple q.cocher_rien').click
    (
      function()
      {
        var obj_select_multiple = $(this).parent().parent().children('span.select_multiple');
        obj_select_multiple.find('input[type=checkbox]').prop('checked',false);
        obj_select_multiple.children('label').removeAttr('class');
      }
    );

    $('span.check_multiple q.cocher_inverse').click
    (
      function()
      {
        var obj_select_multiple = $(this).parent().parent().children('span.select_multiple');
        obj_select_multiple.find('input[type=checkbox]').each
        (
          function()
          {
            if($(this).is(':checked'))
            {
              $(this).prop('checked',false);
              $(this).parent().removeAttr('class');
            }
            else
            {
              $(this).prop('checked',true);
              $(this).parent().addClass('check');
            }
          }
        );
      }
    );

    /**
     * Réagir aux clics pour déployer / replier des arbres (matières, items, socle, users)
     */
    $('.arbre_dynamique li span').siblings('ul').hide('fast');
    $(document).on
    (
      'click',
      '.arbre_dynamique li span',
      function()
      {
        $(this).siblings('ul').toggle();
      }
    );

    /**
     * Réagir aux clics pour cocher / décocher un ensemble de cases d'un arbre (items)
     */
    $('.arbre_check q.cocher_tout').click
    (
      function()
      {
        $(this).parent().find('ul').show();
        $(this).parent().find('input[type=checkbox]').prop('checked',true);
      }
    );
    $('.arbre_check q.cocher_rien').click
    (
      function()
      {
        $(this).parent().find('ul').hide();
        $(this).parent().find('input[type=checkbox]').prop('checked',false);
      }
    );

    /**
     * Réagir aux clics pour déployer / contracter l'ensemble d'un arbre à une étape donnée
     */
    $(document).on
    (
      'click',
      'q.deployer_m1 , q.deployer_m2 , q.deployer_n1 , q.deployer_n2 , q.deployer_n3',
      function()
      {
        var stade = $(this).attr('class').substring(9); // 'deployer_' + stade
        var id_arbre = $(this).parent().parent().attr('id');
        $('#'+id_arbre+' ul').css("display","none");
        switch(stade)
        {
          case 'n3' :  // item
            $('#'+id_arbre+' ul.ul_n3').css("display","block");
          case 'n2' :  // thème
            $('#'+id_arbre+' ul.ul_n2').css("display","block");
          case 'n1' :  // domaine
            $('#'+id_arbre+' ul.ul_n1').css("display","block");
          case 'm2' :  // niveau
            $('#'+id_arbre+' ul.ul_m2').css("display","block");
          case 'm1' :  // matière
            $('#'+id_arbre+' ul.ul_m1').css("display","block");
        }
      }
    );

    /**
     * Réagir aux clics quand on coche/décoche un élève d'une arborescence pour le répercuter sur d'autres regroupements
     */
    $('#zone_eleve').on
    (
      'click',
      'input[type=checkbox]',
      function()
      {
        var tab_id = $(this).attr('id').split('_');
        var id_debut = 'id_'+tab_id[1]+'_';
        var etat = ($(this).is(':checked')) ? true : false ;
        $('#zone_eleve input[id^='+id_debut+']').prop('checked',etat);
      }
    );

    /**
     * Lien pour se déconnecter
     */
    $('#deconnecter').click
    (
      function()
      {
        if(DECONNEXION_REDIR!='')
        {
          fermer_session_en_ajax('redirection');
        }
        else if( (PROFIL_TYPE!='webmestre') && (PROFIL_TYPE!='partenaire') && (PROFIL_TYPE!='developpeur') )
        {
          window.document.location.href = './index.php' ;
        }
        else
        {
          window.document.location.href = './index.php?'+PROFIL_TYPE ;
        }
      }
    );

    /**
     * Clic sur une cellule (remplace un champ label, impossible à définir sur plusieurs colonnes)
     */
    $('#table_action').on
    (
      'click',
      'td.label',
      function()
      { 
        $(this).parent().find("input[type=checkbox]:enabled").click();
      }
    );

    /**
     * Clic sur un lien afin d'afficher ou de masquer un groupe d'options d'un formulaire
     */
    $('a.toggle').click
    (
      function()
      {
        $("div.toggle").toggle("slow");
        return false;
      }
    );

    /**
     * Clic sur une image-lien pour imprimer un referentiel en consultation
     */
    $(document).on
    (
      'click',
      'q.imprimer_arbre',
      function()
      {
        imprimer( $(this).closest('div').html() );
      }
    );

    /**
     * Gestion de la durée d'inactivité
     *
     * Fonction tester_compteur() à appeler régulièrement (un diviseur de 60s).
     */
    initialiser_compteur();
    $("body").everyTime
    ('15s', 'compteur' , function()
      {
        tester_compteur();
      }
    );

    /**
     * Ajoute au document un calque qui est utilisé pour afficher un calendrier
     */
    $('<div id="calque"></div>').appendTo(document.body).hide();
    var leave_erreur = false;

    /**
     * Afficher le calque et le compléter : calendrier
     */
    $(document).on
    (
      'click',
      'q.date_calendrier',
      function(e)
      {
        // Récupérer les infos associées
        champ   = $(this).prev().attr('id');    // champ dans lequel retourner les valeurs
        date_fr = $(this).prev().val();
        tab_date = date_fr.split('/');
        if(tab_date.length==3)
        {
          jour  = tab_date[0];
          mois  = tab_date[1];
          annee = tab_date[2];
          get_data = 'j='+jour+'&m='+mois+'&a='+annee;
        }
        else
        {
          get_data='';
        }
        // Afficher le calque
        posX = e.pageX-5;
        posY = e.pageY-5;
        $("#calque").css('left',posX + 'px');
        $("#calque").css('top',posY + 'px');
        $("#calque").html('<label id="ajax_alerte_calque" class="loader">En cours&hellip;</label>').show();
        // Charger en Ajax le contenu du calque
        $.ajax
        (
          {
            type : 'GET',
            url : 'ajax.php?page=calque_date_calendrier',
            data : get_data,
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_alerte_calque').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
              leave_erreur = true;
            },
            success : function(responseHTML)
            {
              if(responseHTML.substring(0,4)=='<h5>')  // Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
              {
                $('#calque').html(responseHTML);
                leave_erreur = false;
              }
              else
              {
                $('#ajax_alerte_calque').removeAttr("class").addClass("alerte").html(responseHTML);
                leave_erreur = true;
              }
            }
          }
        );
      }
    );

    // Masquer le calque ; mouseout ne fonctionne pas à cause des éléments contenus dans le div ; mouseleave est mieux, mais pb qd même avec les select du calendrier
    $("#calque").mouseleave
    (
      function()
      {
        if(leave_erreur)
        {
          $("#calque").html('&nbsp;').hide();
        }
      }
    );

    // Fermer le calque
    $(document).on
    (
      'click',
      '#form_calque #fermer_calque',
      function()
      {
        $("#calque").html('&nbsp;').hide();
        return false;
      }
    );

    // Envoyer dans l'input une date du calendrier
    $(document).on
    (
      'click',
      '#form_calque a.actu',
      function()
      {
        retour = $(this).attr("href").substring(0,10); // substring() car si l'identifiant de session est passé dans l'URL (session.use-trans-sid à ON) on peut récolter un truc comme "14/08/2012?SACoche-session=507ac2c6e1007ce8d311ab221fb41aeabaf879f79317c" !
        $("#"+champ).val( replaceAll('-','/',retour) ).focus();
        $("#calque").html('&nbsp;').hide();
        return false;
      }
    );

    // Recharger le calendrier
    function reload_calendrier(mois,annee)
    {
      $.ajax
      (
        {
          type : 'GET',
          url : 'ajax.php?page=calque_date_calendrier',
          data : 'm='+mois+'&a='+annee,
          dataType : "html",
          success : function(responseHTML)
          {
            if(responseHTML.substring(0,4)=='<h5>')  // Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
            {
              $('#calque').html(responseHTML);
            }
          }
        }
      );
    }
    $(document).on
    (
      'change',
      '#form_calque select.navig',
      function()
      {
        m = $("#m option:selected").val();
        a = $("#a option:selected").val();
        reload_calendrier(m,a);
        return false;
      }
    );
    $(document).on
    (
      'click',
      '#form_calque a.navig',
      function()
      {
        tab = $(this).attr('id').split('_'); // 'calendrier_' + mois + '_' + année
        m = tab[1];
        a = tab[2];
        reload_calendrier(m,a);
        return false;
      }
    );

    /**
     * Gestion d'une demande d'évaluation d'un élève
     */

    $(document).on
    (
      'click',
      'q.demander_add',
      function()
      {
        // Récupérer les infos associées
        infos = $(this).attr('id');    // 'demande_' + matiere_id + '_' + item_id + '_' + score
        tab_infos = infos.split('_');
        if(tab_infos.length!=4)
        {
          return false;
        }
        matiere_id = tab_infos[1];
        item_id    = tab_infos[2];
        score      = (tab_infos[3]!='') ? tab_infos[3] : -1 ; // si absence de score...
        item_nom   = $(this).parent().text();
        // Récupérer le nombre de profs potentiellements concernés
        $.fancybox( '<label class="loader">'+'En cours&hellip;'+'</label>' , {'centerOnScroll':true} );
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page=evaluation_demande_eleve_ajout',
            data : 'f_action=lister_profs'+'&'+'f_matiere_id='+matiere_id,
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $.fancybox( '<label class="alerte">'+'Échec de la connexion !'+'</label>' , {'centerOnScroll':true} );
            },
            success : function(responseHTML)
            {
              if(responseHTML.substring(0,7)!='<option')  // Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
              {
                $.fancybox( '<label class="alerte">'+responseHTML+'</label>' , {'centerOnScroll':true} );
              }
              else
              {

                var contenu = '<h2>Formuler une demande d\'évaluation</h2>'
                            + '<form action="#" method="post" id="form_demande_evaluation">'
                            + '<p class="b">'+item_nom+'</p>'
                            + '<p><label class="tab">Destinaire(s) :</label><select name="f_prof_id">'+responseHTML+'</select></p>'
                            + '<p><label class="tab">Message (facultatif) :</label><textarea id="zone_message" name="f_message" rows="5" cols="75"></textarea><br /><span class="tab"></span><label id="zone_message_reste"></label></p>'
                            + '<div><label class="tab">Document (facultatif) :</label><button id="bouton_upload_demande_document" type="button" class="fichier_import">Choisir un fichier.</button><label id="ajax_upload_demande_document">&nbsp;</label><input id="f_doc_nom" name="f_doc_nom" type="hidden" value="" /></div>'
                            + '<p><span class="tab"></span><input name="f_matiere_id" type="hidden" value="'+matiere_id+'" /><input name="f_item_id" type="hidden" value="'+item_id+'" /><input name="f_score" type="hidden" value="'+score+'" />'
                            + '<button id="confirmer_demande_evaluation" type="button" class="valider">Confirmer.</button> <button id="fermer_demande_evaluation" type="button" class="annuler">Annuler.</button><label id="ajax_msg_confirmer_demande"></label></p>'
                            + '</form>';
                $.fancybox( contenu , { 'modal':true , 'centerOnScroll':true } );
                $('#form_demande_evaluation textarea').focus();
                // Indiquer le nombre de caractères restant autorisés dans le textarea
                $('#zone_message').keyup
                (
                  function()
                  {
                    afficher_textarea_reste( $(this) , 500 );
                  }
                );
                // Fonction à définir avant new AjaxUpload() sinon Firefox plante
                function verifier_demande_document(fichier_nom,fichier_extension)
                {
                  if (fichier_nom==null || fichier_nom.length<5)
                  {
                    $('#f_doc_nom').val('');
                    $('#ajax_upload_demande_document').removeAttr("class").addClass("erreur").html('Cliquer sur "Parcourir..." pour indiquer un chemin de fichier correct.');
                    return false;
                  }
                  else if ('.bat.com.exe.php.zip.'.indexOf('.'+fichier_extension.toLowerCase()+'.')!=-1)
                  {
                    $('#f_doc_nom').val('');
                    $('#ajax_upload_demande_document').removeAttr("class").addClass("erreur").html('Extension non autorisée.');
                    return false;
                  }
                  else
                  {
                    $('#f_doc_nom').val('');
                    $('#bouton_upload_demande_document').prop('disabled',true);
                    $('#ajax_upload_demande_document').removeAttr("class").addClass("loader").html("En cours&hellip;");
                    return true;
                  }
                }
                // Fonction à définir avant new AjaxUpload() sinon Firefox plante
                function retourner_demande_document(fichier_nom,responseHTML)  // Attention : avec jquery.ajaxupload.js, IE supprime mystérieusement les guillemets et met les éléments en majuscules dans responseHTML.
                {
                  fichier_extension = fichier_nom.split('.').pop();
                  var tab_infos = responseHTML.split(']¤[');
                  if(tab_infos[0]!='ok')
                  {
                    $('#ajax_upload_demande_document').removeAttr("class").addClass("alerte").html(responseHTML);
                  }
                  else
                  {
                    initialiser_compteur();
                    var doc_nom = tab_infos[1];
                    var doc_url = tab_infos[2];
                    $('#f_doc_nom').val(doc_nom);
                    $('#ajax_upload_demande_document').removeAttr("class").addClass("valide").html('<a href="'+doc_url+'" target="_blank">'+fichier_nom+'</a>');
                  }
                  $('#bouton_upload_demande_document').prop('disabled',false);
                }

                // Envoi du fichier avec jquery.ajaxupload.js ; on lui donne un nom afin de pouvoir changer dynamiquement le paramètre.
                var upload_demande_document = new AjaxUpload
                ('#bouton_upload_demande_document',
                  {
                    action: 'ajax.php?page=evaluation_demande_eleve_ajout',
                    name: 'userfile',
                    data: {'f_action':'uploader_document'},
                    autoSubmit: true,
                    responseType: "html",
                    onSubmit: verifier_demande_document,
                    onComplete: retourner_demande_document
                  }
                );

              }
              $('#form_demande_evaluation button').prop('disabled',false);
            }
          }
        );
      }
    );

    $(document).on
    (
      'click',
      '#fermer_demande_evaluation',
      function()
      {
        if(PAGE!='evaluation_voir')
        {
          $.fancybox.close();
        }
        else
        {
          $.fancybox( { 'href':'#zone_eval_voir' , onStart:function(){$('#zone_eval_voir').css("display","block");} , onClosed:function(){$('#zone_eval_voir').css("display","none");} , 'centerOnScroll':true } );
        }
        return false;
      }
    );

    $(document).on
    (
      'click',
      '#confirmer_demande_evaluation',
      function()
      {
        $('#form_demande_evaluation button').prop('disabled',true);
        $('#ajax_msg_confirmer_demande').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page=evaluation_demande_eleve_ajout',
            data : 'f_action=confirmer_ajout'+'&'+$("#form_demande_evaluation").serialize(),
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_msg_confirmer_demande').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
              $('#form_demande_evaluation button').prop('disabled',false);
            },
            success : function(responseHTML)
            {
              if(responseHTML.substring(0,6)!='<label')  // Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
              {
                $('#ajax_msg_confirmer_demande').removeAttr("class").addClass("alerte").html(responseHTML);
              }
              else
              {
                $("#form_demande_evaluation").html( responseHTML + '<p><span class="tab"></span><button id="fermer_demande_evaluation" type="button" class="retourner">Fermer.</button></p>' );
                if (typeof(DUREE_AUTORISEE)!=='undefined')
                {
                  initialiser_compteur(); // Ne modifier l'état du compteur que si l'appel ne provient pas d'une page HTML de bilan
                }
              }
              $('#form_demande_evaluation button').prop('disabled',false);
            }
          }
        );
      }
    );

  }
);
