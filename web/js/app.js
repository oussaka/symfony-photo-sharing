$(function(){
    $('#favorite').click(function(e){
        var url = Routing.generate(
            'favorite',
            {"id": this.value}
        );

        $.ajax({
            'url': url,
            'type': 'GET',
            'success': function(r) {
                if (r.stat == 'add'){
                    $('#favorite span').removeClass("glyphicon-star-empty").addClass("glyphicon glyphicon-star");
                    $('#num-favorites').html(parseInt($('#num-favorites').html(), 10)+1);
                }else if (r.stat == 'remove'){
                    $('#favorite span').removeClass("glyphicon-star").addClass("glyphicon glyphicon-star-empty");
                    $('#num-favorites').html(parseInt($('#num-favorites').html(), 10)-1);
                }
            }
        });
    });




    var $collectionHolder;
    var $addTagLink = $('<a href="#" class="add_tag_link">Add a tag</a>');
    var $newLinkLi = $('<li></li>').append($addTagLink);

    jQuery(document).ready(function() {
        $collectionHolder = $('ul.tags');

        $collectionHolder.append($newLinkLi);

        $collectionHolder.data('index', $collectionHolder.find(':input').length);

        $addTagLink.on('click', function(e) {
            e.preventDefault();
            addTagForm($collectionHolder, $newLinkLi);
        });
    });

    function addTagForm($collectionHolder, $newLinkLi) {
        var prototype = $collectionHolder.data('prototype');
        var index = $collectionHolder.data('index');
        var newForm = prototype.replace(/__name__/g, index);

        $collectionHolder.data('index', index + 1);

        var $newFormLi = $('<li></li>').append(newForm);
        $newLinkLi.before($newFormLi);
    }


});
