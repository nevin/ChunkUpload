# Chunk Upload

Angular based web application which uploads files from the browser as chunks and A PHP server side application which saves the video file as a single file from the chunk.

Client side, A library called ng-flow ( based on flow js ) - https://github.com/flowjs/ng-flow is used 

## Dependencies 

### JS libraries ( bundled in the appilication )
- Angularjs (v1.6)
- bootstrap.min.css
- ng-flowflowjs - for chunk upload
- Videogular - html5 based video player for angular

### PHP libraries
- getid3 - media file parse library


### Server side 
- PHP server 5.3+

 
## Configurtions 

In the server side, there is limitation in the file upload size in php.ini and in the webserver configuration 

#### To support more than 2M uploads, change the settings in  php.ini

The maximum size of an uploaded file.

`memory_limit = 100M`

To Sets max size of post data allowed. This setting also affects file upload. To upload large files, this value must be larger than upload_max_filesize

`upload_max_filesize = 100M;`

`post_max_size = 100M`


#### To support more than 2M uploads, change the settings in  nginx webserver

`client_max_body_size 100M; `


#### For video playback using javascript
  - index.html 

 #### For video playback using angular library
  - index-angular.html 

#### for uploading video
  - uploader.html

#### Upload -  server Side
  - server/upload.php

#### get video  -  server Side
 - server/download.php