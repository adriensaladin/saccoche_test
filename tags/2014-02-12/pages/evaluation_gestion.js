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
    var modification = false;
    var memo_pilotage = 'clavier';
    var memo_direction = 'down';
    var memo_input_id = false;
    var colonne = 1;
    var ligne   = 1;
    var nb_colonnes = 1;
    var nb_lignes   = 1;
    var nb_lignes_max = 20;

    // tri du tableau (avec jquery.tablesorter.js).
    if(TYPE=='groupe')
    {
      var sorting = [[0,1],[3,0]];
      $('#table_action').tablesorter({ headers:{0:{sorter:'date_fr'},1:{sorter:false},2:{sorter:false},4:{sorter:false},6:{sorter:false},7:{sorter:false},8:{sorter:false},9:{sorter:false}} });
    }
    else
    {
      var sorting = [[0,1],[5,0]];
      $('#table_action').tablesorter({ headers:{0:{sorter:'date_fr'},1:{sorter:false},2:{sorter:false},3:{sorter:false},4:{sorter:false},6:{sorter:false},7:{sorter:false},8:{sorter:false},9:{sorter:false}} });
    }
    var tableau_tri = function(){ $('#table_action').trigger( 'sorton' , [ sorting ] ); };
    var tableau_maj = function(){ $('#table_action').trigger( 'update' , [ true ] ); };
    tableau_tri();

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Fonctions utilisées
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function activer_boutons_upload(ref)
    {
      $('#zone_upload button').prop('disabled',false);
      if(!tab_sujets[ref])     {$('#bouton_supprimer_sujet').prop('disabled',true);}
      if(!tab_corriges[ref]) {$('#bouton_supprimer_corrige').prop('disabled',true);}
    }

    function afficher_form_gestion( mode , ref , date_fr , date_visible , date_autoeval , groupe_val , groupe_nom , eleve_nombre , eleve_liste , prof_nombre , prof_liste , description , compet_nombre , compet_liste , doc_sujet , doc_corrige , fini )
    {
      $('#f_action').val(mode);
      $('#f_ref').val(ref);
      $('#f_date').val(date_fr);
      if(TYPE=='groupe')
      {
        var selected_groupe = (mode=='ajouter') ? select_groupe.replace('value="'+groupe_val+'"','value="'+groupe_val+'" selected') : select_groupe.replace('>'+groupe_nom,' selected>'+groupe_nom) ;
        $('#f_groupe').html(selected_groupe);
      }
      else
      {
        $('#f_eleve_nombre').val(eleve_nombre);
        $('#f_eleve_liste').val(eleve_liste);
      }
      $('#f_prof_nombre').val(prof_nombre);
      $('#f_prof_liste').val(prof_liste);
      $('#f_description').val(description);
      $('#f_compet_nombre').val(compet_nombre);
      $('#f_compet_liste').val(compet_liste);
      $('#f_doc_sujet').val(doc_sujet);
      $('#f_doc_corrige').val(doc_corrige);
      $('#f_fini').val(fini);
      // date de visibilité
      if(date_visible=='identique')
      {
        $('#box_visible').prop('checked',true).next().show(0);
        $('#f_date_visible').val(date_fr).parent().css('display','none'); // plutôt que .hide(0) car suite au passage vers jQuery 1.11.0 un hide() sur un élément déjà caché provoque ici sa réapparition...
      }
      else
      {
        $('#box_visible').prop('checked',false).next().css('display','none'); // plutôt que .hide(0) car suite au passage vers jQuery 1.11.0 un hide() sur un élément déjà caché provoque ici sa réapparition...
        $('#f_date_visible').val(date_visible).parent().show(0);
      }
      // date fin auto-évaluation
      if(date_autoeval=='sans objet')
      {
        $('#box_autoeval').prop('checked',true).next().show(0);
        $('#f_date_autoeval').val('00/00/0000').parent().css('display','none'); // plutôt que .hide(0) car suite au passage vers jQuery 1.11.0 un hide() sur un élément déjà caché provoque ici sa réapparition...
      }
      else
      {
        $('#box_autoeval').prop('checked',false).next().css('display','none'); // plutôt que .hide(0) car suite au passage vers jQuery 1.11.0 un hide() sur un élément déjà caché provoque ici sa réapparition...
        $('#f_date_autoeval').val(date_autoeval).parent().show(0);
      }
      // pour finir
      $('#form_gestion h2').html(mode[0].toUpperCase() + mode.substring(1) + " une évaluation");
      if(mode!='supprimer')
      {
        $('#gestion_edit').show(0);
        $('#gestion_delete').hide(0);
      }
      else
      {
        $('#gestion_delete_identite').html(description);
        $('#gestion_edit').hide(0);
        $('#gestion_delete').show(0);
      }
      $('#alerte_groupe').css('display','none'); // plutôt que .hide(0) car suite au passage vers jQuery 1.11.0 un hide() sur un élément déjà caché provoque ici sa réapparition...
      $('#ajax_msg_gestion').removeAttr('class').html("");
      $('#form_gestion label[generated=true]').removeAttr('class').html("");
      $.fancybox( { 'href':'#form_gestion' , onStart:function(){$('#form_gestion').css("display","block");} , onClosed:function(){$('#form_gestion').css("display","none");} , 'modal':true , 'minWidth':600 , 'centerOnScroll':true } );
      if(mode=='ajouter') { $('#f_description').focus(); }
    }

    /**
     * Ajouter une évaluation : mise en place du formulaire
     * @return void
     */
    var ajouter = function()
    {
      mode = $(this).attr('class');
      // Report des valeurs transmises via un formulaire depuis un tableau de synthèse bilan (1er affichage seulement)
      if(reception_todo)
      {
        reception_todo = false;
      }
      else
      {
        reception_users_texte = 'aucun';
        reception_items_texte = 'aucun';
        reception_users_liste = '';
        reception_items_liste = '';
      }
      var groupe_val = (TYPE=='groupe') ? $('#f_aff_classe option:selected').val() : '' ;
      // Afficher le formulaire
      afficher_form_gestion( mode , '' /*ref*/ , input_date /*date_fr*/ , 'identique' /*date_visible*/ , 'sans objet' /*date_autoeval*/ , groupe_val , '' /*groupe_nom*/ , reception_users_texte /*eleve_nombre*/ , reception_users_liste /*eleve_liste*/ , 'moi seul' /*prof_nombre*/ , '' /*prof_liste*/ , '' /*description*/ , reception_items_texte /*compet_nombre*/ , reception_items_liste /*compet_liste*/ , '' /*doc_sujet*/ , '' /*doc_corrige*/ , '' /*fini*/ );
    };

    /**
     * Modifier | Dupliquer une évaluation : mise en place du formulaire
     * @return void
     */
    var modifier_dupliquer = function()
    {
      mode = $(this).attr('class');
      var objet_tds     = $(this).parent().parent().find('td');
      // Récupérer les informations de la ligne concernée
      var ref           = objet_tds.eq(9).attr('id').substring(7); // "devoir_" + ref
      var date_fr       = objet_tds.eq(0).html();
      var date_visible  = objet_tds.eq(1).html();
      var date_autoeval = objet_tds.eq(2).html();
      if(TYPE=='groupe')
      {
        var groupe_nom    = objet_tds.eq(3).html();
        var eleve_nombre  = '';
        var eleve_liste   = '';
      }
      else
      {
        var groupe_nom    = '';
        var eleve_nombre  = objet_tds.eq(3).html();
        var eleve_liste   = tab_eleves[ref];
      }
      var prof_nombre   = objet_tds.eq(4).html();
      var description   = objet_tds.eq(5).html();
      var compet_nombre = objet_tds.eq(6).html();
      var fini          =(objet_tds.eq(8).find('span').text()=='terminé') ? 'oui' : 'non' ;
      // liste des profs et des items
      var prof_liste    = tab_profs[ref];
      var compet_liste  = tab_items[ref];
      // Afficher le formulaire
      afficher_form_gestion( mode , ref , date_fr , date_visible , date_autoeval , '' /*groupe_val*/ , groupe_nom /* volontairement sans unescapeHtml() */ , eleve_nombre , eleve_liste , prof_nombre , prof_liste , unescapeHtml(description) , compet_nombre , compet_liste , tab_sujets[ref] , tab_corriges[ref] , fini );
    };

    /**
     * Supprimer une évaluation : mise en place du formulaire
     * @return void
     */
    var supprimer = function()
    {
      mode = $(this).attr('class');
      var objet_tds     = $(this).parent().parent().find('td');
      // Récupérer les informations de la ligne concernée
      var ref           = objet_tds.eq(9).attr('id').substring(7); // "devoir_" + ref
      var groupe_nom    = objet_tds.eq(3).html();
      var description   = objet_tds.eq(5).html();
      // Afficher le formulaire
      afficher_form_gestion( mode , ref , '' /*date_fr*/ , '' /*date_visible*/ , '' /*date_autoeval*/ , '' /*groupe_val*/ , '' /*groupe_nom*/ , '' /*eleve_nombre*/ , '' /*eleve_liste*/ , '' /*prof_nombre*/ , '' /*prof_liste*/ , unescapeHtml(description+' ('+groupe_nom+')') , '' /*compet_nombre*/ , '' /*compet_liste*/ , '' /*doc_sujet*/ , '' /*doc_corrige*/ , '' /*fini*/ );
    };

    /**
     * Imprimer un cartouche d'une évaluation : mise en place du formulaire
     * @return void
     */
    var imprimer = function()
    {
      mode = $(this).attr('class');
      var objet_tds     = $(this).parent().parent().find('td');
      // Récupérer les informations de la ligne concernée
      var ref           = objet_tds.eq(9).attr('id').substring(7); // "devoir_" + ref
      var date_fr       = objet_tds.eq(0).html();
      var groupe        = objet_tds.eq(3).html();
      var description   = objet_tds.eq(5).html();
      // Mettre les infos de côté
      $('#imprimer_ref').val(ref);
      $('#imprimer_date_fr').val(date_fr);
      $('#imprimer_groupe_nom').val(unescapeHtml(groupe));
      $('#imprimer_description').val(unescapeHtml(description));
      // Afficher la zone associée
      $('#titre_imprimer').html(groupe+' | '+date_fr+' | '+description);
      $('#ajax_msg_imprimer').removeAttr("class").html("&nbsp;");
      $('#zone_imprimer_retour').html("&nbsp;");
      $.fancybox( { 'href':'#zone_imprimer' , onStart:function(){$('#zone_imprimer').css("display","block");} , onClosed:function(){$('#zone_imprimer').css("display","none");} , 'modal':true , 'minWidth':800 , 'centerOnScroll':true } );
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
      if( (mode=='ajouter') || (mode=='dupliquer') || (mode=='modifier') || (mode=='supprimer') )
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
     * Saisir les items acquis par les élèves à une évaluation : chargement du formulaire
     * @return void
     */
    var saisir = function()
    {
      mode = $(this).attr('class');
      var objet_tds     = $(this).parent().parent().find('td');
      // Récupérer les informations de la ligne concernée
      var ref           = objet_tds.eq(9).attr('id').substring(7); // "devoir_" + ref
      var date_fr       = objet_tds.eq(0).html();
      var date_visible  = objet_tds.eq(1).html();
      var groupe        = objet_tds.eq(3).html();
      var description   = objet_tds.eq(5).html();
      var fini          =(objet_tds.eq(8).find('span').text()=='terminé') ? 'oui' : 'non' ;
      // Mettre les infos de côté
      $('#saisir_ref').val(ref);
      $('#saisir_date_fr').val(date_fr);
      $('#saisir_date_visible').val(date_visible);
      $('#saisir_description').val(unescapeHtml(description));
      $('#saisir_fini').val(fini);
      $.fancybox( '<label class="loader">'+'En cours&hellip;'+'</label>' , {'centerOnScroll':true} );
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action='+mode+'&f_ref='+ref+'&f_description='+encodeURIComponent(description)+'&f_groupe_nom='+encodeURIComponent(groupe)+'&f_date_fr='+encodeURIComponent(date_fr),
          dataType : "html",
          error : function(jqXHR, textStatus, errorThrown)
          {
            $.fancybox( '<label class="alerte">'+'Échec de la connexion !'+'</label>' , {'centerOnScroll':true} );
            return false;
          },
          success : function(responseHTML)
          {
            initialiser_compteur();
            var tab_response = responseHTML.split('<SEP>');
            if( (tab_response.length!=2) || (tab_response[0].substring(0,1)!='<') )
            {
              $.fancybox( '<label class="alerte">'+responseHTML+'</label>' , {'centerOnScroll':true} );
            }
            else
            {
              modification = false;
              $.fancybox.close();
              // Masquer le tableau ; Afficher la zone associée et remplir son contenu
              $('#form_prechoix , #table_action').hide('fast');
              $('#msg_import').removeAttr("class").html('&nbsp;');
              $('#titre_saisir').html(groupe+' | '+date_fr+' | '+description);
              $('#ajax_msg_saisir').removeAttr("class").html('&nbsp;');
              $('#table_saisir').html(tab_response[0]);
              $('#table_saisir tbody tr th img').css('display','none'); // .hide(0) s'avère bcp plus lent dans FF et pose pb si bcp élèves / items ...
              $('#export_file_saisir_tableau_scores_csv'   ).attr("href", './force_download.php?fichier='+'saisie_deportee_'+tab_response[1]+'.csv' );
              $('#export_file_saisir_tableau_scores_vierge').attr("href", url_export+'tableau_sans_notes_'+tab_response[1]+'.pdf' );
              colorer_cellules();
              format_liens('#table_saisir');
              $('#radio_'+memo_pilotage).click();
              $('#arrow_continue_'+memo_direction).click();
              nb_colonnes = $('#table_saisir thead th').length;
              nb_lignes   = $('#table_saisir tbody tr').length;
              $('#zone_saisir').css("display","block");
              if(nb_lignes>nb_lignes_max)
              {
                $('#table_saisir').thfloat( { onShow : function(table, block){ block.find('td').html(''); } } ); /* jQuery TH Float Plugin */
              }
              if(memo_pilotage=='clavier')
              {
                $('#C'+colonne+'L'+ligne).focus();
                if(isMobile)
                {
                  $('#cadre_tactile').show();
                }
              }
              else
              {
                $('#arrow_continue').hide();
              }
            }
          }
        }
      );
    };

    /**
     * Voir les items acquis par les élèves à une évaluation : chargement des données
     * @return void
     */
    var voir = function()
    {
      mode = $(this).attr('class');
      var objet_tds     = $(this).parent().parent().find('td');
      // Récupérer les informations de la ligne concernée
      var ref           = objet_tds.eq(9).attr('id').substring(7); // "devoir_" + ref
      var date_fr       = objet_tds.eq(0).html();
      var groupe        = objet_tds.eq(3).html();
      var description   = objet_tds.eq(5).html();
      $.fancybox( '<label class="loader">'+'En cours&hellip;'+'</label>' , {'centerOnScroll':true} );
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action='+mode+'&f_ref='+ref+'&f_date_fr='+encodeURIComponent(date_fr)+'&f_description='+encodeURIComponent(description)+'&f_groupe_nom='+encodeURIComponent(groupe),
          dataType : "html",
          error : function(jqXHR, textStatus, errorThrown)
          {
            $.fancybox( '<label class="alerte">'+'Échec de la connexion !'+'</label>' , {'centerOnScroll':true} );
            return false;
          },
          success : function(responseHTML)
          {
            initialiser_compteur();
            var tab_response = responseHTML.split('<SEP>');
            if( (tab_response.length!=2) || (tab_response[0].substring(0,1)!='<') )
            {
              $.fancybox( '<label class="alerte">'+responseHTML+'</label>' , {'centerOnScroll':true} );
            }
            else
            {
              $.fancybox.close();
              // Masquer le tableau ; Afficher la zone associée et remplir son contenu
              $('#form_prechoix , #table_action').hide('fast');
              $('#titre_voir').html(groupe+' | '+date_fr+' | '+description);
              $('#ajax_msg_voir').removeAttr("class").html('&nbsp;');
              $('#table_voir').html(tab_response[0]);
              $('#table_voir tbody tr th img').css('display','none'); // .hide(0) s'avère bcp plus lent dans FF et pose pb si bcp élèves / items ...
              format_liens('#table_voir');
              $('#export_file_voir_tableau_scores_csv'    ).attr("href", './force_download.php?fichier='+'saisie_deportee_'+tab_response[1]+'.csv' );
              $('#export_file_voir_tableau_scores_vierge' ).attr("href", url_export+'tableau_sans_notes_'           +tab_response[1]+'.pdf' );
              $('#export_file_voir_tableau_scores_couleur').attr("href", url_export+'tableau_avec_notes_couleur_'   +tab_response[1]+'.pdf' );
              $('#export_file_voir_tableau_scores_gris'   ).attr("href", url_export+'tableau_avec_notes_monochrome_'+tab_response[1]+'.pdf' );
              $('#table_voir tbody td').css({"background-color":"#DDF","text-align":"center","vertical-align":"middle","font-size":"110%"});
              nb_lignes   = $('#table_voir tbody tr').length;
              $('#zone_voir').css("display","block");
              if(nb_lignes>nb_lignes_max)
              {
                $('#table_voir').thfloat( { onShow : function(table, block){ block.find('td').html(''); } } ); /* jQuery TH Float Plugin */
              }
            }
          }
        }
      );
    };

    /**
     * Choisir les items associés à une évaluation : mise en place du formulaire
     * @return void
     */
    var choisir_compet = function()
    {
      // Ne pas changer ici la valeur de "mode" (qui est à "ajouter" ou "modifier" ou "dupliquer").
      $('#f_selection_items option:first').prop('selected',true);
      cocher_matieres_items( $('#f_compet_liste').val() );
      if(mode=='modifier') {$('#alerte_items').show();}
      else                 {$('#alerte_items').hide();}
      // Afficher la zone
      $.fancybox( { 'href':'#zone_matieres_items' , onStart:function(){$('#zone_matieres_items').css("display","block");} , onClosed:function(){$('#zone_matieres_items').css("display","none");} , 'modal':true , 'centerOnScroll':true } );
      $(document).tooltip("destroy");infobulle(); // Sinon, bug avec l'infobulle contenu dans le fancybox qui ne disparait pas au clic...
    };

    /**
     * Choisir les élèves associés à une évaluation : mise en place du formulaire (uniquement pour des élèves sélectionnés)
     * @return void
     */
    var choisir_eleve = function()
    {
      // Ne pas changer ici la valeur de "mode" (qui est à "ajouter" ou "modifier" ou "dupliquer").
      $('#zone_eleve q.date_calendrier').show();
      $('#zone_eleve li.li_m1 span.gradient_pourcent').html('');
      $('#msg_indiquer_eleves_deja').removeAttr("class").html('');
      cocher_eleves( $('#f_eleve_liste').val() );
      if(mode=='modifier') {$('#alerte_eleves').show();}
      else                 {$('#alerte_eleves').hide();}
      // Afficher la zone
      $.fancybox( { 'href':'#zone_eleve' , onStart:function(){$('#zone_eleve').css("display","block");} , onClosed:function(){$('#zone_eleve').css("display","none");} , 'modal':true , 'centerOnScroll':true } );
      $(document).tooltip("destroy");infobulle(); // Sinon, bug avec l'infobulle contenu dans le fancybox qui ne disparait pas au clic...
    };

    /**
     * Réordonner les items associés à une évaluation : mise en place du formulaire
     * @return void
     */
    var ordonner = function()
    {
      mode = $(this).attr('class');
      var objet_tds     = $(this).parent().parent().find('td');
      // Récupérer les informations de la ligne concernée
      var ref           = objet_tds.eq(9).attr('id').substring(7); // "devoir_" + ref
      var date_fr       = objet_tds.eq(0).html();
      var groupe        = objet_tds.eq(3).html();
      var description   = objet_tds.eq(5).html();
      // Mettre les infos de côté
      $('#ordre_ref').val(ref);
      // Afficher la zone associée après avoir chargé son contenu
      $('#titre_ordonner').html(groupe+' | '+date_fr+' | '+description);
      $('#ajax_msg_ordonner').removeAttr("class").html('&nbsp;');
      $.fancybox( '<label class="loader">'+'En cours&hellip;'+'</label>' , {'centerOnScroll':true} );
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action='+mode+'&f_ref='+ref,
          dataType : "html",
          error : function(jqXHR, textStatus, errorThrown)
          {
            $.fancybox( '<label class="alerte">'+'Échec de la connexion !'+'</label>' , {'centerOnScroll':true} );
            return false;
          },
          success : function(responseHTML)
          {
            initialiser_compteur();
            if(responseHTML.substring(0,4)!='<li ')
            {
              $.fancybox( '<label class="alerte">'+responseHTML+'</label>' , {'centerOnScroll':true} );
            }
            else
            {
              modification = false;
              $('#sortable').html(responseHTML).sortable( { cursor:'n-resize' , update:function(event,ui){modif_ordre();} } );
              // Afficher la zone
              $.fancybox( { 'href':'#zone_ordonner' , onStart:function(){$('#zone_ordonner').css("display","block");} , onClosed:function(){$('#zone_ordonner').css("display","none");} , 'modal':true , 'centerOnScroll':true } );
            }
          }
        }
      );
    };
    function modif_ordre()
    {
      if(modification==false)
      {
        $('#fermer_zone_ordonner').removeAttr("class").addClass("annuler").html('Annuler / Retour');
        modification = true;
        $('#ajax_msg_ordonner').removeAttr("class").html("&nbsp;");
      }
    }

    /**
     * Voir les répartitions des élèves à une évaluation : chargement des données
     * @return void
     */
    var voir_repart = function()
    {
      mode = $(this).attr('class');
      var objet_tds     = $(this).parent().parent().find('td');
      // Récupérer les informations de la ligne concernée
      var ref           = objet_tds.eq(9).attr('id').substring(7); // "devoir_" + ref
      var date_fr       = objet_tds.eq(0).html();
      var groupe        = objet_tds.eq(3).html();
      var description   = objet_tds.eq(5).html();
      // Afficher la zone associée après avoir chargé son contenu
      $('#titre_voir_repart').html(groupe+' | '+date_fr+' | '+description);
      $.fancybox( '<label class="loader">'+'En cours&hellip;'+'</label>' , {'centerOnScroll':true} );
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action='+mode+'&f_ref='+ref+'&f_date_fr='+encodeURIComponent(date_fr)+'&f_description='+encodeURIComponent(description)+'&f_groupe_nom='+encodeURIComponent(groupe),
          dataType : "html",
          error : function(jqXHR, textStatus, errorThrown)
          {
            $.fancybox( '<label class="alerte">'+'Échec de la connexion !'+'</label>' , {'centerOnScroll':true} );
            return false;
          },
          success : function(responseHTML)
          {
            initialiser_compteur();
            var tab_response = responseHTML.split('<SEP>');
            if( tab_response.length!=3 )
            {
              $.fancybox( '<label class="alerte">'+responseHTML+'</label>' , {'centerOnScroll':true} );
            }
            else
            {
              // Afficher la zone
              $('#table_voir_repart1').html(tab_response[0]);
              $('#table_voir_repart2').html(tab_response[1]);
              format_liens('#zone_voir_repart');
              $('#export_voir_repart_quantitative_couleur').attr("href", url_export+'repartition_quantitative_couleur_'+tab_response[2]+'.pdf' );
              $('#export_voir_repart_quantitative_gris'   ).attr("href", url_export+'repartition_quantitative_monochrome_'+tab_response[2]+'.pdf' );
              $('#export_voir_repart_nominative_couleur'  ).attr("href", url_export+'repartition_nominative_couleur_'+tab_response[2]+'.pdf' );
              $('#export_voir_repart_nominative_gris'     ).attr("href", url_export+'repartition_nominative_monochrome_'+tab_response[2]+'.pdf' );
              $('#table_voir_repart1 tbody td').css({"background-color":"#DDF","font-weight":"normal","text-align":"center"});
              $('#table_voir_repart2 tbody td').css({"background-color":"#DDF","font-weight":"normal","font-size":"85%"});
              $.fancybox( { 'href':'#zone_voir_repart' , onStart:function(){$('#zone_voir_repart').css("display","block");} , onClosed:function(){$('#zone_voir_repart').css("display","none");} , 'centerOnScroll':true } );
            }
          }
        }
      );
    };

    /**
     * Choisir les professeurs associés à une évaluation : mise en place du formulaire
     * @return void
     */
    var choisir_prof = function()
    {
      cocher_profs( $('#f_prof_liste').val() );
      // Afficher la zone
      $.fancybox( { 'href':'#zone_profs' , onStart:function(){$('#zone_profs').css("display","block");} , onClosed:function(){$('#zone_profs').css("display","none");} , 'modal':true , 'centerOnScroll':true } );
      $(document).tooltip("destroy");infobulle(); // Sinon, bug avec l'infobulle contenu dans le fancybox qui ne disparait pas au clic...
    };

    /**
     * Uploader les documents associés à une évaluation : mise en place du formulaire
     * @return void
     */
    var uploader_doc = function()
    {
      mode = $(this).attr('class');
      var objet_tds     = $(this).parent().parent().find('td');
      // Récupérer les informations de la ligne concernée
      var ref           = objet_tds.eq(9).attr('id').substring(7); // "devoir_" + ref
      var date_fr       = objet_tds.eq(0).html();
      var groupe        = objet_tds.eq(3).html();
      var description   = objet_tds.eq(5).html();
      // Sujet & Corrigé
      var img_sujet     = (tab_sujets[ref])   ? '<a href="'+tab_sujets[ref]+'" target="_blank"><img alt="sujet" src="./_img/document/sujet_oui.png" title="Sujet disponible." /></a>' : '<img alt="sujet" src="./_img/document/sujet_non.png" />' ;
      var img_corrige   = (tab_corriges[ref]) ? '<a href="'+tab_corriges[ref]+'" target="_blank"><img alt="corrigé" src="./_img/document/corrige_oui.png" title="Corrigé disponible." /></a>' : '<img alt="corrigé" src="./_img/document/corrige_non.png" />' ;
      // Renseigner les champs dynamique affichés
      $('#titre_upload').html(groupe+' | '+date_fr+' | '+description);
      $('#ajax_document_upload').removeAttr("class").html("");
      $('#span_sujet').html(img_sujet);
      $('#span_corrige').html(img_corrige);
      activer_boutons_upload(ref);
      // maj du paramètre AjaxUpload (les paramètres n'étant pas directement modifiables...)
      uploader_sujet['_settings']['data']['f_ref']   = ref;
      uploader_corrige['_settings']['data']['f_ref'] = ref;
      // Afficher la zone
      $.fancybox( { 'href':'#zone_upload' , onStart:function(){$('#zone_upload').css("display","block");} , onClosed:function(){$('#zone_upload').css("display","none");} , 'modal':true , 'centerOnScroll':true } );
    };

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Appel des fonctions en fonction des événements ; live est utilisé pour prendre en compte les nouveaux éléments créés
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_action').on( 'click' , 'q.ajouter'        , ajouter );
    $('#table_action').on( 'click' , 'q.modifier'       , modifier_dupliquer );
    $('#table_action').on( 'click' , 'q.dupliquer'      , modifier_dupliquer );
    $('#table_action').on( 'click' , 'q.supprimer'      , supprimer );
    $('#table_action').on( 'click' , 'q.ordonner'       , ordonner );
    $('#table_action').on( 'click' , 'q.imprimer'       , imprimer );
    $('#table_action').on( 'click' , 'q.saisir'         , saisir );
    $('#table_action').on( 'click' , 'q.voir'           , voir );
    $('#table_action').on( 'click' , 'q.voir_repart'    , voir_repart );
    $('#table_action').on( 'click' , 'q.uploader_doc'   , uploader_doc );

    $('#form_gestion').on( 'click' , 'q.choisir_compet' , choisir_compet );
    $('#form_gestion').on( 'click' , 'q.choisir_eleve'  , choisir_eleve );
    $('#form_gestion').on( 'click' , 'q.choisir_prof'   , choisir_prof );
    $('#form_gestion').on( 'click' , '#bouton_annuler'  , annuler );
    $('#form_gestion').on( 'click' , '#bouton_valider'  , function(){formulaire.submit();} );
    $('#form_gestion').on( 'keyup' , 'input,select'     , function(e){intercepter(e);} );

    $('#zone_upload'  ).on( 'click' , '#fermer_zone_upload'   , annuler );
    $('#zone_ordonner').on( 'click' , '#fermer_zone_ordonner' , annuler );
    $('#zone_imprimer').on( 'click' , '#fermer_zone_imprimer' , annuler );

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
    // Clic sur le checkbox pour choisir ou non une date visible différente de la date du devoir
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#box_visible').click
    (
      function()
      {
        if($(this).is(':checked'))
        {
          $('#f_date_visible').val($('#f_date').val());
          $(this).next().show(0).next().hide(0);
        }
        else
        {
          $(this).next().hide(0).next().show(0);
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le checkbox pour choisir ou non une date d'auto-évaluation
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#box_autoeval').click
    (
      function()
      {
        if($(this).is(':checked'))
        {
          $('#f_date_autoeval').val('00/00/0000');
          $(this).next().show(0).next().hide(0);
        }
        else
        {
          $(this).next().hide(0).next().show(0);
          $('#f_date_autoeval').val(input_autoeval);
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Reporter la date visible si modif date du devoir et demande dates identiques
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#f_date').change
    (
      function()
      {
        if($('#box_visible').is(':checked'))
        {
          $('#f_date_visible').val($('#f_date').val());
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Alerte si modification de groupe d'une évaluation
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#f_groupe').change
    (
      function()
      {
        if(mode=='modifier')
        {
          $('#alerte_groupe').show();
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le bouton pour fermer le cadre des items associés à une évaluation (annuler / retour)
    // Clic sur le bouton pour fermer le cadre des élèves associés à une évaluation (annuler / retour) (uniquement pour des élèves sélectionnés)
    // Clic sur le bouton pour fermer le cadre des professeurs associés à une évaluation (annuler / retour)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#annuler_compet , #annuler_eleve , #annuler_profs').click
    (
      function()
      {
        $.fancybox( { 'href':'#form_gestion' , onStart:function(){$('#form_gestion').css("display","block");} , onClosed:function(){$('#form_gestion').css("display","none");} , 'modal':true , 'minWidth':600 , 'centerOnScroll':true } );
        return(false);
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le bouton pour fermer le formulaire servant à saisir les acquisitions des élèves à une évaluation
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function fermer_zone_saisir()
    {
      $('#titre_saisir').html("");
      $('#table_saisir').html("<tbody><tr><td></td></tr></tbody>");
      if(nb_lignes>nb_lignes_max)
      {
        $('#table_saisir').thfloat('destroy'); /* jQuery TH Float Plugin */
      }
      if(isMobile)
      {
        $('#cadre_tactile').hide();
      }
      $('#zone_saisir').css("display","none");
      $('#form_prechoix , #table_action').show('fast');
      return(false);
    }

    $('#fermer_zone_saisir').click
    (
      function()
      {
        if(!modification)
        {
          fermer_zone_saisir();
        }
        else
        {
          $.fancybox( { 'href':'#zone_confirmer_fermer_saisir' , onStart:function(){$('#zone_confirmer_fermer_saisir').css("display","block");} , onClosed:function(){$('#zone_confirmer_fermer_saisir').css("display","none");} , 'modal':true , 'centerOnScroll':true } );
          return(false);
        }
      }
    );

    $('#confirmer_fermer_zone_saisir').click
    (
      function()
      {
        $.fancybox.close();
        fermer_zone_saisir();
      }
    );

    $('#annuler_fermer_zone_saisir').click
    (
      function()
      {
        $.fancybox.close();
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le bouton pour fermer le bloc pour voir les acquisitions des élèves à une évaluation
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#fermer_zone_voir').click
    (
      function()
      {
        $('#titre_voir').html("");
        $('#table_voir').html("<tbody><tr><td></td></tr></tbody>");
        if(nb_lignes>nb_lignes_max)
        {
          $('#table_voir').thfloat('destroy'); /* jQuery TH Float Plugin */
        }
        $('#zone_voir').css("display","none");
        $('#form_prechoix , #table_action').show('fast');
        return(false);
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le bouton pour valider le choix des items associés à une évaluation
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
    // Clic sur le bouton pour valider le choix des élèves associés à une évaluation (uniquement pour des élèves sélectionnés)
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
    // Clic sur le bouton pour valider le choix des profs associés à une évaluation
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
        nombre = (nombre==1) ? 'moi seul' : nombre+' collègues' ;
        $('#f_prof_liste').val(liste);
        $('#f_prof_nombre').val(nombre);
        $('#annuler_profs').click();
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Demande pour sélectionner d'une liste d'items mémorisés
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#f_selection_items').change
    (
      function()
      {
        cocher_matieres_items( $("#f_selection_items").val() );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le bouton pour mémoriser un choix d'items
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#f_enregistrer_items').click
    (
      function()
      {
        memoriser_selection_matieres_items( $("#f_liste_items_nom").val() );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Fonction pour colorer les cases du tableau de saisie des items déjà enregistrés
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function colorer_cellules()
    {
      $("#table_saisir tbody td input").each
      (
        function ()
        {
          if( ($(this).val()!='X') && ($(this).val()!='REQ') )
          {
            $(this).parent().css("background-color","#AAF");
          }
          else
          {
            $(this).parent().css("background-color","#EEF");
          }
        }
      );
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Validation de la demande de génération d'un cartouche pour une évaluation
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#valider_imprimer').click
    (
      function()
      {
        $('#zone_imprimer button').prop('disabled',true);
        $('#ajax_msg_imprimer').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $('#zone_imprimer_retour').html("&nbsp;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=imprimer_cartouche'+'&'+$("#zone_imprimer").serialize(),
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#zone_imprimer button').prop('disabled',false);
              $('#ajax_msg_imprimer').removeAttr("class").addClass("alerte").html('Échec de la connexion !');
              return false;
            },
            success : function(responseHTML)
            {
              initialiser_compteur();
              $('#zone_imprimer button').prop('disabled',false);
              if(responseHTML.substring(0,6)!='<hr />')
              {
                $('#ajax_msg_imprimer').removeAttr("class").addClass("alerte").html(responseHTML);
              }
              else
              {
                $('#ajax_msg_imprimer').removeAttr("class").addClass("valide").html("Cartouches générés !");
                $('#zone_imprimer_retour').html(responseHTML);
                format_liens('#zone_imprimer_retour');
              }
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Demande d'indiquer la liste des élèves associés à une évaluation de même nom (uniquement pour des élèves sélectionnés)
    // Reprise d'un développement initié par Alain Pottier <alain.pottier613@orange.fr>
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#indiquer_eleves_deja').click
    (
      function()
      {
        if(!$('#f_description').val())
        {
          $('#msg_indiquer_eleves_deja').removeAttr("class").addClass("erreur").html('évaluation sans nom');
          return false;
        }
        var f_date_debut = $('#f_date_deja').val();
        if(!f_date_debut)
        {
          $('#msg_indiquer_eleves_deja').removeAttr("class").addClass("erreur").html('date manquante');
          return false;
        }
        if(!test_dateITA(f_date_debut))
        {
          $('#msg_indiquer_eleves_deja').removeAttr("class").addClass("erreur").html('date JJ/MM/AAAA incorrecte');
          return false;
        }
        $('button').prop('disabled',true);
        $('#msg_indiquer_eleves_deja').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=indiquer_eleves_deja'+'&f_description='+encodeURIComponent($('#f_description').val())+'&f_date_debut='+encodeURIComponent(f_date_debut),
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('button').prop('disabled',false);
              $('#msg_indiquer_eleves_deja').removeAttr("class").addClass("alerte").html('Échec de la connexion !');
              return false;
            },
            success : function(responseHTML)
            {
              initialiser_compteur();
              $('button').prop('disabled',false);
              if(responseHTML.substring(0,3)!='ok,')
              {
                $('#msg_indiquer_eleves_deja').removeAttr("class").addClass("alerte").html(responseHTML);
              }
              else
              {
                // On récupère les associations élèves -> dates
                var tab_dates   = new Array();
                var tab_groupes = new Array();
                var tab_infos = responseHTML.substring(3).split(',');
                var memo_groupe_id = 0;
                for(i in tab_infos)
                {
                  var tab = tab_infos[i].split('_');
                  tab_dates[tab[0]] = tab[1];
                }
                // Passer en revue les lignes élève
                $("#zone_eleve input[type=checkbox]").each
                (
                  function()
                  {
                    var tab_ids = $(this).attr('id').split('_');
                    var eleve_id  = tab_ids[1];
                    var groupe_id = tab_ids[2];
                    var eleve_date = tab_dates[eleve_id];
                    if(groupe_id!=memo_groupe_id)
                    {
                      memo_groupe_id = groupe_id;
                      tab_groupes[groupe_id] = new Array(0,0);
                    }
                    $(this).next('label').removeAttr('class').next('span').html('');
                    if(typeof(eleve_date)=='undefined')
                    {
                      tab_groupes[groupe_id][0]++;
                    }
                    else
                    {
                      $(this).next('label').addClass('deja grey').next('span').html('<span>'+eleve_date+'</span>');
                      tab_groupes[groupe_id][1]++;
                    }
                  }
                );
                // Passer en revue les bilans par groupe
                for(groupe_id in tab_groupes)
                {
                  var nb_eleves = tab_groupes[groupe_id][0]+tab_groupes[groupe_id][1];
                  var pourcentage = (nb_eleves) ? (100*tab_groupes[groupe_id][1]/nb_eleves).toFixed(0) : 0 ;
                  switch (pourcentage)
                  {
                    case '0'   : var couleur = '#C00';break;
                    case '100' : var couleur = '#080';break;
                    default    : var couleur = '#333';break;
                  }
                  $('#groupe_'+groupe_id).css('color',couleur).html('<span class="gradient_outer"><span class="gradient_inner" style="width:'+pourcentage+'px"></span></span>'+pourcentage+'%');
                }
                $('#msg_indiquer_eleves_deja').removeAttr("class").addClass("valide").html("Affichage actualisé.");
              }
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Choix du mode de pilotage pour la saisie des résultats
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_saisir').on
    (
      'click',
      'input[name=mode_saisie]',
      function()
      {
        memo_pilotage = $(this).val();
        if(memo_pilotage=='clavier')
        {
          $('#arrow_continue').show(0);
          $('#C'+colonne+'L'+ligne).focus();
          if(isMobile)
          {
            $('#cadre_tactile').show();
          }
        }
        else
        {
          $('#arrow_continue').hide(0);
          if(isMobile)
          {
            $('#cadre_tactile').hide();
          }
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Choix du sens de parcours pour la saisie des résultats (si pilotage au clavier)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_saisir').on
    (
      'click',
      'input[name=arrow_continue]',
      function()
      {
        memo_direction = $(this).val();
        $('#C'+colonne+'L'+ligne).focus();
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Choix de rétrécir ou pas les colonnes sur #table_saisir
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_saisir').on
    (
      'click',
      '#check_largeur',
      function()
      {
        var condense = ($(this).is(':checked')) ? 'v' : 'h' ; // 'h' ou 'v' pour horizontal (non condensé) ou vertical (condensé)
        $('#table_saisir tbody').removeAttr("class").addClass(condense);
        $("#table_saisir thead tr th img").each
        (
          function ()
          {
            img_src_old = $(this).attr('src');
            img_src_new = (condense=='v') ? img_src_old.substring(0,img_src_old.length-3) : img_src_old+'&br' ;
            $(this).attr('src',img_src_new);
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Choix de rétrécir ou pas les colonnes sur #table_voir
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_voir').on
    (
      'click',
      '#check_largeur',
      function()
      {
        var condense = ($(this).is(':checked')) ? 'v' : 'h' ; // 'h' ou 'v' pour horizontal (non condensé) ou vertical (condensé)
        $("#table_voir thead tr th img").each
        (
          function ()
          {
            img_src_old = $(this).attr('src');
            img_src_new = (condense=='v') ? img_src_old.substring(0,img_src_old.length-3) : img_src_old+'&br' ;
            $(this).attr('src',img_src_new);
          }
        );
        $("#table_voir tbody tr td img").each
        (
          function ()
          {
            img_src_old = $(this).attr('src');
            img_src_new = (condense=='v') ? img_src_old.replace('/h/','/v/') : img_src_old.replace('/v/','/h/') ; // Pas besoin d'expression régulière car une seule occurence
            $(this).attr('src',img_src_new);
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Choix de rétrécir ou pas les lignes sur #table_saisir ou #table_voir
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $(document).on
    (
      'click',
      '#check_hauteur',
      function()
      {
        var table_id = $(this).closest('table').attr('id');
        if($(this).is(':checked'))
        {
          $("#"+table_id+" tbody tr th div").css('display','none');         // .hide(0) s'avère bcp plus lent dans FF et pose pb si bcp élèves / items ...
          $("#"+table_id+" tbody tr th img").css('display','inline-block'); // .show(0) s'avère bcp plus lent dans FF et pose pb si bcp élèves / items ...
        }
        else
        {
          $("#"+table_id+" tbody tr th img").css('display','none');  // .hide(0) s'avère bcp plus lent dans FF et pose pb si bcp élèves / items ...
          $("#"+table_id+" tbody tr th div").css('display','block'); // .show(0) s'avère bcp plus lent dans FF et pose pb si bcp élèves / items ...
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Gérer la saisie des acquisitions au clavier ou avec un dispositif tactile
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function focus_cellule_suivante_en_evitant_sortie_tableau()
    {
      if(colonne==0)
      {
        colonne = nb_colonnes;
        ligne = (ligne!=1) ? ligne-1 : nb_lignes ;
      }
      else if(colonne>nb_colonnes)
      {
        colonne = 1;
        ligne = (ligne!=nb_lignes) ? ligne+1 : 1 ;
      }
      else if(ligne==0)
      {
        ligne = nb_lignes;
        colonne = (colonne!=1) ? colonne-1 : nb_colonnes ;
        if(nb_lignes>nb_lignes_max)
        {
          window.scrollTo(0,10000); /* jQuery TH Float Plugin */
        }
      }
      else if(ligne>nb_lignes)
      {
        ligne = 1;
        colonne = (colonne!=nb_colonnes) ? colonne+1 : 1 ;
        if(nb_lignes>nb_lignes_max)
        {
          window.scrollTo(0,150); /* jQuery TH Float Plugin */
        }
      }
      var new_id = 'C'+colonne+'L'+ligne;
      $('#'+new_id).focus();
    }

    $('#cadre_tactile').on
    (
      'click',
      'kbd',
      function()
      {
        var code = parseInt( $(this).attr('id').substring(4) , 10 ); // "kbd_" + ref
        navigation_clavier(code);
      }
    );

    $('#table_saisir').on
    (
      'click',
      'tbody td input',
      function(e)
      {
        var cellule_id  = $(this).attr("id");
        colonne = parseInt(cellule_id.substring(1,cellule_id.indexOf('L')),10);
        ligne   = parseInt(cellule_id.substring(cellule_id.indexOf('L')+1),10);
      }
    );

    $('#table_saisir').on
    (
      'keydown',  // keydown au lieu de keyup permet de laisser appuyer sur la touche pour répéter une action
      'tbody td input',
      function(e)
      {
        if(memo_pilotage=='clavier')
        {
          var cellule_id  = $(this).attr("id");
          colonne = parseInt(cellule_id.substring(1,cellule_id.indexOf('L')),10);
          ligne   = parseInt(cellule_id.substring(cellule_id.indexOf('L')+1),10);
          navigation_clavier(e.which);
        }
      }
    );

    function navigation_clavier(touche_code)
    {
      var findme = '.'+touche_code+'.';
      var endroit_report_note = 'cellule';
      if('.8.46.49.50.51.52.65.68.69.70.78.80.82.97.98.99.100.'.indexOf(findme)!=-1)
      {
        // Une touche d'item a été pressée
        switch (touche_code)
        {
          case   8: var note = 'X';    break; // backspace
          case  46: var note = 'X';    break; // suppr
          case  97: var note = 'RR';   break; // 1
          case  49: var note = 'RR';   break; // 1 (&)
          case  98: var note = 'R';    break; // 2
          case  50: var note = 'R';    break; // 2 (é)
          case  99: var note = 'V';    break; // 3
          case  51: var note = 'V';    break; // 3 (")
          case 100: var note = 'VV';   break; // 4
          case  52: var note = 'VV';   break; // 4 (')
          case  65: var note = 'ABS';  break; // A
          case  68: var note = 'DISP'; break; // D
          case  78: var note = 'NN';   break; // N
          case  69: var note = 'NE';   break; // E
          case  70: var note = 'NF';   break; // F
          case  82: var note = 'NR';   break; // R
          case  80: var note = 'REQ';  break; // P
        }
        endroit_report_note = $("input[name=f_endroit_report_note]:checked").val();
        if( (typeof(endroit_report_note)=='undefined') || (endroit_report_note=='cellule') )
        {
          // pour une seule case
          var cellule_obj = $('#C'+colonne+'L'+ligne);
          cellule_obj.val(note).removeAttr("class").addClass(note);
          cellule_obj.parent().css("background-color","#F6D");
          if(memo_direction=='down')
          {
            ligne++;
          }
          else
          {
            colonne++;
          }
        }
        else if(endroit_report_note=='tableau')
        {
          // pour toutes les cases vides du tableau
          $("#table_saisir tbody td input").each
          (
            function()
            {
              if($(this).val()=='X')
              {
                $(this).val(note).removeAttr("class").addClass(note);
                $(this).parent().css("background-color","#F6D");
              }
            }
          );
        }
        else if(endroit_report_note=='colonne')
        {
          // pour toutes les cases vides d'une colonne
          $("#table_saisir tbody td input[id^=C"+colonne+"L]").each
          (
            function()
            {
              if($(this).val()=='X')
              {
                $(this).val(note).removeAttr("class").addClass(note);
                $(this).parent().css("background-color","#F6D");
              }
            }
          );
          colonne++;
        }
        else if(endroit_report_note=='ligne')
        {
          // pour toutes les cases vides d'une ligne
          $("#table_saisir tbody td input[id$=L"+ligne+"]").each
          (
            function()
            {
              if($(this).val()=='X')
              {
                $(this).val(note).removeAttr("class").addClass(note);
                $(this).parent().css("background-color","#F6D");
              }
            }
          );
          ligne++;
        }
        if(modification==false)
        {
          $('#fermer_zone_saisir').removeAttr("class").addClass("annuler").html('Annuler / Retour');
          $('#kbd_27').removeAttr("class").addClass("img annuler");
          modification = true;
        }
        $('#ajax_msg_saisir').removeAttr("class").html("&nbsp;");
        focus_cellule_suivante_en_evitant_sortie_tableau();
        endroit_report_note = 'cellule';
      }
      else if('.37.38.39.40.'.indexOf(findme)!=-1)
      {
        // Une flèche a été pressée
        switch (touche_code)
        {
          case 37: colonne--; break; // flèche gauche
          case 38: ligne--;   break; // flèche haut
          case 39: colonne++; break; // flèche droit
          case 40: ligne++;   break; // flèche bas
        }
        focus_cellule_suivante_en_evitant_sortie_tableau();
      }
      else if(touche_code==13)  // touche entrée
      {
        // La touche entrée a été pressée
        $('#valider_saisir').click();
      }
      else if(touche_code==27)
      {
        // La touche escape a été pressée
        $('#fermer_zone_saisir').click();
      }
      else if('.67.76.84.'.indexOf(findme)!=-1)
      {
        // Une touche de préparation de modification par lot a été pressée
        switch (touche_code)
        {
          case 67: endroit_report_note = 'colonne'; break; // C
          case 76: endroit_report_note = 'ligne';   break; // L
          case 84: endroit_report_note = 'tableau'; break; // T
        }
      }
      else if('.16.17.18.20.144.'.indexOf(findme)!=-1)
      {
        // Une touche Shift / Ctrl / Alt / CapsLock / VerrNum [*] a été pressée
        // [*] 144 est aussi un signal particulier envoyé par un clavier étendu en parallèle à chaque appui sur une touche du pavé numérique pour signaler qu'il est actif
        endroit_report_note = $("input[name=f_endroit_report_note]:checked").val();
      }
      $('#f_report_'+endroit_report_note).prop('checked',true);
      return false; // Evite notamment qu'IE fasse "page précédente" si on appuie sur backspace.
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Gérer la saisie des acquisitions à la souris
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Remplacer la cellule par les images de choix
    $('#table_saisir').on
    (
      'mouseover',
      'tbody td.td_clavier',
      function()
      {
        if(memo_pilotage=='souris')
        {
          // Test si un précédent td n'a pas été remis en place (js a du mal à suivre le mouseleave sinon)
          if(memo_input_id)
          {
            $("td#td_"+memo_input_id).removeAttr("class").addClass("td_clavier").children("div").remove();
            $("input#"+memo_input_id).show();
            memo_input_id = false;
          }
          else
          {
            // Récupérer les infos associées
            memo_input_id = $(this).children("input").attr("id");
            colonne = parseInt(memo_input_id.substring(1,memo_input_id.indexOf('L')),10);
            ligne   = parseInt(memo_input_id.substring(memo_input_id.indexOf('L')+1),10);
            var valeur = $(this).children("input").val();
            $(this).children("input").hide();
            $(this).removeAttr("class").addClass("td_souris").append( $("#td_souris_container").html() ).find("img[alt="+valeur+"]").addClass("on");
          }
        }
      }
    );

    // Revenir à la cellule initiale ; mouseout ne fonctionne pas à cause des éléments contenus dans le div ; mouseleave est mieux, mais pb qd même avec les select du calendrier
    $('#table_saisir').on
    (
      'mouseleave',
      'tbody td',
      function()
      {
        if(memo_pilotage=='souris')
        {
          if(memo_input_id)
          {
            $("td#td_"+memo_input_id).removeAttr("class").addClass("td_clavier").children("div").remove();
            $("input#"+memo_input_id).show();
            memo_input_id = false;
          }
        }
      }
    );

    // Renvoyer l'information dans la ou les cellule(s)
    $('#table_saisir').on
    (
      'click',
      'div.td_souris img',
      function()
      {
        var note = $(this).attr("alt");
        endroit_report_note = $("input[name=f_endroit_report_note]:checked").val();
        if( (typeof(endroit_report_note)=='undefined') || (endroit_report_note=='cellule') )
        {
          // pour une seule case
          $("input#"+memo_input_id).val(note).removeAttr("class").addClass(note);
          $(this).parent().children("img").removeAttr("class");
          $(this).addClass("on").parent().parent().css("background-color","#F6D");
        }
        else
        {
          if(endroit_report_note=='tableau')
          {
            // pour toutes les cases vides du tableau
            $("#table_saisir tbody td input").each
            (
              function()
              {
                if($(this).val()=='X')
                {
                  $(this).val(note).removeAttr("class").addClass(note);
                  $(this).parent().css("background-color","#F6D");
                }
              }
            );
          }
          else if(endroit_report_note=='colonne')
          {
            // pour toutes les cases vides d'une colonne
            $("#table_saisir tbody td input[id^=C"+colonne+"L]").each
            (
              function()
              {
                if($(this).val()=='X')
                {
                  $(this).val(note).removeAttr("class").addClass(note);
                  $(this).parent().css("background-color","#F6D");
                }
              }
            );
          }
          else if(endroit_report_note=='ligne')
          {
            // pour toutes les cases vides d'une ligne
            $("#table_saisir tbody td input[id$=L"+ligne+"]").each
            (
              function()
              {
                if($(this).val()=='X')
                {
                  $(this).val(note).removeAttr("class").addClass(note);
                  $(this).parent().css("background-color","#F6D");
                }
              }
            );
          }
        }
        if(modification==false)
        {
          $('#fermer_zone_saisir').removeAttr("class").addClass("annuler").html('Annuler / Retour');
          $('#kbd_27').removeAttr("class").addClass("img annuler");
          modification = true;
        }
        $('#ajax_msg_saisir').removeAttr("class").html("&nbsp;");
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le lien pour mettre à jour l'ordre des items d'une évaluation
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#valider_ordre').click
    (
      function()
      {
        if(modification==false)
        {
          $('#ajax_msg_ordonner').removeAttr("class").addClass("alerte").html("Aucune modification effectuée !");
        }
        else
        {
          // On récupère la liste des items dans l'ordre de la page
          var tab_id = new Array();
          $('#sortable').children('li').each
          (
            function()
            {
              var test_id = $(this).attr('id');
              if(typeof(test_id)!='undefined')
              {
                tab_id.push(test_id.substring(1));
              }
            }
          );
          $('#zone_ordonner button').prop('disabled',true);
          $('#ajax_msg_ordonner').removeAttr("class").addClass("loader").html("En cours&hellip;");
          $.ajax
          (
            {
              type : 'POST',
              url : 'ajax.php?page='+PAGE,
              data : 'csrf='+CSRF+'&f_action=enregistrer_ordre'+'&f_ref='+$('#ordre_ref').val()+'&tab_id='+tab_id,
              dataType : "html",
              error : function(jqXHR, textStatus, errorThrown)
              {
                $('#zone_ordonner button').prop('disabled',false);
                $('#ajax_msg_ordonner').removeAttr("class").addClass("alerte").html('Échec de la connexion !');
                return false;
              },
              success : function(responseHTML)
              {
                initialiser_compteur();
                $('#zone_ordonner button').prop('disabled',false);
                if(responseHTML!='<ok>')
                {
                  $('#ajax_msg_ordonner').removeAttr("class").addClass("alerte").html(responseHTML);
                }
                else
                {
                  modification = false;
                  $('#ajax_msg_ordonner').removeAttr("class").addClass("valide").html("Ordre enregistré !");
                  $('#fermer_zone_ordonner').removeAttr("class").addClass("retourner").html('Retour');
                }
              }
            }
          );
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le lien pour mettre à jour les acquisitions des élèves à une évaluation
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#valider_saisir').click
    (
      function()
      {
        if(modification==false)
        {
          $('#ajax_msg_saisir').removeAttr("class").addClass("alerte").html("Aucune modification effectuée !");
        }
        else
        {
          $('button').prop('disabled',true);
          $('#ajax_msg_saisir').removeAttr("class").addClass("loader").html("En cours&hellip;");
          // Grouper les saisies dans une variable unique afin d'éviter tout problème avec une limitation du module "suhosin" (voir par exemple http://xuxu.fr/2008/12/04/nombre-de-variables-post-limite-ou-tronque) ou "max input vars" généralement fixé à 1000.
          var f_notes = new Array();
          $("#table_saisir tbody input").each
          (
            function()
            {
              var ids  = $(this).attr('name');
              var note = $(this).val();
              if(note)
              {
                f_notes.push( ids + '_' + note );
              }
            }
          );
          $.ajax
          (
            {
              type : 'POST',
              url : 'ajax.php?page='+PAGE,
              data : 'csrf='+CSRF+'&f_action=enregistrer_saisie'+'&f_ref='+$("#saisir_ref").val()+'&f_date_fr='+$("#saisir_date_fr").val()+'&f_date_visible='+$("#saisir_date_visible").val()+'&f_fini='+$("#saisir_fini").val()+'&f_notes='+f_notes+'&f_description='+encodeURIComponent($("#saisir_description").val()),
              dataType : "html",
              error : function(jqXHR, textStatus, errorThrown)
              {
                $('button').prop('disabled',false);
                $('#ajax_msg_saisir').removeAttr("class").addClass("alerte").html('Échec de la connexion !');
                return false;
              },
              success : function(responseHTML)
              {
                modification = false; // Mis ici pour le cas "aucune modification détectée"
                initialiser_compteur();
                $('button').prop('disabled',false);
                if(responseHTML.substring(0,4)!='<td ')
                {
                  $('#ajax_msg_saisir').removeAttr("class").addClass("alerte").html(responseHTML);
                }
                else
                {
                  $('#ajax_msg_saisir').removeAttr("class").addClass("valide").html("Saisies enregistrées !");
                  $('#fermer_zone_saisir').removeAttr("class").addClass("retourner").html('Retour');
                  $('#kbd_27').removeAttr("class").addClass("img retourner");
                  colorer_cellules();
                  $("#devoir_"+$("#saisir_ref").val()).prev().replaceWith(responseHTML);
                }
              }
            }
          );
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du formulaire principal
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire = $('#form_gestion');

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation = formulaire.validate
    (
      {
        rules :
        {
          // "required:true" ne fonctionne pas sur "f_eleve_liste" & "f_prof_liste" & "f_compet_liste" car type hidden
          f_date          : { required:true , dateITA:true },
          f_date_visible  : { required:function(){return !$('#box_visible').is(':checked');} , dateITA:true },
          f_date_autoeval : { required:function(){return !$('#box_autoeval').is(':checked');} , dateITA:true },
          f_groupe        : { required:true },
          f_eleve_nombre  : { isWord:'élève' },
          f_description   : { required:false , maxlength:60 },
          f_prof_nombre   : { required:false },
          f_compet_nombre : { isWord:'item' },
          f_doc_sujet     : { required:false , url:true },
          f_doc_corrige   : { required:false , url:true }
        },
        messages :
        {
          f_date          : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" },
          f_date_visible  : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" },
          f_date_autoeval : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" },
          f_groupe        : { required:"groupe manquant" },
          f_eleve_nombre  : { isWord:"élève(s) manquant(s)" },
          f_description   : { maxlength:"60 caractères maximum" },
          f_prof_nombre   : { },
          f_compet_nombre : { isWord:"item(s) manquant(s)" },
          f_doc_sujet     : { url:" URL sujet invalide" },
          f_doc_corrige   : { url:" URL corrigé invalide" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element)
        {
          if('.f_date.f_date_visible.f_date_autoeval.f_eleve_nombre.f_prof_nombre.f_compet_nombre.'.indexOf('.'+element.attr("id")+'.')!=-1) { element.next().after(error); }
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
      beforeSerialize : action_form_avant_serialize,
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

    // Fonction précédent le traitement du formulaire (avec jquery.form.js)
    function action_form_avant_serialize(jqForm, options)
    {
      if($('#box_visible').is(':checked'))
      {
        // Obligé rajouter le test à ce niveau car si la date a été changé depuis le calendrier, l'événement change() n'a pas été déclenché (et dans test_form_avant_envoi() c'est trop tard).
        $('#f_date_visible').val($('#f_date').val());
      }
      if($('#box_autoeval').is(':checked'))
      {
        $('#f_date_autoeval').val('00/00/0000');
      }
    }

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
            $('#table_action tbody tr td[colspan=10]').parent().remove(); // En cas de tableau avec une ligne vide pour la conformité XHTML ; IE8 bugue si on n'indique que [colspan]
          case 'dupliquer':
            var position_script = responseHTML.lastIndexOf('<SCRIPT>');
            var new_tds = responseHTML.substring(0,position_script);
            if(TYPE=='groupe')
            {
              var groupe_id = $("#f_groupe option:selected").val();
              var new_tds = new_tds.replace('<td>{{GROUPE_NOM}}</td>','<td>'+tab_groupe[groupe_id]+'</td>');
            }
            var new_tr = '<tr class="new">'+new_tds+'</tr>';
            $('#table_action tbody').prepend(new_tr);
            eval( responseHTML.substring(position_script+8) );
            break;
          case 'modifier':
            var position_script = responseHTML.lastIndexOf('<SCRIPT>');
            var new_tds = responseHTML.substring(0,position_script);
            if(TYPE=='groupe')
            {
              var groupe_id = $("#f_groupe option:selected").val();
              var new_tds = new_tds.replace('<td>{{GROUPE_NOM}}</td>','<td>'+tab_groupe[groupe_id]+'</td>');
            }
            $('#devoir_'+$('#f_ref').val()).parent().addClass("new").html(new_tds);
            eval( responseHTML.substring(position_script+8) );
            break;
          case 'supprimer':
            $('#devoir_'+$('#f_ref').val()).parent().remove();
            break;
        }
        tableau_maj();
        $.fancybox.close();
        mode = false;
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du clic sur le bouton pour envoyer un import csv (saisie déportée)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Envoi du fichier avec jquery.ajaxupload.js
    new AjaxUpload
    ('#import_file',
      {
        action: 'ajax.php?page='+PAGE+'&f_action=importer_saisie_csv',
        name: 'userfile',
        data : {'csrf':CSRF},
        autoSubmit: true,
        responseType: "html",
        onChange: changer_fichier,
        onSubmit: verifier_fichier,
        onComplete: retourner_fichier
      }
    );

    function changer_fichier(fichier_nom,fichier_extension)
    {
      $('#msg_import').removeAttr("class").html('&nbsp;');
      return true;
    }

    function verifier_fichier(fichier_nom,fichier_extension)
    {
      if (fichier_nom==null || fichier_nom.length<5)
      {
        $('#msg_import').removeAttr("class").addClass("erreur").html('"'+fichier_nom+'" n\'est pas un chemin de fichier correct.');
        return false;
      }
      else if ('.csv.txt.'.indexOf('.'+fichier_extension.toLowerCase()+'.')==-1)
      {
        $('#msg_import').removeAttr("class").addClass("erreur").html('Le fichier "'+fichier_nom+'" n\'a pas l\'extension "csv" ou "txt".');
        return false;
      }
      else
      {
        $('button').prop('disabled',true);
        $('#msg_import').removeAttr("class").addClass("loader").html("En cours&hellip;");
        return true;
      }
    }

    function retourner_fichier(fichier_nom,responseHTML)  // Attention : avec jquery.ajaxupload.js, IE supprime mystérieusement les guillemets et met les éléments en majuscules dans responseHTML.
    {
      $('button').prop('disabled',false);
      if(responseHTML.substring(0,1)!='|')
      {
        $('#msg_import').removeAttr("class").addClass("alerte").html(responseHTML);
      }
      else
      {
        initialiser_compteur();
        if(responseHTML.length>2)
        {
          responseHTML = responseHTML.substring(1);
          tab_resultat = responseHTML.split('|');
          for (i=0 ; i<tab_resultat.length ; i++)
          {
            tab_valeur = tab_resultat[i].split('.');
            if(tab_valeur.length==3)
            {
              var eleve_id = tab_valeur[0];
              var item_id  = tab_valeur[1];
              var score    = tab_valeur[2];
              champ = $('#table_saisir input[name='+item_id+'x'+eleve_id+']');
              if(champ.length)
              {
                switch (score)
                {
                  case '1': champ.val('RR'  ).removeAttr("class").addClass('RR'  ); break;
                  case '2': champ.val('R'   ).removeAttr("class").addClass('R'   ); break;
                  case '3': champ.val('V'   ).removeAttr("class").addClass('V'   ); break;
                  case '4': champ.val('VV'  ).removeAttr("class").addClass('VV'  ); break;
                  case 'A': champ.val('ABS' ).removeAttr("class").addClass('ABS' ); break;
                  case 'D': champ.val('DISP').removeAttr("class").addClass('DISP'); break;
                  case 'N': champ.val('NN'  ).removeAttr("class").addClass('NN'  ); break;
                  case 'E': champ.val('NE'  ).removeAttr("class").addClass('NE'  ); break;
                  case 'F': champ.val('NF'  ).removeAttr("class").addClass('NF'  ); break;
                  case 'R': champ.val('NR'  ).removeAttr("class").addClass('NR'  ); break;
                  case 'P': champ.val('REQ' ).removeAttr("class").addClass('REQ' ); break;
                }
                champ.parent().css("background-color","#F6D");
              }
              modification = true;
            }
          }
        }
        $('#msg_import').removeAttr("class").addClass("valide").html("Tableau complété ! N'oubliez pas d'enregistrer...");
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du clic sur un bouton pour envoyer un sujet ou un corrigé de devoir
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Envoi du fichier avec jquery.ajaxupload.js ; on lui donne un nom afin de pouvoir changer dynamiquement le paramètre.
    var uploader_sujet = new AjaxUpload
    ('#bouton_uploader_sujet',
      {
        action: 'ajax.php?page='+PAGE,
        name: 'userfile',
        data: {'csrf':CSRF,'f_action':'uploader_document','f_doc_objet':'sujet','f_ref':'maj_plus_tard'},
        autoSubmit: true,
        responseType: "html",
        onChange: changer_fichier_document,
        onSubmit: verifier_fichier_document,
        onComplete: retourner_fichier_document
      }
    );

    // Envoi du fichier avec jquery.ajaxupload.js ; on lui donne un nom afin de pouvoir changer dynamiquement le paramètre.
    var uploader_corrige = new AjaxUpload
    ('#bouton_uploader_corrige',
      {
        action: 'ajax.php?page='+PAGE,
        name: 'userfile',
        data: {'csrf':CSRF,'f_action':'uploader_document','f_doc_objet':'corrige','f_ref':'maj_plus_tard'},
        autoSubmit: true,
        responseType: "html",
        onChange: changer_fichier_document,
        onSubmit: verifier_fichier_document,
        onComplete: retourner_fichier_document
      }
    );

    function changer_fichier_document(fichier_nom,fichier_extension)
    {
      $('#ajax_document_upload').removeAttr("class").html('&nbsp;');
      $('#zone_upload button').prop('disabled',true);
      return true;
    }

    function verifier_fichier_document(fichier_nom,fichier_extension)
    {
      if (fichier_nom==null || fichier_nom.length<5)
      {
        $('#ajax_document_upload').removeAttr("class").addClass("erreur").html('Cliquer sur "Parcourir..." pour indiquer un chemin de fichier correct.');
        activer_boutons_upload(uploader_sujet['_settings']['data']['f_ref']);
        return false;
      }
      else if ( ('.doc.docx.odg.odp.ods.odt.ppt.pptx.rtf.sxc.sxd.sxi.sxw.xls.xlsx.'.indexOf('.'+fichier_extension.toLowerCase()+'.')!=-1) && !confirm('Vous devriez convertir votre fichier au format PDF.\nEtes-vous certain de vouloir l\'envoyer sous ce format ?') )
      {
        $('#ajax_document_upload').removeAttr("class").addClass("erreur").html('Convertissez votre fichier en "pdf".');
        activer_boutons_upload(uploader_sujet['_settings']['data']['f_ref']);
        return false;
      }
      else if ('.bat.com.exe.php.zip.'.indexOf('.'+fichier_extension.toLowerCase()+'.')!=-1)
      {
        $('#ajax_document_upload').removeAttr("class").addClass("erreur").html('Extension non autorisée.');
        activer_boutons_upload(uploader_sujet['_settings']['data']['f_ref']);
        return false;
      }
      else
      {
        $('#zone_upload button').prop('disabled',true);
        $('#ajax_document_upload').removeAttr("class").addClass("loader").html("En cours&hellip;");
        return true;
      }
    }

    function retourner_fichier_document(fichier_nom,responseHTML)  // Attention : avec jquery.ajaxupload.js, IE supprime mystérieusement les guillemets et met les éléments en majuscules dans responseHTML.
    {
      var tab_infos = responseHTML.split(']¤[');
      if(tab_infos[0]!='ok')
      {
        $('#ajax_document_upload').removeAttr("class").addClass("alerte").html(responseHTML);
      }
      else
      {
        initialiser_compteur();
        $('#ajax_document_upload').removeAttr("class").addClass("valide").html("Document enregistré.");
        var ref   = tab_infos[1];
        var objet = tab_infos[2];
        var url   = tab_infos[3];
        if(objet=='sujet') { var alt='sujet';   var title='Sujet';   var numero=0; tab_sujets[ref] = url; }
        else               { var alt='corrigé'; var title='Corrigé'; var numero=1; tab_corriges[ref] = url; }
        var lien        = '<a href="'+url+'" target="_blank"><img alt="'+alt+'" src="./_img/document/'+objet+'_oui.png" title="'+title+' disponible." /></a>';
        $('#span_'+objet).html(lien);
        $('#devoir_'+ref).prev().prev().children().eq(numero).replaceWith(lien);
      }
      activer_boutons_upload(ref);
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du clic sur un bouton pour retirer un sujet ou un corrigé de devoir
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_supprimer_sujet , #bouton_supprimer_corrige').click
    (
      function()
      {
        $('#zone_upload button').prop('disabled',true);
        $('#ajax_document_upload').removeAttr("class").addClass("loader").html("En cours&hellip;");
        var objet = $(this).attr('id').substring(17);
        var ref   = uploader_sujet['_settings']['data']['f_ref'];
        var url   = (objet=='sujet') ? tab_sujets[ref] : tab_corriges[ref] ;
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=retirer_document'+'&f_doc_objet='+objet+'&f_ref='+ref+'&f_doc_url='+encodeURIComponent(url),
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_document_upload').removeAttr("class").addClass("alerte").html(responseHTML);
              activer_boutons_upload(ref);
              return false;
            },
            success : function(responseHTML)
            {
              initialiser_compteur();
              if(responseHTML!='ok')
              {
                $('#ajax_document_upload').removeAttr("class").addClass("alerte").html(responseHTML);
              }
              else
              {
                $('#ajax_document_upload').removeAttr("class").addClass("valide").html("Document retiré.");
                if(objet=='sujet') { var alt='sujet';   var numero=0; tab_sujets[ref] = ''; }
                else               { var alt='corrigé'; var numero=1; tab_corriges[ref] = ''; }
                var lien        = '<img alt="'+alt+'" src="./_img/document/'+objet+'_non.png" />';
                $('#span_'+objet).html(lien);
                $('#devoir_'+ref).prev().prev().children().eq(numero).replaceWith(lien);
              }
              activer_boutons_upload(ref);
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du clic sur un bouton pour référencer un lien de sujet ou corrigé de devoir
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_referencer_sujet , #bouton_referencer_corrige').click
    (
      function()
      {
        var objet = $(this).attr('id').substring(18);
        var ref   = uploader_sujet['_settings']['data']['f_ref'];
        var url   = $('#f_adresse_'+objet).val();
        if(url == '')
        {
          $('#ajax_document_upload').removeAttr("class").addClass("erreur").html("Adresse manquante !");
          $('#f_adresse_'+objet).focus();
          return false;
        }
        else if(!testURL(url))
        {
          $('#ajax_document_upload').removeAttr("class").addClass("erreur").html("Adresse incorrecte !");
          $('#f_adresse_'+objet).focus();
          return false;
        }
        else
        {
          $('#zone_upload button').prop('disabled',true);
          $('#ajax_document_upload').removeAttr("class").addClass("loader").html("En cours&hellip;");
          $.ajax
          (
            {
              type : 'POST',
              url : 'ajax.php?page='+PAGE,
              data : 'csrf='+CSRF+'&f_action=referencer_document'+'&f_doc_objet='+objet+'&f_ref='+ref+'&f_doc_url='+encodeURIComponent(url),
              dataType : "html",
              error : function(jqXHR, textStatus, errorThrown)
              {
                $('#ajax_document_upload').removeAttr("class").addClass("alerte").html(responseHTML);
                activer_boutons_upload(ref);
                return false;
              },
              success : function(responseHTML)
              {
                initialiser_compteur();
                if(responseHTML!='ok')
                {
                  $('#ajax_document_upload').removeAttr("class").addClass("alerte").html(responseHTML);
                }
                else
                {
                  $('#ajax_document_upload').removeAttr("class").addClass("valide").html("Document référencé.");
                  if(objet=='sujet') { var alt='sujet';   var title='Sujet';   var numero=0; tab_sujets[ref] = url; }
                  else               { var alt='corrigé'; var title='Corrigé'; var numero=1; tab_corriges[ref] = url; }
                  var lien        = '<a href="'+url+'" target="_blank"><img alt="'+alt+'" src="./_img/document/'+objet+'_oui.png" title="'+title+' disponible." /></a>';
                  $('#span_'+objet).html(lien);
                  $('#devoir_'+ref).prev().prev().children().eq(numero).replaceWith(lien);
                }
                activer_boutons_upload(ref);
              }
            }
          );
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du clic sur un checkbox pour déclarer (ou pas) une évaluation complète en saisie
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_action').on
    (
      'click',
      'a.fini',
      function()
      {
        var obj_lien = $(this);
        var txt_span = obj_lien.children('span').text();
        var txt_i    = obj_lien.children('i').text();
        var fini     = (txt_i=='terminé') ? 'oui' : 'non' ;
        var ref      = obj_lien.parent().next().attr('id').substring(7); // "devoir_" + ref
        $.ajax
        (
          {
            type : 'POST',
            url  : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=maj_fini'+'&f_fini='+fini+'&f_ref='+ref,
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $.fancybox( '<label class="alerte">'+'Échec de la connexion !\nVeuillez recommencer.'+'</label>' , {'centerOnScroll':true} );
              return false;
            },
            success : function(responseHTML)
            {
              initialiser_compteur();
              if(responseHTML!='ok')
              {
                $.fancybox( '<label class="alerte">'+responseHTML+'</label>' , {'centerOnScroll':true} );
              }
              else
              {
                if(fini=='oui') { obj_lien.html('<span>'+txt_i+'</span><i>'+txt_span+'</i>').parent().addClass("bf"); }
                else            { obj_lien.html('<span>'+txt_i+'</span><i>'+txt_span+'</i>').parent().removeClass("bf"); }
              }
              return false;
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du premier formulaire pour afficher le tableau avec la liste des évaluations
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Afficher masquer des options de la grille (uniquement pour un groupe)

    var autoperiode = true; // Tant qu'on ne modifie pas manuellement le choix des périodes, modification automatique du formulaire

    function view_dates_perso()
    {
      var periode_val = $("#f_aff_periode").val();
      if(periode_val!=0)
      {
        $("#dates_perso").attr("class","hide");
      }
      else
      {
        $("#dates_perso").attr("class","show");
      }
    }

    $('#f_aff_periode').change
    (
      function()
      {
        view_dates_perso();
        autoperiode = false;
      }
    );

    // Changement de groupe (uniquement pour un groupe)
    // -> desactiver les périodes prédéfinies en cas de groupe de besoin
    // -> choisir automatiquement la meilleure période et chercher les évaluations si un changement manuel de période n'a jamais été effectué

    function modifier_periodes()
    {
      var groupe_type = $("#f_aff_classe option:selected").parent().attr('label');
      $("#f_aff_periode option").each
      (
        function()
        {
          var periode_id = $(this).val();
          // La période personnalisée est tout le temps accessible
          if(periode_id!=0)
          {
            // groupe de besoin -> desactiver les périodes prédéfinies
            if( (typeof(groupe_type)=='undefined') || (groupe_type=='Besoins') )
            {
              $(this).prop('disabled',true);
            }
            // classe ou groupe classique -> toutes périodes accessibles
            else
            {
              $(this).prop('disabled',false);
            }
          }
        }
      );
      // Sélectionner si besoin la période personnalisée
      if( (typeof(groupe_type)=='undefined') || (groupe_type=='Besoins') )
      {
        $("#f_aff_periode option[value=0]").prop('selected',true);
        $("#dates_perso").attr("class","show");
      }
      // Modification automatique du formulaire
      if(autoperiode)
      {
        if( (groupe_type=='Classes') || (groupe_type=='Groupes') )
        {
          // Rechercher automatiquement la meilleure période
          var id_classe = $('#f_aff_classe option:selected').val().substring(1);
          if(typeof(tab_groupe_periode[id_classe])!='undefined')
          {
            for(var id_periode in tab_groupe_periode[id_classe]) // Parcourir un tableau associatif...
            {
              var tab_split = tab_groupe_periode[id_classe][id_periode].split('_');
              if( (date_mysql>=tab_split[0]) && (date_mysql<=tab_split[1]) )
              {
                $("#f_aff_periode option[value="+id_periode+"]").prop('selected',true);
                view_dates_perso();
                break;
              }
            }
          }
        }
        // Soumettre le formulaire
        if(autoperiode)
        {
          formulaire_prechoix.submit();
        }
      }
    }

    $('#f_aff_classe').change
    (
      function()
      {
        modifier_periodes();
      }
    );

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire_prechoix = $('#form_prechoix');

    // Ajout d'une méthode pour valider les dates de la forme jj/mm/aaaa (trouvé dans le zip du plugin, corrige en plus un bug avec Safari)
    // méthode dateITA déjà ajoutée

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation_prechoix = formulaire_prechoix.validate
    (
      {
        rules :
        {
          f_aff_classe : { required:true },
          f_date_debut : { required:function(){return (TYPE=='selection') || $("#f_aff_periode").val()==0;} , dateITA:true },
          f_date_fin   : { required:function(){return (TYPE=='selection') || $("#f_aff_periode").val()==0;} , dateITA:true }
        },
        messages :
        {
          f_aff_classe : { required:"classe / groupe manquant" },
          f_date_debut : { required:"date manquante" , dateITA:"date JJ/MM/AAAA incorrecte" },
          f_date_fin   : { required:"date manquante" , dateITA:"date JJ/MM/AAAA incorrecte" }
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
    var ajaxOptions_prechoix =
    {
      url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
      type : 'POST',
      dataType : "html",
      clearForm : false,
      resetForm : false,
      target : "#ajax_msg_prechoix",
      beforeSubmit : test_form_avant_envoi_prechoix,
      error : retour_form_erreur_prechoix,
      success : retour_form_valide_prechoix
    };

    // Envoi du formulaire (avec jquery.form.js)
    formulaire_prechoix.submit
    (
      function()
      {
        $(this).ajaxSubmit(ajaxOptions_prechoix);
        return false;
      }
    ); 

    // Fonction précédent l'envoi du formulaire (avec jquery.form.js)
    function test_form_avant_envoi_prechoix(formData, jqForm, options)
    {
      $('#ajax_msg_prechoix').removeAttr("class").html("&nbsp;");
      var readytogo = validation_prechoix.form();
      if(readytogo)
      {
        $('#ajax_msg_prechoix').removeAttr("class").addClass("loader").html("En cours&hellip;");
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur_prechoix(jqXHR, textStatus, errorThrown)
    {
      $('#ajax_msg_prechoix').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide_prechoix(responseHTML)
    {
      initialiser_compteur();
      if( (responseHTML.substring(0,4)!='<tr>') && (responseHTML!='<SCRIPT>') )
      {
        $('#ajax_msg_prechoix').removeAttr("class").addClass("alerte").html(responseHTML);
      }
      else
      {
        $('#ajax_msg_prechoix').removeAttr("class").addClass("valide").html("Demande réalisée !").fadeOut(3000,function(){$(this).removeAttr("class").html("").show();});
        var position_script = responseHTML.lastIndexOf('<SCRIPT>');
        $('#table_action tbody').html( responseHTML.substring(0,position_script) );
        eval( responseHTML.substring(position_script+8) );
        tableau_maj();
        if( reception_todo )
        {
          $('q.ajouter').click();
        }
      }
    }

    // N'afficher les éléments qu'une fois le js bien chargé...
    $('#form_prechoix , #table_action').show('fast');

    // Et charger par défaut les dernières évaluations du prof.
    $('#form_prechoix').submit();

  }
);

