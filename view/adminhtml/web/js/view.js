require(
    [
        'jquery',
        'Magento_Ui/js/modal/modal',
        'mage/url'
    ],
    function(
        $,
        modal,
        url
    ) {
        var options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            //title: 'popup modal title',
            /*buttons: [{
                text: $.mage.__('Continue'),
                class: '',
                click: function () {
                    this.closeModal();
                }
            }]*/
        };

        var popup = modal(options, $('#theFrame'));

        $(document).on('click', '#popup-modal', function () {
            $.post('/admin/integration_module/oauth/activate', {
                'test': 3,
            }).done(function(data) {
                if (data.status == true) {
                    alert('Successfully activated');
                    window.location.href = ''
                } else {
                    alert('Error, try later');
                }
            });
            //$('#theFrame').modal('openModal');
        });

        $(document).on('click', '#add_integration', function () {
            $.post('/admin/integration_module/oauth/add', {
                'test': 3,
            }).done(function(data) {
                if (data.status == true) {
                    alert('Integration successfully added')
                } else {
                    alert('Error, integration already added')
                }
            });
            //$('#theFrame').modal('openModal');
        });

        $(document).on('click', '#deactivate_integration', function () {
            $.post('/admin/integration_module/oauth/deactivate', {
                'test': 3,
            }).done(function(data) {
                if (data.status == true) {
                    alert('Integration successfully deactivated')
                } else {
                    alert('Error, integration already deactivated')
                }
            });
            //$('#theFrame').modal('openModal');
        });

        $(document).on('click', '#delete_integration', function () {
            $.post('/admin/integration_module/oauth/delete', {
                'test': 3,
            }).done(function(data) {
                if (data.status == true) {
                    alert('Integration successfully deleted')
                } else {
                    alert('Error, integration already deleted')
                }
            });
            //$('#theFrame').modal('openModal');
        });
    }
);