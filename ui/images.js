//
// The app to add/edit artcatalog images
//
function ciniki_artcatalog_images() {
	this.webFlags = {
		'1':{'name':'Hidden'},
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
			'_image':{'label':'Photo', 'fields':{
				'image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no'},
			}},
			'info':{'label':'Information', 'type':'simpleform', 'fields':{
				'name':{'label':'Title', 'type':'text'},
				'webflags':{'label':'Website', 'type':'flags', 'join':'yes', 'flags':this.webFlags},
			}},
			'_description':{'label':'Description', 'type':'simpleform', 'fields':{
				'description':{'label':'', 'type':'textarea', 'size':'small', 'hidelabel':'yes'},
			}},
			'_buttons':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_artcatalog_images.saveImage();'},
				'download':{'label':'Download Original', 'fn':'M.ciniki_artcatalog_images.downloadImage(M.ciniki_artcatalog_images.edit.data.image_id, \'original\');'},
				'delete':{'label':'Delete', 'fn':'M.ciniki_artcatalog_images.deleteImage();'},
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
				'args':{'business_id':M.curBusinessID, 
				'artcatalog_image_id':M.ciniki_artcatalog_images.edit.artcatalog_image_id, 'field':i}};
		};
		this.edit.addDropImage = function(iid) {
			M.ciniki_artcatalog_images.edit.setFieldValue('image_id', iid, null, null);
			return true;
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
			alert('App Error');
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
		if( this.edit.artcatalog_image_id > 0 ) {
			this.edit.sections._buttons.buttons.download.visible = 'yes';
			this.edit.sections._buttons.buttons.delete.visible = 'yes';
			var rsp = M.api.getJSONCb('ciniki.artcatalog.imageGet', 
				{'business_id':M.curBusinessID, 'artcatalog_image_id':this.edit.artcatalog_image_id}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					M.ciniki_artcatalog_images.edit.data = rsp.image;
					M.ciniki_artcatalog_images.edit.refresh();
					M.ciniki_artcatalog_images.edit.show(cb);
				});
		} else {
			this.edit.reset();
			this.edit.data = {};
			this.edit.sections._buttons.buttons.download.visible = 'no';
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
					{'business_id':M.curBusinessID, 
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
				{'business_id':M.curBusinessID, 'artcatalog_id':this.edit.artcatalog_id}, c,
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

	this.downloadImage = function(iid, version) {
		M.api.openFile('ciniki.images.get', {'business_id':M.curBusinessID,
			'image_id':iid, 'version':version, 'attachment':'yes'});
	};

	this.deleteImage = function() {
		if( confirm('Are you sure you want to delete this image?') ) {
			var rsp = M.api.getJSONCb('ciniki.artcatalog.imageDelete', {'business_id':M.curBusinessID, 
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
