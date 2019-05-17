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
                    alert('Integration successfully installed')
                } else {
                    alert('Error, integration already installed')
                }
            });
            //$('#theFrame').modal('openModal');
        });
    }
);