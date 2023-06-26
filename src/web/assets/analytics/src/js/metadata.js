/* global Analytics, $ */

export const getContinentByCode = (code) => {
  var continent;

  $.each(Analytics.continents, function(key, _continent) {
    if (code == _continent.code) {
      continent = _continent.label;
    }
  });

  if (continent) {
    return continent;
  }

  return code;
}

export const getSubContinentByCode = (code) => {
  var continent;

  $.each(Analytics.subContinents, function(key, _continent) {
    if (code == _continent.code) {
      continent = _continent.label;
    }
  });

  if (continent) {
    return continent;
  }

  return code;
}

export default {
  getContinentByCode,
  getSubContinentByCode,
}