Facebook-Group-Search
=====================

Combination of an export script and front end. The exporter grabs Facebook group information and imports it to MySQL. The frontend boilerplate supports desktop and mobile.

Instructions:

Skipping the basic LAMP setup, you should first setup import.php.

You first need to get your Facebook group ID (GID) and access token (for open groups a nonexpire token can be used).

Fill in the MySQL information, GID, and access token in import.php.

Attempt to run the script in console: php import.php. If all goes to plan your MySQL should be populated.

From there you may attempt to load the front end which searchs the same database.
