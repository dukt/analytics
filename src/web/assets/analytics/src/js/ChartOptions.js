/** global: Analytics */
/** global: Garnish */
/**
 * Chart Options
 */
import merge from 'lodash.merge';

export class ChartOptions {
  constructor() {
    this.defaults = {
      area: {
        theme: 'maximized',
        legend: 'none',
        backgroundColor: 'transparent',
        colors: ['#058DC7'],
        areaOpacity: 0.1,
        pointSize: 7,
        lineWidth: 4,
        chartArea: {},
        hAxis: {
          textPosition: 'in',
          textStyle: {
            color: '#058DC7'
          },
          baselineColor: '#fff',
          gridlines: {
            color: 'none'
          }
        },
        vAxis: {
          textPosition: 'in',
          textStyle: {
            color: '#058DC7'
          },
          baselineColor: '#ccc',
          gridlines: {
            color: '#fafafa'
          },
          // viewWindow: {min:0, max: 10},
          minValue: 0,
          maxValue: 10,
          format: '#'
        }
      },

      geo: {
        // height: 282
        displayMode: 'auto'
      },

      pie: {
        theme: 'maximized',
        height: 282,
        pieHole: 0.5,
        legend: {
          alignment: 'center',
          position: 'top'
        },
        chartArea: {
          top: 40,
          height: '82%'
        },
        sliceVisibilityThreshold: 1 / 120
      },

      table: {
        page: 'auto',
        pageSize: 10,
      }
    }
  }

  area(scale) {
    var options = JSON.parse(JSON.stringify(this.defaults.area));

    switch (scale) {
      case 'week':
        options.hAxis.format = 'E';
        break;

      case 'month':
        options.hAxis.format = 'MMM d';
        break;

      case 'year':
        options.hAxis.format = 'MMM yy';
        break;
    }

    return options;
  }

  table() {
    return this.defaults.table;
  }

  geo(dimension) {
    var options = this.defaults.geo;

    switch (dimension) {
      case 'city':
        options.displayMode = 'markers';
        break;

      case 'country':
        options.resolution = 'countries';
        options.displayMode = 'regions';
        break;

      case 'continent':
      case 'continentId':
        options.resolution = 'continents';
        options.displayMode = 'regions';
        break;

      case 'subContinent':
        options.resolution = 'subcontinents';
        options.displayMode = 'regions';
        break;
    }

    return options;
  }

  pie() {
    return this.defaults.pie;
  }

  field() {
    var areaOptions = JSON.parse(JSON.stringify(this.defaults.area));

    return merge(areaOptions, {
      height: 120,
      hAxis: {
        format: 'MMM d',
      }
    })
  }
}

