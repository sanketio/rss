## RSS Reader

  Display feed data of URL, Download those feeds in PDF.
  
### Contributor : [Sanket Parmar](https;//github.com/sanketio)

### Description

  This is a web application which gives the feed data of the website url logged in user has requested. Login is for keeping the report of searches he has made. The sequencial flow is given below step-wise:
  
  * User has to login using his/her gmail account.
  * After login when user submits the url, the application will first check whether given url is a valid feed url or not.
  * If requested url is not a valid feed url then application will find the feed url.
  * After this application will parse the feed url and displays to the user in slideshow with image (first image from content part, if content part has no images then it will fetch first image from description part), title and descrption.
  * User can download the feeds in PDF format.
  
### Live Demo Link
  http://sanket.host22.com/rss/

### Technologies and Libraries
  * **Backend** : PHP
  * **Frontend** : HTML5, CSS, jQyery, Bootstrap
  * **Libs** :
    * mpdf (To download Feed data in PDF Format)
    * Google Oauth (To sign in with gmail account)
  
