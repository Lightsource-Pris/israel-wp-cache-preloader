//Query to respond to button click for caching
jQuery(document).ready(function($) {
    $('#preload-button').click(function(e) {
        e.preventDefault();
        var postId = $(this).data("post-id");
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: {
                action: "preload_cache_handler",
                post_id: postId,
            },
            success: function(response) {
                console.log(response)
                $("#messenger").css("display","block")
                $("#messenger").css("color","green")
                $("#messenger").html("Cache Preloaded successfully");
                $("#messenger").fadeIn("fast").delay(2000).fadeOut("slow");
            },
            error: function() {
                console.log("Error caching")
                $("#messenger").css("display","block")
                $("#messenger").css("color","red")
                $("#messenger").html("Cache Preload failed");
                $("#messenger").fadeIn("fast").delay(2000).fadeOut("slow");
            },
        });
    });
});