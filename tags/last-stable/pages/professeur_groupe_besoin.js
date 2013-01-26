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
    $('table.form').tablesorter({ headers:{2:{sorter:false},3:{sorter:false},4:{sorter:false}} });
    function trier_tableau()
    {
      if($('table.form tbody tr td').length>1)
      {
        $('table.form').trigger('update');
        $('table.form').trigger('sorton',[sorting]);
      }
    }
    trier_tableau();

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Fonctions utilisées
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function afficher_form_gestion( mode , id , niveau_nom , nom , eleve_nombre , eleve_liste , prof_nombre , prof_liste )
    {
      $('#f_action').val(mode);
      $('#f_id').val(id);
      $('#f_niveau').html(select_niveau.replace('>'+niveau_nom,' selected>'+niveau_nom));
      $('#f_nom').val(nom);
      $('#f_eleve_nombre').val(eleve_nombre);
      $('#f_eleve_liste').val(eleve_liste);
      $('#f_prof_nombre').val(prof_nombre);
      $('#f_prof_liste').val(prof_liste);
      // pour finir
      $('#form_gestion h2').html(mode[0].toUpperCase() + mode.substring(1) + " un groupe de besoin");
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
     * Ajouter un groupe de besoin : mise en place du formulaire
     * @return void
     */
    var ajouter = function()
    {
      mode = $(this).attr('class');
      // Report des valeurs transmises via un formulaire depuis un tableau de synthèse bilan
      if(reception_todo)
      {
        reception_todo = false;
      }
      else
      {
        reception_users_texte  = 'aucun';
        reception_users_liste  = '';
      }
      // Afficher le formulaire
      afficher_form_gestion( mode , '' /*id*/ , '' /*niveau_nom*/ , '' /*nom*/ , reception_users_texte /*eleve_nombre*/ , reception_users_liste /*eleve_liste*/ , 'moi seul' /*prof_nombre*/ , '' /*prof_liste*/ );
    };

    /**
     * Modifier un groupe de besoin : mise en place du formulaire
     * @return void
     */
    var modifier = function()
    {
      mode = $(this).attr('class');
      var objet_tr     = $(this).parent().parent();
      var objet_tds    = objet_tr.find('td');
      // Récupérer les informations de la ligne concernée
      var id           = objet_tr.attr('id').substring(3);
      var niveau_nom   = objet_tds.eq(0).html();
      var nom          = objet_tds.eq(1).html();
      var eleve_nombre = objet_tds.eq(2).html();
      var prof_nombre  = objet_tds.eq(3).html();
      // enlever l'ordre du niveau caché
      niveau_nom = niveau_nom.substring(9,niveau_nom.length);
      // liste des élèves et des profs
      var eleve_liste   = tab_eleves[id];
      var prof_liste    = tab_profs[id];
      // Afficher le formulaire
      afficher_form_gestion( mode , id , unescapeHtml(niveau_nom) , unescapeHtml(nom) , eleve_nombre , eleve_liste , prof_nombre , prof_liste );
    };

    /**
     * Retirer un groupe de besoin : mise en place du formulaire
     * @return void
     */
    var supprimer = function()
    {
      mode = $(this).attr('class');
      var objet_tr     = $(this).parent().parent();
      var objet_tds    = objet_tr.find('td');
      // Récupérer les informations de la ligne concernée
      var id           = objet_tr.attr('id').substring(3);
      var nom          = objet_tds.eq(1).html();
      // Afficher le formulaire
      afficher_form_gestion( mode , id , '' /*niveau_nom*/ , unescapeHtml(nom) , '' /*eleve_nombre*/ , '' /*eleve_liste*/ , '' /*prof_nombre*/ , '' /*prof_liste*/ );
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
     * Choisir les élèves associés à un groupe : mise en place du formulaire
     * @return void
     */
    var choisir_eleve = function()
    {
      // Ne pas changer ici la valeur de "mode" (qui est à "ajouter" ou "modifier" ou "dupliquer").
      $('#zone_eleve li.li_m1 span.gradient_pourcent').html('');
      cocher_eleves( $('#f_eleve_liste').val() );
      // Afficher la zone
      $.fancybox( { 'href':'#zone_eleve' , onStart:function(){$('#zone_eleve').css("display","block");} , onClosed:function(){$('#zone_eleve').css("display","none");} , 'modal':true , 'centerOnScroll':true } );
    };

    /**
     * Choisir les professeurs associés à un groupe : mise en place du formulaire
     * @return void
     */
    var choisir_prof = function()
    {
      cocher_profs( $('#f_prof_liste').val() );
      // Afficher la zone
      $.fancybox( { 'href':'#zone_profs' , onStart:function(){$('#zone_profs').css("display","block");} , onClosed:function(){$('#zone_profs').css("display","none");} , 'modal':true , 'centerOnScroll':true } );
    };

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Appel des fonctions en fonction des événements ; live est utilisé pour prendre en compte les nouveaux éléments créés
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('q.ajouter').click( ajouter );
    $('q.modifier').live(  'click' , modifier );
    $('q.supprimer').live( 'click' , supprimer );
    $('#bouton_annuler').click( annuler );
    $('#bouton_valider').click( function(){formulaire.submit();} );
    $('#form_gestion input , #form_gestion select').live( 'keyup' , function(e){intercepter(e);} );

    $('q.choisir_eleve').live(  'click' , choisir_eleve );
    $('q.choisir_prof').live(   'click' , choisir_prof );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Cocher / décocher par lot des individus
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#prof_check_all').click
    (
      function()
      {
        $('.prof_liste').find('input:enabled').prop('checked',true);
        return false;
      }
    );
    $('#prof_uncheck_all').click
    (
      function()
      {
        $('.prof_liste').find('input:enabled').prop('checked',false);
        return false;
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le bouton pour fermer le cadre des élèves associés à un groupe (annuler / retour)
// Clic sur le bouton pour fermer le cadre des professeurs associés à un groupe (annuler / retour)
// ////////////////////////////////////////////////////////////////////////////////////////////////////
    $('#annuler_eleve , #annuler_profs').click
    (
      function()
      {
        $.fancybox( { 'href':'#form_gestion' , onStart:function(){$('#form_gestion').css("display","block");} , onClosed:function(){$('#form_gestion').css("display","none");} , 'modal':true , 'minWidth':600 , 'centerOnScroll':true } );
        return(false);
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le bouton pour valider le choix des élèves associés à un groupe
// ////////////////////////////////////////////////////////////////////////////////////////////////////
    $('#valider_eleve').click
    (
      function()
      {
        var liste = '';
        var nombre = 0;
        var test_doublon = new Array();
        $("#zone_eleve input[type=checkbox]:checked").each
        (
          function()
          {
            var eleve_id = $(this).val();
            if(typeof(test_doublon[eleve_id])=='undefined')
            {
              test_doublon[eleve_id] = true;
              liste += eleve_id+'_';
              nombre++;
            }
          }
        );
        var eleve_liste  = liste.substring(0,liste.length-1);
        var eleve_nombre = (nombre==0) ? 'aucun' : ( (nombre>1) ? nombre+' élèves' : nombre+' élève' ) ;
        $('#f_eleve_liste').val(eleve_liste);
        $('#f_eleve_nombre').val(eleve_nombre);
        $('#annuler_eleve').click();
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le bouton pour valider le choix des profs associés à un groupe
// ////////////////////////////////////////////////////////////////////////////////////////////////////
    $('#valider_profs').click
    (
      function()
      {
        var liste = '';
        var nombre = 0;
        $("#zone_profs input[type=checkbox]:checked").each
        (
          function()
          {
            liste += $(this).val()+'_';
            nombre++;
          }
        );
        liste  = (nombre==1) ? '' : liste.substring(0,liste.length-1) ;
        nombre = (nombre==1) ? 'moi seul' : nombre+' profs' ;
        $('#f_prof_liste').val(liste);
        $('#f_prof_nombre').val(nombre);
        $('#annuler_profs').click();
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
          f_niveau       : { required:true },
          f_nom          : { required:true , maxlength:20 },
          f_eleve_nombre : { accept:'élève|élèves' },
          f_prof_nombre  : { required:false }
        },
        messages :
        {
          f_niveau       : { required:"niveau manquant" },
          f_nom          : { required:"nom manquant" , maxlength:"20 caractères maximum" },
          f_eleve_nombre : { accept:"élève(s) manquant(s)" },
          f_prof_nombre  : { }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element)
        {
          if( (element.attr("id")=='f_eleve_nombre') || (element.attr("id")=='f_prof_nombre') ) { element.next().next().after(error); }
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
        switch (mode)
        {
          case 'ajouter':
            $('table.form tbody tr td[colspan]').parent().remove(); // En cas de tableau avec une ligne vide pour la conformité XHTML
            var position_script = responseHTML.lastIndexOf('<SCRIPT>');
            var niveau_nom = $('#f_niveau option:selected').text();
            var new_tr = responseHTML.substring(0,position_script).replace('<td>{{NIVEAU_NOM}}</td>','<td>'+'<i>'+tab_niveau_ordre[niveau_nom]+'</i>'+niveau_nom+'</td>');
            $('table.form tbody').prepend(new_tr);
            eval( responseHTML.substring(position_script+8) );
            break;
          case 'modifier':
            var position_script = responseHTML.lastIndexOf('<SCRIPT>');
            var niveau_nom = $('#f_niveau option:selected').text();
            var new_tds = responseHTML.substring(0,position_script).replace('<td>{{NIVEAU_NOM}}</td>','<td>'+'<i>'+tab_niveau_ordre[niveau_nom]+'</i>'+niveau_nom+'</td>');
            $('#id_'+$('#f_id').val()).addClass("new").html(new_tds);
            eval( responseHTML.substring(position_script+8) );
            break;
          case 'supprimer':
            $('#id_'+$('#f_id').val()).remove();
            break;
        }
        $.fancybox.close();
        mode = false;
        infobulle();
      }
    }

    // Initialiser l'affichage au démarrage
    if( reception_todo )
    {
      $('q.ajouter').click();
    }

  }
);
