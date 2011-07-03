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

// jQuery !
$(document).ready
(
	function()
	{

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Initialisation
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		var mode = false;
		var please_wait = false;

		// tri du tableau (avec jquery.tablesorter.js).
		var sorting = [[3,0],[4,0]];
		$('table.bilan_synthese').tablesorter({ headers:{0:{sorter:false},1:{sorter:false},8:{sorter:false}} });
		function trier_tableau()
		{
			if($('table.bilan_synthese tbody tr').length)
			{
				$('table.bilan_synthese').trigger('update');
				$('table.bilan_synthese').trigger('sorton',[sorting]);
			}
		}
		trier_tableau();

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur une cellule (remplace un champ label, impossible à définir sur plusieurs colonnes)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('td.label').live
		('click',
			function()
			{
				$(this).parent().find("input[type=checkbox]").click();
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Fonctions utilisées
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		/**
		 * Ajouter un établissement : mise en place du formulaire
		 * @return void
		 */
		var ajouter = function()
		{
			mode = $(this).attr('class');
			// Fabriquer la ligne avec les éléments de formulaires
			afficher_masquer_images_action('hide');
			new_tr  = '<tr>';
			new_tr += '<td class="nu"></td>';
			new_tr += '<td class="nu"></td>';
			new_tr += '<td></td>';
			new_tr += '<td><select id="f_geo" name="f_geo">'+options_geo+'</select></td>';
			new_tr += '<td><input id="f_localisation" name="f_localisation" size="30" type="text" value="" />'+'<br />'+'<input id="f_denomination" name="f_denomination" size="30" type="text" value="" /></td>';
			new_tr += '<td><input id="f_uai" name="f_uai" size="8" type="text" value="" /></td>';
			new_tr += '<td><input id="f_contact_nom" name="f_contact_nom" size="15" type="text" value="" />'+'<br />'+'<input id="f_contact_prenom" name="f_contact_prenom" size="15" type="text" value="" /></td>';
			new_tr += '<td><input id="f_contact_courriel" name="f_contact_courriel" size="30" type="text" value="" />'+'<br />'+'<input id="f_courriel_envoi" name="f_courriel_envoi" type="checkbox" value="1" checked /><label for="f_courriel_envoi"> envoyer le courriel d\'inscription</label></td>';
			new_tr += '<td class="nu"><input id="f_action" name="f_action" type="hidden" value="'+mode+'" /><q class="valider" title="Valider l\'ajout de cet établissement."></q><q class="annuler" title="Annuler l\'ajout de cet établissement."></q> <label id="ajax_msg">&nbsp;</label></td>';
			new_tr += '</tr>';
			// Ajouter cette nouvelle ligne
			$(this).parent().parent().after(new_tr);
			infobulle();
			$('#f_etabl_id').focus();
		};

		/**
		 * Modifier un établissement : mise en place du formulaire
		 * @return void
		 */
		var modifier = function()
		{
			mode = $(this).attr('class');
			afficher_masquer_images_action('hide');
			// Récupérer les informations de la ligne concernée
			base_id          = $(this).parent().prev().prev().prev().prev().prev().prev().html();
			geo              = $(this).parent().prev().prev().prev().prev().prev().html();
			structure        = $(this).parent().prev().prev().prev().prev().html();
			uai              = $(this).parent().prev().prev().prev().html();
			contact          = $(this).parent().prev().prev().html();
			contact_courriel = $(this).parent().prev().html();
			// séparer localisation et denomination
			var reg = new RegExp('<br ?/?>',"g");	// Le navigateur semble transformer <br /> en <br> ...
			tab_infos        = structure.split(reg);
			localisation     = tab_infos[0];
			denomination     = tab_infos[1];
			// séparer contact_nom et contact_prenom
			tab_infos        = contact.split(reg);
			contact_nom      = tab_infos[0];
			contact_prenom   = tab_infos[1];
			// enlever l'indice de tri caché
			geo = geo.substring(9,geo.length); 
			// Fabriquer la ligne avec les éléments de formulaires
			new_tr  = '<tr>';
			new_tr += '<td class="nu"></td>';
			new_tr += '<td class="nu"></td>';
			new_tr += '<td>'+base_id+'<input id="f_base_id" name="f_base_id" type="hidden" value="'+base_id+'" /></td>';
			new_tr += '<td><select id="f_geo" name="f_geo">'+options_geo.replace('>'+geo+'<',' selected>'+geo+'<')+'</select></td>';
			new_tr += '<td><input id="f_localisation" name="f_localisation" size="'+Math.max(localisation.length,30)+'" type="text" value="'+localisation+'" />'+'<br />'+'<input id="f_denomination" name="f_denomination" size="'+Math.max(denomination.length,30)+'" type="text" value="'+denomination+'" /></td>';
			new_tr += '<td><input id="f_uai" name="f_uai" size="8" type="text" value="'+uai+'" /></td>';
			new_tr += '<td><input id="f_contact_nom" name="f_contact_nom" size="'+Math.max(contact_nom.length,15)+'" type="text" value="'+contact_nom+'" />'+'<br />'+'<input id="f_contact_prenom" name="f_contact_prenom" size="'+Math.max(contact_prenom.length,15)+'" type="text" value="'+contact_prenom+'" /></td>';
			new_tr += '<td><input id="f_contact_courriel" name="f_contact_courriel" size="'+Math.max(contact_courriel.length,30)+'" type="text" value="'+contact_courriel+'" /></td>';
			new_tr += '<td class="nu"><input id="f_action" name="f_action" type="hidden" value="'+mode+'" /><q class="valider" title="Valider les modifications de cet établissement."></q><q class="annuler" title="Annuler les modifications de cet établissement."></q> <label id="ajax_msg">&nbsp;</label></td>';
			new_tr += '</tr>';
			// Cacher la ligne en cours et ajouter la nouvelle
			$(this).parent().parent().hide();
			$(this).parent().parent().after(new_tr);
			infobulle();
			$('#f_etabl_id').focus();
		};

		/**
		 * Supprimer un établissement : mise en place du formulaire
		 * @return void
		 */
		var supprimer = function()
		{
			mode = $(this).attr('class');
			afficher_masquer_images_action('hide');
			base_id = $(this).parent().parent().attr('id').substring(3);
			new_span  = '<span class="danger"><input id="f_action" name="f_action" type="hidden" value="'+mode+'" /><input id="f_base_id" name="f_base_id" type="hidden" value="'+base_id+'" />Toute la base sera supprimée !<q class="valider" title="Confirmer la suppression de cet établissement."></q><q class="annuler" title="Annuler la suppression de cet établissement."></q> <label id="ajax_msg">&nbsp;</label></span>';
			$(this).after(new_span);
			infobulle();
		};

		/**
		 * Générer un nouveau mdp admin : mise en place du formulaire
		 * @return void
		 */
		var initialiser_mdp = function()
		{
			mode = $(this).attr('class');
			afficher_masquer_images_action('hide');
			base_id = $(this).parent().parent().attr('id').substring(3);
			new_span  = '<span id="init_form"><label id="ajax_msg" class="loader">Chargement en cours... Veuillez patienter.</label></span>';
			$(this).after(new_span);
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?page='+PAGE,
					data : 'f_action=lister_admin&f_base_id='+base_id,
					dataType : "html",
					error : function(msg,string)
					{
						$('#init_form').html('<label id="ajax_msg" class="alerte">Echec de la connexion !</label><q class="annuler" title="Annuler."></q>')
					},
					success : function(responseHTML)
					{
						initialiser_compteur();
						if(responseHTML.substring(0,7)=='<option')	// Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
						{
							$('#init_form').html('<input id="f_action" name="f_action" type="hidden" value="'+mode+'" /><select id="f_admin_id" name="f_admin_id"><option value="">administrateurs</option>'+responseHTML+'</select><input id="f_base_id" name="f_base_id" type="hidden" value="'+base_id+'" /><q class="valider" title="Confirmer l\'initialisation du mot de passe."></q><q class="annuler" title="Annuler l\'initialisation du mot de passe."></q> <label id="ajax_msg">&nbsp;</label>');
						}
						else
						{
							$('#init_form').html('<label id="ajax_msg" class="alerte">'+responseHTML+'</label><q class="annuler" title="Annuler."></q>')
						}
						infobulle();
					}
				}
			);
		};

		/**
		 * Annuler une action
		 * @return void
		 */
		var annuler = function()
		{
			$('#ajax_msg').removeAttr("class").html("&nbsp;");
			switch (mode)
			{
				case 'ajouter':
					$(this).parent().parent().remove();
					break;
				case 'modifier':
					$(this).parent().parent().remove();
					$("table.bilan_synthese tr").show(); // $(this).parent().parent().prev().show(); pose pb si tri du tableau entre temps
					break;
				case 'supprimer':
				case 'initialiser_mdp':
					$(this).parent().remove().html('<q class="modifier" title="Modifier cet établissement."></q><q class="initialiser_mdp" title="Générer un nouveau mdp d\'un admin."></q><q class="supprimer" title="Supprimer cet établissement."></q>');
					break;
			};
			afficher_masquer_images_action('show');
			mode = false;
		};

		/**
		 * Intercepter la touche entrée ou escape pour valider ou annuler les modifications
		 * @return void
		 */
		function intercepter(e)
		{
			if(e.which==13)	// touche entrée
			{
				$('q.valider').click();
			}
			else if(e.which==27)	// touche escape
			{
				$('q.annuler').click();
			}
		}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Appel des fonctions en fonction des événements ; live est utilisé pour prendre en compte les nouveaux éléments créés
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('q.ajouter').click( ajouter );
		$('q.modifier').live(        'click' , modifier );
		$('q.supprimer').live(       'click' , supprimer );
		$('q.initialiser_mdp').live( 'click' , initialiser_mdp );
		$('q.annuler').live(         'click' , annuler );
		$('q.valider').live(         'click' , function(){formulaire.submit();} );
		$('table.bilan_synthese input , table.bilan_synthese select').live( 'keyup' , function(e){intercepter(e);} );

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Éléments dynamiques du formulaire
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		// Tout cocher ou tout décocher
		$('#all_check').click
		(
			function()
			{
				$('#structures input[type=checkbox]').prop('checked',true);
				return false;
			}
		);
		$('#all_uncheck').click
		(
			function()
			{
				$('#structures input[type=checkbox]').prop('checked',false);
				return false;
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur un bouton pour bloquer ou débloquer une structure
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('img[class=bloquer] , img[class=debloquer]').live
		('click',
			function()
			{
				var objet   = $(this);
				var action  = $(this).attr('class');
				var base_id = $(this).parent().parent().next().next().html();
				var img_src = $(this).attr('src');
				$(this).removeAttr("class").attr('src','./_img/ajax/ajax_loader.gif');
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'f_action='+action+'&f_base_id='+base_id,
						dataType : "html",
						error : function(msg,string)
						{
							objet.addClass(action).attr('src',img_src);
							return false;
						},
						success : function(responseHTML)
						{
							initialiser_compteur();
							if(responseHTML.substring(0,4)!='<img')	// Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
							{
								objet.addClass(action).attr('src',img_src);
							}
							else
							{
								objet.parent().html(responseHTML);
								infobulle();
							}
							return false;
						}
					}
				);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur un bouton pour effectuer une action sur les structures cochées
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		var supprimer_structures_cochees = function(listing_id)
		{
			$("button").prop('disabled',true);
			afficher_masquer_images_action('hide');
			$('#ajax_supprimer').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?page='+PAGE,
					data : 'f_action=supprimer&f_listing_id='+listing_id,
					dataType : "html",
					error : function(msg,string)
					{
						$('#ajax_supprimer').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez recommencer.");
						$("button").prop('disabled',false);
						afficher_masquer_images_action('show');
					},
					success : function(responseHTML)
					{
						initialiser_compteur();
						if(responseHTML!='<ok>')	// Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
						{
							$('#ajax_supprimer').removeAttr("class").addClass("alerte").html(responseHTML);
						}
						else
						{
							$("input[type=checkbox]:checked").each
							(
								function()
								{
									$(this).parent().parent().remove();
								}
							);
							$('#ajax_supprimer').removeAttr("class").html('&nbsp;');
							$("button").prop('disabled',false);
							afficher_masquer_images_action('show');
						}
					}
				}
			);
		};

		$('#zone_actions button').click
		(
			function()
			{
				var listing_id = new Array(); $("input[type=checkbox]:checked").each(function(){listing_id.push($(this).val());});
				if(!listing_id.length)
				{
					$('#ajax_supprimer').removeAttr("class").addClass("erreur").html("Aucune structure cochée !");
					return false;
				}
				$('#ajax_supprimer').removeAttr("class").html('&nbsp;');
				var id = $(this).attr('id');
				if(id=='bouton_supprimer')
				{
					if(confirm("Toutes les bases des structures cochées seront supprimées !\nConfirmez-vous vouloir effacer les données de ces structures ?"))
					{
						supprimer_structures_cochees(listing_id);
					}
				}
				else
				{
					$('#listing_ids').val(listing_id);
					var tab = new Array;
					tab['bouton_newsletter'] = "webmestre_newsletter";
					tab['bouton_stats']      = "webmestre_statistiques";
					tab['bouton_transfert']  = "webmestre_structure_transfert";
					var page = tab[id];
					var form = document.getElementById('structures');
					form.action = './index.php?page='+page;
					form.method = 'post';
					// form.target = '_blank';
					form.submit();
				}
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Traitement du formulaire
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		// Le formulaire qui va être analysé et traité en AJAX
		var formulaire = $("#structures");

		// Ajout d'une méthode pour vérifier le format du numéro UAI
		jQuery.validator.addMethod
		(
			"uai_format", function(value, element)
			{
				var uai = value.toUpperCase();
				var uai_valide = true;
				if(uai.length!=8)
				{
					uai_valide = false;
				}
				else
				{
					var uai_fin = uai.substring(7,8);
					if((uai_fin<"A")||(uai_fin>"Z"))
					{
						uai_valide = false;
					}
					else
					{
						for(i=0;i<7;i++)
						{
							var t = uai.substring(i,i+1);
							if((t<"0")||(t>"9"))
							{
								uai_valide = false;
							}
						}
					}
				}
				return this.optional(element) || uai_valide ;
			}
			, "il faut 7 chiffres suivis d'une lettre"
		); 

		// Ajout d'une méthode pour vérifier la clef de contrôle du numéro UAI
		jQuery.validator.addMethod
		(
			"uai_clef", function(value, element)
			{
				var uai = value.toUpperCase();
				var uai_valide = true;
				var uai_nombre = uai.substring(0,7);
				var uai_fin = uai.substring(7,8);
				alphabet = "ABCDEFGHJKLMNPRSTUVWXYZ";
				reste = uai_nombre-(23*Math.floor(uai_nombre/23));
				clef = alphabet.substring(reste,reste+1);;
				if(clef!=uai_fin )
				{
					uai_valide = false;
				}
				return this.optional(element) || uai_valide ;
			}
			, "clef de contrôle incompatible"
		); 

		// Vérifier la validité du formulaire (avec jquery.validate.js)
		var validation = formulaire.validate
		(
			{
				rules :
				{
					f_geo              : { required:true },
					f_localisation     : { required:true , maxlength:100 },
					f_denomination     : { required:true , maxlength:50 },
					f_uai              : { required:false , uai_format:true , uai_clef:true },
					f_contact_nom      : { required:true , maxlength:20 },
					f_contact_prenom   : { required:true , maxlength:20 },
					f_contact_courriel : { required:true , email:true , maxlength:60 },
					f_courriel_envoi   : { required:false },
					f_admin_id         : { required:true }
				},
				messages :
				{
					f_geo              : { required:"zone manquante" },
					f_localisation     : { required:"localisation manquante" , maxlength:"100 caractères maximum" },
					f_denomination     : { required:"dénomination manquante" , maxlength:"50 caractères maximum" },
					f_uai              : { uai_format:"n°UAI invalide" , uai_clef:"n°UAI invalide" },
					f_contact_nom      : { required:"nom manquant" , maxlength:"20 caractères maximum" },
					f_contact_prenom   : { required:"prénom manquant" , maxlength:"20 caractères maximum" },
					f_contact_courriel : { required:"courriel manquant" , email:"courriel invalide", maxlength:"60 caractères maximum" },
					f_courriel_envoi   : { },
					f_admin_id         : { required:"admin manquant" }
				},
				errorElement : "label",
				errorClass : "erreur",
				errorPlacement : function(error,element) { $('#ajax_msg').after(error); }
			}
		);

		// Options d'envoi du formulaire (avec jquery.form.js)
		var ajaxOptions =
		{
			url : 'ajax.php?page='+PAGE,
			type : 'POST',
			dataType : "html",
			clearForm : false,
			resetForm : false,
			target : "#ajax_msg",
			beforeSubmit : test_form_avant_envoi,
			error : retour_form_erreur,
			success : retour_form_valide
		};

		// Envoi du formulaire (avec jquery.form.js)
		formulaire.submit
		(
			function()
			{
				if (!please_wait)
				{
					$(this).ajaxSubmit(ajaxOptions);
					return false;
				}
				else
				{
					return false;
				}
			}
		); 

		// Fonction précédent l'envoi du formulaire (avec jquery.form.js)
		function test_form_avant_envoi(formData, jqForm, options)
		{
			$('#ajax_msg').removeAttr("class").html("&nbsp;");
			if( (mode!='ajouter') || ($('#f_courriel_envoi').is(':checked')) || confirm("Le mot de passe du premier administrateur, non récupérable ultérieurement, ne sera pas transmis !\nConfirmez-vous ne pas vouloir envoyer le courriel d'inscription ?") )
			{
				var readytogo = validation.form();
			}
			else
			{
				var readytogo = false;
			}
			if(readytogo)
			{
				please_wait = true;
				$('#ajax_msg').parent().children('q').hide();
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
			}
			return readytogo;
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_erreur(msg,string)
		{
			please_wait = false;
			$('#ajax_msg').parent().children('q').show();
			$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez recommencer.");
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_valide(responseHTML)
		{
			initialiser_compteur();
			please_wait = false;
			$('#ajax_msg').parent().children('q').show();
			if(responseHTML.substring(0,1)!='<')
			{
				$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
			}
			else
			{
				$('#ajax_msg').removeAttr("class").addClass("valide").html("Demande réalisée !");
				action = $('#f_action').val();
				switch (action)
				{
					case 'ajouter':
						$('table.bilan_synthese tbody').append(responseHTML);
						$('q.valider').parent().parent().remove();
						break;
					case 'modifier':
						$('q.valider').parent().parent().prev().addClass("new").html(responseHTML).show();
						$('q.valider').parent().parent().remove();
						break;
					case 'initialiser_mdp':
						$('q.valider').parent().remove();
						var reg = new RegExp('<BR />',"g");	// Si on transmet les retours à la ligne en ajax alors ils se font pas...
						var message = responseHTML.replace(reg,'\n').substring(4);
						alert( message );
						break;
					case 'supprimer':
						$('q.valider').parent().parent().parent().remove();
						break;
				}
				afficher_masquer_images_action('show');
				infobulle();
			}
		} 

	}
);
