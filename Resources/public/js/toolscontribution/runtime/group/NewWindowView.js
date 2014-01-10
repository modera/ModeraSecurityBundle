/**
 * @author Sergei Lissovski <sergei.lissovski@modera.org>
 */
Ext.define('Modera.backend.security.toolscontribution.runtime.group.NewWindowView', {
    extend: 'MF.viewsmanagement.views.BasicNewRecordWindowView',

    requires: [
        'Modera.backend.security.toolscontribution.view.group.NewAndEditWindow'
    ],

    // override
    constructor: function(config) {
        var defaults = {
            id: 'new-group',
            uiFactory: function() {
                return Ext.create('Modera.backend.security.toolscontribution.view.group.NewAndEditWindow', {
                    type: 'new'
                });
            },
            directClass: Actions.ModeraBackendSecurity_Groups
        };

        this.callParent([Ext.apply(defaults, config || {})]);
    }
});