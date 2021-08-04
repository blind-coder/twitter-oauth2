This is a translation gateway between Twitter's OAuth1 authentication and OAuth2. This way, IDP services like Okta can authenticate users via Twitter social login by going through this gateway instead.

Requirements
============
Requires a recent version of Apache, mod\_php and MySQL. Unless you're using something from pre-2018, you should be good.

Twitter App
===========
Go to https://developer.twitter.com/en/portal/projects-and-apps and register a new standalone application. Make sure to enable "3-legged OAuth" and "Request email address from users". Then go to the tab "Keys and tokens" and request a set of Consumer Keys (ID and Secret).

Installation
============
Clone the repository into your webservers directory. Copy etc/Config.sample.php to etc/Config.php and edit the settings in there to suit your setup. See docs/okta.png for how to configure Okta for Twitter authentication through this gateway. Create MySQL tables with the sql/structure.sql file. Go to certs/ and create a private key and public key by running `make`. Then create a JWKS on https://russelldavies.github.io/jwk-creator/ with the public key and Key Use set to "signing", Algorithm set to "RS256" and key id set to 0. Put the resulting jwks to certs/jwks.json.

Once you set up a routing rule for Okta to request authentication from Twitter your users should now be able to log in with Twitter.
