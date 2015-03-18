function fnSelect(objId) {
    fnDeSelect();
    if (document.selection) {
        var range = document.body.createTextRange();
        range.moveToElementText(document.getElementById(objId));
        range.select();
    } else if (window.getSelection) {
        var range = document.createRange();
        range.selectNode(document.getElementById(objId));
        window.getSelection().addRange(range);
    }
}
function fnDeSelect() {
    if (document.selection)
        document.selection.empty();
    else if (window.getSelection)
        window.getSelection().removeAllRanges();
}

jQuery(document).ready(function($) {
    
    $('#add-new-sidebar').live('click', function() {
        $('#response').html('');
        $('#response').addClass('loader');
        var serial = $('#new-sidebar').serialize();
        var data = {
            action: 'save_wp_dynamic_sidebar',
            elements: serial
        };
        jQuery.post(ajaxurl, data,
            function(response) {
                $('#response').removeClass('loader');
                if(response == '* fields are mandatory.' || response == 'Insert unique sidebar.') {
                    $('#response').html(response);
                } else {
                    $('#dynamic-sidebar tbody').prepend(response).hide().fadeIn('slow');
                    $('#response').html('Data saved.');
                    $("#new-sidebar input[type=text], #new-sidebar textarea").val("");
                    if($('#dynamic-sidebar tbody tr').hasClass('no-sidebar')) {
                        $('#dynamic-sidebar tbody tr.no-sidebar').remove();
                    }
                }
            });
        return false;
    });
    
    $('.edit-dynamic-sidebar').live('click', function() {
        if($('#new-sidebar #update-new-sidebar').length > 0) {
            popup_message('Update or Cancel editing Dynamic Sidebar to Edit.')
            return false;
        }
        var id = $(this).attr('data-id');
        var data = {
            action: 'edit_wp_dynamic_sidebar',
            sidebar_id: id
        };
        jQuery.post(ajaxurl, data,
            function(response) {
                $('.wrap-dynamic-sidebar form input[type=button]').attr({
                    id: 'update-new-sidebar',
                    value: 'Update Sidebar',
                    'data-id': id
                });
                $("#response").before('<input class="button" type="button" id="cancel-update-new-sidebar" value="Cancel">');
                var obj = $.parseJSON( response );
                $('#sidebar_name').val(obj.name);
                $('#sidebar_desc').val(obj.description);
                $('#sidebar_class').val(obj.class);
                $('#sidebar_before_widget').val(html_entity_decode(obj.before_widget));
                $('#sidebar_after_widget').val(html_entity_decode(obj.after_widget));
                $('#sidebar_before_title').val(html_entity_decode(obj.before_title));
                $('#sidebar_after_title').val(html_entity_decode(obj.after_title));
                //alert(obj.name);
            });
        return false;
    });
    
    $('#update-new-sidebar').live('click', function() {
        $('#response').html('');
        $('#response').addClass('loader');
        var id = $(this).attr('data-id');
        var serial = $('#new-sidebar').serialize();
        var data = {
            action: 'update_wp_dynamic_sidebar',
            elements: serial,
            slug: id
        };
        jQuery.post(ajaxurl, data,
            function(response) {
                $('#response').removeClass('loader');
                if(response == '* fields are mandatory.' || response == 'Insert unique sidebar.') {
                    $('#response').html(response);
                } else {
                    var i_a = $('#dynamic-sidebar').find("a[data-id='"+id+"']");
                    i_a.parent('td').parent('tr').html(response);
                    $("#cancel-update-new-sidebar").remove();
                    $('.wrap-dynamic-sidebar form input[type=button]').attr({
                        id: 'add-new-sidebar',
                        value: 'Add Sidebar'
                    });
                    $('.wrap-dynamic-sidebar form input[type=button]').removeAttr('data-id');
                    $("#new-sidebar input[type=text], #new-sidebar textarea").val("");
                    $('#response').html('Sidebar successfully updated.');
                }
            });
        return false;
    });
    
    $('#cancel-update-new-sidebar').live('click', function() {
        $("#cancel-update-new-sidebar").remove();
        $('.wrap-dynamic-sidebar form input[type=button]').attr({
            id: 'add-new-sidebar',
            value: 'Add Sidebar'
        });
        $('.wrap-dynamic-sidebar form input[type=button]').removeAttr('data-id');
        $("#new-sidebar input[type=text], #new-sidebar textarea").val("");
    });
    
    $('.delete-dynamic-sidebar').live('click', function() {
        if($('#new-sidebar #update-new-sidebar').length > 0) {
            popup_message('Update or Cancel editing Dynamic Sidebar to Delete.')
            return false;
        }
        var answer = confirm('Are you sure you want to delete the sidebar?');
        if(!answer) {
            return false;
        }
        var i = $(this);
        var id = $(this).attr('data-id');
        var data = {
            action: 'delete_wp_dynamic_sidebar',
            sidebar_id: id
        };
        jQuery.post(ajaxurl, data,
            function(response) {
                $('#response').html(response);
                i.parent('td').parent('tr').slideUp("slow", function(){
                    $(this).remove();
                });
                if($('#dynamic-sidebar tbody tr').length == 1) {
                    $('#dynamic-sidebar tbody').append('<tr class="no-sidebar"><td colspan="4">You did not created the sidebar yet. You can create it from the form left.</td></tr>');
                }
            });
        return false;
    });
    
    function popup_message(msg) {
        $('#popuup_message').html(msg);
        var b_width = $( window ).width() / 2;
        var b_height = $( window ).height() / 2;
        var e_width = $('#popup_message').width();
        var e_height = $('#popup_message').height();
        var e_left = b_width - (e_width + 165);
        var e_top = b_height - (e_height + 100);
        $('#popuup_message').css({
            top: e_top+'px',
            left: e_left+'px'
        });
        $('#popuup_message').fadeIn(1500).fadeOut(1500);
    }
});

function html_entity_decode(str) {
    try {
        var  tarea=document.createElement('textarea');
        tarea.innerHTML = str; return tarea.value;
        tarea.parentNode.removeChild(tarea);
    } catch(e) {
        //for IE add <div id="htmlconverter" style="display:none;"></div> to the page
        document.getElementById("htmlconverter").innerHTML = '<textarea id="innerConverter">' + str + '</textarea>';
        var content = document.getElementById("innerConverter").value;
        document.getElementById("htmlconverter").innerHTML = "";
        return content;
    }
}