# Microsoft contacts integration for Cisco IP Phones
This set of PHP scripts integrates Outlook.com contacts into Cisco IP Phones.

It provides:

## Endpoints for Cisco IP Phones
located in [`cisco/`](cisco/)
 - `directory.php` - To bring up your Outlook.com contacts on the phone as soon as you press the directory button, set this as [`directoryURL` in its `SEP<MAC>.cnf.xml` config file](https://usecallmanager.nz/sepmac-cnf-xml.html#directoryURL).  
   (this is still in an early state as there's no search function and a maximum of 100 entries is shown)
 - `authenticate.php` - To assign credentials to the phone, you can set this as [`authenticationURL` in its `SEP<MAC>.cnf.xml` config file](https://usecallmanager.nz/sepmac-cnf-xml.html#authenticationURL).  
   (these are required when you are using the phone's [CGI Execute](https://usecallmanager.nz/cgi-execute-xml.html) endpoint, e.g. with [`call.php`](#browser-endpoints))
 
 ## Browser Endpoints
  - [`call.php`](#notes-on-callphp) - Start a call from your browser on an IP Phone whose credentials you know!  
 (suggests telephone numbers based on your contacts as you type)
 - `vcard.php` - export all contacts as ``.vcf`` file  
  (originally, a pretty naive attempt for a CardDAV endpoint that can be used to sync contacts with a telephone system such as a [FRITZ!Box router](https://service.avm.de/help/en/FRITZ-Box-Fon-WLAN-7490/019/hilfe_howto_carddav_kontakte))
- `index.php` - overview of all functions
- `oauth-grant.php` - (Re-)authorize access to your contacts [via Microsoft's Graph API](https://docs.microsoft.com/en-us/graph/api/user-list-contacts)

# Setup
1. Clone or download this repository and put the files on a web server that supports PHP (tested on apache2 with libapache2-mod-php and libapache2-mod-fcgid).
2. [Register an app for OAuth 2.0 in the Microsoft Azure app registration portal](https://docs.microsoft.com/en-us/graph/auth-v2-user#1-register-your-app).
3. There, add the URL of the `oauth-grant.php` on your web server as a redirect URI. For example, `https://example.com/ipphones/oauth-grant.php`.
4. Rename or copy [`config.example.php`](includes/config.example.php) in [`includes`](includes/) to `config.php` and replace the default values with your Client ID and Client Secret.
5. Also, add your IP Phones if you want to use [`call.php`](#browser-endpoints) or [`cisco/authenticate.php`](#endpoints-for-cisco-ip-phones).
   - `authenticate.php` requires `devicename`, `username` and `password` per phone,
   - `call.php` uses `label`, `devicename` (and optionally `host`)  
     (specify a `host` to contact the phone at `http[s]://<host>/` rather than `http[s]://<devicename>/`, e.g. if the latter doesn't work for you)
6. Open your `oauth-grant.php` in a web browser and connect your first Microsoft account.
7. At the function overview that you are greeted with, note the key from the URL somewhere safe: `https://example.org/ipphones/?key=`__f53fe305ad73b3ff33cf__
   - __Be careful: anyone with this key and the ability to reach the URL can view this Microsoft account's contacts!__
   - If you lose the key, you can look it up in [`storage/`](#notes-on-storage-of-keys--connected-microsoft-accounts) on your web server. However, as soon as you connect another account, you'll be unable to distinguish between the two.

8. Add the shown [`directoryURL`](#endpoints-for-cisco-ip-phones) to your phone's `SEP<MAC>.cnf.xml`, and optionally [`authenticationURL`](#endpoints-for-cisco-ip-phones)  
   (e.g. `https://example.org/ipphones/cisco/directory.php?key=`f53fe305ad73b3ff33cf)

# Notes on [`call.php`](call.php)
Suggestions are based on contacts of Microsoft account(s) stored under the provided key(s) (see table below).

Also, [phone books of a FRITZ!Box router](https://service.avm.de/help/en/FRITZ-Box-Fon-WLAN-7490/019/hilfe_fon_telefonbuch) can be integrated using [fritzco](https://github.com/SkyhawkXava/fritzco), see [`config.example.php`](includes/config.example.php). However, note that fritzco's phone books won't be supported in [`directory.php`](#endpoints-for-cisco-ip-phones) since that's a function fritzco itself offers.

Using query parameters in the URL, you can prefill the input fields:

| parameter    | default                           | example value          |
| ------------ | --------------------------------- | ---------------------- |
| `key`        | suggestions based on contacts from CALL_DEFAULT_KEY in [`config.php`](includes/config.example.php) | `f53fe305ad73b3ff33cf` <br/> (comma-separated if multiple) |
| `devicename` | no selection                      | `SEP1304E58F0643`      |
| `num`        | empty                             | `+12065550100` <br/>(you may need to [URL encode](https://www.urlencoder.org/) this) |
| `ssl`        | active                            | `0` or `1`             |

Example: `https://example.org/ipphones/call.php?key=f53fe305ad73b3ff33cf,bf674bddac25380a20bc&devicename=SEP1304E58F0643&num=+12065550100&ssl=1`

# Notes on storage of keys & connected Microsoft accounts
The information on connected Microsoft accounts is stored in [`storage/`](storage/) on your web server, prefixed with the corresponding key:

```
storage/
  .htaccess
  2d71246242e4e3889e2b_access_token
  2d71246242e4e3889e2b_access_token_expiry
  2d71246242e4e3889e2b_refresh_token
  f53fe305ad73b3ff33cf_access_token
  f53fe305ad73b3ff33cf_access_token_expiry
  f53fe305ad73b3ff33cf_refresh_token
  index.php
```

If you want to change a key, change all filenames containing it. __For the sake of your privacy, choose a random hexadecimal string of sufficient length as the new key!__

To disconnect a Microsoft account, go to [its preferences](https://microsoft.com/consent) and revoke all permissions for the Microsoft Azure app you created during [setup](#setup). If you want, you can also remove all files whose names contain the corresponding key from `storage/`.