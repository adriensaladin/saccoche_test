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
	$('img[title] , th[title] , td[title] , a[title] , q[title] , input[title]').tooltip({showURL:false});
}

/**
 * Fonction pour un tester la robustesse d'un mot de passe.
 *
 * @param void
 * @return void
 */
function analyse_mdp(mdp)
{
	mdp.replace(/^\s+/g,'').replace(/\s+$/g,'');	// équivalent de trim() en javascript
	mdp = mdp.substring(0,20);
	var nb_min = 0;
	var nb_maj = 0;
	var nb_num = 0;
	var nb_spe = 0;
	var longueur = mdp.length;
	for (i=0 ; i<longueur ; i++)
	{
		var car = mdp.charAt(i);
				 if((/[a-z]/).test(car)) {nb_min++;}	// 2 points maxi pour des minuscules
		else if((/[A-Z]/).test(car)) {nb_maj++;}	// 2 points maxi pour des majuscules
		else if((/[0-9]/).test(car)) {nb_num++;}	// 2 points maxi pour des chiffres
		else                         {nb_spe++;}	// 6 points maxi pour des caractères autres
	}
	var coef = Math.min(nb_min,2) + Math.min(nb_maj,2) + Math.min(nb_num,2) + Math.min(nb_spe*2,6) ;
	if(longueur>7)
	{
		coef += Math.floor( (longueur-5)/3 );	// 6 points maxi pour la longueur du mdp
	}
	coef = Math.min(coef,12);	// total 18 points maxi, plafonné à 12
	var rouge = 255 - 16*Math.max(0,coef-6) ; // 255 -> 255 -> 159
	var vert  = 159 + 16*Math.min(6,coef) ;   // 159 -> 255 -> 255
	var bleu  = 159 ;
	$('#robustesse').css('background-color','rgb('+rouge+','+vert+','+bleu+')').children('span').html(coef);
}

/**
 * Fonction pour imprimer un contenu
 *
 * En javascript, print() s'applique à l'objet window, et l'usage d'une feuille se style adaptée n'a pas permis d'obtenir un résultat satisfaisant.
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

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
//	Gestion de la durée d'inactivité
//	On utilise un cookie plutôt qu'une variable js car ceci permet de gérer plusieurs onglets.
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

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
			setVolume(100);play("bip");
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
			error : function(msg,string)
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
			error : function(msg,string)
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
					$('#top_info').html('<span class="button alerte">Votre session a expiré. Vous êtes désormais déconnecté de SACoche !</span> <span class="button connexion"><a href="./index.php">Se reconnecter&hellip;</a></span>');
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
 * Lire un fichier audio grace au génial lecteur de neolao http://flash-mp3-player.net/
 */

// Objet js
var myListener = new Object();
// Initialisation
myListener.onInit = function()
{
	this.position = 0;
};
// Update
myListener.onUpdate = function()
{
	info_playing  = this.isPlaying;
	info_url      = this.url;
	info_volume   = this.volume;
	info_position = this.position;
	info_duration = this.duration;
	info_bytes    = this.bytesLoaded + "/" + this.bytesTotal + " (" + this.bytesPercent + "%)";
	var isPlaying = (this.isPlaying == "true");
};
// Le lecteur flash
function getFlashObject()
{
	return document.getElementById("myFlash");
}
// Play
function play(file)
{
	if (myListener.position == 0)
	{
		getFlashObject().SetVariable("method:setUrl", "./_mp3/"+file+".mp3");
	}
	getFlashObject().SetVariable("method:play", "");
	getFlashObject().SetVariable("enabled", "true");
}
// Pause
function pause()
{
	getFlashObject().SetVariable("method:pause", "");
}
// Stop
function stop()
{
	getFlashObject().SetVariable("method:stop", "");
}
// setPosition
function setPosition(position)
{
	getFlashObject().SetVariable("method:setPosition", position);
}
// setVolume
function setVolume(volume)
{
	getFlashObject().SetVariable("method:setVolume", volume);
}

/**
 * Fonction pour arrondir les coins des boites avec bordures
 *
 * En CSS3 il y a la propriété border-radius : http://www.w3.org/TR/css3-background/#the-border-radius
 * Actuellement elle est pré-déclinée par qqs navigateurs :
 * => Gecko, 	avec -moz-border-radius 		(valable pour Firefox, Camino et tout navigateur basé sur Gecko),
 * => Webkit, 	avec -webkit-border-radius 	(valable pour Safari, Chrome et tout navigateur basé sur Webkit).
 * => KHTML, 	avec -khtml-border-radius 	(valable pour Konqueror),
 * => Opera, 	avec -o-border-radius 	(valable pour Opéra depuis la version 10.50),
 * => MSIE à partir de sa version 9
 * Sinon (MSIE...) il y a des techniques tordues et pas universelles (div imbriqués, pixel par pixel...).
 * => http://plugins.jquery.com/project/backgroundCanvas
 * => http://plugins.jquery.com/project/roundCorners => fonctionne à peu près mais temps de calcul long + plante IE si masquage cadre_haut + fait disparaitre la ligne centrale et pb de bordures à cause de l'overflow...
 * => http://plugins.jquery.com/project/DivCorners => reste figé en largeur et en hauteur
 * => http://plugins.jquery.com/project/curvy-corners => erreur inexpliquée sous IE, marge sous FF (il faut ajouter un margin 10px), très joli sinon (http://www.curvycorners.net/instructions/)
 *
 * @param void
 * @return void
 */
function arrondir_coins(element,taille)
{
	// On cherche si le navigateur sait gérer cet attribut css3, éventuellement avec une syntaxe propriétaire
	     if(document.body.style['BorderRadius'] !== undefined)       {style = 'border-radius';}
	else if(document.body.style['borderRadius'] !== undefined)       {style = 'border-radius';} // Opéra (commence par une minuscule...)
	else if(document.body.style['MozBorderRadius'] !== undefined)    {style = '-moz-border-radius';}
	else if(document.body.style['WebkitBorderRadius'] !== undefined) {style = '-webkit-border-radius';}
	else if(document.body.style['KhtmlBorderRadius'] !== undefined)  {style = '-khtml-border-radius';}
	else if(document.body.style['OBorderRadius'] !== undefined)      {style = '-o-border-radius';}
	else {style = false;}
	if(style !== false)
	{
		$(element).css(style,taille);
	}
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
	"Veuillez entrer une date correcte."
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
 * jQuery !
 */
$(document).ready
(
	function()
	{

		//	Initialisation
		format_liens('body');
		infobulle();

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
		 * Réagir aux clics pour déployer / replier des arbres (matières, items, socle, users)
		 */
		$('.arbre_dynamique li span').siblings('ul').hide('fast');
		$('.arbre_dynamique li span').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				$(this).siblings('ul').toggle();
			}
		);

		/**
		 * Réagir aux clics pour cocher / décocher un ensemble de cases d'un arbre (items)
		 */
		$('.arbre_check input[name=all_check]').click
		(
			function()
			{
				$(this).parent().find('ul').show();
				$(this).parent().find('input[type=checkbox]').prop('checked',true);
				return false;
			}
		);
		$('.arbre_check input[name=all_uncheck]').click
		(
			function()
			{
				$(this).parent().find('ul').hide();
				$(this).parent().find('input[type=checkbox]').prop('checked',false);
				return false;
			}
		);

		/**
		 * Réagir aux clics pour déployer / contracter l'ensemble d'un arbre à une étape donnée
		 */
		$('a.all_extend').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				var stade = $(this).attr('href');
				var id_arbre = $(this).parent().parent().attr('id');
				$('#'+id_arbre+' ul').css("display","none");
				switch(stade)
				{
					case 'n3' :	// item
						$('#'+id_arbre+' ul.ul_n3').css("display","block");
					case 'n2' :	// thème
						$('#'+id_arbre+' ul.ul_n2').css("display","block");
					case 'n1' :	// domaine
						$('#'+id_arbre+' ul.ul_n1').css("display","block");
					case 'm2' :	// niveau
						$('#'+id_arbre+' ul.ul_m2').css("display","block");
					case 'm1' :	// matière
						$('#'+id_arbre+' ul.ul_m1').css("display","block");
				}
				return false;
			}
		);

		/**
		 * Lien pour se déconnecter
		 */
		$('#deconnecter').click
		(
			function()
			{
				window.document.location.href='./index.php';
			}
		);

		/**
		 * Clic sur une image-lien pour imprimer un referentiel en consultation
		 */
		$('#fancybox_contenu q.imprimer').live
		('click',
			function()
			{
				imprimer(document.getElementById('fancybox_contenu').innerHTML);
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
		 * Clic sur une image-lien afin d'afficher ou de masquer le détail d'un bilan d'acquisition du socle
		 */
		$('img.toggle').live
		('click',
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
		$('a.pop_up').live
		('click',
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
		 * Calque pour afficher un calendrier, ou le résultat d'une demande d'évaluation
		 */

		// Ajoute au document le calque d'aide au remplissage
		$('<div id="calque"></div>').appendTo(document.body).hide();
		var leave_erreur = false;

		// Afficher le calque et le compléter : calendrier
		$('q.date_calendrier').live
		('click',
			function(e)
			{
				// Récupérer les infos associées
				champ   = $(this).prev().attr("id");    // champ dans lequel retourner les valeurs
				date_fr = $(this).prev().attr("value");
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
				$("#calque").html('<label id="ajax_alerte_calque" for="nada" class="loader">Chargement en cours...</label>').show();
				// Charger en Ajax le contenu du calque
				$.ajax
				(
					{
						type : 'GET',
						url : 'ajax.php?page=date_calendrier',
						data : get_data,
						dataType : "html",
						error : function(msg,string)
						{
							$('#ajax_alerte_calque').removeAttr("class").addClass("alerte").html("Echec de la connexion !");
							leave_erreur = true;
						},
						success : function(responseHTML)
						{
							if(responseHTML.substring(0,4)=='<h5>')	// Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
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

		// Afficher le calque et le compléter : ajouter une demande d'évaluation
		$('q.demander_add').live
		('click',
			function(e)
			{
				// Récupérer les infos associées
				infos = $(this).attr('id');    // 'demande_' + matiere_id + '_' + item_id + '_' + score
				tab_infos = infos.split('_');
				if(tab_infos.length==4)
				{
					matiere_id = tab_infos[1];
					item_id    = tab_infos[2];
					score      = (tab_infos[3]!='') ? tab_infos[3] : -1 ; // si absence de score...
					get_data   = 'matiere_id='+matiere_id+'&item_id='+item_id+'&score='+score;
				}
				else
				{
					return false;
				}
				// Afficher le calque
				posX = e.pageX-5;
				posY = e.pageY-5;
				$("#calque").css('left',posX + 'px');
				$("#calque").css('top',posY + 'px');
				$("#calque").html('<label id="ajax_alerte_calque" for="nada" class="loader">Chargement en cours...</label>').show();
				// Charger en Ajax le contenu du calque
				$.ajax
				(
					{
						type : 'GET',
						url : 'ajax.php?page=eleve_eval_demande_ajout',
						data : get_data,
						dataType : "html",
						error : function(msg,string)
						{
							$('#ajax_alerte_calque').removeAttr("class").addClass("alerte").html("Echec de la connexion !");
							leave_erreur = true;
						},
						success : function(responseHTML)
						{
							if(responseHTML.substring(0,5)=='<form')	// Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
							{
								if (typeof(DUREE_AUTORISEE)!=='undefined')
								{
									initialiser_compteur(); // Ne modifier l'état du compteur que si l'appel ne provient pas d'une page HTML de bilan
								}
								$('#calque').html(responseHTML);
								leave_erreur = true;
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
		$("#form_calque #fermer_calque").live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				$("#calque").html('&nbsp;').hide();
				return false;
			}
		);

		// Envoyer dans l'input une date du calendrier
		$("#form_calque a.actu").live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				retour = $(this).attr("href");
				retour = retour.replace(/\-/g,"/"); // http://javascript.developpez.com/sources/?page=tips#replaceall
				$("#"+champ).val( retour ).focus();
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
					url : 'ajax.php?page=date_calendrier',
					data : 'm='+mois+'&a='+annee,
					dataType : "html",
					success : function(responseHTML)
					{
						if(responseHTML.substring(0,4)=='<h5>')	// Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
						{
							$('#calque').html(responseHTML);
						}
					}
				}
			);
		}
		$("#form_calque select.actu").live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('change',
			function()
			{
				m = $("#m option:selected").val();
				a = $("#a option:selected").val();
				reload_calendrier(m,a);
				return false;
			}
		);
		$("#form_calque input.actu").live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				tab = $(this).attr('id').split('_'); // 'calendrier_' + mois + '_' + année
				m = tab[1];
				a = tab[2];
				reload_calendrier(m,a);
				return false;
			}
		);

	}
);
