angular.module('myApp').controller('videoViewerController',
        ["$sce", "$timeout", "$scope","videoFactory",  function ($sce, $timeout, $scope, videoFactory) {

            $scope.setLoading = function(loading) {
                $scope.isLoading = loading;
            };
            $scope.setLoading(true);
            $scope.sourceList = [];
            $scope.playList = {}; 
            $scope.controller = this;
            $scope.controller.state = null;
            $scope.controller.API = null;
            $scope.controller.currentVideo = 0;
            $scope.selectedVideo = "";
            $scope.videoList = [];
            videoFactory.getVideoList().then(function success(response){
                $scope.setLoading(false);
                if(response.status == 200){

                    $scope.playList = response.data;
                    for(var i = 0; i<response.data.length;i++){
                        var sources = [];
                        var sourceArr = {};
                        $scope.videoList[i] = response.data[i];
                        sources.push({src: $sce.trustAsResourceUrl(response.data[i].videoUrl), type: response.data[i].mime_type,tag:i});
                       
                        sourceArr.sources = sources;
                
                        $scope.sourceList.push(sourceArr);
                    }
               

                $scope.controller.videos =$scope.sourceList;
                
      
                $scope.controller.config = {
                preload: "none", 
                autoHide: false,
                autoHideTime: 3000,
                autoPlay: false,
                sources: $scope.controller.videos[0].sources,
                theme: {
                    url: "./lib/js/videogular/themes/default/videogular.css"
                },
                plugins: {
                        poster: "./img/press-here-to-play-button.png"
                }
            };


             $scope.controller.setVideo = function(index) {
                $scope.controller.API.stop();
                $scope.controller.currentVideo = index;
                $scope.selectedVideo  = $scope.videoList[index];
                $scope.controller.config.sources = $scope.controller.videos[index].sources;
                 $scope.setLoading(false);
                $timeout($scope.controller.API.play.bind($scope.controller.API), 100);
            };

                } else{
                    console.log("error");

                }

            }, function error(error){
                console.log(error);
                $scope.setLoading(false);

            })

            

            $scope.controller.onPlayerReady = function(API) {
                $scope.controller.API = API;

            };

            $scope.controller.onCompleteVideo = function() {
                $scope.controller.isCompleted = true;

                $scope.controller.currentVideo++;

                if ($scope.controller.currentVideo >= $scope.controller.videos.length) $scope.controller.currentVideo = 0;

                $scope.controller.setVideo($scope.controller.currentVideo);
            };            
        }]
    );
