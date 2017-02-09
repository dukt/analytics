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



		if(this.settings.forceRefresh)
		{
			this.refreshViews();
		}
		else
		{
			this.parseAccountExplorerData(this.settings.data);
		}
	},

	refreshViews: function()
	{
		this.$spinner.removeClass('hidden');
		this.$refreshViewsBtn.addClass('disabled');

		Craft.postActionRequest('analytics/settings/getAccountExplorerData', {}, $.proxy(function(response, textStatus)
		{
			if (textStatus == 'success')
			{
				if(response.error)
				{
					alert(response.error);
				}
				else
				{
					this.parseAccountExplorerData(response);
				}

				this.$spinner.addClass('hidden');
				this.$refreshViewsBtn.removeClass('disabled');
			}
			else
			{
				alert('Couldn’t load account explorer data.');
			}
		}, this));
	},

	parseAccountExplorerData: function(data)
	{
		this.data = data;

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

	updateAccountOptions: function()
	{
		$('option', this.$accountSelect).remove();

		if(this.data)
		{
			$.each(this.data.accounts, $.proxy(function(key, account) {
				var $option = $('<option />').appendTo(this.$accountSelect);
				$option.attr('value', account.id);
				$option.text(account.name);
			}, this));
		}
	},

	updatePropertyOptions: function()
	{
		$('option', this.$propertySelect).remove();

		if(this.data)
		{
			$.each(this.data.properties, $.proxy(function(key, property) {
				if(property.accountId == this.$accountSelect.val())
				{
					var $option = $('<option />').appendTo(this.$propertySelect);
					$option.attr('value', property.id);
					$option.text(property.name);
				}
			}, this));
		}
	},

	updateViewOptions: function()
	{
		$('option', this.$viewSelect).remove();

		if(this.data)
		{

			$.each(this.data.views, $.proxy(function(key, view) {
				if(view.webPropertyId == this.$propertySelect.val())
				{
					var $option = $('<option />').appendTo(this.$viewSelect);
					$option.attr('value', view.id);
					$option.text(view.name);
				}
			}, this));
		}
	}
}, {
	defaults: {
	}
});