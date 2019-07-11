'use strict';

/* Services */

var jwxiccServices = angular.module('jwxiccServices', ['ngResource']);

jwxiccServices.factory('Phone', ['$resource',
  function($resource){
    return $resource('phones/:phoneId.json', {}, {
      query: {method:'GET', params:{phoneId:'phones'}, isArray:true}
    });
  }]);
