function reloadTags(){

    var tagArea = $('#tagarea');
    var jobId = tagArea.data('job-id');

    var data = {
        jobId: jobId
    };

    tagArea.empty();

    $.ajax({
        type: "GET",
        data: data,
        dataType: 'json',
        url: "/web/tags",
        success: function(result) {
            // console.log(result);

            result.forEach(function(value, index) {
                tagArea.append($('<span/>').addClass( "badge badge-pill badge-warning tag-pill mt-1" ).data( "tag-id", value.id ).text(value.name)).append('</br>');
            });

        },
        error: function(result) {
        }
    });
}

$("#tagarea").on({
    mouseenter: function () {
    $( this ).append( $( ' <button type="button" class="btn btn-danger btn-sm tag-remove ml-1"><i class="fas fa-times fa-sm"></i></button>' ) );
    },
    mouseleave: function () {
    $( this ).find( "button" ).remove();
    }
},'.tag-pill');

$("#tagarea").on("click", ".tag-remove", function(e) {
    e.preventDefault();
    var jobId = $("#tagarea").data('job-id');
    var tagId = $(this).closest("span").data('tag-id');

    var data = {
        jobId: jobId,
        tagId: tagId
    };

    // console.log(data);
    $.ajax({
        type: "DELETE",
        data: JSON.stringify(data),
        processData: false,
        contentType : 'application/json',
        dataType: 'json',
        url: "/web/tag",
        success: function(result) {
            reloadTags();
        },
        error: function(result) {
        }
    });
});

$(".tag-enter").click(function(e) {
    e.preventDefault();
    $('#tag-form').removeClass('invisible');
});

$("#tag-cancel").click(function(e) {
    e.preventDefault();
    $('#tag-form :input').val('');
    $('#tag-form').addClass('invisible');
});

$("#tag-add").click(function(e) {
    e.preventDefault();
    var tagName = $('#tagname').val();
    var tagType = $('#tagtype').val();
    var jobId = $(this).data('job-id');
    $('#tag-form :input').val('');
    $('#tag-form').addClass('invisible');

    var data = {
        id: jobId,
        name: tagName,
        type: tagType
    };

    $.ajax({
        type: "POST",
        data: JSON.stringify(data),
        processData: false,
        contentType : 'application/json',
        dataType: 'json',
        url: "/web/tags",
        success: function(result) {
            reloadTags();
        },
        error: function(result) {
        }
    });
});

