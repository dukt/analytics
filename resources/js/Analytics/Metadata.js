
Analytics.Metadata = {

	getContinentByCode: function(code)
	{
		var continent;

		$.each(Analytics.continents, function(key, _continent)
		{
			if(code == _continent.code)
			{
				continent = _continent.label;
			}
		});

		if(continent)
		{
			return continent;
		}

		return code;
	},

	getSubContinentByCode: function(code)
	{
		var continent;

		$.each(Analytics.subContinents, function(key, _continent)
		{
			if(code == _continent.code)
			{
				continent = _continent.label;
			}
		});

		if(continent)
		{
			return continent;
		}

		return code;
	}
};
