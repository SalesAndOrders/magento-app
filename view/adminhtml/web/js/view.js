require(
    [
        'jquery',
        'Magento_Ui/js/modal/modal',
        'mage/url'
    ],
    function (
        $,
        modal,
        url
    ) {
        var options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
        };

        var headLoader = $('.header-view-block .loader');
        var headActivate = $('.header-view-block .activate')

        $(document).on(
            'click', '#popup-modal', function () {
                headLoader.show();
                headActivate.hide();

                var urlStr = url.build('integration_module/oauth/activate');
                //var urlStr = BASE_URL+'oauth/activate';
                $.post(
                    urlStr, {
                        'test': 3,
                    }
                ).done(
                    function (data) {
                        headLoader.hide();
                        if (data.status) {
                            alert(data.message);
                            window.location.href = ''
                        } else {
                            headActivate.show();
                            alert(data.message);
                        }
                    }
                ).fail(
                    function (resp) {
                        headLoader.hide();
                        headActivate.show();
                        alert('Error, plese try install later');
                    }
                );
            }
        );

        $(document).on(
            'click', '#add_integration', function () {
                var urlStr = url.build('integration_module/oauth/add');
                $.post(
                    urlStr, {
                        'test': 3,
                    }
                ).done(
                    function (data) {
                        if (data.status == true) {
                            alert('Integration successfully added');
                            window.location.href = ''
                        } else {
                            alert('Error, integration already added');
                        }
                    }
                );
            }
        );

        $(document).on(
            'click', '#deactivate_integration', function () {
                var urlStr = url.build('integration_module/oauth/deactivate');
                $.post(
                    urlStr, {
                        'test': 3,
                    }
                ).done(
                    function (data) {
                        if (data.status == true) {
                            alert('Integration successfully deactivated');
                            window.location.href = ''
                        } else {
                            alert('Error, integration already deactivated');
                        }
                    }
                );
            }
        );

        $(document).on(
            'click', '#delete_integration', function () {
                var urlStr = url.build('integration_module/oauth/delete');
                $.post(
                    urlStr, {
                        'test': 3,
                    }
                ).done(
                    function (data) {
                        if (data.status == true) {
                            alert('Integration successfully deleted');
                            window.location.href = ''
                        } else {
                            alert('Error, integration already deleted');
                        }
                    }
                );
            }
        );
    }
);
