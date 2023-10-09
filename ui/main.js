//
// The artcatalog app to manage an artists collection
//
function ciniki_artcatalog_main() {
    this.toggleOptions = {
        'no':'No',
        'yes':'Yes',
    };
    this.itemFlags = {
        '1':{'name':'For Sale'},
        '2':{'name':'Sold'},
        };
    this.noyesToggles = {
        'no':'No',
        'yes':'Yes',
        };
    this.webFlags = {
        '1':{'name':'Hide'},
        '5':{'name':'Category Highlight'},
        };
    this.monthOptions = {
        '0':'Unspecified',
        '1':'January',
        '2':'February',
        '3':'March',
        '4':'April',
        '5':'May',
        '6':'June',
        '7':'July',
        '8':'August',
        '9':'September',
        '10':'October',
        '11':'November',
        '12':'December',
        };
    this.dayOptions = {
        '0':'Unspecified',
        '1':'1',
        '2':'2',
        '3':'3',
        '4':'4',
        '5':'5',
        '6':'6',
        '7':'7',
        '8':'8',
        '9':'9',
        '10':'10',
        '11':'11',
        '12':'12',
        '13':'13',
        '14':'14',
        '15':'15',
        '16':'16',
        '17':'17',
        '18':'18',
        '19':'19',
        '20':'20',
        '21':'21',
        '22':'22',
        '23':'23',
        '24':'24',
        '25':'25',
        '26':'26',
        '27':'27',
        '28':'28',
        '29':'29',
        '30':'30',
        '31':'31',
        };
    this.statusOptions = {
        '10':'Not for Sale',
        '20':'For Sale',
        '50':'Sold',
        '60':'Private Collection',
        '70':'Artist Collection',
        '80':'Commissioned',
        '85':'Donated',
        '90':'Gifted',
        };
    this.cur_type = null;

    //
    // Setup the main panel to list the collection
    //
    this.menu = new M.panel('Catalog',
        'ciniki_artcatalog_main', 'menu',
        'mc', 'large narrowaside', 'sectioned', 'ciniki.artcatalog.main.menu');
    this.menu.data = {};
    this.menu.sectiontab = 'categories';
    this.menu.sections = {
        'types':{'label':'', 'visible':'no', 'type':'menutabs', 'selected':'0', 'tabs':{}},
        'sectiontabs':{'label':'', 'type':'menutabs', 'selected':'categories', 'tabs':{}},
        'categories':{'label':'Categories', 'hidelabel':'yes', 'type':'simplegrid', 'aside':'yes',
            'visible':function() { return M.ciniki_artcatalog_main.menu.sections.sectiontabs.selected == 'categories' ? 'yes' : 'no';},
            'num_cols':1,
            'noData':'No categories found',
            'selected':'Unknown',
            'editFn':function(s, i, d) {
                return 'M.ciniki_artcatalog_main.changename.open(\'M.ciniki_artcatalog_main.menu.open();\',\'Category\',\'' + escape(d.name) + '\')';
                },
            },
        'media':{'label':'Media', 'hidelabel':'yes', 'type':'simplegrid', 'aside':'yes',
            'visible':function() { return M.ciniki_artcatalog_main.menu.sections.sectiontabs.selected == 'media' ? 'yes' : 'no';},
            'num_cols':1,
            'noData':'No media found',
            'selected':'Unknown',
            'editFn':function(s, i, d) {
                return 'M.ciniki_artcatalog_main.changename.open(\'M.ciniki_artcatalog_main.menu.open();\',\'Media\',\'' + escape(d.name) + '\')';
                },
            },
        'locations':{'label':'Locations', 'hidelabel':'yes', 'type':'simplegrid', 'aside':'yes',
            'visible':function() { return M.ciniki_artcatalog_main.menu.sections.sectiontabs.selected == 'locations' ? 'yes' : 'no';},
            'num_cols':1,
            'noData':'No locations found',
            'selected':'Unknown',
            'editFn':function(s, i, d) {
                return 'M.ciniki_artcatalog_main.changename.open(\'M.ciniki_artcatalog_main.menu.open();\',\'Location\',\'' + escape(d.name) + '\')';
                },
            },
        'years':{'label':'Years', 'hidelabel':'yes', 'type':'simplegrid', 'aside':'yes',
            'visible':function() { return M.ciniki_artcatalog_main.menu.sections.sectiontabs.selected == 'years' ? 'yes' : 'no';},
            'num_cols':1,
            'noData':'No years found',
            'selected':'Unknown',
            'editFn':function(s, i, d) {
                return 'M.ciniki_artcatalog_main.changename.open(\'M.ciniki_artcatalog_main.menu.open();\',\'Year\',\'' + escape(d.name) + '\')';
                },
            },
        'materials':{'label':'Materials', 'hidelabel':'yes', 'type':'simplegrid', 'aside':'yes',
            'visible':function() { return M.ciniki_artcatalog_main.menu.sections.sectiontabs.selected == 'materials' ? 'yes' : 'no';},
            'num_cols':1,
            'noData':'No materials found',
            'selected':'',
            'editFn':function(s, i, d) {
                return 'M.ciniki_artcatalog_main.changename.open(\'M.ciniki_artcatalog_main.menu.open();\',\'Material\',\'' + escape(d.name) + '\')';
                },
            },
        'activetracking':{'label':'Current Exhibitions', 'hidelabel':'no', 'type':'simplegrid', 'aside':'yes',
            'visible':function() { return M.ciniki_artcatalog_main.menu.sections.sectiontabs.selected == 'tracking' ? 'yes' : 'no';},
            'cellClasses':['multiline', 'multiline'],
            'selected':'',
            'start_date':'',
            'end_date':'',
            'num_cols':1,
            'noData':'Nothing exhibited',
            'editFn':function(s, i, d) {
                return 'M.ciniki_artcatalog_main.trackinggroup.open(\'M.ciniki_artcatalog_main.menu.open();\',\'' + escape(d.name) + '\',\'' + escape(d.start_date) + '\',\'' + escape(d.end_date) + '\')';
                },
            },
        '_buttons':{'label':'', 'aside':'yes', 'buttons':{
            'pdf':{'label':'Download Catalog', 'fn':'M.ciniki_artcatalog_main.showDownload(\'M.ciniki_artcatalog_main.menu.open();\',\'ciniki.artcatalog.listWithImages\',M.ciniki_artcatalog_main.menu.listby,\'\',\'\',\'Catalog\');'},
            'categorypdf':{'label':'Download Category', 
                'visible':function() { 
                    return M.ciniki_artcatalog_main.menu.sections.sectiontabs.selected == 'categories' && M.ciniki_artcatalog_main.menu.data.items.length > 0 ? 'yes' : 'no';
                    },
                'fn':'M.ciniki_artcatalog_main.showDownload(\'M.ciniki_artcatalog_main.menu.open();\',\'ciniki.artcatalog.listWithImages\',\'category\',M.ciniki_artcatalog_main.menu.sections.categories.selected,M.ciniki_artcatalog_main.menu.sections.types.selected,M.ciniki_artcatalog_main.menu.sections.categories.selected);',
                },
            'mediapdf':{'label':'Download Media', 
                'visible':function() { 
                    return M.ciniki_artcatalog_main.menu.sections.sectiontabs.selected == 'media' && M.ciniki_artcatalog_main.menu.data.items.length > 0 ? 'yes' : 'no';
                    },
                'fn':'M.ciniki_artcatalog_main.showDownload(\'M.ciniki_artcatalog_main.menu.open();\',\'ciniki.artcatalog.listWithImages\',\'media\',M.ciniki_artcatalog_main.menu.sections.media.selected,M.ciniki_artcatalog_main.menu.sections.types.selected,M.ciniki_artcatalog_main.menu.sections.media.selected);',
                },
            'locationpdf':{'label':'Download Location', 
                'visible':function() { 
                    return M.ciniki_artcatalog_main.menu.sections.sectiontabs.selected == 'locations' && M.ciniki_artcatalog_main.menu.data.items.length > 0 ? 'yes' : 'no';
                    },
                'fn':'M.ciniki_artcatalog_main.showDownload(\'M.ciniki_artcatalog_main.menu.open();\',\'ciniki.artcatalog.listWithImages\',\'location\',M.ciniki_artcatalog_main.menu.sections.locations.selected,M.ciniki_artcatalog_main.menu.sections.types.selected,M.ciniki_artcatalog_main.menu.sections.locations.selected);',
                },
            'yearpdf':{'label':'Download Year', 
                'visible':function() { 
                    return M.ciniki_artcatalog_main.menu.sections.sectiontabs.selected == 'years' && M.ciniki_artcatalog_main.menu.data.items.length > 0 ? 'yes' : 'no';
                    },
                'fn':'M.ciniki_artcatalog_main.showDownload(\'M.ciniki_artcatalog_main.menu.open();\',\'ciniki.artcatalog.listWithImages\',\'year\',M.ciniki_artcatalog_main.menu.sections.years.selected,M.ciniki_artcatalog_main.menu.sections.types.selected,M.ciniki_artcatalog_main.menu.sections.years.selected);',
                },
            'trackingpdf':{'label':'Download Exhibit', 
                'visible':function() { 
                    return M.ciniki_artcatalog_main.menu.sections.sectiontabs.selected == 'tracking' && M.ciniki_artcatalog_main.menu.data.items.length > 0 ? 'yes' : 'no';
                    },
                'fn':'M.ciniki_artcatalog_main.menu.showDownload(\'tracking\');',
                },
            }},
        'pasttracking':{'label':'Past Exhibitions', 'hidelabel':'no', 'type':'simplegrid', 'aside':'yes',
//            'collapsable':'yes', 'collapse':'all',
            'visible':function() { return M.ciniki_artcatalog_main.menu.sections.sectiontabs.selected == 'tracking' ? 'yes' : 'no';},
            'cellClasses':['multiline', 'multiline'],
            'selected':'',
            'num_cols':1,
            'noData':'No past exhibitions',
            'editFn':function(s, i, d) {
                return 'M.ciniki_artcatalog_main.trackinggroup.open(\'M.ciniki_artcatalog_main.menu.open();\',\'' + escape(d.name) + '\',\'' + escape(d.start_date) + '\',\'' + escape(d.end_date) + '\')';
                },
            },
        'search':{'label':'', 'type':'livesearchgrid', 'livesearchempty':'no', 'livesearchcols':3, 'hint':'search',
            'noData':'No art found',
            'headerValues':null,
            'cellClasses':['thumbnail', 'multiline', 'multiline'],
            },
        'items':{'label':'', 'type':'simplegrid', 'num_cols':3,
            'cellClasses':['thumbnail', 'multiline', 'multiline'],
//            'noData':'No Items Found',
            'addTxt':'Add',
            'addFn':'M.ciniki_artcatalog_main.menu.addItem();',
            },
    };
    this.menu.addItem = function() {
        M.ciniki_artcatalog_main.edit.open('M.ciniki_artcatalog_main.menu.open();',0,M.ciniki_artcatalog_main.menu.sections.sectiontabs.selected,M.ciniki_artcatalog_main.menu.sections[M.ciniki_artcatalog_main.menu.sections.sectiontabs.selected].selected,null);
    }
    this.menu.showDownload = function(type,id) {
        if( type == 'tracking' ) {
            var name = '';
            for(var i in this.data.activetracking) {
                if( this.sections.activetracking.selected == this.data.activetracking[i].id ) {
                    name = this.data.activetracking[i].name;
                    break;
                }
            }
            if( name == '' ) {
                for(var i in this.data.pasttracking) {
                    if( this.sections.pasttracking.selected == this.data.pasttracking[i].id ) {
                        name = this.data.pasttracking[i].name;
                        break;
                    }
                }
            }
            M.ciniki_artcatalog_main.showDownload(
                'M.ciniki_artcatalog_main.menu.open();', 
                'ciniki.artcatalog.listWithImages',
                'tracking',
                this.sections.activetracking.selected,
                this.sections.types.selected,
                name);
        }
    }
    this.menu.liveSearchCb = function(s, i, v) {
        if( v != '' ) {
            M.api.getJSONBgCb('ciniki.artcatalog.searchQuick', {'tnid':M.curTenantID, 'start_needle':v, 'limit':'15'},
                function(rsp) {
                    M.ciniki_artcatalog_main.menu.liveSearchShow(s, null, M.gE(M.ciniki_artcatalog_main.menu.panelUID + '_' + s), rsp.items);
                });
        }
        return true;
    };
    this.menu.liveSearchResultValue = function(s, f, i, j, d) {
        return this.cellValue(s, i, j, d);
    };
    this.menu.liveSearchResultRowFn = function(s, f, i, j, d) {
        return this.rowFn(s, i, d);
    };
    this.menu.liveSearchResultRowStyle = function(s, f, i, d) { return ''; };
    this.menu.sectionData = function(s) { 
        return this.data[s];
    };
    this.menu.cellValue = function(s, i, j, d) {
        if( ['activetracking','pasttracking'].indexOf(s) >= 0 ) {
            return '<span class="maintext">' + d.name + ' <span class="count">' + d.count + '</span></span>'
                + '<span class="subtext">' + d.start_date + ' - ' + d.end_date + '</span>';
        }
        if( ['categories','media','locations','years','materials','lists'].indexOf(s) >= 0 ) {
            return d.name + ' <span class="count">' + d.count + '</span>';
        }
        if( (s == 'items' || s == 'search') && j == 0 ) { 
            if( d.image_id > 0 ) {
                if( d.image != null && d.image != '' ) {
                    return '<img width="75px" height="75px" src=\'' + d.image + '\' />'; 
                } else {
                    return '<img width="75px" height="75px" src=\'' + M.api.getBinaryURL('ciniki.artcatalog.getImage', {'tnid':M.curTenantID, 'image_id':d.image_id, 'version':'thumbnail', 'maxwidth':'75'}) + '\' />'; 
                }
            } else {
                return '<img width="75px" height="75px" src=\'/ciniki-mods/core/ui/themes/default/img/noimage_75.jpg\' />';
            }
        }
        if( (s == 'items' || s == 'search') && j == 1 ) { 
            var sold = '';
            var price = '<b>Price</b>: ';
            var media = '';
            var size = '';
            if( d.price != '' ) {
                price += d.price;
            }
            var subtxt = '';
            subtxt += (d.catalog_number!=''?'<b>Number</b>: ' + d.catalog_number:'');
            subtxt += (d.location!=''?(subtxt!=''?', ':'') + '<b>Location</b>: ' + d.location:'');
            var subtxt2 = '';
            subtxt2 += (d.media!=''?(subtxt2!=''?', ':'') + '<b>Media</b>: ' + d.media:'');
            subtxt2 += (d.size!=''?(subtxt2!=''?', ':'') + '<b>Size</b>: ' + d.size:'');
            subtxt2 += (d.framed_size!=''?(subtxt2!=''?', ':'') + '<b>Framed</b>: ' + d.framed_size:'');
            if( subtxt != '' && subtxt2 != '' ) { subtxt += '<br/>'; }
            subtxt += subtxt2;
            return '<span class="maintext">' + d.name + '</span>'
                + (subtxt!=''?'<span class="subtext">'+subtxt+'</span>':'');
        }
        if( (s == 'items' || s == 'search') && j == 2 ) {
            return '<span class="maintext">' + d.status_text + '</span>'
            + '<span class="subtext">' + d.price + '</span>'; }
    }
    this.menu.switchType = function(t) {
        this.sections.types.selected = t;
        this.open();
    }
    this.menu.switchSection = function(t) {
        this.sections.sectiontabs.selected = t;
        this.open();
    }
    this.menu.selectSectionItem = function(s,c,sd,ed) {
        if( s == 'pasttracking' ) {
            this.sections['activetracking'].selected = unescape(c);
            this.sections['activetracking'].start_date = sd;
            this.sections['pasttracking'].end_date = ed;
        }
        this.sections[s].selected = unescape(c);

        this.open();
    }
    this.menu.savePos = function(s) {
        if( s == 'items' ) {
            return M.panel.prototype.savePos.call(this,s);
        } else {
            this.lastY = 0;
        }
        return true;
    }
    this.menu.rowClass = function(s, i, d) {
        if( s == 'pasttracking' || s == 'activetracking' ) {
            if( this.sections.activetracking.selected == d.id ) {  
                return 'highlight';
            } else {
                return '';
            }
        }
        if( ['categories','media','locations','years','materials','lists'].indexOf(s) >= 0 
            && this.sections[s].selected == d.name
            ) {
            return 'highlight';
        }
        return '';
    }
    this.menu.rowFn = function(s, i, d) {
        if( ['activetracking','pasttracking'].indexOf(s) >= 0 ) {
            return 'M.ciniki_artcatalog_main.menu.selectSectionItem(\'' + s + '\',\'' + escape(d.id) + '\',\'' + d.start_date + '\',\'' + d.end_date + '\');';
        }
        if( ['categories','media','locations','years','materials','lists'].indexOf(s) >= 0 ) {
            return 'M.ciniki_artcatalog_main.menu.selectSectionItem(\'' + s + '\',\'' + escape(d.name) + '\');';
        }
        if( s == 'search' || s == 'items' ) {
            // FIXME: Have saved search term to return back to
            return 'M.ciniki_artcatalog_main.edit.open(\'M.ciniki_artcatalog_main.menu.open();\',' + d.id + ',null,null,M.ciniki_artcatalog_main.menu.data[\'' + s + '\']);';
        }
    };
    this.menu.open = function(cb) {
        var args = {'tnid':M.curTenantID};
        switch(this.sections.sectiontabs.selected) {
            case 'categories':
                args['category'] = this.sections.categories.selected;
                break;
            case 'media':
                args['media'] = this.sections.media.selected;
                break;
            case 'locations':
                args['location'] = this.sections.locations.selected;
                break;
            case 'years':
                args['year'] = this.sections.years.selected;
                break;
            case 'materials':
                args['material'] = this.sections.materials.selected;
                break;
            case 'lists':
                args['list'] = this.sections.lists.selected;
                break;
            case 'tracking':
                args['tracking'] = this.sections.activetracking.selected;
                args['start_date'] = this.sections.activetracking.start_date;
                args['end_date'] = this.sections.activetracking.end_date;
                break;
        }
        if( this.sections.types.selected > 0 ) {
            args['type'] = this.sections.types.selected;
        }
        M.api.getJSONCb('ciniki.artcatalog.list', args, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_artcatalog_main.menu;
            p.data = rsp;

            // Setup types if multiple
            p.sections.types.visible = (rsp.types.length > 1 ? 'yes' : 'no');
            p.sections.types.tabs = {}
            p.sections.types.tabs[0] = {'label':'All', 'fn':'M.ciniki_artcatalog_main.menu.switchType(0);'};
            for(var i in rsp.types) {
                p.sections.types.tabs[rsp.types[i].type] = {'label':rsp.types[i].name, 'fn':'M.ciniki_artcatalog_main.menu.switchType(\'' + rsp.types[i].type + '\');'};
            }

            // Setup categories if multiple
            p.sections.sectiontabs.visible = 'yes';
            p.sections.sectiontabs.tabs = {};
            for(var i in rsp.sections) {
                p.sections.sectiontabs.tabs[i] = {'label':rsp.sections[i].label, 'fn':'M.ciniki_artcatalog_main.menu.switchSection(\'' + i + '\');'};
            }

            p.refresh();
            p.show(cb);
            });
    }
    this.menu.addButton('add', 'Add', 'M.ciniki_artcatalog_main.edit.open(\'M.ciniki_artcatalog_main.menu.show();\',0);');
    this.menu.addButton('tools', 'Tools', 'M.ciniki_artcatalog_main.tools.show(\'M.ciniki_artcatalog_main.menu.show();\');');
    this.menu.addClose('Back');

    //
    // Setup the panel to list the collection of a category/media/location/year
    //
    this.trackinggroup = new M.panel('Exhibited Settings',
        'ciniki_artcatalog_main', 'trackinggroup',
        'mc', 'medium', 'sectioned', 'ciniki.artcatalog.main.trackinggroup');
    this.trackinggroup.data = {};
    this.trackinggroup.sections = {
        'info':{'label':'Place', 'type':'simpleform', 
            'fields':{
                'name':{'label':'Name', 'type':'text', 'livesearch':'yes', 'livesearchempty':'yes'},
                'start_date':{'label':'Start', 'type':'date'},
                'end_date':{'label':'End', 'type':'date'},
            }},
        '_buttons':{'label':'', 'buttons':{
            'update':{'label':'Update', 'fn':'M.ciniki_artcatalog_main.trackinggroup.save();'},
            'delete':{'label':'Delete', 'fn':'M.ciniki_artcatalog_main.trackinggroup.remove();'},
            }},
        };
    this.trackinggroup.fieldValue = function(s, i, d) { 
        return this.data[i]; 
    }
    this.trackinggroup.open = function(cb,name,sd,ed) {
        this.data = {
            'name':unescape(name),
            'start_date':unescape(sd),
            'end_date':unescape(ed),
            }
        this.refresh();
        this.show(cb);
    }
    this.trackinggroup.save = function() {
        var c = this.serializeForm('no');
        if( c != '' ) {
            c += '&org_name=' + M.eU(this.data.name);
            c += '&org_start_date=' + M.eU(this.data.start_date);
            c += '&org_end_date=' + M.eU(this.data.end_date);
            M.api.postJSONCb('ciniki.artcatalog.trackingGroupUpdate', {'tnid':M.curTenantID}, c, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                } else {
                    M.ciniki_artcatalog_main.trackinggroup.close();
                }
            });
        } 
    }
    this.trackinggroup.remove = function() {
        M.confirm('Are you sure you want to delete \'' + this.data.name + '\'?',null,function() {
            c += '&name=' + M.eU(M.ciniki_artcatalog_main.trackinggroup.data.name);
            c += '&start_date=' + M.eU(M.ciniki_artcatalog_main.trackinggroup.data.start_date);
            c += '&end_date=' + M.eU(M.ciniki_artcatalog_main.trackinggroup.data.end_date);
            M.api.postJSONCb('ciniki.artcatalog.trackingGroupDelete', {'tnid':M.curTenantID}, c, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_artcatalog_main.trackinggroup.close();
            });
        });
    };
    this.trackinggroup.addClose('Cancel');

    //
    // Setup the panel to update the name of a category/media/location/year
    //
    this.changename = new M.panel('Change Name',
        'ciniki_artcatalog_main', 'changename',
        'mc', 'medium', 'sectioned', 'ciniki.artcatalog.main.changename');
    this.changename.data = {};
    this.changename.sections = {
        'info':{'label':'Place', 'type':'simpleform', 
            'fields':{
                'new_value':{'label':'Name', 'type':'text'},
            }},
        '_buttons':{'label':'', 'buttons':{
            'update':{'label':'Update', 'fn':'M.ciniki_artcatalog_main.changename.save();'},
            }},
        };
    this.changename.fieldValue = function(s, i, d) { 
        return this.data[i]; 
    }
    this.changename.open = function(cb,title,name) {
        this.title = title;
        this.data = {
            'new_value':unescape(name),
            }
        this.refresh();
        this.show(cb);
    }
    this.changename.save = function() {
        var c = this.serializeForm('no');
        if( c != '' ) {
            if( this.title == 'Material' ) {
                c += '&old_value=' + M.eU(this.data.new_value);
                M.api.postJSONCb('ciniki.artcatalog.tagUpdate', {'tnid':M.curTenantID, 'tag_type':100}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    } else {
                        M.ciniki_artcatalog_main.changename.close();
                    }
                });

            } else {
                c += '&old_value=' + M.eU(this.data.new_value);
                M.api.postJSONCb('ciniki.artcatalog.fieldUpdate', {'tnid':M.curTenantID, 'field':this.title.toLowerCase()}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    } else {
                        M.ciniki_artcatalog_main.changename.close();
                    }
                });
            }
        } else {
            this.close();
        }
    }
    this.changename.addClose('Cancel');

    //
    // Setup the panel to list the collection of a category/media/location/year
    //
    this.list = new M.panel('Catalog',
        'ciniki_artcatalog_main', 'list',
        'mc', 'medium', 'sectioned', 'ciniki.artcatalog.main.list');
    this.list.data = {};
    this.list.current_section = '';
    this.list.current_name = '';
    this.list.sections = {};    // Sections are set in showPieces function
    this.list.downloadFn = '';
    this.list.next_list_name = '';
    this.list.prev_list_name = '';
// NOT SURE WHY THIS REFERENDCES this.menu.cellValue
//    this.list.cellValue = this.menu.cellValue;
    this.list.rowFn = function(s, i, d) {
        return 'M.ciniki_artcatalog_main.item.open(\'M.ciniki_artcatalog_main.showList();\', \'' + d.id + '\',M.ciniki_artcatalog_main.list.data[unescape(\'' + escape(s) + '\')]);'; 
    };
    this.list.sectionData = function(s) { 
        return this.data[s];
    };
    this.list.listValue = function(s, i, d) { 
        return d['label'];
    };
    this.list.noData = function(s) { return 'Nothing found'; }
    this.list.prevButtonFn = function() {
        if( this.prev_list_name != '' ) {
            return 'M.ciniki_artcatalog_main.showList(null,null,\'' + escape(this.prev_list_name) + '\');';
        }
        return null;
    };
    this.list.nextButtonFn = function() {
        if( this.next_list_name != '-1' && this.next_list_name != '' ) {
            return 'M.ciniki_artcatalog_main.showList(null,null,\'' + escape(this.next_list_name) + '\');';
        }
        return null;
    };
    this.list.addButton('add', 'Add', 'M.ciniki_artcatalog_main.edit.open(\'M.ciniki_artcatalog_main.showList();\',0,M.ciniki_artcatalog_main.list.current_section,M.ciniki_artcatalog_main.list.current_name);');
    this.list.addButton('next', 'next');
    this.list.addClose('Back');
    this.list.addLeftButton('prev', 'Prev');

    //
    // Display information about a item of art
    //
    this.item = new M.panel('Art',
        'ciniki_artcatalog_main', 'item',
        'mc', 'medium mediumaside', 'sectioned', 'ciniki.artcatalog.main.item');
    this.item.next_item_id = 0;
    this.item.prev_item_id = 0;
    this.item.data = null;
    this.item.artcatalog_id = 0;
    this.item.sections = {
        '_image':{'label':'Image', 'aside':'yes', 'type':'imageform', 'fields':{
            'image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'history':'no'},
        }},
        'info':{'label':'Public Information', 'aside':'yes', 'list':{
            'type_text':{'label':'Type'},
            'name':{'label':'Title', 'type':'text'},
            'category':{'label':'Category'},
//              'date_completed':{'label':'Completed'},
            'size':{'label':'Size'},
            'status_text':{'label':'Status'},
            'price':{'label':'Price'},
//              'forsale':{'label':'For sale'},
            'website':{'label':'Website', 'type':'flags', 'join':'yes', 'flags':this.webFlags},
        }},
        'description':{'label':'Description', 'type':'htmlcontent'},
        'awards':{'label':'Awards', 'type':'htmlcontent'},
        'publications':{'label':'Publications', 'type':'htmlcontent'},
        'ainfo':{'label':'Private Information', 'list':{
            'catalog_number':{'label':'Number'},
            'completed':{'label':'Completed'},
            'media':{'label':'Media'},
            'location':{'label':'Location'},
            'materials':{'label':'Materials'},
//            'lists':{'label':'Lists'},
        }},
        'tracking':{'label':'Exhibited', 'visible':'yes', 'type':'simplegrid', 'num_cols':1,
            'headerValues':null,
            'cellClasses':['multiline', 'multiline'],
            'addTxt':'Add Exhibited',
            'addFn':'M.startApp(\'ciniki.artcatalog.tracking\',null,\'M.ciniki_artcatalog_main.item.open();\',\'mc\',{\'artcatalog_id\':M.ciniki_artcatalog_main.item.artcatalog_id,\'add\':\'yes\'});',
            },
        'inspiration':{'label':'Inspiration', 'type':'htmlcontent'},
        'notes':{'label':'Notes', 'type':'htmlcontent'},
        'images':{'label':'Additional Images', 'type':'simplethumbs'},
        '_images':{'label':'', 'type':'simplegrid', 'num_cols':1,
            'addTxt':'Add Additional Image',
            'addFn':'M.startApp(\'ciniki.artcatalog.images\',null,\'M.ciniki_artcatalog_main.item.open();\',\'mc\',{\'artcatalog_id\':M.ciniki_artcatalog_main.item.artcatalog_id,\'add\':\'yes\'});',
            },
        'oldproducts':{'label':'OLD Products', 'visible':'no', 'type':'simplegrid', 'num_cols':3,
            'headerValues':['Product', 'Inv', 'Price'],
            'cellClasses':['', '', ''],
//            'addTxt':'Add Product',
//            'addFn':'M.startApp(\'ciniki.artcatalog.products\',null,\'M.ciniki_artcatalog_main.item.open();\',\'mc\',{\'artcatalog_id\':M.ciniki_artcatalog_main.item.artcatalog_id,\'add\':\'yes\'});',
            },
        'products':{'label':'Products', 'type':'simplegrid', 'num_cols':3,
            'visible':function() { return M.modOn('ciniki.merchandise') ? 'yes' : 'no'; },
            'headerValues':['Product', 'Inv', 'Price'],
            'cellClasses':['', '', ''],
            'addTxt':'Add Product',
//            'addFn':'M.ciniki_artcatalog_main.product.open(\'M.ciniki_artcatalog_main.item.open();\',0,M.ciniki_artcatalog_main.item.artcatalog_id);',
            'addFn':'M.startApp(\'ciniki.merchandise.main\',null,\'M.ciniki_artcatalog_main.item.open();\',\'mc\',{\'product_id\':0, \'object\':\'ciniki.artcatalog.item\',\'object_id\':M.ciniki_artcatalog_main.item.artcatalog_id});',
            },
        'invoices':{'label':'Sold to', 'visible':'no', 'type':'simplegrid', 'num_cols':'2',
            'headerValues':null,
            'cellClasses':['multiline','multiline'],
            'addTxt':'Add Sale',
            'addFn':'M.startApp(\'ciniki.sapos.invoice\',null,\'M.ciniki_artcatalog_main.item.open();\',\'mc\',{\'object\':\'ciniki.artcatalog.item\',\'object_id\':M.ciniki_artcatalog_main.item.artcatalog_id});',
            },
        '_buttons':{'label':'', 'buttons':{
            'edit':{'label':'Edit', 'fn':'M.ciniki_artcatalog_main.edit.open(\'M.ciniki_artcatalog_main.item.open();\',M.ciniki_artcatalog_main.item.artcatalog_id);'},
            'pdf':{'label':'Download', 'fn':'M.ciniki_artcatalog_main.openDownload(\'M.ciniki_artcatalog_main.item.open();\',\'ciniki.artcatalog.get\',\'\',M.ciniki_artcatalog_main.item.artcatalog_id);'},
            'delete':{'label':'Delete', 'fn':'M.ciniki_artcatalog_main.item.remove();'},
        }},
        };
    this.item.sectionData = function(s) {
        if( s == 'description' || s == 'awards' || s == 'publications' || s == 'notes' ) {
            return this.data[s].replace(/\n/g, '<br/>');
        }
        if( s == 'info' || s == 'ainfo' ) { return this.sections[s].list; }
        return this.data[s];
        };
    this.item.listLabel = function(s, i, d) {
        switch (s) {
            case 'info': return d.label;
            case 'ainfo': return d.label;
        }
    };
    this.item.listValue = function(s, i, d) {
        if( i == 'completed' ) {
            var com = '';
            if( this.data['month'] > 0 ) {
                com += M.ciniki_artcatalog_main.monthOptions[this.data['month']] + ' ';
                if( this.data['day'] > 0 ) {
                    com += M.ciniki_artcatalog_main.dayOptions[this.data['day']] + ', ';
                }
            }
            return com + this.data['year'];
        }
        if( i == 'size' && (this.data.flags&0x10) > 0 ) {
            if( this.data['framed_size'] != '' ) {
                return this.data[i] + ' (framed: ' + this.data['framed_size'] + ')';
            } else {
                return this.data[i] + ' framed';
            }
        }
        if( i == 'forsale' && this.data['sold'] == 'yes' ) {    
            return this.data[i] + ', SOLD';
        }
        if( i == 'materials' ) {
            if( this.data[i] != null && this.data[i] != '' ) {
                return this.data[i].replace(/\,/g, ', ');
            }
            return '';
        }
        if( i == 'lists' ) {
            if( this.data[i] != null && this.data[i] != '' ) {
                return this.data[i].replace(/\,/g, ', ');
            }
            return '';
        }
        if( s == '_images' ) {
            return d.label;
        }
        return this.data[i];
    };
    this.item.fieldValue = function(s, i, d) {
        if( i == 'description' || i == 'inspiration' || i == 'awards' || s == 'publications' || i == 'notes' ) { 
            return this.data[i].replace(/\n/g, '<br/>');
        }
        return this.data[i];
        };
    this.item.cellValue = function(s, i, j, d) {
        if( s == 'tracking' && j == 0 ) {
            var exnum = '';
            if( d.place.external_number != null && d.place.external_number != '' ) {
                exnum = ' (' + d.place.external_number + ')';
            }
            var dates = '';
            if( d.place.start_date != null && d.place.start_date != '' ) {
                dates = d.place.start_date;
                if( d.place.end_date != null && d.place.end_date != '' ) {
                    dates += ' - ' + d.place.end_date;
                }
            }
            return '<span class="maintext">' + d.place.name + exnum + '</span><span class="subtext">' + dates + '</span>';
        }
        else if( s == 'oldproducts' ) {
            switch (j) {
                case 0: return d.product.name;
                case 1: return d.product.inventory;
                case 2: return d.product.price;
            }
        }
        else if( s == 'products' ) {
            switch (j) {
                case 0: return d.name;
                case 1: return d.inventory;
                case 2: return d.unit_amount_display;
            }
        }
        else if( s == 'invoices' ) {
            if( j == 0 ) {
                return '<span class="maintext">' + d.invoice.customer_name + '</span><span class="subtext">Invoice #' + d.invoice.invoice_number + ' - ' + d.invoice.invoice_date + '</span>';
            } else if( j == 1 ) {
                return '<span class="maintext">' + d.invoice.item_amount + '</span><span class="subtext">' + d.invoice.status_text + '</span>';
            }
        }
    };
    this.item.rowFn = function(s, i, d) {
        switch(s) {
            case 'tracking': return 'M.startApp(\'ciniki.artcatalog.tracking\',null,\'M.ciniki_artcatalog_main.item.open();\',\'mc\',{\'tracking_id\':' + d.place.id + '});';
            case 'oldproducts': return 'M.startApp(\'ciniki.artcatalog.products\',null,\'M.ciniki_artcatalog_main.item.open();\',\'mc\',{\'product_id\':' + d.product.id + '});';
            case 'products': return 'M.startApp(\'ciniki.merchandise.main\',null,\'M.ciniki_artcatalog_main.item.open();\',\'mc\',{\'product_id\':' + d.product_id + '});';
            case 'invoices': return 'M.startApp(\'ciniki.sapos.invoice\',null,\'M.ciniki_artcatalog_main.item.open();\',\'mc\',{\'invoice_id\':' + d.invoice.id + '});';
        }
    };
    this.item.noData = function(s) {
        return '';
    };
    this.item.prevButtonFn = function() {
        if( this.prev_item_id > 0 ) {
            return 'M.ciniki_artcatalog_main.item.open(null,\'' + this.prev_item_id + '\');';
        }
        return null;
    };
    this.item.nextButtonFn = function() {
        if( this.next_item_id > 0 ) {
            return 'M.ciniki_artcatalog_main.item.open(null,\'' + this.next_item_id + '\');';
        }
        return null;
    };
    this.item.thumbFn = function(s, i, d) {
        return 'M.startApp(\'ciniki.artcatalog.images\',null,\'M.ciniki_artcatalog_main.item.open();\',\'mc\',{\'artcatalog_image_id\':\'' + d.image.id + '\'});';
    };
    this.item.addDropImage = function(iid) {
        M.api.getJSONCb('ciniki.artcatalog.imageAdd', {'tnid':M.curTenantID, 'image_id':iid, 'artcatalog_id':M.ciniki_artcatalog_main.item.artcatalog_id}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            M.ciniki_artcatalog_main.item.addDropImageRefresh();
        });
    };
    this.item.addDropImageRefresh = function() {
        if( M.ciniki_artcatalog_main.item.artcatalog_id > 0 ) {
            M.api.getJSONCb('ciniki.artcatalog.get', {'tnid':M.curTenantID, 'artcatalog_id':M.ciniki_artcatalog_main.item.artcatalog_id, 'images':'yes'}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_artcatalog_main.item.data.images = rsp.item.images;
                M.ciniki_artcatalog_main.item.refreshSection('images');
            });
        }
    };
    this.item.open = function(cb, aid, list) {
        if( aid != null ) { this.artcatalog_id = aid; }
        if( list != null ) { this.list = list; }

        this.sections.invoices.visible = (M.curTenant.modules['ciniki.customers'] != null && M.curTenant.modules['ciniki.sapos'] != null)?'yes':'no';

        M.api.getJSONCb('ciniki.artcatalog.get', {'tnid':M.curTenantID, 'artcatalog_id':this.artcatalog_id, 'tracking':'yes', 'images':'yes', 'invoices':'yes', 'products':'yes'}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_artcatalog_main.item;
            p.reset();
            p.data = rsp.item;
            if( rsp.item.type == '1' ) {
                p.sections.ainfo.list.media.visible = 'yes';
                p.sections.info.list.size.visible = 'yes';
            } else if( rsp.item.type == '2' ) {
                p.sections.ainfo.list.media.visible = 'no';
                p.sections.info.list.size.visible = 'no';
            } else if( rsp.item.type == '3' ) {
                p.sections.ainfo.list.media.visible = 'no';
                p.sections.info.list.size.visible = 'no';
            }
            p.sections.description.visible=(rsp.item.description!=null&&rsp.item.description!='')?'yes':'no';
            p.sections.inspiration.visible=(rsp.item.inspiration!=null&&rsp.item.inspiration!='')?'yes':'no';
            p.sections.awards.visible=(rsp.item.awards!=null&&rsp.item.awards!='')?'yes':'no';
            p.sections.publications.visible=(rsp.item.publications!=null&&rsp.item.publications!='')?'yes':'no';
            p.sections.notes.visible=(rsp.item.notes!=null&&rsp.item.notes!='')?'yes':'no';
            if( p.data.materials != null && p.data.materials != '' ) {
                p.data.materials = p.data.materials.replace(/::/g, ', ');
            }
            if( p.data.lists != null && p.data.lists != '' ) {
                p.data.lists = p.data.lists.replace(/::/g, ', ');
            }

//              p.sections.tracking.visible=(M.curTenant.artcatalog != null && M.curTenant.artcatalog.settings['enable-tracking'] == 'yes' )?'yes':'no';

            // Setup next/prev buttons
            p.prev_item_id = 0;
            p.next_item_id = 0;
            if( p.list != null ) {
                for(i in p.list) {
                    if( p.next_item_id == -1 ) {
                        p.next_item_id = (p.list[i].item != null ? p.list[i].item.id : p.list[i].id);
                        break;
                    } else if( p.list[i].item != null && p.list[i].item.id == p.artcatalog_id ) {
                        // Flag to pickup next item
                        p.next_item_id = -1;
                    } else if( p.list[i].id == p.artcatalog_id ) {
                        // Flag to pickup next item
                        p.next_item_id = -1;
                    } else {
                        p.prev_item_id = (p.list[i].item != null ? p.list[i].item.id : p.list[i].id);
                    }
                }
            }
            p.downloadFn = 'M.ciniki_artcatalog_main.showDownload(\'M.ciniki_artcatalog_main.item.open();\',\'ciniki.artcatalog.get\',\'\',\'\',\'\',\'\');';
            p.refresh();
            p.show(cb);
        });
    };
    this.item.remove = function() {
        M.confirm('Are you sure you want to delete \'' + this.data.name + '\'?  All information, photos and exhibited information will be removed. There is no way to get the information back once deleted.',null,function() {
            M.api.getJSONCb('ciniki.artcatalog.delete', {'tnid':M.curTenantID, 'artcatalog_id':M.ciniki_artcatalog_main.item.artcatalog_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_artcatalog_main.item.close();
            });
        });
    };
    this.item.addButton('edit', 'Edit', 'M.ciniki_artcatalog_main.edit.open(\'M.ciniki_artcatalog_main.item.open();\',M.ciniki_artcatalog_main.item.artcatalog_id);');
    this.item.addButton('next', 'Next');
    this.item.addClose('Back');
    this.item.addLeftButton('prev', 'Prev');
    this.item.addLeftButton('website', 'Preview', 'M.showWebsite(\'/gallery/category/\'+M.ciniki_artcatalog_main.item.data.category+\'/\'+M.ciniki_artcatalog_main.item.data.permalink);');

    //
    // The panel to display the edit form
    //
    this.edit = new M.panel('Art',
        'ciniki_artcatalog_main', 'edit',
        'mc', 'medium mediumaside', 'sectioned', 'ciniki.artcatalog.main.edit');
    this.edit.aid = 0;
    this.edit.next_item_id = 0;
    this.edit.prev_item_id = 0;
    this.edit.form_id = 1;
    this.edit.data = null;
    this.edit.cb = null;
    this.edit.forms = {};
    this.edit.gstep = 1;
    this.edit.formtabs = {'label':'', 'field':'type', 'tabs':{
        'painting':{'label':'Painting', 'field_id':1},
        'photograph':{'label':'Photograph', 'field_id':2},
        'jewelry':{'label':'Jewelry', 'field_id':3},
        'sculpture':{'label':'Sculpture', 'field_id':4},
        'fibreart':{'label':'Fibre Art', 'field_id':5},
        'printmaking':{'label':'Print Making', 'field_id':6},
        'pottery':{'label':'Pottery', 'field_id':8},
        'graphicart':{'label':'Graphic Art', 'field_id':11},
        }};
    this.edit.forms.painting = {
        '_image':{'label':'Image', 'aside':'yes', 'type':'imageform', 'fields':{
            'image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no'},
            }},
        'info':{'label':'Catalog Information', 'aside':'yes', 'type':'simpleform', 'fields':{
            'name':{'label':'Title', 'type':'text'},
            'category':{'label':'Category', 'type':'text', 'livesearch':'yes', 'livesearchempty':'yes'},
            'size':{'label':'Size', 'type':'text', 'size':'small' },
            'flags_5':{'label':'Framed', 'type':'flagtoggle', 'bit':0x10, 'field':'flags', 'default':'off',
                'on_fields':['framed_size'],
                },
            'framed_size':{'label':'Framed Size', 'type':'text', 'size':'small',
                'active':'yes', 
                'visible':function() {
                    if( (M.ciniki_artcatalog_main.edit.data.flags&0x10) > 0 ) {
                        return 'yes';
                    } else {
                        return 'no';
                    }
                }, 
                },
            'status':{'label':'Status', 'type':'select', 'options':this.statusOptions},
            'price':{'label':'Price', 'type':'text', 'size':'small' },
            }},
        '_tabs':{'label':'', 'type':'paneltabs', 'selected':'details', 'tabs':{ 
            'details':{'label':'Details', 'fn':'M.ciniki_artcatalog_main.edit.switchTab(\'details\');'},
            'gallery':{'label':'Gallery', 'fn':'M.ciniki_artcatalog_main.edit.switchTab(\'gallery\');'},
            'tracking':{'label':'Exhibited', 'fn':'M.ciniki_artcatalog_main.edit.switchTab(\'tracking\');'},
            'products':{'label':'Products', 
                'visible':function() { return M.modOn('ciniki.merchandise') ? 'yes' : 'no'; },
                'fn':'M.ciniki_artcatalog_main.edit.switchTab(\'products\');',
                },
//            'sales':{'label':'Sales', 'fn':'M.ciniki_artcatalog_main.edit.switchTab(\'sales\');',
//                'visible':function() { return M.modOn('ciniki.sapos') ? 'yes' : 'no'; },
//                },
            'notes':{'label':'Notes', 'fn':'M.ciniki_artcatalog_main.edit.switchTab(\'notes\');'},
            }},
        'ainfo':{'label':'Additional Catalog Information', 'type':'simpleform', 
            'visible':function() {
                return M.ciniki_artcatalog_main.edit.sections._tabs.selected == 'details' ? 'yes' : 'hidden';
                },
            'fields':{
                'catalog_number':{'label':'Number', 'type':'text', 'size':'small' },
                'year':{'label':'Year', 'type':'text', 'size':'small', 'livesearch':'yes', 'livesearchempty':'yes'},
                'month':{'label':'Month', 'type':'select', 'options':this.monthOptions },
                'day':{'label':'Day', 'type':'select', 'options':this.dayOptions },
                'media':{'label':'Media', 'type':'text', 'size':'small', 'livesearch':'yes', 'livesearchempty':'yes'},
                'location':{'label':'Location', 'type':'text', 'livesearch':'yes', 'livesearchempty':'yes'},
            }},
        '_description':{'label':'Description', 'aside':'yes', 'type':'simpleform', 
            'visible':function() {
                return M.ciniki_artcatalog_main.edit.sections._tabs.selected == 'details' ? 'yes' : 'hidden';
                },
            'fields':{
                'description':{'label':'', 'type':'textarea', 'size':'medium', 'hidelabel':'yes'},
            }},
        '_inspiration':{'label':'Inspiration', 'type':'simpleform', 
            'visible':function() {
                return M.ciniki_artcatalog_main.edit.sections._tabs.selected == 'details' ? 'yes' : 'hidden';
                },
            'fields':{
                'inspiration':{'label':'', 'type':'textarea', 'size':'small', 'hidelabel':'yes'},
            }},
        '_awards':{'label':'Awards', 'type':'simpleform', 
            'visible':function() {
                return M.ciniki_artcatalog_main.edit.sections._tabs.selected == 'details' ? 'yes' : 'hidden';
                },
            'fields':{
                'awards':{'label':'', 'type':'textarea', 'size':'small', 'hidelabel':'yes'},
            }},
        '_publications':{'label':'Publications', 'type':'simpleform', 
            'visible':function() {
                return M.ciniki_artcatalog_main.edit.sections._tabs.selected == 'details' ? 'yes' : 'hidden';
                },
            'fields':{
                'publications':{'label':'', 'type':'textarea', 'size':'small', 'hidelabel':'yes'},
            }},
        'images':{'label':'Additional Images', 'type':'simplethumbs',
            'visible':function() {
                return M.ciniki_artcatalog_main.edit.sections._tabs.selected == 'gallery' ? 'yes' : 'hidden';
                },
            },
        '_images':{'label':'', 'type':'simplegrid', 'num_cols':1,
            'visible':function() {
                return M.ciniki_artcatalog_main.edit.sections._tabs.selected == 'gallery' ? 'yes' : 'hidden';
                },
            'addTxt':'Add Additional Image',
            'addFn':'M.ciniki_artcatalog_main.edit.save("M.startApp(\'ciniki.artcatalog.images\',null,\'M.ciniki_artcatalog_main.edit.open();\',\'mc\',{\'artcatalog_id\':M.ciniki_artcatalog_main.edit.artcatalog_id,\'add\':\'yes\'});");',
            },
        'tracking':{'label':'Exhibited', 'type':'simplegrid', 'num_cols':1,
            'visible':function() {
                return M.ciniki_artcatalog_main.edit.sections._tabs.selected == 'tracking' ? 'yes' : 'hidden';
                },
            'headerValues':null,
            'cellClasses':['multiline', 'multiline'],
            'addTxt':'Add Exhibited',
            'addFn':'M.ciniki_artcatalog_main.edit.save("M.startApp(\'ciniki.artcatalog.tracking\',null,\'M.ciniki_artcatalog_main.edit.open();\',\'mc\',{\'artcatalog_id\':M.ciniki_artcatalog_main.edit.artcatalog_id,\'add\':\'yes\'});");',
            },
        'oldproducts':{'label':'OLD Products', 'type':'simplegrid', 'num_cols':3,
            'visible':function() {
                return M.ciniki_artcatalog_main.edit.sections._tabs.selected == 'products' ? 'yes' : 'hidden';
                },
            'headerValues':['Product', 'Inv', 'Price'],
            'cellClasses':['', '', ''],
            },
        'products':{'label':'Products', 'type':'simplegrid', 'num_cols':3,
            'visible':function() { 
                return M.modOn('ciniki.merchandise') && M.ciniki_artcatalog_main.edit.sections._tabs.selected == 'products' ? 'yes' : 'hidden'; 
                },
            'headerValues':['Product', 'Inv', 'Price'],
            'cellClasses':['', '', ''],
            'addTxt':'Add Product',
            'addFn':'M.startApp(\'ciniki.merchandise.main\',null,\'M.ciniki_artcatalog_main.edit.open();\',\'mc\',{\'product_id\':0, \'object\':\'ciniki.artcatalog.item\',\'object_id\':M.ciniki_artcatalog_main.edit.artcatalog_id});',
            },
        'invoices':{'label':'Sold to', 'type':'simplegrid', 'num_cols':'2',
            'visible':function() { 
                return M.modOn('ciniki.merchandise') && M.ciniki_artcatalog_main.edit.sections._tabs.selected == 'sales' ? 'yes' : 'hidden'; 
                },
            'headerValues':null,
            'cellClasses':['multiline','multiline'],
            'noData':'No sales for this item',
            'addTxt':'Add Sale',
            'addFn':'M.startApp(\'ciniki.sapos.invoice\',null,\'M.ciniki_artcatalog_main.edit.open();\',\'mc\',{\'object\':\'ciniki.artcatalog.item\',\'object_id\':M.ciniki_artcatalog_main.edit.artcatalog_id});',
            },
        '_notes':{'label':'Notes', 'type':'simpleform', 
            'visible':function() {
                return M.ciniki_artcatalog_main.edit.sections._tabs.selected == 'notes' ? 'yes' : 'hidden';
                },
            'fields':{
                'notes':{'label':'', 'type':'textarea', 'size':'large', 'hidelabel':'yes'},
            }},
        '_website':{'label':'Website Information', 'type':'simpleform', 
            'visible':function() {
                return M.ciniki_artcatalog_main.edit.sections._tabs.selected == 'details' ? 'yes' : 'hidden';
                },
            'fields':{
                'webflags_1':{'label':'Visible', 'type':'flagtoggle', 'field':'webflags', 'bit':0x01, 'default':'on',
                    'on_fields':['webflags_5', 'webflags_16', 'webflags_13', 'webflags_14', 'webflags_12', 'webflags_9', 'webflags_10', 'webflags_11', 'webflags_15'],
                    },
                'webflags_5':{'label':'Category Highlight', 'type':'flagtoggle', 'field':'webflags', 'bit':0x10, 'default':'off',
                    'visible':function() {
                        if( (M.ciniki_artcatalog_main.edit.data.webflags&0x01) > 0 ) {
                            return 'yes';
                        } else {
                            return 'no';
                        }
                    }},
                'webflags_12':{'label':'Price', 'type':'flagtoggle', 'field':'webflags', 'bit':0x0800, 'default':'on', 
                    'visible':function() { return ((M.ciniki_artcatalog_main.edit.data.webflags&0x01)>0?'yes':'no'); }
                    },
                'webflags_16':{'label':'Year', 'type':'flagtoggle', 'field':'webflags', 'bit':0x8000, 'default':'off',
                    'visible':function() { return ((M.ciniki_artcatalog_main.edit.data.webflags&0x01)>0?'yes':'no'); }
                    },
                'webflags_13':{'label':'Media', 'type':'flagtoggle', 'field':'webflags', 'bit':0x1000, 'default':'on',
                    'visible':function() { return ((M.ciniki_artcatalog_main.edit.data.webflags&0x01)>0?'yes':'no'); }
                    },
                'webflags_9':{'label':'Description', 'type':'flagtoggle', 'field':'webflags', 'bit':0x0100, 'default':'on',
                    'visible':function() { return ((M.ciniki_artcatalog_main.edit.data.webflags&0x01)>0?'yes':'no'); }
                    },
                'webflags_10':{'label':'Inspiration', 'type':'flagtoggle', 'field':'webflags', 'bit':0x0200, 'default':'on',
                    'visible':function() { return ((M.ciniki_artcatalog_main.edit.data.webflags&0x01)>0?'yes':'no'); }
                    },
                'webflags_11':{'label':'Awards', 'type':'flagtoggle', 'field':'webflags', 'bit':0x0400, 'default':'on',
                    'visible':function() { return ((M.ciniki_artcatalog_main.edit.data.webflags&0x01)>0?'yes':'no'); }
                    },
                'webflags_15':{'label':'Publications', 'type':'flagtoggle', 'field':'webflags', 'bit':0x4000, 'default':'on',
                    'visible':function() { return ((M.ciniki_artcatalog_main.edit.data.webflags&0x01)>0?'yes':'no'); }
                    },
            }},
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Save', 'fn':'M.ciniki_artcatalog_main.edit.save();'},
            'pdf':{'label':'Download', 'fn':'M.ciniki_artcatalog_main.edit.save("M.ciniki_artcatalog_main.openDownload(\'M.ciniki_artcatalog_main.edit.open();\',\'ciniki.artcatalog.get\',\'\',M.ciniki_artcatalog_main.edit.artcatalog_id);");'},
            'delete':{'label':'Delete', 'fn':'M.ciniki_artcatalog_main.edit.remove();'},
            }},
    };
    this.edit.forms.photograph = {
        '_image':{'label':'Image', 'aside':'yes', 'type':'imageform', 'fields':{
            'image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no'},
            }},
        'info':{'label':'Catalog Information', 'aside':'yes', 'type':'simpleform', 'fields':{
            'name':{'label':'Title', 'type':'text'},
            'category':{'label':'Category', 'type':'text', 'livesearch':'yes', 'livesearchempty':'yes'},
            'size':{'label':'Size', 'type':'text', 'size':'small'},
            'status':{'label':'Status', 'type':'select', 'options':this.statusOptions},
            'price':{'label':'Price', 'type':'text', 'size':'small'},
            }},
        '_tabs':this.edit.forms.painting._tabs,
        'ainfo':{'label':'Additional Catalog Information', 'type':'simpleform', 
            'visible':function() {
                return M.ciniki_artcatalog_main.edit.sections._tabs.selected == 'details' ? 'yes' : 'hidden';
                },
            'fields':{
                'catalog_number':this.edit.forms.painting.ainfo.fields.catalog_number,
                'year':{'label':'Year', 'type':'text', 'size':'small', 'livesearch':'yes', 'livesearchempty':'yes', },
                'month':{'label':'Month', 'type':'select', 'options':this.monthOptions},
                'day':{'label':'Day', 'type':'select', 'options':this.dayOptions},
                'media':{'label':'Media', 'type':'text', 'size':'small', 'livesearch':'yes', 'livesearchempty':'yes'},
                'location':{'label':'Location', 'type':'text', 'livesearch':'yes', 'livesearchempty':'yes'},
            }},
        '_description':{'label':'Description', 'aside':'yes', 'type':'simpleform', 
            'visible':function() {
                return M.ciniki_artcatalog_main.edit.sections._tabs.selected == 'details' ? 'yes' : 'hidden';
                },
            'fields':{
                'description':{'label':'', 'type':'textarea', 'size':'medium', 'hidelabel':'yes'},
            }},
        '_inspiration':this.edit.forms.painting._inspiration,
        '_awards':this.edit.forms.painting._awards,
        '_publications':this.edit.forms.painting._publications,
        'images':this.edit.forms.painting.images,
        '_images':this.edit.forms.painting._images,
        'tracking':this.edit.forms.painting.tracking,
        'oldproducts':this.edit.forms.painting.oldproducts,
        'products':this.edit.forms.painting.products,
        'invoices':this.edit.forms.painting.invoices,
        '_notes':this.edit.forms.painting._notes,
        '_website':{'label':'Website Information', 'type':'simpleform', 
            'visible':function() {
                return M.ciniki_artcatalog_main.edit.sections._tabs.selected == 'details' ? 'yes' : 'hidden';
                },
            'fields':{
                'webflags_1':this.edit.forms.painting._website.fields.webflags_1,
                'webflags_5':this.edit.forms.painting._website.fields.webflags_5,
                'webflags_16':this.edit.forms.painting._website.fields.webflags_16,
                'webflags_13':this.edit.forms.painting._website.fields.webflags_13,
                'webflags_12':this.edit.forms.painting._website.fields.webflags_12,
                'webflags_9':this.edit.forms.painting._website.fields.webflags_9,
                'webflags_10':this.edit.forms.painting._website.fields.webflags_10,
                'webflags_11':this.edit.forms.painting._website.fields.webflags_11,
                'webflags_15':this.edit.forms.painting._website.fields.webflags_15,
            }},
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Save', 'fn':'M.ciniki_artcatalog_main.edit.save();'},
            'delete':{'label':'Delete', 'fn':'M.ciniki_artcatalog_main.edit.remove();'},
            }},
    };
    this.edit.forms.jewelry = {
        '_image':{'label':'Image', 'aside':'yes', 'type':'imageform', 'fields':{
            'image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no'},
            }},
        'info':{'label':'Catalog Information', 'aside':'yes', 'type':'simpleform', 'fields':{
            'name':{'label':'Title', 'type':'text'},
            'category':{'label':'Category', 'type':'text', 'livesearch':'yes', 'livesearchempty':'yes'},
            'status':{'label':'Status', 'type':'select', 'options':this.statusOptions},
            'price':{'label':'Price', 'type':'text', 'size':'small'},
            }},
        '_tabs':this.edit.forms.painting._tabs,
        'ainfo':{'label':'Additional Catalog Information', 'type':'simpleform', 
            'visible':function() {
                return M.ciniki_artcatalog_main.edit.sections._tabs.selected == 'details' ? 'yes' : 'hidden';
                },
            'fields':{
                'catalog_number':this.edit.forms.painting.ainfo.fields.catalog_number,
                'year':{'label':'Year', 'type':'text', 'size':'small', 'livesearch':'yes', 'livesearchempty':'yes'},
                'month':{'label':'Month', 'type':'select', 'options':this.monthOptions},
                'day':{'label':'Day', 'type':'select', 'options':this.dayOptions},
                'location':{'label':'Location', 'type':'text', 'livesearch':'yes', 'livesearchempty':'yes'},
            }},
        '_materials':{'label':'Materials', 'active':'yes', 'type':'simpleform', 
            'visible':function() {
                return M.ciniki_artcatalog_main.edit.sections._tabs.selected == 'details' ? 'yes' : 'hidden';
                },
            'fields':{
                'materials':{'label':'', 'active':'yes', 'hidelabel':'yes', 'type':'tags', 'tags':[], 'hint':'New Material'},
            }},
        '_description':{'label':'Description', 'aside':'yes', 'type':'simpleform', 
            'visible':function() {
                return M.ciniki_artcatalog_main.edit.sections._tabs.selected == 'details' ? 'yes' : 'hidden';
                },
            'fields':{
                'description':{'label':'', 'type':'textarea', 'size':'medium', 'hidelabel':'yes'},
            }},
        '_description':this.edit.forms.painting._description,
        '_inspiration':this.edit.forms.painting._inspiration,
        '_awards':this.edit.forms.painting._awards,
        '_publications':this.edit.forms.painting._publications,
        'images':this.edit.forms.painting.images,
        '_images':this.edit.forms.painting._images,
        'tracking':this.edit.forms.painting.tracking,
        'oldproducts':this.edit.forms.painting.oldproducts,
        'products':this.edit.forms.painting.products,
        'invoices':this.edit.forms.painting.invoices,
        '_notes':this.edit.forms.painting._notes,
        '_website':{'label':'Website Information', 'type':'simpleform', 
            'visible':function() {
                return M.ciniki_artcatalog_main.edit.sections._tabs.selected == 'details' ? 'yes' : 'hidden';
                },
            'fields':{
                'webflags_1':this.edit.forms.painting._website.fields.webflags_1,
                'webflags_5':this.edit.forms.painting._website.fields.webflags_5,
                'webflags_12':this.edit.forms.painting._website.fields.webflags_12,
                'webflags_9':this.edit.forms.painting._website.fields.webflags_9,
                'webflags_16':this.edit.forms.painting._website.fields.webflags_16,
                'webflags_14':{'label':'Materials', 'type':'flagtoggle', 'field':'webflags', 'bit':0x2000, 'default':'on',
                    'visible':function() { return ((M.ciniki_artcatalog_main.edit.data.webflags&0x01)>0?'yes':'no'); }
                    },
                'webflags_10':this.edit.forms.painting._website.fields.webflags_10,
                'webflags_11':this.edit.forms.painting._website.fields.webflags_11,
                'webflags_15':this.edit.forms.painting._website.fields.webflags_15,
            }},
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Save', 'fn':'M.ciniki_artcatalog_main.edit.save();'},
            'delete':{'label':'Delete', 'fn':'M.ciniki_artcatalog_main.edit.remove();'},
            }},
    };
    this.edit.forms.sculpture = {
        '_image':{'label':'Image', 'aside':'yes', 'type':'imageform', 'fields':{
            'image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no'},
            }},
        'info':{'label':'Catalog Information', 'aside':'yes', 'type':'simpleform', 'fields':{
            'name':{'label':'Title', 'type':'text'},
            'category':{'label':'Category', 'type':'text', 'livesearch':'yes', 'livesearchempty':'yes'},
            'size':{'label':'Size', 'type':'text', 'size':'small'},
            'status':{'label':'Status', 'type':'select', 'options':this.statusOptions},
            'price':{'label':'Price', 'type':'text', 'size':'small'},
            }},
        '_tabs':this.edit.forms.painting._tabs,
        'ainfo':{'label':'Additional Catalog Information', 'type':'simpleform', 
            'visible':function() {
                return M.ciniki_artcatalog_main.edit.sections._tabs.selected == 'details' ? 'yes' : 'hidden';
                },
            'fields':{
                'catalog_number':this.edit.forms.painting.ainfo.fields.catalog_number,
                'year':{'label':'Year', 'type':'text', 'size':'small', 'livesearch':'yes', 'livesearchempty':'yes'},
                'month':{'label':'Month', 'type':'select', 'options':this.monthOptions},
                'day':{'label':'Day', 'type':'select', 'options':this.dayOptions},
                'media':{'label':'Media', 'type':'text', 'livesearch':'yes', 'livesearchempty':'yes'},
                'location':{'label':'Location', 'type':'text', 'livesearch':'yes', 'livesearchempty':'yes'},
            }},
        '_materials':this.edit.forms.jewelry._materials,
        '_description':{'label':'Description', 'aside':'yes', 'type':'simpleform', 
            'visible':function() {
                return M.ciniki_artcatalog_main.edit.sections._tabs.selected == 'details' ? 'yes' : 'hidden';
                },
            'fields':{
                'description':{'label':'', 'type':'textarea', 'size':'medium', 'hidelabel':'yes'},
            }},
        '_inspiration':this.edit.forms.painting._inspiration,
        '_awards':this.edit.forms.painting._awards,
        '_publications':this.edit.forms.painting._publications,
        'images':this.edit.forms.painting.images,
        '_images':this.edit.forms.painting._images,
        'tracking':this.edit.forms.painting.tracking,
        'oldproducts':this.edit.forms.painting.oldproducts,
        'products':this.edit.forms.painting.products,
        'invoices':this.edit.forms.painting.invoices,
        '_notes':this.edit.forms.painting._notes,
        '_website':{'label':'Website Information', 'type':'simpleform', 
            'visible':function() {
                return M.ciniki_artcatalog_main.edit.sections._tabs.selected == 'details' ? 'yes' : 'hidden';
                },
            'fields':{
                'webflags_1':this.edit.forms.painting._website.fields.webflags_1,
                'webflags_5':this.edit.forms.painting._website.fields.webflags_5,
                'webflags_16':this.edit.forms.painting._website.fields.webflags_16,
                'webflags_12':this.edit.forms.painting._website.fields.webflags_12,
                'webflags_9':this.edit.forms.painting._website.fields.webflags_9,
                'webflags_14':this.edit.forms.jewelry._website.fields.webflags_14,
                'webflags_10':this.edit.forms.painting._website.fields.webflags_10,
                'webflags_11':this.edit.forms.painting._website.fields.webflags_11,
                'webflags_15':this.edit.forms.painting._website.fields.webflags_15,
            }},
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Save', 'fn':'M.ciniki_artcatalog_main.edit.save();'},
            'delete':{'label':'Delete', 'fn':'M.ciniki_artcatalog_main.edit.remove();'},
            }},
    };
    this.edit.forms.fibreart = {
        '_image':{'label':'Image', 'aside':'yes', 'type':'imageform', 'fields':{
            'image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no'},
            }},
        'info':{'label':'Catalog Information', 'aside':'yes', 'type':'simpleform', 'fields':{
            'name':{'label':'Title', 'type':'text'},
            'category':{'label':'Category', 'type':'text', 'livesearch':'yes', 'livesearchempty':'yes'},
            'size':{'label':'Size', 'type':'text', 'size':'small'},
            'status':{'label':'Status', 'type':'select', 'options':this.statusOptions},
            'price':{'label':'Price', 'type':'text', 'size':'small'},
            }},
        '_tabs':this.edit.forms.painting._tabs,
        'ainfo':{'label':'Additional Catalog Information', 'type':'simpleform', 
            'visible':function() {
                return M.ciniki_artcatalog_main.edit.sections._tabs.selected == 'details' ? 'yes' : 'hidden';
                },
            'fields':{
                'catalog_number':this.edit.forms.painting.ainfo.fields.catalog_number,
                'year':{'label':'Year', 'type':'text', 'size':'small', 'livesearch':'yes', 'livesearchempty':'yes'},
                'month':{'label':'Month', 'type':'select', 'options':this.monthOptions},
                'day':{'label':'Day', 'type':'select', 'options':this.dayOptions},
                'location':{'label':'Location', 'type':'text', 'livesearch':'yes', 'livesearchempty':'yes'},
            }},
        '_materials':this.edit.forms.jewelry._materials,
        '_description':{'label':'Description', 'aside':'yes', 'type':'simpleform', 
            'visible':function() {
                return M.ciniki_artcatalog_main.edit.sections._tabs.selected == 'details' ? 'yes' : 'hidden';
                },
            'fields':{
                'description':{'label':'', 'type':'textarea', 'size':'medium', 'hidelabel':'yes'},
            }},
        '_inspiration':this.edit.forms.painting._inspiration,
        '_awards':this.edit.forms.painting._awards,
        '_publications':this.edit.forms.painting._publications,
        'images':this.edit.forms.painting.images,
        '_images':this.edit.forms.painting._images,
        'tracking':this.edit.forms.painting.tracking,
        'oldproducts':this.edit.forms.painting.oldproducts,
        'products':this.edit.forms.painting.products,
        'invoices':this.edit.forms.painting.invoices,
        '_notes':this.edit.forms.painting._notes,
        '_website':{'label':'Website Information', 'type':'simpleform', 
            'visible':function() {
                return M.ciniki_artcatalog_main.edit.sections._tabs.selected == 'details' ? 'yes' : 'hidden';
                },
            'fields':{
                'webflags_1':this.edit.forms.painting._website.fields.webflags_1,
                'webflags_5':this.edit.forms.painting._website.fields.webflags_5,
                'webflags_16':this.edit.forms.painting._website.fields.webflags_16,
                'webflags_12':this.edit.forms.painting._website.fields.webflags_12,
                'webflags_9':this.edit.forms.painting._website.fields.webflags_9,
                'webflags_14':this.edit.forms.jewelry._website.fields.webflags_14,
                'webflags_10':this.edit.forms.painting._website.fields.webflags_10,
                'webflags_11':this.edit.forms.painting._website.fields.webflags_11,
                'webflags_15':this.edit.forms.painting._website.fields.webflags_15,
            }},
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Save', 'fn':'M.ciniki_artcatalog_main.edit.save();'},
            'delete':{'label':'Delete', 'fn':'M.ciniki_artcatalog_main.edit.remove();'},
            }},
    };
    this.edit.forms.printmaking = {
        '_image':this.edit.forms.painting._image,
        'info':this.edit.forms.painting.info,
        '_tabs':this.edit.forms.painting._tabs,
        'ainfo':this.edit.forms.painting.ainfo,
        '_materials':this.edit.forms.jewelry._materials,
        '_description':this.edit.forms.painting._description,
        '_inspiration':this.edit.forms.painting._inspiration,
        '_awards':this.edit.forms.painting._awards,
        '_publications':this.edit.forms.painting._publications,
        'images':this.edit.forms.painting.images,
        '_images':this.edit.forms.painting._images,
        'tracking':this.edit.forms.painting.tracking,
        'oldproducts':this.edit.forms.painting.oldproducts,
        'products':this.edit.forms.painting.products,
        'invoices':this.edit.forms.painting.invoices,
        '_notes':this.edit.forms.painting._notes,
        '_website':{'label':'Website Information', 'type':'simpleform', 
            'visible':function() {
                return M.ciniki_artcatalog_main.edit.sections._tabs.selected == 'details' ? 'yes' : 'hidden';
                },
            'fields':{
                'webflags_1':this.edit.forms.painting._website.fields.webflags_1,
                'webflags_5':this.edit.forms.painting._website.fields.webflags_5,
                'webflags_16':this.edit.forms.painting._website.fields.webflags_16,
                'webflags_12':this.edit.forms.painting._website.fields.webflags_12,
                'webflags_9':this.edit.forms.painting._website.fields.webflags_9,
                'webflags_14':this.edit.forms.jewelry._website.fields.webflags_14,
                'webflags_10':this.edit.forms.painting._website.fields.webflags_10,
                'webflags_11':this.edit.forms.painting._website.fields.webflags_11,
                'webflags_15':this.edit.forms.painting._website.fields.webflags_15,
            }},
        '_buttons':this.edit.forms.painting._buttons,
    };
    this.edit.forms.pottery = {
        '_image':{'label':'Image', 'aside':'yes', 'type':'imageform', 'fields':{
            'image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no'},
            }},
        'info':{'label':'Catalog Information', 'aside':'yes', 'type':'simpleform', 'fields':{
            'name':{'label':'Title', 'type':'text'},
            'category':{'label':'Category', 'type':'text', 'livesearch':'yes', 'livesearchempty':'yes'},
            'size':{'label':'Size', 'type':'text', 'size':'small'},
            'status':{'label':'Status', 'type':'select', 'options':this.statusOptions},
            'price':{'label':'Price', 'type':'text', 'size':'small'},
            }},
        '_tabs':this.edit.forms.painting._tabs,
        'ainfo':{'label':'Additional Catalog Information', 'type':'simpleform', 
            'visible':function() {
                return M.ciniki_artcatalog_main.edit.sections._tabs.selected == 'details' ? 'yes' : 'hidden';
                },
            'fields':{
                'catalog_number':this.edit.forms.painting.ainfo.fields.catalog_number,
                'year':{'label':'Year', 'type':'text', 'size':'small', 'livesearch':'yes', 'livesearchempty':'yes'},
                'month':{'label':'Month', 'type':'select', 'options':this.monthOptions},
                'day':{'label':'Day', 'type':'select', 'options':this.dayOptions},
                'location':{'label':'Location', 'type':'text', 'livesearch':'yes', 'livesearchempty':'yes'},
            }},
        '_description':{'label':'Description', 'aside':'yes', 'type':'simpleform', 
            'visible':function() {
                return M.ciniki_artcatalog_main.edit.sections._tabs.selected == 'details' ? 'yes' : 'hidden';
                },
            'fields':{
                'description':{'label':'', 'type':'textarea', 'size':'medium', 'hidelabel':'yes'},
            }},
        '_inspiration':this.edit.forms.painting._inspiration,
        '_awards':this.edit.forms.painting._awards,
        '_publications':this.edit.forms.painting._publications,
        'images':this.edit.forms.painting.images,
        '_images':this.edit.forms.painting._images,
        'tracking':this.edit.forms.painting.tracking,
        'oldproducts':this.edit.forms.painting.oldproducts,
        'products':this.edit.forms.painting.products,
        'invoices':this.edit.forms.painting.invoices,
        '_notes':this.edit.forms.painting._notes,
        '_website':{'label':'Website Information', 'type':'simpleform', 
            'visible':function() {
                return M.ciniki_artcatalog_main.edit.sections._tabs.selected == 'details' ? 'yes' : 'hidden';
                },
            'fields':{
                'webflags_1':this.edit.forms.painting._website.fields.webflags_1,
                'webflags_5':this.edit.forms.painting._website.fields.webflags_5,
                'webflags_16':this.edit.forms.painting._website.fields.webflags_16,
                'webflags_12':this.edit.forms.painting._website.fields.webflags_12,
                'webflags_9':this.edit.forms.painting._website.fields.webflags_9,
                'webflags_14':this.edit.forms.jewelry._website.fields.webflags_14,
                'webflags_10':this.edit.forms.painting._website.fields.webflags_10,
                'webflags_11':this.edit.forms.painting._website.fields.webflags_11,
                'webflags_15':this.edit.forms.painting._website.fields.webflags_15,
            }},
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Save', 'fn':'M.ciniki_artcatalog_main.edit.save();'},
            'pdf':{'label':'Download', 'fn':'M.ciniki_artcatalog_main.edit.save("M.ciniki_artcatalog_main.openDownload(\'M.ciniki_artcatalog_main.edit.open();\',\'ciniki.artcatalog.get\',\'\',M.ciniki_artcatalog_main.item.artcatalog_id);");'},
            'delete':{'label':'Delete', 'fn':'M.ciniki_artcatalog_main.edit.remove();'},
            }},
    };
    this.edit.forms.graphicart = {
        '_image':this.edit.forms.painting._image,
        'info':this.edit.forms.painting.info,
        '_tabs':this.edit.forms.painting._tabs,
        'ainfo':this.edit.forms.painting.ainfo,
        '_description':this.edit.forms.painting._description,
        '_inspiration':this.edit.forms.painting._inspiration,
        '_awards':this.edit.forms.painting._awards,
        '_publications':this.edit.forms.painting._publications,
        'images':this.edit.forms.painting.images,
        '_images':this.edit.forms.painting._images,
        'tracking':this.edit.forms.painting.tracking,
        'oldproducts':this.edit.forms.painting.oldproducts,
        'products':this.edit.forms.painting.products,
        'invoices':this.edit.forms.painting.invoices,
        '_notes':this.edit.forms.painting._notes,
        '_website':{'label':'Website Information', 'type':'simpleform', 
            'visible':function() {
                return M.ciniki_artcatalog_main.edit.sections._tabs.selected == 'details' ? 'yes' : 'hidden';
                },
            'fields':{
                'webflags_1':this.edit.forms.painting._website.fields.webflags_1,
                'webflags_5':this.edit.forms.painting._website.fields.webflags_5,
                'webflags_16':this.edit.forms.painting._website.fields.webflags_16,
                'webflags_12':this.edit.forms.painting._website.fields.webflags_12,
                'webflags_9':this.edit.forms.painting._website.fields.webflags_9,
                'webflags_14':this.edit.forms.jewelry._website.fields.webflags_14,
                'webflags_10':this.edit.forms.painting._website.fields.webflags_10,
                'webflags_11':this.edit.forms.painting._website.fields.webflags_11,
                'webflags_15':this.edit.forms.painting._website.fields.webflags_15,
            }},
        '_buttons':this.edit.forms.painting._buttons,
    };
    this.edit.form_id = 1;
    this.edit.sections = this.edit.forms.painting;
    this.edit.fieldValue = function(s, i, d) { 
        return this.data[i]; 
    }
    this.edit.sectionData = function(s) {
        return this.data[s];
    };
    this.edit.switchTab = function(t) {
        this.sections._tabs.selected = t;
        this.refreshSections(['_tabs']);
        this.showHideSections(['ainfo', '_materials', '_description', '_inspiration', '_awards', '_publications', '_notes', '_website', 'images', '_images', 'tracking', 'oldproducts', 'products', 'invoices']);
    }
    this.edit.liveSearchCb = function(s, i, value) {
        if( i == 'category' || i == 'media' || i == 'location' || i == 'year' ) {
            var rsp = M.api.getJSONBgCb('ciniki.artcatalog.searchField', {'tnid':M.curTenantID, 'field':i, 'start_needle':value, 'limit':15},
                function(rsp) {
                    M.ciniki_artcatalog_main.edit.liveSearchShow(s, i, M.gE(M.ciniki_artcatalog_main.edit.panelUID + '_' + i), rsp.results);
                });
        }
    };
    this.edit.liveSearchResultValue = function(s, f, i, j, d) {
        if( (f == 'category' || f == 'media' || f == 'location' || f == 'year' ) && d.result != null ) { return d.result.name; }
        return '';
    };
    this.edit.liveSearchResultRowFn = function(s, f, i, j, d) { 
        if( (f == 'category' || f == 'media' || f == 'location' || f == 'year' )
            && d.result != null ) {
            return 'M.ciniki_artcatalog_main.edit.updateField(\'' + s + '\',\'' + f + '\',\'' + escape(d.result.name) + '\');';
        }
    };
    this.edit.cellValue = function(s, i, j, d) {
        if( s == 'tracking' && j == 0 ) {
            var exnum = '';
            if( d.place.external_number != null && d.place.external_number != '' ) {
                exnum = ' (' + d.place.external_number + ')';
            }
            var dates = '';
            if( d.place.start_date != null && d.place.start_date != '' ) {
                dates = d.place.start_date;
                if( d.place.end_date != null && d.place.end_date != '' ) {
                    dates += ' - ' + d.place.end_date;
                }
            }
            return '<span class="maintext">' + d.place.name + exnum + '</span><span class="subtext">' + dates + '</span>';
        }
        else if( s == 'oldproducts' ) {
            switch (j) {
                case 0: return d.product.name;
                case 1: return d.product.inventory;
                case 2: return d.product.price;
            }
        }
        else if( s == 'products' ) {
            switch (j) {
                case 0: return d.name;
                case 1: return d.inventory;
                case 2: return d.unit_amount_display;
            }
        }
        else if( s == 'invoices' ) {
            if( j == 0 ) {
                return '<span class="maintext">' + d.invoice.customer_name + '</span><span class="subtext">Invoice #' + d.invoice.invoice_number + ' - ' + d.invoice.invoice_date + '</span>';
            } else if( j == 1 ) {
                return '<span class="maintext">' + d.invoice.item_amount + '</span><span class="subtext">' + d.invoice.status_text + '</span>';
            }
        }
    };
    this.edit.thumbFn = function(s, i, d) {
        return 'M.startApp(\'ciniki.artcatalog.images\',null,\'M.ciniki_artcatalog_main.edit.open();\',\'mc\',{\'artcatalog_image_id\':\'' + d.image.id + '\'});';
    };
    this.edit.rowFn = function(s, i, d) {
        switch(s) {
            case 'tracking': return 'M.ciniki_artcatalog_main.edit.save("M.startApp(\'ciniki.artcatalog.tracking\',null,\'M.ciniki_artcatalog_main.edit.open();\',\'mc\',{\'tracking_id\':' + d.place.id + '});");';
            case 'oldproducts': return 'M.startApp(\'ciniki.artcatalog.products\',null,\'M.ciniki_artcatalog_main.edit.open();\',\'mc\',{\'product_id\':' + d.product.id + '});';
            case 'products': return 'M.ciniki_artcatalog_main.edit.save("M.startApp(\'ciniki.merchandise.main\',null,\'M.ciniki_artcatalog_main.edit.open();\',\'mc\',{\'product_id\':' + d.product_id + '});");';
            case 'invoices': return 'M.ciniki_artcatalog_main.edit.save("M.startApp(\'ciniki.sapos.invoice\',null,\'M.ciniki_artcatalog_main.edit.open();\',\'mc\',{\'invoice_id\':' + d.invoice.id + '});");';
        }
    };
    this.edit.updateField = function(s, fid, result) {
        M.gE(this.panelUID + '_' + fid).value = unescape(result);
        this.removeLiveSearch(s, fid);
    };
    this.edit.fieldHistoryArgs = function(s, i) {
        return {'method':'ciniki.artcatalog.getHistory', 
            'args':{'tnid':M.curTenantID, 'artcatalog_id':this.artcatalog_id, 'field':i}};
    }
    this.edit.addDropImage = function(iid) {
        M.ciniki_artcatalog_main.edit.setFieldValue('image_id', iid);
        return true;
    };
    this.edit.deleteImage = function(fid) {
        this.setFieldValue(fid, 0);
        return true;
    };
    this.edit.open = function(cb, aid, section, name, list) {
        if( aid != null ) { this.artcatalog_id = aid; }
        if( list != null ) { this.list = list; }
        if( this.artcatalog_id > 0 ) {
            M.api.getJSONCb('ciniki.artcatalog.get', {'tnid':M.curTenantID, 'artcatalog_id':this.artcatalog_id, 
                'tracking':'yes', 'images':'yes', 'invoices':'yes', 'products':'yes', 'tags':'yes'}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    var p = M.ciniki_artcatalog_main.edit;
                    p.formtab = null;
                    p.formtab_field_id = null;
                    if( p.sections != null && p.sections._materials != null ) { p.sections._materials.fields.materials.tags = []; }
                    var material_tags = [];
                    for(i in rsp.tags.materials) {
                        material_tags.push(rsp.tags.materials[i].tag.name);
                    }
                    for(i in p.forms) {
                        if( p.forms[i]._materials != null ) {
                            p.forms[i]._materials.fields.materials.tags = material_tags;
                        }
                    }
                    p.data = rsp.item;
                    // Setup next/prev buttons
                    p.prev_item_id = 0;
                    p.next_item_id = 0;
                    if( p.list != null ) {
                        for(i in p.list) {
                            if( p.next_item_id == -1 ) {
                                p.next_item_id = (p.list[i].item != null ? p.list[i].item.id : p.list[i].id);
                                break;
                            } else if( p.list[i].item != null && p.list[i].item.id == p.aid ) {
                                // Flag to pickup next item
                                p.next_item_id = -1;
                            } else if( p.list[i].id == p.artcatalog_id ) {
                                // Flag to pickup next item
                                p.next_item_id = -1;
                            } else {
                                p.prev_item_id = (p.list[i].item != null ? p.list[i].item.id : p.list[i].id);
                            }
                        }
                    }
                    p.refresh();
                    p.show(cb);
                });
        } else {
            this.reset();
            M.api.getJSONCb('ciniki.artcatalog.get', {'tnid':M.curTenantID, 'artcatalog_id':this.artcatalog_id, 
                'tracking':'yes', 'images':'yes', 'invoices':'yes', 'products':'yes', 'tags':'yes'}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    var p = M.ciniki_artcatalog_main.edit;
                    p.formtab = null;
                    p.formtab_field_id = null;
                    if( p.sections != null && p.sections._materials != null ) { p.sections._materials.fields.materials.tags = []; }
                    var material_tags = [];
                    for(i in rsp.tags.materials) {
                        material_tags.push(rsp.tags.materials[i].tag.name);
                    }
                    for(i in p.forms) {
                        if( p.forms[i]._materials != null ) {
                            p.forms[i]._materials.fields.materials.tags = material_tags;
                        }
                    }
                    p.data = rsp.item;
                    if( section != null && (section == 'category' || section == 'categories') && name != null && name != '' ) {
                        p.data.category = decodeURIComponent(name);
                    } else if( section != null && section == 'media' && name != null && name != '' ) {
                        p.data.media = decodeURIComponent(name);
                    } else if( section != null && (section == 'location' || section == 'locations') && name != null && name != '' ) {
                        p.data.location = decodeURIComponent(name);
                    } else if( section != null && (section == 'year' || section == 'years') && name != null && name != '' ) {
                        p.data.year = decodeURIComponent(name);
                    } else if( section != null && section == 'material' && name != null && name != '' ) {
                        p.data['materials'] = name;
                    }
                    if( M.ciniki_artcatalog_main.menu.sections.types.visible == 'yes' && M.ciniki_artcatalog_main.menu.sections.types.selected != 'all' ) {
                        p.formtab = M.ciniki_artcatalog_main.menu.sections.types.selected;
                    } else {
                        var max = 0;
                        for(i in M.ciniki_artcatalog_main.menu.data.types) {
                            if( parseInt(M.ciniki_artcatalog_main.menu.data.types[i].count) > max ) {
                                p.formtab = M.ciniki_artcatalog_main.menu.data.types[i].type;
                                max = parseInt(M.ciniki_artcatalog_main.menu.data.types[i].count);
                            }
                        }
                    }
                    p.prev_item_id = 0;
                    p.next_item_id = 0;
                    p.refresh();
                    p.show(cb);
                });
        }
    };
    this.edit.save = function(cb) {
        if( cb == null ) { cb = 'M.ciniki_artcatalog_main.edit.close();'; }
        if( this.artcatalog_id > 0 ) {
            var c = this.serializeFormData('no');
            if( c != '' ) {
                var nv = this.formFieldValue(this.sections.info.fields.name, 'name');
                if( nv != this.fieldValue('info', 'name') && nv == '' ) {
                    M.alert('You must specifiy a title');
                    return false;
                }
                M.api.postJSONFormData('ciniki.artcatalog.update', {'tnid':M.curTenantID, 'artcatalog_id':this.artcatalog_id}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    } else {
                        eval(cb);
                    }
                });
            } 
        } else {
            var c = this.serializeFormData('yes');
            var nv = this.formFieldValue(this.sections.info.fields.name, 'name');
            if( nv == '' ) {
                M.alert('You must specifiy a title');
                return false;
            }
            M.api.postJSONFormData('ciniki.artcatalog.add', {'tnid':M.curTenantID}, c, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                } 
                M.ciniki_artcatalog_main.edit.artcatalog_id = rsp.id;
                eval(cb);
            });
        }
    };
    this.edit.remove = function() {
        M.confirm('Are you sure you want to delete \'' + this.data.name + '\'?  All information, photos and exhibited information will be removed. There is no way to get the information back once deleted.',null,function() {
            M.api.getJSONCb('ciniki.artcatalog.delete', {'tnid':M.curTenantID, 'artcatalog_id':M.ciniki_artcatalog_main.edit.artcatalog_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_artcatalog_main.edit.close();
            });
        });
    };
    this.edit.prevButtonFn = function() {
        if( this.prev_item_id > 0 ) {
            return 'M.ciniki_artcatalog_main.edit.save("M.ciniki_artcatalog_main.edit.open(null,\'' + this.prev_item_id + '\');");';
        }
        return null;
    };
    this.edit.nextButtonFn = function() {
        if( this.next_item_id > 0 ) {
            return 'M.ciniki_artcatalog_main.edit.save("M.ciniki_artcatalog_main.edit.open(null,\'' + this.next_item_id + '\');");';
        }
        return null;
    };
    this.edit.addButton('save', 'Save', 'M.ciniki_artcatalog_main.edit.save();');
    this.edit.addClose('Cancel');
    this.edit.addButton('next', 'Next');
    this.edit.addLeftButton('prev', 'Prev');

    //
    // The download panel
    //
    this.downloadpdf = new M.panel('Download',
        'ciniki_artcatalog_main', 'downloadpdf',
        'mc', 'medium', 'sectioned', 'ciniki.artcatalog.main.downloadpdf');
    this.downloadpdf.method = '';
    this.downloadpdf.data = {'layout':'list'};
    this.downloadpdf.list_section = null;
    this.downloadpdf.list_name = null;
    this.downloadpdf.list_type = null;
    this.downloadpdf.sortbylist = {
        'category':'Category', 
        'media':'Media', 
        'location':'Location', 
        'year':'Year', 
        'tracking':'Exhibited', 
        'catalognumber':'Catalog Number',
        };
    this.downloadpdf.forms = {};
    this.downloadpdf.formtab = 'list';
    this.downloadpdf.formtabs = {'label':'', 'field':'layout', 'tabs':{
        'pricelist':{'label':'Price List', 'field_id':'pricelist'},
        'thumbnails':{'label':'Thumbnails', 'field_id':'thumbnails'},
        'list':{'label':'List', 'field_id':'list'},
//          'quad':{'label':'Quad', 'field_id':'quad'},
        'single':{'label':'Single', 'field_id':'single'},
        'excel':{'label':'Excel', 'field_id':'excel'},
        }};
    this.downloadpdf.forms.thumbnails = {
        'details':{'label':'', 'fields':{
            'pagetitle':{'label':'Title', 'hidelabel':'no', 'type':'text'},
            }},
        'information':{'label':'', 'visible':'yes', 'fields':{
            'pagenumbers':{'label':'Page Numbers', 'type':'toggle', 'default':'yes', 'none':'yes', 'toggles':this.toggleOptions},
            }},
        '_buttons':{'label':'', 'buttons':{
            'download':{'label':'Download', 'fn':'M.ciniki_artcatalog_main.downloadPDF();'},
            }},
    };
    this.downloadpdf.forms.pricelist = {
        'details':{'label':'', 'fields':{
            'pagetitle':{'label':'Title', 'hidelabel':'no', 'type':'text'},
            }},
        'sort':{'label':'', 'fields':{
            'sortby':{'label':'Sort By', 'type':'toggle', 'default':'category', 'toggles':this.downloadpdf.sortbylist},
            }},
        'information':{'label':'Information to include', 'fields':{
            'catalog_number':{'label':'Catalog Number', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
//              'sold_label':{'label':'Sold Label', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            'media':{'label':'Media', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            'size':{'label':'Size', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            'framed_size':{'label':'Framed Size', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            'location':{'label':'Location', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
//              'status_text':{'label':'Status', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
//              'price':{'label':'Price', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            }},
        '_buttons':{'label':'', 'buttons':{
            'download':{'label':'Download', 'fn':'M.ciniki_artcatalog_main.downloadPDF();'},
            }},
    };
    this.downloadpdf.forms.list = {
        'details':{'label':'', 'fields':{
            'pagetitle':{'label':'Title', 'hidelabel':'no', 'type':'text'},
            }},
        'sort':{'label':'', 'fields':{
            'sortby':{'label':'Sort By', 'type':'toggle', 'default':'category', 'toggles':this.downloadpdf.sortbylist},
            'align':{'label':'Align', 'type':'toggle', 'default':'left', 'toggles':{'left':'Left', 'right':'Right'}},
            }},
        'information':{'label':'Information to include', 'fields':{
            'catalog_number':{'label':'Catalog Number', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
//              'sold_label':{'label':'Sold Label', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            'media':{'label':'Media', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            'size':{'label':'Size', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            'framed_size':{'label':'Framed Size', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            'status_text':{'label':'Status', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            'price':{'label':'Price', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            'location':{'label':'Location', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            'description':{'label':'Description', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            'pagenumbers':{'label':'Page Numbers', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            }},
        '_buttons':{'label':'', 'buttons':{
            'download':{'label':'Download', 'fn':'M.ciniki_artcatalog_main.downloadPDF();'},
            }},
    };
//      this.downloadpdf.forms.quad = {
//          'details':{'label':'Title', 'fields':{
//              'pagetitle':{'label':'', 'hidelabel':'no', 'type':'text'},
//              }},
//          'information':{'label':'Information to include', 'fields':{
//              'catalog_number':{'label':'Catalog Number', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
//              'media':{'label':'Media', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
//              'size':{'label':'Size', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
//              'framed_size':{'label':'Framed Size', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
//              'price':{'label':'Price', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
//              'location':{'label':'Location', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
//              'description':{'label':'Description', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
//              'awards':{'label':'Awards', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
//              'notes':{'label':'Notes', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
//              'inspiration':{'label':'Inspiration', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
//              }},
//          '_buttons':{'label':'', 'buttons':{
//              'download':{'label':'Download', 'fn':'M.ciniki_artcatalog_main.downloadPDF();'},
//              }},
//      };
    this.downloadpdf.forms.single = {
        'details':{'label':'', 'fields':{
            'pagetitle':{'label':'Title', 'hidelabel':'no', 'type':'text'},
            }},
        'information':{'label':'Information to include', 'fields':{
            'catalog_number':{'label':'Catalog Number', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
//              'sold_label':{'label':'Sold Label', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            'media':{'label':'Media', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            'size':{'label':'Size', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            'framed_size':{'label':'Framed Size', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            'status_text':{'label':'Status', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            'price':{'label':'Price', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            'location':{'label':'Location', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            'description':{'label':'Description', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            'awards':{'label':'Awards', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            'publications':{'label':'Publications', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            'notes':{'label':'Notes', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            'inspiration':{'label':'Inspiration', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            'pagenumbers':{'label':'Page Numbers', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            }},
        '_buttons':{'label':'', 'buttons':{
            'download':{'label':'Download', 'fn':'M.ciniki_artcatalog_main.downloadPDF();'},
            }},
    };
    this.downloadpdf.forms.excel = {
        'details':{'label':'', 'fields':{
            'pagetitle':{'label':'File Name', 'hidelabel':'no', 'type':'text'},
            }},
        'information':{'label':'Information to include', 'fields':{
            'catalog_number':{'label':'Catalog Number', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            'name':{'label':'Title', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            'category':{'label':'Category', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
//              'sold_label':{'label':'Sold Label', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            'media':{'label':'Media', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            'size':{'label':'Size', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            'framed_size':{'label':'Framed Size', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            'status_text':{'label':'Status', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            'price':{'label':'Price', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            'location':{'label':'Location', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            'description':{'label':'Description', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            'awards':{'label':'Awards', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            'publications':{'label':'Publications', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            'notes':{'label':'Notes', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            'inspiration':{'label':'Inspiration', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
            }},
        '_buttons':{'label':'', 'buttons':{
            'download':{'label':'Download Excel', 'fn':'M.ciniki_artcatalog_main.downloadExcel();'},
            }},
    };
//      this.downloadpdf.sections = this.downloadpdf.forms.list;
    this.downloadpdf.fieldValue = function(s, i, d) { 
        if( this.data[i] == 'null' ) { return ''; }
        if( this.sections[s].fields[i].type == 'toggle' && this.data[i] == null ) {
            return 'no';
        }
        return this.data[i]; 
    }
    this.downloadpdf.sectionData = function(s) {
        return this.data[s];
    };
    this.downloadpdf.addClose('Back');

    //
    // FIXME: Add search panel
    //
    this.search = new M.panel('Search Results',
        'ciniki_artcatalog_main', 'search',
        'mc', 'medium', 'sectioned', 'ciniki.artcatalog.main.search');
    this.search.addClose('Back');

    //
    // The tools available to work on customer records
    //
    this.tools = new M.panel('Catalog Tools',
        'ciniki_artcatalog_main', 'tools',
        'mc', 'narrow', 'sectioned', 'ciniki.artcatalog.main.tools');
    this.tools.data = {};
    this.tools.sections = {
        'tools':{'label':'Adjustments', 'list':{
            'categories':{'label':'Update Category Names', 'fn':'M.startApp(\'ciniki.artcatalog.fieldupdate\', null, \'M.ciniki_artcatalog_main.tools.show();\',\'mc\',{\'field\':\'category\',\'fieldname\':\'Categories\'});'},
            'media':{'label':'Update Media', 'fn':'M.startApp(\'ciniki.artcatalog.fieldupdate\', null, \'M.ciniki_artcatalog_main.tools.show();\',\'mc\',{\'field\':\'media\',\'fieldname\':\'Media\'});'},
            'location':{'label':'Update Locations', 'fn':'M.startApp(\'ciniki.artcatalog.fieldupdate\', null, \'M.ciniki_artcatalog_main.tools.show();\',\'mc\',{\'field\':\'location\',\'fieldname\':\'Locations\'});'},
            'years':{'label':'Update Years', 'fn':'M.startApp(\'ciniki.artcatalog.fieldupdate\', null, \'M.ciniki_artcatalog_main.tools.show();\',\'mc\',{\'field\':\'year\',\'fieldname\':\'Years\'});'},
        }},
        'tools1':{'label':'', 'list':{
            '_cats':{'label':'Category Details', 'fn':'M.startApp(\'ciniki.artcatalog.categories\', null, \'M.ciniki_artcatalog_main.tools.show();\');'},
        }},
        };
    this.tools.addClose('Back');

    this.start = function(cb, appPrefix, aG) {
        args = {};
        if( aG != null ) { args = eval(aG); }

        //
        // Create container
        //
        var appContainer = M.createContainer(appPrefix, 'ciniki_artcatalog_main', 'yes');
        if( appContainer == null ) {
            M.alert('App Error');
            return false;
        }

        //
        // Set lists to visible if enabled
        //
//        for(i in this.guidededit.forms) {
//            if( (M.curTenant.modules['ciniki.artcatalog'].flags&0x01) > 0 ) {
//                this.guidededit.forms[i]._lists.active = 'yes';
//                this.guidededit.forms[i]._lists.fields.lists.active = 'yes';
//                this.item.sections.ainfo.list.lists.visible = 'yes';
//            } else {
//                this.guidededit.forms[i]._lists.active = 'no';
//                this.guidededit.forms[i]._lists.fields.lists.active = 'no';
//                this.item.sections.ainfo.list.lists.visible = 'no';
//            }
//        }

        this.item.sections.oldproducts.visible = (M.curTenant.modules['ciniki.artcatalog'].flags&0x02)>0?'yes':'no';

        if( args.artcatalog_id != null && args.artcatalog_id == 0 ) {
            this.guidededit.open(cb, 0);
        } else if( args.artcatalog_id != null && args.artcatalog_id != '' ) {
            this.item.open(cb, args.artcatalog_id);
        } else {
            this.menu.open(cb);
        }
    }

/*    this.showMenu = function(cb, listby, type, sec) {
        if( type != null ) {
            this.cur_type = type;
            this.menu.sections.types.selected = type;
        }
        if( sec != null ) {
            this.menu.sectiontab = sec;
            // Setup listby for use in PDF downloads
            if( sec == 'categories' ) { this.menu.listby = 'category'; }
            else if( sec == 'media' ) { this.menu.listby = 'media'; }
            else if( sec == 'locations' ) { this.menu.listby = 'location'; }
            else if( sec == 'years' ) { this.menu.listby = 'year'; }
            else if( sec == 'materials' ) { this.menu.listby = 'material'; }
            else if( sec == 'lists' ) { this.menu.listby = 'list'; }
            else if( sec == 'tracking' ) { this.menu.listby = 'tracking'; }
        }
        if( listby != null && (listby == 'category' || listby == 'media' || listby == 'location' || listby == 'year' || listby == 'material' || listby == 'list' || listby == 'tracking' ) ) {
            this.menu.listby = listby;
        }
        if( this.cur_type != null && this.cur_type != '' ) {
            M.api.getJSONCb('ciniki.artcatalog.stats', 
                {'tnid':M.curTenantID, 'type':this.cur_type}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    if( rsp.total <= 5 ) {
                        //M.ciniki_artcatalog_main.showMenuList(cb, rsp, this.cur_type);
                        M.ciniki_artcatalog_main.menu.open(cb, rsp);
                    } else {
                        M.ciniki_artcatalog_main.menu.open(cb, rsp);
                    }
                });
        } else {
            M.api.getJSONCb('ciniki.artcatalog.stats', {'tnid':M.curTenantID}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                if( rsp.total <= 5 ) {
                    //M.ciniki_artcatalog_main.showMenuList(cb, rsp, null);
                    M.ciniki_artcatalog_main.menu.open(cb, rsp);
                } else {
                    M.ciniki_artcatalog_main.menu.open(cb, rsp);
                }
            });
        }
    }; */
    
    //
    // Display stats menu when too many photos
    //
/*    this.menu.open = function(cb, rsp) {
        var p = M.ciniki_artcatalog_main.menu;
        p.data = rsp.stats;
        p.sections.media.visible = 'no';
        if( rsp.stats.media != null ) { 
            p.sections.media.visible = 'yes';
        }
        p.sections.types.visible = 'no';
        p.sections.types.tabs = {};
        if( rsp.stats.types.length > 1 ) {
            p.sections.types.visible = 'yes';
            p.sections.types.tabs['all'] = {'label':'All', 'fn':'M.ciniki_artcatalog_main.showMenu(null,null,\'all\');'};
            for(i in rsp.stats.types) {
                p.sections.types.tabs[rsp.stats.types[i].section.type] = {'label':rsp.stats.types[i].section.name, 'fn':'M.ciniki_artcatalog_main.showMenu(null,null,\'' + rsp.stats.types[i].section.type + '\');'};
            }
        } else {
            this.cur_type = '';
        }
        if( rsp.stats.materials != null ) {
            p.sections.materials.visible = 'yes';
        } else {
            p.sections.materials.visible = 'no';
        }
        if( rsp.stats.lists != null ) {
            p.sections.lists.visible = 'yes';
        } else {
            p.sections.lists.visible = 'no';
        }
        if( rsp.stats.tracking != null ) {
            p.sections.tracking.visible = 'yes';
        } else {
            p.sections.tracking.visible = 'no';
        }
        //
        // Display one section at a time
        //
//      if( rsp.sections > 10 ) {
        if( rsp.sections > 1 ) {
            p.sections.sectiontabs.visible = 'yes';
            p.sections.sectiontabs.tabs = {};
            p.sections.sectiontabs.selected = '';
            for(i in p.sections) {
                if( p.sections[i].type == 'simplegrid' ) {
                    if( rsp.stats[i] != null && rsp.stats[i].length > 0 ) {
                        p.sections.sectiontabs.tabs[i] = {
                            'label':p.sections[i].label,
                            'fn':'M.ciniki_artcatalog_main.showMenu(null,null,null,\'' + i + '\');'};
                        if( i == p.sectiontab ) {
                            p.sections[i].visible = 'yes';
                            p.sections.sectiontabs.selected = i;
                        } else {
                            p.sections[i].visible = 'no';
                        }
                    }
                }
            }
            if( p.sections.sectiontabs.selected == '' ) {
                p.sections.sectiontabs.selected = 'categories';
                p.sections.categories.visible = 'yes';
            }
        } else {
            p.sections.sectiontabs.visible = 'no';
        }
        p.refresh();
        p.show(cb);
    }
*/
    this.showList = function(cb, section, name, list) {
        if( section != null ) {
            this.list.current_section = encodeURIComponent(unescape(section));
        }
        if( name != null ) {
            this.list.current_name = unescape(name);
        }
        if( list != null ) { this.list.prevnextList = list; }
        this.list.data = {};
        if( cb != null ) { this.list.cb = cb; }
        if( this.menu.sections.types.visible == 'yes' && this.menu.sections.types.selected != '' ) {
            this.list.downloadFn = 'M.ciniki_artcatalog_main.showDownload(\'M.ciniki_artcatalog_main.showList();\',\'ciniki.artcatalog.listWithImages\',\'' + this.list.current_section + '\',\'' + escape(this.list.current_name) + '\',\'' + this.menu.sections.types.selected + '\',\'' + escape(this.list.current_name) + '\');';
            var rsp = M.api.getJSONCb('ciniki.artcatalog.listWithImages', 
                {'tnid':M.curTenantID, 'section':this.list.current_section, 
                    'name':this.list.current_name, 
                    'type':this.menu.sections.types.selected}, 
                M.ciniki_artcatalog_main.showListFinish);
        } else {
            this.list.downloadFn = 'M.ciniki_artcatalog_main.showDownload(\'M.ciniki_artcatalog_main.showList();\',\'ciniki.artcatalog.listWithImages\',\'' + this.list.current_section + '\',\'' + escape(this.list.current_name) + '\',\'\',\'' + escape(this.list.current_name) + '\');';
            var rsp = M.api.getJSONCb('ciniki.artcatalog.listWithImages', 
                {'tnid':M.curTenantID, 'section':this.list.current_section, 
                    'name':this.list.current_name}, 
                M.ciniki_artcatalog_main.showListFinish);
        }
    };

    this.showListFinish = function(rsp) {
        if( rsp.stat != 'ok' ) {
            M.api.err(rsp);
            return false;
        }
        var p = M.ciniki_artcatalog_main.list;
        // Setup next/prev buttons
        p.prev_list_name = '';
        p.next_list_name = '';
        if( p.prevnextList != null ) {
            for(i in p.prevnextList) {
                if( p.next_list_name == -1 ) {
                    p.next_list_name = p.prevnextList[i].section.name;
                    break;
                } else if( p.prevnextList[i].section.name == p.current_name ) {
                    p.next_list_name = -1;
                } else {
                    p.prev_list_name = p.prevnextList[i].section.name;
                }
            }
        }

        //
        // If the last image was removed, close this section.
        //
        if( p.current_section != null && rsp.sections.length == 0 ) {
            p.close();
        } else {
            p.sections = {
            };
            // 
            // Setup the menu to display the categories
            //
            p.data = {};
            for(i in rsp.sections) {
                p.data[rsp.sections[i].section.name + ' '] = rsp.sections[i].section.items;
                p.sections[rsp.sections[i].section.name + ' '] = {'label':rsp.sections[i].section.name,
                    'num_cols':3, 'type':'simplegrid', 'headerValues':null,
                    'cellClasses':['thumbnail','multiline','multiline'],
                    'noData':'No Items found',
                    'addTxt':'Add',
                    'addFn':'M.ciniki_artcatalog_main.guidededit.open(\'M.ciniki_artcatalog_main.showList();\',0,M.ciniki_artcatalog_main.list.current_section,M.ciniki_artcatalog_main.list.current_name);',
                };
            }
            if( p.downloadFn != '' ) {
                p.sections['_buttons'] = {'label':'', 'buttons':{
                    'pdf':{'label':'Download', 'fn':p.downloadFn, 'output':'pdf'},
                    }};
            }
                
            p.refresh();
            p.show();
        }
    };

    this.showDownload = function(cb, method, section, name, type, pagetitle) {
        this.downloadpdf.reset();
        this.downloadpdf.method = method;
        this.downloadpdf.list_section = section;
        this.downloadpdf.list_name = unescape(name);
        this.downloadpdf.list_type = type;
        this.downloadpdf.list_artcatalog_id = null;
        this.downloadpdf.data = {'pagetitle':M.curTenant.name + (pagetitle!=''?' - ' + unescape(pagetitle):''),
            'sortby':section,
            'align':'left',
            'catalog_number':'yes',
            'name':'yes',
            'category':'yes',
            'media':'yes',
            'size':'yes',
            'framed_size':'yes',
            'price':'yes',
            'status_text':'yes',
            'location':'yes',
            'description':'yes',
            'awards':'yes',
            'publications':'yes',
//          'notes':'yes',
//          'inspiration':'yes',
            'pagenumbers':'yes',
            };
        this.downloadpdf.formtab = 'list';
        this.downloadpdf.sections = this.downloadpdf.forms.list;
        this.downloadpdf.refresh();
        this.downloadpdf.show(cb);
    };

    this.openDownload = function(cb, method, pagetitle, aid) {
        this.downloadpdf.reset();
        this.downloadpdf.method = method;
        this.downloadpdf.list_section = null;
        this.downloadpdf.list_name = null;
        this.downloadpdf.list_type = null;
        this.downloadpdf.list_artcatalog_id = aid;
        this.downloadpdf.data = {'pagetitle':M.curTenant.name + (pagetitle!=''?' - ' + pagetitle:''),
            'catalog_number':'yes',
            'name':'yes',
            'category':'yes',
            'media':'yes',
            'size':'yes',
            'framed_size':'yes',
            'price':'yes',
            'sold_label':'yes',
            'location':'yes',
            'description':'yes',
            'awards':'yes',
            'publications':'yes',
//          'notes':'yes',
//          'inspiration':'yes',
            'pagenumbers':'no',
            };
        this.downloadpdf.formtab = 'single';
        this.downloadpdf.sections = this.downloadpdf.forms.single;
        this.downloadpdf.refresh();
        this.downloadpdf.show(cb);
    };

    this.downloadPDF = function() {
        var args = {'tnid':M.curTenantID, 'output':'pdf'};
        if( this.downloadpdf.list_section != null && this.downloadpdf.list_section != '' ) { 
            args['section'] = M.eU(this.downloadpdf.list_section);
        }
        if( this.downloadpdf.list_name != null && this.downloadpdf.list_name != '' ) { 
            args['name'] = M.eU(this.downloadpdf.list_name);
        }
        if( this.downloadpdf.list_type != null && this.downloadpdf.list_type != '' ) { 
            args['type'] = this.downloadpdf.list_type;
        }
        if( this.downloadpdf.list_artcatalog_id != null && this.downloadpdf.list_artcatalog_id != '' ) { 
            args['artcatalog_id'] = this.downloadpdf.list_artcatalog_id;
        }
        args['layout'] = this.downloadpdf.formtab;
        var t = this.downloadpdf.formFieldValue(this.downloadpdf.formField('pagetitle'), 'pagetitle');
        args['pagetitle'] = M.eU(t);
        if( args['layout'] == 'pricelist' || args['layout'] == 'list' ) {
            args['sortby'] = this.downloadpdf.formFieldValue(this.downloadpdf.formField('sortby'), 'sortby');
        }
        if( args['layout'] == 'list' ) {
            args['align'] = this.downloadpdf.formFieldValue(this.downloadpdf.formField('align'), 'align');
        }
        var fields = '';
        var flds = ['catalog_number','media','size','framed_size','price','location','description','awards','publications','notes','inspiration'];
        for(i in this.downloadpdf.sections.information.fields) {
            if( this.downloadpdf.formFieldValue(this.downloadpdf.formField(i), i) == 'yes' ) {
                fields += ',' + i;
            }
        }
        if( fields != '' ) {
            args['fields'] = fields.substring(1);
        }
        M.showPDF(this.downloadpdf.method, args);
    };

    this.downloadExcel = function() {
        var args = {'tnid':M.curTenantID, 'output':'excel'};
        if( this.downloadpdf.list_section != null && this.downloadpdf.list_section != '' ) { 
            args['section'] = this.downloadpdf.list_section; 
        }
        if( this.downloadpdf.list_name != null && this.downloadpdf.list_name != '' ) { 
            args['name'] = this.downloadpdf.list_name; 
        }
        if( this.downloadpdf.list_type != null && this.downloadpdf.list_type != '' ) { 
            args['type'] = this.downloadpdf.list_type;
        }
        if( this.downloadpdf.list_artcatalog_id != null && this.downloadpdf.list_artcatalog_id != '' ) { 
            args['artcatalog_id'] = this.downloadpdf.list_artcatalog_id;
        }
        args['layout'] = this.downloadpdf.formtab;
        var t = this.downloadpdf.formFieldValue(this.downloadpdf.formField('pagetitle'), 'pagetitle');
        args['pagetitle'] = t;
        var fields = '';
        var flds = ['catalog_number','title', 'category', 'media','size','framed_size','price','location','description','awards','publications','notes','inspiration'];
        for(i in this.downloadpdf.sections.information.fields) {
            if( this.downloadpdf.formFieldValue(this.downloadpdf.formField(i), i) == 'yes' ) {
                fields += ',' + i;
            }
        }
        if( fields != '' ) {
            args['fields'] = fields.substring(1);
        }

        M.api.openFile(this.downloadpdf.method, args);
    };



}
