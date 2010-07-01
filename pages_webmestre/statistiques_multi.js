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

		// tri du tableau (avec jquery.tablesorter.js).
		var sorting = [[1,0]];
		$('table#resultat').tablesorter({ headers:{0:{sorter:false},3:{sorter:false}} });
		function trier_tableau()
		{
			if($('table#resultat tbody tr').length)
			{
				$('table#resultat').trigger('update');
				$('table#resultat').trigger('sorton',[sorting]);
			}
		}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Formulaire et traitement
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		// Le formulaire qui va être analysé et traité en AJAX
		var formulaire = $("#statistiques");

		// Vérifier la validité du formulaire (avec jquery.validate.js)
		var validation = formulaire.validate
		(
			{
				rules :
				{
					f_base    : { required:true }
				},
				messages :
				{
					f_base    : { required:"structure(s) manquante(s)" }
				},
				errorElement : "label",
				errorClass : "erreur",
				errorPlacement : function(error,element) { element.after(error); }
				// success: function(label) {label.text("ok").removeAttr("class").addClass("valide");} Pas pour des champs soumis à vérification PHP
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
				// grouper les select multiples => normalement pas besoin si name de la forme nom[], mais ça plante curieusement sur le serveur competences.sesamath.net
				// alors j'ai copié le tableau dans un champ hidden...
				var bases = new Array(); $("#f_base option:selected").each(function(){bases.push($(this).val());});
				$('#bases').val(bases);
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
				$('#f_submit').hide();
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Préparation des statistiques... Veuillez patienter.");
			}
			return readytogo;
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_erreur(msg,string)
		{
			please_wait = false;
			$('#f_submit').show();
			$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez valider de nouveau.");
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_valide(responseHTML)
		{
			maj_clock(1);
			please_wait = false;
			if(responseHTML.substring(0,2)!='ok')
			{
				$('#f_submit').show();
				$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
			}
			else
			{
				var max = responseHTML.substring(3,responseHTML.length);
				$('#ajax_msg1').removeAttr("class").addClass("loader").html('Structures à l\'étude : étape 1 sur ' + max + '...');
				$('#ajax_msg2').html('Ne pas interrompre la procédure avant la fin du traitement !');
				$('#ajax_num').html(1);
				$('#ajax_max').html(max);
				$('#ajax_info').show('fast');
				$('#structures').hide('fast').children('#resultat').children('tbody').html('');
				calculer();
			}
		} 

		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		// Etapes d'envoi de la newsletter
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		function calculer()
		{
			var num = parseInt( $('#ajax_num').html() );
			var max = parseInt( $('#ajax_max').html() );
			// Appel en ajax
			$.ajax
			(
				{
					type : 'GET',
					url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
					data : 'num=' + num + '&max=' + max,
					dataType : "html",
					error : function(msg,string)
					{
						$('#ajax_msg1').removeAttr("class").addClass("alerte").html('Echec lors de la connexion au serveur !');
						$('#ajax_msg2').html('<a id="a_reprise" href="#">Reprendre la procédure à l\'étape ' + num + ' sur ' + max + '.</a>');
					},
					success : function(responseHTML)
					{
						if(responseHTML.substring(0,2)=='ok')
						{
							var ligne = responseHTML.substring(3,responseHTML.length);
							$('#resultat tbody').append(ligne);
							num++;
							if(num > max)	// Utilisation de parseInt obligatoire sinon la comparaison des valeurs pose ici pb
							{
								$('#ajax_msg1').removeAttr("class").addClass("valide").html('Calcul des statistiques terminé.');
								$('#ajax_msg2').html('');
								trier_tableau();
								$('#structures').show('fast');
								$('#expli').show('fast');
								$('#ajax_info').hide('fast');
								$('#f_submit').show();
								$('#ajax_msg').removeAttr("class").html("&nbsp;");
							}
							else
							{
								$('#ajax_num').html(num);
								$('#ajax_msg1').removeAttr("class").addClass("loader").html('Structures à l\'étude : étape ' + num + ' sur ' + max + '...');
								$('#ajax_msg2').html('Ne pas interrompre la procédure avant la fin du traitement !');
								calculer();
							}
						}
						else
						{
							$('#ajax_msg1').removeAttr("class").addClass("alerte").html(responseHTML);
							$('#ajax_msg2').html('<a id="a_reprise" href="#">Reprendre la procédure à l\'étape ' + num + ' sur ' + max + '.</a>');
						}
					}
				}
			);
		}

		// live est utilisé pour prendre en compte les nouveaux éléments html créés

		$('#a_reprise').live
		('click',
			function()
			{
				num = $('#ajax_num').html();
				max = $('#ajax_max').html();
				$('#ajax_msg1').removeAttr("class").addClass("loader").html('Structures à l\'étude : étape ' + num + ' sur ' + max + '...');
				$('#ajax_msg2').html('Ne pas interrompre la procédure avant la fin du traitement !');
				calculer();
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Initialisation
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		if( $('#f_base option:selected').length )
		{
			formulaire.submit();
		}

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
//	Éléments dynamiques du formulaire
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		// Tout cocher ou tout décocher
		$('#all_check').click
		(
			function()
			{
				$('input[type=checkbox]').attr('checked',true);
			}
		);
		$('#all_uncheck').click
		(
			function()
			{
				$('input[type=checkbox]').attr('checked',false);
			}
		);

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
//	Appeler la page de newsletter
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		$('button').click
		(
			function()
			{
				var check_ids = new Array(); $("input[type=checkbox]:checked").each(function(){check_ids.push($(this).val());});
				$('#listing_ids').val(check_ids);
				var form = document.getElementById('structures');
				form.action = './index.php?dossier='+DOSSIER+'&fichier=newsletter_multi';
				form.method = 'post';
				form.submit();
			}
		);

	}
);
