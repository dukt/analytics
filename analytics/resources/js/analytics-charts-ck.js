$(document).ready(function(){charts=$(".analyticsChart.bar, .analyticsChart.donut, .analyticsChart.bubble, .analyticsChart.column, .analyticsChart.line");charts.each(function(e,t){var n=$(".data",t).html(),r=$.parseJSON(n);Craft.postActionRequest("analytics/charts/parse",r,function(e){e=$.parseJSON(e);$(t).dchart(e,r)})});charts=$(".analyticsChart.table");charts.each(function(e,t){var n=$(".data",t).html(),r=$.parseJSON(n);Craft.postActionRequest("analytics/charts/parseTable",r,function(e){e=$.parseJSON(e);$(t).dchart(e,r)})})});