jQuery(document).ready(function ($) {
    $(".add_cat form").submit(function (e) {
        e.preventDefault(); // Prevent default form submission

        let cat_name = $("input[name='cat_name']").val();

        $.ajax({
            type: "POST",
            url: ajax_object.ajax_url, // Use localized URL
            data: {
                action: "add_category",
                cat_name: cat_name,
            },
            success: function (response) {
                if (response.success) {
                    $(".cta-check").append(
                        `<span><input type='checkbox' value='${response.data.id}' id='${response.data.id}' name='cats[]'><label for='${response.data.id}'>${response.data.name}</label></span>`
                    );
                    $("input[name='cat_name']").val(""); // Clear input field
                } else {
                    alert(response.data.message);
                }
            },
        });
    });
});
