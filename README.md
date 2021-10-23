# Microsoft contacts integration for Cisco IP Phones
This toolbox of PHP scripts integrates Outlook.com contacts into Cisco IP Phones.

It provides:
 - [``ciscodir.php``](ciscodir.php) - endpoint to use as [``directoryURL`` in the ``SEP<MAC>.cnf.xml`` config file](https://usecallmanager.nz/sepmac-cnf-xml.html#directoryURL) of the IP phone  
  (This would bring up your Outlook.com contacts on the phone when you press the directory button.)
 - [``index.php``](index.php) - browser endpoint for (re-)authorizing the app to access the user's contacts [via Microsoft's Graph API](https://docs.microsoft.com/en-us/graph/api/user-list-contacts?view=graph-rest-1.0&tabs=http)
 - [``vcard.php``](vcard.php) - to export all contacts as ``*.vcf`` file  
  (will hopefully be expanded to a CardDAV endpoint that can be used to sync contacts with a telephone system such as a [FRITZ!Box router](https://service.avm.de/help/en/FRITZ-Box-Fon-WLAN-7490/019/hilfe_howto_carddav_kontakte))

# Setup
1. Clone or download this repository and put the files on a web server that supports PHP (tested with apache2 and libapache2-mod-php).
2. [Register an app for OAuth 2.0 in the Microsoft Azure app registration portal](https://docs.microsoft.com/en-us/graph/auth-v2-user#1-register-your-app).  
Add the URL of the repository files on your webserver and the [``index.php``](index.php) as redirect URIs. For example, ``https://example.com/cisco/mscontacts/`` as well as ``https://example.com/cisco/mscontacts/index.php``, if the files served at ``https://example.com/cisco/mscontacts/``.
3. Rename [``config.php.dist``](config.php.dist) to ``config.php`` and replace the default values with your Client ID and Client Secret.
