//
// The app to add/edit artcatalog images
//
function ciniki_artcatalog_products() {
	this.init = function() {
		//
		// The panel to display the edit product form
		//
		this.edit = new M.panel('Product',
			'ciniki_artcatalog_products', 'edit',
			'mc', 'medium mediumaside', 'sectioned', 'ciniki.artcatalog.products.edit');
		this.edit.data = {};
		this.edit.sections = {
			'_image':{'label':'Image', 'aside':'yes', 'type':'imageform', 'fields':{
				'image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'history':'no', 'controls':'all'},
			}},
			'info':{'label':'Place', 'aside':'yes', 'type':'simpleform', 'fields':{
				'name':{'label':'Name', 'type':'text'},
				'price':{'label':'Price', 'type':'text', 'size':'small'},
//				'taxtype_id':{'label':'Price', 'type':'text', 'size':'small'},
				'inventory':{'label':'Inventory', 'type':'text', 'size':'small'},
				'flags':{'label':'Website', 'type':'flags', 'flags':{'1':{'name':'Visible'}}},
				}},
			'_synopsis':{'label':'Synopsis', 'type':'simpleform', 'fields':{
				'synopsis':{'label':'', 'type':'textarea', 'size':'small', 'hidelabel':'yes'},
				}},
			'_description':{'label':'Description', 'type':'simpleform', 'fields':{
				'description':{'label':'', 'type':'textarea', 'size':'medium', 'hidelabel':'yes'},
				}},
			'_buttons':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_artcatalog_products.productSave();'},
				'delete':{'label':'Remove', 'visible':'no', 'fn':'M.ciniki_artcatalog_products.productDelete();'},
				}},
		};
		this.edit.fieldHistoryArgs = function(s, i) {
			return {'method':'ciniki.artcatalog.productHistory', 
				'args':{'business_id':M.curBusinessID, 'product_id':this.product_id, 'field':i}};
			
		};
		this.edit.fieldValue = function(s, i, d) { 
			if( this.data[i] != null ) { return this.data[i]; } 
			return ''; 
		};
		this.edit.addDropImage = function(iid) {
			M.ciniki_artcatalog_products.edit.setFieldValue('image_id', iid);
			return true;
		};
		this.edit.deleteImage = function(fid) {
			this.setFieldValue(fid, 0);
			return true;
		};
		this.edit.addButton('save', 'Save', 'M.ciniki_artcatalog_products.productSave();');
		this.edit.addClose('Cancel');

	};

	this.start = function(cb, appPrefix, aG) {
		args = {};
		if( aG != null ) { args = eval(aG); }

		//
		// Create container
		//
		var appContainer = M.createContainer(appPrefix, 'ciniki_artcatalog_products', 'yes');
		if( appContainer == null ) {
			alert('App Error');
			return false;
		}

		if( args.add != null && args.add == 'yes' ) {
			this.showEdit(cb, 0, args.artcatalog_id);
		} else if( args.product_id != null && args.product_id > 0 ) {
			this.showEdit(cb, args.product_id);
		}
		return false;
	}

	this.showEdit = function(cb, tid, aid) {
		if( tid != null ) { this.edit.product_id = tid; }
		if( aid != null ) { this.edit.artcatalog_id = aid; }
		this.edit.sections._buttons.buttons.delete.visible = (this.edit.product_id>0?'yes':'no');
		M.api.getJSONCb('ciniki.artcatalog.productGet', 
			{'business_id':M.curBusinessID, 'product_id':this.edit.product_id, 'artcatalog_id':this.edit.artcatalog_id}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				var p = M.ciniki_artcatalog_products.edit;
				p.data = rsp.product;
				p.refresh();
				p.show(cb);
			});
	};

	this.productSave = function() {
		if( this.edit.product_id > 0 ) {
			var c = this.edit.serializeForm('no');
			if( c != '' ) {
				var rsp = M.api.postJSONCb('ciniki.artcatalog.productUpdate', 
					{'business_id':M.curBusinessID, 
					'product_id':this.edit.product_id}, c, function(rsp) {
						if( rsp.stat != 'ok' ) {
							M.api.err(rsp);
							return false;
						} else {
							M.ciniki_artcatalog_products.edit.close();
						}
					});
			} else {
				this.edit.close();
			}
		} else {
			var c = this.edit.serializeForm('yes');
			var rsp = M.api.postJSONCb('ciniki.artcatalog.productAdd', 
				{'business_id':M.curBusinessID, 'artcatalog_id':this.edit.artcatalog_id}, c,
					function(rsp) {
						if( rsp.stat != 'ok' ) {
							M.api.err(rsp);
							return false;
						} else {
							M.ciniki_artcatalog_products.edit.close();
						}
					});
		}
	};

	this.productDelete = function() {
		if( confirm('Are you sure you want to remove this products? All information about the product will be removed and unrecoverable.') ) {
			var rsp = M.api.getJSONCb('ciniki.artcatalog.productDelete', {'business_id':M.curBusinessID, 
				'product_id':this.edit.product_id}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					M.ciniki_artcatalog_products.edit.close();
				});
		}
	};
}
