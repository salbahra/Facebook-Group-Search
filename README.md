[Facebook Group Search](http://salbahra.github.io/Facebook-Group-Search/)
=====================

Combination of an export script and front end. The exporter grabs Facebook group information and imports it to MySQL. The frontend boilerplate supports searching the database on both desktop and mobile.

Instructions:
-------------

+ You first need to get your Facebook group ID (GID) and access token (for open groups a no-expire application token can be used).
  + More information [can be found here](https://developers.facebook.com/docs/opengraph/howtos/publishing-with-app-token/)

+ Install prerequisites as needed (example for Debian using Apache web server)
  + ```apt-get install apache2 php5 libapache2-mod-php5 mysql-server``` 

+ Create the directory you wish to place the files in (ex. /var/www/fb for http://yourwebsite/fb)
  + ```mkdir -m 777 /var/www/fb```

+ Download the files to your web directory
  + ```git clone https://github.com/salbahra/Facebook-Group-Search.git /var/www/fb```
  + ```git clone https://github.com/serbanghita/Mobile-Detect.git /var/www/fb/mobiledetect```

+ From there you may attempt to access the front end.

[![githalytics.com alpha](https://cruel-carlota.pagodabox.com/e5cc4d27f7ebaa5a2d81276f31b2f9ae "githalytics.com")](http://githalytics.com/salbahra/Facebook-Group-Search)
