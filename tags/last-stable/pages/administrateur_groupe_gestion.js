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

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Initialisation
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    var mode = false;

    // tri du tableau (avec jquery.tablesorter.js).
    var sorting = [[0,0],[1,0]]; 
    $('table.form').tablesorter({ headers:{3:{sorter:false}} });
    function trier_tableau()
    {
      if($('table.form tbody tr').length>1)
      {
        $('table.form').trigger('update');
        $('table.form').trigger('sorton',[sorting]);
      }
    }
    trier_tableau();

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Fonctions utilisées
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function afficher_form_gestion( mode , id , niveau_nom , ref , nom )
    {
      $('#f_action').val(mode);
      $('#f_id').val(id);
      $('#f_niveau').html(select_niveau.replace('>'+niveau_nom,' selected>'+niveau_nom));
      $('#f_ref').val(ref);
      $('#f_nom').val(nom);
      // pour finir
      $('#form_gestion h2').html(mode[0].toUpperCase() + mode.substring(1) + " un groupe");
      if(mode!='supprimer')
      {
        $('#gestion_edit').show(0);
        $('#gestion_delete').hide(0);
      }
      else
      {
        $('#gestion_delete_identite').html(nom);
        $('#gestion_edit').hide(0);
        $('#gestion_delete').show(0);
      }
      $('#ajax_msg_gestion').removeAttr('class').html("");
      $('#form_gestion label[generated=true]').removeAttr('class').html("");
      $.fancybox( { 'href':'#form_gestion' , onStart:function(){$('#form_gestion').css("display","block");} , onClosed:function(){$('#form_gestion').css("display","none");} , 'modal':true , 'minWidth':600 , 'centerOnScroll':true } );
      if(mode!='supprimer') { $('#f_ref').focus(); }
    }

    /**
     * Ajouter un groupe : mise en place du formulaire
     * @return void
     */
    var ajouter = function()
    {
      mode = $(this).attr('class');
      // Afficher le formulaire
      afficher_form_gestion( mode , '' /*id*/ , '' /*niveau_nom*/ , '' /*ref*/ , '' /*nom*/ );
    };

    /**
     * Modifier une classe : mise en place du formulaire
     * @return void
     */
    var modifier = function()
    {
      mode = $(this).attr('class');
      var objet_tr   = $(this).parent().parent();
      var objet_tds  = objet_tr.find('td');
      // Récupérer les informations de la ligne concernée
      var id         = objet_tr.attr('id').substring(3);
      var niveau_nom = objet_tds.eq(0).html();
      var ref        = objet_tds.eq(1).html();
      var nom        = objet_tds.eq(2).html();
      // Enlever l'ordre du niveau caché
      niveau_nom = niveau_nom.substring(9,niveau_nom.length);
      // Afficher le formulaire
      afficher_form_gestion( mode , id , unescapeHtml(niveau_nom) , unescapeHtml(ref) , unescapeHtml(nom) );
    };

    /**
     * Supprimer une classe : mise en place du formulaire
     * @return void
     */
    var supprimer = function()
    {
      mode = $(this).attr('class');
      var objet_tr   = $(this).parent().parent();
      var objet_tds  = objet_tr.find('td');
      // Récupérer les informations de la ligne concernée
      var id         = objet_tr.attr('id').substring(3);
      var nom        = objet_tds.eq(2).html();
      // Afficher le formulaire
      afficher_form_gestion( mode , id , '' /*niveau_nom*/ , '' /*ref*/ , unescapeHtml(nom) );
    };

    /**
     * Annuler une action
     * @return void
     */
    var annuler = function()
    {
      $.fancybox.close();
      mode = false;
    };

    /**
     * Intercepter la touche entrée ou escape pour valider ou annuler les modifications
     * @return void
     */
    function intercepter(e)
    {
      if(mode)
      {
        if(e.which==13)  // touche entrée
        {
          $('#bouton_valider').click();
        }
        else if(e.which==27)  // touche escape
        {
          $('#bouton_annuler').click();
        }
      }
    }

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Appel des fonctions en fonction des événements ; live est utilisé pour prendre en compte les nouveaux éléments créés
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('q.ajouter').click( ajouter );
    $('q.modifier').live(  'click' , modifier );
    $('q.supprimer').live( 'click' , supprimer );
    $('#bouton_annuler').click( annuler );
    $('#bouton_valider').click( function(){formulaire.submit();} );
    $('#form_gestion input').live( 'keyup' , function(e){intercepter(e);} );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traitement du formulaire
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire = $('#form_gestion');

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation = formulaire.validate
    (
      {
        rules :
        {
          f_niveau : { required:true },
          f_ref    : { required:true , maxlength:8 },
          f_nom    : { required:true , maxlength:20 }
        },
        messages :
        {
          f_niveau : { required:"niveau manquant" },
          f_ref    : { required:"référence manquante" , maxlength:"8 caractères maximum" },
          f_nom    : { required:"nom manquant" , maxlength:"20 caractères maximum" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element) { element.after(error); }
      }
    );

    // Options d'envoi du formulaire (avec jquery.form.js)
    var ajaxOptions =
    {
      url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
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
      $('#ajax_msg_gestion').removeAttr("class").html("&nbsp;");
      var readytogo = validation.form();
      if(readytogo)
      {
        please_wait = true;
        $('#form_gestion button').prop('disabled',true);
        $('#ajax_msg_gestion').removeAttr("class").addClass("loader").html("En cours&hellip;");
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur(jqXHR, textStatus, errorThrown)
    {
      please_wait = false;
      $('#form_gestion button').prop('disabled',false);
      $('#ajax_msg_gestion').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide(responseHTML)
    {
      initialiser_compteur();
      please_wait = false;
      $('#form_gestion button').prop('disabled',false);
      if(responseHTML.substring(0,2)!='<t')
      {
        $('#ajax_msg_gestion').removeAttr("class").addClass("alerte").html(responseHTML);
      }
      else
      {
        $('#ajax_msg_gestion').removeAttr("class").addClass("valide").html("Demande réalisée !");
        action = $('#f_action').val();
        switch (action)
        {
          case 'ajouter':
            niveau_nom = $('#f_niveau option:selected').text();
            responseHTML  = responseHTML.replace('<td>{{NIVEAU_NOM}}</td>','<td>'+'<i>'+tab_niveau_ordre[niveau_nom]+'</i>'+niveau_nom+'</td>');
            $('table.form tbody tr td[colspan=4]').parent().remove(); // En cas de tableau avec une ligne vide pour la conformité XHTML ; IE8 bugue si on n'indique que [colspan]
            $('table.form tbody').append(responseHTML);
            trier_tableau();
            break;
          case 'modifier':
            niveau_nom = $('#f_niveau option:selected').text();
            responseHTML  = responseHTML.replace('<td>{{NIVEAU_NOM}}</td>','<td>'+'<i>'+tab_niveau_ordre[niveau_nom]+'</i>'+niveau_nom+'</td>');
            $('#id_'+$('#f_id').val()).addClass("new").html(responseHTML);
            trier_tableau();
            break;
          case 'supprimer':
            $('#id_'+$('#f_id').val()).remove();
            break;
        }
        $.fancybox.close();
        mode = false;
      }
    }

  }
);
