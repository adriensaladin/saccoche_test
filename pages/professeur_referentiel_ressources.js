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

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Tester une URL : extrait du plugin jQuery Validation
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function testURL(lien)
{
	return /^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)*(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(lien);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Permettre l'utilisation de caractères spéciaux
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

var tab_entite_nom = new Array('&sup2;','&sup3;','&times;','&divide;','&minus;','&pi;','&rarr;','&radic;','&infin;','&asymp;','&ne;','&le;','&ge;');
var tab_entite_val = new Array('²'     ,'³'     ,'×'      ,'÷'       ,'–'      ,'π'   ,'→'     ,'√'      ,'∞'      ,'≈'      ,'≠'   ,'≤'   ,'≥'   );
var imax = tab_entite_nom.length;
function entity_convert(string)
{
	for(i=0;i<imax;i++)
	{
		var reg = new RegExp(tab_entite_nom[i],"g");
		string = string.replace(reg,tab_entite_val[i]);
	}
	return string;
}

// jQuery !
$(document).ready
(
	function()
	{

		// initialisation (variables globales)
		var item_id     = 0;
		var item_nom    = '';
		var upload_lien = '';
		var matiere_id  = 0;
		var matiere_ref = '';
		var bouton_interface_travail = etablissement_identifie ? '<q class="ress_page_elaborer" title="Créer / Modifier une page de ressources pour travailler (partagées sur le serveur communautaire)."></q>' : '<q class="partager_non" title="Pour pouvoir créer une page de ressources sur le serveur communautaire, un administrateur doit préalablement identifier l\'établissement dans la base Sésamath."></q>' ;
		var tab_ressources = new Array();
		var images = new Array();
		images[1]  = '';
		images[1] += '<q class="modifier" title="Modifier ce sous-titre"></q>';
		images[1] += '<q class="dupliquer" title="Dupliquer ce sous-titre"></q>';
		images[1] += '<q class="supprimer" title="Supprimer ce sous-titre"></q>';
		images[2]  = '';
		images[2] += '<q class="modifier" title="Modifier ce lien"></q>';
		images[2] += '<q class="dupliquer" title="Dupliquer ce lien"></q>';
		images[2] += '<q class="supprimer" title="Supprimer ce lien"></q>';
		images[3]  = '';
		images[3] += '<q class="ajouter" title="Ajouter ce lien"></q>';
		images[4]  = '';
		images[4] += '<q class="valider" title="Sélectionner cette ressource"></q>';

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Charger le form zone_elaboration_referentiel en ajax
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#zone_choix_referentiel q.modifier').click
		(
			function()
			{
				var id      = $(this).parent().attr('id');
				tab_id      = id.split('_');
				matiere_id  = tab_id[1];
				niveau_id   = tab_id[2];
				matiere_ref = $(this).parent().parent().attr('class').substring(3);
				afficher_masquer_images_action('hide');
				new_label = '<label for="'+id+'" class="loader">Demande envoyée...</label>';
				$(this).after(new_label);
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'action=Voir_referentiel&matiere_id='+matiere_id+'&niveau_id='+niveau_id+'&matiere_ref='+matiere_ref,
						dataType : "html",
						error : function(msg,string)
						{
							$.fancybox( '<label class="alerte">'+'Echec de la connexion !'+'</label>' , {'centerOnScroll':true} );
							$('label[for='+id+']').remove();
							afficher_masquer_images_action('show');
						},
						success : function(responseHTML)
						{
							initialiser_compteur();
							if(responseHTML.substring(0,16)!='<ul class="ul_m1')	// Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
							{
								$.fancybox( '<label class="alerte">'+responseHTML+'</label>' , {'centerOnScroll':true} );
							}
							else
							{
								$('#zone_choix_referentiel').hide();
								$('#zone_elaboration_referentiel').html('<span class="tab"></span><button id="fermer_zone_elaboration_referentiel" type="button" class="retourner">Retour à la liste des referentiels</button>'+responseHTML);
								// Récupérer le contenu des title des ressources avant que le tooltip ne les enlève
								// Ajouter les icônes pour modifier les items
								$('#zone_elaboration_referentiel li.li_n3').each
								(
									function()
									{
										id2 = $(this).attr('id').substring(3);
										titre = $(this).children('img').attr('title');
										tab_ressources[id2] = (titre=='Absence de ressource.') ? '' : titre ;
										$(this).append('<br /><input name="f_lien" size="100" maxlength="256" type="text" value="'+tab_ressources[id2]+'" /><q class="voir" title="Tester ce lien."></q>'+bouton_interface_travail+'<q class="valider" title="Valider la modification de ce lien."></q><label>&nbsp;</label>');
									}
								);
							}
							$('label[for='+id+']').remove();
							afficher_masquer_images_action('show');
							infobulle();
						}
					}
				);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le bouton pour fermer la zone compet
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#fermer_zone_elaboration_referentiel').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				$('#zone_elaboration_referentiel').html("&nbsp;");
				afficher_masquer_images_action('show'); // au cas où on serait en train d'éditer qq chose
				$('#zone_choix_referentiel').show('fast');
				return(false);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur l'image pour voir la page correspondant au lien
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#zone_elaboration_referentiel q.voir').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				item_id   = $(this).parent().attr('id').substring(3);
				item_lien = $('#n3_'+item_id).children('input').val();
				if(!item_lien)
				{
					$('#n3_'+item_id).children('label').removeAttr("class").addClass("erreur").html("Adresse absente !");
					return false;
				}
				if(!testURL(item_lien))
				{
					$('#n3_'+item_id).children('label').removeAttr("class").addClass("erreur").html("Adresse incorrecte !");
					return false;
				}
				window.open(item_lien);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Réagir au changement dans un formulaire de lien associé à un item
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#zone_elaboration_referentiel input').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('change',
			function()
			{
				$(this).parent().children('label').removeAttr("class").addClass("alerte").html("Penser à valider !");
				return(false);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur l'image pour valider la modification du lien associé à un item
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#zone_elaboration_referentiel q.valider').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				item_id   = $(this).parent().attr('id').substring(3);
				item_lien = $('#n3_'+item_id).children('input').val();
				if(item_lien && !testURL(item_lien))
				{
					$('#n3_'+item_id).children('label').removeAttr("class").addClass("erreur").html("Adresse incorrecte !");
					return false;
				}
				// Envoi des infos en ajax pour le traitement de la demande
				$('#n3_'+item_id).children('label').removeAttr("class").addClass("loader").html('Demande envoyée...');
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'action=Enregistrer_lien&item_id='+item_id+'&item_lien='+encodeURIComponent(item_lien),
						dataType : "html",
						error : function(msg,string)
						{
							$('#n3_'+item_id).children('label').removeAttr("class").addClass("alerte").html('Echec de la connexion !');
						},
						success : function(responseHTML)
						{
							initialiser_compteur();
							if(responseHTML=='ok')	// Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
							{
								lien_image  = (item_lien=='') ? 'non' : 'oui' ;
								lien_title  = (item_lien=='') ? 'Absence de ressource.' : escapeHtml(item_lien) ;
								$('#n3_'+item_id).children('img').attr('src','./_img/etat/link_'+lien_image+'.png').attr('title',lien_title);
								tab_ressources[item_id] = (item_lien=='') ? '' : lien_title ;
								infobulle();
								$('#n3_'+item_id).children('label').removeAttr("class").addClass("valide").html('Lien enregistré.');
							}
							else
							{
								$('#n3_'+item_id).children('label').removeAttr("class").addClass("alerte").html(responseHTML);
							}
						}
					}
				);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur l'image afin d'élaborer ou d'éditer sur le serveur communautaire une page de liens pour travailler
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#zone_elaboration_referentiel q.ress_page_elaborer').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				item_id   = $(this).parent().attr('id').substring(3);
				item_lien = $('#n3_'+item_id).children('input').val();
				item_nom  = $(this).parent().html(); // text ne convient pas car on récupère le contenu du label avec...
				pos_debut = item_nom.indexOf('-')+2;
				pos_fin   = item_nom.indexOf('<br');
				item_nom  = item_nom.substring(pos_debut,pos_fin);
				// reporter le nom de l'item
				$('#zone_ressources span.f_nom').html(item_nom);
				// appel ajax
				$.fancybox( '<label class="loader">'+'Demande envoyée...'+'</label>' , {'centerOnScroll':true} );
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'action=Charger_ressources'+'&item_id='+item_id+'&item_lien='+encodeURIComponent(item_lien),
						dataType : "html",
						error : function(msg,string)
						{
							$.fancybox( '<label class="alerte">'+'Echec de la connexion !'+'</label>' , {'centerOnScroll':true} );
							return false;
						},
						success : function(responseHTML)
						{
							if(responseHTML.substring(0,3)!='<li')
							{
								$.fancybox( '<label class="alerte">'+responseHTML+'</label>' , {'centerOnScroll':true} );
								return false;
							}
							else
							{
								initialiser_compteur();
								// mode page_create | page_update
								var mode = (responseHTML.substring(0,14)=='<li class="i">') ? 'page_create' : 'page_update' ;
								$('#page_mode').val(mode);
								// ajouter les boutons
								var reg = new RegExp('</span>',"g"); // Si on ne prend pas une expression régulière alors replace() ne remplace que la 1e occurence
								responseHTML = responseHTML.replace(reg,'</span>'+images[1]);
								var reg = new RegExp('</a>',"g"); // Si on ne prend pas une expression régulière alors replace() ne remplace que la 1e occurence
								responseHTML = responseHTML.replace(reg,'</a>'+images[2]);
								// montrer le cadre
								$('#sortable').html(responseHTML);
								$('#zone_resultat_recherche_liens').html('');
								format_liens('#sortable');
								infobulle();
								$('#zone_ressources q').show();
								$('#ajax_ressources_msg').removeAttr("class").html("&nbsp;");
								$.fancybox( { 'href':'#zone_ressources' , onStart:function(){$('#zone_ressources').css("display","block");} , onClosed:function(){$('#zone_ressources').css("display","none");} , 'modal':true , 'centerOnScroll':true } );
								$('#sortable').sortable( { cursor:'n-resize' } );
							}
						}
					}
				);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le bouton pour Annuler la page de liens pour travailler
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#choisir_ressources_annuler').click
		(
			function()
			{
				$('label[for=paragraphe_nom]').removeAttr("class").html('');
				$('label[for=lien_url]').removeAttr("class").html('');
				$('label[for=lien_nom]').removeAttr("class").html('');
				$.fancybox.close();
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le bouton pour supprimer un élément d'une page de liens pour travailler
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#sortable q.supprimer').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				var nb_li = $(this).parent().parent().children().length;
				$(this).parent().remove();
				if(nb_li==1)
				{
					$('#sortable').append('<li class="i">Encore aucun élément actuellement ! Utilisez les outils ci-dessous pour en ajouter&hellip;</li>');
				}
				initialiser_compteur();
				return false;
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur un bouton pour modifier un élément d'une page de liens pour travailler
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#sortable q.modifier').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				var element = $(this).prev();
				// soit c'est un sous-titre de paragraphe
				if(element.is('span'))
				{
					var paragraphe_nom = element.html();
					$(this).parent().html('<label class="tab">Sous-titre :</label><input name="paragraphe_nom" value="'+paragraphe_nom+'" size="75" maxlength="256" /><input name="paragraphe_nom_old" value="'+paragraphe_nom+'" type="hidden" /><q class="valider" title="Valider les modifications"></q><q class="annuler" title="Annuler les modifications"></q><label></label>');
				}
				// soit c'est un lien
				else if(element.is('a'))
				{
					var lien_url = element.attr('href');
					var lien_nom = element.html();
					$(this).parent().html('<label class="tab">Adresse :</label><input name="lien_url" value="'+lien_url+'" size="75" maxlength="256" /><input name="lien_url_old" value="'+lien_url+'" type="hidden" /><br /><label class="tab">Intitulé :</label><input name="lien_nom" value="'+lien_nom+'" size="75" maxlength="256" /><input name="lien_nom_old" value="'+lien_nom+'" type="hidden" /><q class="valider" title="Valider les modifications"></q><q class="annuler" title="Annuler les modifications"></q><label></label>');
				}
				infobulle();
				return false;
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur un bouton pour dupliquer un élément d'une page de liens pour travailler
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#sortable q.dupliquer').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				var element = $(this).prev().prev();
				// soit c'est un sous-titre de paragraphe
				if(element.is('span'))
				{
					var paragraphe_nom = element.html();
					$('#paragraphe_nom').val(paragraphe_nom).focus();
				}
				// soit c'est un lien
				else if(element.is('a'))
				{
					var lien_url = element.attr('href');
					var lien_nom = element.html();
					$('#lien_url').val(lien_url);
					$('#lien_nom').val(lien_nom).focus();
				}
				return false;
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur un bouton pour annuler la modification d'un élément d'une page de liens pour travailler
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#sortable q.annuler').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				var nb_input = $(this).parent().children('input').length;
				// soit c'est un sous-titre de paragraphe
				if(nb_input==2)
				{
					var paragraphe_nom = escapeHtml( $(this).parent().children('input[name=paragraphe_nom_old]').val() );
					$(this).parent().html('<span class="b">'+paragraphe_nom+'</span>'+images[1]);
				}
				// soit c'est un lien
				else if(nb_input==4)
				{
					var lien_url = escapeHtml( $(this).parent().children('input[name=lien_url_old]').val() );
					var lien_nom = escapeHtml( $(this).parent().children('input[name=lien_nom_old]').val() );
					$(this).parent().html('<a href="'+lien_url+'" title="'+lien_url+'" class="lien_ext">'+lien_nom+'</a>'+images[2]);
					format_liens('#sortable');
				}
				infobulle();
				return false;
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur un bouton pour valider la modification d'un élément d'une page de liens pour travailler
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#sortable q.valider').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				var nb_input = $(this).parent().children('input').length;
				// soit c'est un sous-titre de paragraphe
				if(nb_input==2)
				{
					var paragraphe_nom = escapeHtml( entity_convert( $(this).parent().children('input[name=paragraphe_nom]').val() ) );
					if(paragraphe_nom == '')
					{
						$(this).next().next('label').addClass("erreur").html("Nom manquant !");
						$(this).parent().children('input[name=paragraphe_nom]').focus();
						return false;
					}
					else
					{
						$(this).parent().html('<span class="b">'+paragraphe_nom+'</span>'+images[1]+'</q>');
					}
				}
				// soit c'est un lien
				else if(nb_input==4)
				{
					var lien_url = escapeHtml( entity_convert( $(this).parent().children('input[name=lien_url]').val() ) );
					var lien_nom = escapeHtml( entity_convert( $(this).parent().children('input[name=lien_nom]').val() ) );
					if(lien_url == '')
					{
						$(this).next().next('label').addClass("erreur").html("Adresse manquante !");
						$(this).parent().children('input[name=lien_url]').focus();
						return false;
					}
					else if(!testURL(lien_url))
					{
						$(this).next().next('label').addClass("erreur").html("Adresse incorrecte !");
						$(this).parent().children('input[name=lien_url]').focus();
						return false;
					}
					$(this).next().next('label').removeAttr("class").html("");
					if(lien_nom == '')
					{
						$(this).next().next('label').addClass("erreur").html("Nom manquant !");
						$(this).parent().children('input[name=lien_nom]').focus();
						return false;
					}
					else
					{
						$(this).parent().html('<a href="'+lien_url+'" title="'+lien_url+'" class="lien_ext">'+lien_nom+'</a>'+images[2]+'</q>');
						format_liens('#sortable');
					}
				}
				initialiser_compteur();
				infobulle();
				return false;
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le bouton pour ajouter un sous-titre de paragraphe dans une page de liens pour travailler
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#paragraphe_ajouter').click
		(
			function()
			{
				var paragraphe_nom = escapeHtml( entity_convert( $('#paragraphe_nom').val() ) );
				if(paragraphe_nom == '')
				{
					$('label[for=paragraphe_nom]').addClass("erreur").html("Nom manquant !");
					$('#paragraphe_nom').focus();
					return false;
				}
				else
				{
					initialiser_compteur();
					$('label[for=paragraphe_nom]').removeAttr("class").html('');
					$('#sortable').append('<li><span class="b">'+paragraphe_nom+'</span>'+images[1]+'</li>');
					infobulle();
					$('#sortable li.i').remove();
					$('#paragraphe_nom').val('');
				}
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le bouton pour ajouter une ressource dans une page de liens pour travailler
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#lien_ajouter').click
		(
			function()
			{
				// vérif lien_url
				var lien_url = escapeHtml( $('#lien_url').val() );
				if(lien_url == '')
				{
					$('label[for=lien_url]').addClass("erreur").html("Adresse manquante !");
					$('#lien_url').focus();
					return false;
				}
				else if(!testURL(lien_url))
				{
					$('label[for=lien_url]').addClass("erreur").html("Adresse incorrecte !");
					$('#lien_url').focus();
					return false;
				}
				$('label[for=lien_url]').removeAttr("class").html("");
				// vérif lien_nom
				var lien_nom = escapeHtml( entity_convert( $('#lien_nom').val() ) );
				if(lien_nom == '')
				{
					$('label[for=lien_nom]').addClass("erreur").html("Nom manquant !");
					$('#lien_nom').focus();
					return false;
				}
				$('label[for=lien_nom]').removeAttr("class").html('');
				// ok
				initialiser_compteur();
				$('#sortable').append('<li><a href="'+lien_url+'" title="'+lien_url+'" class="lien_ext">'+lien_nom+'</a>'+images[2]+'</li>');
				infobulle();
				$('#sortable li.i').remove();
				format_liens('#sortable');
				$('#lien_url').val('');
				$('#lien_nom').val('');
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le bouton pour valider et enregistrer le contenu d'une page de liens pour travailler
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#choisir_ressources_valider').click
		(
			function()
			{
				if($('#sortable li.i').length)
				{
					$('#ajax_ressources_msg').removeAttr("class").addClass("erreur").html("La liste de ressources est vide !");
					return false;
				}
				// Récupérer les éléments
				var tab_ressources = new Array();
				var modif_en_cours = false;
				var nb_ressources = 0;
				$('#sortable li').each
				(
					function()
					{
						// soit c'est un sous-titre de paragraphe
						if($(this).children('span').length)
						{
							var paragraphe_nom = $(this).children('span').html();
							tab_ressources.push(paragraphe_nom);
						}
						// soit c'est un lien
						else if($(this).children('a').length)
						{
							var lien_url = $(this).children('a').attr('href');
							var lien_nom = $(this).children('a').html();
							tab_ressources.push(lien_nom+']¤['+lien_url);
							nb_ressources++;
						}
						// soit une modification d'un élément est en cours
						else
						{
							modif_en_cours = true;
							return false;
						}
					}
				);
				if(modif_en_cours)
				{
					$('#ajax_ressources_msg').removeAttr("class").addClass("erreur").html("Valider ou annuler d'abord toute modification en cours !");
					return false;
				}
				if(!nb_ressources)
				{
					$('#ajax_ressources_msg').removeAttr("class").addClass("erreur").html("Aucun lien trouvé vers une ressource !");
					return false;
				}
				// appel ajax
				$('#ajax_ressources_msg').removeAttr("class").addClass("loader").html("Demande envoyée...");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'action=Enregistrer_ressources'+'&item_id='+item_id+'&item_nom='+encodeURIComponent(item_nom)+'&page_mode='+$('#page_mode').val()+'&ressources='+encodeURIComponent(tab_ressources.join('}¤{')),
						dataType : "html",
						error : function(msg,string)
						{
							$('#ajax_ressources_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion !");
							return false;
						},
						success : function(responseHTML)
						{
							if(!testURL(responseHTML))
							{
								$('#ajax_ressources_msg').removeAttr("class").addClass("alerte").html(responseHTML);
								return false;
							}
							else
							{
								$('label[for=paragraphe_nom]').removeAttr("class").html('');
								$('label[for=lien_url]').removeAttr("class").html('');
								$('label[for=lien_nom]').removeAttr("class").html('');
								$('#ajax_ressources_msg').removeAttr("class").html("&nbsp;");
								$.fancybox.close();
								initialiser_compteur();
								$('#n3_'+item_id).children('input').val(responseHTML);
								$('#n3_'+item_id).children('q.valider').click();
							}
						}
					}
				);
				return false;
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le bouton pour rechercher des liens existants à partir de mots clefs
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#liens_rechercher').click
		(
			function()
			{
				var findme = $('#chaine_recherche').val();
				if(findme=='')
				{
					$('#zone_resultat_recherche_liens').html('<label class="erreur">Saisir des mots clefs !</label>');
					$('#chaine_recherche').focus();
					return false;
				}
				// appel ajax
				$('#zone_resultat_recherche_liens').html('<label class="loader">Demande envoyée...</label>');
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'action=Rechercher_liens_ressources'+'&item_id='+item_id+'&findme='+encodeURIComponent(findme),
						dataType : "html",
						error : function(msg,string)
						{
							$('#zone_resultat_recherche_liens').html('<label class="erreur">Echec de la connexion !</label>');
							return false;
						},
						success : function(responseHTML)
						{
							if(responseHTML.substring(0,3)!='<li')
							{
								$('#zone_resultat_recherche_liens').html('<label class="alerte">'+responseHTML+'</label>');
								return false;
							}
							else
							{
								var reg = new RegExp('</a>',"g"); // Si on ne prend pas une expression régulière alors replace() ne remplace que la 1e occurence
								responseHTML = responseHTML.replace(reg,'</a>'+images[3]);
								$('#zone_resultat_recherche_liens').html('<ul>'+responseHTML+'</ul>');
								format_liens('#zone_resultat_recherche_liens');
								initialiser_compteur();
								infobulle();
							}
						}
					}
				);
				return false;
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le bouton pour ajouter un lien trouvé suite à une recherche
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#zone_resultat_recherche_liens q.ajouter').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				var lien_url = $(this).prev().attr('href');
				var lien_nom = $(this).prev().html();
				$(this).parent().remove();
				initialiser_compteur();
				$('#sortable').append('<li><a href="'+lien_url+'" title="'+lien_url+'" class="lien_ext">'+lien_nom+'</a>'+images[2]+'</li>');
				infobulle();
				$('#sortable li.i').remove();
				format_liens('#sortable');
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Uploader une ressource
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		// Envoi du fichier avec jquery.ajaxupload.js
		new AjaxUpload
		('#bouton_import',
			{
				action: 'ajax.php?page='+PAGE,
				name: 'userfile',
				data: {'action':'Uploader_document'},
				autoSubmit: true,
				responseType: "html",
				onChange: changer_fichier,
				onSubmit: verifier_fichier,
				onComplete: retourner_fichier
			}
		);

		function changer_fichier(fichier_nom,fichier_extension)
		{
			$('#ajax_ressources_upload').removeAttr("class").html('&nbsp;');
			$('#zone_ressources_upload button').prop('disabled',true);
			return true;
		}

		function verifier_fichier(fichier_nom,fichier_extension)
		{
			if (fichier_nom==null || fichier_nom.length<5)
			{
				$('#ajax_ressources_upload').removeAttr("class").addClass("erreur").html('Cliquer sur "Parcourir..." pour indiquer un chemin de fichier correct.');
				$('#zone_ressources_upload button').prop('disabled',false);
				return false;
			}
			else if ( ('.doc.docx.odg.odp.ods.odt.ppt.pptx.rtf.sxc.sxd.sxi.sxw.xls.xlsx.'.indexOf('.'+fichier_extension.toLowerCase()+'.')!=-1) && !confirm('Vous devriez convertir votre fichier au format PDF.\nEtes-vous certain de vouloir l\'envoyer sous ce format ?') )
			{
				$('#ajax_ressources_upload').removeAttr("class").addClass("erreur").html('Convertissez votre fichier en "pdf".');
				$('#zone_ressources_upload button').prop('disabled',false);
				return false;
			}
			else if ('.bat.com.exe.php.zip.'.indexOf('.'+fichier_extension.toLowerCase()+'.')!=-1)
			{
				$('#ajax_ressources_upload').removeAttr("class").addClass("erreur").html('Extension non autorisée.');
				$('#zone_ressources_upload button').prop('disabled',false);
				return false;
			}
			else
			{
				$('#zone_ressources_upload button').prop('disabled',true);
				$('#ajax_ressources_upload').removeAttr("class").addClass("loader").html('Fichier envoyé...');
				return true;
			}
		}

		function retourner_fichier(fichier_nom,responseHTML)	// Attention : avec jquery.ajaxupload.js, IE supprime mystérieusement les guillemets et met les éléments en majuscules dans responseHTML.
		{
			$('#zone_ressources_upload button').prop('disabled',false);
			if(responseHTML.substring(0,4)!='http')
			{
				$('#ajax_ressources_upload').removeAttr("class").addClass("alerte").html(responseHTML);
			}
			else
			{
				initialiser_compteur();
				upload_lien = responseHTML;
				$('#ajax_ressources_upload').removeAttr("class").html('');
				$('#afficher_zone_ressources_form').click();
				$('label[for=lien_url]').removeAttr("class").addClass("valide").html("Upload réussi !");
				$('label[for=lien_nom]').removeAttr("class").addClass("alerte").html("Validez l'ajout&hellip;");
				$('#lien_url').val(upload_lien);
				$('#lien_nom').focus();
			}
		}

		$('#acceptation_conditions').click
		(
			function()
			{
				if($(this).is(':checked'))
				{
					$('#bouton_import').prop('disabled',false);
				}
				else
				{
					$('#bouton_import').prop('disabled',true);
				}
			}
		);

		// A remettre en disabled sinon la propriété disparait au chargement du js...
		$('#bouton_import').prop('disabled',true);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le bouton pour rechercher des ressources existantes uploadées par l'établissement
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#ressources_rechercher').click
		(
			function()
			{
				// appel ajax
				$('#zone_resultat_recherche_ressources').html('<label class="loader">Demande envoyée...</label>');
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'action=Rechercher_documents',
						dataType : "html",
						error : function(msg,string)
						{
							$('#zone_resultat_recherche_ressources').html('<label class="erreur">Echec de la connexion !</label>');
							return false;
						},
						success : function(responseHTML)
						{
							if(responseHTML.substring(0,3)!='<li')
							{
								$('#zone_resultat_recherche_ressources').html('<label class="alerte">'+responseHTML+'</label>');
								return false;
							}
							else
							{
								var reg = new RegExp('</a>',"g"); // Si on ne prend pas une expression régulière alors replace() ne remplace que la 1e occurence
								responseHTML = responseHTML.replace(reg,'</a>'+images[4]);
								$('#zone_resultat_recherche_ressources').html('<ul>'+responseHTML+'</ul>');
								format_liens('#zone_resultat_recherche_ressources');
								initialiser_compteur();
								infobulle();
							}
						}
					}
				);
				return false;
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le bouton pour ajouter une ressource trouvée suite à une recherche
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#zone_resultat_recherche_ressources q.valider').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				var lien_url = $(this).prev().attr('href');
				$('#ajax_ressources_upload').removeAttr("class").html('');
				$('#afficher_zone_ressources_form').click();
				$('label[for=lien_url]').removeAttr("class").html("");
				$('label[for=lien_nom]').removeAttr("class").addClass("alerte").html("Validez l'ajout&hellip;");
				$('#lien_url').val(lien_url);
				$('#lien_nom').focus();
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Passer de zone_ressources_form à zone_ressources_upload et vice-versa ; report d'un lien vers une ressource.
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#afficher_zone_ressources_upload').click
		(
			function()
			{
				$('#ajax_ressources_upload').removeAttr("class").html('');
				$('#zone_ressources_form').hide();
				$('#zone_ressources_upload').show();
			}
		);

		$('#afficher_zone_ressources_form').click
		(
			function()
			{
				$('#zone_ressources_upload').hide();
				$('#zone_ressources_form').show();
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Intercepter la touche entrée ou escape pour valider ou annuler les modifications
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('input').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('keyup',
			function(e)
			{
				if(e.which==13)	// touche entrée
				{
					$(this).nextAll('q.valider , q.ajouter').click();
				}
				else if(e.which==27)	// touche escape
				{
					$(this).nextAll('q.annuler').click();
				}
				return false;
			}
		);

	}
);
