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

		this.$refreshViewsBtn = $('.refresh-views', this.$container);
		this.$spinner = $('.spinner', this.$container);

		this.$accountSelect = $('.account > select', this.$container);
		this.$propertySelect = $('.property > select', this.$container);
		this.$viewSelect = $('.view > select', this.$container);


		// Add listeners

		this.addListener(this.$refreshViewsBtn, 'click', 'refreshViews');
		this.addListener(this.$accountSelect, 'change', 'onAccountChange');
		this.addListener(this.$propertySelect, 'change', 'onPropertyChange');
		this.addListener(this.$viewSelect, 'change', 'onViewChange');

		if(this.settings.forceRefresh)
		{
			this.refreshViews();
		}
	},

	refreshViews: function()
	{
		this.$spinner.removeClass('hidden');
		this.$refreshViewsBtn.addClass('disabled');

		Craft.postActionRequest('analytics/tests/getAccountExplorerData', {}, $.proxy(function(response, textStatus)
		{
			if (textStatus == 'success')
			{
				this.data = response;

				var currentAccountId = this.$accountSelect.val();
				var currentPropertyId = this.$propertySelect.val();
				var currentViewId = this.$viewSelect.val();


				// Add account, property and view options

				this.updateAccountOptions();
				this.updatePropertyOptions();
				this.updateViewOptions();

				if(currentAccountId)
				{
					this.$accountSelect.val(currentAccountId);
					this.$accountSelect.trigger('change');
				}

				if(currentPropertyId)
				{
					this.$propertySelect.val(currentPropertyId);
					this.$propertySelect.trigger('change');
				}

				if(currentViewId)
				{
					this.$viewSelect.val(currentViewId);
					this.$viewSelect.trigger('change');
				}

				this.$spinner.addClass('hidden');
				this.$refreshViewsBtn.removeClass('disabled');
			}
			else
			{
				console.log('Couldn’t load account explorer data.');
			}
		}, this));
	},

	onAccountChange: function()
	{
		console.log('onAccountChange');
		this.updatePropertyOptions();
		this.updateViewOptions();
	},

	onPropertyChange: function()
	{
		console.log('onPropertyChange');
		this.updateViewOptions();
	},

	onViewChange: function()
	{
		console.log('onViewChange');
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