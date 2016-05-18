$(function(){
    
    $('.favorite').click(function(e){
        var id = $(this).data('id');

        var url = Routing.generate(
            'favorite',
            {"id": id}
        );

        $.ajax({
            'url': url,
            'type': 'GET',
            'success': function(r) {
                if (r.stat == 'add'){
                    $('.favorite span#icon-'+id).removeClass("glyphicon-star-empty").addClass("glyphicon glyphicon-star");
                    $('#num-favorites-'+id).html(parseInt($('#num-favorites-'+id).html(), 10)+1);
                }else if (r.stat == 'remove'){
                    $('.favorite span#icon-'+id).removeClass("glyphicon-star").addClass("glyphicon glyphicon-star-empty");
                    $('#num-favorites-'+id).html(parseInt($('#num-favorites-'+id).html(), 10)-1);
                }
            }
        });
    });


    jQuery(document).ready(function() {
        addRemoveButton();
        addButtonTagEvent();
    });

    function addButtonTagEvent() {
        $('#add-tag').on('click', function(e) {
            e.preventDefault();

            var $newLinkLi = $('<li></li>');

            $collectionHolder = $('ul.tags');

            $collectionHolder.data('index', $collectionHolder.find(':input').length);

            var prototype = $collectionHolder.data('prototype');
            var index = $collectionHolder.data('index');
            var newForm = prototype.replace(/__name__/g, index);

            $collectionHolder.data('index', index + 1);

            var $newFormLi = $('<li></li>').append(newForm);

            console.log($newFormLi);
            $collectionHolder.append($newFormLi);

            addRemoveButton();
        });
    }

    function addRemoveButton() {
        $('ul.tags li .btn-danger').click(function(e) {
            e.preventDefault();
            $( this ).parentsUntil('ul').remove();
        });
    }

});
