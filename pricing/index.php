<?php
	ini_set('session.gc_maxlifetime', 86400);
	require_once('sessionValidator.php');

	@session_start();

	$pageURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
	} 
	else {
		$pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
	}
	if(preg_match('/index\.php/', $pageURL))
		$pageURL = preg_replace('/index\.php/', 'login.php', $pageURL);
	else
		$pageURL .= 'login.php';
	$referrerUrl = $pageURL;
	
	if(isset($_COOKIE['username'])) {
		$username = $_COOKIE['username'];
		if(isset($_COOKIE['session'])) {
			$session = $_COOKIE['session'];
			$ob = new validateSession();
			$validSession = $ob->validateCurrentSession($username, $session);
			if(!$validSession === TRUE) {
				header('Location: ' . $referrerUrl);
			}
		}
		else {
			header('Location: ' . $referrerUrl);
		}
	}
	else {
		header('Location: ' . $referrerUrl);
	}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8"> 
<link rel="stylesheet" type="text/css" href="css/index.css" />
<link rel="stylesheet" type="text/css" href="css/items.css" />
<link rel="stylesheet" type="text/css" href="css/suppliers.css" />
<link rel="stylesheet" type="text/css" href="css/pricing.css" />
<link rel="stylesheet" type="text/css" href="css/clients.css" />
<link rel="stylesheet" type="text/css" href="css/font-awesome.css" />
<link rel="stylesheet" type="text/css" href="css/jquery.jscrollpane.css" />
</head>

<body>
<div id="app-contents"></div>


<!-- INDEX : START -->
<!-- JS in index.js -->
	<script type="text/template" id="index-view">
		<div id="home-tabs">
			<img src="img/logo.png" id="aalgro-logo" />
			<a id="items-tab" href="#/items">Items</a>
			<a id="suppliers-tab" href="#/suppliers">Suppliers</a>
			<a id="pricing-tab" href="#/pricing">Pricing</a>
			<a id="clients-tab" href="#/clients">Clients</a>
		</div>
		<div id="contents"></div>
	</script>
<!-- INDEX : END -->


<!-- ITEMS : START -->
<!-- JS in items.js -->
	<script type="text/template" id="items-view">
		<div id="items-tabs">
			<a id="leafy-items" class="item-category-tab" href="#/items/leafy">Leafy</a>
			<a id="fruits-items" class="item-category-tab" href="#/items/fruits">Fruits</a>
			<a id="exotic-items" class="item-category-tab" href="#/items/exotic">Exotic</a>
			<a id="opg-items" class="item-category-tab" href="#/items/opg">OPG</a>
			<a id="vegetables-items" class="item-category-tab" href="#/items/vegetables">Vegetables</a>
		</div>
		<div id="item-category-contents"></div>
	</script>

	<script type="text/template" id="items-loader-view">
		<div id="all-items-loader"><img src="img/486.gif" /></div>
	</script>

	<script type="text/template" id="items-category-view">
		<% if(items.length == 0) { %>
		<div id="no-items">No items found</div>
		<table id="all-items-table" style="display:none" data-units='<%= JSON.stringify(units) %>' data-category-id="<%= category_id %>">
			<tr>
				<th class="item-header-name">Name</th>
				<th class="item-header-units">Units</th>
				<th class="item-header-varieties">Varieties</th>
				<th class="item-header-edit"></th>
			</tr>
		</table>
		<% } else { %>
		<div id="no-items" style="display:none">No items found</div>
		<table id="all-items-table" data-units='<%= JSON.stringify(units) %>' data-category-id="<%= category_id %>">
			<tr>
				<th class="item-header-name">Name</th>
				<th class="item-header-units">Units</th>
				<th class="item-header-varieties">Varieties</th>
				<th class="item-header-edit"></th>
			</tr>
			<% _.each(items, function (item, index) { %>
			<tr class="item-row" id="item-<%= index.replace('_','') %>" data-item-id="<%= index.replace('_','') %>" data-item='<%= JSON.stringify(item) %>'>
				<td class="item-header-name"><%= item['item_name'] %></td>
				<% 
				var units = '';
				for(var i in item.item_units) {
					units += '<div class="sep-div">' + item.item_units[i] + '</div>';
				}
				%>
				<td class="item-header-units"><%= units %></td>
				<% 
				var varieties = '';
				for(var i in item.item_varieties) {
					varieties += '<div class="sep-div">' + item.item_varieties[i] + '</div>';
				}
				%>
				<td class="item-header-varieties"><%= varieties %></td>
				<td class="item-header-edit"><i class="item-edit-button fa fa-edit fa-lg" title="Edit Item"></i></td>
			</tr>
			<% }); %>
		</table>
		<% } %>
		<div id="add-save-more-items">
			<div id="add-more-items">Add Item</div>
			<div id="save-new-items" data-in-progress="0">Save Items</div>
		</div>
	</script>

	<script type="text/template" id="item-row-edit-view">
		<% 
		var item = JSON.parse(item_json); 
		var units = JSON.parse(units);
		if(item_id == 'new') 
			var new_item = 1;
		else 
			var new_item = 0;
		%>
		<tr data-item-id="<%= item_id %>" class="item-row item-row-edit <%= (new_item == 1 ? 'item-row-new' : '') %>" data-item='<%= item_json %>'>
			<td class="item-header-name">
				<div class="td-div">
					<div class="error item-name-error"></div>
					<div class="sep-div">
						<input type="text" <%= (new_item == 1 ? '' : 'disabled') %> defaultValue="<%= item['item_name'] %>" value="<%= item['item_name'] %>" />
						<%= (new_item == 1 ? '' : '<i class="item-name-edit fa fa-edit" title="Edit Item Name"></i>') %>
					</div>
				</div>
			</td>
			<td class="item-header-units">
				<div class="td-div">
					<div class="error item-unit-error"></div>
					<% for(var i in units) { 
					print('<div class="sep-div"><input type="checkbox" ' + (i in item.item_units ? 'checked':'') + ' value="' + i + '" />' + units[i] + '</div>');
					} %>
				</div>
			</td>
			<td class="item-header-varieties">
				<div class="td-div">
					<div class="error item-variety-error"></div>
					<% for(var i in item.item_varieties) { 
					print('<div class="sep-div"><input disabled type="text" class="item-variety-old" data-item-variety-id="' + i + '" defaultValue="' + item.item_varieties[i] + '" value="' + item.item_varieties[i] + '" /><i class="item-variety-edit fa fa-edit" title="Edit Item Variety"></i><i style="display:none" class="item-variety-delete fa fa-trash-o" title="Delete Item Variety"></i></div>');
					} %>
					<i class="add-variety-button fa fa-plus-square-o fa-lg" title="Add Item Variety"></i> 
				</div>
			</td>
			<td class="item-header-edit">
				<div class="td-div">
					<div class="sep-div">
						<% if(new_item == 0) { %>
						<span class="item-row-save"><i class="fa fa-save fa-lg" title="Save Item"></i></span>
						<span class="item-row-delete"><i class="fa fa-trash-o fa-lg" title="Delete Item"></i></span>
						<span class="item-row-cancel"><i class="fa fa-times fa-lg" title="Cancel & Return"></i></span>
						<% } else { %>
						<span class="item-new-row-cancel"><i class="fa fa-trash-o fa-lg" title="Delete Item"></i></span>
						<% } %>
					</div>
					<img style="display:none" class="item-local-loader" src="img/486.gif" />
					<% if(new_item == 0) { %>
					<div class="item-confirm-delete">
						<div class="item-confirm-delete-header">Delete Item ?</div>
						<div class="item-confirm-delete-controls">
							<div class="item-confirm-delete-yes">Yes</div>
							<div class="item-confirm-delete-no">No</div>
						</div>
					</div>
					<% } %>
				</div>
			</td>
		</tr>
	</script>

	<script type="text/template" id="item-row-after-edit-view">
		<tr class="item-row" id="item-<%= item['item_id'] %>" data-item-id="<%= item['item_id'] %>" data-item='<%= JSON.stringify(item) %>'>
			<td class="item-header-name"><%= item['item_name'] %></td>
			<% 
			var units = '';
			for(var i in item.item_units) {
				units += '<div class="sep-div">' + item.item_units[i] + '</div>';
			}
			%>
			<td class="item-header-units"><%= units %></td>
			<% 
			var varieties = '';
			for(var i in item.item_varieties) {
				varieties += '<div class="sep-div">' + item.item_varieties[i] + '</div>';
			}
			%>
			<td class="item-header-varieties"><%= varieties %></td>
			<td class="item-header-edit"><i class="item-edit-button fa fa-edit fa-lg" title="Edit Item"></i></td>
		</tr>
	</script>
<!-- ITEMS : END -->


<!-- SUPPLIERS : START -->
<!-- JS in suppliers.js -->
	<script type="text/template" id="suppliers-view">
		<div id="suppliers-tabs">
			<a id="vendor-suppliers" class="supplier-category-tab" href="#/suppliers/vendor">Vendor</a>
			<a id="retail-suppliers" class="supplier-category-tab" href="#/suppliers/retail">Retail</a>
			<a id="mandi-suppliers" class="supplier-category-tab" href="#/suppliers/mandi">Mandi</a>
		</div>
		<div id="supplier-category-contents"></div>
	</script>

	<script type="text/template" id="suppliers-loader-view">
		<div id="all-suppliers-loader"><img src="img/486.gif" /></div>
	</script>

	<script type="text/template" id="suppliers-category-view">
		<% if(suppliers.length == 0) { %>
		<div id="no-suppliers">No suppliers found</div>
		<table id="all-suppliers-table" style="display:none" data-category-id="<%= category_id %>">
			<tr>
				<th class="supplier-header-name">Name</th>
				<th class="supplier-header-edit"></th>
			</tr>
		</table>
		<% } else { %>
		<div id="no-suppliers" style="display:none">No suppliers found</div>
		<table id="all-suppliers-table" data-category-id="<%= category_id %>">
			<tr>
				<th class="supplier-header-name">Name</th>
				<th class="supplier-header-edit"></th>
			</tr>
			<% _.each(suppliers, function (supplier, index) { %>
			<tr class="supplier-row" id="supplier-<%= supplier['supplier_id'] %>" data-supplier-id="<%= supplier['supplier_id'] %>">
				<td class="supplier-header-name"><%= supplier['supplier_name'] %></td>
				<td class="supplier-header-edit">
					<div class="supplier-header-edit-buttons">
						<span class="supplier-delete-button"><i class="fa fa-trash-o fa-lg" title="Delete Supplier"></i></span>
						<span class="supplier-items-button"><i class="fa fa-leaf fa-lg" title="Show Supplier Items"></i></span>
						<span class="supplier-info-button"><i class="fa fa-info fa-lg" title="Show Supplier Information"></i></span>
					</div>
					<img style="display:none" class="supplier-local-loader" src="img/486.gif" />
					<div class="supplier-confirm-delete">
						<div class="supplier-confirm-delete-header">Delete Supplier ?</div>
						<div class="supplier-confirm-delete-controls">
							<div class="supplier-confirm-delete-yes">Yes</div>
							<div class="supplier-confirm-delete-no">No</div>
						</div>
					</div>
				</td>
			</tr>
			<% }); %>
		</table>
		<% } %>
		<div id="add-save-more-suppliers">
			<div id="add-more-suppliers">Add Supplier</div>
			<div id="save-new-suppliers" data-in-progress="0">Save</div>
		</div>
		<div id="add-save-more-suppliers-error">Errors were found</div>
	</script>

	<script type="text/template" id="new-supplier-view">
		<div class="new-supplier-container">
			<div class="new-supplier-form-input">
				<label>Name</label>
				<input type="text" class="new-supplier-name" />
			</div>
			<div class="new-supplier-form-input">
				<label>Phone</label>
				<input type="text" class="new-supplier-phone" />
			</div>
			<div class="new-supplier-form-input">
				<label>Address</label>
				<textarea class="new-supplier-address"></textarea>
			</div>
			<div class="new-supplier-add-remark">Add Remark</div>
			<div class="new-supplier-error">Error</div>
			<div class="new-supplier-delete"><i class="fa fa-trash-o fa-lg" title="Delete Supplier"></i></div>
		</div>
	</script>

	<script type="text/template" id="supplier-items-view">
		<div id="supplier-all-items" data-supplier-id="<%= supplier_id %>">
			<div id="supplier-all-items-pointer"><i class="fa fa-chevron-right fa-lg"></i></div>
			<div id="supplier-items-table-container">
				<% if(supplier_items.length == 0) { %>
				<div id="no-supplier-items">No items found</div>
				<table id="supplier-items-table" style="display:none" data-supplier-id="<%= supplier_id %>">
					<tr>
						<th class="supplier-items-header-name">Name</th>
						<th class="supplier-items-header-variety">Variety</th>
						<th class="supplier-items-header-unit">Unit</th>
						<th class="supplier-items-header-price">Price</th>
						<th class="supplier-items-header-edit"></th>
					</tr>
				</table>
				<% } else { %>
				<div id="no-supplier-items" style="display:none">No items found</div>
				<table id="supplier-items-table" data-supplier-id="<%= supplier_id %>">
					<tr>
						<th class="supplier-items-header-name">Name</th>
						<th class="supplier-items-header-variety">Variety</th>
						<th class="supplier-items-header-unit">Unit</th>
						<th class="supplier-items-header-price">Price</th>
						<th class="supplier-items-header-edit"></th>
					</tr>
					<% 
					/* For all supplier items */
					for(var i in supplier_items) { 
						var item_rowspan = 0,               // Item rowspan holder [ An item can have many varieties so we need to span rows in the table ]
							this_variety_count,             // Count of an item variety [ An item variety can have many units associated with it ]
							item_varieties_num = [],        // Holds the rowspan of item varieties; eg [2, 0 , 2, 0] => There are 2 varieties each containing 2 units. We need to rowspan both the varieties
							item_varieties_info = [];       // Holds each item variety id and its unit [ {item_variety_id: 101, unit_id: 101}, {item_variety_id: 102, unit_id: 102} ]
						
						/* For all varieties in the item */
						for(var j in supplier_items[i]) {
							// To find the total varieties of the item and the item rowspan
							this_variety_count = Object.keys(supplier_items[i][j]).length;
							item_rowspan += this_variety_count;

							// For each item variety unit push item variety id and the unit
							for(var k in supplier_items[i][j]) {
								item_varieties_info.push({ item_variety_id: j, unit_id: k });
							}

							// Find the rowspan of the variety; Refer to "item_varieties_num"
							item_varieties_num.push(this_variety_count);
							for(var k=0; k<this_variety_count-1; k++) {
								item_varieties_num.push(0);
							}
						}

						// For all total no of varieties in the item
						for(var j=0; j<item_rowspan; j++) {
							print('<tr class="supplier-item-row">');
							
							// The first one can only rowspan the item
							if(j == 0) {
								print('<td class="supplier-items-header-name" rowspan="' + item_rowspan + '">' + ITEMS[i].item_name + '</td>');
							}

							// If rowspan is allowed for the item variety
							if(item_varieties_num[j] != 0) {
								print('<td class="supplier-items-header-variety" rowspan="' + item_varieties_num[j] + '">' + ITEMS[i]['item_varieties'][item_varieties_info[j]['item_variety_id']] + '</td>');
							}
							
							print('<td class="supplier-items-header-units">' + ITEMS[i].item_units[item_varieties_info[j]['unit_id']] + '</td>');
							print('<td class="supplier-items-header-price"><input type="text" defaultValue="' + supplier_items[i][item_varieties_info[j]['item_variety_id']][item_varieties_info[j]['unit_id']]['item_price'] + '" value="' + supplier_items[i][item_varieties_info[j]['item_variety_id']][item_varieties_info[j]['unit_id']]['item_price'] + '" /><div class="supplier-item-parameter-error">Error</div></td>');
							print('<td class="supplier-items-header-edit" data-item-id="' + i + '" data-item-variety-id="' + item_varieties_info[j]['item_variety_id'] + '" data-unit-id="' + item_varieties_info[j]['unit_id'] + '" id="supplier-item-' + i + '-' + item_varieties_info[j]['item_variety_id'] + '-' + item_varieties_info[j]['unit_id'] + '">' + 
									'<i class="fa fa-trash-o fa-lg supplier-item-row-delete" title="Delete Item"></i>' + 
									'<img style="display:none" class="supplier-item-local-loader" src="img/486.gif" />' +
									'<div class="supplier-item-confirm-delete">' + 
										'<div class="supplier-item-confirm-delete-header">Delete ?</div>' + 
										'<div class="supplier-item-confirm-delete-controls">' + 
											'<div class="supplier-item-confirm-delete-yes">Yes</div>' + 
											'<div class="supplier-item-confirm-delete-no">No</div>' +
										'</div>' + 
									'</div>' +
								'</td>');
							
							print('</tr>');
						}
					} %>
				</table>
				<% } %>
			</div>
			<div id="add-save-more-supplier-items">
				<div id="add-more-supplier-items" data-counter="1">Add Item</div>
				<div id="save-new-supplier-items" data-in-progress="0">Save Items</div>
				<div id="close-supplier-items"><i class="fa fa-times fa-lg" title="Close"></i></div>
			</div>
			<div id="item-suggestions"></div>
			<div id="save-supplier-items-prices">Save New Prices</div>
		</div>
	</script>

	<script type="text/template" id="supplier-new-item-view">
		<tr class="supplier-new-item-row supplier-item-row" id="<%= 'new-supplier-item-' + temp_id %>">
			<td class="supplier-items-header-name">
				<div class="supplier-new-item-name-container">
					<input type="text" data-valid="0" />
					<div class="supplier-item-parameter-error">Error</div>
				</div>
			</td>
			<td class="supplier-items-header-variety"></td>
			<td class="supplier-items-header-unit"></td>
			<td class="supplier-items-header-price"></td>
			<td class="supplier-items-header-edit"><i class="supplier-new-item-delete fa fa-trash-o fa-lg" title="Delete Item"></i></td>
		</tr>
	</script>

	<script type="text/template" id="supplier-saved-items-view">
		<% _.each(items, function (item) { 
			if($("#supplier-item-" + item['item_id'] + '-' + item['item_variety_id'] + '-' + item['unit_id']).length == 1) {
				var parent_row = $("#supplier-item-" + item['item_id'] + '-' + item['item_variety_id'] + '-' + item['unit_id']).closest('tr');

				parent_row.find(".supplier-items-header-price input[type='text']").val(item['item_price']).attr('defaultValue', item['item_price']);
			}
			else { %>
				<tr class="supplier-item-row">
					<td class="supplier-items-header-name"><%= ITEMS[item['item_id']].item_name %></td>
					<td class="supplier-items-header-variety"><%= ITEMS[item['item_id']]['item_varieties'][item['item_variety_id']] %></td>
					<td class="supplier-items-header-units"><%= ITEMS[item['item_id']]['item_units'][item['unit_id']] %></td>
					<td class="supplier-items-header-price"><input type="text" defaultValue="<%= item['item_price'] %>" value="<%= item['item_price'] %>" /><div class="supplier-item-parameter-error">Error</div></td>
					<td class="supplier-items-header-edit" data-item-id="<%= item['item_id'] %>" data-item-variety-id="<%= item['item_variety_id'] %>" data-unit-id="<%= item['unit_id'] %>" id="<%= 'supplier-item-' + item['item_id'] + '-' + item['item_variety_id'] + '-' + item['unit_id'] %>">
						<i class="fa fa-trash-o fa-lg supplier-item-row-delete" title="Delete Item">
						<img style="display:none" class="supplier-item-local-loader" src="img/486.gif" />
						<div class="supplier-item-confirm-delete">
							<div class="supplier-item-confirm-delete-header">Delete ?</div> 
							<div class="supplier-item-confirm-delete-controls">
								<div class="supplier-item-confirm-delete-yes">Yes</div> 
								<div class="supplier-item-confirm-delete-no">No</div>
							</div> 
						</div>
					</td>
				</tr>
			<% } 
		}); %>
	</script>

	<script type="text/template" id="supplier-info-view">
		<div id="supplier-info-container" data-supplier-id="<%= supplier['supplier_id'] %>">
			<div id="supplier-info-pointer"><i class="fa fa-chevron-right fa-lg"></i></div>
			<div id="supplier-info">
				<div class="supplier-info-form-input">
					<label>Name</label>
					<input type="text" class="supplier-info-name" defaultValue="<%= supplier['supplier_name'] %>" value="<%= supplier['supplier_name'] %>" />
				</div>
				<div class="supplier-info-form-input">
					<label>Phone</label>
					<input type="text" class="supplier-info-phone" defaultValue="<%= supplier['supplier_phone'] %>" value="<%= supplier['supplier_phone'] %>" />
				</div>
				<div class="supplier-info-form-input">
					<label>Address</label>
					<textarea class="supplier-info-address"  defaultValue="<%= supplier['supplier_address'] %>"><%= supplier['supplier_address'] %></textarea>
				</div>
				<% _.each(supplier['supplier_remarks'], function (remark, index) { %>
				<div class="supplier-info-remark-container" data-remark-id="<%= index %>">
					<textarea class="supplier-info-remark"><%= remark %></textarea>
					<div class="supplier-info-delete-remark"><i class="fa fa-trash-o fa-lg" title="Delete Remark"></i></div>
				</div>
				<% }); %>
				<div class="supplier-info-add-remark">Add Remark</div>
				<div id="save-supplier-info-container">
					<div id="save-supplier-info" data-in-progress="0">Save</div>
					<div id="supplier-info-error">Error</div>
				</div>
			</div>
	</script>
<!-- SUPPLIERS : END -->


<!-- PRICING : START -->
<!-- JS in pricing.js -->
	<script type="text/template" id="pricing-items-loader-view">
		<div id="pricing-items-loader"><img src="img/486.gif" /></div>
	</script>

	<script type="text/template" id="pricing-items-view">
		<div id="pricing-items">
			<% _.each(categories_items, function (category_items, item_category_id) { 
			var item_category = '';
			switch(item_category_id) {
				case '101': 
					item_category = 'Leafy';
					break;

				case '102': 
					item_category = 'Fruits';
					break;

				case '103': 
					item_category = 'Exotic';
					break;

				case '104': 
					item_category = 'OPG';
					break;

				case '105': 
					item_category = 'Vegetables';
			}

			print('<div class="pricing-item-category pricing-item-category-' + item_category_id + '">' + 
						'<div class="pricing-item-category-name">' + item_category + '<input type="checkbox" class="pricing-item-category-check-all" value="' + item_category_id + '" /></div>' +
						'<div class="pricing-item-category-items">');
								_.each(category_items, function (item, item_id) { 
								print('<div class="pricing-item-category-item" data-chosen="0" data-category-id="' + item_category_id + '" data-item-id="' + item_id + '">' + item['item_name'] + '</div>');
								});
						print('</div>' +  
			'</div>');
			}); %>
		</div>
		<div id="send-selected-items-container"> 
			<div id="prices-categories">
				<%
				for(var i in categories_prices) {
					print('<div class="price-category"><input type="checkbox" checked value="' + i + '" />' + categories_prices[i] + '</div>');
				}	
				%>
			</div>
			<div id="send-selected-items" data-in-progress="0">Edit Prices</div>
		</div>
	</script>

	<script type="text/template" id="pricing-items-suppliers-table-view">
		<div id="pricing-items-suppliers-container">
			<div id="pricing-table-options">
				<div id="pricing-table-save-prices" data-in-progress="0">Save</div>
				<div id="pricing-table-excel-container">
					<div id="prepare-excel">Create Excel</div>
					<div id="excel-prices-container">
						<%
						for(var price_category_id in SELECTED_PRICE_CATEGORIES) {
							print('<div class="excel-price-option"><input type="radio" name="excel-main-price" value="' + price_category_id + '" checked />' + SELECTED_PRICE_CATEGORIES[price_category_id] + '</div>');
						}	
						%>
					</div>
					<div id="create-excel" data-in-progress="0">Create</div>
				</div>
			</div>
			<%
			var items_header_table = '<table id="items-header-table">' +
										'<tr>' + 
											'<th class="pricing-table-item-name"><div>Item</div></th>' + 
											'<th class="pricing-table-item-variety"><div>Variety</div></th>' + 
											'<th class="pricing-table-item-unit"><div>Unit</div></th>' +
										'</tr>' + 
									'</table>'; 

			var suppliers_header_table = '<table id="suppliers-header-table"><tr>';
			_.each(suppliers, function (supplier, index) { 
				suppliers_header_table += '<th class="pricing-table-supplier pricing-table-supplier-' + supplier['supplier_category_id'] + '"><div>' + supplier['supplier_name'] + '</div></th>'; 
			});
			suppliers_header_table += '</tr></table>';

			var prices_header_table = '<table id="prices-header-table"><tr>';
			for(var price_category_id in SELECTED_PRICE_CATEGORIES) {
				prices_header_table += '<th class="pricing-table-price" data-price-category-id="' + price_category_id + '"><div>' + SELECTED_PRICE_CATEGORIES[price_category_id] + '</div></th>';
			}	
			prices_header_table += '<th class="pricing-table-edit"><div><input type="checkbox" id="pricing-table-check-all" /></div></th>';
			prices_header_table += '</tr></table>';

			var item_categories = { 101: 'Leafy', 102: 'Fruits', 103: 'Exotic', 104: 'OPG', 105: 'Vegetables' };
			var items_body_table = '<table id="items-body-table">',
				suppliers_body_table = '<table id="suppliers-body-table">',
				prices_body_table = '<table id="prices-body-table">';

			// For all item categories
			for(var category in SELECTED_ITEMS) {
				if(Object.keys(SELECTED_ITEMS[category]).length == 0) {
					continue;
				}

				items_body_table += '<tr><td class="pricing-table-item-category" colspan="3"><div>' + item_categories[category] + '</div></td></tr>';
				suppliers_body_table += '<tr><td colspan="' +  Object.keys(suppliers).length + '"><div>&nbsp;</div></td></tr>';
				prices_body_table += '<tr><td colspan="' + (Object.keys(SELECTED_PRICE_CATEGORIES).length+1) + '"><div>&nbsp;</div></td></tr>';

				// For all items in this category
				for(var i in SELECTED_ITEMS[category]) {
					var item_rowspan = 0,               // Item rowspan holder [ An item can have many varieties so we need to span rows in the table ]
						item_varieties_count,           // Count of an item variety [ An item variety can have many units associated with it ]
						item_varieties_num = [],        // Holds the rowspan of item varieties; eg [2, 0 , 2, 0] => There are 2 varieties each containing 2 units. We need to rowspan both the varieties
						item_varieties_info = [],       // Holds each item variety id and its unit [ {item_variety_id: 101, unit_id: 101}, {item_variety_id: 102, unit_id: 102} ]
						num_item_units = 0;             // No of item units

					item_varieties_count = Object.keys(SELECTED_ITEMS[category][i]['item_varieties']).length;
					num_item_units = Object.keys(SELECTED_ITEMS[category][i]['item_units']).length;
					item_rowspan += item_varieties_count*num_item_units;

					/* For all varieties in the item */
					for(var j in SELECTED_ITEMS[category][i]['item_varieties']) { 
						item_varieties_num.push(num_item_units);
						
						for(var k=0; k<num_item_units-1; k++) {
							item_varieties_num.push(0);
						}

						for(var k in SELECTED_ITEMS[category][i]['item_units']) {
							item_varieties_info.push({ item_variety_id: j, unit_id: k });
						}					
					}	

					for(var j=0; j<item_varieties_num.length; j++) {
						items_body_table += '<tr id="items-body-table-row-' + i + '-' + item_varieties_info[j]['item_variety_id'] + '-' + item_varieties_info[j]['unit_id'] + '" data-item-id="' + i + '" data-item-variety-id="' + item_varieties_info[j]['item_variety_id'] + '" data-unit-id="' + item_varieties_info[j]['unit_id'] + '">';
						suppliers_body_table += '<tr id="suppliers-body-table-row-' + i + '-' + item_varieties_info[j]['item_variety_id'] + '-' + item_varieties_info[j]['unit_id'] + '" data-item-id="' + i + '" data-item-variety-id="' + item_varieties_info[j]['item_variety_id'] + '" data-unit-id="' + item_varieties_info[j]['unit_id'] + '">';
						prices_body_table += '<tr id="prices-body-table-row-' + i + '-' + item_varieties_info[j]['item_variety_id'] + '-' + item_varieties_info[j]['unit_id'] + '" data-item-id="' + i + '" data-item-variety-id="' + item_varieties_info[j]['item_variety_id'] + '" data-unit-id="' + item_varieties_info[j]['unit_id'] + '" data-category-id="' + category + '">';

						// The first one can only rowspan the item
						if(j == 0) {
							items_body_table += '<td class="pricing-table-item-name" rowspan="' + item_rowspan + '"><div>' + SELECTED_ITEMS[category][i].item_name + '</div></td>';
						}

						// If rowspan is allowed for the item variety
						if(item_varieties_num[j] != 0) {
							items_body_table += '<td class="pricing-table-item-variety" rowspan="' + item_varieties_num[j] + '"><div>' + SELECTED_ITEMS[category][i]['item_varieties'][item_varieties_info[j]['item_variety_id']] + '</div></td>';
						}

						items_body_table += '<td class="pricing-table-item-unit"><div>' + SELECTED_ITEMS[category][i].item_units[item_varieties_info[j]['unit_id']] + '</div></td>';

						_.each(suppliers, function (supplier, index) { 
							var supplier_price = '&nbsp',
								supplier_price_ts = '&nbsp'; 
							
							if(supplier['supplier_items'] != '') {
								if(i in supplier['supplier_items']) {
									if(item_varieties_info[j]['item_variety_id'] in supplier['supplier_items'][i]) {
										if(item_varieties_info[j]['unit_id'] in supplier['supplier_items'][i][item_varieties_info[j]['item_variety_id']]) {
											supplier_price = supplier['supplier_items'][i][item_varieties_info[j]['item_variety_id']][item_varieties_info[j]['unit_id']]['item_price'];
											supplier_price_ts = supplier['supplier_items'][i][item_varieties_info[j]['item_variety_id']][item_varieties_info[j]['unit_id']]['ts'];
										}
									}
								}
							}
							
							suppliers_body_table += '<td class="pricing-table-supplier pricing-table-supplier-' + supplier['supplier_category_id'] + '"><div>' + supplier_price + '</div>';
							if(supplier_price_ts != '&nbsp') 
								suppliers_body_table += '<div class="supplier-price-ts timeago" title="' + new Date(supplier_price_ts*1000).toISOString() + '"></div>';
							suppliers_body_table += '</td>';
						});

						var this_price = '';
						for(var price_category_id in SELECTED_PRICE_CATEGORIES) {
							this_price = '';

							if(i in prices) {
								if(item_varieties_info[j]['item_variety_id'] in prices[i]) {
									if(item_varieties_info[j]['unit_id'] in prices[i][item_varieties_info[j]['item_variety_id']]) {
										if(price_category_id in prices[i][item_varieties_info[j]['item_variety_id']][item_varieties_info[j]['unit_id']]) {
											this_price = prices[i][item_varieties_info[j]['item_variety_id']][item_varieties_info[j]['unit_id']][price_category_id]['price'];
										}
									}
								}
							}
							prices_body_table += '<td class="pricing-table-price pricing-table-price-' + price_category_id + '" id="prices-body-table-col-' + i + '-' + item_varieties_info[j]['item_variety_id'] + '-' + item_varieties_info[j]['unit_id'] + '-' + price_category_id + '" data-price-category-id="' + price_category_id + '"><div><input type="text" value="' + this_price + '" defaultValue="' + this_price + '" /></div>';
							if(this_price != '') {
								prices_body_table += '<div class="aalgro-price-ts timeago" title="' + new Date(prices[i][item_varieties_info[j]['item_variety_id']][item_varieties_info[j]['unit_id']][price_category_id]['ts']*1000).toISOString() + '"></div>';
							}
							prices_body_table += '</td>';
						}	

						prices_body_table += '<td class="pricing-table-edit"><div><input type="checkbox" /></div></td>';
						
						items_body_table += '</tr>';
						suppliers_body_table += '</tr>';
						prices_body_table += '</tr>';
					}
				}

				items_body_table += '<tr><td class="pricing-table-item-category" colspan="3"><div>&nbsp;</div></td></tr>';
				suppliers_body_table += '<tr><td colspan="' +  Object.keys(suppliers).length + '"><div>&nbsp;</div></td></tr>';
				prices_body_table += '<tr><td colspan="' + (Object.keys(SELECTED_PRICE_CATEGORIES).length+1) + '"><div>&nbsp;</div></td></tr>';
			}
			items_body_table += '</table>';
			suppliers_body_table += '</table>';
			prices_body_table += '</table>';

			print( '<div id="pricing-table-container">' +
						'<div id="pricing-table-header-container">' +
							'<div id="items-header-table-container">' + items_header_table + '</div>' + 
							'<div id="suppliers-header-table-container">' + suppliers_header_table + '</div>' + 
							'<div id="prices-header-table-container">' + prices_header_table + '</div>' + 
						'</div>' + 
						 '<div id="pricing-table-body-container">' +
							'<div id="items-body-table-container">' + items_body_table + '</div>' + 
							'<div id="suppliers-body-table-container">' + suppliers_body_table + '</div>' + 
							'<div id="prices-body-table-container">' + prices_body_table + '</div>' + 
						'</div>' + 
					'</div>');
			print('<div id="suppliers-scroll-container"><div id="suppliers-scroll"></div></div>');
			%>
		</div>
	</script>
<!-- PRICING : END -->

<!-- CLIENTS : START -->
<!-- JS in clients.js -->
	<script type="text/template" id="clients-view">
		<div id="clients-tabs">
			<a id="existing-clients" class="client-category-tab" href="#/clients/existing">Existing</a>
			<a id="prospective-clients" class="client-category-tab" href="#/clients/prospective">Prospective</a>
		</div>
		<div id="client-category-contents"></div>
	</script>

	<script type="text/template" id="clients-loader-view">
		<div id="all-clients-loader"><img src="img/486.gif" /></div>
	</script>

	<script type="text/template" id="clients-category-view">
		<% if(clients.length == 0) { %>
		<div id="no-clients">No clients found</div>
		<table id="all-clients-table" style="display:none" data-category-id="<%= category_id %>">
			<tr>
				<th class="client-header-name">Name</th>
				<th class="client-header-edit"></th>
			</tr>
		</table>
		<% } else { %>
		<div id="no-clients" style="display:none">No clients found</div>
		<table id="all-clients-table" data-category-id="<%= category_id %>">
			<tr>
				<th class="client-header-name">Name</th>
				<th class="clientclients-header-edit"></th>
			</tr>
			<% _.each(clients, function (client, index) { %>
			<tr class="client-row" id="client-<%= client['client_id'] %>" data-client-id="<%= client['client_id'] %>">
				<td class="client-header-name"><%= client['client_name'] %></td>
				<td class="client-header-edit">
					<div class="client-header-edit-buttons">
						<span class="client-delete-button"><i class="fa fa-trash-o fa-lg" title="Delete Client"></i></span>
						<span class="client-items-button"><i class="fa fa-leaf fa-lg" title="Show Client Items"></i></span>
						<span class="client-info-button"><i class="fa fa-info fa-lg" title="Show Client Information"></i></span>
					</div>
					<img style="display:none" class="client-local-loader" src="img/486.gif" />
					<div class="client-confirm-delete">
						<div class="client-confirm-delete-header">Delete Client ?</div>
						<div class="client-confirm-delete-controls">
							<div class="client-confirm-delete-yes">Yes</div>
							<div class="client-confirm-delete-no">No</div>
						</div>
					</div>
				</td>
			</tr>
			<% }); %>
		</table>
		<% } %>
		<div id="add-save-client">
			<div id="add-client" data-in-progress="0">Add Client</div>
			<div id="cancel-new-client">Cancel</div>
			<div id="save-new-client" data-in-progress="0">Save</div>
		</div>
		<div id="add-save-client-error">Errors were found</div>
	</script>

	<script type="text/template" id="new-client-view">
		<div class="new-client-container">
			<div class="new-client-form-container">
				<div class="new-client-form-input">
					<label>Name *</label>
					<input type="text" class="new-client-name" />
				</div>
				<div class="new-client-form-input">
					<label>Phone</label>
					<input type="text" class="new-client-phone" />
				</div>
				<div class="new-client-form-input">
					<label>Email</label>
					<input type="text" class="new-client-email" />
				</div>
				<div class="new-client-form-input">
					<label>Address</label>
					<textarea class="new-client-address"></textarea>
				</div>
				<div class="new-client-items-container">
					<div class="new-client-items-header">Items *</div>
					<div class="new-client-items-options">
						<div class="new-client-items-option">
							<input type="radio" name="new-client-items-option" value="new" />Create New List
						</div>
						<div class="new-client-items-option">
							<input type="radio" name="new-client-items-option" value="existing" <% print(Object.keys(existing_clients).length == 0 ? 'disabled' : ''); %> />Import from Existing Client
							<select id="new-client-existing-clients">
								<option value="-1">-</option>
							<%
							var html = '';
							for(var i in existing_clients) {
								html += '<option value="' + i + '">' + existing_clients[i] + '</option>';
							}
							print(html);
							%>
							</select>
							<div id="new-client-items-existing-options">
								<div><input type="radio" name="new-client-items-existing-option" value="1" />Only Items</div>
								<div><input type="radio" name="new-client-items-existing-option" value="2" />Both Items & Discount %</div>
							</div>
						</div>
					</div>
				</div>
				<div class="new-client-form-error">Errors were found</div>
			</div>
		</div>
	</script>

	<script type="text/template" id="client-items-view">
		<div id="client-all-items" data-client-id="<%= client_id %>">
			<div id="client-all-items-pointer"><i class="fa fa-chevron-right fa-lg"></i></div>
			<div id="client-items-table-container">
				<% if(client_items.length == 0) { %>
				<div id="no-client-items">No items found</div>
				<table id="client-items-table" style="display:none" data-client-id="<%= client_id %>">
					<tr>
						<th class="client-items-header-name">Name</th>
						<th class="client-items-header-variety">Variety</th>
						<th class="client-items-header-unit">Unit</th>
						<th class="client-items-header-discount">Discount</th>
						<th class="client-items-header-price">Price</th>
						<th class="client-items-header-price-category">Price Type</th>
						<th class="client-items-header-edit"></th>
					</tr>
				</table>
				<% } else { %>
				<div id="no-client-items" style="display:none">No items found</div>
				<table id="client-items-table" data-client-id="<%= client_id %>">
					<tr>
						<th class="client-items-header-name">Name</th>
						<th class="client-items-header-variety">Variety</th>
						<th class="client-items-header-unit">Unit</th>
						<th class="client-items-header-discount">Discount</th>
						<th class="client-items-header-price">Price</th>
						<th class="client-items-header-price-category">Price Type</th>
						<th class="client-items-header-edit"></th>
					</tr>
					<% 
					/* For all client items */
					for(var i in client_items) { 
						var item_rowspan = 0,               // Item rowspan holder [ An item can have many varieties so we need to span rows in the table ]
							this_variety_count,             // Count of an item variety [ An item variety can have many units associated with it ]
							item_varieties_num = [],        // Holds the rowspan of item varieties; eg [2, 0 , 2, 0] => There are 2 varieties each containing 2 units. We need to rowspan both the varieties
							item_varieties_info = [];       // Holds each item variety id and its unit [ {item_variety_id: 101, unit_id: 101}, {item_variety_id: 102, unit_id: 102} ]
						
						/* For all varieties in the item */
						for(var j in client_items[i]) {
							// To find the total varieties of the item and the item rowspan
							this_variety_count = Object.keys(client_items[i][j]).length;
							item_rowspan += this_variety_count;

							// For each item variety unit push item variety id and the unit
							for(var k in client_items[i][j]) {
								item_varieties_info.push({ item_variety_id: j, unit_id: k });
							}

							// Find the rowspan of the variety; Refer to "item_varieties_num"
							item_varieties_num.push(this_variety_count);
							for(var k=0; k<this_variety_count-1; k++) {
								item_varieties_num.push(0);
							}
						}

						// For all total no of varieties in the item
						for(var j=0; j<item_rowspan; j++) {
							print('<tr class="client-item-row">');
							
							// The first one can only rowspan the item
							if(j == 0) {
								print('<td class="client-items-header-name" rowspan="' + item_rowspan + '">' + CLIENTS_ALL_ITEMS[i].item_name + '</td>');
							}

							// If rowspan is allowed for the item variety
							if(item_varieties_num[j] != 0) {
								print('<td class="client-items-header-variety" rowspan="' + item_varieties_num[j] + '">' + CLIENTS_ALL_ITEMS[i]['item_varieties'][item_varieties_info[j]['item_variety_id']] + '</td>');
							}
							
							print('<td class="client-items-header-unit">' + CLIENTS_ALL_ITEMS[i].item_units[item_varieties_info[j]['unit_id']] + '</td>');
							print('<td class="client-items-header-discount"><input type="text" defaultValue="' + client_items[i][item_varieties_info[j]['item_variety_id']][item_varieties_info[j]['unit_id']]['discount'] + '" value="' + client_items[i][item_varieties_info[j]['item_variety_id']][item_varieties_info[j]['unit_id']]['discount'] + '" /><div class="client-item-parameter-error">Error</div></td>');

							var item_price = client_items[i][item_varieties_info[j]['item_variety_id']][item_varieties_info[j]['unit_id']]['item_price'];
							print('<td class="client-items-header-price">');
							if(item_price != '') {
								print(	'<div class="client-items-client-price">' + (item_price-(client_items[i][item_varieties_info[j]['item_variety_id']][item_varieties_info[j]['unit_id']]['discount']*item_price/100)).toFixed(1) + '</div>' + 
										'<div class="client-items-org-price-container">' + 
											'<div class="client-items-org-price">' + item_price + '</div>' +
											'<div class="client-items-price-ts timeago" title="' + new Date(client_items[i][item_varieties_info[j]['item_variety_id']][item_varieties_info[j]['unit_id']]['price_ts']*1000).toISOString() + '"></div>' +
										'</div>');
							}
							print('</td>');

							print('<td class="client-items-header-price-category">' + CLIENTS_ALL_PRICE_CATEGORIES[client_items[i][item_varieties_info[j]['item_variety_id']][item_varieties_info[j]['unit_id']]['price_category_id']] + '</td>');
							
							print('<td class="client-items-header-edit" data-item-id="' + i + '" data-item-variety-id="' + item_varieties_info[j]['item_variety_id'] + '" data-unit-id="' + item_varieties_info[j]['unit_id'] + '" data-price-category-id="' + client_items[i][item_varieties_info[j]['item_variety_id']][item_varieties_info[j]['unit_id']]['price_category_id'] + '" id="client-item-' + i + '-' + item_varieties_info[j]['item_variety_id'] + '-' + item_varieties_info[j]['unit_id'] + '-' + client_items[i][item_varieties_info[j]['item_variety_id']][item_varieties_info[j]['unit_id']]['price_category_id'] + '">' + 
									'<i class="fa fa-trash-o fa-lg client-item-row-delete" title="Delete Item"></i>' + 
									'<img style="display:none" class="client-item-local-loader" src="img/486.gif" />' +
									'<div class="client-item-confirm-delete">' + 
										'<div class="client-item-confirm-delete-header">Delete ?</div>' + 
										'<div class="client-item-confirm-delete-controls">' + 
											'<div class="client-item-confirm-delete-yes">Yes</div>' + 
											'<div class="client-item-confirm-delete-no">No</div>' +
										'</div>' + 
									'</div>' +
								'</td>');
							
							print('</tr>');
						}
					} %>
				</table>
				<% } %>
			</div>
			<div id="client-items-main-error">Error</div>
			<div id="save-new-client-items" data-in-progress="0">Save Items</div>
			<div id="add-save-more-client-items">
				<div id="add-more-client-items" data-counter="1">Add Item</div>
				<div id="client-communicate">
					<div id="client-communicate-button">Communicate</div>
					<div id="client-communicate-options" style="display:none">
						<div><input type="checkbox" checked disabled id="client-communicate-save" />Save Prices</div>
						<div><input type="checkbox" id="client-communicate-email" />Send Email</div>
						<div><input type="checkbox" id="client-communicate-sms" />Send SMS</div>
					</div>
					<div id="client-communicate-proceed" data-in-progress="0" style="display:none">Proceed</div>
				</div>
				<div id="client-items-download-excel-container">
					<div id="client-items-download-excel" data-in-progress="0">Download Excel</div>
				</div>
				<div id="close-client-items"><i class="fa fa-times fa-lg" title="Close"></i></div>
			</div>
			<div id="client-item-suggestions"></div>
			<div id="save-client-items-discounts">Save Discounts</div>
		</div>
	</script>

	<script type="text/template" id="client-new-item-view">
		<tr class="client-new-item-row client-item-row" id="<%= 'new-client-item-' + temp_id %>">
			<td class="client-items-header-name">
				<div class="client-new-item-name-container">
					<input type="text" data-valid="0" />
					<div class="client-item-parameter-error">Error</div>
				</div>
			</td>
			<td class="client-items-header-variety"></td>
			<td class="client-items-header-unit"></td>
			<td class="client-items-header-discount"></td>
			<td class="client-items-header-price"></td>
			<td class="client-items-header-price-category"></td>
			<td class="client-items-header-edit"><i class="client-new-item-delete fa fa-trash-o fa-lg" title="Delete Item"></i></td>
		</tr>
	</script>

	<script type="text/template" id="client-saved-items-view">
		<% _.each(items, function (item) { 
			if($("#client-item-" + item['item_id'] + '-' + item['item_variety_id'] + '-' + item['unit_id'] + '-' + item['price_category_id']).length == 1) {
				var parent_row = $("#client-item-" + item['item_id'] + '-' + item['item_variety_id'] + '-' + item['unit_id'] + '-' + item['price_category_id']).closest('tr');

				parent_row.find(".client-items-header-price input[type='text']").val(item['item_price']).attr('defaultValue', item['item_price']);
			}
			else { %>
				<tr class="client-item-row">
					<td class="client-items-header-name"><%= CLIENTS_ALL_ITEMS[item['item_id']].item_name %></td>
					<td class="client-items-header-variety"><%= CLIENTS_ALL_ITEMS[item['item_id']]['item_varieties'][item['item_variety_id']] %></td>
					<td class="client-items-header-units"><%= CLIENTS_ALL_ITEMS[item['item_id']]['item_units'][item['unit_id']] %></td>
					<td class="client-items-header-discount"><input type="text" defaultValue="<%= item['discount'] %>" value="<%= item['discount'] %>" /><div class="client-item-parameter-error">Error</div></td>
					
					<td class="client-items-header-price">
					<%
					if(item['item_price'] != '') {
						print(	'<div class="client-items-client-price">' + (item['item_price']-(item['discount']*item['item_price']/100)).toFixed(1) + '</div>' + 
								'<div class="client-items-org-price-container">' + 
									'<div class="client-items-org-price">' + item['item_price'] + '</div>' +
									'<div class="client-items-price-ts timeago" title="' + new Date(item['price_ts']*1000).toISOString() + '"></div>' +
								'</div>');
					}
					%>
					</td>

					<td class="client-items-header-price-category"><%= CLIENTS_ALL_PRICE_CATEGORIES[item['price_category_id']] %></td>
					<td class="client-items-header-edit" data-item-id="<%= item['item_id'] %>" data-item-variety-id="<%= item['item_variety_id'] %>" data-unit-id="<%= item['unit_id'] %>" data-price-category-id="<%= item['price_category_id'] %>" id="<%= 'client-item-' + item['item_id'] + '-' + item['item_variety_id'] + '-' + item['unit_id'] + '-' + item['price_category_id'] %>">
						<i class="fa fa-trash-o fa-lg client-item-row-delete" title="Delete Item">
						<img style="display:none" class="client-item-local-loader" src="img/486.gif" />
						<div class="client-item-confirm-delete">
							<div class="client-item-confirm-delete-header">Delete ?</div> 
							<div class="client-item-confirm-delete-controls">
								<div class="client-item-confirm-delete-yes">Yes</div> 
								<div class="client-item-confirm-delete-no">No</div>
							</div> 
						</div>
					</td>
				</tr>
			<% } 
		}); %>
	</script>

	<script type="text/template" id="client-info-view">
		<div id="client-info-container" data-client-id="<%= client['client_id'] %>">
			<div id="client-info-pointer"><i class="fa fa-chevron-right fa-lg"></i></div>
			<div id="client-info">
				<div class="client-info-form-input">
					<label>Name</label>
					<input type="text" class="client-info-name" defaultValue="<%= client['client_name'] %>" value="<%= client['client_name'] %>" />
				</div>
				<div class="client-info-form-input">
					<label>Phone</label>
					<input type="text" class="client-info-phone" defaultValue="<%= client['client_phone'] %>" value="<%= client['client_phone'] %>" />
				</div>
				<div class="client-info-form-input">
					<label>Email</label>
					<input type="text" class="client-info-email" defaultValue="<%= client['client_email'] %>" value="<%= client['client_email'] %>" />
				</div>
				<div class="client-info-form-input">
					<label>Address</label>
					<textarea class="client-info-address"  defaultValue="<%= client['client_address'] %>"><%= client['client_address'] %></textarea>
				</div>
				<div id="save-client-info-container">
					<div id="save-client-info" data-in-progress="0">Save</div>
					<div id="client-info-error">Error</div>
				</div>
			</div>
	</script>
<!-- CLIENTS : END -->

<script type="text/javascript" src="js/lib/jquery-2.0.3.min.js"></script>
<script type="text/javascript" src="js/lib/underscore-1.5.1.min.js"></script>
<script type="text/javascript" src="js/lib/backbone-1.0.0.min.js"></script>
<script type="text/javascript" src="js/lib/jquery.jscrollpane.min.js"></script>
<script type="text/javascript" src="js/lib/jquery.mousewheel.js"></script>
<script type="text/javascript" src="js/lib/jquery.timeago.js"></script>
<script type="text/javascript" src="js/items.js"></script>
<script type="text/javascript" src="js/suppliers.js"></script>
<script type="text/javascript" src="js/pricing.js"></script>
<script type="text/javascript" src="js/clients.js"></script>
<script type="text/javascript" src="js/index.js"></script>

</body>
</html>

