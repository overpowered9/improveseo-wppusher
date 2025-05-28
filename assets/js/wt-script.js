"use strict";

jQuery(function($){



    //saving the form fields for testimonials

    $(document).on('submit', '.wt-save-admin-settings-testimonials', function(e) {



        e.preventDefault();

        

        /* var img_src = $('.testimonial-img').prop('src');

        $('.img-source').val(img_src); */



        var data = $(this).serialize();



        var submit_btn = $("input[type='submit']", this);

        //submit_btn.css({'width' : '100px'});

        submit_btn.val("Please Wait...").attr('disabled', true);

        $.post(ajax_vars.ajax_url, data, function(resp){

            swal("Data Have Been Saved Successfully!", {

              icon: "success",

            }).then(function(){

                window.location.href = resp.url;

            });

        });

    });



    //saving the form fields for google maps

    $(document).on('submit', '.wt-save-admin-settings-googlemaps', function(e) {



        e.preventDefault();

        var data = $(this).serialize();



        var submit_btn = $("input[type='submit']", this);

        //submit_btn.css({'width' : '100px'});

        submit_btn.val("Please Wait...").attr('disabled', true);

        $.post(ajax_vars.ajax_url, data, function(resp){

            swal("Data Have Been Saved Successfully!", {

              icon: "success",

            }).then(function(){

                window.location.href=resp.url;

            });

        });

    });



    //saving the form fields for Buttons

    $(document).on('submit', '.wt-save-admin-settings-buttons', function(e) {



        e.preventDefault();

        var data = $(this).serialize();



        var submit_btn = $("input[type='submit']", this);

        //submit_btn.css({'width' : '100px'});

        submit_btn.val("Please Wait...").attr('disabled', true);

        $.post(ajax_vars.ajax_url, data, function(resp){

            swal("Data Have Been Saved Successfully!", {

              icon: "success",

            }).then(function(){

                window.location.href=resp.url;

            });

        });

    });



    //saving the form fields for videos

    $(document).on('submit', '.wt-save-admin-settings-videos', function(e) {



        e.preventDefault();

        var disabled = $(this).find(':input:disabled').removeAttr('disabled');

        var data = $(this).serialize();

        disabled.attr('disabled','disabled');



        var submit_btn = $("input[type='submit']", this);

        submit_btn.val("Please Wait...").attr('disabled', true);

        $.post(ajax_vars.ajax_url, data, function(resp){

            swal("Data Have Been Saved Successfully!", {

              icon: "success",

            }).then(function(){

                window.location.href = resp.url;

            });

        });

    });





    //Download the file with search results clear-search-results

    $(document).on('click', '.sw-save-search-results', function(e) {

        

        var proj_name = $('.sw-project-name').val();

        var output = $('.sw-output-ta').val();

        if(output ==null || output == ''){

             swal({

              text: "Nothing to Save!!! ",

              icon: "warning",

            })

            return;

        }

        

        var data = {

            'action'            : 'sw_saved_search_results_keyword' ,

            'proj_name'         : proj_name ,

            'search_results'    : output ,

        }



        swal({

          title: "Are you sure?",

          text: "You Want to Save The Searched DATA!",

          icon: "warning",

          buttons: true,

          dangerMode: true,

        })

        .then((willDelete) => {

          if (willDelete) {



            $.post(ajax_vars.ajax_url, data, function(resp){

                

                if (resp.status=='success') {

                    swal("Your Data has been saved Successfully!", {

                      icon: "success",

                    }).then(function(){

                        window.location.reload();

                    });

                }



            });



          }

        });



        

    })

    

    

    //the clear results clear-search-results

    $(document).on('click', '.clear-search-results', function(e) {

        

        var output = $('.sw-output-ta').val();

        if(output ==null || output == ''){

             swal({

              text: "Nothing to Clear!!! ",

              icon: "warning",

            })

            return;

        }

        var submit_btn = $(this);

        // submit_btn.css({'width' : '100px'});

        submit_btn.val("Please Wait...").attr('disabled', true);

        

        setTimeout(function(){ 

                swal({

                  title: "Are you sure?",

                  text: "Once Cleared, you will not be able to recover this DATA!",

                  icon: "warning",

                  buttons: true,

                  dangerMode: true,

                })

                .then((willDelete) => {

                  if (willDelete) {

                    $('.sw-output-ta').val(' ');

                    submit_btn.val("Clear Results").attr('disabled', false);

                  }

                  else{

                    submit_btn.val("Clear Results").attr('disabled', false);  

                  }

                });

            

        }, 200);

        

    })

    

    

    //Delete The Selected Keyword on admin side With ID

    $(document).on('click', '.kw-dlt-kwproject', function(e) {



        e.preventDefault();

        var rand_id = $(this).data('keyword_rand_id');

        var data = {

            'action' : 'kwdelete_selected_data_for_keyword',

            'kw_rand_id' : rand_id,

        }

        swal({

          title: "Are you sure?",

          text: "Once deleted, you will not be able to recover this DATA!",

          icon: "warning",

          buttons: true,

          dangerMode: true,

        })

        .then((willDelete) => {

          if (willDelete) {



            $.post(ajax_vars.ajax_url, data, function(resp){

                

                if (resp.status=='success') {

                    swal("Your Selected Data has been deleted!", {

                      icon: "success",

                    }).then(function(){

                        window.location.reload();

                    });

                }



            });



          }

        });



    })

    

    //Download The Selected data of Keyword on admin side With ID

    $(document).on('click', '.kw-download-kwproject', function(e) {



        e.preventDefault();

        

        window.swal({

          title: "Downloading...",

          text: "Please wait",

        });

        

        var rand_id = $(this).data('keyword_rand_id');

        var data = {

            'action' : 'kwdownload_selected_data_for_keyword',

            'kw_rand_id' : rand_id,

        }

      

        $.post(ajax_vars.ajax_url, data, function(resp){

            

            if (resp.status=='success') {

                download(resp.proj_name , resp.proj_content);

                // $('.swal-overlay').hide();

            }



        });



          

    });

    



    

    // Function to download a file

    function download(filename, text) {

          var element = document.createElement('a');

          element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));

          element.setAttribute('download', filename);

        

          element.style.display = 'none';

          document.body.appendChild(element);

        

          element.click();

        

          document.body.removeChild(element);

    }







    //Delete The Selected Testimonial With ID

    $(document).on('click', '.wt-dlt-testi', function(e) {



        e.preventDefault();

        var rand_id = $(this).data('rand_id');

        var data = {

            'action' : 'delete_selected_data',

            'rand_id' : rand_id,

        }

        swal({

          title: "Are you sure?",

          text: "Once deleted, you will not be able to recover this DATA!",

          icon: "warning",

          buttons: true,

          dangerMode: true,

        })

        .then((willDelete) => {

          if (willDelete) {



            $.post(ajax_vars.ajax_url, data, function(resp){

                

                if (resp.status=='success') {

                    swal("Your Selected Data has been deleted!", {

                      icon: "success",

                    }).then(function(){

                        window.location.href=resp.url;

                    });

                }



            });



          }

        });



    })

    

    // queryargs

    //get query args

    function getUrlVars(){

        

        var vars = [], hash;

        var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');

        for(var i = 0; i < hashes.length; i++)

        {

            hash = hashes[i].split('=');

            vars.push(hash[0]);

            vars[hash[0]] = hash[1];

        }

        return vars;

    }

    

    //active Tab

    /* $(document).ready(function(){

        

        var action = getUrlVars();

        if(action!=null){

            var act = action.action;

            if(act=='deleted'){

               actionson_init('saved_testimonials');

            }

            else if(act=='googlemaps'){

               actionson_init('google_maps');

            }   

            else if(act=='buttons'){

               actionson_init('button_wt');

            }

        }



    });

    

    function actionson_init(activeTab){

        $('#myTab a[href="#' + activeTab + '"]').tab('show');

    } */



    //Edit The Selected Testimonial With ID

    $(document).on('click', '.wt-edit-testi', function(e) {



        e.preventDefault();



        var url      = window.location.href;

        var rand_id = $(this).data('rand_id');

        var btn_act = $(this).data('action');

    

        var data = {

            'action' : 'edit_selected_data',

            'rand_id' : rand_id,

            'page_url' : url,

            'btn_action'  : btn_act

        }

        swal({

          title: "Are you sure?",

          text: "You Want To Update/Edit The DATA!",

          icon: "warning",

          buttons: true,

          dangerMode: true,

        })

        .then((willDelete) => {

          if (willDelete) {



            $.post(ajax_vars.ajax_url, data, function(resp){

                

                if (resp.status=='success') {

                    window.location.href = resp.page_url;

                }



            });



          }

        });



    })

    

    

 

    function onchange_show_btns(action){

        

        $('.sw-hide-btn').each(function(){

            var btn_act = $(this).data('action');

            if(btn_act==action){

                $(this).show();

            }

            else $(this).hide();

        })

    }

    

    function hide_oninit(){

        $('.sw-hide-btn').each(function(){

            $(this).hide();

        })

    }



    // ADding the shortcode on admin side

    $(document).ready(function(){

        hide_oninit();

        $(document).on('change', '.sw-editor-selector', function() {

            var value = $(this).val();

            onchange_show_btns(value);

        })

        

        

        // $(document).on('click', '.sw-hide-btn', function(e) {

        //     e.preventDefault();

        //     var id = $(this).attr('id');

        //     var selector = $('.sw-editor-selector').val();

        //     var name = $(this).data('name');



        //     var text = '';

        //     if(selector=='testimonial'){

        //         text = '[improveseo_testimonial id="'+id+'" name="'+name+'"]';

        //     }

        //     else if(selector=='googlemap'){

        //         text ='[improveseo_googlemaps id="'+id+'" name="'+name+'" address="" title=""]';

        //     }

        //     else if(selector=='button'){

        //         text = '[improveseo_buttons id="'+id+'" name="'+name+'"]';

        //     }else if(selector=="video"){

        //         text = '[improveseo_video id="'+id+'" name="'+name+'"]';

        //     }

        //     else if(selector=='list'){

        //         text = '@list:'+id;

        //     }

            

        //     var prev_content = tinymce.activeEditor.setContent(tinyMCE.activeEditor.getContent());

        //     if(prev_content==null||prev_content==''){

        //         tinymce.activeEditor.setContent(text);

        //     }

        //     else

        //     tinymce.activeEditor.setContent(text+prev_content);  

        //     //tinymce.activeEditor.insertContent(text);

    

        // });



        });









    // Upload Images

    $(document).on('click', '.upload-image-js', function(e) {

        e.preventDefault();



        var button = $(this),

        custom_uploader = wp.media({

            title: 'Insert Image',

            library: {

                type: 'image'

            },

            button: {

                text: 'Use this image'

            },

            multiple: false

        }).on('select', function() {

            var attachment = custom_uploader.state().get('selection').first().toJSON();

            var fileurl = attachment.url;

            

            button.html('<img class="testimonial-img" style="width: 62px;" src="' + attachment.url + '">').next().val(attachment.id).next().show();

            

            $('.img-source').val(fileurl);



        }).open();

    });



    $(document).on('click', '.tap-to-call-upload-image-js', function(e) {

        e.preventDefault();



        var button = $(this),

        custom_uploader = wp.media({

            title: 'Insert Image',

            library: {

                type: 'image'

            },

            button: {

                text: 'Use this image'

            },

            multiple: false

        }).on('select', function() {

            var attachment = custom_uploader.state().get('selection').first().toJSON();

            var fileurl = attachment.url;

            

            button.html('<img class="tap-to-call-img" style="width: 62px;" src="' + attachment.url + '">').next().val(attachment.id).next().show();

            

            $('.tap-to-call-img-source').val(fileurl);



        }).open();

    });

    

    $(document).on('click', '.video-poster-image-js', function(e) {

        e.preventDefault();

         

        var custom_uploader = wp.media({

            title: 'Insert Image',

            library: {

                type: 'image'

            },

            button: {

                text: 'Use this image'

            },

            multiple: false

        }).on('select', function() {

            var attachment = custom_uploader.state().get('selection').first().toJSON();

            var fileurl = attachment.url;

            

            var img_poster = '<img class="video-poster-img" src="'+fileurl+'" style="width:auto; height:100px;" />';

            $('.video-poster-img-wrapper').html(img_poster);

            $('.video-poster-img-source').val(fileurl);

            $('.video-poster-img-id').val(attachment.id);



        }).open();

    });

    

    $(document).on('click', '.video-upload-btn', function(e) {

        e.preventDefault();

        var video_type = $(this).data('video-type');

        var video_url_cls = '.video-url-mp4';

        var video_id_cls = '.video-id-mp4';



        if(video_type=='video/mp4'){

            video_url_cls = '.video-url-mp4';

            video_id_cls = '.video-id-mp4';

        }else if(video_type=="video/ogg"){

            video_url_cls = '.video-url-ogv';

            video_id_cls = '.video-id-ogv';

        }else if(video_type=="video/webm"){

            video_url_cls = '.video-url-webm';

            video_id_cls = '.video-id-webm';

        }

        var custom_uploader = wp.media({

            title: 'Insert MP4 Video',

            library: {

                type: video_type

            },

            button: {

                text: 'Use this Video'

            },

            multiple: false

        }).on('select', function() {

            var attachment = custom_uploader.state().get('selection').first().toJSON();

            

            $(video_url_cls).val(attachment.url);

            $(video_id_cls).val(attachment.id);

        }).open();

    });

    



    $(document).on('click', '.tw_button_type', function(e){

        var btn_type = $(this).val();

        if(btn_type=="normal_btn"){

            $('#tap_to_call_btn_wrapper').hide();

            $('#normal_btn_wrapper').show();

        }else if(btn_type=="tap_to_call"){

            $('#tap_to_call_btn_wrapper').show();

            $('#normal_btn_wrapper').hide();

        }

    });



    $(document).on('click', '.video_type', function(e){

        var video_type = $(this).val();

        if(video_type=="upload_video"){

            $('.youtube_wrapper').hide();

            $('.vimeo_wrapper').hide();

            $('.upload_video_wrapper').show();

        }else if(video_type=="youtube"){

            $('.youtube_wrapper').show();

            $('.vimeo_wrapper').hide();

            $('.upload_video_wrapper').hide();

        }else if(video_type=="vimeo"){

            $('.youtube_wrapper').hide();

            $('.vimeo_wrapper').show();

            $('.upload_video_wrapper').hide();

        }

    });

})