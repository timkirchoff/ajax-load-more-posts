(function($){

    // Blog Ajax Load More
    var content = $('.ajax-posts');
    var loader = $('#load-more-posts');
    var ppp = 9;
    var offset = $('.post-list').find('.post-box').length;
    var cat = loader.attr('data-category');
     
    loader.on( 'click', load_ajax_posts );
     
    function load_ajax_posts() {
        if (!(loader.hasClass('post-loading-loader') || loader.hasClass('post-no-more-posts'))) {

            // grab the category attribute
            var updated_cat = loader.attr('data-category');

            // if the category is new
            if (cat != updated_cat) {
                offset = ppp;
                cat = updated_cat;
            }

            // get the excluded post id
            var exclude = loader.attr('data-exclude');

            $.ajax({
                type: 'POST',
                dataType: 'html',
                url: ajax_params.ajax_url,
                data: {
                    'cat': cat,
                    'ppp': ppp,
                    'offset': offset,
                    'exclude': exclude,
                    'action': 'ajax_load_more_posts'
                },
                beforeSend : function () {
                    loader.addClass('post-loading-loader');
                    $('.after-post-component').addClass('hidden');
                    $('.loading-animation').removeClass('hidden');
                },
                success: function (data) {
                    var data = $(data);
                    loader.parent().removeClass('loading');
                    if (data.length) {
                        var newElements = data.css({ opacity: 0 });
                        content.append(newElements);
                        newElements.animate({ opacity: 1 });
                        loader.removeClass('post-no-more-posts').removeClass('post-loading-loader');
                        $('.after-post-component').addClass('hidden');
                        var post_count = $('.post-list').find('.post-box').length;
                        if (post_count < offset) {
                            // do not show button
                        } else {
                            $('.load-more-posts').removeClass('hidden');
                        }
                    } else {
                        loader.removeClass('post-loading-loader').addClass('post-no-more-posts');
                        $('.after-post-component').addClass('hidden');
                        $('.no-more-message').removeClass('hidden');
                    }
                },
                error : function (errorResponse, textStatus, errorThrown) {
                    loader.html($.parseJSON(errorResponse.responseText) + ' :: ' + textStatus + ' :: ' + errorThrown);
                    console.log(errorResponse);
                },
            });
        }
        offset += ppp;
        return false;
    }


    // Blog Filter
    var content = $('.ajax-posts');
    var loader = $('#load-more-posts');
    var select = $('.blog-category-select');
    var ppp = 9;
    var exclude = loader.attr('data-exclude');
     
    select.on( 'change', category_filter_ajax_posts );
     
    function category_filter_ajax_posts() {

        var cat = $(this).val();
        loader.attr('data-category', cat);
        content.html('');

        $.ajax({
            type: 'POST',
            dataType: 'html',
            url: ajax_params.ajax_url,
            data: {
                'cat': cat,
                'ppp': ppp,
                'offset': 0,
                'exclude': exclude,
                'action': 'ajax_load_more_posts'
            },
            beforeSend : function () {
                loader.addClass('post-loading-loader');
                $('.after-post-component').addClass('hidden');
                $('.loading-animation').removeClass('hidden');
            },
            success: function (data) {
                var data = $(data);
                loader.parent().removeClass('loading');
                if (data.length) {
                    var newElements = data.css({ opacity: 0 });
                    content.html(newElements);
                    newElements.animate({ opacity: 1 });
                    loader.removeClass('post-no-more-posts').removeClass('post-loading-loader');
                    $('.after-post-component').addClass('hidden');
                    var post_count = $('.post-list').find('.post-box').length;
                    if (post_count < ppp) {
                        // do not show button
                    } else {
                        $('.load-more-posts').removeClass('hidden');
                    }
                } else {
                    loader.removeClass('post-loading-loader').addClass('post-no-more-posts');
                    $('.after-post-component').addClass('hidden');
                    $('.no-more-message').removeClass('hidden');
                }
            },
            error : function (errorResponse, textStatus, errorThrown) {
                loader.html($.parseJSON(errorResponse.responseText) + ' :: ' + textStatus + ' :: ' + errorThrown);
                console.log(errorResponse);
            },
        });
        return false;
    }

})(jQuery);