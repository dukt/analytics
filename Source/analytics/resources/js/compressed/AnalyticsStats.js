!function(e){"use strict";e.fn.serializeJSON=function(t){var a,s,n,i,o,r,l;return r=e.serializeJSON,l=r.setupOpts(t),s=this.serializeArray(),r.readCheckboxUncheckedValues(s,this,l),a={},e.each(s,function(e,t){n=r.splitInputNameIntoKeysArray(t.name,l),i=n.pop(),"skip"!==i&&(o=r.parseValue(t.value,i,l),l.parseWithFunction&&"_"===i&&(o=l.parseWithFunction(o,t.name)),r.deepSet(a,n,o,l))}),a},e.serializeJSON={defaultOptions:{checkboxUncheckedValue:void 0,parseNumbers:!1,parseBooleans:!1,parseNulls:!1,parseAll:!1,parseWithFunction:null,customTypes:{},defaultTypes:{string:function(e){return String(e)},number:function(e){return Number(e)},"boolean":function(e){var t=["false","null","undefined","","0"];return-1===t.indexOf(e)},"null":function(e){var t=["false","null","undefined","","0"];return-1===t.indexOf(e)?e:null},array:function(e){return JSON.parse(e)},object:function(e){return JSON.parse(e)},auto:function(t){return e.serializeJSON.parseValue(t,null,{parseNumbers:!0,parseBooleans:!0,parseNulls:!0})}},useIntKeysAsArrayIndex:!1},setupOpts:function(t){var a,s,n,i,o,r;r=e.serializeJSON,null==t&&(t={}),n=r.defaultOptions||{},s=["checkboxUncheckedValue","parseNumbers","parseBooleans","parseNulls","parseAll","parseWithFunction","customTypes","defaultTypes","useIntKeysAsArrayIndex"];for(a in t)if(-1===s.indexOf(a))throw new Error("serializeJSON ERROR: invalid option '"+a+"'. Please use one of "+s.join(", "));return i=function(e){return t[e]!==!1&&""!==t[e]&&(t[e]||n[e])},o=i("parseAll"),{checkboxUncheckedValue:i("checkboxUncheckedValue"),parseNumbers:o||i("parseNumbers"),parseBooleans:o||i("parseBooleans"),parseNulls:o||i("parseNulls"),parseWithFunction:i("parseWithFunction"),typeFunctions:e.extend({},i("defaultTypes"),i("customTypes")),useIntKeysAsArrayIndex:i("useIntKeysAsArrayIndex")}},parseValue:function(t,a,s){var n,i;return i=e.serializeJSON,n=s.typeFunctions&&s.typeFunctions[a],n?n(t):s.parseNumbers&&i.isNumeric(t)?Number(t):!s.parseBooleans||"true"!==t&&"false"!==t?s.parseNulls&&"null"==t?null:t:"true"===t},isObject:function(e){return e===Object(e)},isUndefined:function(e){return void 0===e},isValidArrayIndex:function(e){return/^[0-9]+$/.test(String(e))},isNumeric:function(e){return e-parseFloat(e)>=0},optionKeys:function(e){if(Object.keys)return Object.keys(e);var t,a=[];for(t in e)a.push(t);return a},splitInputNameIntoKeysArray:function(t,a){var s,n,i,o,r;return r=e.serializeJSON,o=r.extractTypeFromInputName(t,a),n=o[0],i=o[1],s=n.split("["),s=e.map(s,function(e){return e.replace(/\]/g,"")}),""===s[0]&&s.shift(),s.push(i),s},extractTypeFromInputName:function(t,a){var s,n,i;if(s=t.match(/(.*):([^:]+)$/)){if(i=e.serializeJSON,n=i.optionKeys(a?a.typeFunctions:i.defaultOptions.defaultTypes),n.push("skip"),-1!==n.indexOf(s[2]))return[s[1],s[2]];throw new Error("serializeJSON ERROR: Invalid type "+s[2]+" found in input name '"+t+"', please use one of "+n.join(", "))}return[t,"_"]},deepSet:function(t,a,s,n){var i,o,r,l,c,h;if(null==n&&(n={}),h=e.serializeJSON,h.isUndefined(t))throw new Error("ArgumentError: param 'o' expected to be an object or array, found undefined");if(!a||0===a.length)throw new Error("ArgumentError: param 'keys' expected to be an array with least one element");i=a[0],1===a.length?""===i?t.push(s):t[i]=s:(o=a[1],""===i&&(l=t.length-1,c=t[l],i=h.isObject(c)&&(h.isUndefined(c[o])||a.length>2)?l:l+1),""===o?(h.isUndefined(t[i])||!e.isArray(t[i]))&&(t[i]=[]):n.useIntKeysAsArrayIndex&&h.isValidArrayIndex(o)?(h.isUndefined(t[i])||!e.isArray(t[i]))&&(t[i]=[]):(h.isUndefined(t[i])||!h.isObject(t[i]))&&(t[i]={}),r=a.slice(1),h.deepSet(t[i],r,s,n))},readCheckboxUncheckedValues:function(t,a,s){var n,i,o,r,l;null==s&&(s={}),l=e.serializeJSON,n="input[type=checkbox][name]:not(:checked):not([disabled])",i=a.find(n).add(a.filter(n)),i.each(function(a,n){o=e(n),r=o.attr("data-unchecked-value"),r?t.push({name:n.name,value:r}):l.isUndefined(s.checkboxUncheckedValue)||t.push({name:n.name,value:s.checkboxUncheckedValue})})}}}(window.jQuery||window.Zepto||window.$);var Analytics={},googleVisualisationCalled=!1;Analytics.Stats=Garnish.Base.extend({requestData:null,init:function(e,t){console.log("Analytics.Stats(element, options)"),console.log("---- element",e),console.log("---- options",t),this.$element=$("#"+e),this.$body=$(".body",this.$element),this.$spinner=$(".spinner",this.$element),this.$settingsBtn=$(".dk-settings-btn",this.$element),this.$period=$(".period select",this.$element),this.chartRequest=t.cachedRequest,this.chartResponse=t.cachedResponse,"undefined"!=typeof this.chartRequest&&(this.requestData=this.chartRequest,this.$period.val(this.requestData.period)),this.addListener(this.$period,"change","periodChange"),this.addListener(this.$settingsBtn,"click","openSettings"),this.initGoogleVisualization($.proxy(function(){this.chartResponse&&(this.$spinner.addClass("hidden"),this.handleChartResponse(this.requestData.chart,this.chartResponse))},this))},initGoogleVisualization:function(e){0==googleVisualisationCalled&&("undefined"==typeof AnalyticsChartLanguage&&(AnalyticsChartLanguage="en"),google.load("visualization","1",{packages:["corechart","table","geochart"],language:AnalyticsChartLanguage}),googleVisualisationCalled=!0),google.setOnLoadCallback($.proxy(function(){"undefined"!=typeof google.visualization&&e()},this))},periodChange:function(e){console.log("Analytics.Stats.periodChange()"),this.requestData&&(this.requestData.period=$(e.currentTarget).val(),this.chartResponse=this.sendRequest(this.requestData))},openSettings:function(e){this.settingsModal?this.settingsModal.show():($form=$('<form class="settingsmodal modal fitted"></form>').appendTo(Garnish.$bod),$body=$('<div class="body"/>').appendTo($form),$footer=$('<div class="footer"/>').appendTo($form),$buttons=$('<div class="buttons right"/>').appendTo($footer),$cancelBtn=$('<div class="btn">'+Craft.t("Cancel")+"</div>").appendTo($buttons),$saveBtn=$('<input type="submit" class="btn submit" value="'+Craft.t("Save")+'" />').appendTo($buttons),this.settingsModal=new Garnish.Modal($form,{visible:!1,resizable:!1}),this.addListener($cancelBtn,"click",function(){this.settingsModal.hide()}),this.addListener($form,"submit",$.proxy(function(e){e.preventDefault();var t=$("input, textarea, select",$form).filter(":visible").serializeJSON();this.requestData=t,console.log("parsedParams",this.requestData),this.chartResponse=this.sendRequest(this.requestData),this.settingsModal.hide(),this.saveState()},this)),Craft.postActionRequest("analytics/settingsModal",{},$.proxy(function(e,t){$(".body",this.settingsModal.$container).html(e.html),this.$chartSelect=$(".chart-select select",this.settingsModal.$container),this.requestData&&this.$chartSelect.val(this.requestData.chart),Craft.initUiElements()},this)))},saveState:function(){console.log("Analytics.Stats().saveState()");var e={id:this.$element.data("id"),settings:{chart:this.requestData.chart,period:this.requestData.period,options:this.requestData.options}};console.log("Save state data",e),Craft.queueActionRequest("analytics/saveWidgetState",e,$.proxy(function(e){},this))},sendRequest:function(e){e.period=this.$period.val(),this.$spinner.removeClass("hidden"),$(".chart",this.$body).remove(),console.log("Analytics.Stats().sendRequest(data)"),console.log("---- data",e),Craft.postActionRequest("analytics/stats/getChart",e,$.proxy(function(t,a){this.$spinner.addClass("hidden"),this.handleChartResponse(e.chart,t)},this))},handleChartResponse:function(e,t){switch(e){case"area":this.handleAreaChartResponse(t);break;case"counter":this.handleCounterResponse(t);break;case"geo":this.handleGeoResponse(t);break;case"pie":this.handlePieResponse(t);break;case"table":this.handleTableResponse(t);break;default:console.error('Chart type "'+e+'" not supported.')}},handleGeoResponse:function(e){$chart=$('<div class="chart geo" />'),$chart.appendTo(this.$body),this.chartDataTable=Analytics.Utils.responseToDataTable(e.table),this.chartOptions=Analytics.ChartOptions.geo(this.requestData.dimensions),this.chart=new google.visualization.GeoChart($chart.get(0)),this.chart.draw(this.chartDataTable,this.chartOptions)},handleTableResponse:function(e){$chart=$('<div class="chart table" />'),$chart.appendTo(this.$body),this.chartDataTable=Analytics.Utils.responseToDataTable(e.table),this.chartOptions=Analytics.ChartOptions.table(),this.chart=new google.visualization.Table($chart.get(0)),this.chart.draw(this.chartDataTable,this.chartOptions)},handlePieResponse:function(e){$chart=$('<div class="chart pie" />'),$chart.appendTo(this.$body),this.chartDataTable=Analytics.Utils.responseToDataTable(e.chart),this.chartOptions=Analytics.ChartOptions.pie(),this.chart=new google.visualization.PieChart($chart.get(0)),this.chart.draw(this.chartDataTable,this.chartOptions)},handleAreaChartResponse:function(e){if($chart=$('<div class="chart area" />'),$chart.appendTo(this.$body),this.chartDataTable=Analytics.Utils.responseToDataTable(e.area),this.chartOptions=Analytics.ChartOptions.area(e.period),"year"==e.period){var t=new google.visualization.DateFormat({pattern:"MMMM yyyy"});t.format(this.chartDataTable,0)}this.chart=new google.visualization.AreaChart($chart.get(0)),this.chart.draw(this.chartDataTable,this.chartOptions)},handleCounterResponse:function(e){$chart=$('<div class="chart counter" />').appendTo(this.$body),$value=$('<div class="value" />').appendTo($chart),$label=$('<div class="label" />').appendTo($chart),$period=$('<div class="period" />').appendTo($chart),$value.html(e.counter.count),$label.html(e.metric),$period.html(e.period)}}),Analytics.Utils={responseToDataTable:function(response){console.log("responseToDataTable",response);var data=new google.visualization.DataTable;return $.each(response.cols,function(e,t){data.addColumn(t)}),console.log("response",response),$.each(response.rows,function(kRow,row){$.each(row,function(kCell,cell){switch(response.cols[kCell].type){case"date":$dateString=cell.v,8==$dateString.length?($year=eval($dateString.substr(0,4)),$month=eval($dateString.substr(4,2))-1,$day=eval($dateString.substr(6,2)),$date=new Date($year,$month,$day),row[kCell]=$date):6==$dateString.length&&($year=eval($dateString.substr(0,4)),$month=eval($dateString.substr(4,2))-1,$date=new Date($year,$month,"01"),row[kCell]=$date)}}),data.addRow(row)}),data}},Analytics.ChartOptions=Garnish.Base.extend({},{area:function(e){switch(options=this.defaults.area,e){case"week":options.hAxis.format="E",options.hAxis.showTextEvery=1;break;case"month":options.hAxis.format="MMM d",options.hAxis.showTextEvery=1;break;case"year":options.hAxis.showTextEvery=1,options.hAxis.format="MMM yy"}return options},table:function(){return this.defaults.table},geo:function(e){switch(options=this.defaults.geo,e){case"ga:city":options.displayMode="markers";break;case"ga:country":options.resolution="countries",options.displayMode="regions";break;case"ga:continent":options.resolution="continents",options.displayMode="regions";break;case"ga:subContinent":options.resolution="subcontinents",options.displayMode="regions"}return options},pie:function(){return this.defaults.pie},field:function(){return{colors:["#058DC7"],backgroundColor:"#fdfdfd",areaOpacity:.1,pointSize:8,lineWidth:4,legend:!1,hAxis:{textStyle:{color:"#888"},baselineColor:"#fdfdfd",gridlines:{color:"none"}},vAxis:{maxValue:5},series:{0:{targetAxisIndex:0},1:{targetAxisIndex:1}},vAxes:[{textStyle:{color:"#888"},format:"#",textPosition:"in",baselineColor:"#eee",gridlines:{color:"#eee"}},{textStyle:{color:"#888"},format:"#",textPosition:"in",baselineColor:"#eee",gridlines:{color:"#eee"}}],chartArea:{top:10,bottom:10,width:"100%",height:"80%"}}},defaults:{area:{theme:"maximized",legend:"none",backgroundColor:"#FFF",colors:["#058DC7"],areaOpacity:.1,pointSize:8,lineWidth:4,chartArea:{},hAxis:{format:"E",textPosition:"in",textStyle:{color:"#058DC7"},showTextEvery:1,baselineColor:"#fff",gridlines:{color:"none"}},vAxis:{textPosition:"in",textStyle:{color:"#058DC7"},baselineColor:"#ccc",gridlines:{color:"#fafafa"},maxValue:0}},geo:{displayMode:"auto"},pie:{theme:"maximized",height:282,pieHole:.5,legend:{alignment:"center",position:"top"},chartArea:{top:40,height:"82%"},sliceVisibilityThreshold:1/120},table:{}}});