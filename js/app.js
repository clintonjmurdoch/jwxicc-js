'use strict';

/* App Module */

var jwxiccApp = angular.module('jwxiccApp', [
  'ngRoute',
  'jwxiccAnimations',
  'jwxiccControllers',
  'jwxiccFilters',
  'jwxiccServices'
]);
jwxiccApp.run(function($rootScope) {
    $rootScope.wfOnlyCheckboxModel = {wfOnly:false};
});

jwxiccApp.config(['$routeProvider','$locationProvider',
  function($routeProvider,$locationProvider) {
    $routeProvider.
      when('/records/team', {
      templateUrl: 'partials/records/team.html',
      controller: 'RecTeamCtrl'
      }).
      when('/records/batting', {
        templateUrl: 'partials/records/batting.html',
        controller: 'RecIndivBatCtrl'
      }).
      when('/records/bowling', {
        templateUrl: 'partials/records/bowling.html',
        controller: 'RecIndivBowlCtrl'
      }).
      when('/records/partnerships', {
        templateUrl: 'partials/records/partnerships.html',
        controller: 'RecPartnershipsCtrl'
      }).
      otherwise({
        redirectTo: '/records/batting'
      });
      $locationProvider.html5Mode(true);
  }]);
