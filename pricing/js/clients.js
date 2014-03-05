var clients_view,
	clients_loader_view,
	clients_category_view,
	client_info_view,
	client_items_view,
	new_client_view,
	client_new_item_view,
	client_saved_items_view;

var CLIENTS_ALL_ITEMS,
	CLIENTS_ALL_PRICE_CATEGORIES;


/** 
View Id : clients-view 
Show the 2 client categories - "Existing" & "Prospective"
*/
var ClientsView = Backbone.View.extend({
	el: '#contents',
	render: function () {
		var template = _.template($("#clients-view").html());
		this.$el.html(template);

		$("#home-tabs a").removeClass('home-tab-active');
		$("#clients-tab").addClass('home-tab-active');
	}
});


/** 
View Id : clients-loader-view 
Send an ajax to get clients of a category
*/
var ClientsLoaderView = Backbone.View.extend({
	el: '#client-category-contents',
	render: function () {
		var template = _.template($("#clients-loader-view").html());
		this.$el.html(template);
		
		$(".client-category-tab").removeClass('client-category-tab-active');
		$("#" + this.options['category_name'] + "-clients").addClass('client-category-tab-active');

		Backbone.ajax({
			type: 'GET',
			url: 'controller.php',
			data: { command: 'GetClientsByCategory', category_id: this.options['category_id'] },
			dataType: 'json',
			success: function (response) {
				$("#all-clients-loader").hide();
				
				/* On no error show the clients */
				if(response.error == 0) {
					clients_category_view = new ClientsCategoryView({ 
																	clients: response.data.clients,                  
																	category_id: response.data.category_id           
																});
					clients_category_view.render();
				}
			}
		});
	}
});


/** 
View Id : clients-category-view 
Show the clients of the category
*/
var ClientsCategoryView = Backbone.View.extend({
	el: '#client-category-contents',
	render: function () {
		var template = _.template(
								$("#clients-category-view").html(), { 
									clients: this.options['clients'],             
									category_id: this.options['category_id']           
								});
		this.$el.html(template);
		$("#add-save-client, #add-save-client-error").outerWidth($("#all-clients-table").width());
	},
	events: {
		'click .client-delete-button': 'deleteClient',                   
		'click .client-confirm-delete-no': 'cancelDelete',                  
		'click .client-confirm-delete-yes': 'proceedDelete',
		'click #add-client': 'addClient',
		'click #cancel-new-client': 'cancelNewClient',
		'click input[name="new-client-items-option"]': 'itemsCreateSelection',
		'change #new-client-existing-clients': 'existingClientSelection',
		'click #save-new-client': 'saveNewClient',
		'click .client-items-button': 'clientItems',
		'click .client-info-button': 'clientInfo',
	},
	deleteClient: function (e) {
		var parent_td = $(e.currentTarget).closest('.client-header-edit');
		parent_td.find('.client-confirm-delete').show();
	}, 
	cancelDelete: function (e) {
		$(e.currentTarget).closest('.client-confirm-delete').hide();
	},
	proceedDelete: function (e) {
		var parent_td = $(e.currentTarget).closest('td'),
			parent_row = $(e.currentTarget).closest('tr'),
			client_id = parent_row.attr('data-client-id');

		parent_td.find('.client-header-edit-buttons').hide();
		parent_td.find('.client-confirm-delete').hide();
		parent_td.find('.client-local-loader').show();
		
		Backbone.ajax({
			type: 'GET',
			url: 'controller.php',
			data: { command: 'DeleteClient', client_id: client_id },
			dataType: 'json',
			success: function (response) {
				parent_td.find('.client-header-edit-buttons').show();
				parent_td.find('.client-local-loader').hide();
				
				if(response.error == 0) {
					parent_row.remove();
					
					if($(".client-row").length == 0) {
						$("#all-clients-table").hide();
						$("#no-clients").show();
					}

					if($("#client-info-container").is(':visible')) {
						if($("#client-info-container").attr('data-client-id') == client_id) {
							$("#client-info-container").remove();
							if(typeof client_info_view != 'undefined') {
								client_info_view.undelegateEvents();
							}
						}
					}

					if($("#client-all-items").is(':visible')) {
						if($("#client-all-items").attr('data-client-id') == client_id) {
							$("#client-all-items").remove();
							if(typeof client_items_view != 'undefined') {
								client_items_view.undelegateEvents();
							}
						}
					}
				}
			}
		});
	},
	addClient: function () {
		if($("#add-client").attr('data-in-progress') == '1') {
			return;
		}

		var existing_clients = {};
		$(".client-row").each(function () {
			existing_clients[$(this).attr('data-client-id')] = $(this).find(".client-header-name").text();
		});

		new_client_view = new NewClientView({ 
												category_id: $("#all-clients-table").attr('data-category-id'),
												existing_clients: existing_clients           
											});
		new_client_view.render();

		$("#add-client").hide();
		$("#cancel-new-client, #save-new-client").show();
	},
	cancelNewClient: function () { 
		$(".new-client-container").remove();
		if(typeof new_client_view != 'undefined') {
			new_client_view.undelegateEvents();
		}

		$("#add-client").show();
		$("#cancel-new-client, #save-new-client").hide();
	},
	itemsCreateSelection: function () { 
		if($("input[name='new-client-items-option']:checked").val() == 'new') {
			$("#new-client-existing-clients").hide();
			$("#new-client-items-existing-options").hide();
		}
		else if($("input[name='new-client-items-option']:checked").val() == 'existing') {
			$("#new-client-existing-clients").show();
		} 
	},
	existingClientSelection: function () {
		if($("#new-client-existing-clients").val() == -1) {
			$("#new-client-items-existing-options").hide();
		}
		else {
			$("#new-client-items-existing-options input[type='radio']").prop('checked', false);
			$("#new-client-items-existing-options").show();
		}
	},
	saveNewClient: function () {
		if($("#add-client").attr('data-in-progress') == '1') {
			return;
		}

		var blank_reg_exp = /^([\s]{0,}[^\s]{1,}[\s]{0,}){1,}$/,
			email_reg_exp = /^([a-zA-z0-9]{1,}(?:([\._-]{0,1}[a-zA-Z0-9]{1,}))+@{1}([a-zA-Z0-9-]{2,}(?:([\.]{1}[a-zA-Z]{2,}))+))$/,
			digits_reg_exp = /^[0-9]{1,}$/,
			error = 0,
			data = {},
			that;

		$(".new-client-wrong-parameter").removeClass('new-client-wrong-parameter');
		$(".new-client-form-error").hide();

		if(!blank_reg_exp.test($(".new-client-name").val())) {
			$(".new-client-name").prev().addClass('new-client-wrong-parameter');
			error = 1;
		}

		if(blank_reg_exp.test($(".new-client-phone").val())) {
			if(!digits_reg_exp.test($(".new-client-phone").val())) {
				$(".new-client-phone").prev().addClass('new-client-wrong-parameter');
				error = 1;
			}
		}

		if(blank_reg_exp.test($(".new-client-email").val())) {
			if(!email_reg_exp.test($(".new-client-email").val())) {
				$(".new-client-email").prev().addClass('new-client-wrong-parameter');
				error = 1;
			}
		}

		if(!$("input[name='new-client-items-option']").is(':checked')) { 
			$(".new-client-items-header").addClass('new-client-wrong-parameter');
			error = 1;	
		}

		if($("input[name='new-client-items-option']:checked").val() == 'existing') {
			if($("#new-client-existing-clients").val() == '-1') { 
				$(".new-client-items-header").addClass('new-client-wrong-parameter');
				error = 1;	
			}
			else { 
				if(!$("input[name='new-client-items-existing-option']").is(':checked')) { 
					$(".new-client-items-header").addClass('new-client-wrong-parameter');
					error = 1;	
				}
			}
		}
		
		if(error == 1) {
			$(".new-client-form-error").show();
			return;
		} 

		data.client_name = $(".new-client-name").val();
		data.phone = $(".new-client-phone").val();
		data.email = $(".new-client-email").val();
		data.address = $(".new-client-address").val();
		data.items_option = $("input[name='new-client-items-option']:checked").val();
		if(data.items_option == 'existing') {
			data.client_id = $("#new-client-existing-clients").val();
			data.import_type = $("input[name='new-client-items-existing-option']:checked").val();
		}

		that = this;
		$("#save-new-client").css('opacity', '0.5').attr('data-in-progress', '1');
		$("#cancel-new-client").hide();
		Backbone.ajax({
			type: 'POST',
			url: 'controller.php',
			data: { command: 'AddClient', data: data, category_id: $("#all-clients-table").attr('data-category-id') },
			dataType: 'json',
			success: function (response) {
				$("#save-new-client").css('opacity', '1').attr('data-in-progress', '0');
				$("#cancel-new-client").show();

				if(response.error == 0) {
					var html = '<tr class="client-row" id="client-' + response.data.client_id + '" data-client-id="' + response.data.client_id + '">' + 
									'<td class="client-header-name">'+ response.data.client_name + '</td>' + 
									'<td class="client-header-edit">' + 
										'<div class="client-header-edit-buttons">' + 
											'<span class="client-delete-button"><i class="fa fa-trash-o fa-lg" title="Delete Client"></i></span>' + 
											'<span class="client-items-button"><i class="fa fa-leaf fa-lg" title="Show Client Items"></i></span>' + 
											'<span class="client-info-button"><i class="fa fa-info fa-lg" title="Show Client Information"></i></span>' + 
										'</div>' + 
										'<img style="display:none" class="client-local-loader" src="img/486.gif" />' + 
										'<div class="client-confirm-delete">' + 
											'<div class="client-confirm-delete-header">Delete client ?</div>' + 
											'<div class="client-confirm-delete-controls">' + 
												'<div class="client-confirm-delete-yes">Yes</div>' + 
												'<div class="client-confirm-delete-no">No</div>' + 
											'</div>' + 
										'</div>' + 
									'</td>' + 
								'</tr>';

					if($(".client-row").length == 0) {
						$("#all-clients-table").show();
						$("#no-clients").hide();	
					}

					$("#all-clients-table").append(html);

					that.cancelNewClient();
				}
			}
		});
	},
	clientItems: function(e) {
		var parent_row = $(e.currentTarget).closest('.client-row');
		parent_row.find('.client-header-edit-buttons, .client-confirm-delete').hide();
		parent_row.find('.client-local-loader').show();

		Backbone.ajax({
			type: 'GET',
			url: 'controller.php',
			data: { command: 'GetClientItems', client_id: parent_row.attr('data-client-id') },
			dataType: 'json',
			success: function (response) {
				parent_row.find('.client-header-edit-buttons').show();
				parent_row.find('.client-local-loader').hide();
				
				if(response.error == 0) {
					CLIENTS_ALL_ITEMS = response.data.items;
					CLIENTS_ALL_PRICE_CATEGORIES = response.data.price_categories;

					var offset = parent_row.find('.client-header-edit').offset();  
					offset.left = offset.left + $('.client-header-edit').outerWidth() + 20;
					
					$("#client-all-items").remove();
					if(typeof client_items_view != 'undefined') {
						client_items_view.undelegateEvents();
					}

					$("#client-info-container").remove();
					if(typeof client_info_view != 'undefined') {
						client_info_view.undelegateEvents();
					}

					client_items_view = new ClientItemsView({ 
																client_items: response.data.client_items,                 
																client_id: parent_row.attr('data-client-id'),             
																offset: offset  
															});
					client_items_view.render(); 
				}
			}
		});
	},
	clientInfo: function (e) {
		// Hide buttons & client delete confirmation; Show loader
		var parent_row = $(e.currentTarget).closest('.client-row');
		parent_row.find('.client-header-edit-buttons, .client-confirm-delete').hide();
		parent_row.find('.client-local-loader').show();

		// Make an ajax
		Backbone.ajax({
			type: 'GET',
			url: 'controller.php',
			data: { command: 'GetClientInfo', client_id: parent_row.attr('data-client-id') },
			dataType: 'json',
			success: function (response) {
				// Show buttons; hide loader
				parent_row.find('.client-header-edit-buttons').show();
				parent_row.find('.client-local-loader').hide();
				
				// On no error
				if(response.error == 0) {
					// Offset.left of the parent row [ client row ] + width of the client buttons td + 20  
					var offset = parent_row.find('.client-header-edit').offset();  
					offset.left = offset.left + $('.client-header-edit').outerWidth() + 20;
					
					// Remove Client Info container & Client Info View
					$("#client-info-container").remove();
					if(typeof client_info_view != 'undefined') {
						client_info_view.undelegateEvents();
					}
					
					// Remove Client Items container & Client Items View
					$("#client-all-items").remove();
					if(typeof client_items_view != 'undefined') {
						client_items_view.undelegateEvents();
					}

					// Render the Client Info View
					client_info_view = new ClientInfoView({ 
															client: response.data,                 // client Info
															offset: offset                         // Offset to display the client Info container
														});
					client_info_view.render(); 
				}
			}
		});
	}
});



/** 
View Id : new-client-view 
Show the new client container
*/
var NewClientView = Backbone.View.extend({
	el: '#add-save-client',
	render: function () {
		var template = _.template(
								$("#new-client-view").html(), {
									price_categories: this.options['price_categories'],
									client_category_id: this.options['category_id'],
									existing_clients: this.options['existing_clients']
								});
		$(template).insertBefore(this.$el);
		
		$(".new-client-container").outerWidth($("#all-clients-table").width());
	}
});



/** 
View Id : client-items-view 
Show the client's items
*/
var ClientItemsView = Backbone.View.extend({
	el: '#contents',
	render: function () {
		var template = _.template(
								$("#client-items-view").html(), { 
									client_items: this.options['client_items'],              
									client_id:  this.options['client_id']
								});
		this.$el.append(template);
		$(".timeago").timeago();
		
		// Set offset of the client items table, Add & Save Client Item, Close Client Items, Client Communicate & Save Discounts accordingly
		$("#client-all-items").css({ left: this.options['offset'].left, top: this.options['offset'].top });
		$("#save-new-client-items").css('left', ($('#client-items-table-container').position().left-1) + 'px');
		$("#client-items-main-error").css('left', ($('#client-items-table-container').position().left-1) + 'px');
		$("#add-save-more-client-items").css('left', ($('#client-items-table-container').position().left-1) + 'px');
		$("#close-client-items").css('margin-right', ($('#client-items-table-container').position().left-1) + 'px');
		$("#client-communicate").css('left', ($('#client-items-table-container').position().left-1) + 'px');
		$("#save-client-items-discounts").css({ 'left': (this.options['offset'].left + 30 + (100 + 100 + 80 + 70 + 60 + 80 + 100) + 30) + 'px', top: this.options['offset'].top });
	},
	events: {
		'keyup .client-item-row .client-items-header-discount input[type="text"]': 'showDiscountedPrices',
		'click #save-client-items-discounts': 'saveDiscounts',  
		'click .client-item-row-delete': 'deleteItem', 
		'click .client-item-confirm-delete-no': 'cancelDelete', 
		'click .client-item-confirm-delete-yes': 'proceedDelete', 
		'click #add-more-client-items': 'addItem',
		'click #save-new-client-items': 'saveNewItems', 
		'click .client-new-item-delete': 'deleteNewItem',
		'keyup .client-new-item-row .client-items-header-name input[type="text"]': 'showSuggestions',  
		'click .client-item-suggestion': 'chooseSuggestion', 
		'click #close-client-items': 'closeClientItems',
		'click #client-communicate-button': 'showCommunicationOptions',
		'click #client-communicate-proceed': 'communicateWithClient',
		'click #client-items-download-excel': 'downloadExcel'
	},
	showDiscountedPrices: function (e) {
		if(!$(e.currentTarget).closest('tr').hasClass('client-new-item-row')) {
			var parent_tr = $(e.currentTarget).closest('tr'),
					discount = parseFloat($(e.currentTarget).val()).toFixed(2),
					discount_reg_exp = /^((\+|\-){0,1}([\d]+\.{0,1}|[\d]*\.{0,1}[\d]+){1})$/;
				
			$(e.currentTarget).next().hide();

			if(!discount_reg_exp.test($(e.currentTarget).val())) {
				$(e.currentTarget).next().show();
				$(e.currentTarget).removeClass('client-item-edited-discount');
				return;
			}

			if(parent_tr.find('.client-items-header-price').text() != '') {
				var item_price = parent_tr.find('.client-items-header-price .client-items-org-price').text();
				parent_tr.find('.client-items-header-price .client-items-client-price').text((item_price-(discount*item_price/100)).toFixed(1));
			}

			if($(e.currentTarget).val() != $(e.currentTarget).attr('defaultValue')) {
				$(e.currentTarget).addClass('client-item-edited-discount');
			}
			else {
				$(e.currentTarget).removeClass('client-item-edited-discount');	
			}

			if($(".client-item-edited-discount").length == 0) {
				$("#save-client-items-discounts").hide();
			}
			else {
				$("#save-client-items-discounts").show();
			}
		}
	}, 
	saveDiscounts: function (e) {
		if($("#save-client-items-discounts").attr('data-in-progress') == '1') {
			return;
		}

		var discount_reg_exp = /^((\+|\-){0,1}([\d]+\.{0,1}|[\d]*\.{0,1}[\d]+){1})$/,                                                    
			error = 0,                                                                      
			edited_items = [];                                                              

		$(".client-item-edited-discount").each(function () {
			if(!discount_reg_exp.test($(this).val())) {
				$(this).next().show();
				error = 1;
			}
		});

		if(error == 1) {
			return;
		}

		$(".client-item-edited-discount").each(function () {
			var next_td = $(this).closest('td').next().next().next();

			edited_items.push({ item_id: next_td.attr('data-item-id'), item_variety_id: next_td.attr('data-item-variety-id'), unit_id: next_td.attr('data-unit-id'), price_category_id: next_td.attr('data-price-category-id'), discount: $(this).val() });
		});

		$("#save-client-items-discounts").text('Saving ..').css('opacity', '0.5').attr('data-in-progress', '1');
		Backbone.ajax({
			type: 'POST',
			url: 'controller.php',
			data: { command: 'EditClientItemsDiscounts', items: edited_items, client_id: $("#client-items-table").attr('data-client-id') },
			dataType: 'json',
			success: function (response) {
				$("#save-client-items-discounts").text('Save Discounts').css('opacity', '1').attr('data-in-progress', '0').hide();

				if(response.error == 0) {
					for(var i=0; i<response.data.length; i++) {
						var parent_row = $("#client-item-" + response.data[i]['item_id'] + '-' + response.data[i]['item_variety_id'] + '-' + response.data[i]['unit_id'] + '-' + response.data[i]['price_category_id']).closest('tr');

						parent_row.find(".client-items-header-discount input[type='text']").val(response.data[i]['discount']).attr('defaultValue', response.data[i]['discount']).removeClass('client-item-edited-discount');
					}
				}
			}
		});
	},
	deleteItem: function (e) {
		var parent_td = $(e.currentTarget).closest('.client-items-header-edit');
		parent_td.find('.client-item-confirm-delete').show();
	}, 
	cancelDelete: function (e) {
		$(e.currentTarget).closest('.client-item-confirm-delete').hide();
	}, 
	proceedDelete: function (e) {
		var parent_row = $(e.currentTarget).closest('.client-item-row');
		var parent_td = $(e.currentTarget).closest('.client-items-header-edit');

		parent_td.find('.client-item-edit-controls').hide();
		parent_td.find('.client-item-confirm-delete').hide();
		parent_td.find('.client-item-local-loader').show();
		
		Backbone.ajax({
			type: 'POST',
			url: 'controller.php',
			data: { command: 'DeleteClientItem', client_id: $("#client-items-table").attr('data-client-id'), item_id: parent_td.attr('data-item-id'), item_variety_id: parent_td.attr('data-item-variety-id'), unit_id: parent_td.attr('data-unit-id'), price_category_id: parent_td.attr('data-price-category-id') },
			dataType: 'json',
			success: function (response) {
				parent_td.find('.client-item-edit-controls').show();
				parent_td.find('.client-item-local-loader').hide();
				
				if(response.error == 0) {
					parent_row.find(".client-items-header-discount, .client-items-header-price").html('');
					parent_row.find(".client-items-header-edit").html('DELETED');
				}
			}
		});
	},
	addItem: function () {
		if($(".client-item-row").length == 0) {
			$("#no-client-items").hide();
			$("#client-items-table").show();
		}

		client_new_item_view = new ClientNewItemView();
		client_new_item_view.render();

		$("#save-new-client-items").show();
	},
	deleteNewItem : function (e) {
		var parent_row = $(e.currentTarget).closest('tr');

		if(parent_row.attr('id') == $("#item-suggestions").attr('data-row-id')) {
			$("#item-suggestions").hide();
		}
		parent_row.remove();

		if($(".client-item-row").length == 0) {
			$("#no-client-items").show();
			$("#client-items-table").hide();
		}

		if($(".client-new-item-row").length == 0) {
			$("#save-new-client-items").hide();		
		}
	},
	saveNewItems: function (e) {
		var discount_reg_exp = /^((\+|\-){0,1}([\d]+\.{0,1}|[\d]*\.{0,1}[\d]+){1})$/,
			error = 0,
			new_item = {},
			new_items = [];

		$("#item-suggestions").hide();
		$("#client-items-table").find('.client-new-item-row .client-item-parameter-error').hide();

		$("#client-items-table").find('.client-new-item-row').each(function () {
			new_item = {};

			if($(this).find(".client-items-header-name input[type='text']").attr('data-valid') == '0') {
				$(this).find(".client-items-header-name .client-item-parameter-error").show();
				error = 1;
			}
			else {
				new_item.item_id = $(this).find(".client-items-header-name input[type='text']").attr('data-item-id'); 
				new_item.item_variety_id = $(this).find(".client-items-header-variety select").val(); 
				new_item.unit_id = $(this).find(".client-items-header-unit select").val(); 
				new_item.discount = $(this).find(".client-items-header-discount input[type='text']").val();
				new_item.price_category_id = $(this).find(".client-items-header-price-category select").val(); 
				new_items.push(new_item);
				
				if(!discount_reg_exp.test($(this).find(".client-items-header-discount input[type='text']").val())) {
					$(this).find(".client-items-header-discount .client-item-parameter-error").show();
					error = 1;
				}
			}
		});

		if(error == 1) {
			return;
		}

		$("#add-more-client-items").hide();
		$("#save-new-client-items").css('opacity', '0.5').text('Saving ..').attr('data-in-progress', '1');
		Backbone.ajax({
			type: 'POST',
			url: 'controller.php',
			data: { command: 'AddClientItems', client_id: $("#client-items-table").attr('data-client-id'), new_items: new_items },
			dataType: 'json',
			success: function (response) {
				$("#add-more-client-items").show();
				$("#save-new-client-items").css('opacity', '1').text('Save Items').attr('data-in-progress', '0');;
				
				if(response.error == 0) {
					$("#save-new-client-items").hide();
					$('#client-items-table .client-new-item-row').remove();
					
					client_saved_items_view = new ClientSavedItemsView({            
																	items: response.data       
																});
					client_saved_items_view.render();
				}
			}
		});
	},
	showSuggestions: function (e) { 
		var item_query = $(e.currentTarget).val().toLowerCase(),
			suggestions = [],
			html = '',
			blank_reg_exp = /^([\s]{0,}[^\s]{1,}[\s]{0,}){1,}$/,
			parent_row = $(e.currentTarget).closest('tr');

		$(e.currentTarget).attr('data-valid', '0');
		parent_row.find('.client-items-header-variety, .client-items-header-unit, .client-items-header-price, .client-items-header-max-qty, .client-items-header-min-qty').html(html);

		$("#client-item-suggestions").css({ left: parent_row.find(".client-items-header-variety").position().left + 1, top: parent_row.find(".client-items-header-variety").position().top + 1 });
		$("#client-item-suggestions").width(parent_row.find(".client-items-header-variety").outerWidth() + parent_row.find(".client-items-header-unit").outerWidth() + parent_row.find(".client-items-header-discount").outerWidth() + parent_row.find(".client-items-header-price").outerWidth() + parent_row.find(".client-items-header-price-category").outerWidth() - 2);
		$("#client-item-suggestions").height(parent_row.find(".client-items-header-variety").outerHeight() - 2);

		if(!blank_reg_exp.test(item_query)) { 
			$("#client-item-suggestions").hide();
			return;
		}

		for(var i in CLIENTS_ALL_ITEMS) {
			if(CLIENTS_ALL_ITEMS[i]['item_name'].toLowerCase().indexOf(item_query) != -1) {
				suggestions.push({ name: CLIENTS_ALL_ITEMS[i]['item_name'], id: i });
			}

			if(suggestions.length == 3) {
				break;
			}
		}

		if(suggestions.length == 0) {
			html += '<div id="suggestions-no-items">No such items</div>';
		}
		else { 
			for(var i=0; i<suggestions.length; i++) {
				html += '<div class="client-item-suggestion" data-item-id="' + suggestions[i]['id'] + '">' + suggestions[i]['name'] + '</div>';
			}
		}

		$("#client-item-suggestions").attr('data-row-id', parent_row.attr('id')).html(html).show();
	},
	chooseSuggestion: function (e) { 
		$("#client-item-suggestions").hide();

		var item_name = CLIENTS_ALL_ITEMS[$(e.currentTarget).attr('data-item-id')]['item_name'],
			item_varieties = CLIENTS_ALL_ITEMS[$(e.currentTarget).attr('data-item-id')]['item_varieties'],
			item_units = CLIENTS_ALL_ITEMS[$(e.currentTarget).attr('data-item-id')]['item_units'],
			html = '';

		$("#" + $(e.currentTarget).parent().attr('data-row-id')).find('.client-items-header-name input[type="text"]').val(CLIENTS_ALL_ITEMS[$(e.currentTarget).attr('data-item-id')]['item_name']).attr('data-valid', '1').attr('data-item-id', $(e.currentTarget).attr('data-item-id'));
		
		html = '<select>';
		for(var i in item_varieties) {
			if(item_varieties[i] == '') 
				html += '<option value="' + i + '" selected>[ NONE ]</option>';
			else
				html += '<option value="' + i + '">' + item_varieties[i] + '</option>';
		}
		html +='</select>';
		$("#" + $(e.currentTarget).parent().attr('data-row-id')).find('.client-items-header-variety').html(html);

		html = '<select>';
		for(var i in item_units) {
			html += '<option value="' + i + '">' + item_units[i] + '</option>';
		}
		html +='</select>';
		$("#" + $(e.currentTarget).parent().attr('data-row-id')).find('.client-items-header-unit').html(html); 

		html = '<select>';
		for(var i in CLIENTS_ALL_PRICE_CATEGORIES) {
			html += '<option value="' + i + '">' + CLIENTS_ALL_PRICE_CATEGORIES[i] + '</option>';
		}
		html +='</select>';
		$("#" + $(e.currentTarget).parent().attr('data-row-id')).find('.client-items-header-price-category').html(html); 

		html = '<input type="text" value="0.00" />';
		html += '<div class="client-item-parameter-error">Error</div>';
		$("#" + $(e.currentTarget).parent().attr('data-row-id')).find('.client-items-header-discount').html(html);
	},
	closeClientItems: function () {
		$("#client-all-items").hide();
	},
	showCommunicationOptions: function () {
		if($(".client-item-edited-discount").length > 0) {
			$("#client-items-main-error").text('Error : Please save the edited discounts first').show();
			setTimeout(function () {
				$("#client-items-main-error").slideUp('slow');
			}, 3000);

			return;
		}

		$("#client-communicate-button").hide();

		$("#client-communicate-options, #client-communicate-proceed").show();
	},
	communicateWithClient: function () {
		if($("#client-communicate-proceed").attr('data-in-progress') == '1') {
			return;
		}

		$("#client-communicate-proceed").text('Processing ..').css('opacity', '0.5').attr('data-in-progress', '1');
		Backbone.ajax({
			type: 'POST',
			url: 'controller.php',
			data: { command: 'ClientCommunication', send_email: +($("#client-communicate-email").is(':checked')), send_sms: 0, client_id: $("#client-items-table").attr('data-client-id') },
			dataType: 'json',
			success: function (response) {
				$("#client-communicate-proceed").text('Proceed').css('opacity', 1).attr('data-in-progress', '0');

				if(response.error == 0) {
					
				}
			}
		});
	},
	downloadExcel: function () {
		if($("#client-items-download-excel").attr('data-in-progress') == '1') {
			return;
		}

		if($(".client-item-edited-discount").length > 0) {
			$("#client-items-main-error").text('Error : Please save the edited discounts first').show();
			setTimeout(function () {
				$("#client-items-main-error").slideUp('slow');
			}, 3000);

			return;
		}

		$("#client-items-download-excel").text('Processing ..').css('opacity', '0.5').attr('data-in-progress', '1');
		Backbone.ajax({
			type: 'GET',
			url: 'controller.php',
			data: { command: 'ClientItemsDownloadExcel', client_id: $("#client-items-table").attr('data-client-id') },
			dataType: 'json',
			success: function (response) {
				$("#client-items-download-excel").text('Download Excel').css('opacity', 1).attr('data-in-progress', '0');

				if(response.error == 0) {
					$("<a id='client-items-download-link' target='_blank' href='excels/clients/" + response.data.name + "'>DOWNLOAD</a>").insertAfter("#client-items-download-excel");
				}
			}
		});
	}
});


var ClientNewItemView = Backbone.View.extend({
	el: '#client-items-table',
	render: function () {
		var template = _.template(
								$("#client-new-item-view").html(), {
								temp_id: $("#add-more-client-items").attr('data-counter')
								});
		this.$el.append(template);
		$("#add-more-client-items").attr('data-counter', parseInt($("#add-more-client-items").attr('data-counter'), 10) + 1);
	}
});


var ClientSavedItemsView = Backbone.View.extend({
	el: '#client-items-table',
	render: function () {
		var template = _.template(
								$("#client-saved-items-view").html(), {
									items: this.options['items']
								});
		this.$el.append(template);
	}
});


var ClientInfoView = Backbone.View.extend({
	el: '#contents',
	render: function () {
		var template = _.template(
								$("#client-info-view").html(), { 
									client: this.options['client']
								});
		this.$el.append(template);
		$("#client-info-container").css({ left: this.options['offset'].left, top: this.options['offset'].top });
		$("#save-client-info").css('left', ($('#client-info-container').position().left-1) + 'px');
		$("#close-client-info").css('margin-right', ($('#client-info-container').position().left-1) + 'px');
	},
	events: {
		'click #save-client-info': 'saveClientInfo'
	},
	saveClientInfo: function(e) {
		if($("#save-client-info").attr('data-in-progress') == '1') {
			return;
		}

		var blank_reg_exp = /^([\s]{0,}[^\s]{1,}[\s]{0,}){1,}$/,
			email_reg_exp = /^([a-zA-z0-9]{1,}(?:([\._-]{0,1}[a-zA-Z0-9]{1,}))+@{1}([a-zA-Z0-9-]{2,}(?:([\.]{1}[a-zA-Z]{2,}))+))$/,
			digits_reg_exp = /^[0-9]{1,}$/,
			error = 0,
			change = 0,
			client_info = {};
		client_info.client_id = $("#client-info-container").attr('data-client-id');

		$("#client-info-error").hide();
		$("#client-info").find("input[type='text'], textarea").removeClass('client-info-wrong-parameter');

		if(!blank_reg_exp.test($(".client-info-name").val())) {
			$(".client-info-name").addClass('client-info-wrong-parameter');
			error = 1;
		}

		if(blank_reg_exp.test($(".client-info-phone").val())) {
			if(!digits_reg_exp.test($(".client-info-phone").val())) {
				$(".client-info-phone").addClass('client-info-wrong-parameter');
				error = 1;
			}
		}

		if(blank_reg_exp.test($(".client-info-email").val())) {
			if(!email_reg_exp.test($(".client-info-email").val())) {
				$(".client-info-email").prev().addClass('client-info-wrong-parameter');
				error = 1;
			}
		}

		if(error == 1) {
			$("#client-info-error").show();
			return;
		}

		if($(".client-info-name").val() != $(".client-info-name").attr('defaultValue')) {
			client_info.client_name = $(".client-info-name").val();
			change = 1;
		}
		if($(".client-info-phone").val() != $(".client-info-phone").attr('defaultValue')) {
			client_info.client_phone = $(".client-info-phone").val();
			change = 1;
		}
		if($(".client-info-email").val() != $(".client-info-email").attr('defaultValue')) {
			client_info.client_email = $(".client-info-email").val();
			change = 1;
		}
		if($(".client-info-address").val() != $(".client-info-address").attr('defaultValue')) {
			client_info.client_address = $(".client-info-address").val();
			change = 1;
		}

		if(change == 0) {
			return;
		}

		$("#save-client-info").css('opacity', '0.5').text('Saving ..').attr('data-in-progress', '1');
		Backbone.ajax({
			type: 'POST',
			url: 'controller.php',
			data: { command: 'EditClientInfo', client_info: client_info },
			dataType: 'json',
			success: function (response) {
				$("#save-client-info").css('opacity', '1').text('Save').attr('data-in-progress', '0');

				if(response.error == 0) {
					if('client_name' in response.data) {
						$("#client-" + response.data.client_id).find(".client-header-name").text(response.data['client_name']);

						$("#client-info-container").remove();
						if(typeof client_info_view != 'undefined') {
							client_info_view.undelegateEvents();
						}
					}
				}
			}
		});
	}
});