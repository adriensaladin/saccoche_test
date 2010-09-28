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
//	Reporter dans les input equiv_txt les valeurs préféfinies lors du clic sur un bouton radio (jeu de symboles colorés).
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('table.simulation input[type=radio]').click
		(
			function()
			{
				var note_nom = $(this).val();
				$('#texte_RR').val( tab_notes_txt[note_nom]['RR'] );
				$('#texte_R').val( tab_notes_txt[note_nom]['R'] );
				$('#texte_V').val( tab_notes_txt[note_nom]['V'] );
				$('#texte_VV').val( tab_notes_txt[note_nom]['VV'] );
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Activation du colorpicker pour les 3 champs input.
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		var f = $.farbtastic('#colorpicker');
		$('div.colorpicker input').focus
		(
			function()
			{
				$('#colorpicker').removeAttr("class");
				f.linkTo(this);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Reporter dans un input colorpicker une valeur préféfinie lors du clic sur un bouton (couleur de fond).
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('div.colorpicker button').click
		(
			function()
			{
				$('#'+$(this).attr('name')).val($(this).val()).focus();
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Affecter aux div la même couleur de fond que celle du input.
//	Utilisation d'un test en boucle car un simple test change() ne fonctionne pas.
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		function reporter_couleur()
		{
			$("body").everyTime
			('1ds', 'report', function()
				{
					$('div.colorpicker input').each
					(
						function()
						{
							$(this).parent().parent().css('backgroundColor',$(this).val());
						}
					);
				}
			);
		}
		reporter_couleur();

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		// Traitement du formulaire principal
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		// Le formulaire qui va être analysé et traité en AJAX
		var formulaire = $('#form');

		// Ajout d'une méthode pour vérifier le format hexadécimal
		jQuery.validator.addMethod
		(
			"hexa_format", function(value, element)
			{
				return this.optional(element) || ( (/^\#[0-9a-f]{3,6}$/i.test(value)) && (value.length!=5) && (value.length!=6) ) ;
			}
			, "format incorrect"
		); 

		// Vérifier la validité du formulaire (avec jquery.validate.js)
		var validation = formulaire.validate
		(
			{
				rules :
				{
					image_style : { required:true },
					texte_RR    : { required:true , maxlength:3 }, // restriction alphabétique non imposée pour permettre - + etc.
					texte_R     : { required:true , maxlength:3 }, // restriction alphabétique non imposée pour permettre - + etc.
					texte_V     : { required:true , maxlength:3 }, // restriction alphabétique non imposée pour permettre - + etc.
					texte_VV    : { required:true , maxlength:3 }, // restriction alphabétique non imposée pour permettre - + etc.
					color_NA    : { required:true , hexa_format:true },
					color_VA    : { required:true , hexa_format:true },
					color_A     : { required:true , hexa_format:true }
				},
				messages :
				{
					image_style : { required:"codes de couleur manquant" },
					texte_RR    : { required:"texte manquant" , maxlength:"3 caractères maximum" },
					texte_R     : { required:"texte manquant" , maxlength:"3 caractères maximum" },
					texte_V     : { required:"texte manquant" , maxlength:"3 caractères maximum" },
					texte_VV    : { required:"texte manquant" , maxlength:"3 caractères maximum" },
					color_NA    : { required:"couleur manquante" , hexa_format:"format incorrect" },
					color_VA    : { required:"couleur manquante" , hexa_format:"format incorrect" },
					color_A     : { required:"couleur manquante" , hexa_format:"format incorrect" }
				},
				errorElement : "label",
				errorClass : "erreur",
				errorPlacement : function(error,element)
				{
					if(element.attr("type")=="radio") { $('#ajax_msg').html(error); }
					else if(element.parent().attr("id")=="equiv_txt") {element.after(error);}
					else if(element.attr("type")=="text") {element.parent().next().children('label').html(error);}
				}
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
				$(this).ajaxSubmit(ajaxOptions);
				return false;
			}
		); 

		// Fonction précédent l'envoi du formulaire (avec jquery.form.js)
		function test_form_avant_envoi(formData, jqForm, options)
		{
			$('#ajax_msg').removeAttr("class").html("&nbsp;");
			var readytogo = validation.form();
			if(readytogo)
			{
				$("button").attr('disabled','disabled');
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Traitement de la demande en cours... Veuillez patienter.");
			}
			return readytogo;
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_erreur(msg,string)
		{
			$("button").removeAttr('disabled');
			$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez valider de nouveau.");
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_valide(responseHTML)
		{
			maj_clock(1);
			$("button").removeAttr('disabled');
			if(responseHTML=='ok')
			{
				$('#ajax_msg').removeAttr("class").addClass("valide").html("Choix mémorisés !");
			}
			else
			{
				$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
			}
		} 

	}
);
