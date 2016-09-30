/**
 * Account Explorer
 */
Analytics.AccountExplorer = Garnish.Base.extend({

	data: null,
	$accountSelect: null,
	$propertySelect: null,
	$viewSelect: null,

	init: function(container, options)
	{
		this.setSettings(options, Analytics.AccountExplorer.defaults);

		this.$container = $(container);

		this.$account = $('.account', this.$container);
		this.$property = $('.property', this.$container);
		this.$view = $('.view', this.$container);

		this.$accountSelect = $('> select', this.$account);
		this.$propertySelect = $('> select', this.$property);
		this.$viewSelect = $('> select', this.$view);


		// Add listeners

		this.addListener(this.$accountSelect, 'change', 'onAccountChange');
		this.addListener(this.$propertySelect, 'change', 'onPropertyChange');
		this.addListener(this.$viewSelect, 'change', 'onViewChange');


		// Load data

		this.$account.addClass('disabled');
		this.$property.addClass('disabled');
		this.$view.addClass('disabled');

		this.$accountSelect.prop('disabled', true);
		this.$propertySelect.prop('disabled', true);
		this.$viewSelect.prop('disabled', true);

		Craft.postActionRequest('analytics/tests/getAccountExplorerData', {}, $.proxy(function(response, textStatus)
		{
			if (textStatus == 'success')
			{
				this.data = response;


				// Add account, property and view options

				this.updateAccountOptions();
				this.updatePropertyOptions();
				this.updateViewOptions();


				// Enable selects

				this.$account.removeClass('disabled');
				this.$property.removeClass('disabled');
				this.$view.removeClass('disabled');

				this.$accountSelect.prop('disabled', false);
				this.$propertySelect.prop('disabled', false);
				this.$viewSelect.prop('disabled', false);
			}
			else
			{
				// error
				console.log('error');
			}
		}, this));
	},

	onAccountChange: function()
	{
		this.updatePropertyOptions();
		this.updateViewOptions();
	},

	onPropertyChange: function()
	{
		this.updateViewOptions();
	},

	onViewChange: function()
	{
		// nothing here for now
	},

	updateAccountOptions: function()
	{
		$('option', this.$accountSelect).remove();

		$.each(this.data.accounts, $.proxy(function(key, account) {
			var $option = $('<option />').appendTo(this.$accountSelect);
			$option.attr('value', account.id);
			$option.text(account.name);
		}, this));
	},

	updatePropertyOptions: function()
	{
		$('option', this.$propertySelect).remove();

		$.each(this.data.properties, $.proxy(function(key, property) {
			if(property.accountId == this.$accountSelect.val())
			{
				var $option = $('<option />').appendTo(this.$propertySelect);
				$option.attr('value', property.id);
				$option.text(property.name);
			}
		}, this));
	},

	updateViewOptions: function()
	{
		$('option', this.$viewSelect).remove();

		$.each(this.data.views, $.proxy(function(key, view) {
			console.log('compare', view.webPropertyId, this.$propertySelect.val());
			if(view.webPropertyId == this.$propertySelect.val())
			{
				var $option = $('<option />').appendTo(this.$viewSelect);
				$option.attr('value', view.id);
				$option.text(view.name);
			}
		}, this));
	},

	initData: function()
	{
		this.data = {
			accounts: [
				{
					id: '1547168',
					name: "Dukt Network",
				},
				{
					id: '67133596',
					name: "Gumroad Dukt",
				}
			],

			properties: [
				{
					id: 'UA-1547168-20',
					accountId: '1547168',
					name: 'https://dukt.net/',
				},
				{
					id: 'UA-1547168-27',
					accountId: '1547168',
					name: 'plugins.dev',
				},
				{
					id: 'UA-67133596-1',
					accountId: '67133596',
					name: 'Gumroad Dukt',
				},
			],

			views: [
				{
					id: '107887337',
					accountId: '67133596',
					propertyId: 'UA-67133596-1',
					name: 'All Web Site Data',
				},
				{
					id: '42395806',
					accountId: '1547168',
					propertyId: 'UA-1547168-20',
					name: 'dukt.net',
				},
				{
					id: '82988457',
					accountId: '1547168',
					propertyId: 'UA-1547168-27',
					name: 'plugins.dev',
				},
			]
		};
	}
}, {
	defaults: {
	}
});