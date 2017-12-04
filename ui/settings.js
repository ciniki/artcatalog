//
function ciniki_artcatalog_settings() {
    //
    // Panels
    //
    this.main = null;
    this.add = null;

    this.cb = null;
    this.toggleOptions = {'no':'Off', 'yes':'On'};

    this.themes = {
        'Black':'Blue Titles on Black',
        'Default':'Black Titles on White',
        };

    this.init = function() {
        //
        // The main panel, which lists the options for production
        //
        this.main = new M.panel('Settings',
            'ciniki_artcatalog_settings', 'main',
            'mc', 'medium', 'sectioned', 'ciniki.artcatalog.settings.main');
        this.main.sections = {
//          'advanced':{'label':'Advanced Features', 'fields':{
//              'enable-lists':{'label':'Lists', 'type':'multitoggle', 'default':'no', 'toggles':this.toggleOptions},
//              'enable-tracking':{'label':'Exhibited', 'type':'multitoggle', 'default':'no', 'toggles':this.toggleOptions},
//              'enable-inspiration':{'label':'Inspiration', 'type':'multitoggle', 'default':'no', 'toggles':this.toggleOptions},
//              }},
            '_webflags':{'label':'Default Settings', 'fields':{
                'webflags_1':{'label':'Visible', 'type':'flagtoggle', 'field':'defaults-webflags', 'bit':0x01, 'default':'on'},
                'webflags_12':{'label':'Price', 'type':'flagtoggle', 'field':'defaults-webflags', 'bit':0x0800, 'default':'on'},
                'webflags_13':{'label':'Media', 'type':'flagtoggle', 'field':'defaults-webflags', 'bit':0x1000, 'default':'on'},
                'webflags_9':{'label':'Description', 'type':'flagtoggle', 'field':'defaults-webflags', 'bit':0x0100, 'default':'on'},
                'webflags_14':{'label':'Materials', 'type':'flagtoggle', 'field':'defaults-webflags', 'bit':0x2000, 'default':'on'},
                'webflags_10':{'label':'Inspiration', 'type':'flagtoggle', 'field':'defaults-webflags', 'bit':0x0200, 'default':'on'},
                'webflags_11':{'label':'Awards', 'type':'flagtoggle', 'field':'defaults-webflags', 'bit':0x0400, 'default':'on'},
                }},
            'taxes':{'label':'Taxes', 
                'active':function() { return M.curTenant.modules['ciniki.taxes']!=null?'yes':'no'; },
                'fields':{
                    'taxes-default-taxtype':{'label':'Default Tax Type', 'type':'select', 'options':{}},
                }},
            '_sales':{'label':'Sales Description', 'fields':{
                'forsale-message':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'medium'},
                }},
            '_buttons':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_artcatalog_settings.saveSettings();'},
                }},
        };
        this.main.fieldValue = function(s, i, d) { 
            if( this.data[i] == null ) { return ''; }
            return this.data[i];
        };
        this.main.fieldHistoryArgs = function(s, i) {
            return {'method':'ciniki.artcatalog.settingsHistory', 'args':{'tnid':M.curTenantID, 'setting':i}};
        };
        this.main.addButton('save', 'Save', 'M.ciniki_artcatalog_settings.saveSettings();');
        this.main.addClose('Cancel');
    }

    //
    // Arguments:
    // aG - The arguments to be parsed into args
    //
    this.start = function(cb, appPrefix, aG) {
        args = {};
        if( aG != null ) { args = eval(aG); }

        //
        // Create the app container if it doesn't exist, and clear it out
        // if it does exist.
        //
        var appContainer = M.createContainer(appPrefix, 'ciniki_artcatalog_settings', 'yes');
        if( appContainer == null ) {
            alert('App Error');
            return false;
        } 

        this.showMain(cb);
    }

    //
    // Grab the stats for the tenant from the database and present the list of orders.
    //
    this.showMain = function(cb) {
        var rsp = M.api.getJSONCb('ciniki.artcatalog.settingsGet', {'tnid':M.curTenantID}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_artcatalog_settings.main;
            p.data = rsp.settings;
            p.sections.taxes.active=(M.curTenant.modules['ciniki.taxes']!=null)?'yes':'no';
            if( M.curTenant.modules['ciniki.taxes'] != null ) {
                var types = {'0':'No Tax'};
                for(i in rsp.taxtypes) {
                    types[rsp.taxtypes[i].type.id] = rsp.taxtypes[i].type.name + ((rsp.taxtypes[i].type.rates=='')?', No Taxes':', ' + rsp.taxtypes[i].type.rates);
                }
                p.sections.taxes.fields['taxes-default-taxtype'].options = types;
            }
            p.refresh();
            p.show(cb);
        });
    }

    this.saveSettings = function() {
        var c = this.main.serializeForm('no');
        if( c != '' ) {
            var rsp = M.api.postJSONCb('ciniki.artcatalog.settingsUpdate', 
                {'tnid':M.curTenantID}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    } 
                    M.ciniki_artcatalog_settings.main.close();
                });
        } else {
            this.main.close();
        }
    }
}
