/* global Analytics, google, $, Craft, d3 */

import Metadata from './metadata.js';

/**
 * Response to data table
 */
export const responseToDataTable = (response, localeDefinition) => {
  var dataTable = new google.visualization.DataTable();


  // Columns

  $.each(response.cols, function(key, column) {
    var dataTableColumnType;

    switch (column.type) {
      case 'date':
        dataTableColumnType = 'date';
        break;
      case 'percent':
      case 'time':
      case 'integer':
      case 'currency':
      case 'float':
        dataTableColumnType = 'number';
        break;

      default:
        dataTableColumnType = 'string';
    }

    dataTable.addColumn({
      type: dataTableColumnType,
      label: column.label,
      id: column.id,
    });
  });


  // Rows

  $.each(response.rows, $.proxy(function(keyRow, row) {

    var dataTableRow = [];

    $.each(response.cols, $.proxy(function(keyColumn, column) {
      switch (column.type) {
        case 'date':
          dataTableRow[keyColumn] = formatByType(localeDefinition, column.type, row[keyColumn]);
          break;

        case 'float':
          dataTableRow[keyColumn] = +row[keyColumn];
          break;

        case 'integer':
        case 'currency':
        case 'percent':
        case 'time':
        case 'continent':
        case 'continentId':
          dataTableRow[keyColumn] = {
            v: formatRawValueByType(localeDefinition, column.type, row[keyColumn]),
            f: formatByType(localeDefinition, column.type, row[keyColumn])
          };
          break;

        default:
          dataTableRow[keyColumn] = row[keyColumn];
      }
    }, this));

    dataTable.addRow(dataTableRow);

  }, this));

  return dataTable;
}

export const formatRawValueByType = (localeDefinition, type, value) => {
  switch (type) {
    case 'integer':
    case 'currency':
    case 'percent':
    case 'time':
    case 'float':
      return +value;

    default:
      return value;
  }
}

export const formatByType = (localeDefinition, type, value) => {
  switch (type) {
    case 'continent':
      return Metadata.getContinentByCode(value);

    case 'currency':
      return formatCurrency(localeDefinition, +value);

    case 'float':
      return (+value).toFixed(2);

    case 'integer':
      return formatInteger(localeDefinition, +value);

    case 'time':
      return formatDuration(+value);

    case 'percent':
      return formatPercent(localeDefinition, +value);

    case 'date': {
      const dateString = value;

      if (dateString.length == 8) {
        // 20150101

        let $year = parseInt(dateString.substr(0, 4));
        let $month = parseInt(dateString.substr(4, 2)) - 1;
        let $day = parseInt(dateString.substr(6, 2));

        const $date = new Date($year, $month, $day);

        return $date;
      } else if (dateString.length == 6) {
        // 201501

        let $year = parseInt(dateString.substr(0, 4));
        let $month = parseInt(dateString.substr(4, 2)) - 1;

        const $date = new Date($year, $month, '01');

        return $date;
      }
      break;
    }

    default:
      return value;
  }
}


export const getD3Locale = (localeDefinition) => {
  return d3.formatLocale(localeDefinition);
}

export const formatCurrency = (localeDefinition, value) => {
  var d3Locale = getD3Locale(localeDefinition);
  var formatter = d3Locale.format(Craft.charts.BaseChart.defaults.formats.currencyFormat);

  return formatter(value);
}

export const formatDuration = (_seconds) => {
  var sec_num = parseInt(_seconds, 10); // don't forget the second param
  var hours = Math.floor(sec_num / 3600);
  var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
  var seconds = sec_num - (hours * 3600) - (minutes * 60);

  if (hours < 10) {
    hours = "0" + hours;
  }
  if (minutes < 10) {
    minutes = "0" + minutes;
  }
  if (seconds < 10) {
    seconds = "0" + seconds;
  }
  return hours + ':' + minutes + ':' + seconds;
}

export const formatInteger = (localeDefinition, value) => {
  return getD3Locale(localeDefinition).format(",")(value);
}

export const formatPercent = (localeDefinition, value) => {
  return getD3Locale(localeDefinition).format(Craft.charts.BaseChart.defaults.formats.percentFormat)(value / 100);
}

export const _getD3LocaleDefinition = (extendLocaleDefinition) => {
  return $.extend(true, Analytics.d3FormatLocaleDefinition, extendLocaleDefinition);
}

export const getLocaleDefinition = (currencyDefinition) => {
  // Get D3 locale definition with currency definition set from the widget's settings
  return _getD3LocaleDefinition({
    currency: currencyDefinition
  })
}

export const t = (category, message, params) => {
  return Craft.t(category, message, params)
}