//
// The app to add/edit artcatalog images
//
function ciniki_artcatalog_images() {
    this.webFlags = {
        '1':{'name':'Visible'},
        };
    this.init = function() {
        //
        // The panel to display the edit form
        //
        this.edit = new M.panel('Edit Image',
            'ciniki_artcatalog_images', 'edit',
            'mc', 'medium', 'sectioned', 'ciniki.artcatalog.images.edit');
        this.edit.default_data = {};
        this.edit.data = {};
        this.edit.artcatalog_id = 0;
        this.edit.sections = {
            '_image':{'label':'Image', 'type':'imageform',
                'gstep':1,
                'gtitle':function() { },
                'gtitle-add':'Do you have an additional photo to add?',
                'gtitle-edit':'Would you like to change the photo?',
                'gmore-add':'Use the <b>Add Photo</b> button to select a photo from your computer or tablet.',
                'gmore-edit':'Use the <b>Change Photo</b> button below to select a new photo from your computer or tablet.'
                    + ' If you would like to save the original photo to your computer, press the <span class="icon">G</span> button.',
                'fields':{
                    'image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no'},
                }},
            'info':{'label':'Information', 'type':'simpleform', 
                'gstep':2,
                'gtitle':'Additional Information',
                'fields':{
                    'name':{'label':'Title', 'type':'text',
                        'gtitle':'Do you have a title for this photo?',
                        'htext':'The title is optional, you can leave this blank.'
                        },
//                  'webflags':{'label':'Website', 'type':'flags', 'join':'yes', 'flags':this.webFlags},
                }},
            '_website':{'label':'Website Information', 
                'gstep':2,
                'fields':{
                    'webflags_1':{'label':'Visible', 'type':'flagtoggle', 'field':'webflags', 'bit':0x01, 'default':'on',
                        'gtitle':'Do you want this photo on your website?',
                        },
                }},
            '_description':{'label':'Description', 'type':'simpleform', 
                'gstep':3,
                'gtitle':'How would you describe this photo?',
                'gmore':'Use this field to add more explaination about the contents of the photo.',
                'fields':{
                    'description':{'label':'', 'type':'textarea', 'size':'medium', 'hidelabel':'yes'},
                }},
            '_buttons':{'label':'', 
                'gstep':4,
                'gtext-add':'Press the save button to add the additional image.',
                'gtext-edit':'Press the save button to update the additional image.',
//                  + ' If you would like to save the original photo to your computer, press the <em><span class="icon">G</span></em> button.',
                'gmore-edit':'If you want to remove this additional photo, press the <em>Delete</em> button.',
                'buttons':{
                    'save':{'label':'Save', 'fn':'M.ciniki_artcatalog_images.saveImage();'},
                    'delete':{'label':'Delete', 'visible':'no', 'fn':'M.ciniki_artcatalog_images.deleteImage();'},
                }},
        };
        this.edit.fieldValue = function(s, i, d) { 
            if( this.data[i] != null ) {
                return this.data[i]; 
            } 
            return ''; 
        };
        this.edit.fieldHistoryArgs = function(s, i) {
            return {'method':'ciniki.artcatalog.imageHistory',
                'args':{'tnid':M.curTenantID, 
                'artcatalog_image_id':M.ciniki_artcatalog_images.edit.artcatalog_image_id, 'field':i}};
        };
        this.edit.addDropImage = function(iid) {
            M.ciniki_artcatalog_images.edit.setFieldValue('image_id', iid, null, null);
            return true;
        };
        this.edit.sectionGuidedTitle = function(s) {
            if( s == '_image' ) {
                if( this.data.image_id != null && this.data.image_id > 0 ) {
                    return this.sections[s]['gtitle-edit'];
                } else {
                    return this.sections[s]['gtitle-add'];
                }
            }
            if( this.sections[s] != null && this.sections[s].gtitle != null ) { return this.sections[s].gtitle; }
            return null;
        };
        this.edit.sectionGuidedText = function(s) {
            if( s == '_image' ) {
                if( this.data.image_id != null && this.data.image_id > 0 ) {
                    return this.sections[s]['gtext-edit'];
                } else {
                    return this.sections[s]['gtext-add'];
                }
            }
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
            if( s == '_image' ) {
                if( this.data.image_id != null && this.data.image_id > 0 ) {
                    return this.sections[s]['gmore-edit'];
                } else {
                    return this.sections[s]['gmore-add'];
                }
            }
            if( s == '_buttons' ) {
                if( this.sections[s].buttons.delete.visible == 'yes' ) {
                    return this.sections[s]['gmore-edit'];
                }
            }
            if( this.sections[s] != null && this.sections[s].gmore != null ) { return this.sections[s].gmore; }
            return null;
        };
        this.edit.addButton('save', 'Save', 'M.ciniki_artcatalog_images.saveImage();');
        this.edit.addClose('Cancel');
    };

    this.start = function(cb, appPrefix, aG) {
        args = {};
        if( aG != null ) { args = eval(aG); }

        //
        // Create container
        //
        var appContainer = M.createContainer(appPrefix, 'ciniki_artcatalog_images', 'yes');
        if( appContainer == null ) {
            M.alert('App Error');
            return false;
        }

        if( args.add != null && args.add == 'yes' ) {
            this.showEdit(cb, 0, args.artcatalog_id);
        } else if( args.artcatalog_image_id != null && args.artcatalog_image_id > 0 ) {
            this.showEdit(cb, args.artcatalog_image_id);
        }
        return false;
    }

    this.showEdit = function(cb, iid, eid) {
        if( iid != null ) { this.edit.artcatalog_image_id = iid; }
        if( eid != null ) { this.edit.artcatalog_id = eid; }
        this.edit.reset();
        if( this.edit.artcatalog_image_id > 0 ) {
            this.edit.sections._buttons.buttons.delete.visible = 'yes';
            var rsp = M.api.getJSONCb('ciniki.artcatalog.imageGet', 
                {'tnid':M.curTenantID, 'artcatalog_image_id':this.edit.artcatalog_image_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    M.ciniki_artcatalog_images.edit.data = rsp.image;
                    M.ciniki_artcatalog_images.edit.refresh();
                    M.ciniki_artcatalog_images.edit.show(cb);
                });
        } else {
            this.edit.data = {};
            this.edit.sections._buttons.buttons.delete.visible = 'no';
            this.edit.refresh();
            this.edit.show(cb);
        }
    };

    this.saveImage = function() {
        if( this.edit.artcatalog_image_id > 0 ) {
            var c = this.edit.serializeFormData('no');
            if( c != '' ) {
                var rsp = M.api.postJSONFormData('ciniki.artcatalog.imageUpdate', 
                    {'tnid':M.curTenantID, 
                    'artcatalog_image_id':this.edit.artcatalog_image_id}, c,
                        function(rsp) {
                            if( rsp.stat != 'ok' ) {
                                M.api.err(rsp);
                                return false;
                            } else {
                                M.ciniki_artcatalog_images.edit.close();
                            }
                        });
            } else {
                this.edit.close();
            }
        } else {
            var c = this.edit.serializeFormData('yes');
            var rsp = M.api.postJSONFormData('ciniki.artcatalog.imageAdd', 
                {'tnid':M.curTenantID, 'artcatalog_id':this.edit.artcatalog_id}, c,
                    function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        } else {
                            M.ciniki_artcatalog_images.edit.close();
                        }
                    });
        }
    };

    this.deleteImage = function() {
        if( confirm('Are you sure you want to delete this image?') ) {
            var rsp = M.api.getJSONCb('ciniki.artcatalog.imageDelete', {'tnid':M.curTenantID, 
                'artcatalog_image_id':this.edit.artcatalog_image_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    M.ciniki_artcatalog_images.edit.close();
                });
        }
    };
}
