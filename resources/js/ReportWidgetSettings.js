/**
 * Report Widget Settings
 */
Analytics.ReportWidgetSettings = Garnish.Base.extend(
{
	$container: null,
	$form: null,
	$chartTypes: null,
	$chartSelect: null,
	$selectizeSelects: null,

	init: function(id, settings)
	{
		this.$container = $('#'+id);
		this.$form = this.$container.closest('form');

		this.$chartTypes = $('.chart-picker ul.chart-types li', this.$form);
		this.$chartSelect = $('.chart-select select', this.$form);

		this.$selectizeSelects = $('.selectize select', this.$form);
		this.$selectizeSelects.selectize();

		this.addListener(this.$chartTypes, 'click', $.proxy(function(ev) {

			var $target = $(ev.currentTarget);

			this.$chartTypes.removeClass('active');

			$target.addClass('active');


			// before change

			var $chartSettingsBefore = $('.chart-settings > div:not(.hidden)', this.$form);

			var $metricSelectBefore = $('.metric select', $chartSettingsBefore);
			var $metricValueBefore;

			if($metricSelectBefore.length > 0)
			{
				$metricValueBefore = $metricSelectBefore[0].selectize.getValue();
			}

			var $dimensionSelectBefore = $('.dimension select', $chartSettingsBefore);
			var $dimensionValueBefore;

			if($dimensionSelectBefore.length > 0)
			{
				$dimensionValueBefore = $dimensionSelectBefore[0].selectize.getValue();
			}


			// change chart select

			this.$chartSelect.val($target.data('chart-type'));
			this.$chartSelect.trigger('change');

			
			// after change

			var $chartSettingsAfter = $('.chart-settings > div:not(.hidden)', this.$form);

			var $metricSelectAfter = $('.metric select', $chartSettingsAfter);

			if($metricSelectAfter.length > 0)
			{
				if($metricSelectAfter[0].selectize.options[$metricValueBefore])
				{
					$metricSelectAfter[0].selectize.setValue($metricValueBefore);
				}
			}

			var $dimensionSelectAfter = $('.dimension select', $chartSettingsAfter);

			if($dimensionSelectAfter.length > 0)
			{
				if($dimensionSelectAfter[0].selectize.options[$dimensionValueBefore])
				{
					$dimensionSelectAfter[0].selectize.setValue($dimensionValueBefore);
				}
			}

		}, this));

		this.$chartTypes.filter('[data-chart-type='+this.$chartSelect.val()+']').trigger('click');

		window.dashboard.grid.refreshCols(true);
	}
});

