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
		var sorting = [[1,0],[2,0],[3,0]];
		$('table.bilan_synthese').tablesorter({ headers:{8:{sorter:false}} });
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
			new_tr += '<td></td>';
			new_tr += '<td><select id="f_geo" name="f_geo">'+options_geo+'</select></td>';
			new_tr += '<td><input id="f_localisation" name="f_localisation" size="40" type="text" value="" /></td>';
			new_tr += '<td><input id="f_denomination" name="f_denomination" size="30" type="text" value="" /></td>';
			new_tr += '<td><input id="f_uai" name="f_uai" size="8" type="text" value="" /></td>';
			new_tr += '<td><input id="f_contact_nom" name="f_contact_nom" size="15" type="text" value="" /></td>';
			new_tr += '<td><input id="f_contact_prenom" name="f_contact_prenom" size="15" type="text" value="" /></td>';
			new_tr += '<td><input id="f_contact_courriel" name="f_contact_courriel" size="30" type="text" value="" /></td>';
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
			base_id          = $(this).parent().prev().prev().prev().prev().prev().prev().prev().prev().html();
			geo              = $(this).parent().prev().prev().prev().prev().prev().prev().prev().html();
			localisation     = $(this).parent().prev().prev().prev().prev().prev().prev().html();
			denomination     = $(this).parent().prev().prev().prev().prev().prev().html();
			uai              = $(this).parent().prev().prev().prev().prev().html();
			contact_nom      = $(this).parent().prev().prev().prev().html();
			contact_prenom   = $(this).parent().prev().prev().html();
			contact_courriel = $(this).parent().prev().html();
			geo = geo.substring(9,geo.length); // enlever l'indice de tri caché
			// Fabriquer la ligne avec les éléments de formulaires
			new_tr  = '<tr>';
			new_tr += '<td>'+base_id+'<input id="f_base_id" name="f_base_id" type="hidden" value="'+base_id+'" /></td>';
			new_tr += '<td><select id="f_geo" name="f_geo">'+options_geo.replace('>'+geo+'<',' selected="selected">'+geo+'<')+'</select></td>';
			new_tr += '<td><input id="f_localisation" name="f_localisation" size="'+Math.max(localisation.length,30)+'" type="text" value="'+localisation+'" /></td>';
			new_tr += '<td><input id="f_denomination" name="f_denomination" size="'+Math.max(denomination.length,20)+'" type="text" value="'+denomination+'" /></td>';
			new_tr += '<td><input id="f_uai" name="f_uai" size="8" type="text" value="'+uai+'" /></td>';
			new_tr += '<td><input id="f_contact_nom" name="f_contact_nom" size="'+Math.max(contact_nom.length,10)+'" type="text" value="'+contact_nom+'" /></td>';
			new_tr += '<td><input id="f_contact_prenom" name="f_contact_prenom" size="'+Math.max(contact_prenom.length,10)+'" type="text" value="'+contact_prenom+'" /></td>';
			new_tr += '<td><input id="f_contact_courriel" name="f_contact_courriel" size="'+Math.max(contact_courriel.length,20)+'" type="text" value="'+contact_courriel+'" /></td>';
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
					$(this).parent().remove();
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
		$('q.modifier').live(  'click' , modifier );
		$('q.supprimer').live( 'click' , supprimer );
		$('q.annuler').live(   'click' , annuler );
		$('q.valider').live(   'click' , function(){formulaire.submit();} );
		$('table.bilan_synthese input , table.bilan_synthese select').live( 'keyup' , function(e){intercepter(e);} );

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Traitement du formulaire
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		// Le formulaire qui va être analysé et traité en AJAX
		var formulaire = $('form');

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
					f_geo :              { required:true },
					f_localisation :     { required:true , maxlength:100 },
					f_denomination :     { required:true , maxlength:50 },
					f_uai :              { required:false , uai_format:true , uai_clef:true },
					f_contact_nom :      { required:true , maxlength:20 },
					f_contact_prenom :   { required:true , maxlength:20 },
					f_contact_courriel : { required:true , email:true , maxlength:60 }
				},
				messages :
				{
					f_geo :              { required:"zone manquante" },
					f_localisation :     { required:"localisation manquante" , maxlength:"100 caractères maximum" },
					f_denomination :     { required:"dénomination manquante" , maxlength:"50 caractères maximum" },
					f_uai :              { uai_format:"n°UAI invalide" , uai_clef:"n°UAI invalide" },
					f_contact_nom :      { required:"nom manquant" , maxlength:"20 caractères maximum" },
					f_contact_prenom :   { required:"prénom manquant" , maxlength:"20 caractères maximum" },
					f_contact_courriel : { required:"courriel manquant" , email:"courriel invalide", maxlength:"60 caractères maximum" }
				},
				errorElement : "label",
				errorClass : "erreur",
				errorPlacement : function(error,element) { $('#ajax_msg').after(error); }
			}
		);

		// Options d'envoi du formulaire (avec jquery.form.js)
		var ajaxOptions =
		{
			url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
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
					please_wait = true;
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
			var readytogo = validation.form();
			if(readytogo)
			{
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
			maj_clock(1);
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
					case 'supprimer':
						$('q.valider').parent().parent().parent().remove();
						break;
				}
				trier_tableau();
				afficher_masquer_images_action('show');
				infobulle();
			}
		} 

	}
);
