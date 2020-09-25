$(document).ready(function () {
    $(document).on('click', '.delete-user', function () {
        var objThis = $(this);
        var user_id = objThis.attr('data-id');
        objThis.parent().hide();
        $.ajax({
            type: 'POST',
            url: '/local/components/custom/crm.presales.edit/ajax.php',
            data: {
                id: user_id,
                group: presaleGroup,
                action: "DEL_USER"
            },
            dataType: "html",
            success: function (data) {
                var data = JSON.parse(data);
                if (data.success === 'true') {
                    objThis.parent().remove();
                }
                if (data.success === 'false') {
                    objThis.parent().show();
                }
            },
        });
        return false;
    });

    $(document).on('click', '.make-main-presale, .delete-main-presale', function () {

        $("#error-info").html("");
        var objThis = $(this);
        var user_id = objThis.attr('data-id');
        var classAction = objThis.attr('class');
        objThis.removeClass(classAction);
        var action = '';

        if (classAction === 'make-main-presale') {
            action = "MAKE_MAIN_PRESALE";
        } else if (classAction === 'delete-main-presale') {
            action = "DELETE_MAIN_PRESALE";
        }

        $.ajax({
            type: 'POST',
            url: '/local/components/custom/crm.presales.edit/ajax.php',
            data: {
                id: user_id,
                group: presaleGroup,
                departmentId: departmentId,
                action: action
            },
            dataType: "html",
            success: function (data) {
                data = JSON.parse(data);
                if (data.success === 'true') {
                    if (classAction === 'make-main-presale') {
                        objThis.addClass('delete-main-presale');
                        objThis.text('Убрать статус "Главный пресейл"');
                    } else if (classAction === 'delete-main-presale') {
                        objThis.addClass('make-main-presale');
                        objThis.text('Поставить статус "Главный пресейл"');
                    }
                }
                if (data.success === 'false') {
                    objThis.addClass(classAction);
                    $("#error-info").html(data.text_error);
                }
            },
        });
        return false;
    });


    //Живой поиск
    $('.who').bind("change keyup input click", function () {
        if (this.value.length >= 3) {
            $("#error-info").html('');
            $.ajax({
                type: 'POST',
                url: "/local/components/custom/crm.presales.edit/ajax.php",
                data: {referal: this.value, action: "LIVE_SEARCH", userList: presaleUserListToSearch},
                dataType: "html",
                response: 'text',
                success: function (data) {
                    var data = JSON.parse(data);
                    if (data.success === 'true') {
                        $(".search_result").html(data.list).fadeIn();
                    }
                    if (data.success === 'false') {
                        $(".search_result").fadeOut();
                    }
                }
            })
        } else {
            $(".search_result").fadeOut();
        }
    });

    $(".search_result").hover(function () {
        //$("#error-info").html('');
        $(".who").blur(); //Убираем фокус с input
    });

    //При выборе результата поиска, прячем список и заносим выбранный результат в input
    $(".search_result").on("click", "li", function () {
        $("#error-info").html('');
        var objThis = $(this);
        var user_id = objThis.attr('data-id');
        var s_user = objThis.text();
        $(".who").val(s_user);
        $(".search_result").fadeOut();
        $.ajax({
            type: 'POST',
            url: "/local/components/custom/crm.presales.edit/ajax.php",
            data: {
                id: user_id,
                group: presaleGroup,
                departmentId: departmentId,
                maxCount: maxCountInGroup,
                action: "ADD_USER"
            },
            dataType: "html",
            response: 'html',
            success: function (data) {
                var data = JSON.parse(data);
                console.log(data.html_text);
                if (data.success === 'true') {
                    console.log(data.html_text);
                    $("#user-block-presales").append(data.html_text);
                }
                if (data.success === 'false') {
                    $("#error-info").html(data.text_error);
                }
            }
        });

    });

});

