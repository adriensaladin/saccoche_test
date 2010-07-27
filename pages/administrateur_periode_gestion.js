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
		// tri du tableau (avec jquery.tablesorter.js).
		var sorting = [[0,0],[1,0]];
		$('table.form').tablesorter({ headers:{2:{sorter:false}} });
		function trier_tableau()
		{
			if($('table.form tbody tr').length)
			{
				$('table.form').trigger('update');
				$('table.form').trigger('sorton',[sorting]);
			}
		}
		trier_tableau();

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Fonctions utilisées
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		/**
		 * Ajouter une période : mise en place du formulaire
		 * @return void
		 */
		var ajouter = function()
		{
			mode = $(this).attr('class');
			// Fabriquer la ligne avec les éléments de formulaires
			afficher_masquer_images_action('hide');
			new_tr  = '<tr>';
			new_tr += '<td><input id="f_ordre" name="f_ordre" size="3" type="text" value="" /></td>';
			new_tr += '<td><input id="f_nom" name="f_nom" size="35" type="text" value="" /></td>';
			new_tr += '<td class="nu"><input id="f_action" name="f_action" type="hidden" value="'+mode+'" /><q class="valider" title="Valider l\'ajout de cette période."></q><q class="annuler" title="Annuler l\'ajout de cette période."></q> <label id="ajax_msg">&nbsp;</label></td>';
			new_tr += '</tr>';
			// Ajouter cette nouvelle ligne
			$(this).parent().parent().after(new_tr);
			infobulle();
			$('#f_ordre').focus();
		};

		/**
		 * Modifier une période : mise en place du formulaire
		 * @return void
		 */
		var modifier = function()
		{
			mode = $(this).attr('class');
			afficher_masquer_images_action('hide');
			// Récupérer les informations de la ligne concernée
			id         = $(this).parent().parent().attr('id').substring(3);
			ordre      = $(this).parent().prev().prev().html();
			nom        = $(this).parent().prev().html();
			// Fabriquer la ligne avec les éléments de formulaires
			new_tr  = '<tr>';
			new_tr += '<td><input id="f_ordre" name="f_ordre" size="3" type="text" value="'+ordre+'" /></td>';
			new_tr += '<td><input id="f_nom" name="f_nom" size="'+Math.max(nom.length,20)+'" type="text" value="'+nom+'" /></td>';
			new_tr += '<td class="nu"><input id="f_id" name="f_id" type="hidden" value="'+id+'" /><input id="f_action" name="f_action" type="hidden" value="'+mode+'" /><q class="valider" title="Valider les modifications de cette période."></q><q class="annuler" title="Annuler les modifications de cette période."></q> <label id="ajax_msg">&nbsp;</label></td>';
			new_tr += '</tr>';
			// Cacher la ligne en cours et ajouter la nouvelle
			$(this).parent().parent().hide();
			$(this).parent().parent().after(new_tr);
			infobulle();
			$('#f_ordre').focus();
		};

		/**
		 * Dupliquer une période : mise en place du formulaire
		 * @return void
		 */
		var dupliquer = function()
		{
			mode = $(this).attr('class');
			afficher_masquer_images_action('hide');
			// Récupérer les informations de la ligne concernée
			ordre      = $(this).parent().prev().prev().html();
			nom        = $(this).parent().prev().html();
			ordre++;
			// Fabriquer la ligne avec les éléments de formulaires
			new_tr  = '<tr>';
			new_tr += '<td><input id="f_ordre" name="f_ordre" size="3" type="text" value="'+ordre+'" /></td>';
			new_tr += '<td><input id="f_nom" name="f_nom" size="'+Math.max(nom.length,20)+'" type="text" value="'+nom+'" /></td>';
			new_tr += '<td class="nu"><input id="f_action" name="f_action" type="hidden" value="'+mode+'" /><q class="valider" title="Valider l\'ajout de cette période."></q><q class="annuler" title="Annuler l\'ajout de cette période."></q> <label id="ajax_msg">&nbsp;</label></td>';
			new_tr += '</tr>';
			// Ajouter cette nouvelle ligne
			$(this).parent().parent().after(new_tr);
			infobulle();
			$('#f_ordre').focus();
		};

		/**
		 * Supprimer une période : mise en place du formulaire
		 * @return void
		 */
		var supprimer = function()
		{
			mode = $(this).attr('class');
			afficher_masquer_images_action('hide');
			id = $(this).parent().parent().attr('id').substring(3);
			new_span  = '<span class="danger"><input id="f_action" name="f_action" type="hidden" value="'+mode+'" /><input id="f_id" name="f_id" type="hidden" value="'+id+'" />Etes-vous certain ?<q class="valider" title="Confirmer la suppression de cette période."></q><q class="annuler" title="Annuler la suppression de cette période."></q> <label id="ajax_msg">&nbsp;</label></span>';
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
				case 'dupliquer':
					$(this).parent().parent().remove();
					break;
				case 'modifier':
					$(this).parent().parent().remove();
					$("table.form tr").show(); // $(this).parent().parent().prev().show(); pose pb si tri du tableau entre temps
					break;
				case 'supprimer':
					$(this).parent().remove();
					break;
			}
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
		$('q.dupliquer').live( 'click' , dupliquer );
		$('q.supprimer').live( 'click' , supprimer );
		$('q.annuler').live(   'click' , annuler );
		$('q.valider').live(   'click' , function(){formulaire.submit();} );
		$('table.form input').live( 'keyup' , function(e){intercepter(e);} );

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Traitement du formulaire
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		// Le formulaire qui va être analysé et traité en AJAX
		var formulaire = $('form');

		// Vérifier la validité du formulaire (avec jquery.validate.js)
		var validation = formulaire.validate
		(
			{
				rules :
				{
					f_ordre : { required:true , digits:true , range:[1,99] },
					f_nom   : { required:true , maxlength:40 }
				},
				messages :
				{
					f_ordre : { required:"ordre manquant" , digits:"nombre entier requis" , range:"nombre entre 1 et 99" },
					f_nom   : { required:"nom manquant" , maxlength:"40 caractères maximum" }
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
			var readytogo = validation.form();
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
			maj_clock(1);
			please_wait = false;
			$('#ajax_msg').parent().children('q').show();
			if(responseHTML.substring(0,2)!='<t')
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
					case 'dupliquer':
						$('table.form tbody').append(responseHTML);
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
