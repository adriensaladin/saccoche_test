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

		// tri du tableau (avec jquery.tablesorter.js).
		var sorting = [[1,0],[2,0]];
		$('table.form').tablesorter({ headers:{5:{sorter:false}} });
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
		 * Supprimer une demande : mise en place du formulaire
		 * @return void
		 */
		var supprimer = function()
		{
			mode = $(this).attr('class');
			afficher_masquer_images_action('hide');
			id = $(this).parent().parent().attr('id').substring(3);
			new_span  = '<span class="danger"><input id="f_action" name="f_action" type="hidden" value="'+mode+'" /><input id="f_id" name="f_id" type="hidden" value="'+id+'" />Confirmez vous ?<q class="valider" title="Confirmer la suppression de cette demande."></q><q class="annuler" title="Annuler la suppression de cette demande."></q> <label id="ajax_msg">&nbsp;</label></span>';
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
			$(this).parent().remove();
			afficher_masquer_images_action('show');
			mode = false;
		};

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Appel des fonctions en fonction des événements ; live est utilisé pour prendre en compte les nouveaux éléments créés
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('q.demander_del').click( supprimer );
		$('q.annuler').live( 'click' , annuler );
		$('q.valider').live( 'click' , function(){formulaire.submit();} );


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
				},
				messages :
				{
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
			if(responseHTML!='ok')
			{
				$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
			}
			else
			{
				$('#ajax_msg').removeAttr("class").addClass("valide").html("Demande réalisée !");
				$('q.valider').parent().parent().parent().remove();
				afficher_masquer_images_action('show');
			}
		} 

	}
);
