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
		var id = 0;
		var nom_prenom = '';
		var td_resp = false;

		// tri du tableau (avec jquery.tablesorter.js).
		var sorting = [[1,0]];
		$('table.form').tablesorter({ headers:{6:{sorter:false}} });
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
		 * Modifier une adresse : mise en place du formulaire
		 * @return void
		 */
		var modifier = function()
		{
			reference = $(this).parent().parent().attr('id').substring(3);
			mode = (reference.substring(0,1)=='M') ? 'modifier' : 'ajouter' ;
			afficher_masquer_images_action('hide');
			// Récupérer les informations de la ligne concernée
			id          = reference.substring(1,reference.length);
			td_resp     = $(this).parent().prev().prev().prev().prev().prev().prev();
			nom_prenom  = $(this).parent().prev().prev().prev().prev().prev().html();
			obj_lignes  = $(this).parent().prev().prev().prev().prev();
			code_postal = $(this).parent().prev().prev().prev().html();
			commune     = $(this).parent().prev().prev().html();
			pays        = $(this).parent().prev().html();
			// Fabriquer la ligne avec les éléments de formulaires
			new_tr  = '<tr>';
			new_tr += '<td></td>';
			new_tr += '<td>'+nom_prenom+'</td>';
			new_tr += '<td>';
			i=1;
			obj_lignes.children('span').each
			(
				function()
				{
					ligne = $(this).html();
					new_tr += '<input id="f_ligne'+i+'" name="f_ligne'+i+'" size="'+Math.max(ligne.length,10)+'" type="text" value="'+ligne+'" />';
					i++;
				}
			);
			new_tr += '</td>';
			new_tr += '<td><input id="f_code_postal" name="f_code_postal" size="5" type="text" value="'+code_postal+'" /></td>';
			new_tr += '<td><input id="f_commune" name="f_commune" size="'+Math.max(commune.length,10)+'" type="text" value="'+commune+'" /></td>';
			new_tr += '<td><input id="f_pays" name="f_pays" size="'+Math.max(pays.length,5)+'" type="text" value="'+pays+'" /></td>';
			new_tr += '<td class="nu"><input id="f_action" name="f_action" type="hidden" value="'+mode+'" /><input id="f_id" name="f_id" type="hidden" value="'+id+'" /><q class="valider" title="Valider les modifications de cette adresse."></q><q class="annuler" title="Annuler les modifications de cette adresse."></q> <label id="ajax_msg">&nbsp;</label></td>';
			new_tr += '</tr>';
			// Cacher la ligne en cours et ajouter la nouvelle
			$(this).parent().parent().hide();
			$(this).parent().parent().after(new_tr);
			infobulle();
			$('#f_ligne1').focus();
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
				case 'modifier':
				case 'ajouter':
					$(this).parent().parent().remove();
					$("table.form tr").show(); // $(this).parent().parent().prev().show(); pose pb si tri du tableau entre temps
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

		$('q.modifier').live(   'click' , modifier );
		$('q.annuler').live(    'click' , annuler );
		$('q.valider').live(    'click' , function(){formulaire.submit();} );
		$('table.form input , table.form select').live( 'keyup' , function(e){intercepter(e);} );

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Traitement du formulaire
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		// Le formulaire qui va être analysé et traité en AJAX
		var formulaire = $('#form1');

		// Vérifier la validité du formulaire (avec jquery.validate.js)
		var validation = formulaire.validate
		(
			{
				rules :
				{
					f_ligne1      : { required:false , maxlength:50 },
					f_ligne2      : { required:false , maxlength:50 },
					f_ligne3      : { required:false , maxlength:50 },
					f_ligne4      : { required:false , maxlength:50 },
					f_code_postal : { required:false , digits:true , max:999999 },
					f_commune     : { required:false , maxlength:45 },
					f_pays        : { required:false , maxlength:35 }
				},
				messages :
				{
					f_ligne1      : { maxlength:"50 caractères maxi par élément d'adresse" },
					f_ligne2      : { maxlength:"50 caractères maxi par élément d'adresse" },
					f_ligne3      : { maxlength:"50 caractères maxi par élément d'adresse" },
					f_ligne4      : { maxlength:"50 caractères maxi par élément d'adresse" },
					f_code_postal : { digits:"CP : nombre entier" },
					f_commune     : { maxlength:"Commune : 45 caractères maximum" },
					f_pays        : { maxlength:"Pays : 35 caractères maximum" }
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
					case 'modifier':
						$('#temp_td').html(td_resp); // Pour ne pas perdre l'objet avec l'infobulle, on est obligé de le copier ailleurs avant le html qui suit.
						$('q.valider').parent().parent().prev().addClass("new").html('<td>'+nom_prenom+'</td>'+responseHTML).prepend( td_resp ).show();
						$('q.valider').parent().parent().remove();
						break;
					case 'ajouter':
						$('q.valider').parent().parent().prev().addClass("new").attr('id','id_M'+id).html('<td>'+nom_prenom+'</td>'+responseHTML).show();
						$('q.valider').parent().parent().remove();
						break;
				}
				trier_tableau();
				afficher_masquer_images_action('show');
				infobulle();
			}
		} 

	}
);
