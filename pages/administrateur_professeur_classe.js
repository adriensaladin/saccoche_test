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
		//	Ajouter / Retirer une affectation à une classe
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#autocheckbox input[type=checkbox]').click
		(
			function()
			{
				var obj_bouton = $(this);
				var action     = (obj_bouton.is(':checked')) ? 'ajouter' : 'retirer' ;
				var user_id    = obj_bouton.val();
				var classe_id  = obj_bouton.parent().parent().attr('id').substring(3);
				var check_old  = (action=='ajouter') ? false : true ;
				var class_old  = (action=='ajouter') ? 'off' : 'on' ;
				var class_new  = (action=='ajouter') ? 'on' : 'off' ;
				obj_bouton.hide(0).parent().removeAttr('class').addClass('load');
				$.ajax
				(
					{
						type : 'POST',
						url  : 'ajax.php?page='+PAGE,
						data : 'action='+action+'&user_id='+user_id+'&classe_id='+classe_id,
						dataType : "html",
						error : function(msg,string)
						{
							obj_bouton.prop('checked',check_old).show(0).parent().removeAttr('class').addClass(class_old);
							$.fancybox( '<label class="alerte">'+'Echec de la connexion !\nVeuillez recommencer.'+'</label>' , {'centerOnScroll':true} );
							return false;
						},
						success : function(responseHTML)
						{
							if(responseHTML!='ok')
							{
								$.fancybox( '<label class="alerte">'+responseHTML+'</label>' , {'centerOnScroll':true} );
								obj_bouton.prop('checked',check_old).show(0).parent().removeAttr('class').addClass(class_old);
							}
							else
							{
								obj_bouton.show(0).parent().removeAttr('class').addClass(class_new);
								// MAJ tableaux bilans : lignes
								if(action=='ajouter')
								{
									var prof_nom   = $('#th_'+user_id).children('img').attr('alt');
									var classe_nom = $('#tr_'+classe_id+' th').html();
									$('#cpb_'+classe_id).append('<div id="cp_'+classe_id+'_'+user_id+'" class="off"><input type="checkbox" id="'+classe_id+'cp'+user_id+'" value="" /> <label for="'+classe_id+'cp'+user_id+'">'+prof_nom+'</label></div>');
									$('#pcb_'+user_id).append('<div id="pc_'+user_id+'_'+classe_id+'" class="off"><input type="checkbox" id="'+user_id+'pc'+classe_id+'" value="" /> <label for="'+user_id+'pc'+classe_id+'">'+classe_nom+'</label></div>');
								}
								else if(action=='retirer')
								{
									$('#cp_'+classe_id+'_'+user_id).remove();
									$('#pc_'+user_id+'_'+classe_id).remove();
								}
								// MAJ tableaux bilans : totaux
								var nb_profs = $('#cpb_'+classe_id+' div').length;
								var nb_classes = $('#pcb_'+user_id+' div').length;
								var s_profs = (nb_profs>1) ? 's' : '' ;
								var s_classes = (nb_classes>1) ? 's' : '' ;
								$('#cpf_'+classe_id).html(nb_profs+' professeur'+s_profs);
								$('#pcf_'+user_id).html(nb_classes+' classe'+s_classes);
							}
						}
					}
				);
			}
		);

		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		//	Ajouter / Retirer une affectation en tant que professeur principal
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('table.affectation input[type=checkbox]').click
		(
			function()
			{
				var obj_bouton = $(this);
				var action     = (obj_bouton.is(':checked')) ? 'ajouter_pp' : 'retirer_pp' ;
				var tab_id     = obj_bouton.parent().attr('id').split('_');
				var user_id    = (tab_id[0]=='pc') ? tab_id[1] : tab_id[2] ;
				var classe_id  = (tab_id[0]=='cp') ? tab_id[1] : tab_id[2] ;
				var check_old  = (action=='ajouter_pp') ? false : true ;
				var check_new  = (action=='ajouter_pp') ? true : false ;
				var class_old  = (action=='ajouter_pp') ? 'off' : 'on' ;
				var class_new  = (action=='ajouter_pp') ? 'on' : 'off' ;
				obj_bouton.prop('disabled',true).parent().removeAttr('class').addClass('load');
				$.ajax
				(
					{
						type : 'POST',
						url  : 'ajax.php?page='+PAGE,
						data : 'action='+action+'&user_id='+user_id+'&classe_id='+classe_id,
						dataType : "html",
						error : function(msg,string)
						{
							obj_bouton.prop('disabled',false).prop('checked',check_old).parent().removeAttr('class').addClass(class_old);
							$.fancybox( '<label class="alerte">'+'Echec de la connexion !\nVeuillez recommencer.'+'</label>' , {'centerOnScroll':true} );
							return false;
						},
						success : function(responseHTML)
						{
							if(responseHTML!='ok')
							{
								$.fancybox( '<label class="alerte">'+responseHTML+'</label>' , {'centerOnScroll':true} );
								obj_bouton.prop('disabled',false).prop('checked',check_old).parent().removeAttr('class').addClass(class_old);
							}
							else
							{
								obj_bouton.prop('disabled',false).parent().removeAttr('class').addClass(class_new);
								// MAJ tableaux bilans
								var id_autre = (tab_id[0]=='cp') ? user_id+'pc'+classe_id : classe_id+'cp'+user_id ;
								$('#'+id_autre).prop('checked',check_new).parent().removeAttr('class').addClass(class_new);
							}
						}
					}
				);
			}
		);

	}
);
