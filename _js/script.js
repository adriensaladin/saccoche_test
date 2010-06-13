/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 * 
 * ****************************************************************************************************
 * SACoche <http://competences.sesamath.net> - Suivi d'Acquisitions de Compétences
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
 * Fonction pour afficher / masquer les images cliquables (en général dans la dernière colonne du tableau)
 * Remarque : un toogle ne peut être simplement mis en oeuvre à cause des nouvelle images créées...
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
 * Fonction pour formater les liens vers l'extérieur (nouvel onglet)
 * Fonction pour formater les liens vers l'aide en ligne (nouvelle fenêtre pop-up)
 * Fonction pour formater les liens de type mailto
 * @param element "body" ou un élément sur lequel restreindre la recherche
 * @return void
 */

function format_liens(element)
{
	$(element).find("a.lien_ext" ).attr("target","_blank");
	$(element).find("a.lien_ext" ).css({"padding-right":"14px" , "background":"url(./_img/popup2.gif) no-repeat right"});
	$(element).find("a.pop_up" ).css({"padding-right":"18px" , "background":"url(./_img/popup1.gif) no-repeat right"});
	$(element).find("a.lien_mail").css({"padding-left":"15px" , "background":"url(./_img/mail.gif) no-repeat left"});
}

/**
 * Fonction pour appliquer une infobulle au survol de tous les éléments possédants un attribut "title"
 * Remarque : attention, cela fait disparaitre le contenu de l'attribut alt"...
 * @param void
 * @return void
 */

function infobulle()
{
	$('img[title]').tooltip({showURL:false});
	$('th[title]').tooltip({showURL:false});
	$('td[title]').tooltip({showURL:false});
	$('a[title]').tooltip({showURL:false});
	$('q[title]').tooltip({showURL:false});
}

/**
 * Fonction pour un préchargement d'images (pas certain que ça fonctionne bien...).
 * @param why valeur parmi [show] [hide]
 * @return void
 */

function preloadImages(tableau)
{
	var repertoire = './_img/';
	for (var i=0; i<tableau.length; i++)
	{
		$('<img/>').attr('src', repertoire+tableau[i]);
	}
}

/**
 * Fonction pour un tester la robustesse d'un mot de passe.
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

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
//	Gestion de la durée d'inactivité
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

/**
 * Fonction pour modifier l'état du compteur, et déconnecter si besoin
 * @param nb_minutes_restantes
 * @return void
 */

function maj_clock(evolution)
{
	DUREE_RESTANTE = (evolution==-1) ? DUREE_RESTANTE-1 : DUREE_AUTORISEE ;
	if(DUREE_RESTANTE>5)
	{
		$("#clock").html('<img alt="" src="./_img/clock_fixe.png" /> '+DUREE_RESTANTE+' min');
		if( (evolution==-1) && (DUREE_RESTANTE%10==0) )
		{
			// Fonction conserver_session_active() à appeler une fois toutes les 10min ; code placé ici pour éviter un appel après déconnection, et l'application inutile d'un 2nd compteur
			conserver_session_active();
		}
		
	}
	else
	{
		setVolume(100);play("bip");
		$("#clock").html('<img alt="" src="./_img/clock_anim.gif" /> '+DUREE_RESTANTE+' min');
		if(DUREE_RESTANTE==0)
		{
			$('q.deconnecter').click();
		}
	}
}

/**
 * Fonction pour ne pas perdre la session : appel au serveur toutes les 10 minutes (en ajax)
 * @param void
 * @return void
 */

function conserver_session_active()
{
	$.ajax
	(
		{
			type : 'GET',
			url : 'ajax.php?dossier='+DOSSIER+'&fichier=conserver_session_active',
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
 * Fonction pour lire un fichier audio grace au génial lecteur de neolao http://flash-mp3-player.net/
 * @param void
 * @return void
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
 * jQuery !
 */

$(document).ready
(
	function()
	{
		format_liens('body');
		infobulle();
		var imagesToPreload = Array(
			'ajax/ajax_alerte.png','ajax/ajax_erreur.png','ajax/ajax_loader.gif','ajax/ajax_valide.png','puce_danger.gif',
			'folder/folder_m1.png','folder/folder_m2.png','folder/folder_n0.png','folder/folder_n1.png','folder/folder_n2.png','folder/folder_n3.png'
		);
		preloadImages(imagesToPreload);

		// MENU - Styler les puces avec les images
		$("#treeview li").each
		(
			function()
			{
				classe = $(this).attr("class");
				if(classe)
				{
					$(this).css("background","url(./_img/menu/"+classe+".png) no-repeat top left");
				}
			}
		);

		// MENU - Afficher / Masquer
		$("#appel_menu").hover
		(
			function()
			{
				$("#treeview").show();
			}
			,
			function()
			{
			}
		);
		$("#treeview").hover
		(
			function()
			{
			}
			,
			function()
			{
				$("#treeview").hide();
			}
		);

		// piocher dans un arbre de COMPETENCES - Réagir aux clics sur les dossiers
		$('#zone_compet li span').siblings('ul').hide('fast');
		$('#zone_compet li span').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				$(this).siblings('ul').toggle();
			}
		);

		// consulter un arbre du SOCLE - Réagir aux clics sur les dossiers
		$('#zone_paliers li span').siblings('ul').hide('fast');
		$('#zone_paliers li span').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				$(this).siblings('ul').toggle();
			}
		);

		// piocher dans un arbre du SOCLE (masqué au départ) - Réagir aux clics sur les dossiers
		$('#zone_socle li span').siblings('ul').hide('fast');
		$('#zone_socle li span').click
		(
			function()
			{
				$(this).siblings('ul').toggle();
			}
		);

		// piocher dans un arbre d' ELEVES - Réagir aux clics sur les dossiers
		$('#zone_eleve li span').siblings('ul').hide('fast');
		$('#zone_eleve li span').click
		(
			function()
			{
				$(this).siblings('ul').toggle();
			}
		);

		// Lien pour se déconnecter
		$('q.deconnecter').click
		(
			function()
			{
				window.document.location.href='./index.php';
			}
		);

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		//	Clic sur un lien afin d'afficher ou de masquer un groupe d'options d'un formulaire
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		$('a.toggle').click
		(
			function()
			{
				$("div.toggle").toggle("slow");
				return false;
			}
		);

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		//	Clic sur un lien afin d'afficher ou de masquer le détail d'un bilan d'acquisition du socle
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		$('img.toggle').live
		('click',
			function()
			{
				id = $(this).parent().attr('lang');
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

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		//	Clic sur un lien pour ouvrir une fenêtre d'aide en ligne (pop-up)
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

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

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		//	Gestion de la durée d'inactivité
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		// Fonction maj_clock() à appeler une fois toutes les 1min = 60s
		if(DOSSIER!='public')
		{
			$("body").everyTime
			('60s', function()
				{
					maj_clock(-1);
				}
			);
		}

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		//	Calque pour afficher un calendrier, ou le résultat d'une demande d'évaluation
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

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
						url : 'ajax.php?dossier=public&fichier=date_calendrier',
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
				infos = $(this).attr("lang");    // 'ids_' + eleve_id + '_' + matiere_id + '_' + item_id + '_' + score
				tab_infos = infos.split('_');
				if(tab_infos.length==5)
				{
					eleve_id   = tab_infos[1];
					matiere_id = tab_infos[2];
					item_id    = tab_infos[3];
					score      = (tab_infos[4]!='') ? tab_infos[4] : -1 ; // si absence de score...
					get_data   = 'eleve_id='+eleve_id+'&matiere_id='+matiere_id+'&item_id='+item_id+'&score='+score;
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
						url : 'ajax.php?dossier=public&fichier=demander_add',
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
								maj_clock(1);
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
		$("#form_calque input[name=fermer]").live // live est utilisé pour prendre en compte les nouveaux éléments créés
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
					url : 'ajax.php?dossier=public&fichier=date_calendrier',
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
			}
		);
		$("#form_calque input.actu").live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				tab = $(this).attr("lang").split('_');
				m = tab[0];
				a = tab[1];
				reload_calendrier(m,a);
				return false;
			}
		);

	}
);
