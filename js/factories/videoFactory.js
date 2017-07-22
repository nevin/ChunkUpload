angular.module('myApp').factory('videoFactory',['$http','$q',function($http,$q){
    var serverUrl = 'server/download.php';
    var videoFactory = {};
    videoFactory.getVideoList = function(){
        return $http.get(serverUrl);
    }
    return videoFactory;
}]);