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
 * Fonction pour formater les liens
 *
 * - vers l'extérieur (nouvel onglet)
 * - vers l'aide en ligne (nouvelle fenêtre pop-up)
 * - de type mailto
 *
 * @param element "body" ou un élément sur lequel restreindre la recherche
 * @return void
 */
function format_liens(element)
{
  $(element).find("a.lien_ext" ).attr("target","_blank");
  $(element).find("a.lien_ext" ).css({"padding-right":"14px" , "background":"url(./_img/puce/puce_popup_onglet.gif) no-repeat right"});
  $(element).find("a.pop_up" ).css({"padding-right":"18px" , "background":"url(./_img/puce/puce_popup_window.gif) no-repeat right"});
  $(element).find("a.lien_mail").css({"padding-left":"15px" , "background":"url(./_img/puce/puce_mail.gif) no-repeat left"});
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
      items: "img[title] , th[title] , td[title] , a[title] , q[title]",
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
 * @param void
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
 * Fonction pour afficher et cocher une liste de profs donnés
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
 * Fonction pour afficher le nombre de caractères restant autorisés dans un textarea.
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
  if(textarea_contenu.length > textarea_maxi_length)
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
  $("#clock").html(DUREE_AFFICHEE+' min').parent().removeAttr("class").addClass("button clock_fixe");
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
      $("#clock").html(DUREE_AFFICHEE+' min').parent().removeAttr("class").addClass("button clock_fixe");
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
      $("#clock").html(DUREE_AFFICHEE+' min').parent().removeAttr("class").addClass("button clock_anim");
      if(DUREE_AFFICHEE==0)
      {
        fermer_session();
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
        alert('Avertissement : échec lors de la connexion au serveur !\nLe travail en cours pourrait ne pas pouvoir être sauvegardé...');
      },
      success : function(responseHTML)
      {
        if(responseHTML != 'ok')
        {
          alert(responseHTML);
        }
      }
    }
  );
}

/**
 * Fonction pour fermer la session : appel si le compteur arrive à zéro (en ajax)
 *
 * @param void
 * @return void
 */
function fermer_session()
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
        $("body").stopTime('compteur');
        $('#menu').remove();
        if(CONNEXION_USED=='normal')
        {
          var adresse = ( (PROFIL_TYPE!='webmestre') && (PROFIL_TYPE!='partenaire') ) ? './index.php' : './index.php?'+PROFIL_TYPE ;
          $('#top_info').html('<span class="button alerte">Votre session a expiré. Vous êtes désormais déconnecté de SACoche !</span> <span class="button connexion"><a href="'+adresse+'">Se reconnecter&hellip;</a></span>');
        }
        else
        {
          $('#top_info').html('<span class="button alerte">Session expirée. Vous êtes déconnecté de SACoche mais sans doute pas du SSO !</span> <span class="button connexion"><a href="#" onclick="document.location.reload()">Recharger la page&hellip;</a></span>');
        }
        $.fancybox( '<div class="danger">Délai de '+DUREE_AUTORISEE+'min sans activité atteint &rarr; session fermée.<br />Toute action ultérieure ne sera pas enregistrée.</div>' , {'centerOnScroll':true} );
      }
    }
  );
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
 * Fonction pour tester une URL : extrait du plugin jQuery Validation
 *
 * @param string
 * @return bool
 */
function testURL(lien)
{
  return /^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)*(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(lien);
}
jQuery.validator.addMethod
(
  "testURL", function(value, element)
  {
    return this.optional(element) || testURL(value) ;
  }
  , "URL invalide"
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
 * jQuery !
 */
$(document).ready
(
  function()
  {

    // Initialisation
    format_liens('body');
    infobulle();

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
     * Difficultés pour utiliser fadeTo('slow',0.2) et fadeTo('normal',1) car une durée d'animation provoque des boucles
     * Difficultés pour utiliser aussi css('opacity',0.2) et css('opacity',1) car un passage de la souris au dessus du menu provoque un clignotement désagréable
     * Alors il a fallu ruser (compliquer) avec un marqueur et un timing...
     */
    var test_over_avant = false;
    var test_over_apres = false;
    $('#menu li').mouseenter( function(){test_over_apres = true; });
    $('#menu li').mouseleave( function(){test_over_apres = false;});
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
              $('#cadre_bas').fadeTo('normal',0.2);
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
     * Si on appuie sur la touche entrée, le premier élèment de formulaire est actionné.
     * S'il s'agit d'un input type image, cela peut dé-cocher tout un ensemble de cases à l'insu de l'utilisateur.
     * Feinte de balayeur trouvée : insérer en premier un input type image inoffensif.
     * Mais il faut aussi saisir son interception, sinon le formulaire est envoyée et la page rechargée.
     */
    // 
    $(document).on
    (
      'click',
      'input[name=leurre]',
      function()
      {
        return false;
      }
    );

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
        var adresse = ( (PROFIL_TYPE!='webmestre') && (PROFIL_TYPE!='partenaire') ) ? './index.php' : './index.php?'+PROFIL_TYPE ;
        window.document.location.href = adresse;
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
     * Clic sur une image-lien afin d'afficher ou de masquer le détail d'une synthese ou d'un relevé socle
     */
    $(document).on
    (
      'click',
      'img.toggle',
      function()
      {
        id = $(this).parent().attr('id').substring(3); // 'to_' + id
        $('#'+id).toggle('fast');
        src = $(this).attr('src');
        if( src.indexOf("plus") > 0 )
        {
          $(this).attr('src',src.replace('plus','moins'));
        }
        else
        {
          $(this).attr('src',src.replace('moins','plus'));
        }
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
          var haut  = 0 ;
          window.moveTo(gauche,haut);
          window.resizeTo(largeur,hauteur);
        }
        // Fenêtre pop-up
        var largeur = 600 ;
        var hauteur = screen.height * 1 ;
        var gauche = screen.width - largeur ;
        var haut  = 0 ;
        w = window.open( adresse , 'popup' ,"toolbar=no,location=no,menubar=no,directories=no,status=no,scrollbars=yes,resizable=yes,copyhistory=no,width="+largeur+",height="+hauteur+",top="+haut+",left="+gauche ) ;
        w.focus() ;
        return false;
      }
    );

    /**
     * Gestion de la durée d'inactivité
     *
     * Fonction tester_compteur() à appeler régulièrement (un diviseur de 60s).
     */
    if(PAGE.substring(0,6)!='public')
    {
      initialiser_compteur();
      $("body").everyTime
      ('15s', 'compteur' , function()
        {
          tester_compteur();
        }
      );
    }

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
     * Calque pour une demande d'évaluation élève
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
        var contenu = '<h2>Formuler une demande d\'évaluation</h2>'
                    + '<form action="#" method="post" id="form_demande_evaluation">'
                    + '<p class="b">'+item_nom+'</p>'
                    + '<p>Message (facultatif) : <textarea id="zone_message" name="message" rows="5" cols="75"></textarea><br /><span class="tab"></span><label id="zone_message_reste"></label></p>'
                    + '<p><span class="tab"></span><input name="matiere_id" type="hidden" value="'+matiere_id+'" /><input name="item_id" type="hidden" value="'+item_id+'" /><input name="score" type="hidden" value="'+score+'" />'
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
        return(false);
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
            data : $("#form_demande_evaluation").serialize(),
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
