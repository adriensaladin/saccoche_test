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
		var sorting = [[4,0],[5,0]];
		$('table.form').tablesorter({ headers:{7:{sorter:false},8:{sorter:false}} });
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
		 * Ajouter un directeur : mise en place du formulaire
		 * @return void
		 */
		var ajouter = function()
		{
			mode = $(this).attr('class');
			// Fabriquer la ligne avec les éléments de formulaires
			afficher_masquer_images_action('hide');
			new_tr  = '<tr>';
			new_tr += '<td><input id="f_id_ent" name="f_id_ent" size="10" type="text" value="" /><img alt="" src="./_img/bulle_aide.png" title="Uniquement en cas d\'identification via un ENT." /></td>';
			new_tr += '<td><input id="f_id_gepi" name="f_id_gepi" size="10" type="text" value="" /><img alt="" src="./_img/bulle_aide.png" title="Uniquement en cas d\'utilisation du logiciel GEPI." /></td>';
			new_tr += '<td><input id="f_sconet_id" name="f_sconet_id" size="5" type="text" value="" /><img alt="" src="./_img/bulle_aide.png" title="Champ de STS-Web INDIVIDU.ID (laisser vide si inconnu)." /></td>';
			new_tr += '<td><input id="f_reference" name="f_reference" size="10" type="text" value="" /><img alt="" src="./_img/bulle_aide.png" title="Sconet : champ inutilisé (laisser vide).<br />Tableur : référence dans l\'établissement." /></td>';
			new_tr += '<td><input id="f_nom" name="f_nom" size="15" type="text" value="" /></td>';
			new_tr += '<td><input id="f_prenom" name="f_prenom" size="15" type="text" value="" /></td>';
			new_tr += '<td class="i">forme "'+select_login+'"</td>';
			new_tr += '<td class="i">généré aléatoirement</td>';
			new_tr += '<td class="nu"><input id="f_action" name="f_action" type="hidden" value="'+mode+'" /><q class="valider" title="Valider l\'ajout de ce directeur."></q><q class="annuler" title="Annuler l\'ajout de ce directeur."></q> <label id="ajax_msg">&nbsp;</label></td>';
			new_tr += '</tr>';
			// Ajouter cette nouvelle ligne
			$(this).parent().parent().after(new_tr);
			infobulle();
			$('#f_nom').focus();
		};

		/**
		 * Modifier un directeur : mise en place du formulaire
		 * @return void
		 */
		var modifier = function()
		{
			mode = $(this).attr('class');
			afficher_masquer_images_action('hide');
			// Récupérer les informations de la ligne concernée
			id         = $(this).parent().parent().attr('id').substring(3);
			id_ent     = $(this).parent().prev().prev().prev().prev().prev().prev().prev().prev().html();
			id_gepi    = $(this).parent().prev().prev().prev().prev().prev().prev().prev().html();
			sconet_id  = $(this).parent().prev().prev().prev().prev().prev().prev().html();
			reference  = $(this).parent().prev().prev().prev().prev().prev().html();
			nom        = $(this).parent().prev().prev().prev().prev().html();
			prenom     = $(this).parent().prev().prev().prev().html();
			login      = $(this).parent().prev().prev().html();
			// Retirer une éventuelle balise image présente
			position_image = login.indexOf('<');
			if (position_image!=-1)
			{
				login = login.substring(0,position_image-1);
			}
			// Fabriquer la ligne avec les éléments de formulaires
			new_tr  = '<tr>';
			new_tr += '<td><input id="f_id_ent" name="f_id_ent" size="'+Math.max(id_ent.length,10)+'" type="text" value="'+id_ent+'" /><img alt="" src="./_img/bulle_aide.png" title="Uniquement en cas d\'identification via un ENT." /></td>';
			new_tr += '<td><input id="f_id_gepi" name="f_id_gepi" size="'+Math.max(id_gepi.length,10)+'" type="text" value="'+id_gepi+'" /><img alt="" src="./_img/bulle_aide.png" title="Uniquement en cas d\'utilisation du logiciel GEPI." /></td>';
			new_tr += '<td><input id="f_sconet_id" name="f_sconet_id" size="5" type="text" value="'+sconet_id+'" /><img alt="" src="./_img/bulle_aide.png" title="Champ de STS-Web INDIVIDU.ID (laisser à 0 si inconnu)." /></td>';
			new_tr += '<td><input id="f_reference" name="f_reference" size="10" type="text" value="'+reference+'" /><img alt="" src="./_img/bulle_aide.png" title="Sconet : champ inutilisé (laisser vide).<br />Tableur : référence dans l\'établissement." /></td>';
			new_tr += '<td><input id="f_nom" name="f_nom" size="'+Math.max(nom.length,5)+'" type="text" value="'+nom+'" /></td>';
			new_tr += '<td><input id="f_prenom" name="f_prenom" size="'+Math.max(prenom.length,5)+'" type="text" value="'+prenom+'" /></td>';
			new_tr += '<td><input id="f_login" name="f_login" size="'+Math.max(login.length,10)+'" type="text" value="'+login+'" /></td>';
			new_tr += '<td><label for="f_password">générer un nouveau </label><input id="f_password" name="f_password" type="checkbox" value="'+id+'" /></td>';
			new_tr += '<td class="nu"><input id="f_action" name="f_action" type="hidden" value="'+mode+'" /><input id="f_id" name="f_id" type="hidden" value="'+id+'" /><q class="valider" title="Valider les modifications de ce directeur."></q><q class="annuler" title="Annuler les modifications de ce directeur."></q> <label id="ajax_msg">&nbsp;</label></td>';
			new_tr += '</tr>';
			// Cacher la ligne en cours et ajouter la nouvelle
			$(this).parent().parent().hide();
			$(this).parent().parent().after(new_tr);
			infobulle();
			$('#f_nom').focus();
		};

		/**
		 * Désactiver un directeur : mise en place du formulaire
		 * @return void
		 */
		var supprimer = function()
		{
			mode = $(this).attr('class');
			afficher_masquer_images_action('hide');
			id = $(this).parent().parent().attr('id').substring(3);
			new_span  = '<span class="astuce"><input id="f_action" name="f_action" type="hidden" value="'+mode+'" /><input id="f_id" name="f_id" type="hidden" value="'+id+'" />Le compte du directeur sera désactivé.<q class="valider" title="Confirmer le retrait de ce directeur."></q><q class="annuler" title="Annuler le retrait de ce directeur."></q> <label id="ajax_msg">&nbsp;</label></span>';
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
		$('q.modifier').live(   'click' , modifier );
		$('q.supprimer').live( 'click' , supprimer );
		$('q.annuler').live(    'click' , annuler );
		$('q.valider').live(    'click' , function(){formulaire.submit();} );
		$('table.form input , table.form select').live( 'keyup' , function(e){intercepter(e);} );

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
					f_id_ent     : { required:false , maxlength:32 },
					f_id_gepi    : { required:false , maxlength:32 },
					f_sconet_id  : { required:false , digits:true , max:16777215 },
					f_reference  : { required:false , maxlength:11 },
					f_nom        : { required:true , maxlength:25 },
					f_prenom     : { required:true , maxlength:25 },
					f_login      : { required:true , maxlength:20 },
					f_password   : { required:false }
				},
				messages :
				{
					f_id_ent     : { maxlength:"identifiant ENT de 32 caractères maximum" },
					f_id_gepi    : { maxlength:"identifiant Gepi de 32 caractères maximum" },
					f_sconet_id  : { digits:"Id Sconet : nombre entier inférieur à 2^24" },
					f_reference  : { maxlength:"référence de 11 caractères maximum" },
					f_nom        : { required:"nom manquant"    , maxlength:"25 caractères maximum" },
					f_prenom     : { required:"prénom manquant" , maxlength:"25 caractères maximum" },
					f_login      : { required:"login manquant"  , maxlength:"20 caractères maximum" },
					f_password   : { }
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
			initialiser_compteur();
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
