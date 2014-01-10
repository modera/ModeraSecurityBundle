/**
 * @author Sergei Lissovski <sergei.lissovski@modera.org>
 */
Ext.define('Modera.backend.security.toolscontribution.runtime.group.DeleteWindowView', {
    extend: 'MF.viewsmanagement.views.BasicDeleteRecordWindowView',

    requires: [
        'MFC.window.DeleteRecordConfirmationWindow'
    ],

    // override
    constructor: function(config) {
        var defaults = {
            id: 'delete-group',
            uiClass: 'MFC.window.DeleteRecordConfirmationWindow',
            directClass: Actions.ModeraBackendSecurity_Groups,
            responseRecordNameKey: 'name'
        };

        this.callParent([Ext.apply(defaults, config || {})]);
    }
});