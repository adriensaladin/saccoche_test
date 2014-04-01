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
    $('#table_action').tablesorter({ headers:{1:{sorter:false},2:{sorter:false}} });
    var tableau_tri = function(){ $('#table_action').trigger( 'sorton' , [ [[0,0]] ] ); };
    var tableau_maj = function(){ $('#table_action').trigger( 'update' , [ true ] ); };
    tableau_tri();

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Fonctions utilisées
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function afficher_form_gestion( mode , id , nom , compet_nombre , compet_liste )
    {
      $('#f_action').val(mode);
      $('#f_id').val(id);
      $('#f_nom').val(nom);
      $('#f_compet_nombre').val(compet_nombre);
      $('#f_compet_liste').val(compet_liste);
      // pour finir
      $('#form_gestion h2').html(mode[0].toUpperCase() + mode.substring(1) + " un regroupements d'items");
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
      if(mode=='ajouter') { $('#f_nom').focus(); }
    }

    /**
     * Ajouter une sélection d'items : mise en place du formulaire
     * @return void
     */
    var ajouter = function()
    {
      mode = $(this).attr('class');
      // Afficher le formulaire
      afficher_form_gestion( mode , '' /*id*/ , '' /*nom*/ , 'aucun' /*compet_nombre*/ , '' /*compet_liste*/ );
    };

    /**
     * Modifier un message : mise en place du formulaire
     * @return void
     */
    var modifier = function()
    {
      mode = $(this).attr('class');
      var objet_tr      = $(this).parent().parent();
      var objet_tds     = objet_tr.find('td');
      // Récupérer les informations de la ligne concernée
      var id            = objet_tr.attr('id').substring(3);
      var nom           = objet_tds.eq(0).html();
      var compet_nombre = objet_tds.eq(1).html();
      // liste des items
      var compet_liste  = tab_items[id];
      // Afficher le formulaire
      afficher_form_gestion( mode , id , unescapeHtml(nom) , compet_nombre , compet_liste );
    };

    /**
     * Supprimer un message : mise en place du formulaire
     * @return void
     */
    var supprimer = function()
    {
      mode = $(this).attr('class');
      var objet_tr      = $(this).parent().parent();
      var objet_tds     = objet_tr.find('td');
      // Récupérer les informations de la ligne concernée
      var id            = objet_tr.attr('id').substring(3);
      var nom           = objet_tds.eq(0).html();
      // Afficher le formulaire
      afficher_form_gestion( mode , id , unescapeHtml(nom) , '' /*compet_nombre*/ , '' /*compet_liste*/ );
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

    /**
     * Choisir les items associés à une sélection : mise en place du formulaire
     * @return void
     */
    var choisir_compet = function()
    {
      // Ne pas changer ici la valeur de "mode" (qui est à "ajouter" ou "modifier" ou "dupliquer").
      cocher_matieres_items( $('#f_compet_liste').val() );
      // Afficher la zone
      $.fancybox( { 'href':'#zone_matieres_items' , onStart:function(){$('#zone_matieres_items').css("display","block");} , onClosed:function(){$('#zone_matieres_items').css("display","none");} , 'modal':true , 'centerOnScroll':true } );
      $(document).tooltip("destroy");infobulle(); // Sinon, bug avec l'infobulle contenu dans le fancybox qui ne disparait pas au clic...
    };

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Appel des fonctions en fonction des événements ; live est utilisé pour prendre en compte les nouveaux éléments créés
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_action').on( 'click' , 'q.ajouter'        , ajouter );
    $('#table_action').on( 'click' , 'q.modifier'       , modifier );
    $('#table_action').on( 'click' , 'q.supprimer'      , supprimer );

    $('#form_gestion').on( 'click' , '#bouton_annuler'  , annuler );
    $('#form_gestion').on( 'click' , '#bouton_valider'  , function(){formulaire.submit();} );
    $('#form_gestion').on( 'keyup' , 'input,select'     , function(e){intercepter(e);} );
    $('#form_gestion').on( 'click' , 'q.choisir_compet' , choisir_compet );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le bouton pour fermer le cadre des items associés à une sélection (annuler / retour)
// ////////////////////////////////////////////////////////////////////////////////////////////////////
    $('#annuler_compet').click
    (
      function()
      {
        $.fancybox( { 'href':'#form_gestion' , onStart:function(){$('#form_gestion').css("display","block");} , onClosed:function(){$('#form_gestion').css("display","none");} , 'modal':true , 'minWidth':600 , 'centerOnScroll':true } );
        return(false);
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le bouton pour valider le choix des items associés à une sélection
// ////////////////////////////////////////////////////////////////////////////////////////////////////
    $('#valider_compet').click
    (
      function()
      {
        var liste = '';
        var nombre = 0;
        $("#zone_matieres_items input[type=checkbox]:checked").each
        (
          function()
          {
            liste += $(this).val()+'_';
            nombre++;
          }
        );
        var compet_liste  = liste.substring(0,liste.length-1);
        var compet_nombre = (nombre==0) ? 'aucun' : ( (nombre>1) ? nombre+' items' : nombre+' item' ) ;
        $('#f_compet_liste').val(compet_liste);
        $('#f_compet_nombre').val(compet_nombre);
        $('#annuler_compet').click();
      }
    );

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
          f_nom           : { required:true , maxlength:60 },
          f_compet_nombre : { isWord:'item' }
        },
        messages :
        {
          f_nom           : { required:"nom manquant" , maxlength:"60 caractères maximum" },
          f_compet_nombre : { isWord:"item(s) manquant(s)" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element)
        {
          if(element.attr("id")=='f_compet_nombre') { element.next().next().after(error); }
          else {element.after(error);}
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
        switch (mode)
        {
          case 'ajouter':
            $('#table_action tbody tr td[colspan=3]').parent().remove(); // En cas de tableau avec une ligne vide pour la conformité XHTML ; IE8 bugue si on n'indique que [colspan]
            var position_script = responseHTML.lastIndexOf('<SCRIPT>');
            var new_tr = responseHTML.substring(0,position_script);
            $('#table_action tbody').prepend(new_tr);
            eval( responseHTML.substring(position_script+8) );
            break;
          case 'modifier':
            var position_script = responseHTML.lastIndexOf('<SCRIPT>');
            var new_tds = responseHTML.substring(0,position_script);
            $('#id_'+$('#f_id').val()).addClass("new").html(new_tds);
            eval( responseHTML.substring(position_script+8) );
            break;
          case 'supprimer':
            $('#id_'+$('#f_id').val()).remove();
            break;
        }
        tableau_maj();
        $.fancybox.close();
        mode = false;
      }
    }

  }
);
