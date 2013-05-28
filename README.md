[Facebook Group Search](http://salbahra.github.io/Facebook-Group-Search/)
=====================

Combination of an export script and front end. The exporter grabs Facebook group information and imports it to MySQL. The frontend boilerplate supports searching the database on both desktop and mobile.

Instructions:
-------------

+ You first need to get your Facebook group ID (GID) and access token (for open groups a no-expire application token can be used).
  + More information [can be found here](https://developers.facebook.com/docs/opengraph/howtos/publishing-with-app-token/)

+ Download the files
  + ```cd /var/www/```
  + ```git clone https://github.com/salbahra/Facebook-Group-Search.git fb```
  + ```git clone https://github.com/serbanghita/Mobile-Detect.git fb/mobiledetect```

+ Fill in the MySQL information, GID, and access token in config.php.
  + ```nano /var/www/fb/config.php```

+ Attempt to run the script in console:
  + ```php /var/www/fb/import.php```
  + If all goes to plan your MySQL should be populated

+ Add the import to crontab every hour to ensure your database is up to date with Facebook by adding:
  + ```0 * * * * php /var/www/fb/import.php >/dev/null 2>&1```

+ From there you may attempt to load the front end which searchs the same database and uses the same config

Known Problems:
---------------

None!

Disclaimer:
-----------

This has not been tested yet and is slowly being migrated from a working copy sans specific information for that use.
[![githalytics.com alpha](https://cruel-carlota.pagodabox.com/e5cc4d27f7ebaa5a2d81276f31b2f9ae "githalytics.com")](http://githalytics.com/salbahra/Facebook-Group-Search)
