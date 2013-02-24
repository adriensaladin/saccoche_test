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

    // tri du tableau (avec jquery.tablesorter.js).
    $('#table_action').tablesorter({ headers:{0:{sorter:false},4:{sorter:false},7:{sorter:'date_fr'},9:{sorter:false}} });
    var tableau_tri = function(){ $('#table_action').trigger( 'sorton' , [ [[8,0],[1,0],[3,1],[2,0]] ] ); };
    var tableau_maj = function(){ $('#table_action').trigger( 'update' , [ true ] ); };
    tableau_tri();

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le checkbox pour choisir ou non une date visible différente de la date du devoir
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_visible()
    {
      // Emploi de css() au lieu de show() hide() car sinon conflits constatés avec $("#step_creer").show() et $("#step_creer").hide() vers ligne 360.
      if($('#box_date').is(':checked'))
      {
        $('#f_date_visible').val($('#f_date').val());
        $('#box_date').next().css('display','inline-block').next().css('display','none');
      }
      else
      {
        $('#box_date').next().css('display','none').next().css('display','inline-block');
      }
    }

    function maj_autoeval()
    {
      // Emploi de css() au lieu de show() hide() car sinon conflits constatés avec $("#step_creer").show() et $("#step_creer").hide() vers ligne 360.
      if($('#box_autoeval').is(':checked'))
      {
        $('#f_date_autoeval').val('00/00/0000');
        $('#box_autoeval').next().css('display','inline-block').next().css('display','none');
      }
      else
      {
        $('#box_autoeval').next().css('display','none').next().css('display','inline-block');
        $('#f_date_autoeval').val(input_autoeval);
      }
    }

    function maj_dates()
    {
      if( $("#f_quoi option:selected").val() == 'completer')
      {
        var tab_infos = $('#f_devoir option:selected').text().split(' || ');
      }
      else
      {
        var tab_infos = new Array();
        tab_infos[0] = input_date;
        tab_infos[1] = input_date;
      }
      if(tab_infos.length>1)
      {
        $('#f_date').val(tab_infos[0]);
        $('#f_date_visible').val(tab_infos[1]);
        // Simuler un clic sur #box_date pour un appel de maj_visible() deconne (dans maj_visible() le test .is(':checked') ne renvoie pas ce qui est attendu) :
        /*
        if( ( (tab_infos[0]==tab_infos[1])&&(!$('#box_date').is(':checked')) ) || ( (tab_infos[0]!=tab_infos[1])&&($('#box_date').is(':checked')) ) )
        {
          $('#box_date').click();
        }
        */
        // Alors j'ai réécrit ici une partie de maj_visible() :
        // Emploi de css() au lieu de show() hide() car sinon conflits constatés avec $("#step_creer").show() et $("#step_creer").hide() vers ligne 360.
        if(tab_infos[0]==tab_infos[1])
        {
          $('#box_date').prop('checked',true).next().css('display','inline-block').next().css('display','none');
        }
        else
        {
          $('#box_date').prop('checked',false).next().css('display','none').next().css('display','inline-block');
        }
      }
    }

    $('#box_date').click
    (
      function()
      {
        maj_visible();
      }
    );

    $('#box_autoeval').click
    (
      function()
      {
        maj_autoeval();
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traitement du premier formulaire pour afficher le tableau avec la liste des demandes
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Afficher masquer des options du formulaire

    $('#f_periode').change
    (
      function()
      {
        var periode_val = $("#f_periode").val();
        if(periode_val!=0)
        {
          $("#dates_perso").attr("class","hide");
        }
        else
        {
          $("#dates_perso").attr("class","show");
        }
      }
    );

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire0 = $('#form_prechoix');

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation0 = formulaire0.validate
    (
      {
        rules :
        {
          f_matiere : { required:false },
          f_groupe  : { required:false }
        },
        messages :
        {
          f_matiere : { },
          f_groupe  : { }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element)
        {
          if(element.is("select")) {element.after(error);}
          else if(element.attr("type")=="text") {element.next().after(error);}
        }
      }
    );

    // Options d'envoi du formulaire (avec jquery.form.js)
    var ajaxOptions0 =
    {
      url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
      type : 'POST',
      dataType : "html",
      clearForm : false,
      resetForm : false,
      target : "#ajax_msg_prechoix",
      beforeSubmit : test_form_avant_envoi0,
      error : retour_form_erreur0,
      success : retour_form_valide0
    };

    // Envoi du formulaire (avec jquery.form.js)
    formulaire0.submit
    (
      function()
      {
        $('#table_action tbody').html('');
        $('#tr_sans').html('<td class="nu"></td>');
        $("#zone_actions").hide(0);
        $('#ajax_msg_gestion').removeAttr("class").html("&nbsp;");
        // Mémoriser le nom de la matière + le type de groupe + le nom du groupe
        $('#f_matiere_nom').val(  $("#f_matiere option:selected").text() );
        $("#f_groupe_id").val(    $("#f_groupe option:selected").val() );
        $("#f_groupe_id2").val(   $("#f_groupe option:selected").val() );
        $("#f_groupe_nom").val(   $("#f_groupe option:selected").text() );
        $("#f_groupe_type").val(  $("#f_groupe option:selected").parent().attr('label') );
        $("#f_groupe_type2").val( $("#f_groupe option:selected").parent().attr('label') );
        $(this).ajaxSubmit(ajaxOptions0);
        return false;
      }
    ); 

    // Fonction précédent l'envoi du formulaire (avec jquery.form.js)
    function test_form_avant_envoi0(formData, jqForm, options)
    {
      $('#ajax_msg_prechoix').removeAttr("class").html("&nbsp;");
      var readytogo = validation0.form();
      if(readytogo)
      {
        $('#ajax_msg_prechoix').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $('#form_gestion').hide();
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur0(jqXHR, textStatus, errorThrown)
    {
      $('#ajax_msg_prechoix').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide0(responseHTML)
    {
      initialiser_compteur();
      tab_response = responseHTML.split('<¤>');
      if( tab_response[0]!='ok' )
      {
        $('#ajax_msg_prechoix').removeAttr("class").addClass("alerte").html(tab_response[0]);
      }
      else
      {
        response_msg = tab_response[1];
        response_td  = tab_response[2];
        response_tr  = tab_response[3];
        $('#ajax_msg_prechoix').removeAttr("class").addClass("valide").html("Demande réalisée !");
        
        $('#zone_messages').html(response_msg);
        $('#table_action tbody').html(response_tr);
        $('#tr_sans').html(response_td);
        format_liens('#zone_messages');
        tableau_maj();
        var etat_disabled = ($("#f_groupe_id").val()>0) ? false : true ;
        $('#form_gestion').show();
        $("#f_qui option[value=groupe]").text($("#f_groupe_nom").val()).prop('disabled',etat_disabled);
        if(etat_disabled) { $("#f_qui option[value=select]").prop('selected',true); }
        maj_evaluation();
        $("#zone_actions").show(0);
      }
    }

    // Soumettre au chargement pour initialiser l'affichage, et au changement d'un select initial

    formulaire0.submit();

    $('#f_matiere , #f_groupe').change
    (
      function()
      {
        formulaire0.submit();
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic pour voir les messages des élèves
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#voir_messages').click
    (
      function()
      {
        $.fancybox( { 'href':'#zone_messages' , onStart:function(){$('#zone_messages').css("display","block");} , onClosed:function(){$('#zone_messages').css("display","none");} , 'centerOnScroll':true } );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Charger le select f_devoir en ajax
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_evaluation()
    {
      $("#f_devoir").html('<option value=""></option>');
      $('#ajax_maj1').removeAttr("class").addClass("loader").html("En cours&hellip;");
      eval_type = $('#f_qui option:selected').val();
      groupe_id = $("#f_groupe_id").val();
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page=_maj_select_eval',
          data : 'eval_type='+eval_type+'&groupe_id='+groupe_id,
          dataType : "html",
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_maj1').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
          },
          success : function(responseHTML)
          {
            initialiser_compteur();
            if(responseHTML.substring(0,7)=='<option')  // Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
            {
              $('#ajax_maj1').removeAttr("class").html("&nbsp;");
              $('#f_devoir').html(responseHTML).show();
              maj_dates();
            }
          else
            {
              $('#ajax_maj1').removeAttr("class").addClass("alerte").html(responseHTML);
            }
          }
        }
      );
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Éléments dynamiques du formulaire
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Tout cocher ou tout décocher
    $('#all_check').click
    (
      function()
      {
        $('#table_action input[type=checkbox]').prop('checked',true);
        return false;
      }
    );
    $('#all_uncheck').click
    (
      function()
      {
        $('#table_action input[type=checkbox]').prop('checked',false);
        return false;
      }
    );

    // Récupérer les noms de items des checkbox cochés pour la description de l'évaluation
    $('#table_action').on
    (
      'click',
      'input[type=checkbox]',
      function()
      {
        // Récupérer les checkbox cochés
        var listing_refs = '';
        $('#table_action input[type=checkbox]:checked').each
        (
          function()
          {
            item = $(this).parent().next().next().text();
            ref  = ' ' + item.substring( item.indexOf('.')+1 , item.length-1 );
            if(listing_refs.indexOf(ref)==-1)
            {
              listing_refs += ref;
            }
          }
        );
        if(listing_refs.length)
        {
          $("#f_info").val('Demande'+listing_refs);
        }
      }
    );

    // Afficher / masquer les éléments suivants du formulaire suivant le choix du select "f_quoi"
    // Si "f_quoi" vaut "completer" alors charger le select "f_devoir" en ajax
    $('#f_quoi').change
    (
      function()
      {
        quoi = $("#f_quoi option:selected").val();
        if(quoi=='completer')                        {maj_evaluation();}
        if( (quoi=='creer') || (quoi=='completer') ) {$("#step_qui").show(0);}       else {$("#step_qui").hide(0);}
        if(quoi=='creer')                            {$("#step_creer").show(0);}     else {$("#step_creer").hide(0);}
        if(quoi=='completer')                        {$("#step_completer").show(0);} else {$("#step_completer").hide(0);}
        if( (quoi=='creer') || (quoi=='completer') ) {$("#step_suite").show(0);}     else {$("#step_suite").hide(0);}
        if( (quoi!='') && (quoi!='retirer') )        {$("#step_message").show(0);}   else {$("#step_message").hide(0);}
        if(quoi!='')                                 {$("#step_valider").show(0);}
      }
    );

    // Charger le select "f_devoir" en ajax si "f_qui" change et que "f_quoi" est à "completer"
    $('#f_qui').change
    (
      function()
      {
        if( $("#f_quoi option:selected").val() == 'completer')
        {
          maj_evaluation();
        }
      }
    );

    $('#f_quoi , #f_devoir').change
    (
      function()
      {
        maj_dates();
      }
    );

    // Indiquer le nombre de caractères restant autorisés dans le textarea
    $('#f_message').keyup
    (
      function()
      {
        afficher_textarea_reste( $(this) , 500 );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traitement du formulaire principal
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire = $('#form_gestion');

    // Ajout d'une méthode pour valider les dates de la forme jj/mm/aaaa (trouvé dans le zip du plugin, corrige en plus un bug avec Safari)
    // méthode dateITA déjà ajoutée

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation = formulaire.validate
    (
      {
        rules :
        {
          f_ids           : { required:true },
          f_quoi          : { required:true },
          f_qui           : { required:function(){quoi=$("#f_quoi").val(); return ((quoi=='creer')||(quoi=='completer'));} },
          f_date          : { required:function(){return $("#f_quoi").val()=='creer';} , dateITA:true },
          f_date_visible  : { required:function(){return (($("#f_quoi").val()=='creer')&&(!$('#box_date').is(':checked')));} , dateITA:true },
          f_date_autoeval : { required:function(){return (($("#f_quoi").val()=='creer')&&(!$('#box_autoeval').is(':checked')));} , dateITA:true },
          f_info          : { required:false , maxlength:60 },
          f_devoir        : { required:function(){return $("#f_quoi").val()=='completer';} },
          f_suite         : { required:function(){quoi=$("#f_quoi").val(); return ((quoi=='creer')||(quoi=='completer'));} },
          f_message       : { required:false }
        },
        messages :
        {
          f_ids           : { required:"demandes manquantes" },
          f_quoi          : { required:"action manquante" },
          f_qui           : { required:"groupe manquant" },
          f_date          : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" },
          f_date_visible  : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" },
          f_date_autoeval : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" },
          f_info          : { maxlength:"60 caractères maximum" },
          f_devoir        : { required:"évaluation manquante" },
          f_suite         : { required:"suite manquante" },
          f_message       : {  }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element)
        {
          if(element.is("select")) {element.after(error);}
          else if(element.attr("id")=='f_info') {element.after(error);}
          else if(element.attr("type")=="text") {element.next().after(error);}
          else if(element.attr("type")=="checkbox") {$('#ajax_msg_gestion').after(error);}
        }
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
      target : "#ajax_msg_gestion",
      beforeSubmit : test_form_avant_envoi,
      error : retour_form_erreur,
      success : retour_form_valide
    };

    // Envoi du formulaire (avec jquery.form.js)
    formulaire.submit
    (
      function()
      {
        // grouper les checkbox multiples => normalement pas besoin si name de la forme nom[], mais ça pose pb à jquery.validate.js d'avoir un id avec []
        // alors j'ai copié le tableau dans un champ hidden...
        var f_ids = new Array(); $("input[name=f_ids]:checked").each(function(){f_ids.push($(this).val());});
        $('#ids').val(f_ids);
        $(this).ajaxSubmit(ajaxOptions);
        return false;
      }
    ); 

    // Fonction précédent l'envoi du formulaire (avec jquery.form.js)
    function test_form_avant_envoi(formData, jqForm, options)
    {
      $('#ajax_msg_gestion').removeAttr("class").html("&nbsp;");
      var readytogo = validation.form();
      if(readytogo)
      {
        $('button').prop('disabled',true);
        $('#ajax_msg_gestion').removeAttr("class").addClass("loader").html("En cours&hellip;");
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur(jqXHR, textStatus, errorThrown)
    {
      $('button').prop('disabled',false);
      $('#ajax_msg_gestion').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide(responseHTML)
    {
      initialiser_compteur();
      $('button').prop('disabled',false);
      if(responseHTML!='ok')
      {
        $('#ajax_msg_gestion').removeAttr("class").addClass("alerte").html(responseHTML);
      }
      else
      {
        quoi  = $("#f_quoi").val();
        suite = $("#f_suite").val();
        if( ((quoi=='creer')&&(suite=='changer')) || ((quoi=='completer')&&(suite=='changer')) || (quoi=='changer') )
        {
          // Changer le statut des demandes cochées
          $('#table_action input[type=checkbox]:checked').each
          (
            function()
            {
              this.checked = false;
              $(this).parent().parent().removeAttr("class").children("td:last").prev().html('évaluation en préparation');
              tableau_maj(); // sinon, un clic ultérieur pour retrier par statut ne fonctionne pas
            }
          );
        }
        else if( ((quoi=='creer')&&(suite=='retirer')) || ((quoi=='completer')&&(suite=='retirer')) || (quoi=='retirer') )
        {
          // Retirer les demandes cochées
          $('#table_action input[type=checkbox]:checked').each
          (
            function()
            {
              $(this).parent().parent().remove();
            }
          );
        }
        $('#ajax_msg_gestion').removeAttr("class").addClass("valide").html("Demande réalisée !");
      }
    }

  }
);
