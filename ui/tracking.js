//
// The app to add/edit artcatalog images
//
function ciniki_artcatalog_tracking() {
    //
    // The panel to display the edit tracking form
    //
    this.edit = new M.panel('Edit Exhibited',
        'ciniki_artcatalog_tracking', 'edit',
        'mc', 'medium', 'sectioned', 'ciniki.artcatalog.tracking.edit');
    this.edit.data = {};
//    this.edit.gstep = 1;
    this.edit.sections = {
        'info':{'label':'Place', 'type':'simpleform', 
//            'gstep':1,
//            'gtitle':'Where was the item exhibited?',
            'fields':{
                'name':{'label':'Name', 'type':'text', 'livesearch':'yes', 'livesearchempty':'yes',
                    'gtitle':'What is the name of the venue?',
                    'htext':'The place where you displayed your work.'
                        + " This can be a gallery, personal collection, show or anything else you want.",
                        },
                'external_number':{'label':'Number', 'type':'text', 'size':'small',
                    'gtitle':'Did they give you an item number?',
                    'htext':'If the venue has their own item number, you can enter that here.'},
                'start_date':{'label':'Start', 'type':'date',
                    'gtitle':'When was your item displayed?',
                    'htext':'The first day your item was on display.',
                    },
                'end_date':{'label':'End', 'type':'date',
                    'htext':'The last day your item was on display.'},
            }},
        '_notes':{'label':'Notes', 'type':'simpleform', 
//            'gstep':2,
//            'gtitle':'Do you have any notes about the exhibition?',
//            'gmore':'Any private notes you want to keep about showing this item at this venue.',
            'fields':{
                'notes':{'label':'', 'type':'textarea', 'size':'medium', 'hidelabel':'yes'},
            }},
        '_buttons':{'label':'', 
//            'gstep':3,
//            'gtitle':'Save the exhibition information',
//            'gtext-add':'Press the save button this exhibited place.',
//            'gtext-edit':'Press the save button the changes.',
//            'gmore-edit':'If you want to remove this exhibited place for your item, press the Remove button.',
            'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_artcatalog_tracking.edit.save();'},
                'delete':{'label':'Remove', 'visible':'no', 'fn':'M.ciniki_artcatalog_tracking.edit.remove();'},
            }},
    };
    this.edit.fieldHistoryArgs = function(s, i) {
        return {'method':'ciniki.artcatalog.trackingHistory', 
            'args':{'tnid':M.curTenantID, 'tracking_id':this.tracking_id, 'field':i}};
        
    };
    this.edit.fieldValue = function(s, i, d) { 
        if( this.data[i] != null ) { return this.data[i]; } 
        return ''; 
    };
    this.edit.liveSearchCb = function(s, i, value) {
        if( i == 'name' ) {
            M.api.getJSONBgCb('ciniki.artcatalog.trackingSearch', 
                {'tnid':M.curTenantID, 'field':i, 'start_needle':value, 'limit':15},
                function(rsp) {
                    M.ciniki_artcatalog_tracking.edit.liveSearchShow(s, i, M.gE(M.ciniki_artcatalog_tracking.edit.panelUID + '_' + i), rsp.results);
                });
        }
    };
    this.edit.liveSearchResultClass = function(s, f, i, j, value) {
        return 'multiline';
    };
    this.edit.liveSearchResultValue = function(s, f, i, j, d) {
        if( (f == 'name' ) && d.result != null ) { return '<span class="maintext">' + d.result.name + '</span><span class="subtext">' + d.result.start_date + ' - ' + d.result.end_date + '</span>'; }
        return '';
    };
    this.edit.liveSearchResultRowFn = function(s, f, i, j, d) { 
        if( (f == 'name') && d.result != null ) {
            return 'M.ciniki_artcatalog_tracking.edit.updateField(\'' + s + '\',\'' + f + '\',\'' + escape(d.result.name) + '\',\'' + escape(d.result.start_date) + '\',\'' + escape(d.result.end_date) + '\');';
        }
    };
    this.edit.updateField = function(s, fid, result, sd, ed) {
        M.gE(this.panelUID + '_' + fid).value = unescape(result);
        if( fid == 'name' ) {
            M.gE(this.panelUID + '_start_date').value = unescape(sd);
            M.gE(this.panelUID + '_end_date').value = unescape(ed);
        }
        this.removeLiveSearch(s, fid);
    };
    this.edit.sectionGuidedText = function(s) {
        if( s == '_buttons' ) {
            if( this.sections[s].buttons.delete.visible == 'yes' ) {
                return this.sections[s]['gtext-edit'];
            } else {
                return this.sections[s]['gtext-add'];
            }
        }
        if( this.sections[s] != null && this.sections[s].gtext != null ) { return this.sections[s].gtext; }
        return null;
    };
    this.edit.sectionGuidedMore = function(s) {
        if( s == '_buttons' ) {
            if( this.sections[s].buttons.delete.visible == 'yes' ) {
                return this.sections[s]['gmore-edit'];
            }
        }
        if( this.sections[s] != null && this.sections[s].gmore != null ) { return this.sections[s].gmore; }
        return null;
    };
    this.edit.open = function(cb, tid, aid, name) {
        if( tid != null ) { this.tracking_id = tid; }
        if( aid != null ) { this.artcatalog_id = aid; }
        if( this.tracking_id > 0 ) {
            this.sections._buttons.buttons.delete.visible = 'yes';
            M.api.getJSONCb('ciniki.artcatalog.trackingGet', 
                {'tnid':M.curTenantID, 'tracking_id':this.tracking_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    var p = M.ciniki_artcatalog_tracking.edit;
                    p.data = rsp.place;
                    p.refresh();
                    p.show(cb);
                });
        } else {
            this.reset();
            this.sections._buttons.buttons.delete.visible = 'no';
            this.data = {};
            this.refresh();
            this.show(cb);
        }
    }
    this.edit.save = function() {
        if( this.tracking_id > 0 ) {
            var c = this.serializeForm('no');
            if( c != '' ) {
                M.api.postJSONCb('ciniki.artcatalog.trackingUpdate', {'tnid':M.curTenantID, 
                    'tracking_id':this.tracking_id}, c, function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        } else {
                            M.ciniki_artcatalog_tracking.edit.close();
                        }
                    });
            } else {
                this.close();
            }
        } else {
            var c = this.serializeForm('yes');
            M.api.postJSONCb('ciniki.artcatalog.trackingAdd', 
                {'tnid':M.curTenantID, 'artcatalog_id':this.artcatalog_id}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    } else {
                        M.ciniki_artcatalog_tracking.edit.close();
                    }
                });
        }
    }
    this.edit.remove = function() {
        M.confirm('Are you sure you want to remove \'' + this.data.artcatalog_name + '\' from the exhibited list \'' + this.data.name + '\'?',null,function() {
            M.api.getJSONCb('ciniki.artcatalog.trackingDelete', {'tnid':M.curTenantID, 
                'tracking_id':M.ciniki_artcatalog_tracking.edit.tracking_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    M.ciniki_artcatalog_tracking.edit.close();
                });
        });
    }
    this.edit.addButton('save', 'Save', 'M.ciniki_artcatalog_tracking.edit.save();');
    this.edit.addClose('Cancel');

    this.start = function(cb, appPrefix, aG) {
        args = {};
        if( aG != null ) {
            args = eval(aG);
        }

        //
        // Create container
        //
        var appContainer = M.createContainer(appPrefix, 'ciniki_artcatalog_tracking', 'yes');
        if( appContainer == null ) {
            M.alert('App Error');
            return false;
        }

        if( args.add != null && args.add == 'yes' ) {
            this.edit.open(cb, 0, args.artcatalog_id, unescape(args.name));
        } else if( args.tracking_id != null && args.tracking_id > 0 ) {
            this.edit.open(cb, args.tracking_id, unescape(args.name));
        }
        return false;
    }
}
