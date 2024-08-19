//
function ciniki_artcatalog_categories() {
    //
    // Panels
    //
    this.toggleOptions = {'no':'Off', 'yes':'On'};

    this.init = function() {
        //
        // The categories list panel
        //
        this.menu = new M.panel('Settings',
            'ciniki_artcatalog_categories', 'menu',
            'mc', 'narrow', 'sectioned', 'ciniki.artcatalog.categories.menu');
        this.menu.sections = {};
        this.menu.sectionData = function(s) {
            if( this.data[s] != null ) { return this.data[s]; }
            return '';
        };
        this.menu.cellValue = function(s, i, j, d) {
            return d.category.name;
        };
        this.menu.rowFn = function(s, i, d) {
            return 'M.ciniki_artcatalog_categories.editCategory(\'M.ciniki_artcatalog_categories.showMenu();\',\'' + d.category.type + '\',\'' + escape(d.category.name) + '\');';
        };
        this.menu.addClose('Back');

        //
        // The edit category panel
        //
        this.category = new M.panel('Category',
            'ciniki_artcatalog_categories', 'category',
            'mc', 'medium', 'sectioned', 'ciniki.artcatalog.categories.category');
        this.category.sections = {
            '_synopsis':{'label':'Synopsis', 'fields':{
                'synopsis':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'small'},
                }},
            '_description':{'label':'Description', 'type':'simpleform', 'fields':{
                'description':{'label':'', 'type':'textarea', 'size':'large', 'hidelabel':'yes'},
            }},
            '_buttons':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_artcatalog_categories.saveCategory();'},
            }},
        };
        this.category.fieldValue = function(s, i, d) { 
            return this.data[i]; 
        }
        this.category.addButton('save', 'Save', 'M.ciniki_artcatalog_categories.saveCategory();');
        this.category.addClose('Cancel');
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
        var appContainer = M.createContainer(appPrefix, 'ciniki_artcatalog_categories', 'yes');
        if( appContainer == null ) {
            M.alert('App Error');
            return false;
        } 

        this.showMenu(cb);
    }

    //
    // Grab the stats for the tenant from the database and present the list of orders.
    //
    this.showMenu = function(cb) {
        M.api.getJSONCb('ciniki.artcatalog.categoryList', {'tnid':M.curTenantID}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_artcatalog_categories.menu;
            if( rsp.types != null ) {
                p.sections = {};
                p.data = {};
                for(i in rsp.types) {
                    p.data[i] = rsp.types[i].type.categories;
                    p.sections[i] = {
                        'label':rsp.types[i].type.name, 'type':'simplegrid', 'num_cols':1
                        };
                }
            } else {
                p.data = {'categories':rsp.categories};
                p.sections = {'categories':{
                    'label':'Categories', 'type':'simplegrid', 'num_cols':1,
                    }};
            }
            p.refresh();
            p.show(cb);
        });
    }

    this.editCategory = function(cb, type, name) {
        if( type != null ) { this.category.artcatalog_type = type; }
        if( name != null ) { this.category.category_name = unescape(name); }
        M.api.getJSONCb('ciniki.artcatalog.categoryDetails', {'tnid':M.curTenantID,
            'type':M.eU(this.category.artcatalog_type), 'category':M.eU(this.category.category_name)}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_artcatalog_categories.category;
            p.data = rsp.details;
            p.refresh();
            p.show(cb);
        }); 
    }

    this.saveCategory = function() {
        var c = this.category.serializeFormData('no');
        if( c != '' ) {
            M.api.postJSONFormData('ciniki.artcatalog.categoryUpdate', 
                {'tnid':M.curTenantID, 'artcatalog_type':this.category.artcatalog_type,
                'category':M.eU(this.category.category_name)}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    } 
                    M.ciniki_artcatalog_categories.category.close();
                });
        } else {
            M.ciniki_artcatalog_categories.category.close();
        }
    }
}
