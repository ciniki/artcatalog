//
function ciniki_artcatalog_fieldupdate() {
    this.init = function() {
        //
        // The main panel, which lists the options for production
        //
        this.list = new M.panel('Fields',
            'ciniki_artcatalog_fieldupdate', 'list',
            'mc', 'medium', 'sectioned', 'ciniki.artcatalog.fieldupdate.list');
        this.list.data = {};
        this.list.sections = {
            'items':{'label':'Fields', 'fields':{}},
            'buttons':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_artcatalog_fieldupdate.save();'},
                }},
            };
        this.list.fieldValue = function(s, i, d) {
            return this.data[i].item.name;
        };
        this.list.addButton('save', 'Save', 'M.ciniki_artcatalog_fieldupdate.save();');
        this.list.addClose('Cancel');
    }

    //
    // Arguments:
    // aG - The arguments to be parsed into args
    //
    this.start = function(cb, appPrefix, aG) {
        args = {};
        if( aG != null ) {
            args = eval(aG);
        }

        //
        // Create the app container if it doesn't exist, and clear it out
        // if it does exist.
        //
        var appContainer = M.createContainer(appPrefix, 'ciniki_artcatalog_fieldupdate', 'yes');
        if( appContainer == null ) {
            M.alert('App Error');
            return false;
        } 

        this.showList(cb, args.field, args.fieldname);
    };

    //
    // Grab the stats for the tenant from the database and present the list of customers.
    //
    this.showList = function(cb, field, fieldname) {
        if( field != null ) {
            this.list.field = field;
        }
        if( fieldname != null ) {
            this.list.fieldname = fieldname;
            this.list.title = fieldname;
            this.list.sections.items.label = fieldname;
        }
        //
        // Grab list of recently updated customers
        //
        var rsp = M.api.getJSON('ciniki.artcatalog.fieldList', {'tnid':M.curTenantID, 'field':this.list.field});
        if( rsp['stat'] != 'ok' ) {
            M.api.err(rsp);
            return false;
        } 
        this.list.data = rsp.items;
        this.list.sections.items.fields = {};
        for(i in rsp.items) {
            this.list.sections.items.fields[i] = {'label':'', 'hidelabel':'yes', 'type':'text'};
        }
        this.list.refresh();
        this.list.show(cb);
    };

    this.save = function() {
        for(i in this.list.data) {
            var c = this.list.formFieldValue(this.list.sections.items.fields[i], i);
            if( this.list.data[i].item.name != c ) {
                var rsp = M.api.getJSON('ciniki.artcatalog.fieldUpdate', 
                    {'tnid':M.curTenantID, 'field':this.list.field,
                    'old_value':this.list.data[i].item.name,
                    'new_value':c});
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                } 
            }
        }
        this.list.close();
    };
}
