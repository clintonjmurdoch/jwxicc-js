'use strict';

/* Controllers */

var jwxiccControllers = angular.module('jwxiccControllers', []);

jwxiccControllers.controller('RecTeamCtrl', ['$scope', '$http','$rootScope', 
            function ($scope, $http, $rootScope){
      $scope.filterWillowfest = function() {
        $scope.overallRecord = null;
        $scope.highestScoresFor = null;
        $scope.highestScoresAgainst = null;
        $scope.lowestScoresFor = null;
        $scope.lowestScoresAgainst = null;
        $http.get('api/records/team/overallrecord?willowfestOnly=' + $rootScope.wfOnlyCheckboxModel.wfOnly).
          then(function(response) {
              $scope.overallRecord = response.data;
        });
        $http.get('api/records/team/highestscores/for?willowfestOnly=' + $rootScope.wfOnlyCheckboxModel.wfOnly).
          then(function(response) {
              $scope.highestScoresFor = response.data
        });
        $http.get('api/records/team/highestscores/against?willowfestOnly=' + $rootScope.wfOnlyCheckboxModel.wfOnly).
          then(function(response) {
              $scope.highestScoresAgainst = response.data
        });
        $http.get('api/records/team/lowestscores/for?willowfestOnly=' + $rootScope.wfOnlyCheckboxModel.wfOnly).
          then(function(response) {
              $scope.lowestScoresFor = response.data
        });
        $http.get('api/records/team/lowestscores/against?willowfestOnly=' + $rootScope.wfOnlyCheckboxModel.wfOnly).
          then(function(response) {
              $scope.lowestScoresAgainst = response.data
        });
      };
      $scope.filterWillowfest();
    }]);

jwxiccControllers.controller('RecIndivBatCtrl', ['$scope', '$http','$rootScope', 
            function ($scope, $http, $rootScope){
      $scope.filterWillowfest = function() {
        $scope.highestScores = null;
        $scope.careerRuns = null;
        $scope.careerAverage = null;
        $http.get('api/records/individual/batting/highscore?willowfestOnly=' + $rootScope.wfOnlyCheckboxModel.wfOnly).
          then(function(response) {
              $scope.highestScores = response.data;
        });
        $http.get('api/records/individual/batting/careerruns?willowfestOnly=' + $rootScope.wfOnlyCheckboxModel.wfOnly).
          then(function(response) {
              $scope.careerRuns = response.data
        });
        $http.get('api/records/individual/batting/careeraverage?willowfestOnly=' + $rootScope.wfOnlyCheckboxModel.wfOnly).
          then(function(response) {
              $scope.careerAverage = response.data
        });
      };
      $scope.filterWillowfest();
    }]);

jwxiccControllers.controller('RecIndivBowlCtrl', ['$scope', '$http','$rootScope', 
            function ($scope, $http, $rootScope){
      $scope.filterWillowfest = function() {
        $scope.bestBowling = null;
        $scope.careerWickets = null;
        $scope.careerAverage = null;
        $http.get('api/records/individual/bowling/bestbowling?willowfestOnly=' + $rootScope.wfOnlyCheckboxModel.wfOnly).
          then(function(response) {
              $scope.bestBowling = response.data;
        });
        $http.get('api/records/individual/bowling/careerwickets?willowfestOnly=' + $rootScope.wfOnlyCheckboxModel.wfOnly).
          then(function(response) {
              $scope.careerWickets = response.data
        });
        $http.get('api/records/individual/bowling/careeraverage?willowfestOnly=' + $rootScope.wfOnlyCheckboxModel.wfOnly).
          then(function(response) {
              $scope.careerAverage = response.data
        });
      };
      $scope.filterWillowfest();
    }]);

jwxiccControllers.controller('RecPartnershipsCtrl', ['$scope', '$http','$rootScope', 
            function($scope,$http, $rootScope){
      $scope.filterWillowfest = function() {
        $scope.partnerships = []
        $scope.wickets = [0,1,2,3,4,5,6,7,8,9,10]
        angular.forEach($scope.wickets, function(value, key) {
          $http.get('api/records/partnerships/' + value + '?willowfestOnly=' + $rootScope.wfOnlyCheckboxModel.wfOnly).
          then(function(response) {
              $scope.partnerships[value] = response.data
          });
        });
      };
      $scope.filterWillowfest();
    }]);

jwxiccControllers.controller('PhoneDetailCtrl', ['$scope', '$routeParams', 'Phone',
  function($scope, $routeParams, Phone) {
    $scope.phone = Phone.get({phoneId: $routeParams.phoneId}, function(phone) {
      $scope.mainImageUrl = phone.images[0];
    });

    $scope.setImage = function(imageUrl) {
      $scope.mainImageUrl = imageUrl;
    }
  }]);
