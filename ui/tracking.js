//
// The app to add/edit artcatalog images
//
function ciniki_artcatalog_tracking() {
	this.init = function() {
		//
		// The panel to display the edit tracking form
		//
		this.edit = new M.panel('Edit Exhibited',
			'ciniki_artcatalog_tracking', 'edit',
			'mc', 'medium', 'sectioned', 'ciniki.artcatalog.tracking.edit');
		this.edit.data = {};
		this.edit.sections = {
			'info':{'label':'Place', 'type':'simpleform', 'fields':{
				'name':{'label':'Name', 'type':'text', 'livesearch':'yes', 'livesearchempty':'yes'},
				'external_number':{'label':'Number', 'type':'text', 'size':'small'},
				'start_date':{'label':'Start', 'type':'date'},
				'end_date':{'label':'End', 'type':'date'},
			}},
			'_notes':{'label':'Notes', 'type':'simpleform', 'fields':{
				'notes':{'label':'', 'type':'textarea', 'size':'medium', 'hidelabel':'yes'},
			}},
			'_buttons':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_artcatalog_tracking.saveTracking();'},
				'delete':{'label':'Delete', 'fn':'M.ciniki_artcatalog_tracking.deleteTracking();'},
			}},
		};
		this.edit.fieldHistoryArgs = function(s, i) {
			return {'method':'ciniki.artcatalog.trackingHistory', 
				'args':{'business_id':M.curBusinessID, 'tracking_id':this.tracking_id, 'field':i}};
			
		};
		this.edit.fieldValue = function(s, i, d) { 
			if( this.data[i] != null ) { return this.data[i]; } 
			return ''; 
		};
		this.edit.liveSearchCb = function(s, i, value) {
			if( i == 'name' ) {
				var rsp = M.api.getJSONBgCb('ciniki.artcatalog.trackingSearch', 
					{'business_id':M.curBusinessID, 'field':i, 'start_needle':value, 'limit':15},
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
		this.edit.addButton('save', 'Save', 'M.ciniki_artcatalog_tracking.saveTracking();');
		this.edit.addClose('Cancel');

	};

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
			alert('App Error');
			return false;
		}

		if( args.add != null && args.add == 'yes' ) {
			this.showEdit(cb, 0, args.artcatalog_id);
		} else if( args.tracking_id != null && args.tracking_id > 0 ) {
			this.showEdit(cb, args.tracking_id);
		}
		return false;
	}

	this.showEdit = function(cb, tid, aid) {
		if( tid != null ) {
			this.edit.tracking_id = tid;
		}
		if( aid != null ) {
			this.edit.artcatalog_id = aid;
		}
		if( this.edit.tracking_id > 0 ) {
			var rsp = M.api.getJSONCb('ciniki.artcatalog.trackingGet', 
				{'business_id':M.curBusinessID, 'tracking_id':this.edit.tracking_id}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					M.ciniki_artcatalog_tracking.edit.data = rsp.place;
					M.ciniki_artcatalog_tracking.edit.refresh();
					M.ciniki_artcatalog_tracking.edit.show(cb);
				});
		} else {
			this.edit.reset();
			this.edit.data = {};
			this.edit.refresh();
			this.edit.show(cb);
		}
	};

	this.saveTracking = function() {
		if( this.edit.tracking_id > 0 ) {
			var c = this.edit.serializeForm('no');
			if( c != '' ) {
				var rsp = M.api.postJSONCb('ciniki.artcatalog.trackingUpdate', 
					{'business_id':M.curBusinessID, 
					'tracking_id':this.edit.tracking_id}, c, function(rsp) {
						if( rsp.stat != 'ok' ) {
							M.api.err(rsp);
							return false;
						} else {
							M.ciniki_artcatalog_tracking.edit.close();
						}
					});
			} else {
				this.edit.close();
			}
		} else {
			var c = this.edit.serializeForm('yes');
			var rsp = M.api.postJSONCb('ciniki.artcatalog.trackingAdd', 
				{'business_id':M.curBusinessID, 'artcatalog_id':this.edit.artcatalog_id}, c,
					function(rsp) {
						if( rsp.stat != 'ok' ) {
							M.api.err(rsp);
							return false;
						} else {
							M.ciniki_artcatalog_tracking.edit.close();
						}
					});
		}
	};

	this.deleteTracking = function() {
		if( confirm('Are you sure you want to delete \'' + this.edit.data.name + '\' from exhibited?  All information about it will be removed and unrecoverable.') ) {
			var rsp = M.api.getJSONCb('ciniki.artcatalog.trackingDelete', {'business_id':M.curBusinessID, 
				'tracking_id':this.edit.tracking_id}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					M.ciniki_artcatalog_tracking.edit.close();
				});
		}
	};
}
