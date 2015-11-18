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
		'10':'NFS',
		'20':'For Sale',
		'50':'Sold',
		'60':'Private Collection',
		'70':'Artist Collection',
		};
	this.cur_type = null;
	this.init = function() {
		//
		// Setup the main panel to list the collection
		//
		this.menu = new M.panel('Catalog',
			'ciniki_artcatalog_main', 'menu',
			'mc', 'medium', 'sectioned', 'ciniki.artcatalog.main.menu');
		this.menu.data = {};
		this.menu.sections = {};	// Sections are set in showPieces function
		this.menu.listby = 'category';
		this.menu.liveSearchCb = function(s, i, v) {
			if( v != '' ) {
				M.api.getJSONBgCb('ciniki.artcatalog.searchQuick', {'business_id':M.curBusinessID, 'start_needle':v, 'limit':'15'},
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
			return 'M.ciniki_artcatalog_main.showItem(\'M.ciniki_artcatalog_main.showMenu(null);\', \'' + d.item.id + '\');'; 
		};
		this.menu.liveSearchResultRowStyle = function(s, f, i, d) { return ''; };
// 		Currently not allowing full search
//		this.menu.liveSearchSubmitFn = function(s, search_str) {
//			M.ciniki_artcatalog_main.searchArtCatalog('M.ciniki_artcatalog_main.showMenu();', search_str);
//		};
		this.menu.cellValue = function(s, i, j, d) {
			if( j == 0 ) { 
				if( d.item.image_id > 0 ) {
					if( d.item.image != null && d.item.image != '' ) {
						return '<img width="75px" height="75px" src=\'' + d.item.image + '\' />'; 
					} else {
						return '<img width="75px" height="75px" src=\'' + M.api.getBinaryURL('ciniki.artcatalog.getImage', {'business_id':M.curBusinessID, 'image_id':d.item.image_id, 'version':'thumbnail', 'maxwidth':'75'}) + '\' />'; 
					}
				} else {
					return '<img width="75px" height="75px" src=\'/ciniki-mods/core/ui/themes/default/img/noimage_75.jpg\' />';
				}
			}
			if( j == 1 ) { 
				var sold = '';
				var price = '<b>Price</b>: ';
				var media = '';
				var size = '';
//				if( d.item.sold == 'yes' ) { sold = ' <b>SOLD</b>'; }
				if( d.item.price != '' ) {
					price += d.item.price;
				}
				var subtxt = '';
				subtxt += (d.item.catalog_number!=''?'<b>Number</b>: ' + d.item.catalog_number:'');
				subtxt += (d.item.location!=''?(subtxt!=''?', ':'') + '<b>Location</b>: ' + d.item.location:'');
				var subtxt2 = '';
				subtxt2 += (d.item.media!=''?(subtxt2!=''?', ':'') + '<b>Media</b>: ' + d.item.media:'');
				subtxt2 += (d.item.size!=''?(subtxt2!=''?', ':'') + '<b>Size</b>: ' + d.item.size:'');
				subtxt2 += (d.item.framed_size!=''?(subtxt2!=''?', ':'') + '<b>Framed</b>: ' + d.item.framed_size:'');
//				subtxt2 += (d.item.price!='$0.00'||sold!=''?(subtxt2!=''?', ':'') + price + sold:'');
//				subtxt2 += (d.item.price!='$0.00'||sold!=''?(subtxt2!=''?', ':'') + price + sold:'');
				if( subtxt != '' && subtxt2 != '' ) { subtxt += '<br/>'; }
				subtxt += subtxt2;
				return '<span class="maintext">' + d.item.name + '</span>'
					+ (subtxt!=''?'<span class="subtext">'+subtxt+'</span>':'');
			}
			if( j == 2 ) { return '<span class="maintext">' + d.item.status_text + '</span>'
				+ '<span class="subtext">' + d.item.price + '</span>'; }
		};
		this.menu.rowFn = function(s, i, d) {
			return 'M.ciniki_artcatalog_main.showItem(\'M.ciniki_artcatalog_main.showMenu(null);\', \'' + d.item.id + '\',M.ciniki_artcatalog_main.menu.data[unescape(\'' + escape(s) + '\')]);'; 
		};
		this.menu.sectionData = function(s) { 
			return this.data[s];
		};
		this.menu.listValue = function(s, i, d) { 
			return d['label'];
		};
		this.menu.addButton('add', 'Add', 'M.ciniki_artcatalog_main.showEdit(\'M.ciniki_artcatalog_main.showMenu();\',0);');
		this.menu.addButton('tools', 'Tools', 'M.ciniki_artcatalog_main.tools.show(\'M.ciniki_artcatalog_main.showMenu();\');');
		this.menu.addClose('Back');

		//
		// Setup the main panel to list the collection
		//
		this.statsmenu = new M.panel('Catalog',
			'ciniki_artcatalog_main', 'statsmenu',
			'mc', 'medium', 'sectioned', 'ciniki.artcatalog.main.statsmenu');
		this.statsmenu.data = {};
		this.statsmenu.sectiontab = 'categories';
		this.statsmenu.sections = {
			'search':{'label':'', 'type':'livesearchgrid', 'livesearchempty':'no', 'livesearchcols':3, 'hint':'search',
				'noData':'No art found',
				'headerValues':null,
				'cellClasses':['thumbnail', 'multiline', 'multiline'],
				},
			'types':{'label':'', 'visible':'no', 'type':'paneltabs', 'selected':'all', 'tabs':{}},
			'sectiontabs':{'label':'', 'visible':'no', 'type':'paneltabs', 'selected':'categories', 'tabs':{}},
			'categories':{'label':'Categories', 'hidelabel':'yes', 'type':'simplegrid',
				'num_cols':1,
				'addTxt':'Add',
				'addFn':'M.ciniki_artcatalog_main.showEdit(\'M.ciniki_artcatalog_main.showMenu();\',0);',
				},
			'media':{'label':'Media', 'hidelabel':'yes', 'type':'simplegrid',
				'num_cols':1,
				'addTxt':'Add',
				'addFn':'M.ciniki_artcatalog_main.showEdit(\'M.ciniki_artcatalog_main.showMenu();\',0);',
				},
			'locations':{'label':'Locations', 'hidelabel':'yes', 'type':'simplegrid',
				'num_cols':1,
				'addTxt':'Add',
				'addFn':'M.ciniki_artcatalog_main.showEdit(\'M.ciniki_artcatalog_main.showMenu();\',0);',
				},
			'years':{'label':'Years', 'hidelabel':'yes', 'type':'simplegrid',
				'num_cols':1,
				'noData':'No artwork with a year found',
				'addTxt':'Add',
				'addFn':'M.ciniki_artcatalog_main.showEdit(\'M.ciniki_artcatalog_main.showMenu();\',0);',
				},
			'lists':{'label':'Lists', 'hidelabel':'yes', 'visible':'no', 'type':'simplegrid',
				'num_cols':1,
				'noData':'No lists found',
				'addTxt':'Add',
				'addFn':'M.ciniki_artcatalog_main.showEdit(\'M.ciniki_artcatalog_main.showMenu();\',0);',
				},
			'tracking':{'label':'Exhibited', 'hidelabel':'yes', 'visible':'no', 'type':'simplegrid',
				'num_cols':1,
				'noData':'No exhibition places found',
				},
			'_buttons':{'label':'', 'buttons':{
				'pdf':{'label':'Download', 'fn':'M.ciniki_artcatalog_main.showDownload(\'M.ciniki_artcatalog_main.showMenu();\',\'ciniki.artcatalog.listWithImages\',M.ciniki_artcatalog_main.statsmenu.listby,\'\',\'\',\'Catalog\');'},
			}},
		};
		this.statsmenu.listby = 'category';
		this.statsmenu.liveSearchCb = function(s, i, v) {
			if( v != '' ) {
				M.api.getJSONBgCb('ciniki.artcatalog.searchQuick', {'business_id':M.curBusinessID, 'start_needle':v, 'limit':'15'},
					function(rsp) {
						M.ciniki_artcatalog_main.statsmenu.liveSearchShow(s, null, M.gE(M.ciniki_artcatalog_main.statsmenu.panelUID + '_' + s), rsp.items);
					});
			}
			return true;
		};
		this.statsmenu.liveSearchResultValue = function(s, f, i, j, d) {
			if( j == 0 ) { 
				if( d.item.image_id > 0 ) {
					if( d.item.image != null && d.item.image != '' ) {
						return '<img width="75px" height="75px" src=\'' + d.item.image + '\' />'; 
					} else {
						return '<img width="75px" height="75px" src=\'' + M.api.getBinaryURL('ciniki.artcatalog.getImage', {'business_id':M.curBusinessID, 'image_id':d.item.image_id, 'version':'thumbnail', 'maxwidth':'75'}) + '\' />'; 
					}
				} else {
					return '<img width="75px" height="75px" src=\'/ciniki-mods/core/ui/themes/default/img/noimage_75.jpg\' />';
				}
			}
			if( j == 1 ) { return '<span class="maintext">' + d.item.name + '</span><span class="subtext"><b>Media</b>: ' + d.item.media + ', <b>Size</b>: ' + d.item.size + ', <b>Framed</b>: ' + d.item.framed_size + ', <b>Price</b>: ' + d.item.price + '</span>'; }
			if( j == 2 ) { return '<span class="maintext">' + d.item.catalog_number + '</span><span class="subtext">' + d.item.location + '</span>'; }
		};
		this.statsmenu.liveSearchResultRowFn = function(s, f, i, j, d) {
			return 'M.ciniki_artcatalog_main.showItem(\'M.ciniki_artcatalog_main.showMenu(null);\', \'' + d.item.id + '\');'; 
		};
		this.statsmenu.liveSearchResultRowStyle = function(s, f, i, d) { return ''; };
// 		Currently not allowing full search
//		this.statsmenu.liveSearchSubmitFn = function(s, search_str) {
//			M.ciniki_artcatalog_main.searchArtCatalog('M.ciniki_artcatalog_main.showMenu();', search_str);
//		};
		this.statsmenu.sectionData = function(s) { 
			return this.data[s];
		};
		this.statsmenu.cellValue = function(s, i, j, d) {
			return d.section.name + ' <span class="count">' + d.section.count + '</span>';
		};
		this.statsmenu.rowFn = function(s, i, d) {
			switch (s) {
				case 'categories': return 'M.ciniki_artcatalog_main.showList(\'M.ciniki_artcatalog_main.showMenu();\',\'category\',\'' + escape(d.section.name) + '\', M.ciniki_artcatalog_main.statsmenu.data.'+s+');';
				case 'media': return 'M.ciniki_artcatalog_main.showList(\'M.ciniki_artcatalog_main.showMenu();\',\'media\',\'' + escape(d.section.name) + '\', M.ciniki_artcatalog_main.statsmenu.data.'+s+');';
				case 'locations': return 'M.ciniki_artcatalog_main.showList(\'M.ciniki_artcatalog_main.showMenu();\',\'location\',\'' + escape(d.section.name) + '\', M.ciniki_artcatalog_main.statsmenu.data.'+s+');';
				case 'years': return 'M.ciniki_artcatalog_main.showList(\'M.ciniki_artcatalog_main.showMenu();\',\'year\',\'' + escape(d.section.name) + '\', M.ciniki_artcatalog_main.statsmenu.data.'+s+');';
				case 'lists': return 'M.ciniki_artcatalog_main.showList(\'M.ciniki_artcatalog_main.showMenu();\',\'list\',\'' + escape(d.section.name) + '\', M.ciniki_artcatalog_main.statsmenu.data.'+s+');';
				case 'tracking': return 'M.ciniki_artcatalog_main.showList(\'M.ciniki_artcatalog_main.showMenu();\',\'tracking\',\'' + escape(d.section.name) + '\', M.ciniki_artcatalog_main.statsmenu.data.'+s+');';
			}
		};
		this.statsmenu.addButton('add', 'Add', 'M.ciniki_artcatalog_main.showEdit(\'M.ciniki_artcatalog_main.showMenu();\',0);');
		this.statsmenu.addButton('tools', 'Tools', 'M.ciniki_artcatalog_main.tools.show(\'M.ciniki_artcatalog_main.showMenu();\');');
		this.statsmenu.addClose('Back');

		//
		// Setup the panel to list the collection of a category/media/location/year
		//
		this.list = new M.panel('Catalog',
			'ciniki_artcatalog_main', 'list',
			'mc', 'medium', 'sectioned', 'ciniki.artcatalog.main.list');
		this.list.data = {};
		this.list.current_section = '';
		this.list.current_name = '';
		this.list.sections = {};	// Sections are set in showPieces function
		this.list.downloadFn = '';
		this.list.next_list_name = '';
		this.list.prev_list_name = '';
//		this.list.cellValue = function(s, i, j, d) {
//			if( j == 0 ) { 
//				if( d.item.image_id > 0 ) {
//					if( d.item.image != null && d.item.image != '' ) {
//						return '<img width="75px" height="75px" src=\'' + d.item.image + '\' />'; 
//					} else {
//						return '<img width="75px" height="75px" src=\'' + M.api.getBinaryURL('ciniki.artcatalog.getImage', {'business_id':M.curBusinessID, 'image_id':d.item.image_id, 'version':'thumbnail', 'maxwidth':'75'}) + '\' />'; 
//					}
//				} else {
//					return '<img width="75px" height="75px" src=\'/ciniki-mods/core/ui/themes/default/img/noimage_75.jpg\' />';
//				}
//			}
//			if( j == 1 ) { 
//				var sold = '';
//				var price = '<b>Price</b>: ';
//				if( d.item.sold == 'yes' ) { sold = ' <b>SOLD</b>'; }
//				if( d.item.price != '' ) {
//					price += d.item.price;
//				}
//				var subtxt = '';
//				subtxt += (d.item.media!=''?(subtxt!=''?', ':'') + '<b>Media</b>: ' + d.item.media:'');
//				subtxt += (d.item.size!=''?(subtxt!=''?', ':'') + '<b>Size</b>: ' + d.item.size:'');
//				subtxt += (d.item.framed_size!=''?(subtxt!=''?', ':'') + '<b>Framed</b>: ' + d.item.framed_size:'');
//				subtxt += (d.item.price!='$0.00'||sold!=''?(subtxt!=''?', ':'') + price + sold:'');
//				return '<span class="maintext">' + d.item.name + '</span>'
//					+ (subtxt!=''?'<span class="subtext">'+subtxt+'</span>':'');
//			}
//			if( j == 2 ) { return '<span class="maintext">' + d.item.catalog_number + '</span><span class="subtext">' + d.item.location + '</span>'; }
//		};
		this.list.cellValue = this.menu.cellValue;
		this.list.rowFn = function(s, i, d) {
			return 'M.ciniki_artcatalog_main.showItem(\'M.ciniki_artcatalog_main.showList();\', \'' + d.item.id + '\',M.ciniki_artcatalog_main.list.data[unescape(\'' + escape(s) + '\')]);'; 
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
		this.list.addButton('add', 'Add', 'M.ciniki_artcatalog_main.showEdit(\'M.ciniki_artcatalog_main.showList();\',0,M.ciniki_artcatalog_main.list.current_section,M.ciniki_artcatalog_main.list.current_name);');
		this.list.addButton('next', 'next');
		this.list.addClose('Back');
		this.list.addLeftButton('prev', 'Prev');

		//
		// Display information about a item of art
		//
		this.item = new M.panel('Art',
			'ciniki_artcatalog_main', 'item',
			'mc', 'medium mediumaside', 'sectioned', 'ciniki.artcatalog.main.edit');
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
//				'date_completed':{'label':'Completed'},
				'size':{'label':'Size'},
				'status_text':{'label':'Status'},
				'price':{'label':'Price'},
//				'forsale':{'label':'For sale'},
				'website':{'label':'Website', 'type':'flags', 'join':'yes', 'flags':this.webFlags},
			}},
			'description':{'label':'Description', 'type':'htmlcontent'},
			'awards':{'label':'Awards', 'type':'htmlcontent'},
			'ainfo':{'label':'Private Information', 'list':{
				'catalog_number':{'label':'Number'},
				'completed':{'label':'Completed'},
				'media':{'label':'Media'},
				'location':{'label':'Location'},
				'lists':{'label':'Lists'},
			}},
			'tracking':{'label':'Exhibited', 'visible':'yes', 'type':'simplegrid', 'num_cols':1,
				'headerValues':null,
				'cellClasses':['multiline', 'multiline'],
				'addTxt':'Add Exhibited',
				'addFn':'M.startApp(\'ciniki.artcatalog.tracking\',null,\'M.ciniki_artcatalog_main.showItem();\',\'mc\',{\'artcatalog_id\':M.ciniki_artcatalog_main.item.artcatalog_id,\'add\':\'yes\'});',
				},
			'inspiration':{'label':'Inspiration', 'type':'htmlcontent'},
			'notes':{'label':'Notes', 'type':'htmlcontent'},
			'images':{'label':'Additional Images', 'type':'simplethumbs'},
			'_images':{'label':'', 'type':'simplegrid', 'num_cols':1,
				'addTxt':'Add Additional Image',
				'addFn':'M.startApp(\'ciniki.artcatalog.images\',null,\'M.ciniki_artcatalog_main.showItem();\',\'mc\',{\'artcatalog_id\':M.ciniki_artcatalog_main.item.artcatalog_id,\'add\':\'yes\'});',
				},
			'products':{'label':'Products', 'visible':'no', 'type':'simplegrid', 'num_cols':3,
				'headerValues':['Product', 'Inv', 'Price'],
				'cellClasses':['', '', ''],
				'addTxt':'Add Product',
				'addFn':'M.startApp(\'ciniki.artcatalog.products\',null,\'M.ciniki_artcatalog_main.showItem();\',\'mc\',{\'artcatalog_id\':M.ciniki_artcatalog_main.item.artcatalog_id,\'add\':\'yes\'});',
				},
			'invoices':{'label':'Sold to', 'visible':'no', 'type':'simplegrid', 'num_cols':'2',
				'headerValues':null,
				'cellClasses':['multiline','multiline'],
				'addTxt':'Add Sale',
				'addFn':'M.startApp(\'ciniki.sapos.invoice\',null,\'M.ciniki_artcatalog_main.showItem();\',\'mc\',{\'object\':\'ciniki.artcatalog.item\',\'object_id\':M.ciniki_artcatalog_main.item.artcatalog_id});',
				},
			'_buttons':{'label':'', 'buttons':{
				'edit':{'label':'Edit', 'fn':'M.ciniki_artcatalog_main.showEdit(\'M.ciniki_artcatalog_main.showItem();\',M.ciniki_artcatalog_main.item.artcatalog_id);'},
				'pdf':{'label':'Download', 'fn':'M.ciniki_artcatalog_main.showItemDownload(\'M.ciniki_artcatalog_main.showItem();\',\'ciniki.artcatalog.get\',\'\',M.ciniki_artcatalog_main.item.artcatalog_id);'},
				'delete':{'label':'Delete', 'fn':'M.ciniki_artcatalog_main.deletePiece();'},
			}},
			};
		this.item.sectionData = function(s) {
			if( s == 'description' || s == 'awards' || s == 'notes' ) {
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
			if( i == 'description' || i == 'inspiration' || i == 'awards' || i == 'notes' ) { 
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
			else if( s == 'products' ) {
				switch (j) {
					case 0: return d.product.name;
					case 1: return d.product.inventory;
					case 2: return d.product.price;
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
				case 'tracking': return 'M.startApp(\'ciniki.artcatalog.tracking\',null,\'M.ciniki_artcatalog_main.showItem();\',\'mc\',{\'tracking_id\':' + d.place.id + '});';
				case 'products': return 'M.startApp(\'ciniki.artcatalog.products\',null,\'M.ciniki_artcatalog_main.showItem();\',\'mc\',{\'product_id\':' + d.product.id + '});';
				case 'invoices': return 'M.startApp(\'ciniki.sapos.invoice\',null,\'M.ciniki_artcatalog_main.showItem();\',\'mc\',{\'invoice_id\':' + d.invoice.id + '});';
			}
		};
		this.item.noData = function(s) {
			return '';
		};
		this.item.prevButtonFn = function() {
			if( this.prev_item_id > 0 ) {
				return 'M.ciniki_artcatalog_main.showItem(null,\'' + this.prev_item_id + '\');';
			}
			return null;
		};
		this.item.nextButtonFn = function() {
			if( this.next_item_id > 0 ) {
				return 'M.ciniki_artcatalog_main.showItem(null,\'' + this.next_item_id + '\');';
			}
			return null;
		};
		this.item.thumbFn = function(s, i, d) {
			return 'M.startApp(\'ciniki.artcatalog.images\',null,\'M.ciniki_artcatalog_main.showItem();\',\'mc\',{\'artcatalog_image_id\':\'' + d.image.id + '\'});';
		};
		this.item.addDropImage = function(iid) {
			var rsp = M.api.getJSON('ciniki.artcatalog.imageAdd',
				{'business_id':M.curBusinessID, 'image_id':iid,
					'artcatalog_id':M.ciniki_artcatalog_main.item.artcatalog_id});
			if( rsp.stat != 'ok' ) {
				M.api.err(rsp);
				return false;
			}
			return true;
		};
		this.item.addDropImageRefresh = function() {
			if( M.ciniki_artcatalog_main.item.artcatalog_id > 0 ) {
				var rsp = M.api.getJSONCb('ciniki.artcatalog.get', {'business_id':M.curBusinessID, 
					'artcatalog_id':M.ciniki_artcatalog_main.item.artcatalog_id, 'images':'yes'}, function(rsp) {
						if( rsp.stat != 'ok' ) {
							M.api.err(rsp);
							return false;
						}
						M.ciniki_artcatalog_main.item.data.images = rsp.item.images;
						M.ciniki_artcatalog_main.item.refreshSection('images');
					});
			}
		};
		this.item.addButton('edit', 'Edit', 'M.ciniki_artcatalog_main.showEdit(\'M.ciniki_artcatalog_main.showItem();\',M.ciniki_artcatalog_main.item.artcatalog_id);');
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
		this.edit.form_id = 1;
		this.edit.data = null;
		this.edit.cb = null;
		this.edit.forms = {};
		this.edit.gstep = 1;
//		this.edit.gsaveBtn = {'label':'Save', 'fn':'M.ciniki_artcatalog_main.saveItem();'};
		this.edit.formtabs = {'label':'', 'gstep':1, 'field':'type', 
			'gtitle':'What type of artwork is this?',
			'gmore':'The final step is deciding what information to show on your website.',
			'tabs':{
				'painting':{'label':'Painting', 'field_id':1},
				'photograph':{'label':'Photograph', 'field_id':2},
				'jewelry':{'label':'Jewelry', 'field_id':3},
				'sculpture':{'label':'Sculpture', 'field_id':4},
				'fibreart':{'label':'Fibre Art', 'field_id':5},
				'pottery':{'label':'Pottery', 'field_id':8},
			}};
		this.edit.forms.painting = {
			'_image':{'label':'Image', 'aside':'yes', 'type':'imageform', 
				'gstep':2,
				'gtitle-add':'Do you have a photo of your artwork?',
				'gtitle-edit':'Would you like to change the photo?',
				'gmore-add':'Use the <b>Add Photo</b> button to select a photo from your computer or tablet.',
				'gmore-edit':'Use the <b>Change Photo</b> button below to select a new photo from your computer or tablet.',
				'fields':{
					'image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no'},
				}},
			'info':{'label':'Catalog Information', 'aside':'yes', 'type':'simpleform', 
				'gstep':3,
				'gtitle':'Catalog Information',
//				'gtext':'The following information will appear on your website. '
//					+ ' If you don\'t want the painting to be shown, press <b>Hidden</b> below.',
				'fields':{
					'name':{'label':'Title', 'type':'text',
						'gtitle':'What is the name of this painting?',
						'htext':'Each painting in your art catalog must have a unique name. '
							+ ' If you do not name your paintings, then give each painting a unique number. '
							+ ' The numbers can be any format you would like, eg: #134 or 2015-01.',
						},
					'category':{'label':'Category', 'type':'text', 
						'livesearch':'yes', 'livesearchempty':'yes',
						'gtitle':'What is the category for this painting?',
						'htext':'It is recommended to make the category names plural. '
							+ ' examples: Landscapes, Abstracts, Portraits, Regional Landscapes, etc.',
						},
					'size':{'label':'Size', 'type':'text', 'size':'small',
						'gtitle':'What is the unframed size of your painting?',
						},
					'flags_5':{'label':'Framed', 'type':'flagtoggle', 'bit':0x10, 'field':'flags', 'default':'off',
						'gtitle':'Is this item framed?',
						'htext':'',
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
						'gtitle':'What is the framed size?',
						},
					'status':{'label':'Status', 'type':'select', 'options':this.statusOptions},
//					'flags_1':{'label':'For Sale', 'type':'flagtoggle', 'bit':0x01, 'field':'flags', 'default':'off',
//						'gtitle':'Is this item for sale?',
//						'htext':'',
//						},
//					'flags_2':{'label':'Sold', 'type':'flagtoggle', 'bit':0x02, 'field':'flags', 'default':'off',
//						'gtitle':'Is this item sold?',
//						'htext':'',
//						},
					'price':{'label':'Price', 'type':'text', 'size':'small',
						'gtitle':'What is the price of your painting?',
						'htext':"(optional) You can leave this blank if you haven't priced this item.",
						},
				}},
			'ainfo':{'label':'Additional Catalog Information', 'type':'simpleform', 
				'gstep':4,
//				'gtext':'The following information provides additional ways to organized your catalog.'
//					+ ' All of it is optional.',
				'fields':{
					'catalog_number':{'label':'Number', 'type':'text', 'size':'small',
						'gtitle':'Have you assigned a number to this item?',
						'htext':'If you have your own numbering system for your items, you can enter that here.'
							+ ' Otherwise you can leave this blank.',
						},
					'year':{'label':'Year', 'type':'text', 'size':'small', 'livesearch':'yes', 'livesearchempty':'yes',
						'gtitle':'When did you finish this painting?',
						},
					'month':{'label':'Month', 'type':'select', 'options':this.monthOptions,
						},
					'day':{'label':'Day', 'type':'select', 'options':this.dayOptions,
						'htext':"If you don't remember the month and day you can leave them unspecified.",
						},
					'media':{'label':'Media', 'type':'text', 'size':'small', 'livesearch':'yes', 'livesearchempty':'yes',
						'gtitle':'What media did you use to create the painting?',
						'htext':'example: Acrylic, Watercolours, Mixed, etc.',
						},
					'location':{'label':'Location', 'type':'text', 'livesearch':'yes', 'livesearchempty':'yes',
						'gtitle':'Where is the painting currently located?',
						'htext':'This lets you keep track of where your paintings are, or who has them.'
							+ ' Know what is in your studio, at a friends house, or somewhere else.'
							+ " examples: Home Studio, Andrews Collection, Cottage, etc."
						},
				}},
			'_lists':{'label':'Lists', 'active':'no', 'type':'simpleform', 
				'gstep':5,
				'gtitle':'Any lists you want this painting on?',
				'gtext':'These lists can be anything you want and are another way to organize your catalog.'
					+ ' You use the <em>+</em> button to create a new list, or select any existing lists.',
				'fields':{
					'lists':{'label':'', 'active':'no', 'hidelabel':'yes', 'type':'tags', 'tags':[], 'hint':'New List'},
				}},
			'_description':{'label':'Description', 'aside':'yes', 'type':'simpleform', 
				'gstep':6,
				'gtitle':'How would you describe this painting?',
				'gmore':'You can enter anything you want here, including what paint used and what material you painted on. For example: Acrylic on Canvas.',
				'fields':{
					'description':{'label':'', 'type':'textarea', 'size':'medium', 'hidelabel':'yes'},
				}},
			'_inspiration':{'label':'Inspiration', 'type':'simpleform', 
				'gstep':7, 
				'gtitle':'What was your inspiration for this item?',
				'fields':{
					'inspiration':{'label':'', 'type':'textarea', 'size':'small', 'hidelabel':'yes'},
				}},
			'_awards':{'label':'Awards', 'type':'simpleform', 
				'gstep':7,
				'gtitle':'Has this item won any awards?',
				'fields':{
					'awards':{'label':'', 'type':'textarea', 'size':'small', 'hidelabel':'yes'},
				}},
			'_notes':{'label':'Notes', 'type':'simpleform', 
				'gstep':8,
				'gtitle':'Do you have any notes about this item?',
				'gmore':'These notes are only visible to you.',
				'fields':{
					'notes':{'label':'', 'type':'textarea', 'size':'medium', 'hidelabel':'yes'},
				}},
			'_website':{'label':'Website Information', 'type':'simpleform', 
				'gstep':9,
				'fields':{
					'webflags_1':{'label':'Visible', 'type':'flagtoggle', 'field':'webflags', 'bit':0x01, 'default':'on',
						'gtitle':'Do you want this item to appear on your website?',
						'on_fields':['webflags_5', 'webflags_13', 'webflags_12', 'webflags_9', 'webflags_10', 'webflags_11'],
						},
					'webflags_5':{'label':'Category Highlight', 'type':'flagtoggle', 'field':'webflags', 'bit':0x10, 'default':'off',
						'gtitle':'Do you want this item to be the category thumbnail?',
						'htext':'When the list of categories is displayed on your website, the image of this item will be used as the thumbnail.',
						'visible':function() {
							if( (M.ciniki_artcatalog_main.edit.data.webflags&0x01) > 0 ) {
								return 'yes';
							} else {
								return 'no';
							}
						}},
					'webflags_12':{'label':'Price', 'type':'flagtoggle', 'field':'webflags', 'bit':0x0800, 'default':'on', 
						'gtitle':'What other information do you want to include?',
						'htext':'If your item is for sale, the price will be displayed on your website. '
							+ ' If the item is Sold, the word SOLD will be shown instead of the price.',
						'visible':function() {
							if( (M.ciniki_artcatalog_main.edit.data.webflags&0x01) > 0 ) {
								return 'yes';
							} else {
								return 'no';
							}
						}},
					'webflags_13':{'label':'Media', 'type':'flagtoggle', 'field':'webflags', 'bit':0x1000, 'default':'on',
//						'gtitle':"Do you want to show this item's media on your website?",
						'visible':function() {
							if( (M.ciniki_artcatalog_main.edit.data.webflags&0x01) > 0 ) {
								return 'yes';
							} else {
								return 'no';
							}
						}},
					'webflags_9':{'label':'Description', 'type':'flagtoggle', 'field':'webflags', 'bit':0x0100, 'default':'on',
//						'gtitle':'Do you want to show this items description on your website?',
						'visible':function() {
							if( (M.ciniki_artcatalog_main.edit.data.webflags&0x01) > 0 ) {
								return 'yes';
							} else {
								return 'no';
							}
						}},
					'webflags_10':{'label':'Inspiration', 'type':'flagtoggle', 'field':'webflags', 'bit':0x0200, 'default':'on',
//						'gtitle':'Do you want to show this items inspiration on your website?',
						'visible':function() {
							if( (M.ciniki_artcatalog_main.edit.data.webflags&0x01) > 0 ) {
								return 'yes';
							} else {
								return 'no';
							}
						}},
					'webflags_11':{'label':'Awards', 'type':'flagtoggle', 'field':'webflags', 'bit':0x0400, 'default':'on',
//						'gtitle':'Do you want to show this items awards on your website?',
						'visible':function() {
							if( (M.ciniki_artcatalog_main.edit.data.webflags&0x01) > 0 ) {
								return 'yes';
							} else {
								return 'no';
							}
						}},
					}},
			'_buttons':{'label':'', 'gstep':10, 
				'gtitle':'Save your work',
				'gtext':"Press the save button to save the changes you've made.",
//				'gmore-edit':'If you want to remove this item from your catalog, press the Delete button.',
				'buttons':{
					'save':{'label':'Save', 'fn':'M.ciniki_artcatalog_main.saveItem();'},
//					'delete':{'label':'Delete', 'fn':'M.ciniki_artcatalog_main.deletePiece();'},
				}},
		};
		this.edit.forms.photograph = {
			'_image':{'label':'Image', 'aside':'yes', 'type':'imageform',
				'gstep':2,
				'gtitle-add':'Do you have the image on your computer?',
				'gtitle-edit':'Would you like to change the image?',
				'gmore-add':'Use the <b>Add Photo</b> button to select the photo from your computer or tablet.',
				'gmore-edit':'Use the <b>Change Photo</b> button below to select a new photo from your computer or tablet.',
				'fields':{
					'image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no'},
				}},
			'info':{'label':'Catalog Information', 'aside':'yes', 'type':'simpleform', 
				'gstep':3,
				'fields':{
					'name':{'label':'Title', 'type':'text',
						'gtitle':'What is the title of your photograph?',
						'htext':'Each photograph in your art catalog must have a unique name.'
							+ " If you don't name your photographs, then give each photograph a unique number."
							+ " The numbers can be any format you would like, eg: #134 or 2015-01.",
						},
					'category':{'label':'Category', 'type':'text', 'livesearch':'yes', 'livesearchempty':'yes',
						'gtitle':'What is the category for this photograph?',
						'htext':'It is recommended to make the category names plural. '
							+ ' examples: Landscapes, Abstracts, Portraits, Regional Landscapes, etc.',
						},
					'size':{'label':'Size', 'type':'text', 'size':'small',
						'gtitle':'What is the unframed size of your painting?',
						},
//					'flags_1':{'label':'For Sale', 'type':'flagtoggle', 'bit':0x01, 'field':'flags', 'default':'off',
//						'gtitle':'Is this item for sale?',
//						'htext':'',
//						},
					'status':{'label':'Status', 'type':'select', 'options':this.statusOptions},
					'price':{'label':'Price', 'type':'text', 'size':'small',
						'gtitle':'What is the price of your photograph?',
						'htext':"(optional) You can leave this blank if you haven't priced this item.",
						},
				}},
			'ainfo':{'label':'Additional Catalog Information', 'type':'simpleform', 
				'gstep':4,
				'fields':{
					'catalog_number':this.edit.forms.painting.ainfo.fields.catalog_number,
					'year':{'label':'Year', 'type':'text', 'size':'small', 'livesearch':'yes', 'livesearchempty':'yes',
						'gtitle':'When did you take this photograph?',
						},
					'month':{'label':'Month', 'type':'select', 'options':this.monthOptions,
						},
					'day':{'label':'Day', 'type':'select', 'options':this.dayOptions,
						'htext':"If you don't remember the month and day you can leave them unspecified.",
						},
					'media':{'label':'Media', 'type':'text', 'size':'small', 'livesearch':'yes', 'livesearchempty':'yes',
						'gtitle':'What media did you use to create the painting?',
						'htext':'example: Acrylic, Watercolours, Mixed, etc.',
						},
					'location':{'label':'Location', 'type':'text', 'livesearch':'yes', 'livesearchempty':'yes',
						'gtitle':'Where was the photo taken?',
						'htext':'This lets you keep track of where you took the photo.'
							+ " examples: Local Park, Winter 2014 Holiday, Cottage, etc."
						},
//					'other_location':{'label':'Location', 'type':'text', 'livesearch':'yes', 'livesearchempty':'yes',
//						'gtitle':'Where is the painting currently located?',
//						'htext':'This lets you keep track of where your paintings are, or who has them.'
//							+ ' Know what is in your studio, at a friends house, or somewhere else.'
//							+ " examples: Home Studio, Andrews Collection, Cottage, etc."
//						},
				}},
			'_lists':this.edit.forms.painting._lists,
			'_description':{'label':'Description', 'aside':'yes', 'type':'simpleform', 
				'gstep':6,
				'gtitle':'What is the description of this photograph?',
				'gmore':'You can enter anything you want here, include where was the photo taken, etc.',
				'fields':{
					'description':{'label':'', 'type':'textarea', 'size':'medium', 'hidelabel':'yes'},
				}},
			'_inspiration':this.edit.forms.painting._inspiration,
			'_awards':this.edit.forms.painting._awards,
			'_notes':this.edit.forms.painting._notes,
			'_website':{'label':'Website Information', 'type':'simpleform', 
				'gstep':9,
				'gtitle':this.edit.forms.painting._website.gtitle,
				'gtext':this.edit.forms.painting._website.gtext,
				'fields':{
					'webflags_1':this.edit.forms.painting._website.fields.webflags_1,
					'webflags_5':this.edit.forms.painting._website.fields.webflags_5,
					'webflags_12':this.edit.forms.painting._website.fields.webflags_12,
					'webflags_9':this.edit.forms.painting._website.fields.webflags_9,
					'webflags_10':this.edit.forms.painting._website.fields.webflags_10,
					'webflags_11':this.edit.forms.painting._website.fields.webflags_11,
				}},
			'_buttons':{'label':'', 'gstep':10, 
				'gtitle':'Save your work',
				'gtext':"Press the save button to save the changes you've made.",
//				'gmore-edit':'If you want to remove this item from your catalog, press the Delete button.',
				'buttons':{
					'save':{'label':'Save', 'fn':'M.ciniki_artcatalog_main.saveItem();'},
//					'delete':{'label':'Delete', 'fn':'M.ciniki_artcatalog_main.deletePiece();'},
				}},
		};
		this.edit.forms.jewelry = {
			'_image':{'label':'Image', 'aside':'yes', 'type':'imageform',
				'gstep':2,
				'gtitle-add':'Do you have a photo of the jewelry?',
				'gtitle-edit':'Would you like to change the photo?',
				'gmore-add':'Use the <b>Add Photo</b> button to select the photo from your computer or tablet.',
				'gmore-edit':'Use the <b>Change Photo</b> button below to select a new photo from your computer or tablet.',
				'fields':{
					'image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no'},
				}},
			'info':{'label':'Catalog Information', 'aside':'yes', 'type':'simpleform', 
				'gstep':3,
				'fields':{
					'name':{'label':'Title', 'type':'text',
						'gtitle':'What is the title of your item?',
						'htext':'Each item in your art catalog must have a unique name.'
							+ " If you don't name your items, then give each item a unique number."
							+ " The numbers can be any format you would like, eg: #134 or 2015-01.",
						},
					'category':{'label':'Category', 'type':'text', 'livesearch':'yes', 'livesearchempty':'yes',
						'gtitle':'What is the category for this item?',
						'htext':'It is recommended to make the category names plural. '
							+ ' examples: Earrings, Braclets, Pendants, etc.',
						},
//					'flags_1':{'label':'For Sale', 'type':'flagtoggle', 'bit':0x01, 'field':'flags', 'default':'off',
//						'gtitle':'Is this item for sale?',
//						'htext':'',
//						},
//					'flags_2':{'label':'Sold', 'type':'flagtoggle', 'bit':0x02, 'field':'flags', 'default':'off',
//						'gtitle':'Is this item sold?',
//						'htext':'',
//						},
					'status':{'label':'Status', 'type':'select', 'options':this.statusOptions},
					'price':{'label':'Price', 'type':'text', 'size':'small',
						'gtitle':'What is the price of your item?',
						'htext':"(optional) You can leave this blank if you haven't priced this item.",
						},
				}},
			'ainfo':{'label':'Additional Catalog Information', 'type':'simpleform', 
				'gstep':4,
				'fields':{
					'catalog_number':this.edit.forms.painting.ainfo.fields.catalog_number,
					'year':{'label':'Year', 'type':'text', 'size':'small', 'livesearch':'yes', 'livesearchempty':'yes',
						'gtitle':'When did you create this item?',
						},
					'month':{'label':'Month', 'type':'select', 'options':this.monthOptions,
						},
					'day':{'label':'Day', 'type':'select', 'options':this.dayOptions,
						'htext':"If you don't remember the month and day you can leave them unspecified.",
						},
//					'media':{'label':'Media', 'type':'text', 'livesearch':'yes', 'livesearchempty':'yes',
//						'gtitle':'What media did you use to create the item?',
//						'htext':'example: , etc.',
//						},
					'location':{'label':'Location', 'type':'text', 'livesearch':'yes', 'livesearchempty':'yes',
						'gtitle':'Where is the item currently located?',
						'htext':'This lets you keep track of where your items are, or who has them.'
							+ ' Know what is in your studio, at a friends house, or somewhere else.'
							+ " examples: Home Studio, Andrews Collection, Cottage, etc."
						},
				}},
			'_lists':this.edit.forms.painting._lists,
			'_description':{'label':'Description', 'aside':'yes', 'type':'simpleform', 
				'gstep':6,
				'gtitle':'How would you describe this item?',
				'gmore':'You can include information about the materials used, quality of stones or gems, etc.',
				'fields':{
					'description':{'label':'', 'type':'textarea', 'size':'medium', 'hidelabel':'yes'},
				}},
			'_description':this.edit.forms.painting._description,
			'_inspiration':this.edit.forms.painting._inspiration,
			'_awards':this.edit.forms.painting._awards,
			'_notes':this.edit.forms.painting._notes,
			'_website':{'label':'Website Information', 'type':'simpleform', 
				'gstep':9,
				'gtitle':this.edit.forms.painting._website.gtitle,
				'gtext':this.edit.forms.painting._website.gtext,
				'fields':{
					'webflags_1':this.edit.forms.painting._website.fields.webflags_1,
					'webflags_5':this.edit.forms.painting._website.fields.webflags_5,
					'webflags_13':this.edit.forms.painting._website.fields.webflags_13,
					'webflags_12':this.edit.forms.painting._website.fields.webflags_12,
					'webflags_9':this.edit.forms.painting._website.fields.webflags_9,
					'webflags_10':this.edit.forms.painting._website.fields.webflags_10,
					'webflags_11':this.edit.forms.painting._website.fields.webflags_11,
				}},
			'_buttons':{'label':'', 'gstep':10, 
				'gtitle':'Save your work',
				'gtext':"Press the save button to save the changes you've made.",
//				'gmore-edit':'If you want to remove this item from your catalog, press the Delete button.',
				'buttons':{
					'save':{'label':'Save', 'fn':'M.ciniki_artcatalog_main.saveItem();'},
//					'delete':{'label':'Delete', 'fn':'M.ciniki_artcatalog_main.deletePiece();'},
				}},
		};
		this.edit.forms.sculpture = {
			'_image':{'label':'Image', 'aside':'yes', 'type':'imageform',
				'gstep':2,
				'gtitle-add':'Do you have a photo of the sculpture?',
				'gtitle-edit':'Would you like to change the photo?',
				'gmore-add':'Use the <b>Add Photo</b> button to select the photo from your computer or tablet.',
				'gmore-edit':'Use the <b>Change Photo</b> button below to select a new photo from your computer or tablet.',
				'fields':{
					'image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no'},
				}},
			'info':{'label':'Catalog Information', 'aside':'yes', 'type':'simpleform', 
				'gstep':3,
				'gtitle':'Catalog Information',
				'fields':{
					'name':{'label':'Title', 'type':'text',
						'gtitle':'What is the name of this sculpture?',
						'htext':'Each sculpture in your art catalog must have a unique name. '
							+ " If you don't name your sculptures, then give each sculpture a unique number. "
							+ " The numbers can be any format you would like, eg: #134 or 2015-01.",
						},
					'category':{'label':'Category', 'type':'text', 
						'livesearch':'yes', 'livesearchempty':'yes',
						'gtitle':'What is the category for this sculpture?',
						'htext':'It is recommended to make the category names plural. '
							+ ' examples: Statues, Studies, Carvings, etc.',
						},
					'size':{'label':'Size', 'type':'text', 'size':'small',
						'gtitle':'What is the size of your sculpture?',
						},
//					'flags_1':{'label':'For Sale', 'type':'flagtoggle', 'bit':0x01, 'field':'flags', 'default':'off',
//						'gtitle':'Is this sculpture for sale?',
//						'htext':'',
//						},
//					'flags_2':{'label':'Sold', 'type':'flagtoggle', 'bit':0x02, 'field':'flags', 'default':'off',
//						'gtitle':'Is this sculpture sold?',
//						'htext':'',
//						},
					'status':{'label':'Status', 'type':'select', 'options':this.statusOptions},
					'price':{'label':'Price', 'type':'text', 'size':'small',
						'gtitle':'What is the price of your sculpture?',
						'htext':"(optional) You can leave this blank if you haven't priced this item.",
						},
				}},
			'ainfo':{'label':'Additional Catalog Information', 'type':'simpleform', 
				'gstep':4,
				'fields':{
					'catalog_number':this.edit.forms.painting.ainfo.fields.catalog_number,
					'year':{'label':'Year', 'type':'text', 'size':'small', 'livesearch':'yes', 'livesearchempty':'yes',
						'gtitle':'When did you create this item?',
						},
					'month':{'label':'Month', 'type':'select', 'options':this.monthOptions,
						},
					'day':{'label':'Day', 'type':'select', 'options':this.dayOptions,
						'htext':"If you don't remember the month and day you can leave them unspecified.",
						},
					'media':{'label':'Media', 'type':'text', 'livesearch':'yes', 'livesearchempty':'yes',
						'gtitle':'What media did you use to create the item?',
						'htext':'example: Clay, Snow, etc.',
						},
					'location':{'label':'Location', 'type':'text', 'livesearch':'yes', 'livesearchempty':'yes',
						'gtitle':'Where is the item currently located?',
						'htext':'This lets you keep track of where your items are, or who has them.'
							+ ' Know what is in your studio, at a friends house, or somewhere else.'
							+ " examples: Home Studio, Andrews Collection, Cottage, etc."
						},
				}},
			'_lists':this.edit.forms.painting._lists,
			'_description':{'label':'Description', 'aside':'yes', 'type':'simpleform', 
				'gstep':6,
				'gtitle':'How would you describe this sculpture?',
				'gmore':'You can include information about the materials used, how it was produced and any techniques that were utilized.',
				'fields':{
					'description':{'label':'', 'type':'textarea', 'size':'medium', 'hidelabel':'yes'},
				}},
			'_inspiration':this.edit.forms.painting._inspiration,
			'_awards':this.edit.forms.painting._awards,
			'_notes':this.edit.forms.painting._notes,
			'_website':{'label':'Website Information', 'type':'simpleform', 
				'gstep':9,
				'gtitle':this.edit.forms.painting._website.gtitle,
				'gtext':this.edit.forms.painting._website.gtext,
				'fields':{
					'webflags_1':this.edit.forms.painting._website.fields.webflags_1,
					'webflags_5':this.edit.forms.painting._website.fields.webflags_5,
					'webflags_13':this.edit.forms.painting._website.fields.webflags_13,
					'webflags_12':this.edit.forms.painting._website.fields.webflags_12,
					'webflags_9':this.edit.forms.painting._website.fields.webflags_9,
					'webflags_10':this.edit.forms.painting._website.fields.webflags_10,
					'webflags_11':this.edit.forms.painting._website.fields.webflags_11,
				}},
			'_buttons':{'label':'', 'gstep':10, 
				'gtitle':'Save your work',
				'gtext':"Press the save button to save the changes you've made.",
//				'gmore-edit':'If you want to remove this item from your catalog, press the Delete button.',
				'buttons':{
					'save':{'label':'Save', 'fn':'M.ciniki_artcatalog_main.saveItem();'},
//					'delete':{'label':'Delete', 'fn':'M.ciniki_artcatalog_main.deletePiece();'},
				}},
		};
		this.edit.forms.fibreart = {
			'_image':{'label':'Image', 'aside':'yes', 'type':'imageform',
				'gstep':2,
				'gtitle-add':'Do you have a photo of the item?',
				'gtitle-edit':'Would you like to change the photo?',
				'gmore-add':'Use the <b>Add Photo</b> button to select the photo from your computer or tablet.',
				'gmore-edit':'Use the <b>Change Photo</b> button below to select a new photo from your computer or tablet.',
				'fields':{
					'image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no'},
				}},
			'info':{'label':'Catalog Information', 'aside':'yes', 'type':'simpleform', 
				'gstep':3,
				'gtitle':'Catalog Information',
				'fields':{
					'name':{'label':'Title', 'type':'text',
						'gtitle':'What is the name of this item?',
						'htext':'Each item in your art catalog must have a unique name. '
							+ " If you don't name your items, then give each item a unique number. "
							+ " The numbers can be any format you would like, eg: #134 or 2015-01.",
						},
					'category':{'label':'Category', 'type':'text', 
						'livesearch':'yes', 'livesearchempty':'yes',
						'gtitle':'What is the category for this item?',
						'htext':'It is recommended to make the category names plural. '
							+ ' examples: Throws, Rugs, Wall Hangings, etc.',
						},
					'size':{'label':'Size', 'type':'text', 'size':'small',
						'gtitle':'What is the size of your item?',
						},
//					'flags_1':{'label':'For Sale', 'type':'flagtoggle', 'bit':0x01, 'field':'flags', 'default':'off',
//						'gtitle':'Is this item for sale?',
//						'htext':'',
//						},
//					'flags_2':{'label':'Sold', 'type':'flagtoggle', 'bit':0x02, 'field':'flags', 'default':'off',
//						'gtitle':'Is this item sold?',
//						'htext':'',
//						},
					'status':{'label':'Status', 'type':'select', 'options':this.statusOptions},
					'price':{'label':'Price', 'type':'text', 'size':'small',
						'gtitle':'What is the price of your item?',
						'htext':"(optional) You can leave this blank if you haven't priced this item.",
						},
				}},
			'ainfo':{'label':'Additional Catalog Information', 'type':'simpleform', 
				'gstep':4,
				'fields':{
					'catalog_number':this.edit.forms.painting.ainfo.fields.catalog_number,
					'year':{'label':'Year', 'type':'text', 'size':'small', 'livesearch':'yes', 'livesearchempty':'yes',
						'gtitle':'When did you create this item?',
						},
					'month':{'label':'Month', 'type':'select', 'options':this.monthOptions,
						},
					'day':{'label':'Day', 'type':'select', 'options':this.dayOptions,
						'htext':"If you don't remember the month and day you can leave them unspecified.",
						},
					'location':{'label':'Location', 'type':'text', 'livesearch':'yes', 'livesearchempty':'yes',
						'gtitle':'Where is the item currently located?',
						'htext':'This lets you keep track of where your items are, or who has them.'
							+ ' Know what is in your studio, at a friends house, or somewhere else.'
							+ " examples: Home Studio, Andrews Collection, Cottage, etc."
						},
				}},
			'_lists':this.edit.forms.painting._lists,
			'_description':{'label':'Description', 'aside':'yes', 'type':'simpleform', 
				'gstep':6,
				'gtitle':'How would you describe this item?',
				'gmore':'You can include information about how it was produced, what tools or techniques were used.',
				'fields':{
					'description':{'label':'', 'type':'textarea', 'size':'medium', 'hidelabel':'yes'},
				}},
			'_inspiration':this.edit.forms.painting._inspiration,
			'_awards':this.edit.forms.painting._awards,
			'_notes':this.edit.forms.painting._notes,
			'_website':{'label':'Website Information', 'type':'simpleform', 
				'gstep':9,
				'gtitle':this.edit.forms.painting._website.gtitle,
				'gtext':this.edit.forms.painting._website.gtext,
				'fields':{
					'webflags_1':this.edit.forms.painting._website.fields.webflags_1,
					'webflags_5':this.edit.forms.painting._website.fields.webflags_5,
					'webflags_12':this.edit.forms.painting._website.fields.webflags_12,
					'webflags_9':this.edit.forms.painting._website.fields.webflags_9,
					'webflags_10':this.edit.forms.painting._website.fields.webflags_10,
					'webflags_11':this.edit.forms.painting._website.fields.webflags_11,
				}},
			'_buttons':{'label':'', 'gstep':10, 
				'gtitle':'Save your work',
				'gtext':"Press the save button to save the changes you've made.",
//				'gmore-edit':'If you want to remove this item from your catalog, press the Delete button.',
				'buttons':{
					'save':{'label':'Save', 'fn':'M.ciniki_artcatalog_main.saveItem();'},
//					'delete':{'label':'Delete', 'fn':'M.ciniki_artcatalog_main.deletePiece();'},
				}},
		};
		this.edit.forms.pottery = {
			'_image':{'label':'Image', 'aside':'yes', 'type':'imageform',
				'gstep':2,
				'gtitle-add':'Do you have a photo of the item?',
				'gtitle-edit':'Would you like to change the photo?',
				'gmore-add':'Use the <b>Add Photo</b> button to select the photo from your computer or tablet.',
				'gmore-edit':'Use the <b>Change Photo</b> button below to select a new photo from your computer or tablet.',
				'fields':{
					'image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no'},
				}},
			'info':{'label':'Catalog Information', 'aside':'yes', 'type':'simpleform', 
				'gstep':3,
				'gtitle':'Catalog Information',
				'fields':{
					'name':{'label':'Title', 'type':'text',
						'gtitle':'What is the name of this item?',
						'htext':'Each item in your art catalog must have a unique name. '
							+ " If you don't name your items, then give each item a unique number. "
							+ " The numbers can be any format you would like, eg: #134 or 2015-01.",
						},
					'category':{'label':'Category', 'type':'text', 
						'livesearch':'yes', 'livesearchempty':'yes',
						'gtitle':'What is the category for this item?',
						'htext':'It is recommended to make the category names plural. '
							+ ' examples: Bowls, Plates, Mugs, Sets, etc.',
						},
					'size':{'label':'Size', 'type':'text', 'size':'small',
						'gtitle':'What is the size of your item?',
						},
//					'flags_1':{'label':'For Sale', 'type':'flagtoggle', 'bit':0x01, 'field':'flags', 'default':'off',
//						'gtitle':'Is this item for sale?',
//						'htext':'',
//						},
//					'flags_2':{'label':'Sold', 'type':'flagtoggle', 'bit':0x02, 'field':'flags', 'default':'off',
//						'gtitle':'Is this item sold?',
//						'htext':'',
//						},
					'status':{'label':'Status', 'type':'select', 'options':this.statusOptions},
					'price':{'label':'Price', 'type':'text', 'size':'small',
						'gtitle':'What is the price of your item?',
						'htext':"(optional) You can leave this blank if you haven't priced this item.",
						},
				}},
			'ainfo':{'label':'Additional Catalog Information', 'type':'simpleform', 
				'gstep':4,
				'fields':{
					'catalog_number':this.edit.forms.painting.ainfo.fields.catalog_number,
					'year':{'label':'Year', 'type':'text', 'size':'small', 'livesearch':'yes', 'livesearchempty':'yes',
						'gtitle':'When did you create this item?',
						},
					'month':{'label':'Month', 'type':'select', 'options':this.monthOptions,
						},
					'day':{'label':'Day', 'type':'select', 'options':this.dayOptions,
						'htext':"If you don't remember the month and day you can leave them unspecified.",
						},
					'location':{'label':'Location', 'type':'text', 'livesearch':'yes', 'livesearchempty':'yes',
						'gtitle':'Where is the item currently located?',
						'htext':'This lets you keep track of where your items are, or who has them.'
							+ ' Know what is in your studio, at a friends house, or somewhere else.'
							+ " examples: Home Studio, Andrews Collection, Cottage, etc."
						},
				}},
			'_lists':this.edit.forms.painting._lists,
			'_description':{'label':'Description', 'aside':'yes', 'type':'simpleform', 
				'gstep':6,
				'gtitle':'How would you describe this item?',
				'gmore':'You can include information about how it was produced, what tools or techniques were used.',
				'fields':{
					'description':{'label':'', 'type':'textarea', 'size':'medium', 'hidelabel':'yes'},
				}},
			'_inspiration':this.edit.forms.painting._inspiration,
			'_awards':this.edit.forms.painting._awards,
			'_notes':this.edit.forms.painting._notes,
			'_website':{'label':'Website Information', 'type':'simpleform', 
				'gstep':9,
				'gtitle':this.edit.forms.painting._website.gtitle,
				'gtext':this.edit.forms.painting._website.gtext,
				'fields':{
					'webflags_1':this.edit.forms.painting._website.fields.webflags_1,
					'webflags_5':this.edit.forms.painting._website.fields.webflags_5,
					'webflags_13':this.edit.forms.painting._website.fields.webflags_13,
					'webflags_12':this.edit.forms.painting._website.fields.webflags_12,
					'webflags_9':this.edit.forms.painting._website.fields.webflags_9,
					'webflags_10':this.edit.forms.painting._website.fields.webflags_10,
					'webflags_11':this.edit.forms.painting._website.fields.webflags_11,
				}},
			'_buttons':{'label':'', 'gstep':10, 
				'gtitle':'Save your work',
				'gtext':"Press the save button to save the changes you've made.",
//				'gmore-edit':'If you want to remove this item from your catalog, press the Delete button.',
				'buttons':{
					'save':{'label':'Save', 'fn':'M.ciniki_artcatalog_main.saveItem();'},
//					'delete':{'label':'Delete', 'fn':'M.ciniki_artcatalog_main.deletePiece();'},
				}},
		};
		this.edit.form_id = 1;
		this.edit.sections = this.edit.forms.painting;
		this.edit.fieldValue = function(s, i, d) { 
//			if( i.match(/^flags_/) ) { console.log(d);console.log((this.data.flags&d.bit)>0?'on':'off');return (this.data.flags&d.bit)>0?'on':'off'; }
			return this.data[i]; 
		}
		this.edit.sectionData = function(s) {
			return this.data[s];
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
//			if( s == '_buttons' ) {
//				if( this.sections[s].buttons.delete.visible == 'yes' ) {
//					return this.sections[s]['gmore-edit'];
//				}
//			}
			if( this.sections[s] != null && this.sections[s].gmore != null ) { return this.sections[s].gmore; }
			return null;
		};
		this.edit.liveSearchCb = function(s, i, value) {
			if( i == 'category' || i == 'media' || i == 'location' || i == 'year' ) {
				var rsp = M.api.getJSONBgCb('ciniki.artcatalog.searchField', {'business_id':M.curBusinessID, 'field':i, 'start_needle':value, 'limit':15},
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
		this.edit.updateField = function(s, fid, result) {
			M.gE(this.panelUID + '_' + fid).value = unescape(result);
			this.removeLiveSearch(s, fid);
		};
		this.edit.fieldHistoryArgs = function(s, i) {
			return {'method':'ciniki.artcatalog.getHistory', 
				'args':{'business_id':M.curBusinessID, 'artcatalog_id':this.artcatalog_id, 'field':i}};
		}
		this.edit.addDropImage = function(iid) {
			M.ciniki_artcatalog_main.edit.setFieldValue('image_id', iid);
			return true;
		};
		this.edit.deleteImage = function(fid) {
			this.setFieldValue(fid, 0);
			return true;
		};
		this.edit.addButton('save', 'Save', 'M.ciniki_artcatalog_main.saveItem();');
		this.edit.addClose('Cancel');

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
//			'quad':{'label':'Quad', 'field_id':'quad'},
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
//				'sold_label':{'label':'Sold Label', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
				'media':{'label':'Media', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
				'size':{'label':'Size', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
				'framed_size':{'label':'Framed Size', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
				'location':{'label':'Location', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
//				'status_text':{'label':'Status', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
//				'price':{'label':'Price', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
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
				}},
			'information':{'label':'Information to include', 'fields':{
				'catalog_number':{'label':'Catalog Number', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
//				'sold_label':{'label':'Sold Label', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
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
//		this.downloadpdf.forms.quad = {
//			'details':{'label':'Title', 'fields':{
//				'pagetitle':{'label':'', 'hidelabel':'no', 'type':'text'},
//				}},
//			'information':{'label':'Information to include', 'fields':{
//				'catalog_number':{'label':'Catalog Number', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
//				'media':{'label':'Media', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
//				'size':{'label':'Size', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
//				'framed_size':{'label':'Framed Size', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
//				'price':{'label':'Price', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
//				'location':{'label':'Location', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
//				'description':{'label':'Description', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
//				'awards':{'label':'Awards', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
//				'notes':{'label':'Notes', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
//				'inspiration':{'label':'Inspiration', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
//				}},
//			'_buttons':{'label':'', 'buttons':{
//				'download':{'label':'Download', 'fn':'M.ciniki_artcatalog_main.downloadPDF();'},
//				}},
//		};
		this.downloadpdf.forms.single = {
			'details':{'label':'', 'fields':{
				'pagetitle':{'label':'Title', 'hidelabel':'no', 'type':'text'},
				}},
			'information':{'label':'Information to include', 'fields':{
				'catalog_number':{'label':'Catalog Number', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
//				'sold_label':{'label':'Sold Label', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
				'media':{'label':'Media', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
				'size':{'label':'Size', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
				'framed_size':{'label':'Framed Size', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
				'status_text':{'label':'Status', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
				'price':{'label':'Price', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
				'location':{'label':'Location', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
				'description':{'label':'Description', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
				'awards':{'label':'Awards', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
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
//				'sold_label':{'label':'Sold Label', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
				'media':{'label':'Media', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
				'size':{'label':'Size', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
				'framed_size':{'label':'Framed Size', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
				'status_text':{'label':'Status', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
				'price':{'label':'Price', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
				'location':{'label':'Location', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
				'description':{'label':'Description', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
				'awards':{'label':'Awards', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
				'notes':{'label':'Notes', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
				'inspiration':{'label':'Inspiration', 'type':'toggle', 'none':'yes', 'toggles':this.toggleOptions},
				}},
			'_buttons':{'label':'', 'buttons':{
				'download':{'label':'Download Excel', 'fn':'M.ciniki_artcatalog_main.downloadExcel();'},
				}},
		};
//		this.downloadpdf.sections = this.downloadpdf.forms.list;
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
	}

	this.start = function(cb, appPrefix, aG) {
		args = {};
		if( aG != null ) { args = eval(aG); }

		//
		// Create container
		//
		var appContainer = M.createContainer(appPrefix, 'ciniki_artcatalog_main', 'yes');
		if( appContainer == null ) {
			alert('App Error');
			return false;
		}

		//
		// Set lists to visible if enabled
		//
		for(i in this.edit.forms) {
			if( (M.curBusiness.modules['ciniki.artcatalog'].flags&0x01) > 0 ) {
				this.edit.forms[i]._lists.active = 'yes';
				this.edit.forms[i]._lists.fields.lists.active = 'yes';
				this.item.sections.ainfo.list.lists.visible = 'yes';
			} else {
				this.edit.forms[i]._lists.active = 'no';
				this.edit.forms[i]._lists.fields.lists.active = 'no';
				this.item.sections.ainfo.list.lists.visible = 'no';
			}

//			if( M.curBusiness.artcatalog != null && M.curBusiness.artcatalog.settings['enable-lists'] == 'yes' ) {
//			} else {
//				this.edit.forms[i]._lists.visible = 'no';
//				this.edit.forms[i]._lists.fields.lists.active = 'no';
//			}
//			if( M.curBusiness.artcatalog != null && M.curBusiness.artcatalog.settings['enable-inspiration'] == 'yes' ) {
//				this.edit.forms[i]._inspiration.visible = 'yes';
//				this.edit.forms[i]._inspiration.fields.inspiration.active = 'yes';
//			} else {
//				this.edit.forms[i]._inspiration.visible = 'no';
//				this.edit.forms[i]._inspiration.fields.inspiration.active = 'no';
//			}
		}

		this.item.sections.products.visible = (M.curBusiness.modules['ciniki.artcatalog'].flags&0x02)>0?'yes':'no';

		if( args.artcatalog_id != null && args.artcatalog_id == 0 ) {
			this.showEdit(cb, 0);
		} else if( args.artcatalog_id != null && args.artcatalog_id != '' ) {
			this.showItem(cb, args.artcatalog_id);
		} else {
			this.showMenu(cb);
		}
	}

	this.showMenu = function(cb, listby, type, sec) {
//		if( this.statsmenu.sections.types.visible == 'yes' && type != null && type != '') {
		if( type != null ) {
			this.cur_type = type;
			this.statsmenu.sections.types.selected = type;
		}
		if( sec != null ) {
			this.statsmenu.sectiontab = sec;
			// Setup listby for use in PDF downloads
			if( sec == 'categories' ) { this.statsmenu.listby = 'category'; }
			else if( sec == 'media' ) { this.statsmenu.listby = 'media'; }
			else if( sec == 'locations' ) { this.statsmenu.listby = 'location'; }
			else if( sec == 'years' ) { this.statsmenu.listby = 'year'; }
			else if( sec == 'lists' ) { this.statsmenu.listby = 'list'; }
			else if( sec == 'tracking' ) { this.statsmenu.listby = 'tracking'; }
		}
		if( listby != null && (listby == 'category' || listby == 'media' || listby == 'location' || listby == 'year' || listby == 'list' || listby == 'tracking' ) ) {
			this.menu.listby = listby;
			this.statsmenu.listby = listby;
		}
		if( this.cur_type != null && this.cur_type != '' ) {
//		if( this.statsmenu.sections.types.visible
//		if( (this.statsmenu.sections.types.visible == 'yes' && this.statsmenu.sections.types.selected != '') 
//			|| (this.menu.sections.types != null && this.menu.sections.types.visible == 'yes' 
//				&& this.menu.sections.types.selected != '')
//			) {
			var rsp = M.api.getJSONCb('ciniki.artcatalog.stats', 
				{'business_id':M.curBusinessID, 'type':this.cur_type}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					if( rsp.total <= 5 ) {
						M.ciniki_artcatalog_main.showMenuList(cb, rsp, this.cur_type);
					} else {
						M.ciniki_artcatalog_main.showMenuStats(cb, rsp);
					}
				});
		} else {
			var rsp = M.api.getJSONCb('ciniki.artcatalog.stats', {'business_id':M.curBusinessID}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				if( rsp.total <= 5 ) {
					M.ciniki_artcatalog_main.showMenuList(cb, rsp, null);
				} else {
					M.ciniki_artcatalog_main.showMenuStats(cb, rsp);
				}
			});
		}
	};
	
	//
	// Display stats menu when too many photos
	//
	this.showMenuStats = function(cb, rsp) {
		var p = M.ciniki_artcatalog_main.statsmenu;
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
//		if( rsp.sections > 10 ) {
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

	this.showMenuList = function(cb, rsp, type) {
//		var p = M.ciniki_artcatalog_main.statsmenu;
		var p = M.ciniki_artcatalog_main.menu;
		p.sections = {
//			'search':{'label':'', 'type':'livesearchgrid', 'livesearchcols':3, 'hint':'search',
//				'noData':'No art found',
//				'headerValues':null,
//				'cellClasses':['thumbnail', 'multiline', 'multiline'],
//				},
			'types':{'label':'', 'visible':'no', 'type':'paneltabs', 'selected':'all', 'tabs':{}},
			'tabs':{'label':'', 'type':'paneltabs', 'selected':p.listby, 'tabs':{
				'category':{'label':'Category', 'fn':'M.ciniki_artcatalog_main.showMenu(null,\'category\');'},
				'media':{'label':'Media', 'fn':'M.ciniki_artcatalog_main.showMenu(null,\'media\');'},
				'location':{'label':'Location', 'fn':'M.ciniki_artcatalog_main.showMenu(null,\'location\');'},
				'year':{'label':'Year', 'fn':'M.ciniki_artcatalog_main.showMenu(null,\'year\');'},
				}},
		};
		p.sections.types.visible = 'no';
		p.sections.types.tabs = {};
		if( rsp.stats.types != null && rsp.stats.types.length > 1 ) {
			p.sections.types.visible = 'yes';
			p.sections.types.tabs['all'] = {'label':'All', 'fn':'M.ciniki_artcatalog_main.showMenu(null,null,\'all\');'};
			for(i in rsp.stats.types) {
				p.sections.types.tabs[rsp.stats.types[i].section.type] = {'label':rsp.stats.types[i].section.name, 'fn':'M.ciniki_artcatalog_main.showMenu(null,null,\'' + rsp.stats.types[i].section.type + '\');'};
			}
		} else {
			this.cur_type = '';
		}
		if( this.cur_type != null ) {
			this.cur_type = this.cur_type;
			p.sections.types.selected = this.cur_type;
		}
//		if( M.curBusiness.artcatalog != null && M.curBusiness.artcatalog.settings['enable-lists'] == 'yes' ) {	
		if( rsp.stats.lists != null ) {
			p.sections.tabs.tabs['list'] = {'label':'Lists', 'fn':'M.ciniki_artcatalog_main.showMenu(null,\'list\');'};
		}
//		if( M.curBusiness.artcatalog != null && M.curBusiness.artcatalog.settings['enable-tracking'] == 'yes' ) {	
		if( rsp.stats.tracking != null ) {
			p.sections.tabs.tabs['tracking'] = {'label':'Exhibited', 'fn':'M.ciniki_artcatalog_main.showMenu(null,\'tracking\');'};
		}

		//
		// If there is not many items of art, then it's easier to just display a list
		//
		p.data = {};
		var rsp = M.api.getJSONCb('ciniki.artcatalog.listWithImages', 
			{'business_id':M.curBusinessID, 'section':p.listby, 
				'type':(this.cur_type!=null&&this.cur_type!=''?this.cur_type:'')}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}

				// 
				// Setup the menu to display the categories
				//
				var p = M.ciniki_artcatalog_main.menu;
				p.data = {};
				var i = 0;
				for(i in rsp.sections) {
					p.data[rsp.sections[i].section.name + ' '] = rsp.sections[i].section.items;
					p.sections[rsp.sections[i].section.name + ' '] = {'label':rsp.sections[i].section.name,
						'num_cols':3, 'type':'simplegrid', 'headerValues':null,
						'cellClasses':['thumbnail','multiline','multiline'],
						'noData':'No FAQs found',
						'addTxt':'Add',
						'addFn':'M.ciniki_artcatalog_main.showEdit(\'M.ciniki_artcatalog_main.showMenu();\',0,M.ciniki_artcatalog_main.menu.listby,\'' + rsp.sections[i].section.name + '\');',
					};
				}
				if( rsp.sections.length == 0 ) {
					p.data = {};
					p.data['nodata'] = {};
					p.sections.tabs.visible = 'no';
					p.sections['_nodata'] = {'label':'', 'type':'simplegrid', 'num_cols':1,
						'addTxt':'Add your first item',
						'addFn':'M.ciniki_artcatalog_main.showEdit(\'M.ciniki_artcatalog_main.showMenu();\',0);',
						};

//					p.data['_nodata'] = [{'label':'No items found.  '},];
//					p.sections['_nodata'] = {'label':' ', 'type':'simplelist', 'list':{
//						'nodata':{'label':'No items found'}}};
				} else {
					p.sections.tabs.visible = 'yes';
					p.sections['_buttons'] = {'label':'', 'buttons':{
						'pdf':{'label':'Download', 'fn':'M.ciniki_artcatalog_main.showDownload(\'M.ciniki_artcatalog_main.showMenu();\',\'ciniki.artcatalog.listWithImages\',\'' + p.listby + '\',\'\',\'\',\'Catalog\');'},
						}};
				}
				
				p.refresh();
				p.show(cb);
			});
	};

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
		if( this.statsmenu.sections.types.visible == 'yes' && this.statsmenu.sections.types.selected != '' ) {
			this.list.downloadFn = 'M.ciniki_artcatalog_main.showDownload(\'M.ciniki_artcatalog_main.showList();\',\'ciniki.artcatalog.listWithImages\',\'' + this.list.current_section + '\',\'' + escape(this.list.current_name) + '\',\'' + this.statsmenu.sections.types.selected + '\',\'' + escape(this.list.current_name) + '\');';
			var rsp = M.api.getJSONCb('ciniki.artcatalog.listWithImages', 
				{'business_id':M.curBusinessID, 'section':this.list.current_section, 
					'name':this.list.current_name, 
					'type':this.statsmenu.sections.types.selected}, 
				M.ciniki_artcatalog_main.showListFinish);
		} else {
			this.list.downloadFn = 'M.ciniki_artcatalog_main.showDownload(\'M.ciniki_artcatalog_main.showList();\',\'ciniki.artcatalog.listWithImages\',\'' + this.list.current_section + '\',\'' + escape(this.list.current_name) + '\',\'\',\'' + escape(this.list.current_name) + '\');';
			var rsp = M.api.getJSONCb('ciniki.artcatalog.listWithImages', 
				{'business_id':M.curBusinessID, 'section':this.list.current_section, 
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
	//			'search':{'label':'', 'type':'livesearchgrid', 'livesearchcols':3, 'hint':'search',
	//				'noData':'No art found',
	//				'headerValues':null,
	//				'cellClasses':['thumbnail', 'multiline', 'multiline'],
	//				},
	//			'tabs':{'label':'', 'type':'paneltabs', 'selected':this.menu.listby, 'tabs':{
	//				'category':{'label':'Category', 'fn':'M.ciniki_artcatalog_main.showMenu(null,\'category\');'},
	//				'media':{'label':'Media', 'fn':'M.ciniki_artcatalog_main.showMenu(null,\'media\');'},
	//				'location':{'label':'Location', 'fn':'M.ciniki_artcatalog_main.showMenu(null,\'location\');'},
	//				'year':{'label':'Year', 'fn':'M.ciniki_artcatalog_main.showMenu(null,\'year\');'},
	//				}},
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
					'addFn':'M.ciniki_artcatalog_main.showEdit(\'M.ciniki_artcatalog_main.showList();\',0,M.ciniki_artcatalog_main.list.current_section,M.ciniki_artcatalog_main.list.current_name);',
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
		this.downloadpdf.data = {'pagetitle':M.curBusiness.name + (pagetitle!=''?' - ' + unescape(pagetitle):''),
			'sortby':section,
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
//			'notes':'yes',
//			'inspiration':'yes',
			'pagenumbers':'yes',
			};
		this.downloadpdf.formtab = 'list';
		this.downloadpdf.sections = this.downloadpdf.forms.list;
		this.downloadpdf.refresh();
		this.downloadpdf.show(cb);
	};

	this.showItemDownload = function(cb, method, pagetitle, aid) {
		this.downloadpdf.reset();
		this.downloadpdf.method = method;
		this.downloadpdf.list_section = null;
		this.downloadpdf.list_name = null;
		this.downloadpdf.list_type = null;
		this.downloadpdf.list_artcatalog_id = aid;
		this.downloadpdf.data = {'pagetitle':M.curBusiness.name + (pagetitle!=''?' - ' + pagetitle:''),
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
//			'notes':'yes',
//			'inspiration':'yes',
			'pagenumbers':'no',
			};
		this.downloadpdf.formtab = 'single';
		this.downloadpdf.sections = this.downloadpdf.forms.single;
		this.downloadpdf.refresh();
		this.downloadpdf.show(cb);
	};

	this.downloadPDF = function() {
		var args = {'business_id':M.curBusinessID, 'output':'pdf'};
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
//		var l = this.downloadpdf.formFieldValue(this.downloadpdf.formField('layout'), 'layout');
		args['layout'] = this.downloadpdf.formtab;
		var t = this.downloadpdf.formFieldValue(this.downloadpdf.formField('pagetitle'), 'pagetitle');
		args['pagetitle'] = t;
		if( args['layout'] == 'pricelist' || args['layout'] == 'list' ) {
			args['sortby'] = this.downloadpdf.formFieldValue(this.downloadpdf.formField('sortby'), 'sortby');
		}
		var fields = '';
		var flds = ['catalog_number','media','size','framed_size','price','location','description','awards','notes','inspiration'];
		for(i in this.downloadpdf.sections.information.fields) {
			if( this.downloadpdf.formFieldValue(this.downloadpdf.formField(i), i) == 'yes' ) {
				fields += ',' + i;
			}
		}
		if( fields != '' ) {
			args['fields'] = fields.substring(1);
		}
		M.showPDF(this.downloadpdf.method, args);
//		window.open(M.api.getUploadURL(this.downloadpdf.method, args));
	};

	this.downloadExcel = function() {
		var args = {'business_id':M.curBusinessID, 'output':'excel'};
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
//		var l = this.downloadpdf.formFieldValue(this.downloadpdf.formField('layout'), 'layout');
		args['layout'] = this.downloadpdf.formtab;
		var t = this.downloadpdf.formFieldValue(this.downloadpdf.formField('pagetitle'), 'pagetitle');
		args['pagetitle'] = t;
		var fields = '';
		var flds = ['catalog_number','title', 'category', 'media','size','framed_size','price','location','description','awards','notes','inspiration'];
		for(i in this.downloadpdf.sections.information.fields) {
			if( this.downloadpdf.formFieldValue(this.downloadpdf.formField(i), i) == 'yes' ) {
				fields += ',' + i;
			}
		}
		if( fields != '' ) {
			args['fields'] = fields.substring(1);
		}

		M.api.openFile(this.downloadpdf.method, args);
//		window.open(M.api.getUploadURL(this.downloadpdf.method, args));
	};

	this.showItem = function(cb, aid, list) {
		if( aid != null ) { this.item.artcatalog_id = aid; }
		if( list != null ) { this.item.list = list; }

		this.item.sections.invoices.visible = (M.curBusiness.modules['ciniki.customers'] != null && M.curBusiness.modules['ciniki.sapos'] != null)?'yes':'no';

		var rsp = M.api.getJSONCb('ciniki.artcatalog.get', 
			{'business_id':M.curBusinessID, 'artcatalog_id':this.item.artcatalog_id, 
			'tracking':'yes', 'images':'yes', 'invoices':'yes', 'products':'yes'}, function(rsp) {
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
				p.sections.notes.visible=(rsp.item.notes!=null&&rsp.item.notes!='')?'yes':'no';
				if( p.data.lists != null && p.data.lists != '' ) {
					p.data.lists = p.data.lists.replace(/::/g, ', ');
				}

//				p.sections.tracking.visible=(M.curBusiness.artcatalog != null && M.curBusiness.artcatalog.settings['enable-tracking'] == 'yes' )?'yes':'no';

				// Setup next/prev buttons
				p.prev_item_id = 0;
				p.next_item_id = 0;
				if( p.list != null ) {
					for(i in p.list) {
						if( p.next_item_id == -1 ) {
							p.next_item_id = p.list[i].item.id;
							break;
						} else if( p.list[i].item.id == p.artcatalog_id ) {
							// Flag to pickup next item
							p.next_item_id = -1;
						} else {
							p.prev_item_id = p.list[i].item.id;
						}
					}
				}
				p.downloadFn = 'M.ciniki_artcatalog_main.showDownload(\'M.ciniki_artcatalog_main.showItem();\',\'ciniki.artcatalog.get\',\'\',\'\',\'\',\'\');';
				p.refresh();
				p.show(cb);
			});
	};

	this.refreshItemImages = function() {
		if( M.ciniki_artcatalog_main.item.artcatalog_id > 0 ) {
			var rsp = M.api.getJSONCb('ciniki.artcatalog.get', 
				{'business_id':M.curBusinessID, 'artcatalog_id':M.ciniki_artcatalog_main.item.artcatalog_id, 
				'images':'yes'}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					var p = M.ciniki_artcatalog_main.item;
					p.data.images = rsp.item.images;
					p.refreshSection('images');
					p.show();
				});
		}
	};

	this.showEdit = function(cb, aid, section, name) {
		if( aid != null ) { this.edit.artcatalog_id = aid; }
		if( this.edit.artcatalog_id > 0 ) {
			this.edit.gstep = 0;
//			this.edit.size = 'mediumaside';
			var rsp = M.api.getJSONCb('ciniki.artcatalog.get', 
				{'business_id':M.curBusinessID, 'artcatalog_id':this.edit.artcatalog_id}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					var p = M.ciniki_artcatalog_main.edit;
					p.formtab = null;
					p.formtab_field_id = null;
					p.sections._lists.fields.lists.tags = [];
					var tags = [];
					for(i in rsp.tags) {
						tags.push(rsp.tags[i].tag.name);
					}
					for(i in p.forms) {
						if( p.forms[i]._lists != null ) {
							p.forms[i]._lists.fields.lists.tags = tags;
						}
//						p.forms[i]._buttons.buttons.delete.visible = 'yes';
					}
//					p.forms.painting._lists.fields.lists.tags = tags;
//					p.forms.photograph._lists.fields.lists.tags = tags;
//					p.forms.jewelry._lists.fields.lists.tags = tags;
					p.data = rsp.item;
					p.data.followup = '';

					p.refresh();
					p.show(cb);
				});
		} else {
			this.edit.reset();
			this.edit.gstep = 1;
			M.api.getJSONCb('ciniki.artcatalog.get', {'business_id':M.curBusinessID, 'artcatalog_id':this.edit.artcatalog_id}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				var p = M.ciniki_artcatalog_main.edit;
				p.formtab = null;
				p.formtab_field_id = null;
				p.sections._lists.fields.lists.tags = [];
				var tags = [];
				for(i in rsp.tags) {
					tags.push(rsp.tags[i].tag.name);
				}
				for(i in p.forms) {
					if( p.forms[i]._lists != null ) {
						p.forms[i]._lists.fields.lists.tags = tags;
					}
				}
				p.data = rsp.item;
				if( section != null && section == 'category' && name != null && name != '' ) {
					p.data.category = decodeURIComponent(name);
				} else if( section != null && section == 'media' && name != null && name != '' ) {
					p.data.media = decodeURIComponent(name);
				} else if( section != null && section == 'location' && name != null && name != '' ) {
					p.data.location = decodeURIComponent(name);
				} else if( section != null && section == 'year' && name != null && name != '' ) {
					p.data.year = decodeURIComponent(name);
				} else if( section != null && section == 'list' && name != null && name != '' ) {
					p.data['lists'] = name;
				}
				if( M.ciniki_artcatalog_main.statsmenu.sections.types.visible == 'yes' && M.ciniki_artcatalog_main.statsmenu.sections.types.selected != 'all' ) {
					p.formtab = M.ciniki_artcatalog_main.statsmenu.sections.types.selected;
				} else {
					var max = 0;
					for(i in M.ciniki_artcatalog_main.statsmenu.data.types) {
						if( parseInt(M.ciniki_artcatalog_main.statsmenu.data.types[i].section.count) > max ) {
							p.formtab = M.ciniki_artcatalog_main.statsmenu.data.types[i].section.type;
							max = parseInt(M.ciniki_artcatalog_main.statsmenu.data.types[i].section.count);
						}
					}
				}
				p.refresh();
				p.show(cb);
			});
/*
//			if( M.curBusiness.artcatalog != null && M.curBusiness.artcatalog.settings['enable-lists'] == 'yes' ) {
				M.api.getJSONCb('ciniki.artcatalog.getLists', 
					{'business_id':M.curBusinessID, 'type':1}, function(rsp) {
						if( rsp.stat != 'ok' ) {
							M.api.err(rsp);
							return false;
						}
						tags = [];
						for(i in rsp.tags) {
							tags.push(rsp.tags[i].tag.name);
						}
						var p = M.ciniki_artcatalog_main.edit;
						for(i in p.forms) {
							if( p.forms[i]._lists != null ) {
								p.forms[i]._lists.fields.lists.tags = tags;
							}
//							p.forms[i]._buttons.buttons.delete.visible = 'no';
						}
//						p.forms.painting._lists.fields.lists.tags = tags;
//						p.forms.photograph._lists.fields.lists.tags = tags;
//						p.forms.jewelry._lists.fields.lists.tags = tags;
						p.refresh();
						p.show(cb);
					});
//			} else {
//				this.edit.refresh();
//				this.edit.show(cb);
//			}
*/
		}
	};

	this.saveItem = function() {
		if( this.edit.artcatalog_id > 0 ) {
			var c = this.edit.serializeFormData('no');
			if( c != '' ) {
				var nv = this.edit.formFieldValue(this.edit.sections.info.fields.name, 'name');
				if( nv != this.edit.fieldValue('info', 'name') && nv == '' ) {
					alert('You must specifiy a title');
					return false;
				}
				M.api.postJSONFormData('ciniki.artcatalog.update', 
					{'business_id':M.curBusinessID, 'artcatalog_id':this.edit.artcatalog_id}, c,
						function(rsp) {
							if( rsp.stat != 'ok' ) {
								M.api.err(rsp);
								return false;
							} else {
								M.ciniki_artcatalog_main.edit.close();
							}
						});
			} 
		} else {
			var c = this.edit.serializeFormData('yes');
			var nv = this.edit.formFieldValue(this.edit.sections.info.fields.name, 'name');
			if( nv == '' ) {
				alert('You must specifiy a title');
				return false;
			}
			M.api.postJSONFormData('ciniki.artcatalog.add', {'business_id':M.curBusinessID}, c,
				function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					} else {
						M.ciniki_artcatalog_main.edit.close();
					}
				});
		}
	};

	this.deletePiece = function() {
		if( confirm('Are you sure you want to delete \'' + this.item.data.name + '\'?  All information, photos and exhibited information will be removed. There is no way to get the information back once deleted.') ) {
			var rsp = M.api.getJSONCb('ciniki.artcatalog.delete', 
				{'business_id':M.curBusinessID, 'artcatalog_id':this.item.artcatalog_id}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					M.ciniki_artcatalog_main.item.close();
				});
		}
	};
}
